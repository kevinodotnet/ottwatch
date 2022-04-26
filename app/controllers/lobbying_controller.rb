class LobbyingController < ApplicationController
  def index
    relation = LobbyingUndertaking.all
    if params["before_id"]
      relation = relation.where("id < ?", params["before_id"])
    end
    if params["before_created_at"]
      relation = relation.where("created_at <= ?", params["before_created_at"])
    end
    @undertakings = relation.order(created_at: :desc, id: :desc).limit(100)
  end

  def show
    @undertaking = LobbyingUndertaking.find(params[:id])
    raise ActionController::RoutingError.new('Lobbying Entry Not Found') unless @undertaking
  end
end
