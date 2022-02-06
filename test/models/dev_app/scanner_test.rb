require "test_helper"

class DevApp::ScannerTest < ActiveSupport::TestCase
  setup do
    @scanner = DevApp::Scanner.new(cached_devapps_file)
  end

  test "expected number of devapps in fixture file" do
    assert_equal 2436, @scanner.to_a.count
  end

  test "structure of open data entries" do
    expected = [
      "Application Number",
      "Application Date",
      "Application Type",
      "Address Number",
      "Road Name",
      "Road Type",
      "Object Status Type",
      "Application Status",
      "File Lead",
      "Brief Description",
      "Object Status Date",
      "Ward #",
      "Ward",
    ]
    expected = [:number, :date, :type, :road_number, :road_name, :road_type, :status_type, :status, :file_lead, :description, :status_date, :ward_num, :ward_name]
    assert @scanner.to_a.all?{|d| expected == d.keys}
  end

  private

  def cached_devapps_file
    filename = Rails.root.join("test/fixtures/files/dev_apps.xlsx").to_s
    return filename if File.exists?(filename)
		data = Net::HTTP.get(URI(DevApp::Scanner.open_data_url))
		File.write(filename, data.force_encoding("UTF-8"))
    filename
  end
end
