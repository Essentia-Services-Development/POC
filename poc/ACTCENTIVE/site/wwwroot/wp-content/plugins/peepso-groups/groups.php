<?php
/**
 * Plugin Name: PeepSo Core: Groups
 * Plugin URI: https://peepso.com
 * Description: Public and closed user groups
 * Author: PeepSo
 * Author URI: https://peepso.com
 * Version: 6.2.7.0
 * Copyright: (c) 2015 PeepSo, Inc. All Rights Reserved.
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: groupso
 * Domain Path: /language
 *
 * We are Open Source. You can redistribute and/or modify this software under the terms of the GNU General Public License (version 2 or later)
 * as published by the Free Software Foundation. See the GNU General Public License or the LICENSE file for more details.
 * This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY.
 */

class PeepSoGroupsPlugin
{
    private static $_instance = NULL;

    private $url_segments;
    private $input;

    private $photo_group_system_album;

    const PLUGIN_NAME	 = 'Core: Groups';
    const PLUGIN_VERSION = '6.2.7.0';
    const PLUGIN_RELEASE = ''; //ALPHA1, RC1 etc, '' for STABLE
    const PLUGIN_EDD = 67133;
    const PLUGIN_SLUG 	 = 'groupso';

    const JOIN_STREAM = 'PEEPSO_GROUPS_JOIN_STREAM_NOTIFICATION';
    const JOIN_STREAM_META = 'peepso_groups_is_join_notification';

    const MODULE_ID 	 = 8;

    const ICON_CATEGORIES = 'https://cdn.peepso.com/plugins/peepso-groups/icon.svg';

    public $shortcodes= array(
        'peepso_groups' => 'PeepSoGroupsShortcode::shortcode_groups',
    );

    public $view_user_id;

    public $widgets = array(
        'PeepSoWidgetGroup',
    );

    public static $group_slug_blocklist = array('category',);

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
        add_filter('peepso_license_config', function($list) {
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

        // Translations
        add_action('plugins_loaded', function(){
            $path = str_ireplace(WP_PLUGIN_DIR, '', dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
            load_plugin_textdomain('groupso', FALSE, $path);
        });

        // Activation
        register_activation_hook(__FILE__, array(&$this, 'activate'));

        /** VERSION LOCKED hooks **/
        if(self::ready()) {
            if (!PeepSoLicense::check_license(self::PLUGIN_EDD, self::PLUGIN_SLUG)) {
                return;
            }

            if(is_admin()) {
                add_filter('peepso_report_column_title', function($title, $item, $column_name) {

                    if ('post_title' === $column_name) {
                        if (PeepSoGroupsPlugin::MODULE_ID === intval($item['rep_module_id'])) {
                            return ('<a href="' . PeepSo::get_page('activity_status') . $item['post_title'] . '/" target="_blank">' . $item['post_title'] . ' <i class="fa fa-external-link"></i></a>');
                        }
                    }
                    return ($title);
                }, 20, 3);
            }

            add_filter('peepso_filter_shortcodes', function ($list) {
                return array_merge($list, $this->shortcodes);
            });

            add_action('peepso_init', array(&$this, 'init'));

            // Owner, Manager and Moderator should be able to delete, edit  and pin/unpin posts
            add_filter('peepso_check_permissions-post_delete', array(&$this, 'check_permissions_delete_content'), 99, 4);
            add_filter('peepso_check_permissions-post_edit', array(&$this, 'check_permissions_edit_content'), 99, 4);
            add_filter('peepso_can_pin', function ($can_pin, $post_id) {

                if(!PeepSo::get_option_new('groups_pin_allow_managers')) {
                    // echo "Groups - pins not allowed";
                    return FALSE;
                }

                // If post ID is null, we are checking postbox permission
                // Extract group id from URL and decide
                if($post_id == NULL) {
                    $group_id = NULL;
                    if (PeepSoUrlSegments::get_instance()->_shortcode == 'peepso_groups') {

                        $sc = PeepSoGroupsShortcode::get_instance();
                        $group_id = $sc->group_id;
                    }
                } else {
                    $group_id = get_post_meta($post_id, 'peepso_group_id', true);
                }

                if (is_numeric($group_id)) {
                    $PeepSoGroupUser = new PeepSoGroupUser($group_id);
                    $can_pin = $PeepSoGroupUser->can('manage_content');
                }

                return $can_pin;
            }, 10, 2);

            add_filter('peepso_can_nsfw', function ($can_nsfw, $post_id) {
                $group_id = get_post_meta($post_id, 'peepso_group_id', true);

                if (!empty($group_id)) {
                    $PeepSoGroupUser = new PeepSoGroupUser($group_id);
                    $can_nsfw = $PeepSoGroupUser->can('manage_content');
                }

                return $can_nsfw;
            }, 10, 2);

            // Clean up "joined a group" notification posts
            add_filter('peepso_activity_content', function ($content) {
                global $post;
                if (self::is_join_notification($post)) {
                    $content = '';
                }

                return $content;
            }, -1, 1);

            #5814 migrate groups_categories_multiple_enabled to groups_categories_multiple_max
            add_action('init', function() {
                if(get_option('peepso_5814_migrated')) { return; }

                if(PeepSo::get_option('groups_categories_multiple_enabled',0)) {
                    $settings = PeepSoConfigSettings::get_instance();
                    $settings->set_option('groups_categories_multiple_max', 100);
                }

                update_option('peepso_5814_migrated', 1);
            });
            //add_filter('peepso_widgets', array(&$this, 'register_widgets'));
        }
    }


    public function filter_check_query($sc, $page, $url)
    {
        if(PeepSoGroupsShortcode::SHORTCODE == $page ) {
            $sc = PeepSoGroupsShortcode::get_instance();
            $sc->set_page($url);
        }
    }

    public static function get_instance()
    {
        if (NULL === self::$_instance) {
            self::$_instance = new self();
        }
        return (self::$_instance);
    }


    public function init()
    {
        // Load classes, templates and shortcoded only in backend, or in frontend
        $dir_classes = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR;
        PeepSo::add_autoload_directory($dir_classes);

        PeepSoTemplate::add_template_directory(plugin_dir_path(__FILE__));

        PeepSoGroupsShortCode::register_shortcodes();

        add_filter('peepso_activity_remove_shortcode', array(&$this, 'filter_activity_remove_shortcode'));

        // #5554 & #5556 load this always, to fix WP 5.8 widget preview
        add_filter('peepso_photos_post_clauses',		array(&$this, 'filter_photos_post_clauses'), 10, 3);
        add_filter('peepso_videos_post_clauses',		array(&$this, 'filter_videos_post_clauses'), 10, 3);

        if (is_admin()) {
            add_action('admin_init', 						array(&$this, 'peepso_check'));
            add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));
            add_filter('peepso_admin_config_tabs', 			function($tabs){
                $tabs['groups'] = array(
                    'label' => __('Groups', 'groupso'),
                    'icon' => 'https://cdn.peepso.com/icons/plugins/'.self::PLUGIN_EDD.'.svg',
                    'tab' => 'groups',
                    'description' => __('PeepSo Groups', 'groupso'),
                    'function' => 'PeepSoConfigSectionGroups',
                    'cat' => 'core',
                );

                return $tabs;
            });

            add_filter('peepso_admin_manage_tabs', function($tabs){
                $tabs['groups'] = array(
                    'label' => __('Groups', 'groupso'),
                    'icon' => 'https://cdn.peepso.com/icons/plugins/'.self::PLUGIN_EDD.'.svg',
                    'tab' => 'groups',
                    'description' => '',
                    'function' => array('PeepSogroupsAdmin', 'admin_page'),
                    'cat'   => 'core',
                );

                $tabs['group_categories'] = array(
                    'label' => __('Group categories', 'groupso'),
                    'icon' => self::ICON_CATEGORIES,
                    'tab' => 'group_categories',
                    'description' => '',
                    'function' => array('PeepSoGroupCategoriesAdmin', 'administration'),
                    'cat'   => 'core',
                );

                return $tabs;
            });

        } else {
            add_action('peepso_action_post_classes', function($id) {
                global $post;
                if (get_post_meta($post->ID, 'peepso_group_id', true)) {
                    echo " ps-post--group";
                }
            });
            $this->url_segments = PeepSoUrlSegments::get_instance();

            add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));

            // hide privacy in postbox
            add_filter('peepso_postbox_interactions', 		array(&$this, 'postbox_interactions'), 17, 2);

            // show/hide commentsbox
            add_filter('peepso_commentsbox_display',		array(&$this, 'commentsbox_display'), 10, 2);

            // Profile Segments
            add_action('peepso_profile_segment_groups', 	array(&$this, 'filter_profile_segment_groups'));
            add_filter('peepso_widget_me_community_links', 			array(&$this, 'filter_widget_me_community_links'));
            add_filter('peepso_rewrite_profile_pages', 		array(&$this, 'filter_rewrite_profile_pages'));

            // activity filters & hooks
            add_filter('peepso_post_filters', 				array(&$this, 'post_filters'), -1,1);
            add_filter('peepso_post_filters', 				array(&$this, 'post_filters_after'), 50);
            add_filter('peepso_activity_post_actions', 		array(&$this, 'modify_post_actions'),50); // priority set to last
            add_filter('peepso_activity_comment_actions', 	array(&$this, 'modify_comments_actions'),50); // priority set to last
            add_filter('peepso_activity_post_clauses', 		array(&$this, 'filter_post_clauses'), 10, 2);
            add_filter('peepso_activity_meta_query_args', 	array(&$this, 'activity_meta_query_args'), 10, 2);

            // ajax auth exceptions
            add_filter('peepso_photos_ajax_auth_exceptions', function($exceptions){
                if (isset($_GET['group_id']) &&
                    (isset($_GET['module_id']) && $_GET['module_id'] == PeepSoGroupsPlugin::MODULE_ID)
                ) {
                    $exceptions = array_merge($exceptions, array('get_user_photos', 'get_list_albums', 'get_user_photos_album'));
                }

                return $exceptions;
            });

            add_filter('peepso_videos_ajax_auth_exceptions', function($exceptions){
                if (isset($_GET['group_id']) &&
                    (isset($_GET['module_id']) && $_GET['module_id'] == PeepSoGroupsPlugin::MODULE_ID)
                ) {
                    $exceptions = array_merge($exceptions, array('get_user_videos'));
                }

                return $exceptions;
            });


            add_filter('peepso_activity_post_clauses_follow', function($following){
                global $wpdb;

                $following['groups'] = "(`pm`.`meta_value` IS NOT NULL AND gm.gm_group_id IN (SELECT gf_group_id FROM " . $wpdb->prefix . PeepSoGroupFollowers::TABLE . " WHERE gf_user_id = " . get_current_user_id() . " AND gf_follow=1))";

                return $following;
            });

            add_action('peepso_activity_after_add_post', 	array(&$this, 'after_add_post'), 10, 2);
            add_action('future_to_publish',                 array(&$this, 'future_to_publish'),10,1);
            add_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'), 10, 1);

            add_filter('peepso_activity_stream_title', 		array(&$this, 'filter_activity_stream_title'), 10, 3);
            add_filter('peepso_activity_has_privacy', 		array(&$this, 'filter_activity_has_privacy'), 10, 2);
            add_filter('peepso_photos_dir_' . self::MODULE_ID,		array(&$this, 'photos_groups_dir'));
            add_filter('peepso_photos_url_' . self::MODULE_ID,		array(&$this, 'photos_groups_url'));
            add_filter('peepso_photos_thumbs_url_' . self::MODULE_ID,		array(&$this, 'photos_groups_thumbs_url'), 10, 2);
            add_filter('peepso_post_photos_location',		array(&$this, 'post_photos_groups_location'), 10, 3);
            add_filter('peepso_post_photos_ajax_id',		function($id, $post_id) {
                $group_id = get_post_meta($post_id, 'peepso_group_id', true);

                if(!empty($group_id)) {
                    $id=$group_id;
                }

                return $id;
            },10,2);

            add_filter('peepso_post_photos_ajax_dir',		function($dir, $post_id) {
                $group_id = get_post_meta($post_id, 'peepso_group_id', true);

                if(!empty($group_id)) {
                    $dir='groups';
                }

                return $dir;
            },10,2);

            // single activity view accessible
            add_filter('peepso_access_content',  array(&$this, 'access_content'), 10, 3);

            // hide groups photos from photos widgets
            add_filter('peepso_photos_photo_click',			array(&$this, 'filter_photos_photo_click'), 10, 3);
            add_filter('peepso_photos_photo_item_click',	array(&$this, 'filter_photos_photo_item_click'), 10, 3);
            add_filter('peepso_photos_set_as_avatar',		array(&$this, 'filter_photos_photo_set_as_avatar'), 10, 3);
            add_filter('peepso_photos_set_as_cover',		array(&$this, 'filter_photos_photo_set_as_cover'), 10, 3);

