class DropObjectIdIndexFromParcels < ActiveRecord::Migration[7.0]
  def change
    remove_index :parcels, name: "index_parcels_on_objectid"
  end
end
