<?php

/**
 * In this file we have all the hooks and functions related to the ban users module.
 * We have functions that add elements in the admin panel to allow you to ban/unban users, also functions that track users and deny them access to
 * login, commenting and signup if they are banned.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// All the hooks for the ban users module will run only if the module is turned on
if ( be_mu_get_setting( 'be-mu-ban-status-module' ) == 'on' ) {

    // Adds a submenu page called Banned under the Users menu in the network admin panel and also loads a css style on that page
    add_action( 'network_admin_menu', 'be_mu_add_banned_menu' );

    // Adds the links to the actions to ban and unban a user on the Users page on every row in the list of user actions
    add_filter( 'ms_user_row_actions', 'be_mu_ban_users_action_link', 10, 2 );

    // Adds the current ip of the logged-in user in the database on every page view
    add_action( 'init', 'be_mu_track_ip' );

    // Checks on every page view if the IP of anyone that is logged-in is banned. If it is banned, it forces a logout and displays a message.
    add_action( 'init', 'be_mu_check_for_banned_ip' );

    // Loads a css file for the Users page in the network admin panel
    add_action( 'admin_enqueue_scripts', 'be_mu_register_users_style' );

    if ( be_mu_get_setting( 'be-mu-ban-show-flags' ) == 'on' ) {

        // Registers and localizes the javascript file for the country flags
        add_action( 'admin_enqueue_scripts', 'be_mu_ban_register_flags_script' );

        // Using ajax we get and update the country code of an IP address for a user
        add_action( 'wp_ajax_be_mu_ban_check_ip_country_action', 'be_mu_ban_check_ip_country_action_callback' );
    }

    // Adds the Last IP column in the users table on the users page in the network admin panel (if the settings allow it)
    if ( be_mu_get_setting( 'be-mu-ban-ip-column' ) == 'on' ) {
        add_filter( 'wpmu_users_columns', 'be_mu_ban_add_last_ip_column' );
    }

    // Adds the Ban Status column in the users table on the users page in the network admin panel (if the settings allow it)
    if ( be_mu_get_setting( 'be-mu-ban-status-column' ) == 'on' ) {
        add_filter( 'wpmu_users_columns', 'be_mu_ban_add_ban_status_column' );
    }

    // Adds the data for the extra columns we added in the table on the Users page in the network admin panel
    add_action( 'manage_users_custom_column', 'be_mu_ban_extra_columns_data', 999999, 3 );

    /**
     * Display an error in the validation of the user signup form (if error is present)
     * We create such an error in the be_mu_check_banned_user_signup function if the ip is banned
     */
    add_action( 'signup_extra_fields', 'be_mu_signup_user_form_ban', 10000000000 );

    // Checks if the ip is banned when a user signup attempt is made and adds an error if yes
    add_filter( 'wpmu_validate_user_signup', 'be_mu_check_banned_user_signup' );

    /**
     * Display an error in the validation of the blog signup form (if error is present)
     * We create such an error in the be_mu_check_banned_blog_signup function if the ip is banned
     */
    add_action( 'signup_blogform', 'be_mu_signup_blog_form_ban', 10000000000 );

    // Checks if the ip is banned when a blog signup attempt is made and adds an error if yes
    add_filter( 'wpmu_validate_blog_signup', 'be_mu_check_banned_blog_signup' );

    // If the IP, from which the comment is sent, is banned - we show an error and stop everything
    add_action( 'preprocess_comment', 'be_mu_check_banned_comment', 11, 1 );

    // On login checks if the user or the ip are banned and denies access if yes
    add_filter( 'authenticate', 'be_mu_check_banned_login', 9, 3 );  //9 causes it to be executed after the captcha (which is weird)

    // Removes all expired bans once an hour
    add_action( 'be_mu_unban_hourly_event', 'be_mu_unban_expired' );
}

// Adds a submenu page called Banned under the Users menu in the network admin panel and also loads a css style on that page
function be_mu_add_banned_menu() {
    $banned_page = add_submenu_page(
        'users.php',
        esc_html__( 'Banned Users', 'beyond-multisite' ),
        esc_html__( 'Banned Users', 'beyond-multisite' ),
        'manage_network',
        'be_mu_banned_users',
        'be_mu_banned_users_subpage'
    );

    add_action( 'load-' . $banned_page, 'be_mu_add_ban_script' );
}

// Adds the action needed to register the script for the banned users page
function be_mu_add_ban_script() {
    add_action( 'admin_enqueue_scripts', 'be_mu_ban_register_script' );
}

// Registers and localizes the javascript file for the banned users page
function be_mu_ban_register_script() {

    if ( ( ! isset( $_GET['action'] ) || ! isset( $_GET['user'] ) ) && current_user_can( 'manage_network' ) ) {
        if ( isset( $_GET['search'] ) && '' != $_GET['search'] ) {
            $search_string = '&search=' . esc_js( wp_filter_nohtml_kses( $_GET['search'] ) );
        } else {
            $search_string = '';
        }

        // Register the script
        wp_register_script( 'be-mu-ban-script', be_mu_plugin_dir_url() . 'scripts/ban-users.js', array(), BEYOND_MULTISITE_VERSION, false );

        // This is the data we will send from the php to the javascript file
        $localize = array(
            'pageURL' => esc_js( esc_url( network_admin_url( 'users.php?page=be_mu_banned_users' ) ) ),
            'searchString' => $search_string,
        );

        // We localize the script - we send php data to the javascript file
        wp_localize_script( 'be-mu-ban-script', 'localizedBanUsers', $localize );

        // Enqueued script with localized data
        wp_enqueue_script( 'be-mu-ban-script', '', array(), false, true );
    }
}

// Registers and localizes the javascript file for the country flags
function be_mu_ban_register_flags_script( $hook ) {

    if ( ( 'users.php' == $hook || ( isset( $_GET['page'] ) && 'be_mu_banned_users' == $_GET['page'] ) ) && is_network_admin() ) {

        // Register the script
        wp_register_script( 'be-mu-flags-script', be_mu_plugin_dir_url() . 'scripts/flags.js', array(), BEYOND_MULTISITE_VERSION, false );

        // This is the data we will send from the php to the javascript file
        $localize = array(
            'ajaxNonce' => wp_create_nonce( 'be_mu_flags_nonce' ),
        );

        // We localize the script - we send php data to the javascript file
        wp_localize_script( 'be-mu-flags-script', 'localizedFlags', $localize );

        // Enqueued script with localized data
        wp_enqueue_script( 'be-mu-flags-script', '', array(), false, true );
    }
}

/**
 * Loads the styles for the Users page and Banned Users page in the network admin panel
 * @param string $hook
 */
function be_mu_register_users_style( $hook ) {

    if ( ( 'users.php' == $hook || ( isset( $_GET['page'] ) && 'be_mu_banned_users' == $_GET['page'] ) ) && is_network_admin() ) {

        be_mu_register_beyond_multisite_style();

        if ( be_mu_get_setting( 'be-mu-ban-show-flags' ) == 'on' ) {
            wp_register_style( 'be-mu-flags-style', be_mu_plugin_dir_url() . 'styles/flags.css', false, BEYOND_MULTISITE_VERSION );
            wp_enqueue_style( 'be-mu-flags-style' );
        }
    }
}

/**
 * Outputs some links to sites with info on the ip address
 * @param string $ip
 */
function be_mu_echo_ip_links( $ip ) {

    if ( be_mu_is_valid_ip( $ip ) ) {

        printf( esc_html__( 'Information about the IP %s:', 'beyond-multisite' ), $ip );
        ?>
        <br />
        <ul class='be-mu-ul'>
            <li>
                <a href="https://mxtoolbox.com/SuperTool.aspx?action=blacklist%3a<?php echo esc_attr( $ip ); ?>&run=toolpage" target="_blank">
                    <?php esc_html_e( 'MxToolBox Blacklists', 'beyond-multisite' ); ?>
                </a>
            </li>
            <li>
                <a href="https://cleantalk.org/blacklists/<?php echo esc_attr( $ip ); ?>" target="_blank">
                    <?php esc_html_e( 'CleanTalk', 'beyond-multisite' ); ?>
                </a>
            </li>
            <li>
                <a href="https://www.projecthoneypot.org/ip_<?php echo esc_attr( $ip ); ?>" target="_blank">
                    <?php esc_html_e( 'Project Honey Pot', 'beyond-multisite' ); ?>
                </a>
            </li>
            <li>
                <a href="https://www.stopforumspam.com/ipcheck/<?php echo esc_attr( $ip ); ?>" target="_blank">
                    <?php esc_html_e( 'Stop Forum Spam', 'beyond-multisite' ); ?>
                </a>
            </li>
            <li>
                <a href="https://www.spamhaus.org/query/ip/<?php echo esc_attr( $ip ); ?>" target="_blank">
                    <?php esc_html_e( 'The Spamhaus Project', 'beyond-multisite' ); ?>
                </a>
            </li>
            <li>
                <a href="https://mxtoolbox.com/SuperTool.aspx?action=arin%3a<?php echo esc_attr( $ip ); ?>&run=toolpage" target="_blank">
                    <?php esc_html_e( 'MxToolBox ARIN', 'beyond-multisite' ); ?>
                </a>
            </li>
            <li>
                <a href="https://www.google.com/search?q=<?php echo esc_attr( $ip ); ?>" target="_blank">
                    <?php esc_html_e( 'Google', 'beyond-multisite' ); ?>
                </a>
            </li>
        </ul>
        <?php

    } else {
        esc_html_e( 'Error: Invalid IP address', 'beyond-multisite' );
    }
}

// Creates the database tables to store the banned users and the ip adresses for all users
function be_mu_create_ban_db_tables() {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // This is the query that will create the database table if it does not exist already
    $sql = 'CREATE TABLE IF NOT EXISTS ' . $main_blog_prefix . 'be_mu_banned_users ( '
        . 'row_id int( 11 ) NOT NULL AUTO_INCREMENT, '
        . 'user_id int( 11 ) DEFAULT NULL, '
        . 'username varchar( 100 ) DEFAULT NULL, '
        . 'ip varchar( 50 ) DEFAULT NULL, '
        . 'period varchar( 20 ) DEFAULT NULL, '
        . 'unix_time_banned int( 11 ) NOT NULL, '
        . 'PRIMARY KEY ( row_id ) '
        . ') ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;';

    // Execute the query
    dbDelta( $sql );

    // This is the query that will create the database table if it does not exist already
    $sql = 'CREATE TABLE IF NOT EXISTS ' . $main_blog_prefix . 'be_mu_user_ips ( '
        . 'row_id int( 11 ) NOT NULL AUTO_INCREMENT, '
        . 'user_id int( 11 ) DEFAULT NULL, '
        . 'ip varchar( 50 ) DEFAULT NULL, '
        . 'PRIMARY KEY ( row_id ) '
        . ') ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;';

    // Execute the query
    dbDelta( $sql );
}

/**
 * Adds or updates the value of the ip of the user in the database
 * @param int $user_id
 */
function be_mu_add_or_update_user_ip( $user_id ) {

    if ( function_exists( 'be_mu_get_visitor_ip' ) ) {

        // We get the current user ip
        $ip = be_mu_get_visitor_ip();

        // We only proceed if the user has an ip, if not, there is nothing we can do
        if ( false !== $ip ) {

            // We need these to connect to the database
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            global $wpdb;
            $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
            $db_table = $main_blog_prefix . 'be_mu_user_ips';

            // We get the ip data for that user
            $results = $wpdb->get_row( $wpdb->prepare( 'SELECT ip FROM ' . $db_table . ' WHERE user_id = %d', $user_id ), ARRAY_A );

            // If we do not have the ip of that user we insert the current user ip
            if ( null === $results ) {
                $wpdb->insert(
                	$db_table,
                	array(
                		'user_id' => $user_id,
                		'ip' => $ip,
                	),
                	array(
                		'%d',
                		'%s',
                	)
                );
            } elseif ( $results['ip'] != $ip ) {

                // If we have data for the user ip and it is different from his current ip we update it with the new one
                $wpdb->update(
                    $db_table,
                    array( 'ip' => $ip ),
                    array( 'user_id' => $user_id ),
                    array( '%s' ),
                    array( '%s' )
                );
            }
        }
    }
}

