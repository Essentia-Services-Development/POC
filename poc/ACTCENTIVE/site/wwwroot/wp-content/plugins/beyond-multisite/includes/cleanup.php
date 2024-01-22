<?php

/**
 * In this file we have all the hooks and functions related to the cleanup module.
 * This module is very powerful and lets you mass delete a lot of comments, revisions or even sites by a chosen criteria.
 * It even schedules future site deletions and gives admins the option to cancel them.
 * The whole thing is made a little complicated and it can be made simpler if we didn't care about huge networks with a lof of sites.
 * But the way it is made now should work without using too much memory and without reaching the maximum execution time regardless of how many sites there are.
 * To achieve this we go through all the sites in chunks (not all in one time) and we also store potencially big data in the database for a while.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// All the hooks for the cleanup module will run only if the module is turned on
if ( be_mu_get_setting( 'be-mu-clean-status-module' ) == 'on' ) {

    // Adds a submenu page called Network Cleanup under the Dashboard menu in the network admin panel and also loads a css style on that page
    add_action( 'network_admin_menu', 'be_mu_add_cleanup_menu' );

    // Ajax call to a function that goes through the sites and either executes primary comment deletion or counts how many would be deleted to preview deletion
    add_action( 'wp_ajax_be_mu_clean_comment_primary_action', 'be_mu_clean_comment_primary_callback' );

    // Ajax call to a function that goes through the sites and executes secondary comment deletion
    add_action( 'wp_ajax_be_mu_clean_comment_secondary_action', 'be_mu_clean_comment_secondary_callback' );

    // Ajax call to a function that cancels or completes a site deletion task, which is the same - deletes the database information and stops the crons
    add_action( 'wp_ajax_be_mu_clean_cancel_or_complete_site_deletion_task_action', 'be_mu_clean_cancel_or_complete_site_deletion_task_callback' );

    // Ajax call to a function that goes through the sites and either executes revision deletion or counts how many would be deleted to preview deletion
    add_action( 'wp_ajax_be_mu_clean_revision_action', 'be_mu_clean_revision_callback' );

    // Ajax call to a function that goes through the sites and either executes site deletion or counts how many would be deleted to preview deletion
    add_action( 'wp_ajax_be_mu_clean_site_action', 'be_mu_clean_site_callback' );

    // Ajax call to a function that goes through the database tables and either deletes or just shows the ones that are leftover after a site is deleted
    add_action( 'wp_ajax_be_mu_clean_table_action', 'be_mu_clean_table_callback' );

    // Ajax call to a function that goes through the users and either deletes or just shows the ones that have no role in any site
    add_action( 'wp_ajax_be_mu_clean_user_action', 'be_mu_clean_user_callback' );

    // Ajax call to a function that shows the results of the current comment deletion task
    add_action( 'wp_ajax_be_mu_clean_comment_results_action', 'be_mu_clean_comment_results_callback' );

    // Ajax call to a function that shows the results of the current revision deletion task
    add_action( 'wp_ajax_be_mu_clean_revision_results_action', 'be_mu_clean_revision_results_callback' );

    // Ajax call to a function that shows the results of the current site deletion task
    add_action( 'wp_ajax_be_mu_clean_site_results_action', 'be_mu_clean_site_results_callback' );

    // Ajax call to a function that shows the results of the current leftover database tables deletion task
    add_action( 'wp_ajax_be_mu_clean_table_results_action', 'be_mu_clean_table_results_callback' );

    // Ajax call to a function that shows the results of the current no role users deletion task
    add_action( 'wp_ajax_be_mu_clean_user_results_action', 'be_mu_clean_user_results_callback' );

    // Ajax call to a function that cancels the site deletion for the current site
    add_action( 'wp_ajax_be_mu_clean_cancel_site_deletion_action', 'be_mu_clean_cancel_site_deletion_callback' );

    // Adds a big red message and a cancellation button to the top of the admin dashboard of the sites that are scheduled for deletion
    add_action( 'admin_notices', 'be_mu_clean_admin_notice_site_deletion' );

    // Registers and localizes the javascript file for the admin dashboard of sites scheduled for deletion
    add_action( 'admin_enqueue_scripts', 'be_mu_clean_site_deletion_script_and_style' );

    // A cron job that goes through the notifications for site deletions and sends emails and schedules the site deletions
    add_action( 'be_mu_clean_event_hook_send_emails', 'be_mu_clean_send_site_deletion_emails_cron' );

    // A cron job that goes through the scheduled site deletions and actually deletes the sites if the time has come
    add_action( 'be_mu_clean_event_hook_delete_sites', 'be_mu_clean_delete_scheduled_sites_cron' );

    // Handles the ajax request to export a file with IDs or URLs from the preview results of a cleanup task
    add_action( 'wp_ajax_be_mu_clean_export_results_action', 'be_mu_clean_export_results_callback' );
}

// Ajax call to a function that sends a test email notification about scheduled site deletion. It will work even if the module is disabled.
add_action( 'wp_ajax_be_mu_clean_send_test_email_action', 'be_mu_clean_send_test_email_callback' );

// A limit for the number of sites (or database tables for the leftover tables deletion) to process in a single ajax request
define( "BE_MU_CLEAN_LIMIT_DATA", 700 );

// After this many seconds we will try to stop the ajax request when we are done with a certain task (might take more time)
define( "BE_MU_CLEAN_LIMIT_TIME", 10 );

// Adds a submenu page called Network Cleanup under the Dashboard menu in the network admin panel and also loads the style and script for the page
function be_mu_add_cleanup_menu() {
    $cleanup_page = add_submenu_page(
        'index.php',
        esc_html__( 'Network Cleanup', 'beyond-multisite' ),
        esc_html__( 'Network Cleanup', 'beyond-multisite' ),
        'manage_network',
        'be_mu_cleanup',
        'be_mu_cleanup_subpage'
    );
    add_action( 'load-' . $cleanup_page, 'be_mu_add_beyond_multisite_style' );
    add_action( 'load-' . $cleanup_page, 'be_mu_add_clean_script' );
}

// Adds the action needed to register the script for the cleanup module
function be_mu_add_clean_script() {
    add_action( 'admin_enqueue_scripts', 'be_mu_clean_register_script' );
}

// Registers and localizes the javascript file for the cleanup module
function be_mu_clean_register_script() {

    // Register the script
    wp_register_script( 'be_mu_clean_script', be_mu_plugin_dir_url() . 'scripts/cleanup.js', array(), BEYOND_MULTISITE_VERSION, false );

    // This is the data we will send from the php to the javascript file
    $localize = array(
        'extraFieldsSiteDeletion' => apply_filters( 'beyond-multisite-delete-sites-extra-fields-names', Array() ),
        'extraErrorsSiteDeletionSkip' => apply_filters( 'beyond-multisite-delete-sites-skip-extra-errors', Array() ),
        'ajaxNonce' => wp_create_nonce( 'be_mu_clean_nonce' ),
        'abortComments' => be_mu_clean_get_abort_comments(),
        'abortRevisions' => be_mu_clean_get_abort_revisions(),
        'abortSites' => be_mu_clean_get_abort_sites(),
        'abortTables' => be_mu_clean_get_abort_tables(),
        'abortUsers' => be_mu_clean_get_abort_users(),
        'loadingGIF' => esc_url( be_mu_img_url( 'loading.gif' ) ),
        'pageURL' => esc_js( network_admin_url( 'index.php?page=be_mu_cleanup' ) ),
        'processing' => esc_js( esc_html__( 'Processing...', 'beyond-multisite' ) ),
        'errorRequest' => esc_js( __( 'Error: There is another request that is still running. Please wait a few seconds and try again. '
            . 'If this problem continues, please reload the page.', 'beyond-multisite' ) ),
        'warningComments' => esc_js( __( 'WARNING! You are about to PERMANENTLY DELETE COMMENTS!', 'beyond-multisite' ) ) . "\n\n"
            . esc_js( __( 'Do you want to continue?', 'beyond-multisite' ) ),
        'warningRevisions' => esc_js( __( 'WARNING! You are about to PERMANENTLY DELETE REVISIONS!', 'beyond-multisite' ) ) . "\n\n"
            . esc_js( __( 'Do you want to continue?', 'beyond-multisite' ) ),
        'warningSites' => esc_js( __( 'WARNING! You are about to PERMANENTLY DELETE SITES!', 'beyond-multisite' ) ) . "\n\n"
            . esc_js( __( 'Do you want to continue?', 'beyond-multisite' ) ),
        'warningScheduleSites' => esc_js( __( 'WARNING! You are about to SCHEDULE a PERMANENT SITE DELETION!', 'beyond-multisite' ) ) . "\n\n"
            . esc_js( __( 'NOTICE! Archived, spammed, or deleted sites are EXCLUDED from scheduled deletions.', 'beyond-multisite' ) ) . "\n\n"
            . esc_js( __( 'Do you want to continue?', 'beyond-multisite' ) ),
        'confirmMarkDeleted' => esc_js( __( 'You are about to mark sites as deleted.', 'beyond-multisite' ) ) . "\n\n"
            . esc_js( __( 'Do you want to continue?', 'beyond-multisite' ) ),
        'confirmScheduleDeleted' => esc_js( __( 'You are about to schedule sites to be marked as deleted.', 'beyond-multisite' ) ) . "\n\n"
            . esc_js( __( 'Do you want to continue?', 'beyond-multisite' ) ),
        'confirmMarkArchived' => esc_js( __( 'You are about to mark sites as archived.', 'beyond-multisite' ) ) . "\n\n"
            . esc_js( __( 'Do you want to continue?', 'beyond-multisite' ) ),
        'confirmScheduleArchived' => esc_js( __( 'You are about to schedule sites to be marked as archived.', 'beyond-multisite' ) ) . "\n\n"
            . esc_js( __( 'Do you want to continue?', 'beyond-multisite' ) ),
        'confirmTableDeletion' => esc_js( __( 'You are about to PERMANENTLY DELETE all database tables of non-existent sites.', 'beyond-multisite' ) ) . "\n\n"
            . esc_js( __( 'Do you want to continue?', 'beyond-multisite' ) ),
        'confirmUserDeletion' => esc_js( __( 'You are about to PERMANENTLY DELETE all users that do not have a role in any site.', 'beyond-multisite' ) ) . "\n\n"
            . esc_js( __( 'Do you want to continue?', 'beyond-multisite' ) ),
        'promptNumberCustomOption' => esc_js( __( 'Please enter a whole number value for X:', 'beyond-multisite' ) ),
        'downloadIDs' => esc_js( esc_html__( 'Download IDs', 'beyond-multisite' ) ),
        'downloadURLs' => esc_js( esc_html__( 'Download URLs', 'beyond-multisite' ) ),
        'errorError' => esc_js( esc_html__( 'Error', 'beyond-multisite' ) ),
        'errorAccess' => esc_js( __( 'Error: You do not have sufficient permissions to make this request.', 'beyond-multisite' ) ),
        'errorInvalidNonce' => esc_js( __( 'Error: Invalid security nonce. Please reload the page and try again.', 'beyond-multisite' ) ),
        'errorInvalidUserRoleData' => esc_js( __( 'Error: Invalid data sent. If you are entering a list of role slugs, do not use any empty spaces and make sure they '
            . 'are slugs and not role names. Read the text description in the Delete Users section for more information.', 'beyond-multisite' ) ),
        'errorWriteExport' => esc_js( __( 'Error: Cannot write to export folder.', 'beyond-multisite' ) ),
        'errorResponse' => esc_js( __( 'Error: We got an empty response.', 'beyond-multisite' ) ),
        'errorData' => esc_js( __( 'Error: Invalid form data sent.', 'beyond-multisite' ) ),
        'errorDeleted' => esc_js( __( 'Error: You are trying to mark as deleted sites that are already marked as deleted.', 'beyond-multisite' ) ),
        'errorArchived' => esc_js( __( 'Error: You are trying to mark as archived sites that are already marked as archived.', 'beyond-multisite' ) ),
        'errorSiteFilled' => esc_js( __( 'Error: The field for the site IDs is filled, but you chose a setting that ignores it.', 'beyond-multisite' ) ),
        'errorNoSites' => esc_js( __( 'Error: Missing sites data.', 'beyond-multisite' ) ),
        'errorMainSite' => esc_js( __( 'Error: You cannot delete the main site of the network.', 'beyond-multisite' ) ),
        'errorAnotherTask' => esc_js( __( 'Error: There is another task in progress. Please reload the page.', 'beyond-multisite' ) ),
        'errorNoSchedule' => esc_js( __( 'Error: Archived, spammed or deleted sites cannot be scheduled for deletion. '
            . 'You can only execute now.', 'beyond-multisite' ) ),
        'errorSiteEmpty' => esc_js( __( 'Error: The field for the site IDs is empty or invalid. '
            . 'The current settings require a comma-separated list of site IDs.', 'beyond-multisite' ) ),
        'errorNumberCustomOptionHigh' => esc_js( sprintf( __( 'Error: You have to enter a whole number between 1 and %d. Please try again.',
            'beyond-multisite' ), 999999999 ) ),
        'errorNumberCustomOptionLow' => esc_js( sprintf( __( 'Error: You have to enter a whole number between 1 and %d. Please try again.',
            'beyond-multisite' ), 9999 ) ),
        'sitesProcessedPrimary' => esc_js( esc_html__( 'Sites processed with primary deletion:', 'beyond-multisite' ) ),
        'sitesProcessedSecondary' => esc_js( esc_html__( 'Sites processed with primary deletion:', 'beyond-multisite' ) ),
        'sitesProcessed' => esc_js( esc_html__( 'Sites processed so far:', 'beyond-multisite' ) ),
        'tablesProcessed' => esc_js( esc_html__( 'Tables processed so far:', 'beyond-multisite' ) ),
        'usersProcessed' => esc_js( esc_html__( 'Users processed so far:', 'beyond-multisite' ) ),
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
    wp_localize_script( 'be_mu_clean_script', 'localizedCleanup', $localize );

    // Enqueued script with localized data
    wp_enqueue_script( 'be_mu_clean_script', '', array(), false, true );
}

// Registers and localizes the javascript file for the admin dashboard of sites scheduled for deletion
function be_mu_clean_site_deletion_script_and_style() {

    // We add the script only if the user is a site administrator or a network administrator and the site is scheduled for deletion
    if ( ( current_user_can( 'administrator' ) || current_user_can( 'manage_network' ) ) && be_mu_clean_site_to_be_deleted() !== false ) {

        // Register the script
        wp_register_script( 'be-mu-clean-site-delete-script', be_mu_plugin_dir_url() . 'scripts/site-deletion.js', array(), BEYOND_MULTISITE_VERSION, false );

        // This is the data we will send from the php to the javascript file
        $localize = array(
            'ajaxNonce' => wp_create_nonce( 'be_mu_clean_cancel_nonce' ),
            'error' => esc_js( esc_html__( 'Error', 'beyond-multisite' ) ),
            'done' => esc_js( esc_html__( 'Done', 'beyond-multisite' ) ),
            'loading' => esc_js( esc_html__( 'Loading...', 'beyond-multisite' ) ),
            'errorUserServerFail' => esc_js( __( 'Error: Unexpected server error.', 'beyond-multisite' ) ),
        );

        // We localize the script - we send php data to the javascript file
        wp_localize_script( 'be-mu-clean-site-delete-script', 'localizedSiteDeletion', $localize );

        // Enqueued script with localized data
        wp_enqueue_script( 'be-mu-clean-site-delete-script', '', array(), false, true );

        // Register the style for the site deletion message
        wp_register_style( 'be-mu-clean-site-delete-style', be_mu_plugin_dir_url() . 'styles/site-deletion.css', false, BEYOND_MULTISITE_VERSION );

        // Enqueue the style for the site deletion message
        wp_enqueue_style( 'be-mu-clean-site-delete-style' );
    }
}

// This function is executed when the Network Cleanup subpage is opened
function be_mu_cleanup_subpage() {

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'beyond-multisite' ) );
    }
    ?>

    <div class="wrap">

        <?php be_mu_header_super_admin_page( __( 'Network Cleanup', 'beyond-multisite' ) ); ?>

        <?php

        // We need this to connect to the database
        global $wpdb;

        // The database table prefix for the main network site
        $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

        // The database table for the scheduled site deletions
        $db_table_deletions = $main_blog_prefix . 'be_mu_scheduled_site_deletions';

        // The database table for the email notifications about site deletions
        $db_table_emails = $main_blog_prefix . 'be_mu_site_deletion_emails';

        // The database table with cleanup task data
        $db_table_cleanup = $main_blog_prefix . 'be_mu_cleanup';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $db_table_deletions . "'" ) !== $db_table_deletions
            || $wpdb->get_var( "SHOW TABLES LIKE '" . $db_table_emails . "'" ) !== $db_table_emails
            || $wpdb->get_var( "SHOW TABLES LIKE '" . $db_table_cleanup . "'" ) !== $db_table_cleanup ) {
            ?>
            <div class="be-mu-warning-box be-mu-clean-warning">
                <?php
                printf(
                    esc_html__( '%sERROR:%s At least one database table is missing. '
                        . 'Please deactivate the plugin and activate it to trigger the database tables creation again.', 'beyond-multisite' ), '<b>', '</b>'
                );
                ?>
            </div>
            <?php

        } else {

            // We will not show the forms for comment deletion and revision deletion if this is a self-refreshing page used to speed up a site deletion task
            if ( ! isset( $_GET['be-mu-auto-refresh'] ) ) {

            ?>

            <div class="be-mu-warning-box be-mu-clean-warning">
                <?php
                printf(
                    esc_html__( '%1$sWARNING:%2$s The features on this page can %3$sdelete permanently%4$s comments, revisions, users '
                        . 'and sites along with their uploaded files. %3$sMake a full backup%4$s before you continue. '
                        . 'Be very careful and proceed at your own risk.', 'beyond-multisite' ),
                    '<b>', '</b>', '<u>', '</u>'
                );
                ?>
            </div>

            <div class="be-mu-white-box be-mu-cleanup-box">
                <h3>
                    <?php esc_html_e( 'Delete Comments', 'beyond-multisite' ); ?>
                </h3>
                <i>
                    <?php
                    printf(
                        esc_html__( 'Important Information (%1$sLearn more...%2$s):', 'beyond-multisite' ),
                        '<a href="https://nikolaydev.com/beyond-multisite-documentation/cleanup/#comments" target="_blank">', '</a>'
                    );
                    ?>
                </i>
                <br />
                <ul class="be-mu-ul">
                    <li>
                        <i>
                            <?php
                            printf(
                                esc_html__( 'Comments are deleted directly from the database (%1$shooks%2$s related '
                                    . 'to deleting a comment will not run)', 'beyond-multisite' ),
                                '<a href="https://codex.wordpress.org/Writing_a_Plugin#WordPress_Plugin_Hooks" target="_blank">', '</a>'
                            );
                            ?>
                        </i>
                    </li>
                    <li>
                        <i>
                            <?php
                            printf(
                                esc_html__( 'All %1$snested comments%2$s of deleted comments are also deleted and are not counted in the results',
                                    'beyond-multisite' ),
                                '<a href="https://codex.wordpress.org/Comments_in_WordPress#Comment_Display" target="_blank">', '</a>'
                            );
                            ?>
                        </i>
                    </li>
                    <li>
                        <i>
                            <?php esc_html_e( 'Only regular comments (with an empty type or "comment" type) are deleted', 'beyond-multisite' ); ?>
                        </i>
                    </li>
                    <li>
                        <i>
                            <?php
                            esc_html_e( 'All comment meta data of comments that do not exist is also deleted (affects all comment types)', 'beyond-multisite' );
                            ?>
                        </i>
                    </li>
                </ul>
                <ul>
                    <li>
                        <label for="be-mu-clean-comment-status">
                            <?php esc_html_e( 'Comment status:', 'beyond-multisite' ); ?>
                        </label>
                        <?php
                        be_mu_select(
                            'be-mu-clean-comment-status',
                            array( 'Any', 'Pending', 'Approved', 'Spammed', 'Trashed', 'Post trashed' ),
                            array(
                                __( 'Any', 'beyond-multisite' ),
                                __( 'Pending', 'beyond-multisite' ),
                                __( 'Approved', 'beyond-multisite' ),
                                __( 'Spammed', 'beyond-multisite' ),
                                __( 'Trashed', 'beyond-multisite' ),
                                __( 'Post trashed', 'beyond-multisite' ),
                            )
                        );
                        ?>
                        <span class="be-mu-tooltip">
                            <span class="be-mu-info">i</span>
                            <span class="be-mu-tooltip-text">
                                <?php
                                esc_html_e( 'Hint: "Post trashed" comments are for posts or pages that are in the trash. '
                                    . 'These comments are not visible on the "edit comments" admin page.', 'beyond-multisite' );
                                ?>
                            </span>
                        </span>
                    </li>
                    <li>
                        <label for="be-mu-clean-comment-url-count"><?php esc_html_e( 'Comment URLs:', 'beyond-multisite' ); ?></label>
                        <?php
                        be_mu_select(
                            'be-mu-clean-comment-url-count',
                            array( 'Any', 'Filled URL field', 'Has a URL in the text',
                                'Filled URL field or URL in the text', 'Filled URL field and URL in the text' ),
                            array(
                                __( 'Any', 'beyond-multisite' ),
                                __( 'Filled URL field', 'beyond-multisite' ),
                                __( 'Has a URL in the text', 'beyond-multisite' ),
                                __( 'Filled URL field or URL in the text', 'beyond-multisite' ),
                                __( 'Filled URL field and URL in the text', 'beyond-multisite' ),
                            )
                        );
                        ?>
                    </li>
                    <li>
                        <label for="be-mu-clean-comment-datetime">
                            <?php esc_html_e( 'Comment date/time:', 'beyond-multisite' ); ?>
                        </label>
                        <?php
                        be_mu_select(
                            'be-mu-clean-comment-datetime',
                            array( 'Any', 'Older than 7 days', 'Older than 30 days', 'Older than 90 days', 'Older than 365 days', 'Older than [X] days',
                                'In the last 7 days', 'In the last 30 days', 'In the last 90 days', 'In the last 365 days', 'In the last [X] days' ),
                            array(
                                __( 'Any', 'beyond-multisite' ),
                                sprintf( __( 'Older than %d days', 'beyond-multisite' ), 7 ),
                                sprintf( __( 'Older than %d days', 'beyond-multisite' ), 30 ),
                                sprintf( __( 'Older than %d days', 'beyond-multisite' ), 90 ),
                                sprintf( __( 'Older than %d days', 'beyond-multisite' ), 365 ),
                                str_replace( '%d', '[X]', __( 'Older than %d days', 'beyond-multisite' ) ),
                                sprintf( __( 'In the last %d days', 'beyond-multisite' ), 7 ),
                                sprintf( __( 'In the last %d days', 'beyond-multisite' ), 30 ),
                                sprintf( __( 'In the last %d days', 'beyond-multisite' ), 90 ),
                                sprintf( __( 'In the last %d days', 'beyond-multisite' ), 365 ),
                                str_replace( '%d', '[X]', __( 'In the last %d days', 'beyond-multisite' ) ),
                            )
                        );
                        ?>
                    </li>
                    <li>
                        <label for="be-mu-clean-comment-affect-sites-comment-amount">
                            <?php esc_html_e( 'Affect sites with:', 'beyond-multisite' ); ?>
                        </label>
                        <?php
                        be_mu_select(
                            'be-mu-clean-comment-affect-sites-comment-amount',
                            array( 'Any amount of', 'At least 10', 'At least 100', 'At least 1000', 'At least 10000', 'At least [X]' ),
                            array(
                                __( 'Any amount of', 'beyond-multisite' ),
                                sprintf( __( 'At least %d', 'beyond-multisite' ), 10 ),
                                sprintf( __( 'At least %d', 'beyond-multisite' ), 100 ),
                                sprintf( __( 'At least %d', 'beyond-multisite' ), 1000 ),
                                sprintf( __( 'At least %d', 'beyond-multisite' ), 10000 ),
                                str_replace( '%d', '[X]', __( 'At least %d', 'beyond-multisite' ) ),
                            )
                        );
                        be_mu_select(
                            'be-mu-clean-comment-affect-sites-comment-status',
                            array( 'comments in total', 'pending comments', 'approved comments', 'spammed comments', 'trashed comments' ),
                            array(
                                __( 'comments in total', 'beyond-multisite' ),
                                __( 'pending comments', 'beyond-multisite' ),
                                __( 'approved comments', 'beyond-multisite' ),
                                __( 'spammed comments', 'beyond-multisite' ),
                                __( 'trashed comments', 'beyond-multisite' ),
                            )
                        );
                        ?>
                    </li>
                    <li>
                        <label for="be-mu-clean-comment-affect-sites-id-option">
                            <?php esc_html_e( 'Affect sites with:', 'beyond-multisite' ); ?>
                        </label>
                        <?php
                        be_mu_select(
                            'be-mu-clean-comment-affect-sites-id-option',
                            array( 'Any site ID', 'Only these site IDs:', 'All except these site IDs:' ),
                            array(
                                __( 'Any site ID (All sites)', 'beyond-multisite' ),
                                __( 'Only these site IDs:', 'beyond-multisite' ),
                                __( 'All except these site IDs:', 'beyond-multisite' ),
                            )
                        );
                        echo '&nbsp;';
                        be_mu_input_text( 'be-mu-clean-comment-affect-sites-ids' );
                        ?>
                        <span class="be-mu-tooltip">
                            <span class="be-mu-info">i</span>
                            <span class="be-mu-tooltip-text">
                                <?php esc_html_e( 'Accepts: Comma-separated numbers or an empty string.', 'beyond-multisite' ); ?>
                            </span>
                        </span>
                    </li>
                </ul>
                <p>
                    <input class="button" onclick="cleanupStartComments( 'preview' )" type="button"
                        value="<?php esc_attr_e( 'Preview Comment Deletion', 'beyond-multisite' ); ?>" />
                    <input class="button button-primary" onclick="cleanupStartComments( 'delete' )" type="button"
                        value="<?php esc_attr_e( 'Execute Comment Deletion!', 'beyond-multisite' ); ?>" />
                </p>
            </div>

            <div class="be-mu-white-box be-mu-cleanup-box">
                <h3>
                    <?php esc_html_e( 'Delete Revisions', 'beyond-multisite' ); ?>
                </h3>
                <p>
                    <i>
                        <?php
                        esc_html_e( 'What are revisions? Revisions are old versions of posts (including pages and some custom post types).', 'beyond-multisite' );
                        ?>
                        <a href="https://codex.wordpress.org/Revisions" target="_blank">
                            <?php esc_html_e( 'Learn more...', 'beyond-multisite' ); ?>
                        </a>
                    </i>
                </p>
                <ul>
                    <li>
                        <label for="be-mu-clean-revision-datetime">
                            <?php esc_html_e( 'Revision date/time:', 'beyond-multisite' ); ?>
                        </label>
                        <?php
                        be_mu_select(
                            'be-mu-clean-revision-datetime',
                            array( 'Any', 'Older than 1 day', 'Older than 7 days', 'Older than 30 days', 'Older than 90 days',
                                'Older than 365 days', 'Older than 730 days', 'Older than [X] days' ),
                            array(
                                __( 'Any', 'beyond-multisite' ),
                                sprintf( __( 'Older than %d day', 'beyond-multisite' ), 1 ),
                                sprintf( __( 'Older than %d days', 'beyond-multisite' ), 7 ),
                                sprintf( __( 'Older than %d days', 'beyond-multisite' ), 30 ),
                                sprintf( __( 'Older than %d days', 'beyond-multisite' ), 90 ),
                                sprintf( __( 'Older than %1$s days (%2$s year)', 'beyond-multisite' ), 365, 1 ),
                                sprintf( __( 'Older than %1$s days (%2$s years)', 'beyond-multisite' ), 730, 2 ),
                                str_replace( '%d', '[X]', __( 'Older than %d days', 'beyond-multisite' ) ),
                            )
                        );
                        ?>
                    </li>
                    <li>
                        <label for="be-mu-clean-revision-exclude">
                            <?php esc_html_e( 'Exclude from deletion:', 'beyond-multisite' ); ?>
                        </label>
                        <?php
                        be_mu_select(
                            'be-mu-clean-revision-exclude',
                            array( 'None', 'The 3 most recent for each post', 'The 5 most recent for each post',
                                'The 10 most recent for each post', 'The 50 most recent for each post', 'The 100 most recent for each post',
                                'The [X] most recent for each post' ),
                            array(
                                __( 'None', 'beyond-multisite' ),
                                sprintf( __( 'The %d most recent for each post', 'beyond-multisite' ), 3 ),
                                sprintf( __( 'The %d most recent for each post', 'beyond-multisite' ), 5 ),
                                sprintf( __( 'The %d most recent for each post', 'beyond-multisite' ), 10 ),
                                sprintf( __( 'The %d most recent for each post', 'beyond-multisite' ), 50 ),
                                sprintf( __( 'The %d most recent for each post', 'beyond-multisite' ), 100 ),
                                str_replace( '%d', '[X]', __( 'The %d most recent for each post', 'beyond-multisite' ) ),
                            )
                        );
                        ?>
                    </li>
                    <li>
                        <label for="be-mu-clean-revision-affect-sites-id-option">
                            <?php esc_html_e( 'Affect sites with:', 'beyond-multisite' ); ?>
                        </label>
                        <?php
                        be_mu_select(
                            'be-mu-clean-revision-affect-sites-id-option',
                            array( 'Any site ID', 'Only these site IDs:', 'All except these site IDs:' ),
                            array(
                                __( 'Any site ID (All sites)', 'beyond-multisite' ),
                                __( 'Only these site IDs:', 'beyond-multisite' ),
                                __( 'All except these site IDs:', 'beyond-multisite' ),
                            )
                        );
                        echo '&nbsp;';
                        be_mu_input_text( 'be-mu-clean-revision-affect-sites-ids' );
                        ?>
                        <span class="be-mu-tooltip">
                            <span class="be-mu-info">i</span>
                            <span class="be-mu-tooltip-text">
                                <?php esc_html_e( 'Accepts: Comma-separated numbers or an empty string.', 'beyond-multisite' ); ?>
                            </span>
                        </span>
                    </li>
                </ul>
                <p>
                    <input class="button" onclick="cleanupStartRevisions( 'preview' )" type="button"
                        value="<?php esc_attr_e( 'Preview Revision Deletion', 'beyond-multisite' ); ?>" />
                    <input class="button button-primary" onclick="cleanupStartRevisions( 'delete' )" type="button"
                        value="<?php esc_attr_e( 'Execute Revision Deletion!', 'beyond-multisite' ); ?>" />
                </p>
            </div>

            <?php

            // This is the end of the if that hides comment deletion and revision deletion forms if this is a self-refreshing page
            }

            ?>

            <div class="be-mu-white-box be-mu-cleanup-box">
                <h3>
                    <?php esc_html_e( 'Delete Sites', 'beyond-multisite' ); ?><a name="site"></a>
                </h3>

                <?php

                // We will not show the important information if this is a self-refreshing page used to speed up a site deletion task
                if ( ! isset( $_GET['be-mu-auto-refresh'] ) ) {

                ?>

                <i>
                    <?php esc_html_e( 'Important Information', 'beyond-multisite' ); ?>
                    (<a href="https://nikolaydev.com/beyond-multisite-documentation/cleanup/#sites"
                        target="_blank"><?php esc_html_e( 'Learn more...', 'beyond-multisite' ); ?></a>):
                </i>
                <br />
                <ul class="be-mu-ul">
                    <li>
                        <i>
                            <?php
                            esc_html_e( 'You can either permanently delete sites (delete database tables and uploaded files) or just mark them as deleted',
                                'beyond-multisite' );
                            ?>
                        </i>
                    </li>
                    <li>
                        <i>
                            <?php
                            esc_html_e( 'You can either delete now or allow site admins to cancel the deletion within a given amount of days',
                                'beyond-multisite' );
                            ?>
                        </i>
                    </li>
                    <li>
                        <i>
                            <?php
                            esc_html_e( 'If you choose to schedule deletion, the admins of the affected sites will be notified. Also the '
                                . 'sites that are marked as archived, spam or deleted will be excluded.', 'beyond-multisite' );
                            ?>
                        </i>
                    </li>
                    <li>
                        <i>
                            <?php
                            printf(
                                esc_html__( 'Scheduled site deletion tasks will run automatically in the background using the %1$sWordPress cron%2$s feature',
                                    'beyond-multisite' ),
                                '<a href="https://developer.wordpress.org/plugins/cron/" target="_blank">', '</a>'
                            );
                            ?>
                        </i>
                    </li>
                    <li>
                        <i>
                            <?php
                            printf(
                                esc_html__( 'Before scheduling a site deletion task please check the %1$ssettings%2$s for the Cleanup module',
                                    'beyond-multisite' ),
                                '<a href="' . esc_url( network_admin_url( 'admin.php?page=beyond-multisite' ) ) . '">', '</a>'
                            );
                            ?>
                        </i>
                    </li>
                </ul>

                <?php

                // This is the end of the if that hides the important information if this is a self-refreshing page used to speed up a site deletion task
                }

                // We try to get the task id of the tasks (should be one or none) for scheduled site deletion
                $results_multi_array = $wpdb->get_results( "SELECT DISTINCT task_id FROM " . $db_table_emails . " WHERE 1", ARRAY_A );

                // If there is a task for scheduled site deletion we will show its statistics instead of the form
                if ( ! empty( $results_multi_array ) ) {

                    // Should be only one task
                    foreach ( $results_multi_array as $results ) {

                        // Set the task id
                        $task_id = $results['task_id'];

                        // Set the mode of the button we will show to cancel
                        $button_mode = 'cancel';

                        // Set the value of the button we will show
                        $button_value = __( 'Cancel Site Deletion Task', 'beyond-multisite' );

                        // Set the var for the button-primary class of the button
                        $primary_class = '';

                        // In the next several lines we are just getting some statistics about the task from the email notifications table
                        $total_emails_count = $wpdb->get_var( $wpdb->prepare( "SELECT count( row_id ) FROM " . $db_table_emails
                            . " WHERE task_id = %s AND status != 'no-admins' AND status != 'no-admins-done'", $task_id ) );
                        $sent_emails_count = $wpdb->get_var( $wpdb->prepare( "SELECT count( row_id ) FROM " . $db_table_emails
                            . " WHERE task_id = %s AND status = 'sent'", $task_id ) );
                        $skipped_emails_count = $wpdb->get_var( $wpdb->prepare( "SELECT count( row_id ) FROM " . $db_table_emails
                            . " WHERE task_id = %s AND status = 'skipped'", $task_id ) );
                        $failed_emails_count = $wpdb->get_var( $wpdb->prepare( "SELECT count( row_id ) FROM " . $db_table_emails
                            . " WHERE task_id = %s AND status = 'failed'", $task_id ) );
                        $total_sites_count = $wpdb->get_var( $wpdb->prepare( "SELECT count( DISTINCT site_id ) FROM " . $db_table_emails
                            . " WHERE task_id = %s", $task_id ) );
                        $deletion_type = $wpdb->get_var( $wpdb->prepare( "SELECT site_delete_type FROM " . $db_table_emails
                            . " WHERE task_id = %s", $task_id ) );

                        // We are still getting statistics about the task but from the table with the site deletions now
                        $cancelled_sites_count = $wpdb->get_var( $wpdb->prepare( "SELECT count( row_id ) FROM " . $db_table_deletions
                            . " WHERE task_id = %s AND status = 'cancelled'", $task_id ) );
                        $deletion_starts_unix = $wpdb->get_var( $wpdb->prepare( "SELECT MIN( unix_time_to_be_deleted ) FROM " . $db_table_deletions
                            . " WHERE task_id = %s AND status != 'cancelled'", $task_id ) );
                        $scheduled_sites_count = $wpdb->get_var( $wpdb->prepare( "SELECT count( row_id ) FROM " . $db_table_deletions
                            . " WHERE task_id = %s AND status = 'scheduled'", $task_id ) );
                        $deleted_sites_count = $wpdb->get_var( $wpdb->prepare( "SELECT count( row_id ) FROM " . $db_table_deletions
                            . " WHERE task_id = %s AND status = 'deleted'", $task_id ) );

                        $task_message = __( 'There is an active scheduled site deletion task.', 'beyond-multisite' );

                        // Here based on the statistics we figure out what is the status of each stage of the task
                        if ( $total_emails_count != ( $sent_emails_count + $failed_emails_count + $skipped_emails_count ) ) {

                            $stage_1_status = __( 'Active', 'beyond-multisite' );
                            $stage_2_status = __( 'Waiting for Stage 1', 'beyond-multisite' );
                            $stage_3_status = __( 'Waiting for Stage 2', 'beyond-multisite' );
                        } else {

                            $stage_1_status = __( 'Done', 'beyond-multisite' );

                            if ( time() < intval( $deletion_starts_unix ) ) {
                                $stage_2_status = __( 'Active', 'beyond-multisite' );
                                $stage_3_status = __( 'Waiting for Stage 2', 'beyond-multisite' );
                            } else {
                                $stage_2_status = __( 'Done', 'beyond-multisite' );

                                if ( 0 != $scheduled_sites_count ) {
                                    $stage_3_status = __( 'Active', 'beyond-multisite' );
                                } else {
                                    $stage_3_status = __( 'Done', 'beyond-multisite' );
                                    $button_mode = 'complete';
                                    $button_value = __( 'Remove Task', 'beyond-multisite' );
                                    $primary_class = 'button-primary';
                                    $task_message = __( 'There is a finished scheduled site deletion task.', 'beyond-multisite' );
                                }
                            }
                        }

                        // Here we figure out what should be the deletion starts string based on the deletion time
                        if ( intval( $deletion_starts_unix ) > 0 && intval( $deletion_starts_unix ) > time() ) {
                            $deletion_starts_string = be_mu_unixtime_to_wp_datetime( intval( $deletion_starts_unix ) ) . ' ' . be_mu_get_wp_time_zone();
                        } else {
                            if ( __( 'Done', 'beyond-multisite' ) == $stage_2_status ) {
                                $deletion_starts_string = __( 'Already started', 'beyond-multisite' );
                            } else {
                                $deletion_starts_string = __( 'Waiting for Stage 1', 'beyond-multisite' );
                            }
                        }

                        ?>

                        <p>
                            <b>
                                <?php echo esc_html( $task_message ); ?>
                            </b>
                        </p>
                        <p>
                            <b>
                                <?php esc_html_e( 'Task statistics', 'beyond-multisite' ); ?>
                            </b>
                        </p>
                        <table class="be-mu-clean-task-stats-table">
                            <tr class="be-mu-tr1">
                                <td class="be-mu-col1">
                                    <?php esc_html_e( 'Total notifications to send:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col2">
                                    <?php echo intval( $total_emails_count ); ?>
                                </td>
                                <td class="be-mu-col3">
                                    <?php esc_html_e( 'Total sites to delete:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col4">
                                    <?php echo intval( $total_sites_count ); ?>
                                </td>
                                <td class="be-mu-col5">
                                    <?php esc_html_e( 'Deletion type:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col6">
                                    <?php echo esc_html( be_mu_translate_deletion_type( $deletion_type ) ); ?>
                                </td>
                            </tr>
                            <tr class="be-mu-tr2">
                                <td class="be-mu-col1">
                                    <?php esc_html_e( 'Sent notifications:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col2">
                                    <?php echo intval( $sent_emails_count ); ?>
                                </td>
                                <td class="be-mu-col3">
                                    <?php esc_html_e( 'Scheduled site deletions:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col4">
                                    <?php
                                    echo intval( $scheduled_sites_count )
                                        . ' (<a href="javascript:cleanupTaskViewSites( \'' . esc_js( esc_attr( $task_id ) ) . '\', \'scheduled\', \'yes\' )">'
                                        . esc_html__( 'View', 'beyond-multisite' ) . '</a>)';
                                    ?>
                                </td>
                                <td class="be-mu-col5">
                                    <?php esc_html_e( 'Deletion starts*:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col6">
                                    <?php echo esc_html( $deletion_starts_string ); ?>
                                </td>
                            </tr>
                            <tr class="be-mu-tr1">
                                <td class="be-mu-col1">
                                    <?php esc_html_e( 'Notifications skipped**:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col2">
                                    <?php echo intval( $skipped_emails_count ); ?>
                                </td>
                                <td class="be-mu-col3">
                                    <?php esc_html_e( 'Cancelled site deletions:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col4">
                                    <?php
                                    echo intval( $cancelled_sites_count )
                                        . ' (<a href="javascript:cleanupTaskViewSites( \'' . esc_js( esc_attr( $task_id ) ) . '\', \'cancelled\', \'yes\' )">'
                                        . esc_html__( 'View', 'beyond-multisite' ) . '</a>)';
                                    ?>
                                </td>
                                <td class="be-mu-col5">
                                    <?php esc_html_e( 'Task ID:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col6">
                                    <?php echo esc_html( $task_id ); ?>
                                </td>
                            </tr>
                            <tr class="be-mu-tr2">
                                <td class="be-mu-col1">
                                    <?php esc_html_e( 'Notifications failed:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col2">
                                    <?php echo intval( $failed_emails_count ); ?>
                                </td>
                                <td class="be-mu-col3">
                                    <?php esc_html_e( 'Deleted sites:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col4">
                                    <?php echo intval( $deleted_sites_count ); ?>
                                </td>
                                <td class="be-mu-col5">
                                    <?php esc_html_e( 'Cron jobs:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col6">
                                    <?php
                                    if ( ! wp_next_scheduled( 'be_mu_clean_event_hook_delete_sites' ) && ! wp_next_scheduled( 'be_mu_clean_event_hook_send_emails' ) ) {
                                        echo "<span class='be-mu-red'>" . esc_html__( 'None found!', 'beyond-multisite' ) . "</span>";
                                    } elseif ( ! wp_next_scheduled( 'be_mu_clean_event_hook_delete_sites' ) || ! wp_next_scheduled( 'be_mu_clean_event_hook_send_emails' ) ) {
                                        echo "<span class='be-mu-red'>" . esc_html__( 'One missing!', 'beyond-multisite' ) . "</span>";
                                    } else {
                                        echo esc_html__( 'Active', 'beyond-multisite' );
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>

                        <!-- This is the mobile version of the previous table -->
                        <table class="be-mu-clean-task-stats-table-mobile">
                            <tr class="be-mu-tr1">
                                <td class="be-mu-col1">
                                    <?php esc_html_e( 'Total notifications to send:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col2">
                                    <?php echo intval( $total_emails_count ); ?>
                                </td>
                            </tr>
                            <tr class="be-mu-tr2">
                                <td class="be-mu-col1">
                                    <?php esc_html_e( 'Sent notifications:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col2">
                                    <?php echo intval( $sent_emails_count ); ?>
                                </td>
                            </tr>
                            <tr class="be-mu-tr1">
                                <td class="be-mu-col1">
                                    <?php esc_html_e( 'Notifications skipped**:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col2">
                                    <?php echo intval( $skipped_emails_count ); ?>
                                </td>
                            </tr>
                            <tr class="be-mu-tr2">
                                <td class="be-mu-col1">
                                    <?php esc_html_e( 'Notifications failed:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col2">
                                    <?php echo intval( $failed_emails_count ); ?>
                                </td>
                            </tr>
                            <tr class="be-mu-tr1">
                                <td class="be-mu-col1">
                                    <?php esc_html_e( 'Total sites to delete:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col2">
                                    <?php echo intval( $total_sites_count ); ?>
                                </td>
                            </tr>
                            <tr class="be-mu-tr2">
                                <td class="be-mu-col1">
                                    <?php esc_html_e( 'Scheduled site deletions:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col2">
                                    <?php
                                    echo intval( $scheduled_sites_count )
                                        . ' (<a href="javascript:cleanupTaskViewSites( \'' . esc_js( esc_attr( $task_id ) ) . '\', \'scheduled\', \'yes\' )">'
                                        . esc_html__( 'View', 'beyond-multisite' ) . '</a>)';
                                    ?>
                                </td>
                            </tr>
                            <tr class="be-mu-tr1">
                                <td class="be-mu-col1">
                                    <?php esc_html_e( 'Cancelled site deletions:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col2">
                                    <?php
                                    echo intval( $cancelled_sites_count )
                                        . ' (<a href="javascript:cleanupTaskViewSites( \'' . esc_js( esc_attr( $task_id ) ) . '\', \'cancelled\', \'yes\' )">'
                                        . esc_html__( 'View', 'beyond-multisite' ) . '</a>)';
                                    ?>
                                </td>
                            </tr>
                            <tr class="be-mu-tr2">
                                <td class="be-mu-col1">
                                    <?php esc_html_e( 'Deleted sites:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col2">
                                    <?php echo intval( $deleted_sites_count ); ?>
                                </td>
                            </tr>
                            <tr class="be-mu-tr1">
                                <td class="be-mu-col1">
                                    <?php esc_html_e( 'Deletion type:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col2">
                                    <?php echo esc_html( be_mu_translate_deletion_type( $deletion_type ) ); ?>
                                </td>
                            </tr>
                            <tr class="be-mu-tr2">
                                <td class="be-mu-col1">
                                    <?php esc_html_e( 'Deletion starts*:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col2">
                                    <?php echo esc_html( $deletion_starts_string ); ?>
                                </td>
                            </tr>
                            <tr class="be-mu-tr1">
                                <td class="be-mu-col1">
                                    <?php esc_html_e( 'Task ID:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col2">
                                    <?php echo esc_html( $task_id ); ?>
                                </td>
                            </tr>
                        </table>
                        <p>
                            <b>
                                <?php esc_html_e( 'Task progress', 'beyond-multisite' ); ?>
                            </b>
                        </p>
                        <table class="be-mu-clean-task-progr-table">
                            <tr class="be-mu-tr1">
                                <td class="be-mu-col1">
                                    <?php esc_html_e( 'Stage 1:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col2">
                                    <?php echo esc_html( $stage_1_status ); ?>
                                </td>
                                <td class="be-mu-col3">
                                    <?php esc_html_e( 'Description: Notifying administrators and scheduling deletions', 'beyond-multisite' ); ?>
                                </td>
                            </tr>
                            <tr class="be-mu-tr2">
                                <td class="be-mu-col1">
                                    <?php esc_html_e( 'Stage 2:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col2">
                                    <?php echo esc_html( $stage_2_status ); ?>
                                </td>
                                <td class="be-mu-col3">
                                    <?php esc_html_e( 'Description: Waiting for deletion cancellations', 'beyond-multisite' ); ?>
                                </td>
                            </tr>
                            <tr class="be-mu-tr1">
                                <td class="be-mu-col1">
                                    <?php esc_html_e( 'Stage 3:', 'beyond-multisite' ); ?>
                                </td>
                                <td class="be-mu-col2">
                                    <?php echo esc_html( $stage_3_status ); ?>
                                </td>
                                <td class="be-mu-col3">
                                    <?php esc_html_e( 'Description: Executing site deletions', 'beyond-multisite' ); ?>
                                </td>
                            </tr>
                        </table>

                        <?php

                        // This is the cancel or complete task button along with the loading gif
                        echo '<p>'
                            . '<input class="button ' . esc_attr( $primary_class ) . '" onclick="cleanupCancelOrCompleteSiteDeletionTask( \''
                            . esc_js( esc_attr( $task_id ) ) . '\', \'' . esc_js( esc_attr( $button_mode ) )
                            . '\' )" type="button" value="' . esc_attr( $button_value )
                            . '" />&nbsp;<img id="be-mu-clean-loading-cancel-deletion-task" src="'
                            . esc_url( be_mu_img_url( 'loading.gif' ) ) . '" />'
                            . '</p>';

                        // Based on a variable in the URL it shows a link to a page that reloads automatically or a link to the normal version of the page
                        if ( isset( $_GET['be-mu-auto-refresh'] ) ) {
                            echo '<p>'
                                . '<a href="' . esc_url( network_admin_url( 'index.php?page=be_mu_cleanup#site' ) ) . '">'
                                . '<b>'
                                . esc_html__( 'Stop refreshing the page', 'beyond-multisite' )
                                . '</b>'
                                . '</a>'
                                . '</p>';
                        } else {
                            echo '<p>'
                                . '<a href="' . esc_url( network_admin_url( 'index.php?page=be_mu_cleanup' ) ) . '&be-mu-auto-refresh">'
                                . esc_html__( 'Force chosen speed', 'beyond-multisite' )
                                . '</a>&nbsp;'
                                . '<span class="be-mu-tooltip">'
                                . '<span class="be-mu-info">i</span>'
                                . '<span class="be-mu-tooltip-text">'
                                . esc_html__( 'If you want to force the maximum email sending speed that you chose from the module settings, you can '
                                . 'make this page refresh automatically once every 15 seconds.', 'beyond-multisite' )
                                . '</span>'
                                . '</span>'
                                . '</p>';
                        }

                        printf(
                            esc_html__( '%1$s* The date and time have been converted to your chosen format and timezone. You can change them in the '
                                . '%2$sGeneral Options%3$s.%4$s** If a site scheduled for deletion is marked as archived, spam or deleted after the '
                                . 'task has been created, the site admins will not be notified. '
                                . 'The site will still be affected by deletion though.%5$s', 'beyond-multisite' ),
                            '<p>',
                            '<a href="' . esc_url( admin_url( 'options-general.php' ) ) . '">',
                            '</a>',
                            '<br />',
                            '</p>'
                        );
                    }

                // This else means that there are no active tasks for scheduled site deletions so we show the form
                } else {

                ?>
                    <ul>
                        <?php
                        do_action( 'beyond-multisite-delete-sites-extra-fields-output' );
                        ?>
                        <li>
                            <label for="be-mu-clean-site-attributes">
                                <?php esc_html_e( 'Attributes:', 'beyond-multisite' ); ?>
                            </label>
                            <?php
                            be_mu_select(
                                'be-mu-clean-site-attributes',
                                array( 'Any', 'Public', 'Deleted', 'Spam', 'Archived', 'Mature',
                                    'Not public', 'Not deleted', 'Not spam', 'Not archived', 'Not mature' ),
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
                                )
                            );
                            ?>
                        </li>
                        <li>
                            <label for="be-mu-clean-site-registered">
                                <?php esc_html_e( 'Registered:', 'beyond-multisite' ); ?>
                            </label>
                            <?php
                            be_mu_select(
                                'be-mu-clean-site-registered',
                                array( 'Any', 'Older than 7 days', 'Older than 30 days', 'Older than 90 days', 'Older than 365 days',
                                    'Older than 730 days', 'Older than 1095 days', 'Older than 1825 days',
                                    'Older than [X] days',
                                    'In the last 7 days', 'In the last 30 days', 'In the last 90 days', 'In the last 365 days',
                                    'In the last 730 days', 'In the last 1095 days', 'In the last 1825 days',
                                    'In the last [X] days' ),
                                array(
                                    __( 'Any', 'beyond-multisite' ),
                                    sprintf( __( 'Older than %d days', 'beyond-multisite' ), 7 ),
                                    sprintf( __( 'Older than %d days', 'beyond-multisite' ), 30 ),
                                    sprintf( __( 'Older than %d days', 'beyond-multisite' ), 90 ),
                                    sprintf( __( 'Older than %1$d days (%2$d year)', 'beyond-multisite' ), 365, 1 ),
                                    sprintf( __( 'Older than %1$d days (%2$d years)', 'beyond-multisite' ), 730, 2 ),
                                    sprintf( __( 'Older than %1$d days (%2$d years)', 'beyond-multisite' ), 1095, 3 ),
                                    sprintf( __( 'Older than %1$d days (%2$d years)', 'beyond-multisite' ), 1825, 5 ),
                                    str_replace( '%d', '[X]', __( 'Older than %d days', 'beyond-multisite' ) ),
                                    sprintf( __( 'In the last %d days', 'beyond-multisite' ), 7 ),
                                    sprintf( __( 'In the last %d days', 'beyond-multisite' ), 30 ),
                                    sprintf( __( 'In the last %d days', 'beyond-multisite' ), 90 ),
                                    sprintf( __( 'In the last %1$d days (%2$d year)', 'beyond-multisite' ), 365, 1 ),
                                    sprintf( __( 'In the last %1$d days (%2$d years)', 'beyond-multisite' ), 730, 2 ),
                                    sprintf( __( 'In the last %1$d days (%2$d years)', 'beyond-multisite' ), 1095, 3 ),
                                    sprintf( __( 'In the last %1$d days (%2$d years)', 'beyond-multisite' ), 1825, 5 ),
                                    str_replace( '%d', '[X]', __( 'In the last %d days', 'beyond-multisite' ) ),
                                )
                            );
                            ?>
                        </li>
                        <li>
                            <label for="be-mu-clean-site-updated">
                                <?php esc_html_e( 'Last updated:', 'beyond-multisite' ); ?>
                            </label>
                            <?php
                            be_mu_select(
                                'be-mu-clean-site-updated',
                                array( 'Any', 'Less than 5 min after registration', 'Less than 30 min after registration', 'Less than [X] min after registration',
                                    'Less than 2 hours after registration', 'Less than 6 hours after registration', 'Less than [X] hours after registration',
                                    'Less than 1 day after registration', 'Less than 3 days after registration',
                                    'Less than 7 days after registration', 'Less than [X] days after registration', 'Older than 7 days', 'Older than 30 days',
                                    'Older than 90 days', 'Older than 365 days', 'Older than 730 days', 'Older than 1095 days',
                                    'Older than 1825 days', 'Older than [X] days',
                                    'In the last 7 days', 'In the last 30 days', 'In the last 90 days',
                                    'In the last 365 days', 'In the last 730 days', 'In the last 1095 days',
                                    'In the last 1825 days', 'In the last [X] days' ),
                                array(
                                    __( 'Any', 'beyond-multisite' ),
                                    sprintf( __( 'Less than %d min after registration', 'beyond-multisite' ), 5 ),
                                    sprintf( __( 'Less than %d min after registration', 'beyond-multisite' ), 30 ),
                                    str_replace( '%d', '[X]', __( 'Less than %d min after registration', 'beyond-multisite' ) ),
                                    sprintf( __( 'Less than %d hours after registration', 'beyond-multisite' ), 2 ),
                                    sprintf( __( 'Less than %d hours after registration', 'beyond-multisite' ), 6 ),
                                    str_replace( '%d', '[X]', __( 'Less than %d hours after registration', 'beyond-multisite' ) ),
                                    sprintf( __( 'Less than %d day after registration', 'beyond-multisite' ), 1 ),
                                    sprintf( __( 'Less than %d days after registration', 'beyond-multisite' ), 3 ),
                                    sprintf( __( 'Less than %d days after registration', 'beyond-multisite' ), 7 ),
                                    str_replace( '%d', '[X]', __( 'Less than %d days after registration', 'beyond-multisite' ) ),
                                    sprintf( __( 'Older than %d days', 'beyond-multisite' ), 7 ),
                                    sprintf( __( 'Older than %d days', 'beyond-multisite' ), 30 ),
                                    sprintf( __( 'Older than %d days', 'beyond-multisite' ), 90 ),
                                    sprintf( __( 'Older than %1$d days (%2$d year)', 'beyond-multisite' ), 365, 1 ),
                                    sprintf( __( 'Older than %1$d days (%2$d years)', 'beyond-multisite' ), 730, 2 ),
                                    sprintf( __( 'Older than %1$d days (%2$d years)', 'beyond-multisite' ), 1095, 3 ),
                                    sprintf( __( 'Older than %1$d days (%2$d years)', 'beyond-multisite' ), 1825, 5 ),
                                    str_replace( '%d', '[X]', __( 'Older than %d days', 'beyond-multisite' ) ),
                                    sprintf( __( 'In the last %d days', 'beyond-multisite' ), 7 ),
                                    sprintf( __( 'In the last %d days', 'beyond-multisite' ), 30 ),
                                    sprintf( __( 'In the last %d days', 'beyond-multisite' ), 90 ),
                                    sprintf( __( 'In the last %1$d days (%2$d year)', 'beyond-multisite' ), 365, 1 ),
                                    sprintf( __( 'In the last %1$d days (%2$d years)', 'beyond-multisite' ), 730, 2 ),
                                    sprintf( __( 'In the last %1$d days (%2$d years)', 'beyond-multisite' ), 1095, 3 ),
                                    sprintf( __( 'In the last %1$d days (%2$d years)', 'beyond-multisite' ), 1825, 5 ),
                                    str_replace( '%d', '[X]', __( 'In the last %d days', 'beyond-multisite' ) ),
                                )
                            );
                            ?>
                            <span class="be-mu-tooltip">
                                <span class="be-mu-info">i</span>
                                <span class="be-mu-tooltip-text">
                                    <?php
                                    esc_html_e( 'Hint: A site update is publishing or deleting a post/page, or updating a published post/page.',
                                        'beyond-multisite' );
                                    ?>
                                </span>
                            </span>
                            <span id="be-mu-clean-gmt-bug-message">
                                <?php
                                printf(
                                    esc_html__( 'Warning: All sites created before WordPress 5.1 and at the same time created while the timezone'
                                        . ' for the main site of the network was not UTC, are affected by %1$sa bug in WordPress%2$s. This bug will cause incorrect '
                                        . 'site selection when you use the currently selected option because the registered time and the last updated '
                                        . 'time were using a different timezone.', 'beyond-multisite' ),
                                    '<a href="https://core.trac.wordpress.org/ticket/40035" target="_blank">',
                                    '</a>'
                                );
                                ?>
                            </span>
                        </li>
                        <li>
                            <label for="be-mu-clean-site-posts">
                                <?php esc_html_e( 'Published posts count:', 'beyond-multisite' ); ?>
                            </label>
                            <?php
                            be_mu_select(
                                'be-mu-clean-site-posts',
                                array( 'Any', '0 (ignore first post)', '0', '1', '0 or 1', 'Less than 5', 'Less than 10', 'Less than [X]' ),
                                array(
                                    __( 'Any', 'beyond-multisite' ),
                                    __( '0 (ignore first post)', 'beyond-multisite' ),
                                    '0',
                                    '1',
                                    __( '0 or 1', 'beyond-multisite' ),
                                    sprintf( __( 'Less than %d', 'beyond-multisite' ), 5 ),
                                    sprintf( __( 'Less than %d', 'beyond-multisite' ), 10 ),
                                    str_replace( '%d', '[X]', __( 'Less than %d', 'beyond-multisite' ) ),
                                )
                            );
                            ?>
                            <span class="be-mu-tooltip">
                                <span class="be-mu-info">i</span>
                                <span class="be-mu-tooltip-text">
                                    <?php
                                    esc_html_e( 'Hint: Newly created sites have a "Hello world!" post by default. If you choose the option '
                                        . '"0 (ignore first post)", that post will be ignored, and only sites with no other published posts will be affected. '
                                        . 'But if that post was modified, or deleted and another one added in its place, then it will not be ignored.',
                                        'beyond-multisite' );
                                    ?>
                                </span>
                            </span>
                        </li>
                        <li>
                            <label for="be-mu-clean-site-pages">
                                <?php esc_html_e( 'Published pages count:', 'beyond-multisite' ); ?>
                            </label>
                            <?php
                            be_mu_select(
                                'be-mu-clean-site-pages',
                                array( 'Any', '0 (ignore first page)', '0', '1', '0 or 1', 'Less than 5', 'Less than 10', 'Less than [X]' ),
                                array(
                                    __( 'Any', 'beyond-multisite' ),
                                    __( '0 (ignore first page)', 'beyond-multisite' ),
                                    '0',
                                    '1',
                                    __( '0 or 1', 'beyond-multisite' ),
                                    sprintf( __( 'Less than %d', 'beyond-multisite' ), 5 ),
                                    sprintf( __( 'Less than %d', 'beyond-multisite' ), 10 ),
                                    str_replace( '%d', '[X]', __( 'Less than %d', 'beyond-multisite' ) ),
                                )
                            );
                            ?>
                            <span class="be-mu-tooltip">
                                <span class="be-mu-info">i</span>
                                <span class="be-mu-tooltip-text">
                                    <?php
                                    esc_html_e( 'Hint: Newly created sites have a sample page by default. If you choose the option "0 (ignore first page)", '
                                        . 'that page will be ignored, and only sites with no other published pages will be affected. But if that page was modified, '
                                        . 'or deleted and another one added in its place, then it will not be ignored.', 'beyond-multisite' );
                                    ?>
                                </span>
                            </span>
                        </li>
                        <li>
                            <label for="be-mu-clean-site-comments">
                                <?php esc_html_e( 'Approved comments count:', 'beyond-multisite' ); ?>
                            </label>
                            <?php
                            be_mu_select(
                                'be-mu-clean-site-comments',
                                array( 'Any', '0 (ignore first comment)', '0', '1', '0 or 1', 'Less than 5', 'Less than 10', 'Less than [X]' ),
                                array(
                                    __( 'Any', 'beyond-multisite' ),
                                    __( '0 (ignore first comment)', 'beyond-multisite' ),
                                    '0',
                                    '1',
                                    __( '0 or 1', 'beyond-multisite' ),
                                    sprintf( __( 'Less than %d', 'beyond-multisite' ), 5 ),
                                    sprintf( __( 'Less than %d', 'beyond-multisite' ), 10 ),
                                    str_replace( '%d', '[X]', __( 'Less than %d', 'beyond-multisite' ) ),
                                )
                            );
                            ?>
                            <span class="be-mu-tooltip">
                                <span class="be-mu-info">i</span>
                                <span class="be-mu-tooltip-text">
                                    <?php
                                    esc_html_e( 'Hint: Newly created sites have one example comment by default. If you choose the option '
                                        . '"0 (ignore first comment)", that comment with comment ID 1 will be ignored (even if modified), and '
                                        . 'only sites with no other approved comments will be affected.', 'beyond-multisite' );
                                    ?>
                                </span>
                            </span>
                        </li>
                        <li>
                            <label for="be-mu-clean-site-affect-sites-id-option">
                                <?php esc_html_e( 'Affect sites with:', 'beyond-multisite' ); ?>
                            </label>
                            <?php
                            be_mu_select(
                                'be-mu-clean-site-affect-sites-id-option',
                                array( 'Any site ID', 'Only these site IDs:', 'All except these site IDs:' ),
                                array(
                                    __( 'Any site ID (All sites)', 'beyond-multisite' ),
                                    __( 'Only these site IDs:', 'beyond-multisite' ),
                                    __( 'All except these site IDs:', 'beyond-multisite' ),
                                )
                            );
                            echo '&nbsp;';
                            be_mu_input_text( 'be-mu-clean-site-affect-sites-ids' );
                            ?>
                            <span class="be-mu-tooltip">
                                <span class="be-mu-info">i</span>
                                <span class="be-mu-tooltip-text">
                                    <?php esc_html_e( 'Accepts: Comma-separated numbers or an empty string.', 'beyond-multisite' ); ?>
                                </span>
                            </span>
                        </li>
                        <li>
                            <label for="be-mu-clean-site-skip-cancelled">
                                <?php esc_html_e( 'Skip previously cancelled:', 'beyond-multisite' ); ?>
                            </label>
                            <?php
                            be_mu_select(
                                'be-mu-clean-site-skip-cancelled',
                                array( 'Yes', 'No' ),
                                array(
                                    __( 'Yes', 'beyond-multisite' ),
                                    __( 'No', 'beyond-multisite' ),
                                )
                            );
                            ?>
                            <span class="be-mu-tooltip">
                                <span class="be-mu-info">i</span>
                                <span class="be-mu-tooltip-text">
                                    <?php
                                    esc_html_e( 'Hint: You can choose to exclude sites which deletion has been cancelled in the past '
                                        . '(but not earlier than version 1.1.0). We are talking about individually cancelled sites, not about '
                                        . 'cancelling the whole site deletion task.', 'beyond-multisite' );
                                    ?>
                                </span>
                            </span>
                        </li>
                        <li>
                            <label for="be-mu-clean-site-deletion-type">
                                <?php esc_html_e( 'Deletion type:', 'beyond-multisite' ); ?>
                            </label>
                            <?php
                            be_mu_select(
                                'be-mu-clean-site-deletion-type',
                                array( 'Permanent deletion', 'Mark as deleted (change last updated time)', 'Mark as deleted (keep last updated time)',
                                    'Mark as archived (change last updated time)', 'Mark as archived (keep last updated time)' ),
                                array(
                                    __( 'Permanent deletion', 'beyond-multisite' ),
                                    __( 'Mark as deleted (change last updated time)', 'beyond-multisite' ),
                                    __( 'Mark as deleted (keep last updated time)', 'beyond-multisite' ),
                                    __( 'Mark as archived (change last updated time)', 'beyond-multisite' ),
                                    __( 'Mark as archived (keep last updated time)', 'beyond-multisite' ),
                                )
                            );
                            ?>
                            <span class="be-mu-tooltip">
                                <span class="be-mu-info">i</span>
                                <span class="be-mu-tooltip-text">
                                    <?php esc_html_e( 'Hint: When marking as deleted/archived, it is useful to change the last updated time, '
                                        . 'if you plan to later do a permanent deletion after a certain time has passed since you marked '
                                        . 'them as deleted/archived. Also keep in mind that permanent deletion will delete the database '
                                        . 'tables and the uploaded files.', 'beyond-multisite' ); ?>
                                </span>
                            </span>
                        </li>
                        <li>
                            <label for="be-mu-clean-site-deletion-time">
                                <?php esc_html_e( 'Deletion time and cancellation:', 'beyond-multisite' ); ?>
                            </label>
                            <?php
                            be_mu_select(
                                'be-mu-clean-site-deletion-time',
                                array( 'Schedule, notify, and wait 7 days',
                                    'Schedule, notify, and wait 14 days',
                                    'Schedule, notify, and wait 30 days',
                                    'Schedule, notify, and wait [X] days',
                                    'No cancellation. Execute now!' ),
                                array(
                                    sprintf( __( 'Schedule, notify, and wait %d days', 'beyond-multisite' ), 7 ),
                                    sprintf( __( 'Schedule, notify, and wait %d days', 'beyond-multisite' ), 14 ),
                                    sprintf( __( 'Schedule, notify, and wait %d days', 'beyond-multisite' ), 30 ),
                                    str_replace( '%d', '[X]', __( 'Schedule, notify, and wait %d days', 'beyond-multisite' ) ),
                                    __( 'No cancellation. Execute now!', 'beyond-multisite' ),
                                )
                            );
                            ?>
                            <span class="be-mu-tooltip">
                                <span class="be-mu-info">i</span>
                                <span class="be-mu-tooltip-text">
                                    <?php
                                    esc_html_e( 'Hint: You can notify site admins and give them some time to cancel the deletion, '
                                        . 'or you can delete now.', 'beyond-multisite' );
                                    ?>
                                </span>
                            </span>
                        </li>
                    </ul>
                    <p>
                        <input class="button" onclick="cleanupStartSite( 'preview' )" type="button"
                            value="<?php esc_attr_e( 'Preview Site Deletion', 'beyond-multisite' ); ?>" />
                        <input class="button button-primary" onclick="cleanupStartSite( 'delete' )" type="button"
                            value="<?php esc_attr_e( 'Execute Site Deletion!', 'beyond-multisite' ); ?>" />
                    </p>

                <?php

                }

                ?>

            </div>

            <?php

            // We will not show the leftover database section if this is a self-refreshing page used to speed up a site deletion task
            if ( ! isset( $_GET['be-mu-auto-refresh'] ) ) {

            ?>

            <div class="be-mu-white-box be-mu-cleanup-box">
                <h3>
                    <?php esc_html_e( 'Delete Leftover Database Tables', 'beyond-multisite' ); ?>
                </h3>
                <p>
                    <i>
                        <?php
                        esc_html_e( 'When a site is permanently deleted, some database tables may be left behind. These are tables created by the plugins it used. '
                            . 'With this feature you can delete all leftover database tables of non-existent sites.', 'beyond-multisite' );
                        ?>
                    </i>
                </p>
                <p>
                    <i>
                        <?php
                        printf(
                            esc_html__( '%sWARNING!%s On some servers this feature can cause a very slow MySQL query that cannot finish. '
                                . 'This is related to how the INFORMATION_SCHEMA tables are set up. Be prepared to contact your hosting administrator '
                                . 'in case of problems.', 'beyond-multisite' ),
                            '<b>', '</b>'
                        );
                        ?>
                    </i>
                </p>
                <p>
                    <input class="button" onclick="cleanupStartTable( 'preview' )" type="button"
                        value="<?php esc_attr_e( 'Preview Leftover Tables Deletion', 'beyond-multisite' ); ?>" />
                    <input class="button button-primary" onclick="cleanupStartTable( 'delete' )" type="button"
                        value="<?php esc_attr_e( 'Execute Leftover Tables Deletion!', 'beyond-multisite' ); ?>" />
                </p>
            </div>

            <div class="be-mu-white-box be-mu-cleanup-box">
                <h3>
                    <?php esc_html_e( 'Delete Users', 'beyond-multisite' ); ?>
                </h3>
                <p>
                    <b><i><?php esc_html_e( 'Delete Users Without a Role', 'beyond-multisite' ); ?></i></b><br>
                    <i>
                        <?php
                        printf(
                            esc_html__( 'When a site is permanently deleted, users that only had a %srole%s in that site will now have no roles anywhere. '
                            . 'These users are still working, people can login, edit their profile, comment on sites (if allowed), '
                            . 'and even create sites if this is allowed '
                            . 'in your network (if they create a site, they will have a role in it). You could choose to leave them, but if you want with '
                            . 'this feature you can delete all users without a role in any site. Super Administrators will not be affected. The deletion is global '
                            . 'and permanent. The users will not be notified. If they are the author of any content on any sites, the content will not be deleted '
                            . 'and it will not be assigned to another user.', 'beyond-multisite' ),
                            '<a href="https://wordpress.org/support/article/roles-and-capabilities/" target="_blank">', '</a>'
                        );
                        ?>
                    </i>
                </p>
                <p>
                    <b><i><?php esc_html_e( 'Delete Users by Role', 'beyond-multisite' ); ?></i></b><br>
                    <i>
                        <?php
                        printf(
                            esc_html__( 'All users that have %sonly%s the selected role in any site, will be deleted. If they have any other role anywhere, '
                            . 'they will be skipped. Super Administrators will not be affected. '
                            . 'The deletion is global and permanent. The users will not be notified. If they are the author of any content on any sites, '
                            . 'the content will not be deleted and it will not be assigned to another user.', 'beyond-multisite' ), '<u>', '</u>'
                        );
                        ?>
                    </i>
                </p>
                <p>
                    <b><i><?php esc_html_e( 'Delete Users by a List of Roles', 'beyond-multisite' ); ?></i></b><br>
                    <i>
                        <?php esc_html_e( 'You enter a comma-separated list of role slugs. Users with at least one of the provided roles (in any site), '
                        . 'who also do not have any role that is not in the list in any site, will be deleted. You can include custom '
                        . 'roles too. Make sure you are writing the role slugs, not the role names! The role slugs are lower case '
                        . 'and have no spaces, but they can also be different from the role name in other ways, so do not assume '
                        . 'they are always a lower case version of the name. To see the slug you can go to edit the site that has '
                        . 'the role you want and click on the Users tab. We have put a list of the roles with the slugs there. '
                        . 'Super Administrators will not be affected. The deletion is global and permanent. The users will not be notified. '
                        . 'If they are the author of any content on any sites, the content will not be deleted and it will not be assigned to another user.',
                        'beyond-multisite' ); ?>
                    </i>
                </p>
                <ul>
                    <li>
                        <label for="be-mu-cleanup-users-role">
                            <?php esc_html_e( 'User Selection:', 'beyond-multisite' ); ?>
                        </label>
                        <?php
                        be_mu_select(
                            'be-mu-cleanup-users-role',
                            array( 'Without a role', 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Any role from a list' ),
                            array(
                                __( 'Without a role in any site', 'beyond-multisite' ),
                                __( 'Only Administrator role in any site', 'beyond-multisite' ),
                                __( 'Only Editor role in any site', 'beyond-multisite' ),
                                __( 'Only Author role in any site', 'beyond-multisite' ),
                                __( 'Only Contributor role in any site', 'beyond-multisite' ),
                                __( 'Only Subscriber role in any site', 'beyond-multisite' ),
                                __( 'Only any of the roles from a custom list in any site', 'beyond-multisite' ),
                            )
                        );
                        ?>
                    </li>
                    <li id="be-mu-cleanup-users-list-roles-show" class="be-mu-display-none">
                        <label for="be-mu-cleanup-users-roles-list">
                            <?php esc_html_e( 'Role slugs (comma-separated)', 'beyond-multisite' ); ?>
                        </label>
                        <?php
                        be_mu_input_text( 'be-mu-cleanup-users-roles-list' );
                        ?>
                    </li>
                </ul>
                <p>
                    <input class="button" onclick="cleanupStartUsers( 'preview' )" type="button"
                        value="<?php esc_attr_e( 'Preview User Deletion', 'beyond-multisite' ); ?>" />
                    <input class="button button-primary" onclick="cleanupStartUsers( 'delete' )" type="button"
                        value="<?php esc_attr_e( 'Execute User Deletion!', 'beyond-multisite' ); ?>" />
                </p>
            </div>

            <?php

            }

        }

        ?>

    </div>

    <div id="be-mu-clean-container" class="be-mu-div-contain-results">
        <table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
            <tr>
                <td valign="middle">
                    <div id="be-mu-clean-div-results">

                    </div>
                </td>
            </tr>
        </table>
    </div>

    <?php

}

/**
 * This function goes through the sites and performs the action, it either executes primary comment deletion or counts how many would be deleted.
 * It is called by an ajax request and could run multiple times in order to go through all the sites in a big network of sites.
 * It outputs a json-encoded array of numbers that we use to know how many sites are done so far in the current request, how many were affected,
 * whether a limit was reached (limit of time or number of sites), or what offest we used when calling the get_sites function.
 */
