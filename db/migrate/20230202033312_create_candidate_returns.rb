class CreateCandidateReturns < ActiveRecord::Migration[7.0]
  def change
    create_table :candidate_returns do |t|
      t.references :candidate, null: false, foreign_key: true

      t.timestamps
    end
  end
end
