class Parcel < ApplicationRecord
  def center
    t = tmp_polygon
    [
      t.map{|p| p[0]}.sum / t.count,
      t.map{|p| p[1]}.sum / t.count,
    ]
  end

  def tmp_polygon
    JSON.parse(geometry_json)["rings"].first.map do |p|
      x = p[0]
      y = p[1]
    
      return nil if x.abs < 180 && y.abs < 90
      # 20037508.3427892 - is the full extent of web mercator
      return nil if x.abs > 20037508.3427892 || y.abs > 20037508.3427892
    
      num3 = x / 6378137.0;
      # 57.29 = 180/pi
      num4 = num3 * 57.295779513082323;
      num5 = (((num4 + 180.0) / 360.0)).floor;
      num6 = num4 - (num5 * 360.0);
      num7 = 1.5707963267948966 - (2.0 * Math.atan(Math.exp((-1.0 * y) / 6378137.0)));
    
      lon = num6;
      lat = num7 * 57.295779513082323;

      [lat, lon]
    end
  end
end