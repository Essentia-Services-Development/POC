<?php
/**
 * Plugin Name: PeepSo Core: User Limits
 * Plugin URI: https://peepso.com
 * Description: Limit user privileges and/or hide them from PeepSo user listings based on role and profile completeness
 * Author: PeepSo
 * Author URI: https://peepso.com
 * Version: 6.2.7.0
 * Copyright: (c) 2016 PeepSo, Inc. All Rights Reserved.
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: peepsolimitusers
 * Domain Path: /language
 *
 * We are Open Source. You can redistribute and/or modify this software under the terms of the GNU General Public License (version 2 or later)
 * as published by the Free Software Foundation. See the GNU General Public License or the LICENSE file for more details.
 * This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY.
 *
 */


class peepsolimitusers
{
    private static $_instance = NULL;

    private $all_roles = array();
    private $roles = array();


    const PLUGIN_NAME	 = 'Core: User Limits';
    const PLUGIN_SLUG 	 = 'limitusers';
    const PLUGIN_EDD 	 = 97020;
    const PLUGIN_VERSION = '6.2.7.0';
    const PLUGIN_RELEASE = ''; //ALPHA1, RC1 etc, '' for STABLE

    public $debug;
    public $sections;
    public $sections_user_descriptions;
    public $sections_user_icons;
    public $completeness;
    public $limits;

