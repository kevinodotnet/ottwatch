class CreateMeetingItemDocuments < ActiveRecord::Migration[7.0]
  def change
    create_table :meeting_item_documents do |t|
      t.string :reference_id
      t.string :title
      t.references :meeting_item, null: false, foreign_key: true

      t.timestamps
    end
  end
end
