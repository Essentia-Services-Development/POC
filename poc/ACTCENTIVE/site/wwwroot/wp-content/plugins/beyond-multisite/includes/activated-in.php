<?php

/**
 * In this file we have all the hooks and functions related to the activated in module.
 * This module shows in which sites is a plugin or a theme activated in.
 * The whole thing is made a little complicated and it can be made much simpler if we didn't care about huge networks with a lof of sites.
 * The way it is made now should work without using up too much memory and without reaching the maximum execution time regardless of how many sites there are.
 * To achieve this we go through all the sites in chunks and we also store potencially big data in the database for a while (not in memory).
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// All the hooks for the activated in module will run only if the module is turned on
if ( be_mu_get_setting( 'be-mu-activated-in-status-module' ) == 'on' ) {

    // Loads our css file for some pages in the network admin panel
    add_action( 'admin_enqueue_scripts', 'be_mu_activated_in_register_style' );

    // Adds the Activated in? action link under every plugin (that is not network activated or network only) in the plugins page in the network admin panel
    add_filter( 'network_admin_plugin_action_links', 'be_mu_activated_in_plugins_action_link', 10, 2 );

    // Adds the Activated in? action link under every theme in the themes page in the network admin panel
    add_filter( 'theme_action_links', 'be_mu_activated_in_themes_action_link', 10, 2 );

    // Adds our html code for the results layer to the footer of 3 pages in the network admin panel
    add_action( 'admin_footer-plugins.php', 'be_mu_activated_in_results_html', 99999999999 );
    add_action( 'admin_footer-site-themes.php', 'be_mu_activated_in_results_html', 99999999999 );
    add_action( 'admin_footer-themes.php', 'be_mu_activated_in_results_html', 99999999999 );

    /**
     * We made this ajax action and we call a callback function that gives us the number of sites that have the plugin/theme active.
     * And also it adds the ids of the sites in a database table so we can easily view the sites in the results layer even if there are huge amounts of them.
     */
    add_action( 'wp_ajax_be_mu_activated_in_numbers_action', 'be_mu_activated_in_numbers_action_callback' );

    // We made this ajax action and we call a callback function that outputs the results (a table with the sites that have the plugin/theme active)
    add_action( 'wp_ajax_be_mu_activated_in_results_action', 'be_mu_activated_in_results_callback' );

    // Handles the ajax request to export a file with IDs or URLs from the preview results of a activated in task
    add_action( 'wp_ajax_be_mu_activated_in_export_results_action', 'be_mu_activated_in_export_results_callback' );
}

/**
 * Loads our style file and script file for some pages in the network admin panel
 * @param string $hook
 */
function be_mu_activated_in_register_style( $hook ) {
    if ( ( ( 'plugins.php' == $hook && ( ! isset( $_GET['plugin_status'] ) || ( 'dropins' !== $_GET['plugin_status'] && 'mustuse' !== $_GET['plugin_status'] ) ) )
        || 'themes.php' == $hook || 'site-themes.php' == $hook ) && is_network_admin() ) {

        // Registers a style file and enqueues it
        be_mu_register_beyond_multisite_style();

        // Registers and localizes the javascript file for the activated in module
        be_mu_activated_in_register_script();
    }
}

