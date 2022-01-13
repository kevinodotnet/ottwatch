class CreateElections < ActiveRecord::Migration[7.0]
  def change
    create_table :election do |t|
      t.date :date
      t.string :city
    end
  end
end
