<?php

//
//  ADD WIDGET AREAS
//
function gecko_widgets_init() {
    $gecko_settings = GeckoConfigSettings::get_instance();

    register_sidebar( array(
        'name'          => 'Header',
        'description'   => __( 'Widgets & Blocks will be displayed on header bar, next to header menu.', 'peepso-theme-gecko' ),
        'id'            => 'header-widgets',
        'before_widget' => '<div id="%1$s" class="header__widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3>',
        'after_title'   => '</h3>',
    ));

    register_sidebar( array(
        'name'          => 'Header (Search)',
        'description'   => __( 'Dedicated area for search widgets, will be displayed on header bar.', 'peepso-theme-gecko' ),
        'id'            => 'header-search',
        'before_widget' => '<div id="%1$s" class="header__widget header__widget--search %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3>',
        'after_title'   => '</h3>',
    ));

    register_sidebar( array(
        'name'          => 'Header (Cart)',
        'description'   => __( 'Dedicated area for shopping cart widgets, will be displayed on header bar.', 'peepso-theme-gecko' ),
        'id'            => 'header-cart',
        'before_widget' => '<div id="%1$s" class="gc-header__cart %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3>',
        'after_title'   => '</h3>',
    ));

    register_sidebar( array(
        'name'          => 'Mobile menu (above)',
        'id'            => 'mobile-menu-above',
        'before_widget' => '<div id="%1$s" class="gc-widget gc-header__sidebar-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3>',
        'after_title'   => '</h3>',
    ));

    register_sidebar( array(
        'name'          => 'Mobile menu (under)',
        'id'            => 'mobile-menu-under',
        'before_widget' => '<div id="%1$s" class="gc-widget gc-header__sidebar-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3>',
        'after_title'   => '</h3>',
    ));

    register_sidebar( array(
        'name'          => 'Slider',
        'description'   => __( 'Designed for content slider, will be displayed under header.', 'peepso-theme-gecko' ),
        'id'            => 'slider-widgets',
        'before_widget' => '<div id="%1$s" class="slider__widget %2$s">',
        'after_widget'  => '</div>',
    ) );

    register_sidebar( array(
        'name'          => 'Top',
        'description'   => __( 'Widgets added to this area will be displayed above content and sidebars.', 'peepso-theme-gecko' ),
        'id'            => 'top-widgets',
        'before_widget' => '<div id="%1$s" class="gc-widget gc-widget--top top__widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="gc-widget__title"><h3>',
        'after_title'   => '</h3></div>',
    ) );

    register_sidebar( array(
        'name'          => 'Sticky Top (Above header)',
        'id'            => 'sticky-top-above-header-widgets',
        'before_widget' => '<div id="%1$s" class="gc-widget gc-widget--sticky-top sticky-top__widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="gc-widget__title"><h3>',
        'after_title'   => '</h3></div>',
    ) );

    register_sidebar( array(
        'name'          => 'Sticky Top (Under header)',
        'id'            => 'sticky-top-under-header-widgets',
        'before_widget' => '<div id="%1$s" class="gc-widget gc-widget--sticky-top sticky-top__widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="gc-widget__title"><h3>',
        'after_title'   => '</h3></div>',
    ) );

    register_sidebar( array(
        'name'          => 'Above content',
        'description'   => __( 'Widgets added to this area will be displayed above content but between sidebars.', 'peepso-theme-gecko' ),
        'id'            => 'above-content-widgets',
        'before_widget' => '<div id="%1$s" class="gc-widget gc-widget--above-content above-content__widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="gc-widget__title"><h3>',
        'after_title'   => '</h3></div>',
    ) );

    if(class_exists('PeepSoAppPlugin') && $gecko_settings->get_option( 'opt_app_widget_positions', 0 )) {
        register_sidebar(array(
            'name' => 'Top (Mobile App, Sticky)',
            'id' => 'mobi-sticky-top-widgets',
            'before_widget' => '<div id="%1$s" class="gc-widget gc-widget--mobi gc-widget--top top__widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<div class="gc-widget__title"><h3>',
            'after_title' => '</h3></div>',
        ));

        register_sidebar(array(
            'name' => 'Top (Mobile App)',
            'id' => 'mobi-top-widgets',
            'before_widget' => '<div id="%1$s" class="gc-widget gc-widget--mobi gc-widget--top top__widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<div class="gc-widget__title"><h3>',
            'after_title' => '</h3></div>',
        ));
    }

    register_sidebar( array(
        'name'          => 'Sidebar Left',
        'id'            => 'sidebar-left',
        'before_widget' => '<div id="%1$s" class="gc-widget gc-widget--sidebar %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="gc-widget__title"><h3>',
        'after_title'   => '</h3></div>',
    ) );

    register_sidebar( array(
        'name'          => 'Sidebar Right',
        'id'            => 'sidebar-right',
        'before_widget' => '<div id="%1$s" class="gc-widget gc-widget--sidebar %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="gc-widget__title"><h3>',
        'after_title'   => '</h3></div>',
    ) );

    register_sidebar( array(
        'name'          => 'Under content',
        'description'   => __( 'Widgets added to this area will be displayed under content but between sidebars.', 'peepso-theme-gecko' ),
        'id'            => 'under-content-widgets',
        'before_widget' => '<div id="%1$s" class="gc-widget gc-widget--under-content under-content__widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="gc-widget__title"><h3>',
        'after_title'   => '</h3></div>',
    ) );

    register_sidebar( array(
        'name'          => 'Bottom',
        'description'   => __( 'Widgets added to this area will be displayed under content and sidebars.', 'peepso-theme-gecko' ),
        'id'            => 'bottom-widgets',
        'before_widget' => '<div id="%1$s" class="gc-widget gc-widget--bottom bottom__widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="gc-widget__title"><h3>',
        'after_title'   => '</h3></div>',
    ) );

    if(class_exists('PeepSoAppPlugin') && $gecko_settings->get_option( 'opt_app_widget_positions', 0 )) {
        register_sidebar(array(
            'name' => 'Bottom (Mobile App)',
            'id' => 'mobi-bottom-widgets',
            'before_widget' => '<div id="%1$s" class="gc-widget gc-widget--mobi gc-widget--top top__widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<div class="gc-widget__title"><h3>',
            'after_title' => '</h3></div>',
        ));
    }

    register_sidebar( array(
        'name'          => 'Footer',
        'id'            => 'footer-widgets',
        'before_widget' => '<div id="%1$s" class="gc-widget gc-widget--footer footer__widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="gc-widget__title"><h3>',
        'after_title'   => '</h3></div>',
    ) );

    register_sidebar( array(
        'name'          => 'Footer (Social)',
        'id'            => 'footer-social',
        'before_widget' => '<div id="%1$s" class="gc-widget gc-widget--footer footer__widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '',
        'after_title'   => '',
    ) );

    register_sidebar( array(
        'name'          => 'Landing',
        'id'            => 'landing',
        'before_widget' => '<div id="%1$s" class="gc-widget gc-widget--landing %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="gc-widget__title"><h3>',
        'after_title'   => '</h3></div>',
    ) );
}
add_action( 'widgets_init', 'gecko_widgets_init', -1 );


