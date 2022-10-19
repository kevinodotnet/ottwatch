class Consultation < ApplicationRecord
  has_many :announcements, as: :reference
end
