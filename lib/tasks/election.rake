namespace :election do
  desc "Generate fake v1 election data"
  task v1gen: :environment do
    FIRST_NAMES = %w(
      Sarah Judith Chantelle Chloe Dakota Kaylen Kalisha Tanisha
      Makeba Nikita Tanisha Kiyana
      Hakeem Izaak Jadyn Jamal Joseph Justus Kahlil Kelvin Khalan
      Kordell Lamonte Lemarcus Malachiah Marcel
    )
    LAST_NAMES = %w(
      Khan Ali Ahmed Abdullah Habib Hadi Mustafa Amin Isa Rafiq
      Wong Ho Chan Li Chen Wang Liu Zhang Lam Leung
    )
    STREET_TYPES = %w(
      Blvd
      Street
      Ave
      Avenue
      St
      Crescent
      Road
    )
    V1::Election.transaction do
      3.times do
        election = V1::Election.create!(
          date: Date.today - rand(10000).days,
          city: "Ottawa"
        )
        5.times do
          candidate = election.candidates.create!(
            ward: rand(24),
            first: FIRST_NAMES.sample,
            last: LAST_NAMES.sample
          )
          canret = candidate.returns.create!(
            done: true,
            supplemental: false,
          )
          100.times do
            canret.donations.create!(
              type: rand(2),
              name: "#{LAST_NAMES.sample}, #{FIRST_NAMES.sample}",
              address: "#{rand(1000)} #{(FIRST_NAMES + LAST_NAMES).sample} #{STREET_TYPES.sample}",
              city: "Ottawa",
              prov: "ON",
              postal: ["K",rand(10),('A'..'Z').to_a.sample," ",rand(10),('A'..'Z').to_a.sample,rand(10)].join,
              amount: rand(500)
            )
          end
        end
      end
    end
  end
end
