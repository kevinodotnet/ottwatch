require "test_helper"

class ConsultationScannerTest < ActiveSupport::TestCase
  test "tbd empty poke test" do
    ConsultationScanner.perform_now
  end
end
