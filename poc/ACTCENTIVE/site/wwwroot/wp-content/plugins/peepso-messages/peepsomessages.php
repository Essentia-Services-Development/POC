<?php
/**
 * Plugin Name: PeepSo Core: Chat
 * Plugin URI: https://peepso.com
 * Description: Private messages and live chat
 * Author: PeepSo
 * Author URI: https://peepso.com
 * Version: 6.2.7.0
 * Copyright: (c) 2015 PeepSo, Inc. All Rights Reserved.
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: msgso
 * Domain Path: /language
 *
 * We are Open Source. You can redistribute and/or modify this software under the terms of the GNU General Public License (version 2 or later)
 * as published by the Free Software Foundation. See the GNU General Public License or the LICENSE file for more details.
 * This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY.
 */

class PeepSoMessagesPlugin
{
	private static $_instance = NULL;

	const PLUGIN_VERSION = '6.2.7.0';
	const PLUGIN_RELEASE = ''; //ALPHA1, RC1 etc, '' for STABLE
    const PLUGIN_NAME = 'Core: Chat';
    const PLUGIN_EDD = 263;
	const PLUGIN_SLUG = 'msgso';

	const MODULE_ID = 6;
	const CPT_MESSAGE = 'peepso-message';
	const CPT_MESSAGE_INLINE_NOTICE = 'peepso-message-notic'; // intentional missing `e` due to char limit
	const DEFAULT_PER_PAGE = 10;
	const PERM_SEND_MESSAGE = 'send_message';

	// indicator for users that left the conversation
	const MESSAGE_INLINE_LEFT_CONVERSATION = 'left';
	const MESSAGE_INLINE_NEW_GROUP = 'new_group';

	private $_messages = array();
	private $_sent = array();
	private $_messages_in_conversation = NULL;
	private $_query = '';

