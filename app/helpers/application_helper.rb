module ApplicationHelper
  def google_map(center)
    lat = center.lat
    lon = center.lon
    center = [lat, lon].join(',')
    "https://maps.googleapis.com/maps/api/staticmap?center=#{center}&size=300x300&zoom=17"
  end
end
