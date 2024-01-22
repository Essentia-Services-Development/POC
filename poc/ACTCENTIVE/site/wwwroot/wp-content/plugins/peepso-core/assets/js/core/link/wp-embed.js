import { observer, modules } from 'peepso';

// Alter WPEmbed link handling to respect PeepSo link config.
setTimeout(function() {
	let wp = window.wp || {},
		supportedBrowser = false,
		receiveEmbedMessage;

	if (document.querySelector) {
		if (window.addEventListener) {
			// This feature will be used later to check if WPEmbed iframe
			// is inside PeepSo container.
			// https://developer.mozilla.org/en-US/docs/Web/API/Element/closest
			if (document.body.closest) {
				supportedBrowser = true;
			}
		}
	}

	// Skip if requirements are not met.
	if (!(supportedBrowser && wp.receiveEmbedMessage)) {
		return;
	}

	receiveEmbedMessage = function(e) {
		let { data } = e;

		if (data && ['height', 'link'].indexOf(data.message) > -1) {
			let iframes = document.querySelectorAll('iframe[data-secret="' + data.secret + '"]'),
				source,
				sourceURL,
				targetURL;

			for (let i = 0; i < iframes.length; i++) {
				if (e.source === iframes[i].contentWindow) {
					source = iframes[i];
					break;
				}
			}

			if (source) {
				if ('height' === data.message) {
					// Only handle iframe inside `ps-media-iframe` container.
					let wrapperClass = 'ps-media-iframe',
						wrapperEmbedClass = `${wrapperClass}--wpembed`,
						wrapper = source.closest(`.${wrapperClass}`);

					if (wrapper && wrapper.className.indexOf(wrapperEmbedClass) < 0) {
						wrapper.className += ` ${wrapperEmbedClass}`;
					}
				}

				if ('link' === data.message) {
					sourceURL = document.createElement('a');
					targetURL = document.createElement('a');
					sourceURL.href = source.getAttribute('src');
					targetURL.href = data.value;

					// Only continue if link hostname matches iframe's hostname.
					if (targetURL.host === sourceURL.host) {
						if (document.activeElement === source) {
							// Only handle links inside PeepSo container.
							if (source.closest('#peepso-wrap')) {
								let url = observer.applyFilters('url_filter', data.value);
								if (modules.url.getTarget(url, false)) {
									window.top.open(url);
								} else {
									window.top.location.href = url;
								}
								return;
							}
						}
					}
				}
			}
		}

		wp.receiveEmbedMessage(e);
	};

	window.removeEventListener('message', wp.receiveEmbedMessage, false);
	window.addEventListener('message', receiveEmbedMessage, false);
}, 1000);
