require "test_helper"

class ElectionTest < ActiveSupport::TestCase
  test "ward names work" do
    assert_equal "Orléans East-Cumberland", Election.ward_name(1)
    assert_equal "Barrhaven East", Election.ward_name(24)
    binding.pry
  end
  
  test "factory bot works" do
    e = FactoryBot.create(:election)
    
  end
end
