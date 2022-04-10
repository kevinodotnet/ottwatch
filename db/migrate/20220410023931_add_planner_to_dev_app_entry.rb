class AddPlannerToDevAppEntry < ActiveRecord::Migration[7.0]
  def change
    add_column :dev_app_entries, :planner_first_name, :string
    add_column :dev_app_entries, :planner_last_name, :string
    add_column :dev_app_entries, :planner_phone, :string
    add_column :dev_app_entries, :planner_email, :string
  end
end
