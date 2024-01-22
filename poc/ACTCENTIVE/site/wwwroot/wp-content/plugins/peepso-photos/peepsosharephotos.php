<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3U2Q4bUI0eUJSZ2pJZjFCWXprdFArcGNkMStiRGNpTE1XdGtUMGdlc2pWY1dLS2J1VHJrNVpnYnlROGZyVmlTVGRyWmlKUlRZQmx3ZzFjNWcwTFdBUjViUHdSMjRiY1BhUjVlVC9uT0h2ZlBERzVHcUo4NEUvbFcvWmw2d2YzWElRRFVnN1N6WVo3L0dwVzdxdDNyMXEx*/
/**
 * Plugin Name: PeepSo Core: Photos
 * Plugin URI: https://peepso.com
 * Description: Photo uploads and albums
 * Author: PeepSo
 * Author URI: https://peepso.com
 * Version: 6.2.7.0
 * Copyright: (c) 2015 PeepSo, Inc. All Rights Reserved.
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: picso
 * Domain Path: /language
 *
 * We are Open Source. You can redistribute and/or modify this software under the terms of the GNU General Public License (version 2 or later)
 * as published by the Free Software Foundation. See the GNU General Public License or the LICENSE file for more details.
 * This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY.
 */

class PeepSoSharePhotos
{
    private static $_instance = NULL;

    private $url_segments;
    private $view_user_id;

    public $widgets = array(
        'PeepSoWidgetPhotos',
        'PeepSoWidgetCommunityphotos'
    );

    public $_photos_model = NULL;

    public $file_avatar;
    public $file_cover;

    // default system album name for each user
    private $photo_system_album;

    const PLUGIN_VERSION = '6.2.7.0';
    const PLUGIN_RELEASE = ''; //ALPHA1, RC1 etc, '' for STABLE
    const PLUGIN_NAME = 'Core: Photos';
    const PLUGIN_EDD = 221;
    const PLUGIN_SLUG = 'picso';
    const MODULE_ID = 4;

    // post meta key for photo type (avatar/cover)
    const POST_META_KEY_PHOTO_TYPE          = 'peepso_photo_type';
    const POST_META_KEY_PHOTO_TYPE_AVATAR   = 'peepso_avatar';
    const POST_META_KEY_PHOTO_TYPE_COVER    = 'peepso_cover';

    // post meta key for photo comments
    const POST_META_KEY_PHOTO_COMMENTS      = 'peepso_photo_comments';

    // post meta for albums
    const POST_META_KEY_PHOTO_TYPE_ALBUM    = 'peepso_album';
    const POST_META_KEY_PHOTO_COUNT         = 'peepso_photo_count';

    const ALBUM_CUSTOM  = 0;
    const ALBUM_AVATARS = 1;
    const ALBUM_COVERS  = 2;
    const ALBUM_STREAM  = 3;

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

    /**
     * Initialize all variables, filters and actions
     */
    private function __construct()
    {
        /** VERSION INDEPENDENT hooks **/

        // Admin
        add_filter('peepso_license_config', function($list){
            $data = array(
                'plugin_slug' => self::PLUGIN_SLUG,
                'plugin_name' => self::PLUGIN_NAME,
                'plugin_edd' => self::PLUGIN_EDD,
                'plugin_version' => self::PLUGIN_VERSION
            );
            $list[] = $data;
            return ($list);
        });
        if (is_admin()) {
            add_action('admin_init', array(&$this, 'peepso_check'));
        }

        // Compatibility
        add_filter('peepso_all_plugins', function($plugins){
            $plugins[plugin_basename(__FILE__)] = get_class($this);
            return $plugins;
        });
        add_filter('peepso_free_bundle_should_brand', '__return_true');

        // Translations
        add_action('plugins_loaded', function(){
            $path = str_ireplace(WP_PLUGIN_DIR, '', dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
            load_plugin_textdomain('picso', FALSE, $path);
        });

        // Activation
        register_activation_hook(__FILE__, array(&$this, 'activate'));

        /** VERSION LOCKED hooks **/
        if(self::ready()) {
            if (!PeepSoLicense::check_license(self::PLUGIN_EDD, self::PLUGIN_SLUG)) {
                return;
            }

            if(is_admin()) {
                add_filter('peepso_admin_config_tabs', array(&$this, 'admin_config_tabs'));
            }

            add_action('peepso_init', array(&$this, 'init'));
            add_filter('peepso_widgets', array(&$this, 'register_widgets'));
        }
    }

    public function get_photos_model() {

        $this->_photos_model = new PeepSoPhotosModel();
        return ($this->_photos_model);
    }

    /**
     * Retrieve singleton class instance
     * @return PeepSoPhotos instance
     */
    public static function get_instance()
    {
        if (NULL === self::$_instance) {
            self::$_instance = new self();
        }
        return (self::$_instance);
    }

    /*
     * Initialize the PeepSoPhotos plugin
     */
    public function init()
	{
        // set up autoloading, need these in the activate method.
        PeepSo::add_autoload_directory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR);
        PeepSoTemplate::add_template_directory(plugin_dir_path(__FILE__));
        if (is_admin()) {
            PeepSoPhotosAdmin::get_instance();

            add_filter('peepso_activity_stream_config', array(&$this, 'set_activity_stream_config'),10,1);
            add_filter('peepso_groups_general_config', array(&$this, 'set_groups_general_config'), 10, 1);

        } else {
            add_filter('peepso_postbox_interactions', array(&$this, 'postbox_interactions'), 100, 2);
            add_filter('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
            add_filter('peepso_post_types', array(&$this, 'post_types'), 10, 2);
            add_filter('peepso_postbox_tabs', array(&$this, 'postbox_tabs'));
            add_filter('peepso_activity_stream_action', array(&$this, 'activity_stream_action'), 10, 2);
            add_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'), 10, 1);
            add_filter('peepso_activity_allow_empty_comment', array(&$this, 'activity_allow_empty_comment'), 10, 1);
            add_filter('peepso_activity_insert_data', array(&$this, 'activity_insert_data'));
            add_filter('peepso_get_object_photo', array(&$this, 'get_photo_group'), 10, 2);
            add_filter('peepso_get_total_object_photo', array(&$this, 'get_total_object_photo'), 10, 2);
            add_filter('peepso_activity_get_post', array(&$this, 'activity_get_post'), 10, 4);
            add_filter('peepso_activity_post_id', array(&$this, 'activity_post_id'), 10, 2);

            add_action('peepso_activity_after_add_post', array(&$this, 'after_add_post'), 20, 2);
            add_action('peepso_activity_post_attachment', array(&$this, 'attach_photos'), 20, 1);
            add_action('peepso_ajax_before_send', array(&$this, 'before_send_ajax'), 10, 1);
            add_action('peepso_before_ajax_delete_activity', array(&$this, 'before_ajax_delete'), 10, 2);
            add_action('peepso_activity_change_privacy', array(&$this, 'change_photos_privacy'), 20, 2);
            add_action('peepso_delete_content', array(&$this, 'delete_content'));
            add_action('peepso_activity_delete', array(&$this, 'delete_photo'));
            add_action('peepso_messages_after_conversation_title', array(&$this, 'attach_photos'));
            add_filter('peepso_activity_post_actions', array(&$this, 'add_post_actions'),20);
            add_filter('peepso_post_filters', array(&$this, 'post_filters'), 20,1);
            add_filter('peepso_activity_post_clauses',  array(&$this, 'filter_post_clauses'), 20, 2);

            add_filter('peepso_privacy_access_levels', array(&$this, 'privacy_access_levels'), 10, 1);

            // change avatar & cover section
            add_action('peepso_user_after_change_avatar', array(&$this, 'after_change_avatar'), 10, 4);
            add_action('peepso_user_after_change_cover', array(&$this, 'after_change_cover'), 10, 2);

            // comments addons
            add_filter('peepso_commentsbox_interactions', array(&$this, 'commentsbox_interactions'), 10, 2);
            add_filter('peepso_commentsbox_addons', array(&$this, 'commentsbox_addons'), 10, 2);
            add_action('peepso_activity_comment_attachment', array(&$this, 'comments_attach_photo'), 10);
            add_action('peepso_after_add_comment', array(&$this, 'after_add_comment'), 10, 4);
            add_action('peepso_activity_after_save_comment', array(&$this, 'after_save_comment'), 10, 2);

            // album comments
            add_filter('peepso_get_object_album', array(&$this, 'get_album_comments'), 10, 2);
            add_action('peepso_activity_after_add_post', array(&$this, 'after_add_post_album'), 10, 2);

            // edit post
            add_action('peepso_activity_after_save_post', array(&$this, 'after_save_post'), 10, 1);

            // notifications
            add_filter('peepso_notifications_activity_type', array(&$this, 'notifications_activity_type'), 10, 3);
            add_filter('peepso_activity_user_comment_object_id', function($external_id, $module_id) {

                if ($module_id == self::MODULE_ID) {
                    $photo = $this->get_photos_model()->get_photo($external_id);
                    if(NULL != $photo) {
                        return $photo->pho_post_id;
                    }
                }

                return $external_id;
            }, 10, 2);

            add_filter('peepso_message_input_addons',   array(&$this, 'message_input_addons'), 10, 1);

            add_filter('peepso_check_permissions-post_edit', array(&$this, 'check_permissions_edit_content'), 99, 4);
            add_filter('peepso_check_permissions-post_delete', array(&$this, 'check_permissions_delete_content'), 99, 4);
            PeepSoPhotosShortcode::register_shortcodes();

            add_filter('peepso_notification_link', array(&$this, 'modify_notification_link'), 10, 2);
        }


        add_filter('posts_clauses_request', function($clauses, $query) {
            return ($clauses); // #2113 comments taken from albums instead of single photo. The MODAL flag is supposed to help, but I never managed to get it working

            global $wpdb;

            if (
                    isset($query->query['_comment_object_id']) &&
                    (isset($query->query['_comment_module_id']) && (self::MODULE_ID == $query->query['_comment_module_id'])) &&
                    (isset($query->query['_is_modal']) && TRUE == $query->query['_is_modal'])
            ) {
                // DO SOMETHING
                $where = $wpdb->prepare(' AND NOT EXISTS (SELECT `act`.`act_id` FROM '.$wpdb->prefix.PeepSoActivity::TABLE_NAME.' WHERE `act`.`act_external_id`= %d AND `act`.`act_module_id`= %d AND `act`.`act_description` IS NULL) ', $query->query['_comment_object_id'], $query->query['_comment_module_id']);
                $clauses['where'] .= $where;
            }


            return ($clauses);
        },999,2);


        add_filter('peepso_profile_alerts', array(&$this, 'profile_alerts'), 10, 1);
        add_filter('peepso_widgets', array(&$this, 'register_widgets'));

        // create album for user after user verified
        add_action('peepso_register_verified', array(&$this, 'create_album'), 10, 1);
        add_action('peepso_register_approved', array(&$this, 'create_album'), 10, 1);

        // Hooks into profile pages and "me" widget
        add_action('peepso_profile_segment_photos', array(&$this, 'peepso_profile_segment_photos'));
        add_filter('peepso_navigation_profile', array(&$this, 'filter_peepso_navigation_profile'));
        add_filter('peepso_rewrite_profile_pages', array(&$this, 'peepso_rewrite_profile_pages'));
        add_filter('peepso_filter_opengraph_' . self::MODULE_ID, array(&$this, 'peepso_filter_opengraph'), 10, 2);

		// Hook into Groups segment menu
		add_filter('peepso_group_segment_menu_links', array(&$this, 'filter_group_segment_menu_links'));
        add_action('peepso_group_segment_photos', array(&$this, 'peepso_group_segment_photos'), 10, 2);

        // Hook for getting root post
        add_filter('peepso_root_post_' . self::MODULE_ID, function($root) {
            $activity = new PeepSoActivity();

            $photo_type = get_post_meta($root->act_comment_object_id, self::POST_META_KEY_PHOTO_TYPE, true);

            // if post_types is album
            if($photo_type === self::POST_META_KEY_PHOTO_TYPE_ALBUM || $photo_type === self::POST_META_KEY_PHOTO_TYPE_AVATAR || $photo_type === self::POST_META_KEY_PHOTO_TYPE_COVER) {
                $root_activity = $activity->get_activity_data($root->act_comment_object_id, $root->act_comment_module_id);
                $root = $activity->get_activity_post($root_activity->act_id);
            } else {
                # @TODO: return root post for photo object
                $root_activity_id = $this->get_photos_model()->get_photo_activity($root->act_comment_object_id, NULL, NULL, FALSE, TRUE);
                $root = $activity->get_activity($root_activity_id);
            }

            return $root;
        });

        // Hooks for getting root object
        add_filter('peepso_root_object_' . self::MODULE_ID, function($root) {
            $activity = new PeepSoActivity();

            $photo_type = get_post_meta($root->act_comment_object_id, self::POST_META_KEY_PHOTO_TYPE, true);

            // if post_types is album
            if($photo_type === self::POST_META_KEY_PHOTO_TYPE_ALBUM || $photo_type === self::POST_META_KEY_PHOTO_TYPE_AVATAR || $photo_type === self::POST_META_KEY_PHOTO_TYPE_COVER) {
                $root_activity = $activity->get_activity_data($root->act_comment_object_id, $root->act_comment_module_id);
                $root = $activity->get_activity($root_activity->act_id);
            } else {
                $root_activity_id = $this->get_photos_model()->get_photo_activity($root->act_comment_object_id, NULL, NULL, FALSE, TRUE);
                $root = $activity->get_activity($root_activity_id);
            }

            return $root;
        });

        $this->url_segments = PeepSoUrlSegments::get_instance();

        $this->photo_system_album = array(
            array(
                'albumname' => __('Profile Avatars', 'picso'),
                'albumname_acc' => PeepSo::ACCESS_PUBLIC,
                'is_system'=> self::ALBUM_AVATARS),
            array(
                'albumname' => __('Profile Covers', 'picso'),
                'albumname_acc' => PeepSo::ACCESS_PUBLIC,
                'is_system'=> self::ALBUM_COVERS),
            array(
                'albumname' => __('Stream Photos', 'picso'),
                'albumname_acc' => PeepSo::ACCESS_PUBLIC,
                'is_system'=> self::ALBUM_STREAM));

        // Compare last version stored in transient with current version
        if( $this::PLUGIN_VERSION.$this::PLUGIN_RELEASE != PeepSo3_Mayfly::get($mayfly = 'peepso_'.$this::PLUGIN_SLUG.'_version')) {
            PeepSo3_Mayfly::set($mayfly, $this::PLUGIN_VERSION.$this::PLUGIN_RELEASE);
            $this->activate();
        }
    }

    public function license_notice()
    {
        PeepSo::license_notice(self::PLUGIN_NAME, self::PLUGIN_SLUG);
    }

    public function license_notice_forced()
    {
        PeepSo::license_notice(self::PLUGIN_NAME, self::PLUGIN_SLUG, true);
    }

	/**
	 * Plugin activation
	 * Check PeepSo
	 * @return bool
	 */
	public function activate()
	{
		if (!$this->peepso_check()) {
			return (FALSE);
		}

		require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'activate.php');
		$install = new PeepSoPhotosInstall();
		$res = $install->plugin_activation();
		if (FALSE === $res) {
			// error during installation - disable
			deactivate_plugins(plugin_basename(__FILE__));
		}

		return (TRUE);
	}

