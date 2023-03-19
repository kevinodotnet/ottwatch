Bugsnag.configure do |config|
  config.api_key = ENV.fetch("BUGSNAG_KEY") if Rails.env.production?
end
