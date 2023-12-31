require "test_helper"

class DevappControllerTest < ActionDispatch::IntegrationTest
  test "#index works" do
    assert_raises ActionController::RoutingError do
      assert_equal 200, (get "/devapp/index")
    end
  end
end
