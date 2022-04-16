require "test_helper"

class MeetingScanJobTest < ActiveJob::TestCase
  test "meetings can be scanned and all fields are found" do
    Meeting.delete_all
    VCR.use_cassette("#{class_name}_#{method_name}") do
      MeetingScanJob.perform_now
    end
    assert Meeting.where(committee_id: nil).none?
    assert Meeting.where(start_time: nil).none?
    assert Meeting.where(contact_name: nil).none?
    assert Meeting.where(contact_email: nil).none?
    assert Meeting.where(contact_phone: nil).none?
    assert Meeting.where(reference_id: nil).none?
  end
end