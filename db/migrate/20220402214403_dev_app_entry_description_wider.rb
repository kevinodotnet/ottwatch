class DevAppEntryDescriptionWider < ActiveRecord::Migration[7.0]
  def change
    change_column :dev_app_entries, :desc, :text
  end
end
