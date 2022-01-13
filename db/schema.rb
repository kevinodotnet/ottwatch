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

ActiveRecord::Schema.define(version: 2022_01_13_151701) do

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
    t.datetime "nominated"
    t.boolean "incumbent", default: false
    t.string "phone", limit: 30
    t.datetime "withdrew"
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
    t.datetime "updated"
    t.datetime "created", default: -> { "CURRENT_TIMESTAMP" }
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

  create_table "election", id: { type: :integer, limit: 3 }, charset: "latin1", force: :cascade do |t|
    t.date "date"
    t.string "city", limit: 64
  end

  add_foreign_key "candidate", "people", column: "personid", name: "candidate_ibfk_1"
  add_foreign_key "candidate_donation", "candidate_return", column: "returnid", name: "candidate_donation_ibfk_1", on_update: :cascade, on_delete: :cascade
  add_foreign_key "candidate_return", "candidate", column: "candidateid", name: "candidate_return_ibfk_1", on_update: :cascade, on_delete: :cascade
end
