require "test_helper"

# https://bsky.app/profile/ottwatch-test.bsky.social

class BlueSkyTest < ActiveSupport::TestCase
  test "#create_post posts successfully" do
    VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
      travel_to(Time.new(2025, 2, 24, 0, 0, 0, 0)) do
        post_text = "A test message for #{class_name}_#{method_name} at #{Time.now.strftime('%Y%m%d_%H%M%S')} with a link https://ottwatch.ca and @ottwatch-test.bsky.social name reference and #testhashtag"
        post_text = "A test message for #{class_name}_#{method_name} at #{Time.now.strftime('%Y%m%d_%H%M%S')}"
        post_text = "A test message for #{class_name}_#{method_name} at #{Time.now.strftime('%Y%m%d_%H%M%S')}, link https://ottwatch.ca"
        post_text = "Hello from bskyrb, link https://github.com/"
        post = BlueSky.create_post(post_text)
        puts "#"*50
        puts post
        puts "#"*50
        # binding.pry
        assert_match /at:\/\/did:/, post["uri"]
        assert_equal "valid", post["validationStatus"]
      end
    end
  end
end