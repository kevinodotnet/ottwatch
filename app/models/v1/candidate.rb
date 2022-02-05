module V1
	class Candidate < ApplicationRecord
		self.table_name = 'v1_candidates'
		belongs_to :v1_election, foreign_key: 'electionid'
		#has_many :v1_candidate_returns, foreign_key: 'candidateid'
	end
end