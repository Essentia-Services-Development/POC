(function ($, factory) {
	var PsPageFriends = factory($);
	var ps_page_friends = new PsPageFriends('.ps-js-friends');
})(jQuery, function ($) {
	function PsPageFriends() {
		PsPageFriends.super_.apply(this, arguments);
		$($.proxy(this.init_page, this));
	}

	// inherit from `PsPageAutoload`
	peepso.npm.inherits(PsPageFriends, PsPageAutoload);

	peepso.npm.objectAssign(PsPageFriends.prototype, {
		init_page: function () {},

		_search_url: 'friendsajax.get_user_friends',

		_search_params: {
			uid: peepsodata.currentuserid,
			user_id: peepsodata.userid,
			limit: 2,
			page: 1
		},

		_search_render_html: function (data) {
			return data.friends || '';
		},

		_search_get_items: function () {
			return this._search_$ct.children('.ps-js-member');
		}
	});

	return PsPageFriends;
});
