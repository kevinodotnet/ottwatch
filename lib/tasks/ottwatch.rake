require 'csv'

namespace :ottwatch do
  desc "Seed data"
  task seed: :environment do
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
end
