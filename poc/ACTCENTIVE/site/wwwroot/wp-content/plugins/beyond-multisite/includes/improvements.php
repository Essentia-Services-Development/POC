<?php

// In this file we have all the hooks and functions related to the improvements module

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// We get all the settings we will need in this array
$GLOBALS['be_mu_improve_settings'] = be_mu_get_settings( array( 'be-mu-improve-status-module', 'be-mu-improve-user-id-column', 'be-mu-improve-site-id-column',
    'be-mu-improve-hide-plugin-meta', 'be-mu-ban-status-module', 'be-mu-improve-user-sites-dash-action', 'be-mu-improve-network-plugin-admin-menus',
    'be-mu-improve-network-theme-admin-menus', 'be-mu-improve-password-change-email', 'be-mu-improve-wp-signup-css-class',
    'be-mu-improve-drop-leftover-tables', 'be-mu-improve-show-noindex-status', 'be-mu-improve-user-sites-role-icon', 'be-mu-improve-delete-leftover-folder' ) );

// All the hooks for the improvements module will run only if the module is turned on
if ( 'on' == $GLOBALS['be_mu_improve_settings']['be-mu-improve-status-module'] ) {
    if ( 'on' == $GLOBALS['be_mu_improve_settings']['be-mu-improve-user-id-column'] ) {

        // Adds an extra column with the user id in the table on the users page in the network admin panel (if the settings allow it)
        add_filter( 'wpmu_users_columns', 'be_mu_improve_users_add_extra_columns' );

        // Adds the data for the user id column we added in the table on the Users page in the network admin panel (if the settings allow it)
        add_action( 'manage_users_custom_column', 'be_mu_improve_users_extra_columns_data', 999999, 3 );
    }

    if ( 'on' == $GLOBALS['be_mu_improve_settings']['be-mu-improve-site-id-column'] ) {

        // Adds an extra column with the site id in the table on the sites page in the network admin panel (if the settings allow it)
        add_filter( 'wpmu_blogs_columns', 'be_mu_improve_sites_add_extra_columns' );

        // Adds the data for the site id column we added in the table on the sites page in the network admin panel (if the settings allow it)
        add_action( 'manage_sites_custom_column', 'be_mu_improve_sites_extra_columns_data', 10, 2 );
    }

    // If the ban module is not turned on we call one function from the ban module, so the css file is loaded for the Users page in the network admin panel
    if ( 'on' != $GLOBALS['be_mu_improve_settings']['be-mu-ban-status-module'] ) {
        add_action( 'admin_enqueue_scripts', 'be_mu_register_users_style' );
    }

    // Adds a new action link to the site dashboard in the sites column on the users page for each user site (if settings allow it)
    if ( 'on' == $GLOBALS['be_mu_improve_settings']['be-mu-improve-user-sites-dash-action'] ) {
        add_filter( 'ms_user_list_site_actions', 'be_mu_improve_users_sites_action_link', 10, 2 );
    }

    // Loads our css file for some admin pages in the network admin panel
    add_action( 'admin_enqueue_scripts', 'be_mu_improve_register_style' );

    // Hides the plugin meta information from anyone that cannot manage the network (if settings allow it)
    if ( 'on' == $GLOBALS['be_mu_improve_settings']['be-mu-improve-hide-plugin-meta'] ) {
        add_filter( 'plugin_row_meta', 'be_mu_improve_hide_plugin_meta', 10, 2 );
    }

    if ( 'on' == $GLOBALS['be_mu_improve_settings']['be-mu-improve-network-plugin-admin-menus']
        || 'on' == $GLOBALS['be_mu_improve_settings']['be-mu-improve-network-theme-admin-menus'] ) {

        // Adds a few submenus to the plugins and appearance admin menus that point to network admin pages and are visible for super admins (if settings allow it)
        add_action( 'admin_menu', 'be_mu_improve_add_to_admin_menu' );

        // Since we cannot just add any link as an admin menu, we added pages earlier, so here we redirect them to the correct places
        add_action( 'plugins_loaded', 'be_mu_improve_redirect_page' );
    }

    // Disables the email notification sent to the network admin when a user changes his password (if settings allow it)
    if ( 'on' == $GLOBALS['be_mu_improve_settings']['be-mu-improve-password-change-email'] ) {
        add_action( 'wp_loaded', 'be_mu_improve_disable_pass_change_notification' );
    }

    // Adds a CSS class to the body tag on the wp-signup.php page (if settings allow it)
    if ( 'on' == $GLOBALS['be_mu_improve_settings']['be-mu-improve-wp-signup-css-class'] ) {
        add_filter( 'body_class', 'be_mu_improve_add_wp_signup_body_css_class' );
    }

    // Deletes leftover database tables (created by plugins) when a site is permanently deleted (if settings allow it)
    if ( 'on' == $GLOBALS['be_mu_improve_settings']['be-mu-improve-drop-leftover-tables'] ) {
        add_filter( 'wpmu_drop_tables', 'be_mu_improve_drop_leftover_tables', 10, 2 );
    }

    // Show an icon in the admin bar and in "My Sites" for sites with discouraged search engine indexing (if settings allow it)
    if ( 'on' == $GLOBALS['be_mu_improve_settings']['be-mu-improve-show-noindex-status'] ) {

        // Adds the CSS style that adds an icon in the admin bar for sites that discourage indexing
        add_action( 'admin_enqueue_scripts', 'be_mu_improve_noindex_style' );

        // Adds the CSS style that adds an icon in the admin bar for sites that discourage indexing
        add_action( 'wp_enqueue_scripts', 'be_mu_improve_noindex_style' );

        // Change the icon in the My Sites admin bar menu for sites that have discouraged indexing
        add_action( 'admin_head', 'be_mu_improve_my_sites_menu_noindex_style' );

        // Change the icon in the My Sites admin bar menu for sites that have discouraged indexing
        add_action( 'wp_head', 'be_mu_improve_my_sites_menu_noindex_style' );

        // Adds the admin bar element for the icon for sites that discourage indexing
        add_action( 'admin_bar_menu', 'be_mu_improve_noindex_admin_bar', 99999999 );

        // Adds an icon next to the site actions on the My Sites page, for sites that discourage indexing
        add_filter( 'myblogs_blog_actions', 'be_mu_improve_noindex_my_sites_page', 99999999, 2 );
    }

    // We add the user role icons next to each site in the Network Users page (if settings allow it)
    if ( 'on' == $GLOBALS['be_mu_improve_settings']['be-mu-improve-user-sites-role-icon'] ) {

        $GLOBALS['be_mu_tricky_get_user_id'] = 0;

        // Loads a css file for the Users page in the network admin panel
        add_action( 'admin_enqueue_scripts', 'be_mu_improve_register_users_style' );

        // We get the user ID into a global variable to use it in another hook
        add_filter( 'ms_user_row_actions', 'be_mu_improve_get_user_id', 10, 2 );

        // Adds the role icons next in the action area for each site in the Users page in the network admin panel
        add_filter( 'ms_user_list_site_actions', 'be_mu_improve_add_user_role_icons', 10, 2 );
    }

    // We delete the leftover empty uploads folder when a site is deleted (if settings allow it)
    if ( 'on' == $GLOBALS['be_mu_improve_settings']['be-mu-improve-delete-leftover-folder'] ) {

        // We get and save the path to the uploads folder of the deleted site when its tables are deleted, because later we will not be able to get it.
        add_filter( 'wpmu_drop_tables', 'be_mu_improve_get_deleting_uploads_path', 10, 2 );

        // Depending on the WordPress version we use a different hook to delete the leftover empty uploads folder after a site is deleted
        if ( has_action( 'wp_delete_site' ) ) {
            add_action( 'wp_delete_site', 'be_mu_improve_delete_leftover_folder' );
        } else {
            add_action( 'deleted_blog', 'be_mu_improve_delete_leftover_folder_old', 10, 2 );
        }        
    }
}

