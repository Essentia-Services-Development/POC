<?php

/**
 * In this file we have all the hooks and functions related to the copy maker module.
 * Most of the work of copying a site is done by the function be_mu_copy_sites_process_action_callback, which can be called multiple times, if it cannot
 * finish the job in 15 seconds. It is pretty long and complicated because of few reasons: it can stop at many points and then continue from the
 * same point in the next request, it logs most of the actions and errors for future debugging, and the third reason is
 * that there are just a lot of things to do in order to properly copy a site.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// All the hooks and global variables for the copy maker module will be created only if the module is turned on
if ( be_mu_get_setting( 'be-mu-copy-status-module' ) == 'on' ) {

    // Adds a submenu page for the logs of copied sites under the Sites menu in the network admin panel and also loads a css style on that page
    add_action( 'network_admin_menu', 'be_mu_add_copy_sites_logs_menu' );

    // Adds the action link, that allows us to copy a site, under each site (except the main one) in the network Sites table
    add_filter( 'manage_sites_action_links', 'be_mu_copy_sites_action_link', 10, 3 );

    // Outputs the html code for the results layer in the footer of the network sites page
    add_action( 'admin_footer-sites.php', 'be_mu_copy_sites_results_html', 99999999999 );

    // Loads our css and js files for the sites page in the network admin panel
    add_action( 'admin_enqueue_scripts', 'be_mu_copy_register_style_and_script' );

    /**
     * jax call to a function that processes the actual copying of the site. It can be called many times until it finishes, and it will continue
     * from where it left off. This way we can copy big sites with a lot of files without the script timing out.
     */
    add_action( 'wp_ajax_be_mu_copy_sites_process_action', 'be_mu_copy_sites_process_action_callback' );

    // Ajax call to a function that outputs in json format some site information we need for the confirmation message we show before we start the copy process
    add_action( 'wp_ajax_be_mu_copy_sites_confirm_action', 'be_mu_copy_sites_confirm_action_callback' );

    // Depending on the WordPress version we use a different hook to copy the template site after a new site is created or normal copy to new site
    if ( has_action( 'wp_initialize_site' ) ) {
        add_action( 'wp_initialize_site', 'be_mu_copy_sites_on_site_creation', 10, 2 );
    } else {
        add_action( 'wpmu_new_blog', 'be_mu_copy_sites_on_site_creation_old_wordpress', 10, 6 );
    }

    // Shows a message in the network admin add new site screen after a site is created and it is replaced with the template site
    add_action( 'network_admin_notices', 'be_mu_copy_sites_network_notice_after_add_site' );

    // With this if the super admin activates a signup from the pending users module, we can skip the super admin check
    $GLOBALS['be_mu_copy_skip_super_check'] = 'no';

    // Shows a notice inside the form on the network admin add new site screen, saying that the new site will be replaced with the template site
    add_action( 'network_site_new_form', 'be_mu_copy_sites_network_notice_before_add_site' );

    // If the currently visited site is not fully copied we continue the process
    add_action( 'init', 'be_mu_copy_sites_maybe_continue_copy_site' );

    // On WP-CLI init, creates a command for copying sites
    add_action( 'cli_init', 'be_mu_copy_sites_wp_cli_init' );

    // Global arrays that hold the paths of the files and folders we will copy or delete
    $GLOBALS['be_mu_copy_sites_delete_files'] = $GLOBALS['be_mu_copy_sites_delete_folders']
        = $GLOBALS['be_mu_copy_sites_copy_files'] = $GLOBALS['be_mu_copy_sites_copy_folders'] = Array();

    // The number of seconds to work on each request
    $GLOBALS['be_mu_copy_limit_time'] = 15;

    // These will hold the URL that we need to find and replace and the one to replace with
    $GLOBALS['be_mu_copy_site_search_for_url'] = $GLOBALS['be_mu_copy_site_replace_with_url'] = '';

    // Will hold a log with actions taken in the copy process
    $GLOBALS['be_mu_copy_log'] = '';
}

// Adds a submenu page for the logs of copied sites under the Sites menu in the network admin panel and also loads a style and script on that page
function be_mu_add_copy_sites_logs_menu() {
    $copy_sites_logs_page = add_submenu_page(
        'sites.php',
        esc_html__( 'Logs of copied sites', 'beyond-multisite' ),
        esc_html__( 'Logs of copied sites', 'beyond-multisite' ),
        'manage_network',
        'be_mu_copy_sites_logs',
        'be_mu_copy_sites_logs_subpage'
    );
    add_action( 'load-' . $copy_sites_logs_page, 'be_mu_add_beyond_multisite_style' );
    add_action( 'load-' . $copy_sites_logs_page, 'be_mu_copy_register_script' );
}

// This function is executed when the Logs of copied sites subpage is opened
function be_mu_copy_sites_logs_subpage() {

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'beyond-multisite' ) );
    }
    ?>

    <div class="wrap">

        <?php

        // We need these to connect to the database
        global $wpdb;
        $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
        $db_table = $main_blog_prefix . 'be_mu_logs';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $db_table . "'" ) !== $db_table ) {
            ?>
            <div class="be-mu-warning-box">
                <?php
                printf(
                    esc_html__( '%sERROR:%s At least one database table is missing. '
                        . 'Please deactivate the plugin and activate it to trigger the database tables creation again.', 'beyond-multisite' ), '<b>', '</b>'
                );
                ?>
            </div>
            <?php

        } else {

            // This var will hold a message to show after an action, but it will be empty if there are no actions made
            $action_message = '';

            // We are deleting a selected log based on the task id
            if ( isset( $_GET['action'] ) && isset( $_GET['task_id'] ) && 'delete' === $_GET['action'] ) {

                // Delete the log from the database
                $status = $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $db_table . ' WHERE task_id = %s',
                    be_mu_strip_all_but_digits_and_letters( $_GET['task_id'] ) ) );

                // Based on the status of the action we will display either a successful message or an error
                if ( $status ) {
                    $action_message = '<div class="be-mu-clear notice notice-success is-dismissible">'
                        . '<p>' . esc_html__( 'The operation was successful.', 'beyond-multisite' ) . '</p>'
                        . '</div>';
                } else {
                    $action_message = '<div class="be-mu-clear notice notice-error is-dismissible">'
                        . '<p>' . esc_html__( 'An error occurred.', 'beyond-multisite' ) . '</p>'
                        . '</div>';
                }

            // We are viewing a selected log based on the task id
            } elseif ( isset( $_GET['action'] ) && isset( $_GET['task_id'] ) && 'view' === $_GET['action'] ) {

                // We get the task ID and strip all invalid characters
                $task_id = be_mu_strip_all_but_digits_and_letters( $_GET['task_id'] );

                // We output the header of the page
                be_mu_header_super_admin_page( __( 'Log for task ID:', 'beyond-multisite' ) . ' ' . esc_html( $task_id ) );

                // We get the log data for the selected task id
                $results_array = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $db_table
                    . " WHERE type = 'copy sites' and task_id = %s", $task_id ), ARRAY_A );

                // If this log exists, we show it
                if ( ! empty( $results_array ) ) {
                    echo '<div class="be-mu-white-box be-mu-w100per">';
                    echo esc_html__( 'Number of errors in this log:', 'beyond-multisite' ) . ' ' . substr_count( $results_array['log'],
                        '<b class="be-mu-red">Error:');
                    echo '<br />';
                    echo esc_html__( 'Scroll to:', 'beyond-multisite' );
                    echo '&nbsp;';
                    echo '<a href="#stage1">' . esc_html__( sprintf( 'Stage %d', 1 ), 'beyond-multisite' ) . '</a> | ';
                    echo '<a href="#stage2">' . esc_html__( sprintf( 'Stage %d', 2 ), 'beyond-multisite' ) . '</a> | ';
                    echo '<a href="#stage3">' . esc_html__( sprintf( 'Stage %d', 3 ), 'beyond-multisite' ) . '</a> | ';
                    echo '<a href="#stage4">' . esc_html__( sprintf( 'Stage %d', 4 ), 'beyond-multisite' ) . '</a> | ';
                    echo '<a href="#stage5">' . esc_html__( sprintf( 'Stage %d', 5 ), 'beyond-multisite' ) . '</a> | ';
                    echo '<a href="#stage6">' . esc_html__( sprintf( 'Stage %d', 6 ), 'beyond-multisite' ) . '</a> | ';
                    echo '<a href="#stage7">' . esc_html__( sprintf( 'Stage %d', 7 ), 'beyond-multisite' ) . '</a> | ';
                    echo '<a href="#stage8">' . esc_html__( sprintf( 'Stage %d', 8 ), 'beyond-multisite' ) . '</a> | ';
                    echo '<a href="#stage9">' . esc_html__( sprintf( 'Stage %d', 9 ), 'beyond-multisite' ) . '</a>';
                    echo '</div>';
                    echo '<div class="be-mu-white-box be-mu-w100per be-mu-break-word">';
                    echo wp_kses_post( $results_array['log'] );
                    echo '</div>';

                // If there is no data for this log, we show a message
                } else {
                    echo '<div class="be-mu-white-box be-mu-w100per">';
                    echo esc_html__( 'There is no data for this task ID.', 'beyond-multisite' );
                    echo '</div>';
                }
            }

            // We are viewing the list of logs
            if ( ! isset( $_GET['action'] ) || ! isset( $_GET['task_id'] ) || 'view' !== $_GET['action'] ) {

                // How much logs to show per page
                $per_page = 12;

                // We get the total count of all logs of copied sites
                $total_logs_count = $wpdb->get_var( "SELECT COUNT( row_id ) FROM " . $db_table . " WHERE type = 'copy sites'" );

                // We calculate the number of pages we will shows them in
                $pages_count = ceil( $total_logs_count / $per_page );

                // We set the current page number
                if ( isset( $_GET['page_number'] ) ) {
                    $page_number = intval( $_GET['page_number'] );
                } else {
                    $page_number = 1;
                }

                // If the logs do not fit on one page, we will display the page number in the title with this variable
                if ( $total_logs_count > $per_page ) {
                    $page_number_heading = ' - ' . esc_html__( 'Page', 'beyond-multisite' ) . ' ' . $page_number . '/' . $pages_count;
                } else {
                    $page_number_heading = '';
                }

                // The title of the page
                $page_title = esc_html__( 'Logs of copied sites', 'beyond-multisite' ) . esc_html( $page_number_heading );

                // We output the header of the page
                be_mu_header_super_admin_page( $page_title );

                // Display a message after an action (or empty string if there are no actions made)
                echo $action_message;

                // We get the setting about how many logs to store
                $to_store = be_mu_get_setting( 'be-mu-copy-store-logs', 'Last 20' );

                // If there is a limit set on the number of logs to store, we show a message
                if ( 'All' !== $to_store ) {

                    // We extract the count of the logs to store form the settings string
                    $to_store_parts = explode( ' ', $to_store );
                    $to_store_count = intval( $to_store_parts[1] );

                    // If something went wrong and the value is not valid, we use the value 20
                    if ( $to_store_count < 10 || $to_store_count > 500 ) {
                        $to_store_count = 20;
                    }

                    echo '<div class="be-mu-white-box be-mu-w100per">';
                    printf(
                        esc_html__( 'Only the last %1$d logs are currently beeing stored. You can change this from the %2$splugin settings%3$s for the '
                        . 'Copy Maker module.', 'beyond-multisite' ),
                        $to_store_count, '<a href="' . esc_url( network_admin_url( 'admin.php?page=beyond-multisite' ) ) . '">', '</a>'
                    );
                    echo '</div>';
                }

                // If there is no data, then there are no logs of copied sites
                if ( empty( $total_logs_count ) ) {
                    echo '<div class="be-mu-white-box be-mu-w100per">'
                        . esc_html__( 'There are no logs of copied sites at the moment.', 'beyond-multisite' )
                        . '</div>';

                // There are logs to show
                } else {

                    // This is the limit string for the mysql query - it is calculated based on the current page number
                    $limit_string = intval( ( $page_number - 1 ) * $per_page ) . ',' . intval( $per_page );

                    // Get the logs of copied sites
                    $results_multi_array = $wpdb->get_results( "SELECT * FROM " . $db_table . " WHERE type = 'copy sites' ORDER BY unix_time_added DESC LIMIT "
                        . $limit_string, ARRAY_A );

                    // If this is a page number that has no results we display a message, otherwise we show the table with logs
                    if ( empty( $results_multi_array ) && $page_number > 1 ) {
                        echo '<div class="be-mu-white-box be-mu-w100per">'
                        . esc_html__( 'There are no results on this page.', 'beyond-multisite' )
                        . ' <a href="' . esc_url( network_admin_url( 'sites.php?page=be_mu_copy_sites_logs' ) ) . '">'
                        . esc_html__( 'Go to page 1', 'beyond-multisite' )
                        . '</a>.'
                        . '</div>';
                    } else {
                        echo '<table class="be-mu-table be-mu-mbot15 be-mu-w100per">';
                        echo '<thead>'
                            . '<tr>'
                            . '<th>' . esc_html__( 'Task ID', 'beyond-multisite' ) . '</th>'
                            . '<th>' . esc_html__( 'Date and time*', 'beyond-multisite' ) . ' <div class="be-mu-sort-arrow"></div></th>'
                            . '<th>' . esc_html__( 'Errors', 'beyond-multisite' ) . '</th>'
                            . '<th>' . esc_html__( 'Size', 'beyond-multisite' ) . '</th>'
                            . '<th>' . esc_html__( 'Actions', 'beyond-multisite' ) . '</th>'
                            . '</tr>'
                            . '</thead>';
                        echo '<tbody>';

                        // We go through all the logs of copied sites
                        foreach ( $results_multi_array as $results ) {

                            // We strip all invalid characters from the task id
                            $task_id = be_mu_strip_all_but_digits_and_letters( $results['task_id'] );

                            // Now we start showing the actual row with the cells with the log data
                            echo '<tr>';
                            echo '<td data-title="' . esc_attr__( 'Task ID', 'beyond-multisite' ) . '">' . $task_id . '</td>';
                            echo '<td data-title="' . esc_attr__( 'Date and time*', 'beyond-multisite' ) . '">'
                                . esc_html( be_mu_unixtime_to_wp_datetime( $results['unix_time_added'] ) ) . '</td>';
                            echo '<td data-title="' . esc_attr__( 'Errors', 'beyond-multisite' ) . '">'
                                . esc_html( substr_count( $results['log'], '<b class="be-mu-red">Error:')
                                + substr_count( $results['log'], '<b class="be-mu-red">Fatal Error:') ) . '</td>';
                            echo '<td data-title="' . esc_attr__( 'Size', 'beyond-multisite' ) . '">'
                                . esc_html( be_mu_get_string_size( $results['log'] ) ) . '</td>';
                            echo '<td data-title="' . esc_attr__( 'Actions', 'beyond-multisite' )
                                . '"><a href="' . esc_url( network_admin_url( 'sites.php?page=be_mu_copy_sites_logs&task_id='
                                . $task_id . '&action=view' ) ) . '">' .esc_html__( 'View', 'beyond-multisite' )
                                . '</a> <span class="be-mu-gray">|</span> '
                                . '<a class="be-mu-red-link" href="javascript:copyMakerCopySitesLogsActionLink( \'delete\', \''
                                . $task_id . '\' )">' .esc_html__( 'Delete', 'beyond-multisite' ) . '</a>'
                                . '</td>';
                            echo '</tr>';
                        }

                        echo '</tbody>';
                        echo '<tfoot>'
                            . '<tr>'
                            . '<th>' . esc_html__( 'Task ID', 'beyond-multisite' ) . '</th>'
                            . '<th>' . esc_html__( 'Date and time*', 'beyond-multisite' ) . '</th>'
                            . '<th>' . esc_html__( 'Errors', 'beyond-multisite' ) . '</th>'
                            . '<th>' . esc_html__( 'Size', 'beyond-multisite' ) . '</th>'
                            . '<th>' . esc_html__( 'Actions', 'beyond-multisite' ) . '</th>'
                            . '</tr>'
                            . '</tfoot>';
                        echo '</table>';

                        echo '<div class="be-mu-white-box be-mu-w100per">* ';
                        printf(
                            esc_html__( 'The date and time have been converted to your chosen format and timezone. '
                                . 'You can change them in the %1$sGeneral Options%2$s.', 'beyond-multisite' ),
                            '<a href="' . esc_url( admin_url( 'options-general.php' ) ) . '">',
                            '</a>'
                        );
                        echo '</div>';

                        // If the logs do not fit on one page we show this dropdown menu to choose a page number to display
                        if ( $total_logs_count > $per_page ) {
                            echo '<div class="be-mu-white-box be-mu-w100per">'
                                . '<label for="be-mu-copy-sites-logs-page-number">'
                                . esc_html__( 'Go to page:', 'beyond-multisite' )
                                . '</label> '
                                . '<select onchange="copyMakerGoToCopySitesLogsPage()" id="be-mu-copy-sites-logs-page-number" '
                                . 'name="be-mu-copy-sites-logs-page-number" size="1">';

                            // We go through all the pages and display an option and we mark the current page as the selected option in the dropdown menu
                            for ( $i = 0; $i < $pages_count; $i++ ) {
                                if ( $page_number == ( $i + 1 ) ) {
                                    $selected_string = 'selected="selected"';
                                } else {
                                    $selected_string = '';
                                }

                                echo  '<option ' . $selected_string . ' value="' . esc_attr( $i + 1 ) . '">' . esc_html( $i + 1 ) . '</option>';
                            }

                            echo '</select>';

                            if ( $page_number > 1 ) {
                                echo '&nbsp;<span onclick="copyMakerNextPreviousPage(' . intval( $page_number - 1 ) . ')" '
                                    . 'class="dashicons dashicons-arrow-left-alt2" title="' . esc_attr__( 'Previous Page', 'beyond-multisite' ) . '"></span>';
                            }

                            if ( $pages_count > $page_number ) {
                                echo '&nbsp;<span onclick="copyMakerNextPreviousPage(' . intval( $page_number + 1 ) . ')" '
                                    . 'class="dashicons dashicons-arrow-right-alt2" title="' . esc_attr__( 'Next Page', 'beyond-multisite' ) . '"></span>';
                            }

                            echo '</div>';
                        }
                    }
                }
            }
        }
        ?>

    </div>

    <?php

}

/**
 * Adds the action link, that allows us to copy a site, under each site (except the main one) in the network Sites table
 * @param array $actions
 * @param int $blog_id
 * @param string $blogname
 */
function be_mu_copy_sites_action_link( $actions, $blog_id, $blogname ) {
    if ( be_mu_get_main_site_id() != $blog_id && ! is_main_site( $blog_id ) ) {
        $link = '<a href="javascript:copyMakerCopySiteForm( \'' . intval( $blog_id ) . '\' )">' . esc_html__( 'Copy', 'beyond-multisite' ) . '</a>';
        return be_mu_add_element_to_array( $actions, 'be-mu-copy', $link, 'visit' );
    } else {
        return $actions;
    }
}

/**
 * Loads our style file and script file for the sites pages in the network admin panel
 * @param string $hook
 */
function be_mu_copy_register_style_and_script( $hook ) {
    if ( 'sites.php' == $hook || 'site-new.php' == $hook ) {

        // Registers a style file and enqueues it
        be_mu_register_beyond_multisite_style();
    }
    if ( 'sites.php' == $hook ) {

        // Registers and localizes the javascript file for the copy maker module
        be_mu_copy_register_script();
    }
    if ( 'site-new.php' == $hook && isset( $_GET['be-mu-copy-from'] ) ) {
        be_mu_copy_register_paste_to_new_script();
    }
}

/**
 * Returns the html code for the button that closes the results layer
 * @return string
 */
function be_mu_copy_sites_get_close() {
    return '<input type="button" class="button" onclick="copySitesAbortClose()" value="' . esc_attr__( 'Close', 'beyond-multisite' ) . '" />';
}

/**
 * Returns the html code for the button that aborts and closes the results layer.
 * @return string
 */
function be_mu_copy_sites_get_abort() {
    return '<input type="button" class="button" onclick="copySitesAbortClose()" value="' . esc_attr__( 'Abort', 'beyond-multisite' ) . '" />';
}

// Registers the javascript file for the new site creation network page. The be-mu-copy-from-post parameter is added to the new site form action URL.
function be_mu_copy_register_paste_to_new_script() {
    if ( isset( $_GET['be-mu-copy-from'] ) ) {
        wp_register_script( 'be-mu-copy-to-new-script', be_mu_plugin_dir_url() . 'scripts/copy-to-new.js', array(), BEYOND_MULTISITE_VERSION, false );
        $localize = array(
            'copyFrom' => intval( $_GET['be-mu-copy-from'] ),
        );
        wp_localize_script( 'be-mu-copy-to-new-script', 'localizedCopyToNew', $localize );
        wp_enqueue_script( 'be-mu-copy-to-new-script', '', array(), false, true );
    }
}

// Registers and localizes the javascript file for the copy maker module
function be_mu_copy_register_script() {

    // We set the current page number
    if ( isset( $_GET['page_number'] ) ) {
        $page_number = intval( $_GET['page_number'] );
    } else {
        $page_number = 1;
    }

    // Register the script
    wp_register_script( 'be-mu-copy-script', be_mu_plugin_dir_url() . 'scripts/copy-maker.js', array(), BEYOND_MULTISITE_VERSION, false );

    // This is the data we will send from the php to the javascript file
    $localize = array(
        'ajaxNonce' => wp_create_nonce( 'copy_maker_ajax_nonce' ),
        'pageURL' => esc_js( esc_url( network_admin_url( 'sites.php?page=be_mu_copy_sites_logs' ) ) ),
        'pageNumber' => $page_number,
        'confirmDeleteLog' => esc_js( __( 'Are you sure you want to delete this log? This action cannot be reversed!', 'beyond-multisite' ) ),
        'invalidAction' => esc_js( __( 'Error: Invalid action.', 'beyond-multisite' ) ),
        'warningCopySite' => esc_js( __( 'WARNING! You are about to PERMANENTLY DELETE the site: %1$s'
            . 'and REPLACE it with a copy of the site: %2$s', 'beyond-multisite' ) ) . "\n\n"
            . esc_js( __( 'All database tables with a name that starts with %3$s, and all files and folders inside the folder %4$s will be deleted.',
            'beyond-multisite' ) ) . "\n\n" . esc_js( __( 'Do you want to continue?', 'beyond-multisite' ) ),
        'errorRequest' => esc_js( __( 'Error: There is another request that is still running. Please wait a few seconds and try again. '
            . 'If this problem continues, please reload the page.', 'beyond-multisite' ) ),
        'errorAccess' => esc_js( __( 'Error: You do not have sufficient permissions to make this request.', 'beyond-multisite' ) ),
        'errorInvalidNonce' => esc_js( __( 'Error: Invalid security nonce. Please reload the page and try again.', 'beyond-multisite' ) ),
        'errorMissingPOST' => esc_js( __( 'Error: Some data is missing for the request.', 'beyond-multisite' ) ),
        'errorResponse' => esc_js( __( 'Error: We got an empty response.', 'beyond-multisite' ) ),
        'errorMainSite' => esc_js( __( 'Error: Copying from or pasting into the main site is not allowed.', 'beyond-multisite' ) ),
        'errorUploadPathNotReliable' => esc_js( __( 'Error: It seems that your network is created a long time ago in a very old version of WordPress and it '
            . 'might not be compatible with this module of the plugin. Please contact our support to investigate this. Sorry for the inconvenience.',
            'beyond-multisite' ) ),
        'errorFromSiteNotExist' => esc_js( __( 'Error: The site you are trying to copy from does not exist.', 'beyond-multisite' ) ),
        'errorToSiteNotExist' => esc_js( __( 'Error: The site you are trying to paste into does not exist.', 'beyond-multisite' ) ),
        'errorFromNoSiteURL' => esc_js( __( 'Error: Cannot get the URL of the site to copy from.', 'beyond-multisite' ) ),
        'errorToNoSiteURL' => esc_js( __( 'Error: Cannot get the URL of the site to paste into.', 'beyond-multisite' ) ),
        'errorInvalidFromID' => esc_js( __( 'Error: The site ID for the site to copy from is invalid.', 'beyond-multisite' ) ),
        'errorInvalidToID' => esc_js( __( 'Error: The site ID for the site to paste into is invalid.', 'beyond-multisite' ) ),
        'errorSameIDs' => esc_js( __( 'Error: The site ID to paste into is the same as the site ID to copy from.', 'beyond-multisite' ) ),
        'errorInvalidStage' => esc_js( __( 'Error: The data for the current stage is invalid.', 'beyond-multisite' ) ),
        'errorSameUploadFolders' => esc_js( __( 'Error: The upload folders of both sites have the same path.', 'beyond-multisite' ) ),
        'errorCannotReadFromFolder' => esc_js( __( 'Error: Cannot read from the uploads folder of the site to copy from.', 'beyond-multisite' ) ),
        'errorCannotWriteToFolder' => esc_js( __( 'Error: Cannot write to the uploads folder of the site to paste into.', 'beyond-multisite' ) ),
        'errorEmptyFromPrefix' => esc_js( __( 'Error: Something went very wrong.', 'beyond-multisite' ) ) . ' '
            . esc_js( __( 'The prefix for the site to copy from is empty.' , 'beyond-multisite' ) ) . ' '
            . esc_js( __( 'We had to abort in the middle of copying. Since we did not finished, the site that was going to be replaced is probably '
                . 'now damaged. It should be deleted.', 'beyond-multisite' ) ),
        'errorEmptyToPrefix' => esc_js( __( 'Error: Something went very wrong.', 'beyond-multisite' ) ) . ' '
            . esc_js( __( 'The prefix for the site to paste into is empty.' , 'beyond-multisite' ) ) . ' '
            . esc_js( __( 'We had to abort in the middle of copying. Since we did not finished, the site that was going to be replaced is probably '
                . 'now damaged. It should be deleted.', 'beyond-multisite' ) ),
        'errorEmptyToDbName' => esc_js( __( 'Error: Something went very wrong.', 'beyond-multisite' ) ) . ' '
            . esc_js( __( 'The database name for the site to paste into is empty.' , 'beyond-multisite' ) ) . ' '
            . esc_js( __( 'We had to abort in the middle of copying. Since we did not finished, the site that was going to be replaced is probably '
                . 'now damaged. It should be deleted.', 'beyond-multisite' ) ),
        'errorSamePrefixes' => esc_js( __( 'Error: Something went very wrong.', 'beyond-multisite' ) ) . ' '
            . esc_js( __( 'Both sites have the same prefix.' , 'beyond-multisite' ) ) . ' '
            . esc_js( __( 'We had to abort in the middle of copying. Since we did not finished, the site that was going to be replaced is probably '
                . 'now damaged. It should be deleted.', 'beyond-multisite' ) ),
        'errorSamePrefixAsMain' => esc_js( __( 'Error: Something went very wrong.', 'beyond-multisite' ) ) . ' '
            . esc_js( __( 'The site to paste into has the same prefix as the main site.' , 'beyond-multisite' ) ) . ' '
            . esc_js( __( 'We had to abort in the middle of copying. Since we did not finished, the site that was going to be replaced is probably '
                . 'now damaged. It should be deleted.', 'beyond-multisite' ) ),
        'errorInvalidToPrefix' => esc_js( __( 'Error: Something went very wrong.', 'beyond-multisite' ) ) . ' '
            . esc_js( __( 'The prefix of the site to paste into is invalid.' , 'beyond-multisite' ) ) . ' '
            . esc_js( __( 'We had to abort in the middle of copying. Since we did not finished, the site that was going to be replaced is probably '
                . 'now damaged. It should be deleted.', 'beyond-multisite' ) ),
        'statusSoFarMessage' => esc_js( __( 'Working on: Stage %1$d (Parts done: %2$d)', 'beyond-multisite' ) ),
        'urlCopySite' => esc_url( network_admin_url( 'site-new.php?be-mu-copy-from=' ) ),
        'errorServerFail' => esc_js( __( 'Error: Unexpected server error. If you have WordPress debugging and logging enabled, '
            . 'you should be able to see more details about the error in the /wp-content/debug.log file.', 'beyond-multisite' ) ),
    );

    // We localize the script - we send php data to the javascript file
    wp_localize_script( 'be-mu-copy-script', 'localizedCopyMaker', $localize );

    // Enqueued script with localized data
    wp_enqueue_script( 'be-mu-copy-script', '', array(), false, true );
}

