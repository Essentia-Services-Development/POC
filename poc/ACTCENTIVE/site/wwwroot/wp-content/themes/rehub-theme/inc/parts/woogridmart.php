<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>
<?php global $product;
global $post; ?>
<?php if (empty($product)) {
    return;
} ?>
<?php $classes = array('product', 'col_item', 'two_column_mobile', 'type-product', 'woo_mart'); ?>
<?php if (rehub_option('woo_btn_disable') == '1') {
    $classes[] = 'no_btn_enabled';
} ?>
<?php if (rehub_option('woo_wholesale')) {
    $classes[] = 'no_cart_sliding';
} ?>
<?php $isvariable = $product->is_type('variable'); ?>
<?php $woolinktype = (isset($woolinktype)) ? $woolinktype : ''; ?>
<?php $attrelpanel = (isset($attrelpanel)) ? $attrelpanel : ''; ?>
<?php $woo_aff_btn = rehub_option('woo_aff_btn'); ?>
<?php $affiliatetype = ($product->get_type() == 'external') ? true : false; ?>
<?php if ($affiliatetype && ($woolinktype == 'aff' || $woo_aff_btn)) : ?>
    <?php $woolink = $product->add_to_cart_url();
    $wootarget = ' target="_blank" rel="nofollow sponsored"'; ?>
<?php else : ?>
    <?php $woolink = get_post_permalink($post->ID);
    $wootarget = ''; ?>
<?php endif; ?>
<?php $custom_img_width = (isset($custom_img_width)) ? $custom_img_width : ''; ?>
<?php $custom_img_height = (isset($custom_img_height)) ? $custom_img_height : ''; ?>
<?php $custom_col = (isset($custom_col)) ? $custom_col : ''; ?>
<?php $soldout = (isset($soldout)) ? $soldout : ''; ?>
<?php $sales_html = '';
if ($product->is_on_sale()) : ?>
    <?php
    $percentage = 0;
    if ($product->get_regular_price() && is_numeric($product->get_regular_price()) && $product->get_regular_price() !=0 && is_numeric($product->get_price()) ) {
        $percentage = round((($product->get_regular_price() - $product->get_price()) / $product->get_regular_price()) * 100);
    }
    if ($percentage && $percentage > 0 && !$product->is_type('variable')) {
        $sales_html = '<div class="font80 text-right-align greencolor ml10"><span><i class="rhicon rhi-arrow-down"></i> ' . $percentage . '%</span></div>';
        $classes[] = 'prodonsale';
    }
    ?>
