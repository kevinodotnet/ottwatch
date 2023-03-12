class CreateCampaignDonations < ActiveRecord::Migration[7.0]
  def change
    create_table :campaign_donations do |t|
      t.references :campaign_return_page, null: false, foreign_key: true
      t.string :name
      t.string :address
      t.string :city
      t.string :prov
      t.string :postal
      t.decimal :amount, precision: 10, scale: 2
      t.decimal :x, precision: 10, scale: 4
      t.decimal :y, precision: 10, scale: 4
      t.date :donated_on
      t.boolean :redacted

      t.timestamps
    end
  end
end
