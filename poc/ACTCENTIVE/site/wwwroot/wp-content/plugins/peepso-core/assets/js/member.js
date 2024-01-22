(function (root, $, factory) {
	var PsMember = factory(root, $);
	ps_member = new PsMember();
})(window, jQuery, function (window, $) {
	/**
	 * User managements.
	 * @class PsMember
	 */
	function PsMember() {}

	/**
	 * Block specific user.
	 * @param {number} userId User ID to be blocked.
	 * @param {HTMLElement} [elem] Block button.
	 */
	PsMember.prototype.block_user = function (userId, elem) {
		var func = this.block_user,
			blockId = 'block_' + userId,
			params;

		if (func[blockId]) {
			return;
		}

		if (elem) {
			elem = $(elem);
			elem.find('img').css('display', 'inline');
		}

		params = {
			uid: peepsodata.currentuserid,
			user_id: userId
		};

		func[blockId] = true;
		peepso.postJson(
			'activity.blockuser',
			params,
			$.proxy(function (json) {
				var $focus = $('.ps-js-focus--' + userId),
					$actions = $focus.find('.ps-js-focus-actions'),
					data = json.data;

				func[blockId] = false;
				if (json.success) {
					if (data && data.actions) {
						$actions.html(data.actions);
					}

					peepso.observer.applyFilters('ps_member_user_blocked', userId, data);

					// Show message if provided.
					if (data && (data.header || data.message)) {
						psmessage.show(data.header, data.message, psmessage.fade_time);
					}

					// Redirect if provided.
					if (data.redirect) {
						setTimeout(function () {
							window.location = data.redirect;
						}, Math.min(1000, psmessage.fade_time));
					}
				} else if (json.errors) {
					alert(json.errors[0]);
				}
			}, this)
		);
	};

	/**
	 * Unblock specific user.
	 * @param {number} userId User ID to be unblocked.
	 * @param {HTMLElement} [elem] Unblock button.
	 */
	PsMember.prototype.unblock_user = function (userId, elem) {
		var func = this.unblock_user,
			unblockId = 'block_' + userId,
			params;

		if (func[unblockId]) {
			return;
		}

		if (elem) {
			elem = $(elem);
			elem.find('img').css('display', 'inline');
		}

		params = {
			uid: peepsodata.currentuserid,
			user_id: userId
		};

		func[unblockId] = true;
		peepso.postJson(
			'activity.unblockuser',
			params,
			$.proxy(function (json) {
				var $focus = $('.ps-js-focus--' + userId),
					$actions = $focus.find('.ps-js-focus-actions'),
					data = json.data;

				func[unblockId] = false;
				if (json.success) {
					if (data && data.actions) {
						$actions.html(data.actions);
					}

					peepso.observer.applyFilters('ps_member_user_unblocked', userId, data);

					// Show message if provided.
					if (data && (data.header || data.message)) {
						psmessage.show(data.header, data.message, psmessage.fade_time);
					}

					// Remove item.
					$(elem)
						.closest('.ps-js-member')
						.fadeOut('fast', function () {
							$(this).remove();
						});
				}
			}, this)
		);
	};

	/**
	 * Ban specific user.
	 * @param {number} user_id User ID to be banned.
	 * @param {HTMLElement=} elem Ban button.
	 */
	PsMember.prototype.ban_user = function (user_id, elem) {
		if (this.banning_user) {
			return;
		}

		if (elem) {
			elem = $(elem);
			elem.find('img').css('display', 'inline');
		}

		var title = peepsomemberdata.ban_popup_title;
		var content = peepsomemberdata.ban_popup_content;
		var actions = [
			'<button type="button" class="ps-btn ps-btn-small ps-button-cancel" onclick="return pswindow.do_no_confirm();">',
			peepsomemberdata.ban_popup_cancel,
			'</button>',
			'<button type="button" class="ps-btn ps-btn-small ps-button-action" onclick="return ps_member.do_ban_user(' +
				user_id +
				');">',
			peepsomemberdata.ban_popup_save,
			'</button>'
		].join(' ');

		var popup = pswindow.show(title, content).set_actions(actions);
		var $ct = popup.$container;

		$ct.find('#ban-forever').on('focus', function () {
			$('#ban-period-empty').hide();
		});

		// init datepicker
		ps_datepicker.init($ct.find('[name=ban_period_date]'));
	};

	/**
	 * Confirm to Ban specific user.
	 * @param {number} user_id User ID to be banned.
	 * @param {HTMLElement=} elem Ban button.
	 */
	PsMember.prototype.do_ban_user = function (user_id) {
		var $form = $('#form_ban_user'),
			ban_type = $form.find('input[name=ban_type]:checked').val(),
			ban_period_date;

		if (ban_type === 'ban_period') {
			ban_period_date = $form.find('input[name=ban_period_date]').data('value');
			if (!ban_period_date) {
				$form.find('#ban-period-empty').show();
				return false;
			}
		}

		var req = {
			user_id: user_id,
			ban_status: 1,
			ban_type: ban_type,
			ban_period_date: ban_period_date
		};

		this.banning_user = true;
		peepso.postJson(
			'activity.set_ban_status',
			req,
			$.proxy(function (json) {
				this.banning_user = false;
				if (json.success) {
					peepso.observer.applyFilters('ps_member_user_banned', user_id, json.data);
					psmessage.show(json.data.header, json.data.message, psmessage.fade_time);
					setTimeout(function () {
						window.location.reload();
					}, Math.min(1000, psmessage.fade_time));
				}
			}, this)
		);
	};

	/**
	 * Unban specific user.
	 * @param {number} user_id User ID to be unbanned.
	 * @param {HTMLElement=} elem Unban button.
	 */
	PsMember.prototype.unban_user = function (user_id, elem) {
		if (this.unbanning_user) {
			return;
		}

		if (elem) {
			elem = $(elem);
			elem.find('img').css('display', 'inline');
		}

		this.unbanning_user = true;
		peepso.postJson(
			'activity.set_ban_status',
			{ user_id: user_id, ban_status: 0 },
			$.proxy(function (json) {
				this.unbanning_user = false;
				if (json.success) {
					peepso.observer.applyFilters('ps_member_user_unbanned', user_id, json.data);
					psmessage.show(json.data.header, json.data.message, psmessage.fade_time);
					setTimeout(function () {
						window.location.reload();
					}, Math.min(1000, psmessage.fade_time));
				}
			}, this)
		);
	};

	/**
	 * Report specific user.
	 * @param {number} user_id User ID to be reported.
	 */
	PsMember.prototype.report_user = function (user_id) {
		var title = $('#activity-report-title').html(),
			content = $('#activity-report-content').html(),
			actions = $('#activity-report-actions').html();

		content = content.replace('{post-content}', 'Profile');
		content = content.replace('{post-id}', user_id + '');

		function filterAction() {
			return 'profile.report';
		}

		function filterParams(params) {
			var newParams = $.extend({}, params);
			newParams.user_id = newParams.act_id;
			newParams.act_id = undefined;
			newParams.uid = undefined;
			return newParams;
		}

		peepso.observer.addFilter('activity_report_action', filterAction, 10);
		peepso.observer.addFilter('activity_report_params', filterParams, 10, 1);

		pswindow.show(title, content).set_actions(actions).refresh();

		$('#ps-window').one('pswindow.hidden', function () {
			peepso.observer.removeFilter('activity_report_action', filterAction, 10);
			peepso.observer.removeFilter('activity_report_params', filterParams, 10);
		});

		$(window).one('report.submitted', function (e, response) {
			pswindow.show('Profile Reported', response.notices[0]);
		});
	};

	return PsMember;
});
