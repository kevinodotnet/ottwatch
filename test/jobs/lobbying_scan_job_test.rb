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
    assert u.activities.count > 1
  end

  test "new lobbying activities are announced" do
    VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
      LobbyingScanJob.perform_now(date: "2022-03-23") # "new lobbying file"
      u = LobbyingUndertaking.where("issue like ?", "%Kaaj%").first
      u.activities.where('activity_date > ?', '2022-04-10').delete_all
      u.reload
      assert_equal 1, u.announcements.count
      count_1 = u.activities.count
      assert count_1 > 1
      LobbyingScanJob.perform_now(date: "2022-03-23") # "new activity on file"
      assert_equal 2, u.announcements.count
      count_2 = u.activities.count
      assert count_2 > count_1

      a = u.announcements.first
      assert_equal "New Lobbying undertaking", a.message
      assert_equal a.reference_context, "Reza Lotfalian (CTO): Kaaj Energy Inc. is in the planning stage to propose ..."
      assert_equal a.reference_link, "http://localhost:33000/lobbying/#{u.id}"

      a = u.announcements.last
      assert_equal "Additional Lobbying activity", a.message
      assert_equal a.reference_context, "Reza Lotfalian (CTO): Kaaj Energy Inc. is in the planning stage to propose ..."
      assert_equal a.reference_link, "http://localhost:33000/lobbying/#{u.id}"
    end
  end

  test "existing lobbying w/o an announcement dont get announced on re-scan" do
    VCR.use_cassette("#{class_name}_#{method_name}", :match_requests_on => [:body]) do
      LobbyingScanJob.perform_now(date: "2022-03-23")
      Announcement.delete_all
      assert_no_changes -> { Announcement.count } do
        LobbyingScanJob.perform_now(date: "2022-03-23")
      end
    end
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