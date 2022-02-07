class CreateDevAppEntries < ActiveRecord::Migration[7.0]
  def change
    create_table :dev_app_entries do |t|
      t.string :app_id
      t.string :app_number
      t.string :app_type

      t.timestamps
    end
  end
end