    public $widgets = array(
        'PeepSoWidgetLimitUsers',
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
                return;
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

    /**
	 * Loads the translation file for the plugin
	 */
	public function load_textdomain()
	{
		$path = str_ireplace(WP_PLUGIN_DIR, '', dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
		load_plugin_textdomain('peepsolimitusers', FALSE, $path);
    }

    public function debug() {
        echo "<table><tr><td>Section</td><td>Reason</td><td>Limit</td></tr>";
        foreach($this->sections as $section) {
            if($this->debug[$section]) {
                echo "<tr><td>$section</td><td>{$this->debug[$section]['reason']}</td><td>{$this->debug[$section]['limit']}</td></tr>";
            }
        }

        echo "</table>";
    }

    public function debug_formatted() {
        if(is_array($this->debug) && count($this->debug)) {
            $output = array();

            foreach ($this->debug as $section => $details) {


                if ('role' == $details['reason'] && PeepSo::get_option_new('limitusers_roles_show')) {
                    $output['role'][$details['limit']][] = $section;
                }

                if ('profile' == $details['reason']) {
                    $output['profile'][$details['limit']][] = $section;
                }

                if ('avatar' == $details['reason']) {
                    $output['avatar'][] = $section;
                }

                if ('cover' == $details['reason']) {
                    $output['cover'][] = $section;
                }
            }

            if(count($output)) {
                PeepSoTemplate::exec_template('limitusers', 'debug-formatted', array('data' => $output, 'sections_descriptions' => $this->sections_user_descriptions, 'sections_icon' => $this->sections_user_icons));
            }
        }
    }

    public function init()
    {
        PeepSo::add_autoload_directory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR);
        PeepSoTemplate::add_template_directory(plugin_dir_path(__FILE__));

        $this->sections = array('hide', 'posts', 'repost', 'comments', 'reactions');

        $this->sections_user_descriptions = array(
            'hide'      => __('show on members page', 'peepsolimitusers'),
            'posts'     => __('add new posts', 'peepsolimitusers'),
            'repost'     => __('repost', 'peepsolimitusers'),
            'comments'  => __('comment on posts', 'peepsolimitusers'),
            'reactions'  => __('react on posts and comments', 'peepsolimitusers')
        );

        $this->sections_user_icons = array(
            'hide'      => '<i class="gcis gci-user-friends"></i>',
            'posts'     => '<i class="gcis gci-edit"></i>',
            'repost'     => '<i class="gcis gci-retweet"></i>',
            'comments'  => '<i class="gcis gci-comment"></i>',
            'reactions'  => '<i class="gcis gci-thumbs-up"></i>'
        );

		if (PeepSo::get_option_new('post_backgrounds_enable')) {
            $this->sections[] = 'post_backgrounds';
            $this->sections_user_descriptions['post_backgrounds'] = __('create posts with backgrounds', 'peepsolimitusers');
            $this->sections_user_icons['post_backgrounds'] = '<i class="gcis gci-users"></i>';
        }

        if (class_exists('PeepSoFriendsPlugin')) {
            $this->sections[] = 'friends';
            $this->sections_user_descriptions['friends'] = __('send friend requests', 'peepsolimitusers');
            $this->sections_user_icons['friends'] = '<i class="gcis gci-user-friends"></i>';
        }

        if (class_exists('PeepSoGroupsPlugin')) {
            $this->sections[] = 'groups';
            $this->sections_user_descriptions['groups'] = __('join groups', 'peepsolimitusers');
            $this->sections_user_icons['groups'] = '<i class="gcis gci-users"></i>';

            $this->sections[] = 'groups_create';
            $this->sections_user_descriptions['groups_create'] = __('create groups', 'peepsolimitusers');
            $this->sections_user_icons['groups_create'] = '<i class="gcis gci-users"></i>';
        }

        if (class_exists('PeepSoMessagesPlugin')) {
            $this->sections[] = 'messages';
            $this->sections_user_descriptions['messages'] = __('write new messages', 'peepsolimitusers');
            $this->sections_user_icons['messages'] = '<i class="gcis gci-envelope"></i>';
        }

        if (class_exists('PeepSoSharePhotos')) {
            $this->sections[] = 'photos';
            $this->sections_user_descriptions['photos'] = __('add photos', 'peepsolimitusers');
            $this->sections_user_icons['photos'] = '<i class="gcis gci-camera"></i>';
        }

        if(class_exists('PeepSoPolls') || class_exists('PeepSoPollsPlugin')) {
            $this->sections[] = 'polls';
            $this->sections_user_descriptions['polls'] = __('add polls', 'peepsolimitusers');
            $this->sections_user_icons['polls'] = '<i class="gcis gci-list"></i>';
        }

        if (class_exists('PeepSoVideos')) {
            $this->sections[] = 'videos';
            $this->sections_user_descriptions['videos'] = __('upload videos', 'peepsolimitusers');
            $this->sections_user_icons['videos'] = '<i class="gcib gci-youtube"></i>';

            $this->sections[] = 'videos_embed';
            $this->sections_user_descriptions['videos_embed'] = __('add videos via link', 'peepsolimitusers');
            $this->sections_user_icons['videos_embed'] = '<i class="gcib gci-youtube"></i>';

            $this->sections[] = 'audio';
            $this->sections_user_descriptions['audio'] = __('upload audio', 'peepsolimitusers');
            $this->sections_user_icons['audio'] = '<i class="gcis gci-music"></i>';

            $this->sections[] = 'audio_embed';
            $this->sections_user_descriptions['audio_embed'] = __('add audio via link', 'peepsolimitusers');
            $this->sections_user_icons['audio_embed'] = '<i class="gcis gci-music"></i>';
        }

        if (class_exists('PeepSo_WPEM_Plugin')) {
            $this->sections[] = 'wpem_create';
            $this->sections_user_descriptions['wpem_create'] = __('create and manage events', 'peepsolimitusers');
            $this->sections_user_icons['wpem_create'] = '<i class="gcis gci-calendar"></i>';

            if(PeepSo::get_option_new('wpem_rsvp_enable')) {
                $this->sections[] = 'wpem_rsvp';
                $this->sections_user_descriptions['wpem_rsvp'] = __('RSVP events', 'peepsolimitusers');
                $this->sections_user_icons['wpem_rsvp'] = '<i class="gcis gci-calendar"></i>';
            }
        }

        if (class_exists('PeepSoWPJM')) {
            $this->sections[] = 'wpjm_create';
            $this->sections_user_descriptions['wpjm_create'] = __('create jobs', 'peepsolimitusers');
            $this->sections_user_icons['wpjm_create'] = '<i class="gcis gci-calendar"></i>';
        }

        if (class_exists('PeepSoFileUploads')) {
            $this->sections[] = 'files';
            $this->sections_user_descriptions['files'] = __('add file', 'peepsolimitusers');
            $this->sections_user_icons['files'] = '<i class="gcis gci-file"></i>';
        }

        if (is_admin()) {
            add_action('admin_init', array(&$this, 'peepso_check'));
            add_filter('peepso_admin_config_tabs', array(&$this, 'admin_config_tabs'));

            add_action('admin_enqueue_scripts', function(){
                wp_enqueue_script('peepso-admin-config-limitusers',
                    PeepSo::get_asset('js/admin/config-limitusers.js', __FILE__),
                    array('jquery'), self::PLUGIN_VERSION, TRUE);
            });
        }

        // Check license
        if (!PeepSoLicense::check_license(self::PLUGIN_EDD, self::PLUGIN_SLUG)) {
            return false;
        }

        add_action('peepso_action_render_navbar_after', function() {

            $debug = PeepSoLimitUsers::get_instance()->debug_formatted();
            if(!empty($debug)) { ?>
                <div class="ps-alert ps-alert-warning">
                    <?php echo $debug; ?>
                </div>
        <?php
            }
        });

        add_filter('the_content', function($content) {
            global $post;
            if ($post instanceof WP_Post && $post->post_type == 'post' && is_single()) {
                $debug = PeepSoLimitUsers::get_instance()->debug_formatted();
            }
            return $content;
        }, 10);

        // only run for logged in users (non admin)
        if(get_current_user_id() && !PeepSo::is_admin()) {

            #add_action('peepso_action_render_profile_completeness_message_after', array(&$this,'debug'));

            // check cached profile completeness
            $this->completeness = get_user_option('peepso_profile_completeness');

            // The data is somehow not in options, get it from the model
            if( !is_int($this->completeness) ) {
                $user = PeepSoUser::get_instance();
                $user->profile_fields->load_fields();
                $stats = $user->profile_fields->profile_fields_stats;

                $this->completeness = $stats['completeness'];
            }

            // all WP roles
            global $wp_roles;
            $this->all_roles = $wp_roles->roles;

            // current user roles
            $user = wp_get_current_user();
            $this->roles=$user->roles;

            // array of disabling methods to call
            $exec_sections = array();

            // role based logic
            $mode = PeepSo::get_option('limitusers_roles_mode',0); // 0 = exclude, 1 = include


            foreach($this->sections as $section) {
                $found = 0;
                // Process roles
                foreach ($this->all_roles as $role_key => $role) {

                    if($mode==0) {
                        // Skip if user doesn't have this role
                        if (!in_array($role_key, $this->roles)) {
                            continue;
                        }
                        // Disable role for section
                        if (PeepSo::get_option("limitusers_{$section}_role_{$role_key}", FALSE)) {
                            $this->limits[$section] = $role['name'];
                            $exec_sections[$section] = 'role';
                        }
                    }

                    if($mode==1) {

                        // Skip if this role is not enabled
                        if (!PeepSo::get_option("limitusers_{$section}_role_{$role_key}", FALSE)) {
                            continue;
                        }

                        // User has this role
                        if (in_array($role_key, $this->roles)) {
                            $found = 1;
                            break;
                        }
                    }
                }

                if($mode ==1 && !$found) {
                    $this->limits[$section] = 'ROLE';
                    $exec_sections[$section] = 'role';
                }

                // process profile completeness only if it was NOT disabled on role basis
                if(!array_key_exists($section, $exec_sections)) {
                    // store limits for debug
                    $this->limits[$section] = PeepSo::get_option("limitusers_{$section}_completeness_min", 0);

                    if ($this->limits[$section] > $this->completeness) {
                        $exec_sections[$section] = 'profile';
                    }
                }

                // process avatar rule only if it was NOT disabled on role or completeness basis
                if(!array_key_exists($section, $exec_sections)) {
                    if(PeepSo::get_option("limitusers_{$section}_avatar", 0)) {
                        $PeepSoUser = PeepSoUser::get_instance();
                        if (!$PeepSoUser->peepso_user['usr_avatar_custom']) {
                            $exec_sections[$section] = 'avatar';
                        }
                    }
                }

                // process cover rule only if it was NOT disabled on role or completeness basis
                if(!array_key_exists($section, $exec_sections)) {
                    if(PeepSo::get_option("limitusers_{$section}_cover", 0)) {
                        $PeepSoUser = PeepSoUser::get_instance();
                        if (!$PeepSoUser->peepso_user['usr_cover_photo']) {
                            $exec_sections[$section] = 'cover';
                        }
                    }
                }
            }

            // call disabling methods for all sections
            if(sizeof($exec_sections)) {
                foreach($exec_sections as $section=>$reason) {
                    // call the disabling method
                    $method = "exec_{$section}";
                    if (method_exists($this, $method)) { $this->$method($reason); }
                }
            }
        }

        $this->exec_hide_filters();
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
        $install = new PeepSoLimitUsersInstall();
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

    public function license_notice()
    {
        PeepSo::license_notice(self::PLUGIN_NAME, self::PLUGIN_SLUG);
    }

    public function license_notice_forced()
    {
        PeepSo::license_notice(self::PLUGIN_NAME, self::PLUGIN_SLUG, true);
    }

    /**
     * Display a message about PeepSo not present
     */
    public function peepso_disabled_notice()
    {
        ?>
        <div class="error peepso">
            <strong>
                <?php echo sprintf(__('The %s plugin requires the PeepSo plugin to be installed and activated.', 'peepsolimitusers'), self::PLUGIN_NAME);?>
				<a href="plugin-install.php?tab=plugin-information&plugin=peepso-core&TB_iframe=true&width=772&height=291" class="thickbox">
					<?php echo __('Get it now!', 'peepsolimitusers');?>
				</a>
            </strong>
        </div>
        <?php
    }

    /**
     * Hook into PeepSo for compatibility checks
     * @param $plugins
     * @return mixed
     */
    public function peepso_filter_all_plugins($plugins)
    {
        $plugins[plugin_basename(__FILE__)] = get_class($this);
        return $plugins;
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *  LICENSING, VERSION CHECK, UPDATES */

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

    /**
     * Registers a tab in the PeepSo Config Toolbar
     * PS_FILTER
     *
     * @param $tabs array
     * @return array
     */
    public function admin_config_tabs( $tabs )
    {
        $tabs['limitusers'] = array(
            'label' => __('User Limits', 'peepsolimitusers'),
            'tab' => 'limitusers',
            'icon' => 'https://cdn.peepso.com/icons/plugins/'.self::PLUGIN_EDD.'.svg',
            'description' => __('Configure which users to hide from the listings', 'peepsolimitusers'),
            'function' => 'PeepSoConfigSectionLimitUsers',
            'cat'   => 'core',
        );

        return $tabs;
    }

    private function debug_add($section, $reason)
    {
        $array = array('reason'=>$reason, 'limit'=>NULL);

        if(is_array($this->limits) && array_key_exists($section, $this->limits)) {
            $array['limit'] = $this->limits[$section];
        }

        $this->debug[$section] = $array;
    }

    // JOBS - CREATE
    private function exec_wpjm_create($reason = 'role')
    {
        $this->debug_add('wpjm_create', $reason);

        add_filter('peepso_permissions_wpjm_create', function(){return FALSE;});
    }
    // EVENTS - CREATE
    private function exec_wpem_create($reason = 'role')
    {
        $this->debug_add('wpem_create', $reason);

        add_filter('peepso_permissions_wpem_create', function(){return FALSE;});
    }

    // EVENTS - RSVP
    private function exec_wpem_rsvp($reason = 'role')
    {
        $this->debug_add('wpem_rsvp', $reason);

        add_filter('peepso_permissions_wpem_rsvp', function(){return FALSE;});
    }

    // FRIENDS
    private function exec_friends($reason = 'role')
    {
        $this->debug_add('friends', $reason);

        add_filter('peepso_permissions_friends_request', function(){return FALSE;});
    }

    // GROUPS
    private function exec_groups($reason = 'role')
    {
        $this->debug_add('groups', $reason);

        add_filter('peepso_permissions_groups_join', function(){return FALSE;});
        add_filter('peepso_permissions_groups_join_request', function(){return FALSE;});
        add_filter('peepso_permissions_groups_be_invited', function(){return FALSE;});
    }

    private function exec_groups_create($reason = 'role') {
        $this->debug_add('groups_create', $reason);

        add_filter('peepso_permissions_groups_create', function(){return FALSE;});
    }

    // MESSAGES
    private function exec_messages($reason = 'role')
    {
        $this->debug_add('messages', $reason);

        add_filter('peepso_permissions_messages_create', function(){return FALSE;});
    }

    // PHOTOS
    private function exec_photos($reason = 'role')
    {
        $this->debug_add('photos', $reason);

        add_filter('peepso_permissions_photos_upload', function(){return FALSE;});
    }

    // POLLS
    private function exec_polls($reason = 'role')
    {
        $this->debug_add('polls', $reason);

        add_filter('peepso_permissions_polls_create', function(){return FALSE;});
    }

    // VIDEOS
    private function exec_videos_embed($reason = 'role') {
        $this->debug_add('videos_embed', $reason);

        add_filter('peepso_permissions_videos_embed', function(){return FALSE;});
    }

    private function exec_videos($reason = 'role') {
        $this->debug_add('videos', $reason);

        add_filter('peepso_permissions_videos_upload', function(){return FALSE;});
    }

    private function exec_audio_embed($reason = 'role') {
        $this->debug_add('audio_embed', $reason);

        add_filter('peepso_permissions_audio_embed', function(){return FALSE;});
    }

    private function exec_audio($reason = 'role') {
        $this->debug_add('audio', $reason);

        add_filter('peepso_permissions_audio_upload', function(){return FALSE;});
    }

    private function exec_post_backgrounds($reason = 'role') {
        $this->debug_add('post_backgrounds', $reason);

        add_filter('peepso_permissions_post_backgrounds_create', function(){return FALSE;});
    }

    // FILES
    private function exec_files($reason = 'role')
    {
        $this->debug_add('files', $reason);

        add_filter('peepso_permissions_files_upload', function(){return FALSE;});
    }

    // HIDE
    private function exec_hide($reason = 'role')
    {
        $this->debug_add('hide', $reason);
    }

    // POSTS
    private function exec_posts($reason = 'role') {
        $this->debug_add('posts', $reason);

        add_filter('peepso_permissions_post_create', function(){return FALSE;}, 99);

        add_filter('peepso_permissions_post_create_denied_reason', function() use ($reason) {
            ob_start();
            echo __('You are currently not allowed to create new posts','peepsolimitusers');
            echo  "<!--".__CLASS__. " - $reason -->";

            #echo PeepSoLimitUsers::get_instance()->debug_formatted();

            return ob_get_clean();
        });

    }

    // RePOSTS
    private function exec_repost($reason = 'role') {
        $this->debug_add('repost', $reason);

        add_filter('peepso_permissions_repost_create', function(){return FALSE;}, 99);

    }

    // COMMENTS
    private function exec_comments($reason = 'role') {
        $this->debug_add('comments', $reason);

        add_filter('peepso_permissions_comment_create', function(){return FALSE;});

        add_filter('peepso_permissions_comment_create_denied_reason', function() use ($reason) {
            ob_start();
            echo __('You are currently not allowed to create new comments','peepsolimitusers');
            echo  "<!--".__CLASS__. " - $reason -->";

            return ob_get_clean();
        });
    }

    // REACTIONS
    private function exec_reactions($reason = 'role') {
        $this->debug_add('reactions', $reason);

        add_filter('peepso_permissions_reactions_create', function(){return FALSE;});
    }

    private function exec_hide_filters() {

        // Don't filter anything if currrent user is an admin
        if(PeepSo::is_admin()) {
            return false;
        }

        // hide users based on specific roles
        add_filter('peepso_user_search_args', function ($args) {

            // check if all roles is not set
            if (!$this->all_roles) {
                global $wp_roles;
                $this->all_roles = $wp_roles->roles;
            }

            foreach ($this->all_roles as $role_key => $role) {

                if (!PeepSo::get_option("limitusers_hide_role_{$role_key }", FALSE)) {
                    continue;
                }

                if (isset($args['role__not_in'])) {
                    $not_in = $args['role__not_in'];
                } else {
                    $not_in = array();
                }

                $args['role__not_in'] = array_merge(
                    $not_in,
                    array($role_key)
                );
            }

            return $args;
        });

        // hide users based on profile completeness
        if(isset($this->limits['hide']) && $this->limits['hide'] > 0) {

            add_filter('peepso_user_search_args', function ($args) {

                if(isset($args['meta_query'])){

                    $args_hide['meta_query'] = array(
                        array(
                            'relation' => 'OR',
                            array(
                                'key' => 'peepso_profile_completeness',
                                'value' => (int)$this->limits['hide'],
                                'type' => 'numeric',
                                'compare' => '>=',
                            ),
                        ),
                    );

                    $args['meta_query'] = array(
                        $args['meta_query'],
                        $args_hide['meta_query'],
                    );

                } else {
                    $args['meta_query'] = array(
                        array(
                            'relation' => 'OR',
                            array(
                                'key' => 'peepso_profile_completeness',
                                'value' => (int)$this->limits['hide'],
                                'type' => 'numeric',
                                'compare' => '>=',
                            ),
                        ),
                    );
                }
                return $args;
            });
        }

        // hide users with no avatars
        if(PeepSo::get_option("limitusers_hide_avatar", 0)) {
            add_filter('peepso_user_search_args', function ($args) {
                $args['_peepso_args']['avatar_custom'] = 1;
                return $args;
            });
        }

        // hide users with no covers
        if(PeepSo::get_option("limitusers_hide_cover", 0)) {
            add_filter('peepso_user_search_args', function ($args) {
                $args['_peepso_args']['cover_photo'] = 1;
                return $args;
            });
        }
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
}

peepsolimitusers::get_instance();
// EOF
