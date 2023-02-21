class CreateCampaignDonations < ActiveRecord::Migration[7.0]
  def change
    create_table :campaign_donations do |t|
      t.references :campaign_return_page, null: false, foreign_key: true
      t.string :name
      t.string :address
      t.string :city
      t.string :province
      t.string :postal
      t.float :x
      t.float :y
      t.decimal :amount, precision: 10, scale: 2
      t.date :donation_date

      t.timestamps
    end
  end
end
