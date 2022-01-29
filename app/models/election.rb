class Election < ApplicationRecord
	has_many :candidates, foreign_key: 'electionid'
end
