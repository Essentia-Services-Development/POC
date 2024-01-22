<style type="text/css">
<?php
$css_styles = "";
if(!empty($this->aof_options['login_external_bg_url']) && filter_var($this->aof_options['login_external_bg_url'], FILTER_VALIDATE_URL)) {
  $login_bg_img = esc_url( $this->aof_options['login_external_bg_url']);
}
else {
  $login_bg_img = (is_numeric($this->aof_options['login_bg_img'])) ? $this->get_wps_image_url($this->aof_options['login_bg_img']) : $this->aof_options['login_bg_img'];
}

if(!empty($this->aof_options['login_external_logo_url']) && filter_var($this->aof_options['login_external_logo_url'], FILTER_VALIDATE_URL)) {
  $login_logo = esc_url( $this->aof_options['login_external_logo_url']);
}
else {
  $login_logo = (is_numeric($this->aof_options['admin_login_logo'])) ? $this->get_wps_image_url($this->aof_options['admin_login_logo']) : $this->aof_options['admin_login_logo'];
}
if(empty($login_logo)) {
  $login_logo = WPSHAPERE_DIR_URI . 'assets/images/wps-logo-v5-195px.png';
}

if(empty($this->aof_options['login_form_align_type']) || $this->aof_options['login_form_align_type'] == 1) {
  $css_styles .= 'body.login, html { height: 100%; display:flex;justify-content: center;align-items: center;}';
}
else {
  $css_styles .= 'body.login, html {height: auto}';
}
$css_styles .= 'body.login{
  background-color: '. $this->aof_options['login_bg_color'] . ' !important;';
  if(!empty($login_bg_img))
    $css_styles .= 'background-image: url(' . $login_bg_img  . ');';
  if($this->aof_options['login_bg_img_repeat'] == 1)
    $css_styles .=  'background-repeat: repeat;';
    else
    $css_styles .= 'background-repeat: no-repeat;';
$css_styles .= 'background-position: center center;';

$login_bg_size = '100% auto';
if(isset($this->aof_options['login_bg_img_size']) && !empty($this->aof_options['login_bg_img_size'])) {
  $login_bg_size = $this->aof_options['login_bg_img_size'];
}
$css_styles .= 'background-size:' . $login_bg_size .';';
$css_styles .= 'background-attachment: fixed; margin:0; padding:1px; top: 0; right: 0; bottom: 0; left: 0;';
$css_styles .= '}';

$css_styles .= 'html, body.login:after { display: block; clear: both; }
body.login-action-register { position: relative }';
//$css_styles .= 'body.login-action-login, body.login-action-lostpassword { position: fixed }';
$css_styles .= '.wps-login-container{position:relative}
.login form {
    margin-top: 0;
    background: #fff !important;
    -webkit-box-shadow: none;
    -moz-box-shadow: none;
    box-shadow: none;
    border:none;
    border-bottom: 1px solid #f9f9f9;
    padding: 0px 55px 30px !important;
}
#login > h1 {
    padding: 40px 0;
}';

$css_styles .= '.login h1 a {';
  if(!empty($login_logo)) {
    $css_styles .= 'width: 100%;text-indent: -9999px;
    background-image: url('. $login_logo . ') !important;
    background-position:center center;
    background-repeat:no-repeat !important;
    ';
    if($this->aof_options['admin_logo_resize']) {
      $css_styles .= 'background-size: ' . $this->aof_options['admin_logo_size_percent'] . '%;';
    }
    else {
      $css_styles .= 'background-size:auto;';
    }
  }
$css_styles .= 'height: '. $this->aof_options['admin_logo_height'] . 'px; margin: 0 auto 20px;';
$css_styles .= '}';
$css_styles .= 'div#login {';

if(isset($this->aof_options['login_form_align_type']) && $this->aof_options['login_form_align_type'] == 2) {
  $css_styles .= 'margin-top: '. $this->aof_options['login_form_margintop'] . 'px;';
}
$css_styles .= 'padding: 18px 0';
$css_styles .= '}';
$css_styles .= 'body.interim-login div#login {position:relative;width: 95% !important; height: auto }
.login label, .login form, .login form p { color: ' . $this->aof_options['form_text_color'] . ' !important }
.login a { color: ' . $this->aof_options['form_link_color'] . '!important }
.login a:focus, .login a:hover { color: ' . $this->aof_options['form_link_hover_color'] . '!important; }';
$css_styles .= '.login form { background:';
  if($this->aof_options['login_divbg_transparent'] == 1)
    $css_styles .= 'transparent';
  else $css_styles .= $this->aof_options['login_formbg_color'] . '!important;
