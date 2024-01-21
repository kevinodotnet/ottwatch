require "test_helper"

class TranspoControllerTest < ActionDispatch::IntegrationTest
  test "4940" do
    TranspoController.expects(:get_next_trips_for_stop_all_routes).with("4940").returns(ROUTE_SUMMARY_4940)
    TranspoController.stop_trips("4940")
  end

  test "3011" do
    TranspoController.expects(:get_next_trips_for_stop_all_routes).with("3011").returns(ROUTE_SUMMARY_3011)
    TranspoController.stop_trips("3011")
  end

  ROUTE_SUMMARY_3011 = <<-JSON
  {
    "GetRouteSummaryForStopResult": {
      "StopNo": "3011",
      "Error": "",
      "StopDescription": "TUNNEY'S PASTURE",
      "Routes": {
        "Route": [
          {
            "RouteNo": "1",
            "RouteHeading": "Blair",
            "DirectionID": 0,
            "Direction": "",
            "Trips": [
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Blair",
                "TripStartTime": "",
                "AdjustedScheduleTime": "0",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Blair",
                "TripStartTime": "",
                "AdjustedScheduleTime": "9",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Blair",
                "TripStartTime": "",
                "AdjustedScheduleTime": "19",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              }
            ]
          },
          {
            "RouteNo": "11",
            "RouteHeading": "Parliament",
            "DirectionID": 0,
            "Direction": "",
            "Trips": [
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Laurier",
                "TripStartTime": "23:08",
                "AdjustedScheduleTime": "27",
                "AdjustmentAge": "0.50",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Laurier",
                "TripStartTime": "23:38",
                "AdjustedScheduleTime": "57",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Laurier",
                "TripStartTime": "00:08",
                "AdjustedScheduleTime": "87",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              }
            ]
          },
          {
            "RouteNo": "11",
            "RouteHeading": "Bayshore",
            "DirectionID": 1,
            "Direction": "",
            "Trips": [
              {
                "Longitude": "-75.6961489738302",
                "Latitude": "45.42257057352269",
                "GPSSpeed": "0",
                "TripDestination": "Lincoln Fields",
                "TripStartTime": "22:57",
                "AdjustedScheduleTime": "19",
                "AdjustmentAge": "0.50",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Lincoln Fields",
                "TripStartTime": "23:27",
                "AdjustedScheduleTime": "50",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Lincoln Fields",
                "TripStartTime": "23:57",
                "AdjustedScheduleTime": "80",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              }
            ]
          },
          {
            "RouteNo": "14",
            "RouteHeading": "St-Laurent",
            "DirectionID": 0,
            "Direction": "",
            "Trips": [
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "St-Laurent",
                "TripStartTime": "23:15",
                "AdjustedScheduleTime": "14",
                "AdjustmentAge": "0.58",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "St-Laurent",
                "TripStartTime": "23:45",
                "AdjustedScheduleTime": "44",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "St-Laurent",
                "TripStartTime": "00:15",
                "AdjustedScheduleTime": "74",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              }
            ]
          },
          {
            "RouteNo": "51",
            "RouteHeading": "Britannia",
            "DirectionID": 1,
            "Direction": "",
            "Trips": [
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Lincoln Fields",
                "TripStartTime": "23:17",
                "AdjustedScheduleTime": "16",
                "AdjustmentAge": "0.53",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Britannia",
                "TripStartTime": "23:45",
                "AdjustedScheduleTime": "44",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              }
            ]
          },
          {
            "RouteNo": "53",
            "RouteHeading": "Carlington",
            "DirectionID": 1,
            "Direction": "",
            "Trips": [
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Carlington",
                "TripStartTime": "23:27",
                "AdjustedScheduleTime": "26",
                "AdjustmentAge": "0.50",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Carlington",
                "TripStartTime": "23:57",
                "AdjustedScheduleTime": "56",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Carlington",
                "TripStartTime": "00:27",
                "AdjustedScheduleTime": "86",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              }
            ]
          },
          {
            "RouteNo": "56",
            "RouteHeading": "King Edward & Civic",
            "DirectionID": 0,
            "Direction": "",
            "Trips": [
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Civic",
                "TripStartTime": "23:30",
                "AdjustedScheduleTime": "29",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Civic",
                "TripStartTime": "00:10",
                "AdjustedScheduleTime": "69",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              }
            ]
          },
          {
            "RouteNo": "57",
            "RouteHeading": "Crystal Bay",
            "DirectionID": 1,
            "Direction": "",
            "Trips": [
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Bayshore",
                "TripStartTime": "23:30",
                "AdjustedScheduleTime": "29",
                "AdjustmentAge": "0.50",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Bayshore",
                "TripStartTime": "00:00",
                "AdjustedScheduleTime": "59",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Bayshore",
                "TripStartTime": "00:30",
                "AdjustedScheduleTime": "89",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              }
            ]
          },
          {
            "RouteNo": "61",
            "RouteHeading": "Stittsville",
            "DirectionID": 1,
            "Direction": "",
            "Trips": [
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Stittsville",
                "TripStartTime": "23:25",
                "AdjustedScheduleTime": "24",
                "AdjustmentAge": "0.58",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Terry Fox",
                "TripStartTime": "23:55",
                "AdjustedScheduleTime": "54",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Terry Fox",
                "TripStartTime": "00:24",
                "AdjustedScheduleTime": "83",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              }
            ]
          },
          {
            "RouteNo": "63",
            "RouteHeading": "Briarbrook via Innovation",
            "DirectionID": 0,
            "Direction": "",
            "Trips": [
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Innovation",
                "TripStartTime": "23:30",
                "AdjustedScheduleTime": "29",
                "AdjustmentAge": "0.50",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Innovation",
                "TripStartTime": "00:00",
                "AdjustedScheduleTime": "59",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              }
            ]
          },
          {
            "RouteNo": "75",
            "RouteHeading": "Barrhaven Centre",
            "DirectionID": 1,
            "Direction": "",
            "Trips": [
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Barrhaven Centre",
                "TripStartTime": "23:17",
                "AdjustedScheduleTime": "16",
                "AdjustmentAge": "0.58",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Barrhaven Centre",
                "TripStartTime": "23:32",
                "AdjustedScheduleTime": "31",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Barrhaven Centre",
                "TripStartTime": "23:55",
                "AdjustedScheduleTime": "54",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              }
            ]
          },
          {
            "RouteNo": "80",
            "RouteHeading": "Barrhaven Centre",
            "DirectionID": 0,
            "Direction": "",
            "Trips": [
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Barrhaven Centre",
                "TripStartTime": "23:30",
                "AdjustedScheduleTime": "29",
                "AdjustmentAge": "0.42",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Barrhaven Centre",
                "TripStartTime": "00:00",
                "AdjustedScheduleTime": "59",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              }
            ]
          },
          {
            "RouteNo": "86",
            "RouteHeading": "Baseline",
            "DirectionID": 1,
            "Direction": "",
            "Trips": {
              "Longitude": "",
              "Latitude": "",
              "GPSSpeed": "",
              "TripDestination": "Baseline",
              "TripStartTime": "23:48",
              "AdjustedScheduleTime": "47",
              "AdjustmentAge": "-1",
              "LastTripOfSchedule": false,
              "BusType": ""
            }
          },
          {
            "RouteNo": "89",
            "RouteHeading": "Colonnade",
            "DirectionID": 1,
            "Direction": "",
            "Trips": [
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Colonnade",
                "TripStartTime": "23:03",
                "AdjustedScheduleTime": "2",
                "AdjustmentAge": "0.47",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Colonnade",
                "TripStartTime": "23:33",
                "AdjustedScheduleTime": "32",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              },
              {
                "Longitude": "",
                "Latitude": "",
                "GPSSpeed": "",
                "TripDestination": "Colonnade",
                "TripStartTime": "00:03",
                "AdjustedScheduleTime": "62",
                "AdjustmentAge": "-1",
                "LastTripOfSchedule": false,
                "BusType": ""
              }
            ]
          }
        ]
      }
    }
  }
  JSON

  ROUTE_SUMMARY_4940 = <<-JSON
    {
      "GetRouteSummaryForStopResult": {
        "StopNo": "4940",
        "Error": "",
        "StopDescription": "RICHMOND / WESTMINSTER",
        "Routes": {
          "Route": {
            "RouteNo": "11",
            "RouteHeading": "Parliament",
            "DirectionID": 0,
            "Direction": "",
            "Trips": {
              "Trip": [
                {
                  "Longitude": "-75.7856342212574",
                  "Latitude": "45.36580668268977",
                  "GPSSpeed": "18",
                  "TripDestination": "Laurier",
                  "TripStartTime": "22:38",
                  "AdjustedScheduleTime": "5",
                  "AdjustmentAge": "0.62",
                  "LastTripOfSchedule": false,
                  "BusType": ""
                },
                {
                  "Longitude": "",
                  "Latitude": "",
                  "GPSSpeed": "",
                  "TripDestination": "Laurier",
                  "TripStartTime": "23:08",
                  "AdjustedScheduleTime": "34",
                  "AdjustmentAge": "-1",
                  "LastTripOfSchedule": false,
                  "BusType": ""
                },
                {
                  "Longitude": "",
                  "Latitude": "",
                  "GPSSpeed": "",
                  "TripDestination": "Laurier",
                  "TripStartTime": "23:38",
                  "AdjustedScheduleTime": "64",
                  "AdjustmentAge": "-1",
                  "LastTripOfSchedule": false,
                  "BusType": ""
                }
              ]
            }
          }
        }
      }
    }
  JSON
end
