require "test_helper"

class ElectionScanJobTest < ActiveJob::TestCase
  test "elections are scanned" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      ElectionScanJob.perform_now

      election = Election.where(date: "2022-10-24").first
      assert election.candidates.count > 10 # as of test case writing
      assert election.candidates.map{|c| c.name}.uniq.count > 10
      assert election.candidates.map{|c| c.ward}.uniq.count > 4
    end
  end
end