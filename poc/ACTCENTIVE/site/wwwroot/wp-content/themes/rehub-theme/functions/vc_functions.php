<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
//////////////////////////////////////////////////////////////////
// Visual Composer functions
//////////////////////////////////////////////////////////////////

//REMOVE SOME DEFAULT ELEMENTS
vc_remove_element( 'vc_images_carousel' );
vc_remove_element( 'vc_teaser_grid' );
vc_remove_element( 'vc_posts_grid' );
vc_remove_element( 'vc_carousel' );
vc_remove_element( 'vc_posts_slider' );
vc_remove_element( 'vc_wp_recentcomments' );
vc_remove_element( 'vc_wp_calendar' );
vc_remove_element( 'vc_wp_tagcloud' );
vc_remove_element( 'vc_wp_text' );
vc_remove_element( 'vc_wp_meta' );
vc_remove_element( 'vc_wp_posts' );
vc_remove_element( 'vc_wp_pages' );
vc_remove_element( 'vc_wp_links' );
vc_remove_element( 'vc_wp_archives' );
vc_remove_element( 'vc_cta_button' );
vc_remove_element( 'vc_basic_grid' );
vc_remove_element( 'vc_media_grid' );
vc_remove_element( 'vc_masonry_grid' );
vc_remove_element( 'vc_masonry_media_grid' );
vc_remove_element( 'vc_hoverbox' );
function rehub_vc_remove_woocommerce() {
    if ( class_exists('Woocommerce') ) {
        vc_remove_element( 'woocommerce_cart' );
        vc_remove_element( 'woocommerce_checkout' );
        vc_remove_element( 'woocommerce_order_tracking' );
        vc_remove_element( 'woocommerce_my_account' );
        vc_remove_element( 'recent_products' );
        vc_remove_element( 'featured_products' );
        vc_remove_element( 'product' );
        vc_remove_element( 'products' );

        vc_remove_element( 'add_to_cart_url' );
        vc_remove_element( 'product_page' );
        vc_remove_element( 'product_category' );
        vc_remove_element( 'product_categories' );
        vc_remove_element( 'sale_products' );
        vc_remove_element( 'best_selling_products' );
        vc_remove_element( 'top_rated_products' );
        vc_remove_element( 'product_attribute' );
    }
}
add_action( 'vc_build_admin_page', 'rehub_vc_remove_woocommerce', 11 );
add_action( 'vc_load_shortcode', 'rehub_vc_remove_woocommerce', 11 );


add_filter( 'vc_load_default_templates', 'rh_delete_vc_default_templates' ); // we deleted default templates of VC
function rh_delete_vc_default_templates( $data ) {
    return array(); 
}

add_action( 'vc_load_default_templates_action','rh_custom_default_templates_for_vc' ); // We added our templates
function rh_custom_default_templates_for_vc() {
    include (rh_locate_template( 'functions/vc_templates/basic_templates.php' ) );
}

//Set default post types
vc_set_default_editor_post_types( array('page') );

$dir_for_vc = get_template_directory() . '/functions/vc_templates';
vc_set_shortcodes_templates_dir( $dir_for_vc );

//WIDGET BLOCK
vc_remove_param("vc_widget_sidebar", "title");

//ROW BLOCK
add_action( 'vc_after_init_base', 'add_more_rehub_layouts' );
function add_more_rehub_layouts() {
    global $vc_row_layouts;
    array_push( $vc_row_layouts, array(
        'cells' => '34_14',
        'mask' => '212',
        'title' => '3/4 + 1/4',
        'icon_class' => 'l_34_14')
    );    
}

vc_remove_param("vc_row", "full_width");
vc_add_params("vc_row", array(
    array(
        "type" => "checkbox",
        "class" => "",
        "group" => esc_html__("Type of row", "rehub-theme"),
        "heading" => esc_html__("Enable Optimized for Reading fonts and width?", "rehub-theme"),
        "value" => array(__("Yes", "rehub-theme") => "true" ),
        "param_name" => "optreading",
        "description" => esc_html__("This will enable more compact width and more big fonts. ", "rehub-theme")
    ),    
    array(
        "type" => "checkbox",
        "class" => "",
        "group" => esc_html__("Type of row", "rehub-theme"),
        "heading" => esc_html__("Container with sidebar?", "rehub-theme"),
        "value" => array(__("Yes", "rehub-theme") => "true" ),
        "param_name" => "rehub_container",
        "description" => esc_html__("Is this container with sidebar? Enable this option and use 2/3 + 1/3 layout for better compatibility if you want to add sidebar widget area.", "rehub-theme")
    ),
    array(
        "type" => "checkbox",
        "class" => "",
        "group" => esc_html__("Type of row", "rehub-theme"),
        "heading" => esc_html__("Make sidebar with smart scroll function?", "rehub-theme"),
        "value" => array(__("Yes", "rehub-theme") => "true" ),
        "param_name" => "stickysidebar",
        'dependency' => array(
            'element' => 'rehub_container',
            'not_empty' => true,
        ),
    ),    
    array(
        "type" => "checkbox",
        "class" => "",
        "group" => esc_html__("Type of row", "rehub-theme"),        
        "heading" => esc_html__("Disable center alignment?", "rehub-theme"),
        "value" => array(__("Yes", "rehub-theme") => "true" ),
        "param_name" => "disable_centered_container",
        "description" => esc_html__("By default, all post modules have center alignment and max width as 1200px, you can disable this.", "rehub-theme")
    )        

));

$setting_row = array (
  'show_settings_on_create' => true,
);
$deprecate_sep = array (
  'deprecated' => '4.9',
);
vc_map_update( 'vc_row', $setting_row ); 
vc_map_update( 'vc_text_separator', $deprecate_sep );

//Filter autocompletes for default modules
$autocompletemoduleids = array('wpsm_offer_list', 'small_thumb_loop', 'regular_blog_loop', 'grid_loop_mod', 'columngrid_loop', 'compactgrid_loop_mod', 'wpsm_featured', 'post_carousel_mod', 'wpsm_recent_posts_list', 'wpsm_three_col_posts');
foreach ($autocompletemoduleids as $autocompletemoduleid) {
    add_filter( 'vc_autocomplete_'.$autocompletemoduleid.'_ids_callback',
    'rehub_post_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_'.$autocompletemoduleid.'_ids_render',
    'rehub_post_render_vc', 10, 1 );
    add_filter( 'vc_autocomplete_'.$autocompletemoduleid.'_cat_callback',
    'rehub_cat_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_'.$autocompletemoduleid.'_cat_render',
    'rehub_cat_render_vc', 10, 1 );
    add_filter( 'vc_autocomplete_'.$autocompletemoduleid.'_cat_exclude_callback',
    'rehub_cat_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_'.$autocompletemoduleid.'_cat_exclude_render',
    'rehub_cat_render_vc', 10, 1 );  
    add_filter( 'vc_autocomplete_'.$autocompletemoduleid.'_tag_callback',
    'rehub_tag_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_'.$autocompletemoduleid.'_tag_render',
    'rehub_tag_render_vc', 10, 1 );
    add_filter( 'vc_autocomplete_'.$autocompletemoduleid.'_tag_exclude_callback',
    'rehub_tag_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_'.$autocompletemoduleid.'_tag_exclude_render',
    'rehub_tag_render_vc', 10, 1 );          
}

//Filter autocompletes for news modules
$autocompletemodulenews = array('news_with_thumbs_mod');
foreach ($autocompletemodulenews as $autocompletemodulenew) {

    add_filter( 'vc_autocomplete_'.$autocompletemodulenew.'_module_cats_callback',
    'rehub_cat_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_'.$autocompletemodulenew.'_module_cats_render',
    'rehub_cat_render_vc', 10, 1 );
    add_filter( 'vc_autocomplete_'.$autocompletemodulenew.'_cat_exclude_callback',
    'rehub_cat_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_'.$autocompletemodulenew.'_cat_exclude_render',
    'rehub_cat_render_vc', 10, 1 );
    add_filter( 'vc_autocomplete_'.$autocompletemodulenew.'_module_tags_callback',
    'rehub_tag_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_'.$autocompletemodulenew.'_module_tags_render',
    'rehub_tag_render_vc', 10, 1 );
    add_filter( 'vc_autocomplete_'.$autocompletemodulenew.'_tag_exclude_callback',
    'rehub_tag_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_'.$autocompletemodulenew.'_tag_exclude_render',
    'rehub_tag_render_vc', 10, 1 );    
}

//Filter autocompletes for two col news module
$numberarraytwocols = array('first', 'second');
foreach ($numberarraytwocols as $numberarraytwocol) {
    add_filter( 'vc_autocomplete_two_col_news_module_cats_'.$numberarraytwocol.'_callback',
    'rehub_cat_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_two_col_news_module_cats_'.$numberarraytwocol.'_render',
    'rehub_cat_render_vc', 10, 1 );
    add_filter( 'vc_autocomplete_two_col_news_cat_exclude_'.$numberarraytwocol.'_callback',
    'rehub_cat_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_two_col_news_cat_exclude_'.$numberarraytwocol.'_render',
    'rehub_cat_render_vc', 10, 1 );

    add_filter( 'vc_autocomplete_two_col_news_module_tags_'.$numberarraytwocol.'_callback',
    'rehub_tag_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_two_col_news_module_tags_'.$numberarraytwocol.'_render',
    'rehub_tag_render_vc', 10, 1 );
    add_filter( 'vc_autocomplete_two_col_news_tag_exclude_'.$numberarraytwocol.'_callback',
    'rehub_tag_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_two_col_news_tag_exclude_'.$numberarraytwocol.'_render',
    'rehub_tag_render_vc', 10, 1 );        
}

//Filter autocompletes for tab news module
$numberarrays = array('first', 'second', 'third', 'fourth');
foreach ($numberarrays as $numberarray) {
    add_filter( 'vc_autocomplete_tab_mod_module_cats_'.$numberarray.'_callback',
    'rehub_cat_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_tab_mod_module_cats_'.$numberarray.'_render',
    'rehub_cat_render_vc', 10, 1 );
    add_filter( 'vc_autocomplete_tab_mod_cat_exclude_'.$numberarray.'_callback',
    'rehub_cat_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_tab_mod_cat_exclude_'.$numberarray.'_render',
    'rehub_cat_render_vc', 10, 1 );  
    add_filter( 'vc_autocomplete_tab_mod_module_tags_'.$numberarray.'_callback',
    'rehub_tag_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_tab_mod_module_tags_'.$numberarray.'_render',
    'rehub_tag_render_vc', 10, 1 );
    add_filter( 'vc_autocomplete_tab_mod_tag_exclude_'.$numberarray.'_callback',
    'rehub_tag_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_tab_mod_tag_exclude_'.$numberarray.'_render',
    'rehub_tag_render_vc', 10, 1 );        
}

//Filter autocompletes for woo modules
$autocompletewooids = array('wpsm_woorows', 'wpsm_woolist', 'wpsm_woogrid', 'wpsm_woocolumns', 'woo_mod', 'wpsm_woofeatured');
foreach ($autocompletewooids as $autocompletewooid) {
    add_filter( 'vc_autocomplete_'.$autocompletewooid.'_ids_callback',
        'rehub_woopost_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_'.$autocompletewooid.'_ids_render',
        'rehub_woopost_render_vc', 10, 1 );   
    add_filter( 'vc_autocomplete_'.$autocompletewooid.'_cat_callback',
        'rehub_catwoo_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_'.$autocompletewooid.'_cat_render',
        'rehub_catwoo_render_vc', 10, 1 ); 
    add_filter( 'vc_autocomplete_'.$autocompletewooid.'_tag_callback',
        'rehub_tagwoo_search_vc', 10, 1 );
    add_filter( 'vc_autocomplete_'.$autocompletewooid.'_tag_render',
        'rehub_tagwoo_render_vc', 10, 1 );                     
}
add_filter( 'vc_autocomplete_wpsm_woobox_id_callback',
    'rehub_woopost_search_vc', 10, 1 );
add_filter( 'vc_autocomplete_wpsm_woobox_id_render',
    'rehub_woopost_render_vc', 10, 1 );

add_filter( 'vc_autocomplete_wpsm_woo_versus_ids_callback',
    'rehub_woopost_search_vc', 10, 1 );
add_filter( 'vc_autocomplete_wpsm_woo_versus_ids_render',
    'rehub_woopost_render_vc', 10, 1 );

add_filter( 'vc_autocomplete_wpsm_woo_versus_attr_callback',
    'rehub_search_woo_attributes', 10, 1 );
add_filter( 'vc_autocomplete_wpsm_woo_versus_attr_render',
    'rehub_render_woo_attributes', 10, 1 );

function rehub_post_search_vc( $search_string ) {
    $query = $search_string;
    $data = array();
    $args = array( 's' => $query, 'post_type' => 'any' );
    $args['vc_search_by_title_only'] = true;
    $args['numberposts'] = - 1;
    if ( strlen( $args['s'] ) == 0 ) {
        unset( $args['s'] );
    }
    add_filter( 'posts_search', 'vc_search_by_title_only', 500, 2 );
    $posts = get_posts( $args );
    foreach ( $posts as $post ) {
        $data[] = array(
            'value' => $post->ID,
            'label' => $post->post_title,
        );
    }
    return $data;
}

function rehub_post_render_vc( $value ) {
    $post = get_post( $value['value'] );

    return is_null( $post ) ? false : array(
        'label' => $post->post_title,
        'value' => $post->ID,
    );
}

function rehub_woopost_search_vc( $search_string ) {
    $query = $search_string;
    $data = array();
    $args = array( 's' => $query, 'post_type' => 'product' );
    $args['vc_search_by_title_only'] = true;
    $args['numberposts'] = - 1;
    if ( strlen( $args['s'] ) == 0 ) {
        unset( $args['s'] );
    }
    add_filter( 'posts_search', 'vc_search_by_title_only', 500, 2 );
    $posts = get_posts( $args );
    foreach ( $posts as $post ) {
        $data[] = array(
            'value' => $post->ID,
            'label' => $post->post_title,
        );
    }
    return $data;
}

function rehub_woopost_render_vc( $value ) {
    $post = get_post( $value['value'] );

    return is_null( $post ) ? false : array(
        'label' => $post->post_title,
        'value' => $post->ID,
    );
}

function rehub_cat_search_vc( $query, $slug = false ) {
    global $wpdb;
    $cat_id = (int) $query;
    $query = trim( $query );
    $query = '%'. $wpdb->esc_like($query) .'%';
    $post_meta_infos = $wpdb->get_results($wpdb->prepare("SELECT a.term_id AS id, b.name as name, b.slug AS slug FROM $wpdb->term_taxonomy AS a INNER JOIN $wpdb->terms AS b ON b.term_id = a.term_id WHERE a.taxonomy = 'category' AND (a.term_id = %d OR b.slug LIKE %s OR b.name LIKE %s )", $cat_id > 0 ? $cat_id : -1, $query, $query), ARRAY_A);

    $result = array();
    if ( is_array( $post_meta_infos ) && ! empty( $post_meta_infos ) ) {
        foreach ( $post_meta_infos as $value ) {
            $data = array();
            $data['value'] = $slug ? $value['slug'] : $value['id'];
            $data['label'] = esc_html__( 'Id', 'rehub-theme' ) . ': ' . $value['id'] . ( ( strlen( $value['name'] ) > 0 ) ? ' - ' . esc_html__( 'Name', 'rehub-theme' ) . ': ' . $value['name'] : '' ) . ( ( strlen( $value['slug'] ) > 0 ) ? ' - ' . esc_html__( 'Slug', 'rehub-theme' ) . ': ' . $value['slug'] : '' );
            $result[] = $data;
        }
    }
    return $result;
}

