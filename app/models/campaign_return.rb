class CampaignReturn < ApplicationRecord
  belongs_to :candidate
  has_one_attached :pdf
  has_many :campaign_return_pages

  def self.injest(pdf_file_path:, return_href:, candidate:)
    cr = CampaignReturn.new(candidate: candidate, return_href: return_href)
    cr.save!

    url = URI.parse(return_href)
    filename = File.basename(url.path)

    cr.pdf.attach(io: open(pdf_file_path), filename: filename, content_type: "application/pdf")

    i = 0
    CombinePDF.load(pdf_file_path).pages.each do |page|
      i += 1
      pdf_page = CombinePDF.new
      pdf_page << page
      pdf_page.save("page_#{i}.pdf")

      cr_page = CampaignReturnPage.new(page: i)
      # cr_page.image.attache(io: open("page_#{i}.pdf"))
      cr_page.image.attach(io: open("page_#{i}.pdf"), filename: "the_test_#{i}.pdf", content_type: "application/pdf")
      cr.campaign_return_pages << cr_page
    end

    cr
  end
end
