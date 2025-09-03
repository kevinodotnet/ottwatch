require "test_helper"

class MemoScanJobTest < ActiveJob::TestCase
  test "perform with no args scans main memo page and enqueues jobs" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      MemoScanJob.perform_now

      assert enqueued_jobs.select { |job| job[:job] == MemoScanJob}.count > 10

      enqueued_jobs.select { |job| job[:job] == MemoScanJob }.each do |job|
        assert job[:args].first.key?("page"), "Expected job to have page: argument"
      end
    end
  end

  test "perform with page arg reads memo page" do 
    page = "https://ottawa.ca/en/city-hall/open-transparent-and-accountable-government/public-disclosure/memoranda-issued-members-council/memoranda-issued-infrastructure-and-water-services"
    VCR.use_cassette("#{class_name}_#{method_name}") do
      assert_changes -> { Memo.count } do
        assert_changes -> { Announcement.count } do
          MemoScanJob.perform_now(page: page)
        end
      end
    end
  end
end
