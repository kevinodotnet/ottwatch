class ParcelsController < ApplicationController
  def show
    @parcel = Parcel.find_by_objectid(params[:objectid])
  end
end
