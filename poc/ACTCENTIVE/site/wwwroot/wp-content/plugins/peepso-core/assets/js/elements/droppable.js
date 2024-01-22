import { observer } from 'peepso';

/**
 * Initialize droppable feature to an element.
 *
 * @param {HTMLElement} element
 * @param {Object} opts
 * @private
 */
function _droppable(element, opts = {}) {
	element.addEventListener('dragover', e => {
		e.preventDefault();
		e.dataTransfer.dropEffect = 'copy';
	});

	element.addEventListener('drop', e => {
		e.preventDefault();

		let files = e.dataTransfer.files;
		if (files && files.length) {
			if ('function' === typeof opts.dropped) {
				opts.dropped(files);
			}
		}
	});
}

/**
 * Initialize droppable feature to an element.
 *
 * @param {HTMLElement} element
 * @param {Object} opts
 */
function droppable(element, opts) {
	if (element instanceof Element) {
		_droppable(element, opts);
	}
}

export default droppable;
