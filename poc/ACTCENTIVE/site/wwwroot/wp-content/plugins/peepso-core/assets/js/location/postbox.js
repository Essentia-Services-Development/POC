(function ($, factory) {
	var PsPostboxLocation = factory($);

	peepso.observer.addAction(
		'postbox_init',
		function (postbox) {
			var inst = new PsPostboxLocation(postbox);
		},
		10,
		1
	);
})(jQuery, function ($) {
	var evtSuffix = '.ps-postbox-location';

	/**
	 * Postbox location addon.
	 */
	function PsPostboxLocation() {
		this.__constructor.apply(this, arguments);
	}

	PsPostboxLocation.prototype = {
		__constructor: function (postbox) {
			var template = (peepsodata.location && peepsodata.location.template_postbox) || '';

			this.postbox = postbox;

			// element caches
			this.$container = postbox.$el.find('#location-tab');
			this.$toggle = this.$container.find('.ps-js-postbox-toggle');
			this.$dropdown = this.$container.find('.ps-js-postbox-location').html(template);
			this.$input = this.$dropdown.find('input[type=text]');
			this.$loading = this.$dropdown.find('.ps-js-location-loading');
			this.$result = this.$dropdown.find('.ps-js-location-result');
			this.$map = this.$dropdown.find('.ps-js-location-map');
			this.$select = this.$dropdown.find('.ps-js-select');
			this.$remove = this.$dropdown.find('.ps-js-remove');

			// item template
			this.listItemTemplate = peepso.template(
				this.$dropdown.find('.ps-js-location-fragment').text()
			);

			// event handler
			this.$toggle.on('click' + evtSuffix, $.proxy(this.onToggle, this));
			this.$input.on('input' + evtSuffix, $.proxy(this.onInput, this));
			this.$result.on(
				'mousedown' + evtSuffix,
				'.ps-js-location-listitem',
				$.proxy(this.onSelectItem, this)
			);
			this.$result.on('click' + evtSuffix, '.ps-js-location-listitem', e =>
				e.preventDefault()
			);
			this.$select.on('mousedown' + evtSuffix, $.proxy(this.onSelect, this));
			this.$remove.on('mousedown' + evtSuffix, $.proxy(this.onRemove, this));

			// filters and actions
			postbox.addAction('update', this.update, 10, 2, this);
			postbox.addFilter('render_addons', this.render, 10, 1, this);
			postbox.addFilter('data', this.filterData, 10, 1, this);
			postbox.addFilter('data_validate', this.validate, 10, 2, this);
		},

		show: function () {
			this.$dropdown.show();
			this.$container.addClass('ps-postbox__menu-item--open');

			// Add autohide on document-click.
			setTimeout(() => {
				$(document)
					.off('mouseup.ps-postbox-location')
					.on('mouseup.ps-postbox-location', e => {
						if (this.$container.has(e.target).length === 0) {
							this.hide();
						}
					});
			}, 1);

			// check whether initial value needs to be updated
			if (this._needUpdate) {
				this._needUpdate = false;

				if (this._selected) {
					this.$select.hide();
					this.$remove.show();
					this.$result.empty();
					this.$map.children().show();
					this.$toggle.addClass('active');

					this.updateList([
						{
							place_id: '',
							name: this._selected.name,
							description: this._selected.description
						}
					]);

					this.updateMap({
						latitude: this._selected.latitude,
						longitude: this._selected.longitude,
						zoom: this._selected.zoom
					});
				} else {
					this.$select.hide();
					this.$remove.hide();
					this.$result.empty();
					this.$map.children().hide();
					this.$toggle.removeClass('active');

					this.updateList([]);
				}
			}
		},

		hide: function () {
			this.$dropdown.hide();
			this.$container.removeClass('ps-postbox__menu-item--open');
			$(document).off('mouseup.ps-postbox-location');
		},

		toggle: function () {
			if (this.$dropdown.is(':visible')) {
				this.hide();
			} else {
				this.show();
			}
		},

		search: function (query) {
			this.$result.empty().append(this.$loading);
			pslocation.search(query).done(
				$.proxy(function (results) {
					var list = [],
						description;

					for (var i = 0; i < results.length; i++) {
						description = results[i].description;
						description = description.split(/,\s(.+)?/);
						list.push({
							place_id: results[i].place_id,
							name: description[0],
							description: description[1]
						});
					}

					this.$loading.detach();
					this.updateList(list);
				}, this)
			);
		},

		filterData: function (data) {
			if (this._selected) {
				data.location = this._selected;
			} else {
				data.location = '';
			}
			return data;
		},

		validate: function (valid, data) {
			if (this._selected) {
				return true;
			}
			return valid;
		},

		render: function (list) {
			var html;
			if (this._selected) {
				html = '<i class="gcis gci-map-marker-alt"></i>';
				html += '<strong>' + this._selected.name + '</strong>';
				list.push(html);
			}
			return list;
		},

		update: function (data) {
			data = (data && data.data) || {};

			if (data.location) {
				this._selected = {
					name: data.location.name,
					description: data.location.description,
					latitude: data.location.latitude,
					longitude: data.location.longitude,
					zoom: data.location.zoom
				};

				this.$input.data('location', data.location.name);
				this.$input.data('latitude', data.location.latitude);
				this.$input.data('longitude', data.location.longitude);
				this.$input.val(data.location.name);
				this.$toggle.addClass('active');
			} else {
				this._selected = false;
				this.$toggle.removeClass('active');
			}

			this._needUpdate = true;
			this.postbox.doAction('refresh');
		},

		updateList: function (list) {
			var html = [];
			for (var i = 0; i < list.length; i++) {
				html.push(this.listItemTemplate(list[i]));
			}
			this.$result.html(html.join(''));
		},

		updateMap: function (location) {
			pslocation._gmap_load_library().done(
				$.proxy(function () {
					pslocation._gmap_render_map(this.$map[0], location);
				}, this)
			);
		},

		select: function (name, lat, lng) {},

		remove: function () {},

		destroy: function () {
			this.$toggle.off('click');
		},

		onToggle: _.throttle(function (e) {
			e.preventDefault();
			e.stopPropagation();
			var $el = $(e.target);
			if (!this.$dropdown.is($el) && !this.$dropdown.find($el).length) {
				this.toggle();
			}
		}, 200),

		onInput: function () {
			var query = this.$input.val().trim();
			if (query) {
				this.$result.empty().append(this.$loading);
				this._onInput(query);
			}
		},

		_onInput: _.debounce(function (query) {
			this.search(query);
		}, 200),

		onSelectItem: function (e) {
			e.preventDefault();
			e.stopPropagation();

			var $item = $(e.currentTarget),
				id = $item.data('place-id');

			$item.addClass('ps-location-selected');
			$item.siblings().removeClass('ps-location-selected');
			this.$select.show();
			this.$remove.hide();
			this.$map.children().show();

			pslocation._gmap_get_place_detail(id).done(
				$.proxy(function (place) {
					var name = place.name,
						loc = place.geometry.location;
					this.$input
						.data('tmp-location', name)
						.data('tmp-latitude', loc.lat())
						.data('tmp-longitude', loc.lng());
					pslocation._gmap_render_map(this.$map[0], place);
				}, this)
			);
		},

		onSelect: function (e) {
			e.preventDefault();
			e.stopPropagation();

			var name = this.$input.data('tmp-location'),
				latitude = this.$input.data('tmp-latitude'),
				longitude = this.$input.data('tmp-longitude');

			this.$input.data('location', name);
			this.$input.data('latitude', latitude);
			this.$input.data('longitude', longitude);
			this.$input.val(name);
			this.$select.hide();
			this.$remove.show();
			this.$map.children().show();
			this.$toggle.addClass('active');

			this._selected = {
				name: name,
				latitude: latitude,
				longitude: longitude
			};

			this.hide();
			this.postbox.doAction('refresh');
		},

		onRemove: function (e) {
			e.preventDefault();
			e.stopPropagation();

			this.$input
				.removeData('location')
				.removeData('latitude')
				.removeData('longitude')
				.val('');

			this.$select.hide();
			this.$remove.hide();
			this.$map.children().hide();
			this.$toggle.removeClass('active');

			// Remove invalid list.
			this.$result.children().each(function () {
				var $item = $(this);
				$item.data('place-id') || $item.remove();
			});

			this._selected = false;

			this.hide();
			this.postbox.doAction('refresh');
		}
	};

	return PsPostboxLocation;
});
