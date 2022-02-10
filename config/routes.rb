Rails.application.routes.draw do
  get 'home/index'

  get '/election/donation/:id', to: 'elections#donation'

  get 'devapp/index'
  get 'devapp/:app_number', to: 'devapp#show'

  # Defines the root path route ("/")
  root "home#index"
end
