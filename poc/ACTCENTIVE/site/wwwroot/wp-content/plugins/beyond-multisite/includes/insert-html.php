<?php

/**
 * The functions and hooks in this file are for the Insert HTML module. Using the Wordpress hooks we can add code on different places
 * as long as the theme that the site is using supports the hook.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// All the hooks for the Insert HTML module will run only if the module is turned on
if ( be_mu_get_setting( 'be-mu-insert-status-module' ) == 'on' ) {

    // We get the settings related to which types of pages to show the code on
    $GLOBALS['be_mu_insert_page_settings'] = be_mu_get_settings( array( 'be-mu-insert-head-front-end-theme', 'be-mu-insert-head-front-end-login',
        'be-mu-insert-head-back-end', 'be-mu-insert-footer-front-end-theme', 'be-mu-insert-footer-front-end-login', 'be-mu-insert-footer-back-end' ) );

    // Inserts the code in the head section of the site before </head> on front-end pages using the theme
    if ( 'on' === $GLOBALS['be_mu_insert_page_settings']['be-mu-insert-head-front-end-theme'] ) {
        add_action( 'wp_head', 'be_mu_insert_to_head', 99999999999 );
    }

    // Inserts the code in the head section of the site before </head> on front-end pages related to login
    if ( 'on' === $GLOBALS['be_mu_insert_page_settings']['be-mu-insert-head-front-end-login'] ) {
        add_action( 'login_head', 'be_mu_insert_to_head', 99999999999 );
    }

    // Inserts the code in the head section of the site before </head> on back-end admin pages
    if ( 'on' === $GLOBALS['be_mu_insert_page_settings']['be-mu-insert-head-back-end'] ) {
        add_action( 'admin_head', 'be_mu_insert_to_head', 99999999999 );
        add_action( 'customize_controls_print_scripts', 'be_mu_insert_to_head', 99999999999 );
    }

    // Inserts the code in the footer section of the site before </body> on front-end pages using the theme
    if ( 'on' === $GLOBALS['be_mu_insert_page_settings']['be-mu-insert-footer-front-end-theme'] ) {
        add_action( 'wp_footer', 'be_mu_insert_to_footer', 99999999999 );
    }

    // Inserts the code in the footer section of the site before </body> on front-end pages related to login
    if ( 'on' === $GLOBALS['be_mu_insert_page_settings']['be-mu-insert-footer-front-end-login'] ) {
        add_action( 'login_footer', 'be_mu_insert_to_footer', 99999999999 );
    }

    // Inserts the code in the footer section of the site before </body> on back-end admin pages
    if ( 'on' === $GLOBALS['be_mu_insert_page_settings']['be-mu-insert-footer-back-end'] ) {
        add_action( 'admin_print_footer_scripts', 'be_mu_insert_to_footer', 99999999999 );
    }
}

/**
 * Inserts html code in the head section of the site before </head>
 * @return mixed
 */
function be_mu_insert_to_head() {

    // We get the values of some settings
    $settings = be_mu_get_settings( array( 'be-mu-insert-head', 'be-mu-insert-head-affect-sites-id-option', 'be-mu-insert-head-site-ids' ) );

    /**
     * If the code to insert is empty, or if the settings are set to insert only on the selected site ids and the list of
     * ids is empty, or if this is a post request updating the module settings, then we return and do nothing.
     * The last condition is needed because Chrome blocks the request if we add script tags to the same page we are in.
     */
    if ( '' == $settings['be-mu-insert-head'] || ( 'Only these site IDs:' == $settings['be-mu-insert-head-affect-sites-id-option']
            && '' == $settings['be-mu-insert-head-site-ids'] ) || isset( $_POST['be-mu-update-insert-settings'] ) ) {
        return true;
    }

    // There are site ids set and we have to show the code only on those sites
    if ( 'Only these site IDs:' == $settings['be-mu-insert-head-affect-sites-id-option'] ) {

        // Make an array of all the site ids
        $site_ids = explode( ',', $settings['be-mu-insert-head-site-ids'] );

        // Get the current site id
        $current_site_id = get_current_blog_id();

        // If the current site id is in the array of site ids set in the settings we display the code
        if ( in_array( $current_site_id, $site_ids ) ) {
            echo $settings['be-mu-insert-head'];
        }

    // We have to show the code on all sites except the selected site ids if there are any
    } elseif ( 'All except these site IDs:' == $settings['be-mu-insert-head-affect-sites-id-option'] ) {

        // We check if there are site ids set in the settings
        if ( '' == $settings['be-mu-insert-head-site-ids'] ) {

            // There are no sites to exclude so we display to all sites
            echo $settings['be-mu-insert-head'];

        // There are sites to exclude
        } else {

            // Make an array of all the site ids
            $site_ids = explode( ',', $settings['be-mu-insert-head-site-ids'] );

            // Get the current site id
            $current_site_id = get_current_blog_id();

            // If the current site id is NOT in the array of site ids set in the settings we display the code
            if ( ! in_array( $current_site_id, $site_ids ) ) {
                echo $settings['be-mu-insert-head'];
            }
        }

    // Any site ID is chosen, so we show the code on all sites
    } else {
        echo $settings['be-mu-insert-head'];
    }
}

/**
 * Inserts html code in the footer section of the site before </body>
 * @return mixed
 */
function be_mu_insert_to_footer() {

    // We get the values of some settings
    $settings = be_mu_get_settings( array( 'be-mu-insert-footer', 'be-mu-insert-footer-affect-sites-id-option', 'be-mu-insert-footer-site-ids' ) );

    /**
     * If the code to insert is empty, or if the settings are set to insert only on the selected site ids and the list of
     * ids is empty, or if this is a post request updating the module settings, then we return and do nothing.
     * The last condition is needed because Chrome blocks the request if we add script tags to the same page we are in.
     */
    if ( '' == $settings['be-mu-insert-footer'] || ( 'Only these site IDs:' == $settings['be-mu-insert-footer-affect-sites-id-option']
            && '' == $settings['be-mu-insert-footer-site-ids'] ) || isset( $_POST['be-mu-update-insert-settings'] ) ) {
        return true;
    }

    // There are site ids set and we have to show the code only on those sites
    if ( 'Only these site IDs:' == $settings['be-mu-insert-footer-affect-sites-id-option'] ) {

        // Make an array of all the site ids
        $site_ids = explode( ',', $settings['be-mu-insert-footer-site-ids'] );

        // Get the current site id
        $current_site_id = get_current_blog_id();

        // If the current site id is in the array of site ids set in the settings we display the code
        if ( in_array( $current_site_id, $site_ids ) ) {
            echo $settings['be-mu-insert-footer'];
        }

    // We have to show the code on all sites except the selected site ids if there are any
    } elseif ( 'All except these site IDs:' == $settings['be-mu-insert-footer-affect-sites-id-option'] ) {

        // We check if there are site ids set in the settings
        if ( '' == $settings['be-mu-insert-footer-site-ids'] ) {

            // There are no sites to exclude so we display to all sites
            echo $settings['be-mu-insert-footer'];

        // There are sites to exclude
        } else {

            // Make an array of all the site ids
            $site_ids = explode( ',', $settings['be-mu-insert-footer-site-ids'] );

            // Get the current site id
            $current_site_id = get_current_blog_id();

            // If the current site id is NOT in the array of site ids set in the settings we display the code
            if ( ! in_array( $current_site_id, $site_ids ) ) {
                echo $settings['be-mu-insert-footer'];
            }
        }

    // Any site ID is chosen, so we show the code on all sites
    } else {
        echo $settings['be-mu-insert-footer'];
    }
}
