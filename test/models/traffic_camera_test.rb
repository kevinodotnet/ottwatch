require 'test_helper'

class TrafficCameraTest < ActiveSupport::TestCase
  # include ActiveSupport::Testing::TimeHelpers

    setup do
    @camera = TrafficCamera.create!(reference_id: 37, camera_number: 8, name: "Belfast & St. Laurent", camera_owner: "CITY", lat: 45.411858, lon: -75.630376)
    end

    focus
    test "camera path has yyyymmdd format" do
      ActiveSupport::Testing::TimeHelpers.travel_to(Time.zone.local(2021, 1, 1, 12, 0, 0)) do
        path = TrafficCamera.new(id: 12).capture_image_path
        binding.pry
        end
      
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

    private

    def captured_files
        capture_folder = TrafficCamera.capture_folder
        Dir.glob(File.join(capture_folder, '**', '*')).select { |f| File.file?(f) }.sort
    end
end
