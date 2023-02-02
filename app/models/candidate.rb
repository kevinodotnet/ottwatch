class Candidate < ApplicationRecord
  belongs_to :election
  has_many :candidate_returns, class_name: "V1::CandidateReturn", foreign_key: :candidateid
end
