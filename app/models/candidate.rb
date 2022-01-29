class Candidate < ApplicationRecord
	belongs_to :election, foreign_key: 'electionid'
	has_many :candidate_returns, foreign_key: 'candidateid'
end
