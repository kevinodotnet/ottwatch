class AddGeometryToParcels < ActiveRecord::Migration[7.0]
  def change
    add_column :parcels, :geometry_json, :text
  end
end
