<?php
/*
 * WPSHAPERE
 * @author   AcmeeDesign
 * @url     http://acmeedesign.com
*/

defined('ABSPATH') || die;

if (!class_exists('WPS_ADMIN_NOTICES_CLASS')) {

    class WPS_ADMIN_NOTICES_CLASS extends WPSHAPERE
    {
        public $aof_options;

        function __construct()
        {
            $this->aof_options = parent::get_wps_option_data(WPSHAPERE_OPTIONS_SLUG);
            add_action('admin_print_scripts', array($this, 'wps_get_admin_notices'), 999);
        }

        function wps_get_admin_notices() {

          global $wp_filter;

          $current_user_role = parent::wps_get_user_role();
          $current_user_id = get_current_user_id();
          $show_admin_notices_for = $this->aof_options['show_admin_notices_for'];
          $wps_privilege_users = (!empty($this->aof_options['privilege_users'])) ? $this->aof_options['privilege_users'] : array();

          if(isset($show_admin_notices_for) && $show_admin_notices_for == 1)
            return;

          if(is_super_admin($current_user_id)) {
            if(isset($show_admin_notices_for) && $show_admin_notices_for == 4) {
              $this->wps_hide_admin_notices();
            }
            elseif(isset($show_admin_notices_for) && $show_admin_notices_for == 2) {
              return;
            }
            elseif(isset($show_admin_notices_for) && $show_admin_notices_for == 3 && !empty($wps_privilege_users) && !in_array($current_user_id, $wps_privilege_users)) {
              $this->wps_hide_admin_notices();
            }

          }
          else {
            $this->wps_hide_admin_notices();
          }

        }

        function wps_hide_admin_notices() {

          global $wp_filter;

          if ( is_user_admin() ) {
            if ( isset( $wp_filter['user_admin_notices'] ) ) {
                unset( $wp_filter['user_admin_notices'] );
            }
          } elseif ( isset( $wp_filter['admin_notices'] ) ) {
              unset( $wp_filter['admin_notices'] );
          }
          if ( isset( $wp_filter['all_admin_notices'] ) ) {
              unset( $wp_filter['all_admin_notices'] );
          }

        }

    }

}

new WPS_ADMIN_NOTICES_CLASS();
