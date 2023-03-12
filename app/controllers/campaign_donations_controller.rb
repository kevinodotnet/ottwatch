class CampaignDonationsController < ApplicationController
  def new
    @donation = CampaignDonation.new(campaign_return_page: CampaignReturnPage.find(params[:campaign_return_page_id]))
  end
end
