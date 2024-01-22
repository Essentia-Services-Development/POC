import EventEmitter from 'eventemitter3';

/**
 * VideoAbstract class.
 * @class VideoAbstract
 */
export default class VideoAbstract extends EventEmitter {

	/**
	 * VideoAbstract constructor.
	 * @constructor VideoAbstract
	 * @param {*} source
	 */
	constructor( source ) {
		super();
		this.source = source;
		this.data = null;
	}

	/**
	 * Fetch video information.
	 * @return {Promise}
	 */
	fetch() {
		throw new Error( 'Must be implemented by subclass!' );
	}

	/**
	 * Abort fetching process.
	 */
	fetchAbort() {
		throw new Error( 'Must be implemented by subclass!' );
	}

	/**
	 * Get html code for video.
	 * @returns {Promise}
	 */
	getHTML() {
		return new Promise(( resolve, reject ) => {
			this.fetch().then(( data ) => {
				resolve( data.html );
			}).catch(( error ) => {
				reject( error );
			});
		});
	}

	/**
	 * Get video data.
	 * @returns {Promise}
	 */
	getData() {
		return new Promise(( resolve, reject ) => {
			this.fetch().then(( data ) => {
				resolve( data );
			}).catch(( error ) => {
				reject( error );
			});
		});
	}

}
