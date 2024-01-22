<?php  if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Show setup wizard when plugin is installed
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class EPKB_KB_Wizard_Setup {

	private $kb_config;
	private $is_setup_run_first_time;
	private $elay_enabled;
	private $is_old_elay;   // FUTURE TODO: remove in December 2024

	function __construct( $kb_config=array() ) {
		add_action( 'wp_ajax_epkb_apply_setup_wizard_changes',  array( $this, 'apply_setup_wizard_changes' ) );
		add_action( 'wp_ajax_nopriv_epkb_apply_setup_wizard_changes', array( 'EPKB_Utilities', 'user_not_logged_in' ) );

		add_action( 'wp_ajax_epkb_report_admin_error',  array( 'EPKB_Controller', 'handle_report_admin_error' ) );
		add_action( 'wp_ajax_nopriv_epkb_report_admin_error', array( 'EPKB_Utilities', 'user_not_logged_in' ) );

		$this->kb_config = $kb_config;
		$this->is_setup_run_first_time = EPKB_Core_Utilities::is_run_setup_wizard_first_time() || EPKB_Utilities::post( 'emkb_admin_notice' ) == 'kb_add_success';

		$this->elay_enabled = EPKB_Utilities::is_elegant_layouts_enabled();
		$this->is_old_elay = $this->elay_enabled && class_exists( 'Echo_Elegant_Layouts' ) && version_compare( Echo_Elegant_Layouts::$version, '2.14.1', '<=' );
	}

	/**
	 * Show KB Setup page
	 *
	 * @return boolean
	 */
	public function display_kb_setup_wizard() {

		$is_modular_main_page = $this->kb_config['modular_main_page_toggle'] == 'on';

		// Step: URL
		$setup_steps_config[] = [
			'label'     => __( 'URL', 'echo-knowledge-base' ),
			'header'    => $this->wizard_step_header( array(
				'title_html'            => __( 'Setup Your Knowledge Base', 'echo-knowledge-base' ),
				'info_title'            => __( 'Set your Knowledge Base nickname, create a slug, and add it to the menu.', 'echo-knowledge-base' ),
			) ),
			'content'   => $this->wizard_step_title_url_content(),
		];

		// Step: Modules
		if ( $is_modular_main_page ) {
			$setup_steps_config[] = [
				'label'     => __( 'Features', 'echo-knowledge-base' ),
				'sub_label' => __( 'Main Page', 'echo-knowledge-base' ),
				'header'    => $this->wizard_step_header( array(
					'title_html'        => esc_html__( 'Customize KB Main Page', 'echo-knowledge-base' ),
					'info_title'        => sprintf( esc_html__( 'The page is divided into rows. Simply select which features, called %s, you want to display in each row.', 'echo-knowledge-base' ),
												'<span class="epkb-setup-wizard-step__topic">' . esc_html__( 'Modules', 'echo-knowledge-base' ) . '</span>' ),
					'info_description'  => __( 'Feel free to experiment with different arrangements. You can make additional changes at any time, either on this page or in the Knowledge Base Settings.', 'echo-knowledge-base' ),
				) ),
				'content'   => $this->wizard_step_modules_content(),
			];
		}

		// Step: Layout
		$setup_steps_config[] = [
			'label'     => __( 'Layout', 'echo-knowledge-base' ),
			'sub_label' => __( 'Main Page', 'echo-knowledge-base' ),
			'header'    => $is_modular_main_page
				? $this->wizard_step_header( array(
					'title_html'        => esc_html__( 'Choose Layout Matching Your Needs', 'echo-knowledge-base' ),
					'info_title'        => __( 'Each layout offers a different way to show categories and articles. Layout features are explained below.', 'echo-knowledge-base' ),
					'info_description'  => __( 'Don\'t hesitate to try out various layouts. You can change your KB Layout at any time.', 'echo-knowledge-base' ),
					'info_html'         => $this->is_old_elay
						? EPKB_HTML_Forms::notification_box_middle( array(
							'type' => 'error',
							'desc' => '<p>' . esc_html__( 'Modular Main Page feature is supported for Sidebar and Grid layouts in the "KB - Elegant Layouts" add-on version higher than 2.14.1.', 'echo-knowledge-base' ) .
								'<br>' . sprintf( esc_html__( 'Please %supgrade%s the add-on to use Modular Main Page feature for the Sidebar and Grid layouts.', 'echo-knowledge-base' ), '<a href="https://www.echoknowledgebase.com/wordpress-plugin/elegant-layouts/" target="_blank">', '</a>' ) . '</p>',
						), true )
						: '',
				) )
				: $this->wizard_step_header( array(
					'title_html'        => __( 'Setup Your Knowledge Base', 'echo-knowledge-base' ),
					'info_title'        => __( 'Choose an initial Knowledge Base design. You can easily adjust colors and other elements later.', 'echo-knowledge-base' ),
				) ),
			'content'   => $is_modular_main_page ? $this->wizard_step_modular_layout_content() : $this->wizard_step_layout_content(),
		];


		// Step: Presets
		if ( $is_modular_main_page ) {
			$setup_steps_config[] = [
				'label'     => __( 'Presets', 'echo-knowledge-base' ),
				'sub_label' => __( 'Main Page', 'echo-knowledge-base' ),
				'header'    => $this->wizard_step_header( array(
					'title_html'        => __( 'Select a Preset that best matches your requirements (Optional Step)', 'echo-knowledge-base' ),
					'info_title'        => '', // __( 'Select a Preset that best matches your site theme or requirements.', 'echo-knowledge-base' ),
					'info_description'  => __( 'You can easily fine-tune colors and other elements later on the Settings page.', 'echo-knowledge-base' ),
					'content_show_option'  => array(
						'ignore_layouts'=> 'Classic, Drill-Down',
						'text'          => __( 'Do you want to change the style and colors of the KB Main Page using one of our presets?', 'echo-knowledge-base' ),
					)
				) ),
				'content'   => $this->wizard_step_presets_content(),
			];
		}

		// Step: Article Page
		$setup_steps_config[] = [
			'label'     => __( 'Article Page', 'echo-knowledge-base' ),
			'header'    => $this->wizard_step_header( array(
				'title_html'        => __( 'Setup Your Article Page', 'echo-knowledge-base' ),
				'info_title'        => __( 'Article pages can have navigation links in the left sidebar or in the right sidebar.', 'echo-knowledge-base' ),
			) ),
			'content'   => $is_modular_main_page ? $this->wizard_step_modular_navigation_content() : $this->wizard_step_navigation_content(),
		];  ?>

		<div id="ekb-admin-page-wrap" class="ekb-admin-page-wrap epkb-wizard-container">
			<div class="<?php echo $is_modular_main_page ? 'epkb-config-setup-wizard-modular' : ''; ?>" id="epkb-config-wizard-content">

				<!------- Wizard Steps Bar ------------>
				<div class="epkb-setup-wizard-steps-bar">   <?php
					foreach ( $setup_steps_config as $step_index => $step_config ) {   ?>
						<div data-step="<?php echo esc_attr( $step_index + 1 ); ?>" class="epkb-setup-wizard-step-tab epkb-setup-wizard-step-tab--<?php echo esc_attr( $step_index + 1 ); echo $step_index == 0 ? ' ' . 'epkb-setup-wizard-step-tab--active' : ''; ?>">
							<div class="epkbfa epkbfa-check-circle epkb-setup-wizard-step-tab__icon"></div>
							<div class="epkb-setup-wizard-step-tab__number"><?php echo esc_html( $step_index + 1 ); ?></div>
							<div class="epkb-setup-wizard-step-tab__label"><?php
								if ( ! empty( $step_config['sub_label'] ) ) {   ?>
									<span class="epkb-setup-wizard-step-tab__sub-label"><?php echo esc_html( $step_config['sub_label'] ); ?></span><?php
								}
								echo esc_html( $step_config['label'] ); ?>
							</div>
						</div>  <?php
						if ( ( $step_index + 1 ) < count( $setup_steps_config ) ) {    ?>
							<div class="epkb-setup-wizard-step-tab-divider">
								<i class="epkbfa epkbfa-chevron-right"></i>
								<i class="epkbfa epkbfa-chevron-right"></i>
							</div>  <?php
						}
					}   ?>
				</div>

				<div class="epkb-config-wizard-inner">

					<!------- Wizard Header ------------>
					<div class="epkb-wizard-header">    <?php
						foreach ( $setup_steps_config as $step_index => $step_config ) {    ?>
							<div class="epkb-wc-step-header epkb-wc-step-header--<?php echo esc_attr( $step_index + 1 ); echo $step_index == 0 ? ' ' . 'epkb-wc-step-header--active' : ''; ?>"> <?php
								echo $step_config['header'];   ?>
							</div>  <?php
						}   ?>
					</div>

					<!------- Wizard Content ---------->
					<div class="epkb-wizard-content">   <?php
						foreach ( $setup_steps_config as $step_index => $step_config ) {
							echo $step_config['content'];
						}   ?>
					</div>

					<!------- Wizard Footer ---------->
					<div class="epkb-wizard-footer">

						<!----First Step Buttons---->
						<div class="epkb-wizard-button-container epkb-wsb-step-1-panel-button epkb-wc-step-panel-button epkb-wc-step-panel-button--active">
							<div class="epkb-wizard-button-container__inner">
								<button value="2" class="epkb-wizard-button epkb-setup-wizard-button-next">
									<span class="epkb-setup-wizard-button-next__text"><?php esc_html_e( 'Next Step', 'echo-knowledge-base' ); ?>&nbsp;&gt;</span>
								</button>
							</div>
						</div>

						<!----Middle Steps Buttons---->
						<div class="epkb-wizard-button-container epkb-wsb-step-2-panel-button epkb-wc-step-panel-button">
							<div class="epkb-wizard-button-container__inner">
								<button value="1" class="epkb-wizard-button epkb-setup-wizard-button-prev">
									<span class="epkb-setup-wizard-button-prev__text">&lt;&nbsp;<?php esc_html_e( 'Previous Step', 'echo-knowledge-base' ); ?></span>
								</button>
								<button value="3" class="epkb-wizard-button epkb-setup-wizard-button-next">
									<span class="epkb-setup-wizard-button-next__text"><?php esc_html_e( 'Next Step', 'echo-knowledge-base' ); ?>&nbsp;&gt;</span>
								</button>
							</div>
						</div>

						<!----Last Step Buttons---->
						<div class="epkb-wizard-button-container epkb-wsb-step-3-panel-button epkb-wc-step-panel-button">
							<div class="epkb-wizard-button-container__inner">
								<button value="<?php echo esc_attr( count( $setup_steps_config ) - 1 ); ?>" class="epkb-wizard-button epkb-setup-wizard-button-prev">
									<span class="epkb-setup-wizard-button-prev__text">&lt;&nbsp;<?php esc_html_e( 'Previous Step', 'echo-knowledge-base' ); ?></span>
								</button>
								<button value="apply" class="epkb-wizard-button epkb-setup-wizard-button-apply" data-wizard-type="setup"><?php esc_html_e( 'Finish Set Up', 'echo-knowledge-base' ); ?></button>

								<input type="hidden" id="_wpnonce_epkb_ajax_action" name="_wpnonce_epkb_ajax_action" value="<?php echo wp_create_nonce( "_wpnonce_epkb_ajax_action" ); ?>">
							</div>
						</div>

					</div>

					<input type="hidden" id="epkb_wizard_kb_id" name="epkb_wizard_kb_id" value="<?php echo $this->kb_config['id']; ?>"/>

					<div class="eckb-bottom-notice-message"></div>

				</div>
			</div>

		</div>		<?php

		// Report error form
		EPKB_HTML_Admin::display_report_admin_error_form();

		// Success message
		EPKB_HTML_Forms::dialog_confirm_action( [
			'id'           => 'epkb-wizard-success-message',
			'title'        => __( 'Success', 'echo-knowledge-base' ),
			'body'         => __( 'Wizard Completed Successfully.', 'echo-knowledge-base' ),
			'accept_label' => __( 'Ok', 'echo-knowledge-base' ),
			'accept_type'  => 'success'
		] );

		return true;
	}

	/**
	 * Setup Wizard: Step 1 - Title & URL
	 *
	 * @return false|string
	 */
	private function wizard_step_title_url_content() {

		ob_start();     ?>

		<div id="epkb-wsb-step-1-panel" class="epkb-wc-step-panel eckb-wizard-step-1 epkb-wc-step-panel--active epkb-wizard-theme-step-1">  <?php

			// KB Name
		    EPKB_HTML_Elements::text(
				array(
					'label'             => __('Knowledge Base Nickname', 'echo-knowledge-base'),
					'placeholder'       => __('Knowledge Base', 'echo-knowledge-base'),
					'main_tag'          => 'div',
					'input_group_class' => 'epkb-wizard-row-form-input epkb-wizard-name',
					'value'             => $this->kb_config['kb_name']
				)
			);      ?>
			<div class="epkb-wizard-row-form-input">
				<div class="epkb-wizard-col2">
					<p class="epkb-wizard-input-desc"><?php
						echo __( 'Give your Knowledge Base a name. The name will show when we refer to it or when you see a list of post types.', 'echo-knowledge-base' ) .
						     '</br>' . __('Examples: Knowledge Base, Help, Support', 'echo-knowledge-base');							?>
					</p>
				</div>
			</div>			<?php

			// KB Slug - if Setup Wizard is run first time or no KB Main Pages exist, then show input field
			$main_pages = EPKB_KB_Handler::get_kb_main_pages( $this->kb_config );
			if ( $this->is_setup_run_first_time || empty( $main_pages ) ) {
				EPKB_HTML_Elements::text(
					array(
						'label'             => __( 'Knowledge Base Slug', 'echo-knowledge-base' ),
						'placeholder'       => 'knowledge-base',
						'main_tag'          => 'div',
						'readonly'          => ! EPKB_Admin_UI_Access::is_user_access_to_context_allowed( 'admin_eckb_access_frontend_editor_write' ),
						'input_group_class' => 'epkb-wizard-row-form-input epkb-wizard-slug',
						'value'             => $this->kb_config['kb_articles_common_path'],
					)
				);      ?>
				<div class="epkb-wizard-row-form-input">
					<div class="epkb-wizard-col2">
						<p id="epkb-wizard-slug-error"><?php esc_html_e( 'The slug should not contain full KB URL.', 'echo-knowledge-base' ); ?></p>
						<p class="epkb-wizard-input-desc"><?php esc_html_e( 'This KB slug is part of your full knowledge base URL:', 'echo-knowledge-base' ); ?></p>
						<p class="epkb-wizard-input-desc"><span><?php echo site_url(); ?></span> / <span id="epkb-wizard-slug-target"><?php echo $this->kb_config['kb_articles_common_path']; ?></span></p>
					</div>
				</div>				<?php

			// KB Slug - if user re-run Setup Wizard, then only show slug with Link to change it (KB URL)
			} else {
				$main_page_id = EPKB_KB_Handler::get_first_kb_main_page_id( $this->kb_config );
				$main_page_slug = EPKB_Core_Utilities::get_main_page_slug( $main_page_id );
				$main_page_url = EPKB_KB_Handler::get_first_kb_main_page_url( $this->kb_config );
				EPKB_HTML_Elements::text(
					array(
						'label'             => __( 'Knowledge Base Slug', 'echo-knowledge-base' ),
						'placeholder'       => 'knowledge-base',
						'main_tag'          => 'div',
						'readonly'          => ! ( EPKB_Utilities::get_wp_option( 'epkb_not_completed_setup_wizard_' . $this->kb_config['id'], false ) && EPKB_Admin_UI_Access::is_user_access_to_context_allowed( 'admin_eckb_access_frontend_editor_write' ) ),
						'input_group_class' => 'epkb-wizard-row-form-input epkb-wizard-slug',
						'value'             => $main_page_slug,
					)
				);      ?>
				<div class="epkb-wizard-row-form-input">
					<div class="epkb-wizard-col2">
						<p class="epkb-wizard-input-desc"><?php esc_html_e( 'This is KB slug that is part of your full knowledge base URL:', 'echo-knowledge-base' ); ?></p>
						<a class="epkb-wizard-input-desc" href="<?php echo esc_url( $main_page_url ); ?>" target="_blank"><?php echo esc_url( $main_page_url ); ?></a><?php
						if ( current_user_can( EPKB_Admin_UI_Access::get_admin_capability() ) ) {   ?>
							<p class="epkb-wizard-input-desc">
								<a href="https://www.echoknowledgebase.com/documentation/changing-permalinks-urls-and-slugs/" target="_blank"><?php esc_html_e( 'Need to change KB URL?', 'echo-knowledge-base' ); ?>
								<span class="ep_font_icon_external_link"></span>
								</a>
							</p>    <?php
						}   ?>
					</div>
				</div>				<?php
			}

			// if we have menus and menus without link
			$menus = $this->kb_menus_without_item();
			if ( is_array($menus) && ! empty($menus) ) {      ?>

				<div class="input_group epkb-wizard-row-form-input epkb-wizard-menus" >
					<label><?php esc_html_e( 'Add KB to Website Menu', 'echo-knowledge-base' ); ?></label>
					<ul>	<?php
						foreach ($menus as $menu_id => $menu_title) {
							EPKB_HTML_Elements::checkbox( array(
								'name'              => 'epkb_menu_' . $menu_id,
								'label'             => $menu_title,
								'input_group_class' => 'epkb-menu-checkbox',
								'value'             => 'off'
							) );
						}           ?>
					</ul>
				</div>
				<div class="epkb-wizard-row-form-input">
				<div class="epkb-wizard-col2">
					<p class="epkb-wizard-input-desc"><?php esc_html_e( 'Choose the website menu(s) where users will access the Knowledge Base. You can change it at any time in WordPress -> Appearance -> Menus.', 'echo-knowledge-base' ); ?></p>
				</div>
				</div><?php

			}       ?>
		</div>	<?php

		return ob_get_clean();
	}

	/**
	 * Setup Wizard: Step 2 - Choose Design
	 *
	 * @return false|string
	 */
	private function wizard_step_layout_content() {

		// group themes by layout names
		$theme_description = EPKB_KB_Wizard_Themes::get_themes_description();

		// pre-define sequence of layouts shown in Setup Wizard presets
		$preset_options = array(
			'Basic'         => [],
			'Tabs'          => [],
			'Categories'    => [],
		);

		// group themes by layout names
		foreach ( EPKB_KB_Wizard_Themes::get_all_presets( $this->kb_config ) as $theme_slug => $theme_data ) {
			$preset_options[$theme_data['kb_main_page_layout']][$theme_slug] = $theme_data['kb_name'];
		}

		ob_start(); ?>

		<div id="epkb-wsb-step-2-panel" class="epkb-setup-wizard-theme epkb-wc-step-panel eckb-wizard-step-2">
			<div class="epkb-setup-wizard-theme-preview">

				<!-- THEME BUTTONS -->
				<div class="epkb-wizard-theme-tab-container">
					<input type="hidden" id="_wpnonce_epkb_ajax_action" name="_wpnonce_epkb_ajax_action" value="<?php echo wp_create_nonce( "_wpnonce_epkb_ajax_action" ); ?>"/>		<?php

					// add categories  	?>
					<div class="epkb-setup-wizard-group__container"> <?php

					// Pre-select first theme if Setup Wizard is running first time or KB > 1
					$pre_select_theme = false;
					if ( $this->is_setup_run_first_time ) {
						$pre_select_theme = true;
					} else { ?>
						<div class="epkb-setup-wt-tc__themes-group__list config-input-group">

                            <div class="epkb-setup-current">
                                <div class="epkb-setup-option-container epkb-setup-option-container--description">
                                    <h4><?php esc_html_e( 'Your current settings', 'echo-knowledge-base'); ?></h4>
                                    <p><?php esc_html_e( 'You can either keep the current colors and design or choose a different one below.', 'echo-knowledge-base'); ?></p>
                                </div>
                                <div id="epkb-setup-wt-theme-current-panel" class="epkb-setup-option-container epkb-setup-option-container--active">
                                    <div class="epkb-setup-option__inner">
                                        <div class="epkb-setup-option__selection">
                                            <div class="epkb-setup-option__option-container">
                                                <label class="epkb-setup-option__option__label">
                                                    <input type="radio" name="epkp-theme" value="current" checked>
                                                </label>
                                            </div>
                                            <div class="epkb-setup-option__featured-img-container">
                                                <img alt="" class="epkb-setup-option__featured-img" src="<?php echo EPKB_KB_Wizard_Themes::$theme_images['current_design']; ?>" title="<?php esc_html_e( 'Current Colors', 'echo-knowledge-base' ); ?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

						</div><?php
					}

					foreach ( $preset_options as $title => $group ) {

						$layout_title = self::get_layout_title( $title ); ?>

						<div class="epkb-setup-wizard-group__container-inner">
							<div class="epkb-setup-wt-tc__themes-group__header__title"><?php echo esc_html( $layout_title ); ?></div>
						    <div class="epkb-setup-wt-tc__themes-group__header__desc"><?php echo esc_html( $theme_description[$title] ); ?></div>

							<div class="epkb-setup-wt-tc__themes-group__list config-input-group">       <?php

								foreach ( $group as $template_id => $template_name ) {      ?>
									<div id="epkb-setup-wt-theme-<?php echo $template_id; ?>-panel" class="epkb-setup-option-container">
										<div class="epkb-setup-option__inner">
											<div class="epkb-setup-option__selection">
												<div class="epkb-setup-option__option-container">
													<label class="epkb-setup-option__option__label">
														<input type="radio" name="epkp-theme" value="<?php echo $template_id; ?>"<?php echo $pre_select_theme ? ' checked' : ''; ?>>
														<span><?php echo $template_name; ?></span>
													</label>
												</div>
												<div class="epkb-setup-option__featured-img-container">
													<img alt="" class="epkb-setup-option__featured-img" src="<?php echo EPKB_KB_Wizard_Themes::$theme_images[$template_id]; ?>" title="<?php echo $template_name; ?>" />
												</div>
											</div>
										</div>
									</div>		<?php

									$pre_select_theme = false;
								}       ?>

							</div>

						</div><?php
					} ?>
					</div>

				</div>

			</div>
		</div>	<?php

		return ob_get_clean();
	}

	/**
	 * Setup Wizard: Step 3 - Choose navigation on left or right sidebar
	 *
	 * @return false|string
	 */
	private function wizard_step_navigation_content() {

		$groups = EPKB_KB_Wizard_Themes::get_sidebar_groups();

		$selected_option_id = 1;
		if ( ! $this->is_setup_run_first_time ) {
			$groups = array_merge( [
				[
					'title' => __( 'Your current settings', 'echo-knowledge-base' ),
                    'class' => 'epkb-setup-current-article',
					'description' => __( 'You can either keep the current sidebars setup or choose a different one below.', 'echo-knowledge-base' ),
					'learn_more_url' => '',
					'options' => [
						0 => __( 'Current Navigation', 'echo-knowledge-base' ),
					]
				],
			], $groups );

			$selected_option_id = 0;
		}

		ob_start(); ?>

		<div id="epkb-wsb-step-3-panel" class="epkb-setup-wizard-sidebar epkb-wc-step-panel eckb-wizard-step-3">
			<div class="epkb-setup-wizard-theme-preview">
				<div class="epkb-wizard-theme-tab-container">
					<div class="epkb-setup-wizard-group__container"><?php
						foreach ( $groups as $group ) { ?>
							<div class="epkb-setup-wizard-group__container-inner <?php echo esc_html( $group['class'] ); ?>">                                <?php

								if ( $group['class'] == 'epkb-setup-current-article') { ?>
									<div class="epkb-setup-current-article__text"> <?php
								}       ?>

								    <div class="epkb-setup-wt-tc__themes-group__header__title"><?php echo esc_html( $group['title'] ); ?></div>
								    <div class="epkb-setup-wt-tc__themes-group__header__desc"><?php
									echo esc_html( $group['description'] );
									if ( ! empty( $group['learn_more_url'] ) ) { ?>
										<a href="<?php echo esc_url( $group['learn_more_url'] ); ?>" target="_blank"><?php esc_html_e( 'See navigation demo page', 'echo-knowledge-base' ); ?></a><?php
									} ?>
									</div>                                <?php

								if ( $group['class'] == 'epkb-setup-current-article') { ?>
									</div>
									<div class="epkb-setup-current-article__image"> <?php
								}   ?>

								    <div class="epkb-setup-wt-tc__themes-group__list config-input-group">       <?php

									foreach ( $group['options'] as $id => $option_title ) {
										$image_id = $id ? $id : self::get_current_sidebar_selection( $this->kb_config );
										$image_url = Echo_Knowledge_Base::$plugin_url . 'img/' . EPKB_KB_Wizard_Themes::$sidebar_images[ $image_id ]; ?>
										<div id="epkb-setup-wt-sidebar-<?php echo $id; ?>-panel"
											 class="epkb-setup-option-container <?php echo $selected_option_id == $id ? 'epkb-setup-option-container--active' : ''; ?>">
											<div class="epkb-setup-option__inner">
												<div class="epkb-setup-option__selection">
													<div class="epkb-setup-option__option-container">
														<label class="epkb-setup-option__option__label">
															<input type="radio" name="epkb-sidebar" value="<?php echo $id; ?>" <?php checked( $selected_option_id, $id ); ?>>
															<span><?php echo $option_title; ?></span>
														</label>
													</div>
													<div class="epkb-setup-option__featured-img-container">
														<img alt="" class="epkb-setup-option__featured-img"
															 src="<?php echo $image_url; ?>"
															 title="<?php echo $option_title; ?>"/>
													</div>
												</div>
											</div>
										</div><?php
									} ?>

									</div>                                <?php

								if ( $group['class'] == 'epkb-setup-current-article') { ?>
									</div> <?php
								}       ?>

							</div><?php
						} ?>
					</div>
				</div>
			</div>
		</div>    <?php

		return ob_get_clean();
	}

	/**
	 * Find menu items with a link to KB
	 *
	 * @return array|bool - true on ERROR,
	 *                      false if found a menu with KB link
	 *                      empty array if no menu exists
	 *                      non-empty array for existing menus.
	 */
	private function kb_menus_without_item() {

		$menus = wp_get_nav_menus();
		if ( empty($menus) || ! is_array($menus) ) {
			return array();
		}

		$kb_main_pages_info = EPKB_KB_Handler::get_kb_main_pages( $this->kb_config );

		// check if we have any menu item with KB page
		$menu_without_kb_links = array();
		foreach ( $menus as $menu ) {

			// does menu have any menu items?
			$menu_items = wp_get_nav_menu_items($menu);
			if ( empty($menu_items) && ! is_array($menu_items) )  {
				continue;
			}

			foreach ( $menu_items as $item ) {

				// true if we already have KB link in menu
				if ( $item->object == 'page' && isset( $kb_main_pages_info[$item->object_id] ) ) {
					return false; // use this string to show menus without KB link only if ALL menus have no KB links
				}
			}

			$menu_without_kb_links[$menu->term_id] = $menu->name;
		}

		return $menu_without_kb_links;
	}


	/***************************************************************************
	 *
	 * Setup Wizards Functions
	 *
	 ***************************************************************************/

	/**
	 * User submit theme to use for the new Knowledge Base
	 */
	public function apply_setup_wizard_changes() {

		// wp_die if nonce invalid or user does not have correct permission
		EPKB_Utilities::ajax_verify_nonce_and_admin_permission_or_error_die( 'admin_eckb_access_frontend_editor_write' );

		// get current KB ID
		$wizard_kb_id = EPKB_Utilities::post( 'epkb_wizard_kb_id' );
		if ( empty( $wizard_kb_id ) || ! EPKB_Utilities::is_positive_int( $wizard_kb_id ) ) {
			EPKB_Utilities::ajax_show_error_die( EPKB_Utilities::report_generic_error( 159 ) );
		}

		// get selected Theme Name - support for old Setup Wizard (versions before KB 11.30.0); new Setup Wizard uses $categories_articles_preset_name
		$theme_name = EPKB_Utilities::post( 'theme_name' );
		if ( empty( $theme_name ) ) {
			EPKB_Utilities::ajax_show_error_die( EPKB_Utilities::report_generic_error( 160 ) );
		}

		// get Layout Name - is always set in new Setup Wizard (introduced in KB 11.30.0); is empty in old Setup Wizard
		$layout_name = EPKB_Utilities::post( 'layout_name' );

		// create demo KB only for the first time and save it
		if ( $this->is_setup_run_first_time ) {
			$new_kb_config = EPKB_KB_Handler::add_new_knowledge_base( EPKB_KB_Config_DB::DEFAULT_KB_ID, '', '', $layout_name );
			if ( is_wp_error( $new_kb_config ) ) {
				EPKB_Utilities::ajax_show_error_die( EPKB_Utilities::report_generic_error( 158, $new_kb_config ) );
			}
		}

		// get current KB configuration (or new one if first time setup)
		$orig_config = epkb_get_instance()->kb_config_obj->get_kb_config( $wizard_kb_id, true );
		if ( is_wp_error( $orig_config ) ) {
			EPKB_Utilities::ajax_show_error_die( EPKB_Utilities::report_generic_error( 8, $orig_config ) );
		}

		// get current Add-ons configuration
		$orig_config = apply_filters( 'epkb_all_wizards_get_current_config', $orig_config, $wizard_kb_id );
		if ( empty( $orig_config ) || ! is_array( $orig_config ) || count( $orig_config ) < 3 ) {
			EPKB_Utilities::ajax_show_error_die( EPKB_Utilities::report_generic_error( 500, EPKB_Utilities::get_variable_string( $orig_config ) ) );
		}

		// if user selected a theme then apply it - for new Setup Wizard is always 'current' (see usage of get_themed_kb_config() with $categories_articles_preset_name below)
		$new_config = $theme_name == 'current' ? $orig_config : $this->get_themed_kb_config( $theme_name, $orig_config );

		// apply Categories & Articles module preset - is always set in new Setup Wizard (introduced in KB 11.30.0); is empty in old Setup Wizard
		$categories_articles_preset_name = EPKB_Utilities::post( 'categories_articles_preset_name' );
		if ( ! empty( $categories_articles_preset_name ) ) {
			$new_config = $categories_articles_preset_name == 'current' ? $orig_config : $this->get_themed_kb_config( $categories_articles_preset_name, $orig_config );
		}

		$kb_id = $new_config['id'];

		// apply Layout Name - is always set in new Setup Wizard (introduced in KB 11.30.0); is empty in old Setup Wizard
		$new_config['kb_main_page_layout'] = empty( $layout_name ) ? $new_config['kb_main_page_layout'] : $layout_name;

		$new_config = EPKB_Core_Utilities::ensure_modular_upgrade_completed( $new_config );

		// apply selected Modules
		$row_1_module = EPKB_Utilities::post( 'row_1_module' );
		$new_config['ml_row_1_module'] = empty( $row_1_module ) ? $new_config['ml_row_1_module'] : $row_1_module;
		$row_2_module = EPKB_Utilities::post( 'row_2_module' );
		$new_config['ml_row_2_module'] = empty( $row_2_module ) ? $new_config['ml_row_2_module'] : $row_2_module;
		$row_3_module = EPKB_Utilities::post( 'row_3_module' );
		$new_config['ml_row_3_module'] = empty( $row_3_module ) ? $new_config['ml_row_3_module'] : $row_3_module;
		$row_4_module = EPKB_Utilities::post( 'row_4_module' );
		$new_config['ml_row_4_module'] = empty( $row_4_module ) ? $new_config['ml_row_4_module'] : $row_4_module;
		$row_5_module = EPKB_Utilities::post( 'row_5_module' );
		$new_config['ml_row_5_module'] = empty( $row_5_module ) ? $new_config['ml_row_5_module'] : $row_5_module;

		// apply Sidebar location for Categories & Articles module - is always set in new Setup Wizard (introduced in KB 11.30.0); is empty in old Setup Wizard
		$categories_articles_sidebar_location = EPKB_Utilities::post( 'categories_articles_sidebar_location' );
		$new_config['ml_categories_articles_sidebar_toggle'] = empty( $categories_articles_sidebar_location )
			? $new_config['ml_categories_articles_sidebar_toggle']
			: ( $categories_articles_sidebar_location == 'none' ? 'off' : 'on' );
		$new_config['ml_categories_articles_sidebar_location'] = empty( $categories_articles_sidebar_location ) || $categories_articles_sidebar_location == 'none'
			? $new_config['ml_categories_articles_sidebar_location']
			: $categories_articles_sidebar_location;

		// set better Modular Sidebar width when user switched it 'on'
		if ( $new_config['ml_categories_articles_sidebar_toggle'] == 'on' && $orig_config['ml_categories_articles_sidebar_toggle'] == 'off' ) {
			for ( $row_number = 1; $row_number <= 5; $row_number ++ ) {
				if ( $new_config['ml_row_' . $row_number . '_module'] != 'categories_articles' ) {
					continue;
				}
				$new_config['ml_categories_articles_sidebar_desktop_width'] = $new_config['ml_row_' . $row_number . '_desktop_width_units'] == 'px'
					? 250
					: 9;
				break;
			}
		}

		// add menu link
		$this->add_kb_link_to_top_menu( $new_config['kb_main_pages'] );

		// get and sanitize KB Nickname
		$kb_nickname = EPKB_Utilities::post( 'kb_name', '', 'text', 50 );
		if ( empty( $kb_nickname ) ) {
			$kb_nickname = __( 'Knowledge Base', 'echo-knowledge-base' ) . ( $kb_id == EPKB_KB_Config_DB::DEFAULT_KB_ID ? '' : ' ' . $kb_id );
		}
		$new_config['kb_name'] = $kb_nickname;

		delete_option( 'epkb_run_setup' );

		$this->create_main_page_if_missing( $new_config );

		$main_page_id = EPKB_KB_Handler::get_first_kb_main_page_id( $new_config );

		// allow change slug only for users with admin capability
		if ( EPKB_Admin_UI_Access::is_user_access_to_context_allowed( 'admin_eckb_access_frontend_editor_write' ) ) {

			// allow change slug if Setup Wizard is running for the first time
			if ( $this->is_setup_run_first_time || EPKB_Utilities::get_wp_option( 'epkb_not_completed_setup_wizard_' . $kb_id, false )  ) {
				$kb_slug = EPKB_Utilities::post( 'kb_slug', '', 'text', 100 );
				$kb_slug = empty( $kb_slug ) ? EPKB_KB_Handler::get_default_slug( $kb_id ) : sanitize_title_with_dashes( $kb_slug );
				wp_update_post( array( 'ID' => $main_page_id, 'post_name' => $kb_slug ) );
			}

			// ensure that KB URL and article common path are the same; if not make them so
			$main_page_slug = EPKB_Core_Utilities::get_main_page_slug( $main_page_id );
			if ( empty( $new_config['kb_articles_common_path'] ) || $new_config['kb_articles_common_path'] != $main_page_slug ) {
				$new_config['kb_articles_common_path'] = $main_page_slug;
			}
		}

		// update article sidebar
		$sidebar_settings_id = (int)EPKB_Utilities::post( 'sidebar', 0 );
		if ( $sidebar_settings_id ) {

			foreach ( EPKB_KB_Wizard_Themes::$sidebar_compacted as $setting_name => $values ) {
				// something went wrong with the settings
				if ( ! isset( $values[ $sidebar_settings_id ] ) ) {
					continue;
				}

				if ( isset( $new_config[ $setting_name ] ) ) {
					if ( $new_config[ $setting_name ] != $values[ $sidebar_settings_id ] ) {
						$new_config[ $setting_name ] = $values[ $sidebar_settings_id ];
					}
					continue;
				}

				if ( $new_config['article_sidebar_component_priority'][ $setting_name ] != $values[ $sidebar_settings_id ] ) {
					$new_config['article_sidebar_component_priority'][ $setting_name ] = $values[ $sidebar_settings_id ];
				}
			}

			// if user has KB Sidebar on, then preserve it
			if ( $new_config['article_sidebar_component_priority']['kb_sidebar_left'] != '0' ) {
				$new_config['article-left-sidebar-toggle'] = 'on';
				$new_config['article_sidebar_component_priority']['kb_sidebar_left'] = '2';
			}

			if ( $new_config['article_sidebar_component_priority']['kb_sidebar_right'] != '0' ) {
				$new_config['article-right-sidebar-toggle'] = 'on';
				$new_config['article_sidebar_component_priority']['kb_sidebar_right'] = '2';
			}
		}

		// Don't change sidebar intro text on the second setup
		if ( ! $this->is_setup_run_first_time && isset( $new_config['sidebar_main_page_intro_text'] ) && isset( $orig_config['sidebar_main_page_intro_text'] ) ) {
			$new_config['sidebar_main_page_intro_text'] = $orig_config['sidebar_main_page_intro_text'];
		}

		// update KB and add-ons configuration
		$update_kb_msg = $this->update_kb_configuration( $kb_id, $orig_config, $new_config );
		if ( ! empty( $update_kb_msg ) ) {
			EPKB_Utilities::ajax_show_error_die( EPKB_Utilities::report_generic_error( 39, esc_html( $update_kb_msg ) ) );
		}

		// update icons if user chose another theme design - for new Setup Wizard is always 'current' (see usage of get_demo_category_icons() with $categories_articles_preset_name below)
		if ( $theme_name != 'current' ) {
			// if user selects Image theme then change font icons to image icons
			$categories_icons = EPKB_Icons::get_demo_category_icons( $new_config, $theme_name );
			if ( ! empty( $categories_icons ) ) {
				EPKB_Utilities::save_kb_option( $kb_id, EPKB_Icons::CATEGORIES_ICONS, $categories_icons );
			}
		}

		// update icons if user chose another theme design - is always set in new Setup Wizard (introduced in KB 11.30.0); is empty in old Setup Wizard
		if ( ! empty( $categories_articles_preset_name ) && $categories_articles_preset_name != 'current' ) {
			// if user selects Image theme then change font icons to image icons
			$categories_icons = EPKB_Icons::get_demo_category_icons( $new_config, $categories_articles_preset_name );
			if ( ! empty( $categories_icons ) ) {
				EPKB_Utilities::save_kb_option( $kb_id, EPKB_Icons::CATEGORIES_ICONS, $categories_icons );
			}
		}

		if ( EPKB_Admin_UI_Access::is_user_access_to_context_allowed( 'admin_eckb_access_frontend_editor_write' ) ) {

			// in case user changed article common path, flush the rules
			EPKB_Articles_CPT_Setup::register_custom_post_type( $new_config, $new_config['id'] );

			// always flush the rules; this will ensure that proper rewrite rules for layouts with article visible will be added
			flush_rewrite_rules( false );
			update_option( 'epkb_flush_rewrite_rules', true );

			EPKB_Admin_Notices::remove_ongoing_notice( 'epkb_changed_slug' );
		}

		// mark setup wizard was completed at least once for the current KB - does not matter admin or editor user
		delete_option( 'epkb_not_completed_setup_wizard_' . $kb_id );

		// update KB ids list option that indicates for which KBs the Setup Wizard is completed at least once
		EPKB_Core_Utilities::update_kb_flag( 'completed_setup_wizard_' . $new_config['id'] );

		wp_die( json_encode( array(
				'message' => 'success',
				'redirect_to_url' => admin_url( 'edit.php?post_type=' . EPKB_KB_Handler::get_post_type( $new_config['id'] ) . '&page=epkb-kb-need-help&epkb_after_kb_setup' ) ) ) );
	}

	/**
	 * SETUP WIZARD - get updated configuration from selected pre-made design and add-ons
	 *
	 * @param $theme_name
	 * @param $orig_config
	 * @return array
	 */
	private function get_themed_kb_config( $theme_name, $orig_config ) {

		$kb_id = $orig_config['id'];
		$kb_status = $orig_config['status'];
		$kb_articles_common_path = $orig_config['kb_articles_common_path'];

		// get selected theme config
		$theme_config = EPKB_KB_Wizard_Themes::get_theme( $theme_name, $orig_config );

		// overwrite current KB configuration with new configuration from this Wizard
		$new_config = array_merge( $orig_config, $theme_config );

		$new_config['id'] = $kb_id;
		$new_config['status'] = $kb_status;
		$new_config['kb_articles_common_path'] = $kb_articles_common_path;

		return $new_config;
	}

	/**
	 * if no KB Main Page found, e.g. user deleted it after running Setup Wizard the first time, then try to create a new one
	 *
	 * @param $new_config
	 */
	private function create_main_page_if_missing( &$new_config ) {

		$kb_id = $new_config['id'];
		$kb_nickname = $new_config['kb_name'];

		$kb_page_id = EPKB_KB_Handler::get_first_kb_main_page_id( $new_config );
		if ( ! empty( $kb_page_id ) ) {
			return;
		}

		// get and sanitize KB slug
		$kb_slug = EPKB_Utilities::post( 'kb_slug', '', 'text', 100 );
		$kb_slug = empty( $kb_slug ) ? EPKB_KB_Handler::get_default_slug( $kb_id ) : sanitize_title_with_dashes( $kb_slug );

		$new_kb_main_page = EPKB_KB_Handler::create_kb_main_page( $kb_id, $kb_nickname, $kb_slug );
		if ( is_wp_error( $new_kb_main_page ) ) {
			EPKB_Logging::add_log( 'Could not create KB main page', $kb_id, $new_kb_main_page );
		} else {
			$new_config['kb_articles_common_path'] = urldecode(sanitize_title_with_dashes( $new_kb_main_page->post_name, '', 'save' ));
			$kb_main_pages[ $new_kb_main_page->ID ] = $new_kb_main_page->post_title;
			$new_config['kb_main_pages'] = $kb_main_pages;
		}
	}

	/**
	 * Add KB link to top menu
	 *
	 * @param $kb_main_pages
	 */
	private function add_kb_link_to_top_menu( $kb_main_pages ) {

		// add items to menus if needed
		$menu_ids = EPKB_Utilities::post( 'menu_ids', [] );
		if ( $menu_ids && ! empty( $kb_main_pages ) ) {
			foreach ( $menu_ids as $id ) {
				$itemData =  array(
					'menu-item-object-id'   => key($kb_main_pages),
					'menu-item-parent-id'   => 0,
					'menu-item-position'    => 99,
					'menu-item-object'      => 'page',
					'menu-item-type'        => 'post_type',
					'menu-item-status'      => 'publish'
				);

				wp_update_nav_menu_item( $id, 0, $itemData );
			}
		}
	}

	/**
	 * Triggered when user submits changes to KB configuration
	 *
	 * @param $kb_id
	 * @param $orig_config
	 * @param $new_config
	 * @return string
	 */
	private function update_kb_configuration( $kb_id, $orig_config, $new_config ) {

		// core handles only default KB
		if ( $kb_id != EPKB_KB_Config_DB::DEFAULT_KB_ID && ! EPKB_Utilities::is_multiple_kbs_enabled() ) {
			EPKB_Logging::add_log('Invalid kb_id (yx1)', $kb_id);
			return __('Ensure that Multiple KB add-on is active and refresh this page', 'echo-knowledge-base');
		}

		// if user switches layout then ensure the sidebar is set correctly; $orig_config is used to overwrite filter
		$new_config = EPKB_Editor_Controller::reset_layout( $orig_config, $new_config, false );

		// save add-ons configuration
		$result = apply_filters( 'eckb_kb_config_save_input_v3', '', $kb_id, $new_config );
		if ( is_wp_error( $result ) ) {
			EPKB_Logging::add_log( 'Could not save the new configuration . (4)', $result );
		}

		// save KB core configuration
		$result = epkb_get_instance()->kb_config_obj->update_kb_configuration( $kb_id, $new_config );
		if ( is_wp_error( $result ) ) {
			EPKB_Logging::add_log( 'Could not save the new configuration . (5)', $result );

			/* @var $result WP_Error */
			$message = $result->get_error_message();
			if ( empty($message) ) {
				return __( 'Could not save the new configuration', 'echo-knowledge-base' ) . '(3)';
			} else {
				return __( 'Configuration NOT saved due to following problem:' . $message, 'echo-knowledge-base' );
			}
		}

		// we are done here
		return '';
	}

	/**
	 * Determine what sidebar setup the user has and return corresponding selection id.
	 *
	 * @param $kb_config
	 * @return int
	 */
	public static function get_current_sidebar_selection( $kb_config ) {

		if ( $kb_config['article-left-sidebar-toggle'] == 'on' && isset( $kb_config['article_sidebar_component_priority']['nav_sidebar_left'] ) && (int)$kb_config['article_sidebar_component_priority']['nav_sidebar_left'] ) {

			// Articles and Categories Navigation: Left Side
			if ( $kb_config['article_nav_sidebar_type_left'] == 'eckb-nav-sidebar-v1' ) {
				return 1;
			}

			// Top Categories Navigation: Left Side
			if ( $kb_config['article_nav_sidebar_type_left'] == 'eckb-nav-sidebar-categories' ) {
				return 3;
			}
		}

		if ( $kb_config['article-right-sidebar-toggle'] == 'on' && isset( $kb_config['article_sidebar_component_priority']['nav_sidebar_right'] ) && (int)$kb_config['article_sidebar_component_priority']['nav_sidebar_right'] ) {

			// Articles and Categories Navigation: Right Side
			if ( $kb_config['article_nav_sidebar_type_right'] == 'eckb-nav-sidebar-v1' ) {
				return 2;
			}

			// Top Categories Navigation: Right Side
			if ( $kb_config['article_nav_sidebar_type_right'] == 'eckb-nav-sidebar-categories' ) {
				return 4;
			}
		}

		// No Navigation/Default
		return 5;
	}

	/**
	 * Setup Wizard: Modular Step 3 - Choose Layout
	 *
	 * @return false|string
	 */
	private function wizard_step_modular_layout_content() {

		$layouts_config = [];

		// TODO: update text and images
		$layouts_config['Basic'] = [
			'layout_title'          => __( 'Basic Layout', 'echo-knowledge-base' ),
			'layout_description'    => __( 'This layout lists the top two levels of categories with the option to expand each category. ' .
											'It also shows the main articles from the top category.', 'echo-knowledge-base' ),
			'layout_image'          => [
				'title' => __( 'Basic', 'echo-knowledge-base' ),
				'url'   => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-3/Basic-Layout-Standard.jpg',
			],
			'layout_features'       => [
				__( 'Two levels of categories are initially displayed.', 'echo-knowledge-base' ),
				__( 'Articles from the top categories are also listed.', 'echo-knowledge-base' ),
			],
			/* 'youtube_link'          => [    // TODO
				'title' => __( 'Watch our Video', 'echo-knowledge-base' ),
				'url'   => '#',
			], */
			'demo_link'             => [
				'title' => __( 'Try out our Demo', 'echo-knowledge-base' ),
				'url'   => 'https://www.echoknowledgebase.com/demo-1-knowledge-base-basic-layout/',
			],
		];
		$layouts_config['Classic'] = [
			'layout_title'          => __( 'Classic Layout', 'echo-knowledge-base' ),
			'layout_description'    => __( 'In the Classic layout, the top categories are shown with the option to expand each category to reveal its articles and sub-categories directly below.', 'echo-knowledge-base' ),
			'layout_image'          => [
				'title' => __( 'Classic', 'echo-knowledge-base' ),
				'url'   => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-3/Classic-Layout-Standard.jpg',
			],
			'layout_features'       => [
				__( 'Initially, only top categories are listed.', 'echo-knowledge-base' ),
				__( 'Users can click to expand and view articles and sub-categories in a compact format.', 'echo-knowledge-base' ),
			],
			/* 'youtube_link'          => [    // TODO
				'title' => __( 'Watch our Video', 'echo-knowledge-base' ),
				'url'   => '#',
			], */
			'demo_link'             => [
				'title' => __( 'Try out our Demo', 'echo-knowledge-base' ),
				'url'   => 'https://www.echoknowledgebase.com/demo-12-knowledge-base-image-layout/',
			],
		];
		$layouts_config['Drill-Down'] = [
			'layout_title'          => __( 'Drill Down Layout', 'echo-knowledge-base' ),
			'layout_description'    => __( 'The Drill Down layout allows users to click on a category, expanding it to display a list of ' .
										'its articles and sub-categories in a wide format. Users can further explore sub-categories in the same manner.', 'echo-knowledge-base' ),
			'layout_image'          => [
				'title' => __( 'Drill Down', 'echo-knowledge-base' ),
				'url'   => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-3/Drill-Down-Layout-Standard.jpg',
			],
			'layout_features'       => [
				__( 'Only top categories are initially listed.', 'echo-knowledge-base' ),
				__( 'Users can click to reveal articles and sub-categories in an extensive format.', 'echo-knowledge-base' ),
			],
			/* 'youtube_link'          => [    // TODO
				'title' => __( 'Watch our Video', 'echo-knowledge-base' ),
				'url'   => '#',
			], */
			/* 'demo_link'             => [  // TODO
				'title' => __( 'Try out our Demo', 'echo-knowledge-base' ),
				'url'   => '#',
			], */
		];
		$layouts_config['Tabs'] = [
			'layout_title'          => __( 'Tabs Layout', 'echo-knowledge-base' ),
			'layout_description'    => __( 'The Tabs layout displays top categories as tabs, allowing documentation to be divided across several separate pages. ' .
										'This format is particularly useful for differentiating products, services, or areas of interest.', 'echo-knowledge-base' ),
			'layout_image'          => [
				'title' => __( 'Tabs', 'echo-knowledge-base' ),
				'url'   => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-3/Tab-Layout-Standard.jpg',
			],
			'layout_features'       => [
				__( 'Top categories are presented as tabs.', 'echo-knowledge-base' ),
				__( 'Each tab page follows a structure similar to the Basic layout.', 'echo-knowledge-base' ),
			],
			/* 'youtube_link'          => [    // TODO
				'title' => __( 'Watch our Video', 'echo-knowledge-base' ),
				'url'   => '#',
			], */
			'demo_link'             => [
				'title' => __( 'Try out our Demo', 'echo-knowledge-base' ),
				'url'   => 'https://www.echoknowledgebase.com/demo-3-knowledge-base-tabs-layout/',
			],
		];
		$layouts_config['Categories'] = [
			'layout_title'          => __( 'Category Focused Layout', 'echo-knowledge-base' ),
			'layout_description'    => __( 'The Categories layout resembles the Basic layout but includes the number of articles beside each category name.', 'echo-knowledge-base' ),
			'layout_image'          => [
				'title' => __( 'Category Focused', 'echo-knowledge-base' ),
				'url'   => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-3/Category-Layout-Standard.jpg',
			],
			'layout_features'       => [
				__( 'Lists two levels of categories. Sub categories link to their Category Archive page.', 'echo-knowledge-base' ),
				__( 'Displays the number of articles in each category.', 'echo-knowledge-base' ),
			],
			/* 'youtube_link'          => [    // TODO
				'title' => __( 'Watch our Video', 'echo-knowledge-base' ),
				'url'   => '#',
			], */
			'demo_link'             => [
				'title' => __( 'Try out our Demo', 'echo-knowledge-base' ),
				'url'   => 'https://www.echoknowledgebase.com/demo-14-category-layout/',
			],
		];
		$layouts_config['Grid'] = [
			'layout_title'          => __( 'Grid Layout', 'echo-knowledge-base' ),
			'layout_description'    => __( 'This layout presents top categories with the count of articles in each. Clicking on a category navigates the user to either an article page or a category archive page.', 'echo-knowledge-base' ),
			'layout_image'          => [
				'title' => __( 'Grid', 'echo-knowledge-base' ),
				'url'   => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-3/Grid-Layout-Standard.jpg',
			],
			'layout_features'       => [
				__( 'Initially displays only top categories.', 'echo-knowledge-base' ),
				__( 'Clicking on a category leads to the first article or the category archive page.', 'echo-knowledge-base' ),
			],
			/* 'youtube_link'          => [    // TODO
				'title' => __( 'Watch our Video', 'echo-knowledge-base' ),
				'url'   => '#',
			], */
			'demo_link'             => [
				'title' => __( 'Try out our Demo', 'echo-knowledge-base' ),
				'url'   => 'https://www.echoknowledgebase.com/demo-5-knowledge-base-grid-layout/',
			],
		];
		$layouts_config['Sidebar'] = [
			'layout_title'          => __( 'Sidebar Layout', 'echo-knowledge-base' ),
			'layout_description'    => __( 'The Sidebar layout features a navigation sidebar alongside articles on both the Knowledge Base (KB) Main Page and KB Article Pages.', 'echo-knowledge-base' ),
			'layout_image'          => [
				'title' => __( 'Sidebar', 'echo-knowledge-base' ),
				'url'   => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-3/Sidebar-Layout-Standard.jpg',
			],
			'layout_features'       => [
				__( 'The article navigation sidebar is always visible.', 'echo-knowledge-base' ),
				__( 'The KB Main Page includes introductory text.', 'echo-knowledge-base' ),
			],
			/* 'youtube_link'          => [    // TODO
				'title' => __( 'Watch our Video', 'echo-knowledge-base' ),
				'url'   => '#',
			], */
			'demo_link'             => [
				'title' => __( 'Try out our Demo', 'echo-knowledge-base' ),
				'url'   => 'https://www.echoknowledgebase.com/demo-7-knowledge-base-sidebar-layout/',
			],
		];

		// move the current layout to the top so the user can see it. Simply move the active layout to the top of the Layout step page
		$current_layout = $this->kb_config['kb_main_page_layout'];
		$active_layout = $layouts_config[ $current_layout ];
		unset( $layouts_config[ $current_layout ] );
		$layouts_config = array_merge( [ $current_layout => $active_layout ], $layouts_config );

		// add the get pro link to the layouts if the user does not have the pro version
		if ( ! $this->elay_enabled ) {
			$layouts_config['Grid']['get_pro_link'] = [
				'url'   => 'https://www.echoknowledgebase.com/wordpress-plugin/elegant-layouts/',
			];
			$layouts_config['Sidebar']['get_pro_link'] = [
				'url'   => 'https://www.echoknowledgebase.com/wordpress-plugin/elegant-layouts/',
			];
		}

		ob_start();  ?>

		<div id="epkb-wsb-step-3-panel" class="epkb-setup-wizard-theme epkb-wc-step-panel eckb-wizard-step-3">

			<div class="epkb-setup-wizard-no-categories-articles-message"><?php esc_html_e( 'Categories & Articles module was not selected in previous step.', 'echo-knowledge-base' ); ?></div>

			<div class="epkb-setup-wizard-step-container epkb-setup-wizard-step-container--layout">
				<input type="hidden" id="_wpnonce_epkb_ajax_action" name="_wpnonce_epkb_ajax_action" value="<?php echo wp_create_nonce( "_wpnonce_epkb_ajax_action" ); ?>"/>    <?php

				foreach ( $layouts_config as $layout_name => $layout_config ) { ?>

					<!-- Layout -->
					<div class="epkb-setup-wizard-step__item">

						<!-- Title -->
						<div class="epkb-setup-wizard-step__item-title"><span><?php echo esc_html( $layout_config['layout_title'] ); ?></span></div>

						<!-- Content -->
						<div class="epkb-setup-wizard-step__item-content">

							<!-- Layout Selection -->
							<div class="epkb-setup-wizard-step__item-selection">
								<div class="epkb-setup-option-container<?php echo $layout_name == $this->kb_config['kb_main_page_layout'] ? ' ' . 'epkb-setup-option-container--active' : ''; ?>">
									<div class="epkb-setup-option__inner">
										<div class="epkb-setup-option__selection">
											<div class="epkb-setup-option__option-container">
												<label class="epkb-setup-option__option__label">
													<span><?php echo esc_html( $layout_config['layout_image']['title'] ); ?></span>
												</label>
											</div>
											<div class="epkb-setup-option__featured-img-container">
												<img class="epkb-setup-option__featured-img" src="<?php echo esc_url( $layout_config['layout_image']['url'] ); ?>" title="<?php echo esc_attr( $layout_config['layout_image']['title'] ); ?>" alt="<?php echo esc_attr( $layout_config['layout_image']['title'] ); ?>" />
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- Layout Description -->
							<div class="epkb-setup-wizard-step__item-description">

								<!-- Description Text -->
								<div class="epkb-setup-wizard-step__item-description-text"><?php echo esc_html( $layout_config['layout_description'] ); ?></div>  <?php

								// Choose/Selected Button
								if ( isset( $layout_config['get_pro_link'] ) ) {    ?>
									<button class="epkb-success-btn epkb-setup-wizard-step__item-description__button-pro" data-target="<?php echo esc_attr( 'epkb-dialog-pro-feature-ad-' . strtolower( $layout_name ) ); ?>"><?php esc_html_e( 'Choose', 'echo-knowledge-base'); ?></button> <?php
								} else {    ?>
									<label class="epkb-setup-wizard-step__item-description__option__label">
										<input type="radio" name="epkb-layout" value="<?php echo esc_attr( $layout_name ); ?>"<?php checked( $layout_name, $this->kb_config['kb_main_page_layout'] ); ?>>
									</label> <?php
								} ?>

								<!-- Key Features Title -->
								<div class="epkb-setup-wizard-step__item-description-features-title"><?php esc_html_e( 'Key Features', 'echo-knowledge-base'); ?></div>

								<!-- Features -->
								<ul class="epkb-setup-wizard-step__item-description-features">   <?php
									foreach ( $layout_config['layout_features'] as $index => $feature ) {  ?>
										<li data-feature="<?php echo esc_attr( $index + 1 ); ?>"><?php echo wp_kses( $feature, EPKB_Utilities::get_admin_ui_extended_html_tags() ); ?></li><?php
									}   ?>
								</ul>   <?php

								if ( isset( $layout_config['youtube_link'] ) || isset( $layout_config['demo_link'] ) ) {    ?>
									<!-- Links -->
									<div class="epkb-setup-wizard-step__item-description-links">   <?php

										if ( isset( $layout_config['youtube_link'] ) ) {   ?>
											<!-- Youtube Link -->
											<div class="epkb-setup-wizard-step__item-description-link epkb-setup-wizard-step__item-youtube-link">
												<a href="<?php echo esc_url( $layout_config['youtube_link']['url'] ); ?>"><?php echo esc_html( $layout_config['youtube_link']['title'] ); ?></a>
											</div>  <?php
										}

										if ( isset( $layout_config['demo_link'] ) ) {  ?>
											<!-- Demo Link -->
											<div class="epkb-setup-wizard-step__item-description-link epkb-setup-wizard-step__item-demo-link">
												<a href="<?php echo esc_url( $layout_config['demo_link']['url'] ); ?>"><?php echo esc_html( $layout_config['demo_link']['title'] ); ?></a>
											</div>  <?php
										}   ?>

									</div>  <?php
								}   ?>
							</div>
						</div>
					</div>  <?php
					if ( isset( $layout_config['get_pro_link'] ) ) {
						EPKB_HTML_Forms::dialog_pro_feature_ad( array(
							'id' => 'epkb-dialog-pro-feature-ad-' . strtolower( $layout_name ),
							'title' => sprintf(__("Unlock %s" . $layout_config['layout_title'] . " Feature%s By Upgrading to PRO ", 'echo-knowledge-base'), '<strong>', '</strong>'),
							'list' => array( __( 'Grid Layout for the Main Page', 'echo-knowledge-base'), __( 'Sidebar Layout for the Main Page', 'echo-knowledge-base'),
											__( 'Resource Links feature for the Main Page', 'echo-knowledge-base')),
							'btn_text' => __('Upgrade Now', 'echo-knowledge-base'),
							'btn_url' => 'https://www.echoknowledgebase.com/wordpress-plugin/elegant-layouts/',
							'show_close_btn' => 'yes',
							'return_html' => true,
						));
					}
				}   ?>

			</div>

		</div>	<?php

		return ob_get_clean();
	}

	/**
	 * Return Layout name
	 *
	 * @param $title
	 * @return string|void
	 */
	private static function get_layout_title( $title ) {
		switch ( $title ) {
			case 'Basic': return __( 'Basic Layout', 'echo-knowledge-base' );
			case 'Tabs': return __( 'Tabs Layout', 'echo-knowledge-base' );
			case 'Categories': return __( 'Category Focused Layout', 'echo-knowledge-base' );
			case 'Classic': return __( 'Classic Layout', 'echo-knowledge-base' );
			case 'Drill-Down': return __( 'Drill Down Layout', 'echo-knowledge-base' );
			case 'Grid': return __( 'Grid Layout', 'echo-knowledge-base' );
			case 'Sidebar': return __( 'Sidebar Layout', 'echo-knowledge-base' );
			default: return '';
		}
	}

	/**
	 * Return HTML for Step Header based on args
	 *
	 * @param $args
	 * @return false|string
	 */
	private static function wizard_step_header( $args ) {
		ob_start();     ?>
		<div class="epkb-wizard-header__info">
			<h1 class="epkb-wizard-header__info__title"><?php echo wp_kses( $args['title_html'], EPKB_Utilities::get_admin_ui_extended_html_tags() ); ?></h1>
		</div>
		<div class="epkb-setup-wizard-theme-header">
			<h2 class="epkb-setup-wizard-theme-header__info__title"><?php echo wp_kses( $args['info_title'], EPKB_Utilities::get_admin_ui_extended_html_tags() ); ?></h2>  <?php
			if ( isset( $args['info_description'] ) ) { ?>
				<h2 class="epkb-setup-wizard-theme-header__info__description"><?php echo esc_html( $args['info_description'] ); ?></h2>  <?php
			}
			if ( isset( $args['info_html'] ) ) {
				echo wp_kses( $args['info_html'], EPKB_Utilities::get_admin_ui_extended_html_tags() );
			}   ?>
		</div>  <?php
		$first_time = EPKB_Core_Utilities::is_run_setup_wizard_first_time() || EPKB_Utilities::post( 'emkb_admin_notice' ) == 'kb_add_success';
		if ( ! $first_time && isset( $args['content_show_option'] ) ) { ?>
			<div class="epkb-setup-wizard-theme-content-show-option" data-ignore_layouts="<?php echo esc_attr( $args['content_show_option']['ignore_layouts'] ); ?>">
				<h5 class="epkb-setup-wizard-theme-content-show-option__text"><?php echo esc_html( $args['content_show_option']['text'] ); ?></h5> <?php
				EPKB_HTML_Elements::checkbox_toggle( [
					'name' => 'epkb-setup-wizard-theme-content-show-option__toggle',
					'toggleOnText'  => __( 'yes', 'echo-knowledge-base' ),
					'toggleOffext'  => __( 'no', 'echo-knowledge-base' ),
				] ); ?>
			</div> <?php
		}
		return ob_get_clean();
	}

	/**
	 * Setup Wizard: Modular Step 2 - Choose which Modules on which Row to display
	 *
	 * @return false|string
	 */
	private function wizard_step_modules_content() {

		$modules_rows_config = $this->get_modules_rows_config();
		$modules_presets_config = $this->get_modules_presets_config();

		$row_number = 1;
		$selected_modules_flag = true;
		$modules_total = count( $modules_rows_config );

		$sidebar_location_value = 'none';
		if ( $this->kb_config['ml_categories_articles_sidebar_toggle'] == 'on' ) {
			$sidebar_location_value = $this->kb_config['ml_categories_articles_sidebar_location'];
		}

		ob_start();  ?>

		<div id="epkb-wsb-step-2-panel" class="epkb-wc-step-panel eckb-wizard-step-2">

			<div class="epkb-setup-wizard-step-container epkb-setup-wizard-step-container--modules">
				<input type="hidden" id="_wpnonce_epkb_ajax_action" name="_wpnonce_epkb_ajax_action" value="<?php echo wp_create_nonce( "_wpnonce_epkb_ajax_action" ); ?>"/>

				<!-- Modules Rows List -->
				<div class="epkb-setup-wizard-module-rows-list">    <?php

					foreach ( $modules_rows_config as $module_name => $module_row_config ) {

						// Show Inactive Rows title before the first unselected module
						if ( $module_row_config['toggle_value'] == 'none' && $selected_modules_flag ) { ?>
							<div class="epkb-setup-wizard-hidden-rows-title epkb-setup-wizard-hidden-rows-title--active"><?php esc_html_e( 'Inactive Features', 'echo-knowledge-base' ); ?><span class="epkb-setup-wizard-hidden-rows-title__line"></span></div>    <?php
							$selected_modules_flag = false;
						}

						$elay_modules_disabled = in_array( $module_name, ['resource_links'] ) && ( ! $this->elay_enabled || $this->is_old_elay );         ?>

						<!-- Module Row -->
						<div class="epkb-setup-wizard-module-row<?php echo $module_row_config['toggle_value'] == 'none' ? '' : ' ' . 'epkb-setup-wizard-module-row--active';
								echo $elay_modules_disabled ? ' ' . 'epkb-setup-wizard-module-row--resource-link--disabled' : ''; ?>" data-row-module="<?php echo esc_attr( $module_name ); ?>">

							<!-- Module Row Left Settings -->
							<div class="epkb-setup-wizard-module-row-left-settings">
								<div class="epkb-setup-wizard-module-settings-title"> <?php
									echo esc_html( $module_row_config['label'] );
									EPKB_HTML_Elements::display_tooltip( '', '', array(), $module_row_config['tooltip_external_links'] ); ?>
								</div> <?php
								if ( $module_name == 'categories_articles' ) {  ?>
									<!-- Settings Row -->
									<div class="epkb-setup-wizard-module-settings-row epkb-setup-wizard-module-settings-row--sidebar">  <?php
										EPKB_HTML_Elements::radio_buttons_horizontal( [
											'name' => 'categories_articles_sidebar_location',
											'options' => [
												'none' => __( 'None', 'echo-knowledge-base' ),
												'left' => __( 'Left', 'echo-knowledge-base' ),
												'right' => __( 'Right', 'echo-knowledge-base' ),
											],
											'value' => $sidebar_location_value,
											'label' => __( 'Sidebar Visibility', 'echo-knowledge-base' ),
											'input_group_class' => 'epkb-setup-wizard-module-sidebar-selector',
										] );    ?>
									</div>  <?php
								}   ?>
							</div>

							<!-- Module Row Preview -->
							<div class="epkb-setup-wizard-module-row-preview">  <?php

								if ( $module_name == 'categories_articles' ) {   ?>
									<!-- Sidebar Left -->
									<div class="epkb-setup-wizard-module-sidebar epkb-setup-wizard-module-sidebar--left<?php echo $sidebar_location_value == 'left' ? ' ' . 'epkb-setup-wizard-module-sidebar--active' : ''; ?>">
										<img alt="" src="<?php echo esc_url( Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-2/module-sidebar.jpg' ); ?>">
									</div>  <?php
								}

								if ( isset( $modules_presets_config[$module_name] ) ) {    ?>
									<!-- Module -->
									<div class="epkb-setup-wizard-module epkb-setup-wizard-module--<?php echo esc_attr( $module_name ); ?>">   <?php

										foreach ( $modules_presets_config[$module_name] as $layout_name => $layout_config ) {  ?>

											<!-- Layout -->
											<div class="epkb-setup-wizard-module-layout<?php echo empty( $layout_config['preselected'] ) ? '' : ' ' . 'epkb-setup-wizard-module-layout--active'; ?> epkb-setup-wizard-module-layout--<?php echo esc_attr( $layout_name ); ?>">  <?php
												foreach ( $layout_config['presets'] as $preset_name => $preset_config ) {    ?>
													<!-- Preset -->
													<div class="epkb-setup-wizard-module-preset<?php echo empty( $preset_config['preselected'] ) ? '' : ' ' . 'epkb-setup-wizard-module-preset--active'; ?> epkb-setup-wizard-module-preset--<?php echo esc_attr( $preset_name ); ?>">  <?php

														if ( $module_name == 'categories_articles' ) {
															$layouts = [
																'Basic'         => 'Basic-Layout-Standard-no-search.jpg',
																'Tabs'          => 'Tab-Layout-Standard-no-search.jpg',
																'Categories'    => 'Category-Layout-Standard-no-search.jpg',
																'Classic'       => 'Classic-Layout-Standard-no-search.jpg',
																'Drill-Down'    => 'Drill-Down-Layout-Standard-no-search.jpg',
																'Sidebar'       => 'Sidebar-Layout-Standard-no-search.jpg',
																'Grid'          => 'Grid-Layout-Standard-no-search.jpg'
															];

															$module_url = isset( $layouts[$layout_name] ) ? Echo_Knowledge_Base::$plugin_url . 'img/setup-wizard/step-2/' . $layouts[$layout_name] : '';

															echo '<img src="' . esc_url( $module_url ) . '">';

														} else {
															echo '<img src="' . esc_url( $preset_config['image_url'] ) . '">';
														}       ?>
													</div>  <?php
												}   ?>
											</div>  <?php

										}   ?>
									</div>  <?php
								}

								if ( $module_name == 'categories_articles' ) {   ?>
									<!-- Sidebar Right -->
									<div class="epkb-setup-wizard-module-sidebar epkb-setup-wizard-module-sidebar--right<?php echo $sidebar_location_value == 'right' ? ' ' . 'epkb-setup-wizard-module-sidebar--active' : ''; ?>">
										<img alt="" src="<?php echo esc_url( Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-2/module-sidebar.jpg' ); ?>">
									</div>  <?php
								}   ?>

							</div>

							<!-- Module Row Right Settings -->
							<div class="epkb-setup-wizard-module-row-right-settings">

								<!-- Module -->
								<div class="epkb-setup-wizard-module">

									<!-- Settings Row -->
									<div class="epkb-setup-wizard-module-settings-row epkb-setup-wizard-module-settings-row--active epkb-setup-wizard-module-settings-row--allowed-modules"> <?php
										if ( $elay_modules_disabled ) {     	?>
											<button class="epkb-success-btn epkb-setup-wizard-module-row--resource-links-activate">
												<?php esc_html_e( 'Activate', 'echo-knowledge-base' ); ?>
											</button> <?php
										} else { ?>
											<span class="epkbfa epkbfa-chevron-up epkb-setup-wizard-module-row-sequence epkb-setup-wizard-module-row-sequence--up"
											      data-tooltip="<?php esc_html_e( 'Move Up', 'echo-knowledge-base' ); ?>"></span>
											<span class="epkbfa epkbfa-chevron-down epkb-setup-wizard-module-row-sequence epkb-setup-wizard-module-row-sequence--down"
											      data-tooltip="<?php esc_html_e( 'Move Down', 'echo-knowledge-base' ); ?>"></span> <?php
											EPKB_HTML_Elements::radio_buttons_horizontal( [
												'name' => 'module_row_toggle_' . $row_number,
												'options' => $module_row_config['toggle_options'],
												'value' => $module_row_config['toggle_value'],
												'input_group_class' => 'epkb-setup-wizard-module-row-toggle',
											] );
										} ?>
									</div>

								</div>
							</div>
						</div>  <?php

						// If all modules are selected, then render hidden Inactive Rows title at the end of modules list
						if ( $row_number == $modules_total && $selected_modules_flag ) { ?>
							<div class="epkb-setup-wizard-hidden-rows-title"><?php esc_html_e( 'Inactive Rows', 'echo-knowledge-base' ); ?><span class="epkb-setup-wizard-hidden-rows-title__line"></span></div>    <?php
							$selected_modules_flag = false;
						}

						$row_number++;
					}   ?>

				</div>
			</div> <?php

			EPKB_HTML_Forms::dialog_pro_feature_ad( array(
				'id'                => 'epkb-dialog-pro-feature-ad-resource-links',
				'title'             => sprintf( __( "Unlock %sResource Links Feature%s", 'echo-knowledge-base' ), '<strong>', '</strong>' ),
				'list'              => array( __( 'Grid Layout for the Main Page', 'echo-knowledge-base' ), __( 'Sidebar Layout for the Main Page', 'echo-knowledge-base' ), __( 'Resource Links feature for the Main Page', 'echo-knowledge-base' ) ),
				'btn_text'          => __( 'Upgrade Now', 'echo-knowledge-base' ),
				'btn_url'           => 'https://www.echoknowledgebase.com/wordpress-plugin/elegant-layouts/',
				'show_close_btn'    => 'yes',
				'return_html'       => true,
			) );    ?>

		</div>	<?php

		return ob_get_clean();
	}

	/**
	 * Setup Wizard: Modular Step 4 - Choose Presets for selected Modules
	 *
	 * @return false|string
	 */
	private function wizard_step_presets_content() {

		$modules_presets_config = $this->get_modules_presets_config();

		ob_start();  ?>

		<div id="epkb-wsb-step-4-panel" class="epkb-wc-step-panel eckb-wizard-step-4">

			<div class="epkb-setup-wizard-no-categories-articles-message"><?php esc_html_e( 'Categories & Articles module was not selected in previous step.', 'echo-knowledge-base' ); ?></div>

			<div class="epkb-setup-wizard-step-container epkb-setup-wizard-step-container--presets">
				<input type="hidden" id="_wpnonce_epkb_ajax_action" name="_wpnonce_epkb_ajax_action" value="<?php echo wp_create_nonce( "_wpnonce_epkb_ajax_action" ); ?>"/>

				<!-- Module Row -->
				<div class="epkb-setup-wizard-module-row">

					<!-- Module Preset Previews -->
					<div class="epkb-setup-wizard-module-preset-previews">

						<!-- Module -->
						<div class="epkb-setup-wizard-module epkb-setup-wizard-module--categories_articles">   <?php

							foreach ( $modules_presets_config['categories_articles'] as $layout_name => $layout_config ) {  ?>

								<!-- Layout -->
								<div class="epkb-setup-wizard-module-layout<?php echo empty( $layout_config['preselected'] ) ? '' : ' ' . 'epkb-setup-wizard-module-layout--active'; ?> epkb-setup-wizard-module-layout--<?php echo esc_attr( $layout_name ); ?>">  <?php

									foreach ( $layout_config['presets'] as $preset_name => $preset_config ) {    ?>
										<!-- Preset -->
										<div class="epkb-setup-wizard-module-preset<?php echo empty( $preset_config['preselected'] ) ? '' : ' ' . 'epkb-setup-wizard-module-preset--active'; ?> epkb-setup-wizard-module-preset--<?php echo esc_attr( $preset_name ); ?>">
											<img src="<?php echo esc_url( $preset_config['image_url'] ); ?>" alt="">
										</div>  <?php
									}   ?>
								</div>  <?php

							}   ?>
						</div>

					</div>

					<!-- Module Preset Settings -->
					<div class="epkb-setup-wizard-module-preset-settings">

						<!-- Module -->
						<div class="epkb-setup-wizard-module epkb-setup-wizard-module--categories_articles">   <?php

							foreach ( $modules_presets_config['categories_articles'] as $layout_name => $layout_config ) {
								$presets_titles = [];
								$preselected_preset = '';
								foreach ( $layout_config['presets'] as $preset_name => $preset_config ) {
									$presets_titles[$preset_name] = $preset_config['title'];
									if ( isset( $preset_config['preselected'] ) ) {
										$preselected_preset = $preset_name;
									}
								}   ?>
								<!-- Settings Row -->
								<div class="epkb-setup-wizard-module-settings-row<?php echo empty( $layout_config['preselected'] ) ? '' : ' ' . 'epkb-setup-wizard-module-settings-row--active'; ?> epkb-setup-wizard-module-settings-row--layout epkb-setup-wizard-module-settings-row--<?php echo esc_attr( $layout_name ); ?>">    <?php
									EPKB_HTML_Elements::radio_buttons_horizontal( [
										'name' => 'categories_articles_' . strtolower( $layout_name ) . '_preset',
										'options' => $presets_titles,
										'value' => $preselected_preset,
										'input_group_class' => 'epkb-setup-wizard-module-preset-selector',
									] );    ?>
								</div>  <?php
							}   ?>
						</div>

					</div>

				</div>

			</div>
		</div>	<?php

		return ob_get_clean();
	}

	/**
	 * Setup Wizard: Modular Step 5 - Choose navigation on left or right sidebar
	 *
	 * @return false|string
	 */
	private function wizard_step_modular_navigation_content() {

		$groups = EPKB_KB_Wizard_Themes::get_sidebar_groups();

		$selected_id = $this->is_setup_run_first_time ? 1 : self::get_current_sidebar_selection( $this->kb_config );

		ob_start(); ?>

		<div id="epkb-wsb-step-5-panel" class="epkb-setup-wizard-sidebar epkb-wc-step-panel eckb-wizard-step-article-page eckb-wizard-step-5">
			<div class="epkb-setup-wizard-theme-preview">
				<div class="epkb-wizard-theme-tab-container">
					<div class="epkb-setup-wizard-article__container">
						<div class="epkb-setup-wizard-article-image__container">
							<div class="epkb-setup-wizard-article-image__list"><?php
								foreach ( $groups as $group ) {
									foreach ( $group['options'] as $id => $option_title ) {
										$image_id = $id ? $id : self::get_current_sidebar_selection( $this->kb_config );
										$image_url = Echo_Knowledge_Base::$plugin_url . 'img/' . EPKB_KB_Wizard_Themes::$sidebar_images[ $image_id ]; ?>
										<div class="epkb-setup-wizard__featured-img-container <?php echo $selected_id === $image_id ? 'epkb-setup-wizard__featured-img-container--active' : ''; ?>" data-value="<?php echo esc_attr( $image_id ); ?>">
											<img alt="" class="epkb-setup-wizard__featured-img" src="<?php echo esc_url( $image_url ); ?>" title="<?php echo esc_attr( $option_title ); ?>"/>
										</div> <?php
									}
								} ?>
							</div>
						</div>
						<div class="epkb-setup-wizard-option__container">
							<div class="epkb-setup-wizard-option__title"><?php esc_html_e( 'Navigation', 'echo-knowledge-base'); ?></div> <?php
							$article_navigation = 'none';
							$article_location = 'left';
							if ( $selected_id === 1 || $selected_id === 2 ) {
								$article_navigation = 'categories_articles';
							}
							if ( $selected_id === 3 || $selected_id === 4 ) {
								$article_navigation = 'top_categories';
							}
							if ( $selected_id === 2 || $selected_id === 4 ) {
								$article_location = 'right';
							}
							EPKB_HTML_Elements::radio_buttons_horizontal( [
								'name' => 'article_navigation',
								'options' => [
									'categories_articles' => __( 'Categories and Articles', 'echo-knowledge-base' ),
									'top_categories' => __( 'Top Categories', 'echo-knowledge-base' ),
									'none' => __( 'None', 'echo-knowledge-base' ),
								],
								'value' => $article_navigation,
								'input_group_class' => 'epkb-setup-wizard-option__navigation-selector',
								'group_data' => [ 'current-value' => $article_navigation, 'hide-none-on-layout' => EPKB_Layout::SIDEBAR_LAYOUT ],
							] ); ?>
							<div class="epkb-setup-wizard-option__title"><?php esc_html_e( 'Location', 'echo-knowledge-base'); ?></div> <?php
							EPKB_HTML_Elements::radio_buttons_horizontal( [
								'name' => 'article_location',
								'options' => [
									'left' => __( 'Left', 'echo-knowledge-base' ),
									'right' => __( 'Right', 'echo-knowledge-base' ),
								],
								'value' => $article_location,
								'input_group_class' => 'epkb-setup-wizard-option__location-selector',
							] ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>  <?php

		return ob_get_clean();
	}

	/**
	 * Return array of Presets for each Module TODO: update images (current are placeholders for tests)
	 *
	 * @return array
	 */
	private function get_modules_presets_config() {

		$modules_presets_config = [
			'search' => [],
			'categories_articles' => [],
			'articles_list' => [],
			'faqs' => [],
			'resource_links' => [],
		];

		// Search Module Presets
		$modules_presets_config['search']['layout_1'] = [
			'preselected' => true,
			'presets' => [
				'preset_1' => [
					'preselected'   => true,
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-2/module-search.jpg',
					'title'         => __( 'Basic', 'echo-knowledge-base' ),
				],
			],
		];

		// Categories & Articles Module Presets: Basic
		$modules_presets_config['categories_articles']['Basic'] = [
			'preselected' => $this->kb_config['kb_main_page_layout'] == 'Basic',
			'presets' => [
				'standard' => [
					'preselected'   => true,
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-basic-standard.jpg',
					'title'         => __( 'Standard', 'echo-knowledge-base' ),
				],
				'elegant' => [
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-basic-elegant.jpg',
					'title'         => __( 'Elegant', 'echo-knowledge-base' ),
				],
				'modern' => [
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-basic-modern.jpg',
					'title'         => __( 'Modern', 'echo-knowledge-base' ),
				],
				'image' => [
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-basic-image.jpg',
					'title'         => __( 'Image', 'echo-knowledge-base' ),
				],
				'informative' => [
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-basic-informative.jpg',
					'title'         => __( 'Informative', 'echo-knowledge-base' ),
				],
				'distinct' => [
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-basic-distinct.jpg',
					'title'         => __( 'Distinct', 'echo-knowledge-base' ),
				],
			],
		];

		// Categories & Articles Module Presets: Tabs
		$modules_presets_config['categories_articles']['Tabs'] = [
			'preselected' => $this->kb_config['kb_main_page_layout'] == 'Tabs',
			'presets' => [
				'basic' => [
					'preselected'   => true,
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-tabs-basic.jpg',
					'title'         => __( 'Basic', 'echo-knowledge-base' ),
				],
				'organized' => [
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-tabs-organized.jpg',
					'title'         => __( 'Organized', 'echo-knowledge-base' ),
				],
				'organized_2' => [
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-tabs-organized-2.jpg',
					'title'         => __( 'Organized 2', 'echo-knowledge-base' ),
				],
				'products_based' => [
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-tabs-product-based.jpg',
					'title'         => __( 'Product Based', 'echo-knowledge-base' ),
				]
			],
		];

		// Categories & Articles Module Presets: Categories
		$modules_presets_config['categories_articles']['Categories'] = [
			'preselected' => $this->kb_config['kb_main_page_layout'] == 'Categories',
			'presets' => [
				'standard_2' => [
					'preselected'   => true,
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-categoryfocused-standard.jpg',
					'title'         => __( 'Standard 2', 'echo-knowledge-base' ),
				],
				'business' => [
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-categoryfocused-business.jpg',
					'title'         => __( 'Business', 'echo-knowledge-base' ),
				],
				'minimalistic' => [
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-categoryfocused-minimalistic.jpg',
					'title'         => __( 'Minimalistic', 'echo-knowledge-base' ),
				],
			],
		];

		// Categories & Articles Module Presets: Classic    // TODO: it looks like we need update names for the presets to fit the current logic (as Categories & Articles module does not control FAQs or Articles List or Sidebar by presets)
		$modules_presets_config['categories_articles']['Classic'] = [
			'preselected' => $this->kb_config['kb_main_page_layout'] == 'Classic',
			'presets' => [
				'ml_articles_list_classic_layout' => [
					'preselected'   => true,
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-classic-1.jpg',
					'title'         => __( 'Default Preset', 'echo-knowledge-base' ),   // __( 'Classic', 'echo-knowledge-base' ),
				],
			/*	'ml_classic_layout_articles_list' => [
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/categories-and-articles-module-preset2.jpg',
					'title'         => __( 'Classic 2', 'echo-knowledge-base' ),
				],
				'ml_articles_list_classic_layout_faqs' => [
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/categories-and-articles-module-preset3.jpg',
					'title'         => __( 'Classic 3', 'echo-knowledge-base' ),
				],
				'ml_classic_layout_sidebar' => [
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/categories-and-articles-module-preset4.jpg',
					'title'         => __( 'Classic 4', 'echo-knowledge-base' ),
				],
				'ml_classic_layout_sidebar_faqs' => [
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/categories-and-articles-module-preset5.jpg',
					'title'         => __( 'Classic 5', 'echo-knowledge-base' ),
				],*/
			],
		];

		// Categories & Articles Module Presets: Drill-Down    // TODO: it looks like we need update names for the presets to fit the current logic (as Categories & Articles module does not control FAQs or Articles List or Sidebar by presets)
		$modules_presets_config['categories_articles']['Drill-Down'] = [
			'preselected' => $this->kb_config['kb_main_page_layout'] == 'Drill-Down',
			'presets' => [
				'ml_articles_list_drill_down_layout' => [
					'preselected'   => true,
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-drill-down-1.jpg',
					'title'         => __( 'Default Preset', 'echo-knowledge-base' ),   // __( 'Drill Down', 'echo-knowledge-base' ),
				],
			/*	'ml_drill_down_layout_articles_list' => [
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/categories-and-articles-module-preset2.jpg',
					'title'         => __( 'Drill Down 2', 'echo-knowledge-base' ),
				],
				'ml_articles_list_drill_down_layout_faqs' => [
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/categories-and-articles-module-preset3.jpg',
					'title'         => __( 'Drill Down 3', 'echo-knowledge-base' ),
				],
				'ml_drill_down_layout_sidebar' => [
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/categories-and-articles-module-preset4.jpg',
					'title'         => __( 'Drill Down 4', 'echo-knowledge-base' ),
				],*/
			],
		];

		// Categories & Articles Module Add-ons Presets
		if ( $this->elay_enabled ) {

			// Categories & Articles Module Presets: Grid
			$modules_presets_config['categories_articles']['Grid'] = [
				'preselected' => $this->kb_config['kb_main_page_layout'] == 'Grid',
				'presets' => [
					'grid_basic' => [
						'preselected'   => true,
						'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-grid-basic.jpg',
						'title'         => __( 'Basic', 'echo-knowledge-base' ),
					],
					'grid_demo_5' => [
						'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-grid-informative.jpg',
						'title'         => __( 'Informative', 'echo-knowledge-base' ),
					],
					'grid_demo_6' => [
						'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-grid-simple.jpg',
						'title'         => __( 'Simple', 'echo-knowledge-base' ),
					],
					'grid_demo_7' => [
						'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-grid-left-icon.jpg',
						'title'         => __( 'Left Icon Style', 'echo-knowledge-base' ),
					],
				],
			];

			// Categories & Articles Module Presets: Sidebar
			$modules_presets_config['categories_articles']['Sidebar'] = [
				'preselected' => $this->kb_config['kb_main_page_layout'] == 'Sidebar',
				'presets' => [
					'sidebar_basic' => [
						'preselected'   => true,
						'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-sidebar-basic.jpg',
						'title'         => __( 'Basic', 'echo-knowledge-base' ),
					],
					'sidebar_colapsed' => [
						'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-sidebar-collapsed.jpg',
						'title'         => __( 'Collapsed', 'echo-knowledge-base' ),
					],
					'sidebar_formal' => [
						'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-sidebar-formal.jpg',
						'title'         => __( 'Formal', 'echo-knowledge-base' ),
					],
					'sidebar_compact' => [
						'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-sidebar-compact.jpg',
						'title'         => __( 'Compact', 'echo-knowledge-base' ),
					],
					'sidebar_plain' => [
						'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-4/cat-art-module-sidebar-plain.jpg',
						'title'         => __( 'Plain', 'echo-knowledge-base' ),
					],
				],
			];
		}

		// Articles List Module Presets
		$modules_presets_config['articles_list']['layout_1'] = [
			'preselected' => true,
			'presets' => [
				'preset_1' => [
					'preselected'   => true,
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-2/module-articles-list.jpg',
					'title'         => __( 'Basic', 'echo-knowledge-base' ),
				],
			],
		];

		// FAQs Module Presets
		$modules_presets_config['faqs']['layout_1'] = [
			'preselected' => true,
			'presets' => [
				'preset_1' => [
					'preselected'   => true,
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-2/module-faqs.jpg',
					'title'         => __( 'Basic', 'echo-knowledge-base' ),
				],
			],
		];

		// Resource Links Module Presets
		$modules_presets_config['resource_links']['layout_1'] = [
			'preselected' => true,
			'presets' => [
				'preset_1' => [
					'preselected'   => true,
					'image_url'     => Echo_Knowledge_Base::$plugin_url . 'img/' . 'setup-wizard/step-2/module-resource-links.jpg',
					'title'         => __( 'Basic', 'echo-knowledge-base' ),
				],
			],
		];

		return $modules_presets_config;
	}

	/**
	 * Return configuration for each Module Row
	 *
	 * @return array
	 */
	private function get_modules_rows_config() {

		$modules_config = [
			'search'                => [
				'label' => __( 'Search', 'echo-knowledge-base' ),
				'toggle_value' => $this->is_setup_run_first_time ? 'search' : 'none',
				'toggle_options' => [
					'search'  => '<i class="epkbfa epkbfa-plus epkb-setup-wizard-module-row-toggle--on"></i>',
					'none'  => '<i class="epkbfa epkbfa-minus epkb-setup-wizard-module-row-toggle--off"></i>',
				],
				'tooltip_external_links' => [ [ 'link_text' => __( 'Learn More', 'echo-knowledge-base' ), 'link_url' => 'https://www.echoknowledgebase.com/documentation/search/' ] ]
			],
			'categories_articles'   => [
				'label' => __( 'Categories & Articles', 'echo-knowledge-base' ),
				'toggle_value' => $this->is_setup_run_first_time ? 'categories_articles' : 'none',
				'toggle_options' => [
					'categories_articles'  => '<i class="epkbfa epkbfa-plus epkb-setup-wizard-module-row-toggle--on"></i>',
					'none'  => '<i class="epkbfa epkbfa-minus epkb-setup-wizard-module-row-toggle--off"></i>',
				],
				'tooltip_external_links' => [ [ 'link_text' => __( 'Learn More', 'echo-knowledge-base' ), 'link_url' => 'https://www.echoknowledgebase.com/documentation/categories-and-articles/' ] ]
			],
			'articles_list'         => [
				'label' => __( 'Articles List', 'echo-knowledge-base' ),
				'toggle_value' => $this->is_setup_run_first_time ? 'articles_list' : 'none',
				'toggle_options' => [
					'articles_list'  => '<i class="epkbfa epkbfa-plus epkb-setup-wizard-module-row-toggle--on"></i>',
					'none'  => '<i class="epkbfa epkbfa-minus epkb-setup-wizard-module-row-toggle--off"></i>',
				],
				'tooltip_external_links' => [ [ 'link_text' => __( 'Learn More', 'echo-knowledge-base' ), 'link_url' => 'https://www.echoknowledgebase.com/documentation/articles-list/' ] ]
			],
			'faqs'                  => [
				'label' => __( 'FAQs', 'echo-knowledge-base' ),
				'toggle_value' => $this->is_setup_run_first_time ? 'faqs' : 'none',
				'toggle_options' => [
					'faqs'  => '<i class="epkbfa epkbfa-plus epkb-setup-wizard-module-row-toggle--on"></i>',
					'none'  => '<i class="epkbfa epkbfa-minus epkb-setup-wizard-module-row-toggle--off"></i>',
				],
				'tooltip_external_links' => [ [ 'link_text' => __( 'Learn More', 'echo-knowledge-base' ), 'link_url' => 'https://www.echoknowledgebase.com/documentation/faqs/' ] ]
			],
			'resource_links'        => [
				'label' => __( 'Resource Links', 'echo-knowledge-base' ),
				'toggle_value' => $this->is_setup_run_first_time && $this->elay_enabled ? 'resource_links' : 'none',
				'toggle_options' => [
					'resource_links'  => '<i class="epkbfa epkbfa-plus epkb-setup-wizard-module-row-toggle--on"></i>',
					'none'  => '<i class="epkbfa epkbfa-minus epkb-setup-wizard-module-row-toggle--off"></i>',
				],
				'tooltip_external_links' => [ [ 'link_text' => __( 'Learn More', 'echo-knowledge-base' ), 'link_url' => 'https://www.echoknowledgebase.com/documentation/resource-links/' ] ]
			],
		];

		// Do not check which modules are selected in KB Configuration for the first run of Setup Wizard
		if ( $this->is_setup_run_first_time ) {
			return $modules_config;
		}

		$selected_modules_config = [];

		// assign selected modules
		for ( $row_number = 1; $row_number <= 5; $row_number++ ) {

			$selected_module = $this->kb_config['ml_row_' . $row_number . '_module'];

			if ( empty( $modules_config[ $selected_module ] ) ) {
				continue;
			}

			if ( $selected_module == 'resource_links' && ! $this->elay_enabled ) {
				continue;
			}

			$selected_modules_config[ $selected_module ] = $modules_config[ $selected_module ];
			$selected_modules_config[ $selected_module ]['toggle_value'] = $selected_module;
			unset( $modules_config[ $selected_module ] );
		}

		// append unselected modules and return the full modules list
		return array_merge_recursive( $selected_modules_config, $modules_config );
	}
}