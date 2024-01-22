<?php

/**
 *  Outputs the FAQs module for Modular Main Page.
 *
 * @copyright   Copyright (c) 2022, Echo Plugins
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class EPKB_ML_FAQs {

	const FAQS_KB_ID = 'epkb_ml_faqs_kb_id';
	const FAQS_CATEGORY_IDS = 'epkb_ml_faqs_category_ids';

	private $kb_config;

	private $faqs_kb_config;
	private $faqs_category_ids;
	private $faqs_category_seq_data;
	private $faqs_articles_seq_data;

	function __construct( $kb_config ) {
		$this->kb_config = $kb_config;

		// FAQs module can use Categories and Articles from another KB
		$faqs_kb_id = EPKB_Utilities::get_kb_option( $this->kb_config['id'], self::FAQS_KB_ID, null );
		if ( empty( $faqs_kb_id ) ) {
			return;
		}

		$this->faqs_kb_config = epkb_get_instance()->kb_config_obj->get_kb_config( $faqs_kb_id, true );
		if ( is_wp_error( $this->faqs_kb_config ) ) {
			return;
		}

		// Display categories and articles only from published KBs
		if ( $this->faqs_kb_config['status'] != 'published' ) {
			return;
        }

		$this->faqs_category_ids = EPKB_Utilities::get_kb_option( $this->kb_config['id'], self::FAQS_CATEGORY_IDS, array() );
		$this->faqs_category_seq_data = EPKB_Utilities::get_kb_option( $this->faqs_kb_config['id'], EPKB_Categories_Admin::KB_CATEGORIES_SEQ_META, array(), true );
		$this->faqs_articles_seq_data = EPKB_Utilities::get_kb_option( $this->faqs_kb_config['id'], EPKB_Articles_Admin::KB_ARTICLES_SEQ_META, array(), true );

		// for WPML filter categories and articles given active language
		if ( EPKB_Utilities::is_wpml_enabled( $this->faqs_kb_config ) ) {
			$this->faqs_category_seq_data = EPKB_WPML::apply_category_language_filter( $this->faqs_category_seq_data );
			$this->faqs_articles_seq_data = EPKB_WPML::apply_article_language_filter( $this->faqs_articles_seq_data );
		}
	}

	public function display_faqs() {

		// if no categories assigned then show message
		if ( empty( $this->faqs_category_ids ) || empty( $this->faqs_category_seq_data ) ) {
			$this->display_no_categories_assigned_message();
			return;
		}

		$stored_ids_obj = new EPKB_Categories_Array( $this->faqs_category_seq_data ); // normalizes the array as well
		$allowed_categories_ids = $stored_ids_obj->get_all_keys();

		// No categories found - message only for admins
		if ( empty( $allowed_categories_ids ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				esc_html_e( 'FAQs Module: No categories with articles found.', 'echo-knowledge-base' );
			}
			return;
		}

		// remove epkb filter
		remove_filter( 'the_content', array( 'EPKB_Layouts_Setup', 'get_kb_page_output_hook' ), 99999 );

		// init FAQ schema json
		$faq_schema_json = [
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => [],
		];  ?>

		<div id="epkb-ml-faqs-<?php echo strtolower( $this->kb_config['kb_main_page_layout'] ); ?>-layout" class="epkb-ml-faqs-container <?php echo esc_html( $this->kb_config['ml_faqs_custom_css_class'] ); ?>">  <?php

			if ( ! empty( $this->kb_config['ml_faqs_title_text'] ) ) {  ?>
				<h2 class="epkb-ml-faqs__title"><?php echo esc_html( $this->kb_config['ml_faqs_title_text'] ); ?></h2>  <?php
			}   ?>

			<div class="epkb-ml-faqs__row"> <?php

				foreach( $this->faqs_category_ids as $selected_category_id ) {

					if ( empty( $this->faqs_articles_seq_data[$selected_category_id] ) ) {
						continue;
					}

					if ( empty( $allowed_categories_ids[$selected_category_id] ) ) {
						continue;
					}

					foreach ( $this->faqs_articles_seq_data[$selected_category_id] as $article_id => $article_title ) {

						// category title/description
						if ( $article_id == 0 || $article_id == 1 ) {
							continue;
						}

						// exclude linked articles
						$article = get_post( $article_id );

						// disallow article that failed to retrieve
						if ( empty( $article ) || empty( $article->post_status ) ) {
							unset( $this->faqs_articles_seq_data[$selected_category_id][$article_id] );
							continue;
						}

						if ( EPKB_Utilities::is_link_editor( $article ) ) {
							unset( $this->faqs_articles_seq_data[$selected_category_id][$article_id] );
							continue;
						}

						// exclude not allowed
						if ( ! EPKB_Utilities::is_article_allowed_for_current_user( $article_id ) ) {
							unset( $this->faqs_articles_seq_data[$selected_category_id][$article_id] );
						}
					}

					// not empty term but with hidden articles for the user
					if ( empty( $this->faqs_articles_seq_data[$selected_category_id] ) ) {
						continue;
					}   ?>

					<div class="epkb-ml-faqs-cat-container" id="epkb-ml-faqs-cat-<?php echo esc_attr( $selected_category_id ); ?>">
						<div class="epkb-ml-faqs__cat-header">
							<h3><?php echo esc_html( $this->faqs_articles_seq_data[$selected_category_id][0] ); ?></h3>
						</div>  <?php

						foreach( $this->faqs_articles_seq_data[$selected_category_id] as $article_id => $article_title ) {

							if ( $article_id == 0 || $article_id == 1 ) {
								continue;
							}

							// second call is cached by wp core, will not create db query
							$article = get_post( $article_id );

							// disallow article that failed to retrieve
							if ( empty( $article ) || empty( $article->post_status ) ) {
								continue;
							}

							// ignore password-protected pages
							if ( ! empty( $article->post_password ) ) {
								continue;
							}

							$post_content = '';
							if ( $this->kb_config['ml_faqs_content_mode'] == 'content' ) {
								$post_content = $article->post_content;
							}

							if ( $this->kb_config['ml_faqs_content_mode'] == 'excerpt' ) {
								$post_content = $article->post_excerpt;
							}

	                        // add article title and content to the FAQ schema
							if ( $this->kb_config['faq_schema_toggle'] == 'on' ) {
								$faq_schema_json['mainEntity'][] = array(
									'@type' => 'Question',
									'name' => get_the_title( $article ),
									'acceptedAnswer' => array(
										'@type' => 'Answer',
										'text' => wp_strip_all_tags( $post_content ),
									)
								);
							}   ?>

							<div class="epkb-ml-faqs__item-container" id="epkb-ml-faqs-article-<?php echo $article->ID; ?>">
								<div class="epkb-ml-faqs__item__question">
									<div class="epkb-ml-faqs__item__question__icon epkbfa epkbfa-plus-square"></div>
									<div class="epkb-ml-faqs__item__question__icon epkbfa epkbfa-minus-square"></div>
									<div class="epkb-ml-faqs__item__question__text"><?php echo get_the_title( $article ); ?></div>
								</div>
								<div class="epkb-ml-faqs__item__answer">
									<div class="epkb-ml-faqs__item__answer__text">    <?php
										$content = apply_filters( 'the_content', $post_content );
										$content = str_replace( ']]>', ']]&gt;', $content );
										echo $content;  ?>
									</div>
								</div>
							</div>  <?php
						}   ?>
					</div>  <?php
				}   ?>

			</div>  <?php

			if ( $this->kb_config['faq_schema_toggle'] == 'on' ) {    ?>
				<script type="application/ld+json"><?php echo wp_json_encode( $faq_schema_json ); ?></script>   <?php
			}   ?>
		</div>  <?php

		// add epkb filter back
		add_filter( 'the_content', array( 'EPKB_Layouts_Setup', 'get_kb_page_output_hook' ), 99999 );
	}

	/**
	 * Display message for users with access to FAQs Module settings
	 */
	private function display_no_categories_assigned_message() {

		// only users with at least Editor access can see the message - if WPML enabled, then show message only for original KB Main Page
		if ( ! EPKB_Admin_UI_Access::is_user_access_to_context_allowed( 'admin_eckb_access_frontend_editor_write' ) || ! EPKB_PLL::is_original_language_page( $this->kb_config ) ) {
			return;
		}

		// enqueue custom CSS file here that normally never used if FAQs Module has any category assigned
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_register_style( 'epkb-mp-custom-faqs-not-assigned', Echo_Knowledge_Base::$plugin_url . 'css/mp-custom-faqs-not-assigned' . $suffix . '.css', array(), Echo_Knowledge_Base::$version );
		wp_print_styles( array( 'epkb-mp-custom-faqs-not-assigned' ) );    ?>

		<section id="eckb-kb-faqs-not-assigned">
			<h2 class="eckb-kb-faqs-not-assigned-title"><?php esc_html_e( 'You do not have any KB categories assigned for the FAQs module.', 'echo-knowledge-base' ); ?></h2>
			<div class="eckb-kb-faqs-not-assigned-body">
				<p>
					<a class="eckb-kb-faqs-not-assigned-btn" href="<?php echo esc_url( admin_url( 'edit.php?post_type=epkb_post_type_' . $this->kb_config['id'] . '&page=epkb-kb-configuration#settings__main-page' ) ); ?>"><?php esc_html_e( 'Assign category for the FAQs module.', 'echo-knowledge-base' ); ?></a>
				</p>
				<p><?php esc_html_e( 'To find out more about the FAQs Module', 'echo-knowledge-base' ); ?>, <a href="https://www.echoknowledgebase.com/documentation/modular-layout/#FAQs-Module" target="_blank"><?php esc_html_e( 'click here.', 'echo-knowledge-base' ); ?>  </a></p>
			</div>
			<div class="eckb-kb-faqs-not-assigned-footer">
				<p>
					<span><?php esc_html_e( 'If you need help, please contact us', 'echo-knowledge-base' ); ?></span>
					<a href="https://www.echoknowledgebase.com/technical-support/" target="_blank"> <?php esc_html_e( 'here', 'echo-knowledge-base' ); ?></a>
				</p>
			</div>
		</section>  <?php
	}

	/**
	 * Returns inline styles for FAQs Module
	 *
	 * @param $kb_config
	 * @return string
	 */
	public static function get_inline_styles( $kb_config ) {

		/*
		 * Legacy Layouts that have specific settings
		 */
		$legacy_layouts = [
			EPKB_Layout::BASIC_LAYOUT,
			EPKB_Layout::TABS_LAYOUT,
			EPKB_Layout::CATEGORIES_LAYOUT,
			EPKB_Layout::SIDEBAR_LAYOUT,
			EPKB_Layout::GRID_LAYOUT,
		];

		// Use CSS Settings from Layout selected to match the styling.
		$output = '';
		$shadow_setting_name = 'section_box_shadow';
		$background_setting_name = 'section_body_background_color';
		$border_setting_prefix = 'section_border';
		$head_font_setting_name = 'section_head_font_color';
		$head_typography_setting_name = 'section_head_typography';
		$article_typography_setting_name = 'article_typography';
		$article_font_setting_name = 'article_font_color';
		if ( EPKB_Utilities::is_elegant_layouts_enabled() ) {
			switch ( $kb_config['kb_main_page_layout'] ) {
				case EPKB_Layout::GRID_LAYOUT:
					$shadow_setting_name = 'grid_section_box_shadow';
					$background_setting_name = 'grid_section_body_background_color';
					$border_setting_prefix = 'grid_section_border';
					$head_font_setting_name = 'grid_section_head_font_color';
					$head_typography_setting_name = 'grid_section_typography';
					$article_font_setting_name = 'grid_section_body_text_color';
					break;
				case EPKB_Layout::SIDEBAR_LAYOUT:
					$shadow_setting_name = 'sidebar_section_box_shadow';
					$background_setting_name = 'sidebar_background_color';
					$border_setting_prefix = 'sidebar_section_border';
					$head_font_setting_name = 'sidebar_section_head_font_color';
					$head_typography_setting_name = 'sidebar_section_category_typography';
					$article_typography_setting_name = 'sidebar_section_body_typography';
					$article_font_setting_name = 'sidebar_article_font_color';
					break;
				default: break;
			}
		}

		// Container -----------------------------------------/
		$container_shadow = '';
		$container_background = '';
		if ( in_array( $kb_config['kb_main_page_layout'], $legacy_layouts ) ) {

			switch ( $kb_config[$shadow_setting_name] ) {
				case 'section_light_shadow':
					$container_shadow = 'box-shadow: 0px 3px 20px -10px rgba(0, 0, 0, 0.75);';
					break;
				case 'section_medium_shadow':
					$container_shadow = 'box-shadow: 0px 3px 20px -4px rgba(0, 0, 0, 0.75);';
					break;
				case 'section_bottom_shadow':
					$container_shadow = 'box-shadow: 0 2px 0 0 #E1E1E1;';
					break;
				default: break;
			}

			$container_background = 'background-color: ' . $kb_config[$background_setting_name] . ';';
		}

		if ( ! empty( $kb_config['general_typography']['font-family'] ) ) {
			$output .= '
			#epkb-ml__module-faqs .epkb-ml-faqs__title {
				    ' . 'font-family:' . $kb_config['general_typography']['font-family'] . ' !important;' . '
				}';
		}

		$output .= '
		#epkb-ml__module-faqs .epkb-ml-faqs-cat-container {
			border-color: ' . $kb_config[$border_setting_prefix . '_color'] . ' !important;
			border-width: ' . $kb_config[$border_setting_prefix . '_width'] . 'px !important;
			border-radius: ' . $kb_config[$border_setting_prefix . '_radius'] . 'px !important;
			border-style: solid !important;' .
			$container_shadow .
			$container_background .
		'}';

		// Headings Typography -----------------------------------------/
		if ( in_array( $kb_config['kb_main_page_layout'], $legacy_layouts ) ) {
			if ( ! empty( $kb_config[$head_typography_setting_name]['font-size'] ) || ! empty( $kb_config[$head_typography_setting_name]['font-weight'] ) || ! empty( $kb_config[$head_typography_setting_name]['font-family'] ) ) {
				$output .= '
				.epkb-ml-faqs-cat-container .epkb-ml-faqs__cat-header h3 {
				    ' . ( empty( $kb_config[$head_typography_setting_name]['font-size'] ) ? '' : 'font-size:' . $kb_config[$head_typography_setting_name]['font-size'] . 'px !important;' ) . '
				    ' . ( empty( $kb_config[$head_typography_setting_name]['font-weight'] ) ? '' : 'font-weight:' . $kb_config[$head_typography_setting_name]['font-weight'] . '!important;' ) . '
				    ' . ( empty( $kb_config[$head_typography_setting_name]['font-family'] ) ? '' : 'font-family:' . $kb_config[$head_typography_setting_name]['font-family'] . '!important;' ) . '
			    }';
			}
		}

		// Articles Typography -----------------------------------------/
		if ( in_array( $kb_config['kb_main_page_layout'], $legacy_layouts ) ) {
			if ( ! empty( $kb_config[$article_typography_setting_name]['font-size'] ) || ! empty( $kb_config[$article_typography_setting_name]['font-weight'] ) || ! empty( $kb_config[$article_typography_setting_name]['font-family'] ) ) {
				$output .= '
				#epkb-ml__module-faqs .epkb-ml-faqs__item__question .epkb-ml-faqs__item__question__text {
				    ' . ( empty( $kb_config[$article_typography_setting_name]['font-size'] ) ? '' : 'font-size:' . $kb_config[$article_typography_setting_name]['font-size'] . 'px !important;' ) . '
				    ' . ( empty( $kb_config[$article_typography_setting_name]['font-weight'] ) ? '' : 'font-weight:' . $kb_config[$article_typography_setting_name]['font-weight'] . '!important;' ) . '
				    ' . ( empty( $kb_config[$article_typography_setting_name]['font-family'] ) ? '' : 'font-family:' . $kb_config[$article_typography_setting_name]['font-family'] . '!important;' ) . '
			    }';
			}
		}
		$output .= '
		    #epkb-ml__module-faqs .epkb-ml-faqs__item-container {
		        padding-top: ' . $kb_config['article_list_spacing'] . 'px !important;
		        padding-bottom: ' . $kb_config['article_list_spacing'] . 'px !important;
		        line-height: 1 !important;
		    }';

        $output .= '
        #epkb-ml__module-faqs .epkb-ml-faqs__cat-header h3 { 
	        color: ' . $kb_config[$head_font_setting_name] . ';
        }
        #epkb-ml__module-faqs .epkb-ml-faqs__item__question .epkb-ml-faqs__item__question__text {
            color: ' . $kb_config[$article_font_setting_name] . ';
        }';

		return $output;
	}
}