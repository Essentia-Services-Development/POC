<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UWNHMkczalZZenc1SHZoT1ByQU1ldnY0bE9tblQwMVpnZE5uTGV6UjdrTjZteUJCQ1htelpud2Z1d3BCZmlYbmlodnhhN2cvRlRpL0V0R3Q0RnZpT3lnRXRDbDU2bWZkOTJVYjFwTWU4UkN1M1FtZWN0YVZwN2xXckFOVWx4bHVvPQ==*/

class PeepSoPhotosAdmin
{
    private static $_instance = NULL;

    /**
     * return singleton instance of PeepSoPhotosAdmin
     */
    public static function get_instance()
    {
        if (self::$_instance === NULL)
            self::$_instance = new self();
        return (self::$_instance);
    }

    /**
     * Constructor as private since class is singleton
     */
    private function __construct()
    {
        #add_filter('peepso_admin_register_config_group-site', array(&$this, 'register_config_options'));
        add_filter('peepso_report_column_title', array(&$this, 'report_column_title'), 20, 3);
        add_action('admin_enqueue_scripts', array(&$this, 'enqueue_scripts'));

        add_filter('peepso_config_email_messages', array(&$this, 'config_email'));
        add_filter('peepso_config_email_messages_defaults', array(&$this, 'config_email_messages_defaults'));
    }