function rehub_tag_search_vc( $query, $slug = false ) {
    global $wpdb;
    $cat_id = (int) $query;
    $query = trim( $query );
    $post_meta_infos = $wpdb->get_results( $wpdb->prepare( "SELECT a.term_id AS id, b.name as name, b.slug AS slug
                    FROM {$wpdb->term_taxonomy} AS a
                    INNER JOIN {$wpdb->terms} AS b ON b.term_id = a.term_id
                    WHERE a.taxonomy = 'post_tag' AND (a.term_id = '%d' OR b.slug LIKE '%%%s%%' OR b.name LIKE '%%%s%%' )", $cat_id > 0 ? $cat_id : - 1, stripslashes( $query ), stripslashes( $query ) ), ARRAY_A );

    $result = array();
    if ( is_array( $post_meta_infos ) && ! empty( $post_meta_infos ) ) {
        foreach ( $post_meta_infos as $value ) {
            $data = array();
            $data['value'] = $slug ? $value['slug'] : $value['id'];
            $data['label'] = esc_html__( 'Id', 'rehub-theme' ) . ': ' . $value['id'] . ( ( strlen( $value['name'] ) > 0 ) ? ' - ' . esc_html__( 'Name', 'rehub-theme' ) . ': ' . $value['name'] : '' ) . ( ( strlen( $value['slug'] ) > 0 ) ? ' - ' . esc_html__( 'Slug', 'rehub-theme' ) . ': ' . $value['slug'] : '' );
            $result[] = $data;
        }
    }
    return $result;
}

function rehub_catwoo_search_vc( $query, $slug = false ) {
    global $wpdb;
    $cat_id = (int) $query;
    $query = trim( $query );
    $post_meta_infos = $wpdb->get_results( $wpdb->prepare( "SELECT a.term_id AS id, b.name as name, b.slug AS slug
                    FROM {$wpdb->term_taxonomy} AS a
                    INNER JOIN {$wpdb->terms} AS b ON b.term_id = a.term_id
                    WHERE a.taxonomy = 'product_cat' AND (a.term_id = '%d' OR b.slug LIKE '%%%s%%' OR b.name LIKE '%%%s%%' )", $cat_id > 0 ? $cat_id : - 1, stripslashes( $query ), stripslashes( $query ) ), ARRAY_A );

    $result = array();
    if ( is_array( $post_meta_infos ) && ! empty( $post_meta_infos ) ) {
        foreach ( $post_meta_infos as $value ) {
            $data = array();
            $data['value'] = $slug ? $value['slug'] : $value['id'];
            $data['label'] = esc_html__( 'Id', 'rehub-theme' ) . ': ' . $value['id'] . ( ( strlen( $value['name'] ) > 0 ) ? ' - ' . esc_html__( 'Name', 'rehub-theme' ) . ': ' . $value['name'] : '' ) . ( ( strlen( $value['slug'] ) > 0 ) ? ' - ' . esc_html__( 'Slug', 'rehub-theme' ) . ': ' . $value['slug'] : '' );
            $result[] = $data;
        }
    }
    return $result;
}

function rehub_tagwoo_search_vc( $query, $slug = false ) {
    global $wpdb;
    $cat_id = (int) $query;
    $query = trim( $query );
    $post_meta_infos = $wpdb->get_results( $wpdb->prepare( "SELECT a.term_id AS id, b.name as name, b.slug AS slug
                    FROM {$wpdb->term_taxonomy} AS a
                    INNER JOIN {$wpdb->terms} AS b ON b.term_id = a.term_id
                    WHERE a.taxonomy = 'product_tag' AND (a.term_id = '%d' OR b.slug LIKE '%%%s%%' OR b.name LIKE '%%%s%%' )", $cat_id > 0 ? $cat_id : - 1, stripslashes( $query ), stripslashes( $query ) ), ARRAY_A );

    $result = array();
    if ( is_array( $post_meta_infos ) && ! empty( $post_meta_infos ) ) {
        foreach ( $post_meta_infos as $value ) {
            $data = array();
            $data['value'] = $slug ? $value['slug'] : $value['id'];
            $data['label'] = esc_html__( 'Id', 'rehub-theme' ) . ': ' . $value['id'] . ( ( strlen( $value['name'] ) > 0 ) ? ' - ' . esc_html__( 'Name', 'rehub-theme' ) . ': ' . $value['name'] : '' ) . ( ( strlen( $value['slug'] ) > 0 ) ? ' - ' . esc_html__( 'Slug', 'rehub-theme' ) . ': ' . $value['slug'] : '' );
            $result[] = $data;
        }
    }
    return $result;
}

function rehub_cat_render_vc( $query ) {
    $query = $query['value'];
    $cat_id = (int) $query;
    $term = get_term( $cat_id, 'category' );
    return rehubTaxTermOutput( $term );
}

function rehub_tag_render_vc( $query ) {
    $query = $query['value'];
    $cat_id = (int) $query;
    $term = get_term( $cat_id, 'post_tag' );
    return rehubTaxTermOutput( $term );
}

function rehub_catwoo_render_vc( $query ) {
    $query = $query['value'];
    $cat_id = (int) $query;
    $term = get_term( $cat_id, 'product_cat' );
    return rehubTaxTermOutput( $term );
}

function rehub_tagwoo_render_vc( $query ) {
    $query = $query['value'];
    $cat_id = (int) $query;
    $term = get_term( $cat_id, 'product_tag' );
    return rehubTaxTermOutput( $term );
}

function rehubTaxTermOutput( $term ) {
    $term_slug = $term->slug;
    $term_title = $term->name;
    $term_id = $term->term_id;

    $term_slug_display = '';
    if ( ! empty( $term_slug ) ) {
        $term_slug_display = ' - ' . esc_html__( 'Slug', 'rehub-theme' ) . ': ' . $term_slug;
    }

    $term_title_display = '';
    if ( ! empty( $term_title ) ) {
        $term_title_display = ' - ' . esc_html__( 'Title', 'rehub-theme' ) . ': ' . $term_title;
    }

    $term_id_display = esc_html__( 'Id', 'rehub-theme' ) . ': ' . $term_id;

    $data = array();
    $data['value'] = $term_id;
    $data['label'] = $term_id_display . $term_title_display . $term_slug_display;

    return ! empty( $data ) ? $data : false;
}

function rehub_search_woo_attributes ($query, $slug = false){
    global $wpdb;
    $query = trim( $query );
    $query = '%'. $wpdb->esc_like($query) .'%';
    $attribute_taxonomies = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". $wpdb->prefix ."woocommerce_attribute_taxonomies WHERE attribute_name LIKE %s", $query));

    $attribute_taxonomies = array_filter( $attribute_taxonomies  ) ;
    $result = array();
    if ( is_array( $attribute_taxonomies ) && ! empty( $attribute_taxonomies ) ) {
        foreach ( $attribute_taxonomies as $value ) {
            $data = array();
            $data['value'] = $value->attribute_name;
            $data['label'] = $value->attribute_label;
            $result[] = $data;
        }
    }  
    return $result;  
}

function rehub_render_woo_attributes( $value ) {

    return array(
        'label' =>  $value['value'],
        'value' => $value['label'],
    );
}



//FILTER FUNCTIONS
if( !function_exists('rehub_vc_filter_formodules') ) {
    function rehub_vc_filter_formodules() {
    $post_formats = array(   
        esc_html__('all', 'rehub-theme') => 'all',
        esc_html__('regular', 'rehub-theme') => 'regular',
        esc_html__('video', 'rehub-theme') => 'video',
        esc_html__('gallery', 'rehub-theme') => 'gallery',
        esc_html__('review', 'rehub-theme') => 'review',
        esc_html__('music', 'rehub-theme') => 'music',              
    );        
    return array(         
        array(
            "type" => "dropdown",
            "class" => "",
            "admin_label" => true,
            "heading" => esc_html__('Data source', 'rehub-theme'),
            "param_name" => "data_source",
            "value" => array(
                esc_html__('Category or tag', 'rehub-theme') => "cat",
                esc_html__('Manual select and order', 'rehub-theme') => "ids",
                esc_html__('Is editor choice', 'rehub-theme') => "badge",
                esc_html__('Custom post type and taxonomy', 'rehub-theme') => "cpt",                    
            ), 
        ),
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Category', 'rehub-theme' ),
            'param_name' => 'cat',
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            "admin_label" => true,
            'description' => esc_html__( 'Enter names of categories. Or leave blank to show all', 'rehub-theme' ),
            'dependency' => array(
                'element' => 'data_source',
                'value' => array( 'cat' ),
            ),          
        ),
        array(
            'type' => 'autocomplete',
            "admin_label" => true,
            'heading' => esc_html__( 'Category exclude', 'rehub-theme' ),
            'param_name' => 'cat_exclude',
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of categories to exclude', 'rehub-theme' ),
            'dependency' => array(
                'element' => 'data_source',
                'value' => array( 'cat' ),
            ),          
        ),        
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Tags', 'rehub-theme' ),
            'param_name' => 'tag',
            "admin_label" => true,
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of tags. Or leave blank to show all', 'rehub-theme' ),
            'dependency' => array(
                'element' => 'data_source',
                'value' => array( 'cat' ),
            ),          
        ),  
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Tags exclude', 'rehub-theme' ),
            'param_name' => 'tag_exclude',
            "admin_label" => true,
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of tags to exclude.', 'rehub-theme' ),
            'dependency' => array(
                'element' => 'data_source',
                'value' => array( 'cat' ),
            ),          
        ),                  
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Post names', 'rehub-theme' ),
            'param_name' => 'ids',
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            "admin_label" => true,
            'description' => esc_html__( 'Or enter names of posts.', 'rehub-theme' ),
            'dependency' => array(
                'element' => 'data_source',
                'value' => array( 'ids' ),
            ),                          
        ), 
        array(
            'type' => 'dropdown',
            "admin_label" => true,
            'heading' => esc_html__( 'Editor label', 'rehub-theme' ),
            'param_name' => 'badge_label',
            'value' => array(
                esc_html__( 'Editor choice', 'rehub-theme' ) => '1',
                esc_html__( 'Custom label 2', 'rehub-theme' ) => '2',
                esc_html__( 'Custom label 3', 'rehub-theme' ) => '3',
                esc_html__( 'Custom label 4', 'rehub-theme' ) => '4',
            ),
            'description' => esc_html__( 'Select admin label. You can customize labels in theme option - custom badges for posts', 'rehub-theme' ),
            'dependency' => array(
                'element' => 'data_source',
                'value' => array( 'badge' ),
            ),
        ),         
        array(
            'type' => 'dropdown',
            "admin_label" => true,
            'heading' => esc_html__( 'Post type', 'rehub-theme' ),
            'param_name' => 'post_type',
            'value' => rehub_post_type_vc(),
            'dependency' => array(
                'element' => 'data_source',
                'value' => array( 'cpt'),
            ),            
        ),   
        array(
            'type' => 'textfield',
            "admin_label" => true,
            'heading' => esc_html__( 'Taxonomy slug', 'rehub-theme' ),
            'param_name' => 'tax_name',
            'description' => esc_html__( 'Enter slug of your taxonomy. Examples: if you want to use post categories - use <strong>category</strong>. If you want to use woocommerce product category - use <strong>product_cat</strong>, woocommerce tags - <strong>product_tag</strong>', 'rehub-theme' ),
            'dependency' => array(
                'element' => 'data_source',
                'value' => array( 'cpt'),
            ),
        ), 
        array(
            'type' => 'textfield',
            "admin_label" => true,
            'heading' => esc_html__( 'Taxonomy term slug', 'rehub-theme' ),
            'param_name' => 'tax_slug',
            'description' => esc_html__( 'Enter term slug of your taxonomy if you want to show only posts from this taxonomy term', 'rehub-theme' ),
            'dependency' => array(
                'element' => 'tax_name',
                'not_empty' => true,
            ),
        ),  
        array(
            'type' => 'textfield',
            "admin_label" => true,
            'heading' => esc_html__( 'Taxonomy term slug exclude', 'rehub-theme' ),
            'param_name' => 'tax_slug_exclude',
            'description' => esc_html__( 'Enter slug of your taxonomy term to exclude', 'rehub-theme' ),
            'dependency' => array(
                'element' => 'tax_name',
                'not_empty' => true,
            ),
        ), 
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Deal filter', 'rehub-theme'),
            "admin_label" => true,
            "param_name" => "show_coupons_only",
            "value" => array(
                esc_html__('Show all', 'rehub-theme') => "all",
                esc_html__('Show discounts (not expired)', 'rehub-theme') => "1",
                esc_html__('Only offers, excluding coupons (not expired)', 'rehub-theme') => "5",                
                esc_html__('Only coupons (not expired)', 'rehub-theme') => "2",                  
                esc_html__('Show all except expired', 'rehub-theme') => "3", 
                esc_html__('Only expired offers (which have expired date)', 'rehub-theme') => "4",
                esc_html__('Only with reviews', 'rehub-theme') => "6",                 
            ), 
            'description' => esc_html__( 'Choose deal type if you use Posts as offers', 'rehub-theme' ),
        ),    
        array(
            'type' => 'textfield',
            "admin_label" => true,
            'heading' => esc_html__( 'Price range', 'rehub-theme' ),
            'param_name' => 'price_range',
            'description' => esc_html__( 'Set price range to show. Works only for posts with Main Post offer section. Example of using: 0-100. Will show products with price under 100', 'rehub-theme' ),
            'group' => esc_html__( 'Data settings', 'rehub-theme' ),
        ),                                             
        array(
            'type' => 'dropdown',
            'heading' => esc_html__( 'Order by', 'rehub-theme' ),
            'param_name' => 'orderby',
            "admin_label" => true,
            'value' => array(
                esc_html__( 'Date', 'rehub-theme' ) => 'date',
                esc_html__( 'Order by post ID', 'rehub-theme' ) => 'ID',
                esc_html__( 'Title', 'rehub-theme' ) => 'title',
                esc_html__( 'Last modified date', 'rehub-theme' ) => 'modified',
                esc_html__( 'Number of comments', 'rehub-theme' ) => 'comment_count',               
                esc_html__( 'Meta value', 'rehub-theme' ) => 'meta_value',
                esc_html__( 'Meta value number', 'rehub-theme' ) => 'meta_value_num',
                esc_html__( 'Views', 'rehub-theme' ) => 'view',  
                esc_html__( 'Thumb/Hot counter', 'rehub-theme' ) => 'thumb',
                esc_html__('Show hottest sorted by date', 'rehub-theme') => "hot",
                esc_html__( 'Expiration date', 'rehub-theme' ) => 'expirationdate',
                esc_html__( 'Price', 'rehub-theme' ) => 'price',                    
                esc_html__( 'Discount', 'rehub-theme' ) => 'discount',                                            
                esc_html__( 'Random order', 'rehub-theme' ) => 'rand',
            ),
            'description' => esc_html__( 'Select order type. If "Meta value" or "Meta value Number" is chosen then meta key is required.', 'rehub-theme' ),
            'group' => esc_html__( 'Data settings', 'rehub-theme' ),
            'dependency' => array(
                'element' => 'data_source',
                'value_not_equal_to' => array( 'ids'),
            ),
        ),
        array(
            'type' => 'dropdown',
            'heading' => esc_html__( 'Sorting', 'rehub-theme' ),
            'param_name' => 'order',
            'group' => esc_html__( 'Data settings', 'rehub-theme' ),
            'value' => array(
                esc_html__( 'Descending', 'rehub-theme' ) => 'DESC',
                esc_html__( 'Ascending', 'rehub-theme' ) => 'ASC',
            ),
            'description' => esc_html__( 'Select sorting order.', 'rehub-theme' ),
            'dependency' => array(
                'element' => 'data_source',
                'value_not_equal_to' => array( 'ids' ),
            ),
        ),
        array(
            'type' => 'textfield',
            "admin_label" => true,
            'heading' => esc_html__( 'Meta key', 'rehub-theme' ),
            'param_name' => 'meta_key',
            'description' => esc_html__( 'Input meta key for ordering.', 'rehub-theme' ),
            'group' => esc_html__( 'Data settings', 'rehub-theme' ),
            'dependency' => array(
                'element' => 'orderby',
                'value' => array( 'meta_value', 'meta_value_num' ),
            ),
        ),
        array(
            "type" => "dropdown",
            "admin_label" => true,
            "heading" => esc_html__('Choose post formats', 'rehub-theme'),
            "param_name" => "post_formats",
            "value" => $post_formats,
            'description' => esc_html__('Choose post formats to display or leave blank to display all', 'rehub-theme'),
            'group' => esc_html__( 'Data settings', 'rehub-theme' ),            
            'dependency' => array(
                'element' => 'data_source',
                'value' => array( 'cat', 'badge' ),
            ),          
        ),  
        array(
            "type" => "textfield",
            "heading" => esc_html__('Offset', 'rehub-theme'),
            "param_name" => "offset",
            "value" => '',
            'description' => esc_html__('Number of products to offset', 'rehub-theme'),
            'group' => esc_html__( 'Data settings', 'rehub-theme' ),          
        ),                  
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Filter by date', 'rehub-theme'),
            "param_name" => "show_date",
            "value" => array(
                esc_html__('All', 'rehub-theme') => "all",
                esc_html__('Published last 24 hours', 'rehub-theme') => "day",
                esc_html__('Published last 7 days', 'rehub-theme') => "week", 
                esc_html__('Published last month', 'rehub-theme') => "month",  
                esc_html__('Published last year', 'rehub-theme') => "year",                                                
            ),
            'group' => esc_html__( 'Data settings', 'rehub-theme' ),
            'dependency' => array(
                'element' => 'data_source',
                'value_not_equal_to' => array( 'ids' ),
            ),          
        ),                 
        array(
            "type" => "textfield",
            "heading" => esc_html__('Fetch Count', 'rehub-theme'),
            "param_name" => "show",
            "admin_label" => true,
            "value" => '12',
            'description' => esc_html__('Number of products to display', 'rehub-theme'),
            'group' => esc_html__( 'Data settings', 'rehub-theme' ),
            'dependency' => array(
                'element' => 'data_source',
                'value_not_equal_to' => array( 'ids' ),
            ),          
        ),  
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Pagination type', 'rehub-theme'),
            "param_name" => "enable_pagination",
            "value" => array(
                esc_html__('No pagination', 'rehub-theme') => "no",
                esc_html__('Simple pagination', 'rehub-theme') => "1",
                esc_html__('Infinite scroll', 'rehub-theme') => "2",  
                esc_html__('New item will be added by click', 'rehub-theme') => "3",                                  
            ),
            'group' => esc_html__( 'Data settings', 'rehub-theme' ),
            'dependency' => array(
                'element' => 'data_source',
                'value_not_equal_to' => array( 'ids' ),
            ),          
        ),                                                                   
    );       
    }
}

