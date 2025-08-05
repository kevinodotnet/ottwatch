import { Controller } from "@hotwired/stimulus"
import maplibregl from 'maplibre-gl'

export default class extends Controller {
  static targets = ["mapContainer", "filters", "filterCheckbox", "popupTemplate"]
  static values = {
    initialLat: Number,
    initialLon: Number,
    dataUrl: String,
    filterGroups: Object,
    singlePoint: Boolean
  }

  connect() {
    this.initMap()
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
      zoom: this.singlePointValue ? 15 : 13
    });

    this.map.on('load', () => {
      if (this.singlePointValue) {
        this.addSingleMarker()
      } else {
        this.loadData()
      }
    });
  }

  addSingleMarker() {
    new maplibregl.Marker()
      .setLngLat([this.initialLonValue, this.initialLatValue])
      .addTo(this.map);
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

        // Apply filters after data is loaded
        if (!this.singlePointValue) {
          this.applyFilters()
        }
      });
  }

  handleMarkerClick(e) {
    const coordinates = e.features[0].geometry.coordinates.slice();
    const properties = e.features[0].properties;

    const popupContent = this.generatePopupContent(properties);

    new maplibregl.Popup({
      maxWidth: '300px',
      closeButton: true,
      closeOnClick: false
    })
      .setLngLat(coordinates)
      .setHTML(popupContent)
      .addTo(this.map);
  }

  generatePopupContent(properties) {
    const template = this.popupTemplateTarget.cloneNode(true);
    template.style.display = 'block';

    // Generic popup content population based on data attributes
    const popupTargets = template.querySelectorAll('[data-map-popup-target]');
    popupTargets.forEach(element => {
      const targetName = element.dataset.mapPopupTarget;
      
      if (targetName === 'viewDetails') {
        element.href = properties.url;
      } else if (targetName === 'currentImage') {
        // Handle image elements
        const propertyValue = properties.current_image_url || properties.currentImageUrl;
        if (propertyValue) {
          element.src = propertyValue;
        }
      } else {
        // Map common property names
        const propertyValue = properties[targetName] || 
                            properties[this.camelToSnake(targetName)] ||
                            properties[this.snakeToCamel(targetName)];
        
        if (propertyValue !== undefined) {
          element.textContent = propertyValue;
        }
      }
    });

    return template.innerHTML;
  }

  camelToSnake(str) {
    return str.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`);
  }

  snakeToCamel(str) {
    return str.replace(/_([a-z])/g, (match, letter) => letter.toUpperCase());
  }

  applyFilters() {
    if (!this.map.getLayer('data-layer')) {
      console.warn('Data layer not yet loaded. Skipping filter application.');
      return;
    }

    const filters = Object.entries(this.filterGroupsValue).map(([groupName, options]) => {
      const checkedOptions = this.filterCheckboxTargets
        .filter(checkbox => checkbox.dataset.mapFilterGroup === groupName && checkbox.checked)
        .map(checkbox => checkbox.dataset.mapFilterOption);
      return ['in', ['get', groupName], ['literal', checkedOptions]];
    });

    const combinedFilter = ['all', ...filters];
    this.map.setFilter('data-layer', combinedFilter);
  }

  resetFilters() {
    this.filterCheckboxTargets.forEach(checkbox => {
      checkbox.checked = true;
    });
    this.applyFilters();
  }
}
