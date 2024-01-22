<?php

class PeepSoConfigSectionvideos extends PeepSoConfigSectionAbstract {

    public function register_config_groups() {
        $this->context = 'left';
        $this->video();


        $this->video_uploads();
        $this->audio_uploads();
        $this->media_library();

        $this->context = 'right';
        if(!PeepSo3_Helper_Addons::license_is_free_bundle(FALSE)) {
            $this->aws();
            $this->warnings();
        }
    }

    private function video() {
        $this->args('descript',__('When enabled users are able to link and/or upload videos.', 'vidso'));
        $this->args('default', TRUE);
        $this->set_field(
            'videos_video_master_switch',
            __('Enabled', 'vidso'),
            'yesno_switch'
        );

        $this->args('descript',__('When enabled, videos will be autoplayed when visible on activity stream.', 'vidso'));
        $this->args('default', FALSE);
        $this->set_field(
            'videos_autoplay',
            __('Autoplay on activity stream', 'vidso'),
            'yesno_switch'
        );

        $this->args('descript',__('When enabled, PeepSo will attempt fo force an inline player on mobile devices. The results may vary depending on video providers.', 'vidso'));
        $this->args('default', FALSE);
        $this->set_field(
            'videos_play_inline',
            __('Play inline on mobile', 'vidso'),
            'yesno_switch'
        );


        // Build Group
        $this->set_group(
            'videos', __('Video', 'vidso')
        );
    }

