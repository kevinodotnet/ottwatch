require "test_helper"

class DevappControllerTest < ActionDispatch::IntegrationTest
  test "#show for non-existant devapp fails cleanly" do
    assert_raises ActionController::RoutingError do
      get "/devapp/DOES_NOT_EXIST"
    end
  end
end
