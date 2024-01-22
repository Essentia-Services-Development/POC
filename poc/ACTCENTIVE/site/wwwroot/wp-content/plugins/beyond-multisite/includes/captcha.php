<?php

/**
 * In this file we have all the hooks and functions related to the captcha module
 * We have functions that create the captcha, that put it on different forms, that validate if the answer given by users is correct and so on
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// We get all the settings we will need in this array
$GLOBALS['be_mu_captcha_settings'] = be_mu_get_settings( array( 'be-mu-captcha-status-module', 'be-mu-captcha-login', 'be-mu-captcha-lost-password',
    'be-mu-captcha-reset-password', 'be-mu-captcha-user-signup', 'be-mu-captcha-blog-signup-logged-out', 'be-mu-captcha-blog-signup-logged-in',
    'be-mu-captcha-comment-logged-in', 'be-mu-captcha-comment-logged-out' ) );

// All the hooks for the captcha module (except the one for the preview) will run only if the module is turned on
if ( 'on' == $GLOBALS['be_mu_captcha_settings']['be-mu-captcha-status-module'] ) {

    /**
     * These lines add the captcha to the different forms: new blog signup, new user signup, login, lost password, reset password, and comment.
     * The number 9999999999 is the priority and it is high so the captcha shows after any other things that may be added from other plugins.
     */
    add_action( 'signup_blogform', 'be_mu_signup_blog_form_captcha', 9999999999 );
    add_action( 'signup_extra_fields', 'be_mu_signup_user_form_captcha', 9999999999 );
    add_action( 'login_form', 'be_mu_login_form_captcha', 9999999999 );
    add_action( 'lostpassword_form', 'be_mu_lost_pass_form_captcha', 9999999999 );
    add_action( 'resetpass_form', 'be_mu_reset_pass_form_captcha', 9999999999 );
    add_filter( 'comment_form_submit_button', 'be_mu_comment_form_captcha', 9999999999, 2 );

    /*
     * We add the captcha to the registration form made by the WP Ultimo plugin on the step a domain is chosen.
     * But it is validated in the function be_mu_validate_blog_signup as well. Tested on WP Ultimo 1.10.11.
     */
    add_action( 'wp_ultimo_registration_step_domain', 'be_mu_captcha_signup_wp_ultimo' );

    // These lines validate the captcha on all the forms we mentioned above
    add_filter( 'wpmu_validate_blog_signup', 'be_mu_validate_blog_signup' );
    add_filter( 'wpmu_validate_user_signup', 'be_mu_validate_user_signup' );
    add_filter( 'authenticate', 'be_mu_validate_login_form', 10, 3 );
    add_action( 'lostpassword_post', 'be_mu_validate_lost_pass_form' );
    add_action( 'validate_password_reset', 'be_mu_validate_reset_pass_form', 10, 2 );
    add_action( 'preprocess_comment', 'be_mu_validate_comment_form' );

    // If the captcha is enabled for at least one of the forms on the login page, we add the captcha style file to the page
    if ( 'on' == $GLOBALS['be_mu_captcha_settings']['be-mu-captcha-login'] || 'on' == $GLOBALS['be_mu_captcha_settings']['be-mu-captcha-lost-password']
        || 'on' == $GLOBALS['be_mu_captcha_settings']['be-mu-captcha-reset-password'] ) {
        add_action( 'login_enqueue_scripts', 'be_mu_register_captcha_style' );
    }

    // If the captcha is enabled for at least one of the forms on the signup page, we add the captcha style file to the page
    if ( 'on' == $GLOBALS['be_mu_captcha_settings']['be-mu-captcha-user-signup']
        || 'on' == $GLOBALS['be_mu_captcha_settings']['be-mu-captcha-blog-signup-logged-out']
        || 'on' == $GLOBALS['be_mu_captcha_settings']['be-mu-captcha-blog-signup-logged-in'] ) {
        add_action( 'wp_enqueue_scripts', 'be_mu_register_captcha_signup_style' );
    }

    // If the captcha is enabled for the comment form, we add the captcha style file to posts and pages
    if ( 'on' == $GLOBALS['be_mu_captcha_settings']['be-mu-captcha-comment-logged-in']
        || 'on' == $GLOBALS['be_mu_captcha_settings']['be-mu-captcha-comment-logged-in'] ) {
        add_action( 'wp_enqueue_scripts', 'be_mu_register_captcha_comment_style' );
    }
}

// This is used to update the captcha preview in the settings. It will work even if the module is disabled.
add_action( 'wp_ajax_be_mu_update_captcha_preview_action', 'be_mu_update_captcha_preview_action_callback' );

// Adds the captcha style to the signup page
function be_mu_register_captcha_signup_style() {
    if ( 'wp-signup.php' === $GLOBALS['pagenow'] || '/wp-signup.php' === $_SERVER['PHP_SELF'] ) {
        be_mu_register_captcha_style();
    }
}

// Adds the captcha style to pages and posts with open comments
function be_mu_register_captcha_comment_style() {
    if ( ( is_page() || is_single() ) && comments_open() ) {
        be_mu_register_captcha_style();
    }
}

// Registers the captcha style file and enqueues it
function be_mu_register_captcha_style() {
    wp_register_style( 'be-mu-captcha-style', be_mu_plugin_dir_url() . 'styles/captcha.css', false, BEYOND_MULTISITE_VERSION );
    wp_enqueue_style( 'be-mu-captcha-style' );
}

/**
 * Returns the path to the folder where the captcha images are storred
 * @return string
 */
function be_mu_get_captcha_folder_path() {
    if ( be_mu_get_setting( 'be-mu-captcha-images-folder' ) === 'Plugin folder' ) {
        return rtrim( be_mu_plugin_dir_path(), '/' ) . '/be_mu_captcha/';
    } else {

        // This is an array with information about the uploads directory for the current site
        $upload_dir = wp_upload_dir();

        /*
         * A sidenote: the be_mu_captcha folder is not one global folder for the network - it will be created in the uploads folder
         * of each subsite that uses the captcha
         */
        return $upload_dir['basedir'] . '/be_mu_captcha/';
    }
}

/**
 * Returns the url to the folder where the captcha images are storred
 * @return string
 */
function be_mu_get_captcha_folder_url() {
    if ( be_mu_get_setting( 'be-mu-captcha-images-folder' ) === 'Plugin folder' ) {
        return rtrim( be_mu_plugin_dir_url(), '/' ) . '/be_mu_captcha/';
    } else {

        // This is an array with information about the uploads directory for the current site
        $upload_dir = wp_upload_dir();

        /*
         * A sidenote: the be_mu_captcha folder is not one global folder for the network - it will be created in the uploads folder
         * of each subsite that uses the captcha
         */
        return $upload_dir['baseurl'] . '/be_mu_captcha/';
    }
}

/**
 * Makes an rgb array from a hex color value; example: #ffffff => Array(255,255,255)
 * @param string $hex
 * @return array
 */
