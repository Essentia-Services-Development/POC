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
<article class="rething_item small_post col_item inf_scr_item<?php if (rehub_option('disable_resizer_grid') == 1){echo ' no_crop_grid';};?>">
        <figure  class="width-100p position-relative mb20"><?php echo re_badge_create('tablelabel'); ?>
            <?php if(rehub_option('repick_social_disable') !='1' && function_exists('rehub_social_share')) :?><?php echo rehub_social_share(''); ?> <?php endif;?>
            <div class="thing-post-like"> 
                <?php $wishlistadd = __('Save', 'rehub-theme');?>           
                <?php $wishlistadded = __('Saved', 'rehub-theme');?>
                <?php $wishlistremoved = __('Removed', 'rehub-theme');?>
                <?php echo RH_get_wishlist($post->ID, $wishlistadd, $wishlistadded, $wishlistremoved);?>
            </div>            
            <a href="<?php echo ''.$link;?>"<?php echo ''.$target;?>>
                <?php 
                    WPSM_image_resizer::show_static_resized_image(array('thumb'=> true, 'width'=> 381, 'height'=> 255, 'crop'=> true, 'no_thumb_url'=> get_template_directory_uri() . '/images/default/noimage_378_310.png'));
                ?>
            </a>
        </figure>                                     
    <div class="wrap_thing">
        <div class="top">
            <?php $category = get_the_category(get_the_ID());  ?>
            <?php if ($category) {$first_cat = $category[0]->term_id; meta_small( false, $first_cat, false, false );} ?>
        </div>
        <div class="hover_anons">
            <h2 class="mt0 mb15"><a href="<?php echo ''.$link;?>"<?php echo ''.$target;?>><?php the_title();?></a></h2>
            <div class="post-meta"> <?php meta_small( true, false, true, false ); ?> </div>
            <p><?php kama_excerpt('maxchar=320'); ?></p>
        </div>
    </div>
    <?php if(rehub_option('disable_btn_offer_loop')!='1')  : ?>  
        <div class="rething_button rh-flex-columns rh-flex-align-end padd15 width-100p">                              
        <?php rehub_generate_offerbtn('wrapperclass=block_btnblock width-100p mobile_block_btnclock&btn_more=yes');?>
        </div>
    <?php endif; ?>
</article>