module V1
  class CandidateReturn < V1::ApplicationRecord
    self.table_name = :candidate_return
    has_one :candidate, class_name: "V1::Candidate", foreign_key: :candidateid
    has_many :donations, class_name: "V1::CandidateDonation", foreign_key: :returnid
  end
end
