<?php
/*
 * WPSHAPERE
 * @author   AcmeeDesign
 * @url     http://acmeedesign.com
*/

defined('ABSPATH') || die;

if (!class_exists('WPS_HELP')) {

    class WPS_HELP extends WPSHAPERE
    {
        function __construct()
        {
          add_action('admin_menu', array($this, 'add_wps_help_menu'));
        }

        function add_wps_help_menu()
        {
            add_submenu_page( WPSHAPERE_MENU_SLUG, esc_html__('Quick Support', 'wps'), esc_html__('Need Support?', 'wps'), 'manage_options', 'wpshapere_help', array($this, 'wps_help_resources') );
        }

        function wps_help_resources() {
          ?>
          <div class="clearfix wrap wps-wrap">
            <div class="wps-new-page-heading">
              <h1>WPSHAPERE <?php echo esc_html__('Help', 'wps'); ?>
                <span class="plugin_desc"><?php
                echo esc_html__('Congratulations! You have made the right choice of choosing', 'wps') . ' WPShapere. ' .
                esc_html__('You are about to use the most powerful white labelling solution for WordPress.', 'wps');
                ?></span>
              </h1>
            </div>

            <div class="wps-help-content-wrap wps-new-content-wrap">

              <h2><?php echo esc_html__('ONLINE RESOURCES', 'wps'); ?></h2>

              <div class="wps-cols-wrap">
                <div class="wps-cols wps-col-6">
                  <h2><?php echo esc_html__('Customization help', 'wps'); ?></h2>
                  <ul>
                    <li><a target="_blank" href="https://kb.acmeedesign.com/kbase/how-can-i-update-the-plugin/">How can I update the plugin?</a></li>
                    <li><a target="_blank" href="https://kb.acmeedesign.com/kbase/i-edited-my-admin-theme-and-dont-like-it-how-can-i-reset-it-to-its-original-state/">I edited my admin theme and don’t like it. How can I reset it to it’s original state?</a></li>
                      <li><a target="_blank" href="https://kb.acmeedesign.com/kbase/how-to-remove-wordpress-default-widgets/">How to remove WordPress default widgets</a></li>
                      <li><a target="_blank" href="https://kb.acmeedesign.com/kbase/customizing-wordpress-admin-menus-by-user-roles/">Customizing WordPress admin menus by user roles</a></li>
                      <li><a target="_blank" href="https://kb.acmeedesign.com/kbase/my-customizations-are-not-being-applied-or-look-different-than-expected/">My customizations are not being applied or look different than expected.</a></li>
                      <li><a target="_blank" href="https://kb.acmeedesign.com/kbase/exporting-and-importing-wpshapere-settings/">Exporting and Importing WPShapere settings</a></li>
                  </ul>
                </div>
                <div class="wps-cols wps-col-6">
                  <h2><?php echo esc_html__("How to's", 'wps'); ?></h2>
                  <ul>
                      <li><a target="_blank" href="https://kb.acmeedesign.com/kbase/im-building-a-site-for-a-client-and-dont-want-them-to-see-unnecessary-parts-of-the-admin-page-can-i-hide-certain-menu-options-such-as-the-themes-and-plugins-menus/">How to hide certain menu items from admin menu?</a></li>
                  		<li><a target="_blank" href="https://kb.acmeedesign.com/kbase/how-to-remove-or-add-new-menu-links-to-the-admin-bar/">How to add new menu items to admin bar?</a></li>
                  		<li><a target="_blank" href="https://kb.acmeedesign.com/kbase/can-i-use-shortcodes-in-the-custom-dashboard-widgets/">Is it possible to use shortcodes in custom dashboard widgets?</a></li>
                  		<li><a target="_blank" href="https://kb.acmeedesign.com/kbase/some-menu-icons-missing-out-after-plugin-activation/">Some menu icons missing out after plugin activation?</a></li>
                  		<li><a target="_blank" href="https://kb.acmeedesign.com/kbase/ive-hidden-some-menus-but-i-can-still-see-them/">I’ve hidden some menus, but I can still see them. why?</a></li>
                  		<li><a target="_blank" href="https://kb.acmeedesign.com/kbase/can-i-use-this-plugin-for-multiple-clients-or-projects/">Can I use this plugin for multiple clients or projects?</a></li>
                  </ul>
                </div>
              </div>

                <a target="_blank" href="https://kb.acmeedesign.com/kbase_categories/wpshapere/">
                  <?php echo esc_html__('View all articles', 'wps'); ?>
                </a>
                <br />
                <a class="wps-open-ticket" target="_blank" href="https://envato.acmeedesign.support">
                  <?php echo esc_html__("Can't find a solution? Open a support ticket", 'wps'); ?>
                </a>

                <div class="wps_system_details">
                  <h2><?php echo esc_html__('WordPress/Server details', 'wps'); ?></h2>

                  <div class="wps-env-detail-row">
                    <div class="wps-env-title">
                    WPShapere Version
                    </div>
                    <div class="wps-env-details">
                      <?php
                      if(defined('WPSHAPERE_VERSION'))
                        echo WPSHAPERE_VERSION;
                      ?>
                    </div>
                  </div>

                  <div class="wps-env-detail-row">
                    <div class="wps-env-title">
                    Home URL
                    </div>
                    <div class="wps-env-details">
                      <?php
                      if(is_multisite())
                        echo esc_url( network_home_url( '/' ) );
                      else
                        echo esc_url( home_url( '/' ) );
                      ?>
                    </div>
                  </div>

                  <div class="wps-env-detail-row">
                    <div class="wps-env-title">
                      Is Multi-site?
                    </div>
                    <div class="wps-env-details">
                      <?php
                      if(is_multisite())
                        echo 'Yes';
                      else
                        echo 'No';
                      ?>
                    </div>
                  </div>

                  <div class="wps-env-detail-row">
                    <div class="wps-env-title">
                      WP Version
                    </div>
                    <div class="wps-env-details">
                      <?php
                      echo get_bloginfo( 'version' );
                      ?>
                    </div>
                  </div>

                  <div class="wps-env-detail-row">
                    <div class="wps-env-title">
                      PHP Version
                    </div>
                    <div class="wps-env-details">
                      <?php
                      echo phpversion();
                      ?>
                    </div>
                  </div>

                  <div class="wps-env-detail-row">
                    <div class="wps-env-title">
                      POST MAX SIZE
                    </div>
                    <div class="wps-env-details">
                      <?php
                      $max_upload = min(ini_get('post_max_size'), ini_get('upload_max_filesize'));
                      echo esc_html($max_upload);
                      ?>
                    </div>
                  </div>

                  <div class="wps-env-detail-row">
                    <div class="wps-env-title">
                      cURL Version
                    </div>
                    <div class="wps-env-details">
                      <?php
                      $curl_version = curl_version();
                      echo esc_attr($curl_version['version']);
                      ?>
                    </div>
                  </div>

                  <div class="wps-env-detail-row">
                    <div class="wps-env-title">
                      Debug mode
                    </div>
                    <div class="wps-env-details">
                      <?php
                      if(WP_DEBUG) echo 'Debug mode enabled';
                        else echo 'Debug mode disabled';
                      ?>
                    </div>
                  </div>

                  <div class="wps-env-detail-row">
                    <div class="wps-env-title">
                      Active WP Theme
                    </div>
                    <div class="wps-env-details">
                      <?php
                      $wp_theme_details = wp_get_theme();
                      if(is_object($wp_theme_details)) {
                        echo esc_html( $wp_theme_details->get( 'Name' ) );
                      }
                      ?>
                    </div>
                  </div>

                </div>

            </div>
          </div>
          <?php
        }

    }

}

new WPS_HELP();
