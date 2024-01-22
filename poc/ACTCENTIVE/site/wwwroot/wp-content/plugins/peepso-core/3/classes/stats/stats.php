<?php

class PeepSo3_Stats {

    private $optin_stats = 0;

    private static $_instance = NULL;

    private $mayfly = 'peepso_stats_last_run';
    private $mayfly_expire = 3600;

    private $debug = FALSE;

    public static $desc = '
Help us improve PeepSo by sending some important statistical information we can use to understand our users better.
<br/><br/>When ON, our servers will receive and store your environment information (PHP version, WordPress version, locale used), some basic information about your set-up (how many users there are, what plugins and themes are used) and a few key PeepSo configuration options.
<br/><br/>This data will help us focus our efforts better based on real world scenarios.';

    private function __construct()
    {
        add_action('admin_init',function() {
            if(isset($_GET['peepso_stats_details'])) {
                $stats = $this->get_stats(TRUE);
                ?>
                <h1>PeepSo Usage Tracking</h1>

                <p><?php echo self::$desc;?></p>

                <h2>What is being tracked?</h2>


                <h3>PeepSo Product Data</h3>
                <ul>
                    <li>Current <b><?php
                            echo $stats['ver_peepso_desc'];
                            unset($stats['ver_peepso']);
                            unset($stats['ver_peepso_desc']);?></b></li>

                    <li><b><?php
                            echo $stats['peepso_install_date_desc'];
                            unset($stats['peepso_install_date']);
                            unset($stats['peepso_install_date_desc']);?></b></li>
                    <?php
                    foreach($stats as $k=>$v) {
                        if(stristr($k, 'prd_') && stristr($k, '_desc')) {
                            unset($stats[$k]);
                            unset($stats[str_replace('_desc','',$k)]);

                            echo "<li>Is <b>$v</b> active?</li>";
                        }
                    }
                    ?>
                </ul>

                <h3>Selected PeepSo Configuration Options</h3>
                <ul>
                    <?php
                    $c = $stats['peepso_config_desc'];
                    $desc = array(
                        'videos_upload_enable' => 'Videos: are <b>video uploads</b> enabled?',
                        'videos_conversion_mode' => 'Videos: which <b>conversion mode</b> is used?',
                    );
                    unset($stats['peepso_config']);
                    unset($stats['peepso_config_desc']);
                    foreach($c as $k=>$v) {
                        $k = isset($desc[$k]) ? $desc[$k] : $k ." (missing description)";
                        echo "<li>$k</li>";
                    }

                    ?>
                </ul>

                <h3>Basic Environment & Website Data</h3>
                <ul>
                    <?php
                    foreach($stats as $k=>$v) {
                        if(stristr($k, 'ver_') && stristr($k, '_desc')) {
                            unset($stats[$k]);
                            unset($stats[str_replace('_desc','',$k)]);

                            echo "<li>Current <b>$v</b></li>";
                        }
                    }
                    $keys = array('url_desc','count_users_desc','count_plugins_desc','theme_desc');
                    foreach($stats as $k=>$v) {
                        if(in_array($k, $keys)) {
                            unset($stats[$k]);
                            unset($stats[str_replace('_desc','',$k)]);

                            echo "<li>$v</li>";
                        }
                    }
                    ?>
                </ul>

                <h3>Third Party Product Data</h3>
                <ul>
                    <?php
                    foreach($stats as $k=>$v) {
                        if(stristr($k, 'tp_') && stristr($k, '_desc')) {
                            unset($stats[$k]);
                            unset($stats[str_replace('_desc','',$k)]);

                            echo "<li>Is <b>$v</b> active?</li>";
                        }
                    }
                    ?>
                </ul>


                <?php
                if(count($stats)) {
                    ?>
                    <h3>Other</h3>
                    <p>Variables with a missing description</p>
                    <ul>
                    <?php
                    foreach ($stats as $k => $v) {
                        echo "<li>$k => $v</li>";
                    }
                    echo "</ul>";
                }
                ?>


                <h2>Raw data sent to PeepSo, Inc.</h2>
                <pre><?php
                    $stats = $this->get_stats();
                    print_r($stats);
                    ?></pre>



                <?php
                die();
            }
        });

        $this->debug = isset($_REQUEST['peepso_stats_debug']);

        if(isset($_GET['peepso_enable_tracking_nudge'])) {
            PeepSoConfigSettings::get_instance()->set_option('optin_stats', 1);
        }

        $this->optin_stats = (PeepSo3_Helper_Addons::maybe_optin_stats());
        if(!$this->optin_stats) {

            if(PeepSo::is_admin() && is_admin() && isset($_GET['page']) && 'peepso_config'==$_GET['page']) {

                add_action('admin_notices', function() {

                    // reset logic
                    if(isset($_GET['reset_hide_tracking_nudge'])) {
                        delete_user_option(get_current_user_id(), 'peepso_tracking_last_nudge');
                    }

                    // dismiss logic
                    if(isset($_GET['peepso_hide_tracking_nudge'])) {
                        update_user_option(get_current_user_id(), 'peepso_tracking_last_nudge', date('Y-m-d'));
                    }

                    $was_asked = TRUE;
                    $limit = 90; // By default, ask once every 3 months
                    $last_nudge = get_user_option('peepso_tracking_last_nudge');

                    // If last nudge is not found, user was never asked, use the install date instead
                    if(!$last_nudge) {
                        $was_asked = FALSE;
                        $last_nudge = get_option('peepso_install_date');
                        $limit = 15; // Exception: 15 days since installation
                    }

                    if(time() - strtotime($last_nudge) > $limit * 24 * 3600) {
                        PeepSoTemplate::exec_template('admin','tracking_nudge',array('was_asked'=>$was_asked, 'last_nudge'=>$last_nudge));
                    }

                });

            }

            return FALSE;
        }

        $this->run();
    }

