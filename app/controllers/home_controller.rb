class HomeController < ApplicationController
  def index
    # @announcements = Announcement.all.includes(reference: :addresses).order(id: :desc).limit(50)
    @announcements = Announcement.all.includes([:reference]).order(id: :desc).limit(10)

    @meetings = Meeting.includes(:committee)
      .where('start_time > ?', Time.now.beginning_of_day)
      .order(:start_time)

    respond_to do |format|
      format.html
      format.rss { render :layout => false }
    end
  end
end
