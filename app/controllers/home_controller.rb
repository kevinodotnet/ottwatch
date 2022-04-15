class HomeController < ApplicationController
  def index
    @announcements = Announcement.all.includes(reference: :addresses).order(id: :desc).limit(50)
  end
end
