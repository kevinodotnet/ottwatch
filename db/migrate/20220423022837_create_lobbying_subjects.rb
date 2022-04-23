class CreateLobbyingSubjects < ActiveRecord::Migration[7.0]
  def change
    create_table :lobbying_subjects do |t|
      t.references :lobbyist, null: false, foreign_key: true
      t.references :lobbying_client, null: false, foreign_key: true
      t.string :subject
      t.text :issue

      t.timestamps
    end
  end
end
