<?php
/*
 * WPShapere
 * @author   AcmeeDesign
 * @url     http://acmeedesign.com
*/

defined('ABSPATH') || die;

if (!class_exists('WPS_IMP_EXP')) {

    class WPS_IMP_EXP extends WPSHAPERE
    {
        public $aof_options;

        function __construct()
        {
            $this->aof_options = parent::get_wps_option_data(WPSHAPERE_OPTIONS_SLUG);
            add_action('admin_menu', array($this, 'add_impexp_menu'));
            add_action('plugins_loaded',array($this, 'wps_settings_action'));
        }

        function add_impexp_menu() {
            add_submenu_page( WPSHAPERE_MENU_SLUG, esc_html__('Import and Export Settings', 'wps'), esc_html__('Import-Export Settings', 'wps'), 'manage_options', 'wps_impexp_settings', array($this, 'wps_impexp_settings_page') );
        }

        function wps_impexp_settings_page() {
            global $aof_options;
            ?>
            <div class="wrap wps-wrap">
              <h1><?php echo esc_html__('Import/Export Settings', 'wps'); ?></h1>
              <?php parent::wps_help_link(); ?>
        <?php
            if(isset($_GET['page']) && $_GET['page'] == 'wps_impexp_settings' && isset($_GET['status']) && $_GET['status'] == 'updated')
            {
                ?>
                <div class="updated top">
                    <p><strong><?php echo esc_html__('Settings Imported!', 'wps'); ?></strong></p>
                </div>
        <?php
            }
            elseif(isset($_GET['page']) && $_GET['page'] == 'wps_impexp_settings' && isset($_GET['status']) && $_GET['status'] == 'dataerror')
            {
                ?>
                <div class="updated top">
                    <p><strong><?php echo esc_html__('You are importing empty data or wrong data format.', 'wps'); ?></strong></p>
                </div>
        <?php
            }

            ?>
                <h3><?php echo esc_html__('Reset to default', 'wps'); ?></h3>
                <span><?php echo esc_html__('By resetting all settings will be deleted!', 'wps'); ?></span>
                <div style="padding: 15px 0">
                    <form name="wps_master_reset_form" method="post" onsubmit="return confirm('Do you really want to Reset?');">
                    <input type="hidden" name="reset_to_default" value="wps_master_reset" />
                    <?php wp_nonce_field('wps_reset_nonce','wps_reset_field'); ?>
                    <input id="wps-button-reset" class="button button-primary" type="submit" value="<?php echo esc_html__('Reset All Settings', 'wps'); ?>" />
                    </form>
                </div>

                <h3><?php echo esc_html__('Export Settings', 'wps'); ?></h3>
                <div style="padding: 15px 0">
                <span><?php echo esc_html__('Save the below contents to a text file.', 'wps'); ?></span>
                <textarea class="widefat" rows="10" ><?php echo $this->wps_get_settings(); ?></textarea>
                </div>

                <h3><?php echo esc_html__('Import Settings', 'wps'); ?></h3>
                <div style="padding:15px 0">
                <form name="wps_import_settings_form" method="post" action="">
                        <input type="hidden" name="wps_import_settings" value="1" />
                        <textarea class="widefat" name="wps_import_settings_data" rows="10" ></textarea><br /><br />
                        <input class="button button-primary button-hero" type="submit" value="<?php echo esc_html__('Import Settings', 'wps'); ?>" />
                <?php wp_nonce_field('wps_import_settings_nonce','wps_import_settings_field'); ?>
                </form>
                </div>

            </div>

<?php
        }

        function wps_settings_action() {
            if(isset($_POST['wps_import_settings_field']) ) {
                if(!wp_verify_nonce( $_POST['wps_import_settings_field'], 'wps_import_settings_nonce' ) )
                    exit();
                $import_data = trim($_POST['wps_import_settings_data']);
                if(empty($import_data) || !is_serialized($import_data)) {
                    wp_safe_redirect( admin_url( 'admin.php?page=wps_impexp_settings&status=dataerror' ) );
                    exit();
                }
                else {
                    $data = unserialize($import_data); //to avoid double serialization
                    parent::updateOption(WPSHAPERE_OPTIONS_SLUG, $data);
                    wp_safe_redirect( admin_url( 'admin.php?page=wps_impexp_settings&status=updated' ) );
                    exit();
                }
            }

            if(isset($_POST['reset_to_default']) && $_POST['reset_to_default'] == "wps_master_reset") {
                if(!wp_verify_nonce( $_POST['wps_reset_field'], 'wps_reset_nonce' ) )
                        exit();

                global $aof_options;
                $aof_options->aofLoaddefault(true);
                wp_safe_redirect( admin_url( 'admin.php?page='.WPSHAPERE_MENU_SLUG ) );
                exit();
            }
        }

        function wps_get_settings() {
           $saved_data = parent::get_wps_option_data(WPSHAPERE_OPTIONS_SLUG);
           if(!empty($saved_data)) {
               if(!is_serialized($saved_data)) {
                   return maybe_serialize($saved_data);
               }
               else {
                   return $saved_data;
               }
           }
        }

    }

}

new WPS_IMP_EXP();
