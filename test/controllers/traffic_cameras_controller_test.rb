require "test_helper"

class TrafficCamerasControllerTest < ActionDispatch::IntegrationTest
  setup do
    @camera = TrafficCamera.create!(
      reference_id: "47",
      name: "Test Camera",
      camera_number: "47",
      lat: 45.0,
      lon: -75.0
    )
    TrafficCamera.stubs(:find).with("47").returns(@camera)
  end

  test "capture action handles HTML requests without missing template error" do
    temp_file = Tempfile.new(['test_capture', '.jpg'])
    begin
      temp_file.write("fake jpg data")
      temp_file.close
      
      mock_capture = {
        camera: @camera,
        time_ms: 1753058414546,
        time: 1753058414,
        file: temp_file.path
      }
      
      @camera.stubs(:captures).returns([mock_capture])
      
      get "/traffic_cameras/47/capture?time_ms=1753058414546"
      
      assert_response :redirect
      assert_redirected_to traffic_camera_path(@camera)
    ensure
      temp_file.unlink if temp_file
    end
  end

  test "capture action handles HTML requests when no capture found - should redirect gracefully" do
    @camera.stubs(:captures).returns([])
    
    get "/traffic_cameras/47/capture?time_ms=1753058414546"
    
    assert_response :redirect
    assert_redirected_to traffic_camera_path(@camera)
  end

  test "capture action handles JPEG requests when no capture found - should redirect gracefully" do
    @camera.stubs(:captures).returns([])
    
    get "/traffic_cameras/47/capture?time_ms=1753058414546", headers: { "Accept" => "image/jpeg" }
    
    assert_response :redirect
    assert_redirected_to traffic_camera_path(@camera)
  end
end
