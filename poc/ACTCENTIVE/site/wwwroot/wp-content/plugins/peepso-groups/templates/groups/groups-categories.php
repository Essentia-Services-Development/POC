<div class="ps-groups__category ps-accordion ps-js-group-category-item ps-js-groups-cat ps-js-groups-cat-{{= data.id }}" data-id="{{= data.id }}">
	<div class="ps-groups__category-title ps-accordion__title ps-js-group-category-title">
		<a href="{{= data.url }}">{{= data.name }}</a>
		<div class="ps-groups__category-action ps-accordion__title-action">
			<a href="#" class="ps-js-group-category-action">
				<i class="gcis gci-expand-alt"></i>
			</a>
		</div>
	</div>

	<?php $single_column = PeepSo::get_option( 'groups_single_column', 0 ); ?>
	<div class="ps-groups__category-list ps-accordion__body ps-groups__list <?php echo $single_column ? 'ps-groups__list--single' : '' ?> ps-js-groups" data-mode="<?php echo $single_column ? 'list' : 'grid' ?>" style="display:none">
		<img class="ps-loading post-ajax-loader" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="loading" />
	</div>

	<div class="ps-groups__category-footer ps-accordion__footer ps-js-group-category-footer">
		<a class="ps-groups__category-footer-action" href="{{= data.url }}{{= data.__uncategorized ? '' : 'groups' }}">
			<?php echo __('Show all', 'peepso-core') ;?>
			<?php if(PeepSo::get_option('groups_categories_show_count', 0)) { ?>
				{{= typeof data.groups_count !== 'undefined' ? ('(' + data.groups_count + ')') : '' }}
			<?php } ?>
		</a>
	</div>
</div>
