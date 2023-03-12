class CampaignReturnPagesController < ApplicationController
  
  def show
    @cr_page = CampaignReturnPage.find(params[:id])
    relation = @cr_page.campaign_return.campaign_return_pages
    @prev_cr_page = relation.where('id < ?', @cr_page.id).order(id: :desc).limit(1).first
    @next_cr_page = relation.where('id > ?', @cr_page.id).order(:id).limit(1).first
  end

  def rotate
    @cr_page = CampaignReturnPage.find(params[:id])
    @cr_page.rotation += params[:dir].to_i
    @cr_page.save!

    redirect_to campaign_return_page_path(@cr_page)
  end

  def create_donation
    crp = CampaignReturnPage.find(params.require(:id))
    crp.campaign_donations.create!(x: params.require(:x).to_i, y: params.require(:y).to_i)
  end
end
