class AlterTitleOnMeetingItems < ActiveRecord::Migration[7.0]
  def up
    change_table :meeting_items do |t|
      t.change :title, :text
    end
  end
end
