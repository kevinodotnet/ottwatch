class ServiceRequestsController < ApplicationController
  def index
    relation = if params["before_sr_id"]
      ServiceRequest.where("service_request_id < ?", params["before_id"])
    else
      ServiceRequest.all
    end
    @service_requests = relation.order(id: :desc).limit(100)
  end
end
