<div class="ps-location ps-js-location-wrapper">
	<div class="ps-location__inner ps-js-location">
		<div class="ps-location__loading ps-js-location-loading" style="display:none">
			<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
		</div>
		<div class="ps-location__box ps-js-location-result">
			<div class="ps-location__list ps-js-location-list">
				<div class="ps-location__select ps-js-location-placeholder"><i class="gcis gci-map-marked-alt"></i> <?php echo __('Enter location name...', 'peepso-core'); ?></div>
			</div>
			<div class="ps-location__view ps-js-location-map"></div>
			<div class="ps-location__actions">
				<a href="#" class="ps-btn ps-btn--sm ps-btn--action ps-js-select"><?php echo __('Select', 'peepso-core'); ?></a>
				<a href="#" class="ps-btn ps-btn--sm ps-btn--abort ps-js-remove"><?php echo __('Remove', 'peepso-core'); ?></a>
			</div>
		</div>
	</div>
	<div class="ps-location-fragment ps-js-location-fragment" style="display:none">
		<a href="#" class="ps-location__list-item ps-js-location-listitem" data-place-id="{place_id}" onclick="return false;">
			<strong class="ps-js-location-listitem-name">{name}</strong>
			<span>{description}</span>
		</a>
	</div>
</div>
