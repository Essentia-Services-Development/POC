<?php

    /* Template Name: Gutenberg Auto Contents */

?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php get_header(); ?>
<?php global $post;?>
<!-- CONTENT -->
<div class="rh_post_layout_default rh_post_layout_outside mb20" id="rh_woo_layout_inimage">
<?php 
    if (rehub_option('rehub_third_color')) {
        $maincolor = rehub_option('rehub_third_color');
    }   
    else if (rehub_option('rehub_custom_color')) {
        $maincolor = rehub_option('rehub_custom_color');
    } 
    else {
        $maincolor = REHUB_MAIN_COLOR;
    }
?>
<style scoped>
    #rh_woo_layout_inimage{background: <?php echo hex2rgba($maincolor, 0.05);?>}
</style>
<div class="rh-container alignfulloutside">
    <?php wp_enqueue_script('rhalignfull');?> 
    <div class="pt20 clearfix pb10">
        <!-- Title area -->
            <div class="title_single_area mb0 rh-flex-eq-height rh-flex-justify-btw flowhidden">
                <div class="rh-336-content-area">
                    <?php 
                        $crumb = '';
                        if( function_exists( 'yoast_breadcrumb' ) ) {
                            $crumb = yoast_breadcrumb('<div class="breadcrumb">','</div>', false);
                        }
                        if( ! is_string( $crumb ) || $crumb === '' ) {
                            if(rehub_option('rehub_disable_breadcrumbs') == '1') {echo '';}
                            elseif (function_exists('dimox_breadcrumbs')) {
                                dimox_breadcrumbs(); 
                            }
                        }
                        echo ''.$crumb;  
                    ?> 
                    <div class="mb15 clearfix"></div>                      
                    <h1><?php the_title(); ?></h1>
                    <div class="mb20 font120 rh_opacity_5"><?php echo ''.$post->post_excerpt;?></div> 
                    <div class="meta post-meta flowhidden mb20">
                        <?php rh_post_header_meta('full', true, true, true, false);?> 
                    </div> 
                    <?php if(rehub_option('rehub_disable_share_top') =='1')  : ?>
                    <?php else :?>
                        <div class="top_share">
                            <?php include(rh_locate_template('inc/parts/post_share.php')); ?>
                        </div>
                        <div class="clearfix"></div> 
                    <?php endif; ?> 
                    <?php if(rehub_option('rehub_single_after_title')) : ?><div class="mediad mediad_top mb15"><?php echo do_shortcode(rehub_option('rehub_single_after_title')); ?></div><div class="clearfix"></div><?php endif; ?>                                                
                </div>
                <div class="post-head-image-part position-relative rh-336-sidebar mb0 rh-flex-center-align rh-flex-justify-center">
                    <?php wpsm_thumb('large_inner', 200); ?>
                </div>
            </div>            
    </div>
</div>

