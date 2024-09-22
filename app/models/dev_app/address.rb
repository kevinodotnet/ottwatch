class DevApp::Address < ApplicationRecord
  belongs_to :entry, class_name: "DevApp::Entry"

  def coordinates
    Coordinates.new(lat, lon)
  end
end
