<?php

/**
 *  Outputs the Drill-Down Layout for knowledge base main page.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class EPKB_Layout_Drill_Down extends EPKB_Layout {

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

		// show message that articles are coming soon if the current KB does not have any Category
		if ( ! $this->has_kb_categories ) {
			$this->show_categories_missing_message();
			return;
		}

		switch ( $this->kb_config['nof_columns'] ) {
			case 'one-col':
				$colClass = "epkb-ml-1-lvl-categories-button--1-col";
				break;
			case 'two-col':
			default:
				$colClass = "epkb-ml-1-lvl-categories-button--2-col";
				break;
			case 'three-col':
				$colClass = "epkb-ml-1-lvl-categories-button--3-col";
				break;
			case 'four-col':
				$colClass = "epkb-ml-1-lvl-categories-button--4-col";
				break;
		}

		$categories_icons = $this->get_category_icons();				?>

		<div id="epkb-ml-drill-down-layout" class="epkb-layout-container">

			<!-- Top Level Categories -->
			<div class="epkb-ml-drill-down-layout-categories-container">

				<!-- 1st Level Categories Button -->
				<div class="epkb-ml-1-lvl-categories-button-container <?php echo esc_html( $colClass ); ?>">                    <?php
					foreach ( $this->category_seq_data as $category_id => $level_2_categories ) {

						$category_name = isset( $this->articles_seq_data[ $category_id ][0] ) ? $this->articles_seq_data[ $category_id ][0] : '';
						if ( empty( $category_name ) ) {
							continue;
						}

						$this->display_drill_down_category_button_lvl_1( $category_id, $categories_icons, $category_name );
					} ?>
				</div>

				<!-- 1st Level Categories content -->
				<div class="epkb-ml-1-lvl-categories-content-container">

					<button class="epkb-back-button">
						<span class="epkb-back-button__icon epkbfa epkbfa-arrow-left"></span>
						<span class="epkb-back-button__text"><?php echo esc_html( $this->kb_config['ml_categories_articles_back_button_text'] ); ?></span>
					</button>   <?php

					foreach ( $this->category_seq_data as $category_id => $level_2_categories ) {
						$this->display_drill_down_category_content_lvl_1( $category_id, $categories_icons, $level_2_categories );
					}   ?>

				</div>

			</div>

		</div>  <?php
	}


	/**
	 * Display button of top category for Drill Down Layout
	 *
	 * @param $category_id
	 * @param $categories_icons
	 * @param $category_name
	 */
	private function display_drill_down_category_button_lvl_1( $category_id, $categories_icons, $category_name ) {

		$category_icon = EPKB_KB_Config_Category::get_category_icon( $category_id, $categories_icons );
		$category_title_tag = empty( $this->kb_config['ml_categories_articles_category_title_html_tag'] ) ? 'div' : $this->kb_config['ml_categories_articles_category_title_html_tag'];

		switch ( $this->kb_config['section_head_category_icon_location'] ) {
			case 'no_icons':            ?>
				<section id="epkb-1-lvl-id-<?php echo $category_id; ?>" class="epkb-ml-1-lvl__cat-container epkb-ml-1-lvl__cat-container--none-location">					<?php
					echo '<' . esc_html( $category_title_tag ) . ' ' . 'class="epkb-ml-1-lvl__cat-title"' . '>' . esc_html( $category_name ) . '</' . esc_html( $category_title_tag ) . '>'; ?>
				</section>				<?php
				break;

			case 'top':
			case 'left':                ?>
				<section id="epkb-1-lvl-id-<?php echo $category_id; ?>" class="epkb-ml-1-lvl__cat-container epkb-ml-1-lvl__cat-container--<?php echo $this->kb_config['section_head_category_icon_location']; ?>-location">

					<!-- Icon / Image -->					<?php
					if ( $category_icon['type'] == 'image' ) { ?>
						<img class="epkb-ml-1-lvl__cat-icon epkb-ml-1-lvl__cat-icon--image" src="<?php echo esc_url( $category_icon['image_thumbnail_url'] ); ?>" alt="<?php echo esc_attr( $category_icon['image_alt'] ); ?>">  <?php
					} else { ?>
						<div class="epkb-ml-1-lvl__cat-icon epkb-ml-1-lvl__cat-icon--font epkbfa <?php echo esc_attr( $category_icon['name'] ); ?>" data-kb-category-icon="<?php echo esc_attr( $category_icon['name'] ); ?>"></div>	<?php
					} ?>

					<!-- Category Name -->					<?php
					echo '<' . esc_html( $category_title_tag ) . ' ' . 'class="epkb-ml-1-lvl__cat-title"' . '>' . esc_html( $category_name ) . '</' . esc_html( $category_title_tag ) . '>'; ?>

				</section>				<?php
				break;

			case 'right':               ?>
				<section id="epkb-1-lvl-id-<?php echo $category_id; ?>" class="epkb-ml-1-lvl__cat-container epkb-ml-1-lvl__cat-container--right-location">

					<!-- Category Name -->					<?php
					echo '<' . esc_html( $category_title_tag ) . ' ' . 'class="epkb-ml-1-lvl__cat-title"' . '>' . esc_html( $category_name ) . '</' . esc_html( $category_title_tag ) . '>'; ?>

					<!-- Icon / Image -->					<?php
					if ( $category_icon['type'] == 'image' ) { ?>
						<img class="epkb-ml-1-lvl__cat-icon epkb-ml-1-lvl__cat-icon--image" src="<?php echo esc_url( $category_icon['image_thumbnail_url'] ); ?>" alt="<?php echo esc_attr( $category_icon['image_alt'] ); ?>">  <?php
					} else { ?>
						<div class="epkb-ml-1-lvl__cat-icon epkb-ml-1-lvl__cat-icon--font epkbfa <?php echo esc_attr( $category_icon['name'] ); ?>" data-kb-category-icon="<?php echo esc_attr( $category_icon['name'] ); ?>"></div>	<?php
					} ?>

				</section>				<?php
				break;
		}
	}

	/**
	 * Display content of top category for Drill Down Layout
	 *
	 * @param $category_id
	 * @param $categories_icons
	 * @param $level_2_categories
	 */
	private function display_drill_down_category_content_lvl_1( $category_id, $categories_icons, $level_2_categories ) {

		$category_desc = isset( $this->articles_seq_data[ $category_id ][1] ) && $this->kb_config['section_desc_text_on'] == 'on' ? $this->articles_seq_data[ $category_id ][1] : '';

		// retrieve level 1 articles
		$articles_level_1_list = array();
		if ( isset( $this->articles_seq_data[ $category_id ] ) ) {
			$articles_level_1_list = $this->articles_seq_data[ $category_id ];
			unset( $articles_level_1_list[0] );
			unset( $articles_level_1_list[1] );
		}

		// If no Level 1 articles exist, add Class to make Category Desc full width.
		$no_level1_articles_class = '';
		$no_desc_class = '';
		$no_articles_class = '';
		$article_columns = 2;

		if ( empty( $articles_level_1_list ) ) {
			$no_level1_articles_class = 'epkb-ml-1-lvl-desc-articles--no-articles';
		}

		if ( empty( $category_desc ) ) {
			$no_desc_class = 'epkb-ml-1-lvl-desc-articles--no-desc';
			$article_columns = 3;
		}

		// If no articles then add class for articles coming soon message.
		if ( empty( $articles_level_1_list ) && empty( $level_2_categories ) ) {
			$no_articles_class = 'epkb-ml-1-lvl__cat-content--no-articles';
		}   ?>

		<div class="epkb-ml-1-lvl__cat-content <?php echo esc_html( $no_articles_class ); ?>" data-cat-content="epkb-1-lvl-id-<?php echo esc_attr( $category_id ); ?>">   <?php

			if ( ! empty( $category_desc ) || ! empty( $articles_level_1_list ) ) { ?>

				<!-- Top Categories Description and articles -->
				<div class="epkb-ml-1-lvl-desc-articles <?php echo esc_html( $no_level1_articles_class . ' ' . $no_desc_class ); ?>">
					<div class="epkb-ml-1-lvl__desc"><?php echo esc_html( $category_desc ); ?></div>
					<div class="epkb-ml-1-lvl__articles">

						<div class="epkb-ml-articles-list">    <?php

							$total_article_index = 0;

							// calculate number of articles per column
							$articles_per_column[0] = ceil( count( $articles_level_1_list ) / $article_columns );
							$articles_per_column[1] = $article_columns > 2
								? ceil( ( count( $articles_level_1_list ) - $articles_per_column[0] ) / ( $article_columns - 1 ) )
								: count( $articles_level_1_list ) - $articles_per_column[0];

							// create a nested array of articles for each column
							$column_count = 0;
							$column_articles_count = 0;
							$columns = array_fill( 0, $article_columns, [] );
							foreach ( $articles_level_1_list as $article_id => $article_title ) {
								if ( ! EPKB_Utilities::is_article_allowed_for_current_user( $article_id ) ) {
									continue;
								}

								if ( isset( $articles_per_column[ $column_count ] ) && $column_articles_count >= $articles_per_column[ $column_count ] ) {
									$column_count ++;
									$column_articles_count = 0;
								}

								$columns[ $column_count ][] = [
									'title'  => $article_title,
									'id'     => $article_id,
								];
								$column_articles_count ++;
							}

							// display the articles in the columns  ?>
							<div class="epkb-ml-articles-list epkb-total-columns-<?php echo $article_columns; ?>">   <?php
								$column_number = 1;
								foreach ( $columns as $column_articles ) {
									$article_index = 0; ?>
									<ul class="epkb-list-column epkb-list-column-<?php echo $column_number; ?>">   <?php
										foreach ( $column_articles as $article ) {
											if ( ceil( $article_index * $article_columns ) < $this->kb_config['nof_articles_displayed'] ) { ?>
												<li><?php $this->single_article_link( $article['title'], $article['id'], EPKB_Layout::DRILL_DOWN_LAYOUT ); ?></li>  <?php
											} else { ?>
												<li class="epkb-ml-article-hide"><?php $this->single_article_link( $article['title'], $article['id'], EPKB_Layout::DRILL_DOWN_LAYOUT ); ?></li>  <?php
											}
											$article_index ++;
											$total_article_index ++;
										} ?>
									</ul>   <?php
									$column_number ++;
								} ?>
							</div> <?php

							$additional_articles_number = $total_article_index - $this->kb_config['nof_articles_displayed'];
							if ( $additional_articles_number > 0 ) { ?>
								<span class="epkb-ml-articles-show-more">
									<a href="#"><?php echo sprintf( esc_html( $this->kb_config['ml_categories_articles_show_more_text'] . ' (%s)' ), $additional_articles_number ); ?></a>
								</span>    <?php
							} ?>
						</div>
					</div>
				</div>            <?php
			}

			// The Top Category and it's children are completely empty display articles coming soon message.
			if ( empty( $articles_level_1_list ) && empty( $level_2_categories ) ) {
				$articles_coming_soon_msg = $this->kb_config['category_empty_msg']; ?>
				<div class="epkb-ml-articles-coming-soon"><?php echo esc_html( $articles_coming_soon_msg ); ?></div>            <?php
			} ?>

		</div>

		<!-- 2nd Level Categories Button -->        <?php
		if ( ! empty( $level_2_categories ) ) { ?>
			<div class="epkb-ml-2-lvl-categories-button-container" data-cat-content="epkb-1-lvl-id-<?php echo esc_attr( $category_id ); ?>">  <?php
				foreach ( $level_2_categories as $level_2_category_id => $level_3_categories ) {

					$level_2_category_name = isset( $this->articles_seq_data[ $level_2_category_id ][0] ) ? $this->articles_seq_data[ $level_2_category_id ][0] : '';
					if ( empty( $level_2_category_name ) ) {
						continue;
					}

					$this->display_drill_down_category_button_lvl_2( $level_2_category_id, $categories_icons, $level_2_category_name );
				} ?>
			</div>        <?php
		} ?>

		<!-- 2nd Level Categories content -->   <?php
		foreach ( $level_2_categories as $level_2_category_id => $level_3_categories ) {
			$this->display_drill_down_category_content_lvl_2( $level_2_category_id, $categories_icons, $level_3_categories );
		}
	}

	/**
	 * Display button of second level category for Drill Down Layout
	 *
	 * @param $level_2_category_id
	 * @param $categories_icons
	 * @param $level_2_category_name
	 */
	private function display_drill_down_category_button_lvl_2( $level_2_category_id, $categories_icons, $level_2_category_name ) {

		$level_2_category_icon = EPKB_KB_Config_Category::get_category_icon( $level_2_category_id, $categories_icons ); ?>

		<section id="epkb-ml-2-lvl-<?php echo $level_2_category_id; ?>" class="epkb-ml-2-lvl__cat-container">  <?php
			if ( $level_2_category_icon['type'] == 'image' ) { ?>
				<img class="epkb-ml-2-lvl__cat-icon epkb-ml-2-lvl__cat-icon--image"
				     src="<?php echo esc_url( $level_2_category_icon['image_thumbnail_url'] ); ?>" alt="<?php echo esc_attr( $level_2_category_icon['image_alt'] ); ?>">  <?php
			} else { ?>
				<div class="epkb-ml-2-lvl__cat-icon epkb-ml-2-lvl__cat-icon--font epkbfa <?php echo esc_attr( $level_2_category_icon['name'] ); ?>" data-kb-category-icon="<?php echo esc_attr( $level_2_category_icon['name'] ); ?>"></div>    <?php
			} ?>
			<div class="epkb-ml-2-lvl__cat-title"><?php echo esc_html( $level_2_category_name ); ?></div>
		</section>  <?php
	}

	/**
	 * Display content of second level category for Drill Down Layout
	 *
	 * @param $level_2_category_id
	 * @param $categories_icons
	 * @param $level_3_categories
	 */
	private function display_drill_down_category_content_lvl_2( $level_2_category_id, $categories_icons, $level_3_categories ) {

		$level_2_category_desc = isset( $this->articles_seq_data[ $level_2_category_id ][1] ) && $this->kb_config['section_desc_text_on'] == 'on' ? $this->articles_seq_data[ $level_2_category_id ][1] : '';

		// retrieve level 2 articles
		$articles_level_2_list = array();
		if ( isset( $this->articles_seq_data[ $level_2_category_id ] ) ) {
			$articles_level_2_list = $this->articles_seq_data[ $level_2_category_id ];
			unset( $articles_level_2_list[0] );
			unset( $articles_level_2_list[1] );
		}

		$articleColumns = 2; ?>

		<div class="epkb-ml-2-lvl__cat-content" data-cat-content="epkb-ml-2-lvl-<?php echo esc_attr( $level_2_category_id ); ?>">            <?php

			if ( ! empty( $level_2_category_desc ) ) { ?>
				<div class="epkb-ml-2-lvl__desc"><?php echo esc_html( $level_2_category_desc ); ?></div>            <?php
			}

			if ( empty( $articles_level_2_list ) ) {
				$articles_coming_soon_msg = $this->kb_config['category_empty_msg']; ?>
				<div class="epkb-ml-articles-coming-soon"><?php echo esc_html( $articles_coming_soon_msg ); ?></div> <?php
			} ?>

			<div class="epkb-ml-2-lvl__articles">
				<ul class="epkb-ml-articles-list">    <?php
					$total_article_index = 0;
					$articles_per_column = ceil( count( $articles_level_2_list ) / $articleColumns ); // calculate number of articles per column
					$column_count = 0;
					$column_articles_count = 0;
					$colNum = 1;

					// create a nested array of articles for each column
					$columns = array_fill( 0, $articleColumns, [] );
					foreach ( $articles_level_2_list as $article_id => $article_title ) {

						if ( ! EPKB_Utilities::is_article_allowed_for_current_user( $article_id ) ) {
							continue;
						}

						if ( $column_articles_count >= $articles_per_column ) {
							$column_count ++;
							$column_articles_count = 0;
						}

						$columns[ $column_count ][] = [
							'title'  => $article_title,
							'id'     => $article_id,
						];
						$column_articles_count ++;
					}

					// display the articles in the columns  ?>
					<div class="epkb-ml-articles-list epkb-total-columns-<?php echo $articleColumns; ?>">   <?php
						foreach ( $columns as $column_articles ) {
							$article_index = 0; ?>
							<ul class="epkb-list-column epkb-list-column-<?php echo $colNum; ?>">   <?php
								foreach ( $column_articles as $article ) {
									if ( ceil( $article_index * $articleColumns ) < $this->kb_config['nof_articles_displayed'] ) { ?>
										<li><?php $this->single_article_link( $article['title'], $article['id'], EPKB_Layout::DRILL_DOWN_LAYOUT ); ?></li>  <?php
									} else { ?>
										<li class="epkb-ml-article-hide"><?php $this->single_article_link( $article['title'], $article['id'], EPKB_Layout::DRILL_DOWN_LAYOUT ); ?></li>  <?php
									}
									$article_index ++;
									$total_article_index ++;
								} ?>
							</ul>   <?php
							$colNum ++;
						} ?>
					</div> <?php

					$additional_articles_number = $total_article_index - $this->kb_config['nof_articles_displayed'];
					if ( $additional_articles_number > 0 ) { ?>
						<span class="epkb-ml-articles-show-more">
							<a	href="#"><?php echo sprintf( esc_html( $this->kb_config['ml_categories_articles_show_more_text'] . ' (%s)' ), $additional_articles_number ); ?></a>
						</span>    <?php
					} ?>
				</ul>
			</div>

			<!-- 3rd Level Categories content -->
			<div class="epkb-ml-3-lvl-cat-container">   <?php

				foreach ( $level_3_categories as $level_3_category_id => $level_4_categories ) {

					$level_3_category_name = isset( $this->articles_seq_data[ $level_3_category_id ][0] ) ? $this->articles_seq_data[ $level_3_category_id ][0] : '';
					if ( empty( $level_3_category_name ) ) {
						continue;
					}

					$this->display_drill_down_category_content_lvl_3( $level_3_category_id, $categories_icons, $level_3_category_name );
				} ?>

			</div>
		</div>  <?php
	}

	/**
	 * Display content of third level category for Drill Down Layout
	 *
	 * @param $level_3_category_id
	 * @param $categories_icons
	 * @param $level_3_category_name
	 */
	private function display_drill_down_category_content_lvl_3( $level_3_category_id, $categories_icons, $level_3_category_name ) {

		// retrieve level 3 articles
		$articles_level_3_list = array();
		if ( isset( $this->articles_seq_data[ $level_3_category_id ] ) ) {
			$articles_level_3_list = $this->articles_seq_data[ $level_3_category_id ];
			unset( $articles_level_3_list[0] );
			unset( $articles_level_3_list[1] );
		}

		$sub_sub_category_icon = EPKB_KB_Config_Category::get_category_icon( $level_3_category_id, $categories_icons ); ?>

		<div class="epkb-ml-3-lvl__category">
			<section class="epkb-ml-3-lvl__title-container">                <?php
				if ( $sub_sub_category_icon['type'] == 'image' ) { ?>
					<img class="epkb-ml-3-lvl__cat-icon epkb-ml-3-lvl__cat-icon--image"
					     src="<?php echo esc_url( $sub_sub_category_icon['image_thumbnail_url'] ); ?>" alt="<?php echo esc_attr( $sub_sub_category_icon['image_alt'] ); ?>">    <?php
				} else { ?>
					<div class="epkb-ml-3-lvl__cat-icon epkb-ml-3-lvl__cat-icon--font epkbfa <?php echo esc_attr( $sub_sub_category_icon['name'] ); ?>"	data-kb-category-icon="<?php echo esc_attr( $sub_sub_category_icon['name'] ); ?>"></div>    <?php
				} ?>
				<div class="epkb-ml-3-lvl__text"><?php echo esc_html( $level_3_category_name ); ?></div>
			</section> <?php

			if ( empty( $articles_level_3_list ) ) {
				$articles_coming_soon_msg = $this->kb_config['category_empty_msg']; ?>
				<div class="epkb-ml-articles-coming-soon"><?php echo esc_html( $articles_coming_soon_msg ); ?></div> <?php
			} ?>

			<!-- Visible Articles -->
			<ul class="epkb-ml-articles-list">    <?php
				$article_index = 0;
				foreach ( $articles_level_3_list as $article_id => $article_title ) {

					if ( ! EPKB_Utilities::is_article_allowed_for_current_user( $article_id ) ) {
						continue;
					}

					if ( $article_index < $this->kb_config['nof_articles_displayed'] ) { ?>
						<li><?php $this->single_article_link( $article_title, $article_id, EPKB_Layout::DRILL_DOWN_LAYOUT ); ?></li>    <?php
					} else { ?>
						<li class="epkb-ml-article-hide"><?php $this->single_article_link( $article_title, $article_id, EPKB_Layout::DRILL_DOWN_LAYOUT ); ?></li>   <?php
					}

					$article_index ++;
				} ?>
			</ul> <?php

			$additional_articles_number = $article_index - $this->kb_config['nof_articles_displayed'];
			if ( $additional_articles_number > 0 ) { ?>
				<span class="epkb-ml-articles-show-more">
					<a href="#"><?php echo sprintf( esc_html( $this->kb_config['ml_categories_articles_show_more_text'] . ' (%s)' ), $additional_articles_number ); ?></a>
				</span>    <?php
			} ?>
		</div> <?php
	}

	/**
	 * Returns inline styles for Categories & Articles Module
	 *
	 * @param $kb_config
	 *
	 * @return string
	 */
	public static function get_inline_styles( $kb_config ) {


		$output = '
		/* CSS for Categories & Articles Module
		-----------------------------------------------------------------------*/';

		// Drill Down Layout ----------------------------------------------------------------------------/
		if ( $kb_config['ml_categories_articles_top_category_icon_bg_color_toggle'] == 'off' ) {
			$output .= '
			#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-1-lvl-categories-button-container .epkb-ml-1-lvl__cat-icon {
				background-color: transparent !important;
			}';
		}

		// Add General Typography
		if ( ! empty( $kb_config['general_typography']['font-family'] ) ) {
			$output .= '
			#epkb-ml-drill-down-layout .epkb-ml-1-lvl__cat-title,
			#epkb-ml-drill-down-layout .epkb-back-button__text {
				    ' . 'font-family:' . $kb_config['general_typography']['font-family'] . ' !important;' . '
				}';
		}

		$border_style = 'none';
		if ( $kb_config['section_border_width'] > 0 ) {
			$border_style = 'solid';
		}
		$output .= ' #epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-1-lvl__cat-container {
						border-color: ' . $kb_config['section_border_color'] . ' !important;
						border-width:' . $kb_config['section_border_width'] . 'px !important;
						border-radius:' . $kb_config['section_border_radius'] . 'px !important;
						border-style: ' . $border_style. ' !important; }';

		$output .= '

		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-1-lvl-categories-button-container .epkb-ml-1-lvl__cat-icon--font {
		    font-size: ' . $kb_config['section_head_category_icon_size'] . 'px;
		}
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-1-lvl-categories-button-container .epkb-ml-1-lvl__cat-title {
		    color: ' . $kb_config['section_head_font_color'] . ';
		}
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-1-lvl-categories-button-container .epkb-ml-1-lvl__cat-icon {
		    color: ' . $kb_config['section_head_category_icon_color'] . ';
		    background-color: ' . $kb_config['ml_categories_articles_top_category_icon_bg_color'] . ';
		    width: ' . ( $kb_config['section_head_category_icon_size'] + 40 ) . 'px;
		    height: ' . ( $kb_config['section_head_category_icon_size'] + 40 ) . 'px;
		}
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-article-inner .epkb-article__icon {
		    color: ' . $kb_config['article_icon_color'] . ';
		}
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-article-inner .epkb-article__text {
		    color: ' . $kb_config['article_font_color'] . ';
		}
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-1-lvl-categories-content-container,
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-2-lvl__cat-content {
		    background-color: ' . $kb_config['ml_categories_articles_article_bg_color'] . ';
		}
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-articles-show-more a {
		    color: ' . $kb_config['ml_categories_articles_article_show_more_color'] . ';
		}
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-1-lvl__cat-content .epkb-ml-1-lvl-desc-articles .epkb-ml-1-lvl__desc,
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-2-lvl__cat-content .epkb-ml-2-lvl__desc,
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-2-lvl__cat-content .epkb-ml-articles-coming-soon {
		    color: ' . $kb_config['section_head_description_font_color'] . ';
		}
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-back-button {
		    background-color: ' . $kb_config['ml_categories_articles_back_button_bg_color'] . '!important;
		}
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-back-button:hover {
		    background-color: ' . EPKB_Utilities::darken_hex_color( $kb_config['ml_categories_articles_back_button_bg_color'], 0.2 )  . '!important;
		}
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-1-lvl-categories-button-container .epkb-ml-1-lvl__cat-container,
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-1-lvl-categories-button-container .epkb-ml-1-lvl__cat-container:hover {
		    border-color: ' . $kb_config['section_border_color'] . ' !important;
		}
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-1-lvl-categories-button-container .epkb-ml-1-lvl__cat-container--active,
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-1-lvl-categories-button-container .epkb-ml-1-lvl__cat-container--active:hover {
		    box-shadow: 0 0 0 4px ' . $kb_config['section_border_color'] . ';
		}
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-2-lvl-categories-button-container .epkb-ml-2-lvl__cat-container,
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-2-lvl-categories-button-container .epkb-ml-2-lvl__cat-container:hover {
		    border-color: ' . $kb_config['section_border_color'] . ';
		}
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-2-lvl-categories-button-container .epkb-ml-2-lvl__cat-container--active {
		    box-shadow: 0px 1px 0 0px ' . $kb_config['section_border_color'] . ';
		}
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-2-lvl-categories-button-container .epkb-ml-2-lvl__cat-container .epkb-ml-2-lvl__cat-icon {
		    color: ' . $kb_config['section_category_icon_color'] . ';
		}
		#epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-2-lvl-categories-button-container .epkb-ml-2-lvl__cat-container .epkb-ml-2-lvl__cat-title {
		    color: ' . $kb_config['section_category_font_color'] . ';
		}';

		$output .= '
	    #epkb-ml__module-categories-articles #epkb-ml-drill-down-layout .epkb-ml-1-lvl-categories-content-container .epkb-ml-article-container {
	        padding-top: ' . $kb_config['article_list_spacing'] . 'px !important;
	        padding-bottom: ' . $kb_config['article_list_spacing'] . 'px !important;
            line-height: 1 !important;
	    }';

		return $output;
	}

	public function generate_kb_main_page() {
		// for compatibility reasons
	}
}