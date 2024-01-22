(function (root, $, factory) {
	var PsTime = factory(root, $);

	/**
	 * PsTime global instance.
	 * @name ps_time
	 * @type {PsTime}
	 */
	root.ps_time = new PsTime();
})(window, jQuery, function (window, $) {
	function time() {
		return Math.floor(new Date().getTime() / 1000);
	}

	/**
	 * PsTime class.
	 * @class PsTime
	 */
	function PsTime() {
		this.ts = time();
		this.diff = window.peepsotimedata.ts - this.ts;
	}

	// port from WordPress's human_time_diff function
	PsTime.prototype.human_time_diff = function (from, to) {
		var MINUTE_IN_SECONDS = 60,
			HOUR_IN_SECONDS = MINUTE_IN_SECONDS * 60,
			DAY_IN_SECONDS = HOUR_IN_SECONDS * 24,
			WEEK_IN_SECONDS = DAY_IN_SECONDS * 7,
			YEAR_IN_SECONDS = DAY_IN_SECONDS * 365,
			data = window.peepsotimedata || {},
			diff,
			mins,
			hours,
			days,
			weeks,
			months,
			years,
			since;

		from = +from;
		to = to ? to : time();
		to = to + this.diff;
		diff = Math.abs(to - from);

		if (diff < MINUTE_IN_SECONDS) {
			since = data.now;
		} else if (diff < HOUR_IN_SECONDS) {
			mins = Math.floor(diff / MINUTE_IN_SECONDS);
			mins = mins <= 1 ? 1 : mins;
			since = data['min_' + (mins > 2 ? 3 : mins)].replace('%s', mins);
		} else if (diff < DAY_IN_SECONDS && diff >= HOUR_IN_SECONDS) {
			hours = Math.floor(diff / HOUR_IN_SECONDS);
			hours = hours <= 1 ? 1 : hours;
			since = data['hour_' + (hours > 2 ? 3 : hours)].replace('%s', hours);
		} else if (diff < WEEK_IN_SECONDS && diff >= DAY_IN_SECONDS) {
			days = Math.floor(diff / DAY_IN_SECONDS);
			days = days <= 1 ? 1 : days;
			since = data['day_' + (days > 2 ? 3 : days)].replace('%s', days);
		} else if (diff < 30 * DAY_IN_SECONDS && diff >= WEEK_IN_SECONDS) {
			weeks = Math.floor(diff / WEEK_IN_SECONDS);
			weeks = weeks <= 1 ? 1 : weeks;
			since = data['week_' + (weeks > 2 ? 3 : weeks)].replace('%s', weeks);
		} else if (diff < YEAR_IN_SECONDS && diff >= 30 * DAY_IN_SECONDS) {
			months = Math.floor(diff / (30 * DAY_IN_SECONDS));
			months = months <= 1 ? 1 : months;
			since = data['month_' + (months > 2 ? 3 : months)].replace('%s', months);
		} else if (diff >= YEAR_IN_SECONDS) {
			years = Math.floor(diff / YEAR_IN_SECONDS);
			years = years <= 1 ? 1 : years;
			since = data['year_' + (years > 2 ? 3 : years)].replace('%s', years);
		}

		return peepso.observer.applyFilters('human_time_diff', since, diff, from, to);
	};

	// Auto-update time label.
	$(function () {
		setInterval(function () {
			var now = time();
			$('.ps-js-autotime').each(function () {
				var $el = $(this),
					ts = $el.data('timestamp');
				if (ts) {
					$el.html(ps_time.human_time_diff(ts, now));
				}
			});
		}, 40 * 1000);
	});

	return PsTime;
});
