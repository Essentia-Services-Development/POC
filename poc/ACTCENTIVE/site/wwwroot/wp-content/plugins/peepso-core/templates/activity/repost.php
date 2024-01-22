<?php
$PeepSoActivity = PeepSoActivity::get_instance();
ob_start();
$PeepSoActivity->content();
$content = ob_get_clean();
?>
<div class="ps-post__repost">
	<?php if (trim(strip_tags($content))) { ?>
		<blockquote class="ps-post__quote ps-js-activity-quote"><?php echo $content; ?></blockquote>
	<?php } ?>
	<div class="ps-post__attachments js-stream-attachments">
		<?php $PeepSoActivity->post_attachment(); ?>
	</div>
</div>
