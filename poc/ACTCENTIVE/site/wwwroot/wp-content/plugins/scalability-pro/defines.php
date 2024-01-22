<?php

/* Define plugin constants */
if (!defined('SPRO_ATTRIBUTE_AJAX_LIMIT')) {
    //Plugin path
    define('SPRO_ATTRIBUTE_AJAX_LIMIT', 10);
}
if (!defined('SPRO_REMOVE_SORT_ORDER_POST_TYPES')) {
    define('SPRO_REMOVE_SORT_ORDER_POST_TYPES', array('product', array('product', 'product_variation')));
}
if (!defined('SPRO_REDUCE_IMAGE_SIZES')) {
    define('SPRO_REDUCE_IMAGE_SIZES', true);
}
if (!defined('SPRO_CACHE_PMXE_META_KEYS')) {
    define('SPRO_CACHE_PMXE_META_KEYS', false);
}
if (!defined('SPRO_MAX_TRACE_CHARS')) {
    define('SPRO_MAX_TRACE_CHARS', 10000);
}
if (!defined('SPRO_ALWAYS_DO_TERM_RECOUNTS')) { // Always do term counts for these taxonomies - you can add to this array by adding this define to wp-config.php
    define('SPRO_ALWAYS_DO_TERM_RECOUNTS', array('nav_menu', 'link_category', 'post_format', 'wp_theme', 'wp_template_part_area', 'elementor_library_type', 'elementor_library_category'));
}
if (!defined('SPRO_PREVENT_WPAI_DUP_CHECK')) {
    define('SPRO_PREVENT_WPAI_DUP_CHECK', false); // WP All Import runs a duplicate check against a file - this duplicate check is checking for duplicates *within* the file and is wasteful
}