// Adds the current ip of the logged-in user in the database on every page view
function be_mu_track_ip() {
    if ( is_user_logged_in() ) {
        be_mu_add_or_update_user_ip( get_current_user_id() );
    }
}

/**
 * Checks if the user is banned and also removes the ban if the ban period has passed
 * @param int $user_id
 * @return bool
 */
function be_mu_is_user_banned( $user_id ) {

    // If the Ban Users module is turned off, all users are considered not banned
    if ( be_mu_get_setting( 'be-mu-ban-status-module' ) == 'off' ) {
        return false;
    }

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
    $db_table = $main_blog_prefix . 'be_mu_banned_users';

    // Get database data for the selected user
    $results = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $db_table . ' WHERE user_id = %d', $user_id ), ARRAY_A );

    // If there is no data, then the user is not banned
    if ( null === $results ) {
        return false;
    } else {

        // If the period is set to Permanent - the user is banned
        if ( 'Permanent' == $results['period'] ) {
            return true;
        } else {

            // If the period is different from Permanent we have to calculate if the user should be still banned and remove the ban if not
            $time_banned = intval( $results['unix_time_banned'] );

            // Based on the ban period we set the time when the ban should be removed
            if ( '90 days' == $results['period'] ) {
                $time_to_remove_ban = $time_banned + ( 90 * 24 * 3600 );
            } elseif ( '30 days' == $results['period'] ) {
                $time_to_remove_ban = $time_banned + ( 30 * 24 * 3600 );
            } else {
                $time_to_remove_ban = $time_banned + ( 7 * 24 * 3600 );
            }

            // If the time to remove the ban has come, we remove the ban and return false (user is not banned)
            if ( time() >= $time_to_remove_ban ) {
                be_mu_unban_user_and_ip( $user_id );
                return false;
            }

            // Otherwise we return true, user is banned
            return true;
        }
    }
}

/**
 * Forces a user to be logged-out from all devices
 * @param int $user_id
 */
function be_mu_logout_user( $user_id ) {

    // Get all the sessions for the user
    $sessions = WP_Session_Tokens::get_instance( $user_id );

    // Destroy them
    $sessions->destroy_all();
}

/**
 * Logs out a user and then bans his user id and ip address
 * @param int $user_id
 * @param string $ip
 * @return bool
 */
function be_mu_ban_user_and_ip( $user_id, $ip, $period ) {

    // We logout the user
    be_mu_logout_user( $user_id );

    // We will ban the user only if it is not banned
    if ( ! be_mu_is_user_banned( $user_id ) ) {

        // We need these to connect to the database
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        global $wpdb;
        $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
        $db_table = $main_blog_prefix . 'be_mu_banned_users';

        // We get the user username
        $user_data = get_userdata( $user_id );
        $username = $user_data->user_login;

        // We add the user in the db table with banned users
        $status = $wpdb->insert(
        	$db_table,
        	array(
        		'user_id' => $user_id,
        		'username' => $username,
        		'period' => $period,
        		'ip' => $ip,
        		'unix_time_banned' => time(),
        	),
        	array(
        		'%d',
        		'%s',
        		'%s',
        		'%s',
        		'%d',
        	)
        );

        // We return the status of the query
        return $status;
    }

    // If the user is already banned we return true
    return true;
}

/**
 * Removes the ban (unbans) a given user (by user id)
 * @param int $user_id
 * @return bool
 */
function be_mu_unban_user_and_ip( $user_id ) {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
    $db_table = $main_blog_prefix . 'be_mu_banned_users';

    // Delete the ban for the selected user
    $status = $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $db_table . ' WHERE user_id = %d', $user_id ) );

    return $status;
}

/**
 * Checks if the IP is banned and also removes the ban if the ban period has passed
 * @param string $ip
 * @return bool
 */
function be_mu_is_ip_banned( $ip ) {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
    $db_table = $main_blog_prefix . 'be_mu_banned_users';

    // Get database data for the selected ip
    $results_multi_array = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $db_table . ' WHERE ip = %s', $ip ), ARRAY_A );

    // If there is no data, then the ip is not banned
    if ( empty( $results_multi_array ) || false === $ip ) {
        return false;
    } else {

        // We go through all the users that are banned with this ip
        foreach ( $results_multi_array as $results ) {

            // If the period is set to Permanent - the ip is banned
            if ( 'Permanent' == $results['period'] ) {
                return true;

            // If the period is different from Permanent we have to calculate if the user should be still banned and remove the ban if not
            } else {

                $time_banned = intval( $results['unix_time_banned'] );

                // Based on the ban period we set the time when the ban should be removed
                if ( '90 days' == $results['period'] ) {
                    $time_to_remove_ban = $time_banned + ( 90 * 24 * 3600 );
                } elseif ( '30 days' == $results['period'] ) {
                    $time_to_remove_ban = $time_banned + ( 30 * 24 * 3600 );
                } else {
                    $time_to_remove_ban = $time_banned + ( 7 * 24 * 3600 );
                }

                // If the time to remove the ban has come, we remove the ban and continue
                if ( time() >= $time_to_remove_ban ) {
                    be_mu_unban_user_and_ip( $results['user_id'] );
                } else {

                    // Otherwise on the first one that we find (that the time has not come yet), we return true (ip is banned) and we skip the others
                    return true;
                }
            }
        }
    }
    // If we make it to here then the ip is not banned since all the bans we found are expired
    return false;
}

/**
 * Returns array with information about a banned user
 * @param int $user_id
 * @return mixed
 */
function be_mu_get_banned_user_info( $user_id ) {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
    $db_table = $main_blog_prefix . 'be_mu_banned_users';

    // Get database data for the selected user
    $results = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $db_table . ' WHERE user_id = %d', $user_id ), ARRAY_A );

    // If there is no data, then the user is not banned
    if ( null === $results ) {
        return false;
    } else {

        // If there is data, we return it
        return $results;
    }
}

/**
 * Returns the last ip we know for a user or false if we don't have his ip
 * @param int $user_id
 * @return mixed
 */
function be_mu_get_user_ip( $user_id ) {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
    $db_table = $main_blog_prefix . 'be_mu_user_ips';

    // Get database data for the selected user
    $results = $wpdb->get_row( $wpdb->prepare( 'SELECT ip FROM ' . $db_table . ' WHERE user_id = %d', $user_id ), ARRAY_A );

    // If there is no data or the IP is invalid, we return false. Otherwise we return the IP.
    if ( null === $results || ! be_mu_is_valid_ip( $results['ip'] ) ) {
        return false;
    } else {
        return $results['ip'];
    }
}

/**
 * Returns an array with the user ids of the users that have the selected ip (or false if there are none)
 * @param string $ip
 * @param int $except_user_id
 * @return mixed
 */
function be_mu_get_users_with_ip_except_user( $ip, $except_user_id ) {

    if ( false === $ip || ! be_mu_is_valid_ip( $ip ) ) {
        return false;
    }

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
    $db_table = $main_blog_prefix . 'be_mu_user_ips';

    // Get the users with the selected ip
    $results_multi_array = $wpdb->get_results( $wpdb->prepare( 'SELECT user_id FROM ' . $db_table
        . ' WHERE ip = %s AND user_id != %d', $ip, $except_user_id ), ARRAY_A);

    // If there is no data, then there are no users with this ip that we know of
    if ( empty( $results_multi_array ) ) {
        return false;

    // If there are users with the ip, we make an array with their IDs and return it
    } else {

        $user_ids = Array();
        foreach ( $results_multi_array as $results ) {

            // If the user exists we add it to the array to return
            if ( false !== get_userdata( $results['user_id'] ) ) {
                $user_ids[] = intval( $results['user_id'] );
            }
        }
        return $user_ids;
    }
}

// Checks on every page view if the IP of anyone that is logged-in is banned. If it is banned, it forces a logout and displays a message.
function be_mu_check_for_banned_ip() {

    if ( function_exists( 'be_mu_get_visitor_ip' ) ) {

        // We get the current visitor ip
        $ip = be_mu_get_visitor_ip();

        if ( is_user_logged_in() && false !== $ip ) {
            if ( be_mu_is_ip_banned( $ip ) ) {

                // We log out the user
                be_mu_logout_user( get_current_user_id() );

                // We show a message and stop everything
                wp_die( esc_html__( 'Your IP address is banned from login, signup and comments. We logged you out.', 'beyond-multisite' ) );
            }
        }
    }
}

/**
 * Adds the Ban Status column in the users table on the users page in the network admin panel
 * @param array $columns
 * @return array
 */
function be_mu_ban_add_ban_status_column( $columns ) {
    return be_mu_add_element_to_array( $columns, 'be-mu-user-ban-status', esc_html__( 'Ban Status', 'beyond-multisite' ), 'registered' );
}

/**
 * Adds the Last IP column in the users table on the users page in the network admin panel
 * @param array $columns
 * @return array
 */
function be_mu_ban_add_last_ip_column( $columns ) {
    return be_mu_add_element_to_array( $columns, 'be-mu-user-ip', esc_html__( 'Last IP', 'beyond-multisite' ), 'registered' );
}

// Using ajax we get and update the country code of an IP address for a user.
function be_mu_ban_check_ip_country_action_callback() {

    // We check the nonce we created earlier to validate the ajax request and improve security
    if ( ! check_ajax_referer( 'be_mu_flags_nonce', 'be_mu_flags_nonce', false ) ) {
        wp_die();
    }

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
        wp_die();
    }

    $ip = wp_strip_all_tags( $_POST['ip'] );
    $user_id = intval( $_POST['user_id'] );

    // We stop everything if the IP address is not valid
    if ( ! be_mu_is_valid_ip( $ip ) ) {
        wp_die();
    }

    // We get the country code of the IP address
    $country_code = be_mu_ban_get_country_code_of_ip( $ip );

    // We stop everything if we failed at getting the country code
    if ( false === $country_code ) {
        wp_die();
    }

    // We get the array with all the country names and then set the $country variable based on the country code
    $countries = be_mu_ban_get_countries_array();
    if ( isset( $countries[ $country_code ] ) ) {
        $country = $countries[ $country_code ];
    } else {
        $country = __( 'Error', 'beyond-multisite' );
    }

    // We remove some things from the strings for security reasons
    $country_code = sanitize_html_class( $country_code );
    $country = wp_strip_all_tags( $country );

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
    $db_table = $main_blog_prefix . 'be_mu_user_ips';

    // We add the country code to the database
    $wpdb->query( $wpdb->prepare( 'UPDATE ' . $db_table . ' SET country_code = %s, country_code_ip = %s WHERE user_id = %d AND ip = %s',
        $country_code, $ip, $user_id, $ip ) );

    // This is the data to pass to the javascript function in json format
    $json_result = array(
        'countryCode' => esc_attr( $country_code ),
        'country' => esc_html( $country ),
    );

    echo json_encode( $json_result );

    // We end the request
    wp_die();
}

/**
 * Returns an array with all country names as values and country codes as keys. Sourse: https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
 * @return array
 */
