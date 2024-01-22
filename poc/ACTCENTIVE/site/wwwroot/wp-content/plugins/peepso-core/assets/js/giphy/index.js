import $ from 'jquery';
import { observer } from 'peepso';

import './comment';
import './message';
import './postbox';

observer.addFilter(
	'human_friendly_extras',
	function (extras, content, root) {
		if (!content && root && !root.querySelector('.ps-js-post-header')) {
			var $giphy = $(root).find('.ps-comment-media .ps-js-giphy [data-preview]');
			if ($giphy.length) {
				extras.push($giphy.data('preview'));
			}
		}
		return extras;
	},
	20,
	3
);
