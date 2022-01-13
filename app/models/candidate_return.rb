class CandidateReturn < ApplicationRecord
	self.table_name = 'candidate_return'
	belongs_to :candidate, foreign_key: 'candidateid'
	has_many :candidate_donations, foreign_key: 'returnid'
end
