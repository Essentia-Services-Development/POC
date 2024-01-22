<?php
/*
 * Configuration for the options function
 */

function is_wps_single() {
   if(!is_multisite())
	return true;
   elseif(is_multisite() && !defined('NETWORK_ADMIN_CONTROL'))
	return true;
   else return false;
}

function get_wps_options() {

  $blog_email = get_option('admin_email');
  $blog_from_name = get_option('blogname');

  if(is_wps_single()) {
    $wps_options = (is_serialized(get_option(WPSHAPERE_OPTIONS_SLUG))) ? unserialize(get_option(WPSHAPERE_OPTIONS_SLUG)) : get_option(WPSHAPERE_OPTIONS_SLUG);
  }
  else {
    $wps_options = (is_serialized(get_site_option(WPSHAPERE_OPTIONS_SLUG))) ? unserialize(get_site_option(WPSHAPERE_OPTIONS_SLUG)) : get_site_option(WPSHAPERE_OPTIONS_SLUG);
  }

  /**
  * get adminbar items
  *
  */
  if(is_wps_single()) {
    $adminbar_items = (is_serialized(get_option(WPS_ADMINBAR_LIST_SLUG))) ? unserialize(get_option(WPS_ADMINBAR_LIST_SLUG)) : get_option(WPS_ADMINBAR_LIST_SLUG);
  }
  else {
    $adminbar_items = (is_serialized(get_site_option(WPS_ADMINBAR_LIST_SLUG))) ? unserialize(get_site_option(WPS_ADMINBAR_LIST_SLUG)) : get_site_option(WPS_ADMINBAR_LIST_SLUG);
  }

  $adminbar_items = (!empty($adminbar_items)) ? array_unique($adminbar_items) : "";

  //get all admin users
  $admin_users_array = (is_serialized(get_option(WPS_ADMIN_USERS_SLUG))) ? unserialize(get_option(WPS_ADMIN_USERS_SLUG)) : get_option(WPS_ADMIN_USERS_SLUG);

  if(empty($admin_users_array) && !is_array($admin_users_array)) {
    //$users_query = new WP_User_Query( array( 'role' => 'Administrator' ) );
    if(isset($users_query) && !empty($users_query)) {
        if ( ! empty( $users_query->results ) ) {
            foreach ( $users_query->results as $user_detail ) {
                $admin_users_array[$user_detail->ID] = $user_detail->data->display_name;
            }
        }
    }
  }

  //get dashboard widgets
  if(is_wps_single()) {
    $dash_widgets_list = (is_serialized(get_option('wps_widgets_list'))) ? unserialize(get_option('wps_widgets_list')) : get_option('wps_widgets_list');
  }
  else {
    $dash_widgets_list = (is_serialized(get_site_option('wps_widgets_list'))) ? unserialize(get_site_option('wps_widgets_list')) : get_site_option('wps_widgets_list');
  }

  $wps_dash_widgets = array();
  $wps_dash_widgets['welcome_panel'] = "Welcome Panel";
  if(!empty($dash_widgets_list)) {
      foreach( $dash_widgets_list as $dash_widget ) {
          $dash_widget_name = (empty($dash_widget[1])) ? $dash_widget[0] : $dash_widget[1];
          $wps_dash_widgets[$dash_widget[0]] = $dash_widget_name;
      }
  }

  $panel_tabs = array(
      'general' => esc_html__( 'General Options', 'wps' ),
      'login' => esc_html__( 'Login Options', 'wps' ),
      'dash' => esc_html__( 'Dashboard Options', 'wps' ),
      'adminbar' => esc_html__( 'Adminbar Options', 'wps' ),
      'adminop' => esc_html__( 'Admin Page Options', 'wps' ),
      'adminmenu' => esc_html__( 'Admin menu Options', 'wps' ),
      'adminmenu_user_profile' => esc_html__( 'User Info', 'wps' ),
      'footer' => esc_html__( 'Footer Options', 'wps' ),
      'email' => esc_html__( 'Email Options', 'wps' ),
      'privilege_users' => esc_html__( 'Set Privilege users', 'wps' ),
      'admin_notices' => esc_html__( 'Admin notices', 'wps' ),
      );

  $panel_tabs = apply_filters( 'aof_tabs_list', $panel_tabs );

  $panel_fields = array();

  //General Options
  $panel_fields[] = array(
      'name' => esc_html__( 'General Options', 'wps' ),
      'type' => 'openTab'
  );

  $panel_fields[] = array(
      'name' => esc_html__( 'Choose design type', 'wps' ),
      'id' => 'design_type',
      'type' => 'radio',
      'options' => array(
          '4' => __( 'Liquido <span class="wps-designtype-new">New</span>', 'wps' ),
          '3' => esc_html__( 'Neu Excite (Lite)', 'wps' ),
          '1' => esc_html__( 'Flat design', 'wps' ),
          '2' => esc_html__( 'Default design', 'wps' ),
      ),
      'default' => '4',
      );

  $panel_fields[] = array(
      'id' => 'heading_colors',
      'type' => 'fieldsetwrapstart',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Heading Colors', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'H1', 'wps' ),
      'id' => 'h1_color',
      'type' => 'wpcolor',
      'default' => '#333333',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'H2', 'wps' ),
      'id' => 'h2_color',
      'type' => 'wpcolor',
      'default' => '#222222',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'H3', 'wps' ),
      'id' => 'h3_color',
      'type' => 'wpcolor',
      'default' => '#222222',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'H4', 'wps' ),
      'id' => 'h4_color',
      'type' => 'wpcolor',
      'default' => '#555555',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'H5', 'wps' ),
      'id' => 'h5_color',
      'type' => 'wpcolor',
      'default' => '#555555',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'H6', 'wps' ),
      'id' => 'h6_color',
      'type' => 'wpcolor',
      'default' => '#555555',
      );

 //need necessary closing of div for fieldsetwrap
  $panel_fields[] = array(
      'id' => 'heading_colors',
      'type' => 'fieldsetwrapclose',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Other General Options', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Remove unwanted items', 'wps' ),
      'id' => 'admin_generaloptions',
      'type' => 'multicheck',
      'desc' => esc_html__( 'Select whichever you want to remove.', 'wps' ),
      'options' => array(
          '1' => esc_html__( 'Wordpress Help tab.', 'wps' ),
          '2' => esc_html__( 'Screen Options.', 'wps' ),
          '3' => esc_html__( 'Wordpress update notifications.', 'wps' ),
      ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Disable automatic updates', 'wps' ),
      'id' => 'disable_auto_updates',
      'type' => 'checkbox',
      'desc' => esc_html__( 'Select to disable all automatic background updates (Not recommended).', 'wps' ),
      'default' => false,
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Disable update emails', 'wps' ),
      'id' => 'disable_update_emails',
      'type' => 'checkbox',
      'desc' => esc_html__( 'Select to disable emails regarding automatic updates.', 'wps' ),
      'default' => false,
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Hide update notifications', 'wps' ),
      'id' => 'hide_update_note_plugins',
      'type' => 'checkbox',
      'desc' => esc_html__( 'Select to hide update notifications on plugins page (Not recommended).', 'wps' ),
      'default' => false,
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Hide Admin bar', 'wps' ),
      'id' => 'hide_admin_bar',
      'type' => 'checkbox',
      'desc' => esc_html__( 'Select to hideadmin bar on frontend.', 'wps' ),
      'default' => false,
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Hide Color picker from user profile', 'wps' ),
      'id' => 'hide_profile_color_picker',
      'type' => 'checkbox',
      'desc' => esc_html__( 'Select to hide Color picker from user profile.', 'wps' ),
      'default' => false,
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Hide Woocommerce header under Woocommerce admin', 'alter' ),
      'id' => 'hide_woo_header_inbox',
      'type' => 'checkbox',
      'desc' => esc_html__( 'Select to hide Woocommerce header.', 'alter' ),
      'default' => false,
      );

  //Login Options
  $panel_fields[] = array(
      'name' => esc_html__( 'Login Options', 'wps' ),
      'type' => 'openTab'
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Disable custom styles for login page.', 'wps' ),
      'id' => 'disable_styles_login',
      'type' => 'checkbox',
      'desc' => esc_html__( 'Check to disable', 'wps' ),
      'default' => false,
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Login page title', 'wps' ),
      'id' => 'login_page_title',
      'type' => 'text',
      'default' => get_bloginfo('name'),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Background Options', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Background color', 'wps' ),
      'id' => 'login_bg_color',
      'type' => 'wpcolor',
      'default' => '#b8f2e8',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'External background url', 'wps' ),
      'id' => 'login_external_bg_url',
      'type' => 'text',
      'desc' => esc_html__( 'Load image from external source.', 'wps' ),
  );

  $panel_fields[] = array(
      'name' => esc_html__( 'Background image', 'wps' ),
      'id' => 'login_bg_img',
      'type' => 'upload',
      );

  $panel_fields[] = array(
      'id' => 'login_bg_options',
      'type' => 'fieldsetwrapstart',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Background Repeat', 'wps' ),
      'id' => 'login_bg_img_repeat',
      'type' => 'checkbox',
      'desc' => esc_html__( 'Check to repeat', 'wps' ),
      'default' => true,
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Background size', 'wps' ),
      'id' => 'login_bg_img_size',
      'type' => 'select',
      'desc' => esc_html__( 'Select size', 'wps' ),
      'options' => array(
          'cover' => esc_html__( 'Cover', 'wps' ),
          'auto' => esc_html__( 'Auto', 'wps' ),
          'contain' => esc_html__( 'Contain', 'wps' ),
          '100% 100%' => esc_html__( '100% 100%', 'wps' ),
        ),
      'default' => 'cover',
      );

  $panel_fields[] = array(
      'id' => 'login_bg_options',
      'type' => 'fieldsetwrapclose',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Login Form Options', 'wps' ),
      'type' => 'title',
      );

