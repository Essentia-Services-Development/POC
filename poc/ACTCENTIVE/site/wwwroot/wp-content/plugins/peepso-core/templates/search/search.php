<?php

	$search = '';
	if ( isset($context) && 'shortcode' === $context ) {
		$search = PeepSo3_Shortcode_Search::get_search_query();
	}

	$show_images = PeepSo::get_option_new('peepso_search_show_images');
	$image_default = PeepSo::get_asset('images/embeds/no_preview_available.png');

?>
<div class="ps-search ps-js-section-search">
	<div class="ps-search__input-wrapper">
		<i class="gcis gci-search"></i>
		<input type="text" value="<?php echo $search; ?>" class="ps-input ps-search__input ps-js-query"
			placeholder="<?php echo __('Type to search...', 'peepso-core'); ?>" />
	</div>

	<div class="ps-loading ps-js-loading" style="display:none">
		<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>">
	</div>

	<div class="ps-search__result ps-js-result" style="display:none"></div>

	<script type="text/template" class="ps-js-template-section">
		<div class="ps-search__section" data-type="{{= data.type }}">
			<div class="ps-search__section-title"><a href="{{= data.url }}">{{= data.title }}</a></div>
			{{= data.html }}
		</div>
	</script>

	<script type="text/template" class="ps-js-template-items">
		<div class="ps-search__items ps-search__items--{{= data.type }}">
			{{ data.results.forEach(function(item) { }}
			<a href="{{= item.url }}" class="ps-search__item ps-search__item--{{= data.type }}" data-type="{{= data.type }}" data-id="{{= item.id }}">
				{{ if (+'<?php echo $show_images ?>') { }}
					{{ if (item.image) { }}
					<div class="ps-search__item-thumb" style="background-image: url({{= item.image }})">
						<img src="{{= item.image }}" alt="{{= item.title }}"/>
					</div>
					{{ } else { }}
					<div class="ps-search__item-thumb ps-search__item-thumb--default" style="background-image: url(<?php echo $image_default ?>)">
						<img src="<?php echo $image_default ?>" alt="{{= item.title }}"/>
					</div>
					{{ } }}
				{{ } }}

				{{ if (item.meta && item.meta.forEach) { var empty = true; }}
					{{ item.meta.forEach(function(meta) { }}
						{{ if (meta.icon && meta.title) { }}
							{{ if (empty) { empty = false; }}
								<div class="ps-search__item-meta">
							{{ } }}
									<span><i class="{{= meta.icon }}"></i> {{= meta.title }}</span>
						{{ } }}
					{{ }); }}
					{{ if (!empty) { }}
								</div>
					{{ } }}
				{{ } }}

				<div class="ps-search__item-title">{{= item.title }}</div>

				{{ if (item.text) { }}
				<div class="ps-search__item-content">{{= item.text }}</div>
				{{ } }}
			</a>
			{{ }); }}
		</div>
	</script>

	<script type="text/template" class="ps-js-template-empty">
		<p class="ps-search__item--empty"><?php echo __('No results.', 'peepso-core'); ?></p>
	</script>
</div>
