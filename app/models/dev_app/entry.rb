class DevApp::Entry < ApplicationRecord
  has_many :addresses, class_name: "DevApp::Address"
end
