require "test_helper"

class SyndicationJobTest < ActiveJob::TestCase
  setup do
    Announcement.create!(message: "A first message", reference: DevApp::Entry.first)
  end

  test "syndication job updates last_id pointer" do
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

  test "syndication job sends one tweet per announcement" do
    MastedonClient.expects(:update).times(Announcement.count)
    SyndicationJob.perform_now
  end

  test "messages include links back to the reference" do
    expected = "A first message (3020 HAWTHORNE Road) http://localhost:33000/devapp/D07-05-16-0003"
    MastedonClient.expects(:update).with(expected)
    SyndicationJob.new.syndicate(Announcement.first)
  end
end