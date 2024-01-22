<?php
/*
 * WPSHAPERE
 * @author   AcmeeDesign
 * @url     http://acmeedesign.com
*/

defined('ABSPATH') || die;

if(!class_exists('WPS_DEACTIVATELICENSE')) {
    class WPS_DEACTIVATELICENSE extends WPSHAPERE {

        function __construct()
        {
            $this->aof_options = parent::get_wps_option_data(WPSHAPERE_OPTIONS_SLUG);
            add_action('admin_menu', array($this, 'add_remove_license_menu'));
            add_action('plugins_loaded', array($this, 'remove_license'));
        }

        function add_remove_license_menu()
        {
          $purchase_data = parent::get_wps_option_data( 'wps_purchase_data' );
          if( !empty( $purchase_data ) && is_array( $purchase_data ) ) {
            add_submenu_page( WPSHAPERE_MENU_SLUG , esc_html__('Deactivate License for this site', 'wps'), esc_html__('Deactivate License', 'wps'), 'manage_options', 'wps_deactivate_license', array($this, 'wps_deactivate_license_form') );
          }
        }

        function wps_deactivate_license_form() {
          ?>
          <div class="wrap wps-wrap">
            <div class="wps-help-content-wrap wps-new-content-wrap">
              <h1><?php echo esc_html__('Deactivate License for this site', 'wps'); ?></h1>
              <?php parent::wps_help_link(); ?>
              <form name="wps_deactiate_license" method="post">
                <br />
                <?php
                $purchase_data = parent::get_wps_option_data( 'wps_purchase_data' );

                if( !empty( $purchase_data ) && is_array( $purchase_data ) ) {
                  $purchase_key = implode( '-', $purchase_data[3] );
                ?>
                <h3><?php echo esc_html__('Registered to envato user:', 'wps'); ?> <?php echo esc_html( $purchase_data[0] ); ?></h3>
                <h4><em><?php echo esc_html__('Purchase key:', 'wps'); ?></em> <?php echo esc_html( $purchase_key ); ?></h4>
                <?php } ?>
                <p><label for="wps_remove_license"><input type="checkbox" name="remove_license" value="1" /> <?php echo esc_html__('Remove license key for this site.', 'wps'); ?></label></p>
                <input type="submit" name="submit" class="button button-primary button-hero" value="Deactivate License" />
              </form>
            </div>
          </div>
          <?php
        }

        function remove_license() {
          if(isset($_POST['remove_license']) && $_POST['remove_license'] == 1) {
              delete_option( 'wps_purchase_data' );
              wp_safe_redirect( admin_url( 'admin.php?page=' . WPSHAPERE_MENU_SLUG ) );
              exit();
          }
        }

    }
}new WPS_DEACTIVATELICENSE();