    public $shortcodes= array(
        'peepso_messages' => 'PeepSoMessagesShortcode::shortcode_messages',
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

	/**
	 * Initialize all variables, filters and actions
	 */
	private function __construct()
	{
        /** VERSION LOCKED AJAX hooks **/
        if(self::ready()) {
            add_action('wp_ajax_peepso_should_get_chats', array(&$this, 'ajax_should_get_chats'));

            // Handle ajax response on an expired login session.
            add_action('wp_ajax_nopriv_peepso_should_get_chats', function() {
                echo json_encode([ 'success' => 0, 'session_timeout' => 1 ]);
                exit();
            });

            add_filter('peepso_profile_alerts', array(&$this, 'profile_alerts'), 10, 1);
            add_action('peepso_messages_list_header', function() {
            	if(FALSE !== apply_filters('peepso_permissions_messages_create', TRUE)) {
	                echo PeepSoTemplate::exec_template('messages','list-header');
	            }
            });
        }

        // stop other hooks if doing AJAX
		if (defined('DOING_AJAX') && DOING_AJAX) { return; }

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
        add_filter('peepso_all_plugins', function($plugins) {
            $plugins[plugin_basename(__FILE__)] = get_class($this);
            return $plugins;
        });
        add_filter('peepso_free_bundle_should_brand', '__return_true');

        // Translations
        add_action('plugins_loaded', function(){
            $path = str_ireplace(WP_PLUGIN_DIR, '', dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
            load_plugin_textdomain('msgso', FALSE, $path);
        });

        // Activation
        register_activation_hook(__FILE__, array(&$this, 'activate'));

        /** VERSION LOCKED hooks **/
        if(self::ready()) {
			if (!PeepSoLicense::check_license(self::PLUGIN_EDD, self::PLUGIN_SLUG)) {
				return;
			}

            add_filter('peepso_filter_shortcodes', function ($list) {
                return array_merge($list, $this->shortcodes);
            });
            add_action('peepso_init', array(&$this, 'init'));
            add_filter('peepso_activity_remove_shortcode', array(&$this, 'peepso_activity_remove_shortcode'));

	        add_filter('peepso_notification_digest_section_title', function($section, $user_id) {
		        if('chat' == $section) {
			        $section = __('Unread messages', 'friendso');
		        }

		        return $section;
	        }, 10, 2);
        }
	}

	/**
	 * Retrieve singleton class instance
	 * @return PeepSoMessages instance
	 */
	public static function get_instance()
	{
		if (NULL === self::$_instance) {
            self::$_instance = new self();
        }
		return (self::$_instance);
	}

	/**
	 * Determine if something changed in chats for current user and if get_chats() call is necessary
	 * @return int|void
	 */
	public function ajax_should_get_chats($return = FALSE)
	{
		$delay_min 			= PeepSo::get_option('notification_ajax_delay_min', 5000);
		$delay_max 		  	= PeepSo::get_option('notification_ajax_delay', 30000);
		$delay_multiplier 	= PeepSo::get_option('notification_ajax_delay_multiplier', 1.5);

		$delay 				= (isset($_POST['delay'])) ? intval($_POST['delay']) : max($delay_min, get_user_option('peepso_should_get_chats_delay'));

		$multiply = TRUE;

		if($delay<$delay_min) {
			$multiply = FALSE; // do not multiply the default (first request without param)
			$delay = $delay_min;
		}

		$chats = 0;

		// if the option is set, it means something changed and we should refresh
		if(get_user_option('peepso_should_get_chats')) {
			delete_user_option(get_current_user_id(), 'peepso_should_get_chats');
            delete_user_option(get_current_user_id(), 'peepso_should_get_chats_delay');
			$delay = $delay_min;
			$chats = 1;
		} else {

			if($multiply) {
				$delay = floor($delay * $delay_multiplier);
			}

			if($delay>$delay_max) {
				$delay = $delay_max;
			}

            update_user_option(get_current_user_id(), 'peepso_should_get_chats_delay', $delay);
		}

		$resp = array($chats,$delay);

		if($return) {
			return($resp);
		}

		echo json_encode($resp);
		exit();
	}

	/*
	 * Initialize the PeepSoMessages plugin
	 */
	public function init()
	{
		PeepSo::add_autoload_directory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR);
		PeepSoTemplate::add_template_directory(plugin_dir_path(__FILE__));

		if (is_admin()) {

			PeepSoMessagesAdmin::get_instance();

			add_filter('peepso_admin_config_tabs', function($tabs){
                $tabs['messages'] = array(
                    'label' => __('Chat', 'msgso'),
                    'icon' => 'https://cdn.peepso.com/icons/plugins/'.self::PLUGIN_EDD.'.svg',
                    'tab' => 'messages',
                    'description' => __('Messages & Chat', 'msgso'),
                    'function' => 'PeepSoConfigSectionMessages',
                    'cat' => 'core',
                );

                return $tabs;
            });
			add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));

		} else {
			// register post types
			$this->register_cpts();

			if(get_current_user_id()) {
                add_filter('peepso_access_types', array(&$this, 'filter_access_types'));

                add_action('peepso_activity_dialogs', array(&$this, 'activity_dialogs'));
                add_filter('peepso_activity_can_attach_file', array(&$this, 'activity_can_attach_file'), 10, 2);


                add_filter('peepso_check_permissions-' . self::PERM_SEND_MESSAGE, array(&$this, 'check_permissions'), 10, 3);

                add_filter('peepso_check_query', array(&$this, 'check_message_page'), 10, 3);

                add_filter('peepso_config_email_messages', array('PeepSoMessagesAdmin', 'config_email_messages'));

                add_filter('peepso_friends_friend_options', array(&$this, 'member_options'), 10, 2);
                add_filter('peepso_friends_friend_buttons', array(&$this, 'member_buttons'), 20, 2);

                add_filter('peepso_live_notifications', array(&$this, 'get_latest_count'), 10, 1);

                add_filter('peepso_location_apply_to_post_types', array(&$this, 'apply_post_type'));

                #add_filter('peepso_member_options', array(&$this, 'member_options'), 10, 2);
                add_filter('peepso_member_buttons', array(&$this, 'member_buttons'), 20, 2);

                add_filter('peepso_moods_apply_to_post_types', array(&$this, 'apply_post_type'));


                add_action('peepso_messages_message_deleted', array(&$this, 'message_deleted'), 10, 3);
                add_action('peepso_messages_new_message', array(&$this, 'new_message'));
                add_action('peepso_messages_new_conversation', array(&$this, 'new_conversation'));


                add_filter('peepso_profile_actions', array(&$this, 'profile_actions'), 99, 2);
                add_filter('peepso_profile_preferences', array(&$this, 'edit_preferences_fields'), 100);
				add_filter('peepso_profile_widget_toolbar', array(&$this, 'navbar_profile_widget_toolbar'));

				add_filter('peepso_post_types', array(&$this, 'postbox_post_types'), 999, 2);
				add_filter('peepso_postbox_interactions', array(&$this, 'postbox_interactions'), 999, 2);

                add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
            }
		}

		if(get_current_user_id()) {
            add_filter('peepso_navigation', array(&$this, 'filter_peepso_navigation'));
        }

