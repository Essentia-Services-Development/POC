<?php

/**
 * In this file we have all the hooks and functions related to the plugin control module.
 * This module gives the ability to disable/enable plugins in a similar way like wordpress allows to enable/disable themes.
 * Also it adds the feature to bulk activate/deactivate a plugin on all or some sites (different from network activate/deactivate).
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// All the hooks for the plugin control module will run only if the module is turned on
if ( be_mu_get_setting( 'be-mu-plugin-status-module' ) == 'on' ) {

    // Registers and localizes the javascript file and css file for the network plugins page
    add_action( 'admin_enqueue_scripts', 'be_mu_plugin_network_register_script_style' );

    // Adds the Plugin element to the edit site navigatin
    add_filter( 'network_edit_site_nav_links', 'be_mu_plugin_add_edit_site_nav_link' );

    // Creates the network admin page for Site Plugin Control and calls actions to load the needed css and sctipt
    add_action( 'network_admin_menu', 'be_mu_plugin_add_submenu' );

    // Adds the Network Enable/Disable action links under every plugin in the plugins page in the network admin panel
    add_filter( 'network_admin_plugin_action_links', 'be_mu_plugin_network_action_links', 10, 2 );

    // Adds our html code for the results layer to the footer of the plugins page in the network admin panel
    add_action( 'admin_footer-plugins.php', 'be_mu_plugin_network_results_html', 99999999999 );

    // Ajax call to a function that enables/disables plugin control on a site level
    add_action( 'wp_ajax_be_mu_plugin_site_enable_disable_action', 'be_mu_plugin_site_enable_disable_callback' );

    // Ajax call to a function that enables/disables plugin control on a network level
    add_action( 'wp_ajax_be_mu_plugin_network_enable_disable_action', 'be_mu_plugin_network_enable_disable_callback' );

    // Ajax call to a function that shows the bulk activate/deactivate form
    add_action( 'wp_ajax_be_mu_plugin_network_bulk_show_action', 'be_mu_plugin_network_bulk_show_callback' );

    // Ajax call to a function that bulk activates/deactivates a selected plugin on all or some selected sites
    add_action( 'wp_ajax_be_mu_plugin_bulk_action', 'be_mu_plugin_bulk_callback' );

    // Shows a message on the network plugins page that asks if the user wants to import settings from the Multisite Plugin Manager plugin
    add_action( 'pre_current_active_plugins', 'be_mu_plugin_import_message' );

    // Ajax call to a function that imports plugin user control settings from the plugin Multisite Plugin Manager
    add_action( 'wp_ajax_be_mu_plugin_import_action', 'be_mu_plugin_import_callback' );

    // We filter the plugins that will be showed and remove the ones that are disabled
    add_filter( 'all_plugins', 'be_mu_plugin_filter_plugins' );

    // Before a plugin is deactivated we check if the user is allowed to control this plugin and show an error if not
    add_action( 'deactivate_plugin', 'be_mu_plugin_before_activate_deactivate', 10, 2 );

    // Before a plugin is activated we check if the user is allowed to control this plugin and show an error if not
    add_action( 'activate_plugin', 'be_mu_plugin_before_activate_deactivate', 10, 2 );
}

// A limit for the number of sites to process in a single ajax request
define( "BE_MU_PLUGIN_LIMIT_SITES", 700 );

// After this many seconds we will try to stop the ajax request when we are done with a certain task (might take more time)
define( "BE_MU_PLUGIN_LIMIT_TIME", 10 );

/**
 * Adds the Plugin element to the edit site navigatin
 * @param array $links
 * @return array
 */
function be_mu_plugin_add_edit_site_nav_link( $links ) {
    $link_data = array(
        'label' => esc_html__( 'Plugins', 'beyond-multisite' ),
        'url' => 'admin.php?page=be_mu_plugin_control_site',
        'cap' => 'manage_sites',
    );

    // With this function we put it before the Settings element
    return be_mu_add_element_to_array( $links, 'be-mu-menu-site-plugins', $link_data, 'site-settings' );
}

// Creates the Site Plugin Control network admin page and calls actions to load the needed style and sctipt
function be_mu_plugin_add_submenu() {

    // Create the Site Plugin Control page (we will add it to the Edit site navigation, and hide it from the admin menu)
    $page = add_menu_page(
        esc_html__( 'Site Plugin Control', 'beyond-multisite' ),
        esc_html__( 'Site Plugin Control', 'beyond-multisite' ),
        'manage_network',
        'be_mu_plugin_control_site',
        'be_mu_plugin_control_site_subpage'
    );

    // We add the style for the site plugin control page
    add_action( 'load-' . $page, 'be_mu_add_beyond_multisite_style' );

    // We add the script for the site plugin control page
    add_action( 'load-' . $page, 'be_mu_plugin_add_site_script' );
}

// Adds the action needed to register the script for the site plugin control page
function be_mu_plugin_add_site_script() {
    add_action( 'admin_enqueue_scripts', 'be_mu_plugin_site_register_script' );
}

