class ContentEditable {
	/**
	 * Initialize content editable.
	 *
	 * @param {Element} elem
	 * @param {Object} [opts]
	 */
	constructor(elem, opts = {}) {
		this.elem = elem;
		this.opts = opts;

		this.elem.addEventListener('keydown', e => this.onKeydown(e));
		this.elem.addEventListener('keyup', e => this.onKeyup(e));
		this.elem.addEventListener('input', e => this.onInput(e));
		this.elem.addEventListener('paste', e => this.onPaste(e));

		this.normalize();
	}

	/**
	 * Get or set the contenteditable value.
	 *
	 * @param {string} [text]
	 * @returns {string}
	 */
	value(text) {
		if ('undefined' === typeof text) {
			text = this.elem.innerHTML;
			text = text.replace(/<span[^>]+data-value=(['"])([\s\S]*?)\1[\s\S]*?<\/span>/gi, '$2');
			text = text.replace(/<br>/gi, '\n');
			text = text.replace(/&nbsp;/gi, ' ');

			return text.trim();
		}

		let html = text;

		html = html.replace(/(\r\n|\r|\n)/g, '<br>');
		html = html.replace(/[ ]{2}/g, '&nbsp; ');
		this.elem.innerHTML = html;
		this.normalize();
	}

	/**
	 * Normalize content.
	 *
	 * @param {Event} [srcEvent] Source event that triggers this function.
	 */
	normalize(srcEvent) {
		// Fix empty content still has `<br>`.
		if (!this.elem.innerText.trim()) {
			this.elem.innerHTML = '';
			this.elem.focus();
			return;
		}

		this.saveCaretPosition();

		// Get the last element node or non-empty text node.
		let last = this.elem.lastChild;
		while (last && last.nodeType === Node.TEXT_NODE && !last.textContent.trim()) {
			last = last.previousSibling;
		}

		// Make sure the trailing linebreak is always present.
		if (last && last.tagName !== 'BR') {
			this.elem.appendChild(document.createElement('br'));
		}

		// Merge consecutive text nodes.
		[...this.elem.childNodes].forEach(node => {
			if (node.nodeType === Node.TEXT_NODE) {
				let prev = node.previousSibling;
				if (prev && prev.nodeType === Node.TEXT_NODE) {
					prev.textContent += node.textContent;
					node.remove();
				}
			}
		});

		// Call the content transformation function if set.
		if ('function' === typeof this.opts.transform) {
			this.opts.transform(this.elem, this);
		}

		this.restoreCaretPosition();
	}

	/**
	 * Handle keydown event.
	 *
	 * @param {Event} e
	 */
	onKeydown(e) {
		if (this.disableInput && e.keyCode === 13) {
			e.preventDefault();
			e.stopPropagation();
			return;
		}

		// Prevents default behavior that separates every line inside a block container (<div> or <p>).
		// This method will intercept and use the <br> tag instead.
		if (13 === e.keyCode) {
			e.preventDefault();

			let selection = window.getSelection();
			if (selection.rangeCount) {
				let range = selection.getRangeAt(0);
				let br = document.createElement('br');

				range.insertNode(br);

				range = document.createRange();
				range.setStartAfter(br);
				range.collapse(true);
				selection.removeAllRanges();
				selection.addRange(range);

				let ancestor = range.commonAncestorContainer;
				if (ancestor.nodeType === Node.TEXT_NODE) {
					ancestor = ancestor.parentNode;
				}

				// Prevents <br> tag to be added to the child elements.
				if (ancestor !== this.elem) {
					let nextNodes = [];
					let node = br;
					while (node) {
						nextNodes.push(node);
						node = node.nextSibling;
					}

					// Merge text if necessary.
					let ancestorSibling = ancestor.nextSibling;
					if (ancestorSibling && ancestorSibling.nodeType === Node.TEXT_NODE) {
						let lastNode = nextNodes[nextNodes.length - 1];
						if (lastNode.nodeType === Node.TEXT_NODE) {
							ancestorSibling.textContent =
								lastNode.textContent + ancestorSibling.textContent;
							nextNodes.pop();
							lastNode.remove();
						}
					}

					// Moves remaining nodes after the <br>.
					ancestor.after.apply(ancestor, nextNodes);
					range.setStartAfter(br);
					range.collapse();
				}

				requestAnimationFrame(() => {
					this.normalize(e);

					if ('function' === typeof this.opts.onChange) {
						this.opts.onChange(this);
					}
				});
			}
		}
	}

	/**
	 * Handle keyup event.
	 *
	 * @param {Event} e
	 */
	onKeyup(e) {
		if (this.disableInput && e.keyCode === 13) {
			e.preventDefault();
			e.stopPropagation();
			return;
		}
	}

	/**
	 * Handle input event.
	 *
	 * @param {Event} e
	 */
	onInput(e) {
		requestAnimationFrame(() => {
			this.normalize(e);

			if ('function' === typeof this.opts.onChange) {
				this.opts.onChange(this);
			}
		});
	}

	/**
	 * Handle paste event.
	 *
	 * @param {Event} e
	 */
	onPaste(e) {
		let clipboardData = e.clipboardData || window.clipboardData;
		if (!clipboardData) {
			return false;
		}

		let selection = window.getSelection();
		if (!selection.rangeCount) {
			return false;
		}

		selection.deleteFromDocument();

		e.preventDefault();

		let text = clipboardData.getData('text');
		let html = text.replace(/(\r\n|\r|\n)/g, '<br>');
		let replacement = document.createElement('div');
		let range = selection.getRangeAt(0);

		// Insert clipboard content into contenteditable at caret.
		replacement.innerHTML = html;
		[...replacement.childNodes].forEach(node => {
			range.insertNode(node);
			range.setStartAfter(node);
			range.collapse(true);
		});

		requestAnimationFrame(() => {
			this.normalize(e);

			if ('function' === typeof this.opts.onChange) {
				this.opts.onChange(this);
			}
		});
	}

	/**
	 * Use non-printable "&zwj;" characters as a marker.
	 *
	 * @see {@link https://en.wikipedia.org/wiki/Zero-width_joiner}
	 * @returns {string}
	 */
	caretMarker() {
		return '\u200D\u200D';
	}

	/**
	 * Inserts marker at the caret position.
	 */
	saveCaretPosition() {
		let selection = window.getSelection();
		if (!selection.rangeCount) {
			return false;
		}

		let marker = this.caretMarker();
		let range = selection.getRangeAt(0);
		let container = range.startContainer;

		if (container.nodeType === Node.ELEMENT_NODE) {
			range.collapse(true);
			range.insertNode(document.createTextNode(marker));
		} else if (container.nodeType === Node.TEXT_NODE) {
			let text = container.textContent;
			let offset = range.startOffset;

			text = `${text.substring(0, offset)}${marker}${text.substring(offset)}`;
			container.textContent = text;
		}
	}

	/**
	 * Moves caret position to the caret marker.
	 */
	restoreCaretPosition() {
		let marker = this.caretMarker();

		// Get the text node that contains marker.
		let container = (function findMarker(elem) {
			let container;

			for (let i = 0, nodes = [...elem.childNodes]; i < nodes.length; i++) {
				if (nodes[i].nodeType === Node.ELEMENT_NODE) {
					container = findMarker(nodes[i]);
					if (container) {
						break;
					}
				} else if (nodes[i].nodeType === Node.TEXT_NODE) {
					if (nodes[i].textContent.match(marker)) {
						container = nodes[i];
						break;
					}
				}
			}

			return container;
		})(this.elem);

		if (container) {
			let text = container.textContent;
			let offset = text.indexOf(marker);

			text = text.replace(marker, '');
			container.textContent = text;

			// Update caret position.
			let selection = window.getSelection();
			let range = document.createRange();
			range.setStart(container, offset);
			range.collapse(true);
			selection.removeAllRanges();
			selection.addRange(range);
		}
	}
}

export default ContentEditable;