        if (defined('DOING_CRON') && DOING_CRON) {
            PeepSoMessagesCron::get_instance();
        }

        PeepSoMessagesShortCode::register_shortcodes();

		// Compare last version stored in transient with current version
		if( $this::PLUGIN_VERSION.$this::PLUGIN_RELEASE != PeepSo3_Mayfly::get($mayfly = 'peepso_'.$this::PLUGIN_SLUG.'_version')) {
			PeepSo3_Mayfly::set($mayfly, $this::PLUGIN_VERSION.$this::PLUGIN_RELEASE);
			$this->activate();
		}

        if(class_exists('PeepSoMaintenanceFactory') && class_exists('PeepSoMaintenanceChat')) {
		    new PeepSoMaintenanceChat();
		}
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

	/**
	 * Plugin activation
	 * Check PeepSo
	 * Run installaton
	 * @return bool
	 */
	public function activate()
	{
		if (!$this->peepso_check()) {
			return (FALSE);
		}

		require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'activate.php');
		$install = new PeepSoMessagesInstall();
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
                        <?php echo sprintf(__('The %s plugin requires the PeepSo plugin to be installed and activated.', 'msgso'), self::PLUGIN_NAME);?>
						<a href="plugin-install.php?tab=plugin-information&plugin=peepso-core&TB_iframe=true&width=772&height=291" class="thickbox">
                            <?php echo __('Get it now!', 'msgso');?>
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

        PeepSoLicense::check_updates_new(self::PLUGIN_EDD, self::PLUGIN_SLUG, self::PLUGIN_VERSION, __FILE__);

