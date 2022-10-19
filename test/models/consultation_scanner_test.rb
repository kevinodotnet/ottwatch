require "test_helper"

class ConsultationScannerTest < ActiveSupport::TestCase
  test "tbd empty poke test" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      ConsultationScanner.perform_now
    end
  end
end
