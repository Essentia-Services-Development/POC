<?php
/**
 * Plugin Name: PeepSo Integrations: WP Job Manager
 * Plugin URI: https://peepso.com
 * Description: Add integration for WP Job Manager plugin
 * Author: PeepSo
 * Author URI: https://peepso.com
 * Version: 6.2.7.0
 * Copyright: (c) 2016 PeepSo, Inc. All Rights Reserved.
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: peepso-wpjm
 * Domain Path: /language
 *
 * We are Open Source. You can redistribute and/or modify this software under the terms of the GNU General Public License (version 2 or later)
 * as published by the Free Software Foundation. See the GNU General Public License or the LICENSE file for more details.
 * This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY.
 *
 */


class PeepSoWPJM
{

    private static $_instance = NULL;

    const PLUGIN_NAME	 = 'Integrations: WP Job Manager';
    const PLUGIN_SLUG 	 = 'peepso-wpjm';
    const PLUGIN_EDD 	 = 72927820;
    const PLUGIN_VERSION = '6.2.7.0';
    const PLUGIN_RELEASE = ''; //ALPHA1, RC1 etc, '' for STABLE
	const MODULE_ID      = 60;

    const THIRDPARTY_MIN_VERSION = '1.39.0';

    private static function ready_thirdparty() {
        $result = TRUE;

        if ( !defined('JOB_MANAGER_VERSION')  || !version_compare( JOB_MANAGER_VERSION, self::THIRDPARTY_MIN_VERSION, '>=' )) {
            $result = FALSE;
        }

        return $result;
    }

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

