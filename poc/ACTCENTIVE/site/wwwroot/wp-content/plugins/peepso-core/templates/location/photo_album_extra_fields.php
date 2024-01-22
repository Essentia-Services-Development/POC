<div id="pslocation" class="ps-location">
	<div class="ps-location__inner ps-postbox-location ps-postbox-location-compact">
		<div class="ps-location__loading ps-postbox-loading" style="display: none;">
			<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
			<div> </div>
		</div>
		<div class="ps-location__box ps-postbox-locmap">
			<div id="pslocation-map" class="ps-location__view ps-postbox-map"></div>
			<div class="ps-location__select ps-postbox-locct">
				<?php echo __('Enter your location:', 'peepso-core'); ?>
				<input type="text" class="ps-input ps-input--sm" name="postbox_loc_search" value="" disabled/>
				<ul class="ps-location__list ps-postbox-locations"></ul>
				<div class="ps-location__actions ps-postbox-action ps-location-action" style="display: none;">
					<button class="ps-btn ps-add-location" style="display: inline-block;">
						<i class="gcis gci-map-marker-alt"></i><?php echo __('Select', 'peepso-core'); ?>
					</button>
					<button class="ps-btn ps-remove-location" style="display: none;">
						<i class="gcis gci-times"></i><?php echo __('Remove', 'peepso-core'); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
<div style="display: none;">
	<div id="pslocation-search-loading">
		<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
	</div>
	<div id="pslocation-in-text"></div>
</div>