-webkit-box-shadow: none; -moz-box-shadow: none; box-shadow: none;';
if($this->aof_options['login_divbg_transparent'] != 1)
$css_styles .= 'border-bottom: 1px solid ' . $this->aof_options['form_border_color'] . ';';
$css_styles .= '}';
$css_styles .= 'form#loginform .button-primary, form#registerform .button-primary, .button-primary, .wp-core-ui .button, .wp-core-ui .button-large {
  background: '. $this->aof_options['login_button_color'] . '!important;
  color: '. $this->aof_options['login_button_text_color'] .'!important; text-shadow: none;}';
$css_styles .= 'form#loginform .button-primary.focus,form#loginform .button-primary.hover, .wp-core-ui .button:hover, .wp-core-ui .button-large:hover,
form#loginform .button-primary:focus,form#loginform .button-primary:hover, form#registerform .button-primary.focus,
form#registerform .button-primary.hover,form#registerform .button-primary:focus,form#registerform .button-primary:hover {
  background: '. $this->aof_options['login_button_hover_color'] . '!important;
  color: '. $this->aof_options['login_button_hover_text_color'] . '!important;';
$css_styles .= '}';
if($this->aof_options['login_divbg_transparent'] == 1) {
  $css_styles .= '.login #backtoblog, .login #nav { margin : 0; padding: 0 } .login form { padding-top: 2px !important}';
}

$css_styles .= '.login form .input, .login input[type=text]{font-size:13px;}';

$css_styles .= '.login #backtoblog, .login #nav{position: relative;z-index: 99;text-align:center}';

/* set custom font icons for input fields */

//get dash icons class data
$dashicons = new WPS_DASHICONS();
$dashicons_data = $dashicons->wps_dash_icons();

//get fa icons class data
$faicons = new WPSFAICONS();
$faicons_data = $faicons->wps_fa_icons();

//get line icons class data
$lniicons = new WPS_LNIICONS();
$lniicons_data = $lniicons->wps_lni_icons();

$css_styles .= '.wps-icon-login, .wps-icon-pwd, .wps-icon-email {
    font-size: 16px;
    width: 20px;
    text-align: left;
    position: absolute;
}
.wps-icon-login:before, .wps-icon-pwd:before, .wps-icon-email:before {
    display: inline-block;
    position: absolute;
    top: 13px;
    width: 20px;
    height: 20px;
    font-size: 16px;
    line-height: 1;
    text-decoration: inherit;
    font-weight: 400;
    font-style: normal;
    vertical-align: top;
    text-align: center;
    font-variant: normal !important;
    text-transform: none !important;
    speak: none;
    -webkit-transition: color .1s ease-in 0;
    transition: color .1s ease-in 0;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }
';

if(isset($this->aof_options['login_user_fld_icon']) && !empty($this->aof_options['login_user_fld_icon'])) {

  $wps_login_icon = explode('|', $this->aof_options['login_user_fld_icon']);
  $css_styles .= '.wps-icon-login:before {';
    if($wps_login_icon[0] == 'fa') {
      $css_styles .= 'font-family:FontAwesome!important;';
      $css_styles .= 'content: "' . $faicons_data[$wps_login_icon[1]] . '" !important;';
    }
    elseif($wps_login_icon[0] == 'lni') {
      $css_styles .= 'font-family:LineIcons!important;';
      $css_styles .= 'content: "' . $lniicons_data[$wps_login_icon[1]] . '" !important;';
    }
    else {
      $css_styles .= 'font-family:dashicons!important;';
      $css_styles .= 'content: "' . $dashicons_data[$wps_login_icon[1]] . '" !important;';
    }

  $css_styles .= '}';
}
else {
  $css_styles .= '.wps-icon-login:before {';
  $css_styles .= 'font-family:LineIcons!important;';
  $css_styles .= 'content: "\e9a4" !important;';
  $css_styles .= '}';
}