//FILTER WOO FUNCTIONS
if( !function_exists('rehub_woo_vc_filter_formodules') ) {
    function rehub_woo_vc_filter_formodules() {
        return array(
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__('Data source', 'rehub-theme'),
                "param_name" => "data_source",
                "value" => array(
                    esc_html__('Category', 'rehub-theme') => "cat",
                    esc_html__('Tag', 'rehub-theme') => "tag",                
                    esc_html__('Manual select and order', 'rehub-theme') => "ids",  
                    esc_html__('Type of products', 'rehub-theme') => "type",                
                ), 
            ),      
            array(
                'type' => 'autocomplete',
                'heading' => esc_html__( 'Category', 'rehub-theme' ),
                'param_name' => 'cat',
                "admin_label" => true,
                'settings' => array(
                    'multiple' => true,
                    'sortable' => true,
                    'groups' => false,
                ),
                'description' => esc_html__( 'Enter names of categories', 'rehub-theme' ),
                'dependency' => array(
                    'element' => 'data_source',
                    'value' => array( 'cat' ),
                ),          
            ),
            array(
                'type' => 'autocomplete',
                'heading' => esc_html__( 'Tag', 'rehub-theme' ),
                'param_name' => 'tag',
                "admin_label" => true,
                'settings' => array(
                    'multiple' => true,
                    'sortable' => true,
                    'groups' => false,
                ),
                'description' => esc_html__( 'Enter names of tags', 'rehub-theme' ),
                'dependency' => array(
                    'element' => 'data_source',
                    'value' => array( 'tag' ),
                ),          
            ),         
            array(
                'type' => 'autocomplete',
                'heading' => esc_html__( 'Product names', 'rehub-theme' ),
                'param_name' => 'ids',
                "admin_label" => true,
                'settings' => array(
                    'multiple' => true,
                    'sortable' => true,
                    'groups' => false,
                ),
                'dependency' => array(
                    'element' => 'data_source',
                    'value' => array( 'ids' ),
                ),                          
            ), 
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__('Type of product', 'rehub-theme'),
                "param_name" => "type",
                "admin_label" => true,
                "value" => array(
                    esc_html__('Recent products', 'rehub-theme') => "recent",
                    esc_html__('Featured products', 'rehub-theme') => "featured",   
                    esc_html__('Sale products', 'rehub-theme') => "sale",
                    esc_html__('Best selling products', 'rehub-theme') => "best_sale",
                    esc_html__('Recent viewed products', 'rehub-theme') => "recentviews",                                
                ), 
                "description" => esc_html__( 'Recent viewed products work only if you have Recent Product Widget somewhere on the site', 'rehub-theme' ),
                'dependency' => array(
                    'element' => 'data_source',
                    'value' => array( 'type' ),
                ),          
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__('Deal filter', 'rehub-theme'),
                "admin_label" => true,
                "param_name" => "show_coupons_only",
                "value" => array(
                    esc_html__('Show all', 'rehub-theme') => "all",
                    esc_html__('Show discounts (not expired)', 'rehub-theme') => "1",
                    esc_html__('Only offers, excluding coupons (not expired)', 'rehub-theme') => "5",                
                    esc_html__('Only coupons (not expired)', 'rehub-theme') => "4",                  
                    esc_html__('Show all except expired', 'rehub-theme') => "2", 
                    esc_html__('Only expired offers (which have expired date)', 'rehub-theme') => "3",         
                ), 
                "description" => esc_html__( 'Choose deal type if you use Posts as offers', 'rehub-theme' ),         
            ),
            array(
                'type' => 'textfield',
                "admin_label" => true,
                'heading' => esc_html__( 'Price range', 'rehub-theme' ),
                'param_name' => 'price_range',
                'description' => esc_html__( 'Set price range to show. Works only for posts with Main Post offer section. Example of using: 0-100. Will show products with price under 100', 'rehub-theme' ),
                'group' => esc_html__( 'Data settings', 'rehub-theme' ),
            ),                          
            array(
                'type' => 'dropdown',
                'heading' => esc_html__( 'Order by', 'rehub-theme' ),
                'param_name' => 'orderby',
                "admin_label" => true,
                'value' => array(
                    esc_html__( 'Date', 'rehub-theme' ) => 'date',
                    esc_html__( 'Order by post ID', 'rehub-theme' ) => 'ID',
                    esc_html__( 'Title', 'rehub-theme' ) => 'title',
                    esc_html__( 'Last modified date', 'rehub-theme' ) => 'modified',
                    esc_html__( 'Number of comments', 'rehub-theme' ) => 'comment_count',               
                    esc_html__( 'Meta value', 'rehub-theme' ) => 'meta_value',
                    esc_html__( 'Meta value number', 'rehub-theme' ) => 'meta_value_num',
                    esc_html__( 'Random order', 'rehub-theme' ) => 'rand',
                ),
                'group' => esc_html__( 'Data settings', 'rehub-theme' ),
                'dependency' => array(
                    'element' => 'data_source',
                    'value_not_equal_to' => array( 'ids'),
                ),
            ),
            array(
                'type' => 'textfield',
                "admin_label" => true,
                'heading' => esc_html__( 'Meta key', 'rehub-theme' ),
                'param_name' => 'meta_key',
                'description' => esc_html__( 'Input meta key for ordering.', 'rehub-theme' ),
                'group' => esc_html__( 'Data settings', 'rehub-theme' ),
                'dependency' => array(
                    'element' => 'orderby',
                    'value' => array( 'meta_value', 'meta_value_num' ),
                ),
            ),  
            array(
                'type' => 'textfield',
                "admin_label" => true,
                'heading' => esc_html__( 'User ID', 'rehub-theme' ),
                'param_name' => 'user_id',
                'description' => esc_html__( 'Add user ID to show only his posts', 'rehub-theme' ),
                'group' => esc_html__( 'Data settings', 'rehub-theme' ),
            ),                      
            array(
                'type' => 'dropdown',
                'heading' => esc_html__( 'Sorting', 'rehub-theme' ),
                'param_name' => 'order',
                'group' => esc_html__( 'Data settings', 'rehub-theme' ),
                'value' => array(
                    esc_html__( 'Descending', 'rehub-theme' ) => 'DESC',
                    esc_html__( 'Ascending', 'rehub-theme' ) => 'ASC',
                ),
                'description' => esc_html__( 'Select sorting order.', 'rehub-theme' ),
                'dependency' => array(
                    'element' => 'data_source',
                    'value_not_equal_to' => array( 'ids' ),
                ),
            ),  
            array(
                "type" => "textfield",
                "heading" => esc_html__('Fetch Count', 'rehub-theme'),
                "param_name" => "show",
                "admin_label" => true,
                "value" => '12',
                'description' => esc_html__('Number of products to display', 'rehub-theme'),
                'group' => esc_html__( 'Data settings', 'rehub-theme' ),
                'dependency' => array(
                    'element' => 'data_source',
                    'value_not_equal_to' => array( 'ids' ),
                ),          
            ),   
            array(
                "type" => "textfield",
                "admin_label" => true,
                "heading" => esc_html__('Offset', 'rehub-theme'),
                "param_name" => "offset",
                "value" => '',
                'description' => esc_html__('Number of products to offset', 'rehub-theme'),
                'group' => esc_html__( 'Data settings', 'rehub-theme' ),          
            ),   
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__('Show by date', 'rehub-theme'),
                "param_name" => "show_date",
                "value" => array(
                    esc_html__('All', 'rehub-theme') => "all",
                    esc_html__('Published last 24 hours', 'rehub-theme') => "day",
                    esc_html__('Published last 7 days', 'rehub-theme') => "week", 
                    esc_html__('Published last month', 'rehub-theme') => "month",  
                    esc_html__('Published last year', 'rehub-theme') => "year",                                                
                ),
                'group' => esc_html__( 'Data settings', 'rehub-theme' ),
                'dependency' => array(
                    'element' => 'data_source',
                    'value_not_equal_to' => array( 'ids' ),
                ),          
            ),                                                         
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__('Pagination type', 'rehub-theme'),
                "param_name" => "enable_pagination",
                "value" => array(
                    esc_html__('No pagination', 'rehub-theme') => "0",
                    esc_html__('Simple pagination', 'rehub-theme') => "1",
                    esc_html__('Infinite scroll', 'rehub-theme') => "2",  
                    esc_html__('New item will be added by click', 'rehub-theme') => "3",                      
                ),
                'group' => esc_html__( 'Data settings', 'rehub-theme' ),
                'dependency' => array(
                    'element' => 'data_source',
                    'value_not_equal_to' => array( 'ids' ),
                ),          
            ),
            array(
                'type' => 'textfield',
                'group' => esc_html__( 'Taxonomy', 'rehub-theme' ),
                "admin_label" => true,
                'heading' => esc_html__( 'Taxonomy slug', 'rehub-theme' ),
                'param_name' => 'tax_name',
                'description' => esc_html__( 'Enter slug of your taxonomy. Example, taxonomy for product brand - is store. For color attribute - pa_color, for product tags - product_tag', 'rehub-theme' ),
            ), 
            array(
                'type' => 'textfield',
                'group' => esc_html__( 'Taxonomy', 'rehub-theme' ),
                "admin_label" => true,
                'heading' => esc_html__( 'Taxonomy term slug', 'rehub-theme' ),
                'param_name' => 'tax_slug',
                'description' => esc_html__( 'Enter slug of your taxonomy term if you want to show only posts from certain taxonomy term. Example, for store taxonomy - amazon, for color - black', 'rehub-theme' ),
                'dependency' => array(
                    'element' => 'tax_name',
                    'not_empty' => true,
                ),
            ),
            array(
                'type' => 'textfield',
                "admin_label" => true,
                'heading' => esc_html__( 'Taxonomy term slug exclude', 'rehub-theme' ),
                'param_name' => 'tax_slug_exclude',
                'description' => esc_html__( 'Enter slug of your taxonomy term to exclude', 'rehub-theme' ),
                'dependency' => array(
                    'element' => 'tax_name',
                    'not_empty' => true,
                ),
            ),                         

        );
    }
}

//FILTER PANEL FUNCTIONS
if( !function_exists('rehub_vc_aj_filter_btns_formodules') ) {
    function rehub_vc_aj_filter_btns_formodules() {
        return array(
        array(
            "type" => "checkbox",
            "class" => "",
            "group" => esc_html__('Filter panel', 'rehub-theme'),        
            "heading" => esc_html__('Enable panel?', 'rehub-theme'),
            "value" => array(__("Yes", "rehub-theme") => true ),
            "param_name" => "filterpanelenable",         
        ),  
        array(
            "type" => "textfield",
            "heading" => esc_html__('Title', 'rehub-theme'),
            "param_name" => "filterheading",
            "group" => esc_html__('Filter panel', 'rehub-theme'),             
            'dependency' => array(
                'element' => 'filterpanelenable',
                'not_empty' => true,
            ),             
        ),                  
        array(
            'type' => 'param_group',
            "group" => esc_html__('Filter panel', 'rehub-theme'),             
            'heading' => esc_html__( 'Filter panel', 'rehub-theme' ),
            'param_name' => 'filterpanel',
            'dependency' => array(
                'element' => 'filterpanelenable',
                'not_empty' => true,
            ),             
            'value' => urlencode( json_encode( array(
                array(
                    'filtertitle' => esc_html__( 'Show all', 'rehub-theme' ),
                    'filtertype' => 'all',
                    'filterorder' => 'DESC',
                    'filterdate'=> 'all',
                ),
            ) ) ),
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Label', 'rehub-theme' ),
                    'param_name' => 'filtertitle',
                    'description' => esc_html__( 'Enter title for filter button', 'rehub-theme' ),
                    'admin_label' => true,
                ),
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => esc_html__('Type of filter', 'rehub-theme'),
                    "param_name" => "filtertype",
                    "value" => array(
                        esc_html__('Show all posts', 'rehub-theme') => "all",
                        esc_html__('Sort by comments count', 'rehub-theme') => "comment",
                        esc_html__('Sort by meta field', 'rehub-theme') => "meta", 
                        esc_html__('Sort by expiration date', 'rehub-theme') => "expirationdate",
                        esc_html__('Sort by price range', 'rehub-theme') => "pricerange", 
                        esc_html__('Show hottest sorted by date', 'rehub-theme') => "hot",           
                        esc_html__('Sort by taxonomy', 'rehub-theme') => "tax", 
                        esc_html__('Show only deals', 'rehub-theme') => "deals", 
                        esc_html__('Show only coupons', 'rehub-theme') => "coupons",            
                    ), 
                    "description" => "Some important meta keys: <br /><strong>rehub_main_product_price</strong> - key where stored price of main offer, <br /><strong>rehub_review_overall_score</strong> - key for overall review score, <br /><strong>post_hot_count</strong> - thumbs counter, <br /><strong>post_wish_count</strong> - wishlist counter, <br /><strong>post_user_average</strong> - user rating score(based on full review criterias), <br /><strong>rehub_views</strong> - post view counter, <br /><strong>rehub_views_mon, rehub_views_day, rehub_views_year</strong> - post view counter by day, month, year <br /><strong>affegg_product_price</strong> - price of main offer for Affiliate Egg plugin, <br /><strong>_price</strong> - key for price of woocommerce products, <br /><strong>total_sales</strong> - key for sales of woocommerce products",
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Type key for meta', 'rehub-theme' ),
                    'param_name' => 'filtermetakey',
                    "dependency" => Array('element' => "filtertype", 'value' => array('meta')),
                ), 
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Price range', 'rehub-theme' ),
                    'param_name' => 'filterpricerange',
                    'description' => esc_html__( 'Set price range to show. Works only for posts with Main Post offer section. Example of using: 0-100. Will show products with price under 100', 'rehub-theme' ),                   
                    "dependency" => Array('element' => "filtertype", 'value' => array('pricerange')),
                ), 
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => esc_html__('Order by', 'rehub-theme'),
                    "param_name" => "filterorderby",
                    'value' => array(
                        esc_html__( 'Date', 'rehub-theme' ) => 'date',
                        esc_html__( 'Order by post ID', 'rehub-theme' ) => 'ID',
                        esc_html__( 'Title', 'rehub-theme' ) => 'title',
                        esc_html__( 'Last modified date', 'rehub-theme' ) => 'modified',
                        esc_html__( 'Number of comments', 'rehub-theme' ) => 'comment_count',
                        esc_html__( 'Views', 'rehub-theme' ) => 'view',  
                        esc_html__( 'Thumb/Hot counter', 'rehub-theme' ) => 'thumb',
                        esc_html__( 'Price', 'rehub-theme' ) => 'price',                    
                        esc_html__( 'Discount', 'rehub-theme' ) => 'discount',        
                        esc_html__( 'Random order', 'rehub-theme' ) => 'rand',
                    ),
                ),                                
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Taxonomy slug', 'rehub-theme' ),
                    'param_name' => 'filtertaxkey',
                    'description' => esc_html__( 'Enter slug of your taxonomy. Examples: if you want to use post categories - use <strong>category</strong>. If you want to use woocommerce product category - use <strong>product_cat</strong>, woocommerce tags - <strong>product_tag</strong>', 'rehub-theme' ),
                    "dependency" => Array('element' => "filtertype", 'value' => array('tax')),
                ), 
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Taxonomy term slug', 'rehub-theme' ),
                    'param_name' => 'filtertaxtermslug',
                    'description' => esc_html__( 'Enter term slug of your taxonomy if you want to show only posts from this taxonomy term', 'rehub-theme' ),
                    "dependency" => Array('element' => "filtertype", 'value' => array('tax')),
                ), 
                array(
                    "type" => "checkbox",      
                    "heading" => esc_html__('Use filter taxonomy within general taxonomy option', 'rehub-theme'),
                    "value" => array(__("Yes", "rehub-theme") => true ),
                    "param_name" => "filtertaxcondition",  
                    "dependency" => Array('element' => "filtertype", 'value' => array('tax')),                           
                ),                                 
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__( 'Sorting', 'rehub-theme' ),
                    'param_name' => 'filterorder',
                    'value' => array(
                        esc_html__( 'Descending', 'rehub-theme' ) => 'DESC',
                        esc_html__( 'Ascending', 'rehub-theme' ) => 'ASC',
                    ),
                    'description' => esc_html__( 'Select sorting order.', 'rehub-theme' ),
                ),
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => esc_html__('Filter by date of publishing', 'rehub-theme'),
                    "param_name" => "filterdate",
                    "value" => array(
                        esc_html__('All', 'rehub-theme') => "all",
                        esc_html__('Published last 24 hours', 'rehub-theme') => "day",
                        esc_html__('Published last 7 days', 'rehub-theme') => "week", 
                        esc_html__('Published last month', 'rehub-theme') => "month",  
                        esc_html__('Published last year', 'rehub-theme') => "year",                                                
                    ), 
                ),                                                             
            ),
            'description' => 'Don\'t use more than 4-5 filters!!!!! Settings for first tab must be the same as main post settings of block'
        ),
        array(
            "type" => "textfield",
            "heading" => esc_html__('Taxonomy dropdown', 'rehub-theme'),
            "param_name" => "taxdrop",
            "group" => esc_html__('Filter panel', 'rehub-theme'),             
            "description" => esc_html__('Type here taxonomy slug if you want to show dropdown. For example, type: category or product_cat for woocommerce category', 'rehub-theme'),
            'dependency' => array(
                'element' => 'filterpanelenable',
                'not_empty' => true,
            ),             
        ),
        array(
            "type" => "textfield",
            "heading" => esc_html__('Taxonomy ids', 'rehub-theme'),
            "param_name" => "taxdropids",
            "group" => esc_html__('Filter panel', 'rehub-theme'),             
            "description" => esc_html__('Type here ids of taxonomy separated by comma  which you need to show. Leave empty to show all', 'rehub-theme'),
            'dependency' => array(
                'element' => 'taxdrop',
                'not_empty' => true,
            ),             
        ),        
        array(
            "type" => "textfield",
            "heading" => esc_html__('Taxonomy dropdown label', 'rehub-theme'),
            "param_name" => "taxdroplabel",
            "group" => esc_html__('Filter panel', 'rehub-theme'),             
            "description" => esc_html__('Type here label for dropdown', 'rehub-theme'),
            'dependency' => array(
                'element' => 'taxdrop',
                'not_empty' => true,
            ),             
        ),        

        );
    }
}

//IMAGE SLIDER
add_action( 'vc_after_init', 're_remove_slider_type' ); 
function re_remove_slider_type() {
    $param = WPBMap::getParam( 'vc_gallery', 'type' );
    unset($param['value'][__( 'Flex slider fade', 'rehub-theme' )]);
    unset($param['value'][__( 'Nivo slider', 'rehub-theme' )]);
    vc_update_shortcode_param( 'vc_gallery', $param );
    $newParamDataImageFull = array(
        'type' => 'textfield',
        'heading' => esc_html__( 'Image size', 'rehub-theme' ),
        'param_name' => 'img_size',
        'value' => 'full', // New default value
        'description' => esc_html__( 'Enter image size (Example: "thumbnail", "medium", "large", "full" or other sizes defined by theme). Alternatively enter size in pixels (Example: 200x100 (Width x Height)).', 'rehub-theme' ),
        'dependency' => array(
            'element' => 'source',
            'value' => array( 'media_library', 'featured_image' ),
        ),
    );   
    vc_update_shortcode_param( 'vc_single_image', $newParamDataImageFull );    
}

