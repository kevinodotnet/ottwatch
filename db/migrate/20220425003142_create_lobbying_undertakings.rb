class CreateLobbyingUndertakings < ActiveRecord::Migration[7.0]
  def change
    create_table :lobbying_undertakings do |t|
      t.string :subject
      t.text :issue
      t.string :lobbyist_name
      t.string :lobbyist_position
      t.string :lobbyist_reg_type
      t.text :view_details

      t.timestamps
    end
  end
end
