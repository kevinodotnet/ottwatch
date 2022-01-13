class Election < ApplicationRecord
	self.table_name = 'election'
	has_many :candidates, foreign_key: 'electionid'
end
