class CreateModelComparisons < ActiveRecord::Migration[7.1]
  def change
    create_table :model_comparisons do |t|
      t.references :model1, polymorphic: true, null: false
      t.references :model2, polymorphic: true, null: false

      # t.index [:model1, :model2]

      t.timestamps
    end
  end
end
