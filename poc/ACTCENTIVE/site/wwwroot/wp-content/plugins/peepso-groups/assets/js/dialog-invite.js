import { dialog } from 'peepso';

const TEMPLATE = window.peepsogroupsdata && window.peepsogroupsdata.dialogInviteTemplate;

(function ($, factory) {
	var PsGroupDialogInvite = factory('PsGroupDialogInvite', $);

	peepso.groups || (peepso.groups = {});
	peepso.groups.dlgInvite = function (groupId) {
		let popup = new PsGroupDialogInvite(groupId);
		popup.popup.show();
	};
})(jQuery, function (name, $) {
	var LIMIT_PER_PAGE = 6;

	return peepso.createClass(name, {
		__constructor: function (groupId) {
			this.popup = dialog(TEMPLATE, { wide: true, onClose: this.onClose.bind(this) }).show();

			this.$el = this.popup.$el;
			this.$scrollable = this.$el.find('.ps-js-scrollable');
			this.$list = this.$el.find('.ps-js-member-items');
			this.$loading = this.$el.find('.ps-js-loading').hide();
			this.$more = this.$el.find('.ps-js-loadmore').hide();
			this.$nomore = this.$el.find('.ps-js-nomore').hide();

			this.itemTemplate = this.$el.find('.ps-js-member-item').html();
			this.itemTemplate = peepso.template(this.itemTemplate);

			this.$el.on('input', 'input[type=text]', $.proxy(this.onInput, this));
			this.$el.on('click', '.ps-js-invite', $.proxy(this.onInvite, this));

			this.$el.appendTo(document.body);

			// http://stackoverflow.com/questions/5802467/prevent-scrolling-of-parent-element
			this.$scrollable.bind(
				'mousewheel',
				$.proxy(function (e, d) {
					var t = $(e.currentTarget);
					if (d > 0 && t.scrollTop() === 0) {
						e.preventDefault();
					} else if (d < 0 && t.scrollTop() == t.get(0).scrollHeight - t.innerHeight()) {
						e.preventDefault();
					}
				}, this)
			);

			this.groupId = groupId;
			this.search();
		},

		search: function (query, page) {
			this.$loading.show();
			this.$more.hide();
			this.$nomore.hide();

			// Empty previous result on the first page.
			if (!page || page <= 1) {
				this.$list.empty();
			}

			this._search(query, page);
		},

		_search: _.debounce(function (query, page) {
			var params = {
				group_id: this.groupId,
				query: query || undefined,
				keys: 'id,avatar,fullname,profileurl,fullname_with_addons',
				limit: LIMIT_PER_PAGE,
				page: page || 1
			};

			this.fetch(params)
				.done(
					$.proxy(function (data) {
						this.render(data);

						// Scroll to the bottom of the list.
						var div = this.$scrollable[0];
						setTimeout(function () {
							div.scrollTop = div.scrollHeight;
						}, 100);
					}, this)
				)
				.fail(
					$.proxy(function (errors) {
						if (params.page > 1) {
							this.$nomore.show();
						} else {
							this.renderError(errors);
						}
					}, this)
				);
		}, 500),

		fetch: function (params) {
			return $.Deferred(
				$.proxy(function (defer) {
					peepso.getJson(
						'groupusersajax.search_to_invite',
						params,
						$.proxy(function (response) {
							this.$loading.hide();

							if (response.success) {
								defer.resolveWith(this, [response.data]);

								// Toggle load more button.
								var users = response.data.users;
								if (users.length >= LIMIT_PER_PAGE) {
									this.$more
										.show()
										.off('click')
										.one(
											'click',
											$.proxy(function () {
												this.search(params.query, params.page + 1);
											}, this)
										);
								} else {
									this.$more.hide().off('click');
									if (params.page > 1) {
										this.$nomore.show();
									}
								}
							} else {
								defer.rejectWith(this, [response.errors]);
							}
						}, this)
					);
				}, this)
			);
		},

		render: function (data) {
			var html = '',
				i;

			if (data.users && data.users.length) {
				for (var i = 0; i < data.users.length; i++) {
					html += this.itemTemplate($.extend({ group_id: this.groupId }, data.users[i]));
				}
				this.$list.append(html);
			}
		},

		renderError: function (errors) {
			if (errors && errors.length) {
				this.$list.html(errors.join('<br />'));
			}
		},

		invite: function (userId) {
			return $.Deferred(
				$.proxy(function (defer) {
					ps_group
						.inviteUser(this.groupId, userId)
						.done(
							$.proxy(function (data) {
								defer.resolveWith(this, [data]);
							}, this)
						)
						.fail(
							$.proxy(function (errors) {
								defer.rejectWith(this, [errors]);
							}, this)
						);
				}, this)
			);
		},

		onInput: function (e) {
			e.preventDefault();
			e.stopPropagation();
			this.search($.trim(e.target.value));
		},

		onInvite: function (e) {
			var $btn = $(e.currentTarget),
				$loading = $btn.find('img').show(),
				$text = $btn.find('span'),
				userId = $btn.data('id');

			e.preventDefault();
			e.stopPropagation();

			this.invite(userId)
				.always(function () {
					$loading.hide();
				})
				.done(function () {
					$text.html($text.data('invited'));
					$btn.attr('disabled', 'disabled');
				});
		},

		onClose: function () {
			if (this.popup.opts.reloadOnClose) {
				setTimeout(function () {
					window.location.reload();
				}, 500);
			}
		}
	});
});
