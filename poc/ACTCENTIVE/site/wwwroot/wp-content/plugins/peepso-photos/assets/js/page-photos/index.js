import $ from 'jquery';
import { ls } from 'peepso';

// Photos and albums arragement.
$(function () {
	let modes = ['small', 'large'],
		$modes = $('.ps-js-photos-viewmode'),
		$list = $('.ps-js-photos, .ps-js-albums');

	if (!($modes.length && $list.length)) {
		return;
	}

	function toggle(mode) {
		mode = modes.indexOf(mode) >= 0 ? mode : modes[0];

		$modes.removeClass('ps-btn--active');
		$modes.filter(`[data-mode="${mode}"]`).addClass('ps-btn--active');
		$list.removeClass(modes.map((mode) => `ps-photos__list--${mode}`).join(' '));
		$list.addClass(`ps-photos__list--${mode}`);

		ls.set('photos_viewmode', mode);
	}

	toggle(ls.get('photos_viewmode'));
	$modes.on('click', function (e) {
		e.preventDefault();
		toggle($(this).data('mode'));
	});
});

// Albums arrangement.
$(function () {});

(function (factory) {
	var PsPagePhotos = factory();
	var ps_page_photos = new PsPagePhotos('.ps-js-photos');
})(function () {
	function PsPagePhotos() {
		PsPagePhotos.super_.apply(this, arguments);
		$($.proxy(this.init_page, this));
	}

	// inherit from `PsPageAutoload`
	peepso.npm.inherits(PsPagePhotos, PsPageAutoload);

	peepso.npm.objectAssign(PsPagePhotos.prototype, {
		init_page: function () {
			var albumId = +peepsophotosdata.album_id;

			// update url and params on album photos page
			if (albumId) {
				this._search_url = 'photosajax.get_user_photos_album';
				this._search_params.album_id = albumId;
			}

			this._search_$sortby = $('.ps-js-photos-sortby').on(
				'change',
				$.proxy(this._filter, this)
			);
			if (this._search_$sortby.length) {
				this._search_$sortby.trigger('change');
			}

			// Remove item after delete post action.
			peepso.observer.addAction(
				'peepso_delete_post',
				function (postId) {
					var $item = $('.ps-js-photo').filter('[data-post-id="' + postId + '"]');
					$item.remove();
				},
				10,
				1
			);
		},

		_search_url: 'photosajax.get_user_photos',

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

			return data.photos || '';
		},

		_search_get_items: function () {
			return this._search_$ct.children('.ps-js-photo');
		},

		/**
		 * @param {object} params
		 * @returns jQuery.Deferred
		 */
		_fetch: function (params) {
			return $.Deferred(
				$.proxy(function (defer) {
					params = peepso.observer.applyFilters('photos_get_list_photos', params);

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
		},

		/**
		 * Filter search based on selected elements.
		 */
		_filter: function () {
			var sortby = this._search_$sortby.val();

			// abort current request
			this._fetch_xhr && this._fetch_xhr.abort();

			this._search_params.sort = sortby;
			this._search_params.page = 1;
			this._search();
		}
	});

	return PsPagePhotos;
});
