<?php
/**
 * @package WPShapere
 * @author   AcmeeDesign
 * @url     https://acmeedesign.com
 * defining css styles for WordPress admin pages.
 */

//get privilege users
$wps_privilege_users = $this->get_privilege_users();

//get current user ID
$current_user_id = get_current_user_id();

$css_styles = '';
$css_styles .= '<style type="text/css">';

//static styles
$css_styles .= '
.wpshapere_page_wpshapere_help #wpbody-content {
  float:none;
}
#wpadminbar {
    -webkit-box-shadow: 0 2px 2px rgba(0, 0, 0, 0.05), 0 1px 0 rgba(0, 0, 0, 0.05);
    -moz-box-shadow: 0 2px 2px rgba(0, 0, 0, 0.05), 0 1px 0 rgba(0, 0, 0, 0.05);
    box-shadow: 0 2px 2px rgba(0, 0, 0, 0.05), 0 1px 0 rgba(0, 0, 0, 0.05);
}
#wpadminbar .quicklinks {
    border: none !important;
}
#wpadminbar .quicklinks li#wp-admin-bar-my-account.with-avatar>a img {
    width: 36px;
    height: 36px;
    border-radius: 100px;
    -moz-border-radius: 100px;
    -webkit-border-radius: 100px;
    border: none;
}
li#wp-admin-bar-my-account a {
    font-weight: 600;
}
#wpadminbar .quicklinks li a .blavatar, #wpadminbar .quicklinks li a:hover .blavatar { display: none}
#wpadminbar .quicklinks .ab-empty-item, #wpadminbar .quicklinks a, #wpadminbar .shortlink-input {
    height: 32px;
}
#collapse-button {
    margin:10px 21px 20px;
}
ul#wp-admin-bar-root-default, ul.ab-top-menu {}
#wpadminbar .quicklinks ul li li div.ab-empty-item {padding-top: 0!important;padding-bottom: 0!important}

#adminmenu {margin-top:0}
.folded #adminmenu, .folded #adminmenu li.menu-top, .folded #adminmenuback, .folded #adminmenuwrap {
    width: 58px;
}
#adminmenu li.wp-has-submenu.wp-not-current-submenu:hover:after {
    top: 14px;
}
#adminmenu .wp-submenu {
  padding: 0;
}
#wpbody-content .wrap { margin-top: 20px}
#wp-auth-check-wrap #wp-auth-check {
        width: 450px;
}
#dashboard-widgets .postbox .inside img {max-width:100%;height:auto}
.button, .button-primary, .button-secondary { outline: none}
.dashicons, .dashicons-before:before, .wp-menu-image:before {
    display: inline-block;
    width: 20px;
    height: 20px;
    font-size: 20px;
    line-height: 1;
    font-family: dashicons;
    text-decoration: inherit;
    font-weight: 400;
    font-style: normal;
    vertical-align: top;
    text-align: center;
    -webkit-transition: color .1s ease-in 0;
    transition: color .1s ease-in 0;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }
  .wps-manage-menu-slug-mthd {
    background:#fff;
    width: 670px;
    padding:15px;
  }

  .wps-close-btn{
    position: absolute;
    right: 0;
  }
  .wps-close-btn-dropdown {
    position: fixed;
     display: inline-block;
     z-index: 999;
     top: 83px;
     right: 9px;
  }

  .wps-close-btn-dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    background-color: #f9f9f9;
    min-width: 160px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1;
    width:200px;
  }

  .wps-close-btn-dropdown-content a {
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
  }

  .wps-close-btn-dropdown-content a:hover {background-color: #f1f1f1;}
  .wps-close-btn-dropdown:hover .wps-close-btn-dropdown-content {display: block;}
  .wps-close-btn-dropdown:hover .wps-close-btn {background-color: #3e8e41;}

  @media only screen and (min-width:1400px) {
    .powerbox-banner {
      position: fixed;
      right: 0;
      top: 75px;
    }
  }


@media only screen and (min-width:782px) and (max-width: 960px) {
  .auto-fold #adminmenu, .auto-fold #adminmenu li.menu-top, .auto-fold #adminmenuback, .auto-fold #adminmenuwrap {
          width: 58px;
  }
  .auto-fold #adminmenu .opensub .wp-submenu, .auto-fold #adminmenu .wp-has-current-submenu .wp-submenu.sub-open, .auto-fold #adminmenu .wp-has-current-submenu a.menu-top:focus+.wp-submenu, .auto-fold #adminmenu .wp-has-current-submenu.opensub .wp-submenu, .auto-fold #adminmenu .wp-submenu.sub-open, .auto-fold #adminmenu a.menu-top:focus+.wp-submenu {
          left: 58px;
  }
  .auto-fold #wpcontent, .auto-fold #wpfooter {
          margin-left: 76px;
  }
}

@media screen and (max-width: 782px){
  ul#wp-admin-bar-root-default, ul.ab-top-menu {
          margin-top: 0;
  }
  .auto-fold #adminmenu, .auto-fold #adminmenuback, .auto-fold #adminmenuwrap {
          width: 190px;
  }
  .auto-fold #adminmenu li.menu-top {
          width: 100%;
  }
  #adminmenu .wp-not-current-submenu .wp-submenu, .folded #adminmenu .wp-has-current-submenu .wp-submenu {
          width: 190px;
  }
  .auto-fold #wpcontent {
          margin-left: 0;
  }
  #wpadminbar .quicklinks>ul>li>a {
      padding: 0;
  }
  #wpadminbar .quicklinks>ul>li>a, div.ab-empty-item {
      padding: 0 !important;
  }
  #wpadminbar .quicklinks .ab-empty-item, #wpadminbar .quicklinks a, #wpadminbar .shortlink-input {
          height: 46px;
  }
}

body.folded #adminmenu li.menu-top {
  margin:0
}

body.folded .wps-menu-user-actions .wps-menu-logout{
  margin-left:7px;
  margin-top:7px;
}

body.folded .wps-menu-user-actions a {
  display: block !important;
  padding: 10px;
  margin-left: 7px;
}

body.folded .wps-user-avatar img.avatar {
    max-width: 45px;
    max-height: 45px;
}

#collapse-button {
  margin-left:10px;
}

.wps-wrap .notice, .wps-wrap .notice-warning,
.wps-wrap .settings-error {
  display: none !important;
}

.dashicons-update.spin.hidden {
  display:none;
}

@media screen and (max-width: 960px){
  .wps-user-avatar img.avatar {
    max-width:45px;
    max-height:45px;
  }
  .wps-menu-user-actions .wps-menu-logout{
    margin-left:7px;
    margin-top:7px;
  }
}

@media screen and (min-width: 782px) and (max-width: 960px){
  .wps-menu-user-actions a {
    display: block !important;
    padding: 10px;
    margin-left: 7px;
  }
}

@media screen and (max-width: 600px){
  div#login {
      width: 90% !important;
  }
}
@media (min-width: 782px){
  .edit-post-fullscreen-mode-close.components-button:before {
    left: 0;
    right: 9px;
    top: 0;
  }
  body.block-editor-page #editor .edit-post-header .edit-post-fullscreen-mode-close {
    margin-left:9px;
    margin-top:9px;
  }
}
';

if(isset($this->aof_options['enable_menu_user_info']) &&  $this->aof_options['enable_menu_user_info'] == 1) {
  $css_styles .= '
    .wps-user-avatar,.wps-user-displayname {text-align:center}
    .wps-user-avatar {clear:both;padding-top:24px;}
    .wps-user-displayname {line-height:24px;font-size:15px;font-weight:600;color:#fff;margin-bottom:5px;}
    .wps-user-avatar img.avatar{border-radius: 100px;
    -moz-border-radius: 100px;
    -webkit-border-radius: 100px;}
    .wps-menu-user-actions{
      text-align:center;
      margin-bottom: 8px;
      border-bottom: 1px solid #0e1419;
      padding-bottom: 23px;}
    .wps-menu-user-actions a {
      width:23px;
      height:23px;
      display:inline-block;
      border-style:solid;
      border-width: 1px;
      border-color: rgba(255, 255, 255, 0.3);
      -webkit-border-radius: 6px;
      -moz-border-radius: 6px;
      -ms-border-radius: 6px;
      border-radius: 6px;
    }
    .wps-menu-logout{display:inline-block;margin-left:9px;}
    i.wps-power-switch:before,i.wps-user:before{font-family: LineIcons!important;
        display: inline-block;
        color:#fff;
        font-size: 14px;
        text-decoration: inherit;
        font-weight: 400;
        font-style: normal;
        vertical-align: middle;
        text-align: center;
        -webkit-transition: color .1s ease-in 0;
        transition: color .1s ease-in 0;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;}
    i.wps-power-switch:before{
      content: "\e932" !important;
    }
    i.wps-user:before{content:"\e9a4" !important}
    .edit-post-header .edit-post-fullscreen-mode-close.has-icon:before {
      display:none;
    }
    ';
  $css_styles .= '@media screen and (max-width: 600px){
    .auto-fold #adminmenu {
      top:175px;
    }
    .wps-user-avatar {
      padding-top:56px;
    }
    .wps-menu-logout {
      margin-left:7px;
    }
  }
  ';

  //adminmenu user info
  if( !empty($this->aof_options['user_text_color']) )
    $css_styles .= '.wps-user-displayname {color:' . $this->aof_options['user_text_color'] . '}';

  if( !empty($this->aof_options['user_info_btm_border_color']) )
    $css_styles .= '.wps-menu-user-actions {border-color:' . $this->aof_options['user_info_btm_border_color'] . '}';

  if( !empty($this->aof_options['user_info_icon_border_color']) )
    $css_styles .= '.wps-menu-user-actions a {border-color:' . $this->aof_options['user_info_icon_border_color'] . '}';

  if( !empty($this->aof_options['user_info_icon_color']) )
    $css_styles .= 'i.wps-power-switch:before,i.wps-user:before {color:' . $this->aof_options['user_info_icon_color'] . '}';

  if( !empty($this->aof_options['user_info_icon_hover_bg_color']) )
    $css_styles .= '.wps-menu-user-actions a:hover {background-color:' . $this->aof_options['user_info_icon_hover_bg_color'] . ';
      border-color:' . $this->aof_options['user_info_icon_hover_bg_color'] . '}';

  if( !empty($this->aof_options['user_info_icon_hover_color']) )
    $css_styles .= '.wps-menu-user-actions a:hover i.wps-power-switch:before,
    .wps-menu-user-actions a:hover i.wps-user:before {color:' . $this->aof_options['user_info_icon_hover_color'] . '}';
}