// Registers and localizes the javascript file and css file for the network plugins page
function be_mu_plugin_network_register_script_style( $hook ) {

    if ( is_network_admin() ) {

        if ( 'plugins.php' == $hook ) {

            // Registers a style file and enqueues it
            be_mu_register_beyond_multisite_style();

            // Register the script
            wp_register_script( 'be-mu-plugin-network-script', be_mu_plugin_dir_url() . 'scripts/network-plugin-control.js', array(),
                BEYOND_MULTISITE_VERSION, false );

            // This is the data we will send from the php to the javascript file
            $localize = array(
                'ajaxNonce' => wp_create_nonce( 'be_mu_plugin_network_nonce' ),
                'loadingGIF' => esc_url( be_mu_img_url( 'loading.gif' ) ),
                'processing' => esc_js( esc_html__( 'Processing...', 'beyond-multisite' ) ),
                'abort' => esc_js( esc_attr__( 'Abort', 'beyond-multisite' ) ),
                'error' => esc_js( esc_html__( 'Error', 'beyond-multisite' ) ),
                'networkEnable' => esc_js( esc_html__( 'Network Enable', 'beyond-multisite' ) ),
                'networkDisable' => esc_js( esc_html__( 'Network Disable', 'beyond-multisite' ) ),
                'networkEnabled' => esc_js( esc_attr__( 'Network Enabled', 'beyond-multisite' ) ),
                'networkDisabled' => esc_js( esc_attr__( 'Network Disabled', 'beyond-multisite' ) ),
                'confirmActivate' => esc_js( __( 'You are about to BULK ACTIVATE the plugin:', 'beyond-multisite' ) ),
                'confirmDeactivate' => esc_js( __( 'You are about to BULK DEACTIVATE the plugin:', 'beyond-multisite' ) ),
                'confirmContinue' => esc_js( __( 'Do you want to continue?', 'beyond-multisite' ) ),
                'errorRequest' => esc_js( __( 'Error: There is another request that is still running. Please wait a few seconds and try again. '
                    . 'If this problem continues, please reload the page.', 'beyond-multisite' ) ),
                'errorAccess' => esc_js( __( 'Error: You do not have sufficient permissions to make this request.', 'beyond-multisite' ) ),
                'errorResponse' => esc_js( __( 'Error: We got an empty response.', 'beyond-multisite' ) ),
                'errorInvalidNonce' => esc_js( __( 'Error: Invalid security nonce. Please reload the page and try again.', 'beyond-multisite' ) ),
                'errorData' => esc_js( __( 'Error: Invalid form data sent.', 'beyond-multisite' ) ),
                'errorMode' => esc_js( __( 'Error: Invalid mode data sent.', 'beyond-multisite' ) ),
                'errorNetworkActive' => esc_js( __( 'Error: The plugin is network activated.', 'beyond-multisite' ) ),
                'errorNetworkOnly' => esc_js( __( 'Error: The plugin can only be network activated/deactivated.', 'beyond-multisite' ) ),
                'errorSiteFilled' => esc_js( __( 'Error: The field for the site IDs is filled, but you chose a setting that ignores it.', 'beyond-multisite' ) ),
                'errorPluginGone' => esc_js( __( 'Error: The plugin does not exist.', 'beyond-multisite' ) ),
                'errorSiteEmpty' => esc_js( __( 'Error: The field for the site IDs is empty or invalid. '
                    . 'The current settings require a comma-separated list of site IDs.', 'beyond-multisite' ) ),
                'sitesProcessed' => esc_js( esc_html__( 'Sites processed so far:', 'beyond-multisite' ) ),
                'bulkActivation' => esc_js( esc_html__( 'Bulk Activation Completed', 'beyond-multisite' ) ),
                'bulkDeactivation' => esc_js( esc_html__( 'Bulk Deactivation Completed', 'beyond-multisite' ) ),
                'importCompleted' => esc_js( esc_html__( 'Importing Settings Completed', 'beyond-multisite' ) ),
                'close' => esc_js( esc_attr__( 'Close', 'beyond-multisite' ) ),
                'confirmImport' => esc_js( __( 'WARNING! You are about to IMPORT SETTINGS from the plugin Multisite Plugin Manager. '
                    . 'All current plugin control SETTINGS WILL BE LOST! This action will affect site-specific settings too. '
                    . 'This can only be undone manually!', 'beyond-multisite' ) ),
                'pageURL' => esc_js( network_admin_url( 'plugins.php' ) ),
                'hideImportURL' => esc_js( network_admin_url( 'plugins.php?be-mu-plugin-hide-import' ) ),
                'confirmHideImport' => esc_js( __( 'You are about to permanently hide the import settings message', 'beyond-multisite' ) ),
                'errorServerFail' => esc_js( __( 'Error: Unexpected server error. If you have WordPress debugging and logging enabled, '
                    . 'you should be able to see more details about the error in the /wp-content/debug.log file.', 'beyond-multisite' ) ),

            );

            // We localize the script - we send php data to the javascript file
            wp_localize_script( 'be-mu-plugin-network-script', 'localizedNetworkPluginControl', $localize );

            // Enqueued script with localized data
            wp_enqueue_script( 'be-mu-plugin-network-script', '', array(), false, true );

        }

        // Load the style to hide the Site Plugin Control menu, since we only use the page in the Edit Site screen area.
        wp_register_style( 'be-mu-site-plugin-control-style', be_mu_plugin_dir_url() . 'styles/site-plugin-control.css', false, BEYOND_MULTISITE_VERSION );
        wp_enqueue_style( 'be-mu-site-plugin-control-style' );
    }
}

// Registers and localizes the javascript file for the site plugin control page
function be_mu_plugin_site_register_script() {

    // When editing a site there needs to be a site id in the url
    if ( isset( $_GET['id'] ) ) {

        // Register the script
        wp_register_script( 'be-mu-plugin-site-script', be_mu_plugin_dir_url() . 'scripts/site-plugin-control.js', array(), BEYOND_MULTISITE_VERSION, false );

        // This is the data we will send from the php to the javascript file
        $localize = array(
            'ajaxNonce' => wp_create_nonce( 'be_mu_plugin_site_nonce' ),
            'siteID' => intval( $_GET['id'] ),
            'loading' => esc_js( esc_html__( 'Loading...', 'beyond-multisite' ) ),
            'error' => esc_js( esc_html__( 'Error', 'beyond-multisite' ) ),
            'enable' => esc_js( esc_html__( 'Enable', 'beyond-multisite' ) ),
            'disable' => esc_js( esc_html__( 'Disable', 'beyond-multisite' ) ),
            'enabled' => esc_js( esc_attr__( 'Enabled', 'beyond-multisite' ) ),
            'disabled' => esc_js( esc_attr__( 'Disabled', 'beyond-multisite' ) ),
            'errorAccess' => esc_js( __( 'Error: You do not have sufficient permissions to make this request.', 'beyond-multisite' ) ),
            'errorResponse' => esc_js( __( 'Error: We got an empty response.', 'beyond-multisite' ) ),
            'errorInvalidNonce' => esc_js( __( 'Error: Invalid security nonce. Please reload the page and try again.', 'beyond-multisite' ) ),
            'errorServerFail' => esc_js( __( 'Error: Unexpected server error. If you have WordPress debugging and logging enabled, '
                . 'you should be able to see more details about the error in the /wp-content/debug.log file.', 'beyond-multisite' ) ),
        );

        // We localize the script - we send php data to the javascript file
        wp_localize_script( 'be-mu-plugin-site-script', 'localizedSitePluginControl', $localize );

        // Enqueued script with localized data
        wp_enqueue_script( 'be-mu-plugin-site-script', '', array(), false, true );
    }
}

