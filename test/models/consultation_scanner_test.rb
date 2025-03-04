require "test_helper"

class ConsultationScannerTest < ActiveSupport::TestCase
  test "big integration test dont judge me" do
    VCR.use_cassette("#{class_name}_#{method_name}") do
      assert_changes -> { Consultation.count } do
        ConsultationScanner.perform_now
      end

      # cherry pick attr tests
      c = Consultation.find_by_href("/beryl-gaffney-off-leash-dog-park")
      assert_equal "Beryl Gaffney Off-leash Dog Park", c.title
      assert_equal "archived", c.status
      assert_equal 1, c.announcements.count
      expected = "New Consultation: Beryl Gaffney Off-leash Dog Park"
      assert_equal expected, c.announcements.first.message

      # confirm only two known states
      assert_equal ["archived", "published"], Consultation.all.map{|c| c.status}.uniq.sort

      # confirm all hrefs are relative
      assert Consultation.all.pluck(:href).all?{|v| v.match(/^\//)}

      # re-scan does nothing
      assert_no_changes -> { Consultation.count } do
        ConsultationScanner.perform_now
      end
    end
  end
end