function be_mu_clean_comment_primary_callback() {

    // We start a timer to know how much time the current request runs and abort if the time limit is reached
    $time1 = microtime( true );

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_clean_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // The count of the affected sites on the current request
    $request_affected_sites_count = 0;

    // The count of the affected comments on the current request
    $request_affected_comment_count = 0;

    // The count of the processed sites on the current request
    $request_processed_sites_count = 0;

    // If it is set to 1 it means that a time or site limit is reached for the current request
    $is_request_limit_reached = 0;

    // Comma-separated site ids of sites affected by the current request, we will add this to the database
    $affected_sites_string = '';

    // Comma-separated comment counts for each sites affected by the current request, we will add this to the database
    $affected_comment_count_string = '';

    // Comma-separated strings of dash-separated post ids of affected posts for each site in the request, we will add it to the database
    $affected_posts_string = '';

    // The id of the current task, we will add this to the database
    $task_id = be_mu_strip_all_but_digits_and_letters( $_POST['task_id'] );

    // The mode of the task. If it is "delete", we are executing deletion. If it is "preview", we are previewing the deletion.
    $mode = $_POST['mode'];

    // These 7 variables hold the selected settings for the current task
    $comment_status = $_POST['comment_status'];
    $comment_url_count = $_POST['comment_url_count'];
    $comment_datetime = $_POST['comment_datetime'];
    $affect_sites_comment_amount = $_POST['affect_sites_comment_amount'];
    $affect_sites_comment_status = $_POST['affect_sites_comment_status'];
    $affect_sites_id_option = $_POST['affect_sites_id_option'];
    $affect_sites_ids = be_mu_strip_whitespace( $_POST['affect_sites_ids'] );

    // The offset to use when calling get_sites()
    $offset = intval( $_POST['offset'] );

    // If the settings are not valid we stop and show an error code
    if ( ! be_mu_clean_comment_vars_valid( $comment_status, $comment_url_count, $comment_datetime, $affect_sites_comment_amount,
        $affect_sites_comment_status ) ) {
        wp_die( 'invalid-data' );
    }

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // Based on the settings for the current task and the offset for the current request we create the arguments for get_sites()
    $get_sites_arguments = be_mu_clean_build_get_sites_arguments( $affect_sites_id_option, $affect_sites_ids, $offset );

    // We get the sites that we will try to process in this request
    $sites = get_sites( $get_sites_arguments );

    // We go through all the selected sites
    foreach ( $sites as $object_site ) {

        // The id of the current site in the foreach cycle
        $site_id = intval( $object_site->id );

        // The database site prefix for the current site
        $site_prefix = $wpdb->get_blog_prefix( $site_id );

        // The database name with the comments for the current site
        $db_table = $site_prefix . 'comments';

        // The database name with the comment meta data for the current site
        $db_table_meta = $site_prefix . 'commentmeta';

        // Dash-separated post ids of the affected posts in the current site
        $site_affected_posts_string = '';

        // Based on the selected settings for the current task we could skip some sites with certain amount of comments
        if ( be_mu_clean_comment_determine_site_skip( $affect_sites_comment_amount, $affect_sites_comment_status, $db_table ) ) {

            // Since we are skipping the site, it is processed, so we increase the counter processed sites
            $request_processed_sites_count++;

            /**
             * If more than BE_MU_CLEAN_LIMIT_TIME seconds have passed or we processed more than BE_MU_CLEAN_LIMIT_DATA sites
             * we stop and we set $is_request_limit_reached to 1 so we know that a limit stopped us and we are not done.
             */
            if ( ( microtime( true ) - $time1 ) > BE_MU_CLEAN_LIMIT_TIME
                || $request_processed_sites_count == BE_MU_CLEAN_LIMIT_DATA ) {
                $is_request_limit_reached = 1;
                break;
            }

            // Skip this site and go to the next one in the cycle
            continue;
        }

        // If we are here, then the site will not be skipped, so we create the where part of the query string to run for the site
        $query_where = be_mu_clean_build_comment_query_where( $comment_status, $comment_url_count, $comment_datetime );

        // Skip this site and go to the next one in the cycle
        if ( false === $query_where ) {
            wp_die( 'invalid-data' );
        }

        // Execute deletion
        if ( 'delete' === $mode ) {

            /**
             * If approved comments could be affected, we get all post ids of the comments we will delete,
             * so we can fix the comment counts later in the secondary comment cleanup.
             */
            if ( 'Any' == $comment_status || 'Approved' == $comment_status ) {
                $affected_posts_multi_array = $wpdb->get_results( "SELECT DISTINCT comment_post_ID FROM " . $db_table . $query_where, ARRAY_A );
                if ( ! empty( $affected_posts_multi_array ) ) {

                    foreach ( $affected_posts_multi_array as $affected_posts ) {

                        // Dash-separated post ids
                        $site_affected_posts_string .= $affected_posts['comment_post_ID'] . '-';
                    }
                }
            }

            // We run the primary delete query and get the number of affected comments
            $site_affected_comment_count = $wpdb->query( "DELETE FROM " . $db_table . $query_where );

        // Preview deletion; we get the count of comments that would be deleted if this was actual deletion
        } else {
            $site_affected_comment_count = $wpdb->get_var( "SELECT count( comment_ID ) FROM " . $db_table . $query_where );
        }

        // If the number of affected comments are more then 0, then the site is affected by the request
        if ( $site_affected_comment_count > 0 ) {

            // Current site is affected
            $request_affected_sites_count++;

            // We add the affected comment count for the site to the total count for the request
            $request_affected_comment_count += $site_affected_comment_count;

            // We add the site id to the comma-separated string for the database
            $affected_sites_string .= $site_id . ',';

            // We add the affected comments count to the comma-separated string for the database
            $affected_comment_count_string .= $site_affected_comment_count . ',';

            // If there are affected posts, we add them to the comma-separated string for the database
            if ( '' != $site_affected_posts_string ) {
                $affected_posts_string .= $site_affected_posts_string . ',';
            }
        }

        // At this point the current site is processed, so we increase the counter of processed site
        $request_processed_sites_count++;

        // If more than BE_MU_CLEAN_LIMIT_TIME seconds have passed or we processed more than BE_MU_CLEAN_LIMIT_DATA sites
        // We stop and we set $is_request_limit_reached to 1 so we know that a limit stopped us and we are not done
        if ( ( microtime( true ) - $time1 ) > BE_MU_CLEAN_LIMIT_TIME || $request_processed_sites_count == BE_MU_CLEAN_LIMIT_DATA ) {

            $is_request_limit_reached = 1;
            break;
        }
    }

    // If there are affected sites in the current request we add the data to the database, so we can get it later to display results
    if ( ! empty( $affected_sites_string ) && ! empty( $affected_comment_count_string ) ) {
        be_mu_clean_add_task_data( $task_id, $affected_sites_string, $affected_comment_count_string, $affected_posts_string );
    }

    if ( 'delete' === $mode ) {
        wp_cache_flush();
    }

    // This is the data to pass to the javascript function in json format
    $json_result = array(
        'requestProcessedSitesCount' => $request_processed_sites_count,
        'requestAffectedSitesCount' => $request_affected_sites_count,
        'requestAffectedCommentsCount' => $request_affected_comment_count,
        'currentOffset' => $offset,
        'isRequestLimitReached' => $is_request_limit_reached,
    );

    echo json_encode( $json_result );

    wp_die();
}