	/**
	 * Check if PeepSo class is present (ie the PeepSo plugin is installed and activated)
	 * If there is no PeepSo, immediately disable the plugin and display a warning
	 * Run license and new version checks against PeepSo.com
	 * @return bool
	 */
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


	/**
	 * Display a message about PeepSo not present
	 */
	public function peepso_disabled_notice()
	{
		?>
		<div class="error peepso">
			<strong>
				<?php echo sprintf(__('The %s plugin requires the PeepSo plugin to be installed and activated.', 'picso'), self::PLUGIN_NAME);?>
				<a href="plugin-install.php?tab=plugin-information&plugin=peepso-core&TB_iframe=true&width=772&height=291" class="thickbox">
					<?php echo __('Get it now!', 'picso');?>
				</a>
			</strong>
		</div>
		<?php
	}

    /**
     * Registers a tab in the PeepSo Config Toolbar
     * PS_FILTER
     *
     * @param $tabs array
     * @return array
     */
    public function admin_config_tabs($tabs)
    {
        if (isset($_GET['tab']) && $_GET['tab'] == 'photos' && isset($_GET['clear-aws-history']) && wp_verify_nonce($_GET['nonce'], 'peepso-config-nonce')) {
            PeepSoPhotosAWSErrors::clear_errors();
        }

        $tabs['photos'] = array(
            'label' => __('Photos', 'picso'),
            'icon' => 'https://cdn.peepso.com/icons/plugins/'.self::PLUGIN_EDD.'.svg',
            'tab' => 'photos',
            'description' => __('PeepSo Photos', 'picso'),
            'function' => 'PeepSoConfigSectionPhotos',
            'cat'   => 'core',
        );

        return $tabs;
    }

    /**
     * Modify the clauses to filter posts
     * @param  array $clauses
     * @param  int $user_id The owner of the activity stream
     * @return array
     */
    public function filter_post_clauses($clauses, $user_id = NULL)
    {
        global $wpdb;

        // Filter for groups joined
        $clauses['join'] .= ' LEFT JOIN `' . $wpdb->prefix  . PeepSoPhotosModel::TABLE . '` `pho` ON ' .
                                ' `' . $wpdb->posts . '`.`ID` = `pho`.`pho_post_id`';
                                // and `pho`.`pho_owner_id` = `' . $wpdb->posts . '`.`post_author`

        $clauses['where'] .= " AND (`pho`.`pho_owner_id` = `act`.`act_owner_id` OR (`pho`.`pho_id` IS NULL and `act`.`act_module_id` != ".self::MODULE_ID.")) ";

        return $clauses;
    }

    /*
     * enqueue scripts for peepsophotos
     */
    public function enqueue_scripts()
    {
        $logged_in = is_user_logged_in();

        wp_register_script('peepso-photos-bundle',
            PeepSo::get_asset('js/bundle.min.js', __FILE__),
            array('peepso', 'peepso-page-autoload'), self::PLUGIN_VERSION, TRUE);

        wp_register_script('peepso-page-photos', FALSE,
            array('peepso-photos-bundle'), self::PLUGIN_VERSION, TRUE);

        wp_register_script('peepso-page-albums', FALSE,
            array('peepso-photos-bundle'), self::PLUGIN_VERSION, TRUE);

        wp_register_script('peepso-photos-dropzone',
            $logged_in ? PeepSo::get_asset('js/dropzone.min.js', __FILE__) : FALSE,
            $logged_in ? array('peepso-npm', 'jquery-ui-sortable') : NULL,
            self::PLUGIN_VERSION, TRUE);

        wp_localize_script('peepso-photos-dropzone', 'psdata_photos_dropzone', array(
            'template' => PeepSoTemplate::exec_template('photos', 'photo-dropzone', array(), TRUE),
            'template_preview' => PeepSoTemplate::exec_template('photos', 'photo-dropzone-preview', array(), TRUE),
            'text_upload_failed_notice' => __('Upload Failed! Please try again.', 'picso'),
        ));

        wp_register_script('peepso-photos-postbox',
            $logged_in ? PeepSo::get_asset('js/postbox.min.js', __FILE__) : FALSE,
            $logged_in ? array('peepso', 'peepso-photos-dropzone') : NULL,
            self::PLUGIN_VERSION, TRUE);

        wp_register_script('peepso-photos-grid',
            PeepSo::get_asset('js/grid.min.js', __FILE__),
            array('peepso'), self::PLUGIN_VERSION, TRUE);
        wp_enqueue_script('peepso-photos-grid');

        // photo
        wp_register_script('peepso-photos',
            PeepSo::get_asset('js/peepsophotos.min.js', __FILE__),
            array('peepso-fileupload', 'peepso-photos-postbox', 'peepso-photos-dropzone', 'peepso-load-image', 'peepso-postbox', 'peepso-window'), PeepSo::PLUGIN_VERSION, TRUE);

        $max_file_uploads = ini_get('max_file_uploads');

        // get album id
        $album_id = FALSE;
        if(strtolower($this->url_segments->get(3)) == 'album') {
            $album_id = $this->url_segments->get(4);
        }

        wp_localize_script('peepso', 'peepsophotosdata', array(
            'template_popup' => PeepSoTemplate::exec_template('photos', 'popup', NULL, TRUE),
            'max_file_uploads' => $max_file_uploads,
            'error_max_file_uploads' => __('Only ' . $max_file_uploads . ' photos can be uploaded each time. To add more photos please click "Upload More Photos" button.', 'picso'),
            'error_unsupported_format' => __('Supported formats are: gif, jpg, jpeg, tiff, png, and webp.', 'picso'),
            'album_id' => $album_id,
            'gif_autoplay' => PeepSo::get_option_new('photos_gif_autoplay'),
        ));

        // Enqueue this script when photo widget is loaded.
        wp_register_script('peepso-photos-widget',
            PeepSo::get_asset('js/widget.js', __FILE__),
            array('peepso'), self::PLUGIN_VERSION, TRUE);

        if (apply_filters('peepso_free_bundle_should_brand', FALSE)) {
            wp_add_inline_script('peepso', "setTimeout(() => peepso.observer.do_action('show_branding'), 1000);");
        }
    }

