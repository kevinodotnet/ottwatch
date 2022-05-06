require 'net/http'
class ElectionScanJob < ApplicationJob
  queue_as :default

  def perform
    election = Election.where(date: "2022-10-24").first || Election.create(date: "2022-10-24")

    data = Net::HTTP.get(URI("https://ottawa.ca/en/city-hall/elections/2022-municipal-elections/nominated-candidates-and-registered-third-party-advertisers"))
    doc = Nokogiri::HTML(data)

    candidates = []

    doc.xpath('//table/caption').each do |c|
      next unless c.content.match(/Ward/) || c.content.match(/Mayor/)
      ward = if c.content.match(/Mayor/)
        0
      else
        c.content.match(/Ward (?<ward>\d+) /)[:ward].to_i
      end

      table = c.xpath('..').first
      table.xpath('tbody/tr').each do |r|
        tds = r.xpath('td').map{|td| td.content}
        candidate = {
          ward: ward,
          name: tds[0].gsub("\u00A0", ''),
          nomination_date: tds[1].to_date,
          telephone: tds[3].gsub("\u00A0", ''),
          email: tds[4].gsub("\u00A0", ''),
          website: tds[5].gsub("\u00A0", '')
        }
        candidate = candidate.map { |k, v| [k, v == 'Not provided' ? '' : v] }.to_h
        next if candidate[:name] == ''
        candidates << candidate
      end
    end

    candidates.each do |attr|
      c = Candidate.where(election: election, name: attr[:name], ward: attr[:ward]).first || Candidate.new(election: election, **attr)
      if c.persisted?
        c.assign_attributes(attr)
        c.save!
      else
        c.save!
        message = if c.ward == 0
          "New Mayoral Candidate: #{c.name}"
        else
          "New Candidate in ward #{c.ward} - #{Election.ward_name(c.ward)}: #{c.name}"
        end
        Announcement.create!(message: message, reference: election)
        # new_candidates << c
      end
    end
  end
end