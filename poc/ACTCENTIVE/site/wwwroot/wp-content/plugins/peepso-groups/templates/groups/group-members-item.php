<div class="ps-member ps-js-member" data-id="{{= data.id }}">
	<div class="ps-member__inner">
		<div class="ps-member__header">
			<a href="{{= data.profileurl }}" class="ps-avatar ps-avatar--member">
				<img src="{{= data.avatar }}" title="{{= data.fullname }}" alt="{{= data.fullname }} avatar">
			</a>			
			<div class="ps-member__cover" style="background-image:url('{{= data.cover }}')"></div>
		</div>
		<div class="ps-member__body">
			<div class="ps-member__name">
				<a href="{{= data.profileurl }}" class="ps-members-item-title" title="{{= data.fullname }}">
					{{= data.fullname_before }}{{= data.fullname }}{{= data.fullname_after }}
				</a>
			</div>
            <?php
            #6666 GeoMyWp hooks
            do_action('peepso_action_render_groups_member_details_before');
            ?>
			<div class="ps-member__details">
				{{ if ( data.role=='member_owner' || data.role=='member_manager' || data.role=='member_moderator' ) { }}
				<div class="ps-member__role ps-js-member-role">
					{{ if ( data.role=='member_owner') { }}<i class="gcis gci-user-tie"></i><span><?php echo __('Owner','groupso');?></span>{{ } }}
					{{ if ( data.role=='member_manager') { }}<i class="gcis gci-user-edit"></i><span><?php echo __('Manager','groupso');?></span>{{ } }}
					{{ if ( data.role=='member_moderator') { }}<i class="gcis gci-user-shield"></i><span><?php echo __('Moderator','groupso');?></span>{{ } }}
				</div>
				{{ } else { }}
				<div class="ps-member__role ps-js-member-role" style="display:none"></div>
				{{ } }}
				<script type="text/html" data-role="owner"><i class="gcis gci-user-tie"></i><span><?php echo __('Owner', 'groupso'); ?></span></script>
				<script type="text/html" data-role="manager"><i class="gcis gci-user-edit"></i><span><?php echo __('Manager', 'groupso');?></span></script>
				<script type="text/html" data-role="moderator"><i class="gcis gci-user-shield"></i><span><?php echo __('Moderator', 'groupso');?></span></script>
			</div>
            <?php
            #6666 GeoMyWp hooks
            do_action('peepso_action_render_groups_member_details_after');
            ?>
		</div>

		<div class="ps-member__actions ps-group__member-dropdown ps-dropdown ps-dropdown--menu ps-js-actions-placeholder ps-js-dropdown" data-id="{{= data.id }}" style="display:none">
			<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="">
		</div>
	</div>
</div>
