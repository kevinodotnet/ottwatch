class ParcelScanner < ApplicationJob
  queue_as :default

  def perform
    objectid = Parcel.maximum(:objectid) || 0
    objects_after(objectid).each do |feature|
      parcel_from_api(feature)
      objectid = Parcel.maximum(:objectid)
      return
    end
  end

  def objects_after(objectid)
    json = maps_ottawa_http_get("https://maps.ottawa.ca/proxy/proxy.ashx?https://maps.ottawa.ca/arcgis/rest/services/Property_Parcels/MapServer/2/query?where=OBJECTID>#{objectid}&outFields=*&f=json")
    result = JSON.parse(json)
    result.fetch("features")
  end

  private

  def mercatorToLatLon(mx_long,my_lat)
    # mx_long = -8452764.23;
    # my_lat = 5669659.81;

    if mx_long.abs < 180 && my_lat.abs < 90
      return []
    end

    # 20037508.3427892 - is the full extent of web mercator
    if mx_long.abs > 20037508.3427892 || my_lat.abs > 20037508.3427892
      return []
    end

    x = mx_long;
    y = my_lat;
    num3 = x / 6378137.0;
    # 57.29 = 180/pi
    num4 = num3 * 57.295779513082323;
    num5 = ((num4 + 180.0) / 360.0).floor;
    num6 = num4 - (num5 * 360.0);
    num7 = 1.5707963267948966 - (
      2.0 * Math.atan(
        Math.exp(
          (-1.0 * y) / 6378137.0
        )
      )
    );
    mx_long = num6;
    my_lat = num7 * 57.295779513082323;

    [my_lat, mx_long]
  end
  

  def parcel_from_api(feature)
    attributes = feature.dig("attributes").map{|k, v| [k.downcase, v.to_s.gsub(/ *$/, '')]}.to_h
    attributes = attributes.except("textheight", "textwidth", "textrotation")
    parcel = Parcel.find_or_create_by(objectid: attributes["objectid"])
    parcel.assign_attributes(attributes)

    if rings = feature["geometry"]["rings"]
      raise StandardError.new("bad geometry, parcel_id: #{parcel.id}") if rings.count != 1
      ring_latlon = rings.first.map{|p| mercatorToLatLon(p[0],p[1])}
      geo = GeoRuby::SimpleFeatures::LinearRing.from_coordinates(ring_latlon, 3857)
      # geo = GeoRuby::SimpleFeatures::LinearRing.from_coordinates(rings.first, 3857) # 4326?
      binding.pry
    end

    parcel.save!
    parcel
  end

  def maps_ottawa_http_get(url)
    uri = URI(url)
    req = Net::HTTP::Get.new(uri)
    req['Referer'] = "https://maps.ottawa.ca/wab_stemapp/geoOttawaReport/index_en.html?report=parcelReport"
    res = Net::HTTP.start(uri.hostname, uri.port, use_ssl: uri.scheme == 'https') { |http|
      http.request(req)
    }
    res.body
  end
end