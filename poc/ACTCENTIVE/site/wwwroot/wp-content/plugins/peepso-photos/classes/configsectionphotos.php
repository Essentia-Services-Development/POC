<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UlpHaWovVUNXcE5MemM1RzBjL2VNODFld2VGQThHQlN5M29Td21sc1NsOXBQWlNpS1dmRnNEUDdLcWxBSHNBVGdDaWZPdzF3U3JPMFdnMC8rb2drOHI5TG1idnByelBqVjV3TFdtNE1TU0lZQXd4aDh0cDF4UEsraGVLWnNXMDlET3pyWW81aGI5VkFRUTdqQ215UGhv*/

class PeepSoConfigSectionphotos extends PeepSoConfigSectionAbstract {

// Builds the groups array
    public function register_config_groups() {
        $this->context = 'left';
        $this->general();
        $this->limits();
        $this->quality();

        $this->context = 'right';
        $this->aws();
    }

    /**
     * Add this addon's configuration options to the admin section
     * @param  array $config_groups
     * @return array
     */
    private function general() {

        // WP max upload size
        $wp_max_size = max(wp_max_upload_size(), 0);
        $wp_max_size /= pow(1024, 2);

        $this->args('int', TRUE);
        $this->args('validation', array('numeric', 'minval:1', 'maxval:1000'));
        $this->args('descript', sprintf(__('WordPress maximum upload size allowed is %1$sMB. WordPress setting takes priority over PeepSo. If you want to allow bigger file uploads please look into WrodPress and / or your server configuration.', 'picso'), $wp_max_size));
        $this->set_field(
            'photos_max_upload_size',
            __('Maximum upload size in megabytes', 'picso'),
            'text'
        );

        $this->set_field(
            'photos_gif_autoplay',
            __('Autoplay GIFs', 'picso'),
            'yesno_switch'
        );

        // Build Group
        $this->set_group(
            'general', __('General', 'picso')
        );
    }

    private function limits() {

        if(PeepSo3_Helper_Addons::license_is_free_bundle( FALSE)) {
            $this->set_field(
                'limits_disabled',
                PeepSo3_Helper_Remote_Content::get('free_bundle_disabled_text'),
                'message'
            );
            PeepSoConfigSettings::get_instance()->set_option('photos_allowed_user_space',200);
            PeepSoConfigSettings::get_instance()->set_option('photos_max_user_photo',250);
            PeepSoConfigSettings::get_instance()->set_option('photos_daily_photo_upload_limit',20);
        }

        $wp_max_size = max(wp_max_upload_size(), 0);
        $wp_max_size /= pow(1024, 2);

        $this->args('int', TRUE);
        $val =  ['numeric', 'minval:0', 'maxval:5000'];
        if(PeepSo3_Helper_Addons::license_is_free_bundle( TRUE)) {
            $val[]='readonly';
        }

        $this->args('validation', $val);
        $this->args('descript', sprintf(__('Set 0 for Unlimited', 'picso'), $wp_max_size));
        $this->set_field(
            'photos_allowed_user_space',
            __('Allowed space per user in megabytes', 'picso'),
            'text'
        );

        $options = array(
            '0' => __('Unlimited', 'picso'),
            '100' => __('100', 'picso'),
            '250' => __('250', 'picso'),
            '500' => __('500', 'picso'),
            '1000' => __('1000', 'picso'),
        );

        $this->args('options', $options);
        $val =  ['numeric'];
        if(PeepSo3_Helper_Addons::license_is_free_bundle( TRUE)) {
            $val[]='readonly';
        }
        $this->args('validation', $val);
        $this->set_field(
            'photos_max_user_photo',
            __('Maximum number of Photos per User', 'picso'),
            'select'
        );


        $options = array(
            '0' => __('Unlimited', 'picso'),
            '5' => __('5', 'picso'),
            '10' => __('10', 'picso'),
            '20' => __('20', 'picso'),
            '50' => __('50', 'picso'),
        );

        $this->args('options', $options);
        $val =  ['numeric'];
        if(PeepSo3_Helper_Addons::license_is_free_bundle( TRUE)) {
            $val[]='readonly';
        }
        $this->args('validation', $val);
        $this->set_field(
            'photos_daily_photo_upload_limit',
            __('Daily photo upload limit per user', 'picso'),
            'select'
        );

        // Build Group
        $this->set_group(
            'limits', __('User limits', 'picso')
        );

    }

