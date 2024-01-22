import $ from 'jquery';
import { throttle } from 'underscore';
import { ajax, dialog, hooks } from 'peepso';
import peepsodata, { currentuserid as USER_ID, profile as profileData } from 'peepsodata';

const Hammer = window.Hammer;

const UPLOAD_MAXSIZE = +peepsodata.upload_size;

const PROFILE_ID = +profileData.id;
const IMG_COVER_DEFAULT = profileData.img_cover_default;
const COVER_NONCE = profileData.cover_nonce;
const COVER_UPLOAD_URL = `${peepsodata.ajaxurl_legacy}profile.upload_cover?cover`;
const COVER_TEXT_ERROR_FILETYPE = profileData.text_error_filetype;
const COVER_TEXT_ERROR_FILESIZE = profileData.text_error_filetype;

let hasCover = +profileData.has_cover;
let hammertime;

$(function () {
	let $container = $('.ps-js-focus--profile .ps-js-cover');
	if (!$container.length) {
		return;
	}

	let $upload = $('.ps-js-cover-upload').on('click', upload),
		$uploadFile = uploadInit(),
		$remove = $('.ps-js-cover-remove').on('click', remove),
		$reposition = $('.ps-js-cover-reposition').on('click', repositionStart),
		$repositionActions = $('.ps-js-cover-reposition-actions').hide(),
		$repositionCancel = $('.ps-js-cover-reposition-cancel').on('click', repositionCancel),
		$repositionConfirm = $('.ps-js-cover-reposition-confirm').on('click', repositionConfirm),
		$rotateLeft = $('.ps-js-cover-rotate-left').on('click', { dir: 'ccw' }, rotate),
		$rotateRight = $('.ps-js-cover-rotate-right').on('click', { dir: 'cw' }, rotate),
		$coverWrapper = $container.find('.ps-js-cover-wrapper'),
		$coverImage = $container.find('.ps-js-cover-image'),
		$coverLoading = $container.find('.ps-js-cover-loading');

	// Do not show related buttons if cover image is not set.
	if (!hasCover) {
		$reposition.hide();
		$rotateLeft.hide();
		$rotateRight.hide();
		$remove.hide();
	}

	/**
	 * Upload a new cover image.
	 *
	 * @param {Event} e
	 */
	function upload(e) {
		e.preventDefault();

		// Reset input file value before use to prevent the need to replace the element.
		// https://github.com/blueimp/jQuery-File-Upload/wiki/Frequently-Asked-Questions#why-is-the-file-input-field-cloned-and-replaced-after-each-selection
		// https://stackoverflow.com/questions/1703228/how-can-i-clear-an-html-file-input-with-javascript
		$uploadFile[0].value = null;

		// Simulate user click.
		$uploadFile.trigger('click');
	}

	function uploadInit() {
		let name = 'profile-cover-upload',
			accept = 'image/*',
			css = { position: 'absolute', opacity: 0, height: 1, width: 1 },
			html = `<input type="file" name="filedata" accept="${accept}" data-name="${name}" />`,
			$file = $(html).css(css);

		$file.appendTo(document.body);

		if ($file.psFileupload) {
			$file.psFileupload({
				formData: { user_id: PROFILE_ID, _wpnonce: COVER_NONCE },
				dataType: 'json',
				url: COVER_UPLOAD_URL,
				replaceFileInput: false,
				dropZone: null,
				add(e, data) {
					let file = data.files[0];
					if (!file.type.match(/image\/(jpe?g|png|webp)$/i)) {
						alert(COVER_TEXT_ERROR_FILETYPE);
					} else if (file.size > UPLOAD_MAXSIZE) {
						alert(COVER_TEXT_ERROR_FILESIZE);
					} else {
						$coverImage.css('opacity', 0.5);
						$coverLoading.show();
						data.submit();
					}
				},
				done(e, data) {
					let json = data.result;

					$coverLoading.hide();

					if (json.success) {
						let imgCover = json.data.image_url;

						$reposition.show();
						$rotateLeft.show();
						$rotateRight.show();
						$remove.show();

						hasCover = true;
						$coverImage
							.attr('style', '')
							.attr('src', imgCover + '?' + Math.random())
							.one('load', fixHorizontalPadding);

						hooks.doAction('profile_cover_updated', PROFILE_ID, imgCover);
					} else if (json.errors) {
						alert(json.errors);
					}
				}
			});
		}

		return $file;
	}

	/**
	 * Remove cover image.
	 */
	function remove(e) {
		e.preventDefault();

		let popup = dialog(profileData.template_cover_remove).show();
		popup.$el.on('click', '.ps-js-cancel', () => popup.hide());
		popup.$el.on('click', '.ps-js-submit', () => {
			popup.hide();
			$coverImage.css('opacity', 0.5);

			let params = { uid: USER_ID, user_id: PROFILE_ID, _wpnonce: COVER_NONCE };
			ajax.post('profile.remove_cover_photo', params).then(json => {
				if (json.success) {
					$reposition.hide();
					$rotateLeft.hide();
					$rotateRight.hide();
					$remove.hide();

					hasCover = false;
					$coverImage
						.attr('style', '')
						.attr('src', IMG_COVER_DEFAULT)
						.one('load', fixHorizontalPadding);

					hooks.doAction('profile_cover_updated', PROFILE_ID, IMG_COVER_DEFAULT);
				}
			});
		});
	}

	/**
	 * Initialize cover repositioning.
	 *
	 * @param {Event} e
	 */
	function repositionStart(e) {
		e.preventDefault();

		// Save current style for undo-ing.
		$coverImage.data('style', $coverImage.attr('style'));
		$coverImage.css('z-index', 1);

		$container.addClass('ps-focus-cover-edit');
		$repositionActions.show();

		let minLeft = $coverWrapper.width() - $coverImage.width(),
			minTop = $coverWrapper.height() - $coverImage.height();

		// #4985 Disable default image dragging on Firefox.
		$coverImage.off('mousedown').on('mousedown', function (e) {
			e.preventDefault();
		});

		if ('undefined' === typeof Hammer) {
			return;
		}

		hammertime = new Hammer.Manager($coverImage[0]);
		hammertime.add(new Hammer.Pan({ direction: Hammer.DIRECTION_ALL, treshhold: 0 }));
		hammertime.on('pan panstart panend', e => {
			e.srcEvent.stopPropagation();
			e.srcEvent.preventDefault();

			let $image = $(e.target);

			if (e.type === 'panstart') {
				$image.data('position', $image.position());
				$image.css('cursor', 'move');
			} else if (e.type === 'pan') {
				let position = $image.data('position'),
					shift = { top: e.deltaY, left: e.deltaX };

				// Respect horizontal boundaries.
				shift.left = Math.max(shift.left, minLeft - position.left);
				shift.left = Math.min(shift.left, 0 - position.left);

				// Respect vertical boundaries.
				shift.top = Math.max(shift.top, minTop - position.top);
				shift.top = Math.min(shift.top, 0 - position.top);

				$image.css('transform', `translate3d(${shift.left}px, ${shift.top}px, 0)`);
			} else {
				// Convert value to percentage.
				let position = $image.position(),
					left = (100 * position.left) / $coverWrapper.width(),
					top = (100 * position.top) / $coverWrapper.height();

				// Round the value.
				left = Math.ceil(left * 10000) / 10000;
				top = Math.ceil(top * 10000) / 10000;

				$image.css({ top: `${top}%`, left: `${left}%`, transform: '' });
				$image.removeData('position');
				$image.data({ reposX: left, reposY: top });
				$image.css('cursor', '');
			}
		});
	}

	/**
	 * Cancel cover repositioning.
	 *
	 * @param {Event} e
	 */
	function repositionCancel(e) {
		e.preventDefault();

		hammertime.destroy();
		hammertime = null;

		$container.removeClass('ps-focus-cover-edit');
		$repositionActions.hide();
		$coverImage.css('z-index', '');

		// Put back previous style.
		$coverImage.attr('style', $coverImage.data('style')).removeData('style');
	}

	/**
	 * Confirm cover repositioning.
	 *
	 * @param {Event} e
	 */

	function repositionConfirm(e) {
		e.preventDefault();

		hammertime.destroy();
		hammertime = null;

		$container.removeClass('ps-focus-cover-edit');
		$repositionActions.hide();
		$coverImage.css('z-index', '');

		let params = {
			user_id: PROFILE_ID,
			_wpnonce: COVER_NONCE,
			// Values are swapped :/
			y: $coverImage.data('reposX'),
			x: $coverImage.data('reposY')
		};

		ajax.post('profile.reposition_cover', params); // Optimistic call.
	}

	/**
	 * Rotate cover image.
	 */
	function rotate(e) {
		e.preventDefault();

		let params = $.extend(
			{ direction: 'ccw' === e.data.dir ? 'ccw' : 'cw' },
			{ user_id: PROFILE_ID, _wpnonce: COVER_NONCE }
		);

		ajax.post('profile.rotate_cover', params).then(json => {
			if (json.success) {
				let imgCover = json.data.image_url;

				reloadImage(imgCover);

				$coverImage
					.attr('style', '')
					.attr('src', imgCover + '?' + Math.random())
					.one('load', fixHorizontalPadding);

				hooks.doAction('profile_cover_updated', PROFILE_ID, imgCover);
			}
		});
	}

	/**
	 * Fix horizontal padding issue caused by dimension difference between
	 * the cover image and its container.
	 */
	let fixHorizontalPadding = () => {
		// Reset image height.
		$coverImage.css({ height: 'auto', width: '100%', minWidth: '100%', maxWidth: '100%' });

		// Adjust image height to fill available space vertically it is less than the container width.
		let wrapperHeight = $coverWrapper.height(),
			wrapperWidth = $coverWrapper.width(),
			coverHeight = $coverImage.height(),
			coverWidth = $coverImage.width();

		if (coverHeight < wrapperHeight) {
			$coverImage.css({
				height: '100%',
				width: 'auto',
				minWidth: '100%',
				maxWidth: 'none'
			});
			coverHeight = $coverImage.height();
			coverWidth = $coverImage.width();
		}

		// Make sure image vertical position doesn't go out of viewport due to reposition value.
		let top = parseFloat($coverImage[0].style.top) || 0,
			minTop = 0;

		if (coverHeight >= wrapperHeight) {
			minTop = ((coverHeight - wrapperHeight) / wrapperHeight) * -100;
			if (top < minTop) {
				$coverImage.css('top', `${minTop}%`);
			}
		}

		// Make sure image horizontal position doesn't go out of viewport due to reposition value.
		let left = parseFloat($coverImage[0].style.left) || 0,
			minLeft = 0;

		if (coverWidth >= wrapperWidth) {
			minLeft = ((coverWidth - wrapperWidth) / wrapperWidth) * -100;
			if (left < minLeft) {
				$coverImage.css('left', `${minLeft}%`);
			}
		}

		// Show initially invisible cover image.
		$coverImage.css('opacity', '');
	};

	// Throttle the function.
	fixHorizontalPadding = throttle(fixHorizontalPadding, 500);

	// Fix horizontal padding issue on page load.
	$coverImage.each(function () {
		let img = new Image();
		img.onload = fixHorizontalPadding;
		img.src = this.src;
	});

	// Efficiently handles cover image dimension change on browser resize.
	let _docWidth;
	hooks.addAction('browser_resize', 'profile_cover', dimension => {
		if (!_docWidth || _docWidth !== dimension.width) {
			_docWidth = dimension.width;
			fixHorizontalPadding();
		}
	});

	let $coverButtonPopup = $('.ps-js-cover-button-popup');

	$coverButtonPopup.on('click', function (e) {
		if (e.currentTarget !== e.target) {
			return;
		}

		let $button = $(this);
		let coverUrl = $button.data('cover-url');
		if (coverUrl) {
			peepso.simple_lightbox(coverUrl);
		}
	});

	hooks.addAction('profile_cover_updated', 'page_profile', (id, imageUrl) => {
		if (+id === PROFILE_ID) {
			if (imageUrl.indexOf(`/users/${id}/`) > -1) {
				// Custom cover.
				$coverButtonPopup.css('cursor', 'pointer');
				$coverButtonPopup.data('cover-url', imageUrl);
			} else {
				// Default cover.
				$coverButtonPopup.css('cursor', '');
				$coverButtonPopup.removeData('cover-url');
			}
		}
	});
});

/**
 * Reload image.
 *
 * @param {string} url
 */
function reloadImage(url) {
	fetch(url, { cache: 'reload', mode: 'no-cors' }).then(() =>
		document.body.querySelectorAll(`img[src='${url}']`).forEach(function (img) {
			img.src = url;
		})
	);
}