/**
 * Adds the Network Enable/Disable action links under every plugin in the plugins page in the network admin panel
 * @param array $actions
 * @param string $plugin_file
 * @return array
 */
function be_mu_plugin_network_action_links( $actions, $plugin_file ) {

    // We add the links only if this is not the dropins page
    if ( ! isset( $_GET['plugin_status'] ) || ( 'dropins' !== $_GET['plugin_status'] && 'mustuse' !== $_GET['plugin_status'] ) ) {

        // An md5 of the plugin file, we use it as an unique identifier
        $md5_plugin_file = md5( $plugin_file );

        $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file );

        // The option key of the network plugin control option for the current plugin
        $network_option_key = 'be-mu-plugin-network-' . md5( $plugin_file );

        // If the plugin is network activated we show it as network disabled and don't allow status change
        if ( is_plugin_active_for_network( $plugin_file ) ) {

            $status = '<span class="be-mu-plugin-network-circle be-mu-circle-off" title="'
                . esc_attr__( 'Network Disabled', 'beyond-multisite' ) . '"></span>';

            $enable_disable_actions = '<span id="be-mu-plugin-network-status-' . esc_attr( $md5_plugin_file ) . '">' . $status . '</span>'
                . '<span class="be-mu-dark-text">' . esc_html__( 'Network Activated', 'beyond-multisite' ) . '</span>';

        // If the plugin is network only we show it as network disabled and don't allow status change
        } elseif ( true === $plugin_data['Network'] ) {

            $status = '<span class="be-mu-plugin-network-circle be-mu-circle-off" title="'
                . esc_attr__( 'Network Disabled', 'beyond-multisite' ) . '"></span>';

            $enable_disable_actions = '<span id="be-mu-plugin-network-status-' . esc_attr( $md5_plugin_file ) . '">' . $status . '</span>'
                . '<span class="be-mu-dark-text">' . esc_html__( 'Network Only', 'beyond-multisite' ) . '</span>';

        // The plugin is not network activated and is not a network only plugin
        } else {

            // The plugin is network disabled
            if ( get_site_option( $network_option_key ) == 'disabled' ) {

                // Argument for the javascript function be_mu_plugin_network_enable_disable
                $enable_or_disable = 'enable';

                // The text of the action link
                $enable_or_disable_string = __( 'Network Enable', 'beyond-multisite' );

                // The status of the plugin
                $status = '<span class="be-mu-plugin-network-circle be-mu-circle-off" title="'
                    . esc_attr__( 'Network Disabled', 'beyond-multisite' ) . '"></span>';

                // The HTML for the action link
                $enable_disable_actions = '<span id="be-mu-plugin-network-status-' . esc_attr( $md5_plugin_file ) . '">' .$status . '</span>'
                    . '<span id="be-mu-plugin-network-enable-disable-' . esc_attr( $md5_plugin_file )
                    . '"><a href="javascript:pluginControlNetworkEnableDisable( \''
                    . esc_js( esc_attr( $plugin_file ) ) . '\', \'' . esc_js( esc_attr( $md5_plugin_file ) ) . '\', \''
                    . esc_js( esc_attr( $enable_or_disable ) ) . '\' )">'
                    . esc_html( $enable_or_disable_string ) . '</a></span>';

            // The plugin is network enabled
            } else {

                // Argument for the javascript function be_mu_plugin_network_enable_disable
                $enable_or_disable = 'disable';

                // The text of the action link
                $enable_or_disable_string = __( 'Network Disable', 'beyond-multisite' );

                // The status of the plugin
                $status = '<span class="be-mu-plugin-network-circle be-mu-circle-on" title="'
                    . esc_attr__( 'Network Enabled', 'beyond-multisite' ) . '"></span>';

                // The HTML for the action link
                $enable_disable_actions = '<span id="be-mu-plugin-network-status-' . esc_attr( $md5_plugin_file ) . '">' . $status . '</span>'
                    . '<span id="be-mu-plugin-network-enable-disable-' . esc_attr( $md5_plugin_file )
                    . '"><a href="javascript:pluginControlNetworkEnableDisable( \''
                    . esc_js( esc_attr( $plugin_file ) ) . '\', \'' . esc_js( esc_attr( $md5_plugin_file ) ) . '\', \''
                    . esc_js( esc_attr( $enable_or_disable ) ) . '\' )">'
                    . esc_html( $enable_or_disable_string ) . '</a></span>';
            }
        }

        // We add the action link to the array of links
        $actions = be_mu_add_element_to_array( $actions, 'be-mu-network-enable-disable', $enable_disable_actions, 'edit' );

        $bad_plugins = be_mu_plugin_get_bulk_bad_plugins();

        // If the plugin is not network activated and not network only we also add the action link to bulk activate/deactivate
        if ( ! is_plugin_active_for_network( $plugin_file ) && true !== $plugin_data['Network'] ) {

            if ( in_array( $plugin_file, $bad_plugins ) ) {

                // The HTML for the message for incompatible plugin
                $bulk_actions = '<a href="javascript:pluginControlShowBulk( \'' . esc_js( esc_attr( $plugin_file ) ) . '\' )">'
                    . esc_html__( 'Bulk Activate/Deactivate', 'beyond-multisite' ) . ' ' . esc_html__( '(incompatible)', 'beyond-multisite' ) . '</a>';
            } else {

                // The HTML for the action link
                $bulk_actions = '<a href="javascript:pluginControlShowBulk( \'' . esc_js( esc_attr( $plugin_file ) ) . '\' )">'
                    . esc_html__( 'Bulk Activate/Deactivate', 'beyond-multisite' ) . '</a>';
            }

            // If the Activated in? module is enabled we add the link before its link, otherwise before the Network Enable/Disable link
            if ( be_mu_get_setting( 'be-mu-activated-in-status-module' ) == 'on' ) {
                $actions = be_mu_add_element_to_array( $actions, 'be-mu-bulk-activate-deactivate', $bulk_actions, 'be-mu-activated-in' );
            } else {
                $actions = be_mu_add_element_to_array( $actions, 'be-mu-bulk-activate-deactivate', $bulk_actions, 'be-mu-network-enable-disable' );
            }
        }

    }

    // We return the modified array of action links
    return $actions;
}

/**
 * Returns an array of plugins that are known to be not compatible with bulk activation/deactivation
 * @return array
 */
