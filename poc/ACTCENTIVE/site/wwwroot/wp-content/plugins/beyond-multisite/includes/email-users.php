<?php

/**
 * In this file we have all the hooks and functions related to the email users module.
 * This module allows you to send emails to all or some users in the network. The way we process the users makes it possible to target any amount of them.
 * We can also target them by id or role in a given site or sites, and we can target the sites by attribute or id too. So pretty advanced user seletion.
 * The emails are sent in chunks using the WordPress cron feature and you can choose the maximum speeed.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// All the hooks for the email users module will run only if the module is turned on
if ( be_mu_get_setting( 'be-mu-email-status-module' ) == 'on' ) {

    // Adds a submenu page called Email Users under the Users menu in the network admin panel and also loads styles and scripts for that page
    add_action( 'network_admin_menu', 'be_mu_email_add_menu' );

    // Ajax call to a function that sends a test email to a selected user
    add_action( 'wp_ajax_be_mu_email_send_test_email_action', 'be_mu_email_send_test_email_callback' );

    // Ajax call to a function that goes through the users and either schedules an email to be sent or counts how many users would be affected
    add_action( 'wp_ajax_be_mu_email_schedule_action', 'be_mu_email_schedule_action_callback' );

    // Ajax call to a function that shows the results of the current email task
    add_action( 'wp_ajax_be_mu_email_results_action', 'be_mu_email_results_callback' );

    // Ajax call to a function that gets the data for the current email task
    add_action( 'wp_ajax_be_mu_email_task_data_action', 'be_mu_email_task_data_callback' );

    // Ajax call to a function that switches the sending mode for the current email task
    add_action( 'wp_ajax_be_mu_email_sending_mode_action', 'be_mu_email_sending_mode_callback' );

    // Ajax call to a function that cancels or completes an email task, which is the same - deletes the database information and stops the cron job
    add_action( 'wp_ajax_be_mu_email_cancel_or_complete_email_task_action', 'be_mu_email_cancel_or_complete_email_task_callback' );

    // A cron job that goes through the emails sends them and marks them as sent of failed
    add_action( 'be_mu_email_event_hook_send_emails', 'be_mu_email_send_emails_cron' );

    // Registers and enqueues the login style file that hides the login form when the page is used for user unsubscribing actions
    add_action( 'login_enqueue_scripts', 'be_mu_email_register_unsubscribe_login_style' );

    // Using the lost password page it unsubscribes a user when the unsubscribe link is clicked and shows the appropriate message
    add_filter( 'login_message', 'be_mu_email_unsubscribe_login_message' );

    // Adds a filter that changes the title of the unsubscribe page (since we are using the lost password page, we need to change its title)
    add_action( 'setup_theme', 'be_mu_email_unsubscribe_page_title' );

    // Shows the role slugs in the Edit Site Users page
    add_action( 'network_site_users_after_list_table', 'be_mu_show_list_roles' );

    // If the unsubscribe feature is turned on we add a drop-down menu in the user profile settings page that allows the user to unsubscribe from there too
    if ( be_mu_get_setting( 'be-mu-email-unsubscribe-feature' ) == 'on' ) {

        // Adds the drop-down menu in the user profile settings page that allows the user to unsubscribe
        add_action( 'show_user_profile', 'be_mu_email_unsubscribe_profile_field' );
        add_action( 'edit_user_profile', 'be_mu_email_unsubscribe_profile_field' );

        // Saves the data for the drop-down menu in the user profile settings page that allows the user to unsubscribe
        add_action( 'personal_options_update', 'be_mu_email_unsubscribe_save_profile_field' );
        add_action( 'edit_user_profile_update', 'be_mu_email_unsubscribe_save_profile_field' );
    }
}

// A limit for the number of users to process in a single ajax request
define( "BE_MU_EMAIL_LIMIT_USERS", 700 );

// After this many seconds we will try to stop the ajax request when we are done with a certain task (might take more time)
define( "BE_MU_EMAIL_LIMIT_TIME", 10 );

// Adds a submenu page called Email Users under the Users menu in the network admin panel and also loads styles and scripts for that page
function be_mu_email_add_menu() {
    $email_page = add_submenu_page(
        'users.php',
        esc_html__( 'Email Users', 'beyond-multisite' ),
        esc_html__( 'Email Users', 'beyond-multisite' ),
        'manage_network',
        'be_mu_email_users',
        'be_mu_email_users_subpage'
    );
    add_action( 'load-' . $email_page, 'be_mu_add_beyond_multisite_style' );
    add_action( 'load-' . $email_page, 'be_mu_email_add_script' );
}

// Adds the action needed to register the script for the email users page
function be_mu_email_add_script() {
    add_action( 'admin_enqueue_scripts', 'be_mu_email_register_script' );
}

// Registers and localizes the javascript file for the email users page
function be_mu_email_register_script() {

    // Register the script
    wp_register_script( 'be-mu-email-script', be_mu_plugin_dir_url() . 'scripts/email-users.js', array(), BEYOND_MULTISITE_VERSION, false );

    // This is the data we will send from the php to the javascript file
    $localize = array(
        'ajaxNonce' => wp_create_nonce( 'be_mu_email_nonce' ),
        'done' => esc_js( esc_html__( 'Done', 'beyond-multisite' ) ),
        'close' => esc_js( esc_attr__( 'Close', 'beyond-multisite' ) ),
        'affectedUsers' => esc_js( esc_html__( 'Affected Users:', 'beyond-multisite' ) ),
        'exportTitle' => esc_js( esc_html__( 'Export Emails', 'beyond-multisite' ) ),
        'getAbort' => be_mu_email_get_abort(),
        'loadingGIF' => esc_url( be_mu_img_url( 'loading.gif' ) ),
        'pageURL' => esc_js( network_admin_url( 'users.php?page=be_mu_email_users' ) ),
        'processing' => esc_js( esc_html__( 'Processing...', 'beyond-multisite' ) ),
        'errorData' => esc_js( __( 'Error: Invalid form data sent.', 'beyond-multisite' ) ),
        'errorIdsInvalid' => esc_js( __( 'Error: A setting in the user selection form was chosen, that requires a comma-separated list of '
            . 'IDs to be provided, but we did not receive a valid list for it.', 'beyond-multisite' ) ),
        'errorIdsNotNeeded' => esc_js( __( 'Error: A setting in the user selection form was chosen, that ignores the field for the comma-separated list of '
            . 'IDs, but there is data in it.', 'beyond-multisite' ) ),
        'errorAnotherTask' => esc_js( __( 'Error: There is another task in progress. Please reload the page.', 'beyond-multisite' ) ),
        'errorFromEmail' => esc_js( __( 'Error: This is not a valid email to send from.', 'beyond-multisite' ) ),
        'errorToUser' => esc_js( __( 'Error: This is not a valid user to send to. Please enter the username of an existing user.', 'beyond-multisite' ) ),
        'errorMessage' => esc_js( __( 'Error: Could not get the message content. Try reloading the page.', 'beyond-multisite' ) ),
        'errorEmpty' => esc_js( __( 'Error: One or more fields are empty. Please fill all fields.', 'beyond-multisite' ) ),
        'errorSend' => esc_js( __( 'Error: We could not send the email.', 'beyond-multisite' ) ),
        'errorRequest' => esc_js( __( 'Error: There is another request that is still running. Please wait a few seconds and try again. '
            . 'If this problem continues, please reload the page.', 'beyond-multisite' ) ),
        'errorAccess' => esc_js( __( 'Error: You do not have sufficient permissions to make this request.', 'beyond-multisite' ) ),
        'errorResponse' => esc_js( __( 'Error: We got an empty response.', 'beyond-multisite' ) ),
        'errorInvalidNonce' => esc_js( __( 'Error: Invalid security nonce. Please reload the page and try again.', 'beyond-multisite' ) ),
        'confirmStart' => esc_js( __( 'Are you sure you want to send the email to the selected users?', 'beyond-multisite' ) ),
        'processedSoFar' => esc_js( esc_html__( 'Users processed so far:', 'beyond-multisite' ) ),
        'abortTask' => esc_js( __( 'This will abort the current task. Are you sure?', 'beyond-multisite' ) ),
        'suggestReload' => esc_js( __( 'Even though you aborted, there is probably a task already created. '
            . 'You should reload the page to see it.', 'beyond-multisite' ) ),
        'cancelTask' => esc_js( __( 'Cancelling this task will stop it from taking further actions, but every action taken so far will not be undone. '
            . 'Cancelling the task cannot be reversed.', 'beyond-multisite' ) ) . "\n\n"
            . esc_js( __( 'Are you sure you want to cancel the task?', 'beyond-multisite' ) ),
        'completeTask' => esc_js( __( 'This will delete all data about the task from the database.', 'beyond-multisite' ) ) . "\n\n"
            . esc_js( __( 'Are you sure you want to remove the task?', 'beyond-multisite' ) ),
        'errorServerFail' => esc_js( __( 'Error: Unexpected server error. If you have WordPress debugging and logging enabled, '
            . 'you should be able to see more details about the error in the /wp-content/debug.log file.', 'beyond-multisite' ) ),
    );

    // We localize the script - we send php data to the javascript file
    wp_localize_script( 'be-mu-email-script', 'localizedEmail', $localize );

    // Enqueued script with localized data
    wp_enqueue_script( 'be-mu-email-script', '', array(), false, true );
}

// This function is executed when the Email Users subpage is visited
function be_mu_email_users_subpage() {

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'beyond-multisite'  ) );
    }

    $loading_gif_url = be_mu_img_url( 'loading.gif' );

    ?>

    <div class="wrap">

        <?php

        global $wpdb;
        $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
        $db_table_emails = $main_blog_prefix . 'be_mu_email_emails';
        $db_table_email_tasks = $main_blog_prefix . 'be_mu_email_tasks';

        be_mu_header_super_admin_page( __( 'Email Users', 'beyond-multisite' ) );

        if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $db_table_emails . "'" ) !== $db_table_emails
            || $wpdb->get_var( "SHOW TABLES LIKE '" . $db_table_email_tasks . "'" ) !== $db_table_email_tasks ) {
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

            // We try to get the task id of the email tasks (should be one or none)
            $results_multi_array = $wpdb->get_results( "SELECT DISTINCT task_id FROM " . $db_table_emails . " WHERE 1", ARRAY_A );

            ?>

            <div class="be-mu-white-box be-mu-email-box">

                <?php

                // If there is an email task we will show its statistics instead of the form
                if ( ! empty( $results_multi_array ) ) {

                    // Should be only one task
                    foreach ( $results_multi_array as $results ) {
                        $task_id = $results['task_id'];
                    }

                    echo "<div id='be-mu-email-current-task-data'></div>";

                    if ( get_site_option( 'be-mu-email-task-mode-' . $task_id, 'background' ) === "real-time" ) {
                        ?>
                        <script type="text/javascript">
                        var myEmailInterval;
                        var myEmailTimeout;
                        jQuery( function() {
                            emailWorkAndGetTaskData();
                        });
                        </script>
                        <?php
                    } else {
                        ?>
                        <script type="text/javascript">
                        var myEmailInterval;
                        var myEmailTimeout;
                        jQuery( function() {
                            emailGetTaskData();
                            myEmailInterval = setInterval( emailGetTaskData, 60000 );
                        });
                        </script>
                        <?php
                    }

                // There is no email task present at the moment, so we show the Email Users form
                } else {

                ?>

                <form name="be-mu-email-form" id="be-mu-email-form" method="post" action="">

                    <h3>
                        <?php esc_html_e( 'User Selection', 'beyond-multisite' ); ?>
                    </h3>

                    <ul>
                        <li>
                            <label for="be-mu-email-role">
                                <?php esc_html_e( 'Role:', 'beyond-multisite' ); ?>
                            </label>
                            <?php
                            be_mu_select(
                                'be-mu-email-role',
                                array( 'Any or no role', 'Any role', 'Super Admin', 'Administrator', 'Editor', 'Author', 'Contributor',
                                    'Subscriber', 'Any role from a list' ),
                                array(
                                    __( 'Any or no role', 'beyond-multisite' ),
                                    __( 'Any role', 'beyond-multisite' ),
                                    __( 'Super Admin', 'beyond-multisite' ),
                                    __( 'Administrator', 'beyond-multisite' ),
                                    __( 'Editor', 'beyond-multisite' ),
                                    __( 'Author', 'beyond-multisite' ),
                                    __( 'Contributor', 'beyond-multisite' ),
                                    __( 'Subscriber', 'beyond-multisite' ),
                                    __( 'Any role from a list', 'beyond-multisite' ),
                                )
                            );
                            ?>
                            <span class="be-mu-tooltip">
                                <span class="be-mu-info">i</span>
                                <span class="be-mu-tooltip-text">
                                    <?php esc_html_e( 'Hint: Both "Any or no role" and "Super Admin" will ignore the next two fields.', 'beyond-multisite' ); ?>
                                </span>
                            </span>
                        </li>
                        <li id="be-mu-email-list-roles-show" class="be-mu-display-none">
                            <label for="be-mu-email-roles-list">
                                <?php esc_html_e( 'Role slugs (comma-separated)', 'beyond-multisite' ); ?>
                            </label>
                            <?php
                            be_mu_input_text( 'be-mu-email-roles-list' );
                            ?>
                            <span class="be-mu-tooltip">
                                <span class="be-mu-info">i</span>
                                <span class="be-mu-tooltip-text">
                                    <?php esc_html_e( 'Users with at least one of the provided roles will be affected. You can include custom '
                                    . 'roles too. Make sure you are writing the role slugs, not the role names! The role slugs are lower case '
                                    . 'and have no spaces, but they can also be different from the role name in other ways, so do not assume '
                                    . 'they are always a lower case version of the name. To see the slug you can go to edit the site that has '
                                    . 'the role you want and click on the Users tab. We have put a list of the roles with the slugs there.',
                                    'beyond-multisite' ); ?>
                                </span>
                            </span>
                        </li>
                        <li>
                            <label for="be-mu-email-role-sites-attribute">
                                <?php esc_html_e( 'Role in sites by attribute:', 'beyond-multisite' ); ?>
                            </label>
                            <?php
                            be_mu_select(
                                'be-mu-email-role-sites-attribute',
                                array( 'Any', 'Public', 'Deleted', 'Spam', 'Archived', 'Mature', 'Not public', 'Not deleted', 'Not spam',
                                    'Not archived', 'Not mature', 'Not deleted or spam' ),
                                array(
                                    __( 'Any', 'beyond-multisite' ),
                                    __( 'Public', 'beyond-multisite' ),
                                    __( 'Deleted', 'beyond-multisite' ),
                                    __( 'Spam', 'beyond-multisite' ),
                                    __( 'Archived', 'beyond-multisite' ),
                                    __( 'Mature', 'beyond-multisite' ),
                                    __( 'Not public', 'beyond-multisite' ),
                                    __( 'Not deleted', 'beyond-multisite' ),
                                    __( 'Not spam', 'beyond-multisite' ),
                                    __( 'Not archived', 'beyond-multisite' ),
                                    __( 'Not mature', 'beyond-multisite' ),
                                    __( 'Not deleted or spam', 'beyond-multisite' ),
                                )
                            );
                            ?>
                        </li>
                        <li>
                            <label for="be-mu-email-role-sites-id-option">
                                <?php esc_html_e( 'Role in sites by ID:', 'beyond-multisite' ); ?>
                            </label>
                            <?php
                            be_mu_select(
                                'be-mu-email-role-sites-id-option',
                                array( 'Any site ID', 'Only these site IDs:', 'All except these site IDs:' ),
                                array(
                                    __( 'Any site ID (All sites)', 'beyond-multisite' ),
                                    __( 'Only these site IDs:', 'beyond-multisite' ),
                                    __( 'All except these site IDs:', 'beyond-multisite' ),
                                )
                            );
                            echo '&nbsp;';
                            be_mu_input_text( 'be-mu-email-role-sites-ids' );
                            ?>
                            <span class="be-mu-tooltip">
                                <span class="be-mu-info">i</span>
                                <span class="be-mu-tooltip-text">
                                    <?php esc_html_e( 'Accepts: Comma-separated numbers or an empty string.', 'beyond-multisite' ); ?>
                                </span>
                            </span>
                        </li>
                        <li>
                            <label for="be-mu-email-users-id-option">
                                <?php esc_html_e( 'Select users with:', 'beyond-multisite' ); ?>
                            </label>
                            <?php
                            be_mu_select(
                                'be-mu-email-users-id-option',
                                array( 'Any user ID', 'Only these user IDs:', 'All except these user IDs:' ),
                                array(
                                    __( 'Any user ID (All users)', 'beyond-multisite' ),
                                    __( 'Only these user IDs:', 'beyond-multisite' ),
                                    __( 'All except these user IDs:', 'beyond-multisite' ),
                                )
                            );
                            echo '&nbsp;';
                            be_mu_input_text( 'be-mu-email-users-ids' );
                            ?>
                            <span class="be-mu-tooltip">
                                <span class="be-mu-info">i</span>
                                <span class="be-mu-tooltip-text">
                                    <?php esc_html_e( 'Accepts: Comma-separated numbers or an empty string.', 'beyond-multisite' ); ?>
                                </span>
                            </span>
                        </li>
                        <li>
                            <label for="be-mu-email-ban-status">
                                <?php esc_html_e( 'Ban status:', 'beyond-multisite' ); ?>
                            </label>
                            <?php
                            be_mu_select(
                                'be-mu-email-ban-status',
                                array( 'Not banned', 'Banned', 'Any' ),
                                array(
                                    __( 'Not banned', 'beyond-multisite' ),
                                    __( 'Banned', 'beyond-multisite' ),
                                    __( 'Any', 'beyond-multisite' ),
                                )
                            );
                            ?>
                            <i class="be-mu-hint">
                                <?php
                                if ( be_mu_get_setting( 'be-mu-ban-status-module' ) == 'off' ) {
                                    esc_html_e( 'The Ban Users module is turned off. All users are treated as not banned.', 'beyond-multisite' );
                                }
                                ?>
                            </i>
                        </li>
                        <li>
                            <label for="be-mu-email-spam-status">
                                <?php esc_html_e( 'Spam status:', 'beyond-multisite' ); ?>
                            </label>
                            <?php
                            be_mu_select(
                                'be-mu-email-spam-status',
                                array( 'Not spammed', 'Spammed', 'Any' ),
                                array(
                                    __( 'Not spammed', 'beyond-multisite' ),
                                    __( 'Spammed', 'beyond-multisite' ),
                                    __( 'Any', 'beyond-multisite' ),
                                )
                            );
                            ?>
                        </li>
                        <li>
                            <label for="be-mu-email-unsubscribe-status">
                                <?php esc_html_e( 'Unsubscribe status:', 'beyond-multisite' ); ?>
                            </label>
                            <?php
                            be_mu_select(
                                'be-mu-email-unsubscribe-status',
                                array( 'Not unsubscribed', 'Unsubscribed', 'Any' ),
                                array(
                                    __( 'Not unsubscribed', 'beyond-multisite' ),
                                    __( 'Unsubscribed', 'beyond-multisite' ),
                                    __( 'Any', 'beyond-multisite' ),
                                )
                            );
                            ?>
                            <i class="be-mu-hint">
                                <?php
                                if ( be_mu_get_setting( 'be-mu-email-unsubscribe-feature' ) == 'off' ) {
                                    esc_html_e( 'The unsubscribe feature is turned off. All users are treated as not unsubscribed.', 'beyond-multisite' );
                                }
                                ?>
                            </i>
                        </li>
                    </ul>

                    <h3>
                        <?php esc_html_e( 'Email Message', 'beyond-multisite' ); ?>
                    </h3>

                    <ul>
                        <li>
                            <label for="be-mu-email-from-email">
                                <?php esc_html_e( 'From email:', 'beyond-multisite' ); ?>
                            </label>
                            <?php be_mu_input_text( 'be-mu-email-from-email' ); ?>
                        </li>
                        <li>
                            <label for="be-mu-email-from-name">
                                <?php esc_html_e( 'From name:', 'beyond-multisite' ); ?>
                            </label>
                            <?php be_mu_input_text( 'be-mu-email-from-name' ); ?>
                        </li>
                        <li>
                            <label for="be-mu-email-subject">
                                <?php esc_html_e( 'Subject:', 'beyond-multisite' ); ?>
                            </label>
                            <?php be_mu_input_text( 'be-mu-email-subject' ); ?>
                        </li>
                        <li class="be-mu-wp-editor-list-item">
                            <label for="be-mu-email-message">
                                <?php esc_html_e( 'Message:', 'beyond-multisite' ); ?>
                            </label>
                            <?php be_mu_wp_editor( 'be-mu-email-message' ); ?>
                        </li>
                    </ul>
                    <p>
                        <?php esc_html_e( 'Shortcodes for the Message field:', 'beyond-multisite' ); ?><br />
                        [user_smart_name] -
                        <?php
                            esc_html_e( 'If the first and last names are set, this shows the full name. If only the first name is set, '
                                . 'it shows it. If the first name is empty, it shows the username.', 'beyond-multisite' );
                        ?><br />
                        [user_display_name] -
                        <?php esc_html_e( 'The display name of the user.', 'beyond-multisite' ); ?><br />
                        [user_username] -
                        <?php esc_html_e( 'The username of the user.', 'beyond-multisite' ); ?><br />
                        [user_first_name] -
                        <?php esc_html_e( 'The first name of the user. Could be empty.', 'beyond-multisite' ); ?><br />
                        [user_last_name] -
                        <?php esc_html_e( 'The last name of the user. Could be empty.', 'beyond-multisite' ); ?><br />
                        [user_admin_sites] -
                        <?php esc_html_e( 'A list of links to the sites where the user has an administrator role. '
                            . 'If there are no such sites, an empty string is shown.', 'beyond-multisite' ); ?><br />
                        [user_admin_sites_only_selected_sites] -
                        <?php esc_html_e( 'A list of links to the sites where the user has an administrator role, but only the sites that '
                            . 'are also selected in the "User Selection" form by the field "Role in sites by ID" (the field "Role in sites by attribute" is '
                            . 'ignored for this list, but still used for user selection). '
                            . 'If there are no such sites, an empty string is shown.', 'beyond-multisite' ); ?><br />
                        [user_admin_site_title] -
                        <?php esc_html_e( 'The title of the site, where the user has an administrator role. If there are multiple such sites, '
                            . 'then the one with lower site ID is chosen. If there are no such sites, an empty string is shown.', 'beyond-multisite' ); ?><br />
                        [user_admin_site_url] -
                        <?php esc_html_e( 'The URL of the site, where the user has an administrator role. If there are multiple such sites, '
                            . 'then the one with lower site ID is chosen. If there are no such sites, an empty string is shown.', 'beyond-multisite' ); ?><br />
                        [network_site_url] -
                        <?php esc_html_e( 'The URL of the main network site.', 'beyond-multisite' ); ?><br />
                    </p>
                    <p class="be-mu-mtop20 be-mu-mbot20">
                        <input class="button" type="button" onclick="emailStart( 'preview' )"
                            value="<?php esc_attr_e( 'Preview User Selection', 'beyond-multisite' ); ?>" />

                        <input class="button" type="button" onclick="emailStart( 'export' )"
                            value="<?php esc_attr_e( 'Export Emails', 'beyond-multisite' ); ?>" />

                        <input class="button button-primary" type="button" onclick="emailStart( 'send' )"
                            value="<?php esc_attr_e( 'Send Email', 'beyond-multisite' ); ?>" />
                    </p>

                    <h3>
                        <?php esc_html_e( 'Test Email', 'beyond-multisite' ); ?>
                    </h3>

                    <ul>
                        <li>
                            <label for="be-mu-email-test-user">
                                <?php esc_html_e( 'Send to this user:', 'beyond-multisite' ); ?>
                            </label>
                            <?php be_mu_input_text( 'be-mu-email-test-user' ); ?>
                            <span class="be-mu-tooltip">
                                <span class="be-mu-info">i</span>
                                <span class="be-mu-tooltip-text">
                                    <?php
                                    esc_html_e( 'Hint: Enter the username of the user, to which you want to send the test email.', 'beyond-multisite' );
                                    ?>
                                </span>
                            </span>
                        </li>
                    </ul>
                    <p>
                        <input class="button" type="button" onclick="emailSendTestEmail()"
                            value="<?php esc_attr_e( 'Send Test Email', 'beyond-multisite' ); ?>" />
                        <span id="be-mu-email-test-email-done-span" class="be-mu-green"></span>
                        <img id="be-mu-loading-email-test-email" src="<?php echo esc_url( $loading_gif_url ); ?>" />
                    </p>
                </form>


                <?php

                // End of the else that says that there is not an email task running at the moment
                }

                ?>


            </div>

            <?php

        }

        ?>

    </div>

    <!-- these layers will contain the results of the ajax requests - the list of users that will receive the email -->
    <div id="be-mu-email-container" class="be-mu-div-contain-results">
        <table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
            <tr>
                <td valign="middle">
                    <div id="be-mu-email-div-results">

                    </div>
                </td>
            </tr>
        </table>
    </div>

    <?php

}

// Ajax call to a function that switches the sending mode for the current email task
function be_mu_email_sending_mode_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_email_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    $task_id = sanitize_html_class( $_POST['task_id'] );
    $sending_mode = sanitize_html_class( $_POST['sending_mode'] );
    update_site_option( 'be-mu-email-task-mode-' . $task_id, $sending_mode );

    if ( 'background' === $sending_mode && ! wp_next_scheduled( 'be_mu_email_event_hook_send_emails' ) ) {
        wp_schedule_event( time(), 'be_mu_every_15_sec', 'be_mu_email_event_hook_send_emails' );
    }

    if ( 'real-time' === $sending_mode && wp_next_scheduled( 'be_mu_email_event_hook_send_emails' ) ) {
        wp_clear_scheduled_hook( 'be_mu_email_event_hook_send_emails' );
    }

    wp_die( 'done' );
}

// Ajax call to a function that gets the data for the current email task
function be_mu_email_task_data_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_email_nonce', 'security', false ) ) {
        wp_die( esc_html__( 'Error: You do not have sufficient permissions to make this request.', 'beyond-multisite' ) . "<!-- be_mu_email_task_error -->" );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( esc_html__( 'Error: Invalid security nonce. Please reload the page and try again.', 'beyond-multisite' ) . "<!-- be_mu_email_task_error -->" );
    }

    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
    $db_table_emails = $main_blog_prefix . 'be_mu_email_emails';
    $db_table_email_tasks = $main_blog_prefix . 'be_mu_email_tasks';

    $work = $_POST['work'];

    if ( "yes" === $work ) {
        be_mu_email_send_emails_cron();
    }

    // We try to get the task id of the email tasks (should be one or none)
    $results_multi_array = $wpdb->get_results( "SELECT DISTINCT task_id FROM " . $db_table_emails . " WHERE 1", ARRAY_A );

    // Should be only one task
    foreach ( $results_multi_array as $results) {

        // Set the task id
        $task_id = $results['task_id'];

        // In the next several lines we are just getting some statistics about the task from the emails table
        $total_emails_count = intval( $wpdb->get_var( $wpdb->prepare( "SELECT count( row_id ) FROM " . $db_table_emails
            . " WHERE task_id = %s", $task_id ) ) );
        $sending_emails_count = intval( $wpdb->get_var( $wpdb->prepare( "SELECT count( row_id ) FROM " . $db_table_emails
            . " WHERE task_id = %s AND status = 'working'", $task_id ) ) );
        $sent_emails_count = intval( $wpdb->get_var( $wpdb->prepare( "SELECT count( row_id ) FROM " . $db_table_emails
            . " WHERE task_id = %s AND status = 'sent'", $task_id ) ) );
        $failed_emails_count = intval( $wpdb->get_var( $wpdb->prepare( "SELECT count( row_id ) FROM " . $db_table_emails
            . " WHERE task_id = %s AND status = 'failed'", $task_id ) ) );

        // Based on whether all scheduled emails are processed or not we set some variables
        if ( $total_emails_count == ( $sent_emails_count + $failed_emails_count ) ) {

            // Set the mode of the button we will show to cancel or complete the task
            $button_mode = 'complete';

            // Set the value of the button we will show
            $button_value = __( 'Remove Task', 'beyond-multisite' );

            // Set the var for the button-primary class of the button
            $primary_class = 'button-primary';

            // The status of the task
            $task_status = __( 'Done', 'beyond-multisite' );

            // The message we show for the current task
            $task_message = __( 'There is a finished email task.', 'beyond-multisite' );

            $task_done = 'yes';

            echo "<!-- be_mu_email_task_completed -->";

        } else {

            // Set the mode of the button we will show to cancel or complete the task
            $button_mode = 'cancel';

            // Set the value of the button we will show
            $button_value = __( 'Cancel Task', 'beyond-multisite' );

            // Set the var for the button-primary class of the button
            $primary_class = '';

            // The status of the task
            $task_status = __( 'Active', 'beyond-multisite' );

            // The message we show for the current task
            $task_message = __( 'There is an active email task.', 'beyond-multisite' );

            $task_done = 'no';
        }

        ?>

        <h3>
            <?php echo esc_html( $task_message ); ?>
        </h3>
        <p>
            <b>
                <?php esc_html_e( 'Task statistics', 'beyond-multisite' ); ?>
            </b><br>
            <?php
            if ( "no" === $task_done ) {
                printf( esc_html__( '(updates automatically, last updated: %s)', 'beyond-multisite' ), be_mu_unixtime_to_wp_datetime( time() ) );
            }
            ?>
        </p>

        <table class="be-mu-email-task-stats-table">
            <tr class="be-mu-tr1">
                <td class="be-mu-col1">
                    <?php esc_html_e( 'Total emails to send:', 'beyond-multisite' ); ?>
                </td>
                <td class="be-mu-col2">
                    <?php echo intval( $total_emails_count ); ?>
                </td>
                <td class="be-mu-col3">
                    <?php esc_html_e( 'Task ID:', 'beyond-multisite' ); ?>
                </td>
                <td class="be-mu-col4">
                    <?php echo esc_html( $task_id ); ?>
                </td>
            </tr>
            <tr class="be-mu-tr2">
                <td class="be-mu-col1">
                    <?php esc_html_e( 'Sending emails:', 'beyond-multisite' ); ?>
                </td>
                <td class="be-mu-col2">
                    <?php echo intval( $sending_emails_count ); ?>
                </td>
                <td class="be-mu-col3">
                    <?php esc_html_e( 'Task status:', 'beyond-multisite' ); ?>
                </td>
                <td class="be-mu-col4">
                    <?php echo esc_html( $task_status ); ?>
                </td>
            </tr>
            <tr class="be-mu-tr1">
                <td class="be-mu-col1">
                    <?php esc_html_e( 'Sent emails:', 'beyond-multisite' ); ?>
                </td>
                <td class="be-mu-col2">
                    <?php echo intval( $sent_emails_count ); ?>
                </td>
                <td class="be-mu-col3">
                    <?php esc_html_e( 'Sending mode:', 'beyond-multisite' ); ?>
                </td>
                <td class="be-mu-col4">
                    <?php
                    if ( get_site_option( 'be-mu-email-task-mode-' . $task_id, 'background' ) === "real-time" ) {
                        esc_html_e( 'Real-time', 'beyond-multisite' );
                    } else {
                        esc_html_e( 'Background', 'beyond-multisite' );
                    }
                    ?>
                </td>
            </tr>
            <tr class="be-mu-tr2">
                <td class="be-mu-col1">
                    <?php esc_html_e( 'Failed emails:', 'beyond-multisite' ); ?>
                </td>
                <td class="be-mu-col2">
                    <?php echo intval( $failed_emails_count ); ?>
                </td>
                <td class="be-mu-col3">
                    <?php esc_html_e( 'Cron job:', 'beyond-multisite' ); ?>
                </td>
                <td class="be-mu-col4">
                    <?php
                    if ( wp_next_scheduled( 'be_mu_email_event_hook_send_emails' ) ) {
                        echo esc_html__( 'Active', 'beyond-multisite' );
                    } else {
                        if ( get_site_option( 'be-mu-email-task-mode-' . $task_id, 'background' ) === "real-time" ) {
                            echo "<span>" . esc_html__( 'Not needed', 'beyond-multisite' ) . "</span>";
                        } else {
                            echo "<span class='be-mu-red'>" . esc_html__( 'Not found', 'beyond-multisite' ) . "</span>";
                        }
                    }
                    ?>
                </td>
            </tr>
            <tr class="be-mu-tr1">
                <td class="be-mu-col1">
                    <?php esc_html_e( 'Sending speed:', 'beyond-multisite' ); ?>
                </td>
                <td class="be-mu-col2">
                    <?php
                    echo esc_html( sprintf( __( '%d per hour', 'beyond-multisite' ),
                        intval( be_mu_strip_non_digit( be_mu_get_setting( 'be-mu-email-speed' ) ) ) ) );
                    ?>
                </td>
                <td class="be-mu-col3">
                    &nbsp;
                </td>
                <td class="be-mu-col4">
                    &nbsp;
                </td>
            </tr>
        </table>

        <!-- This is the mobile version of the previous table -->
        <table class="be-mu-email-task-stats-table-mobile">
            <tr class="be-mu-tr1">
                <td class="be-mu-col1">
                    <?php esc_html_e( 'Total emails to send:', 'beyond-multisite' ); ?>
                </td>
                <td class="be-mu-col2">
                    <?php echo intval( $total_emails_count ); ?>
                </td>
            </tr>
            <tr class="be-mu-tr2">
                <td class="be-mu-col1">
                    <?php esc_html_e( 'Sending emails:', 'beyond-multisite' ); ?>
                </td>
                <td class="be-mu-col2">
                    <?php echo intval( $sending_emails_count ); ?>
                </td>
            </tr>
            <tr class="be-mu-tr1">
                <td class="be-mu-col1">
                    <?php esc_html_e( 'Sent emails:', 'beyond-multisite' ); ?>
                </td>
                <td class="be-mu-col2">
                    <?php echo intval( $sent_emails_count ); ?>
                </td>
            </tr>
            <tr class="be-mu-tr2">
                <td class="be-mu-col1">
                    <?php esc_html_e( 'Failed emails:', 'beyond-multisite' ); ?>
                </td>
                <td class="be-mu-col2">
                    <?php echo intval( $failed_emails_count ); ?>
                </td>
            </tr>
            <tr class="be-mu-tr1">
                <td class="be-mu-col1">
                    <?php esc_html_e( 'Sending speed:', 'beyond-multisite' ); ?>
                </td>
                <td class="be-mu-col2">
                    <?php
                    echo esc_html( sprintf( __( '%d per hour', 'beyond-multisite' ),
                        intval( be_mu_strip_non_digit( be_mu_get_setting( 'be-mu-email-speed' ) ) ) ) );
                    ?>
                </td>
            </tr>
            <tr class="be-mu-tr2">
                <td class="be-mu-col1">
                    <?php esc_html_e( 'Task ID:', 'beyond-multisite' ); ?>
                </td>
                <td class="be-mu-col2">
                    <?php echo esc_html( $task_id ); ?>
                </td>
            </tr>
            <tr class="be-mu-tr1">
                <td class="be-mu-col1">
                    <?php esc_html_e( 'Task status:', 'beyond-multisite' ); ?>
                </td>
                <td class="be-mu-col2">
                    <?php echo esc_html( $task_status ); ?>
                </td>
            </tr>
            <tr class="be-mu-tr2">
                <td class="be-mu-col1">
                    <?php esc_html_e( 'Sending mode:', 'beyond-multisite' ); ?>
                </td>
                <td class="be-mu-col2">
                    <?php
                    if ( get_site_option( 'be-mu-email-task-mode-' . $task_id, 'background' ) === "real-time" ) {
                        esc_html_e( 'Real-time', 'beyond-multisite' );
                    } else {
                        esc_html_e( 'Background', 'beyond-multisite' );
                    }
                    ?>
                </td>
            </tr>
            <tr class="be-mu-tr1">
                <td class="be-mu-col1">
                    <?php esc_html_e( 'Cron job:', 'beyond-multisite' ); ?>
                </td>
                <td class="be-mu-col2">
                    <?php
                    if ( wp_next_scheduled( 'be_mu_email_event_hook_send_emails' ) ) {
                        echo esc_html__( 'Active', 'beyond-multisite' );
                    } else {
                        if ( get_site_option( 'be-mu-email-task-mode-' . $task_id, 'background' ) === "real-time" ) {
                            echo "<span>" . esc_html__( 'Not needed', 'beyond-multisite' ) . "</span>";
                        } else {
                            echo "<span class='be-mu-red'>" . esc_html__( 'Not found', 'beyond-multisite' ) . "</span>";
                        }
                    }
                    ?>
                </td>
            </tr>
        </table>

        <?php

        // This is the cancel or complete task button along with the loading gif
        echo '<p><input class="button ' . esc_attr( $primary_class ) . '" onclick="emailCancelOrCompleteEmailTask( \''
                . esc_js( esc_attr( $task_id ) ) . '\', \'' . esc_js( esc_attr( $button_mode ) )
                . '\' )" type="button" value="' . esc_attr( $button_value )
                . '" />&nbsp;<img id="be-mu-email-loading-cancel-email-task" src="'
                . esc_url( be_mu_img_url( 'loading.gif' ) ) . '" /></p>';

        if ( "no" === $task_done ) {
            if ( get_site_option( 'be-mu-email-task-mode-' . $task_id, 'background' ) === "real-time" ) {
                echo '<p>'
                    . '<a href="javascript:emailSwitchSendingMode(\'background\', \'' . esc_attr( sanitize_html_class( $task_id ) ) . '\')"><b>'
                    . esc_html__( 'Switch to Background sending mode', 'beyond-multisite' )
                    . '</b></a>&nbsp;';
            } else {
                echo '<p>'
                    . '<a href="javascript:emailSwitchSendingMode(\'real-time\', \'' . esc_attr( sanitize_html_class( $task_id ) ) . '\')"><b>'
                    . esc_html__( 'Switch to Real-time sending mode', 'beyond-multisite' )
                    . '</b></a>&nbsp;';
            }

            echo '<span class="be-mu-tooltip">'
                . '<span class="be-mu-info">i</span>'
                . '<span class="be-mu-tooltip-text">'
                . esc_html__( 'There are two sending modes. The default background mode uses cron jobs to do the work in the background. '
                    . 'You do not have to stay on this page, and the work is done during a page load. This can also slow down the page load for users. '
                    . 'It is also possible to not be able to achieve the set sending speed if you do not have a lot of page loads. '
                    . 'The other mode is real-time. Real-time mode only does work while you keep this page open and does not use any cron jobs. '
                    . 'It does not slow the page load and it always achieves the set sending speed (unless your '
                    . 'server sending limit is reached or the server connection is too slow).', 'beyond-multisite' )
                . '</span>'
                . '</span>'
                . '</p>';

            if ( get_site_option( 'be-mu-email-task-mode-' . $task_id, 'background' ) === "real-time" ) {
                echo "<h3 class='be-mu-blue'>" . esc_html__( 'Please keep this page open.', 'beyond-multisite' ) . "</h3>";
                echo "<p>" . esc_html__( 'This task in currently in real-time sending mode. Please keep this page open. '
                    . 'If you close it, the task will pause working and will continue the next time you open this page.', 'beyond-multisite' ) . "</p>";
            } else {
                echo "<h3 class='be-mu-green'>" . esc_html__( 'You are free to leave this page.', 'beyond-multisite' ) . "</h3>";
                echo "<p>" . esc_html__( 'This task in currently in background sending mode. You are free to leave this page, and it will continue working.',
                    'beyond-multisite' ) . "</p>";
            }
        }

    }
    wp_die();
}

/**
 * This function goes through the users and performs the needed action, it either schedules an email to be sent or counts how many would be affected.
 * It is called by an ajax request and could run multiple times in order to go through all the users.
 * It outputs a json-encoded array of numbers that we use to know how many users are done so far in the current request, how many were affected,
 * whether a limit was reached (limit of time or number of users), or what offest we used when calling the get_users function.
 */