add_action( 'vc_before_init', 'rehub_integrateWithVC' );
function rehub_integrateWithVC() { 

vc_remove_param("vc_gallery", "interval");
vc_add_param("vc_gallery", 
    array(
        "type" => "checkbox",
        "class" => "",
        "heading" => esc_html__('Autoplay?', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "autoplay",         
    ) 
);

//Where to open window
$target_arr = array(__("Same window", "rehub-theme") => "_self", esc_html__("New window", "rehub-theme") => "_blank");

//Post format chooser
$post_formats = array(   
    esc_html__('all', 'rehub-theme') => 'all',
    esc_html__('regular', 'rehub-theme') => 'regular',
    esc_html__('video', 'rehub-theme') => 'video',
    esc_html__('gallery', 'rehub-theme') => 'gallery',
    esc_html__('review', 'rehub-theme') => 'review',
    esc_html__('music', 'rehub-theme') => 'music',              
);

//CPT chooser
if( !function_exists('rehub_post_type_vc') ) {
    function rehub_post_type_vc() {
        $post_types = get_post_types( array('public'   => true) );
        $post_types_list = array();
        foreach ( $post_types as $post_type ) {
            if ( $post_type !== 'revision' && $post_type !== 'nav_menu_item' && $post_type !== 'attachment') {
                $label = ucfirst( $post_type );
                $post_types_list[$label] = $post_type;
            }
        }
        return $post_types_list;
    }
}

//TITLE FOR CUSTOM BLOCK
vc_map( array(
    "name" => esc_html__('Module title', 'rehub-theme'),
    "base" => "title_mod",
    "icon" => "icon-title-mod",
    "category" => esc_html__('Content modules', 'rehub-theme'),
    'description' => esc_html__('Title for modules', 'rehub-theme'), 
    "params" => array(
        array(
            "type" => "textfield",
            "heading" => esc_html__('Title', 'rehub-theme'),
            "param_name" => "title_name",
            "admin_label" => true,
        ),
        array(
            'type' => 'colorpicker',
            "admin_label" => true,
            'heading' => esc_html__( 'Color for title', 'rehub-theme' ),
            'description' => esc_html__('Default is black', 'rehub-theme'),
            'param_name' => 'title_color',        
        ),
        array(
            'type' => 'colorpicker',
            "admin_label" => true,
            'heading' => esc_html__( 'Color for title background', 'rehub-theme' ),
            'description' => esc_html__('Default is transparent', 'rehub-theme'),
            'param_name' => 'title_background_color',        
        ),   
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Size of title', 'rehub-theme'),
            "param_name" => "title_size",
            "value" => array(
                esc_html__('Middle', 'rehub-theme') => "middle",
                esc_html__('Big', 'rehub-theme') => "big",
                esc_html__('Small', 'rehub-theme') => "small",  
                esc_html__('Extra Big', 'rehub-theme') => "extrabig", 
                esc_html__('Extra Small', 'rehub-theme') => "extrasmall",               
            ), 
        ), 
        array(
            "type" => "checkbox",
            "class" => "",
            "heading" => esc_html__('Disable bold?', 'rehub-theme'),
            "value" => array(__("Yes", "rehub-theme") => true ),
            "param_name" => "title_bold",         
        ),                 
        array(
            'type' => 'iconpicker',
            'heading' => esc_html__( 'Icon', 'rehub-theme' ),
            'param_name' => 'title_icon',
            'value' => '',
            'settings' => array(
                'emptyIcon' => true,
                'iconsPerPage' => 100,
            ),
            'group'=> 'Icon',            
        ),              
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Title Position', 'rehub-theme'),
            "param_name" => "title_pos",
            "value" => array(
                esc_html__('Left', 'rehub-theme') => "left",
                esc_html__('Right', 'rehub-theme') => "right",
                esc_html__('Center', 'rehub-theme') => "center",                
            ), 
        ),                             
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Title line', 'rehub-theme'),
            "param_name" => "title_line",
            "value" => array(
                esc_html__('Under title', 'rehub-theme') => "under-title",
                esc_html__('Above title', 'rehub-theme') => "above-title",  
                esc_html__('Title inside line', 'rehub-theme') => "inside-title",
                esc_html__('Small line under title', 'rehub-theme') => "small-line",
                esc_html__('No line', 'rehub-theme') => "no-line",
            )
        ),
        array(
            "type" => "colorpicker",
            "heading" => esc_html__('Color for line', 'rehub-theme' ),
            "description" => esc_html__('Default is grey', 'rehub-theme'),
            "param_name" => 'title_line_color',
            "admin_label" => true,    
            'dependency' => array(
                'element' => 'title_line',
                'value_not_equal_to' => array( 'no-line' ),
            ),                 
        ),        
        array(
            "type" => "vc_link",
            "heading" => esc_html__('Custom URL:', 'rehub-theme'),
            "param_name" => "vc_link",
            'description' => esc_html__('Set url near title or leave blank', 'rehub-theme'),
            "admin_label" => true,
        ),   
        array(
            "type" => "textfield",
            "heading" => esc_html__('Additional class', 'rehub-theme'),
            "param_name" => "title_class_add",
            "description" => esc_html__('Use mb5, mb10, mb15, mb20, mb25, mt10, mt15, mt20 to change margins', 'rehub-theme'),
            "admin_label" => true,
        ),                                           
    )
) );

//HOME FEATURED SECTION
vc_map( array(
    "name" => esc_html__('Featured section', 'rehub-theme'),
    "base" => "wpsm_featured",
    "icon" => "icon-featured",
    "category" => esc_html__('Content modules', 'rehub-theme'),
    "description" => esc_html__('For full width row', 'rehub-theme'),
    "params" => rehub_vc_filter_formodules() 
) );
vc_remove_param("wpsm_featured", "enable_pagination");
vc_remove_param("wpsm_featured", "show");
vc_remove_param("wpsm_featured", "offset");
vc_add_params("wpsm_featured", array(
    array(
        "type" => "dropdown",
        "class" => "",
        "heading" => esc_html__('Type of area', 'rehub-theme'),
        "group" =>  esc_html__('Control', 'rehub-theme'),
        "param_name" => "feat_type",
        "admin_label" => true,
        "value" => array(
            esc_html__('Featured area (slider + 2 posts)', 'rehub-theme') => "1",
            esc_html__('Featured full width slider', 'rehub-theme') => "2",
            esc_html__('Featured grid', 'rehub-theme') => "3",                 
        ),
        'description' => esc_html__('Featured area works only in full width row', 'rehub-theme'), 
    ),   
    array(
        "type" => "checkbox",
        "heading" => esc_html__('Show only featured products?', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "show_featured_products",
        "dependency" => Array('element' => "post_type", 'value' => array('product')),
    ),      
    array(
        "type" => "checkbox",
        "heading" => esc_html__('Disable exerpt?', 'rehub-theme'),
        "group" =>  esc_html__('Control', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "dis_excerpt",
        "dependency" => Array('element' => "feat_type", 'value' => array('1', '2')),
    ), 
    array(
        "type" => "checkbox",
        "heading" => esc_html__('Show text in left bottom side?', 'rehub-theme'),
        "group" =>  esc_html__('Control', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "bottom_style",
        "dependency" => Array('element' => "feat_type", 'value' => array('1', '2')),
    ),    
    array(
        "type" => "textfield",
        "heading" => esc_html__("Number of posts to show in slider", "rehub-theme"),
        "param_name" => "show",
        "group" =>  esc_html__('Control', 'rehub-theme'),
        "value" => '5',
        "dependency" => Array('element' => "feat_type", 'value' => array('1', '2')),       
    ),   
    array(
        "type" => "textfield",
        "heading" => esc_html__("Custom height (default is 490) in px", "rehub-theme"),
        "param_name" => "custom_height",
        "group" =>  esc_html__('Control', 'rehub-theme'),
        "dependency" => Array('element' => "feat_type", 'value' => array('2')),       
    ),            
        
));

//DEAL CAROUSEL BLOCK
vc_map( array(
    "name" => esc_html__('Deal and Post carousel', 'rehub-theme'),
    "base" => "post_carousel_mod",
    "icon" => "icon-p-c-mod",
    "category" => esc_html__('Deal helper', 'rehub-theme'),
    'description' => esc_html__('Shows post deals', 'rehub-theme'), 
    "params" => rehub_vc_filter_formodules(),
));
vc_add_params("post_carousel_mod", array(
    array(
        "type" => "dropdown",
        "class" => "",
        "heading" => esc_html__('Carousel style', 'rehub-theme'),
        "group" => esc_html__('Carousel control', 'rehub-theme'),    
        "param_name" => "style",
        "value" => array(
            esc_html__('Horizontal items (use for areas without sidebar)', 'rehub-theme') => "1",              
            esc_html__('Deal grid', 'rehub-theme') => "2",  
            esc_html__('Simple Post', 'rehub-theme') => "simple",                                                  
        ),
    ),    
    array(
        "type" => "dropdown",
        "class" => "",
        "heading" => esc_html__('Number of items in row', 'rehub-theme'),
        "group" => esc_html__('Carousel control', 'rehub-theme'),    
        "param_name" => "showrow",
        'dependency' => array(
            'element' => 'style',
            'value_not_equal_to' => array( '1' ),
        ),        
        "value" => array(
            esc_html__('5', 'rehub-theme') => "5",   
            esc_html__('4', 'rehub-theme') => "4",
            esc_html__('6', 'rehub-theme') => "6",
            esc_html__('3 (Only if you use inside row with sidebar)', 'rehub-theme') => "3",                                                   
        ),
    ), 
    array(
        "type" => "checkbox",
        "class" => "",
        "group" => esc_html__('Carousel control', 'rehub-theme'),        
        "heading" => esc_html__('Disable navigation?', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "nav_dis",         
    ),         
    array(
        "type" => "checkbox",
        "class" => "",
        "group" => esc_html__('Carousel control', 'rehub-theme'),        
        "heading" => esc_html__('Make autorotate?', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "autorotate",         
    ),     
    array(
        "type" => "checkbox",
        "class" => "",
        "heading" => esc_html__('Make link as affiliate?', 'rehub-theme'),
        "group" => esc_html__('Carousel control', 'rehub-theme'),        
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "aff_link", 
        "description" => esc_html__('This will change all inner post links to affiliate link of post offer', 'rehub-theme'),        
    ),            
));
vc_remove_param("post_carousel_mod", "enable_pagination");

//NEWS Ticker
vc_map( array(
    "name" => esc_html__("News with thumbnails", "rehub-theme"),
    "base" => "news_with_thumbs_mod",
    "category" => esc_html__('Content modules', 'rehub-theme'), 
    'description' => esc_html__('News block', 'rehub-theme'), 
    "icon" => "icon-n-w-thumbs",
    "params" => array(
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Category', 'rehub-theme' ),
            'param_name' => 'module_cats',
            "admin_label" => true,
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of categories. Or leave blank to show all', 'rehub-theme' ),         
        ),
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Category exclude', 'rehub-theme' ),
            'param_name' => 'cat_exclude',
            "admin_label" => true,
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of categories to exclude', 'rehub-theme' ),         
        ),        
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Tags', 'rehub-theme' ),
            'param_name' => 'module_tags',
            "admin_label" => true,
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of tags. Or leave blank to show all', 'rehub-theme' ),         
        ),  
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Tags exclude', 'rehub-theme' ),
            'param_name' => 'tag_exclude',
            "admin_label" => true,
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of tags to exclude.', 'rehub-theme' ),         
        ), 
        array(
            "type" => "dropdown",
            "heading" => esc_html__('Choose post formats', 'rehub-theme'),
            "param_name" => "post_formats",
            "value" => $post_formats,
            "admin_label" => true,
            'description' => esc_html__('Choose post formats to display or leave blank to display all', 'rehub-theme'),          
        ),        
        array(
            'type' => 'colorpicker',
            "admin_label" => true,
            'heading' => esc_html__( 'Color for category label', 'rehub-theme' ),
            'param_name' => 'color_cat',        
        ),        
    )
) );

//NEWS WITHOUT THUMBNAILS BLOCK
vc_map( array(
    "name" => esc_html__("News ticker", "rehub-theme"),
    "base" => "wpsm_news_ticker",
    "category" => esc_html__('Content modules', 'rehub-theme'), 
    'description' => esc_html__('News ticker', 'rehub-theme'),
    "icon" => "icon-n-n-thumbs",    
    "params" => array(
        array(
            "type" => "textfield",
            "heading" => esc_html__('Label', 'rehub-theme'),
            "param_name" => "label",
            'description' => esc_html__('Label before news ticker', 'rehub-theme'),
            "admin_label" => true,            
        ), 
        array(
            "type" => "textfield",
            "heading" => esc_html__('Category name', 'rehub-theme'),
            "param_name" => "catname",
            'description' => esc_html__('Category name to show in ticker', 'rehub-theme'),
            "admin_label" => true,            
        ), 
        array(
            "type" => "textfield",
            "heading" => esc_html__('Category taxonomy', 'rehub-theme'),
            "param_name" => "catslug",
            'description' => esc_html__('Category taxonomy name. Leave blank if you need Post category. For post tags - set as post_tag', 'rehub-theme'),
        ),
        array(
            "type" => "textfield",
            "heading" => esc_html__('Number of posts to show', 'rehub-theme'),
            "param_name" => "fetch",
            'description' => esc_html__('Default is 5', 'rehub-theme'),
        ),                               
       
    )
) );

//VIDEO NEWS BLOCK
vc_map( array(
    "name" => esc_html__('Video playlist block', 'rehub-theme'),
    "base" => "video_mod",
    "icon" => "icon-v-n-block",
    "category" => esc_html__('Content modules', 'rehub-theme'),
    'description' => esc_html__('Youtube/Vimeo gallery', 'rehub-theme'), 
    "params" => array(
        array(
            'type' => 'exploded_textarea',
            'heading' => esc_html__( 'Links on videos', 'rehub-theme' ),
            'description' => esc_html__( 'Each link must be from new line. Works with youtube and vimeo. Example for youtube: https://www.youtube.com/watch?v=ZZZZZZZZZZZ. Example for vimeo: https://vimeo.com/111111111', 'rehub-theme' ),
            'param_name' => 'videolinks',
            "admin_label" => true,
        ), 
        array(
            "param_name" => "playlist_type",
            "type" => "dropdown",
            "value" => array('Playlist' => 'playlist', 'Slider' => 'slider'),
            "admin_label" => true,
            "heading" => esc_html__('Playlist type', 'rehub-theme' ),
            "description" => esc_html__('Video gallery works only with youtube or vimeo, but not at once. Also, playlist type can be only one on page. Slider type can have multiple instances', 'rehub-theme' ),
        ),            
        array(
            "param_name" => "playlist_auto_play",
            "type" => "dropdown",
            "value" => array('OFF' => '0', 'ON' => '1'),
            "heading" => "Autoplay ON / OFF:",
            "admin_label" => true,
            "description" => esc_html__('Autoplay does not work on mobile devices (android, windows phone, iOS)', 'rehub-theme' ),
            'dependency' => array(
                'element' => 'playlist_type',
                'value_not_equal_to' => array( 'slider' ),
            ),             
        ),
        array(
            "param_name" => "playlist_width",
            "type" => "dropdown",
            "value" => array('Full width' => 'full', 'Stack' => 'stack'),
            "admin_label" => true,
            "heading" => esc_html__('Column style', 'rehub-theme' ),
            'dependency' => array(
                'element' => 'playlist_type',
                'value_not_equal_to' => array( 'slider' ),
            ),             
        ),               
        array(
            "param_name" => "playlist_host",
            "type" => "dropdown",
            "value" => array('youtube' => 'youtube', 'vimeo' => 'vimeo'),
            "heading" => "Video host",
            "admin_label" => true,            
        ),                                     
    )
) );

//1-4 tabed block
vc_map( array(
    "name" => esc_html__('Tabbed block', 'rehub-theme'),
    "base" => "tab_mod",
    "icon" => "icon-tab-block",
    "category" => esc_html__('Content modules', 'rehub-theme'),
    'description' => esc_html__('4 tab content block', 'rehub-theme'), 
    "params" => array(
        array(
            "type" => "textfield",
            "heading" => esc_html__('Name for 1 tab*', 'rehub-theme'),
            "param_name" => "module_name_first",
            "admin_label" => true,
            'group' => esc_html__( 'First tab', 'rehub-theme' ),
        ),
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Category', 'rehub-theme' ),
            'param_name' => 'module_cats_first',
            'group' => esc_html__( 'First tab', 'rehub-theme' ),
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of categories. Or leave blank to show all', 'rehub-theme' ),         
        ),
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Category exclude', 'rehub-theme' ),
            'param_name' => 'cat_exclude_first',
            'group' => esc_html__( 'First tab', 'rehub-theme' ),
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of categories to exclude', 'rehub-theme' ),         
        ),        
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Tags', 'rehub-theme' ),
            'param_name' => 'module_tags_first',
            'group' => esc_html__( 'First tab', 'rehub-theme' ),
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of tags. Or leave blank to show all', 'rehub-theme' ),         
        ),  
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Tags exclude', 'rehub-theme' ),
            'param_name' => 'tag_exclude_first',
            'group' => esc_html__( 'First tab', 'rehub-theme' ),
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of tags to exclude.', 'rehub-theme' ),         
        ), 
        array(
            'type' => 'colorpicker',            
            'heading' => esc_html__( 'Color for first tab label', 'rehub-theme' ),
            'param_name' => 'color_cat_first',
            'group' => esc_html__( 'First tab', 'rehub-theme' ),        
        ),
        array(
            "type" => "textfield",
            "heading" => esc_html__('Name for 2 tab*', 'rehub-theme'),
            "param_name" => "module_name_second",
            "admin_label" => true,
            'group' => esc_html__( 'Second tab', 'rehub-theme' ),
        ),
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Category', 'rehub-theme' ),
            'param_name' => 'module_cats_second',
            'group' => esc_html__( 'Second tab', 'rehub-theme' ),
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of categories. Or leave blank to show all', 'rehub-theme' ),         
        ),
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Category exclude', 'rehub-theme' ),
            'param_name' => 'cat_exclude_second',
            'group' => esc_html__( 'Second tab', 'rehub-theme' ),
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of categories to exclude', 'rehub-theme' ),         
        ),        
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Tags', 'rehub-theme' ),
            'param_name' => 'module_tags_second',
            'group' => esc_html__( 'Second tab', 'rehub-theme' ),
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of tags. Or leave blank to show all', 'rehub-theme' ),         
        ),  
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Tags exclude', 'rehub-theme' ),
            'param_name' => 'tag_exclude_second',
            'group' => esc_html__( 'Second tab', 'rehub-theme' ),
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of tags to exclude.', 'rehub-theme' ),         
        ), 
        array(
            'type' => 'colorpicker',
            'heading' => esc_html__( 'Color for tab label', 'rehub-theme' ),
            'param_name' => 'color_cat_second',
            'group' => esc_html__( 'Second tab', 'rehub-theme' ),        
        ),
       array(
            "type" => "textfield",
            "heading" => esc_html__('Name for 3 tab*', 'rehub-theme'),
            "param_name" => "module_name_third",
            "admin_label" => true,
            'group' => esc_html__( 'Third tab', 'rehub-theme' ),
        ),
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Category', 'rehub-theme' ),
            'param_name' => 'module_cats_third',
            'group' => esc_html__( 'Third tab', 'rehub-theme' ),
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of categories. Or leave blank to show all', 'rehub-theme' ),         
        ),
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Category exclude', 'rehub-theme' ),
            'param_name' => 'cat_exclude_third',
            'group' => esc_html__( 'Third tab', 'rehub-theme' ),
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of categories to exclude', 'rehub-theme' ),         
        ),        
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Tags', 'rehub-theme' ),
            'param_name' => 'module_tags_third',
            'group' => esc_html__( 'Third tab', 'rehub-theme' ),
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of tags. Or leave blank to show all', 'rehub-theme' ),         
        ),  
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Tags exclude', 'rehub-theme' ),
            'param_name' => 'tag_exclude_third',
            'group' => esc_html__( 'Third tab', 'rehub-theme' ),
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of tags to exclude.', 'rehub-theme' ),         
        ), 
        array(
            'type' => 'colorpicker',
            'heading' => esc_html__( 'Color for tab label', 'rehub-theme' ),
            'param_name' => 'color_cat_third',
            'group' => esc_html__( 'Third tab', 'rehub-theme' ),        
        ),
       array(
            "type" => "textfield",
            "heading" => esc_html__('Name for 4 tab*', 'rehub-theme'),
            "param_name" => "module_name_fourth",
            "admin_label" => true,
            'group' => esc_html__( 'Fourth tab', 'rehub-theme' ),
        ),
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Category', 'rehub-theme' ),
            'param_name' => 'module_cats_fourth',
            'group' => esc_html__( 'Fourth tab', 'rehub-theme' ),
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of categories. Or leave blank to show all', 'rehub-theme' ),         
        ),
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Category exclude', 'rehub-theme' ),
            'param_name' => 'cat_exclude_fourth',
            'group' => esc_html__( 'Fourth tab', 'rehub-theme' ),
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of categories to exclude', 'rehub-theme' ),         
        ),        
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Tags', 'rehub-theme' ),
            'param_name' => 'module_tags_fourth',
            'group' => esc_html__( 'Fourth tab', 'rehub-theme' ),
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of tags. Or leave blank to show all', 'rehub-theme' ),         
        ),  
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Tags exclude', 'rehub-theme' ),
            'param_name' => 'tag_exclude_fourth',
            "admin_label" => true,
            'group' => esc_html__( 'Fourth tab', 'rehub-theme' ),
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),
            'description' => esc_html__( 'Enter names of tags to exclude.', 'rehub-theme' ),         
        ), 
        array(
            'type' => 'colorpicker',
            'heading' => esc_html__( 'Color for tab label', 'rehub-theme' ),
            'param_name' => 'color_cat_fourth',
            'group' => esc_html__( 'Fourth tab', 'rehub-theme' ),        
        ),

      
    )
) );

