class CandidateDonation < ApplicationRecord
	belongs_to :v1_candidate_return, foreign_key: 'returnid'
	self.inheritance_column = :_type_disabled
	self.table_name = 'v1_candidate_donations'
end
