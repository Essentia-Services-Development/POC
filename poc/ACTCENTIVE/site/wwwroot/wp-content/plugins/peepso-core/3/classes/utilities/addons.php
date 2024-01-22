<?php

class PeepSo3_Helper_Addons {

    public static function license_to_data($license=NULL, $cache = TRUE) {

        if(NULL===$license) {
            $license=self::get_license();
        }

        if(!$license) return FALSE;

        $data = PeepSo3_Helper_Remote_Content::get('license_to_data', $cache, ['license_to_id' => $license]);
        $data = json_decode($data, TRUE);

        if(is_array($data) && isset($data['bundle_id']) && isset($data['bundle_name'])) {
            return $data;
        }

        return FALSE;
    }

    public static function license_to_id($license, $cache = TRUE) {
        $data = self::license_to_data($license, $cache);

        if(FALSE !== $data) {
            return $data['bundle_id'];
        }

        return FALSE;
    }

    public static function license_to_name($license=NULL, $cache = TRUE) {
        if(NULL === $license) {
            $license = self::get_license();
        }

        $data = self::license_to_data($license, $cache);
        if(FALSE !== $data) {
            return $data['bundle_name'];
        }

        return FALSE;
    }

    public static function license_is_eligible_upgrade() {
        
        // License is free bundle
        if (PeepSo3_Helper_Addons::license_is_free_bundle()) {
            return TRUE;
        }

        // No license (fresh install / PeepSo Foundation only
        $license = PeepSo3_Helper_Addons::get_license();
        if (!strlen($license)) {
            return TRUE;
        }

        // License Provided but PeepSo Foundation only
        if (strlen($license)) {
            $current_item_id = PeepSo3_Helper_Addons::license_to_id($license, 0);
            if (!$current_item_id) {
                return TRUE;
            }
        }

        $display_warning = PeepSo3_Mayfly::get('peepso_has_displayed_license_warning');
        if (PeepSo3_Utilities_String::maybe_strlen($display_warning)) {
            return TRUE;
        }
        return FALSE;
    }

    public static function license_is_free_bundle($cache = TRUE) {
        return ('bundle-free' == PeepSo3_Helper_Addons::license_to_name(NULL, $cache));
    }

    public static function license_is_basic_bundle($cache = TRUE) {
        return ('bundle-basic' == PeepSo3_Helper_Addons::license_to_name(NULL, $cache));
    }

