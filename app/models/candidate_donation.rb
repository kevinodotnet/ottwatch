class CandidateDonation < ApplicationRecord
	belongs_to :candidate_return, foreign_key: 'returnid'
	self.inheritance_column = :_type_disabled
end
