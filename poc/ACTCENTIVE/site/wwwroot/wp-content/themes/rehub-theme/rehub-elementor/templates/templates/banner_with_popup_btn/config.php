<?php

if ( ! defined('ABSPATH') ) {
    exit('restricted access');
}

return [
    'id'               => 'banner_with_popup_btn',
    'title'            => 'Banner with popup button',
    'thumbnail'        => $local_dir_url . 'thumbnail.jpg',
    'tmpl_created'     => time(),
    'author'           => 'WPSM',
    'url'              => $ext_dir_url . 'info-banner-with-popup',
    'type'             => 'block',
    'subtype'          => 'wpsm',
    'tags'             => ['banner', 'popup', 'info'],
    'menu_order'       => 0,
    'popularity_index' => 10,
    'trend_index'      => 1,
    'is_pro'           => 0,
    'has_page_settings'=> 0
];
