import { observer } from 'peepso';
import Videos from './videos';
import VideosPage from './page-videos';
import PostboxVideo from './postbox';
import PostboxAudio from './postbox-audio';
import './activitystream';
import './card';

observer.addFilter(
	'peepso_postbox_addons',
	addons => {
		// Make a wrapper object to be executed by main postbox script, so that
		// PostboxVideo class can have a clean initialization with 'new' operator.
		const wrapper = {
			init() {},
			set_postbox( $postbox ) {
				new PostboxVideo( $postbox );
				new PostboxAudio( $postbox );
			}
		};

		addons.push( wrapper );
		return addons;
	},
	10,
	1
);
