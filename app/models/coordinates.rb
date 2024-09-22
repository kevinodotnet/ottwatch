class Coordinates
  def initialize(lat, lon, precision: 5)
    @precision = precision
    @full_lat = lat
    @full_lon = lon
    # Accuracy  | DD.dddddd° Decimal places
    # 10m       | 4
    # 1m        | 5
    # 0.1m      | 6
    # https://wiki.openstreetmap.org/wiki/Precision_of_coordinates
    @lat = lat.round(precision)
    @lon = lon.round(precision)
  end

  attr_reader :lat, :lon, :full_lat, :full_lon, :precision
end
