class CampaignReturnPage < ApplicationRecord
  belongs_to :campaign_return
  has_one_attached :image
end
