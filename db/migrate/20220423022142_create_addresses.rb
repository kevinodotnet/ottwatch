class CreateAddresses < ActiveRecord::Migration[7.0]
  def change
    create_table :addresses do |t|
      t.string :number_name
      t.string :city
      t.string :province
      t.string :postal

      t.timestamps
    end
  end
end
