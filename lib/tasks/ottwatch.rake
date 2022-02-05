namespace :ottwatch do
  desc "Iterate over all candidate returns and attach the legacy PDFs to the active storage resource"
  task attach_legacy_candidate_return_pdfs: :environment do
    CandidateReturn.all.each do |cr|
      legacy_fn = cr.legacy_pdf_filename
      puts "cr:#{cr.id} legacy_path: #{legacy_fn}"
      next if legacy_fn.nil?
      if cr.pdf.attached?
        puts "  already attached"
      else
        new_fn = legacy_fn.split("/").last
        cr.pdf.attach(io: File.open(legacy_fn), filename: new_fn)
      end
    end
  end

  desc "Iterate over all candidate returns and attach the per-page images from legacy"
  task attach_legacy_candidate_return_pages: :environment do
    CandidateReturn.joins(candidate: :election).where(elections: { date: "2014-01-01".to_date..}).each do |cr|
      directory = if cr.candidate.election.date.year < 2018
        fn = [CandidateReturn::LEGACY_STORAGE,"election",cr.candidate.election.date.year,"financial_returns",cr.filename].join("/")
        fn.gsub(/\.pdf$/, "")
      else
        fn = cr.filename.gsub(/.pdf$/, "").gsub(/.*election/,"election").gsub(/\/pdf/, "")
        [CandidateReturn::LEGACY_STORAGE,fn].join("/")
      end

      files = Dir.entries(directory).select{|f| f.match(/page/)}.sort
      files.map{|f| f.gsub(/^page-/,"").gsub(/\..*/,"")}.uniq.sort.each do |page|
        orig = "page-#{page}.png"
        rotated = "page-#{page}.png.rotated"
        source = files.include?(rotated) ? rotated : orig
        binding.pry
      end
    end
  end
end



 