    /**
     * Add config options for PeepSoPhotos
     * @param array $config_groups
     * @return array
     */
    public function register_config_options($config_groups)
    {
        // WP max upload size
        $wp_max_size = max(wp_max_upload_size(), 0);
        $wp_max_size /= pow(1024, 2);

        $section = 'photos_';
        $max_upload_size = array(
            'name' => $section . 'max_upload_size',
            'label' => __('Maximum upload size in megabytes', 'picso'),
            'descript' => sprintf(__('WordPress maximum upload size allowed is %1$sMB', 'picso'), $wp_max_size),
            'type' => 'text',
            'int' => TRUE,
            'validation' => array('numeric', 'minval:1', 'maxval:1000'),
            'field_wrapper_class' => 'controls col-sm-8',
            'field_label_class' => 'control-label col-sm-4',
            'value' => PeepSo::get_option($section . 'max_upload_size')
        );

        $allowed_user_space = array(
            'name' => $section . 'allowed_user_space',
            'label' => __('Allowed space per user in megabytes', 'picso'),
            'type' => 'text',
            'int' => TRUE,
            'validation' => array('numeric', 'minval:1', 'maxval:5000'),
            'field_wrapper_class' => 'controls col-sm-8',
            'field_label_class' => 'control-label col-sm-4',
            'value' => PeepSo::get_option($section . 'allowed_user_space')
        );

        $options = array(
            '0' => __('Unlimited', 'picso'),
            '100' => __('100', 'picso'),
            '250' => __('250', 'picso'),
            '500' => __('500', 'picso'),
            '1000' => __('1000', 'picso'),
        );

        $max_user_photo = array(
            'name' => $section . 'max_user_photo',
            'label' => __('Maximum number of Photos per User', 'picso'),
            'type' => 'select',
            'validation' => array('numeric'),
            'field_wrapper_class' => 'controls col-sm-8',
            'field_label_class' => 'control-label col-sm-4',
            'options' => $options,
            'value' => PeepSo::get_option($section . 'max_user_photo')
        );

        $options = array(
            '0' => __('Unlimited', 'picso'),
            '5' => __('5', 'picso'),
            '10' => __('10', 'picso'),
            '20' => __('20', 'picso'),
            '50' => __('50', 'picso'),
        );

        $daily_photo_upload_limit = array(
            'name' => $section . 'daily_photo_upload_limit',
            'label' => __('Daily photo upload limit per user', 'picso'),
            'type' => 'select',
            'validation' => array('numeric'),
            'field_wrapper_class' => 'controls col-sm-8',
            'field_label_class' => 'control-label col-sm-4',
            'options' => $options,
            'value' => PeepSo::get_option($section . 'daily_photo_upload_limit')
        );

        $options = array(
            '0' => __('Use Original', 'picso'),
            '1' => __('Resize', 'picso'),
        );

        $behavior = array(
            'name' => $section . 'behavior',
            'label' => __('Photo Upload Behavior', 'picso'),
            'type' => 'select',
            'validation' => array('numeric'),
            'field_wrapper_class' => 'controls col-sm-8',
            'field_label_class' => 'control-label col-sm-4',
            'options' => $options,
            'value' => PeepSo::get_option($section . 'behavior')
        );

        $max_image_width = array(
            'name' => $section . 'max_image_width',
            'label' => __('Maximum image width', 'picso'),
            'type' => 'text',
            'int' => TRUE,
            'validation' => array('numeric', 'minval:20', 'maxval:20000'),
            'field_wrapper_class' => 'controls col-sm-8',
            'field_label_class' => 'control-label col-sm-4',
            'value' => PeepSo::get_option($section . 'max_image_width'),
        );


        $options = array();
        $recommended = 80;

        for($i=30; $i<=100; $i+=5) {
            $options[$i]=$i.'%';
        }

        $options[$recommended] = $recommended .'% ('.__('recommended', 'picso').')';

        $quality_full = array(
            'name' => $section . 'quality_full',
            'label' => __('Image quality', 'picso'),
            'type' => 'select',
            'options' => $options,
            'field_wrapper_class' => 'controls col-sm-8',
            'field_label_class' => 'control-label col-sm-4',
            'value' => PeepSo::get_option($section . 'quality_full'),
        );


        $options = array();
        $recommended = 60;

        for($i=30; $i<=100; $i+=5) {
            $options[$i]=$i.'%';
        }

        $options[$recommended] = $recommended .'% ('.__('recommended', 'picso').')';

        $quality_thumb = array(
            'name' => $section . 'quality_thumb',
            'label' => __('Thumbnail quality', 'picso'),
            'type' => 'select',
            'options' => $options,
            'field_wrapper_class' => 'controls col-sm-8',
            'field_label_class' => 'control-label col-sm-4',
            'value' => PeepSo::get_option($section . 'quality_thumb'),
        );

        $max_image_height = array(
            'name' => $section . 'max_image_height',
            'label' => __('Maximum image height', 'picso'),
            'type' => 'text',
            'int' => TRUE,
            'validation' => array('numeric', 'minval:20', 'maxval:20000'),
            'field_wrapper_class' => 'controls col-sm-8',
            'field_label_class' => 'control-label col-sm-4',
            'value' => PeepSo::get_option($section . 'max_image_height'),
        );

        $enable_aws_s3 = array(
            'name' => $section . 'enable_aws_s3',
            'label' => __('Enable AWS S3 storage', 'picso'),
            'descript' => __('Turn on the use of Amazon AWS for image storage', 'picso'),
            'type' => 'yesno_switch',
            'int' => TRUE,
            'field_wrapper_class' => 'controls col-sm-8',
            'field_label_class' => 'control-label col-sm-4',
            'value' => intval(PeepSo::get_option($section . 'enable_aws_s3')),
        );

        $aws_access_key_id = array(
            'name' => $section . 'aws_access_key_id',
            'label' => __('Amazon Access Key ID', 'picso'),
            'descript' => __('The Access Key ID you received when you signed up for AWS (20 characters)', 'picso'),
            'type' => 'text',
            'validation' => array('custom'),
            'validation_options' =>
                [
                    [
                        'function' => array($this, 'validate_s3_requirements'),
                        'error_message' => __('Amazon Access Key ID is required', 'picso')
                    ],
                ],
            'field_wrapper_class' => 'controls col-sm-8',
            'field_label_class' => 'control-label col-sm-4',
            'value' => PeepSo::get_option($section . 'aws_access_key_id'),
        );

        $aws_secret_access_key = array(
            'name' => $section . 'aws_secret_access_key',
            'label' => __('Amazon Secret Access Key', 'picso'),
            'descript' => __('The AWS Secret Key you received when you signed up for AWS (40 characters)', 'picso'),
            'type' => 'text',
            'validation' => array('custom'),
            'validation_options' =>
                [
                    [
                        'function' => array($this, 'validate_s3_requirements'),
                        'error_message' => __('Amazon Secret Access Key is required', 'picso')
                    ],
                ],
            'field_wrapper_class' => 'controls col-sm-8',
            'field_label_class' => 'control-label col-sm-4',
            'value' => PeepSo::get_option($section . 'aws_secret_access_key'),
        );

        $aws_s3_bucket = array(
            'name' => $section . 'aws_s3_bucket',
            'label' => __('Amazon S3 Bucket', 'picso'),
            'descript' => __('Name of the Bucket to upload your images to on Amazon S3', 'picso'),
            'type' => 'text',
            'validation' => array('custom'),
            'validation_options' =>
                [
                    [
                        'function' => array($this, 'validate_s3_requirements'),
                        'error_message' => __('Amazon S3 Bucket is required', 'picso')
                    ],
                ],
            'field_wrapper_class' => 'controls col-sm-8',
            'field_label_class' => 'control-label col-sm-4',
            'value' => PeepSo::get_option($section . 'aws_s3_bucket'),
        );

        $summary = '';

        // add any AWS related errors to the Config box so admins can see them
        $aws_errors = new PeepSoPhotosAWSErrors();
        $errors = $aws_errors->get_errors();
        if (0 !== count($errors)) {
            $summary .= '<b>' . __('AWS Error History:', 'picso') . '</b><br/>';
            $format = get_option('date_format') . ' ' . get_option('time_format');
            foreach ($errors as $error) {
                $msg = explode(':', $error, 2);
                $summary .= date($format, $msg[0]) . ': ' . esc_html($msg[1]) . '<br/>';
            }
            $summary .= '&nbsp;<br/>';
        }

        $config_groups[] = array(
            'name' => 'photos',
            'title' => __('Photos Settings', 'picso'),
            'fields' => array($max_upload_size, $allowed_user_space, $max_user_photo, $daily_photo_upload_limit, $behavior, $max_image_width, $max_image_height, $quality_full, $quality_thumb, $enable_aws_s3, $aws_access_key_id, $aws_secret_access_key, $aws_s3_bucket),
            'context' => 'right',
            'description' => __('Configure the use of PeepSo Share Photos.', 'picso'),
            'summary' => $summary . sprintf(__('To enable the use of AWS, set the "Enable AWS S3 Storage" to "YES" and fill in the additional settings fields.<br/>To get your AWS Access ID and Keys, you can sign up here: %sGet Your AWS Access Keys%s.', 'picso'),
                    '<a href="http://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSGettingStartedGuide/AWSCredentials.html" target="_blank">',
                    '</a>'),
        );

        return ($config_groups);
    }

