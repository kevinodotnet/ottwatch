class GlobalControl < ApplicationRecord
  def self.get(name)
    find_by(name: name)&.value
  end

  def self.set(name, value)
    gc = find_by(name: name) || GlobalControl.new(name: name)
    gc.value = value
    gc.save!
  end
end
