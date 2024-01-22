<?php

namespace WPGO_Plugins\SVG_Flags;

/*
 *    Enqueue plugin scripts
 */
class Enqueue_Scripts
{
    // protected $module_roots;
    // protected $js_deps;
    // protected $core_css_rel;
    // protected $core_css_url;
    // protected $core_css_ver;
    /* Main class constructor. */
    public function __construct(
        $module_roots,
        $new_features_arr,
        $plugin_data,
        $custom_plugin_data
    )
    {
        $this->module_roots = $module_roots;
        $this->new_features_arr = $new_features_arr;
        $this->plugin_data = $plugin_data;
        $this->custom_plugin_data = $custom_plugin_data;
        $this->country_codes = $this->custom_plugin_data->country_codes;
        $this->enq_pfx = $this->custom_plugin_data->enqueue_prefix;
        //$this->js_deps = [ 'wp-element', 'wp-i18n', 'wp-hooks', 'wp-components', 'wp-blocks', 'wp-editor', 'wp-compose' ];
        $this->js_deps = [
            'wp-plugins',
            'wp-element',
            'wp-edit-post',
            'wp-i18n',
            'wp-api-request',
            'wp-data',
            'wp-hooks',
            'wp-plugins',
            'wp-components',
            'wp-blocks',
            'wp-editor',
            'wp-compose'
        ];
        $this->core_css_rel = 'assets/flag-icon-css/css/flag-icon.min.css';
        $this->core_css_url = plugins_url( $this->core_css_rel, $this->module_roots['file'] );
        $this->core_css_ver = filemtime( $this->module_roots['dir'] . $this->core_css_rel );
        add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_admin_settings_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_admin_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
        add_action( 'enqueue_block_assets', array( &$this, 'enqueue_block_scripts' ) );
        add_action( 'enqueue_block_editor_assets', array( &$this, 'enqueue_block_editor_scripts' ) );
    }
    
    /* Scripts for all admin pages. This is necessary as we need to modify the main admin menu from JS. */
    public function enqueue_admin_scripts( $hook )
    {
        // $all_admin_pages_js_rel = 'api/assets/js/all-admin-pages-fw.js';
        // $all_admin_pages_js_url = plugins_url($all_admin_pages_js_rel, $this->module_roots['file']);
        // $all_admin_pages_js_ver = filemtime($this->module_roots['dir'] . $all_admin_pages_js_rel);
        $admin_settings_js_rel = 'assets/js/update-menu.js';
        $admin_settings_js_url = plugins_url( $admin_settings_js_rel, $this->module_roots['file'] );
        $admin_settings_js_ver = filemtime( $this->module_roots['dir'] . $admin_settings_js_rel );
        $opt_pfx = $this->custom_plugin_data->db_option_prefix;
        
        if ( method_exists( '\\WPGO_Plugins\\Plugin_Framework\\Upgrade_FW', 'calc_new_features' ) ) {
            $new_features_number = \WPGO_Plugins\Plugin_Framework\Upgrade_FW::calc_new_features( $opt_pfx, $this->new_features_arr, $this->plugin_data );
        } else {
            $new_features_number = 0;
        }
        
        // Register and localize the script with new data
        wp_register_script(
            $this->enq_pfx . '-update-menu-js',
            $admin_settings_js_url,
            array( 'wpgo-all-admin-pages-fw-js' ),
            $admin_settings_js_ver,
            true
        );
        $data = array(
            'admin_url'           => admin_url(),
            'new_features_number' => $new_features_number,
            'nav_status'          => SVG_FLAGS_FREEMIUS_NAVIGATION,
            'hook'                => $hook,
            'menu_type'           => $this->custom_plugin_data->menu_type,
            'main_menu_label'     => $this->custom_plugin_data->main_menu_label,
            'plugin_prefix'       => $this->enq_pfx,
        );
        // keep the handle generic so only one instance is enqueued (if multiple WPGO plugins are installed)
        //wp_enqueue_script('wpgo-all-admin-pages-fw-js', $all_admin_pages_js_url, array(), $all_admin_pages_js_ver, true);
        wp_localize_script( $this->enq_pfx . '-update-menu-js', $this->custom_plugin_data->plugin_settings_prefix . '_admin_menu_data', $data );
        wp_enqueue_script( $this->enq_pfx . '-update-menu-js' );
    }
    