function be_mu_email_schedule_action_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_email_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    $export_emails = Array();

    // The count of the affected users on the current request
    $request_affected_users_count = 0;

    // The count of the processed users on the current request
    $request_processed_users_count = 0;

    // If it is set to 1 it means that a time or user limit is reached for the current request
    $is_request_limit_reached = 0;

    // Comma-separated user ids of users affected by the current request, we will add this to the database
    $affected_users_string = '';

    // Get the current microtime
    $time1 = microtime( true );

    // The task id string that helps us find the data for this task in the database
    $task_id = wp_filter_nohtml_kses( $_POST['task_id'] );

    // The mode of the task. If it is "send", we are scheduling emails to be sent. If it is "preview", we are previewing the user selection.
    $mode = $_POST['mode'];

    // These variables hold the selected settings for the current task
    $role = $_POST['role'];
    $role_sites_attribute = $_POST['role_sites_attribute'];
    $role_sites_id_option = $_POST['role_sites_id_option'];
    $role_sites_ids = be_mu_strip_whitespace( $_POST['role_sites_ids'] );
    $users_id_option = $_POST['users_id_option'];
    $users_ids = be_mu_strip_whitespace( $_POST['users_ids'] );
    $list_roles = be_mu_strip_whitespace( strtolower( $_POST['list_roles'] ) );
    $ban_status = $_POST['ban_status'];
    $spam_status = $_POST['spam_status'];
    $unsubscribe_status = $_POST['unsubscribe_status'];

    // How many users to skip when we process them (because we might need to process them in chunks if can't finish them all at once)
    $offset = intval( $_POST['offset'] );

    // If the settings are not valid we stop and show an error code
    if ( ! be_mu_email_user_vars_valid( $role, $role_sites_attribute, $role_sites_id_option, $users_id_option, $ban_status, $spam_status,
        $unsubscribe_status, $list_roles ) ) {
        wp_die( 'invalid-data' );
    }

    if ( 'Any role from a list' === $role ) {
        if ( empty( $list_roles ) ) {
            wp_die( 'invalid-data' );
        }
        $list_roles_array = explode( ',', $list_roles );
    }

    // If a setting that requires a comma-separated list of ids is chosen and there is no such valid list provided, we show an error code and stop
    if ( ( 'Any user ID' !== $users_id_option && ! be_mu_is_comma_separated_numbers( $users_ids ) )
        || ( 'Any site ID' !== $role_sites_id_option && ! be_mu_is_comma_separated_numbers( $role_sites_ids ) ) ) {
        wp_die( 'ids-invalid' );
    }

    // If a setting that ignores the comma-separated list of ids is chosen but the field is filled, we show an error code and stop
    if ( ( 'Any user ID' === $users_id_option && '' !== $users_ids ) || ( 'Any site ID' === $role_sites_id_option && '' !== $role_sites_ids ) ) {
        wp_die( 'ids-not-needed' );
    }

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // The database table for the emails for this module
    $db_table_emails = $main_blog_prefix . 'be_mu_email_emails';

    $emails_multi_array = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $db_table_emails . " WHERE task_id != %s LIMIT 1", $task_id ), ARRAY_A );

    // There is another email task so we cannot start a new one
    if ( ! empty( $emails_multi_array ) ) {
        wp_die( 'another-task' );
    }

    // We also get the data for the email to send if the mode is "send"
    if ( 'send' === $mode ) {
        $from_email = $_POST['from_email'];
        $from_name = wp_filter_nohtml_kses( $_POST['from_name'] );
        $from_name = wp_encode_emoji( $from_name );
        $subject = wp_filter_nohtml_kses( $_POST['subject'] );
        $subject = wp_encode_emoji( $subject );
        $message = stripslashes( $_POST['message'] );
        $message = wpautop( $message );
        $message = wp_encode_emoji( $message );

        // Validates the email message fields. Stops the script and shows an error if invalid data is found.
        be_mu_email_message_vars_validate( $from_email, $from_name, $subject, $message );
    }

    // If any or no role is chosen, role in which sites does not matter
    if ( 'Any or no role' === $role ) {

        // Based on the settings for the current task and the offset for the current request we create the arguments for get_users()
        $get_users_arguments = be_mu_email_build_get_users_arguments( $users_id_option, $users_ids, $offset );

        // We get the users
        $users = get_users( $get_users_arguments );

        // We go through the users
        foreach ( $users as $user_object ) {

            // The id of the current user in the foreach cycle
            $user_id = intval( $user_object->ID );

            // Based on the selected settings for the current task we could skip some users
            if ( be_mu_email_any_role_determine_user_skip( $user_id, $ban_status, $spam_status, $unsubscribe_status ) ) {

                // Since we are skipping the user, it is processed, so we increase the counter for processed users
                $request_processed_users_count++;

                /**
                 * If more than BE_MU_EMAIL_LIMIT_TIME seconds have passed or we processed more than BE_MU_EMAIL_LIMIT_USERS users
                 * we stop and we set $is_request_limit_reached to 1 so we know that a limit stopped us and we are not done.
                 */
                if ( ( microtime( true ) - $time1 ) > BE_MU_EMAIL_LIMIT_TIME
                    || $request_processed_users_count == BE_MU_EMAIL_LIMIT_USERS ) {
                    $is_request_limit_reached = 1;
                    break;
                }

                // Skip this user and go to the next one in the cycle
                continue;
            }

            // If the mode is "send" we also schedule the email
            if ( 'send' === $mode ) {

                $to_email = $user_object->user_email;

                // We apply the shortcodes to the email message text
                $message_with_shortcodes = be_mu_email_apply_message_shortcodes( $message, $user_id, $role_sites_id_option, $role_sites_ids );

                // If the user does not have a user token assigned, we assign a new random one and add it to the user global data.
                be_mu_assign_user_token_if_not_exist( $user_id );

                // Adds the unsubscribe footer message to the email message if needed
                $message_with_footer = be_mu_email_add_unsubscribe_footer( $message_with_shortcodes, $user_id );

                // Schedule the email to be sent
                be_mu_email_schedule_email( $task_id, $from_name, $from_email, $to_email, $subject, $message_with_footer );
            }

            if ( 'export' === $mode ) {
                $export_emails[] = $user_object->user_email;
            }

            // At this point the current user is processed, so we increase the counter of processed users
            $request_processed_users_count++;

            // Current user is affected
            $request_affected_users_count++;

            // We add the user id to the comma-separated string for the database
            $affected_users_string .= $user_id . ",";

            /**
             * If more than BE_MU_EMAIL_LIMIT_TIME seconds have passed or we processed more than BE_MU_EMAIL_LIMIT_USERS users
             * we stop and we set $is_request_limit_reached to 1 so we know that a limit stopped us and we are not done.
             */
            if ( ( microtime( true ) - $time1 ) > BE_MU_EMAIL_LIMIT_TIME
                || $request_processed_users_count == BE_MU_EMAIL_LIMIT_USERS ) {
                $is_request_limit_reached = 1;
                break;
            }

        }

    // If "Super Admin" role is chosen the limits do not apply here. We always process them in one request.
    } elseif ( 'Super Admin' === $role ) {

        // Get all usernames of all Super Admins
        $super_admin_logins = get_super_admins();

        // We go through all the Super Admins
        foreach ( $super_admin_logins as $super_admin_login ) {

            // Get the WP_User object of the current Super Admin
            $super_admin_object = get_user_by( 'login', $super_admin_login );

            // Get the ID of the current Super Admin
            $super_admin_id = intval( $super_admin_object->ID );

            // Based on the selected settings for the current task we could skip some users
            if ( be_mu_email_determine_super_admin_skip_by_id( $super_admin_id, $users_id_option, $users_ids )
                || be_mu_email_any_role_determine_user_skip( $super_admin_id, $ban_status, $spam_status, $unsubscribe_status ) ) {

                // Since we are skipping the user, it is processed, so we increase the counter for processed users
                $request_processed_users_count++;

                // Skip this user and go to the next one in the cycle
                continue;
            }

            // If the mode is "send" we also schedule the email
            if ( 'send' === $mode ) {

                $to_email = $super_admin_object->user_email;

                // We apply the shortcodes to the email message text
                $message_with_shortcodes = be_mu_email_apply_message_shortcodes( $message, $super_admin_id, $role_sites_id_option, $role_sites_ids );

                // If the user does not have a user token assigned, we assign a new random one and add it to the user global data.
                be_mu_assign_user_token_if_not_exist( $super_admin_id );

                // Adds the unsubscribe footer message to the email message if needed
                $message_with_footer = be_mu_email_add_unsubscribe_footer( $message_with_shortcodes, $super_admin_id );

                // Schedule the email to be sent
                be_mu_email_schedule_email( $task_id, $from_name, $from_email, $to_email, $subject, $message_with_footer );
            }

            if ( 'export' === $mode ) {
                $export_emails[] = $super_admin_object->user_email;
            }

            // At this point the current user is processed, so we increase the counter of processed users
            $request_processed_users_count++;

            // Current user is affected
            $request_affected_users_count++;

            // We add the user id to the comma-separated string for the database
            $affected_users_string .= $super_admin_id . ",";
        }

    // In this else there is a role selected different from "Super Admin" and "Any or no role"
    } else {

        // If the selected role has to be in specific site(s) (and not just any site) we get the site IDs of these sites
        if ( 'Any' !== $role_sites_attribute || 'Any site ID' !== $role_sites_id_option ) {

            $role_get_sites_arguments = be_mu_email_specific_role_build_get_sites_arguments( $role_sites_id_option, $role_sites_ids, $role_sites_attribute );

            // If the user has the selected role in at least one of these sites, we dont't have to skip it (the user is affected by the email task)
            $site_ids = get_sites( $role_get_sites_arguments );
        }

        // Based on the settings for the current task and the offset for the current request we create the arguments for get_users()
        $get_users_arguments = be_mu_email_build_get_users_arguments( $users_id_option, $users_ids, $offset );

        // We get the users
        $users = get_users( $get_users_arguments );

        // We go through the users
        foreach ( $users as $user_object ) {

            // The id of the current user in the foreach cycle
            $user_id = intval( $user_object->ID );

            if ( 'Any role from a list' === $role ) {

                // Based on the selected settings for the current task we could skip some users
                if ( be_mu_email_any_role_determine_user_skip( $user_id, $ban_status, $spam_status, $unsubscribe_status )
                    || ( 'Any' === $role_sites_attribute && 'Any site ID' === $role_sites_id_option
                        && be_mu_email_role_list_any_site_determine_user_skip( $user_id, $list_roles_array ) )
                    || ( ( 'Any' !== $role_sites_attribute || 'Any site ID' !== $role_sites_id_option )
                        && be_mu_email_list_roles_specific_sites_determine_user_skip( $user_id, $list_roles_array, $site_ids ) ) ) {

                    // Since we are skipping the user, it is processed, so we increase the counter for processed users
                    $request_processed_users_count++;

                    /**
                     * If more than BE_MU_EMAIL_LIMIT_TIME seconds have passed or we processed more than BE_MU_EMAIL_LIMIT_USERS users
                     * we stop and we set $is_request_limit_reached to 1 so we know that a limit stopped us and we are not done.
                     */
                    if ( ( microtime( true ) - $time1 ) > BE_MU_EMAIL_LIMIT_TIME
                        || $request_processed_users_count == BE_MU_EMAIL_LIMIT_USERS ) {
                        $is_request_limit_reached = 1;
                        break;
                    }

                    // Skip this user and go to the next one in the cycle
                    continue;
                }

            } else {

                // Based on the selected settings for the current task we could skip some users
                if ( be_mu_email_any_role_determine_user_skip( $user_id, $ban_status, $spam_status, $unsubscribe_status )
                    || ( 'Any' === $role_sites_attribute && 'Any site ID' === $role_sites_id_option
                        && be_mu_email_specific_role_any_site_determine_user_skip( $user_id, $role ) )
                    || ( ( 'Any' !== $role_sites_attribute || 'Any site ID' !== $role_sites_id_option )
                        && be_mu_email_specific_sites_determine_user_skip( $user_id, $role, $site_ids ) ) ) {

                    // Since we are skipping the user, it is processed, so we increase the counter for processed users
                    $request_processed_users_count++;

                    /**
                     * If more than BE_MU_EMAIL_LIMIT_TIME seconds have passed or we processed more than BE_MU_EMAIL_LIMIT_USERS users
                     * we stop and we set $is_request_limit_reached to 1 so we know that a limit stopped us and we are not done.
                     */
                    if ( ( microtime( true ) - $time1 ) > BE_MU_EMAIL_LIMIT_TIME
                        || $request_processed_users_count == BE_MU_EMAIL_LIMIT_USERS ) {
                        $is_request_limit_reached = 1;
                        break;
                    }

                    // Skip this user and go to the next one in the cycle
                    continue;
                }

            }

            // If the mode is "send" we also schedule the email
            if ( 'send' === $mode ) {

                $to_email = $user_object->user_email;

                // We apply the shortcodes to the email message text
                $message_with_shortcodes = be_mu_email_apply_message_shortcodes( $message, $user_id, $role_sites_id_option, $role_sites_ids );

                // If the user does not have a user token assigned, we assign a new random one and add it to the user global data.
                be_mu_assign_user_token_if_not_exist( $user_id );

                // Adds the unsubscribe footer message to the email message if needed
                $message_with_footer = be_mu_email_add_unsubscribe_footer( $message_with_shortcodes, $user_id );

                // Schedule the email to be sent
                be_mu_email_schedule_email( $task_id, $from_name, $from_email, $to_email, $subject, $message_with_footer );
            }

            if ( 'export' === $mode ) {
                $export_emails[] = $user_object->user_email;
            }

            // At this point the current user is processed, so we increase the counter of processed users
            $request_processed_users_count++;

            // Current user is affected
            $request_affected_users_count++;

            // We add the user id to the comma-separated string for the database
            $affected_users_string .= $user_id . ",";

            /**
             * If more than BE_MU_EMAIL_LIMIT_TIME seconds have passed or we processed more than BE_MU_EMAIL_LIMIT_USERS users
             * we stop and we set $is_request_limit_reached to 1 so we know that a limit stopped us and we are not done.
             */
            if ( ( microtime( true ) - $time1 ) > BE_MU_EMAIL_LIMIT_TIME
                || $request_processed_users_count == BE_MU_EMAIL_LIMIT_USERS ) {
                $is_request_limit_reached = 1;
                break;
            }
        }
    }

    // Inserts a comma-separated list of ids of affected users in the database (if there are any in the current request)
    if ( ! empty( $affected_users_string ) ) {
        be_mu_email_add_task_data( $task_id, $affected_users_string );
    }

    // If the mode is "send" and there are affected users and the cron is not already scheduled we scheduled the cron to send emails
    if ( 'send' === $mode && $request_affected_users_count > 0 && ! wp_next_scheduled( 'be_mu_email_event_hook_send_emails' ) ) {
        wp_schedule_event( time(), 'be_mu_every_15_sec', 'be_mu_email_event_hook_send_emails' );
    }

    if ( count( $export_emails ) > 0 ) {
        $export_emails_string = implode( ';;', $export_emails ) . ';;';
    } else {
        $export_emails_string = '';
    }

    // This is the data to pass to the javascript function in json format
    $json_result = array(
        'requestProcessedUsersCount' => $request_processed_users_count,
        'requestAffectedUsersCount' => $request_affected_users_count,
        'currentOffset' => $offset,
        'exportEmails' => $export_emails_string,
        'limitReached' => $is_request_limit_reached,
    );

    echo json_encode( $json_result );
                     
    wp_die();
}

