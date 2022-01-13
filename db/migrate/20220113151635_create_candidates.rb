class CreateCandidates < ActiveRecord::Migration[7.0]
  def change
    return if Candidate.table_exists?
    create_table :candidates do |t|
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

      t.timestamps
    end
  end
end
