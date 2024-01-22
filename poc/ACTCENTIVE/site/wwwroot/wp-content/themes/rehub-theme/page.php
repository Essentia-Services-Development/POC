<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php 
    global $post;
    $postID = $post->ID;
    $header_disable = $footer_disable = $content_type = $page_class = $content_class = $enable_preloader = ''; 

    if(!$header_disable) $header_disable = get_post_meta($postID, "_header_disable", true);
    if(!$footer_disable) $footer_disable = get_post_meta($postID, "_footer_disable", true);
    if(!$content_type) $content_type = get_post_meta($postID, "content_type", true);
    if(!$enable_preloader) $enable_preloader =  get_post_meta($postID, "_enable_preloader", true);    

    if ($content_type =='full_width') {
        $page_class = ' full_width';
    }elseif($content_type =='full_post_area'){
        $page_class = ' visual_page_builder full_width';
    }
    elseif($content_type =='full_gutenberg'){
        $page_class = ' fullgutenberg full_width';
    }
    elseif($content_type =='full_gutenberg_reg'){
        $page_class = ' fullgutenberg full_width fullgutenberg_reg';
    }
    elseif($content_type =='full_gutenberg_ext'){
        $page_class = ' fullgutenberg full_width fullgutenberg_ext';
    }
    $title_disable =  get_post_meta($postID, "_title_disable", true);
    $comment_enable = get_post_meta($postID, "_enable_comments", true); 
    $content_class = ($content_type == 'full_post_area') ? 'rh-fullbrowser' : 'rh-post-wrapper';
    if($content_type == 'full_gutenberg' || $content_type =='full_gutenberg_reg' || $content_type =='full_gutenberg_ext'){
        $content_class = '';
    }
?>
<?php if ($header_disable =='1') :?>
    <!DOCTYPE html>
    <!--[if IE 8]>    <html class="ie8" <?php language_attributes(); ?>> <![endif]-->
    <!--[if IE 9]>    <html class="ie9" <?php language_attributes(); ?>> <![endif]-->
    <!--[if (gt IE 9)|!(IE)] <?php language_attributes(); ?>><![endif]-->
    <html <?php language_attributes(); ?>>
    <head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width" />
    <!-- feeds & pingback -->
      <link rel="profile" href="http://gmpg.org/xfn/11" />
      <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />    
    <?php wp_head(); ?>
    </head>
    <body <?php body_class(); ?>>
    <?php if(function_exists('wp_body_open')){wp_body_open();}?>
    <div class="rh-outer-wrap">
    <div id="top_ankor"></div>
    <?php $branded_bg_url = rehub_option('rehub_branded_bg_url');?>
    <?php if ($branded_bg_url ) :?>
      <a id="branded_bg" href="<?php echo esc_url($branded_bg_url); ?>" target="_blank" rel="sponsored"></a>
    <?php endif; ?>
    <?php include(rh_locate_template('inc/parts/branded_banner.php')); ?>   
    <!-- HEADER --> 
<?php elseif($header_disable =='2') :?>
    <?php get_header(); ?> 
    <?php $addstyles = '#main_header{position: absolute;}  
    #main_header, .main-nav{background:none transparent !important;}  
    nav.top_menu > ul > li > a, .logo_section_wrap .user-ava-intop:after, .dl-menuwrapper button i, .dl-menuwrapper .re-compare-icon-toggle, nav.top_menu > ul > li > a{color: #fff !important} 
    .main-nav{border:none !important}
    .dl-menuwrapper button svg line{stroke:#fff !important}
    .responsive_nav_wrap{background:transparent !important}';
    if (rehub_option('rehub_header_color_background') ==''){
        $addstyles .= '.is-sticky .logo_section_wrap{background: #000 !important}';
    }
    echo '<style>'.$addstyles.'</style>';
    ?>    
<?php else :?>
    <?php get_header(); ?>
<?php endif ;?>
<?php if($enable_preloader):?>
    <!-- PRELOADER -->
    <?php 

    $script = '
    window.onload = function () {
        if (jQuery("#rhLoader").length) {
            jQuery("#rhLoader").fadeOut();
        }        
    };';
    wp_add_inline_script('rehub', $script);
    ?>
    <div id="rhLoader">
        <style scoped>
            #loading-spinner {
              animation: loading-spinner 1s linear infinite;
            }

            @keyframes loading-spinner {
              from {
                transform: rotate(0deg);
              }
              to {
                transform: rotate(360deg);
              }
            }  
        </style>  
        <?php 
            if (rehub_option('rehub_custom_color')) {
                $maincolor = rehub_option('rehub_custom_color');
            } 
            else {
                $maincolor = REHUB_MAIN_COLOR;
            }
        ?>                
        <div class="preloader-cell">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: none; display: block; shape-rendering: auto;" width="200px" height="200px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" id="loading-spinner">
                <circle cx="50" cy="50" r="15" stroke-width="4" stroke="<?php echo esc_attr($maincolor);?>" stroke-dasharray="23.561944901923447 23.561944901923447" fill="none" stroke-linecap="round">
                  
                </circle>
                </svg>                
            </div>
        </div>
    </div>
    <!-- /end PRELOADER --> 
<?php endif;?>

<!-- CONTENT -->
<div class="rh-container <?php echo str_replace(array('full_gutenberg_reg', 'full_gutenberg_ext'), 'full_gutenberg', $content_type) ?>"> 
    <div class="rh-content-wrap clearfix <?php if($content_type == 'full_gutenberg' || $content_type =='full_gutenberg_reg' || $content_type =='full_gutenberg_ext'){echo 'pt0';}?>">
        <!-- Main Side -->
        <div class="main-side page clearfix<?php echo ''.$page_class ?>" id="content">
            <div class="<?php echo ''.$content_class;?>">
                <article class="post mb0" id="page-<?php the_ID(); ?>">       
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <?php if(!$title_disable):?>
                        <div class="title"><h1 class="entry-title"><?php the_title(); ?></h1></div>
                    <?php endif;?>
                    <?php if($content_type == 'full_gutenberg' || $content_type =='full_gutenberg_reg' || $content_type =='full_gutenberg_ext'){echo rh_generate_incss('fullgutenberg');}?>
                    <?php the_content(); ?>
                    <?php if($content_type =='full_post_area'):?>
                        <?php wp_link_pages(array( 'before' => '<div class="page-link"><span class="page-link-title">' . esc_html__( 'Pages:', 'rehub-theme' ).'</span>', 'after' => '</div>', 'pagelink' => '<span>%</span>' )); ?>
                    <?php endif;?>
                    <?php if($comment_enable):?>
                        <?php comments_template(); ?>
                    <?php endif;?>
                <?php endwhile; endif; ?>  
                </article> 
            </div>         
        </div>	
        <!-- /Main Side --> 
        <?php if($content_type =='def' || $content_type == ''):?> 
            <!-- Sidebar -->
            <?php get_sidebar(); ?>
            <!-- /Sidebar --> 
        <?php endif;?>
    </div>
</div>
<!-- /CONTENT -->     
<!-- FOOTER -->
<?php if ($footer_disable =='1') :?>
</div>
<span class="rehub_scroll" id="topcontrol" data-scrollto="#top_ankor"><i class="rhicon rhi-chevron-up"></i></span>
<?php wp_footer(); ?>
</body>
</html>
<?php else :?>
<?php get_footer(); ?>
<?php endif ;?>