<?php

if ( ! defined('ABSPATH') ) {
    exit('restricted access');
}

return [
    'id'               => 'locator',
    'title'            => 'Locator for GMW',
    'thumbnail'        => $local_dir_url . 'thumbnail.jpg',
    'tmpl_created'     => time(),
    'author'           => 'WPSM',
    'url'              => 'https://retour.wpsoul.com/tour-locator/',
    'type'             => 'page',
    'subtype'          => 'wpsm',
    'tags'             => ['home'],
    'menu_order'       => 0,
    'popularity_index' => 10,
    'trend_index'      => 1,
    'is_pro'           => 0,
    'has_page_settings'=> 1
];
