jQuery(function ($) {
	var $video = $('input[name=videos_video_master_switch]'),
		$videoUpload = $('input[name=videos_upload_enable]'),
		$videoConversionMode = $('select[name=videos_conversion_mode]'),
		$videoAnimatedGif = $('input[name=videos_generate_animated_gif]'),
		$videoAnimatedOutput = $('select[name=videos_animated_output]'),
		$videoNoUpscale = $('input[name=videos_never_upscale]'),
		$videoFFProbe = $('input[name=videos_ffprobe_binary]'),
		$audio = $('input[name=videos_audio_master_switch]'),
		$audioUpload = $('input[name=videos_audio_enable]'),
		$audioLastFm = $('input[name=videos_audio_lastfm]'),
		$audioLastFmApi = $('input[name=videos_audio_lastfm_api_key]');

	// Toggle video configs.
	$video.on('click', function () {
		var $target = $videoUpload.closest('.postbox');
		this.checked ? $target.show() : $target.hide();
	});

	// Toggle video upload configs.
	$videoUpload.on('click', function () {
		var $enable = $(this).closest('.form-group');
		var $target = $enable.next().nextAll();

		if (this.checked) {
			$target.show();
			$videoConversionMode.triggerHandler('change');
		} else {
			$target.hide();
		}
	});

	// Toggle video skip conversion
	$videoConversionMode.on('change', function () {
		var mode = $(this).val();
		var $targetAws = $('#field_videos_elastic_transcoder_separator');
		var $targetFfmpeg = $('#field_videos_ffmpeg_separator');
		var $targetVideoAllowedExtension = $('#field_videos_allowed_extensions');
		

		$targetAws = $targetAws.add($targetAws.nextUntil($targetFfmpeg));
		$targetFfmpeg = $targetFfmpeg.add($targetFfmpeg.nextAll());

		if ('aws_elastic' === mode) {
			$targetVideoAllowedExtension.hide();
			$targetAws.show();
			$targetFfmpeg.hide();
		} else if ('ffmpeg' === mode) {
			$targetVideoAllowedExtension.hide();
			$targetAws.hide();
			$targetFfmpeg.show();
			$videoAnimatedGif.triggerHandler('click');
			$videoNoUpscale.triggerHandler('click');
		} else {
			$targetVideoAllowedExtension.show();
			$targetAws.hide();
			$targetFfmpeg.hide();
		}
	});

	// Toggle video never upscale configs.
	$videoAnimatedGif.on('click', function () {
		var $target = $videoAnimatedOutput.closest('.form-group');
		this.checked ? $target.show() : $target.hide();
	});

	// Toggle video never upscale configs.
	$videoNoUpscale.on('click', function () {
		var $target = $videoFFProbe.closest('.form-group');
		this.checked ? $target.show() : $target.hide();
	});

	// Toggle audio configs.
	$audio.on('click', function () {
		var $enable = $(this).closest('.form-group');
		var $target = $enable.next().nextAll();

		if (this.checked) {
			$target.show();
			$audioUpload.triggerHandler('click');
		} else {
			$target.hide();
		}
	});

	// Toggle audio upload configs.
	$audioUpload.on('click', function () {
		var $enable = $(this).closest('.form-group');
		var $target = $enable.next().nextAll();

		if (this.checked) {
			$target.show();
			$audioLastFm.triggerHandler('click');
		} else {
			$target.hide();
		}
	});

	// Toggle audio last.fm integration configs.
	$audioLastFm.on('click', function () {
		var $target = $audioLastFmApi.closest('.form-group');
		this.checked ? $target.show() : $target.hide();
	});

	$video.triggerHandler('click');
	$videoUpload.triggerHandler('click');
	$videoConversionMode.triggerHandler('click');

	$audio.triggerHandler('click');
});

(function ($) {
	var $aws = $('#videos_enable_aws_s3_elastic_transcoder');
	var $awsId = $('#field_videos_aws_access_key_id');
	var $awsKey = $('#field_videos_aws_secret_access_key');
	var $awsBucket = $('#field_videos_aws_s3_bucket');
	var $awsBucketLocation = $('#field_videos_aws_region');
	var $awsTrancoderPipeline = $('#field_videos_aws_elastic_transcoder_pipeline');
	var $awsTranscoderPreset = $('#field_videos_aws_elastic_transcoder_preset');
	var $awsRemoveLocalCopy = $('#field_videos_aws_s3_not_keep');

	$aws.on('click', function () {
		var $enable = $('#field_videos_aws_msg').closest('.form-group');
		var $target = $enable.next().nextAll();
		if (this.checked) {
			$awsId.show();
			$awsKey.show();
			$awsBucket.show();
			$awsBucketLocation.show();
			$awsTrancoderPipeline.show();
			$awsTranscoderPreset.show();
			$awsRemoveLocalCopy.show();

			$target.hide();
			$('#field_videos_limits_separator').show();
			$('#field_videos_limits_separator').next().show();
			$('#field_videos_max_upload_size').show();
			$('#field_videos_max_upload_size').next().show();
			$('#field_videos_allowed_user_space').show();
			$('#field_videos_allowed_user_space').next().show();

			$('#field_videos_uploads_warning').parent().parent().hide();
			$('#field_aws_elastic_transcoder_warning').parent().parent().show();
		} else {
			$awsId.hide();
			$awsKey.hide();
			$awsBucket.hide();
			$awsBucketLocation.hide();
			$awsTrancoderPipeline.hide();
			$awsTranscoderPreset.hide();
			$awsRemoveLocalCopy.hide();

			$target.show();

			$('#field_videos_uploads_warning').parent().parent().show();
			$('#field_aws_elastic_transcoder_warning').parent().parent().hide();
		}
	});
	$aws.triggerHandler('click');
})(jQuery);
