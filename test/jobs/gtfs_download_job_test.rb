require "test_helper"
require "webmock/minitest"

class GtfsDownloadJobTest < ActiveJob::TestCase
  def setup
    @test_storage_folder = Rails.root.join("tmp", "test_gtfs_storage")
    FileUtils.mkdir_p(@test_storage_folder)
    ENV["LOCAL_STORAGE_FOLDER"] = @test_storage_folder.to_s
    
    # Create a mock zip file content with 9 GTFS files
    @mock_zip_content = create_mock_gtfs_zip
    
    # Stub the HTTP request for all tests
    WebMock.stub_request(:get, "https://oct-gtfs-emasagcnfmcgeham.z01.azurefd.net/public-access/GTFSExport.zip")
      .to_return(status: 200, body: @mock_zip_content, headers: {"Content-Type" => "application/zip"})
  end

  def teardown
    FileUtils.rm_rf(@test_storage_folder) if @test_storage_folder&.exist?
    ENV.delete("LOCAL_STORAGE_FOLDER")
    WebMock.reset!
  end

  test "downloads GTFS file successfully" do
    GtfsDownloadJob.perform_now

    # Verify file was downloaded
    current_date = Date.current
    year = current_date.strftime("%Y")
    month = current_date.strftime("%m")
    gtfs_folder = File.join(@test_storage_folder, "gtfs", year, month)
    
    assert Dir.exist?(gtfs_folder), "GTFS folder should be created"
    
    # Find the downloaded zip file
    zip_files = Dir.glob(File.join(gtfs_folder, "*.zip"))
    assert_equal 1, zip_files.length, "Should have downloaded exactly one zip file"
    
    zip_file = zip_files.first
    assert File.exist?(zip_file), "Downloaded zip file should exist"
    assert File.size(zip_file) > 0, "Downloaded zip file should not be empty"
  end

  test "downloaded file is a valid zip file" do
    GtfsDownloadJob.perform_now

    # Find the downloaded zip file
    current_date = Date.current
    year = current_date.strftime("%Y")
    month = current_date.strftime("%m")
    gtfs_folder = File.join(@test_storage_folder, "gtfs", year, month)
    zip_file = Dir.glob(File.join(gtfs_folder, "*.zip")).first

    # Test that it's a valid zip file by attempting to read it
    require 'zip'
    
    assert_nothing_raised do
      Zip::File.open(zip_file) do |zip_file_obj|
        assert zip_file_obj.entries.any?, "Zip file should contain entries"
      end
    end
  end

  test "zip contains exactly 9 GTFS files" do
    GtfsDownloadJob.perform_now

    # Find the downloaded zip file
    current_date = Date.current
    year = current_date.strftime("%Y")
    month = current_date.strftime("%m")
    gtfs_folder = File.join(@test_storage_folder, "gtfs", year, month)
    zip_file = Dir.glob(File.join(gtfs_folder, "*.zip")).first

    require 'zip'
    
    expected_files = [
      "agency.txt",
      "stops.txt", 
      "routes.txt",
      "trips.txt",
      "stop_times.txt",
      "calendar.txt",
      "calendar_dates.txt",
      "shapes.txt",
      "feed_info.txt"
    ]

    Zip::File.open(zip_file) do |zip_file_obj|
      actual_files = zip_file_obj.entries.map(&:name)
      
      assert_equal 9, actual_files.length, "Should contain exactly 9 files"
      
      expected_files.each do |expected_file|
        assert_includes actual_files, expected_file, "Should contain #{expected_file}"
      end
    end
  end

  test "handles download failure gracefully" do
    # Mock a failed HTTP response
    WebMock.stub_request(:get, "https://oct-gtfs-emasagcnfmcgeham.z01.azurefd.net/public-access/GTFSExport.zip")
      .to_return(status: 404, body: "Not Found")

    assert_raises(RuntimeError, "HTTP Error: 404") do
      GtfsDownloadJob.perform_now
    end
  end

  test "creates proper directory structure" do
    GtfsDownloadJob.perform_now

    current_date = Date.current
    year = current_date.strftime("%Y")
    month = current_date.strftime("%m")
    
    expected_path = File.join(@test_storage_folder, "gtfs", year, month)
    assert Dir.exist?(expected_path), "Should create year/month directory structure"
  end

  test "filename follows YYYYMMDD_EPOCH format" do
    GtfsDownloadJob.perform_now

    current_date = Date.current
    year = current_date.strftime("%Y")
    month = current_date.strftime("%m")
    gtfs_folder = File.join(@test_storage_folder, "gtfs", year, month)
    
    zip_files = Dir.glob(File.join(gtfs_folder, "*.zip"))
    zip_filename = File.basename(zip_files.first)
    
    # Should match pattern: YYYYMMDD_EPOCH.zip
    date_string = current_date.strftime("%Y%m%d")
    assert_match(/^#{date_string}_\d+\.zip$/, zip_filename, "Filename should follow YYYYMMDD_EPOCH.zip format")
  end

  private

  def create_mock_gtfs_zip
    # Create a temporary zip file with the 9 required GTFS files
    require 'zip'
    require 'stringio'
    
    gtfs_files = {
      "agency.txt" => "agency_id,agency_name,agency_url,agency_timezone\nOC,OC Transpo,https://www.octranspo.com,America/Toronto\n",
      "stops.txt" => "stop_id,stop_name,stop_lat,stop_lon\n1,Test Stop,45.4215,-75.6972\n",
      "routes.txt" => "route_id,route_short_name,route_long_name,route_type\n1,95,Barrhaven Centre,3\n",
      "trips.txt" => "route_id,service_id,trip_id,trip_headsign,direction_id\n1,1,1,Test Trip,0\n",
      "stop_times.txt" => "trip_id,arrival_time,departure_time,stop_id,stop_sequence\n1,08:00:00,08:00:00,1,1\n",
      "calendar.txt" => "service_id,monday,tuesday,wednesday,thursday,friday,saturday,sunday,start_date,end_date\n1,1,1,1,1,1,0,0,20250101,20251231\n",
      "calendar_dates.txt" => "service_id,date,exception_type\n1,20250101,2\n",
      "shapes.txt" => "shape_id,shape_pt_lat,shape_pt_lon,shape_pt_sequence\n1,45.4215,-75.6972,1\n",
      "feed_info.txt" => "feed_publisher_name,feed_publisher_url,feed_lang,feed_version\nOC Transpo,https://www.octranspo.com,en,1.0\n"
    }
    
    # Create zip content in memory using StringIO
    zip_buffer = StringIO.new('')
    zip_buffer.set_encoding('ASCII-8BIT')
    
    Zip::OutputStream.write_buffer(zip_buffer) do |zip|
      gtfs_files.each do |filename, content|
        zip.put_next_entry(filename)
        zip.write(content)
      end
    end
    
    zip_buffer.string
  end
end