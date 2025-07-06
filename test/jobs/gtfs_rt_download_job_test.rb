require "test_helper"
require "webmock/minitest"

class GtfsRtDownloadJobTest < ActiveJob::TestCase
  def setup
    @test_storage_folder = Rails.root.join("tmp", "test_gtfs_rt_storage")
    FileUtils.mkdir_p(@test_storage_folder)
    ENV["LOCAL_STORAGE_FOLDER"] = @test_storage_folder.to_s
    ENV["GTFS_KEY_1"] = "test_subscription_key"
    
    # Mock JSON response data
    @mock_json_response = {
      "header" => {
        "gtfsRealtimeVersion" => "2.0",
        "incrementality" => "FULL_DATASET",
        "timestamp" => "1751773800"
      },
      "entity" => [
        {
          "id" => "1001",
          "vehicle" => {
            "trip" => {
              "tripId" => "trip_123",
              "routeId" => "95"
            },
            "position" => {
              "latitude" => 45.4215,
              "longitude" => -75.6972,
              "bearing" => 180.0,
              "speed" => 15.5
            },
            "timestamp" => "1751773800",
            "vehicle" => {
              "id" => "1001"
            }
          }
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
    current_date = Date.current
    year = current_date.strftime("%Y")
    month = current_date.strftime("%m")
    day = current_date.strftime("%d")
    gtfs_rt_folder = File.join(@test_storage_folder, "gtfs-rt", year, month, day)
    
    assert Dir.exist?(gtfs_rt_folder), "GTFS-RT folder should be created"
    
    # Find the downloaded JSON file
    json_files = Dir.glob(File.join(gtfs_rt_folder, "*.json"))
    assert_equal 1, json_files.length, "Should have downloaded exactly one JSON file"
    
    json_file = json_files.first
    assert File.exist?(json_file), "Downloaded JSON file should exist"
    assert File.size(json_file) > 0, "Downloaded JSON file should not be empty"
  end

  test "downloaded file contains valid JSON" do
    GtfsRtDownloadJob.perform_now

    # Find the downloaded JSON file
    current_date = Date.current
    year = current_date.strftime("%Y")
    month = current_date.strftime("%m")
    day = current_date.strftime("%d")
    gtfs_rt_folder = File.join(@test_storage_folder, "gtfs-rt", year, month, day)
    json_file = Dir.glob(File.join(gtfs_rt_folder, "*.json")).first

    # Test that it's valid JSON
    assert_nothing_raised do
      json_data = JSON.parse(File.read(json_file))
      assert json_data.is_a?(Hash), "JSON should be a hash"
      assert json_data.key?("header"), "JSON should contain header"
      assert json_data.key?("entity"), "JSON should contain entity array"
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

    current_date = Date.current
    year = current_date.strftime("%Y")
    month = current_date.strftime("%m")
    day = current_date.strftime("%d")
    expected_path = File.join(@test_storage_folder, "gtfs-rt", year, month, day)
    assert Dir.exist?(expected_path), "Should create year/month/day directory structure"
  end

  test "filename follows vehicle_positions_YYYYMMDD_HHMMSS format" do
    GtfsRtDownloadJob.perform_now

    current_date = Date.current
    year = current_date.strftime("%Y")
    month = current_date.strftime("%m")
    day = current_date.strftime("%d")
    gtfs_rt_folder = File.join(@test_storage_folder, "gtfs-rt", year, month, day)
    
    json_files = Dir.glob(File.join(gtfs_rt_folder, "*.json"))
    json_filename = File.basename(json_files.first)
    
    # Should match pattern: vehicle_positions_YYYYMMDD_HHMMSS.json
    date_string = current_date.strftime("%Y%m%d")
    assert_match(/^vehicle_positions_#{date_string}_\d{6}\.json$/, json_filename, "Filename should follow vehicle_positions_YYYYMMDD_HHMMSS.json format")
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

  test "saves response body correctly" do
    GtfsRtDownloadJob.perform_now

    # Find and read the downloaded file
    current_date = Date.current
    year = current_date.strftime("%Y")
    month = current_date.strftime("%m")
    day = current_date.strftime("%d")
    gtfs_rt_folder = File.join(@test_storage_folder, "gtfs-rt", year, month, day)
    json_file = Dir.glob(File.join(gtfs_rt_folder, "*.json")).first
    
    saved_content = File.read(json_file)
    assert_equal @mock_json_response, saved_content, "Saved content should match the API response"
  end
end