<?php if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}?>
<?php $sticky_header = (isset ($row['sticky_header']) && $row['sticky_header'] == 1) ? ' sticky-cell': '';?>
<?php $postid = get_the_ID();?>
<?php $posttype = get_post_type($postid);?>

<div class="product_image_col<?php echo ''.$sticky_header; ?>">
    <?php echo re_badge_create('ribbonleft'); ?>                                            
    <?php 
    $affiliate_link_image = isset($row['image_link_affiliate']) ? $row['image_link_affiliate'] : '';
    $affiliate_link_title = isset($row['title_link_affiliate']) ? $row['title_link_affiliate'] : '';
    $link_on_thumb = ($affiliate_link_image =='1') ? rehub_create_affiliate_link() : get_the_permalink(); 
    $link_on_title = ($affiliate_link_title =='1') ? rehub_create_affiliate_link() : get_the_permalink();   
    $link_on_thumb_target = ($affiliate_link_image =='1') ? ' target="_blank" rel="nofollow"' : '';
    $link_on_title_target = ($affiliate_link_title =='1') ? ' target="_blank" rel="nofollow"' : '';
    if ($posttype == 'product'){
        global $product;
        if($product->get_type() =='external'){
            $link_on_thumb = $link_on_title = $product->add_to_cart_url();
            $link_on_thumb_target = $link_on_title_target = ' target="_blank" rel="nofollow"';
        }
        else{
            $link_on_thumb = $link_on_title = get_post_permalink($postid);
            $link_on_thumb_target = $link_on_title_target = '';            
        }
    }   
    ?>
    <figure>
        <a href="<?php echo esc_url($link_on_thumb);?>"<?php echo ''.$link_on_thumb_target;?>>
            <?php       
                $image_id = get_post_thumbnail_id(get_the_ID());  
                $image_url = wp_get_attachment_image_src($image_id,'full');  
                $img = $image_url[0];
            ?>
            <img src="<?php echo bfi_thumb( $img, array( 'height' => 150) ); ?>" alt="image" />                              
        </a>
    </figure>
    <h2>
        <a href="<?php echo ''.$link_on_title;?>"<?php echo ''.$link_on_title_target;?>>
            <?php echo rehub_truncate_title(65, get_the_ID());?>                     
        </a>
    </h2>
    <div class="rev-in-compare-flip">
        <?php $rating_score_clean = '';?> 
        <?php $rating_score_clean = get_post_meta(get_the_ID(), 'rehub_review_overall_score', true); ?>            

        <?php if ($rating_score_clean):?>
            <div class="radial-progress" data-rating="<?php echo ''.$rating_score_clean?>">
                <div class="circle">
                    <div class="mask full">
                        <div class="fill"></div>
                    </div>
                    <div class="mask half">
                        <div class="fill"></div>
                        <div class="fill fix"></div>
                    </div>
                    
                </div>
                <div class="inset">
                    <div class="percentage"><?php echo ''.$rating_score_clean?></div>
                </div>
            </div>                                                            
        <?php endif;?>                                                        
    </div>  
    <div class="price-in-compare-flip mt20">
        <?php if($posttype == 'product') :?>
            <div class="mt10"></div>
            <?php include(rh_locate_template('inc/top/woobtn.php')); ?>        
        <?php else:?>
            <?php $price_from = get_post_meta(get_the_ID(), 'rehub_offer_product_price', true); ?> 
            <?php if($price_from) :?>
                <?php esc_html_e('Prices start from:', 'rehub-theme');?> <span class="greencolor"><?php echo ''.$price_from;?></span>
                <a href="<?php echo get_the_permalink(get_the_ID());?>" class="re_track_btn rh-deal-compact-btn padforbuttonsmall fontnormal font95 lineheight15 text-center inlinestyle btn_offer_block mt15"><?php esc_html_e('Check all prices', 'rehub-theme');?></a>
            <?php endif;?>                   
        <?php endif;?>                                               
    </div>                                              
</div>