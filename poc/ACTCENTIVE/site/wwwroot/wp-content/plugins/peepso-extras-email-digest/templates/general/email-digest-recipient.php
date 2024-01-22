<div class="itemdiv memberdiv clearfix">
	<div class="user">
		<a href="<?php echo $url; ?>" title="View profile" target="_blank">
			<img alt="<?php echo $name; ?>" src="<?php echo $avatar_url; ?>">
		</a>
		<a href="#" title="Preview email was sent" class="preview-email" data-id="<?php echo $edc_id; ?>"><i class="ps-icon-envelope-alt"></i></a>
	</div>
	<div class="body">
		<div>
			<a href="<?php echo $url; ?>" title="View profile" target="_blank"><?php echo $name; ?></a>
		</div>
		<div>
			<span><?php echo __('User last login: ', 'peepso-email-digest') . $last_login; ?></span>
		</div>
		<div>
			<span><?php echo __('Last email sent: ', 'peepso-email-digest') . $last_email_sent; ?></span>
		</div>
	</div>
</div>