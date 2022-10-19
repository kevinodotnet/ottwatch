class ConsultationScanner < ApplicationJob
  queue_as :default

  def perform
    puts "Hi, I don't really do anything"
  end
end