/**
 * Shows the results of the current email task.
 * It is called by an ajax request in the javascript function emailResults().
 */
function be_mu_email_results_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_email_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // The current page num we are showing results for
    $page_number = intval( $_POST['page_number'] );

    // The number of users that were/would be affected
    $count_affected_users = intval( $_POST['count_affected_users'] );

    // The current task id that tells us which rows to get from the database
    $task_id = wp_filter_nohtml_kses( $_POST['task_id'] );

    // The mode that we are working with in this task: send or preview
    $mode = $_POST['mode'];

    //how many users to show per page
    $per_page = 10;

    // We get the user ids for the users to be displayed in the current page of results
    $task_data_multi_array = be_mu_get_task_data( $task_id, 'calculate', $page_number, $per_page, 'be_mu_email_tasks', array( 'users' ) );

    if ( false != $task_data_multi_array ) {

        // If there are affected users we set an array with the user ids
        $user_ids = $task_data_multi_array['users'];
    } else {
        $count_affected_users = 0;
    }

    // We calculate the total number of pages in the results for the task
    $pages_count = ceil( $count_affected_users / $per_page );

    ?>

    <div class='be-mu-p20'>
        <h2 class='be-mu-email-h2'>
            <?php
                if ( 'send' === $mode ) {
                    esc_html_e( 'Scheduling Emails Completed', 'beyond-multisite' );
                } else {
                    esc_html_e( 'Preview User Selection', 'beyond-multisite' );
                }
            ?>
            <div class='be-mu-right'>
                <?php
                    if ( 'send' === $mode ) {
                        echo be_mu_email_get_close_and_reload();
                    } else {
                        echo be_mu_email_get_close();
                    }
                ?>
            </div>
        </h2>

        <p class='be-mu-1-15-em'>
            <b>
                <?php
                    echo esc_html__( 'Affected users:', 'beyond-multisite' ) . ' ' . intval( $count_affected_users );
                ?>
            </b>
        </p>

        <?php

        // If there are any affected users we will show a table with the users
        if ( $count_affected_users > 0 ) {

            // These are the settings for the get_users function, we set it to get only the users we want
            $get_users_arguments = array(
                'blog_id' => 0,
                'include' => $user_ids,
            );

            // We get the users into an array with the user objects
            $users = get_users( $get_users_arguments );

            /**
             * If the users do not fit on one page
             * we display a text saying which users are showed now and which page.
             */
            if ( $count_affected_users > $per_page ) {
                $to_results = $per_page * $page_number;
                if ( $to_results > $count_affected_users ) {
                    $to_results = $count_affected_users;
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

            // If we were able to get the users, we display the table with them, otherwise we show an error
            if ( ! empty( $users ) ) {

                echo '<table class="be-mu-table be-mu-mtop20">';
                echo '<thead>'
                    . '<tr>'
                    . '<th>' . esc_html__( 'ID', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'Username', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'Actions', 'beyond-multisite' ) . '</th>'
                    . '</tr>'
                    . '</thead>';

                echo '<tbody>';

                // We show information about each affected user
                for ( $i = 0; $i < count( $users ); $i++ ) {

                    echo '<tr>'
                        . '<td data-title="' . esc_attr__( 'ID', 'beyond-multisite' ) . '">' . esc_html( $users[ $i ]->ID ) . '</td>'
                        . '<td data-title="' . esc_attr__( 'Username', 'beyond-multisite' ) . '" class="be-mu-break-word">'
                        . esc_html( $users[ $i ]->user_login ) . '</td>'
                        . '<td data-title="' . esc_attr__( 'Actions', 'beyond-multisite' ) . '">'
                        . '<a href="' . esc_url( network_admin_url( 'user-edit.php?user_id=' . intval( $users[ $i ]->ID ) ) )
                        . '" target="_blank">' . esc_html__( 'Edit', 'beyond-multisite' ) . '</a> | '
                        . '<a href="' . esc_url( network_admin_url( 'users.php?s=' . $users[ $i ]->user_email ) )
                        . '" target="_blank">' . esc_html__( 'User Sites', 'beyond-multisite' ) . '</a>'
                        . '</td>'
                        . '</tr>';
                }

                echo '</tbody>';

                echo '<tfoot>'
                    . '<tr>'
                    . '<th>' . esc_html__( 'ID', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'Username', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'Actions', 'beyond-multisite' ) . '</th>'
                    . '</tr>'
                    . '</tfoot>';

                echo '</table>';

            } else {
                echo '<p>' . esc_html__( 'An error occurred.', 'beyond-multisite' ) . '</p>';
            }

            /**
             * If the users do not fit on one page
             * we show this dropdown menu to choose a page number to display.
             */
            if ( $count_affected_users > $per_page ) {
                echo '<p>'
                    . '<label for="be-mu-email-page-number">' . esc_html__( 'Go to page:', 'beyond-multisite' ) . '</label> '
                    . '<select onchange="emailResults()" id="be-mu-email-page-number" name="be-mu-email-page-number" size="1">';

                // We go through the pages and display an option and mark the current page as selected in the dropdown menu
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
                    echo '&nbsp;<span onclick="emailUsersNextPreviousPage(' . intval( $page_number - 1 ) . ')" '
                        . 'class="dashicons dashicons-arrow-left-alt2" title="' . esc_attr__( 'Previous Page', 'beyond-multisite' ) . '"></span>';
                }

                if ( $pages_count > $page_number ) {
                    echo '&nbsp;<span onclick="emailUsersNextPreviousPage(' . intval( $page_number + 1 ) . ')" '
                        . 'class="dashicons dashicons-arrow-right-alt2" title="' . esc_attr__( 'Next Page', 'beyond-multisite' ) . '"></span>';
                }

                echo '&nbsp;'
                    . '<img id="be-mu-email-loading-page-number" src="' . esc_url( be_mu_img_url( 'loading.gif' ) ) . '" />'
                    . '</p>';
            }

            if ( 'send' != $mode ) {
                echo '<p class="be-mu-right-txt"><input class="button button-primary" onclick="emailStart( \'send\' )"'
                    . 'type="button" value="' . esc_attr__( 'Send Email', 'beyond-multisite' ) . '" /></p>';
            }
        }

        ?>

    </div>

    <?php

    wp_die();

}

