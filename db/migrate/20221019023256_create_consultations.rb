class CreateConsultations < ActiveRecord::Migration[7.0]
  def change
    create_table :consultations do |t|
      t.string :title
      t.string :href
      t.string :status

      t.timestamps
    end
  end
end
