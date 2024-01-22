<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/*
  Name: Offers grid
 */
  use Keywordrush\AffiliateEgg\TemplateHelper; 
?>

<?php wp_enqueue_style('eggrehub'); ?>

<div class="tabs-item egg_widget_grid rh_deal_block"> 
    <?php if(is_array($items)) $number = count($items);?>
    <?php $i=0; foreach ($items as $key => $item): ?>
        <?php $offer_price = (!empty($item['price'])) ? $item['price'] : ''; ?>
        <?php $offer_price_old = (!empty($item['price'])) ? $item['old_price'] : ''; ?>
        <?php $offer_post_url = $item['url'] ;?>
        <?php $afflink = apply_filters('rh_post_offer_url_filter', $offer_post_url );?>
        <?php $aff_thumb = $item['img'] ;?>
        <?php $offer_title = wp_trim_words( $item['title'], 10, '...' ); ?>
        <?php $i++;?>  
        <div class="clearfix flowhidden<?php if($i != $number): ?> mb15 pb15 border-grey-bottom<?php endif;?>">
            <figure class="floatleft width-100 img-maxh-100 img-width-auto">
                <a rel="nofollow sponsored" target="_blank" class="re_track_btn" href="<?php echo esc_url($afflink) ?>"<?php echo ''.$item['ga_event'] ?>>
                    <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $aff_thumb, 'height'=> 100, 'title' => $offer_title, 'no_thumb_url' => get_template_directory_uri().'/images/default/noimage_123_90.png'));?>                                     
                </a>                
            </figure>
            <div class="detail floatright width-100-calc pl15 rtlpr15">
                <div class="mt0 lineheight20 fontnormal font95">
                    <a rel="nofollow sponsored" target="_blank" href="<?php echo esc_url($afflink) ?>"<?php echo ''.$item['ga_event'] ?>>
                        <?php echo esc_attr($offer_title); ?>
                    </a>                    
                </div>
                <div class="post-meta">
                    <?php if(!empty($offer_price)) : ?>
                        <div class="rh-deal-price mb10 fontbold font90">
                            <ins><?php echo TemplateHelper::formatPriceCurrency($item['price_raw'], $item['currency_code'], '', ''); ?></ins>
                            <?php if(!empty($offer_price_old)) : ?>
                                <del class="rh_opacity_3 blockstyle fontnormal blackcolor"><?php echo ''.$item['old_price_raw'];?></del>
                            <?php endif ;?>                                
                        </div>
                    <?php endif ;?>                  
                    <div class="rh-deal-tag">
                        <?php echo rehub_get_site_favicon($item['orig_url']); ?>                             
                    </div>                    
                </div>

            </div>
        </div>
    <?php endforeach; ?>
</div>