// Registers and localizes the javascript file for the activated in module
function be_mu_activated_in_register_script() {

    // Register the script
    wp_register_script( 'be-mu-activated-in-script', be_mu_plugin_dir_url() . 'scripts/activated-in.js', array(), BEYOND_MULTISITE_VERSION, false );

    // This is the data we will send from the php to the javascript file
    $localize = array(
        'ajaxNonce' => wp_create_nonce( 'activated_in_ajax_nonce' ),
        'getClose' => be_mu_activated_in_get_close(),
        'loadingGIF' => esc_url( be_mu_img_url( 'loading.gif' ) ),
        'processing' => esc_js( esc_html__( 'Processing...', 'beyond-multisite' ) ),
        'errorWriteExport' => esc_js( __( 'Error: Cannot write to export folder.', 'beyond-multisite' ) ),
        'checking' => esc_js( esc_html__( 'Checking...', 'beyond-multisite' ) ),
        'errorError' => esc_js( esc_html__( 'Error', 'beyond-multisite' ) ),
        'downloadIDs' => esc_js( esc_html__( 'Download IDs', 'beyond-multisite' ) ),
        'downloadURLs' => esc_js( esc_html__( 'Download URLs', 'beyond-multisite' ) ),
        'errorRequest' => esc_js( __( 'Error: There is another request that is still running. Please wait a few seconds and try again. '
            . 'If this problem continues, please reload the page.', 'beyond-multisite' ) ),
        'errorAccess' => esc_js( __( 'Error: You do not have sufficient permissions to make this request.', 'beyond-multisite' ) ),
        'errorNetworkActivated' => esc_js( __( 'Error: This plugin is network activated.', 'beyond-multisite' ) ),
        'errorNoDatabaseTable' => esc_js( __( 'Error: There is at least one database table missing. '
            . 'Please deactivate Beyond Multisite and activate it to trigger database tables creation again.', 'beyond-multisite' ) ),
        'errorResponse' => esc_js( __( 'Error: We got an empty response.', 'beyond-multisite' ) ),
        'errorInvalidNonce' => esc_js( __( 'Error: Invalid security nonce. Please reload the page and try again.', 'beyond-multisite' ) ),
        'checkedSoFar' => esc_js( esc_html__( 'Sites checked so far:', 'beyond-multisite' ) ),
        'errorServerFail' => esc_js( __( 'Error: Unexpected server error. If you have WordPress debugging and logging enabled, '
            . 'you should be able to see more details about the error in the /wp-content/debug.log file.', 'beyond-multisite' ) ),
    );

    // We localize the script - we send php data to the javascript file
    wp_localize_script( 'be-mu-activated-in-script', 'localizedActivatedIn', $localize );

    // Enqueued script with localized data
    wp_enqueue_script( 'be-mu-activated-in-script', '', array(), false, true );
}

/**
 * Returns the html code for the button that aborts and closes the results layer
 * @return string
 */
function be_mu_activated_in_get_close() {
    return '<input type="button" class="button" onclick="activatedInAbortClose()" value="' . esc_attr__( 'Close', 'beyond-multisite' ) . '" />';
}

// Outputs the html code for the results layer in the footer of the some pages in the network admin panel
function be_mu_activated_in_results_html() {

    // We output the code only for network admin pages
    if ( is_network_admin() ) {

    ?>

        <!-- these layers will contain the results of the ajax requests - the list of sites where a plugin or theme is activated in -->
        <div id="be-mu-activated-in-container" class="be-mu-div-contain-results">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
                <tr>
                    <td valign="middle">
                        <div id="be-mu-activated-in-div-results">

                        </div>
                    </td>
                </tr>
            </table>
        </div>

    <?php

    }
}

/**
 * Gives us the number of sites that have the plugin/theme active and also it adds the ids of the sites in a database table so we can
 * Easily view the sites in the results layer even if there are huge amounts of them
 */