// Outputs the html code for the results layer in the footer of the network sites page
function be_mu_copy_sites_results_html() {
    ?>
        <!-- these layers will contain the results of the ajax requests and the form to copy a site -->
        <div id="be-mu-copy-sites-container">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
                <tr>
                    <td valign="middle">
                        <div id="be-mu-copy-sites-div-results">
                            <div id="be-mu-copy-sites-form" class="be-mu-p20">
                                <h2 class="be-mu-copy-h2">
                                    <?php esc_html_e( 'Normal Site Copy', 'beyond-multisite' ); ?>
                                    <div class='be-mu-right be-mu-pleft10'>
                                        <?php echo be_mu_copy_sites_get_close(); ?>
                                    </div>
                                </h2>
                                <ul>
                                    <li>
                                        <label>
                                            <?php esc_html_e( 'Copy from site ID:', 'beyond-multisite' ); ?>
                                        </label>
                                        <span id="be-mu-copy-from-site-id-span"></span>
                                    </li>
                                    <li>
                                        <label for="be-mu-copy-to-site-id">
                                            <?php esc_html_e( 'Paste into site ID:', 'beyond-multisite' ); ?>
                                        </label>
                                        <input id="be-mu-copy-to-site-id" name="be-mu-copy-to-site-id" type="text" size="5" />
                                    </li>
                                    <li>
                                        <input id="be-mu-copy-site-button" class="button button-primary" onclick="copyMakerConfirmCopySite()"
                                            name="be-mu-copy-site-button" type="button" value="<?php esc_attr_e( 'Copy Site', 'beyond-multisite' ); ?>" />
                                        <img id="be-mu-copy-loading-confirm" src="<?php echo esc_url( be_mu_img_url( 'loading.gif' ) ); ?>" />
                                    </li>
                                    <li>
                                        <?php esc_html_e( 'Or', 'beyond-multisite' ); ?> <a id="be-mu-copy-paste-into-new-link"
                                            href="#"><?php esc_html_e( 'paste into new site', 'beyond-multisite' ); ?></a>
                                            <?php esc_html_e( '(but uses "Template site copy settings")', 'beyond-multisite' ); ?>
                                    </li>
                                </ul>
                            </div>
                            <div id="be-mu-copy-sites-loading" class="be-mu-p20">
                                <p class="be-mu-center">
                                    <img src="<?php echo esc_url( be_mu_img_url( 'loading.gif' ) ); ?>" />
                                </p>
                                <p class="be-mu-center">
                                    <?php esc_html_e( 'Processing...', 'beyond-multisite' ); ?>
                                </p>
                                <p class="be-mu-center" id="be-mu-copy-sites-processed-so-far"></p>
                                <p class="be-mu-center">
                                    <?php echo be_mu_copy_sites_get_abort(); ?>
                                </p>
                            </div>
                            <div id="be-mu-copy-sites-done" class="be-mu-p20">
                                <h2 class="be-mu-copy-h2">
                                    <?php esc_html_e( 'The Copy is Completed', 'beyond-multisite' ); ?>
                                    <div class='be-mu-right be-mu-pleft10'>
                                        <?php echo be_mu_copy_sites_get_close(); ?>
                                    </div>
                                </h2>
                                <p>
                                    <a href="#" id="be-mu-copy-sites-edit-action"><?php esc_html_e( 'Edit', 'beyond-multisite' ); ?></a> |
                                    <a href="#" id="be-mu-copy-sites-dashboard-action"><?php esc_html_e( 'Dashboard', 'beyond-multisite' ); ?></a> |
                                    <a href="#" id="be-mu-copy-sites-visit-action"><?php esc_html_e( 'Visit', 'beyond-multisite' ); ?></a> |
                                    <a href="#" id="be-mu-copy-sites-log-action"><?php esc_html_e( 'View Log (%d errors)', 'beyond-multisite' ); ?></a>
                                </p>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    <?php
}

// Outputs in json format some site information we need for the confirmation message we show before we start the copy process
function be_mu_copy_sites_confirm_action_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'copy_maker_ajax_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    $copy_from_site_id = intval( $_POST['from_site_id'] );
    $copy_to_site_id = intval( $_POST['to_site_id'] );

    $main_site_id = be_mu_get_main_site_id();

    // We stop everything and show an error if we are copying from or pasting into the main site
    if ( $copy_from_site_id == $main_site_id || $copy_to_site_id == $main_site_id || is_main_site( $copy_from_site_id )
        || is_main_site( $copy_to_site_id ) ) {
        wp_die( 'main-site' );
    }

    // We stop everything and show an error if the site ID to copy from is less than 1
    if ( $copy_from_site_id < 1 ) {
        wp_die( 'invalid-from-id' );
    }

    // We stop everything and show an error if the site ID to paste into is less than 1
    if ( $copy_to_site_id < 1 ) {
        wp_die( 'invalid-to-id' );
    }

    // We stop everything and show an error if the site ID to paste into is the same as the site ID to copy from
    if ( $copy_to_site_id == $copy_from_site_id ) {
        wp_die( 'same-ids' );
    }

    // We stop everything and show an error if the site to copy from does not exist
    if ( get_site( $copy_from_site_id ) === null ) {
        wp_die( 'from-site-not-exist' );
    }

    // We stop everything and show an error if the site to paste into does not exist
    if ( get_site( $copy_to_site_id ) === null ) {
        wp_die( 'to-site-not-exist' );
    }

    // We get the site URLs of both sites
    $copy_from_site_url = get_blog_option( $copy_from_site_id, 'siteurl' );
    $copy_to_site_url = get_blog_option( $copy_to_site_id, 'siteurl' );

    // We stop everything and show an error if we could not get the site URL of the site to copy from
    if ( false === $copy_from_site_url ) {
        wp_die( 'no-site-url-copy-from' );
    }

    // We stop everything and show an error if we could not get the site URL of the site to paste into
    if ( false === $copy_to_site_url ) {
        wp_die( 'no-site-url-copy-to' );
    }

    // If ms_files_rewriting is enabled and upload_path is empty, wp_upload_dir is not reliable.
    $trim_from_upload_path = trim( get_blog_option( $copy_from_site_id, 'upload_path' ) );
    $trim_to_upload_path = trim( get_blog_option( $copy_to_site_id, 'upload_path' ) );
    if ( get_site_option( 'ms_files_rewriting' ) && ( empty( $trim_from_upload_path ) || empty( $trim_to_upload_path ) ) ) {
        wp_die( 'upload-path-not-reliable' );
    }

    $upload_dir_to = be_mu_get_site_upload_folder( $copy_to_site_id );

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the site we will paste into
    $copy_to_site_prefix = $wpdb->get_blog_prefix( $copy_to_site_id );

    // This is the data to pass to the javascript function in json format
    $json_result = array(
        'fromURL' => $copy_from_site_url,
        'toURL' => $copy_to_site_url,
        'toFolder' => $upload_dir_to,
        'toPrefix' => $copy_to_site_prefix,
    );

    echo json_encode( $json_result );

    wp_die();
}

/**
 * This function processes the actual copying of the site. It can be called many times until it finishes, and it will continue from where it left off.
 * This way we can copy big sites with a lot of files without the script timing out.
 */