function be_mu_plugin_get_bulk_bad_plugins() {
    return Array(
        'wp-rocket/wp-rocket.php',
    );
}

// Outputs the html code for the results layer in the footer of the plugins page in the network admin panel
function be_mu_plugin_network_results_html() {

    // We output the code only if it is a network admin page
    if( is_network_admin() ) {

    ?>

        <div id="be-mu-plugin-container">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
                <tr>
                    <td valign="middle">
                        <div id="be-mu-plugin-div-results">

                        </div>
                    </td>
                </tr>
            </table>
        </div>

    <?php

    }
}

// Creates the Site Plugin Control page in the network admin
function be_mu_plugin_control_site_subpage() {

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'beyond-multisite' ) );
    }

    if ( isset( $_GET['id'] ) ) {

        // If the site id exists we set a var with it
        $blog_id = intval( $_GET['id'] );
    } else {

        // If the site id does not exist we show an error (we cannot edit a site without an id)
        wp_die( esc_html__( 'Error: Missing ID.', 'beyond-multisite' ) );
    }

    // We get the details of the site we are editing
    $details = get_blog_details( $blog_id );

    // This is the title of the page we are viewing
    $title = sprintf( esc_html__( 'Edit Site: %s', 'beyond-multisite' ), esc_html( $details->blogname ) );

    ?>

    <div class="wrap">

        <h1 id="edit-site"><?php echo $title; ?></h1>
        <p class="edit-site-actions">
            <a href="<?php echo esc_url( get_home_url( $blog_id, '/' ) ); ?>"><?php esc_html_e( 'Visit', 'beyond-multisite' ); ?></a> |
            <a href="<?php echo esc_url( get_admin_url( $blog_id ) ); ?>"><?php esc_html_e( 'Dashboard', 'beyond-multisite' ); ?></a>
        </p>

        <?php

        // We show the edit site navigation menu with our site plugin control page selected
        network_edit_site_nav( array(
            'blog_id' => $blog_id,
            'selected' => 'be-mu-menu-site-plugins',
        ) );

        echo '<div>&nbsp;</div>';

        // we output the header of the page
        be_mu_header_super_admin_page( __( 'Site Plugin Control', 'beyond-multisite' ) );

        // We get all plugins
        $plugins = get_plugins();

        echo '<div class="be-mu-white-box be-mu-w100per">'
            . esc_html__( 'Network enabled, network activated, and network only plugins are not shown on this page.', 'beyond-multisite' ) . ' '
            . sprintf(
                esc_html__( 'You can network disable plugins from the %1$sPlugins%2$s page.', 'beyond-multisite' ),
                '<a href="' . esc_url( network_admin_url( 'plugins.php' ) ) . '">',
                '</a>'
            )
            . '</div>';

        echo '<table class="be-mu-table be-mu-mtop20 be-mu-w100per">';

        echo '<thead>'
            . '<tr>'
            . '<th class="be-mu-plugin-name-cell">' . esc_html__( 'Name', 'beyond-multisite' ) . '</th>'
            . '<th class="be-mu-plugin-site-actions-cell">' . esc_html__( 'Actions', 'beyond-multisite' ) . '</th>'
            . '<th class="be-mu-plugin-description-cell">' . esc_html__( 'Description', 'beyond-multisite' ) . '</th>'
            . '</tr>'
            . '</thead>';

        // Array with allowed tags in comments to use in wp_kses when showing plugin description
        $allowed_tags = wp_kses_allowed_html( 'comment' );

        // Counts how many plugins we showed in the table below
        $plugin_count_to_show = 0;

        echo '<tbody>';

        // We go through all the plugins
        foreach ( $plugins as $plugin_file => $plugin_data ) {

            // An md5 of the plugin file, we use it as an unique identifier
            $md5_plugin_file = md5( $plugin_file );

            // The option key of the site plugin control option for the current plugin
            $site_option_key = 'be-mu-plugin-site-' . $md5_plugin_file;

            // The option key of the network plugin control option for the current plugin
            $network_option_key = 'be-mu-plugin-network-' . $md5_plugin_file;

            // We will show only plugins that are not network active, are not network only, and are network disabled
            if ( ! is_plugin_active_for_network( $plugin_file ) && true != $plugin_data['Network'] && get_site_option( $network_option_key ) == 'disabled' ) {

                // The plugin is site enabled
                if ( get_blog_option( $blog_id, $site_option_key ) == 'enabled' ) {

                    // Argument for the javascript function be_mu_plugin_site_enable_disable
                    $enable_or_disable = 'disable';

                    // The text of the action link
                    $enable_or_disable_string = __( 'Disable', 'beyond-multisite' );

                    // The status of the plugin
                    $status = '<div class="be-mu-plugin-circle be-mu-circle-on" title="' . esc_attr__( 'Enabled', 'beyond-multisite' ) . '"></div>';

                // The plugin is site disabled
                } else {

                    // Argument for the javascript function be_mu_plugin_site_enable_disable
                    $enable_or_disable = 'enable';

                    // The text of the action link
                    $enable_or_disable_string = __( 'Enable', 'beyond-multisite' );

                    // The status of the plugin
                    $status = '<div class="be-mu-plugin-circle be-mu-circle-off" title="' . esc_attr__( 'Disabled', 'beyond-multisite' ) . '"></div>';
                }

                // The content of the cell for the actions column of the table
                $actions_string = '<span id="be-mu-plugin-site-enable-disable-' . esc_attr( $md5_plugin_file ) . '">'
                    . '<a href="javascript:pluginControlSiteEnableDisable(\''
                    . esc_js( esc_attr( $plugin_file ) ) . '\', \'' . esc_js( esc_attr( $md5_plugin_file ) ) . '\', \''
                    . esc_js( esc_attr( $enable_or_disable ) ) . '\' )">' . esc_html( $enable_or_disable_string )
                    . '</a>'
                    . '</span>';

                // The row with data for the current plugin in the loop
                echo '<tr>'
                    . '<td data-title="' . esc_attr__( 'Name', 'beyond-multisite' ) . '" class="be-mu-plugin-name-cell"><span id="be-mu-plugin-site-status-'
                    . esc_attr( $md5_plugin_file ) . '">' . $status . '</span><b>' . esc_html( $plugin_data['Name'] ) . '</b> '
                    . esc_html( $plugin_data['Version'] ) . '</td>'
                    . '<td data-title="' . esc_attr__( 'Actions', 'beyond-multisite' ) . '" class="be-mu-plugin-site-actions-cell">'. $actions_string . '</td>'
                    . '<td data-title="' . esc_attr__( 'Description', 'beyond-multisite' ) . '" class="be-mu-plugin-description-cell">'
                    . wp_kses( $plugin_data['Description'], $allowed_tags ) . '</td>'
                    . '</tr>';

                // We increase the number of shown plugins
                $plugin_count_to_show++;
            }
        }

        // If we have not showed any plugins we show a message
        if ( 0 == $plugin_count_to_show ) {
            echo '<tr>'
                . '<td colspan="3">' . esc_html__( 'There are no plugins to show here at this time.', 'beyond-multisite' ) . '</td>'
                . '</tr>';
        }

        echo '</tbody>';

        echo '<tfoot>'
            . '<tr>'
            . '<th class="be-mu-plugin-name-cell">' . esc_html__( 'Name', 'beyond-multisite' ) . '</th>'
            . '<th class="be-mu-plugin-site-actions-cell">' . esc_html__( 'Actions', 'beyond-multisite' ) . '</th>'
            . '<th class="be-mu-plugin-description-cell">' . esc_html__( 'Description', 'beyond-multisite' ) . '</th>'
            . '</tr>'
            . '</tfoot>';

        echo '</table>';

        ?>

    </div>

    <?php

}

