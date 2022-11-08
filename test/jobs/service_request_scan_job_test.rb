require "test_helper"
class ServiceRequestScanJobTest < ActiveJob::TestCase
  test "open311 gem and configuration works" do
    VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
      service_list = Open311.service_list
      expected = ["description", "group", "keywords", "metadata", "service_code", "service_name", "type"]
      assert_equal expected, service_list.map{|s| s.keys}.flatten.uniq.sort

      stopped_in_bike_lane = service_list.detect{|s| s["service_name"] == "Parked or stopped in a bike lane"}
      assert_equal stopped_in_bike_lane["service_code"], "2000040-1"
    end
  end

  test "#perform can scan a specific date" do
    date = "2021-08-01".to_date
    VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
      ServiceRequestScanJob.perform_now(date: date)
    end
  end

  # TODO: Open311.service_requests("service_request_id" => "202202080046")

  test "#perform saves 1000 service requests" do
    VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
      assert_changes -> { ServiceRequest.count }, from: 0, to: 1000 do
        ServiceRequestScanJob.perform_now
      end
      assert_no_changes -> { ServiceRequest.count } do
        ServiceRequestScanJob.perform_now
      end
    end
  end
end