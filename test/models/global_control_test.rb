require "test_helper"

class GlobalControlTest < ActiveSupport::TestCase
  test "can set and get" do
    name = "the_key"
    value = "the_value"
    assert_difference -> { GlobalControl.count } do
      assert_nil GlobalControl.get(name)
      GlobalControl.set(name, value)
      assert_equal value, GlobalControl.get(name)
      GlobalControl.set(name, "val2")
      assert_equal "val2", GlobalControl.get(name)
    end
  end
end
