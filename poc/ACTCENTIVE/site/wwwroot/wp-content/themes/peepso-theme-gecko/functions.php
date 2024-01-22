<?php

// PeepSo Version Check
$ver_gecko = explode('.', wp_get_theme()->version);

while(count($ver_gecko) > 3) {
    array_pop($ver_gecko);
}

$ver_gecko = implode('.', $ver_gecko);

if(class_exists('PeepSo')) {

    $ver_peepso = explode('.',PeepSo::PLUGIN_VERSION);
    while(count($ver_peepso) > 3) {
        array_pop($ver_peepso);
    }

    $ver_peepso = implode('.', $ver_peepso);

    if($ver_peepso != $ver_gecko) {
        // @TODO RAISE WARNING #3343
        add_action('admin_notices', function () {
            echo '<div class="error peepso">' .
                sprintf(__('Please make sure the first three version numbers of PeepSo plugins %s and Gecko theme %s match. It’d be best to update the plugins and theme to latest versions. If you are using a child theme, please check our <a href="%s">documentation</a> to fix the issue.', 'peepso-theme-gecko'), PeepSo::PLUGIN_VERSION, wp_get_theme()->version, "https://www.peepso.com/documentation/version-mismatch-notice-when-using-child-theme/")
                . '</strong></div>';
        });
    }
}

//  ENQUEUE SCRIPTS
require_once( __DIR__ . '/core/enqueue-scripts.php');

add_action('wp_head',function() { ?>
    <!--[if lt IE 9]>
    <script src="<?php echo esc_url( get_template_directory_uri() ); ?>/js/html5.js"></script>
    <![endif]-->
<?php });

//
//  REGISTER MENU
//
register_nav_menus( array(
    'primary-menu' => 'Header Menu',
    'mobile-menu' => 'Mobile Menu',
    'footer-menu'  => 'Footer Menu',
));


//
// INITIAL THEME SETUP
//
function gecko_load_theme_setup() {
    // Language
    load_theme_textdomain( 'gecko', get_template_directory() . '/language' );

    // Add default posts and comments RSS feed links to head
    add_theme_support( 'automatic-feed-links' );

    // Add support for Block Styles.
    add_theme_support( 'wp-block-styles' );

    // Add support for full and wide align images.
    add_theme_support( 'align-wide' );

    // Enqueue editor styles.
    $editor_stylesheet_path = '/assets/css/style-editor.css';

    add_editor_style( $editor_stylesheet_path );
}
add_action( 'after_setup_theme', 'gecko_load_theme_setup' );


// SETUP INITIAL CONFIG
/* Tell WordPress to run gecko_setup() when the 'after_switch_theme' hook is run. */
function gecko_setup() {
    $default_config = array(
        // 'opt_show_search_in_header' => '',
        // 'opt_limit_page_options' => '',
        // 'opt_sticky_sidebar' => '0',
        // 'opt_blog_sidebars' => '',
        // 'opt_blog_update' => '',
        // 'opt_blog_grid' => '',
        // 'opt_archives_grid' => '',
        // 'opt_search_grid' => '',
        // 'opt_woo_builder' => '',
        // 'opt_woo_sidebars'  => '',
        // 'opt_ld_sidebars' => '',
        'gecko_license' => '',
        // 'opt_limit_blog_post' => '0'
    );

    add_option('gecko_options', $default_config);
}
add_action( 'after_switch_theme', 'gecko_setup' );


//
//  INCLUDES
//

//  helper class
require_once( __DIR__ . '/core/helpers.php');

//  date class
require_once( __DIR__ . '/core/date.php');

//  OPTIONS class
require_once( __DIR__ . '/core/admin/options.php');

//  SETTINGS PAGE
require_once( __DIR__ . '/core/admin/settings.php');

//  PAGE BUILDERS PAGE
require_once( __DIR__ . '/core/admin/page_builders.php');

//  SETTINGS - LICENSE SUBPAGE
require_once( __DIR__ . '/core/admin/license.php');

//  CUSTOMIZER
require_once( __DIR__ . '/core/admin/customizer.php');

//  WIDGETS
require_once( __DIR__ . '/core/widgets.php');

//  PAGE OPTIONS
require_once( __DIR__ . '/core/page.php');

//  LANDING OPTIONS
require_once( __DIR__ . '/core/landing.php');