function be_mu_activated_in_numbers_action_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'activated_in_ajax_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
    $db_table = $main_blog_prefix . "be_mu_activated_in";

    if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $db_table . "'" ) !== $db_table ) {
        wp_die( 'no-database-table' );
    }

    // Get the current microtime
    $time1 = microtime( true );

    // The plugin file or theme name
    $plugin_file_or_theme_name = wp_filter_nohtml_kses( $_POST['plugin_file_or_theme_name'] );

    // The task id string that helps us find the data for this task in the database
    $task_id = wp_filter_nohtml_kses( $_POST['task_id'] );

    // Is it a plugin or a theme that we are checking
    $plugin_or_theme = $_POST['plugin_or_theme'];

    // How many sites to skip when we check them (if we are skipping it means we are checking them in chunks, not at once)
    $offset = intval( $_POST['offset'] );

    // A limit for the number of sites to check in the current request
    $limit_sites = 700;

    // A limit for the time in seconds that we allow this request to run
    $limit_time = 10;

    // If it is a plugin and it is network active we will show an error
    if ( 'plugin' == $plugin_or_theme && is_plugin_active_for_network( $plugin_file_or_theme_name ) ) {
        wp_die( 'network-activated' );
    }

    /**
     * We will check for the time passed on every $check_time_on_every site (just so we do not do it on every single site).
     * So this is basically also the minimum amount of sites we will check on every request.
     */
    $check_time_on_every = 10;

    /*
     * The settings for the get_sites() function.
     * It has to be a number in the 'number' key here for the offset to work, and default is null.
     */
    $get_sites_arguments = array(
        'offset' => $offset,
        'number' => $limit_sites,
    );

    /**
     * $count_activated is the number of sites from the current request in which the plugin is activated, $count_deactivated is the number of deactivated sites.
     * $limit_reached is whether we have reached a limit in the current request for the time or sites (1) or we have not reached it (0).
     * If we have not reached a limit and the request ends - this means that we are done with all sites in the network.
     */
    $count_activated = $count_deactivated = $limit_reached = 0;

    // In this var we will make a string of comma-separated site ids and we will store them in the database
    $string_activated_ids = "";

    // We get all the sites for the current request
    $sites = get_sites( $get_sites_arguments );

    // We go through all the sites
    foreach ( $sites as $site_object ) {

        // The id of the current site in the foreach
        $site_id = intval( $site_object->id );

        // This if decides if we are checking a theme or a plugin
        if ( 'theme' == $plugin_or_theme ) {

            // We get the stylesheet of the currently activated theme of the current site we are checking
            $stylesheet = get_blog_option( $site_id, 'stylesheet' );

            // From the stylesheet we get the theme object and the theme name from there
            $object_theme = wp_get_theme( $stylesheet );
            $site_activated_theme = $object_theme->Name;

            /**
             * If the theme name of the theme we are checking is the same as the theme name of the theme of the current site, we increase $count_activated
             * And we also add the site id to $string_activated_ids (we are using esc_js just because we have used it on the other one too - to be the same)
             */
            if ( $plugin_file_or_theme_name == esc_js( $site_activated_theme ) ) {
                $count_activated++;
                $string_activated_ids .= $site_id . ",";
            } else {

                // If the themes are different we increase the var that has the number of sites where the theme is deactivated in
                $count_deactivated++;
            }

        // We are checking a plugin
        } else {

            // We get all the active plugins for the current site we are checking
            $site_activated_plugins = get_blog_option( $site_id, 'active_plugins' );

            /**
             * If the plugin we are checking now is in the active ones for the site, we increase the $count_activated variable,
             * otherwise we increase $count_deactivated.
             */
            if ( in_array( $plugin_file_or_theme_name, $site_activated_plugins ) ) {
                $count_activated++;
                $string_activated_ids .= $site_id . ",";
            } else {
                $count_deactivated++;
            }
        }

        // The variable shows for how many sites we have get the data so far in the current request
        $current_sites_done = $count_deactivated + $count_activated;

        // On every $check_time_on_every-th site we check the time (this is just so we do not check every single time)
        if ( 0 == ( $current_sites_done % $check_time_on_every ) ) {

            // Get the current microtime
            $time2 = microtime( true );

            // If more than $limit_time seconds have passed we stop and we set $limit_reached to 1 so we know that a limit stopped us and we are not done
            if ( ( $time2 - $time1 ) > $limit_time ) {
                $limit_reached = 1;
                break;
            }
        }

        // If we checked more than $limit_sites we stop and we set $limit_reached to 1 so we know that a limit stopped us and we are not done
        if ( $current_sites_done == $limit_sites ) {
            $limit_reached = 1;
            break;
        }
    }

    // Inserts a comma-separated list of ids of sites where a plugin/theme is activated in (if there are any in the current request)
    if ( ! empty( $string_activated_ids ) ) {
        be_mu_activated_in_add_task_data( $task_id, $string_activated_ids );
    }

    // This is the data to pass to the javascript function in json format
    $json_result = array(
        'activatedCount' => $count_activated,
        'deactivatedCount' => $count_deactivated,
        'currentOffset' => $offset,
        'limitReached' => $limit_reached,
    );

    echo json_encode( $json_result );

    wp_die();
}


