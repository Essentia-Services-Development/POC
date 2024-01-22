<?php
$PeepSoFriends = PeepSoFriends::get_instance();
echo $args['before_widget'];
$owner = PeepSoUser::get_instance($instance['user_id']);

?>

<div class="ps-widget__wrapper<?php echo $instance['class_suffix'];?> ps-widget<?php echo $instance['class_suffix'];?>">
	<div class="ps-widget__header<?php echo $instance['class_suffix'];?>">
		<a href="<?php echo $owner->get_profileurl();?>friends"><?php
			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
			}
		?></a>
	</div>
	<?php
		if ( count($instance['list']) )
		{
	?>
	<div class="ps-widget__body<?php echo $instance['class_suffix'];?>">
		<div class="psw-friends">
			<?php foreach ($instance['list'] as $friend) { ?>
				<div class="psw-friends__item">
					<?php
					$friend = PeepSoUser::get_instance($friend);
					printf('<a href="%s" class="ps-avatar ps-avatar--member ps-tip ps-tip--inline ps-tip--arrow" aria-label="' . strip_tags($friend->get_fullname()) . '"><img alt="%s avatar" title="%s" src="%s" class="ps-name-tips"></a>',
						$friend->get_profileurl(),
						$friend->get_fullname(),
						$friend->get_fullname(),
						$friend->get_avatar()
					);
					?>
				</div>
			<?php } ?>
			<div class="psw-friends__count"><?php $friends_count = $PeepSoFriends->get_num_friends($instance['user_id']); ?></div>
		</div>
	</div>
	<div class="ps-widget__footer<?php echo $instance['class_suffix'];?>">
		<div class="psw-friends__more">
			<a href="<?php echo $owner->get_profileurl();?>friends">
				<span><?php echo __('View All', 'peepso-core');?></span>
				<span>(<?php echo $friends_count;?>)</span>
			</a>
		</div>
	</div>
	<?php } else { ?>
	<div class="ps-widget__body<?php echo $instance['class_suffix'];?>">
		<div class="psw-friends">
			<div class='psw-friends__info'><?php echo __('No friends', 'friendso'); ?></div>
		</div>
	</div>
	<?php } ?>
</div>

<?php

echo $args['after_widget'];

// EOF