    public static function get_upsell($location = FALSE, $cache = TRUE) {

        $license = self::get_license();

        if('admin_top' == $location) {

            // Do not print admin-top in PeepSo admin pages
            if (isset($_GET['page']) && in_array($_GET['page'], ['peepso_config', 'peepso-installer', 'peepso-manage', 'peepso-queue', 'peepso'])) {
                return '';
            }

            // Only print admin-top for PeepSo Free Bundle
            if(!PeepSo3_Helper_Addons::license_is_free_bundle()) {
                return;
            }
        }

        // Do not print banner on general config, as upsell already exists there in a box
        if('banner' == $location && isset($_GET['page']) && 'peepso_config'==$_GET['page'] && (!isset($_GET['tab']) || 'site'==$_GET['tab'])) {
            // We want banner to fire for Basic too
            if(!PeepSo3_Helper_Addons::license_is_basic_bundle()) {
                return '';
            }
        }

        // Override start date with these
        $ps_time = self::get_peepso_age();

        $delay_1 = 24;      // hours
        $delay_2 = 24*7*1;  // 1 week
        $delay_3 = 24*7*2;  // 2 weeks
        $delay_4 = 24*7*3;  // 3 weeks
        $delay_5 = 24*7*4;  // 4 weeks


        $discount = 0;

        // No license (fresh install / PeepSo Foundation only - except banner and expired license warning
        if (!strlen($license) && !in_array($location,['banner','maybe_expired_license'])) {

            // If X time passed, print discount
            if($ps_time) {
                if ($ps_time > $delay_1) { $discount = 1; }
                if ($ps_time > $delay_2) { $discount = 2; }
                if ($ps_time > $delay_3) { $discount = 3; }
                if ($ps_time > $delay_4) { $discount = 4; }
                if ($ps_time > $delay_5) { $discount = 5; }
            }
        }

        // Basic - show only in general config in banner
        elseif('banner' == $location && PeepSo3_Helper_Addons::license_is_basic_bundle()) {
            $discount = 21;
            if($ps_time) {
                if ($ps_time > $delay_2) { $discount = 22; }
                if ($ps_time > $delay_3) { $discount = 23; }
                if ($ps_time > $delay_4) { $discount = 24; }
                if ($ps_time > $delay_5) { $discount = 25; }
            }
        }

        // Free Bundle (except "maybe expired")
        elseif (PeepSo3_Helper_Addons::license_is_free_bundle() && !in_array($location,['maybe_expired_license'])) {
            // By default use discount=1
            $discount = 1;

            // If X time passed, use discount=2
            if ($ps_time) {
                if ($ps_time > $delay_2) { $discount = 2; }
                if ($ps_time > $delay_3) { $discount = 3; }
                if ($ps_time > $delay_4) { $discount = 4; }
                if ($ps_time > $delay_5) { $discount = 5; }
            }
        }

        // Maybe Expired, but not for Basic as they have a permanent one
        elseif (in_array($location,['maybe_expired_license']) && !PeepSo3_Helper_Addons::license_is_basic_bundle()) {

            // Reset cache if license is being saved
            $data = PeepSo3_Helper_Addons::license_to_data(NULL, !isset($_REQUEST['bundle_license']));

            if(is_array($data) && isset($data['expiration'])) {
                $expired_since = intval((strtotime(current_time('Y-m-d H:i:s')) - strtotime($data['expiration'])) / 3600);

                if($expired_since < 0) { return; }

                // Show monthly to expired
                $args['show_monthly'] = TRUE;

                if ($expired_since > 24)        { $discount = 11; }
                if ($expired_since > $delay_2)  { $discount = 12; }
                if ($expired_since > $delay_3)  { $discount = 13; }
                if ($expired_since > $delay_4)  { $discount = 14; }
                if ($expired_since > $delay_5)  { $discount = 15; }

            } else {
                return;
            }
        }

        // Nothing
        else {
            return;
        }

        $args=[];

        // Show monthly to PFBs and Basic
        if (PeepSo3_Helper_Addons::license_is_free_bundle() || PeepSo3_Helper_Addons::license_is_basic_bundle()) {
            $args['show_monthly'] = TRUE;
        }

        $args['expired_since']= isset($expired_since) ? $expired_since : 0;

        if (!strlen($license)) {
            $args['show_free'] = 1;
        }

        if($location) {
            $args['location'] = $location;
        }

        if($discount) {
            $args['discount'] = $discount;
        }

        $args['installer_url'] = admin_url('admin.php?page=peepso-installer&action=peepso-free');

        return PeepSo3_Helper_Remote_Content::get('upsell',$cache, $args);
    }

