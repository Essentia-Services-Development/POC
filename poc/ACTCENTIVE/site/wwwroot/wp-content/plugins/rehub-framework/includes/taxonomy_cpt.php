<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
   

//Add new column in term list
function rh_woostore_group_column( $columns ){
    $columns['rhwoostore'] = esc_html__( 'Logo', 'rehub-framework' );
    return $columns;
}
function rh_add_rhwoostore_column_content( $content, $column_name, $term_id ){
    if( $column_name !== 'rhwoostore' ){
        return $content;
    }
    $term_id = absint( $term_id );
    $rhwoostoreimage = get_term_meta( $term_id, 'brandimage', true );
    if( !empty( $rhwoostoreimage ) ){
        $content .= '<img src="'.$rhwoostoreimage.'" width=50 />';
    }
    return $content;
}

//Affiliate Store taxonomy for posts
if(REHub_Framework::get_option('enable_brand_taxonomy') == 1){

    //Creating store taxonomy
    if(!function_exists('post_dealstore_init')){
        function post_dealstore_init() {
            register_taxonomy(
                'dealstore',
                'post',
                array(
                    'labels' => array(
                        'name'              => esc_html__( 'Affiliate Store', 'rehub-framework' ),
                        'singular_name'     => esc_html__( 'Affiliate Store', 'rehub-framework' ),
                        'search_items'      => esc_html__( 'Search store', 'rehub-framework' ),
                        'all_items'         => esc_html__( 'All stores', 'rehub-framework' ),
                        'parent_item'       => esc_html__( 'Parent store', 'rehub-framework' ),
                        'parent_item_colon' => esc_html__( 'Parent store:', 'rehub-framework' ),
                        'edit_item'         => esc_html__( 'Edit store', 'rehub-framework' ),
                        'update_item'       => esc_html__( 'Update store', 'rehub-framework' ),
                        'add_new_item'      => esc_html__( 'Add new store', 'rehub-framework' ),
                        'new_item_name'     => esc_html__( 'New store name', 'rehub-framework' ),
                        'menu_name'         => esc_html__( 'Affiliate Store', 'rehub-framework' ),
                    ),      
                    'show_ui' => true,
                    'show_admin_column' => true,
                    'update_count_callback' => '_update_post_term_count',
                    'hierarchical' => true,
                    'public' => true,
                    'query_var' => true,
                    'show_in_quick_edit' => true,
                    'rewrite' => array( 'slug' => (REHub_Framework::get_option('rehub_deal_store_tag') !='') ? REHub_Framework::get_option('rehub_deal_store_tag') : 'dealstore' ),
                    'show_in_rest' => true
                )
            );
        }
    }
    add_action( 'init', 'post_dealstore_init' );  

    //Adding column to store page in admin page
    add_filter('manage_dealstore_custom_column', 'rh_add_rhwoostore_column_content', 10, 3 ); 
    add_filter('manage_edit-dealstore_columns', 'rh_woostore_group_column' ); 
}

if(!function_exists('offer_expiration_init')){
    function offer_expiration_init() {
        register_taxonomy(
            'offerexpiration',
            'post',
            array(
                'labels' => array(
                    'name'              => esc_html__( 'Expired', 'rehub-framework' ),
                    'singular_name'     => esc_html__( 'Expired', 'rehub-framework' ),
                    'search_items'      => esc_html__( 'Expired', 'rehub-framework' ),
                    'all_items'         => esc_html__( 'Expired', 'rehub-framework' ),
                    'edit_item'         => esc_html__( 'Edit', 'rehub-framework' ),
                    'update_item'       => esc_html__( 'Update', 'rehub-framework' ),
                    'add_new_item'      => esc_html__( 'Add new', 'rehub-framework' ),
                    'new_item_name'     => esc_html__( 'New', 'rehub-framework' ),
                    'menu_name'         => esc_html__( 'Expired', 'rehub-framework' ),
                ),                 
                'hierarchical' => true,
                'show_ui'           => true,
                'show_admin_column' => false,
                'show_in_nav_menus' => false,
                'query_var'         => true,
                'rewrite'           => false,
                'public'            => true,
                'show_in_quick_edit' => true,
                'show_in_rest' => true
            )
        );
    }
    add_action( 'init', 'offer_expiration_init' ); 
}

