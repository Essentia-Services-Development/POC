<?php

if ( ! defined('ABSPATH') ) {
    exit('restricted access');
}

return [
    'id'               => 'top_product_block',
    'title'            => esc_html__('Top product block', 'rehub-theme'),
    'thumbnail'        => $local_dir_url . 'thumbnail.jpg',
    'tmpl_created'     => time(),
    'author'           => 'WPSM',
    'url'              => $ext_dir_url . 'top-product-block',
    'type'             => 'block',
    'subtype'          => 'wpsm',
    'tags'             => ['product', 'top'],
    'menu_order'       => 0,
    'popularity_index' => 10,
    'trend_index'      => 1,
    'is_pro'           => 0,
    'has_page_settings'=> 0
];
