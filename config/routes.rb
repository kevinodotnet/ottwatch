Rails.application.routes.draw do
  get 'announcement/index'
  get 'transpo/show_stop'

  devise_for :users, controllers: { omniauth_callbacks: 'users/omniauth_callbacks' }

  get 'home/index'
  root "home#index"

  get 'traffic_cameras', to: 'traffic_cameras#index'
  get 'traffic_cameras/map', to: 'traffic_cameras#map', as: 'traffic_cameras_map'
  get 'traffic_cameras/map_data', to: 'traffic_cameras#map_data', as: 'traffic_cameras_map_data'
  get 'traffic_cameras/:id', to: 'traffic_cameras#show', as: 'traffic_camera'
  get 'traffic_cameras/:id/capture/:time_ms.jpg', to: 'traffic_cameras#capture', as: 'traffic_camera_capture', constraints: { time_ms: /\d+/ }

  get 'parcels/:id', to: 'parcels#show'

  get 'devapp/map', to: 'devapp#map'
  get 'devapp/map_data', to: 'devapp#map_data'
  get 'devapp/index'
  get 'devapp/:app_number', to: 'devapp#show', as: 'devapp'

  get 'lobbying/index'
  get 'lobbying/:id', to: 'lobbying#show'

  get 'meeting/index'
  get 'meeting/:reference_id', to: 'meeting#show'

  get 'team/index'
end