if(isset($this->aof_options['login_pwd_fld_icon']) && !empty($this->aof_options['login_pwd_fld_icon'])) {

  $wps_login_icon = explode('|', $this->aof_options['login_pwd_fld_icon']);
  $css_styles .= '.wps-icon-pwd:before {';
    if($wps_login_icon[0] == 'fa') {
      $css_styles .= 'font-family:FontAwesome!important;';
      $css_styles .= 'content: "' . $faicons_data[$wps_login_icon[1]] . '" !important;';
    }
    elseif($wps_login_icon[0] == 'lni') {
      $css_styles .= 'font-family:LineIcons!important;';
      $css_styles .= 'content: "' . $lniicons_data[$wps_login_icon[1]] . '" !important;';
    }
    else {
      $css_styles .= 'font-family:dashicons!important;';
      $css_styles .= 'content: "' . $dashicons_data[$wps_login_icon[1]] . '" !important;';
    }

  $css_styles .= '}';
}
else {
  $css_styles .= '.wps-icon-pwd:before {';
  $css_styles .= 'font-family:LineIcons!important;';
  $css_styles .= 'content: "\e946" !important;';
  $css_styles .= '}';
}

if(isset($this->aof_options['login_email_fld_icon']) && !empty($this->aof_options['login_email_fld_icon'])) {

  $wps_login_icon = explode('|', $this->aof_options['login_email_fld_icon']);
  $css_styles .= '.wps-icon-email:before {';
    if($wps_login_icon[0] == 'fa') {
      $css_styles .= 'font-family:FontAwesome!important;';
      $css_styles .= 'content: "' . $faicons_data[$wps_login_icon[1]] . '" !important;';
    }
    elseif($wps_login_icon[0] == 'lni') {
      $css_styles .= 'font-family:LineIcons!important;';
      $css_styles .= 'content: "' . $lniicons_data[$wps_login_icon[1]] . '" !important;';
    }
    else {
      $css_styles .= 'font-family:dashicons!important;';
      $css_styles .= 'content: "' . $dashicons_data[$wps_login_icon[1]] . '" !important;';
    }

  $css_styles .= '}';
}
else {
  $css_styles .= '.wps-icon-email:before {';
  $css_styles .= 'font-family:LineIcons!important;';
  $css_styles .= 'content: "\e997" !important;';
  $css_styles .= '}';
}

$login_form_inpt_padding = ( is_rtl() ) ? '9px 49px 9px 0 !important' : '9px 0 9px 32px !important';

$css_styles .= '.login form input.input { background: transparent;
  padding: ' . $login_form_inpt_padding . '; font-size: 16px !important; line-height: 1; outline: none !important;-webkit-border-radius: 0;
  -moz-border-radius: 0;
  -ms-border-radius: 0;
  border-radius: 0;}
input#user_login { background-position:7px -6px !important; }
input#user_pass, input#user_email, input#pass1, input#pass2 { background-position:7px -56px !important; }
.login form #wp-submit { width: 100%; height: 35px }
p.forgetmenot { margin-bottom: 16px !important; }
.login #pass-strength-result {margin: 12px 0 16px !important }
p.indicator-hint { clear:both }
div.updated, .login #login_error, .login .message {
  border-left: 4px solid '. $this->aof_options['msgbox_border_color'] .';
  background-color: '. $this->aof_options['msg_box_color'] .';
  color: '. $this->aof_options['msgbox_text_color'] .'; }

.login_footer_content { padding: 20px 0; text-align:center }
#resetpassform input.input {padding-left:9px!important}
';

if($this->aof_options['hide_backtoblog'] == 1)
  $css_styles .= '#backtoblog { display:none !important; }';
if($this->aof_options['hide_remember'] == 1)
  $css_styles .= 'p.forgetmenot { display:none !important; }';
if($this->aof_options['design_type'] == 1 || $this->aof_options['design_type'] == 3) {
$css_styles .= '
  .login .message, .button-primary,.wp-core-ui .button-primary,.button-primary:hover {
  	-webkit-box-shadow: none !important;
  	-moz-box-shadow: none !important;
  	box-shadow: none !important;
  }
  .button-primary,.wp-core-ui .button-primary,.button-primary:hover {
  	border: none !important;
  }';
}
else {
$css_styles .= '
.button-primary, form#loginform .button-primary, form#registerform .button-primary, .button-primary {
  border-color:'. $this->aof_options['login_button_border_color'] .' !important;}
form#loginform .button-primary.focus,form#loginform .button-primary.hover,form#loginform .button-primary:focus,form#loginform .button-primary:hover,
form#registerform .button-primary.focus,.button-primary:hover,form#registerform .button-primary.hover,
form#registerform .button-primary:focus,form#registerform .button-primary:hover{
  border-color:'. $this->aof_options['login_button_hover_border_color'] .' !important;}';
}
 //end of design_type