/**
 * Return the html code for the button that aborts the email task
 * @return string
 */
function be_mu_email_get_abort() {
    return '<input type="button" class="button be-mu-email-abort-button" onclick="emailCloseAbort( \'no-reload\' )" value="'
        . esc_attr__( 'Abort', 'beyond-multisite' ) . '" />';
}

/**
 * Return the html code for the button that closes the email task
 * @return string
 */
function be_mu_email_get_close() {
    return '<input type="button" class="button be-mu-mleft10imp" onclick="emailCloseAbort( \'no-reload\' )" value="'
        . esc_attr__( 'Close', 'beyond-multisite' ) . '" />';
}

/**
 * Return the html code for the button that closes the email task and reloads the page
 * @return string
 */
function be_mu_email_get_close_and_reload() {
    return '<input type="button" class="button be-mu-mleft10imp" onclick="emailCloseAbort( \'reload\' )" value="'
        . esc_attr__( 'Close', 'beyond-multisite' ) . '" />';
}

/**
 * Based on ban, spam, and unsubscribe user status and the selected task settings we could skip some users while processing them in an email task.
 * This function checks that and returns true if we need to skip the current user.
 * @param int $user_id
 * @param string $ban_status
 * @param string $spam_status
 * @param string $unsubscribe_status
 * @return bool
 */
