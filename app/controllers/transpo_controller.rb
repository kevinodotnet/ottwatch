class TranspoController < ApplicationController
  def self.get_next_trips_for_stop_all_routes(stop_no)
    app_id = ENV["OCTRANSPO_APP_ID"]
    api_key = ENV["OCTRANSPO_APP_KEY"]

    url = "https://api.octranspo1.com/v2.0/GetNextTripsForStopAllRoutes?appID=#{app_id}&apiKey=#{api_key}&stopNo=#{stop_no}&format=JSON"
    data = Net::HTTP.get(URI(url))
    Rails.logger.info(data)
    data
  end

  def self.stop_trips(stop_no)
    data = get_next_trips_for_stop_all_routes(stop_no)
    data = JSON.parse(data)["GetRouteSummaryForStopResult"]

    routes = data["Routes"]["Route"]
    routes = [routes] if routes.is_a?(Hash)
    routes = routes.map do |r|
      trips = if r["Trips"].is_a?(Array)
        r["Trips"]
      elsif r["Trips"].is_a?(Hash)
        if r["Trips"]["Trip"]
          r["Trips"]["Trip"]
        else
          [r["Trips"]]
        end
      end

      trips = trips.map do |t|
        age = (t["AdjustmentAge"].to_f * 60).round
        {
          # lat: t["Latitude"],
          # lon: t["Longitude"],
          # speed: t["GPSSpeed"],
          dest: t["TripDestination"],
          in: t["AdjustedScheduleTime"],
          age: age,
          per: (age < 0 ? :sched : :gps)
        }
      end
      {
        no: r["RouteNo"],
        heading: r["RouteHeading"],
        trips: trips
      }
    end
    {
      no: data["StopNo"],
      desc: data["StopDescription"],
      routes: routes
    }
  end

  def show_stop
    @stop_data = self.class.stop_trips(params[:stop_no]) if params[:stop_no]
    if params[:stop_routes] && params[:stop_routes].size > 0
      keep_routes = params[:stop_routes].split(" ")
      @stop_data[:routes] = @stop_data[:routes].select{|r| keep_routes.include?(r[:no])}
    end
  end
end