//POSTS LISTS
vc_map( array(
    "name" => esc_html__('News/Directory list', 'rehub-theme'),
    "base" => "small_thumb_loop",
    "icon" => "icon-s-t-loop",
    "category" => esc_html__('Content modules', 'rehub-theme'),
    "description" => esc_html__('Left thumbnail', 'rehub-theme'), 
    "params" => rehub_vc_filter_formodules()
));
vc_add_param("small_thumb_loop", 
    array(
        "type" => "dropdown",
        "class" => "",
        "heading" => esc_html__('Set type', 'rehub-theme'),
        "group" => esc_html__('Type', 'rehub-theme'),
        "param_name" => "type",
        "value" => array(
            esc_html__('Directory/Community Style', 'rehub-theme') => "1",
            esc_html__('News Magazine style', 'rehub-theme') => "2",                 
        ),
    )
);
vc_add_params("small_thumb_loop", rehub_vc_aj_filter_btns_formodules());

//Deal, coupon LISTS
vc_map( array(
    "name" => esc_html__('Deal/Coupon List', 'rehub-theme'),
    "base" => "wpsm_offer_list",
    "icon" => "icon-s-t-loop",
    "category" => esc_html__('Content modules', 'rehub-theme'),
    "description" => esc_html__('Use for Post Coupons', 'rehub-theme'), 
    "params" => rehub_vc_filter_formodules()
));
vc_add_params("wpsm_offer_list", rehub_vc_aj_filter_btns_formodules());  
        
//BLOG STYLE LOOP
vc_map( array(
    "name" => esc_html__('Regular blog posts', 'rehub-theme'),
    "base" => "regular_blog_loop",
    "icon" => "icon-r-b-loop",
    "category" => esc_html__('Content modules', 'rehub-theme'),
    "description" => esc_html__('Full width thumbnail', 'rehub-theme'), 
    "params" => rehub_vc_filter_formodules()
)); 
vc_add_params("regular_blog_loop", rehub_vc_aj_filter_btns_formodules());            

//GRID STYLE LOOP
vc_map( array(
    "name" => esc_html__('Masonry grid', 'rehub-theme'),
    "base" => "grid_loop_mod",
    "icon" => "icon-g-l-loop",
    "category" => esc_html__('Content modules', 'rehub-theme'),
    "description" => esc_html__('Masonry grid', 'rehub-theme'),
    "params" => rehub_vc_filter_formodules() 
) );
vc_add_params("grid_loop_mod", array(
    array(
        "type" => "dropdown",
        "class" => "",
        "group" => esc_html__('Control', 'rehub-theme'),
        "heading" => esc_html__('Set columns', 'rehub-theme'),
        "param_name" => "columns",
        "value" => array(
            esc_html__('4 columns', 'rehub-theme') => "4_col",
            esc_html__('3 columns', 'rehub-theme') => "3_col",
            esc_html__('2 columns', 'rehub-theme') => "2_col", 
            esc_html__('5 columns', 'rehub-theme') => "5_col",                 
        ),
        'description' => esc_html__('Use 4 columns only for full width row', 'rehub-theme'), 
    ),
    array(
        "type" => "checkbox",
        "class" => "",
        "group" => esc_html__('Control', 'rehub-theme'),        
        "heading" => esc_html__('Make link as affiliate?', 'rehub-theme'),       
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "aff_link", 
        "description" => esc_html__('This will change all inner post links to affiliate link of post offer', 'rehub-theme'),        
    ), 
));
vc_add_params("grid_loop_mod", rehub_vc_aj_filter_btns_formodules());

