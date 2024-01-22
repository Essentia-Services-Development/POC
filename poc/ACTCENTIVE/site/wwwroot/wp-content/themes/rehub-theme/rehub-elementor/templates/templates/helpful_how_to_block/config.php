<?php

if ( ! defined('ABSPATH') ) {
    exit('restricted access');
}

return [
    'id'               => 'helpful_how_to_block',
    'title'            => esc_html__('Helpful How to Block', 'rehub-theme'),
    'thumbnail'        => $local_dir_url . 'thumbnail.jpg',
    'tmpl_created'     => time(),
    'author'           => 'WPSM',
    'url'              => $ext_dir_url . 'helpfull-how-to-block',
    'type'             => 'block',
    'subtype'          => 'wpsm',
    'tags'             => ['helpful', 'howto', 'numbers'],
    'menu_order'       => 0,
    'popularity_index' => 10,
    'trend_index'      => 1,
    'is_pro'           => 0,
    'has_page_settings'=> 0
];