// A function that enables/disables plugin control on a site level
function be_mu_plugin_site_enable_disable_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_plugin_site_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // The action we perform - enable or disable
    $enable_or_disable = $_POST['enable_or_disable'];

    // The file of the plugin we will perform an action on
    $plugin_file = wp_filter_nohtml_kses( $_POST['plugin_file'] );

    // The id of the site we will perform action on
    $blog_id = intval( $_POST['blog_id'] );

    // The option key of the site plugin control option for the current plugin
    $site_option_key = 'be-mu-plugin-site-' . md5( $plugin_file );

    // We enable the plugin
    if ( 'enable' == $enable_or_disable ) {

        // If the blog option exists we update it, otherwise we add it
        be_mu_set_or_make_blog_setting( $blog_id, $site_option_key, 'enabled' );

    // We disable the plugin
    } else {

        // If the blog option exists we delete it
        delete_blog_option( $blog_id, $site_option_key );
    }

    wp_die();
}

// A function that enables/disables plugin control on a network level
function be_mu_plugin_network_enable_disable_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_plugin_network_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // The action we perform - enable or disable
    $enable_or_disable = $_POST['enable_or_disable'];

    // The file of the plugin we will perform an action on
    $plugin_file = wp_filter_nohtml_kses( $_POST['plugin_file'] );

    // The option key of the network plugin control option for the current plugin
    $network_option_key = 'be-mu-plugin-network-' . md5( $plugin_file );

    // We network disable the plugin
    if ( 'disable' == $enable_or_disable ) {

        // If the option exists we update it, otherwise we add it
        be_mu_set_or_make_setting( $network_option_key, 'disabled' );

    // We network enable the plugin
    } else {

        // If the option exists we delete it
        be_mu_delete_setting( $network_option_key );
    }

    wp_die();
}

// Shows the bulk activate/deactivate form in the network plugins page
function be_mu_plugin_network_bulk_show_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_plugin_network_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // The file of the plugin that we will bulk activate/deactivate
    $plugin_file = $_POST['plugin_file'];

    $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file );

    // The name of the plugin that we will bulk activate/deactivate
    $plugin_name = $plugin_data['Name'];

    $bad_plugins = be_mu_plugin_get_bulk_bad_plugins();

    ?>

    <div class="be-mu-p20">
        <h2 class="be-mu-plugin-h2">
            <?php
                printf(
                    esc_html__( 'Bulk Activate/Deactivate: %s', 'beyond-multisite' ),
                    esc_html( $plugin_name )
                );
            ?>
            <div class='be-mu-right'><?php echo be_mu_plugin_bulk_get_close(); ?></div>
        </h2>
        <?php
        if ( in_array( $plugin_file, $bad_plugins ) ) {
            ?>
            <p class="be-mu-red"><?php esc_html_e( 'This plugin is not compatible with the bulk activate/deactivate feature. '
                . 'To avoid problems, it is recommended that you do not use this feature on this plugin. Sorry for the inconvenience.', 'beyond-multisite' ) ?></p>
            <?php
        }
        ?>
        <ul class="be-mu-mtop15">
            <li>
                <label for="be-mu-plugin-bulk-affect-sites-id-opt">
                    <b><?php esc_html_e( 'Affect sites with:', 'beyond-multisite' ) ?></b>
                </label>
                <?php
                be_mu_select(
                    'be-mu-plugin-bulk-affect-sites-id-opt',
                    array( 'Any site ID', 'Only these site IDs:', 'All except these site IDs:' ),
                    array(
                        esc_html__( 'Any site ID (All sites)', 'beyond-multisite' ),
                        esc_html__( 'Only these site IDs:', 'beyond-multisite' ),
                        esc_html__( 'All except these site IDs:', 'beyond-multisite' ),
                    )
                );
                be_mu_input_text( 'be-mu-plugin-bulk-affect-sites-ids' );
                ?>
                <span class="be-mu-tooltip">
                    <span class="be-mu-info">i</span>
                    <span class="be-mu-tooltip-text">
                        <?php
                            esc_html_e( 'Accepts: Comma-separated numbers or an empty string.', 'beyond-multisite' );
                        ?>
                    </span>
                </span>
            </li>
        </ul>
        <p class="be-mu-mbot0">
            <input class="button button-primary"
                onclick="pluginControlStartBulk( 'activate', '<?php echo esc_js( esc_attr( $plugin_file ) ); ?>',
                '<?php echo esc_js( esc_attr( $plugin_name ) ); ?>' )"
                type="button"
                value="<?php esc_attr_e( 'Bulk Activate', 'beyond-multisite' ) ?>" />
            <input class="button button-primary"
                onclick="pluginControlStartBulk( 'deactivate', '<?php echo esc_js( esc_attr( $plugin_file ) ); ?>',
                '<?php echo esc_js( esc_attr( $plugin_name ) ); ?>' )"
                type="button"
                value="<?php esc_attr_e( 'Bulk Deactivate', 'beyond-multisite' ) ?>" />
        </p>
    </div>

    <?php

    wp_die();
}

