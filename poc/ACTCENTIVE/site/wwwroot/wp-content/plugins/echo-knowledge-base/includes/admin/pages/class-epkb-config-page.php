<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display KB configuration menu and pages
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class EPKB_Config_Page {

	private $message = array(); // error/warning/success messages
	private $kb_config;
	private $kb_main_pages;

	// Show error/success messages
	function __construct() {
		$this->message = EPKB_KB_Config_Controller::handle_form_actions();
	}

	/**
	 * Displays the KB Config page with top panel + sidebar + preview panel
	 */
	public function display_kb_config_page() {

		// ensure KB config is there
		$kb_id = EPKB_KB_Handler::get_current_kb_id();
		$this->kb_config = epkb_get_instance()->kb_config_obj->get_kb_config( $kb_id, true );
		if ( is_wp_error( $this->kb_config ) || empty( $this->kb_config ) || ! is_array( $this->kb_config ) || count( $this->kb_config ) < 100 ) {
			EPKB_Logging::add_log( 'Could not retrieve KB configuration (715)', $this->kb_config );
			self::display_config_error_page();
			return;
		}

		// ensure user KB has first KB version
		$kb_first_version = EPKB_Utilities::get_wp_option( 'epkb_version_first', null );
		if ( empty( $kb_first_version ) ) {
			EPKB_Utilities::save_wp_option( 'epkb_version_first', Echo_Knowledge_Base::$version );
		}

		// get current add-ons configuration
		$wizard_kb_config = $this->kb_config;
		$wizard_kb_config = apply_filters( 'epkb_all_wizards_get_current_config', $wizard_kb_config, $kb_id );
		if ( is_wp_error( $wizard_kb_config ) || empty( $wizard_kb_config ) || ! is_array( $wizard_kb_config ) || count( $wizard_kb_config ) < 100 ) {
			self::display_config_error_page();
			return;
		}

		EPKB_HTML_Admin::admin_page_css_missing_message();

		// regenerate KB sequence for Categories and Articles if missing
		EPKB_KB_Handler::get_refreshed_kb_categories( $kb_id );

		//-------------------------------- SETUP WIZARD --------------------------------

		// should we display Setup Wizard or KB Configuration?
		if ( isset( $_GET['setup-wizard-on'] ) && EPKB_Admin_UI_Access::is_user_access_to_context_allowed( 'admin_eckb_access_frontend_editor_write' ) ) {
			$handler = new EPKB_KB_Wizard_Setup( $wizard_kb_config );
			$handler->display_kb_setup_wizard();
			return;
		}


		//---------------------- GENERAL CONFIGURATION PAGE -----------------------

		// retrieve KB Main Pages
		$this->kb_main_pages = EPKB_KB_Handler::get_kb_main_pages( $this->kb_config );

		/**
		 * Views of the Configuration Admin Page - show limited content for users that did not complete Setup Wizard
		 */
		if ( isset( $_GET['archived-kbs'] ) ) {
			$admin_page_views = self::get_archived_kbs_views_config();
		} else {
			$admin_page_views = EPKB_Core_Utilities::is_run_setup_wizard_first_time()
				? self::get_run_setup_first_views_config()
				: $this->get_regular_views_config( $wizard_kb_config );
		}   ?>

		<!-- Admin Page Wrap -->
		<div id="ekb-admin-page-wrap">

			<div class="epkb-kb-config-page-container">    <?php

				/**
				 * ADMIN HEADER (KB logo and list of KBs dropdown)
				 */
				EPKB_HTML_Admin::admin_header( $this->kb_config, ['admin_eckb_access_order_articles_write', 'admin_eckb_access_frontend_editor_write'] );

				/**
				 * ADMIN TOOLBAR
				 */
				EPKB_HTML_Admin::admin_primary_tabs( $admin_page_views );

				/**
				 * ADMIN SECONDARY TABS
				 */
				EPKB_HTML_Admin::admin_secondary_tabs( $admin_page_views );

				/**
				 * LIST OF SETTINGS IN TABS
				 */
				EPKB_HTML_Admin::admin_primary_tabs_content( $admin_page_views );

				// generic confirmation box to reload page
				EPKB_HTML_Forms::dialog_confirm_action( array(
					'id'                => 'epkb-admin-page-reload-confirmation',
					'title'             => __( 'Change KB Main Page Layout', 'echo-knowledge-base' ),
					'accept_label'      => __( 'Ok', 'echo-knowledge-base' ),
					'accept_type'       => 'primary',
					'show_cancel_btn'   => 'yes',
					'show_close_btn'    => 'no',
				) );    ?>

			</div>

		</div>  <?php

		/**
		 * Show any notifications
		 */
		foreach ( $this->message as $class => $message ) {
			echo  EPKB_HTML_Forms::notification_box_bottom( $message, '', $class );
		}
	}

	/**
	 * Get configuration array for regular views of the KB Configuration page
	 *
	 * @param $wizard_kb_config
	 * @return array[]
	 */
	private function get_regular_views_config( $wizard_kb_config ) {

		/**
		 * PRIMARY TAB: Settings
		 */
		$settings_tab_handler = new EPKB_Config_Settings_Page( $this->kb_config );
		$settings_view_config = array(

			// Shared
			'minimum_required_capability' => EPKB_Admin_UI_Access::get_context_required_capability( 'admin_eckb_access_frontend_editor_write' ),
			'list_key' => 'settings',

			// Top Panel Item
			'label_text' => __( 'Settings', 'echo-knowledge-base' ),
			'icon_class' => 'epkbfa epkbfa-cogs',
			'vertical_tabs' => $settings_tab_handler->get_vertical_tabs_config()
		);

		/**
		 * PRIMARY TAB: Ordering
		 */
		$wizard_ordering = new EPKB_KB_Wizard_Ordering();
		$ordering_view_config = array(

			// Shared
			'minimum_required_capability' => EPKB_Admin_UI_Access::get_context_required_capability( 'admin_eckb_access_order_articles_write' ),
			'list_key' => 'ordering',
			'kb_config_id' => $this->kb_config['id'],

			// Top Panel Item
			'label_text' => __( 'Order Articles and Categories', 'echo-knowledge-base' ),
			'icon_class' => 'epkbfa epkbfa-cubes',

			// Boxes List
			'boxes_list' => array(

				array(
					'class' => 'epkb-admin__boxes-list__box__ordering',
					'title' => __( 'Ordering Settings', 'echo-knowledge-base' ),
					'description' => '',
					'html' => $wizard_ordering->show_article_ordering( $wizard_kb_config ),
				),
			),
		);

		/**
		 * PRIMARY TAB: KB URLs
		 */
		$kb_url_view_config = array(

			// Shared
			'minimum_required_capability' => EPKB_Admin_UI_Access::get_admin_capability(),
			'list_key' => 'kb-url',
			'kb_config_id' => $this->kb_config['id'],

			// Top Panel Item
			'label_text' => __( 'KB URLs', 'echo-knowledge-base' ),
			'icon_class' => 'epkbfa epkbfa-link',

			// Boxes List
			'boxes_list' => self::get_kb_urls_config( $wizard_kb_config )
		);

		/**
		 * PRIMARY TAB: Widgets / Shortcode
		 */
		$kb_widgets_view_config = array(

			// Shared
			'active' => false,
			'list_key' => 'widgets',
			'minimum_required_capability' => EPKB_Admin_UI_Access::get_editor_capability(),
			'kb_config_id' => $this->kb_config['id'],

			// Top Panel Item
			'label_text' => __( 'Shortcodes' ) . ' / ' . __( 'Widgets', 'echo-knowledge-base' ),
			'icon_class' => 'epkbfa epkbfa-list-alt',

			// Secondary Panel Items
			'secondary_tabs'  => array(

				// SECONDARY VIEW: SHORTCODES
				array(

					// Shared
					'list_key'   => 'shortcodes',
					'active'     => true,

					// Secondary Panel Item
					'label_text' => __( 'Shortcodes', 'echo-knowledge-base' ),

					// Secondary Boxes List
					'boxes_list' => self::get_widgets_boxes( $this->get_shortcodes_boxes_config() )
				),

				// SECONDARY VIEW: WIDGETS
				array(

					// Shared
					'list_key'   => 'widgets',

					// Secondary Panel Item
					'label_text' => __( 'Widgets', 'echo-knowledge-base' ),

					// Secondary Boxes List
					'boxes_list' => self::get_widgets_boxes( self::get_widgets_boxes_config() )
				)
			),
		);

		/**
		 * PRIMARY TAB: TOOLS
		 */
		$tools_view_config = EPKB_Config_Tools_Page::get_tools_view_config( $this->kb_config );


		/**
		 * OUTPUT PRIMARY TABS
		 */

		// compose views
		$core_views = [];

		$errors_tab_config = $this->get_errors_view_config();
		if ( ! empty( $errors_tab_config ) ) {
			$core_views[] = $errors_tab_config;
		}

		// Limited config for archived KBs
		if ( ! EPKB_Core_Utilities::is_kb_archived( $this->kb_config['status'] ) ) {
			$core_views[] = $settings_view_config;
			$core_views[] = $ordering_view_config;
			$core_views[] = $kb_url_view_config;
			$core_views[] = $kb_widgets_view_config;
			$core_views[] = $tools_view_config;
		}

		/**
		 * Add-on views for KB Configuration page
		 */
		$add_on_views = apply_filters( 'eckb_admin_config_page_views', [], $this->kb_config );
		if ( empty( $add_on_views ) || ! is_array( $add_on_views ) ) {
			$add_on_views = [];
		}

		$all_views = array_merge( $core_views, $add_on_views );

		if ( ! EPKB_Articles_Setup::is_article_structure_v2( $this->kb_config ) ) {
			foreach ( $all_views as &$view ) {
				$view['boxes_list'] = [ [
					'class' => 'epkb-admin__boxes-list__box__editors-list',
					'title' => __( 'Deprecates Settings Error', 'echo-knowledge-base' ),
					'html' => $this->get_article_version_error_box(),
				] ];

				unset( $view['secondary_tabs'] );
			}
		}

		// Full config for published KBs
		return $all_views;
	}

	/**
	 * Display KB URLs page
	 *
	 * @param $wizard_kb_config
	 * @return array
	 */
	private function get_kb_urls_config( $wizard_kb_config ) {
		$kb_url_boxes = [];

		// Box: Help box with Docs link for URL changing
		$kb_url_boxes[] = array(
			'title' => __( 'How To Change KB URLs', 'echo-knowledge-base' ),
			'html' => EPKB_HTML_Forms::notification_box_middle( array(
					'type'  => 'info',
					'title' => esc_html__( 'Need to change KB URLs?', 'echo-knowledge-base' ),
					'desc'  => sprintf( '<a href="%s" target="_blank">%s <span class="ep_font_icon_external_link"></span></a>',
						'https://www.echoknowledgebase.com/documentation/changing-permalinks-urls-and-slugs/', esc_html__( 'Learn More', 'echo-knowledge-base' ) )
				), true  ),
		);

		if ( empty( $this->kb_main_pages ) ) {
			$kb_url_boxes[] = array(
				'title' => __( 'Your Knowledge Base URL', 'echo-knowledge-base' ),
				'html' => $this->display_no_shortcode_warning( $this->kb_config, true ),
				'class' => 'epkb-admin__warning-box',
			);

		} else {

			// Box: Category Name in KB URL
			$kb_url_boxes[] = array(
				'title' => __( 'Category Name in KB URL', 'echo-knowledge-base' ),
				'html' => EPKB_HTML_Elements::checkbox_toggle( array(
					'id'            => 'categories_in_url_enabled__toggle',
					'textLoc'       => 'right',
					'data'          => 'on',
					'toggleOnText'  => __( 'yes', 'echo-knowledge-base' ),
					'toggleOffext'  => __( 'no', 'echo-knowledge-base' ),
					'checked'       => $this->kb_config['categories_in_url_enabled'] == 'on',
					'return_html'   => true,
					'topDesc'       => __( 'Should article URLs contain the slug of their categories?', 'echo-knowledge-base' ),
				) ),
				'class' => 'epkb-admin__toggle-box',
			);

			// Box: KB Location
			$kb_url_boxes[] = array(
				'class' => 'epkb-admin__boxes-list__box__kb-location',
				'title' => __( 'KB Location', 'echo-knowledge-base' ),
				'description' => '',
				'html' => $this->get_kb_location_box(),
			);

			// Box: Your Knowledge Base URL
			$wizard_global = new EPKB_KB_Wizard_Global( $wizard_kb_config );

			$kb_url_boxes[] = array(
				'title' => __( 'Knowledge Base URL', 'echo-knowledge-base' ),
				'html' => $wizard_global->show_kb_urls_global_wizard(),
				'class' => 'epkb-admin__wizard-box',
			);
		}

		return $kb_url_boxes;
	}

	/**
	 * Get KB Location settings box
	 *
	 * @return false|string
	 */
	private function get_kb_location_box() {

		$HTML = new EPKB_HTML_Forms();
		ob_start();

		// If no Main Pages were detected for the current KB
		if ( empty( $this->kb_main_pages ) ) {
			$this->display_no_shortcode_warning( $this->kb_config );

			// If at least one KB Main Page exists for the current KB
		} else {
			$kb_main_page_url = EPKB_KB_Handler::get_first_kb_main_page_url( $this->kb_config );
			$kb_page_id = EPKB_KB_Handler::get_first_kb_main_page_id( $this->kb_config );     ?>

			<div class="epkb-admin__chapter"><?php esc_html_e( 'Your knowledge base will be displayed on the page with KB shortcode: ', 'echo-knowledge-base' ); ?><strong>[epkb-knowledge-base id=<?php echo esc_attr( $this->kb_config['id'] ); ?>]</strong></div>
			<table class="epkb-admin__chapter__wrap">
				<tbody>
				<tr class="epkb-admin__chapter__content">
					<td><span><?php esc_html_e( 'Page Title: ', 'echo-knowledge-base' ); ?></span></td>
					<td><span><?php echo esc_html( $this->kb_config['kb_main_pages'][$kb_page_id] ); ?></span></td>
					<td><a class="epkb-primary-btn" href="<?php echo esc_url( get_edit_post_link( $kb_page_id ) ); ?>" target="_blank"><?php esc_html_e( 'Change Title', 'echo-knowledge-base' ); ?></a></td>
				</tr>
				<tr class="epkb-admin__chapter__content">
					<td><span><?php esc_html_e( 'Page / KB URL: ', 'echo-knowledge-base' ); ?></span></td>
					<td><a href="<?php echo esc_url( $kb_main_page_url ); ?>" target="_blank"><?php echo esc_html(  $kb_main_page_url ); ?><i class="ep_font_icon_external_link"></i></a></td>
					<td></td>
				</tr>
				<tr class="epkb-admin__chapter__content"><td colspan="3"></td></tr>
				<tr class="epkb-admin__chapter__content">
					<td colspan="3"><b><?php esc_html_e( 'Need to change KB URLs?', 'echo-knowledge-base' ); ?></b>
						<a href="https://www.echoknowledgebase.com/documentation/changing-permalinks-urls-and-slugs/" target="_blank"><?php esc_html_e( 'Learn More', 'echo-knowledge-base' ); ?> <i class="ep_font_icon_external_link"></i></a>
					</td>
				</tr>
				</tbody>
			</table>      <?php

			// If user has multiple pages with KB Shortcode then let them know this is normal for WPML users
			if ( count( $this->kb_main_pages ) > 1 && ! EPKB_Utilities::is_wpml_enabled( $this->kb_config ) ) {        ?>
				<div class="epkb-admin__chapter"><?php echo sprintf( esc_html__( 'Note: You have other pages with KB shortcode that are currently %snot used%s: ', 'echo-knowledge-base' ), '<strong>', '</strong>' ); ?></div>
				<ul class="epkb-admin__items-list">    <?php

					foreach ( $this->kb_main_pages as $page_id => $page_info ) {

						// Do not show relevant KB Main Page in the extra Main Pages list
						if ( $page_id == $kb_page_id ) {
							continue;
						}   ?>

						<li><span><?php echo esc_html( $page_info['post_title'] ); ?></span> <a href="<?php echo esc_url( get_edit_post_link( $page_id ) ); ?>" target="_blank"><?php esc_html_e( 'Edit page', 'echo-knowledge-base' ); ?></a></li><?php
					}   ?>

				</ul>                <?php
				$HTML::notification_box_middle( array(
					'type' => 'error-no-icon',
					'desc' => __( "It's best to remove KB shortcode from these pages unless you have a very specific reason for having them.", 'echo-knowledge-base' ),
					'' => '',
				));
			}
		}

		return ob_get_clean();
	}

	/**
	 * Get boxes for Widgets / Shortcode panel
	 *
	 * @param $boxes_content
	 * @return array
	 */
	private static function get_widgets_boxes( $boxes_content ) {

		$boxes = [];
		foreach ( $boxes_content as $box ) {

            // Hide install button for all Widgets / Shortcode boxes
			$box['hide_install_btn'] = true;

			$box['active_status'] = EPKB_Utilities::is_plugin_enabled( $box['plugin'] );

            // Add box separator heading
            if ( isset( $box['box-heading'] ) ) {
	            $boxes[] = [
		            'class' => 'epkb-kbnh__feature-heading',
		            'html'  => self::get_box_heading_html( $box ),
	            ];
                continue;
            }

			$boxes[] = [
				'class' => 'epkb-kbnh__feature-container',
				'html'  => EPKB_HTML_Forms::get_feature_box_html( $box ),
			];
		}

        return $boxes;
	}

	/**
	 * Get boxes config for Widgets
	 *
	 * @return array
	 */
	private static function get_widgets_boxes_config() {

		return [
			[
				'plugin'    => 'core',
				'icon'      => 'epkbfa epkbfa-list-alt',
				'title'     => __( 'Widgets for Elementor', 'echo-knowledge-base' ),
				'desc'      => __( 'Our Elementor widgets are designed for writers. We make it easy to write great instructions, step-by-step guides, manuals and detailed documentation.', 'echo-knowledge-base' ),
				'docs'      => 'https://www.echoknowledgebase.com/documentation/elementor-widgets-for-documentation/',
			],
			[
				'plugin'      => 'ep'.'hd',
				'box-heading' => __( 'Help Dialog Plugin', 'echo-knowledge-base' ),
			],
			[
				'plugin'    => 'ep'.'hd',
				'icon'      => 'epkbfa epkbfa-list-alt',
				'title'     => __( 'Help Dialog', 'echo-knowledge-base' ),
				'desc'      => __( 'Help Dialog is a frontend dialog where users can easily search for answers, browse FAQs and submit contact form.', 'echo-knowledge-base' ),
				'docs'      => 'https://www.helpdialog.com/documentation/',
				'video'     => '',
			],
			[
				'plugin'      => 'widg',
				'box-heading' => __( 'Widgets Add-on', 'echo-knowledge-base' ),
			],
			[
				'plugin'    => 'widg',
				'icon'      => 'epkbfa epkbfa-list-alt',
				'title'     => __( 'Recent Articles', 'echo-knowledge-base' ),
				'desc'      => __( 'Show either recently created or recently modified KB Articles.', 'echo-knowledge-base' ),
				'config'    => admin_url( '/widgets.php' ),
				'docs'      => 'https://www.echoknowledgebase.com/documentation/recent-articles-widget/',
				'video'     => '',
			],
			[
				'plugin'    => 'widg',
				'icon'      => 'epkbfa epkbfa-list-alt',
				'title'     => __( 'Popular Articles', 'echo-knowledge-base' ),
				'desc'      => __( 'Show a list of the most popular articles based on article views.', 'echo-knowledge-base' ),
				'config'    => admin_url( '/widgets.php' ),
				'docs'      => 'https://www.echoknowledgebase.com/documentation/popular-articles-widget/',
				'video'     => '',
			],
			[
				'plugin'    => 'widg',
				'icon'      => 'epkbfa epkbfa-list-alt',
				'title'     => __( 'KB Sidebar', 'echo-knowledge-base' ),
				'desc'      => __( 'A dedicated KB Sidebar will be shown only on the left side or right side of your KB articles.', 'echo-knowledge-base' ),
				'config'    => admin_url( '/widgets.php' ),
				'docs'      => 'https://www.echoknowledgebase.com/documentation/kb-sidebar/',
				'video'     => '',
			],
			[
				'plugin'    => 'widg',
				'icon'      => 'epkbfa epkbfa-list-alt',
				'title'     => __( 'KB Search', 'echo-knowledge-base' ),
				'desc'      => __( 'Add a search box on your Home page, Contact Us page, and others.', 'echo-knowledge-base' ),
				'config'    => admin_url( '/widgets.php' ),
				'docs'      => 'https://www.echoknowledgebase.com/documentation/search-widget/',
				'video'     => '',
			],
			[
				'plugin'    => 'widg',
				'icon'      => 'epkbfa epkbfa-list-alt',
				'title'     => __( 'KB Categories', 'echo-knowledge-base' ),
				'desc'      => __( 'List your KB Categories for easy reference, which are typically displayed in sidebars.', 'echo-knowledge-base' ),
				'config'    => admin_url( '/widgets.php' ),
				'docs'      => 'https://www.echoknowledgebase.com/documentation/categories-list-widget/',
				'video'     => '',
			],
			[
				'plugin'    => 'widg',
				'icon'      => 'epkbfa epkbfa-list-alt',
				'title'     => __( 'List of Category Articles', 'echo-knowledge-base' ),
				'desc'      => __( 'Display a list of articles for a given category.', 'echo-knowledge-base' ),
				'config'    => admin_url( '/widgets.php' ),
				'docs'      => 'https://www.echoknowledgebase.com/documentation/category-articles-widget/',
				'video'     => '',
			],
			[
				'plugin'    => 'widg',
				'icon'      => 'epkbfa epkbfa-list-alt',
				'title'     => __( 'KB Tags', 'echo-knowledge-base' ),
				'desc'      => __( 'Display current KB tags ordered alphabetically.', 'echo-knowledge-base' ),
				'config'    => admin_url( '/widgets.php' ),
				'docs'      => 'https://www.echoknowledgebase.com/documentation/tags-list-widget/',
				'video'     => '',
			],
			[
				'plugin'    => 'widg',
				'icon'      => 'epkbfa epkbfa-list-alt',
				'title'     => __( 'List of Tagged Articles', 'echo-knowledge-base' ),
				'desc'      => __( 'Display a list of articles that have a given tag.', 'echo-knowledge-base' ),
				'config'    => admin_url( '/widgets.php' ),
				'docs'      => 'https://www.echoknowledgebase.com/documentation/tagged-articles-widget/',
				'video'     => '',
			],
		];
	}

	/**
     * Get box separator heading html
     *
	 * @param $box
	 *
	 * @return string
	 */
    public static function get_box_heading_html( $box ) {

        ob_start(); ?>

        <h1 class="epkb-kbnh__feature-heading-title"><?php echo esc_html( $box['box-heading'] ); ?></h1> <?php

	    // Plugin is enabled
	    if ( ! empty( $box['active_status'] ) ) {   ?>
            <span class="epkb-kbnh__feature-status epkb-kbnh__feature--installed">
                <span class="epkbfa epkbfa-check"></span>
            </span>    <?php
        // Plugin is not enabled
	    } else if ( $box['plugin'] == 'ep'.'hd' ) { ?>
		    <a class="epkb-kbnh__feature-status epkb-kbnh__feature--disabled epkb-success-btn" href="https://wordpress.org/plugins/help-dialog/" target="_blank"><span><?php esc_html_e( 'Upgrade', 'echo-knowledge-base' ) ?></span></a>   <?php
	    } else {    ?>
		    <a class="epkb-kbnh__feature-status epkb-kbnh__feature--disabled epkb-success-btn" href="<?php echo EPKB_Core_Utilities::get_plugin_sales_page( $box['plugin'] ) ?>" target="_blank"><span><?php echo esc_html__( 'Upgrade', 'echo-knowledge-base' ); ?></span></a> <?php
	    }

	    return ob_get_clean();
    }

	/**
	 * Get boxes config for Shortcodes
	 *
	 * @return array
	 */
	private function get_shortcodes_boxes_config() {

		$kb_id = $this->kb_config['id'];
		$kb_categories = EPKB_Categories_DB::get_top_level_categories( $kb_id, true );

		$kb_categories_ids = [];
		$count = 0;
		foreach( $kb_categories as $kb_category ) {
			$kb_categories_ids[] = $kb_category->term_id;
			if ( ++$count > 1 ) {
				break;
			}
		}

		return [
			[
				'plugin'       => 'core',
				'icon'         => 'epkbfa epkbfa-list-alt',
				'title'        => __( 'FAQs', 'echo-knowledge-base' ),
				'desc'         => __( 'Show articles in FAQ format.', 'echo-knowledge-base' ) .
				                  EPKB_Shortcodes::get_copy_custom_box( 'epkb-faqs', [ 'kb_id' => $kb_id, 'category_ids' => implode( ',', $kb_categories_ids ) ], __( 'Shortcode example:', 'echo-knowledge-base' ) ),
				'docs'         => 'https://www.echoknowledgebase.com/documentation/faqs-shortcode/',
				//'experimental' => 'This feature is being tested and can change how it functions in the meantime.' Leaving this here for future examples to use elsewhere.
			],
			[
				'plugin'       => 'core',
				'icon'         => 'epkbfa epkbfa-list-alt',
				'title'        => __( 'Articles Index Directory', 'echo-knowledge-base' ),
				'desc'         => __( 'Show alphabetical list of articles grouped by letter in a three-column format.', 'echo-knowledge-base' ) .
								  EPKB_Shortcodes::get_copy_box( 'epkb-articles-index-directory', $kb_id, __( 'Shortcode:', 'echo-knowledge-base' ) ),
				'docs'         => 'https://www.echoknowledgebase.com/documentation/shortcode-articles-index-directory/',
			],
            [
	            'plugin'      => 'widg',
	            'box-heading' => __( 'Widgets Add-on', 'echo-knowledge-base' ),
            ],
			[
				'plugin'       => 'widg',
				'icon'         => 'epkbfa epkbfa-list-alt',
				'title'        => __( 'Recent Articles', 'echo-knowledge-base' ),
				'desc'         => __( 'Show either recently created or recently modified KB Articles.', 'echo-knowledge-base' ) .
				                  EPKB_Shortcodes::get_copy_box( 'widg-recent-articles', $kb_id, __( 'Shortcode:', 'echo-knowledge-base' ) ),
				'docs'         => 'https://www.echoknowledgebase.com/documentation/recent-articles-shortcode/',
				'video'        => '',
			],
			[
				'plugin'       => 'widg',
				'icon'         => 'epkbfa epkbfa-list-alt',
				'title'        => __( 'Popular Articles', 'echo-knowledge-base' ),
				'desc'         => __( 'Show a list of the most popular articles based on article views.', 'echo-knowledge-base' ) .
				                  EPKB_Shortcodes::get_copy_box( 'widg-popular-articles', $kb_id, __( 'Shortcode:', 'echo-knowledge-base' ) ),
				'docs'         => 'https://www.echoknowledgebase.com/documentation/popular-articles-widget/',
				'video'        => '',
			],
			[
				'plugin'       => 'widg',
				'icon'         => 'epkbfa epkbfa-list-alt',
				'title'        => __( 'KB Categories', 'echo-knowledge-base' ),
				'desc'         => __( 'List your KB Categories for easy reference, which are typically displayed in sidebars.', 'echo-knowledge-base' ) .
				                  EPKB_Shortcodes::get_copy_box( 'widg-categories-list', $kb_id, __( 'Shortcode:', 'echo-knowledge-base' ) ),
				'docs'         => 'https://www.echoknowledgebase.com/documentation/categories-list-shortcode/',
				'video'        => '',
			],
			[
				'plugin'       => 'widg',
				'icon'         => 'epkbfa epkbfa-list-alt',
				'title'        => __( 'List of Category Articles', 'echo-knowledge-base' ),
				'desc'         => __( 'Display a list of articles for a given category.', 'echo-knowledge-base' ) .
				                  EPKB_Shortcodes::get_copy_box( 'widg-category-articles', $kb_id, __( 'Shortcode:', 'echo-knowledge-base' ) ),
				'docs'         => 'https://www.echoknowledgebase.com/documentation/category-articles-shortcode/',
				'video'        => '',
			],
			[
				'plugin'       => 'widg',
				'icon'         => 'epkbfa epkbfa-list-alt',
				'title'        => __( 'KB Tags', 'echo-knowledge-base' ),
				'desc'         => __( 'Display current KB tags ordered alphabetically.', 'echo-knowledge-base' ) .
				                  EPKB_Shortcodes::get_copy_box( 'widg-tags-list', $kb_id, __( 'Shortcode:', 'echo-knowledge-base' ) ),
				'docs'         => 'https://www.echoknowledgebase.com/documentation/tags-list-shortcode/',
				'video'        => '',
			],
			[
				'plugin'       => 'widg',
				'icon'         => 'epkbfa epkbfa-list-alt',
				'title'        => __( 'List of Tagged Articles', 'echo-knowledge-base' ),
				'desc'         => __( 'Display a list of articles that have a given tag.', 'echo-knowledge-base' ) .
				                  EPKB_Shortcodes::get_copy_box( 'widg-tag-articles', $kb_id, __( 'Shortcode:', 'echo-knowledge-base' ) ),
				'docs'         => 'https://www.echoknowledgebase.com/documentation/tagged-articles-shortcode/',
				'video'        => '',
			],
			[
				'plugin'       => 'widg',
				'icon'         => 'epkbfa epkbfa-list-alt',
				'title'        => __( 'KB Search', 'echo-knowledge-base' ),
				'desc'         => __( 'Add a search box on your Home page, Contact Us page, and others.', 'echo-knowledge-base' ) .
				                  EPKB_Shortcodes::get_copy_box( 'widg-search-articles', $kb_id, __( 'Shortcode:', 'echo-knowledge-base' ) ),
				'docs'         => 'https://www.echoknowledgebase.com/documentation/search-shortcode/',
				'video'        => '',
			],
		];
	}

	/**
	 * Get configuration array for views of KB Configuration page before the first KB setup
	 *
	 * @return array[]
	 */
	private static function get_run_setup_first_views_config() {

		return array(

			// VIEW: SETUP WIZARD
			array(

				// Shared
				'minimum_required_capability' => EPKB_Admin_UI_Access::get_context_required_capability( ['admin_eckb_access_frontend_editor_write'] ),
				'list_key' => 'setup-wizard',

				// Top Panel Item
				'label_text' => __( 'Setup Wizard', 'echo-knowledge-base' ),
				'icon_class' => 'epkbfa epkbfa-cogs',

				'boxes_list' => array(

					// Box: Setup Wizard Message
					array(
						'html' => self::get_setup_wizard_message(),
						'class' => 'epkb-admin__notice'
					),
				),
			),
		);
	}

	/**
	 * Return message to complete Setup Wizard
	 *
	 * @return false|string
	 */
	private static function get_setup_wizard_message() {

		ob_start();     ?>

		<div class="epkb-admin__setup-wizard-warning">     <?php

			EPKB_HTML_Forms::notification_box_popup( array(
				'type'  => 'success',
				'title' => __( 'Thank you for installing our Knowledge Base.', 'echo-knowledge-base' ) . ' ' . __( 'Get started by running our Setup Wizard.', 'echo-knowledge-base' ),
				'desc'  => '<span>' . EPKB_Core_Utilities::get_kb_admin_page_link( 'page=epkb-kb-configuration&setup-wizard-on', __( 'Start the Setup Wizard', 'echo-knowledge-base' ), false,'epkb-success-btn' ) . '</span>',
			) );   ?>

		</div>      <?php

		return ob_get_clean();
	}

	/**
	 * Get configuration array for Errors view of KB Configuration page
	 *
	 * @return array
	 */
	private function get_errors_view_config() {

		$error_boxes = array();

		// KB missing shortcode error message
		if ( empty( $this->kb_main_pages ) ) {
			$error_boxes[] = array(
				'icon_class' => 'epkbfa-exclamation-circle',
				'title' => __( 'Missing shortcode', 'echo-knowledge-base' ),
				'html' => $this->display_no_shortcode_warning( $this->kb_config, true ),
				'class' => 'epkb-admin__warning-box',
			);
		}

		// License issue messages from add-ons
		$add_on_messages = apply_filters( 'epkb_add_on_license_message', array() );
		if ( ( ! empty( $add_on_messages ) && is_array( $add_on_messages ) ) || did_action( 'kb_overview_add_on_errors' ) ) {

			$licenses_tab_url = admin_url( 'edit.php?post_type=' . EPKB_KB_Handler::get_post_type( EPKB_KB_Handler::get_current_kb_id() ) . '&page=epkb-add-ons#licenses' );
			$licenses_tab_button = '<a href="' . esc_url( $licenses_tab_url ) . '" class="epkb-primary-btn"> ' . esc_html__( 'Fix the Issue', 'echo-knowledge-base' ) . '</a>';

			foreach ( $add_on_messages as $add_on_name => $add_on_message ) {

                // Add 'See Your License' button html
				$add_on_message .= $licenses_tab_button;

				$add_on_name = str_replace( array( '2', '3', '4' ), '', $add_on_name );

				$error_boxes[] = array(
					'icon_class' => 'epkbfa-exclamation-circle',
					'class' => 'epkb-admin__boxes-list__box__addons-license',
					'title' => $add_on_name . ': ' . __('License issue', 'echo-knowledge-base'),
					'description' => '',
					'html' => $add_on_message,
				);
			}
		}

		return empty( $error_boxes )
			? array()
			: array(

				// Shared
				'active' => true,
				'list_key' => 'errors',

				// Top Panel Item
				'label_text' => __( 'Errors', 'echo-knowledge-base' ),
				'icon_class' => 'page-icon overview-icon epkbfa epkbfa-exclamation-triangle',

				// Boxes List
				'boxes_list' => $error_boxes,
			);
	}

	/**
	 * Get configuration array for archived KBs
	 *
	 * @return array
	 */
	private static function get_archived_kbs_views_config() {

		$views_config = array(

			// View: Archived KBs
			array(

				// Shared
				'active' => true,
				'list_key' => 'archived-kbs',

				// Top Panel Item
				'label_text' => __( 'Archived KBs', 'echo-knowledge-base' ),
				'icon_class' => 'epkbfa epkbfa-cubes',

				// Boxes List
				'boxes_list' => array(

				),
			),
		);

		$archived_kbs = EPKB_Core_Utilities::get_archived_kbs();
		foreach ( $archived_kbs as $one_kb_config ) {

			$views_config[0]['boxes_list'][] = array(
				'class' => '',
				'title' => $one_kb_config['kb_name'],
				'description' => '',
				'html' => self::get_archived_kb_box_html( $one_kb_config ),
			);
		}

		return $views_config;
	}

	/**
	 * Get HTML for one archived KB box
	 *
	 * @param $kb_config
	 *
	 * @return false|string
	 */
	private static function get_archived_kb_box_html( $kb_config ) {

		ob_start();

		if ( ! EPKB_Utilities::is_multiple_kbs_enabled() ) {    ?>
			<div><?php esc_html_e( 'To manage non-default KBs you need Multiple KB add-on to be activated.', 'echo-knowledge-base' ); ?></div><?php
		}

		do_action( 'eckb_admin_config_page_kb_status', $kb_config );

		return ob_get_clean();
	}

	/**
	 * Display warning about missing shortcode
	 *
	 * @param $kb_config
	 * @param bool $return_html
	 *
	 * @return false|string|void
	 */
	private function display_no_shortcode_warning( $kb_config, $return_html=false ) {

        $notification = EPKB_HTML_Forms::notification_box_middle( array(
            'type'  => 'error',
            'title' => 'We did not detect any page with KB shortcode for your knowledge base '.$kb_config['kb_name'].'. You can do the following:',
            'desc'  => '<ul>
                            <li>If you have this page, please re-save it and come back</li>
                            <li>Create or update a page and add KB shortcode '.$kb_config['id'].' to that page. Save the page and then come back here.</li>
                            <li>Run Setup Wizard to create a new KB Main Page <a href="'.esc_url( admin_url( '/edit.php?post_type=' . EPKB_KB_Handler::get_post_type( $kb_config['id'] ) .
                                  '&page=epkb-kb-configuration&setup-wizard-on' ) ).'" target="_blank">Run Setup Wizard</a></li>
                        </ul>'
        ), $return_html  );

        if ( $return_html ) {
            return $notification;
        } else {
            echo $notification;
        }
	}

	/**
	 * Message that will show the link to change article version and warning message
	 */
	private function get_article_version_error_box() {
		ob_start(); ?>

		<div class="epkb-admin__section-wrap epkb-admin__deprecated-wizard-warning"><?php
			$editor_url = add_query_arg( [ 'action' => 'epkb_update_article_v2', '_wpnonce_epkb_ajax_action' => wp_create_nonce( "_wpnonce_epkb_ajax_action" ), 'emkb_kb_id' => $this->kb_config['id'] ] );

			EPKB_HTML_Forms::notification_box_popup( array(
				'type'  => 'error',
				'title' => esc_html__( 'Upgrade to Articles v2 Required.', 'echo-knowledge-base' ),
				'desc'  => '<span>' . esc_html__( 'You have an old version of articles format. Please run the upgrade to continue. After the upgrade, check your articles and make minor adjustments if required.', 'echo-knowledge-base' ) . ' ' .
				 '<a href="' . esc_url( $editor_url ) . '" class="epkb-primary-btn">' . esc_html__( 'UPGRADE NOW', 'echo-knowledge-base' ) . '</a></span>'
						   . '<span>' . ' ' . esc_html__( 'If you have questions or concerns, please talk to us and we will gladly help you with this upgrade.', 'echo-knowledge-base' ) . ' ' . EPKB_Utilities::contact_us_for_support() . '</span>'
			) ); ?>
		</div> <?php

		return ob_get_clean();
	}

	/**
	 * Generic admin page to display message on configuration error
	 */
	private static function display_config_error_page() {    ?>
		<div id="ekb-admin-page-wrap" class="ekb-admin-page-wrap--config-error">    <?php
			EPKB_HTML_Forms::notification_box_middle( [ 'type' => 'error', 'title' => __( 'Cannot load configuration.', 'echo-knowledge-base' ), 'desc' =>  EPKB_Utilities::contact_us_for_support() ] );  ?>
		</div>  <?php
	}
}