/**
 * Loads our css file for some admin pages in the network admin panel
 * @param string $hook
 */
function be_mu_improve_register_style( $hook ) {
    if ( 'sites.php' == $hook ) {
        be_mu_register_beyond_multisite_style();
    }
}

/**
 * Adds an extra column with the user id in the table on the users page in the network admin panel (if the settings allow it)
 * @param array $columns
 * @return array
 */
function be_mu_improve_users_add_extra_columns( $columns ) {
    return be_mu_add_element_to_array( $columns, 'be-mu-user-id', esc_html__( 'ID', 'beyond-multisite' ), 'name' );
}

/**
 * Adds the data for the user id column we added in the table on the Users page in the network admin panel
 * @param mixed $value
 * @param string $column_name
 * @param int $user_id
 * @return mixed
 */
function be_mu_improve_users_extra_columns_data( $value, $column_name, $user_id ) {

    // If this is the user id column we return the id
	if ( 'be-mu-user-id' == $column_name ) {
        return intval( $user_id );
    }

    // If this is not our user id column we just return the normal data
    return $value;
}

/**
 * Adds a new action link to the site dashboard in the sites column on the users page (if settings allow it)
 * @param array $actions
 * @param int $userblog_id
 * @return array
 */
function be_mu_improve_users_sites_action_link( $actions, $userblog_id ) {
    return be_mu_add_element_to_array( $actions, 'be-mu-user-site-dashboard',
        '<a href="' . esc_url( get_admin_url( intval( $userblog_id ) ) ) . '">' . esc_html__( 'Dashboard', 'beyond-multisite' ) . '</a>', 'view' );
}

