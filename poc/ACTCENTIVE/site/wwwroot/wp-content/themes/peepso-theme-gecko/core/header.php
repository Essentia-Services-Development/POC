<?php
//
// HEADER FUNCTIONS
//

// Get header classes
function gecko_get_header_class( $class = '' ) {
  $classes = array();

  if ( ! empty( $class ) ) {
    if ( ! is_array( $class ) ) {
      $class = preg_split( '#\s+#', $class );
    }
    $classes = array_merge( $classes, $class );
  } else {
    // Ensure that we always coerce class to being an array.
    $class = array();
  }

  $classes = array_map( 'esc_attr', $classes );

  $classes = apply_filters( 'gecko_header_class', $classes, $class );

  return array_unique( $classes );
}

function gecko_header_class( $class = '' ) {
  // Separates class names with a single space, collates class names for body element
  echo 'class="' . join( ' ', gecko_get_header_class( $class ) ) . '"';
}

// Get header menu classes
function gecko_get_header_menu_class( $class = '' ) {
  $classes = array();

  if ( ! empty( $class ) ) {
    if ( ! is_array( $class ) ) {
      $class = preg_split( '#\s+#', $class );
    }
    $classes = array_merge( $classes, $class );
  } else {
    // Ensure that we always coerce class to being an array.
    $class = array();
  }

  $classes = array_map( 'esc_attr', $classes );

  $classes = apply_filters( 'gecko_header_menu_class', $classes, $class );

  return array_unique( $classes );
}

function gecko_header_menu_class( $class = '' ) {
  // Separates class names with a single space, collates class names for body element
  echo 'class="' . join( ' ', gecko_get_header_menu_class( $class ) ) . '"';
}

// Get header options
$hide_sidebars_mobile         = get_theme_mod('layout_hide_sidebars_mobile', 0);
$hide_footer_widgets_mobile   = get_theme_mod('layout_hide_footer_widgets_mobile', 0);
$active_menu_indicator        = get_theme_mod('header_active_menu_indicator', 0);

// Hide sidebars on mobile
if($hide_sidebars_mobile == 1) {
  add_filter( 'gecko_html_class', function( $classes ) {
      return array_merge( $classes, array( 'hide-sidebars-mobile' ) );
  } );
}

// Hide footer widgets on mobile
if($hide_footer_widgets_mobile == 1) {
  add_filter( 'gecko_html_class', function( $classes ) {
      return array_merge( $classes, array( 'hide-footer-widgets-mobile' ) );
  } );
}

// Check for MegaMenu Support
function is_Gecko_MegaMenu() {
  $is_MegaMenu = 0;

  if (class_exists('wp_megamenu')) {
    $wpmm_nav_location_settings = get_wpmm_option('primary-menu');

    if (is_array($wpmm_nav_location_settings) && array_key_exists('is_enabled', $wpmm_nav_location_settings)) {
      $is_MegaMenu = 1;
    }
  }

  if (class_exists( 'QuadMenu' )) {
    if (is_quadmenu('primary-menu')) {
      $is_MegaMenu = 1;
    }
  }

  return $is_MegaMenu;
}
