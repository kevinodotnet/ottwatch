class MeetingController < ApplicationController
  def index
    relation = if params["before_id"]
      Meeting.where("id < ?", params["before_id"])
    else
      Meeting.all
    end
    @meetings = relation.order(id: :desc).limit(50)
  end

  def show
    @meeting = Meeting.find_by_reference_id(params[:reference_id])
  end
end