if(REHub_Framework::get_option('enable_blog_posttype') == 1){
    //Create separate Blog post type
    if ( ! function_exists('rh_blog_create_posttype') ) {
    // Register Custom Post Type
    function rh_blog_create_posttype() {

        $labels = array(
            'name'                  => esc_html__( 'Blog', 'rehub-framework' ),
            'singular_name'         => esc_html__( 'Blog', 'rehub-framework' ),
            'menu_name'             => esc_html__( 'Blog posts', 'rehub-framework' ),
            'name_admin_bar'        => esc_html__( 'Blog', 'rehub-framework' ),
            'archives'              => esc_html__( 'Item Archives', 'rehub-framework' ),
            'parent_item_colon'     => esc_html__( 'Parent Item:', 'rehub-framework' ),
            'all_items'             => esc_html__( 'All Items', 'rehub-framework' ),
            'add_new_item'          => esc_html__( 'Add New Item', 'rehub-framework' ),
            'add_new'               => esc_html__( 'Add New', 'rehub-framework' ),
            'new_item'              => esc_html__( 'New Item', 'rehub-framework' ),
            'edit_item'             => esc_html__( 'Edit Item', 'rehub-framework' ),
            'update_item'           => esc_html__( 'Update Item', 'rehub-framework' ),
            'view_item'             => esc_html__( 'View Item', 'rehub-framework' ),
            'search_items'          => esc_html__( 'Search Item', 'rehub-framework' ),
            'not_found'             => esc_html__( 'Not found', 'rehub-framework' ),
            'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'rehub-framework' ),
            'featured_image'        => esc_html__( 'Featured Image', 'rehub-framework' ),
            'set_featured_image'    => esc_html__( 'Set featured image', 'rehub-framework' ),
            'remove_featured_image' => esc_html__( 'Remove featured image', 'rehub-framework' ),
            'use_featured_image'    => esc_html__( 'Use as featured image', 'rehub-framework' ),
            'insert_into_item'      => esc_html__( 'Insert into item', 'rehub-framework' ),
            'uploaded_to_this_item' => esc_html__( 'Uploaded to this item', 'rehub-framework' ),
            'items_list'            => esc_html__( 'Items list', 'rehub-framework' ),
            'items_list_navigation' => esc_html__( 'Items list navigation', 'rehub-framework' ),
            'filter_items_list'     => esc_html__( 'Filter items list', 'rehub-framework' ),
        );
        $args = array(
            'label'                 => esc_html__( 'Blog', 'rehub-framework' ),
            'description'           => esc_html__( 'Blog Description', 'rehub-framework' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'trackbacks', 'revisions', 'custom-fields', ),
            'taxonomies'            => array( 'blog_category', 'blog_tag' ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,        
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
            'show_in_rest'          => true,
            'rewrite' => array( 'slug' => (REHub_Framework::get_option('blog_posttype_slug') !='') ? REHub_Framework::get_option('blog_posttype_slug') : 'blog' ),
        );
        register_post_type( 'blog', $args );
    }
    }

    if ( ! function_exists( 'rh_blog_category' ) ) {
    // Register Custom Taxonomy
    function rh_blog_category() {
        $labels = array(
            'name'                       => _x( 'Blog categories', 'Taxonomy General Name', 'rehub-framework' ),
            'singular_name'              => _x( 'Blog category', 'Taxonomy Singular Name', 'rehub-framework' ),
            'menu_name'                  => esc_html__( 'Blog category', 'rehub-framework' ),
            'all_items'                  => esc_html__( 'All categories', 'rehub-framework' ),
            'parent_item'                => esc_html__( 'Parent Item', 'rehub-framework' ),
            'parent_item_colon'          => esc_html__( 'Parent Item:', 'rehub-framework' ),
            'new_item_name'              => esc_html__( 'New category', 'rehub-framework' ),
            'add_new_item'               => esc_html__( 'Add category', 'rehub-framework' ),
            'edit_item'                  => esc_html__( 'Edit category', 'rehub-framework' ),
            'update_item'                => esc_html__( 'Update category', 'rehub-framework' ),
            'view_item'                  => esc_html__( 'View category', 'rehub-framework' ),
            'separate_items_with_commas' => esc_html__( 'Separate items with commas', 'rehub-framework' ),
            'add_or_remove_items'        => esc_html__( 'Add or remove items', 'rehub-framework' ),
            'choose_from_most_used'      => esc_html__( 'Choose from the most used', 'rehub-framework' ),
            'popular_items'              => esc_html__( 'Popular Items', 'rehub-framework' ),
            'search_items'               => esc_html__( 'Search Items', 'rehub-framework' ),
            'not_found'                  => esc_html__( 'Not Found', 'rehub-framework' ),
            'no_terms'                   => esc_html__( 'No items', 'rehub-framework' ),
            'items_list'                 => esc_html__( 'Items list', 'rehub-framework' ),
            'items_list_navigation'      => esc_html__( 'Items list navigation', 'rehub-framework' ),       
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'          => true,
            'rewrite' => array( 'slug' => (REHub_Framework::get_option('blog_posttypecat_slug') !='') ? REHub_Framework::get_option('blog_posttypecat_slug') : 'blog_category' ),         
        );
        register_taxonomy( 'blog_category', array( 'blog' ), $args );
    }
    }

    if ( ! function_exists( 'rh_blog_tag' ) ) {
    // Register Custom Taxonomy
    function rh_blog_tag() {
        $labels = array(
            'name'                       => _x( 'Blog tags', 'Taxonomy General Name', 'rehub-framework' ),
            'singular_name'              => _x( 'Blog tag', 'Taxonomy Singular Name', 'rehub-framework' ),
            'menu_name'                  => esc_html__( 'Blog tag', 'rehub-framework' ),
            'all_items'                  => esc_html__( 'All tags', 'rehub-framework' ),
            'parent_item'                => esc_html__( 'Parent Item', 'rehub-framework' ),
            'parent_item_colon'          => esc_html__( 'Parent Item:', 'rehub-framework' ),
            'new_item_name'              => esc_html__( 'New tag', 'rehub-framework' ),
            'add_new_item'               => esc_html__( 'Add tag', 'rehub-framework' ),
            'edit_item'                  => esc_html__( 'Edit tag', 'rehub-framework' ),
            'update_item'                => esc_html__( 'Update tag', 'rehub-framework' ),
            'view_item'                  => esc_html__( 'View tag', 'rehub-framework' ),
            'separate_items_with_commas' => esc_html__( 'Separate items with commas', 'rehub-framework' ),
            'add_or_remove_items'        => esc_html__( 'Add or remove items', 'rehub-framework' ),
            'choose_from_most_used'      => esc_html__( 'Choose from the most used', 'rehub-framework' ),
            'popular_items'              => esc_html__( 'Popular Items', 'rehub-framework' ),
            'search_items'               => esc_html__( 'Search Items', 'rehub-framework' ),
            'not_found'                  => esc_html__( 'Not Found', 'rehub-framework' ),
            'no_terms'                   => esc_html__( 'No items', 'rehub-framework' ),
            'items_list'                 => esc_html__( 'Items list', 'rehub-framework' ),
            'items_list_navigation'      => esc_html__( 'Items list navigation', 'rehub-framework' ),        
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => false,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'          => true,
            'rewrite' => array( 'slug' => (REHub_Framework::get_option('blog_posttypetag_slug') !='') ? REHub_Framework::get_option('blog_posttypetag_slug') : 'blog_tag' ),        
        );
        register_taxonomy( 'blog_tag', array( 'blog' ), $args );
    }
    }

    add_action( 'init', 'rh_blog_create_posttype', 0 );
    add_action( 'init', 'rh_blog_tag', 0 );
    add_action( 'init', 'rh_blog_category', 0 );
}


//EXTEND WORDPRESS TABLE OF POST
add_filter('manage_post_posts_columns', 'rh_admin_expired_table_head');
function rh_admin_expired_table_head( $defaults ) {
    $defaults['expiration_date']  = 'Expiration Date';
    $defaults['expiration_status']    = 'Expired';
    return $defaults;
}

add_action( 'manage_post_posts_custom_column', 'rh_admin_expired_table_content', 10, 2 );
function rh_admin_expired_table_content( $column_name, $post_id ) {
    if ($column_name == 'expiration_date') {
        $offer_coupon_date = get_post_meta( $post_id, 'rehub_offer_coupon_date', true );
        if($offer_coupon_date){
        echo  date( _x( 'F d, Y', 'Event date format', 'rehub-framework' ), strtotime( $offer_coupon_date ) );
        }
    }
    if ($column_name == 'expiration_status') {
        $expiration_status = get_post_meta( $post_id, 're_post_expired', true );
        if($expiration_status){ 
            echo '<span style="font-size:18px; color:red">&#128467;</span>';
        }
    }

}

add_filter( 'manage_edit-post_sortable_columns', 'rh_admin_expired_table_sorting' );
function rh_admin_expired_table_sorting( $columns ) {
  $columns['expiration_date'] = 'expiration_date';
  $columns['expiration_status'] = 'expiration_status';
  return $columns;
}

add_filter( 'request', 'rh_admin_expired_date_column_orderby' );
function rh_admin_expired_date_column_orderby( $vars ) {
    if ( isset( $vars['orderby'] ) && 'expiration_date' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => 'rehub_offer_coupon_date',
            'orderby' => 'meta_value'
        ) );
    }

    return $vars;
}

