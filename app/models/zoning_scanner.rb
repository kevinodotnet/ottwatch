class ZoningScanner < ApplicationJob
  queue_as :default

  TRANSLATIONS = {
    "shape.area" => "shape_area",
    "shape.len" => "shape_length",
    "tooltip_en" => nil,
    "tooltip_fr" => nil,
    "url_fr" => nil,
  }

  def perform
    objectid = Zoning.where(snapshot_date: current_month).maximum(:objectid) || 0
    again = false
    objects_after(objectid).each do |feature|
      zoning_from_api(feature)
      again = true
    end
    ZoningScanner.perform_later if again
  end

  def objects_after(objectid)
    json = maps_ottawa_http_get("https://maps.ottawa.ca/arcgis/rest/services/Zoning/MapServer/3/query?where=OBJECTID>#{objectid}&orderByFields=OBJECTID&outFields=*&f=json")
    result = JSON.parse(json)
    result.fetch("features")
  end

  private

  def current_month
    Date.today.strftime("%Y-%m-01").to_date
  end

  def zoning_from_api(feature)
    orig_attr = feature.dig("attributes").map{|k, v| [k.downcase, v.to_s.gsub(/ *$/, '')]}.to_h

    attributes = orig_attr.except(*TRANSLATIONS.keys)
    TRANSLATIONS.each do |k,v|
      next if v.nil?
      attributes[v] = orig_attr[k]
    end

    zoning = Zoning.find_or_create_by(snapshot_date: current_month, objectid: attributes["objectid"]) do |zoning|
      zoning.assign_attributes(attributes)
      zoning.geometry_json = feature["geometry"].to_json
    end
  end

  def maps_ottawa_http_get(url)
    uri = URI(url)
    req = Net::HTTP::Get.new(uri)
    # req['Referer'] = "https://maps.ottawa.ca/wab_stemapp/geoOttawaReport/index_en.html?report=parcelReport"
    res = Net::HTTP.start(uri.hostname, uri.port, use_ssl: uri.scheme == 'https') { |http|
      http.request(req)
    }
    res.body
  end
end