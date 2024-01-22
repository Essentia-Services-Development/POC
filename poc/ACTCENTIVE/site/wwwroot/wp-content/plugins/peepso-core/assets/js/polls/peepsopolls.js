import $ from 'jquery';
import peepso, { observer } from 'peepso';
import { polls as pollsData } from 'peepsodata';
import { initActivities } from './polls';

function PsPolls() {
	this.allow_multiple = null;
	this.textarea = null;
}

peepso.polls = new PsPolls();
/**
 * Initializes this instance's container and selector reference to a postbox instance.
 * Called on postbox.js _load_addons()
 */
PsPolls.prototype.init = function () {
	var that = this;

	var orig_placeholder = this.$postbox.$textarea.attr('placeholder');

	$(this.$postbox.$posttabs).on('peepso_posttabs_show-polls', function (e, tab, posttabs) {
		$('#poll-post', that.$postbox).addClass('active');
		tab.show();
		if ('undefined' !== typeof that.$postbox.$textarea) {
			that.$postbox.$textarea.attr('placeholder', pollsData.textPostboxPlaceholder);
		}
		$('.ps-postbox-status', posttabs.options.container).show();
		that.$postbox.on_change();
	});

	observer.addAction(
		'postbox_type_set',
		function ($postbox, type) {
			if ($postbox === that.$postbox) {
				if (type === 'polls') {
					$postbox.find('#poll-post.ps-postbox__menu-item').click();
				}
			}
		},
		10,
		2
	);

	this.allow_multiple = $('.allow-multiple', this.$postbox);

	this.$postbox.$posttabs.on('peepso_posttabs_submit-polls', function () {
		var filterParams = function (req) {
			return that.set_post_filter(req);
		};

		observer.addFilter('postbox_req_' + that.$postbox.guid, filterParams, 10, 1);
		that.$postbox.save_post();
		observer.removeFilter('postbox_req_' + that.$postbox.guid, filterParams, 10, 1);
	});

	this.$postbox.$posttabs.on('peepso_posttabs_cancel-polls', function () {
		$('#poll-post', that.$postbox).removeClass('active');
		that.$postbox.$textarea.attr('placeholder', orig_placeholder);
		that.on_cancel();
	});

	$('#poll-post', this.$postbox).on('click', function () {
		// Do nothing when we're on the polls tab
		if ('polls' === that.$postbox.$posttabs.current_tab().data('tab')) {
			return;
		}
		$(that.$postbox.$posttabs).find("[data-tab='polls']").trigger('click');
		//		that.$postbox.$posttabs.on_cancel();
		that.$postbox.$textarea.focus();
	});

	this.save_post = jQuery.proxy(window.activity.option_savepost, window.activity);

	window.activity.option_savepost = function (post_id) {
		that.option_savepost(post_id);
	};

	// Handle polls options value change.
	this.$postbox.on(
		'keyup',
		'.ps-poll__option input',
		$.proxy(
			_.throttle(function () {
				this.$postbox.on_change();
			}, 1000),
			this
		)
	);

	// Handle add new option.
	this.$postbox.on(
		'click',
		'#ps-add-new-option',
		$.proxy(function (e) {
			var $ct = $(e.currentTarget).closest('.ps-js-polls'),
				$clone = $ct.find('.ps-poll__option').eq(0).clone();

			$clone.find('input').val('');
			$ct.find('.ui-sortable').append($clone);
			this.reorder_placeholder($ct);
			this.$postbox.on_change();
		}, this)
	);

	// Handle delete option.
	this.$postbox.on(
		'click',
		'#ps-delete-option',
		$.proxy(function (e) {
			var $btn = $(e.currentTarget),
				$ct = $btn.closest('.ps-js-polls');

			e.preventDefault();
			if ($btn.closest('.ps-js-polls').find('.ps-poll__option').length > 2) {
				$btn.closest('.ps-poll__option').remove();
				this.reorder_placeholder($ct);
				this.$postbox.on_change();
			}
		}, this)
	);

	// Check whether postbox content is submittable.
	observer.addFilter(
		'peepso_postbox_can_submit',
		function (canSubmit) {
			var $postbox = this.$postbox,
				$posttab = $postbox.$posttabs,
				submittable = true,
				values = [],
				$options;

			if ($posttab.current_tab_id === 'polls') {
				// Do not submit on empty question.
				if ($.trim($postbox.$textarea.val()) === '') {
					submittable = false;
				} else {
					$options = $postbox.find('.ps-poll__option input');

					// Do not submit if options are less then two.
					if ($options.length < 2) {
						submittable = false;

						// Do not submit on empty question (post content).
					} else if (false) {
						submittable = false;
					} else {
						$options.each(function () {
							var val = $.trim(this.value);

							// Don't submit if there is empty value.
							if (!val) {
								submittable = false;
								return false;
							}
							// Don't submit on duplicate value.
							if (values.indexOf(val) >= 0) {
								submittable = false;
								return false;
							}

							values.push(val);
						});
					}
				}

				canSubmit.hard.push(submittable);
			}

			return canSubmit;
		},
		30,
		1,
		this
	);

	// drag n' drop functionality
	var $container = $('.ps-js-polls .ui-sortable');

	$container.sortable({
		handle: '.ps-js-handle',
		update: $.proxy(function (e, ui) {
			this.reorder_placeholder(ui.item.closest('.ps-js-polls'));
		}, this)
	});
};

/**
 * Defines the postbox this instance is running on.
 * Called on postbox.js _load_addons()
 * @param {object} postbox pspostbox
 */
