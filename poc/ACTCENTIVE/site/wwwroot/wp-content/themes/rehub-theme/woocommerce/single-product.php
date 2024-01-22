<?php

/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see         http://docs.woothemes.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     1.6.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header(); ?>

<?php global $post; ?>
<?php $rh_product_layout_style = get_post_meta($post->ID, '_rh_woo_product_layout', true); ?>
<?php if ($rh_product_layout_style == '' || $rh_product_layout_style == 'global') {
    $rh_product_layout_style = rehub_option('product_layout_style');
} ?>
<?php if ($rh_product_layout_style == '') : ?>
    <?php $rh_product_layout_style = 'default_full_width'; ?>
<?php endif; ?>
<?php
$custombg = get_post_meta($post->ID, '_woo_code_bg', true);
if ($custombg) {
    if ($rh_product_layout_style == 'woo_compact' || $rh_product_layout_style == 'side_block' || $rh_product_layout_style == 'side_block_light' || $rh_product_layout_style == 'side_block_video') {
        $cssbg = "#rh_woo_layout_inimage{background:" . esc_attr($custombg) . " !important;}";
    } elseif ($rh_product_layout_style == 'full_width_extended' || $rh_product_layout_style == 'full_width_advanced') {
        $cssbg = "#content .top-woo-area, .content-woo-section, #contents-section-woo-area{background:" . esc_attr($custombg) . " !important;}.re_wooinner_cta_wrapper{background-color:white !important}#contents-section-woo-area{border:none; box-shadow:none; padding-top:15px}";
    } elseif ($rh_product_layout_style == 'woo_directory') {
        $cssbg = ".woo_directory_layout{background:" . esc_attr($custombg) . " !important;}";
    } elseif ($rh_product_layout_style == 'sections_w_sidebar') {
        $cssbg = ".sections_w_sidebar{background:" . esc_attr($custombg) . " !important;}";
    } else {
        $cssbg = "body{background:" . esc_attr($custombg) . " !important;}";
    }
    wp_register_style('woobg-inline-style', false);
    wp_enqueue_style('woobg-inline-style');
    wp_add_inline_style('woobg-inline-style', $cssbg);
}
?>
<?php if (is_numeric($rh_product_layout_style) && function_exists('rh_wp_reusable_render')) : ?>
    <div class="post woocommerce product">
        <?php echo rh_wp_reusable_render(array('id' => $rh_product_layout_style));?> 
    </div>
<?php else : ?>
    <?php if ($rh_product_layout_style == 'default_with_sidebar') : ?>
        <?php include(rh_locate_template('inc/product_layout/default_with_sidebar.php')); ?>
    <?php elseif ($rh_product_layout_style == 'default_no_sidebar') : ?>
        <?php include(rh_locate_template('inc/product_layout/default_no_sidebar.php')); ?>
    <?php elseif ($rh_product_layout_style == 'default_full_width') : ?>
        <?php include(rh_locate_template('inc/product_layout/default_full_width.php')); ?>
    <?php elseif ($rh_product_layout_style == 'full_width_extended') : ?>
        <?php include(rh_locate_template('inc/product_layout/full_width_extended.php')); ?>
    <?php elseif ($rh_product_layout_style == 'side_block') : ?>
        <?php include(rh_locate_template('inc/product_layout/side_block.php')); ?>
    <?php elseif ($rh_product_layout_style == 'side_block_light') : ?>
        <?php include(rh_locate_template('inc/product_layout/side_block_light.php')); ?>
    <?php elseif ($rh_product_layout_style == 'side_block_video') : ?>
        <?php include(rh_locate_template('inc/product_layout/side_block_video.php')); ?>
    <?php elseif ($rh_product_layout_style == 'full_width_advanced') : ?>
        <?php include(rh_locate_template('inc/product_layout/full_width_advanced.php')); ?>
    <?php elseif ($rh_product_layout_style == 'ce_woo_list') : ?>
        <?php include(rh_locate_template('inc/product_layout/ce_woo_list.php')); ?>
    <?php elseif ($rh_product_layout_style == 'ce_woo_sections') : ?>
        <?php include(rh_locate_template('inc/product_layout/ce_woo_sections.php')); ?>
    <?php elseif ($rh_product_layout_style == 'ce_woo_blocks') : ?>
        <?php include(rh_locate_template('inc/product_layout/ce_woo_blocks.php')); ?>
    <?php elseif ($rh_product_layout_style == 'full_photo_booking') : ?>
        <?php include(rh_locate_template('inc/product_layout/full_photo_booking.php')); ?>
    <?php elseif ($rh_product_layout_style == 'vendor_woo_list') : ?>
        <?php include(rh_locate_template('inc/product_layout/vendor_woo_list.php')); ?>
    <?php elseif ($rh_product_layout_style == 'sections_w_sidebar') : ?>
        <?php include(rh_locate_template('inc/product_layout/sections_w_sidebar.php')); ?>
    <?php elseif ($rh_product_layout_style == 'compare_woo_list') : ?>
        <?php include(rh_locate_template('inc/product_layout/compare_woo_list.php')); ?>
    <?php elseif ($rh_product_layout_style == 'woo_compact') : ?>
        <?php include(rh_locate_template('inc/product_layout/woo_compact.php')); ?>
    <?php elseif ($rh_product_layout_style == 'woo_directory') : ?>
        <?php include(rh_locate_template('inc/product_layout/woo_directory.php')); ?>
    <?php elseif ($rh_product_layout_style == 'darkwoo') : ?>
        <?php include(rh_locate_template('inc/product_layout/darkwoo.php')); ?>
    <?php elseif ($rh_product_layout_style == 'marketplace') : ?>
        <?php include(rh_locate_template('inc/product_layout/full_width_marketplace.php')); ?>
    <?php elseif ($rh_product_layout_style == 'woostack') : ?>
        <?php include(rh_locate_template('inc/product_layout/woostack.php')); ?>
    <?php else : ?>
        <?php include(rh_locate_template('inc/product_layout/default_full_width.php')); ?>
    <?php endif; ?>
<?php endif; ?>


<!-- FOOTER -->
<?php get_footer(); ?>