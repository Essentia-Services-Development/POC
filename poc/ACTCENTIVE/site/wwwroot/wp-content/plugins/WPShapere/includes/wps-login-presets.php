<?php
/**
 * @package WPShapere
 * @author   AcmeeDesign
 * @url     http://acmeedesign.com
 * defining css styles for WordPress admin pages.
 */

 defined('ABSPATH') || die;

 class WPS_LOGIN_PRESETS extends WPSHAPERE {

   function __construct()
   {
      $this->aof_options = parent::get_wps_option_data(WPSHAPERE_OPTIONS_SLUG);
      add_action('admin_menu', array($this, 'wps_login_presets_menu'));
      add_action('plugins_loaded',array($this, 'wps_import_login_preset'));
      if($this->aof_options['disable_styles_login'] != 1) {
          add_action('login_header', array($this, 'wps_login_form_wrap_start'), 1);
          add_action('login_footer', array($this, 'wps_login_form_wrap_close'), 99);
      }
   }

   function wps_login_form_wrap_start() {
     echo '<div class="wps-login-container">
     <div class="wps-login-bg"></div>';
   }

   function wps_login_form_wrap_close() {
     ?>
     <div class="clear"></div></div>
     <?php
     //get login page path
     $login_page = basename($_SERVER['REQUEST_URI'], '?'.$_SERVER['QUERY_STRING']);
     if($login_page == 'wp-login.php' && isset($_GET['action']) && $_GET['action'] == 'rp')
      return;
      ?>
     <script type="text/javascript">
       jQuery(document).ready(function(){
         jQuery( "#user_login" ).before( "<div class='wps-icon-login'></div>" );
         jQuery( "#user_email" ).before( "<div class='wps-icon-email'></div>" );
         jQuery( "#user_pass" ).before( "<div class='wps-icon-pwd'></div>" );
         jQuery('#user_login,#user_email').attr('autocomplete', 'off');
       });
     </script>
     <?php
   }

   function wps_login_presets_menu()
   {
      add_submenu_page( WPSHAPERE_MENU_SLUG , esc_html__('Login Theme Presets', 'wps'), esc_html__('Login Theme Presets', 'wps'), 'manage_options', 'wps_import_login_theme', array($this,'wps_login_presets_page') );
   }

   function wps_import_login_preset() {

     if(isset($_POST) && isset($_POST['wps_login_theme_field']) && wp_verify_nonce( $_POST['wps_login_theme_field'], 'wps_login_theme_nonce' )) {

       $wps_login_presets = $this->wos_login_presets();

       $saved_data = array();
       $theme_name = sanitize_text_field($_POST['wps_import_login_theme']);

       $selected_theme = array( 'login_theme_preset' => $theme_name );

       $theme_data = $wps_login_presets[$theme_name];
       $import_data = array_merge($selected_theme, $theme_data['theme_params']); //echo '<pre>'; print_r($import_data); echo '</pre>'; exit();
       $saved_data = parent::get_wps_option_data( WPSHAPERE_OPTIONS_SLUG );
       if($saved_data) {
           $data = array_merge($saved_data, $import_data);
        }
       else
           $data = $import_data;
       parent::updateOption(WPSHAPERE_OPTIONS_SLUG, $data);
       wp_safe_redirect( admin_url( 'admin.php?page=wps_import_login_theme' ) );
       exit();

     }

   }

   function wps_login_presets_page()
   {
      global $aof_options;
     ?>

     <div class="wrap wps-wrap">
       <?php $aof_options->licenseValidate(); ?>
         <h1><?php echo esc_html__('Import Login Theme', 'wps'); ?></h1>
         <?php parent::wps_help_link();
         $wps_login_presets = $this->wos_login_presets();
         $login_theme = null;
         $login_theme = (isset($this->aof_options['login_theme_preset'])) ? $this->aof_options['login_theme_preset'] : 'plain';
         ?>
         <div id="wps_import_login_theme" class="clearfix">
           <form name="wps_import_login_themes" method="post" action="">
             <select name="wps_import_login_theme" class="aof-image-select">
               <?php
               if(is_array($wps_login_presets)) {
                 foreach ($wps_login_presets as $theme_name => $params) {
                   $selected = ($theme_name == $login_theme) ? "selected" : "";
                   if(!empty($params['thumb_url'])) {
                     echo '<option value="'. $theme_name .'" data-img-src="'. $params['thumb_url'] .'" '. $selected .'>';
                     echo $params['theme_name'];
                     echo '</option>';
                   }
                 }
               }
               ?>
             </select>

             <input type="hidden" name="wps_login_theme_select" value="1" />
             <?php wp_nonce_field('wps_login_theme_nonce','wps_login_theme_field'); ?>
             <input type="submit"  class="button button-primary button-hero" value="<?php echo esc_html__('Import Theme', 'wps'); ?>" />

           </form>
         </div>

      <?php
   }

   function wos_login_presets() {

     //defining login presets array
     $wps_login_themes = array();

     //plain theme
     $wps_login_themes['plain'] =
     array(
       'theme_name' => 'Plain',
       'thumb_url' => WPSHAPERE_DIR_URI . 'assets/images/login-presets/login-plain.jpg',
       'theme_params' => array(
         'login_bg_color' => '#b8f2e8',
         'login_external_bg_url' => '',
         'login_bg_img_repeat' => '',
         'login_bg_img_scale' => '',
         'login_form_align_type' => '1',
         'login_form_margintop' => '0',
         'login_form_width' => '500',
         'disable_login_form_shadow' => false,
         'login_divbg_transparent' => false,
         'login_divbg_color' => '#ffffff',
         'login_formbg_color' => '#ffffff',
         'form_border_color' => '#e5e5e5',
         'form_text_color' => '#212121',
         'form_link_color' => '#101010',
         'form_link_hover_color' => '#000000',
         'login_button_style' => '1',
         'login_button_color' => '#582d50',
         'login_button_text_color' => '#ffffff',
         'login_button_hover_color' => '#381c32',
         'login_button_hover_text_color' => '#ffffff',
       )
     );

     //white glare
     $wps_login_themes['rangoli'] =
     array(
       'theme_name' => 'Rangoli',
       'thumb_url' => WPSHAPERE_DIR_URI . 'assets/images/login-presets/login-rangoli.jpg',
       'theme_params' => array(
         'login_bg_color' => '#ffffff',
         'login_external_bg_url' => WPSHAPERE_DIR_URI . 'assets/images/login-bgs/rangoli.jpeg',
         'login_bg_img_repeat' => '0',
         'login_bg_img_scale' => '1',
         'login_form_align_type' => '1',
         'login_form_margintop' => '0',
         'login_form_width' => '500',
         'disable_login_form_shadow' => false,
         'login_divbg_transparent' => false,
         'login_divbg_color' => '#ffffff',
         'login_formbg_color' => '#ffffff',
         'form_border_color' => '#ffffff',
         'form_text_color' => '#a4a9af',
         'form_link_color' => '#7499ca',
         'form_link_hover_color' => '#567194',
         'login_button_style' => '2',
         'login_button_color' => '#0014c6',
         'login_button_text_color' => '#ffffff',
         'login_button_hover_color' => '#0514a2',
         'login_button_hover_text_color' => '#ffffff',
       )
     );

     //white glare
     $wps_login_themes['gradientocean'] =
     array(
       'theme_name' => 'Gradient Ocean',
       'thumb_url' => WPSHAPERE_DIR_URI . 'assets/images/login-presets/login-gradient-ocean.jpg',
       'theme_params' => array(
         'login_bg_color' => '#ffffff',
         'login_external_bg_url' => WPSHAPERE_DIR_URI . 'assets/images/login-bgs/gradient-ocean.jpg',
         'login_bg_img_repeat' => '0',
         'login_bg_img_scale' => '1',
         'login_form_align_type' => '1',
         'login_form_margintop' => '0',
         'login_form_width' => '470',
         'disable_login_form_shadow' => false,
         'login_divbg_transparent' => false,
         'login_divbg_color' => '#504dff',
         'login_formbg_color' => '#504dff',
         'form_border_color' => '#504dff',
         'form_text_color' => '#ffffff',
         'form_link_color' => '#bbcee8',
         'form_link_hover_color' => '#ffffff',
         'login_button_style' => '2',
         'login_button_color' => '#3d3e60',
         'login_button_text_color' => '#ffffff',
         'login_button_hover_color' => '#232337',
         'login_button_hover_text_color' => '#ffffff',
       )
     );

     return $wps_login_themes;

   }

}

new WPS_LOGIN_PRESETS();
