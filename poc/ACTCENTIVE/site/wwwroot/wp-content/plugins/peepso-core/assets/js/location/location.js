import peepsodata from 'peepsodata';

(function (root, $, factory) {
	/**
	 * PsLocation global instance.
	 * @name pslocation
	 * @type {PsLocation}
	 */
	root.pslocation = new (factory(root, $))();

	// initialize location on create album dialog
	peepso.observer.addFilter(
		'photo_create_album',
		function (obj) {
			var $el = obj.popup,
				$input = $el.find('.ps-js-location');
			pslocation.init_location_search($input);
			return obj;
		},
		10,
		1
	);

	// edit location field
	$(function () {
		peepso.observer.addFilter(
			'profile_field_save',
			function (value, $input) {
				if ($input.hasClass('ps-js-field-location')) {
					var data = $input.data();
					if (data.location && data.latitude && data.longitude) {
						return JSON.stringify({
							name: data.location,
							latitude: data.latitude,
							longitude: data.longitude
						});
					}
				}
				return value;
			},
			10,
			2
		);

		peepso.observer.addAction(
			'profile_field_save_register',
			function ($input) {
				if ($input.hasClass('ps-js-field-location')) {
					var data = $input.data(),
						$hidden;

					if (data.location && data.latitude && data.longitude) {
						$hidden = $('<input type="hidden" name="' + $input.attr('name') + '" />');
						$input.removeAttr('name');
						$hidden.insertAfter($input);
						$hidden.val(
							JSON.stringify({
								name: data.location,
								latitude: data.latitude,
								longitude: data.longitude
							})
						);
					}
				}
			},
			10,
			1
		);

		var $input = $('.ps-js-field-location');
		$input.each(function () {
			pslocation.init_location_search($(this));
		});
	});

	// edit location
	$(function () {
		var $ct = $('.ps-js-album-location'),
			$text = $ct.find('.ps-js-album-location-text'),
			$empty = $ct.find('.ps-js-album-location-empty'),
			$editor = $ct.find('.ps-js-album-location-editor'),
			$btnEdit = $ct.find('.ps-js-album-location-edit'),
			$btnRemove = $ct.find('.ps-js-album-location-remove'),
			$submit = $editor.find('.ps-js-submit'),
			$input = $editor.find('input').eq(0),
			value;

		// edit location
		$btnEdit.click(function () {
			if ($editor.is(':visible')) {
				return;
			}

			$text.hide();
			$empty.hide();
			$btnEdit.hide();
			$btnRemove.hide();
			$editor.show();

			$input.data('original-value', (value = $input.val())); // save original value
			$input.focus().val('').val(value); // focus

			pslocation.init_location_search($input);

			$editor.off('click input');

			// handle cancel button
			$editor.on('click', '.ps-js-cancel', function () {
				$input.val(value);
				$editor.off('click').hide();
				$btnEdit.show();
				if (value) {
					$text.show();
					$btnRemove.show();
				} else {
					$empty.show();
				}
			});

			// handle save button
			$editor.on(
				'click',
				'.ps-js-submit',
				$.proxy(function (e) {
					var data = $input.data();
					var params = {
						user_id: peepsodata.userid,
						post_id: data.postId,
						type_extra_field: 'location',
						'location[name]': data.location,
						'location[latitude]': data.latitude,
						'location[longitude]': data.longitude,
						_wpnonce: $('#_wpnonce_set_album_location').val()
					};
					peepso.postJson('photosajax.set_album_extra_field', params, function (json) {
						if (json.success) {
							$editor.off('click').hide();
							$input.val(data.location);
							$text.find('span').html(data.location);
							$text.show();
							$empty.hide();
							$btnEdit.show();
							$btnRemove.show();
						}
					});
				}, this)
			);
		});

		// remove location
		$btnRemove.click(function () {
			var data = $btnRemove.data();
			var params = {
				user_id: peepsodata.userid,
				post_id: data.postId,
				type_extra_field: 'location',
				location: '',
				_wpnonce: $('#_wpnonce_set_album_location').val()
			};
			peepso.postJson('photosajax.set_album_extra_field', params, function (json) {
				if (json.success) {
					$input.val('');
					$text.find('span').html('');
					$text.hide();
					$empty.show();
					$btnRemove.hide();
				}
			});
		});
	});
})(window, jQuery, function (window, $) {
	/**
	 * PeepSo geolocation class.
	 * @class PsLocation
	 */
	function PsLocation() {
		this.coords = null;
		this.$places_container = null;
		this.$input_search = null;
		this.marker = null;
		this.map = null;
		this.selected_place = null;
		this._search_service = null;
		this._latLang = null;
		this.last_selected_place = null;
		this.location_selected = false;
		this.can_submit = false;
	}

	/**
	 * Initializes this instance's container and selector reference to a postbox instance.
	 * Called on postbox.js _load_addons()
	 */
	PsLocation.prototype.init = function () {
		if (_.isNull(this.$postbox)) {
			return;
		}

		var that = this;

		peepso.observer.addFilter(
			'peepso_postbox_can_submit',
			function (can_submit) {
				can_submit.soft.push(that.can_submit);
				return can_submit;
			},
			30,
			1
		);

		this.$container = this.$postbox.find('#location-tab');

		$(this.$postbox).on('click', '#location-tab a', function () {
			that.toggle_input();
		});

		this.$input_search = $('[name=postbox_loc_search]', this.$postbox);
		this.$dropdown = $('#pslocation', this.$postbox).on('click', function (e) {
			e.stopPropagation();
		});
		this.$postboxcontainer = this.$postbox.$textarea.parent();
		this.$places_container = $('.ps-js-postbox-locations', this.$dropdown);

		// Add delay 15 seconds before call 'location_search()' to give user enough time to type new location manually
		// It's important because 'location_search()' will trigger 'click' event to draw map using first location
		var timer = null;
		this.$input_search.on('keyup', function () {
			var t = this;
			clearTimeout(timer);
			var $loading = $(
				'<div class="ps-postbox__location-item ps-postbox__location-item--loading ps-js-postbox-location-item">' +
					$('#pslocation-search-loading').html() +
					'</div>'
			);
			that.$places_container.html($loading);
			timer = setTimeout(function () {
				that.location_search($(t).val());
			}, 1500);
		});

		peepso.observer.addFilter(
			'postbox_req_' + this.$postbox.guid,
			function (req, other) {
				return that.postbox_request(req, other);
			},
			10,
			1
		);

		this.$postbox.on(
			'postbox.post_cancel postbox.post_saved',
			function (evt, request, response) {
				that.postbox_cancel_saved(request, response);
			}
		);

		this.$select_location = $('.ps-js-location-action .ps-js-add-location', this.$dropdown);
		this.$remove_location = $('.ps-js-location-action .ps-js-remove-location', this.$dropdown);

		this.$select_location.on('click', function (e) {
			e.preventDefault();
			that.on_select_location();
		});
		this.$remove_location.on('click', function (e) {
			e.preventDefault();
			that.on_remove_location();
		});

		$(this.$postbox).on('peepso.interaction-hide', '#location-tab a', function () {
			that.$dropdown.hide();
			that.$container.removeClass('ps-postbox__menu-item--open');
		});

		peepso.observer.addFilter(
			'peepso_postbox_addons_update',
			function (list) {
				if (that.location_selected) {
					list.unshift(
						'<b><i class=ps-icon-map-marker></i>' + that.location_selected + '</b>'
					);
				}
				return list;
			},
			10,
			1
		);
	};

	/**
	 * Adds the selected location/place when Post button is clicked and before submitted
	 * @param {object} postbox request object
	 * @param {mixed} other currently not in used
	 */
	PsLocation.prototype.postbox_request = function (req, other) {
		if (null !== this.selected_place) {
			req.location = {
				name: this.selected_place.name,
				latitude: this.selected_place.geometry.location.lat(),
				longitude: this.selected_place.geometry.location.lng()
			};
		}
		return req;

		peepso.observer.addFilter(
			'postbox_req' + this.$postbox.guid,
			function (req, other) {
				if (null !== that.selected_place) {
					req.location = {
						name: that.selected_place.name,
						latitude: that.selected_place.geometry.location.lat(),
						longitude: that.selected_place.geometry.location.lng()
					};
				}
				return req;
			},
			10,
			1
		);
	};

	/**
	 * Called after postbox is saved or cancelled
	 * @param {object} request Postbox request object - available only for after saved
	 * @param {object} response Postbox response - available only for after saved
	 */
	PsLocation.prototype.postbox_cancel_saved = function (request, response) {
		this.$dropdown.hide();
		this.$input_search.val('');
		this.$remove_location.hide();
		//this.$select_location.hide();
		this.$select_location.show();
		this.$postboxcontainer.find('span#postlocation').remove();
		this.$container.removeClass('active');

		// Reset tooltip.
		var $tooltip = this.$container.find('.ps-js-interaction-toggle');
		if ($tooltip.attr('data-tooltip-original')) {
			$tooltip.attr('data-tooltip', $tooltip.attr('data-tooltip-original'));
			$tooltip.removeAttr('data-tooltip-original');
		}

		this.selected_place = null;
		this.location_selected = false;
		this.can_submit = false;
		this.$postbox.on_change();
	};

	/**
	 * Defines the postbox this instance is running on.
	 * Called on postbox.js _load_addons()
	 * @param {object} postbox This refers to the parent postbox object which this plugin may inherit, override, and manipulate its input boxes and behavior
	 */
	PsLocation.prototype.set_postbox = function (postbox) {
		this.$postbox = postbox;
	};

	/**
	 * Searches for a location using the google API
	 * @param {string} query The location to search for.
	 * @param {function} success_callback Function to run after the search is complete.
	 */
	PsLocation.prototype.location_search = function (query, success_callback) {
		var that = this;

		if (_.isEmpty(this.map)) {
			this._latLang = new google.maps.LatLng(0, 0);
			this.draw_map(this._latLang);
		}

		if (_.isEmpty(query)) {
			this.draw_map(this._latLang);
			return;
		}

		this.get_search_service().textSearch(
			{
				query: query,
				location: this._latLang,
				radius: 50000
			},
			function (results, status) {
				that.set_places(results, status);

				// Uses first location to draw map
				if (!that.$select_location.is(':visible')) {
					that.$places_container
						.find('.ps-js-postbox-location-item')
						.first()
						.trigger('click');
				}

				if (typeof Function === typeof success_callback) {
					success_callback();
				}
			}
		);
	};

	/**
	 * Sets the location value and appends the location name to the postbox.
	 */
	PsLocation.prototype.on_select_location = function () {
		if (null === this.selected_place) {
			this.selected_place = this.last_selected_place;
		}

		this.$select_location.hide();
		this.$remove_location.show();

		this.$dropdown.hide();
		this.$container.addClass('active');
		this.$container.removeClass('ps-postbox__menu-item--open');

		// Update tooltip.
		var $tooltip = this.$container.find('.ps-js-interaction-toggle');
		if (!$tooltip.attr('data-tooltip-original')) {
			$tooltip.attr('data-tooltip-original', $tooltip.attr('data-tooltip'));
		}
		$tooltip.attr('data-tooltip', this.selected_place.name);

		this.location_selected = '';
		if (this.selected_place) {
			this.location_selected = this.selected_place.name;
		}

		this.can_submit = true;
		this.$postbox.on_change();
	};

	/**
	 * Removes the location value and name on the postbox
	 */
	PsLocation.prototype.on_remove_location = function () {
		this.$select_location.show();
		this.$remove_location.hide();

		this.selected_place = null;
		this.$postboxcontainer.find('span#postlocation').remove();
		this.$dropdown.hide();
		this.$container.removeClass('active');
		this.$container.removeClass('ps-postbox__menu-item--open');

		// Reset tooltip.
		var $tooltip = this.$container.find('.ps-js-interaction-toggle');
		if ($tooltip.attr('data-tooltip-original')) {
			$tooltip.attr('data-tooltip', $tooltip.attr('data-tooltip-original'));
			$tooltip.removeAttr('data-tooltip-original');
		}

		this.location_selected = false;
		this.can_submit = false;
		this.$postbox.on_change();
	};

	/**
	 * Toggles the display of the location UI.
	 */
	PsLocation.prototype.toggle_input = function () {
		if (this.$dropdown.is(':visible')) {
			this.$dropdown.hide();
			this.$container.removeClass('ps-postbox__menu-item--open');
			jQuery(document).off('mouseup.ps-postbox-location');
		} else {
			this.$dropdown.show();
			this.$container.addClass('ps-postbox__menu-item--open');

			// Add autohide on document-click.
			setTimeout(
				$.proxy(function () {
					jQuery(document)
						.off('mouseup.ps-postbox-location')
						.on(
							'mouseup.ps-postbox-location',
							$.proxy(function (e) {
								if (this.$container.has(e.target).length === 0) {
									this.$dropdown.hide();
									this.$container.removeClass('ps-postbox__menu-item--open');
									jQuery(document).off('mouseup.ps-postbox-location');
								}
							}, this)
						);
				}, this),
				1
			);
		}

		this.$input_search.val('');
		this.location = null;

		if (this.$dropdown.is(':visible')) {
			var that = this;
			this.load_library(
				function () {
					that.shown();
				}.bind(that)
			);
		}
	};

	/**
	 * Fires after the location UI is shown and asks the user for geolocation information.
	 */
	PsLocation.prototype.shown = function () {
		var that = this;

		this.$input_search.focus();

		// Only draw the map once per page load
		if (false === _.isEmpty(this.map)) {
			return;
		}

		this.detect_location()
			.done(function (lat, lng) {
				that.draw_default_map(lat, lng);
			})
			.fail(function () {
				that.draw_default_map();
			});
	};

	/**
	 * Uses the user's current location to draw the map
	 */
	PsLocation.prototype.draw_default_map = function (lat, lng) {
		if (lat && lng) {
			var location = new google.maps.LatLng(lat, lng);
			this.draw_map(location);
		} else {
			var $map = this.$postbox.find('.ps-js-postbox-map');
			$map.show();
			this.$input_search.removeAttr('disabled');
			this.$input_search.focus();
		}
	};

	/**
	 * Draws the google map
	 * @param {object} location The default center/marker coordinates(latitude and longitude) of google.maps.LatLng object used to render maps
	 * @param {boolean} search_nearby If true, search nearby places/locations. Default is true.
	 */
	PsLocation.prototype.draw_map = function (location, search_nearby) {
		if (false === _.isBoolean(search_nearby)) {
			search_nearby = true;
		}

		if (false === location instanceof google.maps.LatLng) {
			return;
		}

		var $map = this.$postbox.find('.ps-js-postbox-map');

		$('#pslocation .ps-postbox-loading', this.$postbox).hide();
		$map.show();
		this.$input_search.removeAttr('disabled');

		var that = this;
		this._latLang = location;

		var mapOptions = {
			center: location,
			zoom: 15,
			draggable: false,
			scrollwheel: false,
			disableDefaultUI: true
		};

		peepso.observer.applyFilters(
			'ps_location_before_draw_map',
			$('#pslocation', this.$postbox)
		);

		// Draw map
		if (_.isEmpty(this.map)) {
			this.map = new google.maps.Map($map.get(0), mapOptions);

			// Draw marker
			this.marker = new google.maps.Marker({
				position: mapOptions.center,
				map: this.map,
				title: 'You are here (more or less)'
			});
		} else {
			this.set_map_center(this._latLang);
		}

		if (search_nearby) {
			// Search nearby places, default action
			var request = {
				location: this._latLang,
				types: ['establishment'],
				rankBy: google.maps.places.RankBy.DISTANCE
			};

			this.get_search_service().nearbySearch(request, function (results, status) {
				that.set_places(results, status);
				if (!that.$select_location.is(':visible')) {
					that.$places_container
						.find('.ps-js-postbox-location-item')
						.first()
						.trigger('click');
				}
			});
		}
	};

	/**
	 * Returns an instance of the google places service
	 */
	PsLocation.prototype.get_search_service = function () {
		if (_.isEmpty(this.search_service)) {
			this._search_service = new google.maps.places.PlacesService(this.map);
		}

		return this._search_service;
	};

	/**
	 * Renders the retrieved places to the dropdown.
	 * @param {array} results for google maps places
	 * @param {int} status of google maps search
	 */
	PsLocation.prototype.set_places = function (results, status) {
		var that = this;
		this.$places_container.find('.ps-js-postbox-location-item').remove();

		if (status === google.maps.places.PlacesServiceStatus.OK) {
			for (var i = 0; i < results.length; i++) this.add_place(results[i]);
		}

		$('.ps-js-postbox-location-item', this.$places_container).on('click', function () {
			$('.ps-js-location-action', this.$dropdown).show();
			that.$select_location.show();
			that.$remove_location.hide();
		});
	};

	/**
	 * Adds the place to the search list.
	 * @param {object} place Contains the details of the place/location in google.maps.Map object which represents a single option in the search result
	 */
	PsLocation.prototype.add_place = function (place) {
		if (!_.isEmpty(place.formatted_address)) {
			place.vicinity = place.formatted_address;
		}

		if (_.isEmpty(place.vicinity)) {
			return;
		}

		var that = this;

		var $li = $('<div class="ps-postbox__location-item ps-js-postbox-location-item"></div>');
		$li.append('<p>' + place.name + '</p>');

		$li.append('<span>' + place.vicinity + '</span>');

		this.$places_container.append($li);

		$li.on('click', function () {
			that.set_map_center(place.geometry.location);
			that.$input_search.val(place.name);
			that.selected_place = place;
			that.last_selected_place = that.selected_place;
		});
	};

	/**
	 * Draw a marker and center the view point to the location
	 * @param {object} location A google latlang instance.
	 */
	PsLocation.prototype.set_map_center = function (location) {
		this.map.setCenter(location);
		this.marker.setPosition(location);
	};

	/**
	 * TODO: docblock
	 */
	PsLocation.prototype.load_library = function (callback) {
		if (this.gmap_is_loaded) {
			callback();
			return;
		}

		this.load_library_callbacks || (this.load_library_callbacks = []);
		this.load_library_callbacks.push(callback);

		if (this.gmap_is_loading) {
			return;
		}

		this.gmap_is_loading = true;

		var script = document.createElement('script');
		var api_key = peepsodata.location.api_key;
		var that = this;

		script.type = 'text/javascript';
		script.src =
			'https://maps.googleapis.com/maps/api/js?libraries=places' +
			(api_key ? '&key=' + api_key : '') +
			'&callback=ps_gmap_callback';

		window.ps_gmap_callback = function () {
			that.gmap_is_loaded = true;
			that.gmap_is_loading = false;
			while (that.load_library_callbacks.length) {
				that.load_library_callbacks.shift()();
			}
			delete window.ps_gmap_callback;
		};

		document.body.appendChild(script);
	};

	/**
	 * TODO: docblock
	 */
	PsLocation.prototype.show_map = function (lat, lng, name) {
		peepso.lightbox(
			[
				{
					content: '<div class="ps-location__map ps-js-mapct" />'
				}
			],
			{
				simple: true,
				nofulllink: true,
				afterchange: $.proxy(function (lightbox) {
					this.load_library(function () {
						var mapct = lightbox.$container.find('.ps-js-mapct');
						var location = new google.maps.LatLng(lat, lng);
						var map = new google.maps.Map(mapct[0], {
							center: location,
							zoom: 14
						});

						var marker = new google.maps.Marker({
							position: location,
							map: map
						});
					});
				}, this)
			}
		);
	};

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * TODO: docblock
	 */
	PsLocation.prototype.init_location_search = function ($input) {
		if ($input.data('location-search')) {
			return;
		}

		$input.data('location-search', 1);

		var template = peepsodata.location.template_selector;

		var $div = $(template),
			$loc = $div.children('.ps-js-location'),
			$map = $div.find('.ps-js-location-map'),
			$list = $div.find('.ps-js-location-list'),
			$close = $div.find('.ps-js-close'),
			$select = $div.find('.ps-js-select'),
			$remove = $div.find('.ps-js-remove'),
			$loading = $div.find('.ps-js-location-loading'),
			$result = $div.find('.ps-js-location-result'),
			$placeholder = $div.find('.ps-js-location-placeholder'),
			listitem = $div.find('.ps-js-location-listitem').get(0).outerHTML;

		$input.on(
			'input.ps-location',
			$.proxy(
				_.debounce(function (e) {
					var query = e.target.value;
					if (!query) {
						return;
					}
					if ($placeholder) {
						$placeholder.remove();
						$placeholder = null;
					}
					$result.hide();
					$loading.show();
					$div.show();
					this.search(query).done(function (results) {
						var html = [],
							description,
							item,
							i;
						for (i = 0; i < results.length; i++) {
							description = results[i].description;
							description = description.split(/,\s(.+)?/);
							item = listitem
								.replace('{place_id}', results[i].place_id)
								.replace('{name}', description[0])
								.replace('{description}', description[1] || '&nbsp;');
							html.push(item);
						}
						$list.html(html.join(''));
						$loading.hide();
						$result.show();
						$div.show();
					});
				}, 200),
				this
			)
		);

		$input.on(
			'blur.ps-location',
			$.proxy(function (e) {
				$div.hide();
				$select.hide();
				$input.val($input.data('location') || '');
				if ($input.data('location')) {
					$remove.show();
				}
			}, this)
		);

		$input.on(
			'focus.ps-location',
			$.proxy(function (e) {
				$list.find('.ps-location-selected').removeClass('ps-location-selected');
				$div.show();
			}, this)
		);

		$list.on(
			'mousedown',
			'a.ps-js-location-listitem',
			$.proxy(function (e) {
				var $item = $(e.currentTarget),
					name = $item.find('.ps-js-location-listitem-name').text(),
					id = $item.data('place-id');

				e.preventDefault();
				e.stopPropagation();

				$item.addClass('ps-location-selected');
				$item.siblings().removeClass('ps-location-selected');
				$select.show();
				$remove.hide();
				$map.show();
				this._gmap_get_place_detail(id).done(
					$.proxy(function (place) {
						var name = place.formatted_address,
							loc = place.geometry.location;

						$input
							.data('tmp-location', name)
							.data('tmp-latitude', loc.lat())
							.data('tmp-longitude', loc.lng());
						this._gmap_render_map($map[0], place);
					}, this)
				);
			}, this)
		);

		$close.on('mousedown', function () {
			$input.trigger('blur.ps-location');
		});

		$select.on('mousedown', function (e) {
			e.preventDefault();
			e.stopPropagation();
			$input.data('location', $input.data('tmp-location'));
			$input.data('latitude', $input.data('tmp-latitude'));
			$input.data('longitude', $input.data('tmp-longitude'));
			$input.val($input.data('location'));
			$select.hide();
			$remove.show();
			$input.trigger('blur.ps-location');
		});

		$remove.on('mousedown', function (e) {
			e.preventDefault();
			e.stopPropagation();
			$input.removeData('location').removeData('latitude').removeData('longitude').val('');
			$list.find('.ps-location-selected').removeClass('ps-location-selected');
			$remove.hide();
			$map.hide();
		});

		$div.insertAfter($input);
	};

	/**
	 *
	 */
	PsLocation.prototype.search = function (query) {
		return $.Deferred(
			$.proxy(function (defer) {
				this._gmap_get_autocomplete_service().done(function (service) {
					service.getPlacePredictions({ input: query }, function (results, status) {
						if (status === 'OK') {
							defer.resolve(results);
						}
					});
				});
			}, this)
		);
	};

	/**
	 *
	 */
	PsLocation.prototype.detect_location = function () {
		var that = this;
		return $.Deferred(function (defer) {
			if (window.location.protocol !== 'https:') {
				defer.reject();
			} else {
				that.detect_location_by_device()
					.done(function (lat, lng) {
						defer.resolve(lat, lng);
					})
					.fail(function () {
						that.detect_location_by_gmap_api()
							.done(function (lat, lng) {
								defer.resolve(lat, lng);
							})
							.fail(function () {
								that.detect_location_by_ip()
									.done(function (lat, lng) {
										defer.resolve(lat, lng);
									})
									.fail(function () {
										defer.reject();
									});
							});
					});
			}
		});
	};

	/**
	 *
	 */
	PsLocation.prototype.detect_location_by_device = function () {
		return $.Deferred(
			$.proxy(function (defer) {
				navigator.geolocation.getCurrentPosition(
					function (position) {
						defer.resolve(position.coords.latitude, position.coords.longitude);
					},
					function () {
						defer.reject();
					},
					{
						timeout: 10000
					}
				);
			}, this)
		);
	};

	/**
	 *
	 */
	PsLocation.prototype.detect_location_by_gmap_api = function () {
		return $.Deferred(
			$.proxy(function (defer) {
				var api_key = peepsodata.location.api_key;
				if (this._client_location) {
					defer.resolve(this._client_location);
				} else if (!api_key) {
					defer.reject();
				} else {
					$.post(
						'https://www.googleapis.com/geolocation/v1/geolocate?key=' + api_key,
						function (coords) {
							defer.resolve(coords.location.lat, coords.location.lng);
						}
					).fail(function (error) {
						defer.reject(error);
					});
				}
			}, this)
		);
	};

	/**
	 *
	 */
	PsLocation.prototype.detect_location_by_ip = function () {
		return $.Deferred(
			$.proxy(function (defer) {
				var success;
				$.ajax({
					url: 'https://ipapi.co/jsonp',
					dataType: 'jsonp',
					success: function (json) {
						var lat = json.latitude,
							lng = json.longitude;
						if (lat && lng) {
							success = true;
							defer.resolve(lat, lng);
						}
					},
					complete: function () {
						if (!success) {
							defer.reject();
						}
					}
				});
			}, this)
		);
	};

	/**
	 *
	 */
	PsLocation.prototype._gmap_load_library = function () {
		return $.Deferred(
			$.proxy(function (defer) {
				this.load_library(function () {
					defer.resolve();
				});
			}, this)
		);
	};

	/**
	 *
	 */
	PsLocation.prototype._gmap_get_autocomplete_service = function () {
		return $.Deferred(
			$.proxy(function (defer) {
				if (this._gmap_autocomplete_service) {
					defer.resolve(this._gmap_autocomplete_service);
				} else {
					this._gmap_load_library().done(
						$.proxy(function () {
							this._gmap_autocomplete_service =
								new google.maps.places.AutocompleteService();
							defer.resolve(this._gmap_autocomplete_service);
						}, this)
					);
				}
			}, this)
		);
	};

	PsLocation.prototype._gmap_render_map = function (div, place) {
		var location, viewport, map, marker;

		if (place.geometry) {
			location = place.geometry.location;
			viewport = place.geometry.viewport;
		} else {
			location = new google.maps.LatLng(place.latitude, place.longitude);
		}

		div = $(div).show();
		map = div.data('ps-map');
		marker = div.data('ps-map-marker');

		if (!map) {
			map = new google.maps.Map(div[0], {
				center: location,
				zoom: 15,
				draggable: false,
				scrollwheel: false,
				disableDefaultUI: true
			});
			div.data('ps-map', map);
		}

		if (!marker) {
			marker = new google.maps.Marker({
				position: location,
				map: map,
				title: 'You are here (more or less)'
			});
			div.data('ps-map-marker', marker);
		}

		map.setCenter(location);
		marker.setPosition(location);
		if (viewport) {
			map.fitBounds(viewport);
		} else {
			map.setZoom(15);
		}
	};

	/**
	 *
	 */
	PsLocation.prototype._gmap_get_place_service = function () {
		return $.Deferred(
			$.proxy(function (defer) {
				if (this._gmap_place_service) {
					defer.resolve(this._gmap_place_service);
				} else {
					this._gmap_load_library().done(
						$.proxy(function () {
							var div = document.createElement('div');
							document.body.appendChild(div);
							this._gmap_place_service = new google.maps.places.PlacesService(div);
							defer.resolve(this._gmap_place_service);
						}, this)
					);
				}
			}, this)
		);
	};

	/**
	 *
	 */
	PsLocation.prototype._gmap_get_place_detail = function (id) {
		return $.Deferred(
			$.proxy(function (defer) {
				if (this._gmap_place_cache && this._gmap_place_cache[id]) {
					defer.resolve(this._gmap_place_cache[id]);
				} else {
					this._gmap_get_place_service().done(
						$.proxy(function (service) {
							service.getDetails(
								{ placeId: id },
								$.proxy(function (place, status) {
									if (status === 'OK') {
										this._gmap_place_cache || (this._gmap_place_cache = {});
										this._gmap_place_cache[id] = place;
										defer.resolve(place);
									} else {
										defer.reject(status);
									}
								}, this)
							);
						}, this)
					);
				}
			}, this)
		);
	};

	/**
	 * Adds a new PsLocation object to a postbox instance.
	 * @param {array} addons An array of addons to plug into the postbox.
	 */
	peepso.observer.addFilter(
		'peepso_postbox_addons',
		function (addons) {
			addons.push(new PsLocation());
			return addons;
		},
		10,
		1
	);

	//
	return PsLocation;
});
// EOF
