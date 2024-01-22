<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/*
  Name: Offers with button
 */
  use Keywordrush\AffiliateEgg\TemplateHelper; 
?>

<?php wp_enqueue_style('eggrehub'); ?>

<div class="rh_deal_block">
    <?php if (is_array($items)) $number = count($items);?>
    <?php $i=0; foreach ($items as $key => $item): ?>
        <?php $offer_price = (!empty($item['price'])) ? $item['price'] : ''; ?>
        <?php $offer_price_old = (!empty($item['price'])) ? $item['old_price'] : ''; ?>
        <?php $offer_post_url = $item['url'] ;?>
        <?php $afflink = apply_filters('rh_post_offer_url_filter', $offer_post_url );?>
        <?php $aff_thumb = $item['img'] ;?>
        <?php $offer_title = wp_trim_words( $item['title'], 10, '...' ); ?>
        <?php $i++;?>  
        <?php if(rehub_option('rehub_btn_text') !='') :?><?php $btn_txt = rehub_option('rehub_btn_text') ; ?><?php else :?><?php $btn_txt = esc_html__('See deal', 'rehub-theme') ;?><?php endif ;?>     
        <div class="deal_block_row flowhidden clearbox<?php if($i != $number): ?> mb15 pb15 border-grey-bottom<?php endif;?>">
            <div class="deal-pic-wrapper width-80 floatleft text-center img-maxh-100">
                <a rel="nofollow sponsored" target="_blank" class="re_track_btn" href="<?php echo esc_url($afflink) ?>"<?php echo ''.$item['ga_event'] ?>>
                    <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $aff_thumb, 'crop'=> false, 'width'=> 80, 'height'=> 80, 'no_thumb_url' => get_template_directory_uri() . '/images/default/noimage_70_70.png'));?>                                    
                </a>                
            </div>
            <div class="rh-deal-details width-80-calc pl15 rtlpr15 floatright">
                <div class="rh-deal-text">
                    <div class="rh-deal-name mb10">
                        <div class="mt0 mb10 fontnormal">
                        <a rel="nofollow sponsored" class="re_track_btn" target="_blank" href="<?php echo esc_url($afflink) ?>"<?php echo ''.$item['ga_event'] ?>>
                            <?php echo esc_attr($offer_title); ?>
                        </a>
                        </div>
                    </div>
                </div>
                <div class="rh-flex-columns">
                    <div class="rh-deal-left">
                        <?php if(!empty($offer_price)) : ?>
                            <div class="rh-deal-price mb10 fontbold font90">
                                <?php echo TemplateHelper::formatPriceCurrency($item['price_raw'], $item['currency_code'], '', ''); ?>
                                <?php if(!empty($offer_price_old)) : ?>
                                    <del><?php echo ''.$item['old_price_raw'];?></del>
                                <?php endif ;?>                                
                            </div>
                        <?php endif ;?>                  
                        <div class="rh-deal-tag">
                            <?php echo rehub_get_site_favicon($item['orig_url']); ?>                             
                        </div>
                    </div> 
                    <div class="rh-deal-right rh-flex-right-align">                
                        <div class="rh-deal-btn mb10 text-right-align">
                            <a class="re_track_btn rh-deal-compact-btn" href="<?php echo esc_url($afflink) ?>"<?php echo ''.$item['ga_event'] ?> target="_blank" rel="nofollow sponsored">
                                <?php echo esc_attr($btn_txt) ; ?>
                            </a>                    
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
    <?php endforeach; ?>
</div>