    /**
     * Add this addon's configuration options to the admin section
     * @param  array $config_groups
     * @return array
     */
    private function video_uploads() {
        if(PeepSo3_Helper_Addons::license_is_free_bundle(FALSE)) {
            $this->set_field(
                'videos_upload_disabled',
                PeepSo3_Helper_Remote_Content::get('free_bundle_disabled_text'),
                'message'
            );
        } else {

            // WP max upload size
            $wp_max_size = max(wp_max_upload_size(), 0);
            $wp_max_size /= pow(1024, 2);

            // Enable uploads
            $this->args('default', '0');
            $this->args('int', TRUE);

            $this->set_field(
                'videos_upload_enable', __('Enabled', 'vidso'), 'yesno_switch'
            );

            // LIMITS

            $this->set_field('videos_limits_separator', __('Limits', 'vidso'), 'separator');

//        if(PeepSo::is_dev_mode('max_video_length')) {
//            $this->args('default', 0);
//            $this->args('validation', array('numeric', 'minval:0'));
//            $this->args('descript', 'In seconds. 0 to disable.'); #TODO translations
//            $this->set_field(
//                'videos_max_upload_length',
//                'Maximum video length',#TODO translations
//                'text'
//            );
//        }


            // $this->args('int', TRUE);
            $this->args('validation', array('numeric', 'minval:1'));
            $this->args('descript', sprintf(__('In megabytes - WordPress maximum upload size allowed is %1$sMB', 'vidso'), $wp_max_size));
            $this->set_field(
                'videos_max_upload_size',
                __('Maximum video upload size', 'vidso'),
                'text'
            );


            // $this->args('int', TRUE);
            $this->args('default', 0);
            $this->args('validation', array('numeric', 'minval:0'));
            $this->args('descript', __('In megabytes. 0 (zero) for no limit', 'vidso'));
            $this->set_field(
                'videos_allowed_user_space',
                __('Allowed space per user for video', 'vidso'),
                'text'
            );

            // Conversion
            $this->args('descript', __('Please bear in mind, video conversion is an advanced feature which carries a lot of technical requirements. It is recommended to test in on a staging copy of your website before deploying it live. By checking this field you confirm you understand all technical requirements that have to be met in order to use this feature, as outlined in the "important information" box.', 'vidso'));
            $this->args('options', [
                    'no' => 'No conversions (default)',
                    'aws_elastic' => 'Conversions with AWS Elastic Transcoder',
                    'ffmpeg' => 'Conversions with ffmpeg',
                ]
            );
            $this->set_field(
                'videos_conversion_mode', __('Advanced options', 'vidso'), 'select'
            );

            // # Predefined  Text
            $this->args('raw', true);
            $this->args('multiple', true);
            $this->args('descript', __('One per line, it will allow MP4 if empty. The only officially supported format at the moment is mp4.', 'vidso'));
            $this->set_field(
                'videos_allowed_extensions',
                __('Allowed file extensions', 'vidso'),
                'textarea'
            );


            // AWS ELASTIC TRANSCODER INTEGRATION

            $this->set_field('videos_elastic_transcoder_separator', __('Conversions with AWS Elastic Transcoder', 'vidso'), 'separator');

            $this->args('descript', __('The Access Key ID you received when you signed up for AWS (20 characters)', 'vidso'));
            $this->args('validation', array('custom'));
            $this->args('validation_options',
                [
                    [
                        'function' => array($this, 'validate_s3_requirements'),
                        'error_message' => __('Amazon Access Key ID is required', 'vidso')
                    ],
                ]
            );

            $this->set_field(
                'videos_aws_access_key_id',
                __('Amazon Access Key ID', 'vidso'),
                'text'
            );

            $this->args('validation', array('custom'));
            $this->args('validation_options',
                [
                    [
                        'function' => array($this, 'validate_s3_requirements'),
                        'error_message' => __('Amazon Secret Access Key is required', 'vidso')
                    ],
                ]
            );
            $this->set_field(
                'videos_aws_secret_access_key',
                __('Amazon Secret Access Key', 'vidso'),
                'text'
            );

            $options = array(
                'ap-south-1' => __('Asia Pacific (Mumbai)', 'vidso'),
                'ap-northeast-2' => __('Asia Pacific (Seoul)', 'vidso'),
                'ap-southeast-1' => __('Asia Pacific (Singapore)', 'vidso'),
                'ap-southeast-2' => __('Asia Pacific (Sydney)', 'vidso'),
                'ap-northeast-1' => __('Asia Pacific (Tokyo)', 'vidso'),
                'ca-central-1' => __('Canada (Central)', 'vidso'),
                'eu-central-1' => __('EU (Frankfurt)', 'vidso'),
                'eu-west-1' => __('EU (Ireland)', 'vidso'),
                'eu-west-2' => __('EU (London)', 'vidso'),
                'eu-west-3' => __('EU (Paris)', 'vidso'),
                'sa-east-1' => __('South America (São Paulo)', 'vidso'),
                'us-east-1' => __('US East (N. Virginia)', 'vidso'),
                'us-east-2' => __('US East (Ohio)', 'vidso'),
                'us-west-1' => __('US West (N. California)', 'vidso'),
                'us-west-2' => __('US West (Oregon)', 'vidso'),
            );

            $this->args('options', $options);
            $this->set_field(
                'videos_aws_region',
                __('AWS Region', 'vidso'),
                'select'
            );

            $this->args('descript', __('Name of the Bucket to upload your videos to on Amazon S3', 'vidso'));
            $this->args('validation', array('custom'));
            $this->args('validation_options',
                [
                    [
                        'function' => array($this, 'validate_s3_requirements'),
                        'error_message' => __('Amazon S3 Bucket is required', 'vidso')
                    ],
                ]
            );
            $this->set_field(
                'videos_aws_s3_bucket',
                __('Amazon S3 Bucket', 'vidso'),
                'text'
            );

            $this->args('validation', array('custom'));
            $this->args('validation_options',
                [
                    [
                        'function' => array($this, 'validate_elastic_transcoder'),
                        'error_message' => __('Amazon Elastic Transcoder Pipeline is required', 'vidso')
                    ],
                ]
            );
            $this->set_field(
                'videos_aws_elastic_transcoder_pipeline',
                __("Elastic Transcoder Pipeline Id", 'vidso'),
                'text'
            );

            $this->args('validation', array('custom'));
            $this->args('validation_options',
                [
                    [
                        'function' => array($this, 'validate_elastic_transcoder'),
                        'error_message' => __('Amazon Elastic Transcoder Preset is required', 'vidso')
                    ],
                ]
            );
            $this->set_field(
                'videos_aws_elastic_transcoder_preset',
                __("Elastic Transcoder Preset Id", 'vidso'),
                'text'
            );

            $this->set_field(
                'videos_aws_s3_not_keep',
                __("Don't keep a local copy of uploaded files", 'vidso'),
                'yesno_switch'
            );

            $summary = '';

            // add any AWS related errors to the Config box so admins can see them
            $aws_errors = new PeepSoVideosAWSErrors();
            $errors = $aws_errors->get_errors();
            if (!empty($errors)) {
                $url = admin_url('admin.php?page=peepso_config&tab=videos&clear-aws-history=1&nonce=' . wp_create_nonce('peepso-config-nonce'));
                $summary .= '<a href="' . $url . '" class="btn btn-primary">' . __('Clear history', 'vidso') . '</a><br/>';
                $summary .= '<b>' . __('AWS Error History:', 'vidso') . '</b><br/>';
                $format = get_option('date_format') . ' ' . get_option('time_format');
                foreach ($errors as $error) {
                    $msg = explode(':', $error, 2);
                    $summary .= date($format, $msg[0]) . ': ' . esc_html($msg[1]) . '<br/>';
                }
                $summary .= '&nbsp;<br/>';
            }

            $summary .= "To be sure that the integration works correctly, be sure to see our <a href=\"https://peep.so/docs_video_uploads_amazon_transcoder\" target=\"_blank\">documentation</a>. The usual reason for the integration not working are either misconfigured permissions for transcoder pipeline or a conflict with a 3rd party plugin.<br/><br/>";

            $summary .= sprintf(__('To enable the use of AWS, set the "Enable AWS S3 & Elastic Transcoder Integration" to "YES" and fill in the additional settings fields.<br/>To get your AWS Access ID and Keys, you can sign up here: %sGet Your AWS Access Keys%s.', 'vidso'),
                '<a href="https://docs.aws.amazon.com/IAM/latest/UserGuide/best-practices.html" target="_blank">',
                '</a>');

            $this->set_field(
                'videos_aws_msg',
                $summary,
                'message'
            );

            // FFMPEG AND CONVERSION


            $this->set_field('videos_ffmpeg_separator', __('Conversions with ffmpeg', 'vidso'), 'separator');

            $this->args('descript', __('The FFmpeg library is required for video conversions. Please ask your hosting provider about it.', 'vidso'));
            $this->set_field(
                'videos_ffmpeg_binary',
                __('Path to the FFmpeg binary', 'vidso'),
                'text'
            );

            $this->set_field(
                'videos_ffmpeg_extra_param',
                __('FFmpeg extra param', 'vidso'),
                'text'
            );

            $options = array(
                480 => '480p',
                640 => '640p',
                720 => '720p',
                1080 => '1080p',
                2160 => '2160p',
            );

            $this->args('descript', __('Applies to newly uploaded videos', 'vidso'));
            $this->args('options', $options);
            $this->args('default', '720');
            $this->set_field(
                'videos_specific_size', __('Output resolution', 'vidso'), 'select'
            );

            // Never upscale
            $this->args('descript', __('When enabled, source videos with resolution lower than the defined output resolution will not be upscaled. The nearest lower resolution will be preferred. This setting is recommended to save space.', 'vidso'));
            $this->args('default', FALSE);
            $this->set_field(
                'videos_never_upscale',
                __('Never upscale videos', 'vidso'),
                'yesno_switch'
            );

            $this->args('descript', __('The FFprobe library is required for video resolution analysis. Please ask your hosting provider about it.', 'vidso'));
            $this->set_field(
                'videos_ffprobe_binary',
                __('Path to the FFprobe binary', 'vidso'),
                'text'
            );

            // Delete original file
            $this->args('descript', __('Deleting the original file after conversion is recommended to save space on the server'));
            $this->args('default', TRUE);
            $this->set_field(
                'videos_remove_original', __('Delete original file after conversion', 'vidso'), 'yesno_switch'
            );


            // THUMBNAILS AND PREVIEWS


            $this->set_field('videos_thumb_separator', __('Thumbnails and previews', 'vidso'), 'separator');

            $this->args('descript', __('When enabled video preview thumbnail will be generated. With the option disabled, there will be no preview frame. It’ll just be black background with a play icon on it.', 'vidso'));
            $this->args('default', TRUE);
            $this->set_field(
                'videos_generate_poster',
                __('Generate poster videos', 'vidso'),
                'yesno_switch'
            );


            $this->args('descript', __('When enabled a quick few second gif will be generated from the video. It’ll be visible when hovering cursor over the video.', 'vidso'));
            $this->args('default', TRUE);
            $this->set_field(
                'videos_generate_animated_gif',
                __('Generate animated gif', 'vidso'),
                'yesno_switch'
            );

            $options = array(
                'gif' => __('GIF', 'vidso'),
                'webm' => __('WEBM', 'vidso'),
                'both' => __('Both', 'vidso')
            );

            $this->args('descript', __('WEBM file format results in smaller files, but conversion might use more server resources.', 'vidso'));
            $this->args('options', $options);
            $this->args('default', 'gif');
            $this->set_field(
                'videos_animated_output', __('Output format', 'vidso'), 'select'
            );
        }

        // Build Group
        $this->set_group(
            'video_uploads', __('Video uploads', 'vidso')
        );
    }

