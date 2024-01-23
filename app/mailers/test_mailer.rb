class TestMailer < ApplicationMailer
  default from: 'no-reply@ottwatch.ca'

  def welcome_email
    @url = 'https://ottwatch.ca/?start=1'
    mail(to: 'kevinodotnet@gmail.com', subject: 'Welcome to My Awesome Site')
  end
end
