<?php
/**
 * Plugin Name: PeepSo Monetization: Paid Memberships Pro
 * Plugin URI: https://peepso.com
 * Description: Memberships and access control for your PeepSo community. Requires the Paid Memberships Pro plugin.
 * Author: PeepSo
 * Author URI: https://peepso.com
 * Version: 6.2.7.0
 * Copyright: (c) 2016 PeepSo, Inc. All Rights Reserved.
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: peepso-pmp
 * Domain Path: /language
 *
 * We are Open Source. You can redistribute and/or modify this software under the terms of the GNU General Public License (version 2 or later)
 * as published by the Free Software Foundation. See the GNU General Public License or the LICENSE file for more details.
 * This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY.
 */


class PeepSoPMP
{
	private static $_instance = NULL;

	const PLUGIN_NAME	 = 'Monetization: Paid Memberships Pro';
	const PLUGIN_VERSION = '6.2.7.0';
	const PLUGIN_RELEASE = ''; //ALPHA1, BETA10, RC1, '' for STABLE
    const PLUGIN_EDD = 43387;
    const PLUGIN_SLUG = 'peepso-pmp-integration';

	public $widgets = array(
	);

    const THIRDPARTY_MIN_VERSION = '2.2.5';

    private static function ready_thirdparty() {
        $result = TRUE;

        if ( !defined('PMPRO_DIR')  || !version_compare( PMPRO_VERSION, self::THIRDPARTY_MIN_VERSION, '>=' )) {
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
        add_filter('peepso_license_config', function($list){
            $list[] = array(
                'plugin_slug' => self::PLUGIN_SLUG,
                'plugin_name' => self::PLUGIN_NAME,
                'plugin_edd' => self::PLUGIN_EDD,
                'plugin_version' => self::PLUGIN_VERSION
            );
            return ($list);
        }, 160);

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
            load_plugin_textdomain('peepso-pmp', FALSE, $path);
        });

        // Activation
        register_activation_hook(__FILE__, array(&$this, 'activate'));

