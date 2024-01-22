import { hooks } from 'peepso';
/*
 * Handlers for user profile page
 * @package PeepSo
 * @author PeepSo
 */

//peepso.log("profile.js");

// declare class
function PsProfile() {
	this.cover = {};
	this.cover.x_position_percent = 0;
	this.cover.y_position_percent = 0;

	this.$cover_ct = jQuery('.js-focus-cover');
	this.$cover_image = jQuery('img#' + peepsodata.userid);
	this.initial_cover_position = this.$cover_image.attr('style');

	this.avatar_use_gravatar = false;
}

var profile = (window.profile = new PsProfile());

/**
 * Initializes this instance's container and selector reference to a postbox instance.
 */
PsProfile.prototype.init = function () {
	// initialize the "About Me" collapsible area
	var coll = jQuery('.js-collapse-about-btn');
	if (0 !== coll.length) {
		coll.on('click', function (e) {
			e.preventDefault();
			var about = jQuery('.js-collapse-about');
			var disp = about.css('display');
			if ('none' === disp) {
				about.show();
			} else {
				about.hide();
			}
		});
	}

	jQuery('.js-focus-cover').hover(
		function () {
			if (false === pswindow.is_visible) jQuery('.js-focus-change-cover').show();
		},
		function () {
			jQuery('.js-focus-change-cover').hide();
		}
	);

	// removed the jquery event handlers in favor of onclick= attributes
	//	jQuery(".ps-tab__bar a").click(function(e) {
	//		e.preventDefault();
	//		jQuery(this).tab("show");
	//	});
	// remove Divi event handlers from the activity/about me tabs
	jQuery('.ps-tab__bar').unbind('click');

	var $pref = jQuery('.ps-js-page-about-preferences');
	if ($pref.length) {
		$pref.find('input[type=checkbox]').on('click.savepref', profile.save_preference);
		$pref.find('select').on('change.savepref', profile.save_preference);
		$pref.find('.ps-js-dropdown input[type=hidden]').on('change', profile.save_preference);
		$pref
			.find('.ps-preferences__checkbox')
			.find('input[type=checkbox]')
			.off('click.savepref');
		// .on('click.savepref', profile.save_notification);
	}

	var $items = jQuery('.ps-js-profile-item');
	if ($items.length) {
		$items.each(function () {
			var $item = jQuery(this);
			if ($item.find('.peepso-markdown').length) {
				var html = peepso.observer.applyFilters('peepso_parse_content', $item.html());
				$item.html(html);
			}
		});
	}
};

/**
 * event callback for switching tabs between Activity Stream and About Me
 * @param Event e Current event
 * @param string name Name of tab to activate
 * @param string hide Name of tab to hide
 * @returns Boolean To prevent continuing execution
 */
PsProfile.prototype.activate_tab = function (e, name, hide) {
	e.preventDefault();

	jQuery(e.target)
		.addClass('active')
		.siblings('[data-toggle=tab]')
		.removeClass('active');

	jQuery(hide).hide();
	jQuery(name).show();
	return false;
};

/**
 * Likes a profile
 * @return {boolean} Always returns FALSE
 */
PsProfile.prototype.new_like = function () {
	peepso.postJson('profile.like', { user_id: peepsodata.userid }, function (json) {
		var data, html, likeCount;
		if (json.success) {
			data = json.data || {};
			html = data.html;
			likeCount = data.like_count;
			jQuery('.ps-js-focus-interactions').html(html);
			if (typeof likeCount !== 'undefined') {
				peepso.observer.doAction('profile_update_like', peepsodata.userid, likeCount);
			}
		} else {
			psmessage.show('', json.errors[0]).fade_out(psmessage.fade_time);
		}
	});

	return false;
};

/**
 * Performs unblock user operation
 */
PsProfile.prototype.unblock_user = function (user_id, elem) {
	if (this.unblocking_user) {
		return;
	}

	if (elem) {
		elem = jQuery(elem);
		elem.find('img').css('display', 'inline');
	}

	var req = { uid: peepsodata.currentuserid, user_id: user_id };

	this.unblocking_user = true;
	peepso.postJson(
		'activity.unblockuser',
		req,
		jQuery.proxy(function (json) {
			this.unblocking_user = false;
			if (json.success) {
				jQuery('.ps-js-focus--' + user_id)
					.find('.ps-js-focus-actions')
					.html(json.data.actions);
				psmessage.show(json.data.header, json.data.message, psmessage.fade_time);
			}
		}, this)
	);
};

/**
 * Deletes profile operation
 */
PsProfile.prototype.delete_profile = function () {
	var title = jQuery('#profile-delete-title').html();
	var content = jQuery('#profile-delete-content').html();

	pswindow.show(title, content);
};

/**
 * Performs the delete operation
 */
PsProfile.prototype.delete_profile_action = function () {
	$req = {};
	var req = { uid: peepsodata.currentuserid };
	peepso.postJson('profile.delete_profile', req, function (json) {
		if (json.success) {
			window.location = json.data.url;
		} else psmessage.show('', json.errors[0]).fade_out(psmessage.fade_time);
	});
};

PsProfile.prototype.field_changed = function (elem, evt) {
	var id = jQuery(elem).data('id');

	this.field_changed_list || (this.field_changed_list = []);
	if (this.field_changed_list.indexOf(id) === -1) {
		this.field_changed_list.push(id);
	}

	this.change_beforeunload();
	return true;
};

