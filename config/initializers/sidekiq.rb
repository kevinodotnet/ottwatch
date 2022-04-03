Sidekiq.configure_server do |config| # Already had this line
   config.on(:startup) do
      schedule_file = Rails.root.join("config", "schedule.yml")
      if File.exists?(schedule_file)
         Sidekiq.schedule = YAML.load_file(schedule_file)
         SidekiqScheduler::Scheduler.instance.reload_schedule!
      end
   end 
end
