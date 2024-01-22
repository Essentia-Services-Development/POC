<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product, $post;
?>
<?php $related = wc_get_related_products($product->get_id(), 6);?> 
<?php if( sizeof( $related ) == 0 ):?>
<?php else:?>
    <div class="border-lightgrey clearbox flowhidden mb25 rh-shadow1 rh-tabletext-block rh-tabletext-wooblock whitebg width-100p related-woo-area" id="section-woo-related">
        <div class="rh-tabletext-block-heading fontbold border-grey-bottom">
            <span class="cursorpointer floatright lineheight15 ml10 toggle-this-table rtlmr10"></span>
            <h4 class="rh-heading-icon"><?php esc_html_e( 'Related Products', 'rehub-theme' );?></h4>
        </div>
        <div class="rh-tabletext-block-wrapper padd20"> 
            <?php $classcol = ($sidebar) ? 'col_wrap_two' : 'col_wrap_three';?>        
            <div class="<?php echo ''.$classcol;?> rh-flex-eq-height woorelatedgrid compact_rel_grid">
                <?php 
                    if($sidebar){
                        array_slice($related, 0, 4);
                    }
                ?>
                <?php foreach ($related as $item): ?>
                    <?php 
                        $title = get_the_title($item);
                        $url = get_the_permalink($item);
                        $image_id = '';
                        if ( has_post_thumbnail($item) ){
                            $image_id = get_post_thumbnail_id($item);  
                            $image_url = wp_get_attachment_image_src($image_id, 'full');  
                            $image_url = $image_url[0];
                            $image_url = apply_filters('rh_thumb_url', $image_url );
                        }
                        else {
                            $image_url = get_template_directory_uri() . '/images/default/noimage_123_90.png' ;
                            $image_url = apply_filters('rh_no_thumb_url', $image_url, $item);
                        }

                    ?>
                    <div class="col_item border-lightgrey pb10 pl10 pr10 pt10">
                        <div class="medianews-img floatleft mr20 rtlml20">
                            <a href="<?php echo esc_url($url);?>">
                                <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $image_url, 'width'=> 80, 'height'=> 80, 'title' => $title, 'css_class'=>'width-80'));?>
                            </a>                    
                        </div>
                        <div class="medianews-body floatright width-100-calc">
                            <h5 class="font90 lineheight20 mb10 mt0 fontnormal">
                                <a href="<?php echo esc_url($url);?>"><?php echo strip_tags($title);?></a>
                            </h5>
                            <div class="font80 lineheight15 greencolor">
                                <?php  
                                    $the_price = get_post_meta( $item, '_price', true);  
                                    if ( '' != $the_price ) {
                                        if(rehub_option('ce_custom_currency')){
                                            $currency_code = rehub_option('ce_custom_currency');
                                            $woocurrency = get_woocommerce_currency(); 
                                            if($currency_code != $woocurrency && defined('\ContentEgg\PLUGIN_PATH')){
                                                $currency_rate = \ContentEgg\application\helpers\CurrencyHelper::getCurrencyRate($woocurrency, $currency_code);
                                                if (!$currency_rate) $currency_rate = 1;
                                                $the_price = \ContentEgg\application\helpers\TemplateHelper::formatPriceCurrency($the_price*$currency_rate, $currency_code, '<span class="woocommerce-Price-currencySymbol">', '</span>');
                                            }
                                            else{
                                                $the_price = wc_price( $the_price ) ;
                                            }                                               
                                        }else{
                                            $the_price = wc_price( $the_price );
                                        }
                                        echo strip_tags($the_price);
                                    }  
                                ?>
                            </div>
                            <?php if(rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) :?>  
                                <div class="woo-btn-actions-notext mt10">         
                                <?php 
                                    $cmp_btn_args = array(); 
                                    $cmp_btn_args['class']= 'rhwoosinglecompare';
                                    $cmp_btn_args['id'] = $item;
                                    if(rehub_option('compare_woo_cats') != '') {
                                        $cmp_btn_args['woocats'] = esc_html(rehub_option('compare_woo_cats'));
                                    }
                                ?>                                                  
                                <?php echo wpsm_comparison_button($cmp_btn_args); ?>
                                </div> 
                            <?php endif;?>                                                        
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif;?>