if(1 == 2) { //currently disabled
  $panel_fields[] = array(
      'name' => esc_html__( 'Form Layout', 'wps' ),
      'id' => 'login_form_layout_type',
      'type' => 'radio',
      'desc' => esc_html__( 'Select layout', 'wps' ),
      'options' => array(
          '1' => esc_html__( 'Single column', 'wps' ),
          '2' => esc_html__( 'Double column', 'wps' ),
        ),
      'default' => 1,
      );
}

  $panel_fields[] = array(
      'name' => esc_html__( 'Form vertical align method', 'wps' ),
      'id' => 'login_form_align_type',
      'type' => 'select',
      'desc' => esc_html__( 'Select align type', 'wps' ),
      'options' => array(
          '1' => esc_html__( 'CSS Flex method.', 'wps' ),
          '2' => esc_html__( 'Top Margin.', 'wps' ),
        ),
      'default' => 1,
      );

if($wps_options['login_form_align_type'] != 1) {
  $panel_fields[] = array(
      'name' => esc_html__( 'Login Form Top margin', 'wps' ),
      'id' => 'login_form_margintop',
      'type' => 'number',
      'default' => '100',
      'min' => '0',
      'max' => '700',
      );
}
  $panel_fields[] = array(
      'name' => esc_html__( 'Login Form Width in px', 'wps' ),
      'id' => 'login_form_width',
      'type' => 'number',
      'default' => '500',
      'min' => '350',
      'max' => '700',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Disable Form Shadow', 'wps' ),
      'id' => 'disable_login_form_shadow',
      'type' => 'checkbox',
      'desc' => esc_html__( 'Select to show Form shadow.', 'wps' ),
      'default' => false,
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'External Logo url', 'wps' ),
      'id' => 'login_external_logo_url',
      'type' => 'text',
      'desc' => esc_html__( 'Load image from external source.', 'wps' ),
  );

  $panel_fields[] = array(
      'name' => esc_html__( 'Upload Logo', 'wps' ),
      'id' => 'admin_login_logo',
      'type' => 'upload',
      'desc' => esc_html__( 'Image to be displayed on login page. Maximum width should be under 450pixels.', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Resize Logo?', 'wps' ),
      'id' => 'admin_logo_resize',
      'type' => 'checkbox',
      'default' => false,
      'desc' => esc_html__( 'Select to resize logo size.', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Set Logo size in %', 'wps' ),
      'id' => 'admin_logo_size_percent',
      'type' => 'number',
      'default' => '1',
      'max' => '100',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Logo Height', 'wps' ),
      'id' => 'admin_logo_height',
      'type' => 'number',
      'default' => '50',
      'max' => '150',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Logo url', 'wps' ),
      'id' => 'login_logo_url',
      'type' => 'text',
      'default' => get_bloginfo('url'),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Transparent Form', 'wps' ),
      'id' => 'login_divbg_transparent',
      'type' => 'checkbox',
      'default' => false,
      'desc' => esc_html__( 'Select to show transparent form background.', 'wps' ),
      );

  $panel_fields[] = array(
      'id' => 'form_bg_colors',
      'type' => 'fieldsetwrapstart',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Form background color', 'wps' ),
      'id' => 'login_formbg_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Form border color', 'wps' ),
      'id' => 'form_border_color',
      'type' => 'wpcolor',
      'default' => '#e5e5e5',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Form text color', 'wps' ),
      'id' => 'form_text_color',
      'type' => 'wpcolor',
      'default' => '#777777',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Form link color', 'wps' ),
      'id' => 'form_link_color',
      'type' => 'wpcolor',
      'default' => '#212121',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Form link hover color', 'wps' ),
      'id' => 'form_link_hover_color',
      'type' => 'wpcolor',
      'default' => '#101010',
      );

  $panel_fields[] = array(
      'id' => 'form_bg_colors',
      'type' => 'fieldsetwrapclose',
      );

  $panel_fields[] = array(
      'id' => 'hide_backlink_remember',
      'type' => 'fieldsetwrapstart',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Hide Back to blog link', 'wps' ),
      'id' => 'hide_backtoblog',
      'type' => 'checkbox',
      'default' => false,
      'desc' => esc_html__( 'select to hide', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Hide Remember me', 'wps' ),
      'id' => 'hide_remember',
      'type' => 'checkbox',
      'default' => false,
      'desc' => esc_html__( 'select to hide', 'wps' ),
      );

  $panel_fields[] = array(
      'id' => 'hide_backlink_remember',
      'type' => 'fieldsetwrapclose',
      );

  $panel_fields[] = array(
      'id' => 'login_field_icons',
      'type' => 'fieldsetwrapstart',
  );

  $panel_fields[] = array(
      'name' => esc_html__( 'Username field icon', 'wps' ),
      'id' => 'login_user_fld_icon',
      'type' => 'aoficon',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Password field icon', 'wps' ),
      'id' => 'login_pwd_fld_icon',
      'type' => 'aoficon',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Email field icon', 'wps' ),
      'id' => 'login_email_fld_icon',
      'type' => 'aoficon',
      );

  $panel_fields[] = array(
      'id' => 'login_field_icons',
      'type' => 'fieldsetwrapclose',
  );

  $panel_fields[] = array(
      'name' => esc_html__( 'Custom Footer content', 'wps' ),
      'id' => 'login_footer_content',
      'type' => 'wpeditor',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Login button style', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Login button style', 'wps' ),
      'id' => 'login_button_style',
      'type' => 'select',
      'desc' => esc_html__( 'select button style', 'wps' ),
      'options' => array(
          '1' => esc_html__( 'Flat button.', 'wps' ),
          '2' => esc_html__( 'Round button.', 'wps' ),
        ),
      'default' => 1,
      );

  $panel_fields[] = array(
      'id' => 'login_bg_color_options',
      'type' => 'fieldsetwrapstart',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Login button colors', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Background color', 'wps' ),
      'id' => 'login_button_color',
      'type' => 'wpcolor',
      'default' => '#0014c6',
      );

  if(isset($wps_options['design_type']) && $wps_options['design_type'] == 2) {
    $panel_fields[] = array(
        'name' => esc_html__( 'Border color', 'wps' ),
        'id' => 'login_button_border_color',
        'type' => 'wpcolor',
        'default' => '#86b520',
        );

    $panel_fields[] = array(
        'name' => esc_html__( 'Shadow color', 'wps' ),
        'id' => 'login_button_shadow_color',
        'type' => 'wpcolor',
        'default' => '#98ce23',
        );
}

  $panel_fields[] = array(
      'name' => esc_html__( 'Text color', 'wps' ),
      'id' => 'login_button_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Hover background color', 'wps' ),
      'id' => 'login_button_hover_color',
      'type' => 'wpcolor',
      'default' => '#0514a2',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Hover text color', 'wps' ),
      'id' => 'login_button_hover_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'id' => 'login_bg_color_options',
      'type' => 'fieldsetwrapclose',
      );

  if(isset($wps_options['design_type']) && $wps_options['design_type'] == 2) {
    $panel_fields[] = array(
        'name' => esc_html__( 'Button hover border color', 'wps' ),
        'id' => 'login_button_hover_border_color',
        'type' => 'wpcolor',
        'default' => '#259633',
        );

    $panel_fields[] = array(
        'name' => esc_html__( 'Button hover shadow color', 'wps' ),
        'id' => 'login_button_hover_shadow_color',
        'type' => 'wpcolor',
        'default' => '#3d7a0c',
        );
  }

  $panel_fields[] = array(
      'name' => esc_html__( 'Custom CSS', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Custom CSS for Login page', 'wps' ),
      'id' => 'login_custom_css',
      'type' => 'textarea',
      );


  //Dash Options
  $panel_fields[] = array(
      'name' => esc_html__( 'Dashboard Options', 'wps' ),
      'type' => 'openTab'
      );

  if(!empty($wps_dash_widgets) && is_array($wps_dash_widgets)) {
      $panel_fields[] = array(
          'name' => esc_html__( 'Remove unwanted Widgets', 'wps' ),
          'id' => 'remove_dash_widgets',
          'type' => 'multicheck',
          'desc' => esc_html__( 'Select whichever you want to remove.', 'wps' ),
          'options' => $wps_dash_widgets,
          );
  }

  $panel_fields[] = array(
      'name' => esc_html__( 'Create New Widgets', 'wps' ),
      'type' => 'title',
      'desc' => sprintf( '<a target="_blank" href="%s" class="aof-quickvideo-btn">' . esc_html__( 'Quick Video Help', 'wps' ) . '</a>',  'https://youtu.be/49KaRtMmVVI' ) . '<br />',
      );

if(defined('POWERBOX_PATH')) {

  $panel_fields[] = array(
      'name' => esc_html__( 'Set number of widgets required.', 'wps' ),
      'id' => 'set_dash_widget_count',
      'type' => 'number',
      'default' => '5',
      'max' => '50',
      );

  $dash_counts = (isset($wps_options['set_dash_widget_count']) && !empty($wps_options['set_dash_widget_count'])) ? $wps_options['set_dash_widget_count'] : 5;
    for ($w=0; $w < $dash_counts; $w++) {

      $n = $w + 1;
      $panel_fields[] = array(
          'type' => 'note',
          'desc' => esc_html__( 'Widget', 'wps' ) . " " . $n,
          );

      $panel_fields[] = array(
          'name' => esc_html__( 'Widget Type', 'wps' ),
          'id' => 'wps_widget_'.$n.'_type',
          'options' => array(
              '1' => esc_html__( 'RSS Feed', 'wps' ),
              '2' => esc_html__( 'Text Content', 'wps' ),
              '3' => esc_html__( 'Video Content', 'wps' ),
          ),
          'type' => 'radio',
          'default' => '2',
          );

          $panel_fields[] = array(
            'name' => esc_html__( 'Widget Position', 'wps' ),
            'id' => 'wps_widget_'.$n.'_position',
            'options' => array(
                'normal' => esc_html__( 'Left', 'wps' ),
                'side' => esc_html__( 'Right', 'wps' ),
            ),
            'type' => 'select',
          );

          $panel_fields[] = array(
            'name' => esc_html__( 'Widget Title', 'wps' ) . ' *',
            'id' => 'wps_widget_'.$n.'_title',
            'type' => 'text',
          );

          $panel_fields[] = array(
            'name' => esc_html__( 'RSS Feed url', 'wps' ),
            'id' => 'wps_widget_'.$n.'_rss',
            'type' => 'text',
            'desc' => esc_html__( 'Put your RSS feed url here if you want to show your own RSS feeds. Otherwise fill your static contents in the below editor.', 'wps' ),
          );

          $panel_fields[] = array(
            'name' => esc_html__( 'Widget Content', 'wps' ),
            'id' => 'wps_widget_'.$n.'_content',
            'type' => 'wpeditor',
          );

    } //close of for
  }
  else {

  $panel_fields[] = array(
      'type' => 'note',
      'desc' => esc_html__( 'Widget 1', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Widget Type', 'wps' ),
      'id' => 'wps_widget_1_type',
      'options' => array(
          '1' => esc_html__( 'RSS Feed', 'wps' ),
          '2' => esc_html__( 'Text Content', 'wps' ),
          '3' => esc_html__( 'Video Content', 'wps' ),
      ),
      'type' => 'radio',
      'default' => '1',
      );

  $panel_fields[] = array(
    'name' => esc_html__( 'Widget Position', 'wps' ),
    'id' => 'wps_widget_1_position',
    'options' => array(
        'normal' => esc_html__( 'Left', 'wps' ),
        'side' => esc_html__( 'Right', 'wps' ),
    ),
    'type' => 'select',
  );

  $panel_fields[] = array(
      'name' => esc_html__( 'Widget Title', 'wps' ),
      'id' => 'wps_widget_1_title',
      'type' => 'text',
  );

  $panel_fields[] = array(
      'name' => esc_html__( 'RSS Feed url', 'wps' ),
      'id' => 'wps_widget_1_rss',
      'type' => 'text',
      'desc' => esc_html__( 'Put your RSS feed url here if you want to show your own RSS feeds. Otherwise fill your static contents in the below editor.', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Widget Content', 'wps' ),
      'id' => 'wps_widget_1_content',
      'type' => 'wpeditor',
      );

  $panel_fields[] = array(
      'type' => 'note',
      'desc' => esc_html__( 'Widget 2', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Widget Type', 'wps' ),
      'id' => 'wps_widget_2_type',
      'options' => array(
          '1' => esc_html__( 'RSS Feed', 'wps' ),
          '2' => esc_html__( 'Text Content', 'wps' ),
          '3' => esc_html__( 'Video Content', 'wps' ),
      ),
      'type' => 'radio',
      'default' => '1',
      );

  $panel_fields[] = array(
          'name' => esc_html__( 'Widget Position', 'wps' ),
          'id' => 'wps_widget_2_position',
      'options' => array(
          'normal' => esc_html__( 'Left', 'wps' ),
          'side' => esc_html__( 'Right', 'wps' ),
      ),
      'type' => 'select',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Widget Title', 'wps' ),
      'id' => 'wps_widget_2_title',
      'type' => 'text',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'RSS Feed url', 'wps' ),
      'id' => 'wps_widget_2_rss',
      'type' => 'text',
      'desc' => esc_html__( 'Put your RSS feed url here if you want to show your own RSS feeds. Otherwise fill your static contents in the below editor.', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Widget Content', 'wps' ),
      'id' => 'wps_widget_2_content',
      'type' => 'wpeditor',
      );

  $panel_fields[] = array(
      'type' => 'note',
      'desc' => esc_html__( 'Widget 3', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Widget Type', 'wps' ),
      'id' => 'wps_widget_3_type',
      'options' => array(
          '1' => esc_html__( 'RSS Feed', 'wps' ),
          '2' => esc_html__( 'Text Content', 'wps' ),
          '3' => esc_html__( 'Video Content', 'wps' ),
      ),
      'type' => 'radio',
      'default' => '1',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Widget Position', 'wps' ),
      'id' => 'wps_widget_3_position',
      'options' => array(
          'normal' => esc_html__( 'Left', 'wps' ),
          'side' => esc_html__( 'Right', 'wps' ),
      ),
      'type' => 'select',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Widget Title', 'wps' ),
      'id' => 'wps_widget_3_title',
      'type' => 'text',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'RSS Feed url', 'wps' ),
      'id' => 'wps_widget_3_rss',
      'type' => 'text',
      'desc' => esc_html__( 'Put your RSS feed url here if you want to show your own RSS feeds. Otherwise fill your static contents in the below editor.', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Widget Content', 'wps' ),
      'id' => 'wps_widget_3_content',
      'type' => 'wpeditor',
      );

  $panel_fields[] = array(
      'type' => 'note',
      'desc' => esc_html__( 'Widget 4', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Widget Type', 'wps' ),
      'id' => 'wps_widget_4_type',
      'options' => array(
          '1' => esc_html__( 'RSS Feed', 'wps' ),
          '2' => esc_html__( 'Text Content', 'wps' ),
          '3' => esc_html__( 'Video Content', 'wps' ),
      ),
      'type' => 'radio',
      'default' => '1',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Widget Position', 'wps' ),
      'id' => 'wps_widget_4_position',
      'options' => array(
          'normal' => esc_html__( 'Left', 'wps' ),
          'side' => esc_html__( 'Right', 'wps' ),
      ),
      'type' => 'select',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Widget Title', 'wps' ),
      'id' => 'wps_widget_4_title',
      'type' => 'text',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'RSS Feed url', 'wps' ),
      'id' => 'wps_widget_4_rss',
      'type' => 'text',
      'desc' => esc_html__( 'Put your RSS feed url here if you want to show your own RSS feeds. Otherwise fill your static contents in the below editor.', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Widget Content', 'wps' ),
      'id' => 'wps_widget_4_content',
      'type' => 'wpeditor',
      );

} //end of elseif

  //AdminBar Options
  $panel_fields[] = array(
      'name' => esc_html__( 'Adminbar Options', 'wps' ),
      'type' => 'openTab'
      );

    $panel_fields[] = array(
        'name' => esc_html__( 'Hide admin bar.', 'wps' ),
        'id' => 'hide_adminbar_backend',
        'type' => 'checkbox',
        'default' => false,
        'desc' => esc_html__( 'Select this option to hide admin bar on the WordPress backend.', 'wps' ),
    );

  $panel_fields[] = array(
      'name' => esc_html__( 'Set default adminbar height.', 'wps' ),
      'id' => 'default_adminbar_height',
      'type' => 'checkbox',
      'default' => false,
      'desc' => esc_html__( 'Select this option to set default admin bar height.', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Logo options', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'External Logo url', 'wps' ),
      'id' => 'adminbar_external_logo_url',
      'type' => 'text',
      'desc' => esc_html__( 'Load image from external source. Maximum size 200x50 pixels.', 'wps' ),
  );

  $panel_fields[] = array(
      'name' => esc_html__( 'Upload Logo', 'wps' ),
      'id' => 'admin_logo',
      'type' => 'upload',
      'desc' => esc_html__( 'Image to be displayed in all pages. Maximum size 200x50 pixels.', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Logo link', 'wps' ),
      'id' => 'adminbar_logo_link',
      'type' => 'text',
      'desc' => esc_html__( 'If empty it will default to admin dashboard url.', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Resize Logo?', 'wps' ),
      'id' => 'adminbar_logo_resize',
      'type' => 'checkbox',
      'default' => false,
      'desc' => esc_html__( 'Select to resize logo size.', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Set Logo size in %', 'wps' ),
      'id' => 'adminbar_logo_size_percent',
      'type' => 'number',
      'default' => '75',
      'max' => '100',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Move logo horizontally by', 'wps' ),
      'id' => 'logo_left_margin',
      'type' => 'number',
      'desc' => esc_html__( "Can be used in case of logo position haven't matched the menu position.", 'wps' ),
      'default' => '0',
      'min' => '-75',
      'max' => '150',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Move logo Top by', 'wps' ),
      'id' => 'logo_top_margin',
      'type' => 'number',
      'desc' => esc_html__( "Can be used in case of logo position haven't matched the menu position.", 'wps' ),
      'default' => '0',
      'max' => '20',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Move logo Bottom by', 'wps' ),
      'id' => 'logo_bottom_margin',
      'type' => 'number',
      'desc' => esc_html__( "Can be used in case of logo position haven't matched the menu position.", 'wps' ),
      'default' => '0',
      'max' => '20',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Collapsed menu Logo options', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'External Logo url', 'wps' ),
      'id' => 'collapsed_adminbar_ext_logo_url',
      'type' => 'text',
      'desc' => esc_html__( 'External logo url for collapsed menu. Maximum size 100x100 pixels.', 'wps' ),
  );

  $panel_fields[] = array(
      'name' => esc_html__( 'Upload Logo', 'wps' ),
      'id' => 'collapsed_admin_logo',
      'type' => 'upload',
      'desc' => esc_html__( 'Upload logo for collapsed menu. Maximum size 100x100 pixels.', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Color options', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Admin bar color', 'wps' ),
      'id' => 'admin_bar_color',
      'type' => 'wpcolor',
      'default' => '#fff',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Menu Link color', 'wps' ),
      'id' => 'admin_bar_menu_color',
      'type' => 'wpcolor',
      'default' => '#94979B',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Menu Link hover color', 'wps' ),
      'id' => 'admin_bar_menu_hover_color',
      'type' => 'wpcolor',
      'default' => '#474747',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Menu background hover/Sub menu color', 'wps' ),
      'id' => 'admin_bar_menu_bg_hover_color',
      'type' => 'wpcolor',
      'default' => '#f4f4f4',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Submenu Link color', 'wps' ),
      'id' => 'admin_bar_sbmenu_link_color',
      'type' => 'wpcolor',
      'default' => '#666666',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Submenu Link hover color', 'wps' ),
      'id' => 'admin_bar_sbmenu_link_hover_color',
      'type' => 'wpcolor',
      'default' => '#333333',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Custom welcome Text', 'wps' ),
      'id' => 'adminbar_custom_welcome_text',
      'type' => 'text',
      'desc' => esc_html__( 'Custom welcome text instead of default "Howdy".', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Remove menus from Admin bar', 'wps' ),
      'type' => 'title',
      'desc' => sprintf( '<a target="_blank" href="%s" class="aof-quickvideo-btn">' . esc_html__( 'Quick Video Help', 'wps' ) . '</a>',  'https://youtu.be/Ltlg1x7NfT0' ) . '<br />' .
      esc_html__( 'By default, all menu items will be shown to admin users. Please set who can access to hidden menu items in Privilege users tab.', 'wps' ),
      );

  if(!empty($adminbar_items)) {
    $panel_fields[] = array(
        'name' => esc_html__( 'Remove Unwanted Menus', 'wps' ),
        'id' => 'hide_admin_bar_menus',
        'type' => 'multicheck',
        'desc' => esc_html__( 'Select menu items to remove.', 'wps' ),
        'options' => $adminbar_items,
        );
  }

  //Admin Options
  $panel_fields[] = array(
      'name' => esc_html__( 'Admin Page Options', 'wps' ),
      'type' => 'openTab'
  );

  $panel_fields[] = array(
      'name' => esc_html__( 'Disable admin theme', 'wps' ),
      'id' => 'disable_admin_theme',
      'type' => 'checkbox',
      'default' => false,
      'desc' => esc_html__( 'Select to disable custom admin theme.', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Page background color', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Background color', 'wps' ),
      'id' => 'bg_color',
      'type' => 'wpcolor',
      'default' => '#e3e7ea',
      );

  $panel_fields[] = array(
      'id' => 'primary_btn_colors',
      'type' => 'fieldsetwrapstart',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Primary button colors', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Background  color', 'wps' ),
      'id' => 'pry_button_color',
      'type' => 'wpcolor',
      'default' => '#7ac600',
      );

if(isset($wps_options['design_type']) && $wps_options['design_type'] == 2) {
  $panel_fields[] = array(
      'name' => esc_html__( 'Border color', 'wps' ),
      'id' => 'pry_button_border_color',
      'type' => 'wpcolor',
      'default' => '#86b520',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Shadow color', 'wps' ),
      'id' => 'pry_button_shadow_color',
      'type' => 'wpcolor',
      'default' => '#98ce23',
      );
}

  $panel_fields[] = array(
      'name' => esc_html__( 'Text color', 'wps' ),
      'id' => 'pry_button_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Hover background color', 'wps' ),
      'id' => 'pry_button_hover_color',
      'type' => 'wpcolor',
      'default' => '#29ac39',
      );

if(isset($wps_options['design_type']) && $wps_options['design_type'] == 2) {
  $panel_fields[] = array(
      'name' => esc_html__( 'Hover border color', 'wps' ),
      'id' => 'pry_button_hover_border_color',
      'type' => 'wpcolor',
      'default' => '#259633',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Hover shadow color', 'wps' ),
      'id' => 'pry_button_hover_shadow_color',
      'type' => 'wpcolor',
      'default' => '#3d7a0c',
      );
      }

  $panel_fields[] = array(
      'name' => esc_html__( 'Hover text color', 'wps' ),
      'id' => 'pry_button_hover_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'id' => 'primary_btn_colors',
      'type' => 'fieldsetwrapclose',
      );

  $panel_fields[] = array(
      'id' => 'secondary_btn_colors',
      'type' => 'fieldsetwrapstart',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Secondary button colors', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Background color', 'wps' ),
      'id' => 'sec_button_color',
      'type' => 'wpcolor',
      'default' => '#ced6c9',
      );

  if(isset($wps_options['design_type']) && $wps_options['design_type'] == 2) {
  $panel_fields[] = array(
      'name' => esc_html__( 'Border color', 'wps' ),
      'id' => 'sec_button_border_color',
      'type' => 'wpcolor',
      'default' => '#bdc4b8',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Shadow color', 'wps' ),
      'id' => 'sec_button_shadow_color',
      'type' => 'wpcolor',
      'default' => '#dde5d7',
      );
  }

  $panel_fields[] = array(
      'name' => esc_html__( 'Text color', 'wps' ),
      'id' => 'sec_button_text_color',
      'type' => 'wpcolor',
      'default' => '#7a7a7a',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Hover background color', 'wps' ),
      'id' => 'sec_button_hover_color',
      'type' => 'wpcolor',
      'default' => '#c9c8bf',
      );

  if(isset($wps_options['design_type']) && $wps_options['design_type'] == 2) {
  $panel_fields[] = array(
      'name' => esc_html__( 'Hover border color', 'wps' ),
      'id' => 'sec_button_hover_border_color',
      'type' => 'wpcolor',
      'default' => '#babab0',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Hover shadow color', 'wps' ),
      'id' => 'sec_button_hover_shadow_color',
      'type' => 'wpcolor',
      'default' => '#9ea59b',
      );
      }

  $panel_fields[] = array(
      'name' => esc_html__( 'Hover text color', 'wps' ),
      'id' => 'sec_button_hover_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'id' => 'secondary_btn_colors',
      'type' => 'fieldsetwrapclose',
      );

  $panel_fields[] = array(
      'id' => 'new_btn_colors',
      'type' => 'fieldsetwrapstart',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Add New button', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Background color', 'wps' ),
      'id' => 'addbtn_bg_color',
      'type' => 'wpcolor',
      'default' => '#53D860',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Hover background color', 'wps' ),
      'id' => 'addbtn_hover_bg_color',
      'type' => 'wpcolor',
      'default' => '#5AC565',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Text color', 'wps' ),
      'id' => 'addbtn_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Hover text color', 'wps' ),
      'id' => 'addbtn_hover_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'id' => 'new_btn_colors',
      'type' => 'fieldsetwrapclose',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Metabox Colors', 'wps' ),
      'type' => 'title',
  );

  $panel_fields[] = array(
      'name' => esc_html__( 'Metabox header box', 'wps' ),
      'id' => 'metabox_h3_color',
      'type' => 'wpcolor',
      'default' => '#bdbdbd',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Metabox header Click button color', 'wps' ),
      'id' => 'metabox_handle_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Metabox header Click button hover color', 'wps' ),
      'id' => 'metabox_handle_hover_color',
      'type' => 'wpcolor',
      'default' => '#949494',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Metabox header text color', 'wps' ),
      'id' => 'metabox_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Message box (Post/Page updates)', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Message box color', 'wps' ),
      'id' => 'msg_box_color',
      'type' => 'wpcolor',
      'default' => '#02c5cc',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Message text color', 'wps' ),
      'id' => 'msgbox_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Message box border color', 'wps' ),
      'id' => 'msgbox_border_color',
      'type' => 'wpcolor',
      'default' => '#007e87',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Message link color', 'wps' ),
      'id' => 'msgbox_link_color',
      'type' => 'wpcolor',
      'default' => '#efefef',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Message link hover color', 'wps' ),
      'id' => 'msgbox_link_hover_color',
      'type' => 'wpcolor',
      'default' => '#e5e5e5',
      );

    $panel_fields[] = array(
    'name' => esc_html__( 'Notice box color', 'wps' ),
    'id' => 'notice_box_color',
    'type' => 'wpcolor',
    'default' => '#e3bbbb',
    );

$panel_fields[] = array(
    'name' => esc_html__( 'Notice text color', 'wps' ),
    'id' => 'notice_text_color',
    'type' => 'wpcolor',
    'default' => '#f00033',
    );

$panel_fields[] = array(
    'name' => esc_html__( 'Notice link color', 'wps' ),
    'id' => 'notice_link_color',
    'type' => 'wpcolor',
    'default' => '#4290ff',
    );

$panel_fields[] = array(
    'name' => esc_html__( 'Notice link hover color', 'wps' ),
    'id' => 'notice_link_hover_color',
    'type' => 'wpcolor',
    'default' => '#28416e',
    );

  $panel_fields[] = array(
      'name' => esc_html__( 'Custom CSS', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Custom CSS for Admin pages', 'wps' ),
      'id' => 'admin_page_custom_css',
      'type' => 'textarea',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Custom CSS for Admin pages for non-privilege users', 'wps' ),
      'id' => 'admin_page_custom_css_non_privilege',
      'type' => 'textarea',
      );

  //Admin menu Options
  $panel_fields[] = array(
      'name' => esc_html__( 'Admin menu Options', 'wps' ),
      'type' => 'openTab'
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Admin menu width', 'wps' ),
      'id' => 'admin_menu_width',
      'type' => 'number',
      'default' => '200',
      'min' => '160',
      'max' => '400',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Parent menu vertical padding', 'wps' ),
      'id' => 'admin_par_menu_v_padding',
      'type' => 'number',
      'default' => '3',
      'min' => '0',
      'max' => '10',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Submenu vertical padding', 'wps' ),
      'id' => 'admin_submenu_v_padding',
      'type' => 'number',
      'default' => '10',
      'min' => '5',
      'max' => '15',
      );

    $panel_fields[] = array(
        'name' => esc_html__( 'Enable admin menu shadow.', 'wps' ),
        'id' => 'enable_admin_menu_shadow',
        'type' => 'checkbox',
        'desc' => esc_html__( 'Select to enable admin menu shadow.', 'wps' ),
        'default' => false,
        );
    $panel_fields[] = array(
        'name' => esc_html__( 'Admin menu shadow opacity', 'wps' ),
        'id' => 'admin_menu_shadow_opacity',
        'type' => 'number',
        'default' => '3',
        'min' => '0',
        'max' => '10',
        );

  $panel_fields[] = array(
      'name' => esc_html__( 'Menu font size', 'wps' ),
      'id' => 'admin_menu_font_size',
      'type' => 'radio',
      'options' => array(
          'small' => esc_html__( 'Small', 'wps' ),
          'default' => esc_html__( 'Default', 'wps' ),
        ),
      'default' => 'small',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Admin Menu Color options', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Left menu wrap color', 'wps' ),
      'id' => 'nav_wrap_color',
      'type' => 'wpcolor',
      'default' => '#1b2831',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Menu hover color', 'wps' ),
      'id' => 'hover_menu_color',
      'type' => 'wpcolor',
      'default' => '#3f4457',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Menu text color', 'wps' ),
      'id' => 'nav_text_color',
      'type' => 'wpcolor',
      'default' => '#90a1a8',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Menu hover text color', 'wps' ),
      'id' => 'menu_hover_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Current active Menu color', 'wps' ),
      'id' => 'active_menu_color',
      'type' => 'wpcolor',
      'default' => '#6da87a',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Active Menu text color', 'wps' ),
      'id' => 'menu_active_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Submenu wrap color', 'wps' ),
      'id' => 'sub_nav_wrap_color',
      'type' => 'wpcolor',
      'default' => '#22303a',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Submenu hover color', 'wps' ),
      'id' => 'sub_nav_hover_color',
      'type' => 'wpcolor',
      'default' => '#22303a',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Submenu text color', 'wps' ),
      'id' => 'sub_nav_text_color',
      'type' => 'wpcolor',
      'default' => '#17b7b2',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Submenu hover text color', 'wps' ),
      'id' => 'sub_nav_hover_text_color',
      'type' => 'wpcolor',
      'default' => '#17b7b2',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Active submenu text color', 'wps' ),
      'id' => 'submenu_active_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Updates Count notification background', 'wps' ),
      'id' => 'menu_updates_count_bg',
      'type' => 'wpcolor',
      'default' => '#212121',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Updates Count text color', 'wps' ),
      'id' => 'menu_updates_count_text',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  //user info
  $panel_fields[] = array(
      'name' => esc_html__( 'User Info', 'wps' ),
      'type' => 'openTab'
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Enable user info on admin menu', 'wps' ),
      'id' => 'enable_menu_user_info',
      'type' => 'checkbox',
      'default' => 1,
      );

  $default_userinfo_icon_color = (!empty($wps_options['nav_text_color'])) ? $wps_options['nav_text_color'] : '#ffffff';
  $default_userinfo_icon_border_color = (!empty($wps_options['nav_text_color'])) ? $wps_options['nav_text_color'] : '#3d3d3d';
  $default_user_info_icon_hover_bg_color = (!empty($wps_options['hover_menu_color'])) ? $wps_options['hover_menu_color'] : '#3d3d3d';
  $default_user_info_icon_hover_color = (!empty($wps_options['menu_hover_text_color'])) ? $wps_options['menu_hover_text_color'] : '#ffffff';

  $panel_fields[] = array(
      'name' => esc_html__( 'User Text color', 'wps' ),
      'id' => 'user_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'User info bottom border color', 'wps' ),
      'id' => 'user_info_btm_border_color',
      'type' => 'wpcolor',
      'default' => '#0e1419',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Icon color', 'wps' ),
      'id' => 'user_info_icon_color',
      'type' => 'wpcolor',
      'default' => $default_userinfo_icon_color,
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Border color', 'wps' ),
      'id' => 'user_info_icon_border_color',
      'type' => 'wpcolor',
      'default' => $default_userinfo_icon_border_color,
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Icon hover background color', 'wps' ),
      'id' => 'user_info_icon_hover_bg_color',
      'type' => 'wpcolor',
      'default' => $default_user_info_icon_hover_bg_color,
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Icon hover color', 'wps' ),
      'id' => 'user_info_icon_hover_color',
      'type' => 'wpcolor',
      'default' => $default_user_info_icon_hover_color,
      );


  //Footer Options
  $panel_fields[] = array(
      'name' => esc_html__( 'Footer Options', 'wps' ),
      'type' => 'openTab'
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Footer Text', 'wps' ),
      'id' => 'admin_footer_txt',
      'type' => 'wpeditor',
      'desc' => esc_html__( 'Put any text you want to show on admin footer.', 'wps' ),
      );


  //Email Options
  $panel_fields[] = array(
      'name' => esc_html__( 'Email Options', 'wps' ),
      'type' => 'openTab'
  );

  $panel_fields[] = array(
      'name' => esc_html__( 'White Label emails', 'wps' ),
      'id' => 'email_settings',
      'options' => array(
          '3' => esc_html__( 'Disable White Label emails', 'wps' ),
          '1' => sprintf( esc_html__( 'Set Email address as <strong> %1$s </strong> From name as <strong> %2$s </strong>', 'wps' ), $blog_email, $blog_from_name ),
          '2' => esc_html__( 'Set different', 'wps' ),
      ),
      'type' => 'radio',
      'default' => '1',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Email From address', 'wps' ),
      'id' => 'email_from_addr',
      'type' => 'text',
      'desc' => esc_html__( 'Enter valid email address', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Email From name', 'wps' ),
      'id' => 'email_from_name',
      'type' => 'text',
      );

  //Privilege feature
  $panel_fields[] = array(
      'name' => esc_html__( 'Set Privilege users', 'wps' ),
      'type' => 'openTab',
      );

  $panel_fields[] = array(
      'type' => 'note',
      'desc' => sprintf( '<a target="_blank" href="%s" class="aof-quickvideo-btn">' . esc_html__( 'Quick Video Help', 'wps' ) . '</a>',  'https://youtu.be/I_yMhCbWXqQ' ) . '<br />',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Select Privilege users', 'wps' ),
      'id' => 'privilege_users',
      'type' => 'multicheck',
      'desc' => esc_html__( 'Select admin users who can have access to all menu items. Note: Atleast one user must be selected in order to activate Privilege feature.', 'wps' ),
      'options' => $admin_users_array,
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Widget Customization options', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
          'name' => esc_html__( 'Widgets display', 'wps' ),
          'id' => 'show_all_widgets_to_admin',
          'type' => 'radio',
      'options' => array(
          '1' => esc_html__( 'Show all widgets to all admin users', 'wps' ),
          '2' => esc_html__( 'Show all widgets to specific admin users', 'wps' ),
          '3' => esc_html__( 'Hide selected widgets to specific admin users also', 'wps' ),
      ),
      'default' => '3',
  );

  $panel_fields[] = array(
      'name' => esc_html__( 'Menu Customization options (Left admin menus and admin bar menus)', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Menu display', 'wps' ),
      'id' => 'show_all_menu_to_admin',
      'type' => 'radio',
      'options' => array(
          '1' => esc_html__( 'Show all Menu links to all admin users', 'wps' ),
          '2' => esc_html__( 'Show all Menu links to specific admin users', 'wps' ),
      ),
      'default' => '2',
      'desc' => esc_html__( 'Option - "Hide selected Menu links to specific admin users also has been deprecated".', 'wps' ),
  );

  $panel_fields[] = array(
      'name' => esc_html__( 'Screen meta links and Admin notices', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
          'name' => esc_html__( 'Screen meta links and Admin notices', 'wps' ),
          'id' => 'show_screen_meta_to_admin',
          'type' => 'radio',
      'options' => array(
          '1' => esc_html__( 'Show screen meta and admin notices to all admin users', 'wps' ),
          '2' => esc_html__( 'Show screen meta and admin notices to privilege users', 'wps' ),
          '3' => esc_html__( 'Hide screen meta and admin notices to privilege users also', 'wps' ),
      ),
      'default' => '3',
  );

if(defined('POWERBOX_PATH')) {
  $panel_fields[] = array(
      'name' => esc_html__( 'Hide plugins options', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
          'name' => esc_html__( 'Plugins list display', 'wps' ),
          'id' => 'show_all_plugins_list_to_admin',
          'type' => 'radio',
      'options' => array(
          '1' => esc_html__( 'Show all plugins list to all admin users', 'wps' ),
          '2' => esc_html__( 'Show all plugins list to specific admin users', 'wps' ),
          '3' => esc_html__( 'Hide selected plugins to specific admin users also', 'wps' ),
      ),
      'default' => '3',
  );

  $panel_fields[] = array(
          'name' => esc_html__( 'Users list display', 'wps' ),
          'id' => 'show_all_users_list_to_admin',
          'type' => 'radio',
      'options' => array(
          '1' => esc_html__( 'Show all users list to all admin users', 'wps' ),
          '2' => esc_html__( 'Show all users list to specific admin users', 'wps' ),
          '3' => esc_html__( 'Hide selected users to specific admin users also', 'wps' ),
      ),
      'default' => '3',
  );
}

    //admin notices
    $panel_fields[] = array(
        'name' => esc_html__( 'Admin notices', 'wps' ),
        'type' => 'openTab'
    );

    $panel_fields[] = array(
        'name' => esc_html__( 'Show admin notices for', 'wps' ),
        'id' => 'show_admin_notices_for',
        'type' => 'radio',
        'options' => array(
            '1' => esc_html__( 'Show admin notices for all users', 'wps' ),
            '2' => esc_html__( 'Show admin notices for all admin users', 'wps' ),
            '3' => esc_html__( 'Show admin notices for specific admin users', 'wps' ),
            '4' => esc_html__( 'Hide admin notices for specific admin users also', 'wps' ),
        ),
        'default' => '4',
    );

    $panel_fields[] = array(
        'name' => esc_html__( 'Powerbox Settings', 'wps' ),
        'type' => 'openTab'
    );

    $panel_fields[] = array(
        'name' => esc_html__( 'Powerbox restrictions', 'wps' ),
        'type' => 'title',
    );

    $panel_fields[] = array(
        'name' => esc_html__( 'Enable url restrictions', 'wps' ),
        'id' => 'wpspb_url_restriction',
        'type' => 'checkbox',
        'desc' => esc_html__( 'Restrict urls other than allowed menu item urls.', 'wps' ),
        'default' => false,
        );

    $panel_fields[] = array(
        'name' => esc_html__( 'Enable admin bar', 'wps' ),
        'id' => 'wpspb_enable_adminbar',
        'type' => 'checkbox',
        'desc' => esc_html__( 'Show WP Adminbar to Powerbox menu users.', 'wps' ),
        'default' => false,
        );

    $panel_fields[] = array(
        'name' => esc_html__( 'Client Sidebar Menu Settings', 'wps' ),
        'type' => 'title',
    );

    $panel_fields[] = array(
        'name' => esc_html__( 'Client sidebar menu width', 'wps' ),
        'id' => 'wpspb_sidebar_menu_width',
        'type' => 'number',
        'default' => '250',
        'min' => '160',
        'max' => '400',
        );

    $panel_fields[] = array(
        'name' => esc_html__( 'External Logo url', 'wps' ),
        'id' => 'wpspb_ext_logo_url',
        'type' => 'text',
        'desc' => esc_html__( 'Load image from external source.', 'wps' ),
    );

    $panel_fields[] = array(
        'name' => esc_html__( 'Upload Logo', 'wps' ),
        'id' => 'wpspb_sidebar_logo',
        'type' => 'upload',
        'desc' => esc_html__( 'Logo to display in admin sidebar.', 'wps' ),
    );

    $panel_fields[] = array(
        'name' => esc_html__( 'External Logo url for collapsed menu.', 'wps' ),
        'id' => 'wpspb_ext_logo_url_collapsed',
        'type' => 'text',
        'desc' => esc_html__( 'Load image from external source.', 'wps' ),
    );

    $panel_fields[] = array(
        'name' => esc_html__( 'Upload Logo for collapsed menu.', 'wps' ),
        'id' => 'wpspb_sidebar_logo_collapsed',
        'type' => 'upload',
        'desc' => esc_html__( 'Maximum size is 65 x 65px.', 'wps' ),
    );

    $panel_fields[] = array(
        'name' => esc_html__( 'Logo link', 'wps' ),
        'id' => 'wpspb_sidebar_logo_link',
        'type' => 'text',
        'desc' => esc_html__( 'If empty it will default to admin dashboard url.', 'wps' ),
    );

    $panel_fields[] = array(
        'name' => esc_html__( 'Enable admin menu shadow.', 'wps' ),
        'id' => 'wpspb_sidebar_shadow',
        'type' => 'checkbox',
        'desc' => esc_html__( 'Select to enable admin menu shadow.', 'wps' ),
        'default' => false,
        );
    $panel_fields[] = array(
        'name' => esc_html__( 'Admin menu shadow opacity', 'wps' ),
        'id' => 'wpspb_sidebar_shadow_opacity',
        'type' => 'number',
        'default' => '3',
        'min' => '0',
        'max' => '10',
        );

  $panel_fields[] = array(
      'name' => esc_html__( 'Sidebar Color options', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Sidebar wrap color', 'wps' ),
      'id' => 'wpspb_sidebar_wrap_color',
      'type' => 'wpcolor',
      'default' => '#1b2831',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Sidebar header color', 'wps' ),
      'id' => 'wpspb_sidebar_header_bg',
      'type' => 'wpcolor',
      'default' => '#0f161b',
      'desc' => esc_html__( 'Set header background color', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Hamburger color', 'wps' ),
      'id' => 'wpspb_sidebar_hamb_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      'desc' => esc_html__( 'Set hamburger button color.', 'wps' ),
  );

  $panel_fields[] = array(
      'name' => esc_html__( 'Mobile Hamburger color', 'wps' ),
      'id' => 'wpspb_mob_hamburger_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      'desc' => esc_html__( 'Set mobile hamburger button color.', 'wps' ),
  );

  $panel_fields[] = array(
      'name' => esc_html__( 'Menu separator color', 'wps' ),
      'id' => 'wpspb_sidebar_sep_color',
      'type' => 'wpcolor',
      'default' => '#32343a',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Menu text color', 'wps' ),
      'id' => 'wpspb_sidebar_menu_color',
      'type' => 'wpcolor',
      'default' => '#6c727f',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Menu hover text color', 'wps' ),
      'id' => 'wpspb_sidebar_hover_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Current active menu color', 'wps' ),
      'id' => 'wpspb_sidebar_active_menu_color',
      'type' => 'wpcolor',
      'default' => '#507ee4',
      'desc' => esc_html__( 'Set active menu background color', 'wps' ),
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Active Menu text color', 'wps' ),
      'id' => 'wpspb_sidebar_active_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Submenu wrap color', 'wps' ),
      'id' => 'wpspb_sidebar_subnav_wrap_color',
      'type' => 'wpcolor',
      'default' => '#22303a',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Submenu text color', 'wps' ),
      'id' => 'wpspb_sidebar_subnav_text_color',
      'type' => 'wpcolor',
      'default' => '#6c727f',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Submenu hover text color', 'wps' ),
      'id' => 'wpspb_sidebar_subnav_hover_text_color',
      'type' => 'wpcolor',
      'default' => '#ffffff',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'User info', 'wps' ),
      'type' => 'title',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'User info text color', 'wps' ),
      'id' => 'wpspb_userinfo_text_color',
      'type' => 'wpcolor',
      'default' => '#b1a1a1',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'User info bottom border color', 'wps' ),
      'id' => 'wpspb_userinfo_border_color',
      'type' => 'wpcolor',
      'default' => '#0e1419',
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Icon color', 'wps' ),
      'id' => 'wpspb_user_info_icon_color',
      'type' => 'wpcolor',
      'default' => $default_userinfo_icon_color,
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Border color', 'wps' ),
      'id' => 'wpspb_userinfo_icon_border_color',
      'type' => 'wpcolor',
      'default' => $default_userinfo_icon_border_color,
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Icon hover background color', 'wps' ),
      'id' => 'wpspb_userinfo_icon_hover_bg_color',
      'type' => 'wpcolor',
      'default' => $default_user_info_icon_hover_bg_color,
      );

  $panel_fields[] = array(
      'name' => esc_html__( 'Icon hover color', 'wps' ),
      'id' => 'wpspb_userinfo_icon_hover_color',
      'type' => 'wpcolor',
      'default' => $default_user_info_icon_hover_color,
      );


  $panel_fields = apply_filters( 'aof_panel_fields', $panel_fields );

  $output = array('wps_tabs' => $panel_tabs, 'wps_fields' => $panel_fields);
  return $output;
}

/**
* Options for WPSPowerbox sidebar menu
* @since 6.1.2
*/
add_action( 'admin_menu', 'wpspb_aof_fields', 99 );
function wpspb_aof_fields() {

  $wpspb_db_version = get_option('wps_menu_db_version');

  if( defined('POWERBOX_VERSION') && !empty( $wpspb_db_version ) ) {

      add_filter( 'aof_tabs_list', 'wpspb_tabs' );
      function wpspb_tabs( $panel_tabs ) {
        $wpspb_tabs = array(
          'wpspb_client_sidebar' => esc_html__( 'Powerbox Sidebar Menu', 'wps' ),
        );
        return array_merge ( $panel_tabs, $wpspb_tabs );
      }

    }

}