// Outputs the results (a table with the sites that have the plugin/theme active)
function be_mu_activated_in_results_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'activated_in_ajax_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // The plugin file name or the theme name
    $plugin_file_or_theme_name = wp_filter_nohtml_kses( $_POST['plugin_file_or_theme_name'] );

    // Is it a plugin or a theme
    $plugin_or_theme = $_POST['plugin_or_theme'];

    // We set the $plugin_or_theme_name with the name of the plugin or the theme (for a theme we already have it, but for a plugin we get it based on the file)
    if ( 'theme' == $plugin_or_theme ) {
        $plugin_or_theme_name = $plugin_file_or_theme_name;
    } else {
        $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file_or_theme_name );
        $plugin_or_theme_name = $plugin_data['Name'];
    }

    // The current page num we are showing results for
    $page_number = intval( $_POST['page_number'] );

    // The current task id that tells us which rows to get from the database
    $task_id = wp_filter_nohtml_kses( $_POST['task_id'] );

    // The number of sites the plugin or theme is activated in
    $count_activated = intval( $_POST['count_activated'] );

    // The number of sites the plugin or theme is deactivated in
    $count_deactivated = intval( $_POST['count_deactivated'] );

    // The total number of sites there are
    $count_all = $count_activated + $count_deactivated;

    // How many sites to show per page
    $per_page = 10;

    // We calculate the total number of pages with sites where the plugin or theme is activated in
    $pages_count = ceil( $count_activated / $per_page );

    // The $activated_in_string variable holds the text result we show
    if ( 1 != $count_activated ) {
        $activated_in_string = sprintf( esc_html__( 'Activated in %1$d sites (out of %2$d in total)', 'beyond-multisite' ), $count_activated, $count_all );
    } else {
        $activated_in_string = sprintf( esc_html__( 'Activated in 1 site (out of %d in total)', 'beyond-multisite' ), $count_all );
    }

    ?>

    <div class='be-mu-p20'>
        <h2 class='be-mu-activated-in-h2'>
            <?php echo esc_html( $plugin_or_theme_name ); ?>
            <div class='be-mu-right be-mu-pleft10'><?php echo be_mu_activated_in_get_close(); ?></div>
        </h2>

        <p class='be-mu-1-15-em'>
            <b>
                <?php echo esc_html( $activated_in_string ); ?>
            </b>
        </p>

        <?php

        // If there are any sites where the plugin or theme is activated in we will show a table with the sites
        if ( $count_activated > 0 ) {

            global $wpdb;
            $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
            $db_table = $main_blog_prefix . "be_mu_activated_in";
            $chunks_count = $wpdb->get_var( $wpdb->prepare( 'SELECT count(row_id) FROM ' . $db_table . ' WHERE task_id = %s', $task_id ) );

            // We get the site ids for the sites to be displayed in the current page of results
            $task_data_multi_array = be_mu_get_task_data( $task_id, 'calculate', $page_number, $per_page, 'be_mu_activated_in', array( 'activated_in' ) );
            $site_ids = $task_data_multi_array['activated_in'];

            // These are the settings for the get_sites function, we set it to get only the sites we want
            $get_sites_arguments = array( 'site__in' => $site_ids );

            // We get the sites into an array with the site objects
            $sites = get_sites( $get_sites_arguments );

            // If the sites do not fit on one page we display a text saying which sites are showed now and which page
            if ( $count_activated > $per_page ) {
                $to_results = $per_page * $page_number;
                if( $to_results > $count_activated ) {
                    $to_results = $count_activated;
                }

                echo '<p>';
                printf(
                    esc_html__( 'Showing results %1$d - %2$d on page %3$d/%4$d', 'beyond-multisite' ),
                    ( ( $page_number - 1) * $per_page ) + 1,
                    $to_results,
                    $page_number,
                    $pages_count
                );
                echo '</p>';

            }

            // If we were able to get the sites, we display the table with them
            if ( ! empty( $sites ) ) {
                echo '<table class="be-mu-table be-mu-mtop20">';

                echo '<thead>'
                    . '<tr>'
                    . '<th>' . esc_html__( 'ID', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'URL', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'Actions', 'beyond-multisite' ) . '</th>'
                    . '</tr>'
                    . '</thead>';

                echo '<tbody>';

                // For each site we show a row with the site id, site url and some action links
                foreach ( $sites as $site_object ) {
                    $site_details = get_blog_details( $site_object->id );
                    $site_url = $site_details->siteurl;

                    echo '<tr>'
                        . '<td data-title="' . esc_attr__( 'ID', 'beyond-multisite' ) . '">' . esc_html( $site_object->id ) . '</td>'
                        . '<td data-title="' . esc_attr__( 'URL', 'beyond-multisite' ) . '" class="be-mu-break-word">'
                        . '<a href="' . esc_url( $site_url ) . '" target="_blank">' . esc_url( $site_url ) . '</a></td>'
                        . '<td data-title="' . esc_attr__( 'Actions', 'beyond-multisite' ) . '"><a href="'
                        . esc_url( network_admin_url( 'site-info.php?id=' . intval( $site_object->id ) ) )
                        .'" target="_blank">' . esc_html__( 'Edit', 'beyond-multisite' ) . '</a> | ';

                    if ( 'theme' == $plugin_or_theme ) {
                        echo '<a href="' . esc_url( get_admin_url( intval( $site_object->id ), 'themes.php' ) )
                            . '" target="_blank">' . esc_html__( 'Themes', 'beyond-multisite' ) . '</a>';
                    } else {
                        echo '<a href="' . esc_url( get_admin_url ( intval( $site_object->id ), 'plugins.php' ) )
                            . '" target="_blank">' . esc_html__( 'Plugins', 'beyond-multisite' ) . '</a>';
                    }

                    echo '</td>';
                    echo '</tr>';
                }

                echo '</tbody>';

                echo '<tfoot>'
                    . '<tr>'
                    . '<th>' . esc_html__( 'ID', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'URL', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'Actions', 'beyond-multisite' ) . '</th>'
                    . '</tr>'
                    . '</tfoot>';

                echo '</table>';
            }

            // If the sites do not fit on one page we show this dropdown menu to choose a page number to display
            if ( $count_activated > $per_page ) {
                echo '<p class="be-mu-activated-in-preview-page-navigation be-mu-left"><label for="be-mu-activated-in-page-number">'
                    . esc_html__( 'Go to page:', 'beyond-multisite' ) . '</label>'
                    . '<select onchange="activatedInResults()" id="be-mu-activated-in-page-number" name="be-mu-activated-in-page-number" size="1">';

                // We go through all the pages and display an option and we mark the current page as the selected option in the dropdown menu
                for ( $i = 0; $i < $pages_count; $i++ ) {
                    if( $page_number == ( $i + 1 ) ) {
                        $selected_string = 'selected="selected"';
                    } else {
                        $selected_string = '';
                    }

                    echo  '<option ' . $selected_string . ' value="' . esc_attr( $i + 1 ) . '">' . esc_html( $i + 1 ) . '</option>';
                }

                echo '</select>';

                if ( $page_number > 1 ) {
                    echo '&nbsp;<span onclick="activatedInNextPreviousPage(' . intval( $page_number - 1 ) . ')" '
                        . 'class="dashicons dashicons-arrow-left-alt2" title="' . esc_attr__( 'Previous Page', 'beyond-multisite' ) . '"></span>';
                }

                if ( $pages_count > $page_number ) {
                    echo '&nbsp;<span onclick="activatedInNextPreviousPage(' . intval( $page_number + 1 ) . ')" '
                        . 'class="dashicons dashicons-arrow-right-alt2" title="' . esc_attr__( 'Next Page', 'beyond-multisite' ) . '"></span>';
                }

                echo '&nbsp;<img id="be-mu-activated-in-loading-page-number" src="' . esc_url( be_mu_img_url( 'loading.gif' ) ) . '" /></p>';
            }

            echo '<p id="be-mu-activated-in-bottom-actions" class="be-mu-right-txt">'
                . '<a id="be-mu-activated-in-export-ids-link" href="javascript:activatedInExportResults(\''
                . esc_js( esc_attr( $task_id ) ) . '\', \'ids\', 0, ' . intval( $chunks_count ) . ' )">'
                . esc_html__( 'Export Site IDs', 'beyond-multisite' ) . '</a> | '
                . '<a id="be-mu-activated-in-export-urls-link" href="javascript:activatedInExportResults(\''
                . esc_js( esc_attr( $task_id ) ) . '\', \'urls\', 0, ' . intval( $count_activated ) . ' )">'
                . esc_html__( 'Export Site URLs', 'beyond-multisite' ) . '</a></p>';
        }

        ?>

    </div>

    <?php

    wp_die();
}

