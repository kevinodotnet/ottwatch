class AddWithdrewToCandidates < ActiveRecord::Migration[7.0]
  def change
    add_column :candidates, :withdrew, :boolean
  end
end
