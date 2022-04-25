class CreateLobbyingActivities < ActiveRecord::Migration[7.0]
  def change
    create_table :lobbying_activities do |t|
      t.references :lobbying_undertaking, null: false, foreign_key: true
      t.date :activity_date
      t.string :activity_type
      t.string :lobbied_name
      t.string :lobbied_title

      t.timestamps
    end
  end
end
