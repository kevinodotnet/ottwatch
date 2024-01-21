require "test_helper"

class AnnouncementControllerTest < ActionDispatch::IntegrationTest
  test "should get index" do
    get announcement_index_url
    assert_response :success
  end
end
