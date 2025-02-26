require "test_helper"

class SyndicationJobTest < ActiveJob::TestCase
  setup do
    Announcement.create!(message: "A first message", reference: DevApp::Entry.first)
  end

  test "syndication job updates last_id pointer" do
    SyndicationJob.any_instance.expects(:syndicate).times(1)
    assert_changes -> { GlobalControl.get("syndication_job_last_id") }, from: nil, to: Announcement.last.id.to_s do
      SyndicationJob.perform_now
    end
  end

  test "syndication job does not double announce" do
    SyndicationJob.any_instance.expects(:syndicate).times(1)
    SyndicationJob.perform_now
    SyndicationJob.any_instance.expects(:syndicate).times(0)
    SyndicationJob.perform_now
  end
end