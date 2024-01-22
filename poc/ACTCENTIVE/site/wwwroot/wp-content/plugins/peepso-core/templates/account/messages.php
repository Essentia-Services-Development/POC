	<?php
	$PeepSoMessages = PeepSoMessages::get_instance();
	$PeepSoMessagesModel = new PeepSoMessagesModel();
	?>	
	<h2><?php echo __('Messages', 'peepso-core');?></h2>
	<?php
	$i = 0;
	foreach ($messages as $key => $message) {
		$args = array(
			'post_author' => $message->post_author, 'post_id' => $message->ID
		);
		echo '<p><a href="../messages/'. $i++ .'.htm">';
		$PeepSoMessages->get_recipient_name($args, $user->get_id());
		echo ' - ' . $message->post_date;
		echo '</a></p>';
	}
	
	?>

