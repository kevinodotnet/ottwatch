require "test_helper"

class DevApp::EntryTest < ActiveSupport::TestCase
  test "#current_status returns sane default if no statuses exist in DB" do
    entry = DevApp::Entry.first
    entry.statuses.destroy_all
    assert_changes -> { DevApp::Status.count } do
      assert_equal "404_missing_data", entry.current_status.status
    end
  end
end
