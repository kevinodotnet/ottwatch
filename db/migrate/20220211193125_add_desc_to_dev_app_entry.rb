class AddDescToDevAppEntry < ActiveRecord::Migration[7.0]
  def change
    add_column(:dev_app_entries, :desc, :string)
  end
end
