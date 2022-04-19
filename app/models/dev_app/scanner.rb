require 'net/http'
require 'xsv'

class DevApp::Scanner
	def initialize(file)
		x = Xsv::Workbook.open(file)
		sheet = x.sheets[0]
		sheet.mode # => :array
		sheet.parse_headers!
		sheet.mode # => :hash
		@data = []
		sheet.each_row do |row|
      next if row["Application Number"] == "D07-16-15-0001 (Phase 2)" # broken API edge-case; cut down exception noise
      d = {}
			d[:app_number] = row["Application Number"]
      d[:date] = row["Application Date"]
      d[:type] = row["Application Type"]
      d[:road_number] = row["Address Number"]
      d[:road_name] = row["Road Name"]
      d[:road_type] = row["Road Type"]
      d[:status_type] = row["Object Status Type"]
      d[:status] = row["Application Status"]
      d[:file_lead] = row["File Lead"]
      d[:description] = row["Brief Description"]
      d[:status_date] = row["Object Status Date"]
      d[:ward_num] = row["Ward #"]
      d[:ward_name] = row["Ward"]
			@data << d
		end
	end

	def to_a
		@data.dup
	end

  def self.scan_application(app_number)
    url = "https://devapps-restapi.ottawa.ca/devapps/search?authKey=#{authkey}&appStatus=all&searchText=#{app_number}&appType=all&ward=all&bounds=0,0,0,0"

    d = JSON.parse(Net::HTTP.get(URI(url)))
    # return nil if d["totalDevApps"] == 0

    announcements = []

    data = d["devApps"].select{ |data| data["applicationNumber"] == app_number}.first

    attributes = {}
    attributes[:app_id] = data["devAppId"]
    attributes[:app_number] = data["applicationNumber"]
    attributes[:app_type] = data.dig("applicationType","en")
    # these need to be in a join/array table to track state over time
    # attributes[:status] = data.dig("applicationStatus", "en")
    # attributes[:statusDetail] = data.dig("objectStatus", "objectCurrentStatus", "en")
    # attributes[:foo] = data.dig("objectStatus", "objectCurrentStatusDateYMD")
    DevApp::Entry.transaction do 
      entry = DevApp::Entry.where(app_number: app_number, app_id: data["devAppId"]).first || DevApp::Entry.new
      entry.assign_attributes(attributes)
      unless entry.persisted?
        announcements << {type: :new_dev_app}
      end
      entry.save!

      address_attr_mapping = {
        "addressReferenceId" => "ref_id",
        "addressNumber" => "road_number",
        "addressQualifier" => "qualifier",
        "legalUnit" => "legal_unit",
        "roadName" => "road_name",
        "cardinalDirection" => "direction",
        "roadType" => "road_type",
        "municipality" => "municipality",
        "addressType" => "address_type",
        "addressLatitude" => "lat",
        "addressLongitude" => "lon",
        "parcelPinNumber" => "parcel_pin"
      }

      data["devAppAddresses"].each do |a|
        attributes = {}
        address_attr_mapping.each do |k,v|
          attributes[v] = a[k]
        end
        addr = DevApp::Address.find_by(ref_id: attributes["ref_id"], entry: entry) || DevApp::Address.new
        addr.assign_attributes(attributes)
        addr.entry = entry
        addr.save!
      end

      # file level data isn't in the search results; so hit the other api endpoint
      url = "https://devapps-restapi.ottawa.ca/devapps/#{app_number}?authKey=#{authkey}"
      data = JSON.parse(Net::HTTP.get(URI(url)))

      desc = data.dig("applicationBriefDesc", "en")
      entry.desc = desc
      entry.planner_first_name = data["plannerFirstName"]
      entry.planner_last_name = data["plannerLastName"]
      entry.planner_phone = data["plannerPhone"]
      entry.planner_email = data["plannerEmail"]
      entry.save!

      status = data.dig("applicationStatus", "en")
      Rails.logger.info(msg: "scanning devapp", app_number: app_number, api_status: status)
      if current_status = entry.current_status
        if current_status.status == status
          Rails.logger.info(msg: "scanning devapp NO_CHANGE_NO_DB", app_number: app_number, api_status: status, db_status_id: current_status.id, db_status: current_status.status)
        else
          Rails.logger.info(msg: "scanning devapp CHANGED_UPDATING", app_number: app_number, api_status: status, db_status_id: current_status.id, db_status: current_status.status)
          announcements << { type: :status_change, from: current_status.status, to: status}
          entry.statuses << DevApp::Status.new(status: status)
        end
      else
        Rails.logger.info(msg: "scanning devapp INSERT NEW", app_number: app_number, status: status)
        entry.statuses << DevApp::Status.new(status: status)
      end

      data["devAppDocuments"].each do |doc|
        url = "http://webcast.ottawa.ca/plan/All_Image%20Referencing_#{doc["filePath"].gsub(/ /, "%20")}"
        
        attributes = {
          ref_id: doc["docReferenceId"],
          name: doc["documentName"],
          path: doc["filePath"],
          url: url
        }

        # "2022-04-19 - Application Summary - D07-12-22-0055"
        attributes[:name] = attributes[:name].gsub(/#{entry.app_number}/, '').gsub(/ - $/, '')

        begin
          uri = URI(attributes[:url])
          Net::HTTP.start(uri.host, uri.port) do |http|
            response = http.head(uri.path)
            attributes[:state] = response.code
          end
        rescue => e
          Rails.logger.warn("error on HTTP HEAD for #{attributes[:url]}; #{e.message}")
        end

        doc = DevApp::Document.find_by(ref_id: attributes[:ref_id], entry: entry) || DevApp::Document.new
        doc.assign_attributes(attributes)
        doc.entry = entry
        doc.save!
      end

      if announcements.any?
        msg = announcements.first
        message = if msg[:type] == :new_dev_app
        "DevApp: #{entry.app_number} has been born"
      else
        "DevApp: #{entry.app_number} changed its relationship status from '#{msg[:from]}' to '#{msg[:to]}'"
      end
      entry.announcements << Announcement.new(message: message)
      end

      entry
    end
  end

	def self.latest
		data = Net::HTTP.get(URI(open_data_url))
		file = Tempfile.new("devapp_xlsx")
		file.write(data.force_encoding("UTF-8"))
		file.close
		return DevApp::Scanner.new(file.path).to_a
  end

	private 

	def self.open_data_url
		"https://devapps-restapi.ottawa.ca/devapps/ExportData"
	end

	def self.authkey
    '4r5T2egSmKm5'
  end
end
