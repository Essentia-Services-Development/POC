(function ($, peepso, factory) {
	/**
	 * PsGroup global instance.
	 * @name ps_group
	 * @type {PsGroup}
	 */
	ps_group = new (factory($, peepso))();

	// initialize group page
	$(function () {
		ps_group.init_page(+peepsogroupsdata.group_id);
	});
})(jQuery, peepso, function ($, peepso) {
	const MAX_CATEGORIES = window.peepsogroupsdata && +window.peepsogroupsdata.max_categories;

	/**
	 * PsGroup class.
	 * @class PsGroup
	 */
	function PsGroup() {
		this.ajax = {};
		this.cover = {};
		this.cover.x_position_percent = 0;
		this.cover.y_position_percent = 0;

		this.$cover_ct = jQuery('.js-focus-cover');
		this.$cover_image = peepsogroupsdata.group_id
			? jQuery('img#' + peepsogroupsdata.group_id)
			: jQuery();
		this.initial_cover_position = this.$cover_image.attr('style');

		// add module id
		peepso.observer.addFilter(
			'postbox_req',
			function (req) {
				if (+peepsogroupsdata.group_id) {
					req.module_id = peepsogroupsdata.module_id;
					req.group_id = peepsogroupsdata.group_id;
				}
				return req;
			},
			10,
			1
		);

		// add module id
		peepso.observer.addFilter(
			'show_more_posts',
			function (req) {
				if (+peepsogroupsdata.group_id) {
					req.stream_id = peepsogroupsdata.module_id;
					req.module_id = peepsogroupsdata.module_id;
					req.group_id = peepsogroupsdata.group_id;
				} else if (+peepsogroupsdata.group_category_id) {
					req.group_category_id = peepsogroupsdata.group_category_id;
				}
				return req;
			},
			10,
			1
		);

		/**
		 * Photos observer
		 */
		// add module id
		peepso.observer.addFilter(
			'photos_validate_req',
			function (req) {
				if (+peepsogroupsdata.group_id) {
					req.module_id = peepsogroupsdata.module_id;
					req.group_id = peepsogroupsdata.group_id;
				}
				return req;
			},
			10,
			1
		);

		peepso.observer.addFilter(
			'photos_upload_req',
			function (req) {
				if (+peepsogroupsdata.group_id) {
					req.module_id = peepsogroupsdata.module_id;
					req.group_id = peepsogroupsdata.group_id;
				}
				return req;
			},
			10,
			1
		);

		peepso.observer.addFilter(
			'photos_rotate_req',
			function (req) {
				if (+peepsogroupsdata.group_id) {
					req.module_id = peepsogroupsdata.module_id;
					req.group_id = peepsogroupsdata.group_id;
				}
				return req;
			},
			10,
			1
		);

		peepso.observer.addFilter(
			'photos_get_list_photos',
			function (req) {
				var isProfile = peepsodata.profile && +peepsodata.profile.id;
				if (!isProfile && +peepsogroupsdata.group_id) {
					req.module_id = peepsogroupsdata.module_id;
					req.group_id = peepsogroupsdata.group_id;
				}
				return req;
			},
			10,
			1
		);

		peepso.observer.addFilter(
			'photos_get_list_albums',
			function (req) {
				var isProfile = peepsodata.profile && +peepsodata.profile.id;
				if (!isProfile && +peepsogroupsdata.group_id) {
					req.module_id = peepsogroupsdata.module_id;
					req.group_id = peepsogroupsdata.group_id;
				}
				return req;
			},
			10,
			1
		);

		peepso.observer.addFilter(
			'photos_create_album_req',
			function (req) {
				if (+peepsogroupsdata.group_id) {
					req.module_id = peepsogroupsdata.module_id;
					req.group_id = peepsogroupsdata.group_id;
				}
				return req;
			},
			10,
			1
		);

		peepso.observer.addFilter(
			'photos_add_photos_to_album_req',
			function (req) {
				if (+peepsogroupsdata.group_id) {
					req.module_id = peepsogroupsdata.module_id;
					req.group_id = peepsogroupsdata.group_id;
				}
				return req;
			},
			10,
			1
		);

		peepso.observer.addFilter(
			'photos_remove_temp_files',
			function (req) {
				if (+peepsogroupsdata.group_id) {
					req.module_id = peepsogroupsdata.module_id;
					req.group_id = peepsogroupsdata.group_id;
				}
				return req;
			},
			10,
			1
		);

		peepso.observer.addFilter(
			'photos_delete_album',
			function (req) {
				if (+peepsogroupsdata.group_id) {
					req.module_id = peepsogroupsdata.module_id;
					req.group_id = peepsogroupsdata.group_id;
				}
				return req;
			},
			10,
			1
		);

		peepso.observer.addFilter(
			'photos_delete_stream_album',
			function (req) {
				if (+peepsogroupsdata.group_id) {
					req.module_id = peepsogroupsdata.module_id;
					req.group_id = peepsogroupsdata.group_id;
				}
				return req;
			},
			10,
			1
		);

		/*VIDEOS*/
		peepso.observer.addFilter(
			'peepso_list_videos',
			function (req) {
				if (+peepsogroupsdata.group_id) {
					req.module_id = peepsogroupsdata.module_id;
					req.group_id = peepsogroupsdata.group_id;
				}
				return req;
			},
			10,
			1
		);

		peepso.observer.addFilter(
			'tags_get_taggable_params',
			function (req) {
				if (+peepsogroupsdata.group_id) {
					req.module_id = peepsogroupsdata.module_id;
					req.group_id = peepsogroupsdata.group_id;
				}
				return req;
			},
			10,
			1
		);

		// Show avatar dialog handler.
		peepso.observer.addFilter(
			'show_avatar_dialog',
			function (handler) {
				if (peepsogroupsdata.id) {
					return $.proxy(this.showAvatarDialog, this);
				} else {
					return handler;
				}
			},
			10,
			1,
			this
		);

		// Response on update group name
		peepso.observer.addAction(
			'group_name_updated',
			function (groupId, name) {
				var $header;
				if (+peepsogroupsdata.group_id === +groupId) {
					$header = $('.ps-js-group-header');
					if ($header.length) {
						$header.find('.ps-focus-title > span').text(name);
					}
				}
			},
			10,
			2,
			this
		);

		// Response on update group privacy
		peepso.observer.addAction(
			'group_privacy_updated',
			function (groupId, privacy) {
				if (+peepsogroupsdata.group_id === +groupId) {
					// Reload settings page on update group privacy.
					if ($('.ps-page--group-settings').length) {
						window.location.reload();
					}
				}
			},
			10,
			2,
			this
		);

		peepso.observer.addFilter(
			'files_upload_req',
			function (req) {
				if (+peepsogroupsdata.group_id) {
					req.module_id = peepsogroupsdata.module_id;
					req.group_id = peepsogroupsdata.group_id;
				}
				return req;
			},
			10,
			1
		);

		peepso.observer.addFilter(
			'files_get_list_files',
			function (req) {
				var isProfile = peepsodata.profile && +peepsodata.profile.id;
				if (!isProfile && +peepsogroupsdata.group_id) {
					req.module_id = peepsogroupsdata.module_id;
					req.group_id = peepsogroupsdata.group_id;
				}
				return req;
			},
			10,
			1
		);
	}

	/**
	 * @memberof PsGroup
	 * @param {number} group_id
	 */
	PsGroup.prototype.init_page = function (group_id) {
		if (group_id) {
			this._header_$el = $('.ps-js-group-header');
			this._header_$actions = $('.ps-js-group-header-actions');
			this._header_$actions_loadingHtml = this._header_$actions.eq(0).html();

			// fetch group page data
			this._header_$actions.html(this._header_$actions_loadingHtml);
			this._get_data(group_id).done(function (data) {
				this._data = data;
				peepso.observer.applyFilters('group_data', data);
			});

			// update header actions
			peepso.observer.addFilter(
				'group_data',
				$.proxy(function (data) {
					this._update_header_actions(data);
				}, this),
				10,
				1
			);

			// attach event listeners
			this._header_$actions.on(
				'click',
				'.ps-js-join',
				$.proxy(function (e) {
					var $loading = $(e.currentTarget).find('img').show();
					this.join(group_id).done(function (data) {
						$loading.hide();
						window.location.reload();
					});
				}, this)
			);

			// attach event listeners
			this._header_$actions.on(
				'click',
				'.ps-js-leave',
				$.proxy(function (e) {
					var $loading = $(e.currentTarget).find('img').show();
					this.leave(group_id).done(function (data) {
						$loading.hide();
						window.location.reload();
					});
				}, this)
			);

			// call lagacy init page code
			this.init();
		}

		$(document.body).on(
			'click',
			'.ps-js-group-member-action',
			$.proxy(function (e) {
				var $btn = $(e.currentTarget);
				if ($btn.data('ps-loading')) {
					return;
				}

				e.preventDefault();
				e.stopPropagation();
				var data = $.extend({}, $btn.data());
				if (!data.method || !(data.id || group_id)) {
					return;
				}

				var $loading = $btn.find('img');
				if (!$loading.length && $btn.parent().hasClass('ps-js-dropdown-menu')) {
					$loading = $btn.parent().siblings('.ps-js-dropdown-toggle');
					$loading = $loading.find('img');

					// Hide dropdown automatically if loading is on the trigger button.
					$btn.parent().hide();
				}

				var method = data.method;
				var confirm = data.confirm;
				data.group_id = data.id || group_id;
				delete data.method;
				delete data.confirm;
				delete data.id;

				this._member_action_confirmation(confirm).done(function () {
					if (method.indexOf('search_to_invite') >= 0) {
						// invite button
						this.invite(group_id);
						return;
					}

					$btn.data('ps-loading', true);
					$loading.show();

					this.member_action(method, data)
						.done(function (json) {
							var $item = $btn.closest('.ps-js-member'),
								template,
								templateData;

							if (method.indexOf('passive') >= 0) {
								// passive
								if (json.reload) {
									window.location.reload();
									return;
								}
								if (json.hide) {
									$item.remove();

									// Update invited members count.
									if (!_.isUndefined(json.pending_user_member_count)) {
										peepso.observer.doAction(
											'pending_user_member_count',
											+data.group_id,
											+json.pending_user_member_count
										);
									}

									// Update pending members count.
									if (!_.isUndefined(json.pending_admin_member_count)) {
										peepso.observer.doAction(
											'pending_admin_member_count',
											+data.group_id,
											+json.pending_admin_member_count
										);
									}
								} else {
									if (!_.isUndefined(json.display_role)) {
										var role = json.display_role,
											$role = $item.find('script[data-role="' + role + '"]'),
											$label = $item.find('.ps-js-member-role');

										if ($role.length) {
											$label.html($role.text()).show();
										} else {
											$label.hide().html('');
										}
									}
									if (
										json.member_passive_actions &&
										json.member_passive_actions.length
									) {
										template = peepso.template(
											peepsogroupsdata.memberItemActionsTemplate
										);
										templateData = {
											member_passive_actions: json.member_passive_actions,
											id: data.group_id,
											passive_user_id: data.passive_user_id
										};
										$item.find('.ps-js-dropdown').html(template(templateData));
									}
								}

								// Reload the passive action in group cover after modifying our own role.
								if (+peepsodata.currentuserid === +data.passive_user_id) {
									this._header_$actions.html(this._header_$actions_loadingHtml);
									this._get_data(data.group_id).done(function (data) {
										this._data = data;
										peepso.observer.applyFilters('group_data', data);
									});
								}

								peepso.observer.doAction('notification_clear_cache');
							} else if (method.match(/\.?join$/)) {
								// join
								window.location.reload();
							} else if (method.indexOf('leave') >= 0) {
								// leave
								if (json.member_actions && json.member_actions.length > 1) {
									this._update_header_actions(json);
								} else {
									window.location.reload();
								}
							} else {
								this._update_header_actions(json);
							}
						})
						.fail(function (errors) {
							$btn.removeData('ps-loading');
							$loading.hide();
							if (errors && errors.length) {
								pswindow.show('', errors.join('<br/>'));
							}
						});
				});
			}, this)
		);

		// show group extended info on click 'more' button on group listing
		$(document.body).on(
			'click',
			'.ps-js-groups .ps-js-more',
			$.proxy(function (e) {
				e.preventDefault();
				e.stopPropagation();

				var $item = $(e.currentTarget).closest('.ps-js-group-item'),
					$owner = $item.find('.ps-js-owner'),
					$categories = $item.find('.ps-js-categories'),
					$caticon = $item.find('.ps-js-category-icon');

				// get owner info
				if (+peepsogroupsdata.list_show_owner) {
					if (!$owner.data('ps-loaded') && !$owner.data('ps-loading')) {
						$owner.data('ps-loading', true);
						this.get_owners($owner.data('id')).done(function (owners) {
							$owner.data('ps-loaded', true);
							$owner.removeData('ps-loading');
							if (owners && owners.length) {
								$owner.html(
									'<a href="' +
										owners[0].profileurl +
										'">' +
										owners[0].fullname +
										'</a>'
								);
							} else {
								$owner.html('<em>No owner</em>');
							}
						});
					}
				}

				// get category info
				if (!$categories.data('ps-loaded') && !$categories.data('ps-loading')) {
					$categories.data('ps-loading', true);
					this.get_categories($categories.data('id')).done(function (categories) {
						$categories.data('ps-loaded', true);
						$categories.removeData('ps-loading');
						if (categories && categories.length) {
							categories = _.map(categories, function (category) {
								return '<a href="' + category.url + '">' + category.name + '</a>';
							});
							$categories.html(categories.join(', '));
							if (categories.length > 1) {
								$caticon.attr('class', 'ps-icon-tags');
							}
						} else {
							$categories.html('<em>No category</em>');
						}
					});
				}
			}, this)
		);

		$('.ps-js-focus .ps-js-privacy .ps-js-dropdown-menu').on(
			'click',
			'[data-option-value]',
			function (e) {
				var $a = $(e.currentTarget),
					$dd = $a.closest('.ps-js-dropdown-menu'),
					$btn = $dd.siblings('.ps-js-dropdown-toggle'),
					privacy = +$a.data('optionValue'),
					params;

				// Exit if selected is the same as current value.
				if (privacy === +peepsogroupsdata.privacy) {
					return;
				}

				params = {
					privacy: privacy,
					group_id: peepsogroupsdata.group_id,
					_wpnonce: peepsogroupsdata.nonce_set_group_privacy
				};

				pswindow.confirm(peepsogroupsdata.lang.privacy_change_confirmation, function () {
					var $loading = $btn.find('img').show();

					pswindow.hide();
					peepso.getJson('groupajax.set_group_privacy', params, function (json) {
						var $btnIcon = $btn.find('i'),
							$btnText = $btnIcon.siblings('span'),
							privacy;

						$loading.hide();

						if (json.success) {
							privacy = json.data.new_privacy;

							// Update selected privacy.
							peepsogroupsdata.privacy = privacy.id;
							$btnIcon.attr('class', privacy.icon);
							$btnText.html(privacy.name);

							// Highlight button on success.
							$btn.addClass('ps-list-info-success');
							setTimeout(function () {
								$btn.removeClass('ps-list-info-success');
							}, 1000);

							// Notify app about updated privacy setting of this group.
							peepso.observer.doAction('group_privacy_updated', params.group_id, {
								id: privacy.id,
								icon: privacy.icon,
								name: privacy.name
							});
						} else if (json.errors) {
							psmessage.show('', json.errors[0]);
						}
					});
				});
			}
		);

		$('.ps-js-focus-box-toggle').on('click', function (e) {
			var $box = $('.ps-js-focus-desc'),
				$doc = $(document),
				evtName = 'click.ps-focusbox-toggle',
				clsOpen = 'ps-focus__desc--open';

			e.stopPropagation();

			if ($box.hasClass(clsOpen)) {
				$box.removeClass(clsOpen);
				$doc.off(evtName);
			} else {
				$box.addClass(clsOpen);
				$doc.one(evtName, function () {
					$box.removeClass(clsOpen);
				});
			}
		});
	};

	/**
	 * @memberof PsGroup
	 * @param {number} group_id
	 * @return jQuery.Deffered
	 */
	PsGroup.prototype._get_data = function (group_id) {
		return $.Deferred(
			$.proxy(function (defer) {
				var params = {
					group_id: group_id,
					context: 'cover',
					keys: 'groupuserajax.member_actions,groupfollowerajax.follower_actions'
				};

				peepso.ajax.get('groupajax.group', params, 5).done(
					function (json) {
						if (json.success) {
							defer.resolveWith(this, [json.data]);
						} else {
							defer.rejectWith(this, [json.errors]);
						}
					}.bind(this)
				);
			}, this)
		);
	};

	PsGroup.prototype._normalize_header_actions = function (actions, prefix) {
		return _.map(
			actions,
			function (item) {
				var label = item.label || '',
					firstChar = label.charAt(0);

				if (firstChar.match(/[a-z]/)) {
					label = firstChar.toUpperCase() + label.slice(1);
				}

				item.label = label;

				if (_.isString(item.action) && item.action.indexOf('.') < 0) {
					item.action = prefix + '.' + item.action;
				} else if (_.isArray(item.action)) {
					item.action = this._normalize_header_actions(item.action, prefix);
				}

				return item;
			},
			this
		);
	};

	/**
	 * @memberof PsGroup
	 * @param {object} data
	 */
	PsGroup.prototype._update_header_actions = function (data) {
		var template = peepso.template(peepsogroupsdata.headerActionsTemplate),
			memberActions,
			followerActions,
			group;

		data = data || {};
		group = data.group || {};

		if (group.groupuserajax && !_.isUndefined(group.groupuserajax.member_actions)) {
			memberActions = group.groupuserajax.member_actions || [];
		} else if (!_.isUndefined(data.member_actions)) {
			memberActions = data.member_actions || [];
		}

		if (memberActions) {
			memberActions = this._normalize_header_actions(memberActions, 'groupuserajax');
		}

		if (group.groupfollowerajax && !_.isUndefined(group.groupfollowerajax.follower_actions)) {
			followerActions = group.groupfollowerajax.follower_actions || [];
		} else if (!_.isUndefined(data.follower_actions)) {
			followerActions = data.follower_actions || [];
		}

		if (followerActions) {
			followerActions = this._normalize_header_actions(followerActions, 'groupfollowerajax');
		}

		this._header_actions = this._header_actions || {};
		this._header_actions.member_actions = memberActions || this._header_actions.member_actions;
		this._header_actions.follower_actions =
			followerActions || this._header_actions.follower_actions;
		this._header_$actions.html(template(this._header_actions));

		// Check for #membership hash, then open and flash `.ps-js-btn-membership` button
		// if the hash is found.
		_.defer(
			$.proxy(function () {
				var loc = window.location,
					url = loc.href,
					$btn,
					$dropdown;

				if (url.match(/#membership/)) {
					$btn = this._header_$actions.find('.ps-js-btn-membership');
					$dropdown = $btn.siblings('.ps-js-dropdown-menu').show();

					// Highlight button and dropdown.
					$btn.add($dropdown).addClass('ps-list-info-success');
					setTimeout(function () {
						$btn.add($dropdown).removeClass('ps-list-info-success');
					}, 1000);

					// Remove hash with History API if possible.
					if ('replaceState' in window.history) {
						history.replaceState('', document.title, loc.pathname + loc.search);
					}
				}
			}, this)
		);
	};

	/**
	 * @memberof PsGroup
	 * @param {number} group_id
	 */
	PsGroup.prototype.get_owners = function (group_id) {
		return $.Deferred(
			$.proxy(function (defer) {
				var params = {
					group_id: group_id,
					role: 'owner',
					keys: 'fullname,profileurl'
				};
				peepso.getJson(
					'groupusersajax.search',
					params,
					$.proxy(function (response) {
						if (response.data && response.data.members) {
							defer.resolveWith(this, [response.data.members]);
						} else {
							defer.rejectWith(this, [response.errors]);
						}
					}, this)
				);
			}, this)
		);
	};

	/**
	 * @memberof PsGroup
	 * @param {number} group_id
	 */
	PsGroup.prototype.get_members = function (group_id) {
		return $.Deferred(
			$.proxy(function (defer) {
				var params = {
					group_id: group_id,
					keys: 'avatar,fullname,profileurl,role'
				};
				peepso.getJson(
					'groupusersajax.search',
					params,
					$.proxy(function (response) {
						if (response.data && response.data.members) {
							defer.resolveWith(this, [response.data.members]);
						} else {
							defer.rejectWith(this, [response.errors]);
						}
					}, this)
				);
			}, this)
		);
	};

	/**
	 * @memberof PsGroup
	 * @param {number} group_id
	 */
	PsGroup.prototype.get_categories = function (group_id) {
		return $.Deferred(
			$.proxy(function (defer) {
				var params = {
					group_id: group_id,
					keys: 'name,url'
				};
				peepso.getJson(
					'groupcategoriesgroupsajax.categories_for_group',
					params,
					$.proxy(function (response) {
						if (response.data && response.data.categories) {
							defer.resolveWith(this, [response.data.categories]);
						} else {
							defer.rejectWith(this, [response.errors]);
						}
					}, this)
				);
			}, this)
		);
	};

	/**
	 * @memberof PsGroup
	 * @param {number} group_id
	 */
	PsGroup.prototype.join = function (group_id) {
		return $.Deferred(
			$.proxy(function (defer) {
				peepso.postJson(
					'groupuserajax.join',
					{ group_id: group_id },
					$.proxy(function (response) {
						if (response.success) {
							defer.resolveWith(this, [response.data]);
						} else {
							defer.rejectWith(this, [response.errors]);
						}
					}, this)
				);
			}, this)
		);
	};

	/**
	 * @memberof PsGroup
	 * @param {number} group_id
	 */
	PsGroup.prototype.leave = function (group_id) {
		return $.Deferred(
			$.proxy(function (defer) {
				peepso.postJson(
					'groupuserajax.leave',
					{ group_id: group_id },
					$.proxy(function (response) {
						if (response.success) {
							defer.resolveWith(this, [response.data]);
						} else {
							defer.rejectWith(this, [response.errors]);
						}
					}, this)
				);
			}, this)
		);
	};

	/**
	 * @memberof PsGroup
	 * @param {number} groupId
	 */
	PsGroup.prototype.invite = function (groupId) {
		peepso.groups.dlgInvite(groupId);
	};

	/**
	 * @memberof PsGroup
	 * @param {number} groupId
	 * @param {number} userId
	 */
	PsGroup.prototype.inviteUser = function (groupId, userId) {
		return $.Deferred(
			$.proxy(function (defer) {
				peepso.getJson(
					'groupuserajax.passive_invite',
					{
						group_id: groupId,
						passive_user_id: userId
					},
					$.proxy(function (response) {
						if (response.success) {
							defer.resolveWith(this, [response.data]);
						} else {
							defer.rejectWith(this, [response.errors]);
						}
					}, this)
				);
			}, this)
		);
	};

	/**
	 * Edit group name.
	 * @param {Number} groupId
	 * @param {HTMLElement} elem
	 */
	PsGroup.prototype.edit_name = function (groupId, elem) {
		var $ct = $(elem).closest('.ps-js-group-name'),
			$text = $ct.find('.ps-js-group-name-text'),
			$editor = $ct.find('.ps-js-group-name-editor'),
			$btnEdit = $ct.find('.ps-js-btn-edit'),
			$btnCancel = $ct.find('.ps-js-btn-cancel'),
			$btnSubmit = $ct.find('.ps-js-btn-submit'),
			$input = $editor.find('input[type=text]'),
			$limit = $ct.find('.ps-js-limit'),
			value = $input.val();

		if ($editor.is(':visible')) {
			return;
		}

		// Show editor.
		$text.hide();
		$btnEdit.hide();
		$btnCancel.show();
		$btnSubmit.show();
		$editor.show();

		// Focus input and put cursor at the end of the text.
		$input.focus();
		_.defer(function () {
			$input[0].selectionStart = $input[0].selectionEnd = value.length;
		});

		// Handle input value change.
		if (!$input.data('original-value')) {
			$input.data('original-value', value);
			$input
				.off('input keydown')
				.on('input', function (e) {
					var currVal = $.trim($input.val()),
						origVal = $.trim($input.data('original-value')),
						maxLength = +$input.data('maxlength');

					// Respect charaters limit value.
					if (maxLength && currVal.length > maxLength) {
						currVal = currVal.substr(0, maxLength);
						$input.val(currVal);
					}
					$limit.html(maxLength - currVal.length);

					// Disable submit on empty or same value.
					if (!currVal || currVal === origVal) {
						$btnSubmit.attr('disabled', 'disabled');
					} else {
						$btnSubmit.removeAttr('disabled');
					}
				})
				.on('keydown', function (e) {
					if (e.keyCode === 13) {
						e.preventDefault();
						e.stopPropagation();

						if (!$btnSubmit.attr('disabled')) {
							$btnSubmit.click();
						}
					}
				});
		}

		$input.trigger('input');

		// Handle cancel button.
		$btnCancel.off('click').on('click', function () {
			$input.val($input.data('original-value'));
			$editor.hide();
			$btnSubmit.hide();
			$btnCancel.hide();
			$btnEdit.show();
			$text.show();
		});

		// Handle submit button.
		$btnSubmit.off('click').on(
			'click',
			$.proxy(function () {
				var confirmText = peepsogroupsdata.lang.name_change_confirmation;

				pswindow.confirm(
					confirmText,
					$.proxy(function () {
						var $loading = $btnSubmit.find('img').show(),
							value = $input.val();

						pswindow.hide();
						this._updateName(groupId, value)
							.always(function () {
								$loading.hide();
							})
							.done(function () {
								$input.data('original-value', value);
								$text.text(value);
								$btnCancel.click();
							})
							.fail(function (error) {
								psmessage.show('', error);
							});
					}, this),
					function () {
						pswindow.hide();
						$btnCancel.click();
					}
				);
			}, this)
		);
	};

	/**
	 * Save edit group name.
	 * @param {Number} groupId
	 * @param {String} name
	 * @return {jQuery.Deferred}
	 */
	PsGroup.prototype._updateName = function (groupId, name) {
		var xhrID = '_xhr_' + groupId,
			xhr = this._updateName[xhrID];

		// Cancel previous ajax request.
		if (xhr) {
			xhr.abort();
		}

		return $.Deferred(
			$.proxy(function (defer) {
				var xhr = peepso.postJson(
					'groupajax.set_group_name',
					{
						group_id: groupId,
						name: name,
						_wpnonce: peepsogroupsdata.nonce_set_group_name
					},
					$.proxy(function (json) {
						if (json.success) {
							peepso.observer.doAction('group_name_updated', groupId, name);
							defer.resolve();
						} else if (json.errors) {
							defer.reject(json.errors[0]);
						} else {
							defer.reject();
						}
					}, this)
				);

				// Save XHR object for cancel purpose.
				this._updateName[xhrID] = xhr.ret;
			}, this)
		);
	};

	/**
	 * @memberof PsGroup
	 * @param {string} method
	 * @param {object} data
	 */
	PsGroup.prototype.edit_slug = function (group_id, elem) {
		var $ct = $(elem).closest('.ps-js-group-slug'),
			$text = $ct.find('.ps-js-group-slug-text'),
			$editor = $ct.find('.ps-js-group-slug-editor'),
			$btnEdit = $ct.find('.ps-js-group-slug-trigger'),
			$btnCancel = $ct.find('.ps-js-cancel'),
			$btnSubmit = $ct.find('.ps-js-submit'),
			$input,
			$submit,
			value;

		if ($editor.is(':visible')) {
			return;
		}

		// Show editor.
		$text.hide();
		$btnEdit.hide();
		$btnCancel.show();
		$btnSubmit.show();
		$editor.show();

		$submit = $editor.find('.ps-js-submit');
		$input = $editor.find('input');
		$input.data('original-value', (value = $input.val())); // save original value
		$input.focus().val('').val(value); // focus

		$editor.off('click input');

		// Handle cancel button.
		$btnCancel.off('click').on('click', function () {
			$input.val(value);
			$editor.hide();
			$btnSubmit.hide();
			$btnCancel.hide();
			$btnEdit.show();
			$text.show();
		});

		// Handle submit button.
		$btnSubmit.off('click').on(
			'click',
			$.proxy(function (e) {
				this.save_slug(group_id, $input.val(), e.currentTarget);
			}, this)
		);

		// handle text input
		$editor.on('input', 'input', function (e) {
			var $input = $(e.currentTarget),
				$limit = $input.closest('.ps-js-group-slug').find('.ps-js-limit'),
				maxLength = +$input.data('maxlength'),
				val = $input.val();

			if (maxLength && val.length > maxLength) {
				val = val.substr(0, maxLength);
				$input.val(val);
			}
			$limit.html(maxLength - val.length);

			if ($.trim(val)) {
				$submit.removeAttr('disabled');
			} else {
				$submit.attr('disabled', 'disabled');
			}
		});
		$input.trigger('input');
	};

	/**
	 * @memberof PsGroup
	 * @param {string} method
	 * @param {object} data
	 */
	PsGroup.prototype.save_slug = function (group_id, slug, elem) {
		var flag = 'save_slug',
			$loading;

		if (this.ajax[flag]) {
			return;
		}

		this.ajax[flag] = true;
		slug = $.trim(slug);
		$loading = $(elem).find('.ps-js-loading');
		$loading.show();

		peepso.postJson(
			'groupajax.set_group_slug',
			{
				group_id: group_id,
				slug: slug,
				_wpnonce: peepsogroupsdata.nonce_set_group_slug
			},
			$.proxy(function (json) {
				this.ajax[flag] = false;
				$loading.hide();

				if (json.success) {
					window.location = json.data.redirect;
				} else if (json.errors) {
					peepso.dialog(json.errors[0], { error: true }).show();
				}
			}, this)
		);
	};

	/**
	 * @memberof PsGroup
	 * @param {string} method
	 * @param {object} data
	 */
	PsGroup.prototype.save_name = function (group_id, name, elem) {
		var flag = 'save_name',
			$loading;

		if (this.ajax[flag]) {
			return;
		}

		this.ajax[flag] = true;
		name = $.trim(name);
		$loading = $(elem).find('.ps-js-loading');
		$loading.show();

		peepso.postJson(
			'groupajax.set_group_name',
			{
				group_id: group_id,
				name: name,
				_wpnonce: peepsogroupsdata.nonce_set_group_name
			},
			$.proxy(function (json) {
				var $ct = $(elem).closest('.ps-js-group-name'),
					$text = $ct.find('.ps-js-group-name-text'),
					$editor = $ct.find('.ps-js-group-name-editor'),
					$trigger = $ct.find('.ps-js-group-name-trigger'),
					$input = $editor.find('input');

				this.ajax[flag] = false;
				$loading.hide();

				if (json.success) {
					$input.val(name);
					$editor.off('click').hide();
					$text.text(name).show();
					$trigger.show();

					if (json.data && json.data.redirect) {
						window.location = json.data.redirect;
					}
				} else if (json.errors) {
					psmessage.show('', json.errors[0]);
				}
			}, this)
		);
	};

	/**
	 * Show form for edit group categories.
	 *
	 * @memberof PsGroup
	 * @param {number} group_id
	 * @param {HTMLElement} elem
	 */
	PsGroup.prototype.edit_cats = function (group_id, elem) {
		var $ct = $(elem).closest('.ps-js-group-cat'),
			$text = $ct.find('.ps-js-group-cat-text'),
			$editor = $ct.find('.ps-js-group-cat-editor'),
			$btnEdit = $ct.find('.ps-js-btn-edit'),
			$btnCancel = $ct.find('.ps-js-cancel'),
			$btnSubmit = $ct.find('.ps-js-submit'),
			$checkboxes;

		if ($editor.is(':visible')) {
			return;
		}

		// Show editor.
		$text.hide();
		$btnEdit.hide();
		$btnCancel.show();
		$btnSubmit.show();
		$editor.show();

		$checkboxes = $editor.find('input[type=checkbox], input[type=radio]');
		$checkboxes.each(function () {
			$(this).data('original-value', this.checked);
		});

		$editor.off('click');

		// Handle cancel button.
		$btnCancel.off('click').on('click', function () {
			$checkboxes.each(function () {
				this.checked = $(this).data('original-value');
			});
			$editor.hide();
			$btnSubmit.hide();
			$btnCancel.hide();
			$btnEdit.show();
			$text.show();
		});

		// Handle submit button.
		$btnSubmit.off('click').on(
			'click',
			$.proxy(function (e) {
				var value = $checkboxes
					.filter(function () {
						return this.checked;
					})
					.map(function () {
						return +this.value;
					});
				value = Array.prototype.slice.call(value);
				this.save_cats(group_id, value, e.currentTarget);
			}, this)
		);

		// handle checkbox click
		$checkboxes.on('click', function (e) {
			var $submit = $editor.find('.ps-js-submit'),
				$checked = $checkboxes.filter(':checked'),
				$unchecked = $checkboxes.not($checked);

			if ('checkbox' === e.target.type) {
				if ($checked.length >= MAX_CATEGORIES) {
					$unchecked.prop('disabled', true);
					$unchecked.parent().css('opacity', 0.3);
				} else {
					$unchecked.prop('disabled', false);
					$unchecked.parent().css('opacity', '');
				}
			}

			if ($checked.length >= 1) {
				$submit.removeAttr('disabled');
			} else {
				$submit.attr('disabled', 'disabled');
			}
		});

		$checkboxes.first().triggerHandler('click');
	};

	/**
	 * Save group categories.
	 *
	 * @memberof PsGroup
	 * @param {number} group_id
	 * @param {number[]} category_id
	 * @param {HTMLElement} elem
	 */
	PsGroup.prototype.save_cats = function (group_id, category_id, elem) {
		var flag = 'save_cats',
			$loading;

		if (this.ajax[flag]) {
			return;
		}

		this.ajax[flag] = true;
		$loading = $(elem).find('.ps-js-loading');
		$loading.show();

		peepso.postJson(
			'groupajax.set_group_categories',
			{
				group_id: group_id,
				category_id: category_id,
				_wpnonce: peepsogroupsdata.nonce_set_group_categories
			},
			$.proxy(function (json) {
				var $ct = $(elem).closest('.ps-js-group-cat'),
					$text = $ct.find('.ps-js-group-cat-text'),
					$editor = $ct.find('.ps-js-group-cat-editor'),
					$btnEdit = $ct.find('.ps-js-btn-edit'),
					$btnCancel = $ct.find('.ps-js-cancel'),
					$btnSubmit = $ct.find('.ps-js-submit'),
					$checkboxes = $editor.find('input[type=checkbox], input[type=radio]'),
					html = [];

				this.ajax[flag] = false;
				$loading.hide();

				if (json.success) {
					$checkboxes.each(function () {
						this.checked = category_id.indexOf(+this.value) >= 0;
						if (this.checked) {
							html.push(
								[
									'<a href="',
									peepsogroupsdata.group_url.replace(
										'##category_id##',
										this.value
									),
									'">',
									$(this).next('label').text(),
									'</a>'
								].join('')
							);
						}
					});
					$editor.off('click').hide();
					$text.html(html.join('')).show();
					$btnSubmit.hide();
					$btnCancel.hide();
					$btnEdit.show();
				} else if (json.errors) {
					psmessage.show('', json.errors[0]);
				}
			}, this)
		);
	};

	/**
	 * Show form for edit group description.
	 *
	 * @memberof PsGroup
	 * @param {string} method
	 * @param {object} data
	 */
	PsGroup.prototype.edit_desc = function (group_id, elem) {
		var $ct = $(elem).closest('.ps-js-group-desc'),
			$text = $ct.find('.ps-js-group-desc-text'),
			$placeholder = $ct.find('.ps-js-group-desc-placeholder'),
			$editor = $ct.find('.ps-js-group-desc-editor'),
			$btnEdit = $ct.find('.ps-js-btn-edit'),
			$btnCancel = $ct.find('.ps-js-cancel'),
			$btnSubmit = $ct.find('.ps-js-submit'),
			$textarea,
			value;

		if ($editor.is(':visible')) {
			return;
		}

		// Show editor.
		$text.hide();
		$placeholder.hide();
		$btnEdit.hide();
		$btnCancel.show();
		$btnSubmit.show();
		$editor.show();

		$submit = $editor.find('.ps-js-submit');
		$textarea = $editor.find('textarea');
		$textarea.data('original-value', (value = $textarea.val())); // save original value
		$textarea.focus().val('').val(value); // focus

		$editor.off('click input');

		// Handle cancel button.
		$btnCancel.off('click').on('click', function () {
			$textarea.val(value);
			$editor.hide();
			$btnSubmit.hide();
			$btnCancel.hide();
			$btnEdit.show();

			if ($.trim(value)) {
				$text.show();
				$placeholder.hide();
			} else {
				$text.hide();
				$placeholder.show();
			}
		});

		// Handle submit button.
		$btnSubmit.off('click').on(
			'click',
			$.proxy(function (e) {
				this.save_desc(group_id, $textarea.val(), e.currentTarget);
			}, this)
		);

		// handle text input
		$editor.on('input', 'textarea', function (e) {
			var $input = $(e.currentTarget),
				$limit = $input.closest('.ps-js-group-desc').find('.ps-js-limit'),
				maxLength = +$input.data('maxlength'),
				val = $input.val();

			if (maxLength && val.length > maxLength) {
				val = val.substr(0, maxLength);
				$input.val(val);
			}

			$limit.html(maxLength - val.length);

			if ($.trim(val)) {
				$submit.removeAttr('disabled');
			} else {
				$submit.attr('disabled', 'disabled');
			}
		});
		$textarea.trigger('input');
	};

	/**
	 * Save album description.
	 *
	 * @memberof PsGroup
	 * @param {string} method
	 * @param {object} data
	 */
	PsGroup.prototype.save_desc = function (group_id, description, elem) {
		var flag = 'save_desc',
			$loading;

		if (this.ajax[flag]) {
			return;
		}

		this.ajax[flag] = true;
		description = $.trim(description || '');
		$loading = $(elem).find('.ps-js-loading');
		$loading.show();

		peepso.postJson(
			'groupajax.set_group_description',
			{
				group_id: group_id,
				description: description,
				_wpnonce: peepsogroupsdata.nonce_set_group_description
			},
			$.proxy(function (json) {
				var $ct = $(elem).closest('.ps-js-group-desc'),
					$text = $ct.find('.ps-js-group-desc-text'),
					$placeholder = $ct.find('.ps-js-group-desc-placeholder'),
					$editor = $ct.find('.ps-js-group-desc-editor'),
					$btnEdit = $ct.find('.ps-js-btn-edit'),
					$btnCancel = $ct.find('.ps-js-cancel'),
					$btnSubmit = $ct.find('.ps-js-submit'),
					$textarea = $editor.find('textarea'),
					$focusDesc = $('.ps-js-focus-desc');

				this.ajax[flag] = false;
				$loading.hide();

				if (json.success) {
					$textarea.val(description);
					$editor.off('click').hide();
					$btnSubmit.hide();
					$btnCancel.hide();
					$btnEdit.show();
					description = _.escape($.trim(description));

					// respect new line
					description = description.replace(/(?:\r\n|\r|\n)/g, '<br />');

					// Handle markdown parsing if necessary.
					var $markdown = $text.find('.peepso-markdown'),
						hasDescription = description,
						parsedDescription = description;

					if ($markdown.length) {
						parsedDescription = peepso.observer.applyFilters(
							'peepso_parse_content',
							'<div class="peepso-markdown">' + parsedDescription + '</div>'
						);
					}

					if (hasDescription) {
						$text.html(parsedDescription).show();
						$placeholder.hide();
						$focusDesc.html(parsedDescription);
					} else {
						$text.text(parsedDescription).hide();
						$placeholder.show();
						$focusDesc.text(parsedDescription);
					}
				} else if (json.errors) {
					psmessage.show('', json.errors[0]);
				}
			}, this)
		);
	};

	/**
	 * @memberof PsGroup
	 * @param {string} method
	 * @param {object} data
	 */
	PsGroup.prototype._member_action_confirmation = function (confirm) {
		return $.Deferred(
			$.proxy(function (defer) {
				if (confirm) {
					pswindow.confirm(
						confirm,
						$.proxy(function () {
							pswindow.hide();
							defer.resolveWith(this);
						}, this),
						$.proxy(function () {
							defer.rejectWith(this);
						}, this)
					);
				} else {
					defer.resolveWith(this);
				}
			}, this)
		);
	};

	/**
	 * @memberof PsGroup
	 *
	 * @param {string} method
	 * @param {object} data
	 * @returns {JQueryDeferred}
	 */
	PsGroup.prototype.member_action = function (method, data) {
		return $.Deferred(
			function (defer) {
				if (!method) {
					defer.rejectWith(this);
					return;
				}

				if (-1 === method.indexOf('.')) {
					method = 'groupuserajax.' + method;
				}

				peepso.ajax
					.post(method, data, -1)
					.done(
						function (json) {
							if (json.data) {
								defer.resolveWith(this, [json.data]);
							} else {
								defer.rejectWith(this, [json.errors]);
							}
						}.bind(this)
					)
					.fail(
						function () {
							defer.rejectWith(this);
						}.bind(this)
					);
			}.bind(this)
		);
	};

	/**
	 * Initializes this instance's container and selector reference to a postbox instance.
	 */
	PsGroup.prototype.init = function () {
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

		// fix horizontal padding
		var that = this;
		this.$cover_image
			.one('load', function () {
				that.fix_horizontal_padding();
			})
			.each(function () {
				if (this.complete) {
					jQuery(this).trigger('load');
				}
			});
		jQuery(window).on(
			'resize.focus-image',
			jQuery.proxy(this.fix_horizontal_padding_debounced, this)
		);
	};

	/**
	 * Fix horizontal padding on lanscape image.
	 */
	PsGroup.prototype.fix_horizontal_padding = function () {
		var ctWidth, ctHeight, imgHeight;

		// reset
		this.$cover_image.css({
			height: 'auto',
			width: '100%',
			minWidth: '100%',
			maxWidth: '100%'
		});

		ctHeight = this.$cover_ct.width() * 0.375; // 0.375 is from css height percentage from its width;
		ctHeight = Math.max(ctHeight, this.$cover_ct.height());
		imgHeight = this.$cover_image.height();

		// horizontal
		if (imgHeight < ctHeight) {
			this.$cover_image.css({
				height: ctHeight,
				width: 'auto',
				minWidth: '100%',
				maxWidth: 'none'
			});
		}

		this.initial_cover_position = this.$cover_image.attr('style');
	};

	PsGroup.prototype.fix_horizontal_padding_debounced = _.debounce(function () {
		this.fix_horizontal_padding();
	}, 300);

	/**
	 * Shows avatar dialog to upload/change avatar
	 */
	PsGroup.prototype.show_avatar_dialog = function () {
		jQuery('.ps-js-error').html(''); // clear any remaining error messages
		var $dialog = jQuery('#dialog-upload-avatar');
		var title = $dialog.find('#dialog-upload-avatar-title').html();
		var content = $dialog.find('#dialog-upload-avatar-content').html();
		var actions = $dialog.find('.dialog-action').html();

		var inst = pswindow.show(title, content).set_actions(actions);
		var elem = inst.$container.find('.ps-dialog');

		elem.addClass('ps-dialog-wide');
		peepso.observer.addFilter(
			'pswindow_close',
			function () {
				elem.removeClass('ps-dialog-wide');
			},
			10,
			1
		);

		this.init_avatar_fileupload();

		jQuery('#ps-window').on('pswindow.hidden', function () {
			jQuery('.upload-avatar .fileupload:visible').psFileupload('destroy');
		});
	};

	/**
	 * Initializes avatar file upload
	 */
	PsGroup.prototype.init_avatar_fileupload = function () {
		var that = this;

		jQuery('.upload-avatar .fileupload:visible').psFileupload({
			formData: {
				group_id: peepsogroupsdata.group_id,
				_wpnonce: jQuery('#_covernonce').val()
			},
			dataType: 'json',
			url: peepsodata.ajaxurl_legacy + 'groupajax.avatar_upload?avatar',
			add: function (e, data) {
				var acceptFileTypes = /(\.|\/)(jpe?g|png|webp)$/i;
				if (data.files[0]['type'].length && !acceptFileTypes.test(data.files[0]['type'])) {
					var error_filetype = jQuery('#profile-avatar-error-filetype').text();
					jQuery('.ps-js-error').html(error_filetype);
				} else if (parseInt(data.files[0]['size']) > peepsodata.upload_size) {
					var error_filesize = jQuery('#profile-avatar-error-filesize').text();
					jQuery('.ps-js-error').html(error_filesize);
				} else {
					jQuery('#ps-window .ps-loading-image').show();
					jQuery(
						'#ps-window .show-avatar, #ps-window .show-thumbnail, #ps-window .upload-avatar'
					).hide();
					jQuery('.ps-js-error').hide();
					pswindow.refresh();
					data.submit();
				}
			},
			done: function (e, data) {
				var response = data.result;

				if (response.success) {
					var content_html = jQuery(
						'#dialog-upload-avatar-content',
						jQuery(response.data.html)
					);
					var actions = jQuery('#dialog-upload-avatar .dialog-action').html();
					var rand = '?' + Math.random();
					jQuery('.js-focus-avatar img', content_html).attr(
						'src',
						response.data.image_url + rand
					);
					jQuery('.imagePreview img', content_html).attr(
						'src',
						response.data.orig_image_url + rand
					);
					jQuery('.imagePreview', content_html).after(
						'<input type="hidden" name="is_tmp" value="1"/>'
					);
					jQuery('.ps-js-has-avatar', content_html).show();
					jQuery('.ps-js-no-avatar', content_html).hide();

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
					that.avatar_use_gravatar = false;
				} else {
					jQuery(
						'#ps-window .show-avatar, #ps-window .show-thumbnail, #ps-window .upload-avatar'
					).show();
					jQuery('.ps-js-error').html(response.errors).show();
					jQuery('#ps-window .ps-loading-image').hide();
					jQuery('#ps-window button[name=rep_submit]')
						.attr('disabled', 'disabled')
						.removeClass('ps-btn-primary');
					that.invalid_avatar_upload = true;
				}
			}
		});
	};

	/**
	 * Finalize avatar upload
	 */
	PsGroup.prototype.confirm_avatar = function (elem) {
		var fn, req;

		if (this.invalid_avatar_upload) {
			return;
		}

		// prevent repeated call
		fn = this.confirm_avatar;
		if (fn._loading) {
			return;
		}
		fn._loading = true;

		// disable button on loading
		if (elem) {
			elem = jQuery(elem);
			elem.attr('disabled', 'disabled');
		}

		req = {
			group_id: peepsogroupsdata.group_id,
			module_id: peepsogroupsdata.module_id,
			_wpnonce: jQuery('#_covernonce').val()
		};

		peepso.postJson('groupajax.avatar_confirm', req, function (json) {
			if (json && json.success) {
				window.location.reload();
				return;
			}

			fn._loading = false;
			if (elem) {
				elem.removeAttr('disabled');
			}
		});
	};

	/**
	 * Confirms remove avatar photo request
	 */
	PsGroup.prototype.confirm_remove_avatar = function () {
		var title = jQuery('#delete-confirmation #delete-title').html();
		var content = jQuery('#delete-confirmation #delete-content').html();

		pswindow.show(title, content);
	};

	/**
	 * Performs remove avatar photo operation
	 */
	PsGroup.prototype.remove_avatar = function (user_id) {
		var req = {
			group_id: peepsogroupsdata.group_id,
			user_id: user_id,
			_wpnonce: jQuery('#_covernonce').val()
		};
		peepso.postJson('groupajax.avatar_delete', req, function (json) {
			if (json.success) {
				window.location.reload();
			}
		});
	};

	// --------------------- COVER ----------------------- //

	/**
	 * Confirms remove cover photo request
	 */
	PsGroup.prototype.confirm_remove_cover_photo = function () {
		var title = jQuery('#delete-confirmation #delete-title').html();
		var content = jQuery('#delete-confirmation #delete-content').html();

		pswindow.show(title, content);
	};

	/**
	 * Performs remove cover photo operation
	 */
	PsGroup.prototype.remove_cover_photo = function (user_id) {
		var req = { group_id: peepsogroupsdata.group_id, _wpnonce: jQuery('#_covernonce').val() };
		peepso.postJson('groupajax.cover_delete', req, function (json) {
			if (json.success) {
				window.location.reload();
			}
		});
	};

	/**
	 * Applies jquery draggable and saves the dragged position to this.cover
	 */
	PsGroup.prototype.reposition_cover = function () {
		jQuery('.js-focus-gradient', '.js-focus-cover').hide();
		jQuery('.js-focus-change-cover > a', '.js-focus-cover').hide();
		jQuery('.reposition-cover-actions', '.js-focus-cover').show();
		jQuery('.js-focus-cover').addClass('ps-focus-cover-edit');

		var that = this;
		var g =
			jQuery('.js-focus-cover').height() -
			jQuery('img#' + peepsogroupsdata.group_id).height();
		var w =
			jQuery('.js-focus-cover').width() - jQuery('img#' + peepsogroupsdata.group_id).width();

		jQuery('img#' + peepsogroupsdata.group_id).draggable({
			cursor: 'move',
			drag: function (a, b) {
				b.position.top < g && (b.position.top = g),
					b.position.top > 0 && (b.position.top = 0),
					b.position.left < w && (b.position.left = w),
					b.position.left > 0 && (b.position.left = 0);
			},
			stop: function (a, c) {
				var d = jQuery('img#' + peepsogroupsdata.group_id),
					e = d.closest('.js-focus-cover'),
					x = (100 * c.position.top) / e.height();
				x = Math.round(1e4 * x) / 1e4;
				y = (100 * c.position.left) / e.width();
				y = Math.round(1e4 * y) / 1e4;

				that.cover.x_position_percent = x;
				that.cover.y_position_percent = y;
				d.css('top', x + '%');
				d.css('left', y + '%');
			}
		});
	};

	/**
	 * Performs when reposition cover is cancelled
	 */
	PsGroup.prototype.cancel_reposition_cover = function () {
		jQuery('.js-focus-gradient', '.js-focus-cover').show();
		jQuery('.js-focus-change-cover > a', '.js-focus-cover').show();
		jQuery('.reposition-cover-actions', '.js-focus-cover').hide();
		jQuery('.js-focus-cover').removeClass('ps-focus-cover-edit');

		jQuery('img#' + peepsogroupsdata.group_id).attr('style', this.initial_cover_position);
		jQuery('img#' + peepsogroupsdata.group_id).draggable('destroy');
	};

	/**
	 * Saves the cover images position after repositioning
	 */
	PsGroup.prototype.save_reposition_cover = function () {
		var req = {
			group_id: peepsogroupsdata.group_id,
			x: this.cover.x_position_percent,
			y: this.cover.y_position_percent,
			_wpnonce: jQuery('#_covernonce').val()
		};

		var that = this;

		jQuery('.reposition-cover-actions', '.js-focus-cover').hide();
		jQuery('.ps-reposition-loading', '.js-focus-cover').show();
		jQuery('.js-focus-cover').removeClass('ps-focus-cover-edit');

		peepso.postJson('groupajax.cover_reposition', req, function (json) {
			jQuery('.ps-reposition-loading', '.js-focus-cover').hide();
			that.initial_cover_position = jQuery('img#' + peepsogroupsdata.group_id).attr('style');
			that.cancel_reposition_cover();
		});
	};

	/**
	 * Shows cover dialog to change/upload cover content
	 */
	PsGroup.prototype.show_cover_dialog = function () {
		jQuery('.ps-js-error').html(''); // clear any remaining error messages
		var $dialog = jQuery('#dialog-upload-cover');
		var title = $dialog.find('#dialog-upload-cover-title').html();
		var content = $dialog.find('#dialog-upload-cover-content').html();

		pswindow.show(title, content);

		this.init_cover_fileupload();

		jQuery('#ps-window').on('pswindow.hidden', function () {
			jQuery('.upload-cover .fileupload:visible').psFileupload('destroy');
		});
	};

	/**
	 * Initializes cover file upload
	 */
	PsGroup.prototype.init_cover_fileupload = function () {
		var that = this;

		jQuery('.upload-cover .fileupload:visible').psFileupload({
			formData: {
				group_id: peepsogroupsdata.group_id,
				module_id: peepsogroupsdata.module_id,
				_wpnonce: jQuery('#_covernonce').val()
			},
			dataType: 'json',
			url: peepsodata.ajaxurl_legacy + 'groupajax.cover_upload?cover',
			add: function (e, data) {
				var acceptFileTypes = /(\.|\/)(jpe?g|png|webp)$/i;
				if (data.files[0]['type'].length && !acceptFileTypes.test(data.files[0]['type'])) {
					var error_filetype = jQuery('#profile-cover-error-filetype').text();
					jQuery('.ps-js-error').html(error_filetype);
				} else if (parseInt(data.files[0]['size']) > peepsodata.upload_size) {
					var error_filesize = jQuery('#profile-cover-error-filesize').text();
					jQuery('.ps-js-error').html(error_filesize);
				} else {
					jQuery('#ps-window .ps-loading-image').show();
					jQuery('#ps-window .upload-cover').hide();
					data.submit();
				}
			},
			done: function (e, data) {
				var response = data.result;
				jQuery('#ps-window .ps-loading-image').hide();
				jQuery('#ps-window .upload-cover').show();
				if (response.success) {
					jQuery('.cover-image')
						.attr('src', response.data.image_url + '?' + Math.random())
						.css('top', '0')
						.css('left', '0')
						.removeClass('default')
						.addClass('has-cover');

					pswindow.fade_out('slow');
					jQuery('#profile-reposition-cover').show();
					jQuery('#dialog-upload-cover-content').html(response.data.html);
					pswindow.set_content(
						jQuery('#dialog-upload-cover-content', response.data.html).html()
					);
					that.fix_horizontal_padding_debounced();
					that.init_cover_fileupload();
				} else {
					jQuery('.ps-js-error').html(response.errors);
				}
			}
		});
	};

	/**
	 * Show avatar dialog for group.
	 */
	PsGroup.prototype.showAvatarDialog = function () {
		var data;

		if (peepsogroupsdata.id) {
			if (!this.avatarDialog) {
				data = {
					id: +peepsogroupsdata.id,
					name: peepsogroupsdata.name,
					hasAvatar: +peepsogroupsdata.hasAvatar,
					imgAvatar: peepsogroupsdata.imgAvatar,
					imgOriginal: peepsogroupsdata.imgOriginal
				};
				if (!data.hasAvatar) {
					data.imgOriginal = '';
				}
				this.avatarDialog = new PsGroupAvatarDialog(data);
			}
			this.avatarDialog.show();
		}
	};

	/**
	 * Start editing group property.
	 * NOTE: As for now, we assume that all form inputs used in this function are combobox element. We
	 * need to update this function if there is another type of element used in the future.
	 * @param {HTMLElement} elem
	 * @param {number} groupId
	 * @param {string} propertyName
	 */
	PsGroup.prototype.edit_property = function (elem, groupId, propertyName) {
		var $ct = $(elem).closest('.ps-js-group-' + propertyName),
			$text = $ct.find('.ps-js-text'),
			$editor = $ct.find('.ps-js-editor'),
			$btnEdit = $ct.find('.ps-js-btn-edit'),
			$btnCancel = $ct.find('.ps-js-btn-cancel'),
			$btnSubmit = $ct.find('.ps-js-btn-submit'),
			$input = $editor.find('select[name="' + propertyName + '"]'),
			value = $input.val();

		// Stop here if field editor is already visible.
		if ($editor.is(':visible')) {
			return;
		}

		// Show editor.
		$text.hide();
		$btnEdit.hide();
		$btnCancel.show();
		$btnSubmit.show();
		$editor.show();

		// Stop here if field editor is already initialized.
		if ($input.data('psInitialized')) {
			return;
		}

		$input.data('psInitialized', 1);

		// Save initial input value for submit and cancel purpose.
		$input.data('initialValue', value);

		// Handle input value change.
		$input.off('change').on('change', function () {
			var $input = $(this),
				currentValue = $input.val(),
				initialValue = $input.data('initialValue');

			// Disable submit on empty or same value.
			if (!currentValue || currentValue === initialValue) {
				$btnSubmit.attr('disabled', 'disabled');
			} else {
				$btnSubmit.removeAttr('disabled');
			}
		});

		// Handle cancel button.
		$btnCancel.off('click').on('click', function () {
			$input.val($input.data('initialValue'));
			$editor.hide();
			$btnSubmit.hide();
			$btnCancel.hide();
			$btnEdit.show();
			$text.show();
		});

		// Handle submit button.
		$btnSubmit.off('click').on('click', function () {
			var $loading = $btnSubmit.find('img'),
				value = $input.val();

			if ($loading.is(':visible')) {
				return;
			}

			$loading.show();

			peepso.postJson(
				'groupajax.set_group_property',
				{
					group_id: groupId,
					property_name: propertyName,
					property_value: value,
					_wpnonce: peepsogroupsdata.nonce_set_group_property
				},
				function (json) {
					$loading.hide();

					if (json.success) {
						$input.data('initialValue', value);
						$text.html($input.children('option:selected').text());
					}

					$btnCancel.click();
				}
			);
		});
	};

	/**
	 * Start editing group custom input.
	 * NOTE: As for now, we assume that all form inputs used in this function are combobox element. We
	 * need to update this function if there is another type of element used in the future.
	 * @param {HTMLElement} elem
	 * @param {number} groupId
	 * @param {string} customName
	 */
	PsGroup.prototype.edit_custom_input = function (elem, groupId, customName) {
		var $ct = $(elem).closest(`.ps-js-group-${customName}`),
			$text = $ct.find('.ps-js-text'),
			$editor = $ct.find('.ps-js-editor'),
			$btnEdit = $ct.find('.ps-js-btn-edit'),
			$btnCancel = $ct.find('.ps-js-btn-cancel'),
			$btnSubmit = $ct.find('.ps-js-btn-submit'),
			$input = $editor.find(`[name="${customName}"]`),
			value = $input.val();

		// Stop here if field editor is already visible.
		if ($editor.is(':visible')) {
			return;
		}

		// Show editor.
		$text.hide();
		$btnEdit.hide();
		$btnCancel.show();
		$btnSubmit.show();
		$editor.show();

		// Stop here if field editor is already initialized.
		if ($input.data('psInitialized')) {
			return;
		}

		$input.data('psInitialized', 1);

		// Save initial input value for submit and cancel purpose.
		$input.data('initialValue', value);

		// Handle input value change.
		$input.off('change').on('change', function () {
			var $input = $(this),
				currentValue = $input.val(),
				initialValue = $input.data('initialValue');

			// Disable submit on empty or same value.
			if (!currentValue || currentValue === initialValue) {
				$btnSubmit.attr('disabled', 'disabled');
			} else {
				$btnSubmit.removeAttr('disabled');
			}
		});

		// Handle cancel button.
		$btnCancel.off('click').on('click', function () {
			$input.val($input.data('initialValue'));
			$editor.hide();
			$btnSubmit.hide();
			$btnCancel.hide();
			$btnEdit.show();
			$text.show();
		});

		// Handle submit button.
		$btnSubmit.off('click').on('click', function () {
			var $loading = $btnSubmit.find('img'),
				value = $input.val();

			if ($loading.is(':visible')) {
				return;
			}

			$loading.show();

			peepso.postJson(
				'groupajax.set_group_custom_input',
				{
					group_id: groupId,
					custom_name: customName,
					custom_value: value,
					_wpnonce: peepsogroupsdata.nonce_set_group_custom_input
				},
				function (json) {
					$loading.hide();

					if (json.success) {
						$input.data('initialValue', value);
						$text.html($input.children('option:selected').text());
					}

					$btnCancel.click();
				}
			);
		});
	};

	return PsGroup;
});
