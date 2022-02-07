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
end