function be_mu_hex_to_rgb( $hex ) {

    // If it is a short hex, turn it into regular; example: #fff => #ffffff
    if ( 4 == strlen( $hex ) ) {
        $hex =  $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2] . $hex[3] . $hex[3];
    }

    list( $r, $g, $b ) = sscanf( $hex, '#%02x%02x%02x' );
    return array( $r, $g, $b );
}

/**
 * Chooses at random a set of predefined colors to be used for the captcha
 * @return array
 */
function be_mu_get_rand_captcha_colors() {

    // A multi-array with tested color sets of two that produce readable results after treatment with the imageconvolution function
    $colors = Array(
        Array( '#3bb', '#555' ),
        Array( '#3bb', '#55b' ),
        Array( '#3bb', '#b55' ),
        Array( '#3bb', '#bb5' ),
        Array( '#3bb', '#b5b' ),
        Array( '#bb3', '#555' ),
        Array( '#bb3', '#55b' ),
        Array( '#bb3', '#b55' ),
        Array( '#bb3', '#b5b' ),
        Array( '#33b', '#555' ),
        Array( '#33b', '#b55' ),
        Array( '#33b', '#bb5' ),
        Array( '#b33', '#555' ),
        Array( '#b33', '#55b' ),
        Array( '#b3b', '#bb5' ),
        Array( '#b3b', '#555' ),
        Array( '#b3b', '#55b' ),
        Array( '#b3b', '#b55' ),
    );

    // We choose a random array index
    $index = mt_rand( 0, ( count( $colors ) - 1 ) );

    // We return the chosen color set
    return Array(
        'bg_color' => $colors[ $index ][0],
        'text_color' => $colors[ $index ][1],
    );
}

/**
 * Returns a label for the answer field based on the current setting for the character set
 * @return string
 */
function be_mu_get_answer_label() {
    if ( be_mu_get_setting( 'be-mu-captcha-character-set' ) == 'Numbers' ) {
        return esc_html__( 'Security Number:', 'beyond-multisite' );
    } else {
        return esc_html__( 'Security Text:', 'beyond-multisite' );
    }
}

/**
 * Returns an error message for empty answer field based on the current setting for the character set
 * @return string
 */
function be_mu_get_empty_answer_error() {
    if ( be_mu_get_setting( 'be-mu-captcha-character-set' ) == 'Numbers' ) {
        return esc_html__( 'Please enter the number from the security image.', 'beyond-multisite' );
    } else {
        return esc_html__( 'Please enter the text from the security image.', 'beyond-multisite' );
    }
}

/**
 * Returns an error message for wrong answer field based on the current setting for the character set
 * @return string
 */
function be_mu_get_wrong_answer_error() {
    if ( be_mu_get_setting( 'be-mu-captcha-character-set' ) == 'Numbers' ) {
        return esc_html__( 'This is not the correct security number.', 'beyond-multisite' );
    } else {
        return esc_html__( 'This is not the correct security text.', 'beyond-multisite' );
    }
}

/**
 * Create the captcha image file
 * @param int $image_height
 * @param string $answer
 * @param string $request_id
 * @return bool
 */
function be_mu_captcha_image( $image_height, $answer, $request_id ) {

    // An array with settings about the font
    $font =  Array(
        'path' => be_mu_plugin_dir_path() . 'fonts/circulat/circulat.ttf',
        'height' => '0.7',
        'padding' => '1.07',
        'valign' => '5',
        'valign_numbers' => '7',
    );

    // Calculate font size and image width
    $font_size = round( $image_height * floatval( $font['height'] ) );
    $image_width = round( ( $font_size * strlen( $answer ) ) * floatval( $font['padding'] ) );

    // Get two colors for the captcha
    $colors = be_mu_get_rand_captcha_colors();
    $bg_color = $colors['bg_color'];
    $text_color = $colors['text_color'];

    // Convert the colors to rgb
    $rgb_bg_color = be_mu_hex_to_rgb( $bg_color );
    $rgb_text_color = be_mu_hex_to_rgb( $text_color );

    if ( ! function_exists( 'imagecreate' ) || ! function_exists( 'imagecolorallocate' ) || ! function_exists( 'imagettfbbox' )
        || ! function_exists( 'imagettftext' ) || ! function_exists( 'imagepng' ) || ! function_exists( 'imagedestroy' ) ) {
        return false;
    }

    // Create an image
    if ( ! $image = @imagecreate( $image_width, $image_height ) ) {
        return false;
    }

    // Set the image background color
    // The check for error is a little different here, because the function imagecolorallocate can return a non boolean false
    if ( false === imagecolorallocate( $image, $rgb_bg_color[0], $rgb_bg_color[1], $rgb_bg_color[2] ) ) {
        return false;
    }

    // Set the image text color
    $image_text_color = imagecolorallocate( $image, $rgb_text_color[0], $rgb_text_color[1], $rgb_text_color[2] );

    // The check for error is a little different here, because the function imagecolorallocate can return a non boolean false
    if ( false === $image_text_color ) {
        return false;
    }

    // Create textbox and add the text to the image
    if ( ! $text_box = imagettfbbox( $font_size, 0, $font['path'], $answer ) ) {
        return false;
    }

    // Calculate text width and height
    $text_width = $text_box[2] - $text_box[0];
    $text_height = $text_box[7] - $text_box[1];

    // Based on whether the answer contains only digits or not, we use a different vertical align value to better position the text in the middle
    if ( is_numeric( $answer ) ) {
        $valign = $font['valign_numbers'];
    } else {
        $valign = $font['valign'];
    }

    // Calculate coordinates to use so the text is placed in the center of the image
    $x = ceil( ( $image_width - $text_width ) / 2 );
    $y = ceil( ( $image_height - $text_height ) / 2 ) - round( $image_height / $valign );

    // Put the text in the image
    if ( ! imagettftext( $image, $font_size, 0, $x, $y, $image_text_color, $font['path'], $answer ) ) {
        return false;
    }

    // Creates an awesome effect that makes the image very hard to read by captcha breaking software
    $kernel = array(
        array( 4, 4, -4 ),
        array( 4, 4, -4 ),
        array( 4, -4, -4 )
    );
    if ( ! imageconvolution( $image, $kernel, 1, 0 ) ) {
        return false;
    }

    // Set the path to the captcha images folder
    $captchas_path = be_mu_get_captcha_folder_path();

    // If the folder for the captchas does not exist, create it
    if ( ! file_exists( $captchas_path ) ) {
        mkdir( $captchas_path, 0755, true );
    }

    // If we do not have permission to write in the folder, return an error
    if ( ! is_writable( $captchas_path ) ) {
        return false;
    }

    // Sanitize the request id just in case
    $request_id = preg_replace( '/[^a-z0-9]/', '', $request_id );

    // Set the full file path of the captcha image file we will create
    $captcha_file_path = $captchas_path . $request_id . '.png';

    // Create the actual captcha image file
    if ( ! imagepng( $image, $captcha_file_path ) ) {
        return false;
    }

    // Destroy the image from the memory, since we already have it in a file
    if ( ! imagedestroy( $image ) ) {
        return false;
    }

    // Success
    return true;
}

/**
 * Make a new captcha
 * @return array
 */
