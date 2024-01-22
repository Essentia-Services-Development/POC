(function ($, factory) {
	PsPageAutoload = factory($);
})(jQuery, function ($) {
	var LOAD_MORE_ENABLE = 1,
		LOAD_MORE_ENABLE_MOBILE = 2,
		LOAD_MORE_ENABLE_DESKTOP = 3;

	function PsPageAutoload() {
		return this.init.apply(this, arguments);
	}

	PsPageAutoload.prototype = {
		/**
		 *
		 */
		init: function (prefix) {
			if (!prefix) {
				throw new Error('CSS prefix is not supplied!');
			}

			// Set loadmore button availability.
			var lmEnable = +peepsodata.loadmore_enable;
			if (peepso.isMobile()) {
				lmEnable = lmEnable === LOAD_MORE_ENABLE || lmEnable === LOAD_MORE_ENABLE_MOBILE;
			} else {
				lmEnable = lmEnable === LOAD_MORE_ENABLE || lmEnable === LOAD_MORE_ENABLE_DESKTOP;
			}

			this._css_prefix = prefix;
			this._config_loadmore_enable = lmEnable;
			this._config_loadmore_repeat = this._config_loadmore_enable
				? +peepsodata.loadmore_repeat
				: 0;

			$(_.bind(this.onDocumentLoaded, this));

			return this;
		},

		/**
		 *
		 */
		onDocumentLoaded: function () {
			this._search_$ct = $(this._css_prefix).eq(0);
			this._search_$trigger = $(this._css_prefix + '-triggerscroll');
			this._search_$loading = $(this._css_prefix + '-loading');
			this._search_$nomore = $(peepsodata.activity.template_no_more)
				.hide()
				.insertBefore(this._search_$trigger);

			if (!this._search_$ct.length) {
				return false;
			}

			this._search();
		},

		/**
		 *
		 */
		_search_url: '',

		/**
		 *
		 */
		_search_params: {},

		/**
		 *
		 */
		_search_loadmore_enable: null,

		/**
		 *
		 */
		_search: function () {
			this._search_toggle_autoscroll('off');
			this._search_toggle_loading('show');
			this._search_$ct.empty();
			this._search_$nomore.hide();

			// reset "load more" setting on first page
			if (this._search_params.page <= 1) {
				this._search_loadmore_enable = this._config_loadmore_enable;
				this._search_loadmore_repeat = this._config_loadmore_repeat;
				if (this._search_$loadmore) {
					this._search_$loadmore.remove();
					this._search_$loadmore = null;
				}
			}

			this._search_debounced();
		},

		/**
		 *
		 */
		_search_next: function () {
			this._search_toggle_autoscroll('off');
			this._search_toggle_loading('show');
			this._search_params.page++;
			this._search_debounced();
		},

		/**
		 *
		 */
		_search_debounced: function () {
			clearTimeout(this._search_debounced_timer);
			this._search_debounced_timer = setTimeout(
				_.bind(function () {
					this._fetch(this._search_params)
						.done(function (data) {
							var html = this._search_render_html(data);

							// Determine if next page is available.
							var has_next = !!html;
							if ('undefined' !== typeof data.has_next) {
								has_next = !!data.has_next;
							}

							this._search_toggle_loading('hide');
							if (html) {
								this._search_$ct.append(html);
							}

							if (has_next) {
								this._search_toggle_autoscroll('on');
							} else {
								this._search_$nomore.show();
							}
						})
						.fail(function (errors) {
							this._search_toggle_loading('hide');
							if (this._search_params.page <= 1) {
								this._search_$ct.html(errors.join('<br>'));
							} else {
								this._search_$nomore.show();
							}
						});
				}, this),
				500
			);
		},

		/**
		 * @returns {string|null} html
		 */
		_search_render_html: function () {
			throw new Error('This method must be implemented by subclass!');
		},

		/**
		 * @param {string} method
		 */
		_search_toggle_loading: function (method) {
			var toggleLoading = function (method, $img) {
				$img = this._search_$loading;
				if ($img.data('lazySrc')) {
					$img = this._search_$loading = $(this._css_prefix + '-loading');
				}
				method === 'show' ? $img.show() : $img.hide();
			};

			if (method === 'show') {
				clearTimeout(this._search_toggle_loading_timer);
				toggleLoading.call(this, method);
			} else if (method === 'hide') {
				this._search_toggle_loading_timer = setTimeout(
					$.proxy(function () {
						toggleLoading.call(this, method);
					}, this),
					1000
				);
			}
		},

		/**
		 * @param {string} method
		 */
		_search_toggle_autoscroll: function (method) {
			var evtName = 'scroll' + this._css_prefix,
				$win = $(peepso.observer.applyFilters('autoload_scrollable_container', window)),
				$btn;

			if (method === 'off') {
				$win.off(evtName);
			} else if (method === 'on' && this._search_$trigger.length) {
				if (this._search_loadmore_enable) {
					if (this._search_should_load_more()) {
						this._search_next();
					} else {
						this._search_$loadmore = $(
							peepsodata.activity.template_load_more
						).insertAfter(this._search_$ct);
						this._search_$loadmore.one(
							'click',
							$.proxy(function (e) {
								this._search_$loadmore.remove();
								if (!this._search_loadmore_repeat) {
									this._search_loadmore_enable = false;
								}
								this._search_next();
							}, this)
						);
					}
				} else {
					$win.off(evtName)
						.on(
							evtName,
							$.proxy(function () {
								if (this._search_should_load_more()) {
									this._search_next();
								}
							}, this)
						)
						.trigger(evtName);
				}
			}
		},

		/**
		 * @returns jQuery
		 */
		_search_get_items: function () {
			return $();
		},

		/**
		 * @returns boolean
		 */
		_search_should_load_more: function () {
			var limit = +peepsodata.activity_limit_below_fold,
				$items = this._search_get_items(),
				$lastItem,
				position;

			// Handle fixed-number batch load of items.
			if (this._search_loadmore_enable && this._search_loadmore_repeat) {
				if (this._search_params.page % this._search_loadmore_repeat === 0) {
					return false;
				} else {
					return true;
				}
			}

			limit = limit > 0 ? limit : 3;
			if (this._search_params.limit) {
				limit = limit * this._search_params.limit;
			}

			$lastItem = $items.slice(0 - limit).eq(0);
			if ($lastItem.length) {
				if (this._search_loadmore_enable) {
					position = $lastItem.eq(0).offset();
				} else {
					position = $lastItem.get(0).getBoundingClientRect();
				}
				if (position.top < (window.innerHeight || document.documentElement.clientHeight)) {
					return true;
				}
			}

			return false;
		},

		/**
		 * @param {object} params
		 * @returns jQuery.Deferred
		 */
		_fetch: function (params) {
			return $.Deferred(
				$.proxy(function (defer) {
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
	};

	return PsPageAutoload;
});
