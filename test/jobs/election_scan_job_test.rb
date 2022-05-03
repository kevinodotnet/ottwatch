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

  test "changes to candidate data are absorbed" do
    fake_email = 'exclusionary_zoning_is_bad_delete_r1_and_then_some@example.com'
    VCR.use_cassette("#{class_name}_#{method_name}") do
      ElectionScanJob.perform_now

      election = Election.where(date: "2022-10-24").first
      c = election.candidates.where.not(email: nil).first
      original_email = c.email
      c.email = fake_email
      c.save!

      assert_changes -> { c.reload.email }, from: fake_email, to: original_email do
        ElectionScanJob.perform_now
      end
    end
  end
end