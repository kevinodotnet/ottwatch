class Election < ApplicationRecord
  has_many :candidates
  has_many :announcements, as: :reference

  def self.ward_name(id)
    [
      "Orléans East-Cumberland",
      "Orléans West-Innes",
      "Barrhaven West",
      "Kanata North",
      "West Carleton-March",
      "Stittsville",
      "Bay",
      "College",
      "Knoxdale-Merivale",
      "Gloucester-Southgate",
      "Beacon Hill-Cyrville",
      "Rideau-Vanier",
      "Rideau-Rockcliffe",
      "Somerset",
      "Kitchissippi",
      "River",
      "Capital",
      "Alta Vista",
      "Orléans South-Navan",
      "Osgoode",
      "Rideau-Jock",
      "Riverside South-Findlay Creek",
      "Kanata South",
      "Barrhaven East",
    ][id-1]
  end
end
