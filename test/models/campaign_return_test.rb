require "test_helper"

class CampaignReturnTest < ActiveSupport::TestCase
  setup do
    @pdf_file_path = Rails.root.join("fixtures/four_pages.pdf")
    @election = Election.new(date: Time.now)
    @election.save!
  end

  test "injest from local PDF" do
    assert_difference -> { CampaignReturn.count } do
      CampaignReturn.injest(
        candidate: candidate, 
        pdf_file_path: @pdf_file_path, 
        return_href: "http://example.com/campaign_return.pdf"
      )
    end
    cr = CampaignReturn.last
    assert_equal open(@pdf_file_path).read, cr.pdf.download.force_encoding("UTF-8")
  end

  private

  def candidate
    @candidate ||= @election.candidates.new
    @candidate.save!
    @candidate
  end

  def election
    @election ||= Election.new(date: Time.now)
    @election.save!
    @election
  end
end
