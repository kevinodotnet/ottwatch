class SessionsController < ApplicationController
  protect_from_forgery with: :null_session, only: [:create]

  def new
    render :new
  end

  def create
    user_info = request.env['omniauth.auth']
    binding.pry
    raise user_info # Your own session management should be placed here.
  end
end