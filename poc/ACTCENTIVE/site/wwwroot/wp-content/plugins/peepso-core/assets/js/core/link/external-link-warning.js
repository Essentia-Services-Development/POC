import $ from 'jquery';
import _ from 'underscore';
import peepsodata from 'peepsodata';

const EXTERNAL_WARNING = +peepsodata.external_link_warning;
const EXTERNAL_WARNING_PAGE = peepsodata.external_link_warning_page;
const EXTERNAL_WHITELIST = peepsodata.external_link_whitelist || '';

$(function() {
	if (!EXTERNAL_WARNING) {
		return;
	}

	let externalWhitelist = EXTERNAL_WHITELIST;

	// Add own domain to whitelist.
	externalWhitelist = `${peepsodata.home_url}\n${externalWhitelist}`;

	// Map whitelist.
	externalWhitelist = externalWhitelist.split(/\s+/);
	externalWhitelist = _.reduce(
		externalWhitelist,
		function(memo, item) {
			item = item.trim();

			// Removes scheme and "www" string.
			item = item.replace(/^https?:\/\/(www\.)?/i, '').replace(/\/+$/, '');

			if (item && memo.indexOf(item) === -1) {
				memo.push(item);
			}
			return memo;
		},
		[]
	);

	/**
	 * Filter URL to respect external link configuration.
	 *
	 * @param {string} url
	 * @returns {string}
	 */
	function filterUrl(url) {
		// Only replace absolute URLs.
		if (url && url.match(/^https?:\/\//i)) {
			// Skip whitelisted domains.
			let skip = _.find(externalWhitelist, function(domain) {
				return url.match(new RegExp('^https?://(www\\.)?' + domain));
			});

			if (!skip) {
				url = `${EXTERNAL_WARNING_PAGE}?url=${encodeURIComponent(url)}`;
			}
		}

		return url;
	}

	/**
	 * Replaces external links found in an element.
	 *
	 * @param {JQuery} $elem
	 */
	function replaceLinks($elem) {
		$elem.find('a').each(function() {
			var $link = $(this),
				href = $link.attr('href'),
				newHref;

			// Skip link with `no-hijack` attribute.
			if (+$link.data('noHijack')) {
				return true;
			}

			newHref = filterUrl(href);
			if (newHref !== href) {
				$link.attr('href', newHref);
			}
		});
	}

	/**
	 * Hijacks external links.
	 */
	function hijackLinks() {
		let $elem = $('#peepso-wrap, .ps-widget--external, .ps-lightbox-wrapper');
		replaceLinks($elem);
	}

	// Hijack external links on content update.
	$(document).on(
		[
			'ps_activitystream_loaded',
			'ps_activitystream_append',
			'ps_comment_added',
			'ps_comment_aftersave',
			'ps_lightbox_navigate'
		].join(' '),
		hijackLinks
	);

	// Hijack external links on profile field update.
	peepso.observer.addAction('profile_field_updated', function() {
		hijackLinks();
	});

	// Hijack external links callable action.
	peepso.observer.addAction(
		'peepso_external_link',
		function($elem) {
			replaceLinks($elem);
		},
		10,
		1
	);

	// URL filter handler.
	peepso.observer.addFilter('url_filter', filterUrl, 10, 1);

	// Hijack currently available external links.
	hijackLinks();
});
