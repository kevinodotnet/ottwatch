class CampaignReturn < ApplicationRecord
  belongs_to :candidate
  has_one_attached :pdf
  has_many :campaign_return_pages
end
