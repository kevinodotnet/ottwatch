require "test_helper"

class CampaignReturnsControllerTest < ActionDispatch::IntegrationTest
  setup do
    @election = FactoryBot.create(:election)
    @candidate = @election.candidates.last
  end

  test "return can be created for an existing candidate" do
    binding.pry
    
  end
end
