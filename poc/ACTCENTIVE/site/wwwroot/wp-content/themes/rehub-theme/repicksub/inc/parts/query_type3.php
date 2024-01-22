<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $post;
if (rehub_option('aff_link_image') == '1') {
    $link = rehub_create_affiliate_link();
    $target = ' rel="nofollow sponsored" target="_blank"';
}
else {
    $link = get_the_permalink();
    $target = '';  
}
if (rehub_option('aff_link_title') == '1') {
    $linktitle = rehub_create_affiliate_link ();
    $targettitle = ' rel="nofollow sponsored" target="_blank"';
}
else {
    $linktitle = get_the_permalink();
    $targettitle = '';  
}
?>
<article class="repick_item small_post col_item inf_scr_item<?php if(rehub_option('rehub_grid_images') =='center') : ?> centered_im_grid<?php else : ?> contain_im_grid<?php endif ; ?>">
    <?php echo re_badge_create('ribbonleft'); ?>
    <figure class="width-100p position-relative mb20 <?php if(rehub_option('rehub_grid_images') =='center') : ?> pad_wrap<?php endif ;?>">
        <a href="<?php echo ''.$link;?>"<?php echo ''.$target;?>>
            <?php if(rehub_option('rehub_grid_images') =='center') : ?>
                <?php WPSM_image_resizer::show_static_resized_image(array('thumb'=> true, 'height'=> 325));?>
            <?php else : ?>
                <?php WPSM_image_resizer::show_static_resized_image(array('thumb'=> true, 'width'=> 383, 'height'=> 383, 'crop'=> true));?>
            <?php endif ; ?>
        </a>  
        <?php if(rehub_option('repick_social_disable') !='1' && function_exists('rehub_social_share')) :?> <?php echo rehub_social_share(''); ?> <?php endif;?> 
        <div class="favour_in_image favour_btn_red"> 
            <?php $wishlistadd = esc_html__('Save', 'rehub-theme');?>           
            <?php $wishlistadded = esc_html__('Saved', 'rehub-theme');?>
            <?php $wishlistremoved = esc_html__('Removed', 'rehub-theme');?>
            <?php echo RH_get_wishlist($post->ID, $wishlistadd, $wishlistadded, $wishlistremoved);?>
        </div>
        <?php if(function_exists('wprc_report_submission_form')) :?> <?php wprc_report_submission_form(); ?> <?php endif;?> 
        <?php do_action( 'repick_inside_grid_figure' ); ?>            
    </figure>
    <div class="wrap_thing">
        <div class="hover_anons meta_enabled">
            <h2 class="mt0 mb15"><a href="<?php echo ''.$linktitle;?>"<?php echo ''.$targettitle;?>><?php the_title();?></a></h2>
            <?php do_action( 'repick_after_grid_title' ); ?>
            <div class="repick_grid_meta">
                <?php if(rehub_option('exclude_author_meta') != 1) :?>
                <?php global $post; $author_id=$post->post_author; $name = get_the_author_meta( 'display_name', $author_id ); ?>
                <span class="admin_meta_grid">
                    <a class="admin" href="<?php echo get_author_posts_url( $author_id ) ?>"><?php echo get_avatar( $author_id, '22', '', $name ); ?><?php echo esc_attr($name); ?>
                    </a>
                </span> 
                <?php endif ?>
                <?php if(rehub_option('hotmeter_disable') !='1') :?><?php echo getHotThumb(get_the_ID(), true);?><?php endif;?>               
            </div>            
            <p><?php kama_excerpt('maxchar=320'); ?></p>
        </div>
        <?php if(rehub_option('disable_btn_offer_loop')!='1')  : ?>                              
            <?php rehub_generate_offerbtn('btn_more=yes');?>
        <?php endif; ?>
        
    </div>
</article>

<?php if (isset ($count) && isset ($count_ads) && isset ($count_ad_code) && !empty($count_ads) && !empty($count_ad_code) && in_array($count, $count_ads)) : ?>    
    <article class="repick_item small_post col_item inf_scr_item contain_im_grid">
        <figure class="mediad_wrap_pad width-100p position-relative mb20">
            <?php echo ''.$count_ad_code; ?>
        </figure>
        <div class="wrap_thing">
            <div class="hover_anons meta_enabled">
                <?php if (isset ($count_ad_descs) && !empty($count_ad_descs) ) : ?>
                    <?php if ($count_ad_descs) {
                        $randomKey = array_rand($count_ad_descs, 1); 
                        $count_ad_desc = $count_ad_descs[$randomKey]; 
                        unset($count_ad_descs[$randomKey]);
                    }?>
                    <p><?php echo ''.$count_ad_desc; ?></p>
                <?php endif ;?>                         
            </div>
        </div>
    </article> 
<?php endif ;?>