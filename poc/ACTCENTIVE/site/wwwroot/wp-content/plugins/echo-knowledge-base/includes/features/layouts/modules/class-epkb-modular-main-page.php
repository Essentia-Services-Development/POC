<?php

/**
 *  Outputs the Modular Main Page for knowledge base main page.
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class EPKB_Modular_Main_Page extends EPKB_Layout {

	const MAX_ROWS = 5;

	private $sidebar_layout_content;

	/**
	 * Generate content of the KB main page
	 */
	public function generate_kb_main_page() { ?>
		<div id="epkb-modular-main-page-container" role="main" aria-labelledby="Knowledge Base" class="epkb-css-full-reset <?php echo EPKB_Utilities::get_active_theme_classes( 'mp' ); ?>">			<?php
			$this->display_modular_container(); ?>
		</div>   <?php
	}

	/**
	 * Display KB Main Page content
	 */
	private function display_modular_container() {

		// show message that articles are coming soon if the current KB does not have any Category
		if ( ! $this->has_kb_categories ) {
			$this->show_categories_missing_message();
			return;
		}

		// display rows of the Modular Main Page
		for ( $row_number = 1; $row_number <= self::MAX_ROWS; $row_number ++ ) {

			$row_module = empty( $this->kb_config[ 'ml_row_' . $row_number . '_module' ] ) ? 'none' : $this->kb_config[ 'ml_row_' . $row_number . '_module' ];
			if ( $row_module == 'none' ) {
				continue;
			}   ?>

			<div id="epkb-ml__row-<?php echo esc_attr( $row_number ); ?>" class="epkb-ml__row">                <?php
				switch ( $row_module ) {

					// core modules
					case 'search':
						self::search_module( $this->kb_config );
						break;
					case 'categories_articles':
						$this->categories_articles_module();
						break;
					case 'articles_list':
						$this->articles_list_module();
						break;
					case 'faqs':
						$this->faqs_module();
						break;

					// add-on modules
					case 'resource_links':
						do_action( 'epkb_ml_' . $row_module . '_module', $this->kb_config );
						break;

					default:
						break;
				}   ?>
			</div>  <?php
		}
	}

	/**
	 * MODULE: Search
	 *
	 * @param $kb_config
	 */
	public static function search_module( $kb_config ) {
		global $eckb_is_kb_main_page;

		// Advanced Search uses its own search box
		if ( EPKB_Utilities::is_advanced_search_enabled( $kb_config ) ) {
			do_action( 'eckb_advanced_search_box', $kb_config );
			return;
		}

		$layout = empty( $eckb_is_kb_main_page ) ? $kb_config['ml_article_search_layout'] : $kb_config['ml_search_layout'];
		$search_handler = new EPKB_ML_Search( $kb_config ); ?>

		<div id="epkb-ml__module-search" class="epkb-ml__module">   <?php

			switch ( $layout ) {
				case 'modern':
				default:
					$search_handler->display_modern_search_layout();
					break;

				case 'classic':
					$search_handler->display_classic_search_layout();
					break;
			} ?>

		</div>  <?php
	}

	/**
	 * MODULE: Categories and Articles
	 */
	private function categories_articles_module() {

		$categories_articles_sidebar_class = '';
		if ( $this->kb_config['ml_categories_articles_sidebar_toggle'] == 'on' ) {
			$categories_articles_sidebar_class = 'epkb-ml-cat-article-sidebar--active';
		} ?>

		<div id="epkb-ml__module-categories-articles" class="epkb-ml__module <?php echo $categories_articles_sidebar_class; ?>">  <?php

			// Display Left Sidebar
			if ( $this->kb_config['ml_categories_articles_sidebar_toggle'] == 'on' && $this->kb_config['ml_categories_articles_sidebar_location'] == 'left' ) {
				$this->display_categories_articles_sidebar();
			}

			// let layout class display the KB main page
			$layout = empty( $this->kb_config['kb_main_page_layout'] ) ? EPKB_Layout::BASIC_LAYOUT : $this->kb_config['kb_main_page_layout'];
			$layout =  EPKB_Layouts_Setup::is_elay_layout( $layout ) && ! EPKB_Utilities::is_elegant_layouts_enabled() ? EPKB_Layout::BASIC_LAYOUT : $layout;

			// select core layout or default
			$handler = new EPKB_Layout_Basic();
			switch ( $layout ) {
				case EPKB_Layout::BASIC_LAYOUT:
				default:
					$handler = new EPKB_Layout_Basic();
					$layout = EPKB_Layout::BASIC_LAYOUT;  // default
					break;
				case EPKB_Layout::TABS_LAYOUT:
					$handler = new EPKB_Layout_Tabs();
					break;
				case EPKB_Layout::CATEGORIES_LAYOUT:
					$handler = new EPKB_Layout_Categories();
					break;
				case EPKB_Layout::CLASSIC_LAYOUT:
					$handler = new EPKB_Layout_Classic();
					break;
				case EPKB_Layout::DRILL_DOWN_LAYOUT:
					$handler = new EPKB_Layout_Drill_Down();
					break;
				case EPKB_Layout::GRID_LAYOUT:
				case EPKB_Layout::SIDEBAR_LAYOUT:
					break;
			}

			// generate layout
			$layout_output = '';

			// handle Elegant layouts
			if ( EPKB_Layouts_Setup::is_elay_layout( $layout ) ) {
				ob_start();
				if ( $layout == EPKB_Layout::SIDEBAR_LAYOUT ) {
					apply_filters( 'sidebar_display_categories_and_articles', $this->kb_config, $this->category_seq_data, $this->articles_seq_data, $this->sidebar_layout_content );
				} else {
					apply_filters(strtolower($layout) . '_display_categories_and_articles', $this->kb_config, $this->category_seq_data, $this->articles_seq_data );
				}
				$layout_output = ob_get_clean();
				if ( ! empty( $layout_output ) ) {
					echo $layout_output;
				}
			}

			// handle Core layouts and default
			if ( empty( $layout_output ) ) {
				$handler->display_categories_and_articles( $this->kb_config, $this->category_seq_data, $this->articles_seq_data );
			}

			// Display Right Sidebar
			if ( $this->kb_config['ml_categories_articles_sidebar_toggle'] == 'on' && $this->kb_config['ml_categories_articles_sidebar_location'] == 'right' ) {
				$this->display_categories_articles_sidebar();
			} ?>

		</div>    <?php
	}

	/**
	 * Categories & Articles Sidebar
	 */
	private function display_categories_articles_sidebar() {

		$sidebar_location = 'epkb-ml-sidebar--' . $this->kb_config['ml_categories_articles_sidebar_location']         ?>

		<div id="epkb-ml-cat-article-sidebar" class="<?php echo esc_attr( $sidebar_location ); ?>">			<?php

			// Sidebar Position 1
			switch ( $this->kb_config['ml_categories_articles_sidebar_position_1'] ) {

				case 'popular_articles':
					$this->display_sidebar_popular_articles();
					break;

				case 'newest_articles':
					$this->display_sidebar_newest_articles();
					break;

				case 'recent_articles':
					$this->display_sidebar_recent_articles();
					break;

				default: break;
			}

			// Sidebar Position 2
			switch ( $this->kb_config['ml_categories_articles_sidebar_position_2'] ) {

				case 'popular_articles':
					$this->display_sidebar_popular_articles();
					break;

				case 'newest_articles':
					$this->display_sidebar_newest_articles();
					break;

				case 'recent_articles':
					$this->display_sidebar_recent_articles();
					break;

				default: break;
			}   ?>

		</div>	<?php
	}

	/**
	 * Popular Articles list for Categories & Articles Sidebar
	 */
	private function display_sidebar_popular_articles() {

		$articles_list_handler = new EPKB_ML_Articles_List( $this->kb_config );
		$popular_articles = $articles_list_handler->execute_search( 'date' );    ?>

		<!-- Popular Articles -->
		<section id="epkb-ml-popular-articles" class="epkb-ml-article-section">
			<div class="epkb-ml-article-section__head"><?php echo esc_html( $this->kb_config['ml_articles_list_popular_articles_msg'] ); ?></div>
			<div class="epkb-ml-article-section__body">
				<ul class="epkb-ml-articles-list">  <?php
					if ( empty( $popular_articles) ) {   ?>
						<li class="epkb-ml-articles-coming-soon"><?php echo esc_html( $this->kb_config['category_empty_msg'] ); ?></li> <?php
					}
					foreach ( $popular_articles as $article ) { ?>
						<li><?php EPKB_Utilities::get_single_article_link( $this->kb_config, $article->post_title, $article->ID, 'Module' ); ?></li><?php
					}   ?>
				</ul>
			</div>
		</section>  <?php
	}

	/**
	 * Newest Articles list for Categories & Articles Sidebar
	 */
	private function display_sidebar_newest_articles() {

		$articles_list_handler = new EPKB_ML_Articles_List( $this->kb_config );
		$newest_articles = $articles_list_handler->execute_search( 'date' );    ?>

		<!-- Newest Articles -->
		<section id="epkb-ml-newest-articles" class="epkb-ml-article-section">
			<div class="epkb-ml-article-section__head"><?php echo esc_html( $this->kb_config['ml_articles_list_newest_articles_msg'] ); ?></div>
			<div class="epkb-ml-article-section__body">
				<ul class="epkb-ml-articles-list">  <?php
					if ( empty( $newest_articles) ) {   ?>
						<li class="epkb-ml-articles-coming-soon"><?php echo esc_html( $this->kb_config['category_empty_msg'] ); ?></li> <?php
					}
					foreach ( $newest_articles as $article ) { ?>
						<li><?php EPKB_Utilities::get_single_article_link( $this->kb_config, $article->post_title, $article->ID, 'Module' ); ?></li><?php
					}   ?>
				</ul>
			</div>
		</section>  <?php
	}

	/**
	 * Recent Articles list for Categories & Articles Sidebar
	 */
	private function display_sidebar_recent_articles() {

		$articles_list_handler = new EPKB_ML_Articles_List( $this->kb_config );
		$recent_articles = $articles_list_handler->execute_search( 'modified' );    ?>

		<!-- Recent Articles -->
		<section id="epkb-ml-recent-articles" class="epkb-ml-article-section">
			<div class="epkb-ml-article-section__head"><?php echo esc_html( $this->kb_config['ml_articles_list_recent_articles_msg'] ); ?></div>
			<div class="epkb-ml-article-section__body">
				<ul class="epkb-ml-articles-list">  <?php
					if ( empty( $recent_articles) ) {   ?>
						<li class="epkb-ml-articles-coming-soon"><?php echo esc_html( $this->kb_config['category_empty_msg'] ); ?></li> <?php
					}
					foreach ( $recent_articles as $article ) { ?>
						<li><?php EPKB_Utilities::get_single_article_link( $this->kb_config, $article->post_title, $article->ID, 'Module' ); ?></li><?php
					}   ?>
				</ul>
			</div>
		</section>			<?php
	}

	/**
	 * MODULE:  Articles List
	 */
	private function articles_list_module() { ?>
		<div id="epkb-ml__module-articles-list" class="epkb-ml__module">   <?php
			$articles_list_handler = new EPKB_ML_Articles_List( $this->kb_config );
			$articles_list_handler->display_articles_list(); ?>
		</div>  <?php
	}

	/**
	 * MODULE: FAQs
	 */
	private function faqs_module() { ?>
		<div id="epkb-ml__module-faqs" class="epkb-ml__module">   <?php
			$faqs_handler = new EPKB_ML_FAQs( $this->kb_config );
			$faqs_handler->display_faqs(); ?>
		</div>  <?php
	}

	/**
	 * Returns inline styles for Modular Main Page
	 *
	 * @param $kb_config
	 *
	 * @return string
	 */
	public static function get_all_inline_styles( $kb_config ) {

		$output = '
		/* CSS for Modular Main Page
		-----------------------------------------------------------------------*/';

		$output .= self::get_inline_styles( $kb_config );

		for ( $row_number = 1; $row_number <= self::MAX_ROWS; $row_number ++ ) {

			$row_module = empty( $kb_config[ 'ml_row_' . $row_number . '_module' ] ) ? 'none' : $kb_config[ 'ml_row_' . $row_number . '_module' ];
			if ( $row_module == 'none' ) {
				continue;
			}

			$output .= '
				#epkb-ml__row-' . $row_number . ' {
					max-width: ' . $kb_config['ml_row_' . $row_number . '_desktop_width'] . $kb_config['ml_row_' . $row_number . '_desktop_width_units'] . ';
				}';

			switch ( $kb_config[ 'ml_row_' . $row_number . '_module' ] ) {

				// CSS for Module: Search
				case 'search':
					$output .= EPKB_ML_Search::get_inline_styles( $kb_config );
					break;

				// CSS for Module: Categories & Articles MODULAR
				case 'categories_articles':
					switch ( $kb_config['kb_main_page_layout'] ) {
						default:
						case 'Basic':
							$output .= EPKB_Layout_Basic::get_inline_styles( $kb_config );
							break;
						case 'Tabs':
							$output .= EPKB_Layout_Tabs::get_inline_styles( $kb_config );
							break;
						case 'Categories':
							$output .= EPKB_Layout_Categories::get_inline_styles( $kb_config );
							break;
						case 'Classic':
							$output .= EPKB_Layout_Classic::get_inline_styles( $kb_config );
							break;
						case 'Drill-Down':
							$output .= EPKB_Layout_Drill_Down::get_inline_styles( $kb_config );
							break;
						case 'Grid':
							$output .= apply_filters( 'epkb_ml_grid_layout_styles', '', $kb_config );
							break;
					}
					break;

				// CSS for Module: Articles List
				case 'articles_list':
					$output .= EPKB_ML_Articles_List::get_inline_styles( $kb_config );
					break;

				// CSS for Module: FAQs
				case 'faqs':
					$output .= EPKB_ML_FAQs::get_inline_styles( $kb_config );
					break;

				// CSS for add-on modules
				case 'resource_links':
				 	$output .= apply_filters( 'epkb_ml_' . $row_module . '_module_styles', '', $kb_config );
					break;

				default:
					break;
			}
		}

		return $output;
	}

	private static function get_inline_styles( $kb_config ) {

		$output = '';

		// General Typography ----------------------------------------------/
		if ( ! empty( $kb_config['general_typography']['font-family'] ) ) {
			$output .= '
			#epkb-modular-main-page-container,
			#epkb-modular-main-page-container .epkb-category-section__head_title__text,
			#epkb-modular-main-page-container #epkb-ml-search-box .epkb-ml-search-box__input,
			#epkb-modular-main-page-container #epkb-ml-search-box .epkb-ml-search-box__text, {
			    ' . 'font-family:' . $kb_config['general_typography']['font-family'] . ' !important;' . '
			}';
		}

		// Sidebar ---------------------------------------------------------/
		if ( $kb_config['ml_categories_articles_sidebar_toggle'] == 'on' ) {

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

			$row_width = '';
			$row_units = '';

			// Find which Row the Categories Module is saved too.
			$module = '';
			foreach ( $kb_config as $key => $value ) {
				if ( $value === 'categories_articles' ) {
					$module = $key;
				}
			}

			// Get the Row Values based on which row the Category articles module has been assigned to.
			switch ( $module ) {
				case 'ml_row_1_module':
					$row_width = $kb_config['ml_row_1_desktop_width'];
					$row_units = $kb_config['ml_row_1_desktop_width_units'];
					break;
				case 'ml_row_2_module':
					$row_width = $kb_config['ml_row_2_desktop_width'];
					$row_units = $kb_config['ml_row_2_desktop_width_units'];
					break;
				case 'ml_row_3_module':
					$row_width = $kb_config['ml_row_3_desktop_width'];
					$row_units = $kb_config['ml_row_3_desktop_width_units'];
					break;
				case 'ml_row_4_module':
					$row_width = $kb_config['ml_row_4_desktop_width'];
					$row_units = $kb_config['ml_row_4_desktop_width_units'];
					break;
				case 'ml_row_5_module':
					$row_width = $kb_config['ml_row_5_desktop_width'];
					$row_units = $kb_config['ml_row_5_desktop_width_units'];
					break;
				default:
					break;
			}

			if ( $row_units == 'px' ) {
				$output .= '
				#epkb-ml__module-categories-articles .epkb-layout-container {
					width: ' . ( $row_width - $kb_config['ml_categories_articles_sidebar_desktop_width'] ) . $row_units . ';
				}
				#epkb-ml-cat-article-sidebar {
	                width: ' . $kb_config['ml_categories_articles_sidebar_desktop_width'] . $row_units . ';
				}';
			} else {
				$content_width_percent = ( ( $row_width - $kb_config['ml_categories_articles_sidebar_desktop_width'] ) / $row_width ) * 100;
				$sidebar_width_percent = ( $kb_config['ml_categories_articles_sidebar_desktop_width'] / $row_width ) * 100;
				$output .= '
				#epkb-ml__module-categories-articles .epkb-layout-container {
					width: ' . $content_width_percent . '%;
				}
				#epkb-ml-cat-article-sidebar {
	                width: ' . $sidebar_width_percent . '%;
				}';
			}

			// Use CSS Settings from Layout selected to match the styling.
			$shadow_setting_name = 'section_box_shadow';
			$background_setting_name = 'section_body_background_color';
			$border_setting_prefix = 'section_border';
			$head_font_setting_name = 'section_head_font_color';
			$head_typography_setting_name = 'section_head_typography';
			$article_typography_setting_name = 'article_typography';
			$article_font_setting_name = 'article_font_color';
			$article_icon_color_setting_name = 'article_icon_color';
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
						$article_icon_color_setting_name = 'sidebar_article_icon_color';
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
					default:
						break;
				}

				$container_background = 'background-color: ' . $kb_config[$background_setting_name] . ';';
			}

			$output .= '
			#epkb-ml__module-categories-articles #epkb-ml-cat-article-sidebar .epkb-ml-article-section {
				border-color: ' . $kb_config[$border_setting_prefix . '_color'] . ' !important;
				border-width: ' . $kb_config[$border_setting_prefix . '_width'] . 'px !important;
				border-radius: ' . $kb_config[$border_setting_prefix . '_radius'] . 'px !important;
				border-style: solid !important;' .
				$container_shadow .
				$container_background .
			'}';
			
			// Headings  -----------------------------------------/
			if ( in_array( $kb_config['kb_main_page_layout'], $legacy_layouts ) ) {
				if ( ! empty( $kb_config[$head_typography_setting_name]['font-size'] ) || ! empty( $kb_config[$head_typography_setting_name]['font-weight'] ) || ! empty( $kb_config[$head_typography_setting_name]['font-family'] ) ) {
					$output .= '#epkb-ml-cat-article-sidebar .epkb-ml-article-section__head {';
					if ( ! empty( $kb_config[$head_typography_setting_name]['font-size'] ) ) {
						$output .= 'font-size: ' . $kb_config[$head_typography_setting_name]['font-size'] . 'px !important;';
					}
					if ( ! empty( $kb_config[$head_typography_setting_name]['font-weight'] ) ) {
						$output .= 'font-weight: ' . $kb_config[$head_typography_setting_name]['font-weight'] . ' !important;';
					}
					if ( ! empty( $kb_config[$head_typography_setting_name]['font-family'] ) ) {
						$output .= 'font-family: ' . $kb_config[$head_typography_setting_name]['font-family'] . ' !important;';
					}
					$output .= '}';
				}
			}

			$output .= '
			#epkb-ml__module-categories-articles #epkb-ml-cat-article-sidebar .epkb-ml-article-section__head {
			        color: ' . $kb_config[$head_font_setting_name] . ' !important;
			    }';

			// Articles  -----------------------------------------/
			if ( in_array( $kb_config['kb_main_page_layout'], $legacy_layouts ) ) {
				if ( ! empty( $kb_config[$article_typography_setting_name]['font-size'] ) || ! empty( $kb_config[$article_typography_setting_name]['font-weight'] ) || ! empty( $kb_config[$article_typography_setting_name]['font-family'] ) ) {
					$output .= '#epkb-ml-cat-article-sidebar .epkb-article-inner {';
					if ( ! empty( $kb_config[$article_typography_setting_name]['font-size'] ) ) {
						$output .= 'font-size: ' . $kb_config[$article_typography_setting_name]['font-size'] . 'px !important;';
					}
					if ( ! empty( $kb_config[$article_typography_setting_name]['font-weight'] ) ) {
						$output .= 'font-weight: ' . $kb_config[$article_typography_setting_name]['font-weight'] . ' !important;';
					}
					if ( ! empty( $kb_config[$article_typography_setting_name]['font-family'] ) ) {
						$output .= 'font-family: ' . $kb_config[$article_typography_setting_name]['font-family'] . ' !important;';
					}
					$output .= '}';
				}
			}
			$output .= '
			#epkb-ml-cat-article-sidebar .epkb-article__text {
			    color: ' . $kb_config[$article_font_setting_name] . '; 
		    }
			#epkb-ml-cat-article-sidebar .epkb-article__icon {
			    color: ' . $kb_config[$article_icon_color_setting_name] . '; 
		    }';

			// Modular Sidebar -----------------------------------------/
			$output .= '
			#epkb-ml__module-categories-articles #epkb-ml-cat-article-sidebar .epkb-ml-articles-list li a {
			        padding-top: ' . $kb_config['article_list_spacing'] . 'px !important;
			        padding-bottom: ' . $kb_config['article_list_spacing'] . 'px !important;
		            line-height: 1 !important;
			    }';

		} // End of Sidebar Condition

		return $output;
	}

	public function set_sidebar_layout_content( $sidebar_layout_content) {
		$this->sidebar_layout_content = $sidebar_layout_content;
	}
}