    public function audio_uploads() {

        if(PeepSo3_Helper_Addons::license_is_free_bundle(FALSE)) {
            $this->set_field(
                'videos_audio_disabled',
                PeepSo3_Helper_Remote_Content::get('free_bundle_disabled_text'),
                'message'
            );
        } else {
            $this->args('descript',__('When enabled users are able to link and/or upload audio.', 'vidso'));
            $this->args('default', FALSE);
            $this->set_field(
                'videos_audio_master_switch',
                __('Enabled', 'vidso'),
                'yesno_switch'
            );



            // WP max upload size
            $wp_max_size = max(wp_max_upload_size(), 0);
            $wp_max_size /= pow(1024, 2);

            ob_start();
            echo __("Audio uploads don't require a cron job or conversion, but they require a PHP function finfo_open() to work properly.", 'vidso');

            $finfo_open = (function_exists('finfo_open') && is_callable('finfo_open')) ? TRUE : FALSE;
            ?>

            <br/><br/>

            <span style="color:<?php echo $finfo_open ? 'green' : 'red';?>">
            <?php
            $state = $finfo_open ? __('enabled','vidso') : __('disabled','vidso');
            echo sprintf(__('%s appears to be %s','vidso'), 'finfo_open()', $state);
            ?>
        </span>

            <?php
            $this->set_field('videos_audio_warning', ob_get_clean(), 'message');

            $this->set_field('videos_audio_separator',__('Audio uploads','vidso'), 'separator');

            $this->args('descript',__('Users will be able to upload audio files. The files will be published immediately without conversion.', 'vidso'));
            $this->args('default', FALSE);
            $this->set_field(
                'videos_audio_enable',
                __('Enabled', 'vidso'),
                'yesno_switch'
            );

            $this->set_field('videos_audio_limits_separator',__('Limits','vidso'), 'separator');

            // $this->args('int', TRUE);
            $this->args('default', 20);
            $this->args('validation', array('numeric', 'minval:1'));
            $this->args('descript', sprintf(__('In megabytes - WordPress maximum upload size allowed is %1$sMB', 'vidso'), $wp_max_size));
            $this->set_field(
                'videos_audio_max_upload_size',
                __('Maximum audio upload size', 'vidso'),
                'text'
            );


            // $this->args('int', TRUE);
            $this->args('default', 0);
            $this->args('validation', array('numeric', 'minval:0'));
            $this->args('descript', __('In megabytes. 0 (zero) for no limit', 'vidso'));
            $this->set_field(
                'videos_audio_allowed_user_space',
                __('Allowed space per user for audio', 'vidso'),
                'text'
            );


            // Last.fm
            $this->set_field('videos_audio_lastfm_separator',__('Cover Art','vidso'), 'separator');
            $this->args('descript',__('Cover art download will be attempted via Last.fm API.', 'vidso'));
            $this->args('default', FALSE);
            $this->set_field(
                'videos_audio_lastfm',
                __('Enabled', 'vidso'),
                'yesno_switch'
            );

            $this->args('descript', sprintf(__('You can get the API key %s', 'vidso'),'<a href="https://peep.so/lastfm" target="_blank">'.__('here','vidso').'</a>'));
            $this->set_field(
                'videos_audio_lastfm_api_key',
                __('Last.fm API key', 'vidso'),
                'text'
            );
        }

        // Build Group
        $this->set_group(
            'audio', __('Audio', 'vidso')
        );
    }

