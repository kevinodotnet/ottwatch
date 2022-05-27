class AddUniqueIndexToParcels < ActiveRecord::Migration[7.0]
  def change
    add_index :parcels, :objectid, :unique => true
  end
end
