class DevApp::Entry < ApplicationRecord
  has_many :statuses, class_name: "DevApp::Status"
  has_many :addresses, class_name: "DevApp::Address"
  has_many :documents, class_name: "DevApp::Document"
  has_many :announcements, as: :reference

  def current_status
    if statuses.none?
      # likely due to missing ottawa.ca data, some entries never got an "at least one" status
      # recorded. At runtime, avoid this by returning a mock/stub/placeholder and also persist
      # it so UI elements that power themselves from DB enums on that column continue to work.
      statuses.create(status: "404_missing_data")
    else
      statuses.last
    end
  end
end
