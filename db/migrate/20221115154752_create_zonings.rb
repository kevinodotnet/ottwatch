class CreateZonings < ActiveRecord::Migration[7.0]
  def change
    create_table :zonings do |t|
      t.integer :objectid
      t.decimal :shape_area, precision: 25, scale: 15
      t.decimal :shape_length, precision: 25, scale: 15
      t.string :bylaw_num
      t.string :cons_date
      t.string :cons_datef
      t.string :fp_group
      t.string :height
      t.string :heightinfo
      t.string :history
      t.string :label
      t.string :label_en
      t.string :label_fr
      t.string :link_en
      t.string :link_fr
      t.string :parentzone
      t.string :subtype
      t.string :url
      t.string :village_op
      t.string :zone_code
      t.string :zone_main
      t.string :zoningtype
      t.longtext :geometry_json
      
      t.timestamps

      t.index :objectid, unique: true
    end
  end
end
