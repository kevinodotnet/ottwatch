require "test_helper"

class Meeting2ScanJobTest < ActiveJob::TestCase

  MEETINGS = [
    {"title":"Police Services Board Human Resources Committee","reference_guid":"ce1a3efd-4f33-4838-8aae-76f7123aed8c","meeting_time":"2022-08-30T13:00:00.000-05:00"},
    {"title":"City Council","reference_guid":"59a74d3a-4563-4269-9196-ab3bea684571","meeting_time":"2022-08-31T10:00:00.000-05:00"},
    {"title":"Agriculture and Rural Affairs Committee","reference_guid":"4f806962-c059-4605-b48c-751daee8bd85","meeting_time":"2022-09-01T10:00:00.000-05:00"},
    {"title":"Committee of Adjustment - Panel 3","reference_guid":"2dd97c8d-fdc0-4ecb-833e-6d5c8489d552","meeting_time":"2022-09-07T09:00:00.000-05:00"},
    {"title":"Committee of Adjustment - Panel 1","reference_guid":"d0f46ee8-dbd2-4f80-99aa-e8d7fa5a0742","meeting_time":"2022-09-07T13:00:00.000-05:00"},
    {"title":"Committee of Adjustment - Panel 2","reference_guid":"e5affc34-2148-4958-a978-99647b66492d","meeting_time":"2022-09-07T18:30:00.000-05:00"},
    {"title":"Planning Committee","reference_guid":"128fff38-faa9-4b07-a8cc-e13e88688f9d","meeting_time":"2022-09-08T09:30:00.000-05:00"}
  ]

  MEETINGS.each do |m|
    test "#{m[:title]} #{m[:reference_guid]} can be scanned" do
      VCR.use_cassette("#{class_name}_#{method_name}") do
        assert_difference -> { Announcement.count } do
          assert_difference -> { Meeting.count } do
            Meeting2ScanJob.perform_now(attrs: m)
          end
        end

        meeting = Meeting.last
        refute meeting.reference_id
        assert meeting.reference_guid
        assert_equal m[:title], meeting.committee.name

        announcement = Announcement.last
        assert "New Meeting: #{m[:title]}", announcement.message

        # scanning again does not create new records, nor re-announce

        assert_no_difference -> { Announcement.count } do
          assert_no_difference -> { Meeting.count } do
            Meeting2ScanJob.perform_now(attrs: m)
          end
        end
      end
    end
  end

  test "no argument job inhales the meeting index and enqueues subsequent jobs" do
    Meeting2ScanJob.expects(:perform_later).at_least(2) # fails if there are fewer than 2 meetings with published HTML agendas at time of test
    VCR.use_cassette("#{class_name}_#{method_name}") do
      Meeting2ScanJob.perform_now
    end
  end
end