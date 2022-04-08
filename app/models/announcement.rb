class Announcement < ApplicationRecord
  belongs_to :reference, polymorphic: true

  def link_to_context
    if reference.is_a?(DevApp::Entry)
      if addr = reference.addresses.first
        [addr.road_number, addr.road_name, addr.road_type, addr.direction].reject{|c| c == ""}.join(" ")
      end
    end
  end

  def link_to_reference
    # TODO: this should move to a helper that can be used in the UI as well
    url = if Rails.env.production?
      "https://v2.ottwatch.ca"
    else
      "http://localhost:33000"
    end
    url << "/devapp/#{reference.app_number}" if reference.is_a?(DevApp::Entry)
    url
  end
end
