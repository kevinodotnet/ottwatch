require "test_helper"

class LobbyingScanJobTest < ActiveJob::TestCase
  focus
  test "specific dates can be scraped" do
    VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
      r = LobbyingScanJob.perform_now(date: "2022-04-12")
      binding.pry
    end
  end

  test "lobbying records are scraped over many days" do
    VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
      LobbyingScanJob.perform_now
    end
  end
end