require "test_helper"

class CampaignReturnScannerTest < ActiveSupport::TestCase
  setup do
    @election = Election.find_or_create_by(date: '2001-01-01')
    @candidate = @election.candidates.find_or_create_by(ward: 0)
    @cached_return_pdf_file = Rails.root.join("fixtures", "four_pages.pdf")
  end

  test "scanner creates a return, attaches pdf, cuts page level images" do
    assert_difference -> { CampaignReturn.count } do
      assert_difference -> { CampaignReturnPage.count }, 4 do
        CampaignReturnScanner.scan(candidate: @candidate, pdf_file: @cached_return_pdf_file)
      end
    end

    cr = @candidate.campaign_returns.last
    assert cr.pdf.attached?

    cr.campaign_return_pages.each do |p|
      assert p.img.attached?
      assert_equal "image/png", p.img.blob.content_type
    end
  end
end