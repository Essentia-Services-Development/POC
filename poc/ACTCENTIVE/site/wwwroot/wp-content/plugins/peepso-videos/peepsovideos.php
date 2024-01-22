<?php
/**
 * Plugin Name: PeepSo Core: Audio & Video
 * Plugin URI: https://peepso.com
 * Description: Upload audio and video files. Link and embed audio and video from supported providers
 * Author: PeepSo
 * Author URI: https://peepso.com
 * Version: 6.2.7.0
 * Copyright: (c) 2015 PeepSo, Inc. All Rights Reserved.
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: vidso
 * Domain Path: /language
 *
 * We are Open Source. You can redistribute and/or modify this software under the terms of the GNU General Public License (version 2 or later)
 * as published by the Free Software Foundation. See the GNU General Public License or the LICENSE file for more details.
 * This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY.
 */

class PeepSoVideos
{
    private static $_instance = NULL;
    private $_oembed_type = NULL;
    private $_oembed_title = NULL;
    private $_oembed_data;


    private $url_segments;
    private $view_user_id;

    const PLUGIN_VERSION = '6.2.7.0';
    const PLUGIN_RELEASE = ''; //ALPHA1, RC1 etc, '' for STABLE
    const PLUGIN_NAME = 'Core: Audio & Video';
    const PLUGIN_EDD = 245;
    const PLUGIN_SLUG = 'vidso';
    const MODULE_ID = 5;
    const TABLE = 'peepso_videos';
    const CRON_URL = 'peepso_convert_videos_event';
    const CRON_URL_UPLOAD_S3 = 'peepso_upload_videos_to_s3';
    const CRON_URL_CLEANUP = 'peepso_cleanup_videos_event';
    const CRON_VIDEOS_CONVERSION_EVENT = 'peepso_convert_videos_event';
    const CRON_VIDEOS_CLEANUP_EVENT = 'peepso_cleanup_videos_event';
    const PEEPSOCOM_LICENSES = 'http://tiny.cc/peepso-licenses';

    const POST_META_KEY_MEDIA_TYPE = 'peepso_media_type';
    const POST_META_KEY_VIDEO_ATTACHMENT_TYPE = 'peepso_video_attachment_type';
    const POST_META_KEY_VIDEO_CONVERSION_DONE = 'peepso_video_conversion_done';
    const POST_META_KEY_VIDEO_NO_CONVERSION = 'peepso_video_no_conversion';
    const ATTACHMENT_TYPE_VIDEO_TEMPORARY = 'peepso-video-temp';
    const ATTACHMENT_TYPE_VIDEO_ORIGINAL = 'peepso-video-original';
    const ATTACHMENT_TYPE_VIDEO = 'peepso-video';
    const ATTACHMENT_TYPE_AUDIO = 'peepso-audio';
    const ATTACHMENT_TYPE_POSTER = 'peepso-video-poster';
    const ATTACHMENT_TYPE_ANIMATED_GIF = 'peepso-video-animated-gif';
    const ATTACHMENT_TYPE_ANIMATED_WEBM = 'peepso-video-animated-webm';

    public $widgets = array(
        'PeepSoWidgetVideos',
        'PeepSoWidgetCommunityvideos',
    );

    private $_urls = array(); // temporary storage for parsed urls

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

    public static function video_enabled() {
        return (1==PeepSo::get_option('videos_video_master_switch',1));
    }

    public static function audio_enabled() {
        return (1==PeepSo::get_option('videos_audio_master_switch',0));
    }

    public static function profile_menu_label() {
        $label = __('Audio & Video', 'vidso');

        if(!PeepSoVideos::audio_enabled() && PeepSoVideos::video_enabled()) {
            $label = __('Videos', 'vidso');
        }

        if(!PeepSoVideos::video_enabled() && PeepSoVideos::audio_enabled()) {
            $label = __('Audio', 'vidso');
        }

        return $label;
    }

    public static function profile_menu_icon() {
        $icon = 'gcib gci-youtube';

        if(!PeepSoVideos::audio_enabled() && PeepSoVideos::video_enabled()) {
            $icon = 'gcis gci-video';
        }

        if(!PeepSoVideos::video_enabled() && PeepSoVideos::audio_enabled()) {
            $icon = 'gcis gci-music';
        }

        return $icon;
    }

    public static function profile_menu_slug() {
        $slug = 'media';

        if(!PeepSoVideos::audio_enabled() && PeepSoVideos::video_enabled()) {
            $slug = 'videos';
        }

        if(!PeepSoVideos::video_enabled() && PeepSoVideos::audio_enabled()) {
            $slug = 'audio';
        }

        return $slug;
    }
    /**
     * Initialize all variables, filters and actions
     */
    private function __construct()
    {
        /** VERSION INDEPENDENT hooks **/

        // Admin
        add_filter('peepso_license_config', array(&$this, 'add_license_info'), 60);
        if (is_admin()) {
            add_action('admin_init', array(&$this, 'peepso_check'));
        }

        // Compatibility
        add_filter('peepso_all_plugins', array($this, 'filter_all_plugins'),1);
        add_filter('peepso_free_bundle_should_brand', '__return_true');

        // Translations
        add_action('plugins_loaded', array(&$this, 'load_textdomain'));

        // Activation
        register_activation_hook(__FILE__, array(&$this, 'activate'));

        /** VERSION LOCKED hooks **/
        if(self::ready()) {
            if (!PeepSoLicense::check_license(self::PLUGIN_EDD, self::PLUGIN_SLUG)) {
                return;
            }

            if (is_admin()) {
                add_action('admin_init', array(&$this, 'allow_subscriber_to_uploads'));
                add_filter('ajax_query_attachments_args', function ($query) {
                    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], admin_url('upload.php') ) !== FALSE) {
                        $user_id = get_current_user_id();
                        // if not admin, just scramble the user_id to big value
                        if ($user_id && !current_user_can('administrator') && !current_user_can('editor')) {
                            if(!PeepSo::get_option('videos_subscriber_media_library_access', FALSE)) {
                                $query['author'] = $user_id;
                                $query['meta_query'] = array(
                                    'key' => PeepSoVideos::POST_META_KEY_VIDEO_ATTACHMENT_TYPE,
                                    'compare' => 'NOT EXISTS'
                                );
                            }
                        }
                    }
                    return $query;
                });

