<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="guten-auto-layout position-relative darkbgl">
    <style scoped>
        .post-meta span a, .post-meta a.admin, .post-meta a.cat, .post-meta-big a{color:#bbb;}
        .breadcrumb a{color:#eee;}
        .row_social_inpost span.share-link-image{box-shadow:none;}
        .main-nav.white_style{border-bottom:none;}
        @media (max-width:469px){
            .rh-video-scroll-wrap{height:200px}
        }
    </style>
    <div class="rh-container wide_width_restricted position-static flowhidden pt30 pb30">                                   
        <div class="rh-360-content-area tabletsblockdisplay">
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
            <?php 
                $post_image_videos = get_post_meta( $post->ID, 'rh_post_image_videos', true );
            ?>
            <?php $videos=array(); if($post_image_videos):?>
                <?php 
                    $videos = array_map('trim', explode(PHP_EOL, $post_image_videos));
                    $video = $videos[0]; 
                    wp_enqueue_script('rhvideolazy');
                ?>
                <div class="rh-video-scroll-wrap mb20">
                    <div class="rh-video-scroll-cont">
                        <?php $videodata = parse_video_url(esc_url($video), 'data');?>
                        <div class="rh_video_thumb_schema"  itemscope itemtype="http://schema.org/VideoObject">
                            <meta content="<?php echo get_the_title();?>" itemprop="name" />
                            <meta itemprop="uploadDate" content="<?php echo ''.$post->post_date;?>" />
                            <meta itemprop="thumbnailURL" content="<?php echo ''.$videodata['image'];?>" />
                            <meta itemprop="embedUrl" content="<?php echo ''.$videodata['embed'];?>" />
                            <meta itemprop="description" content="<?php echo wp_strip_all_tags($post->post_excerpt); ?>" />
                            <div class="rh_videothumb_link text-center cursorpointer rh_lazy_load_video darkbg" data-hoster="<?php echo ''.$videodata['hoster'];?>" data-width="930" data-height="520" data-videoid="<?php echo ''.$videodata['id'];?>">
                            <?php if(has_post_thumbnail()):?>
                                <?php echo WPSM_image_resizer::show_wp_image('full', '', array('max_height'=>'520px', 'emptyimage'=>get_template_directory_uri() . '/images/default/noimage_800_520.png')); ?> 
                            <?php else:?>
                                <img src="<?php echo ''.$videodata['image'];?>" alt="video <?php echo get_the_title();?>" />
                            <?php endif;?>     
                            </div>
                        </div>
                    </div>
                </div>
            <?php else:?>
                <div class="woo-image-part position-relative text-center mb20">
                    <?php echo WPSM_image_resizer::show_wp_image('full', '', array('max_height'=>'520px', 'emptyimage'=>get_template_directory_uri() . '/images/default/noimage_800_520.png')); ?>  
                </div> 
            <?php endif;?>                      
        </div>
        <div class="rh-360-sidebar tabletsblockdisplay summary woo_white_text_layout darkbgl padd15 whitecolorinner calcposright float_p_trigger float_trigger_clr_change">
            <div class="hideonfloattablet title_single_area mb15">
                    <?php echo re_badge_create('labelsmall'); ?>                       
                    <h1 class="whitecolor"><?php the_title(); ?></h1>
                    <div class="mb20 font120 rh_opacity_5 fontbold"><?php echo ''.$post->post_excerpt;?></div>
                    <?php if(rehub_option('rehub_single_after_title')) : ?><div class="mediad mediad_top mb15"><?php echo do_shortcode(rehub_option('rehub_single_after_title')); ?></div><div class="clearfix"></div><?php endif; ?>                                 
            </div>
            <div class="rh-video-scroll-copy"></div>
            <div class="wpsm_score_box pb0">
                <?php block_template_part( 'post-single-button' );?>
                <div class="priced_block">
                    <?php rehub_generate_offerbtn('wrapperclass=text-center');?>
                </div>
                <?php $disclaimer = get_post_meta($post->ID, 'rehub_offer_disclaimer', true);?>
                <?php if($disclaimer):?>
                    <div class="font80 guten-disclaimer text-center greycolor lineheight15 pb15"><?php echo wp_kses($disclaimer, 'post');?></div>
                <?php endif;?> 
            </div>
            <div class="rhhidden showonfloat">
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
        </div> 
        <div class="rh-360-content-area tabletsblockdisplay">
            <div class="rh-flex-center-align woo_top_meta mobileblockdisplay mb20">
                <div class="meta post-meta">
                    <?php rh_post_header_meta(true, true, true, true, false);?> 
                </div>                                         
            </div>
            <?php if(rehub_option('rehub_disable_share_top') =='1')  : ?>
            <?php else :?>
                <div class="top_share">
                    <?php include(rh_locate_template('inc/parts/post_share.php')); ?>
                </div>
                <div class="clearfix"></div> 
            <?php endif; ?>                                     
        </div>                   
    </div>
</div>
<?php if (count($videos) > 1):?>
    <div class="darkbg flowhidden pt20 woo-video-top-area">
        <div class="rh-container wide_width_restricted">
            <div class="rh-360-content-area tabletsblockdisplay">
                <?php echo woo_custom_video_output('class=rh-flex-center-align mb10 rh_videothumb_link&rel=rh_top_gallery&wrapper=1&title=no&exceptfirst=1');?> 
            </div>
        </div>
    </div>
<?php endif;?>  
<!-- CONTENT -->
<div class="alignfulloutside rh-container wide_width_restricted">
    <?php wp_enqueue_script('rhalignfull');?> 
    <div class="rh-content-wrap clearfix">   
	    <!-- Main Side -->
        <div class="rh-360-content-area tabletsblockdisplay">                        
            <?php if (!empty($score)):?>
                <?php $rate_position = rh_get_product_position($post->ID, 'category', 'rehub_review_overall_score', 'post');?>
                    <?php if (!empty($rate_position['rate_pos'])):?>
                        <div class="rev-verdict bd-dbl-btm-orange flowhidden mb25">
                            <div class="floatleft mobileblockdisplay pb15"><?php echo wpsm_reviewbox(array('compact'=>'text', 'id'=> $post->ID, 'scrollid'=>'tab-title-description'));?></div> 
                            <div class="flowhidden lineheight20 floatright mobileblockdisplay pb15">
                                <?php 
                                    if($rate_position['rate_pos'] < 3){
                                        echo '<i class="rhicon rhi-trophy-alt font150 orangecolor mr10 vertmiddle rtlml10"></i>';
                                    }
                                ?> 
                                <?php esc_html_e( 'Product is rated as', 'rehub-theme' ); ?> <strong>#<?php echo ''.$rate_position['rate_pos'];?></strong> <?php esc_html_e( 'in category', 'rehub-theme' ); ?> <a href="<?php echo esc_url($rate_position['link']);?>"><?php echo esc_attr($rate_position['cat_name']); ?></a>                                                               
                            </div>
                        </div>
                    <?php endif; ?>
            <?php endif; ?> 
            <div class="">            
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <?php $postclasses = array('post-inner', 'post');?>
                    <article <?php post_class($postclasses); ?> id="post-<?php the_ID(); ?>">
                        <?php $disableads = get_post_meta($post->ID, 'show_banner_ads', true);?>
                        <?php if(rehub_option('rehub_single_before_post') && $disableads != '1') : ?><div class="mediad mediad_before_content mb15"><?php echo do_shortcode(rehub_option('rehub_single_before_post')); ?></div><?php endif; ?>                                      
                        <!--Added-->
                        <?php wp_enqueue_script('customfloatpanel'); ?> 
                        <div id="contents-section-woo-area"></div>
                        <?php  
                            $offer_post_url = get_post_meta($post->ID, 'rehub_offer_product_url', true );
                            $offer_url = apply_filters('rh_post_offer_url_filter', $offer_post_url );
                            $offer_price = get_post_meta($post->ID, 'rehub_offer_product_price', true );
                            $offer_btn_text = get_post_meta($post->ID, 'rehub_offer_btn_text', true );
                            $offer_price_old = get_post_meta($post->ID, 'rehub_offer_product_price_old', true );
                            $offer_coupon = get_post_meta( $post->ID, 'rehub_offer_product_coupon', true );
                            $offer_coupon_date = get_post_meta( $post->ID, 'rehub_offer_coupon_date', true );
                            $offer_coupon_mask = get_post_meta( $post->ID, 'rehub_offer_coupon_mask', true );

                            $coupon_style = $expired = ''; if(!empty($offer_coupon_date)){
                                $timestamp1 = strtotime($offer_coupon_date);
                                if(strpos($offer_coupon_date, ':') ===false){
                                    $timestamp1 += 86399;
                                }
                                $seconds = $timestamp1 - (int)current_time('timestamp',0);
                                $days = floor($seconds / 86400);
                                $seconds %= 86400;
                                if ($days > 0) {
                                    $coupon_text = $days.' '.__('days left', 'rehub-theme');
                                    $coupon_style = '';
                                    $expired = 'no';
                                }
                                elseif ($days == 0){
                                    $coupon_text = esc_html__('Last day', 'rehub-theme');
                                    $coupon_style = '';
                                    $expired = 'no';
                                }
                                else {
                                    $coupon_text = esc_html__('Expired', 'rehub-theme');
                                    $coupon_style = ' expired_coupon';
                                    $expired = '1';
                                }
                            }

                            $coupon_mask_enabled = (!empty($offer_coupon) && ($offer_coupon_mask =='1' || $offer_coupon_mask =='on') && $expired!='1') ? '1' : '';
                            $outsidelinkpart = ($coupon_mask_enabled=='1') ? 'data-codeid="'.$post->ID.'" data-dest="'.$offer_url.'" data-clipboard-text="'.$offer_coupon.'" class="masked_coupon"' : '';
                            
                        ?> 
                        <!--./Added--> 
                        <?php the_content(); ?>
                        <div class="flowhidden rh-float-panel darkbg whitecolor" id="float-panel-woo-area">
                            <div class="rh-container wide_width_restricted rh-flex-center-align pt10 pb10">
                                <div class="float-panel-woo-image hideonsmobile">
                                    <?php WPSM_image_resizer::show_static_resized_image(array('lazy'=>true, 'thumb'=> true, 'width'=> 50, 'height'=> 50));?>
                                </div>
                                <div class="ml15">
                                    <div class="hideonstablet mb5 font110 fontbold whitecolor">
                                            <?php the_title();?>
                                    </div>
                                    <div class="desktabldisplaynone mb5">
                                        <div class="float-panel-woo-info darkbg greycolorinner whitecurrentlist rh-360-content-area smart-scroll-desktop">
                                            <ul class="float-panel-woo-links list-unstyled list-line-style font80 fontbold lineheight15">
                                                <?php                        
                                                    $i = 0; 
                                                    foreach ($headings as $heading) {
                                                        $i++;
                                                        echo '<li class=""><a class="rh-flex-center-align" href="#'.$anchorarray[$i].'"><span class="height-22 width-22 roundborder rehub-main-color-bg whitecolor text-center inlinestyle mr10">'.$i.'</span><span>' . $heading['title'] . '</span></a></li>';
                                                    }                                               
                                                 ?>                                                                             
                                            </ul>                                  
                                        </div>                                        
                                    </div> 
                                    <div class="float-panel-price rhhidden showonsmobile">
                                        <div class="fontbold font110 rehub-btn-font">
                                            <?php echo esc_html($offer_price) ?>
                                            <?php if($offer_price_old !='') :?>
                                            <span class="retail-old greycolor rh_opacity_5 font90">
                                                <strike><span class="value"><?php echo esc_html($offer_price_old) ; ?></span></strike>
                                                </span>
                                                <?php endif;?>                                                                    
                                        </div>                                                           
                                    </div>            
                                </div>
                                <div class="float-panel-woo-btn rh-flex-columns rh-flex-right-align rh-flex-nowrap desktabldisplaynone">
                                    <div class="float-panel-woo-button rh-flex-center-align rh-flex-right-align">                                                    
                                        <div class="clearfix desktabldisplaynone mb5 priced_block">
                                        <?php if ($coupon_mask_enabled =='1') :?>
                                            <?php wp_enqueue_script('zeroclipboard'); ?>
                                            <a class="coupon_btn showonsmobile re_track_btn btn_offer_block rehub_offer_coupon masked_coupon <?php if(!empty($offer_coupon_date)) {echo ''.$coupon_style ;} ?>" <?php echo ''.$outsidelinkpart;?>>
                                                <?php if($offer_btn_text !='') :?>
                                                    <?php echo esc_html ($offer_btn_text) ; ?>
                                                <?php elseif(rehub_option('rehub_mask_text') !='') :?>
                                                    <?php echo rehub_option('rehub_mask_text') ; ?>
                                                <?php else :?>
                                                    <?php esc_html_e('Reveal coupon', 'rehub-theme') ?>
                                                <?php endif ;?>                 
                                            </a>
                                        <?php endif;?>                                                             
                                            <a class="re_track_btn showonsmobile btn_offer_block" href="<?php echo esc_url ($offer_url) ?>" target="_blank" rel="nofollow sponsored" <?php echo ''.$outsidelinkpart;?>>
                                                <?php if($offer_btn_text !='') :?>
                                                    <?php echo esc_attr($offer_btn_text) ; ?>
                                                <?php elseif(rehub_option('rehub_btn_text') !='') :?>
                                                    <?php echo rehub_option('rehub_btn_text') ; ?>
                                                <?php else :?>
                                                    <?php esc_html_e('Buy this item', 'rehub-theme') ?>
                                                <?php endif ;?>
                                            </a> 
                                        </div>
                                    </div>                                        
                                </div>                                    
                            </div>                           
                        </div> 
                    </article>
                    <div class="clearfix"></div>
                    <?php include(rh_locate_template('inc/post_layout/single-common-footer.php')); ?>                    
                <?php endwhile; endif; ?>
                <?php comments_template(); ?>
            </div>
		</div>	
        <!-- /Main Side -->  
    </div>
</div>
<!-- /CONTENT -->     