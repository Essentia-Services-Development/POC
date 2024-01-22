<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php 
global $post;
if (isset($aff_link) && $aff_link == '1') {
    $link = rehub_create_affiliate_link ();
    $target = ' rel="nofollow sponsored" target="_blank"';
}
else {
    $link = get_the_permalink();
    $target = '';  
}
?>  
<?php
$disable_meta = (isset($disable_meta)) ? $disable_meta : '';
$disable_price = (isset($disable_price)) ? $disable_price : '';
$exerpt_count = (isset($exerpt_count)) ? $exerpt_count : '';
$enable_btn = (isset($enable_btn)) ? $enable_btn : '';
$image_padding = (isset($image_padding) && $image_padding) ? ' padd20' : '';
$cardclass = (isset($disablecard) && $disablecard) ? '' : ' rh-cartbox';
$paddclass = (isset($disablecard) && $disablecard) ? '' : ' pb0 pr20 pl20 mobilepadding';
$cropimage = (isset($cropimage)) ? $cropimage : true;
$image_class = (isset($image_class)) ? $image_class : '';
$twocolmobclass = ($cropimage) ? ' two_column_mobile' : '';
?>
<?php
$dealcat = '';       
if(rehub_option('enable_brand_taxonomy') == 1){ 
    $dealcats = wp_get_post_terms($post->ID, 'dealstore', array("fields" => "all")); 
    if( ! empty( $dealcats ) && ! is_wp_error( $dealcats ) ) {
        $dealcat = $dealcats[0];                   
    }                               
}
?>
<article class="col_item column_grid rh-heading-hover-color rh-bg-hover-color no-padding<?php echo ''.$cardclass.$twocolmobclass;?>"> 
    <div class="button_action abdposright pr5 pt5">
        <div class="floatleft mr5">
            <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
            <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
            <?php echo RH_get_wishlist($post->ID, '', $wishlistadded, $wishlistremoved);?>  
        </div>                                                           
    </div> 
    <?php $discountpercentage = get_post_meta($post->ID, 'rehub_offer_discount', true);?>    
    <figure class="<?php if ($discountpercentage) :?> border-lightgrey <?php endif;?>mb20 position-relative text-center<?php echo esc_attr($image_padding);?>"><?php echo re_badge_create('tablelabel'); ?>             
        <a href="<?php echo ''.$link;?>"<?php echo ''.$target;?> class="<?php echo esc_attr($image_class);?>">
            <?php if ($discountpercentage) :?>
                <span class="height-80 rh-flex-center-align rh-flex-justify-center sale_tag_inwoolist text-center"><div class="font150 fontbold greencolor mb0 ml0 mr0 mt0 overflow-elipse pb0 pl0 pr0 pt0"><?php echo esc_html($discountpercentage);?></div></span>
            <?php else :?>
                <?php if(!$cropimage):?>
                    <?php WPSM_image_resizer::show_static_resized_image(array('thumb'=> true, 'width'=> 336, 'height'=> 220, 'crop'=> $cropimage, 'no_thumb_url'=> get_template_directory_uri() . '/images/default/noimage_336_220.png')); ?>
                <?php else:?>
                    <?php wpsm_thumb('mediumgrid'); ?>
                <?php endif;?>
            <?php endif;?>
        </a>
    </figure>
    <?php do_action( 'rehub_after_grid_column_figure' ); ?>
    <div class="content_constructor<?php echo ''.$paddclass;?>">
        <h3 class="mb15 mt0 font110 mobfont100 fontnormal lineheight20"><a href="<?php echo ''.$link;?>"<?php echo ''.$target;?>><?php the_title();?></a></h3>
        <?php $custom_notice = get_post_meta($post->ID, '_notice_custom', true);?>
        <?php 
            if($custom_notice){
                echo '<div class="rh_custom_notice mb10 lineheight20 fontbold font90 rehub-sec-color">'.esc_html($custom_notice).'</div>';
            }
            elseif (!empty($dealcat)) {
                $dealcat_notice = get_term_meta($dealcat->term_id, 'cashback_notice', true );
                if($dealcat_notice){
                    echo '<div class="rh_custom_notice mb10 lineheight20 fontbold font90 rehub-sec-color">'.esc_html($dealcat_notice).'</div>';
                }
            } 
        ?>                 
        <?php do_action( 'rehub_after_grid_column_title' ); ?> 
        <?php if($exerpt_count && $exerpt_count !='0'):?>                      
        <div class="mb15 greycolor lineheight20 font90">                                 
            <?php kama_excerpt('maxchar='.$exerpt_count.''); ?>                       
        </div> 
        <?php endif?>
        <?php if($disable_price&& $disable_meta):?>
        <?php else:?>
            <div class="rh-flex-center-align mb15 <?php if($cropimage):?>mobileblockdisplay<?php endif;?>">
                <?php if(!$disable_meta):?>
                    <div class="post-meta mb0 mobmb10">
                        <?php if ('post' == get_post_type($post->ID) && rehub_option('exclude_cat_meta') != 1) :?>
                            <?php $category = get_the_category($post->ID);  ?>
                            <?php if ($category) {
                                if ( class_exists( 'WPSEO_Primary_Term' ) ) {
                                    $wpseo_primary_term = new WPSEO_Primary_Term( 'category', $post->ID );
                                    $wpseo_primary_term = $wpseo_primary_term->get_primary_term();
                                    //$termyoast               = get_term( $wpseo_primary_term );
                                    if (!is_numeric($wpseo_primary_term )) {
                                        $first_cat = $category[0]->term_id;
                                    }else{
                                        $first_cat = $wpseo_primary_term; 
                                    }
                                }else{
                                    $first_cat = $category[0]->term_id; 
                                }
                                meta_small( false, $first_cat, false, false );
                            } ?>            
                        <?php endif; ?> 
                        <div class="store_for_grid">
                            <?php WPSM_Postfilters::re_show_brand_tax('list');?>
                        </div>               
                    </div>
                <?php endif?>
                <?php if(!$disable_price):?>
                <div <?php if(!$disable_meta):?>class="rh-flex-right-align"<?php endif;?>>
                    <?php rehub_generate_offerbtn('showme=price&wrapperclass=pricefont110 rehub-btn-font rehub-main-color mobpricefont100 fontbold mb0 lineheight20');?>            
                </div>
                <?php endif?>               
            </div>
        <?php endif?> 
        <?php if($enable_btn):?>
        <div class="columngridbtn">
            <?php rehub_generate_offerbtn('showme=button&wrapperclass=mb10');?>            
        </div>
        <?php endif?>
    </div>                                   
</article>