function be_mu_ban_get_countries_array() {
    return Array(
        'AD' => __( "Andorra", "beyond-multisite" ),
        'AE' => __( "United Arab Emirates", "beyond-multisite" ),
        'AF' => __( "Afghanistan", "beyond-multisite" ),
        'AG' => __( "Antigua and Barbuda", "beyond-multisite" ),
        'AI' => __( "Anguilla", "beyond-multisite" ),
        'AL' => __( "Albania", "beyond-multisite" ),
        'AM' => __( "Armenia", "beyond-multisite" ),
        'AO' => __( "Angola", "beyond-multisite" ),
        'AQ' => __( "Antarctica", "beyond-multisite" ),
        'AR' => __( "Argentina", "beyond-multisite" ),
        'AS' => __( "American Samoa", "beyond-multisite" ),
        'AT' => __( "Austria", "beyond-multisite" ),
        'AU' => __( "Australia", "beyond-multisite" ),
        'AW' => __( "Aruba", "beyond-multisite" ),
        'AX' => __( "Aland Islands", "beyond-multisite" ),
        'AZ' => __( "Azerbaijan", "beyond-multisite" ),
        'BA' => __( "Bosnia and Herzegovina", "beyond-multisite" ),
        'BB' => __( "Barbados", "beyond-multisite" ),
        'BD' => __( "Bangladesh", "beyond-multisite" ),
        'BE' => __( "Belgium", "beyond-multisite" ),
        'BF' => __( "Burkina Faso", "beyond-multisite" ),
        'BG' => __( "Bulgaria", "beyond-multisite" ),
        'BH' => __( "Bahrain", "beyond-multisite" ),
        'BI' => __( "Burundi", "beyond-multisite" ),
        'BJ' => __( "Benin", "beyond-multisite" ),
        'BL' => __( "Saint Barthelemy", "beyond-multisite" ),
        'BM' => __( "Bermuda", "beyond-multisite" ),
        'BN' => __( "Brunei Darussalam", "beyond-multisite" ),
        'BO' => __( "Bolivia, Plurinational State of", "beyond-multisite" ),
        'BQ' => __( "Bonaire, Sint Eustatius and Saba", "beyond-multisite" ),
        'BR' => __( "Brazil", "beyond-multisite" ),
        'BS' => __( "Bahamas", "beyond-multisite" ),
        'BT' => __( "Bhutan", "beyond-multisite" ),
        'BV' => __( "Bouvet Island", "beyond-multisite" ),
        'BW' => __( "Botswana", "beyond-multisite" ),
        'BY' => __( "Belarus", "beyond-multisite" ),
        'BZ' => __( "Belize", "beyond-multisite" ),
        'CA' => __( "Canada", "beyond-multisite" ),
        'CC' => __( "Cocos (Keeling) Islands", "beyond-multisite" ),
        'CD' => __( "Congo, the Democratic Republic of the", "beyond-multisite" ),
        'CF' => __( "Central African Republic", "beyond-multisite" ),
        'CG' => __( "Congo", "beyond-multisite" ),
        'CH' => __( "Switzerland", "beyond-multisite" ),
        'CI' => __( "Cote d'Ivoire", "beyond-multisite" ),
        'CK' => __( "Cook Islands", "beyond-multisite" ),
        'CL' => __( "Chile", "beyond-multisite" ),
        'CM' => __( "Cameroon", "beyond-multisite" ),
        'CN' => __( "China", "beyond-multisite" ),
        'CO' => __( "Colombia", "beyond-multisite" ),
        'CR' => __( "Costa Rica", "beyond-multisite" ),
        'CU' => __( "Cuba", "beyond-multisite" ),
        'CV' => __( "Cabo Verde", "beyond-multisite" ),
        'CW' => __( "Curacao", "beyond-multisite" ),
        'CX' => __( "Christmas Island", "beyond-multisite" ),
        'CY' => __( "Cyprus", "beyond-multisite" ),
        'CZ' => __( "Czechia", "beyond-multisite" ),
        'DE' => __( "Germany", "beyond-multisite" ),
        'DJ' => __( "Djibouti", "beyond-multisite" ),
        'DK' => __( "Denmark", "beyond-multisite" ),
        'DM' => __( "Dominica", "beyond-multisite" ),
        'DO' => __( "Dominican Republic", "beyond-multisite" ),
        'DZ' => __( "Algeria", "beyond-multisite" ),
        'EC' => __( "Ecuador", "beyond-multisite" ),
        'EE' => __( "Estonia", "beyond-multisite" ),
        'EG' => __( "Egypt", "beyond-multisite" ),
        'EH' => __( "Western Sahara", "beyond-multisite" ),
        'ER' => __( "Eritrea", "beyond-multisite" ),
        'ES' => __( "Spain", "beyond-multisite" ),
        'ET' => __( "Ethiopia", "beyond-multisite" ),
        'FI' => __( "Finland", "beyond-multisite" ),
        'FJ' => __( "Fiji", "beyond-multisite" ),
        'FK' => __( "Falkland Islands (Malvinas)", "beyond-multisite" ),
        'FM' => __( "Micronesia, Federated States of", "beyond-multisite" ),
        'FO' => __( "Faroe Islands", "beyond-multisite" ),
        'FR' => __( "France", "beyond-multisite" ),
        'GA' => __( "Gabon", "beyond-multisite" ),
        'GB' => __( "United Kingdom of Great Britain and Northern Ireland", "beyond-multisite" ),
        'GD' => __( "Grenada", "beyond-multisite" ),
        'GE' => __( "Georgia", "beyond-multisite" ),
        'GF' => __( "French Guiana", "beyond-multisite" ),
        'GG' => __( "Guernsey", "beyond-multisite" ),
        'GH' => __( "Ghana", "beyond-multisite" ),
        'GI' => __( "Gibraltar", "beyond-multisite" ),
        'GL' => __( "Greenland", "beyond-multisite" ),
        'GM' => __( "Gambia", "beyond-multisite" ),
        'GN' => __( "Guinea", "beyond-multisite" ),
        'GP' => __( "Guadeloupe", "beyond-multisite" ),
        'GQ' => __( "Equatorial Guinea", "beyond-multisite" ),
        'GR' => __( "Greece", "beyond-multisite" ),
        'GS' => __( "South Georgia and the South Sandwich Islands", "beyond-multisite" ),
        'GT' => __( "Guatemala", "beyond-multisite" ),
        'GU' => __( "Guam", "beyond-multisite" ),
        'GW' => __( "Guinea-Bissau", "beyond-multisite" ),
        'GY' => __( "Guyana", "beyond-multisite" ),
        'HK' => __( "Hong Kong", "beyond-multisite" ),
        'HM' => __( "Heard Island and McDonald Islands", "beyond-multisite" ),
        'HN' => __( "Honduras", "beyond-multisite" ),
        'HR' => __( "Croatia", "beyond-multisite" ),
        'HT' => __( "Haiti", "beyond-multisite" ),
        'HU' => __( "Hungary", "beyond-multisite" ),
        'ID' => __( "Indonesia", "beyond-multisite" ),
        'IE' => __( "Ireland", "beyond-multisite" ),
        'IL' => __( "Israel", "beyond-multisite" ),
        'IM' => __( "Isle of Man", "beyond-multisite" ),
        'IN' => __( "India", "beyond-multisite" ),
        'IO' => __( "British Indian Ocean Territory", "beyond-multisite" ),
        'IQ' => __( "Iraq", "beyond-multisite" ),
        'IR' => __( "Iran, Islamic Republic of", "beyond-multisite" ),
        'IS' => __( "Iceland", "beyond-multisite" ),
        'IT' => __( "Italy", "beyond-multisite" ),
        'JE' => __( "Jersey", "beyond-multisite" ),
        'JM' => __( "Jamaica", "beyond-multisite" ),
        'JO' => __( "Jordan", "beyond-multisite" ),
        'JP' => __( "Japan", "beyond-multisite" ),
        'KE' => __( "Kenya", "beyond-multisite" ),
        'KG' => __( "Kyrgyzstan", "beyond-multisite" ),
        'KH' => __( "Cambodia", "beyond-multisite" ),
        'KI' => __( "Kiribati", "beyond-multisite" ),
        'KM' => __( "Comoros", "beyond-multisite" ),
        'KN' => __( "Saint Kitts and Nevis", "beyond-multisite" ),
        'KP' => __( "Korea, Democratic People's Republic of", "beyond-multisite" ),
        'KR' => __( "Korea, Republic of", "beyond-multisite" ),
        'KW' => __( "Kuwait", "beyond-multisite" ),
        'KY' => __( "Cayman Islands", "beyond-multisite" ),
        'KZ' => __( "Kazakhstan", "beyond-multisite" ),
        'LA' => __( "Lao People's Democratic Republic", "beyond-multisite" ),
        'LB' => __( "Lebanon", "beyond-multisite" ),
        'LC' => __( "Saint Lucia", "beyond-multisite" ),
        'LI' => __( "Liechtenstein", "beyond-multisite" ),
        'LK' => __( "Sri Lanka", "beyond-multisite" ),
        'LR' => __( "Liberia", "beyond-multisite" ),
        'LS' => __( "Lesotho", "beyond-multisite" ),
        'LT' => __( "Lithuania", "beyond-multisite" ),
        'LU' => __( "Luxembourg", "beyond-multisite" ),
        'LV' => __( "Latvia", "beyond-multisite" ),
        'LY' => __( "Libya", "beyond-multisite" ),
        'MA' => __( "Morocco", "beyond-multisite" ),
        'MC' => __( "Monaco", "beyond-multisite" ),
        'MD' => __( "Moldova, Republic of", "beyond-multisite" ),
        'ME' => __( "Montenegro", "beyond-multisite" ),
        'MF' => __( "Saint Martin (French part)", "beyond-multisite" ),
        'MG' => __( "Madagascar", "beyond-multisite" ),
        'MH' => __( "Marshall Islands", "beyond-multisite" ),
        'MK' => __( "Macedonia, the former Yugoslav Republic of", "beyond-multisite" ),
        'ML' => __( "Mali", "beyond-multisite" ),
        'MM' => __( "Myanmar", "beyond-multisite" ),
        'MN' => __( "Mongolia", "beyond-multisite" ),
        'MO' => __( "Macao", "beyond-multisite" ),
        'MP' => __( "Northern Mariana Islands", "beyond-multisite" ),
        'MQ' => __( "Martinique", "beyond-multisite" ),
        'MR' => __( "Mauritania", "beyond-multisite" ),
        'MS' => __( "Montserrat", "beyond-multisite" ),
        'MT' => __( "Malta", "beyond-multisite" ),
        'MU' => __( "Mauritius", "beyond-multisite" ),
        'MV' => __( "Maldives", "beyond-multisite" ),
        'MW' => __( "Malawi", "beyond-multisite" ),
        'MX' => __( "Mexico", "beyond-multisite" ),
        'MY' => __( "Malaysia", "beyond-multisite" ),
        'MZ' => __( "Mozambique", "beyond-multisite" ),
        'NA' => __( "Namibia", "beyond-multisite" ),
        'NC' => __( "New Caledonia", "beyond-multisite" ),
        'NE' => __( "Niger", "beyond-multisite" ),
        'NF' => __( "Norfolk Island", "beyond-multisite" ),
        'NG' => __( "Nigeria", "beyond-multisite" ),
        'NI' => __( "Nicaragua", "beyond-multisite" ),
        'NL' => __( "Netherlands", "beyond-multisite" ),
        'NO' => __( "Norway", "beyond-multisite" ),
        'NP' => __( "Nepal", "beyond-multisite" ),
        'NR' => __( "Nauru", "beyond-multisite" ),
        'NU' => __( "Niue", "beyond-multisite" ),
        'NZ' => __( "New Zealand", "beyond-multisite" ),
        'OM' => __( "Oman", "beyond-multisite" ),
        'PA' => __( "Panama", "beyond-multisite" ),
        'PE' => __( "Peru", "beyond-multisite" ),
        'PF' => __( "French Polynesia", "beyond-multisite" ),
        'PG' => __( "Papua New Guinea", "beyond-multisite" ),
        'PH' => __( "Philippines", "beyond-multisite" ),
        'PK' => __( "Pakistan", "beyond-multisite" ),
        'PL' => __( "Poland", "beyond-multisite" ),
        'PM' => __( "Saint Pierre and Miquelon", "beyond-multisite" ),
        'PN' => __( "Pitcairn", "beyond-multisite" ),
        'PR' => __( "Puerto Rico", "beyond-multisite" ),
        'PS' => __( "Palestine, State of", "beyond-multisite" ),
        'PT' => __( "Portugal", "beyond-multisite" ),
        'PW' => __( "Palau", "beyond-multisite" ),
        'PY' => __( "Paraguay", "beyond-multisite" ),
        'QA' => __( "Qatar", "beyond-multisite" ),
        'RE' => __( "Reunion", "beyond-multisite" ),
        'RO' => __( "Romania", "beyond-multisite" ),
        'RS' => __( "Serbia", "beyond-multisite" ),
        'RU' => __( "Russian Federation", "beyond-multisite" ),
        'RW' => __( "Rwanda", "beyond-multisite" ),
        'SA' => __( "Saudi Arabia", "beyond-multisite" ),
        'SB' => __( "Solomon Islands", "beyond-multisite" ),
        'SC' => __( "Seychelles", "beyond-multisite" ),
        'SD' => __( "Sudan", "beyond-multisite" ),
        'SE' => __( "Sweden", "beyond-multisite" ),
        'SG' => __( "Singapore", "beyond-multisite" ),
        'SH' => __( "Saint Helena, Ascension and Tristan da Cunha", "beyond-multisite" ),
        'SI' => __( "Slovenia", "beyond-multisite" ),
        'SJ' => __( "Svalbard and Jan Mayen", "beyond-multisite" ),
        'SK' => __( "Slovakia", "beyond-multisite" ),
        'SL' => __( "Sierra Leone", "beyond-multisite" ),
        'SM' => __( "San Marino", "beyond-multisite" ),
        'SN' => __( "Senegal", "beyond-multisite" ),
        'SO' => __( "Somalia", "beyond-multisite" ),
        'SR' => __( "Suriname", "beyond-multisite" ),
        'SS' => __( "South Sudan", "beyond-multisite" ),
        'ST' => __( "Sao Tome and Principe", "beyond-multisite" ),
        'SV' => __( "El Salvador", "beyond-multisite" ),
        'SX' => __( "Sint Maarten (Dutch part)", "beyond-multisite" ),
        'SY' => __( "Syrian Arab Republic", "beyond-multisite" ),
        'SZ' => __( "Swaziland", "beyond-multisite" ),
        'TC' => __( "Turks and Caicos Islands", "beyond-multisite" ),
        'TD' => __( "Chad", "beyond-multisite" ),
        'TF' => __( "French Southern Territories", "beyond-multisite" ),
        'TG' => __( "Togo", "beyond-multisite" ),
        'TH' => __( "Thailand", "beyond-multisite" ),
        'TJ' => __( "Tajikistan", "beyond-multisite" ),
        'TK' => __( "Tokelau", "beyond-multisite" ),
        'TL' => __( "Timor-Leste", "beyond-multisite" ),
        'TM' => __( "Turkmenistan", "beyond-multisite" ),
        'TN' => __( "Tunisia", "beyond-multisite" ),
        'TO' => __( "Tonga", "beyond-multisite" ),
        'TR' => __( "Turkey", "beyond-multisite" ),
        'TT' => __( "Trinidad and Tobago", "beyond-multisite" ),
        'TV' => __( "Tuvalu", "beyond-multisite" ),
        'TW' => __( "Taiwan, Province of China", "beyond-multisite" ),
        'TZ' => __( "Tanzania, United Republic of", "beyond-multisite" ),
        'UA' => __( "Ukraine", "beyond-multisite" ),
        'UG' => __( "Uganda", "beyond-multisite" ),
        'UM' => __( "United States Minor Outlying Islands", "beyond-multisite" ),
        'US' => __( "United States of America", "beyond-multisite" ),
        'UY' => __( "Uruguay", "beyond-multisite" ),
        'UZ' => __( "Uzbekistan", "beyond-multisite" ),
        'VA' => __( "Holy See", "beyond-multisite" ),
        'VC' => __( "Saint Vincent and the Grenadines", "beyond-multisite" ),
        'VE' => __( "Venezuela, Bolivarian Republic of", "beyond-multisite" ),
        'VG' => __( "Virgin Islands, British", "beyond-multisite" ),
        'VI' => __( "Virgin Islands, U.S.", "beyond-multisite" ),
        'VN' => __( "Viet Nam", "beyond-multisite" ),
        'VU' => __( "Vanuatu", "beyond-multisite" ),
        'WF' => __( "Wallis and Futuna", "beyond-multisite" ),
        'WS' => __( "Samoa", "beyond-multisite" ),
        'YE' => __( "Yemen", "beyond-multisite" ),
        'YT' => __( "Mayotte", "beyond-multisite" ),
        'ZA' => __( "South Africa", "beyond-multisite" ),
        'ZM' => __( "Zambia", "beyond-multisite" ),
        'ZW' => __( "Zimbabwe", "beyond-multisite" ),
    );
}

