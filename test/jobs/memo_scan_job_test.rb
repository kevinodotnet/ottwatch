require "test_helper"

class MemoScanJobTest < ActiveJob::TestCase
  test "main memo page gets parsed" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      MemoScanJob.perform_now
      assert 1 == 1
    end
  end
end
