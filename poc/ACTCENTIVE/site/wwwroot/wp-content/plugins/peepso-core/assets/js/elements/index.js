import $ from 'jquery';
import peepso from 'peepso';
import { auto_rtl } from 'peepsodata';
import droppable from './droppable';
import post from './post';
import hashtag from './hashtag';
import mention from './mention';
import permalink from './permalink';
import autoRtl from './auto-rtl';

peepso.elements = {
	droppable
};

$(function () {
	post.init();
	hashtag.init();
	mention.init();
	permalink.init();

	if (+auto_rtl) {
		autoRtl.init();
	}
});
