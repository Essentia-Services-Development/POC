<?php
/**
 * Plugin Name: PeepSo Core: File Uploads
 * Plugin URI: https://peepso.com
 * Description: Add ability to upload file
 * Author: PeepSo
 * Author URI: https://peepso.com
 * Version: 6.2.7.0
 * Copyright: (c) 2016 PeepSo, Inc. All Rights Reserved.
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: peepsofileuploads
 * Domain Path: /language
 *
 * We are Open Source. You can redistribute and/or modify this software under the terms of the GNU General Public License (version 2 or later)
 * as published by the Free Software Foundation. See the GNU General Public License or the LICENSE file for more details.
 * This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY.
 *
 */


class PeepSoFileUploads
{

    private static $_instance = NULL;

    const PLUGIN_NAME	 = 'Core: File Uploads';
    const PLUGIN_SLUG 	 = 'peepsofileuploads';
    const PLUGIN_EDD 	 = 66947856;
    const PLUGIN_VERSION = '6.2.7.0';
    const PLUGIN_RELEASE = ''; //ALPHA1, RC1 etc, '' for STABLE
	const MODULE_ID      = 50;

    public $widgets = array(
        'PeepSoWidgetFiles',
        'PeepSoWidgetCommunityFiles'
    );

    // This mime types is to cover additional mime types which not registered in default Wordpress Mime types
    // This is special case if user wan't to add another mime types.
    // Reference: https://developer.wordpress.org/reference/functions/wp_get_mime_types/
    // Since #6663
    public static $additional_mime_types = array(
        'hc' => 'text/plain',
        'hi' => 'text/plain'
    );

    private static function ready() {
        if(class_exists('PeepSo')) {
            $plugin_version = explode('.', self::PLUGIN_VERSION);
            $peepso_version = explode('.', PeepSo::PLUGIN_VERSION);

            if(4==count($plugin_version)) {
                array_pop($plugin_version);
            }

            if(4==count($peepso_version)) {
                array_pop($peepso_version);
            }

            $plugin_version = implode('.', $plugin_version);
            $peepso_version = implode('.', $peepso_version);

            return($peepso_version == $plugin_version);
        }
    }

    private function __construct()
    {
        /** VERSION INDEPENDENT hooks **/

        // Admin
        if (is_admin()) {
            add_action('admin_init', array(&$this, 'peepso_check'));
        }

        // Compatibility
        add_filter('peepso_all_plugins', array($this, 'peepso_filter_all_plugins'));

        // Translations
		add_action('plugins_loaded', array(&$this, 'load_textdomain'));

        // Activation
        register_activation_hook(__FILE__, array(&$this, 'activate'));

        /** VERSION LOCKED hooks **/
        if(self::ready()) {
            add_filter('peepso_license_config', array(&$this, 'add_license_info'), 666);
            if (is_admin()) {
            }

            if (!PeepSoLicense::check_license(self::PLUGIN_EDD, self::PLUGIN_SLUG)) {
                return FALSE;
            }

            add_action('peepso_init', array(&$this, 'init'));
            add_filter('peepso_widgets', array(&$this, 'register_widgets'));
        }
    }

    public static function get_instance()
    {
        if (NULL === self::$_instance) {
            self::$_instance = new self();
        }
        return (self::$_instance);
    }

    public function load_textdomain()
	{
		$path = str_ireplace(WP_PLUGIN_DIR, '', dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
		load_plugin_textdomain('peepsofileuploads', FALSE, $path);
    }

    public function init()
    {
        PeepSo::add_autoload_directory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR);
        PeepSoTemplate::add_template_directory(plugin_dir_path(__FILE__));

        if (is_admin()) {
            add_action('admin_init', array(&$this, 'peepso_check'));
            add_filter('peepso_admin_config_tabs', array(&$this, 'admin_config_tabs'));

            add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));

