<?php
/**
 * Plugin Name: PeepSo
 * Plugin URI: https://peepso.com
 * Description: PeepSo Foundation - The Next Generation Social Networking Plugin for WordPress
 * Author: PeepSo
 * Author URI: https://peepso.com
 * Version: 6.2.7.1
 * Copyright: (c) 2015 PeepSo, Inc. All Rights Reserved.
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: peepso-core
 * Domain Path: /language
 *
 * We are Open Source. You can redistribute and/or modify this software under the terms of the GNU General Public License (version 2 or later)
 * as published by the Free Software Foundation. See the GNU General Public License or the LICENSE file for more details.
 * This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY.
 */

class PeepSoSystemRequirements {

    const PHP_REQUIRED = '7.2.0';
    const PHP_RECOMMENDED = '8.0.0';

    const PHP_REQUIRED_SSE = '7.4.0';

    const MYSQL_REQUIRED = '5.6';

    const MEMORY_REQUIRED = '64M';

    const RELEASE_DATE = '2023-12-14';

    const DEMO_SITES = [
        'demo.peepso.com',
    ];

    public static function is_demo_site() {
        if ( isset( $_SERVER['HTTP_HOST'] ) && in_array( $_SERVER['HTTP_HOST'], self::DEMO_SITES ) ) {
            return TRUE;
        }

        return FALSE;
    }
}

class PeepSo
{
    const MODULE_ID = 0;

    const PLUGIN_VERSION = '6.2.7.1';
    const PLUGIN_RELEASE = ''; //ALPHA1, RC1 etc, '' for STABLE

    const PLUGIN_NAME = 'PeepSo';
    const PLUGIN_SLUG = 'peepso_';

    const PLUGIN_SLUG_EDD = 'peepso';
    const PLUGIN_EDD = 231;

    const PEEPSOCOM_LICENSES = 'https://peep.so/licenses';

    const ACCESS_FORCE_PUBLIC = -1;
    const ACCESS_PUBLIC = 10;
    const ACCESS_MEMBERS = 20;
    const ACCESS_PRIVATE = 40;
    const CRON_MAILQUEUE = 'peepso_mailqueue_send_event';
    const CRON_MAINTENANCE_EVENT = 'peepso_maintenance_event';
    const CRON_GDPR_EXPORT_DATA = 'peepso_gdpr_export_data_event';

    const HASHTAGS_POST_META = 'peepso_hashtags_done';
    const BLOGPOSTS_MODULE_ID = 6661;
    const BLOGPOSTS_SHORTCODE = 'peepso_postnotify';

    const OVERRIDES_DIRECTORY = 'custom';

    // const CRON_DAILY_EVENT = 'peepso_daily_event';               // hook into CRON_MAINTENANCE_EVENT instead
    // const CRON_WEEKLY_EVENT = 'peepso_weekly_event';             // hook into CRON_MAINTENANCE_EVENT instead
    // const CRON_CLEANUP_BASIC= 'peepso_action_cron_PeepSoPluginCleanupGroups_basic';  // hook into CRON_MAINTENANCE_EVENT instead



    private static $_instance = NULL;
    private static $_current_shortcode = NULL;

    private $_widgets = array(
        'PeepSoWidgetMe',
        'PeepSoWidgetHashtags',
        'PeepSoWidgetOnlinemembers',
        'PeepSoWidgetLatestmembers',
        'PeepSoWidgetUserBar',
        'PeepSoWidgetLogin',
    );

    /* array of paths to use in autoloading */
    private static $_autoload_paths = array();

    /* options data */
    private static $_config = NULL;

    private $is_ajax = FALSE;

    private $wp_title = array();

    private $sc = NULL;

    public $shortcodes= array(
        'peepso_activity' => 'PeepSoActivityShortcode::get_instance',
        'peepso_profile' => 'PeepSo::profile_shortcode',
        'peepso_register' => 'PeepSo::register_shortcode',
        'peepso_recover' => 'PeepSo::recover_shortcode',
        'peepso_reset' => 'PeepSo::reset_shortcode',
        'peepso_members' => 'PeepSo::search_shortcode',
        'peepso_notifications' => 'PeepSo::notifications_shortcode',
        'peepso_external_link_warning' => 'PeepSo::external_link_warning_shortcode',
    );

    public $shortcode_classes = array (
        'peepso_activity' => 'PeepSoActivityShortcode',
        'peepso_profile' => 'PeepSoProfileShortcode',
        'peepso_register' => 'PeepSoRegisterShortcode',
        'peepso_recover' => 'PeepSoRecoverPasswordShortcode',
        'peepso_reset' => 'PeepSoResetPasswordShortcode',
        'peepso_members' => 'PeepsoMembersShortcode',
        'peepso_notifications' => 'PeepSoNotificationsShortcode',
        'peepso_external_link_warning' => 'PeepSoExternalLinkWarningShortcode',
    );

    public $reactions_model;

    public $hashtags_regex = " /#([A-Za-z0-9]+)/u";

    // Extend PeepSoField keys, as_int and as_array with extra values
    protected $field_meta_keys_extra = array(
        'user_on_cover',
    );

    protected $field_meta_keys_extra_as_int = array(
        'user_on_cover',
    );

    protected $field_meta_keys_extra_as_array = array(
    );

    public $field_types = array(
        'textemail',
        'selectmulti',
        'selectbool',
        'separator',
        'country',
    );