if(!isset($this->aof_options['admin_menu_font_size']) || $this->aof_options['admin_menu_font_size'] == 'small') {
  $css_styles .= '#adminmenu .wp-submenu-head, #adminmenu a.menu-top {font-size:0.95em;}
  #adminmenu .wp-submenu a {font-size:0.9em;}';
}
  if(empty($this->aof_options['default_adminbar_height'])) {
    $css_styles .= '#wpadminbar {height:50px;}';
    $css_styles .= '@media screen and (max-width: 782px){
      #wpadminbar .quicklinks .ab-empty-item, #wpadminbar .quicklinks a, #wpadminbar .shortlink-input {
          height: 46px;
      }
    }
    @media only screen and (min-width:782px) {
      html.wp-toolbar {padding-top: 50px;}
      #wpadminbar .quicklinks>ul>li>a, div.ab-empty-item { padding: 9px !important }
      #wpadminbar #wp-admin-bar-wpshapere_site_title a{padding:0 !important;height:50px}
    }
    ';
  }

$css_styles .= 'html, #wpwrap, #wp-content-editor-tools,body { background: ' . $this->aof_options['bg_color'] . '; }';
$css_styles .= 'ul#adminmenu a.wp-has-current-submenu:after, ul#adminmenu>li.current>a.current:after { ';
  if(is_rtl()) {
    $css_styles .= 'border-left-color: ' . $this->aof_options['bg_color'];
  }
  else {
    $css_styles .= 'border-right-color: ' . $this->aof_options['bg_color'];
  }
$css_styles .= '}';

/* Headings */
$css_styles .= 'h1 { color: ' . $this->aof_options['h1_color'] . '}';
$css_styles .= 'h2 { color: ' . $this->aof_options['h2_color'] . '}';
$css_styles .= 'h3 { color: ' . $this->aof_options['h3_color'] . '}';
$css_styles .= 'h4 { color: ' . $this->aof_options['h4_color'] . '}';
$css_styles .= 'h5 { color: ' . $this->aof_options['h5_color'] . '}';

/* Admin Bar */
$css_styles .= '#wpadminbar, #wpadminbar .menupop .ab-sub-wrapper, .ab-sub-secondary, #wpadminbar .quicklinks .menupop ul.ab-sub-secondary,
#wpadminbar .quicklinks .menupop ul.ab-sub-secondary .ab-submenu {
  background: ' . $this->aof_options['admin_bar_color'] . '}';
$css_styles .= '#wpadminbar .ab-empty-item, #wpadminbar a.ab-item, #wpadminbar>#wp-toolbar span.ab-label, #wpadminbar>#wp-toolbar span.noticon, #wpadminbar .ab-icon:before,
#wpadminbar .ab-item:before {
  color: ' . $this->aof_options['admin_bar_menu_color'] .'}';
$css_styles .= '#wpadminbar .quicklinks .menupop ul li a, #wpadminbar .quicklinks .menupop ul li a strong, #wpadminbar .quicklinks .menupop.hover ul li a,
#wpadminbar.nojs .quicklinks .menupop:hover ul li a {
  color: ' . $this->aof_options['admin_bar_menu_color'] . '; font-size:13px !important }';

if($this->aof_options['design_type'] != 3) {

  $css_styles .= '#wpadminbar .ab-top-menu>li.hover>.ab-item,#wpadminbar.nojq .quicklinks .ab-top-menu>li>.ab-item:focus,
  #wpadminbar:not(.mobile) .ab-top-menu>li:hover>.ab-item,#wpadminbar:not(.mobile) .ab-top-menu>li>.ab-item:focus{
    background: ' . $this->aof_options['admin_bar_menu_bg_hover_color'] . '; color: ' . $this->aof_options['admin_bar_menu_hover_color'] . '}';

}

$css_styles .= '#wpcontent #wpadminbar .ab-top-menu>li.hover>a.ab-item,
#wpcontent #wpadminbar>#wp-toolbar li.hover span.ab-label, #wpcontent #wpadminbar>#wp-toolbar li.hover span.ab-icon,
#wpadminbar:not(.mobile)>#wp-toolbar a:focus span.ab-label, #wpadminbar:not(.mobile)>#wp-toolbar li:hover span.ab-label, #wpadminbar>#wp-toolbar li.hover span.ab-label,
#wpcontent #wpadminbar .ab-top-menu>li:hover>a.ab-item,#wpcontent #wpadminbar .ab-top-menu>li:hover>a.ab-label,#wpcontent #wpadminbar .ab-top-menu>li:hover>a.ab-icon {
  color:' . $this->aof_options['admin_bar_menu_hover_color'] .'}';
$css_styles .= '#wpadminbar .quicklinks .ab-sub-wrapper .menupop.hover>a,#wpadminbar .quicklinks .menupop ul li a:focus,
#wpadminbar .quicklinks .menupop ul li a:focus strong,#wpadminbar .quicklinks .menupop ul li a:hover,
#wpadminbar .quicklinks .menupop ul li a:hover strong,#wpadminbar .quicklinks .menupop.hover ul li a:focus,
#wpadminbar .quicklinks .menupop.hover ul li a:hover,#wpadminbar li #adminbarsearch.adminbar-focused:before,
#wpadminbar li .ab-item:focus:before,#wpadminbar li a:focus .ab-icon:before,#wpadminbar li.hover .ab-icon:before,
#wpadminbar li.hover .ab-item:before,#wpadminbar li:hover #adminbarsearch:before,#wpadminbar li:hover .ab-icon:before,
#wpadminbar li:hover .ab-item:before,#wpadminbar.nojs .quicklinks .menupop:hover ul li a:focus,
#wpadminbar.nojs .quicklinks .menupop:hover ul li a:hover, #wpadminbar .quicklinks .ab-sub-wrapper .menupop.hover>a .blavatar,
#wpadminbar .quicklinks li a:focus .blavatar,#wpadminbar .quicklinks li a:hover .blavatar{
  color: ' . $this->aof_options['admin_bar_menu_hover_color'] .'}';
$css_styles .= '#wpadminbar .menupop .ab-sub-wrapper, #wpadminbar .shortlink-input {
  background: ' . $this->aof_options['admin_bar_menu_bg_hover_color'] . '}';

$css_styles .= '#wpadminbar .ab-submenu .ab-item, #wpadminbar .quicklinks .menupop ul.ab-submenu li a,
#wpadminbar .quicklinks .menupop ul.ab-submenu li a.ab-item {
  color: '. $this->aof_options['admin_bar_sbmenu_link_color'] . '}';
$css_styles .= '#wpadminbar .ab-submenu .ab-item:hover, #wpadminbar .quicklinks .menupop ul.ab-submenu li a:hover,
#wpadminbar .quicklinks .menupop ul.ab-submenu li a.ab-item:hover {
  color: ' . $this->aof_options['admin_bar_sbmenu_link_hover_color'] . '}';

/* Site logo and title */
$css_styles .= '.quicklinks li.wpshapere_site_title {';
if( ! empty( $this->aof_options['logo_top_margin'] ) )
  $css_styles .= 'margin-top:-' . $this->aof_options['logo_top_margin'] . 'px !important;';
if( ! empty( $this->aof_options['logo_bottom_margin'] ) )
  $css_styles .= 'margin-top:' . $this->aof_options['logo_bottom_margin'] . 'px !important;';
$css_styles .= '}';
$css_styles .= '#wpadminbar .quicklinks li.wpshapere_site_title a{outline:none; border:none;}';

