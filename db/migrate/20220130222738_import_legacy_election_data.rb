class ImportLegacyElectionData < ActiveRecord::Migration[7.0]
  def change
    puts <<~EOF





      mysql ottwatch_dev < db/ottwatch_v1_snapshot.sql
    
    
    
    
    
    
    
      EOF
    Election.transaction do
      rename_table :election, :v1_elections
      rename_table :candidate, :v1_candidates
      rename_table :candidate_return, :v1_candidate_returns
      rename_table :candidate_donation, :v1_candidate_donations
    end
  end
end
