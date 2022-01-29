class CandidateReturn < ApplicationRecord
	belongs_to :candidate, foreign_key: 'candidateid'
	has_one_attached :pdf
	has_many :candidate_donations, foreign_key: 'returnid'

	LEGAGY_STORAGE = Rails.root.join("storage/legacy")

	def legacy_pdf_filename
		return nil if filename.nil?
		full_fn = nil
		if filename.match(/^\/mnt/)
			# /mnt/shared/ottwatch/var/election/2018/financial_returns/pdf/Weber_Peter_Anthony_Ward9.pdf
			fn = filename.gsub(/.*election/,'election').gsub(/\/pdf\//,'/')
			full_fn = File.join(LEGAGY_STORAGE, fn)
		else
			# find based on year
			fn = "election/#{candidate.election.date.year}/financial_returns/#{filename}"
			full_fn = File.join(LEGAGY_STORAGE, fn)
		end
		if File.exist?(full_fn)
			full_fn
		else
			raise StandardError.new("(id: #{id} bad filename: #{filename}")
		end
	end
end
