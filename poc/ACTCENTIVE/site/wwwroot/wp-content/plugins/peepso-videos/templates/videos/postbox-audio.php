<?php

	$enable_upload = false;
	if (PeepSo::get_option('videos_audio_enable', 0) === 1 && apply_filters('peepso_permissions_audio_upload', TRUE)) {
		$enable_upload = true;
	}

?>
<div class="ps-postbox__media ps-postbox__media--audio <?php echo $enable_upload ? 'ps-postbox__media--withupload' : ''; ?> ps-postbox-videos">
	<div class="ps-postbox__media-inner ps-postbox-input ps-inputbox">

		<!-- Media embed -->
		<?php if(apply_filters('peepso_permissions_audio_embed', TRUE)) { ?>
		<div class="ps-postbox__media-embed ps-js-audio-embed">
			<input class="ps-input ps-input--sm ps-videos-url ps-js-url"
				placeholder="<?php echo __('Audio URL', 'vidso'); ?>" />
			<div class="ps-loading ps-postbox-loading ps-js-loading">
				<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>">
			</div>
		</div>
		<?php } ?>

		<?php if ($enable_upload) { ?>

		<?php if(apply_filters('peepso_permissions_audio_embed', TRUE)) { ?>
		<!-- Separator -->
		<div class="ps-postbox__media-separator ps-js-audio-separator">
			<span>
				<?php echo __('or', 'vidso'); ?>
			</span>
		</div>
		<?php } ?>

		<!-- Media upload -->
		<div class="ps-postbox__media-upload ps-js-audio-upload">

			<!-- Media upload button -->
			<div class="ps-postbox__media-action ps-js-btn">
				<i class="gcis gci-upload"></i>
				<strong><?php echo __('Upload', 'vidso'); ?></strong>

				<?php if ( isset($video_size) ) { ?>
					<span><?php echo sprintf( __('Max file size: %1$sMB', 'vidso'), $video_size['max_size'] ); ?></span>
				<?php } ?>
			</div>

			<div class="ps-postbox__media-file">
				<input type="file" name="filedata[]" class="ps-js-file"
               accept=".aac,.ac3,.aiff,.amr,.au,.flac,.m4a,.mid,.mka,.mp3,.ogg,.ra,.voc,.wav,.wma" style="display:none" />
			</div>

			<!-- Media upload form -->
			<div class="ps-postbox__media-form ps-js-form">
				<div class="ps-postbox__media-message ps-postbox__media-message--done ps-js-done">
					<i class="gcis gci-check-circle"></i> <?php echo __( 'File uploaded', 'vidso' ); ?>
				</div>
				<div class="ps-postbox__media-message ps-postbox__media-message--fail ps-js-failed">
					<i class="gcis gci-exclamation-circle"></i> <?php echo __( 'Upload failed: ', 'vidso' ); ?>
					<div class="ps-postbox__media-message-data ps-js-failed-message"></div>
				</div>
				<div class="ps-postbox__media-progress-wrapper ps-js-progress">
					<div class="ps-postbox__media-progress">
						<div class="ps-postbox__media-progress-bar"><span class="ps-js-percent-bar"></span></div>
					</div>
					<div class="ps-postbox__media-progress-percent ps-js-percent"></div>
				</div>
				<div class="ps-postbox__media-details">
					<div class="ps-postbox__media-details-field ps-postbox__media-details-field--full">
						<input class="ps-input ps-input--sm ps-js-title"
							placeholder="<?php echo __('Enter the title...', 'vidso'); ?>" />
					</div>
					<div class="ps-postbox__media-details-field">
						<input class="ps-input ps-input--sm ps-js-artist"
							placeholder="<?php echo __('Artist (optional)', 'vidso'); ?>" />
					</div>
					<div class="ps-postbox__media-details-field">
						<input class="ps-input ps-input--sm ps-js-album"
							placeholder="<?php echo __('Album (optional)', 'vidso'); ?>" />
					</div>
				</div>
			</div>

			<!-- Media upload success notice -->
			<div class="ps-postbox__media-alert ps-alert ps-alert--success ps-js-success">
				<i class="gcis gci-check-circle"></i>
				<span>
					<?php

						$notice = __( "Your audio was uploaded successfully!\nIt's been added to the queue, we will notify you when your audio is published.", 'vidso' );
						echo nl2br( $notice );

					?>
				</span>
			</div>

		</div>
		<?php } ?>

		<!-- Media preview -->
		<div class="ps-postbox__media-preview ps-js-audio-preview"></div>
	</div>
</div>
