require "test_helper"

class SyndicationJobTest < ActiveJob::TestCase
  test "syndication job updates last_id pointer" do
    assert_changes -> { GlobalControl.get("syndication_job_last_id") }, from: nil, to: Announcement.last.id.to_s do
      SyndicationJob.perform_now
    end
  end

  test "syndication job does not double announce" do
    SyndicationJob.any_instance.expects(:syndicate).times(2)
    SyndicationJob.perform_now
    SyndicationJob.any_instance.expects(:syndicate).times(0)
    SyndicationJob.perform_now
  end

  test "syndication job sends one tweet per announcement" do
    TwitterClient.expects(:update).times(Announcement.count)
    SyndicationJob.perform_now
  end
end