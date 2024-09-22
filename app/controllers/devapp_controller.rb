class DevappController < ApplicationController
  def index
    limit = params["limit"] || 10
    relation = if params["before_id"]
      DevApp::Entry.where("id < ?", params["before_id"])
    else
      DevApp::Entry.all
    end

    @devapps = relation.includes(:addresses).order(updated_at: :desc).limit(limit)
  end

  def show
    @entry = DevApp::Entry.where(app_number: params[:app_number]).includes(:statuses, :addresses, :documents).first
    return render plain: "404 Not Found", status: 404 unless @entry
  end

  def map
    ottawa_city_hall = Coordinates.new(45.420906, -75.689374)
    @initial_lat = ottawa_city_hall.lat
    @initial_lon = ottawa_city_hall.lon

    @statuses = DevApp::Status.distinct.pluck(:status).sort
    @app_types = DevApp::Entry.distinct.pluck(:app_type).sort
  end

  def map_data
    geojson = {
      type: "FeatureCollection",
      features: DevApp::Entry.includes(:addresses, :statuses).filter_map do |app|
        next unless (coordinates = app.addresses.first&.coordinates)

        {
          type: "Feature",
          geometry: {
            type: "Point",
            coordinates: [coordinates.lon, coordinates.lat],
          },
          properties: {
            id: app.id,
            app_number: app.app_number,
            app_type: app.app_type,
            status: app.statuses.last&.status,
            description: app.desc.truncate(140, separator: /\s/),
            url: url_for(controller: 'devapp', action: 'show', app_number: app.app_number)
          }
        }
      end
    }

    render json: geojson
  end
end
