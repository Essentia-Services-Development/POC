import $ from 'jquery';

const LOADING_IMAGE = peepsodata.loading_gif;

export default class NotificationPopover {
	/**
	 * Constructor.
	 * @param {Element} elem
	 */
	constructor(elem) {
		this.guid = _.uniqueId('psNotificationPopover_');

		this.$elem = $(elem);
		this.$counter = this.$elem.find('.ps-js-counter');
		this.$popover = null;
		this.$popoverHeader = null;
		this.$popoverBody = null;
		this.$popoverFooter = null;
		this.$popoverLoading = null;

		this.$elem.on('click', e => {
			this.toggle(e);
		});

		this.render();
	}

	/**
	 * Create popover element.
	 */
	render() {
		if (!this.$popover) {
			this.$popoverHeader = this.createHeader();
			this.$popoverBody = this.createBody();
			this.$popoverFooter = this.createFooter();

			this.$popoverLoading = $('<div class="ps-loading ps-loading--notifs" />')
				.append('<img src="' + LOADING_IMAGE + '" />')
				.appendTo(this.$popoverBody);

			this.$popover = $('<div class="ps-notif__box" />')
				.append(this.$popoverHeader)
				.append(this.$popoverBody)
				.append(this.$popoverFooter)
				.hide();

			this.$elem.append(this.$popover);
		}
	}

	/**
	 * Create popover header.
	 * @return {jQuery}
	 */
	createHeader() {
		return $();
	}

	/**
	 * Create popover body.
	 * @return {jQuery}
	 */
	createBody() {
		return $('<div class="ps-notifications ps-notifications--notifs" />')
			.css({ maxHeight: '40vh', overflow: 'auto' })
			.on('wheel', e => {
				this.disableParentScroll(e);
			})
			.on('scroll', e => {
				this.handleScroll(e);
			});
	}

	/**
	 * Create popover footer.
	 * @return {jQuery}
	 */
	createFooter() {
		return $();
	}

	/**
	 * Toggle popover visibility.
	 * TODO: Prevent rapid click.
	 * @param {Event} [e]
	 */
	toggle(e) {
		let evtName = 'click.' + this.guid;

		if (e) {
			e.preventDefault();
		}

		// In case of lazy popover creation.
		if (!this.$popover) {
			this.render();
		}

		// Hide the popover if its currently visible.
		if (this.$popover.is(':visible')) {
			$(document).off(evtName);
			this.$popover.stop().slideUp('fast');
			return;
		}

		// Defer slideDown on next tick to prevent glitch.
		_.defer(() => {
			// Show popover if its currently hidden. Requires jquery-ui-position plugin.
			this.$popover.stop().slideDown({
				duration: 'fast',
				done: () => {
					// Hide popover when user clicks outside of the popover.
					$(document).one(evtName, e => {
						this.$popover.hide();
					});

					this.tryLoadNext();
				}
			});
		});
	}

	/**
	 * Try to load next notification items if condition is valid.
	 */
	tryLoadNext() {
		let div = this.$popoverBody[0],
			scrollTop = div.scrollTop,
			height = div.scrollHeight - div.clientHeight;

		if (Math.abs(scrollTop - height) <= 1) {
			this.loadNext();
		}
	}

	/**
	 * Load next notification items.
	 */
	loadNext() {}

	/**
	 * Disable parent scrolling when popover is scrolled.
	 * @param {Event} e
	 */
	disableParentScroll(e) {
		var el = e.currentTarget,
			scrollTop = el.scrollTop,
			delta = e.originalEvent.deltaY,
			height;

		if (scrollTop === 0 && delta < 0) {
			e.preventDefault();
			e.stopPropagation();
		} else {
			height = el.scrollHeight - el.clientHeight;
			if (Math.abs(scrollTop - height) <= 1 && delta > 0) {
				e.preventDefault();
				e.stopPropagation();
			}
		}
	}

	/**
	 * Handle scroll event.
	 */
	handleScroll() {
		this.tryLoadNext();
	}
}
