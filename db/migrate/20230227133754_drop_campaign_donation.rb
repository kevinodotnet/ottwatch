class DropCampaignDonation < ActiveRecord::Migration[7.0]
  def change
    drop_table :campaign_donations
  end
end