/**
 * Adds the Activated in? action link under every plugin (that is not network only or network activated or a dropin)
 * in the plugins page in the network admin panel
 * @param array $actions
 * @param string $plugin_file
 * @return array
 */
function be_mu_activated_in_plugins_action_link( $actions, $plugin_file ) {
    if ( ! isset( $_GET['plugin_status'] ) || ( 'dropins' !== $_GET['plugin_status'] && 'mustuse' !== $_GET['plugin_status'] ) ) {
        $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file );
        if ( ! is_plugin_active_for_network( $plugin_file ) && true !== $plugin_data['Network'] ) {
            return be_mu_add_element_to_array( $actions, 'be-mu-activated-in',
                '<a href="javascript:activatedInStart( \'' . esc_attr( esc_js( $plugin_file ) ) . '\', \'plugin\' )">'
                    . esc_html__( 'Activated in?', 'beyond-multisite' ) . '</a>', 'edit' );
        }
    }
    return $actions;
}

/**
 * Adds the Activated in? action link under every theme in the themes page in the network admin panel
 * @param array $actions
 * @param object $theme
 * @return array
 */
function be_mu_activated_in_themes_action_link( $actions, $theme ) {
    return be_mu_add_element_to_array( $actions, 'be-mu-activated-in',
        '<a href="javascript:activatedInStart( \'' . esc_js( $theme->Name ) . '\', \'theme\' )">'
                . esc_html__( 'Activated in?', 'beyond-multisite' ) . '</a>', 'edit' );
}


