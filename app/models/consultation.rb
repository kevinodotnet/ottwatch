class Consultation < ApplicationRecord
  has_many :announcements, as: :reference

  def full_href
    "https://engage.ottawa.ca#{href}"
  end
end
