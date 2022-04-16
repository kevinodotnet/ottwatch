class CreateMeetings < ActiveRecord::Migration[7.0]
  def change
    create_table :meetings do |t|
      t.references :committee, null: false, foreign_key: true
      t.time :start_time
      t.string :contact_name
      t.string :contact_email
      t.string :contact_phone
      t.integer :reference_id

      t.timestamps
    end
  end
end
