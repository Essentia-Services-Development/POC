<?php
/**
 * Plugin Name: PeepSo Core: Friends
 * Plugin URI: https://peepso.com
 * Description: Friend connections and "friends" privacy level
 * Author: PeepSo
 * Author URI: https://peepso.com
 * Version: 6.2.7.0
 * Copyright: (c) 2015 PeepSo, Inc. All Rights Reserved.
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: friendso
 * Domain Path: /language
 *
 * We are Open Source. You can redistribute and/or modify this software under the terms of the GNU General Public License (version 2 or later)
 * as published by the Free Software Foundation. See the GNU General Public License or the LICENSE file for more details.
 * This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY.
 */

class PeepSoFriendsPlugin
{
    private static $_instance = NULL;

    public $widgets = array(
        'PeepSoWidgetFriends',
        'PeepSoWidgetFriendsbirthday',
        'PeepSoWidgetMutualfriends',
    );

    // TODO: move this into the PeepSoFriendsModel class
    const TABLE = 'peepso_friends';
    const PLUGIN_VERSION = '6.2.7.0';
    const PLUGIN_RELEASE = ''; //ALPHA1, RC1 etc, '' for STABLE
    const PLUGIN_NAME = 'Core: Friends';
    const PLUGIN_EDD = 260;
    const PLUGIN_SLUG = 'friendso';

    const PERM_ADD_FRIEND = 'add_friend';
    const ACCESS_FRIENDS = 30;
    const MODULE_ID = 3;

    const MESSAGE_ALL = 10;
    const MESSAGE_FRIENDS = 20;

    private $url_segments;
    private $view_user_id;

    public $model;


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
        add_filter('peepso_all_plugins', array($this, 'filter_all_plugins'));
        add_filter('peepso_free_bundle_should_brand', '__return_true');

		// Translations
        add_filter('peepso_absolute_textdomain_path', function($path, $domain){
            if($domain == 'friendso') {
                return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
            }
            return $path;
        },10,2);
        add_action('plugins_loaded', function() {
            $path = str_ireplace(WP_PLUGIN_DIR, '', dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
            load_plugin_textdomain('friendso', FALSE, $path);
        });

        // Activation
        register_activation_hook(__FILE__, array(&$this, 'activate'));

        /** VERSION LOOCKED hooks **/

        if(self::ready()) {
            if (!PeepSoLicense::check_license(self::PLUGIN_EDD, self::PLUGIN_SLUG)) {
                return;
            }

            add_action('peepso_init', array(&$this, 'init'));
            add_action('peepso_user_blocked', array(&$this, 'block_unfriend'));
            add_filter('peepso_widgets', array(&$this, 'register_widgets'));
        }
    }

    /*
     * return singleton instance
     */
    public static function get_instance()
    {
        if (NULL === self::$_instance)
            self::$_instance = new self();
        return (self::$_instance);
    }

    /*
     * Callback for the 'peepso_init' action; initializes the PeepSoFriends plugin
     */
    public function init()
    {
        // set up autoloading, need these in the activate method.
        PeepSo::add_autoload_directory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR);
        PeepSoTemplate::add_template_directory(plugin_dir_path(__FILE__));


        // #5556 load this always, to fix WP 5.8 widget preview
        add_action('peepso_pre_user_query', array(&$this, 'pre_user_query'), 10, 2);

