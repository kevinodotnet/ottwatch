require "test_helper"

class ElectionControllerTest < ActionDispatch::IntegrationTest
  test "show on id=listDonations returns 404" do
    get "/election/listDonations"
    assert_response :not_found
  end
end
