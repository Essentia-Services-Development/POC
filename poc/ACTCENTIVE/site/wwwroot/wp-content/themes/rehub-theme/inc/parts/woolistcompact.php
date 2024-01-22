<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $product; global $post;?>
<?php if (empty( $product ) ) {return;}?>
<?php $attrelpanel = (isset($attrelpanel)) ? $attrelpanel : '';?>
<?php $woolinktype = (isset($woolinktype)) ? $woolinktype : '';?>
<?php $woo_aff_btn = rehub_option('woo_aff_btn');?>
<?php $isvariable = $product->is_type( 'variable' );?>
<?php $affiliatetype = ($product->get_type() =='external') ? true : false;?>
<?php if($affiliatetype && ($woolinktype == 'aff' || $woo_aff_btn)) :?>
    <?php $woolink = $product->add_to_cart_url(); $wootarget = ' target="_blank" rel="nofollow sponsored"';?>
<?php else:?>
    <?php $woolink = get_post_permalink($post->ID); $wootarget = '';?>
<?php endif;?>

<div class="woocommerce type-product woocompactlist rh-flex-columns width-100p mb15 border-grey-bottom pb15 mobilesblockdisplay no_cart_sliding">    
    <?php do_action('woocommerce_before_shop_loop_item');?> 
	<?php if ( $isvariable ) : ?>
        <?php
        // Enqueue variation scripts.
        wp_enqueue_script( 'wc-add-to-cart-variation' );
        wp_enqueue_script( 'rhajaxvariation' );

        // Get Available variations?
        $get_variations = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );

        $available_variations = $get_variations ? $product->get_available_variations() : false;
        $attributes           = $product->get_variation_attributes();
        $selected_attributes  = $product->get_default_attributes();

        $attribute_keys  = array_keys( $attributes );
        $variations_json = wp_json_encode( $available_variations );
        $variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );
        ?>
        <form class="variations_form cart rh-flex-columns mobilesblockdisplay width-100p" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. ?>">
            <?php do_action( 'woocommerce_before_variations_form' ); ?>
    <?php endif;?>


    <div class="rh-flex-columns rh-flex-grow1 mobmb10 rh-flex-nowrap">
        <div class="deal_img_wrap position-relative text-center width-80 height-80 img-width-auto"> 
            <?php 
                if($product->is_on_sale() && $product->get_regular_price() && $product->get_price() > 0 && !$isvariable){
                    $offer_price_calc = (float) $product->get_price();
                    $offer_price_old_calc = (float) $product->get_regular_price();
                    $sale_proc = 0 -(100 - ($offer_price_calc / $offer_price_old_calc) * 100); 
                    $sale_proc = round($sale_proc);
                    echo '<span class="rh-label-string abdposright greenbg mr0 mb5">'.$sale_proc.'%</span>';
                }

            ?>       
            <a href="<?php echo esc_url($woolink) ;?>"<?php echo ''.$wootarget ;?>>           
                <?php echo WPSM_image_resizer::show_wp_image('woocommerce_thumbnail'); ?>
            </a>
            <?php do_action( 'rh_woo_thumbnail_loop' ); ?>
        </div>
        <div class="woo_list_desc rh-flex-grow1 pr15 pl15">                            
            <h3 class="font90 mb15 mt0 fontnormal lineheight15"><a href="<?php echo esc_url($woolink) ;?>"<?php echo ''.$wootarget ;?>><?php the_title();?></a></h3>
            <?php rh_wooattr_code_loop($attrelpanel);?> 
            <?php if ($product->get_price() !='') : ?>
            <?php echo '<div class="pricefont110 rehub-main-color mobpricefont90 fontbold mb10 mr10 lineheight15"><span class="price">'.$product->get_price_html().'</span></div>';?>
            <?php endif ;?> 
            <div class="single_variation_wrap"><div class="woocommerce-variation single_variation fontbold"></div></div>
            <?php if ( $product->managing_stock() ):?>
                <?php if(! $product->is_in_stock()):?>
                    <div class="stock out-of-stock mt5 redbrightcolor mb5"><?php esc_html_e('Out of Stock', 'rehub-theme');?></div>
                <?php else:?>
                    <div class="stock in-stock mt5 font80"><span class="greycolor">SKU: <?php echo ''.$product->get_sku();?></span>  <span class="greencolor"><i class="rhicon rhi-database mr5 ml10"></i><?php echo ''.$product->get_stock_quantity( );?> <?php esc_html_e('in stock', 'rehub-theme');?></span></div>
                <?php endif;?> 
            <?php endif;?>                                          
            <div class="clearfix"></div>
            <span class="woolist_vendor">
                <?php do_action( 'rehub_vendor_show_action' ); ?>                            
            </span> 
            
        </div> 
    </div>          
    
    <?php if ( 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' ) && $product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() && ! $product->is_sold_individually() ):?>
        <form action="<?php esc_url( $product->add_to_cart_url() );?>" class="rh-flex-columns mb10 rh-loop-quantity cart" method="post" enctype="multipart/form-data">
        <div class="rh-woo-quantity">
            <?php rehub_cart_quantity_input(array('mb'=> 'mb0'), $product, true);?>
        </div>
    <?php elseif ( $isvariable ) : ?>
        <div class="rh-flex-columns rh-flex-justify-end disablemsflexjustify variations_button woocommerce-variation-add-to-cart mobmb10">
            <?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
                <p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'rehub-theme' ) ) ); ?></p>                
            <?php else:?>
                <div class="rh-woo-quantity">
                    <?php rehub_cart_quantity_input(array('mb'=> 'mb0'), $product, true);?>
                </div>   
            <?php endif;?>    
    <?php else:?>
        <div class="rh-flex-columns mb10 rh-loop-quantity rh-flex-justify-end">
    <?php endif;?>

    <div class="width-50 ml15">
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
    </div>

    <?php if ( 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' ) && $product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() && ! $product->is_sold_individually() ):?>
        </form>
    <?php elseif ( $isvariable ) : ?>
                <input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
                <input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
                <input type="hidden" name="variation_id" class="variation_id" value="0" />
            </div>
            <?php if ( !empty( $available_variations ) ) : ?>
                <div class="rh-flex-columns rh-flex-nowrap width-100p woo-list-variation-wrap">
                    <div class="width-80 height-80">

                    </div>                
                    <div class="variations pl15 pr15 rh-flex-grow1 width-100p" cellspacing="0">
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
            <?php do_action( 'woocommerce_after_variations_form' ); ?>
        </form>
    <?php else:?>
        </div>
    <?php endif;?>  

    <?php do_action( 'woocommerce_after_shop_loop_item' );?>
</div>