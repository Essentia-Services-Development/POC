<?php

//
// ENQUEUE SCRIPTS
//

function gecko_scripts() {
    // Load our main stylesheet.
    wp_enqueue_style( 'gecko-styles', gecko_add_cachebust_arg(get_stylesheet_uri()) );
    // Do not load gecko icons when PeepSo is active (both use the same icons pack)
    if (!class_exists('PeepSo')) {
        wp_enqueue_style( 'gecko-icons-css', gecko_add_cachebust_arg(get_template_directory_uri() . '/assets/css/icons.css'), array(), wp_get_theme()->version );
    }
    wp_enqueue_style( 'gecko-css', gecko_add_cachebust_arg(get_template_directory_uri() . '/assets/css/gecko.css'), array(), wp_get_theme()->version );
    wp_style_add_data( 'gecko-css', 'rtl', 'replace' );

    // Prints CSS variables selected from the Gecko customizer as inline style.
    $theme_switcher = Gecko_User_Theme_Switcher::get_instance();
    $gecko_options = Gecko_Customizer_Options::get_instance();
    $gecko_preset = Gecko_Customizer_Preset::get_instance();
    $gecko_active_preset = get_option('gecko_active_preset', 'light');
    $gecko_preview = isset( $_GET['gecko-preview'] );
    $gecko_enable_user_preset = $theme_switcher->mode > 0;
    $gecko_default_preset = $gecko_active_preset;

    if ( ! $gecko_preview && $gecko_enable_user_preset ) {
        $gecko_user_preset = $theme_switcher->preset_user;
        if ( $gecko_user_preset && $gecko_preset->get($gecko_user_preset) ) {
            $gecko_active_preset = $gecko_user_preset;
        }
    }



    $presets = Gecko_Customizer_Preset::get_instance()->list();

    $themes = array();
    foreach ($presets as $preset) {
        $themes[$preset['id']] = $preset['label'];
    }

    foreach($themes as $key => $theme) {
        $fallback = $key;
        break;
    }

    // if preset is hide, then use fallback
    if (!isset($themes[$gecko_active_preset])) {
        $gecko_active_preset = $fallback;
        if (isset($themes[$gecko_default_preset])) {
            $gecko_active_preset = $gecko_default_preset;
        }
    }

    $gecko_css = '';
    $body_class = '';

    $active_preset = $gecko_preset->get($gecko_active_preset);
    $gecko_font = '';
    if ($active_preset && isset($active_preset['css_vars'])) {
        foreach ( $active_preset['css_vars'] as $key => $value ) {
            // skip preset
            if('--presets' == $key)                     { continue; }

            // skip preset-specific config values that are not CSS vars
            if(substr($key,0,6) == 'config') { continue; }

            if ('--GC-FONT-FAMILY' === $key) {
                $gecko_font = urlencode($value);
                $value = "'$value'";
            }

            $gecko_css .= "\t" . $key . ': ' . $value . ';' . PHP_EOL;
        }

        if(isset($active_preset['css_vars']['config-body-class'])) {
            $body_class = $active_preset['css_vars']['config-body-class'];
        }
    }

    add_filter('body_class', function($args) use ($gecko_active_preset, $body_class) {
        $args[]="gc-preset--$gecko_active_preset $body_class";

        return $args;
    });

    if ($gecko_font) {
        $gecko_font_url = 'https://fonts.googleapis.com/css2?family=' . $gecko_font . ':wght@400;500;700&display=swap';
        wp_enqueue_style( 'gecko-css-font', $gecko_font_url, array(), wp_get_theme()->version );
    }

    wp_add_inline_style( 'gecko-css', 'body {' . PHP_EOL . $gecko_css . '}' );

    // Gecko Scripts
    wp_enqueue_script( 'gecko-macy-js', gecko_add_cachebust_arg(get_template_directory_uri() . '/assets/js/macy.js'), array(), wp_get_theme()->version, true );
    wp_enqueue_script( 'gecko-js', gecko_add_cachebust_arg(get_template_directory_uri() . '/assets/js/scripts.js'), array('jquery'), wp_get_theme()->version, true );
    wp_localize_script( 'gecko-js', 'geckodata', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' )
    ) );

    // PeepSo-specific scripts.
    if ( class_exists( 'PeepSo' ) ) {
        $trim_long_photo = false;
        if ( isset($active_preset['css_vars']['--c-ps-post-photo-width']) ) {
            if ( '100%' === $active_preset['css_vars']['--c-ps-post-photo-width'] ) {
                if(isset($active_preset['css_vars']['config-ps-post-photo-height-trim'])) {
                    $trim_long_photo = (bool) $active_preset['css_vars']['config-ps-post-photo-height-trim'];
                }
            }
        }

        $trim_long_photo_height = apply_filters('gecko_trim_long_photo_height', 650);

        wp_enqueue_script( 'gecko-peepso', gecko_add_cachebust_arg(get_template_directory_uri() . '/assets/js/peepso.js'), array('jquery', 'peepso'), wp_get_theme()->version, true );
        wp_localize_script( 'gecko-peepso', 'geckopeepsodata', array(
            'trim_long_photo' => $trim_long_photo,
            'trim_long_photo_height' => $trim_long_photo_height,
            'text' => array(
                'click_to_expand' => __( 'Click to expand', 'peepso-theme-gecko' )
            )
        ) );
    }

    if ( class_exists( 'woocommerce' ) ) {
        wp_enqueue_style( 'jquery-ui-style', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.min.css', array(), '1.11.4' );
    }
}
add_action( 'wp_enqueue_scripts', 'gecko_scripts' );

function gecko_admin_scripts() {
    wp_enqueue_style( 'gecko-admin-font', gecko_add_cachebust_arg('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700&display=swap'), array(), wp_get_theme()->version );
    wp_enqueue_style( 'gecko-admin-css-19-11-2021', gecko_add_cachebust_arg(get_template_directory_uri() . '/assets/css/admin.css'), array(), wp_get_theme()->version );
    wp_enqueue_script( 'gecko-admin', gecko_add_cachebust_arg(get_template_directory_uri() . '/assets/js/admin.js'), 'jquery' );
    wp_enqueue_style( 'gecko-icons-css', gecko_add_cachebust_arg(get_template_directory_uri() . '/assets/css/icons.css'), array(), wp_get_theme()->version );
}
add_action( 'admin_footer', 'gecko_admin_scripts' );
