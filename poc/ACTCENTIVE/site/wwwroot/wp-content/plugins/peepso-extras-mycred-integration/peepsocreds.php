<?php
/**
 * Plugin Name: PeepSo Integrations: myCRED
 * Plugin URI: https://peepso.com
 * Description: Award myCRED points for performing actions in PeepSo. Requires the MyCred plugin.
 * Author: PeepSo
 * Author URI: https://peepso.com
 * Version: 6.2.7.0
 * Copyright: (c) 2015 PeepSo, Inc. All Rights Reserved.
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: peepsocreds
 * Domain Path: /language
 *
 * We are Open Source. You can redistribute and/or modify this software under the terms of the GNU General Public License (version 2 or later)
 * as published by the Free Software Foundation. See the GNU General Public License or the LICENSE file for more details.
 * This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY.
 */

Class PeepSoMyCreds {

    private static $_instance = NULL;

    const PLUGIN_VERSION = '6.2.7.0';
    const PLUGIN_RELEASE = ''; //ALPHA1, BETA10, RC1, '' for STABLE
    const PLUGIN_NAME = 'Integrations: myCred';
    const PLUGIN_EDD = 69775;
    const PLUGIN_SLUG = 'peepso-mycred';
    const THIRDPARTY_MIN_VERSION = '1.8.11';

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

            return(self::ready_thirdparty() && class_exists('PeepSo') && $peepso_version == $plugin_version);
        }
    }

    private static function ready_thirdparty() {
        if (!class_exists('myCRED_Core') || !get_option('mycred_setup_completed') || (function_exists('mycred_check') && !mycred_check()) || !version_compare( myCRED_VERSION, self::THIRDPARTY_MIN_VERSION, '>=' )) {
            return (FALSE);
        }

        return (TRUE);
    }

    private function __construct() {

        /** VERSION INDEPENDENT hooks **/

        // Admin
        add_filter('peepso_license_config', array(&$this, 'add_license_info'), 160);

        if (is_admin()) {
            add_action('admin_init', array(&$this, 'peepso_check'));
        }

        // Compatibility
        add_filter('peepso_all_plugins', function($plugins) {
            $plugins[plugin_basename(__FILE__)] = get_class($this);
            return $plugins;
        });

        // Translations
        add_action('plugins_loaded', array(&$this, 'load_textdomain'));

        // Activation
        register_activation_hook( __FILE__, array( $this, 'activate' ) );

        /** VERSION LOCKED hooks **/
        if(self::ready()) {
            // license checking
            if (!PeepSoLicense::check_license(self::PLUGIN_EDD, self::PLUGIN_SLUG)) {
                return;
            }

            add_action('peepso_init', array(&$this, 'init'));
        }

        add_action('mycred_load_hooks', function() {
            require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'mycredhook.php';
        });
    }

    /**
     * Retrieve singleton class instance
     * @return PeepSoMyCreds instance
     */
    public static function get_instance()
    {
        if (NULL === self::$_instance) {
            self::$_instance = new self();
        }
        return (self::$_instance);
    }

    function init() {
        PeepSo::add_autoload_directory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR);

        add_action('peepso_navigation_profile', function ($links) {
            if (PeepSo::get_option('mycred_point_history_enabled', 0) === 1 && PeepSoUrlSegments::get_view_id(PeepSoProfileShortcode::get_instance()->get_view_user_id()) === get_current_user_id()) {
                $links['points'] = array(
                    'label' => function_exists('mycred_get_point_type_name') ? mycred_get_point_type_name(MYCRED_DEFAULT_TYPE_KEY, FALSE) : _x('Points', 'Profile link', 'peepsocreds'),
                    'href' => 'points',
                    'icon' => 'gcis gci-certificate'
                );
            }

            return $links;
        });

        if (is_admin()) {
            add_filter('peepso_admin_config_tabs', array($this, 'admin_config_tabs'), 90);

            add_action('admin_enqueue_scripts', function(){
                wp_enqueue_script('peepso-admin-mycred',
                    PeepSo::get_asset('js/admin.js', __FILE__),
                    array('jquery', 'underscore'), self::PLUGIN_VERSION, TRUE);
            });
        }

        add_filter('peepso_user_activities_links_before', function($act_links){
            $url = PeepSoUrlSegments::get_instance();

            $decimals = apply_filters('peepso_filter_short_profile_count_decimals',1);
            $threshold = apply_filters('peepso_filter_short_profile_count_threshold',1000);

            if ($url->get(0) === 'peepsoajax' && isset($_POST['user_id'])) {
                $user_id = (int) $_POST['user_id'];
            } else if ($url->get(1)) {
                $user = get_user_by('slug', $url->get(1));

                if (FALSE === $user) {
                    $user_id = get_current_user_id();
                } else {
                    $user_id = $user->ID;
                }
            } else {
                $user_id = get_current_user_id();
            }

            $account = mycred_get_account($user_id);

            $count = isset($account->balance['mycred_default']->current) ? $account->balance['mycred_default']->current : 0;

            if(function_exists('mycred_get_point_type_name')) {
                $label = mycred_get_point_type_name(MYCRED_DEFAULT_TYPE_KEY, ($count == 1));
            } else {
                $label = _n('Point', 'Points', $count, 'peepsocreds');
            }

            // Debug
            if(isset($_GET['profile_counts_debug'])) { $count = rand(1,11111); }

            $a['points'] = array(
                'label' => $label,
                'title' => __('User Points', 'peepsocreds'),
                'icon' => 'gcis gci-certificate',
                'count' => PeepSo3_Utilities_String::shorten_big_number($count,$decimals,$threshold),
                'all_values' => TRUE,
                'order' => 903,
                'class'=> 'ps-focus__detail',
                'is_details' => TRUE,
            );

            $act_links = array_merge($a, $act_links);
            return $act_links;
        });


        if (PeepSo::get_option('mycred_point_history_enabled', 0) === 1) {

            add_action('peepso_profile_segment_points', function() {
                $pro = PeepSoProfileShortcode::get_instance();

                $this->view_user_id = PeepSoUrlSegments::get_view_id($pro->get_view_user_id());

                PeepSoTemplate::add_template_directory(plugin_dir_path(__FILE__));
                echo PeepSoTemplate::exec_template('profile', 'profile-mycred', array('view_user_id' => $this->view_user_id), TRUE);
            });
        }



        add_filter('mycred_ranking_row', function($layout, $template, $user, $position) {

            $layout = str_replace('/author/', '/' . PeepSo::get_option('page_profile', 'profile') . '/?', $layout);

            return $layout;
        }, 10, 4);

        if (PeepSo::get_option('override_admin_navbar', 0) === 1) {
            add_action( 'admin_bar_menu', function($wp_admin_bar) {
                $wp_admin_bar->remove_menu('mycred-account');
            });
        }

        add_filter('mycred_setup_hooks', array($this, 'my_peepsocreds_setup_hooks'));

        add_filter('mycred_all_references', function($hooks) {
            $hooks['new_peepso-post']				= __('PeepSo - New post', 'peepsocreds');
            // $hooks['delete_peepso-post']			= __('PeepSo - Delete post', 'peepsocreds');

            $hooks['new_peepso-comment']			= __('PeepSo - New comment', 'peepsocreds');
            // $hooks['delete_peepso-comment']			= __('PeepSo - Delete comment', 'peepsocreds');

            $hooks['like_peepso_content']			= __('PeepSo - Like content', 'peepsocreds');
            // $hooks['unlike_peepso_content']			= __('PeepSo - Unlike cntent', 'peepsocreds');

            $hooks['new_peepso_profile_cover']		= __('PeepSo - New profile cover', 'peepsocreds');
            // $hooks['delete_peepso_profile_cover']	= __('PeepSo - Delete profile cover', 'peepsocreds');

            $hooks['new_peepso_profile_avatar']		= __('PeepSo - New profile avatar', 'peepsocreds');
            // $hooks['delete_peepso_profile_avatar']	= __('PeepSo - Delete profile avatar', 'peepsocreds');

            $hooks['new_peepso-message']			= __('PeepSo - New message', 'peepsocreds');
            // $hooks['delete_peepso-message']			= __('PeepSo - Delete message', 'peepsocreds');

            $hooks['add_peepso_friend']				= __('PeepSo - Add friend', 'peepsocreds');
            // $hooks['delete_peepso_friend']			= __('PeepSo - Delete friend', 'peepsocreds');

            $hooks['new_peepso_stream_photo']		= __('PeepSo - New stream photo', 'peepsocreds');
            // $hooks['delete_peepso_stream_photo']	= __('PeepSo - Delete stream photo', 'peepsocreds');

            return $hooks;
        });
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

    /**
     * Registers a tab in the PeepSo Config Toolbar
     * PS_FILTER
     *
     * @param $tabs array
     * @return array
     */
    public function admin_config_tabs($tabs)
    {
        $tabs['mycred'] = array(
            'label' => __('myCRED', 'peepsocreds'),
            'title' => __('myCRED', 'peepsocreds'),
            'tab' => 'mycred',
            'icon' => 'https://cdn.peepso.com/icons/plugins/'.self::PLUGIN_EDD.'.svg',
            'description' => __('myCRED', 'peepsocreds'),
            'function' => 'PeepSoConfigSectionMyCred',
            'cat'   => 'integrations',
        );

        return $tabs;
    }

    public function activate() {

        if (!$this->peepso_check()) {
            return (FALSE);
        }

        return (TRUE);
    }


    /**
     * Check if PeepSo class is present (ie the PeepSo plugin is installed and activated)
     * If there is no PeepSo, immediately disable the plugin and display a warning
     * Run license and new version checks against PeepSo.com
     * @return bool
     */
    function peepso_check()
    {
        if (!class_exists('PeepSo')) {
            add_action('admin_notices', array(&$this, 'peepso_disabled_notice'));
            unset($_GET['activate']);
            deactivate_plugins(plugin_basename(__FILE__));
            return (FALSE);
        }

        if (!self::ready_thirdparty()) {
            add_action('admin_notices', function() {
                if(method_exists('PeepSo','third_party_warning')) {
                    $extra = '';
                    if(class_exists('myCRED_Core') && !get_option('mycred_setup_completed')) {
                        $extra = "<b>PeepSo</b> requires <b>MyCred setup</b> to be fininshed to run <b>PeepSo ".self::PLUGIN_NAME."</b>";
                    }
                    PeepSo::third_party_warning('myCred','mycred',FALSE,self::THIRDPARTY_MIN_VERSION, self::PLUGIN_NAME, $extra);
                }
            }, 10050);
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
                <?php
                echo sprintf(__('The %s plugin requires the PeepSo plugin to be installed and activated.', 'peepsocreds'), self::PLUGIN_NAME),
                ' <a href="plugin-install.php?tab=plugin-information&amp;plugin=peepso-core&amp;TB_iframe=true&amp;width=772&amp;height=291" class="thickbox">',
                __('Get it now!', 'peepsocreds'),
                '</a>';
                ?>
                <?php //echo sprintf(__('The %s plugin requires the PeepSo plugin to be installed and activated.', 'peepso-wpadverts'), self::PLUGIN_NAME);?>
            </strong>
        </div>
        <?php
    }

    function my_peepsocreds_setup_hooks($installed) { //, $point_type ) {
        // Add a custom hook
        $installed['peepsocreds'] = array(
            'title' => 'PeepSo',
            'description' => 'Receive points for PeepSo activities.',
            'callback' => array('myCREDHook')
        );

        return $installed;
    }

    /**
     * Loads the translation file for the PeepSo plugin
     */
    public function load_textdomain()
    {
        $path = str_ireplace(WP_PLUGIN_DIR, '', dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
        load_plugin_textdomain('peepsocreds', FALSE, $path);
    }
}

PeepSoMyCreds::get_instance();
