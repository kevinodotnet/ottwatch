require "test_helper"

class ParcelTest < ActiveSupport::TestCase
  test "#perform loads new entries starting with largest objectid" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      assert_difference -> { Parcel.count }, 1000 do
        ParcelScanner.new.perform
      end
      assert_difference -> { Parcel.count }, 1000 do
        ParcelScanner.new.perform
      end
    end
  end

  test "#perform uses first day of month as snapshot_date and pulls a full clone each month" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      travel_to(Time.zone.local(2023, 12, 15, 01, 02, 03)) do
        ParcelScanner.new.perform
      end
      travel_to(Time.zone.local(2024, 01, 15, 01, 02, 03)) do
        ParcelScanner.new.perform
      end
      assert_equal 2, Parcel.where(objectid: 1).count
      assert_equal ["2023-12-01".to_date, "2024-01-01".to_date], Parcel.where(objectid: 1).map(&:snapshot_date).sort
    end
  end

  test "#objects_after returns objects after the given one" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
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
        ParcelScanner.new.send(:parcel_from_api, feature)
      end
      assert_no_difference -> { Parcel.count } do
        ParcelScanner.new.send(:parcel_from_api, feature)
      end
    end
  end
end