function be_mu_email_any_role_determine_user_skip( $user_id, $ban_status, $spam_status, $unsubscribe_status ) {

    // If the sected task setting for the ban status is not "Any", we might skip some users
    if ( 'Any' !== $ban_status ) {

        // Check if the user is banned
        $is_user_banned = be_mu_is_user_banned( $user_id );

        // Skip banned or not banned users based on the selected setting
        if ( ( 'Not banned' === $ban_status && $is_user_banned )
            || ( 'Banned' === $ban_status && ! $is_user_banned ) ) {
            return true;
        }
    }

    // If the sected task setting for the spam status is not "Any", we might skip some users
    if ( 'Any' !== $spam_status ) {

        $user_object = get_user_by( 'ID', $user_id );
        $user_login = $user_object->user_login;

        // Check if the user is spammed
        $is_user_spammed = is_user_spammy( $user_login );

        // Skip spammed or not spammed users based on the selected setting
        if ( ( 'Not spammed' === $spam_status && $is_user_spammed )
            || ( 'Spammed' === $spam_status && ! $is_user_spammed ) ) {
            return true;
        }
    }

    // If the sected task setting for the unsubscribe status is not "Any", we might skip some users
    if ( 'Any' !== $unsubscribe_status ) {

        // Check if the user is unsubscribed
        if ( get_user_option( 'be-mu-email-unsubscribed', $user_id ) === 'Yes' ) {
            $is_user_unsubscribed = true;
        } else {
            $is_user_unsubscribed = false;
        }

        // Check if the unsubscribe feature is off and treat all users as not unsubscribed then
        if ( be_mu_get_setting( 'be-mu-email-unsubscribe-feature' ) == 'off' ) {
            $is_user_unsubscribed = false;
        }

        // Skip unsubscribed or not unsubscribed users based on the selected setting
        if ( ( 'Not unsubscribed' === $unsubscribe_status && $is_user_unsubscribed )
            || ( 'Unsubscribed' === $unsubscribe_status && ! $is_user_unsubscribed ) ) {
            return true;
        }
    }

    // If we got to this point, then the user will not be skipped
    return false;
}

