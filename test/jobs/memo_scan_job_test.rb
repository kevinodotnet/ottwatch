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
end
