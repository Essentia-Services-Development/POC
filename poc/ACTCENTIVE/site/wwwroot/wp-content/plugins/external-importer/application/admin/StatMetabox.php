<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\helpers\WooHelper;
use ExternalImporter\application\components\WooImporter;
use ExternalImporter\application\helpers\InputHelper;
use ExternalImporter\application\components\Synchronizer;
use ExternalImporter\application\components\Throttler;
use ExternalImporter\application\LinkProcessor;

/**
 * StatMetabox class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class StatMetabox {

    public function __construct()
    {
        \add_action('add_meta_boxes', array($this, 'addMetabox'));
        \add_action('wp_ajax_ei_update_product', array($this, 'ajaxUpdateProduct'));
    }

    public function addMetabox($post_type)
    {
        if ($post_type !== 'product')
            return;

        global $post;

        if (!WooHelper::isEiProduct($post->ID))
            return;

        \add_meta_box('ei_stat_metabox', __('Product synchronization', 'external-importer'), array($this, 'renderMetabox'), $post_type, 'side', 'default');
    }

    public static function renderMetabox($post)
    {
        echo '<div id="ei_update_metabox_response">';
        echo self::metaboxHtml($post->ID);
        echo '</div>';

        $product = WooImporter::getProductMeta($post->ID);
        ?>

        <div style="padding-top: 10px;">
            <?php \wp_nonce_field('ei_update_product', 'ei_nonce'); ?>
            <a title="<?php echo \esc_attr(__('View product', 'external-importer')); ?>" target="_blank" href="<?php echo \esc_url($product->link); ?>">
                <?php echo \esc_attr(__('Direct link', 'external-importer')); ?>
            </a>
            
            <?php $affiliate_url = LinkProcessor::generateAffiliateUrl($product->link); ?>
            <?php if($affiliate_url != $product->link):?>
            | 
            <a title="<?php echo \esc_attr(__('Affiliate link', 'external-importer')); ?>" target="_blank" href="<?php echo \esc_url($affiliate_url); ?>">
                Affiliate link
            </a>
            <?php endif; ?>
            
            <input style="float: right;" type="submit" id="ei_update_product" class="button button-large" value="<?php echo \esc_attr(__('Sync now', 'external-importer')); ?>">
            <div class="clear"></div>
        </div>
        <script>
            jQuery(document).ready(function ($) {
                $('#ei_update_product').click(function (e) {
                    e.preventDefault();
                    var this_btn = $(this);
                    this_btn.attr('disabled', true);

                    var nonce = this_btn.parent().find("#ei_nonce").val();
                    $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: {
                            action: 'ei_update_product',
                            ei_nonce: nonce,
                            product_id: <?php echo $post->ID; ?>
                        },
                        success: function (data) {
                            $("#ei_update_metabox_response").html(data);
                            this_btn.attr('disabled', false);
                            location.reload();
                        },
                        error: function (errorThrown) {
                            location.reload();
                        }
                    });
                    return false;
                });
            });


        </script>
        <?php
    }

    public static function metaboxHtml($product_id)
    {
        if (!$last_update = WooImporter::getLastUpdateMeta($product_id))
            return '';

        $info = WooImporter::getProductInfoMeta($product_id);
        $product = WooImporter::getProductMeta($product_id);

        // last update
        $res = '<div><span id="ei-last-updated">' . __('Last updated:', 'external-importer') . ' <b>' . WooHelper::dateFormatHuman($last_update) . '</b></span></div>';

        // http status
        $res .= '<div><span id="ei-status">' . __('Status:', 'external-importer') . ' ';
        if ($info['last_error'])
        {
            $res .= '<span style="color:red;"><b>' . __('Error', 'external-importer') . '</b></span>';
            if ($info['last_error_code'] && $info['last_error_code'] > 300)
                $res .= '<br>' . __('Status code:', 'external-importer') . ' <b>' . \esc_html($info['last_error_code']) . '</b>';
        } else
            $res .= '<span style="color:green;"><b>' . __('Success', 'external-importer') . '</b></span>';
        $res .= '</span></div>';

        // stock status
        $res .= '<div><span id="ei-status">' . __('Availability:', 'external-importer') . ' ';
        if ($product->inStock)
            $res .= '<span style="color:#7ad03a;"><b>' . __('In stock', 'external-importer') . '</b></span>';
        else
        {
            $res .= '<span style="color:#a44;"><b>' . __('Out of stock', 'external-importer') . '</b></span>';
            if (!empty($info['last_in_stock']))
                $res .= '<br><span id="ei-last-in-stock">' . __('Last in stock:', 'external-importer') . ' <b>' . WooHelper::dateFormatFromGmt($info['last_in_stock']) . '</b></span>';
        }
        $res .= '</div>';

        if (Throttler::isThrottled($product->domain))
            $res .= ' <div style="color:orange;"> ' . __('Currently throttled', 'external-importer') . '</div>';

        if ($info['last_error'])
            $res .= '<div style="padding-top: 5px;"><em><u>' . \esc_html($info['last_error']) . '</u></em></div>';

        return $res;
    }

    public function ajaxUpdateProduct()
    {
        if (!isset($_POST['ei_nonce']) || !\wp_verify_nonce($_POST['ei_nonce'], 'ei_update_product'))
            \wp_die('Invalid nonce');

        if (!\current_user_can('edit_posts'))
            \wp_die('You don\'t have access to this page.');

        if (!$product_id = (int) InputHelper::post('product_id'))
            \wp_die('Invalid product ID');

        Synchronizer::updateProduct($product_id);

        echo self::metaboxHtml($product_id);
        \wp_die();
    }

}
