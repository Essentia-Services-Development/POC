import $ from 'jquery';
import { observer } from 'peepso';

$(function () {
	function initWidget(container) {
		let $container = $(container);

		// Initialize notification.
		let $notification = $container.find('.ps-js-widget-userbar-notifications');
		if ($notification.length && $notification.html().trim()) {
			observer.doAction('notification_start');
		}

		let $toggle = $container.find('.ps-js-widget-userbar-toggle');
		$toggle.on('click', function (e) {
			e.preventDefault();
			e.stopPropagation();

			let $toggle = $(this),
				$widget = $toggle.closest('.ps-js-widget-userbar');

			$widget.toggleClass('psw-userbar--open');
		});

		// Listen for changes in the notification counters.
		(function () {
			if ('function' !== typeof MutationObserver) {
				return;
			}

			var $notifications = $container.find('.ps-js-widget-userbar-notifications'),
				$notifCounters = $notifications.find('.ps-js-counter'),
				$counter = $container.find('.ps-js-notif-counter');

			if (!$notifCounters.length) {
				return;
			}

			var updaterTimer;
			var updater = function () {
				clearTimeout(updaterTimer);
				updaterTimer = setTimeout(function () {
					var sum = 0;
					$notifCounters.each(function () {
						sum += +this.innerText || 0;
					});

					$counter.html(sum || '');
				}, 1000);
			};

			updater();

			new MutationObserver(function (mutationsList) {
				for (var mutation of mutationsList) {
					if ('childList' === mutation.type) {
						if (mutation.target.className.match(/ps-js-counter/)) {
							updater();
						}
					}
				}
			}).observe($notifications[0], {
				childList: true,
				subtree: true,
				attributes: false,
				characterData: true
			});
		})();
	}

	let $widgets = $('.ps-js-widget-userbar');
	if ($widgets.length) {
		$widgets.each((index, widget) => initWidget(widget));
	}
});
