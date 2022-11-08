class ChangeServiceRequestsTimesToDates < ActiveRecord::Migration[7.0]
  def change
    change_table :service_requests do |t|
      t.change :requested_datetime, :datetime
      t.change :updated_datetime, :datetime
      t.change :expected_datetime, :datetime
    end
  end
end
