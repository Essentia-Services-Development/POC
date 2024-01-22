<?php
/**
 *  Create A Simple Theme Options Panel
 *
 */

//  Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

//  Start Class
if ( ! class_exists( 'Gecko_Theme_License' ) ) {

    class Gecko_Theme_License {

        const PEEPSO_HOME = 'https://www.peepso.com';
        const PEEPSO_LICENSE_TRANS = 'peepso_license_';
        const PEEPSOCOM_LICENSES = 'http://tiny.cc/peepso-licenses';
        const OPTION_DATA = 'gecko_license_data';
        private static $_licenses = NULL;

        const THEME_NAME = 'PeepSo Theme: Gecko';
        const THEME_VERSION = '6.2.7.0'; // keep single spaces here, I need them for search.replace - Matt
        const THEME_RELEASE = ''; //ALPHA1, BETA10, RC1, '' for STABLE // keep single spaces here, I need them for search.replace - Matt
        const THEME_EDD 	= 7354103;
        const THEME_SLUG 	 = 'peepso-theme-gecko';

        /**
         * Autoload method
         * @return void
         */
        public function __construct() {
            // We only need to register the admin panel on the back-end
            if ( is_admin() ) {
                add_action( 'admin_menu', array( 'Gecko_Theme_License', 'register_sub_menu') );
                add_filter( 'gecko_sanitize_option', array('Gecko_Theme_Settings', 'sanitize'));

                // PeepSo.com license check
                if (!self::check_license(self::THEME_EDD, self::THEME_SLUG)) {
                    add_action('admin_notices', array( 'Gecko_Theme_License', 'license_notice'));
                }

                self::check_updates(self::THEME_EDD, self::THEME_SLUG, self::THEME_VERSION, __FILE__);
            }
        }

        /**
         * Sanitization callback
         *
         * @since 1.0.0
         */
        public static function sanitize( $options ) {

            // If we have options lets sanitize them
            if ( isset($options['gecko_license']) ) {

                // License input
                if ( ! empty( $options['gecko_license'] ) ) {
                    $options['gecko_license'] = sanitize_text_field( $options['gecko_license'] );
                } else {
                    $options['gecko_license'] = '';
                }

            }

            // Return sanitized options
            return $options;

        }

        /**
         * Register submenu
         * @return void
         */
        public static function register_sub_menu() {
            if(!isset($_SERVER['HTTP_HOST']) || 'demo.peepso.com' != $_SERVER['HTTP_HOST'] ) {
                add_submenu_page(
                    'gecko-settings', 'Gecko License', 'License', 'manage_options', 'gecko-license', array('Gecko_Theme_License', 'submenu_page_callback')
                );
            }
        }

        /**
         * Render submenu
         * @return void
         */
        public static function submenu_page_callback() {

            self::delete_transient(self::THEME_SLUG);

            $response = array();
            $response_details = array();

            self::activate_license(self::THEME_SLUG, self::THEME_NAME);

            $valid = (int)self::check_license(self::THEME_NAME, self::THEME_SLUG, TRUE);
            $license = self::get_license(self::THEME_SLUG);

            $details = '';
            $message = '';
            $color = '#e53935';

            if($valid) {
                if (isset($license['expire']) && $license['expire']) {
                    $expires = strtotime($license['expire']);

                    if ($expires > time()) {
                        $color = '#689f38';
                        $message = sprintf(__('%s remaining', 'peepso-theme-gecko'), human_time_diff_round_alt($expires));
                    } else {
                        $message = sprintf(__('Expired on %s', 'peepso-theme-gecko'), date('d-M-Y', $expires));
                    }
                }
            } else {
                $message = sprintf(__('The license is invalid (%s).', 'peepso-theme-gecko'), '<i>'.$license['response'].'</i>');
            }

            if(isset($license['expire']) && strstr($license['expire'], '1999')) {
                $message = __('Your license can\'t be checked because of an API request limit.<br/> If the problem persists, please contact <a href="https://www.peepso.com/contact" target="_blank">PeepSo Support</a>.', 'peepso-theme-gecko');
            }

            $details = sprintf('<span style="color:%s">%s</span>', $color, $message);

            ?>
            <form method="post" action="">
              <div class="gca-dash">
                <div class="gca-dash__inner">
                  <div class="gca-dash__header">
                    <div class="gca-dash__logo"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/gecko.png" alt="Gecko" /></div>

                    <div class="gca-dash__title"><?php esc_html_e( 'License', 'peepso-theme-gecko' ); ?></div>

                    <div class="gca-dash__actions">
                      <input type="hidden" name="gecko-config-nonce" value="<?php echo wp_create_nonce('gecko-config-nonce') ?>"/>
                      <?php submit_button(); ?>
                    </div>
                  </div>

                  <div class="gca-dash__main">
                    <div class="gca-dash__sidebar">
                      <div class="gca-dash__menu">
                        <a href="admin.php?page=gecko-settings" class="gca-dash__menu-link"><i class="gcis gci-tools"></i><?php esc_html_e( 'Settings', 'peepso-theme-gecko' ); ?></a>
                        <a href="admin.php?page=gecko-customizer" class="gca-dash__menu-link"><i class="gcis gci-swatchbook"></i><?php esc_html_e( 'Gecko Customizer', 'peepso-theme-gecko' ); ?></a>
                        <a href="admin.php?page=gecko-page-builders" class="gca-dash__menu-link"><i class="gcis gci-file-alt"></i><?php esc_html_e( 'Page Builders', 'peepso-theme-gecko' ); ?></a>
                        <?php if(!isset($_SERVER['HTTP_HOST']) || 'demo.peepso.com' != $_SERVER['HTTP_HOST'] ) { ?>
                        <a href="admin.php?page=gecko-license" class="gca-dash__menu-link gca-dash__menu-link--active"><i class="gcis gci-key"></i><?php esc_html_e( 'License', 'peepso-theme-gecko' ); ?></a>
                        <?php } ?>
                      </div>
                    </div>

                    <div class="gca-dash__content">
                      <div class="gca-dash__settings">
                        <!-- REDIRECTS -->
                        <div class="gca-dash__settings-group">
                          <div class="gca-dash__settings-name"><i class="gcis gci-key"></i><?php esc_html_e( 'License', 'peepso-theme-gecko' ); ?></div>

                          <div class="gca-dash__options gca-dash__options--single">
                            <?php settings_fields( 'gecko_options' ); ?>

                            <div class="gca-dash__option">
                              <div class="gca-dash__option-name"><?php esc_html_e( 'License key', 'peepso-theme-gecko' ); ?></div>
                              <?php
                              $gecko_settings = GeckoConfigSettings::get_instance();
                              $value = $gecko_settings->get_option( 'gecko_license' );
                              ?>
                              <input type="text" name="gecko_options[gecko_license]" id="gecko_license" value="<?php echo esc_attr( $value ); ?>">
                              <div class="gca-dash__option-desc">
                                <p><i class="gcis gci-info-circle"></i><?php echo $details; ?></p>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="gca-dash__foot">
                        <div class="gca-dash__ver">
                          <?php esc_html_e( 'Version', 'peepso-theme-gecko' ); ?>: <?php echo wp_get_theme()->version ?>
                        </div>
                        <div class="gca-dash__actions">
                          <input type="hidden" name="gecko-config-nonce" value="<?php echo wp_create_nonce('gecko-config-nonce') ?>"/>
                          <?php submit_button(); ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </form>
            <?php
        }


        /* Licensing */

        public static function license_notice()
        {
            self::_license_notice(self::THEME_NAME, self::THEME_SLUG);
        }

        public function license_notice_forced()
        {
            self::_license_notice(self::THEME_NAME, self::THEME_SLUG, true);
        }


        /**
         * Show message if peepsofriends can not be installed or run
         */
        public static function _license_notice($theme_name, $theme_slug, $forced=FALSE)
        {
            $license_data = self::get_license($theme_slug);

            self::activate_license($theme_slug,$theme_name);

            $license_data = self::get_license($theme_slug);

            $message =
                "<strong>"
                . __('Gecko license is missing or invalid (%s). Please enter a valid license and click "SAVE" to activate it.', 'peepso-theme-gecko')
                . "</strong> "
                . __('<a href="%s">Review your Gecko license</a>.', 'peepso-theme-gecko');

            $message = sprintf($message, '<i>'.$license_data['response'].'</i>', admin_url('admin.php?page=gecko-license'));

            echo '<div class="error peepso" id="peepso_license_error_combined"><strong>';
            echo $message;
            echo '</strong></div>';
        }

        /**
         * Verifies the license key for an add-on by the plugin's slug
         * @param string $theme_edd The THEME_NAME constant value for the plugin being checked
         * @param string $theme_slug The THEME_SLUG constant value for the plugin being checked
         * @return boolean TRUE if the license is active and valid; otherwise FALSE.
         */
        public static function check_license($theme_edd, $theme_slug, $is_admin = FALSE)
        {
            if( FALSE === $is_admin) {
                $is_admin = is_admin();
            }

            $license_data = self::_get_product_data($theme_slug);

            if (NULL === $license_data) {
                // no license data exists; create it
                $license_data['slug'] = $theme_slug;
                $license_data['name'] = $theme_edd;
                $license_data['license'] = '';
                $license_data['state'] = 'invalid';
                $license_data['response'] = 'invalid';
                $license_data['expire'] = 0;
                $license_data['was_null'] = TRUE;
                // write the license data
            }

            if ($is_admin) {
                self::_set_product_data($theme_slug, $license_data);
                self::activate_license($theme_slug, $theme_edd);
            } else {
                // Frontend will return "FALSE" only in some scenarios
                if (!self::is_valid_key($theme_slug)) {

                    /*
                     * $license_data['response']
                     *
                     * invalid				FALSE - key BAD
                     * inactive				FALSE - key OK, not active
                     * item_name_mismatch 	FALSE - key OK, wrong plugin
                     * missing              FALSE - key doesn't exist
                     * site_inactive		TRUE  - key OK, wrong domain
                     * expired				TRUE  - key OK, license expired
                     */

                    if(!array_key_exists('response', $license_data)) {
                        $license_data['response'] = 'valid';
                    }

                    switch ($license_data['response']) {
                        case 'invalid':
                        case 'inactive':
                        case 'item_name_mismatch':
                        case 'missing':
                            return FALSE;
                            break;
                        default:
                            return TRUE;
                            break;
                    }
                }
            }

            // check to see if the license key is valid for the named plugin
            return (self::is_valid_key($theme_slug));
        }

        public static function get_license($theme_slug)
        {
            return self::_get_product_data($theme_slug);
        }

        public static function delete_transient($theme_slug)
        {
            $trans_key = self::trans_key($theme_slug);
            delete_transient($trans_key);
        }

        private static function trans_key($theme_slug)
        {
            return self::PEEPSO_LICENSE_TRANS . $theme_slug;
        }

        /**
         * Activates the license key for a PeepSo add-on
         * @param string $theme_slug The add-on's slug name
         * @param string $theme_edd The add-on's full plugin name
         * @return boolean TRUE on successful activation; otherwise FALSE
         */
        public static function activate_license($theme_slug, $theme_edd)
        {

            // how long to keep the transient keys?
            $trans_lifetime = 24 * HOUR_IN_SECONDS;

            // get key stored from config pages
            $key = self::_get_key($theme_slug);

            $license_data['license'] = $key;
            $license_data['name'] = $theme_edd;

            if (FALSE === $key || 0 === strlen($key)) {
                return;
            }

            // when asking EDD API use "item_id" if plugin_edd is numeric, otherwise "item_name"
            $key_type = 'item_name';

            if(is_numeric($theme_edd)) {
                $key_type = 'item_id';
                $theme_edd = (int) $theme_edd;
            }

            $args = array(
                'edd_action' => 'activate_license',
                'license' => $key,
                $key_type => $theme_edd,
                'url' => home_url(),
            );

            // Use transient key to check for cached values
            $trans_key = self::trans_key($theme_slug);

            // If there is no cached value, call home
            $validation_data = get_transient($trans_key);

            if ( !is_object($validation_data) ) {

                $peepso_is_offline = FALSE;

                if(strlen(self::_get_transient('peepso_is_offline'))) {
                    $peepso_is_offline = TRUE;
                } else {
                    $resp = wp_remote_get(add_query_arg($args, self::PEEPSO_HOME),	// contact the home office
                        array('timeout' => 10, 'sslverify' => FALSE, 'user-agent' => ''));				// options

                    if(is_wp_error($resp)) {
                        $peepso_is_offline = TRUE;
                        self::_set_transient('peepso_is_offline', 1, 3600);
                    }
                }

                if ($peepso_is_offline) {
                    $trans_lifetime = 1 * HOUR_IN_SECONDS;

                    $validation_data = new stdClass();

                    $validation_data->success = true;

                    $validation_data->license 			= 'valid';
                    $validation_data->item_name 		= $theme_slug;
                    $validation_data->expires			= date('Y-m-d H:i:s', strtotime('next year'));
                    $validation_data->payment_id		= 0;
                    $validation_data->customer_name 	= 'temporary';
                    $validation_data->customer_email	= 'temporary@peepso.com';
                    $validation_data->license_limit 	= 0;
                    $validation_data->site_count		= 0;
                    $validation_data->activations_left 	= 'unlimited';
                } else {
                    $response = wp_remote_retrieve_body($resp);

                    $validation_data = json_decode($response);
                }
                set_transient($trans_key, $validation_data, $trans_lifetime);
            }

            $license_data['expire'] = isset($validation_data->expires) ? $validation_data->expires : NULL;

            if ('valid' === $validation_data->license) {
                // if parent site reports the license is active, update the stored data for this plugin
                $license_data['state'] = 'valid';
            } else {
                $license_data['state'] = 'invalid';
            }

            // remaining options
            $license_data['response'] = $validation_data->license;
            if(isset($validation_data->error)) {
                $license_data['response'] = $validation_data->error;
            }

            // save
            self::_set_product_data($theme_slug, $license_data);
        }

        /**
         * Loads the license information from the options table
         */
        private static function _load_licenses()
        {
            if (NULL === self::$_licenses) {
                $lisc = GeckoConfigSettings::get_instance()->get_option(self::OPTION_DATA, FALSE);
                if (FALSE === $lisc) {
                    $lisc = array();
                    add_option(self::OPTION_DATA, $lisc, FALSE, FALSE);
                }
                self::$_licenses = $lisc;
            }
        }

        /**
         * Retrieves product data for a given add-on by slug name
         * @param string $theme_slug The plugin's slug name
         * @return mixed The data array stored for the plugin or NULL if not found
         */
        private static function _get_product_data($theme_slug)
        {
            self::_load_licenses();
            $theme_slug = sanitize_key($theme_slug);

            if (isset(self::$_licenses[$theme_slug])) {
                // check license data for validity
                $data = self::$_licenses[$theme_slug];
                $str = md5($theme_slug . '|' . esc_html($data['name']) .
                    '~' . $data['license'] . ',' . $data['expire'] . $data['state']);

                // return data only if checksum validates
                if (isset($data['checksum']) && $str === $data['checksum'])
                    return ($data);
            }
            return (NULL);
        }

        /**
         * Sets the stored license information per product
         * @param string $theme_slug The plugin's slug
         * @param array $data The data array to store
         */
        private static function _set_product_data($theme_slug, $data)
        {
            /*
             * data:
             *	['slug'] = plugin slug
             *	['name'] = plugin name
             *	['license'] = license key
             *	['state'] = license state
             *	['expire'] = license expiration
             *	['checksum'] = checksum
             */

            $theme_slug = sanitize_key($theme_slug);
            $data['slug'] = $theme_slug;
            $str = $theme_slug . '|' . esc_html($data['name']) .
                '~' . $data['license'] . ',' . $data['expire'] . $data['state'];
            $data['checksum'] = md5($str);
            self::_load_licenses();
            self::$_licenses[$theme_slug] = $data;
            update_option(self::OPTION_DATA, self::$_licenses);
        }

        /**
         * Get the license key stored for the named plugin
         * @param string $theme_slug The THEME_SLUG constant value for the add-on to obtain the license key for
         * @return string The entered license key or FALSE if the named license key is not found
         */
        private static function _get_key($theme_slug)
        {
            return (GeckoConfigSettings::get_instance()->get_option( 'gecko_license' ));
        }

        /**
         * Determines if a key is valid and active
         * @param string $plugin Plugin slug name
         * @return boolean TRUE if the key for the named plugin is valid; otherwise FALSE
         */
        public static function is_valid_key($plugin)
        {
            self::_load_licenses();
            $theme_slug = sanitize_key($plugin);

            if (!isset(self::$_licenses[$theme_slug])) {
                return (FALSE);
            }

            $data = self::$_licenses[$theme_slug];

            $str = $theme_slug . '|' . esc_html($data['name']) .
                '~' . $data['license'] . ',' . $data['expire'] . $data['state'];

            $dt = new GeckoDate($data['expire']);

            return (md5($str) === $data['checksum'] && 'valid' === $data['state'] && $dt->TimeStamp() > time());
        }

        public static function get_key_state($plugin)
        {
            self::_load_licenses();
            $theme_slug = sanitize_key($plugin);
            if (!isset(self::$_licenses[$theme_slug])) {
                return "unknown";
            }

            $data = self::$_licenses[$theme_slug];

            return array_key_exists('response', $data) ? $data['response'] : 'unknown';
        }

        public static function dump_data()
        {
            self::_load_licenses();
            var_export(self::$_licenses);
        }

        // The old check_updates() doesn't know the difference between plugin_slug and plugin_edd
        // since 1.7.6 plugin_edd can be numeric and different from plugin_slug
        public static function check_updates( $theme_edd, $theme_slug, $theme_version, $file, $is_core = TRUE ) {

            // Version number is usually cached in a transient
            $trans = 'peepso_gecko_current_version';
            $trans_peepso_is_offline = 'peepso_is_offline';


            // Voodoo magic because WordPress geniuses say "that's not how transients work!"
            @delete_expired_transients(TRUE);
            if(class_exists('PeepSo') && class_exists('PeepSo3_Mayfly')) {
                PeepSo3_Mayfly::clr();
            }

            if(rand(1,100) == 66) { // Force delete our transients once every 100 loads, because some cache solutions will keep them forever
                self::_delete_transient($trans);
                self::_delete_transient($trans_peepso_is_offline);
            }
            // End voodoo magic

            $version = self::_get_transient($trans);

            // If not, get it from peepso.com
            if (!strlen($version)) {

                $version = 0;
                $url = 'https://cdn.peepso.com/versioning/version-gecko.txt';

                $peepso_is_offline = FALSE;
                if(strlen(self::_get_transient($trans_peepso_is_offline))) {
                    $peepso_is_offline = TRUE;
                }
                
                if(!$peepso_is_offline) {
                    // Attempt contact with PeepSo.com without sslverify
                    $resp = wp_remote_get(add_query_arg(array(), $url), array('timeout' => 10, 'sslverify' => FALSE));

                    // In some cases sslverify is needed
                    if (is_wp_error($resp)) {
                        $resp = wp_remote_get(add_query_arg(array(), $url), array('timeout' => 10, 'sslverify' => TRUE));
                        if(is_wp_error($resp)) {
                            $peepso_is_offline = TRUE;
                            self::_set_transient($trans_peepso_is_offline, 1, 3600);
                        }
                    }
                }

                // Definite failure - freeze the checks for a while
                if ($peepso_is_offline) {
                    // trigger_error('check_updates - failed to load version.txt from PeepSo.com');
                    self::_set_transient($trans, self::THEME_VERSION, 30);
                } else {
                    // Success - store the version in a 15 minute transient
                    $version = $resp['body'];
                    self::_set_transient($trans, $version, 15 * 60);
                }
            }

            if (1 != version_compare($version, $theme_version)) {
                return( FALSE );
            }

            // If neither if/else block returned FALSE, the version check will happen
            if( !class_exists( 'PeepSO_EDD_Theme_Updater' ) ) {
                include(dirname(__FILE__) . '/license_edd_helper.php');
            }

            $strings = array(
                'theme-license'             => __( 'Theme License', 'peepso-theme-gecko' ),
                'enter-key'                 => __( 'Enter your theme license key.', 'peepso-theme-gecko' ),
                'license-key'               => __( 'License Key', 'peepso-theme-gecko' ),
                'license-action'            => __( 'License Action', 'peepso-theme-gecko' ),
                'deactivate-license'        => __( 'Deactivate License', 'peepso-theme-gecko' ),
                'activate-license'          => __( 'Activate License', 'peepso-theme-gecko' ),
                'status-unknown'            => __( 'License status is unknown.', 'peepso-theme-gecko' ),
                'renew'                     => __( 'Renew?', 'peepso-theme-gecko' ),
                'unlimited'                 => __( 'unlimited', 'peepso-theme-gecko' ),
                'license-key-is-active'     => __( 'License key is active.', 'peepso-theme-gecko' ),
                'expires%s'                 => __( 'Expires %s.', 'peepso-theme-gecko' ),
                'expires-never'             => __( 'Lifetime License.', 'peepso-theme-gecko' ),
                '%1$s/%2$-sites'            => __( 'You have %1$s / %2$s sites activated.', 'peepso-theme-gecko' ),
                'license-key-expired-%s'    => __( 'License key expired %s.', 'peepso-theme-gecko' ),
                'license-key-expired'       => __( 'License key has expired.', 'peepso-theme-gecko' ),
                'license-keys-do-not-match' => __( 'License keys do not match.', 'peepso-theme-gecko' ),
                'license-is-inactive'       => __( 'License is inactive.', 'peepso-theme-gecko' ),
                'license-key-is-disabled'   => __( 'License key is disabled.', 'peepso-theme-gecko' ),
                'site-is-inactive'          => __( 'Site is inactive.', 'peepso-theme-gecko' ),
                'license-status-unknown'    => __( 'License status is unknown.', 'peepso-theme-gecko' ),
                'update-notice'             => __( "Updating this theme will lose any customizations you have made. 'Cancel' to stop, 'OK' to update.", 'peepso-theme-gecko' ),
                'update-available'          => __('<strong>%1$s %2$s</strong> is available. <a href="%3$s" class="thickbox" title="%4s">Check out what\'s new</a> or <a href="%5$s"%6$s>update now</a>.', 'peepso-theme-gecko' ),
            );

            $license = GeckoConfigSettings::get_instance()->get_option( 'gecko_license' );

            $key_name = 'item_name';
            if(is_numeric($theme_edd)) {
                $key_name = 'item_id';
            }

            $api_data = wp_parse_args( array(
                'remote_api_url' => self::PEEPSO_HOME,
                'license'        => trim( $license ),
                'theme_slug'     => self::THEME_SLUG,
                $key_name   	 => $theme_edd,
                'author'         => '',
            ) );
            $updater = new PeepSo_EDD_Theme_Updater( $api_data, $strings );
        }

        private static function _get_transient($trans) {
            if(class_exists('PeepSo') && class_exists('PeepSo3_Mayfly')) {
                return PeepSo3_Mayfly::get($trans);
            }

            return get_transient($trans);
        }

        private static function _set_transient($trans, $version, $ttl) {
            if(class_exists('PeepSo') && class_exists('PeepSo3_Mayfly')) {
                PeepSo3_Mayfly::set($trans, $version, $ttl);
            } else {
                set_transient($trans, $version, $ttl);
            }
        }

        private static function _delete_transient($trans) {
            if(class_exists('PeepSo') && class_exists('PeepSo3_Mayfly')) {
                PeepSo3_Mayfly::del($trans);
            } else {
                delete_transient($trans);
            }
        }
    }
}
new Gecko_Theme_License();
