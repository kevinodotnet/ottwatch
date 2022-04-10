require "test_helper"

class ParcelTest < ActiveSupport::TestCase
  focus
  test "#perform loads new entries starting with largest objectid" do
    assert_difference -> { Parcel.count }, 1000 do
      ParcelScanner.new.perform
    end
  end

  test "#objects_after returns objects after the given one" do
    features = ParcelScanner.new.objects_after(0)
    assert_equal 1000, features.count

    feature = features.first
    assert_equal 1, feature.dig("attributes", "OBJECTID")

    feature = features.last
    assert_equal 1000, feature.dig("attributes", "OBJECTID")

    # ... and the format is as expected
    expected = [
      "OBJECTID",
      "PIN",
      "EASTING",
      "NORTHING",
      "PUBLICLAND",
      "PARCELTYPE",
      "TEXTHEIGHT",
      "TEXTWIDTH",
      "TEXTROTATION",
      "PI_MUNICIPAL_ADDRESS_ID",
      "RECORD_OWNER_ID",
      "RT_ROAD_NAME_ID",
      "ADDRESS_NUMBER",
      "ROAD_NAME",
      "SUFFIX",
      "DIR",
      "MUNICIPALITY_NAME",
      "LEGAL_UNIT",
      "ADDRESS_QUALIFIER",
      "POSTAL_CODE",
      "ADDRESS_STATUS",
      "ADDRESS_TYPE_ID",
      "PIN_NUMBER",
      "FEAT_NUM",
      "PI_PARCEL_ID",
      "Shape_Length",
      "Shape_Area"
    ]
    assert_equal expected, feature.dig("attributes").keys

    # ... and we can save it as Parcel
    assert_difference -> { Parcel.count } do
      ParcelScanner.parcel_from_api(feature)
    end
    assert_no_difference -> { Parcel.count } do
      ParcelScanner.parcel_from_api(feature)
    end
  end
end
