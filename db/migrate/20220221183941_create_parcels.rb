class CreateParcels < ActiveRecord::Migration[7.0]
  def change
    create_table :parcels do |t|
      t.integer :objectid
      t.string :pin
      t.decimal :easting, precision: 15, scale: 3
      t.decimal :northing, precision: 15, scale: 3
      t.string :publicland
      t.string :parceltype
      t.string :pi_municipal_address_id
      t.string :record_owner_id
      t.string :rt_road_name_id
      t.string :address_number
      t.string :road_name
      t.string :suffix
      t.string :dir
      t.string :municipality_name
      t.string :legal_unit
      t.string :address_qualifier
      t.string :postal_code
      t.string :address_status
      t.string :address_type_id
      t.string :pin_number
      t.integer :feat_num
      t.string :pi_parcel_id
      t.decimal :shape_length, precision: 25, scale: 15
      t.decimal :shape_area, precision: 25, scale: 15
      t.timestamps
    end
  end
end