</div>
<div class="rh-container"> 
    <div class="rh-content-wrap clearfix flowhidden rh-stickysidebar-wrapper">   
        <!-- Main Side -->
        <div class="rh-mini-sidebar-content-area single clearfix floatleft rh-sticky-container tabletbslockdisplay">
            <div class="rh-post-wrapper">            
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <?php $postclasses = array('post-inner', 'post');?>
                    <article <?php post_class($postclasses); ?> id="page-<?php the_ID(); ?>">                                      

                        <?php if(rehub_option('rehub_single_before_post')) : ?><div class="mediad mediad_before_content mb15"><?php echo do_shortcode(rehub_option('rehub_single_before_post')); ?></div><?php endif; ?>
                        <div id="contents-section-woo-area"></div>
                        <?php the_content(); ?>

                    </article>
                    <div class="clearfix"></div>

                    <?php if(rehub_option('rehub_single_code')) : ?><div class="single_custom_bottom mt10 mb10 margincenter text-center clearbox"><?php echo do_shortcode (rehub_option('rehub_single_code')); ?></div><div class="clearfix"></div><?php endif; ?>

                    <?php if(rehub_option('rehub_disable_share') =='1')  : ?>
                    <?php else :?>
                        <?php include(rh_locate_template('inc/parts/post_share.php')); ?>  
                    <?php endif; ?>               


                    <?php if(rehub_option('rehub_disable_author') =='1')  : ?>
                    <?php else :?>
                        <?php rh_author_detail_box();?>
                    <?php endif; ?>                                   
                <?php endwhile; endif; ?>
                <?php comments_template(); ?>
            </div>
        </div>  
        <!-- /Main Side -->  
        <!-- Sidebar -->
        <aside class="rh-mini-sidebar floatright rh-sticky-container hideonstablet">            
            <div class="guten-contents whitebg border-lightgrey rh-mini-sidebar">
                <?php wp_enqueue_script('customfloatpanel'); ?><?php wp_enqueue_script('stickysidebar');?>
                <?php 
                    $headings = [];
                    $blocks = parse_blocks($post->post_content);
                     
                    if (count($blocks) == 1 && $blocks[0]['blockName'] == null) {  // Non-Gutenberg posts
                    } else {
                        foreach ($blocks as $block) {

                            if ($block['blockName'] == 'rehub/color-heading') {                               
                                if(!empty($block['attrs']['subtitle'])){
                                    $headings[] = ['title' => wp_strip_all_tags($block['attrs']['subtitle'])];
                                }
                            }
                            if ($block['blockName'] == 'rehub/review-heading') {                               
                                if(!empty($block['attrs']['subtitle'])){
                                    $headings[] = ['title' => wp_strip_all_tags($block['attrs']['subtitle'])];
                                }
                            }
                        }
                    }
                 
                    if (!empty($headings)) { 
                        $i = 0;
                        echo '<div class="clearfix padd15 pt20 fontbold">'.esc_html__('Table of Contents', 'rehub-theme').':</div>';
                        echo '<ul class="sidecontents">';
                        $anchorarray = array();
                        foreach ($headings as $heading) {
                            $i++;
                            $anchor = rh_convert_cyr_symbols($heading['title']);
                            $anchor = str_replace(array('\'', '"'), '', $anchor); 
                            $spec = preg_quote( '\'.+$*~=' );
                            $anchor = preg_replace("/[^a-zA-Z0-9_$spec\-]+/", '-', $anchor );
                            $anchor = strtolower( trim( $anchor, '-') );
                            $anchor = substr( $anchor, 0, 70 );
                            $anchorarray[$i] = $anchor;
                            echo '<li class="top pt10 pb10 pl5 pr15 border-top ml0 mb0"><a class="greycolor rh-flex-center-align" href="#'.$anchor.'"><span class="height-22 width-22 roundborder rehub-main-color-bg whitecolor text-center inlinestyle mr10 ml10">'.$i.'</span><span>' . $heading['title'] . '</span></a></li>';
                        }
                        echo '</ul>';
                    }
                ?>
                    
                </div>      
        </aside>
        <!-- /Sidebar -->
        <!-- Floating panel links contents -->
        <div class="flowhidden rh-float-panel rhhidden" id="float-panel-woo-area">
            <div class="rh-container rh-flex-center-align pt10 pb10">
                <div class="float-panel-woo-info wpsm_pretty_colored rh-line-left pl15 ml15">
                    <ul class="float-panel-woo-links list-unstyled list-line-style font80 fontbold lineheight15">
                        <?php                        
                            $i = 0; 
                            foreach ($headings as $heading) {
                                $i++;
                                echo '<li class=""><a class="rh-flex-center-align" href="#'.$anchorarray[$i].'"><span class="height-22 width-22 roundborder rehub-main-color-bg whitecolor text-center inlinestyle mr10 ml10">'.$i.'</span><span>' . $heading['title'] . '</span></a></li>';
                            }                                               
                         ?>                                                                             
                    </ul>                                  
                </div>                                   
            </div>                           
        </div> 
    </div>
</div>
<!-- /CONTENT --> 

<!-- FOOTER -->
<?php get_footer(); ?> 