//  UTILITY FUNCTIONS
require_once( __DIR__ . '/core/utility.php');


// Save config
if(isset($_REQUEST['gecko_options']) && current_user_can('manage_options')) {

    $options = apply_filters('gecko_sanitize_option', $_REQUEST['gecko_options']);

    foreach ($options as $key => $value) {
        GeckoConfigSettings::get_instance()->set_option(
            $key,
            $value,
            TRUE
        );
    }
}

//
//  REDIRECTS
//
function is_login_page() {
    global $wp, $wpdb;
    $register_id = $wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'posts WHERE post_content LIKE "%[peepso_register]%" AND post_parent = 0');
    $recovery_id = $wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'posts WHERE post_content LIKE "%[peepso_recover]%" AND post_parent = 0');
    $reset_id = $wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'posts WHERE post_content LIKE "%[peepso_reset]%" AND post_parent = 0');

    if ( $GLOBALS['pagenow'] === 'wp-login.php' && ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] === 'register' || is_page( array( $register_id, $recovery_id, $reset_id ) ) )
        return true;
    return false;
}

function guest_redirect() {

    // Do not redirect users who are logged in
    if(is_user_logged_in())     {   return false;   }

    // Get settings
    $gecko_settings = GeckoConfigSettings::get_instance();
    $redirect_page_id = $gecko_settings->get_option( 'opt_redirect_guest', 0 );

    // Exit if option is disabled
    if ($redirect_page_id < 1)   {   return false;   }

    // Do nothing if we are already there
    if( !is_page($redirect_page_id) ){

        // Do not interfere with login, registration, password reset and privacy policy
        if(is_login_page())         {   return false;   }
        if(is_privacy_policy())     {   return false;   }
        if(is_feed())				{   return false;   }

        // Check exceptions
        $exception_ids = $gecko_settings->get_option( 'opt_redirect_guest_exceptions', '' );
        $exception_cpt = $gecko_settings->get_option( 'opt_redirect_guest_exceptions_cpt', '' );

        // Is it comma separated?
        if(strlen($exception_ids && stristr($exception_ids, ','))) {
            $exception_ids = explode( ',', $exception_ids );
        }

        if(strlen($exception_cpt && stristr($exception_cpt, ','))) {
            $exception_cpt = explode( ',', $exception_cpt );
        }

        // Or maybe it's just one item?
        if(!is_array($exception_ids)) {
            $exception_ids = [ $exception_ids ];
        }

        if(!is_array($exception_cpt)) {
            $exception_cpt = [ $exception_cpt ];
        }

        // Initialize
        global $wp;
        $post_id = NULL;
        $post = NULL;
        $page = NULL;

        // Specific page exceptions
        if(is_array($exception_ids) && count($exception_ids)) {

            foreach($exception_ids as $id) {

                $id = strtolower(trim($id));

                if( is_numeric($id) && is_page($id) ) {
                    return false;
                }

                if('blog' == $id && is_home()) {
                    return false;
                }

                if('frontpage' == $id && is_front_page()) {
                    return false;
                }

                // Posts

                #6424 attempt to let in CPTs based on ID

                if(!$post_id) {
                    $post_id = url_to_postid($wp->request);
                }

                if(is_numeric($id) && $id==$post_id) {
                    return false;
                }

                if(!is_numeric($id) && !empty($id) && $id==$wp->request) {
                    return false;
                }

                // Pages
                if(!$page)      { $page = get_page_by_path($wp->request);   }

                if(is_numeric($id) && is_object($page) && $id==$page->ID) {
                    return false;
                }
            }
        }

        // Post type exceptions
        if(is_array($exception_cpt) && count($exception_cpt)) {

            foreach($exception_cpt as $cpt) {

                $cpt = strtolower(trim($cpt));

                if(!$post_id) { $post_id = url_to_postid($wp->request); }
                if(!$post) { $post = get_post($post_id); }

                if($post instanceof WP_Post && $post->post_type == $cpt) {
                    return false;
                }
            }
        }

        // $redirect_page_id is the page id of the target landing page
        $redirect = trim(get_permalink($redirect_page_id), '/');
        if ($redirect != home_url( $wp->request )) {
            wp_redirect( $redirect );
            exit;
        }
    }
}
add_action( 'template_redirect', 'guest_redirect' );