function be_mu_make_captcha() {

    // Generate a random request id that is 40 characters long
    $request_id = be_mu_random_string( 40 );

    // Get the plugin settings for the captcha
    $height = intval( be_mu_get_setting( 'be-mu-captcha-height', 80 ) );
    $characters = intval( be_mu_get_setting( 'be-mu-captcha-characters', 3 ) );
    $character_set = be_mu_get_setting( 'be-mu-captcha-character-set', 'Numbers' );

    // Set the variable with the characters to exclude from the set, based on the setting (we also exclude some misleading or hard to read characters)
    if ( 'Numbers' == $character_set ) {
        $exclude = 'abcdefghijklmnopqrstuvwxyz';
    } elseif ( 'Letters' == $character_set ) {
        $exclude = '0olge312456789';
    } else {
        $exclude = '0olge3';
    }

    // Generate a random answer with a number of characters defined in the plugin settings
    $answer = be_mu_random_string( $characters, $exclude );

    // We need these to connect to the database
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
    $db_table = $main_blog_prefix . 'be_mu_captcha';

    // Insert the captcha data to the database so we can later compare the answer from the user to the correct answer
    $status = $wpdb->insert(
    	$db_table,
    	array(
    		'request_id' => $request_id,
    		'answer' => $answer,
    		'unix_time_added' => time(),
    	),
    	array(
    		'%s',
    		'%s',
    		'%d',
    	)
    );

    // At this point we will do some cleanup of old data
    // First we delete captcha data from the database that is older than an hour
    $before_one_hours = time() - 3600;
    $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $db_table . ' WHERE unix_time_added < %d', $before_one_hours ) );

    // Set the path to the captcha images folder
    $captchas_path = be_mu_get_captcha_folder_path();

    // Set the url to the captcha images folder
    $captchas_url = be_mu_get_captcha_folder_url();

    // If the directory exists, we scan all files inside and delete all older than 3 minutes
    if ( is_dir( $captchas_path ) ) {
        $files = array_diff( scandir( $captchas_path ), array( '.', '..' ) );
        foreach ( $files as $file ) {
            if ( file_exists( $captchas_path . $file ) && filemtime( $captchas_path . $file ) < ( time() - 180 ) ) {
                unlink( $captchas_path . $file );
            }
        }
    }

    // We couldn't insert the data to the database, so we return false
    if ( intval( $status ) !== 1 ) {
        return false;
    }

    // Create the captcha image file
    be_mu_captcha_image( $height, $answer, $request_id );

    // At the end we return the requst id and captcha url, so we can use then when we display the captcha in a form
    return Array(
        'request_id' => $request_id,
        'captchas_url' => $captchas_url,
    );
}

/**
 * Make a new text captcha
 * @return array
 */
function be_mu_make_text_captcha() {

    // Generate a random request id that is 40 characters long
    $request_id = be_mu_random_string( 40 );

    // Get the plugin settings for the captcha
    $characters = intval( be_mu_get_setting( 'be-mu-captcha-characters', 3 ) );
    $character_set = be_mu_get_setting( 'be-mu-captcha-character-set', 'Numbers' );

    // Set the variable with the characters to exclude from the set, based on the setting (we also exclude some misleading or hard to read characters)
    if ( 'Numbers' == $character_set ) {
        $exclude = 'abcdefghijklmnopqrstuvwxyz';
    } elseif ( 'Letters' == $character_set ) {
        $exclude = '0olge312456789';
    } else {
        $exclude = '0olge3';
    }

    // Generate a random answer with a number of characters defined in the plugin settings
    $answer = be_mu_random_string( $characters, $exclude );

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );
    $db_table = $main_blog_prefix . 'be_mu_captcha';

    // Insert the captcha data to the database so we can later compare the answer from the user to the correct answer
    $wpdb->insert(
    	$db_table,
    	array(
    		'request_id' => $request_id,
    		'answer' => $answer,
    		'unix_time_added' => time(),
    	),
    	array(
    		'%s',
    		'%s',
    		'%d',
    	)
    );

    // We delete captcha data from the database that is older than an hour
    $before_one_hours = time() - 3600;
    $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $db_table . ' WHERE unix_time_added < %d', $before_one_hours ) );

    // At the end we return the data we need to display the captcha in a form
    return Array(
        'request_id' => $request_id,
        'answer' => $answer,
    );
}

// Creates a database table to store the captcha information
function be_mu_create_captcha_db_table() {

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // This is the query that will create the database table if it does not exist already
    $sql = 'CREATE TABLE IF NOT EXISTS ' . $main_blog_prefix . 'be_mu_captcha ( '
        . 'row_id bigint( 20 ) NOT NULL AUTO_INCREMENT, '
        . 'request_id varchar( 40 ) DEFAULT NULL, '
        . 'answer varchar( 40 ) DEFAULT NULL, '
        . 'unix_time_added int( 11 ) NOT NULL, '
        . 'PRIMARY KEY ( row_id ) '
        . ') ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;';

    // Execute the query
    dbDelta( $sql );
}

/**
 * Adds the captcha to the blog signup form
 * @param object $errors
 * @return mixed
 */
function be_mu_signup_blog_form_captcha( $errors ) {

    // Based on the plugin settings and the user being logged in or out
    // We may decide to not show a captcha and just return true
    if ( ( is_user_logged_in() && be_mu_get_setting( 'be-mu-captcha-blog-signup-logged-in' ) != 'on' ) ||
        ( ! is_user_logged_in() && be_mu_get_setting( 'be-mu-captcha-blog-signup-logged-out' ) != 'on' ) ) {
        return true;
    }

    // Make a new captcha
    $captcha = be_mu_make_captcha();

    if ( ! is_array( $captcha ) ) {
        echo '<p class="be-mu-captcha-signup-blog-image-p">'
            . '<label>' . esc_html__( 'Security Image:', 'beyond-multisite' ) . '</label><br>'
            . esc_html__( 'Error: Could not save the answer to the database.', 'beyond-multisite' )
            . '<input name="be-mu-captcha-request-blog-signup" type="hidden" value="error" />'
            . '</p>';
    } else {

        // Display the captcha image (security image) and hidden request id field in the form
        echo '<p class="be-mu-captcha-signup-blog-image-p">'
            . '<label>' . esc_html__( 'Security Image:', 'beyond-multisite' ) . '</label> '
            . '<img class="be-mu-captcha-image" src="' . esc_url( $captcha['captchas_url'] . $captcha['request_id'] . '.png' ). '" /> '
            . '<input name="be-mu-captcha-request-blog-signup" type="hidden" value="' . esc_attr( $captcha['request_id'] ) . '" />'
            . '</p>';
    }

    // Display an error in the validation of the form if any
    // We create such errors in the be_mu_validate_blog_signup function if the captcha answer is incorrect
    if ( $error_message = $errors->get_error_message( 'be_mu_captcha_error_blog_signup' ) ) {
    	echo '<p class="error be-mu-captcha-signup-blog-error-p">' . esc_html( $error_message ) . '</p>';
    }

    // Display the text field where users will enter the captcha answer (security text)
    echo '<p class="be-mu-captcha-signup-blog-answer-p">'
        . '<label for="be-mu-captcha-answer-blog-signup">' . esc_html( be_mu_get_answer_label() ) . '</label> '
        . '<input id="be-mu-captcha-answer-blog-signup" autocomplete="off" name="be-mu-captcha-answer-blog-signup" size="9" type="text" />'
        . '</p>';
}

