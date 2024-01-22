(function( $, factory ) {

	var PsPageBlocked = factory( $ );
	var ps_page_blocked = new PsPageBlocked('.ps-js-blocked');

})( jQuery, function( $ ) {

function PsPageBlocked() {
	PsPageBlocked.super_.apply( this, arguments );
	$( $.proxy( this.init_page, this ) );
}

// inherit from `PsPageAutoload`
peepso.npm.inherits( PsPageBlocked, PsPageAutoload );

peepso.npm.objectAssign( PsPageBlocked.prototype, {

	_search_url: 'membersearch.search',

	_search_params: {
		uid: peepsodata.currentuserid,
		user_id: peepsodata.userid,
		query: undefined,
		order_by: undefined,
		order: undefined,
		peepso_gender: undefined,
		peepso_avatar: undefined,
		blocked: 1,
		limit: 2,
		page: 1
	},

	_search_render_html: function( data ) {
		if ( data.members && data.members.length ) {
			return data.members.join('');
		}
		return '';
	},

	_search_get_items: function() {
		return this._search_$ct.children('.ps-members-item-wrapper');
	},

	/**
	 * @param {object} params
	 * @returns jQuery.Deferred
	 */
	_fetch: function( params ) {
		return $.Deferred( $.proxy(function( defer ) {

			// Multiply limit value by 2 which translate to 2 rows each call.
			params = $.extend({}, params );
			if ( ! _.isUndefined( params.limit ) ) {
				params.limit *= 2;
			}

			this._fetch_xhr && this._fetch_xhr.abort();
			this._fetch_xhr = peepso.disableAuth().disableError().getJson( this._search_url, params, $.proxy(function( response ) {
				if ( response.success ) {
					defer.resolveWith( this, [ response.data ]);
				} else {
					defer.rejectWith( this, [ response.errors ]);
				}
			}, this ));
		}, this ));
	}

});

return PsPageBlocked;

});
