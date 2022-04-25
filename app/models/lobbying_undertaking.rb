class LobbyingUndertaking < ApplicationRecord
  has_many :activities, class_name: "LobbyingActivity"
end
