class HomeController < ApplicationController
  def index
    @announcements = Announcement.all.order(id: :desc).limit(50)
  end
end
