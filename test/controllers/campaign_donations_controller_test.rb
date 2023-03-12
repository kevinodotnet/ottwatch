require "test_helper"

class CampaignDonationsControllerTest < ActionDispatch::IntegrationTest
  test "should get new" do
    get campaign_donations_new_url
    assert_response :success
  end
end