    /* Scripts just for the plugin settings page. */
    public function enqueue_admin_settings_scripts( $hook )
    {
        // don't try to enqueue if $_GET['page'] not set
        if ( !isset( $_GET['page'] ) ) {
            return;
        }
        
        if ( $this->custom_plugin_data->menu_type === 'sub' ) {
            // Only enqueue scripts on the plugin settings page(s) (and Freemius pages)
            $pos = strpos( $hook, $this->custom_plugin_data->settings_page_hook );
            if ( $pos !== 0 ) {
                return;
            }
        } else {
            // Only enqueue scripts on the plugin settings page(s) (and Freemius pages)
            $pos = strpos( $hook, $this->custom_plugin_data->settings_page_hook_top );
            $pos1 = strpos( $hook, $this->custom_plugin_data->settings_page_hook_sub );
            // return if at least one values doesn't match
            if ( $pos !== 0 && $pos1 !== 0 ) {
                // echo "<pre>";
                // echo ">>>>>>>>>>> >>>>>>>>>>> ret: BYEBYE!!<br>";
                // echo "</pre>";
                return;
            }
        }
        
        $admin_settings_js_rel = 'assets/js/admin-settings.js';
        $admin_settings_js_url = plugins_url( $admin_settings_js_rel, $this->module_roots['file'] );
        $admin_settings_js_ver = filemtime( $this->module_roots['dir'] . $admin_settings_js_rel );
        // $admin_settings_fw_js_rel = 'api/assets/js/admin-settings-fw.js';
        // $admin_settings_fw_js_url = plugins_url($admin_settings_fw_js_rel, $this->module_roots['file']);
        // $admin_settings_fw_js_ver = filemtime($this->module_roots['dir'] . $admin_settings_fw_js_rel);
        $admin_settings_css_rel = 'assets/css/admin-settings.css';
        $admin_settings_css_url = plugins_url( $admin_settings_css_rel, $this->module_roots['file'] );
        $admin_settings_css_ver = filemtime( $this->module_roots['dir'] . $admin_settings_css_rel );
        // $admin_settings_fw_css_rel = 'api/assets/css/admin-settings-fw.css';
        // $admin_settings_fw_css_url = plugins_url($admin_settings_fw_css_rel, $this->module_roots['file']);
        // $admin_settings_fw_css_ver = filemtime($this->module_roots['dir'] . $admin_settings_fw_css_rel);
        // Register and localize the script with new data
        wp_register_script(
            $this->enq_pfx . '-admin-settings-js',
            $admin_settings_js_url,
            array(),
            $admin_settings_js_ver,
            true
        );
        $data = array(
            'admin_url'     => admin_url(),
            'settings_page' => $_GET['page'],
            'nav_status'    => SVG_FLAGS_FREEMIUS_NAVIGATION,
        );
        wp_localize_script( $this->enq_pfx . '-admin-settings-js', $this->custom_plugin_data->plugin_settings_prefix . '_admin_data', $data );
        wp_enqueue_script( $this->enq_pfx . '-admin-settings-js' );
        //wp_enqueue_script('wpgo-admin-settings-fw-js', $admin_settings_fw_js_url, array(), $admin_settings_fw_js_ver, true);
        // Styles for plugin admin settings page
        //wp_enqueue_style($this->enq_pfx . '-admin-settings-fw-css', $admin_settings_fw_css_url, [], $admin_settings_fw_css_ver);
        wp_enqueue_style(
            $this->enq_pfx . '-admin-settings-css',
            $admin_settings_css_url,
            [],
            $admin_settings_css_ver
        );
        // Core CSS (e.g. SVG flag styles)
        wp_enqueue_style(
            $this->enq_pfx . '-core-css',
            $this->core_css_url,
            [],
            $this->core_css_ver
        );
    }
    
    /* Enqueue flag scripts on all frontend pages. */
    public function enqueue_scripts()
    {
        $frontend_css_rel = 'assets/css/frontend.css';
        $frontend_css_url = plugins_url( $frontend_css_rel, $this->module_roots['file'] );
        $frontend_css_ver = filemtime( $this->module_roots['dir'] . $frontend_css_rel );
        // Main plugin CSS
        wp_enqueue_style(
            $this->enq_pfx . '-plugin-css',
            $frontend_css_url,
            [],
            $frontend_css_ver
        );
        // Core CSS
        wp_enqueue_style(
            $this->enq_pfx . '-core-css',
            $this->core_css_url,
            [],
            $this->core_css_ver
        );
    }
    
    /* Add scripts for block editor only */
    public function enqueue_block_editor_scripts()
    {
        $block_editor_js_rel = 'assets/js/block.editor.js';
        $block_editor_js_url = plugins_url( $block_editor_js_rel, $this->module_roots['file'] );
        $block_editor_js_ver = filemtime( $this->module_roots['dir'] . $block_editor_js_rel );
        $block_editor_css_rel = 'assets/css/block.editor.styles.css';
        $block_editor_css_url = plugins_url( $block_editor_css_rel, $this->module_roots['file'] );
        $block_editor_css_ver = filemtime( $this->module_roots['dir'] . $block_editor_css_rel );
        $deps = $this->js_deps;
        // Block editor script
        wp_register_script(
            $this->enq_pfx . '-block-editor-js',
            $block_editor_js_url,
            $deps,
            $block_editor_js_ver,
            true
        );
        $data = array(
            'countries' => $this->country_codes,
        );
        wp_localize_script( $this->enq_pfx . '-block-editor-js', $this->custom_plugin_data->plugin_settings_prefix . '_editor_data', $data );
        wp_enqueue_script( $this->enq_pfx . '-block-editor-js' );
        // Block editor styles
        wp_enqueue_style(
            $this->enq_pfx . '-block-editor-css',
            $block_editor_css_url,
            [],
            $block_editor_css_ver
        );
    }
    
    /* Add scripts for frontend and block editor */
    public function enqueue_block_scripts()
    {
        $block_css_rel = 'assets/css/block.styles.css';
        $block_css_url = plugins_url( $block_css_rel, $this->module_roots['file'] );
        $block_css_ver = filemtime( $this->module_roots['dir'] . $block_css_rel );
        // Core CSS
        wp_enqueue_style(
            $this->enq_pfx . '-core-css',
            $this->core_css_url,
            [],
            $this->core_css_ver
        );
        // Block styles
        wp_enqueue_style(
            $this->enq_pfx . '-block-css',
            $block_css_url,
            [],
            $block_css_url
        );
    }

}
/* End class definition */