    /**
     * Get the data from the $item based on the given $column_name
     * @param string $title The default title
     * @param array $item An array of single report row/item
     * @param string $column_name The field or column name
     * @return string
     */
    public function report_column_title($title, $item, $column_name)
    {
        if ('post_title' === $column_name) {

            if (PeepSoSharePhotos::MODULE_ID === intval($item['rep_module_id'])) {
                $o_photos = PeepSoSharePhotos::get_instance();
                $o_photos->init();
			
                $photos_model = new PeepSoPhotosModel();
                $photo = $photos_model->get_post_photos($item['rep_external_id']);
    
                if (!$photo) {
                    $title = sprintf(
                        __('Photo of post %s', 'picso'),
                        '<a href="' . PeepSo::get_page('activity') . '?status/' . $item['post_name'] . '/" target="_blank">' . $item['post_name'] . '</a>');
                } else {
                    $post = get_post($photo->pho_post_id);
                    $photos = $o_photos->get_photo_group(array(), $item['rep_external_id']);
                    $index = array_search($item['rep_external_id'], array_keys($photos));

                    if (FALSE !== $index)
                        $title = sprintf(
                            __('Photo %1$d of post %2$s', 'picso'),
                            $index + 1,
                            '<a href="' . PeepSo::get_page('activity') . '?status/' . $post->post_title . '/" target="_blank">' . $post->post_title . '</a>');
                }
            } else if (PeepSoSharePhotos::MODULE_ID === intval($item['rep_module_id']))
                $title = '<a href="' . PeepSo::get_page('activity') . '?status/' . $item[$column_name] . '/" target="_blank">' . $item[$column_name] . '</a>';
        }

        return ($title);
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
        return ((0 === strlen($value) && isset($_POST['photos_enable_aws_s3']) && $_POST['photos_enable_aws_s3']) ? FALSE : TRUE);
    }

