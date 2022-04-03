class CreateGlobalControls < ActiveRecord::Migration[7.0]
  def change
    create_table :global_controls do |t|
      t.string :name, index: { unique: true }
      t.string :value

      t.timestamps
    end
  end
end
