import $ from 'jquery';

const MONTH_NAMES = [
	'January',
	'February',
	'March',
	'April',
	'May',
	'June',
	'July',
	'August',
	'September',
	'October',
	'November',
	'December'
];

/**
 * Simple date selector using HTMLSelectElement.
 */
class DateSelector {
	/** @type {JQuery} */ $year;
	/** @type {JQuery} */ $month;
	/** @type {JQuery} */ $date;
	/** @type {Object} */ opts;

	/**
	 * DateSelector class constructor.
	 *
	 * @param {HTMLSelectElement} year
	 * @param {HTMLSelectElement} month
	 * @param {HTMLSelectElement} date
	 * @param {Object} [opts]
	 * @param {string[]} [opts.monthNames]
	 * @param {Date} [opts.defaultDate]
	 * @param {Date} [opts.minDate]
	 * @param {Date} [opts.maxDate]
	 * @param {Function} [opts.onSelect]
	 */
	constructor(year, month, date, opts = {}) {
		let now = new Date(),
			currentYear = now.getFullYear(),
			defaultOpts = {
				monthNames: MONTH_NAMES,
				defaultDate: new Date(now.getTime()),
				minDate: new Date(currentYear - 80, 0, 1, 1, 0, 0),
				maxDate: new Date(currentYear + 80, 11, 31, 1, 0, 0),
				onSelect: () => {}
			};

		this.opts = $.extend(defaultOpts, opts);

		this.$year = $(year).on('change', e => this.onChangeYear(e));
		this.$month = $(month).on('change', e => this.onChangeMonth(e));
		this.$date = $(date).on('change', () => this.onChangeDate());

		this.populateYear();
		this.populateMonth(+this.$year.val());
		this.populateDate(+this.$year.val(), +this.$month.val());
	}

	/**
	 * Get date.
	 *
	 * @param {boolean} humanReadable
	 * @returns {string}
	 */
	getDate(humanReadable = false) {
		let date = +this.$date.val(),
			month = +this.$month.val(),
			year = +this.$year.val();

		if (humanReadable) {
			return `${date} ${MONTH_NAMES[month - 1]} ${year}`;
		}

		return dateToStr(new Date(year, month - 1, date));
	}

	/**
	 * Set date.
	 *
	 * @param {Date|string} dateObject
	 */
	setDate(dateObject) {
		if (typeof dateObject === 'string') {
			dateObject = strToDate(dateObject);
		}

		let date = dateObject.getDate(),
			month = dateObject.getMonth() + 1,
			year = dateObject.getFullYear();

		this.populateYear(year);
		this.populateMonth(year, month);
		this.populateDate(year, month, date);
	}

	/**
	 * Populate year options.
	 *
	 * @private
	 * @param {number} [selectedYear]
	 */
	populateYear(selectedYear) {
		let { minDate, maxDate, defaultDate } = this.opts,
			fromYear = minDate.getFullYear(),
			toYear = maxDate.getFullYear(),
			html = '';

		if (!selectedYear) {
			selectedYear = defaultDate.getFullYear();
		}

		for (; fromYear <= toYear; fromYear++) {
			html += `<option value="${fromYear}"${
				fromYear === selectedYear ? ' selected="selected"' : ''
			}>${fromYear}</option>`;
		}

		this.$year.html(html);
	}

	/**
	 * Populate month options.
	 *
	 * @private
	 * @param {number} year
	 * @param {number} [selectedMonth]
	 */
	populateMonth(year, selectedMonth) {
		let { minDate, maxDate, defaultDate, monthNames } = this.opts,
			minYear = minDate.getFullYear(),
			maxYear = maxDate.getFullYear(),
			fromMonth = year > minYear ? 1 : minDate.getMonth() + 1,
			toMonth = year < maxYear ? 12 : maxDate.getMonth() + 1,
			html = '';

		if (!selectedMonth) {
			selectedMonth = +this.$month.val() || defaultDate.getMonth() + 1;
		}

		for (; fromMonth <= toMonth; fromMonth++) {
			html += `<option value="${fromMonth}"${
				fromMonth === selectedMonth ? ' selected="selected"' : ''
			}>${monthNames[fromMonth - 1]}</option>`;
		}

		this.$month.html(html);
	}

	/**
	 * Populate date options.
	 *
	 * @private
	 * @param {number} year
	 * @param {number} month
	 * @param {number} [selectedDate]
	 */
	populateDate(year, month, selectedDate) {
		let { minDate, maxDate, defaultDate } = this.opts,
			minYear = minDate.getFullYear(),
			maxYear = maxDate.getFullYear(),
			minYearMonth = minDate.getMonth() + 1,
			maxYearMonth = maxDate.getMonth() + 1,
			fromDate = year > minYear || month > minYearMonth ? 1 : minDate.getDate(),
			toDate =
				year < maxYear || month < maxYearMonth
					? getLastDayOfMonth(year, month)
					: maxDate.getDate(),
			html = '';

		if (!selectedDate) {
			selectedDate = +this.$date.val();
			if (selectedDate) {
				selectedDate = Math.min(selectedDate, toDate);
			} else {
				selectedDate = defaultDate.getDate();
			}
		}

		for (; fromDate <= toDate; fromDate++) {
			html += `<option value="${fromDate}"${
				fromDate === selectedDate ? ' selected="selected"' : ''
			}>${fromDate}</option>`;
		}

		this.$date.html(html);
	}

	/**
	 * Handle change event on year selector.
	 *
	 * @private
	 * @param {Event} e
	 */
	onChangeYear(e) {
		let selectedYear = +e.target.value;

		this.populateMonth(selectedYear);
		this.$month.triggerHandler('change');
	}

	/**
	 * Handle change event on month selector.
	 *
	 * @private
	 * @param {Event} e
	 */
	onChangeMonth(e) {
		let selectedYear = +this.$year.val(),
			selectedMonth = +e.target.value;

		this.populateDate(selectedYear, selectedMonth);
		this.$date.triggerHandler('change');
	}

	/**
	 * Handle change event on date selector.
	 *
	 * @private
	 */
	onChangeDate() {
		this.opts.onSelect(this.getDate());
	}
}

/**
 * Get the last day of a particular month and year.
 *
 * @private
 * @param {number} year
 * @param {number} month
 * @returns {number}
 */
const getLastDayOfMonth = (year, month) => {
	let date = new Date(year, month, 0);
	return date.getDate();
};

/**
 * Convert date string into a Date object.
 *
 * @private
 * @param {string} str - Date string in "yyyy-mm-dd" format.
 * @return {Date}
 */
const strToDate = str => {
	let date = str.split('-');
	return new Date(+date[0], +date[1] - 1, +date[2]);
};

/**
 * Convert Date object into a date string.
 *
 * @private
 * @param {Date} date
 * @returns {string}
 */
const dateToStr = date => {
	let year = date.getFullYear(),
		month = date.getMonth() + 1,
		day = date.getDate();

	return [year, (month < 10 ? '0' : '') + month, (day < 10 ? '0' : '') + day].join('-');
};

export default DateSelector;
