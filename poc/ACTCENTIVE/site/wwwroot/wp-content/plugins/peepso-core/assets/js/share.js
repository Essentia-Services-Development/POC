/*
 * Interactions for share dialog box
 * @package PeepSo
 * @author PeepSo
 */

function escapeRegExp(string) {
	return string.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, '\\$1');
}

function replaceAll(find, replace, str) {
	return str.replace(new RegExp(find, 'g'), replace);
}

function PsShare() {}

window.share = new PsShare();

PsShare.prototype.share_url = function (url) {
	url = encodeURIComponent(url);

	let $title = jQuery('#share-dialog-title').html();

	let $content = jQuery('#share-dialog-content').html();
	$content = replaceAll('--peepso-url--', url, $content);
	$content = jQuery($content);

	let $items = $content.filter('.ps-sharebox').find('a.ps-sharebox__item');

	// Open share link in a "popup" instead of opening a new tab/window.
	$items.not('.internal').on('click', function (e) {
		e.preventDefault();
		e.stopPropagation();
		let width = 550;
		let height = 400;
		let left = Math.max(0, (window.innerWidth - width) / 2 || 0);
		let top = Math.max(0, (window.innerHeight - height) / 2 || 0);
		let opts = [
			'toolbar=no',
			'location=no',
			'status=no',
			'menubar=no',
			'scrollbars=yes',
			'resizable=yes',
			`width=${width}`,
			`height=${height}`,
			`left=${left}`,
			`top=${top}`
		].join(',');

		window.open(this.href, 'targetWindow', opts);
		pswindow.hide();
	});

	$items.filter('.ps-js-copy-link').on('click', function (e) {
		e.preventDefault();
		e.stopPropagation();

		let url = decodeURIComponent(this.getAttribute('href'));
		peepso.util.copyToClipboard(url);

		this.setAttribute('data-tooltip', this.getAttribute('data-tooltip-success'));
	});

	pswindow.show($title, $content);

	// Prevent adblocker from hiding the links.
	$items.not('.internal').each(function () {
		let style = window.getComputedStyle(this);
		if ('none' === style.display) {
			let encodedURI = btoa(this.getAttribute('href'));
			this.setAttribute('onclick', "window.open(atob('" + encodedURI + "'))");
			this.setAttribute('href', 'javascript:');
			this.removeAttribute('target');
		}
	});

	return false;
};
