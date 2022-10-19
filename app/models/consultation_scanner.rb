class ConsultationScanner < ApplicationJob
  queue_as :default

  def perform
    data = Net::HTTP.get(URI("https://engage.ottawa.ca/projects"))
    doc = Nokogiri::HTML(data)

    # Parse for...
    # <div class="col-xs-12 col-sm-6 col-lg-3 project-tile" data-name="quinn farm park" data-state="published">
    # <div class="project-tile__card">
    # <span class="project-tile__state project-tile__state--published">Published</span>
    # <a class="project-tile__link local" href="/quinn-farm-park"><div class="project-tile__image-wrapper">
    # <img class="project-tile__image" alt="" src="https://ehq-production-canada.imgix.net/a11af34e1a8f5bc1ff3843609d5fa286999fe1cd/original/1666015895/e825dbb27f33fba6deed84870d9a2ccb_Parks_department_logo.jpg?auto=compress%2Cformat&amp;w=1080">
    # </div>
    # <div class="project-tile__meta">
    # <span class="project-tile__meta__name">Quinn Farm Park</span>
    # </div>
    # </a></div>
    # </div>
    #
    # And MAP to:
    # {:title=>"Meadowbrook Park (Rideau) ", :state=>"published", :href=>"/meadowbrook-park-rideau"},

    tiles = doc.xpath("//div").map do |d|
      next unless d.attributes["class"]&.value
      next unless d.attributes["class"]&.value.split(" ").include?("project-tile")
      tile = Nokogiri::HTML(d.to_s)
      state = d.attributes["data-state"].value
      href = tile.xpath('//a[@class="project-tile__link"]').first.attributes["href"].value
      title = tile.xpath('//div[@class="project-tile__meta"]/span[@class="project-tile__meta__name"]').first.children.first.to_s
      {
        title: title,
        state: state,
        href: href
      }
    end.compact
  end
end