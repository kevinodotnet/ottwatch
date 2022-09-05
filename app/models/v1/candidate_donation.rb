module V1
  class CandidateDonation < V1::ApplicationRecord
    self.table_name = :candidate_donation
    self.inheritance_column = :_type_disabled
    belongs_to :candidate_return, class_name: "V1::CandidateReturn", foreign_key: :returnid
  end
end
