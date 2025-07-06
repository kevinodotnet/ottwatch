class DevAppScanJob < ApplicationJob
  queue_as :default

  def perform(app_number: nil)
    if app_number
      DevApp::Scanner.scan_application(app_number)
    else
      enqueued = Set.new
      DevApp::Scanner.latest.each do |d|
        next if enqueued.include?(d[:app_number])
        DevAppScanJob.set(wait: rand(0..7200).seconds).perform_later(app_number: d[:app_number])
        enqueued << d[:app_number]
      end
    end
  end
end
