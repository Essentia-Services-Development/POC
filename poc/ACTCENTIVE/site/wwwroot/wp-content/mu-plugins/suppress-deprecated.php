<?php
/*
Plugin Name: Suppress Deprecated Notices
Description: A simple plugin to suppress deprecated notices.
Version: 1.0
Author: Your Name
*/

add_action('init', function() {
    error_reporting( E_ALL & ~E_DEPRECATED );
});
