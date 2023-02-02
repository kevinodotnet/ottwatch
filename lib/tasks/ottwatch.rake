namespace :ottwatch do
  desc "Seed data"
  task seed: :environment do
    ParcelScanner.perform_now
  end

  desc "Generate fake election data"
  task fake_election_data: :environment do
    e = Election.create!(date: Date.today)
    5.times do
      e.candidates.create!(ward: 0, name: "Someone #{Random.rand(100...999)} Lastname")
    end
  end

  desc "Injest candidate return"
  task injest_candidate_return: :environment do
    file_name = 'candidate_return_example.pdf'
    pdf_data = if File.exists?(file_name)
      File.read(file_name)
    else
      url = "https://documents.ottawa.ca/sites/documents/files/Watson_Jim_Mayor.pdf"
      Net::HTTP.get(URI(url))
    end

    binding.pry


  end
end