/**
 * Adds an extra column with the site id in the table on the sites page in the network admin panel (if the settings allow it)
 * @param array $sites_columns
 * @return array
 */
function be_mu_improve_sites_add_extra_columns( $sites_columns ) {
    return be_mu_add_element_to_array( $sites_columns, 'be-mu-site-id', esc_html__( 'ID', 'beyond-multisite' ), 'lastupdated' );
}

/**
 * Adds the data for the site id column we added in the table on the Sites page in the network admin panel
 * @param string $column_name
 * @param int $blog_id
 */
function be_mu_improve_sites_extra_columns_data( $column_name, $blog_id ) {

    // if this is the site id column we output the id
	if ( 'be-mu-site-id' == $column_name ) {
        echo intval( $blog_id );
    }
}

/**
 * Hides the plugin meta information from anyone that cannot manage the network
 * @param array $plugin_meta
 * @param string $plugin_file
 * @return array
 */
function be_mu_improve_hide_plugin_meta( $plugin_meta, $plugin_file ) {
    if ( ! current_user_can( 'manage_network' ) ) {
        return array();
    } else {
        return $plugin_meta;
    }
}

// Adds a few subpages to the plugins and the appearance admin menu that we later redirect to network admin plugins pages and are visible for super admins
function be_mu_improve_add_to_admin_menu() {

    if ( 'on' == $GLOBALS['be_mu_improve_settings']['be-mu-improve-network-plugin-admin-menus'] ) {
        add_submenu_page(
            'plugins.php',
            'Network: Plugins',
            esc_html__( 'Network: Plugins', 'beyond-multisite' ),
            'manage_network',
            'beyond-multisite-go-to-network-plugins',
            'be_mu_empty_function'
        );
        add_submenu_page(
            'plugins.php',
            'Network: Add New',
            esc_html__( 'Network: Add New', 'beyond-multisite' ),
            'manage_network',
            'beyond-multisite-go-to-network-add-new-plugins',
            'be_mu_empty_function'
        );
        add_submenu_page(
            'plugins.php',
            'Network: Edit',
            esc_html__( 'Network: Edit', 'beyond-multisite' ),
            'manage_network',
            'beyond-multisite-go-to-network-edit-plugins',
            'be_mu_empty_function'
        );
    }

    if ( 'on' == $GLOBALS['be_mu_improve_settings']['be-mu-improve-network-theme-admin-menus'] ) {
        add_submenu_page(
            'themes.php',
            'Network: Themes',
            esc_html__( 'Network: Themes', 'beyond-multisite' ),
            'manage_network',
            'beyond-multisite-go-to-network-themes',
            'be_mu_empty_function'
        );
        add_submenu_page(
            'themes.php',
            'Network: Add New',
            esc_html__( 'Network: Add New', 'beyond-multisite' ),
            'manage_network',
            'beyond-multisite-go-to-network-add-new-themes',
            'be_mu_empty_function'
        );
        add_submenu_page(
            'themes.php',
            'Network: Edit',
            esc_html__( 'Network: Edit', 'beyond-multisite' ),
            'manage_network',
            'beyond-multisite-go-to-network-edit-themes',
            'be_mu_empty_function'
        );
    }
}

