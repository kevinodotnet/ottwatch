#
# With thanks to https://t27duck.com/posts/17-a-bluesky-at-proto-api-example-in-ruby
#
class BlueSky
  BASE_URL = "https://bsky.social/xrpc"
  TOKEN_CACHE_KEY = :bluesky_token_data

  def self.username
    if Rails.env.development? || Rails.env.test?
      "ottwatch-test.bsky.social"
    else
      "ottwatch.bsky.social"
    end
  end

  def self.password
    raise "BLUE_SKY_PASSWORD not set" if ENV["BLUE_SKY_PASSWORD"].nil?
    ENV["BLUE_SKY_PASSWORD"]
  end

  def initialize
    @username = BlueSky.username
    @password = BlueSky.password
    token_data = Rails.cache.read(TOKEN_CACHE_KEY)
    process_tokens(token_data) if token_data.present?
  end

  def skeet(message)
    verify_tokens
    facets = link_facets(message)
    facets += tag_facets(message)
    body = {
      repo: @user_did, collection: "app.bsky.feed.post",
      record: { text: message, createdAt: Time.now.iso8601, langs: ["en"], facets: facets }
    }
    post_request("#{BASE_URL}/com.atproto.repo.createRecord", body: body)
  end

  private

  def link_facets(message)
    [].tap do |facets|
      matches = []
      message.scan(URI::RFC2396_PARSER.make_regexp(["http", "https"])) { matches << Regexp.last_match }
      matches.each do |match|
        start, stop = match.byteoffset(0)
        facets << {
          "index" => { "byteStart" => start, "byteEnd" => stop },
          "features" => [{ "uri" => match[0], "$type" => "app.bsky.richtext.facet#link" }]
        }
      end
    end
  end

  def tag_facets(message)
    [].tap do |facets|
      matches = []
      message.scan(/(^|[^\w])(#[\w\-]+)/) { matches << Regexp.last_match }
      matches.each do |match|
        start, stop = match.byteoffset(2)
        facets << {
          "index" => { "byteStart" => start, "byteEnd" => stop },
          "features" => [{ "tag" => match[2].delete_prefix("#"), "$type" => "app.bsky.richtext.facet#tag" }]
        }
      end
    end
  end

  def post_request(url, body: {}, auth_token: true, content_type: "application/json")
    uri = URI.parse(url)
    http = Net::HTTP.new(uri.host, uri.port)
    http.use_ssl = (uri.scheme == "https")
    http.open_timeout = 4
    http.read_timeout = 4
    http.write_timeout = 4
    request = Net::HTTP::Post.new(uri.request_uri)
    request["content-type"] = content_type

    if auth_token
      token = auth_token.is_a?(String) ? auth_token : @token
      request["Authorization"] = "Bearer #{token}"
    end
    request.body = body.is_a?(Hash) ? body.to_json : body if body.present?
    response = http.request(request)
    raise "#{response.code} response - #{response.body}" unless response.code.to_s.start_with?("2")

    response.content_type == "application/json" ? JSON.parse(response.body) : response.body
  end

  def generate_tokens
    body = { identifier: @username, password: @password }
    response_body = post_request("#{BASE_URL}/com.atproto.server.createSession", body: body, auth_token: false)

    process_tokens(response_body)
    store_token_data(response_body)
  end

  def perform_token_refresh
    response_body = post_request("#{BASE_URL}/com.atproto.server.refreshSession", auth_token: @renewal_token)

    process_tokens(response_body)
    store_token_data(response_body)
  end

  def verify_tokens
    if @token.nil?
      generate_tokens
    elsif @token_expires_at < Time.now.utc + 60
      perform_token_refresh
    end
  end

  def process_tokens(response_body)
    @token = response_body["accessJwt"]
    @renewal_token = response_body["refreshJwt"]
    @user_did = response_body["did"]
    @token_expires_at = Time.at(JSON.parse(Base64.decode64(response_body["accessJwt"].split(".")[1]))["exp"]).utc
  end

  def store_token_data(data)
    Rails.cache.write(TOKEN_CACHE_KEY, data)
  end
end