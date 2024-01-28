class AddScanObjectIdIndexToParcels < ActiveRecord::Migration[7.0]
  def change
    add_index :parcels, [:snapshot_date, :objectid], :unique => true
  end
end
