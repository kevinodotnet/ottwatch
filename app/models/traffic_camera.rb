class TrafficCamera < ApplicationRecord
    def self.cameras
        @cameras ||= begin
            data = Net::HTTP.get(URI("https://traffic.ottawa.ca/service/camera"))
            JSON.parse(data)["cameras"].map(&:with_indifferent_access)
        end
    end

    def self.scrape_all
        cameras.each do |camera|
            tc = TrafficCamera.find_or_create_by!(reference_id: camera[:id])
            tc.update!(
                lat: camera[:latitude],
                lon: camera[:longitude],
                name: camera[:name],
                camera_owner: camera[:cameraOwner],
                camera_number: camera[:camera_number]
            )
        end
    end

    def self.capture_folder
        dir = ENV["LOCAL_STORAGE_FOLDER"] || Rails.root.join("tmp").to_s
        "#{dir}/camera"
    end

    def capture_image
        time_now = (Time.now.to_f * 1000).to_i
        url = "https://traffic.ottawa.ca/camera?id=#{camera_number}&timems=#{time_now}"
        response = Net::HTTP.get(URI(url))
        camera_path = "#{self.class.capture_folder}/#{id}"
        capture_filename = "#{camera_path}/#{id}_#{time_now}.jpg"
        FileUtils.mkdir_p(camera_path)
        File.binwrite(capture_filename, response)
        response
    end

    def capture_jpg(time_ms)
        c = captures.detect { |capture| capture[:time_ms] == time_ms }
        return unless c
        File.read(c[:file])
    end

    def captures 
        Dir.glob(File.join(self.class.capture_folder, id.to_s, '**', '*')).select { |f| File.file?(f) }.sort.map do |file|
            time_ms = file.scan(/.*#{id}\/#{id}_(\d+)\.jpg/).first.first.to_i
            time = time_ms / 1000
            {
                camera: self,
                time_ms: time_ms,
                time: time,
                file: file
            }
        end
    end
end