/*
 * This is the secondary cleanup of comments. It only runs after actual deletion of comments and not a preview.
 * Here we delete comment meta data, child comments with parents that were deleted, and we update the comment count of affected posts.
 * We make all this separately from the primary cleanup in order to minimize the chance of request timeout when many comments are deleted at once.
 */
function be_mu_clean_comment_secondary_callback() {

    // We start a timer to know how much time the current request runs and abort if the time limit is reached
    $time1 = microtime( true );

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_clean_nonce', 'security', false ) ) {
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

    // The id of the current task, we will add this to the database
    $task_id = be_mu_strip_all_but_digits_and_letters( $_POST['task_id'] );

    // The offset to use when calling get_sites()
    $offset = intval( $_POST['offset'] );

    // The selected comment status in the comment cleanup form
    $comment_status = $_POST['comment_status'];

    // The selected comment status in the comment cleanup form
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // If approved comments could have been affected we will also get data for the post ids
    if ( 'Any' == $comment_status || 'Approved' == $comment_status ) {

        // The columns argument for gettinh data for the task
        $task_data_columns = array( 'cleanup_sites_data', 'cleanup_data_2' );
    } else {

        // The columns argument for gettinh data for the task
        $task_data_columns = array( 'cleanup_sites_data' );
    }

    // We get the data for the task
    $task_data_multi_array = be_mu_get_task_data( $task_id, $offset, 1, BE_MU_CLEAN_LIMIT_DATA, 'be_mu_cleanup', $task_data_columns );

    // If there is no data for the sites, there is an error
    if ( ! is_array( $task_data_multi_array['cleanup_sites_data'] ) ) {
        wp_die( 'no-sites' );
    }

    // The columns argument for getting data for the task
    for ( $i = 0; $i < count( $task_data_multi_array['cleanup_sites_data'] ); $i++ ) {

        // This is the current site id
        $site_id = intval( $task_data_multi_array['cleanup_sites_data'][ $i ] );

        // The database site prefix for the current site
        $site_prefix = $wpdb->get_blog_prefix( $site_id );

        // The database name with the comments for the current site
        $db_table = $site_prefix . 'comments';

        // The database name with the comment meta data for the current site
        $db_table_meta = $site_prefix . 'commentmeta';

        // We need to perform the deletion of comments with no parents at most 10 times, for 10 times nested comments
        for ( $j = 0; $j < 10; $j++ ) {

            // We get a comma-separated string of comment ids of comments that have parents that do not exist (orphan comments)
            $orphan_comments_multi_array = $wpdb->get_results( "SELECT GROUP_CONCAT( comment_ID ) FROM " . $db_table
                . " WHERE ( comment_type = '' OR comment_type = 'comment' ) AND comment_parent != '0' AND comment_parent != '' AND comment_parent NOT IN "
                . "( SELECT DISTINCT comment_ID FROM " . $db_table . " WHERE 1 )", ARRAY_A );

            // If there is nothing to delete, we stop the cycle
            if ( empty( $orphan_comments_multi_array[0]['GROUP_CONCAT( comment_ID )'] ) ) {
                break;
            }

            // We delete the orphan comments if the comma-separated sting is valid
            if( be_mu_is_comma_separated_numbers( $orphan_comments_multi_array[0]['GROUP_CONCAT( comment_ID )'] ) ) {
                $wpdb->query( "DELETE FROM " . $db_table . " WHERE comment_id IN ( " . $orphan_comments_multi_array[0]['GROUP_CONCAT( comment_ID )'] . " )" );
            }
        }

        // We delete the comment meta data for all comments that do not exist anymore
        $wpdb->query( "DELETE FROM " . $db_table_meta . " WHERE comment_id NOT IN ( SELECT comment_ID FROM " . $db_table . " WHERE 1 )" );

        // If approved comments could have been affected we will update the comment count for each affected post
        if ( 'Any' == $comment_status || 'Approved' == $comment_status ) {

            // This is the string with dash-separated affected post ids
            $dash_separated_post_ids = $task_data_multi_array['cleanup_data_2'][ $i ];

            // Remove the last dash so explode works correctly
            $dash_separated_post_ids = substr_replace( $dash_separated_post_ids, '', -1 );
            $affected_post_ids = explode( '-', $dash_separated_post_ids );

            // We swith to the current site, so we can run the wp_update_comment_count function on it
            switch_to_blog( $site_id );

            // For all affected posts we fix the comment counts
            foreach ( $affected_post_ids as $affected_post_id ) {      
                wp_update_comment_count( intval( $affected_post_id ) );
            }

            // We switch back to our site
            restore_current_blog();
        }

        // We delete this transient so the number of comments is updated if using WooCommerce
        switch_to_blog( $site_id );
        delete_transient( 'wc_count_comments' );
        restore_current_blog();

        // At this point the current site is processed, so we increase the counter of processed site
        $request_processed_sites_count++;

        // If more than BE_MU_CLEAN_LIMIT_TIME seconds have passed or we processed more than BE_MU_CLEAN_LIMIT_DATA sites
        // We stop and we set $is_request_limit_reached to 1 so we know that a limit stopped us and we are not done
        if ( ( microtime( true ) - $time1 ) > BE_MU_CLEAN_LIMIT_TIME || $request_processed_sites_count == BE_MU_CLEAN_LIMIT_DATA ) {

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

    wp_cache_flush();

    echo json_encode( $json_result );

    wp_die();
}

/**
 * This function goes through the sites and performs the action, it either executes revision deletion or counts how many would be deleted.
 * It is called by an ajax request and could run multiple times in order to go through all the sites in a big network of sites.
 * It outputs a json-encoded array of numbers that we use to know how many sites are done so far in the current request, how many were affected,
 * whether a limit was reached (limit of time or number of sites), or what offest we used when calling the get_sites function.
 */
function be_mu_clean_revision_callback() {

    // We start a timer to know how much time the current request runs and abort if the time limit is reached
    $time1 = microtime( true );

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_clean_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // The count of the affected sites on the current request
    $request_affected_sites_count = 0;

    // The count of the affected revisions on the current request
    $request_affected_revision_count = 0;

    // The count of the processed sites on the current request
    $request_processed_sites_count = 0;

    // If it is set to 1 it means that a time or site limit is reached for the current request
    $is_request_limit_reached = 0;

    // Comma-separated site ids of sites affected by the current request, we will add this to the database
    $affected_sites_string = '';

    // Comma-separated revision counts for each sites affected by the current request, we will add this to the database
    $affected_revision_count_string = '';

    // The id of the current task, we will add this to the database
    $task_id = be_mu_strip_all_but_digits_and_letters( $_POST['task_id'] );

    // The mode that we are working with in this task: delete or preview (for executing deletion or previewing it)
    $mode = $_POST['mode'];

    // These 4 variables hold the selected settings for the current task
    $revision_datetime = $_POST['revision_datetime'];
    $revision_exclude = $_POST['revision_exclude'];
    $affect_sites_id_option = $_POST['affect_sites_id_option'];
    $affect_sites_ids = be_mu_strip_whitespace( $_POST['affect_sites_ids'] );

    // The offset to use when calling get_sites()
    $offset = intval( $_POST['offset'] );

    // If the settings are not valid we stop and show an error code
    if ( ! be_mu_clean_revision_vars_valid( $revision_datetime, $revision_exclude ) ) {
        wp_die( 'invalid-data' );
    }

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // Based on the settings for the current task and the offset for the current request we create the arguments for get_sites()
    $get_sites_arguments = be_mu_clean_build_get_sites_arguments( $affect_sites_id_option, $affect_sites_ids, $offset );

    // We get the sites that we will try to process in this request
    $sites = get_sites( $get_sites_arguments );

    // We go through all the selected sites
    foreach ( $sites as $object_site ) {

        // The id of the current site in the foreach cycle
        $site_id = intval( $object_site->id );

        // The database site prefix for the current site
        $site_prefix = $wpdb->get_blog_prefix( $site_id );

        // The database name with the revisions for the current site
        $db_table = $site_prefix . 'posts';

        // We set the affected revisions counter for the current site to 0 before we start
        $site_affected_revision_count = 0;

        // The where part of the query for revision deletion based on the setting for revision date/time
        $where_string = be_mu_clean_build_revision_where_string( $revision_datetime );

        // If some recent revisions are going to be excluded from deletion
        if ( 'None' != $revision_exclude ) {

            $temp = explode( ' ', $revision_exclude );

            // The amount of most recent revisions to excluded from deletion
            $exclude_revision_count = $temp[1];

            // We get all distinct post ids that have revisions that fit the date/time setting
            $results_multi_array = $wpdb->get_results( "SELECT DISTINCT post_parent FROM " . $db_table . $where_string, ARRAY_A );

            // If there are none, we will skip the site
            if ( empty( $results_multi_array ) ) {

                // Since we are skipping the site, it is processed, so we increase the counter for processed sites
                $request_processed_sites_count++;

                /**
                 * If more than BE_MU_CLEAN_LIMIT_TIME seconds have passed or we processed more than BE_MU_CLEAN_LIMIT_DATA sites
                 * we stop and we set $is_request_limit_reached to 1 so we know that a limit stopped us and we are not done.
                 */
                if ( ( microtime( true ) - $time1 ) > BE_MU_CLEAN_LIMIT_TIME
                    || $request_processed_sites_count == BE_MU_CLEAN_LIMIT_DATA ) {
                    $is_request_limit_reached = 1;
                    break;
                }
                continue; // Skip this site and go to the next one in the cycle
            }

            // Since we are skipping the site, it is processed, so we increase the counter for processed sites
            foreach ( $results_multi_array as $results ) {

                // We get the ids of the revisions we need to exclude from deletion
                $a_exclude = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM " . $db_table . " WHERE post_type = 'revision' AND post_parent = %d"
                    . " ORDER BY post_date_gmt DESC LIMIT " . intval( $exclude_revision_count ), $results['post_parent'] ), ARRAY_A );

                // If there are any results we create the string to add to the query so these revisions are not deleted
                if ( ! empty( $a_exclude ) ) {

                    $ids_to_exclude = '';
                    foreach ( $a_exclude as $exclude ) {

                        // Make a comma-separated list of ids
                        $ids_to_exclude .= intval( $exclude['ID'] ) . ',';
                    }

                    // Remove last character, which is a comma
                    $ids_to_exclude = substr_replace( $ids_to_exclude, '', -1 );

                    // This is the string for the query
                    $exclude_string = " AND ID NOT IN ( " . $ids_to_exclude . " )";
                } else {
                    $exclude_string = '';
                }

                // We execute deletion for the current post in the current site and count the deleted revisions
                if ( 'delete' === $mode ) {
                    $site_affected_revision_count += $wpdb->query( $wpdb->prepare( "DELETE FROM " . $db_table . $where_string . " AND post_parent = %d"
                        . $exclude_string, $results['post_parent'] ) );

                // We only select the count of the affected revisions
                } else {
                    $site_affected_revision_count += $wpdb->get_var( $wpdb->prepare( "SELECT count( ID ) FROM " . $db_table . $where_string
                        . " AND post_parent = %d" . $exclude_string, $results['post_parent'] ) );
                }
            }

        // We will not exclude any revisions from deletion, we only look at the date/time setting
        } else {

            // We run the query and get the number of affected revisions nts for the current site
            if ( 'delete' === $mode ) {
                $site_affected_revision_count = $wpdb->query( "DELETE FROM " . $db_table . $where_string );
            } else {
                $site_affected_revision_count = $wpdb->get_var( "SELECT count( ID ) FROM " . $db_table . $where_string );
            }
        }

        // If the number of affected revisions are more then 0, then the site is affected by the request
        if ( $site_affected_revision_count > 0 ) {

            // Current site is affected
            $request_affected_sites_count++;

            // We add the count of the affected revisions for the site to the total for the request
            $request_affected_revision_count += $site_affected_revision_count;

            // We add the site id to the comma-separated string for the database
            $affected_sites_string .= $site_id . ',';

            // We add the affected revisions count to the comma-separated string for the database
            $affected_revision_count_string .= $site_affected_revision_count . ',';
        }

        // At this point the current site is processed, so we increase the counter of processed site
        $request_processed_sites_count++;

        /**
         * If more than BE_MU_CLEAN_LIMIT_TIME seconds have passed or we processed more than BE_MU_CLEAN_LIMIT_DATA sites
         * we stop and we set $is_request_limit_reached to 1 so we know that a limit stopped us and we are not done.
         */
        if ( ( microtime( true ) - $time1 ) > BE_MU_CLEAN_LIMIT_TIME || $request_processed_sites_count == BE_MU_CLEAN_LIMIT_DATA ) {

            $is_request_limit_reached = 1;
            break;
        }
    }

    // If there are affected sites in the current request we add the data to the database, so we can get it later to display results
    if ( ! empty( $affected_sites_string ) && !empty( $affected_revision_count_string ) ) {
        be_mu_clean_add_task_data( $task_id, $affected_sites_string, $affected_revision_count_string );
    }

    // This is the data to pass to the javascript function in json format
    $json_result = array(
        'requestProcessedSitesCount' => $request_processed_sites_count,
        'requestAffectedSitesCount' => $request_affected_sites_count,
        'requestAffectedRevisionsCount' => $request_affected_revision_count,
        'currentOffset' => $offset,
        'isRequestLimitReached' => $is_request_limit_reached,
    );

    echo json_encode( $json_result );

    if ( 'delete' === $mode ) {
        wp_cache_flush();
    }

    wp_die();
}

