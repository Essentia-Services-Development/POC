<?php

if(!class_exists('PeepSo3_Search_Adapter')) {
    require_once(dirname(__FILE__) . '/search_adapter.php');
    //new PeepSoError('Autoload issue: PeepSo3_Search_Adapter not found ' . __FILE__);
}

if(!class_exists('PeepSo3_Search_Adapter_WP')) {
    require_once(dirname(__FILE__) . '/search_adapter_wp.php');
    //new PeepSoError('Autoload issue: PeepSo3_SearchAdapter__WP not found ' . __FILE__);
}

class PeepSo3_Search_Adapter_WP_Pages extends PeepSo3_Search_Adapter_WP {}

new PeepSo3_Search_Adapter_WP_Pages(
    'page',
    __('Pages', 'peepso-core')
);