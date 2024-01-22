/**
 * This script will be loaded across all PeepSo admin pages.
 */

// Handle field highlighting.
(function ($) {
	function highlight(field) {
		var container = field.closest('.postbox');
		var containers = document.querySelectorAll('form .postbox');

		// Highlight the container.
		containers.forEach(function (item) {
			item.style.background = item === container ? '#ffffee' : '';
			item.querySelector('.hndle').style.background = item === container ? '#ffffcc' : '';
		});

		// Highlight the actual field if necessary.
		var fields = document.querySelectorAll('form .postbox .form-group');
		fields.forEach(function (item) {
			item.style.background = item === field ? '#ffffaa' : '';
		});
	}

	function scrollInto(element) {
		if (!element.scrollIntoView) {
			return;
		}

		// Disable auto scroll restoration on reload page.
		// https://stackoverflow.com/questions/10742422/prevent-browser-scroll-on-html5-history-popstate/33004917#33004917
		if ('scrollRestoration' in history) {
			history.scrollRestoration = 'manual';
		}

		setTimeout(function () {
			element.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}, 1000);
	}

	function updateHash(hash) {
		if (location.hash !== '#' + hash) {
			history.replaceState(null, null, '#' + hash);
		}
	}

	window.addEventListener('DOMContentLoaded', function () {
		var hash = location.hash;

		// Handle field highlighting on page load.
		if (hash) {
			var hashParts = hash.split(':');
			var field = document.querySelector(hashParts[0]);
			if (field) {
				if ('parent' === hashParts[1]) {
					var container = field.closest('.postbox');
					highlight(container);
					scrollInto(container);
				} else {
					highlight(field);
					scrollInto(field);
				}
			}
		}

		// Handle right-click on field label.
		$('.form-group').on('contextmenu', '.ps-form__label, h4', function (e) {
			var field = this.closest('.form-group');
			if (field && field.id) {
				e.preventDefault();
				highlight(field);
				updateHash(field.id);
			}
		});

		// Handle right-click on field-group label.
		$('.postbox .hndle').on('contextmenu', function (e) {
			var container = this.closest('.postbox');
			var field = container.querySelector('.form-group');

			if (field && field.id) {
				e.preventDefault();
				highlight(container);
				updateHash(field.id + ':parent');
			}
		});

		// Handle read-only fields.
		var $readonly = $('.form-group .form-field').find(
			'input.readonly, select.readonly, textarea.readonly'
		);

		$readonly
			.each(function () {
				this.disabled = true;
				$(this).closest('.form-group').css('opacity', 0.6);
			})
			// Clear disabled state on form submit so that current config values will be submitted.
			.closest('form')
			.on('submit', function () {
				$readonly.each(function () {
					this.disabled = false;
				});
			});
	});
})(jQuery);
