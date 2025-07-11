require "test_helper"
require "webmock/minitest"
require "zlib"

class GtfsRtDownloadJobTest < ActiveJob::TestCase
  def setup
    @test_storage_folder = Rails.root.join("tmp", "test_gtfs_rt_storage")
    FileUtils.mkdir_p(@test_storage_folder)
    ENV["LOCAL_STORAGE_FOLDER"] = @test_storage_folder.to_s
    ENV["GTFS_KEY_1"] = "test_subscription_key"
    
    # Mock JSON response data matching actual OC Transpo GTFS-RT structure
    @mock_json_response = {
      "Header" => {
        "GtfsRealtimeVersion" => "2.0",
        "HasGtfsRealtimeVersion" => true,
        "Incrementality" => 0,
        "HasIncrementality" => true,
        "Timestamp" => 1751774274,
        "HasTimestamp" => true
      },
      "Entity" => [
        {
          "Id" => "1",
          "HasId" => true,
          "IsDeleted" => false,
          "HasIsDeleted" => false,
          "TripUpdate" => nil,
          "Vehicle" => {
            "Trip" => {
              "TripId" => "23282070",
              "HasTripId" => true,
              "RouteId" => "61",
              "HasRouteId" => true,
              "DirectionId" => 0,
              "HasDirectionId" => false,
              "StartTime" => "",
              "HasStartTime" => false,
              "StartDate" => "",
              "HasStartDate" => false,
              "ScheduleRelationship" => 0,
              "HasScheduleRelationship" => true
            },
            "Vehicle" => {
              "Id" => "6474",
              "HasId" => true,
              "Label" => "",
              "HasLabel" => false,
              "LicensePlate" => "",
              "HasLicensePlate" => false,
              "WheelchairAccessible" => 0,
              "HasWheelchairAccessible" => false
            },
            "Position" => {
              "Latitude" => 45.30957,
              "HasLatitude" => true,
              "Longitude" => -75.90692,
              "HasLongitude" => true,
              "Bearing" => 52,
              "HasBearing" => true,
              "Odometer" => 0,
              "HasOdometer" => false,
              "Speed" => 0,
              "HasSpeed" => true
            },
            "CurrentStopSequence" => 0,
            "HasCurrentStopSequence" => false,
            "StopId" => "",
            "HasStopId" => false,
            "CurrentStatus" => 2,
            "HasCurrentStatus" => false,
            "Timestamp" => 1751774261,
            "HasTimestamp" => true,
            "CongestionLevel" => 0,
            "HasCongestionLevel" => false,
            "OccupancyStatus" => 0,
            "HasOccupancyStatus" => false,
            "OccupancyPercentage" => 0,
            "HasOccupancyPercentage" => false,
            "MultiCarriageDetails" => []
          },
          "Alert" => nil,
          "Shape" => nil
        }
      ]
    }.to_json
    
    # Stub the HTTP request for all tests
    WebMock.stub_request(:get, "https://nextrip-public-api.azure-api.net/octranspo/gtfs-rt-vp/beta/v1/VehiclePositions?format=json")
      .with(headers: { 'Ocp-Apim-Subscription-Key' => 'test_subscription_key' })
      .to_return(status: 200, body: @mock_json_response, headers: {"Content-Type" => "application/json"})
  end

  def teardown
    FileUtils.rm_rf(@test_storage_folder) if @test_storage_folder&.exist?
    ENV.delete("LOCAL_STORAGE_FOLDER")
    ENV.delete("GTFS_KEY_1")
    WebMock.reset!
  end

  test "downloads GTFS-RT data successfully" do
    GtfsRtDownloadJob.perform_now

    # Verify file was downloaded
    current_time = Time.current
    year = current_time.strftime("%Y")
    month = current_time.strftime("%m")
    day = current_time.strftime("%d")
    hour = current_time.strftime("%H")
    gtfs_rt_folder = File.join(@test_storage_folder, "gtfs", year, month, day, hour)
    
    assert Dir.exist?(gtfs_rt_folder), "GTFS-RT folder should be created"
    
    # Find the downloaded compressed JSON file
    json_files = Dir.glob(File.join(gtfs_rt_folder, "*.json.gz"))
    assert_equal 1, json_files.length, "Should have downloaded exactly one compressed JSON file"
    
    json_file = json_files.first
    assert File.exist?(json_file), "Downloaded compressed JSON file should exist"
    assert File.size(json_file) > 0, "Downloaded compressed JSON file should not be empty"
  end

  test "downloaded file contains valid JSON" do
    GtfsRtDownloadJob.perform_now

    # Find the downloaded compressed JSON file
    current_time = Time.current
    year = current_time.strftime("%Y")
    month = current_time.strftime("%m")
    day = current_time.strftime("%d")
    hour = current_time.strftime("%H")
    gtfs_rt_folder = File.join(@test_storage_folder, "gtfs", year, month, day, hour)
    json_file = Dir.glob(File.join(gtfs_rt_folder, "*.json.gz")).first

    # Test that it's valid compressed JSON
    assert_nothing_raised do
      json_content = Zlib::GzipReader.open(json_file) { |gz| gz.read }
      json_data = JSON.parse(json_content)
      assert json_data.is_a?(Hash), "JSON should be a hash"
      assert json_data.key?("Header"), "JSON should contain Header"
      assert json_data.key?("Entity"), "JSON should contain Entity array"
    end
  end

  test "handles missing subscription key gracefully" do
    ENV.delete("GTFS_KEY_1")

    assert_raises(RuntimeError, "GTFS_KEY_1 environment variable not set") do
      GtfsRtDownloadJob.perform_now
    end
  end

  test "handles API failure gracefully" do
    # Mock a failed API response
    WebMock.stub_request(:get, "https://nextrip-public-api.azure-api.net/octranspo/gtfs-rt-vp/beta/v1/VehiclePositions?format=json")
      .with(headers: { 'Ocp-Apim-Subscription-Key' => 'test_subscription_key' })
      .to_return(status: 401, body: "Unauthorized")

    assert_raises(RuntimeError, "HTTP Error: 401") do
      GtfsRtDownloadJob.perform_now
    end
  end

  test "creates proper directory structure" do
    GtfsRtDownloadJob.perform_now

    current_time = Time.current
    year = current_time.strftime("%Y")
    month = current_time.strftime("%m")
    day = current_time.strftime("%d")
    hour = current_time.strftime("%H")
    expected_path = File.join(@test_storage_folder, "gtfs", year, month, day, hour)
    assert Dir.exist?(expected_path), "Should create year/month/day/hour directory structure"
  end

  test "filename follows vehicle_positions_YYYYMMDD_HHMMSS.json.gz format" do
    GtfsRtDownloadJob.perform_now

    current_time = Time.current
    year = current_time.strftime("%Y")
    month = current_time.strftime("%m")
    day = current_time.strftime("%d")
    hour = current_time.strftime("%H")
    gtfs_rt_folder = File.join(@test_storage_folder, "gtfs", year, month, day, hour)
    
    json_files = Dir.glob(File.join(gtfs_rt_folder, "*.json.gz"))
    json_filename = File.basename(json_files.first)
    
    # Should match pattern: vehicle_positions_YYYYMMDD_HHMMSS.json.gz
    date_string = current_time.strftime("%Y%m%d")
    assert_match(/^vehicle_positions_#{date_string}_\d{6}\.json\.gz$/, json_filename, "Filename should follow vehicle_positions_YYYYMMDD_HHMMSS.json.gz format")
  end

  test "includes required headers in API request" do
    GtfsRtDownloadJob.perform_now

    # Verify that the request was made with the correct headers
    assert_requested :get, "https://nextrip-public-api.azure-api.net/octranspo/gtfs-rt-vp/beta/v1/VehiclePositions?format=json",
      headers: { 'Ocp-Apim-Subscription-Key' => 'test_subscription_key' }
  end

  test "handles different response status codes" do
    # Test 403 Forbidden
    WebMock.stub_request(:get, "https://nextrip-public-api.azure-api.net/octranspo/gtfs-rt-vp/beta/v1/VehiclePositions?format=json")
      .with(headers: { 'Ocp-Apim-Subscription-Key' => 'test_subscription_key' })
      .to_return(status: 403, body: "Forbidden")

    assert_raises(RuntimeError, "HTTP Error: 403") do
      GtfsRtDownloadJob.perform_now
    end
  end

  test "saves response body correctly and compresses it" do
    GtfsRtDownloadJob.perform_now

    # Find and read the downloaded compressed file
    current_time = Time.current
    year = current_time.strftime("%Y")
    month = current_time.strftime("%m")
    day = current_time.strftime("%d")
    hour = current_time.strftime("%H")
    gtfs_rt_folder = File.join(@test_storage_folder, "gtfs", year, month, day, hour)
    json_file = Dir.glob(File.join(gtfs_rt_folder, "*.json.gz")).first
    
    # Decompress and verify content
    saved_content = Zlib::GzipReader.open(json_file) { |gz| gz.read }
    assert_equal @mock_json_response, saved_content, "Decompressed content should match the API response"
  end
end