//COLUMN GRID
vc_map( array(
    "name" => esc_html__('Posts grid in columns', 'rehub-theme'),
    "base" => "columngrid_loop",
    "icon" => "icon-columngrid",
    "category" => esc_html__('Content modules', 'rehub-theme'),
    "description" => esc_html__('Columned grid', 'rehub-theme'), 
    "params" => rehub_vc_filter_formodules() 
));
vc_add_params("columngrid_loop", array(
    array(
        'type' => 'textfield',
        'heading' => esc_html__( 'Symbols in exerpt', 'rehub-theme' ),
        'param_name' => 'exerpt_count',
        'group' => esc_html__('Control', 'rehub-theme'),
        'value' => '0',
        'description' => esc_html__('Set 0 to disable exerpt', 'rehub-theme'),
    ),        
    array(
        "type" => "checkbox",
        "class" => "",
        "heading" => esc_html__('Disable post meta?', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "disable_meta",  
        'group' => esc_html__('Control', 'rehub-theme'),               
    ), 
    array(
        "type" => "checkbox",
        "class" => "",
        "heading" => esc_html__('Disable price meta?', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "disable_price",  
        'group' => esc_html__('Control', 'rehub-theme'),               
    ), 
    array(
        "type" => "checkbox",
        "class" => "",
        "heading" => esc_html__('Enable padding in images?', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "image_padding",  
        'group' => esc_html__('Control', 'rehub-theme'),               
    ),          
    array(
        "type" => "checkbox",
        "class" => "",
        "heading" => esc_html__('Enable affiliate button?', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "enable_btn",  
        'group' => esc_html__('Control', 'rehub-theme'),               
    ),      
    array(
        "type" => "dropdown",
        "class" => "",
        "heading" => esc_html__('Set columns', 'rehub-theme'),
        "param_name" => "columns",
        'group' => esc_html__('Control', 'rehub-theme'),        
        "value" => array(
            esc_html__('4 columns', 'rehub-theme') => "4_col",            
            esc_html__('2 columns', 'rehub-theme') => "2_col",
            esc_html__('3 columns', 'rehub-theme') => "3_col",            
            esc_html__('5 columns', 'rehub-theme') => "5_col", 
            esc_html__('6 columns', 'rehub-theme') => "6_col",                 
        ),
        'description' => esc_html__('4 columns is good only for full width row', 'rehub-theme'), 
    ),
    array(
        "type" => "checkbox",
        "class" => "",
        "group" => esc_html__('Control', 'rehub-theme'),        
        "heading" => esc_html__('Make link as affiliate?', 'rehub-theme'),       
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "aff_link", 
        "description" => esc_html__('This will change all inner post links to affiliate link of post offer', 'rehub-theme'),        
    ),               
));
vc_add_params("columngrid_loop", rehub_vc_aj_filter_btns_formodules());

//COMPACT GRID STYLE LOOP
vc_map( array(
    "name" => esc_html__('Deal/Coupon grid', 'rehub-theme'),
    "base" => "compactgrid_loop_mod",
    "icon" => "icon-cg-l-loop",
    "category" => esc_html__('Deal helper', 'rehub-theme'),
    "description" => esc_html__('Compact grid', 'rehub-theme'),
    "params" => rehub_vc_filter_formodules()  
));
vc_add_params("compactgrid_loop_mod", array( 
    array(
        "type" => "dropdown",
        "class" => "",
        "heading" => esc_html__('Type', 'rehub-theme'),
        "param_name" => "gridtype",
        'group' => esc_html__('Control', 'rehub-theme'),        
        "value" => array(
            esc_html__('Full Deal Grid', 'rehub-theme') => "full",
            esc_html__('Compact Deal Grid (Coupon)', 'rehub-theme') => "compact",               
        ),
    ),    
    array(
        "type" => "checkbox",
        "class" => "",
        "heading" => esc_html__('Make link as affiliate?', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "aff_link", 
        "group" => esc_html__('Control', 'rehub-theme'),
        "description" => esc_html__('This will change all inner post links to affiliate link from post offer', 'rehub-theme'),               
    ), 
    array(
        "type" => "checkbox",
        "class" => "",
        "heading" => esc_html__('Disable button?', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "disable_btn", 
        "group" => esc_html__('Control', 'rehub-theme'),
        "description" => esc_html__('This will disable button in grid', 'rehub-theme'),        
    ),  
    array(
        "type" => "checkbox",
        "class" => "",
        "heading" => esc_html__('Disable actions?', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "disable_act", 
        "group" => esc_html__('Control', 'rehub-theme'),
        "description" => esc_html__('This will disable thumbs and comment count in bottom', 'rehub-theme'), 
        "dependency" => array("element" => "gridtype", "value" => array("full")),       
    ),    
    array(
        "type" => "dropdown",
        "class" => "",
        "heading" => esc_html__('Show Price meta as', 'rehub-theme'),
        "param_name" => "price_meta",
        "group" => esc_html__('Control', 'rehub-theme'),        
        "value" => array(
            esc_html__('User logo + Price', 'rehub-theme') => "1",
            esc_html__('Brand logo + Price', 'rehub-theme') => "2",
            esc_html__('Only Price', 'rehub-theme') => "3",  
            esc_html__('Nothing', 'rehub-theme') => "4",                                          
        ),
        "dependency" => array("element" => "gridtype", "value" => array("full")),
    ),         
    array(
        "type" => "dropdown",
        "class" => "",
        "heading" => esc_html__('Set columns', 'rehub-theme'),
        "param_name" => "columns",
        "group" => esc_html__('Control', 'rehub-theme'),        
        "value" => array(
            esc_html__('4 columns', 'rehub-theme') => "4_col",            
            esc_html__('3 columns', 'rehub-theme') => "3_col",
            esc_html__('5 columns', 'rehub-theme') => "5_col", 
            esc_html__('6 columns', 'rehub-theme') => "6_col",                              
        ),
        'description' => esc_html__('4 columns is good only for full width row', 'rehub-theme'), 
    ),
)); 
vc_add_params("compactgrid_loop_mod", rehub_vc_aj_filter_btns_formodules()); 

//SMALL NEWS WITHOUT THUMBNAIL
vc_map( array(
    "name" => esc_html__('Simple list', 'rehub-theme'),
    "base" => "wpsm_recent_posts_list",
    "category" => esc_html__('Content modules', 'rehub-theme'),
    "icon" => "icon-s-l-post",
    "description" => esc_html__('Without thumbnails', 'rehub-theme'), 
    "params" => rehub_vc_filter_formodules()
));

vc_add_params("wpsm_recent_posts_list", array(
    array(
        "type" => "checkbox",
        "class" => "",
        "group" => esc_html__('Control', 'rehub-theme'), 
        "heading" => esc_html__("Make center alignment?", "rehub-theme"),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "center",
    ),
    array(
        "type" => "checkbox",
        "class" => "",
        "group" => esc_html__('Control', 'rehub-theme'), 
        "heading" => esc_html__("Add image?", "rehub-theme"),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "image",
    ),
    array(
        "type" => "checkbox",
        "class" => "",
        "group" => esc_html__('Control', 'rehub-theme'), 
        "heading" => esc_html__("Disable meta", "rehub-theme"),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "nometa",
    ),
    array(
        "type" => "checkbox",
        "class" => "",
        "group" => esc_html__('Control', 'rehub-theme'), 
        "heading" => esc_html__("Enable excerpt", "rehub-theme"),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "excerpt",
    ),    
    array(
        "type" => "checkbox",
        "class" => "",
        "group" => esc_html__('Control', 'rehub-theme'), 
        "heading" => esc_html__("Add border to list items", "rehub-theme"),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "border",
    ), 
    array(
        "type" => "dropdown",
        "class" => "",
        "heading" => esc_html__('Show as columns', 'rehub-theme'),
        "group" =>  esc_html__('Control', 'rehub-theme'),
        "param_name" => "columns",
        "admin_label" => true,
        "value" => array(
            '1' => "1",
            '2' => "2", 
            '3' => "3", 
            '4' => "4", 
            '5' => "5", 
            '6' => "6",                                                 
        ),
    ),
    array(
        "type" => "textfield",
        "class" => "",
        "heading" => esc_html__("Search by Title", "rehub-theme"),
        "param_name" => "searchtitle",
        'description' => esc_html__('Set name CURRENTPAGE to show posts with similar title to current page', 'rehub-theme'),         
    ),                  
));

vc_add_params("wpsm_recent_posts_list", rehub_vc_aj_filter_btns_formodules());

//3 COLUMN BLOCK
vc_map( array(
    "name" => esc_html__('3 column posts', 'rehub-theme'),
    "base" => "wpsm_three_col_posts",
    "category" => esc_html__('Content modules', 'rehub-theme'),
    "icon" => "icon-t-c-post",
    "description" => esc_html__('Use for full width row!', 'rehub-theme'), 
    "params" => rehub_vc_filter_formodules()
));
vc_remove_param("wpsm_three_col_posts", "enable_pagination");
vc_remove_param("wpsm_three_col_posts", "show");
vc_add_params("wpsm_three_col_posts", array(
   array(
        "type" => "textfield",
        "heading" => esc_html__('Custom label', 'rehub-theme'),
        "param_name" => "custom_label",
        "admin_label" => true,
        'group' => esc_html__( 'Additional', 'rehub-theme' ),
    ),
    array(
        'type' => 'colorpicker',
        'heading' => esc_html__( 'Color for label', 'rehub-theme' ),
        'param_name' => 'custom_label_color',
        'group' => esc_html__( 'Additional', 'rehub-theme' ),        
    ),
));

//CUSTOM TEXT BLOCK
vc_add_param("vc_column_text", array(
    "type" => "checkbox",
    "class" => "",
    "heading" => esc_html__("Add border to block?", "rehub-theme"),
    "value" => array(__("Yes", "rehub-theme") => true ),
    "param_name" => "bordered",
));

//OFFER BOX
vc_map( array(
    "name" => esc_html__('Offer Box', 'rehub-theme'),
    "base" => "wpsm_offerbox",
    "icon" => "icon-offer-box",
    "category" => esc_html__('Deal helper', 'rehub-theme'),
    'description' => esc_html__('Offer box', 'rehub-theme'), 
    "params" => array(
        array(
            "type" => "textfield",
            "heading" => esc_html__('Offer sale price', 'rehub-theme'),
            "admin_label" => true,
            "param_name" => "price",
        ),  
        array(
            "type" => "textfield",
            "heading" => esc_html__('Offer old price', 'rehub-theme'),
            "admin_label" => true,
            "param_name" => "price_old",
        ),        
        array(
            "type" => "textfield",
            "heading" => esc_html__('Offer url', 'rehub-theme'),
            "param_name" => "button_link",
        ), 
        array(
            "type" => "textfield",
            "heading" => esc_html__('Button text', 'rehub-theme'),
            "param_name" => "button_text",
        ), 
        array(
            "type" => "textfield",
            "heading" => esc_html__('Name of product', 'rehub-theme'),
            "admin_label" => true,
            "param_name" => "title",
        ),
        array(
            "type" => "textfield",
            "heading" => esc_html__('Short description of product', 'rehub-theme'),
            "admin_label" => true,
            "param_name" => "description",
        ),   
        array(
            'type' => 'attach_image',
            'heading' => esc_html__('Upload thumbnail', 'rehub-theme'),
            'param_name' => 'image_id',
            'value' => '',
        ), 
        array(
            "type" => "textfield",
            "heading" => esc_html__('Set coupon code', 'rehub-theme'),
            "admin_label" => true,
            "param_name" => "offer_coupon",
        ),
        array(
            "type" => "checkbox",
            "class" => "",
            "heading" => esc_html__('Mask coupon code?', 'rehub-theme'),
            "value" => array(__("Yes", "rehub-theme") => true ),
            "param_name" => "offer_coupon_mask",
        ),
        array(
            "type" => "textfield",
            "heading" => esc_html__('Set text on mask', 'rehub-theme'),
            "admin_label" => false,
            "param_name" => "offer_coupon_mask_text",
            'dependency' => array(
                'element' => 'offer_coupon_mask',
                'not_empty' => true,
            ),            
        ), 
        array(
            "type" => "textfield",
            "heading" => esc_html__('Expiration Date', 'rehub-theme'),
            "description" => esc_html__( 'Format date-month-year. Example, 20-12-2015', 'rehub-theme' ),
            "admin_label" => false,
            "param_name" => "offer_coupon_date",
        ), 
        array(
            'type' => 'attach_image',
            'heading' => esc_html__('Brand logo', 'rehub-theme'),
            'param_name' => 'logo_image_id',
            'value' => '',
        ),                                                                                                              
    )
) );


if(class_exists( 'WooCommerce' )) {//WOOBLOCKS

//HOME FEATURED SECTION
vc_map( array(
    "name" => esc_html__('Woo Featured section', 'rehub-theme'),
    "base" => "wpsm_woofeatured",
    "icon" => "icon-woofeatured",
    "category" => esc_html__('Woocommerce', 'rehub-theme'),
    "params" => rehub_woo_vc_filter_formodules(),
) );
vc_remove_param("wpsm_woofeatured", "enable_pagination");
vc_remove_param("wpsm_woofeatured", "show");
vc_remove_param("wpsm_woofeatured", "offset");
vc_add_params("wpsm_woofeatured", array(
    array(
        "type" => "dropdown",
        "class" => "",
        "heading" => esc_html__('Type of area', 'rehub-theme'),
        "group" =>  esc_html__('Control', 'rehub-theme'),
        "param_name" => "feat_type",
        "admin_label" => true,
        "value" => array(
            esc_html__('Featured full width slider', 'rehub-theme') => "1",
            esc_html__('Featured grid', 'rehub-theme') => "2",                 
        ),
        'description' => esc_html__('Featured area works only in full width row', 'rehub-theme'), 
    ),        
    array(
        "type" => "checkbox",
        "heading" => esc_html__('Disable exerpt?', 'rehub-theme'),
        "group" =>  esc_html__('Control', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "dis_excerpt",
        "dependency" => Array('element' => "feat_type", 'value' => array('1', '2')),
    ), 
    array(
        "type" => "checkbox",
        "heading" => esc_html__('Show text in left bottom side?', 'rehub-theme'),
        "group" =>  esc_html__('Control', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "bottom_style",
        "dependency" => Array('element' => "feat_type", 'value' => array('1')),
    ),    
    array(
        "type" => "textfield",
        "heading" => esc_html__("Number of posts to show in slider", "rehub-theme"),
        "param_name" => "show",
        "group" =>  esc_html__('Control', 'rehub-theme'),
        "value" => '5',
        "dependency" => Array('element' => "feat_type", 'value' => array('1')),       
    ), 
    array(
        "type" => "textfield",
        "heading" => esc_html__("Custom height (default is 490) in px", "rehub-theme"),
        "param_name" => "custom_height",
        "group" =>  esc_html__('Control', 'rehub-theme'),
        "dependency" => Array('element' => "feat_type", 'value' => array('1')),       
    ),              
        
));    

//WOO CAROUSEL
vc_map( array(
    "name" => esc_html__('Woo commerce product carousel', 'rehub-theme'),
    "base" => "woo_mod",
    "icon" => "icon-woo-mod",
    "category" => esc_html__('Woocommerce', 'rehub-theme'),
    'description' => esc_html__('Works only with Woocommerce', 'rehub-theme'), 
    "params" => rehub_woo_vc_filter_formodules(),
) );
vc_remove_param("woo_mod", "enable_pagination");
vc_add_params("woo_mod", array( 
    array(
        "type" => "checkbox",
        "class" => "",
        "group"  => esc_html__('Control', 'rehub-theme'),
        "heading" => esc_html__('Make link as affiliate?', 'rehub-theme'),       
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "aff_link", 
        "description" => esc_html__('This will change all inner post links to affiliate link of post offer', 'rehub-theme'),        
    ),               
    array(
        "type" => "checkbox",
        "class" => "",      
        "heading" => esc_html__('Make autorotate?', 'rehub-theme'),
        "group"  => esc_html__('Control', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "autorotate",         
    ),  
    array(
        "type" => "dropdown",
        "class" => "",
        "group"  => esc_html__('Control', 'rehub-theme'),
        "heading" => esc_html__('Type', 'rehub-theme'),    
        "param_name" => "carouseltype",
        "value" => array(
            esc_html__('Columned grid', 'rehub-theme') => "columned",   
            esc_html__('Simple grid', 'rehub-theme') => "simple", 
            esc_html__('Review grid', 'rehub-theme') => "review", 
            esc_html__('Compact grid', 'rehub-theme') => "compact",                                                   
        ),
    ),  
    array(
        "type" => "checkbox",
        "class" => "",      
        "heading" => esc_html__('Add fake sold counter', 'rehub-theme'),
        "group"  => esc_html__('Control', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "soldout",         
    ),       
    array(
        "type" => "dropdown",
        "class" => "",
        "group"  => esc_html__('Control', 'rehub-theme'),
        "heading" => esc_html__('Number of items in row', 'rehub-theme'),    
        "param_name" => "showrow",
        "value" => array(
            esc_html__('5', 'rehub-theme') => "5",            
            esc_html__('4', 'rehub-theme') => "4",
            esc_html__('3', 'rehub-theme') => "3",   
            esc_html__('6', 'rehub-theme') => "6",                                                   
        ),
    ),        
)); 

//WOO OFFER BOX
vc_map( array(
    "name" => esc_html__('Woo Box', 'rehub-theme'),
    "base" => "wpsm_woobox",
    "icon" => "icon-woo-offer-box",
    "category" => esc_html__('Woocommerce', 'rehub-theme'),
    'description' => esc_html__('Woocommerce product box', 'rehub-theme'), 
    "params" => array(
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Set Product name', 'rehub-theme' ),
            'param_name' => 'id',
            "admin_label" => true,
            'settings' => array(
                'multiple' => false,
                'sortable' => false,
                'groups' => false,
            ),
            'description' => esc_html__( 'Type name of product', 'rehub-theme' ),                           
        ),                                                              
    )
) );

//WOO LIST
vc_map( array(
    "name" => esc_html__('List of woo products', 'rehub-theme'),
    "base" => "wpsm_woolist",
    "icon" => "icon-woolist",
    "category" => esc_html__('Woocommerce', 'rehub-theme'),
    'description' => esc_html__('Works only with Woocommerce', 'rehub-theme'), 
    "params" => rehub_woo_vc_filter_formodules(),
));

//WOO ROWS
vc_map( array(
    "name" => esc_html__('Rows of woo products', 'rehub-theme'),
    "base" => "wpsm_woorows",
    "icon" => "icon-woolist",
    "category" => esc_html__('Woocommerce', 'rehub-theme'),
    'description' => esc_html__('Works only with Woocommerce', 'rehub-theme'), 
    "params" => rehub_woo_vc_filter_formodules(),
));

//WOO GRID
vc_map( array(
    "name" => esc_html__('Grid of woocommerce products', 'rehub-theme'),
    "base" => "wpsm_woogrid",
    "icon" => "icon-woogrid",
    "category" => esc_html__('Woocommerce', 'rehub-theme'),
    'description' => esc_html__('Works only with Woocommerce', 'rehub-theme'),
    "params" => rehub_woo_vc_filter_formodules(),
));
vc_add_params("wpsm_woogrid", array( 
    array(
        "type" => "dropdown",
        "class" => "",
        "heading" => esc_html__('Set columns', 'rehub-theme'),
        "param_name" => "columns",
        "group"  => esc_html__('Control', 'rehub-theme'),
        "value" => array(
            esc_html__('4 columns', 'rehub-theme') => "4_col",            
            esc_html__('3 columns', 'rehub-theme') => "3_col",
            esc_html__('5 columns', 'rehub-theme') => "5_col",
            esc_html__('6 columns', 'rehub-theme') => "6_col",                  
        ), 
    ), 
    array(
        "type" => "dropdown",
        "class" => "",
        "heading" => esc_html__('Style of design', 'rehub-theme'),
        "param_name" => "gridtype",
        "group"  => esc_html__('Control', 'rehub-theme'),        
        "value" => array(
            esc_html__('Regular', 'rehub-theme') => "regular",                
            esc_html__('Compact', 'rehub-theme') => "compact", 
            esc_html__('Review', 'rehub-theme') => "review",                             
        ), 
    ),      
    array(
        "type" => "dropdown",
        "class" => "",
        "heading" => esc_html__('Show link from title and image on', 'rehub-theme'),
        "param_name" => "woolinktype",
        "group"  => esc_html__('Control', 'rehub-theme'),        
        "value" => array(
            esc_html__('Product page', 'rehub-theme') => "product",                
            esc_html__('Affiliate link', 'rehub-theme') => "aff",                              
        ), 
    ), 
    array(
        "type" => "checkbox",
        "class" => "",      
        "heading" => esc_html__('Custom image size?', 'rehub-theme'),
        "description"=> 'Use only if your image is blured',
        "group"  => esc_html__('Control', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "custom_col",         
    ),
    array(
        "type" => "checkbox",
        "class" => "",      
        "heading" => esc_html__('Add fake sold counter', 'rehub-theme'),
        "group"  => esc_html__('Control', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "soldout",         
    ),     
    array(
        "type" => "textfield",
        "heading" => esc_html__('Width of image in px', 'rehub-theme'),
        "group"  => esc_html__('Control', 'rehub-theme'),        
        "param_name" => "custom_img_width", 
        "dependency" => Array('element' => "custom_col", 'not_empty' => true),        
    ),
    array(
        "type" => "textfield",
        "heading" => esc_html__('Height of image in px', 'rehub-theme'),
        "param_name" => "custom_img_height", 
        "group"  => esc_html__('Control', 'rehub-theme'),        
        "dependency" => Array('element' => "custom_col", 'not_empty' => true),                 
    ),                     
)); 
vc_add_params("wpsm_woogrid", rehub_vc_aj_filter_btns_formodules());

//WOO COLUMNS
vc_map( array(
    "name" => esc_html__('Columns of woocommerce products', 'rehub-theme'),
    "base" => "wpsm_woocolumns",
    "icon" => "icon-woocolumns",
    "category" => esc_html__('Woocommerce', 'rehub-theme'),
    'description' => esc_html__('Works only with Woocommerce', 'rehub-theme'),
    "params" => rehub_woo_vc_filter_formodules(),
));
vc_add_params("wpsm_woocolumns", array( 
    array(
        "type" => "dropdown",
        "class" => "",
        "heading" => esc_html__('Set columns', 'rehub-theme'),
        "param_name" => "columns",
        "group"  => esc_html__('Control', 'rehub-theme'),
        "value" => array(
            esc_html__('4 columns', 'rehub-theme') => "4_col",             
            esc_html__('3 columns', 'rehub-theme') => "3_col",
            esc_html__('5 columns', 'rehub-theme') => "5_col", 
            esc_html__('6 columns', 'rehub-theme') => "6_col",                                         
        ), 
    ),  
    array(
        "type" => "dropdown",
        "class" => "",
        "heading" => esc_html__('Show link from title and image on', 'rehub-theme'),
        "param_name" => "woolinktype",
        "group"  => esc_html__('Control', 'rehub-theme'),        
        "value" => array(
            esc_html__('Product page', 'rehub-theme') => "product",                
            esc_html__('Affiliate link', 'rehub-theme') => "aff",                              
        ), 
    ),
    array(
        "type" => "checkbox",
        "class" => "",      
        "heading" => esc_html__('Custom image size?', 'rehub-theme'),
        "description"=> 'Use only if your image is blured',
        "group"  => esc_html__('Control', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "param_name" => "custom_col",         
    ), 
    array(
        "type" => "textfield",
        "heading" => esc_html__('Width of image in px', 'rehub-theme'),
        "group"  => esc_html__('Control', 'rehub-theme'),        
        "param_name" => "custom_img_width", 
        "dependency" => Array('element' => "custom_col", 'not_empty' => true),        
    ),
    array(
        "type" => "textfield",
        "heading" => esc_html__('Height of image in px', 'rehub-theme'),
        "param_name" => "custom_img_height", 
        "group"  => esc_html__('Control', 'rehub-theme'),        
        "dependency" => Array('element' => "custom_col", 'not_empty' => true),                 
    ),        
)); 
vc_add_params("wpsm_woocolumns", rehub_vc_aj_filter_btns_formodules());

//Compare Bars
vc_map( array(
    'name' => esc_html__('Woo Compare Bars', 'rehub-theme'),
    'base' => 'wpsm_woo_versus',
    'icon' => 'icon-wpsm-woo-versus',
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    'description' => esc_html__('Woo attribute comparisons', 'rehub-theme'), 
    'params' => array(
        array(
            'type' => 'colorpicker',
            'heading' => esc_html__( 'Color', 'rehub-theme' ),
            'param_name' => 'color', 
            'description' => 'Set default color or leave empty to leave default color as grey'                
        ), 
        array(
            'type' => 'colorpicker',
            'heading' => esc_html__( 'Highlight Color', 'rehub-theme' ),
            'param_name' => 'markcolor', 
            'description' => 'Set highlighted color or leave empty to leave default color as orange'                
        ), 
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Product names for compare', 'rehub-theme' ),
            'param_name' => 'ids',
            "admin_label" => true,
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),                     
        ),   
        array(
            'type' => 'autocomplete',
            'heading' => esc_html__( 'Attribute names', 'rehub-theme' ),
            'description' => 'Choose attributes which have numeric values, other will have errors',
            'param_name' => 'attr',
            "admin_label" => true,
            'settings' => array(
                'multiple' => true,
                'sortable' => true,
                'groups' => false,
            ),                         
        ), 
        array(
            'type' => 'textfield',           
            'heading' => esc_html__('Attribute for minimum priority', 'rehub-theme'),
            'param_name' => 'min',
            'description' => 'By default, bar with maximum value will be highlighted. You can set here number of attribute which will be highlighted with minimum value. For example, if you choosed 5 attributes above, set number 3 if you want to highlight minimum in third attribute. For multiple, use comma divider. For example: 3,5'                
        ),                                    

    )
) );

}

//PROS BLOCK
vc_map( array(
    "name" => esc_html__('Pros block', 'rehub-theme'),
    "base" => "wpsm_pros",
    "icon" => "icon-pros",
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    'description' => esc_html__('List of positives', 'rehub-theme'), 
    "params" => array(  
        array(
            "type" => "textfield",
            "heading" => esc_html__('Pros title', 'rehub-theme'),
            "param_name" => "title",
            "value" => esc_html__('PROS:', 'rehub-theme'),           
        ),
        array(
            'type' => 'textarea_html',
            'heading' => esc_html__( 'Content', 'rehub-theme' ),
            'param_name' => 'content',
            "admin_label" => true,
            'value' => '<ul><li>Positive 1</li><li>Positive 2</li><li>Positive 3</li><li>Positive 4</li></ul>',
        ),                                                              
    )
) );

//CONS BLOCK
vc_map( array(
    "name" => esc_html__('Cons block', 'rehub-theme'),
    "base" => "wpsm_cons",
    "icon" => "icon-cons",
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    'description' => esc_html__('List of negatives', 'rehub-theme'), 
    "params" => array(  
        array(
            "type" => "textfield",
            "heading" => esc_html__('Cons title', 'rehub-theme'),
            "param_name" => "title",
            "value" => esc_html__('CONS:', 'rehub-theme'),           
        ),
        array(
            'type' => 'textarea_html',
            'heading' => esc_html__( 'Content', 'rehub-theme' ),
            'param_name' => 'content',
            "admin_label" => true,
            'value' => '<ul><li>Negative 1</li><li>Negative 2</li><li>Negative 3</li><li>Negative 4</li></ul>',
        ),                                                              
    )
) );

//IMAGE CAROUSEL BLOCK
vc_map( array(
    "name" => esc_html__("Image carousel", "rehub-theme"),
    "base" => "gal_carousel",
    'deprecated' => '5.0',
    "icon" => "icon-gal-carousel",
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    'description' => esc_html__('For row with sidebar', 'rehub-theme'), 
    "params" => array(
        array(
            "type" => "attach_images",
            "heading" => esc_html__("Images", "rehub-theme"),
            "param_name" => "images",
            "value" => "",
            "description" => esc_html__("Select images from media library.", "rehub-theme")
        ),
        array(
            "type" => "dropdown",
            "heading" => esc_html__("On click", "rehub-theme"),
            "param_name" => "onclick",
            "value" => array(__("Open Lightbox", "rehub-theme") => "link_image", esc_html__("Do nothing", "rehub-theme") => "link_no", esc_html__("Open custom link", "rehub-theme") => "custom_link"),
            "description" => esc_html__("What to do when slide is clicked?", "rehub-theme")
        ),
        array(
            "type" => "exploded_textarea",
            "heading" => esc_html__("Custom links", "rehub-theme"),
            "param_name" => "custom_links",
            "description" => esc_html__('Enter links for each slide here. Divide links with linebreaks (Enter).', 'rehub-theme'),
            "dependency" => Array('element' => "onclick", 'value' => array('custom_link'))
        ),
        array(
            "type" => "dropdown",
            "heading" => esc_html__("Custom link target", "rehub-theme"),
            "param_name" => "custom_links_target",
            "description" => esc_html__('Select where to open  custom links.', 'rehub-theme'),
            "dependency" => Array('element' => "onclick", 'value' => array('custom_link')),
            'value' => $target_arr
        ),
        array(
            "type" => "textfield",
            "heading" => esc_html__("Extra class name", "rehub-theme"),
            "param_name" => "el_class",
            "description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "rehub-theme")
        )
    )
) );
require_once vc_path_dir('SHORTCODES_DIR', 'vc-gallery.php');
if ( class_exists( 'WPBakeryShortCode_VC_gallery' ) ) {
    class WPBakeryShortCode_Gal_Carousel extends WPBakeryShortCode_VC_gallery {

    }
}

//SEARCHBOX
vc_map( array(
    "name" => esc_html__('Search box', 'rehub-theme'),
    "base" => "wpsm_searchbox",
    "icon" => "icon-searchbox",
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    'description' => esc_html__('Searchbox', 'rehub-theme'), 
    "params" => array(
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Type of search', 'rehub-theme'),
            "admin_label" => true,
            "param_name" => "search_type",
            "value" => array(
                esc_html__('Posts', 'rehub-theme') => "post",
                esc_html__('Taxonomy', 'rehub-theme') => "tax",                 
            ), 
        ),     
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Where to search', 'rehub-theme'),
            "admin_label" => true,
            "param_name" => "by",
            "dependency" => array("element" => "search_type", "value" => array("post")),
            'value' => rehub_post_type_vc(),
        ), 
        array(
            "type" => "textfield",
            "heading" => esc_html__('Taxonomy', 'rehub-theme'),
            "admin_label" => true,
            "description" => esc_html__('You can set several with commas. Be aware of taxonomies with too much items.', 'rehub-theme'),
            "param_name" => "tax",
            "dependency" => array("element" => "search_type", "value" => array("tax")),          
        ),  
        array(
            "type" => "textfield",
            "heading" => esc_html__('Only inside category', 'rehub-theme'),
            "description" => esc_html__('You can search items only in category Ids, separate by comma', 'rehub-theme'),
            "param_name" => "catid",
            "dependency" => array("element" => "by", "value" => array("post")),          
        ),                
        array(
            "type" => "checkbox",
            "class" => "",
            "heading" => esc_html__('Enable ajax search?', 'rehub-theme'),
            "value" => array(__("Yes", "rehub-theme") => true ),
            "dependency" => array("element" => "search_type", "value" => array("post")),            
            "param_name" => "enable_ajax",         
        ),                         
        array(
            "type" => "textfield",
            "heading" => esc_html__('Placeholder', 'rehub-theme'),
            "admin_label" => true,
            "param_name" => "placeholder",          
        ), 
        array(
            "type" => "textfield",
            "heading" => esc_html__('Text on button', 'rehub-theme'),
            "description" => esc_html__('Or leave blank to show search icon only', 'rehub-theme'),
            "param_name" => "label",         
        ),         
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Color of button', 'rehub-theme'),
            "param_name" => "color",
            "admin_label" => true,
            "value" => array(
                esc_html__('Main Button Color', 'rehub-theme') => "btncolor",
                esc_html__('Main Theme Color', 'rehub-theme') => "main",
                esc_html__('Secondary Theme Color', 'rehub-theme') => "secondary",
                esc_html__('orange', 'rehub-theme') => "orange",
                esc_html__('gold', 'rehub-theme') => "gold",
                esc_html__('black', 'rehub-theme') => "black",  
                esc_html__('blue', 'rehub-theme') => "blue",
                esc_html__('red', 'rehub-theme') => "red",
                esc_html__('green', 'rehub-theme') => "green",  
                esc_html__('rosy', 'rehub-theme') => "rosy",
                esc_html__('brown', 'rehub-theme') => "brown",
                esc_html__('pink', 'rehub-theme') => "pink",
                esc_html__('purple', 'rehub-theme') => "purple",
                esc_html__('teal', 'rehub-theme') => "teal",                
            )
        ),                                                                          
    )
) );

//TESTIMONIAL
vc_map( array(
    "name" => esc_html__('Testimonial', 'rehub-theme'),
    "base" => "wpsm_testimonial",
    "icon" => "icon-testimonial",
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    'description' => esc_html__('Testimonial box', 'rehub-theme'), 
    "params" => array(  
        array(
            "type" => "textfield",
            "heading" => esc_html__('Author', 'rehub-theme'),
            "param_name" => "by",
            'description' => esc_html__('Add author or leave blank.', 'rehub-theme'),            
        ),
        array(
            'type' => 'textarea_html',
            'heading' => esc_html__( 'Content', 'rehub-theme' ),
            "admin_label" => true,
            'param_name' => 'content',
            'value' => esc_html__( 'Content goes here, click edit button to change this text.', 'rehub-theme' ),
        ),                                                              
    )
) );

//LIST
vc_map( array(
    "name" => esc_html__('Styled list', 'rehub-theme'),
    "base" => "wpsm_list",
    "icon" => "icon-s-list",
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    'description' => esc_html__('Styled simple list', 'rehub-theme'), 
    "params" => array(  
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Type of list', 'rehub-theme'),
            "param_name" => "type",
            "value" => array(
                esc_html__('Arrow', 'rehub-theme') => "arrow",
                esc_html__('Check', 'rehub-theme') => "check",  
                esc_html__('Star', 'rehub-theme') => "star",
                esc_html__('Bullet', 'rehub-theme') => "bullet"
            )
        ),
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Type of gap', 'rehub-theme'),
            "param_name" => "gap",
            "value" => array(
                esc_html__('Default', 'rehub-theme') => "default",
                esc_html__('Small', 'rehub-theme') => "small"
            )
        ), 
        array(
            "type" => "checkbox",
            "class" => "",
            "heading" => esc_html__('Pretty hover?', 'rehub-theme'),
            "value" => array(__("Yes", "rehub-theme") => true ),
            "param_name" => "hover",         
        ), 
        array(
            "type" => "checkbox",
            "class" => "",
            "heading" => esc_html__('Make link with dark color?', 'rehub-theme'),
            "value" => array(__("Yes", "rehub-theme") => true ),
            "param_name" => "darklink",         
        ),                        
        array(
            'type' => 'textarea_html',
            'heading' => esc_html__( 'Content', 'rehub-theme' ),
            "admin_label" => true,
            'param_name' => 'content',
            'value' => '<ul><li>Item 1</li><li>Item 2</li><li>Item 3</li><li>Item 4</li></ul>',
        ),                                                              
    )
) );

//NUMBERED HEADING
vc_map( array(
    "name" => esc_html__('Numbered Headings', 'rehub-theme'),
    "base" => "wpsm_numhead",
    "icon" => "icon-numhead",
    'deprecated' => '4.9',
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    'description' => esc_html__('Numbered Headings', 'rehub-theme'), 
    "params" => array(  
        array(
            "type" => "textfield",
            "heading" => esc_html__('Number', 'rehub-theme'),
            "param_name" => "num",
            "value" => '1',           
        ),
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Style of number', 'rehub-theme'),
            "param_name" => "style",
            "admin_label" => true,
            "value" => array(
                esc_html__('Orange', 'rehub-theme') => "3",
                esc_html__('Black', 'rehub-theme') => "2",  
                esc_html__('Grey', 'rehub-theme') => "1",
                esc_html__('Blue', 'rehub-theme') => "4"
            )
        ), 
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Heading', 'rehub-theme'),
            "param_name" => "heading",
            "value" => array(
                "H2" => "2",    
                "H1" => "1",
                "H3" => "3",
                "H4" => "4",
            )
        ),             
        array(
            "type" => "textarea",
            "heading" => esc_html__('Text', 'rehub-theme'),
            "param_name" => "content",
            "admin_label" => true,
            "value" => 'Lorem ipsum dolor sit amet',           
        ),                                                              
    )
) );

//NUMBERED BOX
vc_map( array(
    "name" => esc_html__('Box with number', 'rehub-theme'),
    "base" => "wpsm_numbox",
    "icon" => "icon-numbox",
    'deprecated' => '4.9',
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    'description' => esc_html__('Box with number', 'rehub-theme'), 
    "params" => array(  
        array(
            "type" => "textfield",
            "heading" => esc_html__('Number', 'rehub-theme'),
            "param_name" => "num",
            "value" => '1',           
        ),
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Style of number', 'rehub-theme'),
            "param_name" => "style",
            "admin_label" => true,
            "value" => array(
                esc_html__('Orange', 'rehub-theme') => "3",
                esc_html__('Black', 'rehub-theme') => "2",  
                esc_html__('Grey', 'rehub-theme') => "1",
                esc_html__('Blue', 'rehub-theme') => "4",
                esc_html__('White no border', 'rehub-theme') => "5",
                esc_html__('Black no border', 'rehub-theme') => "6"
            )
        ), 
        array(
            'type' => 'textarea_html',
            'heading' => esc_html__( 'Content', 'rehub-theme' ),
            'param_name' => 'content',
            "admin_label" => true,
            'value' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim',
        ),                     
                                                            
    )
) );

//CART BOX
vc_map( array(
    "name" => esc_html__('Card box', 'rehub-theme'),
    "base" => "wpsm_cartbox",
    "icon" => "icon-cartbox",
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    'description' => esc_html__('Box with image', 'rehub-theme'), 
    "params" => array(  
        array(
            "type" => "textfield",
            "heading" => esc_html__('Title', 'rehub-theme'),
            "param_name" => "title",
            "value" => '', 
            "admin_label" => true,                      
        ),
        array(
            "type" => "textfield",
            "heading" => esc_html__('Description', 'rehub-theme'),
            "param_name" => "description",
            "value" => '', 
            "admin_label" => true,          
        ), 
        array(
            'type' => 'attach_image',
            'heading' => esc_html__('Image', 'rehub-theme'),
            'param_name' => 'image',
            'value' => '',
        ),
        array(
            'type' => 'dropdown',
            'heading' => esc_html__( 'Choose design', 'rehub-theme' ),
            'param_name' => 'design',
            'value' => array(
                esc_html__( 'Full width image', 'rehub-theme' ) => '1',
                esc_html__( 'Image in Right (compact)', 'rehub-theme' ) => '2',
            ),
        ),
        array(
            "type" => "vc_link",
            "heading" => esc_html__('URL:', 'rehub-theme'),
            "param_name" => "link",
            'description' => esc_html__('Will be used on image and title', 'rehub-theme'),
            "admin_label" => true,
        ),                
        array(
            "type" => "checkbox",
            "class" => "",
            "heading" => esc_html__('Make background image contain?', 'rehub-theme'),
            "value" => array(__("Yes", "rehub-theme") => true ),
            "param_name" => "bg_contain", 
            "dependency" => array("element" => "design", "value" => array("1")),                     
        ),  
        array(
            "type" => "checkbox",
            "class" => "",
            "heading" => esc_html__('Show image first?', 'rehub-theme'),
            "value" => array(__("Yes", "rehub-theme") => true ),
            "param_name" => "revert_image", 
            "dependency" => array("element" => "design", "value" => array("1")),                     
        ),               
                                              
                                                            
    )
) );

//CATEGORY BOX
vc_map( array(
    "name" => esc_html__('Category box', 'rehub-theme'),
    "base" => "wpsm_catbox",
    "icon" => "icon-cartbox",
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    'description' => esc_html__('Box for category cart', 'rehub-theme'), 
    "params" => array(  
        array(
            "type" => "textfield",
            "heading" => esc_html__('Title', 'rehub-theme'),
            "param_name" => "title",
            "value" => '', 
            "admin_label" => true,                      
        ),
        array(
            'type' => 'attach_image',
            'heading' => esc_html__('Image', 'rehub-theme'),
            'param_name' => 'image',
            'value' => '',
        ),        
        array(
            "type" => "textfield",
            "heading" => esc_html__('Image size', 'rehub-theme'),
            "description" => esc_html__('Leave blank or try to change size to better fit for image. Example, 170px or 50%', 'rehub-theme'),
            "param_name" => "size_img",
            "value" => '',           
        ), 
        array(
            "type" => "checkbox",
            "class" => "",
            "heading" => esc_html__('Disable link from title', 'rehub-theme'),
            "value" => array(__("Yes", "rehub-theme") => true ),
            "param_name" => "disablelink",         
        ),  
        array(
            "type" => "checkbox",
            "class" => "",
            "heading" => esc_html__('Disable child elements', 'rehub-theme'),
            "value" => array(__("Yes", "rehub-theme") => true ),
            "param_name" => "disablechild",         
        ),                      
        array(
            "type" => "textfield",
            "heading" => esc_html__('Category ID or slug:', 'rehub-theme'),
            "param_name" => "category",
            'description' => esc_html__('Place ID of category. You can use also Category slug, but in this case you must add taxonomy slug, for example, product_cat is slug for woocommerce categories, category is slug for posts categories. ', 'rehub-theme'),
        ),
        array(
            "type" => "textfield",
            "heading" => esc_html__('Taxonomy slug', 'rehub-theme'),
            "param_name" => "taxslug",
            'description' => esc_html__('Leave blank if you use Category ID. Set here "category" for posts if you use slug in field above or "product_cat" if you use slug and woocommerce products', 'rehub-theme'),            
        ),                                                       
                                                            
    )
) );

//TITLED BOX
vc_map( array(
    "name" => esc_html__('Titled box', 'rehub-theme'),
    "base" => "wpsm_titlebox",
    "icon" => "icon-titlebox",
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    'description' => esc_html__('Box with border and title', 'rehub-theme'), 
    "params" => array(  
        array(
            "type" => "textfield",
            "heading" => esc_html__('Title', 'rehub-theme'),
            "param_name" => "title",
            "value" => esc_html__('Title of box', 'rehub-theme'),           
        ),
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Style', 'rehub-theme'),
            "param_name" => "style",
            "admin_label" => true,
            "value" => array(
                esc_html__('Grey', 'rehub-theme') => "1",
                esc_html__('Black', 'rehub-theme') => "2",  
                esc_html__('Orange', 'rehub-theme') => "3",
                esc_html__('Double dotted', 'rehub-theme') => "4"
            )
        ), 
        array(
            'type' => 'textarea_html',
            'heading' => esc_html__( 'Content', 'rehub-theme' ),
            'param_name' => 'content',
            "admin_label" => true,
            'value' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim',
        ),                     
                                                            
    )
) );

//COLORED TABLE
vc_map( array(
    "name" => esc_html__('Colored Table', 'rehub-theme'),
    "base" => "wpsm_colortable",
    "icon" => "icon-colortable",
    'deprecated' => '4.9',
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    'description' => esc_html__('Table with color header', 'rehub-theme'), 
    "params" => array(  
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Color of heading table :', 'rehub-theme'),
            "param_name" => "color",
            "admin_label" => true,
            "value" => array(
                esc_html__('grey', 'rehub-theme') => "grey",
                esc_html__('black', 'rehub-theme') => "black",  
                esc_html__('yellow', 'rehub-theme') => "yellow",
                esc_html__('blue', 'rehub-theme') => "blue",
                esc_html__('red', 'rehub-theme') => "red",
                esc_html__('green', 'rehub-theme') => "green",  
                esc_html__('orange', 'rehub-theme') => "orange",
                esc_html__('purple', 'rehub-theme') => "purple",                
            )
        ), 
        array(
            'type' => 'textarea_html',
            'heading' => esc_html__( 'Content', 'rehub-theme' ),
            'param_name' => 'content',
            'value' => '<table>
                            <thead>
                                <tr>
                                    <th style="width: 25%;">Heading 1</th>
                                    <th style="width: 25%;">Heading 2</th>
                                    <th style="width: 25%;">Heading 3</th>
                                    <th style="width: 25%;">Heading 4</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Value</td>
                                    <td>Value</td>
                                    <td>Value</td>
                                    <td>Value</td>
                                </tr>
                                <tr class="odd">
                                    <td>Value</td>
                                    <td>Value</td>
                                    <td>Value</td>
                                    <td>Value</td>
                                </tr>
                                <tr>
                                    <td>Value</td>
                                    <td>Value</td>
                                    <td>Value</td>
                                    <td>Value</td>
                                </tr>
                                <tr class="odd">
                                    <td>Value</td>
                                    <td>Value</td>
                                    <td>Value</td>
                                    <td>Value</td>
                                </tr>
                            </tbody>
                        </table>',
        ),                                                                              
    )
) );

//PRICE TABLES
vc_map( array(
    "name" => esc_html__("Price table", "rehub-theme"),
    "base" => "wpsm_price_table",
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    "icon" => "icon-pricetable",    
    "as_parent" => array('only' => 'wpsm_price_column'),
    "content_element" => true,
    "show_settings_on_create" => false,
    "params" => array(
        array(
            "type" => "textfield",
            "heading" => esc_html__("Extra class name", "rehub-theme"),
            "param_name" => "el_class",
        )
    ),
    "js_view" => 'VcColumnView'
) );
vc_map( array(
    "name" => esc_html__("Price table column", "rehub-theme"),
    "base" => "wpsm_price_column",
    "icon" => "icon-pricetable", 
    "content_element" => true,
    "as_child" => array('only' => 'wpsm_price_table'), 
    "params" => array(
        array(
            "type" => "dropdown",
            "heading" => esc_html__('Column size', 'rehub-theme'),
            "param_name" => "size",
            "value" => array(
                '1/3' => "3",
                "1/4" => "4",   
                "1/5" => "5",
                "1/2" => "2"
            )
        ),
        array(
            "type" => "dropdown",
            "heading" => esc_html__('Featured', 'rehub-theme'),
            "param_name" => "featured",
            "value" => array(
                esc_html__('No', 'rehub-theme') => "no",
                esc_html__('Yes', 'rehub-theme') => "yes",  
            )
        ),
        array(
            "type" => "textfield",
            "heading" => esc_html__('Title', 'rehub-theme'),
            "admin_label" => true,
            "param_name" => "name",
            "value" => esc_html__('Title of box', 'rehub-theme'),           
        ),
        array(
            "type" => "textfield",
            "heading" => esc_html__('Price', 'rehub-theme'),
            "admin_label" => true,
            "param_name" => "price",
            'edit_field_class' => 'vc_col-md-6 vc_column',
            "value" => esc_html__('$99.99', 'rehub-theme'),           
        ),  
        array(
            "type" => "textfield",
            "heading" => esc_html__('Per', 'rehub-theme'),
            "param_name" => "per",
            'edit_field_class' => 'vc_col-md-6 vc_column',
            "value" => esc_html__('month', 'rehub-theme'),           
        ),  
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Color', 'rehub-theme'),
            "param_name" => "color",
            "value" => array(
                esc_html__('orange', 'rehub-theme') => "orange",
                esc_html__('gold', 'rehub-theme') => "gold",
                esc_html__('black', 'rehub-theme') => "black",  
                esc_html__('blue', 'rehub-theme') => "blue",
                esc_html__('red', 'rehub-theme') => "red",
                esc_html__('green', 'rehub-theme') => "green",  
                esc_html__('rosy', 'rehub-theme') => "rosy",
                esc_html__('brown', 'rehub-theme') => "brown",
                esc_html__('pink', 'rehub-theme') => "pink",
                esc_html__('purple', 'rehub-theme') => "purple",
                esc_html__('teal', 'rehub-theme') => "teal",                
            )
        ), 
        array(
            "type" => "textfield",
            "heading" => esc_html__('Button URL', 'rehub-theme'),
            "param_name" => "button_url",       
        ),
        array(
            "type" => "textfield",
            "heading" => esc_html__('Button text', 'rehub-theme'),
            "param_name" => "button_text",
            "value" => "Buy this",       
        ), 
        array(
            'type' => 'textarea_html',
            'heading' => esc_html__( 'List of items', 'rehub-theme' ),
            'param_name' => 'content',
            'value' => '<ul><li>Item 1</li><li>Item 2</li><li>Item 3</li><li>Item 4</li></ul>',
        ),                                                          
    )
) );

