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
		data = d["devApps"].first

		attributes = {}
		attributes[:app_id] = data["devAppId"]
		attributes[:app_number] = data["applicationNumber"]
		attributes[:app_type] = data.dig("applicationType","en")
		# these need to be in a join/array table to track state over time
		# attributes[:status] = data.dig("applicationStatus", "en")
		# attributes[:statusDetail] = data.dig("objectStatus", "objectCurrentStatus", "en")
		# attributes[:foo] = data.dig("objectStatus", "objectCurrentStatusDateYMD")
		entry = DevApp::Entry.find_by(app_id: data["devAppId"]) || DevApp::Entry.new
		entry.assign_attributes(attributes)
		entry.save!
		entry
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