/**
 * Adds the data for the extra columns we added in the table on the Users page in the network admin panel
 * @param string $value
 * @param string $column_name
 * @param int $user_id
 * @return string
 */
function be_mu_ban_extra_columns_data( $value, $column_name, $user_id ) {

    // If this is the last ip column we get the ip and show it along with useful links (or not detected if we do not have the ip)
	if ( 'be-mu-user-ip' == $column_name ) {

        if ( $ip = be_mu_get_user_ip( $user_id ) ) {

            $country_flag_html = be_mu_ban_get_country_flag_html( $user_id, $ip );

            return '<span class="be-mu-force-one-line">' . $country_flag_html . '<span id="be-mu-ban-ip-value-' . intval( $user_id ) . '">'
                . esc_html( $ip ) . '</span></span>'
                . '<br />'
                . '<a title="' . esc_attr__( 'View IP in CleanTalk', 'beyond-multisite' )
                . '" href="https://cleantalk.org/blacklists/' . esc_attr( $ip ) . '" target="_blank">'
                . '<img src="' . esc_url( be_mu_img_url( 'clean-talk.png' ) ) . '" width="16" height="16" /></a>'
                . '<a title="' . esc_attr__( 'View IP in Project Honey Pot', 'beyond-multisite' )
                . '" href="https://www.projecthoneypot.org/ip_' . esc_attr( $ip ) . '" target="_blank">'
                . '<img src="' . esc_url( be_mu_img_url( 'honey-pot.png' ) ) . '" width="16" height="16" /></a>'
                . '<a title="' . esc_attr__( 'View IP in Stop Forum Spam', 'beyond-multisite' )
                . '" href="https://www.stopforumspam.com/ipcheck/' . esc_attr( $ip ) . '" target="_blank">'
                . '<img src="' . esc_url( be_mu_img_url( 'stop-spam.png' ) ) . '" width="16" height="16" /></a>'
                . '<a title="' . esc_attr__( 'View IP in Google', 'beyond-multisite' )
                . '" href="https://www.google.com/search?q=' . esc_attr( $ip ) . '" target="_blank">'
                . '<img src="' . esc_url( be_mu_img_url( 'google.png' ) ) . '" width="16" height="16" /></a>';
        } else {
            return esc_html__( 'Not detected', 'beyond-multisite' );
        }
    }

    // If this is the ban status column we show it
	if ( 'be-mu-user-ban-status' == $column_name ) {

        // If the user is banned we show banned
        if ( be_mu_is_user_banned( $user_id ) ) {
            $to_return = '<b>' . esc_html__( 'Banned', 'beyond-multisite' ) . '</b>';

        // If the user id not banned we check if the ip is banned from another user ban
        } else {

            // If we have the current user ip it could be banned, if not it is not
            if ( $ip = be_mu_get_user_ip( $user_id ) ) {

                // If the ip is banned we show that user is not banned but ip is banned, otherwise we show not banned
                if ( be_mu_is_ip_banned( $ip ) ) {
                    $to_return = esc_html__( 'User:', 'beyond-multisite' ) . ' <span>' . esc_html__( 'Not Banned', 'beyond-multisite' )
                        . '</span><br />' . esc_html__( 'IP:', 'beyond-multisite' ) . ' <b>' . esc_html__( 'Banned', 'beyond-multisite' ) . '</b>';
                } else {
                    $to_return = '<span>' . esc_html__( 'Not Banned', 'beyond-multisite' ) . '</span>';
                }
            } else {
                $to_return = '<span>' . esc_html__( 'Not Banned', 'beyond-multisite' ) . '</span>';
            }
        }

        // We return the text to show
        return $to_return;
    }

    // If this is not any of our custom columns we just return the normal data
    return $value;
}

/**
 * Adds the links to the actions to ban and unban a user on the Users page on every row in the list of user actions
 * @param array $actions
 * @param object $user
 * @return array
 */
function be_mu_ban_users_action_link( $actions, $user ) {

    /**
     * Here we check for two variables in the url: s and paged. The first one will be present if this is a result of a search for users and the second will
     * be present if this is some next page of the users table. Later we add this data to the urls that lead to the ban or unban page, so we can show
     * the super admin a go back link to the exact same page after he is done with the ban/unban.
     */
    if ( isset( $_GET['s'] ) ) {
        $get_search = '&s=' . $_GET['s'];
    } else {
        $get_search = '';
    }
    if ( isset( $_GET['paged'] ) ) {
        $get_paged = '&paged=' . $_GET['paged'];
    } else {
        $get_paged = '';
    }

    // This variable shows if we were able to add the action link where we wanted
    $is_added = 0;

    // This will be the new array with actions, it will include our actions placed where we want
    $new_actions = array();

    // We go through all the current actions and we add our new action on the place we want
    foreach ( $actions as $key => $value ) {

        // We put the action for the ban/unban before the delete action
        if ( 'delete' == $key ) {

            // If the user from the current row is banned we show an unban link action
            if ( be_mu_is_user_banned( $user->ID ) ) {
                // We add our action to the new actions array
        	    $new_actions['be-mu-remove-ban'] = '<a class="be-mu-green-link" href="' . esc_url( network_admin_url( 'users.php?page=be_mu_banned_users' )
                    . '&action=unban&user=' . intval( $user->ID ) . esc_attr( $get_search ) . esc_attr( $get_paged ) ) . '">'
                    . esc_html__( 'Unban', 'beyond-multisite'  ) . '</a>';

            // If the user from the current row is not banned we show a ban link action
            } elseif ( get_current_user_id() != $user->ID ) {

                // We add our action to the new actions array
        	    $new_actions['be-mu-ban-user'] = '<a class="be-mu-red-link" href="' . esc_url( network_admin_url( 'users.php?page=be_mu_banned_users' )
                    . '&action=ban&user=' . intval( $user->ID ) . esc_attr( $get_search ) . esc_attr( $get_paged ) ) . '">'
                    . esc_html__( 'Ban', 'beyond-multisite'  ) . '</a>';
            }

            // We were able to add the action where we wanted so no need to add it again later
            $is_added = 1;
        }

        // All the normal actions remain and are added to the new array we made
        $new_actions[ $key ] = $value;
    }

    // If we failed to add the action earlier we add it now to the end
    if ( 0 == $is_added ) {

        // If the user from the current row is banned we show an unban link action
        if ( be_mu_is_user_banned( $user->ID ) ) {

            // We add our action to the actions array
    	    $new_actions['be-mu-remove-ban'] = '<a class="be-mu-green-link" href="' . esc_url( network_admin_url( 'users.php?page=be_mu_banned_users' )
                . '&action=unban&user=' . intval( $user->ID ) . esc_attr( $get_search ) . esc_attr( $get_paged ) ) . '">'
                . esc_html__( 'Unban', 'beyond-multisite'  ) . '</a>';

        // If the user from the current row is not banned we show a ban link action
        } elseif ( get_current_user_id() != $user->ID ) {

            // We add our action to the actions array
    	    $new_actions['be-mu-ban-user'] = '<a class="be-mu-red-link" href="' . esc_url( network_admin_url( 'users.php?page=be_mu_banned_users' )
                . '&action=ban&user=' . intval( $user->ID ) . esc_attr( $get_search ) . esc_attr( $get_paged ) ) . '">'
                . esc_html__( 'Ban', 'beyond-multisite'  ) . '</a>';
        }
    }

    // We return the new array of actions we made
    return $new_actions;
}