            add_filter('peepso_photos_filter_owner_album',	array(&$this, 'filter_photos_owner_album'));
            add_filter('peepso_photos_album_owner_profile_url',				array(&$this, 'filter_photos_owner_profile_url'));
            add_filter('peepso_photos_filter_owner_' . self::MODULE_ID,		array(&$this, 'filter_photos_owner'));
            add_filter('peepso_photos_filter_owner_name',       array(&$this, 'filter_photos_owner_name'));

            add_filter('peepso_photos_stream_photos_album',	array(&$this, 'photos_stream_photos_album'));
            add_filter('peepso_photos_profile_photos_album',	array(&$this, 'photos_profile_photos_album'), 10, 2);

            add_filter('peepso_photos_album_url', array(&$this, 'filter_photos_album_url'));

            // hooks for create default album
            add_action('peepso_photos_setup_groups_album',	array(&$this, 'action_setup_group_album'));
            add_action('peepso_action_group_create', 	array(&$this, 'action_setup_group_album'), 10, 1);

            // change avatar & cover section
            add_action('peepso_groups_after_change_avatar', array(&$this, 'action_change_avatar'), 10, 4);
            add_action('peepso_groups_after_change_cover', 	array(&$this, 'action_change_cover'), 10, 2);
            add_filter('peepso_photos_stream_action_change_avatar', 		array(&$this, 'stream_action_change_avatar'), 10, 2);
            add_filter('peepso_photos_stream_action_change_cover', 			array(&$this, 'stream_action_change_cover'), 10, 2);

            // photos item template
            add_filter('peepso_photos_ajax_template_item_album', array(&$this, 'ajax_template_item_album'), 10, 1);
            add_filter('peepso_photos_create_album_privacy_hide', array(&$this, 'create_album_privacy_hide'), 10, 1);
            add_filter('peepso_photos_ajax_create_album_privacy', array(&$this, 'ajax_create_album_privacy'), 10, 1);

            // upload
            add_filter('peepso_photos_stream_action_photo_album', array(&$this, 'photos_stream_action_photo_album'), 10, 2);


            // videos
            add_filter('peepso_videos_filter_owner_name',       array(&$this, 'filter_videos_owner_name'));
            add_filter('peepso_videos_filter_owner_' . self::MODULE_ID,		array(&$this, 'filter_videos_owner'));

            // notifications
            add_action('peepso_action_group_rename', 					array(&$this, 'action_group_rename'), 10, 2);
            add_action('peepso_action_group_privacy_change', 			array(&$this, 'action_group_privacy_change'), 10, 2);

            add_action('peepso_action_group_user_join', 				array(&$this, 'action_group_user_join'), 10, 2);
            add_action('peepso_action_group_user_join_request_accept', 	array(&$this, 'action_group_user_join'), 10, 2);
            add_action('peepso_action_group_add', 				        array(&$this, 'action_group_user_join'), 10, 2);

            add_action('peepso_action_group_user_join_request_send', 	array(&$this, 'action_group_user_join_request_send'), 10, 1);
            add_action('peepso_action_group_user_join_request_accept', array(&$this, 'action_group_user_join_request_accept'), 10, 2);
            add_action('peepso_action_group_user_delete', 				array(&$this, 'action_group_user_delete'), 10, 2);
            add_action('peepso_action_group_user_invitation_accept', 	array(&$this, 'action_group_user_invitation_accept'), 10, 1);


            // extra filter to prevent pinned group post from console
            #add_action('peepso_post_can_be_pinned', array(&$this, 'filter_post_can_be_pinned'));

            // modify notification link
            add_action('peepso_profile_notification_link', array(&$this, 'filter_profile_notification_link'), 10, 2);

            // inject group header to single activity view
            add_action('peepso_activity_single_override_header', array(&$this, 'action_activity_single_override_header'));

            // taggable filter
            add_filter('peepso_taggable', array(&$this, 'filter_taggable'), 10, 2);

            // notifications
            add_filter('peepso_notifications_activity_type', array(&$this, 'notifications_activity_type'), 20, 3);

            // Notify group followers about new posts
            add_action('peepso_groups_new_post', function($group_id, $post_id) {


                $PeepSoNotifications = new PeepSoNotifications();
                $PeepSoGroup = new PeepSoGroup($group_id);
                $post = get_post($post_id);

                $from_first_name = PeepSoUser::get_instance($post->post_author)->get_firstname();
                $group_name = $PeepSoGroup->get('name');


                $i18n = __('posted in %s', 'groupso');
                $message = 'posted in %s';
                $args = [
                        'groupso',

                        $group_name
                ];

                // on-site notifications
                $PeepSoGroupFollowers = new PeepSoGroupFollowers($group_id, FALSE, NULL, 1);
                $followers = $PeepSoGroupFollowers->get_followers();

                $block = new PeepSoBlockUsers();

                if ($post->post_status != 'pending') {
                    foreach($followers as $follower_id) {
                        if($follower_id == $post->post_author || $follower_id == $post->post_author || $block->is_user_blocking($follower_id, $post->post_author)) { continue; }

                        $PeepSoNotifications->add_notification_new($post->post_author, $follower_id, $message, $args,'groups_new_post', self::MODULE_ID, $post_id);
                    }
                }


                // email notifications
                $PeepSoGroupFollowers = new PeepSoGroupFollowers($group_id, FALSE, NULL, NULL, 1);
                $followers = $PeepSoGroupFollowers->get_followers();

                $data = array(
                    'permalink' => PeepSo::get_page('activity_status') . $post->post_title,
                    'fromfirstname' => $from_first_name,
                    'groupname' => $group_name,
                );

                if ($post->post_status != 'pending') {

                    $from_first_name = PeepSoUser::get_instance($post->post_author)->get_firstname();
                    $group_name = $PeepSoGroup->get('name');


                    $i18n = __('%s posted in %s', 'groupso');
                    $message = '%s posted in %s';
                    $args = [
                        'groupso',
                        $from_first_name,
                        $group_name
                    ];

                    foreach($followers as $follower_id) {
                        if($follower_id == $post->post_author || $block->is_user_blocking($follower_id, $post->post_author)) { continue; }

                        //check if the user has the right role
                        $userF = PeepSoUser::get_instance($follower_id);
                        $role = $userF->get_user_role();
                        $sendNotificationEmail = in_array($role,array('member','moderator','admin'));
                        $sendNotificationEmail = apply_filters('peepso_groups_follower_send_notification_email', $sendNotificationEmail, $userF);
                        if ($sendNotificationEmail) {
                            PeepSoMailQueue::add_notification_new( $follower_id, $data, $message, $args, 'group_new_post', 'group_new_post', self::MODULE_ID );
                        }
                    }
                }

            },10,2);

            // Notify Admins about new group
            add_action('peepso_action_group_create', function($group_id) {
                if(!PeepSo::get_option('groups_create_notify_admin',0)) {
                    return;
                }

                // send Administrators an email
                $args = array(
                    'role' => 'administrator',
                );

                $user_query = new WP_User_Query($args);
                $users = $user_query->get_results();

                $adm_email = PeepSo::get_notification_emails();

                $is_admin_email = FALSE;
                if (count($users) > 0) {
                    $PeepSoGroup = new PeepSoGroup($group_id);

                    $data = array(
                        'fromfirstname' => PeepSoUser::get_instance()->get_fullname(),
                        'groupname'     => $PeepSoGroup->get('name'),
                        'permalink'     => $PeepSoGroup->get('url'),
                    );

                    foreach ($users as $user) {
                        $email = $user->data->user_email;

                        PeepSoMailQueue::add_message($user->ID, $data, __('{sitename} - New Group Created', 'groupso'), 'group_created', 'group_created');
                    }
                }

            });

            // opengraph
            add_filter('peepso_filter_check_opengraph', array(&$this, 'filter_check_opengraph'));