//
//  Custom options for widgets
//
function widgets_scripts( $hook ) {
    if ( 'widgets.php' != $hook ) {
        return;
    }
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script(
        'gecko-admin-widgets',
        gecko_add_cachebust_arg(get_template_directory_uri() . '/assets/js/admin-widgets.js'),
        array('wp-color-picker'),
        wp_get_theme()->version
    );
}
add_action( 'admin_enqueue_scripts', 'widgets_scripts' );

function gecko_in_widget_form($t,$return,$instance){
    $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '', 'style' => '', 'gradient_color_1' => 'var(--s-widget--gradient-bg)', 'gradient_color_2' => 'var(--s-widget--gradient-bg-2)', 'gradient_text' => 'var(--s-widget--gradient-text)', 'gradient_links' => 'var(--s-widget--gradient-links)') );
    if ( !isset($instance['style']) )
        $instance['style'] = null;

    if ( !isset($instance['gradient_color_1']) )
        $instance['gradient_color_1'] = "var(--s-widget--gradient-bg)";

    if ( !isset($instance['gradient_color_2']) )
        $instance['gradient_color_2'] = "var(--s-widget--gradient-bg-2)";

    if ( !isset($instance['gradient_text']) )
        $instance['gradient_text'] = "var(--s-widget--gradient-color)";

    if ( !isset($instance['gradient_links']) )
        $instance['gradient_links'] = "var(--s-widget--gradient-links)";
    ?>

    <hr>

    <h3>Gecko options:</h3>
    <div data-gc="widget-form" style="background:#eee; padding:10px; margin-bottom:10px;">
        <label for="<?php echo $t->get_field_id('style'); ?>">Widget style:</label>
        <select id="<?php echo $t->get_field_id('style'); ?>" name="<?php echo $t->get_field_name('style'); ?>" data-gc="widget-style">
            <option <?php selected($instance['style'], 'none');?> value="none">Default</option>
            <option <?php selected($instance['style'], 'bordered');?> value="bordered">Bordered</option>
            <option <?php selected($instance['style'], 'gradient');?> value="gradient">Gradient</option>
            <option <?php selected($instance['style'], 'clean');?> value="clean">Clean (no style)</option>
        </select>

        <!-- COLOR PICKER -->
        <?php
        $gradients_visibility = '';
        if ( 'gradient' !== $instance['style'] ) {
            $gradients_visibility = 'display:none';
        }
        ?>
        <div id="widget-gradient-colors" class="widget-gradient-colors" data-gc="widget-gradients"
             style="<?php echo $gradients_visibility ?>">
            <hr>
            <p style="margin-top:0;"><strong>Gradient colors:</strong></p>
            <p>
                <label style="margin-right: 10px;" for="<?php echo esc_attr( $t->get_field_id( 'gradient_color_1' ) ); ?>">
                    <?php _e( 'Background Color 1', 'peepso-theme-gecko'   ); ?></label>
                <input type="text" id="<?php echo esc_attr( $t->get_field_id( 'gradient_color_1' ) ); ?>" name="<?php echo esc_attr( $t->get_field_name( 'gradient_color_1' ) ); ?>" value="<?php echo $instance['gradient_color_1']; ?>" class="my-color-picker"/>
            </p>
            <p style="margin-bottom:0;">
                <label style="margin-right: 10px;" for="<?php echo esc_attr( $t->get_field_id( 'gradient_color_2' ) ); ?>">
                    <?php _e( 'Background Color 2', 'peepso-theme-gecko'   ); ?></label>
                <input type="text" id="<?php echo esc_attr( $t->get_field_id( 'gradient_color_2' ) ); ?>" name="<?php echo esc_attr( $t->get_field_name( 'gradient_color_2' ) ); ?>" value="<?php echo $instance['gradient_color_2']; ?>" class="my-color-picker-2"/>
            </p>
            <p style="margin-bottom:0;">
                <label style="margin-right: 10px;" for="<?php echo esc_attr( $t->get_field_id( 'gradient_text' ) ); ?>">
                    <?php _e( 'Text color', 'peepso-theme-gecko'   ); ?></label>
                <input type="text" id="<?php echo esc_attr( $t->get_field_id( 'gradient_text' ) ); ?>" name="<?php echo esc_attr( $t->get_field_name( 'gradient_text' ) ); ?>" value="<?php echo $instance['gradient_text']; ?>" class="my-color-picker-3"/>
            </p>
            <p style="margin-bottom:0;">
                <label style="margin-right: 10px;" for="<?php echo esc_attr( $t->get_field_id( 'gradient_links' ) ); ?>">
                    <?php _e( 'Links color', 'peepso-theme-gecko'   ); ?></label>
                <input type="text" id="<?php echo esc_attr( $t->get_field_id( 'gradient_links' ) ); ?>" name="<?php echo esc_attr( $t->get_field_name( 'gradient_links' ) ); ?>" value="<?php echo $instance['gradient_links']; ?>" class="my-color-picker-4"/>
            </p>
        </div><!-- end: COLOR PICKER -->
    </div>
    <?php
    $retrun = null;
    return array($t,$return,$instance);
}

