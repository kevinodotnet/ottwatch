class CandidateReturn < ApplicationRecord
  belongs_to :candidate
  has_one_attached :pdf
end
