<?php

if(class_exists('PeepSoMaintenanceFactory')) {
    class PeepSoMaintenanceVideos extends PeepSoMaintenanceFactory
    {
    	public static function deleteTemporaryVideo()
        {
            // cleanup attachment
	        $attachments = get_posts( array(
	            'post_type' => 'attachment',
	            'posts_per_page' => -1,
	            'post_parent' => 0,
	            'meta_query' => array(
	            	array(
		                'key' => PeepSoVideos::POST_META_KEY_VIDEO_ATTACHMENT_TYPE,
		                'value' => PeepSoVideos::ATTACHMENT_TYPE_VIDEO_TEMPORARY, // IN THIS CASE IT SHOULD BE 12AB1324
		                'compare' => '='
		            )
	            )
	        ) );

	        if ( $attachments ) {
	            $force_delete = true;
	            foreach ( $attachments as $attachment ) {
	                wp_delete_attachment( $attachment->ID, $force_delete );
	            }
	        }
        }

        public static function deleteFailedConversionVideo()
        {

	        // cleanup failed convert video
	        $videos_model = new PeepSoVideosModel();
	        $videos = $videos_model->get_failed_convert_video();

	        $activity = new PeepSoActivity();
	        foreach ($videos as $video) {

	        	if ($video->vid_upload_s3_status == 0) {

		        	if ($video->vid_conversion_status == PeepSoVideosUpload::STATUS_PENDING) {
		                $file_source = $videos_model->get_tmp_dir() . basename($video->vid_url);

		                if (file_exists($file_source)) {
		                    unlink($file_source);
		                }
		            }

		            $attachments = get_posts( array(
		                'post_type' => 'attachment',
		                'posts_per_page' => -1,
		                'post_parent' => $video->vid_post_id
		            ) );

		            if ( $attachments ) {
		                $force_delete = true;
		                foreach ( $attachments as $attachment ) {
		                    wp_delete_attachment( $attachment->ID, $force_delete );
		                }
		            }
		        } else {
		        	// delete file s3

		        	$video_upload = PeepSoVideosUpload::get_instance();
		        	$video_upload::delete_file_tmp_from_s3($video->vid_url);
		        }


	            $activity->delete_post($video->vid_post_id);
	        }
        }
    }
}