/**
 * This function goes through the sites and performs the action, it either executes site deletion or counts how many would be deleted.
 * It is called by an ajax request and could run multiple times in order to go through all the sites in a big network of sites.
 * It outputs a json-encoded array of numbers that we use to know how many sites are done so far in the current request, how many were affected,
 * whether a limit was reached (limit of time or number of sites), or what offest we used when calling the get_sites function.
 */
function be_mu_clean_site_callback() {

    // We start a timer to know how much time the current request runs and abort if the time limit is reached
    $time1 = microtime( true );

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_clean_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // The count of the affected sites on the current request
    $request_affected_sites_count = 0;

    // The count of the processed sites on the current request
    $request_processed_sites_count = 0;

    // If it is set to 1 it means that a time or site limit is reached for the current request
    $is_request_limit_reached = 0;

    // Comma-separated site ids of sites affected by the current request, we will add this to the database
    $affected_sites_string = '';

    // The id of the current task, we will add this to the database
    $task_id = be_mu_strip_all_but_digits_and_letters( $_POST['task_id'] );

    // The mode that we are working with in this task: delete or preview (for executing deletion or previewing it)
    $mode = $_POST['mode'];

    // This var and the next 10 hold the selected settings for the current task
    $site_attributes = $_POST['site_attributes'];
    $site_registered = $_POST['site_registered'];
    $site_updated = $_POST['site_updated'];
    $site_posts = $_POST['site_posts'];
    $site_pages = $_POST['site_pages'];
    $site_comments = $_POST['site_comments'];
    $affect_sites_id_option = $_POST['affect_sites_id_option'];
    $affect_sites_ids = be_mu_strip_whitespace( $_POST['affect_sites_ids'] );
    $site_delete_type = $_POST['site_delete_type'];
    $site_delete_time = $_POST['site_delete_time'];
    $site_skip_cancelled = $_POST['site_skip_cancelled'];
    $extra_field_values = sanitize_text_field( $_POST['extra_field_values'] );
    $extra_field_names = sanitize_text_field( $_POST['extra_field_names'] );

    $extra_field_data = Array();
    if ( ! empty( $extra_field_names ) ){
        $extra_field_names_array = explode( "[be-mu-separator]", $extra_field_names );
        $extra_field_values_array = explode( "[be-mu-separator]", $extra_field_values );
        for ( $iterator = 0; $iterator < count( $extra_field_names_array ); $iterator++ ) {
            $field_name = $extra_field_names_array[ $iterator ];
            $extra_field_data[ $field_name ] = $extra_field_values_array[ $iterator ];
        }
    }

    // Extra fields added with hooks, data validation
    if ( apply_filters( 'beyond-multisite-delete-sites-extra-fields-validate', 'valid', $extra_field_data ) !== 'valid' ) {
        wp_die( 'invalid-data' );
    }

    // The offset to use when calling get_sites()
    $offset = intval( $_POST['offset'] );

    // If the settings are not valid we stop and show an error code
    if ( ! be_mu_clean_site_vars_valid( $site_attributes, $site_registered, $site_updated, $site_posts, $site_pages, $site_comments,
        $site_delete_type, $site_delete_time, $site_skip_cancelled ) ) {
        wp_die( 'invalid-data' );
    }

    // Makes no sense to mark as deleted sites already marked as deleted, so we show an error code
    if ( 'Deleted' == $site_attributes
        && ( 'Mark as deleted (change last updated time)' === $site_delete_type || 'Mark as deleted (keep last updated time)' === $site_delete_type ) ) {
        wp_die( 'already-deleted' );
    }

    // Makes no sense to mark as archived sites already marked as archived, so we show an error code
    if ( 'Archived' == $site_attributes
        && ( 'Mark as archived (change last updated time)' === $site_delete_type || 'Mark as archived (keep last updated time)' === $site_delete_type ) ) {
        wp_die( 'already-archived' );
    }

    // This combination of settings is not valid because admins of deleted, spam, or archived sites cannot access them in order to cancel deletion
    if ( in_array( $site_attributes, array( 'Deleted', 'Spam', 'Archived' ) ) && 'No cancellation. Execute now!' != $site_delete_time ) {
        wp_die( 'no-schedule' );
    }

    // If the setting for scheduled site deletion is chosen we determine after how many days is chosen
    if ( 'No cancellation. Execute now!' != $site_delete_time ) {
        $delete_time_parts = explode( " ", $site_delete_time );
        $delete_after_days = intval( $delete_time_parts[4] );
    }

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // The database table for the email notifications about site deletions
    $db_table_emails = $main_blog_prefix . 'be_mu_site_deletion_emails';

    $emails_multi_array = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $db_table_emails . " WHERE task_id != %s LIMIT 1", $task_id ), ARRAY_A );

    // There is another scheduled site deletion task so we cannot start a new one
    if ( ! empty( $emails_multi_array ) ) {
        wp_die( 'another-task' );
    }

    // Based on the settings for the current task and the offset for the current request we create the arguments for get_sites()
    $get_sites_arguments = be_mu_clean_site_build_get_sites_arguments( $affect_sites_id_option, $affect_sites_ids, $site_attributes, $site_delete_time, $offset );

    // We get the sites that we will try to process in this request
    $sites = get_sites( $get_sites_arguments );

    // We go through all the selected sites
    foreach ( $sites as $object_site ) {

        // The id of the current site in the foreach cycle
        $site_id = intval( $object_site->id );

        // The database site prefix for the current site
        $site_prefix = $wpdb->get_blog_prefix( $site_id );

        // The database name with the revisions for the current site
        $db_table = $site_prefix . 'posts';

        $skip_from_extra_fields = apply_filters( 'beyond-multisite-delete-sites-extra-fields-skip-site', 'no', $extra_field_data, $site_id );

        // If the value is not no or yes it is an error code
        if ( 'no' !== $skip_from_extra_fields && 'yes' !== $skip_from_extra_fields ) {
            wp_die( $skip_from_extra_fields );
        }

        // Deterime if we need to skip this site based on the chosen form settings
        if ( be_mu_clean_site_determine_site_skip( $site_id, $site_registered, $site_updated, $site_posts,
            $site_pages, $site_comments, $site_skip_cancelled )
            || 'yes' === $skip_from_extra_fields ) {

            // Since we are skipping the site, it is processed, so we increase the counter for processed sites
            $request_processed_sites_count++;

            /**
             * If more than BE_MU_CLEAN_LIMIT_TIME seconds have passed or we processed more than BE_MU_CLEAN_LIMIT_DATA sites
             * we stop and we set $is_request_limit_reached to 1 so we know that a limit stopped us and we are not done.
             */
            if ( ( microtime( true ) - $time1 ) > BE_MU_CLEAN_LIMIT_TIME
                || $request_processed_sites_count == BE_MU_CLEAN_LIMIT_DATA ) {
                $is_request_limit_reached = 1;
                break;
            }

            // Skip this site and go to the next one in the cycle
            continue;
        }

        // Execute site deleteion
        if ( 'delete' === $mode ) {

            // Execute now is selected
            if ( 'No cancellation. Execute now!' == $site_delete_time ) {

                // Delete site and drop database tables
                if ( 'Permanent deletion' === $site_delete_type ) {
                    wpmu_delete_blog( $site_id, true );

                // Mark site as deleted and change the last updated time
                } elseif ( 'Mark as deleted (change last updated time)' === $site_delete_type ) {
                    update_blog_status( $site_id, 'deleted', '1' );

                // Mark site as deleted and then set the previous last udpated time
                } elseif ( 'Mark as deleted (keep last updated time)' === $site_delete_type ) {
                    update_blog_status( $site_id, 'deleted', '1' );
                    update_blog_status( $site_id, 'last_updated', $object_site->last_updated );

                // Mark site as archived and change the last updated time
                } elseif ( 'Mark as archived (change last updated time)' === $site_delete_type ) {
                    update_blog_status( $site_id, 'archived', '1' );

                // Mark site as archived and then set the previous last udpated time
                } else {
                    update_blog_status( $site_id, 'archived', '1' );
                    update_blog_status( $site_id, 'last_updated', $object_site->last_updated );
                }

            // Schedule site deletion is selected
            } else {

                // We get the settings for the email notification
                $from_email = be_mu_get_setting( 'be-mu-clean-from-email' );
                $from_name = be_mu_get_setting( 'be-mu-clean-from-name' );
                $subject = be_mu_get_setting( 'be-mu-clean-subject' );
                $message = be_mu_clean_apply_message_shortcodes( be_mu_get_setting( 'be-mu-clean-message' ), $site_delete_time );

                //get all the admins of the website
                $site_admins = get_users( array( 'blog_id' => $site_id, 'role__in' => array( 'administrator' ) ) );

                // There are no admins to notify for this site
                if ( empty( $site_admins ) ) {

                    $to_email = 'be_mu_email@example.com';

                    // We add a fake scheduled email entry because there is no admins to be notified
                    be_mu_clean_schedule_email_for_site_deletion( $task_id, $site_id, $site_delete_type, $delete_after_days, $from_name, $from_email,
                        $to_email, $subject, $message, 'no-admins' );

                // There are admins to notify for this site
                } else {

                    // We go through all the admins
                    foreach ( $site_admins as $object_site_admin ) {

                        // We get the email of the admin
                        $to_email = $object_site_admin->user_email;

                        // We schedule an email to be sent to the admin
                        be_mu_clean_schedule_email_for_site_deletion( $task_id, $site_id, $site_delete_type, $delete_after_days, $from_name, $from_email,
                            $to_email, $subject, $message, 'scheduled' );
                    }
                }
            }
        }

        // Current site is affected
        $request_affected_sites_count++;

        // We add the site id to the comma-separated string for the database
        $affected_sites_string .= $site_id . ",";

        // At this point the current site is processed, so we increase the counter of processed site
        $request_processed_sites_count++;

        /**
         * If more than BE_MU_CLEAN_LIMIT_TIME seconds have passed or we processed more than BE_MU_CLEAN_LIMIT_DATA sites
         * we stop and we set $is_request_limit_reached to 1 so we know that a limit stopped us and we are not done.
         */
        if ( ( microtime( true ) - $time1 ) > BE_MU_CLEAN_LIMIT_TIME || $request_processed_sites_count == BE_MU_CLEAN_LIMIT_DATA ) {
            $is_request_limit_reached = 1;
            break;
        }
    }

    // If there are affected sites in the current request we add the data to the database, so we can get it later to display results
    if ( ! empty( $affected_sites_string ) ) {
        be_mu_clean_add_task_data( $task_id, $affected_sites_string );
    }

    // This is the data to pass to the javascript function in json format
    $json_result = array(
        'requestProcessedSitesCount' => $request_processed_sites_count,
        'requestAffectedSitesCount' => $request_affected_sites_count,
        'currentOffset' => $offset,
        'isRequestLimitReached' => $is_request_limit_reached,
    );

    echo json_encode( $json_result );

    wp_die();
}

/**
 * This function goes through the database tables and finds the ones that belong to permanently deleted sites.
 * It either deletes them or saves data on how many and which would be deleted if we execute the deletion.
 * It is called by an ajax request and could run multiple times in order to go through all the tables in a big network of sites.
 * It outputs a json-encoded array of numbers that we use to know how many tables are done so far in the current request, how many were affected,
 * what is the total size of the affected tables, whether a limit was reached (limit of time or number of tables),
 * or what offest we used when making the query that gets the tables.
 */
