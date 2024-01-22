import $ from 'jquery';
import { observer } from 'peepso';
import { is_admin, datetime as datetimeData } from 'peepsodata';
import { DateSelector, TimeSelector } from '../datetime';

const MONTH_NAMES = datetimeData && datetimeData.text.monthNames;
const TEXT_AM = datetimeData && datetimeData.text.am;
const TEXT_PM = datetimeData && datetimeData.text.pm;

const PostboxDropdown = observer.applyFilters('class_postbox_dropdown', 10, 1);

class PostboxPin extends PostboxDropdown {
	/**
	 * Initialize postbox pinned selector dropdown.
	 *
	 * @param {JQuery} $postbox
	 */
	constructor($postbox) {
		super($postbox.find('#pin-tab')[0]);

		// Override this.$postbox value for now because of missing properties and methods required for various actions.
		// TODO: Do not override.
		this.$postbox = $postbox;

		// Only admin should be able to pin a regular post. Additionally,
		// only user with `can_pin_posts` flag should be able to pin a group post.
		this.is_admin = observer.applyFilters('postbox_can_pin', +is_admin);

		if (this.is_admin) {
			this.$container.show();
		} else {
			this.$container.hide();
		}

		this.$radio = this.$container.find('[type=radio]');
		this.$datetime = this.$container.find('.ps-js-datetime');
		this.$date = this.$container.find('.ps-js-date-dd');
		this.$month = this.$container.find('.ps-js-date-mm');
		this.$year = this.$container.find('.ps-js-date-yy');
		this.$hour = this.$container.find('.ps-js-time-hh');
		this.$minute = this.$container.find('.ps-js-time-mm');
		this.$ampm = this.$container.find('.ps-js-time-ampm');
		this.$done = this.$container.find('.ps-js-done');

		this.minuteInterval = +this.$minute.data('interval') || 15;

		this.dateSelector = null;
		this.timeSelector = null;

		this.$done.on('click', () => this.hide());

		this.$dropdown.on('click', '[data-option-value]', e => {
			let value = $(e.currentTarget).data('optionValue');

			e.stopPropagation();
			this.select(value);
			if (value === 'no' || value === 'indefinitely') {
				this.hide();
			}
		});

		this.$postbox.on('postbox.post_cancel postbox.post_saved', () => {
			this.resetDateTime();
			this.select('no');
		});

		// Filters and actions.
		observer.addFilter('postbox_req', this.filterPostboxReq.bind(this), 10, 1); // for main postbox
		observer.addFilter('postbox_data', this.filterPostboxData.bind(this), 10, 2); // for edit postbox
		observer.addAction('postbox_update', this.actionPostboxUpdate.bind(this), 10, 2); // for edit postbox
		observer.addAction('postbox_group_set', this.actionPostboxGroupSet.bind(this), 10, 3);
		observer.addAction('postbox_group_reset', this.actionPostboxGroupReset.bind(this), 10, 1);
	}

	/**
	 * Get selected date and time.
	 *
	 * @returns {number|string|undefined}
	 */
	value() {
		let value;

		if (this.$radio.get(1).checked) {
			value = 1;
		} else if (this.$radio.get(2).checked) {
			if (this.dateSelector && this.timeSelector) {
				value = `${this.dateSelector.getDate()} ${this.timeSelector.getTime()}`;
			}
		}

		return value;
	}

	/**
	 * Select the option to pin the post or publish it immediately.
	 *
	 * @param {string} value
	 */
	select(value) {
		if (value === 'no') {
			this.$radio.get(0).checked = true;
			this.$datetime.hide();
			this.$container.removeClass('active');

			// Reset tooltip.
			let $tooltip = this.$container.find('.ps-js-interaction-toggle');
			if ($tooltip.attr('data-tooltip-original')) {
				$tooltip.attr('data-tooltip', $tooltip.attr('data-tooltip-original'));
				$tooltip.removeAttr('data-tooltip-original');
			}

			observer.doAction('postbox_pin_reset', this.$postbox);
		} else if (value === 'indefinitely') {
			this.$radio.get(1).checked = true;
			this.$datetime.hide();
			this.$container.addClass('active');

			// Reset tooltip.
			let $tooltip = this.$container.find('.ps-js-interaction-toggle');
			if ($tooltip.attr('data-tooltip-original')) {
				$tooltip.attr('data-tooltip', $tooltip.attr('data-tooltip-original'));
				$tooltip.removeAttr('data-tooltip-original');
			}

			observer.doAction('postbox_pin_set', this.$postbox, this.value());
		} else if (value === 'until') {
			this.$radio.get(2).checked = true;
			this.$datetime.show();
			this.$container.addClass('active');
			this.initDateTime();

			// Update tooltip.
			var $tooltip = this.$container.find('.ps-js-interaction-toggle');
			var tooltip = `${this.timeSelector.getTime(true)}, ${this.dateSelector.getDate(true)}`;
			if (!$tooltip.attr('data-tooltip-original')) {
				$tooltip.attr('data-tooltip-original', $tooltip.attr('data-tooltip'));
			}
			$tooltip.attr('data-tooltip', tooltip);

			observer.doAction('postbox_pin_set', this.$postbox, this.value());
		}
	}