    public static function get_instance()
    {
        if (NULL === self::$_instance)
            self::$_instance = new self();
        return (self::$_instance);
    }

    private function get_stats($desc=FALSE) {
        global $wp_version;

        $peepso_config = array(
            'videos_upload_enable' => (int) PeepSo::get_option('videos_upload_enable'),
            'videos_conversion_mode' => PeepSo::get_option('videos_conversion_mode'),
        );

        $count_users = count_users('memory');

        $stats = array(

            'url'               => trim(str_replace(array('http://','https://','www.'),'',get_option( 'siteurl' )),'/'),
            'url_desc'          => 'The URL of your site',

            'ver_peepso'        => PeepSo::PLUGIN_VERSION,
            'ver_peepso_desc'   => 'PeepSo version',

            'ver_wp'           =>  $wp_version,
            'ver_wp_desc'       => 'WordPress version',

            'ver_php'           => PHP_VERSION,
            'ver_php_desc'       => 'PHP version',

            'ver_locale'        =>  get_locale(),
            'ver_locale_desc'       => 'site language',

            'ver_bundle'        => PeepSo3_Helper_Addons::license_to_name(),
            'ver_bundle_desc'   => 'PeepSo bundle type (if any)',

            'count_users'       => $count_users['total_users'],
            'count_users_desc'  => 'Number of registered users',

            'count_plugins'     => (int) count(get_option( 'active_plugins' )),
            'count_plugins_desc'  => 'Number of active plugins',

            'theme'             => wp_get_theme()->get('Name'),
            'theme_desc'        => 'Currently active theme',

            'peepso_install_date'     => get_option('peepso_install_date'),
            'peepso_install_date_desc'  => 'PeepSo install date (community age)',

            'peepso_config'     => json_encode($peepso_config),
            'peepso_config_desc' => $peepso_config,

            'prd_gecko'         => (int) class_exists('GeckoConfigSettings'),
            'prd_gecko_desc'    => 'Gecko Theme',

            // Core
            'prd_photos'        => (int) class_exists('PeepSoSharePhotos'),
            'prd_photos_desc'    => 'Photos Plugin',

            'prd_media'         => (int) class_exists('PeepSoVideos'),
            'prd_media_desc'    => 'Audio & Video Plugin',

            'prd_chat'          => (int) class_exists('PeepSoMessagesPlugin'),
            'prd_chat_desc'    => 'Chat Plugin',

            'prd_groups'        => (int) class_exists('PeepSoGroupsPlugin'),
            'prd_groups_desc'    => 'Groups Plugin',

            'prd_files'         => (int) class_exists('PeepSoFileUploads'),
            'prd_files_desc'    => 'File Uploads Plugin',

            'prd_friends'       => (int) class_exists('PeepSoFriendsPlugin'),
            'prd_friends_desc'    => 'Friends Plugin',

            'prd_userlimits'    => (int) class_exists('peepsolimitusers'),
            'prd_userlimits_desc'    => 'User Limits Plugin',

            'prd_emaildigest'   => (int) class_exists('PeepSoEmailDigest'),
            'prd_emaildigest_desc'    => 'Email Digest Plugin',

            // Integrations
            'prd_badgeos'       => (int) class_exists('BadgeOS_PeepSo'),
            'prd_badgeos_desc'    => 'BadgeOS Integration Plugin',

            'prd_mycred'        => (int) class_exists('PeepSoMyCreds'),
            'prd_mycred_desc'    => 'myCRED Integration Plugin',

            'prd_app'        => (int) class_exists('PeepSoAppPlugin'),
            'prd_app_desc'    => 'Mobile App Integration Plugin',

            'prd_pmp'           => (int) class_exists('PeepSoPMP'),
            'prd_pmp_desc'    => 'PMP Integration Plugin',

            'prd_social_login' => (int) (class_exists('TwistPress_Social_Login') ||class_exists('PeepSo_Social_Login') || class_exists('PeepSoSocialLoginPlugin')),
            'prd_social_login_desc'    => 'Social Login Integration Plugin',

            'prd_wpem'     => (int) class_exists('PeepSo_WPEM_Plugin'),
            'prd_wpem_desc'    => 'WP Event Manager Integration Plugin',

            'prd_wpjm'     => (int) class_exists('PeepSoWPJM'),
            'prd_wpjm_desc'    => 'WP Job Manager Integration Plugin',

            'prd_ideapush'     => (int) class_exists('PeepSoIdeaPushPlugin'),
            'prd_ideapush_desc'    => 'IdeaPush Integration Plugin',

            'prd_givewp'     => (int) class_exists('PeepSoGiveWPPlugin'),
            'prd_givewp_desc'    => 'GiveWP Integration Plugin',

            // Monetization
            'prd_advancedads'    => (int) class_exists('PeepSoAdvancedAdsPlugin'),
            'prd_advancedads_desc'    => 'Advanced Ads Integration Plugin',

            'prd_edd'           => (int) class_exists('PeepSoEDD'),
            'prd_edd_desc'    => 'EDD Integration Plugin',

            'prd_learndash'     => (int) class_exists('PeepSoLearnDash'),
            'prd_learndash_desc'    => 'LearnDash Integration Plugin',

            'prd_tutorlms'     => (int) class_exists('PeepSoTutorLMSPlugins'),
            'prd_tutorlms_desc'    => 'Tutor LMS Integration Plugin',

            'prd_pmp'           => (int) class_exists('PeepSoPMP'),
            'prd_pmp_desc'    => 'PMP Integration Plugin',

            'prd_woocommerce'   => (int) class_exists('WBPWI_PeepSo_Woo_Integration'),
            'prd_woocommerce_desc'    => 'WooCommerce Integration Plugin',

            'prd_wpadverts'     => (int) class_exists('PeepSoWPAdverts'),
            'prd_wpadverts_desc'    => 'WPAdverts Integration Plugin',

            // Early access
            'prd_earlyaccess'   => (int) class_exists('PeepSoEarlyAccessPlugin'),
            'prd_earlyaccess_desc'    => 'Early Access Plugin',

            'tp_dokan'        => (int) PeepSo3_Third_Party::has_ecommerce_dokan(),
            'tp_dokan_desc'   => 'Third Party Plugin: Dokan',

            'tp_canvas'        => (int) PeepSo3_Third_Party::has_mobile_wrapper_mobiloud_canvas(),
            'tp_canvas_desc'   => 'Third Party Plugin: Mobiloud Canvas',

            'tp_wpma'        => (int) PeepSo3_Third_Party::has_mobile_wrapper_wpma(),
            'tp_wpma_desc'   => 'Third Party Plugin: WPMobile.app',

            'tp_trp'        => (int) PeepSo3_Third_Party::has_multilingual_trp(),
            'tp_trp_desc'   => 'Third Party Plugin: TranslatePress',
        );

        if(!$desc) {
            foreach($stats as $k=>$v) {
                if(stristr($k,'_desc')) {
                    unset($stats[$k]);
                }
            }
        }

        return $stats;
    }

