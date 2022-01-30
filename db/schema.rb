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

ActiveRecord::Schema.define(version: 2022_01_30_150518) do

  create_table "active_storage_attachments", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.string "name", null: false
    t.string "record_type", null: false
    t.bigint "record_id", null: false
    t.bigint "blob_id", null: false
    t.datetime "created_at", precision: 6, null: false
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
    t.datetime "created_at", precision: 6, null: false
    t.index ["key"], name: "index_active_storage_blobs_on_key", unique: true
  end

  create_table "active_storage_variant_records", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.bigint "blob_id", null: false
    t.string "variation_digest", null: false
    t.index ["blob_id", "variation_digest"], name: "index_active_storage_variant_records_uniqueness", unique: true
  end

  create_table "candidate_donations", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
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
    t.datetime "updated", precision: 6
    t.datetime "created", precision: 6
    t.integer "location"
    t.integer "peopleid", limit: 3
    t.integer "donorid", limit: 3, unsigned: true
    t.string "donor_gender", limit: 1
    t.date "donation_date"
    t.string "comment", limit: 1024
    t.integer "ward", limit: 2, unsigned: true
    t.datetime "created_at", precision: 6, null: false
    t.datetime "updated_at", precision: 6, null: false
    t.index ["postal"], name: "postal"
    t.index ["returnid"], name: "returnid"
  end

  create_table "candidate_return_pages", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.bigint "candidate_return_id", null: false
    t.integer "page"
    t.integer "rotation"
    t.datetime "created_at", precision: 6, null: false
    t.datetime "updated_at", precision: 6, null: false
    t.index ["candidate_return_id"], name: "index_candidate_return_pages_on_candidate_return_id"
  end

  create_table "candidate_returns", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.integer "candidateid", limit: 3, null: false
    t.string "filename", limit: 512
    t.boolean "supplemental"
    t.integer "done", limit: 1
    t.datetime "created_at", precision: 6, null: false
    t.datetime "updated_at", precision: 6, null: false
    t.index ["candidateid"], name: "candidateid"
  end

  create_table "candidates", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.integer "year", limit: 2
    t.integer "ward", limit: 1
    t.string "first", limit: 50
    t.string "middle", limit: 50
    t.string "last", limit: 50
    t.string "url", limit: 300
    t.string "email", limit: 50
    t.string "twitter", limit: 50
    t.string "facebook", limit: 100
    t.datetime "nominated", precision: 6
    t.boolean "incumbent", default: false
    t.string "phone", limit: 30
    t.datetime "withdrew", precision: 6
    t.integer "personid", limit: 3
    t.string "gender", limit: 1
    t.integer "retiring", limit: 1
    t.integer "winner", limit: 1
    t.integer "votes", limit: 3, unsigned: true
    t.integer "electionid", limit: 3
    t.datetime "created_at", precision: 6, null: false
    t.datetime "updated_at", precision: 6, null: false
    t.index ["personid"], name: "personid"
  end

  create_table "election", charset: "utf8mb4", collation: "utf8mb4_0900_ai_ci", force: :cascade do |t|
    t.date "date"
    t.string "city"
  end

  add_foreign_key "active_storage_attachments", "active_storage_blobs", column: "blob_id"
  add_foreign_key "active_storage_variant_records", "active_storage_blobs", column: "blob_id"
  add_foreign_key "candidate_return_pages", "candidate_returns"
end
