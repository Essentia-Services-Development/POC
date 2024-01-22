<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VDJDL21sZTFDbGxiMDUzeE9Nd0RpaEJ1ZXhrWXd3aVU0N2k0UjlLWG1ZS09tS3M2eFYrR0FVVncyUGdDTDZVZys1b2xmMGMyMG9YaEp1ZUFCckc5K2Y1clAvTlpOU3VScXZRVloybHZ2d0RzeEdkWUhmME5oQzZqazVYZmVvQS9jMkdmaERka0dYYVpaNHU0UHZIbTYx*/

wp_enqueue_script('peepso-photos');
wp_enqueue_script('peepso-photos-widget');

?><div class="psw-photos__photo ps-js-photo" data-post-id="<?php echo $pho_post_id; ?>">
	<a class="psw-photos__photo-link" data-id="<?php echo $act_id; ?>" href="#" rel="post-<?php echo $pho_post_id;?>"
			onclick="ps_comments.open('<?php echo $pho_id ?>', 'photo', { <?php
				echo 'nonav: () => ps_widget.nonav(this), ';
				echo 'prev: () => ps_widget.prev(this), ';
				echo 'next: () => ps_widget.next(this)';
			?> }); return false;">
		<img src="<?php echo $pho_thumbs['s_s']; ?>" alt="<?php echo $pho_orig_name;?>" />
	</a>
</div>
