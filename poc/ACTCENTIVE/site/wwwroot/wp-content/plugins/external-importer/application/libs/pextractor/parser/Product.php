<?php

namespace ExternalImporter\application\libs\pextractor\parser;

defined('\ABSPATH') || exit;

/**
 * Product class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class Product {
    
    public $link;
    public $domain;
    public $title;
    public $description;
    public $shortDescription;
    public $price;
    public $currencyCode;
    public $image;
    public $oldPrice;
    public $manufacturer;
    public $inStock;
    public $availability;
    public $category;
    public $condition;
    public $ratingValue;
    public $reviewCount;
    public $sku;
    public $gtin;
    public $mpn; 
    
    /*
    public $material;
    public $gender;
    public $age_group;
    public $color;
    public $pattern;
    public $size;
     * 
     */
    
    public $features = array();
    public $images = array();
    public $reviews = array();
    public $categoryPath = array();
    public $variations = array(); 
    public $extra = array();
}
