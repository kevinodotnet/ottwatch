class AddSnapshotDateToParcels < ActiveRecord::Migration[7.0]
  def change
    add_column :parcels, :snapshot_date, :date
  end
end
