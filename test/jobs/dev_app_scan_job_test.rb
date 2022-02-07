require "test_helper"

class DevAppScanJobTest < ActiveJob::TestCase
  test "with no arguments, job runs in enqueuing mode" do
    DevApp::Scanner.expects(:latest).returns([{app_number: "D07-12-15-0115"}])
    #DevApp::Scanner.expects(:scan_application).with("D07-12-15-0115")
    assert_enqueued_with(job: DevAppScanJob) do
      DevAppScanJob.perform_now
    end
  end

  test "with specified app_number, job deep processes just that application" do
    DevApp::Scanner.expects(:latest).never
    DevApp::Scanner.expects(:scan_application).with("D07-12-15-0115")
    DevAppScanJob.perform_now(app_number: "D07-12-15-0115")
  end
end
