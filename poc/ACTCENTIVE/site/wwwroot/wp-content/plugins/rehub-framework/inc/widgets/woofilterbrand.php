<?php 

use Automattic\WooCommerce\Internal\ProductAttributesLookup\Filterer;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Plugin Name: Woo Filter Brand
 */

add_action( 'widgets_init', 'rehub_better_woofilterbrand_load_widget' );

function rehub_better_woofilterbrand_load_widget() {
	register_widget( 'rehub_better_woofilterbrand_widget' );
}

class rehub_better_woofilterbrand_widget extends WC_Widget {

	public $chosen_attributes;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'woocommerce widget_layered_nav woocommerce-widget-layered-nav';
		$this->widget_description = __( 'Display a list of attributes to filter products in your store.', 'rehub-framework' );
		$this->widget_id          = 'rehub_woofilterbrand';
		$this->widget_name        = __( 'ReHub: Filter Products by Brand', 'rehub-framework' );
		parent::__construct();

		add_filter( 'woocommerce_product_query_tax_query', function( $tax_query ) {

			if ( ! empty( $_GET ) ) {
				foreach ( $_GET as $key => $value ) {
					if ( 0 === strpos( $key, 'filter_' ) ) {
						if ( 'filter_store' !== $key ) {
							continue;
						}

						$attribute    = wc_sanitize_taxonomy_name( str_replace( 'filter_', '', $key ) );
						$taxonomy     = wc_sanitize_taxonomy_name( $attribute );
						$filter_terms = ! empty( $value ) ? explode( ',', wc_clean( wp_unslash( $value ) ) ) : array();

						if ( empty( $filter_terms ) || ! taxonomy_exists( $taxonomy ) ) {
							continue;
						}

						$query_type = ! empty( $_GET[ 'query_type_' . $attribute ] ) && in_array( $_GET[ 'query_type_' . $attribute ], array( 'and', 'or' ), true ) ? wc_clean( wp_unslash( $_GET[ 'query_type_' . $attribute ] ) ) : '';

						$tax_query[] = array(
							'taxonomy'         => $taxonomy,
							'field'            => 'slug',
							'terms'            => array_map( 'sanitize_title', $filter_terms ),
							'operator'         => 'and' === $query_type ? 'AND' : 'IN',
							'include_children' => false,
						);
					}
				}
			}
			
			return $tax_query;
		}, 999 );
	}

	/**
	 * Updates a particular instance of a widget.
	 *
	 * @see WP_Widget->update
	 *
	 * @param array $new_instance New Instance.
	 * @param array $old_instance Old Instance.
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$this->init_settings();
		return parent::update( $new_instance, $old_instance );
	}

	/**
	 * Outputs the settings update form.
	 *
	 * @see WP_Widget->form
	 *
	 * @param array $instance Instance.
	 */
	public function form( $instance ) {
		$this->init_settings();
		parent::form( $instance );
	}

	/**
	 * Init settings after post types are registered.
	 */
	public function init_settings() {
		$this->settings = array(
			'title'        => array(
				'type'  => 'text',
				'std'   => __( 'Filter by', 'rehub-framework' ),
				'label' => __( 'Title', 'rehub-framework' ),
			),
			'display_type' => array(
				'type'    => 'select',
				'std'     => 'list',
				'label'   => __( 'Display type', 'rehub-framework' ),
				'options' => array(
					'list'     => __( 'List', 'rehub-framework' ),
					'dropdown' => __( 'Dropdown', 'rehub-framework' ),
				),
			),
			'query_type'   => array(
				'type'    => 'select',
				'std'     => 'and',
				'label'   => __( 'Query type', 'rehub-framework' ),
				'options' => array(
					'and' => __( 'AND', 'rehub-framework' ),
					'or'  => __( 'OR', 'rehub-framework' ),
				),
			),
		);
	}

	/**
	 * Get this widgets query type.
	 *
	 * @param array $instance Array of instance options.
	 * @return string
	 */
	protected function get_instance_query_type( $instance ) {
		return isset( $instance['query_type'] ) ? $instance['query_type'] : 'and';
	}

	/**
	 * Get this widgets display type.
	 *
	 * @param array $instance Array of instance options.
	 * @return string
	 */
	protected function get_instance_display_type( $instance ) {
		return isset( $instance['display_type'] ) ? $instance['display_type'] : 'list';
	}

	/**
	 * Output widget.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args Arguments.
	 * @param array $instance Instance.
	 */
	public function widget( $args, $instance ) {
		if ( ! is_shop() && ! is_product_taxonomy() ) {
			return;
		}

		$_chosen_attributes = $this->wc_get_layered_nav_chosen_attributes();
		$taxonomy           = 'store';
		$query_type         = $this->get_instance_query_type( $instance );
		$display_type       = $this->get_instance_display_type( $instance );

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		$terms = get_terms( $taxonomy, array( 'hide_empty' => '1' ) );

		if ( 0 === count( $terms ) ) {
			return;
		}

		ob_start();

		$this->widget_start( $args, $instance );

		if ( 'dropdown' === $display_type ) {
			wp_enqueue_script( 'selectWoo' );
			wp_enqueue_style( 'select2' );
			$found = $this->layered_nav_dropdown( $terms, $taxonomy, $query_type );
		} else {
			$found = $this->layered_nav_list( $terms, $taxonomy, $query_type );
		}

		$this->widget_end( $args );

		// Force found when option is selected - do not force found on taxonomy attributes.
		if ( ! is_tax() && is_array( $_chosen_attributes ) && array_key_exists( $taxonomy, $_chosen_attributes ) ) {
			$found = true;
		}

		if ( ! $found ) {
			ob_end_clean();
		} else {
			echo ob_get_clean(); // @codingStandardsIgnoreLine
		}
	}

	/**
	 * Return the currently viewed taxonomy name.
	 *
	 * @return string
	 */
	protected function get_current_taxonomy() {
		return is_tax() ? get_queried_object()->taxonomy : '';
	}

	/**
	 * Return the currently viewed term ID.
	 *
	 * @return int
	 */
	protected function get_current_term_id() {
		return absint( is_tax() ? get_queried_object()->term_id : 0 );
	}

	/**
	 * Return the currently viewed term slug.
	 *
	 * @return int
	 */
	protected function get_current_term_slug() {
		return absint( is_tax() ? get_queried_object()->slug : 0 );
	}

	/**
	 * Show dropdown layered nav.
	 *
	 * @param  array  $terms Terms.
	 * @param  string $taxonomy Taxonomy.
	 * @param  string $query_type Query Type.
	 * @return bool Will nav display?
	 */
	protected function layered_nav_dropdown( $terms, $taxonomy, $query_type ) {
		global $wp;
		$found = false;

		if ( $taxonomy !== $this->get_current_taxonomy() ) {
			$term_counts          = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );
			$_chosen_attributes   = $this->wc_get_layered_nav_chosen_attributes();
			$taxonomy_filter_name = wc_attribute_taxonomy_slug( $taxonomy );
			$taxonomy_label       = wc_attribute_label( $taxonomy );

			/* translators: %s: taxonomy name */
			$any_label      = apply_filters( 'woocommerce_layered_nav_any_label', sprintf( __( 'Any %s', 'rehub-framework' ), $taxonomy_label ), $taxonomy_label, $taxonomy );
			$multiple       = 'or' === $query_type;
			$current_values = isset( $_chosen_attributes[ $taxonomy ]['terms'] ) ? $_chosen_attributes[ $taxonomy ]['terms'] : array();

			if ( '' === get_option( 'permalink_structure' ) ) {
				$form_action = remove_query_arg( array( 'page', 'paged' ), add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
			} else {
				$form_action = preg_replace( '%\/page/[0-9]+%', '', home_url( user_trailingslashit( $wp->request ) ) );
			}

			echo '<form method="get" action="' . esc_url( $form_action ) . '" class="woocommerce-widget-layered-nav-dropdown">';
			echo '<select class="woocommerce-widget-layered-nav-dropdown dropdown_layered_nav_' . esc_attr( $taxonomy_filter_name ) . '"' . ( $multiple ? 'multiple="multiple"' : '' ) . '>';
			echo '<option value="">' . esc_html( $any_label ) . '</option>';

			foreach ( $terms as $term ) {

				// If on a term page, skip that term in widget list.
				if ( $term->term_id === $this->get_current_term_id() ) {
					continue;
				}

				// Get count based on current view.
				$option_is_set = in_array( $term->slug, $current_values, true );
				$count         = isset( $term_counts[ $term->term_id ] ) ? $term_counts[ $term->term_id ] : 0;

				// Only show options with count > 0.
				if ( 0 < $count ) {
					$found = true;
				} elseif ( 0 === $count && ! $option_is_set ) {
					continue;
				}

				echo '<option value="' . esc_attr( urldecode( $term->slug ) ) . '" ' . selected( $option_is_set, true, false ) . '>' . esc_html( $term->name ) . '</option>';
			}

			echo '</select>';

			if ( $multiple ) {
				echo '<button class="woocommerce-widget-layered-nav-dropdown__submit" type="submit" value="' . esc_attr__( 'Apply', 'rehub-framework' ) . '">' . esc_html__( 'Apply', 'rehub-framework' ) . '</button>';
			}

			if ( 'or' === $query_type ) {
				echo '<input type="hidden" name="query_type_' . esc_attr( $taxonomy_filter_name ) . '" value="or" />';
			}

			echo '<input type="hidden" name="filter_' . esc_attr( $taxonomy_filter_name ) . '" value="' . esc_attr( implode( ',', $current_values ) ) . '" />';
			echo wc_query_string_form_fields( null, array( 'filter_' . $taxonomy_filter_name, 'query_type_' . $taxonomy_filter_name ), '', true ); // @codingStandardsIgnoreLine
			echo '</form>';

			wc_enqueue_js(
				"
				// Update value on change.
				jQuery( '.dropdown_layered_nav_" . esc_js( $taxonomy_filter_name ) . "' ).on( 'change', function() {
					var slug = jQuery( this ).val();
					jQuery( ':input[name=\"filter_" . esc_js( $taxonomy_filter_name ) . "\"]' ).val( slug );

					// Submit form on change if standard dropdown.
					if ( ! jQuery( this ).attr( 'multiple' ) ) {
						jQuery( this ).closest( 'form' ).trigger( 'submit' );
					}
				});

				// Use Select2 enhancement if possible
				if ( jQuery().selectWoo ) {
					var wc_layered_nav_select = function() {
						jQuery( '.dropdown_layered_nav_" . esc_js( $taxonomy_filter_name ) . "' ).selectWoo( {
							placeholder: decodeURIComponent('" . rawurlencode( (string) wp_specialchars_decode( $any_label ) ) . "'),
							minimumResultsForSearch: 5,
							width: '100%',
							allowClear: " . ( $multiple ? 'false' : 'true' ) . ",
							language: {
								noResults: function() {
									return '" . esc_js( _x( 'No matches found', 'enhanced select', 'rehub-framework' ) ) . "';
								}
							}
						} );
					};
					wc_layered_nav_select();
				}
			"
			);
		}

		return $found;
	}

	public function wc_get_layered_nav_chosen_attributes() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! is_array( $this->chosen_attributes ) ) {
			$this->chosen_attributes = array();

			if ( ! empty( $_GET ) ) {
				foreach ( $_GET as $key => $value ) {
					if ( 0 === strpos( $key, 'filter_' ) ) {
						if ( 'filter_store' !== $key ) {
							continue;
						}

						$attribute    = wc_sanitize_taxonomy_name( str_replace( 'filter_', '', $key ) );
						$taxonomy     = wc_sanitize_taxonomy_name( $attribute );
						$filter_terms = ! empty( $value ) ? explode( ',', wc_clean( wp_unslash( $value ) ) ) : array();

						if ( empty( $filter_terms ) || ! taxonomy_exists( $taxonomy ) ) {
							continue;
						}

						$query_type                                    = ! empty( $_GET[ 'query_type_' . $attribute ] ) && in_array( $_GET[ 'query_type_' . $attribute ], array( 'and', 'or' ), true ) ? wc_clean( wp_unslash( $_GET[ 'query_type_' . $attribute ] ) ) : '';
						$this->chosen_attributes[ $taxonomy ]['terms'] = array_map( 'sanitize_title', $filter_terms ); // Ensures correct encoding.
						$this->chosen_attributes[ $taxonomy ]['query_type'] = $query_type ? $query_type : apply_filters( 'woocommerce_layered_nav_default_query_type', 'and' );
					}
				}
			}
		}

		return $this->chosen_attributes;
	}

	/**
	 * Count products within certain terms, taking the main WP query into consideration.
	 *
	 * This query allows counts to be generated based on the viewed products, not all products.
	 *
	 * @param  array  $term_ids Term IDs.
	 * @param  string $taxonomy Taxonomy.
	 * @param  string $query_type Query Type.
	 * @return array
	 */
	protected function get_filtered_term_product_counts( $term_ids, $taxonomy, $query_type ) {
		return wc_get_container()->get( Filterer::class )->get_filtered_term_product_counts( $term_ids, $taxonomy, $query_type );
	}

	/**
	 * Wrapper for WC_Query::get_main_tax_query() to ease unit testing.
	 *
	 * @since 4.4.0
	 * @return array
	 */
	protected function get_main_tax_query() {
		return WC_Query::get_main_tax_query();
	}

	/**
	 * Wrapper for WC_Query::get_main_search_query_sql() to ease unit testing.
	 *
	 * @since 4.4.0
	 * @return string
	 */
	protected function get_main_search_query_sql() {
		return WC_Query::get_main_search_query_sql();
	}

	/**
	 * Wrapper for WC_Query::get_main_search_queryget_main_meta_query to ease unit testing.
	 *
	 * @since 4.4.0
	 * @return array
	 */
	protected function get_main_meta_query() {
		return WC_Query::get_main_meta_query();
	}

	/**
	 * Show list based layered nav.
	 *
	 * @param  array  $terms Terms.
	 * @param  string $taxonomy Taxonomy.
	 * @param  string $query_type Query Type.
	 * @return bool   Will nav display?
	 */
	protected function layered_nav_list( $terms, $taxonomy, $query_type ) {
		// List display.
		echo '<ul class="woocommerce-widget-layered-nav-list">';

		$term_counts        = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );
		$_chosen_attributes = $this->wc_get_layered_nav_chosen_attributes();
		$found              = false;
		$base_link          = $this->get_current_page_url();

		foreach ( $terms as $term ) {
			$current_values = isset( $_chosen_attributes[ $taxonomy ]['terms'] ) ? $_chosen_attributes[ $taxonomy ]['terms'] : array();
			$option_is_set  = in_array( $term->slug, $current_values, true );
			if('yes' === get_option( 'woocommerce_attribute_lookup_enabled' )){
				$count = $term->count;
			}else{
				$count          = isset( $term_counts[ $term->term_id ] ) ? $term_counts[ $term->term_id ] : 0;
			}
// print_r( [ $term, $this->get_current_term_id() ] );exit()
			// Skip the term for the current archive.
			if ( $this->get_current_term_id() === $term->term_id ) {
				continue;
			}

			// Only show options with count > 0.
			if ( 0 < $count ) {
				$found = true;
			} elseif ( 0 === $count && ! $option_is_set ) {
				continue;
			}

			$filter_name = 'filter_' . wc_attribute_taxonomy_slug( $taxonomy );

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$current_filter = isset( $_GET[ $filter_name ] ) ? explode( ',', wc_clean( wp_unslash( $_GET[ $filter_name ] ) ) ) : array();
			$current_filter = array_map( 'sanitize_title', $current_filter );
// print_r( $current_filter );
			if ( ! in_array( $term->slug, $current_filter, true ) ) {
				$current_filter[] = $term->slug;
			}
// print_r( $current_filter );exit();
			$link = remove_query_arg( $filter_name, $base_link );

			// Add current filters to URL.
			foreach ( $current_filter as $key => $value ) {
				// Exclude query arg for current term archive term.
				if ( $value === $this->get_current_term_slug() ) {
					unset( $current_filter[ $key ] );
				}

				// Exclude self so filter can be unset on click.
				if ( $option_is_set && $value === $term->slug ) {
					unset( $current_filter[ $key ] );
				}
			}
// print_r( $current_filter );exit();
			if ( ! empty( $current_filter ) ) {
				// asort( $current_filter );
				$link = add_query_arg( $filter_name, implode( ',', $current_filter ), $link );

				// Add Query type Arg to URL.
				if ( 'or' === $query_type && ! ( 1 === count( $current_filter ) && $option_is_set ) ) {
					$link = add_query_arg( 'query_type_' . wc_attribute_taxonomy_slug( $taxonomy ), 'or', $link );
				}
				$link = str_replace( '%2C', ',', $link );
			}
// print_r( $link );exit();
			if ( $count > 0 || $option_is_set ) {
				$link      = apply_filters( 'woocommerce_layered_nav_link', $link, $term, $taxonomy );
				$term_html = '<a rel="nofollow" href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a>';
			} else {
				$link      = false;
				$term_html = '<span>' . esc_html( $term->name ) . '</span>';
			}

			if('yes' === get_option( 'woocommerce_attribute_lookup_enabled' )){
				$term_html .= ' ' . apply_filters( 'woocommerce_layered_nav_count', '', $count, $term );
			}else{
				$term_html .= ' ' . apply_filters( 'woocommerce_layered_nav_count', '<span class="count">(' . absint( $count ) . ')</span>', $count, $term );
			}


			echo '<li class="woocommerce-widget-layered-nav-list__item wc-layered-nav-term ' . ( $option_is_set ? 'woocommerce-widget-layered-nav-list__item--chosen chosen' : '' ) . '">';
			echo apply_filters( 'woocommerce_layered_nav_term_html', $term_html, $term, $link, $count );
			echo '</li>';
		}

		echo '</ul>';

		return $found;
	}
}