function gecko_in_widget_form_update($instance, $new_instance, $old_instance){
    $instance['style'] = $new_instance['style'];
    $instance['gradient_color_1'] = $new_instance['gradient_color_1'];
    $instance['gradient_color_2'] = $new_instance['gradient_color_2'];
    $instance['gradient_text'] = $new_instance['gradient_text'];
    $instance['gradient_links'] = $new_instance['gradient_links'];
    return $instance;
}

function gecko_dynamic_sidebar_params($params){
    if (!isset($params[0]['widget_id'])) {
        return $params;
    }
    global $wp_registered_widgets;
    $widget_id = $params[0]['widget_id'];
    $widget_obj = $wp_registered_widgets[$widget_id];
    if (isset($widget_obj['original_callback'][0]->option_name)) {
        $widget_opt = get_option($widget_obj['original_callback'][0]->option_name);
    } elseif(isset($widget_obj['callback'][0]->option_name)) {
        $widget_opt = get_option($widget_obj['callback'][0]->option_name);
    } else {
        $widget_opt = [];
    }
    $widget_num = $widget_obj['params'][0]['number'];

    $style = "";

    $color1 = "inherit";
    $color2 = "inherit";
    $color3 = "inherit";
    $color4 = "inherit";

    // Override with instance vars in WP preview
    if(isset($params['instance']) && is_array($params['instance'])) {

        $instance = $params['instance'];

        if (isset($instance['gradient_color_1'])) {
            $widget_opt[$widget_num]['gradient_color_1'] = $instance['gradient_color_1'];
        }

        if (isset($instance['gradient_color_2'])) {
            $widget_opt[$widget_num]['gradient_color_2'] = $instance['gradient_color_2'];
        }

        if (isset($instance['gradient_color_3'])) {
            $widget_opt[$widget_num]['gradient_color_3'] = $instance['gradient_color_3'];
        }

        if (isset($instance['gradient_text'])) {
            $widget_opt[$widget_num]['gradient_text'] = $instance['gradient_text'];
        }

        if (isset($instance['gradient_links'])) {
            $widget_opt[$widget_num]['gradient_links'] = $instance['gradient_links'];
        }

        if (isset($instance['style'])) {
            $widget_opt[$widget_num]['style'] = $instance['style'];
        }
    }

    if(isset($widget_opt[$widget_num]['gradient_color_1'])) {
        $color1 = $widget_opt[$widget_num]['gradient_color_1'];
    }

    if(isset($widget_opt[$widget_num]['gradient_color_2'])) {
        $color2 = $widget_opt[$widget_num]['gradient_color_2'];
    }

    if(isset($widget_opt[$widget_num]['gradient_text'])) {
        $color3 = $widget_opt[$widget_num]['gradient_text'];
    }

    if(isset($widget_opt[$widget_num]['gradient_links'])) {
        $color4 = $widget_opt[$widget_num]['gradient_links'];
    }

    if(isset($widget_opt[$widget_num]['style']))
        $style = 'gc-widget--' . $widget_opt[$widget_num]['style'];
    else
        $style = '';

    if(isset($_GET['legacy-widget-preview'])) {
        $style .= ' gc-widget gc-widget--preview ';
    }

    if(!isset($params[0]['before_widget'])) {
        $params[0]['before_widget']='<div class="">';
    }

    if(!isset($params[0]['after_widget'])) {
        $params[0]['after_widget']='</div>';
    }

    $params[0]['before_widget'] = preg_replace('/class="/', 'style="--widget--gradient-bg: '.$color1.'; --widget--gradient-bg-2: '.$color2.'; --widget--gradient-color: '.$color3.'; --widget--gradient-links: '.$color4.'; --widget--gradient-links-hover: '.$color3.';" class="'.$style.' ',  $params[0]['before_widget'], 1);
    return $params;
}

