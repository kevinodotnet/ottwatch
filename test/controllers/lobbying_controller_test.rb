require "test_helper"

class LobbyingControllerTest < ActionDispatch::IntegrationTest
  setup do
    10.times do
      attr = {
        subject: "Don't allow driveways through Byron Linear Park, like EVER. Never, ever. Nope. #{rand(1000)}",
        issue: "the issue",
        lobbyist_name: "Lobbying McLobbyist",
        lobbyist_position: "CEO",
        lobbyist_reg_type: "ACTIVE",
      }
      undertaking = LobbyingUndertaking.create!(**attr)
      (rand(10)+5).times do
        attr = {
          activity_date: rand(300).days.ago,
          activity_type: "email",
          lobbied_name: "City McPerson",
          lobbied_title: "Staffer",
        }
        undertaking.activities.create!(attr)
      end
    end
  end

  test "#show for non-existant devapp fails cleanly" do
    get "/lobbying/index"
  end
end
