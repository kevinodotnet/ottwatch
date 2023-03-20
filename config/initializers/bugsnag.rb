Bugsnag.configure do |config|
  if Rails.env.production?
    config.api_key = ENV.fetch("BUGSNAG_KEY") 
    config.app_version = JSON.parse(File.read("version.json"))["object"]["sha"]
  end
end