<?php endif; ?>
<div class="<?php echo implode(' ', $classes); ?>">
    <div class="button_action pt5 pb5">
        <div>
            <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme'); ?>
            <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme'); ?>
            <?php echo RH_get_wishlist($post->ID, '', $wishlistadded, $wishlistremoved); ?>
        </div>
        <?php if (rehub_option('woo_quick_view')) : ?>
            <div>
                <?php echo RH_get_quick_view($post->ID, 'icon', 'pt10 pl5 pr5 pb10'); ?>
            </div>
        <?php endif; ?>
        <?php if (rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) : ?>
            <span class="compare_for_grid">
                <?php
                $cmp_btn_args = array();
                $cmp_btn_args['class'] = 'comparecompact';
                if (rehub_option('compare_woo_cats') != '') {
                    $cmp_btn_args['woocats'] = esc_html(rehub_option('compare_woo_cats'));
                }
                ?>
                <?php echo wpsm_comparison_button($cmp_btn_args); ?>
            </span>
        <?php endif; ?>
    </div>
    <?php $badge = get_post_meta($post->ID, 'is_editor_choice', true); ?>
    <?php if ($badge != '' && $badge != '0') : ?>
        <?php echo re_badge_create('ribbonleft'); ?>
    <?php elseif ($product->is_featured()) : ?>
        <?php echo apply_filters('woocommerce_featured_flash', '<span class="re-ribbon-badge badge_2"><span>' . esc_html__('Featured!', 'rehub-theme') . '</span></span>', $post, $product); ?>
    <?php endif; ?>
    <figure class="mb10 mt10 ml10 mr10 position-relative<?php if ($custom_col) : ?> notresized<?php endif; ?>">
        <a class="img-centered-flex rh-flex-justify-center rh-flex-center-align" href="<?php echo esc_url($woolink); ?>" <?php echo '' . $wootarget; ?>>
            <?php if ($custom_col) : ?>
                <?php
                $showimg = new WPSM_image_resizer();
                $showimg->use_thumb = true;
                $showimg->no_thumb = rehub_woocommerce_placeholder_img_src('');
                $showimg->width = (int)$custom_img_width;
                $showimg->height = (int)$custom_img_height;
                $showimg->show_resized_image();
                ?>
            <?php else : ?>
                <?php echo WPSM_image_resizer::show_wp_image('woocommerce_thumbnail'); ?>
            <?php endif; ?>
        </a>
        <?php do_action('rh_woo_thumbnail_loop'); ?>
        <div class="gridcountdown"><?php rehub_woo_countdown('no'); ?></div>
    </figure>
    <div class="grid_mart_content">
        <?php do_action('woocommerce_before_shop_loop_item'); ?>
        <h3 class="text-clamp text-clamp-2 font95 fontnormal lineheight20 mb15">
            <?php echo rh_expired_or_not($post->ID, 'span'); ?>
            <?php if ($product->is_featured()) : ?>
                <i class="rhicon rhi-bolt mr5 ml5 orangecolor" aria-hidden="true"></i>
            <?php endif; ?>
            <a href="<?php echo esc_url($woolink); ?>" <?php echo '' . $wootarget; ?>><?php the_title(); ?></a>
        </h3>
        <div class="fontnormal mb5 rehub-btn-font rh-flex-columns<?php if($isvariable) echo ' pricevariable';?>">
            <?php wc_get_template('loop/price.php'); ?>
            <?php echo '' . $sales_html; ?>
        </div>
        <?php if (!$product->is_in_stock()) : ?>
            <div class="stock out-of-stock mb5"><?php esc_html_e('Out of Stock', 'rehub-theme'); ?></div>
        <?php endif; ?>
        <?php if ($soldout) : ?>
            <?php rh_soldout_bar($post->ID); ?>
        <?php endif; ?>
        <?php if (get_option('woocommerce_enable_review_rating') !== 'no') : ?>
            <?php
            $rating = number_format( $product->get_average_rating(), 2 );
            $count = $product->get_review_count();
            echo rh_woo_rating_icons_wrapper_zero($rating, $count);
            ?>
        <?php endif; ?>
        <?php do_action('rehub_vendor_show_action'); ?>
        <?php rh_wooattr_code_loop($attrelpanel); ?>

        <?php if (rehub_option('woo_btn_disable') != '1') : ?>
            <div class="abposbot pb10 pl15 pr15 pt10 woo_grid_compact">
                <?php
                    $html = '';
                    if ($product && $product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock() && !$product->is_sold_individually()) {
                        $html = '<form action="' . esc_url($product->add_to_cart_url()) . '" class="cart rh-flex-columns rh-loop-quantity wooloopq" method="post" enctype="multipart/form-data">';
                        $html .= '<div class="mb10 rh-flex-center-align rh-flex-justify-center rh-woo-quantity">' . rehub_cart_quantity_input(array('mb' => 'mb0'), $product, false) . '</div>';
                        $html .= sprintf(
                            '<a href="%s" data-product_id="%s" data-product_sku="%s" class="re_track_btn rh-flex-center-align rh-flex-justify-center rh-shadow-sceu woo_loop_btn btn_offer_block %s %s product_type_%s"%s %s><svg height="24px" version="1.1" viewBox="0 0 64 64" width="24px" xmlns="http://www.w3.org/2000/svg"><g><path d="M56.262,17.837H26.748c-0.961,0-1.508,0.743-1.223,1.661l4.669,13.677c0.23,0.738,1.044,1.336,1.817,1.336h19.35   c0.773,0,1.586-0.598,1.815-1.336l4.069-14C57.476,18.437,57.036,17.837,56.262,17.837z"/><circle cx="29.417" cy="50.267" r="4.415"/><circle cx="48.099" cy="50.323" r="4.415"/><path d="M53.4,39.004H27.579L17.242,9.261H9.193c-1.381,0-2.5,1.119-2.5,2.5s1.119,2.5,2.5,2.5h4.493l10.337,29.743H53.4   c1.381,0,2.5-1.119,2.5-2.5S54.781,39.004,53.4,39.004z"/></g></svg> %s</a>',
                            esc_url($product->add_to_cart_url()),
                            esc_attr($product->get_id()),
                            esc_attr($product->get_sku()),
                            $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                            $product->supports('ajax_add_to_cart') ? 'ajax_add_to_cart' : '',
                            esc_attr($product->get_type()),
                            $product->get_type() == 'external' ? ' target="_blank"' : '',
                            $product->get_type() == 'external' ? ' rel="nofollow sponsored"' : '',
                            esc_html($product->add_to_cart_text()),
                            $product
                        );
                        $html .= '</form>';
                        echo '' . $html;
                    } else if ($isvariable) {
                        ?>
                        <div class="rh-flex-columns rh-flex-nowrap">
                            <div class="cursorpointer csspopuptrigger font80 rehub-main-color rh-flex-center-align rh-flex-justify-center lineheight15 rh-woo-quantity" data-popup="woomartpopup<?php echo (int)$post->ID;?>"><?php esc_html_e('Select variation', 'rehub-theme');?></div>
                            <span class="cursorpointer csspopuptrigger rh-flex-center-align rh-flex-justify-center rh-shadow-sceu woo_loop_btn btn_offer_block add_to_cart_button ajax_add_to_cart product_type_simple" data-popup="woomartpopup<?php echo (int)$post->ID;?>">
                                <svg height="24px" version="1.1" viewBox="0 0 64 64" width="24px" xmlns="http://www.w3.org/2000/svg">
                                <g>
                                    <path d="M56.262,17.837H26.748c-0.961,0-1.508,0.743-1.223,1.661l4.669,13.677c0.23,0.738,1.044,1.336,1.817,1.336h19.35   c0.773,0,1.586-0.598,1.815-1.336l4.069-14C57.476,18.437,57.036,17.837,56.262,17.837z" />
                                    <circle cx="29.417" cy="50.267" r="4.415" />
                                    <circle cx="48.099" cy="50.323" r="4.415" />
                                    <path d="M53.4,39.004H27.579L17.242,9.261H9.193c-1.381,0-2.5,1.119-2.5,2.5s1.119,2.5,2.5,2.5h4.493l10.337,29.743H53.4   c1.381,0,2.5-1.119,2.5-2.5S54.781,39.004,53.4,39.004z" />
                                </g>
                                </svg>
                            </span>
                        </div>

                        <?php
                        // Enqueue variation scripts.
                        wp_enqueue_script('wc-add-to-cart-variation');
                        wp_enqueue_script('rhajaxvariation');

                        // Get Available variations?
                        $get_variations = count($product->get_children()) <= apply_filters('woocommerce_ajax_variation_threshold', 30, $product);

                        $available_variations = $get_variations ? $product->get_available_variations() : false;
                        $attributes           = $product->get_variation_attributes();
                        $selected_attributes  = $product->get_default_attributes();

                        $attribute_keys  = array_keys($attributes);
                        $variations_json = wp_json_encode($available_variations);
                        $variations_attr = function_exists('wc_esc_json') ? wc_esc_json($variations_json) : _wp_specialchars($variations_json, ENT_QUOTES, 'UTF-8', true);
                        ?>
                        <div class="csspopup" id="woomartpopup<?php echo (int)$post->ID;?>">
                            <div class="csspopupinner cegg-price-alert-popup">
                                <span class="cpopupclose cursorpointer lightgreybg rh-close-btn rh-flex-center-align rh-flex-justify-center rh-shadow5 roundborder">×</span> 
                                <div class="padd20">
                                    <form class="variations_form cart mobilesblockdisplay width-100p" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint($product->get_id()); ?>" data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok.?>">
                                        <?php do_action('woocommerce_before_variations_form'); ?>

                                        <?php if ( !empty( $available_variations ) ) : ?>
                                            <div class="rh-flex-columns rh-flex-nowrap width-100p woo-list-variation-wrap mb30">              
                                                <div class="variations rh-flex-grow1 width-100p" cellspacing="0">
                                                        <?php foreach ( $attributes as $attribute_name => $options ) : ?>
                                                            <div class="rh-var-line-item mr10 inlinestyle mobileblockdisplay lineheight25">
                                                                <span class="label font80 pr10"><label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo wc_attribute_label( $attribute_name ); // WPCS: XSS ok. ?></label></span>
                                                                <div class="value">
                                                                    <?php
                                                                        wc_dropdown_variation_attribute_options(
                                                                            array(
                                                                                'options'   => $options,
                                                                                'attribute' => $attribute_name,
                                                                                'product'   => $product,
                                                                                'class'     => 'width-100p mb5 font80 border-grey'
                                                                            )
                                                                        );
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                        <?php echo wp_kses_post( apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'woocommerce' ) . '</a>' ) );?>
                                                </div>
                                            </div>
                                        <?php endif;?>
                                        <div class="rh-flex-columns variations_button woocommerce-variation-add-to-cart">
                                            <?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
                                                <p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'rehub-theme' ) ) ); ?></p>                
                                            <?php else:?>
                                                <div class="rh-woo-quantity">
                                                    <?php rehub_cart_quantity_input(array('mb'=> 'mb0'), $product, true);?>
                                                </div>   
                                            <?php endif;?> 
                                            <?php  echo apply_filters( 'wholesale_loop_add_to_cart_link',
                                                sprintf( '<a href="%s" data-product_id="%s" data-product_sku="%s" class="single_add_to_cart_button re_track_btn woo_loop_btn rh-flex-center-align rh-flex-justify-center rh-shadow-sceu %s %s product_type_%s"%s %s><svg height="24px" version="1.1" viewBox="0 0 64 64" width="24px" xmlns="http://www.w3.org/2000/svg"><g><path d="M56.262,17.837H26.748c-0.961,0-1.508,0.743-1.223,1.661l4.669,13.677c0.23,0.738,1.044,1.336,1.817,1.336h19.35   c0.773,0,1.586-0.598,1.815-1.336l4.069-14C57.476,18.437,57.036,17.837,56.262,17.837z"/><circle cx="29.417" cy="50.267" r="4.415"/><circle cx="48.099" cy="50.323" r="4.415"/><path d="M53.4,39.004H27.579L17.242,9.261H9.193c-1.381,0-2.5,1.119-2.5,2.5s1.119,2.5,2.5,2.5h4.493l10.337,29.743H53.4   c1.381,0,2.5-1.119,2.5-2.5S54.781,39.004,53.4,39.004z"/></g></svg></a>',
                                                esc_url( $product->add_to_cart_url() ),
                                                esc_attr( $product->get_id() ),
                                                esc_attr( $product->get_sku() ),
                                                $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                                                ($product->supports( 'ajax_add_to_cart' ) && !$isvariable) ? 'ajax_add_to_cart' : '',
                                                esc_attr( $product->get_type() ),
                                                $product->get_type() =='external' ? ' target="_blank"' : '',
                                                $product->get_type() =='external' ? ' rel="nofollow sponsored"' : ''
                                                ),
                                            $product );?>           
                                            <?php do_action( 'rh_woo_button_loop' ); ?>
                                            <input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
                                            <input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
                                            <input type="hidden" name="variation_id" class="variation_id" value="0" />
                                        </div>
                                        <?php do_action( 'woocommerce_after_variations_form' ); ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                ?>
            </div>
        <?php endif; ?>
        <?php do_action('woocommerce_after_shop_loop_item'); ?>
    </div>
</div>