// Since we cannot just add any link as an admin menu, we added pages earlier, so here we redirect them to the correct places
function be_mu_improve_redirect_page() {
    if ( isset( $_GET['page'] ) && substr( $_GET['page'], 0, 17 ) === "beyond-multisite-" ) {
        global $pagenow;
        if ( ( 'themes.php' == $pagenow || 'plugins.php' == $pagenow ) && is_admin() && current_user_can( 'manage_network' ) ) {
            if ( 'beyond-multisite-go-to-network-themes' == $_GET['page'] ) {
                wp_redirect( network_admin_url( 'themes.php' ), 301 );
                exit;
            } elseif ( 'beyond-multisite-go-to-network-add-new-themes' == $_GET['page'] ) {
                wp_redirect( network_admin_url( 'theme-install.php' ), 301 );
                exit;
            } elseif ( 'beyond-multisite-go-to-network-edit-themes' == $_GET['page'] ) {
                wp_redirect( network_admin_url( 'theme-editor.php' ), 301 );
                exit;
            } elseif ( 'beyond-multisite-go-to-network-plugins' == $_GET['page'] ) {
                wp_redirect( network_admin_url( 'plugins.php' ), 301 );
                exit;
            } elseif ( 'beyond-multisite-go-to-network-add-new-plugins' == $_GET['page'] ) {
                wp_redirect( network_admin_url( 'plugin-install.php' ), 301 );
                exit;
            } elseif ( 'beyond-multisite-go-to-network-edit-plugins' == $_GET['page'] ) {
                wp_redirect( network_admin_url( 'plugin-editor.php' ), 301 );
                exit;
            }
        }
    }
}

// Disables the email notification sent to the network admin when a user changes his password
function be_mu_improve_disable_pass_change_notification() {
    remove_action( 'after_password_reset', 'wp_password_change_notification' );

    // Without this the email will still be sent if the password is changed via the WooCommerce form
    add_action( 'wp_password_change_notification_email', '__return_false' );
}

/**
 * Adds a CSS class to the body tag on the wp-signup.php page
 * @param array $classes
 * @return array
 */
function be_mu_improve_add_wp_signup_body_css_class( $classes ) {
    if ( 'wp-signup.php' === $GLOBALS['pagenow'] || '/wp-signup.php' === $_SERVER['PHP_SELF'] ) {
        array_push( $classes, 'be-mu-wp-signup-class' );
    }
    return $classes;
}

/**
 * Deletes leftover database tables (created by plugins) when a site is permanently deleted
 * @param array $tables
 * @param int $blog_id
 * @return array
 */
function be_mu_improve_drop_leftover_tables( $tables, $blog_id ) {

    // If for any reason the blog id is invalid, we return the default tables without adding anything, to avoid unwanted table deletion
    if ( ! is_numeric( $blog_id ) || empty( $blog_id ) || $blog_id < 1 || $blog_id == be_mu_get_main_site_id() ) {
        return $tables;
    }

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // The database table prefix for the deleted site
    $blog_prefix = $wpdb->get_blog_prefix( $blog_id );

    $base_prefix = $wpdb->base_prefix;

    /**
     * If for any reason the blog prefix is empty or the same as the main blog prefix,
     * we return the default tables without adding anything, to avoid unwanted table deletion.
     */
    if ( empty( $blog_prefix ) || $blog_prefix == $wpdb->get_blog_prefix( be_mu_get_main_site_id() ) || $base_prefix === $blog_prefix ) {
        return $tables;
    }

    // We get all database tables that start with the blog prefix
    $tables_multi_array = $wpdb->get_results( "SELECT TABLE_NAME FROM information_schema.tables WHERE "
        . "TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA IN ( " . be_mu_get_database_names_string_for_mysql_in() . " ) "
        . "AND LOCATE( '" . $blog_prefix . "', TABLE_NAME ) = 1", ARRAY_A );

    if ( ! empty( $tables_multi_array ) ) {
        foreach ( $tables_multi_array as $table ) {

            // We confirm one more time that the table name starts with the blog prefix and if it is not already in the array, we add it so it is also dropped.
            if ( substr( $table['TABLE_NAME'], 0, strlen( $blog_prefix ) ) === $blog_prefix && ! in_array( $table['TABLE_NAME'], $tables ) ) {
                $tables[] = $table['TABLE_NAME'];
            }
        }
    }

    // We return the new array with tables for deletion
    return $tables;
}