    private function quality() {

        if(PeepSo3_Helper_Addons::license_is_free_bundle( FALSE)) {
            $this->set_field(
                'quality_disabled',
                PeepSo3_Helper_Remote_Content::get('free_bundle_disabled_text'),
                'message'
            );
            PeepSoConfigSettings::get_instance()->set_option('photos_behavior',1);
            PeepSoConfigSettings::get_instance()->set_option('photos_max_image_height',2000);
            PeepSoConfigSettings::get_instance()->set_option('photos_max_image_width',2000);
            PeepSoConfigSettings::get_instance()->set_option('photos_quality_full',75);
            PeepSoConfigSettings::get_instance()->set_option('photos_quality_thumb',50);

        }


        $options = array(
            '0' => __('Use Original', 'picso'),
            '1' => __('Resize', 'picso'),
        );

        $this->args('options', $options);
        $val =  ['numeric'];
        if(PeepSo3_Helper_Addons::license_is_free_bundle( TRUE)) {
            $val[]='readonly';
        }
        $this->args('validation', $val);
        $this->set_field(
            'photos_behavior',
            __('Photo Upload Behavior', 'picso'),
            'select'
        );

        $this->args('int', TRUE);
        $val =  ['numeric','minval:20', 'maxval:20000'];
        if(PeepSo3_Helper_Addons::license_is_free_bundle( TRUE)) {
            $val[]='readonly';
        }
        $this->args('validation', $val);
        $this->set_field(
            'photos_max_image_width',
            __('Maximum image width', 'picso'),
            'text'
        );

        $this->args('int', TRUE);
        $val =  ['numeric','minval:20', 'maxval:20000'];
        if(PeepSo3_Helper_Addons::license_is_free_bundle( TRUE)) {
            $val[]='readonly';
        }
        $this->args('validation', $val);
        $this->set_field(
            'photos_max_image_height',
            __('Maximum image height', 'picso'),
            'text'
        );

        $options = array();
        $recommended = 80;

        for($i=30; $i<=100; $i+=5) {
            $options[$i]=$i.'%';
        }

        $options[$recommended] = $recommended .'% ('.__('recommended', 'picso').')';

        $this->args('options', $options);
        $val =  ['numeric'];
        if(PeepSo3_Helper_Addons::license_is_free_bundle( TRUE)) {
            $val[]='readonly';
        }
        $this->args('validation', $val);
        $this->set_field(
            'photos_quality_full',
            __('Image quality', 'picso'),
            'select'
        );


        $options = array();
        $recommended = 60;

        for($i=30; $i<=100; $i+=5) {
            $options[$i]=$i.'%';
        }

        $options[$recommended] = $recommended .'% ('.__('recommended', 'picso').')';

        $this->args('options', $options);
        $val =  ['numeric'];
        if(PeepSo3_Helper_Addons::license_is_free_bundle( TRUE)) {
            $val[]='readonly';
        }
        $this->args('validation', $val);
        $this->set_field(
            'photos_quality_thumb',
            __('Thumbnail quality', 'picso'),
            'select'
        );

        // Build Group
        $this->set_group(
            'quality', __('Photo size & quality', 'picso')
        );
    }