if(!empty($this->aof_options['adminbar_external_logo_url']) && filter_var($this->aof_options['adminbar_external_logo_url'], FILTER_VALIDATE_URL)) {
  $adminbar_logo = esc_url( $this->aof_options['adminbar_external_logo_url']);
}
else {
  $adminbar_logo = (is_numeric($this->aof_options['admin_logo'])) ? $this->get_wps_image_url($this->aof_options['admin_logo']) : $this->aof_options['admin_logo'];
}

$logo_v_margins = '0px';
if( isset($this->aof_options['logo_bottom_margin']) && ! empty( $this->aof_options['logo_bottom_margin'] ) ) {
  $logo_v_margins = $this->aof_options['logo_bottom_margin'] . 'px';
}
elseif( isset($this->aof_options['logo_top_margin']) && ! empty( $this->aof_options['logo_top_margin'] ) ) {
  $logo_v_margins = '-' . $this->aof_options['logo_top_margin'] . 'px';
}
$logo_l_margins = 'left';
if( isset( $this->aof_options['logo_left_margin'] ) && ! empty( $this->aof_options['logo_left_margin'] ) ) {
  $logo_l_margins = $this->aof_options['logo_left_margin'] . 'px';
}

$css_styles .= '#wpadminbar #wp-admin-bar-wpshapere_site_title a, #wpadminbar .quicklinks li.wpshapere_site_title a, #wpadminbar .quicklinks li.wpshapere_site_title a:hover,
#wpadminbar .quicklinks li.wpshapere_site_title a:focus {';
if( ! empty( $adminbar_logo ) ){
  $css_styles .= 'background:url(' . $adminbar_logo . ') ' . $logo_l_margins . ' ' . $logo_v_margins . ' no-repeat !important; text-indent:-9999px !important; width: auto;';
}
if( $this->aof_options['adminbar_logo_resize'] == 1 && $this->aof_options['adminbar_logo_size_percent'] > 1 ) {
  $css_styles .= 'background-size:' . $this->aof_options['adminbar_logo_size_percent'] . '%!important;';
}
else $css_styles .= 'background-size:contain!important;';
  $css_styles .= '}';

//collapsed logo
if(!empty($this->aof_options['collapsed_adminbar_ext_logo_url']) && filter_var($this->aof_options['collapsed_adminbar_ext_logo_url'], FILTER_VALIDATE_URL)) {
  $clpsd_adminbar_logo = esc_url( $this->aof_options['collapsed_adminbar_ext_logo_url']);
}
else {
  $clpsd_adminbar_logo = (is_numeric($this->aof_options['collapsed_admin_logo'])) ? $this->get_wps_image_url($this->aof_options['collapsed_admin_logo']) : $this->aof_options['collapsed_admin_logo'];
}
$css_styles .= '.quicklinks li.wps-collapsed-logo {display:none; visibility:hidden}';
$css_styles .= '@media screen and (max-width:782px) {';
$css_styles .= '.quicklinks li.wps-collapsed-logo {display:block!important;visibility:visible!important;}';
$css_styles .= '#wpadminbar .quicklinks li.wps-collapsed-logo a, .quicklinks li.wps-collapsed-logo a:hover, .quicklinks li.wps-collapsed-logo a:focus {';
if(!empty($clpsd_adminbar_logo)){
  $css_styles .= 'width:50px;background:url(' . $clpsd_adminbar_logo . ') no-repeat !important; text-indent:-9999px !important;background-size:cover!important;';
}
$css_styles .= '}';
$css_styles .= '}';

/* Buttons */
$css_styles .= '.wp-core-ui .button,.wp-core-ui .button-secondary,.wp-core-ui .button-disabled{
  color: '. $this->aof_options['sec_button_text_color'] . ';background: '. $this->aof_options['sec_button_color'] . '}';
$css_styles .= '.wp-core-ui .button .dashicons-plus:before,.wp-core-ui .button-secondary .dashicons-plus:before,
.wp-core-ui .button-disabled .dashicons-plus:before{
  color: '. $this->aof_options['sec_button_text_color'] .'}';
$css_styles .= '.button.installing:before, .button.updating-message:before,.plugin-card .update-now:before,
.import-php .updating-message:before, .update-message p:before, .updating-message p:before{color:' . $this->aof_options['sec_button_text_color'] . '!important}';
$css_styles .= '.wp-core-ui .button-secondary:focus, .wp-core-ui .button-secondary:hover, .wp-core-ui .button.focus,
.wp-core-ui .button.hover, .wp-core-ui .button:focus, .wp-core-ui .button:hover, #loco-admin.wrap .button:hover, #loco-admin.wrap .button-link:hover {
  color: '. $this->aof_options['sec_button_hover_text_color'] . ';background: '. $this->aof_options['sec_button_hover_color'] . '}';
$css_styles .= '.wp-core-ui .button-secondary:hover .dashicons-plus:before, .wp-core-ui .button.focus .dashicons-plus:before,
  .wp-core-ui .button.hover .dashicons-plus:before, .wp-core-ui .button:focus .dashicons-plus:before, .wp-core-ui .button:hover .dashicons-plus:before {
    color: '. $this->aof_options['sec_button_hover_text_color'] . '}';
$css_styles .= '.wp-core-ui .button-primary, .wp-core-ui .button-primary-disabled, .wp-core-ui .button-primary.disabled,
.wp-core-ui .button-primary:disabled, .wp-core-ui .button-primary[disabled] {
  background: ' . $this->aof_options['pry_button_color'] . '!important; color: '. $this->aof_options['pry_button_text_color'] . '!important;text-shadow: none;}';
$css_styles .= '.wp-core-ui .button-primary.focus, .wp-core-ui .button-primary.hover, .wp-core-ui .button-primary:focus,
.wp-core-ui .button-primary:hover, .wp-core-ui .button-primary.active,.wp-core-ui .button-primary.active:focus,
.wp-core-ui .button-primary.active:hover,.wp-core-ui .button-primary:active {
  background: ' . $this->aof_options['pry_button_hover_color'] . '!important;color: ' . $this->aof_options['pry_button_hover_text_color'] . '!important}';

/* Left Menu */
if( isset( $this->aof_options['enable_admin_menu_shadow'] ) && !empty( $this->aof_options['enable_admin_menu_shadow'] ) ) {
  $admin_menu_shadow_opacity = ( isset( $this->aof_options['admin_menu_shadow_opacity'] ) ) ? '0.' . $this->aof_options['admin_menu_shadow_opacity'] : '0.3';
  $css_styles .= '#wps-sidebar, #adminmenuback {-webkit-box-shadow: 0 3px 9px 0 rgba(14, 14, 14, 0.3);
    box-shadow: 0 3px 9px 0 rgba(14, 14, 14, ' . $admin_menu_shadow_opacity . '); -moz-box-shadow: 0 3px 9px 0 rgba(14, 14, 14, ' . $admin_menu_shadow_opacity . ')}';
}
if(isset($this->aof_options['admin_menu_width']) && !empty($this->aof_options['admin_menu_width'])) {
    $admin_menu_width = $this->aof_options['admin_menu_width'];
    $wp_content_margin = $admin_menu_width + 20;

    $css_styles .= '#adminmenu, #adminmenu .wp-submenu, #adminmenuback, #adminmenuwrap {
            width: ' . $admin_menu_width . 'px}';
    $css_styles .= '#wpcontent, #wpfooter {';
      if(is_rtl()) {
        $css_styles .= 'margin-right: '. $admin_menu_width . 'px';
      } else {
        $css_styles .= 'margin-left: '. $admin_menu_width . 'px';
      }
    $css_styles .= '}';
    $css_styles .= '#adminmenu .wp-submenu {';
      if(is_rtl())
        $css_styles .= 'right:' . $admin_menu_width . 'px';
      else $css_styles .= 'left:' . $admin_menu_width . 'px';
    $css_styles .= '}';
    $css_styles .= '.quicklinks li.wpshapere_site_title {
            width:'. $admin_menu_width . 'px !important}';

    $css_styles .= '@media screen and (min-width: 960px) {
    .auto-fold .edit-post-layout__content .components-editor-notices__snackbar{
      left:'. $admin_menu_width . 'px !important;
    }
    }
    @media screen and (max-width: 782px) {
      .auto-fold .edit-post-layout__content .components-editor-notices__snackbar{
        left:58px !important;
      }
    }
    ';
}
else {
      $css_styles .= '#adminmenu, #adminmenu .wp-submenu, #adminmenuback, #adminmenuwrap {
        width: 200px;
      }
    #wpcontent, #wpfooter {';
      if(is_rtl())
        $css_styles .= 'margin-right:220px';
      else $css_styles .= 'margin-left:220px';
    $css_styles .= '}';

    $css_styles .= '#adminmenu .wp-submenu {';
      if(is_rtl())
        $css_styles .= 'right: 200px';
      else $css_styles .= 'left:200px';
    $css_styles .= '}';

    $css_styles .= '.quicklinks li.wpshapere_site_title {
        width: 200px !important;
    }';
}

