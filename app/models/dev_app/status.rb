class DevApp::Status < ApplicationRecord
  belongs_to :entry, class_name: "DevApp::Entry"
end
