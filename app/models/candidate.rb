class Candidate < ApplicationRecord
	self.table_name = 'candidate'
	belongs_to :election, foreign_key: 'electionid'
	has_many :candidate_returns, foreign_key: 'candidateid'
end