/**
 * Validate the blog signup form
 * @param object $content
 * @return object
 */
function be_mu_validate_blog_signup( $content ) {

    // Based on the plugin settings and the user being logged in or out
    // We may decide to not require a captcha answer and just return the $content array unchanged
    if ( ( is_user_logged_in() && be_mu_get_setting( 'be-mu-captcha-blog-signup-logged-in' ) != 'on' ) ||
        ( ! is_user_logged_in() && be_mu_get_setting( 'be-mu-captcha-blog-signup-logged-out' ) != 'on' ) ) {
        return $content;
    }

    // Check if the request and captcha fields are not empty
    if ( ! isset( $_POST['be-mu-captcha-request-blog-signup'] ) ) {
        $content['errors']->add( 'be_mu_captcha_error_blog_signup', esc_html__( 'An unexpected error occurred.', 'beyond-multisite' ) );
        return $content;
    }
    if ( ! isset( $_POST['be-mu-captcha-answer-blog-signup'] ) || '' == $_POST['be-mu-captcha-answer-blog-signup'] ) {
        $content['errors']->add( 'be_mu_captcha_error_blog_signup', be_mu_get_empty_answer_error() );
        return $content;
    }

    // Get the request id from the hidden field and the answer the user sent us
    $request_id = $_POST['be-mu-captcha-request-blog-signup'];
    $answer = strtolower( $_POST['be-mu-captcha-answer-blog-signup'] );

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // Get database data for the current request
    $results = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $main_blog_prefix . 'be_mu_captcha WHERE request_id = %s', $request_id ), ARRAY_A );

    // If there is no data, then the request is invalid or has expired and has beed deleted while cleaning up
    if ( null === $results ) {
        $content['errors']->add( 'be_mu_captcha_error_blog_signup', esc_html__( 'Invalid or expired request.', 'beyond-multisite' ) );
        return $content;
    }

    // If the submitted answer is different from the one in the database for this request id, generate an error
    // And also delete this request from the database, so it cannot be guessed multiple times
    if ( $results['answer'] != $answer ) {
        $content['errors']->add( 'be_mu_captcha_error_blog_signup', be_mu_get_wrong_answer_error() );
        $wpdb->delete( $main_blog_prefix . 'be_mu_captcha', array( 'request_id' => $request_id ), array( '%s' ) );
        return $content;
    }

    // Delete the database data for this request and answer so they cannot be used again
    $wpdb->delete( $main_blog_prefix . 'be_mu_captcha', array( 'request_id' => $request_id, 'answer' => $answer ), array( '%s', '%s' ) );

    // The answer is correct so we just return the $content array unchanged
    return $content;
}

/**
 * Adds the captcha to the user signup form
 * @param object $errors
 * @return mixed
 */
function be_mu_signup_user_form_captcha( $errors ) {

    // Based on the plugin settings we may decide to not show a captcha and just return true
    if ( be_mu_get_setting( 'be-mu-captcha-user-signup' ) != 'on' ) {
        return true;
    }

    // Make a new captcha
    $captcha = be_mu_make_captcha();

    if ( ! is_array( $captcha ) ) {
        echo '<p class="be-mu-captcha-signup-image-p">'
            . '<label>' . esc_html__( 'Security Image:', 'beyond-multisite' ) . '</label><br>'
            . esc_html__( 'Error: Could not save the answer to the database.', 'beyond-multisite' )
            . '<input name="be-mu-captcha-request" type="hidden" value="error" />'
            . '</p>';
    } else {

        // Display the captcha image (security image) and hidden request id field in the form
        echo '<p class="be-mu-captcha-signup-image-p">'
            . '<label>' . esc_html__( 'Security Image:', 'beyond-multisite' ) . '</label> '
            . '<img class="be-mu-captcha-image" src="' . esc_url( $captcha['captchas_url'] . $captcha['request_id'] . '.png' ) . '" /> '
            . '<input name="be-mu-captcha-request" type="hidden" value="' . esc_attr( $captcha['request_id'] ) . '" />'
            . '</p>';

    }

    // Display an error in the validation of the form if any
    // We create such errors in the be_mu_validate_user_registration function if the captcha answer is incorrect
    if ( $error_message = $errors->get_error_message( 'be_mu_captcha_error_message' ) ) {
    	echo '<p class="error be-mu-captcha-signup-error-p">' . esc_html( $error_message ) . '</p>';
    }

    // Display the text field where users will enter the captcha answer (security text)
    echo '<p class="be-mu-captcha-signup-answer-p">'
        . '<label for="be-mu-captcha-answer">' . esc_html( be_mu_get_answer_label() ) . '</label>'
        . '<input id="be-mu-captcha-answer" autocomplete="off" name="be-mu-captcha-answer" size="9" type="text" />'
        . '</p>';
}

/**
 * Validates the user signup
 * @param object $result
 * @return object
 */
function be_mu_validate_user_signup( $result ) {

    // Based on the plugin settings we may decide to not require a captcha answer and just return the $result array unchanged
    if ( be_mu_get_setting( 'be-mu-captcha-user-signup' ) != 'on' ) {
        return $result;
    }

    // Since the filter we are using runs at both user and blog signup we will force it to run only on user signup by checking the stage value
    if ( ! isset( $_POST['stage'] ) || 'validate-user-signup' != $_POST['stage'] ) {
        return $result;
    }

    // Check if the request and captcha fields are not empty
    if ( ! isset( $_POST['be-mu-captcha-request'] ) ) {
        $result['errors']->add( 'be_mu_captcha_error_message', esc_html__( 'An unexpected error occurred.', 'beyond-multisite' ) );
        return $result;
    }

    if ( ! isset( $_POST['be-mu-captcha-answer'] ) || '' == $_POST['be-mu-captcha-answer'] ) {
        $result['errors']->add( 'be_mu_captcha_error_message', be_mu_get_empty_answer_error() );
        return $result;
    }

    // Get the request id from the hidden field and the answer the user sent us
    $request_id = $_POST['be-mu-captcha-request'];
    $answer = strtolower( $_POST['be-mu-captcha-answer'] );

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // Get database data for the current request
    $results = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $main_blog_prefix . 'be_mu_captcha WHERE request_id = %s', $request_id ), ARRAY_A );

    // If there is no data, then the request is invalid or has expired and has beed deleted while cleaning up
    if ( null === $results ) {
        $result['errors']->add( 'be_mu_captcha_error_message', esc_html__( 'Invalid or expired request.', 'beyond-multisite' ) );
        return $result;
    }

    // If the submitted answer is different from the one in the database for this request id, generate an error
    // And also delete this request from the database, so it cannot be guessed multiple times
    if ( $results['answer'] != $answer ) {
        $result['errors']->add( 'be_mu_captcha_error_message', be_mu_get_wrong_answer_error() );
        $wpdb->delete( $main_blog_prefix . 'be_mu_captcha', array( 'request_id' => $request_id ), array( '%s' ) );
        return $result;
    }

    // Delete the database data for this request and answer so they cannot be used again
    $wpdb->delete( $main_blog_prefix . 'be_mu_captcha', array( 'request_id' => $request_id, 'answer' => $answer ), array( '%s', '%s' ) );

    // The answer is correct so we just return the $result array unchanged
    return $result;
}

