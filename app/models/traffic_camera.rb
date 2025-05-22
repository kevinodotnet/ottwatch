class TrafficCamera < ApplicationRecord
    CAPTURE_FOLDER = (ENV["LOCAL_STORAGE_FOLDER"] || Rails.root.join("tmp").to_s) + "/camera"
    SQLITE_ARCHIVE = "#{CAPTURE_FOLDER}/camera_archive.sqlar"

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

    def current_image_url
        time_now = (Time.now.to_f * 1000).to_i
        "https://traffic.ottawa.ca/camera?id=#{camera_number}&timems=#{time_now}"
    end

    def capture_image
        time_now = (Time.now.to_f * 1000).to_i
        response = Net::HTTP.get(URI(current_image_url))
        camera_path = "#{CAPTURE_FOLDER}/#{id}"
        capture_filename = "#{camera_path}/#{id}_#{time_now}.jpg"
        FileUtils.mkdir_p(camera_path)
        File.binwrite(capture_filename, response)
        save_image_to_sqlite_archive(time: time_now, image: response)
        {
            time: time_now,
            camera_id: id,
            filename: capture_filename,
            image: response
        }
    end

    def save_image_to_sqlite_archive(time:, image:)
        filename = "#{id}_#{time}.jpg"
        file_data = nil # File.binread(filename)
        file_stat = nil # File.stat(filename)

        SQLite3::Database.open(SQLITE_ARCHIVE) do |db|
            db.execute(
                "INSERT INTO sqlar (name, mode, mtime, sz, data) VALUES (?, ?, ?, ?, ?)",
                [filename, file_stat&.mode, file_stat&.mtime.to_i, image.size, image]
            )
        end
    end

    def image_from_sqlite_archive(time_ms)
        SQLite3::Database.open(SQLITE_ARCHIVE) do |db|
            db.execute("SELECT data FROM sqlar WHERE name = ?", ["#{id}_#{time_ms}.jpg"]) do |row|
                return row.first
            end
        end
        nil
    end

    def capture_jpg(time_ms)
        c = captures.detect { |capture| capture[:time_ms] == time_ms }
        return unless c
        File.read(c[:file])
    end

    def captures 
        Dir.glob(File.join(CAPTURE_FOLDER, id.to_s, '**', '*')).select { |f| File.file?(f) }.sort.map do |file|
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
