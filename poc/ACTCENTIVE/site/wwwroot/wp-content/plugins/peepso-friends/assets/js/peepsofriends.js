(function ($, peepso, factory) {
	// psfriends is still used in peepsomessages.js
	window.psfriends = peepso.friends = factory($, peepso);
})(jQuery || $, peepso, function ($, peepso) {
	function PsFriends() {}

	var psfriends = new PsFriends();

	// Sets up event callbacks
	PsFriends.prototype.init = function () {};

	/**
	 * Prevents submission of the search for if the field is empty
	 *
	 * @param form The form field.
	 */
	PsFriends.prototype.submit_search = function (form) {
		var search = $('input[name="query"]', form);
		if ('' === search.val().trim()) return false;

		search.val(encodeURIComponent(search.val()));
		return true;
	};

	/**
	 * TODO: docblock
	 */
	PsFriends.prototype.show_mutual_friends = function (from_id, to_id) {
		var req = {
			from_id: from_id,
			to_id: to_id
		};

		// cancel ajax
		this.show_mutual_friends_ajax &&
			this.show_mutual_friends_ajax.ret &&
			this.show_mutual_friends_ajax.ret.abort();
		this.show_mutual_friends_ajax = false;

		var getMutual = $.proxy(
			_.debounce(function (callback) {
				if (!this.show_mutual_friends_ajax) {
					req.page = (req.page || 0) + 1;
					this.show_mutual_friends_ajax = peepso.postJson(
						'friendsajax.get_mutual_friends',
						req,
						$.proxy(function (response) {
							this.show_mutual_friends_ajax = false;
							callback(response);
						}, this)
					);
				}
			}, 500),
			this
		);

		getMutual(function (response) {
			var data, title, content, popup;
			if (response.success) {
				data = response.data || {};
				title = data.title;
				content = data.template.replace('##friends##', data.friends);
				popup = peepso.dialog(content, { title: title }).show();

				popup.$el.find('.ps-members-item-popup').on('scroll', function () {
					var $el = $(this),
						$ct,
						$loading;

					if ($el.scrollTop() + $el.innerHeight() >= this.scrollHeight - 5) {
						$ct = popup.$el.find('.ps-members-item-popup');
						$loading = $ct.next('img').show();
						getMutual(function (response) {
							$loading.hide();
							if (response.success) {
								$ct.append(response.data.friends);
								if (!+response.data.found_friends) {
									$ct.off('scroll');
								}
							}
						});
					}
				});
			}
		});
	};

	/**
	 * Menu selector
	 */
	PsFriends.prototype.select_menu = function (select) {
		var $option = $(select.options[select.selectedIndex]),
			value = $option.val(),
			url = $option.data('url'),
			loc = window.location + '',
			samePage = loc.match(/\/requests/) && url.match(/\/requests/);

		if (samePage) {
			$('.ps-js-friends-submenu')
				.siblings('.tab-content')
				.find(value === 'sent-request' ? '#sent' : '#received')
				.addClass('active')
				.siblings()
				.removeClass('active');
		} else {
			window.location = $option.data('url');
		}
	};

	$(function () {
		psfriends.init();
	});

	return psfriends;
});
