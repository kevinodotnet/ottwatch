class TwitterClient
  def initialize
  end

  def self.update(message)
    client.update(message)
  end

  def self.client
    if Rails.env.production?
      Twitter::REST::Client.new do |config|
        config.consumer_key = ENV.fetch("TWITTER_POST_CONSUMER_KEY")
        config.consumer_secret = ENV.fetch("TWITTER_POST_CONSUMER_SECRET")
        config.access_token = ENV.fetch("TWITTER_POST_ACCESS_TOKEN")
        config.access_token_secret = ENV.fetch("TWITTER_POST_TOKEN_SECRET")
      end
    else
      MockTwitterClient.new
    end
  end

  private

  class MockTwitterClient
    def update(message)
      # puts "#### SENDING TWEET: #{message}"
      # noop
    end
  end
end
