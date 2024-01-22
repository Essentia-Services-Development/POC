<?php
// Get search visibility option from admin settings
$gecko_settings = GeckoConfigSettings::get_instance();

// Header settings
$header_vis = get_post_meta(get_proper_ID(), 'gecko-page-hide-header', true);
$header_menu_vis = get_post_meta(get_proper_ID(), 'gecko-page-hide-header-menu', true);
$header_blend = get_post_meta(get_proper_ID(), 'gecko-page-transparent-header', true);
$header_full = get_post_meta(get_proper_ID(), 'gecko-page-full-width-header', true);
$header_position = "";

if ($gecko_settings->get_option('opt_header_full_width', 0 ) == 1) {
    $header_full = TRUE;
}

if(1 == $gecko_settings->get_option('opt_show_header_sidebar_position', 0 ) ) {
    $header_position = "gc-header__sidebar--right";
}

if (is_search() || is_archive()) {
    $header_blend = NULL; // WIRE UP

    if($gecko_settings->get_option( 'opt_search_header_vis', 1 ) == 1) {
        $header_vis = 0;
    } else {
        $header_vis = 1;
    }

    if($gecko_settings->get_option( 'opt_header_menu_search_vis', 1 ) == 1) {
        $header_menu_vis = 0;
    } else {
        $header_menu_vis = 1;
    }
}

$header_search_vis = 1; // Override old setting (Customizer will hide search on Desktop or Mobile with CSS)

//
// MobiLoud
//
if ( GeckoAppHelper::is_app() && PeepSo::get_option('app_gecko_hide_widgets_header-search') ) {
    $header_search_vis = 0;
}
// end: Mobiloud

//
// MobiLoud
//
if ( GeckoAppHelper::is_app() && PeepSo::get_option('app_gecko_hide_header') ) {
    $header_vis = 1;
}
// end: Mobiloud

// Remove search from Landing
if ( is_page_template( 'page-tpl-landing.php' ) ) {
    $header_search_vis = 0;
}

if ($header_blend == 1) {
    add_filter( 'gecko_header_class', function( $classes ) {
        return array_merge( $classes, array( 'gc-header--transparent' ) );
    } );

    add_filter( 'gecko_html_class', function( $classes ) {
        return array_merge( $classes, array( 'header-is-transparent' ) );
    } );
}

if ($header_full == 1) {
    add_filter( 'gecko_header_class', function( $classes ) {
        return array_merge( $classes, array( 'gc-header--full' ) );
    } );
}

if (is_search() && $gecko_settings->get_option( 'opt_search_full_width_header', 0 ) === 1) {
    add_filter( 'gecko_header_class', function( $classes ) {
        return array_merge( $classes, array( 'gc-header--full' ) );
    } );
}

if ($header_vis == 1) {
    add_filter( 'gecko_html_class', function( $classes ) {
        return array_merge( $classes, array( 'header-is-hidden' ) );
    } );
}

if ($gecko_settings->get_option( 'opt_show_sidenav', 0 ) && class_exists('PeepSo')) {
    add_filter( 'body_class', function( $classes ) {
        return array_merge( $classes, array( 'gc-body--sidenav' ) );
    } );
}

if ($gecko_settings->get_option( 'opt_woo_mobile_single_col', 1 )) {
    add_filter( 'body_class', function( $classes ) {
        return array_merge( $classes, array( 'gc-woo--single-col-mobile' ) );
    } );
}

