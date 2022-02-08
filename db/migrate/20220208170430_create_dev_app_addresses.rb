class CreateDevAppAddresses < ActiveRecord::Migration[7.0]
  def change
    create_table :dev_app_addresses do |t|
      t.references :entry, class_name: "DevApp::Entry"
      t.string :ref_id
      t.string :road_number
      t.string :qualifier
      t.string :legal_unit
      t.string :road_name
      t.string :direction
      t.string :road_type
      t.string :municipality
      t.string :address_type
      t.decimal :lat, precision: 15, scale: 10
      t.decimal :lon, precision: 15, scale: 10
      t.string :parcel_pin
      
      t.timestamps
    end
  end
end
