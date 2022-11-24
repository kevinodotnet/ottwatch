class CreateCampaignReturnPages < ActiveRecord::Migration[7.0]
  def change
    create_table :campaign_return_pages do |t|
      t.references :campaign_return, null: false, foreign_key: true
      t.integer :page

      t.timestamps
    end
  end
end
