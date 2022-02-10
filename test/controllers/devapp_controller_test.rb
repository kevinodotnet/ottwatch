require "test_helper"

class DevappControllerTest < ActionDispatch::IntegrationTest
  test "should get index" do
    get devapp_index_url
    assert_response :success
  end
end
