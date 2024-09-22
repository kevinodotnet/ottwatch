import { Controller } from "@hotwired/stimulus"
import maplibregl from 'maplibre-gl'

export default class extends Controller {
  static targets = ["mapContainer", "filters", "filterCheckbox", "popupTemplate"]
  static values = {
    initialLat: Number,
    initialLon: Number,
    dataUrl: String,
    filterGroups: Object
  }

  connect() {
    this.initMap()
    this.applyFilters()
  }

  initMap() {
    this.map = new maplibregl.Map({
      container: this.mapContainerTarget,
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
      center: [this.initialLonValue, this.initialLatValue],
      zoom: 13
    });

    this.map.on('load', () => this.loadData());
  }

  loadData() {
    fetch(this.dataUrlValue)
      .then(response => response.json())
      .then(data => {
        this.map.addSource('data-source', {
          type: 'geojson',
          data: data
        });

        this.map.addLayer({
          'id': 'data-layer',
          'type': 'circle',
          'source': 'data-source',
          'paint': {
            'circle-radius': 6,
            'circle-color': '#FFA500',
            'circle-stroke-width': 2,
            'circle-stroke-color': '#ffffff'
          }
        });

        this.map.on('click', 'data-layer', this.handleMarkerClick.bind(this));
        this.map.on('mouseenter', 'data-layer', () => this.map.getCanvas().style.cursor = 'pointer');
        this.map.on('mouseleave', 'data-layer', () => this.map.getCanvas().style.cursor = '');
      });
  }

  handleMarkerClick(e) {
    const coordinates = e.features[0].geometry.coordinates.slice();
    const properties = e.features[0].properties;

    const popupContent = this.generatePopupContent(properties);

    new maplibregl.Popup()
      .setLngLat(coordinates)
      .setHTML(popupContent)
      .addTo(this.map);
  }

  generatePopupContent(properties) {
    const template = this.popupTemplateTarget.cloneNode(true);
    template.style.display = 'block';

    const appNumberEl = template.querySelector('[data-map-popup-target="appNumber"]');
    const appTypeEl = template.querySelector('[data-map-popup-target="appType"]');
    const statusEl = template.querySelector('[data-map-popup-target="status"]');
    const descriptionEl = template.querySelector('[data-map-popup-target="description"]');
    const viewDetailsEl = template.querySelector('[data-map-popup-target="viewDetails"]');

    appNumberEl.textContent = properties.app_number;
    appTypeEl.textContent = properties.app_type;
    statusEl.textContent = properties.status;
    descriptionEl.textContent = properties.description;
    viewDetailsEl.href = properties.url;

    return template.innerHTML;
  }

  applyFilters() {
    const filters = Object.entries(this.filterGroupsValue).map(([groupName, options]) => {
      const checkedOptions = this.filterCheckboxTargets
        .filter(checkbox => checkbox.dataset.mapFilterGroup === groupName && checkbox.checked)
        .map(checkbox => checkbox.dataset.mapFilterOption);
      return ['in', ['get', groupName], ['literal', checkedOptions]];
    });

    const combinedFilter = ['all', ...filters];
    if (this.map) {
      this.map.setFilter('data-layer', combinedFilter);
    }
  }

  resetFilters() {
    this.filterCheckboxTargets.forEach(checkbox => {
      checkbox.checked = true;
    });
    this.applyFilters();
  }
}
