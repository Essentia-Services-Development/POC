<?php
//
// UTILITY FUNCTIONS
//

add_theme_support( 'custom-logo' );
add_theme_support( 'post-thumbnails' );
add_theme_support( 'title-tag' );
add_theme_support( 'responsive-embeds' );
add_theme_support( 'yoast-seo-breadcrumbs' );

// Default copyright text
function default_gecko_copyright() {
    return sprintf(__('All rights reserved © %s', 'peepso-theme-gecko'),get_bloginfo('name'));
}

//  Custom URL on logo
add_filter( 'get_custom_logo', 'add_custom_logo_url' );
function add_custom_logo_url() {
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    $logo_url = esc_url( home_url( '/' ) );

    if (get_theme_mod('logo_url', '0') > 0) {
        $logo_url = get_permalink(get_theme_mod('logo_url'));
    }

    $html = sprintf( '<a href="%1$s" class="custom-logo-link" rel="home" itemprop="url">%2$s</a>',
        $logo_url,
        wp_get_attachment_image( $custom_logo_id, 'full', false, array(
            'class'    => 'custom-logo',
            'alt'      => get_bloginfo( 'name', 'display' ),
        ) )
    );
    return $html;
}

// Custom "active" & "li" classes on Header menu
add_filter('nav_menu_css_class', 'custom_menu_classes', 1, 3);
function custom_menu_classes($classes, $item, $args) {
    if($args->theme_location == 'primary-menu') {
        $classes[] = 'gc-header__menu-item';
    }
    if (in_array('current-menu-item', $classes) ){
        $classes[] = 'gc-header__menu-item--active';
    }
    return $classes;
}

//  Fix WPAdverts single category view
add_action("init", "gecko_theme_wpadverts_init", 20);
function gecko_theme_wpadverts_init() {
    remove_filter('template_include', 'adverts_template_include');
}

//  Detect blog pages
function is_blog() {
    return ( is_archive() || is_author() || is_category() || is_single() || is_tag()) && 'post' == get_post_type();
}

//  Get proper id for any page/post
function get_proper_ID() {

    /*** 5.1.3.0 EXPERIMENT - see #6429 ***/

    // Try to translate URL to ID
    global $wp;
    $page_id = url_to_postid($wp->request);

    if(is_int($page_id) && $page_id>0) {
        return $page_id;
    }

    // Fallback - front page
    if(is_front_page()) {
        return get_option('page_on_front');
    }

    // Fallback  - blog page
    if ( !is_front_page() && is_home() ) {
        return get_option('page_for_posts');
    }

    // Fallback - WooCommerce
    if ( class_exists( 'woocommerce' ) && gecko_is_shop()) {
        return get_option( 'woocommerce_shop_page_id' );
    }

    // Failure
    // echo "get_proper_ID failed";
    return -1;
    /*** 5.1.3.0 EXPERIMENT - see #6429 ***/





    // Code from 5.1.2.0 and earlier
    global $post;

    $page_id = get_the_ID();

    #4851
    global $wp_query;
    if(isset($wp_query->post) && $wp_query->post instanceof  WP_Post) {
        $page_id = $wp_query->post->ID;
    }

    // is BLog
    if ( !is_front_page() && is_home() ) {
        $page_id = get_option('page_for_posts');
    }

    if ( class_exists( 'woocommerce' ) ) {
        if (gecko_is_shop()) {
            $page_id = get_option( 'woocommerce_shop_page_id' );
        }
    }

    return $page_id;
}

//  Remove shortcodes from search results
function remove_shortcode_from_search( $content ) {
    if ( is_search() ) {
        $content = strip_shortcodes( $content );
    }
    return $content;
}
add_filter( 'the_content', 'remove_shortcode_from_search' );

//  Support WooCommerce
function gecko_add_woocommerce_support() {
    add_theme_support( 'woocommerce' );
}
add_action( 'after_setup_theme', 'gecko_add_woocommerce_support' );

add_theme_support( 'wc-product-gallery-zoom' );
add_theme_support( 'wc-product-gallery-lightbox' );
add_theme_support( 'wc-product-gallery-slider' );

////  WP admin bar visibility
//$gecko_settings = GeckoConfigSettings::get_instance();
//if (1==$gecko_settings->get_option( 'opt_show_adminbar', 0 ) ) : show_admin_bar( true ); endif;
//if (2==$gecko_settings->get_option( 'opt_show_adminbar', 0 ) && ! current_user_can( 'manage_options' ) ) : show_admin_bar( false ); endif;
//if (3==$gecko_settings->get_option( 'opt_show_adminbar', 0 ) ) : show_admin_bar( false ); endif;

