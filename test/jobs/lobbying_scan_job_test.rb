require "test_helper"

class LobbyingScanJobTest < ActiveJob::TestCase
  test "specific dates can be scraped" do
    VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
      LobbyingScanJob.perform_now(date: "2022-04-12")
    end
    assert_equal 2, LobbyingUndertaking.count
    assert u = LobbyingUndertaking.find_by(subject: "Budget")
    assert_equal "Submission of OSLA's response to the federal budget 2022 to city council.", u.issue

    assert u = LobbyingUndertaking.find_by(subject: "By-law/Regulation")
    assert_equal "Dog bite prevention program for children", u.issue
    assert_records
  end

  test "lobbying records are scraped over many days" do
    LobbyingScanJob.expects(:perform_later).times(31)
    VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
      LobbyingScanJob.perform_now
    end
  end

  test "lobbying activity dates are parsed and saved correctly" do
    VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
      LobbyingScanJob.perform_now(date: "2022-03-23")
    end
    u = LobbyingUndertaking.last
    binding.pry
    assert_equal 11, u.activities.count
  end

  private

  def assert_records
    [LobbyingUndertaking, LobbyingActivity].each do |klass|
      assert klass.count > 0
      klass.new.attributes.keys.each do |k|
        assert klass.where(k => nil).none?
      end
    end
  end
end