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
    raise ActionController::RoutingError.new('Development Application Not Found') unless @entry
  end
end
