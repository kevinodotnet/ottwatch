require "test_helper"

class DevApp::ScannerTest < ActiveSupport::TestCase

  APP_NUMBER = "D07-12-15-0017"
  setup do
    @scanner = DevApp::Scanner.new(cached_devapps_file)
  end

  test "expected number of devapps in fixture file" do
    assert_equal 2436, @scanner.to_a.count
  end

  test "structure of open data entries" do
    expected = [
      :app_number,
      :date,
      :type,
      :road_number,
      :road_name,
      :road_type,
      :status_type,
      :status,
      :file_lead,
      :description,
      :status_date,
      :ward_num,
      :ward_name
    ]
    assert @scanner.to_a.all?{|d| expected == d.keys}
  end

  test "scanning an application generates an entry; 2nd can updates previous entry" do
    entry = assert_difference -> { DevApp::Entry.all.count} do
      DevApp::Scanner.scan_application(APP_NUMBER)
    end
    entry.update!(app_type: "foo")
    assert_no_difference -> { DevApp::Entry.all.count} do
      assert_changes -> { entry.reload.app_type }, from: "foo", to: "Site Plan Control" do
        DevApp::Scanner.scan_application(APP_NUMBER)
      end
    end
  end

  test "addresses get saved" do
    assert_difference -> { DevApp::Address.all.count}, 1 do
      entry = DevApp::Scanner.scan_application(APP_NUMBER)
    end
  end

  test "files get saved" do
    assert_difference -> { DevApp::Document.all.count}, 10 do
      DevApp::Scanner.scan_application(APP_NUMBER)
    end
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
