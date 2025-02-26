require "test_helper"

# https://bsky.app/profile/ottwatch-test.bsky.social

class BlueSkyTest < ActiveSupport::TestCase
  test "#create_post posts successfully" do
    VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
      vcr_file_path = Rails.root.join("fixtures/vcr_cassettes/#{class_name}_#{method_name.gsub(/#/, '_')}.yml")
      time = (File.exist?(vcr_file_path) ? File.mtime(vcr_file_path) : Time.now).beginning_of_day
      travel_to(time) do
        post_text = "A test message for #{class_name}_#{method_name} at #{Time.now.strftime('%Y%m%d_%H%M%S')}, link https://ottwatch.ca"
        post = BlueSky.new.skeet(post_text)
        # ref = post["uri"].gsub(/.*\//, '')
        # puts "https://bsky.app/profile/ottwatch-test.bsky.social/post/#{ref}"
        assert_match /at:\/\/did:/, post["uri"]
        assert_equal "valid", post["validationStatus"]
      end
    end
  end
end