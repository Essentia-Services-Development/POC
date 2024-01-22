(function ($, factory) {
	var PsMoods = factory($);

	peepso.observer.addAction(
		'postbox_init',
		function (postbox) {
			var inst = new PsMoods(postbox);
		},
		10,
		1
	);

	function capitalize(str) {
		str = str.split(' ');
		for (var i = 0; i < str.length; i++) {
			str[i] = str[i][0].toUpperCase() + str[i].slice(1);
		}
		return str.join(' ');
	}

	peepso.observer.addFilter(
		'human_friendly_extras',
		function (extras, content, root) {
			if (!content && root) {
				var $mood = $(root).find('.ps-js-activity-extras .ps-post__mood');
				if ($mood.length) {
					var mood = capitalize($mood.text().trim());
					extras.push(mood);
				}
			}
			return extras;
		},
		10,
		3
	);
})(jQuery, function ($) {
	var LABEL = $('#mood-text-string').text();

	// mood list
	var MOODS = false;

	var evtSuffix = '.ps-postbox-moods';

	/**
	 * Postbox location addon.
	 */
	function PsPostboxMood() {
		this.__constructor.apply(this, arguments);
	}

	PsPostboxMood.prototype = {
		/**
		 * Initialize postbox with add mood functionality.
		 * @param {PsPostbox} postbox Postbox instance in which mood functionality will be attached to.
		 */
		__constructor: function (postbox) {
			this.postbox = postbox;

			// element caches
			this.$container = postbox.$el.find('#mood-tab');
			this.$toggle = this.$container.find('.ps-js-postbox-toggle');
			this.$dropdown = this.$container.find('.ps-js-postbox-mood');
			this.$remove = this.$dropdown.find('button');

			// event handler
			this.$toggle.on('click' + evtSuffix, $.proxy(this.toggle, this));
			this.$dropdown.on('click' + evtSuffix, '.ps-js-mood-item', $.proxy(this.select, this));
			this.$remove.on('click' + evtSuffix, $.proxy(this.remove, this));

			this._mapMoods();

			// filters and actions
			postbox.addAction('update', this.update, 10, 2, this);
			postbox.addFilter('render_addons', this.render, 10, 1, this);
			postbox.addFilter('data', this.filterData, 10, 1, this);
			postbox.addFilter('data_validate', this.validate, 10, 2, this);
		},

		/**
		 * Show mood dropdown.
		 */
		show: function () {
			this.$dropdown.show();
			this.$container.addClass('ps-postbox__menu-item--open');

			// Add autohide on document-click.
			setTimeout(() => {
				$(document)
					.off('mouseup.ps-postbox-moods')
					.on('mouseup.ps-postbox-moods', e => {
						if (this.$container.has(e.target).length === 0) {
							this.hide();
						}
					});
			}, 1);
		},

		/**
		 * Hide mood dropdown.
		 */
		hide: function () {
			this.$dropdown.hide();
			this.$container.removeClass('ps-postbox__menu-item--open');
			$(document).off('mouseup.ps-postbox-moods');
		},

		/**
		 * Toggle mood dropdown.
		 */
		toggle: function () {
			if (this.$dropdown.is(':visible')) {
				this.hide();
			} else {
				this.show();
			}
		},

		/**
		 * Attach selected mood into post data.
		 * @param {object} data
		 */
		filterData: function (data) {
			if (this._selected) {
				data.mood = this._selected.value;
			} else {
				data.mood = '';
			}
			return data;
		},

		validate: function (valid, data) {
			if (this._selected) {
				return true;
			}
			return valid;
		},

		/**
		 * Select a mood.
		 */
		select: function (e) {
			var $a = $(e.currentTarget);

			if ($a.length) {
				this._selected = {
					value: $a.data('option-value'),
					label: $a.data('option-display-value'),
					className: $a.find('i').attr('class')
				};
				this.$toggle.addClass('active');
				this.$remove.show();
				this.postbox.doAction('refresh');
				this.hide();
			}
		},

		/**
		 * Removes selected mood.
		 */
		remove: function () {
			this._selected = false;
			this.$toggle.removeClass('active');
			this.$remove.hide();
			this.postbox.doAction('refresh');
			this.hide();
		},

		/**
		 * Update selected mood.
		 */
		update: function (data) {
			data = (data && data.data) || {};
			if (data.mood) {
				this._selected = {
					value: data.mood,
					label: MOODS[data.mood].label,
					className: MOODS[data.mood].icon
				};
				this.$toggle.addClass('active');
				this.$remove.show();
			} else {
				this._selected = false;
				this.$toggle.removeClass('active');
				this.$remove.hide();
			}
			this.postbox.doAction('refresh');
		},

		/**
		 * Render selected mood.
		 */
		render: function (list) {
			var html;
			if (this._selected) {
				html = '<i class="' + this._selected.className + '"></i>';
				html += '<b> ' + LABEL + this._selected.label + '</b>';
				list.push(html);
			}
			return list;
		},

		/**
		 *
		 */
		_mapMoods: function () {
			var $moods;

			if (!MOODS) {
				$moods = this.$dropdown.find('a.ps-js-mood-item');
				$moods.each(function () {
					var $a = $(this),
						$i = $a.find('i'),
						id = $a.data('option-value'),
						label = $a.data('option-display-value'),
						icon = $i.attr('class');

					MOODS || (MOODS = {});
					MOODS[id] = {
						icon: icon,
						label: label
					};
				});
			}
		}
	};

	return PsPostboxMood;
});

