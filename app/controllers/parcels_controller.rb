class ParcelsController < ApplicationController
  before_action :authenticate_user!

  def show
    @parcel = Parcel.find_by_objectid(params[:objectid])
  end
end