// Creates a database table to store the data for the activated in module
function be_mu_activated_in_db_table() {
    
    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // This is the query that will create the database table if it does not exist already
    $sql = 'CREATE TABLE IF NOT EXISTS ' . $main_blog_prefix . 'be_mu_activated_in ( '
        . 'row_id bigint( 20 ) NOT NULL AUTO_INCREMENT, '
        . 'task_id varchar( 10 ) DEFAULT NULL, '
        . 'activated_in longtext DEFAULT NULL, '
        . 'unix_time_added int( 11 ) NOT NULL, '
        . 'PRIMARY KEY ( row_id ) '
        . ') ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;';

    // Execute the query
    dbDelta( $sql );
}

/**
 * Inserts a comma-separated list of ids of sites where a plugin/theme is activated in.
 * Every row is with data from a different request, but could be for the same plugin/theme if they have the same task id.
 * @param string $task_id
 * @param string $activated_in_string
 */
function be_mu_activated_in_add_task_data( $task_id, $activated_in_string ) {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
    $db_table = $main_blog_prefix . 'be_mu_activated_in';

    // Insert the activated in data to the database
    $wpdb->insert(
    	$db_table,
    	array(
    		'task_id' => $task_id,
    		'activated_in' => $activated_in_string,
    		'unix_time_added' => time(),
    	),
    	array(
    		'%s',
    		'%s',
    		'%d',
    	)
    );
}

