FactoryBot.define do
  factory :campaign_donation do
    campaign_return_page { nil }
    name { "MyString" }
    address { "MyString" }
    city { "MyString" }
    prov { "MyString" }
    postal { "MyString" }
    amount { "9.99" }
    x { "9.99" }
    y { "9.99" }
    donated_on { "2023-02-27" }
    redacted { false }
  end
end