                $scheme = 'http';
                if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) {
                    $scheme = 'https';
                }
                if (admin_url('upload.php') == $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']
                    || (admin_url('upload.php') == $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] && isset($_GET['mode']) && $_GET['mode'] == 'list')) {
                    add_action('pre_get_posts', function($query) {
                        $user_id = get_current_user_id();
                        if ($user_id && !current_user_can('administrator') && !current_user_can('editor')) {
                            $query->set('author', $user_id);
                            $query->set('meta_query', array(
                                'key' => PeepSoVideos::POST_META_KEY_VIDEO_ATTACHMENT_TYPE,
                                'compare' => 'NOT EXISTS'
                            ));
                        }
                    });
                }

                add_filter('peepso_admin_config_tabs', array(&$this, 'admin_config_tabs'));
                add_filter('peepso_admin_queue_tabs', array(&$this, 'admin_queue_tabs'));
            }

            add_action('peepso_init', array(&$this, 'init'));
            add_filter('peepso_widgets', array(&$this, 'register_widgets'));

            add_action('init', function() {
                if (PeepSo::get_option('videos_upload_enable', 0) === 1) {
                    if (isset($_GET[$this::CRON_URL])) {
                        PeepSoVideosUpload::convert_videos();
                        die();
                    }

                    if (isset($_GET[$this::CRON_URL_UPLOAD_S3])) {
                        PeepSoVideosUpload::do_upload_to_s3();
                        die();
                    }

                    if (isset($_GET[$this::CRON_URL_CLEANUP])) {
                        PeepSoVideosUpload::cleanup_temp();
                        die();
                    }
                }
            });

            add_action('wp_ajax_nopriv_peepso_audio_album_info', array(&$this,'audio_album_info'));
            add_action('wp_ajax_peepso_audio_album_info', array(&$this,'audio_album_info'));
        }
    }

    public static function audio_album_info() {
        $PeepSoInput = new PeepSoInput();
        self::get_cover_art($PeepSoInput->value('artist', '', FALSE), $PeepSoInput->value('album', '', FALSE), TRUE); // SQL Safe
    }
    public static function get_cover_art($artist, $album, $json = FALSE) {

        $cover = PeepSo::get_asset('images/audio/default.png');
        $mayfly = 'peepso_cover_'.md5($artist.$album);
        $cache = FALSE;
        $default = TRUE;

        $result = array(
            'artist' => $artist,
            'album' => $album,
        );

        // Sanitize
        $artist = urlencode($artist);
        $album = urlencode($album);

        // Check cache
        if($cached = PeepSo3_Mayfly::get($mayfly)) {
            $cover = $cached;
            $cache = TRUE;
        } else {

            $url = 'http://ws.audioscrobbler.com/2.0/?method=album.getinfo&autocorrect=1&api_key=' . PeepSo::get_option('videos_audio_lastfm_api_key', '') . '&artist=' . $artist . '&album=' . $album . '&format=json';

            $response = wp_remote_get($url, array('timeout' => 1, 'sslverify' => TRUE));

            if (is_array($response) && isset($response['body']) && strlen($response['body'])) {
                $response = $response['body'];

                $response = @json_decode($response, TRUE);

                if (isset($response['album']) && isset($response['album']['image']) && is_array($response['album']['image']) && count($response['album']['image'])) {
                    $response = $response['album']['image'];
                    $response = array_reverse($response);
                    if (is_array($response)) {
                        $response = $response[0];
                        if (isset($response['#text']) && strlen($response['#text'])) {
                            if (filter_var($response['#text'], FILTER_VALIDATE_URL)) {
                                $cover = $response['#text'];
                                $default = FALSE;
	                            PeepSo3_Mayfly::set($mayfly, $cover, 30);
                            }
                        }
                    }
                }
            }
        }

        $result['cover'] = $cover;
        $result['meta'] = array(
            'transient' => $mayfly,
            'is_fallback'  => $default,
            'is_cache'=> $cache,
        );

        if($json) {
            die(json_encode($result));
        }
        return $cover;
    }

    /**
     * Retrieve singleton class instance
     * @return PeepSoVideos instance
     */
    public static function get_instance()
    {
        if (NULL === self::$_instance)
            self::$_instance = new self();
        return (self::$_instance);
    }

    /**
     * Loads the translation file for the PeepSo plugin
     */
    public function load_textdomain()
    {
        $path = str_ireplace(WP_PLUGIN_DIR, '', dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
        load_plugin_textdomain('vidso', FALSE, $path);
    }

    /*
     * Initialize the PeepSoVideos plugin
     */
    public function init()
    {
        // set up autoloading
        PeepSo::add_autoload_directory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR);
        PeepSoTemplate::add_template_directory(plugin_dir_path(__FILE__));

        $mayfly_3800 = 'videos_3800_upgrade';
        if(!PeepSo3_Mayfly::get($mayfly_3800)) {
            $uploads = PeepSo::get_option_new('videos_upload_enable', 0);
            $amazon = PeepSo::get_option_new('videos_enable_aws_s3_elastic_transcoder', 0);
            $new_option = 'no';

            if($uploads) {
                $new_option = 'ffmpeg';
            }

            if($amazon) {
                $new_option = 'aws_elastic';
            }

            // Update option
            $config = PeepSoConfigSettings::get_instance();
            $config->set_option('videos_conversion_mode', $new_option);

            // Block further checks forever
            PeepSo3_Mayfly::set($mayfly_3800, '1',-1);
        }

        if (is_admin()) {
            PeepSoVideosAdmin::get_instance();

            add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));

            add_action('delete_attachment', array(&$this, 'admin_delete_attachment'), 10, 1);
            add_action('deleted_post', array(&$this, 'admin_deleted_post'), 10, 1);
        } else {
            add_filter('peepso_post_types', array(&$this, 'post_types_audio'), 20, 2);
            add_filter('peepso_post_types', array(&$this, 'post_types'), 21, 2);
            add_filter('peepso_postbox_html-videos', array(&$this, 'display_video_postbox'));
            add_filter('peepso_activity_stream_action', array(&$this, 'activity_stream_action'), 10, 2);
            add_filter('peepso_postbox_tabs', array(&$this, 'postbox_tabs_audio'), 20);
            add_filter('peepso_postbox_tabs', array(&$this, 'postbox_tabs'), 21);
            add_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'), 10, 1);
            add_filter('peepso_postbox_interactions', array(&$this, 'postbox_interactions_video'), 101, 2);
            add_filter('peepso_postbox_interactions', array(&$this, 'postbox_interactions_audio'), 100, 2);
            add_filter('peepso_get_object_video', array(&$this, 'get_modal_video'), 10, 2);

            add_action('peepso_action_post_classes', function($id) {
                global $post;

                $media_type = get_post_meta($post->ID, self::POST_META_KEY_MEDIA_TYPE, TRUE);
                if ($media_type == self::ATTACHMENT_TYPE_AUDIO) {
                    echo " ps-post--audio";
                }
            });

            add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
            add_action('peepso_activity_post_attachment', array(&$this, 'attach_video'), 30, 1);

            add_filter('peepso_activity_insert_data', array(&$this, 'activity_insert_data'));
            add_action('peepso_activity_after_add_post', array(&$this, 'after_add_post'));
            add_action('peepso_activity_after_save_post', array(&$this, 'after_add_post'), 10, 1);
            // add_filter('peepso_pre_write_content', array(&$this, 'set_post_status'), 10, 1);

            // needs to be initialized here otherwise scripts don't get enqueued
            PeepSoVideosYoutube::get_instance();
            PeepSoVideosVimeo::get_instance();

            #add_filter('peepso_content_media', array(&$this, 'content_media'), 10, 2);
            add_filter('peepso_activity_post_edit', array(&$this, 'activity_post_edit'), 10, 2);

            // notifications
            add_filter('peepso_notifications_activity_type', array(&$this, 'notifications_activity_type'), 10, 2);

            // groups
            add_filter('peepso_group_segment_menu_links', array(&$this, 'filter_group_segment_menu_links'));
            add_action('peepso_group_segment_videos', array(&$this, 'peepso_group_segment_videos'), 10, 2);
            add_action('peepso_group_segment_media', array(&$this, 'peepso_group_segment_videos'), 10, 2);
            add_action('peepso_group_segment_audio', array(&$this, 'peepso_group_segment_videos'), 10, 2);

            // Hooks for getting root post
            add_filter('peepso_root_post_' . self::MODULE_ID, function($root) {
                $activity = new PeepSoActivity();

                $root_activity = $activity->get_activity_data($root->act_comment_object_id, $root->act_comment_module_id);
                $root = $activity->get_activity_post($root_activity->act_id);

                return $root;
            });

            // Hooks into getting root object
            add_filter('peepso_root_object_' . self::MODULE_ID, function($root) {
                $activity = new PeepSoActivity();

                $root_activity = $activity->get_activity_data($root->act_comment_object_id, $root->act_comment_module_id);
                $root = $activity->get_activity($root_activity->act_id);

                return $root;
            });
        }

        // move to general, so the content can deleted via admin.
        add_action('peepso_delete_content', array(&$this, 'delete_content'));

        add_filter('peepso_profile_alerts', array(&$this, 'profile_alerts'), 10, 1);
        add_filter('peepso_widgets', array(&$this, 'register_widgets'));

        // Hooks into profile pages and "me" widget
        add_action('peepso_profile_segment_videos', array(&$this, 'peepso_profile_segment_videos'));
        add_action('peepso_profile_segment_media', array(&$this, 'peepso_profile_segment_videos'));
        add_action('peepso_profile_segment_audio', array(&$this, 'peepso_profile_segment_videos'));
        add_filter('peepso_navigation_profile', array(&$this, 'filter_peepso_navigation_profile'));
        add_filter('peepso_rewrite_profile_pages', array(&$this, 'peepso_rewrite_profile_pages'));
        add_filter('peepso_filter_opengraph_' . self::MODULE_ID, array(&$this, 'peepso_filter_opengraph'), 10, 2);

        // Hook into Groups segment menu
        add_filter('peepso_navigation_profile', array(&$this, 'filter_peepso_navigation_profile'));

		// Compare last version stored in transient with current version
		if( $this::PLUGIN_VERSION.$this::PLUGIN_RELEASE != PeepSo3_Mayfly::get($mayfly = 'peepso_'.$this::PLUGIN_SLUG.'_version')) {
			PeepSo3_Mayfly::set($mayfly, $this::PLUGIN_VERSION.$this::PLUGIN_RELEASE);
			global $wpdb;
			$wpdb->query('ALTER TABLE '. $wpdb->prefix.'peepso_videos MODIFY COLUMN `vid_description` TEXT');
			$this->activate();
		}

        if(class_exists('PeepSoMaintenanceFactory') && class_exists('PeepSoMaintenanceVideos')) {
            new PeepSoMaintenanceVideos();
        }

        add_filter('posts_clauses_request', array(&$this, 'filter_post_clauses'), 10, 2);
        add_filter('peepso_activity_search_clauses', array($this, 'filter_search_clauses'));
    }

    /**
     * Adds the license key information to the config metabox
     * @param array $list The list of license key config items
     * @return array The modified list of license key items
     */
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
        $install = new PeepSoVideosInstall();
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
                <?php echo sprintf(__('The %s plugin requires the PeepSo plugin to be installed and activated.', 'vidso'), self::PLUGIN_NAME);?>
				<a href="plugin-install.php?tab=plugin-information&plugin=peepso-core&TB_iframe=true&width=772&height=291" class="thickbox">
                    <?php echo __('Get it now!', 'vidso');?>
                </a>
            </strong>
        </div>
        <?php
    }

    /**
     * Hooks into PeepSo for compatibility checks
     * @param $plugins
     * @return mixed
     */
    public function filter_all_plugins($plugins)
    {
        $plugins[plugin_basename(__FILE__)] = get_class($this);
        return $plugins;
    }

    /*
     * Get the directory the plugin is installed in
     * @return string The plugin directory, including a trailing slash
     */
    public static function get_plugin_dir()
    {
        return (plugin_dir_url(__FILE__));
    }

    public function allow_subscriber_to_uploads() {
        $subscriber = get_role('subscriber');

        if ( !empty($subscriber) && ! $subscriber->has_cap('upload_files') ) {
            $subscriber->add_cap('upload_files');
        }
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
        if (isset($_GET['tab']) && $_GET['tab'] == 'videos' && isset($_GET['clear-aws-history']) && wp_verify_nonce($_GET['nonce'], 'peepso-config-nonce')) {
            PeepSoVideosAWSErrors::clear_errors();
        }

        $tabs['videos'] = array(
            'label' => PeepSoVideos::profile_menu_label(),
            'icon' => 'https://cdn.peepso.com/icons/plugins/'.self::PLUGIN_EDD.'.svg',
            'tab' => 'videos',
            'description' => __('PeepSo Core: Audio & Video', 'vidso'),
            'function' => 'PeepSoConfigSectionVideos',
            'cat' => 'core',
        );

        return $tabs;
    }


    /**
     * Registers a tab in the PeepSo Queue Toolbar
     * PS_FILTER
     *
     * @param $tabs array
     * @return array
     */
    public function admin_queue_tabs($tabs)
    {
        if(PeepSoVideos::video_enabled() || PeepSoVideos::audio_enabled()) {

            $tabs['videos'] = array(
                'label' => __('Video Uploads', 'vidso'),
                'icon' => 'https://cdn.peepso.com/icons/plugins/'.self::PLUGIN_EDD.'.svg',
                'tab' => 'videos',
                'description' => '',
                'function' => array('PeepSoAdminVideosQueue', 'administration'),
                'cat'   => 'core',
            );

        }
        return $tabs;
    }

    /**
     * Adds the Videos tab to the available post type options
     * @param  array $post_types
     * @param  array $params
     * @return array
     */
    public function post_types($post_types, $params = array())
    {
        if (isset($params['is_current_user']) && $params['is_current_user'] === FALSE) {
            return ($post_types);
        }

        if(!apply_filters('peepso_permissions_videos_upload', TRUE) && !apply_filters('peepso_permissions_videos_embed', TRUE)) {
            return $post_types;
        }

        if(PeepSoVideos::video_enabled()) {
            $post_types['videos'] = array(
                'icon' => 'gcib gci-youtube',
                'name' => __('Video', 'vidso'),
                'class' => 'ps-postbox__menu-item',
            );
        }



        return ($post_types);
    }

    /**
     * Adds the Videos tab to the available post type options
     * @param  array $post_types
     * @param  array $params
     * @return array
     */
    public function post_types_audio($post_types, $params = array())
    {
        if (isset($params['is_current_user']) && $params['is_current_user'] === FALSE) {
            return ($post_types);
        }

        if(!apply_filters('peepso_permissions_audio_upload', TRUE) && !apply_filters('peepso_permissions_audio_embed', TRUE)) {
            return $post_types;
        }

        if(PeepSoVideos::audio_enabled()) {
            $post_types['audio'] = array(
                'icon' => 'gcis gci-music',
                'name' => __('Audio', 'vidso'),
                'class' => 'ps-postbox__menu-item',
            );
        }


        return ($post_types);
    }

    /*
     * enqueue scripts for peepsovideos
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script('peepsovideos',
            PeepSo::get_asset('js/bundle.min.js', __FILE__),
            array('peepso', 'peepso-fileupload', 'peepso-page-autoload', 'peepso-postbox'),
            self::PLUGIN_VERSION, TRUE);

        add_filter('peepso_data', function( $data ) {
            $data['media'] = array(
                'templateCard' => PeepSoTemplate::exec_template('videos', 'card', NULL, TRUE)
            );
            return $data;
        }, 10, 1 );

        // TODO move this to `peepsodata.media`;
        wp_localize_script('peepsovideos', 'peepsovideosdata', array(
            'upload_url'    => get_bloginfo('wpurl') . '/peepsoajax/videosajax.upload_video',
            'ajax_url'      => admin_url('admin-ajax.php'),
            'nonce'         => wp_create_nonce('media-form'),
            'upload_enable' => PeepSo::get_option('videos_upload_enable', 0),
            'autoplay'      => PeepSo::get_option('videos_autoplay', 0),
        ));

        wp_localize_script('peepsovideos', 'peepsoaudiodata', array(
            'upload_url'    => get_bloginfo('wpurl') . '/peepsoajax/videosajax.upload_audio',
            'ajax_url'      => admin_url('admin-ajax.php'),
            'nonce'         => wp_create_nonce('media-form'),
            'upload_enable' => PeepSo::get_option('videos_audio_enable', 0)
        ));

        if (apply_filters('peepso_free_bundle_should_brand', FALSE)) {
            wp_add_inline_script('peepso', "setTimeout(() => peepso.observer.do_action('show_branding'), 1000);");
        }
    }

    /**
     * Enqueue peepsovideos admin scripts
     */
    public function admin_enqueue_scripts()
    {
        wp_enqueue_script('peepso-admin-videos',
            PeepSo::get_asset('js/admin.js', __FILE__),
            array('jquery', 'underscore'), self::PLUGIN_VERSION, TRUE);
    }

    /**
     * Admin delete an attachement
     */
    public function admin_delete_attachment($post_id)
    {
        global $wpdb;

        // check the postmeta
        // if deleted attachment is video,
        // we should also delete the activity
        $post = get_post($post_id);
        $attachment_type = get_post_meta($post_id, self::POST_META_KEY_VIDEO_ATTACHMENT_TYPE, TRUE);

        if (($attachment_type == self::ATTACHMENT_TYPE_VIDEO || $attachment_type == self::ATTACHMENT_TYPE_AUDIO) && $post->post_status == 'inherit')
        {
            $this->delete_activity_post_id = $post->post_parent;
        }

        if ($attachment_type == self::ATTACHMENT_TYPE_POSTER)
        {
            $vid_data = array(
                'vid_thumbnail' => $thumbnail
            );
        }

        if ($attachment_type == self::ATTACHMENT_TYPE_ANIMATED_GIF)
        {
            $vid_data = array(
                'vid_animated' => $animated
            );
        }

        if (isset($vid_data))
        {

            $where = array(
                'vid_post_id' => $post->post_parent
            );

            $format_data = array(
                '%s',
            );

            $format_where = array(
                '%d'
            );

            return $wpdb->update($wpdb->prefix . PeepSoVideosModel::TABLE, $vid_data, $where);
        }
    }

    public function admin_deleted_post($post_id)
    {
        if (isset($this->delete_activity_post_id))
        {
            // delete all related attachment if any
            $attachments = get_posts( array(
                'post_type' => 'attachment',
                'posts_per_page' => -1,
                'post_parent' => $this->delete_activity_post_id
            ) );

            if ( $attachments ) {
                $force_delete = false;
                foreach ( $attachments as $attachment ) {
                    wp_delete_attachment( $attachment->ID, $force_delete );
                }
            }

            $activity = new PeepSoActivity();
            $activity->delete_post($this->delete_activity_post_id);
        }
    }

    /**
     * Displays the UI for the video post type
     * @return string The input html
     */
    public function postbox_tabs($tabs)
    {
        if(!apply_filters('peepso_permissions_videos_embed', TRUE) && !apply_filters('peepso_permissions_videos_upload', TRUE)) {
            return $tabs;
        }

        if(!PeepSoVideos::video_enabled()) {
            return $tabs;
        }

        wp_enqueue_script('peepsovideos');
        wp_enqueue_style('peepsovideos');
        // TODO: where are these being registered?
        // SpyDroid: enqueue_scripts() method of classes/videosyoutube.php
        // wp_enqueue_script('peepsovideosyoutubeiframeapi');
        // wp_enqueue_script('peepsovideosyoutube');

        $max_size = intval(PeepSo::get_option('videos_max_upload_size'));

        $wp_max_size = max(wp_max_upload_size(), 0);
        $wp_max_size /= pow(1024, 2); // convert to MB
        // use WP max upload size if it is smaller than PeepSo max upload size
        if ($wp_max_size < $max_size) {
            $max_size = $wp_max_size;
        }

        $data = array();
        $data['video_size'] = array(
            'max_size' => $max_size,
        );
        $tabs['videos'] = PeepSoTemplate::exec_template('videos', 'postbox-videos', $data, TRUE);

        return ($tabs);
    }

    /**
     * Displays the UI for the video post type
     * @return string The input html
     */
    public function postbox_tabs_audio($tabs)
    {

        if(!apply_filters('peepso_permissions_audio_upload', TRUE) && !apply_filters('peepso_permissions_audio_embed', TRUE)) {
            return $tabs;
        }

        if(!PeepSoVideos::audio_enabled()) {
            return $tabs;
        }

        wp_enqueue_script('peepsovideos');
        wp_enqueue_style('peepsovideos');
        // TODO: where are these being registered?
        // SpyDroid: enqueue_scripts() method of classes/videosyoutube.php
        // wp_enqueue_script('peepsovideosyoutubeiframeapi');
        // wp_enqueue_script('peepsovideosyoutube');

        $max_size = intval(PeepSo::get_option('videos_audio_max_upload_size', 20));

        $wp_max_size = max(wp_max_upload_size(), 0);
        $wp_max_size /= pow(1024, 2); // convert to MB
        // use WP max upload size if it is smaller than PeepSo max upload size
        if ($wp_max_size < $max_size) {
            $max_size = $wp_max_size;
        }

        $data = array();
        $data['video_size'] = array(
            'max_size' => $max_size,
        );
        $tabs['audio'] = PeepSoTemplate::exec_template('videos', 'postbox-audio', $data, TRUE);

        return ($tabs);
    }

    /**
     * Extract URL from a given post content
     * @param  string $content Contents of the post
     * @return array list of URLs
     */
    private function parse_urls($content)
    {
        static $urls = array(); // used for cache
        $hash = md5($content);
        if (!isset($urls[$hash])) {
            $pattern = "#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#i";
            $content = preg_replace('/<p[^>]*>(.*)<\/p[^>]*>/i', '$1', $content);

            $this->_urls = array();
            add_filter('oembed_dataparse', array(&$this, 'oembed_dataparse'), 10, 2);
            preg_replace_callback($pattern, array(&$this, 'video_url'), $content);
            remove_filter('oembed_dataparse', array(&$this, 'oembed_dataparse'), 10, 2);

            $urls[$hash] = $this->_urls;
        }
        return ($urls[$hash]);
    }

    /**
     * Adds the post_media metadata to the post, only called when submitting from the videos tab
     * @param  int $post_id The post ID
     */
    public function after_add_post($post_id)
    {
        global $wpdb;

        $input = new PeepSoInput();
        $url = $input->value('url','', FALSE); // SQL safe
        $type = $input->value('type','', array('audio','video'));
        $audio = $input->value('audio', '', FALSE);  // SQL safe
        $video = $input->value('video', '', FALSE);  // SQL safe
        $description = $input->value('content', '', FALSE);  // SQL safe
        $module_id = $input->int('module_id', 0);

        if (empty($url) && (empty($video) && empty($audio)))
            return;

        // delete any existing video
        $wpdb->delete($wpdb->prefix . self::TABLE, array('vid_post_id' => $post_id));

        $do_conversion = PeepSo::get_option('videos_conversion_mode', 'no');
        if(!empty($url)) {
            $media = $this->parse_oembed_url($url, $type);
        } else {
            $user = PeepSoUser::get_instance(get_current_user_id());
            $video_upload = PeepSoVideosUpload::get_instance();

            if ($type == 'audio') {
                $audio_title = $input->value('audio_title', '', FALSE);  // SQL safe
                $audio_artist = $input->value('audio_artist', '', FALSE);  // SQL safe
                $audio_album = $input->value('audio_album', '', FALSE);  // SQL safe

                $file_media_tmp = $user->get_image_dir() . 'videos' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $audio;
                $audio_attachement_id = $video_upload->upload_as_attachment(get_current_user_id(), $post_id, $file_media_tmp, $audio_title, PeepSoVideos::ATTACHMENT_TYPE_AUDIO);
                $audio_url = wp_get_attachment_url( $audio_attachement_id );
                $attr = array(
                    'src' => $audio_url,
                );
                $embed_code = wp_audio_shortcode( $attr );
                $embed_code = str_replace(' controls="controls"', ' controls="controls" controlslist="nodownload"', $embed_code);

                $audio_size = filesize($file_media_tmp);
                $vid_data = array(
                    'vid_post_id' => $post_id,
                    'vid_description' => $description,
                    'vid_embed' => $embed_code,
                    'vid_url' => $audio_url,
                    'vid_title' => $audio_title,
                    'vid_artist' => $audio_artist,
                    'vid_album' => $audio_album,
                    'vid_module_id' => $module_id,
                    'vid_size' => $audio_size,
                    'vid_stored' => 1,
                    'vid_stored_failed' => 0,
                    'vid_conversion_status' => PeepSoVideosUpload::STATUS_SUCCESS
                );

                // remove source
                if(file_exists($file_media_tmp)) {
                    unlink($file_media_tmp);
                }
            } else {
                if ($do_conversion == 'no') {
                    $video_title = $input->value('video_title', '', FALSE);  // SQL safe

                    $file_media_tmp = $user->get_image_dir() . 'videos' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $video;
                    $video_attachement_id = $video_upload->upload_as_attachment(get_current_user_id(), $post_id, $file_media_tmp, $video_title, PeepSoVideos::ATTACHMENT_TYPE_VIDEO);
                    $video_url = wp_get_attachment_url( $video_attachement_id );
                    $attr = array(
                        'src' => $video_url,
                    );

                    add_filter('wp_video_extensions', ['PeepSoVideosUpload','wp_video_extensions']);
                    $embed_code = wp_video_shortcode( $attr );
                    remove_filter('wp_video_extensions', ['PeepSoVideosUpload','wp_video_extensions']);

                    $embed_code = str_replace(' controls="controls"', ' controls="controls" controlslist="nodownload"', $embed_code);

                    $video_size = filesize($file_media_tmp);
                    $vid_data = array(
                        'vid_post_id' => $post_id,
                        'vid_description' => $description,
                        'vid_embed' => $embed_code,
                        'vid_url' => $video_url,
                        'vid_title' => $video_title,
                        'vid_module_id' => $module_id,
                        'vid_size' => $video_size,
                        'vid_stored' => 1,
                        'vid_stored_failed' => 0,
                        'vid_conversion_status' => PeepSoVideosUpload::STATUS_SUCCESS
                    );
                    add_post_meta($post_id, PeepSoVideos::POST_META_KEY_VIDEO_NO_CONVERSION, TRUE, true);

                    // remove source
                    if(file_exists($file_media_tmp)) {
                        unlink($file_media_tmp);
                    }

                } else {
                    $video_title = $input->value('video_title', '', FALSE); // SQL Safe
                    $videourl = $user->get_image_url() . 'videos/tmp/' . $video;
                    $media = array(
                        'host' => get_site_url(),
                        'description' => $description,
                        'content' => '',
                        'url' => $videourl,
                        'title' => $video_title
                    );
                    $url = $media['url'];
                }
            }
        }

        if (isset($media)) {
            $vid_data = array(
                'vid_post_id' => $post_id,
                'vid_description' => $media['description'],
                'vid_embed' => $media['content'],
                'vid_url' => $url,
                'vid_title' => $media['title'],
                'vid_module_id' => $module_id,
                'vid_transcoder_job_id' => ''
            );

            if ($do_conversion == 'aws_elastic') {
                $siteurl = get_option( 'siteurl' );
                $siteurl = parse_url($siteurl, PHP_URL_HOST);
                if (strpos($url, $siteurl)) {
                    $vid_data['vid_upload_s3_status'] = PeepSoVideosUpload::STATUS_S3_WAITING;
                }
            }

            if ( preg_match( '/facebook\.com/', $media['host'] ) ) {
                $thumbnail = $this->get_facebook_thumbnail($url);
                if ($thumbnail) {
                    $vid_data['vid_thumbnail'] = $thumbnail;
                }
            } elseif ( isset($media['thumbnail'] ) ) {
                $vid_data['vid_thumbnail'] = $media['thumbnail'];
            }
        }

        if (isset($vid_data)) {
            $wpdb->insert($wpdb->prefix . self::TABLE, $vid_data);

            $media_type = self::ATTACHMENT_TYPE_VIDEO;
            if ($type == 'audio') {
                $media_type = self::ATTACHMENT_TYPE_AUDIO;
            }

            // add postmeta
            add_post_meta($post_id, PeepSoVideos::POST_META_KEY_MEDIA_TYPE, $media_type, true);
        }
    }

    /**
     * Set post status for videos upload
     * @param array $aPostData
     * @return array $aPostData
     */
    public function set_post_status($aPostData) {

        $input = new PeepSoInput();

        $url = $input->value('url', '', FALSE); // SQL Safe
        $type = $input->value('type','', array('audio','video')); // SQL Safe
        $video = $input->value('video', '', FALSE); // SQL Safe
        $video_title = $input->value('video_title', '', FALSE); // SQL Safe

        if((!empty($video)) && ($type == 'video') && is_array($aPostData)) {
            $aPostData['post_status'] = 'pending';
        }

        return $aPostData;
    }

    /*
     * Callback for preg_replace_callback to extract video url
     *
     * @param array $matches The matched items
     * @return string the modified url
     */
    public function video_url($matches)
    {
        $url = strip_tags($matches[0]);

        if (FALSE === strpos($url, '://'))
            $url = 'http://' . $url;

        $embed_code = ps_oembed_get($url, array('width' => 500, 'height' => 300));
        // Get video only
        if ($embed_code && 'video' === $this->_oembed_type)
            $this->_urls[] = $url;

        return ($url);
    }


    /**
     * Sets the activity's module ID to the plugin's module ID
     * @param  array $activity
     * @return array
     */
    public function activity_insert_data($activity)
    {
        $input = new PeepSoInput();

        $type = $input->value('type','', array('audio','video'));

        if ('video' === $type || 'audio' === $type)
            $activity['act_module_id'] = self::MODULE_ID;

        return ($activity);
    }

    /**
     * Parse the oembed response to get the format needed to display the content
     * @param  string $url The URL to check for
     * @return array
     */
    public function parse_oembed_url($url, $accepted_type = 'video')
    {
        if (FALSE === strpos($url, '://'))
            $url = 'http://' . $url;

        add_filter('oembed_dataparse', array(&$this, 'oembed_dataparse'), 10, 2);
        $response = ps_oembed_get($url, array('width' => 500, 'height' => 300));
        remove_filter('oembed_dataparse', array(&$this, 'oembed_dataparse'), 10, 2);

        if (FALSE === $response) {
            return FALSE;
        }

        if ($accepted_type === 'audio') {
            if ( ! in_array($this->_oembed_type, array('audio', 'rich')) ) {
                return FALSE;
            }
        } else if ($accepted_type !== $this->_oembed_type) {
            return FALSE;
        }

        $media['content'] = $response;
        $media['title'] = (NULL === $this->_oembed_title) ? $url : $this->_oembed_title;
        $media['host'] = parse_url($url, PHP_URL_HOST);
        $media['url'] = $url;
        $media['description'] = $url;
        $media['force_oembed'] = true;
        $media['oembed_type'] = $accepted_type;

        $media['target'] = (int) PeepSo::get_option('site_activity_open_links_in_new_tab', 1);
        if (2 === $media['target'] && 0 === strpos($url, site_url())) {
            $media['target'] = 0;
        }

        $og_tags = PeepSoOpenGraph::fetch($url);

        if ($og_tags) {
            if ($og_tags->title)
                $media['title'] = $og_tags->title;

            if ($og_tags->description)
                $media['description'] = $og_tags->description;
        }

        if (isset($this->_oembed_data->thumbnail_url))
            $media['thumbnail'] = $this->_oembed_data->thumbnail_url;

        return ($media);
    }

    /**
     * Parse the oembed response to get the format needed to display the content
     * @param  string $url The URL to check for
     * @return array
     */
    public function parse_attached_media($post_id, $title)
    {
        $response = '';
        $attached_media = get_attached_media('video', $post_id);
        $post = get_post($post_id);
        foreach ($attached_media as $video) {
            $attr = array(
                'src' => $video->guid,
            );
            $url = $video->guid;

            add_filter('wp_video_extensions', ['PeepSoVideosUpload','wp_video_extensions']);
            $response = wp_video_shortcode( $attr );
            remove_filter('wp_video_extensions', ['PeepSoVideosUpload','wp_video_extensions']);
        }

        if (empty($response)) {
            return (FALSE);
        }

        $media['content'] = $response;
        $media['title'] = $title;
        $media['host'] = parse_url($url, PHP_URL_HOST);
        $media['url'] = $url;
        $media['description'] = $post->post_content;
        $media['force_oembed'] = true;
        $media['oembed_type'] = 'video';

        $media['target'] = (int) PeepSo::get_option('site_activity_open_links_in_new_tab', 1);
        if (2 === $media['target'] && 0 === strpos($url, site_url())) {
            $media['target'] = 0;
        }

        $thumbnail = '';
        if (isset($thumbnail))
            $media['thumbnail'] = $thumbnail;

        return ($media);
    }

    /**
     * Assigns the oemebed type
     * @param  array $return
     * @param  object $data The oembed response data
     * @return array
     */
    public function oembed_dataparse($return, $data)
    {
        $this->_oembed_data = $data;
        $this->_oembed_type = $data->type;

        // Title is an optional oembed response
        if (isset($data->title))
            $this->_oembed_title = $data->title;

        return ($return);
    }

    /**
     * Get a video associated with a post
     * @param  int $post_id
     * @return
     */
    public function get_post_video($post_id)
    {
        global $wpdb;

        $sql = "SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "` WHERE `vid_post_id` = %d";

        return ($wpdb->get_results($wpdb->prepare($sql, $post_id)));
    }

    /**
     * Attach the video to the post display
     * @param  object $post The post
     */
    public function attach_video($post)
    {
        $post_videos = $this->get_post_video($post->ID);

        if (empty($post_videos))
            return;

        PeepSoVideosYoutube::get_instance(); // load only when filter peepso_videos_attachment is called/applied
        foreach ($post_videos as $post_video) {
            $video_target = (int) PeepSo::get_option('site_activity_open_links_in_new_tab', 1);
            if (2 === $video_target && 0 === strpos($post_video->vid_url, site_url())) {
                $video_target = 0;
            }

            $video = array(
                'content' => $post_video->vid_embed,
                'title' => $post_video->vid_title,
                'host' => parse_url($post_video->vid_url, PHP_URL_HOST),
                'url' => $post_video->vid_url,
                'description' => $post_video->vid_description,
                'target' => $video_target,
                'thumbnail' => isset($post_video->vid_thumbnail) ? $post_video->vid_thumbnail : '',
                'animated' => isset($post_video->vid_animated) ? $post_video->vid_animated : '',
                'animated_webm' => isset($post_video->vid_animated_webm) ? $post_video->vid_animated_webm : ''
            );

            if (isset($post_video->vid_thumbnail)) {
                $data = array(
                    'id' => $post_video->vid_id,
                    'content' => '',
                    'thumbnail' => $post_video->vid_thumbnail,
                    'onclick' => (ps_isempty($post->is_repost) ? 'ps_videos.play_video(this);' : "ps_comments.open({$post->ID}, 'video');")
                );
                $video['content'] = $post_video->vid_embed;#PeepSoTemplate::exec_template('videos', 'thumbnail', $data, TRUE);
            }
            #$video = apply_filters('peepso_videos_attachment', $video, $post);

            $video['force_oembed'] = TRUE;

            $playsinline = (int) PeepSo::get_option('videos_play_inline', 0);

            // make iframe full-width
            if (preg_match('/<iframe/i', $video['content'])) {
                $width_pattern = "/width=\"[0-9]*\"/";
                $video['content'] = preg_replace($width_pattern, "width='100%'", $video['content']);
                $video['content'] = '<div class="ps-media ps-media--iframe ps-media-iframe">' . $video['content'] . '</div>';

                // Force video link to play inline.
                if ($playsinline) {
                    // https://www.youtube.com/embed/ilA-uusMHis?feature=oembed
                    if ( preg_match( '#src="([^"]+youtube\.com/embed/[^"]+)"#i', $video['content'], $matches ) ) {
                        $url = $matches[1] . (strpos($matches[1], '?') === false ? '?' : '&') . 'playsinline=1';
                        $video['content'] = preg_replace('#src="([^"]+youtube\.com/embed/[^"]+)"#i', 'src="' . $url . '"', $video['content']);
                    }
                    // https://player.vimeo.com/video/284901308?dnt=1&app_id=122963
                    else if ( preg_match( '#src="([^"]+player\.vimeo\.com/video/[^"]+)"#i', $video['content'], $matches ) ) {
                        $url = $matches[1] . (strpos($matches[1], '?') === false ? '?' : '&') . 'playsinline=1';
                        $video['content'] = preg_replace('#src="([^"]+player\.vimeo\.com/video/[^"]+)"#i', 'src="' . $url . '"', $video['content']);
                    }
                    // https://embed.ted.com/talks/jill_seubert_how_a_miniaturized_atomic_clock_could_revolutionize_space_exploration
                    else if ( preg_match( '#src="([^"]+embed\.ted\.com/[^"]+)"#i', $video['content'], $matches ) ) {
                        $url = $matches[1] . (strpos($matches[1], '?') === false ? '?' : '&') . 'playsinline=1';
                        $video['content'] = preg_replace('#src="([^"]+embed\.ted\.com/[^"]+)"#i', 'src="' . $url . '"', $video['content']);
                    }

                }

                $video['content'] = apply_filters('the_content', $video['content']);

            // Handle self-hosted video.
            } else if (preg_match('/wp-video-shortcode/i', $video['content'])) {
                $video['content'] = preg_replace('/width="[0-9]*"/', 'width="100%"', $video['content']);
                $video['content'] = preg_replace('/\s*style="width[^"]+"/', '', $video['content']);
                $video['content'] = preg_replace('/<video\s/', '<video autoplay ', $video['content']);

                // Fix video thumbnail on Safari: https://stackoverflow.com/questions/41255841/how-to-get-html5-video-thumbnail-without-using-poster-on-safari-or-ios
                $video['content'] = preg_replace('/\s*src="([^"]+)"/', ' src="$1#t=0.1"', $video['content']);

                # 5999 force video/mp4 on QT videos: https://stackoverflow.com/questions/31380695/how-to-open-mov-format-video-in-html-video-tag
                if(stristr($video['content'],'video/quicktime')) {
                    $video['content'] = str_ireplace('video/quicktime','video/mp4', $video['content']);
                }

                // Force video upload to play inline.
                if ($playsinline) {
                    $video['content'] = preg_replace('#(<video[^>]+)(playinline\s)?#i', '$1playsinline ', $video['content']);
                }

                // Video has thumbnail.
                if ($video['thumbnail']) {
                    // $video['content'] = preg_replace('/<video\s/', '<video autoplay ', $video['content']);

                    $html = '<div class="ps-media__video-thumb ps-video-thumbnail" style="background-image:url(' . $video['thumbnail'] . ');">'
                          . '<img src="' . $video['thumbnail'] . '"'
                          . ' data-animated="' . $video['animated'] . '"'
                          . ' data-animated-webm="' . $video['animated_webm'] . '" />'
                          . '<i class="gcib gci-youtube ps-video-play ps-js-media-play"></i>'
                          . '</div>';

                    $embed = '<script type="text/template">' .  $video['content'] . '</script>';

                // Video has no thumbnail.
                } else {
                    // // Remove default controls before video is played.
                    $video['content'] = preg_replace('/(<video[^>]+?)\s*autoplay(="[^"]*?")?/', '$1', $video['content']);
                    $video['content'] = preg_replace('/(<video[^>]+?)\s*controls(="[^"]*?")?/', '$1', $video['content']);

                    $html = '<div class="ps-media__video-thumb ps-video-thumbnail" style="background:none; position:absolute; top:0; left:0; right:0; bottom:0; z-index:1">'
                          . '<i class="gcib gci-youtube ps-video-play ps-js-media-play"></i>'
                          . '</div>';

                    $embed = $video['content'];
                }

                $video['content'] = '<div class="ps-media ps-media--iframe ps-media-iframe ps-js-video">'
                    . $html
                    . $embed
                    .'</div>';
            }

            // Improve Facebook embedded content rendering.
            if (preg_match('#class="fb-(post|video)"#i', $video['content'])) {

                // Remove Facebook SDK loader code.
                $video['content'] = preg_replace('#<div[^>]+id="fb-root"[^<]+</div>#i', '', $video['content']);
                $video['content'] = preg_replace('#<script[^<]+</script>#i', '', $video['content']);

                // Remove width setting, follow container width.
                // #1931 Fix Facebook video issue.
                $video['content'] = preg_replace('#\sdata-width=["\']\d+%?["\']#i', '', $video['content']);
            }

            $video = apply_filters('peepso_videos_attach_before', $video);

            $siteurl = get_option( 'siteurl' );
            $siteurl = parse_url($siteurl, PHP_URL_HOST);
            if (($post_video->vid_stored == 1) ||
                ($post_video->vid_conversion_status == PeepSoVideosUpload::STATUS_SUCCESS && $post_video->vid_stored == 1) ||
                (strpos($post_video->vid_url, $siteurl) === FALSE && $post_video->vid_transcoder_job_id == '')) {
                PeepSoTemplate::exec_template('videos', 'content-media', $video);
            } else {

                $video['vid_upload_s3_status'] = $post_video->vid_upload_s3_status;
                $video['vid_conversion_status'] = $post_video->vid_conversion_status;
                $video['vid_post_id'] = $post_video->vid_post_id;
                PeepSoTemplate::exec_template('videos', 'content-media-pending', $video);
            }
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
        if (self::MODULE_ID === intval($post->act_module_id)) {

            // Defaults
            $before = '';
            $title = '';
            $after = '';

            $icon = apply_filters('peepso_filter_video_action_icon','gcib gci-youtube');
            $text = apply_filters('peepso_filter_video_action_text', __('posted','vidso'));

            $action = ' ' . __('posted a video', 'vidso');

            // Attempt to get a title
            $media = $this->get_post_video($post->ID);
            if(is_array($media)) {
                $media = $media[0];
            }

            #PeepSo/PeepSo#3321 remove override title action text
            if(is_object($media) && property_exists($media,'vid_title') && property_exists($media, 'vid_stored')) {
                if ($media->vid_stored == 1) {
                    $title = $media->vid_title;
                }
            }

            // Maybe audio
            $media_type = get_post_meta($post->ID, self::POST_META_KEY_MEDIA_TYPE, TRUE);
            if ($media_type == self::ATTACHMENT_TYPE_AUDIO) {
                $action = ' ' . __('posted an audio', 'vidso');
                $icon = apply_filters('peepso_filter_audio_action_icon','gcis gci-music');
                $text = apply_filters('peepso_filter_audio_action_text', __('posted','vidso'));

                $album = '';
                $artist = '';
                if(is_object($media) && property_exists($media, 'vid_stored')) {
                    if ($media->vid_stored == 1) {
                        $artist = $media->vid_artist;
                        $album = $media->vid_album;
                    }
                }

                // Append artist
                if(strlen($title) && strlen($artist)) {
                    $after .= ' ' . __('by','vidso') . ' ' . $artist;
                }

                // Hover Card
                if(strlen($album) || strlen($artist)) {
                    $before .= '<a class="cover-art-trigger" data-artist="' . $artist . '" data-album="' . $album . '">';

                    $after .= '</a>';
                }
            }

            if (strlen($title)) {
                $action = $text . '<i class="ps-post__subtitle-icon ' . $icon . '" /></i><i class="ps-post__subtitle-media">"' . $title . '"</i>';
            }


            $action = $before . $action . $after;
        }

        return ($action);
    }

    /**
     * Deletes videos associated to a post when it is deleted
     * @param  int $post_id The post ID
     */
    public function delete_content($post_id)
    {
        global $wpdb;

        $sql = "SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "` WHERE `vid_post_id`=%d";

        $video = ($wpdb->get_row($wpdb->prepare($sql, $post_id)));

        if($video === null) {
            return;
        }

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

        if ($video->vid_conversion_status == PeepSoVideosUpload::STATUS_PENDING) {
            $videomodel = new PeepSoVideosModel();
            $postvideo = $videomodel->get_video($video->vid_id);
            if ($postvideo !== null) {
                $file_source = $videomodel->get_video_dir($postvideo->post_author) . 'tmp' . DIRECTORY_SEPARATOR . basename($video->vid_url);

                if (file_exists($file_source)) {
                    unlink($file_source);
                }
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

        $wpdb->delete($wpdb->prefix . self::TABLE, array('vid_post_id' => $post_id));
    }

    /**
     * Checks if empty content is allowed
     * @param boolean $allowed
     * @return boolean always returns TRUE
     */
    public function activity_allow_empty_content($allowed)
    {
        $input = new PeepSoInput();

        //SQL safe
        $type = $input->value('type','', FALSe);
        if ('video' === $type || 'audio' === $type ) {
            $allowed = TRUE;
        }
        return ($allowed);
    }

    /**
     * Format post_media video type
     * @param array $media PeepSo media post attachment
     * @return array $media Modified peepso media post attachment
     */
    public function content_media($media, $post)
    {
        PeepSoVideosYoutube::get_instance(); // load only when filter peepso_videos_attachment is called/applied
        foreach ($media as $key => $value) {
            $post_video = (isset($value['url']) && $value['url']) ? $this->parse_oembed_url($value['url'], 'video') : NULL;
            if (!$post_video)
                continue;

            $video_id = $post->ID . '-' . $key;
            if (isset($post_video['thumbnail'])) {
                $data = array(
                    'id' => $video_id,
                    'content' => $value['content'],
                    'thumbnail' => $post_video['thumbnail'],
                );
                $post_video['content'] = PeepSoTemplate::exec_template('videos', 'thumbnail', $data, TRUE);
            }
            $media[$key] = apply_filters('peepso_videos_attachment', $post_video, $post);
        }
        return ($media);
    }

    /**
     * Append input box to edit URL
     * @param array $data Contains 'cont' and 'post_id' indexes used for rendering edit box
     * @return array $data Modified input box
     */
    public function activity_post_edit($data)
    {
        // add prefix input box
        $video = $this->get_post_video($data['post_id']);
        if (isset($video[0])) {
            $post_edit = array(
                'url' => $video[0]->vid_url,
                'post_id' => $data['post_id'],
            );
            PeepSoTemplate::exec_template('videos', 'post-edit', $post_edit, TRUE);
            $data['prefix'] = PeepSoTemplate::exec_template('videos', 'post-edit', array('url' => $video[0]->vid_url), TRUE);
        }
        return ($data);
    }

    /**
     * This function inserts the video options on the post box
     * @param array $interactions is the formated html code that get inserted in the postbox
     * @param array $params
     */
    public function postbox_interactions_video($interactions, $params = array())
    {

        if (isset($params['is_current_user']) && $params['is_current_user'] === FALSE) {
            return ($interactions);
        }

        if(!apply_filters('peepso_permissions_videos_upload', TRUE) && !apply_filters('peepso_permissions_videos_embed', TRUE)) {
            return $interactions;
        }

        if(PeepSoVideos::video_enabled()) {
            $interactions['videos'] = array(
                'icon' => 'gcib gci-youtube',
                'id' => 'video-post',
                'class' => 'ps-postbox__menu-item',
                'click' => 'return;',
                'label' => '',
                'title' => __('Video', 'vidso'),
                'style' => 'display:none'
            );
        }

        return ($interactions);
    }

    /**
     * This function inserts the video options on the post box
     * @param array $interactions is the formated html code that get inserted in the postbox
     * @param array $params
     */
    public function postbox_interactions_audio($interactions, $params = array())
    {

        if (isset($params['is_current_user']) && $params['is_current_user'] === FALSE) {
            return ($interactions);
        }

        if(!apply_filters('peepso_permissions_audio_upload', TRUE) && !apply_filters('peepso_permissions_audio_embed', TRUE)) {
            return $interactions;
        }

        if(PeepSoVideos::audio_enabled()) {
            $interactions['audio'] = array(
                'icon' => 'gcis gci-music',
                'id' => 'audio-post',
                'class' => 'ps-postbox__menu-item',
                'click' => 'return;',
                'label' => '',
                'title' => __('Audio', 'vidso'),
                'style' => 'display:none'
            );
        }

        return ($interactions);
    }

    /**
     * modal comment callback
     * Returns the embedded video.
     * @param  array $objects
     * @param  int $post_id The ID of the post.
     * @return array
     */
    public function get_modal_video($objects, $post_id)
    {
        $video = $this->get_post_video($post_id);

        if (NULL !== $video) {
            $video = $video[0];
            $activity = new PeepSoActivity();
            $post = $activity->get_post($post_id);

            $video->vid_embed = preg_replace('#\sheight=["\']\d+%?["\']#i', " height='350'", $video->vid_embed);
            $video->vid_embed = preg_replace('#\swidth=["\']\d+%?["\']#i', " width='100%'", $video->vid_embed);
            $video->vid_embed = apply_filters('widget_custom_html_content', $video->vid_embed);

            // Handle self-hosted video.
            if (preg_match('/wp-video-shortcode/i', $video->vid_embed)) {
                $video->vid_embed = preg_replace('/width="[0-9]*"/', 'width="100%"', $video->vid_embed);
                $video->vid_embed = preg_replace('/\s*style="width[^"]+"/', '', $video->vid_embed);
                $video->vid_embed = preg_replace('/<video\s/', '<video autoplay ', $video->vid_embed);

                // Fix video thumbnail on Safari: https://stackoverflow.com/questions/41255841/how-to-get-html5-video-thumbnail-without-using-poster-on-safari-or-ios
                $video->vid_embed = preg_replace('/\s*src="([^"]+)"/', ' src="$1#t=0.1"', $video->vid_embed);

                # 5999 force video/mp4 on QT videos: https://stackoverflow.com/questions/31380695/how-to-open-mov-format-video-in-html-video-tag
                if (stristr($video->vid_embed,'video/quicktime')) {
                    $video->vid_embed = str_ireplace('video/quicktime','video/mp4', $video->vid_embed);
                }

                // Remove autoplay attribute.
                $video->vid_embed = preg_replace('/(<video[^>]+?)\s*autoplay(="[^"]*?")?/', '$1', $video->vid_embed);
            }

            // Fix Facebook SDK loader code.
            if ( preg_match('#class="fb-(post|video)"#i', $video->vid_embed ) ) {
                $video->vid_embed = preg_replace( '#<div[^>]+id="fb-root"[^<]+</div>#i', '', $video->vid_embed );
                $video->vid_embed = preg_replace( '#<script[^<]+</script>#i', '<script>setTimeout(function(){peepso.util.fbParseXFBML()},1);</script>', $video->vid_embed );
                $video->vid_embed = preg_replace( '#data-href#i', 'data-autoplay="1" data-href', $video->vid_embed );

                // Remove width setting, follow container width.
                // #1931 Fix Facebook video issue.
                $video->vid_embed = preg_replace( '#\sdata-width=["\']\d+%?["\']#i', '', $video->vid_embed );
            }

            $objects[$post_id] = array(
                'module_id' => self::MODULE_ID,
                'content' 	=> $video->vid_embed,
                'post' 		=> $post->post
            );
        }

        return ($objects);
    }

    /**
     * Add capability for like/comment/share video
     * @param $activity_type modified activity type (default is `post`)
     * @param $post_id Post Id in notification or email
     */
    public function notifications_activity_type($activity_type, $post_id) {

        /**
         * Please note that we mus define email template for each
         * 1. like_{type}
         * 2. user_comment_{type}
         * 3. share_{type}
         */

        ## @todo: find other way to escape translation for template name

        $video = $this->get_post_video($post_id);

        if ( count( $video ) > 0 && is_array($activity_type)) {

            $type = 'video';
            if(in_array($activity_type['type'], array('user_comment', 'share'))) {
                $type = $activity_type['type'] . '_' . $type;
            }

            $activity_type = array(
                    'type' => $type,
                    'text' => __('video', 'vidso')
                );
        }

        return ($activity_type);
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
            if(strlen($widget)>=5) require_once($widget_dir . $widget);
        }
        return array_merge($widgets, $this->widgets);
    }


    /**
     * Append profile alerts definition for peepsovideos
     */
    public function profile_alerts($alerts)
    {
        $items = array();

        // @TODO CLEANUP

//        $items = array(
//            array(
//                'label' => __('Someone liked my Video', 'vidso'),
//                'setting' => 'like_video',
//                'loading' => TRUE,
//            ),
//            array(
//                'label' => __('Someone commented on my Video', 'vidso'),
//                'setting' => 'user_comment_video',
//                'loading' => TRUE,
//            )
//        );
//
//        if (PeepSo::get_option('site_repost_enable', TRUE)) {
//            array_push($items, array(
//                'label' => __('Someone shared my Video', 'vidso'),
//                'setting' => 'share_video',
//                'loading' => TRUE,
//            ));
//        }

        if (PeepSo::get_option('videos_upload_enable', FALSE)) {
            array_push($items, array(
                'label' => __('Video conversion complete', 'vidso'),
                'setting' => 'video_conversion_complete',
                'loading' => TRUE,
            ));

            array_push($items, array(
                'label' => __('Video conversion failed', 'vidso'),
                'setting' => 'video_conversion_failed',
                'loading' => TRUE,
            ));
        }

        if(!count($items)) {
            return $alerts;
        }

        $alerts['videos'] = array(
                'title' => (PeepSo::get_option('videos_audio_enable')) ? __('Audio & Video', 'vidso') : __('Videos', 'vidso'),
                'items' => $items,
        );
        // NOTE: when adding new items here, also add settings to /install/activate.php site_alerts_ sections
        return ($alerts);
    }

    public function filter_peepso_navigation_profile($links)
    {
        $links[PeepSoVideos::profile_menu_slug()] = array(
            'href' => PeepSoVideos::profile_menu_slug(),
            'label'=> PeepSoVideos::profile_menu_label(),
            'icon' => PeepSoVideos::profile_menu_icon(),
        );

        return $links;
    }

    public function filter_group_segment_menu_links($links)
    {
        $links[30][] = array(
            'href' => PeepSoVideos::profile_menu_slug(),
            'title'=> PeepSoVideos::profile_menu_label(),
            'icon' => PeepSoVideos::profile_menu_icon(),
        );

        ksort($links);
        return $links;
    }

    public function peepso_profile_segment_videos()
    {
        $pro = PeepSoProfileShortcode::get_instance();
        $this->view_user_id = PeepSoUrlSegments::get_view_id($pro->get_view_user_id());

        echo PeepSoTemplate::exec_template('videos', 'videos', array('view_user_id' => $this->view_user_id), TRUE);
        wp_enqueue_script('peepsovideos');
    }

    public function peepso_rewrite_profile_pages($pages)
    {
        return array_merge($pages, array('videos'));
    }

	public function peepso_filter_opengraph($tags, $activity)
	{
		$video = PeepSoVideos::get_instance()->get_post_video($activity->ID);
		if (count($video) > 0)
		{
			$tags['image'] = $video[0]->vid_thumbnail;
		}

		return $tags;
	}

    public function peepso_group_segment_videos($args, $url_segments)
    {
        if(!$url_segments instanceof PeepSoUrlSegments) {
            $url_segments = PeepSoUrlSegments::get_instance();
        }

        $this->view_user_id = $args['group']->id;

        echo PeepSoTemplate::exec_template('videos', 'videos-group', array_merge(array('view_user_id' => $this->view_user_id), $args), TRUE);
        wp_enqueue_script('peepsovideos');
    }

    /**
     * Facebook video thumbnail fetcher, according to the Drupal code below:
     * https://git.drupalcode.org/project/facebookoembed/blob/6fd48ad891964a3cb2533e8930f26cd95622ceb2/src/FacebookoembedResourceFetcher.php
     *
     * @param string $url
     * @return string|false
     */
    private function get_facebook_thumbnail( $url )
    {
        $result = false;

        // URL format #1: https://www.facebook.com/video.php?v=169031797695592
        // URL format #2: https://web.facebook.com/nba/videos/169031797695592
        // URL format #3: https://www.facebook.com/nba/videos/vl.2574477546123233/169031797695592
        if ( preg_match( '/^.+\/(videos\/([^\/]+\/)?|video\.php\?v=)(\d+)\/?$/i', $url, $matches ) ) {
            $video_id = $matches[3];
            $embed_url = 'https://www.facebook.com/video/embed?video_id=' . $video_id;

            try {
                $request = wp_safe_remote_get($embed_url);
                $content = wp_remote_retrieve_body($request);

                if ( $content ) {
                    $content = substr($content, strpos($content,"<img"));
                    $content = substr($content, strpos($content,'style="')+7);
                    $content = substr($content, strpos($content,'url(&#039;')+10);
                    $content = substr($content, 0, strpos($content,'&#039;'));

                    while (strpos($content,'\\')) {
                        $code=substr($content,strpos($content,'\\')+1,2);
                        $content=substr($content,0,strpos($content,'\\'))."%$code".substr($content,strpos($content,'\\')+4);
                    }

                    $result = urldecode($content) . '&imagetype=image.jpg';
                }
            } catch (Exception $e) {
            }
        }

        return $result;
    }

    function filter_search_clauses($where)
    {
        $PeepSoInput = new PeepSoInput();
        $search = $PeepSoInput->value('search', NULL, FALSE); // SQL Safe. optional, string to search
        if ($search) {
            $search_mode = $PeepSoInput->value('search_mode', 'exact', array('exact','any')); // optional, whether to use exact phrase or any word

            global $wpdb;
    
            if ('any' == $search_mode) {
                $search = explode(' ', $search);
                foreach ($search as $key) {
                    $search_qry[] = " (`peepso_videos`.`vid_title` LIKE '%$key%') ";
                }
            } else {
                $search_qry[] = " (`peepso_videos`.`vid_title` LIKE '%$search%') ";
            }
    
            $where[] = "(". implode(' OR ', $search_qry) .")";
        }

        return $where;
    }

    public function filter_post_clauses($clauses, $query) {
        $PeepSoInput = new PeepSoInput();
        $search = $PeepSoInput->value('search', NULL, FALSE); // SQL Safe. optional, string to search

        if ($search) {
            global $wpdb;

            $clauses['join'] .= " LEFT JOIN `{$wpdb->prefix}peepso_videos` `peepso_videos` ON `peepso_videos`.`vid_post_id` = `{$wpdb->prefix}posts`.`ID`";
        }

        return $clauses;
    }
}

PeepSoVideos::get_instance();

// EOF
