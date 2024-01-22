<div class="ps-postbox__location-inner ps-js-location-wrapper">
	<div class="ps-postbox__location-box">
		<div class="ps-postbox__location-field ps-input__wrapper ps-input__wrapper--icon">
			<input type="text" class="ps-input ps-input--icon ps-postbox__location-input"
				placeholder="<?php echo __('Enter your location', 'peepso-core'); ?>" value="" />
			<i class="gcis gci-map-marked-alt"></i>
		</div>
		<div class="ps-postbox__location-search">
			<div class="ps-postbox__location-list ps-js-location-result"
				data-no-items="<?php echo __('No locations found', 'peepso-core'); ?>"></div>
			<div class="ps-postbox__location-map ps-js-location-map"></div>
			<div class="ps-loading" style="display: none">
				<div class="ps-postbox__location-item ps-postbox__location-item--loading ps-js-postbox-location-item ps-js-location-loading">
					<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
				</div>
			</div>
			<div class="ps-postbox__location-actions ps-js-location-action" style="display:block">
				<button class="ps-btn ps-btn--sm ps-btn--action ps-postbox__location-action ps-js-select" style="display:none">
					<i class="gcis gci-map-marker-alt"></i><span><?php echo __('Select', 'peepso-core'); ?></span>
				</button>
				<button class="ps-btn ps-btn--sm ps-btn--abort ps-postbox__location-action ps-js-remove" style="display:none">
					<i class="gcis gci-times"></i><span><?php echo __('Remove', 'peepso-core'); ?></span>
				</button>
			</div>
		</div>
	</div>
	<script type="text/template" class="ps-js-location-fragment">
		<div class="ps-postbox__location-item {{= data.place_id ? 'ps-js-location-listitem' : '' }}" data-place-id="{{= data.place_id }}" >
			<p class="ps-js-location-listitem-name">{{= data.name }}</p>
			<span>{{= data.description || '&nbsp;' }}</span>
		</div>
	</script>
</div>
