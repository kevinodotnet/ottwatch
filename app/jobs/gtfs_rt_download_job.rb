class GtfsRtDownloadJob < ApplicationJob
  queue_as :default

  def perform
    require 'net/http'
    require 'fileutils'
    require 'zlib'
    
    # GTFS-RT API URL
    api_url = "https://nextrip-public-api.azure-api.net/octranspo/gtfs-rt-vp/beta/v1/VehiclePositions?format=json"
    
    # Get subscription key from environment
    subscription_key = ENV["GTFS_KEY_1"]
    
    if subscription_key.blank?
      Rails.logger.error "GTFS-RT download failed: GTFS_KEY_1 environment variable not set"
      raise "GTFS_KEY_1 environment variable not set"
    end
    
    # Create date-based folder structure
    current_time = Time.current
    year = current_time.strftime("%Y")
    month = current_time.strftime("%m")
    day = current_time.strftime("%d")
    hour = current_time.strftime("%H")
    date_string = current_time.strftime("%Y%m%d")
    time_string = current_time.strftime("%H%M%S")
    timestamp = current_time.to_i
    
    storage_base = ENV["LOCAL_STORAGE_FOLDER"] || Rails.root.join("storage")
    gtfs_rt_folder = File.join(storage_base, "gtfs", year, month, day, hour)
    
    # Create directory if it doesn't exist
    FileUtils.mkdir_p(gtfs_rt_folder)
    
    # Download file
    json_filename = "vehicle_positions_#{date_string}_#{time_string}.json.gz"
    json_path = File.join(gtfs_rt_folder, json_filename)
    
    Rails.logger.info "Downloading GTFS-RT vehicle positions from #{api_url}"
    Rails.logger.info "Saving to: #{json_path}"
    
    begin
      uri = URI(api_url)
      http = Net::HTTP.new(uri.host, uri.port)
      http.use_ssl = true
      
      request = Net::HTTP::Get.new(uri)
      request['Ocp-Apim-Subscription-Key'] = subscription_key
      
      response = http.request(request)
      
      if response.code == '200'
        Zlib::GzipWriter.open(json_path) do |gz|
          gz.write(response.body)
        end
        Rails.logger.info "✓ GTFS-RT vehicle positions downloaded and compressed successfully"
        Rails.logger.info "File size: #{File.size(json_path)} bytes"
      else
        Rails.logger.error "✗ Failed to download GTFS-RT data. HTTP Status: #{response.code}"
        Rails.logger.error "Response body: #{response.body}"
        raise "HTTP Error: #{response.code}"
      end
      
    rescue => e
      Rails.logger.error "✗ Error downloading GTFS-RT data: #{e.message}"
      raise e
    end
  end
end