$css_styles .= '#adminmenuback, #adminmenuwrap, #adminmenu { background: '. $this->aof_options['nav_wrap_color'] .'}';
$css_styles .= '#adminmenu div.wp-menu-image:before, #adminmenu a, #collapse-menu, #collapse-button div:after {
  color:'. $this->aof_options['nav_text_color'] .'}';
$css_styles .= '#adminmenu li a:focus div.wp-menu-image:before, #adminmenu li.opensub div.wp-menu-image:before,
#adminmenu li:hover div.wp-menu-image:before, #adminmenu .opensub .wp-submenu li.current a,
#adminmenu .wp-submenu li.current, #adminmenu a.wp-has-current-submenu:focus+.wp-submenu li.current a {
  color:'. $this->aof_options['menu_hover_text_color'] .'}';

$css_styles .= '#adminmenu li.menu-top:hover, #adminmenu li.menu-top a:hover, #adminmenu li.opensub>a.menu-top, #adminmenu li>a.menu-top:focus{
  background: '. $this->aof_options['hover_menu_color'] .'; color: '. $this->aof_options['menu_hover_text_color'] .'}';

/* Sub menu */
$admin_submenu_v_padding = '10px';
if(isset($this->aof_options['admin_submenu_v_padding']) && !empty($this->aof_options['admin_submenu_v_padding'])) {
  $admin_submenu_v_padding = $this->aof_options['admin_submenu_v_padding'] . 'px';
}
$css_styles .= '#adminmenu .wp-has-current-submenu ul>li>a, .folded #adminmenu li.menu-top .wp-submenu>li>a, #adminmenu .wp-submenu a {
  padding: '. $admin_submenu_v_padding .' 20px;
}
#adminmenu .wp-not-current-submenu li>a, .folded #adminmenu .wp-has-current-submenu li>a {
        padding-left: 20px;
}';
$css_styles .= '#adminmenu .wp-submenu a, #adminmenu li.menu-top .wp-submenu a {
  color:'. $this->aof_options['sub_nav_text_color'] . '}';

$css_styles .= '#adminmenu .wp-submenu a:focus, #adminmenu .wp-submenu a:hover,
#adminmenu li.menu-top .wp-submenu a:hover {
  background: '. $this->aof_options['sub_nav_hover_color'] .'; color: '. $this->aof_options['sub_nav_hover_text_color'] . '}';

$css_styles .= '#adminmenu .wp-submenu-head, #adminmenu a.menu-top {';

$admin_par_menu_v_padding = '5px';
  if(isset($this->aof_options['admin_par_menu_v_padding']) && !empty($this->aof_options['admin_par_menu_v_padding'])) {
    $admin_par_menu_v_padding = $this->aof_options['admin_par_menu_v_padding'] . 'px';
  }

  if(is_rtl())
    $css_styles .= 'padding: ' . $admin_par_menu_v_padding . ' 10px ' . $admin_par_menu_v_padding . ' 0';
  else $css_styles .= 'padding: ' . $admin_par_menu_v_padding . ' 0 ' . $admin_par_menu_v_padding . ' 10px';
$css_styles .= '}';
$css_styles .= '.folded #wpcontent, .folded #wpfooter {';
  if(is_rtl())
    $css_styles .= 'margin-right: ';
  else $css_styles .= 'margin-left: ';
$css_styles .= '78px;}';

$css_styles .= '.folded #adminmenu .opensub .wp-submenu, .folded #adminmenu .wp-has-current-submenu .wp-submenu.sub-open,
.folded #adminmenu .wp-has-current-submenu a.menu-top:focus+.wp-submenu,
.folded #adminmenu .wp-has-current-submenu.opensub .wp-submenu, .folded #adminmenu .wp-submenu.sub-open,
.folded #adminmenu a.menu-top:focus+.wp-submenu, .no-js.folded #adminmenu .wp-has-submenu:hover .wp-submenu {';
  if(is_rtl())
    $css_styles .= 'right: 58px';
  else $css_styles .= 'left: 58px';
$css_styles .= '}';

$css_styles .= '#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu, #adminmenu li.current a.menu-top,
.folded #adminmenu li.wp-has-current-submenu, .folded #adminmenu li.current.menu-top, #adminmenu .wp-menu-arrow,
#adminmenu .wp-has-current-submenu .wp-submenu .wp-submenu-head, #adminmenu .wp-menu-arrow div, .wps-sidebar-menu .wps-curent-menu-item > a,
.wps-sidebar-menu .wps-curent-menu-item > a:hover {
  background: '. $this->aof_options['active_menu_color'] .';color: ' . $this->aof_options['menu_active_text_color'] .'}';

$css_styles .= '#adminmenu li.wp-has-current-submenu > a.menu-top, #adminmenu li.wp-has-current-submenu div.wp-menu-image:before,
#adminmenu li.wp-has-current-submenu:hover > a.menu-top, #adminmenu .current div.wp-menu-image:before, #adminmenu .wp-has-current-submenu div.wp-menu-image:before,
#adminmenu a.current:hover div.wp-menu-image:before, #adminmenu a.wp-has-current-submenu:hover div.wp-menu-image:before,
#adminmenu li.wp-has-current-submenu a:focus div.wp-menu-image:before, #adminmenu li.wp-has-current-submenu.opensub div.wp-menu-image:before,
#adminmenu li.wp-has-current-submenu:hover div.wp-menu-image:before {
  color: '. $this->aof_options['menu_active_text_color'] . '}';

$css_styles .= '#adminmenu .wp-has-current-submenu .wp-submenu, .no-js li.wp-has-current-submenu:hover .wp-submenu,
#adminmenu a.wp-has-current-submenu:focus+.wp-submenu, #adminmenu .wp-has-current-submenu .wp-submenu.sub-open,
#adminmenu .wp-has-current-submenu.opensub .wp-submenu, #adminmenu .wp-not-current-submenu .wp-submenu,
.folded #adminmenu .wp-has-current-submenu .wp-submenu {
  background:'. $this->aof_options['sub_nav_wrap_color'] . '}';

$css_styles .= '#adminmenu .wp-submenu li.current a:focus, #adminmenu li.menu-top .wp-submenu li.current a:hover,
#adminmenu a.wp-has-current-submenu:focus+.wp-submenu li.current a, #adminmenu .opensub .wp-submenu li.current a, #adminmenu .wp-submenu li.current,
#adminmenu li.menu-top .wp-submenu li.current a, #adminmenu li.menu-top .wp-submenu li.current a:focus, #adminmenu li.menu-top .wp-submenu li.current a:hover,
#adminmenu a.wp-has-current-submenu:focus+.wp-submenu li.current a {
  color:'. $this->aof_options['submenu_active_text_color'] . '}';

$css_styles .= '#adminmenu li.wp-has-submenu.wp-not-current-submenu.opensub:hover:after {';
  if(is_rtl())
    $css_styles .= 'border-left-color: ';
  else $css_styles .= 'border-right-color: ';
$css_styles .= $this->aof_options['sub_nav_wrap_color'] .'}';
$css_styles .= '#adminmenu .awaiting-mod, #adminmenu .update-plugins,
#sidemenu li a span.update-plugins, #adminmenu li a.wp-has-current-submenu .update-plugins {
  background-color:'. $this->aof_options['menu_updates_count_bg'] .'; color: '. $this->aof_options['menu_updates_count_text'] . '}';

$css_styles .= '#adminmenu .wp-menu-image img { padding: 6px 0 0 }';

/* Metabox handles */
$css_styles .='.postbox .handle-order-higher, .postbox .handle-order-lower, .postbox .handlediv{height:35px}
.postbox .handle-order-higher:focus, .postbox .handle-order-lower:focus, .postbox .handlediv:focus{
  box-shadow: none;
outline: none;
}';
$css_styles .= '.metabox-holder h2.hndle{line-height: 1.4 !important;}';
$css_styles .= '.postbox-header, #editor .postbox > .postbox-header:hover{background-color:' . $this->aof_options['metabox_h3_color'] . '}';
$css_styles .= '.meta-box-sortables.ui-sortable .hndle,
.sortUls div.menu_handle, .wp-list-table thead,
.postbox-header .handle-actions, .edit-post-meta-boxes-area #poststuff h2.hndle {
  background:none;background-color:none;
  border: none;
  color: '. $this->aof_options['metabox_text_color'] . '}';
$css_styles .= '.postbox .hndle { border: none !important}';
$css_styles .= 'ol.sortUls a.plus:before, ol.sortUls a.minus:before {
  color:'. $this->aof_options['metabox_handle_color'] .'}';
$css_styles .= '.postbox .accordion-section-title:after, .handlediv, .item-edit,
.sidebar-name-arrow, .widget-action, .sortUls a.admin_menu_edit,
.postbox .handle-order-lower .order-lower-indicator::before {
  color:'. $this->aof_options['metabox_handle_color'] .'}';
