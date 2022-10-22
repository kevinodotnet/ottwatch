class EsriScanner < ApplicationJob
  queue_as :default

  def perform
    objects_after(objectid).each do |feature|
      model_from_api(feature)
    end
  end

  private

  def objects_after(objectid)
    result = JSON.parse(Net::HTTP.get(URI("#{query_root}?where=OBJECTID>#{objectid}&outFields=*&f=json")))
    result.fetch("features")
  end

  def query_root
    raise StandardError
  end

  def model
    raise StandardError
  end

  def objectid
    model.maximum(:objectid) || 0
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



# curl 'https://maps.ottawa.ca/arcgis/rest/services/Zoning/MapServer/export?dpi=96&transparent=true&format=png32&layers=show%3A-1%2C-1%2C-1%2C0%2C1%2C2%2C3%2C4&bbox=-8422437.847621517%2C5679173.652036769%2C-8420431.3756292%2C5679862.779619845&bboxSR=102100&imageSR=102100&size=1680%2C577&f=image' \
#   -H 'Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8' \
#   -H 'Accept-Language: en-CA,en;q=0.9,fr-CA;q=0.8,fr;q=0.7,en-US;q=0.6,en-GB;q=0.5' \
#   -H 'Connection: keep-alive' \
#   -H 'Cookie: visid_incap_243507=VSbFza6lQNGLgdoLwygbPliv9mEAAAAAQUIPAAAAAAAblNJhnaQ3kvpC1s0lTPCB; visid_incap_2348304=I8gbgD4tSkW1LwbjaPBJ8Ebi/2EAAAAAQUIPAAAAAABNBzp2ZxGiKHqoudDVdYg0; visid_incap_2134836=DvxTRs1SQzudL4XjOb9hSsmcBGIAAAAAQUIPAAAAAACUy/rEOfOfvQ2Vo63vzPRv; nmstat=37ca09c3-85c3-0fcc-8b25-5d167dd0bcfe; visid_incap_2252431=DOzvR/tjTkmNHKOj76LxKlHPaWIAAAAAQUIPAAAAAABY+r0hKiWgq23d+aJDoLDR; visid_incap_2176400=PjNj1tu9QVGZ37ZTkqA20B1M2WIAAAAAQUIPAAAAAAAvloaum+ROSZJj8b9ARUA3; visid_incap_243513=09bqXnMcThefkVCJ6FGXFw9N2WIAAAAAQUIPAAAAAABmOIQnyeq19YHQ8NxYpGhp; _ga=GA1.2.647300740.1644361579; visid_incap_1747034=eAsoXxZTRDi2yzZHuAqTC8uWHmMAAAAAQUIPAAAAAAC++/C8DTWpBkoO0qLOEMO8; _ga_GTBB95F8N6=GS1.1.1662948917.8.1.1662949724.0.0.0; isfirst_https%3A%2F%2Fmaps.ottawa.ca%2Fgeoottawa%2F=false; incap_ses_889_243507=2EiAB/Tn5nyQNyvO8VxWDO03UGMAAAAAudC7ntKP06LJ7LCfDtoIkg==; nlbi_2348304=LPI9Xufhgyw6lAV0l2uJLQAAAAAcFSLVmXsNDuedXh6/cosl; incap_ses_530_2348304=pRdkOLPWNgoCcFsM9/BaBzDLUmMAAAAAVSKjmGeIeTO8nbZohbfOjA==; _gid=GA1.2.1997615598.1666470508' \
#   -H 'Referer: https://maps.ottawa.ca/geoottawa/' \
#   -H 'Sec-Fetch-Dest: image' \
#   -H 'Sec-Fetch-Mode: no-cors' \
#   -H 'Sec-Fetch-Site: same-origin' \
#   -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/106.0.0.0 Safari/537.36' \
#   -H 'sec-ch-ua: "Chromium";v="106", "Google Chrome";v="106", "Not;A=Brand";v="99"' \
#   -H 'sec-ch-ua-mobile: ?0' \
#   -H 'sec-ch-ua-platform: "macOS"' \
#   --compressed