////////////////////////////////////////////////////////////////////////////////////////////////////
// PsMoods (legacy)
////////////////////////////////////////////////////////////////////////////////////////////////////

(function ($, peepso, factory) {
	factory($, peepso);
})(jQuery || $, peepso, function ($, peepso) {
	/**
	 * Javascript code to handle mood events
	 */
	function PsMoods() {
		this.$postbox = null;
		this.$container = null;
		this.$mood = null;
		this.$mood_remove = null;
		this.$mood_dropdown_toggle = null;
		this.mood_selected = false;
		this.can_submit = false;
	}

	/**
	 * Defines the postbox this instance is running on.
	 * Called on postbox.js _load_addons()
	 * @param {object} postbox This refers to the parent postbox object which this plugin may inherit, override, and manipulate its input boxes and behavior
	 */
	PsMoods.prototype.set_postbox = function (postbox) {
		this.$postbox = postbox;
	};

	/**
	 * Initializes this instance's container and selector reference to a postbox instance.
	 * Called on postbox.js _load_addons()
	 */
	PsMoods.prototype.init = function () {
		if (_.isUndefined(this.$postbox)) {
			return;
		}

		var _self = this;

		peepso.observer.addFilter(
			'peepso_postbox_can_submit',
			function (can_submit) {
				can_submit.soft.push(_self.can_submit);
				return can_submit;
			},
			20,
			1
		);

		this.$container = this.$postbox.find('#mood-tab');

		this.$mood = jQuery('#postbox-mood', this.$postbox);
		this.$mood_dropdown_toggle = jQuery(
			'#mood-tab .ps-js-interaction-wrapper > a',
			this.$postbox
		);
		this.$mood_remove = jQuery('#postbox-mood-remove', this.$postbox);

		// Add click event on all mood links
		this.$mood.on('click', 'a.ps-js-mood-item', function (e) {
			_self.select_mood(e);
		});

		// Add click event on remove mood
		this.$mood_remove.on('click', function () {
			_self.remove_mood();
		});

		this.$mood_dropdown_toggle.on('click', function () {
			if (_self.$mood.is(':visible')) {
				_self.$mood.hide();
				_self.$container.removeClass('ps-postbox__menu-item--open');
				jQuery(document).off('mouseup.ps-postbox-moods');
				return;
			}

			_self.$mood.show();
			_self.$container.addClass('ps-postbox__menu-item--open');

			// Add autohide on document-click.
			setTimeout(function () {
				jQuery(document)
					.off('mouseup.ps-postbox-moods')
					.on('mouseup.ps-postbox-moods', function (e) {
						if (_self.$container.has(e.target).length === 0) {
							_self.$mood.hide();
							_self.$container.removeClass('ps-postbox__menu-item--open');
							jQuery(document).off('mouseup.ps-postbox-moods');
						}
					});
			}, 1);
		});

		this.$mood_dropdown_toggle.on('peepso.interaction-hide', function () {
			_self.$mood.hide();
			_self.$container.removeClass('ps-postbox__menu-item--open');
		});

		// close the moods popup when done with the post
		this.$postbox.on('postbox.post_cancel postbox.post_saved', function () {
			_self.remove_mood();
		});

		// This handles adding the selected mood to the postbox_req variable before submitting to server
		peepso.observer.addFilter(
			'postbox_req_' + this.$postbox.guid,
			function (req) {
				return _self.set_mood(req);
			},
			10,
			1
		);

		peepso.observer.addFilter(
			'peepso_postbox_addons_update',
			function (list) {
				var mood, html;
				if (_self.mood_selected) {
					mood = _self.mood_selected;
					html = '<i class="ps-emoticon ' + mood[0] + '"></i> <b>' + mood[1] + '</b>';
					list.push(html);
				}

				return list;
			},
			10,
			1
		);
	};

	/**
	 * Sets #postbox-mood when user clicks a mood icon
	 * @param {object} e Click event
	 */
	PsMoods.prototype.select_mood = function (e) {
		var a = jQuery(e.target).closest('a');
		var btn = jQuery('#mood-tab', this.$postbox);
		var input = jQuery('#postbox-mood-input', this.$postbox);
		var placeHolder = btn.find('a');
		var menu = a.closest('#postbox-mood');
		var $postboxcontainer = this.$postbox.$textarea.parent();

		var icon = a.find('i').attr('class');
		var label = jQuery('#mood-text-string').text() + a.attr('data-option-display-value');

		input.val(a.attr('data-option-value'));
		this.$mood_remove.show();
		menu.hide();

		this.$container.addClass('active');
		this.$container.removeClass('ps-postbox__menu-item--open');

		// Update tooltip.
		var $tooltip = this.$container.find('.ps-js-interaction-toggle');
		var tooltip = a.attr('data-option-display-value');
		tooltip = tooltip.charAt(0).toUpperCase() + tooltip.slice(1);
		if (!$tooltip.attr('data-tooltip-original')) {
			$tooltip.attr('data-tooltip-original', $tooltip.attr('data-tooltip'));
		}
		$tooltip.attr('data-tooltip', tooltip);

		this.mood_selected = [icon, label];
		this.can_submit = true;
		this.$postbox.on_change();
	};

	/**
	 * Clear #postbox-mood-input when user clicks remove mood button
	 */
	PsMoods.prototype.remove_mood = function () {
		jQuery('span#postmood', this.$postbox.$textarea.parent()).remove();
		jQuery('#postbox-mood-input', this.$postbox).val('');

		this.$container.removeClass('active');
		this.$container.removeClass('ps-postbox__menu-item--open');

		// Reset tooltip.
		var $tooltip = this.$container.find('.ps-js-interaction-toggle');
		if ($tooltip.attr('data-tooltip-original')) {
			$tooltip.attr('data-tooltip', $tooltip.attr('data-tooltip-original'));
			$tooltip.removeAttr('data-tooltip-original');
		}

		this.$mood_remove.hide();
		this.$mood.hide();
		this.mood_selected = false;
		this.can_submit = false;
		this.$postbox.on_change();
	};

	/**
	 * Adds the selected mood to the postbox_req variable
	 * @param {object} req postbox request
	 * @return {object} req Returns modified request with mood value
	 */
	PsMoods.prototype.set_mood = function (req) {
		if ('undefined' === typeof req.mood) req.mood = '';

		req.mood = jQuery('#postbox-mood-input', this.$postbox).val();
		return req;
	};

	/**
	 * Adds a new PsMoods object to the PostBox instance.
	 * @param {array} addons An array of addons that are being pluged in to the PostBox.
	 */
	peepso.observer.addFilter(
		'peepso_postbox_addons',
		function (addons) {
			addons.push(new PsMoods());
			return addons;
		},
		10,
		1
	);
});
