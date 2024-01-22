import $ from 'jquery';
import { observer } from 'peepso';
import peepsodata from 'peepsodata';

const TRIM_URL = +peepsodata.trim_url;
const TRIM_URL_HTTPS = +peepsodata.trim_url_https;

const trimUrl = html => {
	return html.replace(/(<a[^>]+href[^>]+>)([^<]+)(<\/a>)/gi, function(link, start, text, stop) {
		if (text.match(/^https?:\/\//i)) {
			if (TRIM_URL) {
				text = text.replace(/^(https?:\/\/[^\/]+).*$/i, '$1');
			}
			if (TRIM_URL_HTTPS) {
				text = text.replace(/^https?:\/\//i, '');
			}
			link = start + text + stop;
		}
		return link;
	});
};

$(function() {
	if (!(TRIM_URL || TRIM_URL_HTTPS)) {
		return;
	}

	observer.addFilter('peepso_activity_content', trimUrl, 10, 1);
});
