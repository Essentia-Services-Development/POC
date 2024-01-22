import $ from 'jquery';
import { location } from 'peepsodata';

const API_KEY = location.api_key;

let instance = null;

export default class GoogleMaps {
	constructor() {
		if ( ! instance ) {
			instance = this;
		}

		return instance;
	}

	/**
	 * Search for locations based on a keyword.
	 * @param {string} keyword
	 * @return {Promise}
	 */
	search( keyword ) {
		return new Promise(( resolve, reject ) => {
			this.autocompleteService().then(( service ) => {
				service.getPlacePredictions({ input: keyword }, ( results, status ) => {
					if ( status === 'OK' ) {
						console.log( results );
						resolve( results.map( this.mapData ) );
					} else {
						reject( status );
					}
				});
			});
		});
	}

	/**
	 * Get geocode of an address.
	 * @param {string} address
	 * @return {Promise}
	 */
	geocode( address ) {
		return new Promise(( resolve, reject ) => {
			this.loadAPI().then(() => {
				let geocoder = new google.maps.Geocoder;

				geocoder.geocode({ address }, ( results, status ) => {
					if ( status === 'OK' ) {
						resolve( this.mapData( results[0] ) );
					} else {
						reject( status );
					}
				});
			});
		});
	}

	/**
	 * Map place data.
	 * @param {Object} data
	 * @return {Object}
	 */
	mapData( data ) {
		let { place_id: id, geometry } = data,
			address = data.formatted_address || data.description,
			[ name, description = '' ] = address.split( /,\s(.+)?/ ),
			location, viewport;

		if ( geometry ) {
			location = geometry.location.toJSON();
			viewport = geometry.viewport.toJSON();
		}

		return { id, name, description, location, viewport };
	}

	/**
	 * Render map into an element.
	 * @param {HTMLElement} elem
	 * @param {Object} data
	 */
	render( elem, data ) {
		let $elem = $( elem ).show(),
			map = $elem.data( 'psMap' ),
			marker = $elem.data( 'psMapMarker' ),
			{ name, location, viewport } = data;

		if ( ! map ) {
			$elem.data( 'psMap', map = new google.maps.Map( elem, {
				center: location,
				disableDefaultUI: true,
				draggable: false,
				scrollwheel: false,
				zoom: 15
			}) );
		}

		if ( ! marker ) {
			$elem.data( 'psMapMarker', marker = new google.maps.Marker({
				map: map,
				position: location,
				title: name
			}) );
		}

		map.setCenter( location );
		map.fitBounds( viewport );
		marker.setPosition( location );
	}

	/**
	 * Load Google Maps API.
	 * @return {Promise}
	 */
	loadAPI() {
		return new Promise(( resolve, reject ) => {
			let script, callback;

			if ( this.loaded ) {
				resolve();
				return;
			}

			this.queue = this.queue || [];
			this.queue.push({ resolve, reject });

			if ( this.loading ) {
				return;
			}

			this.loading = true;

			callback = _.uniqueId( 'psCallback' );

			script = document.createElement( 'script' );
			script.type = 'text/javascript';
			script.src = 'https://maps.googleapis.com/maps/api/js?libraries=places' +
				( API_KEY ? ( '&key=' + API_KEY ) : '' ) + '&callback=' + callback;

			window[ callback ] = () => {
				this.loaded = true;
				this.loading = false;
				while ( this.queue.length ) {
					( this.queue.shift() ).resolve();
				}
				delete window[ callback ];
			};

			document.body.appendChild( script );
		});
	}

	/**
	 * Get autocomplete service from Places library.
	 * @return {Promise}
	 */
	autocompleteService() {
		return new Promise(( resolve, reject ) => {
			this.loadAPI().then(() => {
				let cache = '_cacheAutocompleteService';

				this[ cache ] = this[ cache ] || new google.maps.places.AutocompleteService();
				resolve( this[ cache ] );
			});
		});
	}

	/**
	 * Get places service from Places library.
	 * @return {Promise}
	 */
	placesService() {
		return new Promise(( resolve, reject ) => {
			this.loadAPI().then(() => {
				let cache = '_cachePlacesService',
					div = document.createElement( 'div' );

				document.body.appendChild( div );
				this[ cache ] = this[ cache ] || new google.maps.places.PlacesService( div );
				resolve( this[ cache ] );
			});
		});
	}

	/**
	 * Get place detail of a Place.
	 * @param {string} id
	 * @return {Promise}
	 */
	placeDetail( id ) {
		return new Promise(( resolve, reject ) => {
			this.placesService().then(( service ) => {
				let cache = '_cachePlaceDetail';

				this[ cache ] = this[ cache ] || {};

				if ( this[ cache ][ id ] ) {
					resolve( this[ cache ][ id ] );
				} else {
					service.getDetails({ placeId: id }, ( place, status ) => {
						if ( status === 'OK' ) {
							place = this.mapData( place );
							this[ cache ][ id ] = place;
							resolve( place );
						} else {
							reject( status );
						}
					});
				}
			});
		});
	}

};
