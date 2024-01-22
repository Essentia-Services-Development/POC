import $ from 'jquery';

const URL = peepsodata.ajaxurl;
const UID = peepsodata.currentuserid;
const USER_ID = peepsodata.userid;

class PsPageBlogposts extends PsPageAutoload {
	constructor( container ) {
		super( container );

		this._search_url = URL;
		this._search_params = {
			action: 'peepsoblogposts_user_posts',
			uid: UID,
			user_id: USER_ID,
			page: 1,
			sort: 'desc'
		};

		$( () => {
			this.init_page();
		} );
	}

	init_page() {
		// exit if container is not found
		if ( ! this._search_$ct.length ) {
			return this;
		}

		this._search_$sortby = $( '.ps-js-blogposts-sortby' ).on( 'change', () => this._filter() );

		this._filter();
	}

	_search_render_html( html ) {
		return html || '';
	}

	_search_get_items() {
		return this._search_$ct.children( '.ps-blogposts__post' );
	}

	/**
	 * Override default fetching function.
	 * @param {Object} params
	 * @returns jQuery.Deferred
	 */
	_fetch( params ) {
		return $.Deferred( defer => {
			this._fetch_xhr && this._fetch_xhr.abort();
			this._fetch_xhr = $.post( this._search_url, params, response => {
				if ( response.success ) {
					defer.resolveWith( this, [ response.html ] );
				} else {
					defer.rejectWith( this, [ [ response.error ] ] );
				}
			} );
		} );
	}

	_filter() {
		let sort = this._search_$sortby.val();

		this._fetch_xhr && this._fetch_xhr.abort();
		this._search_params.sort = sort;
		this._search_params.page = 1;
		this._search();
	}
}

// Performs page initialization on document ready.
let instance = new PsPageBlogposts( '.ps-js-blogposts' );
