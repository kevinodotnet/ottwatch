class Meeting < ApplicationRecord
  belongs_to :committee
  has_many :items, class_name: "MeetingItem"
end
