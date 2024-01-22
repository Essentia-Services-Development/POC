(function ($, factory) {
	var PsPageAlbums = factory($);
	var ps_page_albums = new PsPageAlbums('.ps-js-albums');
})(jQuery, function ($) {
	function PsPageAlbums() {
		PsPageAlbums.super_.apply(this, arguments);
		$($.proxy(this.init_page, this));
	}

	// inherit from `PsPageAutoload`
	peepso.npm.inherits(PsPageAlbums, PsPageAutoload);

	peepso.npm.objectAssign(PsPageAlbums.prototype, {
		init_page: function () {},

		_search_url: 'photosajax.get_list_albums',

		_search_params: {
			uid: peepsodata.currentuserid,
			user_id: peepsodata.userid,
			sort: 'desc',
			limit: 4,
			page: 1
		},

		_search_render_html: function (data) {
			_.defer(
				$.proxy(function () {
					this._search_$ct
						.find('.ps-js-beforeloaded')
						.toggleClass('ps-js-beforeloaded loaded');
				}, this)
			);

			return data.albums || '';
		},

		_search_get_items: function () {
			return this._search_$ct.children('.ps-js-album');
		},

		/**
		 * @param {object} params
		 * @returns jQuery.Deferred
		 */
		_fetch: function (params) {
			return $.Deferred(
				$.proxy(function (defer) {
					params = peepso.observer.applyFilters('photos_get_list_albums', params);

					// Multiply limit value by 2 which translate to 2 rows each call.
					params = $.extend({}, params);
					if (!_.isUndefined(params.limit)) {
						params.limit *= 2;
					}

					this._fetch_xhr && this._fetch_xhr.abort();
					this._fetch_xhr = peepso.getJson(
						this._search_url,
						params,
						$.proxy(function (response) {
							if (response.success) {
								defer.resolveWith(this, [response.data]);
							} else {
								defer.rejectWith(this, [response.errors]);
							}
						}, this)
					);
				}, this)
			);
		}
	});

	return PsPageAlbums;
});