/**
 * Based on the selected role we could skip some users while processing them in an email task. It is used when the role can be in any site.
 * This function returns true if we need to skip the current user.
 * @param int $user_id
 * @param string $role
 * @return bool
 */
function be_mu_email_specific_role_any_site_determine_user_skip( $user_id, $role ) {

    // A list of ids of sites in which the user has the chosen role
    $user_site_ids_by_role = be_mu_get_user_blogs_by_role( $user_id, $role );

    // If the user has the selected role in at least one site, we will not skip it
    if ( count( $user_site_ids_by_role ) > 0 ) {
        return false;
    }

    // If we got to this point, then the user will be skipped
    return true;
}

/**
 * Based on the selected roles we could skip some users while processing them in an email task. It is used when the role can be in any site.
 * This function returns true if we need to skip the current user.
 * @param int $user_id
 * @param array $roles
 * @return bool
 */
function be_mu_email_role_list_any_site_determine_user_skip( $user_id, $roles ) {

    foreach ( $roles as $role ) {

        // A list of ids of sites in which the user has the chosen role
        $user_site_ids_by_role = be_mu_get_user_blogs_by_role( $user_id, $role );

        // If the user has the selected role in at least one site, we will not skip it
        if ( count( $user_site_ids_by_role ) > 0 ) {
            return false;
        }
    }

    // If we got to this point, then the user will be skipped
    return true;
}

/**
 * Based on the selected role we could skip some users while processing them in an email task. It is used when the role has to be in a cetain site or sites.
 * This function returns true if we need to skip the current user.
 * @param int $user_id
 * @param string $role
 * @param array $site_ids
 * @return bool
 */
function be_mu_email_specific_sites_determine_user_skip( $user_id, $role, $site_ids ) {

    // A list of ids of sites in which the user has the chosen role
    $user_site_ids_by_role = be_mu_get_user_blogs_by_role( $user_id, $role );

    /**
     * We check if any of the sites, where the user has the selected role, is also in the array of sites, in which we have to check for the role based
     * on the task settings. If any of the sites is in that array, we will not skip the user.
     */
    foreach ( $user_site_ids_by_role as $user_site_id ) {
        if ( in_array( $user_site_id, $site_ids ) ) {
            return false;
        }
    }

    // If we got to this point, then the user will be skipped
    return true;
}

/**
 * Based on the selected roles we could skip some users while processing them in an email task. It is used when the role has to be in a cetain site or sites.
 * This function returns true if we need to skip the current user.
 * @param int $user_id
 * @param array $roles
 * @param array $site_ids
 * @return bool
 */
function be_mu_email_list_roles_specific_sites_determine_user_skip( $user_id, $roles, $site_ids ) {

    foreach ( $roles as $role ) {

        // A list of ids of sites in which the user has the chosen role
        $user_site_ids_by_role = be_mu_get_user_blogs_by_role( $user_id, $role );

        /**
         * We check if any of the sites, where the user has the selected role, is also in the array of sites, in which we have to check for the role based
         * on the task settings. If any of the sites is in that array, we will not skip the user.
         */
        foreach ( $user_site_ids_by_role as $user_site_id ) {
            if ( in_array( $user_site_id, $site_ids ) ) {
                return false;
            }
        }
    }

    // If we got to this point, then the user will be skipped
    return true;
}

/**
 * Based on the task settings it returns an array with the arguments for the get_sites function we use getting the sites in which we look for the user role.
 * @param string $role_sites_id_option
 * @param string $role_sites_ids
 * @param string $role_sites_attribute
 * @return array
 */
function be_mu_email_specific_role_build_get_sites_arguments( $role_sites_id_option, $role_sites_ids, $role_sites_attribute ) {

    if ( 'Only these site IDs:' == $role_sites_id_option ) {

        // Make an array of all the site ids to include
        $include_site_ids = explode( ',', $role_sites_ids );

        $get_sites_arguments = array(
            'site__in' => $include_site_ids,
        );
    } elseif ( 'All except these site IDs:' == $role_sites_id_option ) {

        // Make an array of all the site ids to exclude
        $exclude_site_ids = explode( ',', $role_sites_ids );

        $get_sites_arguments = array(
            'site__not_in' => $exclude_site_ids,
        );
    }

    // Based on the chosen setting for the site attributes we add more data to the arguments array
    if ( 'Public' == $role_sites_attribute ) {
        $get_sites_arguments['public'] = 1;
    } elseif ( 'Deleted' == $role_sites_attribute ) {
        $get_sites_arguments['deleted'] = 1;
    } elseif ( 'Spam' == $role_sites_attribute ) {
        $get_sites_arguments['spam'] = 1;
    } elseif ( 'Archived' == $role_sites_attribute ) {
        $get_sites_arguments['archived'] = 1;
    } elseif ( 'Mature' == $role_sites_attribute ) {
        $get_sites_arguments['mature'] = 1;
    } elseif ( 'Not public' == $role_sites_attribute ) {
        $get_sites_arguments['public'] = 0;
    } elseif ( 'Not deleted' == $role_sites_attribute ) {
        $get_sites_arguments['deleted'] = 0;
    } elseif ( 'Not spam' == $role_sites_attribute ) {
        $get_sites_arguments['spam'] = 0;
    } elseif ( 'Not archived' == $role_sites_attribute ) {
        $get_sites_arguments['archived'] = 0;
    } elseif ( 'Not mature' == $role_sites_attribute ) {
        $get_sites_arguments['mature'] = 0;
    } elseif ( 'Not deleted or spam' == $role_sites_attribute ) {
        $get_sites_arguments['deleted'] = 0;
        $get_sites_arguments['spam'] = 0;
    } elseif ( 'Any' != $role_sites_attribute ) {
        wp_die( 'invalid-data' );
    }

    // We only need the site ids to be returned
    $get_sites_arguments['fields'] = 'ids';

    // We set a limit for the returned sites to be huge, because it is only 100 by default
    $get_sites_arguments['number'] = 100000000;

    return $get_sites_arguments;
}

/**
 * Based on the chosen settings for user id selection, it decides if we should skip the user. It is used only when the Super Admin role is selected.
 * @param int $super_admin_id
 * @param string $users_id_option
 * @param string $users_ids
 * @return bool
 */
function be_mu_email_determine_super_admin_skip_by_id( $super_admin_id, $users_id_option, $users_ids ) {

    if ( 'Any user ID' == $users_id_option ) {
        return false;
    } elseif ( 'Only these user IDs:' == $users_id_option ) {

        // Make an array of all the user ids to include
        $include_user_ids = explode( ',', $users_ids );

        // If the current Super Admin is in the included, we do not skip it.
        if ( in_array( $super_admin_id, $include_user_ids ) ) {
            return false;
        }
    } elseif ( 'All except these user IDs:' == $users_id_option ) {

        // Make an array of all the user ids to exclude
        $exclude_user_ids = explode( ',', $users_ids );

        // If the current Super Admin is not in the excluded, we do not skip it.
        if ( ! in_array( $super_admin_id, $exclude_user_ids ) ) {
            return false;
        }
    } else {
        wp_die( 'invalid-data' );
    }

    // If we got to this point, we have to skip the current Super Admin
    return true;
}

/**
 * Based on the settings for the current task and the offset for the current request we create the arguments for get_users()
 * @param string $users_id_option
 * @param string $users_ids
 * @param int $offset
 * @return array
 */
function be_mu_email_build_get_users_arguments( $users_id_option, $users_ids, $offset ) {

    if ( 'Any user ID' == $users_id_option ) {
        return array(
            'blog_id' => 0,
            'offset' => $offset,
            'number' => BE_MU_EMAIL_LIMIT_USERS,
        );
    } elseif ( 'Only these user IDs:' == $users_id_option ) {

        // Make an array of all the user ids to include
        $include_user_ids = explode( ',', $users_ids );

        return array(
            'blog_id' => 0,
            'offset' => $offset,
            'number' => BE_MU_EMAIL_LIMIT_USERS,
            'include' => $include_user_ids,
        );
    } elseif ( 'All except these user IDs:' == $users_id_option ) {

        // Make an array of all the user ids to exclude
        $exclude_user_ids = explode( ',', $users_ids );

        return array(
            'blog_id' => 0,
            'offset' => $offset,
            'number' => BE_MU_EMAIL_LIMIT_USERS,
            'exclude' => $exclude_user_ids,
        );
    } else {
        wp_die( 'invalid-data' );
    }
}

