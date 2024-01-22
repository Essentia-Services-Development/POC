/**********************************************************************************
 * Digest Email
 **********************************************************************************/

(function ($) {
	var $email_digest_enable = $("input[name='email_digest_enable']");
	var hide_animation_speed = 500;

	if ($email_digest_enable.length) {
		$email_digest_enable
			.on('change', function () {
				$selector = $(this)
					.closest('.inside')
					.find('[id*="field_"]')
					.not('#field_email_digest_enable');
				if ($(this).is(':checked')) {
					$selector.fadeIn(hide_animation_speed);
				} else {
					$selector.fadeOut(hide_animation_speed);
				}
			})
			.trigger('change');

		// schedule type
		$email_digest_schedule_type = $('#email_digest_schedule_type');

		$('#email_digest_minute_daily, #email_digest_am_pm_daily').insertAfter(
			$('#email_digest_hour_daily')
		);
		$('#field_email_digest_minute_daily, #field_email_digest_am_pm_daily').remove();

		$('#email_digest_minute_monthly, #email_digest_am_pm_monthly').insertAfter(
			$('#email_digest_hour_monthly')
		);
		$('#field_email_digest_minute_monthly, #field_email_digest_am_pm_monthly').remove();

		$selector_day = $('#field_email_digest_hour_daily');
		$selector_week = $(
			'[id="field_email_digest_hour_weekly"], #field_email_digest_schedule_weekly_day'
		);
		$selector_month = $(
			'[id="field_email_digest_hour_monthly"], #field_email_digest_schedule_monthly_date'
		);

		$selector_day.hide();
		$selector_week.hide();

		$email_digest_schedule_type
			.on('change', function () {
				$selector_day.hide();
				$selector_week.hide();
				$selector_month.hide();

				if ($(this).val() == 'daily') {
					$selector_day.show();
				} else if ($(this).val() == 'weekly' || $(this).val() == 'biweekly') {
					$selector_week.show();
				} else if ($(this).val() == 'monthly') {
					$selector_month.show();
				}
			})
			.trigger('change');

		$('#email_digest_schedule_weekly_day a').on('click', function (e) {
			$('#email_digest_schedule_weekly_day a').removeClass('btn-primary');
			$(this).addClass('btn-primary');

			e.preventDefault();
		});

		$('#email_digest_minute_weekly, #email_digest_am_pm_weekly').insertAfter(
			$('#email_digest_hour_weekly')
		);
		$('#field_email_digest_minute_weekly, #field_email_digest_am_pm_weekly').remove();

		$('#email_digest_limit_post_length')
			.css('width', '100px')
			.after(
				'<label class="form-label control-label" style="margin-left:10px;">characters</label>'
			);
		$('#email_digest_send_inactive')
			.closest('.form-field')
			.find('select')
			.after(
				'<label class="form-label control-label" style="margin-left:10px;">days</label>'
			);
		$('#email_digest_per_batch').css('width', '100px');

		$('#field_email_digest_external_cron .lbl-descript').wrapInner(
			'<span style="color:red"></span>'
		);

		if ($('#email_digest_role_member').length > 0) {
			$('#email_digest_role_member')
				.closest('.form-field')
				.wrapInner('<div class="row email-digest-role"></div>');
			$('.email-digest-role').find('.ps-checkbox').wrap('<div class="col-sm-3"></div>');
			$('.email-digest-role')
				.find('.lbl-descript')
				.attr('class', 'col-sm-9')
				.css('line-height', '30px');
		}

		if ($('#email_digest_role_admin').length > 0) {
			$('#field_email_digest_role_admin')
				.find('.ps-checkbox')
				.appendTo('.email-digest-role')
				.wrap('<div class="col-sm-3"></div>');
			$('#field_email_digest_role_admin')
				.find('.lbl-descript')
				.attr('class', 'col-sm-9')
				.css('line-height', '30px')
				.appendTo('.email-digest-role');
			$('#field_email_digest_role_admin').remove();
		}
	}

	function isElementInViewport(el) {
		var rect = el.getBoundingClientRect();
		return (
			rect.top >= 0 &&
			rect.left >= 0 &&
			rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
			rect.right <= (window.innerWidth || document.documentElement.clientWidth)
		);
	}

	var getlog_$el = $('#right-sortables #field_email_digest_log');
	var getlog_$trigger = undefined;
	var getlog_page = 1;

	var getlog = function () {
		getlog_toggle_autoscroll('off');
		getlog_$trigger.find('img').show();

		peepso.ajax
			.post(`emaildigestadminajax.show_log?page=${getlog_page}`, null, -1)
			.always(() => getlog_$trigger.find('img').hide())
			.done(json => {
				if (json.success) {
					if (getlog_page === 1) {
						add_clear_logs_button();
					}
					getlog_$el.append(json.data.html);
					getlog_page++;
					getlog_toggle_autoscroll('on');
				}
			});
	};

	var getlog_toggle_autoscroll = function (method) {
		var evtName = 'scroll.ps-digest-admin',
			$win = $(window);

		if (method === 'off') {
			$win.off(evtName);
		} else if (method === 'on' && getlog_$trigger.length) {
			$win.off(evtName)
				.on(evtName, function () {
					if (isElementInViewport(getlog_$trigger[0])) {
						getlog();
					}
				})
				.trigger(evtName);
		}
	};

	if (getlog_$el.length) {
		getlog_$el.after('<div class="clearfix log-wrapper" />');
		getlog_$el = getlog_$el.next('div');
		getlog_$el.after('<div><img src="' + peepsodata.loading_gif + '" /></div>');
		getlog_$trigger = getlog_$el.next('div');
		getlog();
	}

	$(document.body).on('click', '.preview-email', function () {
		peepso.postJson(
			'emaildigestadminajax.preview_email',
			{ edc_id: $(this).attr('data-id') },
			function (json) {
				if (json.success) {
					alert(emaildigestdata.preview_message_success);
				} else {
					alert(emaildigestdata.preview_message_failed);
				}
			}
		);
	});

	function add_clear_logs_button() {
		$('#right-sortables .inside').append(
			'<p>' +
				emaildigestdata.clear_logs_description +
				'</p><a href="' +
				emaildigestdata.clear_logs_url +
				'" class="btn btn-info btn-sm">' +
				emaildigestdata.clear_logs +
				'</a>'
		);
	}
})(jQuery);
