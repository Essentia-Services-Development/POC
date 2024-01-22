<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>
<?php global $post; ?>
<?php

$columns = (isset($columns)) ? $columns : '';
$gridtype = (isset($gridtype)) ? $gridtype : '';
$postid = $post->ID;

if (!isset($disable_btn) || !$disable_btn) {
    $disable_btn = (rehub_option('rehub_enable_btn_recash') == 1) ? '' : 1;
    if (rehub_option('disable_btn_offer_loop')) {
        $disable_btn = 1;
    }
}
if (!isset($disable_act)) {
    $disable_act = (rehub_option('disable_grid_actions') == 1) ? 1 : '';
}
if (!isset($aff_link)) {
    $aff_link = (rehub_option('disable_inner_links') == 1) ? 1 : '';
}
if (!isset($price_meta)) {
    $price_meta = (rehub_option('price_meta_grid')) ? rehub_option('price_meta_grid') : '1';
}
?>

<?php $offer_coupon = get_post_meta( $postid, 'rehub_offer_product_coupon', true ); ?>
<?php $offer_coupon_date = get_post_meta( $postid, 'rehub_offer_coupon_date', true ); ?>
<?php $offer_coupon_mask = get_post_meta( $postid, 'rehub_offer_coupon_mask', true ); ?>
<?php $coupon_style = $expired =''; if(!empty($offer_coupon_date)) : ?>
    <?php
    $timestamp1 = strtotime($offer_coupon_date);
    if(strpos($offer_coupon_date, ':') ===false){
        $timestamp1 += 86399;
    }
    $seconds = $timestamp1 - (int)current_time('timestamp',0);
    $days = floor($seconds / 86400);
    $seconds %= 86400;
    if ($days > 0) {
        $coupon_text = $days.' '.__('days left', 'rehub-theme');
        $coupon_style = '';
        $expired = 'no';
    }
    elseif ($days == 0){
      $coupon_text = esc_html__('Last day', 'rehub-theme');
      $coupon_style = '';
      $expired = 'no';
    }
    else {
      $coupon_text = esc_html__('Expired', 'rehub-theme');
      $coupon_style = ' expired_coupon';
      $expired = '1';
    }
    ?>
<?php endif;?>

<?php
if ($aff_link == '1') {
    $link = rehub_create_affiliate_link();
    $target = ' rel="nofollow sponsored" target="_blank"';
} else {
    $link = get_the_permalink();
    $target = '';
}
?>

<?php 
    $offer_post_url = esc_url(get_post_meta( $postid, 'rehub_offer_product_url', true ));
    $offer_post_url = apply_filters('rehub_create_btn_url', $offer_post_url);
?>
<?php $offer_url = apply_filters('rh_post_offer_url_filter', $offer_post_url ); ?>
<?php if(empty($offer_url)) {$offer_url = get_the_permalink($postid);}?>
<?php $coupon_mask_enabled = (!empty($offer_coupon) && ($offer_coupon_mask =='1' || $offer_coupon_mask =='on') && $expired!='1') ? '1' : ''; ?>
<?php $outsidelinkpart = ($coupon_mask_enabled=='1' && $aff_link=='1') ? ' data-codeid="'.$postid.'" data-dest="'.$offer_url.'" data-clipboard-text="'.$offer_coupon.'" ' : '';?>

