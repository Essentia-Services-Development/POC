<?php

namespace ExternalImporter\application\libs\pextractor\parser;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;
use ExternalImporter\application\libs\pextractor\ExtractorHelper;
use ExternalImporter\application\helpers\TextHelper;

/**
 * ProductProcessor class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class ProductProcessor {

    const MAX_TITLE_SIZE = 150;
    const MAX_LENGTH_CATEGORY_LIST = 5;

    public static $requiredFields = array('title');
    public static $recommendedFields = array('price', 'image', 'description', 'currencyCode', 'inStock');
    public static $allowedAvailability = array('Discontinued', 'InStock', 'InStoreOnly', 'LimitedAvailability', 'OnlineOnly', 'OutOfStock', 'PreOrder', 'PreSale', 'SoldOut');
    public static $outOfStockAvailability = array('Discontinued', 'InStoreOnly', 'OutOfStock', 'SoldOut');
    public static $allowedCondition = array('NewCondition', 'RefurbishedCondition', 'DamagedCondition', 'UsedCondition');
    public static $featuresStopList = array('Amazon Best Sellers Rank', 'Average Customer Review', 'Warranty', 'Item #');
    protected static $product_properties = null;

    public static function prepare(Product $product, $base_uri)
    {
        self::currencyCodePrepare($product);
        $product->title = html_entity_decode($product->title);
        $product->title = ExtractorHelper::truncate(\sanitize_text_field(html_entity_decode(\wp_encode_emoji($product->title), ENT_QUOTES | ENT_XML1, 'UTF-8')), self::MAX_TITLE_SIZE);

        $product->description = html_entity_decode($product->description);
        $product->description = html_entity_decode(\wp_encode_emoji($product->description), ENT_QUOTES | ENT_XML1, 'UTF-8');
        $product->shortDescription = html_entity_decode(\wp_encode_emoji($product->shortDescription), ENT_QUOTES | ENT_XML1, 'UTF-8');

        // danger! do not disable
        if (!\apply_filters('ei_disable_description_sanitization', false))
        {
            $product->description = TextHelper::sanitizeHtml($product->description);
            $product->shortDescription = TextHelper::sanitizeHtml($product->shortDescription);
        }
        $product->description = normalize_whitespace($product->description);
        $product->shortDescription = normalize_whitespace($product->shortDescription);

        if ($product->description)
            $product->description = TextHelper::closeTags($product->description);
        if ($product->shortDescription)
            $product->shortDescription = TextHelper::closeTags($product->shortDescription);

        $product->price = (float) ExtractorHelper::parsePriceAmount($product->price);
        $product->oldPrice = (float) ExtractorHelper::parsePriceAmount($product->oldPrice);
        if (!$product->price)
            $product->price = null;
        if (!$product->oldPrice || $product->oldPrice <= $product->price)
            $product->oldPrice = null;
        if ($product->reviewCount)
            $product->reviewCount = (int) $product->reviewCount;
        if ($product->ratingValue)
            $product->ratingValue = ExtractorHelper::ratingPrepare($product->ratingValue);
        $product->manufacturer = trim(\sanitize_text_field(html_entity_decode($product->manufacturer)));

        $product->mpn = TextHelper::clear_utf8(\sanitize_text_field($product->mpn));
        $product->gtin = TextHelper::clear_utf8(\sanitize_text_field($product->gtin));
        $product->sku = TextHelper::clear_utf8(\sanitize_text_field($product->sku));

        self::imagesPrepare($product, $base_uri);
        self::availabilityPrepare($product);
        self::conditionalPrepare($product);
        self::categoryPrepare($product);
        self::featuresPrepare($product);
        self::reviewsPrepare($product);
        self::variableAttributesPrepare($product);

        $properties = self::getProductProperties();
        foreach ($properties as $property)
        {
            if (in_array($property, array('description', 'image', 'link', 'images', 'features', 'reviews', 'shortDescription', 'variations')))
                continue;

            if (is_array($product->$property))
                array_walk_recursive($product->$property, function (&$value) {
                    $value = \sanitize_text_field($value);
                });
            else
                $product->$property = \sanitize_text_field($product->$property);
        }

        return $product;
    }

    public static function currencyCodePrepare(Product &$product)
    {
        if ($product->currencyCode && preg_match('/^[a-zA-Z]{3}$/', $product->currencyCode))
        {
            $product->currencyCode = strtoupper($product->currencyCode);
            return;
        }

        if ($code = self::extractCurrencyCode($product->currencyCode))
            $product->currencyCode = $code;
        elseif ($code = self::extractCurrencyCode($product->price))
            $product->currencyCode = $code;
        else
            $product->currencyCode = null;
    }

    public static function extractCurrencyCode($price_str)
    {
        if (!$price_str)
            return null;

        $symbols = array(
            'USD' => 'USD',
            '€' => 'EUR',
            '&euro;' => 'EUR',
            'EUR' => 'EUR',
            'Rs' => 'INR',
            '₹' => 'INR',
            '£' => 'GBP',
            'zł' => 'PLN',
            'Kč' => 'CZK',
            'грн' => 'UAH',
            'руб' => 'RUB',
            'DKK' => 'DKK',
            'CHF' => 'CHF',
            'C$' => 'CAD',
            'R$' => 'BRL',
            '$' => 'USD',
            'kr' => 'SEK',
            'ريـال' => 'SAR',
            'QAR' => 'QAR',
            '₴' => 'UAH',
        );

        foreach ($symbols as $symbol => $code)
        {
            if (strstr($price_str, $symbol))
                return $code;
        }

        return false;
    }

    public static function imagesPrepare(Product &$product, $base_uri)
    {
        if (!$product->images)
            $product->images = array();

        if ($product->image && is_array($product->image))
        {
            if (!$product->images)
                $product->images = $product->image;
            $product->image = reset($product->image);
        }

        $key = array_search($product->image, $product->images);
        if ($key !== false)
        {
            unset($product->images[$key]);
            $product->images = array_values($product->images);
        }

        $product->image = ExtractorHelper::encodeNonAscii($product->image);
        $product->image = ExtractorHelper::resolveUrl(filter_var($product->image, FILTER_SANITIZE_URL), $base_uri);
        foreach ($product->images as $i => $image)
        {
            $image = ExtractorHelper::encodeNonAscii($image);
            $product->images[$i] = ExtractorHelper::resolveUrl(filter_var($image, FILTER_SANITIZE_URL), $base_uri);
        }

        $product->images = array_unique($product->images);
        $product->images = array_filter($product->images);
        $product->images = array_values($product->images);
        $product->images = array_slice($product->images, 0, 9); //external limit
    }

    public static function availabilityPrepare(Product &$product)
    {
        if (!$product->inStock && $product->inStock !== null)
        {
            $product->availability = 'OutOfStock';
            return;
        }

        if ($product->inStock === null)
            $product->inStock = true;

        if (!$product->availability)
            return;

        $product->availability = preg_replace('/https?:\/\/schema\.org\//', '', $product->availability);

        // fix letter case
        $allowed_availability = array_combine(array_map('strtolower', self::$allowedAvailability), self::$allowedAvailability);
        if (!array_key_exists(strtolower($product->availability), $allowed_availability))
        {
            $product->availability = null;
            return;
        } else
            $product->availability = $allowed_availability[strtolower($product->availability)];

        if (in_array($product->availability, self::$outOfStockAvailability))
            $product->inStock = false;
    }

    public static function conditionalPrepare(Product &$product)
    {
        $product->condition = preg_replace('/https?:\/\/schema\.org\//', '', $product->condition);

        // fix letter case
        $allowed_condition = array_combine(array_map('strtolower', self::$allowedCondition), self::$allowedCondition);

        if (!array_key_exists(strtolower($product->condition), self::$allowedCondition))
        {
            $product->condition = null;
            return;
        } else
            $product->condition = $allowed_condition[strtolower($product->condition)];
    }

    public static function categoryPrepare(Product &$product)
    {
        if (!$product->categoryPath)
            $product->categoryPath = array();

        if (!is_array($product->categoryPath))
            $product->categoryPath = array($product->categoryPath);

        if (strstr($product->category, ' < ') && !$product->categoryPath)
            $product->categoryPath = explode(' < ', $product->category);

        $categs = array();
        foreach ($product->categoryPath as $c)
        {
            if ($product->title && $c == $product->title)
                continue;

            $c = html_entity_decode($c);
            $c = trim($c, ' \'".');
            $c = ucfirst($c);

            if (in_array($c, array('Home', 'Products', 'Startseite', 'Homepage', 'Главная', 'Каталог', 'All categories')))
                continue;

            if (!$c || mb_strlen($c, 'utf-8') > 50)
                continue;

            $categs[] = $c;
        }
        $product->categoryPath = array_unique($categs);

        if ($product->categoryPath)
            $product->category = reset($product->categoryPath);

        $product->category = \sanitize_text_field(trim(html_entity_decode($product->category), ' \'".'));

        if (!$product->categoryPath && $product->category)
            $product->categoryPath = array($product->category);

        $product->categoryPath = array_slice($product->categoryPath, 0, self::MAX_LENGTH_CATEGORY_LIST);
    }

    public static function featuresPrepare(Product &$product)
    {
        if (!$product->features || !is_array($product->features))
            $product->features = array();

        foreach ($product->features as $i => $feature)
        {
            if (!$name_value = self::featurePrepare($feature))
            {
                unset($product->features[$i]);
                continue;
            }

            $product->features[$i]['name'] = $name_value[0];
            $product->features[$i]['value'] = $name_value[1];
        }

        $product->features = array_values($product->features);
    }

    public static function variableAttributesPrepare(Product &$product)
    {
        
        if (!$product->variations || !is_array($product->variations))
            $product->variations = array();

        foreach ($product->variations as $i => $variation)
        {

            if (!$variation->attributes || !is_array($variation->attributes))
            {
                $variation->attributes = array();
                $product->variations[$i]->attributes = array();
            }

            foreach ($variation->attributes as $j => $attribute)
            {

                if (!$name_value = self::featurePrepare($attribute))
                {
                    unset($variation->attributes[$j]);
                    continue;
                }

                $product->variations[$i]->attributes[$j]['name'] = $name_value[0];
                $product->variations[$i]->attributes[$j]['value'] = $name_value[1];
            }

            $product->variations[$i]->attributes = array_values($product->variations[$i]->attributes);
        }
                
    }

    public static function featurePrepare(array $feature)
    {
        if (empty($feature['name']) || empty($feature['value']))
            return false;

        $name = ExtractorHelper::clearFeature(\sanitize_text_field($feature['name']));
        $name = TextHelper::clear_utf8($name);
        $name = trim($name, "-: ");
        $value = ExtractorHelper::clearFeature(\sanitize_text_field($feature['value']));
        $value = trim($value, ",; ");

        if (!$name || !$value || mb_strlen($name, 'utf-8') < 2 || in_array($name, self::$featuresStopList))
        {
            return false;
        }

        return array($name, $value);
    }

    public static function reviewsPrepare(Product &$product)
    {
        if (!$product->reviews || !is_array($product->reviews))
            $product->reviews = array();

        foreach ($product->reviews as $i => $review)
        {
            if (empty($review['review']))
            {
                unset($product->reviews[$i]);
                continue;
            }

            $product->reviews[$i]['review'] = \normalize_whitespace(TextHelper::sanitizeHtml(html_entity_decode($review['review'], ENT_QUOTES | ENT_XML1, 'UTF-8')));
            if (!$review['review'])
            {
                unset($product->reviews[$i]);
                continue;
            }

            if (empty($product->reviews[$i]['author']))
                $product->reviews[$i]['author'] = '';
            if (empty($product->reviews[$i]['date']))
                $product->reviews[$i]['date'] = '';
            if (empty($product->reviews[$i]['rating']))
                $product->reviews[$i]['rating'] = '';

            $product->reviews[$i]['review'] = TextHelper::closeTags($product->reviews[$i]['review']);
            $product->reviews[$i]['author'] = isset($product->reviews[$i]['author']) ? \sanitize_text_field(html_entity_decode($review['author'])) : '';
            $product->reviews[$i]['author'] = trim($product->reviews[$i]['author'], ",");
            $product->reviews[$i]['date'] = !empty($product->reviews[$i]['date']) ? (int) $review['date'] : '';
            $product->reviews[$i]['rating'] = ExtractorHelper::ratingPrepare($product->reviews[$i]['rating']);

            if (!$product->reviews[$i]['date'] || $product->reviews[$i]['date'] > time())
                $product->reviews[$i]['date'] = time() - rand(3600, 3600 * 24 * 90);
        }

        $product->reviews = array_values($product->reviews);
    }

    public static function getProductProperties()
    {
        if (self::$product_properties === null)
        {
            $reflect = new \ReflectionClass('\\ExternalImporter\\application\\libs\\pextractor\\parser\Product');
            $props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);
            $results = array();
            foreach ($props as $prop)
            {
                $results[] = $prop->getName();
            }
            self::$product_properties = $results;
        }
        return self::$product_properties;
    }

    public static function isPropertyExists($property)
    {
        $properties = self::getProductProperties();
        if (in_array($property, $properties))
            return true;
        else
            return false;
    }

    public static function isFieldsFilled(Product $product, array $fields)
    {
        foreach ($fields as $field)
        {
            if ($product->$field === '' || $product->$field === null || (is_array($product->$field) && !$product->$field))
                return false;
        }
        return true;
    }

    public static function isRequaredFieldsFilled(Product $product)
    {
        return self::isFieldsFilled($product, self::$requiredFields);
    }

    public static function isRecommendedFieldsFilled(Product $product)
    {
        return self::isFieldsFilled($product, self::$recommendedFields);
    }

    public static function mergeProducts(Product $product1, Product $product2)
    {
        foreach ($product1 as $key => $value)
        {
            if ($value || $value === false)
                continue;

            if ($product2->$key || $product2->$key === false)
                $product1->$key = $product2->$key;
        }
        return $product1;
    }

    public static function productFactory(array $properties = array())
    {
        $product = new Product;
        foreach ($product as $key => $value)
        {
            if (isset($properties[$key]))
                $product->$key = $properties[$key];
        }
        return $product;
    }

}
