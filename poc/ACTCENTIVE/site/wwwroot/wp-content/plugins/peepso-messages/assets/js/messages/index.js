import $ from 'jquery';
import { throttle } from 'underscore';
import { observer } from 'peepso';
import MessageList from './list';

// Prevents chat windows from opening on the messages page.
observer.addFilter('chat_enabled', function (enabled) {
	if (document.querySelector('.ps-js-messages-list')) {
		enabled = false;
	}

	return enabled;
});

$(function () {
	let listElement = document.querySelector('.ps-js-messages-list');
	if (!listElement) {
		return;
	}

	let list = new MessageList({ el: listElement });

	// Attach "narrow" class to the messages container when necessary.
	let $container = $('.ps-js-messages');
	let narrowClass = 'ps-messages--narrow';
	let evtName = 'resize.ps-message-conversation';

	$(window)
		.off(evtName)
		.on(
			evtName,
			throttle(function () {
				$container.width() < 800
					? $container.addClass(narrowClass)
					: $container.removeClass(narrowClass);
			})
		)
		.triggerHandler(evtName);
});