add_filter( 'request', 'rh_admin_expired_status_column_orderby' );
function rh_admin_expired_status_column_orderby( $vars ) {
    if ( isset( $vars['orderby'] ) && 'expiration_status' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => 're_post_expired',
            'orderby' => 'meta_value'
        ) );
    }

    return $vars;
}

add_action( 'plugins_loaded', 'rh_brand_taxonomy_init' );
function rh_brand_taxonomy_init(){
    if(class_exists('Woocommerce')){
        remove_filter( 'woocommerce_product_loop_start', 'woocommerce_maybe_show_product_subcategories' );
        add_filter('manage_store_custom_column', 'rh_add_rhwoostore_column_content', 10, 3 );
        add_filter('manage_edit-store_columns', 'rh_woostore_group_column' );     
        if (!class_exists('WC_RH_Store_Taxonomy')) {
            class WC_RH_Store_Taxonomy {
                /**
                 * Constructor.
                 */
                public function __construct() {
                    add_action( 'woocommerce_register_taxonomy', array( $this, 'woo_product_store_init' ) );
                    add_action( 'current_screen', array( $this, 'conditional_includes' ) );
                }
                /**
                * Create Store Taxonomy
                */
                public function woo_product_store_init() {
                    
                    $permalinks = get_option( 'woocommerce_permalinks' );
                    register_taxonomy(
                        'store',
                        'product',
                        array(
                            'labels' => array(
                                'name'              => esc_html__( 'Brand', 'rehub-framework' ),
                                'singular_name'     => esc_html__( 'Brand', 'rehub-framework' ),
                                'search_items'      => esc_html__( 'Search brand', 'rehub-framework' ),
                                'all_items'         => esc_html__( 'All brands', 'rehub-framework' ),
                                'parent_item'       => esc_html__( 'Parent brand', 'rehub-framework' ),
                                'parent_item_colon' => esc_html__( 'Parent brand:', 'rehub-framework' ),
                                'edit_item'         => esc_html__( 'Edit brand', 'rehub-framework' ),
                                'update_item'       => esc_html__( 'Update brand', 'rehub-framework' ),
                                'add_new_item'      => esc_html__( 'Add new brand', 'rehub-framework' ),
                                'new_item_name'     => esc_html__( 'New brand name', 'rehub-framework' ),
                                'menu_name'         => esc_html__( 'Brand', 'rehub-framework' ),
                            ),      
                            'show_ui' => true,
                            'show_admin_column' => true,
                            'update_count_callback' => '_update_post_term_count',
                            'hierarchical' => true,
                            'public' => true,
                            'query_var' => empty( $permalinks['store_base'] ) ? 'brand' : $permalinks['store_base'],
                            'show_in_quick_edit' => true,
                            'rewrite' =>array(
                                'slug' => empty( $permalinks['store_base'] ) ? 'brand' : $permalinks['store_base'],
                                'with_front'   => false,
                                'hierarchical' => true,
                            ),
                            'show_in_rest' => true
                        )
                    );
                }
                /**
                 * Include admin files conditionally.
                 */
                public function conditional_includes() {
                    if ( ! $screen = get_current_screen() ) {
                        return;
                    }
                    switch ( $screen->id ) {
                        case 'options-permalink' :
                            include( 'woo_store_permalink_class.php' );
                        break;
                    }
                }
            }
        }
        new WC_RH_Store_Taxonomy();
    }
}

function rh_set_custom_edit_elementor_library_posts_columns($columns) {
    //unset( $columns['author'] );
    $columns['ae_shortcode_column'] = esc_html__( 'Shortcode', 'wts_ae' );
    return $columns;
}
function rh_add_elementor_library_columns( $column, $post_id ) {
    switch ( $column ) {

        case 'ae_shortcode_column' :
            echo '<input type=\'text\' class=\'widefat\' value=\'[RH_ELEMENTOR id="'.$post_id.'"]\' readonly="">';
            break;
    }
}
add_filter( 'manage_elementor_library_posts_columns', 'rh_set_custom_edit_elementor_library_posts_columns' );
add_action( 'manage_elementor_library_posts_custom_column' , 'rh_add_elementor_library_columns', 10, 2 );