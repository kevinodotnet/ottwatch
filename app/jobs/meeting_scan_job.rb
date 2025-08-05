require 'net/http'
class MeetingScanJob < ApplicationJob
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

    url = URI("https://pub-ottawa.escribemeetings.com/MeetingsCalendarView.aspx/GetAllMeetings")
    url = URI("https://pub-ottawa.escribemeetings.com/MeetingsCalendarView.aspx/GetCalendarMeetings")
    req = Net::HTTP::Post.new(url)
    req['Content-Type'] = 'application/json'
    req['User-Agent'] = 'OttWatch/1.0'
    # FIXME: lame date hard coding
    req.body = "{'calendarStartDate':'2024-01-01T00:00:00-04:00','calendarEndDate':'2026-12-01T00:00:00-04:00'}"

    http = Net::HTTP.new(url.host, url.port)
    http.use_ssl = true
    res = http.request(req)

    data = res.body
    meetings = JSON.parse(data)["d"]
    meetings
      .select{|m| m["Url"].presence}
      .sort_by{|m| m["StartDate"]}.each do |m|
      next unless m["Url"].presence
      # url = "https://pub-ottawa.escribemeetings.com/Meeting.aspx?Id=#{m["ID"]}&Agenda=Agenda&lang=English"
      attrs = {
        title: m["MeetingName"],
        reference_guid: m["ID"],
        meeting_time: m["StartDate"].in_time_zone('Eastern Time (US & Canada)')
      }
      MeetingScanJob.set(wait: rand(0..7200).seconds).perform_later(attrs: attrs)
    end
  end

  def scan_v1_item(item_div, docs)
    item_num = item_div.attributes["itemid"].value.to_i
    item_title = item_div.text.gsub(/\n/, " ").gsub(/  */, " ")
    {
      num: item_num,
      title: item_title,
      content: nil, # todo
      docs: docs.select{|d| d[:item_id] == item_num}.map{|d| d.slice(:id, :title)}
    }
  end

  def scan_v2_item(item_div)
    title_xpath = '//div[@class="AgendaItemTitle"]/a/text()'
    in_camera_title_xpath = '//div[@class="ClosedAgendaItemTitle"]/text()'

    in_camera = item_div.xpath(in_camera_title_xpath).first.to_s.size > 0
    item_num = nil
    item_content = nil
    item_title = [
      title_xpath,
      in_camera_title_xpath
    ].map do |xpath|
      title = item_div.xpath(xpath).first.to_s
      title == "" ? nil : title
    end.compact.first

    unless in_camera
      item_class_num = elements_with_class(item_div, 'AgendaItem').first.attributes["class"].value.match(/AgendaItem(\d+)/)
      item_num = item_class_num[1].to_i
      item_content = elements_with_class(item_div, 'AgendaItemContentRow').map(&:text).join("\n").strip
    end

    item_docs = item_div.xpath('//a[@class="Link"]').map do |attachment|
      attachment = Nokogiri::HTML(attachment.to_s)
      doc_id = attachment.xpath("//a").first.attributes["href"].value.match(/DocumentId=(\d+)/)[1]
      doc_title = attachment.xpath("//a").first.attributes["data-original-title"].value
      {
        id: doc_id,
        title: doc_title
      }
    end

    {
      num: item_num,
      title: item_title,
      content: item_content,
      docs: item_docs
    }
  end

  def scan_meeting(attrs)
    guid = attrs[:reference_guid]
    meeting_time = attrs[:meeting_time].to_time
    title = attrs[:title]

    url = URI("https://pub-ottawa.escribemeetings.com/Meeting.aspx?Id=#{guid}&Agenda=Agenda&lang=English")
    req = Net::HTTP::Get.new(url)
    req['User-Agent'] = 'OttWatch/1.0'
    http = Net::HTTP.new(url.host, url.port)
    http.use_ssl = true
    data = http.request(req).body
    doc = Nokogiri::HTML(data)

    items = if elements_with_class(doc, 'SelectableItem').any?
      # v1 agenda format

      data = data.force_encoding("UTF-8")
        .gsub(/–/, "-")
        .gsub(/’/, "'")
        .gsub(/&nbsp;/, " ")

      doc = Nokogiri::HTML(data)

      lines = data.gsub(/\r/, "").gsub(/</, "\n<").split("\n")
      doc_div_starts = lines.each_index.select{|i| lines[i].match(/DIV class='AgendaItemAttachment AgendaItemAttachment\d+'/)}
      docs = doc_div_starts.map do |line_num|
        link = lines[line_num..].first(20).detect{|l| l.match(/<a/)}
        {
          item_id: lines[line_num].scan(/\d+/).first.to_i,
          id: link.match(/documentid=(\d+)/i)[1].to_i,
          title: link.match(/data-original-title='(.*)' target/)[1].gsub(/  */, " ")
        }
      end
      elements_with_class(doc, 'SelectableItem').map do |item|
        scan_v1_item(item, docs)
      end
    else
      # v2 agenda format
      elements_with_class(doc, 'AgendaItem').map do |item|
        item_div = Nokogiri::HTML(item.to_s)
        scan_v2_item(item_div)
      end
    end.compact

    Meeting.transaction do
      meeting = create_meeting(
        name: title,
        reference_guid: guid,
        start_time: meeting_time,
      )

      create_announcement(meeting)
      meeting.save!

      items.each do |item|
        i = find_or_create_item(meeting, item[:num])
        i.title = item[:title]
        i.content = item[:content]
        i.save!

        item[:docs].each do |d|
          doc = find_or_create_doc(i, d[:id])
          doc.title = d[:title]
          doc.save!
        end

        current_docs = item[:docs].map{|d| d[:id].to_s}
        saved_docs = i.documents.map(&:reference_id)
        MeetingItemDocument.where(reference_id: (saved_docs - current_docs)).delete_all
      end
    end
  end

  def elements_with_class(node, target_class)
    node.xpath("//*[contains(concat(' ', normalize-space(@class), ' '), ' #{target_class} ')]")
  end

  def create_announcement(meeting)
    return if meeting.persisted? # not a new meeting
    meeting.announcements << Announcement.new(
      message: "New Meeting: #{meeting.committee.name}, #{meeting.start_time.strftime("%b %d, %Y")}"
    )
  end

  def find_or_create_item(meeting, reference_id)
    item = meeting.items.find_by_reference_id(reference_id)
    if item.nil?
      item = meeting.items.new(reference_id: reference_id)
    end
    item
  end

  def find_or_create_doc(item, reference_id)
    doc = item.documents.find_by_reference_id(reference_id)
    if doc.nil?
      doc = item.documents.new(reference_id: reference_id)
    end
    doc
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