/**
 * Log errors.
 *
 * @param string $error_message
 */
function gecko_log_error($error_message) {
    if ( class_exists('PeepSoError') ) {
        new PeepSoError($error_message);
    }
}

// HTML Classes Array
function gecko_get_html_class( $class = '' ) {
    $classes = array();

    if ( ! empty( $class ) ) {
        if ( ! is_array( $class ) ) {
            $class = preg_split( '#\s+#', $class );
        }
        $classes = array_merge( $classes, $class );
    } else {
        // Ensure that we always coerce class to being an array.
        $class = array();
    }

    $classes = array_map( 'esc_attr', $classes );

    $classes = apply_filters( 'gecko_html_class', $classes, $class );

    return array_unique( $classes );
}

function gecko_html_class( $class = '' ) {
    // Separates class names with a single space, collates class names for body element
    echo 'class="' . join( ' ', gecko_get_html_class( $class ) ) . '"';
}

//  HEADER FUNCTIONS
require_once( __DIR__ . '/core/header.php');



add_filter( 'woocommerce_widget_cart_is_hidden', 'always_show_cart', 40, 0 );
function always_show_cart() {
    return false;
}

/**
 * Change number or products per row
 */
add_filter('loop_shop_columns', 'loop_columns', 999);
if (!function_exists('loop_columns')) {
	function loop_columns() {
        $gecko_settings = GeckoConfigSettings::get_instance();
        $number = $gecko_settings->get_option( 'opt_woo_columns', 3 );
		return $number; // 3 products per row
	}
}

add_action( 'admin_bar_menu', function() {
    if ( ! current_user_can( 'customize' ) ) {
        return;
    }

    global $wp_admin_bar;
    $nodes = $wp_admin_bar->get_nodes();
    if (isset($nodes['customize']) || (isset($_GET['page']) && $_GET['page'] == 'gecko-customizer')) {
        if (!isset($nodes['customize'])) {
            $nodes['customize'] = new StdClass();
            $nodes['customize']->title = __('Customize', 'peepso-theme-gecko');
            $nodes['customize']->parent = $nodes['customize']->group = FALSE;
            $nodes['customize']->meta = [
                'class' => 'hide-if-no-customize'
            ];
        }
        $nodes['customize']->href = admin_url() . 'admin.php?page=gecko-customizer';
        $nodes['customize']->id = 'customize';
        $wp_admin_bar->add_menu(array('parent' => 'customize', 'title' => __('Gecko Customizer', 'peepso-theme-gecko'), 'id' => 'customizer-gecko', 'href' => admin_url() . 'admin.php?page=gecko-customizer'));
        $wp_admin_bar->add_menu(array('parent' => 'customize', 'title' => __('WP Customizer', 'peepso-theme-gecko'), 'id' => 'wp-customizer', 'href' => admin_url() . 'customize.php'));
        $wp_admin_bar->add_menu($nodes['customize']);
    }
}, 999 );

// Check for custom logo set by Gecko Customizer.
add_filter( 'theme_mod_custom_logo', 'gecko_theme_mod_custom_logo', 10, 1 );
function gecko_theme_mod_custom_logo( $attachment_id ) {
    $gecko_settings = GeckoConfigSettings::get_instance();
    $custom_logo_id = $gecko_settings->get_option( 'opt_custom_logo' );

    if ( $custom_logo_id ) {
        $attachment_id = $custom_logo_id;
    }

    return $attachment_id;
}

// Check for custom site icon set by Gecko Customizer.
add_filter( 'get_site_icon_url', 'gecko_get_site_icon_url', 10, 3 );
function gecko_get_site_icon_url( $url, $size, $blog_id ) {
    $gecko_settings = GeckoConfigSettings::get_instance();
    $custom_icon_id = $gecko_settings->get_option( 'opt_custom_icon' );

    if ( $custom_icon_id ) {
        if ( $size >= 512 ) {
            $size_data = 'full';
        } else {
            $size_data = array( $size, $size );
        }

        $url = wp_get_attachment_image_url( $custom_icon_id, $size_data );
    }

    return $url;
}

