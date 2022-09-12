class HomeController < ApplicationController
  before_action :authenticate_user!
  def index
    @announcements = Announcement.all.includes(reference: :addresses).order(id: :desc).limit(50)
    @meetings = Meeting.includes(:committee).where('start_time > ?', Time.now).order(:start_time)
    respond_to do |format|
      format.html
      format.rss { render :layout => false }
    end
  end
end
