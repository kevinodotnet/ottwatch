class SyndicationJob < ApplicationJob
  queue_as :default

  GLOBAL_CONFIG_KEY = "syndication_job_last_id"

  def syndicate(a)
    msg = a.message
    msg << " #{a.reference_context}" if a.reference_context
    msg << " #{a.reference_link}"
    BlueSky.new.skeet(msg)
  end

  def perform
    announcements.each do |a|
      GlobalControl.set(GLOBAL_CONFIG_KEY, a.id)
      syndicate(a)
    end
  end

  private

  def announcements
    last_id = GlobalControl.get(GLOBAL_CONFIG_KEY) || Announcement.last.id
    Announcement.where('id > ?', last_id).order(:id).limit(5)
  end
end
