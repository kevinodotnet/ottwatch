class Candidate < ApplicationRecord
  belongs_to :election
  has_many :campaign_returns
end
