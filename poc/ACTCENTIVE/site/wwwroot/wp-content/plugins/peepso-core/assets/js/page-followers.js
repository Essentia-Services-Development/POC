(function ($, factory) {
	var PsPageFollowers = factory($);
	var ps_page_followers = new PsPageFollowers('.ps-js-followers');
})(jQuery, function ($) {
	function PsPageFollowers() {
		PsPageFollowers.super_.apply(this, arguments);
		$($.proxy(this.init_page, this));
	}

	// inherit from `PsPageAutoload`
	peepso.npm.inherits(PsPageFollowers, PsPageAutoload);

	peepso.npm.objectAssign(PsPageFollowers.prototype, {
		init_page: function () {},

		_search_url: 'followerajax.get_user_' + peepsofollowers.current,

		_search_params: {
			uid: peepsodata.currentuserid,
			user_id: peepsodata.userid,
			limit: 2,
			page: 1
		},

		_search_render_html: function (data) {
			return data[peepsofollowers.current] || '';
		},

		_search_get_items: function () {
			return this._search_$ct.children('.ps-js-member');
		}
	});

	return PsPageFollowers;
});