// Main layout class
function layout_main_class($id) {

    if(isset($_GLOBALS['layout_main_class'])) {
        return $_GLOBALS['layout_main_class'];
    }

    $gecko_settings = GeckoConfigSettings::get_instance();

    $main_class = '';
    $hide_sidebars = get_post_meta($id, 'gecko-page-sidebars', true);

    $has_left_sidebar = TRUE;
    $has_right_sidebar = TRUE;

    $has_left_widgets = is_active_sidebar( 'sidebar-left' );
    $has_right_widgets = is_active_sidebar( 'sidebar-right' );

    $show_left_sidebar = $gecko_settings->get_option( 'opt_sidebar_left_vis', 1 );
    $show_right_sidebar = $gecko_settings->get_option( 'opt_sidebar_right_vis', 1 );

    /** LEFT SIDEBAR **/

    // is left sidebar hidden in page meta?
    if(in_array($hide_sidebars, ['both','left'])) {
        $has_left_sidebar = FALSE;
    }

    //
    // MobiLoud
    //
    if ( GeckoAppHelper::is_app() && PeepSo::get_option('app_gecko_hide_widgets_sidebar-left')) {
        $has_left_sidebar = FALSE;
    }
    // end: Mobiloud

    // are there widgets in left sidebar?
    if($has_left_sidebar && !$has_left_widgets) {
        $has_left_sidebar = FALSE;
    }

    // is there content in left sidebar?
    if($has_left_sidebar && !isset($_GLOBALS['gecko_sidebar_left_content'])) {
        gecko_log_error('Initializing GLOBALS LEFT');
        ob_start();
        dynamic_sidebar( 'sidebar-left' );
        $_GLOBALS['gecko_sidebar_left_content'] = ob_get_clean();
    }

    if($has_left_sidebar && !strlen(trim($_GLOBALS['gecko_sidebar_left_content']))) {
        $has_left_sidebar = FALSE;
    }

    // Customizer sidebar visibility toggle
    if ( !$show_left_sidebar ) {
        $has_left_sidebar = FALSE;
    }

    /** RIGHT SIDEBAR **/

    // is right sidebar hidden in page meta?
    if(in_array($hide_sidebars, ['both','right'])) {
        $has_right_sidebar = FALSE;
    }

    //
    // MobiLoud
    //
    if ( GeckoAppHelper::is_app() && PeepSo::get_option('app_gecko_hide_widgets_sidebar-right') ) {
        $has_right_sidebar = FALSE;
    }
    // end: Mobiloud

    // are there widgets in right sidebar?
    if($has_right_sidebar && !$has_right_widgets) {
        $has_right_sidebar = FALSE;
    }

    // is there content in right sidebar?
    if($has_right_sidebar && !isset($_GLOBALS['gecko_sidebar_right_content'])) {
        gecko_log_error('Initializing GLOBALS RIGHT');
        ob_start();
        dynamic_sidebar( 'sidebar-right' );
        $_GLOBALS['gecko_sidebar_right_content'] = ob_get_clean();
    }

    if($has_right_sidebar && !strlen(trim($_GLOBALS['gecko_sidebar_right_content']))) {
        $has_right_sidebar = FALSE;
    }

    // Customizer sidebar visibility toggle
    if ( !$show_right_sidebar ) {
        $has_right_sidebar = FALSE;
    }


    if($has_left_sidebar && !$has_right_sidebar) {
        $main_class ='main--left';
    }

    if($has_right_sidebar && !$has_left_sidebar) {
        $main_class ='main--right';
    }

    if($has_right_sidebar && $has_left_sidebar) {
        $main_class = 'main--both';
    }

    gecko_log_error("Gecko main class: $main_class");

    $_GLOBALS['layout_main_class'] = $main_class;

    return $main_class;
}

function gecko_is_shop() {
    return function_exists('is_shop') ? is_shop() : FALSE;
}

add_filter('body_class', function( $classes ) {
    global $post;

    $gecko_settings = GeckoConfigSettings::get_instance();
    $class = $gecko_settings->get_option( 'opt_ps_side_to_side', 0 );

    // PeepSo/PeepSo#4456 add special <body> class to PeepSo Pages

    if (! is_404() || gecko_is_shop()) {
        if ( class_exists('PeepSo') ) {
            $shortcodes = PeepSo::get_instance()->all_shortcodes();
            foreach ($shortcodes as $sc => $method) {
                if ($post instanceof WP_Post && stristr($post->post_content, "[$sc")) {
                    if($class == 1) {
                        $classes[] = 'peepso-page peepso-sts';
                    } else {
                        $classes[] = 'peepso-page';
                    }
                    break;
                }
            }
        }
    }

    if ( is_user_logged_in() ) {
        if ( get_user_option( 'gecko_darkmode', get_current_user_id() ) ) {
            $classes[] = 'gc-theme--dark';
        }
    }

    return $classes;
});

