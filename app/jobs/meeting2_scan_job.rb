require 'net/http'
class Meeting2ScanJob < ApplicationJob
  queue_as :default

  def perform(attrs: nil)
    if attrs
      scan_meeting(attrs)
    else
      scan_main_list
    end
  end

  private

  def scan_main_list
    data = Net::HTTP.get(URI("https://pub-ottawa.escribemeetings.com/"))
    doc = Nokogiri::HTML(data)

    doc.xpath('//div[@class="calendar-item"]').each do |m|
      md = Nokogiri::HTML(m.to_s)

      title = md.xpath('//div[@class="meeting-title"]/h3/span').children.to_s
      meeting_time = md.xpath('//div[@class="meeting-date"]').first.children.to_s
      meeting_time = "#{meeting_time} EST".to_time
      reference_guid = md.xpath('//a').map do |a|
        a.attributes.map do |k,v|
          next unless k == 'href'
          next unless v.value.match(/Meeting.aspx.*/)
          next unless a.children.to_s.match(/HTML/)
          v.value.match(/Meeting.aspx\?Id=(?<id>[^&]*)/)["id"]
        end
      end.flatten.compact.first

      next unless reference_guid

      attrs = {
        title: title,
        reference_guid: reference_guid,
        meeting_time: meeting_time
      }
      # puts attrs.to_json
      Meeting2ScanJob.perform_later(attrs)
    end

    nil
  end

  def scan_meeting(attrs)

    # {"title":"Committee of Adjustment - Panel 3","reference_guid":"2dd97c8d-fdc0-4ecb-833e-6d5c8489d552","meeting_time":"2022-09-07T09:00:00.000-05:00"},

    guid = attrs[:reference_guid]
    meeting_time = attrs[:meeting_time].to_time
    title = attrs[:title]

    data = Net::HTTP.get(URI("https://pub-ottawa.escribemeetings.com/Meeting.aspx?Id=#{guid}&Agenda=Agenda&lang=English"))
    doc = Nokogiri::HTML(data)

    # if doc.xpath('//strong').detect{|e| e.children.to_s == 'cofa@ottawa.ca'}
    #   # committee of adjustment
    #   contact_name = "Committee of Adjustment Coordinator"
    #   contact_phone = doc.to_s.match(/.613. 580-2436/).to_s
    #   contact_email = "cofa@ottawa.ca"
    # else
    #   # police board; others?
    #   contact_info = doc.xpath('//div[@class="AgendaHeaderSpecialComments"]/p')
    #   binding.pry unless contact_info.children.to_a[1]
    #   contact_name = contact_info.children.to_a[0].children.to_s
    #   contact_phone = contact_info.children.to_a[1].children.to_s
    #   contact_email = contact_info.children.to_a[2].children.to_s
    # end

    doc.xpath('//div[@class="AgendaItemContainer indent"]').each do |item|
      item_doc = Nokogiri::HTML(item.to_s)

      item_title = item_doc.xpath('//div[@class="ClosedAgendaItemTitle"]').children.to_s # city council meetings are "closed" items
      item_title = item_doc.xpath("//a").first.children.to_s unless item_title
      item_doc.xpath('//a[@class="Link"]').each do |attachment|
        doc_id = attachment.attributes["href"].value.match(/DocumentId=(?<id>\d+)/)["id"].to_i
        doc_name = attachment.attributes["data-original-title"].value
      end
    end

    Meeting.transaction do
      meeting = create_meeting(
        name: title, 
        reference_guid: guid, 
        start_time: meeting_time,
        # contact_name: contact_name,
        # contact_email: contact_email,
        # contact_phone: contact_phone
      )
      create_announcement(meeting)
      meeting.save!
    end
  end
  
  private

  def create_announcement(meeting)
    return if meeting.persisted? # not a new meeting
    meeting.announcements << Announcement.new(message: "New Meeting: #{meeting.committee.name}")
  end

  def create_meeting(name:, reference_guid:, contact_name: nil, contact_email: nil, contact_phone: nil, start_time: )
    attributes = {
      contact_name: contact_name, 
      contact_email: contact_email,
      contact_phone: contact_phone,
      reference_guid: reference_guid,
      start_time: start_time,
    }

    committee = Committee.where(name: name).first || Committee.create!(name: name)
    meeting = Meeting.where(reference_guid: reference_guid).first
    unless meeting
      meeting = Meeting.new(committee: committee, reference_guid: reference_guid)
    end
    meeting.assign_attributes(attributes)
    meeting
  end
end
