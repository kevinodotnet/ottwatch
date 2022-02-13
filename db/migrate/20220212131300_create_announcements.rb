class CreateAnnouncements < ActiveRecord::Migration[7.0]
  def change
    create_table :announcements do |t|
      t.string :message
      t.bigint :reference_id
      t.string :reference_type

      t.timestamps
    end
  end
end
