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

    @coordinates = @entry.addresses.first&.coordinates
  end

  def map
    ottawa_city_hall = Coordinates.new(45.420906, -75.689374)
    @initial_lat = ottawa_city_hall.lat
    @initial_lon = ottawa_city_hall.lon

    @statuses = DevApp::Status.distinct.pluck(:status) - ["404_missing_data"]
    @app_types = DevApp::Entry.distinct.pluck(:app_type).sort
  end

  def map_data
    geojson = {
      type: "FeatureCollection",
      # TODO: group the data by unique address and update the popup to show the different apps at the address.
      # Unsure at the moment whether the map is move valuable showing distinct applications (pin to first address) or
      # distinct list of addresses and possible displaying the same app multiple times.
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
            status: app.current_status.status,
            description: app.desc&.truncate(140, separator: /\s/),
            url: url_for(controller: 'devapp', action: 'show', app_number: app.app_number)
          }
        }
      end
    }

    render json: geojson
  end
end