/**
 * Adds the captcha to the login form
 * @return mixed
 */
function be_mu_login_form_captcha() {

    // Based on the plugin settings we may decide to not show a captcha and just return true
    if ( be_mu_get_setting( 'be-mu-captcha-login' ) != 'on' ) {
        return true;
    }

    if ( be_mu_get_setting( 'be-mu-captcha-text-login' ) === 'on' ) {

        // Make a new text captcha
        $captcha = be_mu_make_text_captcha();

        if ( ! is_array( $captcha ) ) {
            echo '<p class="be-mu-captcha-login-image-p">'
                . '<label>' . esc_html__( 'Security Image:', 'beyond-multisite' ) . '</label><br>'
                . esc_html__( 'Error: Could not save the answer to the database.', 'beyond-multisite' )
                . '<input name="be-mu-captcha-request" type="hidden" value="error" />'
                . '</p>';
        } else {

            // Display the captcha and hidden request id field in the form
            echo '<p class="be-mu-captcha-login-image-p">'
                . '<label>' . esc_html__( 'Security Code:', 'beyond-multisite' ) . '</label> '
                . '<div class="be-mu-captcha-login-text-code">' . esc_html( $captcha['answer'] ) . '</div>'
                . '<input name="be-mu-captcha-request" type="hidden" value="' . esc_attr( $captcha['request_id'] ) . '" />'
                . '</p>';
        }

    } else {

        // Make a new image captcha
        $captcha = be_mu_make_captcha();

        if ( ! is_array( $captcha ) ) {
            echo '<p class="be-mu-captcha-login-image-p">'
                . '<label>' . esc_html__( 'Security Image:', 'beyond-multisite' ) . '</label><br>'
                . esc_html__( 'Error: Could not save the answer to the database.', 'beyond-multisite' )
                . '<input name="be-mu-captcha-request" type="hidden" value="error" />'
                . '</p>';
        } else {

            // Display the captcha image (security image) and hidden request id field in the form
            echo '<p class="be-mu-captcha-login-image-p">'
                . '<label>' . esc_html__( 'Security Image:', 'beyond-multisite' ) . '</label> '
                . '<img class="be-mu-captcha-image" src="' . esc_url( $captcha['captchas_url'] . $captcha['request_id'] . '.png' ) . '" /> '
                . '<input name="be-mu-captcha-request" type="hidden" value="' . esc_attr( $captcha['request_id'] ) . '" />'
                . '</p>';
        }

    }

    // Display the text field where users will enter the captcha answer (security text)
    echo '<p class="be-mu-captcha-login-answer-p">'
        . '<label for="be-mu-captcha-answer">' . esc_html( be_mu_get_answer_label() ) . '</label>'
        . '<input id="be-mu-captcha-answer" autocomplete="off" name="be-mu-captcha-answer" size="9" type="text" />'
        . '</p>';
}

/**
 * Validates the login captcha before wordpress checks the user and pass
 * @param object $user
 * @param string $username
 * @param string $password
 * @return object
 */
function be_mu_validate_login_form( $user, $username, $password ) {

    // Based on the plugin settings we may decide to not require a captcha answer and just return $user to let wordpress check the username and password
    if ( be_mu_get_setting( 'be-mu-captcha-login' ) != 'on' ) {
        return $user;
    }

    // If the username or password are not sent we also do nothing (and return $user)
    // This way we avoid errors to be shown before the user clicks the button to log in
    if ( ! isset( $username ) || '' == $username || ! isset( $password ) || '' == $password ) {
        return $user;
    }

    // Check if the request and captcha fields are empty or not set and show an error and cancel further processing if they are
    if ( ! isset( $_POST['be-mu-captcha-request'] ) || '' == $_POST['be-mu-captcha-request'] ) {
        remove_action( 'authenticate', 'wp_authenticate_username_password', 20 );
        remove_action( 'authenticate', 'wp_authenticate_email_password', 20 );
        $user = new WP_Error( 'denied', '<strong>' . esc_html__( 'ERROR', 'beyond-multisite' ) . '</strong>: '
            . esc_html__( 'An unexpected error occurred.', 'beyond-multisite' ) );
        return $user;
    }

    if ( ! isset( $_POST['be-mu-captcha-answer'] ) || '' == $_POST['be-mu-captcha-answer'] ) {
        remove_action( 'authenticate', 'wp_authenticate_username_password', 20 );
        remove_action( 'authenticate', 'wp_authenticate_email_password', 20 );
        $user = new WP_Error( 'denied', '<strong>' . esc_html__( 'ERROR', 'beyond-multisite' ) . '</strong>: ' . be_mu_get_empty_answer_error() );
        return $user;
    }

    // Get the request id from the hidden field and the answer the user sent us
    $request_id = $_POST['be-mu-captcha-request'];
    $answer = strtolower( $_POST['be-mu-captcha-answer'] );

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // Get database data for the current request
    $results = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $main_blog_prefix . 'be_mu_captcha WHERE request_id = %s', $request_id ), ARRAY_A );

    // If there is no data, then the request is invalid or has expired and has beed deleted while cleaning up
    if ( null === $results ) {
        remove_action( 'authenticate', 'wp_authenticate_username_password', 20 );
        remove_action( 'authenticate', 'wp_authenticate_email_password', 20 );
        $user = new WP_Error( 'denied', '<strong>' . esc_html__( 'ERROR', 'beyond-multisite' ) . '</strong>: '
            . esc_html__( 'Invalid or expired request.', 'beyond-multisite' ) );
        return $user;
    }

    // If the submitted answer is different from the one in the database for this request id, generate an error and cancel further processing
    // And also delete this request from the database, so it cannot be guessed multiple times
    if ( $results['answer'] != $answer ) {
        remove_action( 'authenticate', 'wp_authenticate_username_password', 20 );
        remove_action( 'authenticate', 'wp_authenticate_email_password', 20 );
        $user = new WP_Error( 'denied', '<strong>' . esc_html__( 'ERROR', 'beyond-multisite' ) . '</strong>: ' . be_mu_get_wrong_answer_error() );
        $wpdb->delete( $main_blog_prefix . 'be_mu_captcha', array( 'request_id' => $request_id ), array( '%s' ) );
        return $user;
    }

    // Delete the database data for this request and answer so it cannot be used again
    $wpdb->delete( $main_blog_prefix . 'be_mu_captcha', array( 'request_id' => $request_id, 'answer' => $answer ), array( '%s', '%s' ) );

    // Return $user to allow wordpress to check password and username
    return $user;
}

/**
 * Adds the captcha to the lost password form
 * @return mixed
 */
