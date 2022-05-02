require 'net/http'

class ConsultationScanJob < ApplicationJob
  queue_as :default

  def perform
    data = Net::HTTP.get(URI("https://engage.ottawa.ca/projects"))
    doc = Nokogiri::HTML(data)
    doc.xpath('//*[@class="project-tile__card"]').each do |d|
      href = d.xpath('a/@href').first.value
      name = d.xpath('a/div/span').first.content

      puts "https://engage.ottawa.ca#{href}"
      data = Net::HTTP.get(URI("https://engage.ottawa.ca#{href}"))

    end

    nil
  end
end