    /**
     * Enqueue scripts for peepsophotos admin page
     */
    public function enqueue_scripts()
    {
        if (isset($_GET['page']) && 'peepso_config' == $_GET['page']) {
            wp_enqueue_script('peepso-photos-admin',
                PeepSo::get_asset('js/peepsophotos-admin.min.js', __DIR__),
                array('jquery'), PeepSo::PLUGIN_VERSION, TRUE);
        }
    }

    /**
     * Add the Like/comment/share video emails to the list of editable emails on the config page
     * @param  array $emails Array of editable emails
     * @return array
     */
    public static function config_email($emails)
    {
        // @TODO CLEANUP
        return $emails;
//
//    	if(is_array($emails)) {
//    		$photoemails = array(
//	    		'email_like_photo' => array(
//		            'title' => __('Like Photo', 'picso'),
//		            'description' => __('This will be sent when a user "likes" another user\'s photo.', 'picso')),
//		        'email_user_comment_photo' => array(
//		        	'title' => __('User Comment Photo', 'picso'),
//					'description' => __('This will be sent to a photo owner when another user comments on the photo', 'picso')),
//				'email_share_photo' => array(
//		        	'title' => __('User Share Photo', 'picso'),
//					'description' => __('This will be sent to a photo owner when another user shares on the photo', 'picso')),
//
//				'email_like_avatar' => array(
//		            'title' => __('Like Avatar', 'picso'),
//		            'description' => __('This will be sent when a user "likes" another user\'s avatar.', 'picso')),
//		        'email_user_comment_avatar' => array(
//		        	'title' => __('User Comment Avatar', 'picso'),
//					'description' => __('This will be sent to a avatar owner when another user comments on the avatar', 'picso')),
//				'email_share_avatar' => array(
//		        	'title' => __('User Share Avatar', 'picso'),
//					'description' => __('This will be sent to a avatar owner when another user shares on the avatar', 'picso')),
//
//				'email_like_cover' => array(
//		            'title' => __('Like Cover', 'picso'),
//		            'description' => __('This will be sent when a user "likes" another user\'s cover.', 'picso')),
//		        'email_user_comment_cover' => array(
//		        	'title' => __('User Comment Cover', 'picso'),
//					'description' => __('This will be sent to a cover owner when another user comments on the cover', 'picso')),
//				'email_share_cover' => array(
//		        	'title' => __('User Share Cover', 'picso'),
//					'description' => __('This will be sent to a cover owner when another user shares on the cover', 'picso')),
//
//				'email_like_album' => array(
//		            'title' => __('Like Album', 'picso'),
//		            'description' => __('This will be sent when a user "likes" another user\'s album.', 'picso')),
//		        'email_user_comment_album' => array(
//		        	'title' => __('User Comment Album', 'picso'),
//					'description' => __('This will be sent to a album owner when another user comments on the album', 'picso')),
//				'email_share_album' => array(
//		        	'title' => __('User Share Album', 'picso'),
//					'description' => __('This will be sent to a album owner when another user shares on the album', 'picso'))
//			);
//
//			$emails = array_merge($emails, $photoemails);
//		}
//        return ($emails);
    }

    public function config_email_messages_defaults( $emails )
    {
        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../install' . DIRECTORY_SEPARATOR . 'activate.php');
        $install = new PeepSoPhotosInstall();
        $defaults = $install->get_email_contents();

        return array_merge($emails, $defaults);
    }
}

// EOF
