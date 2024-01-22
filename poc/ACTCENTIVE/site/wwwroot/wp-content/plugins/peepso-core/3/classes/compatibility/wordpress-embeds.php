<?php

class PeepSo3_WordPress_Embeds {

    private static $instance;

    public static function get_instance() {

        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public $color = 'green';

    protected function __construct() {

        if(!isset($_GET['peepso'])) return;

        add_filter( 'embed_thumbnail_image_size', function( $image_size, $thumbnail_id ) {

            // Apply only to selected post types?
            global $post;
            if(!$post instanceof WP_Post || !in_array($post->post_type, ['post'])) {
                //return $image_size;
            }

            // Do nothing if there is no preference in PeepSo config
            if(!$min_width = PeepSo::get_option_new('embeds_wp_thumb_size')) {
                return $image_size;
            }

            $meta = wp_get_attachment_metadata( $thumbnail_id );

            $sizes_ordered = [];

            if ( ! empty( $meta['sizes'] ) ) {

                foreach ( $meta['sizes'] as $size => $data ) {
                    // Skip squares
                    if($data['width'] == $data['height']) {
                        continue;
                    }

                    $sizes_ordered[$data['width']] = $size;
                }
            }

            $closest = null;

            if(count($sizes_ordered)) {
                ksort($sizes_ordered);

                // Find the closest value possible
                foreach ($sizes_ordered as $width => $size) {
                    if ($closest === null || abs($min_width - $closest) > abs($width - $min_width)) {
                        $closest = $width;
                    }
                }

                // If found, apply it
                if($closest) {
                    $image_size = $sizes_ordered[$closest];
                }
            }

            return $image_size;
        }, 9999, 2 );

    }
}

PeepSo3_WordPress_Embeds::get_instance();