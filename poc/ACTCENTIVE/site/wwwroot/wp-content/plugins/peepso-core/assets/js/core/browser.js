/** @module browser */

import { memoize, debounce } from 'underscore';
import observer from './observer';
import hooks from './hooks';

const ua = navigator.userAgent;

/**
 * Check if the device is an iOS.
 *
 * @function
 * @returns {boolean}
 */
export const isIOS = memoize(() => {
	return /iphone|ipad|ipod/i.test(ua);
});

/**
 * Check if the device is an Android.
 *
 * @function
 * @returns {boolean}
 */
export const isAndroid = memoize(() => {
	return /android/i.test(ua);
});

/**
 * Check if the device is a mobile device.
 *
 * @function
 * @returns {boolean}
 */
export const isMobile = memoize(() => {
	return /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(ua);
});

/**
 * Check if the device has touch support.
 *
 * @function
 * @returns {boolean}
 */
export const isTouch = memoize(() => {
	return 'ontouchstart' in document.documentElement;
});

/**
 * Check whether the browser is active by utilizing Page Visibility API.
 *
 * @function
 * @returns {boolean}
 */
export const isHidden = () => {
	if (typeof document.hidden !== 'undefined') {
		return document.hidden;
	} else {
		return false;
	}
};

/**
 * Trigger event when the browser goes inactive and vice versa.
 */
document.addEventListener('visibilitychange', function() {
	if (isHidden()) {
		observer.doAction('browser.inactive');
	} else {
		observer.doAction('browser.active');
	}
});

/**
 * Window unload event handler.
 */
window.addEventListener('beforeunload', e => {
	if (observer.applyFilters('beforeunload', false)) {
		(e || window.event).returnValue = null;
		return null;
	}
});

/**
 * Window resize event handler.
 */
let width = window.innerWidth || document.documentElement.clientWidth;
let height = window.innerHeight || document.documentElement.clientHeight;
window.addEventListener(
	'resize',
	debounce(() => {
		let currWidth = window.innerWidth || document.documentElement.clientWidth;
		let currHeight = window.innerHeight || document.documentElement.clientHeight;
		if (currWidth !== width || currHeight !== height) {
			width = currWidth;
			height = currHeight;
			observer.doAction('browser.resize', { width, height });
			hooks.doAction('browser_resize', { width, height });
		}
	}, 1000)
);
