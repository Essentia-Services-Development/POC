<?php
/*
 * WPSHAPERE
 * @author   AcmeeDesign
 * @url     https://acmeedesign.com
*/

defined('ABSPATH') || die;

/*
*   WPSHAPERE menu slug Constant
*/
define( 'WPSHAPERE_MENU_SLUG' , 'wpshapere-options' );

/*
*   WPSHAPERE users list slug Constant
*/
define( 'WPS_ADMIN_USERS_SLUG' , 'wps_admin_users' );

/*
*   WPSHAPERE admin bar items list Constant
*/
define( 'WPS_ADMINBAR_LIST_SLUG' , 'wps_adminbar_list' );

//AOF Framework Implementation
require_once( WPSHAPERE_PATH . 'includes/acmee-framework/acmee-framework.php' );

$wps_pages_slugs = array(
  'wps_options' => WPSHAPERE_MENU_SLUG,
  'wps_manage_menus' => 'wpshapere_page_admin_menu_management',
  'wps_admin_themes' => 'wpshapere_page_wps_themes',
  'wps_login_themes' => 'wpshapere_page_wps_import_login_theme',
  'wps_import_exort' => 'wpshapere_page_wps_impexp_settings',
  'wps_help_page' => 'wpshapere_page_wpshapere_help',
  'wps_deactivate_license' => 'wpshapere_page_wps_deactivate_license',
  'wps_addons_page' => 'wpshapere_page_wps_addons_adv',
  'wpspb_hide_metaboxes' => 'wpshapere_page_powerbox_hide_meta_boxes',
  'wpspb_impexport_sidebar' => 'wpshapere_page_wpspb_impexport_sidebar',
);

//Instantiate the AOF class
$aof_options = new AcmeeFramework();

add_action( 'admin_enqueue_scripts', 'wps_aofAssets', 99 );
function wps_aofAssets($page) {
  global $wps_pages_slugs;
  if( $page == "toplevel_page_wpshapere-options" || in_array($page, $wps_pages_slugs) ) {
      wp_enqueue_script( 'jquery' );
      wp_enqueue_script( 'jquery-ui-core' );
      wp_enqueue_script( 'jquery-ui-sortable' );
      wp_enqueue_script( 'jquery-ui-slider' );
      wp_enqueue_style('aofOptions-css', AOF_DIR_URI . 'assets/css/aof-framework.min.css');
      wp_enqueue_style('aof-ui-css', AOF_DIR_URI . 'assets/css/jquery-ui.css');
      wp_enqueue_script( 'aofresposivetabs', AOF_DIR_URI . 'assets/js/aof-options-tab.js', array( 'jquery' ), '', true );
      wp_enqueue_script( 'aofimageselect', AOF_DIR_URI . 'assets/image-picker/image-picker.min.js', array( 'jquery' ), '', true );
      wp_enqueue_style( 'wp-color-picker' );
      wp_enqueue_style( 'aof-imageselect', AOF_DIR_URI . 'assets/image-picker/image-picker.css');
      wp_enqueue_script( 'aof-scriptjs', AOF_DIR_URI . 'assets/js/script.js', array( 'jquery', 'wp-color-picker' ), false, true );
    }
}

add_action('admin_menu', 'wps_createOptionsmenu');
function wps_createOptionsmenu() {
  $aof_page = add_menu_page( 'WPShapere', 'WPShapere', 'manage_options', 'wpshapere-options', 'wps_generateFields', 'dashicons-art' );
}

function wps_generateFields() {
  global $aof_options;
  $config = wps_config();
  $aof_options->generateFields($config);
}

add_action('admin_menu', 'wps_SaveSettings');
function wps_SaveSettings() {
  global $aof_options;
  if($_POST) {
    $aof_options->SaveSettings($_POST);
  }
}
