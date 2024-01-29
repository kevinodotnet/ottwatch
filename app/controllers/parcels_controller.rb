class ParcelsController < ApplicationController
  before_action :authenticate_user!

  def show
    @parcel = Parcel.find(params[:id])
  end
end
