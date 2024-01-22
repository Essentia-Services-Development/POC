import $ from 'jquery';

/**
 * Simple time selector using HTMLSelectElement.
 */
class TimeSelector {
	/** @type {JQuery} */ $hour;
	/** @type {JQuery} */ $minute;
	/** @type {JQuery} */ $ampm;
	/** @type {Object} */ opts;
	/** @type {boolean} */ isAmpm;

	/**
	 * TimeSelector class constructor.
	 *
	 * @param {HTMLSelectElement} hour
	 * @param {HTMLSelectElement} minute
	 * @param {HTMLSelectElement} [ampm]
	 * @param {Object} [opts]
	 * @param {number} [opts.step]
	 * @param {Function} [opts.onSelect]
	 */
	constructor(hour, minute, ampm, opts) {
		let defaultOpts = {
			am: 'AM',
			pm: 'PM',
			step: 15,
			onSelect: () => {}
		};

		this.isAmpm = !!(ampm && ampm.nodeType);
		this.opts = $.extend(defaultOpts, (this.isAmpm ? opts : ampm) || {});

		this.$hour = $(hour).on('change', () => this.onChangeHour());
		this.$minute = $(minute).on('change', () => this.onChangeMinute());
		if (this.isAmpm) {
			this.$ampm = $(ampm).on('change', () => this.onChangeAmpm());
		}

		this.populateHour();
		this.populateMinute();
		this.populateAmpm();
	}

	/**
	 * Get time.
	 *
	 * @param {boolean} humanReadable
	 * @returns {string}
	 */
	getTime(humanReadable = false) {
		let hour = +this.$hour.val(),
			minute = +this.$minute.val();

		if (humanReadable) {
			return `${hour < 10 ? '0' : ''}${hour}:${minute < 10 ? '0' : ''}${minute}${
				this.isAmpm ? ' ' + this.$ampm.val().toUpperCase() : ''
			}`;
		}

		if (this.isAmpm) {
			hour = hour12to24(hour, this.$ampm.val());
		}

		return `${hour < 10 ? '0' : ''}${hour}:${minute < 10 ? '0' : ''}${minute}`;
	}

	/**
	 * Set time.
	 *
	 * @param {string} time - hh:mm
	 */
	setTime(time) {
		let [hour, minute] = time.split(':');

		hour = +hour;
		if (this.isAmpm) {
			hour = hour24to12(hour);
			this.$ampm.val(hour.ampm);
			this.$hour.val(hour.hour);
		} else {
			this.$hour.val(hour);
		}

		minute = Math.min(59, Math.max(0, +minute));
		minute = minute - (minute % this.opts.step);
		this.$minute.val(minute);
	}

	/**
	 * Populate hour options.
	 *
	 * @private
	 * @param {number} [selectedHour]
	 */
	populateHour(selectedHour) {
		let fromHour = this.isAmpm ? 1 : 0,
			toHour = this.isAmpm ? 12 : 23,
			html = '';

		for (; fromHour <= toHour; fromHour++) {
			html += `<option value="${fromHour}"${
				fromHour === selectedHour ? ' selected="selected"' : ''
			}>${fromHour < 10 ? '0' : ''}${fromHour}</option>`;
		}

		this.$hour.html(html);
	}

	/**
	 * Populate minute options.
	 *
	 * @private
	 * @param {number} [selectedMinute]
	 */
	populateMinute(selectedMinute) {
		let fromMinute = 0,
			toMinute = 59,
			html = '';

		for (; fromMinute <= toMinute; fromMinute += this.opts.step) {
			html += `<option value="${fromMinute}"${
				fromMinute === selectedMinute ? ' selected="selected"' : ''
			}>${fromMinute < 10 ? '0' : ''}${fromMinute}</option>`;
		}

		this.$minute.html(html);
	}

	/**
	 * Populate ampm options.
	 *
	 * @private
	 * @param {string} [selectedAmpm]
	 */
	populateAmpm(selectedAmpm) {
		if (!this.isAmpm) return;

		let opts = [
				{ value: 'AM', label: this.opts.am },
				{ value: 'PM', label: this.opts.pm }
			],
			html = '';

		for (let i = 0; i < opts.length; i++) {
			html += `<option value="${opts[i].value}"${
				opts[i].value === selectedAmpm ? ' selected="selected"' : ''
			}>${opts[i].label || opts[i].value}</option>`;
		}

		this.$ampm.html(html);
	}

	/**
	 * Handle change event on hour selector.
	 *
	 * @private
	 */
	onChangeHour() {
		this.opts.onSelect(this.getTime());
	}

	/**
	 * Handle change event on minute selector.
	 *
	 * @private
	 */
	onChangeMinute() {
		this.opts.onSelect(this.getTime());
	}

	/**
	 * Handle change event on ampm selector.
	 *
	 * @private
	 */
	onChangeAmpm() {
		this.opts.onSelect(this.getTime());
	}
}

/**
 * Convert 12-hour clock to 24-hour clock.
 *
 * @private
 * @param {number} hour
 * @param {string} ampm
 * @returns {number}
 */
const hour12to24 = (hour, ampm) => {
	if (ampm.toUpperCase() !== 'PM') {
		hour = hour === 12 ? 0 : hour;
	} else {
		hour = hour === 12 ? 12 : hour + 12;
	}

	return hour;
};

/**
 * Convert 24-hour clock to 12-hour clock.
 *
 * @private
 * @param {number} hour
 * @returns {Object}
 */
const hour24to12 = hour => {
	let ampm;

	if (hour < 12) {
		ampm = 'AM';
		hour = hour === 0 ? 12 : hour;
	} else {
		ampm = 'PM';
		hour = hour === 12 ? 12 : hour - 12;
	}

	return { hour, ampm };
};

export default TimeSelector;
