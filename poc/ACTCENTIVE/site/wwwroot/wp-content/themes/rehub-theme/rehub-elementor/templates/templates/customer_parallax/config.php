<?php

if ( ! defined('ABSPATH') ) {
    exit('restricted access');
}

return [
    'id'               => 'customer_parallax',
    'title'            => esc_html__('Customer review with parallax', 'rehub-theme'),
    'thumbnail'        => $local_dir_url . 'thumbnail.jpg',
    'tmpl_created'     => time(),
    'author'           => 'WPSM',
    'url'              => $ext_dir_url . 'customer-reviews-parallax',
    'type'             => 'block',
    'subtype'          => 'wpsm',
    'tags'             => ['review', 'testimonial'],
    'menu_order'       => 0,
    'popularity_index' => 10,
    'trend_index'      => 1,
    'is_pro'           => 0,
    'has_page_settings'=> 0
];
