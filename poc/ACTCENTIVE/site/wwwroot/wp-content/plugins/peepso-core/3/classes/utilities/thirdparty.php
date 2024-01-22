<?php

class PeepSo3_Third_Party {

    /** Multilingual plugins **/

    public static function has_multilingual() {
        return (self::has_multilingual_trp());
    }

    public static function has_multilingual_trp() {
        return  class_exists('TRP_Translate_Press');
    }


    /** Mobile app wrapper plugins **/

    public static function has_mobile_wrapper() {
        return (self::has_mobile_wrapper_mobiloud_canvas() || self::has_mobile_wrapper_wpma() || self::has_mobile_wrapper_appmysite());
    }

    public static function has_mobile_wrapper_mobiloud_canvas() {
        return ( defined('CANVAS_URL') && defined('CANVAS_DIR') && defined('CANVAS_PLUGIN_VERSION') );
    }

    public static function has_mobile_wrapper_wpma() {
        return ( defined('WPAPPNINJA_VERSION') && defined('WPAPPNINJA_VERSION_APP') );
    }

    public static function has_mobile_wrapper_appmysite() {
        return ( defined('AMS_PLUGIN_DIR') );
    }


    /** E-Commerce **/

    public static function has_ecommerce_dokan() {
        return ( class_exists('WeDevs_Dokan') || class_exists('Dokan_Pro') );
    }

    public static function has_ecommerce_wc_product_vendors() {
        return class_exists('WC_Product_Vendors');
    }

}