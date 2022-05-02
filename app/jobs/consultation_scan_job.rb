require 'net/http'

class ConsultationScanJob < ApplicationJob
  queue_as :default

  def perform
    data = Net::HTTP.get(URI("https://engage.ottawa.ca/projects"))
    doc = Nokogiri::HTML(data)
    doc.xpath('//*[@class="project-tile__card"]').each do |d|
      href = d.xpath('a/@href').first.value
      name = d.xpath('a/div/span').first.content
      scrape_consultation(name, href)
    end

    nil
  end

  def scrape_consultation(name, href)
    data = Net::HTTP.get(URI("https://engage.ottawa.ca#{href}"))
    doc = Nokogiri::HTML(data)

    description = doc.xpath('//*[@class="truncated-description"]').first.xpath('p').map{|p| p.content}.join("<br/>\n\n")
  end
end
