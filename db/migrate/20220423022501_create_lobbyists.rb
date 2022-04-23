class CreateLobbyists < ActiveRecord::Migration[7.0]
  def change
    create_table :lobbyists do |t|
      t.references :person, null: false, foreign_key: true
      t.string :position
      t.string :reg_type
      t.references :organization, null: false, foreign_key: true
      t.string :status

      t.timestamps
    end
  end
end
