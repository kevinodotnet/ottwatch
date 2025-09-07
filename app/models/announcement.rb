class Announcement < ApplicationRecord
  belongs_to :reference, polymorphic: true

  # TODO: this should be in an ERB helper?
  def font_awesome_class
    case reference_type
    when "Consultation"
      "fa-solid fa-square-poll-horizontal"
    when "DevApp::Entry"
      "fa-solid fa-building"
    when "LobbyingUndertaking"
      "fa-solid fa-handshake"
    when "Meeting"
      "fa-solid fa-calendar"
    when "Memo"
      "fa-solid fa-memo"
    else
      "fa-solid fa-question"
    end
  end

  def reference_context
    parts = []

    if reference.is_a?(Consultation)
      return "Consultation: #{reference.title}"
    end

    if reference.is_a?(DevApp::Entry)
      if addr = reference.addresses.first
        parts = [addr.road_number, addr.road_name, addr.road_type, addr.direction].reject{|c| c == ""}
        return "DevApp #{reference.app_number}: #{parts.join(" ")}"
      end
    end

    if reference.is_a?(Meeting)
      return "Meeting: #{reference.committee.name} #{reference.start_time.in_time_zone("America/New_York").strftime("%b %d %H:%M")}" 
    end

    if reference.is_a?(LobbyingUndertaking)
      issue = reference.issue || ""
      return "Lobbying by #{reference.lobbyist_name}: #{issue.split(" ").first(15).join(" ")} ..."
    end

    if reference.is_a?(Memo)
      content = ActionView::Base.full_sanitizer.sanitize(reference.content).gsub(/\s+/, ' ').strip.gsub(/^Memo: /, '').gsub(/ \(.*20\d\d\) /, ' - ').first(100) + "..."
      return "Memo: #{reference.department} - #{content}" 
    end

    return "? #{reference_context}" # should not be reachable
  end

  def reference_link
    return reference.full_href if reference.is_a?(Consultation)

    # TODO: this should move to a helper that can be used in the UI as well
    url = if Rails.env.production?
      "https://ottwatch.ca"
    else
      "http://localhost:33000"
    end

    return "#{url}/devapp/#{reference.app_number}" if reference.is_a?(DevApp::Entry)
    if reference.is_a?(Meeting)
      return "#{url}/meeting/#{reference.reference_id}" if reference.reference_id
      return "#{url}/meeting/#{reference.reference_guid}" if reference.reference_guid
    end
    return "#{url}/lobbying/#{reference.id}" if reference.is_a?(LobbyingUndertaking)
    return reference.url if reference.is_a?(Memo)
  end
end
