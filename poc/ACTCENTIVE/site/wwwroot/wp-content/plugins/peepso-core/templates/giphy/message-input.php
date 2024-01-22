<?php if(PeepSo::get_option_new('giphy_chat_enable')) { ?>

<i class="ps-chat__window-input-addon ps-giphy__trigger gcis gci-giphy ps-js-giphy-trigger"></i>

<div class="ps-giphy ps-giphy--slider ps-giphy--chat ps-giphy__popover ps-js-giphy-container">
	<div class="ps-giphy__search ps-giphy__search--popover">
		<input type="text" class="ps-input ps-input--sm ps-giphy__input ps-js-giphy-query" placeholder="<?php echo __('Search...', 'peepso-core'); ?>">
		<div class="ps-giphy__powered ps-giphy__powered--chat">
			<a href="https://giphy.com/" target="_blank"></a>
		</div>
	</div>

	<div class="ps-giphy__loading ps-loading ps-js-giphy-loading">
		<i class="gcis gci-circle-notch gci-spin"></i>
	</div>

	<div class="ps-giphy__slider ps-js-slider">
		<div class="ps-giphy__slides ps-js-giphy-list"></div>

		<script type="text/template" class="ps-js-giphy-list-item">
			<div class="ps-giphy__slide ps-giphy__slides-item ps-js-giphy-item">
				<img class="ps-giphy__slide-image" src="{{= data.preview }}" data-id="{{= data.id }}" data-url="{{= data.src }}" />
			</div>
		</script>

		<div class="ps-giphy__nav ps-giphy__nav--left ps-js-giphy-nav-left"><i class="gcis gci-chevron-left"></i></div>
		<div class="ps-giphy__nav ps-giphy__nav--right ps-js-giphy-nav-right"><i class="gcis gci-chevron-right"></i></div>
	</div>
</div>

<?php } ?>
