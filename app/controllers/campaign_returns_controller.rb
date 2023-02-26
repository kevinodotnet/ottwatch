class CampaignReturnsController < ApplicationController
  # TODO
  # before_action :require_login
  # before_action :authenticate_user!

  def new
    @cr = CampaignReturn.new(candidate: Candidate.find(params[:candidate_id]))
  end
  
  def create
    candidate_id = params.require(:campaign_return).require(:candidate_id)
    pdf_upload = params.require(:campaign_return).require(:pdf)
    cr = CampaignReturnScanner.scan(candidate: Candidate.find(candidate_id), pdf_file: pdf_upload)
    redirect_to campaign_return_path(cr), :notice => "Campaign Return successfully uploaded"
  end

  def show
    @campaign_return = CampaignReturn.find(params[:id])
  end
end
