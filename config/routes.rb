Rails.application.routes.draw do
  get 'announcement/index'
  get 'transpo/show_stop'
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