        if (is_admin())
        {
            PeepSoFriendsConfig::get_instance();
            add_action('admin_enqueue_scripts', array(&$this, 'enqueue_scripts'));

            // config tabs
            add_filter('peepso_admin_config_tabs', array(&$this, 'admin_config_tabs'));
        }
        else
        {
            add_filter('peepso_parse_mobile_menus', array(&$this, 'parse_mobile_menu_filter'));
            add_filter('peepso_profile_actions_extra', array(&$this, 'profile_actions_extra'), 10, 2);
            add_filter('peepso_activity_post_filter_access', array(&$this, 'post_filter_access'), 10, 1);
            add_filter('peepso_activity_post_clauses', array(&$this, 'filter_post_clauses'), 10, 2);

            add_filter('peepso_user_is_accessible', array(&$this, 'is_accessible'), 10, 3);
            add_filter('peepso_messages_available_recipients', array(&$this, 'get_available_message_recipients'), 10, 2);
            add_filter('peepso_check_permissions-send_message', array(&$this, 'check_message_permissions'), 20, 3);
            add_filter('peepso_check_permissions-post_view', array(&$this, 'check_post_view_permissions'), 20, 3);
            add_filter('peepso_member_buttons_extra', array(&$this, 'member_buttons_extra'), 10, 2);


            add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
            add_action('peepso_after_preference_update', array(&$this, 'preference_update'), 10, 2);

            add_action('peepso_after_member_thumb', array(&$this, 'show_mutual_friends'));
            //add_action('peepso_messages_list_header', array(&$this, 'message_list_header'));

            add_filter('peepso_profile_notification_link', array(&$this, 'profile_notification_link'), 10, 2);
            add_filter('peepso_notification_digest_section_title', function($section, $user_id) {
                if('friend_requests' == $section) {
                    $count = count( PeepSoFriendsRequests::get_instance()->get_received_requests( $user_id ));
                    $section = __('Pending friend requests', 'friendso');
                }

                return $section;
            }, 10, 2);

            add_filter('peepso_hovercard', function($data, $user_id) {
                $profile_view_count_visibility = PeepSo::get_option('profile_view_count_privacy_default', 99);
                if ($profile_view_count_visibility == 30 && ($user_id == get_current_user_id() || ($user_id != get_current_user_id() && PeepSoFriendsModel::get_instance()->are_friends($user_id, get_current_user_id())))) {
                    $user = PeepSoUser::get_instance($user_id);
                    $data['views'] = $user->get_view_count();
                }

                return $data;
            }, 10, 2);

            PeepSoFriendsShortcode::register_shortcodes();
        }

        add_filter('peepso_privacy_access_levels', array(&$this, 'privacy_access_levels'), 10, 1);

        add_filter('peepso_profile_alerts', array(&$this, 'profile_alerts'), 10, 1);
        add_filter('peepso_widgets', array(&$this, 'register_widgets'));
        add_filter('peepso_access_types', array(&$this, 'filter_access_types'));

        // Hooks into navigation profile pages and "me" widget
        add_filter('peepso_navigation', array(&$this, 'filter_peepso_navigation'));
        add_filter('peepso_navigation_profile', array(&$this, 'filter_peepso_navigation_profile'));

        add_action('peepso_profile_segment_friends', array(&$this, 'peepso_profile_segment_friends'));
        add_filter('peepso_live_notifications', array(&$this, 'get_requests_count'), 10, 1);
        add_filter('peepso_rewrite_profile_pages', array(&$this, 'peepso_rewrite_profile_pages'));

        add_filter('peepso_config_email_messages', array('PeepSoFriendsConfig', 'config_email'));

        add_filter('peepso_stream_id_list', function ($filters) {
            $filters['friends'] = array(
                'order' => 10,
                'label' => __('Friends', 'friendso'),
                'label_warning' => '',
                'desc' =>   __('Posts from your friends', 'friendso'),
                'icon'=> 'gcis gci-user-friends ps-js-icon',
            );
            return $filters;
        },10,1);

        // Compare last version stored in transient with current version
        if( $this::PLUGIN_VERSION.$this::PLUGIN_RELEASE != PeepSo3_Mayfly::get($mayfly = 'peepso_'.$this::PLUGIN_SLUG.'_version')) {
            PeepSo3_Mayfly::set($mayfly, $this::PLUGIN_VERSION.$this::PLUGIN_RELEASE);
            $this->activate();
        }


        if(class_exists('PeepSoMaintenanceFactory') && class_exists('PeepSoMaintenanceFriends')) {
            new PeepSoMaintenanceFriends();
        }

