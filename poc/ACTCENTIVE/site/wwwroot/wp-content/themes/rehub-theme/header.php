<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<!-- feeds & pingback -->
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php do_action('theme_critical_css');?>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php if(function_exists('wp_body_open')){wp_body_open();}?>
<?php 
?>
<?php 
    if (rehub_option('header_topline_style') == '0') {
        $header_topline_style = ' white_style';
    }
    elseif (rehub_option('header_topline_style') == '1') {
        $header_topline_style = ' dark_style';
    }
    else {
        $header_topline_style = ' white_style';
    }    
?>
<?php 
    if (rehub_option('header_logoline_style') == '0') {
        $header_logoline_style = 'white_style';
    }
    elseif (rehub_option('header_logoline_style') == '1') {
        $header_logoline_style = 'dark_style';
    }
    else {
        $header_logoline_style = 'white_style';
    }    
?>
<?php 
    if (rehub_option('header_menuline_style') == '0') {
        $header_menuline_style = ' white_style';
    }
    elseif (rehub_option('header_menuline_style') == '1') {
        $header_menuline_style = ' dark_style';
    }
    else {
        $header_menuline_style = ' dark_style';
    }    
?>
<?php $branded_bg_url = rehub_option('rehub_branded_bg_url');?>
<?php if ($branded_bg_url ) :?>
  <a id="branded_bg" href="<?php echo esc_url($branded_bg_url); ?>" target="_blank" rel="sponsored"></a>
<?php endif; ?>
<?php if(rehub_option('rehub_ads_megatop') !='') : ?>
	<div class="megatop_wrap">
		<div class="mediad megatop_mediad floatnone text-center flowhidden">
			<?php echo do_shortcode(rehub_option('rehub_ads_megatop')); ?>
		</div>
	</div>
<?php endif ;?>	               
<!-- Outer Start -->
<div class="rh-outer-wrap">
    <div id="top_ankor"></div>
    <!-- HEADER -->
    <?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) :?>
        <header id="main_header" class="<?php echo ''.$header_logoline_style; ?> width-100p position-relative">
            <div class="header_wrap">
                <?php if(rehub_option('rehub_header_top_enable') =='1')  : ?>  
                    <!-- top -->  
                    <div class="header_top_wrap<?php echo ''.$header_topline_style;?>">
                        <?php echo rh_generate_incss('headertopline');?>
                        <div class="rh-container">
                            <div class="header-top clearfix rh-flex-center-align">    
                                <?php wp_nav_menu( array( 'container_class' => 'top-nav', 'container' => 'div', 'theme_location' => 'top-menu', 'fallback_cb' => 'add_top_menu_for_blank', 'depth' => '2', 'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>'  ) ); ?>
                                <div class="rh-flex-right-align top-social"> 
                                    <?php if(rehub_option('rehub_top_line_content')) : ?>
                                        <div class="top_custom_content mt10 mb10 font80 lineheight15 flowhidden"><?php echo do_shortcode(rehub_option('rehub_top_line_content'));?></div>
                                    <?php endif; ?>                                                      
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /top --> 
                <?php endif; ?>
                <?php $header_template = (rehub_option('rehub_header_style') !='') ? rehub_option('rehub_header_style') : 'header_seven' ;?>
                <?php if($header_template == 'header_second' || $header_template == 'header_four' || $header_template == 'header_nine') {$header_template = 'header_seven';}?>
                <?php if(is_numeric($header_template) && function_exists('rh_wp_reusable_render')):?>
                
                    <div class="header_clean_style clearfix pt0 pb0 <?php if (rehub_option('rehub_sticky_nav') ==true){echo 'rh-stickme ';}?>">                      
                        <?php echo rh_wp_reusable_render(array('id' => $header_template));?>                  
                    </div>
                    
                <?php else:?>
                    <?php include(rh_locate_template('inc/header_layout/'.$header_template.'.php')); ?>
                <?php endif;?>


            </div>  
        </header>
    <?php endif;?>
    <?php include(rh_locate_template('inc/parts/branded_banner.php')); ?>
    <?php do_action('rehub_action_after_header'); ?>