            // init default value
            $mayfly = PeepSo3_Mayfly::get('uploaded_files');
            if (!$mayfly) {
                PeepSo3_Mayfly::set('uploaded_files', '{}');
            }
        } else {
            // Handling additional mime types
            add_filter( 'upload_mimes', array(&$this, 'get_additional_mime_types'));

            add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));

            if (PeepSo::get_option_new('fileuploads_enable')) {
                // postbox
                add_filter('peepso_post_types', array(&$this, 'post_types'), 30, 2);
                add_filter('peepso_postbox_tabs', array(&$this, 'postbox_tabs'), 120);
                add_filter('peepso_postbox_interactions', array(&$this, 'postbox_interactions'), 110, 2);
                add_filter('peepso_permissions_files_upload', array(&$this, 'permissions_files_upload'));

                // commentbox
                add_filter('peepso_commentsbox_interactions', array(&$this, 'commentsbox_interactions'), 15, 2);
                add_filter('peepso_commentsbox_addons', array(&$this, 'commentsbox_addons'), 10, 2);
                add_filter('peepso_activity_allow_empty_comment', array(&$this, 'activity_allow_empty_comment'), 10, 1);

                // chat input
                add_filter('peepso_message_input_addons',   array(&$this, 'message_input_addons'), 10, 1);

                // save additional data
                add_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'), 10, 1);
                add_filter('peepso_activity_insert_data', array(&$this, 'activity_insert_data'));
                add_action('peepso_activity_after_add_post', array(&$this, 'after_add_post'));
                add_action('peepso_activity_after_save_post', array(&$this, 'after_add_post'), 10, 1);

                add_action('peepso_after_add_comment', array(&$this, 'after_add_comment'), 10, 4);
                add_action('peepso_activity_after_save_comment', array(&$this, 'after_add_comment'), 10, 4);

                // post actions filter
                add_filter('peepso_post_filters', array(&$this, 'post_filters'), 20,1);

                // rest API
                add_filter('peepso_rest_paths', array($this, 'rest_api'));

                // Hooks into profile pages and "me" widget
                add_filter('peepso_navigation_profile', array(&$this, 'filter_profile_segment_menu_links'));
                add_action('peepso_profile_segment_files', array(&$this, 'peepso_profile_segment_files'));

                // Hook into Groups segment menu
                add_filter('peepso_group_segment_menu_links', array(&$this, 'filter_group_segment_menu_links'));
                add_action('peepso_group_segment_files', array(&$this, 'peepso_group_segment_files'), 10, 2);
            }

			// attach file to post and comment
			add_action('peepso_activity_post_attachment', array(&$this, 'attach_file'), 30);
			add_action('peepso_activity_comment_attachment', array(&$this, 'attach_file'), 30, 10, 3);

			// disable repost
			add_filter('peepso_activity_post_actions', array(&$this, 'activity_post_actions'), 100);

			// stream title
			add_filter('peepso_activity_stream_action', array(&$this, 'activity_stream_action'), 10, 2);

            // delete content
            add_action('peepso_delete_content', array(&$this, 'delete_content'));

            if (class_exists('PeepSoMaintenanceFactory') && class_exists('PeepSoMaintenanceFiles')) {
                new PeepSoMaintenanceFiles();
            }
        }

        // Check license
        if (!PeepSoLicense::check_license(self::PLUGIN_EDD, self::PLUGIN_SLUG)) {
            return FALSE;
        }
    }

    public function activate()
    {
        if (!$this->peepso_check()) {
            return (FALSE);
        }

        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'activate.php');
        $install = new PeepSoFileUploadsInstall();
        $res = $install->plugin_activation();
        if (FALSE === $res) {
            // error during installation - disable
            deactivate_plugins(plugin_basename(__FILE__));
        }

        return (TRUE);
    }

    public function peepso_check()
    {
        if (!class_exists('PeepSo')) {
            add_action('admin_notices', array(&$this, 'peepso_disabled_notice'));
            unset($_GET['activate']);
            deactivate_plugins(plugin_basename(__FILE__));
            return (FALSE);
        }

        // PeepSo.com license check
        if (!PeepSoLicense::check_license(self::PLUGIN_EDD, self::PLUGIN_SLUG)) {
            add_action('admin_notices', array(&$this, 'license_notice'));
        }

        if (isset($_GET['page']) && 'peepso_config' == $_GET['page'] && !isset($_GET['tab'])) {
            add_action('admin_notices', array(&$this, 'license_notice_forced'));
        }

        // PeepSo.com new version check
        // since 1.7.6
        if(method_exists('PeepSoLicense', 'check_updates_new')) {
            PeepSoLicense::check_updates_new(self::PLUGIN_EDD, self::PLUGIN_SLUG, self::PLUGIN_VERSION, __FILE__);
        }

        return (TRUE);
    }

    public function license_notice()
    {
        PeepSo::license_notice(self::PLUGIN_NAME, self::PLUGIN_SLUG);
    }

    public function license_notice_forced()
    {
        PeepSo::license_notice(self::PLUGIN_NAME, self::PLUGIN_SLUG, true);
    }

    public function peepso_disabled_notice()
    {
        ?>
        <div class="error peepso">
            <strong>
                <?php echo sprintf(__('The %s plugin requires the PeepSo plugin to be installed and activated.', 'peepsofileuploads'), self::PLUGIN_NAME);?>
				<a href="plugin-install.php?tab=plugin-information&plugin=peepso-core&TB_iframe=true&width=772&height=291" class="thickbox">
					<?php echo __('Get it now!', 'peepsofileuploads');?>
				</a>
            </strong>
        </div>
        <?php
    }

    public function peepso_filter_all_plugins($plugins)
    {
        $plugins[plugin_basename(__FILE__)] = get_class($this);
        return $plugins;
    }

    public function add_license_info($list)
    {
        $data = array(
            'plugin_slug' => self::PLUGIN_SLUG,
            'plugin_name' => self::PLUGIN_NAME,
            'plugin_edd' => self::PLUGIN_EDD,
            'plugin_version' => self::PLUGIN_VERSION
        );
        $list[] = $data;
        return ($list);
    }

    public function admin_config_tabs( $tabs )
    {
        $tabs['fileuploads'] = array(
            'label' => __('File Uploads', 'peepsofileuploads'),
            'tab' => 'fileuploads',
            'icon' => 'https://cdn.peepso.com/icons/plugins/'.self::PLUGIN_EDD.'.svg',
            'description' => __('Upload file', 'peepsofileuploads'),
            'function' => 'PeepSoConfigSectionFileUploads',
            'cat'   => 'core',
        );

        return $tabs;
    }

    /**
     * Adds the file tab to the available post type options
     * @param  array $post_types
     * @param  array $params
     * @return array
     */
    public function post_types($post_types, $params = array())
    {

		if (!apply_filters('peepso_permissions_files_upload', TRUE)) {
            return ($post_types);
        }

        $post_types['file'] = array(
            'icon' => 'gcis gci-file',
            'name' => __('Files', 'peepsofileuploads'),
            'class' => 'ps-postbox__menu-item',
        );

        return ($post_types);
    }

    /**
     * Displays the UI for the file post type
     * @return string The input html
     */
    public function postbox_tabs($tabs)
    {

		if (!apply_filters('peepso_permissions_files_upload', TRUE)) {
			return $tabs;
		}

		$data = array(
			'multiselect' => PeepSo::get_option('file_multiselect', TRUE)
		);

        $tabs['file'] = PeepSoTemplate::exec_template('file', 'postbox-file', $data, TRUE);

        return ($tabs);
    }

    /**
     * This function inserts the file options on the post box
     * @param array $interactions is the formated html code that get inserted in the postbox
     * @param array $params
     */
    public function postbox_interactions($interactions, $params = array())
    {
        if (isset($params['is_current_user']) && $params['is_current_user'] === FALSE) {
            return ($interactions);
        }

        if (!apply_filters('peepso_permissions_files_upload', TRUE)) {
            return ($interactions);
        }

        $interactions['file'] = array(
            'icon' => 'gcis gci-file',
            'id' => 'file-post',
            'class' => 'ps-postbox__menu-item',
            'click' => 'return;',
            'label' => '',
            'title' => __('Files', 'peepsofileuploads'),
            'style' => 'display:none'
        );

        return ($interactions);
    }

	/*
     * enqueue scripts for file
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script('peepsofile', plugin_dir_url(__FILE__) . 'assets/js/bundle.min.js',
            array('jquery', 'peepso', 'peepso-postbox'), PeepSo::PLUGIN_VERSION, TRUE);

        $max_upload_size = self::max_upload_size();

        $files_model = new PeepSoFilesModel();
        $upload_data = $files_model->calculate_user_files(get_current_user_id());

        add_filter('peepso_data', function($data) use ($max_upload_size, $upload_data) {
            $upload_file_types = trim(strtolower(PeepSo::get_option_new('fileuploads_allowed_filetype')));
            if ($upload_file_types) {
                $upload_file_types = preg_split("/\s+/", $upload_file_types);
            } else {
                $upload_file_types = null;
            }

            $max_user_space = $max_user_files = $max_daily_upload = 0;
            if (!PeepSo::is_admin()) {
                $max_user_space = (int) PeepSo::get_option_new('fileuploads_allowed_user_space');
                $max_user_files = (int) PeepSo::get_option_new('fileuploads_max_limit');
                $max_daily_upload = (int) PeepSo::get_option_new('fileuploads_max_daily_limit');
            }

            $data['file'] = array(
                'uploadUrl' => 'file_upload',
                'uploadFileTypes' => $upload_file_types,
                'maxUploadSize' => $max_upload_size,
                'maxUserSpace' => $max_user_space * 1048576,
                'currentUserSpace' => $upload_data['size'],
                'maxDailyUpload' => $max_daily_upload,
                'currentDailyUpload' => $upload_data['uploaded_today'],
                'maxUpload' => $max_user_files,
                'currentUpload' => $upload_data['count'],
                'texts' => array(
                    'postboxPlaceholder' => __('Say something about these files...', 'peepsofileuploads'),
                    'fileTypeWarning' => sprintf(__('Supported formats are: %s.', 'peepsofileuploads'), $upload_file_types ? implode(', ', $upload_file_types) : ''),
                    'maxUploadSizeWarning' => sprintf(__('Only files up to %dMB are allowed.', 'peepsofileuploads'), $max_upload_size),
                    'maxUserSpaceWarning' => __('Maximum file upload quota reached. Delete posts with files to free some space.', 'peepsofileuploads'),
                    'maxDailyUploadWarning' => __('Maximum daily file upload quota reached. Delete posts with files to free some space.', 'peepsofileuploads'),
                    'maxUploadWarning' => __('Maximum file upload quota reached. Delete posts with files to free some space.', 'peepsofileuploads'),
                )
            );

            return $data;
        });
    }

    /**
     * Enqueue admin scripts
     */
    public function admin_enqueue_scripts()
    {
        wp_enqueue_script('peepso-admin-file-uploads',
            PeepSo::get_asset('js/admin.js', __FILE__),
            array('jquery', 'underscore'), self::PLUGIN_VERSION, TRUE);
    }

	/**
     * Change the activity stream item action string
     * @param  string $action The default action string
     * @param  object $post   The activity post object
     * @return string
     */
    public function activity_stream_action($action, $post)
    {
        if (self::MODULE_ID === intval($post->act_module_id)) {
            $files = $this->get_files($post->ID);
            $count = count($files);
            if ($count > 1) {
                $action = sprintf(__(' uploaded %d files', 'peepsofileuploads'), $count);
            } else {
                $action = __(' uploaded a file', 'peepsofileuploads');
            }
		}
        return ($action);
    }


	/**
	* Sets the activity's module ID to the plugin's module ID
	* @param  array $activity
	* @return array
	*/
    public function activity_insert_data($activity)
    {
        $input = new PeepSoInput();

        // SQL safe
        $type = $input->value('type','',FALSE);

        if ('files' === $type) {
            $activity['act_module_id'] = self::MODULE_ID;
		}

        return ($activity);
    }

    /**
     * Adds the postmeta to the post, only called when submitting from the file tab
     * @param  int $post_id The post ID
     */
    public function after_add_post($post_id)
    {
        $input = new PeepSoInput();
        $type = $input->value('type','',FALSE);

        if ('files' !== $type) {
            return;
		}

        $group_id = $input->int('group_id', 0);
        $module_id = $input->int('module_id', 0);
        $files = $input->value('files', '');

        if (class_exists('PeepSoGroupsPlugin') && $module_id && $group_id && $module_id == PeepSoGroupsPlugin::MODULE_ID) {
            $user_or_group_id = $group_id;
        } else {
            $user_or_group_id = get_current_user_id();
        }

        if (count($files)) {
            foreach ($files as $file) {
                $this->save_uploaded_file($file, $user_or_group_id, $post_id, $module_id);
            }
        }
    }

    public function after_add_comment($post_id, $act_id, $did_notify = FALSE, $did_email = FALSE)
    {
        $input = new PeepSoInput();
        $file = $input->value('file', '');

        if (!$file) {
            return;
        }

        $user_or_group_id = get_current_user_id();
        $module_id = self::MODULE_ID;

        if (class_exists('PeepSoGroupsPlugin')) {
            $parent_post = $this->root_post($post_id);
            if ($parent_post) {
                $group_id = get_post_meta($parent_post->ID, 'peepso_group_id', TRUE);
                if ($group_id) {
                    $user_or_group_id = $group_id;
                    $module_id = PeepSoGroupsPlugin::MODULE_ID;
                }
            }
        }

        // delete previously uploaded file
        $this->delete_content($post_id);

        $this->save_uploaded_file($file, $user_or_group_id, $post_id, $module_id);

    }

    public static function extension($ext, $filename) {
        if(!strlen($ext) && strstr($filename, '.')) {
            $ext = explode('.', $filename);
            $ext = end($ext);
        }

        return $ext;
    }

    public function save_uploaded_file($filename, $user_or_group_id, $post_id, $module_id = 0) {
        // move file to wordpress uploads directory
        if (class_exists('PeepSoGroupsPlugin') && $module_id == PeepSoGroupsPlugin::MODULE_ID) {
            $dir = PeepSoFileUploads::get_upload_dir(0, $user_or_group_id, $module_id);
            $key = 'group_id';
        } else {
            $dir = PeepSoFileUploads::get_upload_dir($user_or_group_id, 0, $module_id);
            $key = 'user_id';
        }

        $source_file = $dir . $filename;

        $wp_filetype = wp_check_filetype($source_file);

        $real_filename = md5(time() . $source_file) . '.' . $wp_filetype['ext'];
        $upload_file = wp_upload_bits($real_filename, null, file_get_contents($source_file));

        $ext = PeepSoFileUploads::extension($wp_filetype['ext'], $filename);

        $post_content = [
            $key => (string) $user_or_group_id,
            'size' => wp_filesize($source_file),
            'extension' => $ext,
        ];

        // save attachment
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_parent'    => $post_id,
            'post_author'    => get_current_user_id(),
            'post_title'     => $filename,
            'post_content'   => json_encode($post_content),
            'post_status'    => 'inherit'
        );
        $attachment_id = wp_insert_attachment($attachment, $upload_file['file'], $post_id);

        if (!is_wp_error($attachment_id)) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php' );
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload_file['file']);
            wp_update_attachment_metadata($attachment_id,  $attachment_data);

            // delete temporary file
            @unlink($source_file);

            // remove from mayfly
            $uploaded_files = PeepSo3_Mayfly::get('uploaded_files');

            if (strpos($uploaded_files, '[') !== FALSE) {
                $uploaded_files = str_replace(['[', ']'], ['{', '}'], $uploaded_files);
            }

            $uploaded_files = json_decode($uploaded_files, TRUE);
            $mayfly_index = array_search($source_file, $uploaded_files);

            if ($mayfly_index) {
                unset($uploaded_files[$mayfly_index]);
                self::update_uploaded_files_in_mayfly($uploaded_files);
            }

            return $attachment_id;
        }
    }

    /**
     * Attach the file to the post display
     * @param  object $post The post
     */
    public function attach_file($post, $post_id = 0, $act_module_id = 0)
    {
        $post_types = [PeepSoActivityStream::CPT_COMMENT];
        if (class_exists('PeepSoMessagesPlugin')) {
            $post_types[] = PeepSoMessagesPlugin::CPT_MESSAGE;
        }

        if ($post->act_module_id != self::MODULE_ID && !in_array($post->post_type, $post_types)) {
            return;
        }

        $data = [
            'files' => $this->get_files($post->ID)
        ];

        PeepSoTemplate::exec_template('file', 'content-media', $data);
    }

	public function permissions_files_upload($permission)
	{
		$url = PeepSoUrlSegments::get_instance();

        $user_id = get_current_user_id();

		if ($url->get(1)) {
			if ($viewed_user = get_user_by('slug', $url->get(1))) {
				$user_id = $viewed_user->ID;
			}
		}

		// only on own profile
		if ($url->get(0) == 'peepso_profile' && $user_id !== get_current_user_id()) {
            $permission = FALSE;
		}

		// if in group view and group integration is not disabled
		if($url->get(0) == 'peepso_groups' && $permission) {
            $permission = TRUE;
		}

        return $permission;
	}

	/**
     * Disable repost on file
     * @param array $actions The default options per post
     * @return  array
     */
	public function activity_post_actions($actions) {
		if ($actions['post']->act_module_id == self::MODULE_ID) {
			unset($actions['acts']['repost']);
		}
		return $actions;
	}

    /**
     *
     * @param array $options
     * @return array $options
     */
    public function post_filters($options) {

        return $options;
    }

    public function get_files($post_id) {
        $attachments = get_posts([
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_parent' => $post_id
        ]);

        return $attachments;
    }

    public function delete_content($post_id) {
        $attachments = $this->get_files($post_id);

        if ($attachments) {
            foreach ($attachments as $attachment) {
                wp_delete_attachment($attachment->ID, TRUE);
            }
        }
    }

    public function rest_api($rest_paths) {
        $rest_paths[] = dirname(__FILE__)  . DIRECTORY_SEPARATOR . 'api'. DIRECTORY_SEPARATOR . 'rest' . DIRECTORY_SEPARATOR . PeepSo3_API::REST_V;
        return $rest_paths;
    }

    /**
     * Add profile submenu item.
     *
     * @param array $links
     * @return array
     */
    public function filter_profile_segment_menu_links($links)
    {
        $links['files'] = array(
            'href' => 'files',
            'label'=> __('Files', 'peepsofileuploads'),
            'icon' => 'gcis gci-file'
        );

        return $links;
    }

    /**
     * Add group submenu item.
     *
     * @param array $links
     * @return array
     */
    public function filter_group_segment_menu_links($links)
    {
        $links[30][] = array(
            'href' => 'files',
            'title'=> __('Files', 'peepsofileuploads'),
            'icon' => 'gcis gci-file'
        );

		ksort($links);
        return $links;
    }

    public function peepso_profile_segment_files($url_segments)
    {
        echo PeepSoTemplate::exec_template('file', 'profile');
    }

    public function peepso_group_segment_files($args, $url_segments)
    {
        echo PeepSoTemplate::exec_template('file', 'group', $args);
    }

    public function register_widgets($widgets)
    {
        // register widgets
        foreach (scandir($widget_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'widgets' . DIRECTORY_SEPARATOR) as $widget) {
            if (strlen($widget) >= 5) {
                require_once($widget_dir . $widget);
            }
        }
        return array_merge($widgets, $this->widgets);
    }

    public static function prepare_for_display($post)
    {
        $dir = plugin_dir_url(__FILE__);

        $post_content = json_decode($post->post_content);

        // check if icon exists
        if (file_exists(plugin_dir_path(__FILE__)) . '/assets/images/filetype/' . $post_content->extension . '.png') {
            $icon = $post_content->extension . '.png';
        } else {
            $icon = 'default.png';
        }

        $data = [
            'id'            => $post->ID,
            'name'          => $post->post_title,
            'size'          => size_format($post_content->size),
            'icon'          => $dir . '/assets/images/filetype/' . $icon,
            'download_link' => esc_url_raw( rest_url( '/peepso/v1/' ) ) . 'file_download?id=' . $post->ID,
            'can_delete'    => PeepSoFileUploads::can_delete($post->ID),
            'extension'     => PeepSoFileUploads::extension($post_content->extension, $post->post_title),
        ];

        return $data;
    }

    public static function can_delete($id)
    {
        $user_id = get_current_user_id();

        if (!$id || !$user_id) {
            return FALSE;
        }

        $post = get_post($id);

        if (!$post) {
            return FALSE;
        }

        // check parent
        $parent = get_post($post->post_parent);

        // if parent post is a comment or chat
        if ($parent && ($parent->post_type == PeepSoActivityStream::CPT_COMMENT || (class_exists('PeepSoMessagesPlugin') && $parent->post_type == PeepSoMessagesPlugin::CPT_MESSAGE))) {
            return FALSE;
        }

        if (PeepSo::is_admin() || $post->post_author == $user_id) {
            return TRUE;
        }

        $post_content = json_decode($post->post_content, TRUE);

        if (is_array($post_content) && array_key_exists('group_id', $post_content) && class_exists('PeepSoGroupsPlugin')) {
            $group_user = new PeepSoGroupUser($post_content['group_id'], $user_id);

            if ($group_user && $group_user->can('edit_file')) {
                return TRUE;
            }
        } else if (is_array($post_content) && array_key_exists('user_id', $post_content) && $post_content['user_id'] == $user_id) {
            return TRUE;
        }

        return FALSE;
    }

    public static function get_upload_dir($user_id, $group_id = 0, $module_id = 0)
    {
        if (class_exists('PeepSoGroupsPlugin') && $group_id && $module_id == PeepSoGroupsPlugin::MODULE_ID) {
            $part = 'groups/' . $group_id;
        } else {
            $part = 'users/' . $user_id;
        }

        return PeepSo::get_peepso_dir() . $part . '/files/';
    }

    public static function raw_input($name, $default = '') {
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            parse_str($raw, $output);

            if (isset($output[$name])) {
                return $output[$name];
            }
        }

        return ($default);
    }

    public function activity_allow_empty_content($allowed)
    {
        $input = new PeepSoInput();
        $type = $input->value('type', '', FALSE); // SQL Safe
        if ('files' === $type) {
            $allowed = TRUE;
        }

        return $allowed;
    }

    public function activity_allow_empty_comment($allowed)
    {
        $input = new PeepSoInput();
        $file = $input->value('file', FALSE, FALSE);
        if (FALSE !== $file) {
            $allowed = TRUE;
        }

        return $allowed;
    }

    /**
     * This function inserts File Uploads icon to the comments box
     * @param array $interactions
     * @param int $post_id
     * @return array
     */
    public function commentsbox_interactions($interactions, $post_id = FALSE)
    {
        if (!apply_filters('peepso_permissions_files_upload', TRUE)) {
            return $interactions;
        }

        $interactions['files'] = array(
            'icon' => 'gcis gci-file',
            'class' => 'ps-comments__input-action ps-js-comment-files',
            'title' => __('Attach a file', 'peepsofileuploads')
        );

        return $interactions;
    }

    /**
     * This function inserts File Uploads UI to the comments box
     * @param array $interactions
     * @param int $post_id
     * @return array
     */
    public function commentsbox_addons($addons, $post_id = FALSE)
    {
        $file = array();
        $html = PeepSoTemplate::exec_template('file', 'commentbox', $file, TRUE);
        array_push($addons, $html);
        return $addons;
    }

    /**
     * Add additional File Uploads UI to message input
     * @param array $options The additional addons to be attached to message input
     * @return  array
     */
    public function message_input_addons($addons)
    {
        if (!apply_filters('peepso_permissions_files_upload', TRUE)) {
            return $addons;
        }
        $addons[] = PeepSoTemplate::exec_template('file', 'chatbox', NULL, TRUE);
        return $addons;
    }

    /**
     * Add additional mime types
     * @param array $mimes The default upload mime types from wordpress
     * @return  array
     */
    public function get_additional_mime_types($mimes)
    {
        // check acceptable file type
        $filetypes = PeepSo::get_option_new('fileuploads_allowed_filetype');
        if ($filetypes) {
            $filetypes = array_map('trim', explode(PHP_EOL, $filetypes));
            foreach ($filetypes as $filetype) {
                if (array_key_exists(strtolower($filetype), PeepSoFileUploads::$additional_mime_types)) {
                    $mimes[strtolower($filetype)] = PeepSoFileUploads::$additional_mime_types[strtolower($filetype)];
                }
            }
        }

        return $mimes;
    }

    private function root_post($post_id) {
        $peepso_activity = new PeepSoActivity();

        // get root post
        $comment = $peepso_activity->get_comment($post_id);
		$comment = $comment->post;

		if ($comment) {
			$root_act = $peepso_activity->get_activity_data($comment->act_comment_object_id, $comment->act_comment_module_id);
			$root_post = $peepso_activity->get_activity_post($root_act->act_id);

			// if root post still a comment
			if ($root_post->post_type == PeepSoActivityStream::CPT_COMMENT) {
				$comment = $root_post;
				$root_act = $peepso_activity->get_activity_data($comment->act_comment_object_id, $comment->act_comment_module_id);
				$root_post = $peepso_activity->get_activity_post($root_act->act_id);
			}
		}

        return $root_post;
    }

    /**
     * Get maximum upload size for a single file upload.
     *
     * @return int
     */
    public static function max_upload_size()
    {
        $max_upload_size = PeepSo::get_option_new('fileuploads_max_upload_size');

        // use WP max upload size if it is smaller than PeepSo max upload size
        $wp_max_size = max(wp_max_upload_size(), 0);
        $wp_max_size /= pow(1024, 2);
        if ($wp_max_size < $max_upload_size || empty($max_upload_size)) {
            $max_upload_size = $wp_max_size;
        }

        return $max_upload_size;
    }

    public static function save_to_mayfly($value)
    {
        global $wpdb;

        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}peepso_mayfly SET value = CONCAT_WS(IF(CHAR_LENGTH(value)>2, ',', ''), SUBSTRING(value, 1, CHAR_LENGTH(value) - 1), SUBSTRING(%s, 2)) WHERE `name` = %s", $value, 'uploaded_files'));
    }

    public static function update_uploaded_files_in_mayfly($uploaded_files)
    {
        if (count($uploaded_files) > 0) {
            $uploaded_files = json_encode($uploaded_files);
        } else {
            $uploaded_files = '{}';
        }
        PeepSo3_Mayfly::set('uploaded_files', $uploaded_files);
    }
}

PeepSoFileUploads::get_instance();