$css_styles .= '.postbox .accordion-section-title:hover:after, .handlediv:hover,
.item-edit:hover, .sidebar-name:hover .sidebar-name-arrow, .widget-action:hover,
.sortUls a.admin_menu_edit:hover {
  color:'. $this->aof_options['metabox_handle_hover_color'] .'}';
$css_styles .= '.wp-list-table thead tr th, .wp-list-table thead tr th a, .wp-list-table thead tr th:hover,
.wp-list-table thead tr th a:hover, span.sorting-indicator:before, span.comment-grey-bubble:before,
.ui-sortable .item-type,.manage-column .yoast-column-readability:before {
  color:#444}';
$css_styles .= 'table.wp-list-table thead tr{background-color: #cacdcf;}';


/* Add new buttons */
$css_styles .= '.wrap .page-title-action, .wrap .page-title-action:focus {
  background-color:'. $this->aof_options['addbtn_bg_color'] .';
  color:'. $this->aof_options['addbtn_text_color'] .';border:none}';
$css_styles .= '.wrap .page-title-action:hover {
  background-color:'. $this->aof_options['addbtn_hover_bg_color'] .';
  color:'. $this->aof_options['addbtn_hover_text_color'] . '}';

/* Message box */
$css_styles .= 'div.updated, #update-nag, .update-nag {
  border-left: 4px solid '. $this->aof_options['msgbox_border_color'] .';
  background-color:'. $this->aof_options['msg_box_color'] .';
  color:'. $this->aof_options['msgbox_text_color'] .'}';
$css_styles .= 'div.updated #bulk-titles div a:before, .notice-dismiss:before,
.tagchecklist span a:before, .welcome-panel .welcome-panel-close:before {
  background:transparent;
  color:'. $this->aof_options['msgbox_text_color'] .'}';
$css_styles .= 'div.updated a, #update-nag a, .update-nag a,.notice-yoast a {
  color:'. $this->aof_options['msgbox_link_color'] . '}';
$css_styles .= 'div.updated a:hover, #update-nag a:hover, .update-nag a:hover,.notice-yoast a:hover {
  color:'. $this->aof_options['msgbox_link_hover_color'] . '}';

$css_styles .= '.notice-error{background:'. $this->aof_options['notice_box_color'] . ';color:'. $this->aof_options['notice_text_color'] . '}';
$css_styles .= '.notice-error a{color:'. $this->aof_options['notice_link_color'] . '}';
$css_styles .= '.notice-error a:hover{color:'. $this->aof_options['notice_link_hover_color'] . '}';

if($this->aof_options['hide_update_note_plugins'] == 1) {
  $css_styles .= '.plugin-update-tr { display:none}';
}

if($this->aof_options['design_type'] == 1 || $this->aof_options['design_type'] == 3) {
  $css_styles .= '.wp-core-ui .button-primary, #wpadminbar, .postbox,.wp-core-ui .button-primary.focus,
  .wp-core-ui .button-primary.hover, .wp-core-ui .button-primary:focus, .wp-core-ui .button-primary:hover,
  .wp-core-ui .button, .wp-core-ui .button-secondary, .wp-core-ui .button-secondary:focus,
  .wp-core-ui .button-secondary:hover, .wp-core-ui .button.focus, .wp-core-ui .button.hover,
  .wp-core-ui .button:focus, .wp-core-ui .button:hover, #wpadminbar .menupop .ab-sub-wrapper,
  #wpadminbar .shortlink-input, .theme-browser .theme {
  	-webkit-box-shadow: none !important;
  	-moz-box-shadow: none !important;
  	box-shadow: none !important;
  	border: none !important;
    text-shadow: none !important;
  }
  input[type=checkbox], input[type=radio], #update-nag, .update-nag, .wp-list-table, .widefat, input[type=email],
  input[type=number], input[type=password], input[type=search], input[type=tel], input[type=text],
  input[type=url], select, textarea, #adminmenu .wp-submenu, .folded #adminmenu .wp-has-current-submenu .wp-submenu,
  .folded #adminmenu a.wp-has-current-submenu:focus+.wp-submenu, .mce-toolbar .mce-btn-group .mce-btn.mce-listbox,
  .wp-color-result, .widget-top, .widgets-holder-wrap {
  	-webkit-box-shadow: none !important;
  	-moz-box-shadow: none !important;
  	box-shadow: none !important;
  }
  body #dashboard-widgets .postbox form .submit { padding: 10px 0 !important; }';
}

/* Styles for New excite design */

if($this->aof_options['design_type'] == 3) {

  $css_styles .= 'ul#adminmenu a.wp-has-current-submenu:after, ul#adminmenu>li.current>a.current:after{
  border-right-color:transparent;
}
';

  $css_styles .= '#wpadminbar * .ab-sub-wrapper {
  	transition: all 280ms cubic-bezier(.4,0,.2,1) !important;
  }
  #wp-toolbar > ul > li > .ab-sub-wrapper {
      -webkit-transform: scale(.25,0);
      transform: scale(.25,0);
      -webkit-transition: all 280ms cubic-bezier(.4,0,.2,1);
      transition: all 280ms cubic-bezier(.4,0,.2,1);
      -webkit-transform-origin: 50% 0 !important;
      transform-origin: 50% 0 !important;
      display: block !important;
      opacity: 0 !important;
  }

  #wp-toolbar > ul > li.hover > .ab-sub-wrapper {
      -webkit-transform: scale(1,1);
      transform: scale(1,1);
      opacity: 1 !important;
  }

  @media screen and (min-width: 782px){
    #wp-toolbar > ul > li > .ab-sub-wrapper:before {
        position: absolute;
        top: -8px;
        left: 20%;
        content: "";
        display: block;
        border: 6px solid transparent;
        border-bottom-color: transparent;
        border-bottom-color: ' . $this->aof_options['admin_bar_menu_bg_hover_color'] . ';
        transition: all 0.2s ease-in-out;
        -moz-transition: all 0.2s ease-in-out;
        -webkit-transition: all 0.2s ease-in-out;
    }

    #wp-toolbar > ul > li.hover > .ab-sub-wrapper:before {
    	top: -12px;
    }
  }';

  $css_styles .= '#wp-toolbar > ul > li#wp-admin-bar-my-account > .ab-sub-wrapper:before{left:60%}';

  $css_styles .= '#wpadminbar .ab-top-menu>li.hover>.ab-item,#wpadminbar.nojq .quicklinks .ab-top-menu>li>.ab-item:focus,
  #wpadminbar:not(.mobile) .ab-top-menu>li:hover>.ab-item,#wpadminbar:not(.mobile) .ab-top-menu>li>.ab-item:focus{
    background: ' . $this->aof_options['admin_bar_color'] . '; color: ' . $this->aof_options['admin_bar_menu_color'] . '}';

}

