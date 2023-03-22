Rails.application.routes.draw do
  get 'campaign_donations/new'
  resources :candidates, only: [:show] #  'candidates/show'
  get 'service_requests/index'
  devise_for :users, controllers: { omniauth_callbacks: 'users/omniauth_callbacks' }

  get 'home/index'
  root "home#index"

  get 'parcels/:objectid', to: 'parcels#show'

  get 'devapp/index'
  get 'devapp/:app_number', to: 'devapp#show'

  get 'lobbying/index'
  get 'lobbying/:id', to: 'lobbying#show'

  get 'meeting/index'
  get 'meeting/:reference_id', to: 'meeting#show'

  # get 'election/donation/:id', to: 'election#show_legacy_donation'
  get 'election/index', to: 'election#index'
  get 'election/legacy', to: 'election#legacy_index'
  get 'election/:id', to: 'election#show'
  get 'election/legacy/donation/:id', to: 'election#show_legacy_donation'

  resources :campaign_donations
  resources :campaign_returns
  resources :campaign_return_pages, only: [:show] do
    member do
      get :rotate
      post :create_donation
    end
  end


  get 'team/index'
end