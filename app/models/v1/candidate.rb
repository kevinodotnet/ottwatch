module V1
  class Candidate < V1::ApplicationRecord
    self.table_name = :candidate
    has_one :election, class_name: "V1::Election", foreign_key: :electionid
    has_many :returns, class_name: "V1::CandidateReturn", foreign_key: :candidateid
  end
end