if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
    class WPBakeryShortCode_Wpsm_Price_Table extends WPBakeryShortCodesContainer {
    }
}
if ( class_exists( 'WPBakeryShortCode' ) ) {
    class WPBakeryShortCode_Wpsm_Price_Column extends WPBakeryShortCode {
    }
}

//MEMBER BLOCK CONTENT
vc_map( array(
    "name" => esc_html__('Text for members block', 'rehub-theme'),
    "base" => "wpsm_member",
    "icon" => "icon-memberbox",
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    'description' => esc_html__('Hide from guests', 'rehub-theme'), 
    "params" => array(  
        array(
            "type" => "textfield",
            "heading" => esc_html__('Text for guests', 'rehub-theme'),
            "admin_label" => true,
            "param_name" => "guest_text",
            "value" => esc_html__('Please, login or register to view this content', 'rehub-theme'),           
        ),
        array(
            'type' => 'textarea_html',
            'heading' => esc_html__( 'Content', 'rehub-theme' ),
            'param_name' => 'content',
            "admin_label" => true,
            'value' => esc_html__( 'Text for members', 'rehub-theme' ),
        ),                                                              
    )
) );

//POPUP BUTTON
vc_map( array(
    "name" => esc_html__('Button with popup', 'rehub-theme'),
    "base" => "wpsm_button_popup",
    "icon" => "icon-button_popup",
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    'description' => esc_html__('Popup on button click', 'rehub-theme'), 
    "params" => array( 
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Color of button', 'rehub-theme'),
            "param_name" => "color",
            "value" => array(
                esc_html__('orange', 'rehub-theme') => "orange",
                esc_html__('gold', 'rehub-theme') => "gold",
                esc_html__('black', 'rehub-theme') => "black",  
                esc_html__('blue', 'rehub-theme') => "blue",
                esc_html__('red', 'rehub-theme') => "red",
                esc_html__('green', 'rehub-theme') => "green",  
                esc_html__('rosy', 'rehub-theme') => "rosy",
                esc_html__('brown', 'rehub-theme') => "brown",
                esc_html__('pink', 'rehub-theme') => "pink",
                esc_html__('purple', 'rehub-theme') => "purple",
                esc_html__('teal', 'rehub-theme') => "teal",                
            )
        ),
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Button Size', 'rehub-theme'),
            "param_name" => "size",
            "value" => array(
                esc_html__('Medium', 'rehub-theme') => "medium",                
                esc_html__('Small', 'rehub-theme') => "small",
                esc_html__('Big', 'rehub-theme') => "big",                  
            )
        ),
        array(
            "type" => "checkbox",
            "class" => "",
            "heading" => esc_html__('Enable icon in button?', 'rehub-theme'),
            "value" => array(__("Yes", "rehub-theme") => true ),
            "param_name" => "enable_icon",         
        ),        
        array(
            'type' => 'iconpicker',
            'heading' => esc_html__( 'Icon', 'rehub-theme' ),
            'param_name' => 'icon',
            'value' => '',
            'settings' => array(
                'emptyIcon' => true,
                'iconsPerPage' => 100,
            ),
            "dependency" => Array('element' => "enable_icon", 'not_empty' => true),
        ),                     
        array(
            "type" => "textfield",
            "heading" => esc_html__('Button text', 'rehub-theme'),
            "admin_label" => true,
            "param_name" => "btn_text",         
        ),
        array(
            "type" => "textfield",
            "heading" => esc_html__('Max width of popup', 'rehub-theme'),
            "param_name" => "max_width",
            "value" => 500         
        ),        
        array(
            'type' => 'textarea_html',
            'heading' => esc_html__( 'Content', 'rehub-theme' ),
            'param_name' => 'content',
            "admin_label" => true,
            'value' => esc_html__( 'Content of popup. You can use also shortcode', 'rehub-theme' ),
        ),                                                              
    )
) );