if($this->aof_options['design_type'] == 2) {
  $css_styles .= '.wp-core-ui .button,.wp-core-ui .button-secondary {';
  if( isset ( $this->aof_options['sec_button_border_color'] ) ) {
    $css_styles .= 'border-color: ' . $this->aof_options['sec_button_border_color'] . ';';
  }
  if( isset ( $this->aof_options['sec_button_shadow_color'] ) ) {
    $css_styles .= '-webkit-box-shadow:inset 0 1px 0 ' . $this->aof_options['sec_button_shadow_color'] . ',0 1px 0 rgba(0,0,0,.08);';
    $css_styles .= 'box-shadow:inset 0 1px 0 ' . $this->aof_options['sec_button_shadow_color'] . ',0 1px 0 rgba(0,0,0,.08);';
  }
  $css_styles .= '}';

  $css_styles .= '.wp-core-ui .button-secondary:focus, .wp-core-ui .button-secondary:hover, .wp-core-ui .button.focus,
  .wp-core-ui .button.hover, .wp-core-ui .button:focus, .wp-core-ui .button:hover {';
    if( isset ( $this->aof_options['sec_button_hover_border_color'] ) ) {
      $css_styles .= 'border-color: ' . $this->aof_options['sec_button_hover_border_color'] . ';';
    }
    if( isset ( $this->aof_options['sec_button_hover_shadow_color'] ) ) {
      $css_styles .= '-webkit-box-shadow:inset 0 1px 0 ' . $this->aof_options['sec_button_hover_shadow_color'] . ',0 1px 0 rgba(0,0,0,.08);';
      $css_styles .= 'box-shadow:inset 0 1px 0 ' . $this->aof_options['sec_button_hover_shadow_color'] . ',0 1px 0 rgba(0,0,0,.08);';
    }
  $css_styles .= '}';

  $css_styles .= '.wp-core-ui .button-primary, .wp-core-ui .button-primary-disabled,
  .wp-core-ui .button-primary.disabled, .wp-core-ui .button-primary:disabled, .wp-core-ui .button-primary[disabled] {';
    if( isset ( $this->aof_options['pry_button_border_color'] ) ) {
      $css_styles .= 'border-color: ' . $this->aof_options['pry_button_border_color'] . '!important;';
    }
    if( isset ( $this->aof_options['pry_button_shadow_color'] ) ) {
      $css_styles .= '-webkit-box-shadow:inset 0 1px 0 ' . $this->aof_options['pry_button_shadow_color'] . ',0 1px 0 rgba(0,0,0,.15) !important;';
      $css_styles .= 'box-shadow:inset 0 1px 0 ' . $this->aof_options['pry_button_shadow_color'] . ',0 1px 0 rgba(0,0,0,.15) !important;';
    }
  $css_styles .= '}';

  $css_styles .= '.wp-core-ui .button-primary.focus, .wp-core-ui .button-primary.hover,
  .wp-core-ui .button-primary:focus, .wp-core-ui .button-primary:hover, .wp-core-ui .button-primary.active,
  .wp-core-ui .button-primary.active:focus,.wp-core-ui .button-primary.active:hover,.wp-core-ui .button-primary:active {';
    if( isset ( $this->aof_options['pry_button_hover_border_color'] ) ) {
      $css_styles .= 'border-color: ' . $this->aof_options['pry_button_hover_border_color'] . '!important;';
    }
    if( isset ( $this->aof_options['pry_button_hover_shadow_color'] ) ) {
      $css_styles .= '-webkit-box-shadow:inset 0 1px 0 ' . $this->aof_options['pry_button_hover_shadow_color'] . ',0 1px 0 rgba(0,0,0,.15) !important;';
      $css_styles .= 'box-shadow:inset 0 1px 0 ' . $this->aof_options['pry_button_hover_shadow_color'] . ',0 1px 0 rgba(0,0,0,.15) !important;';
    }
  $css_styles .= '}';
} //if design type is 2

//gutenberg styles
if(isset($this->aof_options['admin_menu_width']) && !empty($this->aof_options['admin_menu_width'])) {
  $wps_menu_width = $this->aof_options['admin_menu_width'];
}
else {
  $wps_menu_width = '200';
}

$css_styles .= '@media screen and (min-width: 960px){
    body.block-editor-page #editor .edit-post-header,body.block-editor-page #editor .components-notice-list,
    body.auto-fold.block-editor-page #editor .block-editor-editor-skeleton,
    .auto-fold .edit-post-layout .components-editor-notices__snackbar,
    body.auto-fold #wpcontent .interface-interface-skeleton {
      left:'. $wps_menu_width . 'px
    }
  }';


  $css_styles .= '@media screen and (min-width: 782px){
    body.auto-fold .edit-post-layout__content {';
    if(is_rtl()) {
      $css_styles .= 'margin-right: '. $wp_content_margin . 'px';
    } else {
      $css_styles .= 'margin-left: '. $wp_content_margin . 'px';
    }
  $css_styles .= '}}';

  if(empty($this->aof_options['default_adminbar_height'])) {
    $css_styles .= '@media screen and (min-width: 782px){
      body.block-editor-page #editor .edit-post-header, .woocommerce-layout__header, body.auto-fold.block-editor-page #editor .block-editor-editor-skeleton,
      .edit-post-layout .editor-post-publish-panel, body.auto-fold.block-editor-page #editor .interface-interface-skeleton {
          top: 50px;
      }
      .ld-header-has-tabs .edit-post-layout .edit-post-header {
        top: 173px!important;
      }
      body.block-editor-page #editor .edit-post-sidebar {
        top:105px;
      }
      body.block-editor-page #sfwd-header {
        top:52px;
      }
      .ld-header-has-tabs .edit-post-sidebar, .ld-header-has-tabs.is-fullscreen-mode .edit-post-sidebar {
        top:235px!important;
      }
      body.auto-fold.is-fullscreen-mode #editor .interface-interface-skeleton,
      body.auto-fold.is-fullscreen-mode #editor .block-editor-editor-skeleton {
          top: 0;
      }
    }';
  }

  $css_styles .= 'body.block-editor-page #editor .edit-post-header a[href="edit.php?post_type=post"] svg,
  body.block-editor-page #editor .edit-post-header .edit-post-fullscreen-mode-close svg {
    display:none;
  }';
  if(!empty($clpsd_adminbar_logo)){
    $css_styles .= 'body.block-editor-page #editor .edit-post-header .edit-post-fullscreen-mode-close {
      background:url(' . $clpsd_adminbar_logo . ') no-repeat !important;
    }';
  }
  //gutenberg styles

  //Wordfence options control fix
  $css_styles .= ".wf-options-controls {
    position:relative;
    left:auto;
  }
  .update-nag .wf-btn {color:#826f6f}
  ";

//Impreza theme options header fixed positioning fix
$css_styles .= '.usof-header {
top:50px;
}
.usof-nav {
top:80px;
}';

//Learndash header fix
$css_styles .= '.block-editor-page #sfwd-header{
  top:50px;
}
.ld-header-has-tabs .edit-post-layout {
    padding-top: 136px;
}
';

//iThemes security popp fix
if(isset($this->aof_options['admin_menu_width']) && !empty($this->aof_options['admin_menu_width'])) {
  $popup_margin = $this->aof_options['admin_menu_width'];
}
else {
  $popup_margin = '200';
}
$popup_margin = (int)$popup_margin + 20;

if( is_rtl()) {
  $css_styles .= '.grid .itsec-module-settings-container{';
    $css_styles .= 'margin-right:' . $popup_margin . 'px;';
  $css_styles .= '}';
}
else {
  $css_styles .= '.grid .itsec-module-settings-container{';
    $css_styles .= 'margin-left:' . $popup_margin . 'px;';
  $css_styles .= '}';
}

//gravity forms fix
$gforms_edit_form_width = $wps_menu_width + 410;
$css_styles .= '@media (min-width: 480px) {
body.toplevel_page_gf_edit_forms .wrap.gforms_edit_form {
  top:50px;
}
 body.toplevel_page_gf_edit_forms .gforms_edit_form .gform-form-toolbar {
   width: calc(100% - '. $wps_menu_width .'px);
 }
 body.toplevel_page_gf_edit_forms .wrap.gforms_edit_form .form_editor_fields_container {
  max-width: calc(100% - '. $gforms_edit_form_width .'px);
 }
}
@media (min-width: 783px) {
body.toplevel_page_gf_edit_forms .wrap.gforms_edit_form .editor-sidebar {
  top: 114px;
}
}';

$css_styles .= '@media (min-width: 783px) and (max-width: 960px) {
  #e-admin-top-bar-root {
    width: calc(100% - 50px);
  }
}';

$css_styles .= '@media (min-width: 961px) {
  #e-admin-top-bar-root {
    width: calc(100% - '. $wps_menu_width .'px);
  }
}';

if( isset( $this->aof_options['hide_adminbar_backend'] ) && 1 == $this->aof_options['hide_adminbar_backend'] ) {
  $css_styles .= '#wpadminbar{margin-top:-100px}
  body.block-editor-page #editor .edit-post-header, .woocommerce-layout__header, body.auto-fold.block-editor-page #editor .block-editor-editor-skeleton, .edit-post-layout .editor-post-publish-panel, body.auto-fold.block-editor-page #editor .interface-interface-skeleton,
  body.toplevel_page_gf_edit_forms .wrap.gforms_edit_form {
    top:0
  }
  @media only screen and (min-width: 782px){
    html.wp-toolbar {padding-top: 0;}
  }';
}

