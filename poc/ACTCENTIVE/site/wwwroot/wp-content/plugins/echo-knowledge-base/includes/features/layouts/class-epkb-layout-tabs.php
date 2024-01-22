<?php

/**
 *  Outputs the tabs theme (tabs used to switch between top categories) for knowledge base main page.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class EPKB_Layout_Tabs extends EPKB_Layout {

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
		$this->articles_seq_data = $articles_seq_data;

		$active_cat_id = $this->get_active_category_id(); ?>

		<div id="epkb-ml-tabs-layout" role="main" aria-labelledby="Knowledge Base" class="epkb-layout-container epkb-css-full-reset epkb-tabs-template">
			<div id="epkb-content-container">
				<div class="epkb-section-container">
					<div class="epkb-content-container">

						<!-- Navigation Tabs -->						<?php
						$this->display_navigation_tabs( $active_cat_id ); ?>

						<!-- Main Page Content -->
						<div class="epkb-panel-container">				<?php
							$this->display_main_page_content( $active_cat_id ); ?>
						</div>

					</div>
				</div>
			</div>
		</div>   <?php
	}

    /**
	 * Generate content of the KB main page
	 */
	public function generate_kb_main_page() {

		$active_cat_id = $this->get_active_category_id();

		$main_container_class = 'epkb-css-full-reset epkb-tabs-template';

		$class2 = $this->get_css_class( '::width' );   	    ?>

		<div id="epkb-main-page-container" role="main" aria-labelledby="Knowledge Base" class="<?php echo esc_attr( $main_container_class ); ?> <?php echo EPKB_Utilities::get_active_theme_classes( 'mp' ); ?>">
			<div <?php echo $class2; ?>>  <?php

				//  KB Search form
				$this->get_search_form();

				//  Knowledge Base Layout
				$style1 = $this->get_inline_style( 'background-color:: background_color' );				?>
				<div id="epkb-content-container" <?php echo $style1; ?> >

					<!--  Navigation Tabs -->
					<?php $this->display_navigation_tabs( $active_cat_id ); ?>

					<!--  Main Page Content -->
					<div class="epkb-panel-container">
						<?php $this->display_main_page_content( $active_cat_id ); ?>
					</div>

				</div>
			</div>
		</div>   <?php
	}

	/**
	 * Display KB Main page navigation tabs
	 *
	 * @param $active_cat_id
	 */
	private function display_navigation_tabs( $active_cat_id ) {

		$nof_top_categories = count($this->category_seq_data);

		// show full KB main page if we have fewer categories (otherwise use 'mobile' style menu)

		// loop through LEVEL 1 CATEGORIES
		if ( $nof_top_categories <= 6 ) {

			$class1 = $this->get_css_class( 'epkb-main-nav'. ($this->kb_config[ 'tab_down_pointer' ] == 'on' ? ', epkb-down-pointer' : '') );
			$style1 = $this->get_inline_style( 'typography:: tab_typography, background-color:: tab_nav_background_color' );
			$style2 = $this->get_inline_style( 'background-color:: tab_nav_background_color, border-bottom-color:: tab_nav_border_color, border-bottom-style: solid, border-bottom-width: 1px' ); ?>

			<section <?php echo $class1 . ' ' . $style1; ?> >

				<ul	class="epkb-nav-tabs epkb-top-categories-list" <?php echo $style2; ?> >					<?php

					$ix = 0;
					foreach ( $this->category_seq_data as $category_id => $subcategories ) {

						$category_name = isset( $this->articles_seq_data[$category_id][0] ) ?	$this->articles_seq_data[$category_id][0] : '';
						if ( empty( $category_name ) ) {
							continue;
						}

						$category_desc = isset($this->articles_seq_data[$category_id][1]) && $this->kb_config['section_desc_text_on'] == 'on' ? $this->articles_seq_data[$category_id][1] : '';
						$tab_cat_name = sanitize_title_with_dashes( $category_name );

						$active = $active_cat_id == $category_id ? 'active' : '';
						$class1 = $this->get_css_class( $active . ', col-' . $nof_top_categories . ', epkb_top_categories');
						$style2 = $this->get_inline_style( 'color:: tab_nav_font_color' );

						$box_category_data = $this->is_builder_on ? 'data-kb-category-id=' . $category_id : '';    ?>

						<li id="epkb_tab_<?php echo ++$ix; ?>" tabindex="0" <?php echo $class1; ?> data-cat-name="<?php echo esc_attr( $tab_cat_name ); ?>">
							<div  class="epkb-category-level-1" <?php echo $box_category_data . ' ' . $style2; ?> >
								<h2 class="epkb-cat-name"><?php echo esc_html( $category_name ); ?></h2>
							</div>
							<?php if( $category_desc ) { ?>
								<p class="epkb-cat-desc" <?php echo $style2; ?> ><?php echo $category_desc ?></p>
							<?php } ?>
						</li> <?php

					} //foreach					?>

				</ul>
			</section> <?php

		} else {   ?>

			<!-- Drop Down Menu -->  <?php

			$class1 = $this->get_css_class( 'main-category-selection-1' );
			$style1 = $this->get_inline_style( 'typography::tab_typography, background-color:: tab_nav_background_color' );
			$style2 = $this->get_inline_style( 'color:: tab_nav_font_color' );  		?>

			<section <?php echo $class1 . ' ' . $style1; ?> >

				<div class="epkb-category-level-1" <?php echo $style2; ?>><?php echo esc_html($this->kb_config['choose_main_topic']); ?></div>

				<select id="main-category-selection" class="epkb-top-categories-list"> <?php
						$ix = 0;
						foreach ( $this->category_seq_data as $category_id => $subcategories ) {

							$category_name = isset($this->articles_seq_data[$category_id][0]) ? $this->articles_seq_data[$category_id][0] : '';
							if ( empty($category_name) ) {
								continue;
							}

							$category_desc = isset($this->articles_seq_data[$category_id][1]) && $this->kb_config['section_desc_text_on'] == 'on' ? $this->articles_seq_data[$category_id][1] : '';
							$tab_cat_name = sanitize_title_with_dashes( $category_name );
							$option = $category_name . ( empty($category_desc) ? '' : ' - ' . $category_desc );
							$active = $active_cat_id == $category_id ? 'selected' : '';
							$box_category_data = $this->is_builder_on ? 'data-kb-category-id=' . $category_id : '';    ?>
							<option <?php echo $active . ' ' . $box_category_data; ?> id="epkb_tab_<?php echo ++$ix; ?>" data-cat-name="<?php echo esc_attr( $tab_cat_name ); ?>"><?php echo $option; ?></option>  <?php
						} 	?>

				</select>

			</section> <?php
		}
	}

	/**
	 * Display KB main page content
	 *
	 * @param $active_cat_id
	 */
	private function display_main_page_content( $active_cat_id ) {

		// show message that articles are coming soon if the current KB does not have any Category
		if ( ! $this->has_kb_categories ) {
			$this->show_categories_missing_message();
			return;
		}

		$class0 = $this->get_css_class('::section_box_shadow, epkb-top-category-box');
		$style0 = $this->get_inline_style( 
				'border-radius:: section_border_radius,
				 border-width:: section_border_width,
				 border-color:: section_border_color, ' .
				'background-color:: section_body_background_color, border-style: solid' );

		$class_section_head = $this->get_css_class( 'section-head' . ( $this->kb_config[ 'section_divider' ] == 'on' ? ', section_divider' : '' ) );
		$style_section_head = $this->get_inline_style(
					'border-bottom-width:: section_divider_thickness,
					background-color:: section_head_background_color, ' .
					'border-top-left-radius:: section_border_radius,
					border-top-right-radius:: section_border_radius,
					text-align::section_head_alignment,
					border-bottom-color:: section_divider_color,
					padding-top:: section_head_padding_top,
					padding-bottom:: section_head_padding_bottom,
					padding-left:: section_head_padding_left,
					padding-right:: section_head_padding_right'
		);
		$style3 = $this->get_inline_style(
					'color:: section_head_font_color,
					 text-align::section_head_alignment,
					 justify-content::section_head_alignment'
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

		$categories_icons = $this->get_category_icons();

		$header_icon_style = $this->get_inline_style( 'color:: section_head_category_icon_color, font-size:: section_head_category_icon_size' );
		$header_image_style = $this->get_inline_style( 'max-height:: section_head_category_icon_size' );

		$icon_location = empty($this->kb_config['section_head_category_icon_location']) ? '' : $this->kb_config['section_head_category_icon_location'];
		$top_icon_class = 'epkb-category--' . $icon_location . '-cat-icon';
		
		// loop through LEVEL 1 CATEGORIES: for the active TOP-CATEGORY display top-level SUB-CATEGORIES with its articles
		$ix = 0;
		$articles_coming_soon_msg = $this->kb_config['category_empty_msg'];
		
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
		}

		foreach ( $this->category_seq_data as $tab_category_id => $box_categories_array ) {

			$level_1_categories = is_array($box_categories_array) ? $box_categories_array : array();
			$active = $active_cat_id == $tab_category_id ? 'active' : '';
			$class_list = 'epkb_tab_' . ++$ix . ' epkb_top_panel epkb-tab-panel ';
			if ( ! empty( $this->kb_config['nof_columns'] ) ) {
				$class_list .= 'epkb-' . $this->kb_config['nof_columns'];
			}
			$class_list .= ' eckb-categories-list ' . $active;			?>

			<div class="<?php echo esc_attr( $class_list ); ?>"> <?php

				$top_category_id_data = 'data-kb-top-category-id=' . $tab_category_id;
				$is_modular = $this->kb_config['modular_main_page_toggle'] == 'on';
				if ( empty($level_1_categories) && ! empty($articles_coming_soon_msg) ) {
					echo '<div class="epkb-articles-coming-soon" ' . $top_category_id_data . ' data-kb-type=top-category-no-articles ' . '>' . esc_html( $articles_coming_soon_msg ) . '</div>';
				}

				/** DISPLAY LEVEL 2 CATEGORIES (BOX) + ARTICLES + SUB-SUB-CATEGORIES */
				$category_number = 0;
				$column_index = 1;
				$loop_index = 1;
				foreach ( $level_1_categories as $box_category_id => $level_2_categories ) {
					$category_number++;

					$category_name = isset($this->articles_seq_data[$box_category_id][0]) ? $this->articles_seq_data[$box_category_id][0] : '';
					if ( empty($category_name) ) {
						continue;
					}
					
					$category_icon = EPKB_KB_Config_Category::get_category_icon( $box_category_id, $categories_icons );
					$category_desc = isset($this->articles_seq_data[$box_category_id][1]) && $this->kb_config['section_desc_text_on'] == 'on' ? $this->articles_seq_data[$box_category_id][1] : '';
					$level_2_categories = is_array($level_2_categories) ? $level_2_categories : array();
					$box_category_data = $this->is_builder_on ? $top_category_id_data . ' data-kb-category-id=' . $box_category_id . ' data-kb-type=sub-category ' : '';

					if ( $is_modular && $column_index == 1 ) { ?>
						<div class="epkb-ml__module-categories-articles__row">  <?php
					}   ?>

					<!-- Section Container ( Category Box ) -->
					<section id="<?php echo 'epkb_cat_' . $category_number; ?>" <?php echo $class0 . ' ' . $style0; ?> >

						<!-- Section Head -->
						<div <?php echo $class_section_head . ' ' . $style_section_head; ?> >

							<!-- Category Name + Icon -->
							<div class="epkb-category-level-2-3 <?php echo $top_icon_class; ?>" aria-expanded="false" <?php echo $box_category_data . ' ' . $style3; ?> >

							<!-- Icon Top / Left -->	                            <?php
							if ( in_array( $icon_location, array('left', 'top') ) ) {

								if ( $category_icon['type'] == 'image' ) { ?>
									<img class="epkb-cat-icon epkb-cat-icon--image  "
									     src="<?php echo $category_icon['image_thumbnail_url']; ?>" alt="<?php echo $category_icon['image_alt']; ?>"
										<?php echo $header_image_style; ?>
									>								<?php
								} else { ?>
									<span class="epkb-cat-icon epkbfa <?php echo  esc_attr( $category_icon['name'] ); ?>" data-kb-category-icon="<?php echo esc_attr( $category_icon['name'] ); ?>" <?php echo $header_icon_style; ?>></span>	<?php
								}
							}

							// Get the URL of this category if link is on
							if ( $this->kb_config['section_hyperlink_on'] == 'on' ) {

								// Get the URL of this category
								$category_link = EPKB_Utilities::get_term_url( $box_category_id );     ?>
								<h3 class="epkb-cat-name"><a href="<?php echo esc_url( $category_link ); ?>" <?php echo $style31; ?>> <?php echo esc_html( $category_name ); ?></a></h3>		<?php
							} else {    ?>
								<h3 class="epkb-cat-name" <?php echo $style31; ?>><?php echo esc_html( $category_name ); ?></h3>							<?php
							}	        ?>

							<!-- Icon Right -->     <?php
							if ( $icon_location == 'right' ) {

								if ( $category_icon['type'] == 'image' ) { ?>
									<img class="epkb-cat-icon epkb-cat-icon--image "
									     src="<?php echo $category_icon['image_thumbnail_url']; ?>" alt="<?php echo $category_icon['image_alt']; ?>"
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
						<div class="epkb-section-body" <?php echo $this->get_inline_style( $style5 ); ?> >   	<?php							
							/** DISPLAY TOP-CATEGORY ARTICLES LIST */
							if (  $this->kb_config['show_articles_before_categories'] != 'off' ) {
								$this->display_articles_list( 2, $box_category_id, ! empty($level_2_categories) );
							}
							
							if ( ! empty($level_2_categories) ) {
								$this->display_level_3_categories( $level_2_categories, 'sub-', 3 );
							}

							/** DISPLAY TOP-CATEGORY ARTICLES LIST */
							if (  $this->kb_config['show_articles_before_categories'] == 'off' ) {
								$this->display_articles_list( 2, $box_category_id, ! empty($level_2_categories) );
							} ?>

						</div><!-- Section Body End -->

					</section><!-- Section End -->  <?php

					if ( $is_modular && ( $column_index == $categories_per_row || $loop_index == count( $level_1_categories ) ) ) {     ?>
						</div>  <?php
						$column_index = 0;
					}

					$column_index ++;
					$loop_index ++;
				} ?>

			</div>  <?php
		}
	}

	/**
	 * Display categories within the Box i.e. sub-sub-categories
	 *
	 * @param $box_sub_category_list
	 * @param string $level_name
	 * @param int $level_num
	 */
	private function display_level_3_categories( $box_sub_category_list, $level_name, $level_num ) {

		$level_name .= 'sub-';
		$body_style1 = $this->get_inline_style( 'padding-left:: article_list_margin' );
        if ( $level_num >= 4 ) {
	        $body_style1 = $this->get_inline_style( 'padding-left:: sub_article_list_margin' );
        }   ?>
		<ul class="epkb-sub-category eckb-sub-category-ordering" <?php echo $body_style1; ?>> <?php

			/** DISPLAY SUB-SUB-CATEGORIES */
			foreach ( $box_sub_category_list as $box_sub_category_id => $box_sub_sub_category_list ) {
				$category_name = isset($this->articles_seq_data[$box_sub_category_id][0]) ?
											$this->articles_seq_data[$box_sub_category_id][0] : _x( 'Category', 'taxonomy singular name' );

				$class1 = $this->get_css_class( '::expand_articles_icon, epkb-category-level-2-3__cat-icon' );
				$style1 = $this->get_inline_style( 'color:: section_category_icon_color' );
				$style2 = $this->get_inline_style( 'color:: section_category_font_color' );

				$box_sub_category_data = $this->is_builder_on ? 'data-kb-category-id=' . $box_sub_category_id  . ' data-kb-type=' . $level_name . 'category ' : '';  	?>

				<li <?php echo $this->get_inline_style( 'padding-bottom:: article_list_spacing,padding-top::article_list_spacing' ); ?>>
					<div class="epkb-category-level-2-3" aria-expanded="false" <?php echo $box_sub_category_data; ?>>
						<span <?php echo $class1 . ' ' . $style1; ?> ></span>
						<h<?php echo ($level_num + 1); ?> class="epkb-category-level-2-3__cat-name" tabindex="0" <?php echo $style2; ?> ><?php echo esc_html( $category_name ); ?></h<?php echo ($level_num + 1); ?>>
					</div>    <?php

					/** DISPLAY TOP-CATEGORY ARTICLES LIST */
					if (  $this->kb_config['show_articles_before_categories'] != 'off' ) {
						$this->display_articles_list( $level_num, $box_sub_category_id, ! empty($box_sub_sub_category_list), $level_name );
					}

					/** RECURSION DISPLAY SUB-SUB-...-CATEGORIES */
					if ( ! empty($box_sub_sub_category_list) && strlen($level_name) < 20 ) {
						$this->display_level_3_categories( $box_sub_sub_category_list, $level_name, $level_num + 1);
					}
					
					/** DISPLAY TOP-CATEGORY ARTICLES LIST */
					if (  $this->kb_config['show_articles_before_categories'] == 'off' ) {
						$this->display_articles_list( $level_num, $box_sub_category_id, ! empty($box_sub_sub_category_list), $level_name );
					}    ?>
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
	 * @param string $level_name
	 */
	private function display_articles_list( $level, $category_id, $sub_category_exists=false, $level_name='' ) {

		// retrieve articles belonging to given (sub) category if any
		$articles_list = array();
		if ( isset($this->articles_seq_data[$category_id]) ) {
			$articles_list = $this->articles_seq_data[$category_id];
			unset($articles_list[0]);
			unset($articles_list[1]);
		}

		// filter top level articles if the will be displayed elsewhere
		if ( isset( $this->category_seq_data[$category_id] ) ) {
			foreach ( $articles_list as $top_article_id => $top_article_title ) {
				foreach ( $this->articles_seq_data as $category_id => $category_article_list ) {
					if ( isset( $category_article_list[ $top_article_id ] ) && ! isset( $this->category_seq_data[ $category_id ] ) ) {
						unset( $articles_list[ $top_article_id ] );
					}
				}
			}
		}

		// return if we have no articles and will not show 'Articles coming soon' message
		$articles_coming_soon_msg = $this->kb_config['category_empty_msg'];
		if ( empty( $articles_list ) && ( $sub_category_exists || empty( $articles_coming_soon_msg ) ) ) {
			return;
		}

		$sub_category_styles = '';
		if ( $level == 1 ) {
			$data_kb_type = 'article';
		} else if ( $level == 2 ) {
			$data_kb_type = 'sub-article';
			$sub_category_styles = is_rtl() ? 'padding-right:: article_list_margin' : 'padding-left:: article_list_margin';
		} else {
			$data_kb_type = empty( $level_name ) ? 'sub-sub-article' : $level_name . 'article';
			$sub_category_styles = is_rtl() ? 'padding-right:: sub_article_list_margin' : 'padding-left:: sub_article_list_margin';
		}

		$style = 'class="' . ( $level == 2 ? 'epkb-main-category ' : '' ) .  'epkb-articles"';		?>

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
				<li class="epkb-article-level-<?php echo $level . ' ' . $hide_class; ?>" <?php echo $article_data . ' ' . $this->get_inline_style( 'padding-bottom:: article_list_spacing,padding-top::article_list_spacing' ); ?> >   <?php
								$this->single_article_link( $article_title, $article_id, EPKB_Layout::TABS_LAYOUT ); ?>
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
	 * Determine active tab based on URL or choose first one
	 * @return int|string
	 */
	private function get_active_category_id() {

		$active_tab = EPKB_Utilities::post( 'top-category' );
		$active_cat_id = '0';
		$first_tab = true;
		foreach ( $this->category_seq_data as $category_id => $subcategories ) {

			$category_name = isset( $this->articles_seq_data[ $category_id ][0] ) ? $this->articles_seq_data[ $category_id ][0] : '';
			if ( empty( $category_name ) ) {
				continue;
			}

			// apply the same sanitization that was used on category name output to HTML attribute, then URL decode it to have the same value for comparison
			$tab_cat_name = urldecode( esc_attr( sanitize_title_with_dashes( $category_name ) ) );

			if ( $first_tab ) {
				$active_cat_id = $category_id;
				$first_tab = false;
			}

			if ( $tab_cat_name == $active_tab ) {
				$active_cat_id = $category_id;
				break;
			}
		}

		return $active_cat_id;
	}

	/**
	 * For users, like author, and above: show warning that top tab category has articles (normally it should not)
	 * @param $tab_category_id
	 * @param $style3
	 * @param $class0
	 * @param $style_section_head
	 * @param $style5
	 */
    private function check_top_categories_empty( $tab_category_id, $style3, $class0, $style_section_head, $style5 ) {

	    // retrieve articles belonging to the current tab category if any (normally it should not)
	    if ( ! isset( $this->articles_seq_data[$tab_category_id] ) ) {
	    	return;
	    }

	    $tab_articles_list = $this->articles_seq_data[$tab_category_id];
	    unset( $tab_articles_list[0] );
	    unset( $tab_articles_list[1] );

	    // filter top level articles if they will be displayed elsewhere
	    foreach ( $tab_articles_list as $article_id => $article_title ) {
		    foreach ( $this->articles_seq_data as $category_id => $category_article_list ) {
		    	if ( isset( $category_article_list[$article_id] ) && ! isset( $this->category_seq_data[$category_id] ) ) {
				    unset( $tab_articles_list[$article_id] );
			    }
		    }
	    }

	    // do we have articles for top categories?
	    if ( empty( $tab_articles_list ) ) {
	    	return;
	    }

	    if ( ! current_user_can( EPKB_Admin_UI_Access::get_author_capability() ) ) {
		    return;
	    }

	    $warning_header_icon_style = $this->get_inline_style( 'color: #ff0000, font-size:: section_head_category_icon_size' );
	    $warning_header_text_style = $this->get_inline_style( 'color: #000000, font-size: 18px, line-height: 1.25' );
	    $warning_description_style = $this->get_inline_style( 'line-height: 1.25, font-style: italic' );
	    $warning_links_wrap_style = $this->get_inline_style( 'margin-top: 10px, margin-bottom: 20px' );
	    $warning_link_style = $this->get_inline_style( 'color: #509fe2, margin-right: 20px' );
	    $warning_style = $this->get_inline_style( 'border-radius:: section_border_radius,  border-width: 1px, border-color: #ff0000, background-color:: section_body_background_color, border-style: solid' );
	    $class_section_head = $this->get_css_class( 'section-head epkb-category--top-warning' . ( $this->kb_config[ 'section_divider' ] == 'on' ? ', section_divider' : '' ) );

	    $post_type = EPKB_KB_Handler::get_post_type( $this->kb_id );
	    $edit_categories_url = admin_url( '/edit-tags.php?taxonomy=' . EPKB_KB_Handler::get_post_type( $this->kb_id ) . '_category&post_type=' . $post_type );
	    $learn_more_url = 'https://www.echoknowledgebase.com/documentation/using-tabs-layout/';  ?>

	    <!-- Section Container ( Category Box ) -->
	    <section id="0" <?php echo $class0 . ' ' . $warning_style; ?> >

		    <!-- Section Head -->
		    <div <?php echo $class_section_head . ' ' . $style_section_head; ?> >

			    <!-- Category Name + Icon -->
			    <div class="epkb-category-level-2-3 epkb-category--top-cat-icon" aria-expanded="false" <?php echo $style3; ?> >
				    <span class="epkb-cat-icon epkbfa ep_font_icon_error_circle" <?php echo $warning_header_icon_style; ?>></span>
				    <h3 class="epkb-cat-name" <?php echo $warning_header_text_style; ?>><?php esc_html_e( 'The Following Articles Are not Visible to End users', 'echo-knowledge-base' ); ?></h3>
			    </div>
		    </div>

		    <!-- Section Body -->
		    <div class="epkb-section-body" <?php echo $this->get_inline_style( $style5 ); ?> >
			    <p <?php echo $warning_description_style; ?>><?php esc_html_e( 'For this Tabs Layout, articles assigned to top categories will not be visible to end users because the top category is used for navigation. ' .
			                                                                   'Please move articles to another category or a new sub-category.', 'echo-knowledge-base' ); ?></p>
			    <div <?php echo $warning_links_wrap_style; ?>>  <?php
				    if ( current_user_can( EPKB_Admin_UI_Access::get_editor_capability() ) ) {   ?>
					    <a href="<?php echo esc_url( $edit_categories_url ); ?>" target="_blank" <?php echo $warning_link_style; ?>><?php esc_html_e( 'Edit Categories', 'echo-knowledge-base' ); ?></a>    <?php
				    }   ?>
				    <a href="<?php echo esc_url( $learn_more_url ); ?>" target="_blank" <?php echo $warning_link_style; ?>><?php esc_html_e( 'Learn More', 'echo-knowledge-base' ); ?></a>
			    </div>  <?php
			    $this->display_articles_list( 2, $tab_category_id );  ?>
		    </div>

	    </section>  <?php
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
		$output .= '';

		// Tabs  ---------------------------------------------/
		$output .= '
			#epkb-content-container .epkb-nav-tabs .active:after {
				border-top-color: ' . $kb_config['tab_nav_active_background_color'] . '!important
			}
			#epkb-content-container .epkb-nav-tabs .active {
				background-color: ' . $kb_config['tab_nav_active_background_color'] . '!important
			}	
			#epkb-content-container .epkb-nav-tabs .active .epkb-category-level-1,
			#epkb-content-container .epkb-nav-tabs .active p {
				color: ' . $kb_config['tab_nav_active_font_color'] . '!important
			}
			#epkb-content-container .epkb-nav-tabs .active:before {
				border-top-color: ' . $kb_config['tab_nav_border_color'] . '!important
			}
		';

		// Headings  -----------------------------------------/
		$output .= '
			#epkb-content-container .epkb-category-level-2-3 {
			    font-size: ' . ( empty( $kb_config['section_typography']['font-size'] ) ? 'inherit;' : $kb_config['section_typography']['font-size'] . 'px!important;' ) . '
			}
		';

		// Articles  -----------------------------------------/
		$output .= '
			#epkb-content-container .epkb-category-level-2-3__cat-name, 
			#epkb-content-container .epkb-articles-coming-soon,
			#epkb-content-container .epkb-show-all-articles { ' .
				EPKB_Typography::get_css_string( $kb_config['section_typography'] ) . '
			}
			#epkb-content-container .epkb-section-body .eckb-article-title { ' .
				EPKB_Typography::get_css_string( $kb_config['article_typography'] ) . '
			}
		';

		return $output;
	}

}