class CreateCandidateReturns < ActiveRecord::Migration[7.0]
  def change
    create_table :candidate_returns do |t|
      t.integer "candidateid", limit: 3, null: false
      t.string "filename", limit: 512
      t.boolean "supplemental"
      t.integer "done", limit: 1
      t.index ["candidateid"], name: "candidateid"

      t.timestamps
    end
  end
end
