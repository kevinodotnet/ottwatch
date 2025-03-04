class AddClientDetailsToLobbyingUndertaking < ActiveRecord::Migration[8.0]
  def change
    add_column :lobbying_undertakings, :client, :string
    add_column :lobbying_undertakings, :client_org, :string
  end
end