// A function that bulk activates/deactivates a selected plugin on all or some selected sites
function be_mu_plugin_bulk_callback() {

    // We start a timer to know how much time the current request runs and abort if the time limit is reached
    $time1 = microtime( true );

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_plugin_network_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // The count of the processed sites on the current request
    $request_processed_sites_count = 0;

    // If it is set to 1 it means that a time or site limit is reached for the current request
    $is_request_limit_reached = 0;

    // The mode that we are working with in this task: activate or deactivate
    $mode = $_POST['mode'];

    // The file of the plugin we will perform action on
    $plugin_file = wp_filter_nohtml_kses( $_POST['plugin_file'] );

    // The selected option about which sites to affect
    $affect_sites_id_option = $_POST['affect_sites_id_option'];

    // The site ids to affect, we strip spaces in case user put them after commas
    $affect_sites_ids = be_mu_strip_whitespace( $_POST['affect_sites_ids'] );

    // The offset to use when calling get_sites()
    $offset = intval( $_POST['offset'] );

    // If the mode is not valid we show an error
    if ( 'activate' != $mode && 'deactivate' != $mode ) {

        wp_die( 'invalid-mode' );
    }

    // We get all plugins
    $plugins = get_plugins();

    $plugin_files = Array();

    foreach ( $plugins as $the_plugin_file => $the_plugin_data ) {

        // An array with the plugin file of each plugin
        $plugin_files[] = $the_plugin_file;
    }

    // Check if the plugin we will perform action on exists
    if ( ! in_array( $plugin_file, $plugin_files ) ) {

        // Error, this plugin does not exist
        wp_die( 'plugin-gone' );
    }

    if ( is_plugin_active_for_network( $plugin_file ) ) {

        // Error, this plugin is network activated
        wp_die( 'network-activated' );
    }

    // We get the plugin data
    $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file );

    if ( true === $plugin_data['Network'] ) {

        // Error, this plugin can only be network activated/deactivated
        wp_die( 'network-only' );
    }

    // Based on the settings for the current task and the offset for the current request we create the arguments for get_sites()
    $get_sites_arguments = be_mu_plugin_build_get_sites_args( $affect_sites_id_option, $affect_sites_ids, $offset );

    // We get the sites that we will try to process in this request
    $sites = get_sites( $get_sites_arguments );

    // We go through all the selected sites
    foreach ( $sites as $site_object ) {              

        // The id of the current site in the foreach cycle
        $site_id = intval( $site_object->id );

        // We switch to the current site in the loop
        switch_to_blog( $site_id );

        if ( 'activate' == $mode ) {

            // We activate the plugin
            activate_plugins( $plugin_file );
        } else {

            // We deactivate the plugin
            deactivate_plugins( $plugin_file );
        }

        // We switch back to our main site
        restore_current_blog();

        // At this point the current site is processed, so we increase the counter of processed site
        $request_processed_sites_count++;

        // If more than BE_MU_PLUGIN_LIMIT_TIME seconds have passed or we processed more than BE_MU_PLUGIN_LIMIT_SITES sites
        // We stop and we set $is_request_limit_reached to 1 so we know that a limit stopped us and we are not done
        if ( ( microtime( true ) - $time1 ) > BE_MU_PLUGIN_LIMIT_TIME || $request_processed_sites_count == BE_MU_PLUGIN_LIMIT_SITES ) {

            $is_request_limit_reached = 1;
            break;
        }
    }

    // This is the data to pass to the javascript function in json format
    $json_result = array(
        'requestProcessedSitesCount' => $request_processed_sites_count,
        'currentOffset' => $offset,
        'isRequestLimitReached' => $is_request_limit_reached,
    );

    echo json_encode( $json_result );

    wp_die();
}

/**
 * Based on the settings it returns an array with the arguments for the get_sites function we use while bulk activating/deactivating plugins
 * @param string $affect_sites_id_option
 * @param string $affect_sites_ids
 * @param int $offset
 * @return array
 */
function be_mu_plugin_build_get_sites_args( $affect_sites_id_option, $affect_sites_ids, $offset ) {

    if ( 'Any site ID' == $affect_sites_id_option ) {

        // If one settings says any site and the one for the site ids is filled, this might indicate a mistake, so we return an error
        if ( '' != $affect_sites_ids ) {

            wp_die( 'site-ids-filled' );
        }

        // Otherwise we return the array with arguments
        return array(
            'offset' => $offset,
            'number' => BE_MU_PLUGIN_LIMIT_SITES,
        );
    }
    elseif ( 'Only these site IDs:' == $affect_sites_id_option ) {

        // If the sites ids field is invalid we return an error number
        if ( ! be_mu_is_comma_separated_numbers( $affect_sites_ids ) ) {
            wp_die( 'site-ids-empty' );
        }

        //make an array of all the site ids to include
        $include_site_ids = explode( ',', $affect_sites_ids );

        return array(
            'offset' => $offset,
            'number' => BE_MU_PLUGIN_LIMIT_SITES,
            'site__in' => $include_site_ids,
        );
    } elseif ( 'All except these site IDs:' == $affect_sites_id_option ) {

        // If the sites ids field is invalid we return an error number
        if ( ! be_mu_is_comma_separated_numbers( $affect_sites_ids ) ) {

            wp_die( 'site-ids-empty' );
        }

        //make an array of all the site ids to exclude
        $exclude_site_ids = explode( ',', $affect_sites_ids );

        return array(
            'offset' => $offset,
            'number'=> BE_MU_PLUGIN_LIMIT_SITES,
            'site__not_in' => $exclude_site_ids,
        );
    } else {
        wp_die( 'invalid-data' );
    }
}

/**
 * Returns true if the user has access to control a given plugin and false if not
 * @param string $plugin_file
 * @return bool
 */
