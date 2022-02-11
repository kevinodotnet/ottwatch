module ApplicationHelper
  def google_map(center)
    lat = center.lat
    lon = center.lon
    center = [lat, lon].join(',')
    "https://maps.googleapis.com/maps/api/staticmap?key=#{google_maps_api_key}&center=#{center}&size=300x300&zoom=13"
  end

  private

  def google_maps_api_key
    ENV["GOOGLE_MAPS_API_KEY"]
  end
end
