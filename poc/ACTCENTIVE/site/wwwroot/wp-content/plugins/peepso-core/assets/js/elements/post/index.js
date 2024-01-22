import readMore from './read-more';
import options from './options';
import save from './save';
import follow from './follow';
import track from './track';
import human from './human';
import linkTarget from './link-target';
import nsfw from './nsfw';

function init() {
	readMore.init();
	options.init();
	save.init();
	follow.init();
	track.init();
	human.init();
	linkTarget.init();
	nsfw.init();
}

export default { init };
