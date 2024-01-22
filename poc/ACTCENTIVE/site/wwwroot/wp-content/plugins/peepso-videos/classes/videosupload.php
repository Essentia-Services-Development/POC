<?php

class PeepSoVideosUpload
{
	private static $_instance = NULL;

    // values for the `request_status` column
    const STATUS_PENDING = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_DELAY = 2;
    const STATUS_FAILED = 3;
    const STATUS_SUCCESS = 4;
    const STATUS_READY = 5;
    const STATUS_RETRY = 6;
    const STATUS_REJECT = 7;

    const STATUS_S3_WAITING = 1;
    const STATUS_S3_INPROGRESS = 2;
    const STATUS_S3_RETRY = 3;
    const STATUS_S3_COMPLETE = 4;
    const STATUS_S3_FAILED = 5;

    public static $allowed_mime_types = array(
        // video
        'asf|asx' => 'video/x-ms-asf',
        'wmv' => 'video/x-ms-wmv',
        'wmx' => 'video/x-ms-wmx',
        'wm' => 'video/x-ms-wm',
        'avi' => 'video/avi',
        'divx' => 'video/divx',
        'flv|f4v|f4p|f4a|f4b' => 'video/x-flv',
        'mov|qt' => 'video/quicktime',
        'mpeg' => 'video/mpeg',
        'mp4' => 'video/mp4',
        'ogv' => 'video/ogg',
        'webm' => 'video/webm',
        'mkv' => 'video/x-matroska',
        '3gp' => 'video/3gpp',
        '3g2' => 'video/3gpp2',
        // audio
        'mp2|mp3|mpga' => 'audio/mpeg',
        'aac' => 'audio/aac',
        'ra|ram' => 'audio/x-realaudio',
        'wav' => 'audio/wav',
        'oga|ogg|spx|opus' => 'audio/ogg',
        'flac' => 'audio/flac',
        'mid|midi' => 'audio/midi',
        'wma' => 'audio/x-ms-wma',
        'wax' => 'audio/x-ms-wax',
        'mka' => 'audio/x-matroska',
        'm4a' => 'audio/m4a',
    );

	private function __construct()
	{

	}

	public static function get_instance()
	{
		if (NULL === self::$_instance)
			self::$_instance = new self();
		return (self::$_instance);
	}

    public static function do_upload_to_s3() 
    {
        global $wpdb;

        echo "<pre>";

        $videos_model = new PeepSoVideosModel();
        $video = $videos_model->get_list_upload_to_s3();

        if ($video) {

            // upload to s3 if configuration enabled and not an audio file
            $conversion_mode = PeepSo::get_option('videos_conversion_mode', 'no');
            if ($conversion_mode == 'aws_elastic') {

                echo "Start upload video to s3.\n";

                $file = str_replace(PeepSo::get_peepso_uri(), PeepSo::get_peepso_dir(), $video->vid_url);

                $failed = TRUE;
                $failed_msg = __('Failed upload video to s3.', 'vidso');
                if (file_exists($file)) {
                    $vid_token = self::upload_to_amazon_s3($file);

                    if (NULL !== $vid_token) {
                        echo "File " . $file . " uploaded to s3.\n";
                        
                        // create transcoder jobs
                        echo "Create transcoder job for uploaded file.\n";
                        $job = self::elastictranscode_transcode($file);
                        $vid_size = @filesize($file);

                        // get the job data as array
                        $job_data = $job->get('Job');

                        // you can save the job ID somewhere, so you can check 
                        // the status from time to time.
                        $job_id = isset($job_data['Id']) ? $job_data['Id'] : '';
                        $failed_msg = __('Failed to create transcoder job.', 'vidso');

                        if (!empty($job_id)) {
                            echo "Successfully create transcoder job with id : ".$job_id.".\n";
                            // update status to inprogress
                            $vid_data = array(
                                'vid_url' => $vid_token,
                                'vid_size' => intval($vid_size),
                                'vid_transcoder_job_id' => $job_id,
                                'vid_upload_s3_status' => self::STATUS_S3_COMPLETE
                            );

                            $where = array(
                                'vid_id' => $video->vid_id,
                                'vid_post_id' => $video->vid_post_id
                            );

                            $format_data = array(
                                '%s',
                                '%d',
                                '%s',
                                '%d'
                            );

                            $format_where = array(
                                '%d',
                                '%d'
                            );

                            $wpdb->update($wpdb->prefix . PeepSoVideosModel::TABLE, $vid_data, $where);

                            $failed = FALSE;
                            $failed_msg = '';
                        }

                        $remove_local_copy = PeepSo::get_option('videos_aws_s3_not_keep', FALSE);
                        if ($remove_local_copy) {
                            unlink($file);
                        }
                    }
                }

                if ($failed) {

                    $retry_count = $video->vid_upload_s3_retry_count + 1;
                    $s3_status = self::STATUS_S3_RETRY;

                    // update status to inprogress
                    $vid_data = array(
                        'vid_upload_s3_status' => $s3_status,
                        'vid_upload_s3_retry_count' => $retry_count,
                        'vid_error_messages' => $failed_msg
                    );
                    $format_data = array(
                        '%d',
                        '%d',
                        '%s'
                    );


                    if ($retry_count >= 3) {
                        $s3_status = self::STATUS_S3_FAILED;

                        $vid_data['vid_stored_failed'] = 1;
                        $vid_data['vid_upload_s3_status'] = $s3_status;
                        $vid_data['vid_conversion_status'] = self::STATUS_FAILED;

                        $format_data = array_merge($format_data, array('%d', '%s'));
                    }

                    $where = array(
                        'vid_id' => $video->vid_id,
                        'vid_post_id' => $video->vid_post_id
                    );

                    $format_where = array(
                        '%d',
                        '%d'
                    );

                    $wpdb->update($wpdb->prefix . PeepSoVideosModel::TABLE, $vid_data, $where);
                }
            }
        }

        echo "</pre>";
    }