	/**
	 * Append any notices and errors before ajax response is sent
	 * @param object PeepSoAjaxResponse instance
	 */
	public function before_send_ajax(PeepSoAjaxResponse $resp)
	{
		if (!PeepSoPhotosModel::$notices) {
			return;
		}

		foreach (PeepSoPhotosModel::$notices as $notice)
			$resp->notice($notice);
		PeepSoPhotosModel::$notices = array();
	}

    /**
     * This function inserts the photo UI on the post box
     * @param array $interactions is the formated html code that get inserted in the postbox
     * @param array $params
     */
    public function postbox_interactions($interactions, $params = array())
    {
        if(!apply_filters('peepso_permissions_photos_upload', TRUE)) {
            return $interactions;
        }

        wp_enqueue_style('peepso-fileupload');

        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('peepso-fileupload');
        wp_enqueue_script('peepso-load-image');
        wp_enqueue_script('peepso-photos');

        if (isset($params['is_current_user']) && $params['is_current_user'] === FALSE) {
            return ($interactions);
        }

        $interactions['photos'] = array(
            'icon' => 'gcis gci-camera',
            'id' => 'photo-post',
            'class' => 'ps-postbox__menu-item',
            'click' => 'return;',
            'label' => '',
            'title' => __('Photos', 'picso'),
            'style' => 'display:none'
        );

        return ($interactions);
    }

    /**
     * This function manipulates the image/photo uploaded including uploading to Amazon S3
     * @param int $post_id The post ID
     * @param int $act_id The activity ID
     */
    public function after_add_post($post_id, $act_id)
    {
        $input = new PeepSoInput();
        $files = $input->value('files', array(), FALSE); // SQL safe

        // SQL safe, not used in queries
        if (count($files) > 0 && 'photo' === $input->value('type','',FALSE)) {
            $this->get_photos_model()->save_images($files, $post_id, $act_id);
        }
    }

    /**
     * This function will update album description after user edit post on stream.
     * @param int $post_id The post ID
     */
    public function after_save_post($post_id)
    {
        $photo_type = get_post_meta($post_id, self::POST_META_KEY_PHOTO_TYPE, true);
        if ( $photo_type === self::POST_META_KEY_PHOTO_TYPE_ALBUM )
        {
            $input = new PeepSoInput();
            $activity = new PeepSoActivity();
            $post = $input->raw('post');
            $owner_id = $activity->get_author_id($post_id);
            $post = htmlspecialchars($post);
            $post = substr(PeepSoSecurity::strip_content($post), 0, PeepSo::get_option('site_status_limit', 4000));

            // save photo album description
            $photos_album_model = new PeepSoPhotosAlbumModel();
            $album = $photos_album_model->get_album_by_post($post_id, $owner_id);
            if($album !== NULL) {
                $photos_album_model->set_photo_album_description($post, $album->pho_album_id);
            }
        }
    }

    /**
     * Sets the activity's module ID to the plugin's module ID
     * @param  array $activity
     * @return array
     */
    public function activity_insert_data($activity)
    {
        $input = new PeepSoInput();

        // SQL safe, not used in queries
        if ('photo' === $input->value('type','', FALSE)) {
            $activity['act_module_id'] = self::MODULE_ID;
        }

        return ($activity);
    }

    /**
     * Adds the Photos tab to the available post type options
     * @param  array $post_types
     * @param  array $params
     * @return array
     */
    public function post_types($post_types, $params = array())
    {
        if (isset($params['is_current_user']) && $params['is_current_user'] === FALSE) {
            return ($post_types);
        }

        if(!apply_filters('peepso_permissions_photos_upload', TRUE)) {
            return $post_types;
        }

        $post_types['photos'] = array(
            'icon' => 'gcis gci-camera',
            'name' => __('Photo', 'picso'),
            'class' => 'ps-postbox__menu-item',
        );

        return ($post_types);
    }

    /**
     * Displays the UI for the photo post type
     * @return string The input html
     */
    public function postbox_tabs($tabs)
    {
        wp_enqueue_script('peepso-fileupload');
        wp_enqueue_script('peepso-load-image');
        wp_enqueue_script('peepso-photos');

        $max_size = intval(PeepSo::get_option('photos_max_upload_size'));

        $wp_max_size = max(wp_max_upload_size(), 0);
        $wp_max_size /= pow(1024, 2); // convert to MB
        // use WP max upload size if it is smaller than PeepSo max upload size
        if ($wp_max_size < $max_size) {
            $max_size = $wp_max_size;
        }

        if(!apply_filters('peepso_permissions_photos_upload', TRUE)) {
            return $tabs;
        }

        $data = array();
        $data['photo_size'] = array(
            'max_width' => intval(PeepSo::get_option('photos_max_image_width')),
            'max_height' => intval(PeepSo::get_option('photos_max_image_height')),
            'max_size' => $max_size,
        );
        $tabs['photos'] = PeepSoTemplate::exec_template('photos', 'postbox-photos', $data, TRUE);

        return ($tabs);
    }

    /**
     * Deletes photos associated to a post when it is deleted
     * @param  int $post_id The post ID
     */
    public function delete_content($post_id)
    {
        $this->get_photos_model()->delete_content($post_id);
    }

    /**
     * Attach the photos to the post display
     * @param  object $post The post
     */
    public function attach_photos($stream_post = NULL)
    {
        $order = 'asc';
        $photo_type = get_post_meta($stream_post->ID, self::POST_META_KEY_PHOTO_TYPE, true);

        // if type is photo album, show latest photos as first ones.
        if($photo_type === self::POST_META_KEY_PHOTO_TYPE_ALBUM) {
            $order = 'desc';
        }

        $photos = $this->get_photos_model()->get_post_photos($stream_post->ID, $order);
        $count = count($photos);

        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                if (isset($photos[$i]->pho_thumbs)) {
                    if (isset($photos[$i]->pho_thumbs['l'])) {
                        $photos[$i]->location = $photos[$i]->pho_thumbs['l'];
                    } else {
                        $photos[$i]->location = $photos[$i]->pho_thumbs['m'];
                    }
                }

            }