function be_mu_copy_sites_process_action_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'copy_maker_ajax_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // This is the time we start working on this request
    $time_start = microtime( true );

    // If any of the post variables is not set, we stop everything and show an error code
    if ( ! isset( $_POST['task_id'] ) || ! isset( $_POST['from_site_id'] ) || ! isset( $_POST['to_site_id'] ) || ! isset( $_POST['stages_done'] )
        || ! isset( $_POST['next_stage_parts_done'] ) ) {
        wp_die( 'missing-post-data' );
    }

    // The current task ID
    $task_id = be_mu_strip_all_but_digits_and_letters( $_POST['task_id'] );

    // The site ID to copy from
    $copy_from_site_id = intval( $_POST['from_site_id'] );

    // The site ID to paste into
    $copy_to_site_id = intval( $_POST['to_site_id'] );

    // How many stages are done so far (for the first request, this should be 0)
    $stages_done = intval( $_POST['stages_done'] );

    // How many parts are done from the next stage that is not done (for example how many files have copied from the next stage)
    $next_stage_parts_done = intval( $_POST['next_stage_parts_done'] );

    // Have we reached a limit in time in this request, if 0 means no, if 1 means yes. If yes, we will stop and continue on the next request.
    $limit_reached = 0;

    // The site ID of the main site
    $main_site_id = be_mu_get_main_site_id();

    // We stop everything and show an error if we are copying from or pasting into the main site
    if ( $copy_from_site_id == $main_site_id || $copy_to_site_id == $main_site_id || is_main_site( $copy_from_site_id )
        || is_main_site( $copy_to_site_id ) ) {

        // Maybe we add the log data to the database, maybe we delete unwanted old logs, and we end the request (maybe also output an error code)
        be_mu_copy_sites_maybe_log_and_die( $task_id, $stages_done, 'main-site' );
    }

    // We stop everything and show an error if the site ID to copy from is less than 1
    if ( $copy_from_site_id < 1 ) {

        // Maybe we add the log data to the database, maybe we delete unwanted old logs, and we end the request (maybe also output an error code)
        be_mu_copy_sites_maybe_log_and_die( $task_id, $stages_done, 'invalid-from-id' );
    }

    // We stop everything and show an error if the site ID to paste into is less than 1
    if ( $copy_to_site_id < 1 ) {

        // Maybe we add the log data to the database, maybe we delete unwanted old logs, and we end the request (maybe also output an error code)
        be_mu_copy_sites_maybe_log_and_die( $task_id, $stages_done, 'invalid-to-id' );
    }

    // We stop everything and show an error if the site ID to paste into is the same as the site ID to copy from
    if ( $copy_to_site_id == $copy_from_site_id ) {

        // Maybe we add the log data to the database, maybe we delete unwanted old logs, and we end the request (maybe also output an error code)
        be_mu_copy_sites_maybe_log_and_die( $task_id, $stages_done, 'same-ids' );
    }

    // We stop everything and show an error if the stage is invalid
    if ( $stages_done < 0 || $stages_done > 8 ) {

        // Maybe we add the log data to the database, maybe we delete unwanted old logs, and we end the request (maybe also output an error code)
        be_mu_copy_sites_maybe_log_and_die( $task_id, $stages_done, 'invalid-stage' );
    }

    // If this is the first request for this task, we make some extra checks
    if ( 0 == $stages_done && 0 == $next_stage_parts_done ) {

        // We stop everything and show an error if the site to copy from does not exist
        if ( get_site( $copy_from_site_id ) === null ) {
            wp_die( 'from-site-not-exist' );
        }

        // We stop everything and show an error if the site to paste into does not exist
        if ( get_site( $copy_to_site_id ) === null ) {
            wp_die( 'to-site-not-exist' );
        }

        $upload_dir_from = be_mu_get_site_upload_folder( $copy_from_site_id );
        $upload_dir_to = be_mu_get_site_upload_folder( $copy_to_site_id );

        // We stop everything and show an error if the upload folder paths of both sites are the same
        if ( $upload_dir_from == $upload_dir_to ) {
            wp_die( 'same-upload-folders' );
        }

        // We stop everything and show an error if we cannot read from the uploads folder of the site to copy from
        if ( ! is_readable( $upload_dir_from ) ) {
            wp_die( 'cannot-read-from-folder' );
        }

        // We stop everything and show an error if we cannot write to the uploads folder of the site to paste into
        if ( ! is_writable( $upload_dir_to ) ) {
            wp_die( 'cannot-write-to-folder' );
        }

        // Add data to the log variable
        $GLOBALS['be_mu_copy_log'] = 'Started copying from site ID "' . $copy_from_site_id . '" to site ID "' . $copy_to_site_id . '" at '
            . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . '). Task ID: "' . $task_id . '"<br />';
    }

    // Add data to the log variable
    $GLOBALS['be_mu_copy_log'] .= 'New request started to work (after error checks) at ' . be_mu_unixtime_to_wp_datetime( time() )
        . ' (Unix time: ' . time() . ').<br />';

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // If stage 1 is done and stage 3 is not done, we get the data about the files and folders we need to delete (and then delete it from the database)
    if ( $stages_done < 3 && $stages_done > 0 ) {
        $GLOBALS['be_mu_copy_sites_delete_files'] = explode( ';;', be_mu_get_setting( 'be_mu_copy_sites_delete_files_array_' . $task_id ) );
        $GLOBALS['be_mu_copy_sites_delete_folders'] = explode( ';;', be_mu_get_setting( 'be_mu_copy_sites_delete_folders_array_' . $task_id ) );
        be_mu_delete_setting( 'be_mu_copy_sites_delete_files_array_' . $task_id );
        be_mu_delete_setting( 'be_mu_copy_sites_delete_folders_array_' . $task_id );
    }

    // If stage 3 is done and stage 6 is not done, we get the data about the files and folders we need to copy (and then delete it from the database)
    if ( $stages_done < 6 && $stages_done > 2 ) {
        $GLOBALS['be_mu_copy_sites_copy_files'] = explode( ';;', be_mu_get_setting( 'be_mu_copy_sites_copy_files_array_' . $task_id ) );
        $GLOBALS['be_mu_copy_sites_copy_folders'] = explode( ';;', be_mu_get_setting( 'be_mu_copy_sites_copy_folders_array_' . $task_id ) );
        be_mu_delete_setting( 'be_mu_copy_sites_copy_files_array_' . $task_id );
        be_mu_delete_setting( 'be_mu_copy_sites_copy_folders_array_' . $task_id );
    }

    // Begin Stage 1: Finding files and folders for deletion from the site to paste into
    if ( $stages_done < 1 ) {

        // Add data to the log variable
        $GLOBALS['be_mu_copy_log'] .= '<a name="stage1"></a><br /><b>Started stage 1</b> (finding files and folders for deletion from the site to paste into) at '
            . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . ')<br />';

        // The path to the uploads folder of the site to paste into
        $upload_dir_to = be_mu_get_site_upload_folder( $copy_to_site_id );

        $GLOBALS['be_mu_copy_sites_delete_files'] = $GLOBALS['be_mu_copy_sites_delete_folders'] = Array();

        // We get all paths of all files and folders from the uploads folder of the site to paste into, and store them in two global arrays
        be_mu_copy_sites_get_files_and_folders( $upload_dir_to, 'delete', '' );

        // If this peepso plugin is active we need to delete some other files as well
        if ( be_mu_copy_sites_is_peepso_active( $copy_from_site_id ) ) {
            $from_peepso_dir = be_mu_copy_sites_get_peepso_directory_if_exists( $copy_from_site_id );
            $to_peepso_dir = be_mu_copy_sites_get_peepso_directory_if_exists( $copy_to_site_id );
            if ( false !== $from_peepso_dir && false !== $to_peepso_dir ) {
                be_mu_copy_sites_get_files_and_folders( $to_peepso_dir, 'delete', '' );
            }
        }

        // We reverse the array so the parent folders are deleted last
        $GLOBALS['be_mu_copy_sites_delete_folders'] = array_reverse( $GLOBALS['be_mu_copy_sites_delete_folders'] );

        // Stage 1 is done
        $stages_done = 1;

        // Add data to the log variable
        $GLOBALS['be_mu_copy_log'] .= 'Stage 1 is done at ' . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . ')<br />';
    }

    /**
     * If the time limit for the request has been reached, we store the global arrays with files and foldes to the database and set the $limit_reached
     * variable to 1, which will skip all next stages below for this request.
     */
    if ( ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
        $limit_reached = 1;
        be_mu_copy_sites_save_arrays_to_database( $task_id, 'delete' );
    }

    // Begin Stage 2: Deleting files from the site to paste into
    if ( $stages_done < 2 && 0 === $limit_reached ) {

        // Add data to the log variable if this is the first time we start this stage for this task
        if ( 0 === $next_stage_parts_done ) {
            $GLOBALS['be_mu_copy_log'] .= '<a name="stage2"></a><br /><b>Started stage 2</b> (deleting files from the site to paste into) at '
                . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . ')<br />';
        }

        // How many files we have skipped so far (because they are done from the previous request)
        $skipped = 0;

        // How many files we have deleted so far
        $done = 0;

        // We go through all files of the uploads folder of the site to paste into
        foreach ( $GLOBALS['be_mu_copy_sites_delete_files'] as $file ) {

            // If we have already deleted some of the files in the previous request, we will skip the same amount
            if ( $next_stage_parts_done > 0 && $skipped < $next_stage_parts_done ) {
                $skipped++;
                continue;
            }

            if ( ! empty( $file ) ) {

                // We delete the current file and increase the counter for the number of files deleted
                $status = unlink( $file );
                $done++;

                // Add data to the log variable
                if ( $status ) {
                    $GLOBALS['be_mu_copy_log'] .= 'Deleted the file "' . esc_html( be_mu_strip_before_substring( $file, 'wp-content' ) ) . '". Parts done: '
                        . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done . '<br />';
                } else {
                    $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete the file "'
                        . esc_html( be_mu_strip_before_substring( $file, 'wp-content' ) ) . '". Parts done: '
                        . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done . '</b><br />';
                }

            }

            // After every file deletion we check the time passed and if the time limit is reached we will stop and continue in the next request
            if ( ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
                $limit_reached = 1;
                break;
            }
        }

        /**
         * If the time limit was reached we add the number of files we deleted in this request to the number we did in the previous request
         * so we know from where to start the next time. We also save the arrays with file and folder paths to the databse.
         */
        if ( 1 === $limit_reached ) {
            $next_stage_parts_done += $done;
            be_mu_copy_sites_save_arrays_to_database( $task_id, 'delete' );

        // If a time limit was not reached, stage 2 is done and 0 parts from the next stage are done
        } else {
            $stages_done = 2;
            $next_stage_parts_done = 0;

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= 'Stage 2 is done at ' . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . ')<br />';
        }
    }

    /**
     * If the time limit for the request has been reached, we store the global arrays with files and foldes to the database and set the $limit_reached
     * variable to 1, which will skip all next stages below for this request.
     */
    if ( 0 === $limit_reached && ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
        $limit_reached = 1;
        be_mu_copy_sites_save_arrays_to_database( $task_id, 'delete' );
    }

    // Begin Stage 3: Deleting folders from the site to paste into
    if ( $stages_done < 3 && 0 === $limit_reached ) {

        // Add data to the log variable if this is the first time we start this stage for this task
        if ( 0 === $next_stage_parts_done ) {
            $GLOBALS['be_mu_copy_log'] .= '<a name="stage3"></a><br /><b>Started stage 3</b> (deleting folders from the site to paste into) at '
                . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . ')<br />';
        }

        // How many folders we have skipped so far (because they are done from the previous request)
        $skipped = 0;

        // How many folders we have deleted so far
        $done = 0;

        // We go through all folders of the uploads folder of the site to paste into
        foreach ( $GLOBALS['be_mu_copy_sites_delete_folders'] as $folder ) {

            // If we have already deleted some of the folders in the previous request, we will skip the same amount
            if ( $next_stage_parts_done > 0 && $skipped < $next_stage_parts_done ) {
                $skipped++;
                continue;
            }

            // We delete the current folder and increase the counter for the number of folders deleted
            $status = @rmdir( $folder );
            $done++;

            // Add data to the log variable
            if ( $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Deleted the directory "' . esc_html( be_mu_strip_before_substring( $folder, 'wp-content' ) ) . '". Parts done: '
                    . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete the directory "'
                    . esc_html( be_mu_strip_before_substring( $folder, 'wp-content' ) ) . '". Parts done: '
                    . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done . '</b><br />';
            }

            // After every folder deletion we check the time passed and if the time limit is reached we will stop and continue in the next request
            if ( ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
                $limit_reached = 1;
                break;
            }
        }

        /**
         * If the time limit was reached we add the number of folders we deleted in this request to the number we did in the previous request
         * so we know from where to start the next time. We also save the arrays with file and folder paths to the databse.
         */
        if ( 1 === $limit_reached ) {
            $next_stage_parts_done += $done;
            be_mu_copy_sites_save_arrays_to_database( $task_id, 'delete' );

        // If a time limit was not reached, stage 3 is done and 0 parts from the next stage are done
        } else {
            $stages_done = 3;
            $next_stage_parts_done = 0;

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= 'Stage 3 is done at ' . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . ')<br />';
        }
    }

    /**
     * If the time limit for the request has been reached, we store the global arrays with files and foldes to the database and set the $limit_reached
     * variable to 1, which will skip all next stages below for this request.
     */
    if ( 0 === $limit_reached && ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
        $limit_reached = 1;
    }

    // Begin Stage 4: Finding files and folders to copy
    if ( $stages_done < 4 && 0 === $limit_reached ) {

        // Add data to the log variable
        $GLOBALS['be_mu_copy_log'] .= '<a name="stage4"></a><br /><b>Started stage 4</b> (finding files and folders to copy) at '
            . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . ')<br />';

        if ( be_mu_get_setting( 'be-mu-copy-site-no-media' ) === 'on' ) {
            $GLOBALS['be_mu_copy_log'] .= 'Skipping stage 4 due to settings.<br />';
        } else {

            // The path to the uploads folder of the site to copy from
            $upload_dir_from = be_mu_get_site_upload_folder( $copy_from_site_id );

            $GLOBALS['be_mu_copy_sites_copy_files'] = $GLOBALS['be_mu_copy_sites_copy_folders'] = Array();

            // We get the files and folders to copy and put then into the global arrays
            be_mu_copy_sites_get_files_and_folders( $upload_dir_from, 'copy', '' );

            // If the peepso plugin is active we need to copy some other files as well
            if ( be_mu_copy_sites_is_peepso_active( $copy_from_site_id ) ) {
                $from_peepso_dir = be_mu_copy_sites_get_peepso_directory_if_exists( $copy_from_site_id );
                if ( false !== $from_peepso_dir ) {
                    be_mu_copy_sites_get_files_and_folders( $from_peepso_dir, 'copy', '' );
                }
            }

        }

        // Stage 4 is done
        $stages_done = 4;

        // Add data to the log variable
        $GLOBALS['be_mu_copy_log'] .= 'Stage 4 is done at ' . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . ')<br />';
    }

    /**
     * If the time limit for the request has been reached, we store the global arrays with files and foldes to the database and set the $limit_reached
     * variable to 1, which will skip all next stages below for this request.
     */
    if ( 0 === $limit_reached && ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
        $limit_reached = 1;
        be_mu_copy_sites_save_arrays_to_database( $task_id, 'copy' );
    }

    // Begin Stage 5: Copying folders
    if ( $stages_done < 5 && 0 === $limit_reached ) {

        // Add data to the log variable if this is the first time we start this stage for this task
        if ( 0 === $next_stage_parts_done ) {
            $GLOBALS['be_mu_copy_log'] .= '<a name="stage5"></a><br /><b>Started stage 5</b> (copying folders) at '
                . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . ')<br />';
        }

        if ( be_mu_get_setting( 'be-mu-copy-site-no-media' ) === 'on' ) {
            $GLOBALS['be_mu_copy_log'] .= 'Skipping stage 5 due to settings.<br />';
        } else {

            // The path to the uploads folder of the site to copy from
            $upload_dir_from = be_mu_get_site_upload_folder( $copy_from_site_id );

            // The path to the uploads folder of the site to paste into
            $upload_dir_to = be_mu_get_site_upload_folder( $copy_to_site_id );

            // If this peepso plugin is active we need to copy some other files as well
            $peepso_active = 'no';
            if ( be_mu_copy_sites_is_peepso_active( $copy_from_site_id ) ) {
                $from_peepso_dir = be_mu_copy_sites_get_peepso_directory_if_exists( $copy_from_site_id );
                $to_peepso_dir = str_replace( 'peepso-' . $copy_from_site_id, 'peepso-' . $copy_to_site_id, $from_peepso_dir );
                if ( false !== $from_peepso_dir && false !== $to_peepso_dir && $to_peepso_dir !== $from_peepso_dir ) {
                    $peepso_active = 'yes';
                }
            }

            // How many folders we have skipped so far (because they are done from the previous request)
            $skipped = 0;

            // How many folders we have deleted so far
            $done = 0;

            // We go through all folders of the uploads folder of the site to copy from
            foreach ( $GLOBALS['be_mu_copy_sites_copy_folders'] as $folder ) {

                // If we have already copied some of the folders in the previous request, we will skip the same amount
                if ( $next_stage_parts_done > 0 && $skipped < $next_stage_parts_done ) {
                    $skipped++;
                    continue;
                }

                // This is the path of the new folder we will create (for the peepso plugin it is different)
                if ( 'yes' === $peepso_active && strpos( $folder, 'peepso-' . $copy_from_site_id ) !== false ) {
                    $new_folder = be_mu_replace_first( $from_peepso_dir, $to_peepso_dir, $folder );
                } else {
                    $new_folder = be_mu_replace_first( $upload_dir_from, $upload_dir_to, $folder );
                }

                // If the new folder does not exist, we create the current new folder and increase the counter for the number of folders copied
                if ( ! file_exists( $new_folder ) ) {
                    $status = mkdir( $new_folder, 0755, true );
                    $done++;

                    // Add data to the log variable
                    if ( $status ) {
                        $GLOBALS['be_mu_copy_log'] .= 'Created the directory "'
                            . esc_html( be_mu_strip_before_substring( $new_folder, 'wp-content' ) ) . '". Parts done: '
                            . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done . '<br />';
                    } else {
                        $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not create the directory "'
                            . esc_html( be_mu_strip_before_substring( $new_folder, 'wp-content' ) ) . '". Parts done: '
                            . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done . '</b><br />';
                    }

                // If the new folder exists we increase the counter for the number of folders copied
                } else {
                    $done++;

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'The directory "' . esc_html( $new_folder ) . '" already exists. Parts done: '
                        . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done . '<br />';
                }

                // After every folder creation we check the time passed and if the time limit is reached we will stop and continue in the next request
                if ( ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
                    $limit_reached = 1;
                    break;
                }
            }

        }

        /**
         * If the time limit was reached we add the number of folders we created in this request to the number we did in the previous request
         * so we know from where to start the next time. We also save the arrays with file and folder paths to the databse.
         */
        if ( 1 === $limit_reached ) {
            $next_stage_parts_done += $done;
            be_mu_copy_sites_save_arrays_to_database( $task_id, 'copy' );

        // If a time limit was not reached, stage 5 is done and 0 parts from the next stage are done
        } else {
            $stages_done = 5;
            $next_stage_parts_done = 0;

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= 'Stage 5 is done at ' . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . ')<br />';
        }
    }

    /**
     * If the time limit for the request has been reached, we store the global arrays with files and foldes to the database and set the $limit_reached
     * variable to 1, which will skip all next stages below for this request.
     */
    if ( 0 === $limit_reached && ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
        $limit_reached = 1;
        be_mu_copy_sites_save_arrays_to_database( $task_id, 'copy' );
    }

    // Begin Stage 6: Copying files
    if ( $stages_done < 6 && 0 === $limit_reached ) {

        // Add data to the log variable if this is the first time we start this stage for this task
        if ( 0 === $next_stage_parts_done ) {
            $GLOBALS['be_mu_copy_log'] .= '<a name="stage6"></a><br /><b>Started stage 6</b> (copying files) at '
                . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . ')<br />';
        }

        if ( be_mu_get_setting( 'be-mu-copy-site-no-media' ) === 'on' ) {
            $GLOBALS['be_mu_copy_log'] .= 'Skipping stage 6 due to settings.<br />';
        } else {

            // The path to the uploads folder of the site to copy from
            $upload_dir_from = be_mu_get_site_upload_folder( $copy_from_site_id );

            // The path to the uploads folder of the site to paste into
            $upload_dir_to = be_mu_get_site_upload_folder( $copy_to_site_id );

            // If this peepso plugin is active we need to copy some other files as well
            $peepso_active = 'no';
            if ( be_mu_copy_sites_is_peepso_active( $copy_from_site_id ) ) {
                $from_peepso_dir = be_mu_copy_sites_get_peepso_directory_if_exists( $copy_from_site_id );
                $to_peepso_dir = str_replace( 'peepso-' . $copy_from_site_id, 'peepso-' . $copy_to_site_id, $from_peepso_dir );
                if ( false !== $from_peepso_dir && false !== $to_peepso_dir && $to_peepso_dir !== $from_peepso_dir ) {
                    $peepso_active = 'yes';
                }
            }

            // How many files we have skipped so far (because they are done from the previous request)
            $skipped = 0;

            // How many files we have copied so far
            $done = 0;

            // We go through all files of the uploads folder of the site to copy from
            foreach ( $GLOBALS['be_mu_copy_sites_copy_files'] as $file ) {

                // If we have already copied some of the files in the previous request, we will skip the same amount
                if ( $next_stage_parts_done > 0 && $skipped < $next_stage_parts_done ) {
                    $skipped++;
                    continue;
                }

                // This is the path of the new folder we will create (for the peepso plugin it is different)
                if ( 'yes' === $peepso_active && strpos( $file, 'peepso-' . $copy_from_site_id ) !== false ) {
                    $new_file = be_mu_replace_first( $from_peepso_dir, $to_peepso_dir, $file );
                } else {
                    $new_file = be_mu_replace_first( $upload_dir_from, $upload_dir_to, $file );
                }

                // We copy the current file and increase the counter for the number of files copied
                $status = copy( $file, $new_file );
                $done++;

                // Add data to the log variable
                if ( $status ) {
                    $GLOBALS['be_mu_copy_log'] .= 'Copied the file "' . esc_html( be_mu_strip_before_substring( $file, 'wp-content' ) ) . '" to "'
                    . esc_html( be_mu_strip_before_substring( $new_file, 'wp-content' ) ) . '". Parts done: '
                        . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done . '<br />';
                } else {
                    $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not copy the file "'
                    . esc_html( be_mu_strip_before_substring( $file, 'wp-content' ) ) . '" to "' . esc_html( be_mu_strip_before_substring( $new_file, 'wp-content' ) )
                        . '". Parts done: ' . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done . '</b><br />';
                }

                // After every file copying we check the time passed and if the time limit is reached we will stop and continue in the next request
                if ( ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
                    $limit_reached = 1;
                    break;
                }
            }

        }

        /**
         * If the time limit was reached we add the number of files we copied in this request to the number we did in the previous request
         * so we know from where to start the next time. We also save the arrays with file and folder paths to the databse.
         */
        if ( 1 === $limit_reached ) {
            $next_stage_parts_done += $done;
            be_mu_copy_sites_save_arrays_to_database( $task_id, 'copy' );

        // If a time limit was not reached, stage 6 is done and 0 parts from the next stage are done
        } else {
            $stages_done = 6;
            $next_stage_parts_done = 0;

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= 'Stage 6 is done at ' . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . ')<br />';
        }
    }

    // If the time limit for the request has been reached, we set the $limit_reached variable to 1, which will skip all next stages below for this request.
    if ( 0 === $limit_reached && ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
        $limit_reached = 1;
    }

    // Begin Stage 7: Deleting database tables
    if ( $stages_done < 7 && 0 === $limit_reached ) {

        // Add data to the log variable
        $GLOBALS['be_mu_copy_log'] .= '<a name="stage7"></a><br /><b>Started stage 7</b> (deleting database tables) at '
            . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . ')<br />';

        // We delete the options where we temporarily storred a list of file and folder paths
        be_mu_delete_setting( 'be_mu_copy_sites_delete_files_array_' . $task_id );
        be_mu_delete_setting( 'be_mu_copy_sites_delete_folders_array_' . $task_id );
        be_mu_delete_setting( 'be_mu_copy_sites_copy_files_array_' . $task_id );
        be_mu_delete_setting( 'be_mu_copy_sites_copy_folders_array_' . $task_id );

        // The database table prefix for the site we will paste into, and therefor almost completely delete first
        $copy_to_site_prefix = $wpdb->get_blog_prefix( $copy_to_site_id );

        // The database table prefix for the site we will copy from
        $copy_from_site_prefix = $wpdb->get_blog_prefix( $copy_from_site_id );

        // The database table prefix for the main site
        $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

        // If for any reason the prefix is empty we will stop and show an error
        if ( empty( $copy_to_site_prefix ) ) {

            // Maybe we add the log data to the database, maybe we delete unwanted old logs, and we end the request (maybe also output an error code)
            be_mu_copy_sites_maybe_log_and_die( $task_id, $stages_done, 'empty-to-prefix' );
        }

        // If for any reason both prefixes are the same we will stop and show an error
        if ( $copy_to_site_prefix == $copy_from_site_prefix ) {

            // Maybe we add the log data to the database, maybe we delete unwanted old logs, and we end the request (maybe also output an error code)
            be_mu_copy_sites_maybe_log_and_die( $task_id, $stages_done, 'same-prefixes' );
        }

        // If for any reason the prefix of the site to paste into is the same as the one of the main site we will stop and show an error
        if ( $copy_to_site_prefix == $main_blog_prefix ) {

            // Maybe we add the log data to the database, maybe we delete unwanted old logs, and we end the request (maybe also output an error code)
            be_mu_copy_sites_maybe_log_and_die( $task_id, $stages_done, 'same-prefix-as-main' );
        }

        // If for any reason the prefix of the site to paste into does not end in a number and then an underscore, we will stop and show an error
        if ( substr( $copy_to_site_prefix, -1 ) !== '_' || ! is_numeric( substr( substr_replace( $copy_to_site_prefix, "", -1), -1 ) ) ) {

            // Maybe we add the log data to the database, maybe we delete unwanted old logs, and we end the request (maybe also output an error code)
            be_mu_copy_sites_maybe_log_and_die( $task_id, $stages_done, 'invalid-to-prefix' );
        }

        /**
         * We save in the database data about the site URL, site home, upload path, upload url, and site prefix for the site to paste into,
         * so we can access it after we delete the site
         */
        $settings = Array(
            'be_mu_copy_sites_copy_to_site_url_' . $task_id => get_blog_option( $copy_to_site_id, 'siteurl' ),
            'be_mu_copy_sites_copy_to_home_' . $task_id => get_blog_option( $copy_to_site_id, 'home' ),
            'be_mu_copy_sites_copy_to_blogname_' . $task_id => get_blog_option( $copy_to_site_id, 'blogname' ),
            'be_mu_copy_sites_copy_to_admin_email_' . $task_id => get_blog_option( $copy_to_site_id, 'admin_email' ),
            'be_mu_copy_sites_copy_to_upload_path_' . $task_id => get_blog_option( $copy_to_site_id, 'upload_path' ),
            'be_mu_copy_sites_copy_to_upload_url_path_' . $task_id => get_blog_option( $copy_to_site_id, 'upload_url_path' ),
            'be_mu_copy_sites_copy_to_site_prefix_' . $task_id => $copy_to_site_prefix,
        );
        foreach ( $settings as $key => $value ) {
            $status = be_mu_set_or_make_setting( $key, $value );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Added or updated the site option named "' . $key . '" with value "' . esc_html( $value ) . '"<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not add or update the site option named "' . $key . '" with value "'
                    . esc_html( $value ) . '"</b><br />';
            }
        }

        // We get all database table names that start with the site prefix of the site to paste into
        $query = "SELECT TABLE_NAME, TABLE_SCHEMA FROM information_schema.tables WHERE "
            . "TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA IN ( " . be_mu_get_database_names_string_for_mysql_in() . " ) "
            . "AND LOCATE( '" . $copy_to_site_prefix . "', TABLE_NAME ) = 1";
        $tables_to_multi_array = $wpdb->get_results( $query, ARRAY_A );

        // If there are any database tables found we go through them one by one
        if ( ! empty( $tables_to_multi_array ) ) {

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= 'We found ' . count( $tables_to_multi_array ) . ' database tables that belong to the site to paste into. Query: "'
                . $query . '".<br />';

            foreach ( $tables_to_multi_array as $table_to ) {

                // Make sure one more time, that the table name really starts with the site prefix, and then we delete the table.
                if ( substr( $table_to['TABLE_NAME'], 0, strlen( $copy_to_site_prefix ) ) === $copy_to_site_prefix ) {

                    // We save the database name for later (this is needed to support the multi db plugin)
                    if ( ! be_mu_get_setting( 'be_mu_copy_sites_copy_to_db_name_' . $task_id ) ) {
                        $status = be_mu_set_or_make_setting( 'be_mu_copy_sites_copy_to_db_name_' . $task_id, $table_to['TABLE_SCHEMA'] );

                        // Add data to the log variable
                        if ( false !== $status ) {
                            $GLOBALS['be_mu_copy_log'] .= 'Added or updated the site option named "' . 'be_mu_copy_sites_copy_to_db_name_' . $task_id . '" with value "'
                                . $table_to['TABLE_SCHEMA'] . '"<br />';
                        } else {
                            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not add or update the site option named "'
                                . 'be_mu_copy_sites_copy_to_db_name_' . $task_id . '" with value "' . $table_to['TABLE_SCHEMA'] . '"</b><br />';
                        }
                    }

                    // Delete the table
                    $query = 'DROP TABLE ' . $table_to['TABLE_NAME'];
                    $status = $wpdb->query( $query );

                    // Add data to the log variable
                    if ( false !== $status ) {
                        $GLOBALS['be_mu_copy_log'] .= 'Deleted the database table "' . $table_to['TABLE_NAME'] . '". Query: "' . $query . '".<br />';
                    } else {
                        $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete the database table "' . $table_to['TABLE_NAME']
                            . '". Query: "' . $query . '".</b><br />';
                    }
                }
            }
        } else {

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: No database tables found that belong to the site to paste into or error in the query. Query: "'
                . $query . '".</b><br />';
        }

        // Stage 7 is done
        $stages_done = 7;

        // Add data to the log variable
        $GLOBALS['be_mu_copy_log'] .= 'Stage 7 is done at ' . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . ')<br />';
    }

    // If the time limit for the request has been reached, we set the $limit_reached variable to 1, which will skip all next stages below for this request.
    if ( 0 === $limit_reached && ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
        $limit_reached = 1;
    }

    // Begin Stage 8: Copying database tables
    if ( $stages_done < 8 && 0 === $limit_reached ) {

        // Add data to the log variable if this is the first time we start this stage for this task
        if ( 0 === $next_stage_parts_done ) {
            $GLOBALS['be_mu_copy_log'] .= '<a name="stage8"></a><br /><b>Started stage 8</b> (copying database tables) at '
                . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . ')<br />';
        }

        // The database table prefix for the site we will copy from
        $copy_from_site_prefix = $wpdb->get_blog_prefix( $copy_from_site_id );

        // The database table prefix for the site we will paste into
        $copy_to_site_prefix = be_mu_get_setting( 'be_mu_copy_sites_copy_to_site_prefix_' . $task_id );

        // The database name for the site we will paste into (to support the multi db plugin)
        $copy_to_site_db_name = be_mu_get_setting( 'be_mu_copy_sites_copy_to_db_name_' . $task_id );

        // If any of the prefixes or the database name are empty for some reason, we stop everything and show an error
        if ( empty( $copy_from_site_prefix ) ) {

            // Maybe we add the log data to the database, maybe we delete unwanted old logs, and we end the request (maybe also output an error code)
            be_mu_copy_sites_maybe_log_and_die( $task_id, $stages_done, 'empty-from-prefix' );
        }
        if ( empty( $copy_to_site_prefix ) ) {

            // Maybe we add the log data to the database, maybe we delete unwanted old logs, and we end the request (maybe also output an error code)
            be_mu_copy_sites_maybe_log_and_die( $task_id, $stages_done, 'empty-to-prefix' );
        }
        if ( empty( $copy_to_site_db_name ) ) {

            // Maybe we add the log data to the database, maybe we delete unwanted old logs, and we end the request (maybe also output an error code)
            be_mu_copy_sites_maybe_log_and_die( $task_id, $stages_done, 'empty-to-db-name' );
        }

        // How many database tables we have skipped so far (because they are done from the previous request)
        $skipped = 0;

        // How many database tables we have copied so far
        $done = 0;

        // We get all database table names that start with the site prefix of the site to copy from
        $query = "SELECT TABLE_NAME, TABLE_SCHEMA FROM information_schema.tables WHERE "
            . "TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA IN ( " . be_mu_get_database_names_string_for_mysql_in() . " ) "
            . "AND LOCATE( '" . $copy_from_site_prefix . "', TABLE_NAME ) = 1";
        $tables_from_multi_array = $wpdb->get_results( $query, ARRAY_A );

        // If there are any database tables found we go through them one by one
        if ( ! empty( $tables_from_multi_array ) ) {

            $GLOBALS['be_mu_copy_log'] .= 'We found ' . count( $tables_from_multi_array ) . ' database tables that belong to the site to copy from. Query: "'
                . $query . '".<br />';

            foreach ( $tables_from_multi_array as $table_from ) {

                // Make sure one more time, that the table name really starts with the site prefix, and then we copy the table
                if ( substr( $table_from['TABLE_NAME'], 0, strlen( $copy_from_site_prefix ) ) === $copy_from_site_prefix ) {

                    // If we have already copied some of the database tables in the previous request, we will skip the same amount
                    if ( $next_stage_parts_done > 0 && $skipped < $next_stage_parts_done ) {
                        $skipped++;
                        continue;
                    }

                    // The name of the new database table we will create
                    $new_table_to = be_mu_replace_first( $copy_from_site_prefix, $copy_to_site_prefix, $table_from['TABLE_NAME'] );

                    // Create the database table that will be a copy of the original
                    $query = 'CREATE TABLE `' . $copy_to_site_db_name . '`.`' . $new_table_to . '` LIKE `'
                        . $table_from['TABLE_SCHEMA'] . '`.`' . $table_from['TABLE_NAME'] . '`';
                    $status = $wpdb->query( $query );

                    // Add data to the log variable
                    if ( false !== $status ) {
                        $GLOBALS['be_mu_copy_log'] .= 'Created the database table "' . $copy_to_site_db_name . '.' . $new_table_to . '". Query: "' . $query
                            . '". Parts done: ' . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done . '<br />';
                    } else {
                        $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not create the database table "'
                            . $copy_to_site_db_name . '.' . $new_table_to . '". Query: "' . $query . '". Parts done: '
                            . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done . '</b><br />';
                    }

                    // We insert all the same data into the new table
                    $query = 'INSERT INTO `' . $copy_to_site_db_name . '`.`' . $new_table_to . '` SELECT * FROM `'
                        . $table_from['TABLE_SCHEMA'] . '`.`' . $table_from['TABLE_NAME'] . '`';
                    $status = $wpdb->query( $query );

                    // We increase the counter for the number of database tables that we have copied already
                    $done++;

                    // Add data to the log variable
                    if ( false !== $status ) {
                        $GLOBALS['be_mu_copy_log'] .= 'Inserted the content of the database table "' . $table_from['TABLE_SCHEMA'] . '.' . $table_from['TABLE_NAME']
                            . '" into the database table "' . $copy_to_site_db_name . '.' . $new_table_to . '". Query: "'
                            . $query . '". Rows inserted: ' . $status . '. Parts done: '
                            . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done . '<br />';
                    } else {
                        $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not insert the content of the database table "'
                            . $table_from['TABLE_SCHEMA'] . '.' . $table_from['TABLE_NAME']
                            . '" into the database table "' . $copy_to_site_db_name . '.' . $new_table_to . '". Query: "' . $query . '". Parts done: '
                            . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done . '</b><br />';
                    }

                    /**
                     * Right away after creating the options table and inserting the data in it, we fix the upload path quickly because if
                     * for some reason the copy process stops at some point before we fix it, both sites will have the same upload folder.
                     * And this is a big problem since if the site to paste into is now deleted or pasted into again, the incorrect files
                     * will be deleted!
                     */
                    if ( $new_table_to == $copy_to_site_prefix . 'options' ) {

                        // The upload path option of the site we paste into
                        $copy_to_upload_path = be_mu_get_setting( 'be_mu_copy_sites_copy_to_upload_path_' . $task_id );

                        $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "options SET option_value = %s WHERE option_name = 'upload_path'",
                            $copy_to_upload_path );
                        $status = $wpdb->query( $query );

                        // Add data to the log variable
                        if ( false !== $status ) {
                            $GLOBALS['be_mu_copy_log'] .= 'Updated the upload path of the site to paste into. Query: "' . esc_html( $query )
                                . '". Rows updated: ' . $status . '<br />';
                        } else {
                            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the upload path of the site to paste into. Query: "'
                                . esc_html( $query ) . '".</b><br />';
                        }
                    }

                    /*
                    * After every database table copying we check the time passed and if the
                    * time limit is reached we will stop and continue in the next request
                    */
                    if ( ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
                        $limit_reached = 1;
                        break;
                    }
                }
            }
        } else {

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: No database tables found that belong to the site to copy from or error in the query. Query: "'
                . $query . '".</b><br />';
        }

        /**
         * If the time limit was reached we add the number of database tables we copied in this request to the number we did in the previous request
         * so we know from where to start the next time.
         */
        if ( 1 === $limit_reached ) {
            $next_stage_parts_done += $done;

        // If a time limit was not reached, stage 7 is done and 0 parts from the next stage are done
        } else {
            $stages_done = 8;
            $next_stage_parts_done = 0;

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= 'Stage 8 is done at ' . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . ')<br />';
        }
    }

    // If the time limit for the request has been reached, we set the $limit_reached variable to 1, which will skip all next stages below for this request.
    if ( 0 === $limit_reached && ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
        $limit_reached = 1;
    }

    // Begin Stage 9: Fix database data
    if ( $stages_done < 9 && 0 === $limit_reached ) {

        // Add data to the log variable
        $GLOBALS['be_mu_copy_log'] .= '<a name="stage9"></a><br /><b>Started stage 9</b> (fixing database data) at '
            . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . ')<br />';

        // The database table prefix for the multisite
        $base_prefix = $wpdb->base_prefix;

        // The database table prefix for the site we will copy from
        $copy_from_site_prefix = $wpdb->get_blog_prefix( $copy_from_site_id );

        // The database table prefix for the site we will paste into
        $copy_to_site_prefix = be_mu_get_setting( 'be_mu_copy_sites_copy_to_site_prefix_' . $task_id );

        // The site URL of the site we paste into
        $copy_to_site_url = esc_url( be_mu_get_setting( 'be_mu_copy_sites_copy_to_site_url_' . $task_id ) );

        // The home option of the site we paste into
        $copy_to_home = be_mu_get_setting( 'be_mu_copy_sites_copy_to_home_' . $task_id );

        // The upload URL path option of the site we paste into
        $copy_to_upload_url_path = be_mu_get_setting( 'be_mu_copy_sites_copy_to_upload_url_path_' . $task_id );

        // The site URL of the site we copy from
        $copy_from_site_url = esc_url( get_blog_option( $copy_from_site_id, 'siteurl' ) );

        $copy_title = be_mu_get_setting( 'be-mu-copy-site-title' );
        $copy_admin_email = be_mu_get_setting( 'be-mu-copy-site-email' );

        if ( $copy_title !== 'on' ) {
            $copy_to_title = be_mu_get_setting( 'be_mu_copy_sites_copy_to_blogname_' . $task_id );
        }

        if ( $copy_admin_email !== 'on' ) {
            $copy_to_admin_email = be_mu_get_setting( 'be_mu_copy_sites_copy_to_admin_email_' . $task_id );
        }

        // If any of the prefixes are empty for some reason, we stop everything and show an error
        if ( empty( $copy_from_site_prefix ) ) {

            // Maybe we add the log data to the database, maybe we delete unwanted old logs, and we end the request (maybe also output an error code)
            be_mu_copy_sites_maybe_log_and_die( $task_id, $stages_done, 'empty-from-prefix' );
        }
        if ( empty( $copy_to_site_prefix ) ) {

            // Maybe we add the log data to the database, maybe we delete unwanted old logs, and we end the request (maybe also output an error code)
            be_mu_copy_sites_maybe_log_and_die( $task_id, $stages_done, 'empty-to-prefix' );
        }

        // Copy site meta data
        if ( function_exists( 'is_site_meta_supported' ) && is_site_meta_supported() ) {
            $inserted_site_meta = 0;
            $query = $wpdb->prepare( "DELETE FROM " . $base_prefix . "blogmeta WHERE blog_id = %d", $copy_to_site_id );
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'The site meta data for site ID ' . $copy_to_site_id . ' in the ' . $base_prefix . 'blogmeta database table'
                    . ' was deleted. Rows deleted: ' . esc_html( $status ) . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete site meta data for site ID ' . $copy_to_site_id . ' in the '
                    . $base_prefix . 'blogmeta database table.</b><br />';
            }

            $query = $wpdb->prepare( "SELECT meta_key, meta_value FROM " . $base_prefix . "blogmeta WHERE blog_id = %d", $copy_from_site_id );
            $results_multi_array = $wpdb->get_results( $query, ARRAY_A );
            if ( ! empty( $results_multi_array ) ) {
                foreach ( $results_multi_array as $results ) {
                    $status = $wpdb->insert(
                    	$base_prefix . 'blogmeta',
                    	array(
                    		'blog_id' => $copy_to_site_id,
                    		'meta_key' => $results['meta_key'],
                    		'meta_value' => $results['meta_value'],
                    	),
                    	array(
                    		'%d',
                    		'%s',
                    		'%s',
                    	)
                    );
                    if ( false !== $status ) {
                        $inserted_site_meta++;
                    }
                }
            }

            // Add data to the log variable
            if ( $inserted_site_meta > 0 ) {
                $GLOBALS['be_mu_copy_log'] .= 'Site meta data in the ' . $base_prefix . 'blogmeta database table was copied. '
                    . ' Rows inserted: ' . esc_html( $inserted_site_meta ) . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= 'There was no copied site meta data in the ' . $base_prefix . 'blogmeta database table.<br>';
            }
        } else {
            $GLOBALS['be_mu_copy_log'] .= 'Site meta data in the ' . $base_prefix . 'blogmeta database table is not supported.<br>';
        }

        /**
         * In the next section of code we update the site ULR, home, and upload url path options in the database tables we created as
         * a copy and put the correct values.
         */
        $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "options SET option_value = %s WHERE option_name = 'siteurl'", $copy_to_site_url );
        $status = $wpdb->query( $query );

        // Add data to the log variable
        if ( false !== $status ) {
            $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL of the site to paste into. Query: "' . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
        } else {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL of the site to paste into. Query: "' . esc_html( $query )
                . '".</b><br />';
        }

        $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "options SET option_value = %s WHERE option_name = 'home'" , $copy_to_home );
        $status = $wpdb->query( $query );

        // Add data to the log variable
        if ( false !== $status ) {
            $GLOBALS['be_mu_copy_log'] .= 'Updated the home address of the site to paste into. Query: "' . esc_html( $query )
                . '". Rows updated: ' . $status . '<br />';
        } else {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the home address of the site to paste into. Query: "'
                . esc_html( $query ) . '".</b><br />';
        }

        $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "options SET option_value = %s WHERE option_name = 'upload_url_path'" ,
            $copy_to_upload_url_path );
        $status = $wpdb->query( $query );

        // Add data to the log variable
        if ( false !== $status ) {
            $GLOBALS['be_mu_copy_log'] .= 'Updated the upload URL path of the site to paste into. Query: "' . esc_html( $query )
                . '". Rows updated: ' . $status . '<br />';
        } else {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the upload URL path of the site to paste into. Query: "'
                . esc_html( $query ) . '".</b><br />';
        }

        if ( $copy_title !== 'on' ) {

            $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "options SET option_value = %s WHERE option_name = 'blogname'" ,
                $copy_to_title );
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Updated the title of the site to paste into. Query: "' . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the title of the site to paste into. Query: "'
                    . esc_html( $query ) . '".</b><br />';
            }

        }

        if ( $copy_admin_email !== 'on' ) {

            // Make sure it does now show a meesage for changed email address from some old change
            $wpdb->query( "DELETE FROM " . $copy_to_site_prefix . "options WHERE option_name = 'new_admin_email'" );

            $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "options SET option_value = %s WHERE option_name = 'admin_email'" ,
                $copy_to_admin_email );
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Updated the admin email of the site to paste into. Query: "' . esc_html( $query )
                    . '". Rows updated: ' . $status . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the admin email of the site to paste into. Query: "'
                    . esc_html( $query ) . '".</b><br />';
            }

        }

        // We update the prefix for the user roles to be for the site we pasted into, and not the one we copied from
        $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "options SET option_name = %s WHERE option_name = %s",
            $copy_to_site_prefix . 'user_roles', $copy_from_site_prefix . 'user_roles' );
        $status = $wpdb->query( $query );

        // Add data to the log variable
        if ( false !== $status ) {
            $GLOBALS['be_mu_copy_log'] .= 'Updated the user roles of the site to paste into. Query: "' . esc_html( $query )
                . '". Rows updated: ' . $status . '<br />';
        } else {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the user roles of the site to paste into. Query: "'
                . esc_html( $query ) . '".</b><br />';
        }

        /**
         * We get all rows from the usermeta database table from the main site, which meta_key starts with the site prefix of the site we pasted into,
         * so we can delete them (because this is old data, and we already deleted the site)
         */
        $query = "SELECT * FROM " . $base_prefix . "usermeta WHERE LOCATE( '" . $copy_to_site_prefix . "', meta_key ) = 1";
        $results_multi_array = $wpdb->get_results( $query, ARRAY_A );

        if ( ! empty( $results_multi_array ) ) {

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= 'We found ' . count( $results_multi_array )
                . ' rows of user meta data to delete, that belongs to the site to paste into. Query: "' . esc_html( $query ) . '".<br />';

            foreach ( $results_multi_array as $results ) {

                // Make sure one more time, that the meta_key really starts with the site prefix, and then we delete the rows
                if ( substr( $results['meta_key'], 0, strlen( $copy_to_site_prefix ) ) === $copy_to_site_prefix ) {
                    $query = $wpdb->prepare( "DELETE FROM " . $base_prefix . "usermeta WHERE meta_key = %s AND user_id = %d", $results['meta_key'],
                        $results['user_id'] );
                    $status = $wpdb->query( $query );

                    // Add data to the log variable
                    if ( false !== $status ) {
                        $GLOBALS['be_mu_copy_log'] .= 'Deleted user meta data for meta key "' . esc_html( $results['meta_key'] ) . '" and user id "'
                            . esc_html( $results['user_id'] ) . '". Query: "' . esc_html( $query ) . '". Rows deleted: ' . $status . '<br />';
                    } else {
                        $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete user meta data for meta key "'
                            . esc_html( $results['meta_key'] ) . '" and user id "' . esc_html( $results['user_id'] ) . '". Query: "'
                            . esc_html( $query ) . '".</b><br />';
                    }
                }
            }
        } else {

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: We did not find any user meta data to delete, that belongs to the site to paste into, '
                . 'or there could be an error in the query. Query: "' . esc_html( $query ) . '".</b><br />';
        }

        /**
         * We get all rows from the usermeta database table from the main site, which meta_key starts with the site prefix of the site we copy from,
         * so we can duplicate them for the site we paste into
         */
        $query = "SELECT * FROM " . $base_prefix . "usermeta WHERE LOCATE( '" . $copy_from_site_prefix . "', meta_key ) = 1";
        $results_multi_array = $wpdb->get_results( $query, ARRAY_A );

        if ( ! empty( $results_multi_array ) ) {

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= 'We found ' . count( $results_multi_array )
                . ' rows of user meta data to copy. Query: "' . esc_html( $query ) . '".<br />';

            foreach ( $results_multi_array as $results ) {

                // Make sure one more time, that the meta_key really starts with the blog prefix, and then we insert the rows
                if ( substr( $results['meta_key'], 0, strlen( $copy_from_site_prefix ) ) === $copy_from_site_prefix ) {

                    // We insert the same rows but with meta_key starting with the new site prefix
                    $status = $wpdb->insert(
                    	$base_prefix . 'usermeta',
                    	array(
                    		'user_id' => $results['user_id'],
                    		'meta_key' => be_mu_replace_first( $copy_from_site_prefix, $copy_to_site_prefix, $results['meta_key'] ),
                    		'meta_value' => $results['meta_value'],
                    	),
                    	array(
                    		'%d',
                    		'%s',
                    		'%s',
                    	)
                    );

                    // Add data to the log variable
                    if ( false !== $status ) {
                        $GLOBALS['be_mu_copy_log'] .= 'Copied user meta data for user id "' . esc_html( $results['user_id'] )
                            . '" from meta_key "' . esc_html( $results['meta_key'] ) . '" to meta_key "'
                            . esc_html( be_mu_replace_first( $copy_from_site_prefix, $copy_to_site_prefix, $results['meta_key'] ) )
                            . '" in database table "' . esc_html( $base_prefix ) . 'usermeta". Rows inserted: ' . $status . '<br />';
                    } else {
                        $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not copy user meta data for user id "' . esc_html( $results['user_id'] )
                            . '" from meta_key "' . esc_html( $results['meta_key'] ) . '" to meta_key "'
                            . esc_html( be_mu_replace_first( $copy_from_site_prefix, $copy_to_site_prefix, $results['meta_key'] ) )
                            . '" in database table "' . esc_html( $base_prefix ) . 'usermeta"</b><br />';
                    }
                }
            }
        } else {

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: We did not find any user meta data to copy or there could be an error in the query. Query: "'
                . esc_html( $query ) . '".</b><br />';
        }

        /**
         * This array will hold all the old site URLs from the site we copy from, that we need to find and replace in the new site we paste into.
         * They might be more than one, since we will consider any mapped domains too.
         * All site URLs will be replaced with the site URL of the site to paste into.
         */
        $from_site_urls = Array( $copy_from_site_url );

        /**
         * If the WordPress MU Domain Mapping plugin (https://wordpress.org/plugins/wordpress-mu-domain-mapping/) is active and there are any domains
         * mapped to the site we copy from, we add the URLs to our array so we can replace them too.
         */
        if ( is_plugin_active( 'wordpress-mu-domain-mapping/domain_mapping.php' ) ) {
            if ( is_ssl() ) {
                $protocol = 'https://';
            } else {
                $protocol = 'http://';
            }
            $domains = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $wpdb->dmtable . " WHERE blog_id = %d", $copy_from_site_id ), ARRAY_A );
            if ( is_array( $domains ) && ! empty( $domains ) ) {
                foreach ( $domains as $details ) {
                    if ( array_key_exists( 'domain', $details ) && ! empty( $details['domain'] ) ) {
                        $url = $protocol . $details['domain'];
                        if ( array_key_exists( 'path', $details ) && ! empty( $details['path'] ) ) {
                            $url .= $details['path'];
                        }
                        if ( filter_var( $url, FILTER_VALIDATE_URL ) !== FALSE && ! in_array( $url, $from_site_urls ) ) {
                            $from_site_urls[] = esc_url( $url );
                        }
                    }
                }
            }
        }

        /**
         * If the Domain Mapping plugin (https://premium.wpmudev.org/project/domain-mapping/) is active and there are any domains
         * mapped to the site we copy from, we add the URLs to our array so we can replace them too.
         */
        if ( is_plugin_active( 'domain-mapping/domain-mapping.php' ) ) {
            $domains = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $base_prefix
                . "domain_mapping WHERE blog_id = %d", $copy_from_site_id ), ARRAY_A );
            if ( is_array( $domains ) && ! empty( $domains ) ) {
                foreach ( $domains as $details ) {
                    if ( array_key_exists( 'domain', $details ) && ! empty( $details['domain'] ) && array_key_exists( 'scheme', $details ) ) {
                        if ( intval( $details['scheme'] ) == 0 ) {
                            $protocol = 'http://';
                        } else {
                            $protocol = 'https://';
                        }
                        $url = $protocol . $details['domain'];
                        if ( filter_var( $url, FILTER_VALIDATE_URL ) !== FALSE && ! in_array( $url, $from_site_urls ) ) {
                            $from_site_urls[] = esc_url( $url );
                        }
                    }
                }
            }
        }

        // We go through different ways an url can be stored in the database
        for ( $loop = 1; $loop < 7; $loop++ ) {

            /**
             * This is the site URL of the site to paste into, it is only one, the one from the options. It needs to be global because we will use it later
             * in the functions that replace in serialized data.
             */
            if ( 1 === $loop ) {
                $GLOBALS['be_mu_copy_site_replace_with_url'] = $copy_to_site_url;
            } elseif ( 2 === $loop ) {
                $GLOBALS['be_mu_copy_site_replace_with_url'] = urlencode( $copy_to_site_url );
            } elseif ( 3 === $loop ) {
                $GLOBALS['be_mu_copy_site_replace_with_url'] = be_mu_copy_sites_add_slashes_to_slashes( $copy_to_site_url );
            } elseif ( 4 === $loop ) {
                $GLOBALS['be_mu_copy_site_replace_with_url'] = $copy_to_site_url;
            } elseif ( 5 === $loop ) {
                $GLOBALS['be_mu_copy_site_replace_with_url'] = urlencode( $copy_to_site_url );
            } else {
                $GLOBALS['be_mu_copy_site_replace_with_url'] = be_mu_copy_sites_add_slashes_to_slashes( $copy_to_site_url );
            }

            // We go through all the URLs that we need to replace and replace them with the new URL everywhere in the new site we pasted into
            foreach ( $from_site_urls as $current_from_site_url ) {

                // If the URL we will replace with has a slash and the other ones don't, we add one (and the opposite too)
                if ( substr( $GLOBALS['be_mu_copy_site_replace_with_url'], -1 ) == '/' && substr( $current_from_site_url, -1 ) != '/' ) {
                    $current_from_site_url .= '/';
                } elseif ( substr( $GLOBALS['be_mu_copy_site_replace_with_url'], -1 ) != '/' && substr( $current_from_site_url, -1 ) == '/' ) {
                    $current_from_site_url = substr_replace( $current_from_site_url, "", -1 );
                }

                // This is a version of the URL that is http if the original was https or the opposite
                $current_from_site_url_alternative_http = be_mu_alternative_http_url( $current_from_site_url );

                /**
                 * The current site URL we need to replace. It needs to be global because we will use it later
                 * in the functions that replace in serialized data.
                 */
                if ( 1 === $loop ) {
                    $GLOBALS['be_mu_copy_site_search_for_url'] = $current_from_site_url;
                } elseif ( 2 === $loop ) {
                    $GLOBALS['be_mu_copy_site_search_for_url'] = urlencode( $current_from_site_url );
                } elseif ( 3 === $loop ) {
                    $GLOBALS['be_mu_copy_site_search_for_url'] = be_mu_copy_sites_add_slashes_to_slashes( $current_from_site_url );
                } elseif ( 4 === $loop ) {
                    $GLOBALS['be_mu_copy_site_search_for_url'] = $current_from_site_url_alternative_http;
                } elseif ( 5 === $loop ) {
                    $GLOBALS['be_mu_copy_site_search_for_url'] = urlencode( $current_from_site_url_alternative_http );
                } else {
                    $GLOBALS['be_mu_copy_site_search_for_url'] = be_mu_copy_sites_add_slashes_to_slashes( $current_from_site_url_alternative_http );
                }

                // In the next section of code we replace the old site URL with the new one if it is mentioned somewhere in the posts and pages
                $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "posts SET post_content = replace( post_content, %s, %s ) WHERE 1",
                    $GLOBALS['be_mu_copy_site_search_for_url'], $GLOBALS['be_mu_copy_site_replace_with_url'] );
                $status = $wpdb->query( $query );

                // Add data to the log variable
                if ( false !== $status ) {
                    $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in posts and pages content. Query: "'
                        . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
                } else {
                    $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in posts and pages content. Query: "'
                        . esc_html( $query ) . '".</b><br />';
                }

                $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "posts SET post_excerpt = replace( post_excerpt, %s, %s ) WHERE 1",
                    $GLOBALS['be_mu_copy_site_search_for_url'], $GLOBALS['be_mu_copy_site_replace_with_url'] );
                $status = $wpdb->query( $query );

                // Add data to the log variable
                if ( false !== $status ) {
                    $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in posts and pages excerpt. Query: "' . esc_html( $query )
                        . '". Rows updated: ' . $status . '<br />';
                } else {
                    $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in posts and pages excerpt. Query: "'
                        . esc_html( $query ) . '".</b><br />';
                }

                $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "posts SET guid = replace( guid, %s, %s ) WHERE 1",
                    $GLOBALS['be_mu_copy_site_search_for_url'], $GLOBALS['be_mu_copy_site_replace_with_url'] );
                $status = $wpdb->query( $query );

                // Add data to the log variable
                if ( false !== $status ) {
                    $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in posts and pages guid. Query: "' . esc_html( $query )
                        . '". Rows updated: ' . $status . '<br />';
                } else {
                    $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in posts and pages guid. Query: "'
                        . esc_html( $query ) . '".</b><br />';
                }

                $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix
                    . "posts SET post_content_filtered = replace( post_content_filtered, %s, %s ) WHERE 1", $GLOBALS['be_mu_copy_site_search_for_url'],
                        $GLOBALS['be_mu_copy_site_replace_with_url'] );
                $status = $wpdb->query( $query );

                // Add data to the log variable
                if ( false !== $status ) {
                    $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in posts and pages filtered content. Query: "' . esc_html( $query )
                        . '". Rows updated: ' . $status . '<br />';
                } else {
                    $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in posts and pages filtered content. Query: "'
                        . esc_html( $query ) . '".</b><br />';
                }

                // We replace the old site URL with the new one if it is mentioned somewhere in the comments
                $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "comments SET comment_content = replace( comment_content, %s, %s ) WHERE 1",
                    $GLOBALS['be_mu_copy_site_search_for_url'], $GLOBALS['be_mu_copy_site_replace_with_url'] );
                $status = $wpdb->query( $query );

                // Add data to the log variable
                if ( false !== $status ) {
                    $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in comments content. Query: "' . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
                } else {
                    $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in comments content. Query: "' . esc_html( $query )
                        . '".</b><br />';
                }

                // We replace the old site URL with the new one if it is mentioned somewhere in the liks
                $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "links SET link_url = replace( link_url, %s, %s ) WHERE 1",
                    $GLOBALS['be_mu_copy_site_search_for_url'], $GLOBALS['be_mu_copy_site_replace_with_url'] );
                $status = $wpdb->query( $query );

                // Add data to the log variable
                if ( false !== $status ) {
                    $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in links data. Query: "' . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
                } else {
                    $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in links data. Query: "'
                        . esc_html( $query ) . '".</b><br />';
                }

                // We get the options that contain the old URL, so we can replace it with the new one
                $query = $wpdb->prepare( "SELECT * FROM " . $copy_to_site_prefix . "options WHERE LOCATE( %s, option_value ) != 0",
                    $GLOBALS['be_mu_copy_site_search_for_url'] );
                $results_multi_array = $wpdb->get_results( $query, ARRAY_A );

                /*
                 * If the URL of the site to copy from, does not end in '/', and it is contained in the URL of the site to paste into, then
                 * we find the options with the URL of the site to paste into, so we can skip them when replacing the URL later.
                 */
                if ( substr( $GLOBALS['be_mu_copy_site_search_for_url'], -1 ) != '/'
                    && strpos( $GLOBALS['be_mu_copy_site_replace_with_url'], $GLOBALS['be_mu_copy_site_search_for_url'] ) !== false ) {
                    $query_skip = $wpdb->prepare( "SELECT * FROM " . $copy_to_site_prefix . "options WHERE LOCATE( %s, option_value ) != 0",
                        $GLOBALS['be_mu_copy_site_replace_with_url'] );
                    $results_skip_multi_array = $wpdb->get_results( $query_skip, ARRAY_A );
                }

                if ( ! empty( $results_multi_array ) ) {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We found ' . count( $results_multi_array ) . ' rows of options that contain the site URL. Query: "'
                        . esc_html( $query ) . '".<br />';

                    $skipped_IDs = Array();

                    foreach ( $results_multi_array as $results ) {

                        /*
                         * If the URL of the site to copy from, does not end in '/', and it is contained in the URL of the site to paste into, and
                         * we found URLs to skip earlier, we check each option if we should skip it and skip it if need to.
                         */
                        if ( substr( $GLOBALS['be_mu_copy_site_search_for_url'], -1 ) != '/'
                            && strpos( $GLOBALS['be_mu_copy_site_replace_with_url'], $GLOBALS['be_mu_copy_site_search_for_url'] ) !== false
                            && ! empty( $results_skip_multi_array ) ) {
                            $skip_option = 'no';
                            foreach ( $results_skip_multi_array as $results_skip ) {
                                if ( $results_skip['option_id'] == $results['option_id'] ) {
                                    $skip_option = 'yes';
                                    break;
                                }
                            }
                            if ( 'yes' === $skip_option ) {
                                $skipped_IDs[] = intval( $results['option_id'] );
                                continue;
                            }
                        }

                        // We replace the old URL with the new one in the options, while preserving serializied data
                        $new_value = be_mu_copy_sites_replace_url_in_maybe_serialized( $results['option_value'] );

                        // Update the new value of the option
                        $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "options SET option_value = %s WHERE option_id = %d",
                            $new_value, $results['option_id'] );
                        $status = $wpdb->query( $query );

                        // Add data to the log variable
                        if ( false !== $status ) {
                            $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in options data for option ID "' . esc_html( $results['option_id'] )
                                . '". Rows updated: ' . $status . '<br />';
                        } else {
                            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in options data for option ID "'
                                . esc_html( $results['option_id'] ) . '". Query: "' . esc_html( $query ) . '".</b><br />';
                        }
                    }

                    // Add data to the log variable if there were skipped options
                    if ( count( $skipped_IDs ) > 0 ) {
                        $GLOBALS['be_mu_copy_log'] .= 'We skipped the rows with the following option IDs "' . implode( ', ', $skipped_IDs )
                            . '" because they contain the URL of the site to paste into, which also starts with the URL of the site to copy from.<br />';
                    }

                } else {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We did not find any options that contain the site URL or there could be an error in the query. Query: "'
                        . esc_html( $query ) . '".<br />';
                }

                // We get the post meta data that contain the old URL, so we can replace it with the new one
                $query = $wpdb->prepare( "SELECT * FROM " . $copy_to_site_prefix . "postmeta WHERE LOCATE( %s, meta_value ) != 0",
                    $GLOBALS['be_mu_copy_site_search_for_url'] );
                $results_multi_array = $wpdb->get_results( $query, ARRAY_A );

                // We replace the old URL with the new one in the post meta data, while preserving serializied data
                if ( ! empty( $results_multi_array ) ) {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We found ' . count( $results_multi_array ) . ' rows of post meta data that contain the site URL. Query: "'
                        . esc_html( $query ) . '".<br />';

                    foreach ( $results_multi_array as $results ) {
                        $new_value = be_mu_copy_sites_replace_url_in_maybe_serialized( $results['meta_value'] );
                        $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "postmeta SET meta_value = %s WHERE meta_id = %d",
                            $new_value, $results['meta_id'] );
                        $status = $wpdb->query( $query );

                        // Add data to the log variable
                        if ( false !== $status ) {
                            $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in post meta data for meta ID "' . esc_html( $results['meta_id'] )
                                . '". Query: "' . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
                        } else {
                            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in post meta data for meta ID "'
                                . esc_html( $results['meta_id'] ) . '". Query: "' . esc_html( $query ) . '".</b><br />';
                        }
                    }
                } else {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We did not find any post meta data that contains the site URL or there could be an error in the query. Query: "'
                        . esc_html( $query ) . '".<br />';
                }

                // We get the comment meta data that contain the old URL, so we can replace it with the new one
                $query = $wpdb->prepare( "SELECT * FROM " . $copy_to_site_prefix . "commentmeta WHERE LOCATE( %s, meta_value ) != 0",
                    $GLOBALS['be_mu_copy_site_search_for_url'] );
                $results_multi_array = $wpdb->get_results( $query, ARRAY_A );

                // We replace the old URL with the new one in the comment meta data, while preserving serializied data
                if ( ! empty( $results_multi_array ) ) {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We found ' . count( $results_multi_array ) . ' rows of comment meta data that contain the site URL. Query: "'
                        . esc_html( $query ) . '".<br />';

                    foreach ( $results_multi_array as $results ) {
                        $new_value = be_mu_copy_sites_replace_url_in_maybe_serialized( $results['meta_value'] );
                        $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "commentmeta SET meta_value = %s WHERE meta_id = %d",
                            $new_value, $results['meta_id'] );
                        $status = $wpdb->query( $query );

                        // Add data to the log variable
                        if ( false !== $status ) {
                            $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in comment meta data for meta ID "' . esc_html( $results['meta_id'] )
                                . '". Query: "' . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
                        } else {
                            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in comment meta data for meta ID "'
                                . esc_html( $results['meta_id'] ) . '". Query: "' . esc_html( $query ) . '".</b><br />';
                        }
                    }
                } else {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We did not find any comment meta data that contains the site URL or there could be an error in the query. Query: "'
                        . esc_html( $query ) . '".<br />';
                }

                // We get the term meta data that contain the old URL, so we can replace it with the new one
                $query = $wpdb->prepare( "SELECT * FROM " . $copy_to_site_prefix . "termmeta WHERE LOCATE( %s, meta_value ) != 0",
                    $GLOBALS['be_mu_copy_site_search_for_url'] );
                $results_multi_array = $wpdb->get_results( $query, ARRAY_A );

                // We replace the old URL with the new one in the term meta data, while preserving serializied data
                if ( ! empty( $results_multi_array ) ) {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We found ' . count( $results_multi_array ) . ' rows of term meta data that contain the site URL. Query: "'
                        . esc_html( $query ) . '".<br />';

                    foreach ( $results_multi_array as $results ) {
                        $new_value = be_mu_copy_sites_replace_url_in_maybe_serialized( $results['meta_value'] );
                        $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "termmeta SET meta_value = %s WHERE meta_id = %d",
                            $new_value, $results['meta_id'] );
                        $status = $wpdb->query( $query );

                        // Add data to the log variable
                        if ( false !== $status ) {
                            $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in term meta data for meta ID "' . esc_html( $results['meta_id'] )
                                . '". Query: "' . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
                        } else {
                            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in term meta data for meta ID "'
                                . esc_html( $results['meta_id'] ) . '". Query: "' . esc_html( $query ) . '".</b><br />';
                        }
                    }
                } else {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We did not find any term meta data that contains the site URL or there could be an error in the query. Query: "'
                        . esc_html( $query ) . '".<br />';
                }

            // Here ends the foreach that goes through all URLs that need replacing
            }

        // We go through different ways an url can be stored in the database
        }

        // We get the blog details for the site to copy from
        $copy_from_attributes = get_blog_details( $copy_from_site_id );

        if ( is_object( $copy_from_attributes ) ) {

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= 'We got the blog details of the site to copy from<br />';

            // We set the blog details of the site to paste into the same as the one we copy from
            $status = update_blog_details(
                $copy_to_site_id,
                array(
                    'public' => $copy_from_attributes->public,
                    'archived' => $copy_from_attributes->archived,
                    'mature' => $copy_from_attributes->mature,
                    'spam' => $copy_from_attributes->spam,
                    'deleted' => $copy_from_attributes->deleted,
                    'lang_id' => $copy_from_attributes->lang_id,
                )
            );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Copied the blog details<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not copy the blog details</b><br />';
            }
        } else {

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not get the blog details of the site to copy from</b><br />';
        }

        // We delete these options where we storred some data temporarily
        $settings = Array(
            'be_mu_copy_sites_copy_to_site_prefix_' . $task_id,
            'be_mu_copy_sites_copy_to_site_url_' . $task_id,
            'be_mu_copy_sites_copy_to_home_' . $task_id,
            'be_mu_copy_sites_copy_to_db_name_' . $task_id,
            'be_mu_copy_sites_copy_to_upload_path_' . $task_id,
            'be_mu_copy_sites_copy_to_upload_url_path_' . $task_id,
        );

        if ( $copy_title !== 'on' ) {
            $settings[] = 'be_mu_copy_sites_copy_to_blogname_' . $task_id;
        }

        if ( $copy_admin_email !== 'on' ) {
            $settings[] = 'be_mu_copy_sites_copy_to_admin_email_' . $task_id;
        }

        foreach ( $settings as $setting ) {
            $status = be_mu_delete_setting( $setting );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Deleted the site option named "' . $setting . '"<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete the site option named "' . $setting . '"</b><br />';
            }
        }

        // Delete all posts in the finished site copy
        if ( be_mu_get_setting( 'be-mu-copy-site-no-posts' ) === 'on' ) {
            $query = "DELETE FROM " . $copy_to_site_prefix . "posts WHERE post_type = 'post'";
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Deleted copied posts. Query: "' . esc_html( $query ) . '". Rows deleted: ' . $status . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete copied posts. Query: "' . esc_html( $query ) . '".</b><br />';
            }
        }

        // Delete all pages in the finished site copy
        if ( be_mu_get_setting( 'be-mu-copy-site-no-pages' ) === 'on' ) {
            $query = "DELETE FROM " . $copy_to_site_prefix . "posts WHERE post_type = 'page'";
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Deleted copied pages. Query: "' . esc_html( $query ) . '". Rows deleted: ' . $status . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete copied pages. Query: "' . esc_html( $query ) . '".</b><br />';
            }
        }

        if ( be_mu_get_setting( 'be-mu-copy-site-no-posts' ) === 'on' || be_mu_get_setting( 'be-mu-copy-site-no-pages' ) === 'on' ) {

            // Delete all revisions of posts (from any post type) that do not exist
            $query = "DELETE FROM " . $copy_to_site_prefix . "posts WHERE post_type = 'revision' "
                . "AND post_parent NOT IN ( SELECT ID FROM " . $copy_to_site_prefix . "posts WHERE 1 )";
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Deleted copied revisions of posts (from any post type) that do not exist. Query: "'
                    . esc_html( $query ) . '". Rows deleted: ' . $status . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete copied revisions of posts (from any post type) that do not exist. Query: "'
                    . esc_html( $query ) . '".</b><br />';
            }

            // Delete all post meta data of posts (from any post type) that do not exist
            $query = "DELETE FROM " . $copy_to_site_prefix . "postmeta WHERE "
                . "post_id NOT IN ( SELECT ID FROM " . $copy_to_site_prefix . "posts WHERE 1 )";
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Deleted copied post meta data of posts (from any post type) that do not exist. Query: "'
                    . esc_html( $query ) . '". Rows deleted: ' . $status . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete copied post meta data of posts '
                    . '(from any post type) that do not exist. Query: "' . esc_html( $query ) . '".</b><br />';
            }

            // Delete all comments for posts (from any post type) that do not exist
            $query = "DELETE FROM " . $copy_to_site_prefix . "comments WHERE "
                . "comment_post_ID NOT IN ( SELECT ID FROM " . $copy_to_site_prefix . "posts WHERE 1 )";
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Deleted copied comments for posts (from any post type) that do not exist. Query: "'
                    . esc_html( $query ) . '". Rows deleted: ' . $status . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete copied comments for posts (from any post type) that do not exist. Query: "'
                    . esc_html( $query ) . '".</b><br />';
            }

            // Delete all comment meta data for comments (from any comment type) that do not exist
            $query = "DELETE FROM " . $copy_to_site_prefix . "commentmeta WHERE "
                . "comment_id NOT IN ( SELECT comment_ID FROM " . $copy_to_site_prefix . "comments WHERE 1 )";
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Deleted copied comment meta data for comments (from any comment type) that do not exist. Query: "'
                    . esc_html( $query ) . '". Rows deleted: ' . $status . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete copied comment meta data for comments (from any comment type) '
                    . 'that do not exist. Query: "' . esc_html( $query ) . '".</b><br />';
            }

            // Update comment count numbers
            switch_to_blog( $copy_to_site_id );
            delete_transient( 'wc_count_comments' );
            restore_current_blog();
        }

        // Delete all categories in the finished site copy
        if ( be_mu_get_setting( 'be-mu-copy-site-no-categories' ) === 'on' ) {
            switch_to_blog( $copy_to_site_id );
            $terms = get_terms( array( 'taxonomy' => 'category', 'fields' => 'ids', 'hide_empty' => false ) );
            if ( is_array( $terms ) ) {
                foreach ( $terms as $value ) {
                    wp_delete_term( $value, 'category' );
                }
            }
            restore_current_blog();
            $GLOBALS['be_mu_copy_log'] .= 'Deleted copied post categories if any.<br />';
        }

        // Delete all tags in the finished site copy
        if ( be_mu_get_setting( 'be-mu-copy-site-no-tags' ) === 'on' ) {
            switch_to_blog( $copy_to_site_id );
            $terms = get_terms( array( 'taxonomy' => 'post_tag', 'fields' => 'ids', 'hide_empty' => false ) );
            if ( is_array( $terms ) ) {
                foreach ( $terms as $value ) {
                    wp_delete_term( $value, 'post_tag' );
                }
            }
            restore_current_blog();
            $GLOBALS['be_mu_copy_log'] .= 'Deleted copied post tags if any.<br />';
        }

        // Delete all attachment posts in the finished site copy
        if ( be_mu_get_setting( 'be-mu-copy-site-no-media' ) === 'on' ) {
            $query = "DELETE FROM " . $copy_to_site_prefix . "posts WHERE post_type = 'attachment'";
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Deleted copied attachments (post type "attachment"). Query: "' . esc_html( $query )
                    . '". Rows deleted: ' . $status . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete copied attachments. Query: "' . esc_html( $query ) . '".</b><br />';
            }
        }

        // We delete all cache to avoid conflicts with redis cache
        $status = wp_cache_flush();
        if ( false !== $status ) {
            $GLOBALS['be_mu_copy_log'] .= 'Cache flushed with wp_cache_flush().<br />';
        } else {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not flush the cache with wp_cache_flush().</b><br />';
        }

        // This is the data to pass to the javascript function in json format
        $json_result = array(
            'limitReached' => $limit_reached,
            'editURL' => esc_url( network_admin_url( 'site-info.php?id=' . $copy_to_site_id ) ),
            'dashboardURL' => esc_url( get_admin_url( $copy_to_site_id ) ),
            'visitURL' => $copy_to_site_url,
            'viewLogURL' => network_admin_url( 'sites.php?page=be_mu_copy_sites_logs&task_id=' . $task_id . '&action=view' ),
            'errorCount' => substr_count( $GLOBALS['be_mu_copy_log'], '<b class="be-mu-red">Error:'),
        );

        // Output the results in json format
        echo json_encode( $json_result );

        // Add data to the log variable
        $GLOBALS['be_mu_copy_log'] .= 'JSON results: ' . json_encode( $json_result ) . '<br />';
        $GLOBALS['be_mu_copy_log'] .= 'Stage 9 is done at ' . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . ')<br />';
        $GLOBALS['be_mu_copy_log'] .= 'Request ended<br />';

        // Maybe we add the log data to the database, maybe we delete unwanted old logs, and we end the request (maybe also output an error code)
        be_mu_copy_sites_maybe_log_and_die( $task_id, $stages_done, '' );
    }

    // This is the data to pass to the javascript function in json format
    $json_result = array(
        'newStagesDone' => $stages_done,
        'newNextStagePartsDone' => $next_stage_parts_done,
        'limitReached' => $limit_reached,
    );

    // Output the results in json format
    echo json_encode( $json_result );

    // Add data to the log variable
    $GLOBALS['be_mu_copy_log'] .= 'JSON results: ' . json_encode( $json_result ) . '<br />';
    $GLOBALS['be_mu_copy_log'] .= 'Request ended<br />';

    // Maybe we add the log data to the database, maybe we delete unwanted old logs, and we end the request (maybe also output an error code)
    be_mu_copy_sites_maybe_log_and_die( $task_id, $stages_done, '' );
}

