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

ActiveRecord::Schema[7.0].define(version: 2022_04_23_022949) do
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

  create_table "addresses", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.string "number_name"
    t.string "city"
    t.string "province"
    t.string "postal"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end

  create_table "announcements", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.string "message"
    t.bigint "reference_id"
    t.string "reference_type"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
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

  create_table "global_controls", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.string "name"
    t.string "value"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["name"], name: "index_global_controls_on_name", unique: true
  end

  create_table "lobbying_activities", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.date "occured_at"
    t.string "type"
    t.bigint "person_id", null: false
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["person_id"], name: "index_lobbying_activities_on_person_id"
  end

  create_table "lobbying_clients", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.string "name"
    t.bigint "organization_id", null: false
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["organization_id"], name: "index_lobbying_clients_on_organization_id"
  end

  create_table "lobbying_subjects", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.bigint "lobbyist_id", null: false
    t.bigint "lobbying_client_id", null: false
    t.string "subject"
    t.text "issue"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["lobbying_client_id"], name: "index_lobbying_subjects_on_lobbying_client_id"
    t.index ["lobbyist_id"], name: "index_lobbying_subjects_on_lobbyist_id"
  end

  create_table "lobbyists", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.bigint "person_id", null: false
    t.string "position"
    t.string "reg_type"
    t.bigint "organization_id", null: false
    t.string "status"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["organization_id"], name: "index_lobbyists_on_organization_id"
    t.index ["person_id"], name: "index_lobbyists_on_person_id"
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
    t.index ["committee_id"], name: "index_meetings_on_committee_id"
  end

  create_table "organizations", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.string "name"
    t.bigint "address_id", null: false
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["address_id"], name: "index_organizations_on_address_id"
  end

  create_table "people", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.string "name"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end

  add_foreign_key "active_storage_attachments", "active_storage_blobs", column: "blob_id"
  add_foreign_key "active_storage_variant_records", "active_storage_blobs", column: "blob_id"
  add_foreign_key "dev_app_documents", "dev_app_entries", column: "entry_id"
  add_foreign_key "dev_app_statuses", "dev_app_entries", column: "entry_id"
  add_foreign_key "lobbying_activities", "people"
  add_foreign_key "lobbying_clients", "organizations"
  add_foreign_key "lobbying_subjects", "lobbying_clients"
  add_foreign_key "lobbying_subjects", "lobbyists"
  add_foreign_key "lobbyists", "organizations"
  add_foreign_key "lobbyists", "people"
  add_foreign_key "meeting_items", "meetings"
  add_foreign_key "meetings", "committees"
  add_foreign_key "organizations", "addresses"
end
