class DevApp::Document < ApplicationRecord
  belongs_to :entry, class_name: "DevApp::Entry", foreign_key: "entry_id"
end