// Maybe we add the log data to the database, maybe we delete unwanted old logs, and we end the request (maybe also output an error code)
function be_mu_copy_sites_maybe_log_and_die( $task_id, $stages_done, $die_message ) {

    // We add the log data to the database only if it is not empty or it is not the first request.
    if ( '' !== $GLOBALS['be_mu_copy_log'] || 0 !== $stages_done ) {

        // Add data to the log variable
        if ( '' !== $die_message ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: The request ended due to a fatal error with code: "' . $die_message . '"</b><br />';
        }

        // We get any log data from a previous request for the same task
        $current_log_array = be_mu_get_log_data( $task_id );

        // If there is any log data from a previous request for the same task we merge it with the new data when we add it to the database
        if ( is_array( $current_log_array ) ) {

            // We need these to connect to the database
            global $wpdb;
            $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
            $db_table = $main_blog_prefix . 'be_mu_logs';

            // We delete the data from previous requests for the current task, because we will add the whole log again
            $wpdb->query( $wpdb->prepare( "DELETE FROM " . $db_table . " WHERE task_id = %s", $task_id ) );

            // Add the log data to the database
            be_mu_add_log_data( 'copy sites', $task_id, $current_log_array['log'] . $GLOBALS['be_mu_copy_log'] );

        // If there is no log data from a previous request for the same task, we just add the new log data
        } else {
            be_mu_add_log_data( 'copy sites', $task_id, $GLOBALS['be_mu_copy_log'] );
        }

        // We delete the unwanted logs based on the selected setting
        be_mu_copy_sites_delete_unwanted_logs();
    }

    // We end the request and maybe output an error code
    wp_die( $die_message );
}

/**
 * Replaces the old URL with the new URL (getting them from global variables) recursively in an array.
 * It is called by array_walk_recursive.
 * @param mixed &$value
 * @param mixed $key
 */
function be_mu_copy_sites_replace_url_in_array( &$value, $key ) {
    if ( is_string( $value ) ) {
        $value = str_replace( $GLOBALS['be_mu_copy_site_search_for_url'], $GLOBALS['be_mu_copy_site_replace_with_url'], $value );
    } elseif ( is_object( $value ) ) {
        be_mu_copy_sites_replace_url_in_object( $value );
    } elseif ( is_array( $value ) ) {
        array_walk_recursive( $value, 'be_mu_copy_sites_replace_url_in_array' );
    }
}

/**
 * Replaces the old URL with the new URL (getting them from global variables) recursively in an object.
 * It is called by array_walk_recursive.
 * @param mixed &$object
 */
function be_mu_copy_sites_replace_url_in_object( &$object ) {
    foreach( $object as $object_key => &$object_value ) {
        if ( is_string( $object_value ) ) {
            $object_value = str_replace( $GLOBALS['be_mu_copy_site_search_for_url'], $GLOBALS['be_mu_copy_site_replace_with_url'], $object_value );
        } elseif ( is_array( $object_value ) ) {
            array_walk_recursive( $object_value, 'be_mu_copy_sites_replace_url_in_array' );
        } elseif ( is_object( $object_value ) ) {
            be_mu_copy_sites_replace_url_in_object( $object_value );
        }
    }
}

/**
 * Replaces the old URL with the new URL (getting them from global variables) in a provided string that might be serialized (in which case it returns it
 * also serialized).
 * @param string $value
 * @return string
 */
function be_mu_copy_sites_replace_url_in_maybe_serialized( $value ) {

    // The unserialized version of the provided string
    $unserialized = maybe_unserialize( $value );

    // If the unserialized version is different, then the string was serialized
    if ( $unserialized !== $value ) {

        // If the unserialized value is an array we apply the replacement to all elements with array_walk_recursive and we return the serialized version
        if ( is_array( $unserialized ) ) {
            array_walk_recursive( $unserialized, 'be_mu_copy_sites_replace_url_in_array' );
            return maybe_serialize( $unserialized );

        // If the unserialized value is a string we apply the replacement to it and we return the serialized version
        } elseif ( is_string( $unserialized ) ) {
            $new_string = str_replace( $GLOBALS['be_mu_copy_site_search_for_url'], $GLOBALS['be_mu_copy_site_replace_with_url'], $unserialized );
            return maybe_serialize( $new_string );

        } elseif ( is_object( $unserialized ) ) {
            be_mu_copy_sites_replace_url_in_object( $unserialized );
            return maybe_serialize( $unserialized );

        // If the unserialized value is not an array or a string or an object we return the original value without replacing
        } else {
            return $value;
        }

    // If the original string was not serialized we just replace the URL in it and return it
    } else {
        return str_replace( $GLOBALS['be_mu_copy_site_search_for_url'], $GLOBALS['be_mu_copy_site_replace_with_url'], $value );
    }
}

/**
 * Puts two global arrays with file and folder paths to the database (in the options) for future usage.
 * @param string $task_id
 */
function be_mu_copy_sites_save_arrays_to_database( $task_id, $mode ) {
    if ( 'delete' === $mode ) {
        be_mu_set_or_make_setting( 'be_mu_copy_sites_delete_files_array_' . $task_id, implode( ';;', $GLOBALS['be_mu_copy_sites_delete_files'] ) );
        be_mu_set_or_make_setting( 'be_mu_copy_sites_delete_folders_array_' . $task_id, implode( ';;', $GLOBALS['be_mu_copy_sites_delete_folders'] ) );
    } else {
        be_mu_set_or_make_setting( 'be_mu_copy_sites_copy_files_array_' . $task_id, implode( ';;', $GLOBALS['be_mu_copy_sites_copy_files'] ) );
        be_mu_set_or_make_setting( 'be_mu_copy_sites_copy_folders_array_' . $task_id, implode( ';;', $GLOBALS['be_mu_copy_sites_copy_folders'] ) );
    }
}

/**
 * For a given folder it puts all file paths and all subdirectory paths each into a global array.
 * @param string $folder_path
 * @param string $mode
 * @param string $space
 */
