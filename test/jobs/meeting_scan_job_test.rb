require "test_helper"

class MeetingScanJobTest < ActiveJob::TestCase

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
            MeetingScanJob.perform_now(attrs: m)
          end
        end

        meeting = Meeting.last
        refute meeting.reference_id
        assert meeting.reference_guid
        assert_equal m[:title], meeting.committee.name

        announcement = Announcement.last
        assert "New Meeting: #{m[:title]}", announcement.message
        assert announcement.reference_link
        assert announcement.reference_context

        # scanning again does not create new records, nor re-announce

        assert_no_difference -> { Announcement.count } do
          assert_no_difference -> { Meeting.count } do
            MeetingScanJob.perform_now(attrs: m)
          end
        end
      end
    end
  end

  test "meeting items and docs are parsed; saved; not duplicated" do
    m = {"title":"Planning Committee","reference_guid":"128fff38-faa9-4b07-a8cc-e13e88688f9d","meeting_time":"2022-09-08T09:30:00.000-05:00"}

    VCR.use_cassette("#{class_name}_#{method_name}") do
      assert_difference -> { MeetingItem.count }, 23 do
        assert_difference -> { MeetingItemDocument.count }, 34 do
          MeetingScanJob.perform_now(attrs: m)
        end
      end
      assert_no_difference -> { MeetingItem.count } do
        assert_no_difference -> { MeetingItemDocument.count } do
          MeetingScanJob.perform_now(attrs: m)
        end
      end
    end
  end

  test "no argument job inhales the meeting index and enqueues subsequent jobs" do
    MeetingScanJob.expects(:perform_later).at_least(2) # fails if there are fewer than 2 meetings with published HTML agendas at time of test
    # MeetingScanJob.expects(:scan_past_meetings).at_least(10).returns([]) # ensure fan-out code that checks past meetings for 10+ meeting types happens
    VCR.use_cassette("#{class_name}_#{method_name}") do
      MeetingScanJob.perform_now
    end
  end

  test "previous agenda formats are also scanned for items and docs" do
    attr = {
      title: "Whatever",
      reference_guid: "f743d690-36be-4e25-9bf6-dd8f944d1f2f", # city council, 27 Jun 2012
      meeting_time: Time.now
    }
    VCR.use_cassette("#{class_name}_#{method_name}") do
      MeetingScanJob.perform_now(attrs: attr)
      m = Meeting.last
      item = m.items.detect{|i| i.title.match(/Joint Ottawa-Gatineau Transit/)}
      assert_equal "05-12 - Joint Ottawa-Gatineau Transit Committee", item.title
      assert_equal "05-12 Joint Ottawa-Gatineau Transit Committee - CC 05-12 - Bloess - Response - Ottawa-Gatineau Transit Committee 2.doc.pdf", item.documents.first.title
    end
  end

  test "regression issue 93: title was too long for schema" do
    attr = {
      title: "City Council",
      reference_guid: "8bb58320-84bf-63d9-a43e-c955acdd42a9",
      meeting_time: "2019-10-23T14:00:00.000000000+00:00".to_time
    }
    VCR.use_cassette("#{class_name}_#{method_name}") do
      assert_nothing_raised do
        MeetingScanJob.perform_now(attrs: attr)
      end
    end
  end

  test "issue 96: regression" do
    attr = {
      title: "Committee of Adjustment - Panel 1",
      reference_guid: "9dc974f2-fb95-401e-bf2f-5fe225f5cdb5",
      meeting_time: "2016-02-03T18:00:00.000000000+00:00".to_time
    }
    VCR.use_cassette("#{class_name}_#{method_name}") do
      assert_nothing_raised do
        MeetingScanJob.perform_now(attrs: attr)
      end
    end
  end

  test "issue 112: item documents are 404ing after items are edited at source" do
    attr = {
      reference_guid: "a2265439-2abd-42a0-8ada-4010b9d4921c",
      title: "n/a",
      meeting_time: Time.now
    }
    VCR.use_cassette("#{class_name}_#{method_name}") do
      # setup the data
      MeetingScanJob.perform_now(attrs: attr)
      # munge the created item docs, replicating what it looks like when ottawa.ca is updated between scans
      MeetingItemDocument.last.update_attribute(:reference_id, 999_999_999)
      # re-scan; old docs are purged since they were deleted at-source
      assert_no_difference -> { MeetingItemDocument.count } do
        MeetingScanJob.perform_now(attrs: attr)
      end
    end
  end

  test "in-camera items do not have AgendaItemXXX class names as they are hidden" do
    attr = {
      title: "Information Technology Sub-Committee",
      reference_guid: "e8b142bc-0992-4fe7-a9de-6973e6c69c4b",
      meeting_time: "2021-11-29T14:30:00.000000000+00:00".to_time
    }
    item_title = "2022 Draft Operating and Capital Budgets - Information Technology Sub-Committee"
    in_camera_item_title = "Verbal Update on Cyber Security and the External Threat Landscape - In Camera – Reporting Out Date: Not To Be Reported Out"
    VCR.use_cassette("#{class_name}_#{method_name}") do
      assert_difference -> { MeetingItem.count }, 15 do
        MeetingScanJob.perform_now(attrs: attr)
        m = Meeting.find_by_reference_guid(attr[:reference_guid])
        assert m.items.where(title: item_title).first
        assert m.items.where(title: in_camera_item_title).first
      end
    end
  end
end