    public static function get_addons() {

        $has_new = FALSE;

        if(isset($_REQUEST['nocache'])) {
            PeepSo3_Mayfly::del('bundle_info');
        }

        $url = ['https://','www.', 'peepso','.com'];
        $url = implode('', $url);
        $url .= "/?product_bundles_list";
        $bundle_info = PeepSo3_Mayfly::get('bundle_info');

        if (!$bundle_info) {
            global $wp_version;


            $vars = [
                'ver_bundle'    => PeepSo3_Helper_Addons::license_to_name(),
                'ver_wp'        => $wp_version,
                'ver_php'       => PHP_VERSION,
                'ver_locale'    => get_locale(),
                'theme'         => wp_get_theme()->get('Name'),
            ];

            foreach($vars as &$var) {
                $var = urlencode($var);
            }

            foreach($vars as $k => $v) {
                $url .= "&$k=$v";
            }

            $request = wp_remote_get($url, ['timeout' => 15, 'sslverify' => TRUE]);

            if (is_wp_error($request)) {
                $request = wp_remote_post($url, ['timeout' => 15, 'sslverify' => FALSE]);
            }

            if (!is_wp_error($request)) {
                $bundle_info = json_decode(wp_remote_retrieve_body($request));
                PeepSo3_Mayfly::set('bundle_info', $bundle_info, 3600);

                foreach($bundle_info as $item) {
                    if(isset($item->new)) {
                        $has_new = $item->id;
                        break;
                    }
                }

                if($has_new) {
                    PeepSo3_Mayfly_Int::set('installer_has_new', $has_new);
                } else {
                    PeepSo3_Mayfly_Int::del('installer_has_new');
                }
            }
        }

        return $bundle_info;
    }

    public static function maybe_powered_by_peepso() {

        if(PeepSo::get_option('system_show_peepso_link',0)) {
            return PeepSo3_Helper_Remote_Content::get('free_bundle_branding');
        }

        if(apply_filters('peepso_free_bundle_should_brand', FALSE) && self::license_is_free_bundle()) {
            $PeepSoConfigSettings = PeepSoConfigSettings::get_instance();
            $PeepSoConfigSettings->set_option('system_show_peepso_link', 1);
            return PeepSo3_Helper_Remote_Content::get('free_bundle_branding');
        }

        return FALSE;
    }

    public static function maybe_optin_stats() {

        if(PeepSo::get_option('optin_stats',0)) {
            return TRUE;
        }

        if(self::license_is_free_bundle()) {
            PeepSoConfigSettings::get_instance()->set_option('optin_stats', 1);
            return TRUE;
        }

        return PeepSo::get_option('optin_stats',0);
    }

    public static function maybe_installer_has_new() {
        return (PeepSo3_Mayfly_Int::get('installer_has_new') || !self::get_license());
    }


    public static function get_license() {
        $license = (isset($_REQUEST['bundle_license'])) ? $_REQUEST['bundle_license'] : PeepSo::get_option('bundle_license');

        if(!$license) return FALSE;

        return strlen($license) ? $license : FALSE;
    }

    /**
     * Retrieve the age of PeepSo installation in HOURS
     * @return int amount of HOURS passed since PeepSo installation
     */
    public static function get_peepso_age() {
        $start_date = isset($_GET['peepso_start_date']) ? $_GET['peepso_start_date'] : get_option('peepso_install_date');

        $ps_start= strtotime($start_date);
        $ps_time = 0;
        if($ps_start) {
            $ps_time = intval((strtotime(current_time('Y-m-d H:i:s')) - $ps_start) / 3600);
        }

        return $ps_time;
    }
}

add_action('admin_notices', function() {

    if(!PeepSo::is_admin()) { return; }

    $mayfly = 'user_'.get_current_user_id().'upsell_dismiss';

    if(isset($_GET['peepso_upsell_dismiss_reset'])) {
        PeepSo3_Mayfly::del($mayfly);
        PeepSo3_Utility_Redirect::_(remove_query_arg('peepso_upsell_dismiss_reset'));
    }

    if(isset($_GET['peepso_upsell_dismiss'])) {
        PeepSo3_Mayfly::set($mayfly,1,24*3600);
        PeepSo3_Utility_Redirect::_(remove_query_arg('peepso_upsell_dismiss'));
    }

    if(PeepSo3_Mayfly::get($mayfly)) { return; }

    $upsell = '';

    $upsell = PeepSo3_Helper_Addons::get_upsell('admin_top');
    if(!empty($upsell)) {
        ?>
        <div style="clear:both">
        <a href="<?php echo add_query_arg(['peepso_upsell_dismiss'=>1]);?>">Dismiss</a>
        <?php
        echo $upsell;
        echo "</div>";
    }
},0,99);