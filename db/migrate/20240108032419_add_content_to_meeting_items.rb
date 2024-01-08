class AddContentToMeetingItems < ActiveRecord::Migration[7.0]
  def change
    add_column :meeting_items, :content, :text
  end
end