function be_mu_clean_table_callback() {

    // We start a timer to know how much time the current request runs and abort if the time limit is reached
    $time1 = microtime( true );

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_clean_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // The count of the affected tables on the current request
    $request_affected_tables_count = 0;

    // The count of the processed tables on the current request
    $request_processed_tables_count = 0;

    // If it is set to 1 it means that a time or table count limit is reached for the current request
    $is_request_limit_reached = 0;

    // String of table names affected by the current request. They are separated by two semicolons. We will add this to the database.
    $affected_table_names_string = '';

    // The id of the current task, we will add this to the database
    $task_id = be_mu_strip_all_but_digits_and_letters( $_POST['task_id'] );

    // The mode that we are working with in this task: delete or preview (for executing deletion or previewing it)
    $mode = $_POST['mode'];

    // The offset to use when we are making the query to get the tables
    $offset = intval( $_POST['offset'] );

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the network
    $base_prefix = $wpdb->base_prefix;

    // We get the database tables to try to process for this request.
    $tables_multi_array = $wpdb->get_results( $wpdb->prepare( "SELECT TABLE_NAME FROM information_schema.tables WHERE "
        . "TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA IN ( " . be_mu_get_database_names_string_for_mysql_in() . " ) "
        . "LIMIT %d, %d", $offset, BE_MU_CLEAN_LIMIT_DATA ), ARRAY_A );

    if ( ! empty( $tables_multi_array ) ) {

        // We go through all the selected tables
        foreach ( $tables_multi_array as $table ) {

            // If the table name starts with the main blog prefix, it is part of this WordPress installation (we do not need other tables)
            if ( substr( $table['TABLE_NAME'], 0, strlen( $base_prefix ) ) === $base_prefix ) {

                // The table name without the main prefix
                $table_no_main_prefix = substr( $table['TABLE_NAME'], strlen( $base_prefix ) );

                // The position of the first underscore in the table name without prefix
                $position_first_underscore = strpos( $table_no_main_prefix, '_' );

                // We check if there is even an underscore in the table name without main prefix, we only need such tables
                if ( $position_first_underscore !== false ) {

                    // The string from the start of the name without main prefix to the first underscore, is the blog id
                    $site_id = substr( $table_no_main_prefix, 0, $position_first_underscore );

                    // We only continue if the string is a number, which means the table belongs to a blog from the network that is not the main blog
                    if ( is_numeric( $site_id ) ) {

                        /**
                         * Clears the cache for the blog details. I am not sure if this is necessary, but I want to make sure we are getting
                         * accurate information in the next line about whether the site exists or not. We don't ever want to delete tables of existing sites.
                         */
                        if ( function_exists( 'clean_blog_cache' ) ) {
                            clean_blog_cache( $site_id );
                        }

                        // We continue only if the blog, to which the table belongs to, does not exist (it is permanently deleted)
                        if ( get_site( $site_id ) === null ) {

                            $default_tables = Array(
                                $base_prefix . $site_id . '_commentmeta',
                                $base_prefix . $site_id . '_comments',
                                $base_prefix . $site_id . '_links',
                                $base_prefix . $site_id . '_options',
                                $base_prefix . $site_id . '_postmeta',
                                $base_prefix . $site_id . '_posts',
                                $base_prefix . $site_id . '_termmeta',
                                $base_prefix . $site_id . '_terms',
                                $base_prefix . $site_id . '_term_relationships',
                                $base_prefix . $site_id . '_term_taxonomy',
                            );

                            /**
                             * Default wordpress database tables were found that belong to sites that do not exist, which is weird, so we will
                             * set a temporary value in the database to check in the results, so we can then show a notice there.
                             */
                            if ( in_array( $table['TABLE_NAME'], $default_tables ) ) {
                                set_transient( $task_id . '_default_tables', 'yes', ( 60 * 60 * 24 ) );
                            }

                            // Current table is affected
                            $request_affected_tables_count++;

                            // We add the table name to the string for the database
                            $affected_table_names_string .= $table['TABLE_NAME'] . ";;";

                            // If we are executing the deletion, we delete the table
                            if ( 'delete' === $mode ) {
                                $wpdb->query( 'DROP TABLE ' . $table['TABLE_NAME'] );
                            }
                        }
                    }
                }
            }

            // At this point the current table is processed, so we increase the counter of processed tables
            $request_processed_tables_count++;

            /**
             * If more than ( BE_MU_CLEAN_LIMIT_TIME + 5 ) seconds have passed or we processed more than BE_MU_CLEAN_LIMIT_DATA tables
             * we stop and we set $is_request_limit_reached to 1 so we know that a limit stopped us and we are not done.
             */
            if ( ( microtime( true ) - $time1 ) > ( BE_MU_CLEAN_LIMIT_TIME + 5 ) || $request_processed_tables_count == BE_MU_CLEAN_LIMIT_DATA ) {
                $is_request_limit_reached = 1;
                break;
            }
        }
    }

    // If there are affected tables in the current request we add the data to the database, so we can get it later to display results
    if ( ! empty( $affected_table_names_string ) ) {
        be_mu_clean_add_task_data( $task_id, 'no data', $affected_table_names_string, 'no data' );
    }

    // This is the data to pass to the javascript function in json format
    $json_result = array(
        'requestProcessedTablesCount' => $request_processed_tables_count,
        'requestAffectedTablesCount' => $request_affected_tables_count,
        'currentOffset' => $offset,
        'isRequestLimitReached' => $is_request_limit_reached,
    );

    echo json_encode( $json_result );

    wp_die();
}

/**
 * This function goes through the users and either deletes or just shows the ones that have no role in any site
 * It is called by an ajax request and could run multiple times in order to go through all the users.
 * It outputs a json-encoded array of numbers that we use to know how many users are done so far in the current request, how many were affected,
 * what is the total size of the affected users, whether a limit was reached (limit of time or number of users),
 * or what offest we used when making the query that gets the users.
 */
function be_mu_clean_user_callback() {

    // We start a timer to know how much time the current request runs and abort if the time limit is reached
    $time1 = microtime( true );

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_clean_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // The count of the affected users on the current request
    $request_affected_users_count = 0;

    // The count of the processed users on the current request
    $request_processed_users_count = 0;

    // If it is set to 1 it means that a time or user count limit is reached for the current request
    $is_request_limit_reached = 0;

    // String of user IDs affected by the current request. They are separated by a comma. We will add this to the database.
    $affected_user_ids_string = '';

    // The id of the current task, we will add this to the database
    $task_id = be_mu_strip_all_but_digits_and_letters( $_POST['task_id'] );

    // The mode that we are working with in this task: delete or preview (for executing deletion or previewing it)
    $mode = $_POST['mode'];

    $role = sanitize_text_field( $_POST['role'] );
    $role_list = be_mu_strip_whitespace( sanitize_text_field( $_POST['role_list'] ) );

    if ( empty( $role ) || ! in_array( $role, array( 'Without a role', 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Any role from a list' ) )
        || ( 'Any role from a list' === $role && ( $role_list !== $_POST['role_list'] || empty( $role_list ) || be_mu_contains_capital_letters( $role_list ) ) ) ) {
        wp_die( 'invalid-role-data' );
    }

    // The offset to use when we are making the query to get the users
    $offset = intval( $_POST['offset'] );

    $super_admins = be_mu_get_super_admin_ids();

    $users = get_users( array( 'blog_id' => 0, 'offset' => $offset, 'number' => BE_MU_CLEAN_LIMIT_DATA ) );

    // We go through the users
    foreach ( $users as $user_object ) {

        // The id of the current user in the foreach cycle
        $user_id = intval( $user_object->ID );

        $affect_the_user = 'no';

        if ( ! in_array( $user_id, $super_admins ) ) {
            if ( 'Without a role' === $role ) {

                // We get all blogs in which the user has any role. Second argument is true to get even spammed, deleted, and archived.
                $blogs = get_blogs_of_user( $user_id, true );

                // If the user has no roles in any site we will affect it
                if ( empty( $blogs ) ) {
                    $affect_the_user = "yes";
                }
            } elseif ( in_array( $role, array( 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber' ) ) ) {
                $role_slug = strtolower( $role );
                if ( be_mu_has_only_role( $user_id, $role_slug ) ) {
                    $affect_the_user = "yes";
                }
            } elseif ( 'Any role from a list' === $role ) {
                if ( strpos( $role_list, ',' ) !== false ) {
                    $role_slugs_array = explode( ',', $role_list );
                    if ( be_mu_has_only_roles( $user_id, $role_slugs_array ) ) {
                        $affect_the_user = "yes";
                    }
                } else {
                    $role_slug = $role_list;
                    if ( be_mu_has_only_role( $user_id, $role_slug ) ) {
                        $affect_the_user = "yes";
                    }
                }
            } else {
                wp_die( 'invalid-role-data' );
            }
        }

        if ( "yes" === $affect_the_user ) {

            // Current user is affected
            $request_affected_users_count++;

            // We add the user id to the string for the database
            $affected_user_ids_string .= $user_id . ",";

            // If we are executing the deletion, we delete the user
            if ( 'delete' === $mode ) {
                wpmu_delete_user( $user_id );
            }
        }

        // At this point the current user is processed, so we increase the counter of processed users
        $request_processed_users_count++;

        /**
         * If more than ( BE_MU_CLEAN_LIMIT_TIME + 5 ) seconds have passed or we processed more than BE_MU_CLEAN_LIMIT_DATA tables
         * we stop and we set $is_request_limit_reached to 1 so we know that a limit stopped us and we are not done.
         */
        if ( ( microtime( true ) - $time1 ) > ( BE_MU_CLEAN_LIMIT_TIME + 5 ) || $request_processed_users_count == BE_MU_CLEAN_LIMIT_DATA ) {
            $is_request_limit_reached = 1;
            break;
        }
    }

    // If there are affected tables in the current request we add the data to the database, so we can get it later to display results
    if ( ! empty( $affected_user_ids_string ) ) {
        be_mu_clean_add_task_data( $task_id, 'no data', $affected_user_ids_string, 'no data' );
    }

    // This is the data to pass to the javascript function in json format
    $json_result = array(
        'requestProcessedUsersCount' => $request_processed_users_count,
        'requestAffectedUsersCount' => $request_affected_users_count,
        'currentOffset' => $offset,
        'isRequestLimitReached' => $is_request_limit_reached,
    );

    echo json_encode( $json_result );

    wp_cache_flush();

    wp_die();
}

/**
 * Shows the results of the current leftover database tables deletion task.
 * It is called by an ajax request in the javascript function cleanupTableResults().
 */
function be_mu_clean_table_results_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_clean_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // The current page number we are showing results for
    $page_number = intval( $_POST['page_number'] );

    // The current task id that tells us which rows to get from the database
    $task_id = be_mu_strip_all_but_digits_and_letters( $_POST['task_id'] );

    // The mode that we are working with in this task: delete or preview (for executing deletion or previewing it)
    $mode = $_POST['mode'];

    // How many tables to show per page
    $per_page = 10;

    // The number of tables that were/would be affected
    $count_affected_tables = intval( $_POST['count_affected_tables'] );

    // We get the names for the tables to be displayed in the current page of results
    $task_data_multi_array = be_mu_get_task_data( $task_id, 'calculate', $page_number, $per_page, 'be_mu_cleanup',
        array( 'cleanup_data_1' ), ';;' );

    if ( false != $task_data_multi_array ) {

        // If there are affected tables we set an array with the table names
        $table_names = $task_data_multi_array['cleanup_data_1'];
    } else {
        $count_affected_tables = 0;
    }

    // We calculate the total number of pages in the results for the task
    $pages_count = ceil( $count_affected_tables / $per_page );

    ?>

    <div class='be-mu-p20'>
        <h2 class='be-mu-clean-h2'>
            <?php
                if ( 'delete' === $mode ) {
                    esc_html_e( 'Leftover Database Table Deletion Completed', 'beyond-multisite' );
                } else {
                    esc_html_e( 'Preview Leftover Database Table Deletion', 'beyond-multisite' );
                }
            ?>
            <div class='be-mu-right'>
                <?php
                    echo be_mu_clean_get_close_tables();
                ?>
            </div>
        </h2>

        <p class='be-mu-1-15-em'><b>
            <?php
                echo esc_html__( 'Affected tables count:', 'beyond-multisite' ) . ' ' . intval( $count_affected_tables );
            ?>
        </b></p>


        <?php

        // Default wordpress database tables were found that belong to sites that do not exist, which is weird, so we will show a notice.
        if ( get_transient( $task_id . '_default_tables' ) == 'yes' ) {
            echo '<p>'
                . esc_html__( 'NOTICE! These results include default WordPress database tables (not added by plugins) that belong '
                    . 'to permanently deleted site(s). '
                    . 'This should usually not happen. Please make sure that you really want to delete these tables.', 'beyond-multisite' )
                . '</p>';
        }

        // If there are any affected database tables we will show them in a table
        if ( $count_affected_tables > 0 ) {

            /**
             * If the tables do not fit on one page
             * we display a text saying which tables are showed now and which page.
             */
            if ( $count_affected_tables > $per_page ) {
                $to_results = $per_page * $page_number;
                if ( $to_results > $count_affected_tables ) {
                    $to_results = $count_affected_tables;
                }

                echo '<p>';
                printf(
                    esc_html__( 'Showing results %1$d - %2$d on page %3$d/%4$d', 'beyond-multisite' ),
                    ( ( $page_number - 1 ) * $per_page ) + 1,
                    $to_results,
                    $page_number,
                    $pages_count
                );
                echo '</p>';
            }

            // If we were able to get the database tables, we display them in a table, otherwise we show an error
            if ( ! empty( $table_names ) ) {

                echo '<table class="be-mu-table be-mu-mtop20">';
                echo '<thead>'
                    . '<tr>'
                    . '<th>' . esc_html__( '#', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'Name', 'beyond-multisite' ) . '</th>'
                    . '</tr>'
                    . '</thead>';

                echo '<tbody>';

                // We show information about each affected table
                for ( $i = 0; $i < count( $table_names ); $i++ ) {

                    echo '<tr>'
                        . '<td data-title="' . esc_attr__( '#', 'beyond-multisite' ) . '">' . esc_html( ( ( $page_number - 1 ) * $per_page ) + 1 + $i ) . '</td>'
                        . '<td data-title="' . esc_attr__( 'Name', 'beyond-multisite' ) . '" class="be-mu-break-word">'
                        . esc_html( $table_names[ $i ] ) . '</td>'
                        . '</tr>';
                }

                echo '</tbody>';

                echo '<tfoot>'
                    . '<tr>'
                    . '<th>' . esc_html__( '#', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'Name', 'beyond-multisite' ) . '</th>'
                    . '</tr>'
                    . '</tfoot>';

                echo '</table>';

            } else {
                echo '<p>' . esc_html__( 'An error occurred.', 'beyond-multisite' ) . '</p>';
            }

            /**
             * If the tables do not fit on one page
             * we show this dropdown menu to choose a page number to display.
             */
            if ( $count_affected_tables > $per_page ) {

                echo '<p class="be-mu-clean-preview-page-navigation">'
                    . '<label for="be-mu-clean-page-number">' . esc_html__( 'Go to page:', 'beyond-multisite' ) . '</label> '
                    . '<select onchange="cleanupTableResults()" id="be-mu-clean-page-number" name="be-mu-clean-page-number" size="1">';

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
                    echo '&nbsp;<span onclick="cleanupTableNextPreviousPage(' . intval( $page_number - 1 ) . ')" '
                        . 'class="dashicons dashicons-arrow-left-alt2" title="' . esc_attr__( 'Previous Page', 'beyond-multisite' ) . '"></span>';
                }

                if ( $pages_count > $page_number ) {
                    echo '&nbsp;<span onclick="cleanupTableNextPreviousPage(' . intval( $page_number + 1 ) . ')" '
                        . 'class="dashicons dashicons-arrow-right-alt2" title="' . esc_attr__( 'Next Page', 'beyond-multisite' ) . '"></span>';
                }

                echo '&nbsp;'
                    . '<img id="be-mu-clean-loading-page-number" src="' . esc_url( be_mu_img_url( 'loading.gif' ) ) . '" />'
                    . '</p>';
            }

            if ( 'delete' != $mode ) {
                echo '<p class="be-mu-right-txt"><input class="button button-primary" onclick="cleanupStartTable( \'delete\' )"'
                    . 'type="button" value="' . esc_attr__( 'Execute Leftover Tables Deletion!', 'beyond-multisite' ) . '" /></p>';
            }
        }

        ?>

    </div>

    <?php

    wp_die();

}

/**
 * Shows the results of the current no role users deletion task.
 * It is called by an ajax request in the javascript function cleanupUserResults().
 */
function be_mu_clean_user_results_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_clean_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // The current page number we are showing results for
    $page_number = intval( $_POST['page_number'] );

    // The current task id that tells us which rows to get from the database
    $task_id = be_mu_strip_all_but_digits_and_letters( $_POST['task_id'] );

    // The mode that we are working with in this task: delete or preview (for executing deletion or previewing it)
    $mode = $_POST['mode'];

    // How many users to show per page
    $per_page = 10;

    // The number of users that were/would be affected
    $count_affected_users = intval( $_POST['count_affected_users'] );

    // We get the names for the users to be displayed in the current page of results
    $task_data_multi_array = be_mu_get_task_data( $task_id, 'calculate', $page_number, $per_page, 'be_mu_cleanup',
        array( 'cleanup_data_1' ), ',' );

    if ( false != $task_data_multi_array ) {

        // If there are affected users we set an array with the user ids
        $user_ids = $task_data_multi_array['cleanup_data_1'];
    } else {
        $count_affected_users = 0;
    }

    // We calculate the total number of pages in the results for the task
    $pages_count = ceil( $count_affected_users / $per_page );

    ?>

    <div class='be-mu-p20'>
        <h2 class='be-mu-clean-h2'>
            <?php
                if ( 'delete' === $mode ) {
                    esc_html_e( 'User Deletion Completed', 'beyond-multisite' );
                } else {
                    esc_html_e( 'Preview User Deletion', 'beyond-multisite' );
                }
            ?>
            <div class='be-mu-right'>
                <?php
                    echo be_mu_clean_get_close_users();
                ?>
            </div>
        </h2>

        <p class='be-mu-1-15-em'><b>
            <?php
                echo esc_html__( 'Affected users count:', 'beyond-multisite' ) . ' ' . intval( $count_affected_users );
            ?>
        </b></p>


        <?php

        // If there are any affected users we will show them in a table
        if ( $count_affected_users > 0 ) {

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
                    ( ( $page_number - 1 ) * $per_page ) + 1,
                    $to_results,
                    $page_number,
                    $pages_count
                );
                echo '</p>';
            }

            // If we were able to get the users, we display them in a table, otherwise we show an error
            if ( ! empty( $user_ids ) ) {

                echo '<table class="be-mu-table be-mu-mtop20">';
                echo '<thead>'
                    . '<tr>'
                    . '<th>' . esc_html__( '#', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'User ID', 'beyond-multisite' ) . '</th>';
                if ( 'delete' !== $mode ) {
                    echo '<th>' . esc_html__( 'Username', 'beyond-multisite' ) . '</th>';
                    echo '<th>' . esc_html__( 'Actions', 'beyond-multisite' ) . '</th>';
                }
                echo '</tr>'
                    . '</thead>';

                echo '<tbody>';

                // We show information about each affected user
                for ( $i = 0; $i < count( $user_ids ); $i++ ) {

                    echo '<tr>'
                        . '<td data-title="' . esc_attr__( '#', 'beyond-multisite' ) . '">' . esc_html( ( ( $page_number - 1 ) * $per_page ) + 1 + $i ) . '</td>'
                        . '<td data-title="' . esc_attr__( 'User ID', 'beyond-multisite' ) . '">'
                        . esc_html( $user_ids[ $i ] ) . '</td>';

                    if ( 'delete' !== $mode ) {
                        $user_object = get_user_by( 'ID', $user_ids[ $i ] );
                        echo '<td data-title="' . esc_attr__( 'Username', 'beyond-multisite' ) . '" class="be-mu-break-word">'
                            . esc_html( $user_object->user_login ) . '</td>';
                        echo '<td data-title="' . esc_attr__( 'Actions', 'beyond-multisite' ) . '"><a href="'
                            . esc_url( network_admin_url( 'user-edit.php?user_id=' . intval( $user_ids[ $i ] ) ) )
                            . '" target="_blank">' . esc_html__( 'Edit', 'beyond-multisite' ) . '</a> | ' . '<a href="'
                            . esc_url( network_admin_url( 'users.php?s=' . $user_object->user_email ) )
                            . '" target="_blank">' . esc_html__( 'View', 'beyond-multisite' ) . '</a></td>';
                    }

                    echo '</tr>';
                }

                echo '</tbody>';

                echo '<tfoot>'
                    . '<tr>'
                    . '<th>' . esc_html__( '#', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'User ID', 'beyond-multisite' ) . '</th>';
                    if ( 'delete' !== $mode ) {
                        echo '<th>' . esc_html__( 'Username', 'beyond-multisite' ) . '</th>';
                        echo '<th>' . esc_html__( 'Actions', 'beyond-multisite' ) . '</th>';
                    }
                    echo '</tr>'
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

                echo '<p class="be-mu-clean-preview-page-navigation">'
                    . '<label for="be-mu-clean-page-number">' . esc_html__( 'Go to page:', 'beyond-multisite' ) . '</label> '
                    . '<select onchange="cleanupUserResults()" id="be-mu-clean-page-number" name="be-mu-clean-page-number" size="1">';

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
                    echo '&nbsp;<span onclick="cleanupUserNextPreviousPage(' . intval( $page_number - 1 ) . ')" '
                        . 'class="dashicons dashicons-arrow-left-alt2" title="' . esc_attr__( 'Previous Page', 'beyond-multisite' ) . '"></span>';
                }

                if ( $pages_count > $page_number ) {
                    echo '&nbsp;<span onclick="cleanupUserNextPreviousPage(' . intval( $page_number + 1 ) . ')" '
                        . 'class="dashicons dashicons-arrow-right-alt2" title="' . esc_attr__( 'Next Page', 'beyond-multisite' ) . '"></span>';
                }

                echo '&nbsp;'
                    . '<img id="be-mu-clean-loading-page-number" src="' . esc_url( be_mu_img_url( 'loading.gif' ) ) . '" />'
                    . '</p>';
            }

            if ( 'delete' != $mode ) {
                echo '<p class="be-mu-right-txt"><input class="button button-primary" onclick="cleanupStartUsers( \'delete\' )"'
                    . 'type="button" value="' . esc_attr__( 'Execute User Deletion!', 'beyond-multisite' ) . '" /></p>';
            }
        }

        ?>

    </div>

    <?php

    wp_die();

}

/**
 * Shows the results of the current comment deletion task.
 * It is called by an ajax request in the javascript function cleanupCommentResults().
 */