function be_mu_lost_pass_form_captcha() {

    // Based on the plugin settings we may decide to not show a captcha and just return true
    if ( be_mu_get_setting( 'be-mu-captcha-lost-password' ) != 'on' ) {
        return true;
    }

    // Make a new captcha
    $captcha = be_mu_make_captcha();

    if ( ! is_array( $captcha ) ) {
        echo '<p class="be-mu-captcha-lost-password-image-p">'
            . '<label>' . esc_html__( 'Security Image:', 'beyond-multisite' ) . '</label><br>'
            . esc_html__( 'Error: Could not save the answer to the database.', 'beyond-multisite' )
            . '<input name="be-mu-captcha-request" type="hidden" value="error" />'
            . '</p>';
    } else {

        // Display the captcha image (security image) and hidden request id field in the form
        echo '<p class="be-mu-captcha-lost-password-image-p">'
            . '<label>' . esc_html__( 'Security Image:', 'beyond-multisite' ) . '</label> '
            . '<img class="be-mu-captcha-image" src="' . esc_url( $captcha['captchas_url'] . $captcha['request_id'] . '.png' ) . '" /> '
            . '<input name="be-mu-captcha-request" type="hidden" value="' . esc_attr( $captcha['request_id'] ) . '" />'
            . '</p>';

    }

    // Display the text field where users will enter the captcha answer (security text)
    echo '<p class="be-mu-captcha-lost-password-answer-p">'
        . '<label for="be-mu-captcha-answer">' . esc_html( be_mu_get_answer_label() ) . '</label> '
        . '<input id="be-mu-captcha-answer" autocomplete="off" name="be-mu-captcha-answer" size="9" type="text" />'
        . '</p>';
}

/**
 * Validates the lost password captcha
 * @param object $errors
 * @return object
 */
function be_mu_validate_lost_pass_form( $errors ) {

    // Based on the plugin settings we may decide to not require a captcha answer and just return $errors to let wordpress do its work
    if ( be_mu_get_setting( 'be-mu-captcha-lost-password' ) != 'on' ) {
        return $errors;
    }

    // Check if the request and captcha fields are empty or not set and show an error if they are
    if ( ! isset( $_POST['be-mu-captcha-request'] ) || '' == $_POST['be-mu-captcha-request'] ) {
        $errors->add( 'be_mu_captcha_error', '<strong>' . esc_html__( 'ERROR', 'beyond-multisite' ) . '</strong>: '
            . esc_html__( 'An unexpected error occurred.', 'beyond-multisite' ) );
        return $errors;
    }

    if ( ! isset( $_POST['be-mu-captcha-answer'] ) || '' == $_POST['be-mu-captcha-answer'] ) {
        $errors->add( 'be_mu_captcha_error', '<strong>' . esc_html__( 'ERROR', 'beyond-multisite' ) . '</strong>: ' . be_mu_get_empty_answer_error() );
        return $errors;
    }

    // Get the request id from the hidden field and the answer the user sent us
    $request_id = $_POST['be-mu-captcha-request'];
    $answer = strtolower( $_POST['be-mu-captcha-answer'] );

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // Get database data for the current request
    $results = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $main_blog_prefix . 'be_mu_captcha WHERE request_id = %s', $request_id ), ARRAY_A );

    // If there is no data, then the request is invalid or has expired and has beed deleted while cleaning up
    if( null === $results ) {
        $errors->add( 'be_mu_captcha_error', '<strong>' . esc_html__( 'ERROR', 'beyond-multisite' ) . '</strong>: '
            . esc_html__( 'Invalid or expired request.', 'beyond-multisite' ) );
        return $errors;
    }

    // If the submitted answer is different from the one in the database for this request id, generate an error
    // And also delete this request from the database, so it cannot be guessed multiple times
    if ( $results['answer'] != $answer ) {
        $wpdb->delete( $main_blog_prefix . 'be_mu_captcha', array( 'request_id' => $request_id ), array( '%s' ) );
        $errors->add( 'be_mu_captcha_error', '<strong>' . esc_html__( 'ERROR', 'beyond-multisite' ) . '</strong>: ' . be_mu_get_wrong_answer_error() );
        return $errors;
    }

    // Delete the database data for this request and answer so it cannot be used again
    $wpdb->delete( $main_blog_prefix . 'be_mu_captcha', array( 'request_id' => $request_id, 'answer' => $answer ), array( '%s', '%s' ) );

    // Return $errors to allow wordpress to do its work
    return $errors;
}

/**
 * Adds the captcha to the reset password form
 * @return mixed
 */
function be_mu_reset_pass_form_captcha() {

    // Based on the plugin settings we may decide to not show a captcha and just return true
    if ( be_mu_get_setting( 'be-mu-captcha-reset-password' ) != 'on' ) {
        return true;
    }

    // Make a new captcha
    $captcha = be_mu_make_captcha();

    if ( ! is_array( $captcha ) ) {
        echo '<p class="be-mu-captcha-reset-password-image-p">'
            . '<label>' . esc_html__( 'Security Image:', 'beyond-multisite' ) . '</label><br>'
            . esc_html__( 'Error: Could not save the answer to the database.', 'beyond-multisite' )
            . '<input name="be-mu-captcha-request" type="hidden" value="error" />'
            . '</p>';
    } else {

        // Display the captcha image (security image) and hidden request id field in the form
        echo '<p class="be-mu-captcha-reset-password-image-p">'
            . '<label>' . esc_html__( 'Security Image:', 'beyond-multisite' ) . '</label> '
            . '<img class="be-mu-captcha-image" src="' . esc_url( $captcha['captchas_url'] . $captcha['request_id'] . '.png' ) . '" /> '
            . '<input name="be-mu-captcha-request" type="hidden" value="' . esc_attr( $captcha['request_id'] ) . '" />'
            . '</p>';

    }

    // Display the text field where users will enter the captcha answer (security text)
    echo '<p class="be-mu-captcha-reset-password-answer-p">'
        . '<label for="be-mu-captcha-answer">' . esc_html( be_mu_get_answer_label() ) . '</label> '
        . '<input id="be-mu-captcha-answer" autocomplete="off" name="be-mu-captcha-answer" size="9" type="text" />'
        . '</p>';
}

/**
 * Validates the reset password captcha
 * @param object $errors
 * @param object $user
 * @return mixed
 */
