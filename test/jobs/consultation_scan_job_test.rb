require "test_helper"

class ConsultationScanJobTest < ActiveJob::TestCase
  test "consultations are scraped" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      ConsultationScanJob.perform_now
    end
  end
end