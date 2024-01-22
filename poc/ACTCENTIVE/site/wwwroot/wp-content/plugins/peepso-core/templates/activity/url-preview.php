<?php

$small_thumbnail = PeepSo::get_option('small_url_preview_thumbnail', 0);

?><div class="ps-postbox__url-preview url-preview <?php echo $small_thumbnail ? '' : 'ps-postbox__url-preview--sm' ?>">
	<div class="ps-postbox__url-close close"><a href="#" class="remove-preview"><i class="gcis gci-times"></i></a></div>
	<?php PeepSoTemplate::exec_template('activity', 'content-media', $media); ?>
</div>
