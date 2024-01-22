import peepso from 'peepso';
import VideoAbstract from './abstract';

/**
 * VideoEmbed class.
 * @class VideoEmbed
 */
export default class VideoEmbed extends VideoAbstract {

	/**
	 * Fetch video information.
	 * @return {Promise}
	 */
	fetch() {
		return new Promise(( resolve, reject ) => {
			if ( this.data ) {
				resolve( this.data );
			} else {
				this.xhr = peepso.postJson( 'videosajax.get_preview', { url: this.source }, ( json ) => {
					if ( json.success ) {
						this.data = json.data;
						resolve( this.data );
					} else {
						reject( json && json.errors && json.errors[0] );
					}
				}).ret;
			}
		});
	}

	/**
	 * Abort fetching process.
	 */
	fetchAbort() {
		if ( this.xhr ) {
			this.xhr.abort();
			this.xhr = undefined;
		}
	}

}
