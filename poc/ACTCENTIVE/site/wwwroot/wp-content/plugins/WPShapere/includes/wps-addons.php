<?php
/*
 * WPSHAPERE
 * @author   AcmeeDesign
 * @url     http://acmeedesign.com
*/

defined('ABSPATH') || die;

add_action('admin_menu', 'wps_show_addons_menu');

function wps_show_addons_menu()
{
  /**
  * if WPSPowerbox plugin is active return
  **/
  if(defined('POWERBOX_PATH'))
    return;

  add_submenu_page( WPSHAPERE_MENU_SLUG , esc_html__('WPShapere Premium Addons', 'wps'), esc_html__('Premium Addons', 'wps'), 'manage_options', 'wps_addons_adv', 'wps_addons_adv_page' );
}

function wps_addons_adv_page () {
  global $aof_options;
  ?>
  <div class="wrap wps-wrap">

    <div class="addons-heading wps-new-page-heading">
      <h1>WPSPowerbox - Extend the Power of WPShapere <span>With great set of addons feature in a single addon plugin.</span></h1>
    </div>

    <div class="addons-content-wrap wps-new-content-wrap">

      <a target="_blank" class="addons-action-btn wps-addon-review-link" href="https://codecanyon.net/item/wpspowerbox-addon-for-wpshapere-wordpress-admin-theme/22169580">
        <?php echo esc_html__('Review Plugin', 'wps') ?>
      </a>
      <a target="_blank" class="addons-action-btn wps-addon-purchase-link" href="https://codecanyon.net/cart/configure_before_adding/22169580?license=regular&size=source&support=bundle_12month&utm_source=wpshapereplugin">
        <?php echo esc_html__('Purchase Now', 'wps') ?>
      </a>

      <img src="<?php echo WPSHAPERE_DIR_URI ?>assets/images/wps-powerbox-promo.jpg" alt="WPSPowerbox addon for WPShapere" />

    </div>

  </div>
  <?php
}
