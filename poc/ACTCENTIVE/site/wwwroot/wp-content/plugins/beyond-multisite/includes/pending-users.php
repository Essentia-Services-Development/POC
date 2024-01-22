<?php

/**
 * In this file we have all the hooks and functions related to the pending users module.
 * They are not a lot, basically we make a menu element and a page, where we display the information from a database table that wordpress using.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// All the hooks for the pending users module will run only if the module is turned on
if ( be_mu_get_setting( 'be-mu-pending-status-module' ) == 'on' ) {

    // Adds a submenu page called Pending under the Users menu in the network admin panel and also loads a css style on that page
    add_action( 'network_admin_menu', 'be_mu_add_pending_menu' );
}

// Displays the search text field and button for searching pending users
function be_mu_echo_search_pending_field() {
    ?>
    <div class="be-mu-white-box be-mu-w100per">
        <input name="be-mu-search-pending-string" onkeypress="pendingUsersKeyPressSeach( event )" id="be-mu-search-pending-string" type="text" size="20" />
        <input onclick="pendingUsersSearch()" class="button" type="button" id="be-mu-search-pending-button" name="be-mu-search-pending-button"
            value="<?php esc_attr_e( 'Search pending users', 'beyond-multisite' ); ?>" />
        <span class="be-mu-tooltip">
            <span class="be-mu-info">i</span>
            <span class="be-mu-tooltip-text">
                <?php
                    esc_html_e( 'Hint: This will search in the Username and User Email', 'beyond-multisite' );
                ?>
            </span>
        </span>
    </div>
    <?php
}

// Adds a submenu page called Pending under the Users menu in the network admin panel and also loads a css style on that page
function be_mu_add_pending_menu() {
    $pending_page = add_submenu_page(
        'users.php',
        esc_html__( 'Pending Users', 'beyond-multisite' ),
        esc_html__( 'Pending Users', 'beyond-multisite' ),
        'manage_network',
        'be_mu_pending_users',
        'be_mu_pending_users_subpage'
    );

    add_action( 'load-' . $pending_page, 'be_mu_add_beyond_multisite_style' );

    add_action( 'load-' . $pending_page, 'be_mu_add_pending_script' );
}

// Adds the action needed to register the script for the pending users page
function be_mu_add_pending_script() {
    add_action( 'admin_enqueue_scripts', 'be_mu_pending_register_script' );
}

// Registers and localizes the javascript file for the pending users page
function be_mu_pending_register_script() {

    if ( current_user_can( 'manage_network' ) ) {

        if ( isset( $_GET['search'] ) && '' != $_GET['search'] ) {
            $search_string = '&search=' . esc_js( wp_filter_nohtml_kses( $_GET['search'] ) );
        } else {
            $search_string = '';
        }

        // We set the current page number
        if ( isset( $_GET['page_number'] ) ) {
            $page_number = intval( $_GET['page_number'] );
        } else {
            $page_number = 1;
        }

        // Register the script
        wp_register_script( 'be-mu-pending-script', be_mu_plugin_dir_url() . 'scripts/pending-users.js', array(), BEYOND_MULTISITE_VERSION, false );

        // This is the data we will send from the php to the javascript file
        $localize = array(
            'pageURL' => esc_js( esc_url( network_admin_url( 'users.php?page=be_mu_pending_users' ) ) ),
            'searchString' => $search_string,
            'pageNumber' => $page_number,
            'confirmActivate' => esc_js( __( 'Are you sure you want to activate this user?', 'beyond-multisite' ) ),
            'confirmResend' => esc_js( __( 'Are you sure you want to resend the activation email to this user?', 'beyond-multisite' ) ),
            'confirmDelete' => esc_js( __( 'Are you sure you want to delete this user? This action cannot be reversed!', 'beyond-multisite' ) ),
            'invalidAction' => esc_js( __( 'Error: Invalid action.', 'beyond-multisite' ) ),
        );

        // We localize the script - we send php data to the javascript file
        wp_localize_script( 'be-mu-pending-script', 'localizedPendingUsers', $localize );

        //enqueued script with localized data
        wp_enqueue_script( 'be-mu-pending-script', '', array(), false, true );

    }
}

// This function is executed when the Pending subpage is visited and displays the table with the users that are not activated
function be_mu_pending_users_subpage() {

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'beyond-multisite' ) );
    }
    
    ?>
    <div class="wrap">

        <?php

        if ( isset( $_GET['search'] ) ) {
            $get_search = wp_unslash( trim( $_GET['search'] ) );
        }

        // We need these to connect to the database
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        global $wpdb;
        $base_prefix = $wpdb->base_prefix;
        $db_table = $base_prefix . 'signups';

        // This var will hold a message to show after an action, but it will be empty if there are no actions made
        $action_message = '';

        // How much users to show per page
        $per_page = 30;

        // If this is an action request (an action link is clicked)
        if ( isset( $_GET['action'] ) && isset( $_GET['signup_id'] ) ) {

            // This is the id of the signup that we will do the action on
            $signup_id = intval( $_GET['signup_id'] );

            // We set the status of the action to false (error) until success is confirmed
            $status = false;

            // If the action is activate we will activate manually the user signup
            if ( 'activate' == $_GET['action'] ) {

                // We get the activation key for that signup
                $key = $wpdb->get_var( $wpdb->prepare( 'SELECT activation_key FROM ' . $db_table . ' WHERE signup_id = %d', $signup_id ) );

                // If the key is not empty we activate the signup and it there was no error we change the status to 1 (success)
                if ( ! empty( $key ) ) {

                    // This is needed so we can skip the super admin check when a template site is copied with the copy maker module
                    if ( isset( $GLOBALS['be_mu_copy_skip_super_check'] ) ) {
                        $GLOBALS['be_mu_copy_skip_super_check'] = 'yes';
                    }

                    if ( ! is_wp_error( wpmu_activate_signup( $key ) ) ) {
                        $status = true;
                    }
                }
            }

            // If the action is resend, we will send the activation email again
            if ( 'resend' == $_GET['action'] ) {

                // We get all the data for the user signup
                $results = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $db_table . ' WHERE signup_id = %d', $signup_id ), ARRAY_A );

                // If there is data, we proceed (if there is no data then it was either activated or deleted already and we do nothing - status is 0 - error)
                if ( ! empty( $results ) ) {

                    // If the domain is empty then it is only user registration so we send the user notification
                    if ( empty( $results['domain'] ) ) {
                        $status = wpmu_signup_user_notification( $results['user_login'], $results['user_email'], $results['activation_key'],
                            maybe_unserialize( $results['meta'] ) );

                    // If the domain is mot empty then it is user and website registration so we send the blog notification
                    } else {   
                        $status = wpmu_signup_blog_notification( $results['domain'], $results['path'], $results['title'], $results['user_login'],
                            $results['user_email'], $results['activation_key'], maybe_unserialize( $results['meta'] ) );
                    }
                }
            }

            // If the action is delete, we run a delete query
            if ( 'delete' == $_GET['action'] ) {
                $status = $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $db_table . ' WHERE signup_id = %d', $signup_id ) );
                wp_cache_flush();
            }

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
        }

        /**
         * Based on whether this is a search result or not we set 2 string variables - one for the mysql query and one for the url.
         * That we redirect to when the page number is changed with the javascript funciton be_mu_go_to_pending_page.
         */
        if ( isset( $_GET['search'] ) && '' != $_GET['search'] ) {

            // We get the total count of all users that are not activated (we call them pending) based on a search query
            $like = '%' . $wpdb->esc_like( $get_search ) . '%';
            $total_pending_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( signup_id ) FROM " . $db_table . " WHERE active = '0' AND ( user_login LIKE %s "
                . "OR user_email LIKE %s )", $like, $like ) );
        } else {

            // We get the total count of all users that are not activated (we call them pending)
            $total_pending_count = $wpdb->get_var( "SELECT COUNT( signup_id ) FROM " . $db_table . " WHERE active = '0'" );
        }

        // We calculate the number of pages we will shows them in
        $pages_count = ceil( $total_pending_count / $per_page );

        // We set the current page number
        if ( isset( $_GET['page_number'] ) ) {
            $page_number = intval( $_GET['page_number'] );
        } else {
            $page_number = 1;
        }

        // If the pending users do not fit on one page, we will display the page number in the title with this variable
        if ( $total_pending_count > $per_page ) {
            $page_number_heading = ' - ' . esc_html__( 'Page', 'beyond-multisite' ) . ' ' . $page_number . '/' . $pages_count;
        } else {
            $page_number_heading = '';
        }

        // The title of the page
        $page_title = esc_html__( 'Pending Users', 'beyond-multisite' ) . esc_html( $page_number_heading );

        // We output the header of the page
        be_mu_header_super_admin_page( $page_title );

        // Display a message after an action (or empty string if there are no actions made)
        echo $action_message;

        // If this is a search result page, we display a different subtitle based on the number of results
        if ( isset( $_GET['search'] ) && '' != $_GET['search'] ) {

            if ( $total_pending_count > 1 ) {

                echo '<div class="be-mu-white-box be-mu-w100per">';
                echo '<h2 class="be-mu-mtop5 be-mu-mbot5">';
                printf(
                    esc_html__( 'There are %1$d search results for "%2$s". To go back to all pending users %3$sclick here%4$s.', 'beyond-multisite' ),
                    $total_pending_count,
                    esc_html( $get_search ),
                    '<a href="' . esc_url( network_admin_url( 'users.php?page=be_mu_pending_users' ) ) . '">',
                    '</a>'
                );
                echo '</h2>';
                echo '</div>';

            } elseif ( 1 == $total_pending_count ) {

                echo '<div class="be-mu-white-box be-mu-w100per">';
                echo '<h2 class="be-mu-mtop5 be-mu-mbot5">';
                printf(
                    esc_html__( 'There is 1 search result for "%1$s". To go back to all pending users %2$sclick here%3$s.', 'beyond-multisite' ),
                    esc_html( $get_search ),
                    '<a href="' . esc_url( network_admin_url( 'users.php?page=be_mu_pending_users' ) ) . '">',
                    '</a>'
                );
                echo '</h2>';
                echo '</div>';

            } else {

                echo '<div class="be-mu-white-box be-mu-w100per">';
                echo '<h2 class="be-mu-mtop5 be-mu-mbot5">';
                printf(
                    esc_html__( 'There are no search results for "%1$s". To go back to all pending users %2$sclick here%3$s.', 'beyond-multisite' ),
                    esc_html( $get_search ),
                    '<a href="' . esc_url( network_admin_url( 'users.php?page=be_mu_pending_users' ) ) . '">',
                    '</a>'
                );
                echo '</h2>';
                echo '</div>';

            }

        }

        // If there is no data, then there are no pending users
        if ( empty( $total_pending_count ) ) {

            // Different message is shown based on whether this is a search result or not
            if ( isset( $_GET['search'] ) && '' != $_GET['search'] ) {

                // We display the search text field and button
                be_mu_echo_search_pending_field();

                // A message for not finding any results
                echo '<div class="be-mu-white-box be-mu-w100per">'
                    . esc_html__( 'There are no results for that search query.', 'beyond-multisite' )
                    . '</div>';
            } else {
                echo '<div class="be-mu-white-box be-mu-w100per">'
                    . esc_html__( 'There are no pending users. All signups are activated.', 'beyond-multisite' )
                    . '</div>';
            }

        // If there are pending users, we will display them in a table
        } else {

            echo '<div class="be-mu-white-box be-mu-w100per">'
                . esc_html__( 'These users have not yet activated their accounts.', 'beyond-multisite' )
                . '</div>';

            // This is the limit string for the mysql query - it is calculated based on the current page number
            $limit_string = intval( ( $page_number - 1 ) * $per_page ) . ',' . intval( $per_page );

            if ( isset( $_GET['search'] ) && '' != $_GET['search'] ) {

                // Get the pending users based on a search query
                $like = '%' . $wpdb->esc_like( $get_search ) . '%';
                $results_multi_array = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $db_table . " WHERE active = '0' AND ( user_login LIKE %s OR"
                    . " user_email LIKE %s ) ORDER BY registered DESC LIMIT " . $limit_string, $like, $like ), ARRAY_A );

            } else {

                // Get the pending users
                $results_multi_array = $wpdb->get_results( "SELECT * FROM " . $db_table . " WHERE active = '0' ORDER BY registered DESC LIMIT "
                    . $limit_string, ARRAY_A );
            }

            // If this is a page number that has no results we display a message, otherwise we show the table with pending users
            if ( empty( $results_multi_array ) && $page_number > 1 ) {
                echo '<div class="be-mu-white-box be-mu-w100per">'
                . esc_html__( 'There are no results on this page.', 'beyond-multisite' )
                . ' <a href="' . esc_url( network_admin_url( 'users.php?page=be_mu_pending_users' ) ) . '">'
                . esc_html__( 'Go to page 1', 'beyond-multisite' )
                . '</a>.'
                . '</div>';
            } else {

                // We display the search text field and button
                be_mu_echo_search_pending_field();

                echo '<table class="be-mu-table be-mu-mbot15 be-mu-w100per">';

                echo '<thead>'
                    . '<tr>'
                    . '<th>' . esc_html__( 'Username', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'User Email', 'beyond-multisite' ) . '</th>'
                    . '<th class="be-mu-pending-registered-cell">'
                    . esc_html__( 'Registered*', 'beyond-multisite' ) . ' <div class="be-mu-sort-arrow"></div></th>'
                    . '<th>' . esc_html__( 'Registration Type', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'Actions', 'beyond-multisite' ) . '</th>'
                    . '</tr>'
                    . '</thead>';

                echo '<tbody>';

                // We go through all the pending users
                foreach ( $results_multi_array as $results ) {

                    $meta_data = array();
                    if ( array_key_exists( 'meta', $results ) ) {
                        $meta_data = maybe_unserialize( $results['meta'] );
                    }

                    if ( array_key_exists( 'domain', $results ) && empty( $results['domain'] ) && empty( $meta_data ) ) {
                        $registration_type = esc_html__( 'User signup', 'beyond-multisite' );
                    } elseif ( array_key_exists( 'domain', $results ) && ! empty( $results['domain'] ) && ! empty( $meta_data ) ) {
                        $registration_type = esc_html__( 'User and website signup', 'beyond-multisite' );
                    } elseif ( array_key_exists( 'domain', $results ) && empty( $results['domain'] ) && ! empty( $meta_data )
                        && array_key_exists( 'add_to_blog', $meta_data ) && array_key_exists( 'new_role', $meta_data ) ) {
                        $registration_type = sprintf( esc_html__( 'Added to %ssite ID %s%s (role: %s)', 'beyond-multisite' ),
                            '<a href="' . esc_url( network_admin_url( 'site-info.php?id=' . intval( $meta_data['add_to_blog'] ) ) ) . '" target="_blank">',
                            intval( $meta_data['add_to_blog'] ), '</a>', esc_html( $meta_data['new_role'] ) );
                    } else {
                        $registration_type = esc_html__( '[Unknown]', 'beyond-multisite' );
                    }

                    // Now we start showing the actual row with the cells with the user data
                    echo '<tr>';
                    echo '<td data-title="' . esc_attr__( 'Username', 'beyond-multisite' ) . '">' . esc_html( $results['user_login'] ) . '</td>';
                    echo '<td data-title="' . esc_attr__( 'User Email', 'beyond-multisite' ) . '">' . esc_html( $results['user_email'] ) . '</td>';
                    echo '<td data-title="' . esc_attr__( 'Registered*', 'beyond-multisite' ) . '">'
                        . esc_html( be_mu_unixtime_to_wp_datetime( strtotime( $results['registered'] ) ) ) . '</td>';
                    echo '<td data-title="' . esc_attr__( 'Registration Type', 'beyond-multisite' ) . '">' . strip_tags( $registration_type, "<a>" ) . '</td>';
                    echo '<td data-title="' . esc_attr__( 'Actions', 'beyond-multisite' )
                        . '"><a class="be-mu-green-link" href="javascript:pendingUsersActionLink( \'activate\', '.intval($results['signup_id'])
                        . ' )">' .esc_html__( 'Activate', 'beyond-multisite' ) . '</a> <span class="be-mu-gray">|</span> '
                        . '<a href="javascript:pendingUsersActionLink( \'resend\', ' . intval( $results['signup_id'] )
                        . ' )">' .esc_html__( 'Resend Email', 'beyond-multisite' ) . '</a> <span class="be-mu-gray">|</span> '
                        . '<a class="be-mu-red-link" href="javascript:pendingUsersActionLink( \'delete\', ' . intval( $results['signup_id'] )
                        . ' )">' .esc_html__( 'Delete', 'beyond-multisite' ) . '</a>'
                        . '</td>';
                    echo '</tr>';
                }

                echo '</tbody>';

                echo '<tfoot>'
                    . '<tr>'
                    . '<th>' . esc_html__( 'Username', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'User Email', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'Registered*', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'Registration Type', 'beyond-multisite' ) . '</th>'
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

            }

            // If the pending users do not fit on one page we show this dropdown menu to choose a page number to display
            if ( $total_pending_count > $per_page ) {
                echo '<div class="be-mu-white-box be-mu-w100per">'
                    . '<label for="be-mu-pending-page-number">'
                    . esc_html__( 'Go to page:', 'beyond-multisite' )
                    . '</label> '
                    . '<select onchange="pendingUsersGoToPendingPage()" id="be-mu-pending-page-number" name="be-mu-pending-page-number" size="1">';

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
                    echo '&nbsp;<span onclick="pendingUsersNextPreviousPage(' . intval( $page_number - 1 ) . ')" '
                        . 'class="dashicons dashicons-arrow-left-alt2" title="' . esc_attr__( 'Previous Page', 'beyond-multisite' ) . '"></span>';
                }

                if ( $pages_count > $page_number ) {
                    echo '&nbsp;<span onclick="pendingUsersNextPreviousPage(' . intval( $page_number + 1 ) . ')" '
                        . 'class="dashicons dashicons-arrow-right-alt2" title="' . esc_attr__( 'Next Page', 'beyond-multisite' ) . '"></span>';
                }

                echo '</div>';
            }
        }

        ?>

    </div>

    <?php
}