    /**
     * Get Amazon S3 object
     * @return mixed object Aws\S3\S3Client if successful otherwise NULL if failed
     */
    private static function get_amazon_s3()
    {
        $aws_access_key_id = PeepSo::get_option('videos_aws_access_key_id');
        $aws_secret_access_key = PeepSo::get_option('videos_aws_secret_access_key');
        $aws_s3_bucket = PeepSo::get_option('videos_aws_s3_bucket');
        if (empty($aws_access_key_id) || empty($aws_secret_access_key) || empty($aws_s3_bucket)) {
            $_aws_error = __('Missing Amazon configuration.', 'vidso');
            
            self::$notices[] = $_aws_error;
            new PeepSoError('[VIDEOS] '.$_aws_error);

            return (NULL);
        }

        // disable auto discovery for default config
        if (!defined('AWS_DISABLE_CONFIG_AUTO_DISCOVERY')) {
            define('AWS_DISABLE_CONFIG_AUTO_DISCOVERY', TRUE);
        }

        require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'lib/aws_v3/aws-autoloader.php');

        try {
            $s3 = new Aws\S3\S3Client(array(
                'version'     => 'latest',
                'region'      => PeepSo::get_option('videos_aws_region', 'us-east-1'),
                'credentials' => array(
                    'key'    => $aws_access_key_id,
                    'secret' => $aws_secret_access_key
                )
            ));
            
            $bucket_exists = FALSE;
            $buckets = $s3->listBuckets();

            foreach ($buckets['Buckets'] as $bucket){
                if ($bucket['Name'] == $aws_s3_bucket) {
                    $bucket_exists = TRUE;
                }
            }

            if (!$bucket_exists) {
                $error = 'Bucket ' . $aws_s3_bucket . ' is not exists.';
                throw new Exception($error);
            }

            $resp = $s3->getBucketAcl([
                'Bucket' => $aws_s3_bucket
            ]);
        } catch (Aws\Exception\AwsException $e) {
            $_aws_error = $e->getMessage();
            new PeepSoError('[VIDEOS] '.$_aws_error);

            // persist the AWS error
            $aws_errors = new PeepSoVideosAWSErrors();
            $aws_errors->add_error($_aws_error);

            return NULL;
        } catch (Exception $e) {
            $_aws_error = $e->getMessage();
            new PeepSoError('[VIDEOS] '.$_aws_error);

            // persist the AWS error
            $aws_errors = new PeepSoVideosAWSErrors();
            $aws_errors->add_error($_aws_error);

            return NULL;
        }

