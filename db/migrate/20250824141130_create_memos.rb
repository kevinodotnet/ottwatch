class CreateMemos < ActiveRecord::Migration[8.0]
  def change
    create_table :memos do |t|
      t.text :title
      t.string :department
      t.date :issued_date
      t.string :sender
      t.text :content
      t.string :url
      t.string :reference_id

      t.timestamps
    end
  end
end