if($this->aof_options['design_type'] == 4) {
  //admin menu width
  $admin_menu_width = $this->aof_options['admin_menu_width'];
  $admin_sub_menu_width = (int)$admin_menu_width - 20;

  $css_styles .= 'a {color: #2f8efd;}
  input[type=color], input[type=date], input[type=datetime-local], input[type=datetime], input[type=email], input[type=month], input[type=number], input[type=password], input[type=search], input[type=tel], input[type=text], input[type=time], input[type=url], input[type=week], select, textarea, .wp-core-ui select{border:1px solid #e5e6e6}
  
  .wp-list-table{margin-top:30px}
  .postbox-header,#wp-content-editor-container div.mce-toolbar-grp,.wp-editor-expand #wp-content-editor-tools,
  .widefat thead td, .widefat thead th{border-bottom: 1px solid #edeef1;}
  .widefat tfoot td, .widefat tfoot th {border-top: 1px solid #edeef1;}
  .alternate, .striped>tbody>:nth-child(odd), ul.striped>:nth-child(odd) {
    background-color: #fff;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
  }
  .widefat ol, .widefat p, .widefat ul {color: #1b1b35;}
  .postbox,.wp-editor-container,#wp-content-editor-container,#wp-content-editor-tools .wp-switch-editor,
  
  #menu-management .menu-edit, #menu-settings-column .accordion-container, .comment-ays, .feature-filter, .imgedit-group, .manage-menus, .menu-item-handle, .popular-tags, .stuffbox, .widget-inside, .widget-top, .widgets-holder-wrap, .wp-editor-container, p.popular-tags, table.widefat{border: 1px solid #edeef1;}
  
  #wp-content-editor-tools .wp-switch-editor{border-bottom-color: #f6f7f7;}

  table.wp-list-table thead tr{background:#fff}
  .wp-list-table thead tr th, .wp-list-table thead tr th a, .wp-list-table thead tr th:hover,
  .wp-list-table thead tr th a:hover, span.sorting-indicator:before, span.comment-grey-bubble:before,
  .ui-sortable .item-type,.manage-column .yoast-column-readability:before {
    color:#242c44}
  .wrap .add-new-h2, .wrap .add-new-h2:active, .wrap .page-title-action, .wrap .page-title-action:active {
    position:relative;
    padding: 6px 14px 6px 24px;
    -webkit-border-radius: 4px;
      -moz-border-radius: 4px;
      -ms-border-radius: 4px;
      border-radius: 4px;
  }
  .wrap .page-title-action:before, .wrap .page-title-action:active:before {
    position: absolute; left: 8px; top: 8px; content: "\f132"; font-family: dashicons; display: inline-block; line-height: 1; font-size: 14px; font-weight: 400;     font-style: normal; text-rendering: auto; -webkit-font-smoothing: antialiased;
  }
  .notice-dismiss {top:4px}
  #bulk-titles .ntdelbutton:before, .notice-dismiss:before, 
  .tagchecklist .ntdelbutton .remove-tag-icon:before, .welcome-panel .welcome-panel-close:before,.e-notice__dismiss:before {
    content:"";
    background-image: url("' . WPSHAPERE_DIR_URI . 'assets/images/close.svg"); background-repeat: no-repeat; background-size: contain; width: 12px; height: 12px;
  }
  .welcome-panel .welcome-panel-close { color:#4491ff;padding: 5px 15px 5px 24px; background-color: #d6d3fe; border-radius: 3px; }
  .welcome-panel .welcome-panel-close:before {top:7px;left:5px;color:#4491ff}
  .welcome-panel .welcome-panel-close:hover{color:' . $this->aof_options['msgbox_link_hover_color'] . '}
  span.activate a {
    padding: 2px 6px; display: inline-block; background-color: #ebe6f8; border-radius: 2px; 
  }
  .notice-warning.notice-alt, .updated-message.notice-success, div.updated {
    background-color: #f9fffd; border: 2px solid #e5e5e5; -webkit-border-radius: 3px; -moz-border-radius: 3px; -ms-border-radius: 3px; border-radius: 3px
  }
  a.notice-dismiss:hover{color:#d63638}
  .wp-core-ui .button, .wp-core-ui .button-secondary {
    padding: 0 15px;
  }
  .wp-core-ui .button, .wp-core-ui .button-secondary, .wp-core-ui .button:hover, .wp-core-ui .button-secondary:hover {
    border-color:transparent;
  }
  .plugins tr.inactive {
    opacity: 0.6;
    transition: all 0.3s ease-in-out 0s;
    -webkit-transition: all 0.3s ease-in-out 0s;
    -ms-transition: all 0.3s ease-in-out 0s;
    -moz-transition: all 0.3s ease-in-out 0s;
  }
  .plugins tr.inactive:hover {
    opacity: 1;
  }
  .plugin-update-tr.active td, .plugins .active th.check-column,.e-notice--warning {
    border-left: none;
  }
  .plugins .active td, .plugins .active th {
    background-color: #ebf7f3;
  }
  #adminmenu .awaiting-mod, #adminmenu .menu-counter, #adminmenu .update-plugins {
    margin-left:9px;
    min-width: 16px;
    height: 16px;
    font-size:10px;
  }
  .e-notice {box-shadow:none}
  .e-notice:before {
    width:0;
  }
  .theme-browser .theme .more-details{background: rgb(117,121,120,0.7);color:#4e3c3c;text-shadow:none}
  .theme-browser .theme.add-new-theme a:focus:after, .theme-browser .theme.add-new-theme a:hover:after {
    background: #bfe2ff;
  }
  .theme-browser .theme.add-new-theme a:focus span:after, .theme-browser .theme.add-new-theme a:hover span:after {
    color: #286cff;
  }
  .theme-browser .theme.add-new-theme a:focus .theme-name, .theme-browser .theme.add-new-theme a:hover .theme-name {
    color: #286cff;
  }
  input[type=checkbox]:focus, input[type=color]:focus, input[type=date]:focus, input[type=datetime-local]:focus, input[type=datetime]:focus, input[type=email]:focus, input[type=month]:focus, input[type=number]:focus, input[type=password]:focus, input[type=radio]:focus, input[type=search]:focus, input[type=tel]:focus, input[type=text]:focus, input[type=time]:focus, input[type=url]:focus, input[type=week]:focus, select:focus, textarea:focus {
    border-color: #b7a6e1; box-shadow: none;
  }
  #screen-meta-links .show-settings,#screen-meta,#screen-meta-links .show-settings:focus {border-color:#dee4e6;box-shadow:none}
  ';

  $css_styles .= 'div.updated, #update-nag, .update-nag,.notice-yoast,.notice,.e-notice,.wrap .notice {
    border:none;border-radius: 3px;-moz-border-radius: 3px;-webkit-border-radius: 3px;
    background: ' . $this->aof_options['msg_box_color'] . '; color: ' . $this->aof_options['msgbox_text_color'] . '}';

  $css_styles .= 'div.updated a, #update-nag, .update-nag a,.notice-yoast a {
    color: ' . $this->aof_options['msgbox_link_color'] . '}';

  $css_styles .= 'div.updated a:hover, #update-nag:hover, .update-nag a:hover,.notice-yoast a:hover {
    color: ' . $this->aof_options['msgbox_link_hover_color'] . '}';

  $css_styles .= '#adminmenu .wp-submenu{left:' . $admin_sub_menu_width . 'px}';

  $css_styles .= '#adminmenu li.menu-top, #adminmenu li#collapse-menu{margin:0 13px;border:none;}
  #collapse-button .collapse-button-icon:after{content: "\f228"}
  #adminmenu li#collapse-menu{background: rgb(255,255,255,0.07)}
  body.folded #adminmenu #collapse-button{margin-left:0}
  ';
  $css_styles .= '.notice-error{border:none;}
  #adminmenu li.menu-top, div.updated, #update-nag, .update-nag,
  #adminmenu li.menu-top a, .notice-error,
  .js #adminmenu .opensub .wp-submenu{border-radius: 3px;-moz-border-radius: 3px;-webkit-border-radius: 3px;}
  .js #adminmenu .opensub .wp-submenu{border-top:5px solid transparent;border-bottom:5px solid transparent;border-right: 5px solid transparent;}
  .js #adminmenu .wp-has-current-submenu.opensub > .wp-submenu {
    border: none;
  }
  #adminmenu a:focus, #adminmenu a:hover, .folded #adminmenu .wp-submenu-head:hover{box-shadow:none}
  #adminmenu li.wp-has-submenu.wp-not-current-submenu.opensub:hover:after,
  ul#adminmenu a.wp-has-current-submenu:after, ul#adminmenu>li.current>a.current:after,
  .woocommerce-page .wp-has-current-submenu:after{border-right-color:transparent;border:none;content:""}
  .wp-menu-open .wp-submenu {width:auto !important}
  ';


}


/* WPSPowerbox Sidebar menu */
if( is_plugin_active( 'wpspowerbox/wpspowerbox.php' ) ) {

  $css_styles .= '';

  $wpspb_sidebar_width = ( isset($this->aof_options['wpspb_sidebar_menu_width']) && !empty( $this->aof_options['wpspb_sidebar_menu_width'] ) ) ?
    $this->aof_options['wpspb_sidebar_menu_width'] : '250';

  $css_styles .= '.wps-sidebar-wrapper{';

    $css_styles .= 'width:' . $wpspb_sidebar_width . 'px;';

    if( isset($this->aof_options['wpspb_sidebar_wrap_color']) && !empty($this->aof_options['wpspb_sidebar_wrap_color']) ) {
      $css_styles .= 'background: ' . $this->aof_options['wpspb_sidebar_wrap_color'] . ';';
    }
    else {
      $css_styles .= 'background: #1b2831;';
    }

  $css_styles .= '}';

  $css_styles .= '.wps-sidebar-header,.wps-mobile-nav {';
    if( !empty( $this->aof_options['wpspb_sidebar_header_bg'] ) ) {
      $css_styles .= 'background:'. $this->aof_options['wpspb_sidebar_header_bg'];
    }
    else {
      $css_styles .= 'background:#0f161b';
    }
  $css_styles .= '}';

  $css_styles .= '#pin-sidebar, #pin-sidebar i {';
    if( !empty( $this->aof_options['wpspb_sidebar_hamb_color'] ) ) {
      $css_styles .= 'color:'. $this->aof_options['wpspb_sidebar_hamb_color'];
    }
    else {
      $css_styles .= 'color:#ffffff';
    }
  $css_styles .= '}';

  $css_styles .= '.mob-hamburger a, .mob-hamburger a i {';
    if(isset($this->aof_options['wpspb_mob_hamburger_color']) && !empty($this->aof_options['wpspb_mob_hamburger_color'])) {
      $css_styles .= 'color:' . $this->aof_options['wpspb_mob_hamburger_color'] . ';';
    }
    else
      $css_styles .= 'color:#ffffff;';
  $css_styles .= '}';

  $css_styles .= '.wps-sidebar-wrapper .wps-separator {';
    if(isset($this->aof_options['wpspb_sidebar_sep_color']) && !empty($this->aof_options['wpspb_sidebar_sep_color'])) {
      $css_styles .= 'color:' . $this->aof_options['wpspb_sidebar_sep_color'] . ';';
    }
    else
      $css_styles .= 'color:#32343a;';
  $css_styles .= '}';

  $css_styles .= 'body.has-wpspb-sidebar #wpcontent, body.has-wpspb-sidebar #wpfooter {';
    if(is_rtl()) {
      $css_styles .= 'margin-right: '. $wpspb_sidebar_width . 'px';
    } else {
      $css_styles .= 'margin-left: '. $wpspb_sidebar_width . 'px';
    }
  $css_styles .= '}';

  $css_styles .= '
  @media screen and (min-width: 961px) {
    body.has-wpspb-sidebar.auto-fold #wpcontent .interface-interface-skeleton {
        left: '. $wpspb_sidebar_width . 'px;
        top: 0;
    }
    body.has-wpspb-sidebar.auto-fold #wpcontent.pinned .interface-interface-skeleton {
        left: 65px;
    }
    body.has-wpspb-sidebar #e-admin-top-bar-root {
      width: calc(100% - '. $wpspb_sidebar_width .'px);
    }
  }';

  $css_styles .= '@media screen and (min-width: 782px) {
    body.js.is-fullscreen-mode .wps-sidebar-wrapper {
        display: none;
    }
  }';

  $css_styles .= '@media screen and (max-width: 960px) {
    body.has-wpspb-sidebar .wps-sidebar-wrapper {
        left:-' . $wpspb_sidebar_width . 'px;
    }
    body.has-wpspb-sidebar #wpcontent, body.has-wpspb-sidebar #wpfooter {
      margin-left:0
    }
  }';

  //menu text color
  $css_styles .= '.wps-sidebar-menu ul li a, .wps-sidebar-menu ul li a i {';
    if(isset($this->aof_options['wpspb_sidebar_menu_color']) && !empty($this->aof_options['wpspb_sidebar_menu_color'])) {
      $css_styles .= 'color:' . $this->aof_options['wpspb_sidebar_menu_color'] . ';';
    }
    else
      $css_styles .= 'color:#6c727f;';
  $css_styles .= '}';

  //menu hover text color
  $css_styles .= '.wps-sidebar-menu ul li a:hover, .wps-sidebar-menu ul li a:hover i {';
    if(isset($this->aof_options['wpspb_sidebar_hover_text_color']) && !empty($this->aof_options['wpspb_sidebar_hover_text_color'])) {
      $css_styles .= 'color:' . $this->aof_options['wpspb_sidebar_hover_text_color'] . ';';
    }
    else
      $css_styles .= 'color:#ffffff;';
  $css_styles .= '}';

  //current menu bg and text color
  $css_styles .= '.wps-sidebar-wrapper .wps-sidebar-menu ul li.wps-curent-menu-item > a {';
    if(isset($this->aof_options['wpspb_sidebar_active_menu_color']) && !empty($this->aof_options['wpspb_sidebar_active_menu_color'])) {
      $css_styles .= 'background:' . $this->aof_options['wpspb_sidebar_active_menu_color'] . ';';
    }
    else
      $css_styles .= 'background:#507ee4;';
  $css_styles .= '}';

  $css_styles .= '.wps-sidebar-wrapper .wps-sidebar-menu ul li.wps-curent-menu-item > a i {';
    if(isset($this->aof_options['wpspb_sidebar_active_text_color']) && !empty($this->aof_options['wpspb_sidebar_active_text_color'])) {
      $css_styles .= 'color:' . $this->aof_options['wpspb_sidebar_active_text_color'] . ';';
    }
    else
      $css_styles .= 'color:#ffffff;';

    $css_styles .= '}';

  //sub menu wrap
  $css_styles .= '.wps-sidebar-dropdown .wps-sidebar-submenu {';
    if(isset($this->aof_options['wpspb_sidebar_subnav_wrap_color']) && !empty($this->aof_options['wpspb_sidebar_subnav_wrap_color'])) {
      $css_styles .= 'background:' . $this->aof_options['wpspb_sidebar_subnav_wrap_color'] . ';';
    }
    else
      $css_styles .= 'background:#22303a;';
  $css_styles .= '}';

  //sub menu colors
  $css_styles .= '.wps-sidebar-dropdown .wps-sidebar-submenu li a {';
    if(isset($this->aof_options['wpspb_sidebar_subnav_text_color']) && !empty($this->aof_options['wpspb_sidebar_subnav_text_color'])) {
      $css_styles .= 'color:' . $this->aof_options['wpspb_sidebar_subnav_text_color'] . ';';
    }
    else
      $css_styles .= 'color:#6c727f;';
  $css_styles .= '}';

  $css_styles .= '.wps-sidebar-dropdown .wps-sidebar-submenu li a:hover {';
    if(isset($this->aof_options['wpspb_sidebar_subnav_hover_text_color']) && !empty($this->aof_options['wpspb_sidebar_subnav_hover_text_color'])) {
      $css_styles .= 'color:' . $this->aof_options['wpspb_sidebar_subnav_hover_text_color'] . ';';
    }
    else
      $css_styles .= 'color:#ffffff;';
  $css_styles .= '}';

  //WPSPowerbox sidebar user info
  if( !empty($this->aof_options['wpspb_userinfo_text_color']) )
    $css_styles .= '.wps-sidebar-wrapper .user-info, .wps-sidebar-wrapper .wps-user-displayname {color:' . $this->aof_options['wpspb_userinfo_text_color'] . '}';

  if( !empty($this->aof_options['wpspb_userinfo_border_color']) )
    $css_styles .= '.wps-sidebar-wrapper .wps-menu-user-actions {border-color:' . $this->aof_options['wpspb_userinfo_border_color'] . '}';

  if( !empty($this->aof_options['wpspb_userinfo_icon_border_color']) )
    $css_styles .= '.wps-sidebar-wrapper .wps-menu-user-actions a {border-color:' . $this->aof_options['wpspb_userinfo_icon_border_color'] . '}';

  if( !empty($this->aof_options['wpspb_user_info_icon_color']) )
    $css_styles .= '.wps-sidebar-wrapper i.wps-power-switch:before, .wps-sidebar-wrapper i.wps-user:before {color:' . $this->aof_options['wpspb_user_info_icon_color'] . '}';

  if( !empty($this->aof_options['wpspb_userinfo_icon_hover_bg_color']) )
    $css_styles .= '.wps-sidebar-wrapper .wps-menu-user-actions a:hover {background-color:' . $this->aof_options['wpspb_userinfo_icon_hover_bg_color'] . ';
      border-color:' . $this->aof_options['wpspb_userinfo_icon_hover_bg_color'] . '}';

  if( !empty($this->aof_options['wpspb_userinfo_icon_hover_color']) )
    $css_styles .= '.wps-sidebar-wrapper .wps-menu-user-actions a:hover i.wps-power-switch:before,
    .wps-sidebar-wrapper .wps-menu-user-actions a:hover i.wps-user:before {color:' . $this->aof_options['wpspb_userinfo_icon_hover_color'] . '}';

  //woocommerce orders page screen options
  $css_styles .= 'body.has-wpspb-sidebar .woocommerce-layout__header, body.has-wpspb-sidebar .woocommerce-layout__activity-panel{top:0}
  body.has-wpspb-sidebar .woocommerce-layout__header .woocommerce-embed-page #screen-meta, .woocommerce-embed-page #screen-meta-links{top:36px}
  ';
  $css_styles .= '.wp-admin .woocommerce-layout__header{width:calc(100% - ' . $this->aof_options['admin_menu_width'] . 'px)}';
  $css_styles .= 'body.has-wpspb-sidebar .woocommerce-layout__header{width:calc(100% - ' . $wpspb_sidebar_width . 'px)}';

}

if( !empty( $this->aof_options['hide_woo_header_inbox'] ) ) {
 $css_styles .= '.woocommerce-layout__header{display: none !important;}
 .woocommerce-embed-page #screen-meta-links{top:0 !important}
 .woocommerce-layout{padding-top: 0 !important}
 ';
}

$css_styles .= '@media screen and (max-width: 782px) {
 .auto-fold #wpcontent {
     margin-left: 0;
 }
}';

/* WPSPowerbox Sidebar menu */

//custom css for non-privilege users
if( !empty( $wps_privilege_users ) && ! in_array( $current_user_id, $wps_privilege_users ) ) {
  $css_styles .= ( !empty( $this->aof_options['admin_page_custom_css_non_privilege'] ) ) ? $this->aof_options['admin_page_custom_css_non_privilege'] : '';
}

//custom css for all users
$css_styles .= ( !empty( $this->aof_options['admin_page_custom_css'] ) ) ? $this->aof_options['admin_page_custom_css'] : '';
$css_styles .= '</style>';

echo $this->wps_compress_css($css_styles);