    private function aws() {
        if(PeepSo3_Helper_Addons::license_is_free_bundle( FALSE)) {
            $this->set_field(
                'aws_disabled',
                PeepSo3_Helper_Remote_Content::get('free_bundle_disabled_text'),
                'message'
            );
        } else {
            $this->set_field(
                'photos_enable_aws_s3',
                __('Enable AWS S3 storage', 'picso'),
                'yesno_switch'
            );

            $this->args('descript', __('The Access Key ID you received when you signed up for AWS (20 characters)', 'picso'));
            $this->args('validation', array('custom'));
            $this->args('validation_options',
                [
                    [
                        'function' => array($this, 'validate_s3_requirements'),
                        'error_message' => __('Amazon Access Key ID is required', 'picso')
                    ],
                ]
            );

            $this->set_field(
                'photos_aws_access_key_id',
                __('Amazon Access Key ID', 'picso'),
                'text'
            );

            $this->args('validation', array('custom'));
            $this->args('validation_options',
                [
                    [
                        'function' => array($this, 'validate_s3_requirements'),
                        'error_message' => __('Amazon Secret Access Key is required', 'picso')
                    ],
                ]
            );

            $this->set_field(
                'photos_aws_secret_access_key',
                __('Amazon Secret Access Key', 'picso'),
                'text'
            );

            $this->args('descript', __('Name of the Bucket to upload your images to on Amazon S3', 'picso'));
            $this->args('validation', array('custom'));
            $this->args('validation_options',
                [
                    [
                        'function' => array($this, 'validate_s3_requirements'),
                        'error_message' => __('Amazon S3 Bucket is required', 'picso')
                    ],
                ]
            );

            $this->set_field(
                'photos_aws_s3_bucket',
                __('Amazon S3 Bucket', 'picso'),
                'text'
            );

            $options = array(
                'ap-south-1' => __('Asia Pacific (Mumbai)', 'picso'),
                'ap-northeast-2' => __('Asia Pacific (Seoul)', 'picso'),
                'ap-southeast-1' => __('Asia Pacific (Singapore)', 'picso'),
                'ap-southeast-2' => __('Asia Pacific (Sydney)', 'picso'),
                'ap-northeast-1' => __('Asia Pacific (Tokyo)', 'picso'),
                'ca-central-1' => __('Canada (Central)', 'picso'),
                'eu-central-1' => __('EU (Frankfurt)', 'picso'),
                'eu-west-1' => __('EU (Ireland)', 'picso'),
                'eu-west-2' => __('EU (London)', 'picso'),
                'eu-west-3' => __('EU (Paris)', 'picso'),
                'sa-east-1' => __('South America (São Paulo)', 'picso'),
                'us-east-1' => __('US East (N. Virginia)', 'picso'),
                'us-east-2' => __('US East (Ohio)', 'picso'),
                'us-west-1' => __('US West (N. California)', 'picso'),
                'us-west-2' => __('US West (Oregon)', 'picso'),
            );

            $this->args('options', $options);
            $this->set_field(
                'photos_aws_bucket_location',
                __('Bucket location', 'picso'),
                'select'
            );

            $this->args('descript', __('Disabling AWS S3 will cause photos to disappear, because photos will not be redownloaded', 'picso'));
            $this->set_field(
                'photos_aws_s3_not_keep',
                __("Don't keep a local copy of uploaded files", 'picso'),
                'yesno_switch'
            );

            $summary = '';

            // add any AWS related errors to the Config box so admins can see them
            $aws_errors = new PeepSoPhotosAWSErrors();
            $errors = $aws_errors->get_errors();
            if (0 !== count($errors)) {
                $url = admin_url('admin.php?page=peepso_config&tab=photos&clear-aws-history=1&nonce=' . wp_create_nonce('peepso-config-nonce'));
                $summary .= '<a href="' . $url . '" class="btn btn-primary">' . __('Clear history', 'picso') . '</a><br/>';
                $summary .= '<b>' . __('AWS Error History:', 'picso') . '</b><br/>';
                $format = get_option('date_format') . ' ' . get_option('time_format');
                foreach ($errors as $error) {
                    $msg = explode(':', $error, 2);
                    $summary .= date($format, $msg[0]) . ': ' . esc_html($msg[1]) . '<br/>';
                }
                $summary .= '&nbsp;<br/>';
            }


            $summary .= sprintf(__('To enable the use of AWS, set the "Enable AWS S3 Storage" to "YES" and fill in the additional settings fields.<br/>To get your AWS Access ID and Keys, you can sign up here: %sGet Your AWS Access Keys%s.', 'picso'),
                '<a href="http://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSGettingStartedGuide/AWSCredentials.html" target="_blank">',
                '</a>');

            $this->set_field(
                'photos_aws_msg',
                $summary,
                'message'
            );
        }

        $this->set_group(
            'aws', __('AWS', 'picso')
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
        return ((0 === strlen($value) && isset($_POST['photos_enable_aws_s3']) && $_POST['photos_enable_aws_s3']) ? FALSE : TRUE);
    }

}
