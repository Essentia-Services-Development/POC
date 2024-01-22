import { observer } from 'peepso';
import { file as fileData } from 'peepsodata';

import './postbox';
import './commentbox';
import './activitystream';
import './page-files';
import './messages';
import './widget';

/**
 * Check whether file upload warning should be shown.
 *
 * @param {string|null} warningText
 * @param {Object|Array} files
 * @return {string|null}
 */
function fileUploadWarning(warningText, files) {
	if (!(files instanceof Array)) {
		files = [files];
	}

	for (let i = 0; i < files.length; i++) {
		let ext = files[i].name.toLowerCase().match(/\.([a-z]+)$/i);
		if (ext && -1 === fileData.uploadFileTypes.indexOf(ext[1])) {
			return fileData.texts.fileTypeWarning;
		}
	}

	let totalSize = files.map(file => file.size).reduce((prev, curr) => prev + curr);

	// Validate file size.
	if (+fileData.maxUploadSize && totalSize > +fileData.maxUploadSize * 1048576) {
		return fileData.texts.maxUploadSizeWarning;
	}

	// Validate user space limit.
	if (+fileData.maxUserSpace && +fileData.currentUserSpace >= 0) {
		if (totalSize > +fileData.maxUserSpace - +fileData.currentUserSpace) {
			return fileData.texts.maxUserSpaceWarning;
		}
	}

	// Validate daily upload limit.
	if (+fileData.maxDailyUpload && +fileData.currentDailyUpload >= 0) {
		if (files.length > +fileData.maxDailyUpload - +fileData.currentDailyUpload) {
			return fileData.texts.maxDailyUploadWarning;
		}
	}

	// Validate max upload limit.
	if (+fileData.maxUpload && +fileData.currentUpload >= 0) {
		if (files.length > +fileData.maxUpload - +fileData.currentUpload) {
			return fileData.texts.maxUploadWarning;
		}
	}

	return warningText;
}

/**
 * Increare the number of uploaded file to reduce current user's file upload quota.
 *
 * @param {Object|Array} files
 */
function fileUploadAdded(files) {
	if (!(files instanceof Array)) {
		files = [files];
	}

	let totalSize = files.map(file => file.size).reduce((prev, curr) => prev + curr);

	if (+fileData.currentUserSpace >= 0) {
		fileData.currentUserSpace = +fileData.currentUserSpace + totalSize;
	}

	if (+fileData.currentDailyUpload >= 0) {
		fileData.currentDailyUpload += files.length;
	}

	if (+fileData.currentUpload >= 0) {
		fileData.currentUpload += files.length;
	}
}

observer.addFilter('file_upload_warning', fileUploadWarning, 10, 2);
observer.addAction('file_upload_added', fileUploadAdded, 10, 1);