function be_mu_copy_sites_get_files_and_folders( $folder_path, $mode, $space ) {

    // Open the folder
    $folder = @opendir( $folder_path );

    // Add data to the log variable
    if ( is_resource( $folder ) ) {
        $GLOBALS['be_mu_copy_log'] .= $space . 'Opened the directory "' . esc_html( be_mu_strip_before_substring( $folder_path, 'wp-content' ) ) . '"<br />';
    } else {
        $GLOBALS['be_mu_copy_log'] .= $space . 'Error: Could not open the directory "'
            . esc_html( be_mu_strip_before_substring( $folder_path, 'wp-content' ) ) . '"<br />';
    }

    // This space will be added to the log every time we go one level inside a folder
    $space .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

    // While there are files and folders we read them
    while ( false !== ( $file = readdir( $folder ) ) ) {

        if ( ( '.' !== $file ) && ( '..' !== $file ) ) {

            // If it is a directory
            if ( is_dir( $folder_path . '/' . $file ) ) {

                // Based on the mode we add the folder path to the appropriate global array
                if ( 'delete' === $mode ) {
                    $GLOBALS['be_mu_copy_sites_delete_folders'][] = $folder_path . '/' . $file;
                } else {
                    $GLOBALS['be_mu_copy_sites_copy_folders'][] = $folder_path . '/' . $file;
                }

                // Add data to the log variable
                $GLOBALS['be_mu_copy_log'] .= $space . 'Found the directory "' . esc_html( $file ) . '"<br />';

                // We continue going through the folders recursively
                be_mu_copy_sites_get_files_and_folders( $folder_path . '/' . $file, $mode, $space );

            // If it is a file
            } else {

                // Based on the mode we add the file path to the appropriate global array
                if ( 'delete' === $mode ) {
                    $GLOBALS['be_mu_copy_sites_delete_files'][] = $folder_path . '/' . $file;
                } else {
                    $GLOBALS['be_mu_copy_sites_copy_files'][] = $folder_path . '/' . $file;
                }

                // Add data to the log variable
                $GLOBALS['be_mu_copy_log'] .= $space . 'Found the file "' . esc_html( $file ) . '"<br />';
            }
        }
    }

    // Close the folder
    closedir( $folder );

    // Remove some space from the space variable on every folder close
    $space = be_mu_replace_first( "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", "", $space );

    // Add data to the log variable
    $GLOBALS['be_mu_copy_log'] .= $space . 'Closed the directory<br />';
}

// Deletes the unwanted logs of copied sites from the database, based on the chosen setting for how many logs to store
function be_mu_copy_sites_delete_unwanted_logs() {

    // We get the setting about how many logs to store
    $to_store = be_mu_get_setting( 'be-mu-copy-store-logs', 'Last 20' );

    // If all logs are to be storred, we just set the count to a huge number
    if ( 'All' === $to_store ) {
        $to_store_count = 99999999;

    // If a setting was chosen to store the last X logs, we break the string into two parts and get the number as the count
    } else {
        $to_store_parts = explode( ' ', $to_store );
        $to_store_count = intval( $to_store_parts[1] );

        // If something went wrong and the value is not valid, we use the value 20
        if ( $to_store_count < 10 || $to_store_count > 500 ) {
            $to_store_count = 20;
        }
    }

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
    $db_table = $main_blog_prefix . 'be_mu_logs';

    // The current count of logs there are in the database
    $logs_count = $wpdb->get_var( "SELECT COUNT( row_id ) FROM " . $db_table . " WHERE type = 'copy sites'" );

    // If currently there are more logs than we need to store, we delete the rest (the older ones)
    if ( $logs_count > $to_store_count ) {

        // This many logs we need to delete
        $to_delete = $logs_count - $to_store_count;

        // We delete the unwanted logs
        $wpdb->query( "DELETE FROM " . $db_table . " WHERE type = 'copy sites' ORDER BY unix_time_added ASC LIMIT " . intval( $to_delete ) );
    }
}

/**
 * If the Peepso plugin is activated for a site and the needed class and method exist it returns true.
 * @param int $site_id
 * @return bool
 */
function be_mu_copy_sites_is_peepso_active( $site_id ) {
    if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    if ( is_plugin_active_for_network( 'peepso-core/peepso.php' ) ) {
        return true;
    }
    $site_activated_plugins = get_blog_option( $site_id, 'active_plugins' );
    if ( is_array( $site_activated_plugins ) && in_array( 'peepso-core/peepso.php', $site_activated_plugins ) ) {
        return true;
    }
    return false;
}

/**
 * If the peepso folder path exists we return it
 * @param int $site_id
 * @return mixed
 */
function be_mu_copy_sites_get_peepso_directory_if_exists( $site_id ) {
    $try_path = WP_CONTENT_DIR . '/peepso-' . $site_id;
    if ( file_exists( $try_path ) && is_dir( $try_path ) ) {
        return $try_path;
    }
    return false;
}

/**
 * Adds slashes to slashes in a string
 * @param string $string
 * @return string
 */
function be_mu_copy_sites_add_slashes_to_slashes( $string ) {
    return str_replace( '/', '\\/', $string );
}

/**
 * Copies a site for some time and saves the progress
 * @param int $copy_from_site_id
 * @param int $copy_to_site_id
 * @param int $seconds_to_work
 * @param string $continue_task_id
 * @return array
 */
