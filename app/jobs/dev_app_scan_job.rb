class DevAppScanJob < ApplicationJob
  queue_as :default

  def perform(app_number: nil)
    if app_number
      DevApp::Scanner.scan_application(app_number)
    else
      DevApp::Scanner.latest.each do |d|
        DevAppScanJob.perform_later(app_number: d[:app_number])
      end
    end
  end
end
