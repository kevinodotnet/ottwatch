require "test_helper"

class DevApp::ScannerTest < ActiveSupport::TestCase
  APP_NUMBER = "D07-05-23-0005"

  setup do
    @scanner = DevApp::Scanner.new(cached_devapps_file)
  end

  test "expected number of devapps in fixture file" do
    assert_equal 2435, @scanner.to_a.count
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
    VCR.use_cassette("#{class_name}_#{method_name}") do
      entry = assert_difference -> { DevApp::Entry.all.count} do
        DevApp::Scanner.scan_application(APP_NUMBER)
      end
      entry.update!(app_type: "foo")
      assert_no_difference -> { DevApp::Entry.all.count} do
        assert_changes -> { entry.reload.app_type }, from: "foo", to: "Demolition Control" do
          DevApp::Scanner.scan_application(APP_NUMBER)
        end
      end
    end
  end

  test "new devapp is announced as new" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      entry = assert_difference -> { Announcement.all.count} do
        DevApp::Scanner.scan_application(APP_NUMBER)
      end
      announcement = Announcement.last
      expected = "DevApp D07-05-23-0005 (Demolition Control for 115 SPENCER) has been born: City-initiated demo control. Two-storey single detached home with detached garage. Existing structure appears to be typical stick..."
      assert_equal expected, announcement.message
    end
  end

  test "ensure app_id collisions are handled" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      assert_difference -> { DevApp::Entry.count }, 2 do
        ["D07-12-20-0058", "D07-16-19-0015"].each {|app_number| DevAppScanJob.perform_now(app_number: app_number)}
      end
      assert_no_difference -> { DevApp::Entry.count } do
        ["D07-12-20-0058", "D07-16-19-0015"].each {|app_number| DevAppScanJob.perform_now(app_number: app_number)}
      end
    end
  end

  test "devapp status changes are announced" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      entry = DevApp::Scanner.scan_application(APP_NUMBER)
      s = entry.statuses.first
      s.status = "fake"
      s.save!
      entry = assert_difference -> { Announcement.all.count} do
        DevApp::Scanner.scan_application(APP_NUMBER)
      end
      announcement = Announcement.last
      assert_equal "DevApp #{APP_NUMBER} changed status from 'fake' to 'Active'", announcement.message
    end    
  end

  test "addresses get saved" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      assert_difference -> { DevApp::Address.all.count}, 1 do
        entry = DevApp::Scanner.scan_application(APP_NUMBER)
      end
    end    
  end

  test "status gets saved" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      entry = assert_difference -> { DevApp::Status.all.count}, 1 do
        DevApp::Scanner.scan_application(APP_NUMBER)
      end
      # double-read of same status does not insert duplicate
      assert_no_difference -> { DevApp::Status.all.count} do
        DevApp::Scanner.scan_application(APP_NUMBER)
      end
      # but additional statuses are tracked when they change
      entry.reload.current_status.update!(status: "fake state")
      assert_difference -> { DevApp::Status.all.count}, 1 do
        DevApp::Scanner.scan_application(APP_NUMBER)
      end
    end    
  end

  test "files get saved" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      assert_difference -> { DevApp::Document.all.count} do
        DevApp::Scanner.scan_application(APP_NUMBER)
      end
    end    
  end

  test "file urls are encoded properly" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      entry = DevApp::Scanner.scan_application(APP_NUMBER)
      expected = "http://webcast.ottawa.ca/plan/All_Image%20Referencing_Demolition%20Control_Image%20Reference_2023-08-17%20-%20application%20-%20D07-05-23-0005.PDF"
      actual = entry.documents.map{|d| d.url}.first
      assert_equal expected, actual
    end
  end

  test "file descriptions are normalized" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      entry = DevApp::Scanner.scan_application("D07-12-22-0055")
      assert entry.documents.map{|d| d.name}.include?("2022-04-19 - Application Summary")
    end
  end

  test "planner contact data is collected" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      entry = DevApp::Scanner.scan_application(APP_NUMBER)
      assert_equal "M Masha Wakula", entry.planner_first_name
      assert_equal "Vakula", entry.planner_last_name
      assert_equal "613-580-2424 x27029", entry.planner_phone
      assert_equal "mmashawakula.vakula@ottawa.ca", entry.planner_email
    end
  end

  private

  def cached_devapps_file
    VCR.use_cassette("#{class_name}_#{method_name}") do
      filename = Rails.root.join("test/fixtures/files/dev_apps.xlsx").to_s
      return filename if File.exist?(filename)
      data = Net::HTTP.get(URI(DevApp::Scanner.open_data_url))
      File.write(filename, data.force_encoding("UTF-8"))
      filename
    end    
  end
end