function be_mu_copy_sites_copy_for_seconds( $copy_from_site_id, $copy_to_site_id, $seconds_to_work, $continue_task_id = '' ) {

    // This is the time we start working on this request
    $time_start = microtime( true );

    // We are starting a copy process
    if ( '' === $continue_task_id ) {
        $task_id = be_mu_random_string( 10 );
        $continuing_task = 'no';

    // We are continuing an already started copy process
    } else {
        $task_id = $continue_task_id;
        $continuing_task = 'yes';
    }

    do_action( 'beyond-multisite-start-copy-site-for-seconds', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
        $task_id, $continuing_task, $time_start );

    if ( 'yes' === $continuing_task ) {
        $GLOBALS['be_mu_copy_log'] = 'Started pre-request error checks for copy task from site ID "' . esc_html( $copy_from_site_id ) . '" to site ID "'
            . esc_html( $copy_to_site_id ) . '" at ' . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . '). Task ID: "'
            . esc_html( $task_id ) . '"<br />';
    } else {
        $GLOBALS['be_mu_copy_log'] = 'Started pre-copy error checks for copy task from site ID "' . esc_html( $copy_from_site_id )
            . '" to site ID "' . esc_html( $copy_to_site_id ) . '" at ' . esc_html( be_mu_unixtime_to_wp_datetime( time() ) )
            . ' (Unix time: ' . time() . '). Task ID: "' . esc_html( $task_id ) . '"<br />';
    }

    $result_checks = be_mu_copy_sites_pre_request_checks( $copy_from_site_id, $copy_to_site_id, $task_id, $continuing_task );

    // Handle error on initial checks
    if ( 'ok' !== $result_checks ) {
        if ( 'main-site' === $result_checks ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Copying from or pasting into the main site is not allowed.<b><br>';
        } elseif ( 'invalid-from-id' === $result_checks ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: The site ID for the site to copy from is invalid.<b><br>';
        } elseif ( 'invalid-to-id' === $result_checks ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: The site ID for the site to paste into is invalid.<b><br>';
        } elseif ( 'same-ids' === $result_checks ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: The site ID to paste into is the same as the site ID to copy from.<b><br>';
        } elseif ( 'from-site-not-exist' === $result_checks ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: The site you are trying to copy from does not exist.<b><br>';
        } elseif ( 'to-site-not-exist' === $result_checks ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: The site you are trying to paste into does not exist.<b><br>';
        } elseif ( 'no-site-url-copy-from' === $result_checks ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Cannot get the URL of the site to copy from.<b><br>';
        } elseif ( 'no-site-url-copy-to' === $result_checks ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Cannot get the URL of the site to paste into.<b><br>';
        } elseif ( 'upload-path-not-reliable' === $result_checks ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: It seems that your network is created a long time ago in a very old version of WordPress and it '
                . 'might not be compatible with this module of the plugin. Please contact our support to investigate this. '
                . 'Sorry for the inconvenience.<b><br>';
        } elseif ( 'same-upload-folders' === $result_checks ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: The upload folders of both sites have the same path.<b><br>';
        } elseif ( 'cannot-read-from-folder' === $result_checks ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Cannot read from the uploads folder of the site to copy from.<b><br>';
        } elseif ( 'cannot-write-to-folder' === $result_checks ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Cannot write to the uploads folder of the site to paste into.<b><br>';
        } elseif ( 'invalid-continue-data' === $result_checks ) {
            $task_data = get_site_option( 'be-mu-copy-site-on-creation-continue-for-' . intval( $copy_to_site_id ) );
            if ( is_array( $task_data ) ) {
                $log_task_data = print_r( $task_data, true );
            }
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Cannot continue due to stored invalid task data: '
                . esc_html( $log_task_data ) . '<b><br>';
        }
        be_mu_copy_sites_add_to_log( $task_id );
        if ( 'yes' === $continuing_task ) {
            delete_site_option( 'be-mu-copy-site-on-creation-continue-for-' . intval( $copy_to_site_id ) );

            do_action( 'beyond-multisite-end-copy-site-for-seconds', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
                $task_id, $continuing_task, microtime( true ), -1, 'pre-request-fatal-error' );

            return Array( 'status' => 'pre-request-fatal-error', 'task-id' => $task_id );
        }

        do_action( 'beyond-multisite-end-copy-site-for-seconds', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
            $task_id, $continuing_task, microtime( true ), -1, 'pre-copy-error' );

        return Array( 'status' => 'pre-copy-error', 'task-id' => $task_id );
    }

    // We are starting a copy process
    if ( 'no' === $continuing_task ) {

        // How many stages are done so far (for the first request, this should be 0)
        $stages_done = 0;

        // How many parts are done from the next stage that is not done (for example how many files have copied from the next stage)
        $next_stage_parts_done = 0;

        $GLOBALS['be_mu_copy_log'] .= esc_html( 'Started copying from site ID "' . $copy_from_site_id . '" to site ID "' . $copy_to_site_id . '" at '
            . be_mu_unixtime_to_wp_datetime( time() ) . ' (Unix time: ' . time() . '). Task ID: "' . $task_id ) . '"<br />';

    // We are continuing an already started copy process
    } else {

        $continue_site_copy = get_site_option( 'be-mu-copy-site-on-creation-continue-for-' . $copy_to_site_id );

        // How many stages are done so far (for the first request, this should be 0)
        $stages_done = $continue_site_copy['stages-done'];

        // How many parts are done from the next stage that is not done (for example how many files have copied from the next stage)
        $next_stage_parts_done = $continue_site_copy['next-stage-parts-done'];
    }

    $GLOBALS['be_mu_copy_limit_time'] = $seconds_to_work;

    // Have we reached a limit in time in this request, if 0 means no, if 1 means yes. If yes, we will stop and continue on the next request.
    $limit_reached = 0;

    // The site ID of the main site
    $main_site_id = be_mu_get_main_site_id();

    // Add data to the log variable
    $GLOBALS['be_mu_copy_log'] .= 'New request started to work (after error checks) at ' . esc_html( be_mu_unixtime_to_wp_datetime( time() ) )
        . ' (Unix time: ' . time() . ').<br />';

    // We need this to connect to the database
    global $wpdb;

    // If stage 1 is done and stage 3 is not done, we get the data about the files and folders we need to delete (and then delete it from the database)
    if ( $stages_done < 3 && $stages_done > 0 ) {
        $GLOBALS['be_mu_copy_sites_delete_files'] = explode( ';;', be_mu_get_setting( 'be_mu_copy_sites_delete_files_array_' . $task_id ) );
        $GLOBALS['be_mu_copy_sites_delete_folders'] = explode( ';;', be_mu_get_setting( 'be_mu_copy_sites_delete_folders_array_' . $task_id ) );
        be_mu_delete_setting( 'be_mu_copy_sites_delete_files_array_' . $task_id );
        be_mu_delete_setting( 'be_mu_copy_sites_delete_folders_array_' . $task_id );
    }

    // If stage 3 is done and stage 6 is not done, we get the data about the files and folders we need to copy (and then delete it from the database)
    if ( $stages_done < 6 && $stages_done > 2 ) {
        $GLOBALS['be_mu_copy_sites_copy_files'] = explode( ';;', be_mu_get_setting( 'be_mu_copy_sites_copy_files_array_' . $task_id ) );
        $GLOBALS['be_mu_copy_sites_copy_folders'] = explode( ';;', be_mu_get_setting( 'be_mu_copy_sites_copy_folders_array_' . $task_id ) );
        be_mu_delete_setting( 'be_mu_copy_sites_copy_files_array_' . $task_id );
        be_mu_delete_setting( 'be_mu_copy_sites_copy_folders_array_' . $task_id );
    }

    // Begin Stage 1: Finding files and folders for deletion from the site to paste into
    if ( $stages_done < 1 ) {

        // Add data to the log variable
        $GLOBALS['be_mu_copy_log'] .= '<a name="stage1"></a><br /><b>Started stage 1</b> (finding files and folders for deletion from the site to paste into) at '
            . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . ')<br />';

        // The path to the uploads folder of the site to paste into
        $upload_dir_to = be_mu_get_site_upload_folder( $copy_to_site_id );

        $GLOBALS['be_mu_copy_sites_delete_files'] = $GLOBALS['be_mu_copy_sites_delete_folders'] = Array();

        // We get all paths of all files and folders from the uploads folder of the site to paste into, and store them in two global arrays
        be_mu_copy_sites_get_files_and_folders( $upload_dir_to, 'delete', '' );

        // If this peepso plugin is active we need to delete some other files as well
        if ( be_mu_copy_sites_is_peepso_active( $copy_from_site_id ) ) {
            $from_peepso_dir = be_mu_copy_sites_get_peepso_directory_if_exists( $copy_from_site_id );
            $to_peepso_dir = be_mu_copy_sites_get_peepso_directory_if_exists( $copy_to_site_id );
            if ( false !== $from_peepso_dir && false !== $to_peepso_dir ) {
                be_mu_copy_sites_get_files_and_folders( $to_peepso_dir, 'delete', '' );
            }
        }

        // We reverse the array so the parent folders are deleted last
        $GLOBALS['be_mu_copy_sites_delete_folders'] = array_reverse( $GLOBALS['be_mu_copy_sites_delete_folders'] );

        // Stage 1 is done
        $stages_done = 1;

        // Add data to the log variable
        $GLOBALS['be_mu_copy_log'] .= 'Stage 1 is done at ' . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . ')<br />';

        do_action( 'beyond-multisite-copy-site-for-seconds-stage-done', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
            $task_id, $continuing_task, microtime( true ), $stages_done );
    }

    /**
     * If the time limit for the request has been reached, we store the global arrays with files and foldes to the database and set the $limit_reached
     * variable to 1, which will skip all next stages below for this request.
     */
    if ( ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
        $limit_reached = 1;
        be_mu_copy_sites_save_arrays_to_database( $task_id, 'delete' );
    }

    // Begin Stage 2: Deleting files from the site to paste into
    if ( $stages_done < 2 && 0 === $limit_reached ) {

        // Add data to the log variable if this is the first time we start this stage for this task
        if ( 0 === $next_stage_parts_done ) {
            $GLOBALS['be_mu_copy_log'] .= '<a name="stage2"></a><br /><b>Started stage 2</b> (deleting files from the site to paste into) at '
                . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . ')<br />';
        }

        // How many files we have skipped so far (because they are done from the previous request)
        $skipped = 0;

        // How many files we have deleted so far
        $done = 0;

        // We go through all files of the uploads folder of the site to paste into
        foreach ( $GLOBALS['be_mu_copy_sites_delete_files'] as $file ) {

            // If we have already deleted some of the files in the previous request, we will skip the same amount
            if ( $next_stage_parts_done > 0 && $skipped < $next_stage_parts_done ) {
                $skipped++;
                continue;
            }

            if ( ! empty( $file ) ) {

                // We delete the current file and increase the counter for the number of files deleted
                $status = unlink( $file );
                $done++;

                // Add data to the log variable
                if ( $status ) {
                    $GLOBALS['be_mu_copy_log'] .= esc_html( 'Deleted the file "' . be_mu_strip_before_substring( $file, 'wp-content' ) . '". Parts done: '
                        . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done ) . '<br />';
                } else {
                    $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete the file "'
                        . esc_html( be_mu_strip_before_substring( $file, 'wp-content' ) ) . '". Parts done: '
                        . esc_html( $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done ) . '</b><br />';
                }

            }

            // After every file deletion we check the time passed and if the time limit is reached we will stop and continue in the next request
            if ( ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
                $limit_reached = 1;
                break;
            }
        }

        /**
         * If the time limit was reached we add the number of files we deleted in this request to the number we did in the previous request
         * so we know from where to start the next time. We also save the arrays with file and folder paths to the databse.
         */
        if ( 1 === $limit_reached ) {
            $next_stage_parts_done += $done;
            be_mu_copy_sites_save_arrays_to_database( $task_id, 'delete' );

        // If a time limit was not reached, stage 2 is done and 0 parts from the next stage are done
        } else {
            $stages_done = 2;
            $next_stage_parts_done = 0;

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= 'Stage 2 is done at ' . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . ')<br />';

            do_action( 'beyond-multisite-copy-site-for-seconds-stage-done', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
                $task_id, $continuing_task, microtime( true ), $stages_done );
        }
    }

    /**
     * If the time limit for the request has been reached, we store the global arrays with files and foldes to the database and set the $limit_reached
     * variable to 1, which will skip all next stages below for this request.
     */
    if ( 0 === $limit_reached && ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
        $limit_reached = 1;
        be_mu_copy_sites_save_arrays_to_database( $task_id, 'delete' );
    }

    // Begin Stage 3: Deleting folders from the site to paste into
    if ( $stages_done < 3 && 0 === $limit_reached ) {

        // Add data to the log variable if this is the first time we start this stage for this task
        if ( 0 === $next_stage_parts_done ) {
            $GLOBALS['be_mu_copy_log'] .= '<a name="stage3"></a><br /><b>Started stage 3</b> (deleting folders from the site to paste into) at '
                . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . ')<br />';
        }

        // How many folders we have skipped so far (because they are done from the previous request)
        $skipped = 0;

        // How many folders we have deleted so far
        $done = 0;

        // We go through all folders of the uploads folder of the site to paste into
        foreach ( $GLOBALS['be_mu_copy_sites_delete_folders'] as $folder ) {

            // If we have already deleted some of the folders in the previous request, we will skip the same amount
            if ( $next_stage_parts_done > 0 && $skipped < $next_stage_parts_done ) {
                $skipped++;
                continue;
            }

            // We delete the current folder and increase the counter for the number of folders deleted
            $status = @rmdir( $folder );
            $done++;

            // Add data to the log variable
            if ( $status ) {
                $GLOBALS['be_mu_copy_log'] .= esc_html( 'Deleted the directory "' . be_mu_strip_before_substring( $folder, 'wp-content' ) . '". Parts done: '
                    . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done ) . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete the directory "'
                    . esc_html( be_mu_strip_before_substring( $folder, 'wp-content' ) ) . '". Parts done: '
                    . esc_html( $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done ) . '</b><br />';
            }

            // After every folder deletion we check the time passed and if the time limit is reached we will stop and continue in the next request
            if ( ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
                $limit_reached = 1;
                break;
            }
        }

        /**
         * If the time limit was reached we add the number of folders we deleted in this request to the number we did in the previous request
         * so we know from where to start the next time. We also save the arrays with file and folder paths to the databse.
         */
        if ( 1 === $limit_reached ) {
            $next_stage_parts_done += $done;
            be_mu_copy_sites_save_arrays_to_database( $task_id, 'delete' );

        // If a time limit was not reached, stage 3 is done and 0 parts from the next stage are done
        } else {
            $stages_done = 3;
            $next_stage_parts_done = 0;

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= 'Stage 3 is done at ' . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . ')<br />';

            do_action( 'beyond-multisite-copy-site-for-seconds-stage-done', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
                $task_id, $continuing_task, microtime( true ), $stages_done );
        }
    }

    /**
     * If the time limit for the request has been reached, we store the global arrays with files and foldes to the database and set the $limit_reached
     * variable to 1, which will skip all next stages below for this request.
     */
    if ( 0 === $limit_reached && ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
        $limit_reached = 1;
    }

    /*
     * This is the option for not copying media files from the template site from the module settings.
     * You can filter it as well. Possible values are:
     * 'on' - Do not media files
     * 'off' - Copy media files
     */
    $dont_copy_media = apply_filters( 'beyond-multisite-filter-copy-template-no-media', be_mu_get_setting( 'be-mu-copy-template-no-media' ),
        $copy_from_site_id, $copy_to_site_id, $seconds_to_work, $task_id, $continuing_task, microtime( true ), $stages_done );


    // Begin Stage 4: Finding files and folders to copy
    if ( $stages_done < 4 && 0 === $limit_reached ) {

        // Add data to the log variable
        $GLOBALS['be_mu_copy_log'] .= '<a name="stage4"></a><br /><b>Started stage 4</b> (finding files and folders to copy) at '
            . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . ')<br />';

        if ( $dont_copy_media === 'on' ) {
            $GLOBALS['be_mu_copy_log'] .= 'Skipping stage 4 due to settings.<br />';
        } else {

            // The path to the uploads folder of the site to copy from
            $upload_dir_from = be_mu_get_site_upload_folder( $copy_from_site_id );

            $GLOBALS['be_mu_copy_sites_copy_files'] = $GLOBALS['be_mu_copy_sites_copy_folders'] = Array();

            // We get the files and folders to copy and put then into the global arrays
            be_mu_copy_sites_get_files_and_folders( $upload_dir_from, 'copy', '' );

            // If the peepso plugin is active we need to copy some other files as well
            if ( be_mu_copy_sites_is_peepso_active( $copy_from_site_id ) ) {
                $from_peepso_dir = be_mu_copy_sites_get_peepso_directory_if_exists( $copy_from_site_id );
                if ( false !== $from_peepso_dir ) {
                    be_mu_copy_sites_get_files_and_folders( $from_peepso_dir, 'copy', '' );
                }
            }

        }

        // Stage 4 is done
        $stages_done = 4;

        // Add data to the log variable
        $GLOBALS['be_mu_copy_log'] .= 'Stage 4 is done at ' . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . ')<br />';

        do_action( 'beyond-multisite-copy-site-for-seconds-stage-done', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
            $task_id, $continuing_task, microtime( true ), $stages_done );
    }

    /**
     * If the time limit for the request has been reached, we store the global arrays with files and foldes to the database and set the $limit_reached
     * variable to 1, which will skip all next stages below for this request.
     */
    if ( 0 === $limit_reached && ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
        $limit_reached = 1;
        be_mu_copy_sites_save_arrays_to_database( $task_id, 'copy' );
    }

    // Begin Stage 5: Copying folders
    if ( $stages_done < 5 && 0 === $limit_reached ) {

        // Add data to the log variable if this is the first time we start this stage for this task
        if ( 0 === $next_stage_parts_done ) {
            $GLOBALS['be_mu_copy_log'] .= '<a name="stage5"></a><br /><b>Started stage 5</b> (copying folders) at '
                . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . ')<br />';
        }

        if ( $dont_copy_media === 'on' ) {
            $GLOBALS['be_mu_copy_log'] .= 'Skipping stage 5 due to settings.<br />';
        } else {

            // The path to the uploads folder of the site to copy from
            $upload_dir_from = be_mu_get_site_upload_folder( $copy_from_site_id );

            // The path to the uploads folder of the site to paste into
            $upload_dir_to = be_mu_get_site_upload_folder( $copy_to_site_id );

            // If this peepso plugin is active we need to copy some other files as well
            $peepso_active = 'no';
            if ( be_mu_copy_sites_is_peepso_active( $copy_from_site_id ) ) {
                $from_peepso_dir = be_mu_copy_sites_get_peepso_directory_if_exists( $copy_from_site_id );
                $to_peepso_dir = str_replace( 'peepso-' . $copy_from_site_id, 'peepso-' . $copy_to_site_id, $from_peepso_dir );
                if ( false !== $from_peepso_dir && false !== $to_peepso_dir && $to_peepso_dir !== $from_peepso_dir ) {
                    $peepso_active = 'yes';
                }
            }

            // How many folders we have skipped so far (because they are done from the previous request)
            $skipped = 0;

            // How many folders we have deleted so far
            $done = 0;

            // We go through all folders of the uploads folder of the site to copy from
            foreach ( $GLOBALS['be_mu_copy_sites_copy_folders'] as $folder ) {

                // If we have already copied some of the folders in the previous request, we will skip the same amount
                if ( $next_stage_parts_done > 0 && $skipped < $next_stage_parts_done ) {
                    $skipped++;
                    continue;
                }

                // This is the path of the new folder we will create (for the peepso plugin it is different)
                if ( 'yes' === $peepso_active && strpos( $folder, 'peepso-' . $copy_from_site_id ) !== false ) {
                    $new_folder = be_mu_replace_first( $from_peepso_dir, $to_peepso_dir, $folder );
                } else {
                    $new_folder = be_mu_replace_first( $upload_dir_from, $upload_dir_to, $folder );
                }

                // If the new folder does not exist, we create the current new folder and increase the counter for the number of folders copied
                if ( ! file_exists( $new_folder ) ) {
                    $status = mkdir( $new_folder, 0755, true );
                    $done++;

                    // Add data to the log variable
                    if ( $status ) {
                        $GLOBALS['be_mu_copy_log'] .= esc_html( 'Created the directory "'
                            . be_mu_strip_before_substring( $new_folder, 'wp-content' ) . '". Parts done: '
                            . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done ) . '<br />';
                    } else {
                        $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not create the directory "'
                            . esc_html( be_mu_strip_before_substring( $new_folder, 'wp-content' ) ) . '". Parts done: '
                            . esc_html( $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done ) . '</b><br />';
                    }

                // If the new folder exists we increase the counter for the number of folders copied
                } else {
                    $done++;

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= esc_html( 'The directory "' . $new_folder . '" already exists. Parts done: '
                        . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done ) . '<br />';
                }

                // After every folder creation we check the time passed and if the time limit is reached we will stop and continue in the next request
                if ( ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
                    $limit_reached = 1;
                    break;
                }
            }

        }

        /**
         * If the time limit was reached we add the number of folders we created in this request to the number we did in the previous request
         * so we know from where to start the next time. We also save the arrays with file and folder paths to the databse.
         */
        if ( 1 === $limit_reached ) {
            $next_stage_parts_done += $done;
            be_mu_copy_sites_save_arrays_to_database( $task_id, 'copy' );

        // If a time limit was not reached, stage 5 is done and 0 parts from the next stage are done
        } else {
            $stages_done = 5;
            $next_stage_parts_done = 0;

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= 'Stage 5 is done at ' . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . ')<br />';

            do_action( 'beyond-multisite-copy-site-for-seconds-stage-done', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
                $task_id, $continuing_task, microtime( true ), $stages_done );
        }
    }

    /**
     * If the time limit for the request has been reached, we store the global arrays with files and foldes to the database and set the $limit_reached
     * variable to 1, which will skip all next stages below for this request.
     */
    if ( 0 === $limit_reached && ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
        $limit_reached = 1;
        be_mu_copy_sites_save_arrays_to_database( $task_id, 'copy' );
    }

    // Begin Stage 6: Copying files
    if ( $stages_done < 6 && 0 === $limit_reached ) {

        // Add data to the log variable if this is the first time we start this stage for this task
        if ( 0 === $next_stage_parts_done ) {
            $GLOBALS['be_mu_copy_log'] .= '<a name="stage6"></a><br /><b>Started stage 6</b> (copying files) at '
                . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . ')<br />';
        }

        if ( $dont_copy_media === 'on' ) {
            $GLOBALS['be_mu_copy_log'] .= 'Skipping stage 6 due to settings.<br />';
        } else {

            // The path to the uploads folder of the site to copy from
            $upload_dir_from = be_mu_get_site_upload_folder( $copy_from_site_id );

            // The path to the uploads folder of the site to paste into
            $upload_dir_to = be_mu_get_site_upload_folder( $copy_to_site_id );

            // If this peepso plugin is active we need to copy some other files as well
            $peepso_active = 'no';
            if ( be_mu_copy_sites_is_peepso_active( $copy_from_site_id ) ) {
                $from_peepso_dir = be_mu_copy_sites_get_peepso_directory_if_exists( $copy_from_site_id );
                $to_peepso_dir = str_replace( 'peepso-' . $copy_from_site_id, 'peepso-' . $copy_to_site_id, $from_peepso_dir );
                if ( false !== $from_peepso_dir && false !== $to_peepso_dir && $to_peepso_dir !== $from_peepso_dir ) {
                    $peepso_active = 'yes';
                }
            }

            // How many files we have skipped so far (because they are done from the previous request)
            $skipped = 0;

            // How many files we have copied so far
            $done = 0;

            // We go through all files of the uploads folder of the site to copy from
            foreach ( $GLOBALS['be_mu_copy_sites_copy_files'] as $file ) {

                // If we have already copied some of the files in the previous request, we will skip the same amount
                if ( $next_stage_parts_done > 0 && $skipped < $next_stage_parts_done ) {
                    $skipped++;
                    continue;
                }

                // This is the path of the new file we will create (for the peepso plugin it is different)
                if ( 'yes' === $peepso_active && strpos( $file, 'peepso-' . $copy_from_site_id ) !== false ) {
                    $new_file = be_mu_replace_first( $from_peepso_dir, $to_peepso_dir, $file );
                } else {
                    $new_file = be_mu_replace_first( $upload_dir_from, $upload_dir_to, $file );
                }

                // We copy the current file and increase the counter for the number of files copied
                $status = copy( $file, $new_file );

                $done++;

                // Add data to the log variable
                if ( $status ) {
                    $GLOBALS['be_mu_copy_log'] .= esc_html( 'Copied the file "' . be_mu_strip_before_substring( $file, 'wp-content' ) . '" to "'
                        . be_mu_strip_before_substring( $new_file, 'wp-content' ) . '". Parts done: '
                        . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done ) . '<br />';
                } else {
                    $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not copy the file "'
                        . esc_html( be_mu_strip_before_substring( $file, 'wp-content' ) . '" to "'
                        . be_mu_strip_before_substring( $new_file, 'wp-content' ) . '". Parts done: ' . $next_stage_parts_done
                        . '. Skipped: ' . $skipped . '. Done: ' . $done )
                        . '</b><br />';
                }

                // After every file copying we check the time passed and if the time limit is reached we will stop and continue in the next request
                if ( ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
                    $limit_reached = 1;
                    break;
                }
            }

        }

        /**
         * If the time limit was reached we add the number of files we copied in this request to the number we did in the previous request
         * so we know from where to start the next time. We also save the arrays with file and folder paths to the databse.
         */
        if ( 1 === $limit_reached ) {
            $next_stage_parts_done += $done;
            be_mu_copy_sites_save_arrays_to_database( $task_id, 'copy' );

        // If a time limit was not reached, stage 6 is done and 0 parts from the next stage are done
        } else {
            $stages_done = 6;
            $next_stage_parts_done = 0;

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= 'Stage 6 is done at ' . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . ')<br />';

            do_action( 'beyond-multisite-copy-site-for-seconds-stage-done', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
                $task_id, $continuing_task, microtime( true ), $stages_done );
        }
    }

    // If the time limit for the request has been reached, we set the $limit_reached variable to 1, which will skip all next stages below for this request.
    if ( 0 === $limit_reached && ( microtime( true ) - $time_start ) > $GLOBALS['be_mu_copy_limit_time'] ) {
        $limit_reached = 1;
    }

    /*
     * Begin Stage 7: Deleting database tables
     * Stages 7 8 and 9 have to be done without interrupting, since we are continuing the process when the the actual site is visited, so it needs to work
     */
    if ( $stages_done < 7 && 0 === $limit_reached ) {

        // Add data to the log variable
        $GLOBALS['be_mu_copy_log'] .= '<a name="stage7"></a><br /><b>Started stage 7</b> (deleting database tables) at '
            . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . ')<br />';

        // We delete the options where we temporarily storred a list of file and folder paths
        be_mu_delete_setting( 'be_mu_copy_sites_delete_files_array_' . $task_id );
        be_mu_delete_setting( 'be_mu_copy_sites_delete_folders_array_' . $task_id );
        be_mu_delete_setting( 'be_mu_copy_sites_copy_files_array_' . $task_id );
        be_mu_delete_setting( 'be_mu_copy_sites_copy_folders_array_' . $task_id );

        // The database table prefix for the site we will paste into, and therefor almost completely delete first
        $copy_to_site_prefix = $wpdb->get_blog_prefix( $copy_to_site_id );

        // The database table prefix for the site we will copy from
        $copy_from_site_prefix = $wpdb->get_blog_prefix( $copy_from_site_id );

        // The database table prefix for the main site
        $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

        // If for any reason the prefix is empty we will stop and show an error
        if ( empty( $copy_to_site_prefix ) ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Fatal Error: Something went very wrong. The prefix for the site to paste into is empty. '
                . 'We had to abort in the middle of copying. Since we did not finished, the site that was going to be replaced is probably now damaged. '
                . 'It should be deleted.<b><br>';
            be_mu_copy_sites_add_to_log( $task_id );
            delete_site_option( 'be-mu-copy-site-on-creation-continue-for-' . intval( $copy_to_site_id ) );

            do_action( 'beyond-multisite-end-copy-site-for-seconds', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
                $task_id, $continuing_task, microtime( true ), $stages_done, 'fatal-error' );

            return Array( 'status' => 'fatal-error', 'task-id' => $task_id );
        }

        // If for any reason both prefixes are the same we will stop and show an error
        if ( $copy_to_site_prefix == $copy_from_site_prefix ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Fatal Error: Something went very wrong. Both sites have the same prefix. '
                . 'We had to abort in the middle of copying. Since we did not finished, the site that was going to be replaced is probably now damaged. '
                . 'It should be deleted.<b><br>';
            be_mu_copy_sites_add_to_log( $task_id );
            delete_site_option( 'be-mu-copy-site-on-creation-continue-for-' . intval( $copy_to_site_id ) );

            do_action( 'beyond-multisite-end-copy-site-for-seconds', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
                $task_id, $continuing_task, microtime( true ), $stages_done, 'fatal-error' );

            return Array( 'status' => 'fatal-error', 'task-id' => $task_id );
        }

        // If for any reason the prefix of the site to paste into is the same as the one of the main site we will stop and show an error
        if ( $copy_to_site_prefix == $main_blog_prefix ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Fatal Error: Something went very wrong. The site to paste into has the same prefix as the main site. '
                . 'We had to abort in the middle of copying. Since we did not finished, the site that was going to be replaced is probably now damaged. '
                . 'It should be deleted.<b><br>';
            be_mu_copy_sites_add_to_log( $task_id );
            delete_site_option( 'be-mu-copy-site-on-creation-continue-for-' . intval( $copy_to_site_id ) );

            do_action( 'beyond-multisite-end-copy-site-for-seconds', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
                $task_id, $continuing_task, microtime( true ), $stages_done, 'fatal-error' );

            return Array( 'status' => 'fatal-error', 'task-id' => $task_id );
        }

        // If for any reason the prefix of the site to paste into does not end in a number and then an underscore, we will stop and show an error
        if ( substr( $copy_to_site_prefix, -1 ) !== '_' || ! is_numeric( substr( substr_replace( $copy_to_site_prefix, "", -1), -1 ) ) ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Fatal Error: Something went very wrong. The prefix of the site to paste into is invalid. '
                . 'We had to abort in the middle of copying. Since we did not finished, the site that was going to be replaced is probably now damaged. '
                . 'It should be deleted.<b><br>';
            be_mu_copy_sites_add_to_log( $task_id );
            delete_site_option( 'be-mu-copy-site-on-creation-continue-for-' . intval( $copy_to_site_id ) );

            do_action( 'beyond-multisite-end-copy-site-for-seconds', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
                $task_id, $continuing_task, microtime( true ), $stages_done, 'fatal-error' );

            return Array( 'status' => 'fatal-error', 'task-id' => $task_id );
        }

        // We save in the database some data about to paste into so we can access it after we delete the site
        $settings = Array(
            'be_mu_copy_sites_copy_to_site_url_' . $task_id => get_blog_option( $copy_to_site_id, 'siteurl' ),
            'be_mu_copy_sites_copy_to_home_' . $task_id => get_blog_option( $copy_to_site_id, 'home' ),
            'be_mu_copy_sites_copy_to_blogname_' . $task_id => get_blog_option( $copy_to_site_id, 'blogname' ),
            'be_mu_copy_sites_copy_to_admin_email_' . $task_id => get_blog_option( $copy_to_site_id, 'admin_email' ),
            'be_mu_copy_sites_copy_to_upload_path_' . $task_id => get_blog_option( $copy_to_site_id, 'upload_path' ),
            'be_mu_copy_sites_copy_to_upload_url_path_' . $task_id => get_blog_option( $copy_to_site_id, 'upload_url_path' ),
            'be_mu_copy_sites_copy_to_site_prefix_' . $task_id => $copy_to_site_prefix,
        );
        foreach ( $settings as $key => $value ) {
            $status = be_mu_set_or_make_setting( $key, $value );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Added or updated the site option named "' . esc_html( $key ) . '" with value "' . esc_html( $value ) . '"<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not add or update the site option named "' . esc_html( $key ) . '" with value "'
                    . esc_html( $value ) . '"</b><br />';
            }
        }

        // We get all database table names that start with the site prefix of the site to paste into
        $query = "SELECT TABLE_NAME, TABLE_SCHEMA FROM information_schema.tables WHERE "
            . "TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA IN ( " . be_mu_get_database_names_string_for_mysql_in() . " ) "
            . "AND LOCATE( '" . $copy_to_site_prefix . "', TABLE_NAME ) = 1";
        $tables_to_multi_array = $wpdb->get_results( $query, ARRAY_A );

        // If there are any database tables found we go through them one by one
        if ( ! empty( $tables_to_multi_array ) ) {

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= 'We found ' . count( $tables_to_multi_array ) . ' database tables that belong to the site to paste into. Query: "'
                . $query . '".<br />';

            foreach ( $tables_to_multi_array as $table_to ) {

                // Make sure one more time, that the table name really starts with the site prefix, and then we delete the table.
                if ( substr( $table_to['TABLE_NAME'], 0, strlen( $copy_to_site_prefix ) ) === $copy_to_site_prefix ) {

                    // We save the database name for later (this is needed to support the multi db plugin)
                    if ( ! be_mu_get_setting( 'be_mu_copy_sites_copy_to_db_name_' . $task_id ) ) {
                        $status = be_mu_set_or_make_setting( 'be_mu_copy_sites_copy_to_db_name_' . $task_id, $table_to['TABLE_SCHEMA'] );

                        // Add data to the log variable
                        if ( false !== $status ) {
                            $GLOBALS['be_mu_copy_log'] .= 'Added or updated the site option named "' . 'be_mu_copy_sites_copy_to_db_name_'
                                . esc_html( $task_id ) . '" with value "' . esc_html( $table_to['TABLE_SCHEMA'] ) . '"<br />';
                        } else {
                            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not add or update the site option named "'
                                . 'be_mu_copy_sites_copy_to_db_name_' . esc_html( $task_id ) . '" with value "'
                                . esc_html( $table_to['TABLE_SCHEMA'] ) . '"</b><br />';
                        }
                    }

                    // Delete the table
                    $query = 'DROP TABLE ' . $table_to['TABLE_NAME'];
                    $status = $wpdb->query( $query );

                    // Add data to the log variable
                    if ( false !== $status ) {
                        $GLOBALS['be_mu_copy_log'] .= 'Deleted the database table "' . esc_html( $table_to['TABLE_NAME'] )
                            . '". Query: "' . esc_html( $query ) . '".<br />';
                    } else {
                        $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete the database table "' . esc_html( $table_to['TABLE_NAME'] )
                            . '". Query: "' . esc_html( $query ) . '".</b><br />';
                    }
                }
            }
        } else {

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: No database tables found that belong to the site to paste into or error in the query. Query: "'
                . esc_html( $query ) . '".</b><br />';
        }

        // Stage 7 is done
        $stages_done = 7;

        // Add data to the log variable
        $GLOBALS['be_mu_copy_log'] .= 'Stage 7 is done at ' . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . ')<br />';

        do_action( 'beyond-multisite-copy-site-for-seconds-stage-done', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
            $task_id, $continuing_task, microtime( true ), $stages_done );
    }

    // Begin Stage 8: Copying database tables
    if ( $stages_done < 8 && 0 === $limit_reached ) {

        // Add data to the log variable if this is the first time we start this stage for this task
        if ( 0 === $next_stage_parts_done ) {
            $GLOBALS['be_mu_copy_log'] .= '<a name="stage8"></a><br /><b>Started stage 8</b> (copying database tables) at '
                . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . ')<br />';
        }

        // The database table prefix for the site we will copy from
        $copy_from_site_prefix = $wpdb->get_blog_prefix( $copy_from_site_id );

        // The database table prefix for the site we will paste into
        $copy_to_site_prefix = be_mu_get_setting( 'be_mu_copy_sites_copy_to_site_prefix_' . $task_id );

        // The database name for the site we will paste into (to support the multi db plugin)
        $copy_to_site_db_name = be_mu_get_setting( 'be_mu_copy_sites_copy_to_db_name_' . $task_id );

        // If any of the prefixes or the database name are empty for some reason, we stop everything and show an error
        if ( empty( $copy_from_site_prefix ) ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Fatal Error: Something went very wrong. The prefix for the site to copy from is empty. '
                . 'We had to abort in the middle of copying. Since we did not finished, the site that was going to be replaced is probably now damaged. '
                . 'It should be deleted.<b><br>';
            be_mu_copy_sites_add_to_log( $task_id );
            delete_site_option( 'be-mu-copy-site-on-creation-continue-for-' . intval( $copy_to_site_id ) );

            do_action( 'beyond-multisite-end-copy-site-for-seconds', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
                $task_id, $continuing_task, microtime( true ), $stages_done, 'fatal-error' );

            return Array( 'status' => 'fatal-error', 'task-id' => $task_id );
        }
        if ( empty( $copy_to_site_prefix ) ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Fatal Error: Something went very wrong. The prefix for the site to paste into is empty. '
                . 'We had to abort in the middle of copying. Since we did not finished, the site that was going to be replaced is probably now damaged. '
                . 'It should be deleted.<b><br>';
            be_mu_copy_sites_add_to_log( $task_id );
            delete_site_option( 'be-mu-copy-site-on-creation-continue-for-' . intval( $copy_to_site_id ) );

            do_action( 'beyond-multisite-end-copy-site-for-seconds', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
                $task_id, $continuing_task, microtime( true ), $stages_done, 'fatal-error' );

            return Array( 'status' => 'fatal-error', 'task-id' => $task_id );
        }
        if ( empty( $copy_to_site_db_name ) ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Fatal Error: Something went very wrong. The database name for the site to paste into is empty. '
                . 'We had to abort in the middle of copying. Since we did not finished, the site that was going to be replaced is probably now damaged. '
                . 'It should be deleted.<b><br>';
            be_mu_copy_sites_add_to_log( $task_id );
            delete_site_option( 'be-mu-copy-site-on-creation-continue-for-' . intval( $copy_to_site_id ) );

            do_action( 'beyond-multisite-end-copy-site-for-seconds', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
                $task_id, $continuing_task, microtime( true ), $stages_done, 'fatal-error' );

            return Array( 'status' => 'fatal-error', 'task-id' => $task_id );
        }

        // How many database tables we have skipped so far (because they are done from the previous request)
        $skipped = 0;

        // How many database tables we have copied so far
        $done = 0;

        // We get all database table names that start with the site prefix of the site to copy from
        $query = "SELECT TABLE_NAME, TABLE_SCHEMA FROM information_schema.tables WHERE "
            . "TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA IN ( " . be_mu_get_database_names_string_for_mysql_in() . " ) "
            . "AND LOCATE( '" . $copy_from_site_prefix . "', TABLE_NAME ) = 1";
        $tables_from_multi_array = $wpdb->get_results( $query, ARRAY_A );

        // If there are any database tables found we go through them one by one
        if ( ! empty( $tables_from_multi_array ) ) {

            $GLOBALS['be_mu_copy_log'] .= 'We found ' . count( $tables_from_multi_array ) . ' database tables that belong to the site to copy from. Query: "'
                . $query . '".<br />';

            foreach ( $tables_from_multi_array as $table_from ) {

                // Make sure one more time, that the table name really starts with the site prefix, and then we copy the table
                if ( substr( $table_from['TABLE_NAME'], 0, strlen( $copy_from_site_prefix ) ) === $copy_from_site_prefix ) {

                    // If we have already copied some of the database tables in the previous request, we will skip the same amount
                    if ( $next_stage_parts_done > 0 && $skipped < $next_stage_parts_done ) {
                        $skipped++;
                        continue;
                    }

                    // The name of the new database table we will create
                    $new_table_to = be_mu_replace_first( $copy_from_site_prefix, $copy_to_site_prefix, $table_from['TABLE_NAME'] );

                    // Create the database table that will be a copy of the original
                    $query = 'CREATE TABLE `' . $copy_to_site_db_name . '`.`' . $new_table_to . '` LIKE `'
                        . $table_from['TABLE_SCHEMA'] . '`.`' . $table_from['TABLE_NAME'] . '`';
                    $status = $wpdb->query( $query );

                    // Add data to the log variable
                    if ( false !== $status ) {
                        $GLOBALS['be_mu_copy_log'] .= esc_html( 'Created the database table "' . $copy_to_site_db_name . '.' . $new_table_to . '". Query: "' . $query
                            . '". Parts done: ' . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done ) . '<br />';
                    } else {
                        $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not create the database table "'
                            . esc_html( $copy_to_site_db_name . '.' . $new_table_to . '". Query: "' . $query . '". Parts done: '
                            . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done ) . '</b><br />';
                    }

                    // We insert all the same data into the new table
                    $query = 'INSERT INTO `' . $copy_to_site_db_name . '`.`' . $new_table_to . '` SELECT * FROM `'
                        . $table_from['TABLE_SCHEMA'] . '`.`' . $table_from['TABLE_NAME'] . '`';
                    $status = $wpdb->query( $query );

                    // We increase the counter for the number of database tables that we have copied already
                    $done++;

                    // Add data to the log variable
                    if ( false !== $status ) {
                        $GLOBALS['be_mu_copy_log'] .= esc_html( 'Inserted the content of the database table "'
                            . $table_from['TABLE_SCHEMA'] . '.' . $table_from['TABLE_NAME']
                            . '" into the database table "' . $copy_to_site_db_name . '.' . $new_table_to . '". Query: "'
                            . $query . '". Rows inserted: ' . $status . '. Parts done: '
                            . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done ) . '<br />';
                    } else {
                        $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not insert the content of the database table "'
                            . esc_html( $table_from['TABLE_SCHEMA'] . '.' . $table_from['TABLE_NAME']
                            . '" into the database table "' . $copy_to_site_db_name . '.' . $new_table_to . '". Query: "' . $query . '". Parts done: '
                            . $next_stage_parts_done . '. Skipped: ' . $skipped . '. Done: ' . $done ) . '</b><br />';
                    }

                    /**
                     * Right away after creating the options table and inserting the data in it, we fix the upload path quickly because if
                     * for some reason the copy process stops at some point before we fix it, both sites will have the same upload folder.
                     * And this is a big problem since if the site to paste into is now deleted or pasted into again, the incorrect files
                     * will be deleted!
                     */
                    if ( $new_table_to == $copy_to_site_prefix . 'options' ) {

                        // The upload path option of the site we paste into
                        $copy_to_upload_path = be_mu_get_setting( 'be_mu_copy_sites_copy_to_upload_path_' . $task_id );

                        $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "options SET option_value = %s WHERE option_name = 'upload_path'",
                            $copy_to_upload_path );
                        $status = $wpdb->query( $query );

                        // Add data to the log variable
                        if ( false !== $status ) {
                            $GLOBALS['be_mu_copy_log'] .= 'Updated the upload path of the site to paste into. Query: "' . esc_html( $query )
                                . '". Rows updated: ' . $status . '<br />';
                        } else {
                            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the upload path of the site to paste into. Query: "'
                                . esc_html( $query ) . '".</b><br />';
                        }
                    }
                }
            }
        } else {

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: No database tables found that belong to the site to copy from or error in the query. Query: "'
                . esc_html( $query ) . '".</b><br />';
        }

        /**
         * If the time limit was reached we add the number of database tables we copied in this request to the number we did in the previous request
         * so we know from where to start the next time.
         */
        if ( 1 === $limit_reached ) {
            $next_stage_parts_done += $done;

        // If a time limit was not reached, stage 7 is done and 0 parts from the next stage are done
        } else {
            $stages_done = 8;
            $next_stage_parts_done = 0;

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= 'Stage 8 is done at ' . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . ')<br />';

            do_action( 'beyond-multisite-copy-site-for-seconds-stage-done', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
                $task_id, $continuing_task, microtime( true ), $stages_done );
        }
    }

    // Begin Stage 9: Fix database data
    if ( $stages_done < 9 && 0 === $limit_reached ) {

        // Add data to the log variable
        $GLOBALS['be_mu_copy_log'] .= '<a name="stage9"></a><br /><b>Started stage 9</b> (fixing database data) at '
            . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . ')<br />';

        // The database table prefix for the multisite
        $base_prefix = $wpdb->base_prefix;

        // The database table prefix for the site we will copy from
        $copy_from_site_prefix = $wpdb->get_blog_prefix( $copy_from_site_id );

        // The database table prefix for the site we will paste into
        $copy_to_site_prefix = be_mu_get_setting( 'be_mu_copy_sites_copy_to_site_prefix_' . $task_id );

        // The site URL of the site we paste into
        $copy_to_site_url = esc_url( be_mu_get_setting( 'be_mu_copy_sites_copy_to_site_url_' . $task_id ) );

        // The home option of the site we paste into
        $copy_to_home = be_mu_get_setting( 'be_mu_copy_sites_copy_to_home_' . $task_id );

        // The upload URL path option of the site we paste into
        $copy_to_upload_url_path = be_mu_get_setting( 'be_mu_copy_sites_copy_to_upload_url_path_' . $task_id );

        // The site URL of the site we copy from
        $copy_from_site_url = esc_url( get_blog_option( $copy_from_site_id, 'siteurl' ) );

        /*
         * This is the option for copying the title from the template site or not from the module settings.
         * You can filter it as well. Possible values are:
         * 'on' - Copies the title
         * 'off' - Does not copy the title
         */
        $copy_title = apply_filters( 'beyond-multisite-filter-copy-template-site-title', be_mu_get_setting( 'be-mu-copy-template-site-title' ),
            $copy_from_site_id, $copy_to_site_id, $seconds_to_work, $task_id, $continuing_task, microtime( true ), $stages_done );

        /*
         * This is the option for copying the admin email from the template site or not from the module settings.
         * You can filter it as well. Possible values are:
         * 'on' - Copies the admin email
         * 'off' - Does not copy the admin email
         */
        $copy_admin_email = apply_filters( 'beyond-multisite-filter-copy-template-site-email', be_mu_get_setting( 'be-mu-copy-template-site-email' ),
            $copy_from_site_id, $copy_to_site_id, $seconds_to_work, $task_id, $continuing_task, microtime( true ), $stages_done );

        // The title of the site we paste into
        if ( $copy_title !== 'on' ) {
            $copy_to_title = be_mu_get_setting( 'be_mu_copy_sites_copy_to_blogname_' . $task_id );
        }

        // The admin email of the site we paste into
        if ( $copy_admin_email !== 'on' ) {
            $copy_to_admin_email = be_mu_get_setting( 'be_mu_copy_sites_copy_to_admin_email_' . $task_id );
        }

        // If any of the prefixes are empty for some reason, we stop everything and show an error
        if ( empty( $copy_from_site_prefix ) ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Fatal Error: Something went very wrong. The prefix for the site to copy from is empty. '
                . 'We had to abort in the middle of copying. Since we did not finished, the site that was going to be replaced is probably now damaged. '
                . 'It should be deleted.<b><br>';
            be_mu_copy_sites_add_to_log( $task_id );
            delete_site_option( 'be-mu-copy-site-on-creation-continue-for-' . intval( $copy_to_site_id ) );

            do_action( 'beyond-multisite-end-copy-site-for-seconds', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
                $task_id, $continuing_task, microtime( true ), $stages_done, 'fatal-error' );

            return Array( 'status' => 'fatal-error', 'task-id' => $task_id );
        }
        if ( empty( $copy_to_site_prefix ) ) {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Fatal Error: Something went very wrong. The prefix for the site to paste into is empty. '
                . 'We had to abort in the middle of copying. Since we did not finished, the site that was going to be replaced is probably now damaged. '
                . 'It should be deleted.<b><br>';
            be_mu_copy_sites_add_to_log( $task_id );
            delete_site_option( 'be-mu-copy-site-on-creation-continue-for-' . intval( $copy_to_site_id ) );

            do_action( 'beyond-multisite-end-copy-site-for-seconds', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
                $task_id, $continuing_task, microtime( true ), $stages_done, 'fatal-error' );

            return Array( 'status' => 'fatal-error', 'task-id' => $task_id );
        }

        // Copy site meta data
        if ( function_exists( 'is_site_meta_supported' ) && is_site_meta_supported() ) {
            $inserted_site_meta = 0;
            $query = $wpdb->prepare( "DELETE FROM " . $base_prefix . "blogmeta WHERE blog_id = %d", $copy_to_site_id );
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'The site meta data for site ID ' . $copy_to_site_id . ' in the ' . $base_prefix . 'blogmeta database table'
                    . ' was deleted. Rows deleted: ' . esc_html( $status ) . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete site meta data for site ID ' . $copy_to_site_id . ' in the '
                    . $base_prefix . 'blogmeta database table.</b><br />';
            }

            $query = $wpdb->prepare( "SELECT meta_key, meta_value FROM " . $base_prefix . "blogmeta WHERE blog_id = %d", $copy_from_site_id );
            $results_multi_array = $wpdb->get_results( $query, ARRAY_A );
            if ( ! empty( $results_multi_array ) ) {
                foreach ( $results_multi_array as $results ) {
                    $status = $wpdb->insert(
                    	$base_prefix . 'blogmeta',
                    	array(
                    		'blog_id' => $copy_to_site_id,
                    		'meta_key' => $results['meta_key'],
                    		'meta_value' => $results['meta_value'],
                    	),
                    	array(
                    		'%d',
                    		'%s',
                    		'%s',
                    	)
                    );
                    if ( false !== $status ) {
                        $inserted_site_meta++;
                    }
                }
            }

            // Add data to the log variable
            if ( $inserted_site_meta > 0 ) {
                $GLOBALS['be_mu_copy_log'] .= 'Site meta data in the ' . $base_prefix . 'blogmeta database table was copied. '
                    . ' Rows inserted: ' . esc_html( $inserted_site_meta ) . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= 'There was no copied site meta data in the ' . $base_prefix . 'blogmeta database table.<br>';
            }
        } else {
            $GLOBALS['be_mu_copy_log'] .= 'Site meta data in the ' . $base_prefix . 'blogmeta database table is not supported.<br>';
        }

        /**
         * In the next section of code we update the site ULR, home, and upload url path options in the database tables we created as
         * a copy and put the correct values.
         */
        $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "options SET option_value = %s WHERE option_name = 'siteurl'", $copy_to_site_url );
        $status = $wpdb->query( $query );

        // Add data to the log variable
        if ( false !== $status ) {
            $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL of the site to paste into. Query: "' . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
        } else {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL of the site to paste into. Query: "' . esc_html( $query )
                . '".</b><br />';
        }

        $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "options SET option_value = %s WHERE option_name = 'home'" , $copy_to_home );
        $status = $wpdb->query( $query );

        // Add data to the log variable
        if ( false !== $status ) {
            $GLOBALS['be_mu_copy_log'] .= 'Updated the home address of the site to paste into. Query: "'
                . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
        } else {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the home address of the site to paste into. Query: "'
                . esc_html( $query ) . '".</b><br />';
        }

        $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "options SET option_value = %s WHERE option_name = 'upload_url_path'" ,
            $copy_to_upload_url_path );
        $status = $wpdb->query( $query );

        // Add data to the log variable
        if ( false !== $status ) {
            $GLOBALS['be_mu_copy_log'] .= 'Updated the upload URL path of the site to paste into. Query: "' . esc_html( $query )
                . '". Rows updated: ' . $status . '<br />';
        } else {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the upload URL path of the site to paste into. Query: "'
                . esc_html( $query ) . '".</b><br />';
        }

        if ( $copy_title !== 'on' ) {

            $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "options SET option_value = %s WHERE option_name = 'blogname'" ,
                $copy_to_title );
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Updated the title of the site to paste into. Query: "' . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the title of the site to paste into. Query: "'
                    . esc_html( $query ) . '".</b><br />';
            }

        }

        if ( $copy_admin_email !== 'on' ) {

            // Make sure it does now show a meesage for changed email address from some old change
            $wpdb->query( "DELETE FROM " . $copy_to_site_prefix . "options WHERE option_name = 'new_admin_email'" );

            $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "options SET option_value = %s WHERE option_name = 'admin_email'" ,
                $copy_to_admin_email );
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Updated the admin email of the site to paste into. Query: "' . esc_html( $query )
                    . '". Rows updated: ' . $status . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the admin email of the site to paste into. Query: "'
                    . esc_html( $query ) . '".</b><br />';
            }

        }

        // We update the prefix for the user roles to be for the site we pasted into, and not the one we copied from
        $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "options SET option_name = %s WHERE option_name = %s",
            $copy_to_site_prefix . 'user_roles', $copy_from_site_prefix . 'user_roles' );
        $status = $wpdb->query( $query );

        // Add data to the log variable
        if ( false !== $status ) {
            $GLOBALS['be_mu_copy_log'] .= 'Updated the user roles of the site to paste into. Query: "' . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
        } else {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the user roles of the site to paste into. Query: "'
                . esc_html( $query ) . '".</b><br />';
        }

        /*
         * This is the option for copying users from the template site or not from the module settings.
         * You can filter it as well. Possible values are:
         * 'skip' - Do not copy the users from the template site
         * 'add' - Copy these users from the template site, that do not exist in the new site
         * 'replace' - Replace the existing users in the new site with all the users from the template site
         */
        $copy_users = apply_filters( 'beyond-multisite-filter-copy-template-site-users', be_mu_get_setting( 'be-mu-copy-template-site-users' ),
            $copy_from_site_id, $copy_to_site_id, $seconds_to_work, $task_id, $continuing_task, microtime( true ), $stages_done );


        if ( 'replace' === $copy_users || 'add' === $copy_users ) {

            $skip_users = Array();

            /**
             * We get all rows from the usermeta database table from the main site, which meta_key starts with the site prefix of the site we pasted into,
             * so we can delete them (because this is old data, and we already deleted the site)
             */
            $query = "SELECT * FROM " . $base_prefix . "usermeta WHERE LOCATE( '" . $copy_to_site_prefix . "', meta_key ) = 1";
            $results_multi_array = $wpdb->get_results( $query, ARRAY_A );

            if ( ! empty( $results_multi_array ) ) {

                if ( 'add' === $copy_users ) {

                    foreach ( $results_multi_array as $results ) {
                        if ( ! in_array( $results['user_id'], $skip_users ) ) {
                            $skip_users[] = intval( $results['user_id'] );
                        }
                    }

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We found ' . count( $skip_users )
                        . ' users to skip when copying, because they already exist in the site to paste into.<br />';
                }

                if ( 'replace' === $copy_users ) {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We found ' . count( $results_multi_array )
                        . ' rows of user meta data to delete, that belongs to the site to paste into. Query: "' . esc_html( $query ) . '".<br />';

                    foreach ( $results_multi_array as $results ) {

                        // Make sure one more time, that the meta_key really starts with the site prefix, and then we delete the rows
                        if ( substr( $results['meta_key'], 0, strlen( $copy_to_site_prefix ) ) === $copy_to_site_prefix ) {
                            $query = $wpdb->prepare( "DELETE FROM " . $base_prefix
                                . "usermeta WHERE meta_key = %s AND user_id = %d", $results['meta_key'], $results['user_id'] );
                            $status = $wpdb->query( $query );

                            // Add data to the log variable
                            if ( false !== $status ) {
                                $GLOBALS['be_mu_copy_log'] .= 'Deleted user meta data for meta key "' . esc_html( $results['meta_key'] ) . '" and user id "'
                                    . esc_html( $results['user_id'] ) . '". Query: "' . esc_html( $query ) . '". Rows deleted: ' . $status . '<br />';
                            } else {
                                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete user meta data for meta key "'
                                    . esc_html( $results['meta_key'] ) . '" and user id "' . esc_html( $results['user_id'] ) . '". Query: "'
                                    . esc_html( $query ) . '".</b><br />';
                            }
                        }
                    }

                }

            } else {

                if ( 'replace' === $copy_users ) {
                    $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: We did not find any user meta data to delete, that belongs to the site to paste into, '
                        . 'or there could be an error in the query. Query: "' . esc_html( $query ) . '".</b><br />';
                }

                if ( 'add' === $copy_users ) {
                    $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: We did not find any users to skip when copying, that belongs to the site to paste into, '
                        . 'or there could be an error in the query. Query: "' . esc_html( $query ) . '".</b><br />';
                }

            }

            /**
             * We get all rows from the usermeta database table from the main site, which meta_key starts with the site prefix of the site we copy from,
             * so we can duplicate them for the site we paste into
             */
            $query = "SELECT * FROM " . $base_prefix . "usermeta WHERE LOCATE( '" . $copy_from_site_prefix . "', meta_key ) = 1";
            $results_multi_array = $wpdb->get_results( $query, ARRAY_A );

            if ( ! empty( $results_multi_array ) ) {

                if ( 'add' === $copy_users && count( $skip_users ) > 0 ) {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We found ' . count( $results_multi_array )
                        . ' rows of user meta data to copy, but some of them will be skipped. Query: "' . esc_html( $query ) . '".<br />';
                } else {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We found ' . count( $results_multi_array )
                        . ' rows of user meta data to copy. Query: "' . esc_html( $query ) . '".<br />';
                }

                foreach ( $results_multi_array as $results ) {

                    // Make sure one more time, that the meta_key really starts with the blog prefix, and then we insert the rows
                    if ( substr( $results['meta_key'], 0, strlen( $copy_from_site_prefix ) ) === $copy_from_site_prefix ) {

                        if ( 'add' === $copy_users && in_array( intval( $results['user_id'] ), $skip_users ) ) {
                            $GLOBALS['be_mu_copy_log'] .= 'We skipped user meta data for user id "' . esc_html( $results['user_id'] ) . '<br />';
                        } else {

                            // We insert the same rows but with meta_key starting with the new site prefix
                            $status = $wpdb->insert(
                            	$base_prefix . 'usermeta',
                            	array(
                            		'user_id' => $results['user_id'],
                            		'meta_key' => be_mu_replace_first( $copy_from_site_prefix, $copy_to_site_prefix, $results['meta_key'] ),
                            		'meta_value' => $results['meta_value'],
                            	),
                            	array(
                            		'%d',
                            		'%s',
                            		'%s',
                            	)
                            );

                            // Add data to the log variable
                            if ( false !== $status ) {
                                $GLOBALS['be_mu_copy_log'] .= 'Copied user meta data for user id "' . esc_html( $results['user_id'] )
                                    . '" from meta_key "' . esc_html( $results['meta_key'] ) . '" to meta_key "'
                                    . esc_html( be_mu_replace_first( $copy_from_site_prefix, $copy_to_site_prefix, $results['meta_key'] ) )
                                    . '" in database table "' . esc_html( $base_prefix ) . 'usermeta". Rows inserted: ' . $status . '<br />';
                            } else {
                                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not copy user meta data for user id "' . esc_html( $results['user_id'] )
                                    . '" from meta_key "' . esc_html( $results['meta_key'] ) . '" to meta_key "'
                                    . esc_html( be_mu_replace_first( $copy_from_site_prefix, $copy_to_site_prefix, $results['meta_key'] ) )
                                    . '" in database table "' . esc_html( $base_prefix ) . 'usermeta"</b><br />';
                            }
                        }
                    }
                }
            } else {

                // Add data to the log variable
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: We did not find any user meta data to copy or there could be an error in the query. Query: "'
                    . esc_html( $query ) . '".</b><br />';
            }
        }

        /**
         * This array will hold all the old site URLs from the site we copy from, that we need to find and replace in the new site we paste into.
         * They might be more than one, since we will consider any mapped domains too.
         * All site URLs will be replaced with the site URL of the site to paste into.
         */
        $from_site_urls = Array( $copy_from_site_url );

        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        /**
         * If the WordPress MU Domain Mapping plugin (https://wordpress.org/plugins/wordpress-mu-domain-mapping/) is active and there are any domains
         * mapped to the site we copy from, we add the URLs to our array so we can replace them too.
         */
        if ( is_plugin_active( 'wordpress-mu-domain-mapping/domain_mapping.php' ) ) {
            if ( is_ssl() ) {
                $protocol = 'https://';
            } else {
                $protocol = 'http://';
            }
            $domains = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $wpdb->dmtable . " WHERE blog_id = %d", $copy_from_site_id ), ARRAY_A );
            if ( is_array( $domains ) && ! empty( $domains ) ) {
                foreach ( $domains as $details ) {
                    if ( array_key_exists( 'domain', $details ) && ! empty( $details['domain'] ) ) {
                        $url = $protocol . $details['domain'];
                        if ( array_key_exists( 'path', $details ) && ! empty( $details['path'] ) ) {
                            $url .= $details['path'];
                        }
                        if ( filter_var( $url, FILTER_VALIDATE_URL ) !== FALSE && ! in_array( $url, $from_site_urls ) ) {
                            $from_site_urls[] = esc_url( $url );
                        }
                    }
                }
            }
        }

        /**
         * If the Domain Mapping plugin (https://premium.wpmudev.org/project/domain-mapping/) is active and there are any domains
         * mapped to the site we copy from, we add the URLs to our array so we can replace them too.
         */
        if ( is_plugin_active( 'domain-mapping/domain-mapping.php' ) ) {
            $domains = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $base_prefix
                . "domain_mapping WHERE blog_id = %d", $copy_from_site_id ), ARRAY_A );
            if ( is_array( $domains ) && ! empty( $domains ) ) {
                foreach ( $domains as $details ) {
                    if ( array_key_exists( 'domain', $details ) && ! empty( $details['domain'] ) && array_key_exists( 'scheme', $details ) ) {
                        if ( intval( $details['scheme'] ) == 0 ) {
                            $protocol = 'http://';
                        } else {
                            $protocol = 'https://';
                        }
                        $url = $protocol . $details['domain'];
                        if ( filter_var( $url, FILTER_VALIDATE_URL ) !== FALSE && ! in_array( $url, $from_site_urls ) ) {
                            $from_site_urls[] = esc_url( $url );
                        }
                    }
                }
            }
        }

        // We go through different ways an url can be stored in the database
        for ( $loop = 1; $loop < 7; $loop++ ) {

            /**
             * This is the site URL of the site to paste into, it is only one, the one from the options. It needs to be global because we will use it later
             * in the functions that replace in serialized data.
             */
            if ( 1 === $loop ) {
                $GLOBALS['be_mu_copy_site_replace_with_url'] = $copy_to_site_url;
            } elseif ( 2 === $loop ) {
                $GLOBALS['be_mu_copy_site_replace_with_url'] = urlencode( $copy_to_site_url );
            } elseif ( 3 === $loop ) {
                $GLOBALS['be_mu_copy_site_replace_with_url'] = be_mu_copy_sites_add_slashes_to_slashes( $copy_to_site_url );
            } elseif ( 4 === $loop ) {
                $GLOBALS['be_mu_copy_site_replace_with_url'] = $copy_to_site_url;
            } elseif ( 5 === $loop ) {
                $GLOBALS['be_mu_copy_site_replace_with_url'] = urlencode( $copy_to_site_url );
            } else {
                $GLOBALS['be_mu_copy_site_replace_with_url'] = be_mu_copy_sites_add_slashes_to_slashes( $copy_to_site_url );
            }

            // We go through all the URLs that we need to replace and replace them with the new URL everywhere in the new site we pasted into
            foreach ( $from_site_urls as $current_from_site_url ) {

                // If the URL we will replace with has a slash and the other ones don't, we add one (and the opposite too)
                if ( substr( $GLOBALS['be_mu_copy_site_replace_with_url'], -1 ) == '/' && substr( $current_from_site_url, -1 ) != '/' ) {
                    $current_from_site_url .= '/';
                } elseif ( substr( $GLOBALS['be_mu_copy_site_replace_with_url'], -1 ) != '/' && substr( $current_from_site_url, -1 ) == '/' ) {
                    $current_from_site_url = substr_replace( $current_from_site_url, "", -1 );
                }

                // This is a version of the URL that is http if the original was https or the opposite
                $current_from_site_url_alternative_http = be_mu_alternative_http_url( $current_from_site_url );

                /**
                 * The current site URL we need to replace. It needs to be global because we will use it later
                 * in the functions that replace in serialized data.
                 */
                if ( 1 === $loop ) {
                    $GLOBALS['be_mu_copy_site_search_for_url'] = $current_from_site_url;
                } elseif ( 2 === $loop ) {
                    $GLOBALS['be_mu_copy_site_search_for_url'] = urlencode( $current_from_site_url );
                } elseif ( 3 === $loop ) {
                    $GLOBALS['be_mu_copy_site_search_for_url'] = be_mu_copy_sites_add_slashes_to_slashes( $current_from_site_url );
                } elseif ( 4 === $loop ) {
                    $GLOBALS['be_mu_copy_site_search_for_url'] = $current_from_site_url_alternative_http;
                } elseif ( 5 === $loop ) {
                    $GLOBALS['be_mu_copy_site_search_for_url'] = urlencode( $current_from_site_url_alternative_http );
                } else {
                    $GLOBALS['be_mu_copy_site_search_for_url'] = be_mu_copy_sites_add_slashes_to_slashes( $current_from_site_url_alternative_http );
                }

                // In the next section of code we replace the old site URL with the new one if it is mentioned somewhere in the posts and pages
                $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "posts SET post_content = replace( post_content, %s, %s ) WHERE 1",
                    $GLOBALS['be_mu_copy_site_search_for_url'], $GLOBALS['be_mu_copy_site_replace_with_url'] );
                $status = $wpdb->query( $query );

                // Add data to the log variable
                if ( false !== $status ) {
                    $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in posts and pages content. Query: "' . esc_html( $query )
                        . '". Rows updated: ' . $status . '<br />';
                } else {
                    $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in posts and pages content. Query: "'
                        . esc_html( $query ) . '".</b><br />';
                }

                $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "posts SET post_excerpt = replace( post_excerpt, %s, %s ) WHERE 1",
                    $GLOBALS['be_mu_copy_site_search_for_url'], $GLOBALS['be_mu_copy_site_replace_with_url'] );
                $status = $wpdb->query( $query );

                // Add data to the log variable
                if ( false !== $status ) {
                    $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in posts and pages excerpt. Query: "'
                        . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
                } else {
                    $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in posts and pages excerpt. Query: "'
                        . esc_html( $query ) . '".</b><br />';
                }

                $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "posts SET guid = replace( guid, %s, %s ) WHERE 1",
                    $GLOBALS['be_mu_copy_site_search_for_url'], $GLOBALS['be_mu_copy_site_replace_with_url'] );
                $status = $wpdb->query( $query );

                // Add data to the log variable
                if ( false !== $status ) {
                    $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in posts and pages guid. Query: "'
                        . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
                } else {
                    $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in posts and pages guid. Query: "'
                        . esc_html( $query ) . '".</b><br />';
                }

                $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix
                    . "posts SET post_content_filtered = replace( post_content_filtered, %s, %s ) WHERE 1", $GLOBALS['be_mu_copy_site_search_for_url'],
                        $GLOBALS['be_mu_copy_site_replace_with_url'] );
                $status = $wpdb->query( $query );

                // Add data to the log variable
                if ( false !== $status ) {
                    $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in posts and pages filtered content. Query: "' . esc_html( $query )
                        . '". Rows updated: ' . $status . '<br />';
                } else {
                    $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in posts and pages filtered content. Query: "'
                        . esc_html( $query ) . '".</b><br />';
                }

                // We replace the old site URL with the new one if it is mentioned somewhere in the comments
                $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "comments SET comment_content = replace( comment_content, %s, %s ) WHERE 1",
                    $GLOBALS['be_mu_copy_site_search_for_url'], $GLOBALS['be_mu_copy_site_replace_with_url'] );
                $status = $wpdb->query( $query );

                // Add data to the log variable
                if ( false !== $status ) {
                    $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in comments content. Query: "' . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
                } else {
                    $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in comments content. Query: "'
                        . esc_html( $query ) . '".</b><br />';
                }

                // We replace the old site URL with the new one if it is mentioned somewhere in the liks
                $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "links SET link_url = replace( link_url, %s, %s ) WHERE 1",
                    $GLOBALS['be_mu_copy_site_search_for_url'], $GLOBALS['be_mu_copy_site_replace_with_url'] );
                $status = $wpdb->query( $query );

                // Add data to the log variable
                if ( false !== $status ) {
                    $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in links data. Query: "' . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
                } else {
                    $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in links data. Query: "'
                        . esc_html( $query ) . '".</b><br />';
                }

                // We get the options that contain the old URL, so we can replace it with the new one
                $query = $wpdb->prepare( "SELECT * FROM " . $copy_to_site_prefix
                    . "options WHERE LOCATE( %s, option_value ) != 0", $GLOBALS['be_mu_copy_site_search_for_url'] );
                $results_multi_array = $wpdb->get_results( $query, ARRAY_A );

                /*
                 * If the URL of the site to copy from, does not end in '/', and it is contained in the URL of the site to paste into, then
                 * we find the options with the URL of the site to paste into, so we can skip them when replacing the URL later.
                 */
                if ( substr( $GLOBALS['be_mu_copy_site_search_for_url'], -1 ) != '/'
                    && strpos( $GLOBALS['be_mu_copy_site_replace_with_url'], $GLOBALS['be_mu_copy_site_search_for_url'] ) !== false ) {
                    $query_skip = $wpdb->prepare( "SELECT * FROM " . $copy_to_site_prefix . "options WHERE LOCATE( %s, option_value ) != 0",
                        $GLOBALS['be_mu_copy_site_replace_with_url'] );
                    $results_skip_multi_array = $wpdb->get_results( $query_skip, ARRAY_A );
                }

                if ( ! empty( $results_multi_array ) ) {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We found ' . count( $results_multi_array ) . ' rows of options that contain the site URL. Query: "'
                        . esc_html( $query ) . '".<br />';

                    $skipped_IDs = Array();

                    foreach ( $results_multi_array as $results ) {

                        /*
                         * If the URL of the site to copy from, does not end in '/', and it is contained in the URL of the site to paste into, and
                         * we found URLs to skip earlier, we check each option if we should skip it and skip it if need to.
                         */
                        if ( substr( $GLOBALS['be_mu_copy_site_search_for_url'], -1 ) != '/'
                            && strpos( $GLOBALS['be_mu_copy_site_replace_with_url'], $GLOBALS['be_mu_copy_site_search_for_url'] ) !== false
                            && ! empty( $results_skip_multi_array ) ) {
                            $skip_option = 'no';
                            foreach ( $results_skip_multi_array as $results_skip ) {
                                if ( $results_skip['option_id'] == $results['option_id'] ) {
                                    $skip_option = 'yes';
                                    break;
                                }
                            }
                            if ( 'yes' === $skip_option ) {
                                $skipped_IDs[] = intval( $results['option_id'] );
                                continue;
                            }
                        }

                        // We replace the old URL with the new one in the options, while preserving serializied data
                        $new_value = be_mu_copy_sites_replace_url_in_maybe_serialized( $results['option_value'] );

                        // Update the new value of the option
                        $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "options SET option_value = %s WHERE option_id = %d",
                            $new_value, $results['option_id'] );
                        $status = $wpdb->query( $query );

                        // Add data to the log variable
                        if ( false !== $status ) {
                            $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in options data for option ID "' . esc_html( $results['option_id'] )
                                . '". Rows updated: ' . $status . '<br />';
                        } else {
                            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in options data for option ID "'
                                . esc_html( $results['option_id'] ) . '". Query: "' . esc_html( $query ) . '".</b><br />';
                        }
                    }

                    // Add data to the log variable if there were skipped options
                    if ( count( $skipped_IDs ) > 0 ) {
                        $GLOBALS['be_mu_copy_log'] .= 'We skipped the rows with the following option IDs "' . implode( ', ', $skipped_IDs )
                            . '" because they contain the URL of the site to paste into, which also starts with the URL of the site to copy from.<br />';
                    }

                } else {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We did not find any options that contain the site URL or there could be an error in the query. Query: "'
                        . esc_html( $query ) . '".<br />';
                }

                // We get the post meta data that contain the old URL, so we can replace it with the new one
                $query = $wpdb->prepare( "SELECT * FROM " . $copy_to_site_prefix . "postmeta WHERE LOCATE( %s, meta_value ) != 0",
                    $GLOBALS['be_mu_copy_site_search_for_url'] );
                $results_multi_array = $wpdb->get_results( $query, ARRAY_A );

                // We replace the old URL with the new one in the post meta data, while preserving serializied data
                if ( ! empty( $results_multi_array ) ) {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We found ' . count( $results_multi_array ) . ' rows of post meta data that contain the site URL. Query: "'
                        . esc_html( $query ) . '".<br />';

                    foreach ( $results_multi_array as $results ) {
                        $new_value = be_mu_copy_sites_replace_url_in_maybe_serialized( $results['meta_value'] );
                        $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "postmeta SET meta_value = %s WHERE meta_id = %d",
                            $new_value, $results['meta_id'] );
                        $status = $wpdb->query( $query );

                        // Add data to the log variable
                        if ( false !== $status ) {
                            $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in post meta data for meta ID "' . esc_html( $results['meta_id'] )
                                . '". Query: "' . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
                        } else {
                            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in post meta data for meta ID "'
                                . esc_html( $results['meta_id'] ) . '". Query: "' . esc_html( $query ) . '".</b><br />';
                        }
                    }
                } else {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We did not find any post meta data that contains the site URL or there could be an error in the query. Query: "'
                        . esc_html( $query ) . '".<br />';
                }

                // We get the comment meta data that contain the old URL, so we can replace it with the new one
                $query = $wpdb->prepare( "SELECT * FROM " . $copy_to_site_prefix . "commentmeta WHERE LOCATE( %s, meta_value ) != 0",
                    $GLOBALS['be_mu_copy_site_search_for_url'] );
                $results_multi_array = $wpdb->get_results( $query, ARRAY_A );

                // We replace the old URL with the new one in the comment meta data, while preserving serializied data
                if ( ! empty( $results_multi_array ) ) {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We found ' . count( $results_multi_array ) . ' rows of comment meta data that contain the site URL. Query: "'
                        . esc_html( $query ) . '".<br />';

                    foreach ( $results_multi_array as $results ) {
                        $new_value = be_mu_copy_sites_replace_url_in_maybe_serialized( $results['meta_value'] );
                        $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "commentmeta SET meta_value = %s WHERE meta_id = %d",
                            $new_value, $results['meta_id'] );
                        $status = $wpdb->query( $query );

                        // Add data to the log variable
                        if ( false !== $status ) {
                            $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in comment meta data for meta ID "' . esc_html( $results['meta_id'] )
                                . '". Query: "' . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
                        } else {
                            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in comment meta data for meta ID "'
                                . esc_html( $results['meta_id'] ) . '". Query: "' . esc_html( $query ) . '".</b><br />';
                        }
                    }
                } else {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We did not find any comment meta data that contains the site URL or there could be an error in the query. Query: "'
                        . esc_html( $query ) . '".<br />';
                }

                // We get the term meta data that contain the old URL, so we can replace it with the new one
                $query = $wpdb->prepare( "SELECT * FROM " . $copy_to_site_prefix . "termmeta WHERE LOCATE( %s, meta_value ) != 0",
                    $GLOBALS['be_mu_copy_site_search_for_url'] );
                $results_multi_array = $wpdb->get_results( $query, ARRAY_A );

                // We replace the old URL with the new one in the term meta data, while preserving serializied data
                if ( ! empty( $results_multi_array ) ) {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We found ' . count( $results_multi_array ) . ' rows of term meta data that contain the site URL. Query: "'
                        . esc_html( $query ) . '".<br />';

                    foreach ( $results_multi_array as $results ) {
                        $new_value = be_mu_copy_sites_replace_url_in_maybe_serialized( $results['meta_value'] );
                        $query = $wpdb->prepare( "UPDATE " . $copy_to_site_prefix . "termmeta SET meta_value = %s WHERE meta_id = %d",
                            $new_value, $results['meta_id'] );
                        $status = $wpdb->query( $query );

                        // Add data to the log variable
                        if ( false !== $status ) {
                            $GLOBALS['be_mu_copy_log'] .= 'Updated the site URL in term meta data for meta ID "' . esc_html( $results['meta_id'] )
                                . '". Query: "' . esc_html( $query ) . '". Rows updated: ' . $status . '<br />';
                        } else {
                            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not update the site URL in term meta data for meta ID "'
                                . esc_html( $results['meta_id'] ) . '". Query: "' . esc_html( $query ) . '".</b><br />';
                        }
                    }
                } else {

                    // Add data to the log variable
                    $GLOBALS['be_mu_copy_log'] .= 'We did not find any term meta data that contains the site URL or there could be an error in the query. Query: "'
                        . esc_html( $query ) . '".<br />';
                }

            // Here ends the foreach that goes through all URLs that need replacing
            }

        // Here ends the for that goes through the different ways an url can be stored in the database
        }

        // We get the blog details for the site to copy from
        $copy_from_attributes = get_blog_details( $copy_from_site_id );

        if ( is_object( $copy_from_attributes ) ) {

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= 'We got the blog details of the site to copy from<br />';

            // We set the blog details of the site to paste into the same as the one we copy from
            $status = update_blog_details(
                $copy_to_site_id,
                array(
                    'public' => $copy_from_attributes->public,
                    'archived' => $copy_from_attributes->archived,
                    'mature' => $copy_from_attributes->mature,
                    'spam' => $copy_from_attributes->spam,
                    'deleted' => $copy_from_attributes->deleted,
                    'lang_id' => $copy_from_attributes->lang_id,
                )
            );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Copied the blog details<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not copy the blog details</b><br />';
            }
        } else {

            // Add data to the log variable
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not get the blog details of the site to copy from</b><br />';
        }

        // We delete these options where we storred some data temporarily
        $settings = Array(
            'be_mu_copy_sites_copy_to_site_prefix_' . $task_id,
            'be_mu_copy_sites_copy_to_site_url_' . $task_id,
            'be_mu_copy_sites_copy_to_home_' . $task_id,
            'be_mu_copy_sites_copy_to_db_name_' . $task_id,
            'be_mu_copy_sites_copy_to_upload_path_' . $task_id,
            'be_mu_copy_sites_copy_to_upload_url_path_' . $task_id,
        );

        if ( $copy_title !== 'on' ) {
            $settings[] = 'be_mu_copy_sites_copy_to_blogname_' . $task_id;
        }

        if ( $copy_admin_email !== 'on' ) {
            $settings[] = 'be_mu_copy_sites_copy_to_admin_email_' . $task_id;
        }

        foreach ( $settings as $setting ) {
            $status = be_mu_delete_setting( $setting );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Deleted the site option named "' . esc_html( $setting ) . '"<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete the site option named "' . esc_html( $setting ) . '"</b><br />';
            }
        }

        /*
         * This is the option for not copying posts from the template site from the module settings.
         * You can filter it as well. Possible values are:
         * 'on' - Do not copy posts (first copies them, then deletes them at the end)
         * 'off' - Copy posts
         */
        $dont_copy_posts = apply_filters( 'beyond-multisite-filter-copy-template-no-posts', be_mu_get_setting( 'be-mu-copy-template-no-posts' ),
            $copy_from_site_id, $copy_to_site_id, $seconds_to_work, $task_id, $continuing_task, microtime( true ), $stages_done );

        // Delete all posts in the finished site copy
        if ( $dont_copy_posts === 'on' ) {
            $query = "DELETE FROM " . $copy_to_site_prefix . "posts WHERE post_type = 'post'";
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Deleted copied posts. Query: "' . esc_html( $query ) . '". Rows deleted: ' . $status . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete copied posts. Query: "' . esc_html( $query ) . '".</b><br />';
            }
        }

        /*
         * This is the option for not copying pages from the template site from the module settings.
         * You can filter it as well. Possible values are:
         * 'on' - Do not copy pages (first copies them, then deletes them at the end)
         * 'off' - Copy pages
         */
        $dont_copy_pages = apply_filters( 'beyond-multisite-filter-copy-template-no-pages', be_mu_get_setting( 'be-mu-copy-template-no-pages' ),
            $copy_from_site_id, $copy_to_site_id, $seconds_to_work, $task_id, $continuing_task, microtime( true ), $stages_done );

        // Delete all pages in the finished site copy
        if ( $dont_copy_pages === 'on' ) {
            $query = "DELETE FROM " . $copy_to_site_prefix . "posts WHERE post_type = 'page'";
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Deleted copied pages. Query: "' . esc_html( $query ) . '". Rows deleted: ' . $status . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete copied pages. Query: "' . esc_html( $query ) . '".</b><br />';
            }
        }

        if ( $dont_copy_posts === 'on' || $dont_copy_pages === 'on' ) {

            // Delete all revisions of posts (from any post type) that do not exist
            $query = "DELETE FROM " . $copy_to_site_prefix . "posts WHERE post_type = 'revision' "
                . "AND post_parent NOT IN ( SELECT ID FROM " . $copy_to_site_prefix . "posts WHERE 1 )";
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Deleted copied revisions of posts (from any post type) that do not exist. Query: "'
                    . esc_html( $query ) . '". Rows deleted: ' . $status . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete copied revisions of posts (from any post type) that do not exist. Query: "'
                    . esc_html( $query ) . '".</b><br />';
            }

            // Delete all post meta data of posts (from any post type) that do not exist
            $query = "DELETE FROM " . $copy_to_site_prefix . "postmeta WHERE "
                . "post_id NOT IN ( SELECT ID FROM " . $copy_to_site_prefix . "posts WHERE 1 )";
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Deleted copied post meta data of posts (from any post type) that do not exist. Query: "'
                    . esc_html( $query ) . '". Rows deleted: ' . $status . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete copied post meta data of posts '
                    . '(from any post type) that do not exist. Query: "' . esc_html( $query ) . '".</b><br />';
            }

            // Delete all comments for posts (from any post type) that do not exist
            $query = "DELETE FROM " . $copy_to_site_prefix . "comments WHERE "
                . "comment_post_ID NOT IN ( SELECT ID FROM " . $copy_to_site_prefix . "posts WHERE 1 )";
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Deleted copied comments for posts (from any post type) that do not exist. Query: "'
                    . esc_html( $query ) . '". Rows deleted: ' . $status . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete copied comments for posts (from any post type) that do not exist. Query: "'
                    . esc_html( $query ) . '".</b><br />';
            }

            // Delete all comment meta data for comments (from any comment type) that do not exist
            $query = "DELETE FROM " . $copy_to_site_prefix . "commentmeta WHERE "
                . "comment_id NOT IN ( SELECT comment_ID FROM " . $copy_to_site_prefix . "comments WHERE 1 )";
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Deleted copied comment meta data for comments (from any comment type) that do not exist. Query: "'
                    . esc_html( $query ) . '". Rows deleted: ' . $status . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete copied comment meta data for comments (from any comment type) '
                    . 'that do not exist. Query: "' . esc_html( $query ) . '".</b><br />';
            }

            // Update comment count numbers
            switch_to_blog( $copy_to_site_id );
            delete_transient( 'wc_count_comments' );
            restore_current_blog();
        }

        /*
         * This is the option for not copying categories from the template site from the module settings.
         * You can filter it as well. Possible values are:
         * 'on' - Do not copy categories (first copies them, then deletes them at the end)
         * 'off' - Copy categories
         */
        $dont_copy_categories = apply_filters( 'beyond-multisite-filter-copy-template-no-categories',
            be_mu_get_setting( 'be-mu-copy-template-no-categories' ),
            $copy_from_site_id, $copy_to_site_id, $seconds_to_work, $task_id, $continuing_task, microtime( true ), $stages_done );

        // Delete all categories in the finished site copy
        if ( $dont_copy_categories === 'on' ) {
            switch_to_blog( $copy_to_site_id );
            $terms = get_terms( array( 'taxonomy' => 'category', 'fields' => 'ids', 'hide_empty' => false ) );
            if ( is_array( $terms ) ) {
                foreach ( $terms as $value ) {
                    wp_delete_term( $value, 'category' );
                }
            }
            restore_current_blog();
            $GLOBALS['be_mu_copy_log'] .= 'Deleted copied post categories if any.<br />';
        }

        /*
         * This is the option for not copying tags from the template site from the module settings.
         * You can filter it as well. Possible values are:
         * 'on' - Do not copy tags (first copies them, then deletes them at the end)
         * 'off' - Copy tags
         */
        $dont_copy_tags = apply_filters( 'beyond-multisite-filter-copy-template-no-tags', be_mu_get_setting( 'be-mu-copy-template-no-tags' ),
            $copy_from_site_id, $copy_to_site_id, $seconds_to_work, $task_id, $continuing_task, microtime( true ), $stages_done );

        // Delete all tags in the finished site copy
        if ( $dont_copy_tags === 'on' ) {
            switch_to_blog( $copy_to_site_id );
            $terms = get_terms( array( 'taxonomy' => 'post_tag', 'fields' => 'ids', 'hide_empty' => false ) );
            if ( is_array( $terms ) ) {
                foreach ( $terms as $value ) {
                    wp_delete_term( $value, 'post_tag' );
                }
            }
            restore_current_blog();
            $GLOBALS['be_mu_copy_log'] .= 'Deleted copied post tags if any.<br />';
        }

        // Delete all attachment posts in the finished site copy
        if ( $dont_copy_media === 'on' ) {
            $query = "DELETE FROM " . $copy_to_site_prefix . "posts WHERE post_type = 'attachment'";
            $status = $wpdb->query( $query );

            // Add data to the log variable
            if ( false !== $status ) {
                $GLOBALS['be_mu_copy_log'] .= 'Deleted copied attachments (post type "attachment"). Query: "' . esc_html( $query )
                    . '". Rows deleted: ' . $status . '<br />';
            } else {
                $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not delete copied attachments. Query: "' . esc_html( $query ) . '".</b><br />';
            }
        }

        // We delete all cache to avoid conflicts with redis cache
        $status = wp_cache_flush();
        if ( false !== $status ) {
            $GLOBALS['be_mu_copy_log'] .= 'Cache flushed with wp_cache_flush().<br />';
        } else {
            $GLOBALS['be_mu_copy_log'] .= '<b class="be-mu-red">Error: Could not flush the cache with wp_cache_flush().</b><br />';
        }

        $GLOBALS['be_mu_copy_log'] .= 'Stage 9 is done at ' . esc_html( be_mu_unixtime_to_wp_datetime( time() ) ) . ' (Unix time: ' . time() . ')<br />';
        $GLOBALS['be_mu_copy_log'] .= 'Request ended<br />';

        be_mu_copy_sites_add_to_log( $task_id );

        delete_site_option( 'be-mu-copy-site-on-creation-continue-for-' . intval( $copy_to_site_id ) );

        do_action( 'beyond-multisite-copy-site-for-seconds-stage-done', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
            $task_id, $continuing_task, microtime( true ), $stages_done );

        $log_array = be_mu_get_log_data( $task_id );
        if ( is_array( $log_array ) ) {
            if ( ( substr_count( $log_array['log'], '<b class="be-mu-red">Error:') ) > 0 ) {

                do_action( 'beyond-multisite-end-copy-site-for-seconds', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
                    $task_id, $continuing_task, microtime( true ), $stages_done, 'task-done-with-errors' );

                return Array( 'status' => 'task-done-with-errors', 'task-id' => $task_id );
            }
        }

        do_action( 'beyond-multisite-end-copy-site-for-seconds', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
            $task_id, $continuing_task, microtime( true ), $stages_done, 'task-done' );

        return Array( 'status' => 'task-done', 'task-id' => $task_id );
    }

    $GLOBALS['be_mu_copy_log'] .= 'Request ended<br />';

    $save_progress = Array(
        'from-id' => $copy_from_site_id,
        'task-id' => $task_id,
        'stages-done' => $stages_done,
        'next-stage-parts-done' => $next_stage_parts_done,
    );

    update_site_option( 'be-mu-copy-site-on-creation-continue-for-' . intval( $copy_to_site_id ), $save_progress );

    be_mu_copy_sites_add_to_log( $task_id );

    do_action( 'beyond-multisite-end-copy-site-for-seconds', $copy_from_site_id, $copy_to_site_id, $seconds_to_work,
        $task_id, $continuing_task, microtime( true ), $stages_done, 'request-done' );

    return Array( 'status' => 'request-done', 'task-id' => $task_id );
}

