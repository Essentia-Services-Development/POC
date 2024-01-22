import $ from 'jquery';
import { observer } from 'peepso';

import './google-maps';
import './location';
import './postbox';

function capitalize(str) {
	str = str.split(' ');
	for (var i = 0; i < str.length; i++) {
		str[i] = str[i][0].toUpperCase() + str[i].slice(1);
	}
	return str.join(' ');
}

observer.addFilter(
	'human_friendly_extras',
	function(extras, content, root) {
		if (!content && root) {
			var $location = $(root).find('.ps-js-activity-extras [data-preview]');
			if ($location.length) {
				extras.push($location.data('preview'));
			}
		}
		return extras;
	},
	20,
	3
);
