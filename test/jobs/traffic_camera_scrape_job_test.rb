require 'test_helper'

class TrafficCameraScrapeJobTest < ActiveJob::TestCase
  test "job calls TrafficCamera.scrape_all" do
    TrafficCamera.expects(:scrape_all).once
    TrafficCameraScrapeJob.perform_now
  end
end

