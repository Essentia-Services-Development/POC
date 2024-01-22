(function ($) {

	// Dropdown toggle handler.
	function toggle(e) {
		var $ct = $(e.currentTarget).closest('.ps-js-dropdown'),
			$dd = $ct.find('.ps-js-dropdown-menu'),
			$doc = $(document),
			guid = $ct.data('ps-guid');

		// Prevent default action for link href="#".
		e.preventDefault();

		// Add guid data.
		if (!guid) {
			guid = _.uniqueId('ps-dropdown-');
			$ct.data('ps-guid', guid);
		}

		// Hide dropdown if it is currently visible.
		if ($dd.is(':visible')) {
			e.stopPropagation();
			$doc.off('click.' + guid);
			$dd.hide();
			return;
		}

		$dd.show();
		$doc.one('click.' + guid, function () {
			$dd.hide();
		});
	}

	// Dropdown select handler.
	function select(e) {
		var iconSelector = '[class*=gci-]',
			textSelector = '.ps-js-dropdown-label',
			$option = $(e.currentTarget),
			$ct = $option.closest('.ps-js-dropdown'),
			$selected = $ct.find('.ps-js-dropdown-toggle'),
			$hidden;

		// Handle privacy dropdown.
		if ($ct.hasClass('ps-js-dropdown--privacy')) {
			$hidden = $ct.children('[type=hidden]');
			$hidden.val($option.data('option-value'));
			$hidden.triggerHandler('change');
			$selected.find(iconSelector).attr('class', $option.find(iconSelector).attr('class'));
			$selected.find(textSelector).html($option.find(textSelector).html());
		}
	}

	$(function () {
		$(document)
			.on('click.ps-dropdown', '.ps-js-dropdown-menu a', select)
			.on('click.ps-dropdown', '.ps-js-dropdown-toggle', toggle);
	});

})(jQuery);
