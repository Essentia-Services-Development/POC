{{

data = data || {};

var memberActions = data.member_actions || [],
	followerActions = data.follower_actions || [];

function fixLabel( label ) {
	return ( label || '' ).replace( /^[a-z]/, function( chr ) {
		return chr.toUpperCase();
	});
}

_.each( memberActions.concat( followerActions ), function( item ) {
	if ( _.isArray( item.action ) ) {

}}

<span class="ps-group__action ps-dropdown ps-dropdown--right ps-dropdown--follow ps-js-dropdown">
	<a class="ps-group__action-toggle ps-js-dropdown-toggle" href="javascript:">
		<span>{{= fixLabel( item.label ) }}</span>
		<img class="ps-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif') ?>" />
	</a>
	<div class="ps-dropdown__menu ps-js-dropdown-menu">
		{{ _.each( item.action, function( item ) { }}
		<a href="#"
				{{= item.action ? 'data-method="' + item.action + '"' : 'disabled="disabled"' }}
				data-confirm="{{= item.confirm }}" data-id="{{= data.id }}"
				{{ if (item.args) _.each( item.args, function( value, key ) { }}
				data-{{= key }}="{{= value }}"
				{{ }); }}
		>
			<div class="ps-dropdown__group-title">
				<i class="{{= item.icon }}"></i>
				<span>{{= fixLabel( item.label ) }}</span>
			</div>
			<div class="ps-dropdown__group-desc">{{= item.desc }}</div>
		</a>
		{{ }); }}
	</div>
</span>

{{ } else { }}

<a class="ps-group__action ps-group__action-toggle" 
		{{ if (item.redirect) { }}
			href="{{= item.redirect }}"
		{{ } else { }}
			href="javascript:"
			{{= item.action ? 'data-method="' + item.action + '"' : 'disabled="disabled"' }}
			data-confirm="{{= item.confirm }}" data-id="{{= data.id }}"
			{{ if (item.args) _.each( item.args, function( value, key ) { }}
			data-{{= key }}="{{= value }}"
			{{ }); }}
		{{ } }}
	<span>{{= fixLabel( item.label ) }}</span>
	<img class="ps-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif') ?>" />
</a>

{{ } }}
{{ }); }}
