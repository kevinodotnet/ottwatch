# This file should contain all the record creation needed to seed the database with its default values.
# The data can then be loaded with the bin/rails db:seed command (or created alongside the database with db:setup).
#
# Examples:
#
#   movies = Movie.create([{ name: "Star Wars" }, { name: "Lord of the Rings" }])
#   Character.create(name: "Luke", movie: movies.first)

e = Election.create(date: Date.today)
3.times do
  e.candidates.create!(ward: 0, name: "Fake Name", email: "fake@example.com", website: "https://example.com", telephone: "613-7545-1576")
end