            // Hook into PeepSo routing, enables single item view (eg /groups/?2137/)
            add_filter('peepso_check_query', array(&$this, 'filter_check_query'), 10, 3);
            add_filter('peepso_profile_alerts', function($alerts) {
                if(get_current_user_id()) {
                    global $wpdb;

                    $groups = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}".PeepSoGroupFollowers::TABLE." WHERE `gf_user_id`=".get_current_user_id());

                    $result = array();
                    if(count($groups)) {
                        foreach($groups as $group) {
                            $PeepSoGroup = new PeepSoGroup($group->gf_group_id);

                            if(!$PeepSoGroup->id) { continue; }
                            $result[$PeepSoGroup->get('name')] = array('id'=>$group->gf_group_id, 'url'=>$PeepSoGroup->get_url(), 'email'=>$group->gf_email, 'onsite'=>$group->gf_notify);
                        }
                    }

                    if(count($result)) {
                        uksort($result, "strnatcasecmp");

                        $items = array();
                        foreach($result as $groupname => $pref) {
                            $items[] = array(
                                'label' => $groupname,
                                'setting' => 'group_' . $pref['id'],
                                'loading' => true,
                            );
                        }

                        $alerts['groups'] = array(
                            'title' => __('Group subscriptions','groupso'),
                            'items' => $items
                        );
                    }
                }

                return $alerts;
            }, 20);

            add_filter('peepso_get_notification_value', function($value, $field) {
                if(get_current_user_id() && strstr($field, 'group_')) {
                    $field = explode('_', $field);
                    $group_id = $field[1];
                    $key = $field[2];

                    $PeepSoGroupFollower = new PeepSoGroupFollower($group_id);

                    if('notification' == $key) {
                        $value = (int) $PeepSoGroupFollower->get('notify');
                    } elseif ('email' == $key) {
                        $value = (int) $PeepSoGroupFollower->get('email');
                    }
                }

                return $value;
            }, 10, 2);

            add_filter('peepso_save_notifications', function($field) {

                if(strstr( $field, 'group_')) {
                    $field=explode('_', $field);

                    $group_id = $field[1];
                    $key = $field[2];

                    $PeepSoInput = new PeepSoInput();
                    $value =$PeepSoInput->int('value');
                    $PeepSoGroupFollower = new PeepSoGroupFollower($group_id);

                    if('notification' == $key) {
                        $PeepSoGroupFollower->set('notify', $value);
                    }

                    if('email' == $key) {
                        $PeepSoGroupFollower->set('email', $value);
                    }

                    return array('success'=>1);
                }

                return $field;
            } );

            wp_enqueue_script('peepso-groups-activitystream',
                PeepSo::get_asset('js/activitystream.min.js', __FILE__),
                array('peepso'), self::PLUGIN_VERSION, TRUE);

            if (PeepSo::get_option('disable_questionmark_urls', 0) === 1 && !wp_doing_ajax() && strpos($_SERVER['REQUEST_URI'], 'ajax' ) === FALSE && strpos($_SERVER['REQUEST_URI'], PeepSo::get_option('page_groups') . '/') !== FALSE) {
                add_filter('request', function($q) {
                    if (isset($q['attachment'])) {
                        $q['pagename'] = $q['page'] = $q['attachment'];
                        unset($q['attachment']);
                    }

                    return $q;
                });
            }

            add_filter('peepso_can_disable_comments', function($allow, $post_id) {
                $group_id = get_post_meta($post_id, 'peepso_group_id', true);
                if ($group_id) {
                    $group_user = new PeepSoGroupUser($group_id, get_current_user_id());
                    $allow = $group_user->can('manage_content');
                }
                return $allow;
            }, 99, 2);
        }

        // Emails
        add_filter('peepso_config_email_messages', function($emails) {

            $emails['email_group_new_post'] = array(
                'title' => __('New Post In Group', 'groupso'),
                'description' => __('Notify users about new posts in groups.', 'groupso')
            );

            $emails['email_group_created'] = array(
                'title' => __('New Group Created', 'groupso'),
                'description' => __('Notify Administrators when a new new group is created', 'groupso')
            );

            if (class_exists('PeepSoSharePhotos')){
                $emails['email_user_comment_group_avatar'] = array(
                    'title' => __('User Comment Group Avatar', 'groupso'),
                    'description' => __('This will be sent to a group owner when another user comments on the avatar', 'groupso')
                );

                $emails['email_user_comment_group_cover'] = array(
                    'title' => __('User Comment Group Cover', 'groupso'),
                    'description' => __('This will be sent to a group owner when another user comments on the cover', 'groupso')
                );
            }

            return ($emails);
        });

        add_filter('peepso_config_email_messages_defaults',  function( $emails ) {
            require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '/install' . DIRECTORY_SEPARATOR . 'activate.php');
            $install = new PeepSoGroupsInstall();
            $defaults = $install->get_email_contents();
            return array_merge($emails, $defaults);
        });

        // PeepSo navigation
        add_filter('peepso_navigation', 				array(&$this, 'filter_peepso_navigation'));
        add_filter('peepso_navigation_profile', array(&$this, 'filter_peepso_navigation_profile'));

        $this->url_segments = PeepSoUrlSegments::get_instance();

        if(class_exists('PeepSoSharePhotos')){
            $this->photo_group_system_album = array(
                array(
                    'albumname' => __('Group Avatars', 'groupso'),
                    'albumname_acc' => PeepSo::ACCESS_PUBLIC,
                    'is_system'=> self::MODULE_ID . PeepSoSharePhotos::ALBUM_AVATARS),
                array(
                    'albumname' => __('Group Covers', 'groupso'),
                    'albumname_acc' => PeepSo::ACCESS_PUBLIC,
                    'is_system'=> self::MODULE_ID . PeepSoSharePhotos::ALBUM_COVERS),
                array(
                    'albumname' => __('Group Stream Photos', 'groupso'),
                    'albumname_acc' => PeepSo::ACCESS_PUBLIC,
                    'is_system'=> self::MODULE_ID . PeepSoSharePhotos::ALBUM_STREAM));
        }

        // Compare last version stored in transient with current version
        if( $this::PLUGIN_VERSION.$this::PLUGIN_RELEASE != PeepSo3_MayFly::get($mayfly = 'peepso_'.$this::PLUGIN_SLUG.'_version')) {
            PeepSo3_Mayfly::set($mayfly, $this::PLUGIN_VERSION.$this::PLUGIN_RELEASE);
            $this->activate();
        }
        if(class_exists('PeepSoMaintenanceFactory') && class_exists('PeepSoMaintenanceGroups')) {
            new PeepSoMaintenanceGroups();
        }

        $this->input = new PeepSoInput();
    }


    function check_permissions_edit_content($allow, $owner, $author, $allow_logged_out) {
        global $post;

        // avoid overriding the global post var
        $peepso_post = $post;

        // if the object is a comment, find the root post
        $i = 0;
        $act = new PeepSoActivity();
        while('peepso-comment' == $peepso_post->post_type) {
            if (!empty($peepso_post->act_comment_object_id)) {
                $peepso_post = get_post($peepso_post->act_comment_object_id);
            } else {
                $activity_data = $act->get_activity_data($peepso_post->ID);
                $peepso_post = $act->get_activity_post($activity_data->act_id);
            }
            if($i++ > 10) { return FALSE; } // infinite loop precaution
        }

        $group_id = get_post_meta($peepso_post->ID, 'peepso_group_id', true);
        if(!empty($group_id)) {
            $PeepSoGroupUser = new PeepSoGroupUser($group_id);
            $allow = $PeepSoGroupUser->can('edit_content');
        }

        return $allow;
    }
    function check_permissions_delete_content($allow, $owner, $author, $allow_logged_out) {
        global $post;

        // avoid overriding the global post var
        $peepso_post = $post;

        // if the object is a comment, find the root post
        $i = 0;
        $act = new PeepSoActivity();
        while('peepso-comment' == $peepso_post->post_type) {
            if (!empty($peepso_post->act_comment_object_id)) {
                $peepso_post = get_post($peepso_post->act_comment_object_id);
            } else {
                $activity_data = $act->get_activity_data($peepso_post->ID);
                $peepso_post = $act->get_activity_post($activity_data->act_id);
            }
            if($i++ > 10) { return FALSE; } // infinite loop precaution
        }

        $group_id = get_post_meta($peepso_post->ID, 'peepso_group_id', true);
        if(!empty($group_id)) {
            $PeepSoGroupUser = new PeepSoGroupUser($group_id);
            $allow = $PeepSoGroupUser->can('manage_content');
        }

        return $allow;
    }

    /**
     * This function removes privacy dropdown on the post box
     * @param array $interactions is the formated html code that get inserted in the postbox
     * @param array $params
     */
    public function postbox_interactions($interactions, $params = array())
    {
        $is_group_view = FALSE;
        $category_id = FALSE;

        // Group page or group category
        if (PeepSoUrlSegments::get_instance()->_shortcode == 'peepso_groups') {
            $PeepSoGroupsShortcode = PeepSoGroupsShortcode::get_instance();

            $is_group_view = $PeepSoGroupsShortcode->group_id ? TRUE : FALSE;
            $category_id = $PeepSoGroupsShortcode->group_category_id ? $PeepSoGroupsShortcode->group_category_id : FALSE;
        }


        // Do not show privacy and schedule postbox dropdown on the group page.
        if ($category_id || $is_group_view) {
            unset($interactions['privacy']);
            //unset($interactions['schedule']);
        }

        // Show "post straight to group" postbox dropdown on the frontpage and own's profile page.
        if ( !$is_group_view && ( ! isset($params['is_current_user']) ) || ( isset($params['is_current_user']) && $params['is_current_user'] === TRUE )) {
            $interactions['groups'] = array(
                'icon' => 'gcis gci-users',
                'icon_html' => '',
                'id' => 'group-tab',
                'class' => 'ps-postbox__menu-item ps-postbox__menu-item--group',
                'click' => 'return;',
                'label' => '',
                'title' => __('Post to', 'groupso'),
                'extra' => PeepSoTemplate::exec_template('groups', 'postbox-interaction', array('category_id'=>$category_id), true),
            );
        }

        return ($interactions);
    }

    /**
     * This function commentsbox if groups is unpublished
     * @param array $interactions is the formated html code that get inserted in the postbox
     * @param array $params
     */
    public function commentsbox_display($display, $post_id = NULL)
    {
        $group_id = get_post_meta($post_id, 'peepso_group_id', true);
        if(!empty($group_id)) {
            // disable commentsbox
            $PeepSoGroupUser = new PeepSoGroupUser($group_id);
            if(!$PeepSoGroupUser->can('post_interact') && !$PeepSoGroupUser->can('post_comments_non_members')) {
                $display = FALSE;
            }
        }

        return ($display);
    }

    /*
     * Widgets
     */

    public function register_widgets($widgets)
    {
        // register widgets
        // @TODO that's too hacky - why doesn't autoload work?
        foreach (scandir($widget_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'widgets' . DIRECTORY_SEPARATOR) as $widget) {
            if (strlen($widget)>=5) require_once($widget_dir . $widget);
        }
        return array_merge($widgets, $this->widgets);
    }

    /*
     * PeepSo navigation
     */

    public function filter_peepso_navigation($navigation)
    {
        $user = PeepSoUser::get_instance(get_current_user_id());

        $navigation['groups'] = array(
            'href' => PeepSo::get_page('groups'),
            'label' => _x('Groups', 'Community link', 'groupso'),
            'icon'  => 'gcis gci-users',

            'primary'           => TRUE,
            'secondary'         => FALSE,
            'mobile-primary'    => TRUE,
            'mobile-secondary'  => FALSE,
            'widget'            => TRUE,
        );

        return ($navigation);
    }

    /*
     * PeepSo profiles
     */

    /**
     * Profile Segments - add link
     * @param $links
     * @return mixed
     */
    public function filter_peepso_navigation_profile($links)
    {
        $links['groups'] = array(
            'label'=> _x('Groups', 'Profile link', 'groupso'),
            'href' => 'groups',
            'icon' => 'gcis gci-users'
        );

        return $links;
    }

    /*
     * Add links to the profile widget community section
     */
    public function filter_widget_me_community_links($links)
    {
        $links[3][] = array(
            'href' => PeepSo::get_page('groups'),
            'title' => __('Groups', 'groupso'),
            'icon' => 'gcis gci-users',
        );

        ksort($links);
        return $links;
    }

    /**
     * Profile Segment - adjust the title
     * @param $title
     * @return mixed
     */
    public function filter_page_title_profile_segment( $title )
    {
        if( 'groups' === $title['profile_segment']) {
            $title['newtitle'] = $title['title'] . " - ". __('groups', 'groupso');
        }

        return $title;
    }

    public function peepso_rewrite_profile_pages($pages)
    {
        return array_merge($pages, array('groups'));
    }

    /**
     * Render groups in user profile
     */
    public function filter_profile_segment_groups()
    {
        $pro = PeepSoProfileShortcode::get_instance();
        $this->view_user_id = PeepSoUrlSegments::get_view_id($pro->get_view_user_id());

        wp_enqueue_style('groupso');
        wp_enqueue_script('groupso');

        $this->enqueue_scripts();

        echo PeepSoTemplate::exec_template('groups', 'profile-groups', array('view_user_id' => $this->view_user_id), TRUE);
    }

    /* * * * PeepSo Activity Stream * * * */

    /**
     * todo:docblock
     */
    public function filter_photos_photo_click($click, $photo, $params = array())
    {
        $group_id = get_post_meta($photo->pho_post_id, 'peepso_group_id', true);

        if(intval($photo->pho_module_id) == self::MODULE_ID && !empty($group_id)) {
            $params = empty($params) ? array() : $params;
            $params_group = array_merge($params, array('module_id' => self::MODULE_ID, 'group_id' => $group_id ));
            $click = "return ps_comments.open('" . $photo->pho_id . "', 'photo', null, " . str_replace('"', "'", json_encode( $params_group )) . ');';
        }

        return $click;
    }

    /**
     * todo:docblock
     */
    public function filter_photos_photo_item_click($click, $photo, $params = array())
    {
        $group_id = get_post_meta($photo->pho_post_id, 'peepso_group_id', true);

        if(intval($photo->pho_module_id) == self::MODULE_ID && !empty($group_id)) {
            $params = empty($params) ? array() : $params;
            $params_group = array_merge($params, array( 'module_id' => self::MODULE_ID, 'group_id' => $group_id ));
            $click = "return ps_comments.open('" . $photo->pho_id . "', 'photo', null, " . str_replace('"', "'", json_encode( $params_group )) . '); return false;';
        }

        return $click;
    }

    /**
     * todo:docblock
     */
    public function filter_photos_photo_set_as_avatar($click, $photo_id, $params = array())
    {
        $photo_model = new PeepSoPhotosModel();
        $photo = $photo_model->get_photo($photo_id);
        if(NULL !== $photo){
            $group_id = get_post_meta($photo->pho_post_id, 'peepso_group_id', true);

            if(intval($photo->pho_module_id) == self::MODULE_ID && !empty($group_id)) {

                $req = '';
                if(count($params) > 0) {
                    $req = array();
                    foreach ($params as $key => $value) {
                        $req[] = $key . ': \'' . $value . '\'';
                    }
                    $req = implode(',', $req) . ',';
                }

                $click = 'peepso.photos.set_as_avatar({' . $req . ' module_id: '. self::MODULE_ID.', group_id: ' . $group_id . '});';
            }
        }

        return $click;
    }

    /**
     * todo:docblock
     */
    public function filter_photos_photo_set_as_cover($click, $photo_id, $params = array())
    {
        $photo_model = new PeepSoPhotosModel();
        $photo = $photo_model->get_photo($photo_id);
        if(NULL !== $photo){
            $group_id = get_post_meta($photo->pho_post_id, 'peepso_group_id', true);

            if(intval($photo->pho_module_id) == self::MODULE_ID && !empty($group_id)) {

                $req = '';
                if(count($params) > 0) {
                    $req = array();
                    foreach ($params as $key => $value) {
                        $req[] = $key . ': \'' . $value . '\'';
                    }
                    $req = implode(',', $req) . ',';
                }

                $click = 'peepso.photos.set_as_cover({' . $req . ' module_id: '. self::MODULE_ID.', group_id: ' . $group_id . '});';
            }
        }

        return $click;
    }

    /**
     * Modify the clauses to filter posts
     * @param  array $clauses
     * @param  int $user_id The owner of the activity stream
     * @return array
     */
    public function filter_photos_post_clauses($clauses, $module_id, $widgets)
    {
        global $wpdb;

        if($module_id == self::MODULE_ID) {
            // Filter for groups joined
            $clauses['join'] .= ' LEFT JOIN `' . $wpdb->prefix . PeepSoPhotosAlbumModel::TABLE . '` `am` ON ' .
                ' `' . $wpdb->prefix . PeepSoPhotosModel::TABLE . '`.`pho_album_id` = `am`.`pho_album_id` ' ;
            /*$clauses['join'] .= ' LEFT JOIN `' . $wpdb->postmeta  . '` `pm` ON ' .
                                    ' `am`.`pho_owner_id` = `pm`.`post_id` AND `pm`.`meta_key` = \'peepso_group_id\' ' ;*/

            $group_id = $this->input->int('group_id', 0);
            if(0 !== $group_id) {
                $clauses['where'] .= " AND (`am`.`pho_owner_id` = '" . $group_id . "') ";
            }
        }

        if($widgets) {
            // Filter for groups joined
            $clauses['join'] .= ' LEFT JOIN `' . $wpdb->postmeta  . '` `pmeta` ON ' .
                ' `' . $wpdb->posts . '`.`ID` = `pmeta`.`post_id` AND `pmeta`.`meta_key` = \'peepso_group_id\' ' ;

            $clauses['where'] .= " AND (`pmeta`.`meta_value` IS NULL) ";
        }

        return $clauses;
    }

    /**
     * Add extra filter to prevent pinned group post from console
     * @param array $post
     * @return array $post
     */
    public function filter_post_can_be_pinned($post) {
        return $post;
        $group_id = get_post_meta($post->ID, 'peepso_group_id', true);

        if (!empty($group_id)) {
            $post->can_be_pinned = 0;
        } else {
            $post->can_be_pinned = 1;
        }
        return $post;
    }

    /**
     * Modify link notification
     * @param array $link
     * @param array $note_data
     * @return string $link
     */
    public function filter_profile_notification_link($link, $note_data)
    {
        $not_types = array(
            'groups_rename',
            'groups_publish',
            'groups_unpublish',

            'groups_user_join',
            'groups_user_join_request_send',
            'groups_user_join_request_accept',

            'groups_user_invitation_send',
            'groups_user_invitation_accept',

            'groups_privacy_change',
        );

        // @todo delete legacy not types in January after the old notifications are out of the system
        $legacy_not_types = array('join_group', 'rename_group', 'group_invited', 'group_accepted', 'publish_group', 'unpublish_group');
        $not_types = array_merge($not_types, $legacy_not_types);

        $not_type = $note_data['not_type'];

        if (in_array($not_type, $not_types)) {
            $group = new PeepSoGroup($note_data['not_external_id']);
            $link = $group->get_url(FALSE);

            if('groups_user_join_request_send' == $not_type) {
                $link.='members/pending';
            }
        }
        return $link;
    }

    /**
     * modify onclick handler delete post for album type post
     * @param array $options
     * @return array $options
     */
    public function post_filters($options) {
        $post = $options['post'];
        $options_acts = $options['acts'];

        $group_id = get_post_meta($post->ID, 'peepso_group_id', true);

        if(!empty($group_id)) {
            // if type is photo album, show latest photos as first ones.
            if (isset($options_acts['delete'])) {
                $options_acts['delete']['click'] = 'return activity.action_delete(' . $post->ID . ', {module_id: '. self::MODULE_ID.', group_id: ' . $group_id . '});';
            }

            unset($options_acts['repost']);
        }

        if(self::is_join_notification($post)) {

            // disable "edit"
            if (isset($options_acts['edit'])) {
                unset($options_acts['edit']);
            }
        }

        $options['acts'] = $options_acts;

        return $options;
    }

    /**
     * modify onclick handler delete post for album type post
     * @param array $options
     * @return array $options
     */
    public function post_filters_after($options) {
        return $this->modify_post_actions($options);
    }

    /**
     * Change act_id on repost button act_id to follow parent's act_id.
     * @param array $options The default options per post
     * @return  array
     */
    public function modify_post_actions($options)
    {
        $post = $options['post'];

        $group_id = get_post_meta($post->ID, 'peepso_group_id', true);

        // fix photos post ID in modal comments
        // wrong post ID information
        if(class_exists('PeepSoSharePhotos')){
            $_photos_model = new PeepSoPhotosModel();
            if(intval($post->act_module_id) == PeepSoSharePhotos::MODULE_ID && empty($group_id)) {
                $photo = $_photos_model->get_photo($post->ID);
                if(isset($photo->pho_module_id) && $photo->pho_module_id == self::MODULE_ID) {
                    $group_id = get_post_meta($photo->pho_post_id, 'peepso_group_id', true);
                }
            }
        }

        // if type is photo album, show latest photos as first ones.
        if(!empty($group_id)) {

            if(isset($options['acts']['delete']['click'])) {
                // modify delete script
                $delete_script = str_replace( ');', ', {module_id: '. self::MODULE_ID.', group_id: ' . $group_id . '});', $options['acts']['delete']['click']);

                $options['acts']['delete']['click'] = $delete_script;
            }

            // disable repost function for group post
            unset($options['acts']['repost']);

            // disable like button when group is unpublished
            $PeepSoGroupUser = new PeepSoGroupUser($group_id);
            if(!$PeepSoGroupUser->can('post_interact') && !$PeepSoGroupUser->can('post_likes_non_members')) {
                // disable repost function for group post
                unset($options['acts']['like']);
            }
        }

        return ($options);
    }

    /**
     * Change act_id on repost button act_id to follow parent's act_id.
     * @param array $options The default options per post
     * @return  array
     */
    public function modify_comments_actions($options)
    {
        global $post;

        $parent_post = get_post($post->act_comment_object_id);

        if (!$parent_post) {
            return ($options);
        }

        if($parent_post->post_type === PeepSoActivityStream::CPT_COMMENT) {
            $group_id = get_post_meta($parent_post->act_comment_object_id, 'peepso_group_id', true);
        } else {
            $group_id = get_post_meta($post->act_comment_object_id, 'peepso_group_id', true);
        }

        // if type is photo album, show latest photos as first ones.
        if(!empty($group_id)) {
            // disable like button when group is unpublished
            $PeepSoGroupUser = new PeepSoGroupUser($group_id);
            if(!$PeepSoGroupUser->can('post_interact')) {
                if (!$PeepSoGroupUser->can('post_likes_non_members')) {
                    unset($options['like']);
                }
                if (!$PeepSoGroupUser->can('post_comments_non_members')) {
                    unset($options['reply']);
                }
            }
        }

        return ($options);
    }

    /**
     * Modify the clauses to filter posts
     * @param  array $clauses
     * @param  int $user_id The owner of the activity stream
     * @return array
     */

    public function filter_post_clauses($clauses, $user_id = NULL) {


        if (!is_null($user_id) && (strpos($clauses['where'], PeepSoActivityStream::CPT_COMMENT) === false)) {
            global $wpdb;

            // Filter for groups joined
            $clauses['join'] .= ' LEFT JOIN `' . $wpdb->postmeta  . '` `pm` ON ' .
                ' `' . $wpdb->posts . '`.`ID` = `pm`.`post_id` AND `pm`.`meta_key` = \'peepso_group_id\' ' .
                ' LEFT JOIN `' . $wpdb->postmeta  . '` `priv` ON ' .
                ' `priv`.`post_id` = `pm`.`meta_value` AND `priv`.`meta_key` = \'peepso_group_privacy\' ' .
                ' LEFT JOIN `' . $wpdb->prefix . PeepSoGroupUsers::TABLE  . '` `gm` ON ' .
                ' `pm`.`meta_value` = `gm`.`gm_group_id` AND gm.gm_user_id = ' . get_current_user_id(). ' ' .
                ' LEFT JOIN `' . $wpdb->posts . '` `grp` ON ' .
                ' `pm`.`meta_value` = `grp`.`ID` ';

            #$stream_id  = $this->input->val ('stream_id',    'core_community');
            $group_id   = $this->input->int('group_id',     0);
            $group_category_id   = $this->input->int('group_category_id',     53);
            #$module_id  = $this->input->int('module_id',    0);
            #$post_id    = $this->input->int('post_id',      0);
            #$profile_id = $this->input->int('uid',          0);




            // GROUP VIEW
            // SQL safe, not used in query
            if( in_array($this->input->value('context', '', FALSE), array('group') ) ) {
                $clauses['where'] .= $wpdb->prepare(" AND `pm`.`meta_value` IS NOT NULL AND `grp`.`ID` = %d ", $group_id);
            }

            // GROUP CATEGORY VIEW
            // SQL safe, not used in query
            if( in_array($this->input->value('context', '', FALSE), array('group-category') ) ) {
                $group_ids = PeepSoGroupCategoriesGroups::get_group_ids_for_category($group_category_id);

                $clauses['where'] .= " AND `pm`.`meta_value` IS NOT NULL AND `grp`.`ID` IN (" . implode(',',$group_ids) . ")";
            }

            // HIDE SECRET AND CLOSED FROM NON-MEMBERS NON-ADMIN
            if(!PeepSo::is_admin()) {

                $clauses['where'] .= "
                AND (
                        `pm`.`meta_value` IS NULL
                        OR
                        (
                            priv.meta_value = 0
                            OR
                            (
                                priv.meta_value IN(1,2)
                                AND
                                substr(gm.gm_user_status, 1, 6) = 'member'
                            )
                        )
                   )
                ";
            }


        }

        return $clauses;
    }

    public function activity_meta_query_args($args, $module_id)
    {
        return $args;
        if($module_id == PeepSoGroupsPlugin::MODULE_ID) {

            if(!isset($args['meta_query'])) {
                $args['meta_query'] = array();
            }

            array_push($args['meta_query'],
                array(
                    'compare' => 'EXISTS',
                    'key' => 'peepso_group_id',
                )
            );
        }

        return $args;
    }

    /**
     * This function add information after new activity on group
     * @param int $post_id The post ID
     * @param int $act_id The activity ID
     */
    public function after_add_post($post_id, $act_id)
    {
        $group_id = $this->input->int('group_id', 0);
        $module_id = $this->input->int('module_id', 0);

        if((0 !== $group_id || 0 !== $module_id)
            && self::MODULE_ID == $module_id) {

            $post = get_post($post_id);
            if (!is_object($post) || !isset($post->post_type) || $post->post_type != 'peepso-post') {
                return;
            }

            $files = $this->input->value('files', array(), FALSE);

            // SQL safe, not used in queries
            if (count($files) > 0 && 'photo' === $this->input->value('type','',FALSE)) {
                // migrate from activate function,
                // setup album before uploading avatar
                $this->action_setup_group_album($group_id);
            }

            update_post_meta($post_id, 'peepso_group_id', $group_id);

            if($post->post_status == 'future') {
                do_action('peepso_groups_new_scheduled_post', $group_id, $post_id);
            } else {
                do_action('peepso_groups_new_post', $group_id, $post_id);
            }

        }
    }

    public function future_to_publish(WP_Post $post) {
        $post_id = $post->ID;

        $group_id = get_post_meta($post_id,'peepso_group_id',TRUE);

        if(strlen($group_id) & is_numeric($group_id)) {
            do_action('peepso_groups_new_post', $group_id, $post_id);
        }
    }

    /**
     * PeepSo stream action title
     * @param $title default stream action title
     * @param $post global post variable
     */
    public function filter_activity_stream_title($title, $post, $action)
    {
        $PeepSoActivityShortcode = PeepSoActivityShortcode::get_instance();
        if( $PeepSoActivityShortcode->is_permalink_page()) {
            return $title;
        }

        $group_id = '';
        if(is_null($post->act_description)) {
            $group_id = get_post_meta($post->ID, 'peepso_group_id', true);
        }

        // fix photos post ID in modal comments
        // wrong post ID information
        // if(class_exists('PeepSoSharePhotos')){
        // 	$_photos_model = new PeepSoPhotosModel();
        // 	if(intval($post->act_module_id) == PeepSoSharePhotos::MODULE_ID && empty($group_id)) {
        // 		$photo = $_photos_model->get_photo($post->ID);
        // 		if(isset($photo->pho_module_id) && $photo->pho_module_id == self::MODULE_ID) {
        // 			$group_id = get_post_meta($photo->pho_post_id, 'peepso_group_id', true);
        // 		}
        // 	}
        // }

        $module_id = $this->input->int('module_id', 0);
        $group_id = $this->input->int('group_id', $group_id);
        $force_as_group_post = $this->input->int('force_as_group_post', 0);

        $is_join_notification = self::is_join_notification($post);

        // group post outside of group stream
        if(!empty($group_id) && (trim(strval(PeepSoUrlSegments::get_instance()->_shortcode)) != 'peepso_groups') && ($module_id !== self::MODULE_ID || $force_as_group_post)) {

            $author = PeepSoUser::get_instance($post->post_author);
            $group = new PeepSoGroup($group_id);

            if(FALSE !== $group) {
                ob_start();
                do_action('peepso_action_render_user_name_before', $author->get_id());
                $before_fullname = ob_get_clean();

                ob_start();
                do_action('peepso_action_render_user_name_after', $author->get_id());
                $after_fullname = ob_get_clean();

                $PeepSoGroupUser = new PeepSoGroupUser($group_id);


                $meta = "";
                if(get_current_user_id() && PeepSo::get_option('groups_meta_in_stream')) {
                    if ($PeepSoGroupUser->is_member) {
                        $meta .= sprintf(__('You are the group %s', 'groupso'), $PeepSoGroupUser->role_l8n);
                    } else {
                        $meta .= __('You are not a member of this group');
                    }

                    $PeepSoGroupFollower = new PeepSoGroupFollower($group_id);

                    if ($PeepSoGroupFollower->follow) {
                        $meta .= "\n" . __('Follow', 'groupso');
                    }

                    if ($PeepSoGroupFollower->notify) {
                        $meta .= "\n" . __('Be notified', 'groupso');
                    }

                    if ($PeepSoGroupFollower->email) {
                        $meta .= "\n" . __('Receive emails', 'groupso');
                    }
                }

                if($is_join_notification) {
                    $action_title = PeepSo::get_option('groups_join_post_action_text_other', __('joined a group','groupso'));
                    if (empty($action_title)) {
                        $action_title = __('joined a group','groupso');
                    }
                    $title = sprintf(
                        '<a class="ps-post__author" href="%s" data-hover-card="%d">%s</a> '. $action_title . ' <a class="ps-post__subtitle" href="%s" title="%s"><i class="gcis gci-users"></i>%s</a><span class="ps-post__title-desc ps-stream-action-title">%s</span> ',
                        $author->get_profileurl(), $author->get_id(), $before_fullname . $author->get_fullname() . $after_fullname,
                        $group->get_url(), $meta, $group->get('name'), $action
                    );
                } else {


                    $title = sprintf(
                        '<a class="ps-post__author" href="%s" data-hover-card="%d">%s</a><i class="gcis gci-angle-right"></i>'

                        .'<a class="ps-avatar" href="%s" title="%s"><img src="%s"/></a>'
                        .'<i class="gcis gci-users ps-post-author-group-indicator"></i><a class="ps-post__subtitle" href="%s" title="%s">%s</a><span class="ps-post__title-desc ps-stream-action-title">%s</span> ',
                        $author->get_profileurl(), $author->get_id(), $before_fullname . $author->get_fullname() . $after_fullname,
                        $group->get_url(), $meta, $group->get_avatar_url_full(),
                        $group->get_url(), $meta, $group->get('name'), $action
                    );
                }
            }
        }

        // group post inside stream
        if(!empty($group_id) && (trim(strval(PeepSoUrlSegments::get_instance()->_shortcode)) == 'peepso_groups') || ($module_id === self::MODULE_ID)) {
            if($is_join_notification) {
                $action_title = PeepSo::get_option('groups_join_post_action_text_group', __('joined this group','groupso'));
                if (empty($action_title)) {
                    $action_title = __('joined this group','groupso');
                }
                $title .= ' '. $action_title;
            }
        }

        return ($title);
    }

    public static function is_join_notification($post) {
        $is_join_notification = FALSE;

        if(strlen(get_post_meta($post->ID, self::JOIN_STREAM_META, TRUE))) {
            $is_join_notification = TRUE;
        }

        return $is_join_notification;
    }
    /**
     * Remove peepso_groups shortcode
     * @param string $string to process
     * @return string $string
     */
    public function filter_activity_remove_shortcode( $content )
    {
        foreach($this->shortcodes as $shortcode=>$class) {
            $from = array('['.$shortcode.']','['.$shortcode);
            $to = array('&#91;'.$shortcode.'&#93;', '&#91;'.$shortcode);
            $content = str_ireplace($from, $to, $content);
        }
        return $content;
    }

    public function filter_activity_has_privacy($has_privacy)
    {
        global $post;
        $group_id = get_post_meta($post->ID, 'peepso_group_id', true);
        if(!empty($group_id)) {
            return FALSE;
        }

        return $has_privacy;
    }

    /* * * * Photos * * * * */
    public function photos_groups_dir($photo_dir)
    {

        // check post parameters if 'group_id' and module_id is exist
        $group_id = $this->input->int('group_id', 0);
        $module_id = $this->input->int('module_id', 0);

        if($photo_dir === NULL && $module_id === self::MODULE_ID && 0 !== $group_id) {
            $group = new PeepSoGroup($group_id);
            if(FALSE !== $group) {
                $photo_dir = ($group) ? $group->get_image_dir() : '';
                $photo_dir .= 'photos' . DIRECTORY_SEPARATOR;
            }
        }

        return ($photo_dir);
    }

    public function photos_groups_url($photo_dir = '')
    {
        // check post parameters if 'group_id' and module_id is exist
        $group_id = $this->input->int('group_id', 0);
        $module_id = $this->input->int('module_id', 0);

        if(empty($photo_dir) && $module_id === self::MODULE_ID && 0 !== $group_id) {
            $group = new PeepSoGroup($group_id);
            if(FALSE !== $group) {
                $photo_dir = $group->get_image_url();
            }
        }

        return ($photo_dir);
    }

    public function photos_groups_thumbs_url($photo_url, $thumbs)
    {
        // check post parameters if 'group_id' and module_id is exist
        $group_id = $this->input->int('group_id', 0);
        $module_id = $this->input->int('module_id', 0);

        if($photo_url === NULL && $module_id === self::MODULE_ID && 0 !== $group_id) {
            $group = new PeepSoGroup($group_id);
            if(FALSE !== $group) {
                $photo_url = $group->get_image_url() . 'photos/thumbs/' . $thumbs;
            }
        }

        return ($photo_url);
    }

    public function post_photos_groups_location($photo_url, $post_id, $type)
    {
        $group_id = get_post_meta($post_id, 'peepso_group_id', true);

        if(!empty($group_id)) {
            $group = new PeepSoGroup($group_id);
            if(FALSE !== $group) {
                $photo_url = $group->get_image_url() . 'photos/';
                if($type == 'thumbs') {
                    $photo_url = $group->get_image_url() . 'photos/thumbs/';
                }
            }
        }

        return ($photo_url);
    }

    public function access_content($allow, $shortcode, $module)
    {
        global $wpdb;

        $PeepSoActivityShortcode = PeepSoActivityShortcode::get_instance();

        if($PeepSoActivityShortcode->is_permalink_page()) {
            $sql = 'SELECT `ID`, `act_access`, `act_owner_id` ' .
                " FROM `{$wpdb->posts}` " .
                " LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` ON `act_external_id`=`{$wpdb->posts}`.`ID` " .
                ' WHERE `post_name`=%s AND `post_type`=%s ' .
                ' LIMIT 1 ';
            $ret = $wpdb->get_row($wpdb->prepare($sql, $PeepSoActivityShortcode->get_permalink(), PeepSoActivityStream::CPT_POST));

            if($ret !== NULL)
            {
                $group_id = get_post_meta($ret->ID, 'peepso_group_id', true);
                if(!empty($group_id))
                {
                    $PeepSoGroupUser= new PeepSoGroupUser($group_id);
                    $allow = $PeepSoGroupUser->can('access');
                }
            }
        }

        return $allow;
    }

    public function access_message($message)
    {
        global $wpdb;

        $PeepSoActivityShortcode = PeepSoActivityShortcode::get_instance();

        if($PeepSoActivityShortcode->is_permalink_page()) {
            $sql = 'SELECT `ID`, `act_access`, `act_owner_id` ' .
                " FROM `{$wpdb->posts}` " .
                " LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` ON `act_external_id`=`{$wpdb->posts}`.`ID` " .
                ' WHERE `post_name`=%s AND `post_type`=%s ' .
                ' LIMIT 1 ';
            $ret = $wpdb->get_row($wpdb->prepare($sql, $PeepSoActivityShortcode->get_permalink(), PeepSoActivityStream::CPT_POST));

            if($ret !== NULL)
            {
                $group_id = get_post_meta($ret->ID, 'peepso_group_id', true);
                if(!empty($group_id))
                {
                    $PeepSoGroupUser = new PeepSoGroupUser($group_id);

                    if(!$PeepSoGroupUser->can('access'))
                    {
                        $message = PeepSoTemplate::do_404();
                    }
                }
            }
        }

        return $message;
    }

    /* * * * * * PHOTO ALBUM  * * * * * * */

    /**
     * todo:docblock
     */
    public function filter_photos_owner_album($owner)
    {
        $module_id = $this->input->int('module_id', 0);
        $group_id = $this->input->int('group_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $group_id) {
            $owner = $group_id;
        }

        $post_id = $this->input->int('post_id', 0);
        if (!$module_id && $post_id) {
            $group_id = get_post_meta($post_id, 'peepso_group_id', true);
            if ($group_id) {
                $owner = $group_id;
            }
        }

        return($owner);
    }

    /**
     * filter_photos_album_url
     */
    public function filter_photos_album_url($album_url)
    {

        if (PeepSoUrlSegments::get_instance()->_shortcode == 'peepso_groups') {
            $group_id = $this->url_segments->get(1);

            $group = new PeepSoGroup($group_id);
            $album_url = $group->get_url() . 'photos/album';
        }

        return($album_url);
    }

    /**
     * ajax_template_item_album
     */
    public function ajax_template_item_album($template)
    {
        $module_id = $this->input->int('module_id', 0);
        $group_id = $this->input->int('group_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $group_id) {
            $template = 'photo-group-item-album';
        }

        return $template;
    }

    /**
     * create_album_privacy_hide
     */
    public function create_album_privacy_hide($hide)
    {
        if (PeepSoUrlSegments::get_instance()->_shortcode == 'peepso_groups') {
            $hide = true;
        }

        return $hide;
    }

    public function ajax_create_album_privacy($privacy)
    {
        $module_id = $this->input->int('module_id', 0);
        $group_id = $this->input->int('group_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $group_id) {
            // todo : @group privacy
            $privacy = PeepSo::ACCESS_PUBLIC;
        }

        return $privacy;
    }

    /**
     * todo:docblock
     */
    public function filter_photos_owner_profile_url()
    {
        $module_id = $this->input->int('module_id', 0);
        $group_id = $this->input->int('group_id', 0);

        $profile_url = '';
        if(self::MODULE_ID === $module_id && 0 !== $group_id) {
            $group = new PeepSoGroup($group_id);
            $profile_url = $group->get_url();
        }

        return($profile_url);
    }

    /**
     * todo:docblock
     */
    public function filter_photos_owner($clauses)
    {
        global $wpdb;

        $module_id = $this->input->int('module_id', 0);
        $group_id = $this->input->int('group_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $group_id) {
            // Filter for groups joined
            $clauses['join'] .= sprintf(' LEFT JOIN `' . $wpdb->prefix . PeepSoGroupUsers::TABLE . '` `gm` ON ' .
                ' `' . $wpdb->prefix . PeepSoPhotosModel::TABLE . '`.`pho_owner_id` = `gm`.`gm_user_id` AND `gm`.`gm_group_id` = %d ', $group_id) ;
        }

        return $clauses;
    }

    /**
     * todo:docblock
     */
    public function filter_photos_owner_name($owner_name)
    {
        $module_id = $this->input->int('module_id', 0);
        $group_id = $this->input->int('group_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $group_id) {
            $group = new PeepSoGroup($group_id);
            $owner_name = $group->name;
        }

        return($owner_name);
    }

    public function photos_stream_photos_album($album_id)
    {
        $module_id = $this->input->int('module_id', 0);
        $group_id = $this->input->int('group_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $group_id) {
            $photo_album = new PeepSoPhotosAlbumModel();
            $album_id = $photo_album->get_photo_album_id($group_id, self::MODULE_ID . PeepSoSharePhotos::ALBUM_STREAM, 0, self::MODULE_ID);
        }

        return($album_id);
    }

    public function photos_profile_photos_album($album_id, $album)
    {
        $module_id = $this->input->int('module_id', 0);
        $group_id = $this->input->int('group_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $group_id) {
            $photo_album = new PeepSoPhotosAlbumModel();
            $album_id = $photo_album->get_photo_album_id($group_id, $album, 0, self::MODULE_ID);
        }

        return($album_id);
    }

    /**
     * Setup album for group if album for group not created yet
     * @param group_id Viewed photo group
     */
    public function action_setup_group_album($group=0)
    {
        if(!class_exists('PeepSoSharePhotos')) {
            return;
        }

        // check group_id
        if($group instanceof PeepSoGroup) {
            $group_id = $group->get('id');
        } else {
            $group_id = $group;
        }

        if($group_id !== 0)
        {
            global $wpdb;

            $group_user = new PeepSoGroupUser($group_id);
            $group = new PeepSoGroup($group_id);
            $dir = $group->get_image_dir();
            $user_id = $group->owner_id;

            $album_model = new PeepSoPhotosAlbumModel();
            foreach($this->photo_group_system_album as $album)
            {
                $album_id = $album_model->get_photo_album_id($group_id, $album['is_system'], 0, PeepSoGroupsPlugin::MODULE_ID);
                $new_album_id = $album_id;
                // if album not found, insert the album
                if(FALSE === $album_id) {
                    $data = array(
                        'pho_owner_id' => $group_id,
                        'pho_album_acc' => $album['albumname_acc'],
                        'pho_album_name' => $album['albumname'],
                        'pho_system_album' => $album['is_system'], // flag for album, 1 = system album, 2 = user created album
                        'pho_module_id' => PeepSoGroupsPlugin::MODULE_ID,
                    );
                    $wpdb->insert($wpdb->prefix . PeepSoPhotosAlbumModel::TABLE , $data);

                    $new_album_id = $wpdb->insert_id;

                    // save avatars when upgrading
                    // if profile avatars album not created yet
                    if($album['is_system'] == self::MODULE_ID . PeepSoSharePhotos::ALBUM_AVATARS) {

                        $content = '';
                        $extra = array(
                            'module_id' => PeepSoSharePhotos::MODULE_ID,
                            'act_access' => PeepSo::ACCESS_PUBLIC,
                        );

                        $dest_orig = $dir . 'avatar-orig.jpg';

                        // check if file exist and post update avatar change option is true
                        if (file_exists($dest_orig)) {

                            $this->file_avatar = $dest_orig;
                            add_filter('peepso_photos_groups_avatar_original', array(&$this, 'set_file_avatar'),10,1);
                            add_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'), 10, 1);
                            add_filter('peepso_pre_write_content', array(&$this, 'set_post_date'), 10, 1);
                            add_filter('peepso_pre_write_content', array(&$this, 'set_post_status'), 20, 1);
                            add_action('peepso_activity_after_add_post', array(&$this, 'after_add_post_avatar'), 10, 2);

                            $peepso_activity = PeepSoActivity::get_instance();
                            $post_id = $peepso_activity->add_post($user_id, $user_id, $content, $extra);
                            add_post_meta($post_id, PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE, PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE_AVATAR, true);
                            add_post_meta($post_id, 'peepso_group_id', $group_id);

                            remove_filter('peepso_photos_groups_avatar_original', array(&$this, 'set_file_avatar'));
                            remove_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'));
                            remove_filter('peepso_pre_write_content', array(&$this, 'set_post_date'));
                            remove_filter('peepso_pre_write_content', array(&$this, 'set_post_status'));
                            remove_action('peepso_activity_after_add_post', array(&$this, 'after_add_post_avatar'));
                        }
                    }

                    // save covers when upgrading
                    // if profile covers album not created yet
                    if($album['is_system'] == self::MODULE_ID . PeepSoSharePhotos::ALBUM_COVERS) {
                        #$content = __('change cover','picso');
                        $content = '';
                        $extra = array(
                            'module_id' => PeepSoSharePhotos::MODULE_ID,
                            'act_access' => PeepSo::ACCESS_PUBLIC,
                        );

                        $dest_file = $dir . 'cover.jpg';

                        if(file_exists($dest_file)) {
                            $this->file_cover = $dest_file;
                            add_filter('peepso_photos_groups_cover_original', array(&$this, 'set_file_cover'));
                            add_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'), 10, 1);
                            add_filter('peepso_pre_write_content', array(&$this, 'set_post_date'), 10, 1);
                            add_filter('peepso_pre_write_content', array(&$this, 'set_post_status'), 20, 1);
                            add_action('peepso_activity_after_add_post', array(&$this, 'action_add_post_cover'), 10, 2);

                            $peepso_activity = PeepSoActivity::get_instance();
                            $post_id = $peepso_activity->add_post($user_id, $user_id, $content, $extra);
                            add_post_meta($post_id, PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE, PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE_COVER, true);
                            add_post_meta($post_id, 'peepso_group_id', $group_id);

                            remove_filter('peepso_photos_groups_cover_original', array(&$this, 'set_file_cover'));
                            remove_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'));
                            remove_filter('peepso_pre_write_content', array(&$this, 'set_post_date'));
                            remove_filter('peepso_pre_write_content', array(&$this, 'set_post_status'));
                            remove_action('peepso_activity_after_add_post', array(&$this, 'action_add_post_cover'));
                        }
                    }
                }
                if($album['is_system'] == self::MODULE_ID . PeepSoSharePhotos::ALBUM_STREAM) {
                    $wpdb->update(
                        $wpdb->prefix . PeepSoPhotosModel::TABLE,
                        array(
                            'pho_album_id' => $new_album_id,    // int (number)
                        ),
                        array( 'pho_owner_id' => $group_id, 'pho_album_id' => 0, 'pho_module_id' => PeepSoGroupsPlugin::MODULE_ID ), // where photo_album_id still undefined (0)
                        array( '%d' ),
                        array( '%d','%d' )
                    );
                }
            }
        }
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
    public function action_add_post_cover($post_id, $act_id)
    {
        $file = '';
        $file = apply_filters('peepso_photos_groups_cover_original',$file);
        $album = apply_filters('peepso_photos_groups_covers_album', self::MODULE_ID . PeepSoSharePhotos::ALBUM_COVERS);
        if(!empty($file)) {
            $_photos_model = new PeepSoPhotosModel;
            $_photos_model->save_images_profile($file, $post_id, $act_id, $album);
        }
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
        $file   = apply_filters('peepso_photos_groups_avatar_original',$file);
        $album  = apply_filters('peepso_photos_groups_avatars_album', self::MODULE_ID . PeepSoSharePhotos::ALBUM_AVATARS);
        if(!empty($file)) {
            $_photos_model = new PeepSoPhotosModel;
            $_photos_model->save_images_profile($file, $post_id, $act_id, $album);
        }
    }

    /**
     * Checks if empty content is allowed
     * @param string $allowed
     * @return boolean always returns TRUE
     */
    public function activity_allow_empty_content($allowed)
    {
        /*$type = $input->value('type', '', array('photo', 'album')); // SQL safe
        if ('photo' === $type || 'album' === $type) {
            $allowed = TRUE;
        }*/

        if(isset($this->file_avatar) || isset($this->file_cover) ) {
            $allowed = TRUE;
        }

        // allowed empty content after adding activity change avatar
        // SQL safe, WP sanitizes it
        if (FALSE !== wp_verify_nonce($this->input->value('_wpnonce','',FALSE), 'cover-photo')) {
            $allowed = TRUE;
        }

        // allowed empty content after adding activity change cover
        // if (isset($_GET['cover'])) {
        //     $allowed = TRUE;
        // }

        return ($allowed);
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

        $group_id = $this->input->int('group_id', 0);

        if(0 !== $group_id) {

            $group = new PeepSoGroup($group_id);

            if(is_array($aPostData) && (isset($aPostData['group_avatar']) && 0 === intval(PeepSo::get_option('photos_groups_enable_post_updates_group_avatar',1)) || FALSE === $group->published)) {
                $aPostData['post_status'] = 'pending';
            }

            if(is_array($aPostData) && (isset($aPostData['group_cover']) && 0 === intval(PeepSo::get_option('photos_groups_enable_post_updates_group_cover',1))  || FALSE === $group->published)) {
                $aPostData['post_status'] = 'pending';
            }
        }

        return $aPostData;
    }

    /**
     * Function called after avatar changed
     * @param user_id
     * @param dest_thumb
     * @param dest_full
     * @param dest_orig
     */
    public function action_change_avatar($group_id, $dest_thumb, $dest_full, $dest_orig)
    {
        if(0 !== $group_id){

            // migrate from activate function,
            // setup album before uploading avatar
            $this->action_setup_group_album($group_id);

            #$content = __('change avatar','picso');
            $content = '';
            $extra = array(
                'module_id' => PeepSoSharePhotos::MODULE_ID,
                'act_access' => PeepSo::ACCESS_PUBLIC,
                'group_avatar' => TRUE
            );
            $user_id = get_current_user_id();

            $this->file_avatar = $dest_orig;
            add_filter('peepso_photos_groups_avatar_original', array(&$this, 'set_file_avatar'));
            add_action('peepso_activity_after_add_post', array(&$this, 'after_add_post_avatar'), 10, 2);
            add_filter('peepso_pre_write_content', array(&$this, 'set_post_date'), 10, 1);
            add_filter('peepso_pre_write_content', array(&$this, 'set_post_status'), 20, 1);

            $peepso_activity = PeepSoActivity::get_instance();
            $post_id = $peepso_activity->add_post($user_id, $user_id, $content, $extra);
            add_post_meta($post_id, PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE, PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE_AVATAR, true);
            add_post_meta($post_id, 'peepso_group_id', $group_id);
        }
    }

    /**
     * Function called after cover changed
     * @param user_id
     * @param dest_file
     */
    public function action_change_cover($group_id, $dest_file)
    {
        if(0 !== $group_id){

            // migrate from activate function,
            // setup album before uploading cover
            $this->action_setup_group_album($group_id);

            #$content = __('change cover','picso');
            $content = '';
            $extra = array(
                'module_id' => PeepSoSharePhotos::MODULE_ID,
                'act_access' => PeepSo::ACCESS_PUBLIC,
                'group_cover' => TRUE
            );
            $user_id = get_current_user_id();

            $this->file_cover = $dest_file;
            add_filter('peepso_photos_groups_cover_original', array(&$this, 'set_file_cover'));
            add_action('peepso_activity_after_add_post', array(&$this, 'action_add_post_cover'), 10, 2);
            add_filter('peepso_pre_write_content', array(&$this, 'set_post_date'), 10, 1);
            add_filter('peepso_pre_write_content', array(&$this, 'set_post_status'), 20, 1);

            $peepso_activity = PeepSoActivity::get_instance();
            $post_id = $peepso_activity->add_post($user_id, $user_id, $content, $extra);
            add_post_meta($post_id, PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE, PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE_COVER, true);
            add_post_meta($post_id, 'peepso_group_id', $group_id);
        }
    }

    public function stream_action_change_avatar($action, $post_id) {
        $group_id = get_post_meta($post_id, 'peepso_group_id', TRUE);

        if(!empty($group_id)) {
            $action = __(' updated group avatar', 'groupso');
        }

        return ($action);
    }

    public function stream_action_change_cover($action, $post_id) {
        $group_id = get_post_meta($post_id, 'peepso_group_id', TRUE);

        if(!empty($group_id)) {
            $action = __(' updated group cover', 'groupso');
        }

        return ($action);
    }

    public function photos_stream_action_photo_album($action, $post_id) {
        $group_id = get_post_meta($post_id, 'peepso_group_id', TRUE);

        if(!empty($group_id)) {
            $photos_album_model = new PeepSoPhotosAlbumModel();

            // [USER] added [photo/photos] to [ALBUM NAME] album
            $total_photos = get_post_meta($post_id, PeepSoSharePhotos::POST_META_KEY_PHOTO_COUNT, true);
            $album = $photos_album_model->get_photo_album($group_id, 0, $post_id, self::MODULE_ID);

            // generate link
            $group = new PeepSoGroup($group_id);
            $link_to_album = $group->get_url() . 'photos/album/' . $album[0]->pho_album_id;

            $action = sprintf(_n(' added %1$d photo to the album: <a href="%3$s">%2$s</a>', ' added %1$d photos to the album: <a href="%3$s">%2$s</a>', $total_photos, 'picso'), $total_photos, $album[0]->pho_album_name, $link_to_album);
        }

        return ($action);
    }

    public function stream_action_album($action, $post_id) {
        $group_id = get_post_meta($post_id, 'peepso_group_id', TRUE);

        if(!empty($group_id)) {
            $action = __(' updated group cover', 'groupso');
        }

        return ($action);
    }

    /* * * * VIDEOS * * * * */

    /**
     * Modify the clauses to filter posts
     * @param  array $clauses
     * @param  int $user_id The owner of the activity stream
     * @return array
     */
    public function filter_videos_post_clauses($clauses, $module_id, $widgets)
    {
        global $wpdb;

        if($module_id == self::MODULE_ID) {

            $group_id = $this->input->int('group_id', 0);

            // filter clauses for videos
        }

        if($widgets) {
            // Filter for groups joined
            $clauses['join'] .= ' LEFT JOIN `' . $wpdb->postmeta  . '` `pm` ON ' .
                ' `' . $wpdb->posts . '`.`ID` = `pm`.`post_id` AND `pm`.`meta_key` = \'peepso_group_id\' ' ;

            $clauses['where'] .= " AND (`pm`.`meta_value` IS NULL) ";
        }

        return $clauses;
    }

    /**
     * todo:docblock
     */
    public function filter_videos_owner_name($owner_name)
    {
        $module_id = $this->input->int('module_id', 0);
        $group_id = $this->input->int('group_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $group_id) {
            $group = new PeepSoGroup($group_id);
            $owner_name = $group->name;
        }

        return($owner_name);
    }

    /**
     * todo:docblock
     */
    public function filter_videos_owner($clauses)
    {
        global $wpdb;

        $module_id = $this->input->int('module_id', 0);
        $group_id = $this->input->int('group_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $group_id) {
            // Filter for groups joined
            $clauses['join'] .= ' LEFT JOIN `' . $wpdb->postmeta . '` `gm` ON ' .
                ' `' . $wpdb->posts . '`.`ID` = `gm`.`post_id`' ;
            $clauses['where'] .= sprintf(' AND (`gm`.`meta_key`=\'peepso_group_id\' AND `gm`.`meta_value` = %d)', $group_id);
        }

        return $clauses;
    }

    /* * * * Notifications * * * */

    /**
     * Notify group OWNERS about a new member (if enabled)
     * Create a stream entry (if applicable)
     * Make sure "followers" entry is created
     * @param $group_id
     * @param $user_id
     */
    public function action_group_user_join($group_id, $user_id)
    {
        $PeepSoGroup = new PeepSoGroup($group_id);

        // Notify Owners and Managers
        if(!$PeepSoGroup->is_join_muted) {
            $PeepSoGroupUsers = new PeepSoGroupUsers($group_id);
            $owners_and_managers = $PeepSoGroupUsers->get_owners_and_managers();

            $PeepSoNotifications = new PeepSoNotifications();

            $i18n = __('joined your group', 'groupso');
            $message = 'joined your group';
            $args = ['groupso'];

            foreach ($owners_and_managers as $PeepSoGroupUser) {
                $PeepSoNotifications->add_notification_new($user_id, $PeepSoGroupUser->user_id, $message, $args, 'groups_user_join', self::MODULE_ID, $group_id);
            }
        }


        // Post to stream
        if(1 == PeepSo::get_option('groups_join_post_to_stream',0)) {
            // POST TO STREAM
            $extra = array(
                'module_id' => self::MODULE_ID,
                'act_access'=> PeepSo::ACCESS_MEMBERS,
            );

            $content = self::JOIN_STREAM;

            // create an activity item
            $act = PeepSoActivity::get_instance();
            $act_id = $act->add_post($user_id, $user_id, $content, $extra);

            update_post_meta($act_id, 'peepso_group_id', $group_id);
            update_post_meta($act_id, self::JOIN_STREAM_META, $group_id);
        }

        // Make sure a "followers" record is created
        $PeepSoGroupFollower = new PeepSoGroupFollower($group_id, $user_id);
    }

    /**
     * Notify the INVITATION SENDER that the invited user accepted
     * @param $group_id
     */
    public function action_group_user_invitation_accept(PeepSoGroupUser $PeepSoGroupUser)
    {
        if($PeepSoGroupUser->invited_by_id) {
            $PeepSoNotifications = new PeepSoNotifications();

            $i18n = __('accepted your group invitation', 'groupso');
            $message = 'accepted your group invitation';
            $args = ['groupso'];

            $PeepSoNotifications->add_notification_new(get_current_user_id(), $PeepSoGroupUser->invited_by_id, $message, $args, 'groups_user_invitation_accept', self::MODULE_ID, $PeepSoGroupUser->group_id);
        }
    }

    /**
     * Notify the group OWNERS AND MANAGERS  about a new join request
     * @param $group_id
     */
    public function action_group_user_join_request_send($group_id) {

        // delete all join_request_send notifications for this group
        global $wpdb;

        $where = array(
            'not_type' 			=> 'groups_user_join_request_send',
            'not_external_id'	=> $group_id,
        );

        $wpdb->delete($wpdb->prefix.PeepSoNotifications::TABLE, $where);

        $PeepSoGroupUsers = new PeepSoGroupUsers($group_id);

        // aggregated notification textp
        $args = ['groupso'];
        $message = $this->notification_message_user_join_request($PeepSoGroupUsers, get_current_user_id(), $args);

        $owners_and_managers = $PeepSoGroupUsers->get_owners_and_managers();

        $PeepSoNotifications = new PeepSoNotifications();

        foreach($owners_and_managers as $PeepSoGroupUser) {
            $PeepSoNotifications->add_notification_new(get_current_user_id(), $PeepSoGroupUser->user_id, $message, $args, 'groups_user_join_request_send', self::MODULE_ID, $group_id);
        }
    }

    private function notification_message_user_join_request(PeepSoGroupUsers $PeepSoGroupUsers, $user_id, &$args = [])
    {

        $pending = $PeepSoGroupUsers->get_pending_admin();
        $pending_count = count($pending) -1; // exclude self

        if($pending_count > 0) {

            $args[] = '<strong>';

            if ($pending_count == 1) {
                foreach ($pending as $PeepSoGroupUser) {

                    if ($PeepSoGroupUser->user_id == $user_id) { continue; }

                    $i18m = __('and %s%s%s requested to join your group', 'groupso');
                    $message = 'and %s%s%s requested to join your group';

                    $PeepSoUser = PeepSoUser::get_instance($PeepSoGroupUser->user_id);
                    $args[] = $PeepSoUser->get_firstname();
                }
            } else {
                $i18m = __('and %s%s more users%s requested to join your group', 'groupso');
                $message = 'and %s%s more users%s requested to join your group';
                $args[] = $pending_count;
            }

            $args[] = '</strong>';
        } else {
            $i18n = __('requested to join your group', 'groupso');
            $message = 'requested to join your group';
        }


        return $message;
    }

    private function notification_update_user_join_request($group_id, $user_id)
    {
        global $wpdb;

        $PeepSoGroupUsers = new PeepSoGroupUsers($group_id, $user_id);
        $pending = $PeepSoGroupUsers->get_pending_admin();

        $where = array(
            'not_type' => 'groups_user_join_request_send',
            'not_external_id' => $group_id,
        );

        if(count($pending)) {
            // new not_from_user_id (in case we accept or reject the user who is the current not_from_user_id)
            foreach ($pending as $PeepSoGroupUser) {
                $data = array('not_from_user_id' => $PeepSoGroupUser->user_id);
                break;
            }

            // need new aggregated notification content
            $data['not_message'] = $this->notification_message_user_join_request($PeepSoGroupUsers, $data['not_from_user_id']);

            $wpdb->update($wpdb->prefix . PeepSoNotifications::TABLE, $data, $where);
        } else {
            $wpdb->delete($wpdb->prefix . PeepSoNotifications::TABLE, $where);
        }
    }

    /**
     * Notify USER WHO REQUESTED that he was accepted
     * @param $group_id
     * @param $user_id
     */
    public function action_group_user_join_request_accept($group_id, $user_id)
    {
        $this->notification_update_user_join_request($group_id, $user_id);
        $PeepSoNotifications = new PeepSoNotifications();

        $i18n = __('accepted you as a group member', 'groupso');
        $message = 'accepted you as a group member';
        $args = ['groupso'];

        $PeepSoNotifications->add_notification_new(get_current_user_id(), $user_id, $message, $args, 'groups_user_join_request_accept', self::MODULE_ID, $group_id);

        // Make sure a "followers" record is created
        $PeepSoGroupFollower = new PeepSoGroupFollower($group_id, $user_id);
    }


    /**
     * Clean up after user deletion
     * @param $group_id
     * @param $user_id
     */
    public function action_group_user_delete($group_id, $user_id)
    {
        // if the user was pending_admin, update the notifications
        $this->notification_update_user_join_request($group_id, $user_id);

        // delete the "user has joined" posts
        $args = array(
            'author' => $user_id,
            'post_type' => 'peepso-post',
            'meta_query' => array(
                array(
                    'key' => self::JOIN_STREAM_META,
                    'value' => $group_id,
                    'compare' => '=',
                ),
            )
        );

        $posts = get_posts($args);
        if(count($posts)) {
            foreach($posts as $post) {
                wp_delete_post($post->ID);
            }
        }

        // unsubscribe
        $PeepSoGroupFollower = new PeepSoGroupFollower($group_id, $user_id);
        $PeepSoGroupFollower->delete();
    }

    public function action_group_rename($group_id, $user_id) {
        $group_users = new PeepSoGroupUsers($group_id);
        $list_members = $group_users->get_members();
        $group = new PeepSoGroup($group_id);

        $i18n = __('renamed a group you\'re a member of', 'groupso');
        $message = 'renamed a group you\'re a member of';
        $args = ['groupso'];

        if(count($list_members) > 0) {
            $notif = new PeepSoNotifications();

            foreach($list_members as $groupuser) {
                if ($groupuser->user_id != $user_id) {
                    $notif->add_notification_new($user_id, $groupuser->user_id, $message, $args, 'groups_rename', self::MODULE_ID, $group_id);
                }
            }
        }
    }

    public function action_group_privacy_change($group_id, $user_id) {
        $group_users = new PeepSoGroupUsers($group_id);
        $group = new PeepSoGroup($group_id);

        $list_members = $group_users->get_members();

        if(count($list_members ) > 0) {
            $notif = new PeepSoNotifications();

            $i18n = __('changed group privacy to %s', 'groupso');
            $message = 'changed group privacy to %s';
            $args = [
                    'groupso',

                    $group->privacy['notif']
            ];

            foreach($list_members  as $groupuser) {
                if ($groupuser->user_id != $user_id) {
                    $notif->add_notification_new($user_id, $groupuser->user_id, $message, $args, 'groups_privacy_change', self::MODULE_ID, $group_id);
                }
            }
        }
    }

    public function action_activity_single_override_header()
    {
        global $post;

        // Group found
        $PeepSoActivityShortcode = PeepSoActivityShortcode::get_instance();
        if( $PeepSoActivityShortcode->is_permalink_page()) {
            $group_id = get_post_meta($post->ID, 'peepso_group_id', true);

            // group not found
            if(!get_post($group_id)) {
                PeepSo::redirect(PeepSo::get_page('groups').'?'.$group_id.'/');
                die();
            }

            // group found
            if($group_id) {
                $group = new PeepSoGroup($group_id);
                PeepSoTemplate::exec_template('groups', 'group-header', array('group'=>$group, 'group_segment'=> 'stream'));
                $this->enqueue_scripts(TRUE);
            }
        }
    }

    /* * * * Frontend utils * * * */

    public  function admin_enqueue_scripts()
    {
        wp_enqueue_script('peepso-admin-groups',
            PeepSo::get_asset('js/admin.js', __FILE__),
            array('jquery', 'underscore'), self::PLUGIN_VERSION, TRUE);
    }

    public  function enqueue_scripts()
    {
        global $post;

        $dialog_invite_params = array();
        $group_id = NULL;

        // Get group_id from single post view.
        $as = PeepSoActivityShortcode::get_instance();
        if ($as->is_permalink_page()) {
            $group_id = get_post_meta($post->ID, 'peepso_group_id', true);
            if ($group_id) {
                $group = new PeepSoGroup($group_id);
                $group_id = $group->get('id');
            }
        }
        // Or from the URL.
        else {
            $segment_part = is_front_page() ? 0 : 1;
            if (PeepSoUrlSegments::get_instance()->_shortcode == 'peepso_groups') {
                $group_id = $this->url_segments->get($segment_part);
            }
            if ($group_id) {
                $group = new PeepSoGroup($group_id);
                $group_id = $group->get('id');

                // Reload on close dialog invite if currently on "GROUP_ID/members/invited" page.
                $reload_on_close = false;
                $segment_part_1 = $this->url_segments->get($segment_part + 1);
                $segment_part_2 = $this->url_segments->get($segment_part + 2);
                if ('members' === $segment_part_1 && 'invited' === $segment_part_2) {
                    $reload_on_close = true;
                }

                $dialog_invite_params = array( 'reload_on_close' => $reload_on_close );
            }
        }

        $PeepSoGroupUser = new PeepSoGroupUser($group_id, get_current_user_id());

        $pin_group_only = 1 == PeepSo::get_option('groups_pin_group_only', 0);
        $pin_group_only_no_pinned_style = 1 == PeepSo::get_option('groups_pin_group_only_no_pinned_style', 0);
        $pin_group_only_no_pinned_style = $pin_group_only && $pin_group_only_no_pinned_style;

        $data = array(
            'dialogCreateTemplate' => PeepSoTemplate::exec_template('groups', 'dialog-create', NULL, TRUE),
            'dialogInviteTemplate' => PeepSoTemplate::exec_template('groups', 'dialog-invite', $dialog_invite_params, TRUE),
            'listItemTemplate' => PeepSoTemplate::exec_template('groups', 'groups-item', NULL, TRUE),
            'listItemMemberActionsTemplate' => PeepSoTemplate::exec_template('groups', 'groups-item-member-actions', NULL, TRUE),
            'listCategoriesTemplate' => PeepSoTemplate::exec_template('groups', 'groups-categories', NULL, TRUE),
            'headerActionsTemplate' => PeepSoTemplate::exec_template('groups', 'group-header-actions', NULL, TRUE),
            'memberItemTemplate' => PeepSoTemplate::exec_template('groups', 'group-members-item', NULL, TRUE),
            'memberItemActionsTemplate' => PeepSoTemplate::exec_template('groups', 'group-members-item-actions', NULL, TRUE),
            'group_url' => PeepSo::get_page('groups') . '?category=##category_id##',
            'group_id' => $group_id,
            'user_id' => get_current_user_id(),
            'max_categories' => PeepSo::get_option_new('groups_categories_multiple_max'),
            'pin_group_only' => $pin_group_only ? 1 : 0,
            'pin_group_only_no_pinned_style' => $pin_group_only_no_pinned_style ? 1 : 0,
            'force_posts_in_groups' => apply_filters('peepso_filter_force_posts_in_groups', FALSE),
            'peepsoGroupUser' => array(
                'can_manage_users' => $PeepSoGroupUser->can('manage_users'),
                'can_pin_posts' => $PeepSoGroupUser->can('pin_posts'),
            ),
            'module_id' => self::MODULE_ID,
            'list_show_owner' => PeepSo::get_option('groups_listing_show_group_owner', 0),
            'lang' => array(
                'more' => __('More', 'groupso'),
                'less' => __('Less', 'groupso'),
                'member' => __('member', 'groupso'),
                'members' => __('members', 'groupso'),
                'name_change_confirmation' => __('Are you sure you want to change the group name?','groupso') .'<br>' . __('All group members will be notified.','groupso'),
                'slug_change_confirmation' => __('Are you sure you want to change the group URL?','groupso') .'<br>' . __('All group members will be notified.','groupso'),
                'privacy_change_confirmation' => __('Are you sure you want to change the group privacy?','groupso') .'<br>' . __('All group members will be notified.','groupso'),
                'uncategorized' => __('Uncategorized', 'groupso'),
            ),

            // set nonce
            'nonce_set_group_name' => wp_create_nonce('set-group-name'),
            'nonce_set_group_slug' => wp_create_nonce('set-group-slug'),
            'nonce_set_group_privacy' => wp_create_nonce('set-group-privacy'),
            'nonce_set_group_description' => wp_create_nonce('set-group-description'),
            'nonce_set_group_categories' => wp_create_nonce('set-group-categories'),
            'nonce_set_group_property' => wp_create_nonce('set-group-property'),
            'nonce_set_group_custom_input' => wp_create_nonce('set-group-custom-input'),
        );

        // get group info
        if ($group_id) {

            if ($group->id)	{
                $data['id'] = $group->get('id');
                $data['name'] = $group->get('name');
                $data['hasAvatar'] = $group->has_avatar() ? TRUE : FALSE;
                $data['imgAvatar'] = $group->get_avatar_url();
                $data['imgOriginal'] = $group->get_avatar_url_orig();
                $data['privacy'] = $group->privacy['id'];
            }
        }

        wp_localize_script('peepso', 'peepsogroupsdata', $data);

        // Single group page's script.
        if ($group_id) {
            wp_enqueue_script('peepso-groups-page-group',
                PeepSo::get_asset('js/page-group.min.js', __FILE__),
                array('jquery-ui-draggable', 'peepso', 'peepso-fileupload'), self::PLUGIN_VERSION, TRUE);

            add_filter('peepso_data', function( $data ) use ( $group_id, $group ) {
                if ($group_id) {
                    $group_data = array(
                        'id'                  => $group->get('id'),
                        'module_id'           => self::MODULE_ID,
                        'slug'                => $group->get('slug'),
                        'name'                => $group->get('name'),
                        'has_avatar'          => $group->has_avatar() ? TRUE : FALSE,
                        'img_avatar'          => $group->get_avatar_url(),
                        'img_avatar_default'  => $group->get_default_avatar_url(),
                        'img_avatar_original' => $group->get_avatar_url_orig(),
                        'avatar_nonce'        => wp_create_nonce('group-avatar'),
                        'has_cover'           => $group->has_cover() ? TRUE : FALSE,
                        'img_cover'           => $group->get_cover_url(),
                        'img_cover_default'   => $group->get_cover_default(),
                        'cover_nonce'         => wp_create_nonce('group-cover'),
                        'text_error_filetype' => __('The file type you uploaded is not allowed. Only JPEG, PNG, and WEBP allowed.', 'groupso'),
                        'text_error_filesize' => sprintf(
                            __('The file size you uploaded is too big. The maximum file size is %s.', 'groupso'),
                            '<strong>' . PeepSoGeneral::get_instance()->upload_size() . '</strong>'
                        )
                    );

                    $data['group'] = array_merge(
                        $group_data,
                        array(
                            'template_avatar'       => PeepSoTemplate::exec_template('groups', 'dialog-avatar', array( 'data' => $group_data ), TRUE),
                            'template_cover_remove' => PeepSoTemplate::exec_template('groups', 'dialog-cover-remove', array(), TRUE),
                        )
                    );
                }

                return $data;
            }, 10, 1);
        }

        $logged_in = is_user_logged_in();

        wp_enqueue_style('peepso-fileupload');
        wp_enqueue_script('peepso-fileupload');

        wp_register_script('peepso-groups-create',
            $logged_in ? PeepSo::get_asset('js/groups-create.min.js', __FILE__) : FALSE,
            array('peepso'), PeepSo::PLUGIN_VERSION, TRUE);

        wp_register_script('peepso-groups-crop',
            $logged_in ? PeepSo::get_asset('js/crop.min.js') : FALSE,
            array('jquery', 'peepso-hammer'), PeepSo::PLUGIN_VERSION, TRUE);

        wp_register_script('peepso-groups-dialog-invite',
            $logged_in ? PeepSo::get_asset('js/dialog-invite.min.js', __FILE__) : FALSE,
            array('jquery', 'peepso'), PeepSo::PLUGIN_VERSION, TRUE);

        wp_enqueue_script('peepso-groups-group',
            PeepSo::get_asset('js/group.min.js', __FILE__),
            array('peepso', 'peepso-groups-dialog-invite'), PeepSo::PLUGIN_VERSION, TRUE);

        wp_enqueue_script('peepso-groups',
            PeepSo::get_asset('js/bundle.min.js', __FILE__),
            array('peepso', 'peepso-page-autoload', 'peepso-groups-create'), self::PLUGIN_VERSION, TRUE);

        add_filter('peepso_data', function( $data ) {
            $data['groups'] = array(
                'textNoResult' => __('No result found.', 'groupso'),
                'categories' => array(
                    'groups_categories_expand_all' => PeepSo::get_option('groups_categories_expand_all', 0),
                    'groups_categories_group_count' => PeepSo::get_option('groups_categories_group_count', 4)
                )
            );
            return $data;
        }, 10, 1 );
    }

    /**
     * todo
     */
    public function notifications_activity_type($activity_type, $post_id, $act_id = NULL) {

        # $activity_type = array(
        #   'text' => __('post', 'peepso'),
        #   'type' => 'post'
        # );

        /**
         * Please note that we mus define email template for each
         * 1. like_{type}
         * 2. user_comment_{type}
         * 3. share_{type}
         */

        if(!class_exists('PeepSoSharePhotos')) {
            return $activity_type;
        }

        $group_id = get_post_meta($post_id, 'peepso_group_id', TRUE);

        if(is_array($activity_type) && !empty($group_id)) {
            $photo_type = get_post_meta($post_id, PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE, true);

            $type = '';
            if(in_array($activity_type['type'], array('user_comment', 'share'))) {
                $type = $activity_type['type'] . '_';
            } elseif(in_array($activity_type['type'], array('user_comment_cover', 'user_comment_avatar'))) {
                $type = 'user_comment_group_';
            }

            if( $photo_type === PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE_AVATAR ) {
                $activity_type = array(
                    'text' => __('group avatar', 'groupso'),
                    'type' => $type . 'avatar'
                );
            } else if( $photo_type === PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE_COVER ) {
                $activity_type = array(
                    'text' => __('group cover photo', 'groupso'),
                    'type' => $type . 'cover'
                );
            }
        }

        return ($activity_type);
    }

    /* * * * Activation, PeepSo detection / version compatibility, licensing * * * */

    /**
     * Plugin activation.
     * Check PeepSo
     * Run installation
     * @return bool
     */
    public function activate()
    {
        if (!$this->peepso_check()) {
            return (FALSE);
        }

        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'activate.php');
        $install = new PeepSoGroupsInstall();
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
            add_action('admin_notices', function(){
                ?>
                <div class="error peepso">
                    <strong>
                        <?php echo sprintf(__('The %s plugin requires the PeepSo plugin to be installed and activated.', 'groupso'), self::PLUGIN_NAME);?>
                        <a href="plugin-install.php?tab=plugin-information&plugin=peepso-core&TB_iframe=true&width=772&height=291" class="thickbox">
                            <?php echo __('Get it now!', 'groupso');?>
                        </a>
                    </strong>
                </div>
                <?php
            });
            unset($_GET['activate']);
            deactivate_plugins(plugin_basename(__FILE__));
            return (FALSE);
        }

        // PeepSo.com license check
        if (!PeepSoLicense::check_license(self::PLUGIN_EDD, self::PLUGIN_SLUG)) {
            add_action('admin_notices', function(){
                PeepSo::license_notice(self::PLUGIN_NAME, self::PLUGIN_SLUG);
            });
        }

        if (isset($_GET['page']) && 'peepso_config' == $_GET['page'] && !isset($_GET['tab'])) {
            add_action('admin_notices', function(){
                PeepSo::license_notice(self::PLUGIN_NAME, self::PLUGIN_SLUG, true);
            });
        }

        // PeepSo.com new version check
        // since 1.7.6
        if(method_exists('PeepSoLicense', 'check_updates_new')) {
            PeepSoLicense::check_updates_new(self::PLUGIN_EDD, self::PLUGIN_SLUG, self::PLUGIN_VERSION, __FILE__);
        }

        return (TRUE);
    }

    /* * * * PeepSo admin section * * * */

    public function filter_taggable($taggable, $act_id) {
        $profile = PeepSoActivity::get_instance();

        if (!is_null($act_id) && FALSE === is_null($activity = $profile->get_activity_post($act_id))) {
            $post_id = $activity->ID;
            if ($activity->post_type == PeepSoActivityStream::CPT_COMMENT) {
                $parent_activity = $profile->get_activity_data($activity->act_comment_object_id, $activity->act_comment_module_id);

                if (is_object($parent_activity)) {
                    $parent_post = $profile->get_activity_post($parent_activity->act_id);
                    $parent_id = $parent_post->act_external_id;

                    // check if parent post is a comment
                    if($parent_post->post_type == 'peepso-comment') {
                        $comment_activity = $profile->get_activity_data($activity->act_comment_object_id, $activity->act_comment_module_id);
                        $post_activity = $profile->get_activity_data($comment_activity->act_comment_object_id, $comment_activity->act_comment_module_id);

                        $parent_comment = $profile->get_activity_post($comment_activity->act_id);
                        $parent_post = $profile->get_activity_post($post_activity->act_id);
                    }
                    $post_id = $parent_post->ID;
                }
                // $parent_activity = PeepSoActivity::get_instance();
                // $parent_activity_data = $parent_activity->get_activity_data($activity->act_comment_object_id);
                // $parent_post = $parent_activity->get_activity_post($parent_activity_data->act_id);
            }

            // check if group post single activity
            $group_id = get_post_meta($post_id, 'peepso_group_id', true);
        } else {
            // check if group page
            $module_id = $this->input->int('module_id', 0);
            if($module_id == self::MODULE_ID) {
                $group_id = $this->input->int('group_id', 0);
            }

        }

        if (isset($group_id) && $group_id > 0) {
            $group_users = new PeepSoGroupUsers($group_id);
            $list_members = $group_users->get_members();

            if(count($list_members) > 0) {
                foreach($list_members as $groupuser) {

                    if ($groupuser->user_id == get_current_user_id() || in_array($groupuser->user_id, $taggable)) {
                        continue;
                    }

                    $user = PeepSoUser::get_instance($groupuser->user_id);

                    $taggable[$groupuser->user_id] = array(
                        'id' => $groupuser->user_id,
                        'name' => trim(strip_tags($user->get_fullname())),
                        'avatar' => $user->get_avatar(),
                        'icon' => $user->get_avatar(),
                        'type' => 'group_member'
                    );
                }
            }
        }

        return $taggable;
    }

    /**
     * todo:docblock
     */
    public function filter_check_opengraph($activity) {
        // check if activity is group activity
        $group_id = get_post_meta($activity->ID, 'peepso_group_id', TRUE);
        if (!empty($group_id)) {
            // check if activity belongs to secret group
            $group_privacy = get_post_meta($group_id, 'peepso_group_privacy', TRUE);
            if ($group_privacy > PeepSoGroupPrivacy::PRIVACY_OPEN) {
                return NULL;
            }
        }

        return $activity;
    }
}

PeepSoGroupsPlugin::get_instance();

// EOF