/**
 * Checks if the selected drop-down settings for the email user selection are valid and returns true if yes, false if no.
 * @param string $role
 * @param string $role_sites_attribute
 * @param string $role_sites_id_option
 * @param string $users_id_option
 * @param string $ban_status
 * @param string $spam_status
 * @param string $unsubscribe_status
 * @return bool
 */
function be_mu_email_user_vars_valid( $role, $role_sites_attribute, $role_sites_id_option, $users_id_option, $ban_status, $spam_status,
    $unsubscribe_status, $list_roles ) {

    if ( ! in_array( $role, array( 'Any or no role', 'Any role', 'Super Admin', 'Administrator', 'Editor', 'Author', 'Contributor',
        'Subscriber', 'Any role from a list' ) )
        || ! in_array( $role_sites_attribute, array( 'Any', 'Public', 'Deleted', 'Spam', 'Archived', 'Mature', 'Not public', 'Not deleted', 'Not spam',
        'Not archived', 'Not mature', 'Not deleted or spam' ) )
        || ! in_array( $role_sites_id_option, array( 'Any site ID', 'Only these site IDs:', 'All except these site IDs:' ) )
        || ! in_array( $users_id_option, array( 'Any user ID', 'Only these user IDs:', 'All except these user IDs:' ) )
        || ! in_array( $ban_status, array( 'Not banned', 'Banned', 'Any' ) )
        || ! in_array( $spam_status, array( 'Not spammed', 'Spammed', 'Any' ) )
        || ! in_array( $unsubscribe_status, array( 'Not unsubscribed', 'Unsubscribed', 'Any' ) )
        || sanitize_html_class( str_replace( ',', '', $list_roles ) ) !== str_replace( ',', '', $list_roles )
        ) {
        return false;
    }
    return true;
}

/**
 * Checks if the entered data for the email message fields is valid.
 * @param string $from_email
 * @param string $from_name
 * @param string $subject
 * @param string $message
 */
function be_mu_email_message_vars_validate( $from_email, $from_name, $subject, $message ) {

    // If the email, that we will send from, is invalid - we stop and show an error code
    if ( ! filter_var( $from_email, FILTER_VALIDATE_EMAIL ) ) {
        wp_die( 'invalid-from-email' );
    }

    // If some of the fields are empty after we stip the whitespace, we stop and show an error code
    $from_name_no_whitespace = be_mu_strip_whitespace( $from_name );
    $subject_no_whitespace = be_mu_strip_whitespace( $subject );
    $message_no_whitespace = be_mu_strip_whitespace( $message );
    if ( empty( $from_name_no_whitespace ) || empty( $subject_no_whitespace ) || empty( $message_no_whitespace ) ) {
        wp_die( 'empty-fields' );
    }
}

// Cancels or completes an email sending task, which are the same - deletes the database information and stops the cron job.
function be_mu_email_cancel_or_complete_email_task_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_email_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    $task_id = wp_filter_nohtml_kses( $_POST['task_id'] );

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // The database table for email sendind tasks
    $db_table_email_tasks = $main_blog_prefix . 'be_mu_email_tasks';

    // The database table for the emais to send
    $db_table_emails = $main_blog_prefix . 'be_mu_email_emails';

    // We delete all data about the task
    $wpdb->query( $wpdb->prepare( "DELETE FROM " . $db_table_email_tasks . " WHERE task_id = %s", $task_id ) );
    $wpdb->query( $wpdb->prepare( "DELETE FROM " . $db_table_emails . " WHERE task_id = %s", $task_id ) );

    // We clear the scheduled cron job that sends the email, since there is no active task now
    if ( wp_next_scheduled( 'be_mu_email_event_hook_send_emails' ) ) {
        wp_clear_scheduled_hook( 'be_mu_email_event_hook_send_emails' );
    }

    delete_site_option( 'be-mu-email-task-mode-' . $task_id );

    // End the ajax request
    wp_die();
}

// A cron job that goes through the emails sends them and marks them as sent of failed.
function be_mu_email_send_emails_cron() {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // The database table for the emails to send
    $db_table_emails = $main_blog_prefix . 'be_mu_email_emails';

    $max_send_speed_parts = explode( ' ', be_mu_get_setting( 'be-mu-email-speed' ) );

    //how many emails maximum to send per 15 seconds
    $max_send_speed_per_15_sec = intval( $max_send_speed_parts[0] ) / 240;

    // If the speed is more than 15 per 15 seconds we set it to 15
    if ( $max_send_speed_per_15_sec > 15 ) {
        $max_send_speed_per_15_sec = 15;

    // If the speed is less than 1 per 15 seconds we set it to 1
    } elseif ( $max_send_speed_per_15_sec < 1 ) {
        $max_send_speed_per_15_sec = 1;
    }

    for ( $i = 0; $i < $max_send_speed_per_15_sec; $i++ ) {

        $one_minute_ago = time() - 60;

        // Get the data for one email that needs sending, including ones that are marked as currently being sent but more than 60 seconds have passed
        $emails_multi_array = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $db_table_emails . " WHERE status='scheduled' OR"
            . " ( status='working' AND unix_time_last_working < %d ) LIMIT 1", $one_minute_ago ), ARRAY_A );

        if ( ! empty( $emails_multi_array ) ) {

            // Foreach should run only once
            foreach ( $emails_multi_array as $email ) {

                // Before we send it we change the status to working so it is not send again while we send it
                $wpdb->update(
                    $db_table_emails,
                    array(
                        'status' => 'working',
                        'unix_time_last_working' => time(),
                    ),
                    array(
                        'row_id' =>  $email['row_id'],
                    ),
                    array( '%s', '%d' ),
                    array( '%d' )
                );

                // We send the email and get the status
                $status = be_mu_send_email( $email['from_email'], html_entity_decode( $email['from_name'] ),
                    $email['to_email'], html_entity_decode( $email['subject'] ), $email['message'] );

                // If the email was sent successfully we change its status to sent
                if ( $status ) {
                    $wpdb->update(
                        $db_table_emails,
                        array(
                            'status' => 'sent',
                        ),
                        array(
                            'row_id' =>  $email['row_id'],
                        ),
                        array( '%s' ),
                        array( '%d' )
                    );

                // If the sending of the email failed we change its status to failed
                } else {
                    $wpdb->update(
                        $db_table_emails,
                        array(
                            'status' => 'failed',
                        ),
                        array(
                            'row_id' => $email['row_id'],
                        ),
                        array( '%s' ),
                        array( '%d' )
                    );
                }
            }

        // If there is not even one email for sending we stop
        } else {
            break;
        }
    }
}

/**
 * Adds data to the database about an email to send.
 * @param string $task_id
 * @param string $from_name
 * @param string $from_email
 * @param string $to_email
 * @param string $subject
 * @param string $message
 */
function be_mu_email_schedule_email( $task_id, $from_name, $from_email, $to_email, $subject, $message ) {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // The database table for the emails to send
    $db_table = $main_blog_prefix . 'be_mu_email_emails';

    // Insert the email data to the database
    $wpdb->insert(
    	$db_table,
    	array(
    		'task_id' => $task_id,
    		'from_name' => $from_name,
    		'from_email' => $from_email,
    		'to_email' => $to_email,
    		'subject' => $subject,
    		'message' => $message,
    		'status' => 'scheduled',
    		'unix_time_added' => time(),
    	),
    	array(
    		'%s',
    		'%s',
    		'%s',
    		'%s',
    		'%s',
    		'%s',
    		'%s',
    		'%d',
    	)
    );
}

// Creates the database tables to store the data for the email users module
function be_mu_email_db_tables() {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // This is the query that will create the database table (if it does not exist) for the emails to send
    $sql = 'CREATE TABLE IF NOT EXISTS ' . $main_blog_prefix . 'be_mu_email_emails ( '
        . 'row_id bigint( 20 ) NOT NULL AUTO_INCREMENT, '
        . 'task_id varchar( 10 ) DEFAULT NULL, '
        . 'from_name text DEFAULT NULL, '
        . 'from_email varchar( 200 ) DEFAULT NULL, '
        . 'to_email varchar( 200 ) DEFAULT NULL, '
        . 'subject text DEFAULT NULL, '
        . 'message longtext DEFAULT NULL, '
        . 'status varchar( 20 ) DEFAULT NULL, '
        . 'unix_time_last_working int( 11 ) NOT NULL, '
        . 'unix_time_added int( 11 ) NOT NULL, '
        . 'PRIMARY KEY ( row_id ) '
        . ') ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;';

    // Execute the query
    dbDelta( $sql );

    // This is the query that will create the database table (if it does not exist) for the email tasks with user ids to send to
    $sql = 'CREATE TABLE IF NOT EXISTS ' . $main_blog_prefix . 'be_mu_email_tasks ( '
        . 'row_id bigint( 20 ) NOT NULL AUTO_INCREMENT, '
        . 'task_id varchar( 10 ) DEFAULT NULL, '
        . 'users longtext DEFAULT NULL, '
        . 'unix_time_added int( 11 ) NOT NULL, '
        . 'PRIMARY KEY ( row_id ) '
        . ') ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;';

    // Execute the query
    dbDelta( $sql );
}

/**
 * Inserts a comma-separated list of ids of users to which we will send an email.
 * Every row is with data from a different request, but could be for the same email task if they have the same task id.
 * @param string $task_id
 * @param string $users_string
 */
function be_mu_email_add_task_data( $task_id, $users_string ) {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
    $db_table = $main_blog_prefix . 'be_mu_email_tasks';

    // Insert the users data to the database
    $wpdb->insert(
    	$db_table,
    	array(
    		'task_id' => $task_id,
    		'users' => $users_string,
    		'unix_time_added' => time(),
    	),
    	array(
    		'%s',
    		'%s',
    		'%d',
    	)
    );
}

// Sends a test email to a chosen user
function be_mu_email_send_test_email_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_email_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // This is data for the email that we will send
    $from_email = $_POST['from_email'];
    $from_name = wp_filter_nohtml_kses( $_POST['from_name'] );
    $from_name = wp_encode_emoji( $from_name );
    $to_test_user = $_POST['to_test_user'];
    $subject = wp_filter_nohtml_kses( $_POST['subject'] );
    $subject = wp_encode_emoji( $subject );
    $message = stripslashes( $_POST['message'] );
    $message = wpautop( $message );
    $message = wp_encode_emoji( $message );
    $role_sites_id_option = $_POST['role_sites_id_option'];
    $role_sites_ids = be_mu_strip_whitespace( $_POST['role_sites_ids'] );

    // Validates the email message fields. Stops the script and shows an error if invalid data is found.
    be_mu_email_message_vars_validate( $from_email, $from_name, $subject, $message );

    // If the settings are not valid we stop and show an error code
    if ( ! in_array( $role_sites_id_option, array( 'Any site ID', 'Only these site IDs:', 'All except these site IDs:' ) ) ) {
        wp_die( 'invalid-data' );
    }

    // If a setting that requires a comma-separated list of ids is chosen and there is no such valid list provided, we show an error code and stop
    if ( 'Any site ID' !== $role_sites_id_option && ! be_mu_is_comma_separated_numbers( $role_sites_ids ) ) {
        wp_die( 'ids-invalid' );
    }

    // We get the user object of the user we will send a test email to
    $to_test_user_object = get_user_by( 'login', $to_test_user );

    // If there is no such user, we stop and show an error code
    if ( false === $to_test_user_object ) {
        wp_die( 'invalid-to-user' );
    }

    // These are the email address and ID of the user we will send a test email to
    $to_test_email = $to_test_user_object->user_email;
    $to_user_id = $to_test_user_object->ID;

    // We apply the shortcodes to the email message text
    $message = be_mu_email_apply_message_shortcodes( $message, $to_user_id, $role_sites_id_option, $role_sites_ids );

    // If the user does not have a user token assigned, we assign a new random one and add it to the user global data.
    be_mu_assign_user_token_if_not_exist( $to_user_id );

    // Adds the unsubscribe footer message to the email message if needed
    $message = be_mu_email_add_unsubscribe_footer( $message, $to_user_id );

    // We send the email. We decode emojis and we encoded them above so it is the same as the real sending of email.
    $status = be_mu_send_email( $from_email, html_entity_decode( $from_name ), $to_test_email, html_entity_decode( $subject ), $message );

    // We could not send the email so we stop and show an error code
    if ( ! $status ) {
        wp_die( 'could-not-send' );
    }

    // End the ajax request
    wp_die();
}