//CTA BLOCK
vc_add_param("vc_cta_button2", array(
    'type' => 'colorpicker',
    'heading' => esc_html__( 'Text Color', 'rehub-theme' ),
    'param_name' => 'text_color',
));

//TABS BLOCK
if (defined('WPB_VC_VERSION') && version_compare(WPB_VC_VERSION, '4.6.0', '>=')) {
    vc_remove_param("vc_tta_tabs", "title");
    vc_add_param("vc_tta_tabs", array(
        "type" => "checkbox",
        "heading" => esc_html__('Overwrite design of tabs with theme settings?', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "weight" => 100,
        "param_name" => "style_rehub",
    ));
    vc_add_param("vc_tta_tabs", array(
        "type" => "checkbox",
        "heading" => esc_html__('Enable design of tabs without border?', 'rehub-theme'),
        "value" => array(__("Yes", "rehub-theme") => true ),
        "weight" => 99,
        "param_name" => "style_sec",
    ));
        
}

//MDTF
vc_map( array(
    "name" => esc_html__('MDTF shortcode', 'rehub-theme'),
    "base" => "mdtf_shortcode",
    "icon" => "icon-mdtf",
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    'description' => esc_html__('Works only with MDTF', 'rehub-theme'), 
    "params" => array(
        array(
            "type" => "dropdown",
            "class" => "",
            "heading" => esc_html__('Output template', 'rehub-theme'),
            "param_name" => "data_source",
            "value" => array(
                esc_html__('Columned grid loop', 'rehub-theme') => "template/column",
                esc_html__('Deal grid', 'rehub-theme') => "template/grid",  
                esc_html__('Full width Deal grid', 'rehub-theme') => "template/grid_full",                 
                esc_html__('List loop', 'rehub-theme') => "template/list",
                esc_html__('Review list - use only for posts', 'rehub-theme') => "template/reviewlist",                
                esc_html__('Woocommerce column - use only with woocommerce enabled', 'rehub-theme') => "template/woocolumn",
                esc_html__('Woocommerce grid - use only with woocommerce enabled', 'rehub-theme') => "template/woogrid",
                esc_html__('Woocommerce list - use only with woocommerce enabled', 'rehub-theme') => "template/woolist",                
            ), 
            "admin_label" => true,
        ),
        array(
            'type' => 'dropdown',
            'heading' => esc_html__( 'Choose post type', 'rehub-theme' ),
            'param_name' => 'post_type',
            'value' => rehub_post_type_vc(),
            'dependency' => array(
                'element' => 'data_source',
                'value_not_equal_to' => array( 'woocommerce'),
            ),            
        ),              
            
        array(
            'type' => 'dropdown',
            'heading' => esc_html__( 'Order by', 'rehub-theme' ),
            'param_name' => 'orderby',
            'value' => array(
                esc_html__( 'Date', 'rehub-theme' ) => 'date',
                esc_html__( 'Order by post ID', 'rehub-theme' ) => 'ID',
                esc_html__( 'Title', 'rehub-theme' ) => 'title',
                esc_html__( 'Last modified date', 'rehub-theme' ) => 'modified',
                esc_html__( 'Number of comments', 'rehub-theme' ) => 'comment_count',
                esc_html__( 'Menu order/Page Order', 'rehub-theme' ) => 'menu_order',
                esc_html__( 'Random order', 'rehub-theme' ) => 'rand',
            ),
        ),
        array(
            'type' => 'dropdown',
            'heading' => esc_html__( 'Sorting', 'rehub-theme' ),
            'param_name' => 'order',
            'value' => array(
                esc_html__( 'Descending', 'rehub-theme' ) => 'DESC',
                esc_html__( 'Ascending', 'rehub-theme' ) => 'ASC',
            ),
            'description' => esc_html__( 'Select sorting order.', 'rehub-theme' ),
        ),  
        array(
            "type" => "textfield",
            "heading" => esc_html__('Fetch Count', 'rehub-theme'),
            "param_name" => "show",
            "value" => '9',
            'description' => esc_html__('Number of products to display', 'rehub-theme'),         
        ),                                       
        array(
            'type' => 'dropdown',
            'heading' => esc_html__( 'Pagination position', 'rehub-theme' ),
            'param_name' => 'pag_pos',
            'value' => array(
                esc_html__( 'Top and bottom', 'rehub-theme' ) => 'tb',
                esc_html__( 'Top', 'rehub-theme' ) => 't',
                esc_html__( 'Bottom', 'rehub-theme' ) => 'b',
            ),            
        ), 
        array(
            "type" => "textfield",
            "heading" => esc_html__('Taxonomies', 'rehub-theme'),
            "param_name" => "tax",
            "value" => '',
            'description' => esc_html__('if you want to show posts of any custom taxonomies. Example of setting this field: taxonomies=product_cat+77,96,12', 'rehub-theme'),         
        ), 
        array(
            "type" => "textfield",
            "heading" => esc_html__('ID of sort panel', 'rehub-theme'),
            "param_name" => "sortid",
            "value" => '',
            'description' => esc_html__('if you want to sort panel before posts, write id of panel', 'rehub-theme'),         
        ),                        
        array(
            "type" => "checkbox",
            "heading" => esc_html__('Enable ajax?', 'rehub-theme'),
            "value" => array(__("Yes", "rehub-theme") => true ),
            "param_name" => "ajax",   
        ),                                                             
    )
) );
if ( class_exists( 'WPBakeryShortCode' ) ) {
    class WPBakeryShortCode_Mdtf_Shortcode extends WPBakeryShortCode {
    }
}

//WPSM HOVER BANNER
vc_map( array(
    'name' => esc_html__('Hover Banner', 'rehub-theme'),
    'base' => 'wpsm_hover_banner',
    'icon' => 'icon-banner-hover',
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    'description' => esc_html__('Animated Hover banner', 'rehub-theme'), 
    'params' => array(
        array(
            'type' => 'textfield',
            'heading' => esc_html__('Title', 'rehub-theme'),
            'admin_label' => true,
            'param_name' => 'title',
        ), 
        array(
            'type' => 'textfield',
            'heading' => esc_html__('Size', 'rehub-theme'),
            'param_name' => 'firstsize',
            'description' => esc_html__( 'With px, em, etc. Example: 2em. Default is 1.7em', 'rehub-theme' ),            
        ),          
        array(
            'type' => 'textfield',
            'heading' => esc_html__('Subtitle', 'rehub-theme'),
            'admin_label' => true,
            'param_name' => 'subtitle',
        ),
        array(
            'type' => 'textfield',
            'heading' => esc_html__('Size', 'rehub-theme'),
            'param_name' => 'secondsize',
            'description' => esc_html__( 'With px, em, etc. Example: 2em. Default is 1.1em', 'rehub-theme' ),              
        ),        
        array(
            'type' => 'attach_image',
            'heading' => esc_html__('Upload background', 'rehub-theme'),
            'param_name' => 'image_id',
            'value' => '',
        ),
        array(
            'type' => 'colorpicker',
            'heading' => esc_html__( 'Or set background color', 'rehub-theme' ),
            'param_name' => 'bg',                 
        ),         
        array(
            'type' => 'checkbox',
            'class' => '',
            'heading' => esc_html__('Enable Icon?', 'rehub-theme'),
            'value' => array(__('Yes', 'rehub-theme') => true ),
            'param_name' => 'enable_icon',         
        ),        
        array(
            'type' => 'iconpicker',
            'heading' => esc_html__( 'Icon', 'rehub-theme' ),
            'param_name' => 'icon',
            'value' => 'rhicon rhi-gift',
            'settings' => array(
                'emptyIcon' => true,
                'iconsPerPage' => 100,
            ),
            'dependency' => Array('element' => 'enable_icon', 'not_empty' => true),
        ),
        array(
            'type' => 'colorpicker',
            'heading' => esc_html__( 'Icon and Hover border Color', 'rehub-theme' ),
            'param_name' => 'color',                 
        ), 
        array(
            'type' => 'colorpicker',
            'heading' => esc_html__( 'Text Color', 'rehub-theme' ),
            'param_name' => 'colortext',                  
        ),         
        array(
            'type' => 'textfield',
            'heading' => esc_html__('Height, px', 'rehub-theme'),
            'param_name' => 'height',
            'value' => '',
        ),
        array(
            'type' => 'textfield',
            'heading' => esc_html__('Padding, px', 'rehub-theme'),
            'param_name' => 'padding',
            'value' => '40',
        ),
        array(
            'type' => 'dropdown',
            'class' => '',
            'heading' => esc_html__('Text Position', 'rehub-theme'),
            'param_name' => 'align',
            'value' => array(
                esc_html__('Left', 'rehub-theme') => 'left',
                esc_html__('Right', 'rehub-theme') => 'right',
                esc_html__('Center', 'rehub-theme') => 'center',                
            ), 
        ),
        array(
            'type' => 'dropdown',
            'heading' => esc_html__( 'Vertical align', 'rehub-theme' ),
            'param_name' => 'vertical',
            'value' => array(
                esc_html__( 'Middle', 'rehub-theme' ) => 'middle',
                esc_html__( 'Top', 'rehub-theme' ) => 'top',
                esc_html__( 'Bottom', 'rehub-theme' ) => 'bottom',
            ),
        ),        
        array(
            'type' => 'textfield',
            'heading' => esc_html__('Banner URL', 'rehub-theme'),
            'param_name' => 'url',
            'value' => '',
        ),
        array(
            'type' => 'checkbox',
            'class' => '',
            'heading' => esc_html__('Open in the same window?', 'rehub-theme'),
            'value' => array(__('Yes', 'rehub-theme') => true ),
            'param_name' => 'targetself',         
        ),         
        array(
            'type' => 'checkbox',
            'class' => '',
            'heading' => esc_html__('Enable Overlay?', 'rehub-theme'),
            'value' => array(__('Yes', 'rehub-theme') => true ),
            'param_name' => 'overlay',
        )
    )
) );

//VERSUS LINE
vc_map( array(
    'name' => esc_html__('Versus Line', 'rehub-theme'),
    'base' => 'wpsm_versus',
    'icon' => 'icon-wpsm-versus',
    "category" => esc_html__('Helper modules', 'rehub-theme'),
    'description' => esc_html__('Versus lines builder', 'rehub-theme'), 
    'params' => array(
        array(
            'type' => 'textfield',
            'heading' => esc_html__('Heading', 'rehub-theme'),
            'admin_label' => true,
            'param_name' => 'heading',
        ),           
        array(
            'type' => 'textfield',
            'heading' => esc_html__('Subheading', 'rehub-theme'),
            'admin_label' => true,
            'param_name' => 'subheading',
        ),
        array(
            'type' => 'colorpicker',
            'heading' => esc_html__( 'Background color (optional)', 'rehub-theme' ),
            'param_name' => 'bg',                 
        ), 
        array(
            'type' => 'colorpicker',
            'heading' => esc_html__( 'Text color (optional)', 'rehub-theme' ),
            'param_name' => 'color',                 
        ),
        array(
            'type' => 'dropdown',
            'class' => '',
            'heading' => esc_html__('Type', 'rehub-theme'),
            'param_name' => 'type',
            'value' => array(
                esc_html__('Two Column', 'rehub-theme') => 'two',
                esc_html__('Three Column', 'rehub-theme') => 'three',               
            ), 
        ),        
        array(
            'type' => 'dropdown',
            'class' => '',
            'heading' => esc_html__('First Column Type', 'rehub-theme'),
            'param_name' => 'firstcolumntype',
            "group" =>  esc_html__('First Column', 'rehub-theme'),
            'value' => array(
                esc_html__('Text', 'rehub-theme') => 'text',
                esc_html__('Image', 'rehub-theme') => 'image', 
                esc_html__('Check Icon', 'rehub-theme') => 'tick',
                esc_html__('Cross Icon', 'rehub-theme') => 'times',                                               
            ), 
        ),
        array(
            'type' => 'textfield',
            "group" =>  esc_html__('First Column', 'rehub-theme'),            
            'heading' => esc_html__('Place text', 'rehub-theme'),
            'param_name' => 'firstcolumncont',
            "dependency" => array("element" => "firstcolumntype", "value" => array("text")),             
        ),        
        array(
            'type' => 'attach_image',
            "group" =>  esc_html__('First Column', 'rehub-theme'),            
            'heading' => esc_html__('Upload Image', 'rehub-theme'),
            'param_name' => 'firstcolumnimg',
            'value' => '',
            "dependency" => array("element" => "firstcolumntype", "value" => array("image")),            
        ),
        array(
            'type' => 'checkbox',
            'class' => '',
            "group" =>  esc_html__('First Column', 'rehub-theme'),            
            'heading' => esc_html__('Make first column unhighlighted?', 'rehub-theme'),
            'value' => array(__('Yes', 'rehub-theme') => true ),
            'param_name' => 'firstcolumngrey',         
        ),  

        array(
            'type' => 'dropdown',
            'class' => '',
            "group" =>  esc_html__('Second Column', 'rehub-theme'),            
            'heading' => esc_html__('Second Column Type', 'rehub-theme'),           
            'param_name' => 'secondcolumntype',
            'value' => array(
                esc_html__('Text', 'rehub-theme') => 'text',
                esc_html__('Image', 'rehub-theme') => 'image', 
                esc_html__('Check Icon', 'rehub-theme') => 'tick',
                esc_html__('Cross Icon', 'rehub-theme') => 'times',                                               
            ), 
        ),
        array(
            'type' => 'textfield',
            "group" =>  esc_html__('Second Column', 'rehub-theme'),            
            'heading' => esc_html__('Place text', 'rehub-theme'),
            'param_name' => 'secondcolumncont',
            "dependency" => array("element" => "secondcolumntype", "value" => array("text")),             
        ),        
        array(
            'type' => 'attach_image',
            "group" =>  esc_html__('Second Column', 'rehub-theme'),            
            'heading' => esc_html__('Upload Image', 'rehub-theme'),
            'param_name' => 'secondcolumnimg',
            'value' => '',
            "dependency" => array("element" => "secondcolumntype", "value" => array("image")),            
        ),
        array(
            'type' => 'checkbox',
            'class' => '',
            "group" =>  esc_html__('Second Column', 'rehub-theme'),            
            'heading' => esc_html__('Make second column unhighlighted?', 'rehub-theme'),
            'value' => array(__('Yes', 'rehub-theme') => true ),
            'param_name' => 'secondcolumngrey',         
        ),


        array(
            'type' => 'dropdown',
            'class' => '',
            "group" =>  esc_html__('Third Column', 'rehub-theme'),            
            'heading' => esc_html__('Third Column Type', 'rehub-theme'),           
            'param_name' => 'thirdcolumntype',
            'value' => array(
                esc_html__('Text', 'rehub-theme') => 'text',
                esc_html__('Image', 'rehub-theme') => 'image', 
                esc_html__('Check Icon', 'rehub-theme') => 'tick',
                esc_html__('Cross Icon', 'rehub-theme') => 'times',                                               
            ), 
            "dependency" => array("element" => "type", "value" => array("three")),            
        ),
        array(
            'type' => 'textfield',
            "group" =>  esc_html__('Third Column', 'rehub-theme'),            
            'heading' => esc_html__('Place text', 'rehub-theme'),
            'param_name' => 'thirdcolumncont',
            "dependency" => array("element" => "thirdcolumntype", "value" => array("text")),             
        ),        
        array(
            'type' => 'attach_image',
            "group" =>  esc_html__('Third Column', 'rehub-theme'),            
            'heading' => esc_html__('Upload Image', 'rehub-theme'),
            'param_name' => 'thirdcolumnimg',
            'value' => '',
            "dependency" => array("element" => "thirdcolumntype", "value" => array("image")),            
        ),
        array(
            'type' => 'checkbox',
            "group" =>  esc_html__('Third Column', 'rehub-theme'),            
            'class' => '',
            'heading' => esc_html__('Make third column unhighlighted?', 'rehub-theme'),
            'value' => array(__('Yes', 'rehub-theme') => true ),
            'param_name' => 'thirdcolumngrey', 
            "dependency" => array("element" => "type", "value" => array("three")),                     
        ),           

    )
) );


//CUSTOM BLOCKS FOR CHILD THEMES
include ( rh_locate_template( 'functions/vc_functions_theme.php' ) );

}
?>