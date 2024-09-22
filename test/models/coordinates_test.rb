require "test_helper"

class CoordinatesTest < ActiveSupport::TestCase
  test "lat and lon are rounded to 5 decimal places for meter precision by default" do
    coordinates = Coordinates.new(45.420906, -75.689374)
    assert_equal 45.42091, coordinates.lat
    assert_equal -75.68937, coordinates.lon
  end

  test "lat and lon are rounded to the specified precision" do
    coordinates = Coordinates.new(45.420906, -75.689374, precision: 2)
    assert_equal 45.42, coordinates.lat
    assert_equal -75.69, coordinates.lon
  end

  test "full_lat and full_lon are the original values" do
    coordinates = Coordinates.new(45.420906, -75.689374)
    assert_equal 45.420906, coordinates.full_lat
    assert_equal -75.689374, coordinates.full_lon
  end
end
