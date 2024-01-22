<?php

/**
 * Shortcode - Lists KB articles like FAQ block with drop-down panels.
 *
 * @copyright   Copyright (c) 2022, Echo Plugins
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class EPKB_Faqs_Shortcode {

	public function __construct() {
		add_shortcode( 'epkb-faqs', array( 'EPKB_Faqs_Shortcode', 'output_shortcode' ) );
	}

	public static function output_shortcode( $attributes ) {
		global $eckb_kb_id, $output_kb_faq_shortcode;

		// we are inside nested shortcode
		if ( ! empty( $output_kb_faq_shortcode ) ) {
			return '';
		}

		wp_enqueue_style( 'epkb-shortcodes' );
		wp_enqueue_script( 'epkb-faq-shortcode-scripts' );

		$kb_id = empty( $attributes['kb_id'] ) ? ( empty( $eckb_kb_id ) ? EPKB_KB_Config_DB::DEFAULT_KB_ID : $eckb_kb_id ) : $attributes['kb_id'];
		$kb_id = EPKB_Core_Utilities::sanitize_kb_id( $kb_id );

		$kb_config = epkb_get_instance()->kb_config_obj->get_kb_config( $kb_id );

		$preset = empty( $attributes['preset'] ) ? '' : trim($attributes['preset']);
		switch ( $preset ) {
			case 'Boxed':
				$presetClass = 'epkb-faqs-shortcode-preset-boxed';
				break;
			case 'Grey Box':
				$presetClass = 'epkb-faqs-shortcode-preset-grey-box';
				break;
			case 'Grey Box Dark':
				$presetClass = 'epkb-faqs-shortcode-preset-grey-box-dark';
				break;
			default:
				$presetClass = 'epkb-faqs-shortcode-preset-basic';
				break;
		}

		$category_seq_data = EPKB_Utilities::get_kb_option( $kb_id, EPKB_Categories_Admin::KB_CATEGORIES_SEQ_META, array(), true );
		$articles_seq_data = EPKB_Utilities::get_kb_option( $kb_id, EPKB_Articles_Admin::KB_ARTICLES_SEQ_META, array(), true );

		// for WPML filter categories and articles given active language
		if ( EPKB_Utilities::is_wpml_enabled( $kb_config ) ) {
			$category_seq_data = EPKB_WPML::apply_category_language_filter( $category_seq_data );
			$articles_seq_data = EPKB_WPML::apply_article_language_filter( $articles_seq_data );
		}

		$stored_ids_obj = new EPKB_Categories_Array( $category_seq_data ); // normalizes the array as well
		$allowed_categories_ids = $stored_ids_obj->get_all_keys();

		// No categories found - message only for admins
		if ( empty( $allowed_categories_ids ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				return __( 'FAQs shortcode: No categories with articles found.', 'echo-knowledge-base' );
			}
			return '';
		}

		// remove epkb filter
		remove_filter( 'the_content', array( 'EPKB_Layouts_Setup', 'get_kb_page_output_hook' ), 99999 );

		// for empty categories parameters show all
		$included_categories = empty( $attributes['category_ids'] ) ? [] : explode( ',', $attributes['category_ids'] );
		if ( empty( $included_categories ) ) {
			$included_categories = array_keys( $allowed_categories_ids );
		}

		// get current post id to exclude it from articles to prevent display issues
		global $post;
		$current_post_id = empty( $post ) || empty( $post->ID ) ? 0 : $post->ID;

		// all nested faq shortcodes will be ignored
		$output_kb_faq_shortcode = true;
		$faq_container_class = empty( $attributes['class'] ) ? '' : ' ' . $attributes['class'];

        // init FAQ schema json
		$faq_schema_json = [
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => [],
		];

		ob_start(); ?>
		<div class="epkb-faqs-container<?php echo esc_html( $faq_container_class ) . ' ' . esc_html( $presetClass ); ?>"><?php

			foreach( $included_categories as $include_category_id ) {

				if ( empty( $articles_seq_data[$include_category_id] ) ) {
					continue;
				}

				if ( empty( $allowed_categories_ids[$include_category_id] ) ) {
					continue;
				}

				foreach ( $articles_seq_data[$include_category_id] as $article_id => $article_title ) {

					// category title/description or current post
					if ( $article_id == 0 || $article_id == 1 || $current_post_id == $article_id ) {
						continue;
					}

					// exclude linked articles
					$article = get_post( $article_id );

					// disallow article that failed to retrieve
					if ( empty( $article ) || empty( $article->post_status ) ) {
						unset( $articles_seq_data[$include_category_id][$article_id] );
						continue;
					}

					if ( EPKB_Utilities::is_link_editor( $article ) ) {
						unset( $articles_seq_data[$include_category_id][$article_id] );
						continue;
					}

					// exclude not allowed for Access Manager articles
					if ( ! EPKB_Utilities::is_article_allowed_for_current_user( $article_id ) ) {
						unset( $articles_seq_data[$include_category_id][$article_id] );
					}
				}

				// not empty term but with hidden articles for the user
				if ( empty( $articles_seq_data[$include_category_id] ) ) {
					continue;
				}   ?>

				<div class="epkb-faqs-cat-container" id="epkb-faqs-cat-<?php echo $include_category_id; ?>">
					<div class="epkb-faqs__cat-header">
						<h3><?php echo esc_html( $articles_seq_data[$include_category_id][0] ); ?></h3>
					</div>  <?php

					foreach( $articles_seq_data[$include_category_id] as $article_id => $article_title ) {

						if ( $article_id == 0 || $article_id == 1 || $current_post_id == $article_id ) {
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
						if ( $kb_config['faq_shortcode_content_mode'] == 'content' ) {
							$post_content = $article->post_content;
						}

						if ( $kb_config['faq_shortcode_content_mode'] == 'excerpt' ) {
							$post_content = $article->post_excerpt;
						}

                        // add article title and content to the FAQ schema
						if ( $kb_config['faq_schema_toggle'] == 'on' ) {
							$faq_schema_json['mainEntity'][] = array(
								'@type' => 'Question',
								'name' => get_the_title( $article ),
								'acceptedAnswer' => array(
									'@type' => 'Answer',
									'text' => wp_strip_all_tags( $post_content ),
								)
							);
						}   ?>

						<div class="epkb-faqs__item-container" id="epkb-faqs-article-<?php echo $article->ID; ?>">
							<div class="epkb-faqs__item__question">
								<div class="epkb-faqs__item__question__icon epkbfa epkbfa-plus-square"></div>
								<div class="epkb-faqs__item__question__icon epkbfa epkbfa-minus-square"></div>
								<div class="epkb-faqs__item__question__text"><?php echo get_the_title( $article ); ?></div>
							</div>
							<div class="epkb-faqs__item__answer">
								<div class="epkbs-faqs__item__answer__text">    <?php
									$content = apply_filters( 'the_content', $post_content );
									$content = str_replace( ']]>', ']]&gt;', $content );
									echo $content;  ?>
								</div>
							</div>
						</div>  <?php
					}   ?>
				</div>  <?php
			}

			if ( $kb_config['faq_schema_toggle'] == 'on' ) {    ?>
				<script type="application/ld+json"><?php echo wp_json_encode( $faq_schema_json ); ?></script>   <?php
			}   ?>
		</div>  <?php

		$html = ob_get_clean();
		$output_kb_faq_shortcode = false;

		// add epkb filter back
		add_filter( 'the_content', array( 'EPKB_Layouts_Setup', 'get_kb_page_output_hook' ), 99999 );

		return $html;
	}
}
