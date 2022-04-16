class ChangeMeetingStartTime < ActiveRecord::Migration[7.0]
  def change
    change_table :meetings do |t|
      t.change :start_time, :datetime
    end
  end
end