/**
 * Replaces the shortcodes with their values and returns the new message string
 * @param string $message
 * @param int $user_id
 * @param string $role_sites_id_option
 * @param string $role_sites_ids
 * @return string
 */
function be_mu_email_apply_message_shortcodes( $message, $user_id, $role_sites_id_option, $role_sites_ids ) {

    // We get some data for the user we will send a test email to
    $user_object = get_user_by( 'ID', $user_id );
    $user_username = $user_object->user_login;
    $user_first_name = $user_object->first_name;
    $user_last_name = $user_object->last_name;
    $user_display_name = $user_object->display_name;

    // We set the smart name based on the available data for the first and last name
    if ( empty( $user_first_name ) ) {
        $user_smart_name = $user_username;
    } elseif ( empty( $user_last_name ) ) {
        $user_smart_name = $user_first_name;
    } else {
        $user_smart_name = $user_first_name . " " . $user_last_name;
    }

    $has_sites_shortcodes = 'no';
    if ( strpos( $message, '[user_admin_sites]' ) !== false || strpos( $message, '[user_admin_site_title]' ) !== false
        || strpos( $message, '[user_admin_site_url]' ) !== false
        || strpos( $message, '[user_admin_sites_only_selected_sites]' ) !== false ) {
        $user_sites = be_mu_get_user_blogs_by_role( $user_id, 'administrator' );
        if ( empty( $user_sites ) ) {
            $user_admin_site_title = $user_admin_site_url = $sites_string = $selected_sites_string = '';
        } else {
            sort( $user_sites );

            // We create the html for the list of sites
            $sites_string = '<ul>';
            foreach ( $user_sites as $site_id ) {
                $site_url = get_site_url( $site_id );
                $sites_string .= '<li><a target="_blank" href="' . esc_url( $site_url ) . '">' . esc_html( $site_url ) . '</a></li>';
            }
            $sites_string .= '</ul>';

            $has_selected_sites_shortcode = 'no';

            // We create the html only for the list of selected sites in user selection if the shortcode exists in the content
            if ( strpos( $message, '[user_admin_sites_only_selected_sites]' ) !== false ) {

                // Make an array of all the site ids to include
                if ( 'Only these site IDs:' === $role_sites_id_option ) {
                    $include_site_ids = explode( ',', $role_sites_ids );

                // Make an array of all the site ids to exclude
                } elseif ( 'All except these site IDs:' === $role_sites_id_option ) {
                    $exclude_site_ids = explode( ',', $role_sites_ids );
                }

                $selected_sites_string = '<ul>';
                foreach ( $user_sites as $site_id ) {

                    // We may not include this site in the shortcode content, based on settings
                    if ( ( 'Only these site IDs:' === $role_sites_id_option && ! in_array( $site_id, $include_site_ids ) )
                        || ( 'All except these site IDs:' === $role_sites_id_option && in_array( $site_id, $exclude_site_ids ) ) ) {
                        continue;
                    }

                    $site_url = get_site_url( $site_id );
                    $selected_sites_string .= '<li><a target="_blank" href="' . esc_url( $site_url ) . '">' . esc_html( $site_url ) . '</a></li>';
                }
                if ( '<ul>' === $selected_sites_string ) {
                    $selected_sites_string = '';
                } else {
                    $selected_sites_string .= '</ul>';
                }
                $has_selected_sites_shortcode = 'yes';
            }

            $user_admin_site_title = get_blog_option( $user_sites[0], 'blogname' );
            $user_admin_site_url = get_site_url( $user_sites[0] );
        }
        $has_sites_shortcodes = 'yes';
    }

    // Replace the shortcodes with their values
    $message = str_replace( '[user_smart_name]', esc_html( $user_smart_name ), $message );
    $message = str_replace( '[user_display_name]', esc_html( $user_display_name ), $message );
    $message = str_replace( '[user_username]', esc_html( $user_username ), $message );
    $message = str_replace( '[user_first_name]', esc_html( $user_first_name ), $message );
    $message = str_replace( '[user_last_name]', esc_html( $user_last_name ), $message );
    if ( 'yes' === $has_sites_shortcodes ) {
        $message = str_replace( '[user_admin_sites]', $sites_string, $message );
        $message = str_replace( '[user_admin_site_title]', esc_html( $user_admin_site_title ), $message );
        $message = str_replace( '[user_admin_site_url]', esc_url( $user_admin_site_url ), $message );
        if ( 'yes' === $has_selected_sites_shortcode ) {
            $message = str_replace( '[user_admin_sites_only_selected_sites]', $selected_sites_string, $message );
        }
    }
    $message = str_replace( '[network_site_url]', esc_url( network_site_url() ), $message );

    // Return the new message var
    return $message;
}

// Registers and enqueues the login style file that hides the login form when the page is used for user unsubscribing actions
function be_mu_email_register_unsubscribe_login_style() {
    if ( isset( $_GET['be-mu-user-token'] ) || isset( $_GET['be-mu-user-id'] ) || isset( $_GET['be-mu-action'] ) ) {
        wp_register_style( 'be-mu-unsubscribe-login-style', be_mu_plugin_dir_url() . 'styles/unsubscribe-login.css', false, BEYOND_MULTISITE_VERSION );
        wp_enqueue_style( 'be-mu-unsubscribe-login-style' );
    }
}

/**
 * Using the lost password page it unsubscribes a user when the unsubscribe link is clicked and shows the appropriate message.
 * @return string
 */
function be_mu_email_unsubscribe_login_message( $message ) {

    // We will only change the message if any of our variables are set in the URL
    if ( isset( $_GET['be-mu-user-token'] ) || isset( $_GET['be-mu-user-id'] ) || isset( $_GET['be-mu-action'] ) ) {

        // If one of the required variables is not in the URL we show an error
        if ( ! isset( $_GET['be-mu-user-token'] ) || ! isset( $_GET['be-mu-user-id'] ) || ! isset( $_GET['be-mu-action'] ) ) {
            return '<div id="login_error">' . esc_html__( 'Invalid request.', 'beyond-multisite' ) . '</div>';
        }

        if ( 'unsubscribe' === $_GET['be-mu-action'] ) {

            $user_id = intval( $_GET['be-mu-user-id'] );

            // If the user token in the URL is not the same as the one in the user options we show an error.
            if ( get_user_option( 'be-mu-user-token', $user_id ) !== $_GET['be-mu-user-token'] ) {
                return '<div id="login_error">' . esc_html__( 'Invalid token.', 'beyond-multisite' ) . '</div>';
            }

            // If the unsubscribe feature is turned off we show an error.
            if ( be_mu_get_setting( 'be-mu-email-unsubscribe-feature' ) == 'off' ) {
                return '<div id="login_error">' . esc_html__( 'The unsubscribe features is currently turned off.', 'beyond-multisite' ) . '</div>';
            }

            // If the user is not unsubscribed, we unsubscribe him and show a message. Otherwise we show a message.
            if ( get_user_option( 'be-mu-email-unsubscribed', $user_id ) !== 'Yes' ) {

                // This unsubscribes the user
                $status = update_user_option( $user_id, 'be-mu-email-unsubscribed', 'Yes', true );

                // We show a message based on whether we successfully unsubscribed the user or there was an error.
                if ( $status ) {
                    return '<div class="message">' . esc_html__( 'You will no longer receive emails from the network administrator. '
                        . 'You can change back this setting from your user profile page.', 'beyond-multisite' ) . '</div>';
                } else {
                    return '<div id="login_error">' . esc_html__( 'An unexpected error occurred.', 'beyond-multisite' ) . '</div>';
                }
            } else {
                return '<div class="message">' . esc_html__( 'You are already unsubscribed from these emails. No changes were made. '
                    . 'You can change back this setting from your user profile page.', 'beyond-multisite' ) . '</div>';
            }
        }
    } else {
        return $message;
    }
}

// Adds the drop-down menu in the user profile settings page that allows the user to unsubscribe
function be_mu_email_unsubscribe_profile_field( $user ) {

    $user_id = intval( $user->ID );

    echo '<h2>' . esc_html__( 'Unsubscribe', 'beyond-multisite' ) . '</h2>';

    echo '<table class="form-table">'
        . '<tbody>'
        . '<tr>'
        . '<th>'
        . '<label for="be-mu-email-unsubscribed">' . esc_html__( 'Unsubscribe from network administrator emails', 'beyond-multisite' ) . '</label>'
        . '</th>'
        . '<td>';

    be_mu_user_setting_select(
        'be-mu-email-unsubscribed',
        $user_id,
        'No',
        array( 'Yes', 'No' ),
        array(
            __( 'Yes', 'beyond-multisite' ),
            __( 'No', 'beyond-multisite' ),
        )
    );

    echo '</td>'
        . '</tr>'
        . '</tbody>'
        . '</table>';
}

// Saves the data for the drop-down menu in the user profile settings page that allows the user to unsubscribe
function be_mu_email_unsubscribe_save_profile_field( $user_id ) {

    // If the user does not have rights to edit the selected user or if the data for the unsubscribe setting is not present or invalid, we stop.
    if ( ! current_user_can( 'edit_user', $user_id ) || ! isset( $_POST['be-mu-email-unsubscribed'] )
        || ! in_array( $_POST['be-mu-email-unsubscribed'], array( 'Yes', 'No' ) ) ) {
        return false;
    }

    // We update the unsubscribe user setting with the chosen value
    update_user_option( $user_id, 'be-mu-email-unsubscribed', $_POST['be-mu-email-unsubscribed'], true );
}

// Adds the unsubscribe footer message to the email message if needed
function be_mu_email_add_unsubscribe_footer( $message, $user_id ) {

    // We will add the unsubscribe footer only if the unsubscribe feature is turned on
    if ( be_mu_get_setting( 'be-mu-email-unsubscribe-feature' ) == 'on' ) {

        // This is the footer message
        $footer_message = be_mu_get_setting( 'be-mu-email-unsubscribe-footer' );

        // This is part of the unsubscribe URL for the selected user
        $add_to_url = '&be-mu-user-id=' . intval( $user_id ) . '&be-mu-user-token=' . get_user_option( 'be-mu-user-token', $user_id )
            . '&be-mu-action=unsubscribe';

        // We replace the shortcodes with their values
        $footer_message = str_replace( '[unsubscribe_url]', esc_url( wp_lostpassword_url() . $add_to_url ), $footer_message );
        $footer_message = str_replace( '[network_site_url]', esc_url( network_site_url() ), $footer_message );

        // We add the footer message to the email message
        $message .= $footer_message;
    }

    // We return the email message possibly with the footer at the end
    return $message;
}

// Adds a filter that changes the title of the unsubscribe page (since we are using the lost password page, we need to change its title)
function be_mu_email_unsubscribe_page_title() {
    if ( isset( $_GET['action'] ) && ( isset( $_GET['be-mu-user-token'] ) || isset( $_GET['be-mu-user-id'] ) || isset( $_GET['be-mu-action'] ) ) ) {
        add_filter( 'gettext', 'be_mu_email_unsubscribe_gettext_page_title', 20, 3 );
    }
}

// Changes the 'Lost Password' page title when we are using the page for unsubscribing users
function be_mu_email_unsubscribe_gettext_page_title( $translated_text, $text, $domain ) {
    if ( 'Lost Password' === $text ) {
        $translated_text = esc_html__( 'Unsubscribe', 'beyond-multisite' );
    }
    return $translated_text;
}

// Shows the role slugs in the Edit Site Users page
function be_mu_show_list_roles() {
    if ( isset( $_GET['id'] ) ) {
        $site_id = intval( $_GET['id'] );
    } else {
        $site_id = 0;
    }
    switch_to_blog( $site_id );
    $editable_roles = get_editable_roles();
    echo '<h2>';
    esc_html_e( 'List of roles', 'beyond-multisite' );
    echo '</h2>';
    echo '<p>' . esc_html__( 'You can see the role slugs on the right of the role name. This list is provided by the Beyond Multisite plugin, '
        . 'so you can see the role slugs of custom roles and use them in the Email Users module.', 'beyond-multisite' ) . '</p>';
    echo "<textarea rows='5' cols='50'>";
    foreach ( $editable_roles as $role => $details ) {
        echo esc_textarea( translate_user_role( $details['name'] ) . ' - ' . $role ) . "\n";
    }
    echo "</textarea>";
    restore_current_blog();
}
