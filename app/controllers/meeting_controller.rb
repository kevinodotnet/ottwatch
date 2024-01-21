class MeetingController < ApplicationController
  def index
    relation = if params["before_id"]
      m = Meeting.find(params["before_id"])
      Meeting.where("start_time < ? and id < ?", m.start_time, params["before_id"])
    else
      Meeting.all
    end
    @meetings = relation.order(start_time: :desc, id: :desc).limit(50)
  end

  def show
    @meeting = Meeting.where(reference_id: params[:reference_id])
      .or(Meeting.where(reference_guid: params[:reference_id]))
      .includes(items: :documents)
      .first
  end
end
