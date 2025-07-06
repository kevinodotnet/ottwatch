require "test_helper"
require "webmock/minitest"

VCR.turn_off!
WebMock.enable!

class GtfsRtTripUpdatesJobTest < ActiveJob::TestCase
  def setup
    @test_storage_folder = Rails.root.join("tmp", "test_gtfs_rt_trip_updates_storage")
    FileUtils.mkdir_p(@test_storage_folder)
    ENV["LOCAL_STORAGE_FOLDER"] = @test_storage_folder.to_s
    ENV["GTFS_KEY_1"] = "test_subscription_key"
    
    # Mock JSON response data for TripUpdates (similar structure to VehiclePositions but with trip update data)
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
          "TripUpdate" => {
            "Trip" => {
              "TripId" => "23282070",
              "HasTripId" => true,
              "RouteId" => "61",
              "HasRouteId" => true,
              "DirectionId" => 0,
              "HasDirectionId" => false,
              "StartTime" => "06:00:00",
              "HasStartTime" => true,
              "StartDate" => "20250706",
              "HasStartDate" => true,
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
            "StopTimeUpdate" => [
              {
                "StopSequence" => 1,
                "HasStopSequence" => true,
                "StopId" => "AA123",
                "HasStopId" => true,
                "Arrival" => {
                  "Delay" => 120,
                  "HasDelay" => true,
                  "Time" => 1751774400,
                  "HasTime" => true,
                  "Uncertainty" => 0,
                  "HasUncertainty" => false
                },
                "Departure" => {
                  "Delay" => 120,
                  "HasDelay" => true,
                  "Time" => 1751774430,
                  "HasTime" => true,
                  "Uncertainty" => 0,
                  "HasUncertainty" => false
                },
                "ScheduleRelationship" => 0,
                "HasScheduleRelationship" => true
              }
            ],
            "Timestamp" => 1751774261,
            "HasTimestamp" => true,
            "Delay" => 120,
            "HasDelay" => true
          },
          "Vehicle" => nil,
          "Alert" => nil,
          "Shape" => nil
        }
      ]
    }.to_json
    
    # Stub the HTTP request for all tests
    WebMock.stub_request(:get, "https://nextrip-public-api.azure-api.net/octranspo/gtfs-rt-tp/beta/v1/TripUpdates?format=json")
      .with(headers: { 'Ocp-Apim-Subscription-Key' => 'test_subscription_key' })
      .to_return(status: 200, body: @mock_json_response, headers: {"Content-Type" => "application/json"})
  end

  def teardown
    FileUtils.rm_rf(@test_storage_folder) if @test_storage_folder&.exist?
    ENV.delete("LOCAL_STORAGE_FOLDER")
    ENV.delete("GTFS_KEY_1")
    WebMock.reset!
  end

  def self.teardown
    VCR.turn_on!
  end

  test "downloads GTFS-RT trip updates successfully" do
    GtfsRtTripUpdatesJob.perform_now

    # Verify file was downloaded
    current_time = Time.current
    year = current_time.strftime("%Y")
    month = current_time.strftime("%m")
    day = current_time.strftime("%d")
    hour = current_time.strftime("%H")
    gtfs_rt_folder = File.join(@test_storage_folder, "gtfs", year, month, day, hour)
    
    assert Dir.exist?(gtfs_rt_folder), "GTFS-RT folder should be created"
    
    # Find the downloaded JSON file
    json_files = Dir.glob(File.join(gtfs_rt_folder, "trip_updates_*.json"))
    assert_equal 1, json_files.length, "Should have downloaded exactly one trip updates JSON file"
    
    json_file = json_files.first
    assert File.exist?(json_file), "Downloaded trip updates JSON file should exist"
    assert File.size(json_file) > 0, "Downloaded trip updates JSON file should not be empty"
  end

  test "downloaded file contains valid JSON" do
    GtfsRtTripUpdatesJob.perform_now

    # Find the downloaded JSON file
    current_time = Time.current
    year = current_time.strftime("%Y")
    month = current_time.strftime("%m")
    day = current_time.strftime("%d")
    hour = current_time.strftime("%H")
    gtfs_rt_folder = File.join(@test_storage_folder, "gtfs", year, month, day, hour)
    json_file = Dir.glob(File.join(gtfs_rt_folder, "trip_updates_*.json")).first

    # Test that it's valid JSON
    assert_nothing_raised do
      json_data = JSON.parse(File.read(json_file))
      assert json_data.is_a?(Hash), "JSON should be a hash"
      assert json_data.key?("Header"), "JSON should contain Header"
      assert json_data.key?("Entity"), "JSON should contain Entity array"
    end
  end

  test "handles missing subscription key gracefully" do
    ENV.delete("GTFS_KEY_1")

    assert_raises(RuntimeError, "GTFS_KEY_1 environment variable not set") do
      GtfsRtTripUpdatesJob.perform_now
    end
  end

  test "handles API failure gracefully" do
    # Mock a failed API response
    WebMock.stub_request(:get, "https://nextrip-public-api.azure-api.net/octranspo/gtfs-rt-tp/beta/v1/TripUpdates?format=json")
      .with(headers: { 'Ocp-Apim-Subscription-Key' => 'test_subscription_key' })
      .to_return(status: 404, body: "Not Found")

    assert_raises(RuntimeError, "HTTP Error: 404") do
      GtfsRtTripUpdatesJob.perform_now
    end
  end

  test "creates proper directory structure" do
    GtfsRtTripUpdatesJob.perform_now

    current_time = Time.current
    year = current_time.strftime("%Y")
    month = current_time.strftime("%m")
    day = current_time.strftime("%d")
    hour = current_time.strftime("%H")
    expected_path = File.join(@test_storage_folder, "gtfs", year, month, day, hour)
    assert Dir.exist?(expected_path), "Should create year/month/day/hour directory structure"
  end

  test "filename follows trip_updates_YYYYMMDD_HHMMSS format" do
    GtfsRtTripUpdatesJob.perform_now

    current_time = Time.current
    year = current_time.strftime("%Y")
    month = current_time.strftime("%m")
    day = current_time.strftime("%d")
    hour = current_time.strftime("%H")
    gtfs_rt_folder = File.join(@test_storage_folder, "gtfs", year, month, day, hour)
    
    json_files = Dir.glob(File.join(gtfs_rt_folder, "trip_updates_*.json"))
    json_filename = File.basename(json_files.first)
    
    # Should match pattern: trip_updates_YYYYMMDD_HHMMSS.json
    date_string = current_time.strftime("%Y%m%d")
    assert_match(/^trip_updates_#{date_string}_\d{6}\.json$/, json_filename, "Filename should follow trip_updates_YYYYMMDD_HHMMSS.json format")
  end

  test "includes required headers in API request" do
    GtfsRtTripUpdatesJob.perform_now

    # Verify that the request was made with the correct headers
    assert_requested :get, "https://nextrip-public-api.azure-api.net/octranspo/gtfs-rt-tp/beta/v1/TripUpdates?format=json",
      headers: { 'Ocp-Apim-Subscription-Key' => 'test_subscription_key' }
  end

  test "saves response body correctly" do
    GtfsRtTripUpdatesJob.perform_now

    # Find and read the downloaded file
    current_time = Time.current
    year = current_time.strftime("%Y")
    month = current_time.strftime("%m")
    day = current_time.strftime("%d")
    hour = current_time.strftime("%H")
    gtfs_rt_folder = File.join(@test_storage_folder, "gtfs", year, month, day, hour)
    json_file = Dir.glob(File.join(gtfs_rt_folder, "trip_updates_*.json")).first
    
    saved_content = File.read(json_file)
    assert_equal @mock_json_response, saved_content, "Saved content should match the API response"
  end

  test "handles different response status codes" do
    # Test 401 Unauthorized
    WebMock.stub_request(:get, "https://nextrip-public-api.azure-api.net/octranspo/gtfs-rt-tp/beta/v1/TripUpdates?format=json")
      .with(headers: { 'Ocp-Apim-Subscription-Key' => 'test_subscription_key' })
      .to_return(status: 401, body: "Unauthorized")

    assert_raises(RuntimeError, "HTTP Error: 401") do
      GtfsRtTripUpdatesJob.perform_now
    end
  end
end