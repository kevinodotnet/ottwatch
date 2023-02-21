FactoryBot.define do
  factory :election do
    date { Date.today - 1.year }
    after :create do |e|
      3.times do
        c = e.candidates.create!(
          ward: 0, 
          name: "Fake Candidate", 
          nomination_date: e.date - 1.day,
          telephone: "613-745-1576",
          email: "fake@example.com",
          website: "https://example.com"
        )
      end
    end
  end
end