    /*******************************************************************************************************************
     *
     *
     *
     *                  DO NOT ADD TRANSLATIONS TO THIS METHOD - THIS STAYS IN ENGLISH FOR EVERYONE
     *
     *
     *
     *
     *******************************************************************************************************************/
    public function warnings() {

        // Uploads warning text
        ob_start();?>



        <?php echo 'Video uploads are a <b>very advanced feature</b> - one that comes with a number of strict technical requirements and limitations.'; ?>

        <br /><br />

        <b>
            <?php echo 'Video conversion requires an external cron job, advanced PHP functions and a special library. It is also prone to consume a lot of server resources.';?>
        </b>

        <br /><br />

        <?php echo 'Please do not enable this feature without a thorough understanding of what is required.';?>


        <?php echo 'To properly configure it, please talk to your hosting provider about the following details.';?>

        <br/><br/>

        <?php echo sprintf("If you have any questions about enabling and configuring this feature, please refer to the %s or %s.",
            '<a href="https://peep.so/docs_video_uploads" target="_blank">'.'documentation'.' <i class="fa fa-external-link"></i></a>',
            '<a href="https://peepso.com/contact" target="_blank">'.'contact us'.' <i class="fa fa-external-link"></i></a>'
        );

        ?>

        <?php

        $this->set_field('videos_uploads_warning', ob_get_clean(), 'message');


        /** SHARED SERVER*/
        $this->set_field('videos_server_separator','Powerful server', 'separator');
        ob_start();

        echo 'As a rule of thumb, the video uploads and conversions will most likely not work in a shared server environment. Please make sure to be using a good VPS or a dedicated machine.';

        $this->set_field('videos_server_warning', ob_get_clean(), 'message');

        /** FFMPEG **/
        $this->set_field('videos_system_libraries_separator','FFmpeg 4.x', 'separator');
        ob_start();

        echo 'This library is absolutely necessary in order for the video conversion to work. PeepSo only supports FFmpeg <b>version 4.0 or newer</b>. Ask your hosting provider about this library and the executable path - you need to fill it in the left panel.';


        if(!PeepSo::get_option('videos_disable_backend_checks') && function_exists('shell_exec')) {
            echo '<br/><br/>';
            $suggested = trim(strval(shell_exec('whereis ffmpeg')));
            if (empty($suggested)) {
                $suggested = trim(strval(exec('whereis ffmpeg')));
            }

            if (strlen($suggested)) {
                echo sprintf('Suggested path(s): <strong>%s</strong>', $suggested);
                echo '<br/><br/>';
            }
            echo 'Bear in mind, FFmpeg will not be detected if exec() or shell_exec() can\'t run. Please read the section below.';

            $ffmpeg = TRUE;
            $ffmpeg_bin = PeepSo::get_option('videos_ffmpeg_binary', '');

            // try another approach when ffmpeg binary is empty
            if (empty($ffmpeg_bin) && !empty($suggested)) {
                $arr = explode(' ', trim($suggested));
                if (isset($arr[1])) {
                    $ffmpeg_bin = $arr[1];
                }
            }

            $checkffmpeg = shell_exec($ffmpeg_bin . ' -version 2>&1');
            $ffmpeg_ver = '';

            if (empty($ffmpeg_bin) || $checkffmpeg == NULL) {
                $ffmpeg = FALSE;
            } else {
                preg_match("/(?:version|v)\s*((?:[0-9]+\.?)+)/i", $checkffmpeg, $matches);
                $ffmpeg_ver = $matches[1];
            }

            $ffmpeg_latest = TRUE;
            if (1 == version_compare('4.0.0', $ffmpeg_ver)) {
                $ffmpeg_latest = FALSE;
            }

            ?>
            <br/><br/>

            <span style="color:<?php echo $ffmpeg ? 'green' : 'red'; ?>">
            <?php
            $state = $ffmpeg ? 'enabled' : 'disabled';
            echo sprintf('%s appears to be %s', 'FFmpeg', $state);
            ?>
            <abbr title="<?php echo $checkffmpeg; ?>"><i class="fa fa-info-circle"></i></abbr>
        </span>

            <br/><br/>

            <?php if ($ffmpeg) { ?>
                <span style="color:<?php echo $ffmpeg_latest ? 'green' : 'red'; ?>">
            <?php
            $state = $ffmpeg_latest ? 'up to date' : 'out of date';
            echo sprintf('%s (%s) appears to be %s', 'FFmpeg', $ffmpeg_ver, $state);
            ?>
            <abbr title="<?php echo $checkffmpeg; ?>">
        </span>
            <?php }
        }?>

        <?php
        $this->set_field('videos_system_libraries_warning', ob_get_clean(), 'message');

        /** FFPROBE **/
        $this->set_field('videos_system_libraries_ffprobe_separator','FFprobe 4.x', 'separator');
        ob_start();

        echo 'This library is absolutely necessary in order for never upscale video feature.';
        if(!PeepSo::get_option('videos_disable_backend_checks') && function_exists('shell_exec')) {
            echo '<br/><br/>';

            $suggested = trim(strval(shell_exec('whereis ffprobe')));
            if(empty($suggested)) {
                $suggested = trim(strval(exec('whereis ffprobe')));
            }

            if(strlen($suggested)) {
                echo sprintf('Suggested path(s): <strong>%s</strong>', $suggested);
                echo '<br/><br/>';
            }
            echo 'Bear in mind, FFprobe will not be detected if exec() or shell_exec() can\'t run. Please read the section below.';

            $ffprobe = TRUE;
            $ffprobe_bin = PeepSo::get_option('videos_ffprobe_binary', '');

            // try another approach when ffprobe binary is empty
            if (empty($ffprobe_bin) && !empty($suggested)) {
                $arr = explode(' ', trim($suggested));
                if (isset($arr[1])) {
                    $ffprobe_bin=$arr[1];
                }
            }

            $checkffprobe = shell_exec($ffprobe_bin . ' -version 2>&1');
            $ffprobe_ver = '';

            if (empty($ffprobe_bin) || $checkffprobe == NULL) {
                $ffprobe = FALSE;
            } else {
                preg_match("/(?:version|v)\s*((?:[0-9]+\.?)+)/i", $checkffprobe, $matches);
                $ffprobe_ver = $matches[1];
            }

            $ffprobe_latest = TRUE;
            if(1 == version_compare('4.0.0', $ffprobe_ver)) {
                $ffprobe_latest = FALSE;
            }

            ?>
            <br/><br/>


            <span style="color:<?php echo $ffprobe ? 'green' : 'red';?>">
            <?php
            $state = $ffprobe ? 'enabled' : 'disabled';
            echo sprintf('%s appears to be %s', 'FFprobe', $state);
            ?>
            <abbr title="<?php echo $checkffprobe;?>"><i class="fa fa-info-circle"></i></abbr>
        </span>

            <br/><br/>

            <?php if($ffprobe) { ?>
                <span style="color:<?php echo $ffprobe_latest ? 'green' : 'red';?>">
            <?php
            $state = $ffprobe_latest ? 'up to date' : 'out of date';
            echo sprintf('%s (%s) appears to be %s', 'FFprobe', $ffprobe_ver, $state);
            ?>
            <abbr title="<?php echo $checkffprobe;?>">
        </span>
            <?php }
        } ?>
        <?php
        $this->set_field('videos_system_libraries_ffprobe_warning', ob_get_clean(), 'message');

        /** PHP FUNCTIONS */
        $this->set_field('videos_php_functions_separator','PHP: exec() & shell_exec()', 'separator');
        ob_start();

        echo 'Make sure these two PHP functions are not blocked on your server. Your hosting provider should be able to give you all necessary information. If it is not possible to enable these functions on your server, the video uploads will not work.';


        if(!PeepSo::get_option('videos_disable_backend_checks') && function_exists('shell_exec')) {

            echo '<br/><br/>' . 'At least one of these functions needs to be available:';

            $exec = (trim(exec('echo OK')) == 'OK') ? TRUE : FALSE;
            $shell_exec = (trim(shell_exec('echo OK')) == 'OK') ? TRUE : FALSE;

            // var_dump(shell_exec('echo OK'));
            ?>

            <br/><br/>

            <span style="color:<?php echo $exec ? 'green' : 'red'; ?>">
            <?php
            $state = $exec ? 'enabled' : 'disabled';
            echo sprintf('%s appears to be %s', 'exec()', $state);
            ?>
        </span>

            <br/><br/>

            <span style="color:<?php echo $shell_exec ? 'green' : 'red'; ?>">
            <?php
            $state = $shell_exec ? 'enabled' : 'disabled';
            echo sprintf('%s appears to be %s', 'shell_exec()', $state);
            ?>
        </span>

            <?php
        }

        $this->set_field('videos_php_functions_warning', ob_get_clean(), 'message');

        /** PHP SETTINGS **/
        $this->set_field('videos_php_other_separator','PHP: '.'other settings', 'separator');
        ob_start();

        echo 'Make sure that your server is able to receive <strong>big uploads</strong>. Ensure there are no configuration settings that prevent big files from being uploaded. PHP <strong>maximum execution time</strong> needs to be adjusted in order to accomodate bigger conversion jobs. <strong>PHP safe mode</strong> will most likely interfere with the conversion process.';


        if(!PeepSo::get_option('videos_disable_backend_checks') && function_exists('shell_exec')) {
            echo '<br/><br>';
            echo 'Here are some PHP options worth looking into - with their current values obtained with ini_get(). There are no strict guidelines here, but as a rule of thumb if you want to process big uploads, you need to raise all other limits.';
            echo '<br/><br>';
            $s = array(
                'file_uploads' => '1 (enabled) - without it no file uploads will work',

                'upload_max_filesize' => 'Lower limit means shorter videos, bigger limit means higher server load',
                'post_max_size' => 'Should not be lower than the above value',


                'max_execution_time' => 'The bigger files are allowed, the more time and the server needs to process them',
                'max_input_time' => 'Should not be lower than the above value',

                'memory_limit' => 'Depends on file sizes and amount of users on your site, but if you want video conversion to work, this limit needs to be very generous.',
            );
            ?>
            <style type="text/css">
                #ps_php_table td, th {
                    padding: 5px;
                }

                #ps_php_table th {
                    background: rgba(0, 0, 0, 0.2);
                    text-align: center;
                    font-weight: bold;
                }

