class Memo < ApplicationRecord
  has_many :announcements, as: :reference

  validates :title, presence: true
  validates :department, presence: true
  validates :issued_date, presence: true
  validates :url, presence: true, uniqueness: true

  scope :recent, -> { order(issued_date: :desc) }
  scope :by_department, ->(department) { where(department: department) }
end