        return ($s3);
    }

    /**
     * Upload object/file to Amazon S3 bucket
     * @param  string $filepath absolute path of the file to be uploaded
     * @return mixed public URL if successful otherwise NULL if upload failed
     */
    public static function upload_to_amazon_s3($filepath)
    {
        $s3 = self::get_amazon_s3();
        if (NULL === $s3) {
            return (NULL);
        }

        // replace peepso absolute dir with just peepso
        // replace \ with / for windows environment
        $filename = str_replace('\\', '/', 'peepso/' . substr($filepath, strlen(PeepSo::get_peepso_dir())));

        try {
            $result = $s3->putObject([
                'Bucket'        => PeepSo::get_option('videos_aws_s3_bucket'),
                'Key'           => $filename,
                'SourceFile'    => $filepath,
                'ACL'           => 'public-read'
            ]);
    
            $metadata = $result->get('@metadata');
            if ($metadata['statusCode'] != 200) {
                $_aws_error = __('There was a problem when uploading the file', 'vidso');
                
                $aws_errors = new PeepSoVideosAWSErrors();
                $aws_errors->add_error($_aws_error);

                new PeepSoError('[VIDEOS] '.$_aws_error);
            } else {
                return $metadata['effectiveUri'];
            }
        } catch (Aws\Exception\AwsException $e) {
            $_aws_error = $e->getMessage();
            new PeepSoError('[VIDEOS] '.$_aws_error);

            $aws_errors = new PeepSoVideosAWSErrors();
            $aws_errors->add_error($_aws_error);
        }
        
        return (NULL);
    }

    public static function delete_file_tmp_from_s3($file) 
    {
        $s3 = self::get_amazon_s3();
        if (NULL === $s3) {
            return (NULL);
        }
        $aws_region = PeepSo::get_option('videos_aws_region', 'us-east-1');
        $aws_s3_bucket = PeepSo::get_option('videos_aws_s3_bucket');
        
        $s3_url = "https://".$aws_s3_bucket.".s3.amazonaws.com/";

        $filename = str_replace('\\', '/', substr($file, strlen($s3_url)));

        try {
            $result = $s3->putObject([
                'Bucket' => PeepSo::get_option('videos_aws_s3_bucket'),
                'Key'    => $filename
            ]);
    
            $metadata = $result->get('@metadata');
            if ($metadata['statusCode'] != 200) {
                $_aws_error = __('There was a problem when deleting file', 'vidso');
                
                $aws_errors = new PeepSoVideosAWSErrors();
                $aws_errors->add_error($_aws_error);

                new PeepSoError('[VIDEOS] '.$_aws_error);
            } else {
                return $metadata['effectiveUri'];
            }
        } catch (Aws\Exception\AwsException $e) {
            $_aws_error = $e->getMessage();
            new PeepSoError('[VIDEOS] '.$_aws_error);

            $aws_errors = new PeepSoVideosAWSErrors();
            $aws_errors->add_error($_aws_error);
        }

        return (NULL);
    }

    /**
     * Elastic transcoder Job
     */
    public static function elastictranscode_transcode($file) {
        require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'lib/aws_v3/aws-autoloader.php');

        $aws_access_key_id = PeepSo::get_option('videos_aws_access_key_id');
        $aws_secret_access_key = PeepSo::get_option('videos_aws_secret_access_key');
        $aws_s3_bucket = PeepSo::get_option('videos_aws_s3_bucket');
        $aws_transcoder_pipeline = PeepSo::get_option('videos_aws_elastic_transcoder_pipeline');
        $aws_transcoder_preset = PeepSo::get_option('videos_aws_elastic_transcoder_preset');
        $aws_region = PeepSo::get_option('videos_aws_region', 'us-east-1');

        $subdir = str_replace('\\', '/', 'peepso/' . substr($file, strlen(PeepSo::get_peepso_dir())));
        $subdir = str_replace('/tmp/', '/', $subdir);
        $subdir = str_replace(basename($file), '', $subdir);


        $filepath = str_replace('\\', '/', 'peepso/' . substr($file, strlen(PeepSo::get_peepso_dir())));

        $thumbnail_filename = substr(basename($file), 0, strpos(basename($file), '.'));


        $elasticTranscoder = Aws\ElasticTranscoder\ElasticTranscoderClient::factory(array(
            'credentials' => array(
                    'key'    => $aws_access_key_id,
                    'secret' => $aws_secret_access_key
                ),
            'version'     => 'latest',
            'region'      => $aws_region,
        ));

        try {
            $job = $elasticTranscoder->createJob(array(
                'PipelineId' => $aws_transcoder_pipeline,
                'OutputKeyPrefix' => $subdir,
                'Input' => array(
                    'Key' => $filepath,
                    'FrameRate' => 'auto',
                    'Resolution' => 'auto',
                    'AspectRatio' => 'auto',
                    'Interlaced' => 'auto',
                    'Container' => 'auto',
                ),
                'Outputs' => array(
                    array(
                        'ThumbnailPattern' => $thumbnail_filename . 'thumb{count}',
                        'Key' => $thumbnail_filename . '.mp4',
                        'Rotate' => 'auto',
                        'PresetId' => $aws_transcoder_preset,
                    ),
                ),
            )); 
        } catch (Exception $e) {

            $_aws_error = __('There was a problem when creating transcoder job.', 'vidso');

            // persist the AWS error
            $aws_errors = new PeepSoVideosAWSErrors();
            $aws_errors->add_error($_aws_error);

            new PeepSoError('[VIDEOS] '.$_aws_error);
        }

        return $job;
    }

	/**
	 * Convert videos
	 */
	public static function convert_videos()
	{
        echo "<pre>";
        $conversion_mode = PeepSo::get_option('videos_conversion_mode', 'no');
        if ($conversion_mode == 'aws_elastic') {
            echo "Checking jobs ...\n";
            self::check_jobs();
            echo "checking jobs completed\n";
            die();
        }

        $ffmpeg_bin = PeepSo::get_option('videos_ffmpeg_binary', '');
        
        if (function_exists('shell_exec')) {
            $exec_function = 'shell_exec';
        } elseif (function_exists('exec')) {
            $exec_function = 'exec';
        } else {
            $err = __('Missing shell_exec() or exec()', 'vidso');
            die($err);
        }

        $checkffmpeg = self::exec_function($exec_function, $ffmpeg_bin . ' -version 2>&1');
        if ($checkffmpeg == NULL || strpos($checkffmpeg, 'ffmpeg version') === false) {
            die('Could not load FFmpeg');
        }

        $videos_model = new PeepSoVideosModel();
        $video = $videos_model->get_unconverted_video();
        if (empty($video)) {
            $err = __('Nothing to convert?', 'vidso');
            die($err);
        }
        echo "Start convert self hosted videos.\n";
        register_shutdown_function(array('PeepSoVideosUpload', 'shutdown'), $video);

        $vid_id = $video->vid_id;
        $vid_album_id = $video->vid_album_id;
        $vid_post_id = $video->vid_post_id;
        $vid_url = $video->vid_url;
        $vid_thumbnail = $video->vid_thumbnail;
        $vid_act_id = $video->act_id;

        $video_upload = PeepSoVideosUpload::get_instance();
        $video_upload->update_status($vid_post_id, $vid_id, self::STATUS_PROCESSING);

        $file_source = $videos_model->get_video_dir($video->post_author) . 'tmp' . DIRECTORY_SEPARATOR . basename($vid_url);

        // echo $file_source;
        if (!file_exists($file_source)) {
            $err = __('Source file not found', 'vidso');
            $video_upload->update_failed_convert($vid_post_id, $vid_id, $err);
            die($err);
        }

        $filename = basename($file_source);
        $filetype = wp_check_filetype($filename, PeepSoVideosUpload::$allowed_mime_types);
        $filename_video = str_replace('.' . $filetype['ext'], '.mp4', $filename);
        $filename_animated = str_replace('.' . $filetype['ext'], '.gif', $filename);
        $filename_animated_webm = str_replace('.' . $filetype['ext'], '.webm', $filename);
        $filename_poster = str_replace('.' . $filetype['ext'], '.jpg', $filename);

        $file_dest_path = $videos_model->get_video_dir($video->post_author);
        $file_dest_video_orig = $file_dest_path . wp_unique_filename($file_dest_path, 'original_' . $filename_video);
        $file_dest_animated = $file_dest_path . wp_unique_filename($file_dest_path, $filename_animated);
        $file_dest_animated_webm = $file_dest_path . wp_unique_filename($file_dest_path, $filename_animated_webm);
        $file_dest_video = $file_dest_path . wp_unique_filename($file_dest_path, $filename_video);
        $file_dest_poster = $file_dest_path . wp_unique_filename($file_dest_path, $filename_poster);

        echo "<pre>";
        $video_size_config = PeepSo::get_option('videos_specific_size', 720);
        switch (intval($video_size_config)) {
            case 480:
                // $video_size = '480x320';
                $video_size = '-2:480';
                break;
            case 640:
                // $video_size = '640x480';
                $video_size = '-2:640';
                break;
            case 720:
                // $video_size = '1260x720';
                $video_size = '-2:720';
                break;
            case 1080:
                // $video_size = '1920x1080';
                $video_size = '-2:1920';
                break;
            case 2160:
                // $video_size = '3840x2160';
                $video_size = '-2:3840';
                break;
            default:
                // $video_size = '720x576';
                $video_size = '-2:720';
                break;
        }

        // never upscale video
        $ffprobe_bin = PeepSo::get_option('videos_ffprobe_binary', '');
        if(PeepSo::get_option('videos_never_upscale', 0) && !empty($ffprobe_bin)) {
            $checkffprobe = self::exec_function($exec_function, $ffprobe_bin . ' -version 2>&1');

            if ($checkffprobe != NULL && strpos($checkffprobe, 'ffprobe version') !== FALSE) {
                $videoconfigsize = explode(':', $video_size);
                $vidconfigheight = $videoconfigsize[1];

                $vidactualheight = self::exec_function($exec_function, $ffprobe_bin . ' -v error -select_streams v:0 -show_entries stream=height -of csv=s=x:p=0 ' . $file_source . ' 2>&1');

                if (intval($vidactualheight) < $vidconfigheight) {
                    $arrvidsize = array(480, 640, 720, 1080, 2160);
                    $closestsize = $video_upload->get_closest(intval($vidactualheight), $arrvidsize);
                    $video_size = '-2:' . $closestsize;
                }
            }
        }

        $ffmpeg_extra_param = PeepSo::get_option('videos_ffmpeg_extra_param', '');
        echo self::exec_function($exec_function, $ffmpeg_bin . ' -y -i ' . $file_source . ' -c:v libx264 ' . $file_dest_video_orig . ' ' . $ffmpeg_extra_param . ' 2>&1');
        // echo $ffmpeg_bin . ' -y -i ' . $file_source . ' -c:v libx264 ' . $file_dest_video_orig . ' 2>&1';
        
        $upload = TRUE;
        $vid_filesize = 0;
        try {
            // check the video if broken or not
            $vid_filesize = @filesize($file_dest_video_orig);
            if ( $vid_filesize === FALSE ) {
                $upload = FALSE;
            }
        } catch (Exception $e) {
            $upload = FALSE;
        }

        if ($upload || $vid_filesize === 0) {
            echo self::exec_function($exec_function, $ffmpeg_bin . ' -y -i ' . $file_dest_video_orig . ' -vf "scale=' . $video_size . '" ' . $file_dest_video . ' ' . $ffmpeg_extra_param . ' 2>&1');
            // echo ($ffmpeg_bin . ' -y -i ' . $file_dest_video_orig . ' -vf scale=' . $video_size . ' ' . $file_dest_video . ' 2>&1');

            try {
                // check the video if broken or not
                $vid_filesize = @filesize($file_dest_video);
                if ( $vid_filesize === FALSE ) {
                    $upload = FALSE;
                }
            } catch (Exception $e) {
                $upload = FALSE;
            }
        }

        if ($upload === FALSE || $vid_filesize === 0) {
            // update video conversion to failed
            $video_upload->update_failed_convert($vid_post_id, $vid_id);

            // notify owner that video can't be converted
            $video_upload->notify_owner($vid_post_id, $video->post_author, FALSE);

            // clean up generated video
            if (file_exists($file_dest_video_orig)) {
                unlink($file_dest_video_orig);
            }

            if (file_exists($file_dest_video)) {
                unlink($file_dest_video);
            }

            $err = __('Failed to convert videos.', 'vidso');
            $video_upload->update_failed_convert($vid_post_id, $vid_id, $err);
            die($err);
        }

        /* Upload as post attachment, so it can displayed on wp media library */
        // remove original attachement
        if (intval(PeepSo::get_option('videos_remove_original', 1)) === 1) {
            $attachments = get_posts( array(
                'post_type' => 'attachment',
                'posts_per_page' => -1,
                'post_parent' => $vid_post_id
            ) );

            if ( $attachments ) {
                $force_delete = true;
                foreach ( $attachments as $attachment ) {
                    wp_delete_attachment( $attachment->ID, $force_delete );
                }
            }
        }

        $video_attachement_id = $video_upload->upload_as_attachment($video->post_author, $vid_post_id, $file_dest_video, $video->vid_title, PeepSoVideos::ATTACHMENT_TYPE_VIDEO);
        $video_url = wp_get_attachment_url( $video_attachement_id );
        $attr = array(
            'src' => $video_url,
        );

        add_filter('wp_video_extensions', ['PeepSoVideosUpload','wp_video_extensions']);
        $embed_code = wp_video_shortcode( $attr );
        remove_filter('wp_video_extensions', ['PeepSoVideosUpload','wp_video_extensions']);

        $embed_code = str_replace(' controls="controls"', ' controls="controls" controlslist="nodownload"', $embed_code);

        /* Generate Animated GIF*/
        $animated_url = '';
        $animated_webm_url = '';
        if (intval(PeepSo::get_option('videos_generate_animated_gif', 1)) === 1) {
            $vidscale = explode(':', $video_size);
            $vidscale = isset($vidscale[1]) ? $vidscale[1] : '720';
            $vidscale = '480';

            $animated_output = PeepSo::get_option('videos_animated_output', 'gif');
            if ($animated_output == 'gif' || $animated_output == 'both') {
                echo self::exec_function($exec_function, $ffmpeg_bin . ' -y -i ' . $file_dest_video . ' -filter_complex "[0:v] fps=12,scale=-2:' . $vidscale . '" -r 1 ' . $file_dest_animated . ' 2>&1');
                if (file_exists($file_dest_animated)) {
                    $animated_title = $video->vid_title . ' ' . __('Animated GIF', 'vidso');
                    $animated_attachement_id = $video_upload->upload_as_attachment($video->post_author, $vid_post_id, $file_dest_animated, $animated_title, PeepSoVideos::ATTACHMENT_TYPE_ANIMATED_GIF);
                    $animated_url = wp_get_attachment_url( $animated_attachement_id );

                    if (file_exists($file_dest_animated)) {
                        unlink($file_dest_animated);
                    }
                }
            }

            if ($animated_output == 'webm' || $animated_output == 'both') {
                echo self::exec_function($exec_function, $ffmpeg_bin . ' -y -i ' . $file_dest_video . ' -an -vf scale=-2:' . $vidscale . ' -vcodec libvpx -acodec libvorbis ' . $file_dest_animated_webm . ' 2>&1');
                if (file_exists($file_dest_animated_webm)) {
                    $animated_webm_title = $video->vid_title . ' ' . __('Animated WEBM', 'vidso');
                    $animated_attachement_id = $video_upload->upload_as_attachment($video->post_author, $vid_post_id, $file_dest_animated_webm, $animated_webm_title, PeepSoVideos::ATTACHMENT_TYPE_ANIMATED_WEBM);
                    $animated_webm_url = wp_get_attachment_url( $animated_attachement_id );

                    if (file_exists($file_dest_animated_webm)) {
                        unlink($file_dest_animated_webm);
                    }
                }
            }
        }

        /* poster */
        $poster_url = '';
        if (intval(PeepSo::get_option('videos_generate_poster', 1)) === 1) {
            if (!isset($vidscale)) {
                $vidscale = explode(':', $video_size);
                $vidscale = isset($vidscale[1]) ? $vidscale[1] : '720';
            }

            // get duration
            $time = '';
            $ffprobe_bin = PeepSo::get_option('videos_ffprobe_binary', '');
            if (!empty($ffprobe_bin)) {
                $checkffprobe = self::exec_function($exec_function, $ffprobe_bin . ' -version 2>&1');
                if ($checkffprobe != NULL && strpos($checkffprobe, 'ffprobe version') !== FALSE) {
                    $time =  exec($ffprobe_bin . " " . $file_dest_video . " -show_entries -sexagesimal format=duration -of compact=p=0:nk=1 -v 0 2>&1");
                }
                
            }

            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && empty($time)) {
                $time =  exec($ffmpeg_bin . " -y -i " . $file_dest_video . " 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//");
            }

            // duration in seconds; half the duration = middle
            $duration = explode(":", $time);
            if (count($duration) == 3) {
                $durationInSeconds = $duration[0]*3600 + $duration[1] * 60 + round($duration[2]);
                $durationMiddle = $durationInSeconds/2;
            } else {
                $durationMiddle = 1;
            }

            // recalculte to minutes and seconds
            // $minutes = $durationMiddle/60;
            // $realMinutes = floor($minutes);
            // $realSeconds = round(($minutes-$realMinutes)*60);

            echo self::exec_function($exec_function, $ffmpeg_bin . ' -ss ' . $durationMiddle . ' -y -i ' .  $file_dest_video . ' -vf "scale=-2:' . $vidscale . '" -f mjpeg -vframes 1 ' . $file_dest_poster. ' 2>&1');

            if (file_exists($file_dest_poster)) {
                $image = wp_get_image_editor($file_dest_poster);
                $video_upload->fix_image_orientation($image, $file_dest_poster);   // reorient once copied - so we have write access

                $poster_title = $video->vid_title . ' ' . __('Poster', 'vidso');
                $poster_attachement_id = $video_upload->upload_as_attachment($video->post_author, $vid_post_id, $file_dest_poster, $poster_title, PeepSoVideos::ATTACHMENT_TYPE_POSTER);
                $poster_url = wp_get_attachment_url( $poster_attachement_id );

                if (file_exists($file_dest_poster)) {
                    unlink($file_dest_poster);
                }
            }
        }

        $vid_size = filesize($file_dest_video);
        $finish = $video_upload->finishing_upload_video($vid_post_id, $vid_id, $video_url, $embed_code, $vid_size, $poster_url, $animated_url, $animated_webm_url, $failed = 0);
        add_post_meta($vid_post_id, PeepSoVideos::POST_META_KEY_VIDEO_CONVERSION_DONE, TRUE, true);

        // $video_upload->publish_post_status($vid_post_id);
        $video_upload->notify_owner($vid_post_id, $video->post_author, TRUE);

        // remove source
        if(file_exists($file_source)) {
            unlink($file_source);
        }

        // remove original
        if (file_exists($file_dest_video_orig)) {
            unlink($file_dest_video_orig);
        }
        if (file_exists($file_dest_video)) {
            unlink($file_dest_video);
        }
        echo "</pre>";
	}

    public static function check_jobs() {
        $videos_model = new PeepSoVideosModel();
        $videos = $videos_model->get_unfinished_transcoder_job();

        $video_upload = PeepSoVideosUpload::get_instance();

        if(count($videos)) {
            foreach ($videos as $key => $video) {
                $job_id = $video->vid_transcoder_job_id;
                $vid_post_id = $video->vid_post_id;
                $vid_id = $video->vid_id;

                require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'lib/aws_v3/aws-autoloader.php');

                $aws_access_key_id = PeepSo::get_option('videos_aws_access_key_id');
                $aws_secret_access_key = PeepSo::get_option('videos_aws_secret_access_key');
                $aws_s3_bucket = PeepSo::get_option('videos_aws_s3_bucket');
                $aws_region = PeepSo::get_option('videos_aws_region', 'us-east-1');

                try {
                    $elasticTranscoder = Aws\ElasticTranscoder\ElasticTranscoderClient::factory(array(
                        'credentials' => array(
                            'key'    => $aws_access_key_id,
                            'secret' => $aws_secret_access_key
                        ),
                        'version'     => 'latest',
                        'region'      => $aws_region,
                    ));
                    
                    $job = $elasticTranscoder->readJob(array('Id' => $job_id));

                    if (isset($job['Job'])) {

                        $status = isset($job['Job']['Status']) ? $job['Job']['Status'] : '';

                        if ($status === 'Complete') {
                            $s3_url = "https://". $aws_s3_bucket . ".s3.amazonaws.com/" . $job['Job']['OutputKeyPrefix'];
                            $video_url = $s3_url . $job['Job']['Output']['Key'];

                            $attr = array(
                                'src' => $video_url,
                            );

                            add_filter('wp_video_extensions', ['PeepSoVideosUpload','wp_video_extensions']);
                            $embed_code = wp_video_shortcode( $attr );
                            remove_filter('wp_video_extensions', ['PeepSoVideosUpload','wp_video_extensions']);

                            $embed_code = str_replace(' controls="controls"', ' controls="controls" controlslist="nodownload"', $embed_code);


                            $poster_url = '';
                            if (isset($job['Job']['Output']['ThumbnailPattern'])) {
                                $thumbnail = str_replace('{count}', '00001', $job['Job']['Output']['ThumbnailPattern']);
                                
                                $poster_url = $s3_url . $thumbnail . '.jpg';
                                $poster_exists = $video_upload->file_exists_from_url($poster_url);
                                if(!$poster_exists) {
                                    $poster_url = $s3_url . $thumbnail . '.png';
                                    $poster_exists = $video_upload->file_exists_from_url($poster_url);
                                    if(!$poster_exists) {
                                        $poster_url = '';
                                    }
                                }
                            }

                            $animated_url = '';
                            $animated_webm_url = '';

                            $vid_size = $job['Job']['Output']['FileSize'];

                            $finish = $video_upload->finishing_upload_video($vid_post_id, $vid_id, $video_url, $embed_code, $vid_size, $poster_url, $animated_url, $animated_webm_url, $failed = 0);
                            add_post_meta($vid_post_id, PeepSoVideos::POST_META_KEY_VIDEO_CONVERSION_DONE, TRUE, true);

                            // $video_upload->publish_post_status($vid_post_id);
                            $video_upload->notify_owner($vid_post_id, $video->post_author, TRUE);

                            self::delete_file_tmp_from_s3($video->vid_url);

                        } elseif ($status === 'Error') {
                            # code...
                            $error_msg = isset($job['Job']['Output']['StatusDetail']) ? $job['Job']['Output']['StatusDetail'] : '';

                            $err = __('Transcoder Job Failed: ', 'vidso');
                            $err = $err . $error_msg;
                            

                            $video_upload->notify_owner($video->vid_post_id, $video->post_author, FALSE);
                            $video_upload->update_failed_convert($video->vid_post_id, $video->vid_id, $err, FALSE);
                        }
                    }

                    echo "<pre>";
                    echo "Vid_id " . $video->vid_id . " converted.\n";
                    // var_dump($job);   
                    echo "</pre>";
                } catch (Exception $e) {
                    # code...

                    $err = __('Transcoder Read Job Failed: ', 'vidso');
                    $err = $err . $e->getMessage();
                    
                    $video_upload->update_failed_convert($video->vid_post_id, $video->vid_id, $err, FALSE);
                }
            }
        }

    }

    public static function shutdown($video) {
        if(!is_null($e = error_get_last()))
        {
            $conversion_done = get_post_meta($video->vid_post_id, PeepSoVideos::POST_META_KEY_VIDEO_CONVERSION_DONE, true);
            if (empty($conversion_done)) {
                $err = __('Server resources exceeded', 'vidso');
                $err = $err;
                if (is_array($e)) {
                    $file = isset($e['file']) ? $e['file'] : '';
                    $line = isset($e['line']) ? $e['line'] : '';
                    $message = isset($e['message']) ? $e['message'] : '';

                    $err = $err . "\n" . __('message:') . $message ;
                    $err = $err . "\n" . __('file:') . $file ;
                    $err = $err . "\n" . __('line:') . $line ;
                } else {
                    $err = $err . "\n" . __('message:') . $e ;
                }

                
                $video_upload = PeepSoVideosUpload::get_instance();
                $video_upload->update_failed_convert($video->vid_post_id, $video->vid_id, $err, FALSE);
            }
        }
    }

    public static function cleanup_temp()
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

        // cleanup failed convert video
        $videos_model = new PeepSoVideosModel();
        $videos = $videos_model->get_failed_convert_video();

        $activity = new PeepSoActivity();
        foreach ($videos as $video) {
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

            $activity->delete_post($video->vid_post_id);
        }

    }

    public static function delete_video($video_id) {
        $videomodel = new PeepSoVideosModel();
        $video = $videomodel->get_video($video_id);

        if($video !== NULL) {

            // delete file from AWS S3
            if ($video->vid_upload_s3_status == PeepSoVideosUpload::STATUS_S3_COMPLETE) {
                $video_upload = PeepSoVideosUpload::get_instance();

                if (!empty($video->vid_url)) {
                    $video_upload::delete_file_tmp_from_s3($video->vid_url);
                }

                if (!empty($video->vid_thumbnail)) {
                    $video_upload::delete_file_tmp_from_s3($video->vid_thumbnail);
                }
            }

            if ($video->vid_conversion_status == self::STATUS_PENDING) {
                $file_source = $videomodel->get_tmp_dir() . basename($video->vid_url);

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

            $activity = new PeepSoActivity();
            $activity->delete_post($video->vid_post_id);
        }
    }

	public function notify_owner($post_id, $post_author, $success=TRUE)
	{
		$user = PeepSoUser::get_instance($post_author);
        $note = new PeepSoNotifications();

        if ($success) {
            $vid_post = get_post($post_id);
            $data = array('permalink' => PeepSo::get_page('activity') . '?status/' . $vid_post->post_title);
            $data = array_merge($data, $user->get_template_fields('user'));

            $i18n = __('Your video is ready', 'vidso');
            $message = 'Your video is ready';
            $args = ['vidso'];
            PeepSoMailQueue::add_notification_new($post_author, $data, $message, $args, 'video_conversion_complete', 'video_conversion_complete', PeepSoVideos::MODULE_ID);
            $note->add_notification_new($post_author, $post_author, $message, $args,'video_conversion_complete', PeepSoVideos::MODULE_ID, $post_id);
        } else {
            $data = $user->get_template_fields('user');

            $i18n = __('Your video failed to convert', 'vidso');
            $message = 'Your video failed to convert';
            $args = ['vidso'];
            PeepSoMailQueue::add_notification_new($post_author, $data, $message, $args, 'video_conversion_failed', 'video_conversion_failed', PeepSoVideos::MODULE_ID);
            $note->add_notification_new($post_author, $post_author, $message, $args, 'video_conversion_failed', PeepSoVideos::MODULE_ID, $post_id);
        }
	}

	public function publish_post_status($post_id)
	{
        // update status to publish
        $vid_post = array(
            'ID'           => $post_id,
            'post_status'   => 'publish',
        );

        return wp_update_post( $vid_post );
	}

    public function update_status($post_id, $vid_id, $status=0)
    {
        global $wpdb;

        $vid_data = array(
            'vid_conversion_status' => $status
        );

        $where = array(
            'vid_id' => $vid_id,
            'vid_post_id' => $post_id
        );

        $format_data = array(
            '%d'
        );

        $format_where = array(
            '%d',
            '%d'
        );

        return $wpdb->update($wpdb->prefix . PeepSoVideosModel::TABLE, $vid_data, $where);
    }

    public function update_failed_convert($post_id, $vid_id, $err='', $is_retry=FALSE)
    {
        global $wpdb;

        $status = self::STATUS_FAILED;
        if ($is_retry) {
            $status = self::STATUS_RETRY;
        }

        $vid_data = array(
            'vid_stored_failed' => 1,
            'vid_conversion_status' => $status,
            'vid_error_messages' => $err
        );

        $where = array(
            'vid_id' => $vid_id,
            'vid_post_id' => $post_id
        );

        $format_data = array(
            '%d',
            '%d',
            '%s'
        );

        $format_where = array(
            '%d',
            '%d'
        );

        return $wpdb->update($wpdb->prefix . PeepSoVideosModel::TABLE, $vid_data, $where);
    }

    public function finishing_upload_video($post_id, $vid_id, $video, $embed_code, $vid_size, $thumbnail='', $animated='', $animated_webm='', $failed = 0)
    {
        global $wpdb;

        $vid_data = array(
            'vid_animated' => $animated,
            'vid_animated_webm' => $animated_webm,
            'vid_thumbnail' => $thumbnail,
            'vid_embed' => $embed_code,
            'vid_url' => $video,
            'vid_size' => $vid_size,
            'vid_stored' => 1,
            'vid_stored_failed' => 0,
            'vid_transcoder_job_id' => '',
            'vid_conversion_status' => self::STATUS_SUCCESS
        );

        $where = array(
            'vid_id' => $vid_id,
            'vid_post_id' => $post_id
        );

        $format_data = array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%d',
            '%d'
        );

        $format_where = array(
            '%d',
            '%d'
        );

        return $wpdb->update($wpdb->prefix . PeepSoVideosModel::TABLE, $vid_data, $where);
    }

    /**
     * Fix image orientation
     * @param object $image WP_Image_Editor
     * @param string $image_file Image filename/path
     * @return object $image WP_Image_Editor
     */
    public function fix_image_orientation(&$image, $image_file)
    {
        // @Since 1.7.4 the EXIF PHP extension is required
        // http://php.net/manual/en/function.exif-imagetype.php
        if (!function_exists('exif_read_data')) {
            return;
        }
        $exif = @exif_read_data($image_file);
        $orientation = isset($exif['Orientation']) ? $exif['Orientation'] : 0;

        $this->last_orientation = $orientation;

        $resave = FALSE;
        switch ($orientation)
        {
        case 3:
            $image->rotate(180);
            $resave = TRUE;
            break;
        case 6:
            $image->rotate(-90);
            $resave = TRUE;
            break;
        case 8:
            $image->rotate(90);
            $resave = TRUE;
            break;
        }
        if ($resave) {              // resave here if image was rotated
            $image->save();
        }
//      return ($image); // no need to return, passed in by reference
    }

    public static function exec_function( $exec_function, $command) 
    {
        if ($exec_function == 'shell_exec') {
            $result = $exec_function($command);
        } elseif ($exec_function == 'exec') {
            $exec_function($command, $output, $return);
            $result = FALSE;
            if(!$return) {
                $result = $output[0];
            }
        }

        return $result;
    }

    /**
     * Gets real MIME type and then see if its on allowed list
     * 
     * @param string $tmp : path to file
     */
    public function check_file_is_audio( $tmp ) 
    {   
        // check REAL MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $tmp );
        finfo_close($finfo);
        
        return $this->check_audio_by_type( $type );
    }

    public function check_audio_by_type ( $type ) {
        $allowed = array(
            'audio/mpeg', 'audio/x-mpeg', 'audio/mpeg3', 'audio/x-mpeg-3', 'audio/aiff', 
            'audio/mid', 'audio/x-aiff', 'audio/x-mpequrl','audio/midi', 'audio/x-mid', 
            'audio/x-midi','audio/wav','audio/x-wav','audio/xm','audio/x-aac','audio/basic',
            'audio/flac','audio/mp4','audio/x-matroska','audio/ogg','audio/s3m','audio/x-ms-wax',
            'audio/xm', 'audio/mp3'
        );
        
        // check to see if REAL MIME type is inside $allowed array
        if( in_array($type, $allowed) ) {
            return true;
        } else {
            return false;
        }
    }

    public function upload_as_attachment($post_author, $parent_post_id, $target_file, $title, $type)
    {
        $filename = basename($target_file);
        add_filter('upload_mimes', [$this,'upload_mimes'], 10, 2);
        $upload_file = wp_upload_bits($filename, null, file_get_contents($target_file));
        remove_filter('upload_mimes', [$this,'upload_mimes']);
        if (!$upload_file['error']) {

            $mimetypes = PeepSoVideosUpload::$allowed_mime_types;
            $mimetypes = array_merge($mimetypes, ['jpg|jpeg|jpe' => 'image/jpeg', 'gif' => 'image/gif']);

            $wp_filetype = wp_check_filetype($filename, $mimetypes);
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_parent' => $parent_post_id,
                'post_author' => $post_author,
                'post_title' => $title,
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $parent_post_id );
            if (!is_wp_error($attachment_id)) {
                require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                require_once( ABSPATH . 'wp-admin/includes/media.php' );
                $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
                wp_update_attachment_metadata( $attachment_id,  $attachment_data );

                // clean up meta key
                delete_post_meta($attachment_id, PeepSoVideos::POST_META_KEY_VIDEO_ATTACHMENT_TYPE);

                // add postmeta type attachment
                add_post_meta($attachment_id, PeepSoVideos::POST_META_KEY_VIDEO_ATTACHMENT_TYPE, $type, true);

                return $attachment_id;
            }
        }

        return false;
    }

    public function upload_mimes($mimes, $user) {
        $mimes = array_merge($mimes, PeepSoVideosUpload::$allowed_mime_types);
        return $mimes;
    }

    public function command_exist($cmd) {
        $return = shell_exec(sprintf("which %s", escapeshellarg($cmd)));
        return !empty($return);
    }

    public function get_closest($search, $arr) {
       $closest = null;
       foreach ($arr as $item) {
          if ($closest === null || abs($search - $closest) > abs($item - $search)) {
             $closest = $item;
          }
       }
       return $closest;
    }

    public function file_exists_from_url($url) {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_NOBODY, TRUE);
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
            if ($code == 200) {
                $status = TRUE;
            } else {
                $status = FALSE;
            }
            curl_close($ch);
        } else {
            $headers = get_headers($url);
            $status = FALSE;
            if(count($headers) > 0) {
                $http_status = substr($headers[0], 9, 3);
                if($http_status == '200') {
                    $status = TRUE;
                }
            }
        }

        return $status;
    }

    public static function no_conversion_mode_filetypes() {

        $allowed_extensions = PeepSo::get_option('videos_allowed_extensions', '');

        if (!empty($allowed_extensions)) {
            $allowed_extensions = str_replace("\r", '', $allowed_extensions);
            $allowed_extensions = explode("\n", $allowed_extensions);
            $test_allowed_extensions=[];
            if(is_array($allowed_extensions) && count($allowed_extensions)) {
                foreach ($allowed_extensions as $extension) {
                    $test_allowed_extensions[] = trim(strtolower($extension),' ,.');
                }

                return $test_allowed_extensions;
            } else {
                return [$allowed_extensions];
            }
        }

        return ['mp4'];
    }

    public static function wp_video_extensions($exts) {
        $custom_filetypes = self::no_conversion_mode_filetypes();
        if(is_array($custom_filetypes) && count($custom_filetypes)) {
            foreach($custom_filetypes as $ext) {
                $exts[] = $ext;
            }
        }

        return $exts;
    }
}

// EOF
