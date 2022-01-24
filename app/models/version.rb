class Version
	def self.current
		if File.exists?(Rails.root.join("version.json"))
			return JSON.parse(File.read(Rails.root.join("version.json")))["object"]["sha"]
		end
		return Rails.env if Rails.env.development? || Rails.env.test?
		raise StandardError.new("No version provided in production environment")
	end
end