// On WP-CLI init, creates a command for copying sites
function be_mu_copy_sites_wp_cli_init() {
    WP_CLI::add_command( 'be-mu-copy-site', 'be_mu_copy_sites_wp_cli' );
}

/**
 * Executes the WP-CLI command to copy a site
 * @param array $args
 * @param array $assoc_args
 * @return mixed
 */
function be_mu_copy_sites_wp_cli( $args, $assoc_args ) {
    if ( ! is_array( $assoc_args ) || ! array_key_exists( 'from', $assoc_args ) || ! array_key_exists( 'to', $assoc_args )
        || ! array_key_exists( 'for', $assoc_args ) ) {
        WP_CLI::error( __( "It is required that you set the 3 arguments 'from', 'to', and 'for'. They tell us from which site ID to copy into "
            . "which site ID, and for how many seconds before we stop and leave the rest work for when the site is visited for the first time.",
            'beyond-multisite' ) );
        return;
    }
    $from_id = $assoc_args['from'];
    $to_id = $assoc_args['to'];
    $for_seconds = $assoc_args['for'];
    if ( ! be_mu_is_whole_positive_number( $from_id ) || ! be_mu_is_whole_positive_number( $to_id )
        || ! be_mu_is_whole_positive_number( $for_seconds ) || $from_id === $to_id ) {
        WP_CLI::error( __( "All 3 arguments must be a whole positive number and the site ID of the site you copy "
            . "from must be different from the one you paste into.", 'beyond-multisite' ) );
        return;
    }
    $time_start = microtime(true);
    $result = be_mu_copy_sites_copy_for_seconds( $from_id, $to_id, $for_seconds );
    $time_took = round( ( microtime(true) - $time_start ), 2 );
    if ( 'request-done' === $result['status'] ) {
        WP_CLI::success( sprintf( __( 'The copy process was started, but it did not finish! To finish it visit the destination site or its dashboard. '
            . 'It took %s seconds.', 'beyond-multisite' ), floatval( $time_took ) ) );
    } elseif ( 'task-done' === $result['status'] ) {
        WP_CLI::success( sprintf( __( 'The copy process was completed. It took %s seconds.', 'beyond-multisite' ), floatval( $time_took ) ) );
    } elseif ( 'task-done-with-errors' === $result['status'] ) {
        WP_CLI::error( sprintf( __( 'The copy process did finish, but with errors! It took %s seconds. Check the copy log for task ID %s.',
            'beyond-multisite' ), floatval( $time_took ), sanitize_html_class( $result['task-id'] ) ) );
    } elseif ( 'fatal-error' === $result['status'] ) {
        WP_CLI::error( sprintf( __( 'The copy process was interrupted due to a fatal error! It took %s'
            . ' seconds. The site we pasted into (site ID %d) is probably damaged now, and it should be deleted. '
            . 'Check the copy log for task ID %s.', 'beyond-multisite' ), floatval( $time_took ), intval( $to_id ),
            sanitize_html_class( $result['task-id'] ) ) );
    } elseif ( 'pre-copy-error' === $result['status'] ) {
        WP_CLI::error( sprintf( __( 'The copy process did not start due to a pre-copy error! It took %s'
            . ' seconds. Check the copy log for task ID %s.', 'beyond-multisite' ), floatval( $time_took ), sanitize_html_class( $result['task-id'] ) ) );
    } elseif ( 'pre-request-fatal-error' === $result['status'] ) {
        WP_CLI::error( sprintf( __( 'The copy process was interrupted due to a pre-request fatal error! It took %s'
            . ' seconds. The site we pasted into (site ID %d) is probably damaged now, and it should be deleted. Check the copy log for task ID %s.',
            'beyond-multisite' ), floatval( $time_took ), intval( $to_id ), sanitize_html_class( $result['task-id'] ) ) );
    } else {
        WP_CLI::error( sprintf( __( 'Unknown error! It took %s seconds. Check the copy log for task ID %s.',
            'beyond-multisite' ), floatval( $time_took ), sanitize_html_class( $result['task-id'] ) ) );
    }
}

