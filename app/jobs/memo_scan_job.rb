require 'net/http'
require 'uri'

class MemoScanJob < ApplicationJob
  queue_as :default

  BASE_URL = "https://ottawa.ca/en/city-hall/open-transparent-and-accountable-government/public-disclosure/memoranda-issued-members-council"

  def perform
    scan_all_memos
  end

  private

  def scan_all_memos
    response = fetch_page(BASE_URL)
    doc = Nokogiri::HTML(response)
    
    memo_links = doc.css("a[href^=\"#{BASE_URL.sub('https://ottawa.ca', '')}\"]")
    
    memo_links.each do |link|
      puts link
    end
  end

  def fetch_page(url)
    uri = URI(url)
    req = Net::HTTP::Get.new(uri)
    
    req['Accept'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7'
    req['Accept-Language'] = 'en-US,en;q=0.9'
    req['Cache-Control'] = 'no-cache'
    # req['Connection'] = 'keep-alive'
    # req['Pragma'] = 'no-cache'
    req['Sec-Fetch-Dest'] = 'document'
    req['Sec-Fetch-Mode'] = 'navigate'
    req['Sec-Fetch-Site'] = 'none'
    req['Sec-Fetch-User'] = '?1'
    req['Upgrade-Insecure-Requests'] = '1'
    req['User-Agent'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'
    req['sec-ch-ua'] = '"Not;A=Brand";v="99", "Google Chrome";v="139", "Chromium";v="139"'
    req['sec-ch-ua-mobile'] = '?0'
    req['sec-ch-ua-platform'] = '"macOS"'
    
    http = Net::HTTP.new(uri.host, uri.port)
    http.use_ssl = true
    
    response = http.request(req)
    response.body
  end


  def create_announcement(memo)
    memo.announcements.create!(
      message: "New Memo: #{memo.title}"
    )
  end
end
