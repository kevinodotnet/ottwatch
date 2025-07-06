class GtfsDownloadJob < ApplicationJob
  queue_as :default

  def perform
    require 'net/http'
    require 'fileutils'
    
    # GTFS download URL from OC Transpo
    gtfs_url = "https://oct-gtfs-emasagcnfmcgeham.z01.azurefd.net/public-access/GTFSExport.zip"
    
    # Create date-based folder structure
    current_date = Date.current
    year = current_date.strftime("%Y")
    month = current_date.strftime("%m")
    date_string = current_date.strftime("%Y%m%d")
    epoch_time = Time.current.to_i
    
    storage_base = ENV["LOCAL_STORAGE_FOLDER"] || Rails.root.join("storage")
    gtfs_folder = File.join(storage_base, "gtfs", year, month)
    
    # Create directory if it doesn't exist
    FileUtils.mkdir_p(gtfs_folder)
    
    # Download file
    zip_filename = "#{date_string}_#{epoch_time}.zip"
    zip_path = File.join(gtfs_folder, zip_filename)
    
    Rails.logger.info "Downloading GTFS files from #{gtfs_url}"
    Rails.logger.info "Saving to: #{zip_path}"
    
    uri = URI(gtfs_url)
    http = Net::HTTP.new(uri.host, uri.port)
    http.use_ssl = true
    
    request = Net::HTTP::Get.new(uri)
    response = http.request(request)
    
    if response.code == '200'
      File.open(zip_path, 'wb') do |file|
        file.write(response.body)
      end
      Rails.logger.info "✓ GTFS files downloaded successfully"
      Rails.logger.info "File size: #{File.size(zip_path)} bytes"
    else
      Rails.logger.error "✗ Failed to download GTFS files. HTTP Status: #{response.code}"
      raise "HTTP Error: #{response.code}"
    end
  end
end