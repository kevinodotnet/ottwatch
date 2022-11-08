class ServiceRequestScanJob < ApplicationJob
  def perform(date: nil)
    if date
      start_date = date.strftime("%Y-%m-%dT00:00:00Z")
      end_date = (date + 1.day).strftime("%Y-%m-%dT00:00:00Z")
      service_requests = Open311.service_requests("start_date" => start_date, "end_date" => end_date)
      service_requests.each do |sr|
        process_sr(sr)
      end
    else
      service_requests = Open311.service_requests
      service_requests.each do |sr|
        process_sr(sr)
      end
    end
  end

  def process_sr(sr)
    sr = from_open_data(sr)
    m = ServiceRequest.find_or_create_by(service_request_id: sr["service_request_id"])
    m.assign_attributes(sr)
    m.save!
    m
  end

  def from_open_data(sr)
    sr["lon"] = sr["long"]
    sr.delete("long")
    sr.to_h
  end
end
