<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<!-- CONTENT -->
<div class="rh-container"> 
    <div class="rh-content-wrap clearfix">
	    <!-- Main Side -->
        <div class="main-side single<?php if(get_post_meta($post->ID, 'post_size', true) == 'full_post' || rehub_option('disable_post_sidebar')) : ?> full_width<?php endif; ?> clearfix">
            <div class="rh-post-wrapper">            
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <?php $expiredclass = rh_expired_or_not($post->ID, 'class');?>
                    <?php $postclasses = array('post-inner', 'post', $expiredclass);?>
                    <article <?php post_class($postclasses); ?> id="post-<?php the_ID(); ?>">
                        <!-- Title area -->
                        <div class="rh_post_layout_compact rh_post_layout_compact_dir">
                            <?php 
                                $crumb = '';
                                if( function_exists( 'yoast_breadcrumb' ) ) {
                                    $crumb = yoast_breadcrumb('<div class="breadcrumb">','</div>', false);
                                }
                                else if (function_exists('rank_math_the_breadcrumbs')) {
                                    $crumb = rank_math_get_breadcrumbs('wrap_before=<div class="breadcrumb">&wrap_after=</div>');
                                }
                                if( ! is_string( $crumb ) || $crumb === '' ) {
                                    if(rehub_option('rehub_disable_breadcrumbs') == '1') {echo '';}
                                    elseif (function_exists('dimox_breadcrumbs')) {
                                        dimox_breadcrumbs(); 
                                    }
                                }
                                echo ''.$crumb;  
                            ?> 
                            <div class="title_single_area mb15 rh-flex-eq-height mobileblockdisplay">
                                <?php $disableimage = get_post_meta($post->ID, 'show_featured_image', true);?>
                                <?php if(!$disableimage)  : ?>
                                    <div class="width-125 mb20 pr20 featured_single_left disablemobilepadding mobilemargincenter">
                                        <figure>
                                            <?php echo re_badge_create('ribbonleft'); ?>
                                            <div class="favorrightside wishonimage"><?php echo RH_get_wishlist($post->ID);?></div> 
                                            <?php $discountpercentage = get_post_meta($post->ID, 'rehub_offer_discount', true);?>       
                                            <?php if ($discountpercentage) :?>
                                                <span class="height-80 rh-flex-center-align rh-flex-justify-center sale_tag_inwoolist text-center"><div class="font150 fontbold greencolor mb0 ml0 mr0 mt0 overflow-elipse pb0 pl0 pr0 pt0"><?php echo esc_html($discountpercentage);?></div></span>
                                            <?php else :?>                                
                                                <?php echo WPSM_image_resizer::show_wp_image('smallgrid', '', array('lazydisable'=>true, 'loading'=>'eager')); ?>  
                                            <?php endif ;?>                                   
                                        </figure>                             
                                    </div>
                                <?php endif;?>
                                <div class="rh-flex-grow1 single_top_main mr20">                                     
                                    <?php echo rh_expired_or_not($post->ID, 'span');?><h1 class="<?php if(rehub_option('hotmeter_disable') !='1') :?><?php echo getHotIconclass($post->ID, true); ?><?php endif ;?>"><?php the_title(); ?></h1>                                                        
                                    <div class="meta post-meta mb15 flowhidden">
                                        <?php rh_post_header_meta('full', true, true, true, false);?> <span class="more-from-store-a ml5 mr5"><?php WPSM_Postfilters::re_show_brand_tax('list');?></span>   
                                    </div>
                                    <?php 
                                    $offer_coupon_date = get_post_meta( $post->ID, 'rehub_offer_coupon_date', true );
                                    $coupon_style = $expired = ''; if(!empty($offer_coupon_date)) : ?>
                                        <?php
                                            $timestamp1 = strtotime($offer_coupon_date);
                                            if(strpos($offer_coupon_date, ':') ===false){
                                                $timestamp1 += 86399;
                                            }
                                            $seconds = $timestamp1 - (int)current_time('timestamp',0);
                                            $days = floor($seconds / 86400);
                                            $seconds %= 86400;
                                            if ($days > 0) {
                                                $coupon_style = '';
                                                $coupon_text = $days.' '.__('days left', 'rehub-theme');
                                            }
                                            elseif ($days == 0){
                                                $coupon_text = esc_html__('Last day', 'rehub-theme');
                                                $coupon_style = '';
                                            }
                                            else {
                                                $coupon_text = esc_html__('Expired', 'rehub-theme');
                                                $coupon_style = ' expired_coupon';
                                                $expired = '1';
                                            }
                                        ?>
                                    <?php endif ;?>                         
                                    <?php if(!empty($offer_coupon_date) && $expired !=1) {
                                        echo '<div class="gridcountdown mb20 mt0 mr0 ml0" style="width:220px">';
                                        $year = date('Y',$timestamp1);
                                        $month = date('m',$timestamp1);
                                        $day  = date('d',$timestamp1); 
                                        $hour  = date('H',$timestamp1); 
                                        $minute  = date('i',$timestamp1); 
                                        echo wpsm_countdown(array('year'=> $year, 'month'=>$month, 'day'=>$day, 'minute'=>$minute, 'hour'=>$hour));
                                        echo '</div>';
                                    } ?>  
                                                                                
                                    <?php if(rehub_option('rehub_disable_share_top') =='1')  : ?>
                                    <?php else :?>
                                        <?php if(function_exists('rehub_social_share')):?>
                                            <div class="top_share">
                                                <div class="post_share">
                                                    <?php echo rehub_social_share('row', false, false);?>
                                                </div> 
                                            </div>
                                            <div class="clearfix"></div> 
                                        <?php endif; ?> 
                                    <?php endif; ?>                                                                                          
                                </div> 
                                <div class="single_top_corner text-right-align"> 
                                    <?php block_template_part( 'post-single-button' );?>
                                    <?php rehub_generate_offerbtn('wrapperclass=block_btnblock mobile_block_btnclock&updateclean=1');?>                                                         
                                </div> 
                            </div>
                        </div>
                        <?php if(rehub_option('rehub_single_after_title')) : ?><div class="mediad mediad_top mb15"><?php echo do_shortcode(rehub_option('rehub_single_after_title')); ?></div><div class="clearfix"></div><?php endif; ?>     
                        <?php $no_featured_image_layout = 1;?>
                        <?php include(rh_locate_template('inc/parts/top_image.php')); ?>                                       
                        <?php $disableads = get_post_meta($post->ID, 'show_banner_ads', true);?>
                        <?php if(rehub_option('rehub_single_before_post') && $disableads != '1') : ?><div class="mediad mediad_before_content mb15"><?php echo do_shortcode(rehub_option('rehub_single_before_post')); ?></div><?php endif; ?>  

                        <?php the_content(); ?>

                    </article>
                    <div class="clearfix"></div>
                    <?php include(rh_locate_template('inc/post_layout/single-common-footer.php')); ?>                    
                <?php endwhile; endif; ?>
                <?php comments_template(); ?>
            </div>
		</div>	
        <!-- /Main Side -->  
        <!-- Sidebar -->
        <?php if(get_post_meta($post->ID, 'post_size', true) == 'full_post' || rehub_option('disable_post_sidebar')) : ?><?php else : ?><?php get_sidebar(); ?><?php endif; ?>
        <!-- /Sidebar -->
    </div>
</div>
<!-- /CONTENT -->     