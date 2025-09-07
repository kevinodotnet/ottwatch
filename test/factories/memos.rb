FactoryBot.define do
  factory :memo do
    title { "MyText" }
    department { "MyString" }
    issued_date { "2025-08-24" }
    content { "MyText" }
    url { "MyString" }
    reference_id { "MyString" }
  end
end
