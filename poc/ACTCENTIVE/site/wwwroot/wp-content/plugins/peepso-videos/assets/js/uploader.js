import $ from 'jquery';
import EventEmitter from 'eventemitter3';

const URL = peepsovideosdata.upload_url;
const NONCE = peepsovideosdata.nonce;

export default class Uploader extends EventEmitter {
	constructor(elem, opts = {}) {
		let file = elem.files[0];

		super();

		this.data = new FormData();
		this.data.append('filedata', file);
		this.data.append('size', file.size);
		this.data.append('name', file.name);
		this.data.append('_wpnonce', NONCE);

		if ('object' === typeof opts.data) {
			for (let param in opts.data) {
				this.data.append(param, opts.data[param]);
			}
		}
	}

	/**
	 * Upload file asynchronously.
	 * @returns {Promise}
	 */
	upload() {
		return new Promise((resolve, reject) => {
			$.ajax({
				url: URL,
				data: this.data,
				processData: false,
				contentType: false,
				dataType: 'json',
				type: 'POST',
				xhr: () => {
					let xhr = null;
					if (window.ActiveXObject) {
						xhr = new window.ActiveXObject('Microsoft.XMLHTTP');
					} else {
						xhr = new window.XMLHttpRequest();
					}
					xhr.upload.addEventListener(
						'progress',
						e => {
							if (e.lengthComputable) {
								let percentComplete = +((e.loaded * 100) / e.total).toFixed(2);
								this.emit('progress', percentComplete);
							}
						},
						false
					);

					return xhr;
				}
			})
				.done(json => {
					if (json.success) {
						resolve(json.data);
					} else {
						reject(json);
					}
				})
				.fail((xhr, status) => {
					reject(status);
				});
		});
	}
}
