class CreateTrafficCameras < ActiveRecord::Migration[7.1]
  def change
    create_table :traffic_cameras do |t|
      t.float :lat
      t.float :lon
      t.string :name
      t.string :camera_owner
      t.integer :camera_number
      t.string :reference_id

      t.timestamps
    end
  end
end
