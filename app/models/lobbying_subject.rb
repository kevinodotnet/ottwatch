class LobbyingSubject < ApplicationRecord
  belongs_to :lobbyist
  belongs_to :lobbying_client
end
