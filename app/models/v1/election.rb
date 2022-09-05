module V1
  class Election < V1::ApplicationRecord
    self.table_name = :election
    has_many :candidates, class_name: "V1::Candidate", foreign_key: :electionid
  end
end
