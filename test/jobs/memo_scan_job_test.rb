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
      m = Memo.last

      url = "https://ottawa.ca/en/city-hall/open-transparent-and-accountable-government/public-disclosure/memoranda-issued-members-council/memoranda-issued-infrastructure-and-water-services#section-3add4eea-d4ac-4f40-92ec-935cba85b16e"

      assert_equal "Bayswater Watermain", m.title
      assert_equal "Infrastructure and Water Services", m.department
      assert_equal Date.parse("2025-09-07"), m.issued_date
      assert_equal url, m.url

      a = Announcement.last
      assert a.reference_link.match(/memo\/\d+/)
      assert a.reference_context.match(/Infrastructure and Water Services - Bayswater Watermain - To: Mayor and Members of Council/)

    end
  end
end