// Displays the search text field and button for searching banned users
function be_mu_echo_search_banned_field() {
    ?>
    <div class="be-mu-white-box be-mu-w100per">
        <input name="be-mu-search-banned-string" onkeypress="banUsersKeypressSearchBanned( event )" id="be-mu-search-banned-string" type="text" size="20" />
        <input onclick="banUsersSearchBanned()" class="button" type="button" id="be-mu-search-banned-button"
            name="be-mu-search-banned-button" value="<?php esc_attr_e( 'Search banned users', 'beyond-multisite'  ); ?>" />
        <span class="be-mu-tooltip">
            <span class="be-mu-info">i</span>
            <span class="be-mu-tooltip-text">
                <?php
                    esc_html_e( 'Hint: This will search in the User ID, Username and IP address.', 'beyond-multisite' );
                ?>
            </span>
        </span>
    </div>
    <?php
}

// This function is executed when the Banned subpage is visited and also when the ban/unban action linkas are clicked
function be_mu_banned_users_subpage() {

    // Just to be safe, we stop everything if the user cannot manage the network
    if ( ! current_user_can( 'manage_network' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'beyond-multisite'  ) );
    }

    $visitor_ip = be_mu_get_visitor_ip();

    ?>

    <div class="wrap">

        <?php

        // We need these to connect to the database
        global $wpdb;
        $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
        $db_table = $main_blog_prefix . 'be_mu_banned_users';

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


            if ( isset( $_GET['search'] ) ) {
                $get_search = wp_unslash( trim( $_GET['search'] ) );
            }

            // The following code will run when the ban link is clicked
            if ( isset( $_GET['action'] ) && isset( $_GET['user'] ) && "ban" == $_GET['action'] ) {

                // We get some data for the user that is going to be banned (we will call him just the user below in this if segment)
                $user_id = intval( $_GET['user'] );
                $user_data = get_userdata( $user_id );
                $username = $user_data->user_login;
                $user_ip = be_mu_get_user_ip( $user_id );

                // This array will contain the usernames of users with the same ip as the user
                $usernames_with_same_ip = Array();

                // If there are users with the same ip we take their ids and then turn them into usernames and add them to the array
                if ( $user_ids_with_same_ip = be_mu_get_users_with_ip_except_user( $user_ip, $user_id ) ) {

                    foreach ( $user_ids_with_same_ip as $user_id_with_same_ip ) {

                        $other_user_data = get_userdata( $user_id_with_same_ip );
                        $usernames_with_same_ip[] = $other_user_data->user_login;
                    }
                }

                // The $status variable indicates the success or failure of the ban action (after the button is clicked)
                // The $disable_ban_submit variable will disble the ban button if filled with the proper string
                $status = $disable_ban_submit = '';

                // We set the user ban status and ip ban status as not banned for now
                $user_ban_status = $ip_ban_status = '<span class="be-mu-green">' . esc_html__( 'Not banned', 'beyond-multisite'  ) . '</span>';

                // This url points to the users page in the network admin panel, we will use it later to make a go back link
                $url = network_admin_url( 'users.php' );

                // Here we check for some variables we set earlier in the be_mu_ban_users_action_link function that will make the go back link lead to the
                // Exact page in the users page (it will consider if it is a search result and also the page number)
                if ( isset( $_GET['s'] ) && ! isset( $_GET['paged'] ) ) {
                    $url .= '?s=' . esc_attr( $_GET['s'] );
                } elseif ( isset( $_GET['paged'] ) && ! isset( $_GET['s'] ) ) {
                    $url .= '?paged=' . esc_attr( $_GET['paged'] );
                } elseif ( isset( $_GET['s'] ) && isset( $_GET['paged'] ) ) {
                    $url .= '?s=' . esc_attr( $_GET['s'] ) . '&paged=' . esc_attr( $_GET['paged'] );
                }

                // If the ban button is clicked and the user is not banned and we have his ip, then we will ban it
                if ( isset( $_POST['be-mu-ban-submit'] ) && ! be_mu_is_user_banned( $user_id ) && false !== $user_ip ) {

                    // Check the nonce field for better security
                    if ( ! wp_verify_nonce( $_POST['be-mu-ban-form-nonce-name'], 'be-mu-ban-form-nonce-action' ) ) {
                        $status = 'error-nonce';
                    } else {

                        // We get the chosen period setting and validate it
                        if ( isset( $_POST['be-mu-ban-period'] ) && ( 'Permanent' == $_POST['be-mu-ban-period'] || '90 days' == $_POST['be-mu-ban-period'] ||
                            '30 days' == $_POST['be-mu-ban-period'] || '7 days' == $_POST['be-mu-ban-period'] ) ) {
                            $ban_period = $_POST['be-mu-ban-period'];
                        } else {
                            $ban_period = 'Permanent';
                        }

                        // We update the setting for the ban period so next time it is set to the last chosen
                        // But we do not track for error because it is not vital to remember this setting
                        be_mu_set_or_make_settings( array( 'be-mu-ban-period' => $ban_period ) );

                        // We ban the user and his ip for the chosen period and we set the status variable based on the success or failure
                        if ( be_mu_ban_user_and_ip( $user_id, $user_ip, $ban_period ) ) {
                            $status = 'done';
                        } else {
                            $status = 'error';
                        }
                    }
                }

                // This variable shows is the user is banned, we set it to 0 - not banned for now
                $user_is_banned = 0;

                // We check if the user is banned and we set the ban status and we disable the ban button if it is
                if ( be_mu_is_user_banned( $user_id ) ) {
                    $user_is_banned = 1;
                    $user_ban_status = '<span class="be-mu-red">' . esc_html__( 'Banned', 'beyond-multisite'  ) . '</span>';
                    $banned_user_data = be_mu_get_banned_user_info( $user_id );

                    // If the user is banned, then the ip is also banned for sure, but if the user is not banned, the ip could be either way
                    // Because of bans from other users. This is why the user ban determines the disabled button.
                    $disable_ban_submit = 'disabled="disabled"';
                }
                // We check the ip ban status and we set it in variable (it is possible that the user is not banned but the ip is banned from another user)
                if ( be_mu_is_ip_banned( $user_ip ) ) {
                    $ip_ban_status = '<span class="be-mu-red">' . esc_html__( 'Banned', 'beyond-multisite'  ) . '</span>';
                }

                ?>

                <div class="be-mu-contain-ban">

                    <?php be_mu_header_super_admin_page( __( 'Ban user and IP', 'beyond-multisite'  ) );  ?>

                    <div class="be-mu-white-box be-mu-w100per">

                        <?php

                        // We do not have the user ip, so we cannot ban him
                        if ( false === $user_ip ) {

                        ?>
                            <div class="be-mu-w100per">
                                <p>
                                    <?php

                                    printf(
                                        esc_html__( 'The user %s cannot be banned. We do not have his IP address. '
                                            . 'The user has to visit the site while logged-in at least once while this plugin is active and '
                                            . 'this module is turned on. It is also possible that the user is hiding his IP.', 'beyond-multisite' ),
                                        '<b>' . esc_html( $username ) . '</b>'
                                    );

                                    ?>
                                </p>
                                <p class="be-mu-align-right">
                                    <b>
                                        <a href="<?php echo esc_url( $url ); ?>">&laquo; <?php esc_html_e( 'Go back to users', 'beyond-multisite' ); ?></a>
                                    </b>
                                </p>
                            </div>
                        <?php

                        // The user ip is the same as the ip of the super admin, so we do not allow the ban
                        } elseif ( false !== $visitor_ip && $visitor_ip == $user_ip ) {

                        ?>
                            <div class="be-mu-w100per">
                                <p>
                                    <?php

                                    printf(
                                        esc_html__( 'The user %s cannot be banned by you. The user has the '
                                            . 'same IP address as you. Banning the user will also ban you.', 'beyond-multisite' ),
                                        '<b>' . esc_html( $username ) . '</b>'
                                    );

                                    ?>
                                </p>
                                <p class="be-mu-align-right">
                                    <b>
                                        <a href="<?php echo esc_url( $url ); ?>">&laquo; <?php esc_html_e( 'Go back to users', 'beyond-multisite' ); ?></a>
                                    </b>
                                </p>
                            </div>
                        <?php

                        // We have the user ip and it is not the same as the one of the super admin so we allow the ban and show the form
                        } else {

                        $country_flag_html = be_mu_ban_get_country_flag_html( $user_id, $user_ip );

                        ?>
                        <div class="be-mu-w50per be-mu-left">

                            <form class="be-mu-ban-form" method="post" action="">
                                <p>
                                    <?php esc_html_e( 'Username:', 'beyond-multisite' ); ?> <b><?php echo esc_html( $username ); ?></b>
                                </p>
                                <p>
                                    <?php
                                        esc_html_e( 'IP address:', 'beyond-multisite' );
                                        echo ' ' . $country_flag_html;
                                        echo '<b><span id="be-mu-ban-ip-value-' . intval( $user_id ) . '">' . esc_html( $user_ip ) . '</span></b>';
                                    ?>
                                </p>
                                <p>
                                    <?php

                                    // If the user is not banned we show a drop down menu to choose the ban period
                                    // Otherwise we show the ban period for the banned user
                                    if ( ! $user_is_banned ) {
                                        echo '<label for="be-mu-ban-period">' . esc_html__( 'Ban period:', 'beyond-multisite' ) . '</label> ';
                                        be_mu_setting_select(
                                            'be-mu-ban-period',
                                            array( 'Permanent', '90 days', '30 days', '7 days' ),
                                            array(
                                                __( 'Permanent', 'beyond-multisite' ),
                                                sprintf( __( '%d days', 'beyond-multisite' ), 90 ),
                                                sprintf( __( '%d days', 'beyond-multisite' ), 30 ),
                                                sprintf( __( '%d days', 'beyond-multisite' ), 7 )
                                            )
                                        );
                                    } else {
                                        echo esc_html__( 'Ban period:', 'beyond-multisite') . ' <b>'
                                            . esc_html( be_mu_ban_translate_period( $banned_user_data['period'] ) ) . '</b>';
                                    }

                                    ?>
                                </p>
                                <p>
                                    <?php esc_html_e( 'User ban status:', 'beyond-multisite' ); ?> <b><?php echo $user_ban_status; ?></b>
                                </p>
                                <p>
                                    <?php esc_html_e( 'IP ban status:', 'beyond-multisite' ); ?> <b><?php echo $ip_ban_status; ?></b>
                                </p>
                                <p>
                                    <?php

                                    esc_html_e( 'Users with the same IP:', 'beyond-multisite' );

                                    // If there are other users with the same ip, we output their usernames in a textarea
                                    if ( count( $usernames_with_same_ip ) > 0 ) {

                                        echo '<br /><textarea>';
                                        foreach ( $usernames_with_same_ip as $same_ip_username ) {

                                            echo esc_textarea( $same_ip_username ) . "\n";
                                        }
                                        echo '</textarea>';
                                    } else {
                                        echo ' <b>' . esc_html__( 'None detected', 'beyond-multisite' ) . '</b>';
                                    }

                                    ?>
                                </p>
                                <p>
                                    <input <?php echo $disable_ban_submit; ?> class='button button-primary'
                                        name='be-mu-ban-submit' type='submit' value='<?php esc_attr_e( 'Ban user and IP', 'beyond-multisite' ); ?>' />
                                </p>
                                <p>
                                    <b>
                                        <a href="<?php echo esc_url( $url ); ?>">&laquo; <?php esc_html_e( 'Go back to users', 'beyond-multisite' ); ?></a>
                                    </b>
                                </p>
                                <?php

                                // We display the result status of the ban action
                                if ( 'done' == $status ) {
                                    echo '<p class="be-mu-green"><b>' . esc_html__( 'Done', 'beyond-multisite' ) . '</b></p>';
                                } elseif ( 'error' == $status ) {
                                    echo '<p class="be-mu-red"><b>' . esc_html__( 'Error', 'beyond-multisite' ) . '</b></p>';
                                } elseif ( 'error-nonce' == $status ) {
                                    echo '<p class="be-mu-red"><b>' . esc_html__( 'Error: Invalid security nonce. Please reload the page and try again.', 'beyond-multisite' )
                                        . '</b></p>';
                                }

                                // Add a nonce field for better security
                                wp_nonce_field( 'be-mu-ban-form-nonce-action', 'be-mu-ban-form-nonce-name' );

                                ?>

                            </form>
                        </div>

                        <div class="be-mu-w50per be-mu-right">
                            <?php esc_html_e( 'What will the ban do?', 'beyond-multisite' ); ?><br />
                            <ul class="be-mu-ul">
                                <li>
                                    <?php esc_html_e( 'The banned user will be logged-out and will be denied login from any IP address.', 'beyond-multisite' ); ?>
                                </li>
                                <li>
                                    <?php
                                    esc_html_e( 'If there are other users with the same IP, they will be logged-out. However, '
                                        . 'they will be able to login from another IP.', 'beyond-multisite' );
                                    ?>
                                </li>
                                <li>
                                    <?php esc_html_e( 'No one will be able to login, signup or comment from the banned IP.', 'beyond-multisite' ); ?>
                                </li>
                            </ul>
                            <?php be_mu_echo_ip_links( $user_ip ); ?>
                        </div>
                        <?php
                        }
                        ?>
                    </div>

                </div>

            <?php

            // The following code will run when the unban link is clicked
            } elseif ( isset( $_GET['action'] ) && isset( $_GET['user'] ) && 'unban' == $_GET['action'] ) {

                // We set the user id of the user that is going to be unbanned, we will call him the user below in this elseif segment
                $user_id = intval( $_GET['user'] );

                // The $status variable indicates the success or failure of the unban action (after the button is clicked)
                // The $disable_unban_submit variable will disble the unban button if filled with the proper string
                $status = $disable_unban_submit = '';

                // We set the user ban status and ip ban status as banned for now
                $user_ban_status = $ip_ban_status = '<span class="be-mu-red">' . esc_html__( 'Banned', 'beyond-multisite' ) . '</span>';

                // If the user is really banned we get the information about him and the ban
                if ( be_mu_is_user_banned( $user_id ) ) {
                    $banned_user_data = be_mu_get_banned_user_info( $user_id );
                    $username = $banned_user_data['username'];
                    $user_ip = $banned_user_data['ip'];
                    $period = $banned_user_data['period'];
                    $unix_time_banned = intval( $banned_user_data['unix_time_banned'] );

                    if ( ! be_mu_is_valid_ip( $user_ip ) ) {
                        $user_ip = false;
                    }

                    // If the button to unban is clicked we unban the user and set the success/failure status variable
                    if ( isset( $_POST['be-mu-unban-submit'] ) ) {

                        // Check the nonce field for better security
                        if ( ! wp_verify_nonce( $_POST['be-mu-unban-form-nonce-name'], 'be-mu-unban-form-nonce-action' ) ) {
                            $status = 'error-nonce';
                        } else {
                            if ( be_mu_unban_user_and_ip( $user_id ) ) {
                                $status = 'done';
                            } else {
                                $status = 'error';
                            }
                        }
                    }
                }

                // At this point we determine if the user is banned and set a variable to 1 - yes or 0 - no
                if ( be_mu_is_user_banned( $user_id ) ) {
                    $user_is_banned = 1;
                } else {
                    // If the user is not banned we get his data to display in the form and set the variable to disable the unban button
                    $user_is_banned = 0;
                    $user_data = get_userdata( $user_id );
                    $username = $user_data->user_login;
                    $user_ip = be_mu_get_user_ip( $user_id );
                    $user_ban_status = '<span class="be-mu-green">' . esc_html__( 'Not banned', 'beyond-multisite' ) . '</span>';
                    $disable_unban_submit = 'disabled="disabled"';
                }

                // This variable shows if only the ip is banned (and not the user) 1 - yes, 0 - no
                $only_ip_banned = 0;

                // We check if the ip is not banned and set the status to not banned if yes
                if ( ! be_mu_is_ip_banned( $user_ip ) ) {
                    $ip_ban_status = '<span class="be-mu-green">' . esc_html__( 'Not banned', 'beyond-multisite' ) . '</span>';

                // If the ip is banned and the user is not banned we set the variable from before to 1 and the ip ban status to banned with a star
                } elseif ( ! $user_is_banned ) {
                    $only_ip_banned = 1;
                    $ip_ban_status = '<span class="be-mu-red">' . esc_html__( 'Banned', 'beyond-multisite' ) . '*</span>';
                }

                // This url points to the users page in the network admin panel, we will use it later to make a go back link
                $url = network_admin_url( 'users.php' );

                // Here we check for some variables we set earlier in the be_mu_ban_users_action_link function that will make the go back link lead to the
                // Exact page in the users page (it will consider if it is a search result and also the page number)
                if ( isset( $_GET['s'] ) && ! isset( $_GET['paged'] ) ) {
                    $url .= '?s=' . esc_attr( $_GET['s'] );
                } elseif ( isset( $_GET['paged'] ) && ! isset( $_GET['s'] ) ) {
                    $url .= '?paged=' . esc_attr( $_GET['paged'] );
                } elseif ( isset( $_GET['s'] ) && isset( $_GET['paged'] ) ) {
                    $url .= '?s=' . esc_attr( $_GET['s'] ) . '&paged=' . esc_attr( $_GET['paged'] );
                }

                // This is another variable from the url, if it is set, then the unban link is clicked from the Banned submenu page
                // In that case we will show a go back link pointing there; also we add the correct page number and search query (if any) to the url
                if ( isset( $_GET['from_banned'] ) ) {
                    $url = network_admin_url( 'users.php?page=be_mu_banned_users' );
                    if ( $_GET['from_banned'] > 1 ) {
                        $url .= '&page_number=' . intval( $_GET['from_banned'] );
                    }
                    if ( isset( $_GET['search'] ) ) {
                        $url .= '&search=' . esc_attr( $get_search );
                    }
                    $go_back_string = esc_html__( 'Go back to banned users', 'beyond-multisite' );
                } else {
                    $go_back_string = esc_html__( 'Go back to users', 'beyond-multisite' );
                }

                // This array will contain the usernames of users with the same ip as the user
                $usernames_with_same_ip = Array();

                // If there are users with the same ip we take their ids and then turn them into usernames and add them to the array
                if ( $user_ids_with_same_ip = be_mu_get_users_with_ip_except_user( $user_ip, $user_id ) ) {
                    foreach ( $user_ids_with_same_ip as $user_id_with_same_ip ) {
                        $other_user_data = get_userdata( $user_id_with_same_ip );
                        $usernames_with_same_ip[] = $other_user_data->user_login;
                    }
                }

                $country_flag_html = be_mu_ban_get_country_flag_html( $user_id, $user_ip );

                ?>

                <div class="be-mu-contain-ban">

                    <?php be_mu_header_super_admin_page( esc_html__( 'Unban user and IP', 'beyond-multisite' ) ); ?>

                    <div class="be-mu-white-box be-mu-w100per">

                        <form class="be-mu-ban-form" method="post" action="">

                            <div class="be-mu-w50per be-mu-left">

                                <p>
                                    <?php esc_html_e( 'Username:', 'beyond-multisite' ); ?> <b><?php echo esc_html( $username ); ?></b>
                                </p>
                                <p>
                                    <?php
                                        esc_html_e( 'IP address:', 'beyond-multisite' );
                                        echo ' ' . $country_flag_html;
                                        echo '<b><span id="be-mu-ban-ip-value-' . intval( $user_id ) . '">' . esc_html( $user_ip ) . '</span></b>';
                                    ?>
                                </p>
                                <p>
                                    <?php esc_html_e( 'User ban status:', 'beyond-multisite' ); ?> <b><?php echo $user_ban_status; ?></b>
                                </p>
                                <p>
                                    <?php esc_html_e( 'IP ban status:', 'beyond-multisite' ); ?> <b><?php echo $ip_ban_status; ?></b>
                                </p>
                                <?php

                                // This shows only if the user is banned
                                if ( $user_is_banned )
                                {

                                ?>
                                    <p>
                                        <?php esc_html_e( 'Ban period:', 'beyond-multisite' ); ?>
                                        <b>
                                            <?php echo esc_html( be_mu_ban_translate_period( $period ) ); ?>
                                        </b>
                                    </p>
                                    <p>
                                        <?php esc_html_e( 'Ban date:', 'beyond-multisite' ); ?>
                                        <b>
                                            <?php echo esc_html( date_i18n( get_option( 'date_format' ), $unix_time_banned ) ); ?>
                                        </b>
                                    </p>
                                <?php

                                }

                                ?>
                                <p>
                                    <?php

                                    esc_html_e( 'Users with the same IP:', 'beyond-multisite' );

                                    // If there are other users with the same ip, we output their usernames in a textarea
                                    if ( count( $usernames_with_same_ip ) > 0 ) {
                                        echo '<br /><textarea>';
                                        foreach( $usernames_with_same_ip as $same_ip_username ) {
                                            echo esc_textarea( $same_ip_username ) . "\n";
                                        }
                                        echo '</textarea>';
                                    } else {
                                        echo ' <b>' . esc_html__( 'None detected', 'beyond-multisite' ) . '</b>';
                                    }

                                    ?>
                                </p>

                            </div>

                            <div class="be-mu-w50per be-mu-right">

                                <?php

                                    // This shows only if the ip is banned and the user is not
                                    if ( $only_ip_banned ) {
                                    ?>
                                        <p>
                                            <i>
                                                <span class='be-mu-red'>
                                                    <b>*</b>
                                                </span>
                                                <?php
                                                esc_html_e( 'The IP remains banned after you unban the user, because there is '
                                                    . 'another banned user with the same IP.', 'beyond-multisite' );
                                                ?>
                                            </i>
                                        </p>
                                    <?php
                                    }

                                    // This shows some useful links with info on the ip
                                    be_mu_echo_ip_links( $user_ip );
                                ?>

                                <p>
                                    <input <?php echo $disable_unban_submit; ?> class='button button-primary'
                                        name='be-mu-unban-submit' type='submit' value='<?php esc_attr_e( 'Unban user and IP', 'beyond-multisite' ); ?>' />
                                </p>

                                <p>
                                    <b>
                                        <a href="<?php echo esc_url( $url ); ?>">&laquo; <?php echo esc_html( $go_back_string ); ?></a>
                                    </b>
                                </p>

                                <?php

                                // We display the result status of the unban action
                                if ( 'done' == $status ) {
                                    echo '<p class="be-mu-green"><b>' . esc_html__( 'Done', 'beyond-multisite' ) . '</b></p>';
                                } elseif ( 'error' == $status ) {
                                    echo '<p class="be-mu-red"><b>' . esc_html__( 'Error', 'beyond-multisite' ) . '</b></p>';
                                } elseif ( 'error-nonce' == $status ) {
                                    echo '<p class="be-mu-red"><b>' . esc_html__( 'Error: Invalid security nonce. Please reload the page and try again.', 'beyond-multisite' )
                                        . '</b></p>';
                                }

                                // Add a nonce field for better security
                                wp_nonce_field( 'be-mu-unban-form-nonce-action', 'be-mu-unban-form-nonce-name' );

                                ?>

                            </div>

                        </form>

                    </div>

                </div>

            <?php

            // The following code will run when the Banned submenu of the network menu Users is clicked
            } else {

                /*
                 * Based on whether this is a search result or not we set 3 string variables - one for the mysql query, one for the url
                 * that we redirect to when the page number is changed with the javascript funciton banUsersGoToBannedPage, and one
                 * for the unban link
                 */
                if ( isset( $_GET['search'] ) && '' != $_GET['search'] ) {

                    // We get the total count of all banned users
                    $like = '%' . $wpdb->esc_like( $get_search ) . '%';
                    $total_banned_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( row_id ) FROM " . $db_table
                        . " WHERE username LIKE %s OR ip LIKE %s OR user_id LIKE %s", $like, $like, $like ) );

                    $unban_link_search_string = '&search=' . esc_attr( $get_search );
                } else {

                    // We get the total count of all banned users
                    $total_banned_count = $wpdb->get_var( 'SELECT COUNT( row_id ) FROM ' . $db_table . ' WHERE 1' );

                    $unban_link_search_string = '';
                }

                // How much users to show per page
                $per_page = 12;

                // We calculate the number of pages (each with $per_page users) we will shows them in
                $pages_count = ceil( $total_banned_count / $per_page );

                // We set the current page number
                if ( isset( $_GET['page_number'] ) ) {
                    $page_number = intval( $_GET['page_number'] );
                } else {
                    $page_number = 1;
                }

                // If the banned users are more than $per_page and therefor do not fit on one page, we will display the page number in the title with this variable
                if ( $total_banned_count > $per_page ) {
                    $page_number_string_heading = ' - ' . __( 'Page', 'beyond-multisite' ) . ' ' . $page_number . '/' . $pages_count;
                } else {
                    $page_number_string_heading = '';
                }

                // The title of the page
                $page_title = esc_html__( 'Banned Users', 'beyond-multisite' ) . esc_html( $page_number_string_heading );

                // We output the header of the page
                be_mu_header_super_admin_page( $page_title );

                // If this is a search result page, we display a different subtitle based on the number of results
                if ( isset( $_GET['search'] ) && '' != $_GET['search'] ) {

                    if ( $total_banned_count > 1 ) {

                        echo '<div class="be-mu-white-box be-mu-w100per">';
                        echo '<h2 class="be-mu-mtop5 be-mu-mbot5">';
                        printf(
                            esc_html__( 'There are %1$d search results for "%2$s". To go back to all banned users %3$sclick here%4$s.', 'beyond-multisite' ),
                            $total_banned_count,
                            esc_html( $get_search ),
                            '<a href="' . esc_url( network_admin_url( 'users.php?page=be_mu_banned_users' ) ) . '">',
                            '</a>'
                        );
                        echo '</h2>';
                        echo '</div>';

                    } elseif ( 1 == $total_banned_count ) {

                        echo '<div class="be-mu-white-box be-mu-w100per">';
                        echo '<h2 class="be-mu-mtop5 be-mu-mbot5">';
                        printf(
                            esc_html__( 'There is 1 search result for "%1$s". To go back to all banned users %2$sclick here%3$s.', 'beyond-multisite' ),
                            esc_html( $get_search ),
                            '<a href="' . esc_url( network_admin_url( 'users.php?page=be_mu_banned_users' ) ) . '">',
                            '</a>'
                        );
                        echo '</h2>';
                        echo '</div>';

                    } else {

                        echo '<div class="be-mu-white-box be-mu-w100per">';
                        echo '<h2 class="be-mu-mtop5 be-mu-mbot5">';
                        printf(
                            esc_html__( 'There are no search results for "%1$s". To go back to all banned users %2$sclick here%3$s.', 'beyond-multisite' ),
                            esc_html( $get_search ),
                            '<a href="' . esc_url( network_admin_url( 'users.php?page=be_mu_banned_users' ) ) . '">',
                            '</a>'
                        );
                        echo '</h2>';
                        echo '</div>';

                    }

                }

                // If there is no data, then there are no banned users
                if ( empty( $total_banned_count ) ) {

                    // Different message is shown based on whether this is a search result or not
                    if ( isset( $_GET['search'] ) && '' != $_GET['search'] ) {
                        // We display the search text field and button
                        be_mu_echo_search_banned_field();

                        // A message for not finding any results
                        echo '<div class="be-mu-white-box be-mu-w100per">'
                            . esc_html__( 'There are no results for that search query.', 'beyond-multisite' )
                            . '</div>';
                    } else {
                        echo '<div class="be-mu-white-box be-mu-w100per">';
                        printf(
                            esc_html__( 'There are no banned users. You can ban a user from the %1$sUsers%2$s page.', 'beyond-multisite' ),
                            '<a href="' . esc_url( network_admin_url( 'users.php' ) ) . '">',
                            '</a>'
                        );
                        echo '</div>';
                    }

                // If there are banned users, we will display them in a table
                } else {

                    // This is the limit string for the mysql query - it is calculated based on the current page number
                    $limit_string = ( ( $page_number - 1 ) * $per_page ) . ',' . $per_page;

                    if ( isset( $_GET['search'] ) && '' != $_GET['search'] ) {

                        // Get the banned users based on a search query
                        $like = '%' . $wpdb->esc_like( $get_search ) . '%';
                        $results_multi_array = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $db_table . " WHERE username LIKE %s"
                            . " OR ip LIKE %s OR user_id LIKE %s ORDER BY unix_time_banned DESC LIMIT " . $limit_string, $like, $like, $like ), ARRAY_A );

                    } else {

                        // Get all the banned users
                        $results_multi_array = $wpdb->get_results( 'SELECT * FROM ' . $db_table . ' WHERE 1 ORDER BY unix_time_banned DESC LIMIT '
                            . $limit_string, ARRAY_A );
                    }

                    // We display the search text field and button
                    be_mu_echo_search_banned_field();

                    // Here starts the main table with the banned users
                    echo '<table class="be-mu-table be-mu-mbot15 be-mu-w100per">';

                    echo '<thead>'
                        . '<tr>'
                        . '<th>' . esc_html__( 'User ID', 'beyond-multisite' ) . '</th>'
                        . '<th>' . esc_html__( 'Username', 'beyond-multisite' ) . '</th>'
                        . '<th>' . esc_html__( 'IP', 'beyond-multisite' ) . '</th>'
                        . '<th>' . esc_html__( 'Ban Period', 'beyond-multisite' ) . '</th>'
                        . '<th>' . esc_html__( 'Ban Date and Time', 'beyond-multisite' ) . '* ' . '<div class="be-mu-sort-arrow"></div>' . '</th>'
                        . '<th>' . esc_html__( 'User Deleted', 'beyond-multisite' ) . '</th>'
                        . '<th>' . esc_html__( 'Actions', 'beyond-multisite' ) . '</th>'
                        . '</tr>'
                        . '</thead>';

                    echo '<tbody>';

                    // We go through all the banned users
                    foreach ( $results_multi_array as $results ) {

                        // Get the current user id
                        $current_user_id = intval( $results['user_id'] );

                        // If the user exists (it is not deleted) we set a variable as not deleted and we will show his username with a link to edit the user
                        if ( be_mu_user_exists( $current_user_id ) ) {
                            $user_deleted = esc_html__( 'Not deleted', 'beyond-multisite' );
                            $username = '<a href="' . esc_url( network_admin_url( 'user-edit.php' ) . '?user_id=' . $current_user_id
                                . '&wp_http_referer=/wp-admin/network/users.php?page=be_mu_banned_users' ) . '">'
                                . '<b>' . esc_html( $results['username'] ) . '</b>'
                                . '</a>';
                        } else {
                            $user_deleted = '<span class="be-mu-red">' . esc_html__( 'Deleted', 'beyond-multisite' ) . '</span>';
                            $username = '<b>' . esc_html( $results['username'] ) . '</b>';
                        }

                        if ( ! be_mu_is_valid_ip( $results['ip'] ) ) {
                            $results['ip'] = false;
                        }

                        $country_flag_html = be_mu_ban_get_country_flag_html( $current_user_id, $results['ip'] );

                        //now we start showing the actual row with the cells with the user data
                        echo '<tr>';
                        echo '<td data-title="' . esc_attr__( 'User ID', 'beyond-multisite' ) . '">' . $current_user_id . '</td>';
                        echo '<td data-title="' . esc_attr__( 'Username', 'beyond-multisite' ) . '">' . $username . '</td>';
                        echo '<td data-title="' . esc_attr__( 'IP', 'beyond-multisite' ) . '"><span class="be-mu-force-one-line">' . $country_flag_html
                            . '<span id="be-mu-ban-ip-value-' . $current_user_id . '" class="be-mu-ban-ip-in-table-span">'
                            . esc_html( $results['ip'] ) . '</span></span></td>';
                        echo '<td data-title="' . esc_attr__( 'Ban Period', 'beyond-multisite' ) . '">'
                            . esc_html( be_mu_ban_translate_period( $results['period'] ) ) . '</td>';
                        echo '<td data-title="' . esc_attr__( 'Ban Date and Time', 'beyond-multisite' ) . '*">'
                            . esc_html( be_mu_unixtime_to_wp_datetime( intval( $results['unix_time_banned'] ) ) ) . '</td>';
                        echo '<td data-title="' . esc_attr__( 'User Deleted', 'beyond-multisite' ) . '">' . $user_deleted . '</td>';
                        echo '<td data-title="' . esc_attr__( 'Actions', 'beyond-multisite' ) . '"><a class="be-mu-green-link" href="'
                            . esc_url( network_admin_url( 'users.php?page=be_mu_banned_users' )
                            . '&action=unban&user=' . $current_user_id . '&from_banned=' . $page_number . $unban_link_search_string ) . '">'
                            . esc_html__( 'Unban', 'beyond-multisite' ) . '</a></td>';
                        echo '</tr>';
                    }

                    echo '</tbody>';

                    echo '<tfoot>'
                        . '<tr>'
                        . '<th>' . esc_html__( 'User ID', 'beyond-multisite' ) . '</th>'
                        . '<th>' . esc_html__( 'Username', 'beyond-multisite' ) . '</th>'
                        . '<th>' . esc_html__( 'IP', 'beyond-multisite' ) . '</th>'
                        . '<th>' . esc_html__( 'Ban Period', 'beyond-multisite' ) . '</th>'
                        . '<th>' . esc_html__( 'Ban Date and Time', 'beyond-multisite' ) . '*</th>'
                        . '<th>' . esc_html__( 'User Deleted', 'beyond-multisite' ) . '</th>'
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

                    // If there are more than $per_page banned users we will show this dropdown menu to choose a page number to display
                    if ( $total_banned_count > $per_page ) {
                        echo '<div class="be-mu-white-box be-mu-w100per">'
                            . '<label for="be-mu-banned-page-number">'
                            . esc_html__( 'Go to page:', 'beyond-multisite' )
                            . '</label> '
                            . '<select onchange="banUsersGoToBannedPage()" id="be-mu-banned-page-number" name="be-mu-banned-page-number" size="1">';

                        // We go through all the pages and display an option and we mark the current page as the selected option in the dropdown menu
                        for ( $i = 0; $i < $pages_count; $i++ ) {
                            if ( $page_number == ( $i + 1 ) )  {
                                $selected_string = 'selected="selected"';
                            } else {
                                $selected_string = '';
                            }

                            echo  '<option ' . $selected_string . ' value="' . esc_attr( $i + 1 ) . '">' . esc_html( $i + 1 ) . '</option>';
                        }

                        echo '</select>';

                        if ( $page_number > 1 ) {
                            echo '&nbsp;<span onclick="banUsersNextPreviousPage(' . intval( $page_number - 1 ) . ')" '
                                . 'class="dashicons dashicons-arrow-left-alt2" title="' . esc_attr__( 'Previous Page', 'beyond-multisite' ) . '"></span>';
                        }

                        if ( $pages_count > $page_number ) {
                            echo '&nbsp;<span onclick="banUsersNextPreviousPage(' . intval( $page_number + 1 ) . ')" '
                                . 'class="dashicons dashicons-arrow-right-alt2" title="' . esc_attr__( 'Next Page', 'beyond-multisite' ) . '"></span>';
                        }

                        echo '</div>';
                    }
                }
            }

        }
        ?>
    </div>
    <?php

}

