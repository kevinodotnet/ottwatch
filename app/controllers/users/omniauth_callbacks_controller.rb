class Users::OmniauthCallbacksController < Devise::OmniauthCallbacksController
  def google_oauth2
    generic_handler
  end

  def github
    generic_handler
  end

  def generic_handler
    Rails.logger.info("omniauth.auth: #{request.env["omniauth.auth"]}")
    @user = User.from_omniauth(request.env["omniauth.auth"])

    sign_in_and_redirect @user, event: :authentication #this will throw if @user is not activated
    set_flash_message(:notice, :success) if is_navigational_format?
  end

  def failure
    redirect_to root_path
  end
end