PsPolls.prototype.set_postbox = function (postbox) {
	this.$postbox = postbox;
};

/**
 * Aborts preview request and hides view input fields
 */
PsPolls.prototype.on_cancel = function () {
	if (this.preview_request) {
		this.preview_request.ret.abort();
		$('.ps-js-polls .ps-postbox-input .ps-postbox-loading', this.$postbox).hide();
	}

	var input = $('.ps-poll__option input', this.$postbox);
	for (var i = 0; i <= input.length; i++) {
		if (i <= 1) {
			input.eq(i).val('');
		} else {
			input.eq(i).closest('.ps-poll__option').remove();
		}
	}
	if (this.allow_multiple.length > 0) {
		this.allow_multiple.prop('checked', false);
	}

	$('#ps-polls-input', this.$postbox).hide();
	$('.ps-js-polls .ps-postbox-preview', this.$postbox).hide().html('');
	this.$postbox.on_change();
};

/**
 * Set request url and set request type to "poll"
 * @param {object} req postbox request
 */
PsPolls.prototype.set_post_filter = function (req) {
	// options data
	req.options = [];
	var input = $('.ps-poll__option input', this.$postbox);
	for (var i = 0, value; i <= input.length; i++) {
		value = input.eq(i).val();
		if (value) {
			req.options.push(value);
		}
	}
	// allow multiple data
	req.allow_multiple = 0;
	if (this.allow_multiple.length > 0 && this.allow_multiple.is(':checked')) {
		req.allow_multiple = 1;
	}

	req.type = 'poll';
	return req;
};

/**
 * Submit vote on particular polls.
 * @param {Number} id
 * @param {HTMLElement} btn
 */
PsPolls.prototype.submit_vote = function (id, btn) {
	var $btn = $(btn),
		$loading = $btn.find('img'),
		$options = $btn.closest('.ps-js-poll-item').find('.ps-js-poll-item-option'),
		polls = [],
		$act;

	$options.filter(':checked').each(function () {
		polls.push(this.value);
	});

	$btn.attr('disabled', 'disabled');
	$loading.show();

	peepso.postJson(
		'pollsajax.submit_vote',
		{
			user_id: peepsodata.currentuserid,
			poll_id: id,
			polls: polls
		},
		$.proxy(function (json) {
			$btn.removeAttr('disabled');
			$loading.hide();

			if (json.success) {
				if (json.data && json.data.html) {
					$act = $btn.closest('.ps-js-activity');
					$act.find('.ps-stream-attachments').html(json.data.html);
					$act.find('.ps-js-poll-option-changevote').show();
					initActivities();
				}
			} else if (json.errors) {
				peepso.dialog(json.errors, { error: true }).show();
			}
		}, this)
	);
};

/**
 * Update already-selected vote.
 * @param {Number} id
 * @param {HTMLElement} btn
 */
PsPolls.prototype.change_vote = function (id, btn) {
	var $btn = $(btn),
		$act;

	peepso.postJson(
		'pollsajax.change_vote',
		{
			user_id: peepsodata.currentuserid,
			poll_id: id
		},
		$.proxy(function (json) {
			if (json.success) {
				if (json.data && json.data.html) {
					$act = $btn.closest('.ps-js-activity');
					$act.find('.ps-stream-attachments').html(json.data.html);
					$act.find('.ps-js-poll-option-changevote').show();
					initActivities();
				}
			} else if (json.errors) {
				peepso.dialog(json.errors, { error: true }).show();
			}
		}, this)
	);
};

/**
 * Unvote a pool.
 * @param {Number} id
 * @param {HTMLElement} btn
 */
PsPolls.prototype.unvote = function (id, btn) {
	var $btn = $(btn),
		$loading = $btn.find('img'),
		$act;

	$btn.attr('disabled', 'disabled');
	$loading.show();

	peepso.postJson(
		'pollsajax.unvote',
		{
			user_id: peepsodata.currentuserid,
			poll_id: id
		},
		$.proxy(function (json) {
			$btn.removeAttr('disabled');
			$loading.hide();

			if (json.success) {
				if (json.data && json.data.html) {
					$act = $btn.closest('.ps-js-activity');
					$act.find('.ps-stream-attachments').html(json.data.html);
					$act.find('.ps-js-poll-option-changevote').hide();
					initActivities();
				}
			} else if (json.errors) {
				peepso.dialog(json.errors, { error: true }).show();
			}
		}, this)
	);
};

/**
 * Re-order peepso polls placeholders.
 * @param {Element} $ct
 */
PsPolls.prototype.reorder_placeholder = function ($ct) {
	var placeholder = pollsData.textOptionPlaceholder;
	var $inputs = $ct.find('.ps-poll__option input[type=text]');

	$inputs.each(function (index) {
		$(this).attr('placeholder', placeholder.replace('%d', index + 1));
	});
};

$(function () {
	// Initialize poll for every activities on page load.
	initActivities();

	// Also initialize poll on every activities added to the stream later.
	$(document).on('ps_activitystream_loaded ps_activitystream_append', function () {
		initActivities();
	});
});

/**
 * Adds a new PsPolls object to a postbox instance.
 * @param {array} addons An array of addons to plug into the postbox.
 */
observer.addFilter(
	'peepso_postbox_addons',
	function (addons) {
		addons.push(new PsPolls());
		return addons;
	},
	10,
	1
);

// remove poll tab switch, unused for now
observer.addAction(
	'postbox_init',
	function (postbox) {
		postbox.$tabContext.find('#poll-post').remove();
	},
	10,
	1
);
