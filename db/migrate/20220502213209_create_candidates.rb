class CreateCandidates < ActiveRecord::Migration[7.0]
  def change
    create_table :candidates do |t|
      t.references :election, null: false, foreign_key: true
      t.integer :ward
      t.string :name
      t.date :nomination_date
      t.string :telephone
      t.string :email
      t.string :website

      t.timestamps
    end
  end
end
