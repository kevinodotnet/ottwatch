require "test_helper"

class CandidatesControllerTest < ActionDispatch::IntegrationTest
  test "should get show" do
    get candidates_show_url
    assert_response :success
  end
end
