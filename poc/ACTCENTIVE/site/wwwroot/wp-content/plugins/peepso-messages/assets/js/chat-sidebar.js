import $ from 'jquery';
import _ from 'underscore';
import peepso from 'peepso';

const CSS_EXPANDED = 'ps-chat-sidebar-open';

/**
 * Chat sidebar class.
 * @class PsChatSidebar
 */
export default function PsChatSidebar() {
	this.ids = [];
	this.create();
}

peepso.npm.objectAssign(
	PsChatSidebar.prototype,
	peepso.npm.EventEmitter.prototype,
	/** @lends PsChatSidebar.prototype */ {
		/**
		 * Sidebar container template.
		 * @type {string}
		 */
		template: peepsochatdata.sidebarTemplate,

		/**
		 * Sidebar item template.
		 * @type {string}
		 */
		itemTemplate: peepsochatdata.sidebarItemTemplate,

		/**
		 * Languge translations.
		 * @type {Object.<string, string>}
		 */
		translations: peepsochatdata.translations,

		/**
		 * Initialize sidebar DOM.
		 */
		create: function () {
			this.$el = $(this.template).hide();
			this.$items = this.$el.find('.ps-chat-sidebar-items');
			this.$notif = this.$el.find('.ps-chat-sidebar-label .ps-chat-sidebar-notif');
			this.$label = this.$el.find('.ps-chat-sidebar-label').find('span');

			this.visible = false;

			this.$el.on('click', $.proxy(this.onToggle, this));
			this.$el.on('click', '.ps-chat-sidebar-item', $.proxy(this.onSelect, this));
			this.$el.on('click', '.ps-chat-close', $.proxy(this.onRemove, this));
		},

		/**
		 * Check whether sidebar is expanded.
		 *
		 * @returns {boolean}
		 */
		isExpanded() {
			return this.$el.hasClass(CSS_EXPANDED);
		},

		/**
		 * Expand sidebar.
		 */
		expand() {
			this.$el.addClass(CSS_EXPANDED);

			/**
			 * Sidebar expand event.
			 * @event PsChatSidebar#expand
			 */
			this.emit('expand');
		},

		/**
		 * Collapse sidebar.
		 */
		collapse() {
			this.$el.removeClass(CSS_EXPANDED);

			/**
			 * Sidebar collapse event.
			 * @event PsChatSidebar#collapse
			 */
			this.emit('collapse');
		},

		/**
		 * Toggle sidebar expanded state.
		 */
		toggle() {
			this.isExpanded() ? this.collapse() : this.expand();
		},

		/**
		 * Reset sidebar items based on supplied ids.
		 * @param {Array.<number>=} ids List of conversation ID.
		 * @param {Object.<number>=} updateIds List of conversation ID to be updated.
		 */
		reset: function (ids, updateIds) {
			ids || (ids = []);
			this.remove(_.difference(this.ids, ids));
			this.add(ids);
			this.updateNotification(updateIds);
		},

		/**
		 * Add sidebar items.
		 * @param {Array.<number>} ids List of conversation ID to be added.
		 * @param {boolean=} trigger Flag to trigger add event.
		 */
		add: function (ids, trigger) {
			var $item, id, index, i;
			if (ids && ids.length) {
				// add missing items
				for (i = 0; i < ids.length; i++) {
					id = +ids[i];
					index = this.ids.indexOf(id);
					if (index === -1) {
						$item = $(this.itemTemplate.replace(/\{id\}/g, id));
						$item.appendTo(this.$items);
						this.ids.push(id);
						this.update(id, $item);
					}
				}
				// fix wrong item position
				for (i = 0; i < ids.length; i++) {
					id = +ids[i];
					index = this.ids.indexOf(id);
					if (i !== index) {
						$item = this.$items.children('[data-id=' + id + ']');
						this.ids.splice(index, 1);
						this.ids.splice(i, 0, id);
						if (i === 0) {
							$item.prependTo(this.$items);
						} else {
							$item.insertBefore(this.$items.children().eq(i));
						}
					}
				}
				// show sidebar if not currently shown
				if (this.ids.length && !this.visible) {
					this.$el.show();
					this.visible = true;
				}
				// trigger remove event
				if (trigger) {
					/**
					 * Event fired when a new item added into stack.
					 * @event PsChatSidebar#added
					 */
					this.emit('add', ids);
				}
				// update sidebar counter
				this.updateCounter();
			}
		},

		/**
		 * Remove sidebar items.
		 * @param {Array.<number>} ids List of conversation ID to be removed.
		 * @param {boolean=} trigger Flag to trigger remove event.
		 */
		remove: function (ids, trigger) {
			var id, index, i;
			if (ids && ids.length) {
				// remove listed items
				for (i = 0; i < ids.length; i++) {
					id = +ids[i];
					index = this.ids.indexOf(id);
					if (index >= 0) {
						this.$items.children('[data-id=' + id + ']').remove();
						this.ids.splice(index, 1);
					}
				}
				// hide sidebar if not currently hidden
				if (!this.ids.length && this.visible) {
					this.$el.hide();
					this.visible = false;
				}
				// trigger remove event
				if (trigger) {
					/**
					 * Event fired when a existing item removed from stack.
					 * @event PsChatSidebar#removed
					 */
					this.emit('remove', ids);
				}
				// update sidebar counter
				this.updateCounter();
			}
		},

		/**
		 * Remove first sidebar item.
		 * @return {number} Removed conversation ID.
		 */
		removeFirst: function () {
			if (this.ids.length) {
				var id = this.ids[0];
				this.remove([id]);
				return id;
			}
		},

		/**
		 * Select specific sidebar item.
		 * @param {Number} id Conversation ID.
		 */
		select: function (id) {
			/**
			 * Event fired specific item is selected/clicked.
			 * @event PsChatSidebar#removed
			 */
			this.emit('select', +id);
		},

		/**
		 * Update sidebar item.
		 *
		 * @param {number} id Conversation ID.
		 * @param {jQuery} $item Conversation item to be updated.
		 */
		update(id, $item) {
			this.captions = this.captions || {};

			if (!$item) {
				$item = this.$items.children('[data-id=' + id + ']');
			}

			if ($item.length) {
				let $caption = $item.find('.ps-js-caption'),
					caption = this.captions[id];

				// Update label directly if it is already available.
				if (caption) {
					$caption.find('img').attr('src', caption.avatar);
					$caption.find('span').html(caption.name);
					return;
				}

				// Or wait, if label fetching is already scheduled.
				if ($caption.data('deferCaption')) {
					return;
				}

				// Otherwise, defer label fetching until sidebar is expanded
				// to prevent unnecessary ajax calls when sidebar is never expanded
				// by user.
				$caption.data('deferCaption', 1);
				const fetcher = () => {
					let transport = peepso.disableAuth().disableError(),
						action = 'messagesajax.get_messages_in_conversation',
						params = {
							msg_id: id,
							chat: 1,
							get_participants: 1,
							get_messages: 0
						};

					this.fetchQueue = this.fetchQueue || [];
					this.fetchQueueCounter = this.fetchQueueCounter || 0;
					this.fetchQueueExec =
						this.fetchQueueExec ||
						function () {
							if (this.fetchQueueCounter >= 5 || !this.fetchQueue.length) return;
							this.fetchQueueCounter++;

							let { transport, action, params, $caption } = this.fetchQueue.shift();
							transport.postJson(action, params, response => {
								if (response.success) {
									let users = response.data.users;
									if (users && users.length) {
										this.captions[id] = caption = {
											avatar: users.length === 1 ? users[0].avatar : '',
											name: this.formatParticipants(users)
										};

										// Remove unnecessary extra html markup.
										caption.name = $('<span/>').html(caption.name).text();

										$caption.find('a').attr('aria-label', caption.name);
										$caption.find('img').attr('src', caption.avatar);
										$caption.find('span').html(caption.name);
									}
								}
								this.fetchQueueCounter--;
								this.fetchQueueExec();
							});
						}.bind(this);

					this.fetchQueue.push({ transport, action, params, $caption });
					setTimeout(() => this.fetchQueueExec(), 500);
				};

				if (this.isExpanded()) {
					fetcher();
				} else {
					this.once('expand', () => {
						fetcher();
					});
				}
			}
		},

		/**
		 * Efficiently update sidebar counter.
		 * @function
		 */
		updateCounter: _.debounce(function () {
			this.counter || (this.counter = 0);
			if (this.ids.length !== this.counter) {
				this.counter = this.ids.length;
				this.$label.html(this.counter);
			}
		}, 400),

		/**
		 * Update sidebar notification if there is any update in one of the items.
		 * @function
		 * @param {Object.<number>=} ids List of conversation ID to be updated.
		 */
		updateNotification: _.debounce(function (ids) {
			var count = 0,
				changed = false,
				$el,
				$notif,
				id;

			this.notifState || (this.notifState = {});

			// update sidebar item notification
			for (id in ids) {
				if (this.ids.indexOf(+id) === -1) {
					continue;
				}
				if (ids[id] === this.notifState[id]) {
					continue;
				}
				changed = true;
				this.notifState[id] = ids[id];
				$el = this.$items.find('.ps-chat-sidebar-item-' + id);
				$notif = $el.find('.ps-chat-sidebar-notif');
				if (ids[id]) {
					$notif.show();
					count++;
				} else {
					$notif.hide();
				}
			}

			// update sidebar notification counter
			if (changed) {
				if (count) {
					this.$notif.html(count).show();
				} else {
					this.$notif.hide();
				}
			}
		}, 400),

		/**
		 * Participant names formatter.
		 * @param {Object[]} users
		 * @return {String} Formatted participant names.
		 */
		formatParticipants: function (users) {
			var str = '&nbsp;';
			if (users.length === 1) {
				str = users[0].name_full;
			} else if (users.length > 1) {
				str = [];
				for (var i = 0, len = Math.min(2, users.length - 1); i < len; i++) {
					str.push(users[i].name_first);
				}
				str = str.join(', ');
				if (users.length === 2) {
					str = this.translations.and.replace(
						/%s(.+)%s/,
						str + '$1' + users[users.length - 1].name_first
					);
				} else if (users.length === 3) {
					str = this.translations.and_x_other.replace('%s', str).replace('%d', 1);
				} else {
					str = this.translations.and_x_others
						.replace('%s', str)
						.replace('%d', users.length - 2);
				}
			}
			return str;
		},

		/**
		 * Event handler when one of sidebar is being collapsed/expanded by user.
		 * @private
		 * @param {Event} e Browser event.
		 */
		onToggle: function (e) {
			e.preventDefault();
			e.stopPropagation();
			this.toggle();
		},

		/**
		 * Event handler when one of sidebar item is being selected by user.
		 * @private
		 * @param {Event} e Browser event.
		 */
		onSelect: function (e) {
			e.preventDefault();
			e.stopPropagation();
			this.select($(e.currentTarget).data('id'));
		},

		/**
		 * Event handler when one of sidebar item is being removed by user.
		 * @private
		 * @param {Event} e Browser event.
		 */
		onRemove: function (e) {
			e.preventDefault();
			e.stopPropagation();
			this.remove([$(e.currentTarget).data('id')], true);
			this.updateCounter();
		}
	}
);
