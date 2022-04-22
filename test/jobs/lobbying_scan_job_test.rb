require "test_helper"

class LobbyingScanJobTest < ActiveJob::TestCase
  test "lobbying records are scraped" do
    VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
      LobbyingScanJob.perform_now
    end
  end
end