class ElectionsController < ApplicationController
	def donation
		@donation = CandidateDonation.find(params['id'].to_i)
		respond_to do |format|
			format.html {
				render
			}
			format.json {
				render(json: @donation)
			}
		end
	end
end
