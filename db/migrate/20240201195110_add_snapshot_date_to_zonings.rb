class AddSnapshotDateToZonings < ActiveRecord::Migration[7.0]
  def change
    remove_index :zonings, name: "index_zonings_on_objectid"
    add_column :zonings, :snapshot_date, :date
    add_index :zonings, [:snapshot_date, :objectid], :unique => true
  end
end