            $max_photos = apply_filters('peepso_photos_max_visible_photos', 5);
            $template = apply_filters('peepso_photos_get_template', array('photos', 'activity-content'));
            PeepSoTemplate::exec_template($template[0], $template[1], array('photos' => $photos, 'max_photos' => $max_photos, 'count_photos' => $count ));
        }

    }

    /**
     * Change the activity stream item action string
     * @param  string $action The default action string
     * @param  object $post   The activity post object
     * @return string
     */
    public function activity_stream_action($action, $post)
    {
        wp_enqueue_script('peepso-photos');

        if (self::MODULE_ID === intval($post->act_module_id)) {


            $photo = $this->get_photos_model()->get_photo($post->object_index_key);
            if(NULL != $photo) {
                $post = get_post($photo->pho_post_id);
            }

            $photo_type = get_post_meta($post->ID, self::POST_META_KEY_PHOTO_TYPE, true);
            if($photo_type === self::POST_META_KEY_PHOTO_TYPE_AVATAR) {
                $action = __(' uploaded a new avatar', 'picso');
                $action = apply_filters('peepso_photos_stream_action_change_avatar', $action, $post->ID);
            } else if($photo_type === self::POST_META_KEY_PHOTO_TYPE_COVER) {
                $action = __(' uploaded a new profile cover', 'picso');
                $action = apply_filters('peepso_photos_stream_action_change_cover', $action, $post->ID);
            } else if($photo_type === self::POST_META_KEY_PHOTO_TYPE_ALBUM) {
                $action = apply_filters('peepso_photos_stream_action_photo_album', $action, $post->ID);

                // modify action if only its empty
                if($action == '')
                {
                    $photos_album_model = new PeepSoPhotosAlbumModel();

                    // [USER] added [photo/photos] to [ALBUM NAME] album
                    $total_photos = get_post_meta($post->ID, self::POST_META_KEY_PHOTO_COUNT, true);
                    $album = $photos_album_model->get_photo_album($post->post_author, 0, $post->ID);

                    // generate link
                    $user = PeepSoUser::get_instance($post->post_author);
                    $link_to_album = $user->get_profileurl() . 'photos/album/' . $album[0]->pho_album_id;

                    $action = sprintf(_n(' added %1$d photo to the album: <a href="%3$s">%2$s</a>', ' added %1$d photos to the album: <a href="%3$s">%2$s</a>', $total_photos, 'picso'), $total_photos, $album[0]->pho_album_name, $link_to_album);
                }
            }
            else {
                $total_photos = $this->get_photos_model()->count_post_photos($post->ID);
                $action = sprintf(_n(' uploaded %1$d photo', ' uploaded %1$d photos', $total_photos, 'picso'), $total_photos);
            }
        } else if (FALSE === empty($post->act_repost_id)) { // check for reposted
            $peepso_activity = PeepSoActivity::get_instance();
            $repost_act = $peepso_activity->get_activity($post->act_repost_id);

            // fix "Trying to get property of non-object" errors
            if (is_object($repost_act) && self::MODULE_ID === intval($repost_act->act_module_id)) {
                $action = __(' shared photos', 'picso');
            }
        }

        return ($action);
    }

    /**
     * Removes the Only Me access if a post does not belong to the current user's stream
     * @param  array $acc
     * @return array
     */
    public function privacy_access_levels($acc)
    {
        global $post;

        if ($post instanceof WP_Post && property_exists($post, 'act_module_id') && self::MODULE_ID === intval($post->act_module_id)) {

            $photo_type = get_post_meta($post->ID, self::POST_META_KEY_PHOTO_TYPE, true);
            if($photo_type === self::POST_META_KEY_PHOTO_TYPE_AVATAR || $photo_type === self::POST_META_KEY_PHOTO_TYPE_COVER) {
                if (intval(get_current_user_id()) === intval($post->act_owner_id)) {
                    $whitelist = array(PeepSo::ACCESS_PUBLIC => array());
                    $acc = array_intersect_key($acc, $whitelist);
                }
            }

        }

        return ($acc);
    }

    /**
     * Iterates throught the $_photos ArrayObject and returns the current photo
     * @param int $photo_id
     * @return PeepSoUser
     */
    public function get_next_photo()
    {
        $iterator = $this->get_photos_model()->get_iterator();
        if (NULL !== $iterator && $iterator->valid()) {
            $photo = $iterator->current();

            $iterator->next();
            return ($photo);
        }

        return (FALSE);
    }

    /**
     * Shows a single photo.
     * @param WP_Post $photo A WP_Post object with a post type of peepso-photo.
     */
    public function show_photo($photo)
    {
        #4861 Invert sort for album photos to match how it is displayed in the stream post.
        $params = NULL;
        $photo_type = get_post_meta($photo->pho_post_id, self::POST_META_KEY_PHOTO_TYPE, true);
        if ($photo_type === self::POST_META_KEY_PHOTO_TYPE_ALBUM) {
            $params = array( 'sort' => 'desc' );
        }

        $onclick = "return ps_comments.open('" . $photo->pho_id . "', 'photo', null, " . str_replace('"', "'", json_encode( $params )) . ');';
        $photo->onclick = apply_filters('peepso_photos_photo_click', $onclick, $photo, $params);

        PeepSoTemplate::exec_template('photos', 'photo-item', (array)$photo);
    }

    /**
     * Shows a single photo comments.
     * @param WP_Post $photo A WP_Post object with a post type of peepso-photo.
     */
    public function show_photo_comments($photo)
    {
        $PeepSoSharePhotos = PeepSoSharePhotos::get_instance();
        $photo_url = $photo->photo_original;
        $is_gif = $PeepSoSharePhotos->is_gif_file($photo_url, $photo->photo_thumbs);

        if ($is_gif && isset($this->gif_file_uri)) {
            $photo_url = $this->gif_file_uri;
            $photo->onclick = apply_filters('peepso_photos_photo_comments_click', 'peepso.photos.show_image(\'' . $photo_url . '\'); return false;', $photo);
        } else {
            $photo->onclick = apply_filters('peepso_photos_photo_comments_click', 'peepso.simple_lightbox(\'' . $photo_url . '\'); return false;', $photo);
        }

        $photo = (array) $photo;
        $photo['photo_url'] = $photo_url;

        PeepSoTemplate::exec_template('photos', 'photo-comment', $photo);
    }

    /**
     * Checks if empty content is allowed
     * @param string $allowed
     * @return boolean always returns TRUE
     */
    public function activity_allow_empty_content($allowed)
    {
        $input = new PeepSoInput();

        // SQL safe, not used in queries
        $type = $input->value('type', '', FALSE);

        if ('photo' === $type || 'album' === $type) {
            $allowed = TRUE;
        }

        if(isset($this->file_avatar) || isset($this->file_cover) ) {
            $allowed = TRUE;
        }

        // allowed empty content after adding activity change avatar
        // SQL safe, WP sanitizes it
        if (FALSE !== wp_verify_nonce($input->value('_wpnonce','',FALSE), 'profile-photo')) {
            if ($input->int('use_gravatar') != 1) {
                $allowed = TRUE;
            }
        }

        // allowed empty content after adding activity change cover
        if (isset($_GET['cover'])) {
            $allowed = TRUE;
        }

        return ($allowed);
    }

    /**
     * Checks if empty comment is allowed
     * @param string $allowed
     * @return boolean always returns TRUE
     */
    public function activity_allow_empty_comment($allowed)
    {
        $input = new PeepSoInput();

        // SQL safe, not used in queries
        $photo = $input->value('photo','',FALSE);
        if (!empty($photo)) {
            $allowed = TRUE;
        }

        return ($allowed);
    }

    /**
     * modal comment callback
     * Returns the source URL and ID of photos belonging to the same post as $photo_id.
     * @param  array $objects  Array of URLs and IDs.
     * @param  int $photo_id The ID of the photo to get the group from.
     * @return array $objects Modified array of photo group by Post ID
     */
    public function get_photo_group($objects, $photo_id)
    {
        $activity = PeepSoActivity::get_instance();

        $input = new PeepSoInput();
        $owner_id = $input->int('user', NULL);
        $album_id = $input->int('album', NULL);

        // module, extends for groups / page / events
        $module_id = $input->int('module_id', 0);

        // override default sort
        $sort = $input->value('sort', NULL, array('asc', 'desc'));

        if($module_id !== 0) {
            $owner_id = apply_filters('peepso_photos_filter_owner_album', $owner_id);
        }

        $photo = $this->get_photos_model()->get_photo($photo_id);

        if ( $owner_id && $album_id ) { // album photo set
            $photos = $this->get_photos_model()->get_user_photos_by_album($owner_id, $album_id, 0, 0, $sort ? $sort : 'desc', $module_id);
        } else if ( $owner_id && $module_id == 0) { // user photo set
            $photos = $this->get_photos_model()->get_user_photos($owner_id, 0, 0, $sort ? $sort : 'desc', $module_id);
        } else { // post (activity) photo set
            $photos = $this->get_photos_model()->get_post_photos($photo->pho_post_id, $sort ? $sort : 'asc');
        }

        if (($count = count($photos)) > 1) {
            // #3189
            // get database timestamp
            $timestamp = $this->get_photos_model()->get_timestamp();
            // get hour differences with between server and database
            $hour = round( ( current_time( 'timestamp', 1 ) - strtotime($timestamp) ) / 3600, 0);

            foreach ($photos as $photo) {
                $post = $activity->get_post($photo->pho_post_id, NULL, NULL, FALSE, TRUE);
                // #3189 override post_date_gmt to use pho_created field

                if (!is_null($post->post)) {
                    $post->post->post_date_gmt = date('Y-m-d H:i:s', (strtotime($photo->pho_created) + 3600 * $hour));

                    $data = (array) $photo;
                    $data['count'] = $count;
                    $data['post'] = $post->post;
                    // TODO: the modal-photo-item template refers to $act_id - so it need to be created
                    // no need for this line, $photo contains act_id already
                    // $data['act_id'] = (1 === $count && $post->posts[0]->act_id) ? $post->posts[0]->act_id : '';

                    // checking batch upload
                    $key_object = $photo->pho_id;
                    if($this->get_photos_model()->count_post_photos($photo->pho_post_id) == 1) {
                        $new_act_id = $this->get_photos_model()->get_photo_activity($photo->pho_post_id);
                        if($new_act_id) {
                            $data['act_id'] = $new_act_id;
                        }
                        $key_object = $photo->pho_post_id;
                    }

                    $objects[$key_object] = array(
                        'object_index_key' => $photo->pho_id,
                        'module_id'        => self::MODULE_ID,
                        'content'          => PeepSoTemplate::exec_template('photos', 'modal-photo-item', $data, TRUE),
                        'post'             => $post->post,
                        'act_id'           => $data['act_id'],
                        'act_description'  => $data['act_description']
                    );
                }
            }
        } else {
            $photo_type = get_post_meta($photo->pho_post_id, self::POST_META_KEY_PHOTO_TYPE, true);
            // if post_types is album, just set to unpublish
            if($photo_type === self::POST_META_KEY_PHOTO_TYPE_ALBUM) {
                $post = $activity->get_post($photo->pho_post_id, NULL, NULL, FALSE, TRUE);
                $photo = array_pop($photos);
                $data = (array) $photo;
                $data['count'] = $count;
                $data['post'] = $post->post;
                //$data['act_id'] = (isset($data['post']->act_id)) ? $data['post']->act_id : '';

                $objects[$photo->pho_id] = array(
                    'object_index_key' => $photo->pho_id,
                    'module_id'        => self::MODULE_ID,
                    'content'          => PeepSoTemplate::exec_template('photos', 'modal-photo-item', $data, TRUE),
                    'post'             => $post->post,
                    'act_id'           => $data['act_id'],
                    'act_description'  => $data['act_description'],
                    'using_activity_desc' => TRUE
                );
            }else {
                $post = $activity->get_post($photo->pho_post_id, NULL, NULL, FALSE, TRUE);
                $photo = array_pop($photos);
                $data = (array) $photo;
                $data['count'] = $count;
                $data['post'] = $post->post;
                $data['act_id'] = (isset($data['post']->act_id)) ? $data['post']->act_id : '';

                $objects[$photo->pho_post_id] = array(
                    'object_index_key' => $photo->pho_id,
                    'module_id'        => self::MODULE_ID,
                    'content'          => PeepSoTemplate::exec_template('photos', 'modal-photo-item', $data, TRUE),
                    'post'             => $post->post,
                    'act_id'           => $data['act_id'],
                    'act_description'  => $data['act_description']
                );
            }
        }

        return ($objects);
    }

    public function get_total_object_photo($total_object, $photo_id) {
        $photo = $this->get_photos_model()->get_photo($photo_id);

        // checking batch upload
        $total_object = $this->get_photos_model()->count_post_photos($photo->pho_post_id);

        return $total_object;
    }

    /**
     * get album comments
     * Returns the comments for photos.
     * @param  array $objects  Array of URLs and IDs.
     * @param  int $photo_id The ID of the photo to get the group from.
     * @return array $objects Modified array of photo group by Post ID
     */
    public function get_album_comments($objects, $album_id)
    {
        $activity = PeepSoActivity::get_instance();

        $input = new PeepSoInput();

        $post = $activity->get_post($photo->pho_post_id);
        $photo = array_pop($photos);
        $data = (array) $photo;
        $data['count'] = $count;
        $data['post'] = $post->post;
        $data['act_id'] = (isset($data['post']->act_id)) ? $data['post']->act_id : '';

        $objects[$photo->pho_post_id] = array(
            'object_index_key' => $photo->pho_post_id,
            'module_id'        => self::MODULE_ID,
            'content'          => PeepSoTemplate::exec_template('photos', 'modal-photo-item', $data, TRUE),
            'post'             => $post->post,
            'act_id'           => $data['act_id'],
            'act_description'  => $data['act_description']
        );

        return ($objects);
    }

    /**
     * Returns the photo to display or NULL if the activity is not of this module
     * @param  string $post  The HTML post to display
     * @param  array $activity  The activity data
     * @param  int $owner_id The owner of the activity
     * @param  int $user_id The user requesting access to the activity post
     * @return mixed The HTML post to display | NULL if no relevant post is found
     */
    public function activity_get_post($post, $activity, $owner_id, $user_id)
    {
        // fix "Trying to get property of non-object" errors
        if (is_object($activity) && self::MODULE_ID === intval($activity->act_module_id)) {
            $photo = $this->get_photos_model()->get_photo($activity->act_external_id);

            if (NULL !== $photo) {
                $peepso_activity = PeepSoActivity::get_instance();
                $post = $peepso_activity->get_activity_post($activity->act_id);

                if (NULL !== $post) {
                    // Merge the two, so that the activity data is from act_id and not the containing post
                    $post = (object) array_merge((array) $post, (array) $activity);
                    $location = NULL;
                    $enable_aws_s3 = PeepSo::get_option('photos_enable_aws_s3');
                    if ('1' === $photo->pho_stored && $enable_aws_s3) {
                        $location = $photo->pho_token;
                    }
                    if (NULL === $location) {
                        $user = PeepSoUser::get_instance($photo->post_author);
                        $image_url = $user->get_image_url() . 'photos/';
                        $location = $image_url . $photo->pho_filesystem_name;
                    }
                    $photo->location = $location;
                    $photo->act_id = $post->act_id;
                    $photos = $this->get_photos_model()->set_photos(array($photo));

                    if (count($photos) > 0) {
                        $max_photos = apply_filters('peepso_photos_max_visible_photos', 5);
                        $post->post_content = PeepSoTemplate::exec_template('photos', 'activity-content', array('photos' => $photos, 'max_photos' => $max_photos), TRUE);
                    }
                }
            }
        }

        return ($post);
    }

    /**
     * `peepso_activity_delete` callback
     * Deletes a photo when an activity is deleted
     * @param  array $activity The activity row
     * @return boolean
     */
    // TODO: move this to the model class
    public function delete_photo($activity)
    {
        if (self::MODULE_ID === intval($activity->act_module_id)) {
            $photo = $this->get_photos_model()->get_photo($activity->act_external_id);
            $this->get_photos_model()->delete_photo($photo);

            $total_photos_album = $this->get_photos_model()->count_post_photos($photo->pho_post_id, FALSE);

            // Delete the post when there are no more photos
            if (0 === intval($total_photos_album)) {
                $activities = PeepSoActivity::get_instance();
                $post = $activities->get_post($photo->pho_post_id);

                $photo_type = get_post_meta($photo->pho_post_id, self::POST_META_KEY_PHOTO_TYPE, true);
                // if post_types is album, just set to unpublish
                if($photo_type === self::POST_META_KEY_PHOTO_TYPE_ALBUM) {
                    // Update post
                    $my_post = array(
                        'ID'           => $photo->pho_post_id,
                        'post_status'  => 'pending'
                    );

                    // Update the post into the database
                    wp_update_post( $my_post );
                } else {
                    if ($post->have_posts()) {
                        $activities->delete_activity($post->post->act_id);
                    }
                }
            } else {
                $total_photos = get_post_meta($photo->pho_post_id, self::POST_META_KEY_PHOTO_COUNT, true);
                if ($total_photos == 1) {
                    $total_photos = $total_photos_album;
                } else {
                    $total_photos -= 1;
                }
                update_post_meta($photo->pho_post_id, self::POST_META_KEY_PHOTO_COUNT, $total_photos);
            }
        }
        else if(self::MODULE_ID === intval($activity->act_comment_module_id))
        {
            $this->get_photos_model()->delete_photo_comment($activity);
        }
    }

    /**
     * Adds the number of photos left in the post to the ajax response
     * @param  PeepSoAjaxResponse $resp     PeepSoAjaxResponse instance
     * @param  object             $activity The activity row
     */
    // TODO: move this to the model class
    public function before_ajax_delete(PeepSoAjaxResponse $resp, $activity)
    {
        if (self::MODULE_ID === intval($activity->act_module_id)) {
            $activities = PeepSoActivity::get_instance();

            $photo = $this->get_photos_model()->get_photo($activity->act_external_id);
            $total_photos = $this->get_photos_model()->count_post_photos($photo->pho_post_id, FALSE);
            $post = $activities->get_post($photo->pho_post_id)->post;

            $resp->set('photo_total', $total_photos - 1);
            $resp->set('post_act_id', $post->act_id); // Set the act id of the photo post to remove from the stream
        }
    }

    /**
     * Change a Photo post's privacy setting and all photos posted under it.
     * @param  array $activity   The activity post record
     * @param  int $act_access The activity level to set
     */
    public function change_photos_privacy($activity, $act_access)
    {
        // fix "Trying to get property of non-object" errors
        if (is_object($activity) && self::MODULE_ID === intval($activity->act_module_id)) {
            $this->get_photos_model()->update_post_photos_privacy($activity->act_external_id, $act_access);
        }
    }

    /**
     * todo
     */
    public function notifications_activity_type($activity_type, $post_id, $act_id = NULL) {

        # $activity_type = array(
        #   'text' => __('post', 'peepso-core'),
        #   'type' => 'post'
        # );

        /**
         * Please note that we mus define email template for each
         * 1. like_{type}
         * 2. user_comment_{type}
         * 3. share_{type}
         */

        if(is_array($activity_type)) {
            $photo_type = get_post_meta($post_id, self::POST_META_KEY_PHOTO_TYPE, true);

            $type = '';
            if(in_array($activity_type['type'], array('user_comment', 'share'))) {
                $type = $activity_type['type'] . '_';
            }

            // if type is photo album, show latest photos as first ones.
            if( $photo_type === self::POST_META_KEY_PHOTO_TYPE_ALBUM ) {
                $activity_type = array(
                    'text' => __('album', 'picso'),
                    'type' => $type . 'album'
                );

                if($act_id !== NULL) {

                    $activity = PeepSoActivity::get_instance();
                    $act_data = $activity->get_activity($act_id);

                    if(isset($act_data->act_external_id) && (intval($act_data->act_external_id) !== intval($post_id)))
                    {
                        $activity_type = array(
                            'text' => __('photo', 'picso'),
                            'type' => $type . 'photo'
                        );
                    }

                }
            } else if( $photo_type === self::POST_META_KEY_PHOTO_TYPE_AVATAR ) {
                $activity_type = array(
                        'text' => __('avatar', 'picso'),
                        'type' => $type . 'avatar'
                    );
            } else if( $photo_type === self::POST_META_KEY_PHOTO_TYPE_COVER ) {
                $activity_type = array(
                        'text' => __('cover photo', 'picso'),
                        'type' => $type . 'cover'
                    );
            } else {
                $photo_model = new PeepSoPhotosModel();
                $photo = $photo_model->get_post_photos($post_id);
                $total_photos = count($photo);

                if ($total_photos > 0) {
                    $activity_type = array(
                        'text' => _n('photo', 'photos', $total_photos, 'picso'),
                        'type' => $type . 'photo'
                    );
                }
            }
        }

        return ($activity_type);
    }

    /**
     * Append profile alerts definition for peepsovideos
     */
    public function profile_alerts($alerts)
    {
        $items = array();

        // @TODO CLEANUP

//        $items_photos = array(array(
//                    'label' => __('Someone liked my Photos', 'picso'),
//                    'setting' => 'like_photo',
//                    'loading' => TRUE,
//                ),
//                array(
//                    'label' => __('Someone commented on my Photos', 'picso'),
//                    'setting' => 'user_comment_photo',
//                    'loading' => TRUE,
//                ));
//
//        $items_avatar = array(array(
//                    'label' => __('Someone liked my Avatar', 'picso'),
//                    'setting' => 'like_avatar',
//                    'loading' => TRUE,
//                ),
//                array(
//                    'label' => __('Someone commented on my Avatar', 'picso'),
//                    'setting' => 'user_comment_avatar',
//                    'loading' => TRUE,
//                ));
//
//        $items_cover = array(array(
//                    'label' => __('Someone liked my Cover Photo', 'picso'),
//                    'setting' => 'like_cover',
//                    'loading' => TRUE,
//                ),
//                array(
//                    'label' => __('Someone commented on my Cover Photo', 'picso'),
//                    'setting' => 'user_comment_cover',
//                    'loading' => TRUE,
//                ));
//
//        $items_album = array(array(
//                    'label' => __('Someone liked my Album', 'picso'),
//                    'setting' => 'like_album',
//                    'loading' => TRUE,
//                ),
//                array(
//                    'label' => __('Someone commented on my Album', 'picso'),
//                    'setting' => 'user_comment_album',
//                    'loading' => TRUE,
//                ));
//
//        if (PeepSo::get_option('site_repost_enable', TRUE)) {
//            array_push($items_photos, array(
//                'label' => __('Someone shared my Photos', 'picso'),
//                'setting' => 'share_photo',
//                'loading' => TRUE,
//            ));
//
//            array_push($items_avatar, array(
//                'label' => __('Someone shared my Avatar', 'picso'),
//                'setting' => 'share_avatar',
//                'loading' => TRUE,
//            ));
//
//            array_push($items_cover, array(
//                'label' => __('Someone shared my Cover Photo', 'picso'),
//                'setting' => 'share_cover',
//                'loading' => TRUE,
//            ));
//
//            array_push($items_album, array(
//                'label' => __('Someone shared my Album', 'picso'),
//                'setting' => 'share_album',
//                'loading' => TRUE,
//            ));
//        }

//        $items = array_merge($items_photos, $items_avatar, $items_cover, $items_album);

        if(!count($items)) {
            return $alerts;
        }

        $alerts['photos'] = array(
                'title' => __('Photos', 'picso'),
                'items' => $items,
        );
        // NOTE: when adding new items here, also add settings to /install/activate.php site_alerts_ sections
        return ($alerts);
    }

    /**
     * Callback for the core 'peepso_widgets' filter; appends our widgets to the list
     * @param $widgets
     * @return array
     */
    public function register_widgets($widgets)
    {
        // register widgets
        // @TODO that's too hacky - why doesn't autoload work?
        foreach (scandir($widget_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'widgets' . DIRECTORY_SEPARATOR) as $widget) {
            if(strlen($widget)>=5) {
                require_once($widget_dir . $widget);
            }
        }
        return array_merge($widgets, $this->widgets);
    }

	public function filter_peepso_navigation_profile($links)
	{
		$links['photos'] = array(
			'href' => 'photos',
			'label'=> __('Photos', 'picso'),
			'icon' => 'gcis gci-camera'
		);

		return $links;
	}

	public function filter_group_segment_menu_links($links)
	{
		$links[20][] = array(
			'href' => 'photos',
			'title'=> __('Photos', 'picso'),
            'icon' => 'gcis gci-camera',
		);

		ksort($links);
		return $links;
	}


    private function peepso_album_enqueue_scripts()
    {
        wp_enqueue_style('peepso-fileupload');

        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('peepso-fileupload');
        wp_enqueue_script('peepso-load-image');
        wp_enqueue_script('peepso-photos');

        wp_register_script('peepso-photos-dropzone',
            PeepSo::get_asset('js/dropzone.min.js', __FILE__),
            array('peepso-npm', 'jquery-ui-sortable'),
            self::PLUGIN_VERSION, TRUE);

        wp_register_script('peepso-photos-albumdialog',
            PeepSo::get_asset('js/albumdialog.min.js', __FILE__),
            array('peepso-npm', 'peepso-photos-dropzone', 'peepso-window'), PeepSo::PLUGIN_VERSION, TRUE);

        wp_register_script('peepso-photos-albumuploaddialog',
            PeepSo::get_asset('js/albumuploaddialog.min.js', __FILE__),
            array('peepso-npm', 'peepso-photos-dropzone', 'peepso-window'), PeepSo::PLUGIN_VERSION, TRUE);

        wp_register_script('peepso-photos-album',
            PeepSo::get_asset('js/album.min.js', __FILE__),
            array('peepso-npm'), PeepSo::PLUGIN_VERSION, TRUE);

        wp_localize_script('peepso-photos-dropzone', 'psdata_photos_dropzone', array(
            'template' => PeepSoTemplate::exec_template('photos', 'photo-dropzone', array(), TRUE),
            'template_preview' => PeepSoTemplate::exec_template('photos', 'photo-dropzone-preview', array(), TRUE),
            'text_upload_failed_notice' => __('Upload Failed! Please try again.', 'picso'),
        ));

        $privacy = PeepSoPrivacy::get_instance();
        $access_settings = $privacy->get_access_settings();

        $album_url = PeepSoSharePhotos::get_url(get_current_user_id()) . '/album';
        $album_url = apply_filters('peepso_photos_album_url', $album_url);

        wp_localize_script('peepso-photos-albumdialog', 'psdata_photos_albumdialog', array(
            'template' => PeepSoTemplate::exec_template('photos', 'photo-album-dialog', array('access_settings' => $access_settings), TRUE),
            'album_url' => $album_url,
        ));

        wp_enqueue_script('peepso-photos-albumdialog');

        wp_localize_script('peepso-photos-albumuploaddialog', 'psdata_photos_albumuploaddialog', array(
            'template' => PeepSoTemplate::exec_template('photos', 'photo-album-upload', array(), TRUE),
        ));

        wp_enqueue_script('peepso-photos-albumuploaddialog');

        wp_localize_script('peepso-photos-album', 'psdata_photos_album', array(
            'nonce_set_album_name' => wp_create_nonce('set-album-name'),
            'nonce_set_album_description' => wp_create_nonce('set-album-description'),
            'nonce_set_album_access' => wp_create_nonce('set-album-access'),
        ));

        wp_enqueue_script('peepso-photos-album');
    }

    public function peepso_profile_segment_photos($url_segments)
    {

        $this->peepso_album_enqueue_scripts();

        $pro = PeepSoProfileShortcode::get_instance();
        $this->view_user_id = PeepSoUrlSegments::get_view_id($pro->get_view_user_id());

        if ('photos' == $url_segments->get(2) && 'album' != $url_segments->get(3)) {
            // migrate from activate function,
            // setup album for viewed user
            $this->setup_album($pro->get_view_user_id());
        }

        // Grab and run the shortcode
        $sc = PeepSoPhotosShortcode::get_instance();
        echo $sc->profile_segment($this->view_user_id, $url_segments);
    }

    public function peepso_group_segment_photos($args, $url_segments)
    {
        $this->peepso_album_enqueue_scripts();

        if(!$url_segments instanceof PeepSoUrlSegments) {
            $url_segments = PeepSoUrlSegments::get_instance();
        }

        if ('photos' == $url_segments->get(2) && 'album' != $url_segments->get(3)) {
            // migrate from activate function,
            // setup album for viewed groups
            do_action('peepso_photos_setup_groups_album' , $args['group']->id);
        }

        $sc = PeepSoPhotosShortcode::get_instance();
        echo $sc->group_segment($args, $url_segments);
    }

    public static function get_url($view_id = 0, $page='latest')
    {
        $user = PeepSoUser::get_instance($view_id);

        switch($page) {
            case 'album':
                return PeepSoSharePhotos::get_url($view_id) . '/album';
                break;
            default:
                return $user->get_profileurl().'photos';
        }
    }

    public static function get_group_url($view_id = 0, $page='latest')
    {
        $group_user = new PeepSoGroupUser($view_id);
        $group = new PeepSoGroup($view_id);

        switch($page) {
            case 'album':
                return PeepSoSharePhotos::get_group_url($view_id) . '/album';
                break;
            default:
                return $group->get_url().'photos';
        }
    }

    public function peepso_rewrite_profile_pages($pages)
    {
        return array_merge($pages, array('photos'));
    }

    /**
     * Returns the photo to display or NULL if the activity is not of this module
     * @param  int $act_external_id  Referenced entity id
     * @param  array $activity  The activity data
     * @return int $post_id
     */
    public function activity_post_id($act_external_id, $activity)
    {
        // fix "Trying to get property of non-object" errors
        if (is_object($activity) && self::MODULE_ID === intval($activity->act_module_id)) {
            $photo = $this->get_photos_model()->get_photo($activity->act_external_id);
            if (NULL !== $photo) {
                $act_external_id = $photo->ID;
            }
        }

        return ($act_external_id);
    }

    /**
     * Change act_id on repost button act_id to follow parent's act_id.
     * @param array $options The default options per post
     * @return  array
     */
    public function add_post_actions($options)
    {
        $post = $options['post'];
        if (self::MODULE_ID === intval($post->module_id)) {
            if (isset($options['acts']['repost'])) {
                $options['acts']['repost']['click'] = 'return activity.action_repost(' . $post->post_parent . ');';
            }

            $user_id = get_current_user_id();

            if (PeepSo::check_permissions(intval($post->author_id), PeepSo::PERM_POST_DELETE, $user_id)) {
                // get photo album_id
                $photo_model = new PeepSoPhotosModel();
                $photo_album_model = new PeepSoPhotosAlbumModel();
                if($post->_total_objects > 1) {
                    $photo = $photo_model->get_photo($post->object_index_key);
                } else {
                    $photo = $photo_model->get_post_photos($post->object_index_key);
                    if(empty($photo)) {
                        $photo = $photo_model->get_photo($post->object_index_key);
                    } else {
                        $photo = $photo[0];
                    }
                }

                $delete_script = 'return activity.delete_activity(' . $photo->act_id . ');';

                // delete post
                $delete_post = TRUE;
                $total_object = $photo_model->count_post_photos($photo->pho_post_id);

                if(isset($photo->pho_album_id)) {

                    $user_id = apply_filters('peepso_photos_filter_owner_album', $photo->pho_owner_id);

                    $album = $photo_album_model->get_album($photo->pho_album_id, $user_id);
                    if(isset($album->pho_system_album) && (0 === intval($album->pho_system_album))) {
                        $delete_post = FALSE;
                        if($total_object == 1) {
                            $delete_script = 'peepso.photos.show_dialog_delete_album('.$album->pho_owner_id.','.$album->pho_album_id.'); return false;';
                        }
                    }
                }

                if ($total_object == 1 && ($delete_post)) {
                    if(isset($album) && $album->pho_system_album == 3) {
                        $delete_script = 'return activity.action_delete(' . $photo->pho_post_id . ');';
                    } else {
                        $delete_script = 'return activity.action_delete(' . $post->ID . ');';
                    }
                }

                $options['acts']['delete']['click'] = $delete_script;
            }
        }

        return ($options);
    }

    /**
     * modify onclick handler delete post for album type post
     * @param array $options
     * @return array $options
     */
    public function post_filters($options) {
        $post = $options['post'];
        if (self::MODULE_ID === intval($post->module_id)) {
            return $this->add_post_actions($options);
        }

        $photo_type = get_post_meta($post->ID, self::POST_META_KEY_PHOTO_TYPE, true);
		$user_id = get_current_user_id();

        if($photo_type === self::POST_META_KEY_PHOTO_TYPE_ALBUM &&
			(PeepSo::check_permissions(intval($post->author_id), PeepSo::PERM_POST_DELETE, $user_id) ||
            PeepSo::check_permissions(intval($post->act_owner_id), PeepSo::PERM_POST_DELETE, $user_id))) {

            $options['acts']['delete']['icon'] = 'gcis gci-trash';
            $options['acts']['delete']['click'] = 'return peepso.photos.delete_stream_album(' . $post->ID . ',' . $post->act_id . ');';
            $options['acts']['delete']['label'] = __('Delete Album', 'picso');
        }

        return $options;
    }

	public function peepso_filter_opengraph($tags, $activity)
	{
		$photo_model = new PeepSoPhotosModel();
		$photos = $photo_model->get_post_photos($activity->ID);

		$count = count($photos);
		if ($count > 0)
		{
			$tags['image'] = array();
			for ($i = 0; $i < $count; $i++)
			{
				if (isset($photos[$i]->pho_file_name)) {
					$tags['image'][] = $photos[$i]->location;
				}
			}
		}

		return $tags;
	}

    /**
     * Function called after cover changed
     * @param user_id
     * @param dest_file
     */
    public function after_change_cover($user_id, $dest_file)
    {
        // migrate from activate function,
        // setup album before uploading cover
        $this->setup_album($user_id);

        #$content = __('change cover','picso');
        $content = '';
        $extra = array(
            'module_id' => self::MODULE_ID,
            'act_access' => PeepSo::ACCESS_PUBLIC,
            'post_date_gmt' => date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ))
        );

        $this->file_cover = $dest_file;
        add_filter('peepso_photos_cover_original', array(&$this, 'set_file_cover'));
        add_action('peepso_activity_after_add_post', array(&$this, 'after_add_post_cover'), 10, 2);
        add_filter('peepso_pre_write_content', array(&$this, 'set_post_status'), 10, 1);

        $peepso_activity = PeepSoActivity::get_instance();
        $post_id = $peepso_activity->add_post($user_id, $user_id, $content, $extra);
        add_post_meta($post_id, self::POST_META_KEY_PHOTO_TYPE, self::POST_META_KEY_PHOTO_TYPE_COVER, true);
    }

    /**
     * Set file cover
     */
    function set_file_cover($file)
    {
        if(!empty($this->file_cover))
        {
            $file = $this->file_cover;
        }
        return ($file);
    }

    /**
     * This function manipulates the image/photo uploaded including uploading to Amazon S3
     * @param int $post_id The post ID
     * @param int $act_id The activity ID
     */
    public function after_add_post_cover($post_id, $act_id)
    {
        $file = '';
        $file = apply_filters('peepso_photos_cover_original',$file);
        $album = apply_filters('peepso_photos_profile_covers_album', self::ALBUM_COVERS);
        if(!empty($file)) {
            $this->get_photos_model()->save_images_profile($file, $post_id, $act_id,$album);
        }
    }

    /**
     * Function called after avatar changed
     * @param user_id
     * @param dest_thumb
     * @param dest_full
     * @param dest_orig
     */
    public function after_change_avatar($user_id, $dest_thumb, $dest_full, $dest_orig)
    {
        // migrate from activate function,
        // setup album before uploading avatar
        $this->setup_album($user_id);

        #$content = __('change avatar','picso');
        $content = '';
        $extra = array(
            'module_id' => self::MODULE_ID,
            'act_access' => PeepSo::ACCESS_PUBLIC,
            'post_date_gmt' => date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ))
        );

        $this->file_avatar = $dest_orig;
        add_filter('peepso_photos_avatar_original', array(&$this, 'set_file_avatar'));
        add_action('peepso_activity_after_add_post', array(&$this, 'after_add_post_avatar'), 10, 2);
        add_filter('peepso_pre_write_content', array(&$this, 'set_post_status'), 10, 1);

        $peepso_activity = PeepSoActivity::get_instance();
        $post_id = $peepso_activity->add_post($user_id, $user_id, $content, $extra);
        add_post_meta($post_id, self::POST_META_KEY_PHOTO_TYPE, self::POST_META_KEY_PHOTO_TYPE_AVATAR, true);
    }

    /**
     * Set file avatar
     */
    function set_file_avatar($file)
    {
        if(!empty($this->file_avatar))
        {
            $file = $this->file_avatar;
        }
        return ($file);
    }

    /**
     * This function manipulates the image/photo uploaded including uploading to Amazon S3
     * @param int $post_id The post ID
     * @param int $act_id The activity ID
     */
    public function after_add_post_avatar($post_id, $act_id)
    {
        $file   = '';
        $file   = apply_filters('peepso_photos_avatar_original',$file);
        $album  = apply_filters('peepso_photos_profile_avatars_album', self::ALBUM_AVATARS);
        if(!empty($file)) {
            $this->get_photos_model()->save_images_profile($file, $post_id, $act_id,$album);
        }
    }

    /**
     * This function will save the postmeta for photo comments
     * @param int $post_id The post ID
     * @param int $act_id The activity ID
     */
    public function after_add_comment($post_id, $act_id, $did_notify, $did_email)
    {
        $input = new PeepSoInput();
        $photo = $input->value('photo', false, false); // SQL safe

        if(FALSE !== $photo)
        {
            $this->get_photos_model()->save_images_comment($photo, $post_id, $act_id);
        }
    }

    /**
     * This function will save/update the postmeta for photo comments
     * @param object $post The post
     */
    public function after_save_comment($post_id, $activity)
    {
        $input = new PeepSoInput();
        $photo = $input->value('photo', false, false); // SQL safe

        // delete photo
        if(FALSE === $photo) {
            $this->get_photos_model()->delete_photo_comment($activity);
            return;
        }

        $photo_meta = get_post_meta($post_id, PeepSoSharePhotos::POST_META_KEY_PHOTO_COMMENTS, true);

        if(!empty($photo_meta)) {
            if(is_string($photo_meta)) {
                $photo_meta = json_decode($photo_meta);
            }
            if(isset($photo_meta->filesystem_name)) {
                if($photo === $photo_meta->filesystem_name) {
                    return; // same photo
                }
                // delete previous photo
                $this->get_photos_model()->delete_photo_comment($activity);
            }
        }

        $this->get_photos_model()->save_images_comment($photo, $post_id, $activity->act_id);
    }

    /**
     * This function inserts the photo UI on the comments box
     * @param array $interactions is the formated html code that get inserted in the postbox
     * @param int $post_id Post content ID
     */
    public function commentsbox_interactions($interactions, $post_id = FALSE)
    {
        wp_enqueue_style('peepso-fileupload');

        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('peepso-fileupload');
        wp_enqueue_script('peepso-load-image');
        wp_enqueue_script('peepso-photos');

        if(!apply_filters('peepso_permissions_photos_upload', TRUE)) {
            return $interactions;
        }

        $interactions['photos'] = array(
            'icon' => 'gcis gci-camera',
            'id' => 'comment-photo-post',
            'class' => 'ps-comments__input-action ps-js-action-photo',
            'click' => 'peepso.photos.comment_attach_photo(this); return false;',
            'label' => '',
            'title' => __('Upload photos', 'picso')
        );

        return ($interactions);
    }

    /**
     * This function inserts the photo UI on the comments box
     * @param array $interactions is the formated html code that get inserted in the postbox
     * @param int $post_id Post content ID
     */
    public function commentsbox_addons($addons, $post_id = FALSE)
    {
        wp_enqueue_style('peepso-fileupload');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('peepso-fileupload');
        wp_enqueue_script('peepso-load-image');
        wp_enqueue_script('peepso-photos');

        $photo = array();

        if ($post_id) {
            $comment = get_post($post_id);
            $photo_comment = $this->get_photos_model()->get_photo_comments($comment);
            if (!empty($photo_comment)) {
                $photo['id'] = $photo_comment['filesystem_name'];
                $photo['thumb'] = $photo_comment['thumbs']['s_s'];
            }
        }

        $html = PeepSoTemplate::exec_template('photos', 'photo-comment-addon', $photo, TRUE);
        array_push($addons, $html);
        return ($addons);
    }

    /**
     * Displays the embeded media on the comment.
     * - peepso_activity_comment_attachment
     * @param WP_Post The current post object
     */
    public function comments_attach_photo($stream_comment = NULL)
    {
        $peepso_photo_comments = $this->get_photos_model()->get_photo_comments($stream_comment);
        if (empty($peepso_photo_comments))
        {
            return;
        }

        $photo_comments = array();
        $photo = new stdClass();

        $photo->photo_thumbs = $peepso_photo_comments['thumbs'];
        $photo->photo_original = $peepso_photo_comments['original'];
        $photo->act_id = $stream_comment->act_id;
        $photo->title = $stream_comment->post_title;

        $photo_comments['photo'] = $photo;

        PeepSoTemplate::exec_template('photos', 'comments-content', $photo_comments);
    }

    /**
     * Create default album for user after user register
     * @param object PeepSoUser
     */
    public function create_album($user)
    {
        global $wpdb;

        $album_model = new PeepSoPhotosAlbumModel();

        foreach($this->photo_system_album as $album) {
            $album_id = $album_model->get_photo_album_id($user->get_id(), $album['is_system']);
            if(FALSE===$album_id) {
                $data = array(
                        'pho_owner_id' => $user->get_id(),
                        'pho_album_acc' => $album['albumname_acc'],
                        'pho_album_name' => $album['albumname'],
                        'pho_system_album' => $album['is_system'], // flag for album, 1 = system album, 2 = user created album
                    );
                $wpdb->insert($wpdb->prefix . PeepSoPhotosAlbumModel::TABLE , $data);
            }
        }
    }

    /**
     * This function manipulates the image/photo uploaded including uploading to Amazon S3
     * @param int $post_id The post ID
     * @param int $act_id The activity ID
     */
    public function after_add_post_album($post_id, $act_id)
    {
        $input = new PeepSoInput();
        $files = $input->value('photo', array(), false); // SQL safe

        // SQL safe
        if (count($files) > 0 && 'album' === $input->value('type','',FALSE)) {
            $album_model = new PeepSoPhotosAlbumModel();
            // create album
            $name = $input->value('name', '', FALSE); // SQL safe
            $privacy = $input->int('privacy');
            $description = $input->value('description', '', FALSE); // SQL safe
            $module_id = $input->int('module_id', 0);

            // module, extends for groups / page / events
            $module_id = $input->int('module_id', 0);

            $owner_id = get_current_user_id();
            if($module_id !== 0) {
                $owner_id = apply_filters('peepso_photos_filter_owner_album', $owner_id);
            }

            $album_model->create_album($owner_id, $name, $privacy, $description, $post_id, $module_id);

            // get album_id
            $album_id = $album_model->get_photo_album_id($owner_id, self::ALBUM_CUSTOM, $post_id, $module_id);

            $this->get_photos_model()->save_images($files, $post_id, $act_id, $album_id);
        }
    }

    /**
     * Setup album for viewed user_id if album for user not created yet
     * @param user_id Viewed photo user
     */
    private function setup_album($user_id=0)
    {
        if($user_id !== 0)
        {
            global $wpdb;

            $user = PeepSoUser::get_instance($user_id);
            $dir = $user->get_image_dir();

            $album_model = new PeepSoPhotosAlbumModel();
            foreach($this->photo_system_album as $album)
            {
                $album_id = $album_model->get_photo_album_id($user_id, $album['is_system']);
                $new_album_id = $album_id;
                // if album not found, insert the album
                if(FALSE ===$album_id) {
                    $data = array(
                                'pho_owner_id' => $user_id,
                                'pho_album_acc' => $album['albumname_acc'],
                                'pho_album_name' => $album['albumname'],
                                'pho_system_album' => $album['is_system'], // flag for album, 1 = system album, 2 = user created album
                            );
                    $wpdb->insert($wpdb->prefix . PeepSoPhotosAlbumModel::TABLE , $data);

                    $new_album_id = $wpdb->insert_id;

                    // save avatars when upgrading
                    // if profile avatars album not created yet
                    if($album['is_system'] == self::ALBUM_AVATARS) {

                        // if user using gravatar just continue
                        if (get_user_meta($user_id, 'peepso_use_gravatar', TRUE) == 1 && PeepSo::get_option('avatars_gravatar_enable') == 1) {
                            continue;
                        }

                        $content = '';
                        $extra = array(
                            'module_id' => self::MODULE_ID,
                            'act_access' => PeepSo::ACCESS_PUBLIC,
                        );

                        $dest_orig = $dir . 'avatar-orig.jpg';

                        // check if file exist and post update avatar change option is true
                        if (file_exists($dest_orig)) {

                            $this->file_avatar = $dest_orig;
                            add_filter('peepso_photos_avatar_original', array(&$this, 'set_file_avatar'),10,1);
                            add_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'), 10, 1);
                            add_filter('peepso_pre_write_content', array(&$this, 'set_post_date'), 10, 1);
                            add_filter('peepso_pre_write_content', array(&$this, 'set_post_status'), 20, 1);
                            add_action('peepso_activity_after_add_post', array(&$this, 'after_add_post_avatar'), 10, 2);

                            $peepso_activity = PeepSoActivity::get_instance();
                            $post_id = $peepso_activity->add_post($user_id, $user_id, $content, $extra);
                            add_post_meta($post_id, self::POST_META_KEY_PHOTO_TYPE, self::POST_META_KEY_PHOTO_TYPE_AVATAR, true);

                            remove_filter('peepso_photos_avatar_original', array(&$this, 'set_file_avatar'));
                            remove_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'));
                            remove_filter('peepso_pre_write_content', array(&$this, 'set_post_date'));
                            remove_filter('peepso_pre_write_content', array(&$this, 'set_post_status'));
                            remove_action('peepso_activity_after_add_post', array(&$this, 'after_add_post_avatar'));
                        }
                    }

                    // save covers when upgrading
                    // if profile covers album not created yet
                    if($album['is_system'] == self::ALBUM_COVERS) {
                        #$content = __('change cover','picso');
                        $content = '';
                        $extra = array(
                            'module_id' => self::MODULE_ID,
                            'act_access' => PeepSo::ACCESS_PUBLIC,
                        );

                        $dest_file = $dir . 'cover.jpg';

                        if(file_exists($dest_file)) {
                            $this->file_cover = $dest_file;
                            add_filter('peepso_photos_cover_original', array(&$this, 'set_file_cover'));
                            add_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'), 10, 1);
                            add_filter('peepso_pre_write_content', array(&$this, 'set_post_date'), 10, 1);
                            add_filter('peepso_pre_write_content', array(&$this, 'set_post_status'), 20, 1);
                            add_action('peepso_activity_after_add_post', array(&$this, 'after_add_post_cover'), 10, 2);

                            $peepso_activity = PeepSoActivity::get_instance();
                            $post_id = $peepso_activity->add_post($user_id, $user_id, $content, $extra);
                            add_post_meta($post_id, self::POST_META_KEY_PHOTO_TYPE, self::POST_META_KEY_PHOTO_TYPE_COVER, true);

                            remove_filter('peepso_photos_cover_original', array(&$this, 'set_file_cover'));
                            remove_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'));
                            remove_filter('peepso_pre_write_content', array(&$this, 'set_post_date'));
                            remove_filter('peepso_pre_write_content', array(&$this, 'set_post_status'));
                            remove_action('peepso_activity_after_add_post', array(&$this, 'after_add_post_cover'));
                        }
                    }
                }

                if($album['is_system'] == self::ALBUM_STREAM) {
                    $wpdb->update(
                        $wpdb->prefix . PeepSoPhotosModel::TABLE,
                        array(
                            'pho_album_id' => $new_album_id,    // int (number)
                        ),
                        array( 'pho_owner_id' => $user_id, 'pho_album_id' => 0 ), // where photo_album_id still undefined (0)
                        array( '%d' ),
                        array( '%d','%d' )
                    );
                }
            }
        }
    }

    /**
     * Set post date for change avatar/cover activities
     * @param array $aPostData
     * @return array $aPostData
     */
    public function set_post_date($aPostData) {

        if(!empty($this->file_avatar))
        {
            $filename = $this->file_avatar;
        }

        if(!empty($this->file_cover))
        {
            $filename = $this->file_cover;
        }

        if(is_array($aPostData)) {
            $post_date = date( 'Y-m-d H:i:s', current_time( 'timestamp'));
            $post_date_gmt = date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ));
            $aPostData['post_date'] = $post_date;
            $aPostData['post_date_gmt'] = $post_date_gmt;
        }

        return $aPostData;
    }

    /**
     * Set post status for change avatar/cover activities
     * @param array $aPostData
     * @return array $aPostData
     */
    public function set_post_status($aPostData) {
        $user_id = $aPostData['post_author'];

        if (0 === $user_id) {
            $aPostData['post_status'] = 'pending';
        }

        if((!empty($this->file_avatar)) && is_array($aPostData) && (0 === intval(PeepSo::get_option('photos_enable_post_updates_avatar',1)))) {
            $aPostData['post_status'] = 'pending';
        }

        if((!empty($this->file_cover)) && is_array($aPostData) && (0 === intval(PeepSo::get_option('photos_enable_post_updates_cover',1)))) {
            $aPostData['post_status'] = 'pending';
        }

        return $aPostData;
    }

    /**
     * todo
     */
    public function set_activity_stream_config($arr_config) {
        $section = 'photos_' ;

        $enable_post_updates_avatar = array(
            'name' => $section . 'enable_post_updates_avatar',
            'label' => __('Enable post updates upon user avatar change', 'picso'),
            'descript' => __('Post updates on Activity Stream when user changes profile avatar.', 'picso'),
            'type' => 'yesno_switch',
            'default' => 1,
        );

        $enable_post_updates_cover = array(
            'name' => $section . 'enable_post_updates_cover',
            'label' => __('Enable post updates upon user cover change', 'picso'),
            'descript' => __('Post updates on Activity Stream when user changes profile cover.', 'picso'),
            'type' => 'yesno_switch',
            'default' => 1,
        );

        return (array($enable_post_updates_avatar, $enable_post_updates_cover));
    }

    /**
     * todo
     */
    public function set_groups_general_config($arr_config) {
        $section = 'photos_groups_' ;

        $enable_post_updates_avatar = array(
            'name' => $section . 'enable_post_updates_group_avatar',
            'label' => __('Enable post updates upon groups avatar change', 'picso'),
            'descript' => __('Post updates on Activity Stream when user changes groups avatar.', 'picso'),
            'type' => 'yesno_switch',
            'default' => 1,
        );

        $enable_post_updates_cover = array(
            'name' => $section . 'enable_post_updates_group_cover',
            'label' => __('Enable post updates upon groups cover change', 'picso'),
            'descript' => __('Post updates on Activity Stream when user changes groups cover.', 'picso'),
            'type' => 'yesno_switch',
            'default' => 1,
        );

        return (array($enable_post_updates_avatar, $enable_post_updates_cover));
    }

	/**
     * check for gif file
     */
	public function is_gif_file($location, $photo_thumbs = NULL) {
		$gif_file_location = str_replace(array('.jpg', '.png'), '.gif', $location);
		$gif_file_location = str_replace(array('_l', '_m', '_m_s', '_s_s', 'thumbs/'), '', $gif_file_location);
		$gif_file_location = str_replace(PeepSo::get_peepso_uri(), PeepSo::get_peepso_dir(), $gif_file_location);

		if (is_file($gif_file_location)) {
            $gif_file_uri = str_replace(PeepSo::get_peepso_dir(), PeepSo::get_peepso_uri(), $gif_file_location);
            $this->gif_file_uri = $gif_file_uri;
			return TRUE;
		} else {
            #5453 animated GIF issue when using AWS S3
            $filename = explode('/', $location);
            $filename = end($filename);
            $filename = str_replace(array('_l', '_m', '_m_s', '_s_s', 'thumbs/'), '', $filename);

            $photo = $this->get_photos_model()->get_photo_by_filename($filename);
            if ($photo) {
                $thumbs = json_decode($photo->pho_thumbs, true);
                if (is_array($thumbs) && array_key_exists('gif', $thumbs)) {
                    $this->gif_file_uri = $thumbs['gif'];
                    return TRUE;
                }
            } else if (!empty($photo_thumbs) && is_array($photo_thumbs)) {
                if (array_key_exists('gif', $photo_thumbs)) {
                    $this->gif_file_uri = $photo_thumbs['gif'];
                    return TRUE;
                }
            }
			return FALSE;
		}
	}

    /**
     * Add additional photo upload addon to message input
     * @param array $options The additional addons to be attached to message input
     * @return  array
     */
    public function message_input_addons($addons)
    {
        if (!apply_filters('peepso_permissions_photos_upload', TRUE)) {
            return $addons;
        }
        $addons[] = PeepSoTemplate::exec_template('photos', 'message-input', NULL, TRUE);
        return ($addons);
    }

    /**
     * This function is a filter for peepso_check_permissions-post_edit
     * @param bool $allow the result of previous value
     * @param int $owner the ID of the owner
     * @param int $author the ID of the author
     * @param boolean $allow_logged_out the ID of author
     */
    public function check_permissions_edit_content($allow, $owner, $author, $allow_logged_out) {
        return $this->check_permissions_edit_and_delete_content($allow, 'edit_content');
    }

    /**
     * This function is a filter for peepso_check_permissions-post_delete
     * @param bool $allow the result of previous value
     * @param int $owner the ID of the owner
     * @param int $author the ID of the author
     * @param boolean $allow_logged_out the ID of author
     */
    public function check_permissions_delete_content($allow, $owner, $author, $allow_logged_out) {
        return $this->check_permissions_edit_and_delete_content($allow, 'manage_content');
    }

    /**
     * This function will check if current user is allowed to edit or delete content
     * @param bool $allow the result of previous value
     * @param string $permission Permission type
     */
    private function check_permissions_edit_and_delete_content($allow, $permission) {
        global $post;

        // avoid overriding the global post var
        $peepso_post = $post;
        $group_user = NULL;

        if (class_exists('PeepSoGroupUser')) {
            $peepso_input = new PeepSoInput();
			$group_id = $peepso_input->int('group_id');
			$album_id = $peepso_input->int('album_id');
            $object_id = $peepso_input->int('object_id');
            $post_id = $peepso_input->int('post_id');
			$type = $peepso_input->value('type', '', FALSE);

            if (empty($group_id)) {
                $album_model = new PeepSoPhotosAlbumModel();
                $photo_model = new PeepSoPhotosModel();

                if (isset($peepso_post) && $peepso_post->ID != -1) {
                    // if global post data is available
                    $group_id = get_post_meta($peepso_post->ID, 'peepso_group_id', true);
                } else if ($album_id > 0) {
                    // if album id is provided
                    $album = $album_model->get_album_by_id($album_id);
                    $group_id = $album->pho_owner_id;

                    $post = get_post($album->pho_post_id);
                } else if ($type == 'photo' && $object_id > 0) {
                    $photo = $photo_model->get_photo($object_id);
                    $album = $album_model->get_album_by_id($photo->pho_album_id);
                    $group_id = $album->pho_owner_id;

                    $post = get_post($album->pho_post_id);
                } else if ($post_id > 0) {
                    $album = $album_model->get_album_by_post($post_id);
                    $group_id = $album->pho_owner_id;

                    $post = get_post($post_id);
                }

                // always true if owner
                if (isset($post) && $post->post_author == get_current_user_id()) {
                    return TRUE;
                }
            }

            if ($group_id) {
                $group_user = new PeepSoGroupUser($group_id, get_current_user_id());
                return $group_user->can($permission);
            }
        }

        return $allow;
    }

    /**
     * Modify notification link for photo post.
     *
     * @since 3.1.0.0
     *
     * @param string $link
     * @param array  $data
     * @return string
     */
    public function modify_notification_link($link, $data) {
        $type = $data['not_type'];

        // Only modify link of certain notification types.
        $types = array('user_comment', 'stream_reply_comment', 'like_post', 'tag', 'tag_comment');
        if (!in_array($type, $types)) {
            return $link;
        }

        $activities = PeepSoActivity::get_instance();
        $act_id = (int) $data['not_act_id']; // This data is only available since version 3.1.0.0.

        // Get the correct activity.
        $activity = NULL;
        if ($act_id) {
            $activity = $activities->get_activity($act_id);
        } else if ($type === 'user_comment' || $type === 'stream_reply_comment' || $type === 'like_post') {
            $activity = $activities->get_activity_data($data['not_external_id'], $data['not_module_id']);
        }

        // Skip if activity object can't be found.
        if (! (is_object($activity) && (int) $activity->act_id) ) {
            return $link;
        }

        $is_comment = FALSE;
        $post = $activities->get_activity_post($activity->act_id);

        // Get the root object's activity and post.
        while (is_object($activity) && (int) $activity->act_comment_object_id) {
            $is_comment = TRUE;
            $activity = $activities->get_activity_data($post->act_comment_object_id, $post->act_comment_module_id);
            $post = $activities->get_activity_post($activity->act_id);
        }

        // Only modify photo post.
        $pho_id = NULL;
        if (is_object($activity) && $activity->act_module_id == self::MODULE_ID) {
            $photos = $this->get_photos_model()->get_post_photos($post->ID);
            $photos_count = count($photos);
            if (1 === $photos_count) {
                $pho_id = $photos[0]->pho_id;
            } else if (1 < $photos_count) {
                if ($activity->act_external_id != $post->ID) {
                    $pho_id = $activity->act_external_id;
                } else if (!$is_comment && $act_id) {
                    $activity = $activities->get_activity($act_id);
                    if ($activity->act_external_id != $post->ID) {
                        $pho_id = $activity->act_external_id;
                    }
                }
            }
        }

        // Attach photo ID to the link if available.
        if ($pho_id) {
            $link .= (strpos($link, '#') ? '&' : '#') . 'photo=' . $pho_id;
        }

        return $link;
    }
}

PeepSoSharePhotos::get_instance();

// EOF
