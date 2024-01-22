import peepso from 'peepso';
import * as mention from './mention';
import * as post from './post';
import * as request from './request';
import * as url from './url';
import * as user from './user';

peepso.modules = {
	mention,
	post,
	request,
	url,
	user
};
