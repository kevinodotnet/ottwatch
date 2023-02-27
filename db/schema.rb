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

ActiveRecord::Schema[7.0].define(version: 2023_02_27_025209) do
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

  create_table "campaign_donations", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.bigint "campaign_return_page_id", null: false
    t.string "name"
    t.string "address"
    t.string "city"
    t.string "province"
    t.string "postal"
    t.float "x"
    t.float "y"
    t.decimal "amount", precision: 10, scale: 2
    t.date "donation_date"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["campaign_return_page_id"], name: "index_campaign_donations_on_campaign_return_page_id"
  end

  create_table "campaign_return_pages", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.bigint "campaign_return_id", null: false
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.integer "rotation", default: 0
    t.index ["campaign_return_id"], name: "index_campaign_return_pages_on_campaign_return_id"
  end

  create_table "campaign_returns", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.bigint "candidate_id", null: false
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["candidate_id"], name: "index_campaign_returns_on_candidate_id"
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

  create_table "consultations", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.string "title"
    t.string "href"
    t.string "status"
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

  create_table "service_requests", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.string "service_request_id"
    t.string "status"
    t.string "status_notes"
    t.string "service_name"
    t.string "service_code"
    t.string "description"
    t.string "agency_responsible"
    t.string "service_notice"
    t.datetime "requested_datetime", precision: nil
    t.datetime "updated_datetime", precision: nil
    t.datetime "expected_datetime", precision: nil
    t.string "address"
    t.string "address_id"
    t.string "zipcode"
    t.decimal "lat", precision: 15, scale: 10
    t.decimal "lon", precision: 15, scale: 10
    t.string "media_url"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
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

  create_table "zonings", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.integer "objectid"
    t.decimal "shape_area", precision: 25, scale: 15
    t.decimal "shape_length", precision: 25, scale: 15
    t.string "bylaw_num"
    t.string "cons_date"
    t.string "cons_datef"
    t.string "fp_group"
    t.string "height"
    t.string "heightinfo"
    t.string "history"
    t.string "label"
    t.string "label_en"
    t.string "label_fr"
    t.string "link_en"
    t.string "link_fr"
    t.string "parentzone"
    t.string "subtype"
    t.string "url"
    t.string "village_op"
    t.string "zone_code"
    t.string "zone_main"
    t.string "zoningtype"
    t.text "geometry_json", size: :long
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["objectid"], name: "index_zonings_on_objectid", unique: true
  end

  add_foreign_key "active_storage_attachments", "active_storage_blobs", column: "blob_id"
  add_foreign_key "active_storage_variant_records", "active_storage_blobs", column: "blob_id"
  add_foreign_key "campaign_donations", "campaign_return_pages"
  add_foreign_key "campaign_return_pages", "campaign_returns"
  add_foreign_key "campaign_returns", "candidates"
  add_foreign_key "candidates", "elections"
  add_foreign_key "dev_app_documents", "dev_app_entries", column: "entry_id"
  add_foreign_key "dev_app_statuses", "dev_app_entries", column: "entry_id"
  add_foreign_key "lobbying_activities", "lobbying_undertakings"
  add_foreign_key "meeting_items", "meetings"
  add_foreign_key "meetings", "committees"
end
