class MeetingItem < ApplicationRecord
  belongs_to :meeting
  has_many :documents, class_name: "MeetingItemDocument"
end
