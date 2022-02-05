module V1
	class Election < ApplicationRecord
		self.table_name = 'v1_elections'
		has_many :v1_candidates, class_name: 'V1::Candidate', foreign_key: 'electionid'
	end
end