xml.instruct! :xml, :version => "1.0"
xml.rss :version => "2.0" do
  xml.channel do
    xml.title "OttWatch Announcements"
    xml.description "Latest announcements"
    xml.link root_url

    @announcements.each do |a|
      xml.item do
        xml.title a.message
        xml.description "#{a.reference_context}: #{a.reference.class.name}"
        xml.pubDate a.created_at.to_fs(:rfc822)
        xml.link a.reference_link
        xml.guid "https://v2.ottwatch.ca/announcement/#{a.id}"
      end
    end
  end
end