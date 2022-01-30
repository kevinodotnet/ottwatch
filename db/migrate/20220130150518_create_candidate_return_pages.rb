class CreateCandidateReturnPages < ActiveRecord::Migration[7.0]
  def change
    create_table :candidate_return_pages do |t|
      t.references :candidate_return, null: false, foreign_key: true
      t.integer :page
      t.integer :rotation

      t.timestamps
    end
  end
end
