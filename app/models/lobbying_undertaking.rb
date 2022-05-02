class LobbyingUndertaking < ApplicationRecord
  has_many :activities, class_name: "LobbyingActivity"
  has_many :announcements, as: :reference
end
