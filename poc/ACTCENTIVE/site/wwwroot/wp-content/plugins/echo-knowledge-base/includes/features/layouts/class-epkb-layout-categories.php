<?php

/**
 *  Outputs the Category Focused Layout for the knowledge base main page.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class EPKB_Layout_Categories extends EPKB_Layout {

	/**
	 * Display Categories and Articles module content for KB Main Page
	 *
	 * @param $kb_config
	 * @param $category_seq_data
	 * @param $articles_seq_data
	 */
	public function display_categories_and_articles( $kb_config, $category_seq_data, $articles_seq_data ) {
		$this->kb_config = $kb_config;
		$this->category_seq_data = $category_seq_data;
		$this->articles_seq_data = $articles_seq_data;      ?>

		<div id="epkb-ml-categories-layout" role="main" aria-labelledby="Knowledge Base" class="epkb-layout-container epkb-css-full-reset epkb-categories-template">
			<div id="epkb-content-container">
				<div class="epkb-section-container">	<?php
					$this->display_main_page_content(); ?>
				</div>
			</div>
		</div>   <?php
	}

    /**
	 * Generate content of the KB main page
	 */
	public function generate_kb_main_page() {

		$class2 = $this->get_css_class( '::width' );		    ?>

		<div id="epkb-main-page-container" role="main" aria-labelledby="Knowledge Base" class="epkb-css-full-reset epkb-categories-template <?php echo EPKB_Utilities::get_active_theme_classes( 'mp' ); ?>">
			<div <?php echo $class2; ?>>  <?php

				//  KB Search form
				$this->get_search_form();

				//  Knowledge Base Layout
				$style1 = $this->get_inline_style( 'background-color:: background_color' );				?>
				<div id="epkb-content-container" <?php echo $style1; ?> >

					<!--  Main Page Content -->
					<div class="epkb-section-container">	<?php
						$this->display_main_page_content(); ?>
					</div>

				</div>
			</div>
		</div>   <?php
	}

	/**
	 * Display KB Main Page content
	 */
	private function display_main_page_content() {

		// show message that articles are coming soon if the current KB does not have any Category
		if ( ! $this->has_kb_categories ) {
			$this->show_categories_missing_message();
			return;
		}

		$class0 = $this->get_css_class('::section_box_shadow, epkb-top-category-box');
		$style0 = $this->get_inline_style( 
					'border-radius:: section_border_radius,
					 border-width:: section_border_width,
					 border-color:: section_border_color,
					 background-color:: section_body_background_color, border-style: solid' );

		$class_section_head = $this->get_css_class( 'section-head' . ( $this->kb_config[ 'section_divider' ] == 'on' ? ', section_divider' : '' ) );
		$style_section_head = $this->get_inline_style(
					'border-bottom-width:: section_divider_thickness,
					background-color:: section_head_background_color, 
					border-top-left-radius:: section_border_radius,
					border-top-right-radius:: section_border_radius,
					border-bottom-color:: section_divider_color,
					padding-top:: section_head_padding_top,
					padding-bottom:: section_head_padding_bottom,
					padding-left:: section_head_padding_left,
					padding-right:: section_head_padding_right'
		);
		$style3 = $this->get_inline_style(
					'color:: section_head_font_color'
		);
		
		$style31 = $this->get_inline_style(
					'color:: section_head_font_color,
			 		typography:: section_head_typography'
		);
		$style4 = $this->get_inline_style(
					'color:: section_head_description_font_color,
					 text-align::section_head_alignment,
					 typography:: section_head_description_typography'
		);
		$style5 = 'border-bottom-width:: section_border_width,
					padding-top::    section_body_padding_top,
					padding-bottom:: section_body_padding_bottom,
					padding-left::   section_body_padding_left,
					padding-right::  section_body_padding_right,
					';

		if ( $this->kb_config['section_box_height_mode'] == 'section_min_height' ) {
			$style5 .= 'min-height:: section_body_height';
		} else if ( $this->kb_config['section_box_height_mode'] == 'section_fixed_height' ) {
			$style5 .= 'overflow: auto, height:: section_body_height';
		}

		// for each CATEGORY display: a) its articles and b) top-level SUB-CATEGORIES with its articles

		$categories_icons = $this->get_category_icons();

		$header_icon_style = $this->get_inline_style( 'color:: section_head_category_icon_color, font-size:: section_head_category_icon_size' );
		$header_image_style = $this->get_inline_style( 'max-height:: section_head_category_icon_size' );

		$icon_location = empty($this->kb_config['section_head_category_icon_location']) ? '' : $this->kb_config['section_head_category_icon_location'];

		$top_icon_class = 'epkb-category-level-1--icon-loc-' . $icon_location;
		$alignmentClass = 'epkb-category-level-1--alignment-' . $this->kb_config['section_head_alignment'];

		//Count Styling
		$count_background_color     = '#FFFFFF ';    //TODO Get KB setting
		$count_text_color           = '#000000';     //TODO Get KB setting
		$count_border_color         = '#CCCCCC';     //TODO Get KB setting      ?>

		<style><?php
            ob_start(); ?>
			.epkb-cat-count {
				color:<?php echo $count_text_color; ?> !important;
				background-color:<?php echo $count_background_color; ?> !important;
				border:solid 1px <?php echo $count_border_color; ?> !important;
			}   <?php
			$inline_styles = ob_get_clean();
			echo EPKB_Utilities::minify_css( $inline_styles );
		?></style>      <?php

		switch ( $this->kb_config['nof_columns'] ) {
			case 'one-col':
				$categories_per_row = 1;
				break;
			case 'two-col':
			default:
				$categories_per_row = 2;
				break;
			case 'three-col':
				$categories_per_row = 3;
				break;
			case 'four-col':
				$categories_per_row = 4;
				break;
		}   ?>

		<div class="<?php echo empty( $this->kb_config['nof_columns'] ) ? '' : 'epkb-' . $this->kb_config['nof_columns']; ?> eckb-categories-list" > <?php

			/** DISPLAY BOXED CATEGORIES */
			$category_number = 0;
			$column_index = 1;
			$loop_index = 1;
			$is_modular = $this->kb_config['modular_main_page_toggle'] == 'on';
			foreach ( $this->category_seq_data as $box_category_id => $box_sub_categories ) {
				$category_number++;

				$category_name = isset( $this->articles_seq_data[$box_category_id][0] ) ? $this->articles_seq_data[$box_category_id][0] : '';
				if ( empty( $category_name ) ) {
					continue;
				}

				$category_icon = EPKB_KB_Config_Category::get_category_icon( $box_category_id, $categories_icons );
				$category_desc = isset($this->articles_seq_data[$box_category_id][1]) && $this->kb_config['section_desc_text_on'] == 'on' ? $this->articles_seq_data[$box_category_id][1] : '';
				$box_sub_categories = is_array($box_sub_categories) ? $box_sub_categories : array();
				$box_category_data = $this->is_builder_on ? 'data-kb-category-id=' . $box_category_id . ' data-kb-type=category ' : '';
				$category_count = EPKB_Categories_DB::get_category_count( $this->kb_config['id'] , $box_category_id );

				if ( $is_modular && $column_index == 1 ) { ?>
					<div class="epkb-ml__module-categories-articles__row">  <?php
				}   ?>

				<!-- Section Container ( Category Box ) -->
				<section id="<?php echo 'epkb_cat_' . $category_number; ?>" <?php echo $class0 . ' ' . $style0; ?> >

					<!-- Section Head -->
					<div <?php echo $class_section_head . ' ' . $style_section_head; ?> >

						<!-- Category Name + Icon -->
						<div class="epkb-category-level-1 <?php echo $top_icon_class . ' ' . $alignmentClass; ?>" <?php echo $box_category_data . ' ' . $style3; ?> >

							<!-- Icon Top / Left -->	                            <?php
							if ( in_array( $icon_location, array('left', 'top') ) ) {

								if ( $category_icon['type'] == 'image' ) { ?>
									<img class="epkb-cat-icon epkb-cat-icon--image "
									     src="<?php echo esc_url($category_icon['image_thumbnail_url']); ?>" alt="<?php echo $category_icon['image_alt']; ?>"
										<?php echo $header_image_style; ?>
									>								<?php
								} else { ?>
									<span class="epkb-cat-icon epkbfa <?php echo esc_attr( $category_icon['name'] ); ?>" data-kb-category-icon="<?php echo esc_attr( $category_icon['name'] ); ?>" <?php echo $header_icon_style; ?>></span>	<?php
								}
							}

							// Category name							  ?>
							<span class="epkb-cat-name">    <?php

								if ( $this->kb_config['section_hyperlink_on'] === 'on' ) {
									$category_link = EPKB_Utilities::get_term_url( $box_category_id );      ?>
									<a class="epkb-cat-name-count-container" href="<?php echo esc_url( $category_link ); ?>" <?php echo $style31; ?>>
										<h2 class="epkb-cat-name"><?php echo esc_html( $category_name ); ?></h2>
										<span class="epkb-cat-count"><?php echo $category_count; ?></span>
									</a>    <?php
								} else {   ?>
									<span class="epkb-cat-name-count-container" <?php echo $style31; ?>>
										<h2 class="epkb-cat-name"><?php echo esc_html( $category_name ); ?></h2>
										<span class="epkb-cat-count"><?php echo $category_count; ?></span>
									</span> <?php
								}   ?>

							</span>

							<!-- Icon Right -->     <?php
							if ( $icon_location == 'right' ) {

								if ( $category_icon['type'] == 'image' ) { ?>
									<img class="epkb-cat-icon epkb-cat-icon--image"
									     src="<?php echo esc_url($category_icon['image_thumbnail_url']); ?>" alt="<?php echo $category_icon['image_alt']; ?>"
										<?php echo $header_image_style; ?>
									>								<?php
								} else { ?>
									<span class="epkb-cat-icon epkbfa <?php echo esc_attr( $category_icon['name'] ); ?>" data-kb-category-icon="<?php echo esc_attr( $category_icon['name'] ); ?>" <?php echo $header_icon_style; ?>></span>	<?php
								}
							}       ?>

						</div>

						<!-- Category Description -->						<?php
						if ( $category_desc ) {   ?>
						    <p class="epkb-cat-desc" <?php echo $style4; ?> >
						        <?php echo $category_desc; ?>
						    </p>						<?php
						}       ?>
					</div>

					<!-- Section Body -->
					<div class="epkb-section-body" <?php echo $this->get_inline_style( $style5 ); ?> >						<?php 
						
						/** DISPLAY TOP-CATEGORY ARTICLES LIST */
						if (  $this->kb_config['show_articles_before_categories'] != 'off' ) {
							$this->display_articles_list( 1, $box_category_id, ! empty($box_sub_categories) );
						}
						
						if ( ! empty($box_sub_categories) ) {
							$this->display_box_sub_categories( $box_sub_categories, $categories_icons );
						}
						
						/** DISPLAY TOP-CATEGORY ARTICLES LIST */
						if (  $this->kb_config['show_articles_before_categories'] == 'off' ) {
							$this->display_articles_list( 1, $box_category_id, ! empty($box_sub_categories) );
						}                      ?>

					</div><!-- Section Body End -->

				</section><!-- Section End -->  <?php

				if ( $is_modular && ( $column_index == $categories_per_row || $loop_index == count( $this->category_seq_data ) ) ) {     ?>
					</div>  <?php
					$column_index = 0;
				}

				$column_index ++;
				$loop_index ++;
			}  ?>

		</div>       <?php
	}

	/**
	 * Display categories within the Box i.e. sub-sub-categories
	 *
	 * @param $box_sub_category_list
	 * @param $categories_icons
	 */
	private function display_box_sub_categories( $box_sub_category_list, $categories_icons ) {     	?>

		<ul class="epkb-sub-category eckb-sub-category-ordering"> <?php

			/** DISPLAY SUB-CATEGORIES */
			foreach ( $box_sub_category_list as $box_sub_category_id => $box_sub_sub_category_list ) {

				$category_count = EPKB_Categories_DB::get_category_count( $this->kb_config['id'], $box_sub_category_id );

				$category_name = isset($this->articles_seq_data[$box_sub_category_id][0]) ?
											$this->articles_seq_data[$box_sub_category_id][0] : _x( 'Category', 'taxonomy singular name' );

				$default_icon_name = $this->kb_config['expand_articles_icon'];
				$category_icon = EPKB_KB_Config_Category::get_category_icon( $box_sub_category_id, $categories_icons, $default_icon_name );
				$style1 = $this->get_inline_style( 'color:: section_category_icon_color' );
				$style2 = $this->get_inline_style( 'color:: section_category_font_color' );
				
				$box_sub_category_data = $this->is_builder_on ? 'data-kb-category-id=' . $box_sub_category_id  . ' data-kb-type=sub-category ' : '';  	?>

				<li <?php echo $this->get_inline_style( 'padding-bottom:: article_list_spacing,padding-top::article_list_spacing' ); ?>>
					<div class="epkb-category-level-2-3 epkb-category-focused" <?php echo $box_sub_category_data; ?>>

						<span class="epkb-category-level-2-3__cat-icon epkbfa <?php echo esc_attr( $category_icon['name'] ); ?>" <?php echo $style1; ?>></span>					<?php

				        // Get the URL of this category
				        $sub_category_link = EPKB_Utilities::get_term_url( $box_sub_category_id );      ?>
						<span class="epkb-category-level-2-3__cat-name" tabindex="0">
							<a href="<?php echo esc_url( $sub_category_link ); ?>" <?php echo $style2; ?>>
								<h3 class="epkb-category-level-2-3__cat-name_text"><?php echo esc_html( $category_name ); ?></h3>
								<span class="epkb-cat-count"><?php echo $category_count; ?></span>
							</a>
						</span>

					</div>                    <?php

					//Sequence number calculation :: START

					/** SUB-SUB-CATEGORIES */
					if ( ! empty( $box_sub_sub_category_list ) ) {
						$this->adjust_article_sub_sub_categories_seq( $box_sub_sub_category_list );
					}

					//Sequence number calculation :: END                    ?>
				</li>  <?php
			}           ?>

		</ul> <?php
	}

	/**
	 * Display list of articles that belong to given subcategory
	 *
	 * @param $level
	 * @param $category_id
	 * @param bool $sub_category_exists - if true then we don't want to show "Articles coming soon" if there are no articles because
	 *                                   we have at least categories listed. But sub-category should always have that message if no article present
	 * @param string $sub_sub_string
	 */
	private function display_articles_list( $level, $category_id, $sub_category_exists=false, $sub_sub_string = '' ) {

		// retrieve articles belonging to given (sub) category if any
		$articles_list = array();
		if ( isset($this->articles_seq_data[$category_id]) ) {
			$articles_list = $this->articles_seq_data[$category_id];
			unset($articles_list[0]);
			unset($articles_list[1]);
		}

		// return if we have no articles and will not show 'Articles coming soon' message
		$articles_coming_soon_msg = $this->kb_config['category_empty_msg'];
		if ( empty($articles_list) && ( $sub_category_exists || empty($articles_coming_soon_msg) ) ) {
			return;
		}

		$sub_category_styles = is_rtl() ? 'padding-right:: article_list_margin' : 'padding-left:: article_list_margin';
		if ( $level == 1 ) {
			$data_kb_type = 'article';
			$sub_category_styles = '';
		} else if ( $level == 2 ) {
			$data_kb_type = 'sub-article';
		} else {
			$data_kb_type = empty( $sub_sub_string ) ? 'sub-sub-article' : $sub_sub_string . 'article';
		}

		$style = 'class="' . ( $level == 1 ? 'epkb-main-category ' : '' ) .  'epkb-articles"';		?>

		<ul <?php echo $style . ' ' . $this->get_inline_style( $sub_category_styles ); ?>> <?php

			$article_num = 0;
			$nof_articles_displayed = $this->kb_config['nof_articles_displayed'];
			foreach ( $articles_list as $article_id => $article_title ) {

				if ( ! EPKB_Utilities::is_article_allowed_for_current_user( $article_id ) ) {
					continue;
				}

				$article_num++;
				$hide_class = $article_num > $nof_articles_displayed ? 'epkb-hide-elem' : '';
				$article_data = $this->is_builder_on ? 'data-kb-article-id=' . $article_id . ' data-kb-type=' . $data_kb_type : '';

				/** DISPLAY ARTICLE LINK */         ?>
				<li class="epkb-article-level-<?php echo $level . ' ' . $hide_class; ?>" <?php echo $article_data; ?> <?php echo $this->get_inline_style( 'padding-bottom:: article_list_spacing,padding-top::article_list_spacing' ); ?> >   <?php
								$this->single_article_link( $article_title, $article_id, EPKB_Layout::CATEGORIES_LAYOUT); ?>
				</li> <?php
			}

			// if article list is longer than initial article list size then show expand/collapse message
			if ( $article_num > $nof_articles_displayed ) { ?>
				<button class="epkb-show-all-articles" aria-expanded="false">
					<span class="epkb-show-text">
						<?php echo esc_html( $this->kb_config['show_all_articles_msg'] ) . ' ( ' . ( $article_num - $nof_articles_displayed ); ?> )
					</span>
					<span class="epkb-hide-text epkb-hide-elem"><?php echo esc_html( $this->kb_config['collapse_articles_msg'] ); ?></span>
				</button>					<?php
			}

			if ( $article_num == 0 ) {
				echo '<li class="epkb-articles-coming-soon">' . esc_html( $articles_coming_soon_msg ) . '</li>';
			} ?>

		</ul> <?php
	}

	/**
	* Set Article sub-sub-categories Sequence No
	*
	* @param $box_sub_sub_category_list
	* @param string $level
	*/
	private function adjust_article_sub_sub_categories_seq( $box_sub_sub_category_list, $level = 'sub-' ) {

		$level .= 'sub-';
		/** SUB-SUB-CATEGORIES */
		foreach ( $box_sub_sub_category_list as $box_sub_sub_category_id => $box_sub_sub_sub_category_list ) {

		   /** RECURSION DISPLAY SUB-SUB-...-CATEGORIES */
		   if ( ! empty( $box_sub_sub_sub_category_list ) && strlen($level) < 20 ) {
		       $this->adjust_article_sub_sub_categories_seq( $box_sub_sub_sub_category_list, $level );
		   }
		}
	}

	/**
	 * Returns inline styles for Categories & Articles Module
	 *
	 * @param $kb_config
	 *
	 * @return string
	 */
	public static function get_inline_styles( $kb_config ) {

		$output = '';

		// General -------------------------------------------/
		if ( !empty( $kb_config['background_color'] ) ) {
			$output .= '
			#epkb-content-container {
				padding: 20px!important;
				background-color: ' . $kb_config['background_color'] . '!important;
			}';
		}

		// Container -----------------------------------------/
		if ( ! empty( $kb_config['section_typography']['font-family'] ) || ! empty( $kb_config['section_typography']['font-weight'] ) || ! empty( $kb_config['section_typography']['font-size'] ) ) {
			$output .= '
			#epkb-content-container .epkb-category-level-2-3__cat-name_text {
			    ' . ( empty( $kb_config['section_typography']['font-family'] ) ? '' : 'font-family: ' . $kb_config['section_typography']['font-family'] . ' !important;' ) . '
			    ' . ( empty( $kb_config['section_typography']['font-weight'] ) ? '' : 'font-weight: ' . $kb_config['section_typography']['font-weight'] . ' !important;' ) . '
			    ' . ( empty( $kb_config['section_typography']['font-size'] ) ? '' : 'font-size: ' . $kb_config['section_typography']['font-size'] . 'px' . ' !important;' ) . '
			}
			#epkb-content-container .epkb-category-level-2-3 {
			    font-size: ' . $kb_config['section_typography']['font-size'] . 'px' . ' !important;
			}';
		}

		// Headings  -----------------------------------------/
		$output .= '';

		// Articles  -----------------------------------------/
		if ( ! empty( $kb_config['article_typography']['font-family'] ) || ! empty( $kb_config['article_typography']['font-weight'] ) || ! empty( $kb_config['article_typography']['font-size'] ) ) {
			$output .= '
			#epkb-content-container .epkb-section-body .eckb-article-title {
			    ' . (empty($kb_config['article_typography']['font-family']) ? '' : 'font-family: ' . $kb_config['article_typography']['font-family'] . ' !important;') . '
			    ' . (empty($kb_config['article_typography']['font-weight']) ? '' : 'font-weight: ' . $kb_config['article_typography']['font-weight'] . ' !important;') . '
			    ' . (empty($kb_config['article_typography']['font-size']) ? '' : 'font-size: ' . $kb_config['article_typography']['font-size'] . 'px' . ' !important;') . '
			}';
		}

		return $output;
	}
}