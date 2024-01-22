import { ajax, dialog, hooks } from 'peepso';
import { currentuserid as LOGIN_USER_ID, user as userData } from 'peepsodata';

const TEMPLATE_REPORT = userData && userData.template_report;

/** User class. */
class User {
	/**
	 * Create a User instance.
	 *
	 * @param {number} id
	 */
	constructor(id) {
		this.id = id;
	}

	/**
	 * Like a user.
	 * @todo Implement this function.
	 *
	 * @returns {JQueryDeferred}
	 */
	like() {}

	/**
	 * Block user.
	 *
	 * @returns {JQueryDeferred}
	 */
	block() {
		let params = {
			uid: LOGIN_USER_ID,
			user_id: this.id
		};

		return ajax.post('activity.blockuser', params).then(json => {
			if (json.success) {
				hooks.doAction('user_blocked', userId);
			}
		});
	}

	/**
	 * Unblock user.
	 *
	 * @returns {JQueryDeferred}
	 */
	unblock() {
		let params = {
			uid: LOGIN_USER_ID,
			user_id: this.id
		};

		return ajax.post('activity.unblockuser', params).then(json => {
			if (json.success) {
				hooks.doAction('user_unblocked', this.id);
			}
		});
	}

	/**
	 * Show dialog to ban user.
	 * @todo Implement this function.
	 *
	 * @returns {boolean}
	 */
	doBan() {
		return false;
	}

	/**
	 * Ban user.
	 *
	 * @param {string} [date]
	 * @returns {JQueryDeferred}
	 */
	ban(date) {
		let params = {
			user_id: this.id,
			ban_status: 1,
			ban_type: date ? 'ban_period' : 'ban_forever',
			ban_period_date: date ? date : undefined
		};

		return ajax.post('activity.set_ban_status', params).then(json => {
			if (json.success) {
				hooks.doAction('user_banned', this.id);
			}
		});
	}

	/**
	 * Unban user.
	 *
	 * @returns {JQueryDeferred}
	 */
	unban() {
		let params = {
			user_id: this.id,
			ban_status: 0
		};

		return ajax.post('activity.set_ban_status', params).then(json => {
			if (json.success) {
				hooks.doAction('user_unbanned', this.id);
			}
		});
	}

	/**
	 * Show dialog to report a user.
	 *
	 * @returns {boolean}
	 */
	doReport() {
		let popup = dialog(TEMPLATE_REPORT).show();

		popup.$el.on('click', '.ps-js-cancel', () => popup.hide());
		popup.$el.on('click', '.ps-js-submit', () => {
			let $reason = popup.$el.find('.ps-js-report-type option:selected'),
				$description = popup.$el.find('.ps-js-report-desc textarea'),
				$error = popup.$el.find('.ps-js-report-error'),
				reason = $reason.val(),
				description = $description.val().trim();

			if (!reason) {
				$error.html(popup.opts.text_select_reason).show();
			} else if ($reason.data('need-reason') && !description) {
				$error.html(popup.opts.text_fill_description).show();
			} else {
				$error.hide();
				this.report(reason, description).then(function(json) {
					let title = popup.title();
					popup.hide();

					if (json.notices) {
						dialog(json.notices, { title })
							.show()
							.autohide();
					}
				});
			}
		});
	}

	/**
	 * Report a user.
	 *
	 * @param {string} reason
	 * @param {string} [description]
	 * @returns {JQueryDeferred}
	 */
	report(reason, description) {
		let params = {
			user_id: this.id,
			reason: reason,
			reason_desc: description
		};

		return ajax.post('profile.report', params).done(json => {
			if (json.success) {
				hooks.doAction('user_reported', this.id);
			}
		});
	}
}

export default function user(...args) {
	return new User(...args);
}