// Adds an icon next to the site actions on the My Sites page, for sites that discourage indexing
function be_mu_improve_noindex_my_sites_page( $actions, $user_blog ) {

    if ( function_exists( 'get_blog_status' ) ) {

        $blog_id = $user_blog->userblog_id;

        if ( get_blog_status( $blog_id, 'public' ) == 0 ) {
            $actions = '<span title="' . esc_attr__( 'Search engine indexing is discouraged', 'beyond-multisite' )
                . '" class="dashicons dashicons-hidden"></span> | ' . $actions;
        }

    }

    return $actions;
}

// Adds the admin bar element for the icon for sites that discourage indexing
function be_mu_improve_noindex_admin_bar( $wp_admin_bar ) {

    if ( function_exists( 'get_blog_status' ) ) {

        $current_blog_id = get_current_blog_id();

        if ( get_blog_status( $current_blog_id, 'public' ) == 0 ) {

            // For users that can manage_options the icon will also be a link to the options page to change the indexing setting
            if ( current_user_can( 'manage_options' ) ) {
                $href = esc_url( get_admin_url( $current_blog_id, '/options-reading.php') );
            } else {
                $href = '';
            }

            $args = array(
                'id'    => 'be-mu-noindex',
                'title' => '',
                'href'  => $href,
                'meta'  => array( 'class' => 'be-mu-noindex', 'title' => esc_attr__( 'Search engine indexing is discouraged', 'beyond-multisite' ) )
            );

            $wp_admin_bar->add_node( $args );
        }

    }

}

// Adds the CSS style that adds an icon in the admin bar for sites that discourage indexing
function be_mu_improve_noindex_style() {
    if ( is_user_logged_in() ) {
        wp_register_style( 'be-mu-noindex-style', be_mu_plugin_dir_url() . 'styles/noindex.css', false, BEYOND_MULTISITE_VERSION );
        wp_enqueue_style( 'be-mu-noindex-style' );
    }
}

// Change the icon in the My Sites admin bar menu for sites that have discouraged indexing
function be_mu_improve_my_sites_menu_noindex_style() {
    if ( is_user_logged_in() && function_exists( 'get_blog_status' ) ) {
        echo '<style type="text/css">';

        // We get all blogs in which the user has any role. Second argument is true to get even spammed, deleted, and archived.
        $blogs = get_blogs_of_user( get_current_user_id(), true );

        // We go through all the user blogs and if indexing is discouraged we change the icon in the My Sites menu with CSS
        foreach ( $blogs as $blog_id => $blog ) {
            if ( get_blog_status( $blog_id, 'public' ) == 0 ) {
                echo '
                #wpadminbar .quicklinks li #wp-admin-bar-my-sites-list #wp-admin-bar-blog-' . intval( $blog_id ) . ' .blavatar::before {
                    content: "\f530";
                    display: block !important;
                }
                ';
            }
        }

        echo '</style>';
    }
}


/**
 * Loads the styles for the Users page in the network admin panel
 * @param string $hook
 */
function be_mu_improve_register_users_style( $hook ) {
    if ( 'users.php' == $hook && is_network_admin() ) {
        wp_register_style( 'be-mu-role-icons-style', be_mu_plugin_dir_url() . 'styles/role-icons.css', false, BEYOND_MULTISITE_VERSION );
        wp_enqueue_style( 'be-mu-role-icons-style' );
    }
}

/**
 * We get the user ID into a global variable to use it in another hook
 * @param array $actions
 * @param object $user
 * @return array
 */
function be_mu_improve_get_user_id( $actions, $user ) {
    $GLOBALS['be_mu_tricky_get_user_id'] = $user->ID;
    return $actions;
}