/**
 * Display an error in the validation of the user signup form (if error is present).
 * We create such an error in the be_mu_check_banned_user_signup function if the ip is banned.
 * @param object $errors
 */
function be_mu_signup_user_form_ban( $errors ) {
    if ( $error_message = $errors->get_error_message( 'be_mu_ban_error_user_signup' ) ) {
    	echo '<p class="error be-mu-ban-signup-user-error-p">' . esc_html( $error_message ) . '</p>';
    }
}

/**
 * Checks if the ip is banned when a user signup attempt is made and adds an error if yes.
 * @param object $result
 * @return object
 */
function be_mu_check_banned_user_signup( $result ) {

    $ip = be_mu_get_visitor_ip();

    if ( false !== $ip && be_mu_is_ip_banned( $ip ) ) {
        $result['errors']->add( 'be_mu_ban_error_user_signup', esc_html__( 'Your IP is banned from signup.', 'beyond-multisite' ) );
    }
    return $result;
}

/**
 * Display an error in the validation of the blog signup form (if error is present).
 * We create such an error in the be_mu_check_banned_blog_signup function if the ip is banned.
 * @param object $errors
 */
function be_mu_signup_blog_form_ban( $errors ) {
    if ( $error_message = $errors->get_error_message( 'be_mu_ban_error_blog_signup' ) ) {
    	echo '<p class="error be-mu-ban-signup-blog-error-p">' . esc_html( $error_message ) . '</p>';
    }
}

