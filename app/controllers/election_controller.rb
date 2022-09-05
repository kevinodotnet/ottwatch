class ElectionController < ApplicationController
  def show
    @election = Election.find(params[:id])
  end

  def show_legacy_donation
    @donation = V1::CandidateDonation.find(params[:id])
  end

  def legacy_index
    @donations = []
    relation = V1::CandidateDonation.all

    if params[:candidate_id]
      filtered = true
      relation = relation.joins(:candidate_return).where(candidate_return: { candidateid: params[:candidate_id] })
    end

    if params[:postal]
      filtered = true
      relation = relation.where("postal like ?", "%#{params[:postal]}%")
    end

    if params[:name]
      filtered = true
      relation = relation.where("name like ?", "%#{params[:name]}%")
    end

    if filtered
      @donations = relation.includes(candidate_return: [candidate: [:election]]).order("rand()")
    end
  end
end
