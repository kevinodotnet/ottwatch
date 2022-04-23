class CreateLobbyingActivities < ActiveRecord::Migration[7.0]
  def change
    create_table :lobbying_activities do |t|
      t.date :occured_at
      t.string :type
      t.references :person, null: false, foreign_key: true

      t.timestamps
    end
  end
end
