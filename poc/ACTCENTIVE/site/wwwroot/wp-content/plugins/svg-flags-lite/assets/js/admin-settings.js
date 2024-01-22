// JavaScript for the plugin admin settings page
jQuery(document).ready(function ($) {
  
  //console.log('admin settings: svg flags', svg_flags_admin_data);
  wpgoplugins_admin_settings_fw.move_about_page_tab($, svg_flags_admin_data);
  wpgoplugins_admin_settings_fw.add_numbered_icon_to_tab_label($, svg_flags_admin_menu_data);
  wpgoplugins_admin_settings_fw.collapsible_settings_section($, svg_flags_admin_data);
});