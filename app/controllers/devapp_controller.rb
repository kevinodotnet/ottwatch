class DevappController < ApplicationController
  def index
    relation = if params["before_id"]
      DevApp::Entry.where("id < ?", params["before_id"])
    else
      DevApp::Entry.all
    end

    @devapps = relation.order(updated_at: :desc).limit(100)

    # binding.pry
  end

  def show
    @entry = DevApp::Entry.where(app_number: params[:app_number]).includes(:statuses, :addresses, :documents).first
  end
end