function be_mu_clean_comment_results_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_clean_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // The current page number we are showing results for
    $page_number = intval( $_POST['page_number'] );

    // The number of sites that were/would be affected
    $count_affected_sites = intval( $_POST['count_affected_sites'] );

    // The number of comments that were/would be affected
    $count_affected_comments = intval( $_POST['count_affected_comments'] );

    // The current task id that tells us which rows to get from the database
    $task_id = be_mu_strip_all_but_digits_and_letters( $_POST['task_id'] );

    // The mode that we are working with in this task: delete or preview (for executing deletion or previewing it)
    $mode = $_POST['mode'];

    // How many sites to show per page
    $per_page = 10;

    // We get the site ids and affected comment counts for the sites to be displayed in the current page of results
    $task_data_multi_array = be_mu_get_task_data( $task_id, 'calculate', $page_number, $per_page, 'be_mu_cleanup',
        array( 'cleanup_sites_data', 'cleanup_data_1' ) );

    if ( false != $task_data_multi_array ) {

        // If there are affected sites we set two arrays with the sites ids and comment counts
        $site_ids = $task_data_multi_array['cleanup_sites_data'];
        $comment_counts = $task_data_multi_array['cleanup_data_1'];

        // We also create this $comment_counts_by_id array that will have the site id as key and comment count as value
        for ( $i = 0; $i < count( $site_ids ); $i++ ) {
            $comment_counts_by_id[ intval( $site_ids[ $i ] ) ] = intval( $comment_counts[ $i ] );
        }
    } else {
        $count_affected_sites = 0;
    }

    // We calculate the total number of pages in the results for the task
    $pages_count = ceil( $count_affected_sites / $per_page );

    ?>

    <div class='be-mu-p20'>
        <h2 class='be-mu-clean-h2'>
            <?php
            if ( 'delete' === $mode ) {
                esc_html_e( 'Comment Deletion Completed', 'beyond-multisite' );
            } else {
                esc_html_e( 'Preview Comment Deletion', 'beyond-multisite' );
            }
            ?>
            <div class='be-mu-right'>
                <?php echo be_mu_clean_get_close_comments(); ?>
            </div>
        </h2>

        <p class='be-mu-1-15-em'>
            <b>
                <?php
                echo esc_html__( 'Affected sites:', 'beyond-multisite' ) . ' ' . intval( $count_affected_sites );
                echo '<br />' . esc_html__( 'Affected comments:', 'beyond-multisite' ) . ' ' . intval( $count_affected_comments );
                ?>
            </b>
        </p>

        <?php

        // If there are any affected sites we will show a table with the sites
        if ( $count_affected_sites > 0 ) {

            global $wpdb;
            $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
            $db_table = $main_blog_prefix . "be_mu_cleanup";
            $chunks_count = $wpdb->get_var( $wpdb->prepare( 'SELECT count(row_id) FROM ' . $db_table . ' WHERE task_id = %s', $task_id ) );

            // These are the settings for the get_sites function, we set it to get only the sites we want
            $get_sites_arguments = array(
                'site__in' => $site_ids,
            );

            // We get the sites into an array with the site objects
            $sites = get_sites( $get_sites_arguments );

            // If the sites do not fit on one page we display a text saying which sites are showed now and which page
            if ( $count_affected_sites > $per_page ) {

                $to_results = $per_page * $page_number;
                if ( $to_results > $count_affected_sites ) {

                    $to_results = $count_affected_sites;
                }

                echo '<p>';
                printf(
                    esc_html__( 'Showing results %1$d - %2$d on page %3$d/%4$d', 'beyond-multisite' ),
                    ( ( $page_number - 1 ) * $per_page ) + 1,
                    $to_results,
                    $page_number,
                    $pages_count
                );
                echo '</p>';

            }

            // If we were able to get the sites, we display the table with them, otherwise we show an error
            if ( ! empty( $sites ) ) {

                echo '<table class="be-mu-table be-mu-mtop20">';

                if ( 'delete' === $mode ) {
                    $deleted_column_name = __( 'Deleted*', 'beyond-multisite' );
                } else {
                    $deleted_column_name = __( 'For deletion*', 'beyond-multisite' );
                }

                echo '<thead>'
                    . '<tr>'
                    . '<th>' . esc_html__( 'ID', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'URL', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html( $deleted_column_name ) . '</th>'
                    . '<th>' . esc_html__( 'Actions', 'beyond-multisite' ) . '</th>'
                    . '</tr>'
                    . '</thead>';

                echo '<tbody>';

                // We show information about each affected site
                for ( $i = 0; $i < count( $sites ); $i++ ) {

                    // We get the site details
                    $site_details = get_blog_details( $sites[ $i ]->id );

                    // We get the site url
                    $site_url = $site_details->siteurl;

                    echo '<tr>'
                        . '<td data-title="' . esc_attr__( 'ID', 'beyond-multisite' ) . '">' . esc_html( $sites[ $i ]->id ) . '</td>'
                        . '<td data-title="' . esc_attr__( 'URL', 'beyond-multisite' ) . '" class="be-mu-break-word">'
                        . '<a href="' . esc_url( $site_url ) . '" target="_blank">' . esc_url( $site_url ) . '</a></td>'
                        . '<td data-title="' . esc_attr( $deleted_column_name ) . '">'
                        . esc_html( $comment_counts_by_id[ intval( $sites[ $i ]->id ) ] ) . '</td>'
                        . '<td data-title="' . esc_attr__( 'Actions', 'beyond-multisite' ) . '"><a href="'
                        . esc_url( get_admin_url( intval( $sites[ $i ]->id ), 'edit-comments.php' ) ) . '" target="_blank">'
                        . esc_html__( 'Comments', 'beyond-multisite' ) . '</a></td>'
                        . '</tr>';
                }

                echo '</tbody>';

                echo '<tfoot>'
                    . '<tr>'
                    . '<th>' . esc_html__( 'ID', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'URL', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html( $deleted_column_name ) . '</th>'
                    . '<th>' . esc_html__( 'Actions', 'beyond-multisite' ) . '</th>'
                    . '</tr>'
                    . '</tfoot>';

                echo '</table>';
            } else {
                echo '<p>' . esc_html__( 'An error occurred.', 'beyond-multisite' ) . '</p>';
            }

            // If the sites do not fit on one page we show this dropdown menu to choose a page number to display
            if ( $count_affected_sites > $per_page ) {

                echo '<p class="be-mu-clean-preview-page-navigation">'
                    . '<label for="be-mu-clean-page-number">' . esc_html__( 'Go to page:', 'beyond-multisite' ) . '</label> '
                    . '<select onchange="cleanupCommentResults()" id="be-mu-clean-page-number" name="be-mu-clean-page-number" size="1">';

                // We go through the pages and display an option and we mark the current page as selected in the dropdown menu
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
                    echo '&nbsp;<span onclick="cleanupCommentNextPreviousPage(' . intval( $page_number - 1 ) . ')" '
                        . 'class="dashicons dashicons-arrow-left-alt2" title="' . esc_attr__( 'Previous Page', 'beyond-multisite' ) . '"></span>';
                }

                if ( $pages_count > $page_number ) {
                    echo '&nbsp;<span onclick="cleanupCommentNextPreviousPage(' . intval( $page_number + 1 ) . ')" '
                        . 'class="dashicons dashicons-arrow-right-alt2" title="' . esc_attr__( 'Next Page', 'beyond-multisite' ) . '"></span>';
                }

                echo '&nbsp;'
                    . '<img id="be-mu-clean-loading-page-number" src="' . esc_url( be_mu_img_url( 'loading.gif' ) ) . '" />'
                    . '</p>';
            }

            if ( 'delete' === $mode ) {
                echo '<p>' . esc_html__( '* That many comments were deleted from the site.', 'beyond-multisite' ) . '</p>';
            } else {
                echo '<p>';
                esc_html_e( '* That many comments would be deleted from the site if you execute the deletion with the current settings.', 'beyond-multisite' );
                echo '</p>';

                echo '<p id="be-mu-clean-comment-preview-bottom-actions" class="be-mu-right-txt">'
                    . '<a id="be-mu-cleanup-comment-export-ids-link" href="javascript:cleanupExportResults(\''
                    . esc_js( esc_attr( $task_id ) ) . '\', \'ids\', 0, ' . intval( $chunks_count ) . ', \'comment\' )">'
                    . esc_html__( 'Export Site IDs', 'beyond-multisite' ) . '</a> | '
                    . '<a id="be-mu-cleanup-comment-export-urls-link" href="javascript:cleanupExportResults(\''
                    . esc_js( esc_attr( $task_id ) ) . '\', \'urls\', 0, ' . intval( $count_affected_sites ) . ', \'comment\' )">'
                    . esc_html__( 'Export Site URLs', 'beyond-multisite' ) . '</a>&nbsp;&nbsp;&nbsp;'
                    . '<input class="button button-primary" onclick="cleanupStartComments( \'delete\' )"'
                    . 'type="button" value="' . esc_attr__( 'Execute Comment Deletion!', 'beyond-multisite' ) . '" /></p>';
            }
        }

        ?>

    </div>

    <?php

    wp_die();
}

/**
 * Shows the results of the current revision deletion task.
 * It is called by an ajax request in the javascript function cleanupRevisionResults().
 */
function be_mu_clean_revision_results_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_clean_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // The current page number we are showing results for
    $page_number = intval( $_POST['page_number'] );

    // The number of sites that were/would be affected
    $count_affected_sites = intval( $_POST['count_affected_sites'] );

    // The number of revisions that were/would be affected
    $count_affected_revision = intval( $_POST['count_affected_revisions'] );

    // The current task id that tells us which rows to get from the database
    $task_id = be_mu_strip_all_but_digits_and_letters( $_POST['task_id'] );

    // The mode that we are working with in this task: delete or preview (for executing deletion or previewing it)
    $mode = $_POST['mode'];

    // How many sites to show per page
    $per_page = 10;

    // We get the site ids and affected revisions counts for the sites to be displayed in the current page of results
    $task_data_multi_array = be_mu_get_task_data( $task_id, 'calculate', $page_number, $per_page, 'be_mu_cleanup',
        array( 'cleanup_sites_data', 'cleanup_data_1' ) );

    if ( false != $task_data_multi_array ) {

        // If there are affected sites we set two arrays with the sites ids and revision counts
        $site_ids = $task_data_multi_array['cleanup_sites_data'];
        $revision_counts = $task_data_multi_array['cleanup_data_1'];

        // We also create this $revision_counts_by_id array that will have the site id as key and revision count as value
        for ( $i = 0; $i < count( $site_ids ); $i++ ) {
            $revision_counts_by_id[ intval( $site_ids[ $i ] ) ] = intval( $revision_counts[ $i ] );
        }
    } else {
        $count_affected_sites = 0;
    }

    // We calculate the total number of pages in the results for the task
    $pages_count = ceil( $count_affected_sites / $per_page );

    ?>

    <div class='be-mu-p20'>
        <h2 class='be-mu-clean-h2'>
            <?php
                if ( 'delete' === $mode ) {
                    esc_html_e( 'Revision Deletion Completed', 'beyond-multisite' );
                } else {
                    esc_html_e( 'Preview Revision Deletion', 'beyond-multisite' );
                }
            ?>
            <div class='be-mu-right'>
                <?php echo be_mu_clean_get_close_revisions(); ?>
            </div>
        </h2>

        <p class='be-mu-1-15-em'>
            <b>
                <?php
                    echo esc_html__( 'Affected sites:', 'beyond-multisite' ) . ' ' . intval( $count_affected_sites );
                    echo '<br />' . esc_html__( 'Affected revisions:', 'beyond-multisite' ) . ' ' . intval( $count_affected_revision );
                ?>
            </b>
        </p>

        <?php

        // If there are any affected sites we will show a table with the sites
        if ( $count_affected_sites > 0 ) {

            global $wpdb;
            $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
            $db_table = $main_blog_prefix . "be_mu_cleanup";
            $chunks_count = $wpdb->get_var( $wpdb->prepare( 'SELECT count(row_id) FROM ' . $db_table . ' WHERE task_id = %s', $task_id ) );

            // These are the settings for the get_sites function, we set it to get only the sites we want
            $get_sites_arguments = array(
                'site__in' => $site_ids,
            );

            // We get the sites into an array with the site objects
            $sites = get_sites( $get_sites_arguments );

            // If the sites do not fit on one page we display a text saying which sites are showed now and which page
            if ( $count_affected_sites > $per_page ) {
                $to_results = $per_page * $page_number;
                if ( $to_results > $count_affected_sites ) {
                    $to_results = $count_affected_sites;
                }

                echo '<p>';
                printf(
                    esc_html__( 'Showing results %1$d - %2$d on page %3$d/%4$d', 'beyond-multisite' ),
                    ( ( $page_number - 1 ) * $per_page ) + 1,
                    $to_results,
                    $page_number,
                    $pages_count
                );
                echo '</p>';
            }

            // If we were able to get the sites, we display the table with them, otherwise we show an error
            if ( ! empty( $sites ) ) {

                echo '<table class="be-mu-table be-mu-mtop20">';
                if ( 'delete' === $mode ) {
                    $deleted_column_name = __( 'Deleted*', 'beyond-multisite' );
                } else {
                    $deleted_column_name = __( 'For deletion*', 'beyond-multisite' );
                }

                echo '<thead>'
                    . '<tr>'
                    . '<th>' . esc_html__( 'ID', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'URL', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html( $deleted_column_name ) . '</th>'
                    . '<th>' . esc_html__( 'Actions', 'beyond-multisite' ) . '</th>'
                    . '</tr>'
                    . '</thead>';

                echo '<tbody>';

                // We show information about each affected site
                for ( $i = 0; $i < count( $sites ); $i++ ) {

                    // We get the site details
                    $site_details = get_blog_details( $sites[ $i ]->id );

                    // We get the site url
                    $site_url = $site_details->siteurl;

                    echo '<tr>'
                        . '<td data-title="' . esc_attr__( 'ID', 'beyond-multisite' ) . '">' . esc_html( $sites[ $i ]->id ) . '</td>'
                        . '<td data-title="' . esc_attr__( 'URL', 'beyond-multisite' ) . '" class="be-mu-break-word">'
                        . '<a href="' . esc_url( $site_url ) . '" target="_blank">' . esc_url( $site_url ) . '</a></td>'
                        . '<td data-title="' . esc_attr( $deleted_column_name ) . '">'
                        . esc_html( $revision_counts_by_id[ intval( $sites[ $i ]->id) ] ) . '</td>'
                        . '<td data-title="' . esc_attr__( 'Actions', 'beyond-multisite' ) . '"><a href="'
                        . esc_url( get_admin_url( intval( $sites[ $i ]->id ),'edit.php' ) ) . '" target="_blank">'
                        . esc_html__( 'Posts', 'beyond-multisite' ) . '</a> | <a href="'
                        . esc_url( get_admin_url( intval( $sites[ $i ]->id ), 'edit.php?post_type=page' ) ) . '" target="_blank">'
                        . esc_html__( 'Pages', 'beyond-multisite' ) . '</a></td>'
                        . '</tr>';
                }

                echo '</tbody>';

                echo '<tfoot>'
                    . '<tr>'
                    . '<th>' . esc_html__( 'ID', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html__( 'URL', 'beyond-multisite' ) . '</th>'
                    . '<th>' . esc_html( $deleted_column_name ) . '</th>'
                    . '<th>' . esc_html__( 'Actions', 'beyond-multisite' ) . '</th>'
                    . '</tr>'
                    . '</tfoot>';

                echo '</table>';
            } else {
                echo '<p>' . esc_html__( 'An error occurred.', 'beyond-multisite' ) . '</p>';
            }

            // If the sites do not fit on one page we show this dropdown menu to choose a page number to display
            if ( $count_affected_sites > $per_page ) {

                echo '<p class="be-mu-clean-preview-page-navigation">'
                    . '<label for="be-mu-clean-page-number">' . esc_html__( 'Go to page:', 'beyond-multisite' ) . '</label> '
                    . '<select onchange="cleanupRevisionResults()" id="be-mu-clean-page-number" name="be-mu-clean-page-number" size="1">';

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
                    echo '&nbsp;<span onclick="cleanupRevisionNextPreviousPage(' . intval( $page_number - 1 ) . ')" '
                        . 'class="dashicons dashicons-arrow-left-alt2" title="' . esc_attr__( 'Previous Page', 'beyond-multisite' ) . '"></span>';
                }

                if ( $pages_count > $page_number ) {
                    echo '&nbsp;<span onclick="cleanupRevisionNextPreviousPage(' . intval( $page_number + 1 ) . ')" '
                        . 'class="dashicons dashicons-arrow-right-alt2" title="' . esc_attr__( 'Next Page', 'beyond-multisite' ) . '"></span>';
                }

                echo '&nbsp;'
                    . '<img id="be-mu-clean-loading-page-number" src="' . esc_url( be_mu_img_url( 'loading.gif' ) ) . '" />'
                    . '</p>';
            }

            if ( 'delete' === $mode ) {
                echo '<p>' . esc_html__( '* That many revisions were deleted from the site.', 'beyond-multisite' ) . '</p>';
            } else {
                echo '<p>';
                esc_html_e( '* That many revisions would be deleted from the site if you execute the deletion with the current settings.', 'beyond-multisite' );
                echo '</p>';

                echo '<p id="be-mu-clean-revision-preview-bottom-actions" class="be-mu-right-txt">'
                    . '<a id="be-mu-cleanup-revision-export-ids-link" href="javascript:cleanupExportResults(\''
                    . esc_js( esc_attr( $task_id ) ) . '\', \'ids\', 0, ' . intval( $chunks_count ) . ', \'revision\' )">'
                    . esc_html__( 'Export Site IDs', 'beyond-multisite' ) . '</a> | '
                    . '<a id="be-mu-cleanup-revision-export-urls-link" href="javascript:cleanupExportResults(\''
                    . esc_js( esc_attr( $task_id ) ) . '\', \'urls\', 0, ' . intval( $count_affected_sites ) . ', \'revision\' )">'
                    . esc_html__( 'Export Site URLs', 'beyond-multisite' ) . '</a>&nbsp;&nbsp;&nbsp;'
                    . '<input class="button button-primary" onclick="cleanupStartRevisions( \'delete\' )"'
                    . 'type="button" value="' . esc_attr__( 'Execute Revision Deletion!', 'beyond-multisite' ) . '" /></p>';
            }

        }

        ?>

    </div>

    <?php

    wp_die();

}

/**
 * Shows the results of the current site deletion task. It is also used to view sites with specific status from a given task (mode "view").
 * It is called by an ajax request in one of the javascript functions: cleanupSiteResults() or cleanupTaskViewSites().
 */
function be_mu_clean_site_results_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_clean_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // The current page number we are showing results for
    $page_number = intval( $_POST['page_number'] );

    // The current task id that tells us which rows to get from the database
    $task_id = be_mu_strip_all_but_digits_and_letters( $_POST['task_id'] );

    // The mode that we are working with in this task: delete or preview (for executing deletion or previewing it)
    $mode = $_POST['mode'];

    // How many sites to show per page
    $per_page = 10;

    // The mode is not "view", so we are either previewing a task or executing it
    if ( 'view' !== $mode ) {

        // The number of sites that were/would be affected
        $count_affected_sites = intval( $_POST['count_affected_sites'] );

        // The chosen setting for when the sites to be deleted
        $site_delete_time = $_POST['site_delete_time'];

        // The chosen setting for the deletion type
        $site_delete_type = $_POST['site_delete_type'];

        // We get the site ids for the sites to be displayed in the current page of results
        $task_data_multi_array = be_mu_get_task_data( $task_id, 'calculate', $page_number, $per_page, 'be_mu_cleanup', array( 'cleanup_sites_data' ) );

        if ( false != $task_data_multi_array ) {

            // If there are affected sites we set an array with the site ids
            $site_ids = $task_data_multi_array['cleanup_sites_data'];
        } else {
            $count_affected_sites = 0;
        }

        // If the mode is exedute deletion and the deletion is scheduled and there are affected sites we scheduled the crons (if they are not already scheduled).
        if ( 'delete' === $mode && 'No cancellation. Execute now!' != $site_delete_time && $count_affected_sites > 0 ) {
            if ( ! wp_next_scheduled( 'be_mu_clean_event_hook_send_emails' ) ) {

                // Cron to send emails and schedule site deletions
                wp_schedule_event( time(), 'be_mu_every_15_sec', 'be_mu_clean_event_hook_send_emails' );
            }

            if ( ! wp_next_scheduled( 'be_mu_clean_event_hook_delete_sites' ) ) {

                // Cron to delete sites when the time comes
                wp_schedule_event( time(), 'be_mu_every_9_sec', 'be_mu_clean_event_hook_delete_sites' );
            }
        }

    // The mode is "view", which means we are viewing sites with a specific status from a given task
    } else {

        // Sites with which status to display
        $status = $_POST['status'];

        // We get the site ids for the sites to be displayed in the current page of results
        $task_data_multi_array = be_mu_get_specific_task_data( $task_id, 'calculate', $page_number, $per_page, 'be_mu_scheduled_site_deletions',
            array( 'site_id' ), 'status', '%s', $status );

        if ( false != $task_data_multi_array ) {

            // If there are affected sites we set an array with the site ids
            $site_ids = $task_data_multi_array['site_id'];

            // The number of sites that were affected
            $count_affected_sites = intval( $task_data_multi_array['total_results_count'] );
        } else {
            $count_affected_sites = 0;
        }
    }

    // We calculate the total number of pages in the results for the task
    $pages_count = ceil( $count_affected_sites / $per_page );

    ?>

    <div class='be-mu-p20'>
        <h2 class='be-mu-clean-h2'>
            <?php
                if ( 'delete' === $mode ) {
                    esc_html_e( 'Site Deletion Completed', 'beyond-multisite' );
                } elseif ( 'view' === $mode ) {
                    if ( 'cancelled' === $status ) {
                        printf( esc_html__( 'Cancelled Site Deletions (Task ID: %s)', 'beyond-multisite' ), $task_id );
                    } elseif ( 'scheduled' === $status ) {
                        printf( esc_html__( 'Scheduled Site Deletions (Task ID: %s)', 'beyond-multisite' ), $task_id );
                    }
                } else {
                    esc_html_e( 'Preview Site Deletion', 'beyond-multisite' );
                }
            ?>
            <div class='be-mu-right'>
                <?php
                    if ( 'delete' === $mode && 'No cancellation. Execute now!' != $site_delete_time ) {
                        echo be_mu_clean_get_close_sites_and_reload();
                    } else {
                        echo be_mu_clean_get_close_sites();
                    }
                ?>
            </div>
        </h2>

        <p class='be-mu-1-15-em'><b>
            <?php
                echo esc_html__( 'Affected sites:', 'beyond-multisite' ) . ' ' . intval( $count_affected_sites );
            ?>
        </b></p>

        <?php

        if ( 'preview' === $mode && 'No cancellation. Execute now!' != $site_delete_time ) {
            echo '<p>' . esc_html__( 'NOTICE! This is a scheduled deletion, so archived, spammed, or deleted sites have been excluded.', 'beyond-multisite' )
                . '</p>';
        }

        // If there are any affected sites we will show a table with the sites
        if ( $count_affected_sites > 0 ) {

            global $wpdb;
            $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
            $db_table = $main_blog_prefix . "be_mu_cleanup";
            $chunks_count = $wpdb->get_var( $wpdb->prepare( 'SELECT count(row_id) FROM ' . $db_table . ' WHERE task_id = %s', $task_id ) );

            // These are the settings for the get_sites function, we set it to get only the sites we want
            $get_sites_arguments = array(
                'site__in' => $site_ids,
            );

            // We get the sites into an array with the site objects
            $sites = get_sites( $get_sites_arguments );

            /**
             * If the sites do not fit on one page (and the sites are not totally deleted based on the deletion settings)
             * we display a text saying which sites are showed now and which page.
             */
            if ( $count_affected_sites > $per_page && ! ( 'delete' === $mode && 'No cancellation. Execute now!' === $site_delete_time
                && 'Permanent deletion' === $site_delete_type ) ) {
                $to_results = $per_page * $page_number;
                if ( $to_results > $count_affected_sites ) {
                    $to_results = $count_affected_sites;
                }

                echo '<p>';
                printf(
                    esc_html__( 'Showing results %1$d - %2$d on page %3$d/%4$d', 'beyond-multisite' ),
                    ( ( $page_number - 1 ) * $per_page ) + 1,
                    $to_results,
                    $page_number,
                    $pages_count
                );
                echo '</p>';
            }

            // If we were able to get the sites, we display the table with them, otherwise we show an error
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

                // We show information about each affected site
                for ( $i = 0; $i < count( $sites ); $i++ ) {

                    // We get the site details
                    $site_details = get_blog_details( $sites[ $i ]->id );

                    // We get the site url
                    $site_url = $site_details->siteurl;

                    echo '<tr>'
                        . '<td data-title="' . esc_attr__( 'ID', 'beyond-multisite' ) . '">' . esc_html( $sites[ $i ]->id ) . '</td>'
                        . '<td data-title="' . esc_attr__( 'URL', 'beyond-multisite' ) . '" class="be-mu-break-word">'
                        . '<a href="' . esc_url( $site_url ) . '" target="_blank">' . esc_url( $site_url ) . '</a></td>'
                        . '<td data-title="' . esc_attr__( 'Actions', 'beyond-multisite' ) . '"><a href="'
                        . esc_url( network_admin_url( 'site-info.php?id=' . intval( $sites[ $i ]->id ) ) )
                        . '" target="_blank">' . esc_html__( 'Edit', 'beyond-multisite' ) . '</a> | <a href="'
                        . esc_url( get_admin_url( intval( $sites[ $i ]->id ) ) ) . '" target="_blank">'
                        . esc_html__( 'Dashboard', 'beyond-multisite' ) . '</a></td>'
                        . '</tr>';
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

            // If the sites are completely deleted we cannot show data for them
            } elseif ( 'delete' === $mode && 'No cancellation. Execute now!' === $site_delete_time && 'Permanent deletion' === $site_delete_type ) {
                echo '<p>' . esc_html__( 'No additional details available for permanently deleted sites.', 'beyond-multisite' ) . '</p>';
            } else {
                echo '<p>' . esc_html__( 'An error occurred.', 'beyond-multisite' ) . '</p>';
            }

            /**
             * If the sites do not fit on one page (and the sites are not totally deleted based on the deletion settings)
             * we show this dropdown menu to choose a page number to display.
             */
            if ( $count_affected_sites > $per_page && ! ( 'delete' === $mode && 'No cancellation. Execute now!' === $site_delete_time
                && 'Permanent deletion' === $site_delete_type ) ) {

                if ( 'view' !== $mode ) {
                    $on_change_page = 'cleanupSiteResults()';
                } else {
                    $on_change_page = 'cleanupTaskViewSites( \'' . esc_js( esc_attr( $task_id ) ) . '\', \'' . esc_js( esc_attr( $status ) ) . '\', \'no\' )';
                }

                echo '<p class="be-mu-clean-preview-page-navigation">'
                    . '<label for="be-mu-clean-page-number">' . esc_html__( 'Go to page:', 'beyond-multisite' ) . '</label> '
                    . '<select onchange="' . $on_change_page . '" id="be-mu-clean-page-number" name="be-mu-clean-page-number" size="1">';

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
                    if ( 'view' === $mode ) {
                        echo '&nbsp;<span onclick="cleanupSiteViewNextPreviousPage(' . intval( $page_number - 1 )
                            . ', \'' . esc_js( esc_attr( $task_id ) ) . '\', \'' . esc_js( esc_attr( $status ) ) . '\', \'no\' )" '
                            . 'class="dashicons dashicons-arrow-left-alt2" title="' . esc_attr__( 'Previous Page', 'beyond-multisite' ) . '"></span>';
                    } else {
                        echo '&nbsp;<span onclick="cleanupSiteNextPreviousPage(' . intval( $page_number - 1 ) . ')" '
                            . 'class="dashicons dashicons-arrow-left-alt2" title="' . esc_attr__( 'Previous Page', 'beyond-multisite' ) . '"></span>';
                    }
                }

                if ( $pages_count > $page_number ) {
                    if ( 'view' === $mode ) {
                        echo '&nbsp;<span onclick="cleanupSiteViewNextPreviousPage(' . intval( $page_number + 1 )
                            . ', \'' . esc_js( esc_attr( $task_id ) ) . '\', \'' . esc_js( esc_attr( $status ) ) . '\', \'no\' )" '
                            . 'class="dashicons dashicons-arrow-right-alt2" title="' . esc_attr__( 'Next Page', 'beyond-multisite' ) . '"></span>';
                    } else {
                        echo '&nbsp;<span onclick="cleanupSiteNextPreviousPage(' . intval( $page_number + 1 ) . ')" '
                            . 'class="dashicons dashicons-arrow-right-alt2" title="' . esc_attr__( 'Next Page', 'beyond-multisite' ) . '"></span>';
                    }
                }

                echo '&nbsp;'
                    . '<img id="be-mu-clean-loading-page-number" src="' . esc_url( be_mu_img_url( 'loading.gif' ) ) . '" />'
                    . '</p>';
            }

            if ( 'delete' != $mode && 'view' != $mode ) {
                echo '<p id="be-mu-clean-site-preview-bottom-actions" class="be-mu-right-txt">'
                    . '<a id="be-mu-cleanup-site-export-ids-link" href="javascript:cleanupExportResults(\''
                    . esc_js( esc_attr( $task_id ) ) . '\', \'ids\', 0, ' . intval( $chunks_count ) . ', \'site\' )">'
                    . esc_html__( 'Export Site IDs', 'beyond-multisite' ) . '</a> | '
                    . '<a id="be-mu-cleanup-site-export-urls-link" href="javascript:cleanupExportResults(\''
                    . esc_js( esc_attr( $task_id ) ) . '\', \'urls\', 0, ' . intval( $count_affected_sites ) . ', \'site\' )">'
                    . esc_html__( 'Export Site URLs', 'beyond-multisite' ) . '</a>&nbsp;&nbsp;&nbsp;'
                    . '<input class="button button-primary" onclick="cleanupStartSite( \'delete\' )"'
                    . 'type="button" value="' . esc_attr__( 'Execute Site Deletion!', 'beyond-multisite' ) . '" /></p>';
            }
        }

        ?>

    </div>

    <?php

    wp_die();

}

