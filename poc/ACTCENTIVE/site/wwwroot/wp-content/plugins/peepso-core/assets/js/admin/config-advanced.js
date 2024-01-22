jQuery(function($) {
	var $loadMore = $('select[name=loadmore_enable]'),
		$loadMoreRepeat = $('select[name=loadmore_repeat]');

	$loadMore.on('change', function() {
		var $field = $loadMoreRepeat.closest('.form-group');
		this.value == 0 ? $field.hide() : $field.show();
	});
	$loadMore.triggerHandler('change');
});
