<?php

/**
 * Bootstrap plugin classes.
 *
 * @package SVG_Flags
 */
namespace WPGO_Plugins\SVG_Flags;

/**
 * Main bootstrap class.
 */
class BootStrap
{
    /**
     * Common root paths/directories.
     *
     * @var $module_roots
     */
    protected  $module_roots ;
    /**
     * Main class constructor.
     */
    public function __construct()
    {
        $this->module_roots = Main::$module_roots;
        $this->load_supported_features();
    }
    
    /**
     * Load plugin features.
     */
    public function load_supported_features()
    {
        $root = $this->module_roots['dir'];
        // Load plugin constants/data.
        require_once $root . 'classes/constants.php';
        $custom_plugin_data = new Constants( $this->module_roots );
        // Links on the main plugin index page.
        require_once $root . 'classes/utility.php';
        $utility = new Utility( $this->module_roots, $custom_plugin_data );
        $plugin_data = get_plugin_data( $this->module_roots['file'], false, false );
        // Import plugin framework classes (fw = framework).
        if ( !class_exists( '\\WPGO_Plugins\\Plugin_Framework\\Settings_Templates_FW' ) ) {
            require_once $root . 'api/templates/settings/settings.php';
        }
        $settings_fw = new \WPGO_Plugins\Plugin_Framework\Settings_Templates_FW( $this->module_roots );
        if ( !class_exists( '\\WPGO_Plugins\\Plugin_Framework\\New_Features_Templates_FW' ) ) {
            require_once $root . 'api/templates/settings/new-features.php';
        }
        $new_features_fw = new \WPGO_Plugins\Plugin_Framework\New_Features_Templates_FW( $this->module_roots );
        // Data to pass to certain classes.
        $new_features_json = '';
        if ( file_exists( $root . 'assets/misc/new-features.json' ) ) {
            $new_features_json = file_get_contents( $root . 'assets/misc/new-features.json' );
        }
        $new_features_arr = Utility::filter_and_decode_json( $new_features_json );
        // Plugin framework hooks.
        if ( !class_exists( '\\WPGO_Plugins\\Plugin_Framework\\Hooks_FW' ) ) {
            require_once $root . 'api/classes/hooks.php';
        }
        new \WPGO_Plugins\Plugin_Framework\Hooks_FW( $this->module_roots, $custom_plugin_data, svg_flags_fs() );
        // Enqueue framework scripts.
        if ( !class_exists( '\\WPGO_Plugins\\Plugin_Framework\\Enqueue_Framework_Scripts' ) ) {
            require_once $root . 'api/classes/enqueue-scripts.php';
        }
        new \WPGO_Plugins\Plugin_Framework\Enqueue_Framework_Scripts(
            $this->module_roots,
            $new_features_arr,
            $plugin_data,
            $custom_plugin_data
        );
        // Enqueue plugin scripts.
        require_once $root . 'classes/enqueue-scripts.php';
        new Enqueue_Scripts(
            $this->module_roots,
            $new_features_arr,
            $plugin_data,
            $custom_plugin_data
        );
        // Plugin settings pages.
        require_once $root . 'classes/plugin-admin-pages/settings.php';
        new Settings(
            $this->module_roots,
            $plugin_data,
            $custom_plugin_data,
            $utility,
            $settings_fw
        );
        require_once $root . 'classes/plugin-admin-pages/settings-new-features.php';
        new New_Features_Settings(
            $this->module_roots,
            $new_features_arr,
            $plugin_data,
            $custom_plugin_data,
            $utility,
            $new_features_fw
        );
        require_once $root . 'classes/plugin-admin-pages/settings-welcome.php';
        new Welcome_Settings(
            $this->module_roots,
            $plugin_data,
            $custom_plugin_data,
            $utility
        );
        // Register blocks.
        require_once $root . 'classes/register-blocks.php';
        new Register_Blocks( $this->module_roots );
        // Shortcodes bootstrap.
        require_once $root . 'classes/shortcodes/shortcodes.php';
        new Shortcodes( $this->module_roots, $custom_plugin_data );
        // Links on the main plugin index page.
        if ( !class_exists( '\\WPGO_Plugins\\Plugin_Framework\\Plugin_Links_FW' ) ) {
            require_once $root . 'api/classes/links.php';
        }
        new \WPGO_Plugins\Plugin_Framework\Plugin_Links_FW(
            $this->module_roots,
            $plugin_data,
            $custom_plugin_data,
            $utility
        );
        // Run upgrade routine when plugin updated to new version.
        if ( !class_exists( '\\WPGO_Plugins\\Plugin_Framework\\Upgrade_FW' ) ) {
            require_once $root . 'api/classes/upgrade.php';
        }
        new \WPGO_Plugins\Plugin_Framework\Upgrade_FW( $this->module_roots, $custom_plugin_data );
        // Localize plugin.
        require_once $root . 'classes/localize.php';
        new Localize( $this->module_roots );
    }

}
/* End class definition */