            </style>
            <center>

                <table id="ps_php_table" width="90%" border="1">
                    <tr>
                        <th><?php echo 'Option'; ?></th>
                        <th><?php echo 'Value'; ?></th>
                        <th><?php echo 'Suggestions'; ?></th>
                    </tr>
                    <?php foreach ($s as $k => $v) { ?>
                        <tr>
                            <td><?php echo $k; ?></td>
                            <td><?php echo ini_get($k); ?></td>
                            <td><?php echo $v; ?></td>
                        </tr>
                    <?php } ?>
                </table>

            </center>

            <?php
        }
        $this->set_field('videos_php_other_warning', ob_get_clean(), 'message');

        /** CRON */
        $this->set_field('videos_cron_separator','External cron job', 'separator');
        ob_start();

        echo sprintf("Video conversion is too intense for the WordPress cron and requires a scheduled worker (cron job) to be executed periodically by the server. Please refer to the %s and ask your hosting provider about configuring it.", '<a href="https://peep.so/docs_video_uploads" target="_blank">documentation <i class="fa fa-external-link"></i></a>');

        $this->set_field('videos_cron_warning', ob_get_clean(), 'message');


        $this->set_field('videos_disable_backend_checks_separator','Technical requirements verification', 'separator');
        $this->args('descript', 'Will prevent this page from checking the technical requirements. Use this to avoid unnecessary error log entries if you don\'t intend to use Video Uploads.');
        $this->set_field(
            'videos_disable_backend_checks',
            'Disable technical checks',
            'yesno_switch'
        );
        // Build Group
        $this->set_group(
            'warnings', 'Video uploads - important information'
        );
    }

    private function media_library() {
        $this->args('descript',__('When enabled all users are able to access all files in media library.', 'vidso'));
        $this->args('default', FALSE);
        $this->set_field(
            'videos_subscriber_media_library_access',
            __('Enabled', 'vidso'),
            'yesno_switch'
        );

        // Build Group
        $this->set_group(
            'media_library', __('Media Library', 'vidso')
        );
    }

    /*******************************************************************************************************************
     *
     *
     *
     *                  DO NOT ADD TRANSLATIONS TO THIS METHOD - THIS STAYS IN ENGLISH FOR EVERYONE
     *
     *
     *
     *
     *******************************************************************************************************************/
    private function aws() {

        // AWS warning text
        ob_start();?>

        <b>
            <?php echo 'Video conversion requires an external cron job, AWS Elastic Transcoder, AWS S3 Bucket, and AWS Keys.';?>
        </b>

        <br /><br />

        <?php echo 'Please do not enable this feature without a thorough understanding of what is required.';?>

        <br/><br/>

        <?php echo sprintf("If you have any questions about enabling and configuring this feature, please refer to the %s or %s.",
            '<a href="https://peep.so/docs_video_uploads_amazon_transcoder" target="_blank">'.'documentation'.' <i class="fa fa-external-link"></i></a>',
            '<a href="https://peepso.com/contact" target="_blank">'.'contact us'.' <i class="fa fa-external-link"></i></a>'
        );

        ?>

        <?php
        $this->set_field('aws_elastic_transcoder_warning', ob_get_clean(), 'message');

        // Build Group
        $this->set_group(
            'warnings', 'AWS Elastic Transcoder - important information'
        );
    }

    /**
     * Validation callback to check if Amazon S3 requirements are met
     *
     * @param string $value Amazon configuration value
     * @return boolean Returns TRUE if value is filled up and Amazon S3 Storage is enabled, otherwise returns FALSE
     */
    public function validate_s3_requirements($value)
    {
        // TODO: use PeepSoInput::post_exists() instead of $_POST
        return ((0 === strlen($value) && isset($_POST['videos_conversion_mode']) && $_POST['videos_conversion_mode'] == 'aws_elastic') ? FALSE : TRUE);
    }

    /**
     * Validation callback to check if Amazon Elastic Transcoder  are met
     *
     * @param string $value Amazon configuration value
     * @return boolean Returns TRUE if value is filled up and Amazon S3 Storage is enabled, otherwise returns FALSE
     */
    public function validate_elastic_transcoder($value)
    {
        // TODO: use PeepSoInput::post_exists() instead of $_POST
        return ((0 === strlen($value) && isset($_POST['videos_conversion_mode']) && $_POST['videos_conversion_mode'] == 'aws_elastic') ? FALSE : TRUE);
    }
}