function be_mu_validate_reset_pass_form( $errors, $user ) {

    // Based on the plugin settings we may decide to not require a captcha answer and just return $errors to let wordpress do its work
    if ( be_mu_get_setting( 'be-mu-captcha-reset-password' ) != 'on' ) {
        return null;
    }

    // If the password is not sent we also do nothing (and return null)
    // This way we avoid errors to be shown before the user clicks the button to change the password
    if ( ! isset( $_POST['pass1'] ) ) {
        return null;
    }

    // Check if the request and captcha fields are empty or not set and show an error if they are
    if ( ! isset( $_POST['be-mu-captcha-request'] ) || '' == $_POST['be-mu-captcha-request'] ) {
        $errors->add( 'be_mu_captcha_error', '<strong>' . esc_html__( 'ERROR', 'beyond-multisite' ) . '</strong>: '
            . esc_html__( 'An unexpected error occurred.', 'beyond-multisite' ) );
        return $errors;
    }

    if ( ! isset( $_POST['be-mu-captcha-answer'] ) || '' == $_POST['be-mu-captcha-answer'] ) {
        $errors->add( 'be_mu_captcha_error', '<strong>' . esc_html__( 'ERROR', 'beyond-multisite' ) . '</strong>: ' . be_mu_get_empty_answer_error() );
        return $errors;
    }

    // Get the request id from the hidden field and the answer the user sent us
    $request_id = $_POST['be-mu-captcha-request'];
    $answer = strtolower( $_POST['be-mu-captcha-answer'] );

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // Get database data for the current request
    $results = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $main_blog_prefix . 'be_mu_captcha WHERE request_id = %s', $request_id ), ARRAY_A );

    // If there is no data, then the request is invalid or has expired and has beed deleted while cleaning up
    if ( null === $results ) {
        $errors->add( 'be_mu_captcha_error', '<strong>' . esc_html__( 'ERROR', 'beyond-multisite' ) . '</strong>: '
            . esc_html__( 'Invalid or expired request.', 'beyond-multisite' ) );
        return $errors;
    }

    // If the submitted answer is different from the one in the database for this request id, generate an error
    // And also delete this request from the database, so it cannot be guessed multiple times
    if ( $results['answer'] != $answer ) {
        $wpdb->delete( $main_blog_prefix . 'be_mu_captcha', array( 'request_id' => $request_id ), array( '%s' ) );
        $errors->add( 'be_mu_captcha_error', '<strong>' . esc_html__( 'ERROR', 'beyond-multisite' ) . '</strong>: '.be_mu_get_wrong_answer_error() );
        return $errors;
    }

    // Delete the database data for this request and answer so it cannot be used again
    $wpdb->delete( $main_blog_prefix . 'be_mu_captcha', array( 'request_id' => $request_id, 'answer' => $answer ), array( '%s', '%s' ) );

    // Return null to allow wordpress to do its work
    return null;
}

/**
 * Adds the captcha to the comment form before the submit button
 * @param string $submit_button
 * @param array $args
 * @return string
 */
function be_mu_comment_form_captcha( $submit_button, $args ) {

    // Based on the plugin settings and the user being logged in or out
    // We may decide to not require a captcha answer and just return the submit button
    if ( ( is_user_logged_in() && be_mu_get_setting( 'be-mu-captcha-comment-logged-in' ) != 'on' ) ||
        ( ! is_user_logged_in() && be_mu_get_setting( 'be-mu-captcha-comment-logged-out' ) != 'on' ) ) {
        return $submit_button;
    }

    // Make a new captcha
    $captcha = be_mu_make_captcha();

    if ( ! is_array( $captcha ) ) {
        $to_add = '<p class="be-mu-captcha-comment-image-p">'
            . '<label>' . esc_html__( 'Security Image:', 'beyond-multisite' ) . '</label><br>'
            . esc_html__( 'Error: Could not save the answer to the database.', 'beyond-multisite' )
            . '<input name="be-mu-captcha-request" type="hidden" value="error" />'
            . '</p>';
    } else {

        // This is the code for the captcha image (security image) and hidden request id field
        $to_add = '<p class="be-mu-captcha-comment-image-p">'
            . '<label>' . esc_html__( 'Security Image:', 'beyond-multisite' ) . '</label> '
            . '<img class="be-mu-captcha-image" src="' . esc_url( $captcha['captchas_url'] . $captcha['request_id'] . '.png' ) . '" /> '
            . '<input name="be-mu-captcha-request" type="hidden" value="' . esc_attr( $captcha['request_id'] ) . '" />'
            . '</p>';
    }

    // This is the text field where users will enter the captcha answer (security text)
    $to_add .= '<p class="be-mu-captcha-comment-answer-p">'
        . '<label for="be-mu-captcha-answer">' . esc_html( be_mu_get_answer_label() ) . '</label> '
        . '<input id="be-mu-captcha-answer" autocomplete="off" name="be-mu-captcha-answer" size="9" type="text" />'
        . '</p>';

    // We return the code with the captcha put before the submit button
    return $to_add . $submit_button;
}

/**
 * Validates the comment captcha
 * @param array $commentdata
 * @return array
 */
function be_mu_validate_comment_form( $commentdata ) {

    // Based on the plugin settings and the user being logged in or out
    // We may decide to not require a captcha answer and just return the $commentdata
    if ( ( is_user_logged_in() && be_mu_get_setting( 'be-mu-captcha-comment-logged-in' ) != 'on' ) ||
    ( ! is_user_logged_in() && be_mu_get_setting( 'be-mu-captcha-comment-logged-out') != 'on' ) ) {
        return $commentdata;
    }

    // Check if the request and captcha fields are empty or not set and show an error if they are
    if ( ! isset( $_POST['be-mu-captcha-request'] ) || '' == $_POST['be-mu-captcha-request'] ) {
	    wp_die( '<p><strong>' . esc_html__( 'ERROR', 'beyond-multisite' ) . '</strong>: ' . esc_html__( 'An unexpected error occurred.', 'beyond-multisite' )
            . '</p><p></p><p><a href="javascript:history.back()">&laquo; ' . esc_html__( 'Go Back', 'beyond-multisite' ) . '</a></p>' );
    }
    if ( ! isset( $_POST['be-mu-captcha-answer'] ) || '' == $_POST['be-mu-captcha-answer'] ) {
        wp_die( '<p><strong>' . esc_html__( 'ERROR', 'beyond-multisite' ) . '</strong>: ' . be_mu_get_empty_answer_error()
            . '</p><p></p><p><a href="javascript:history.back()">&laquo; ' . esc_html__( 'Go Back', 'beyond-multisite' ) . '</a></p>' );
    }

    // Get the request id from the hidden field and the answer the user sent us
    $request_id = $_POST['be-mu-captcha-request'];
    $answer = strtolower( $_POST['be-mu-captcha-answer'] );

    // We need these to connect to the database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;
    $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

    // Get database data for the current request
    $results = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $main_blog_prefix . 'be_mu_captcha WHERE request_id = %s', $request_id ), ARRAY_A );

    // If there is no data, then the request is invalid or has expired and has beed deleted while cleaning up
    if ( null === $results ) {
        wp_die(
            '<p><strong>' . esc_html__( 'ERROR', 'beyond-multisite' ) . '</strong>: '
            . esc_html__( 'Invalid or expired request.', 'beyond-multisite' ) . '</p>'
            . sprintf(
                esc_html__( '%1$sYou have to %2$sgo back and RELOAD THE PAGE%3$s to make a new valid request.%4$s'
                    . '%1$sBefore you reload %2$scopy your comment%3$s, so you can paste it (instead of writing it again).%4$s', 'beyond-multisite'),
                '<p>',
                '<b>',
                '</b>',
                '</p>'
            )
            . '<p><a href="javascript:history.back()">&laquo; ' . esc_html__( 'Go Back', 'beyond-multisite' ) . '</a></p>'
        );
    }

    // If the submitted answer is different from the one in the database for this request id, generate an error
    // And also delete this request from the database, so it cannot be guessed multiple times
    if ( $results['answer'] != $answer ) {
        $wpdb->delete( $main_blog_prefix . 'be_mu_captcha', array( 'request_id' => $request_id ), array( '%s' ) );
        wp_die(
            '<p><strong>' . esc_html__( 'ERROR', 'beyond-multisite' ) . '</strong>: ' . be_mu_get_wrong_answer_error() . '</p>'
            . sprintf(
                esc_html__( '%1$sYou have to %2$sgo back and RELOAD THE PAGE%3$s to make a new valid request.%4$s'
                    . '%1$sBefore you reload %2$scopy your comment%3$s, so you can paste it (instead of writing it again).%4$s', 'beyond-multisite'),
                '<p>',
                '<b>',
                '</b>',
                '</p>'
            )
            . '<p><a href="javascript:history.back()">&laquo; ' . esc_html__( 'Go Back', 'beyond-multisite' ) . '</a></p>'
        );
    }

    // Delete the database data for this request and answer so it cannot be used again
    $wpdb->delete( $main_blog_prefix . 'be_mu_captcha', array( 'request_id' => $request_id, 'answer' => $answer ), array( '%s', '%s' ) );

    // Return $commentdata to allow wordpress to do its work
    return $commentdata;
}

