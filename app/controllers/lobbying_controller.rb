class LobbyingController < ApplicationController
  def index
    relation = if params["before_id"]
      LobbyingUndertaking.where("id < ?", params["before_id"])
    else
      LobbyingUndertaking.all
    end

    @undertakings = relation.order(updated_at: :desc).limit(100)
  end

  def show
    @undertaking = LobbyingUndertaking.find(id: params[:id])
    raise ActionController::RoutingError.new('Lobbying Entry Not Found') unless @undertaking
  end
end
