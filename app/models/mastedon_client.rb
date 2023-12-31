require 'net/http'
require 'uri'
require 'json'

class MastedonClient
  def initialize
  end

  def self.update(message)
    true

    # base_url = "https://urbanists.social/"
    # access_token = ENV.fetch("MASTEDON_ACCESS_TOKEN")

    # uri = URI("#{base_url}/api/v1/statuses")
    # req = Net::HTTP::Post.new(uri)
    # req.set_form_data('status' => message)
    # req['Authorization'] = "Bearer #{access_token}"

    # http = Net::HTTP.new(uri.host, uri.port)
    # http.use_ssl = true
    # res = http.request(req)

    # res.body
  end
end