class Gecko_User_Theme_Switcher
{
    public static $instance = NULL;

    public $mode = 0;           // 0 - disabled, 1 - all custom themes, 2 - selected custom themes
    public $preset_user = '';   // Preset selected by the user
    public $preset_list = [];   // List of presets available to user

    public static function get_instance()
    {
        return (NULL != self::$instance) ? self::$instance : self::$instance = new self();
    }

    private function __construct()
    {
        require_once __DIR__ . '/core/admin/customizer-preset.php';
        require_once __DIR__ . '/core/admin/customizer-options.php';

        $gecko_settings = GeckoConfigSettings::get_instance();

        /** Set mode */
        $this->mode = $gecko_settings->get_option('opt_user_preset', 0);

        /** Load available presets */

        // Admin can define specific presets
        $enabled_presets = (array)$gecko_settings->get_option('opt_user_preset_list', []);

        // All presets present in customizer
        $presets = Gecko_Customizer_Preset::get_instance()->list(FALSE);

        $themes = array();
        foreach ($presets as $preset) {
            // 1 == load all, 2 == load some from enabled_presets
            if (1 == $this->mode || array_key_exists($preset['id'], $enabled_presets)) {
                $themes[$preset['id']] = $preset['label'];
            }
        }

        $this->preset_list = $themes;

        // Make sure the  fallback is to the first one available
        $fallback = '';
        if (count($themes)) {
            foreach ($themes as $key => $theme) {
                $fallback = $key;
                break;
            }
        }

        if(get_current_user_id()) {
            // Logged in user - use wp options
            $user_theme = get_user_meta(get_current_user_id(), 'peepso_gecko_user_theme', true);
        } else {
            // Fallback to session
            $user_theme = isset($_SESSION['gecko_active_preset']) ? $_SESSION['gecko_active_preset'] : FALSE;
        }


        if (!($user_theme && isset($themes[$user_theme]))) {
            $user_theme = get_option('gecko_active_preset', $fallback);
        }
        $this->preset_user = $user_theme;

        $_SESSION['gecko_active_preset'] = $this->preset_user;
    }
}


// Add additional user preference for PeepSo.
add_filter( 'peepso_profile_preferences', 'gecko_peepso_profile_preferences', 10, 1 );
function gecko_peepso_profile_preferences( $pref ) {

    $theme_switcher = Gecko_User_Theme_Switcher::get_instance();

    if (!$theme_switcher->mode) {
        return $pref;
    }

    $themes = $theme_switcher->preset_list;

    if(count($themes)) {
        $user_theme = $theme_switcher->preset_user;

        $fields['peepso_gecko_user_theme'] = array(
            'label' => __('Preferred color theme', 'peepso-theme-gecko'),
            'type' => 'select',
            'options' => $themes,
            'value' => $user_theme,
            'validation' => array(),
            'loading' => TRUE,
        );

        $pref['gecko'] = array(
            'title' => __('Theme', 'peepso-theme-gecko'),
            'items' => $fields,
        );
    }

    return $pref;
}

// Add PeepSo theme override message.
add_filter('peepso_free_bundle_should_brand', '__return_true');
add_filter( 'peepso_theme_override', 'gecko_peepso_theme_override', 10, 1 );
function gecko_peepso_theme_override( $override_message ) {

    $override_message = sprintf(
        'This feature is controlled by %s.',
        '<a href="' . admin_url() . 'admin.php?page=gecko-customizer">Gecko Theme <i class="fa fa-external-link"></i></a>'
    );

    return $override_message;
}