//Add input fields(priority 5, 3 parameters)
add_action('in_widget_form', 'gecko_in_widget_form',5,3);
//Callback function for options update (priorität 5, 3 parameters)
add_filter('widget_update_callback', 'gecko_in_widget_form_update',5,3);
//add class names (default priority, one parameter)
add_filter('dynamic_sidebar_params', 'gecko_dynamic_sidebar_params');
add_filter('peepso_legacy_widget_preview_args', 'gecko_dynamic_sidebar_params');

//
//add_filter('peepso_legacy_widget_preview_args', function($args) {
//    global $wp_registered_widgets;
//    $widget_id = $args['widget_id'];
//    $widget_obj = $wp_registered_widgets[$widget_id];
//    if (isset($widget_obj['original_callback'][0]->option_name)) {
//        $widget_opt = get_option($widget_obj['original_callback'][0]->option_name);
//    } else {
//        $widget_opt = get_option($widget_obj['callback'][0]->option_name);
//    }
//
//    $widget_num = $widget_obj['params'][0]['number'];
//
//    $style = "";
//
//    $color1 = "inherit";
//    $color2 = "inherit";
//    $color3 = "inherit";
//    $color4 = "inherit";
//
//    if(isset($widget_opt[$widget_num]['gradient_color_1'])) {
//        $color1 = $widget_opt[$widget_num]['gradient_color_1'];
//    }
//
//    if(isset($widget_opt[$widget_num]['gradient_color_2'])) {
//        $color2 = $widget_opt[$widget_num]['gradient_color_2'];
//    }
//
//    if(isset($widget_opt[$widget_num]['gradient_text'])) {
//        $color3 = $widget_opt[$widget_num]['gradient_text'];
//    }
//
//    if(isset($widget_opt[$widget_num]['gradient_links'])) {
//        $color4 = $widget_opt[$widget_num]['gradient_links'];
//    }
//
//    if(isset($widget_opt[$widget_num]['style']))
//        $style = 'gc-widget--' . $widget_opt[$widget_num]['style'];
//    else
//        $style = '';
//    $args['before_widget'] = preg_replace('/class="/', 'style="--widget--gradient-bg: '.$color1.'; --widget--gradient-bg-2: '.$color2.'; --widget--gradient-color: '.$color3.'; --widget--gradient-links: '.$color4.'; --widget--gradient-links-hover: '.$color3.';" class="'.$style.' ',  $args['before_widget'], 1);
//    return $args;
//});
