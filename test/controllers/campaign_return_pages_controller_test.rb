require "test_helper"

class CampaignReturnPagesControllerTest < ActionDispatch::IntegrationTest
  test "should get show" do
    get campaign_return_pages_show_url
    assert_response :success
  end
end
