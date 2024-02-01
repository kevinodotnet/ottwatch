require "test_helper"

class ZoningScannerTest < ActiveSupport::TestCase
  test "#perform starts at 0 and moves forward in steps of 1000" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      assert_difference -> { Zoning.count }, 1000 do
        ZoningScanner.new.perform
      end
      assert_difference -> { Zoning.count }, 1000 do
        ZoningScanner.new.perform
      end
    end
  end

  test "#perform uses first day of month as snapshot_date and pulls a full clone each month" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      travel_to(Time.zone.local(2023, 12, 15, 01, 02, 03)) do
        ZoningScanner.new.perform
      end
      assert_equal "2023-12-01".to_date, Zoning.first.snapshot_date
    end
  end
end