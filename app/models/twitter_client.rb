class TwitterClient
  def initialize
    @client = Twitter::REST::Client.new do |config|
      config.consumer_key = ENV.fetch("TWITTER_POST_CONSUMER_KEY")
      config.consumer_secret = ENV.fetch("TWITTER_POST_CONSUMER_SECRET")
      config.access_token = ENV.fetch("TWITTER_POST_ACCESS_TOKEN")
      config.access_token_secret = ENV.fetch("TWITTER_POST_TOKEN_SECRET")
    end
  end

  def client
    @client
  end
end
