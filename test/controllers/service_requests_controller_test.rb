require "test_helper"

class ServiceRequestsControllerTest < ActionDispatch::IntegrationTest
  test "should get index" do
    get service_requests_index_url
    assert_response :success
  end
end
