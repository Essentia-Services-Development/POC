import $ from 'jquery';
import { hooks, observer } from 'peepso';
import { currentuserid as USER_ID } from 'peepsodata';

$(function () {
	function initWidget(container) {
		let $container = $(container);

		// Initialize notification.
		let $notification = $container.find('.ps-js-widget-me-notifications');
		if ($notification.length) {
			observer.doAction('notification_start');
		}

		// Initialize cover image.
		let $cover = $container.find('.ps-js-widget-me-cover');
		if ($cover.length) {
			hooks.addAction('profile_cover_updated', 'widget_me', function (id, imageUrl) {
				if (+id === +USER_ID) {
					$cover.css('background-image', 'url(' + imageUrl + ')');
				}
			});
		}

		// Initialize avatar image.
		let $avatar = $container.find('.ps-js-widget-me-avatar');
		if ($avatar.length) {
			hooks.addAction('profile_avatar_updated', 'widget_me', function (id, imageUrl) {
				if (+id === +USER_ID) {
					$avatar.attr('src', imageUrl);
				}
			});
		}

		// Initialize profile completeness status.
		let $completeness = $container.find('.ps-js-widget-me-completeness');
		if ($completeness.length) {
			let $status = $completeness.find('.ps-js-status'),
				$progressbar = $completeness.find('.ps-js-progressbar');

			hooks.addAction('profile_completeness_updated', 'widget_me', function (id, data = {}) {
				if (+id === +USER_ID) {
					if ('undefined' !== typeof data.profile_completeness) {
						if (+data.profile_completeness >= 100) {
							$completeness.hide();
						} else {
							$completeness.show();
							$status.html(data.profile_completeness_message);
							$progressbar
								.children('span')
								.css({ width: `${+data.profile_completeness}%` });
						}
					}
				}
			});
		}

		// Handle community filter links.
		let $links = $container
			.find('.ps-js-widget-community-link')
			.filter((index, item) => item.href.match(/#(following|saved)/));

		$links.on('click', function () {
			let pageHref = window.location.href.replace(/#.*$/, '');
			let linkHref = this.href.replace(/#.*$/, '');

			if (pageHref === linkHref) {
				setTimeout(function () {
					window.location.reload();
				}, 1);
			}
		});

		// Fix notification popup position.
		// Execute on the next event loop after all notification popups are initialized.
		setTimeout(function () {
			let halfWidth = window.innerWidth / 2;
			// Flip popup alignment if the widget is positioned on the right half of the screen.
			if ($container.width() < halfWidth && $container.offset().left > halfWidth) {
				$container.find('.ps-notif__box').css({ left: 'unset', right: 1 });
			}
		}, 1);
	}

	let $widgets = $('.ps-js-widget-me');
	if ($widgets.length) {
		$widgets.each((index, widget) => initWidget(widget));
	}
});
