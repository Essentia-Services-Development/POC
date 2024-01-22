{{ if ( _.isObject( data.vip ) && ! _.isArray( data.vip ) ) { }}
<div class="ps-hovercard__vip">
	{{ _.each( data.vip, function( item ) { }}
	<span class="ps-hovercard__vip-item ps-vip__tooltip-trigger"><img src="{{= item.icon_url }}" alt="{{= item.title }}" class="ps-vip__icon"></span>
	<div class="ps-vip__tooltip">
		<span class="ps-vip__tooltip-title">{{= item.title }}</span>
		{{ if ( item.content ) { }}
		<span class="ps-vip__tooltip-desc"> - {{= item.content }}</span>
		{{ } }}
	</div>
	{{ }); }}
</div>
{{ } }}
