class CreateMemos < ActiveRecord::Migration[8.0]
  def change
    create_table :memos do |t|
      t.text :title
      t.string :department
      t.date :issued_date
      t.text :content
      t.string :url

      t.timestamps
    end
  end
end
