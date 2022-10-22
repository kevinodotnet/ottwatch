class ZoningScanner < EsriScanner

  private

  def query_root
    "https://maps.ottawa.ca/arcgis/rest/services/Zoning/MapServer/3/query"
  end

  def model
    Zoning
  end

  def model_from_api(feature)
    raise StandardError
    attributes = feature.dig("attributes").map{|k, v| [k.downcase, v.to_s.gsub(/ *$/, '')]}.to_h
    attributes = attributes.except("textheight", "textwidth", "textrotation")
    record = model.find_or_create_by(objectid: attributes["objectid"])
    record.assign_attributes(attributes)
    record.geometry_json = feature["geometry"].to_json
    record.save!
    record
  end
end