function gecko_blocks()
{
    global $pagenow;
    if ($pagenow != 'widgets.php') {
        wp_register_script(
            'gecko-blocks',
            gecko_add_cachebust_arg(get_template_directory_uri() . '/assets/js/blocks.js'),
            array('wp-blocks', 'wp-element', 'wp-editor'),
            wp_get_theme()->version
        );

        wp_register_style(
            'gecko-blocks',
            gecko_add_cachebust_arg(get_template_directory_uri() . '/assets/css/blocks.css'),
            array(),
            wp_get_theme()->version
        );

        // #5631 Disable the custom block to conform with the WordPress theme guideline. Please use the built-in "Group" block instead.
        // register_block_type() was introduced in WP 5
        // if (function_exists('register_block_type')) {
        //     register_block_type('gecko-blocks/container', array(
        //         'editor_script' => 'gecko-blocks',
        //         'editor_style' => 'gecko-blocks',
        //         //'style' => 'gecko-blocks'
        //     ));
        // }
    }
}
add_action( 'init', 'gecko_blocks' );

//  Get peepso color template
function get_peepso_color_template() {
    $color = "";

    if (class_exists( 'PeepSo' )) {
        $color = PeepSo::get_option('site_css_template','');
    }

    return $color;
}

//  Open Graph
add_action('wp_head', 'fc_opengraph', 100);
function fc_opengraph() {
    $gecko_settings = GeckoConfigSettings::get_instance();
    if(0 == $gecko_settings->get_option( 'opt_open_graph', 1 )) return;

    if( is_single() || is_page() ) {

        $post_id = get_queried_object_id();

        $url = get_permalink($post_id);
        $title = get_the_title($post_id);
        $site_name = get_bloginfo('name');

        $description = get_bloginfo('description');

        $locale = get_locale();

        if(class_exists('PeepSo') && PeepSo::get_option('opengraph_enable') === 0) {
            echo '<!-- OpenGraph -->';
            echo '<meta property="og:locale" content="' . esc_attr($locale) . '" />';
            echo '<meta property="og:type" content="article" />';
            echo '<meta property="og:title" content="' . esc_attr($site_name) . ' | ' . esc_attr($title) . '" />';
            echo '<meta property="og:description" content="' . esc_attr($description) . '" />';
            echo '<meta property="og:url" content="' . esc_url($url) . '" />';
            echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '" />';
        }

        if( !has_post_thumbnail( $post_id ) ) {
            if ( has_custom_logo() ) {
                $custom_logo_id = get_theme_mod( 'custom_logo' );
                $logo = wp_get_attachment_image_src( $custom_logo_id , 'full' );
                $default_image = esc_url( $logo[0] );
            } else {
                $default_image = get_stylesheet_directory_uri() . "/assets/images/logo.svg";
            }

            echo '<meta property="og:image" content="' . $default_image . '"/>';
        } else {
            $default_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'large' );
            if($default_image) {

                echo '<meta property="og:image" content="' . esc_attr($default_image[0]) . '"/>';
            }
        }

        echo '
    ';
    }

}

if ( ! function_exists( 'gc_post_date' ) ) {
    function gc_post_date() {
        global $post;

        if ( in_array( get_post_type(), array( 'post', 'attachment' ) ) ) {
            $gecko_settings = GeckoConfigSettings::get_instance();

            if (get_post_meta($post->ID, 'gecko-post-update-date', true) == 1) {
                $value = 1;
            } elseif (get_post_meta($post->ID, 'gecko-post-update-date', true) == 2) {
                $value = 0;
            } else {
                $value = $gecko_settings->get_option( 'opt_blog_update', 1 );
            }

            if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) && $value == 1 ) {
                ?>
                <strong datetime="<?php esc_attr(get_the_date( 'c' )); ?>"><?php echo get_the_date(); ?></strong>
                <span class="updated" datetime="<?php esc_attr(get_the_modified_date( 'c' )); ?>">
          (<?php _e('updated', 'peepso-theme-gecko'); ?> <?php echo get_the_modified_date(); ?>)
        </span>
                <?php

            } else {
                ?>
                <strong datetime="<?php esc_attr(get_the_date( 'c' )); ?>"><?php echo get_the_date(); ?></strong>
                <?php
            }
        }
    }
}

function gecko_content_width() {
    // This variable is intended to be overruled from themes.
    // Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $GLOBALS['content_width'] = apply_filters( 'gecko_content_width', 750 );
}
add_action( 'after_setup_theme', 'gecko_content_width', 0 );