/**
 * Checks if the ip is banned when a blog signup attempt is made and adds an error if yes.
 * @param object $content
 * @return object
 */
function be_mu_check_banned_blog_signup( $content ) {

    $ip = be_mu_get_visitor_ip();

    if ( false !== $ip && be_mu_is_ip_banned( $ip ) ) {
        $content['errors']->add( 'be_mu_ban_error_blog_signup', esc_html__( 'Your IP is banned from signup.', 'beyond-multisite' ) );
    }
    return $content;
}

/**
 * If the IP, from which the comment is sent, is banned - we show an error and stop everything
 * @param array $commentdata
 * @return array
 */
function be_mu_check_banned_comment( $commentdata ) {

    $ip = be_mu_get_visitor_ip();

    if ( false !== $ip && be_mu_is_ip_banned( $ip ) ) {
	    wp_die( '<p><strong>' .  esc_html__( 'ERROR', 'beyond-multisite' ) . '</strong>: '
            .  esc_html__( 'Your IP address is banned from comments.', 'beyond-multisite' ) . '</p><p></p><p><a href="javascript:history.back()">&laquo; '
            .  esc_html__( 'Go Back', 'beyond-multisite' ) .'</a></p>' );
    } else {
        return $commentdata;
    }
}

/**
 * On login checks if the user or the ip are banned and denies access if yes
 * @param object $user
 * @param string $username
 * @param string $password
 * @return object
 */
