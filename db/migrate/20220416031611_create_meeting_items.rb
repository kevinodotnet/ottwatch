class CreateMeetingItems < ActiveRecord::Migration[7.0]
  def change
    create_table :meeting_items do |t|
      t.string :title
      t.integer :reference_id

      t.timestamps
    end
  end
end
