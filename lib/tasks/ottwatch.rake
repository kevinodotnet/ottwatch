require 'csv'

namespace :ottwatch do
  desc "Seed data"
  task seed: :environment do

    Nokogiri::HTML(Net::HTTP.get(URI("https://pub-ottawa.escribemeetings.com/"))).xpath('//div[@class="calendar-item"]').each do |m|
      md = Nokogiri::HTML(m.to_s)

      title = md.xpath('//div[@class="meeting-title"]/h3/span').children.to_s
      meeting_time = md.xpath('//div[@class="meeting-date"]').first.children.to_s
      meeting_time = "#{meeting_time} EST".to_time
      reference_guid = md.xpath('//a').map do |a|
        a.attributes.map do |k,v|
          next unless k == 'href'
          next unless v.value.match(/Meeting.aspx.*/)
          next unless a.children.to_s.match(/HTML/)
          v.value.match(/Meeting.aspx\?Id=(?<id>[^&]*)/)["id"]
        end
      end.flatten.compact.first

      next unless reference_guid

      attrs = {
        title: title,
        reference_guid: reference_guid,
        meeting_time: meeting_time
      }
      MeetingScanJob.perform_now(attrs: attrs)
    end

    puts "DevApp: (getting latest)"
    dev_apps = Set.new
    latest = DevApp::Scanner.latest
    latest.sort_by{|d| d[:status_date].to_date}.reverse.first(10).map{|d| d[:app_number]}.each do |a|
      if dev_apps.include?(a)
        puts "DevApp: #{a} (dup)"
        next
      end
      puts "DevApp: #{a}"
      dev_apps << a
      DevApp::Scanner.scan_application(a)
    end

    puts "ConsultationScanner"
    ConsultationScanner.perform_now

    puts "ZoningScanner"
    ZoningScanner.perform_now(allow_again: false)

    puts "ParcelScanner"
    ParcelScanner.perform_now(allow_again: false)

    meeting_type = "City Council"
    puts "MeetingScanJob: #{meeting_type}"
    meetings = MeetingScanJob.scan_past_meetings(meeting_type)
    meetings.sort{|d| d[:meeting_time]}.last(10).each do |m|
      puts "MeetingScanJob: #{m}"
      MeetingScanJob.new.send(:scan_meeting, meetings.sample)
    end

    (-10..0).each do |i|
      date = Date.today + i.days
      puts "LobbyingScanJob: #{date}"
      LobbyingScanJob.perform_now(date: date)
    end
  end

  desc "Speed data"
  task speed: :environment do
    url = "https://opendata.arcgis.com/api/v3/datasets/eb75a3d3c8d34e67a923e886ae006f90_0/downloads/data?format=csv&spatialRefId=4326"
    data = Net::HTTP.get(URI(url))
    data = data.force_encoding("UTF-8").sub("\xEF\xBB\xBF", "")
    csv = CSV.new(data, headers: true)

    row = csv.first.to_h
    dates = row.keys.map{|k| k.match(/20\d\d_\d\d_\d\d/)&.to_s}.compact.uniq

    static_cols = [
      "Location",
      "Camera_Install_Year",
      "Latitude",
      "Longitude",
      "X",
      "Y",
      "ObjectId",
      "ObjectId2"
    ]

    dynamic_cols = [
      "AvgSpeed",
      "Pct85th",
      "PctCompliance",
      "PctHighEndSpeeders",
    ]

    headers = ["Date"] + static_cols + dynamic_cols

    puts CSV.generate(write_headers: true, headers: headers) do |out_csv|
      csv.each do |r|
        r = r.to_h
        dates.each do |d|
          o = r.slice(*static_cols)
          o["Date"] = d.gsub(/_/, '-')
          dynamic_cols.each do |k|
            o[k] = r["#{k}_#{d}"]
          end
          out_csv << o
        end
      end
    end
  end

  desc "Move TrafficCamera image files to correct directory structure (reads filenames from STDIN)"
  task :move_camera_file => :environment do
    puts "Reading filenames from STDIN (one per line)..."
    
    STDIN.each_line do |line|
      filename = line.strip
      next if filename.empty?
      
      unless File.exist?(filename)
        puts "Error: File '#{filename}' does not exist"
        next
      end
      
      basename = File.basename(filename, '.jpg')
      
      # Parse ID_TIMESTAMP format
      match = basename.match(/^(\d+)_(\d+)$/)
      unless match
        puts "Error: Filename '#{basename}' does not match expected format ID_TIMESTAMP"
        next
      end
      
      camera_id = match[1]
      timestamp = match[2].to_i
      
      # Convert timestamp to date
      begin
        date = Time.at(timestamp / 1000.0)
      rescue ArgumentError
        puts "Error: Invalid timestamp '#{timestamp}'"
        next
      end
      
      # Build target directory path
      capture_folder = ENV["LOCAL_STORAGE_FOLDER"] || Rails.root.join("tmp")
      date_path = date.strftime("%Y/%m/%d")
      target_dir = File.join(capture_folder, "camera", date_path, camera_id)
      target_file = File.join(target_dir, "#{basename}.jpg")
      
      # Create directory if it doesn't exist
      FileUtils.mkdir_p(target_dir)
      
      # Move the file
      if File.exist?(target_file)
        puts "Warning: Target file '#{target_file}' already exists, skipping"
        next
      end
      
      FileUtils.mv(filename, target_file)
      puts "Moved '#{filename}' to '#{target_file}'"
    end
  end
end
