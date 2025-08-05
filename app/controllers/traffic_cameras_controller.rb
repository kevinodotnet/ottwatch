class TrafficCamerasController < ApplicationController
  def index
    @traffic_cameras = TrafficCamera.all.order(:id)
  end

  def show
    @traffic_camera = TrafficCamera.find(params[:id])
  end

  def map
    ottawa_city_hall = Coordinates.new(45.420906, -75.689374)
    @initial_lat = ottawa_city_hall.lat
    @initial_lon = ottawa_city_hall.lon
  end

  def map_data
    geojson = {
      type: "FeatureCollection",
      features: TrafficCamera.all.filter_map do |camera|
        next unless camera.lat && camera.lon

        {
          type: "Feature",
          geometry: {
            type: "Point",
            coordinates: [camera.lon, camera.lat],
          },
          properties: {
            id: camera.id,
            name: camera.name,
            camera_number: camera.camera_number,
            camera_owner: camera.camera_owner,
            url: url_for(controller: 'traffic_cameras', action: 'show', id: camera.id)
          }
        }
      end
    }

    render json: geojson
  end

  def capture
    # params: { id: '1010' }
    # params: { time_ms: '1717234234' }
    @traffic_camera = TrafficCamera.find(params[:id])
    capture = @traffic_camera.captures.detect{|c| c[:time_ms] == params[:time_ms].to_i}
    
    if capture
      response.headers['Cache-Control'] = 'public, max-age=86400'
      send_data(File.read(capture[:file]), type: 'image/jpeg', disposition: 'inline', filename: "cam_#{params[:id]}_#{params[:time_ms]}.jpg")
    else
      # No capture found - redirect to camera page
      redirect_to traffic_camera_path(@traffic_camera)
    end
  end
end
