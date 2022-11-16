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
end