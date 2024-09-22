import { Controller } from "@hotwired/stimulus"
import maplibregl from 'maplibre-gl'

export default class extends Controller {
  static targets = ["map"]

  connect() {
    if (this.hasMapTarget) {
      this.initMap()
    }
  }

  initMap() {
    const lat = this.mapTarget.dataset.lat
    const lon = this.mapTarget.dataset.lon

    if (!lat || !lon) {
      console.error("Latitude or longitude not provided")
      return
    }

    this.map = new maplibregl.Map({
      container: this.mapTarget,
      style: {
        version: 8,
        sources: {
          'osm': {
            type: 'raster',
            tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
            tileSize: 256,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
          }
        },
        layers: [{
          id: 'osm-tiles',
          type: 'raster',
          source: 'osm',
          minzoom: 0,
          maxzoom: 19
        }]
      },
      center: [lon, lat],
      zoom: 15
    });

    new maplibregl.Marker()
      .setLngLat([lon, lat])
      .addTo(this.map);
  }
}
