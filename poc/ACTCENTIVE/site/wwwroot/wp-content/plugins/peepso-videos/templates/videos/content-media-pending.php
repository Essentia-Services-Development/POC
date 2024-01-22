<?php
if(!isset($oembed_type) || (isset($oembed_type) && 'video' === $oembed_type) ) {

	$activity = new PeepSoActivity();
    $postvideo = $activity->get_post($vid_post_id);
    $url = PeepSo::get_page('activity');
    if (!empty($postvideo->post)) {
	    $url = PeepSo::get_page('activity_status') . $postvideo->post->post_title;
    }
?>
<div class="ps-media ps-media--video ps-media--pending ps-media-video">
	<div class="ps-media__body ps-media-body video-description">
		<?php if ($vid_conversion_status == PeepSoVideosUpload::STATUS_PENDING) { ?>
			<?php if ($vid_upload_s3_status == PeepSoVideosUpload::STATUS_S3_WAITING) { ?>
				<div class="ps-media__notif"><i class="gcis gci-magic"></i> <?php echo __("Video is being processed. You'll be notified when it's ready.", 'vidso'); ?></div>
			<?php } else { ?>
				<div class="ps-media__notif"><i class="gcis gci-magic"></i> <?php echo __('Video is being converted. It should be available in a few minutes.', 'vidso'); ?></div>
			<?php } ?>
		<?php } elseif ($vid_conversion_status == PeepSoVideosUpload::STATUS_PROCESSING) { ?>
			<div class="ps-media__notif"><i class="gcis gci-check"></i> <?php echo __('It has now converted.'); ?></div>
		<?php } elseif ($vid_conversion_status == PeepSoVideosUpload::STATUS_FAILED) { ?>
			<div class="ps-alert ps-alert--abort"><i class="gcis gci-exclamation-triangle"></i> <?php echo __('Video failed to convert.', 'vidso'); ?></div>
		<?php } ?>
	</div>
</div>
<?php }
