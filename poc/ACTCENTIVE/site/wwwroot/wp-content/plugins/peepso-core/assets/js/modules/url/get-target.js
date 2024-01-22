import { open_in_new_tab } from 'peepsodata';

const OPEN_IN_NEW_TAB = +open_in_new_tab;
const OWN_DOMAIN = window.location.href.replace(/^(https?:\/\/[^\/]+).*$/, '$1');

/**
 * Get the url target.
 *
 * @param {string} url
 * @param {boolean} fullAttr
 * @returns {string}
 */
export function getTarget(url = '', fullAttr = true) {
	// Relative URL should never be opened in a new tab.
	if (!url.match(/^https?:\/\//i)) {
		return '';
	}

	// Open all URLs in a new tab.
	if (1 === OPEN_IN_NEW_TAB) {
		return fullAttr ? ' target="_blank"' : '_blank';
	}

	// Open all external URLs in a new tab.
	if (2 === OPEN_IN_NEW_TAB) {
		if (0 !== url.indexOf(OWN_DOMAIN)) {
			return fullAttr ? ' target="_blank"' : '_blank';
		}
	}

	// Anything else should be opened in the same tab.
	return '';
}