// Handles the ajax request to export a file with IDs or URLs from the preview results of a cleanup task
function be_mu_clean_export_results_callback() {

    // We start a timer to know how much time the current request runs and abort if the time limit is reached
    $time1 = microtime( true );

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_clean_nonce', 'security', false ) ) {
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

    $db_table = $main_blog_prefix . 'be_mu_cleanup';

    // Get the data for the current task
    $results_multi_array = $wpdb->get_results( $wpdb->prepare( "SELECT cleanup_sites_data FROM " . $db_table
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
                if ( $current_number === $total_count && substr( $results['cleanup_sites_data'], -1 ) === ',' ) {
                    $results['cleanup_sites_data'] = substr_replace( $results['cleanup_sites_data'], "", -1 );
                }

                // Write the data to the file
                be_mu_clean_write_to_export_file( $results['cleanup_sites_data'], $file_path );

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
                $site_ids = $results['cleanup_sites_data'];

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
                    be_mu_clean_write_to_export_file( esc_url( $site_details->siteurl ) . $add_comma, $file_path );

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
function be_mu_clean_write_to_export_file( $string, $file_path ) {
    if ( ! file_exists( $file_path ) || filesize( $file_path ) < 50000000 ) {
        file_put_contents( $file_path, $string, FILE_APPEND | LOCK_EX );
    }
}

// A cron job that goes through the notifications for site deletions and sends emails and schedules the site deletions
function be_mu_clean_send_site_deletion_emails_cron() {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // The database table for the email notifications about site deletions
    $db_table_emails = $main_blog_prefix . 'be_mu_site_deletion_emails';

    $max_send_speed_parts = explode( ' ', be_mu_get_setting( 'be-mu-clean-email-speed' ) );

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

        // We are getting only one email at a time because we might update the status of some emails while we work on this one
        $emails_multi_array = $wpdb->get_results( "SELECT * FROM " . $db_table_emails . " WHERE status='scheduled' OR status='no-admins' LIMIT 1", ARRAY_A );

        if ( ! empty( $emails_multi_array ) ) {

            // Foreach should run only once
            foreach ( $emails_multi_array as $email ) {

                // We calculate the unix time when the site will be deleted
                $unix_time_to_be_deleted = time() + ( $email['delete_after_days'] * 24 * 60 * 60 );

                $blog_details = get_blog_details( $email['site_id'] );

                // If there are no admins to notify, we only add the data about the scheduled site deletion to the database, and mark it as done
                if ( 'no-admins' === $email['status'] ) {
                    be_mu_clean_schedule_site_deletion( $email['task_id'], $email['site_id'], $email['site_delete_type'], $unix_time_to_be_deleted );
                    $wpdb->update(
                        $db_table_emails,
                        array(
                            'status' => 'no-admins-done',
                        ),
                        array(
                            'row_id' => $email['row_id'],
                        ),
                        array( '%s' ),
                        array( '%d' )
                    );

                // There are admins to notify (or at least one)
                } else {

                    if ( 1 == $blog_details->archived || 1 == $blog_details->spam || 1 == $blog_details->deleted ) {

                        // We mark all email notifications regarding this site as skipped, since it is arhived, spam, or deleted
                        $wpdb->update(
                            $db_table_emails,
                            array(
                                'status' => 'skipped',
                            ),
                            array(
                                'site_id' => $email['site_id'],
                            ),
                            array( '%s' ),
                            array( '%d' )
                        );

                        // We add the data about the scheduled site deletion to the database
                        be_mu_clean_schedule_site_deletion( $email['task_id'], $email['site_id'], $email['site_delete_type'], $unix_time_to_be_deleted );

                    // The site is not arhived, spam, or deleted so we will notify the admins about the deletion
                    } else {

                        // We get all sites for this email that we will send to, so we can notify the person all at once about all his affected sites
                        $sites_multi_array = $wpdb->get_results( $wpdb->prepare( "SELECT row_id, site_id FROM " . $db_table_emails
                            . " WHERE status = 'scheduled' AND to_email = %s AND task_id = %s", $email['to_email'], $email['task_id'] ), ARRAY_A );

                        $site_ids = $row_ids = Array();

                        // We go through all affected sites of the user
                        foreach ( $sites_multi_array as $site ) {

                            $site_id = intval( $site['site_id'] );

                            // All affected site ids for the current admin email we will notify
                            $site_ids[] = $site_id;

                            // All row ids we need to update and set their status to sent
                            $row_ids[] = intval( $site['row_id'] );

                            // We add the data about the scheduled site deletion to the database
                            be_mu_clean_schedule_site_deletion( $email['task_id'], $site_id, $email['site_delete_type'], $unix_time_to_be_deleted );
                        }

                        // We apply the shortcode for the list of affected user sites for the email message
                        $email_message = be_mu_clean_apply_message_sites_shortcode( $email['message'], $site_ids );

                        /*
                         * We send the email and get the status.
                         * We use html_entity_decode to decode the emojis if used (since we encoded them for the fields that don't support html)
                         */
                        $status = be_mu_send_email( $email['from_email'], html_entity_decode( $email['from_name'] ),
                            $email['to_email'], html_entity_decode( $email['subject'] ), $email_message );

                        // If the email was sent successfully we mark all the notifications in the database for this user as sent
                        if ( $status ) {

                            foreach ( $row_ids as $row_id ) {

                                $wpdb->update(
                                    $db_table_emails,
                                    array(
                                        'status' => 'sent',
                                    ),
                                    array(
                                        'row_id' => $row_id,
                                    ),
                                    array( '%s' ),
                                    array( '%d' )
                                );
                            }

                        // If the email was not sent successfully we mark all the notifications in the database for this user as failed
                        } else {

                            foreach ( $row_ids as $row_id) {

                                $wpdb->update(
                                    $db_table_emails,
                                    array(
                                        'status' => 'failed',
                                    ),
                                    array(
                                        'row_id' => $row_id,
                                    ),
                                    array( '%s' ),
                                    array( '%d' )
                                );
                            }
                        }
                    }
                }
            }
        }
    }
}

// A cron job that goes through the scheduled site deletions and actually deletes the sites if the time has come
function be_mu_clean_delete_scheduled_sites_cron() {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // The database table for the scheduled site deletions
    $db_table_deletions = $main_blog_prefix . 'be_mu_scheduled_site_deletions';

    // Try to get 3 sites that are scheduled for deletion and their time has come
    $sites_multi_array = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $db_table_deletions
        . " WHERE status = 'scheduled' AND unix_time_to_be_deleted <= %d LIMIT 3", time() ), ARRAY_A );

    // If there are sites for deletion
    if ( ! empty( $sites_multi_array ) ) {

        // If there are sites for deletion
        foreach ( $sites_multi_array as $site ) {

            // Delete site and drop database tables
            if ( 'Permanent deletion' === $site['deletion_type'] ) {
                wpmu_delete_blog( $site['site_id'], true );

            // Mark site as deleted and change the last updated time
            } elseif ( 'Mark as deleted (change last updated time)' === $site['deletion_type'] ) {
                update_blog_status( $site['site_id'], 'deleted', '1' );

            // Mark site as deleted and then set the previous last udpated time
            } elseif ( 'Mark as deleted (keep last updated time)' === $site['deletion_type'] ) {
                $object_site = get_site( $site['site_id'] );
                update_blog_status( $site['site_id'], 'deleted', '1' );
                update_blog_status( $site['site_id'], 'last_updated', $object_site->last_updated );

            // Mark site as archived and change the last updated time
            } elseif ( 'Mark as archived (change last updated time)' === $site['deletion_type'] ) {
                update_blog_status( $site['site_id'], 'archived', '1' );

            // Mark site as archived and then set the previous last udpated time
            } else {
                $object_site = get_site( $site['site_id'] );
                update_blog_status( $site['site_id'], 'archived', '1' );
                update_blog_status( $site['site_id'], 'last_updated', $object_site->last_updated );
            }

            // We update the status of the site as deleted in order to show task statistics
            $wpdb->update( $db_table_deletions, array( 'status' => 'deleted' ), array( 'row_id' => $site['row_id'] ), array( '%s' ), array( '%d' ) );
        }
    }
}

// Adds a big red message and a cancellation button to the top of the admin dashboard of the sites that are scheduled for deletion
function be_mu_clean_admin_notice_site_deletion() {

    // We get the unix time when a site is scheduled to be deleted, or false if it is not
    $to_be_deleted = be_mu_clean_site_to_be_deleted();

    // If the user is a site administrator or a network administrator and the site is scheduled for deletion, we show the message
    if ( ( current_user_can( 'administrator' ) || current_user_can( 'manage_network' ) ) && false !== $to_be_deleted ) {
        ?>
        <div class="be-mu-clean-red-deletion-message" id="be-mu-clean-red-deletion-message-id">
            <img class="be-mu-clean-caution-img" src="<?php echo esc_url( be_mu_img_url( 'caution.png' ) ); ?>" />
            <p class="be-mu-clean-deletion-text">
                <?php
                printf(
                    esc_html__( 'This site is scheduled for deletion on %1$s at %2$s %3$s!%4$sClick the button below to cancel the deletion.',
                        'beyond-multisite' ),
                    esc_html( be_mu_unixtime_to_wp_date( $to_be_deleted ) ),
                    esc_html( be_mu_unixtime_to_wp_time( $to_be_deleted ) ),
                    esc_html( be_mu_get_wp_time_zone() ),
                    '<br />'
                );
                ?>
            </p>
            <p>
                <input class='button' onclick='cleanupCancelSiteDeletion()' type='button'
                    value='<?php esc_attr_e( 'Cancel Deletion!', 'beyond-multisite' ); ?>' />&nbsp;&nbsp;
                <span id="be-mu-clean-loading-cancel-deletion"></span>
            </p>
        </div>
        <?php
    }
}

// Cancels the site deletion for the current site
function be_mu_clean_cancel_site_deletion_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_clean_cancel_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // If it is not a super admin and not an admin we abort
    if ( ! current_user_can( 'manage_network' ) && ! current_user_can( 'administrator' ) ) {
        wp_die( 'no-access' );
    }

    $site_id = get_current_blog_id();

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // The database table for the scheduled site deletions
    $db_table_deletions = $main_blog_prefix . 'be_mu_scheduled_site_deletions';

    // We update the status of the site deletion for this site to be cancelled
    $status = $wpdb->update( $db_table_deletions, array( 'status' => 'cancelled' ), array( 'site_id' => $site_id ), array( '%s' ), array( '%d' ) );

    // Add an option that tells us that the deletion for this site has been cancelled in the past. This way we can exclude it from future deletions if we want.
    add_blog_option( $site_id, 'be-mu-cancelled-site-deletion', 'Yes' );

    // We could not update the status
    if ( false === $status ) {
        wp_die( 'error-updating' );
    }

    wp_die();
}

// Sends a test email notification about site deletion, this is done from within the settings of the module
function be_mu_clean_send_test_email_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_ajax_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    // This is the email that we will send to
    $test_email = $_POST['test_email'];

    // If the email is invalid, we stop and show an error code
    if ( ! filter_var( $test_email, FILTER_VALIDATE_EMAIL ) ) {
        wp_die( 'invalid-email' );
    }

    // We get the current settings for the notification email
    $from_email = be_mu_get_setting( 'be-mu-clean-from-email' );
    $from_name = be_mu_get_setting( 'be-mu-clean-from-name' );
    $subject = be_mu_get_setting( 'be-mu-clean-subject' );
    $message = be_mu_get_setting( 'be-mu-clean-message' );

    // We try to get the data for the first site that is not the main site, we will use it for the test notification
    $site = get_sites( array( 'site__not_in' => array( be_mu_get_main_site_id() ), 'orderby' => 'id', 'order' => 'ASC', 'number' => '1' ) );

    // If there are no other sites we will use the main site
    if ( empty( $site ) ) {
        $test_site_id = be_mu_get_main_site_id();

    // If we found another site, we will use it for the test email
    } else {
        $test_site_id = intval( $site[0]->id );
    }

    // We apply the shortcodes to the email message text
    $message = be_mu_clean_apply_message_shortcodes( $message, 'Schedule, notify, and wait 7 days' );
    $message = be_mu_clean_apply_message_sites_shortcode( $message, array( $test_site_id ) );

    // We send the email. We decode html entity to make emojis work.
    $status = be_mu_send_email( $from_email, html_entity_decode( $from_name ), $test_email, html_entity_decode( $subject ), $message );
            
    // If the sending failed, we show an error
    if ( ! $status ) {
        wp_die( 'failed-send' );
    }

    wp_die();
}

// Cancels or completes a site deletion task, which are the same - deletes the database information and stops the cron jobs.
function be_mu_clean_cancel_or_complete_site_deletion_task_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_clean_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die( 'no-access' );
    }

    $task_id = be_mu_strip_all_but_digits_and_letters( $_POST['task_id'] );

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // The database table for the scheduled site deletions
    $db_table_deletions = $main_blog_prefix . 'be_mu_scheduled_site_deletions';

    // The database table for the email notifications about site deletions
    $db_table_emails = $main_blog_prefix . 'be_mu_site_deletion_emails';

    // We delete all data about the task
    $wpdb->query( $wpdb->prepare( "DELETE FROM " . $db_table_deletions . " WHERE task_id = %s", $task_id ) );
    $wpdb->query( $wpdb->prepare( "DELETE FROM " . $db_table_emails . " WHERE task_id = %s", $task_id ) );

    // We clear all scheduled crons related to side deletions for now, since there is no active task now
    wp_clear_scheduled_hook( 'be_mu_clean_event_hook_send_emails' );
    wp_clear_scheduled_hook( 'be_mu_clean_event_hook_delete_sites' );

    wp_die();
}

/**
 * Returns the html code for the button that closes the comment deletion
 * @return string
 */
function be_mu_clean_get_close_comments() {
    return '<input type="button" class="button be-mu-mleft10imp" onclick="cleanupCloseAbortComments()" value="'
        . esc_attr__( 'Close', 'beyond-multisite' ) . '" />';
}

/**
 * Return the html code for the button that closes the revision deletion
 * @return string
 */
function be_mu_clean_get_close_revisions() {
    return '<input type="button" class="button be-mu-mleft10imp" onclick="cleanupCloseAbortRevisions()" value="'
        . esc_attr__( 'Close', 'beyond-multisite' ) . '" />';
}

/**
 * Return the html code for the button that closes the site deletion
 * @return string
 */
function be_mu_clean_get_close_sites() {
    return '<input type="button" class="button be-mu-mleft10imp" onclick="cleanupCloseAbortSites( \'no-reload\' )" value="'
        . esc_attr__( 'Close', 'beyond-multisite' ) . '" />';
}

/**
 * Return the html code for the button that closes the site deletion and reloads the page
 * @return string
 */
function be_mu_clean_get_close_sites_and_reload() {
    return '<input type="button" class="button be-mu-mleft10imp" onclick="cleanupCloseAbortSites( \'reload\' )" value="'
        . esc_attr__( 'Close', 'beyond-multisite' ) . '" />';
}

/**
 * Return the html code for the button that closes the leftover database table deletion
 * @return string
 */
function be_mu_clean_get_close_tables() {
    return '<input type="button" class="button be-mu-mleft10imp" onclick="cleanupCloseAbortTables()" value="'
        . esc_attr__( 'Close', 'beyond-multisite' ) . '" />';
}

/**
 * Return the html code for the button that closes the no role users deletion
 * @return string
 */
function be_mu_clean_get_close_users() {
    return '<input type="button" class="button be-mu-mleft10imp" onclick="cleanupCloseAbortUsers()" value="'
        . esc_attr__( 'Close', 'beyond-multisite' ) . '" />';
}

/**
 * Return the html code for the button that aborts the comment deletion
 * @return string
 */
function be_mu_clean_get_abort_comments() {
    return '<input type="button" class="button" onclick="cleanupCloseAbortComments()" value="'
        . esc_attr__( 'Abort', 'beyond-multisite' ) . '" />';
}

/**
 * Return the html code for the button that aborts the revision deletion
 * @return string
 */
function be_mu_clean_get_abort_revisions() {
    return '<input type="button" class="button" onclick="cleanupCloseAbortRevisions()" value="'
        . esc_attr__( 'Abort', 'beyond-multisite' ) . '" />';
}

/**
 * Return the html code for the button that aborts the site deletion
 * @return string
 */
function be_mu_clean_get_abort_sites() {
    return '<input type="button" class="button" onclick="cleanupCloseAbortSites( \'no-reload\' )" value="'
        . esc_attr__( 'Abort', 'beyond-multisite' ) . '" />';
}

/**
 * Return the html code for the button that aborts the leftover database table deletion
 * @return string
 */
function be_mu_clean_get_abort_tables() {
    return '<input type="button" class="button" onclick="cleanupCloseAbortTables()" value="'
        . esc_attr__( 'Abort', 'beyond-multisite' ) . '" />';
}

/**
 * Return the html code for the button that aborts the leftover database table deletion
 * @return string
 */
function be_mu_clean_get_abort_users() {
    return '<input type="button" class="button" onclick="cleanupCloseAbortUsers()" value="'
        . esc_attr__( 'Abort', 'beyond-multisite' ) . '" />';
}

// If the site is scheduled for deletion returns the unix time of deleteion and if it is not, returns false
function be_mu_clean_site_to_be_deleted() {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // The database table for the scheduled site deletions
    $db_table_deletions = $main_blog_prefix . 'be_mu_scheduled_site_deletions';

    // The id of the current site
    $site_id = get_current_blog_id();

    // We try to get data about a scheduled site deletion about the current site
    $results_multi_array = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $db_table_deletions
        . " WHERE site_id = %d AND status='scheduled'", $site_id ), ARRAY_A );

    // If the site is scheduled for deletion we will return the time of deletion
    if ( ! empty( $results_multi_array ) ) {
        return intval( $results_multi_array[0]['unix_time_to_be_deleted'] );

    // If the site is not scheduled for deletion we return false
    } else {
        return false;
    }
}

/**
 * Creates and returns the mysql query to use on each affected site by comment deletion based on the selected settings for the task
 * @param string $comment_status
 * @param string $comment_url_count
 * @param string $comment_datetime
 * @return mixed
 */
function be_mu_clean_build_comment_query_where( $comment_status, $comment_url_count, $comment_datetime ) {

    if ( 'Any' == $comment_status ) {
        $where_comment_st_string = "";
    } elseif ( 'Pending' == $comment_status ) {
        $where_comment_st_string = " AND comment_approved = '0' ";
    } elseif ( 'Approved' == $comment_status ) {
        $where_comment_st_string = " AND comment_approved = '1' ";
    } elseif ( 'Spammed' == $comment_status ) {
        $where_comment_st_string = " AND comment_approved = 'spam' ";
    } elseif ( 'Trashed' == $comment_status ) {
        $where_comment_st_string = " AND comment_approved = 'trash' ";
    } elseif ( 'Post trashed' == $comment_status ) {
        $where_comment_st_string = " AND comment_approved = 'post-trashed' ";
    } else {
        return false;
    }

    if ( 'Any' == $comment_url_count ) {
        $where_comment_url_string = "";
    } elseif ( 'Filled URL field' == $comment_url_count ) {
        $where_comment_url_string = " AND comment_author_url != '' ";
    } elseif ( 'Has a URL in the text' == $comment_url_count ) {
        $where_comment_url_string = " AND ( LOCATE( 'http://', comment_content ) != 0 OR LOCATE( 'https://', comment_content ) != 0 "
            . "OR LOCATE( 'ftp://', comment_content ) != 0 OR LOCATE( 'sftp://', comment_content ) != 0 OR LOCATE( 'www.', comment_content ) != 0 ) ";
    } elseif ( 'Filled URL field or URL in the text' == $comment_url_count ) {
        $where_comment_url_string = " AND ( comment_author_url != '' OR ( LOCATE( 'http://', comment_content ) != 0 OR LOCATE( 'https://', comment_content ) != 0 "
            . "OR LOCATE( 'ftp://', comment_content ) != 0 OR LOCATE( 'sftp://', comment_content ) != 0 OR LOCATE( 'www.', comment_content ) != 0 ) ) ";
    } elseif ( 'Filled URL field and URL in the text' == $comment_url_count ) {
        $where_comment_url_string = " AND comment_author_url != '' AND ( LOCATE( 'http://', comment_content ) != 0 OR LOCATE( 'https://', comment_content ) != 0 "
            . "OR LOCATE( 'ftp://', comment_content ) != 0 OR LOCATE( 'sftp://', comment_content ) != 0 OR LOCATE( 'www.', comment_content ) != 0 ) ";
    } else {
        return false;
    }

    if ( 'Any' == $comment_datetime ) {
        $where_comment_datetime_string = "";
    } elseif ( preg_match( '/Older than [1-9][0-9]{0,3} days/', $comment_datetime ) ) {
        $number_days = intval( preg_replace( '/[^0-9]/', '', $comment_datetime ) );
        $gmt_before_X_days = get_gmt_from_date( date( 'Y-m-d H:i:s', time() - ( $number_days * 24 * 3600 ) ) );
        $where_comment_datetime_string = " AND comment_date_gmt < '" . esc_sql( $gmt_before_X_days ) . "' ";
    } elseif ( preg_match( '/In the last [1-9][0-9]{0,3} days/', $comment_datetime ) ) {
        $number_days = intval( preg_replace( '/[^0-9]/', '', $comment_datetime ) );
        $gmt_before_X_days = get_gmt_from_date( date( 'Y-m-d H:i:s', time() - ( $number_days * 24 * 3600 ) ) );
        $where_comment_datetime_string = " AND comment_date_gmt >= '" . esc_sql( $gmt_before_X_days ) . "' ";
    } else {
        return false;
    }

    return " WHERE ( comment_type = '' OR comment_type = 'comment' ) " . $where_comment_st_string . $where_comment_url_string . $where_comment_datetime_string;
}

/**
 * Creates and returns the where part of the query for revision deletion based on the setting for revision date/time
 * @param string $revision_datetime
 * @return string
 */
function be_mu_clean_build_revision_where_string( $revision_datetime ) {

    // If $revision_datetime is not Any, than it is a tring like: Older than X days
    if ( 'Any' != $revision_datetime ) {

        // We split the string into all parts separated by a space
        $parts = explode( ' ', $revision_datetime );

        // And we take the third part, which is the number of days and return the where string below
        $older_than_days = intval( $parts[2] );
        return " WHERE post_type = 'revision' AND post_date_gmt < '"
            . esc_sql( get_gmt_from_date( date( 'Y-m-d H:i:s', time() - ( $older_than_days * 24 * 3600 ) ) ) ) . "'";
    }

    // Otherwise $revision_datetime is equal to Any, and we return this string
    return " WHERE post_type = 'revision'";
}

/**
 * Based on the settings it returns an array with the arguments for the get_sites function we use while comment and revision deletion
 * @param string $affect_sites_id_option
 * @param string $affect_sites_ids
 * @param int $offset
 * @return array
 */
function be_mu_clean_build_get_sites_arguments( $affect_sites_id_option, $affect_sites_ids, $offset ) {

    if ( 'Any site ID' == $affect_sites_id_option ) {

        // If one settings says any site and the one for the site ids is filled, this might indicate a mistake, so we show an error code
        if ( '' != $affect_sites_ids ) {
            wp_die( 'site-ids-filled' );
        }

        // Otherwise we return the array with arguments
        return array(
            'offset' => $offset,
            'number' => BE_MU_CLEAN_LIMIT_DATA,
        );
    } elseif ( 'Only these site IDs:' == $affect_sites_id_option ) {

        // If the sites ids field is invalid we show an error code
        if ( ! be_mu_is_comma_separated_numbers( $affect_sites_ids ) ) {
            wp_die( 'site-ids-empty' );
        }

        // Make an array of all the site ids to include
        $include_site_ids = explode( ',', $affect_sites_ids );

        return array(
            'offset' => $offset,
            'number' => BE_MU_CLEAN_LIMIT_DATA,
            'site__in' => $include_site_ids,
        );
    } elseif ( 'All except these site IDs:' == $affect_sites_id_option ) {

        // If the sites ids field is invalid we show an error code
        if ( ! be_mu_is_comma_separated_numbers( $affect_sites_ids ) ) { 
            wp_die( 'site-ids-empty' );
        }

        // Make an array of all the site ids to exclude
        $exclude_site_ids = explode( ',', $affect_sites_ids );

        return array(
            'offset' => $offset,
            'number' => BE_MU_CLEAN_LIMIT_DATA,
            'site__not_in' => $exclude_site_ids,
        );
    } else {
        wp_die( 'invalid-data' );
    }
}

/**
 * Based on the settings it returns an array with the arguments for the get_sites function we use when processing the sites while site deletion
 * @param string $affect_sites_id_option
 * @param string $affect_sites_ids
 * @param string $site_attributes
 * @param string $site_delete_time
 * @param int $offset
 * @return array
 */
function be_mu_clean_site_build_get_sites_arguments( $affect_sites_id_option, $affect_sites_ids, $site_attributes, $site_delete_time, $offset ) {

    if ( 'Any site ID' == $affect_sites_id_option ) {

        // If one settings says any site and the one for the site ids is filled, this might indicate a mistake, so we show an error code
        if ( '' != $affect_sites_ids ) {
            wp_die( 'site-ids-filled' );
        }

        // Otherwise we return the array with arguments
        $get_sites_arguments = array(
            'offset' => $offset,
            'number' => BE_MU_CLEAN_LIMIT_DATA,
        );
    } elseif ( 'Only these site IDs:' == $affect_sites_id_option ) {

        // If the sites ids field is invalid we show an error code
        if ( ! be_mu_is_comma_separated_numbers( $affect_sites_ids ) ) {
            wp_die( 'site-ids-empty' );
        }

        // Make an array of all the site ids to include
        $include_site_ids = explode( ',', $affect_sites_ids );

        // We get the id of the main network site
        $main_site_id = be_mu_get_main_site_id();

        // If the user it trying to delete the main network site, so we show an error code
        if (in_array( $main_site_id, $include_site_ids ) ) {
            wp_die( 'main-site' );
        }

        $get_sites_arguments = array(
            'offset' => $offset,
            'number' => BE_MU_CLEAN_LIMIT_DATA,
            'site__in' => $include_site_ids,
        );
    } elseif ( 'All except these site IDs:' == $affect_sites_id_option ) {

        // If the sites ids field is invalid we show an error code
        if ( ! be_mu_is_comma_separated_numbers( $affect_sites_ids ) ) {
            wp_die( 'site-ids-empty' );
        }

        // Make an array of all the site ids to exclude
        $exclude_site_ids = explode( ',', $affect_sites_ids );

        $get_sites_arguments = array(
            'offset' => $offset,
            'number' => BE_MU_CLEAN_LIMIT_DATA,
            'site__not_in' => $exclude_site_ids,
        );
    } else {
        wp_die( 'invalid-data' );
    }

    // Based on the chosen setting for the site attributes we add more data to the arguments array
    if ( 'Public' == $site_attributes ) {
        $get_sites_arguments['public'] = 1;
    } elseif ( 'Deleted' == $site_attributes ) {
        $get_sites_arguments['deleted'] = 1;
    } elseif ( 'Spam' == $site_attributes ) {
        $get_sites_arguments['spam'] = 1;
    } elseif ( 'Archived' == $site_attributes ) {
        $get_sites_arguments['archived'] = 1;
    } elseif ( 'Mature' == $site_attributes ) {
        $get_sites_arguments['mature'] = 1;
    } elseif ( 'Not public' == $site_attributes ) {
        $get_sites_arguments['public'] = 0;
    } elseif ( 'Not deleted' == $site_attributes ) {
        $get_sites_arguments['deleted'] = 0;
    } elseif ( 'Not spam' == $site_attributes ) {
        $get_sites_arguments['spam'] = 0;
    } elseif ( 'Not archived' == $site_attributes ) {
        $get_sites_arguments['archived'] = 0;
    } elseif ( 'Not mature' == $site_attributes ) {
        $get_sites_arguments['mature'] = 0;
    } elseif ( 'Any' != $site_attributes ) {
        wp_die( 'invalid-data' );
    }

    // If we are scheduling deletion we will skip archived, deleted and spam sites
    if ( 'No cancellation. Execute now!' != $site_delete_time ) {
        $get_sites_arguments['archived'] = 0;
        $get_sites_arguments['spam'] = 0;
        $get_sites_arguments['deleted'] = 0;
    }

    // We always exclude the main site because it cannot be deleted
    $get_sites_arguments['site__not_in'][] = be_mu_get_main_site_id();

    return $get_sites_arguments;
}

