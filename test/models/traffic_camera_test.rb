require 'test_helper'

class TrafficCameraTest < ActiveSupport::TestCase
    setup do
      @camera = TrafficCamera.create!(reference_id: 37, camera_number: 8, name: "Belfast & St. Laurent", camera_owner: "CITY", lat: 45.411858, lon: -75.630376)
      system("sqlite3 #{TrafficCamera::CAPTURE_FOLDER}/camera_archive.sqlar -Ac")
    end


  test "cameras are scraped correctly" do
    VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
      cameras =TrafficCamera.cameras
      TrafficCamera.scrape_all
      assert cameras.count > 300
      assert_equal cameras.count, TrafficCamera.count
      end
  end

  test "cameras are not duplicated when scraped again" do
    VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
      TrafficCamera.scrape_all
      assert_no_difference -> { TrafficCamera.count } do
        TrafficCamera.scrape_all
      end
    end
  end

    test "traffic camera images are captured correctly" do
        VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
            assert_difference -> { captured_files.count } do
                @camera.capture_image
            end
        end
    end

    test "#captures returns the correct captures" do
        VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
            assert_difference -> { @camera.captures.count } do
                @camera.capture_image
            end
        end
        capture = @camera.captures.last
    end

    test "#captures inserts a row in the SQLite archive" do
        VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
          image_data = @camera.capture_image
          SQLite3::Database.open(TrafficCamera::SQLITE_ARCHIVE) do |db|
            assert_equal db.execute(" select count(1) from sqlar ").first.first, 1
          end
          db_data = @camera.image_from_sqlite_archive(image_data[:time])
          assert_equal image_data[:image], db_data
        end
    end

    private

    def captured_files
        Dir.glob(File.join(TrafficCamera::CAPTURE_FOLDER, '**', '*')).select { |f| File.file?(f) }.sort
    end
end