	/**
	 * Initialize date and time selectors.
	 */
	initDateTime() {
		if (this.dateSelector || this.timeSelector) {
			return;
		}

		let onSelect = () => {
			let now = new Date(),
				selectedDate,
				selectedTime,
				selectedDateTime;

			selectedDate = this.dateSelector.getDate().split('-');
			selectedTime = this.timeSelector.getTime().split(':');
			selectedDateTime = new Date(
				+selectedDate[0],
				+selectedDate[1] - 1,
				+selectedDate[2],
				+selectedTime[0],
				+selectedTime[1]
			);

			// Users should only be able to post 1 hour in the future or more.
			// It should also conform minutes with the minute interval.
			now.setHours(
				now.getHours() + 1,
				now.getMinutes() + (this.minuteInterval - (now.getMinutes() % this.minuteInterval)),
				0
			);

			if (selectedDateTime < now) {
				this.timeSelector.setTime(`${now.getHours()}:${now.getMinutes()}`);
			}
		};

		let monthNames = MONTH_NAMES,
			minDate = new Date(),
			maxDate = new Date(),
			dateOpts = { monthNames, minDate, maxDate, onSelect },
			timeOpts = { step: this.minuteInterval, am: TEXT_AM, pm: TEXT_PM, onSelect };

		// Set maximum date to year 2035.
		maxDate.setFullYear(Math.max(2035, maxDate.getFullYear() + 1));

		this.dateSelector = new DateSelector(
			this.$year[0],
			this.$month[0],
			this.$date[0],
			dateOpts
		);

		this.timeSelector = new TimeSelector(
			this.$hour[0],
			this.$minute[0],
			this.$ampm.length ? this.$ampm[0] : timeOpts,
			this.$ampm.length ? timeOpts : undefined
		);

		this.resetDateTime();
	}

	/**
	 * Reset selected date and time to the default value.
	 */
	resetDateTime() {
		let defaultDate = new Date();

		// Set default date to the next day (tomorrow).
		defaultDate.setDate(defaultDate.getDate() + 1);

		this.setDateTime(defaultDate);
	}

	/**
	 * Set the selected date and time to a provided value.
	 *
	 * @param {Date} date
	 */
	setDateTime(date) {
		this.initDateTime();

		let dateString = `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}`,
			timeString = `${date.getHours()}:${date.getMinutes()}`;

		this.dateSelector.setDate(dateString);
		this.timeSelector.setTime(timeString);
	}

	/**
	 * Filter hook for "postbox_req".
	 *
	 * @param {Object} params
	 * @returns {Object}
	 */
	filterPostboxReq(params) {
		let value = this.value();
		if (value) {
			params.pin = 'string' === typeof value ? value : 1;
		}

		return params;
	}

	/**
	 * Filter hook for "postbox_data".
	 *
	 * @param {Object} params
	 * @param {Object} postbox
	 * @returns {Object}
	 */
	filterPostboxData(params, postbox) {
		if (postbox.$el === this.$postbox) {
			let value = this.value();
			if (value) {
				params.pin = 'string' === typeof value ? value : 1;
			}
		}

		return params;
	}

	/**
	 * Action hook for "postbox_update".
	 *
	 * @param {Object} postbox
	 * @param {Object} data
	 */
	actionPostboxUpdate(postbox, data) {
		if (postbox.$el === this.$postbox) {
			if (data.pin) {
				if ('string' === typeof data.pin) {
					let [datePart, timePart] = data.pin.split(' '),
						date;

					datePart = datePart.split('-');
					timePart = timePart.split(':');
					date = new Date(
						+datePart[0],
						+datePart[1] - 1,
						+datePart[2],
						+timePart[0],
						+timePart[1]
					);

					this.select('until');
					this.setDateTime(date);
				} else {
					this.select('indefinitely');
					this.resetDateTime();
					this.$container.hide();
				}
			} else {
				this.select('no');
				this.resetDateTime();
				this.$container.hide();
			}
		}
	}

	/**
	 * Action hook for "postbox_group_set".
	 *
	 * @param {JQuery} $postbox
	 */
	actionPostboxGroupSet($postbox, groupId, data) {
		if ($postbox === this.$postbox) {
			// Only group admin should be able to pin a group post.
			if (+data.can_pin_posts) {
				this.$container.show();
			} else {
				this.select('no');
				this.$container.hide();
			}
		}
	}

	/**
	 * Action hook for "postbox_group_reset".
	 *
	 * @param {JQuery} $postbox
	 */
	actionPostboxGroupReset($postbox) {
		if ($postbox === this.$postbox) {
			// Only admin should be able to pin a regular (non-group) post.
			if (this.is_admin) {
				this.$container.show();
			} else {
				this.select('no');
				this.$container.hide();
			}
		}
	}
}

// Initialize class on main postbox initialization.
observer.addAction(
	'peepso_postbox_addons',
	addons => {
		let wrapper = {
			init() {},
			set_postbox($postbox) {
				if ($postbox.find('#pin-tab').length) {
					new PostboxPin($postbox);
				}
			}
		};
		addons.push(wrapper);
		return addons;
	},
	10,
	1
);

// Initialize class on edit postbox initialization.
observer.addAction(
	'postbox_init',
	postbox => {
		let $postbox = postbox.$el;
		if ($postbox.find('#pin-tab').length) {
			new PostboxPin($postbox);
		}
	},
	10,
	1
);
