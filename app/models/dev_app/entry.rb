class DevApp::Entry < ApplicationRecord
  has_many :statuses, class_name: "DevApp::Status"
  has_many :addresses, class_name: "DevApp::Address"
  has_many :documents, class_name: "DevApp::Document"

  def current_status
    statuses.order(:id).last
  end
end