/**
 * Adds the role icons next in the action area for each site in the Users page in the network admin panel
 * @param array $actions
 * @param int $userblog_id
 * @return array
 */
function be_mu_improve_add_user_role_icons( $actions, $userblog_id ) {

    // This is the HTML code we will add to the actions area of each site
    $to_add = "";

    // Here we will store all role data for the current site
    $roles = Array();

    // We switch to the current site to get the data for the available roles
    switch_to_blog( $userblog_id );

    global $wp_roles;
    $editable_roles = get_editable_roles();

    // For each of the roles we add its display name (translatable) to our array
    foreach ( $editable_roles as $role_name => $role_information ) {
        $roles[ $role_name ] = translate_user_role( $wp_roles->roles[ $role_name ]['name'] );
    }

    // We switch back to the main site
    restore_current_blog();

    // Get the user object for the user for the current site
    $user = new WP_User( $GLOBALS['be_mu_tricky_get_user_id'], '', $userblog_id );

    // We go through all the roles that the user has for the current site
    foreach ( $user->roles as $role ) {

        if ( array_key_exists( $role, $roles ) ) {
            $role_display_name = $roles[ $role ];
            $letter = strtoupper( mb_substr( $role_display_name, 0, 1 ) );
        } else {
            $role_display_name = sprintf( __( 'Missing role (%s)', 'beyond-multisite' ), $role );
            $letter = strtoupper( mb_substr( $role, 0, 1 ) );
        }

        // If this is a custom role we will add another class to the icon to change the color
        $custom_role = "";
        if ( ! in_array( $role, Array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' ) ) ) {
            $custom_role = " be-mu-icon-custom";
        }

        // This is the HTML code we will add to the actions area of the site. We make a circle with the first letter of the role name inside.
        $to_add .= "<span title='" . esc_attr( $role_display_name ) . "' class='be-mu-role-icon be-mu-icon-" . esc_attr( $role . $custom_role ) . "'>"
            . esc_html( $letter ) . "</span>";
    }

    // We return an array with our icons as a new action before the first action
    return be_mu_add_element_to_array( $actions, 'be-mu-role-icon-action', $to_add, 'edit' );
}

/**
 * We get and save the path to the uploads folder of the deleted site when its tables are deleted, because later we will not be able to get it.
 * @param array $tables
 * @param int $blog_id
 * @return array
 */
function be_mu_improve_get_deleting_uploads_path( $tables, $blog_id ) {
    $uploads_path = be_mu_get_site_upload_folder( $blog_id );
    update_site_option( 'be-mu-deleting-site-' . intval( $blog_id ) . '-uploads-path', $uploads_path );
    return $tables;
}

/**
 * We delete the leftover empty uploads folder after a site is deleted in at least WordPress 5.1
 * @param object $old_site
 */
function be_mu_improve_delete_leftover_folder( $old_site ) {
    be_mu_execute_leftover_folder_deletion( $old_site->id );
}

/**
 * We delete the leftover empty uploads folder after a site is deleted in WordPress below 5.1
 * @param int $site_id
 * @param bool $drop
 */
function be_mu_improve_delete_leftover_folder_old( $site_id, $drop ) {
    be_mu_execute_leftover_folder_deletion( $site_id );
}

/**
 * We execute the deletion of the leftover empty uploads folder after a site is deleted
 * @param int $site_id
 */
function be_mu_execute_leftover_folder_deletion( $site_id ) {
    $uploads_path = get_site_option( 'be-mu-deleting-site-' . intval( $site_id ) . '-uploads-path' );

    // We make sure that the path to the folder exists and it is a folderm and it is the same name as the deleted site, and that the site is deleted
    if ( $uploads_path !== false && intval( basename( $uploads_path ) ) === $site_id && get_site( $site_id ) === null
        && file_exists( $uploads_path ) && is_dir( $uploads_path ) ) {

        // Deletes the folder only if it is empty and it does not generate an error if it fails
        @rmdir( $uploads_path );
    }
    delete_site_option( 'be-mu-deleting-site-' . intval( $site_id ) . '-uploads-path' );
}
