import VideoAbstract from './abstract';
import Uploader from '../uploader';

/**
 * VideoFile class.
 * @class VideoFile
 */
export default class VideoFile extends VideoAbstract {

	/**
	 * Fetch video information.
	 * @return {Promise}
	 */
	fetch() {
		return new Promise(( resolve, reject ) => {
			if ( this.data ) {
				resolve( this.data );
			} else {
				this.validate().then(() => {
					this.upload().then(( data ) => {
						this.data = _.extend({}, data, {
							html: `<iframe src="${ data.link }embed" />`
						});
						resolve( this.data );
					}).catch(( error ) => {
						reject( error );
					});
				}).catch(( error ) => {
					reject( error );
				});
			}
		});
	}

	/**
	 * Validate video file to be uploaded.
	 * @returns {Promise}
	 */
	validate() {
		return new Promise(( resolve, reject ) => {
			let file = this.source.files[0],
				params = { type: file.type, size: parseInt( file.size ) };

			peepso.postJson( 'videosajax.validate_video_upload', params, ( json ) => {
				if ( json && json.success ) {
					resolve();
				} else {
					reject( json && json.errors && json.errors[0] );
				}
			});
		});
	}

	/**
	 * Upload video file.
	 * @returns {Promise}
	 */
	upload() {
		return new Promise(( resolve, reject ) => {
			let uploader = new Uploader( this.source );
			uploader.on( 'progress', ( percent ) => {
				this.emit( 'progress', percent );
			});
			uploader.upload().then(( data ) => {
				resolve( data );
			}).catch(( error ) => {
				reject( error );
			})
		});
	}

}
