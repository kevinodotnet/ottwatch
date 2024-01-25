class Users::OmniauthCallbacksController < Devise::OmniauthCallbacksController
  def google_oauth2
    generic_handler("google")
  end

  def github
    generic_handler("github")
  end

  def generic_handler(kind)
    Rails.logger.info("omniauth.auth: #{request.env["omniauth.auth"]}")
    @user = User.from_omniauth(request.env["omniauth.auth"])
    Rails.logger.info(
      msg: "user",
      user_id: @user.id,
      user_email: @user.email,
      provider: @user.provider,
      uuid: @user.uid,
      persisted: @user.persisted?
      errors: @user.errors.full_messages
    )

    if @user.errors.any?
      flash.alert = @user.errors.full_messages
      redirect_to root_path
    else
      sign_in_and_redirect @user, event: :authentication #this will throw if @user is not activated
      set_flash_message(:notice, :success, kind: kind) if is_navigational_format?
    end
  end

  def failure
    redirect_to root_path
  end
end