		return (TRUE);
	}


	/**
	 * Adds the Messages dropdown and New Messages notification to the navigation bar.
	 * @param  array $navbar An array of navigation menus
	 * @return array $navbar
	 */
	public function filter_peepso_navigation($navigation)
	{
		$recipients = PeepSoMessageRecipients::get_instance();

		$received = array(
			'href' => PeepSo::get_page('messages'),
			'icon' => 'gcis gci-envelope',
			'class' => 'ps-notif--messages ps-js-messages-notification',
			'title' => __('New message', 'msgso'),
			'label' => __('Messages', 'msgso'),
			'count' => $recipients->get_unread_messages_count(),

            'primary'           => FALSE,
            'secondary'         => TRUE,
            'mobile-primary'    => FALSE,
            'mobile-secondary'  => TRUE,
            'widget'            => FALSE,
            'notifications'     => TRUE,
            'icon-only'         => TRUE,
		);

		if ($received['count'] > 0) {
			$received['class'] .= ' messages-notification';
		}

		$navigation['messages-notification'] = $received;

		return ($navigation);
	}

	public function enqueue_scripts()
	{
		// Register selectize.js
		wp_register_style('msgso-selectize-default', PeepSo::get_asset('css/selectize.default.min.css', __FILE__), array(), self::PLUGIN_VERSION);
		wp_enqueue_style('msgso-selectize', PeepSo::get_asset('css/selectize.custom.css', __FILE__), array('msgso-selectize-default'), self::PLUGIN_VERSION);
		wp_register_script('msgso-selectize', PeepSo::get_asset('js/selectize.min.js', __FILE__), array('jquery'), self::PLUGIN_VERSION, TRUE);

		// Register jquery.mousewheel.js
		wp_register_script('msgso-mousewheel', PeepSo::get_asset('js/jquery.mousewheel.min.js', __FILE__),
			array('jquery'), self::PLUGIN_VERSION, TRUE);

		// Enqueueu main script.
		// TODO: Should be loaded on-demand!
		wp_enqueue_script('msgso', PeepSo::get_asset('js/peepsomessages.min.js', __FILE__),
			is_admin() ? array() : array('msgso-selectize', 'peepso-activity', 'msgso-mousewheel'), self::PLUGIN_VERSION, TRUE);

		// New message dialog template.
		ob_start();
		PeepSoTemplate::exec_template('messages', 'dialogs');
		$template = ob_get_clean();

		$chat_restriction_mode = (int) PeepSo::get_option('messages_chat_restriction_mode', '0');
		$chat_disable_on_pages = 1 === $chat_restriction_mode ? '' : PeepSo::get_option('messages_chat_disable_on_pages', '');
		$chat_enable_on_pages  = 1 !== $chat_restriction_mode ? '' : PeepSo::get_option('messages_chat_enable_on_pages', '');

		wp_localize_script('peepso', 'peepsomessagesdata',
			array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'character_limit' => PeepSo::get_option('messages_limit', 4000),
				'messages_page' => PeepSo::get_page('messages'),
				'per_page' => self::DEFAULT_PER_PAGE,
				'send_button_text' => __('Send', 'msgso'),
				'mute_conversation' => __('Mute conversation', 'msgso'),
				'unmute_conversation' => __('Unmute conversation', 'msgso'),
				'mute_confirm' => PeepSoTemplate::exec_template('messages', 'mute', NULL, TRUE),
				'show_checkmark' => __('Send read receipt', 'msgso'),
				'hide_checkmark' => __("Don't send read receipt", 'msgso'),
				'blockuser_confirm_text' => __('Are you sure want to block this user?', 'msgso'),
				'get_chats_longpoll' => (int) PeepSo::get_option('messages_get_chats_longpoll', FALSE),
				'chat_restriction_mode' => $chat_restriction_mode,
				'chat_disable_on_pages' => $chat_disable_on_pages,
				'chat_enable_on_pages'  => $chat_enable_on_pages,
				'template' => $template,
				'notification_header' => PeepSoTemplate::exec_template('messages', 'notification-popover-header', NULL, TRUE),
			)
		);

		add_filter('peepso_data', function( $data ) {
			$data['messages'] = array(
				'text_bulk_no_items' => __('Please select at least one message for bulk action.', 'msgso'),
				'text_bulk_action' => __('Please select your bulk action.', 'msgso'),
				'text_bulk_delete_confirm' => __('Are you sure want to delete these messages?', 'msgso'),
				'sound_beep' => PeepSo::get_asset('beep.wav', __FILE__),
			);

			return $data;
		}, 10, 1);

		if (!is_admin()) {

			$data_window = array(
				'read_notification' => PeepSo::get_option('messages_read_notification')
			);

			wp_enqueue_script('peepso-messages',
				PeepSo::get_asset('js/bundle.min.js', __FILE__),
				array('peepso', 'peepso-fileupload', 'msgso-mousewheel'),
				self::PLUGIN_VERSION, TRUE);

			if (PeepSoChatModel::chat_enabled(get_current_user_id())) {
				wp_enqueue_script('peepso-chat',
					PeepSo::get_asset('js/chat.min.js', __FILE__),
					array('peepso', 'peepso-fileupload', 'msgso-mousewheel'),
					self::PLUGIN_VERSION, TRUE);

				$message_input_addons = apply_filters('peepso_message_input_addons', array());

				wp_localize_script('peepso-chat', 'peepsochatdata', array(
					'containerTemplate' => PeepSoTemplate::exec_template('chat', 'container', NULL, TRUE),
					'windowTemplate' => PeepSoTemplate::exec_template('chat', 'window', $data_window, TRUE),
					'windowInputTemplate' => PeepSoTemplate::exec_template('chat', 'window-input', array('addons' => $message_input_addons), TRUE),
					'sidebarTemplate' => PeepSoTemplate::exec_template('chat', 'sidebar', NULL, TRUE),
					'sidebarItemTemplate' => PeepSoTemplate::exec_template('chat', 'sidebar-item', NULL, TRUE),
					'sendMessageTemplate' => PeepSoTemplate::exec_template('chat', 'send-message', NULL, TRUE),
					'sendPhotosTemplate' => PeepSoTemplate::exec_template('chat', 'send-photos', NULL, TRUE),
					'sendFilesTemplate' => PeepSoTemplate::exec_template('chat', 'send-files', NULL, TRUE),
					'messageUrl' => PeepSoMessages::get_message_id_url('{id}'),
					'translations' => array(
						'and' => __('%s and %s', 'msgso'),
						'and_x_other' => _n('%s and %d other', '%s and %d others', 1, 'msgso'),
						'and_x_others' => _n('%s and %d other', '%s and %d others', 2, 'msgso'),
						'turn_on_chat' => __('Turn on chat', 'msgso'),
						'turn_off_chat' => __('Turn off chat', 'msgso'),
						'mute_chat' => __('Mute conversation', 'msgso'),
						'unmute_chat' => __('Unmute conversation', 'msgso'),
						'show_checkmark' => __('Send read receipt', 'msgso'),
						'hide_checkmark' => __("Don't send read receipt", 'msgso'),
					)
				));
			}
		}

		if (apply_filters('peepso_free_bundle_should_brand', FALSE)) {
			wp_add_inline_script('peepso', "setTimeout(() => peepso.observer.do_action('show_branding'), 1000);");
		}
	}

	public function admin_enqueue_scripts() {
		wp_enqueue_script('peepso-admin-messages',
			PeepSo::get_asset('js/admin.js', __FILE__),
			array('jquery'), self::PLUGIN_VERSION, TRUE);
	}

	/**
	 * Registers custom post types used by the Messages module
	 */
	public function register_cpts()
	{
		// post type for Messages
		$labels = array(
			'name'					=> _x('PeepSo Messages', 'PeepSo Messages', 'msgso'),
			'singular_name'			=> _x('PeepSo Message', 'PeepSo Message', 'msgso'),
			'menu_name'				=> __('PeepSo Messages', 'msgso'),
			'parent_item_colon'		=> __('PeepSo Messages:', 'msgso'),
			'all_items'				=> __('All PeepSo Messages', 'msgso'),
			'view_item'				=> __('View PeepSo Message', 'msgso'),
			'add_new_item'			=> __('Add New PeepSo Message', 'msgso'),
			'add_new'				=> __('Add New PeepSo Message', 'msgso'),
			'edit_item'				=> __('Edit PeepSo Message', 'msgso'),
			'update_item'			=> __('Update PeepSo Message', 'msgso'),
			'search_items'			=> __('Search PeepSo Messages', 'msgso'),
			'not_found'				=> __('Not found', 'msgso'),
			'not_found_in_trash'	=> __('Not found in Trash', 'msgso'),
			'activity_action'		=> __('sent', 'msgso'),
			'activity_type'			=> __('message', 'msgso')
		);

		$args = array(
			'label'					=> __('PeepSo Messages', 'msgso'),
			'description'			=> __('PeepSo Messages', 'msgso'),
			'labels'				=> $labels,
			'hierarchical'			=> FALSE,
			'public'				=> FALSE,
			'show_ui'				=> FALSE,
			'show_in_menu'			=> FALSE,
			'show_in_nav_menus'		=> FALSE,
			'show_in_admin_bar'		=> FALSE,
			'can_export'			=> FALSE,
			'has_archive'			=> FALSE,
			'exclude_from_search'	=> TRUE,
			'publicly_queryable'	=> FALSE,
			'capability_type'		=> 'page',
		);
		register_post_type(self::CPT_MESSAGE, $args);

		// Post type for inline Message activity (ex: User A has left the conversation)
		// Using a CPT for this - so that we can query the events in chronological order -
		// `wp_peepso_notifications` table may also be possible but may require more tweaking
		$labels = array(
			'name'					=> _x('PeepSo Messages Inline', 'PeepSo Messages Inline', 'msgso'),
			'singular_name'			=> _x('PeepSo Message Inline', 'PeepSo Message Inline', 'msgso'),
			'menu_name'				=> __('PeepSo Messages Inline', 'msgso'),
			'parent_item_colon'		=> __('PeepSo Messages Inline:', 'msgso'),
			'all_items'				=> __('All PeepSo Messages Inline', 'msgso'),
			'view_item'				=> __('View PeepSo Message Inline', 'msgso'),
			'add_new_item'			=> __('Add New PeepSo Message Inline', 'msgso'),
			'add_new'				=> __('Add New PeepSo Message Inline', 'msgso'),
			'edit_item'				=> __('Edit PeepSo Message Inline', 'msgso'),
			'update_item'			=> __('Update PeepSo Message Inline', 'msgso'),
			'search_items'			=> __('Search PeepSo Messages Inline', 'msgso'),
			'not_found'				=> __('Not found', 'msgso'),
			'not_found_in_trash'	=> __('Not found in Trash', 'msgso'),
			'activity_action'		=> __('sent', 'msgso'),
			'activity_type'			=> __('message', 'msgso')
		);

		$args = array(
			'label'					=> __('PeepSo Messages Inline', 'msgso'),
			'description'			=> __('PeepSo Messages Inline', 'msgso'),
			'labels'				=> $labels,
			'hierarchical'			=> FALSE,
			'public'				=> FALSE,
			'show_ui'				=> FALSE,
			'show_in_menu'			=> FALSE,
			'show_in_nav_menus'		=> FALSE,
			'show_in_admin_bar'		=> FALSE,
			'can_export'			=> FALSE,
			'has_archive'			=> FALSE,
			'exclude_from_search'	=> TRUE,
			'publicly_queryable'	=> FALSE,
			'capability_type'		=> 'page',
		);
		register_post_type(self::CPT_MESSAGE_INLINE_NOTICE, $args);
	}

	/**
	 * Displays the dialog boxes used by this plugin
	 */
	public function activity_dialogs()
	{
		add_filter('peepso_postbox_message', array(&$this, 'textarea_placeholder'));
		PeepSoTemplate::exec_template('messages', 'dialogs');
		remove_filter('peepso_postbox_message', array(&$this, 'textarea_placeholder'));
	}

	/**
	 * Chech whether certain activity can attach file.
	 * @param  bool  $can
	 * @param  array $post
	 * @return bool
	 */
	public function activity_can_attach_file($can, $post)
	{
		if (self::CPT_MESSAGE === $post->post_type) {
			$can = true;
		}

		return $can;
	}

	/**
	 * Changes a postbox placeholder.
	 * @param  string $msg The original placeholder text.
	 * @return string
	 */
	public function textarea_placeholder($msg)
	{
		return (__('Your message...', 'msgso'));
	}

	/**
	 * Changes the "Post" tab text on a postbox to "Message".
	 * @param  array $post_types An array of the post types available (Status, Video, Photo, etc)
	 * @return array $post_types The modified $post_types
	 */
	public function postbox_post_types($post_types, $params = array())
	{
		if (isset($params['postbox_message'])) {
			$post_types['status']['name'] = __('Text', 'msgso');
			unset($post_types['videos']);
			unset($post_types['polls']);
            unset($post_types['audio']);
            unset($post_types['post_backgrounds']);

			$post_types = apply_filters('peepso_post_types_message', $post_types);
		}

		return ($post_types);
	}

	/**
	 * Remove privacy from postbox interactions when rendering the message postbox
	 * @param  array $interactions
	 * @return
	 */
	public function postbox_interactions($interactions, $params = array())
	{
		if (isset($params['postbox_message'])) {
			unset($interactions['privacy']);
			unset($interactions['videos']);
			unset($interactions['poll']);
			unset($interactions['groups']);
			unset($interactions['schedule']);
			unset($interactions['pin']);
			unset($interactions['audio']);
		}

		return ($interactions);
	}

	/**
	 * Returns an array of available recipients based on the message ID.
	 * @param  integer $message_id A message/post ID, if this message exists, current participants are excluded.
	 * @param  string $keyword
	 * @param  integer $page
	 * @param  integer $user_id
	 * @return array An array of user display names with user_id as keys.
	 */
	public function get_available_recipients($message_id = 0, $keyword = '', $page = 1, $user_id = FALSE)
	{
		$args = array();
		$current_user = get_current_user_id();
		if ($message_id > 0) {
			$peepso_participants = new PeepSoMessageParticipants();
			$model = new PeepSoMessagesModel();
			$parent_id = $model->get_root_conversation($message_id);
			$current_participants = $peepso_participants->get_participants($parent_id);
			$args['exclude'] = $current_participants;
		} else {
			$args['exclude'] = array( $current_user );
		}

		// Include specific user id set.
		if ( $user_id > 0 ) {
			$args['include'] = array( $user_id );
		}

		// Keyword if set.
		if ( $keyword !== '' ) {
			$args['search_columns'] = array( 'user_login', 'user_nicename', 'display_name' );
		}

		// Pagination.
		$args['number'] = 20;
		$args['paged'] = 1;
		$args = apply_filters('peepso_chat_available_recipients_args', $args);
		$users = new PeepSoUserSearch($args, $current_user, $keyword);

		$recipients = array();

		while ($recipient = $users->get_next()) {
			if (PeepSo::check_permissions($recipient->get_id(), PeepSoMessagesPlugin::PERM_SEND_MESSAGE, $current_user)) {
				$recipients[] = array(
					'id' => $recipient->get_id(),
					'display_name' => $recipient->get_fullname(),
					'avatar' => $recipient->get_avatar(),
					'url' => $recipient->get_profileurl()
				);
			}
		}

		$recipients = apply_filters('peepso_messages_available_recipients', $recipients, $args);

		return ($recipients);
	}

	/**
	 * Checks whether on a message page.
	 * @param  object $sc A peepso shortcode instance
	 * @param  string $page Part of the URL query which describes the current page
	 * @return mixed
	 */
    public function check_message_page($sc, $page, $url)
    {
        // use config setting to determine which page is being loaded and if a Shortcode
        // handler class should be loaded

		$path = PeepSo::get_page_url();
        $parts = explode('?', $path, 2);

		$page = trim($parts[0],'/');

        if (PeepSo::get_option('page_messages') === $page) {
            add_filter( 'the_title', ARRAY(PeepSo::get_instance(),'the_title'), 10, 2 );
            $sc = PeepSoMessagesShortcode::get_instance();
            $extra = isset($parts[1]) ? $parts[1] : '';
            $sc->set_page($page, $extra);
        }
    }

	/**
	 * Adds the peepso-message as one of the post types.
	 * @param  array $post_types An array of post types
	 * @return array $post_types
	 */
	public function apply_post_type($post_types)
	{
		$post_types[] = self::CPT_MESSAGE;

		return ($post_types);
	}

	/**
	 * Add the send message button when a user is viewing the friends list
	 * @param  array $options
	 * @return array
	 */
	public function member_options($options, $user_id)
	{
		if (FALSE === apply_filters('peepso_permissions_messages_create', TRUE)) {
			return $options;
		}

		$options['message'] = array(
			'label' => __('Send Message', 'msgso'),
			'click' => 'ps_messages.new_message(' . $user_id . ', false, this); return false;',
			'icon' => 'comment',
			'loading' => TRUE,
		);

		return ($options);
	}

	/**
	 * Add the send message button when a user is viewing the friends list
	 * @param  array $options
	 * @return array
	 */
	public function member_buttons($options, $user_id)
	{
		if (FALSE === apply_filters('peepso_permissions_messages_create', TRUE)) {
			return $options;
		}

		$current_user = intval(get_current_user_id());

		if ($current_user !== $user_id &&
			PeepSo::check_permissions($user_id, PeepSoMessagesPlugin::PERM_SEND_MESSAGE, $current_user)
		) {
			$options['message'] = array(
				'class' => 'ps-member__action ps-member__action--message',
				'click' => 'ps_messages.new_message(' . $user_id . ', false, this); return false;',
				'icon' => 'gcir gci-envelope',
				'loading' => TRUE,
			);
		}
		return ($options);
	}

	/*
	 * Check if author has permission to send a message to owner
	 * @param mixed $can_access Defaults to -1 , return as TRUE or FALSE depending on permission
	 * @param int $owner The user id of the owner of the Activity Stream
	 * @param int $author The author requesting permission to perform the action
	 * @return mixed Defaults to -1 , return as TRUE or FALSE depending on permission
	 */
	public function check_permissions($can_access, $owner, $author)
	{
	    // Disallow if intended recipient has "friends only" enabled
	    if(class_exists('PeepSoFriends')) {
            if(1==(int)PeepSoChatModel::chat_friends_only($owner)) {
                $PeepSoFriendsModel = PeepSoFriendsModel::get_instance();
                if(FALSE == $PeepSoFriendsModel->are_friends($owner, $author))
                {
                    return FALSE;
                }
            }

        }

		// By default if the user can view the profile, the user can send a message.
		return (PeepSo::check_permissions($owner, PeepSo::PERM_PROFILE_VIEW, $author));
	}

	/**
	 * Cleanup message participants and recipients when message was successfully deleted
	 * @param int $message_id Message ID
	 */
	public function message_deleted($message_id, $user_id, $permanent)
	{
		return (TRUE); // Temporary no effect
		$message_recipients = new PeepSoMessageRecipients();
		$ret = $message_recipients->remove_recipients($user_id, $message_id);
		if ($ret) {
			$message_participants = new PeepSoMessageParticipants();
			$ret = $message_participants->remove_participant($user_id, $message_id);
		}
	}

	/**
	 * Append profile alerts definition for peepsomessages
	 */
	// TODO: move this into the PeepSoMessageAdmin class
	public function profile_alerts($alerts)
	{
		$alerts['messages_notifications'] = array(
				'title' => __('Messages', 'msgso'),
				'items' => array(
					array(
						'label' => __('Someone sent me a new message', 'msgso'),
						'setting' => 'new_message',
						'loading' => TRUE,
					)
				),
		);
		// NOTE: when adding new items here, also add settings to /install/activate.php site_alerts_ sections
		return ($alerts);
	}

	/**
	 * Add access types hook required for PeepSoPMPro plugin
	 * @param array $types existing access types
	 * @return array $types new access types
	 */
	public function filter_access_types($types)
	{
		$types[PeepSoMessagesShortcode::SHORTCODE_MESSAGES] = array(
			'name' => __('Messages', 'msgso'),
			'module' => self::MODULE_ID,
		);
		return ($types);
	}

	/**
	 * Get unread messages count for live notification.
	 */
	public function get_latest_count(PeepSoAjaxResponse $resp)
	{
		$notifications = get_user_meta(get_current_user_id(), 'peepso_notifications');
		// do not send any notification when it's disabled

		if (isset($notifications[0]) && in_array('new_message_notification', $notifications[0])) {
			$count = 0;
		} else {
			$recipients = PeepSoMessageRecipients::get_instance();
			$count = (int) $recipients->get_unread_messages_count();
		}

		$resp->data['ps-js-messages-notification'] 			= array('count' => $count);
		$resp->data['ps-js-messages-notification']['el'] 	= 'ps-js-messages-notification';

		$resp->success(TRUE);
		return $resp;
	}

	/**
	 * Adds the show_on_stream override option to Profile > edit preferences
	 * @param  array $group_fields
	 * @return array
	 */
	public function edit_preferences_fields($group_fields)
	{
		$fields = array();

		// If chat is disabled in the global settings
		if(PeepSo::get_option('messages_chat_enable', 1)) {

            $fields['peepso_chat_enabled'] = array(
                'label-desc' => __('Enable Chat (Messages will still work if you disable Chat)', 'msgso'),
                'type' => 'yesno_switch',
                'value' => (int)PeepSoChatModel::chat_enabled(get_current_user_id()),
                'loading' => TRUE,
            );

            $fields['peepso_chat_new_minimized'] = array(
                'label-desc' => __('Open minimized chat window for new message', 'msgso'),
                'type' => 'yesno_switch',
                'value' => (int)PeepSoChatModel::chat_new_minimized(get_current_user_id()),
                'loading' => TRUE,
            );

        }

        if(class_exists('PeepSoFriends')) {
            $fields['peepso_chat_friends_only'] = array(
                'label-desc' => __('Allow new messages only from friends', 'msgso'),
                'type' => 'yesno_switch',
                'value' => (int)PeepSoChatModel::chat_friends_only(get_current_user_id()),
                'loading' => TRUE,
            );
        }


        $group_fields['chat'] = array(
			'title' => __('Message and Chat', 'msgso'),
			'items' => $fields,
		);

		if (isset($group_fields['chat']))
		{
			$fields += $group_fields['chat']['items'];
			unset($group_fields['chat']);
		}

		if ($fields)
		{
			$group_fields['messages'] = array(
				'title' => __('Messages and Chat', 'msgso'),
				'items' => $fields,
			);
		}

		return ($group_fields);
	}


	// @todo docblock
	public static function get_messages_query_and($sql)
	{
		$sql .= " AND `mrec_chat_state`>0 ";
		return $sql;
	}

	// @todo docblock
	public static function peepso_permission_message_create($allowed)
	{
		return TRUE;
	}

	// @todo docblock
	public static function new_conversation($msg_id)
	{
		// get all recipients of that message and pop the chat up for them
		$msg_participants = new PeepSoMessageParticipants();
		$participants = $msg_participants->get_participants($msg_id);

		foreach ($participants as $user_id) {

			$chat_state = 1;

			if( 1 == PeepSoChatModel::chat_new_minimized($user_id) ) {
				$chat_state = 2;
			}

			$args = array('chat_state' => $chat_state, 'chat_order' => 0);
			PeepSoChatModel::set($msg_id, $user_id, $args);
		}
	}

	// @todo docblock
	public static function new_message($msg_id)
	{
		// get all recipients of that message and pop the chat up for them
		$msg_participants = new PeepSoMessageParticipants();
		$participants = $msg_participants->get_participants($msg_id);

		foreach ($participants as $user_id) {

			$chat_state = 1;

			if( 1 == PeepSoChatModel::chat_new_minimized($user_id) ) {
				$chat_state = 2;
			}

			$args = array('chat_state' => $chat_state, 'chat_order' => 0, 'old_state' => 0);
			$where = array(
				'chat_disabled' => 0,
				'muted' => 0,
			);
			PeepSoChatModel::set($msg_id, $user_id, $args, $where);
		}
	}

	/**
	 * Replaces the “Send Message” button with "Chat" button
	 * @param  array $act The array of profile actions available.
	 * @param  int $user_id The current profile's user ID.
	 * @return array
	 */
	public function profile_actions($act, $user_id)
	{
		if (FALSE === apply_filters('peepso_permissions_messages_create', TRUE)) {
			return $act;
		}

		$current_user = intval(get_current_user_id());

		if ($current_user !== $user_id &&
			PeepSo::check_permissions($user_id, PeepSoMessagesPlugin::PERM_SEND_MESSAGE, $current_user)
		) {
			$act['message'] = array(
				'icon' => 'gcir gci-envelope',
				'class' => 'ps-focus__cover-action',
				'title' => __('Start a chat', 'msgso'),
				'click' => 'ps_messages.new_message(' . $user_id . ', false, this); return false;',
				'loading' => TRUE,
			);
		}

		return ($act);
	}
}

PeepSoMessagesPlugin::get_instance();

// EOF