// Change URL on custom logo
function update_custom_logo_link($html, $blog_id) {

    // The logo
    $custom_logo_id = get_theme_mod( 'custom_logo' );

    // If has logo
    if ( $custom_logo_id ) {

        // Attr
        $custom_logo_attr = array(
            'class'    => 'custom-logo',
            'itemprop' => 'logo',
        );

        // Get search visibility option from admin settings
        $gecko_settings = GeckoConfigSettings::get_instance();

        if ($gecko_settings->get_option( 'opt_logo_link_redirect', 0 ) ) {
            $logo_url = $gecko_settings->get_option( 'opt_logo_link_redirect', 0 );
        } else {
            $logo_url = esc_url( home_url( '/' ) );
        }

        // Image alt
        $image_alt = get_post_meta( $custom_logo_id, '_wp_attachment_image_alt', true );
        if ( empty( $image_alt ) ) {
            $custom_logo_attr['alt'] = get_bloginfo( 'name', 'display' );
        }

        // Get the image
        $html = sprintf( '<a href="%1$s" class="custom-logo-link" rel="home" itemprop="url">%2$s</a>',
            $logo_url,
            wp_get_attachment_image( $custom_logo_id, 'full', false, $custom_logo_attr )
        );

    }

    // Return
    return $html;
}
add_filter( 'get_custom_logo', 'update_custom_logo_link',999,2 );

/**
 * Add cache buster for resource URL.
 *
 * @since 3.0.0.3
 *
 * @param string $url
 * @return string
 */
function gecko_add_cachebust_arg($url) {
    $enabled = defined('GECKO_DEV_MODE_CACHE_BUSTING') && GECKO_DEV_MODE_CACHE_BUSTING;

    // Respect PeepSo setting if present.
    if (!$enabled && class_exists('PeepSo')) {
        $enabled = PeepSo::get_option_new('cache_busting');
    }

    if ($enabled) {
        $base_url = get_stylesheet_directory_uri();
        $base_path = get_stylesheet_directory();
        $file = str_replace($base_url, $base_path, $url);
        if (file_exists($file)) {
            $url = add_query_arg('mt', filemtime($file), $url);
        }
    }

    return $url;
}

// add admin bar to customizer
if (is_customize_preview()) {
    add_action('admin_enqueue_scripts', function() {
        wp_enqueue_script( 'admin-bar' );
        wp_enqueue_style( 'admin-bar' );
    });

    // Use a variable to suppress the Theme Check error
    $show_admin_bar = 'show_admin_bar';
    $show_admin_bar(TRUE);

    add_action('wp_footer', function () {
        ?>
        <script type="text/javascript">
            jQuery(function($) {
                var moveFrom = $("#wpadminbar");
                var moveTo = $("body", window.parent.document);
                moveFrom.appendTo(moveTo);

                $(parent.document).find('.wp-full-overlay').css('z-index', 1);
                $(parent.document).find('#customize-controls').css('margin-top', $(parent.document).find('#wpadminbar').height() + 'px');

                $(window).resize(function() {
                    $(parent.document).find('#customize-controls').css('margin-top', $(parent.document).find('#wpadminbar').height() + 'px');
                });
            });

        </script>
        <?php
    });
}

//
//  Add custom body class if WPMobileAPP is activated
//
if ( GeckoAppHelper::is_app('wpmobileapp')) {
    function wpmobileapp_custom_active_body_class($classes) {
        $classes[] = 'is-wpmobileapp';
        return $classes;
    }
    add_filter('body_class', 'wpmobileapp_custom_active_body_class');
}

//
//  Add custom body class if Mobiloud is activated
//
if (GeckoAppHelper::is_app('mobiloud') ) {
    function mobiloud_custom_active_body_class($classes) {
        $classes[] = 'is-mobiloud';
        return $classes;
    }
    add_filter('body_class', 'mobiloud_custom_active_body_class');
}

//
//  Add custom body class if Mobi Sticky Top widget is active
//
if (GeckoAppHelper::is_app() && is_active_sidebar( 'mobi-sticky-top-widgets' ) || is_active_sidebar( 'sticky-top-widgets' )) {
    function mobiloud_custom_sticky_body_class($classes) {
        $classes[] = 'has-sticky-widget';
        return $classes;
    }
    add_filter('body_class', 'mobiloud_custom_sticky_body_class');
}

//
//	Support Elementor theme locations
//
// function gecko_register_elementor_locations( $elementor_theme_manager ) {
// 	$elementor_theme_manager->register_location( 'header' );
// 	$elementor_theme_manager->register_location( 'footer' );
// }
// add_action( 'elementor/theme/register_locations', 'gecko_register_elementor_locations' );
