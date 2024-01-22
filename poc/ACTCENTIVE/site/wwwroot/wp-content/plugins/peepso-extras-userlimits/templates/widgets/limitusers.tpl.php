<?php
echo $args['before_widget'];

$PeepSoLimitUsers = PeepSoLimitUsers::get_instance();

// widget title
if (!empty( $instance['title'])) {
	echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
}

// short message to the user
if( !empty($instance['message'])) {
	echo '<p>' , $instance['message'] , '</p>';
}
?>

<div class="psw-ulimits">
	<?php
	// profile completeness
	if($instance['user_id'] > 0)
	{
		if(isset($instance['stats']['fields_all']) && $instance['stats']['fields_all'] > 0) {
			$style = '';
			if ($instance['stats']['completeness'] >= 100) {
				$style.='display:none;';
			}
		?>
		<div class="psw-ulimits__progress ps-js-widget-me-completeness" style="<?php echo $style;?>">
			<div class="psw-ulimits__progress-message ps-js-status">
				<?php
					echo $instance['stats']['completeness_message'];
					do_action('peepso_action_render_profile_completeness_message_after', $instance['stats']);
				?>
			</div>
			<div class="psw-ulimits__progress-bar ps-js-progressbar" style="<?php echo $style;?>">
				<span style="width:<?php echo $instance['stats']['completeness'];?>%"></span>
			</div>
		</div>
		<?php
		}
	}

	PeepSoLimitUsers::get_instance()->debug_formatted();
	?>
</div>

<?php

echo $args['after_widget'];

// EOF
