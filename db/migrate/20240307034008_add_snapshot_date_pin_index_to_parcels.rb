class AddSnapshotDatePinIndexToParcels < ActiveRecord::Migration[7.0]
  def change
    add_index :parcels, [:pin, :snapshot_date]
  end
end
