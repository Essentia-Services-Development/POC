import objectAssign from 'object-assign';

import './peepso';

import Ajax from './ajax';
import ContentEditable from './contenteditable';

import hooks from './hooks';
import observer from './observer';
import * as browser from './browser';
import './link';
import util from './util';
import localStorage from './local-storage';

objectAssign(peepso, {
	objectAssign,
	Promise: window.Promise,
	ContentEditable,

	ajax: new Ajax(),
	browser,
	util,

	observer,
	hooks,

	localStorage,
	ls: localStorage // peepso.localStorage alias.
});

import '../npm-expanded';
import '../pswindow';
import '../peepso';

import dialog from './dialog';
import user from './user';
import post from './post';
import login from './login';
import sse from './sse';

objectAssign(peepso, {
	dialog,
	user,
	post,
	login,
	sse
});
