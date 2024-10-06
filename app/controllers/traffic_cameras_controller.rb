class TrafficCamerasController < ApplicationController
  def index
    @traffic_cameras = TrafficCamera.all.order(:name)
  end

  def show
    @traffic_camera = TrafficCamera.find(params[:id])
  end

  def capture
    # params: { id: '1010' }
    # params: { time_ms: '1717234234' }
    @traffic_camera = TrafficCamera.find(params[:id])
    capture = @traffic_camera.captures.detect{|c| c[:time_ms] == params[:time_ms].to_i}
    return unless capture
    send_data(File.read(capture[:file]), type: 'image/jpeg', filename: "cam_#{params[:id]}_#{params[:time_ms]}.jpg")
  end
end