            return(self::ready_thirdparty() && $peepso_version == $plugin_version);
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
		load_plugin_textdomain('peepso-wpjm', FALSE, $path);
    }

    public function init()
    {
        PeepSo::add_autoload_directory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR);
        PeepSoTemplate::add_template_directory(plugin_dir_path(__FILE__));

        if (is_admin()) {
            add_action('admin_init', array(&$this, 'peepso_check'));
            add_filter('peepso_admin_config_tabs', array(&$this, 'admin_config_tabs'));
            add_action('before_delete_post', array(&$this, 'before_delete_post'), 10, 2);
            add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));
        } else {
            if (PeepSo::get_option_new('wpjm_enable')) {
                // Hooks into profile pages and "me" widget
                $profile_slug = PeepSo::get_option('wpjm_navigation_profile_slug','jobs',1);
                add_filter('peepso_navigation_profile', array(&$this, 'filter_profile_segment_menu_links'));
                add_action('peepso_profile_segment_'.$profile_slug, array(&$this, 'peepso_profile_segment_jobs'));

                if (isset($_GET['action']) && isset($_GET['job_id']) && isset($_GET['_wpnonce'])) {
                    add_filter('job_manager_should_run_shortcode_action_handler', '__return_true');
                }

                add_filter('job_manager_locate_template', array($this, 'job_manager_locate_template'), 10, 3);
            }

			// attach file to post and comment
			add_action('peepso_activity_post_attachment', array(&$this, 'attach_job'), 30);

			// disable repost
			add_filter('peepso_activity_post_actions', array(&$this, 'activity_post_actions'), 100);

			// stream title
			add_filter('peepso_activity_stream_action', array(&$this, 'activity_stream_action'), 10, 2);

            add_filter('peepso_post_filters', array($this, 'post_filters'), 99, 1);

            /** Permissions */
            PeepSoWPJM_Permissions::get_instance();
        }

        if (PeepSo::get_option_new('wpjm_stream_enable')) {
            // stream creation hook
            add_action('wp_insert_post', array($this, 'after_add_post'), 10, 3);
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
        $install = new PeepSoWPJMInstall();
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

        if(!self::ready_thirdparty()) {
            add_action('admin_notices', function() {
                if(method_exists('PeepSo','third_party_warning')) {
                    PeepSo::third_party_warning('WP Job Manager', 'wp-job-manager', FALSE,self::THIRDPARTY_MIN_VERSION, self::PLUGIN_NAME);
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

    public function peepso_disabled_notice()
    {
        ?>
        <div class="error peepso">
            <strong>
                <?php echo sprintf(__('The %s plugin requires the PeepSo plugin to be installed and activated.', 'peepso-wpjm'), self::PLUGIN_NAME);?>
				<a href="plugin-install.php?tab=plugin-information&plugin=peepso-core&TB_iframe=true&width=772&height=291" class="thickbox">
					<?php echo __('Get it now!', 'peepso-wpjm');?>
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
        $tabs['wpjm'] = array(
            'label' => __('WP Job Manager', 'peepso-wpjm'),
            'tab' => 'wpjm',
            'icon' => 'https://cdn.peepso.com/icons/plugins/'.self::PLUGIN_EDD.'.svg',
            'description' => __('WP Job Manager', 'peepso-wpjm'),
            'function' => 'PeepSoConfigSectionWPJM',
            'cat'   => 'integrations',
        );

        return $tabs;
    }

    /**
     * Enqueue admin scripts
     */
    public function admin_enqueue_scripts()
    {
        wp_enqueue_script('peepso-admin-wpjm',
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
            $action = ' ' . PeepSo::get_option_new('wpjm_action_text');
            if (empty(trim($action))) {
                $action = ' ' . __('posted a job', 'peepso-wpjm');
            }

            if (PeepSo::get_option_new('wpjm_append_title')) {
                $job_post = get_post(get_post_meta($post->ID, 'peepso-wpjm', true));
                $action .= ' - <a target="_blank" href="' . home_url('job/' . $job_post->post_name). '">' . $job_post->post_title . '</a>';
            }
			add_filter('peepso_activity_content', '__return_false', 10);
		}
        return ($action);
    }

    /**
     * Attach the file to the post display
     * @param  object $post The post
     */
    public function attach_job($post, $post_id = 0, $act_module_id = 0)
    {
        if (!isset($post->act_module_id)) {
            $act = PeepSoActivity::get_instance();
            $activity = $act->get_activity_data($post->ID, self::MODULE_ID);

            if (isset($activity->act_module_id)) {
                $post->act_module_id = $activity->act_module_id;
            }
        }

        if (!isset($post->act_module_id) || $post->act_module_id != self::MODULE_ID) {
            return;
        }

        $data = [
            'post' => get_post(get_post_meta($post->ID, 'peepso-wpjm', true))
        ];

        PeepSoTemplate::exec_template('jobs', 'content-media', $data);
    }

	/**
     * Disable repost on file
     * @param array $actions The default options per post
     * @return  array
     */
	public function activity_post_actions($actions) {
		if ($actions['post']->act_module_id == self::MODULE_ID) {
			unset($actions['acts']['edit']);
			unset($actions['acts']['repost']);
		}
		return $actions;
	}

    /**
     * Add profile submenu item.
     *
     * @param array $links
     * @return array
     */
    public function filter_profile_segment_menu_links($links)
    {
        $links['jobs'] = array(
            'href' => PeepSo::get_option('wpjm_navigation_profile_slug','jobs',1),
            'label'=> PeepSo::get_option('wpjm_navigation_profile_label', __('Jobs', 'peepso-wpjm'),1),
            'icon' => PeepSo::get_option('wpjm_navigation_profile_icon', 'gcis gci-briefcase',1),
        );

        return $links;
    }

    public function peepso_profile_segment_jobs($url_segments)
    {
        if (isset($url_segments->_segments[3]) && $url_segments->_segments[3] == 'create') {
            echo PeepSoTemplate::exec_template('jobs', 'create');
        } else {
            $profile = PeepSoProfileShortcode::get_instance();

            add_filter('job_manager_get_dashboard_jobs_args', function($args) use ($profile) {
                $args['author'] = $profile->get_view_user_id();
                return $args;
            });

            add_filter('job_manager_my_job_actions', function($action, $job) use ($profile) {
                unset($action['duplicate']);
                if ($profile->get_view_user_id() != get_current_user_id()) {
                    $action = [];
                }
                return $action;
            }, 99, 2);

            echo PeepSoTemplate::exec_template('jobs', 'profile', ['view_user_id' => $profile->get_view_user_id()]);
        }
    }

    public function after_add_post($post_id, $post, $update) {
        if (PeepSo::get_option_new('wpjm_stream_enable') && $post->post_type == 'job_listing') {
            // find activity post first
            global $wpdb;
            $activity_post_id = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'peepso-wpjm' AND meta_value = $post_id");

            if (!$activity_post_id) {
                add_filter('peepso_activity_allow_empty_content', '__return_true');

                // create an activity item
                $act = PeepSoActivity::get_instance();
                $activity_post_id = $act->add_post($post->post_author, $post->post_author, '', [
                    'module_id' => self::MODULE_ID,
                    'act_access' => PeepSo::get_option_new('wpjm_default_privacy')
                ]);

                update_post_meta($activity_post_id, 'peepso-wpjm', $post_id);
            }

            if ($post->post_status == 'publish') {
                // publish post
                wp_update_post([
                    'ID' => $activity_post_id,
                    'post_status' => 'publish'
                ]);
            } else {
                // unpublish post
                wp_update_post([
                    'ID' => $activity_post_id,
                    'post_status' => 'pending'
                ]);
            }
        }
    }

    public function before_delete_post($post_id, $post) {
        if ($post->post_type == 'job_listing') {
            global $wpdb;
            $activity_post_id = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'peepso-wpjm' AND meta_value = $post_id");

            if ($activity_post_id) {
                $act = PeepSoActivity::get_instance();
                $act->delete_post($activity_post_id);
            }
        }
    }

    public function job_manager_locate_template($template, $template_name, $template_path) {
        if (strpos($template_name, 'job-dashboard.php') !== FALSE) {
            $template = plugin_dir_path(__FILE__) . 'templates/overrides/job-dashboard.php';
        } else if (strpos($template_name, 'job-application.php') !== FALSE) {
            $template = plugin_dir_path(__FILE__) . 'templates/overrides/job-application.php';
        }
        
        return $template;
    }

    public function post_filters($options) {
        if (self::MODULE_ID == intval($options['post']->act_module_id)) {
            if (isset($options['acts']['edit'])) {
                unset($options['acts']['edit']);
            }
        }
    
        return $options;
    }
}

PeepSoWPJM::get_instance();
