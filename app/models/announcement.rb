class Announcement < ApplicationRecord
  belongs_to :reference, polymorphic: true

  def link_to_context
    if reference.is_a?(DevApp::Entry)
      if addr = reference.addresses.first
        parts = [addr.road_number, addr.road_name, addr.road_type, addr.direction].reject{|c| c == ""}
        return nil if parts.count < 2
        return parts.join(" ")
      end
    end
    return "#{reference.start_time.in_time_zone("America/New_York").strftime("%b %d %H:%M")}" if reference.is_a?(Meeting)
  end

  def link_to_reference
    # TODO: this should move to a helper that can be used in the UI as well
    url = if Rails.env.production?
      "https://v2.ottwatch.ca"
    else
      "http://localhost:33000"
    end
    return "#{url}/devapp/#{reference.app_number}" if reference.is_a?(DevApp::Entry)
    return "/meeting/#{reference.reference_id}" if reference.is_a?(Meeting)
  end
end
