import $ from 'jquery';

const URL = peepsodata.ajaxurl;
const USER_ID = peepsodata.userid;

class PsPageTutorLMS extends PsPageAutoload {
	constructor( container ) {
		super( container );

		this._search_url = URL;
		this._search_params = {
			action: 'peepsotutorlms_user_courses',
			user_id: USER_ID,
			page: 1
		};
	}

	_search_render_html( html ) {
		return html || '';
	}

	_search_get_items() {
		return this._search_$ct.children( '.ps-tutorlms__course' );
	}

	_fetch( params ) {
		return $.Deferred( defer => {
			this._fetch_xhr && this._fetch_xhr.abort();
			this._fetch_xhr = $.post( this._search_url, params, response => {
				if ( response.success ) {
					defer.resolveWith( this, [ response.html ]);
				} else {
					defer.rejectWith( this, [[ response.error ]]);
				}
			});
		});
	}
}

// Performs page initialization on document ready.
let instance = new PsPageTutorLMS( '.ps-js-tutorlms' );
