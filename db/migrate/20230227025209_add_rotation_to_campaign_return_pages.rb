class AddRotationToCampaignReturnPages < ActiveRecord::Migration[7.0]
  def change
    add_column :campaign_return_pages, :rotation, :integer, default: 0
  end
end