// It is used via ajax to update the captcha preview image in the plugin settings
function be_mu_update_captcha_preview_action_callback() {

    // We check the nonce that we generated before the ajax request
    // This validates the request and improves security
    if ( ! check_ajax_referer( 'be_mu_ajax_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }

    // Get the plugin settings for the captcha
    $height = intval( $_POST['height'] );
    $characters = intval( $_POST['characters'] );
    $character_set = $_POST['character_set'];

    // Set the variable with the characters to exclude from the set, based on the setting (we also exclude some misleading or hard to read characters)
    if( 'Numbers' == $character_set ) {
        $exclude = 'abcdefghijklmnopqrstuvwxyz';
    } elseif( 'Letters' == $character_set ) {
        $exclude = "0olge312456789";
    } else {
        $exclude = '0olge3';
    }

    // Generate a random answer with a number of characters defined in the plugin settings
    $answer = be_mu_random_string( $characters, $exclude );

    // Create the captcha image file for the preview based on the settings
    if( ! be_mu_captcha_image( $height, $answer, 'preview' ) ) {
        wp_die( 'error-making-image' );
    }

    // This is required to terminate immediately and return a proper response
	wp_die();
}

/**
 * We add the captcha to the registration form made by the WP Ultimo plugin on the step a domain is chosen.
 * But it is validated in the function be_mu_validate_blog_signup as well. Tested on WP Ultimo 1.10.11.
 * @return bool
 */
function be_mu_captcha_signup_wp_ultimo() {

    // Based on the plugin settings and the user being logged in or out. We may decide to not show a captcha and just return true.
    if ( ( is_user_logged_in() && be_mu_get_setting( 'be-mu-captcha-blog-signup-logged-in' ) != 'on' ) ||
        ( ! is_user_logged_in() && be_mu_get_setting( 'be-mu-captcha-blog-signup-logged-out' ) != 'on' ) ) {
        return true;
    }

    // Make a new captcha
    $captcha = be_mu_make_captcha();

    if ( ! is_array( $captcha ) ) {
        echo '<p class="be-mu-captcha-signup-blog-image-p be-mu-display-none">'
            . '<label>' . esc_html__( 'Security Image:', 'beyond-multisite' ) . '</label><br>'
            . esc_html__( 'Error: Could not save the answer to the database.', 'beyond-multisite' )
            . '<input name="be-mu-captcha-request-blog-signup" type="hidden" value="error" />'
            . '</p>';
    } else {

        // Display the captcha image (security image) and hidden request id field in the form
        echo '<p class="be-mu-captcha-signup-blog-image-p be-mu-display-none">'
            . '<label>' . esc_html__( 'Security Image:', 'beyond-multisite' ) . '</label> '
            . '<img class="be-mu-captcha-image" src="' . esc_url( $captcha['captchas_url'] . $captcha['request_id'] . '.png' ). '" /> '
            . '<input name="be-mu-captcha-request-blog-signup" type="hidden" value="' . esc_attr( $captcha['request_id'] ) . '" />'
            . '</p>';

    }

    if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
        do {

            // Check if the request and captcha fields are not empty
            if ( ! isset( $_POST['be-mu-captcha-request-blog-signup'] ) ) {
                echo '<p class="error be-mu-captcha-signup-blog-error-p">' . esc_html__( 'An unexpected error occurred.', 'beyond-multisite' ) . '</p>';
                break;
            }
            if ( ! isset( $_POST['be-mu-captcha-answer-blog-signup'] ) || '' == $_POST['be-mu-captcha-answer-blog-signup'] ) {
                echo '<p class="error be-mu-captcha-signup-blog-error-p">' . be_mu_get_empty_answer_error() . '</p>';
                break;
            }

            // Get the request id from the hidden field and the answer the user sent us
            $request_id = $_POST['be-mu-captcha-request-blog-signup'];
            $answer = strtolower( $_POST['be-mu-captcha-answer-blog-signup'] );

            // We need these to connect to the database
            global $wpdb;
            $main_blog_prefix = $wpdb->get_blog_prefix( be_mu_get_main_site_id() );

            // Get database data for the current request
            $results = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $main_blog_prefix . 'be_mu_captcha WHERE request_id = %s', $request_id ), ARRAY_A );

            // If there is no data, then the request is invalid or has expired or it is incorrectly answered and has beed deleted while cleaning up
            if ( null === $results ) {
                echo '<p class="error be-mu-captcha-signup-blog-error-p">'
                    . esc_html__( 'Incorrect security text or expired request.', 'beyond-multisite' ). '</p>';
                break;
            }

            // Delete the database data for this request and answer so they cannot be used again
            $wpdb->delete( $main_blog_prefix . 'be_mu_captcha', array( 'request_id' => $request_id, 'answer' => $answer ), array( '%s', '%s' ) );

        } while (false);
    }

    // Display the text field where users will enter the captcha answer (security text)
    echo '<p class="be-mu-captcha-signup-blog-answer-p be-mu-display-none">'
        . '<label for="be-mu-captcha-answer-blog-signup">' . esc_html( be_mu_get_answer_label() ) . '</label> '
        . '<input id="be-mu-captcha-answer-blog-signup" autocomplete="off" name="be-mu-captcha-answer-blog-signup" size="9" type="text" />'
        . '</p>';

    // We move the captcha before the submit button with a script, since there is no other way to insert there
    echo '<script type="text/javascript">
    jQuery( function(){
        if ( jQuery( ".submit" ).length ) {
            jQuery( ".submit").insertAfter( ".be-mu-captcha-signup-blog-answer-p" );
        }
        jQuery( ".be-mu-captcha-signup-blog-answer-p" ).removeClass( "be-mu-display-none" );
        jQuery( ".be-mu-captcha-signup-blog-image-p" ).removeClass( "be-mu-display-none" );
    });
    </script>';
}
