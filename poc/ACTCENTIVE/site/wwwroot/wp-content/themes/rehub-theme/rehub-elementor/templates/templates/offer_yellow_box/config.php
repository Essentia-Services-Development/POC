<?php

if ( ! defined('ABSPATH') ) {
    exit('restricted access');
}

return [
    'id'               => 'offer_yellow_box',
    'title'            => esc_html__('Full Width Offer Yellow Box', 'rehub-theme'),
    'thumbnail'        => $local_dir_url . 'thumbnail.jpg',
    'tmpl_created'     => time(),
    'author'           => 'WPSM',
    'url'              => $ext_dir_url . 'special-offer',
    'type'             => 'block',
    'subtype'          => 'wpsm',
    'tags'             => ['CTA', 'yellow', 'box'],
    'menu_order'       => 0,
    'popularity_index' => 10,
    'trend_index'      => 1,
    'is_pro'           => 0,
    'has_page_settings'=> 0
];
