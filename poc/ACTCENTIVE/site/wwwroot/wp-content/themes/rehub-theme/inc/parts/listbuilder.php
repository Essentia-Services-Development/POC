<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $post;?>
<?php $postid = $post->ID; ?>
<?php if(isset($json_innerargs)){
    $innerargs = (array) json_decode( urldecode( $json_innerargs ), true );
     extract($innerargs);
}?>
<?php $disclaimer = (isset($disclaimer) && $disclaimer) ? $disclaimer : '';?>
<?php $contentpos = (isset($contentpos) && $contentpos) ? $contentpos : 'titleexc';?>
<?php $headingtag = (isset($headingtag) && $headingtag) ? $headingtag : 'h2';?>
<?php $togglelink = (isset($togglelink) && $togglelink) ? $togglelink : '';?>
<?php $togglecontent = (isset($togglecontent) && $togglecontent) ? $togglecontent : '';?>
<?php $togglefield = (isset($togglefield) && $togglefield) ? $togglefield : '';?>
<?php $enable_pagination = (isset($enable_pagination) && $enable_pagination) ? $enable_pagination : '';?>
<?php $offsetnext = (isset($offsetnext) && $offsetnext) ? $offsetnext : '';?>
<?php $perpage = (isset($perpage) && $perpage) ? $perpage : '';?>
<?php $show = (isset($show) && $show) ? $show : 10;?>
<?php $pagenumber = (isset($pagenumber) && $pagenumber) ? $pagenumber : '';?>
<?php $isproduct = '';?>
<?php if(get_post_type($postid) == 'product') {
    $isproduct = true;
    global $product;
}?>
<?php 
if (isset($afflink) && $afflink == '1') {
    $link = rehub_create_affiliate_link ();
    $target = ' rel="nofollow sponsored" target="_blank"';
}
else {
    $link = get_the_permalink();
    $target = '';  
}
?>
<div class="r_offer_details rh_listitem top_rating_item<?php if(isset($stacktablet) && $stacktablet):?> stacktablet<?php endif;?><?php if($isproduct):?> woocommerce<?php endif;?>">
    <?php if($contentpos == 'titlerow'):?>
        <div class="listitem_title_row pt5 pb5 pr15 pl15 border-grey-bottom flowhidden">
            <?php if($togglelink == 'title'):?>
                <span class="def_btn fontnormal floatright r_show_hide font80 ml15 rtlmr15"><?php esc_html_e('More details +', 'rehub-theme');?></span>
            <?php endif;?>
                <?php if($isproduct):?>
                <div class="button_action floatright ml15 rtlmr15">
                    <div class="floatleft mr5">
                        <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
                        <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
                        <?php echo RH_get_wishlist($postid, '', $wishlistadded, $wishlistremoved);?>  
                    </div>
                    <?php if(rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) :?>
                        <span class="compare_for_grid floatleft">            
                            <?php 
                                $cmp_btn_args = array(); 
                                $cmp_btn_args['class']= 'comparecompact';
                                if(rehub_option('compare_woo_cats') != '') {
                                    $cmp_btn_args['woocats'] = esc_html(rehub_option('compare_woo_cats'));
                                }
                            ?>                                                  
                            <?php echo wpsm_comparison_button($cmp_btn_args); ?> 
                        </span>
                    <?php endif;?>                                                            
                </div>
            <?php endif;?>                        
            <<?php echo esc_attr($headingtag);?> class="font80 mt0 mb0 top_rating_title fontbold blackcolor"><a href="<?php echo ''.$link;?>"<?php echo ''.$target;?>><?php the_title();?></a></<?php echo esc_attr($headingtag);?>>
        </div>
    <?php endif;?>
    <div class="rh-flex-center-align rh-flex-justify-center pt15 pb15 <?php if(isset($stacktablet) && $stacktablet):?> tabletblockdisplay<?php else:?>mobileblockdisplay<?php endif;?>">
        <?php if(isset($image) && $image):?>
            <div class="listbuild_image listitem_column text-center">
                <figure class="position-relative ml0">
                    <?php echo re_badge_create('ribbon'); ?>
                        <?php 
                            $pagenumbercount = ($pagenumber && $pagenumber > 1) ? ($pagenumber-1)*$show : 0;
                            $numbertoshow = (int)$i+(int)$offsetnext+$pagenumbercount-(int)$perpage;
                            if($numbertoshow > 99){
                                $fontnumber = ' font70';
                            }else if($numbertoshow > 9){
                                $fontnumber = ' font90';
                            }else{
                                $fontnumber='';
                            }
                        ?>
                        <span class="rank_count<?php if($numbertoshow > 9) echo ''.$fontnumber;?>" id="rank_<?php echo (int)$i?>"><?php echo (int)$numbertoshow ?></span>
                  
                    <a class="img-centered-flex rh-flex-center-align rh-flex-justify-center" href="<?php echo ''.$link;?>"<?php echo ''.$target;?>>
                    <?php 
                    echo WPSM_image_resizer::show_wp_image('large_inner', '', array('emptyimage'=>get_template_directory_uri() . '/images/default/noimage_450_350.png')); ?> 
                    </a> 
                </figure> 
                <?php if($togglelink == 'image'):?>
                    <span class="rehub-main-color fontnormal r_show_hide mt10 blockstyle textcenter ml5 font80"><?php esc_html_e('More details +', 'rehub-theme');?></span>
                <?php endif;?>                             
            </div>
        <?php endif;?>

        <?php if($contentpos == 'titleexc'):?>
            <div class="rh-flex-grow1 listitem_title listitem_column">
                <<?php echo esc_attr($headingtag);?> class="top_rating_title fontbold blackcolor"><a href="<?php echo ''.$link;?>"<?php echo ''.$target;?>><?php the_title();?></a></<?php echo esc_attr($headingtag);?>>
                <div class="postcont">
                    <?php if($post->post_excerpt):?>
                        <?php echo ''.$post->post_excerpt; ?>
                    <?php else:?>
                        <?php kama_excerpt('maxchar=250'); ?>
                    <?php endif;?>
                </div>
                <?php if(!empty($contshortcode)):?>
                    <div class="list_shortcode_area mb10">     
                        <?php 
                        $contshortcode = urldecode($contshortcode); 
                        $contshortcode = wp_kses_post($contshortcode);?>
                        <?php echo do_shortcode($contshortcode);?>                    
                    </div>
                <?php endif;?>                 
                <?php if($isproduct):?>
                    <?php if (isset($userrating) && $userrating=='1') : ?>
                        <div class="list_userrating_area">
                            <?php $average_rating = $product->get_average_rating();
                            if ($average_rating > 0) {
                                echo wc_get_rating_html($average_rating);
                            } ?>
                        </div>
                    <?php endif; ?>
                    <div class="button_action">
                        <div class="floatleft mr5 disablefloattablet">
                            <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
                            <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
                            <?php echo RH_get_wishlist($postid, '', $wishlistadded, $wishlistremoved);?>  
                        </div>
                        <?php if(rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) :?>
                            <span class="compare_for_grid floatleft disablefloattablet">            
                                <?php 
                                    $cmp_btn_args = array(); 
                                    $cmp_btn_args['class']= 'comparecompact';
                                    if(rehub_option('compare_woo_cats') != '') {
                                        $cmp_btn_args['woocats'] = esc_html(rehub_option('compare_woo_cats'));
                                    }
                                ?>                                                  
                                <?php echo wpsm_comparison_button($cmp_btn_args); ?> 
                            </span>
                        <?php endif;?>                                                            
                    </div>
                <?php endif;?>                 
            </div>
        <?php endif;?>
        
        
        <?php if(!empty($section) && isset($section[0]['sectiontype']) && $section[0]['sectiontype'] != 'empty'):?>
            <div class="rh-flex-center-align rh-flex-justify-center rh-flex-grow1 listitem_content_meta<?php if(isset($stackmobile) && $stackmobile):?> mobilesblockdisplay<?php endif;?>">
                <?php foreach ($section as $item):?>
                    <div class="rh-flex-center-align listitem_meta_index rh-flex-grow1 rh-flex-justify-center text-center elementor-repeater-item-<?php echo esc_attr($item['_id']);?>">
                        <div>
                            <?php $type = $item['sectiontype'];?>
                            <?php if ($type == 'custom'):?>
                                <div class="listitem_custom_val">
                                <?php 
                                $atts = array();
                                if(!empty($item['field'])){
                                    $atts['type'] = 'custom';
                                    $atts['field']=$item['field'];
                                    if(!empty($item['unit'])){
                                        if(!empty($item['unitbefore'])){
                                            $atts['label']= $item['unit'];
                                        }else{
                                            $atts['posttext']=$item['unit'];
                                        }
                                    }
                                    $atts['show_empty']= '1';
                                    if (!empty($item['imageMapper'])) {
                                        $atts['imageMapper'] = $item['imageMapper'];
                                    }
                                    echo wpsm_get_custom_value($atts);
                                }
                                ?>
                                </div>  
                            <?php elseif ($type == 'attribute' || $type == 'swatch'):?>
                                <div class="listitem_custom_val">
                                <?php 
                                $atts = array();
                                if(!empty($item['attrfield'])){
                                    $atts['type'] = $type;
                                    $atts['attrfield']=$item['attrfield'];
                                    if(!empty($item['unit'])){
                                        if(!empty($item['unitbefore'])){
                                            $atts['label']= $item['unit'];
                                        }else{
                                            $atts['posttext']=$item['unit'];
                                        }
                                    }
                                    $atts['show_empty']= '1';
                                    echo wpsm_get_custom_value($atts);
                                }
                                ?>
                                </div> 
                            <?php elseif ($type == 'shortcode'):?>
                                <div class="listitem_custom_val listitem_custom_val_shortcode">
                                <?php 
                                if(!empty($item['shortcodefield'])){
                                    if(!empty($item['unit']) && !empty($item['unitbefore'])){
                                        echo '<span class="meta_v_label">'.esc_attr($item['unit']).'</span>';
                                    }
                                    $sectionshortcode = urldecode($item['shortcodefield']); 
                                    $sectionshortcode = wp_kses_post($sectionshortcode); 
                                    echo do_shortcode($sectionshortcode);
                                    if(!empty($item['unit']) && empty($item['unitbefore'])){
                                        echo '<span class="meta_v_posttext">'.esc_attr($item['unit']).'</span>';
                                    }                                
                                }
                                ?>                                                    
                                </div>                                                                          
                            <?php endif;?>
                            <?php $posttext = rh_check_empty_index($item, 'posttext');?>
                            <?php if($posttext):?>
                                <div class="meta_posttext blockstyle"><?php echo esc_html($posttext);?></div>
                            <?php endif;?> 
                            <?php $tooltip = rh_check_empty_index($item, 'tooltip');?>                      
                            <?php if ($tooltip) :?>
                                <span class="wpsm_spec_meta_tooltip lineheight20 blockstyle"><?php echo wpsm_shortcode_tooltip(array('text' => '<i class="rhicon rhi-question-circle greycolor font80"></i>'), $tooltip);?></span>
                            <?php endif;?> 
                        </div>                                              
                    </div> 
                <?php endforeach;?>
            </div>
        <?php endif;?>        
        
        <?php if(isset($review) && $review):?>
            <div class="listbuild_review listitem_column text-center">
                <?php if($isproduct):?>
                    <?php $overall_review  = get_post_meta($postid, 'rehub_review_overall_score', true);?>
                    <?php if ($overall_review){ $overall_review = $overall_review;}?>
                <?php else:?>   
                    <?php $overall_review  = rehub_get_overall_score();?>
                <?php endif;?>                  
                <div class="top-rating-item-circle-view">
                    <div class="radial-progress" data-rating="<?php echo ''.$overall_review?>">
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
                            <div class="percentage"><?php echo ''.$overall_review ?></div>
                        </div>
                    </div>
                </div>             
            </div>
        <?php endif;?> 
        <?php if(isset($button) && $button):?>
            <div class="listbuild_btn listitem_column text-center">
                <?php if($isproduct):?>
                    <span class="rehub-btn-font price font110 mb15 fontbold"><?php echo ''.$product->get_price_html(); ?></span>
                    <div class="mb10"></div>
                 
                    <?php if ( $product->add_to_cart_url() !='') : ?>
                        <div class="priced_block">
                        <?php  echo apply_filters( 'woocommerce_loop_add_to_cart_link',
                            sprintf( '<a href="%s" data-product_id="%s" data-product_sku="%s" class="re_track_btn woo_loop_btn btn_offer_block %s %s product_type_%s"%s %s>%s</a>',
                            esc_url( $product->add_to_cart_url() ),
                            esc_attr( $product->get_id() ),
                            esc_attr( $product->get_sku() ),
                            $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                            $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
                            esc_attr( $product->get_type() ),
                            $product->get_type() =='external' ? ' target="_blank"' : '',
                            $product->get_type() =='external' ? ' rel="nofollow sponsored"' : '',
                            esc_html( $product->add_to_cart_text() )
                            ),
                        $product );?> 
                        </div>
                    <?php endif; ?>                         
                <?php else:?>   
                    <?php rehub_generate_offerbtn('wrapperclass=block_btnblock mobile_block_btnclock mb5');?>
                <?php endif;?>                 
                <a href="<?php the_permalink();?>" class="read_full font70"><?php if(rehub_option('rehub_review_text') !='') :?><?php echo rehub_option('rehub_review_text') ; ?><?php else :?><?php esc_html_e('Read full review', 'rehub-theme'); ?><?php endif ;?></a> 
                <?php if($togglelink == 'button'):?>
                    <span class="def_btn fontnormal r_show_hide mt10"><?php esc_html_e('More details +', 'rehub-theme');?></span>
                <?php endif;?>                             
            </div>
        <?php endif;?>               
    </div> 
    <?php if($disclaimer):?>
        <?php $field = trim(esc_html($disclaimer)); $disclaimercontent = get_post_meta($postid, $field, true);?>
        <?php if($disclaimercontent):?>
            <div class="rev_disclaimer lightbluebg font70 lineheight15 pt10 pb10 pl15 pr15 flowhidden">
                <?php if($togglelink == 'disclaimer'):?>
                    <span class="def_btn fontnormal floatright r_show_hide ml15 rtlmr15"><?php esc_html_e('More details +', 'rehub-theme');?></span>
                <?php endif;?>                
                <?php echo do_shortcode(wp_kses($disclaimercontent, 'post'));?>
            </div>
        <?php endif;?>    
    <?php endif;?>  
    <?php if($togglelink !='no'):?>
        <div class="open_dls_onclk flowhidden border-top pr25 pl25">

            <?php if($togglecontent=='review'):?>
                <?php $summary = rehub_exerpt_function(array('reviewtext'=> '1', 'length'=> ''));?>
                <?php $heading = rehub_exerpt_function(array('reviewheading'=> '1'));?>
                <?php $criteriascore = rehub_exerpt_function(array('reviewcriterias'=> 'editor'));?>
                <?php $prosvalues = rehub_exerpt_function(array('reviewpros'=> '1'));?>
                <?php $consvalues = rehub_exerpt_function(array('reviewcons'=> '1'));?>
                <?php if ($summary):?>
                    <div class="border-grey-bottom mt15 pb15">
                        <?php if ($heading):?><div class="font140 fontbold mb15"><?php echo esc_html($heading);?></div><?php endif;?>
                        <?php echo rehub_kses($summary);?>
                    </div>
                <?php endif;?>
                <?php $colclass = ($criteriascore) ? 'wpsm-one-third' : 'wpsm-one-half';?>
                <?php if($criteriascore) : ?>
                    <div class="pt20 pb20 floatleft <?php echo ''.$colclass?>">
                        <?php echo ''.$criteriascore; ?>
                    </div>
                <?php endif; ?>     
                <!-- PROS CONS BLOCK-->
                <?php if(!empty($prosvalues)):?>
                    <div class="wpsm_pros pt20 pb20 floatleft font90 <?php echo ''.$colclass?>">
                        <div class="title_pros"><?php esc_html_e('PROS:', 'rehub-theme');?></div>
                        <?php echo ''.$prosvalues; ?>
                    </div>
                <?php endif;?>  
                <?php if(!empty($consvalues)):?>
                    <div class="disablemobilepadding wpsm_cons floatleft pt20 pb20 font90 <?php echo ''.$colclass?>">
                        <div class="title_cons"><?php esc_html_e('CONS:', 'rehub-theme');?></div>
                        <?php echo ''.$consvalues; ?>
                    </div>
                <?php endif;?>  
                <!-- PROS CONS BLOCK END--> 
            <?php elseif($togglecontent=='content'):?>
                <article class="post pt20 pb20"><?php echo apply_filters('the_content', $post->post_content); ?></article>
            <?php elseif($togglecontent=='field'):?>
                <?php $field = trim(esc_html($togglefield)); $cont = get_post_meta($postid, $field, true);?>
                <?php if($cont):?>
                <article class="post pt20 pb20"><?php echo apply_filters('the_content', $cont); ?></article> 
                <?php endif;?>               
            <?php endif;?> 


        </div>
    <?php endif;?>     
</div>