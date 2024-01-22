<?php

if ( ! defined('ABSPATH') ) {
    exit('restricted access');
}

return [
    'id'               => '3_cols_price_table_with_images',
    'title'            => '3 Columns Price table with images',
    'thumbnail'        => $local_dir_url . 'thumbnail.jpg',
    'tmpl_created'     => time(),
    'author'           => 'WPSM',
    'url'              => $ext_dir_url . '3-column-price-table',
    'type'             => 'block',
    'subtype'          => 'wpsm',
    'tags'             => ['3 columns', 'price', 'pricing', 'table'],
    'menu_order'       => 0,
    'popularity_index' => 10,
    'trend_index'      => 1,
    'is_pro'           => 0,
    'has_page_settings'=> 0
];