/**
 * Based on the selected settings for the current task we could skip some sites with certain amount of comments.
 * This function checks that and returns true if we need to skip the current site.
 * @param string $affect_sites_comment_amount
 * @param string $affect_sites_comment_status
 * @param string $db_table
 * @return bool
 */
function be_mu_clean_comment_determine_site_skip( $affect_sites_comment_amount, $affect_sites_comment_status, $db_table ) {

    // If the setting is not set to any amount of, it is possible to skip some sites, otherwise we skip nothing
    if ( 'Any amount of' != $affect_sites_comment_amount ) {

        // We split the setting string to get the number at the end
        $temp = explode( ' ', $affect_sites_comment_amount );

        // This is at least how many comments there have to be in order to affect this site
        $at_least_comment_count = intval( $temp[2] );

        // Now based on the status of comments that we need to count we set the string var to use later in the query
        if ( 'comments in total' == $affect_sites_comment_status ) {
            $where_sites_comment_st_string = "";
        } elseif ( 'pending comments' == $affect_sites_comment_status ) {
            $where_sites_comment_st_string = " AND comment_approved = '0' ";
        } elseif ( 'approved comments' == $affect_sites_comment_status ) {
            $where_sites_comment_st_string = " AND comment_approved = '1' ";
        } elseif ( 'spammed comments' == $affect_sites_comment_status ) {
            $where_sites_comment_st_string = " AND comment_approved = 'spam' ";
        } elseif ( 'trashed comments' == $affect_sites_comment_status ) {
            $where_sites_comment_st_string = " AND comment_approved = 'trash' ";
        } else {
            wp_die( 'invalid-data' );
        }

        // We need these to connect to the database
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        global $wpdb;

        // At this point we have everything we need to run the query and count the comments of the site (for selected status only)
        $affect_sites_comment_count = $wpdb->get_var( "SELECT COUNT( comment_ID ) FROM " . $db_table
            . " WHERE ( comment_type = '' OR comment_type = 'comment' ) " . $where_sites_comment_st_string );

        // If the site has less comments than the setting for the task says we need in order to affect it, than we return true to skip it
        if ( $affect_sites_comment_count < $at_least_comment_count ) {
            return true;
        }
    }

    // If we got to this point, then the site will not be skipped
    return false;
}

/**
 * Decides based on the settings for the task if we should skip a site during site deletion task; returns true if we should skip it
 * @param int $site_id
 * @param string $site_registered
 * @param string $site_updated
 * @param string $site_posts
 * @param string $site_pages
 * @param string $site_comments
 * @param string $site_skip_cancelled
 * @return bool
 */
function be_mu_clean_site_determine_site_skip( $site_id, $site_registered, $site_updated, $site_posts, $site_pages,
    $site_comments, $site_skip_cancelled ) {

    //get details about the site
    $object_site_details = get_blog_details( $site_id );

    // The unix time of the site registration
    $site_register_unix = strtotime( $object_site_details->registered );

    // The unix time of the last site update
    $site_update_unix = strtotime( $object_site_details->last_updated );

    // If the chosen setting for the registration date is different than Any, it is possible to have to skip the site
    if ( 'Any' != $site_registered ) {

        $site_register_parts = explode( ' ', $site_registered );

        // The chosen setting for the registration date is something like: Older than ...
        if ( 'Older' == $site_register_parts[0] ) {

            // Older than how many days
            $older_than_x_days = intval( $site_register_parts[2] );
            $unix_before_x_days = time() - ( $older_than_x_days * 24 * 3600 );

            // It is not older than X days so we skip it
            if ( $site_register_unix >= $unix_before_x_days ) {

                // Skip the site
                return true;
            }

        // The chosen setting for the registration date is something like: In the last ...
        } else {

            // In tha last how many days
            $in_the_last_x_days = intval( $site_register_parts[3] );
            $unix_before_x_days = time() - ( $in_the_last_x_days * 24 * 3600 );

            // It is not in the last X days so we skip it
            if ( $site_register_unix < $unix_before_x_days ) {

                // Skip the site
                return true;
            }
        }
    }

    // If the chosen setting for the last update date is different than Any, it is possible to have to skip the site
    if ( 'Any' != $site_updated ) {

        $site_update_parts = explode( ' ', $site_updated );

        // The chosen setting for the last update date is something like: Less than X ... after registration
        if ( 'Less' == $site_update_parts[0] ) {

            // Less than how many something
            $less_than_number = intval( $site_update_parts[2] );

            // We set a seconds multiplier based on the chosen setting (minutes, hours or days)
            if ( 'min' == $site_update_parts[3] ) {
                $less_than_multiplier = 60;
            } elseif ( 'hour' == $site_update_parts[3] || 'hours' == $site_update_parts[3] ) {
                $less_than_multiplier = 60 * 60;
            } else {
                $less_than_multiplier = 24 * 60 * 60;
            }

            // Minimum different between registration and last update that causes a site skip
            $less_than_difference = $less_than_number * $less_than_multiplier;

            /*
             * If the difference between the registered time and the last update time is more than or equal to the chosen one
             * or the last update time is before the registered time (which should not be possible usually, but it is)
             * then we skip the site.
             */
            if ( ( $site_update_unix - $site_register_unix ) >= $less_than_difference || $site_update_unix < $site_register_unix ) {

                // Skip the site
                return true;
            }

        // The chosen setting for the last update date is something like: Older than X days
        } elseif ( 'Older' == $site_update_parts[0] ) {

            // Older than how many days
            $older_than_x_days = intval( $site_update_parts[2] );
            $unix_before_x_days = time() - ( $older_than_x_days * 24 * 3600 );

            // It is not older than X days so we skip it
            if ( $site_update_unix >= $unix_before_x_days ) {

                // Skip the site
                return true;
            }

        // The chosen setting for the last update date is something like: In the last X days
        } else {

            // In the last how many days
            $in_the_last_x_days = intval( $site_update_parts[3] );
            $unix_before_x_days = time() - ( $in_the_last_x_days * 24 * 3600 );

            // It is not in the last X days so we skip it
            if ( $site_update_unix < $unix_before_x_days ) {

                // Skip the site
                return true;
            }
        }
    }

    // If the chosen setting for the posts count, pages count or comments count is different than Any, it is possible to have to skip the site
    if ( 'Any' != $site_posts || 'Any' != $site_pages || 'Any' != $site_comments ) {

        // We swith to the current site, so we can run some functions
        switch_to_blog( $site_id );

        // If the chosen setting for the posts count is different than Any, it is possible to have to skip the site
        if ( 'Any' != $site_posts ) {

            // We get the posts counts for the site
            $object_count_posts = wp_count_posts( 'post' );

            // We get the published posts count
            $count_published_posts = intval( $object_count_posts->publish );

            /*
             * If we need to ignore the first default post, we check if it was modified, and if it was not, we decreased the published posts count
             * by 1 and continue as if the chosen setting was 0
             */
            if ( '0 (ignore first post)' === $site_posts ) {
                $first_post = get_post( 1 );
                if ( $first_post !== null && $first_post->post_date === $first_post->post_modified
                    && $count_published_posts > 0 && $first_post->post_status === 'publish' && $first_post->post_type === 'post' ) {
                    $count_published_posts--;
                }
                $site_posts = '0';
            }

            $less_than_posts = 0;
            if ( preg_match( '/Less than [1-9][0-9]{0,8}/', $site_posts ) ) {
                $less_than_posts = intval( preg_replace( '/[^0-9]/', '', $site_posts ) );
            }

            // Based on the setting chosen and the published posts count we decide if we should skip the site
            if ( ( '0' == $site_posts && 0 != $count_published_posts ) || ( '1' == $site_posts && 1 != $count_published_posts )
                || ( '0 or 1' == $site_posts && 1 != $count_published_posts && 0 != $count_published_posts )
                || ( preg_match( '/Less than [1-9][0-9]{0,8}/', $site_posts ) && $count_published_posts >= $less_than_posts ) ) {

                // We go back to our site
                restore_current_blog();

                // Skip the site
                return true;
            }
        }

        // If the chosen setting for the pages count is different than Any, it is possible to have to skip the site
        if ( 'Any' != $site_pages ) {

            // We get the pages counts for the site
            $object_count_pages = wp_count_posts( 'page' );

            // We get the published pages count
            $count_published_pages = intval( $object_count_pages->publish );

            /*
             * If we need to ignore the first default page, we check if it was modified, and if it was not, we decreased the published pages count
             * by 1 and continue as if the chosen setting was 0
             */
            if ( '0 (ignore first page)' === $site_pages ) {
                $first_page = get_post( 2 );
                if ( $first_page !== null && $first_page->post_date === $first_page->post_modified
                    && $count_published_pages > 0 && $first_page->post_status === 'publish' && $first_page->post_type === 'page' ) {
                    $count_published_pages--;
                }
                $site_pages = '0';
            }

            $less_than_pages = 0;
            if ( preg_match( '/Less than [1-9][0-9]{0,8}/', $site_pages ) ) {
                $less_than_pages = intval( preg_replace( '/[^0-9]/', '', $site_pages ) );
            }

            // Based on the setting chosen and the published pages count we decide if we should skip the site
            if ( ( '0' == $site_pages && 0 != $count_published_pages ) || ( '1' == $site_pages && 1 != $count_published_pages )
                || ( '0 or 1' == $site_pages && 1 != $count_published_pages && 0 != $count_published_pages )
                || ( preg_match( '/Less than [1-9][0-9]{0,8}/', $site_pages ) && $count_published_pages >= $less_than_pages ) ) {

                // We go back to our site
                restore_current_blog();

                // Skip the site
                return true;
            }
        }

        // If the chosen setting for the comments count is different than Any, it is possible to have to skip the site
        if ( 'Any' != $site_comments ) {

            // We get the comments counts
            $object_count_comments = wp_count_comments();

            // We get the approved comments count
            $count_approved_comments = intval( $object_count_comments->approved );

            /*
             * If we need to ignore the first default comment, we check if it is approved, and if it was, we decreased the approved comment count
             * by 1 and continue as if the chosen setting was 0
             */
            if ( '0 (ignore first comment)' === $site_comments ) {
                $must_be_variable = 1;
                $first_comment = get_comment( $must_be_variable );
                if ( $first_comment !== null && ( $first_comment->comment_approved === '1' || $first_comment->comment_approved === 1 )
                    && $count_approved_comments > 0 ) {
                    $count_approved_comments--;
                }
                $site_comments = '0';
            }

            $less_than_comments = 0;
            if ( preg_match( '/Less than [1-9][0-9]{0,8}/', $site_comments ) ) {
                $less_than_comments = intval( preg_replace( '/[^0-9]/', '', $site_comments ) );
            }

            // Based on the setting chosen and the approved comments count we decide if we should skip the site
            if ( ( '0' == $site_comments && 0 != $count_approved_comments) || ( '1' == $site_comments && 1 != $count_approved_comments )
                || ( '0 or 1' == $site_comments && 1 != $count_approved_comments && 0 != $count_approved_comments )
                || ( preg_match( '/Less than [1-9][0-9]{0,8}/', $site_comments ) && $count_approved_comments >= $less_than_comments ) ) {

                // We go back to our site
                restore_current_blog();

                // Skip the site
                return true;
            }
        }

        // We go back to our site
        restore_current_blog();
    }

    // If the chosen setting tells us to skip sites that have a cancelled deletion in the past, and the current site has one, we skip it.
    if ( 'Yes' == $site_skip_cancelled && get_blog_option( $site_id, 'be-mu-cancelled-site-deletion' ) == 'Yes' ) {
        return true;
    }

    // If we got this far then we will not skip the site
    return false;
}

/**
 * Checks if the selected settings for the comment deletion are valid and returns true if yes, false if no
 * @param string $comment_status
 * @param string $comment_url_count
 * @param string $comment_datetime
 * @param string $affect_sites_comment_amount
 * @param string $affect_sites_comment_status
 * @return bool
 */
function be_mu_clean_comment_vars_valid( $comment_status, $comment_url_count, $comment_datetime, $affect_sites_comment_amount, $affect_sites_comment_status ) {
    if ( ! in_array( $comment_status, array( 'Any', 'Pending', 'Approved', 'Spammed', 'Trashed', 'Post trashed' ) )
        || ! in_array( $comment_url_count, array( 'Any', 'Filled URL field', 'Has a URL in the text', 'Filled URL field or URL in the text',
            'Filled URL field and URL in the text' ) )
        || ( $comment_datetime != 'Any' && ! preg_match( '/Older than [1-9][0-9]{0,3} days/', $comment_datetime )
            && ! preg_match( '/In the last [1-9][0-9]{0,3} days/', $comment_datetime ) )
        || ( $affect_sites_comment_amount != 'Any amount of' && ! preg_match( '/At least [1-9][0-9]{0,8}/', $affect_sites_comment_amount ) )
        || ! in_array( $affect_sites_comment_status, array( 'comments in total', 'pending comments', 'approved comments',
            'spammed comments', 'trashed comments' ) ) ) {
        return false;
    }
    return true;
}

/**
 * Checks if the selected settings for the revision deletion are valid and returns true if yes, false if no
 * @param string $revision_datetime
 * @param string $revision_exclude
 * @return bool
 */
function be_mu_clean_revision_vars_valid( $revision_datetime, $revision_exclude ) {
    if ( ( ! in_array( $revision_datetime, array( 'Any', 'Older than 1 day' ) ) && ! preg_match( '/Older than [1-9][0-9]{0,3} days/', $revision_datetime ) )
        || ( $revision_exclude != 'None' && ! preg_match( '/The [1-9][0-9]{0,8} most recent for each post/', $revision_exclude ) ) ) {
        return false;
    }
    return true;
}

/**
 * Checks if the selected settings for the site deletion are valid and returns true if yes, false if no
 * @param string $site_attributes
 * @param string $site_registered
 * @param string $site_updated
 * @param string $site_posts
 * @param string $site_pages
 * @param string $site_comments
 * @param string $site_delete_type
 * @param string $site_delete_time
 * @param string $site_skip_cancelled
 * @return bool
 */
function be_mu_clean_site_vars_valid( $site_attributes, $site_registered, $site_updated, $site_posts, $site_pages, $site_comments,
    $site_delete_type, $site_delete_time, $site_skip_cancelled ) {
    if ( ! in_array( $site_attributes, array( 'Any', 'Public', 'Deleted', 'Spam', 'Archived', 'Mature', 'Not public',
            'Not deleted', 'Not spam', 'Not archived', 'Not mature' ) )
        || ( $site_registered != 'Any' && ! preg_match( '/Older than [1-9][0-9]{0,3} days/', $site_registered )
            && ! preg_match( '/In the last [1-9][0-9]{0,3} days/', $site_registered ) )
        || ( $site_updated != 'Any' && ! preg_match( '/Older than [1-9][0-9]{0,3} days/', $site_updated )
            && ! preg_match( '/In the last [1-9][0-9]{0,3} days/', $site_updated )
            && ! preg_match( '/Less than [1-9][0-9]{0,3} min after registration/', $site_updated )
            && ! preg_match( '/Less than [1-9][0-9]{0,3} hours after registration/', $site_updated )
            && ! preg_match( '/Less than [1-9][0-9]{0,3} days after registration/', $site_updated )
            && $site_updated !== 'Less than 1 day after registration' )
        || ( ! in_array( $site_posts, array( 'Any', '0', '1', '0 or 1', '0 (ignore first post)' ) ) && ! preg_match( '/Less than [1-9][0-9]{0,8}/', $site_posts ) )
        || ( ! in_array( $site_pages, array( 'Any', '0', '1', '0 or 1', '0 (ignore first page)' ) ) && ! preg_match( '/Less than [1-9][0-9]{0,8}/', $site_pages ) )
        || ( ! in_array( $site_comments, array( 'Any', '0', '1', '0 or 1', '0 (ignore first comment)' ) )
        && ! preg_match( '/Less than [1-9][0-9]{0,8}/', $site_comments ) )
        || ! in_array( $site_delete_type, array( 'Permanent deletion', 'Mark as deleted (change last updated time)', 'Mark as deleted (keep last updated time)',
            'Mark as archived (change last updated time)', 'Mark as archived (keep last updated time)' ) )
        || ( $site_delete_time != 'No cancellation. Execute now!' && ! preg_match( '/Schedule, notify, and wait [1-9][0-9]{0,3} days/', $site_delete_time ) )
        || ! in_array( $site_skip_cancelled, array( 'Yes', 'No' ) ) ) {
        return false;
    }
    return true;
}

// Creates the database tables to store the data for the cleanup module
function be_mu_clean_db_tables() {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // This is the query that will create the database table (if it does not exist) for the cleanup tasks
    $sql = 'CREATE TABLE IF NOT EXISTS ' . $main_blog_prefix . 'be_mu_cleanup ( '
        . 'row_id bigint( 20 ) NOT NULL AUTO_INCREMENT, '
        . 'task_id varchar( 100 ) DEFAULT NULL, '
        . 'cleanup_sites_data longtext DEFAULT NULL, '
        . 'cleanup_data_1 longtext DEFAULT NULL, '
        . 'cleanup_data_2 longtext DEFAULT NULL, '
        . 'unix_time_added int( 11 ) NOT NULL, '
        . 'PRIMARY KEY ( row_id ) '
        . ') ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;';

    // Execute the query
    dbDelta( $sql );

    // This is the query that will create the database table (if it does not exist) for the scheduled site deletions
    $sql = 'CREATE TABLE IF NOT EXISTS ' . $main_blog_prefix . 'be_mu_scheduled_site_deletions ( '
        . 'row_id bigint( 20 ) NOT NULL AUTO_INCREMENT, '
        . 'task_id varchar( 100 ) DEFAULT NULL, '
        . 'deletion_type varchar( 200 ) DEFAULT NULL, '
        . 'status varchar( 200 ) DEFAULT NULL, '
        . 'site_id int( 11 ) NOT NULL, '
        . 'unix_time_added int( 11 ) NOT NULL, '
        . 'unix_time_to_be_deleted int( 11 ) NOT NULL, '
        . 'PRIMARY KEY ( row_id ) '
        . ') ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;';

    // Execute the query
    dbDelta( $sql );

    // This is the query that will create the database table (if it does not exist) for the scheduled email notifications for site deletions
    $sql = 'CREATE TABLE IF NOT EXISTS ' . $main_blog_prefix . 'be_mu_site_deletion_emails ( '
        . 'row_id bigint( 20 ) NOT NULL AUTO_INCREMENT, '
        . 'task_id varchar( 100 ) DEFAULT NULL, '
        . 'site_id int( 11 ) NOT NULL, '
        . 'site_delete_type varchar( 200 ) DEFAULT NULL, '
        . 'delete_after_days int( 11 ) NOT NULL, '
        . 'from_name text DEFAULT NULL, '
        . 'from_email varchar( 200 ) DEFAULT NULL, '
        . 'to_email varchar( 200 ) DEFAULT NULL, '
        . 'subject text DEFAULT NULL, '
        . 'message longtext DEFAULT NULL, '
        . 'status varchar( 200 ) DEFAULT NULL, '
        . 'unix_time_added int( 11 ) NOT NULL, '
        . 'PRIMARY KEY ( row_id ) '
        . ') ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;';


    // Execute the query
    dbDelta( $sql );
}

/**
 * Adds data about the results from a cleanup request to the database
 * @param string $task_id
 * @param string $clean_sites_string
 * @param string $clean_data_1
 * @param string $clean_data_2
 */
function be_mu_clean_add_task_data( $task_id, $clean_sites_string, $clean_data_1 = '', $clean_data_2 = '' ) {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
    $db_table = $main_blog_prefix . 'be_mu_cleanup';

    // Insert the cleanup data to the database
    $wpdb->insert(
    	$db_table,
    	array(
    		'task_id' => $task_id,
    		'cleanup_sites_data' => $clean_sites_string,
    		'cleanup_data_1' => $clean_data_1,
    		'cleanup_data_2' => $clean_data_2,
    		'unix_time_added' => time(),
    	),
    	array(
    		'%s',
    		'%s',
    		'%s',
    		'%s',
    		'%d',
    	)
    );
}

/**
 * Adds data to the database about a site that is scheduled to be deleted
 * @param string $task_id
 * @param int $site_id
 * @param string $deletion_type
 * @param int $unix_time_to_be_deleted
 */
function be_mu_clean_schedule_site_deletion( $task_id, $site_id, $deletion_type, $unix_time_to_be_deleted ) {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // The database table for the scheduled site deletions
    $db_table = $main_blog_prefix . 'be_mu_scheduled_site_deletions';

    $results_multi_array = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $db_table . " WHERE task_id = %s AND site_id = %d",
        $task_id, $site_id ), ARRAY_A );

    // Only if the site is not already added
    if ( empty( $results_multi_array ) ) {

        // Insert the site deletion data to the database
        $wpdb->insert(
        	$db_table,
        	array(
        		'task_id' => $task_id,
        		'site_id' => $site_id,
        		'deletion_type' => $deletion_type,
        		'status' => 'scheduled',
        		'unix_time_added' => time(),
        		'unix_time_to_be_deleted' => $unix_time_to_be_deleted,
        	),
        	array(
        		'%s',
        		'%d',
        		'%s',
        		'%s',
        		'%d',
        		'%d',
        	)
        );
    }
}

/**
 * Adds data to the database about an email notification about a scheduled site deletion
 * @param string $task_id
 * @param int $site_id
 * @param string $site_delete_type
 * @param int $delete_after_days
 * @param string $from_name
 * @param string $to_email
 * @param string $subject
 * @param string $message
 */
function be_mu_clean_schedule_email_for_site_deletion( $task_id, $site_id, $site_delete_type, $delete_after_days, $from_name, $from_email,
    $to_email, $subject, $message, $status ) {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the main network site
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // The database table for the email notifications about site deletions
    $db_table = $main_blog_prefix . 'be_mu_site_deletion_emails';

    // Insert the email notification data to the database
    $wpdb->insert(
    	$db_table,
    	array(
    		'task_id' => $task_id,
    		'site_id' => $site_id,
    		'site_delete_type' => $site_delete_type,
    		'delete_after_days' => $delete_after_days,
    		'from_name' => $from_name,
    		'from_email' => $from_email,
    		'to_email' => $to_email,
    		'subject' => $subject,
    		'message' => $message,
    		'status' => $status,
    		'unix_time_added' => time(),
    	),
    	array(
    		'%s',
    		'%d',
    		'%s',
    		'%d',
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

/**
 * Replaces two of the shortcodes with their values and returns the new message string for the notification about site deletion
 * @param string $message
 * @param string $deletion_cancellation_setting
 * @return string
 */
function be_mu_clean_apply_message_shortcodes( $message, $deletion_cancellation_setting ) {

    // We explode the string into an array of parts separated by a space
    $parts = explode( ' ', $deletion_cancellation_setting );

    // This is the number of days after which the site is scheduled for deletion
    $delete_after_days = intval( $parts[4] );

    // Replace the shortcode with the number of days before deletion
    $message = str_replace( '[deletion_after_days]', $delete_after_days, $message );

    // Replace the shortcode with the main network site url
    $message = str_replace( '[network_site_url]', esc_url( network_site_url() ), $message );

    // Return the new message var
    return $message;
}

/**
 * Replaces the user sites shortcode with the list of sites and returns the new message string for the notification about site deletion
 * @param string $message
 * @param array $site_ids
 * @return string
 */
function be_mu_clean_apply_message_sites_shortcode( $message, $site_ids ) {

    // We create the html for the list of sites
    $sites_string = '<ul>';
    foreach ( $site_ids as $site_id ) {
        $site_url = esc_url( get_site_url( $site_id ) );
        $sites_string .= '<li><a target="_blank" href="' . $site_url . '">' . $site_url . '</a></li>';
    }
    $sites_string .= '</ul>';

    // Replace the shortcode with the list of site urls
    $message = str_replace( '[user_sites]', $sites_string, $message );

    // Return the new message var
    return $message;
}

/**
 * Returns unescaped translated string for the deletion type based on the given variable value.
 * @param string $deletion_type
 * @return mixed
 */
function be_mu_translate_deletion_type( $deletion_type ) {
    if( 'Permanent deletion' == $deletion_type ) {
        return __( 'Permanent deletion', 'beyond-multisite' );
    } elseif ( 'Mark as deleted (change last updated time)' == $deletion_type ) {
        return __( 'Mark as deleted (change last updated time)', 'beyond-multisite' );
    } elseif ( 'Mark as deleted (keep last updated time)' == $deletion_type ) {
        return __( 'Mark as deleted (keep last updated time)', 'beyond-multisite' );
    } elseif ( 'Mark as archived (change last updated time)' == $deletion_type ) {
        return __( 'Mark as archived (change last updated time)', 'beyond-multisite' );
    } elseif ( 'Mark as archived (keep last updated time)' == $deletion_type ) {
        return __( 'Mark as archived (keep last updated time)', 'beyond-multisite' );
    } else {
        return false;
    }
}

/**
 * Returns an array of user IDs of all super admins
 * @return array
 */
function be_mu_get_super_admin_ids() {

    $super_admin_ids = Array();

    // Get all usernames of all Super Admins
    $super_admin_logins = get_super_admins();

    // We go through all the Super Admins
    foreach ( $super_admin_logins as $super_admin_login ) {

        // Get the WP_User object of the current Super Admin
        $super_admin_object = get_user_by( 'login', $super_admin_login );

        $super_admin_ids[] = intval( $super_admin_object->ID );
    }

    return $super_admin_ids;
}