function be_mu_plugin_check_access( $plugin_file ) {

    // If these any of these functions does not exists, we are allowing access since this is some kind of automatic activation/deactivation made by a plugin
    if ( ! function_exists( 'wp_get_current_user' ) || ! function_exists( 'current_user_can' ) ) {
        return true;
    }

    // Super admins always have access to control any plugin, regardless of settings
    if ( current_user_can( 'manage_network' ) || defined( 'WP_CLI' ) ) {
        return true;
    }

    // The option key of the site plugin control option for the current plugin
    $site_option_key = 'be-mu-plugin-site-' . md5( $plugin_file );

    // The option key of the network plugin control option for the current plugin
    $network_option_key = 'be-mu-plugin-network-' . md5( $plugin_file );

    // Network disabled, only site enabled have access
    if ( get_site_option( $network_option_key ) == 'disabled' ) {

        // Site enabled
        if ( get_option( $site_option_key ) == 'enabled' ) {
            return true;
        } else {
            return false;
        }

    // Network enabled, everyone have access
    } else {
        return true;
    }
}

/**
 * We filter the plugins that will be showed and remove the ones that are disabled.
 * We also network disable new plugins if we see them for a first time in the list, and if the setting for this is on.
 * @param array $all_plugins
 * @return array
 */
function be_mu_plugin_filter_plugins( $all_plugins ) {

    // The status of the setting to disable new plugins or not
    $disable_new_plugins_setting = be_mu_get_setting( 'be-mu-plugin-network-disable-new-plugins' );

    // If the setting to disable new plugins is on
    if ( 'on' === $disable_new_plugins_setting ) {

        // We create a list of all plugins ever installed, it if does not exist already
        be_mu_plugin_maybe_create_plugins_ever_installed_list();

        // The main plugin files of all plugin in our list of plugins ever installed
        $all_database_plugin_files = Array();

        // The main plugin files of all new plugins we see, that are not in our list of plugins ever installed
        $new_plugin_files = Array();

        // String with our list of plugins ever installed
        $all_database_plugin_files_string = be_mu_get_setting( 'be-mu-plugin-all-plugins-ever-installed' );

        // The main plugin files of all plugin in our list of plugins ever installed
        $all_database_plugin_files = explode( ';;', $all_database_plugin_files_string );
    }

    // We go through the plugins
    foreach ( $all_plugins as $plugin_file => $plugin_data ) {

        // If the setting to disable new plugins is on and the current plugin in the foreach is not in our list of plugins ever installed, we disable it
        if ( 'on' === $disable_new_plugins_setting && ! in_array( $plugin_file, $all_database_plugin_files ) ) {

            // The main plugin files of all new plugins we see, that are not in our list of plugins ever installed
            $new_plugin_files[] = $plugin_file;

            // The option key of the network plugin control option for the current plugin
            $network_option_key = 'be-mu-plugin-network-' . md5( wp_filter_nohtml_kses( $plugin_file ) );

            // We network disable the plugin. If the option exists we update it, otherwise we add it
            be_mu_set_or_make_setting( $network_option_key, 'disabled' );
        }

        // If the user does not have access to control this plugin, we remove it from the list
        if ( ! be_mu_plugin_check_access( $plugin_file ) ) {
            unset( $all_plugins[ $plugin_file ]);
        }
    }       

    // If the setting to disable new plugins is on and we found any new plugins, we add them to the database list of plugins ever installed
    if ( 'on' === $disable_new_plugins_setting && count( $new_plugin_files ) > 0 ) {
        be_mu_set_or_make_setting( 'be-mu-plugin-all-plugins-ever-installed', $all_database_plugin_files_string
            . ';;' . implode( ';;', $new_plugin_files ) );
    }

    return $all_plugins;
}

/**
 * Updates the list of all plugins ever installed, that we keep in the database, to include all currently installed plugins too.
 * If the list does not exist, it creates it from the currently installed plugins.
 */
function be_mu_plugin_create_or_update_plugins_ever_installed_list() {

    // The list of plugins ever installed in the database (if it even exists)
    $all_database_plugin_files_string = be_mu_get_setting( 'be-mu-plugin-all-plugins-ever-installed' );

    // All currently installed plugins
    $all_plugins = get_plugins();

    // The main files of all installed plugins
    $all_plugin_files = Array();
    foreach ( $all_plugins as $current_plugin_file => $plugin_data ) {
        $all_plugin_files[] = $current_plugin_file;
    }

    // If the list of plugins ever installed is not present, we create it from all currnetly installed plugins
    if ( false === $all_database_plugin_files_string ) {

        $all_plugins_string = implode( ';;', $all_plugin_files );
        be_mu_make_setting( 'be-mu-plugin-all-plugins-ever-installed', $all_plugins_string );

    // If the list of plugins ever installed is present from before, we add only the currently installed plugins that are not in there
    } else {

        // The main files of the plugins in the current list of plugins ever installed
        $all_database_plugin_files = explode( ';;', $all_database_plugin_files_string );

        // The newly found plugins we need to add to the list in the database
        $new_plugin_files = Array();

        foreach ( $all_plugin_files as $plugin_file ) {

            // If any of the plugins currently installed is not in our list from before, we will add it
            if ( ! in_array( $plugin_file, $all_database_plugin_files ) ) {
                $new_plugin_files[] = $plugin_file;
            }
        }

        // If we actually found any new plugins that are not in the list we had, we will add them
        if ( count( $new_plugin_files ) > 0 ) {
            $add_to_database_plugins_string = ';;' . implode( ';;', $new_plugin_files );
            be_mu_set_or_make_setting( 'be-mu-plugin-all-plugins-ever-installed', $all_database_plugin_files_string
                . $add_to_database_plugins_string );
        }
    }
}

/**
 * If the list of all plugins ever installed, that we keep in the database, does not exist, it creates it from the currently installed plugins.
 */
function be_mu_plugin_maybe_create_plugins_ever_installed_list() {

    if ( false === be_mu_get_setting( 'be-mu-plugin-all-plugins-ever-installed' ) ) {

        // All currently installed plugins
        $all_plugins = get_plugins();

        // The main files of all installed plugins
        $all_plugin_files = Array();
        foreach ( $all_plugins as $current_plugin_file => $plugin_data ) {
            $all_plugin_files[] = $current_plugin_file;
        }

        // We create the list from all currnetly installed plugins
        $all_plugins_string = implode( ';;', $all_plugin_files );
        be_mu_make_setting( 'be-mu-plugin-all-plugins-ever-installed', $all_plugins_string );
    }
}

/**
 * Before a plugin is activated/deactivated we check if the user is allowed to control this plugin and show an error if not
 * @param string $plugin
 * @param bool $network_wide
 */
