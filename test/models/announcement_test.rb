require "test_helper"

class AnnouncementTest < ActiveSupport::TestCase
  test "reference_context for LobbyingUndertaking format" do
    VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
      LobbyingScanJob.perform_now(date: "2022-03-23")
      a = Announcement.first
      binding.pry
    end
  end
end
