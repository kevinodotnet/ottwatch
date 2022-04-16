require "test_helper"

class MeetingScanJobTest < ActiveJob::TestCase
  test "meetings can be scanned and all fields are found" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      MeetingScanJob.perform_now
    end
    assert Meeting.where(committee_id: nil).none?
    assert Meeting.where(start_time: nil).none?
    assert Meeting.where(start_time: 'Sat, 01 Jan 2000 14:30:00.000000000 UTC +00:00').none?
    assert Meeting.where(contact_name: nil).none?
    assert Meeting.where(contact_email: nil).none?
    assert Meeting.where(contact_phone: nil).none?
    assert Meeting.where(reference_id: nil).none?
    assert_equal [1], Meeting.all.map{|m| m.announcements.count}.uniq
    assert_equal Meeting.all.count, Meeting.all.map{|m| m.announcements.count}.sum

    announcement = Announcement.last
    assert announcement.reference.instance_of?(Meeting)
    assert announcement.message.match(/New Meeting: ............/)
    assert announcement.link_to_context
    assert announcement.link_to_reference
  end

  test "meetings inhale agenda items" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      MeetingScanJob.perform_now
    end
    assert MeetingItem.all.count > 10 # we found at least a few
    assert MeetingItem.where(title: nil).none?
    assert MeetingItem.where(title: "").none?
    assert MeetingItem.where(reference_id: nil).none?
  end
end