// Handles the ajax request to export a file with IDs or URLs from the preview results of a activated in task
function be_mu_activated_in_export_results_callback() {

    // We start a timer to know how much time the current request runs and abort if the time limit is reached
    $time1 = microtime( true );

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'activated_in_ajax_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    global $wpdb;

    // The count of the processed sites on the current request. Only for exporting URLs.
    $request_processed_sites_count = 0;

    // The count of the processed chunks with sites on the current request. Only for exporting IDs.
    $request_processed_site_chunks_count = 0;

    // If it is set to 1 it means that a time or site limit is reached for the current request
    $is_request_limit_reached = 0;

    // How many site chunks (or sites if we are exporting URLs) we have skipped this request
    $skipped = 0;

    $task_id = be_mu_strip_all_but_digits_and_letters( $_POST['task_id'] );
    $field = be_mu_strip_all_but_digits_and_letters( $_POST['field'] );

    // How many site chunks (or sites if we are exporting URLs) to skip, because they were done in previous requests.
    $offset = intval( $_POST['offset'] );

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    $db_table = $main_blog_prefix . 'be_mu_activated_in';

    // Get the data for the current task
    $results_multi_array = $wpdb->get_results( $wpdb->prepare( "SELECT activated_in FROM " . $db_table
        . " WHERE task_id = %s", $task_id ), ARRAY_A );

    // Get the upload folder path and set the path to the export file
    $upload_dir = wp_upload_dir();
    $folder_path = $upload_dir['basedir'] . '/be_mu_exports/';
    $file_path = $upload_dir['basedir'] . '/be_mu_exports/' . $task_id . '-' . $field . '.txt';
    $file_url = $upload_dir['baseurl'] . '/be_mu_exports/' . $task_id . '-' . $field . '.txt';

    // Delete the export file if it exists to avoid appending the data to an existing data from previous export. But only if the export is starting now.
    if ( file_exists( $file_path ) && 0 === $offset ) {
        unlink( $file_path );
    }

    // If the folder does not exist, create it
    if ( ! file_exists( $folder_path ) ) {
        mkdir( $folder_path, 0755, true );
    }

    // If we do not have permission to write in the folder, return an error
    if ( ! is_writable( $folder_path ) ) {
        wp_die( 'cannot-write' );
    }

    // Exporting IDs (in chunks)
    if ( 'ids' === $field ) {

        if ( ! empty( $results_multi_array ) ) {

            $total_count = count( $results_multi_array );
            $current_number = 0;

            foreach ( $results_multi_array as $results ) {

                $current_number++;

                // We skip some of the data that was done in previous request(s) (if needed)
                if ( $offset > $skipped ) {
                    $skipped++;
                    continue;
                }

                // On the last piece of data we remove the last comma
                if ( $current_number === $total_count && substr( $results['activated_in'], -1 ) === ',' ) {
                    $results['activated_in'] = substr_replace( $results['activated_in'], "", -1 );
                }

                // Write the data to the file
                be_mu_activated_in_write_to_export_file( $results['activated_in'], $file_path );

                $request_processed_site_chunks_count++;

                // If 20 seconds have passed for this request, we will stop and continue on the next one
                if ( ( microtime( true ) - $time1 ) > 20 ) {
                    $is_request_limit_reached = 1;
                    break;
                }
            }
        }

    // Exporting URLs (one by one)
    } else {

        if ( ! empty( $results_multi_array ) ) {

            $total_multi_count = count( $results_multi_array );
            $current_multi_number = 0;

            foreach ( $results_multi_array as $results ) {

                $current_multi_number++;
                $site_ids = $results['activated_in'];

                // We remove the last comma
                if ( substr( $site_ids, -1 ) === ',' ) {
                    $site_ids = substr_replace( $site_ids, "", -1 );
                }

                $site_ids_array = explode( ',', $site_ids );
                $array_count = count( $site_ids_array );
                $current_array_number = 0;

                foreach ( $site_ids_array as $site_id ) {

                    $current_array_number++;

                    // We skip some of the data that was done in previous request(s) (if needed)
                    if ( $offset > $skipped ) {
                        $skipped++;
                        continue;
                    }

                    $site_details = get_blog_details( $site_id );

                    // On the last piece of data we remove the last comma
                    if ( $total_multi_count === $current_multi_number && $current_array_number === $array_count ) {
                        $add_comma = '';
                    } else {
                        $add_comma = ',';
                    }

                    // Write the data to the file
                    be_mu_activated_in_write_to_export_file( esc_url( $site_details->siteurl ) . $add_comma, $file_path );

                    $request_processed_sites_count++;

                    // If 20 seconds have passed for this request, we will stop and continue on the next one
                    if ( ( microtime( true ) - $time1 ) > 20 ) {
                        $is_request_limit_reached = 1;
                        break 2;
                    }
                }
            }
        }
    }

    // This is the data to pass to the javascript function in json format
    $json_result = array(
        'requestProcessedSitesCount' => $request_processed_sites_count,
        'requestProcessedSiteChunksCount' => $request_processed_site_chunks_count,
        'fileURL' => esc_url( $file_url ),
        'currentOffset' => $offset,
        'isRequestLimitReached' => $is_request_limit_reached,
    );

    echo json_encode( $json_result );

    wp_die();
}

// Adds data to a file
function be_mu_activated_in_write_to_export_file( $string, $file_path ) {
    if ( ! file_exists( $file_path ) || filesize( $file_path ) < 50000000 ) {
        file_put_contents( $file_path, $string, FILE_APPEND | LOCK_EX );
    }
}