        /** VERSION LOCKED hooks **/
        if(self::ready()) {
            if (!PeepSoLicense::check_license(self::PLUGIN_EDD, self::PLUGIN_SLUG)) {
                return;
            }
			
            add_action('peepso_init', array(&$this, 'init'));
            add_filter('peepso_widgets', array(&$this, 'hide_widget'), 40, 1);
        }
	}

	/*
	 * retrieve singleton class instance
	 * @return instance reference to plugin
	 */
	public static function get_instance()
	{
		if (NULL === self::$_instance)
			self::$_instance = new self();
		return (self::$_instance);
	}

	/*
	 * Initialize the PeepSoPMP plugin
	 */
	public function init()
	{
		PeepSo::add_autoload_directory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR);
		PeepSoTemplate::add_template_directory(plugin_dir_path(__FILE__));

        if (PeepSo::get_option('pmp_integration_hide_membership_tab', 0) == 0) {
            add_filter('peepso_navigation_profile', function($links) {
                if (PeepSo::get_option_new('pmp_integration_enabled') && (is_admin() || (isset($links['_user_id']) && get_current_user_id() == $links['_user_id']))) {

                    $slug = PeepSo::get_option('pmp_navigation_profile_slug', 'membership', TRUE);

                    $links[$slug] = array(
                        'href' => $slug,
                        'label' => PeepSo::get_option('pmp_navigation_profile_label', __('Membership', 'peepso-pmp'), TRUE),
                        'icon' => 'gcis gci-address-card'
                    );
                }

                return $links;
            });
        }

		if (is_admin()) {

            add_action('admin_init', array(&$this, 'peepso_check'));
            add_filter('peepso_admin_config_tabs', array(&$this, 'admin_config_tabs'));

            // PeepSo PMP Integration with PeepSo Groups
            if(class_exists('PeepSoGroupsPlugin')) {
            	add_action('pmpro_membership_level_after_other_settings', array(&$this, 'action_after_other_settings_group'));
            	add_action('pmpro_save_membership_level', array(&$this, 'action_after_save_membership_level_group'), 10, 1);
            	add_action('pmpro_delete_membership_level', array(&$this, 'action_after_delete_membership_level_group'), 10, 1);
            }

            // PeepSo PMP Integration with PeepSo VIP
            if(class_exists('PeepSoVIP')) {
            	add_action('pmpro_membership_level_after_other_settings', array(&$this, 'action_after_other_settings_vip'));
            	add_action('pmpro_save_membership_level', array(&$this, 'action_after_save_membership_level_vip'), 10, 1);
            	add_action('pmpro_delete_membership_level', array(&$this, 'action_after_delete_membership_level_vip'), 10, 1);

            	add_action( 'personal_options_update', array(&$this, 'save_membership_user_profile_fields' ), 100);
                add_action( 'edit_user_profile_update', array(&$this, 'save_membership_user_profile_fields' ), 100);
			}

            add_action('pmpro_delete_order', array(&$this, 'action_delete_order'), 20, 2);
			add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));
        } else {

			add_action('wp_login', array(&$this, 'after_login'));
			add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));

			// call after registration complete
			if (PeepSo::get_option('pmp_integration_enable_upon_registration', 1)) {
				add_action('peepso_register_new_user', array(&$this, 'redirect_to_pmpro'), 100, 1);
				add_action('peepso_register_segment_fail_membership', array(&$this, 'fail_membership'), 10, 1);

				$current_url = $_SERVER['REQUEST_URI'];
				if (strpos($current_url, '?') !== FALSE) {
					$current_url = explode('?', $current_url);
					$current_url = $current_url[0];
				}

				if (class_exists('TRP_Translate_Press')) {
					$trp_settings = get_option('trp_settings');
					if (isset($trp_settings['add-subdirectory-to-default-language']) && $trp_settings['add-subdirectory-to-default-language'] == 'yes') {
						$current_url = explode('/', $current_url);
						unset($current_url[1]);
						$current_url = implode('/', $current_url);
					}
				}

				$page = get_page_by_path($current_url);
				if ($page && strpos($page->post_content, '[pmpro_') !== FALSE) {

					$method = PeepSo::get_option('pmp_integration_user_login_state', 'cookie');
					if($method == 'session') {
						// call session_start only on pmp pages
						if ( ! session_id() ) {
							session_start([
								'read_and_close' => true,
							]);
						}

						$registered_user_id = isset($_SESSION['peepso_user_id_after_register']) ? $_SESSION['peepso_user_id_after_register'] : false;
					} else {
						$registered_user_id = isset($_COOKIE['peepso_user_id_after_register']) ? $_COOKIE['peepso_user_id_after_register'] : false;
					}


					if ( false !== ( $registered_user_id ) ) {
						if (! is_user_logged_in()) {
							wp_set_current_user($registered_user_id);
						}
					}
				}

				add_action('pmpro_after_checkout', array(&$this, 'after_checkout'), 10, 2);
				add_filter('pmpro_pages_shortcode_confirmation', array(&$this, 'modify_confirmation_content'), 10, 1);
			}

			add_filter('peepso_mailqueue_allowed', function($user_id, $is_allowed) {
				$integration = intval(PeepSo::get_option('pmp_integration_enabled'));
				$email_notification = intval(PeepSo::get_option('pmp_integration_email_notification_enabled', 0));

				if (1 !== $integration || 1 === $email_notification) {
					return TRUE;
				}

				$allowed_caller = array(
					'send_activation',
					'resend_activation',
					'retrieve_password'
				);
				foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $caller) {
					if (in_array($caller['function'], $allowed_caller)) {
						return TRUE;
					}
				}

				global $wpdb;
				$sql = $wpdb->prepare("SELECT COUNT(*) AS total FROM $wpdb->pmpro_memberships_users WHERE user_id = %d AND status = 'active'", $user_id);
				$result = $wpdb->get_row($sql);
				if (isset($result) && $result->total > 0) {
					return TRUE;
				} else {
					return FALSE;
				}

			}, 10, 2);

            $profile_slug = PeepSo::get_option('pmp_navigation_profile_slug', 'membership', TRUE);
            add_action('peepso_profile_segment_'.$profile_slug,   function() {
                PeepSoTemplate::exec_template('pmp','profile-membership');
            });

            add_filter('peepso_groups_follower_send_notification_email', function($sendNotificationEmail, $user) {
            	//is there an end date?
				$membership_level = pmpro_getMembershipLevelForUser($user->get_id());
				$end_date = (!empty($membership_level) && !empty($membership_level->enddate)); // Returned as UTC timestamp
				$wp_tz =  get_option( 'timezone_string' );

				// Convert UTC to local time
	            if ( $end_date ) {
		            $membership_level->enddate = strtotime( $wp_tz, $membership_level->enddate );
	            }

	            if($end_date && (intval($membership_level->enddate) <= strtotime(current_time("timestamp")))) {
	            	return FALSE;
	            }

            	return $sendNotificationEmail;
            }, 10, 2);
        }

		add_action('pmpro_before_change_membership_level', array(&$this, 'before_change_membership_level'), 10, 4);
		add_action('pmpro_after_change_membership_level', array(&$this, 'after_change_membership_level'), 10, 3);

		if (!wp_doing_ajax()) {
			add_action('peepso_profile_completeness_redirect', array(&$this, 'action_membership_completeness_redirect'));
		}

		add_filter('peepso_group_member_actions', array($this, 'group_member_actions'), 10, 2);
		if (isset($_GET['peepso-group-id'])) {
			add_filter('pmpro_levels_array', array($this, 'pmpro_levels_array'));
		}

 		// Compare last version stored in transient with current version
 		if( $this::PLUGIN_VERSION.$this::PLUGIN_RELEASE != PeepSo3_Mayfly::get($mayfly = 'peepso_'.$this::PLUGIN_SLUG.'_version')) {
 			PeepSo3_Mayfly::set($mayfly, $this::PLUGIN_VERSION.$this::PLUGIN_RELEASE);
 			$this->activate();
 		}
	}

	/**
	 * PeepSo PMP integration membership completeness
	 *
	 */
	public function action_membership_completeness_redirect() {

		if(!get_current_user_id()) {
			return false;
		}

		// check if site have exist levels
		$levels = pmpro_getAllLevels();
		if(count($levels) === 0 ) {
			return true;
		}

		if(pmpro_hasMembershipLevel()) {
			return TRUE;
		}

		$section = 'pmp_integration_';
		$integration = intval(PeepSo::get_option($section . 'enabled'));
		if(1 !== $integration) {
			return TRUE;
		}

		$url_levels = pmpro_url("levels");
		if(empty($url_levels)) {
			return TRUE;
		}

		// check if site have exist levels
		if(!empty($url_levels)) {
			if (PeepSo::get_option($section . 'force_complete_membership', 1)) {
				PeepSo::redirect($url_levels);
				exit;
			}
		}

		return TRUE;
	}

	public function admin_enqueue_scripts() {
		$input = new PeepSoInput();
        // SQL injection safe
		if ($input->value('page', '', FALSE) == 'peepso_config' && $input->value('tab', '', FALSE) == 'pmp-integration') {
			wp_register_script('peepso-admin-config-peepso-pmp',
				PeepSo::get_asset('peepso-admin-config.js', __FILE__),
				array('jquery'), self::PLUGIN_VERSION, TRUE);

			wp_enqueue_script('peepso-admin-config-peepso-pmp');
		}
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

		if(!self::ready_thirdparty()) {
            add_action('admin_notices', function() {
                if(method_exists('PeepSo','third_party_warning')) {
                    PeepSo::third_party_warning('Paid Memberships Pro','paid-memberships-pro',FALSE,self::THIRDPARTY_MIN_VERSION, self::PLUGIN_NAME);
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

	/**
	 * Display a message about PeepSo not present
	 */
	public function peepso_disabled_notice()
	{
		?>
		<div class="error peepso">
			<strong>
				<?php echo sprintf(__('The %s plugin requires the PeepSo plugin to be installed and activated.', 'peepso-pmp'), self::PLUGIN_NAME);?>
				<a href="plugin-install.php?tab=plugin-information&plugin=peepso-core&TB_iframe=true&width=772&height=291" class="thickbox">
					<?php echo __('Get it now!', 'peepso-pmp');?>
				</a>
			</strong>
		</div>
		<?php
	}


    public function version_notice()
    {
        PeepSo::version_notice(self::PLUGIN_NAME, self::PLUGIN_SLUG, $this->version_check);
    }


    /*
     * Called on first activation
     */
	public function activate()
	{
		if (!$this->peepso_check()) {
			return (FALSE);
		}

		require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'activate.php');
		$install = new PeepSoPMPInstall();
		$res = $install->plugin_activation();
		if (FALSE === $res) {
			// error during installation - disable
			deactivate_plugins(plugin_basename(__FILE__));
		}

		return (TRUE);
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
		$tabs['pmp-integration'] = array(
			'label' => __('PMP', 'peepso-pmp'),
            'icon' => 'https://cdn.peepso.com/icons/plugins/'.self::PLUGIN_EDD.'.svg',
			'tab' => 'pmp-integration',
			'description' => __('PeepSo PMP Integration', 'peepso-pmp'),
			'function' => 'PeepSoConfigSectionPMPIntegration',
            'cat'   => 'monetization',
		);

		return $tabs;
	}

    /**
     * Hide about me widget when user view register page
     * @param array $widgets an array of peepso widgets
     * @return array $widgets
     */
    public function hide_widget($widgets)
    {
    	$section = 'pmp_integration_';
		$integration = intval(PeepSo::get_option($section . 'enabled'));
		if(1 !== $integration) {
			return $widgets;
		}

    	// get current URL
    	$curr_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    	$path = trim(substr($curr_url, strlen(site_url())),'/');
		$parts = explode('/', $path);

		// get PMP account page
		$pmpro_account = get_permalink(get_option('pmpro_account_page_id'));
		$path2 = trim(substr($pmpro_account, strlen(site_url())),'/');

		if (class_exists('TRP_Translate_Press')) {
			$trp_settings = get_option('trp_settings');
			if (isset($trp_settings['add-subdirectory-to-default-language']) && $trp_settings['add-subdirectory-to-default-language'] == 'yes') {
				$path2 = explode('/', $path2);
				unset($path2[0]);
				$path2 = implode('/', $path2);
			}
		}

		$parts2 = explode('/', $path2);

		// check if user logged in and page is PMP account page
		if($parts2[0] === $parts[0] && !is_user_logged_in()) {
			#$key = array_search('PeepSoWidgetMe', $widgets);
	    	#if(array_key_exists($key, $widgets)) {
	    	#	unset($widgets[$key]);
	    	#}

	    	// unset all widget
	    	foreach ($widgets as $key => $value) {
	    		unset($widgets[$key]);
	    	}
		}

    	return $widgets;
    }

	/**
	 * After login hook.
	 */
	public function after_login()
	{
		// Removes registration session/cookie.
		$method = PeepSo::get_option('pmp_integration_user_login_state', 'cookie');
		if ($method == 'session') {
			if (isset($_SESSION['peepso_user_id_after_register']))	{
				unset($_SESSION['peepso_user_id_after_register']);
			}
		} else {
			if (isset($_COOKIE['peepso_user_id_after_register'])) {
                PeepSo3_Cookie::set('peepso_user_id_after_register', $_COOKIE['peepso_user_id_after_register'], time() - 3600);
			}
		}
	}

	/**
	 * Registers the needed scripts and styles
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_style('peepsopmpintegration', PeepSo::get_asset('pmp-integration.css', __FILE__), array('peepso'), self::PLUGIN_VERSION, 'all');
		wp_enqueue_script('peepsopmpintegration', PeepSo::get_asset('pmp-integration.js', __FILE__), array('peepso'), self::PLUGIN_VERSION, TRUE);
	}

	/**
	 * Redirect ro PMPro
	 */
	public function redirect_to_pmpro($user_id)
	{
		$section = 'pmp_integration_';
		$integration = intval(PeepSo::get_option($section . 'enabled'));
		if(1 !== $integration || empty(pmpro_url("levels"))) {
			return;
		}

		$u = PeepSoUser::get_instance($user_id);
		if(FALSE ===$u->get_email()) {
            nocache_headers();
			wp_redirect(PeepSo::get_page('register') . '?fail_membership/');
			exit;
		}

		// Add registration session/cookie.
		$method = PeepSo::get_option('pmp_integration_user_login_state', 'cookie');
		if ($method == 'session') {
			if (isset($_SESSION['peepso_user_id_after_register']))	{
				unset($_SESSION['peepso_user_id_after_register']);
			}

			$_SESSION['peepso_user_id_after_register'] = $user_id;
		} else {
            PeepSo3_Cookie::set('peepso_user_id_after_register', $user_id, time() + 3600);
		}

		// redirect to pmpro levels
        nocache_headers();
		wp_redirect(pmpro_url("levels"));
		exit;
	}

	/**
	 * after_checkout
	 */
	public function after_checkout($user_id, $morder)
	{
		// join to selected group
		if(class_exists('PeepSoGroupsPlugin'))
		{
			$membership_level_id = $morder->membership_id;
			if($membership_level_id) {
				$membershipLevelGroup = new PeepSoMembershipLevelGroup();
				$aGroup = $membershipLevelGroup->get_groups_by_level($membership_level_id);
				foreach ($aGroup as $key => $group) {
					$_model = new PeepSoGroupUser($group, $user_id);
					$_model->member_join();
					new PeepSoGroupFollower($group, $user_id);
				}
			}
		}

		// assign vip icons
		if(class_exists('PeepSoVIP'))
		{
			$membership_level_id = $morder->membership_id;
			if($membership_level_id) {
				$membershipLevelVIP = new PeepSoMembershipLevelVIP();
				$aVip = $membershipLevelVIP->get_vip_by_level($membership_level_id);
				update_user_meta( $user_id, 'peepso_vip_user_icon', '' );
				update_user_meta( $user_id, 'peepso_vip_user_icon', $aVip );
			}
		}

		do_action('peepso_action_pmp_checkout', $user_id, $morder);
	}

	/**
	 * Modify confirmation message on the registration page.
	 */
	public function modify_confirmation_content($content)
	{
		$is_registration = false;

		// Check registration state.
		$method = PeepSo::get_option('pmp_integration_user_login_state', 'cookie');
		if ($method == 'session') {
			$is_registration = isset($_SESSION['peepso_user_id_after_register']);
		} else {
			$is_registration = isset($_COOKIE['peepso_user_id_after_register']);
		}

		// Change the URL on registration.
		if ($is_registration) {
			$content .= '<script>(function(){
				var wrapper = document.querySelector(\'.pmpro_confirmation_wrap\');
				var action = wrapper.querySelector(\'.pmpro_actions_nav a\');
				action.href = \'' . PeepSo::get_page('register') . '?success\';
				action.innerHTML = \'' . __('Done', 'peepso-pmp' ) . '\';
			})();</script>';
		}

		return $content;
	}

	/**
	 * Before change membership level
	 */
	public function before_change_membership_level($membership_level_id, $user_id, $old_level, $cancel_level_id) {
		if (PeepSo::get_option('pmp_integration_remove_from_group', 0) === 1) {
			if (count($old_level) > 0) {
				$old_level_id = $old_level[0]->id;
				PeepSo3_Mayfly::set('peepso_pmp_old_level_' . $user_id, $old_level_id);
			}
		}
	}


	/**
	 * After change membership level
	 */
	public function after_change_membership_level($membership_level_id, $user_id, $cancel_level_id)
	{
		// join to selected group
		if (class_exists('PeepSoGroupsPlugin')) {
			$membershipLevelGroup = new PeepSoMembershipLevelGroup();

			if ($membership_level_id) {
				$aGroup = $membershipLevelGroup->get_groups_by_level($membership_level_id);

				if (PeepSo::get_option('pmp_integration_remove_from_group', 0) === 1) {
					$old_level = PeepSo3_Mayfly::get('peepso_pmp_old_level_' . $user_id);
					if ($old_level) {
						$aGroupCancel = $membershipLevelGroup->get_groups_by_level($old_level);

						foreach ($aGroupCancel as $key => $group) {
							if (!in_array($group, $aGroup)) {
								$_model = new PeepSoGroupUser($group, $user_id);
								$_model->member_leave();

								// remove follow
								$follower = new PeepSoGroupFollower($group, $user_id);
								$follower->delete();

							}
						}
						PeepSo3_Mayfly::del('peepso_pmp_old_level_' . $user_id);
					}
				}

				foreach ($aGroup as $key => $group) {
					$_model = new PeepSoGroupUser($group, $user_id);
					$_model->member_join();
					new PeepSoGroupFollower($group, $user_id);
				}
			} else if ($cancel_level_id) {
				$aGroupCancel = $membershipLevelGroup->get_groups_by_level($cancel_level_id);

				foreach ($aGroupCancel as $key => $group) {
					$_model = new PeepSoGroupUser($group, $user_id);
					$_model->member_leave();

					// remove follow
					$follower = new PeepSoGroupFollower($group, $user_id);
					$follower->delete();
				}
				PeepSo3_Mayfly::del('peepso_pmp_old_level_' . $user_id);
			}

		}

		// assign vip icons
		if(class_exists('PeepSoVIP') && ($membership_level_id))
		{
			$membershipLevelVIP = new PeepSoMembershipLevelVIP();
			$aVip = $membershipLevelVIP->get_vip_by_level($membership_level_id);
			update_user_meta( $user_id, 'peepso_vip_user_icon', '' );
			update_user_meta( $user_id, 'peepso_vip_user_icon', $aVip );
		}
	}

	/**
	 * Fail process membership
	 */
	public function fail_membership()
	{
		echo PeepSoTemplate::exec_template('pmp', 'fail_membership', array(), TRUE);
	}

	/*
	 * Methods below are used solely as an integration with the PMP membership level section
	 */
	public function action_after_other_settings_group()
	{
		$level_id = $_REQUEST['edit'];
		$require_membership = get_pmpro_membership_level_meta($level_id, 'peepso_group_require_membership', TRUE)
		?>
		<h3 class="topborder"><?php echo __('PeepSo Groups', 'peepso-pmp' );?></h3>
		<table class="form-table">
			<tr>
				<th scope="row" valign="top"><label><?php echo __('Require this membeship level', 'peepso-pmp'); ?></label></th>
				<td>
					<input id="peepso_group_require_membership" name="peepso_group_require_membership" type="checkbox" value="yes" <?php echo ($require_membership) ? 'checked="checked"' : ''; ?>>
					<label for="peepso_group_require_membership"><?php echo __('Check this to make selected <b>Private PeepSo Groups</b> require this membership level.'); ?></label>
				</td>
			</tr>
		</table>
		<p><?php echo __('Automatically add users who pick this membership level to the following groups.', 'peepso-pmp' );?></p>
		<table class="form-table">
		
            <tr class="user-admin-color-wrap">
                <td>
                    <fieldset id="peepso-groups" class="scheme-list">
                        <?php
                        $aGroups = PeepSoGroups::admin_get_groups(0, NULL, NULL, NULL, '', 'all');
                        $membershipLevelGroup = new PeepSoMembershipLevelGroup();
                        $selectedGroup = $membershipLevelGroup->get_groups_by_level($level_id);
                        foreach ($aGroups as $key => $group) {
                            ?>
                            <div class="color-option">
                            	<input name="peepsogroups[]" id="peepsogroups<?php echo $key;?>" type="checkbox" value="<?php echo $group->id;?>" class="tog" <?php echo (in_array($group->id, $selectedGroup)) ? ' checked=checked':'';?>>
                                <label for="peepsogroups<?php echo $key;?>">
                                <img src="<?php echo $group->get_avatar_url();?>" style="width: 32px; height: 32px;">
                                <?php echo $group->name;?>
                                <?php  if(intval($group->is_open)) { echo "<small>(".__('open', 'peepso-pmp').")</small>"; }  ?>
                                <?php  if(intval($group->is_closed)) { echo "<small>(".__('private', 'peepso-pmp').")</small>"; }  ?>
                                <?php  if(intval($group->is_secret)) { echo "<small>(".__('secret', 'peepso-pmp').")</small>"; }  ?>
                                </label>
                            </div>
                            <?php
                        }
                        ?>
                    </fieldset>
                </td>
            </tr>
        </table>
		<?php
	}

	public function action_after_save_membership_level_group($membership_level_id)
	{
		// save membership level groups
		if ($membership_level_id) {

			$membershipLevelGroup = new PeepSoMembershipLevelGroup();
			$groups = isset($_REQUEST['peepsogroups']) ? $_REQUEST['peepsogroups'] : array();
			$membershipLevelGroup->update_membership_level_group($membership_level_id, $groups);

			if (isset($_REQUEST['peepso_group_require_membership'])) {
				update_pmpro_membership_level_meta($membership_level_id, 'peepso_group_require_membership', TRUE);
			} else {
				delete_pmpro_membership_level_meta($membership_level_id, 'peepso_group_require_membership');
			}
			
			return true;
		}

		return false;
	}

	public function action_after_delete_membership_level_group($membership_level_id)
	{
		// clean up membership level group table.
		if($membership_level_id) {
			$membershipLevelGroup = new PeepSoMembershipLevelGroup();
			$groups = array();
			$membershipLevelGroup->update_membership_level_group($membership_level_id, $groups);

			return true;
		}

		return false;
	}



	/*
	 * Methods below are used solely as an integration with the PMP membership level section
	 */
	public function action_after_other_settings_vip()
	{
		?>
		<h3 class="topborder"><?php echo __('PeepSo VIP', 'peepso-pmp' );?></h3>
		<p><?php echo __('Automatically assign users a selected VIP icon who pick this membership level', 'peepso-pmp' );?></p>
		<table class="form-table">
            <tr class="user-admin-color-wrap">
                <td>
                    <fieldset id="peepso-groups" class="scheme-list">
                    	<?php
                        $PeepSoVipIconsModel = new PeepSoVipIconsModel();
                        $level_id = $_REQUEST['edit'];
                        $membershipLevelVIP = new PeepSoMembershipLevelVIP();
                        $selectedIcon = $membershipLevelVIP->get_vip_by_level($level_id);
                        ?>
                        	<div class="color-option">
                                <input name="peepso_vip_user_icon" id="vip_icon_0" type="radio" value="0" class="tog"  <?php echo (0 == $selectedIcon) ? ' checked=checked':'';?>>
                                <label for="vip_icon_0"><?php echo __('No Icon', 'peepso-pmp');?></label>
                            </div>
                        <?php
                        foreach ($PeepSoVipIconsModel->vipicons as $key => $value) {
                            ?>
                            <div class="color-option">
                                <input name="peepso_vip_user_icon" id="vip_icon_<?php echo $key;?>" type="radio" value="<?php echo $value->post_id;?>" class="tog" <?php echo ($value->post_id == $selectedIcon) ? ' checked=checked':'';?>>
                                <label for="vip_icon_<?php echo $key;?>"><?php echo $value->title;?> <?php  if(!intval($value->published)) { echo "<small>(".__('unpublished', 'peepso-vip').")</small>"; }  ?></label>
                                <img src="<?php echo $value->icon_url;?>" style="width: auto; height: 16px;">
                            </div>
                            <?php
                        }
                        ?>
                    </fieldset>
                </td>
            </tr>
        </table>
		<?php
	}

	public function action_after_save_membership_level_vip($membership_level_id)
	{
		// save membership level vip
		if ($membership_level_id) {

			$membershipLevelVIP = new PeepSoMembershipLevelVIP();
			$peepso_vip_user_icon = isset($_REQUEST['peepso_vip_user_icon']) ? [$_REQUEST['peepso_vip_user_icon']] : array();
			$membershipLevelVIP->update_membership_level_vip($membership_level_id, $peepso_vip_user_icon);

			return true;
		}

		return false;
	}

	public function action_after_delete_membership_level_vip($membership_level_id)
	{
		// clean up membership level group table.
		if($membership_level_id) {
			$membershipLevelVIP = new PeepSoMembershipLevelVIP();
			$vips = array();
			$membershipLevelVIP->update_membership_level_vip($membership_level_id, $vips);

			return true;
		}

		return false;
	}

	public function save_membership_user_profile_fields($user_id)
	{
		if ( !current_user_can( 'edit_user', $user_id ) ) {
            return (FALSE);
        }

        //level change
    	if(isset($_REQUEST['membership_level']))
    	{

	        $level_id = $_REQUEST['membership_level'];

	        $membershipLevelVIP = new PeepSoMembershipLevelVIP();
			$selectedIcon = $membershipLevelVIP->get_vip_by_level($level_id);

			$icons = (array) get_the_author_meta( 'peepso_vip_user_icon', $user_id );
			if (intval($selectedIcon) > 0) {
				$icons[] = $selectedIcon;
				$icons = array_unique($icons);

				update_user_meta( $user_id, 'peepso_vip_user_icon', $icons );
			}
		}
	}

	public function action_delete_order($order_id, $order)
	{
		new PeepSoError('PMP: Member Order: '.var_export($order, TRUE));
		new PeepSoError('PMP: User Id: '.$order->user_id);
		new PeepSoError('PMP: Membership Id: '.$order->membership_id);
	}

	public function group_member_actions($actions, $group_id) {
		// get all available membership levels
		$memberships = pmpro_getAllLevels();
		$membershipLevelGroup = new PeepSoMembershipLevelGroup();

		foreach ($memberships as $membership) {
			// check if the option is used
			$require_membership = get_pmpro_membership_level_meta($membership->id, 'peepso_group_require_membership', TRUE);

			if (!$require_membership) continue;

			// get all groups associated with the membership level
			$groups = $membershipLevelGroup->get_groups_by_level($membership->id);

			foreach ($groups as $value) {

				for ($i = 0; $i < count($actions); $i++) {
					if ($actions[$i]['action'] == 'join_request' && $group_id == $value) {
						$actions[$i]['redirect'] = pmpro_url('levels') . '?peepso-group-id=' . $group_id;
					}
				}
			}
		}

		return $actions;
	}

	public function pmpro_levels_array($levels) {
		$group_id = intval($_GET['peepso-group-id']);

		if ($group_id) {
			$peepso_group = new PeepSoGroup($group_id);

			if (!$peepso_group->id) {
				return $levels;
			}

			$new_levels = [];
			$membershipLevelGroup = new PeepSoMembershipLevelGroup();

			foreach ($levels as $level) {
				$groups = $membershipLevelGroup->get_groups_by_level($level->id);

				foreach ($groups as $group) {
					if ($group == $group_id) {
						$new_levels[] = $level;
						break;
					}
				}

				$user_level = pmpro_getSpecificMembershipLevelForUser(get_current_user_id(), $level->id);
				if (!empty($user_level)) {
					// prevent duplicate levels
					$found = FALSE;
					foreach ($new_levels as $new_level) {
						if ($new_level->id == $level->id) {
							$found = TRUE;
						}
					}

					if (!$found) {
						$new_levels[] = $level;
					}
				}
			}

			if (!empty($new_levels)) {
				$levels = $new_levels;
			}
		}
		return $levels;
	}
}

PeepSoPMP::get_instance();

// EOF