    private function run($forced=FALSE) {

        if($this->debug) {
            PeepSo3_Mayfly::del($this->mayfly);
        }

        if($forced || (!PeepSo::is_api_request() && empty(PeepSo3_Mayfly::get($this->mayfly))) ) {

            $stats = $this->get_stats();

            if($forced) {
                $deactivation_stats = [
                    'deactivation_reason' =>  isset($_REQUEST['deactivation_reason']) ? trim(stripslashes($_REQUEST['deactivation_reason'])) : '',
                    'deactivation_admin_email' => get_bloginfo('admin_email'),
                    'deactivation_date' => date('Y-m-d'),
                ];

                $stats = array_merge($stats,$deactivation_stats);
            }

            foreach($stats as &$stat) {
                $stat = urlencode($stat);
            }

            //array_walk($stats, 'urlencode');

            $url = 'https://www.peepso.com/?usage_tracking&action=insert';

            foreach($stats as $k => $v) {
                $url .= "&$k=$v";
            }

            // Attempt without sslverify
            $args = array('timeout' => 10, 'sslverify' => FALSE);
            $resp = wp_remote_get(add_query_arg(array(), $url), $args);

            // In some cases sslverify is needed
            $args = array('timeout' => 10, 'sslverify' => TRUE);
            if (is_wp_error($resp)) {
                $resp = wp_remote_get(add_query_arg(array(), $url), $args);
            }

            PeepSo3_Mayfly::set($this->mayfly, $url, $this->mayfly_expire);
        }
    }

    public function deactivation_feedback(){
        $this->run(TRUE);
    }
}
