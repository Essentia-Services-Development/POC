<div id="pslocation" class="ps-dropdown__menu ps-postbox__location ps-postbox__location--loaded ps-js-postbox-location">
	<div class="ps-postbox__location-inner">
		<div class="ps-postbox__location-box">
			<div class="ps-postbox__location-field ps-input__wrapper ps-input__wrapper--icon">
				<input type="text" class="ps-input ps-input--icon ps-postbox__location-input"
							 name="postbox_loc_search"
							 placeholder="<?php echo __('Enter your location', 'peepso-core'); ?>" value="" disabled/>
				<i class="gcis gci-map-marked-alt"></i>
			</div>

			<div class="ps-postbox__location-search">
				<div class="ps-postbox__location-list ps-js-postbox-locations" data-no-items="<?php echo __('No locations found', 'peepso-core'); ?>"></div>

				<div id="pslocation-map" class="ps-postbox__location-map ps-js-postbox-map"></div>

				<div class="ps-loading" style="display: none">
					<div id="pslocation-search-loading" class="ps-loading__spinner">
						<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
					</div>
					<div id="pslocation-in-text" class="ps-loading__data"></div>
				</div>

				<div class="ps-postbox__location-actions ps-js-location-action">
					<button class="ps-btn ps-btn--sm ps-btn--action ps-postbox__location-action ps-js-add-location">
						<i class="gcis gci-map-marker-alt"></i><span><?php echo __('Select', 'peepso-core'); ?></span>
					</button>
					<button class="ps-btn ps-btn--sm ps-btn--abort ps-postbox__location-action ps-js-remove-location" style="display:none">
						<i class="gcis gci-times"></i><span><?php echo __('Remove', 'peepso-core'); ?></span>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
