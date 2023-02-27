class CampaignReturnPagesController < ApplicationController
  def show
    @cr_page = CampaignReturnPage.find(params[:id])
  end
end