PsProfile.prototype.change_beforeunload = function () {
	if (!this.onbeforeunload_changed) {
		var that = this;
		this.onbeforeunload_changed = window.onbeforeunload || function () { };
		window.onbeforeunload = function () {
			if (that.save_field_saving) {
				return (
					peepsodata.profile_saving_notice ||
					'The system is currently saving your changes.'
				);
			}
			if (that.field_changed_list.length) {
				return (
					peepsodata.profile_unsaved_notice || 'There are unsaved changes on this page.'
				);
			}
		};
	}
};

PsProfile.prototype.change_privacy = function (elem) {
	var $elem = jQuery(elem),
		$ct = $elem.closest('.ps-js-dropdown'),
		$button = $ct.find('.ps-js-dropdown-toggle'),
		$hidden = $ct.find('input[type=hidden]'),
		icons = {},
		iconSelector = '[class*=gci-]',
		labelSelector = '.ps-privacy-title',
		id = $hidden.data('id'),
		oldVal = $hidden.val(),
		oldIcon = $button.find(iconSelector).attr('class'),
		oldLabel = $button.find(labelSelector).html(),
		newVal = $elem.data('optionValue'),
		newIcon = $elem.find(iconSelector).attr('class'),
		newLabel = $elem.find('span').html();

	// Map icons.
	$elem
		.parent()
		.children('[data-option-value]')
		.each(function () {
			var $a = jQuery(this),
				val = $a.data('optionValue'),
				icon = $a.find(iconSelector).attr('class');

			icons[val] = icon;
		});

	// Update icon immediately, but revert on failed update.
	$button.find(iconSelector).attr('class', newIcon);
	$button.find(labelSelector).html(newLabel);

	// Post update.
	peepso.postJson(
		'profilefieldsajax.save_acc',
		{
			user_id: peepsodata.currentuserid,
			view_user_id: peepsodata.userid,
			id: id,
			acc: newVal
		},
		function (json) {
			if (json.success) {
				$hidden.val(newVal);
			} else {
				$button.find(iconSelector).attr('class', oldIcon);
				$button.find(labelSelector).html(oldLabel);
			}
		}
	);
};

PsProfile.prototype.use_gravatar = function () {
	var that = this;
	peepso.postJson('profile.use_gravatar', {}, function (response) {
		if (response.success) {
			var content_html = jQuery('#dialog-upload-avatar-content', jQuery(response.data.html));
			var actions = jQuery('#dialog-upload-avatar .dialog-action').html();
			var rand = '?' + Math.random();
			var image_url = response.data.image_url;

			image_url =
				image_url + (image_url.indexOf('?') >= 0 ? '&rand=' : '?rand=') + Math.random();

			jQuery('.js-focus-avatar img', content_html).attr('src', image_url);
			jQuery('.imagePreview img', content_html).attr('src', image_url);
			jQuery('.imagePreview', content_html).after(
				'<input type="hidden" name="is_tmp" value="1"/>'
			);
			jQuery('.ps-js-has-avatar', content_html).show();
			jQuery('.ps-js-no-avatar', content_html).hide();
			jQuery('.ps-js-crop-avatar', content_html).hide();

			pswindow.set_content(content_html);
			pswindow.set_actions(actions);

			jQuery('#imagePreview img').one('load', function () {
				pswindow.refresh();
			});

			that.init_avatar_fileupload();
			jQuery('#ps-window button[name=rep_submit]')
				.removeAttr('disabled')
				.addClass('ps-btn-primary');
			that.invalid_avatar_upload = false;
			that.avatar_use_gravatar = true;
		}
	});
};

PsProfile.prototype.save_preference = function (e) {
	var $el = jQuery(e && e.target ? e.target : e),
		$loading = $el.closest('.ps-js-profile-preferences-option').find('.ps-js-loading'),
		params = {};

	params.user_id = peepsodata.currentuserid;
	params.view_user_id = peepsodata.userid;
	params.meta_key = $el.attr('name');
	params.value = $el.is(':checkbox') ? ($el[0].checked ? 1 : 0) : $el.val();

	$loading
		.find('i')
		.stop()
		.hide();
	$loading.find('img').show();

	peepso.postJson('profilepreferencesajax.savepreference', params, function (json) {
		$loading.find('img').hide();
		$loading
			.find('i')
			.show()
			.delay(800)
			.fadeOut();
	});
};

PsProfile.prototype.save_notification = function (e) {
	var $el = jQuery(e && e.target ? e.target : e),
		$loading = $el.closest('.ps-js-notification-option').find('.ps-js-loading'),
		params = {};

	params.user_id = peepsodata.currentuserid;
	params.view_user_id = peepsodata.userid;
	params.fieldname = $el.attr('name');
	params.value = $el.is(':checkbox') ? ($el[0].checked ? 1 : 0) : $el.val();

	$loading
		.find('i')
		.stop()
		.hide();
	$loading.find('img').show();

	peepso.postJson('profilepreferencesajax.save_notifications', params, function (json) {
		$loading.find('img').hide();
		$loading
			.find('i')
			.show()
			.delay(800)
			.fadeOut();

		if (json.success) {
			// TODO
		} else {
			// TODO
		}
	});
};

jQuery(function () {
	profile.init();
});

// EOF
