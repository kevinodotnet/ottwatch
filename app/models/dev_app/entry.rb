class DevApp::Entry < ApplicationRecord
  has_many :addresses, class_name: "DevApp::Address"
  has_many :documents, class_name: "DevApp::Document"
end