function be_mu_check_banned_login( $user, $username, $password ) {

    // If the username or password are not sent we do nothing (and return $user)
    // This way we avoid errors to be shown before the user clicks the button to log in
    if ( ! isset( $username ) || '' == $username || ! isset( $password ) || '' == $password ) {
        return $user;
    }

    // If there is a user with such a username or email, we check for ban
    if ( false !== ( $user_by_login = get_user_by( 'login', $username ) ) || false !== ( $user_by_email = get_user_by( 'email', $username ) ) ) {

        // We get the user id either based on the username or the email (whatever it is entered by the user)
        if ( false !== $user_by_login ) {
            $user_id = intval( $user_by_login->ID );
        } else {
            $user_id = intval( $user_by_email->ID );
        }

        // These variables will indicate if the user and the ip are banned (1) or not (0)
        $user_is_banned = $ip_is_banned = 0;

        // Check if the user is banned
        if ( be_mu_is_user_banned( $user_id ) ) {
            $user_is_banned = 1;
        }

        $ip = be_mu_get_visitor_ip();

        // Check if the ip is banned
        if ( false !== $ip && be_mu_is_ip_banned( $ip ) ) {
            $ip_is_banned = 1;
        }

        // Based on what the ban is affecting we create an appropriate error
        if ( $user_is_banned && $ip_is_banned ) {
            $user = new WP_Error( 'denied', '<strong>' . esc_html__( 'ERROR', 'beyond-multisite' )
                . '</strong>: ' . esc_html__( 'This user and your IP address are banned from login.', 'beyond-multisite' ) );
        } elseif ( $user_is_banned && ! $ip_is_banned ) {
            $user = new WP_Error( 'denied', '<strong>' . esc_html__( 'ERROR', 'beyond-multisite' )
                . '</strong>: ' . esc_html__( 'This user is banned from login.', 'beyond-multisite' ) );
        } elseif ( ! $user_is_banned && $ip_is_banned ) {
            $user = new WP_Error( 'denied', '<strong>' . esc_html__( 'ERROR', 'beyond-multisite' )
                . '</strong>: ' . esc_html__( 'Your IP address is banned from login.', 'beyond-multisite' ) );
        }

        // If either the user or the ip is banned we stop further authentication and return $user with an error that we added earlier
        if ( $user_is_banned || $ip_is_banned ) {
            remove_action( 'authenticate', 'wp_authenticate_username_password', 20 );
            remove_action( 'authenticate', 'wp_authenticate_email_password', 20 );
            return $user;
        }
    }

    // If the function makes to to here, then there is either no such user or the ip and user are not banned, so we return $user without an error added
    return $user;
}

// Schedules an event (if not already scheduled) to run once an hour to unban expired bans
function be_mu_add_cron_unban() {
    if ( ! wp_next_scheduled( 'be_mu_unban_hourly_event' ) ) {
	    wp_schedule_event( time(), 'hourly', 'be_mu_unban_hourly_event' );
    }
}

// Removes the scheduled event to unban expired bans
function be_mu_remove_cron_unban() {
	wp_clear_scheduled_hook( 'be_mu_unban_hourly_event' );
}

// Removes all expired bans
function be_mu_unban_expired() {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
    $db_table = $main_blog_prefix . 'be_mu_banned_users';

    // Get database data for bans that are not permanent
    $results_multi_array = $wpdb->get_results( "SELECT * FROM " . $db_table . " WHERE period != 'Permanent'", ARRAY_A );

    // If there are no banned users with a period different than Permanent - we return
    if ( empty( $results_multi_array ) ) {
        return;

    // If there are banned users with a period different than Permanent, we check them one by one and unban the expired bans
    } else {

        foreach( $results_multi_array as $results ) {

            $time_banned = intval( $results['unix_time_banned'] );

            // Based on the ban period we set the time when the ban should be removed
            if ( '90 days' == $results['period'] ) {
                $time_to_remove_ban = $time_banned + ( 90 * 24 * 3600 );
            } elseif ( '30 days' == $results['period'] ) {
                $time_to_remove_ban = $time_banned + ( 30 * 24 * 3600 );
            } else {
                $time_to_remove_ban = $time_banned + ( 7 * 24 * 3600 );
            }

            // If the time to remove the ban has come, we remove the ban
            if ( time() >= $time_to_remove_ban ) {
                be_mu_unban_user_and_ip( $results['user_id'] );
            }
        }
    }
}

/**
 * Returns unescaped translated string for the ban period based on the given variable value.
 * @param string $period
 * @return mixed
 */
function be_mu_ban_translate_period( $period ) {
    if( 'Permanent' == $period ) {
        return __( 'Permanent', 'beyond-multisite' );
    } elseif ( '90 days' == $period ) {
        return sprintf( __( '%d days', 'beyond-multisite' ), 90 );
    } elseif ( '30 days' == $period ) {
        return sprintf( __( '%d days', 'beyond-multisite' ), 30 );
    } elseif ( '7 days' == $period ) {
        return sprintf( __( '%d days', 'beyond-multisite' ), 7 );
    } else {
        return false;
    }
}

/**
 * Gets the country code of an IP address, using a free API.
 * @param string $ip
 * @return mixed
 */
function be_mu_ban_get_country_code_of_ip( $ip ) {

    // We proceed if CURL is enabled for the server and the IP is valid
    if ( be_mu_is_curl_enabled() && be_mu_is_valid_ip( $ip ) ) {

        // This is the URL of a free API that allows 1500 requests per day
        $url = "https://www.iplocate.io/api/lookup/" . urlencode( $ip );

        // We set some limits for how long to try to connect to the URL before giving up
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 5
        );

        // We use CURL to get data from the API URL
        $curl = curl_init( $url );
        curl_setopt_array( $curl, $options );
        $content = curl_exec( $curl );

        if ( curl_errno( $curl ) ) {

            // We can get the error with curl_error( $curl ) but for now we just return false
            return false;
        }

        curl_close( $curl );

        // We decode the results
        $result = json_decode( $content, true);

        // If the data is not as we expect it, we return false
        if ( ! is_array( $result ) || ! isset( $result['country_code'] ) || empty( $result['country_code'] ) ) {
            return false;
        }

        // We return the sanitized country code
        return sanitize_html_class( $result['country_code'] );
    }

    return false;
}

/**
 * Returns the HTML code that shows the country flag based on the user id and IP address
 * @param int $user_id
 * @param string $ip
 * @return string
 */
function be_mu_ban_get_country_flag_html( $user_id, $ip ) {

    if ( be_mu_get_setting( 'be-mu-ban-show-flags') == 'on' ) {

        // We need these to connect to the database
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        global $wpdb;
        $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
        $db_table = $main_blog_prefix . 'be_mu_user_ips';

        // Get database data for the country code for the selected user
        $results = $wpdb->get_row( $wpdb->prepare( 'SELECT country_code, country_code_ip FROM ' . $db_table . ' WHERE user_id = %d', $user_id ), ARRAY_A );

        /**
         * If the IP address for which we have a country code is different than the current IP address (or we do not even have data for the ip)
         * we show html code that triggers a script to get the country code and update it after the page loads.
         */
        if ( $results['country_code_ip'] != $ip ) {

            $country_flag_html = '<span class="be-mu-tooltip be-mu-ban-flags-tooltip">'
                . '<span class="be-mu-ban-pending-flag" id="be-mu-ban-ip-flag-' . intval( $user_id ) . '"></span>'
                . '<span class="be-mu-tooltip-text" id="be-mu-ban-ip-country-tooltip-' . intval( $user_id ) . '"></span>'
                . '</span>';

        // If we have the country code for the correct IP address we will display the country image and text.
        } else {

            // We remove some things from the string for security reasons
            $country_code = sanitize_html_class( $results['country_code'] );

            // We get the array with all the country names and then set the $country variable based on the country code
            $countries = be_mu_ban_get_countries_array();
            if ( isset( $countries[ $country_code ] ) ) {
                $country = $countries[ $country_code ];
            } else {
                $country = __( 'Error', 'beyond-multisite' );
            }

            // We remove some things from the string for security reasons
            $country = wp_strip_all_tags( $country );

            $country_flag_html = '<span class="be-mu-tooltip be-mu-ban-flags-tooltip">'
                . '<span class="flag flag-' . strtolower( esc_attr( $country_code ) ) . '" id="be-mu-ban-ip-flag-' . intval( $user_id ) . '"></span>'
                . '<span class="be-mu-tooltip-text" id="be-mu-ban-ip-country-tooltip-' . intval( $user_id ) . '">' . esc_html( $country ) . '</span>'
                . '</span>';
        }

    } else {
        $country_flag_html = '';
    }

    return $country_flag_html;
}
