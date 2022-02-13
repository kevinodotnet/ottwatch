class Announcement < ApplicationRecord
  belongs_to :reference, polymorphic: true
end
