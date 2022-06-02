require "test_helper"

class ParcelsControllerTest < ActionDispatch::IntegrationTest
  test "should get show" do
    get parcels_show_url
    assert_response :success
  end
end
