<?php
/*
 * WPSHAPERE
 * @author   AcmeeDesign
 * @url     http://acmeedesign.com
*/

defined('ABSPATH') || die;

$css_styles = '';
$css_styles .= '<style type="text/css">';

/* Admin Bar */
$css_styles .= '#wpadminbar, #wpadminbar .menupop .ab-sub-wrapper, .ab-sub-secondary, #wpadminbar .quicklinks .menupop ul.ab-sub-secondary,
#wpadminbar .quicklinks .menupop ul.ab-sub-secondary .ab-submenu {
  background: ' . $this->aof_options['admin_bar_color'] . '}';
$css_styles .= '#wpadminbar a.ab-item, #wpadminbar>#wp-toolbar span.ab-label, #wpadminbar>#wp-toolbar span.noticon, #wpadminbar .ab-icon:before,
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

$css_styles .= '#wpadminbar:not(.mobile)>#wp-toolbar a:focus span.ab-label,#wpadminbar:not(.mobile)>#wp-toolbar li:hover span.ab-label,
#wpadminbar>#wp-toolbar li.hover span.ab-label, #wpadminbar.mobile .quicklinks .hover .ab-icon:before,
#wpadminbar.mobile .quicklinks .hover .ab-item:before, #wpadminbar .quicklinks .menupop .ab-sub-secondary>li .ab-item:focus a,
#wpadminbar .quicklinks .menupop .ab-sub-secondary>li>a:hover {
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
$css_styles .= '.quicklinks li.wpshapere_site_title a{ outline:none; border:none;}
.quicklinks li.wpshapere_site_title {width: 180px !important;';
if($this->aof_options['logo_top_margin'] != 0)
  $css_styles .= 'margin-top:-' . $this->aof_options['logo_top_margin'] . 'px !important;';
if($this->aof_options['logo_bottom_margin'] != 0)
  $css_styles .= 'margin-top:' . $this->aof_options['logo_bottom_margin'] . 'px !important;';
$css_styles .= '}';
$css_styles .= '.quicklinks li.wpshapere_site_title a{outline:none; border:none;}';

if(!empty($this->aof_options['adminbar_external_logo_url']) && filter_var($this->aof_options['adminbar_external_logo_url'], FILTER_VALIDATE_URL)) {
  $adminbar_logo = esc_url( $this->aof_options['adminbar_external_logo_url']);
}
else {
  $adminbar_logo = (is_numeric($this->aof_options['admin_logo'])) ? $this->get_wps_image_url($this->aof_options['admin_logo']) : $this->aof_options['admin_logo'];
}

$logo_v_margins = '1px';
if(isset($this->aof_options['logo_bottom_margin']) && $this->aof_options['logo_bottom_margin'] != 0) {
  $logo_v_margins = $this->aof_options['logo_bottom_margin'] . 'px';
}
elseif(isset($this->aof_options['logo_top_margin']) && $this->aof_options['logo_top_margin'] != 0) {
  $logo_v_margins = '-' . $this->aof_options['logo_top_margin'] . 'px';
}
$logo_l_margins = 'center';
if(isset($this->aof_options['logo_left_margin']) && $this->aof_options['logo_left_margin'] != 0) {
  $logo_l_margins = $this->aof_options['logo_left_margin'] . 'px';
}

$css_styles .= '.quicklinks li.wpshapere_site_title a, .quicklinks li.wpshapere_site_title a:hover, .quicklinks li.wpshapere_site_title a:focus {';
if(!empty($adminbar_logo)){
  $css_styles .= 'background:url(' . $adminbar_logo . ') ' . $logo_l_margins . ' ' . $logo_v_margins . ' no-repeat !important; text-indent:-9999px !important; width: auto;';
}
if($this->aof_options['adminbar_logo_resize'] == 1 && $this->aof_options['adminbar_logo_size_percent'] > 1) {
  $css_styles .= 'background-size:' . $this->aof_options['adminbar_logo_size_percent'] . '%!important;';
}
else $css_styles .= 'background-size:contain!important;';
$css_styles .= '}';

/* Styles for New excite design */

if($this->aof_options['design_type'] == 3) {

  $css_styles .= '#adminmenuwrap{-webkit-box-shadow: 0px 4px 16px 0px rgba(0,0,0,0.3);
-moz-box-shadow: 0px 4px 16px 0px rgba(0,0,0,0.3);
box-shadow: 0px 4px 16px 0px rgba(0,0,0,0.3);}
ul#adminmenu a.wp-has-current-submenu:after, ul#adminmenu>li.current>a.current:after{
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
  }';

  $css_styles .= '#wp-toolbar > ul > li#wp-admin-bar-my-account > .ab-sub-wrapper:before{left:60%}';

  $css_styles .= '#wpadminbar .ab-top-menu>li.hover>.ab-item,#wpadminbar.nojq .quicklinks .ab-top-menu>li>.ab-item:focus,
  #wpadminbar:not(.mobile) .ab-top-menu>li:hover>.ab-item,#wpadminbar:not(.mobile) .ab-top-menu>li>.ab-item:focus{
    background: ' . $this->aof_options['admin_bar_color'] . '; color: ' . $this->aof_options['admin_bar_menu_color'] . '}';

}

$css_styles .= '</style>';

echo $this->wps_compress_css($css_styles);
