<?php

if ( ! defined('ABSPATH') ) {
    exit('restricted access');
}

return [
    'id'               => 'size_table_info',
    'title'            => 'Size table Info for Popups',
    'thumbnail'        => $local_dir_url . 'thumbnail.jpg',
    'tmpl_created'     => time(),
    'author'           => 'WPSM',
    'url'              => $ext_dir_url . 'size-table-for-popup',
    'type'             => 'block',
    'subtype'          => 'wpsm',
    'tags'             => ['popup', 'info'],
    'menu_order'       => 1,
    'popularity_index' => 10,
    'trend_index'      => 1,
    'is_pro'           => 0,
    'has_page_settings'=> 0
];