        // @since 1.9.9 - simply hook into the WP user creation event
        add_action('user_register', array(&$this, 'autofriends_new'));
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
        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'friendsshortcode.php');
        $install = new PeepSoFriendsInstall();
        $res = $install->plugin_activation();
        if (FALSE === $res) {
            // error during installation - disable
            deactivate_plugins(plugin_basename(__FILE__));
        }
        return (TRUE);

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
                <?php echo sprintf(__('The %s plugin requires the PeepSo plugin to be installed and activated.', 'friendso'), self::PLUGIN_NAME);?>
                <a href="plugin-install.php?tab=plugin-information&plugin=peepso-core&TB_iframe=true&width=772&height=291" class="thickbox">
                    <?php echo __('Get it now!', 'friendso');?>
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


    public function license_notice()
    {
        PeepSo::license_notice(self::PLUGIN_NAME, self::PLUGIN_SLUG);
    }

    public function license_notice_forced()
    {
        PeepSo::license_notice(self::PLUGIN_NAME, self::PLUGIN_SLUG, true);
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
            if (strlen($widget)>=5) require_once($widget_dir . $widget);
        }
        return array_merge($widgets, $this->widgets);
    }


    /**
     * Adds the Friends dropdown and Friend requests notification to the navigation bar.
     * @param  array $navbar An array of navigation menus
     * @return array $navbar
     */
    public function filter_peepso_navigation($navigation)
    {
        $friends_requests = PeepSoFriendsRequests::get_instance();

        $inv = array(
            'href' => PeepSoFriendsPlugin::get_url(get_current_user_id(), 'requests'),
            'icon' => 'gcis gci-user-friends',
            'class' => 'ps-notif--friends ps-js-friends-notification',
            'title' => __('Friend Requests', 'friendso'),
            'label' => __('Friend Requests', 'friendso'),
            'count' => count($friends_requests->get_received_requests()),

            'primary' => FALSE,
            'secondary' => TRUE,
            'mobile-primary' => FALSE,
            'mobile-secondary' => TRUE,

            'widget' => FALSE,
            'notifications' => TRUE,
            'icon-only'         => TRUE,
        );

        if ($inv['count'] > 0){
            $inv['class'] .= ' friends-notification';
        }

        $navigation['friends-notification'] = $inv;

        return ($navigation);
    }

    /**
     * Change order menus in mobile view
     * @param  array $navbar An array of navigation menus
     * @return array $navbar
     */
    public function parse_mobile_menu_filter($menus)
    {
        foreach ($menus as $index => &$menu) {
            if ($index == 'friends-notification') {
                $menu['order'] = 70;
            }
        }

        return $menus;
    }

    /**
     * Add additional profile action buttons.
     *
     * @param array $acts
     * @param int $user_id
     * @return array
     */
    public function profile_actions_extra($acts, $user_id)
    {
        // TODO: if check_permissions() tests user_id != get_current_user_id() remove it here; otherwise add it to the checks inside check_permissions()
        // TODO: we shouldn't ever need to do any additional tests than check_permissions()
        if ($user_id != get_current_user_id() && PeepSo::check_permissions($user_id, PeepSo::PERM_PROFILE_VIEW, get_current_user_id())) {
            $actions = $this->get_friend_status(get_current_user_id(), $user_id);

            foreach ($actions as $key => $action)
                $acts['friends_' . $key] = array(
                    'label' => $action['label'],
                    'class' => $action['class'],
                    'title' => $action['title'],
                    'click' => $action['click'],
                    'extra' => $action['extra'],
                );
        }

        return ($acts);
    }

    /**
     * Add the Friends Only option to the privacy options
     * @param  array $access The default privacy options
     * @return array
     */
    public function privacy_access_levels($access)
    {
        $access[self::ACCESS_FRIENDS] = array('icon' => 'gcis gci-user', 'label' => __('Friends Only', 'friendso'));
        ksort($access);
        return ($access);
    }

    /**
     * Append ACCESS_FRIENDS to the access string part of the query.
     * @param  string $access The original access string
     * @return string
     */
    public function post_filter_access($access)
    {
        $access .= " OR (`act_access`=" . self::ACCESS_FRIENDS . ")";

        return ($access);
    }

    /**
     * Modify the clauses to filter posts for friends
     * @param  array $clauses
     * @param  int $user_id The owner of the activity stream
     * @return array
     */
    public function filter_post_clauses($clauses, $user_id = NULL)
    {
        $user = PeepSoUser::get_instance($user_id);

        $is_friend_stream_filter = isset($_REQUEST['stream_id']) && 'friends' === $_REQUEST['stream_id'] && FALSE === strpos($clauses['where'], PeepSoActivityStream::CPT_COMMENT);


        if ((!is_null($user_id) && !current_user_can('administrator') && $user->get_user_role() != 'admin') || $is_friend_stream_filter) {

            global $wpdb;

            if (PeepSo::is_dev_mode('new_friends_queries')) {

                $join = ' LEFT JOIN `' . $wpdb->prefix . 'peepso_friends_cache` `friends_cache` ON (`friend_id` = `' . $wpdb->posts . '`.`post_author` AND `user_id`=%1$d) ';

                $clauses['join'] .= sprintf($join, $user_id);

                $clauses['where'] .= $wpdb->prepare(" AND IF(`{$wpdb->posts}`.`post_author` <> %d AND `act_access`=" . self::ACCESS_FRIENDS . ",  IF(`friends_cache`.`friend_id` IS NOT NULL, TRUE, FALSE), TRUE)  ", $user_id);

                if ($is_friend_stream_filter) {
                    $clauses['where'] .= $wpdb->prepare(" AND `{$wpdb->posts}`.`post_author` <> %d", $user_id);
                }
            } else {
                $join = ' LEFT JOIN `' . $wpdb->prefix . self::TABLE . '` `friends` ON ' .
                    ' (`fnd_user_id` = `' . $wpdb->posts . '`.`post_author` AND `fnd_friend_id`=%1$d) ' .
                    ' OR (`fnd_user_id` = %1$d AND `fnd_friend_id`=`' . $wpdb->posts . '`.`post_author`) ' .
                    ' OR (`fnd_user_id` = %1$d AND `fnd_friend_id`=`act_owner_id`) ' .
                    ' OR (`fnd_user_id` = `act_owner_id` AND `fnd_friend_id` = %1$d)  ';

                $clauses['join'] .= sprintf($join, $user_id);

                $clauses['where'] .= $wpdb->prepare(" AND IF(`{$wpdb->posts}`.`post_author` <> %d AND `act_access`=" . self::ACCESS_FRIENDS . ",  IF(`friends`.`fnd_user_id` IS NOT NULL, TRUE, FALSE), TRUE)  ", $user_id);

                if ($is_friend_stream_filter) {
                    $clauses['where'] .= $wpdb->prepare(" AND ((`{$wpdb->posts}`.`post_author` <> %d AND `friends`.`fnd_user_id` IS NOT NULL) OR `{$wpdb->posts}`.`post_author` = %d)", $user_id, $user_id);
                }
            }
        }

        return ($clauses);
    }

    /*
     * enqueue scripts needed
     */
    public function enqueue_scripts()
    {
        $user = PeepSoUser::get_instance(get_current_user_id());

        wp_register_script('peepso-npm', PeepSo::get_asset('js/npm-expanded.min.js'),
            array('peepso'), PeepSo::PLUGIN_VERSION, 'all');

        wp_register_script('peepso-observer', PeepSo::get_asset('js/observer.min.js'),
            array(), PeepSo::PLUGIN_VERSION, TRUE);

        wp_enqueue_script('peepso-friends',
            PeepSo::get_asset('js/peepsofriends.min.js', __FILE__),
            array('peepso', 'peepso-npm'), self::PLUGIN_VERSION,
            TRUE
        );

        wp_register_style('autocompleteautofriends-css',
            PeepSo::get_asset('css/jquery.auto-complete.css', __FILE__),
            NULL, self::PLUGIN_VERSION, 'all');

        wp_register_script('adminuserautofriends-js',
            PeepSo::get_asset('js/adminautofriends.js', __FILE__),
            array('peepso'), self::PLUGIN_VERSION, TRUE);

        wp_localize_script('peepso', 'peepsofriendsdata',
            array(
                'friend_requests_page' => PeepSoFriendsPlugin::get_url(get_current_user_id(), 'requests'),
                'removefriend_popup_title' => __('Remove Friend', 'friendso'),
                'removefriend_popup_content' => __('Are you sure?', 'friendso'),
                'removefriend_popup_save' => __('Remove Friend', 'friendso'),
                'removefriend_popup_cancel' => __('Cancel', 'friendso'),
                'notification_header' => PeepSoTemplate::exec_template('friends', 'notification-popover-header', NULL, TRUE)
            )
        );

        wp_enqueue_script(self::PLUGIN_SLUG,
            PeepSo::get_asset('js/bundle.min.js', __FILE__),
            array('peepso'), self::PLUGIN_VERSION, TRUE );

        add_filter('peepso_data', function($data) {
            $data['friends'] = array(
                'friend_requests_page' => PeepSoFriendsPlugin::get_url(get_current_user_id(), 'requests'),
                'template_notification_header' => PeepSoTemplate::exec_template('friends', 'notification-popover-header', NULL, TRUE),
                'template_remove_friend' => PeepSoTemplate::exec_template('friends', 'dialog-remove-friend', NULL, TRUE)
            );

            return $data;
        }, 10, 1);

        if (apply_filters('peepso_free_bundle_should_brand', FALSE)) {
            wp_add_inline_script('peepso', "setTimeout(() => peepso.observer.do_action('show_branding'), 1000);");
        }
    }

    /**
     * Registers a tab in the PeepSo Config Toolbar
     * PS_FILTER
     *
     * @param $tabs array
     * @return array
     */
    public function admin_config_tabs( $tabs )
    {
        $tabs['friends'] = array(
            'label' => __('Friends', 'friendso'),
            'icon' => 'https://cdn.peepso.com/icons/plugins/'.self::PLUGIN_EDD.'.svg',
            'tab' => 'friends',
            'description' => __('Friends', 'friendso'),
            'function' => 'PeepSoConfigSectionFriends',
            'cat'   => 'core',
        );

        return $tabs;
    }

    /**
     * Returns an array that can be used to actions depending on the two user's friend status.
     * @param  int $from_id The user sending the friend request.
     * @param  int $to_id The user receiving the friend request.
     * @return array An array of actions.
     */
    public function get_friend_status($from_id, $to_id)
    {
        if ($from_id === $to_id)
            return (FALSE);

        $ret = array();

        if (PeepSoFriendsModel::get_instance()->are_friends($from_id, $to_id)) {
            $ret['unfriend'] = array(
                'label' => __('Friend', 'friendso'),
                'class' => 'ps-member__action ps-member__action--unfriend ps-focus__cover-action ps-js-friend-remove',
                'click' => '',
                'loading' => TRUE,
                'icon' => 'gcis gci-user-times',
                'title' => __('Remove friend', 'friendso'),
                'li-class' => 'friend-request-option',
                'extra' => ' data-user-id="' . $to_id . '" data-text-unfriend="' . esc_attr( __('Unfriend', 'friendso') ) . '"'
            );

            return ($ret);
        }

        $request_sent = PeepSoFriendsRequests::request_status($from_id, $to_id);
        $request_id = PeepSoFriendsRequests::get_request_id($from_id, $to_id);

        if (PeepSoFriendsRequests::STATUS_SENT === $request_sent) {
            $ret['cancel'] = array(
                'label' => __('Cancel Friend Request', 'friendso'),
                'class' => 'ps-member__action ps-member__action--cancel ps-focus__cover-action ps-js-friend-cancel-request',
                'click' => '',
                'loading' => TRUE,
                'icon' => 'gcis gci-user-times',
                'title' => __('Cancel Friend Request', 'friendso'),
                'li-class' => 'friend-request-option',
                'extra' => ' data-user-id="' . $to_id . '" data-request-id="' . $request_id . '"'
            );

            return ($ret);
        }

        if (PeepSoFriendsRequests::STATUS_RECEIVED === $request_sent) {
            $ret['accept'] = array(
                'label' => __('Accept', 'friendso'),
                'class' => 'ps-member__action ps-member__action--accept ps-focus__cover-action ps-js-friend-accept-request',
                'click' => '',
                'loading' => TRUE,
                'icon' => 'gcis gci-user-check',
                'title' => __('Accept Request', 'friendso'),
                'li-class' => 'friend-request-option',
                'extra' => ' data-user-id="' . $to_id . '" data-request-id="' . $request_id . '"'
            );

            $ret['ignore'] = array(
                'label' => __('Reject', 'friendso'),
                'class' => 'ps-member__action ps-member__action--reject ps-focus__cover-action ps-js-friend-reject-request',
                'click' => '',
                'loading' => TRUE,
                'icon' => 'gcis gci-user-minus',
                'title' => __('Reject Request', 'friendso'),
                'li-class' => 'friend-request-option',
                'extra' => ' data-user-id="' . $to_id . '" data-request-id="' . $request_id . '"'
            );

            return ($ret);
        }

        // Allow third parties and UserLimits to take over before we check other conditions
        $can_request = apply_filters('peepso_permissions_friends_request', TRUE);

        // Check max friends limit
        if($can_request) {

            $limit = PeepSo::get_option_new('friends_max_amount');
            $limit = intval($limit);

            if($limit > 0) {
                $PeepSoFriends = PeepSoFriends::get_instance();
                $from_over_limit = ($PeepSoFriends->get_num_friends($from_id) >= $limit);
                $to_over_limit = ($PeepSoFriends->get_num_friends($to_id) >= $limit);

                if( $from_over_limit || $to_over_limit ) {
                    $can_request = FALSE;


                    if($from_over_limit) {
                        $label = sprintf(__('You already have %d friends', 'friendso'), $limit);
                    } else {
                        $label = sprintf(__('%s already has %d friends', 'friendso'),PeepSoUser::get_instance($to_id)->get_firstname(), $limit);
                    }


                    $ret['cannot_add'] = array(
                        'label' => $label,
                        'class' => 'ps-member__action ps-member__action--cannot-add ps-focus__cover-action',
                        'click' => '',
                        'loading' => TRUE,
                        'title' => __('Friends limit reached', 'friendso'),
                        'icon' => 'gcis gci-user-times',
                        'title' => '',
                        'li-class' => 'friend-request-option',
                        'extra' => ' data-user-id="' . $to_id . '"'
                    );
                }
            }

        }

        if($can_request) {
            $ret['add'] = array(
                'label' => __('Add Friend', 'friendso'),
                'class' => 'ps-member__action ps-member__action--add ps-focus__cover-action ps-js-friend-send-request',
                'click' => '',
                'loading' => TRUE,
                'icon' => 'gcis gci-user-plus',
                'title' => __('Add as your friend', 'friendso'),
                'li-class' => 'friend-request-option',
                'extra' => ' data-user-id="' . $to_id . '"'
            );
        }

        return ($ret);
    }


    /**
     * Hooks to `peepso_user_is_accessible` to add the ACCESS_FRIENDS check
     * @param  boolean $default The value from the filter
     * @param  int $access The permission requested
     * @param  PeepSoUser $user
     * @return boolean
     */
    public function is_accessible($default, $access, $user_id)
    {
        // Default will always be TRUE if (get_current_user_id() === $user->id) || PeepSo::is_admin())
        // so no need to check if otherwise.
        if (FALSE === $default && self::ACCESS_FRIENDS === $access) {
            return (PeepSoFriendsModel::get_instance()->are_friends($user_id, get_current_user_id()));
        }

        return ($default);
    }


    /**
     * Adds Friend JOIN and WHERE clauses when searching for users.
     * @param  WP_User_Query $wp_user_query  The current WP_User_Query object.
     * @param  int $user_id The user ID to be referenced.
     */
    public function pre_user_query($wp_user_query, $user_id)
    {
        global $wpdb;

        $wp_user_query->query_from .= '
				LEFT JOIN `' . $wpdb->prefix . PeepSoFriendsPlugin::TABLE . '` `ps_fnd`
			ON
				(`ps_fnd`.`fnd_user_id` = ' . $user_id . ' AND `ps_fnd`.`fnd_friend_id` = `' . $wpdb->users . '`.`ID`)
				OR
				(`ps_fnd`.`fnd_friend_id` = ' . $user_id . ' AND `ps_fnd`.`fnd_user_id` = `' . $wpdb->users . '`.`ID`)
		';

        // Friends
        $wp_user_query->query_where .= '
			AND
				IF (`acc`.`usr_profile_acc` = ' . self::ACCESS_FRIENDS . ', `ps_fnd`.`fnd_id` IS NOT NULL, TRUE)
		';

    }

    /**
     * Sets message recipients to friends only if defined in the config.
     * @param array $recipients Array of user display names with user ID as keys
     * @param array $args The array of arguments to be used in WP_User_Query
     * @return array
     */
    public function get_available_message_recipients($recipients, $args)
    {
        $friends = PeepSoFriendsModel::get_instance()->get_friends(get_current_user_id(), $args);
        $iterator = $friends->getIterator();
        $friends = array();

        while ($iterator->valid()) {
            $friend = PeepSoUser::get_instance($iterator->current());
            $friends[] = $friend->get_id();
            $iterator->next();
        }

        if (count($recipients) >= 1) {
            foreach ($recipients as $key => $recipient) {
                $recipients[ $key ]['is_friend'] = in_array($recipient['id'], $friends) ? TRUE : FALSE;
            }
        }

        return ($recipients);
    }

    /*
     * Check if author has permission to send a message to owner depending on the friends_can_send_message_to option
     * @param mixed $can_access Defaults to -1 , return as TRUE or FALSE depending on permission
     * @param int $owner The user id of the owner of the Activity Stream
     * @param int $author The author requesting permission to perform the action
     * @return mixed Defaults to -1 , return as TRUE or FALSE depending on permission
     */
    public function check_message_permissions($can_access, $owner, $author)
    {
        // Do nothing if option is not set to "Friends Only"
        if (self::MESSAGE_FRIENDS !== intval(PeepSo::get_option('friends_can_send_message_to')))
            return ($can_access);

        return (PeepSoFriendsModel::get_instance()->are_friends($owner, $author));
    }

    /*
     * Check if author has permission to view a post
     * @param mixed $can_access Defaults to -1 , return as TRUE or FALSE depending on permission
     * @param int $owner The user id of the owner of the Activity Stream
     * @param int $author The author requesting permission to perform the action
     * @return mixed Defaults to -1 , return as TRUE or FALSE depending on permission
     */
    public function check_post_view_permissions($can_access, $owner, $author)
    {
        return (PeepSoFriendsModel::get_instance()->are_friends($owner, $author));
    }

    /**
     * Append profile alerts definition for peepsofriends
     */
    public function profile_alerts($alerts)
    {
        $alerts['friends'] = array(
            'title' => __('Friends', 'friendso'),
            'items' => array(
                array(
                    'label' => __('Someone sent me a friend request', 'friendso'),
                    'setting' => 'friends_requests',
                    'loading' => TRUE,
                )
            ),
        );
        // NOTE: when adding new items here, also add settings to /install/activate.php site_alerts_ sections
        return ($alerts);
    }


    public function member_buttons_extra($buttons, $user_id)
    {
        $new_buttons = $this->get_friend_status(get_current_user_id(), $user_id);
        foreach ($new_buttons as $i => $value) {
            unset($new_buttons[$i]['icon']);
        }

        $buttons = array_merge($buttons, $new_buttons);

        return ($buttons);
    }


    /**
     * `peepso_after_member_thumb` hook
     * Echoes the number of mutual friends the current user has with $user_id
     * @param  int $user_id The user to get mutual friends
     */
    public function show_mutual_friends($user_id)
    {
        if( get_current_user_id() == $user_id ) {
            return;
        }

        $mutual_friends = PeepSoFriendsModel::get_instance()->get_mutual_friends_count(get_current_user_id(), $user_id);

        if ($mutual_friends === 0) {
            #echo __('No mutual friends', 'pepesofriends');
        }
        else {
            //echo $mutual_friends . ' ' . _n(' mutual friend', ' mutual friends', $mutual_friends, 'friendso');
            echo '<a class="ps-friends__mutual" href="#" onclick="psfriends.show_mutual_friends(' . get_current_user_id() . ', ' . $user_id . '); return false;"><i class="gcis gci-user-friends"></i>'. $mutual_friends . ' ' . _n(' mutual friend', ' mutual friends', $mutual_friends, 'friendso') . '</a>';
        }
    }

    /**
     * Adds the "Write" button to the messages list page, when the PeepSoMessages plugin is also enabled.
     */
    public function message_list_header()
    {
        PeepSoTemplate::exec_template('messages', 'list-header');
    }

    /**
     * Add access types hook required for PeepSoPMPro plugin
     * @param array $types existing access types
     * @return array $types new access types
     */
    public function filter_access_types($types)
    {
        $types[PeepSoFriendsShortcode::SHORTCODE_FRIENDS] = array(
            'name' => __('Friends', 'friendso'),
            'module' => self::MODULE_ID,
        );

        return ($types);
    }

    // Methods to plug photos into profile pages
    public function peepso_profile_segment_friends($url_segments)
    {
        $this->url_segments = $url_segments;
        $pro = PeepSoProfileShortcode::get_instance();
        $this->view_user_id = PeepSoUrlSegments::get_view_id($pro->get_view_user_id());

        // Grab and run the shortcode
        $sc = PeepSoFriendsShortcode::get_instance();
        echo $sc->profile_segment($this->view_user_id, $this->url_segments);
    }

    public function peepso_rewrite_profile_pages($pages)
    {
        return array_merge($pages, array('friends'));
    }

    public function filter_peepso_navigation_profile($links)
    {
        $links['friends'] = array(
            'href' => 'friends',
            'label'=> __('Friends', 'friendso'),
            'icon' => 'gcis gci-user-friends'
        );

        return $links;
    }

    public static function get_url($view_id = 0, $page='friends')
    {
        $user = PeepSoUser::get_instance($view_id);

        switch($page) {
            case 'requests':
                return PeepSoFriendsPlugin::get_url($view_id).'/requests';
                break;
            default:
                return $user->get_profileurl().'friends';
        }
    }

    /**
     * Get friend requests count for live notification.
     */
    public function get_requests_count(PeepSoAjaxResponse $resp)
    {
        // Refresh the auxiliary table if MayFly expired
        PeepSoFriendsCache::reset_friends_cache_table([get_current_user_id()], FALSE);


        $friends_requests = PeepSoFriendsRequests::get_instance();
        $friends_requests_count = count($friends_requests->get_received_requests());
        $data = array('count' => $friends_requests_count);

        $resp->data['ps-js-friends-notification'] 			= array();
        $resp->data['ps-js-friends-notification'] 			= $data;
        $resp->data['ps-js-friends-notification']['el'] 	= 'ps-js-friends-notification';

        $resp->success(TRUE);
        return $resp;

    }

    /**
     * Unfriend the user when peepso_user_block action is fired
     * @param $args['from'=>$int,'to'=>$int]
     */
    public function block_unfriend($args)
    {
        $model = PeepSoFriendsModel::get_instance();
        $model->delete($args['from'], $args['to']);
    }

    /**
     * Modify link notification
     * @param array $link
     * @param array $note_data
     * @return string $link
     */
    public function profile_notification_link($link, $note_data) {

        if ('friends_requests' === $note_data['not_type']) {

            $author = PeepSoUser::get_instance($note_data['not_from_user_id']);

            $link = $author->get_profileurl();

        }

        return $link;
    }

    /**
     * Perform autofriends while user successfully created
     * @param integer $to_user_id
     */
    public function autofriends_new($to_user_id) {
        $users = new PeepSoUserAutoFriendsModel();
        $items = $users->get_users();

        $limit = PeepSo::get_option_new('friends_max_amount');
        $limit = intval($limit);
        $PeepSoFriends = PeepSoFriends::get_instance();

        if(count($items) > 0 && $limit > 0) {

            $count = 1;
            foreach ($items as $af) {
                $from_user_id = $af['af_user_id'];
                $from_over_limit = ($PeepSoFriends->get_num_friends($from_user_id) >= $limit);

                if( !$from_over_limit && $count < $limit ) {
                    PeepSoFriendsModel::get_instance()->add_friend($from_user_id, $to_user_id);
                    $count++;
                }
            }
        }
    }
}

PeepSoFriendsPlugin::get_instance();

// EOF