/**
 * Maybe we add the log data to the database, maybe we delete unwanted old logs, and we end the request (maybe also output an error code)
 * @param string $task_id
 */
function be_mu_copy_sites_add_to_log( $task_id ) {

    // We get any log data from a previous request for the same task
    $current_log_array = be_mu_get_log_data( $task_id );

    // If there is any log data from a previous request for the same task we merge it with the new data when we add it to the database
    if ( is_array( $current_log_array ) ) {

        // We need these to connect to the database
        global $wpdb;
        $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
        $db_table = $main_blog_prefix . 'be_mu_logs';

        // We delete the data from previous requests for the current task, because we will add the whole log again
        $wpdb->query( $wpdb->prepare( "DELETE FROM " . $db_table . " WHERE task_id = %s", $task_id ) );

        // Add the log data to the database
        be_mu_add_log_data( 'copy sites', $task_id, $current_log_array['log'] . $GLOBALS['be_mu_copy_log'] );

    // If there is no log data from a previous request for the same task, we just add the new log data
    } else {
        be_mu_add_log_data( 'copy sites', $task_id, $GLOBALS['be_mu_copy_log'] );
    }

    // We delete the unwanted logs based on the selected setting
    be_mu_copy_sites_delete_unwanted_logs();
}

/**
 * Checks for errors before each request to copy a template site or continue copying it
 * @param int $copy_from_site_id
 * @param int $copy_to_site_id
 * @param string $task_id
 * @param string $continuing_task
 */
function be_mu_copy_sites_pre_request_checks( $copy_from_site_id, $copy_to_site_id, $task_id, $continuing_task ) {

    $main_site_id = be_mu_get_main_site_id();

    // We stop if we are copying from or pasting into the main site
    if ( $copy_from_site_id == $main_site_id || $copy_to_site_id == $main_site_id || is_main_site( $copy_from_site_id )
        || is_main_site( $copy_to_site_id ) ) {
        return 'main-site';
    }

    // We stop if the site ID to copy from is less than 1
    if ( $copy_from_site_id < 1 ) {
        return 'invalid-from-id';
    }

    // We stop if the site ID to paste into is less than 1
    if ( $copy_to_site_id < 1 ) {
        return 'invalid-to-id';
    }

    // We stop if the site ID to paste into is the same as the site ID to copy from
    if ( $copy_to_site_id == $copy_from_site_id ) {
        return 'same-ids';
    }

    // We stop if the site to copy from does not exist
    if ( get_site( $copy_from_site_id ) === null ) {
        return 'from-site-not-exist';
    }

    // We stop if the site to paste into does not exist
    if ( get_site( $copy_to_site_id ) === null ) {
        return 'to-site-not-exist';
    }

    // We get the site URLs of both sites
    $copy_from_site_url = get_blog_option( $copy_from_site_id, 'siteurl' );
    $copy_to_site_url = get_blog_option( $copy_to_site_id, 'siteurl' );

    // We stop if we could not get the site URL of the site to copy from
    if ( false === $copy_from_site_url ) {
        return 'no-site-url-copy-from';
    }

    // We stop if we could not get the site URL of the site to paste into
    if ( false === $copy_to_site_url ) {
        return 'no-site-url-copy-to';
    }

    // If ms_files_rewriting is enabled and upload_path is empty, wp_upload_dir is not reliable.
    $trim_from_upload_path = trim( get_blog_option( $copy_from_site_id, 'upload_path' ) );
    $trim_to_upload_path = trim( get_blog_option( $copy_to_site_id, 'upload_path' ) );
    if ( get_site_option( 'ms_files_rewriting' ) && ( empty( $trim_from_upload_path ) || empty( $trim_to_upload_path ) ) ) {
        return 'upload-path-not-reliable';
    }

    if ( 'no' === $continuing_task ) {

        $upload_dir_from = be_mu_get_site_upload_folder( $copy_from_site_id );
        $upload_dir_to = be_mu_get_site_upload_folder( $copy_to_site_id );

        // We stop if the upload folder paths of both sites are the same
        if ( $upload_dir_from == $upload_dir_to ) {
            return 'same-upload-folders';
        }

        // We stop if we cannot read from the uploads folder of the site to copy from
        if ( ! is_readable( $upload_dir_from ) ) {
            return 'cannot-read-from-folder';
        }

        // We stop if we cannot write to the uploads folder of the site to paste into
        if ( ! is_writable( $upload_dir_to ) ) {
            return 'cannot-write-to-folder';
        }                 

    // If we are continuing a task
    } else {

        $continue_site_copy = get_site_option( 'be-mu-copy-site-on-creation-continue-for-' . $copy_to_site_id );

        if ( ! is_array( $continue_site_copy ) || ! array_key_exists( 'from-id', $continue_site_copy )
            || ! array_key_exists( 'task-id', $continue_site_copy )
            || ! array_key_exists( 'stages-done', $continue_site_copy ) || ! array_key_exists( 'next-stage-parts-done', $continue_site_copy )
            || $task_id !== $continue_site_copy['task-id'] || intval( $copy_from_site_id ) !== intval( $continue_site_copy['from-id'] )
            || intval( $continue_site_copy['stages-done'] ) < 0 || intval( $continue_site_copy['stages-done'] ) > 8 ) {
            return 'invalid-continue-data';
        }
    }

    return 'ok';
}

// Shows a message after a template site was copied into another site created from the network admin add new site screen
function be_mu_copy_sites_network_notice_after_add_site() {
    if ( current_user_can( 'manage_network' ) && isset( $_GET['id'] ) ) {
        $current_screen = get_current_screen();
        if ( is_object( $current_screen ) && 'site-new-network' === $current_screen->base ) {
            $notice_data = get_site_option( 'be-mu-copy-site-on-creation-admin-notice' );
            if ( false !== $notice_data && is_array( $notice_data ) && intval( $_GET['id'] ) === intval( $notice_data['to-site-id'] ) ) {
                delete_site_option( 'be-mu-copy-site-on-creation-admin-notice' );
                ?>
                <div class="notice notice-<?php echo esc_attr( sanitize_html_class( $notice_data['type'] ) ); ?> is-dismissible">
                    <p>
                        <?php
                        if ( 'request-done' === $notice_data['status'] ) {
                            printf( esc_html__( 'The process of replacing the new site with a copy of the %ssite with ID %d%s was started, '
                                . 'but it did not finish! To finish it %svisit the new site%s or its %sdashboard%s. It took %s seconds. %sView log%s.',
                                'beyond-multisite' ), '<a target="_blank" href="' . esc_url( network_admin_url( 'site-info.php?id='
                                . intval( $notice_data['from-site-id'] ) ) ) . '">', intval( $notice_data['from-site-id'] ), '</a>',
                                '<a target="_blank" href="' . esc_url( get_site_url( intval( $notice_data['to-site-id'] ) ) ) . '">', '</a>',
                                '<a target="_blank" href="' . esc_url( get_admin_url( intval( $notice_data['to-site-id'] ) ) ) . '">', '</a>',
                                floatval( $notice_data['time'] ),
                                '<a target="_blank" href="' . esc_url( network_admin_url( 'sites.php?page=be_mu_copy_sites_logs&task_id='
                                . sanitize_html_class( $notice_data['task-id'] ) . '&action=view' ) ) . '">', '</a>' );
                        } elseif ( 'task-done' === $notice_data['status'] ) {
                            printf( esc_html__( 'The new site was replaced with a copy of the %ssite with ID %d%s! It took %s seconds. %sView log%s.',
                                'beyond-multisite' ), '<a target="_blank" href="' . esc_url( network_admin_url( 'site-info.php?id='
                                . intval( $notice_data['from-site-id'] ) ) ) . '">', intval( $notice_data['from-site-id'] ), '</a>', floatval( $notice_data['time'] ),
                                '<a target="_blank" href="' . esc_url( network_admin_url( 'sites.php?page=be_mu_copy_sites_logs&task_id='
                                . sanitize_html_class( $notice_data['task-id'] ) . '&action=view' ) ) . '">', '</a>' );
                        } elseif ( 'task-done-with-errors' === $notice_data['status'] ) {
                            printf( esc_html__( 'The new site was replaced with a copy of the %ssite with ID %d%s, but with errors! It took %s seconds. '
                                . '%sView log%s.', 'beyond-multisite' ), '<a target="_blank" href="' . esc_url( network_admin_url( 'site-info.php?id='
                                . intval( $notice_data['from-site-id'] ) ) ) . '">', intval( $notice_data['from-site-id'] ), '</a>', floatval( $notice_data['time'] ),
                                '<a target="_blank" href="' . esc_url( network_admin_url( 'sites.php?page=be_mu_copy_sites_logs&task_id='
                                . sanitize_html_class( $notice_data['task-id'] ) . '&action=view' ) ) . '">', '</a>' );
                        } elseif ( 'fatal-error' === $notice_data['status'] ) {
                            printf( esc_html__( 'The process of replacing the new site with a copy of the %ssite with ID %d%s was interrupted due '
                                . 'to a fatal error! The new site is probably damaged now, and it should be deleted. It took %s seconds. '
                                . '%sView log%s.', 'beyond-multisite' ), '<a target="_blank" href="' . esc_url( network_admin_url( 'site-info.php?id='
                                . intval( $notice_data['from-site-id'] ) ) ) . '">', intval( $notice_data['from-site-id'] ), '</a>', floatval( $notice_data['time'] ),
                                '<a target="_blank" href="' . esc_url( network_admin_url( 'sites.php?page=be_mu_copy_sites_logs&task_id='
                                . sanitize_html_class( $notice_data['task-id'] ) . '&action=view' ) ) . '">', '</a>' );
                        } elseif ( 'pre-copy-error' === $notice_data['status'] ) {
                            printf( esc_html__( 'The new site was not replaced with a copy of the %ssite with ID %d%s, because of a pre-copy error! '
                                . 'It took %s seconds. %sView log%s.', 'beyond-multisite' ),
                                '<a target="_blank" href="' . esc_url( network_admin_url( 'site-info.php?id='
                                . intval( $notice_data['from-site-id'] ) ) ) . '">', intval( $notice_data['from-site-id'] ), '</a>', floatval( $notice_data['time'] ),
                                '<a target="_blank" href="' . esc_url( network_admin_url( 'sites.php?page=be_mu_copy_sites_logs&task_id='
                                . sanitize_html_class( $notice_data['task-id'] ) . '&action=view' ) ) . '">', '</a>' );
                        } elseif ( 'pre-request-fatal-error' === $notice_data['status'] ) {
                            printf( esc_html__( 'The process of replacing the new site with a copy of the %ssite with ID %d%s was interrupted due '
                                . 'to a pre-request fatal error! The new site is probably damaged now, and it should be deleted. It took %s seconds. '
                                . '%sView log%s.', 'beyond-multisite' ), '<a target="_blank" href="' . esc_url( network_admin_url( 'site-info.php?id='
                                . intval( $notice_data['from-site-id'] ) ) ) . '">', intval( $notice_data['from-site-id'] ), '</a>', floatval( $notice_data['time'] ),
                                '<a target="_blank" href="' . esc_url( network_admin_url( 'sites.php?page=be_mu_copy_sites_logs&task_id='
                                . sanitize_html_class( $notice_data['task-id'] ) . '&action=view' ) ) . '">', '</a>' );
                        } else {
                            printf( esc_html__( 'The process of replacing the new site with a copy of the %ssite with ID %d%s resulted in an unknown error!'
                                . ' It took %s seconds. %sView log%s.', 'beyond-multisite' ),
                                '<a target="_blank" href="' . esc_url( network_admin_url( 'site-info.php?id='
                                . intval( $notice_data['from-site-id'] ) ) ) . '">', intval( $notice_data['from-site-id'] ), '</a>', floatval( $notice_data['time'] ),
                                '<a target="_blank" href="' . esc_url( network_admin_url( 'sites.php?page=be_mu_copy_sites_logs&task_id='
                                . sanitize_html_class( $notice_data['task-id'] ) . '&action=view' ) ) . '">', '</a>' );
                        }
                        ?>
                    </p>
                </div>
                <?php
            }
        }
    }
}

/**
 * Copies a template site or normal copy site into a newly created site for recent versions of WordPress
 * @param object $new_site
 * @param array $args
 */
function be_mu_copy_sites_on_site_creation( $new_site, $args ) {
    if ( isset( $_GET['be-mu-copy-from-post'] ) && current_user_can( 'manage_network' ) ) {
        be_mu_copy_sites_maybe_copy_to_new_site( intval( $_GET['be-mu-copy-from-post'] ), $new_site->blog_id );
    } elseif ( be_mu_get_setting( 'be-mu-copy-template-site-enable' ) == 'on' ) {
        be_mu_copy_sites_maybe_copy_template( $new_site->blog_id );
    }
}

/**
 * Copies a template site or normal copy site into a newly created site for older versions of WordPress
 * @param int $site_id
 * @param int $user_id
 * @param string $domain
 * @param string $path
 * @param int $network_id
 * @param array $meta
 */
function be_mu_copy_sites_on_site_creation_old_wordpress( $site_id, $user_id, $domain, $path, $network_id, $meta ) {
    if ( isset( $_GET['be-mu-copy-from-post'] ) && current_user_can( 'manage_network' ) ) {
        be_mu_copy_sites_maybe_copy_to_new_site( intval( $_GET['be-mu-copy-from-post'] ), $site_id );
    } elseif ( be_mu_get_setting( 'be-mu-copy-template-site-enable' ) == 'on' ) {
        be_mu_copy_sites_maybe_copy_template( $site_id );
    }
}

/**
 * Starts to copy a site into a newly created site
 * @param int $from_site_id
 * @param int $to_site_id
 */
function be_mu_copy_sites_maybe_copy_to_new_site( $from_site_id, $to_site_id ) {

    $start_time = microtime( true );

    // We delete any leftover notice message from site creation outisde the network admin
    delete_site_option( 'be-mu-copy-site-on-creation-admin-notice' );

    // Time to copy before interrupting and postponing the rest of the copy process
    $seconds = be_mu_get_setting( 'be-mu-copy-template-site-time' );

    if ( ! be_mu_is_whole_positive_number( $seconds ) ) {
        $seconds = 5;
    }

    // Copy the site for some seconds and stop to continue later
    $result = be_mu_copy_sites_copy_for_seconds( $from_site_id, $to_site_id, $seconds );

    $copy_time = round( microtime( true ) - $start_time, 2 );

    $notice_data = Array(
        'task-id' => sanitize_html_class( $result['task-id'] ),
        'to-site-id' => intval( $to_site_id ),
        'from-site-id' => intval( $from_site_id ),
        'status' => $result['status'],
        'time' => $copy_time,
    );

    if ( 'request-done' === $result['status'] ) {
        $notice_data['type'] = 'warning';
    } elseif ( 'task-done' === $result['status'] ) {
        $notice_data['type'] = 'success';
    } else {
        $notice_data['type'] = 'error';
    }

    update_site_option( 'be-mu-copy-site-on-creation-admin-notice', $notice_data );
}

/**
 * Starts to copy a template site into a site by id
 * @param int $to_site_id
 */
function be_mu_copy_sites_maybe_copy_template( $to_site_id ) {

    $start_time = microtime( true );

    // We delete any leftover notice message from site creation outisde the network admin
    delete_site_option( 'be-mu-copy-site-on-creation-admin-notice' );

    // We do not do anything if this is a Super Admin and the setting to not affect sites created by a Super Admin is on
    if ( be_mu_get_setting( 'be-mu-copy-template-site-skip-super' ) == 'on' && current_user_can( 'manage_network' )
        && 'yes' !== $GLOBALS['be_mu_copy_skip_super_check'] ) {
        return;
    }

    // get default site to coppy from ID
    $from_site_id = be_mu_get_setting( 'be-mu-copy-template-site-id' );

    // Time to copy before interrupting and postponing the rest of the copy process
    $seconds = be_mu_get_setting( 'be-mu-copy-template-site-time' );

    if ( ! be_mu_is_whole_positive_number( $seconds ) ) {
        $seconds = 5;
    }

    // Copy the site for some seconds and stop to continue later
    $result = be_mu_copy_sites_copy_for_seconds( $from_site_id, $to_site_id, $seconds );

    $copy_time = round( microtime( true ) - $start_time, 2 );

    $notice_data = Array(
        'task-id' => sanitize_html_class( $result['task-id'] ),
        'to-site-id' => intval( $to_site_id ),
        'from-site-id' => intval( $from_site_id ),
        'status' => $result['status'],
        'time' => $copy_time,
    );

    if ( 'request-done' === $result['status'] ) {
        $notice_data['type'] = 'warning';
    } elseif ( 'task-done' === $result['status'] ) {
        $notice_data['type'] = 'success';
    } else {
        $notice_data['type'] = 'error';

        if ( be_mu_get_setting( 'be-mu-copy-template-site-notify-error' ) === 'on' ) {
            $network_object = get_current_site();
            $subject = sprintf( esc_html__( 'Error while copying the template site (Task ID: %s)', 'beyond-multisite' ),
                sanitize_html_class( $result['task-id'] ) );
            $message = sprintf( esc_html__( 'There was an error while copying the template site into a new site. %sView log%s.', 'beyond-multisite' ),
                '<a target="_blank" href="' . esc_url( network_admin_url( 'sites.php?page=be_mu_copy_sites_logs&task_id='
                . sanitize_html_class( $result['task-id'] ) . '&action=view' ) ) . '">', '</a>' );;
            be_mu_send_email( be_mu_get_wordpress_email(), esc_html( $network_object->site_name ), get_site_option( 'admin_email' ), $subject, $message );
        }
    }

    update_site_option( 'be-mu-copy-site-on-creation-admin-notice', $notice_data );

    do_action( 'beyond-multisite-after-copy-template-site', $from_site_id, $to_site_id );
}

// A message shown in the network admin add new site screen before a site is created
function be_mu_copy_sites_network_notice_before_add_site() {
    if ( isset( $_GET['be-mu-copy-from'] ) ) {
        $from_site_id = $_GET['be-mu-copy-from'];
        ?>
        <div>
            <div id="be-mu-copy-site-notice">
                <h3><?php echo esc_html__( 'Template Site Copy', 'beyond-multisite' ) ?></h3>
                <p>
                    <?php
                    echo sprintf( esc_html__( '%sNotice%s: The new site will automatically be replaced with a copy of the site with %sID %d%s (%s)!', 'beyond-multisite' ),
                        '<b>', '</b>', '<b>', intval( $from_site_id ), '</b>', '<a href="' . esc_url( get_site_url( intval( $from_site_id ) ) ) . '" target="_blank">'
                        . esc_html( get_site_url( intval( $from_site_id ) ) ) . "</a>" );
                    ?>
                </p>
                <p>
                    <?php
                    echo esc_html__( 'The "Template site copy settings" will be used for this copy!', 'beyond-multisite' );
                    ?>
                </p>
            </div>
        </div>
        <?php
    } elseif ( be_mu_get_setting( 'be-mu-copy-template-site-enable' ) == 'on' ) {
        if ( be_mu_get_setting( 'be-mu-copy-template-site-skip-super' ) == 'on' && current_user_can( 'manage_network' ) ) {
            return;
        }
        $from_site_id = be_mu_get_setting( 'be-mu-copy-template-site-id' );
        ?>
        <div>
            <div id="be-mu-copy-site-notice">
                <h3><?php echo esc_html__( 'Template Site Copy', 'beyond-multisite' ) ?></h3>
                <p>
                    <?php
                    echo sprintf( esc_html__( '%sNotice%s: The new site will automatically be replaced with a copy of the site with %sID %d%s (%s)!', 'beyond-multisite' ),
                        '<b>', '</b>', '<b>', intval( $from_site_id ), '</b>', '<a href="' . esc_url( get_site_url( intval( $from_site_id ) ) ) . '" target="_blank">'
                        . esc_html( get_site_url( intval( $from_site_id ) ) ) . "</a>" );
                    ?>
                </p>
                <p>
                    <?php
                    echo esc_html__( 'The "Template site copy settings" will be used for this copy!', 'beyond-multisite' );
                    ?>
                </p>
            </div>
        </div>
        <?php
    }
}

// Continues a copying processs if it is needed when the site is visited and refreshes the page until it is done
function be_mu_copy_sites_maybe_continue_copy_site() {

    $continue_site_copy = get_site_option( 'be-mu-copy-site-on-creation-continue-for-' . get_current_blog_id() );

    if ( is_array( $continue_site_copy ) && array_key_exists( 'from-id', $continue_site_copy ) && array_key_exists( 'task-id', $continue_site_copy ) ) {

        $from_site_id = intval( $continue_site_copy['from-id'] );
        $task_id = sanitize_html_class( $continue_site_copy['task-id'] );

        // Time to copy before interrupting and postponing the rest of the copy process
        $seconds = be_mu_get_setting( 'be-mu-copy-template-site-time' );

        if ( ! be_mu_is_whole_positive_number( $seconds ) ) {
            $seconds = 5;
        }

        $result = be_mu_copy_sites_copy_for_seconds( $from_site_id, get_current_blog_id(), $seconds, $task_id );

        if ( 'request-done' !== $result['status'] && 'task-done' !== $result['status']
            && be_mu_get_setting( 'be-mu-copy-template-site-notify-error' ) === 'on' ) {
            $network_object = get_current_site();
            $subject = sprintf( esc_html__( 'Error while copying the template site (Task ID: %s)', 'beyond-multisite' ),
                sanitize_html_class( $result['task-id'] ) );
            $message = sprintf( esc_html__( 'There was an error while copying the template site into a new site. %sView log%s.', 'beyond-multisite' ),
                '<a target="_blank" href="' . esc_url( network_admin_url( 'sites.php?page=be_mu_copy_sites_logs&task_id='
                . sanitize_html_class( $result['task-id'] ) . '&action=view' ) ) . '">', '</a>' );;
            be_mu_send_email( be_mu_get_wordpress_email(), esc_html( $network_object->site_name ), get_site_option( 'admin_email' ), $subject, $message );
        }

        wp_die( esc_html__( 'Configuring site, please wait. This page may reload several times.', 'beyond-multisite' )
            . '<script type="text/javascript">window.location.reload( false );</script>' );
    }
}
