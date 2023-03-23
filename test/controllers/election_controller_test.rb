require "test_helper"

class ElectionControllerTest < ActionDispatch::IntegrationTest
  test "show on id=listDonations returns 404" do
    assert_raises ActionController::RoutingError do
      get "/election/listDonations"
    end
  end
end
