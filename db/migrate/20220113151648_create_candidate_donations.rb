class CreateCandidateDonations < ActiveRecord::Migration[7.0]
  def change
    return if CandidateDonation.table_exists?
    create_table :candidate_donations do |t|
      t.integer "returnid", limit: 3, null: false
      t.integer "type", limit: 1
      t.string "name", limit: 100
      t.string "address", limit: 100
      t.string "city", limit: 100
      t.string "prov", limit: 100
      t.string "postal", limit: 15
      t.decimal "amount", precision: 10, scale: 2
      t.integer "page", limit: 2, unsigned: true
      t.integer "x", limit: 2, unsigned: true
      t.integer "y", limit: 2, unsigned: true
      t.datetime "updated"
      t.datetime "created"
      t.integer "location"
      t.integer "peopleid", limit: 3
      t.integer "donorid", limit: 3, unsigned: true
      t.string "donor_gender", limit: 1
      t.date "donation_date"
      t.string "comment", limit: 1024
      t.integer "ward", limit: 2, unsigned: true
      t.index ["postal"], name: "postal"
      t.index ["returnid"], name: "returnid"

      t.timestamps
    end
  end
end
