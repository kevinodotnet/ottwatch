class ChangeGeometryJsonToMediumText < ActiveRecord::Migration[7.0]
  def change
    change_column :parcels, :geometry_json, :mediumtext
  end
end