if( !isset($this->aof_options['disable_login_form_shadow']) || empty($this->aof_options['disable_login_form_shadow']) ) {
$css_styles .= 'div#login{
   -webkit-box-shadow: 5px 9px 19px 7px rgba(0,0,0,0.09);
   -moz-box-shadow: 5px 9px 19px 7px rgba(0,0,0,0.09);
   box-shadow: 5px 9px 19px 7px rgba(0,0,0,0.09);
  }';
}

$css_styles .= '.login form #wp-submit {
    margin-top: 37px;
    margin-bottom:20px;
    border:none;
  }
.login form input.input {border:none;border-bottom:1px solid #f5e9e9;}
  ';

if( isset($this->aof_options['login_button_style']) && $this->aof_options['login_button_style'] == 2 ) {
$css_styles .= '
  .login form #wp-submit {
      margin-top: 37px;
      height: 60px;
      -webkit-border-radius: 30px;
      -moz-border-radius: 30px;
      -ms-border-radius: 30px;
      border-radius: 30px;
      -webkit-box-shadow: 8px 8px 20px 4px rgba(0, 0, 0, 0.21) !important;
      -moz-box-shadow: 8px 8px 20px 4px rgba(0, 0, 0, 0.21) !important;
      box-shadow: 8px 8px 20px 4px rgba(0, 0, 0, 0.21) !important;
  }';
}

if(!isset($this->aof_options['theme_preset']) || $this->aof_options['theme_preset'] == 'plain') {
  $css_styles .= '.login form input.input{-webkit-box-shadow: none!important;
  -moz-box-shadow: none!important;
  box-shadow: none!important;}';
}

$css_styles .= 'div#login {
	width: '. $this->aof_options['login_form_width'] .'px !important;
  background-color:' . $this->aof_options['login_formbg_color'] .';
}';

//theme styles
if(isset($this->aof_options['login_theme_preset']) && $this->aof_options['login_theme_preset'] == 'rangoli') {
  $css_styles .= '
  .login p {text-align:center}
  ::-webkit-input-placeholder { color: #d5d5d5; }
  :-ms-input-placeholder { color: #d5d5d5; }
  ::placeholder { color: #d5d5d5; }
  ';
}

if(isset($this->aof_options['login_theme_preset']) && $this->aof_options['login_theme_preset'] == "gradientocean") {
    $css_styles .= '
    div#login{
       -webkit-box-shadow: 5px 9px 19px 7px rgba(0,0,0,0.25);
       -moz-box-shadow: 5px 9px 19px 7px rgba(0,0,0,0.25);
       box-shadow: 5px 9px 19px 7px rgba(0,0,0,0.25);
       background:url(' . WPSHAPERE_DIR_URI . 'assets/images/transparent-gradient.png) center bottom no-repeat;
       height:100%;';
         if($this->aof_options['login_divbg_transparent'] == 1)
           $css_styles .= 'background-color:transparent;';
         else $css_styles .= 'background-color:' . $this->aof_options['login_formbg_color'] .';';
    $css_styles .= '}
    .wps-login-container {
        position: relative;
    }
    .wps-bg-effectss{
      content: "";
      width:100%;
      height:100%;
      display:inline-block;
      position:absolute;
      top:0;
      left:0;
      background:url(' . WPSHAPERE_DIR_URI . 'assets/images/transparent-gradient.png) center bottom no-repeat;
      z-index:1;
    }
    .login form {
      background:transparent!important;
      position:relative;
      z-index:5
    }
    .login form input.input,.login form input[type=checkbox] { background: transparent;color:#b0c9ec}
    .login form input.input {border:none;border-bottom:1px solid rgba(255,255,255,0.50);}
    .login form {border-bottom:none;}
    ::-webkit-input-placeholder { color: #d5d5d5; }
    :-ms-input-placeholder { color: #d5d5d5; }
    ::placeholder { color: #d5d5d5; }
    ';
}

$css_styles .= '@media screen and (max-width: 680px){
	div#login {
		width: 90% !important;
	}
	body.login {
		background-size: auto;
	}
	body.login-action-login, body.login-action-lostpassword {
		position: relative;
	}
}';

$css_styles .= '@media screen and (max-width: 480px){
	div#login {
		width: 320px !important;
	}
  .login form {
    padding: 0px 15px 30px !important;
  }
}';

$css_styles .= $this->aof_options['login_custom_css'];

$css_styles .= '</style>';

echo $this->wps_compress_css($css_styles);
