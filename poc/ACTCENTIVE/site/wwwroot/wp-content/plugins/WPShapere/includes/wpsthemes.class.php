<?php
/**
 * @package WPShapere
 * @author   AcmeeDesign
 * @url     http://acmeedesign.com
 * defining css styles for WordPress admin pages.
 */

defined('ABSPATH') || die;

class WPS_THEMES extends WPSHAPERE{

            private $wps_options = 'wpshapere_options';

            function __construct()
            {
              $this->aof_options = parent::get_wps_option_data(WPSHAPERE_OPTIONS_SLUG);
              add_action('admin_menu', array($this, 'wps_themes_menu'));
              add_action('plugins_loaded',array($this, 'import_theme'));
            }

            function wps_themes_menu()
            {
              add_submenu_page( 'wpshapere-options', esc_html__('Admin Theme Presets', 'wps'), esc_html__('Admin Theme Presets', 'wps'), 'manage_options', 'wps_themes', array($this,'wps_theme_page') );
            }

            function wps_theme_page()
            {
                global $aof_options;
                $aof_options->licenseValidate();
                $wps_themes = $this->get_wps_themes();
                $active_admin_theme = (isset($this->aof_options['admin_theme_preset']) && !empty($this->aof_options['admin_theme_preset'])) ? $this->aof_options['admin_theme_preset'] : 'Default';
    ?>
    <div class="wrap wps-wrap">
            <h1><?php echo esc_html__('Import Admin Theme', 'wps'); ?></h1>
            <?php parent::wps_help_link(); ?>
            <p><?php echo esc_html__( 'Note: Importing a theme will replace your existing custom set colors.', 'wps') ?></p>
    <?php
    if(isset($_GET['status']) && $_GET['status'] == 'updated')
            echo '<div id="message" class="updated below-h2"><p>' . esc_html__( 'Theme Imported!', 'wps' ) . '</p></div>';
    ?>
        <form class="wps_set_admin_theme" name="wps_set_theme" method="post" action="">
          <div class="wps-admintheme-presets-wrap">
            <?php
                foreach ($wps_themes as $wps_theme_name => $wps_theme) {
                  $active_theme_class = ($wps_theme_name == $active_admin_theme) ? "wps-active-admin-theme" : "";
                  ?>
                    <div class="wps-admintheme-preset <?php echo $active_theme_class; ?>">
                      <div class="wps-admintheme-preset-space">
                        <div class="wps-palette-wrap">
                          <div class="wps-palette-color" style="background-color: <?php echo $wps_theme['admin_bar_color']; ?>"></div>
                          <div class="wps-palette-color" style="background-color: <?php echo $wps_theme['nav_wrap_color']; ?>"></div>
                          <div class="wps-palette-color" style="background-color: <?php echo $wps_theme['active_menu_color']; ?>"></div>
                          <div class="wps-palette-color" style="background-color: <?php echo $wps_theme['pry_button_color']; ?>"></div>
                          <div class="wps-palette-color" style="background-color: <?php echo $wps_theme['sec_button_color']; ?>;margin-right:0"></div>
                        </div>

                        <div class="wps-preset-select-btn">
                          <div class="wps-radio-group">
                            <input type="radio" name="wps_color_theme" value="<?php echo $wps_theme_name; ?>" <?php if($wps_theme_name == $active_admin_theme) echo 'checked="checked"'; ?> />
                            <label class="wps-radio" for="<?php echo $wps_theme_name; ?>"><?php echo $wps_theme_name; ?></label>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php
                }
                ?>
          </div>

                <input type="submit" class="button button-primary button-hero" name="submit" value="<?php echo esc_html__('Import Theme', 'wps'); ?>" />
                <?php wp_nonce_field('wps_import_theme_nonce','wps_import_theme_field'); ?>
        </form>
    </div>
    <?php
            }

            function import_theme()
            {
                if(isset($_POST['wps_import_theme_field']) ) {

                    if(!wp_verify_nonce( $_POST['wps_import_theme_field'], 'wps_import_theme_nonce' ) )
                            exit();

                    $wps_themes = $this->get_wps_themes();
                    $theme_name = sanitize_text_field($_POST['wps_color_theme']);
                    $wps_theme_preset = $wps_themes[$theme_name];

                    $selected_theme = array( 'admin_theme_preset' => $theme_name );
                    $import_data = array_merge($selected_theme, $wps_theme_preset);

                    $saved_data = parent::get_wps_option_data( WPSHAPERE_OPTIONS_SLUG );
                    if($saved_data) {
                        $data = array_merge($saved_data, $import_data);
                     }
                    else
                        $data = $import_data;
                    parent::updateOption(WPSHAPERE_OPTIONS_SLUG, $data);
                    wp_safe_redirect( admin_url( 'admin.php?page=wps_themes&status=updated' ) );
                    exit();
                }
            }

            function get_wps_themes()
            {
                $wps_themes = array();

                include_once WPSHAPERE_PATH . 'admin-theme-presets/default.php';
                include_once WPSHAPERE_PATH . 'admin-theme-presets/liquido.php';
                include_once WPSHAPERE_PATH . 'admin-theme-presets/sleek.php';
                include_once WPSHAPERE_PATH . 'admin-theme-presets/slate.php';
                include_once WPSHAPERE_PATH . 'admin-theme-presets/pomegranate.php';
                include_once WPSHAPERE_PATH . 'admin-theme-presets/hot-pink.php';
                include_once WPSHAPERE_PATH . 'admin-theme-presets/hive.php';
                include_once WPSHAPERE_PATH . 'admin-theme-presets/blueberry.php';
                include_once WPSHAPERE_PATH . 'admin-theme-presets/blossoms.php';
                include_once WPSHAPERE_PATH . 'admin-theme-presets/black-white.php';
                include_once WPSHAPERE_PATH . 'admin-theme-presets/beach.php';
                include_once WPSHAPERE_PATH . 'admin-theme-presets/africa.php';
                include_once WPSHAPERE_PATH . 'admin-theme-presets/paper-clay.php';
                include_once WPSHAPERE_PATH . 'admin-theme-presets/sailor.php';
                include_once WPSHAPERE_PATH . 'admin-theme-presets/vitamin.php';


                return $wps_themes;
            }


}

$wpshaperethemes = new WPS_THEMES();
