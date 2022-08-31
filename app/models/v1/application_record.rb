module V1
  class ApplicationRecord < ::ApplicationRecord
    self.abstract_class = true
    
    connects_to database: { writing: :v1, reading: :v1 }
  end
end