<div class="ps-vip__dropdown">
	{{ if ( data === 'loading' ) { }}
	<div style="width:100px; text-align:center">
		<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" />
	</div>
	{{ } else { }}
	{{ _.each( data, function( item ) { }}
	<div class="ps-vip-dropdown__item">
		<img src="{{= item.icon_url }}" alt="{{= item.title }}" title="{{= item.title }}" class="ps-vip__icon" />
		<div class="ps-vip-dropdown-item__content">
			<strong>{{= item.title }}</strong>
			<span>{{= item.content }}</span>
		</div>
	</div>
	{{ }); }}
	{{ } }}
</div>
