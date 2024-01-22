<?php

/**
 * List of taxonomies to exclude
 * @return array
 */
function wpsm_taxonomies_exclude_list() {
    return array_flip([
        'nav_menu', 'link_category', 'post_format',
        'elementor_library_type', 'elementor_library_category', 'action-group'
    ]);
}

/**
 * Taxonomies List
 * return the list of taxonomies
 *
 * @return array
 */
add_action('wp_ajax_wpsm_taxonomies_list', function () {
    // Do not include the default taxonomies in this list
    $exclude_list = wpsm_taxonomies_exclude_list();
    $response_data = [
        'results' => []
    ];

    $args = [];

    foreach ( get_taxonomies($args, 'objects') as $taxonomy => $object ) {
        if ( isset( $exclude_list[ $taxonomy ] ) ) {
            continue;
        }

        $taxonomy = esc_html( $taxonomy );
        $response_data['results'][] = [
            'id'    => $taxonomy,
            'text'  => esc_html( $object->label ),
            'slug'  => $taxonomy
        ];
    }

    wp_send_json_success( $response_data );
});

/**
 * Taxonomies Terms List
 * return the list of taxonomy term
 *
 * @return array
 */
add_action('wp_ajax_wpsm_taxonomy_terms', function () {
    // Do not include the default taxonomies in this list
    $response_data = [
        'results' => []
    ];

    if ( empty( $_POST['tax_name'] ) && empty( $_POST['taxonomy'] ) ) {
        wp_send_json_success( $response_data );
    }

    $taxonomy = isset( $_POST['tax_name'] ) ? sanitize_text_field($_POST['tax_name']) : sanitize_text_field($_POST['taxonomy']);
    \Elementor\WPSM_Content_Widget_Base::get_rehub_post_cat_list( $taxonomy );
});

add_action('wp_ajax_wpsm_taxonomy_terms_ids', function () {
    // Do not include the default taxonomies in this list
    $response_data = [
        'results' => []
    ];

    if ( empty( $_POST['tax_name'] ) && empty( $_POST['taxonomy'] ) ) {
        wp_send_json_success( $response_data );
    }

    $taxonomy = isset( $_POST['tax_name'] ) ? sanitize_text_field($_POST['tax_name']) : sanitize_text_field($_POST['taxonomy']);
    $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
    ]);

    foreach ( $terms as $term ) {
        $response_data['results'][] = [
            'id'    => $term->slug,
            'text'  => esc_html( $term->name ) . ' (' . $term->count . ')',
            'slug'  => $term->slug
        ];
    }

    wp_send_json_success( $response_data );
});

add_action( 'wp_ajax_rehub_users_id_list', function() {
    $search_term = '';
    if ( ! empty( $_POST['search'] ) ) {
        $search_term = wp_kses( $_POST['search'], [] ) . '*';
    }

    $args = [
        'search'         => $search_term,
    ];

    $response_data = [
        'results'       => [],
        'total_count'   => 0
    ];

    $users = new WP_User_Query( $args );
    $response_data['total_count'] = $users->total_users;

    if ( $users ) {
        foreach ( $users->results as $user ) {
            $response_data['results'][] = [
                'id'    => $user->ID,
                'text'  => esc_html( $user->user_nicename )
            ];
        }
    }

    wp_send_json_success( $response_data );
});

add_action( 'wp_ajax_rehub_wpsm_search_woo_attributes', function() {
    global $wpdb;

    $query = [
        "select" => "SELECT SQL_CALC_FOUND_ROWS attribute_id, attribute_name, attribute_label FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies",
        "where"  => "WHERE",
        "like"   => "attribute_name NOT LIKE %s",
        "offset" => "LIMIT %d, %d"
    ];

    $search_term = '';
    if ( ! empty( $_POST['search'] ) ) {
        $search_term = $wpdb->esc_like( $_POST['search'] ) . '%';
        $query['like'] = 'attribute_name LIKE %s';
    }

    $offset = 0;
    $search_limit = 100;
    if ( isset( $_POST['page'] ) && intval( $_POST['page'] ) && $_POST['page'] > 1 ) {
        $offset = $search_limit * absint( $_POST['page'] );
    }

    $final_query = $wpdb->prepare( implode(' ', $query ), $search_term, $offset, $search_limit );
    // Return saved values

    if ( ! empty( $_POST['saved'] ) && is_array( $_POST['saved'] ) ) {
        // $saved_ids = array_map('intval', $_POST['saved']);
        $saved_ids = array_filter( $_POST['saved'] );
        $placeholders = array_fill(0, count( $saved_ids ), '%s');
        $format = implode(', ', $placeholders);

        $new_query = [
            "select" => $query['select'],
            "where"  => $query['where'],
            "id"     => " attribute_name IN( $format )",
            // "order"  => "ORDER BY field(attribute_id, " . implode(",", $saved_ids) . ")"
        ];

        $final_query = $wpdb->prepare( implode(" ", $new_query), $saved_ids );
    }

    $results = $wpdb->get_results( $final_query );
    $total_results = $wpdb->get_row("SELECT FOUND_ROWS() as total_rows;");
    $response_data = [
        'results'       => [],
        'total_count'   => $total_results->total_rows
    ];

    if ( $results ) {
        foreach ( $results as $result ) {
            $response_data['results'][] = [
                'id'    => $result->attribute_name,
                'text'  => esc_html( $result->attribute_label )
            ];
        }
    }

    wp_send_json_success( $response_data );
});
