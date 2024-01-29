class ParcelScanner < ApplicationJob
  queue_as :default

  def perform
    objectid = Parcel.where(snapshot_date: current_month).maximum(:objectid) || 0
    again = false
    objects_after(objectid).each do |feature|
      parcel_from_api(feature)
      again = true
    end
    ParcelScanner.perform_later if again
  end

  def objects_after(objectid)
    json = maps_ottawa_http_get("https://maps.ottawa.ca/proxy/proxy.ashx?https://maps.ottawa.ca/arcgis/rest/services/Property_Parcels/MapServer/2/query?where=OBJECTID>#{objectid}&orderByFields=OBJECTID&outFields=*&f=json")
    result = JSON.parse(json)
    result.fetch("features")
  end

  private

  def current_month
    Date.today.strftime("%Y-%m-01").to_date
  end

  def parcel_from_api(feature)
    attributes = feature.dig("attributes").map{|k, v| [k.downcase, v.to_s.gsub(/ *$/, '')]}.to_h
    attributes = attributes.except("textheight", "textwidth", "textrotation")
    parcel = Parcel.find_or_create_by(snapshot_date: current_month, objectid: attributes["objectid"])
    parcel.assign_attributes(attributes)
    parcel.geometry_json = feature["geometry"].to_json
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