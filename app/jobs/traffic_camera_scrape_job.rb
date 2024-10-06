class TrafficCameraScrapeJob < ApplicationJob
  queue_as :default

  def perform
    TrafficCamera.scrape_all
    TrafficCamera.all.each do |camera|
      camera.capture_image
    end
  end
end

