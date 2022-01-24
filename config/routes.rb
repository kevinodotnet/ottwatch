Rails.application.routes.draw do
  get 'home/index'

  get '/election/donation/:id', to: 'elections#donation'

  # Defines the root path route ("/")
  root "home#index"
end
