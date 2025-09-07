require 'net/http'
require 'uri'
require 'selenium-webdriver'

class MemoScanJob < ApplicationJob
  queue_as :default

  BASE_URL = "https://ottawa.ca/en/city-hall/open-transparent-and-accountable-government/public-disclosure/memoranda-issued-members-council"

  def perform(page: nil)
    if page
      scan_page(page)
    else
      scan_all_memos
    end
  end

  private


  def scan_page(page)
    response = fetch_page(page)
    doc = Nokogiri::HTML(response)

    page_title = doc.css('meta[name="dcterms.title"]').first&.[]('content')
    
    doc.css('[data-target]').each do |d|
      memo_element = d.parent.parent
      title = memo_element.css('h2').first.children.text.strip
      title = title.gsub(/^Memo: /, '')
      title = title.gsub(/ \(.*\)/, '') # remove (Date)
      issued_date = title.match(/(\d{4}-\d{2}-\d{2})/) || Date.current
      content = memo_element.css('div').first.to_s
      # binding.pry if title.match?(/Bayswater/)

      reference_id = d['data-target']
      url = "#{page}#{reference_id}"

      next if Memo.exists?(url: url)

      # "infrastructure and water services"      
      department = url.gsub(/#.*/, '').gsub(/.*\/memoranda-issued-/, '').gsub(/-/, ' ')
      department = department.split.map { |word| %w[and].include?(word) ? word : word.capitalize }.join(' ')

      memo = Memo.create!(
        title: title,
        content: content,
        url: url,
        department: department,
        issued_date: issued_date
      )
      
      # create_announcement(memo)
    end
    
  end

  def scan_all_memos
    response = fetch_page(BASE_URL)
    doc = Nokogiri::HTML(response)
    
    memo_links = doc.css("a[href^=\"#{BASE_URL.sub('https://ottawa.ca', '')}\"]")
    
    pages = memo_links.map do |link|
      # href to hash
      href = link.attributes["href"].value
      next unless href.match?(/#section/)
      "https://ottawa.ca#{href.gsub(/#.*/, '')}"
    end.compact.uniq

    pages.each do |p|
      MemoScanJob.set(wait: rand(100..500).seconds).perform_later(page: p)
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
    memo.announcements.create!(message: "New")
  end
end
