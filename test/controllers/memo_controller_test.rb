require "test_helper"

class MemoControllerTest < ActionDispatch::IntegrationTest
  test "should get index" do
    get memo_index_url
    assert_response :success
  end

  test "should get show" do
    get memo_show_url
    assert_response :success
  end
end
