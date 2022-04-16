class AddMeetingToMeetingItems < ActiveRecord::Migration[7.0]
  def change
    add_reference :meeting_items, :meeting, null: false, foreign_key: true
  end
end
