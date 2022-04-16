require 'net/http'
class MeetingScanJob < ApplicationJob
  queue_as :default

  MEETING_CRUMB = "<tr class=\"Results\""

  def perform
    data = Net::HTTP.get(URI("https://app05.ottawa.ca/sirepub/meetresults.aspx"))
    data = data.gsub(/\r/,' ').gsub(/\n/, ' ')
    meetings = data.split(MEETING_CRUMB)
    meetings.shift # garbage before first meeting
    meetings.each do |m|
      begin
        name = m.match(/<td class=.MeetingCell.>(?<value>[^<]*)</)[:value]
        match = m.match(/<td class=.DateCell.>(?<date>[^<]*)<.td><td class=.TimeCell.>(?<time>[^<]*)<.td>/)
        date = match[:date]
        time = match[:time]
        start_time = "#{date} #{time}".in_time_zone("Eastern Time (US & Canada)")

        if match = m.match(/mtgviewer.aspx.meetid=(?<value>\d+)/)
          reference_id = match[:value].to_i
        end

        if match = m.match(/<div class=.email_overflow.>(?<value>[^<]*)<br/)
          contact_name = match[:value]
        end
        if match = m.match(/mailto:(?<value>[^<]*).>/)
          contact_email = match[:value].downcase
        end
        if match = m.match(/>(?<value>\d\d\d-\d\d\d-\d\d\d\d[^<]*)</)
          contact_phone = match[:value].downcase
        end
        
        attributes = {
          contact_name: contact_name, 
          contact_email: contact_email,
          contact_phone: contact_phone,
          reference_id: reference_id,
          start_time: start_time,
        }

        next if reference_id.nil? # no agenda yet; choose not to care yet

        Meeting.transaction do
          committee = Committee.where(name: name).first || Committee.create!(name: name)
          meeting = Meeting.where(reference_id: reference_id).first || Meeting.create!(committee: committee, reference_id: reference_id)
          meeting.assign_attributes(attributes)
          meeting.save!
          
          data = Net::HTTP.get(URI("https://app05.ottawa.ca/sirepub/agview.aspx?agviewmeetid=#{reference_id}&agviewdoctype=AGENDA"))
          agenda_cache_url = data.match(/<a href=..(?<url>.*htm).>here<.a>/)[:url]
          data = Net::HTTP.get(URI("https://app05.ottawa.ca/#{agenda_cache_url}"))
          data = data.gsub(/[\r\n\t]/, ' ').gsub(/  */, ' ')

          doc = Nokogiri::HTML(data)
          doc.xpath('//a').each do |l|
            if l.attributes.values.map{|m| m.to_s}.join(" ").match(/itemid=/)
              reference_id = l.attributes["href"].value.match(/itemid=(?<reference_id>\d+)/)[:reference_id]
              title = l.content
              item = MeetingItem.find_by_reference_id(reference_id) || MeetingItem.create!(title: title, reference_id: reference_id, meeting: meeting)
            end
          end
        end
      rescue => e
        Rails.logger.error("Failure parsing meeting details: #{e.message} #{m}")
      end
    end

    nil
  end
end
