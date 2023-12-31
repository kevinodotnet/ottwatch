namespace :ottwatch do
  desc "Seed data"
  task seed: :environment do
    ParcelScanner.perform_now
  end
end
