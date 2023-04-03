class DevappController < ApplicationController
  def index
    limit = params["limit"] || 10
    relation = if params["before_id"]
      DevApp::Entry.where("id < ?", params["before_id"])
    else
      DevApp::Entry.all
    end

    @devapps = relation.includes(:addresses).order(updated_at: :desc).limit(limit)
  end

  def show
    @entry = DevApp::Entry.where(app_number: params[:app_number]).includes(:statuses, :addresses, :documents).first
    return render plain: "404 Not Found", status: 404 unless @entry
  end
end
