(function ($, _) {
	function autosize(textarea) {
		textarea.style.height = '';
		textarea.style.height = +textarea.scrollHeight + 'px';
	}

	$.fn.ps_autosize = function () {
		return this.each(function () {
			autosize(this);
			$(this)
				.off('input.ps-autosize')
				.on('input.ps-autosize', function () {
					autosize(this);
				});
		});
	};
})(jQuery, _);
