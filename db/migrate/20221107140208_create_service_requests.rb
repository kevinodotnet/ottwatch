class CreateServiceRequests < ActiveRecord::Migration[7.0]
  def change
    create_table :service_requests do |t|
      t.string :service_request_id
      t.string :status
      t.string :status_notes
      t.string :service_name
      t.string :service_code
      t.string :description
      t.string :agency_responsible
      t.string :service_notice
      t.time :requested_datetime
      t.time :updated_datetime
      t.time :expected_datetime
      t.string :address
      t.string :address_id
      t.string :zipcode
      t.decimal :lat, precision: 15, scale: 10
      t.decimal :lon, precision: 15, scale: 10
      t.string :media_url

      t.timestamps
    end
  end
end
