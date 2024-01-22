<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if plugin upgrade to a new version requires any actions like database upgrade
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class EPKB_Upgrades {

	public function __construct() {
        // will run after plugin is updated but not always like front-end rendering
		add_action( 'admin_init', array( 'EPKB_Upgrades', 'update_plugin_version' ) );

		// show initial page after install addons
		//add_action( 'admin_init', array( 'EPKB_Upgrades', 'initial_addons_setup' ), 1 );

		// show initial page after install
		add_action( 'admin_init', array( 'EPKB_Upgrades', 'initial_setup' ), 20 );

		// show additional messages on the plugins page
		add_action( 'in_plugin_update_message-echo-knowledge-base/echo-knowledge-base.php',  array( $this, 'in_plugin_update_message' ) );
		add_action( 'after_switch_theme', array( $this, 'after_switch_theme' ) );
	}

	/**
	 * Display license screen on addon first activation or upgrade - redirect admin user once on visiting any KB admin page
	 */
	public static function initial_addons_setup() {

		// continue only for admin user, on any KB admin page
		if ( ! current_user_can( EPKB_Admin_UI_Access::get_admin_capability() ) || ! is_admin() || ! EPKB_KB_Handler::is_kb_request() ) {
			return;
		}

		// ensure all transients are deleted before redirecting user
		$redirect_to_licenses = false;
		$addons = [ 'emkb', 'epie',	'elay', 'kblk', 'eprf',	'asea',	'widg', 'amgp', 'amcr' ];
		foreach ( $addons as $addon ) {

			// check is addon not recently activated
			$addon_activated = get_transient( "_{$addon}_plugin_activated" );
			if ( ! empty( $addon_activated ) ) {
				delete_transient( "_{$addon}_plugin_activated" );
				$redirect_to_licenses = true;
			}
		}

		// redirect to Getting Started Licenses tab
		if ( ! empty( $redirect_to_licenses ) ) {
			wp_safe_redirect( admin_url( 'edit.php?post_type=' . EPKB_KB_Handler::get_post_type( EPKB_KB_Config_DB::DEFAULT_KB_ID ) . '&page=ep'.'kb-add-ons&epkb_after_addons_setup#licenses') );
			exit;
		}
	}

	/**
	 * Trigger display of wizard setup screen on plugin first activation or upgrade; does NOT work if multiple plugins installed at the same time
	 */
	public static function initial_setup() {

		$kb_version = EPKB_Utilities::get_wp_option( 'epkb_version', null );
		if ( empty( $kb_version) ) {
			return;
		}

		// ignore if plugin not recently activated
		$plugin_installed = get_transient( '_epkb_plugin_installed' );
		if ( empty( $plugin_installed ) ) {
			return;
		}

		// return if activating from network or doing bulk activation
		if ( is_network_admin() || isset($_GET['activate-multi']) ) {
			return;
		}

		// Delete the redirect transient
		delete_transient( '_epkb_plugin_installed' );

		// if setup ran then do not proceed
		if ( ! EPKB_Core_Utilities::is_run_setup_wizard_first_time() ) {
			return;
		}

		// run setup wizard
		wp_safe_redirect( admin_url( 'edit.php?post_type=' . EPKB_KB_Handler::get_post_type( EPKB_KB_Config_DB::DEFAULT_KB_ID ) . '&page=epkb-kb-configuration&setup-wizard-on' ) );
		exit;
	}

		/**
		 * If necessary run plugin database updates
		 */
		public static function update_plugin_version() {

		$last_version = EPKB_Utilities::get_wp_option( 'epkb_version', null );      // TODO FUTURE use upgrade_plugin_version
		if ( empty( $last_version ) ) {
			EPKB_Utilities::save_wp_option( 'epkb_version', Echo_Knowledge_Base::$version );
			EPKB_Utilities::save_wp_option( 'epkb_version_first', Echo_Knowledge_Base::$version );
			// update new first version
			epkb_get_instance()->kb_config_obj->set_value( EPKB_KB_Config_DB::DEFAULT_KB_ID, 'first_plugin_version', Echo_Knowledge_Base::$version );
			return;
		}

		// if plugin is up-to-date then return
		if ( version_compare( $last_version, Echo_Knowledge_Base::$version, '>=' ) ) {
			return;
		}

		// upgrade the plugin
		self::invoke_upgrades( $last_version );

		// update the plugin version
		$result = EPKB_Utilities::save_wp_option( 'epkb_version', Echo_Knowledge_Base::$version );  // TODO FUTURE remove
		if ( is_wp_error( $result ) ) {
			EPKB_Logging::add_log( 'Could not update plugin version', $result );
			return;
		}
	}

	public static function force_plugin_11_30_0_upgrade( &$kb_config ) {
		self::upgrade_to_v11_30_0( $kb_config );
	}

	/**
	 * Invoke each database update as necessary.
	 *
	 * @param $last_version
	 */
	private static function invoke_upgrades( $last_version ) {

		// update all KBs
		$all_kb_configs = epkb_get_instance()->kb_config_obj->get_kb_configs();
		foreach ( $all_kb_configs as $kb_config ) {

			self::run_upgrade( $kb_config, $last_version );

			$kb_config['upgrade_plugin_version'] = Echo_Knowledge_Base::$version;

			// store the updated KB data
			epkb_get_instance()->kb_config_obj->update_kb_configuration( $kb_config['id'], $kb_config );
		}
	}

	public static function run_upgrade( &$kb_config, $last_version ) {

		if ( version_compare( $last_version, '8.0.0', '<' ) ) {
		    self::upgrade_to_v800( $kb_config );
	    }

	    if ( version_compare( $last_version, '8.1.0', '<' ) ) {
		    self::upgrade_to_v810( $kb_config );
	    }

	    if ( version_compare( $last_version, '8.2.0', '<' ) ) {
		    self::upgrade_to_v820( $kb_config );
	    }

	    if ( version_compare( $last_version, '9.0.0', '<' ) ) {
		    self::upgrade_to_v900( $kb_config );
	    }

	    if ( version_compare( $last_version, '9.1.0', '<' ) ) {
		    self::upgrade_to_v910( $kb_config );
	    }

	    if ( version_compare( $last_version, '9.11.0', '<' ) ) {
		    self::upgrade_to_v9_11_0( $kb_config );
	    }

		if ( version_compare( $last_version, '9.12.0', '<' ) ) {
			self::upgrade_to_v9_12_0( $kb_config );
		}

	    if ( version_compare( $last_version, '11.0.1', '<' ) ) {
		    self::upgrade_to_v11_0_1( $kb_config );
	    }

		if ( version_compare( $last_version, '11.20.0', '<' ) ) {
			self::upgrade_to_v11_20_0( $kb_config );
		}

		if ( version_compare( $last_version, '11.30.0', '<' ) ) {
			self::upgrade_to_v11_30_0( $kb_config );
		}

		if ( version_compare( $last_version, '11.30.1', '<' ) ) {
			self::upgrade_to_v11_30_1( $kb_config );
		}
	}

	private static function upgrade_to_v11_30_1( &$kb_config ) {

		// handle article list spacing
		if ( EPKB_Utilities::is_elegant_layouts_enabled() && function_exists( 'elay_get_instance' ) && isset( elay_get_instance()->kb_config_obj ) ) {
			$elay_config = elay_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_config['id'] );
			if ( $kb_config['kb_main_page_layout'] == EPKB_Layout::GRID_LAYOUT && isset( $elay_config['grid_article_list_spacing'] ) ) {
				$kb_config['article_list_spacing'] = $elay_config['grid_article_list_spacing'];
			}
			if ( $kb_config['kb_main_page_layout'] == EPKB_Layout::SIDEBAR_LAYOUT && isset( $elay_config['sidebar_article_list_spacing'] ) ) {
				$kb_config['article_list_spacing'] = $elay_config['sidebar_article_list_spacing'];
			}

			// ensure $kb_config['article_list_spacing'] is valid parameter for min function
			$article_list_spacing = (int)$kb_config['article_list_spacing'];
			$article_list_spacing =  min( $article_list_spacing, 50 );
			$kb_config['article_list_spacing'] = empty( $article_list_spacing ) ? 8 : $article_list_spacing;
		}

		// previously Article Page Search had the same layout as Main Page Search
		$kb_config['ml_article_search_layout'] = $kb_config['ml_search_layout'];

		// only new users have Article Page Search synced with Main Page Search by default
		$kb_config['article_search_sync_toggle'] = 'off';
	}

	private static function upgrade_to_v11_30_0( &$kb_config ) {

		$kb_config['ml_categories_articles_sidebar_location'] = isset( $kb_config['ml_categories_articles_sidebar_location'] ) ? $kb_config['ml_categories_articles_sidebar_location'] : 'right';
		if ( $kb_config['ml_categories_articles_sidebar_location'] == 'none' ) {
			$kb_config['ml_categories_articles_sidebar_toggle'] = 'off';
			$kb_config['ml_categories_articles_sidebar_location'] = 'right';
		}

		// starting from version 11.30.0 the Main Page is Modular by default (the toggle is 'on' in specs); ensure it is 'off' if the user did not use Modular before the upgrade
		if ( $kb_config['kb_main_page_layout'] != 'Modular' ) {
			$kb_config['modular_main_page_toggle'] = 'off';
		}

		// transfer storing values of Modular config to corresponding refactored settings only if the Modular Main Page Layout is enabled, otherwise the default values will be used from specs
		if ( $kb_config['kb_main_page_layout'] == 'Modular' ) {
			$kb_config['modular_main_page_toggle'] = 'on';

			// do not add Popular Articles to Articles List module after upgrade
			$kb_config['ml_articles_list_column_1'] = 'none';

			// refactor Modular settings for Categories & Articles module to use shared configuration
			$kb_config['section_head_category_icon_size'] = isset( $kb_config['ml_categories_articles_icon_size'] ) ? $kb_config['ml_categories_articles_icon_size'] : $kb_config['section_head_category_icon_size'];
			$kb_config['section_head_category_icon_color'] = isset( $kb_config['ml_categories_articles_icon_color'] ) ? $kb_config['ml_categories_articles_icon_color'] : $kb_config['section_head_category_icon_color'];
			if ( isset( $kb_config['ml_categories_articles_height_mode'] ) ) {
				$kb_config['section_box_height_mode'] = $kb_config['ml_categories_articles_height_mode'] == 'variable' ? 'section_no_height' : 'section_min_height';
			}
			$kb_config['section_body_height'] = isset( $kb_config['ml_categories_articles_fixed_height'] ) ? $kb_config['ml_categories_articles_fixed_height'] : $kb_config['section_body_height'];
			$kb_config['nof_articles_displayed'] = isset( $kb_config['ml_categories_articles_nof_articles_displayed'] ) ? $kb_config['ml_categories_articles_nof_articles_displayed'] : $kb_config['nof_articles_displayed'];
			$kb_config['section_head_font_color'] = isset( $kb_config['ml_categories_articles_top_category_title_color'] ) ? $kb_config['ml_categories_articles_top_category_title_color'] : $kb_config['section_head_font_color'];
			if ( isset( $kb_config['ml_categories_articles_sub_category_color'] ) ) {
				$kb_config['section_category_font_color'] = $kb_config['ml_categories_articles_sub_category_color'];
				$kb_config['section_category_icon_color'] = $kb_config['ml_categories_articles_sub_category_color'];
			}
			if ( isset( $kb_config['ml_categories_articles_article_color'] ) ) {
				$kb_config['article_font_color'] = $kb_config['ml_categories_articles_article_color'];
				$kb_config['article_icon_color'] = $kb_config['ml_categories_articles_article_color'];
			}
			$kb_config['section_head_description_font_color'] = isset( $kb_config['ml_categories_articles_cat_desc_color'] ) ? $kb_config['ml_categories_articles_cat_desc_color'] : $kb_config['section_head_description_font_color'];
			if ( isset( $kb_config['ml_categories_columns'] ) ) {
				switch ( $kb_config['ml_categories_columns'] ) {
					case '2-col': $kb_config['nof_columns'] = 'two-col'; break;
					case '3-col': $kb_config['nof_columns'] = 'three-col'; break;
					case '4-col': $kb_config['nof_columns'] = 'four-col'; break;
					default: break;
				}
			}

			// refactor Modular to Classic and Drill-Down
			if ( isset( $kb_config['ml_categories_articles_layout'] ) && $kb_config['ml_categories_articles_layout'] == 'classic' ) {
				$kb_config['kb_main_page_layout'] = EPKB_Layout::CLASSIC_LAYOUT;

				// fit previous styles in .css file
				$kb_config['section_border_color'] = '#ffffff';

			} else {
				$kb_config['kb_main_page_layout'] = EPKB_Layout::DRILL_DOWN_LAYOUT;

				// fit previous styles in .css file
				if( isset( $kb_config['ml_categories_articles_border_color'] ) ) {
					$kb_config['section_border_color'] = $kb_config['ml_categories_articles_border_color'];
				}
			}

			$kb_config['section_desc_text_on'] = 'on';

			// ensure icons are at the same place after refactoring from Modular to Classic or Drill-Down layout
			$kb_config['section_head_category_icon_location'] = 'top';

			// fit previous styles in .css file
			$kb_config['section_border_width'] = '1';
			$kb_config['section_border_radius'] = '15';
			$kb_config['background_color'] = '';
		}

		// rename settings
		$kb_config['ml_categories_articles_category_title_html_tag'] = isset( $kb_config['ml_categories_articles_title_html_tag'] ) ? $kb_config['ml_categories_articles_title_html_tag'] : $kb_config['ml_categories_articles_category_title_html_tag'];
		$kb_config['ml_categories_articles_top_category_icon_bg_color_toggle'] = isset( $kb_config['ml_categories_articles_icon_background_color_toggle'] ) ? $kb_config['ml_categories_articles_icon_background_color_toggle'] : $kb_config['ml_categories_articles_top_category_icon_bg_color_toggle'];
		$kb_config['ml_categories_articles_top_category_icon_bg_color'] = isset( $kb_config['ml_categories_articles_icon_background_color'] ) ? $kb_config['ml_categories_articles_icon_background_color'] : $kb_config['ml_categories_articles_top_category_icon_bg_color'];

		// Copy search width to row settings
		$row_number = 5;
		while ( $row_number > 0 ) {
			if ( ! empty( $kb_config['ml_row_' . $row_number . '_module'] ) && $kb_config['ml_row_' . $row_number . '_module'] == 'search' ) {

				if ( $kb_config['width'] == 'epkb-boxed' ) {
					$kb_config['ml_row_' . $row_number . '_desktop_width'] = '1080';
					$kb_config['ml_row_' . $row_number . '_desktop_width_units'] = 'px';
				} else {
					$kb_config['ml_row_' . $row_number . '_desktop_width'] = '100';
					$kb_config['ml_row_' . $row_number . '_desktop_width_units'] = '%';
				}
			}

			$row_number--;
		}

		$plugin_first_version = EPKB_Utilities::get_wp_option( 'epkb_version_first', '' );
		if ( ! empty( $plugin_first_version ) ) {
			$kb_config['first_plugin_version'] = $plugin_first_version;
		}
	}

	private static function upgrade_to_v11_20_0( &$kb_config ) {
		$plugin_first_version = EPKB_Utilities::get_wp_option( 'epkb_version_first', '' );
		if ( ! empty( $plugin_first_version ) ) {
			$kb_config['first_plugin_version'] = $plugin_first_version;
		}
	}

	private static function upgrade_to_v11_0_1( &$kb_config ) {
		if ( isset( $kb_config['ml_faqs_kb_id'] ) ) {
			$ml_faqs_kb_id = $kb_config['ml_faqs_kb_id'];
			EPKB_Utilities::save_kb_option( $kb_config['id'], EPKB_ML_FAQs::FAQS_KB_ID, $ml_faqs_kb_id );
		}
		if ( isset( $kb_config['ml_faqs_category_ids'] ) ) {
			$faqs_category_ids = explode( ',', $kb_config['ml_faqs_category_ids'] );
			EPKB_Utilities::save_kb_option( $kb_config['id'], EPKB_ML_FAQs::FAQS_CATEGORY_IDS, $faqs_category_ids );
		}
	}

	private static function upgrade_to_v9_12_0( &$kb_config ) {
		if ( ! in_array( $kb_config['search_title_html_tag'], ['div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'] ) ) {
			$kb_config['search_title_html_tag'] = 'div';
		}

		if ( ! in_array( $kb_config['article_search_title_html_tag'], ['div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'] ) ) {
			$kb_config['article_search_title_html_tag'] = 'div';
		}
	}

	private static function upgrade_to_v9_11_0( &$kb_config ) {
		if ( $kb_config['templates_for_kb'] == 'current_theme_templates' ) {
			$kb_config['article_content_enable_article_title'] = 'off';
		}
	}

	private static function upgrade_to_v910( &$kb_config ) {
		if ( isset( $kb_config['last_udpated_on_text'] ) ) {
			$kb_config['last_updated_on_text'] = $kb_config['last_udpated_on_text'];
		}

		if ( isset( $kb_config['last_udpated_on_footer_toggle'] ) ) {
			$kb_config['last_updated_on_footer_toggle'] = $kb_config['last_udpated_on_footer_toggle'];
		}
	}

	private static function upgrade_to_v900( &$kb_config ) {

		// update navigation sidebar config
		$kb_config['article_nav_sidebar_type_left'] = EPKB_Core_Utilities::get_nav_sidebar_type( $kb_config, 'left' );
		$kb_config['article_nav_sidebar_type_right'] = EPKB_Core_Utilities::get_nav_sidebar_type( $kb_config, 'right' );

		// handle Elegant Layouts upgrade
		if ( ! EPKB_Utilities::is_elegant_layouts_enabled() ) {
			return;
		}

		if ( function_exists( 'elay_get_instance' ) && isset( elay_get_instance()->kb_config_obj ) ) {
			$elay_config = elay_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_config['id'] );
		} else {
			return;
		}

		$sidebar_settings = [
			'sidebar_side_bar_height_mode',
			'sidebar_side_bar_height',
			'sidebar_scroll_bar',
			'sidebar_section_category_typography',
			'sidebar_section_category_typography_desc',
			'sidebar_section_body_typography',
			'sidebar_top_categories_collapsed',
			'sidebar_nof_articles_displayed',
			'sidebar_show_articles_before_categories',
			'sidebar_expand_articles_icon',
			'sidebar_section_head_alignment',
			'sidebar_section_head_padding_top',
			'sidebar_section_head_padding_bottom',
			'sidebar_section_head_padding_left',
			'sidebar_section_head_padding_right',
			'sidebar_section_desc_text_on',
			'sidebar_section_border_radius',
			'sidebar_section_border_width',
			'sidebar_section_box_shadow',
			'sidebar_section_divider',
			'sidebar_section_divider_thickness',
			'sidebar_section_box_height_mode',
			'sidebar_section_body_height',
			'sidebar_section_body_padding_top',
			'sidebar_section_body_padding_bottom',
			'sidebar_section_body_padding_left',
			'sidebar_section_body_padding_right',
			'sidebar_article_underline',
			'sidebar_article_active_bold',
			'sidebar_article_list_margin',
			'sidebar_article_list_spacing',
			'sidebar_background_color',
			'sidebar_article_font_color',
			'sidebar_article_icon_color',
			'sidebar_article_active_font_color',
			'sidebar_article_active_background_color',
			'sidebar_section_head_font_color',
			'sidebar_section_head_background_color',
			'sidebar_section_head_description_font_color',
			'sidebar_section_border_color',
			'sidebar_section_divider_color',
			'sidebar_section_category_font_color',
			'sidebar_section_subcategory_typography',
			'sidebar_section_category_icon_color',
			'sidebar_category_empty_msg',
			'sidebar_collapse_articles_msg',
			'sidebar_show_all_articles_msg'
		];

		foreach ( $sidebar_settings as $setting_name ) {
			if ( ! isset( $elay_config[$setting_name] ) ) {
				continue;
			}

			$kb_config[$setting_name] = $elay_config[$setting_name];
		}
	}

	private static function upgrade_to_v820( &$kb_config ) {
		$kb_config['admin_eckb_access_frontend_editor_write'] = empty( $kb_config['access_frontend_editor_write'] ) ? EPKB_Admin_UI_Access::get_admin_capability() : $kb_config['access_frontend_editor_write'];
		$kb_config['admin_eckb_access_search_analytics_read'] = empty( $kb_config['access_search_analytics_read'] ) ? EPKB_Admin_UI_Access::get_admin_capability() : $kb_config['access_search_analytics_read'];
		$kb_config['admin_eckb_access_order_articles_write'] = empty( $kb_config['access_order_articles_write'] ) ? EPKB_Admin_UI_Access::get_admin_capability() : $kb_config['access_order_articles_write'];
		$kb_config['admin_eckb_access_need_help_read'] = empty( $kb_config['access_need_help_read'] ) ? EPKB_Admin_UI_Access::get_admin_capability() : $kb_config['access_need_help_read'];
		$kb_config['admin_eckb_access_addons_news_read'] = empty( $kb_config['access_addons_news_read'] ) ? EPKB_Admin_UI_Access::get_admin_capability() : $kb_config['access_addons_news_read'];
    }

	private static function upgrade_to_v810( &$kb_config ) {
		$kb_config['admin_eckb_access_frontend_editor_write'] = EPKB_Admin_UI_Access::get_admin_capability();
		$kb_config['admin_eckb_access_search_analytics_read'] = EPKB_Admin_UI_Access::get_admin_capability();
		$kb_config['admin_eckb_access_order_articles_write'] = EPKB_Admin_UI_Access::get_admin_capability();
		$kb_config['admin_eckb_access_need_help_read'] = EPKB_Admin_UI_Access::get_editor_capability();
		$kb_config['admin_eckb_access_addons_news_read'] = EPKB_Admin_UI_Access::get_editor_capability();
	}

	private static function upgrade_to_v800( &$kb_config ) {
		$kb_config['article-meta-typography'] = array_merge( EPKB_Typography::$typography_defaults, $kb_config['breadcrumb_typography'] );
	}

	/**
	 * Function for major updates
	 *
	 * @param $args
	 */
	public function in_plugin_update_message( $args ) {

		$current_version = Echo_Knowledge_Base::$version;
		$new_version = empty( $args['new_version'] ) ? $current_version : $args['new_version'];

		// versions x.y0.z are major releases
		if ( ! preg_match( '/.*\.\d0\..*/', $new_version ) ) {
			return;
		}

		echo '<style> .epkb-update-warning+p { opacity: 0; height: 0;} </style> ';
		echo '<hr style="clear:left"><div class="epkb-update-warning"><span class="dashicons dashicons-info" style="float:left;margin-right: 6px;color: #d63638;"></span>';
		echo '<div class="epkb-update-warning__title">' . esc_html__( 'We highly recommend you back up your site before upgrading. Next, run the update in a staging environment.', 'echo-knowledge-base' ) . '</div>';
		echo '<div class="epkb-update-warning__message">' .	esc_html__( 'After you run the update, clear your browser cache, hosting cache, and caching plugins.', 'echo-knowledge-base' ) . '</div>';
		echo '<div class="epkb-update-warning__message">' .	esc_html__( 'The latest update includes some substantial changes across different areas of the plugin', 'echo-knowledge-base' ) . '</div>';
	}

	function after_switch_theme() {
		EPKB_Core_Utilities::update_kb_flag( 'epkb_the_content_fix', false );
	}
}