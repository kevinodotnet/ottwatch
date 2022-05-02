class CreateElections < ActiveRecord::Migration[7.0]
  def change
    create_table :elections do |t|
      t.date :date

      t.timestamps
    end
  end
end
