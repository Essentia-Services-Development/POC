import EventEmitter from 'eventemitter3';
import VideoFile from './file';
import VideoEmbed from './embed';
import VideoFacebook from './facebook';

/**
 * Video class.
 * @class Video
 */
export default class Video extends EventEmitter {
	/**
	 * Video constructor.
	 * @constructor Video
	 * @param {*} source
	 */
	constructor(source) {
		super();

		this.source = source;
		this.object = null;

		// Video upload.
		if ('object' === typeof source && source.files) {
			this.object = new VideoFile(source);
			this.object.on('progress', percent => {
				this.emit('progress', percent);
			});
		}

		// Video embed.
		else if ('string' === typeof source && '' !== source) {
			if (false) {
				// TODO: Facebook video.
				this.object = new VideoFacebook(source);
			} else {
				this.object = new VideoEmbed(source);
			}
		}
	}

	/**
	 * Abort video fetching.
	 */
	fetchAbort() {
		return this.object.fetchAbort();
	}

	/**
	 * Get html code for video.
	 * @returns {Promise}
	 */
	getHTML() {
		return this.object.getHTML();
	}

	/**
	 * Get video data.
	 * @returns {Promise}
	 */
	getData() {
		return this.object.getData();
	}
}

/**
 * Check if video type is supported.
 *
 * @param {string} type
 * @returns {string}
 */
let video;
export const supportsType = type => {
	let formats = {
		ogg: 'video/ogg; codecs="theora"',
		h264: 'video/mp4; codecs="avc1.42E01E"',
		webm: 'video/webm; codecs="vp8, vorbis"',
		vp9: 'video/webm; codecs="vp9"',
		hls: 'application/x-mpegURL; codecs="avc1.42E01E"'
	};

	if (!video) {
		video = document.createElement('video');
	}

	if (video && typeof video.canPlayType === 'function') {
		return video.canPlayType(formats[type] || type);
	}

	return '';
};
