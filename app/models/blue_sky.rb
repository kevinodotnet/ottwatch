require 'bskyrb'

# https://github.com/ShreyanJain9/bskyrb

class BlueSky
  DEVTEST_USERNAME = "ottwatch-test.bsky.social"

  def self.username
    return DEVTEST_USERNAME if Rails.env.development? || Rails.env.test?
    ENV["BLUE_SKY_USERNAME"] # Production
  end

  def self.password
    # BLUE_SKY_PASSWORD=<PASSWORD_HERE> bin/rails t test/models/blue_sky_test.rb
    raise "BLUE_SKY_PASSWORD not set" if ENV["BLUE_SKY_PASSWORD"].nil?
    ENV["BLUE_SKY_PASSWORD"] 
  end

  # def self.pds_url
  #   'https://bsky.social'
  # end

  def self.credentials
    ATProto::Credentials.new(username, password)
  end

  def self.session
    ATProto::Session.new(credentials)
  end

  def self.client
    Bskyrb::Client.new(session)
  end

  def self.create_post(text)
    client.create_post_or_reply(text)
  end
end