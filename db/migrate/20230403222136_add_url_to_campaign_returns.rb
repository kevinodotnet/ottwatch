class AddUrlToCampaignReturns < ActiveRecord::Migration[7.0]
  def change
    add_column :campaign_returns, :url, :string
  end
end
