import $ from 'jquery';
import AccessibleListbox from './accessible-listbox';
import GoogleMaps from './google-maps';

export default class LocationSelector extends AccessibleListbox {
	constructor( elem ) {
		super( elem );
		this.$selector = $( elem );
		this.data = [
			{ id: 1, name: 'Surabaya', description: 'Surabaya desc', lat: 1, lng: 1, zoom: 1 },
			{ id: 2, name: 'Porong, Sidoarjo', description: 'Porong, Sidoarjo desc', lat: 2, lng: 2, zoom: 2 },
			{ id: 3, name: 'Malang', description: 'Malang desc', lat: 3, lng: 3, zoom: 3 }
		];

		this.gmap = new GoogleMaps();
		this.gmap.search( 'kodam 5 brawijaya' ).then(( data ) => {
			data.forEach(( place ) => {
				this.gmap.placeDetail( place.id ).then(( placeDetail ) => {
					console.log( '----' );
					console.log( `${ place.name }: ${ place.description }` );
					console.log( `${ placeDetail.name }: ${ placeDetail.description }` );
				});
			});
		});

		this.render();
	}

	render() {
		const template = [
			'<div role="listbox" tabindex="0" aria-label="Location selector">',
				this.data.map(( item ) => {
					return `<div role="option" tabindex="-1" aria-label="${ item.name }"><div>${ item.name }</div></div>`;
				}).join( ' ' ),
			'</div>'
		].join( ' ' );

		this.$selector.html( template );
	}
}

// Create jQuery plugin wrapper.
$.fn.psLocationSelector = function() {
	this.each(( index, elem ) => {
		let $elem = $( elem ),
			dataName = 'psLocationSelector';

		if ( ! $elem.data( dataName ) ) {
			$elem.data( dataName, new LocationSelector( elem ) );
		}
	});
}
