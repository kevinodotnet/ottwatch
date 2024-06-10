require 'csv'

namespace :ottwatch do
  desc "Seed data"
  task seed: :environment do
    ParcelScanner.perform_now
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