if($gecko_settings->get_option( 'opt_tutorlms_overrides', 1 )) {
    add_filter( 'body_class', function( $classes ) {
        return array_merge( $classes, array( 'gc-tutorlms-overrides' ) );
    } );
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> <?php gecko_html_class('gecko'); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <!--<meta name="viewport" content="width=device-width">-->

        <?php if (0 == $gecko_settings->get_option( 'opt_zoom_feature', 0 ) ) : ?>
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <?php else : ?>
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <?php endif; ?>

        <link rel="profile" href="http://gmpg.org/xfn/11">
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

        <?php wp_head(); ?>

        <!-- if “Enable threaded (nested) comments” in the Discussion Settings
        is activated and if it’s on a single page, then load the script comments-reply.js. -->
        <?php if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' ); ?>

        <style>
            html {
                margin-top: 0 !important;
            }
        </style>
    </head>

    <?php
    $bg_img = ($gecko_settings->get_option( 'gc-body-bg-image', 0 )) ? wp_get_attachment_url($gecko_settings->get_option( 'gc-body-bg-image')) : '';
    ?>

    <body id="body" <?php body_class(); ?> style="<?php if(strlen($bg_img)) { ?>background-image: url(<?php echo $bg_img ?>)<?php } ?>">
        <?php do_action('wp_body_open');?>
        <!-- SIDE NAVIGATION -->
        <?php if ( class_exists('PeepSo') ) : ?>
            <?php if ($gecko_settings->get_option( 'opt_show_sidenav', 0 ) ) : ?>
                <div id="gc-sidenav" class="gc-sidenav">
                    <?php if ($gecko_settings->get_option( 'opt_sidenav_logo', 0 ) ) : ?>
                        <div class="gc-sidenav__header">
                            <!-- Logo -->
                            <?php if ($gecko_settings->get_option( 'opt_logo_link_redirect', 0 ) ) : ?>
                                <?php $logo_url = $gecko_settings->get_option( 'opt_logo_link_redirect', 0 ); ?>
                            <?php else : ?>
                                <?php $logo_url = esc_url( home_url( '/' ) ); ?>
                            <?php endif; ?>

                            <a class="gc-logo__link" href="<?php echo $logo_url; ?>"><img class="gc-logo__image" src="<?php echo wp_get_attachment_url($gecko_settings->get_option( 'opt_sidenav_logo')); ?>" alt="<?php bloginfo( 'name' ); ?>" /></a>
                        </div>
                    <?php endif; ?>

                    <?php
                    $PeepSoProfile=PeepSoProfile::get_instance();
                    $PeepSoUser = $PeepSoProfile->user;
                    ?>

                    <div class="gc-sidenav__middle">
                        <?php if (1 === $header_search_vis) : ?>
                            <a href="javascript:" class="gc-sidenav__search-toggle gc-js-header__search-toggle"><i class="gcis gci-search"></i></a>
                        <?php endif; ?>

                        <?php
                        $items = [
                            'activity' => [
                                'order' => 10,
                                'icon' => 'gcis gci-stream',
                                'label' => __( 'Activity stream', 'peepso-theme-gecko' ),
                                'url' => PeepSo::get_page('activity'),
                                'class' => 'ps-tip',
                            ],
                            'profile_about' => [
                                'order' => 20,
                                'icon' => 'gcis gci-user',
                                'label' => __( 'My Profile', 'peepso-theme-gecko' ),
                                'url' => $PeepSoUser->get_profileurl() . 'about/',
                                'class' => 'ps-tip',
                            ],
                            'members' => [
                                'order' => 30,
                                'icon' => 'gcis gci-user-friends',
                                'label' => __( 'Members', 'peepso-theme-gecko' ),
                                'url' => PeepSo::get_page('members'),
                                'class' => 'ps-tip',
                            ],
                            'profile_preferences' => [
                                'order' => 30,
                                'icon' => 'gcis gci-cog',
                                'label' => __( 'Settings', 'peepso-theme-gecko' ),
                                'url' => $PeepSoUser->get_profileurl() . 'about/preferences/',
                                'class' => 'ps-tip',
                            ],
                        ];

                        // Allow modifications with filter
                        $items = apply_filters('gecko_sidenav_items', $items);

                        // Sort by 'order'
                        usort($items, function($a, $b) {
                            return $a['order'] - $b['order'];
                        });

                        do_action('gecko_sidenav_menu_before');

                        if(count($items)) : ?>
                        <div class="gc-sidenav__menu">
                            <?php do_action('gecko_sidenav_menu_top');?>
                            <?php foreach($items as $key=>$item) : ?>
                                <a class="<?php echo esc_attr($item['class']);?>" href="<?php echo esc_url($item['url']); ?>" aria-label="<?php echo esc_html( $item['label']);?>">
                                    <i class="<?php echo esc_attr($item['icon']);?>"></i>
                                </a>
                            <?php endforeach; ?>
                            <?php do_action('gecko_sidenav_menu_bottom');?>
                        </div>
                        <?php endif; ?>

                        <?php do_action('gecko_sidenav_menu_after');?>

                        <div class="gc-sidenav__notifs">
                            <?php the_widget( 'PeepSoWidgetUserBar', array('compact_mode' => 0, 'show_notifications' => 1) ); ?>
                        </div>
                    </div>

                    <a class="gc-sidenav__logout ps-tip" href="<?php echo PeepSo::get_page('logout'); ?>" aria-label="<?php esc_html_e( 'Log Out', 'peepso-theme-gecko' ); ?>"><i class="gcis gci-power-off"></i></a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- HEADER -->
        <?php do_action('gecko_before_header'); ?>

        <div <?php gecko_header_class('gc-header__wrapper gc-js-header-wrapper'); ?>>
            <?php
            // Sticky Top - Above header visibility
            $sticky_bar_above_header_vis = 1;
            $sticky_bar_above_header_full_width = $gecko_settings->get_option( 'opt_sticky_bar_above_full_width', 0 );

            if ( GeckoAppHelper::is_app() && PeepSo::get_option('app_gecko_hide_widgets_sticky-top-above-header-widgets') ) {
                $sticky_bar_above_header_vis = 0;
            }
            ?>

            <?php
            if ( is_active_sidebar( 'sticky-top-above-header-widgets' ) && $sticky_bar_above_header_vis === 1) : ?>
            <div class="gc-sticky__bar gc-sticky__bar--above-header gc-js-sticky-bar-above-header <?php echo ($sticky_bar_above_header_full_width) ? 'gc-sticky__bar--full' : ''; ?>">
                <div class="gc-widgets">
                    <div class="gc-widgets__inner">
                        <div class="gc-widgets__grid">
                            <?php dynamic_sidebar( 'sticky-top-above-header-widgets' ); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?><!-- end: Sticky top bar - above header -->

            <?php if ($gecko_settings->get_option( 'opt_header_vis', 1 ) === 1) : ?>
            <?php if (!$header_vis) : ?>
                <?php
                  $header_inner_vis = 1; // show header inner as default

                  if (is_Gecko_MegaMenu()) {
                    $header_inner_vis = 0; // hide header inner if MegaMenu is enabled
                    wp_nav_menu( array( 'theme_location' => 'primary-menu', 'items_wrap' => '%3$s', 'fallback_cb' => false,'container' => false ) );
                  }
                ?>

                <?php if ($header_inner_vis) : ?>
                <div class="gc-header gc-js-header">
                    <div class="gc-header__inner">
                        <!-- Logo -->
                        <?php if ($gecko_settings->get_option( 'opt_logo_link_redirect', 0 ) ) : ?>
                            <?php $logo_url = $gecko_settings->get_option( 'opt_logo_link_redirect', 0 ); ?>
                        <?php else : ?>
                            <?php $logo_url = esc_url( home_url( '/' ) ); ?>
                        <?php endif; ?>

                        <?php if ($gecko_settings->get_option( 'opt_header_logo_mobile_vis', 1 ) ) : ?>
                            <?php if ($gecko_settings->get_option( 'opt_custom_mobile_logo', 0 ) ) : ?>
                                <div class="gc-header__logo gc-header__logo--mobile">
                                    <a class="gc-logo__link" href="<?php echo $logo_url; ?>"><img class="gc-logo__image" src="<?php echo wp_get_attachment_url($gecko_settings->get_option( 'opt_custom_mobile_logo')); ?>" alt="<?php bloginfo( 'name' ); ?>" /></a>

                                    <?php if (get_bloginfo( 'description' ) && $gecko_settings->get_option( 'opt_header_tagline_vis', 0 ) && $gecko_settings->get_option( 'opt_header_tagline_mobile_vis', 0 )) : ?>
                                        <div class="gc-logo__tagline"><?php echo get_bloginfo( 'description' ); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($gecko_settings->get_option( 'opt_header_logo_desktop_vis', 1 ) ) : ?>
                            <div class="gc-header__logo">
                                <?php if (has_custom_logo()) : ?>
                                    <div class="gc-logo__image"><?php echo the_custom_logo(); ?></div>
                                <?php else : ?>
                                    <a class="gc-logo__link" href="<?php echo $logo_url; ?>"><h1><?php bloginfo( 'name' ); ?></h1></a>
                                <?php endif; ?>

                                <?php if (get_bloginfo( 'description' ) && $gecko_settings->get_option( 'opt_header_tagline_vis', 0 )) : ?>
                                    <?php
                                    $tagline = $gecko_settings->get_option( 'opt_header_tagline_mobile_vis', 0 );
                                    ?>
                                    <div class="gc-logo__tagline <?php if (!$tagline) { echo 'gc-logo__tagline--mobile'; } ?>"><?php echo get_bloginfo( 'description' ); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!$header_menu_vis) : ?>
                          <!-- Header Menu -->
                          <?php if ($gecko_settings->get_option( 'opt_enable_longmenu', 0 )) : ?>
                            <div class="hm-header__menu gc-header__menu hm-header__menu--short">
                              <ul class="hm-header__menu-list">
                                <?php wp_nav_menu( array( 'theme_location' => 'primary-menu', 'items_wrap' => '%3$s', 'fallback_cb' => false,'container' => false ) ); ?>
                              </ul>

                              <a class="hm-header__menu-toggle" href="javascript:">
                                <i class="gcis gci-bars"></i>
                              </a>

                              <ul class="hm-header__menu-more hidden"></ul>
                            </div>

                            <?php if (1 === $header_search_vis) : ?>
                                <!-- Header Search -->
                                <div class="gc-header__search">
                                    <a href="javascript:" class="gc-header__search-toggle gc-js-header__search-toggle"><i class="gcis gci-search"></i></a>

                                    <div class="gc-header__search-box">
                                        <div class="gc-header__search-box-inner">
                                            <div class="gc-header__search-input-wrapper">
                                                <i class="gcis gci-search"></i>
                                                <?php if (is_active_sidebar( 'header-search' )) : ?>
                                                    <?php dynamic_sidebar( 'header-search' ); ?>
                                                <?php else : ?>
                                                    <?php
                                                    $form_action = home_url( '/' );
                                                    $search_query = get_search_query();
                                                    if(class_exists('PeepSo') && is_callable('PeepSo::is_dev_mode')) {

                                                        if(PeepSo::is_dev_mode('new_search')) {
                                                            $form_action = PeepSo::get_page('search');

                                                            if (class_exists('PeepSo3_Shortcode_Search') && is_callable('PeepSo3_Shortcode_Search::get_search_query')) {
                                                                $search_query = PeepSo3_Shortcode_Search::get_search_query();
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    <form action="<?php echo $form_action; ?>" method="get" class="gc-header__search-form">
                                                        <input type="text" class="gc-header__search-input" name="s" placeholder="<?php esc_attr_e( 'Type to search', 'peepso-theme-gecko'); ?>..." id="search" value="<?php echo $search_query; ?>" />
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                            <a href="javascript:" class="gc-header__search-toggle gc-js-header__search-toggle"><i class="gcis gci-times"></i></a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                          <?php else : ?>
                            <div class="gc-header__menu">
                                <ul>
                                    <?php wp_nav_menu( array( 'theme_location' => 'primary-menu', 'items_wrap' => '%3$s', 'fallback_cb' => false,'container' => false ) ); ?>
                                </ul>

                                <?php if (1 === $header_search_vis) : ?>
                                    <!-- Header Search -->
                                    <div class="gc-header__search">
                                        <a href="javascript:" class="gc-header__search-toggle gc-js-header__search-toggle"><i class="gcis gci-search"></i></a>

                                        <div class="gc-header__search-box">
                                            <div class="gc-header__search-box-inner">
                                                <div class="gc-header__search-input-wrapper">
                                                    <i class="gcis gci-search"></i>
                                                    <?php if (is_active_sidebar( 'header-search' )) : ?>
                                                        <?php dynamic_sidebar( 'header-search' ); ?>
                                                    <?php else : ?>
                                                        <?php
                                                        $form_action = home_url( '/' );
                                                        $search_query = get_search_query();
                                                        if(class_exists('PeepSo') && is_callable('PeepSo::is_dev_mode')) {

                                                            if(PeepSo::is_dev_mode('new_search')) {
                                                                $form_action = PeepSo::get_page('search');

                                                                if (class_exists('PeepSo3_Shortcode_Search') && is_callable('PeepSo3_Shortcode_Search::get_search_query')) {
                                                                    $search_query = PeepSo3_Shortcode_Search::get_search_query();
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                        <form action="<?php echo $form_action; ?>" method="get" class="gc-header__search-form">
                                                            <input type="text" class="gc-header__search-input" name="s" placeholder="<?php esc_attr_e( 'Type to search', 'peepso-theme-gecko'); ?>..." id="search" value="<?php echo $search_query; ?>" />
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                                <a href="javascript:" class="gc-header__search-toggle gc-js-header__search-toggle"><i class="gcis gci-times"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                          <?php endif; ?>
                        <?php endif; ?>

                        <!-- Header Addons -->
                        <?php
                        $header_widget_vis = 1;
                        $header_cart_vis = 1;

                        //
                        // MobiLoud
                        //
                        // Header widgets:
                        if ( GeckoAppHelper::is_app() && PeepSo::get_option('app_gecko_hide_widgets_header-widgets') ) {
                            $header_widget_vis = 0;
                        }
                        // Header cart:
                        if ( GeckoAppHelper::is_app() && PeepSo::get_option('app_gecko_hide_widgets_header-cart') ) {
                            $header_cart_vis = 0;
                        }
                        // end: Mobiloud
                        ?>
                        <?php if (is_active_sidebar( 'header-widgets' ) && $header_widget_vis === 1) : ?>
                            <div class="gc-header__addons">
                                <!-- Header Widget -->
                                <div class="gc-header__widget">
                                    <?php dynamic_sidebar( 'header-widgets' ); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (is_active_sidebar( 'header-cart' ) && $header_cart_vis === 1) : ?>
                            <div class="gc-header__cart-wrapper">
                                <?php dynamic_sidebar( 'header-cart' ); ?>
                                <a href="javascript:" class="gc-header__cart-toggle js-header-cart-toggle empty">
                                    <i class="gcis gci-shopping-basket"></i>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php do_action('gecko_after_header_cart'); ?>

                        <a href="#" class="gc-header__menu-toggle gc-js-header-menu-open">
                            <i class="gcis gci-bars"></i>
                        </a>
                    </div>
                </div><!-- .gc-header -->

                <div class="gc-header__sidebar <?php echo $header_position; ?> gc-js-header-sidebar">
                    <div class="gc-header__sidebar-inner">
                        <?php if ($gecko_settings->get_option( 'opt_show_header_sidebar_logo', 1 )) : ?>
                            <div class="gc-header__sidebar-logo">
                                <!-- Logo -->
                                <?php if ($gecko_settings->get_option( 'opt_logo_link_redirect', 0 ) ) : ?>
                                    <?php $logo_url = $gecko_settings->get_option( 'opt_logo_link_redirect', 0 ); ?>
                                <?php else : ?>
                                    <?php $logo_url = esc_url( home_url( '/' ) ); ?>
                                <?php endif; ?>
                                <?php if ($gecko_settings->get_option( 'opt_custom_mobile_logo', 0 ) ) : ?>
                                    <div class="gc-header__logo gc-header__logo--mobile">
                                        <a class="gc-logo__link" href="<?php echo $logo_url; ?>"><img class="gc-logo__image" src="<?php echo wp_get_attachment_url($gecko_settings->get_option( 'opt_custom_mobile_logo')); ?>" alt="<?php bloginfo( 'name' ); ?>" /></a>
                                    </div>
                                <?php endif; ?>
                                <div class="gc-header__logo">
                                    <?php if (has_custom_logo()) : ?>
                                        <div class="gc-logo__image"><?php echo the_custom_logo(); ?></div>
                                    <?php else : ?>
                                        <a class="gc-logo__link" href="<?php echo $logo_url; ?>"><h1><?php bloginfo( 'name' ); ?></h1></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (is_active_sidebar( 'mobile-menu-above' )) : ?>
                            <div class="gc-header__sidebar-widget gc-header__sidebar-widget--above">
                                <?php dynamic_sidebar( 'mobile-menu-above' ); ?>
                            </div>
                        <?php endif; ?>
                        <ul class="gc-header__sidebar-menu">
                            <?php if (has_nav_menu ('mobile-menu')) : ?>
                                <?php wp_nav_menu( array( 'theme_location' => 'mobile-menu', 'items_wrap' => '%3$s', 'container' => false, 'fallback_cb' => false ) ); ?>
                            <?php else : ?>
                                <?php wp_nav_menu( array( 'theme_location' => 'primary-menu', 'items_wrap' => '%3$s', 'container' => false, 'fallback_cb' => false ) ); ?>
                            <?php endif; ?>
                        </ul>
                        <?php if (is_active_sidebar( 'mobile-menu-under' )) : ?>
                            <div class="gc-header__sidebar-widget gc-header__sidebar-widget--under">
                                <?php dynamic_sidebar( 'mobile-menu-under' ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="gc-header__sidebar-overlay gc-js-header-menu-close">
                    <a href="javascript:" class="gc-header__sidebar-close"><i class="gcis gci-times"></i></a>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            <?php endif; ?>

            <?php
            // Sticky Top - Under header visibility
            $sticky_bar_under_header_vis = 1;
            $sticky_bar_under_header_full_width = $gecko_settings->get_option( 'opt_sticky_bar_under_full_width', 0 );

            if ( GeckoAppHelper::is_app() && PeepSo::get_option('app_gecko_hide_widgets_sticky-top-under-header-widgets') ) {
                $sticky_bar_under_header_vis = 0;
            }
            ?>

            <?php
            if ( is_active_sidebar( 'sticky-top-under-header-widgets' ) && $sticky_bar_under_header_vis === 1) : ?>
            <div class="gc-sticky__bar gc-sticky__bar--under-header gc-js-sticky-bar-under-header <?php echo ($sticky_bar_under_header_full_width) ? 'gc-sticky__bar--full' : ''; ?>">
                <div class="gc-widgets">
                    <div class="gc-widgets__inner">
                        <div class="gc-widgets__grid">
                            <?php dynamic_sidebar( 'sticky-top-under-header-widgets' ); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?><!-- end: Sticky top bar - under header -->

            <?php
            // Mobile Sticky Top - Only for Mobile App
            $sticky_bar_mobile_header_full_width = $gecko_settings->get_option( 'opt_sticky_bar_mobile_full_width', 0 );

            if ( GeckoAppHelper::is_app() && is_active_sidebar( 'mobi-sticky-top-widgets' ) && $gecko_settings->get_option( 'opt_app_widget_positions', 0 )) : ?>
            <div class="gc-sticky__bar gc-sticky__bar--mobile gc-js-sticky-bar-mobile <?php echo ($sticky_bar_mobile_header_full_width) ? 'gc-sticky__bar--full' : ''; ?>">
                <div class="gc-widgets">
                    <div class="gc-widgets__inner">
                        <div class="gc-widgets__grid">
                            <?php dynamic_sidebar( 'mobi-sticky-top-widgets' ); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?><!-- end: Mobile Sticky Top -->
        </div><!-- .gc-header__wrapper -->

        <?php do_action('gecko_after_header'); ?>
        <!-- end: HEADER -->

        <?php if (! is_page_template( 'page-tpl-landing.php' ) ) : ?>
            <!-- TOP WIDGETS -->
            <?php get_template_part( 'template-parts/widgets/top' ); ?>
            <!-- end: TOP WIDGETS -->
        <?php endif; ?>
