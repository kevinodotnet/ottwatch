#!/usr/bin/env ruby

# gem install pdf-reader

require 'pdf-reader'
require 'pry'
require 'date'

def parse_bylaw_pdf(pdf_file)

  reader = PDF::Reader.new(pdf_file)

  bylaw = {}
  [reader.pages.first, reader.pages.last].each do |page|

    text = page.text.gsub(/\n/,' ').gsub(/ +/,' ')

    text.match(/BY-LAW NO. (?<year>\d+)[^\d]+(?<num>\d+) A by-law/) do |m|
      bylaw[:year] = m[:year]
      bylaw[:num] = m[:num]
      bylaw[:id] = "#{m[:year]}-#{m[:num]}"
    end

    text.match(/Enacted by City Council at its meeting o[fn] (?<date>[^ ]* \d+, \d+)/) do |m|
      bylaw[:enacted] = Date.parse(m[:date])
    end
    text.match(/ENACTED AND PASSED this (?<day>\d+) n*d*day of (?<month>[^,]*), (?<year>\d+)/i) do |m|
      bylaw[:enacted] = Date.parse("#{m[:month]} #{m[:day]}, #{m[:year]}")
    end

    [
      /(?<desc>A by-law of the City of Ottawa to confirm proceedings of the Council of the City of Ottawa at its meeting held on [^ ]* \d+, \d\d\d\d)/i,
      /o-o-* (?<desc>A by-law.*) -*o-o.* Enacted/i
    ].each do |regex|
      break unless bylaw[:desc].nil?
      text.match(regex) do |m|
        bylaw[:desc] = m[:desc]
      end
    end
  end

  binding.pry if bylaw[:desc].nil?

  bylaw
end

files = []
Dir.foreach('/Users/kevinodonnell/bylaw') { |f| files << f }
files.sort.each do |f|
  next unless f.match(/pdf/)
  filename = "/Users/kevinodonnell/bylaw/#{f}"
  meta = parse_bylaw_pdf(filename)
  # php bylaw.php injestBylaw ~/bylaws/$date/$num.pdf "$desc" $date
  puts [
    'php bylaw.php injestBylaw',
    f,
    "'#{meta[:desc].gsub(/'/,'"')}'",
    meta[:enacted]
  ].join(" ")
end