function be_mu_plugin_before_activate_deactivate( $plugin, $network_wide ) {
    if ( ! be_mu_plugin_check_access( $plugin ) ) {
        wp_die( esc_html__( 'You are not allowed to control this plugin.', 'beyond-multisite' ) );
    }
}

// Shows a message on the network plugins page that asks if the user wants to import settings from the Multisite Plugin Manager plugin
function be_mu_plugin_import_message() {

    // If the button for hiding this message was clicked, we update the setting that hides it
    if ( isset( $_GET['be-mu-plugin-hide-import'] ) ) {
        be_mu_set_or_make_setting( 'be-mu-plugin-import-hide', 'on' );
    }

    // If we detect any of the options for the Multisite Plugin Manager, and the user hasn't hidden the message, we display the import settings message
    if ( is_network_admin() && ( false !== get_site_option( 'pm_auto_activate_list' ) || false !== get_site_option( 'pm_user_control_list' )
        || false !== get_site_option( 'pm_supporter_control_list' ) ) && be_mu_get_setting( 'be-mu-plugin-import-hide' ) == 'off' ) :
    ?>
        <div class='be-mu-white-box be-mu-w100per be-mu-mtop20'>
            <h2 class="be-mu-mtop5">
                <?php
                    esc_html_e( 'Do you want to import settings from "Multisite Plugin Manager" to the "Plugin Control" module of "Beyond Multisite"?',
                        'beyond-multisite' );
                ?>
            </h2>
            <p>
                <?php
                printf(
                    esc_html__( 'We detect that you have used the plugin %1$sMultisite Plugin Manager%2$s. We can import '
                        . 'your user control settings from there if you want. All plugins with user control setting '
                        . 'set to %1$sNone%2$s will become %1$sNetwork Disabled%2$s, and the ones set to %1$sAll Users%2$s will '
                        . 'become %1$sNetwork Enabled%2$s. Any override settings for individual sites will also be '
                        . 'imported. Auto-activate settings will be ignored. All current settings will be lost.', 'beyond-multisite' ),
                    '<i>', '</i>'
                );
                ?>
            </p>
            <p class="be-mu-mbot5">
                <input class="button button-primary" onclick="pluginControlImport()" type="button"
                    value="<?php esc_attr_e( 'Import Settings', 'beyond-multisite' ) ?>" />
                <input class="button" onclick="pluginControlHideImport()" type="button"
                    value="<?php esc_attr_e( 'Permanently Hide This Message', 'beyond-multisite' ) ?>" />
            </p>
        </div>

    <?php

    endif;

}

// A function that imports plugin user control settings from the plugin Multisite Plugin Manager
function be_mu_plugin_import_callback() {

    // We start a timer to know how much time the current request runs and abort if the time limit is reached
    $time1 = microtime( true );

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_plugin_network_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // The count of the processed sites on the current request
    $request_processed_sites_count = 0;

    // If it is set to 1 it means that a time or site limit is reached for the current request
    $is_request_limit_reached = 0;

    // The offset to use when calling get_sites()
    $offset = intval( $_POST['offset'] );

    // We get all plugins
    $plugins = get_plugins();

    // We only set the network settings once, the first time we call this function (we may call it several times if there are a lof of sites)
    if ( 0 == $offset ) {

        // We get the user control settings from the Multisite Plugin Manager plugin. The plugins in this array will become network enabled.
        $user_control = (array) get_site_option( 'pm_user_control_list' );

        // We go through all the plugins and we network enable/disable them
        foreach ( $plugins as $the_plugin_file => $the_plugin_data ) {

            // The option key of the network plugin control option for the current plugin
            $network_option_key = 'be-mu-plugin-network-' . md5( $the_plugin_file );

            // We network disable the plugin
            if ( ! in_array( $the_plugin_file, $user_control ) ) {

                // If the option exists we update it, otherwise we add it
                be_mu_set_or_make_setting( $network_option_key, 'disabled' );

            // We network enable the plugin
            } else {

                // If the option exists we delete it
                be_mu_delete_setting( $network_option_key );
            }

        }

    }

    // We get the sites that we will try to process in this request
    $sites = get_sites( array( 'offset' => $offset, 'number' => BE_MU_PLUGIN_LIMIT_SITES ) );

    // We go through all the selected sites
    foreach ( $sites as $site_object ) {

        // The id of the current site in the foreach cycle
        $site_id = intval( $site_object->id );

        // We get the list of plugins that override the network settings. These will be site enabled.
        $override_plugins = get_blog_option( $site_id, 'pm_plugin_override_list' );

        // We go through all the plugins and we site enable/disable them
        foreach ( $plugins as $the_plugin_file => $the_plugin_data ) {

            // The option key of the site plugin control option for the current plugin
            $site_option_key = 'be-mu-plugin-site-' . md5( $the_plugin_file );

            // We enable the plugin
            if ( in_array( $the_plugin_file, $override_plugins ) ) {

                // If the blog option exists we update it, otherwise we add it
                be_mu_set_or_make_blog_setting( $site_id, $site_option_key, 'enabled' );

            // We disable the plugin
            } else {

                // If the blog option exists we delete it
                delete_blog_option( $site_id, $site_option_key );
            }

        }

        // At this point the current site is processed, so we increase the counter of processed site
        $request_processed_sites_count++;

        // If more than BE_MU_PLUGIN_LIMIT_TIME seconds have passed or we processed more than BE_MU_PLUGIN_LIMIT_SITES sites
        // We stop and we set $is_request_limit_reached to 1 so we know that a limit stopped us and we are not done
        if ( ( microtime( true ) - $time1 ) > BE_MU_PLUGIN_LIMIT_TIME || $request_processed_sites_count == BE_MU_PLUGIN_LIMIT_SITES ) {

            $is_request_limit_reached = 1;
            break;
        }
    }

    // This is the data to pass to the javascript function in json format
    $json_result = array(
        'requestProcessedSitesCount' => $request_processed_sites_count,
        'currentOffset' => $offset,
        'isRequestLimitReached' => $is_request_limit_reached,
    );

    echo json_encode( $json_result );

    wp_die();
}

/**
 * Returns the html code for the button that aborts and closes the results layer
 * @return string
 */
function be_mu_plugin_bulk_get_close() {
    return '<input type="button" class="button be-mu-mleft10imp" onclick="pluginControlCloseAbort()" value="'
        . esc_attr__( 'Close', 'beyond-multisite' ) . '" />';
}
