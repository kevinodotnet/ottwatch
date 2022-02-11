class CreateDevAppStatuses < ActiveRecord::Migration[7.0]
  def change
    create_table :dev_app_statuses do |t|
      t.references :entry, null: false, foreign_key: true, class_name: "DevApp::Entry", foreign_key: {to_table: :dev_app_entries}
      t.string :status

      t.timestamps
    end
  end
end