    public static function php() {

        $php_required = PeepSoSystemRequirements::PHP_REQUIRED;
        $php_recommended = PeepSoSystemRequirements::PHP_RECOMMENDED;

        $php_current = phpversion();

        // uncomment these to emulate wrong php version
        // $php_current = '5.6.0';

        $allow_activation = TRUE;
        $message = '';

        // check required
        if(-1 == version_compare($php_current, $php_required)) {
            $allow_activation = FALSE;
            $message = "<strong>PeepSo can't be activated!</strong> You are using <strong>PHP $php_current</strong> - PeepSo requires PHP $php_required.x, but <strong>we recommend at least PHP $php_recommended.x</strong>.";
        }

        // check recommended
        if($allow_activation) {

            if (-1 == version_compare($php_current, $php_recommended)) {
                $message = "You are using <strong>PHP $php_current</strong> - <strong>we recommend at least PHP $php_recommended.x</strong>.";
            }
        }

        if(isset($_GET['peepso_hide_php_warning_71'])) {
            $set = (0==$_GET['peepso_hide_php_warning_71']) ? 0 : 1;

            update_user_option(get_current_user_id(), 'peepso_hide_php_warning_71', $set);
        }

        if(strlen($message)) {
            add_action('admin_notices', function() use ($message, $allow_activation) {
                global $peepso_did_warn_php;
                if(true == $peepso_did_warn_php) return;
                $peepso_did_warn_php = TRUE;

                $dismissed = get_user_option('peepso_hide_php_warning_71', get_current_user_id());
                if($dismissed) return;
                ?>
                <div class="error" id="peepso_php_warning">
                    <h1>Read carefully - your website might be in danger!</h1>
                    <?php echo $message;?><br/>
                    Please update your PHP version for better performance and security. <strong>Old PHP versions can be vulnerable to attacks!</strong><br/><br/>
                    We encourage you to <a href="https://Peep.So/PHP" target="_blank">learn more about the consequences of using old PHP versions</a>.

                    <?php if($allow_activation) { ?>
                        <br><br>
                        <a style="opacity:0.5" href="#" id="peepso_dismiss_php_warning">Dismiss, I love living dangerously!</a>
                    <?php } ?>
                </div>
                <script>
                    setTimeout(function() {
                        if (! typeof jQuery) return;
                        var $warning = jQuery('#peepso_php_warning');
                        var $dismiss = jQuery('#peepso_dismiss_php_warning');
                        $dismiss.on('click', function(e) {
                            e.preventDefault();
                            $warning.hide();
                            jQuery.post({ url: ajaxurl, data: { action: 'peepso_dismiss_php_warning' } });
                        });
                    }, 0 );
                </script>
                <?php
            },-900);
        }

        if(!$allow_activation) {

            if(!function_exists('deactivate_plugins')) {
                $admin_path = str_replace(get_site_url() . '/', ABSPATH, get_admin_url());
                @include_once($admin_path . 'includes/plugin.php');
            }

            if(function_exists('deactivate_plugins')) {
                deactivate_plugins(plugin_basename(__FILE__));
            }
        }

        return $allow_activation;

    }
    private function __construct()
    {
        if(!self::php()) return;

        // load the ActivityStream plugin
        require_once(dirname(__FILE__) . '/activity/activitystream.php');

        add_filter('wp_ajax_peepso_sse_token', function(){
            // SQL safe, WP sanitizes it
            $PeepSoInput = new PeepSoInput();
            if (wp_verify_nonce($PeepSoInput->value('_wpnonce','',FALSE), 'peepso-sse-token' ) === FALSE) {
                die(json_encode(array('success'=>0)));
            }

            $ts = time();
            $ds = DIRECTORY_SEPARATOR;

            do {
                $token = $ts . rand(10000, 99999);
                $dir =  PeepSo::get_peepso_dir() . $ds . 'sse' . $ds . 'events' . $ds . get_current_user_id() . $ds . $token;
            } while(file_exists($dir));

            mkdir($dir, 0755, TRUE);

            die(json_encode(array('sse_token'=>$token)));
        });

        add_filter('superpwa_sw_never_cache_urls', function($path) {
            return $path . ',/\/peepsoajax\//,/\/wp-json\/peepso\//';
        });

        // #5081 BBQ Firewall conflict
        add_filter('request_uri_items', function($uri_items){
            for ($i=0; $i < count($uri_items); $i++) {
                if ($uri_items[$i] == '\.conf') {
                    $uri_items[$i] = '\b.conf\b';
                }
            }

            return $uri_items;
        });

        add_filter('wp_ajax_peepso_should_get_notifications', array(&$this, 'ajax_should_get_notifications'));
        add_filter('wp_ajax_peepso_admin_verbose_tabs', function(){
            update_user_option(get_current_user_id(), 'peepso_admin_verbose_tabs', intval($_REQUEST['value']));
        });

        add_filter('wp_ajax_peepso_admin_verbose_fields', function(){
            update_user_option(get_current_user_id(), 'peepso_admin_verbose_fields', intval($_REQUEST['value']));
        });

        add_filter('wp_ajax_peepso_dismiss_php_warning', function(){
            update_user_option(get_current_user_id(), 'peepso_hide_php_warning_71', 1);
        });

        add_filter('wp_ajax_peepso_dismiss_new_addon', function() {
            $new_addon = PeepSo3_Mayfly_Int::get('installer_has_new');
            update_user_option(get_current_user_id(), 'peepso_hide_addon_' . $new_addon, 1);
        });

        add_filter('wp_ajax_peepso_dismiss_old_version', function() {
            $month = $_POST['month'];
            update_user_option(get_current_user_id(), 'peepso_hide_old_version_' . $month, 1);
        });

        // set up autoloading
        self::add_autoload_directory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR);
        self::add_autoload_directory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'widgets' . DIRECTORY_SEPARATOR);
        self::add_autoload_directory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR);
        self::add_autoload_directory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR);
        $res = spl_autoload_register(array(&$this, 'autoload'));

        PeepSoTemplate::add_template_directory(PeepSo::get_peepso_dir().PeepSo::OVERRIDES_DIRECTORY.DIRECTORY_SEPARATOR);
        PeepSoTemplate::add_template_directory(dirname(__FILE__));

        // PeepSo 3
        require_once('3' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'boot' . DIRECTORY_SEPARATOR . 'all.php');
        PeepSo3_Autoload::get_instance();
        PeepSo3_API::get_instance();

        add_action('init', function() {
            PeepSo3_Developer_Tools::get_instance();
            PeepSo3_Stats::get_instance();

            // Compare last version stored in transient with current version
            if( $this::PLUGIN_VERSION.$this::PLUGIN_RELEASE != PeepSo3_Mayfly::get($mayfly = 'peepso_'.$this::PLUGIN_SLUG.'_version')) {
                PeepSo3_Mayfly::set($mayfly, $this::PLUGIN_VERSION.$this::PLUGIN_RELEASE);
                $this->activate();
            }

            // Hover card.
            if ( PeepSo::get_option('hovercards_enable', 1) == 1 ) {
                new PeepSoHoverCard();
            }

        },-1);

        // Redirect wp-login.php?action=registration
        add_action('init', function() {
            if (!PeepSo::get_option_new('site_registration_disabled') &&PeepSo::get_option_new('registration_redirect_wp_to_peepso')) {
                global $pagenow;

                if ((strtolower($pagenow) == 'wp-login.php') && (isset($_GET['action']) && strtolower($_GET['action']) == 'register')) {
                    PeepSo3_Utility_Redirect::_(PeepSo::get_page('register'));
                }
            }
        });

        // add five minute schedule to be used by mailqueue
        add_filter('authenticate', array(&$this, 'auth_signon'), 30, 3);
        add_filter('allow_password_reset', array(&$this, 'allow_password_reset'), 20, 2);
        add_filter('body_class', array(&$this,'body_class_filter'));
        add_filter('cron_schedules', array(&$this, 'filter_cron_schedules'));
        add_filter('peepso_widget_me_community_links', array(&$this, 'peepso_widget_me_community_links'));
        add_filter('peepso_widget_args_internal', array(&$this, 'peepso_widget_args_internal'));
        add_filter('peepso_widget_instance', array(&$this, 'peepso_widget_instance'));
        add_filter('peepso_activity_more_posts_link', array(&$this, 'peepso_activity_more_posts_link'));
        add_filter('peepso_activity_remove_shortcode', array(&$this, 'peepso_activity_remove_shortcode'));
        add_filter('the_title', array(&$this,'the_title'), 5, 2);
        add_filter('get_avatar', array(&$this, 'filter_avatar'), 20, 5);
        add_filter('get_avatar_url', array(&$this, 'filter_avatar_url'), 20, 3);
        add_filter('author_link', array(&$this, 'modify_author_link'), 10, 3 );
        add_filter('edit_profile_url', array(&$this, 'modify_edit_profile_link'), 10, 3 );
        add_filter('get_comment_author_link', array(&$this,'new_comment_author_profile_link'),10,3);

        // brute force
        add_filter('authenticate', array('PeepSoBruteForce', 'authenticate'), 40, 3);
        add_filter('peepso_profile_notifications', array('PeepSoBruteForce', 'edit_notifications_fields'), 100);

        // Is called when a login attempt fails
        // Hence Update our records that the login failed
        add_action('wp_login_failed', array('PeepSoBruteForce', 'login_failed'), 10, 2);

        // Is called before displaying the error message so that we dont show that the username is wrong or the password
        // Update Error message
        #need to verify this
        add_action('wp_login_errors', array('PeepSoBruteForce', 'error_handler'), 10001, 2);

        // reset password emails PeepSo/peepso#2669
        add_action('retrieve_password_key', array('PeepSoBruteForce', 'retrieve_password_key'), 10, 2);
        add_filter('allow_password_reset', array('PeepSoBruteForce', 'allow_password_reset'), 10, 2);

        add_action('peepso_action_render_user_name_before', function($user_id) {
            if ('ban' == PeepSoUser::get_instance($user_id)->get_user_role()) {
                echo "<del>";
            }
        },99,2);

        add_action('peepso_action_render_user_name_after', function($user_id) {
            if ('ban' == PeepSoUser::get_instance($user_id)->get_user_role()) {
                echo "</del>";
            }
        },99,2);

        add_filter('peepso_navigation_profile', array(&$this, 'filter_peepso_navigation_profile'), -1);
        add_filter('peepso_navigation_profile', array(&$this, 'filter_peepso_navigation_profile_final'), 99999);

        if (defined('DOING_CRON') && DOING_CRON) {
            PeepSoCron::initialize();
        }

        add_action('plugins_loaded', array(&$this, 'load_textdomain'));

        add_action('init', function(){
            if(isset($_GET['force-check']) && $_GET['force-check']) {
                // Delete all PeepSo perishables if someone is force checking updates
                PeepSo3_Mayfly::clr(TRUE);
            }

            // Grab all registered field types
            $this->field_types = apply_filters('peepso_admin_profile_field_types', $this->field_types);

            $check_plugins = PeepSo3_Mayfly::get(PeepSoAdmin::MAYFLY_PLUGINS);

            if (NULL == $check_plugins) {

                $peepso_is_offline = FALSE;

                if(!empty(PeepSo3_Mayfly::get('peepso_is_offline'))) {
                    $peepso_is_offline = TRUE;
                } else {

                    if(PeepSoApiRateLimit::check('edd_products_json',10)) {

                        global $wp_version;
                        $license = strlen(PeepSo::get_option('bundle_license')) ? 1 : 0;
                        $response = wp_remote_get('https://www.peepso.com/products.json?version=' . PeepSo::PLUGIN_VERSION . '&wp_version=' . $wp_version . '&locale=' . get_locale() . '&php=' . PHP_VERSION . '&hide_bundle_offer=' . $license, array('timeout' => 10));

                        if (is_array($response)) {
                            $plugins = json_decode($response['body']);
                            PeepSo3_Mayfly::set(PeepSoAdmin::MAYFLY_PLUGINS, $plugins, HOUR_IN_SECONDS);
                        }

                        if (is_wp_error($response)) {
                            $peepso_is_offline = TRUE;
                            PeepSo3_Mayfly::set('peepso_is_offline', 1, 3600);
                        }
                    }
                }
            }
        });

        // setup plugin's hooks
        if (is_admin() && !PeepSo::is_api_request()) {

            // #3305 delete the perishables when plugins are activated / deactivated
            add_action('pre_update_option_active_plugins', function($new, $old){
                if($new!=$old) {
                    // this fires too early for PeepSo Mayfly to be available - use transients?
                    //delete_transient( 'peepso_resort_active_plugins' );
                    PeepSo3_Mayfly::del( 'peepso_resort_active_plugins' );
                }
                return $new;
            },10,2);

            // #3305 make sure Foundation is always on top of the plugin list and everything else is on the bottom
            add_action('init', function(){

                $mayfly = 'peepso_resort_active_plugins';

                if( NULL != PeepSo3_Mayfly::get($mayfly) ) {
                    // this fires too early for PeepSo Mayfly to be available - use transients
                    //if( get_transient($mayfly) ) {
                    return;
                }

                $search = '/peepso.php';

                $plugins = get_option('active_plugins');

                $resort_top = array();
                $resort_middle = array();
                $resort_bottom = array();

                foreach($plugins as $k=>$v) {
                    if(stristr($v, $search)) {
                        $resort_top[] = $v;
                    } else {
                        if(stristr($v, 'peepso')) {
                            $resort_bottom[]=$v;
                        } else {
                            $resort_middle[] = $v;
                        }
                    }
                }

                $plugins = array_merge($resort_top, $resort_middle, $resort_bottom);

                update_option('active_plugins', $plugins);

                PeepSo3_Mayfly::set($mayfly, 1, 5);
                // this fires too early for PeepSo Mayfly to be available - use transients
                // set_transient($mayfly, 1, 5);
            });

            add_action('init', function(){
                if(isset($_GET['page']) && 'peepso-reports' == $_GET['page']) {
                    PeepSo::redirect(admin_url('admin.php?page=peepso-manage&tab=reports'));
                    die();
                }
            });

            add_action('admin_init', array(&$this, 'can_install'));
            add_action('admin_init', function() {
                // PeepSo Early Access - ability to get BETA releases via Easy Digital Downloads API
                if(apply_filters('peepso_foundation_update_override', FALSE)) {
                    //PeepSoLicense::check_updates_new(self::PLUGIN_EDD, self::PLUGIN_SLUG_EDD, self::PLUGIN_VERSION, __FILE__, TRUE, TRUE);
                }
            });

            add_action('init', array(&$this, 'check_admin_access'));

            add_action('peepso_init', array(&$this, 'check_plugins'));

            PeepSoAdmin::get_instance();

            // Additional Core Table since PeepSo Core will not reactivate
            PeepSoGdpr::create_table();
            PeepSoBruteForce::create_table();

            if (NULL !== get_option('peepso_install_date', NULL)) {
                // if multiple crons are failing, only notify about one
                $did_notice = FALSE;

                if(0==PeepSo::get_option('disable_mailqueue')) {
                    if (!stristr(json_encode(_get_cron_array()), PeepSo::CRON_MAILQUEUE)) {
                        if(!$did_notice) {
                            $did_notice = TRUE;
                            add_action('admin_notices', array($this, 'mailqueue_notice'));
                        }
                    }
                }

                if(0==PeepSo::get_option('disable_maintenance')) {
                    if (!stristr(json_encode(_get_cron_array()), PeepSo::CRON_MAINTENANCE_EVENT)) {
                        if(!$did_notice) {
                            $did_notice = TRUE;
                            add_action('admin_notices', array($this, 'maintenance_notice'));
                        }
                    }
                }

                if(0==PeepSo::get_option('gdpr_external_cron')) {
                    if (!stristr(json_encode(_get_cron_array()), PeepSo::CRON_GDPR_EXPORT_DATA)) {
                        if(!$did_notice) {
                            $did_notice = TRUE;
                            add_action('admin_notices', array($this, 'gdpr_external_cron_notice'));
                        }
                    }
                }

                // Print warnigns about missing shortcodes
                add_action('admin_notices', function() {

                    if(isset($_GET['tab']) && 'navigation'==$_GET['tab']) { return; }

                    $shortcodes = PeepSo::get_instance()->all_shortcodes();


                    foreach($shortcodes as $sc => $method) {

                        $options = PeepSo::get_instance()->pages_with_shortcode($sc);
                        $error = PeepSo::get_instance()->check_shortcode($sc, $options);
                        if(strlen($error)) {

                            echo '<div class="error peepso">';
                            echo __('PeepSo can\'t locate all required shortcodes.','peepso-core');
                            echo ' <a href="' . admin_url('admin.php?page=peepso_config&tab=navigation') . '">' . __('Review your navigation settings','peepso-core') . '</a>.';
                            echo '</div>';
                            break;
                        }
                    }
                });
            }


            delete_option('peepso_email_register');

            // modify admin footer text
            add_filter('admin_footer_text', array(&$this, 'remove_footer_admin'), 100, 1);

            // plugin name for WP filters
            $path = basename(dirname(__FILE__)).'/'.basename(__FILE__);

            // hijack update_plugins

            add_action('peepso_config_after_save-advanced', function() {
                delete_site_transient('update_plugins');
            });

            // Delete licensing maylfy when saving licenses
            add_action('peepso_config_after_save-site', function() {
                PeepSo3_Mayfly::del('license_to_id');
            });

            // Attach Community link in wp-admin "Home" menu
            add_action( 'admin_bar_menu', function($wp_admin_bar) {
                $wp_admin_bar->add_menu(array(
                    'parent' => 'site-name',
                    'id' => 'peepso-home',
                    'title' => __('Visit Community', 'peepso-core'),
                    'href' => PeepSo::get_page('activity'),
                ));

                return $wp_admin_bar;
            }, 90);

            // Bundle
            add_action('admin_notices', function () {

                // Do nothing for demo pages
                if(PeepSoSystemRequirements::is_demo_site()) { return; }

                $major_version = explode('.',PeepSo::PLUGIN_VERSION);
                while(count($major_version) > 2) {
                    array_pop($major_version);
                }
                $major_version = implode('',$major_version);

                $option_name = 'peepso_hide_installer_'.$major_version;

                if(isset($_GET['page']) && 'peepso-installer' == $_GET['page']) {
                    return;
                }

                if (isset($_GET['peepso_hide_installer'])) {
                    update_user_option(get_current_user_id(), $option_name, 1);
                }

                if (isset($_GET['peepso_hide_installer_reset'])) {
                    delete_user_option(get_current_user_id(), $option_name);
                }

                // do nothing if user dismissed it
                if (get_user_option($option_name)) {
                    return;
                }

                PeepSoTemplate::exec_template('admin', 'admin_notice_installer');
            });

            add_action('admin_notices', function() {

                // Do nothing for demo pages
                if(PeepSoSystemRequirements::is_demo_site()) { return; }

                $new_addon = PeepSo3_Mayfly_Int::get('installer_has_new');
                if ($new_addon) {
                    $dismissed = get_user_option('peepso_hide_addon_' . $new_addon, get_current_user_id());
                    if ($dismissed) {
                        return;
                    }
                    ?>
                    <div class="notice notice-warning peepso peepso-new-addon ps-notice">

                        <h3>There is a new PeepSo add-on available!</h3>
                        <?php echo sprintf(__('PeepSo released a new add-on! Check out the <a href="%s" aria-label="PeepSo installer!">Installer</a> to learn more.', 'peepso-core'), admin_url('admin.php?page=peepso-installer')); ?>

                        <p>
                            <a href="#" class="ps-notice__dismiss peepso-dismiss-new-addon">
                                <i class="gcir gci-times-circle"></i>
                            </a>
                        </p>
                    </div>
                    <script>
                        setTimeout(function() {
                            if (! typeof jQuery) return;
                            var $warning = jQuery('.peepso-new-addon');
                            var $dismiss = jQuery('.peepso-dismiss-new-addon');
                            $dismiss.on('click', function(e) {
                                e.preventDefault();
                                $warning.hide();
                                jQuery.post({ url: ajaxurl, data: { action: 'peepso_dismiss_new_addon' } });
                            });
                        }, 0 );
                    </script>
                    <?php
                }
            });

            add_action('admin_notices', function() {
                $release_date = get_option('peepso_release_date');
                $remove_all_dismiss = FALSE;

                if ($release_date != PeepSoSystemRequirements::RELEASE_DATE) {
                    // update release date on db
                    $release_date = PeepSoSystemRequirements::RELEASE_DATE;
                    update_option('peepso_release_date', $release_date);
                    $remove_all_dismiss = TRUE;
                }

                if (isset($_GET['peepso_old_version_reset_dismiss'])) {
                    $remove_all_dismiss = TRUE;
                }

                if ($remove_all_dismiss) {
                    // remove all dismiss
                    global $wpdb;
                    $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->usermeta WHERE meta_key LIKE %s", '%peepso_hide_old_version%'));
                }

                if (isset($_GET['peepso_old_version_date'])) {
                    $date = strtotime($_GET['peepso_old_version_date']);
                } else {
                    $date = current_time('timestamp');
                }

                if (strtotime(PeepSoSystemRequirements::RELEASE_DATE) > $date) {
                    return;
                }

                $dismissed = FALSE;
                $current_month = PeepSo3_Mayfly::get('peepso_old_version_current_month');

                if (!$current_month || isset($_GET['peepso_old_version_date'])) {
                    $months = range(1, 60, 1); // generates: 1, 2, 3, 4, ... 60
                    $release_date = strtotime($release_date);

                    foreach ($months as $month) {
                        // compare dates
                        $check_date = strtotime('+' . $month . ' month', $release_date);
                        if ($date >= $check_date) {
                            $current_month = $month;
                            $dismissed = get_user_option('peepso_hide_old_version_' . $month, get_current_user_id());

                            if (!isset($_GET['peepso_old_version_date'])) {
                                PeepSo3_Mayfly::set('peepso_old_version_current_month', $current_month, DAY_IN_SECONDS);
                            }
                        }
                    }
                } else {
                    $dismissed = get_user_option('peepso_hide_old_version_' . $current_month, get_current_user_id());
                }

                if ($dismissed) {
                    return;
                }

                if ($current_month) {
                    $notice_content = PeepSo3_Helper_Remote_Content::get('old_version_notice');
                }

                if (!isset($notice_content) || empty($notice_content)) {
                    return;
                }
                ?>
                <div class="notice notice-error peepso peepso-old-version ps-notice">
                    <p>
                        <?php
                        $age = $current_month.' months old';

                        if($current_month >= 12) {
                            $age = 'over a year old';
                        }

                        $from = [
                            '<span id="age"></span>',
                            '<span id="admin_notice_help"></span>',
                        ];

                        $to = [
                            $age,
                            PeepSoTemplate::exec_template('admin','admin_notice_help',[],TRUE),
                        ];

                        echo str_ireplace($from,$to, $notice_content); ?>
                    </p>
                    <p>
                        <a href="#" class="ps-notice__dismiss peepso-dismiss-old-version" data-month="<?php echo $current_month; ?>">
                            <i class="gcir gci-times-circle"></i>
                        </a>
                    </p>
                </div>
                <script>
                    setTimeout(function() {
                        if (! typeof jQuery) return;
                        var $warning = jQuery('.peepso-old-version');
                        var $dismiss = jQuery('.peepso-dismiss-old-version');
                        $dismiss.on('click', function(e) {
                            e.preventDefault();
                            $warning.hide();
                            jQuery.post({ url: ajaxurl, data: { action: 'peepso_dismiss_old_version', month: jQuery(this).attr('data-month') } });
                        });
                    }, 0 );
                </script>
                <?php
            });

            // Blogposts
            add_action('peepso_config_after_save-blogposts', array(&$this, 'blogposts_rebuild_cache'));

            add_filter('option_users_can_register', function($option) {
                if (PeepSoConfigSettings::get_instance()->get_option('site_registration_disabled') === null) {
                    PeepSoConfigSettings::get_instance()->set_option('site_registration_disabled', $option == 1 ? 0 : 1);
                }
                return $option;
            });

            if ( isset( $_GET['legacy-widget-preview'] ) ) {
                add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
                add_action('wp_enqueue_scripts',array(&$this, 'enqueue_scripts_overrides'), 99);

                add_action('wp_footer', array(&$this, 'enqueue_scripts_data'), 1);
            }
        } else {
            $this->register_shortcodes();
            add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
            add_action('wp_enqueue_scripts',array(&$this,'enqueue_scripts_overrides'), 99);

            add_action('wp_head', array(&$this, 'opengraph_tags'));
            add_action('wp_head', array(&$this, 'peepso_change_page_title'), 100, 2);
            add_action('wp_loaded', array(&$this, 'check_ajax_query'), -1);
            add_action('wp', array(&$this, 'check_query'), -1);
            add_action('wp_footer', array(&$this, 'enqueue_scripts_data'), 1);

            // oEmbed handling
            add_filter('oembed_discovery_links', array(&$this,'modify_oembed_links'),100,1);

            // TranslatePress
            if(PeepSo::is_dev_mode('tpsvg')) {

                add_filter('trp_flags_path', function ($default_path, $language_code) {
                    return plugin_dir_url(__FILE__) . 'assets/images/flags/';
                }, 10, 2);

                add_filter('trp_flag_file_name', function ($flag, $lang) {
                    $lang = strtolower($lang);
                    if (strstr($lang, '_')) {
                        $lang = explode('_', $lang);
                        $lang = $lang[1];
                    }

                    $file = $lang . '.svg';

                    return $file;
                }, 10, 2);
            }

            //  WP admin bar visibility
            add_filter('show_admin_bar', function($show) {

                $wp_toolbar_enable = PeepSo::get_option_new('wp_toolbar_enable');

                if( 4 == $wp_toolbar_enable) { return $show; }

                if ( 1==$wp_toolbar_enable || (2==$wp_toolbar_enable  && PeepSo::is_admin()) ) { return TRUE; }

                return FALSE;
            },99999,1);
        }

        PeepSoTags::get_instance();
        PeepSoLocation::get_instance();
        PeepSoMoods::get_instance();
        PeepSoPostBackgrounds::get_instance();
        PeepSoWordFilter::get_instance();
        PeepSoGiphy::get_instance();
        PeepSoVIP::get_instance();

        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $poll_plugin = array_filter(get_plugins(), function ($var) {
            return ($var['Author'] == 'PeepSo' && strpos($var['Name'], 'Polls') !== FALSE);
        });

        $enable_poll = TRUE;
        if (!empty($poll_plugin)) {
            $keys = array_keys($poll_plugin);
            if (is_plugin_active($keys[0])) {
                $enable_poll = FALSE;
            }
        }

        if (PeepSo::get_option_new('polls_enable') && $enable_poll) {
            PeepSoPolls::get_instance();
        }

        PeepSoFollower::get_instance();

        // #2825 clean up third party registrations to make sure there are no emails as usernames
        if(PeepSo::get_option('thirdparty_username_cleanup') || PeepSo::get_option_new('no_username_on_register')) {

            // hook early into registration process
            add_filter('pre_user_login', function($login){

                // if the username might be an email
                if(strpos($login, '@') !== FALSE) {
                    $old_login = $login;

                    // only grab the fist part
                    $login = explode('@', $login);
                    $login = $login[0];

                    // if the first part is empty or too short, generate a random one
                    if(strlen($login) < 3) {
                        $login = substr(md5(time()),0,5);
                    }

                    if(strlen($login) > 50) {
                        $login = substr($login,0,40);
                    }

                    // avoid colliding with an exising username
                    if ( username_exists( $login ) ) {

                        $count = 1;

                        do {
                            $new_login= $login.$count;
                            $count++;
                        } while ( username_exists( $new_login ) );

                        $login = $new_login;
                    }

                    add_filter('pre_user_nicename', function($nicename) use ($login) {
                        return $login;
                    });
                }

                return $login;
            });



        }

        add_action('init', array(&$this, 'init_callback'));
        add_action('init', array(&$this, 'init_mysql_big_size'));


        // activation hooks
        register_activation_hook(__FILE__, array(&$this, 'activate'));
        register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));

        // register widgets
        add_action('widgets_init', array(&$this, 'widgets_init'));
        add_filter( 'peepso_widget_prerender', array(&$this, 'get_widgets_in_position'));
        add_filter( 'peepso_widget_form', array(&$this, 'get_widget_form'));
        add_filter( 'peepso_widget_list_positions', array(&$this, 'get_widget_positions'));

        // WP Login Form
        if(PeepSo::get_option_new('recaptcha_login_enable', 0)) {
            add_action( 'login_form', array(&$this, 'login_form'));
            add_action( 'login_footer', array(&$this, 'login_footer'));
            add_action( 'authenticate', array(&$this, 'login_recaptcha_validation'), 20, 3);
        }

        /** THIRD PARTY REGISTRATIONS
         * set default notifications
         * automatically approve user
         * ignores email veirifcation and admin approval
         */
        add_action( 'user_register', function( $user_id ) {
            $PeepSoUser = PeepSoUser::get_instance($user_id);
            $PeepSoUser->reset_user_notification();
            $PeepSoUser->reset_user_digest();
            $PeepSoUser->set_user_role('member');
        }, 10, 1 );

        add_filter('peepso_access_types', array(&$this, 'filter_access_types'));

        add_filter( 'wp_title', array(&$this, 'peepso_change_page_title'), 100, 2);
        add_filter( 'pre_get_document_title', array(&$this, 'peepso_change_page_title'), 100, 2);

        add_action('deleted_user', array(&$this, 'action_deleted_user'));
        add_action('peepso_profile_completeness_redirect', array(&$this, 'action_peepso_profile_completeness_redirect'));
        add_action('peepso_action_report_create', array(&$this, 'action_peepso_report_unpublish_automatically'));

        add_filter('peepso_filter_opengraph_' . self::MODULE_ID, array(&$this, 'peepso_filter_opengraph'), 10, 2);

        // move from activity
        add_filter('peepso_post_extras', array(&$this, 'filter_post_extras'), 10, 1);

        add_filter('display_post_states', array(&$this, 'filter_display_post_states'), 10, 2);

        add_action('peepso_activity_post_attachment', array(&$this, 'post_attach_repost'), 10, 1);

        /** Plugin Name: Add Admin Bar Icon */
        add_action('admin_bar_menu', array(&$this, 'wp_admin_bar_menu'), 999 );

        add_filter('upload_size_limit', function($size) { if ($size == 0) return 4 * 1024 * 1024; else return $size; }, 100);

        add_action('wp_ajax_peepso_user_reset_notifications',function(){
            $PeepSoInput = new PeepSoInput();
            $user_id = get_current_user_id();

            // SQL safe, WP sanitizes it
            if (wp_verify_nonce($PeepSoInput->value('_wpnonce','',FALSE), 'peepso-user-reset-notifications' ) === FALSE) {
                die(json_encode(array('success'=>0, 'messages'=>[__('Could not verify nonce.', 'peepso-core')])));
            }

            if($user_id) {
                $PeepSoUser = PeepSoUser::get_instance($user_id);
                $PeepSoUser->reset_user_notification();
            }

            PeepSo::redirect(PeepSoUser::get_instance()->get_profileurl().'about/notifications/');
        });

        add_action('wp_ajax_peepso_submit_uninstall_reason', function(){
            // SQL safe, WP sanitizes it
            $PeepSoInput = new PeepSoInput();
            if (wp_verify_nonce($PeepSoInput->value('_wpnonce','',FALSE), 'peepso-submit-uninstall-reason' )) {
                PeepSo3_Stats::get_instance()->deactivation_feedback();
                echo json_encode(array('success'=>1));
                exit;
            } else {
                die(json_encode(array('success'=>0, 'messages'=>[__('Could not verify nonce.', 'peepso-core')])));
            }
        });

        add_action('wp_ajax_peepso_user_subscribe_onsite', function(){
            $user_id = get_current_user_id();
            $debug = array();
            if($user_id) {
                $PeepSoProfile = PeepSoProfile::get_instance();
                $alerts = $PeepSoProfile->get_available_alerts();

                $user_alerts = get_user_meta($user_id, 'peepso_notifications', TRUE);
                if (!is_array($user_alerts)) {
                    $user_alerts = array();
                }

                $result_count = 0;

                foreach($user_alerts as $id=>$alert) {
                    if(strstr($alert, '_notification')) {
                        unset($user_alerts[$id]);
                        $result_count++;
                    }
                }

                update_user_meta($user_id, 'peepso_notifications', $user_alerts);

                // Resubscribe all groups ONSITE
                if (class_exists('PeepSoGroupsPlugin')) {
                    global $wpdb;
                    $wpdb->query("UPDATE `{$wpdb->prefix}peepso_group_followers` SET `gf_notify`=1 WHERE `gf_user_id`=$user_id");
                }
            }


            PeepSo::redirect(PeepSoUser::get_instance()->get_profileurl().'about/notifications/');
        });

        /*
         * @SINCE 2.7.2 == DISABLE ALL
         *
         * unsubscribe onsite forces unsubscribe email
         */
        add_action('wp_ajax_peepso_user_unsubscribe_onsite', function(){
            $PeepSoInput = new PeepSoInput();
            $user_id = get_current_user_id();

            // SQL safe, WP sanitizes it
            if (wp_verify_nonce($PeepSoInput->value('_wpnonce','',FALSE), 'peepso-user-unsubscribe-onsite' ) === FALSE) {
                die(json_encode(array('success'=>0, 'messages'=>[__('Could not verify nonce.', 'peepso-core')])));
            }

            if($user_id) {
                $PeepSoProfile = PeepSoProfile::get_instance();
                $alerts = $PeepSoProfile->get_available_alerts();

                $user_alerts = get_user_meta($user_id, 'peepso_notifications', TRUE);
                if (!is_array($user_alerts)) {
                    $user_alerts = array();
                }

                $result_count = 0;

                foreach ($alerts as $alert) {
                    foreach ($alert['items'] as $alert) {
                        $key = $alert['setting'] . '_notification';

                        if (!in_array($key, $user_alerts)) {
                            $user_alerts[] = $key;
                            $result_count++;
                        }

                        $key = $alert['setting'] . '_email';

                        if (!in_array($key, $user_alerts)) {
                            $user_alerts[] = $key;
                            $result_count++;
                        }
                    }
                }
            }

            update_user_meta($user_id, 'peepso_notifications', $user_alerts);

            // Unsubscribe all groups ONSITE
            if (class_exists('PeepSoGroupsPlugin')) {
                global $wpdb;
                $wpdb->query("UPDATE `{$wpdb->prefix}peepso_group_followers` SET `gf_notify`=0 WHERE `gf_user_id`=$user_id");
                $wpdb->query("UPDATE `{$wpdb->prefix}peepso_group_followers` SET `gf_email`=0 WHERE `gf_user_id`=$user_id");
            }

            PeepSo::redirect(PeepSoUser::get_instance()->get_profileurl().'about/notifications/');
        });


        /*
         * @SINCE 2.7.2 == ENABLE ALL
         *
         * subscribe email forces subscribe onsite
         */
        add_action('wp_ajax_peepso_user_subscribe_all', function(){

            $PeepSoInput = new PeepSoInput();
            $user_id = get_current_user_id();

            // SQL safe, WP sanitizes it
            if (wp_verify_nonce($PeepSoInput->value('_wpnonce','',FALSE), 'peepso-user-subscribe-all' ) === FALSE) {
                die(json_encode(array('success'=>0, 'messages'=>[__('Could not verify nonce.', 'peepso-core')])));
            }

            if($user_id) {

                // Subscribe to all email alerts
                $user_alerts = get_user_meta($user_id, 'peepso_notifications', TRUE);
                if (!is_array($user_alerts)) {
                    $user_alerts = array();
                }

                $result_count = 0;

                foreach($user_alerts as $id=>$alert) {
                    unset($user_alerts[$id]);
                    $result_count++;

//                    if(strstr($alert, '_email')) {
//                        unset($user_alerts[$id]);
//                        $result_count++;
//                    }
//
//                    if(strstr($alert, '_notification')) {
//                        unset($user_alerts[$id]);
//                        $result_count++;
//                    }
                }

                update_user_meta($user_id, 'peepso_notifications', $user_alerts);

                // Resubscribe all groups EMAIL
                if (class_exists('PeepSoGroupsPlugin')) {
                    global $wpdb;
                    $wpdb->query("UPDATE `{$wpdb->prefix}peepso_group_followers` SET `gf_email`=1 WHERE `gf_user_id`=$user_id");
                    $wpdb->query("UPDATE `{$wpdb->prefix}peepso_group_followers` SET `gf_notify`=1 WHERE `gf_user_id`=$user_id");
                }

                // Enable Email Digest
                if (class_exists('PeepSoEmailDigest')) {
                    update_user_meta($user_id, 'peepso_email_digest_receive_enabled', 1);
                }
            }

            PeepSo::redirect(PeepSoUser::get_instance()->get_profileurl().'about/notifications/');
        });

        add_action('wp_ajax_peepso_user_unsubscribe_emails', function() {

            global $wpdb;
            $debug = array();
            $PeepSoInput = new PeepSoInput();
            $user_id = $PeepSoInput->int('user_id', get_current_user_id());

            // SQL safe, WP sanitizes it
            if (wp_verify_nonce($PeepSoInput->value('_wpnonce','',FALSE), 'peepso-user-unsubscribe-emails' )) {
                if(PeepSo::is_admin() || $user_id == get_current_user_id()) {
                    // Disable all alerts
                    $PeepSoProfile = PeepSoProfile::get_instance();
                    $alerts = $PeepSoProfile->get_available_alerts();

                    $user_alerts = get_user_meta($user_id, 'peepso_notifications', TRUE);
                    if (!is_array($user_alerts)) {
                        $user_alerts = array();
                    }

                    $result_count = 0;

                    foreach ($alerts as $alert) {
                        foreach ($alert['items'] as $alert) {
                            $key = $alert['setting'] . '_email';

                            if (!in_array($key, $user_alerts)) {
                                $user_alerts[] = $key;
                                $result_count++;
                            }
                        }
                    }

                    update_user_meta($user_id, 'peepso_notifications', $user_alerts);

                    $debug['alerts'] = sprintf(__('Disabled %d email notifications', 'peepso-core'), $result_count);

                    // Disable Groups
                    if (class_exists('PeepSoGroupsPlugin')) {
                        $count = $wpdb->query("UPDATE `{$wpdb->prefix}peepso_group_followers` SET `gf_email`=0 WHERE `gf_user_id`=$user_id AND  `gf_email`=1");
                        $debug['groups'] = sprintf(__('Disabled %d group email subscriptions', 'peepso-core'), $count);
                    }
                } else {
                    die(json_encode(array('success'=>0, 'messages'=>[__('You do not have enough permissions.', 'peepso-core')])));
                }
            } else {
                die(json_encode(array('success'=>0, 'messages'=>[__('Could not verify nonce.', 'peepso-core')])));
            }

            if(isset($_GET['redirect'])) {
                PeepSo::redirect(PeepSoUser::get_instance()->get_profileurl().'about/notifications/');
            }

            die(json_encode(array('success'=>1, 'messages'=>$debug)));
        });

        add_action('wp_ajax_peepso_preview_email', function() {
            $override = PeepSo::get_option('emails_override_entire_html','');
            if(strlen($override)) {
                echo stripslashes($override);
            } else {
                // load the general email template
                echo PeepSoTemplate::exec_template('general', 'email', NULL, TRUE);
            }
            die();
        });

        if(class_exists('PeepSoBlogPosts')) {
            PeepSoBlogPosts::get_instance();
        }

        if (class_exists('PeepSoEmbed')) {
            PeepSoEmbed::init();
        }

        if(class_exists('PeepSoMaintenanceFactory') && class_exists('PeepSoMaintenanceCore')) {
            new PeepSoMaintenanceCore();
        }

        if(class_exists('PeepSoMaintenanceFactory') && class_exists('PeepSoMaintenanceBannedUsers')) {
            new PeepSoMaintenanceBannedUsers();
        }

        if(class_exists('PeepSoMaintenanceFactory') && class_exists('PeepSoMaintenanceFollowers')) {
            new PeepSoMaintenanceFollowers();
        }

        if(class_exists('PeepSoMaintenanceFactory') && class_exists('PeepSo3_Maintenance_Activity_Bump')) {
            new PeepSo3_Maintenance_Activity_Bump();
        }

        if(class_exists('PeepSoMaintenanceFactory') && class_exists('PeepSo3_Maintenance_Pin_Until')) {
            new PeepSo3_Maintenance_Pin_Until();
        }

        if (PeepSo::get_option('override_admin_navbar', 0) === 1) {
            add_action( 'admin_bar_menu', function($wp_admin_bar) {
                $user_id      = get_current_user_id();

                if ( ! $user_id )
                    return;

                // remove original admin bar
                $wp_admin_bar->remove_menu('my-account');
                $wp_admin_bar->remove_menu('edit-profile');
                $wp_admin_bar->remove_menu('user-info');
                $wp_admin_bar->remove_menu('logout');

                $user = PeepSoUser::get_instance($user_id);
                $avatar = $user->get_avatar();
                $avatar = '<img width="64" class="avatar avatar-64 photo" src="' . $avatar . '" />';

                $wp_admin_bar->add_group( array(
                    'parent' => 'my-account',
                    'id'     => 'user-actions',
                ) );

                $user_info = $avatar . "<span class='display-name'>" . $user->get_fullname() . "</span>";

                $profile_url =  $user->get_profileurl();

                if (  $user->get_fullname() !== $user->get_username() )
                    $user_info .= "<span class='username'>" . $user->get_username() . "</span>";

                $howdy  = sprintf( __( 'Howdy, %s', 'peepso-core' ), '<span class="display-name">' . $user->get_username() . '</span>' );
                $class  = empty( $avatar ) ? '' : '';

                $wp_admin_bar->add_menu( array(
                    'id'        => 'my-account',
                    'parent'    => 'top-secondary',
                    'title'     => $howdy . $avatar,
                    'href'      => $profile_url,
                    'meta'      => array(
                        'class'     => 'with-avatar',
                    ),
                ) );

                $wp_admin_bar->add_menu( array(
                    'parent' => 'user-actions',
                    'id'     => 'user-info',
                    'title'  => $user_info,
                    'href'   => $profile_url,
                    'meta'   => array(
                        'tabindex' => -1,
                    ),
                ) );

                $wp_admin_bar->add_menu( array(
                    'parent' => 'user-actions',
                    'id'     => 'edit-profile',
                    'title'  => __( 'Edit My Profile', 'peepso-core' ),
                    'href'   => $profile_url . 'about',
                ) );

                $navbars = PeepSoGeneral::get_instance()->get_navigation('secondary');
                if (count($navbars) > 0) {
                    foreach ($navbars as $navbar) {
                        if (strpos($navbar['class'], 'ps-dropdown') !== FALSE) {
                            foreach ($navbar['menu'] as $menu_item) {
                                if (in_array($menu_item['label'], array('Stream', 'About'))) {
                                    continue;
                                }
                                $wp_admin_bar->add_menu( array(
                                    'parent' => 'user-actions',
                                    'id'     => $menu_item['label'],
                                    'title'  => $menu_item['label'],
                                    'href'   => $menu_item['href'],
                                    'meta'   => array(
                                        'tabindex' => -1,
                                    ),
                                ) );
                            }
                        }
                    }
                }

            }, 100);
        }

        add_action( 'activated_plugin', function() {
            $path = str_replace( WP_PLUGIN_DIR . '/', '', __FILE__ );
            if ( $plugins = get_option( 'active_plugins' ) ) {
                if ( $key = array_search( $path, $plugins ) ) {
                    array_splice( $plugins, $key, 1 );
                    array_unshift( $plugins, $path );
                    update_option( 'active_plugins', $plugins );
                }
            }
        } );

        add_filter( 'external_link_whitelist', function( $links = '' ) {
            $whitelisted_links = PeepSo::get_option( 'external_link_whitelist', '' );

            if( ! PeepSo::get_option( 'external_link_warning_social_sharing', '' ) ) {
                $sharer_links = PeepSoShare::get_instance()->get_links();
                if ( count( $sharer_links ) ) {
                    foreach( $sharer_links as $key => $link ) {
                        if(is_array($link)) {
                            $whitelisted_links .= "\n" . preg_replace('#\?.*$#', '', $link['url']);
                        }
                    }
                }
            }

            return $links . "\n" . $whitelisted_links;
        } );

        add_filter('wp_authenticate_user', function($user, $password) {
            if (isset($_POST['log'])) {
                $email = trim($_POST['log']);
                $login_with_email = PeepSo::get_option('login_with_email', 0);
                $error = new WP_Error();
                if ($login_with_email == 2 && !is_email($email)) {
                    $error->add('invalid_login', __('Invalid email address.', 'peepso-core'));
                    return $error;
                }

                if ($login_with_email == 1 && !is_email($email) && isset($user->ID) && PeepSo::is_admin($user->ID)) {
                    $error->add('invalid_login', __('Invalid email address.', 'peepso-core'));
                    return $error;
                }
            }

            return $user;
        }, 10, 3);

        add_action('pre_get_posts', function($query) {
            $scheme = 'http';
            if (!empty( $_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
                $scheme = 'https';
            }
            $current_url = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            if (strpos($current_url, PeepSo::get_page('profile')) !== FALSE && strpos($current_url, 'embed') !== FALSE) {
                $query->is_embed = TRUE;
            }
        });

        add_filter('the_permalink', function($url, $post = 0) {
            $scheme = 'http';
            if (!empty( $_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
                $scheme = 'https';
            }
            $current_url = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            if (strpos($current_url, PeepSo::get_page('profile')) !== FALSE && strpos($current_url, 'embed') !== FALSE) {
                $url = str_replace('/embed', '', $current_url);
            }

            return $url;
        }, 10);

        if (PeepSo::get_option('disable_questionmark_urls', 0) === 1 && !wp_doing_ajax() && strpos($_SERVER['REQUEST_URI'], 'ajax' ) === FALSE && strpos($_SERVER['REQUEST_URI'], PeepSo::get_option('page_profile') . '/') !== FALSE) {
            add_filter('request', function($q) {
                if (isset($q['attachment'])) {
                    $q['pagename'] = $q['page'] = $q['attachment'];
                    unset($q['attachment']);
                }

                return $q;
            });
        }
    }


    /**
     * Determine if something changed in chats for current user and if get_chats() call is necessary
     * @return int|void
     */
    public function ajax_should_get_notifications($return = FALSE)
    {
        $delay_min 			= 1000;
        $delay_max 		  	= 20000;
        $delay_multiplier 	= 1.5;

        $delay 				= intval($_POST['delay']);

        $multiply = TRUE;

        if($delay<$delay_min) {
            $multiply = FALSE; // do not multiply the default (first request without param)
            $delay = $delay_min;
        }

        $chats = 0;

        // if the option is set, it means something changed and we should refresh
        if(get_user_option('peepso_should_get_notifications')) {
            delete_user_option(get_current_user_id(), 'peepso_should_get_notifications');
            $delay = $delay_min;
            $chats = 1;
        } else {

            if($multiply) {
                $delay = floor($delay * $delay_multiplier);
            }

            if($delay>$delay_max) {
                $delay = $delay_max;
            }
        }

        $resp = array($chats,$delay);

        if($return) {
            return($resp);
        }

        echo json_encode($resp);
        exit();
    }

    public function wp_admin_bar_menu($bar)
    {
        if(0 === PeepSo::get_option('site_show_notification_on_navigation_bar', 0)) {
            return;
        }

        $toolbar = PeepSoGeneral::get_instance()->get_navigation('notifications');
        $toolbar = array_reverse($toolbar);

        foreach ($toolbar as $item => $data) {

            $bar->add_menu( array(
                'id'     => 'toolbar-'. $data['icon'],
                'parent' => 'top-secondary',
                'group'  => null,
                'title'  => '<i class="'. $data['icon']. '"></i><div class="js-counter ps-notif__bubble ps-js-counter">'. ($data['count'] > 0 ? $data['count'] : ''). '</div>',
                'href'   => $data['href'],
                'meta'   => array(
                    'class' => $data['class'],
                    'target' => is_admin() ? '_blank' : '',
                    'title' => $data['label'],
                )
            ) );
        }
    }

    public function check_plugins()
    {
        $mayfly = 'peepso_all_plugins';
        $plugins = apply_filters($mayfly, array());

        if(count($plugins) && $plugins != PeepSo3_Mayfly::get($mayfly)) {

            // We will stick potential warnings in here to render them later in wp-admin
            $plugin_warnings 	= array();

            // Loop throug the plugin list and perform compatibility checks
            foreach($plugins as $file => $class) {

                $plugin = new stdClass();
                $plugin->file	= $file;
                $plugin->class	= $class;
                $plugin->name 	= $class::PLUGIN_NAME;
                $plugin->version= $class::PLUGIN_VERSION;
                $plugin->release= $class::PLUGIN_RELEASE;

                if(defined("$class::PEEPSO_VER_MAX") && defined("$class::PEEPSO_VER_MIN")) {
                    // PEEPSO_VER_MIN and PEEPSO_VER_MAX are present
                    // use them to verify compatibility (default path for 3rd party plugins)
                    $plugin->peepso_min = $class::PEEPSO_VER_MIN;
                    $plugin->peepso_max = $class::PEEPSO_VER_MAX;
                    $plugin->version_check = self::check_version_minmax($plugin->version, $plugin->peepso_min, $plugin->peepso_max);
                } else {
                    // PEEPSO_VER_MIN and PEEPSO_VER_MAX are missing
                    // assume a strict version lock (all official PeepSo, Tools and Extras)
                    $plugin->version_check = self::check_version_compat($plugin->version, $plugin->release);
                }

                // if it's not OK, render an error/warning
                if (1 != $plugin->version_check['compat']) {

                    $plugin_warnings[] = $plugin;

                    add_action('admin_notices', array(&$this, 'plugins_version_notice'));
                }
            }

            add_action('admin_notices', array(&$this, 'plugins_version_notice'));

            if(count($plugin_warnings)) {
                PeepSo3_Mayfly::set( 'peepso_plugins_version_notice', $plugin_warnings );
            } else {
                PeepSo3_Mayfly::del('peepso_plugins_version_notice','', FALSE);
            }
        }
    }


    function action_peepso_profile_completeness_redirect() {

        if(0 == PeepSo::get_option('force_required_profile_fields', 0) || wp_doing_ajax()) {
            return;
        }

        if( 1 == get_user_meta( get_current_user_id(), 'peepso_after_register_profile_complete', TRUE)) {
            return TRUE;
        }

        $user = PeepSoUser::get_instance(get_current_user_id());

        $user->profile_fields->load_fields();
        $stats = $user->profile_fields->profile_fields_stats;

        if($stats['missing_required'] > 0) {
            $url_segment = new PeepSoUrlSegments();
            if ($url_segment->get('2') != 'about') {
                PeepSo::redirect($user->get_profileurl().'/about');
            }
        }
    }

    function action_peepso_report_unpublish_automatically( $data ) {
        if (!PeepSo::get_option('site_reporting_enable', TRUE)) {
            return;
        }

        $num_unpublish_post = PeepSo::get_option('site_reporting_num_unpublish_post', 0);
        if (0 == $num_unpublish_post) {
            return;
        }

        if (empty($data)) {
            return;
        }

        if (0 == $data['rep_module_id']) {
            return;
        }

        $rep = new PeepSoReport();
        $list_rep_by = $rep->get_reported_by($data['rep_external_id'], $data['rep_module_id']);
        $count_rep_by = count($list_rep_by);
        if ($count_rep_by >= $num_unpublish_post) {
            $report = new PeepSoReport();
            $success = $report->unpublish_report($data['rep_id'], true);
        }
    }

    function action_deleted_user( $id )
    {
        global $wpdb;

        // Delete all received and sent notifications

        $sql =  "DELETE FROM {$wpdb->prefix}". PeepSoNotifications::TABLE
            .   " WHERE `not_user_id`='$id' OR  `not_from_user_id`='$id'";

        $wpdb->query($sql);

        // Delete all likes

        $sql = "DELETE FROM {$wpdb->prefix}" . PeepSoLike::TABLE . " WHERE `like_user_id`='$id'";
        $wpdb->query($sql);

        // Delete all peepso related posts

        $wpdb->delete(
            $wpdb->posts,
            array('post_author'=>$id, 'post_type' => PeepSoActivityStream::CPT_POST ),
            array('%d','%s')
        );

        $success = $wpdb->delete(
            $wpdb->posts,
            array('post_author'=>$id, 'post_type' => PeepSoActivityStream::CPT_COMMENT ),
            array('%d','%s')
        );
    }


    public function opengraph_tags()
    {
        global $post;

        $url = PeepSoUrlSegments::get_instance();

        if (is_null($post) || $post->post_type != 'page' || PeepSo::get_option('opengraph_enable') === 0 || empty($url->_shortcode))
        {
            return;
        }

        // default tags
        $tags = array(
            'title'			=> str_replace('{sitename}', get_bloginfo('name'), PeepSo::get_option('opengraph_title')),
            'description'	=> PeepSo::get_option('opengraph_description'),
            'image'			=> PeepSo::get_option('opengraph_image', PeepSo::get_asset('images/landing/register-bg.jpg')),
            'url'			=> (( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
        );

        switch ($url->_shortcode)
        {
            case 'peepso_activity' :

                $post_slug = $url->get(2);
                if (!empty($post_slug))
                {
                    $peepso_activity = new PeepSoActivity();
                    $activity = $peepso_activity->get_activity_by_permalink(sanitize_key($post_slug));

                    if(is_object($activity)) {
                        $activity = apply_filters('peepso_filter_check_opengraph', $activity);
                    }

                    if (is_object($activity) && $activity->act_access == PeepSo::ACCESS_PUBLIC)
                    {
                        $user = PeepSoUser::get_instance($activity->post_author);

                        $tags = apply_filters('peepso_filter_opengraph_' . $activity->act_module_id, $tags, $activity);
                        $tags['title'] .= ' - ' . __('Post by', 'peepso-core') . ' ' . trim(strip_tags($user->get_fullname()));
                        $tags['description'] = strlen($human_friendly = get_post_meta($activity->ID, 'peepso_human_friendly', TRUE)) ? $human_friendly : strip_tags(apply_filters('peepso_remove_shortcodes', $activity->post_content, $activity->ID));
                        $tags['description'] = (!empty($tags['description'])) ? $tags['description'] : PeepSo::get_option('opengraph_description');

                        if ($activity->act_module_id == PeepSoPostBackgrounds::MODULE_ID) {
                            $post_meta = get_post_meta($activity->ID, 'peepso_post_background', TRUE);
                            if ($post_meta) {
                                $post_meta = json_decode($post_meta);
                                $tags['image'] = $post_meta->background;
                            }
                        }
                    }
                }

                break;
            case 'peepso_profile' :
                if ($url->get(1))
                {
                    $user = get_user_by('slug', $url->get(1));

                    if (FALSE === $user) {
                        $user = get_user_by('id', get_current_user_id());
                    }
                } else
                {
                    $user = get_user_by('id', get_current_user_id());
                }

                if ($user && is_object($user)) {
                    $user = PeepSoUser::get_instance($user->ID);

                    if ($user && $user->get_profile_accessibility() == PeepSo::ACCESS_PUBLIC)
                    {

                        $tags['title'] .= ' - ' . trim(strip_tags($user->get_fullname()));
                        $tags['image'] = $user->get_avatar();
                        $tags['url'] = $user->get_profileurl();
                    }
                }
                break;
        }

        $tags = apply_filters('peepso_filter_opengraph_tags', $tags);

        if (isset($tags) && is_array($tags))
        {
            add_filter('peepso_filter_format_opengraph', array(&$this, 'peepso_filter_format_opengraph'), 10, 2);
            $output = apply_filters('peepso_filter_format_opengraph', $tags);
            echo $output;
        }
    }

// todo: handling oEmbed when visiting peepso Page
    public function modify_oembed_links($output) {

        global $post;

        // checking og_handling
        if (is_null($post) || $post->post_type != 'page' || PeepSo::get_option('opengraph_enable') === 0)
        {
            return $output;
        }

        switch ($post->post_name) {
            case PeepSo::get_option('page_activity'):
            case PeepSo::get_option('page_profile') :
            case PeepSo::get_option('page_register') :
            case PeepSo::get_option('page_recover') :
            case PeepSo::get_option('page_reset') :
            case PeepSo::get_option('page_members') :
                if(!empty($output)) {
                    #todo adding some modified oembed provider
                    #$output = '<link rel="alternate" type="application/json+oembed" href="http://peep.so/oembed.php">';
                    $output = '';
                }
                break;
        }

        return $output;
    }

    public function remove_footer_admin()
    {
        $page = isset($_GET['page'])? $_GET['page'] : '';
        if(apply_filters('peepso_filter_admin_footer_text', substr($page, 0,6) == 'peepso') ) {
            echo '<p id="footer-left" class="alignleft">If you like <strong>PeepSo</strong> please leave us a <a href="https://wordpress.org/support/view/plugin-reviews/peepso-core?filter=5#postform" target="_blank" class="wc-rating-link" data-rated="Thanks :)">★★★★★</a> rating. Thank you in advance! And have a great time using PeepSo! || <span class="psa-social-text">Find and Follow PeepSo on Social Media:</span> <a class="psa-social-link" href="https://www.PeepSo.com/community/" target="_blank"><img src="https://cdn.peepso.com/newsletter/notifications/icon-peepso.png" alt="PeepSo Community"></a> <a class="psa-social-link" href="https://facebook.com/peepso/" target="_blank"><img src="https://cdn.peepso.com/newsletter/notifications/icon-fb.png" alt="PeepSo Facebook"></a> <a class="psa-social-link" href="https://instagram.com/peepsowp/" target="_blank"><img src="https://cdn.peepso.com/newsletter/notifications/icon-insta.png" alt="PeepSo Instagram"></a> <a class="psa-social-link" href="https://twitter.com/peepsowp/" target="_blank"><img src="https://cdn.peepso.com/newsletter/notifications/icon-twitter.png" alt="PeepSo Twitter"></a> <a class="psa-social-link" href="https://peep.so/yt" target="_blank"><img src="https://cdn.peepso.com/newsletter/notifications/icon-yt.png" alt="PeepSo YouTube"></a></p>';
        }
    }

    public function peepso_change_page_title($title, $sep=''){

        if ( !is_admin() ) {

            $post = get_post();
            $check = apply_filters('peepso_page_title_check', $post);

            if ( !is_object($check) && !is_null($check) && $check) {
                $old_title 	= $title;

                $title = $post->post_content;
                $start = strpos($title, '[peepso') + 1;

                $title=substr($title,$start);
                $stop=strpos($title,']');

                $title 		= substr($title,0,$stop);

                $title 		= apply_filters('peepso_page_title', array('title'=>$title,'newtitle'=>$title));

                if (isset($title['newtitle']) && $title['newtitle'] != '') {
                    $this->wp_title = array('old_title' => $old_title, 'title' => $title['title'], 'newtitle' => str_replace('stream', __('stream', 'peepso-core'), $title['newtitle']));

                    return $this->wp_title['newtitle'];
                }
            }
        }

        return $title;
    }

    public function the_title($title, $post_id = NULL) {

        if (in_the_loop() && !is_admin() ) {

            $post = get_post();
            $check = $post->ID === (int) $post_id ? apply_filters('peepso_page_title_check', $post) : FALSE;

            if ( !is_object($check) && !is_null($check) && $check) {
                $old_title 	= $title;

                $title = $post->post_content;
                $start = strpos($title, '[peepso') + 1;

                $title=substr($title,$start);
                $stop=strpos($title,']');

                $title 		= substr($title,0,$stop);

                if (empty($this->wp_title) || (isset($this->wp_title['newtitle']) && $this->wp_title['newtitle'] == '')) {
                    $this->wp_title 				= apply_filters('peepso_page_title', array('title'=>$title,'newtitle'=>$title));
                    $this->wp_title['old_title'] 	= $old_title;
                }

                // @Since 4.1.0.0 we no longer puth HTML inside the_title
                $title = $this->wp_title['newtitle'];
//                $title= ''
//                    . '<span id="peepso_page_title">'.$this->wp_title['newtitle'].'</span>'
//                    . '<span id="peepso_page_title_old" style="display:none">'.$old_title.'</span>';


            }
        }

        return $title;
    }

    /**
     * Displays	the original author name from a repost
     */
    public function filter_post_extras( $extras = array() )
    {
        global $post;

        $repost = isset($post->act_repost_id) ? $post->act_repost_id : FALSE;

        if ($repost) {
            ob_start();
            $PeepSoActivity = PeepSoActivity::get_instance();
            $repost = $PeepSoActivity->get_activity_post($repost);

            if (NULL !== $repost) {
                $author = PeepSoUser::get_instance($repost->post_author);

                ob_start();
                do_action('peepso_action_render_user_name_before', $author->get_id());
                $before_fullname = ob_get_clean();

                ob_start();
                do_action('peepso_action_render_user_name_after', $author->get_id());
                $after_fullname = ob_get_clean();

                printf(__('via %s', 'peepso-core'),
                    '<a href="' . $author->get_profileurl() . '">' . $before_fullname . $author->get_fullname() . $after_fullname . '</a>');
            }

            $extras[] = ob_get_clean();
        }

        return $extras;
    }

    public function filter_display_post_states($post_states, $post) {

        $sc = get_post_meta($post->ID, 'peepso_shortcode', TRUE);

        if(strlen($sc)) {
            $post_states[$sc] = $sc;
        }

        return $post_states;
    }

    /**
     * Checks if a post is a repost and sets the html
     * @param  object $current_post The post
     */
    public function post_attach_repost($current_post)
    {
        $repost = $current_post->act_repost_id;

        if ($repost) {
            global $post;
            $old_post = $post;
            // Store original loop query, calling $this->get_post() will overwrite it.
            $PeepSoActivity = new PeepSoActivity;
            // $_orig_post_query = $PeepSoActivity->post_query;
            // $_orig_post_data = $PeepSoActivity->post_data;
            $activity = $PeepSoActivity->get_activity($repost);

            // $act_post = apply_filters('peepso_activity_get_post', NULL, $activity, NULL, NULL);
            $act_post = $PeepSoActivity->activity_get_post(NULL, $activity, NULL, NULL);

            if (NULL !== $act_post) {
                // TODO: resetting the value of the global $post variable is dangerous.
                $post = $act_post;
                // Add this property so that callbacks can do necessary adjustments if it's a repost.
                $post->is_repost = TRUE;
                setup_postdata($post);

                $PeepSoActivity->post_data = get_object_vars($post);
                PeepSoTemplate::exec_template('activity', 'repost', $PeepSoActivity->post_data);
                $post = $old_post;
            } else {
//				$post = get_post($repost);
                // TODO: this will reset the global $post variable. Avoid this
//				$post = get_post($repost)
                $re_post = get_post($activity->act_external_id);
                $data = array(
                    'post_author' => (NULL !== $re_post) ? $re_post->post_author : ''
                );
                PeepSoTemplate::exec_template('activity', 'repost-private', $data);
            }

            // Reset to the original loop
            // $PeepSoActivity->post_query = $_orig_post_query;
            // $PeepSoActivity->post_data = $_orig_post_data;
            // $PeepSoActivity->comment_query = NULL;

            // TODO: if you can avoid changing this then it's not needed. Definitely not needed in both cases above so only change it in one and reset before the end of the if-block
            #$post = $_orig_post_data;
            #setup_postdata($post);
        }
    }


    /**
     * Loads the translation file for the PeepSo plugin
     */
    public static function absolute_textdomain_path() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
    }

    public function load_textdomain()
    {
        $path = str_ireplace(WP_PLUGIN_DIR, '', dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
        load_plugin_textdomain('peepso-core', FALSE, $path);
    }

    /*
     * retrieve singleton class instance
     * @return instance reference to plugin
     */
    public static function get_instance()
    {
        if (self::$_instance === NULL)
            self::$_instance = new self();
        return (self::$_instance);
    }

    /*
     * Checks for AJAX queries, sets up AJAX Handler
     */
    public function check_ajax_query()
    {
        global $wp_query;

        $sPageName = $_SERVER['REQUEST_URI'];
        $path = trim(parse_url($sPageName, PHP_URL_PATH), '/');

        $parts = explode('/', $path);
        $segment = count($parts) - 2;

        if ($segment >= 0 && 'peepsoajax' === $parts[$segment]) {

            remove_all_filters('wp_loaded');

            $page = (isset($parts[$segment + 1]) ? $parts[$segment + 1] : '');
            new PeepSoAjaxHandler($page);		// loads AJAX handling code

            header('HTTP/1.0 200 OK');			// reset HTTP result code, no longer a 404 error
            $wp_query->is_404 = FALSE;
            $wp_query->is_page = TRUE;
            $wp_query->is_admin = FALSE;
            unset($wp_query->query['error']);


            if (array_key_exists('HTTP_REFERER', $_SERVER)) {
                PeepSo3_Cookie::set('peepso_last_visited_page',$_SERVER['HTTP_REFERER'], time() + (MINUTE_IN_SECONDS * 30));
            }

            $this->is_ajax = TRUE;
            return;
        }
    }


    /*
     * Called when WP is loaded; need to signal PeepSo plugins that everything's ready
     */
    public function init_callback()
    {
        do_action('peepso_init_plugins');

        do_action('peepso_init');

        $act = new PeepSoActivityShortcode();

        add_filter('peepso_show_my_posts_list', function($filters) {

            $default_filters = [
                'show_mine' => [
                    'label' => __('Show my posts', 'peepso-core'),
                    'icon' => 'gcis gci-user-plus ps-js-icon',
                ],
                'hide_mine' => [
                    'label' => __('Hide my posts', 'peepso-core'),
                    'icon' => 'gcis gci-user-minus ps-js-icon',
                ],
            ];

            return array_merge($default_filters, $filters);
        });

        add_filter('peepso_stream_sort_list', function ($filters) {

            $default_filters = [
                'new' => [
                    'label' => __('New posts', 'peepso-core'),
                    'label_warning' => '',
                    'desc' =>   __('Most recent posts are on top', 'peepso-core'),
                    'icon' => 'gcis gci-comment ps-js-icon',
                ],
                'new_and_commented' => [
                    'label' => __('New posts & comments', 'peepso-core'),
                    'label_warning' => '',
                    'desc' =>   __('Most recently commented and added posts are on top', 'peepso-core'),
                    'icon' => 'gcis gci-comments ps-js-icon',
                ],
            ];

            if(PeepSo::get_option_new('pinned_posts_enable') && class_exists('PeepSoGroupsPlugin') && PeepSo::get_option('groups_pin_group_only', 0)) {
                $default_filters['pinned_only'] = array(
                    'label' => __('Only pinned posts', 'peepso-core'),
                    'label_warning' => '',
                    'desc' =>  __('Show only posts that are pinned', 'peepso-core'),
                    'icon'=> 'gcis gci-thumbtack ps-js-icon',
                );
            }

            return array_merge($default_filters, $filters);

        });

        if( PeepSo::get_option_new('post_save_enable') || PeepSo::can_schedule_posts() || class_exists('PeepSoGroupsPlugin') || class_exists('PeepSoFriends')) {

            add_filter('peepso_stream_id_list', function ($filters) {

                $filters['core_community'] = array(
                    'order' => 10,
                    'label' => __('Community', 'peepso-core'),
                    'label_warning' => '',
                    'desc' =>   __('Posts from the entire community', 'peepso-core'),
                    'icon'=> 'gcis gci-users ps-js-icon',
                );

                $who = [];
                $who[] = __('members', 'peepso-core');

                if (class_exists('PeepSoGroupsPlugin')) {
                    $who[] = __('groups', 'peepso-core');
                }

                $who[] = __('posts', 'peepso-core');
                $who = implode(', ', $who);

                $who = strrev(implode(strrev(' ' . __('&amp;', 'peepso-core') . ' '), explode(strrev(','), strrev($who), 2)));

                $filters['core_following'] = array(
                    'order' => 20,
                    'label' => __('Following', 'peepso-core'),
                    'label_warning' => __('followed', 'peepso-core'),
                    'desc' => sprintf(__('Posts from %s you follow', 'peepso-core'), $who),
                    'icon' => 'gcis gci-square-check ps-js-icon',
                );

                if(PeepSo::get_option_new('post_save_enable')) {
                    $filters['core_saved'] = array(
                        'order' => 30,
                        'label' => __('Saved posts', 'peepso-core'),
                        'label_warning' => __('saved', 'peepso-core'),
                        'desc' => __('Posts you added to your "Saved" list', 'peepso-core'),
                        'icon' => 'gcis gci-bookmark ps-js-icon',
                    );
                }

                if(PeepSo::can_schedule_posts()) {

                    $label =  __('My scheduled posts', 'peepso-core');
                    $desc = __('All the posts you scheduled for later', 'peepso-core');

                    if(PeepSo::is_admin()) {
                        $label = __('All scheduled posts', 'peepso-core');
                        $desc = __('All community posts scheduled for later', 'peepso-core');
                    }

                    $filters['core_scheduled'] = array(
                        'order' => 40,
                        'label' => $label,
                        'label_warning' => __('scheduled', 'peepso-core'),
                        'desc' =>   $desc,
                        'icon'=> 'gcis gci-calendar-days ps-js-icon',
                    );
                }


                return $filters;
            }, -1, 1);
            // Final sort
            add_filter('peepso_stream_id_list', function ($filters) {

                uasort($filters, function($a, $b) {
                    return $a['order'] - $b['order'];
                });
                return $filters;
            }, 99999, 1);

        }

        // add email_notif to profile
        if(PeepSo::is_admin()) {
            add_action('show_user_profile', array(&$this, 'email_notif_user_profile_fields'));
            add_action('edit_user_profile', array(&$this, 'email_notif_user_profile_fields'));
            // add_action( 'personal_options_update', array(&$this, 'save_email_notif_user_profile_fields' ));
            // add_action( 'edit_user_profile_update', array(&$this, 'save_email_notif_user_profile_fields' ));
        }

        // JS file
        add_action('admin_enqueue_scripts', function() {
            wp_register_script('peepso-admin-config-user-profile',
                PeepSo::get_asset('js/peepso-admin-config-user-profile.min.js'),
                array('jquery','peepso'), PeepSo::PLUGIN_VERSION, TRUE);

            wp_localize_script( 'peepso-admin-config-user-profile', 'peepsoconfiguserdata',
                array( 'ajax_url' => admin_url( 'admin-ajax.php' )) );

            wp_enqueue_script('peepso-admin-config-user-profile');

            wp_register_script('peepso-admin-request-data',
                PeepSo::get_asset('js/admin-request-data.min.js'),
                array('jquery', 'peepso'), self::PLUGIN_VERSION, TRUE);
            wp_enqueue_script('peepso-admin-request-data');

            wp_register_script('peepso-admin-brute-force',
                PeepSo::get_asset('js/admin-brute-force.min.js'),
                array('jquery', 'peepso'), self::PLUGIN_VERSION, TRUE);
            wp_enqueue_script('peepso-admin-brute-force');

            if (!current_user_can('install_plugins')) {
                add_action('admin_head', function() {
                    echo '<style>#peepso_php_warning,.error.peepso,.notice.peepso{display:none!important;}</style>';
                });
            }
        });



        $scheme = 'http';
        if ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) {
            $scheme = 'https';
        }
        $current_url = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $cron_direct_url = get_bloginfo('url') . '/?' . self::CRON_GDPR_EXPORT_DATA;
        $is_cron_url = ($current_url == $cron_direct_url) ? TRUE : FALSE;

        if ($is_cron_url && (PeepSo::get_option('gdpr_enable', 1))) {
            PeepSoGdpr::process_export_data();
            PeepSoGdpr::process_cleanup_data();
            die();
        }

        // blogposts

        add_action( 'wp_ajax_peepsoblogposts_user_posts', array(&$this,'blogposts_ajax_user_posts') );
        add_action( 'wp_ajax_nopriv_peepsoblogposts_user_posts', array(&$this,'blogposts_ajax_user_posts') );

        // "Blog Posts" profile section
        if(PeepSo::get_option('blogposts_profile_enable', 0)) {
            // Profile segment renderer
            add_action('peepso_profile_segment_blogposts', array(&$this, 'blogposts_peepso_profile_segment_blogposts'));

            // @todo what is this
            add_filter('peepso_rewrite_profile_pages', array(&$this, 'peepso_rewrite_profile_pages'));

            // limit privacy settings
            if(!is_admin()) {
                add_filter('peepso_privacy_access_levels', array(&$this, 'blogposts_filter_privacy_access_levels'), 100);
            }

        }

        if(PeepSo::get_option_new('blogposts_authorbox_enable') && PeepSo::get_option_new('blogposts_profile_enable')) {
            add_filter('the_content', array($this, 'blogposts_filter_the_content_blogpost_authorbox'));
        }

        // attach comments and likes to WP posts


        if(PeepSo::get_option('blogposts_comments_enable', FALSE)) {

            add_filter('comments_template', function ($theme_template) {
                wp_reset_query();
                global $wp_query, $post;

                if (!in_array($post->post_type, array('post'))) {
                    return $theme_template;
                }

                if(!PeepSoBlogPosts::enabled_for_post_categories($post->ID)) {
                    return $theme_template;
                }

                $wp_query->comments = array();
                $wp_query->comment_count = 0;

                add_filter('comments_array', function () {
                    return array();
                });
                add_filter('pings_open', function () {
                    return FALSE;
                });
                return $theme_template;
            });

            add_filter('comments_open', function ($open, $post_id) {
                global $post;
                if (!in_array($post->post_type, array('post'))) {
                    return $open;
                }

                if(!PeepSoBlogPosts::enabled_for_post_categories($post_id)) {
                    return $open;
                }

                // if called from comment_form() function
                foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $caller) {
                    if ($caller['function'] == 'comment_form') {
                        return FALSE;
                    }
                }

                return TRUE;
            }, 10, 2);

            add_action('comment_form_comments_closed',array($this, 'blogposts_filter_the_content_blogpost'));

            add_filter('get_comments_number', function($count, $post_id) {

                $post = get_post($post_id);

                // This step REQUIRES an existing activity entry
                if(FALSE !== $this->blogposts_publish_post($post->ID, $post)) {
                    $count = $this->blogposts_comment_count($post_id);
                }

                return $count;
            },1,2);
        }

        if(PeepSo::get_option('blogposts_profile_enable', 0)) {
            // Profile segment menu item
            add_filter('peepso_navigation_profile', array(&$this, 'blogposts_filter_peepso_navigation_profile'),-99);
        }

        // Make sure DB is cleaned up when our Activity Stream item is deleted
        add_filter('peepso_delete_content', function($id) {
            global $wpdb;
            $wpdb->delete($wpdb->prefix.'postmeta', array('meta_value'=>$id, 'meta_key'=>self::BLOGPOSTS_SHORTCODE));
            $wpdb->delete($wpdb->prefix.PeepSoActivity::TABLE_NAME, array('act_external_id'=>$id, 'act_module_id'=>self::BLOGPOSTS_MODULE_ID));
        });

        // Post publish action
        add_action( 'publish_post', array(&$this, 'blogposts_publish_post'), 1, 2 );
        add_action( 'future_post', array(&$this, 'blogposts_publish_post'), 1, 2 );
        add_action( 'future_to_publish', array(&$this, 'blogposts_publish_scheduled_post'));
        add_action( 'wp_trash_post', function($post_id) {
            $act_id = get_post_meta($post_id, self::BLOGPOSTS_SHORTCODE, TRUE);

            if(strlen($act_id) && is_numeric($act_id) && 0 < $act_id)
            {
                wp_update_post(array(
                    'ID' => $act_id,
                    'post_status' => 'pending'
                ));
            }
        });

        add_action( 'untrash_post', function($post_id) {
            $act_id = get_post_meta($post_id, self::BLOGPOSTS_SHORTCODE, TRUE);

            if(strlen($act_id) && is_numeric($act_id) && 0 < $act_id)
            {
                wp_update_post(array(
                    'ID' => $act_id,
                    'post_status' => 'publish'
                ));
            }
        });

        add_action( 'before_delete_post', function($post_id) {
            $act_id = get_post_meta($post_id, self::BLOGPOSTS_SHORTCODE, TRUE);

            if(strlen($act_id) && is_numeric($act_id) && 0 < $act_id)
            {
                $act = new PeepSoActivity();
                $act->delete_post($act_id);
            }
        });

        // Author notification schedulled post
        add_action( 'future_to_publish', array(&$this, 'future_to_publish_notify_author'));


        // Activity Stream item - User X "wrote a Z" text
        add_filter('peepso_activity_stream_action', array(&$this, 'blogposts_activity_stream_action'), 99, 2);

        // Activity Item parser - include the embed
        add_filter('peepso_activity_content', array(&$this, 'blogposts_filter_the_content_activity'),1,2);

        add_filter('peepso_post_filters', array(&$this, 'blogposts_post_filters'), 99,1);

        // reactions
        if (apply_filters('peepso_permissions_reactions_create', TRUE)) {
            add_action('peepso_activity_post_actions', array(&$this, 'reactions_post_actions'), 1);
        }
        add_filter('peepso_post_inside_actions', array(&$this, 'reactions_before_comments'));
        //add_filter('peepso_post_before_comments', array(&$this, 'reactions_before_comments'));
        add_filter('peepso_modal_before_comments', array(&$this, 'reactions_before_comments'));

        if(PeepSo::get_option('hashtags_enable', 1)) {
            // hashtags
            add_action('peepso_activity_after_save_post', function ($post_id) {
                if ('peepso-post' == get_post_type($post_id)) {
                    delete_post_meta($post_id, PeepSo::HASHTAGS_POST_META);
                }
            }, 10, 3);

            add_action('peepso_action_render_stream_filters', function () {
                PeepSoTemplate::exec_template('hashtags', 'stream-filters');
            });

            add_filter('peepso_activity_post_clauses', function ($clauses, $user_id = NULL) {
                $PeepSoInput = new PeepSoInput();
                $hashtag = $PeepSoInput->value('search_hashtag', '', FALSE); // SQL Safe
                // exclude where clauses if searching comment
                if (!empty($hashtag) && (FALSE === strpos($clauses['where'], PeepSoActivityStream::CPT_COMMENT)) && !PeepSo::is_dev_mode('experimental_fast_queries') ) {

                    // @todo: check regex
                    $hashtag = function_exists('mb_strtolower') ? mb_strtolower($hashtag, 'UTF-8') : strtolower($hashtag);

                    $clauses['where'] .= " AND " . $this->hashtags_query_where($hashtag);
                }

                return $clauses;
            }, 1, 2);

            add_action('peepso_config_before_save-postbox', function () {

                $rebuild = FALSE;

                $hashtags_everything = isset($_POST['hashtags_everything']) ? $_POST['hashtags_everything'] : 0;
                $hashtags_must_start_with_letter = isset($_POST['hashtags_must_start_with_letter']) ? $_POST['hashtags_must_start_with_letter'] : 0;


                if ($_POST['hashtags_min_length'] != PeepSo::get_option('hashtags_min_length', 3)) {
                    $rebuild = TRUE;
                } elseif ($_POST['hashtags_max_length'] != PeepSo::get_option('hashtags_max_length', 16)) {
                    $rebuild = TRUE;
                } elseif ($hashtags_everything != PeepSo::get_option('hashtags_everything', 0)) {
                    $rebuild = TRUE;
                } elseif ($hashtags_must_start_with_letter != PeepSo::get_option('hashtags_must_start_with_letter')) {
                    $rebuild = TRUE;
                } elseif (isset($_POST['hashtags_rebuild'])) {
                    $_POST['hashtags_rebuild'] = 0;
                    $rebuild = TRUE;
                }


                if ($rebuild) {
                    $this->hashtags_build_reset();
                    $peepso_admin = PeepSoAdmin::get_instance();
                    $peepso_admin->add_notice(__('The hashtag cache has been purged and it may take a while to rebuild it. You can use your site as usual.', 'peepso-core'), 'note');
                }
            });

            add_action('peepso_config_before_save-blogposts', function () {
                if (!isset($_POST['blogposts_activity_enable']) && isset($_POST['blogposts_comments_enable'])) {
                    $_POST['blogposts_comments_enable'] = 0;
                }
            });

            if (class_exists('PeepSoMaintenanceFactory') && class_exists('PeepSoMaintenanceHashtags')) {
                new PeepSoMaintenanceHashtags();
            }
        }

        // MarkDown
        add_filter('peepso_activity_content_before', function ($content) {
            global $post;

            switch ($post->post_type) {

                case 'peepso-post':
                    if (PeepSo::get_option('md_post', 0)) {
                        $content = self::do_parsedown($content);
                    }
                    break;

                case 'peepso-comment':
                    if (PeepSo::get_option('md_comment', 0)) {
                        $content = self::do_parsedown($content);
                    }
                    break;

                case 'peepso-message':
                    if (PeepSo::get_option('md_chat', 0)) {
                        #$content = self::do_parsedown($content);
                    }
                    break;
            }
            return $content;
        });

        // Profiles

        if (is_admin()) {
            add_action('peepso_admin_profiles_list_before',array(&$this,'action_admin_profiles_list_before'));
            add_action('peepso_admin_dashboard_demographic_data',array(&$this,'filter_admin_dashboard_demographic_data'));
        } else {
            add_action('peepso_action_render_member_search_fields', array(&$this, 'action_render_member_search_fields'));
            add_filter('peepso_member_search_args', array(&$this, 'filter_member_search_args'), 10, 2);
        }

        add_shortcode('peepso_user_field', function ($a) {
            $a = shortcode_atts(array(
                'user' => get_current_user_id(),
                'field' => 'firstname',
                'height' => '',
                'width' => '',
            ), $a);

            $PeepSoUser = PeepSoUser::get_instance($a['user']);

            if ('avatar' == $a['field']) {
                $height = esc_attr($a['height']);
                $width = esc_attr($a['width']);
                return "<img src=\"{$PeepSoUser->get_avatar('orig')}\" height=\"{$height}\" width=\"{$width}\">";
            }

            if ('avatar_full' == $a['field']) {
                $height = esc_attr($a['height']);
                $width = esc_attr($a['width']);
                return "<img src=\"{$PeepSoUser->get_avatar('full')}\" height=\"{$height}\" width=\"{$width}\">";
            }


            $PeepSoField = PeepSoField::get_field_by_id($a['field'], $PeepSoUser->get_id());

            if (!$PeepSoUser || !$PeepSoField) {
                return '';
            }

            return $PeepSoField->render(FALSE);
        });

        // Extend PeepSoField keys, as_int and as_array with extra values
        add_filter('peepso_user_field_meta_keys', 			array(&$this, 'filter_user_field_meta_keys'));
        add_filter('peepso_user_field_meta_keys_as_int',	array(&$this, 'filter_user_field_meta_keys_as_int'));
        add_filter('peepso_user_field_meta_keys_as_array',	array(&$this, 'filter_user_field_meta_keys_as_array'));

        # ACTIONS - ADMIN AJAX
        # These must fire outside the is_admin() context, otherwise will not work inside AJAX calls
        add_action('update_postmeta', function($meta_type, $post_id, $meta_key, $meta_value) {
            if('searchable' == $meta_key && 1==$meta_value) {
                $post = get_post($post_id);

                if('peepso_user_field' == $post->post_type) {
                    global $wpdb;
                    $wpdb->update($wpdb->usermeta, array('meta_value'=>PeepSo::ACCESS_MEMBERS), array('meta_key'=> 'peepso_user_field_'.$post_id.'_acc'));
                }
            }

        }, 10, 4);

        ## ALL CLASSES - Tab additions
        // Additional options after Default Privacy
        add_action('peepso_admin_profiles_field_options_default_privacy',array(&$this,'action_admin_profiles_field_options_default_privacy'),1);
        // Additional options on the bottom of Appearance Tab
        add_action('peepsofield_admin_appearance',array(&$this,'action_admin_profiles_field_tab_appearance'),1);

        ## SELECT CLASSES - Tab additions
        // Single Select - General Tab
        add_action('peepsofieldselectsingle_admin_general', array(&$this, 'action_peepsofieldselect_select_options'));
        // Single Select (Bool) - General Tab
        add_action('peepsofieldselectbool_admin_general', array(&$this, 'action_peepsofieldselect_select_options'));
        // Multi Select - General Tab
        add_action('peepsofieldselectmulti_admin_general', array(&$this, 'action_peepsofieldselect_select_options'));
        // URL - Appearance Tab
        add_action('peepsofieldtexturl_admin_appearance', array(&$this, 'action_peepsofieldtexturl_nofollow'));
        add_action('peepsofieldtexturlpreset_admin_appearance', array(&$this, 'action_peepsofieldtexturl_nofollow'),20,1);
        // Country - Appearance Tab
        add_action('peepsofieldcountry_admin_appearance', array(&$this, 'action_peepsofieldcountry_countries_top'));
        // ?
        add_action('peepso_admin_profiles_field_title_after',array(&$this,'action_admin_profiles_field_title_after'));

        ## All CLASSES - Container additions
        // Additional field options on the bottom of the box (eg Delete)
        add_action('peepso_admin_profiles_field_options',array(&$this,'action_admin_profiles_field_options'));


        # FILTERS - GLOBAL

        ## Query modifiers
        // modify limit
        add_filter('peepso_profile_fields_query_limit', array(&$this, 'filter_profile_fields_query_limit'));


        // adding option to profiles fields
        add_action('peepsofieldtext_admin_general',	array(&$this, 'add_fieldtext_admin_general_option'), 10, 1);
        add_action('peepsofieldtextphonenumber_admin_general',	array(&$this, 'add_fieldtext_admin_general_option'), 10, 1);
        add_action('peepsofieldtextdate_admin_general',	array(&$this, 'add_fieldtext_admin_general_option'), 10, 1);
        add_action('peepsofieldtextemail_admin_general',	array(&$this, 'add_fieldtext_admin_general_option'), 10, 1);
        add_action('peepsofieldtexturl_admin_general',	array(&$this, 'add_fieldtext_admin_general_option'), 10, 1);
        add_action('peepsofieldtexturlpreset_admin_general',	array(&$this, 'add_fieldtext_admin_general_option'), 10, 1);
        add_action('peepsofieldtexturlpreset_admin_appearance',	array(&$this, 'add_fieldtexturlpreset_admin_general_option'), 10, 1);
        add_action('peepsofieldselectsingle_admin_general',	array(&$this, 'add_fieldtext_admin_general_option'), 10, 1);
        add_action('peepsofieldselectbool_admin_general',	array(&$this, 'add_fieldtext_admin_general_option'), 10, 1);
        add_action('peepsofieldselectmulti_admin_general',	array(&$this, 'add_fieldtext_admin_general_option'), 10, 1);
        add_action('peepsofieldseparator_admin_general',	array(&$this, 'add_fieldtext_admin_general_option'), 10, 1);
        add_action('peepsofieldlocation_admin_general',	array(&$this, 'add_fieldtext_admin_general_option'), 10, 1);
        add_action('peepsofieldcountry_admin_general',	array(&$this, 'add_fieldtext_admin_general_option'), 10, 1);

        // adding option to profiles fields
        add_action('peepsofieldtext_admin_privacy',	array(&$this, 'add_fieldtext_admin_privacy_option'), 10, 1);
        add_action('peepsofieldtextdate_admin_privacy',	array(&$this, 'add_fieldtext_admin_privacy_option'), 10, 1);
        add_action('peepsofieldtextphonenumber_admin_privacy',	array(&$this, 'add_fieldtext_admin_privacy_option'), 10, 1);
        add_action('peepsofieldtextemail_admin_privacy',	array(&$this, 'add_fieldtext_admin_privacy_option'), 10, 1);
        add_action('peepsofieldtexturl_admin_privacy',	array(&$this, 'add_fieldtext_admin_privacy_option'), 10, 1);
        add_action('peepsofieldtexturlpreset_admin_privacy',	array(&$this, 'add_fieldtext_admin_privacy_option'), 10, 1);
        add_action('peepsofieldselectsingle_admin_privacy',	array(&$this, 'add_fieldtext_admin_privacy_option'), 10, 1);
        add_action('peepsofieldselectbool_admin_privacy',	array(&$this, 'add_fieldtext_admin_privacy_option'), 10, 1);
        add_action('peepsofieldselectmulti_admin_privacy',	array(&$this, 'add_fieldtext_admin_privacy_option'), 10, 1);
        add_action('peepsofieldseparator_admin_privacy',	array(&$this, 'add_fieldtext_admin_privacy_option'), 10, 1);
        add_action('peepsofieldlocation_admin_privacy',	array(&$this, 'add_fieldtext_admin_privacy_option'), 10, 1);

        // add fields to register form
        add_filter('peepso_register_form_fields', array(&$this, 'register_form_fields'), 10, 1);
        add_action('peepso_register_extended_fields', array(&$this, 'register_extended_fields'), 10);

        add_filter('peepso_register_valid_extended_fields', array(&$this, 'valid_extended_fields'), 10, 2);
        add_action('peepso_register_new_user', array(&$this,'register_new_user'));

        // use PeepSo name everywhere
        if (PeepSo::get_option('use_name_everywhere', 0)) {
            if (get_current_user_id()) {
                global $current_user;

                $peepso_user = PeepSoUser::get_instance(get_current_user_id());
                $current_user->data->display_name = $peepso_user->get_fullname();
            }

            add_filter('the_author', function($name) {
                global $authordata;

                if (is_object($authordata)) {
                    $peepso_user = PeepSoUser::get_instance($authordata->ID);
                    if (is_object($peepso_user)) {
                        return $peepso_user->get_fullname();
                    }
                }

                return $name;
            });
        }
    }


    /*
     * Initialize all PeepSo widgets
     */
    public function widgets_init()
    {
        $this->_widgets = apply_filters('peepso_widgets', $this->_widgets);

        if(PeepSo::is_dev_mode('new_search')) {
            $this->_widgets[]='PeepSoWidgetSearch';
        }

        if(0 == PeepSo::get_option('hashtags_enable', 1)) {

            if (($key = array_search('PeepSoWidgetHashtags', $this->_widgets)) !== false) {
                unset($this->_widgets[$key]);
            }
        }

        if (count($this->_widgets)) {
            foreach ($this->_widgets as $widget_name) {
                register_widget($widget_name);
            }
        }
    }

    /*
     * Load widget instances for a given position
     */
    public function get_widgets_in_position($profile_position){

        $widgets = wp_get_sidebars_widgets();

        $result_widgets = array();

        foreach($widgets as $position => $list) {

            // SKIP if the position name does not start with peepso
            if ('peepso' != substr($position,0,6)){
                continue;
            }

            // SKIP if the position is empty
            if (!count($list)) {
                continue;
            }

            $widget_instances = array();

            // loop through widgets in a position
            foreach($list as $widget) {

                // SKIP if the widget name does not contain "peepsowidget"
                if (!stristr($widget, 'peepsowidget')) {
                    continue;
                }

                // remove "peepsowidget"
                $widget = str_ireplace('peepsowidget', '', $widget);

                // extract last part of class name and id of the instance
                // eg "videos-1" becomes "videos" and "1"
                $widget = explode('-', $widget);

                $widget_class = 'PeepSoWidget'.ucfirst($widget[0]);
                $widget_instance_id = $widget[1];

                // to avoid creating multiple instances  use the local aray to store repeated widgets
                if (!array_key_exists($widget_class, $widget_instances) && class_exists($widget_class)) {
                    $widget_instance = new $widget_class;
                    $widget_instances[$widget_class] = $widget_instance->get_settings();
                }

                // load the instance we are interested in (eg PeepSoVideos 1)
                if (array_key_exists($widget_class, $widget_instances)){
                    $current_instance = $widget_instances[$widget_class][$widget_instance_id];
                } else {
                    continue;
                }
                // SKIP if the instance isn't in a valid position
                if (!isset($current_instance['position']) || $current_instance['position'] != $profile_position) {
                    continue;
                }

                $current_instance['widget_class'] = $widget_class;

                // add to result array
                $result_widgets[]=$current_instance;
            }
        }

        return $result_widgets;
    }

    /**
     * Returns HTML used to render options for PeepSo Widgets (including profile widgets)
     * @TODO parameters (optional/additional fields) when needed
     * @TODO text domain
     * @param $widget
     * @return array
     */
    public function get_widget_form($widget)
    {
        $widget['html'] = $widget['html'] . PeepSoTemplate::exec_template('widgets', 'admin_form', $widget, true);
        return $widget;
    }

    public function get_widget_positions($positions)
    {
        return array_merge($positions, array('profile_sidebar_top', 'profile_sidebar_bottom'));
    }

    /**
     * Returns HTML used to render reCaptcha
     */
    public function login_form()
    {
        $field = '<div class="peepso_captcha_field_div"></div>';

        echo $field;
    }

    public function login_footer()
    {
        ?>
        <script type="text/javascript">
            var peepso_onloadCallback = function() {
                for ( var i = 0; i < document.forms.length; i++ ) {
                    var form = document.forms[i];
                    var captcha_div = form.querySelector( '.peepso_captcha_field_div' );

                    if ( null === captcha_div )
                        continue;
                    captcha_div.innerHTML = '';
                    ( function( form ) {
                        var peepso_captcha = grecaptcha.render( captcha_div,{
                            'sitekey' : '<?php echo esc_js( trim( PeepSo::get_option_new( 'site_registration_recaptcha_sitekey' ) ) ); ?>',
                            'size'  : 'invisible',
                            'theme' : 'light',
                            'badge' : 'bottomright',
                            'callback' : function ( token ) {
                                HTMLFormElement.prototype.submit.call( form );
                            },
                            'expired-callback' : function(){
                                grecaptcha.reset( peepso_captcha );
                            }
                        });

                        form.onsubmit = function( e ){
                            e.preventDefault();
                            grecaptcha.execute( peepso_captcha );
                        };
                    })(form);
                }
            };
        </script>
        <?php
        $host = PeepSo3_ReCaptcha::url();
        $google_url = $host . '/recaptcha/api.js?onload=peepso_onloadCallback&render=explicit';
        ?>
        <script src="<?php echo esc_url( $google_url ); ?>"
                async defer>
        </script>
        <?php
    }

    public function login_recaptcha_validation($user, $username, $password)
    {
        if(!$username) {
            return $user;
        }

        // not wp-login page
        if(!function_exists('login_header')) {
            return $user;
        }

        // skip if action from Social Login
        if (class_exists('TwistPress_Social_Login') || class_exists('PeepSo_Social_Login') || class_exists('PeepSoSocialLoginPlugin')) {
            $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
            $code = isset($_REQUEST['code']) ? sanitize_text_field($_REQUEST['code']) : '';
            $provider = isset($_REQUEST['provider']) ? sanitize_text_field($_REQUEST['provider']) : 'Instagram';

            if (!((!in_array($action, ['social_login_authenticate', 'social_login_account_create', 'social_login_account_linking', 'social_login_authenticated']) && !isset($code)) || (isset($code) && empty($action) && $provider != 'Instagram'))) {
                return $user;
            }
        }

        $input = new PeepSoInput();
        $recaptcha_response = $input->value('g-recaptcha-response','', FALSE); //SQL safe

        $args = array(
            'body' => array(
                'response' => $recaptcha_response,
                'secret' => PeepSo::get_option_new('site_registration_recaptcha_secretkey', 0),
            )
        );

        $host = PeepSo3_ReCaptcha::url();
        $request = wp_remote_post($host . '/recaptcha/api/siteverify', $args);
        $response_json = json_decode(wp_remote_retrieve_body($request), true);
        if (isset($response_json['success']) && $response_json['success'] !== TRUE) {
            return (new WP_Error('login_error', __('ReCaptcha security check failed.' , 'peepso-core')));
        }

        return ($user);
    }

    /*
     * checks current URL to see if it's one of the PeepSo specific pages
     * If it is, loads the appropriate shortcode early so it can set up it's hooks
     */
    public function check_query()
    {
        if ($this->is_ajax) {
            status_header(200);
            return;
        }

        if(isset($_GET['peepso_process_mailqueue'])) {
            PeepSoMailQueue::process_mailqueue();
            die();
        }

        if(isset($_GET['peepso_process_maintenance'])) {
            PeepSoCron::initialize();
            do_action(PeepSo::CRON_MAINTENANCE_EVENT);
            die();
        }

        if(isset($_GET['peepso_delete_transients'])) {
            new PeepSoError('peepso_delete_transients is deprecated, use the Mayfly interface instead');
            PeepSo3_Mayfly::clr(TRUE);
            die('Mayfly Records Deleted. This function is deprecated.');
        }

        // check if a logout is requested
        if (isset($_GET['logout'])) {

            PeepSo3_Cookie::set('peepso_last_visited_page', '', time() - 3600);


            wp_logout();
            if (isset($_SESSION['peepso_user_id_after_register'])) {
                unset($_SESSION['peepso_user_id_after_register']);
            }
            PeepSo::redirect(PeepSo::get_page('logout_redirect'));
        } else {

            $current_page = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . trim($_SERVER['HTTP_HOST'],'/') ."/". $_SERVER['REQUEST_URI'];
            $cookie = '';

            if (isset($_GET['community_activate'])) {
                $cookie = 'community_activate';
            } else if (isset($_GET['peepso_activate'])) {
                $cookie = 'peepso_activate';
            } else if (array_key_exists('HTTP_REFERER', $_SERVER)) {
                $cookie = $_SERVER['HTTP_REFERER'];
            }

            if ($cookie) {
                PeepSo3_Cookie::set('peepso_last_visited_page', $cookie, time() + (MINUTE_IN_SECONDS * 30));
            }
            // Track first known visited page to redirect after registration
            @session_start();
            if(!get_current_user_id() && (!isset($_SESSION['peepso_first_visit']) || !strlen($_SESSION['peepso_first_visit']))) {
                $_SESSION['peepso_first_visit'] = $current_page;
            }
        }

        $url = PeepSoUrlSegments::get_instance();

        // If permalinks are turned on use the post name instead. For example 'register':
        // TODO: this is probably no longer needed
        $pl = get_option('permalink_structure');
        if (!empty($pl)) {
            global $post;
            if (NULL !== $post)
                $page = $post->post_name;
        }

        $sc = NULL;
        $is_peepso_page = TRUE;

        switch ($url->get(0))
        {
            case 'peepso_profile':				// PeepSo::get_option('page_profile'):
                $sc = PeepSoProfileShortcode::get_instance();
                break;

            case 'peepso_recover':				// PeepSo::get_option('page_recover'):
                PeepSoRecoverPasswordShortcode::get_instance();
                break;

            case 'peepso_reset':				// PeepSo::get_option('page_resetpassword'):
                PeepSoResetPasswordShortcode::get_instance();
                break;

            case 'peepso_register':             // PeepSo::get_option('page_register'):
                $disable_registration = intval(PeepSo::get_option('site_registration_disabled', 0));
                if (0 === $disable_registration) {
                    $this->sc = PeepSoRegisterShortcode::get_instance();
                } else {
                    PeepSo::redirect(PeepSo::get_page('activity'));
                }
                break;

            case 'peepso_activity':
                $sc = PeepSoActivityShortcode::get_instance();
                break;

            default:
                $is_peepso_page = FALSE;
                $sc = apply_filters('peepso_check_query', NULL, $url->get(0), $url);
                break;
        }

        if (NULL !== $sc) {

            status_header(200);

            if ($user_id = get_current_user_id()) {

                $user = PeepSoUser::get_instance($user_id);

                if ('ban' == $user->get_user_role()) {
                    $ban_date = get_user_meta( $user_id, 'peepso_ban_user_date', true );
                    if(empty($ban_date)) {
                        wp_logout();
                        echo "<script type=text/javascript>"
                            ." alert('" . __('Your account has been suspended indefinitely', 'peepso-core') . "');"
                            . "window.location.replace('" . PeepSo::get_page('activity') . "');"
                            . "</script>";
                        die();
                    } else {
                        #$current_time = strtotime(current_time('Y-m-d H:i:s',1));
                        $current_time = time();

                        $suspense_expired = intval($ban_date) - $current_time;
                        if($suspense_expired > 0)
                        {
                            wp_logout();
                            echo "<script type=text/javascript>"
                                ." alert('" . sprintf(__('Your account has been suspended until %s.', 'peepso-core') , date_i18n(get_option('date_format'), $ban_date)) ."');"
                                . "window.location.replace('" . PeepSo::get_page('activity') . "');"
                                . "</script>";
                            die();
                        } else {
                            // unset ban_date
                            // set user role to member
                            $user->set_user_role('member');
                            delete_user_meta($user_id, 'peepso_ban_user_date');
                        }
                    }
                }

                if( !$sc instanceof PeepSoProfileShortcode || !stristr($_SERVER['REQUEST_URI'] ,'/about') ) {
                    do_action('peepso_profile_completeness_redirect');
                }
            }

            add_filter( 'the_title', ARRAY(&$this,'the_title'), 10, 2 );
            $sc->set_page($url);
        }elseif($is_peepso_page) {
            status_header(200);
        }
    }


    /*
     * Checks the user role and redirects non-admin requests back to the front of the site
     */
    public function check_admin_access()
    {
        return;
        $role = self::_get_role();
        if ('admin' !== $role) {
            PeepSo::redirect(get_home_url());
        }

        // if it's a "peepso_" user, redirect to the front page
//		$sRole = self::get_user_role();
//		if (substr($sRole, 0, 7) == 'peepso_') {
//			PeepSo::redirect(get_home_url());
//			die;
//		}
    }


    /*
     * autoloading callback function
     * @param string $class name of class to autoload
     * @return TRUE to continue; otherwise FALSE
     */
    public function autoload($class)
    {
        // setup the class name
        $classname = $class = strtolower($class);

        // #5593 don't perform filesystem checks if the class in question is not one of ours
        if (strpos($class, 'peepso') === FALSE) {
            return;
        }

        if ('peepso' === substr($class, 0, 6)) {
            $classname = substr($class, 6);        // remove 'peepso' prefix on class file name
        }

        // check each path
        $continue = TRUE;
        foreach (self::$_autoload_paths as $path) {
            $classfile = $path . $classname . '.php';
            if (file_exists($classfile)) {
                require_once($classfile);
                $continue = FALSE;
                break;
            }
        }
        return ($continue);
    }


    /*
     * Adds a directory to the list of autoload directories. Can be used by add-ons
     * to include additional directories to look for class files in.
     * @param string $dirname the directory name to be added
     */
    public static function add_autoload_directory($dirname)
    {
        if (substr($dirname, -1) != DIRECTORY_SEPARATOR) {
            $dirname .= DIRECTORY_SEPARATOR;
        }

        ob_start();
        $dirs = array_diff(scandir($dirname), array('..', '.'));
        ob_end_clean();

        if(is_array($dirs) && count($dirs)) {
            foreach ($dirs as $dir) {
                $path = $dirname . $dir;
                if (!is_dir($path)) {
                    continue;
                }

                PeepSo::add_autoload_directory($path);
            }
        }

        self::$_autoload_paths[] = $dirname;
    }


    /*
     * called on plugin first activation
     */
    public function activate()
    {
        if ($this->can_install()) {
            require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'activate.php');
            $install = new PeepSoActivate();
            $res = $install->plugin_activation();
            if (FALSE === $res) {
                // error during installation - disable
                deactivate_plugins(plugin_basename(__FILE__));
            } else if (NULL === get_option('peepso_install_date', NULL)) {
                add_option('peepso_install_date', date('Y-m-d H:i:s'));

                if(class_exists('PeepSoConfigSettings') && function_exists('get_option')) {
                    $PeepSoConfigSettings = PeepSoConfigSettings::get_instance();
                    $PeepSoConfigSettings->set_option('site_emails_admin_email', get_option('admin_email'));
                }
            }
        }
    }

    /*
     * Method for determining if permalinks are turned on and disabling PeepSo if not
     * @return Boolean TRUE if a permalink structure is defined; otherwise FALSE
     */
    public static function has_permalinks()
    {
        if (!get_option('permalink_structure')) {
            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }

            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

            if (is_plugin_active(plugin_basename(__FILE__))) {
                add_action('admin_notices', function() {
                    $msg = sprintf(__('Cannot activate PeepSo; it requires <b>Permalinks</b> to be enabled. Go to <a href="%1$s">Settings -&gt; Permalinks</a> and select anything but the <i>Default</i> option.', 'peepso-core'),
                        get_admin_url(get_current_blog_id()) . 'options-permalink.php');
                    ?>
                    <div class="error" id="peepso_php_warning">
                        <?php echo $msg;?><br/>
                    </div>
                    <?php
                });

                deactivate_plugins(plugin_basename(__FILE__));
            }

            return (FALSE);
        }
        return (TRUE);
    }


    /**
     * Checks whether PeepSo can be installed on the current hosting and Wordpress setup.
     * Checks if permalinks are enabled.
     *
     * @return boolean TRUE|FALSE if install is possible.
     */
    public static function can_install()
    {
        return (self::has_permalinks() && self::php());
    }

    /*
     * called on plugin deactivation
     */
    public function deactivate()
    {
        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'deactivate.php');
        PeepSoUninstall::plugin_deactivation();
    }

    /*
     * enqueue scripts needed
     */
    public function enqueue_scripts()
    {
        $logged_in = is_user_logged_in();

        // template(-color)(-rtl)(-rounded).css
        $template = array(
            'template',
        );

        // color scheme
        if( !is_string(apply_filters('peepso_theme_override', false)) && strlen($color = PeepSo::get_option('site_css_template',''))) {
            // Since PeepSo 3 we only have light (empty string) and dark (dark), no other color schemes
            if('dark'==$color) {
                $template[] = $color;
            }
        }

        $template = implode('-', $template) . '.css';

        // jQuery UI style
        wp_register_style('peepso-jquery-ui', PeepSo::get_asset('css/jquery-ui.min.css'), NULL,
            '1.11.4', 'all');

        wp_register_style('peepso', PeepSo::get_template_asset(NULL, 'css/'.$template),
            NULL, PeepSo::PLUGIN_VERSION, 'all');
        wp_enqueue_style('peepso');

        // New PeepSo UI (since 2.8.0.0)
        wp_register_style('peepso-icons-new', PeepSo::get_asset('css/icons.css'),
            NULL, PeepSo::PLUGIN_VERSION, 'all');
        wp_enqueue_style('peepso-icons-new');

        if(is_rtl()) {
            if ( ! class_exists( 'Gecko_Theme_Settings' ) ) {
                wp_register_style('peepso-new', PeepSo::get_asset('css/frontend-comp-rtl.css'),
                    NULL, PeepSo::PLUGIN_VERSION, 'all');
            } else {
                wp_register_style('peepso-new', PeepSo::get_asset('css/frontend-rtl.css'),
                    NULL, PeepSo::PLUGIN_VERSION, 'all');
            }

            wp_register_style('peepso-backend', PeepSo::get_asset('css/backend-rtl.css'),
                NULL, PeepSo::PLUGIN_VERSION, 'all');
        } else {
            if ( ! class_exists( 'Gecko_Theme_Settings' ) ) {
                wp_register_style('peepso-new', PeepSo::get_asset('css/frontend-comp.css'),
                    NULL, PeepSo::PLUGIN_VERSION, 'all');
            } else {
                wp_register_style('peepso-new', PeepSo::get_asset('css/frontend.css'),
                    NULL, PeepSo::PLUGIN_VERSION, 'all');
            }

            wp_register_style('peepso-backend', PeepSo::get_asset('css/backend.css'),
                NULL, PeepSo::PLUGIN_VERSION, 'all');
        }
        wp_enqueue_style('peepso-backend');
        wp_enqueue_style('peepso-new');

        wp_add_inline_style('peepso-new', '
            @media screen and (min-width: 980px) {
                .ps-navbar__menu-item {
                    position: relative;
                }
            }
            html[dir=rtl] .ps-notif__box {
                left: 0;
                right: auto;
            }
        ');

        // Divi compatibility
        if(1==PeepSo::get_option('compatibility_divi', 0)) {

            add_action( 'wp_print_styles', function() {
                wp_dequeue_style( 'et-builder-modules-style' );
                wp_deregister_style( 'et-builder-modules-style' );
            });

            wp_register_style('peepso-divi-compat', PeepSo::get_asset('css/divi-frontend-builder-plugin-style.css'), NULL, PeepSo::PLUGIN_VERSION);
            wp_enqueue_style('peepso-divi-compat');
        }

        // core peepso libraries
        wp_register_script('peepso-core', PeepSo::get_asset('js/core.min.js'), array('jquery', 'underscore'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso-observer', FALSE, array('peepso-core'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso-npm', FALSE, array('peepso-core'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso-util', FALSE, array('peepso-core'), PeepSo::PLUGIN_VERSION, TRUE);

        // popup window
        wp_register_script('peepso-window', FALSE, array('peepso-core'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_localize_script('peepso-window', 'peepsowindowdata', array(
            'label_confirm' => __('Confirm', 'peepso-core'),
            'label_confirm_delete' => __('Confirm Delete', 'peepso-core'),
            'label_confirm_delete_content' => __('Are you sure you want to delete this?', 'peepso-core'),
            'label_yes' => __('Yes', 'peepso-core'),
            'label_no' => __('No', 'peepso-core'),
            'label_delete' => __('Delete', 'peepso-core'),
            'label_cancel' => __('Cancel', 'peepso-core'),
            'label_okay' => __('Okay', 'peepso-core'),
        ));

        wp_register_script('peepso-modules', PeepSo::get_asset('js/modules.min.js'), array('peepso-core'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso-elements', PeepSo::get_asset('js/elements.min.js'), array('peepso-core'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso-sections', PeepSo::get_asset('js/sections.min.js'), array('peepso-core'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso', FALSE, array('peepso-core', 'peepso-modules', 'peepso-elements', 'peepso-sections'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_enqueue_script('peepso');

        wp_register_script('peepso-page-autoload', PeepSo::get_asset('js/page-autoload.min.js'), array('jquery', 'underscore', 'peepso'), PeepSo::PLUGIN_VERSION, TRUE);

        // file uploader library
        $avatar = PeepSo::get_option_new('registration_avatars_enable');
        wp_register_style('peepso-fileupload',
            ($logged_in || $avatar) ? PeepSo::get_asset('css/jquery.fileupload.min.css') : FALSE,
            NULL, PeepSo::PLUGIN_VERSION, 'all');

        wp_register_script('peepso-fileupload',
            ($logged_in || $avatar) ? PeepSo::get_asset('js/fileupload.min.js') : FALSE,
            array('jquery', 'jquery-ui-widget'), PeepSo::PLUGIN_VERSION, TRUE);

        // avatar
        wp_register_script('peepso-avatar', PeepSo::get_asset('js/avatar.min.js'), array('peepso', 'peepso-fileupload'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso-hammer', PeepSo::get_asset('js/hammer.min.js'), array('jquery'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso-crop', PeepSo::get_asset('js/crop.min.js'),
            ($logged_in || $avatar) ? array('peepso', 'peepso-hammer') : array('peepso'),
            PeepSo::PLUGIN_VERSION, TRUE);

        wp_register_script('peepso-avatar-dialog',
            ($logged_in || $avatar) ? PeepSo::get_asset('js/avatar-dialog.min.js') : FALSE,
            ($logged_in || $avatar) ? array('peepso', 'peepso-avatar', 'peepso-crop') : NULL,
            PeepSo::PLUGIN_VERSION, TRUE);

        // Datepicker.
        wp_register_style('peepso-datepicker', PeepSo::get_asset('css/datepicker.css'), array('peepso-jquery-ui'), PeepSo::PLUGIN_VERSION, 'all');
        wp_register_script('peepso-datepicker-noconflict', PeepSo::get_asset('js/datepicker-noconflict.js'), array(), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso-datepicker', PeepSo::get_asset('js/datepicker.min.js'),
            array('peepso-datepicker-noconflict', 'jquery-ui-datepicker'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_localize_script('peepso-datepicker', 'peepsodatepickerdata', array(
            'config' => ps_datepicker_config()
        ));

        // Bundled peepso scripts.
        wp_enqueue_script('peepso-bundle', PeepSo::get_asset('js/bundle.min.js'),
            array('jquery-ui-position', 'peepso', 'peepso-window', 'peepso-avatar-dialog'), PeepSo::PLUGIN_VERSION, TRUE);

        add_filter('peepso_data', function( $data ) {
            $data['datetime'] = array(
                'text' => array(
                    'am' => date_i18n('A', strtotime('2016-01-01 06:00:00')),
                    'pm' => date_i18n('A', strtotime('2016-01-01 18:00:00')),
                    'monthNames' => array(
                        date_i18n('F', strtotime('2016-01-01')),
                        date_i18n('F', strtotime('2016-02-01')),
                        date_i18n('F', strtotime('2016-03-01')),
                        date_i18n('F', strtotime('2016-04-01')),
                        date_i18n('F', strtotime('2016-05-01')),
                        date_i18n('F', strtotime('2016-06-01')),
                        date_i18n('F', strtotime('2016-07-01')),
                        date_i18n('F', strtotime('2016-08-01')),
                        date_i18n('F', strtotime('2016-09-01')),
                        date_i18n('F', strtotime('2016-10-01')),
                        date_i18n('F', strtotime('2016-11-01')),
                        date_i18n('F', strtotime('2016-12-01')),
                    ),
                )
            );
            return $data;
        }, 10, 1 );

        // Lightbox.
        wp_register_script('peepso-lightbox', FALSE, array('peepso-bundle'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_localize_script('peepso-bundle', 'peepsolightboxdata', array(
            'template' => PeepSoTemplate::exec_template('general', 'lightbox', NULL, TRUE)
        ));

        // member script
        wp_register_script('peepso-member', PeepSo::get_asset('js/member.min.js'), array('jquery', 'peepso-observer'), PeepSo::PLUGIN_VERSION, TRUE);
        $ban_start_date = date_i18n('Y-m-d', strtotime('+1 day'));
        $ban_start_date_str = date_i18n(get_option('date_format'), strtotime('+1 day'));
        wp_localize_script('peepso-member', 'peepsomemberdata', array(
            'ban_popup_title' => __('Ban this user', 'peepso-core'),
            'ban_popup_content' => PeepSoTemplate::exec_template('profile', 'dialog-ban', array('start_date' => $ban_start_date, 'start_date_str' => $ban_start_date_str), TRUE),
            'ban_popup_save' => __('Ban this user', 'peepso-core'),
            'ban_popup_cancel' => __('Cancel', 'peepso-core'),
        ));
        wp_enqueue_script('peepso-member');

        wp_register_script('peepso-form', PeepSo::get_asset('js/form.min.js'), array('jquery'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('image-scale', PeepSo::get_asset('js/image-scale.min.js'), array('jquery'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso-modal-comments', PeepSo::get_asset('js/modal-comments.min.js'), array('underscore', 'peepso-observer', 'peepso-activity', 'image-scale', 'peepso-lightbox', 'peepso'), PeepSo::PLUGIN_VERSION, TRUE);

        // TODO: remove this and all codes that enqueue this script
        wp_register_script('peepso-load-image', FALSE, array('jquery'), PeepSo::PLUGIN_VERSION, TRUE);

        // Enqueue peepso-window, a lot of functionality uses the popup dialogs
        wp_register_script('peepso-jquery-mousewheel', PeepSo::get_asset('js/jquery.mousewheel.min.js'), array('jquery'), PeepSo::PLUGIN_VERSION, TRUE);

        wp_enqueue_script('peepso-notification',
            $logged_in ? PeepSo::get_asset('js/notifications.min.js') : FALSE,
            $logged_in ? array('underscore', 'peepso-observer', 'jquery-ui-position', 'peepso-jquery-mousewheel') : NULL,
            PeepSo::PLUGIN_VERSION, TRUE);

        wp_enqueue_script('peepso-window');
        wp_enqueue_script('peepso-modal-comments');

        // Recaptcha
        if(PeepSo3_ReCaptcha::should_enqueue()) {

            $host = PeepSo3_ReCaptcha::url();

            wp_register_script('peepso-recaptcha', PeepSo::get_asset('js/recaptcha.min.js'),
                array('peepso'), PeepSo::PLUGIN_VERSION, TRUE);
            wp_localize_script('peepso-recaptcha', 'peepsodata_recaptcha', array(
                'key' => PeepSo::get_option('site_registration_recaptcha_sitekey', 0),
                'host' => $host,
            ));
        }

        // postbox
        wp_register_script('peepso-posttabs', FALSE, array('peepso-bundle'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso-postbox-legacy', FALSE, array('peepso', 'peepso-bundle', 'peepso-posttabs'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso-postbox', FALSE, array('peepso', 'peepso-bundle', 'peepso-postbox-legacy'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_localize_script('peepso-postbox', 'psdata_postbox', array(
            'template' => PeepSoTemplate::exec_template('general', 'postbox', NULL, TRUE),
            'max_chars' => PeepSo::get_option('site_status_limit', 4000)
        ));

        // Auto-update time label script.
        wp_register_script('peepso-time', FALSE, array('peepso-bundle'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_localize_script('peepso-bundle', 'peepsotimedata', array(
            'ts'     => current_time('U'),
            'now'    => __('just now', 'peepso-core'),
            // Take account of the non-English plural forms (e.g. Arabic).
            // https://medium.com/@learningarabicwithangela/the-dual-in-arabic-language-al-muthanna-e7613051ef5f
            'min_1'   => sprintf( __('%s ago', 'peepso-core'), _n('%s min', '%s mins', 1, 'peepso-core') ),
            'min_2'   => sprintf( __('%s ago', 'peepso-core'), _n('%s min', '%s mins', 2, 'peepso-core') ),
            'min_3'   => sprintf( __('%s ago', 'peepso-core'), _n('%s min', '%s mins', 3, 'peepso-core') ),
            'hour_1'  => sprintf( __('%s ago', 'peepso-core'), _n('%s hour', '%s hours', 1, 'peepso-core') ),
            'hour_2'  => sprintf( __('%s ago', 'peepso-core'), _n('%s hour', '%s hours', 2, 'peepso-core') ),
            'hour_3'  => sprintf( __('%s ago', 'peepso-core'), _n('%s hour', '%s hours', 3, 'peepso-core') ),
            'day_1'   => sprintf( __('%s ago', 'peepso-core'), _n('%s day', '%s days', 1, 'peepso-core') ),
            'day_2'   => sprintf( __('%s ago', 'peepso-core'), _n('%s day', '%s days', 2, 'peepso-core') ),
            'day_3'   => sprintf( __('%s ago', 'peepso-core'), _n('%s day', '%s days', 3, 'peepso-core') ),
            'week_1'  => sprintf( __('%s ago', 'peepso-core'), _n('%s week', '%s weeks', 1, 'peepso-core') ),
            'week_2'  => sprintf( __('%s ago', 'peepso-core'), _n('%s week', '%s weeks', 2, 'peepso-core') ),
            'week_3'  => sprintf( __('%s ago', 'peepso-core'), _n('%s week', '%s weeks', 3, 'peepso-core') ),
            'month_1' => sprintf( __('%s ago', 'peepso-core'), _n('%s month', '%s months', 1, 'peepso-core') ),
            'month_2' => sprintf( __('%s ago', 'peepso-core'), _n('%s month', '%s months', 2, 'peepso-core') ),
            'month_3' => sprintf( __('%s ago', 'peepso-core'), _n('%s month', '%s months', 3, 'peepso-core') ),
            'year_1'  => sprintf( __('%s ago', 'peepso-core'), _n('%s year', '%s years', 1, 'peepso-core') ),
            'year_2'  => sprintf( __('%s ago', 'peepso-core'), _n('%s year', '%s years', 2, 'peepso-core') ),
            'year_3'  => sprintf( __('%s ago', 'peepso-core'), _n('%s year', '%s years', 3, 'peepso-core') ),
        ));
        wp_enqueue_script('peepso-time');

        // Reactions

        wp_enqueue_script('peepsoreactions',
            PeepSo::get_asset('js/reactions.min.js'),
            array('peepso'), PeepSo::PLUGIN_VERSION, TRUE);

        $data = array(
            'ajaxloader' => PeepSo::get_asset('images/ajax-loader.gif'),
        );

        wp_localize_script('peepsoreactions', 'peepsoreactionsdata', $data);

        // dynamic CSS
        $css = 'plugins'.DIRECTORY_SEPARATOR.'foundation'.DIRECTORY_SEPARATOR.'reactions-'.PeepSo3_Mayfly::get('peepso_reactions_css').'.css';
        if(!file_exists(PeepSo::get_peepso_dir().$css)) {
            $this->reactions_rebuild_cache();
        }
        $css_url = 'plugins/foundation/reactions-'.PeepSo3_Mayfly::get('peepso_reactions_css').'.css';

        wp_enqueue_style('peepsoreactions-dynamic', PeepSo::get_peepso_uri().$css_url, array(), PeepSo::PLUGIN_VERSION, 'all');

        // Hashtags
        if(PeepSo::get_option('hashtags_enable', 1)) {

            wp_enqueue_script('peepso-hashtags', PeepSo::get_asset('js/hashtags.min.js'), array('peepso'), PeepSo::PLUGIN_VERSION, TRUE);
            add_filter('peepso_data', function ($data) {
                $data['hashtags'] = array(
                    'url' => PeepSo::hashtag_url(),
                    'everything' => PeepSo::get_option('hashtags_everything', 0),
                    'min_length' => PeepSo::get_option('hashtags_min_length', 3 /* PeepSoHashtagsPlugin::CONFIG_MIN_LENGTH */),
                    'max_length' => PeepSo::get_option('hashtags_max_length', 16 /* PeepSoHashtagsPlugin::CONFIG_MAX_LENGTH */),
                    'must_start_with_letter' => PeepSo::get_option('hashtags_must_start_with_letter', 0)
                );
                return $data;
            }, 10, 1);

        }

        // Blogposts
        wp_enqueue_script('peepso-blogposts', PeepSo::get_asset('js/blogposts.min.js'),
            array('peepso', 'peepso-page-autoload'), PeepSo::PLUGIN_VERSION, TRUE);
        add_filter('peepso_data', function( $data ) {
            $data['blogposts'] = array(
                'delete_post_warning' => __( 'Deleting this post will reset likes and comments on the blog post. If comment integration is enabled, a fresh activity post will be automatically created.', 'peepso-core')
            );
            return $data;
        }, 10, 1);

        // Blogposts dynamic CSS
        $css = 'plugins/foundation/blogposts-'.PeepSo3_Mayfly::get('peepso_blogposts_css').'.css';
        if(!file_exists(PeepSo::get_peepso_dir().$css) ) {
            $this->blogposts_rebuild_cache();
            $css = 'plugins/foundation/blogposts-'.PeepSo3_Mayfly::get('peepso_blogposts_css').'.css';
        }

        wp_enqueue_style('peepso-blogposts-dynamic', PeepSo::get_peepso_uri().$css, array(), self::PLUGIN_VERSION, 'all');

        // Markdown
        wp_enqueue_style('peepso-markdown', PeepSo::get_asset('css/markdown/markdown.css'), array(), PeepSo::PLUGIN_VERSION, 'all');
        wp_enqueue_script('peepso-markdown', PeepSo::get_asset('js/markdown.min.js'), array('peepso'), PeepSo::PLUGIN_VERSION, TRUE);
        add_filter('peepso_data', function( $data ) {
            $data['markdown'] = array(
                'highlight-js' => PeepSo::get_asset('js/markdown/highlight.min.js') . '?ver=' . PeepSo::PLUGIN_VERSION,
                'highlight-css' => PeepSo::get_asset('css/markdown/highlight.min.css') . '?ver=' . PeepSo::PLUGIN_VERSION,
                'no_paragraph' => PeepSo::get_option('md_no_paragraph', 1),
                'enable_heading' => PeepSo::get_option('md_headers', 1),
            );
            return $data;
        }, 10, 1);

        // User
        add_filter('peepso_data', function($data) {
            if (!isset($data['user'])) {
                $data['user'] = array();
            }

            $data['user']['template_report'] = PeepSoTemplate::exec_template('profile', 'dialog-report', NULL, TRUE);

            return $data;
        }, 10, 1);

        if (isset($_SESSION['peepso_activate_account'])) {
            wp_add_inline_script('peepso-core', 'peepso.dialog("' . __('Your account has been activated', 'peepso-core') . '", { title: "' . __('Success', 'peepso-core') . '" }).show();');
            unset($_SESSION['peepso_activate_account']);
        }

        wp_enqueue_script('wp-embed');
    }

    public function enqueue_scripts_overrides()
    {
        // 1. Theme overrides
        $custom = locate_template('peepso3/custom.css');
        // only enqueue if custom.css exists in theme/peepso directory
        if (!empty($custom)) {
            $custom = get_stylesheet_directory_uri() . '/peepso3/custom.css';
            wp_register_style('peepso-custom', $custom, array(),
                PeepSo::PLUGIN_VERSION, 'all');
            wp_enqueue_style('peepso-custom');
        }


        // 2. User overrides
        $custom_user_file = PeepSo::OVERRIDES_DIRECTORY . '/css/style.css';

        $custom_user_path = self::get_peepso_dir() . $custom_user_file;

        // only enqueue if file exists
        if (file_exists($custom_user_path)) {
            $custom_user_uri = self::get_peepso_uri() . $custom_user_file;
            wp_register_style('peepso-custom-user', $custom_user_uri, array(),
                PeepSo::PLUGIN_VERSION, 'all');
            wp_enqueue_style('peepso-custom-user');
        }
    }

    public function enqueue_scripts_data()
    {
        $powered_by = PeepSo3_Helper_Addons::maybe_powered_by_peepso();

        $data_modules = apply_filters('peepso_data_modules', array());
        $data_elements = apply_filters('peepso_data_elements', array());

        $data_sections = apply_filters('peepso_data_sections', array(
            'search' => array(
                'show_images' => PeepSo::get_option_new('peepso_search_show_images'),
                'show_empty_sections' => PeepSo::get_option_new('peepso_search_show_empty_sections'),
                'text_no_results' => __('No results.', 'peepso-core'),
            )
        ));

        $ini_upload_max_filesize = convert_php_size_to_bytes(ini_get('upload_max_filesize'));
        $ini_post_max_size = convert_php_size_to_bytes(ini_get('post_max_size'));
        $upload_max_size = min($ini_upload_max_filesize, $ini_post_max_size, wp_max_upload_size());
        $data_upload = array(
            'maxSize' => $upload_max_size,
            'textErrorMaxSize' => sprintf(
                __('The file size you uploaded is too big. The maximum file size is %s.', 'peepso-core'),
                '<strong>' . size_format($upload_max_size) . '</strong>'
            )
        );

        $data_cover = array(
            '_wpnonce' => wp_create_nonce('cover-photo'),
            'templateDialog' => '',
            'textErrorFileType' => __('The file type you uploaded is not allowed. Only JPEG, PNG, and WEBP allowed.', 'peepso-core'),
        );

        $data_avatar = array(
            '_wpnonce' => wp_create_nonce('avatar-photo'),
            'templateDialog2' => '',
            'textErrorFileType' => __('The file type you uploaded is not allowed. Only JPEG, PNG, and WEBP allowed.', 'peepso-core'),
            // deprecated
            'uploadNonce' => wp_create_nonce('profile-photo'),
            'uploadMaxSize' => wp_max_upload_size(),
            'templateDialog' => '',
            'textErrorFileSize' => sprintf(__('The file size you uploaded is too big. The maximum file size is %s.', 'peepso-core'), '<strong>' . PeepSoGeneral::get_instance()->upload_size() . '</strong>'),
        );

        $data_dialog = array(
            'text_title_default' => __('Dialog', 'peepso-core'),
            'text_title_error' => __('Error', 'peepso-core'),
            'text_title_confirm' => __('Confirm', 'peepso-core'),
            'text_button_cancel' => __('Cancel', 'peepso-core'),
            'text_button_ok' => __('OK', 'peepso-core'),
            'template' => PeepSoTemplate::exec_template('general', 'dialog', NULL, TRUE)
        );

        $data = apply_filters('peepso_data', array(
            'is_admin' => $this->is_admin(),
            'home_url' => home_url(),
            'site_url' => site_url(),
            'rest_url' => esc_url_raw( rest_url( '/peepso/v1/' ) ),
            'rest_nonce' => wp_create_nonce( 'wp_rest' ),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'ajaxurl_legacy' => get_bloginfo('wpurl') . '/peepsoajax/',
            'version' => PeepSo::PLUGIN_VERSION,
            'postsize' => PeepSo::get_option('site_status_limit', 4000),
            'readmore_min' => PeepSo::get_option('site_activity_readmore', 1000),
            'readmore_min_single' => PeepSo::get_option('site_activity_readmore_single', 2000),
            'currentuserid' => get_current_user_id(),
            'userid' => apply_filters('peepso_user_profile_id', 0),		// user id of the user being viewed (from PeepSoProfileShortcode)
            'objectid' => apply_filters('peepso_object_id', 0),			// user id of the object being viewed
            'objecttype' => apply_filters('peepso_object_type', ''),	// type of object being viewed (profile, group, etc.)
            'date_format' => ps_dateformat_php_to_datepicker(get_option('date_format')),
            'members_page' => $this->get_page('members'),
            'members_hide_before_search' => PeepSo::get_option('members_hide_before_search', 0),
            'open_in_new_tab' => PeepSo::get_option('site_activity_open_links_in_new_tab',1),
            'hide_url_only' => PeepSo::get_option('hide_url_only', 0),
            'loading_gif' => PeepSo::get_asset('images/ajax-loader.gif'),
            'upload_size' => wp_max_upload_size(),
            'peepso_nonce' => wp_create_nonce('peepso-nonce'),
            // TODO: all labels and messages, etc. need to be moved into HTML content instead of passed in via js data
            // ART: Which template best suited to define the HTML content for these labels?
            // TODO: the one in which they're used. The 'Notice' string isn't used on all pages. Find the javascript that uses it and add it to that page's template
            'ajax_exception_text' => __('Something went wrong. Please contact the administrator.', 'peepso-core'),
            'label_error' => __('Error', 'peepso-core'),
            'label_notice' => __('Notice', 'peepso-core'),
            'label_done' => __('Done!', 'peepso-core'),
            'mark_all_as_read_text' => __('Mark all as read', 'peepso-core'),
            'mark_all_as_read_confirm_text' => __('Are you sure you want to mark all notifications as read?', 'peepso-core'),
            'show_unread_only_text' => __('Show unread only', 'peepso-core'),
            'show_all_text' => __('Show all', 'peepso-core'),
            'view_all_text' => __('View All', 'peepso-core'),
            'read_more_text' => __('Read more', 'peepso-core'),
            'mime_type_error' => __('The file type you uploaded is not allowed.', 'peepso-core'),
            'login_dialog_title' => __('Please login to continue', 'peepso-core'),
            'login_dialog' => PeepSoTemplate::exec_template('general', 'login', NULL, TRUE),
            'login_with_email' => 2 === (int) PeepSo::get_option('login_with_email', 0),
            'like_text' => _n(' person likes this', ' people like this.', 1, 'peepso-core'),
            'like_text_plural' => _n(' person likes this', ' people like this.', 2, 'peepso-core'),
            'profile_unsaved_notice' => __('There are unsaved changes on this page.', 'peepso-core'),
            'profile_saving_notice' => __('The system is currently saving your changes.', 'peepso-core'),
            'comments_unsaved_notice' => __('Any unsaved comments will be discarded. Are you sure?', 'peepso-core'),
            'activity_limit_page_load' => PeepSoActivity::ACTIVITY_LIMIT_PAGE_LOAD,
            'activity_limit_below_fold' => apply_filters('peepso_filter_activity_limit_below_fold',PeepSoActivity::ACTIVITY_LIMIT_BELOW_FOLD),
            'loadmore_enable' => PeepSo::get_option('loadmore_enable', 0),
            'loadmore_repeat' => PeepSo::get_option('loadmore_repeat', 0),
            'get_latest_interval' => PeepSo::get_option('notification_ajax_delay', 30000),
            'external_link_warning' => PeepSo::get_option('external_link_warning', 0),
            'external_link_warning_page' => PeepSo::get_page('external_link_warning', 0),
            'external_link_whitelist' => apply_filters('external_link_whitelist', ''),
            'trim_url' => PeepSo::get_option('trim_url', 0),
            'trim_url_https' => PeepSo::get_option('trim_url_https', 0),
            'notification_ajax_delay_min' => PeepSo::get_option('notification_ajax_delay_min', 5000),
            'notification_ajax_delay' => PeepSo::get_option('notification_ajax_delay', 30000),
            'notification_ajax_delay_multiplier' => PeepSo::get_option('notification_ajax_delay_multiplier', 1.5),
            'notification_header' => PeepSoTemplate::exec_template('general', 'notification-popover-header', NULL, TRUE),
            'notification_url' => PeepSo::get_page('notifications'),
            'sse' => PeepSo::get_option('sse', 0),
            'sse_url' => ! empty( PeepSo::get_option('sse_backend_url', '') ) ? PeepSo::get_option('sse_backend_url', '') : plugin_dir_url( __FILE__ ) . 'sse.php',
            'sse_domains' => array( PeepSo::get_option('sse_backend_url', home_url()) ),
            'sse_backend_delay' => PeepSo::get_option('sse_backend_delay', 5000),
            'sse_backend_timeout' => PeepSo::get_option('sse_backend_timeout', 30000),
            'sse_backend_keepalive' => PeepSo::get_option('sse_backend_keepalive', 5),
            'sse_wpnonce' => wp_create_nonce('peepso-sse-token'),
            'auto_rtl' => PeepSo::is_dev_mode('auto_rtl'),
            'show_powered_by'=> strlen($powered_by) ? TRUE : FALSE,
            'powered_by' => $powered_by,
            '_et_no_asyncscript' => 'var ETBuilderBackendDynamic = {',
            'modules' => $data_modules,
            'elements' => $data_elements,
            'sections' => $data_sections,
            'upload' => $data_upload,
            'avatar' => $data_avatar,
            'cover' => $data_cover,
            'dialog' => $data_dialog
        ));

        wp_localize_script('peepso-core', 'peepsodata', $data);
    }

    /*
     * registers shortcode
     */
    private function register_shortcodes()
    {
        if(PeepSo::is_api_request()) { return; }
        foreach ($this->shortcodes as $shortcode => $callback) {
            if(is_callable($callback)) {
                add_shortcode($shortcode, $callback);
            }
        }
    }

    public function all_shortcodes() {
        $foundation_shortcodes = PeepSo::get_instance()->shortcode_classes;

        if(PeepSo::is_dev_mode('new_search')) {
            $foundation_shortcodes['peepso_search'] = 'PeepSo3_Shortcode_Search';
        }

        ksort($foundation_shortcodes );

        $shortcodes = apply_filters('peepso_filter_shortcodes', array());
        ksort($shortcodes);

        $shortcodes = array_merge($foundation_shortcodes, $shortcodes);


        if(0==PeepSo::get_option('external_link_warning', 0)) {
            unset($shortcodes['peepso_external_link_warning']);
        }

        return $shortcodes;
    }

    public function pages_with_shortcode($sc) {
        $options = array();

        $args = array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'sort_order' => 'asc',
            'sort_column' => 'post_title',
            'hierarchical' => 0,
        );

        $pages = get_pages($args);

        foreach ($pages as $page) {
            if(!stristr($page->post_content, "[$sc")) {
                continue;
            }

            $site_url = preg_replace("(^https?://)", "", get_home_url());
            $page_url = preg_replace("(^https?://)", "", get_permalink($page->ID));
            $peepso_url = str_replace($site_url, '', $page_url);
            $peepso_url = trim(rtrim($peepso_url, '/'), '/');

            if(!strlen($peepso_url)) {
                $peepso_url = '/';
                $options = array_merge(array($peepso_url=>array('id'=> $page->ID, 'label'=>__('Front Page'))),$options);
            } else {
                $options[$peepso_url] = array('id'=>$page->ID, 'label'=>"/$peepso_url/  \"{$page->post_title}\"");
            }


        }

        return $options;
    }

    public function check_shortcode($sc, $options) {

        $error = FALSE;

        if(!array_key_exists(PeepSo::get_option(str_ireplace('peepso_','page_', $sc)), $options)) {
            $error = sprintf(__('The assigned page with %s shortcode was not found.<br/>Make sure the page exists (is not trashed) and is published.', 'peepso-core'), '<b>' . $sc . '</b>');
            if (count($options)) {
                $error .= "<br/>" . sprintf(__('Or choose another page from %s.', 'peepso-core'), '<a href="' . admin_url('admin.php?page=peepso_config&tab=navigation#'.$sc) . '">' . __('the list','peepso-core') . '</a>');
            } else {
                $error .= "<br/>" . sprintf(
                        __('Or create a new page with %s shortcode and assign it %s.', 'peepso-core'),
                        '<b>' . $sc . '</b>',
                        '<a href="' . admin_url('admin.php?page=peepso_config&tab=navigation#'.$sc) . '">' . __('here','peepso-core') . '</a>'
                    );
            }
        }

        return $error;
    }


    /**
     * Sets the current shortcode identifier, only the first call to this method is ran
     * @param string $shortcode A string that may be used to identify which shortcode ran first
     */
    public static function set_current_shortcode($shortcode)
    {
        if (NULL === self::$_current_shortcode)
            self::$_current_shortcode = $shortcode;
    }

    public static function do_not_cache() {
        if(!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }
    }

    /**
     * Returns the identifier for the first PeepSo shortcode that was called
     * @return string
     */
    public static function get_current_shortcode()
    {
        return (self::$_current_shortcode);
    }

    /*
     * callback function for the 'peepso_profile' shortcode
     * @param array $atts shortcode attributes
     * @param string $content contents of shortcode
     */
    public static function profile_shortcode($atts, $content = '')
    {
        $sc = new PeepSoProfileShortcode($atts, $content);
        return ($sc->do_shortcode($atts, $content));
    }

    /*
     * callback function for the 'peepso_register' shortcode
     * @param array $atts shortcode attributes
     * @param string $content contents of shortcode
     */
    public static function register_shortcode($atts, $content = '')
    {
        $sc = self::get_instance()->sc;
        if (is_null($sc)) {
            $sc = new PeepSoRegisterShortcode();
        }
        return ($sc->do_shortcode($atts, $content));
    }

    /*
     * callback function for the 'peepso_recover' shortcode
     * @param array $atts shortcode attributes
     * @param string $content contents of shortcode
     */
    public static function recover_shortcode($atts, $content = '')
    {
        $sc = new PeepSoRecoverPasswordShortcode();
        return ($sc->do_shortcode($atts, $content));
    }

    /*
     * callback function for the 'peepso_reset' shortcode
     * @param array $atts shortcode attributes
     * @param string $content contents of shortcode
     */
    public static function reset_shortcode($atts, $content = '')
    {
        $sc = new PeepSoResetPasswordShortcode();
        return ($sc->do_shortcode($atts, $content));
    }

    /*
     * callback function for the 'peepso_members' shortcode
     * @param array $atts shortcode attributes
     * @param string $content contents of shortcode
     */
    public static function search_shortcode($atts, $content = '')
    {
        $sc = new PeepSoMembersShortcode();
        return ($sc->shortcode_search($atts, $content));
    }

    public static function notifications_shortcode($atts, $content = '')
    {
        $sc = new PeepSoNotificationsShortcode();
        return ($sc->do_shortcode($atts, $content));
    }

    public static function external_link_warning_shortcode($atts, $content = '') {
        $sc = new PeepSoExternalLinkWarningShortcode();
        return ($sc->do_shortcode($atts, $content));
    }

    /*
     * return PeepSo option values
     * @param string $name name of the option value being requested
     * @param string $default default value to return if nothing found
     * @return multi the stored option value
     */
    public static function get_option($name, $default = NULL, $check_length = FALSE)
    {
        if (NULL === self::$_config && class_exists('PeepSoConfigSettings')) {
            self::$_config = PeepSoConfigSettings::get_instance();
        }

        if (NULL === self::$_config) {
            return FALSE;
        }

        // Raise a warning only once in a while because logging can kill the server
        // For now we are not phasing out this option if check_length is in use
        if(!$check_length && NULL !== $default && 1==rand(0,1000) ) {
            //new PeepSoError("PeepSo::get_option('$name') is DEPRECATED since 2.8.0. See issue #4196");
        }

        $value = self::$_config->get_option($name, $default);

        if(TRUE == $check_length && !strlen($value)) {
            return $default;
        }

        /** OVERRIDES && FALLBACKS **/

        // @todo make this a filter if we need more magic in the future

        // make sure loadmore_repeat is an even number
        if('loadmore_repeat' == $name && $value > 0 && $value % 2) {
            $value++;
        }

        return $value;
    }

    // Starting with PeepSo 2.8.0 we begin to phase out the default value for these calls
    public static function get_option_new($name) {
        if (NULL === self::$_config && class_exists('PeepSoConfigSettings')) {
            self::$_config = PeepSoConfigSettings::get_instance();
        }

        if (NULL === self::$_config) {
            return FALSE;
        }

        $value = self::$_config->get_option($name);

        /** OVERRIDES && FALLBACKS **/

        // @todo make this a filter if we need more magic in the future

        // make sure loadmore_repeat is an even number
        if('loadmore_repeat' == $name && $value > 0 && $value % 2) {
            $value++;
        }

        return $value;
    }

    /*
     * Return a named page as a fully qualified URL
     * @param string $name Name of page
     * @param $trp_translate whether to allow TRP to add a language slug
     * @return string URL to the fully qualified page name
     */
    public static function get_page_trp($new_url, $abs_home, $TRP_LANGUAGE, $path, $url ){
        return $url;
    }

    public static function get_page($name, $trp_translate = TRUE)
    {
        // Remove TRP language prefix if called from MailQueue
        if(!$trp_translate) {
            add_filter('trp_home_url', ['PeepSo', 'get_page_trp'],99999,5);
        }

        switch ($name) {
            case 'logout':
                $ret = self::get_page('profile',$trp_translate) . '?logout';
                break;

//            case 'notifications':
//                $ret = '#';
//                break;

            case 'redirectlogin':
                $page_id = PeepSo::get_option('site_frontpage_redirectlogin');

                $ret = '';

                if(-1 == $page_id) {
                    $ret = home_url('/');
                }elseif (is_numeric($page_id)) {
                    $page_id = intval($page_id);
                    if ($page_id > 0) {
                        $post = get_post($page_id);
                        $ret = get_page_link($post);
                    }
                }
                break;
            case 'activation_redirect':
                $page_id = PeepSo::get_option('site_activation_redirect');

                $ret = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : home_url('/');

                if(-1 == $page_id) {
                    $peepso_first_visit = isset($_SESSION['peepso_first_visit']) ? $_SESSION['peepso_first_visit'] : '';
                    if ((strpos($peepso_first_visit, 'community_activation_code') === FALSE && strpos($peepso_first_visit, 'peepso_activation_code') === FALSE) && !empty($peepso_first_visit)) {
                        $ret = $peepso_first_visit;
                    } else {
                        $ret = home_url('/');
                    }
                }elseif (0 == $page_id) {
                    $ret = home_url('/');
                }elseif (is_numeric($page_id)) {
                    $page_id = intval($page_id);
                    if ($page_id > 0) {
                        $post = get_post($page_id);
                        $ret = get_page_link($post);
                    }
                }
                break;
            case 'logout_redirect':
                $page_id = PeepSo::get_option('logout_redirect');

                $ret = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : home_url('/');

                if(-1 == $page_id) {
                    $ret = home_url('/');
                }elseif (is_numeric($page_id)) {
                    $page_id = intval($page_id);
                    if ($page_id > 0) {
                        $post = get_post($page_id);
                        $ret = get_page_link($post);
                    }
                }
                break;
            case 'activity_status':
                $ret = PeepSo::get_page('activity', $trp_translate) . '?status/';


                if (1 == PeepSo::get_option('disable_questionmark_urls', 0)) {
                    $frontpage = get_post(get_option('page_on_front'));

                    if ('posts' == get_option( 'show_on_front' ) || !has_shortcode($frontpage->post_content, 'peepso_activity')) {
                        $ret = PeepSo::get_page('activity', $trp_translate) . 'status/';
                    }
                }

                break;
            case 'activity_status_seo': // used in XML sitemaps

                $frontpage = get_post(get_option('page_on_front'));

                if ('posts' == get_option( 'show_on_front' ) || !has_shortcode($frontpage->post_content, 'peepso_activity')) {
                    $ret = PeepSo::get_page('activity', $trp_translate) . 'status/';
                }

                break;


            default:
                $page_slug = self::get_option('page_' . $name);
                $ret = get_bloginfo('url') . '/' . (!empty($page_slug && $page_slug != '/') ? $page_slug . '/' : '');
                break;
        }

        $ret = apply_filters('peepso_get_page', $ret, $name);
        $ret = preg_replace('/([^:])(\/{2,})/', '$1/', $ret);

        // Clean up TRP removal filter
        remove_filter('trp_home_url', ['PeepSo', 'get_page_trp'],99999,5);

        return ($ret);
    }


    /*
     * builds a link to a user's profile page
     * @param int $user_id
     * @return string URL to user's profile
     */
    public static function get_user_link($user_id)
    {
        $ret = get_home_url();

        $user = get_user_by('id', $user_id);
        if (FALSE !== $user) {
            $ret .= '/' . PeepSo::get_option('page_profile') . '/?';
            $ret .= $user->user_nicename. '/';
        }

        return (apply_filters('peepso_username_link', $ret, $user_id));
    }

    /*
     * Filter function for 'get_avatar'. Substitutes the PeepSo avatar for the WP one
     * @param string $avater The HTML for the <img> reference to the avatar
     * @param mixed $id_or_email The user id for the avatar (if value is numeric)
     * @param int $size Size in pixels of desired avatar
     * @param string $default The src= attribute value for the <img>
     * @param string $alt The alt= attribute for the <img>
     * @param boolean $return_source Return the source of the image
     * @return string The HTML for the full <img> element
     */
    public function filter_avatar($avatar, $id_or_email, $size, $default, $alt, $return_source = false)
    {
        if( 0 === intval(PeepSo::get_option('avatars_peepso_only', 0))) {
            return $avatar;
        }

        // https://github.com/jomsocial/peepso/issues/735
        // http://wordpress.stackexchange.com/questions/125692/how-to-know-if-admin-is-in-edit-page-or-post
        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if (is_object($screen) && $screen->parent_base == 'edit') {
                return ($avatar);
            }
        }

        // if id_or email is an object, it's a Wordpress default, try getting an email address from it
        if (is_object($id_or_email) && property_exists($id_or_email, 'comment_author_email')) {

            // if the email exists
            if (strlen($id_or_email->comment_author_email) && get_user_by('email', $id_or_email->comment_author_email)){
                $id_or_email = $id_or_email->comment_author_email;
            } else {
                $id_or_email = $id_or_email->user_id;
            }
        }

        // numeric id
        if (is_numeric($id_or_email)) {
            $user_id = intval($id_or_email);
        } else if (is_object($id_or_email)) {
            // if it's an object then it's a wp_comments avatar; just return what's already there
            return ($avatar);
        } else {
            if ($user = get_user_by('email', $id_or_email)) {
                $user_id = $user->ID;
            } else {
                return ($avatar); // if we can't lookup by email just return what's already found
            }
        }

        if (intval($user_id) === 0) {
            return ($avatar);
        }

        $user = PeepSoUser::get_instance($user_id);
        $img = $user->get_avatar();
        if ($return_source) {
            $avatar = $img;
        } else {
            $avatar = '<img alt="' . esc_attr(trim(strip_tags($user->get_fullname()))) . ' avatar" src="' . $img . '" class="avatar avatar-' . $size . " photo\" width=\"{$size}\" height=\"{$size}\" />";
        }
        return ($avatar);
    }

    public function filter_avatar_url($avatar, $id_or_email, $args)
    {
        return $this->filter_avatar($avatar, $id_or_email, $args['size'], NULL, NULL, TRUE);
    }

    /**
     * returns URL to PeepSo user's profile page if config enable
     * @return string URL to PeepSo user's profile page
     */
    public static function modify_author_link( $link, $user_id, $user_nicename )
    {
        if( intval($user_id) === 0) {
            return $link;
        }

        if( 1 === intval(PeepSo::get_option('always_link_to_peepso_profile', 0))) {
            $user = PeepSoUser::get_instance($user_id);
            if($user){
                $link = $user->get_profileurl();
            }
        }
        return $link;
    }

    /**
     * returns URL to PeepSo user's profile page if config enable
     * @return string URL to PeepSo user's profile page
     */
    public static function modify_edit_profile_link( $link, $user_id, $scheme )
    {
        if($scheme != 'admin') {
            if( 1 === intval(PeepSo::get_option('always_link_to_peepso_profile', 0))) {
                $user = PeepSoUser::get_instance($user_id);
                if($user){
                    $link = $user->get_profileurl();
                }
            }
        }
        return $link;
    }

    /**
     * returns URL to PeepSo user's profile page if config enable
     * @return string URL to PeepSo user's profile page
     */
    public function new_comment_author_profile_link($return, $author, $comment_ID){

        $comment = get_comment( $comment_ID );

        if(intval($comment->user_id) === 0) {
            return $return;
        }

        /* Get the comment author config option */
        if( 1 === intval(PeepSo::get_option('always_link_to_peepso_profile', 0))) {
            $user = PeepSoUser::get_instance($comment->user_id);
            if($user){
                $return = "<a href='".$user->get_profileurl()."' rel='' class='author-url'>$author</a>";
            }
        }

        return $return;
    }

// Users, roles, permissions

// the following are used to check permissions
// @todo clean up the const
    const PERM_POST = 'post';
    const PERM_POST_VIEW = 'post_view';
    const PERM_POST_EDIT = 'post_edit';
    const PERM_POST_DELETE = 'post_delete';
    const PERM_COMMENT = 'comment';
    const PERM_COMMENT_DELETE = 'delete_comment';
    const PERM_POST_LIKE = 'like_post';
    const PERM_COMMENT_LIKE = 'like_comment';
    const PERM_PROFILE_LIKE = 'like_profile';
    const PERM_PROFILE_VIEW = 'view_profile';
    const PERM_PROFILE_EDIT = 'edit_profile';
    const PERM_REPORT = 'report';

    /**
     * Returns the PeepSo specific role assigned to the current user
     * @return string One of the role names, 'user','member','moderator','admin','ban','register','verified' or FALSE if the user is not logged in
     */
    private static function _get_role()
    {
        static $role = NULL;
        if (NULL !== $role)
            return ($role);

        if (!is_user_logged_in())
            return ($role = FALSE);

        $user = PeepSoUser::get_instance(get_current_user_id());
        return ($role = $user->get_user_role());
    }

    /*
     * Checks if current/specific user has admin priviledges
     * @param int $user_id which user to check  - defaults to current user
     * @return boolean TRUE if user has admin priviledges, otherwise FALSE
     */
    public static function is_admin($user_id = NULL)
    {
        // @since 2.11 $user_id can be passed to check if someone else is an admin
        if(NULL !== $user_id) {

            $user_id = (int) $user_id;

            if (user_can($user_id, 'manage_options')) {
                return TRUE;
            }

            $PeepSoUser = PeepSoUser::get_instance($user_id);
            $role = $PeepSoUser->get_user_role();
            if ('admin' === $role) {
                return TRUE;
            }

            return FALSE;
        }

        // if no $user_id is passed, the odl code is executed
        static $is_admin = NULL;

        if (NULL !== $is_admin) {
            return ( $is_admin );
        }

        // WP administrators is set to PeepSo admins automatically
        if (current_user_can('manage_options')) {
            return ( $is_admin = true );
        }

        // if user not logged in, always return FALSE
        if (!is_user_logged_in()) {
            return ( $is_admin = false );
        }

        // check the PeepSo user role
        $role = self::_get_role();
        if ('admin' === $role) {
            return ( $is_admin = true );
        }

        return ($is_admin = FALSE);
    }



    public static function is_dev_mode($mode='', $check_option = TRUE) {

        if($check_option) {
            if (PeepSo::get_option('dev_mode_' . $mode, 0)) {
                return TRUE;
            }
        }

        if(strlen($mode)) {
            $mode = strtoupper('PEEPSO_DEV_MODE_'.$mode);
        } else {
            $mode = 'PEEPSO_DEV_MODE'; // default
        }

        return( ( defined('PEEPSO_DEV_MODE') && 1==PEEPSO_DEV_MODE ) || ( defined($mode) && 1 == constant($mode) ) );
    }
    public static function can_schedule_posts() {
        return (PeepSo::is_admin() || PeepSo::get_option('scheduled_posts_enable',0));
    }

    public static function is_api_request() {

        // #5560 debug
//        if(!isset($_REQUEST['action']) || 'peepso_log' !=$_REQUEST['action']) {
//            new PeepSoError(print_r($_REQUEST, TRUE));
//        }

        // #5560 attempt to detect WordPress.com / JetPack calls and mark them as API calls
        if(PeepSo::get_option_new('compatibility_wordpress_com')) {

            if (isset($_REQUEST['rest_route'])) {
                return TRUE;
            }

            if (isset($_REQUEST['for']) && 'jetpack' == $_REQUEST['for']) {
                return TRUE;
            }
            if (isset($_REQUEST['_for']) && 'jetpack' == $_REQUEST['_for']) {
                return TRUE;
            }

        }

        return ( wp_doing_ajax() || (defined('DOING_AJAX') && DOING_AJAX) || (defined('REST_REQUEST') && REST_REQUEST) );
    }

    /**
     * Checks if current user is a member, i.e. has access to viewing the site.
     * @return boolean TRUE if user is allowed to view the site; otherwise FALSE.
     */
    public static function is_member()
    {
        static $is_member = NULL;
        if (NULL !== $is_member)
            return ($is_member);

        $role = self::_get_role();
        // banned, and registered/verified but not approved users are not full members
        if ('ban' === $role || 'register' === $role || 'verified' === $role)
            return ($is_member = FALSE);

        // TODO: use current_user_can() when/if we create capabilities
//		if (current_user_can('peepso_member'))
//			return ($is_member = FALSE);

        return ($is_member = TRUE);
    }

    /**
     * Checks if current user is a moderator.
     * @return boolean TRUE if user is a moderator; otherwise FALSE.
     */
    public static function is_moderator()
    {
        static $is_moderator = NULL;
        if (NULL !== $is_moderator)
            return ($is_moderator);

        $role = self::_get_role();
        if ('moderator' === $role)
            return ($is_moderator = TRUE);

        // TODO: use current_user_can() when/if we create capabilities
//		if (current_user_can('peepso_moderator'))
//			return ($is_moderator = TRUE);

        return ($is_moderator = FALSE);
    }

    public static function can_pin($post_id = NULL) {

        // Pinned posts disabled - always FALSE
        if(!PeepSo::get_option_new('pinned_posts_enable')) {
            return FALSE;
        }

        // Admin can pin anything - always TRUE
        if (PeepSo::is_admin()) {
            return TRUE;
        }

        // Guest cannot pin - always FALSE
        if(!get_current_user_id()) {
            return FALSE;
        }

        $result = FALSE;

        $result = apply_filters('peepso_can_pin', $result, $post_id);

        return $result;
    }

    public static function can_nsfw($post_id) {
        if (PeepSo::is_admin()) {
            return TRUE;
        }


        $post = get_post($post_id);
        if ($post->post_author && $post->post_author == get_current_user_id()) {
            return TRUE;
        }

        return apply_filters('peepso_can_nsfw', FALSE, $post_id);
    }

    /*
     * Check if author has permission to perform action on an owner's Activity Stream
     * @param int $owner The user id of the owner of the Activity Stream
     * @param string $action The action that the author would like to perform
     * @param int $author The author requesting permission to perform the action
     * @param boolean $allow_logged_out Whether or not to allow guest permissions
     * @return Boolean TRUE if author can take the requested action; otherwise FALSE
     */
    public static function check_permissions($owner, $action, $author, $allow_logged_out = FALSE)
    {
        $owner = intval($owner);
        $author = intval($author);

        // admin always has permissions to do something
        if (PeepSo::is_admin()) {
            return (TRUE);
        }

        // verify user and author ids
        if (0 === $owner || (0 === $author && FALSE === $allow_logged_out)) {
            return (FALSE);
        }

        // check if author_id is the current user
        if ($author != get_current_user_id()) {
            return (FALSE);
        }

        // owner always has permissions to do something to themself
        if ($owner === $author) {
            return (TRUE);
        }

        // check if on the user's block list
        if (PeepSo::get_option_new('user_blocking_enable')) {
            $blk = new PeepSoBlockUsers();
            if ($blk->is_user_blocking($owner, $author, TRUE)) {
                // author is on the owner's block list - exit
                return (FALSE);
            }
        }

        // check author access depending on the action being performed
        switch ($action)
        {
            case self::PERM_POST_VIEW:

                global $post;
                if (isset($post->act_access)) {
                    $access = intval($post->act_access);
                    $post_owner = intval($post->act_owner_id);
                } else {
                    // in case someone calls this from outside PeepSoActivityShortcode
                    global $wpdb;
                    $sql = 'SELECT `act_access`, `act_owner_id` ' .
                        " FROM `{$wpdb->posts}` " .
                        " LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act` ON `act`.`act_external_id`=`{$wpdb->posts}`.`ID` " .
                        ' WHERE `ID`=%d AND `act`.`act_module_id`=%d
					  LIMIT 1 ';

                    $module_id = (isset($post->act_module_id) ? $post->act_module_id : PeepSoActivity::MODULE_ID);
                    $ret = $wpdb->get_row($wpdb->prepare($sql, $post->ID, $module_id));

                    if ($ret) {
                        $access = intval($ret->act_access);
                        $post_owner = intval($ret->act_owner_id);
                    } else {
                        $access = 10;
                        $post_owner = NULL;
                    }
                }
                switch ($access)
                {
                    case self::ACCESS_PUBLIC:
                        return (TRUE);
                        break;
                    case self::ACCESS_MEMBERS:
                        if (is_user_logged_in()) {
                            return (TRUE);
                        }
                        return FALSE;
                        break;
                    case self::ACCESS_PRIVATE:
                        if (get_current_user_id() === $owner) {
                            return (TRUE);
                        }

                        return FALSE;
                        break;
                }

                $can_access = apply_filters('peepso_check_permissions-' . $action, -1, $owner, $author, $allow_logged_out);

                if (-1 !== $can_access)
                    return ($can_access);
                return (FALSE);
                break;

            case self::PERM_POST:
            case self::PERM_COMMENT:
                break;

            case self::PERM_POST_EDIT:
                if(($owner === $author) || ($owner === get_current_user_id())) {
                    return TRUE;
                }

                return apply_filters('peepso_check_permissions-post_edit', FALSE, $owner, $author, $allow_logged_out);

                break;

            case self::PERM_POST_DELETE:
            case self::PERM_COMMENT_DELETE:
                if(($owner === $author) || ($owner === get_current_user_id())) {
                    return TRUE;
                }

                return apply_filters('peepso_check_permissions-post_delete', FALSE, $owner, $author, $allow_logged_out);

                break;

            case self::PERM_POST_LIKE:			 // intentionally fall through
            case self::PERM_COMMENT_LIKE:
            case self::PERM_PROFILE_VIEW:
                $user = PeepSoUser::get_instance($owner);
                return ($user->is_accessible('profile'));
                break;

            case self::PERM_PROFILE_LIKE:
                if (! PeepSo::get_option('site_likes_profile', TRUE))
                    return (FALSE);

                $user = PeepSoUser::get_instance($owner);
                return ($user->is_profile_likable());
                break;

            case self::PERM_REPORT:
                if (1 === PeepSo::get_option('site_reporting_enable'))
                    return (TRUE);				// if someone can see the content, they can report it
                // TODO: possibly allow reporting only by logged in users
                return (FALSE);
                break;

            default:
                $can_access = apply_filters('peepso_check_permissions-' . $action, -1, $owner, $author, $allow_logged_out);

                if (-1 !== $can_access)
                    return ($can_access);
            // Fall through if a filter for the action doesn't exist.
        }


        // anything that falls through -- check owner's access settings

        $ret = FALSE;

        $own = PeepSoUser::get_instance($owner);
        if ($own) {
            $ret = $own->check_access($action, $author);

        }


        return ($ret);
    }


    public static function check_permission_profile_details(int $owner, string $section)
    {

        $privacy = intval(PeepSo::get_option('profile_' . $section . '_privacy_default', 10));

        // We will add user overrides later
//        if(PeepSo::get_option_new('profile_'.$section.'_privacy_user_override')) {
//            $override = 0; // get from user meta using $sections
//            if( $override > 0 ) {
//              $privacy = $override;
//              }
//        }

        // Disabled
        if( 99 == $privacy)                                     { return FALSE; }

        // Admin
        if(PeepSo::is_admin())                                  { return TRUE; }

        // Owner
        if( $owner == get_current_user_id())                    { return TRUE; }

        // Only Me
        if(40 == $privacy)                                      { return FALSE; }

        // Friends with "site members" fallback
        if(30 == $privacy) {
            if(class_exists('PeepSoFriendsModel')) {
                $PeepSoFriendsModel = PeepSoFriendsModel::get_instance();
                return $PeepSoFriendsModel->are_friends(get_current_user_id(), $owner);
            } else {
                $privacy = 20;
            }
        }

        // Site Members
        if(20 == $privacy && !get_current_user_id())            { return FALSE; }

        return TRUE;
    }


    /* Determine if a given user id is the owner of an item
     * @param int $post_id The id of the post item to check
     * @param int $owner_id The user id of the post item to check
     * @return Boolean TRUE if it's the owner, otherwise FALSE
     */
    public static function is_owner($post_id, $owner_id)
    {
        // TODO: expand capabilities to do checks on other types of data/tables

        global $wpdb;
        // TODO: use class constant for table name
        $sql = "SELECT COUNT(*) FROM `{$wpdb->prefix}peepso_activities` " .
            " WHERE `act_id`=%d AND `act_owner_id`=%d ";
        $ret = $wpdb->get_var($wpdb->prepare($sql, $post_id, $owner_id));

        return (intval($ret) > 0 ? TRUE : FALSE);
    }

    public static function get_last_used_privacy($user_id)
    {
        $privacy = get_user_meta($user_id, 'peepso_last_used_post_privacy', TRUE);

        return $privacy;
    }

    /*
     * Returns the current user's role
     * @return string The name of the current user's PeepSo role (one of 'ban', 'register', 'verified', 'user', 'member', 'moderator', 'admin') or NULL if the user is not logged in
     */
    public static function get_user_role()
    {
        // http://wordpress.org/support/topic/how-to-get-the-current-logged-in-users-role
        $role = NULL;
        if (function_exists('is_user_logged_in') && is_user_logged_in()) {
            $role = self::_get_role();
//			global $current_user;
//
//			$aRoles = array_values($current_user->roles);
//			if (count($aRoles) > 0)
//				$sRet = $aRoles[0];
        }
        return ($role);
    }

// Notifications
    /*
     * Return user id of administrator that should receive notifications
     * @return boolean|int Admin user id if email exists, FALSE if otherwise
     */
    public static function get_notification_user()
    {
        $email = self::get_notification_emails();
        $wpuser = get_user_by('email', $email);

        return (FALSE !== $wpuser) ? $wpuser->ID : FALSE;
    }

    public static function get_notification_emails()
    {
        $email = get_option( 'admin_email' );
        return ($email);
    }


// URLs and paths

    /*
     * return user's IP address
     * @return string The IP address of the current user
     */
    public static function get_ip_address()
    {
        static $ip = NULL;

        if (NULL !== $ip) {
            return ($ip);
        }

        $ret = NULL;

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ret = $_SERVER['REMOTE_ADDR'];
        }

        /*
         * Since 2.7.11
         *
         * We are not a security plugin, and we will base IP detection only on the most secure / mainstream way of detecting IP addresses
         * If anyone wants to do something more fancy, there's now a filter for that
         */
        return ($ip = apply_filters('peepso_get_ip_address', $ret));
    }

    /*
     * Returns the current page URL with any directory prefixes (when WP is installed in a child directory) removed
     * @return string The URL of the current page, with directory prefixes removed
     */
    public static function get_page_url()
    {
        $url = $_SERVER['REQUEST_URI'];

        $page = get_home_url();
        $page = str_replace('http://', '', $page);
        $page = str_replace('https://', '', $page);

        // remove host name at beginning of URL
        if (isset($_SERVER['HTTP_HOST']) && substr($page, 0, strlen($_SERVER['HTTP_HOST'])) === $_SERVER['HTTP_HOST'])
            $page = substr($page, strlen($_SERVER['HTTP_HOST']));

        // remove directory prefix from REQUEST_URI
        if (substr($url, 0, strlen($page)) === $page)
            $url = substr($url, strlen($page));

        // remove any surrounding / characters
        $url = trim($url, '/');

        return ($url);
    }

    /*
     * Get the directory that PeepSo is installed in
     * @return string The PeepSo plugin directory, including a trailing slash
     */
    public static function get_plugin_dir()
    {
        return (plugin_dir_path(__FILE__));
    }

    /**
     * Return reference to asset, relative to the base plugin's /assets/ directory
     *
     * @param string $ref asset name to reference
     * @param string $base_file
     * @return string href to fully qualified location of referenced asset
     */
    public static function get_asset($ref, $base_file = NULL)
    {
        if('images'==substr($ref,0,6)) {
            $override = PeepSo::OVERRIDES_DIRECTORY.'/' . $ref;
            if (file_exists(PeepSo::get_peepso_dir() . $override)) {
                return (PeepSo::get_peepso_uri() . $override);
            }
        }

        $base_file = $base_file ? $base_file : __FILE__;
        $ret = plugin_dir_url($base_file) . 'assets/' . $ref;
        $ret = PeepSo::add_cachebust_arg($ret, $base_file);

        return ($ret);
    }

    /**
     * Add cache buster to the resource URL.
     *
     * @since 3.0.1.0
     *
     * @param string $url
     * @return string
     */
    public static function add_cachebust_arg($url, $base_file = NULL) {
        if (PeepSo::get_option_new('cache_busting')) {
            $base_file = $base_file ? $base_file : __FILE__;
            $base_url = plugin_dir_url($base_file);
            $base_path = plugin_dir_path($base_file);
            $file = str_replace($base_url, $base_path, $url);
            if (file_exists($file)) {
                $url = add_query_arg('mt', filemtime($file), $url);
            }
        }

        return $url;
    }

    /*
     * return the URL to an asset within the template directories
     * @param string $section application section to load the template asset from
     * @param string $ref the reference to the asset
     * @return string the fully qualified URL to the requested asset
     */
    public static function get_template_asset($section, $ref)
    {
        $dir = plugin_dir_url(__FILE__) . 'templates/';
        if (NULL !== $section)
            $dir .= $section . '/';
        $dir = apply_filters('peepso_template_asset', $dir, $section);
        $ret = $dir . $ref;
        $ret = PeepSo::add_cachebust_arg($ret);
        return ($ret);
    }

    /*
     * Return the PeepSo working directory, adjusted for MultiSite installs
     * @return string PeepSo working directory
     */
    public static function get_peepso_dir()
    {
        static $peepso_dir;

        if (!isset($peepso_dir)) {
            // wp-content/peepso/users/{user_id}/
            //$peepso_dir = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'peepso';
            $peepso_dir = self::get_option('site_peepso_dir', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'peepso', TRUE);
            if (is_multisite())
                $peepso_dir .= '-' . get_current_blog_id();
            $peepso_dir .= DIRECTORY_SEPARATOR;
        }
        $peepso_dir = apply_filters('peepso_working_directory', $peepso_dir);
        return ($peepso_dir);
    }

    /*
     * Return the PeepSo working directory as a URL
     * @return string PeepSo working directory URL
     */
    public static function get_peepso_uri()
    {
        static $peepso_uri;

        if (!isset($peepso_uri)) {
            // Clean up Windows nonsense and potential double slashes
            $wp_content_dir = str_replace('\\','/', WP_CONTENT_DIR);
            $abs_path = str_replace('\\','/', ABSPATH);
            $peepso_dir = str_replace('\\','/', self::get_option('site_peepso_dir', WP_CONTENT_DIR . '/peepso', TRUE));

            $working_uri = str_replace(array($wp_content_dir, $abs_path), '', $peepso_dir);
            if (strpos($peepso_dir, $wp_content_dir) !== FALSE) {
                $peepso_uri = content_url() . '/' . $working_uri;
            } else {
                $peepso_uri = site_url() . '/' . $working_uri;
            }

            if (is_multisite()) {
                $peepso_uri .= '-' . get_current_blog_id();
            }

            $peepso_uri .= '/';
        }

        $peepso_uri = apply_filters('peepso_working_url', $peepso_uri);

        // Clean up Windows nonsense and potential double slashes
        $peepso_uri = str_replace('\\','/', $peepso_uri);
        $peepso_uri = str_replace(':/','://', $peepso_uri);
        $peepso_uri = str_replace('//','/', $peepso_uri);

        return ($peepso_uri);
    }

    /*
     * return the fully qualified directory for a specific user
     * @param int user id
     * @return string directory name
     */
    public static function get_userdir($user)
    {
        $ret = self::get_peepso_dir() . $user . '/';
        return ($ret);
    }

    public static function get_useruri($user)
    {
        $ret = self::get_peepso_uri() . $user . '/';
        return ($ret);
    }

// Auth

    /**
     * Perform our own authentication on login.
     * @param  mixed $user      null indicates no process has authenticated the user yet. A WP_Error object indicates another process has failed the authentication. A WP_User object indicates another process has authenticated the user.
     * @param  string $username The user's username.
     * @param  string $password The user's password (encrypted).
     * @return mixed            Either a WP_User object if authenticating the user or, if generating an error, a WP_Error object.
     */
    public function auth_signon($user, $username, $password)
    {
        if (!is_wp_error($user) && NULL !== $user) {
            $ban = $for_approval = FALSE;
            $PeepSoUser = PeepSoUser::get_instance($user->ID);
            $role = $PeepSoUser->get_user_role();
            $ban = ('ban' === $role);
            $for_approval = ('verified' === $role || 'register' === $role);

            if ($ban) {
                $ban_date = get_user_meta( $user->ID, 'peepso_ban_user_date', true );
                if(!empty($ban_date)) {
                    #$current_time = strtotime(current_time('Y-m-d H:i:s',1));
                    $current_time = time();
                    $suspense_expired = intval($ban_date) - $current_time;
                    if($suspense_expired > 0)
                    {
                        return (new WP_Error('account_suspended', sprintf(__('Your account has been suspended until %s.' , 'peepso-core'), date_i18n(get_option('date_format'), $ban_date) )));
                    }
                    else
                    {
                        // unset ban_date
                        // set user role to member
                        $PeepSoUser->set_user_role('member');
                        delete_user_meta($user->ID, 'peepso_ban_user_date');
                    }
                } else {
                    return (new WP_Error('account_suspended', __('Your account has been suspended indefinitely', 'peepso-core')));
                }
            }

            if ($for_approval && self::get_option('site_registration_enableverification', '0')) {
                return (new WP_Error('pending_approval', __('Your account is awaiting admin approval.', 'peepso-core')));
            }

            if ('register' === $role) {
                return (new WP_Error('pending_approval', __('Please verify the email address you have provided using the link in the email that was sent to you.', 'peepso-core')));
            }
        }

        /*
        @todo commented out due to #304 -  "PeepSo login hook breaks WP mobile app login"

        // check referer to ensure login came from installed domain
        if (!isset($_SERVER['HTTP_REFERER']))
            return (new WP_Error('nonwebsite_login', __('Must login from web site', 'peepso-core')));

        $ref_domain = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        $our_domain = parse_url(get_bloginfo('wpurl'), PHP_URL_HOST);
        if ($ref_domain !== $our_domain)
            return (new WP_Error('nonwebsite_login', __('Must login from web site', 'peepso-core')));
        */
        return ($user);
    }

    /**
     * Checks peepso roles whether to allow a password to be reset.
     * @param bool $allow Whether to allow the password to be reset. Default true.
     * @param int  $user_id The ID of the user attempting to reset a password.
     * @return mixed TRUE if password reset is allowed, WP_Error if not
     */
    public function allow_password_reset($allow, $user_id)
    {
        $role = self::_get_role();

        $ban = $for_approval = FALSE;

        $ban = ('ban' === $role);
        $for_approval = in_array($role, array('register', 'verified'));

        // end process and display success message
        if ($ban || ($for_approval && PeepSo::get_option('site_registration_enableverification', '0')))
            $allow = new WP_Error('user_login_blocked', __('This user may not login at the moment.', 'peepso-core'));

        return ($allow);
    }

// HTML, widget, linking utils

    public function body_class_filter($classes)
    {
        $classes[]='plg-peepso';
        return $classes;
    }

    /*
    * Clean up default HTML output for integrated widgets
    */
    public function peepso_widget_args_internal( $args )
    {
        $args['before_widget']  = str_replace('widget ','', $args['before_widget']);
        $args['after_widget']   = '</div>';
        $args['before_title']   = str_replace('widgettitle','', $args['before_title']);
        $args['after_title']    = '</h2>';

        return $args;
    }

    /*
    * Adjust widget instance
    */
    public function peepso_widget_instance( $instance )
    {
        if (isset($instance['is_profile_widget'])) {
            $instance['class_suffix'] ='';
        } else {
            $instance['class_suffix'] ='--external';
        }

        return $instance;
    }

    /*
     * Hide "load more" link for guests
     */
    public function peepso_activity_more_posts_link( $link )
    {
        if (!get_current_user_id()) {
            $link = '';
        }

        return $link;
    }

    public function peepso_activity_remove_shortcode( $content )
    {
        foreach($this->shortcodes as $shortcode=>$class) {
            foreach($this->shortcodes as $shortcode=>$class) {
                $from = array('['.$shortcode.']','['.$shortcode);
                $to = array('&#91;'.$shortcode.'&#93;', '&#91;'.$shortcode);
                $content = str_ireplace($from, $to, $content);
            }
        }
        return $content;
    }

    /*
     * Add links to the profile widget community section
     */
    public function peepso_widget_me_community_links($links)
    {
        $links[0][] = array(
            'href' => PeepSo::get_page('activity'),
            'title' => __('Activity', 'peepso-core'),
            'icon' => 'gcis gci-home',
        );

        $links[1][] = array(
            'href' => PeepSo::get_page('members'),
            'title' => __('Members', 'peepso-core'),
            'icon' => 'gcis gci-user-friends',
        );

        ksort($links);
        return $links;
    }

    /*
     * Add links to the profile segment submenu
     */
    public function filter_peepso_navigation_profile($links)
    {
        // #5989 let admin decide "about" to be first
//        if(PeepSo::is_dev_mode('profile _about_home')) {
//            $links['stream'] = array(
//                'href' => 'stream',
//                'label' => __('Stream', 'peepso-core'),
//                'icon' => 'gcis gci-home'
//            );
//
//            $links['about'] = array(
//                'label'=> __('About', 'peepso-core'),
//                'href' => '',
//                'icon' => 'gcis gci-user-circle'
//            );
//        }

        $links['stream'] = array(
            'href' => '',
            'label' => __('Stream', 'peepso-core'),
            'icon' => 'gcis gci-home'
        );

        $links['about'] = array(
            'label'=> __('About', 'peepso-core'),
            'href' => 'about',
            'icon' => 'gcis gci-user-circle'
        );


        $links['followers'] = array(
            'label'=> __('Followers', 'peepso-core'),
            'href' => 'followers',
            'icon' => 'gcis gci-user-check'
        );

        return $links;
    }

    // The last pass over this filter removes the _user_id  INT to avod notices and other issues
    public function filter_peepso_navigation_profile_final($links)
    {
        $order = ['stream','about','blogposts','followers','friends','groups','photos','media','videos','audio'];

        $order = apply_filters('peepso_filter_navigation_profile_order', $order);

        $order_override = PeepSo::get_option_new('profile_navigation_order');
        if(PeepSo3_Utilities_String::maybe_strlen($order_override)) {
            if(stristr($order_override,"\n")) {
                $order_override = explode("\n", $order_override);
            } else {
                $order_override = [$order_override];
            }

            $order = array_merge($order_override, $order);
        }

        $ordered_links = array();

        foreach($order as $id) {
            $id=strtolower(trim($id));

            if(isset($links[$id])) {
                $ordered_links[$id] = $links[$id];
                unset($links[$id]);
            }
        }

        $ordered_links = array_merge($ordered_links, $links);

        if(isset($ordered_links['_user_id'])) {
            unset($ordered_links['_user_id']);
        }

        foreach($ordered_links as $id=>$link) {
            if(!isset($link['icon']) || !strlen(trim($link['icon']))) {
                $ordered_links[$id]['icon'] = 'gcis gci-newspaper';
            }
        }



        // Make sure the first tab URL is empty in case stream is not first
        if(isset($ordered_links['stream'])) {
            $ordered_links['stream']['href'] = 'stream';
        }

        $ordered_links[array_key_first($ordered_links)]['href']='';

        return $ordered_links;
    }


// Versoning

    /**
     * Used to check PeepSo version-locked plugin compatibility
     * For third party PEEPSO_VER_MIN and PEEPSO_VER_MAX checks, use check_version_minmax
     * @param $version
     * @param null $release
     * @param null $version_compare
     * @param null $release_compare
     * @return array
     */
    public static function check_version_compat($version, $release = '', $version_compare = '', $release_compare = '')
    {
        // @Since 3.0.0.0 the last part of the number can be different

        $full_peepso_version = $peepso_version = self::PLUGIN_VERSION;
        $full_plugin_version = $version;

        $plugin_version = explode('.', $version);
        $peepso_version = explode('.', $peepso_version);


        if(count($plugin_version) == 4) {
            array_pop($plugin_version);
        }

        if(count($peepso_version) == 4) {
            array_pop($peepso_version);
        }

        $plugin_version = implode('.', $plugin_version);
        $peepso_version = implode('.', $peepso_version);

        // @Since 3.0.0.0 EOF

        $version_compare = (strlen($version_compare)) ? $version_compare : $peepso_version;
        $release_compare = (strlen($release_compare)) ? $release_compare : self::PLUGIN_RELEASE;

        // initial success array
        $response = array(
            'ver_core' => $full_peepso_version,
            'rel_core' => $release_compare,
            'ver_self' => $full_plugin_version,
            'rel_self' => $release,
            'exact_match' => ($full_peepso_version == $full_plugin_version),
            'compat'   =>  1, // 1 - OK, 0 - ERROR, -1 - WARNING
            'part'     => '',
        );

        // if the strings are the same check the "release/build" (alpha, beta etc)
//        if ( $plugin_version == $version_compare && $release != $release_compare ) {
//            $response['compat'] = -1;
//        }

        if ($plugin_version != $version_compare){
            $response['compat'] = 0;
        }

        return $response;
    }

    public static function third_party_warning($name, $url, $url_class, $ver_min, $peepso_name, $extra ='') {
        $data = compact('name', 'url', 'url_class', 'ver_min','peepso_name','extra');
        echo PeepSoTemplate::exec_template('admin', 'third-party-warning', $data);
    }
    /**
     * Check if PeepSo is not older than the minimum required version
     * Check if PeepSo is not newer than the maximum tested version
     * Return values:
     *  1 == OKAY (PeepSo is well in the min-max region)
     *  0 == FAIL (PeepSo is older than minimum required version)
     * -1 == WARN (PeepSo is newer than the max tested version)
     * @param $peepso_ver_min
     * @param $peepso_ver_max
     * @return int
     */
    public static function check_version_minmax($version, $peepso_min, $peepso_max)
    {
        /*
         * version_compare(X,Y)
         * -1 X <  Y
         *  0 X == Y
         *  1 X >  Y
         */

        $result = array(
            'ver_core' 	=> self::PLUGIN_VERSION,
            'ver_self'	=> $version,
            'ver_min'	=> $peepso_min,
            'ver_max'	=> $peepso_max,
            'compat'	=> 1
        );

        // "maximum tested" failure is not fatal
        // PeepSo <= ver_max (-1,0)
        if( 1== version_compare(self::PLUGIN_VERSION, $peepso_max)) {
            $result['compat'] = -1;
        }

        // "minimum required" overrides if needed
        // PeepSo >= ver_min (1,0)
        if( -1==version_compare(self::PLUGIN_VERSION, $peepso_min) ) {
            $result['compat'] = 0;
        }

        return $result;
    }

    public static function get_version_parts($version)
    {
        $version = explode('.', $version);

        if (is_array($version) && 3 == count($version)) {
            foreach($version as $sub) {
                if (!is_numeric($sub)) {
                    return false;
                }
            }

            return array(
                'major' => $version[0],
                'minor' => $version[1],
                'bugfix' => $version[2],
            );
        }

        return false;
    }

// Admin notices & alerts

// @todo HTML rendering methods should probably be refactored

    /**
     * Show message if peepsofriends can not be installed or run
     */
    public static function license_notice($plugin_name, $plugin_slug, $forced=FALSE)
    {
        $style="";
        if (isset($_GET['page']) && 'peepso_config' == $_GET['page'] && !isset($_GET['tab'])) {

            if (!$forced) {
                return;
            }

            $style="display:none";
        }

        $license_data = PeepSoLicense::get_license($plugin_slug);

        // try to fix license with ultimate bundle key
        switch ($license_data['response']) {
            case 'site_inactive':
            case 'expired':
                break;
            case 'invalid':
            case 'inactive':
            case 'item_name_mismatch':
            default:
                if(PeepSo3_Helper_Addons::get_license()) {
                    $settings = PeepSoConfigSettings::get_instance();
                    $settings->set_option('site_license_' . $plugin_slug, PeepSo3_Helper_Addons::get_license());
                }
                break;
        }

        PeepSoLicense::activate_license($plugin_slug,$plugin_name);

        $license_data = PeepSoLicense::get_license($plugin_slug);

        switch ($license_data['response']) {
            case 'site_inactive':
                $message = 'This domain is not registered for PLUGIN_NAME. You can register your domain <a target="_blank" href="PEEPSOCOM_LICENSES">here</a>.';
                break;
            case 'expired':
                $message = 'License for PLUGIN_NAME has expired. The plugin will not receive updates and will stop working  three months after expiration. You can get a new license <a target="_blank" href="https://www.PeepSo.com/pricing">here</a>.';
                break;
            case 'invalid':
            case 'inactive':
            case 'item_name_mismatch':
            default:
                $message = 'License for PLUGIN_NAME is missing or invalid. Please enter a valid license and click "SAVE" to activate it. You can get your license key <a target="_blank" href="PEEPSOCOM_LICENSES">here</a>.';
                break;
        }

        #var_dump($license_data);
        $from = array(
            'PLUGIN_NAME',
            'ENTER_LICENSE',
            'PEEPSOCOM_LICENSES',
        );

        $to = array(
            $plugin_name,
            'admin.php?page=peepso_config#field_bundle:parent',
            self::PEEPSOCOM_LICENSES,
        );

        $message = str_ireplace( $from, $to, $message );
        #var_dump($message);

        // set cache for state of the license
        PeepSo3_Mayfly::set('peepso_has_displayed_license_warning', 1, HOUR_IN_SECONDS);

        if($forced) {
            echo '<div class="error peepso" id="error_' . $plugin_slug . '" style="' . $style . '">';
            echo '<strong>', $message, '</strong>';
            echo '</div>';
        } else {
            global $peepso_has_displayed_license_warning;
            $peepso_has_displayed_license_warning = isset($peepso_has_displayed_license_warning) ? $peepso_has_displayed_license_warning : FALSE;

            if (!$peepso_has_displayed_license_warning) {
                $peepso_has_displayed_license_warning = TRUE;

                $message = __('PeepSo is having issues validating your license. <a href="ENTER_LICENSE">Review your PeepSo license keys</a>.','peepso-core');
                $message = str_ireplace( $from, $to, $message );


                echo '<div class="error peepso" id="peepso_license_error_combined">';
                echo $message;
                echo '</div>';
            }
        }
    }

    public static function mailqueue_notice()
    {
        wp_schedule_event(current_time('timestamp'), 'five_minutes', PeepSo::CRON_MAILQUEUE);
        echo '<div class="error peepso">' .
            sprintf(__('It looks like %s were not processing properly. We just tried to fix it automatically.<br><small>If you see this message repeatedly, there might be something wrong with your WordPress Cron. Consider deactivating and re-activating PeepSo or contacting Support.</small>', 'peepso-core'),__('PeepSo emails', 'peepso-core'))
            .'</strong></div>';
    }

    public static function maintenance_notice()
    {
        wp_schedule_event(current_time('timestamp'), 'five_minutes', PeepSo::CRON_MAINTENANCE_EVENT);
        echo '<div class="error peepso">' .
            sprintf(__('It looks like %s were not processing properly. We just tried to fix it automatically.<br><small>If you see this message repeatedly, there might be something wrong with your WordPress Cron. Consider deactivating and re-activating PeepSo or contacting Support.</small>', 'peepso-core'),__('PeepSo Maintenance Scripts', 'peepso-core'))
            .'</strong></div>';
    }

    public static function gdpr_external_cron_notice()
    {
        wp_schedule_event(current_time('timestamp'), 'five_minutes', PeepSo::CRON_GDPR_EXPORT_DATA);
        echo '<div class="error peepso">' .
            sprintf(__('It looks like %s were not processing properly. We just tried to fix it automatically.<br><small>If you see this message repeatedly, there might be something wrong with your WordPress Cron. Consider deactivating and re-activating PeepSo or contacting Support.</small>', 'peepso-core'),__('PeepSo GDPR Scripts', 'peepso-core'))
            .'</strong></div>';
    }

    public static function plugins_version_notice()
    {
        $plugins = PeepSo3_Mayfly::get('peepso_plugins_version_notice','');
        $combined_version_lock = array();
        if(is_array($plugins) && count($plugins)) {
            foreach($plugins as $plugin) {
                $version_lock = TRUE;

                if(!isset($plugin->version_check['rel_core'])) {
                    $version_lock = FALSE;
                }

                if($version_lock) {
                    $combined_version_lock[]=$plugin;
                } else {
                    self::version_notice($plugin->name, $plugin->name, $plugin->version_check, FALSE);
                }
            }
        }

        if(count($combined_version_lock)) {
            self::version_notice_combined($combined_version_lock);
        }
    }


    public static function version_notice_combined($plugins) {
        ?>
        <div class="error peepso">

            <strong><?php
                $foundation_version = PeepSo::PLUGIN_VERSION;

                if(strlen(PeepSo::PLUGIN_RELEASE)) {
                    $foundation_version.=' ('.PeepSo::PLUGIN_RELEASE.')';
                }

                echo sprintf(__('The following PeepSo add-on plugins are incompatible with PeepSo Foundation %s. Please update PeepSo Foundation and the add-on plugins to avoid conflicts and issues.','peepso_core'), $foundation_version);?></strong>

            <?php
            $prev_cat = '';

            foreach($plugins as $plugin) {

                if (strlen($plugin->version_check['ver_self']) && strlen($plugin->version_check['rel_self'])) { $plugin->version_check['ver_self'] .= "-" . $plugin->version_check['rel_self']; }
                $cat = explode(':', $plugin->name);
                $cat = $cat[0];

                if($cat!=$prev_cat) {
                    echo "<br/><strong>$cat:</strong>";
                } else {
                    echo ', ';
                }
                ?>

                <?php echo str_replace(array($cat,':'),'',$plugin->name);?> <small style="opacity:0.5">(<?php echo $plugin->version_check['ver_self']; ?>)</small><?php

                $prev_cat = $cat;
            } ?>
        </div>
        <?php
    }

    public static function version_notice($plugin_name, $plugin_slug, $version_check, $legacy = TRUE)
    {
        // releases (beta, alpha etc) are only considered in the version-lock scenario
        $version_lock = TRUE;
        if(!isset($version_check['rel_core'])) {
            $version_lock = FALSE;
        }

        if( $version_lock ) {
            if (strlen($version_check['rel_core'])) {
                $version_check['ver_core'] .= "-" . $version_check['rel_core'];
            }

            if (strlen($version_check['ver_self']) && strlen($version_check['rel_self'])) {
                $version_check['ver_self'] .= "-" . $version_check['rel_self'];
            }
        }


        ?>
        <div class="error peepso">
            <?php

            // PeepSo Plugin X.Y.Z
            printf('<strong>PeepSo %s %s</strong> ',$plugin_name, $version_check['ver_self']);

            if($version_lock) {
                printf(__('is not fully compatible with <strong>PeepSo %s</strong>. ', 'peepso-core'), $version_check['ver_core']);
            }else {
                if ( -1 == $version_check['compat'] ) {
                    // was only tested up to PeepSo X.Y.Z
                    printf(
                        __('was only tested up to <strong>PeepSo %s</strong>. ', 'peepso-core'), $version_check['ver_max']);
                } else {
                    // requires PeepSo X.Y.Z
                    printf(__('has been disabled because it requires <strong>PeepSo %s</strong>. ', 'peepso-core'), $version_check['ver_min']);
                }

                printf(__('You are running PeepSo %s.', 'peepso-core'), $version_check['ver_core']);
            }



            if($version_lock) {
                // Please upgrade
                printf(__('Please upgrade PeepSo %s and PeepSo. ', 'peepso-core'), $plugin_name);

                // Upgrade link
                printf(' <a href="%s" target="_blank" style="float:right">%s</a>', self::PEEPSOCOM_LICENSES, __('Upgrade now!', 'peepso-core'));
            }
            ?>
        </div>
        <?php
    }

    public function email_notif_user_profile_fields($user)
    {
        ?>
        <h3><?php echo __('PeepSo Email Notifications', 'peepso-core');?></h3>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><?php echo __('Email preferences', 'peepso-core'); ?></th>
                <td id="ps-js-unsub-email">
                    <label for="peepso_unsub_email_notification"><button name="peepso_unsub_email_notification" id="peepso_unsub_email_notification" class="button"><?php echo __('Unsubscribe this user from all email notifications', 'peepso-core');?></button></label>
                    <input type="hidden" name="peepso_unsub_user_id" value="<?php echo $user->ID ?>">
                    <input type="hidden" name="peepso_unsub_nonce" value="<?php echo wp_create_nonce('peepso-user-unsubscribe-emails');?>" />
                    <span class="ps-loading ps-js-loading" style="display: none"><img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif');?>"><i class="gcis gci-check" style="color:green;display:none"></i></span>
                    <div id="ps-js-unsub-message"></div>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
    }

// Debug & utils

    /*
     * Issue #241
     * Adjust WP_Query flags to disable comments rendering under pages
     * Attempt re-init() of WP_Query where %postname% permalink structure might interfere with our routing
     *
     * @todo might yield UNFORESEEN CONSEQUENCES
     * 2-4-1 = -3
     * Half Life 3 confirmed
     */
    public static function reset_query()
    {
        // header("HTTP/1.1 200 OK");

        //return; // #637 resetting query not compatible with SEO & antispam plugins

        // Forced query reset #2195
        if(1 == PeepSo::get_option('force_reset_query',0)) {
            global $wp_query;
            wp_reset_query();

            // Special case for %postname%
            $permalink = get_option('permalink_structure');

            if (stristr($permalink, '%postname%')) {
                $wp_query->init();
                $wp_query->is_single = FALSE;
                $wp_query->is_page = FALSE;
                $wp_query->is_404 = FALSE;
            }
        }
    }

    /*
     * Adds needed intervals
     * @param array $schedules
     * @return array $schedules
    */
    public static function filter_cron_schedules($schedules)
    {
        // adds an interval called 'one_minute' to cron schedules
        $schedules['one_minute'] = array(
            'interval' => 60,
            'display' => __('Every One Minute', 'peepso-core')
        );

        // adds an interval called 'five_minutes' to cron schedules
        $schedules['five_minutes'] = array(
            'interval' => 300,
            'display' => __('Every Five Minutes', 'peepso-core')
        );

        // Adds once weekly to the existing schedules.
        $schedules['weekly'] = array(
            'interval' => 604800,
            'display' => __('Once Weekly', 'peepso-core')
        );

        return ($schedules);
    }

    private static $log_to_console = FALSE;
    public static function log_to_console()
    {
        self::$log_to_console = TRUE;
    }

    /**
     * Add access types hook required for PeepSoPMPro plugin
     * @param array $types existing access types
     * @return array $types new access types
     */
    public function filter_access_types($types)
    {
        $types['peepso_activity'] = array(
            'name' => __('Activity Stream', 'peepso-core'),
            'module' => PeepSoActivity::MODULE_ID,
        );

        $types['peepso_members'] = array(
            'name' => __('Search', 'peepso-core'),
            'module' => self::MODULE_ID,
        );

        $types['peepso_profile'] = array(
            'name' => __('Profile Pages', 'peepso-core'),
            'module' => self::MODULE_ID,
        );

        return ($types);
    }

    public function peepso_filter_opengraph($tags, $activity)
    {
        return $tags;
    }

    public function peepso_filter_format_opengraph($tags, $parent_key = '')
    {
        $output = '';

        foreach($tags as $key => $val) {
            if (is_array($val))
            {
                $output .= apply_filters('peepso_filter_format_opengraph', $val, $key);
            }
            else
            {
                $key = !empty($parent_key) ? $parent_key : esc_attr($key);
                $val = esc_attr($val);

                $output .= "<meta property=\"og:$key\" content=\"$val\" />\n";
            }
        }

        return $output;
    }


    /**
     * Filters the WP_User_Query, add FROM and WHERE clause for join into peepso_users table
     * @param WP_User_query $query The query object to filter
     * @return WP_User_Query The modified query object
     */
    function filter_user_roles(WP_User_Query $user_query)
    {
        global $wpdb;

        if (isset($user_query->query_vars['peepso_roles'])){
            if (is_array($user_query->query_vars['peepso_roles']))
            {
                $roles = "'" . implode("', '", $user_query->query_vars['peepso_roles']) . "'";
            } else
            {
                $roles = "'" . $user_query->query_vars['peepso_roles'] . "'";
            }
            $user_query->query_from .= " LEFT JOIN `{$wpdb->prefix}" . PeepSoUser::TABLE . "` ON `{$wpdb->users}`.`ID` = `{$wpdb->prefix}" . PeepSoUser::TABLE . "`.`usr_id` ";
            $user_query->query_where .= " AND `{$wpdb->prefix}" . PeepSoUser::TABLE . "`.`usr_role` IN ($roles)";

            return $user_query;
        }
    }

    public static function redirect($url)
    {
        #if (is_user_logged_in()) {

        if(!headers_sent()) {
            nocache_headers();
            wp_redirect($url);
            die();
        }

        echo '<script>window.location.replace("'.$url.'");</script>';
        die();
    }

    public function init_mysql_big_size() {
        global $wpdb;
        $wpdb->query('SET SQL_BIG_SELECTS=1');
    }


// MarkDown
    public static function do_parsedown($content)
    {
        $content = '<div class="peepso-markdown">' . html_entity_decode($content) .' </div>';
        return $content;
    }
// Blogposts

    /**
     * Disable "friends" and "only me" privacy
     *
     * @param array $actions The default options per post
     * @return  array
     */
    public function blogposts_filter_privacy_access_levels($levels) {

        global $post;

        if($post instanceof WP_Post && stristr($post->post_content, self::BLOGPOSTS_SHORTCODE)) {
            unset($levels[30]);
            unset($levels[40]);
        }

        return $levels;
    }

    /**
     * modify onclick handler delete post for album type post
     * @param array $options
     * @return array $options
     */
    public function blogposts_post_filters($options) {
        $post = $options['post'];
        $options = $options['acts'];

        if (self::BLOGPOSTS_MODULE_ID == intval($post->act_module_id)) {

            // disable "edit"
            if (isset($options['edit'])) {
                unset($options['edit']);
            }

            // disable "move"
            if (isset($options['move'])) {
                unset($options['move']);
            }

            // show warning before deleting a blog post
            if (isset($options['delete'])) {
                $options['delete']['click'] = 'return peepso.blogposts.deletePost(' . $post->ID . ');';
            }
        }


        return $options;
    }

    public function blogposts_rebuild_cache()
    {
        // Directory where CSS files are stored
        $path = PeepSo::get_peepso_dir().'plugins'.DIRECTORY_SEPARATOR.'foundation'.DIRECTORY_SEPARATOR;

        if (!file_exists($path) ) {
            @mkdir($path, 0755, TRUE);
        }

        // Try to remove the old file
        $old_file = $path.'blogposts-'.PeepSo3_Mayfly::get('peepso_blogposts_css').'.css';
        @unlink($old_file);

        // New cache
        delete_option('peepso_blogposts_css');
        PeepSo3_Mayfly::set('peepso_blogposts_css', time());

        $image_height = intval(PeepSo::get_option('blogposts_profile_featured_image_height', 150));
        $box_height = intval(PeepSo::get_option('blogposts_profile_two_column_height', 350));

        if($image_height < 1) {
            $image_height = 1;
        }

        if($box_height < 1 || !PeepSo::get_option('blogposts_profile_two_column_enable', 1)) {
            $box_height = 'auto';
        }

        // @todo cache this
        ob_start();
        ?>
        .ps-blogposts__post-image {
        height: <?php echo $image_height;?>px;
        }

        .ps-blogposts__post-image--left,
        .ps-blogposts__post-image--right {
        width: <?php echo $image_height;?>px;
        }

        .ps-blogposts__post {
        height: <?php echo $box_height;?>px;
        }
        <?php
        $css = ob_get_clean();

        update_option('peepso_blogposts_css', $css);



        $file = $path.'blogposts-'.PeepSo3_Mayfly::get('peepso_blogposts_css').'.css';
        $h = fopen( $file, "a" );
        fputs( $h, $css );
        fclose( $h );
    }

    private function blogposts_comment_count($post_id)
    {
        $count = PeepSo3_Mayfly::get($mayfly = 'peepso_blogposts_comments_'.$post_id);
        if(!is_null($count)) { return $count; }

        global $wpdb;

        $r = $wpdb->get_row("SELECT `act_id`, `act_external_id`, `act_module_id` FROM ".$wpdb->prefix.PeepSoActivity::TABLE_NAME."  WHERE `act_module_id`=".$this::BLOGPOSTS_MODULE_ID." AND `act_external_id`=".get_post_meta($post_id, self::BLOGPOSTS_SHORTCODE, TRUE));

        $act_external_id = $r->act_external_id;
        $act_module_id = $r->act_module_id;

        // Comments attached to the main post
        $q = "SELECT act_external_id, act_module_id from " . $wpdb->prefix . PeepSoActivity::TABLE_NAME
            . " WHERE act_comment_object_id=$act_external_id "
            ." AND act_comment_module_id=$act_module_id";

        $r = $wpdb->get_results($q);

        $count = 0;
        if(count($r)) {
            foreach($r as $comment) {
                $count++;

                $act_external_id = $comment->act_external_id;
                $act_module_id = $comment->act_module_id;

                // Comments attached to the main post
                $q = "SELECT count(act_id) as subcomments from " . $wpdb->prefix . PeepSoActivity::TABLE_NAME
                    . " WHERE act_comment_object_id=$act_external_id "
                    ." AND act_comment_module_id=$act_module_id";

                $count += $wpdb->get_row($q)->subcomments;
            }
        }

        // cache it for post_id
        PeepSo3_Mayfly::set($mayfly, $count, 15);
        return $count;
    }

    public function blogposts_filter_the_content_blogpost_authorbox($content)
    {
        if(! in_the_loop()  )   { return $content; }
        if(! is_singular()  )   { return $content; }
        if(! is_single()    )   { return $content; }
        if(! is_main_query())   { return $content; }
        if(  is_embed()     )   { return $content; }

        global $post;
        if($post->post_type != 'post') { return $content; }

        return $content . PeepSoTemplate::exec_template('blogposts','author_box', array('author' => PeepSoUser::get_instance($post->post_author)), TRUE);
    }

    public function blogposts_filter_the_content_blogpost()
    {
        if(! is_single()) { return; }


        global $post;
        global $wpdb;

        if(!PeepSoBlogPosts::enabled_for_post_categories($post->ID)) { return; }

        $peepso_actions ='';
        $peepso_comments = '';
        $peepso_wrapper = '';

        // This step REQUIRES an existing activity entry
        if(FALSE !== $this->blogposts_publish_post($post->ID, $post)) {

            // completely disable and hide native WP comments
            remove_post_type_support('post', 'comments');

            add_filter('comments_array', function () { return array(); });
            add_filter('comments_open', function () { return FALSE; });
            add_filter('pings_open', function () { return FALSE; });

            // $act_external_id - ID of post representing the stream activity
            $act_external_id = get_post_meta($post->ID, self::BLOGPOSTS_SHORTCODE, TRUE);

            if($act_external_id==0 || $act_external_id==1 ||  !is_numeric($act_external_id)) {

                // extract act_id from wp_posts by searching for the serialized data
                $search = '{"post_id":'.$post->ID.',';

                $q = "SELECT ID FROM {$wpdb->prefix}posts WHERE `post_content` LIKE '%$search%'";
                $r = $wpdb->get_row($q);

                $act_external_id = (int) $r->ID;

                // update postmeta with new value so we don't have to search again
                update_post_meta($post->ID, self::BLOGPOSTS_SHORTCODE, $act_external_id);
            }

            // don't modify content in the embed
            if(is_embed()) { return; }

            // stash the original post object
            $post_old = $post;

            // post object representing the stream item
            $post = get_post($act_external_id);

            // if post can't be found
            // probably it was deleted and there is orphan data in peepso_activities and postmeta
            if(!$post) {
                ob_start();
                echo ' <br/><br/> '.__('Can\'t load comments and likes. Try refreshing the page or contact the Administrators.','peepso-core');

                $wpdb->delete($wpdb->prefix.'postmeta', array('meta_value'=>$act_external_id, 'meta_key'=>self::BLOGPOSTS_SHORTCODE));
                $wpdb->delete($wpdb->prefix.PeepSoActivity::TABLE_NAME, array('act_external_id'=>$act_external_id, 'act_module_id'=>self::BLOGPOSTS_MODULE_ID));

                return ob_get_clean();
            }

            $PeepSoActivity = new PeepSoActivity();

            // act_id - id of the item in peepso_activities representing the stream item
            $r = $wpdb->get_row("SELECT act_id FROM ".$wpdb->prefix.$PeepSoActivity::TABLE_NAME." WHERE act_module_id=".$this::BLOGPOSTS_MODULE_ID." and act_external_id=$act_external_id");
            $act_id = $r->act_id;

            $post->act_id = $act_id;
            $post->act_module_id = self::BLOGPOSTS_MODULE_ID;
            $post->act_external_id = $act_external_id;

            // PEEPSO WRAPPER
            ob_start();

            $data = array(
                'header'            => PeepSo::get_option('blogposts_comments_header_call_to_action'),
                'header_comments'   => PeepSo::get_option('blogposts_comments_header_comments'),
                'header_actions'    => PeepSo::get_option('blogposts_comments_header_reactions'),
                'no_cover'        => PeepSo::get_option('blogposts_comments_no_cover' ,0),
            );



            if(is_user_logged_in()) {
                PeepSoTemplate::exec_template('blogposts','peepso_wrapper', $data);
            } else {
                PeepSoTemplate::exec_template('blogposts','peepso_wrapper_guest', $data);
            }
            $peepso_wrapper = '<div id="peepso-wrap">' . ob_get_clean() . '</div>';

            // POST ACTIONS
            ob_start();
            add_action('peepso_activity_post_actions', function( $args ){ return array('post'=>$args['post'],'acts'=>array('like'=>$args['acts']['like']));}, 20);
            ?>
            <div class="ps-post__actions ps-stream-actions stream-actions" data-type="stream-action"><?php $PeepSoActivity->post_actions(); ?></div>

            <?php

            do_action('peepso_post_before_comments');
            $peepso_actions = ob_get_clean();

            // POST COMMENTS
            ob_start();
            $PeepSoActivity->show_recent_comments();
            $comments = ob_get_clean();

            ob_start();

            $show_commentsbox = apply_filters('peepso_commentsbox_display', apply_filters('peepso_permissions_comment_create', is_user_logged_in()), $post->ID);

            // show "no comments yet" only if the user can't make a new one
            if(!strlen($comments) && !$show_commentsbox) {
                ?>
                <div class="ps-no-comments-container--<?php echo $act_id; ?>">
                    <?php echo __('No comments yet', 'peepso-core');?>
                </div>
                <?php
            }

            ?>
            <div class="ps-comments--blogpost ps-comment-container ps-js-comment-container ps-js-comment-container--<?php echo $act_id; ?>" data-act-id="<?php echo $act_id; ?>">
                <?php echo $comments;  ?>
            </div>
            <?php
            $peepso_post_id = get_post_meta($post_old->ID, 'peepso_postnotify', TRUE);
            $disable_comments = empty(get_post_meta($peepso_post_id, 'peepso_disable_comments', TRUE)) ? FALSE : TRUE;

            if (is_user_logged_in() && $show_commentsbox) {
                if (!$disable_comments) {
                    $PeepSoUser = PeepSoUser::get_instance();
                    ?>

                    <div id="act-new-comment-<?php echo $act_id; ?>" class="ps-comments__reply ps-comments__reply--blogposts ps-comment-reply cstream-form stream-form wallform ps-js-newcomment-<?php echo $act_id; ?> ps-js-comment-new" data-type="stream-newcomment" data-formblock="true">
                        <a class="ps-avatar cstream-avatar cstream-author" href="<?php echo $PeepSoUser->get_profileurl(); ?>">
                            <img data-author="<?php echo $post->post_author; ?>" src="<?php echo $PeepSoUser->get_avatar(); ?>" alt="" />
                        </a>
                        <div class="ps-comments__input-wrapper ps-textarea-wrapper cstream-form-input">
				<textarea
                        data-act-id="<?php echo $act_id;?>"
                        class="ps-comments__input ps-textarea cstream-form-text"
                        name="comment"
                        oninput="return activity.on_commentbox_change(this);"
                        onfocus="activity.on_commentbox_focus(this);"
                        onblur="activity.on_commentbox_blur(this);"
                        placeholder="<?php echo __('Write a comment...', 'peepso-core');?>"></textarea>
                            <?php
                            // call function to add button addons for comments
                            $PeepSoActivity->show_commentsbox_addons();
                            ?>
                        </div>
                        <div class="ps-comments__reply-send ps-comment-send cstream-form-submit" style="display:none;">
                            <div class="ps-comment-loading" style="display:none;">
                                <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
                                <div> </div>
                            </div>
                            <div class="ps-comments__reply-actions ps-comment-actions" style="display:none;">
                                <button onclick="return activity.comment_cancel(<?php echo $act_id; ?>);" class="ps-btn ps-button-cancel"><?php echo __('Clear', 'peepso-core'); ?></button>
                                <button onclick="return activity.comment_save(<?php echo $act_id; ?>, this);" class="ps-btn ps-btn--action ps-btn-primary ps-button-action" disabled><?php echo __('Post', 'peepso-core'); ?></button>
                            </div>
                        </div>
                    </div>

                <?php } else { ?>
                    <div class="ps-comments__closed ps-js-comments-closed">
                        <i class="fas fa-lock"></i> <?php echo __('Comments are closed', 'peepso-core');?>
                    </div>
                    <?php
                }
            }
            if (strlen($reason = apply_filters('peepso_permissions_comment_create_denied_reason', ''))) {
                echo '<div class="ps-alert ps-alert-warning">' . $reason . '</div>';
            }

            PeepSoTemplate::exec_template('activity', 'dialogs');

            $peepso_comments = ob_get_clean();

            // restore original post object
            $post = $post_old;
        }

        $from = array(
            '<!--{peepso_comments}-->',
            '<!--{peepso_actions}-->',
        );

        $to = array(
            $peepso_comments,
            $peepso_actions,
        );

        echo str_ireplace($from, $to, $peepso_wrapper);
    }

    /* * * NAVIGATION & PROFILE SEGMENT * * */

    /**
     * create a menu item in the PeepSo profile segments menu
     *
     * @param $links
     * @return mixed
     */
    public function blogposts_filter_peepso_navigation_profile($links)
    {
        $user_id = isset($links['_user_id']) ? $links['_user_id'] : get_current_user_id();

        // "Hide when empty" handling if profile has no blog posts
        if(PeepSo::get_option('blogposts_profile_hideempty', 0) && !count_user_posts($user_id)) {

            // Detect if User Submissions are enabled
            $fs_usp = PeepSo::usp_enabled() && PeepSo::get_option('blogposts_submissions_enable_usp');
            $fs_cmd = class_exists('CMUserSubmittedPosts') && PeepSo::get_option('blogposts_submissions_enable');
            $fs = $fs_usp || $fs_cmd;

            // Return if the viewer is not profile owner or Frontend Submission is disabled
            if($user_id != get_current_user_id() || !$fs) {
                return $links;
            }
        }

        $links['blogposts'] = array(
            'href' => 'blogposts',
            'label'=> __('Blog', 'peepso-core'),
            'icon' => 'gcis gci-quote-right'
        );

        return $links;
    }

    /**
     * render the Blogposts profile segment
     *
     * @return void
     */
    public function blogposts_peepso_profile_segment_blogposts()
    {
        // Get the currently viewed User ID from PeepSoProfileShortcode and exec template
        $pro = PeepSoProfileShortcode::get_instance();
        $this->view_user_id = PeepSoUrlSegments::get_view_id($pro->get_view_user_id());

        if($this->view_user_id == get_current_user_id()) {
            $PeepSoUser = PeepSoUser::get_instance();

            $PeepSoUrlSegments = new PeepSoUrlSegments();
            $create_tab = ('create' == $PeepSoUrlSegments->get(3));

            if($create_tab) {

                $permalink = get_permalink();
                add_filter( 'page_link', function($link, $id, $sample ) use ($PeepSoUrlSegments, $permalink) {
                    return $permalink . $PeepSoUrlSegments->get(1) . '/' . $PeepSoUrlSegments->get(2) . '/' . $PeepSoUrlSegments->get(3) . '/';
                }, 99, 3);

                echo PeepSoTemplate::exec_template('blogposts', 'blogposts_create', array('view_user_id' => $this->view_user_id, 'create_tab' => TRUE), TRUE);
                return;
            }
        }

        echo PeepSoTemplate::exec_template('blogposts', 'blogposts', array('view_user_id' => $this->view_user_id), TRUE);
    }

    /**
     * @todo not sure what this does
     *
     * @param $pages
     * @return array
     */
    public function blogposts_peepso_rewrite_profile_pages($pages)
    {
        return array_merge($pages, array('posts'));
    }

    /**
     * return all post types without the blacklisted ones
     *
     * @return array
     */
    public static function blogposts_get_post_types()
    {
        return array('post');
    }

    /* * * ACTIVITY STREAM * * */

    /**
     * create an Activity Stream item when a new post is published
     *
     * @param int 		$ID
     * @param WP_Post 	$post
     * @return bool (FALSE - posting, post type disabled/blacklisted, TRUE - success, NULL - already added)
     */
    function blogposts_publish_post( $ID, $post ) {
        // is this a regular post?
        if('post' != $post->post_type) 			                                            {	return( FALSE );	}

        // is the post published?
        if(!in_array($post->post_status,  array('publish', 'future')))                      {	return( FALSE );	}

        // is activity posting enabled?
        if(0 == PeepSo::get_option('blogposts_activity_enable', 0 )) 			{	return( FALSE );	}

        // allow third parties to disable it
        if(0 == apply_filters('peepso_filter_blogposts_should_create_activity', 1, $post))  {	return( FALSE );	}

        // is this post type enabled?
        // if(!PeepSo::get_option('blogposts_activity_type_'.$post->post_type, 0)) {	return( FALSE );	}

        // check if it's not marked as already posted to activity and has valid act_id
        $act_id = get_post_meta($ID, self::BLOGPOSTS_SHORTCODE, TRUE);
        if(strlen($act_id) && is_numeric($act_id) && 0 < $act_id && $ID < $act_id) {
            add_filter( 'wp_insert_post_data', function( $new, $old) {
                $new['post_modified'] = $old['post_modified'];
                $new['post_modified_gmt'] = $old['post_modified_gmt'];
                return $new;
            }, 10, 2 );

            wp_update_post(array(
                'ID' => $act_id,
                'post_status' => $post->post_status
            ));

            remove_all_filters('wp_insert_post_data');

            return( NULL );
        }


        // author is not always the current user - ie when admin publishes a post written by someone else
        $author_id = $post->post_author;

        // skip blacklisted author IDs
        $blacklist = array();
        if(in_array($author_id, $blacklist))                                                {   return( FALSE );    }

        // #4424 exclude selected categories
        if(!PeepSoBlogPosts::enabled_for_post_categories($post->ID))                        {   return( FALSE );    }


        // build JSON to be used as post content for later display
        $content = array(
            'post_id' => $ID,
            'post_type' => $post->post_type,
            'shortcode' => self::BLOGPOSTS_SHORTCODE,
            'permalink' => get_permalink($ID),
        );

        $extra = array(
            'module_id' => self::BLOGPOSTS_MODULE_ID,
            'act_access'=> PeepSo::get_option('blogposts_activity_privacy',PeepSoUser::get_instance($author_id)->get_profile_accessibility()),
            'post_date'		=> $post->post_date,
            'post_date_gmt' => $post->post_date_gmt,
        );

        $content=json_encode($content);

        // create an activity item
        $act = PeepSoActivity::get_instance();
        $act_id = $act->add_post($author_id, $author_id, $content, $extra);

        update_post_meta($act_id, '_peepso_display_link_preview', 0);
        delete_post_meta($act_id, 'peepso_media');

        // mark this post as already posted to activity
        update_post_meta($ID, self::BLOGPOSTS_SHORTCODE, $act_id);

        return TRUE;
    }

    public function blogposts_publish_scheduled_post($post_id) {
        $act_id = get_post_meta($post_id, PeepSo::BLOGPOSTS_SHORTCODE, TRUE);
        if(strlen($act_id) && is_numeric($act_id) && 0 < $act_id) {
            wp_update_post(array(
                'ID' => $act_id,
                'post_status' => 'publish'
            ));
        }
    }

    /**
     * define the "action text" depending on post type eg "published a page"
     *
     * @param $action
     * @param $post
     * @return string
     */
    public function blogposts_activity_stream_action($action, $post)
    {
        if (self::BLOGPOSTS_MODULE_ID == intval($post->act_module_id)) {

            $action = PeepSo::get_option('blogposts_activity_type_post_text_default');

            $content = strip_tags(get_post_field('post_content', $post, 'raw'));
            if($target_post = json_decode($content)) {
                $key_text = 'blogposts_activity_type_'.$target_post->post_type.'_text';
                $action = PeepSo::get_option($key_text, $action);

                if(1==PeepSo::get_option('blogposts_activity_title_after_action_text',0)) {
                    $wp_post = get_post($target_post->post_id);

                    $action .= sprintf(' <a class="ps-blogposts-action-title" href="%s">%s</a>', get_the_permalink($wp_post->ID), $wp_post->post_title);

                }
            }
        }

        return ($action);
    }


    /**
     * parse the activity item JSON to force a nice embed
     *
     * @param $content
     * @param null $post
     * @return string
     */
    public function blogposts_filter_the_content_activity( $content, $post = NULL )
    {
        if(!stristr($content, self::BLOGPOSTS_SHORTCODE)) {
            return $content;
        }

        $content = strip_tags(get_post_field('post_content', $post, 'raw'));

        if($target_post = json_decode($content)) {
            $content = get_permalink($target_post->post_id);

            /*
             * Issue #5124.
             *
             * Disables lazy loading on iframe which is enabled by default on WordPress 5.7.
             * See: https://core.trac.wordpress.org/ticket/50756
             */
            add_filter('wp_iframe_tag_add_loading_attr', '__return_false');

            $content = apply_filters('the_content', $content);

            remove_filter('wp_iframe_tag_add_loading_attr', '__return_false');
        }


        global $post;
        update_post_meta($post->ID, '_peepso_display_link_preview', 0);
        delete_post_meta($post->ID, 'peepso_media');

        return $content;
    }

    /**
     * Disable repost
     *
     * @param array $actions The default options per post
     * @return  array
     */
    public function blogposts_filter_activity_post_actions($actions) {
        if ($actions['post']->act_module_id == self::BLOGPOSTS_MODULE_ID) {
            unset($actions['acts']['repost']);
            unset($actions['acts']['delete']);
        }
        return $actions;
    }

    /**
     * Build AJAX response with user blog posts
     */
    public function blogposts_ajax_user_posts()
    {
        ob_start();

        $input = new PeepSoInput();
        $owner = $input->int('user_id');
        $page  = $input->int('page', 1);

        $sort  = $input->value('sort', 'desc', array('asc','desc'));

        $limit = 10;
        $offset = ($page - 1) * $limit;

        if ($page < 1) {
            $page = 1;
            $offset = 0;
        }

        $args = array(
            'author'        => $owner,
            'orderby'       => 'post_date',
            'post_status'	=> 'publish',
            'order'         => $sort,
            'posts_per_page'=> $limit,
            'offset'		=> $offset,
        );

        // Count published posts
        $count_posts = wp_count_posts();
        $count_blogposts = $count_posts->publish;

        // Get the posts
        $blogposts=get_posts($args);

        $force_strip_shortcodes = PeepSo::get_option('blogposts_profile_content_force_strip_shortcodes', 0);

        if (count($blogposts)) {
            // Iterate posts
            foreach ($blogposts as $post) {

                // Choose between excerpt or post_content
                // @todo is there a more elegant way?
                $post_content = get_the_excerpt($post->ID);

                if(!strlen($post_content)) {
                    $post_content = $post->post_content;
                }

                $post_content = strip_shortcodes($post_content);

                if($force_strip_shortcodes) {
                    $post_content = preg_replace('/\[.*?\]/', '', $post_content);
                }

                $limit = intval(PeepSo::get_option('blogposts_profile_content_length',50));
                $post_content = wp_trim_words($post_content, $limit,'&hellip;');

                if(0 == $limit) {
                    $post_content = FALSE;
                }

                PeepSoTemplate::exec_template('blogposts', 'blogpost', array('post_content' => $post_content, 'post' => $post));
            }

            $resp['success']		= 1;

            $resp['html']			= ob_get_clean();
        } else {
            $message =  (get_current_user_id() == $owner) ? __('You have no blog posts yet', 'peepso-core') : sprintf(__('%s has no blog posts yet', 'peepso-core'), PeepSoUser::get_instance($owner)->get_firstname());
            $resp['success']		= 0;
            $resp['error'] = PeepSoTemplate::exec_template('profile','no-results-ajax', array('message' => $message), TRUE);
        }

        $resp['page']			= $page;
        $resp['found_blogposts']= abs($count_blogposts - $page * $limit);
        header('Content-Type: application/json');
        echo json_encode($resp);
        exit(0);
    }
// Reactions

    public function reactions_init_model()
    {
        // Global model instance
        if(!$this->reactions_model instanceof PeepSoReactionsModel) {
            $this->reactions_model  = new PeepSoReactionsModel();
        }

        return $this->reactions_model;
    }

    public function reactions_rebuild_cache()
    {
        // Directory where CSS files are stored
        $path = PeepSo::get_peepso_dir().'plugins'.DIRECTORY_SEPARATOR.'foundation'.DIRECTORY_SEPARATOR;

        if (!file_exists($path) ) {
            @mkdir($path, 0755, TRUE);
        }

        // Try to remove the old file
        $old_file = $path.'reactions-'.PeepSo3_Mayfly::get('peepso_reactions_css').'.css';
        if (file_exists($old_file)) {
            unlink($old_file);
        }

        // New cache
        delete_option('peepso_reactions_css');
        PeepSo3_Mayfly::set('peepso_reactions_css', time());

        $css ='';

        $this->reactions_init_model();

        foreach ($this->reactions_model->reactions as $id => $reaction) {

            $contain = '';

            if('svg' != strtolower(substr($reaction->icon_url, -3, 3))) {
                $contain = "background-size:contain;background-repeat:no-repeat;";
            }

            $css .= ".ps-reaction-emoticon-$id {background-image:url('" . $reaction->icon_url . "');$contain}";
        }

        update_option('peepso_reactions_css', $css);

        $file = $path.'reactions-'.PeepSo3_Mayfly::get('peepso_reactions_css').'.css';
        $h = fopen( $file, "a" );
        fputs( $h, $css );
        fclose( $h );
    }

    public function reactions_post_actions( $args )
    {
        $this->reactions_init_model();
        $this->reactions_model->init($args['post']->act_id);

        #6087 load first reaction as default
        $reaction = (FALSE == $this->reactions_model->my_reaction) ? $this->reactions_model->reactions[array_keys($this->reactions_model->reactions_published)[0]] : $this->reactions_model->my_reaction;

        $acts = array(
            'like' => array(
                'href' => '#',
                'label' => $reaction->title,
                'class' => "ps-post__action ps-post__action--reaction ps-reaction ps-reaction__toggle ps-reaction-toggle--{$this->reactions_model->act_id} {$reaction->class} ps-js-reaction-toggle",
                'click' => 'event.stopPropagation(); reactions.action_reactions(this, ' . $this->reactions_model->act_id . '); return false;',
                'count' => 0, // probably not important
            ),
        );

        unset($args['acts']['like']);
        $args['acts'] = array_merge($acts, $args['acts']);

        return $args;
    }

    public function reactions_before_comments()
    {
        global $post;
        $this->reactions_init_model();

        $this->reactions_model->init($post->act_id);
        echo $this->reactions_model->html_before_comments();
    }

// BlogPosts
    public static function usp_enabled() {
        return ( self::usp_pro_enabled() || defined('USP_VERSION') );
    }

    public static function usp_pro_enabled() {
        return class_exists('USP_Pro');
    }

// Social Login
    public static function social_login_enabled() {
        return (class_exists('TwistPress_Social_Login') || class_exists('PeepSo_Social_Login') || class_exists('PeepSoSocialLoginPlugin'));
    }

// 2FA
    public static function two_factor_plugin_enabled() {
        return class_exists('Simba_Two_Factor_Authentication') || class_exists('Simba_Two_Factor_Authentication_Plugin');
    }

// Hashtags

    private function hashtags_query_where($hashtag) {
        global $wpdb;

        $delimiters = array(
            ' ',
            "\n",
            '.',
            ',',
            '-',
            '\_', // escape to be treated literally
            '(',
            ')',
            '[',
            ']',
            '{',
            '}',
            '!',
            ':',
            ';',
            '#',
            '\%', // escape to be treated literally
            '*',
            '<',
        );

        if(1==PeepSo::get_option('hashtags_everything',0)) {
            $delimiters = array (
                " ",
                "\n",
                "\t",
                '#',
            );
        }

        // PeepSo/PeepSo#3649 replace closing tag </p>, if hashtag located in last word.
        $sql = " (REPLACE(`$wpdb->posts`.`post_content`, '</p>','') LIKE '%#$hashtag' "; // hashtag "glued to the end of post

        // hashtag ended by any of the legal delimiters (to avoid counting #hashtag and #hashtagofdoom together
        foreach($delimiters as $d) {
            $sql .= " OR `$wpdb->posts`.`post_content` LIKE '%#{$hashtag}{$d}%' ";
        }

        $sql.=")";

        return $sql;
    }

    /**
     * Process a batch ot posts
     */
    public function hashtags_build_posts($limit=10) {
        global $wpdb;
        $post_meta  = self::HASHTAGS_POST_META;

        $post_query = new WP_Query(array(
            'posts_per_page' => $limit,
            'post_type' => 'peepso-post',
            'meta_query' =>  array(
                'relation' => 'OR',
                array(
                    'key' => $post_meta,
                    'value' => '1',
                    'compare' => '!='
                ),
                array(
                    'compare' => 'NOT EXISTS',
                    'key' => $post_meta,
                )
            ),
        ));

        $count = count($post_query->posts);

        if($count) {

            foreach($post_query->posts as $post) {
                // Mark post as done
                add_post_meta($post->ID, $post_meta, 1, TRUE);

                $content = $post->post_content;

                // Don't match escaped UTF chars
                $content = str_replace('&#x','', $content);

                // regex matches " #", prepend space in case the content starts with #
                $content = str_replace('#',' #', $content);

                // regex matches " ", convert newlines/tabs to spaces
                $content = str_replace("\n", " ", $content);
                $content = str_replace("\t", " ", $content);

                // Detect all hashtags
                $regex = PeepSo::get_instance()->hashtags_regex;
                if(1==PeepSo::get_option('hashtags_everything',0)) {
                    $regex = " /#(\S{1,})/u";
                }
                preg_match_all($regex, $content, $matches);
                if ($matches) {

                    $hashtagsArray = array_count_values($matches[0]);
                    $hashtags = array_keys($hashtagsArray);

                    if(count($hashtags)) {

                        foreach($hashtags as $hashtag) {

                            // lowercase and remove the # symbol
                            $hashtag = function_exists('mb_strtolower') ? mb_strtolower($hashtag, 'UTF-8') : strtolower($hashtag);
                            $hashtag=strip_tags(trim($hashtag,'#'));
                            $hashtag=trim($hashtag);
                            if(!self::hashtag_validate($hashtag)) {
                                continue;
                            }

                            // Insert hashtag to database (ht_name is UNIQUE, so IGNORE to supress double value errors)
                            $wpdb->query("INSERT IGNORE INTO `{$wpdb->prefix}peepso_hashtags` SET `ht_name`='$hashtag'");
                        }
                    }

                }
            };
        }
#die();
        return $count;
    }

    /**
     * Process a batch of hashtags
     */
    public function hashtags_build_hashtags($limit=10) {
        global $wpdb;

        $interval = PeepSo::get_option('hashtags_post_count_interval', 60);

        $hashtags = $wpdb->get_results("SELECT `ht_name` FROM `{$wpdb->prefix}peepso_hashtags`  WHERE `ht_last_count` IS NULL OR `ht_last_count` < DATE_SUB(NOW(),INTERVAL $interval MINUTE) LIMIT $limit", OBJECT); // ARRAY_A);

        $count = count($hashtags);

        if($count) {
            foreach ($hashtags as $hashtag) {

                $hashtag = $hashtag->ht_name;

                $delete = FALSE;
                $count = 0;

                if(!self::hashtag_validate($hashtag)) {
                    $delete = TRUE;
                } else {
                    $sql = "SELECT `ID` FROM $wpdb->posts WHERE post_type='peepso-post' AND `post_status`='publish' AND  " . $this->hashtags_query_where($hashtag);

                    $wpdb->query($sql);

                    $count = (int)$wpdb->num_rows;

                    if ($count == 0 && 1 == PeepSo::get_option('hashtags_delete_empty', 1)) {
                        $delete = TRUE;
                    } else {
                        $wpdb->query("UPDATE `{$wpdb->prefix}peepso_hashtags` SET `ht_count`= $count, `ht_last_count`=NOW() WHERE `ht_name`='$hashtag'");
                    }
                }

                if ($delete) {
                    $wpdb->query("DELETE FROM `{$wpdb->prefix}peepso_hashtags` WHERE `ht_name`='$hashtag'");
                }
            }
        }

        return $count;
    }

    public static function hashtag_validate($hashtag) {

        $valid = TRUE;

        if(strlen($hashtag) == 0) {
            return FALSE;
        }

        if(1==PeepSo::get_option('hashtags_everything', 0)) {
            return TRUE;
        }

        $min_length = PeepSo::get_option('hashtags_min_length',3);
        $max_length = PeepSo::get_option('hashtags_max_length',16);

        if(strlen($hashtag) < $min_length) {
            $valid = FALSE;
        } elseif(strlen($hashtag) > $max_length) {
            $valid = FALSE;
        } elseif(is_numeric(substr($hashtag,0,1)) && 1 == PeepSo::get_option('hashtags_must_start_with_letter')) {
            $valid = FALSE;
        }

        return $valid;
    }

    public function hashtags_build_reset() {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->prefix}peepso_hashtags");
        delete_post_meta_by_key( self::HASHTAGS_POST_META );

    }

    public static function hashtag_url($hashtag = '') {

        // By default, use non-sef URL
        $questionmark="?";

        // If SEF URLs are enabled
        if (1 == PeepSo::get_option('disable_questionmark_urls', 0)) {

            // Make sure peepso_activity is NOT frontpage
            $frontpage = get_post(get_option('page_on_front'));

            // If it's NOT frontpage, it's safe tu use SEF
            if ('posts' == get_option( 'show_on_front' ) || !has_shortcode($frontpage->post_content, 'peepso_activity')) {
                $questionmark="";
            }
        }

        // Build and return URL
        $url = PeepSo::get_page('activity').$questionmark.'hashtag/'.$hashtag;
        if(strlen($hashtag)) {
            $url .='/';
        }


        return $url;
    }

// Profiles
    public function filter_member_search_args($peepso_args, $input)
    {
        $PeepSoUser = PeepSoUser::get_instance(0);

        $PeepSoUser->profile_fields->load_fields();
        $fields = $PeepSoUser->profile_fields->get_fields();

        foreach($fields as  $field) {
            if(1 == $field->prop('published') && 1==$field->prop('meta','searchable')) {
                $param = $input->value('profile_field_' . $field->prop('id'), '', FALSE); // SQL Safe
                if (!empty($param)) {
                    $peepso_args['meta_' . $field->prop('id')] = strtolower($param);
                }
            }
        }

        return $peepso_args;
    }

    public function action_render_member_search_fields() {
        # find all searchable fields
        #render them

        $PeepSoUser = PeepSoUser::get_instance(0);

        $PeepSoUser->profile_fields->load_fields();
        $fields = $PeepSoUser->profile_fields->get_fields();

        foreach($fields as  $field) {
            if(1 == $field->prop('published') && 1==$field->prop('meta','searchable')) {
                $field->value=FALSE;
                ?>
                <div class="ps-members__filter ps-members__filter--custom ps-js-filter-extended">
                    <label class="ps-members__filter-label"><?php echo $field->prop('title'); ?></label>
                    <?php $field->render_input(); ?>
                </div>
                <?php
            }
        };

        echo "<br><br>";
    }

    public function add_fieldtext_admin_general_option($field) {

        /** HALF WIDTH **/
        $params = array(
            'type'          => 'checkbox',
            'data'          => array(
                'data-prop-type'        => 'meta',
                'data-prop-name'        => 'half_width',
                'data-disabled-value'   => '0',
                'value'                 => '1',
                'admin_value'           => $field->prop('meta', 'half_width'),
                'id'                    => 'field-' . $field->prop('id') . '-half_width',
            ),
            'field'         => $field,
            'label'         => __('Half width', 'peepso-core'),
            'label_after'   => '',
        );

        // add "checked" manually - the value is "published" and by default checkbox looks for "1"
        if(1 == $field->prop('meta', 'half_width')) {
            $params['data']['checked'] = 'checked';
        }

        echo PeepSoTemplate::exec_template('admin','profiles_field_config_field', $params);

        if( $field instanceof PeepSoFieldSelectSingle && !($field instanceof PeepSoFieldSelectMulti) && 0 == $field->prop('meta','is_core')) {
            /** SHOW IN SEARCH **/
            $params = array(
                'type' => 'checkbox',
                'data' => array(
                    'data-prop-type' => 'meta',
                    'data-prop-name' => 'searchable',
                    'data-disabled-value' => '0',
                    'value' => '1',
                    'admin_value' => $field->prop('meta', 'searchable'),
                    'id' => 'field-' . $field->prop('id') . '-searchable',
                ),
                'field' => $field,
                'label' => __('Searchable', 'peepso-core'),
                'label_after' => __('Warning: searchable fields privacy will be forced as "Site Members" and will not be editable by users. The "Privacy" config tab will have no effect.', 'peepso-core'),
            );

            // add "checked" manually - the value is "published" and by default checkbox looks for "1"
            if (1 == $field->prop('meta', 'searchable')) {
                $params['data']['checked'] = 'checked';
            }

            echo PeepSoTemplate::exec_template('admin', 'profiles_field_config_field', $params);
        }

        /** SHOW IN REGISTRATION **/
        $params = array(
            'type'			=> 'checkbox',
            'data'			=> array(
                'data-prop-type' 		=> 'meta',
                'data-prop-name' 		=> 'user_registration',
                'data-disabled-value' 	=> '0',
                'value' 				=> '1',
                'admin_value'			=> $field->prop('meta', 'user_registration'),
                'id'					=> 'field-' . $field->prop('id') .'-registration',
            ),
            'field'			=> $field,
            'label'			=> __('Show in registration','peepso-core'),
            'label_after'	=> '',
        );

        // add "checked" manually - the value is "published" and by default checkbox looks for "1"
        if(1 == $field->prop('meta', 'user_registration')) {
            $params['data']['checked'] = 'checked';
        }

        echo PeepSoTemplate::exec_template('admin','profiles_field_config_field', $params);

        /** REGISTRATION ONLY **/
        if($field->prop('admin_registration_only')) {
            $params = array(
                'type'        => 'checkbox',
                'data'        => array(
                    'data-prop-type'      => 'meta',
                    'data-prop-name'      => 'user_registration_only',
                    'data-disabled-value' => '0',
                    'value'               => '1',
                    'admin_value'         => $field->prop( 'meta', 'user_registration_only' ),
                    'id'                  => 'field-' . $field->prop( 'id' ) . '-user-registration-only',
                ),
                'field'       => $field,
                'label'       => __( 'Show only in registration', 'peepso-core' ),
                'label_after' =>
                    __( 'The field will only show during registration. Administrators will be still able to see the field in user profiles.', 'peepso-core' )
                    . ' '
                    . __('The field will not be counted in profile completeness.', 'peepso-core'),
            );

            // add "checked" manually - the value is "published" and by default checkbox looks for "1"
            if ( 1 == $field->prop( 'meta', 'user_registration_only' ) ) {
                $params['data']['checked'] = 'checked';
            }

            echo PeepSoTemplate::exec_template( 'admin', 'profiles_field_config_field', $params );
        }

        /**  ADMIN ONLY **/

        if($field->prop('admin_only')) {

            // Admin Editable Only
            $params = array(
                'type' => 'checkbox',
                'data' => array(
                    'data-prop-type' => 'meta',
                    'data-prop-name' => 'user_admin_editable_only',
                    'data-disabled-value' => '0',
                    'value' => '1',
                    'admin_value' => $field->prop('meta', 'user_admin_editable_only'),
                    'id' => 'field-' . $field->prop('id') . '-user-admin-editable-only',
                ),
                'field' => $field,
                'label' => __('Only Admin can edit', 'peepso-core'),
                'label_after' =>
                    __('The field will be editable only by the Admins, and will be seen by anyone who matches the field privacy.', 'peepso-core')
                    . ' '
                    . __('The field will not be counted in profile completeness.', 'peepso-core'),
                'desc' => '',
            );

            if (1 == $field->prop('meta', 'user_admin_editable_only')) {
                $params['data']['checked'] = 'checked';
            }

            PeepSoTemplate::exec_template('admin', 'profiles_field_config_field', $params);
            // Admin Visible Only
            $params = array(
                'type' => 'checkbox',
                'data' => array(
                    'data-prop-type' => 'meta',
                    'data-prop-name' => 'user_admin_visible_only',
                    'data-disabled-value' => '0',
                    'value' => '1',
                    'admin_value' => $field->prop('meta', 'user_admin_visible_only'),
                    'id' => 'field-' . $field->prop('id') . '-user-admin-visible-only',
                ),
                'field' => $field,
                'label' => __('Only Admin can see', 'peepso-core'),
                'label_after' =>
                    __('The field will be visible and editable only by the Admins. Enable this if you need the field to serve an Admin-only purpose (eg a temporary field draft, or Admin user notes).', 'peepso-core')
                    . ' '
                    . __('The field will not be counted in profile completeness.', 'peepso-core'),
                'desc' => '',
            );

            if (1 == $field->prop('meta', 'user_admin_visible_only')) {
                $params['data']['checked'] = 'checked';
            }

            PeepSoTemplate::exec_template('admin', 'profiles_field_config_field', $params);
        }
    }

    public function add_fieldtexturlpreset_admin_general_option($field) {
        $params = array(
            'type'			=> 'text',
            'data'			=> array(
                'data-prop-type' 		=> 'meta',
                'data-prop-name' 		=> 'preseturl',
                'data-disabled-value' 	=> '0',
                'value' 				=> strlen($field->prop('meta', 'preseturl')) ? $field->prop('meta', 'preseturl') : 'instagram.com',
                'size'                  => '100',
                'admin_value'			=> $field->prop('meta', 'preseturl'),
                'id'					=> 'field-' . $field->prop('id') .'-preseturl',
            ),
            'field'			=> $field,
            'label'			=> __('Preset domain','peepso-core'),
            'label_after'	=> '<b>domain.com</b> without http(s):// or trailing slash',
        );

        echo PeepSoTemplate::exec_template('admin','profiles_field_config_field', $params);

        $params = array(
            'type'			=> 'text',
            'data'			=> array(
                'data-prop-type' 		=> 'meta',
                'data-prop-name' 		=> 'userprefix',
                'data-disabled-value' 	=> '0',
                'value' 				=> $field->prop('meta', 'userprefix'),
                'size'                  => '100',
                'admin_value'			=> $field->prop('meta', 'userprefix'),
                'id'					=> 'field-' . $field->prop('id') .'-userprefix',
            ),
            'field'			=> $field,
            'label'			=> __('User prefix','peepso-core'),
            'label_after'	=> 'For example <b>@</b> for instagram and <b>/u/</b> for Reddit',
        );

        echo PeepSoTemplate::exec_template('admin','profiles_field_config_field', $params);
    }

    public function add_fieldtext_admin_privacy_option($field) {
        $params = array(
            'type'			=> 'checkbox',
            'data'			=> array(
                'data-prop-type' 		=> 'meta',
                'data-prop-name' 		=> 'privacywarning',
                'data-disabled-value' 	=> '0',
                'value' 				=> '1',
                'admin_value'			=> $field->prop('meta', 'privacywarning'),
                'id'					=> 'field-' . $field->prop('id') .'-privacywarning',
            ),
            'field'			=> $field,
            'label'			=> __('Show a privacy warning','peepso-core'),
            'label_after'	=> __('When enabled, users will be presented with a privacy warning after going into "edit mode" of this profile field','peepso-core'),
        );

        // add "checked" manually - the value is "published" and by default checkbox looks for "1"
        if(1 == $field->prop('meta', 'privacywarning')) {
            $params['data']['checked'] = 'checked';
        }

        echo PeepSoTemplate::exec_template('admin','profiles_field_config_field', $params);

        $params = array(
            'type'			=> 'text',
            'data'			=> array(
                'data-prop-type' 		=> 'meta',
                'data-prop-name' 		=> 'privacywarningtext',
                'data-disabled-value' 	=> '0',
                'value' 				=> $field->prop('meta', 'privacywarningtext'),
                'size'                  => '100',
                'admin_value'			=> $field->prop('meta', 'privacywarningtext'),
                'id'					=> 'field-' . $field->prop('id') .'-privacywarningtext',
            ),
            'field'			=> $field,
            'label'			=> __('Privacy warning text','peepso-core'),
            'label_after'	=> '',
        );

        echo PeepSoTemplate::exec_template('admin','profiles_field_config_field', $params);
    }

    public function register_form_fields($fields) {

        $args = array('post_status'=>'publish');

        $user = PeepSoUser::get_instance(0);
        $user->profile_fields->load_fields($args);
        $ext_fields = $user->profile_fields->get_fields();

        // adding field with type `extended_fields` so we can have hook `peepso_register_extended_fields`
        $fields_to_add = array(
            'extended_profile_fields' => array(
                'type' => 'extended_fields',
            )
        );

        $first_fields = array_splice ($fields, 0, 5);
        $fields = array_merge ($first_fields, $fields_to_add, $fields);

        return $fields;
    }

    public function register_extended_fields() {

        $input = new PeepSoInput();
        $user = PeepSoUser::get_instance(0);

        $args = array('post_status'=>'publish');

        $user->profile_fields->load_fields($args);
        $fields = $user->profile_fields->get_fields();
        if( count($fields) ) {
            foreach ($fields as $key => &$field) {
                $field->is_registration_page = TRUE;

                // check if any post request?
                if(!isset($field::$user_disable_edit) && 1 == $field->prop('meta', 'user_registration') && ((isset($field->meta->validation) && count((array)$field->meta->validation)) && (isset($_POST) && count((array)$_POST)))) {

                    // get old meta
                    $meta = $field->meta;

                    $value = $input->value(PeepSoField::$profile_field_prefix . $field->id, '', FALSE); // SQL Safe

                    $field->value = $value;
                    $field->validate();

                    // rollback meta
                    $field->meta->validation = $meta->validation;
                }
            }
        }

        echo PeepSoTemplate::exec_template('profile', 'profile-register', array('fields' => $fields));
    }

    public function valid_extended_fields($ret, $input) {

        $user = PeepSoUser::get_instance(0);

        $args = array('post_status'=>'publish');

        $user->profile_fields->load_fields($args);
        $fields = $user->profile_fields->get_fields();
        if( count($fields) ) {
            foreach ($fields as $key => $field) {
                $field->is_registration_page = TRUE;

                // check if any post request?
                if(!isset($field::$user_disable_edit) && 1 == $field->prop('meta', 'user_registration') && ((isset($field->meta->validation) && count((array)$field->meta->validation)) && (isset($_POST) && count((array)$_POST)))) {

                    // get old meta
                    $meta = $field->meta;

                    $field->value = $input->value(PeepSoField::$profile_field_prefix . $field->id, '', FALSE); // SQL Safe

                    // validate the value
                    $success = $field->validate();
                    if(FALSE === $success) {
                        // just return if any field invalid
                        return $success;
                    }

                    // rollback meta
                    $field->meta->validation = $meta->validation;
                }
            }
        }

        return ($ret);
    }

    public function register_new_user($wp_user) {

        if(0 !== intval($wp_user)) {
            $PeepSoInput = new PeepSoInput();

            foreach ($_POST as $key => $value) {
                // check if key `peepso_field_` exist
                if ( strpos($key, PeepSoField::$profile_field_prefix) !== FALSE) {
                    $id = str_replace(PeepSoField::$profile_field_prefix, "", $key);
                    $field = PeepSoField::get_field_by_id($id, $wp_user);

                    // if not instanceod peepsofield, just continue and not update db
                    if( !($field instanceof PeepSoField)) {
                        continue;
                    }

                    $value = $PeepSoInput->value($key, '', FALSE); // SQL Safe

                    // wp field returns INT, peepso field returns BOOL
                    $success = $field->save($value);
                }
            }
        }
    }

    public static function action_peepsofieldselect_select_options( $field )
    {
        PeepSoTemplate::exec_template('admin','selectoptions', array('field'=>$field));
    }

    // Add "nofollow" to URL fields
    public static function action_peepsofieldtexturl_nofollow( $field )
    {
        PeepSoTemplate::exec_template('admin','urlnofollow', array('field'=>$field));
    }

    // Add "top countries" to country fields
    public static function action_peepsofieldcountry_countries_top( $field )
    {
        PeepSoTemplate::exec_template('admin','countries_top', array('field'=>$field));
    }

    public function filter_user_field_meta_keys( $keys )
    {
        return array_merge($keys, $this->field_meta_keys_extra);
    }

    public function filter_user_field_meta_keys_as_int( $keys )
    {
        return array_merge($keys, $this->field_meta_keys_extra_as_int);
    }

    public function filter_user_field_meta_keys_as_array( $keys )
    {
        return array_merge($keys, $this->field_meta_keys_extra_as_array);
    }

    public function action_admin_profiles_list_before() {

        foreach($this->field_types as $field_type) {

            $class = strtolower('peepsofield'.$field_type);

            if(!class_exists($class)) {
                continue;
            }

            $field_types[$field_type] = $class::$order.'|ORDER|'.$class::$admin_label;
        }
        asort($field_types);

        foreach($field_types as $k=>$v) {
            $v = explode('|ORDER|', $v);
            $v=$v[1];

            $field_types[$k]=$v;
        }

        wp_localize_script('peepso-admin-profiles-extended', 'peepsofieldtypes', $field_types);
    }

    public function action_admin_profiles_field_title_after($field)
    {
        echo str_ireplace('peepso','',self::PLUGIN_NAME) . ': ' . $field::$admin_label;
    }

    public function filter_profile_fields_query_limit( $limit )
    {
        return 1000;
    }

    public static function action_admin_profiles_field_options($field)
    {
        if(0 == $field->prop('meta','is_core')) { ?>
            <div class="ps-settings__action">
                <a data-id="<?php echo $field->prop('id'); ?>" href="#" class="ps-js-field-duplicate"><i class="fa fa-copy"></i><?php echo __('Duplicate', 'peepso-core');?></a>
                <a data-id="<?php echo $field->prop('id'); ?>" href="#" class="ps-js-field-delete"><i class="fa fa-trash"></i></a>
            </div>
            <?php
        }
    }

    // Additional options after the Default Privacy
    public function action_admin_profiles_field_options_default_privacy( $field )
    {
        PeepSoTemplate::exec_template('admin','privacyoptions', array('field'=>$field));
    }

    // Additional options on the bottom of the Appearance Tab
    public function action_admin_profiles_field_tab_appearance( $field )
    {
        PeepSoTemplate::exec_template('admin','appearance', array('field'=>$field));
    }

    public function filter_admin_dashboard_demographic_data($data)
    {
        $PeepSoUser = PeepSoUser::get_instance(0);
        $profile_fields = new PeepSoProfileFields($PeepSoUser);
        $fields = $profile_fields->load_fields();

        $male_gender_key = array_search(__('Male', 'peepso-core'), array_column($data, 'label'));
        $female_gender_key = array_search(__('Female', 'peepso-core'), array_column($data, 'label'));

        if(isset($fields['peepso_user_field_gender'])) {
            foreach ($fields['peepso_user_field_gender']->meta->select_options as $key => $value) {
                if (is_int($male_gender_key) && $male_gender_key >= 0 && $key == 'm') {
                    $data[$male_gender_key]['label'] = $value;
                } else if (is_int($female_gender_key) && $female_gender_key >= 0 && $key == 'f') {
                    $data[$female_gender_key]['label'] = $value;
                } else {
                    $data[] = array(
                        'label' => $value,
                        'value' => $PeepSoUser->get_count_by_gender($key),
                        'icon' => PeepSo::get_asset('images/avatar/user-neutral-thumb.png'),
                        'color' => 'rgb(180,180,180)'
                    );
                }
            }
        }

        return $data;
    }

    public function future_to_publish_notify_author(WP_Post $post) {
        if ($post->post_type == PeepSoActivityStream::CPT_POST) {

            $activity = PeepSoActivity::get_instance();
            $post = $activity->get_post_object($post->ID);

            $PeepSoNotifications = new PeepSoNotifications();

            $i18n = __('Your scheduled post is live', 'peepso-core');
            $message = 'Your scheduled post is live';

            $args = [
                'peepso-core',
            ];

            $PeepSoNotifications->add_notification_new($post->post_author, $post->post_author, $message, $args,'future_to_publish', $post->act_module_id, $post->ID);
        }
    }

}

defined('WPINC') || die;
PeepSo::get_instance();

// load helpers
require_once(dirname(__FILE__) . '/lib/helpers.php');
require_once(dirname(__FILE__) . '/lib/pluggable.php');

// EOF
