namespace :election do
  desc "import campaign return pdf"
  task :import_campaign_return_pdf, [:candidate_id, :url] => [:environment] do |t, args|
    Rails.logger = Logger.new(STDOUT)
    c = Candidate.find(args[:candidate_id])
    pdf_data = Net::HTTP.get(URI(args[:url]))
    tmp_file = Tempfile.new('campaign_return_pdf')
    tmp_file.write(pdf_data.force_encoding("UTF-8"))
    tmp_file.close
    CampaignReturnScanner.scan(candidate: c, pdf_file: tmp_file)
  end

  desc "Scan for 2022 campaign returns"
  task returns_scan: :environment do
    result = CampaignReturnScanner.scan_for_returns
    result.compact.each do |v|
      puts "#" * 100
      puts [v[:candidate_name], v[:url]].join("\t")
      v[:candidate_name].split(" ").each do |p|
        Candidate.where('name like ?', "%#{p}%").each do |c|
          puts [v[:candidate_name], v[:url], c[:name], c[:id]].join("\t")
        end
      end
    end
  end

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