<?php
$dealcat = '';
if (rehub_option('enable_brand_taxonomy') == 1) {
    $dealcats = wp_get_post_terms($post->ID, 'dealstore', array("fields" => "all"));
    if (!empty($dealcats) && !is_wp_error($dealcats)) {
        $dealcat = $dealcats[0];
    }
}
?>
<article class="col_item offer_grid rehub-sec-smooth offer_grid_com mobile_compact_grid<?php if ($gridtype == 'compact') : ?> coupon_grid <?php elseif ($gridtype == 'mobile') : ?> mobile_grid <?php endif; ?><?php if ($disable_btn == 1) : ?> no_btn_enabled<?php endif; ?><?php if ($disable_act != 1 && $gridtype != 'compact') : ?> offer_act_enabled<?php endif; ?><?php echo rh_expired_or_not($post->ID, 'class'); ?>">
    <div class="info_in_dealgrid">
        <?php echo re_badge_create('ribbonleft'); ?>
        <figure class="mb15">
            <?php
            $offer_price_old = get_post_meta($post->ID, 'rehub_offer_product_price_old', true);
            $offer_price_old = apply_filters('rehub_create_btn_price_old', $offer_price_old);
            if (!empty($offer_price_old)) {
                $offer_price = get_post_meta($post->ID, 'rehub_offer_product_price', true);
                $offer_price = apply_filters('rehub_create_btn_price', $offer_price);
                if (!empty($offer_price)) {
                    $offer_pricesale = (float)rehub_price_clean($offer_price); //Clean price from currence symbols
                    $offer_priceold = (float)rehub_price_clean($offer_price_old); //Clean price from currence symbols
                    if ($offer_priceold != '0' && is_numeric($offer_priceold) && $offer_priceold > $offer_pricesale) {
                        $off_proc = 0 - (100 - ($offer_pricesale / $offer_priceold) * 100);
                        $off_proc = round($off_proc);
                        echo '<span class="grid_onsale">' . $off_proc . '%</span>';
                    }
                }
            }

            ?>
            <a class="img-centered-flex rh-flex-center-align rh-flex-justify-center re_track_btn<?php echo ($outsidelinkpart) ? ' masked_coupon' : '';?>" href="<?php echo '' . $link; ?>" <?php echo '' . $target; ?> <?php echo ''.$outsidelinkpart; ?>>
                <?php $discountpercentage = get_post_meta($post->ID, 'rehub_offer_discount', true); ?>
                <?php if ($discountpercentage) : ?>
                    <span class="height-80 rh-flex-center-align rh-flex-justify-center sale_tag_inwoolist text-center">
                        <div class="font150 fontbold greencolor mb0 ml0 mr0 mt0 overflow-elipse pb0 pl0 pr0 pt0"><?php echo esc_html($discountpercentage); ?></div>
                    </span>
                <?php else : ?>
                    <?php if (isset($custom_col)) : ?>
                        <?php
                        $showimg = new WPSM_image_resizer();
                        $showimg->use_thumb = true;
                        $showimg->no_thumb = get_template_directory_uri() . '/images/default/noimage_220_150.png';
                        ?>
                        <?php $showimg->width = (int)$custom_img_width; ?>
                        <?php $showimg->height = (int)$custom_img_height; ?>
                        <?php $showimg->show_resized_image(); ?>
                    <?php else : ?>
                        <?php wpsm_thumb('smallgrid'); ?>
                    <?php endif; ?>
                <?php endif; ?>
            </a>
        </figure>
        <?php do_action('rehub_after_compact_grid_figure'); ?>
        <div class="grid_desc_and_btn">
            <div class="grid_row_info flowhidden">
                <?php if ($price_meta != '4' && $gridtype != 'compact' && $gridtype != 'mobile') : ?>
                    <div class="flowhidden mb15">
                        <div class="price_for_grid redbrightcolor floatleft">
                            <?php rehub_generate_offerbtn('showme=price&wrapperclass=mb0'); ?>
                        </div>
                        <div class="floatright vendor_for_grid">
                            <?php if ($price_meta == '1') : ?>
                                <?php $author_id = $post->post_author;
                                $name = get_the_author_meta('display_name', $author_id); ?>
                                <a class="admin" href="<?php echo get_author_posts_url($author_id) ?>" title="<?php echo esc_attr($name) ?>">
                                    <?php echo get_avatar($author_id, '22', '', $name); ?>
                                </a>
                            <?php elseif ($price_meta == '2') : ?>
                                <div class="aff_tag mb10">
                                    <?php WPSM_Postfilters::re_show_brand_tax('logo'); //show brand logo
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php do_action('rehub_after_compact_grid_price'); ?>
                <h3 class="flowhidden mb10 mt0 fontnormal position-relative <?php if (rehub_option('hotmeter_disable') != '1') : ?><?php echo getHotIconclass($post->ID); ?><?php endif; ?>"><?php echo rh_expired_or_not($post->ID, 'span'); ?><a href="<?php echo '' . $link; ?>" <?php echo '' . $target; ?> <?php echo ''.$outsidelinkpart; ?> class="re_track_btn<?php echo ($outsidelinkpart) ? ' masked_coupon' : '';?>"><?php the_title(); ?></a></h3>
                <?php if ($gridtype == 'mobile') : ?><?php rehub_generate_offerbtn('showme=price&wrapperclass=mb0 fontbold font110'); ?><?php endif; ?>
                    <?php 
                        $verify = get_post_meta( $postid, 'rehub_offer_verify_label', true );
                        if($verify) {echo '<span class="verifymeta mr5 font80 greencolor"><i class="rhicon rhi-shield-check"></i> '.esc_attr($verify).'</span>';}
                    ?> 
                <?php $custom_notice = get_post_meta($post->ID, '_notice_custom', true); ?>
                <?php
                if ($custom_notice) {
                    echo '<div class="rh_notice_wrap mb10 rehub-sec-color lineheight20 fontbold font90"><div class="rh_custom_notice overflow-elipse">' . esc_html($custom_notice) . '</div></div>';
                } elseif (!empty($dealcat)) {
                    $dealcat_notice = get_term_meta($dealcat->term_id, 'cashback_notice', true);
                    if ($dealcat_notice) {
                        echo '<div class="rh_notice_wrap mb10 rehub-sec-color lineheight20 fontbold font90"><div class="rh_custom_notice overflow-elipse">' . esc_html($dealcat_notice) . '</div></div>';
                    }
                }
                ?>
                <?php if ($gridtype == 'mobile') : ?>
                    <div class="meta_for_grid">
                        <div class="cat_store_for_grid fontitalic floatleft">
                            <?php
                            $term_list = get_the_term_list(get_the_ID(), 'dealstore', '<span class="store_post_meta_item">', ', ', '</span>');
                            if (!is_wp_error($term_list) && $term_list) {
                                echo '<span class="tag_post_store_meta">' . $term_list . '</span>';
                            } else {
                                if ('post' == get_post_type($post->ID) && rehub_option('exclude_cat_meta') != 1) {
                                    $category = get_the_category($post->ID);
                                    if ($category) {
                                        if (class_exists('WPSEO_Primary_Term')) {
                                            $wpseo_primary_term = new WPSEO_Primary_Term('category', $post->ID);
                                            $wpseo_primary_term = $wpseo_primary_term->get_primary_term();
                                            //$termyoast               = get_term( $wpseo_primary_term );
                                            if (!is_numeric($wpseo_primary_term)) {
                                                $first_cat = $category[0]->term_id;
                                            } else {
                                                $first_cat = $wpseo_primary_term;
                                            }
                                        } else {
                                            $first_cat = $category[0]->term_id;
                                        }
                                        meta_small(false, $first_cat, false, false);
                                    }
                                }
                            }
                            ?>
                        </div>
                        <?php if (rehub_option('exclude_date_meta') != 1) : ?>
                            <div class="date_for_grid floatright">
                                <span class="date_ago">
                                    <?php
                                    $offer_coupon_date = get_post_meta($post->ID, 'rehub_offer_coupon_date', true);
                                    $timestamp1 = strtotime($offer_coupon_date);
                                    if (strpos($offer_coupon_date, ':') === false) {
                                        $timestamp1 += 86399;
                                    }
                                    $seconds = $timestamp1 - (int)current_time('timestamp', 0);
                                    $days = floor($seconds / 86400);
                                    $seconds %= 86400;
                                    if ($days > 0) {
                                        $coupon_text = $days . ' ' . __('days left', 'rehub-theme');
                                    } elseif ($days == 0) {
                                        $coupon_text = esc_html__('Last day', 'rehub-theme');
                                        $expired = 'no';
                                    } else {
                                        $coupon_text = esc_html__('Expired', 'rehub-theme');
                                    }
                                    ?>
                                    <?php if ($offer_coupon_date) : ?>
                                        <i class="rhicon rhi-clock"></i><?php echo '' . $coupon_text; ?>
                                    <?php else : ?>
                                        <i class="rhicon rhi-clock"></i><?php printf(esc_html__('%s ago', 'rehub-theme'), human_time_diff(get_the_modified_time('U'), current_time('timestamp'))); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($disable_act != 1) : ?>
                        <div class="font90 greycolor rh-flex-eq-height rh-flex-justify-btw two_col_btn_for_grid">
                            <div class="mr20">
                                <?php echo getHotThumb($post->ID, false); ?>
                            </div>
                            <div>
                                <span class="comm_number_for_grid"><?php echo get_comments_number(); ?></span>
                            </div>
                        </div>
                        <?php do_action('rehub_after_compact_grid_actions'); ?>
                    <?php endif; ?>
                <?php endif; ?>
                <?php do_action('rehub_after_compact_grid_title'); ?>
            </div>
        </div>
    </div>
    <?php rh_post_code_loop();?>
    <?php if ($disable_btn != 1 || $gridtype == 'compact') : ?>
        <div class="mt10 text-center clearbox"><?php rehub_generate_offerbtn('showme=button'); ?></div>
    <?php endif; ?>
    <?php if ($gridtype == 'compact') : ?>
        <?php
        if (!empty($dealcat) && !is_tax('dealstore')) {
            echo '<div class="cpn_store_link clearbox font80 text-center lineheight15"><a href="' . esc_url(get_term_link($dealcat->term_id)) . '">' . esc_attr(sprintf(esc_html__('See All %s offers', 'rehub-theme'), $dealcat->name)) . '</a></div>';
        }
        ?>
    <?php elseif ($gridtype == 'mobile') : ?>

    <?php else : ?>
        <div class="meta_for_grid">
            <div class="cat_store_for_grid floatleft">
                <?php if ($gridtype == 'mobile') : ?>
                    <?php
                    $term_list = get_the_term_list(get_the_ID(), 'dealstore', '<span class="store_post_meta_item">', ', ', '</span>');
                    if (!is_wp_error($term_list) && $term_list) {
                        echo '<span class="tag_post_store_meta">' . $term_list . '</span>';
                    } else {
                        if ('post' == get_post_type($post->ID) && rehub_option('exclude_cat_meta') != 1) {
                            $category = get_the_category($post->ID);
                            if ($category) {
                                if (class_exists('WPSEO_Primary_Term')) {
                                    $wpseo_primary_term = new WPSEO_Primary_Term('category', $post->ID);
                                    $wpseo_primary_term = $wpseo_primary_term->get_primary_term();
                                    //$termyoast               = get_term( $wpseo_primary_term );
                                    if (!is_numeric($wpseo_primary_term)) {
                                        $first_cat = $category[0]->term_id;
                                    } else {
                                        $first_cat = $wpseo_primary_term;
                                    }
                                } else {
                                    $first_cat = $category[0]->term_id;
                                }
                                meta_small(false, $first_cat, false, false);
                            }
                        }
                    }
                    ?>
                <?php else : ?>
                    <div class="cat_for_grid lineheight15">
                        <?php if ('post' == get_post_type($post->ID) && rehub_option('exclude_cat_meta') != 1) : ?>
                            <?php $category = get_the_category($post->ID);  ?>
                            <?php if ($category) {
                                if (class_exists('WPSEO_Primary_Term')) {
                                    $wpseo_primary_term = new WPSEO_Primary_Term('category', $post->ID);
                                    $wpseo_primary_term = $wpseo_primary_term->get_primary_term();
                                    //$termyoast               = get_term( $wpseo_primary_term );
                                    if (!is_numeric($wpseo_primary_term)) {
                                        $first_cat = $category[0]->term_id;
                                    } else {
                                        $first_cat = $wpseo_primary_term;
                                    }
                                } else {
                                    $first_cat = $category[0]->term_id;
                                }
                                meta_small(false, $first_cat, false, false);
                            } ?>
                        <?php endif; ?>
                    </div>
                    <?php do_action('rehub_after_compact_grid_cat'); ?>
                    <div class="store_for_grid">
                        <?php WPSM_Postfilters::re_show_brand_tax('list'); ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (rehub_option('exclude_date_meta') != 1) : ?>
                <div class="date_for_grid floatright">
                    <span class="date_ago">
                        <?php
                        $offer_coupon_date = get_post_meta($post->ID, 'rehub_offer_coupon_date', true);
                        $timestamp1 = strtotime($offer_coupon_date);
                        if (strpos($offer_coupon_date, ':') === false) {
                            $timestamp1 += 86399;
                        }
                        $seconds = $timestamp1 - (int)current_time('timestamp', 0);
                        $days = floor($seconds / 86400);
                        $seconds %= 86400;
                        if ($days > 0) {
                            $coupon_text = $days . ' ' . __('days left', 'rehub-theme');
                        } elseif ($days == 0) {
                            $coupon_text = esc_html__('Last day', 'rehub-theme');
                            $expired = 'no';
                        } else {
                            $coupon_text = esc_html__('Expired', 'rehub-theme');
                        }
                        ?>
                        <?php if ($offer_coupon_date) : ?>
                            <i class="rhicon rhi-clock"></i><?php echo '' . $coupon_text; ?>
                        <?php else : ?>
                            <i class="rhicon rhi-clock"></i><?php printf(esc_html__('%s ago', 'rehub-theme'), human_time_diff(get_the_modified_time('U'), current_time('timestamp'))); ?>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php do_action('rehub_after_compact_grid_meta'); ?>
    <?php if ($disable_act != 1 && $gridtype != 'compact' && $gridtype != 'mobile') : ?>
        <div class="re_actions_for_grid two_col_btn_for_grid border-top">
            <div class="btn_act_for_grid">
                <?php echo getHotThumb($post->ID, false); ?>
            </div>
            <div class="btn_act_for_grid">
                <span class="comm_number_for_grid"><?php echo get_comments_number(); ?></span>
            </div>
        </div>
        <?php do_action('rehub_after_compact_grid_actions'); ?>
    <?php endif; ?>
</article>