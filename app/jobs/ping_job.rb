class PingJob < ApplicationJob
  queue_as :default

  # https://edgeguides.rubyonrails.org/active_job_basics.html#enqueue-the-job
  # # Enqueue a job to be performed as soon as the queuing system is
  # # free.
  # GuestsCleanupJob.perform_later guest
  # Copy
  # # Enqueue a job to be performed tomorrow at noon.
  # GuestsCleanupJob.set(wait_until: Date.tomorrow.noon).perform_later(guest)
  # Copy
  # # Enqueue a job to be performed 1 week from now.
  # GuestsCleanupJob.set(wait: 1.week).perform_later(guest)
  # Copy
  # # `perform_now` and `perform_later` will call `perform` under the hood so
  # # you can pass as many arguments as defined in the latter.
  # GuestsCleanupJob.perform_later(guest1, guest2, filter: 'some_filter')

  def perform(*args)
    Rails.logger.info("#" * 50)
    Rails.logger.info("ping: #{job_id}")
    Rails.logger.info("#" * 50)
  end
end
