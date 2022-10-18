# This file is auto-generated from the current state of the database. Instead
# of editing this file, please use the migrations feature of Active Record to
# incrementally modify your database, and then regenerate this schema definition.
#
# This file is the source Rails uses to define your schema when running `bin/rails
# db:schema:load`. When creating a new database, `bin/rails db:schema:load` tends to
# be faster and is potentially less error prone than running all of your
# migrations from scratch. Old migrations may fail to apply correctly if those
# migrations use external dependencies or application code.
#
# It's strongly recommended that you check this file into your version control system.

ActiveRecord::Schema[7.0].define(version: 2022_10_18_220420) do
  create_table "active_storage_attachments", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.string "name", null: false
    t.string "record_type", null: false
    t.bigint "record_id", null: false
    t.bigint "blob_id", null: false
    t.datetime "created_at", null: false
    t.index ["blob_id"], name: "index_active_storage_attachments_on_blob_id"
    t.index ["record_type", "record_id", "name", "blob_id"], name: "index_active_storage_attachments_uniqueness", unique: true
  end

  create_table "active_storage_blobs", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.string "key", null: false
    t.string "filename", null: false
    t.string "content_type"
    t.text "metadata"
    t.string "service_name", null: false
    t.bigint "byte_size", null: false
    t.string "checksum"
    t.datetime "created_at", null: false
    t.index ["key"], name: "index_active_storage_blobs_on_key", unique: true
  end

  create_table "active_storage_variant_records", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.bigint "blob_id", null: false
    t.string "variation_digest", null: false
    t.index ["blob_id", "variation_digest"], name: "index_active_storage_variant_records_uniqueness", unique: true
  end

  create_table "announcements", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.string "message"
    t.bigint "reference_id"
    t.string "reference_type"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end

  create_table "candidate", id: { type: :integer, limit: 3 }, charset: "latin1", force: :cascade do |t|
    t.integer "year", limit: 2
    t.integer "ward", limit: 1
    t.string "first", limit: 50
    t.string "middle", limit: 50
    t.string "last", limit: 50
    t.string "url", limit: 300
    t.string "email", limit: 50
    t.string "twitter", limit: 50
    t.string "facebook", limit: 100
    t.datetime "nominated", precision: nil
    t.boolean "incumbent", default: false
    t.string "phone", limit: 30
    t.datetime "withdrew", precision: nil
    t.integer "personid", limit: 3
    t.string "gender", limit: 1
    t.integer "retiring", limit: 1
    t.integer "winner", limit: 1
    t.integer "votes", limit: 3, unsigned: true
    t.integer "electionid", limit: 3
    t.index ["personid"], name: "personid"
  end

  create_table "candidate_donation", id: { type: :integer, limit: 3 }, charset: "latin1", force: :cascade do |t|
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
    t.datetime "updated", precision: nil
    t.datetime "created", precision: nil, default: -> { "CURRENT_TIMESTAMP" }
    t.integer "location"
    t.integer "peopleid", limit: 3
    t.integer "donorid", limit: 3, unsigned: true
    t.string "donor_gender", limit: 1
    t.date "donation_date"
    t.string "comment", limit: 1024
    t.integer "ward", limit: 2, unsigned: true
    t.index ["postal"], name: "postal"
    t.index ["returnid"], name: "returnid"
  end

  create_table "candidate_return", id: { type: :integer, limit: 3 }, charset: "latin1", force: :cascade do |t|
    t.integer "candidateid", limit: 3, null: false
    t.string "filename", limit: 512
    t.boolean "supplemental"
    t.integer "done", limit: 1
    t.index ["candidateid"], name: "candidateid"
  end

  create_table "candidates", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.bigint "election_id", null: false
    t.integer "ward"
    t.string "name"
    t.date "nomination_date"
    t.string "telephone"
    t.string "email"
    t.string "website"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.boolean "withdrew"
    t.index ["election_id"], name: "index_candidates_on_election_id"
  end

  create_table "committees", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.string "name"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end

  create_table "dev_app_addresses", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.bigint "entry_id"
    t.string "ref_id"
    t.string "road_number"
    t.string "qualifier"
    t.string "legal_unit"
    t.string "road_name"
    t.string "direction"
    t.string "road_type"
    t.string "municipality"
    t.string "address_type"
    t.decimal "lat", precision: 15, scale: 10
    t.decimal "lon", precision: 15, scale: 10
    t.string "parcel_pin"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["entry_id"], name: "index_dev_app_addresses_on_entry_id"
  end

  create_table "dev_app_documents", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.bigint "entry_id", null: false
    t.string "ref_id"
    t.string "name"
    t.string "path"
    t.string "url"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.string "state"
    t.index ["entry_id"], name: "index_dev_app_documents_on_entry_id"
  end

  create_table "dev_app_entries", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.string "app_id"
    t.string "app_number"
    t.string "app_type"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.text "desc"
    t.string "planner_first_name"
    t.string "planner_last_name"
    t.string "planner_phone"
    t.string "planner_email"
  end

  create_table "dev_app_statuses", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.bigint "entry_id", null: false
    t.string "status"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["entry_id"], name: "index_dev_app_statuses_on_entry_id"
  end

  create_table "election", id: { type: :integer, limit: 3 }, charset: "latin1", force: :cascade do |t|
    t.date "date"
    t.string "city", limit: 64
  end

  create_table "elections", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.date "date"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end

  create_table "global_controls", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.string "name"
    t.string "value"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["name"], name: "index_global_controls_on_name", unique: true
  end

  create_table "lobbying_activities", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.bigint "lobbying_undertaking_id", null: false
    t.date "activity_date"
    t.string "activity_type"
    t.string "lobbied_name"
    t.string "lobbied_title"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["lobbying_undertaking_id"], name: "index_lobbying_activities_on_lobbying_undertaking_id"
  end

  create_table "lobbying_undertakings", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.string "subject"
    t.text "issue"
    t.string "lobbyist_name"
    t.string "lobbyist_position"
    t.string "lobbyist_reg_type"
    t.text "view_details"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end

  create_table "meeting_items", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.string "title"
    t.integer "reference_id"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.bigint "meeting_id", null: false
    t.index ["meeting_id"], name: "index_meeting_items_on_meeting_id"
  end

  create_table "meetings", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.bigint "committee_id", null: false
    t.datetime "start_time", precision: nil
    t.string "contact_name"
    t.string "contact_email"
    t.string "contact_phone"
    t.integer "reference_id"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.string "reference_guid"
    t.index ["committee_id"], name: "index_meetings_on_committee_id"
  end

  create_table "parcels", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.integer "objectid"
    t.string "pin"
    t.decimal "easting", precision: 15, scale: 3
    t.decimal "northing", precision: 15, scale: 3
    t.string "publicland"
    t.string "parceltype"
    t.string "pi_municipal_address_id"
    t.string "record_owner_id"
    t.string "rt_road_name_id"
    t.string "address_number"
    t.string "road_name"
    t.string "suffix"
    t.string "dir"
    t.string "municipality_name"
    t.string "legal_unit"
    t.string "address_qualifier"
    t.string "postal_code"
    t.string "address_status"
    t.string "address_type_id"
    t.string "pin_number"
    t.integer "feat_num"
    t.string "pi_parcel_id"
    t.decimal "shape_length", precision: 25, scale: 15
    t.decimal "shape_area", precision: 25, scale: 15
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.text "geometry_json", size: :medium
    t.index ["objectid"], name: "index_parcels_on_objectid", unique: true
  end

  create_table "users", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.string "email", default: ""
    t.string "encrypted_password", default: "", null: false
    t.string "reset_password_token"
    t.datetime "reset_password_sent_at"
    t.datetime "remember_created_at"
    t.integer "sign_in_count", default: 0, null: false
    t.datetime "current_sign_in_at"
    t.datetime "last_sign_in_at"
    t.string "current_sign_in_ip"
    t.string "last_sign_in_ip"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.string "provider"
    t.string "uid"
    t.string "name"
    t.string "username"
    t.index ["reset_password_token"], name: "index_users_on_reset_password_token", unique: true
  end

  add_foreign_key "active_storage_attachments", "active_storage_blobs", column: "blob_id"
  add_foreign_key "active_storage_variant_records", "active_storage_blobs", column: "blob_id"
  add_foreign_key "candidate_donation", "candidate_return", column: "returnid", name: "candidate_donation_ibfk_1", on_update: :cascade, on_delete: :cascade
  add_foreign_key "candidate_return", "candidate", column: "candidateid", name: "candidate_return_ibfk_1", on_update: :cascade, on_delete: :cascade
  add_foreign_key "candidates", "elections"
  add_foreign_key "dev_app_documents", "dev_app_entries", column: "entry_id"
  add_foreign_key "dev_app_statuses", "dev_app_entries", column: "entry_id"
  add_foreign_key "lobbying_activities", "lobbying_undertakings"
  add_foreign_key "meeting_items", "meetings"
  add_foreign_key "meetings", "committees"
end
