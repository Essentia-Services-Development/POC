<?php

/**
 *  Outputs the Search module for Modular Main Page.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class EPKB_ML_Search {

	private $kb_config;

	function __construct( $kb_config ) {
		$this->kb_config = $kb_config;
	}

	/**
	 * Display Search box - Classic Layout
	 */
	public function display_classic_search_layout() {  ?>

		<!-- Classic Search Layout -->
		<div id="epkb-ml-search-classic-layout">    <?php
			$this->display_search_title();  ?>
			<form id="epkb-ml-search-form" method="get" action="/">
				<input type="hidden" id="epkb_kb_id" value="<?php echo esc_attr( $this->kb_config['id'] ); ?>" >

				<!-- Search Input Box -->
				<div id="epkb-ml-search-box">
					<input class="epkb-ml-search-box__input" type="text" name="s" value="" aria-label="<?php echo esc_attr( $this->kb_config['search_box_hint'] ); ?>" placeholder="<?php echo esc_attr( $this->kb_config['search_box_hint'] ); ?>" aria-controls="epkb-ml-search-results" >
					<button class="epkb-ml-search-box__btn" type="submit">
                        <span class="epkb-ml-search-box__text"> <?php echo esc_html( $this->kb_config['search_button_name'] ); ?></span>
                        <span class="epkbfa epkbfa-spinner epkbfa-ml-loading-icon"></span>
                    </button>
				</div>

				<!-- Search Results -->
				<div id="epkb-ml-search-results" aria-live="polite"></div>
			</form>
		</div>  <?php
	}

	/**
	 * Display Search box - Modern Layout
	 */
	public function display_modern_search_layout() {   ?>

		<!-- Modern Search Layout -->
		<div id="epkb-ml-search-modern-layout">    <?php
			$this->display_search_title();  ?>
			<form id="epkb-ml-search-form" method="get" action="/">
				<input type="hidden" id="epkb_kb_id" value="<?php echo esc_attr( $this->kb_config['id'] ); ?>" >

				<!-- Search Input Box -->
				<div id="epkb-ml-search-box">
					<input class="epkb-ml-search-box__input" type="text" name="s" value="" aria-label="<?php echo esc_attr( $this->kb_config['search_box_hint'] ); ?>" placeholder="<?php echo esc_attr( $this->kb_config['search_box_hint'] ); ?>" aria-controls="epkb-ml-search-results" >
					<button class="epkb-ml-search-box__btn" type="submit">
                        <span class="epkbfa epkbfa-search epkbfa-ml-search-icon"></span>
                        <span class="epkbfa epkbfa-spinner epkbfa-ml-loading-icon"></span>
                    </button>
				</div>

				<!-- Search Results -->
				<div id="epkb-ml-search-results" aria-live="polite"></div>
			</form>
		</div>  <?php
    }

	/**
	 * Display HTML for Search Title
	 */
	private function display_search_title() {
		$search_title_tag = empty( $kb_config['search_title_html_tag'] ) ? 'div' : $kb_config['search_title_html_tag']; ?>
		<<?php echo $search_title_tag; ?> class="epkb-ml-search-title"><?php echo esc_html( $this->kb_config['search_title'] ); ?></<?php echo $search_title_tag; ?>>   <?php
	}

	/**
	 * Returns inline styles for Search Module
	 *
	 * @param $kb_config
	 * @param $is_article
	 * @return string
	 */
	public static function get_inline_styles( $kb_config, $is_article=false ) {

		$output = '
		/* CSS for Search Module
		-----------------------------------------------------------------------*/';

		// adjust for Article page
		if ( $is_article ) {
			$output .= '
			#eckb-article-header #epkb-ml__module-search {
				margin-bottom: ' . $kb_config['article_search_box_margin_bottom'] . 'px;
				padding-top: ' . $kb_config['article_search_box_padding_top'] . 'px;
				padding-bottom: ' . $kb_config['article_search_box_padding_bottom'] . 'px;
				background-color: ' . $kb_config['article_search_background_color'] . ';
			}
			#epkb-ml__module-search .epkb-ml-search-title {
				color: ' . $kb_config['article_search_title_font_color'] . ';
			}';
		} else {
			$output .= '
			#epkb-modular-main-page-container #epkb-ml__module-search {
				padding-top: ' . $kb_config['search_box_padding_top'] . 'px;
				padding-bottom: ' . $kb_config['search_box_padding_bottom'] . 'px;
				background-color: ' . $kb_config['search_background_color'] . ';
			}
			#epkb-ml__module-search .epkb-ml-search-title {
				color: ' . $kb_config['search_title_font_color'] . ';
			}';
		}

		return $output;
	}

	/**
	 * Returns HTML for given search results
	 *
	 * @param $search_results
	 * @return string
	 */
	public static function display_search_results_html( $search_results ) {
		ob_start(); ?>

		<ul>    <?php
			foreach ( $search_results as $article ) {

				$article_url = get_permalink( $article->ID );
				if ( empty( $article_url ) || is_wp_error( $article_url )) {
					continue;
				}

				// linked articles have their own icon
				$article_title_icon = 'ep_font_icon_document';
				if ( has_filter( 'eckb_single_article_filter' ) ) {
					$article_title_icon = apply_filters( 'eckb_article_icon_filter', $article_title_icon, $article->ID );
					$article_title_icon = empty( $article_title_icon ) ? 'epkbfa-file-text-o' : $article_title_icon;
				}   ?>

				<li>
					<a href="<?php echo esc_url( $article_url ); ?>" class="epkb-ml-article-container" data-kb-article-id="<?php echo esc_attr( $article->ID ); ?>">
                        <span class="epkb-article-inner">
                            <span class="epkb-article__icon epkbfa <?php echo esc_attr( $article_title_icon ); ?>" aria-hidden="true"></span>
                            <span class="epkb-article__title"><?php echo esc_html( $article->post_title ); ?></span>                           <?php 
							/* if ( ! empty( $article->post_excerpt ) ) { ?>
                                <span class="epkb-article__excerpt"><?php echo esc_html( $article->post_excerpt ); ?></span>                            <?php 
							} */ ?>
                        </span>
					</a>
				</li>   <?php
			}   ?>
		</ul>   <?php

		return ob_get_clean();
	}
}