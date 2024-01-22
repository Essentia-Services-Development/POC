<?php

echo $args['before_widget'];
$owner = PeepSoUser::get_instance($instance['user_id']);

?>

<div class="ps-widget--bday__wrapper ps-widget__wrapper<?php echo $instance['class_suffix'];?> ps-widget<?php echo $instance['class_suffix'];?>">
	<div class="ps-widget__header<?php echo $instance['class_suffix'];?>">
		<?php
			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
			}
		?>
	</div>
	<div class="ps-widget__body<?php echo $instance['class_suffix'];?>">
	<?php
		if ( count($instance['list']['today_birthday']) >0)
		{
	?>
		<div class="ps-widget--bday__title"><?php echo __('Birthday Today', 'friendso');?></div>
		<div class="ps-widget--bday ps-widget--bday--today">
			<div class="ps-widget__bdays">
				<?php foreach ($instance['list']['today_birthday'] as $todaybirthday) { ?>
					<div class="ps-widget__bdays-item">
						<div class="ps-widget--bday__avatar">
							<?php
							$friend = PeepSoUser::get_instance($todaybirthday['usr_id']);
							printf('<a class="ps-avatar" href="%s"><img alt="%s avatar" title="%s" src="%s" class="ps-name-tips"></a>',
								$friend->get_profileurl(),
								$friend->get_fullname(),
								$friend->get_fullname(),
								$friend->get_avatar()
							);
							?>
						</div>
						<div class="ps-widget--bday__details">
							<?php
							printf('<a href="%s">%s<span>'.__('Say Happy Birthday!','friendso').'</span></a> ',
									$friend->get_profileurl(),
									$friend->get_fullname()
								);
							?>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	<?php
		}
		else
		{
			if(0 === intval($instance['hideempty'])) {
				echo "<div class='ps-widget--bday__title'>" . __('Birthday Today', 'friendso') . "</div>";
				echo "<div class='ps-widget--bday ps-widget--bday--today'><span>". __("No friends' birthdays today.", 'friendso') . "</span></div>";
			}
		}
	?>
	<?php
	if(1 === $instance['show_upcoming_birthday']) {
	?>

	<?php
		if ( count($instance['list']['upcoming_birthday'])>0 )
		{
	?>
		<div class="ps-widget--bday__title"><?php echo __('Upcoming Birthday', 'friendso');?></div>
		<div class="ps-widget--bday ps-widget--bday--upcoming">
			<div class="ps-widget__bdays">
				<?php foreach ($instance['list']['upcoming_birthday'] as $upcomingbirthday) { ?>
					<div class="ps-widget__bdays-item">
						<div class="ps-widget--bday__avatar">
							<?php
							//$birth_date = date( "j", strtotime( $upcomingbirthday['birthdate'] ) );
							//$month_name = date( "F", strtotime( $upcomingbirthday['birthdate'] ) );
							$friend = PeepSoUser::get_instance($upcomingbirthday['usr_id']);
							printf('<a class="ps-avatar" href="%s"><img alt="%s avatar" title="%s" src="%s" class="ps-name-tips"></a>',
								$friend->get_profileurl(),
								$friend->get_fullname(),
								$friend->get_fullname(),
								$friend->get_avatar()
							);
							?>
						</div>
						<div class="ps-widget--bday__details">
							<?php
							$birthday = $upcomingbirthday['birthdate'];
							$cur_day = date( 'Y-m-d', current_time( 'timestamp' ) );
							$cur_time_arr = explode('-',$cur_day);
							$birthday_arr = explode('-',$birthday);

							$cur_year_b_day = $cur_time_arr[0]."-".$birthday_arr[1]."-".$birthday_arr[2];

							if(strtotime($cur_year_b_day) > strtotime($cur_day))
							{
							    $diff=strtotime($cur_year_b_day)-strtotime($cur_day);//time returns current time in seconds
							    $daysleft=ceil($diff/(60*60*24));
							}

							printf('<a href="%s">%s</a><span>'.sprintf(_n('Birthday tomorrow.','Birthday in %s days.</span>',$daysleft,'friendso'), $daysleft),
									$friend->get_profileurl(),
									$friend->get_fullname(),
									$daysleft
								);
							?>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	<?php
		}
		else
		{
			if(0 === intval($instance['hideempty'])) {
				echo "<div class='ps-widget--bday__title'>" . __('Upcoming Birthday', 'friendso') . "</div>";
				echo "<div class='ps-widget--bday ps-widget--bday--upcoming'><span>" . __('No upcoming birthdays.', 'friendso') . "</span></div>";
			}
		}
	}
	?>
	</div>
</div>

<?php

echo $args['after_widget'];

// EOF
