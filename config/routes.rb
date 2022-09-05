Rails.application.routes.draw do
  get 'parcels/:objectid', to: 'parcels#show'
  get 'home/index'

  get '/election/donation/:id', to: 'elections#donation'

  get 'devapp/index'
  get 'devapp/:app_number', to: 'devapp#show'

  get 'lobbying/index'
  get 'lobbying/:id', to: 'lobbying#show'

  get 'meeting/index'
  get 'meeting/:reference_id', to: 'meeting#show'

  get 'election/legacy', to: 'election#legacy_index'
  get 'election/:id', to: 'election#show'
  # Defines the root path rout  e ("/")
  root "home#index"
end
