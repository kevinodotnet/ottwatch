Rails.application.routes.draw do
  get 'home/index'

  get '/election/donation/:id', to: 'elections#donation'

  get 'devapp/index'
  get 'devapp/:app_number', to: 'devapp#show'

  get 'meeting/index'
  get 'meeting/:reference_id', to: 'meeting#show'

  # Defines the root path rout  e ("/")
  root "home#index"
end
