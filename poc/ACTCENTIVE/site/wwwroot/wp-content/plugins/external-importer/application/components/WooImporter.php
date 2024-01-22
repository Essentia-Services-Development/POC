<?php

namespace ExternalImporter\application\components;

defined('\ABSPATH') || exit;

use \ExternalImporter\application\libs\pextractor\parser\Product;
use ExternalImporter\application\GalleryScheduler;
use ExternalImporter\application\admin\WooConfig;
use ExternalImporter\application\admin\DeeplinkConfig;
use ExternalImporter\application\admin\SyncConfig;
use ExternalImporter\application\helpers\WooHelper;
use ExternalImporter\application\helpers\TextHelper;
use ExternalImporter\application\ExternalImage;
use ExternalImporter\application\components\Dropshipping;
use ExternalImporter\application\admin\DropshippingConfig;

/**
 * WooImporter class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class WooImporter
{

    const META_PRODUCT_URL = '_ei_product_url';
    const META_PRODUCT_DOMAIN = '_ei_product_domain';
    const META_LAST_UPDATE = '_ei_last_update';
    const META_PRODUCT = '_ei_product';
    const META_PRODUCT_INFO = '_ei_product_info';

    public static function maybeInsert(Product $product, array $params = array())
    {
        if (WooConfig::getInstance()->option('avoid_duplicates') && self::isProductExistsByUrl($product->link))
            throw new \Exception(__('Product already exists.', 'external-importer'));

        return self::insert($product, $params);
    }

    public static function insert(Product $product, array $params = array())
    {
        // type
        $product_type = WooConfig::getInstance()->option('product_type');
        if ($product_type == 'external')
            $objProduct = new \WC_Product_External();
        elseif ($product_type == 'simple' && $product->variations)
            $objProduct = new \WC_Product_Variable();
        else
            $objProduct = new \WC_Product();

        // status
        $product_status = WooConfig::getInstance()->option('product_status');
        if (in_array($product_status, array('publish', 'pending', 'draft')))
            $objProduct->set_status($product_status);

        // category
        $dynamic_categories = WooConfig::getInstance()->option('dynamic_categories');
        $c_id = 0;
        if ($dynamic_categories == 'create' && $product->category)
            $c_id = WooHelper::createCategory($product->category);
        elseif ($dynamic_categories == 'nested' && $product->categoryPath)
            $c_id = WooHelper::createCategory($product->categoryPath);

        if (!$c_id)
        {
            if (!empty($params['category']))
                $c_id = (int) $params['category'];
            else
                $c_id = (int) WooConfig::getInstance()->option('default_category');
        }
        $objProduct->set_category_ids(array($c_id));

        // title
        $title = WooConfig::getInstance()->option('title_template');
        $title = WooHelper::buildTemplate($title, $product);
        $title = trim(\sanitize_text_field($title));
        if (!$title)
            $title = $product->title;

        $objProduct->set_name($title);

        // sku
        $import_sku = WooConfig::getInstance()->option('import_sku');
        $sku = '';
        if ($import_sku == 'enabled' && $product->sku)
            $sku = $product->sku;
        elseif ($import_sku == 'generate' || $import_sku == 'enabled_generate')
            $sku = self::generateSku();

        if ($sku)
            $objProduct->set_sku($sku);

        // description
        if ($product->description)
        {
            if ($truncate = (int) WooConfig::getInstance()->option('truncate_description'))
                $product->description = TextHelper::truncateHtml($product->description, $truncate);
        }

        $description = WooConfig::getInstance()->option('body_template');
        $description = WooHelper::buildTemplate($description, $product);
        $description = trim(\wp_kses_post($description));
        if (!$description)
            $description = $product->description;

        if ($description && $product->shortDescription)
        {

            $objProduct->set_short_description($product->shortDescription);
            $objProduct->set_description($description);
        } elseif ($description)
        {
            $import_description = WooConfig::getInstance()->option('import_description');
            $auto = false;
            if ($import_description == 'auto')
            {
                $size = mb_strlen($description, 'utf-8');
                if ($size < 250)
                    $auto = 'short';
                else
                    $auto = 'full';
            }

            if ($import_description == 'short' || ($auto && $auto == 'short'))
                $objProduct->set_short_description($description);
            elseif ($import_description == 'full' || ($auto && $auto == 'full'))
                $objProduct->set_description($description);
        }

        // price
        self::applayPrice($objProduct, $product, WooConfig::getInstance()->option('import_price'), WooConfig::getInstance()->option('import_old_price'));

        // stock status
        if (WooConfig::getInstance()->option('import_stock_status'))
            self::applayStockStatus($objProduct, $product);

        // url
        if ($objProduct->get_type() == 'external' && WooConfig::getInstance()->option('import_url'))
            $objProduct->set_product_url($product->link);

        // image
        if ($product->image && WooConfig::getInstance()->option('import_image') == 'enabled')
        {
            if ($media_id = WooHelper::uploadMedia($product->image, $objProduct->get_id(), $product->title))
                $objProduct->set_image_id($media_id);
        }

        // catalog visibility
        $objProduct->set_catalog_visibility(WooConfig::getInstance()->option('catalog_visibility'));

        if (!$product->inStock && SyncConfig::getInstance()->option('outofstock_product') == 'hide_product')
            $objProduct->set_catalog_visibility('hidden');

        // save
        $product_id = $objProduct->save();

        // external featured image
        ExternalImage::maybeSetExternalFeaturedImage($objProduct, $product->image);

        // attributes
        if (WooConfig::getInstance()->option('import_attributes') && $product->features)
            self::addAttributes($product->features, $product_id);

        // brand taxonomy (Rehub feature)
        if (WooHelper::isRehubTheme())
        {
            if (\apply_filters('ie_import_brand', true) && $product->manufacturer)
                \wp_set_object_terms($product_id, \sanitize_text_field($product->manufacturer), 'store', true);
            elseif (\apply_filters('ie_import_store', true) && $product->domain)
                \wp_set_object_terms($product_id, \sanitize_text_field($product->domain), 'store', true);
        }

        // reviews
        if (WooConfig::getInstance()->option('import_reviews') && $product->reviews)
        {
            if ($reviews_number = WooConfig::getInstance()->option('import_reviews_number'))
                $product->reviews = array_slice($product->reviews, 0, $reviews_number);

            self::addReviews($product->reviews, $product_id);
        }

        // tags
        if ($tags = WooConfig::getInstance()->option('import_tags'))
        {
            $tags = TextHelper::commaList($tags);
            $tags = WooHelper::buildTemplate($tags, $product);
            $tags = explode(',', $tags);
            if ($tags)
                \wp_set_object_terms($product_id, $tags, 'product_tag');
        }

        // custom fields
        if ($fields = WooConfig::getInstance()->option('custom_fields'))
        {
            foreach ($fields as $field)
            {
                $cf_name = \sanitize_text_field($field['cf_name']);

                $cf_value = \sanitize_text_field($field['cf_value']);
                $cf_value = WooHelper::fixJsonBrackets($cf_value); // becouse spin conflict
                $cf_value = WooHelper::buildTemplate($cf_value, $product);
                $cf_value = preg_replace('~^\[(.+)\]$~', '{$1}', $cf_value);
                $cf_value_decoded = json_decode($cf_value, true);
                if ($cf_value_decoded)
                    $cf_value = $cf_value_decoded;

                \update_post_meta($product_id, \wp_slash($cf_name), \wp_slash($cf_value));
            }
        }

        // gallery
        if ($product->images && WooConfig::getInstance()->option('import_gallery') == 'enabled')
        {
            $gallery_number = WooConfig::getInstance()->option('import_gallery_number');
            // do not increase the maximum limit of 10 images (becouse external gallery images)
            if (!$gallery_number || !$gallery_number > 10)
                $gallery_number = 10;

            $product->images = array_slice($product->images, 0, $gallery_number);
            GalleryScheduler::addGalleryTask($product_id, $product->images);
        }

        // external gallery
        ExternalImage::maybeSetExternalGallery($objProduct, $product->images);

        // variations
        if ($product_type == 'simple' && $product->variations)
        {
            self::createProductVariations($product_id, $product->variations, $product);
        }

        self::setAllMetaSuccess($product_id, $product);
        DeeplinkConfig::getInstance()->maybeAddDeeplinkDomainByUrl($product->link);

        return $product_id;
    }

    public static function update(Product $newProduct, $product_id)
    {
        $objProduct = \wc_get_product($product_id);
        $product = self::getProductMeta($product_id);

        // price
        if ($sync_price = SyncConfig::getInstance()->option('sync_price'))
        {
            $product->price = $newProduct->price;
            if ($sync_old_price = SyncConfig::getInstance()->option('sync_old_price'))
                $product->oldPrice = $newProduct->oldPrice;

            if ($newProduct->currencyCode)
                $product->currencyCode = $newProduct->currencyCode;

            self::applayPrice($objProduct, $product, $sync_price, $sync_old_price);

            // variations
            if ($objProduct->get_type() == 'variable')
            {
                $variation_ids = $objProduct->get_children();
                foreach ($variation_ids as $variation_id)
                {
                    $variation = \wc_get_product($variation_id);
                    if (!$variation || !$variation->exists())
                        continue;
                    self::applayPrice($variation, $product, $sync_price, $sync_old_price);
                    $variation->save();
                }
            }
        }

        // stock status
        if ($sync_stock_status = SyncConfig::getInstance()->option('sync_stock_status'))
        {
            $product->inStock = $newProduct->inStock;
            $product->availability = $newProduct->availability;
            self::applayStockStatus($objProduct, $product);

            // variations
            if ($objProduct->get_type() == 'variable')
            {
                $variation_ids = $objProduct->get_children();
                foreach ($variation_ids as $variation_id)
                {
                    $variation = \wc_get_product($variation_id);
                    if (!$variation || !$variation->exists())
                        continue;
                    self::applayStockStatus($variation, $product);
                    $variation->save();
                }
            }
        }

        // How to deal with Out of Stock products
        if (!$product->inStock)
            self::proccessOutOfStock($objProduct, $product);
        else
            $objProduct->set_catalog_visibility(WooConfig::getInstance()->option('catalog_visibility'));

        if ($product->inStock)
            self::proccessInStockStock($objProduct, $product);

        // save
        $objProduct->save();

        // update external featured image
        ExternalImage::maybeSetExternalFeaturedImage($objProduct, $newProduct->image);

        self::setAllMetaSuccess($product_id, $product);
    }

    public static function getLastInStock($product_id)
    {
        $info = WooImporter::getProductInfoMeta($product_id);
        if (!empty($info['last_in_stock']))
            return $info['last_in_stock'];
        else
            return 0;
    }

    private static function proccessInStockStock($objProduct, Product $product)
    {
        if (get_post_status($objProduct->get_id()) == 'trash')
        {
            $outofstock_product = SyncConfig::getInstance()->option('outofstock_product');
            if (substr($outofstock_product, 0, 13) == 'move_to_trash')
                \wp_untrash_post($objProduct->get_id());
        }
    }

    private static function proccessOutOfStock($objProduct, Product $product)
    {
        $outofstock_product = SyncConfig::getInstance()->option('outofstock_product');
        if ($outofstock_product == 'hide_product')
            $objProduct->set_catalog_visibility('hidden');
        elseif (substr($outofstock_product, 0, 13) == 'move_to_trash')
        {
            $days = (int) trim(str_replace('move_to_trash', '', $outofstock_product), "_");
            if (!$days || (time() - self::getLastInStock($objProduct->get_id()) >= $days * 24 * 3600))
                \wp_trash_post($objProduct->get_id());
        }
    }

    private static function applayPrice($objProduct, $product, $applay_price, $applay_old_price)
    {
        if ($objProduct->is_type('simple') || $objProduct->is_type('variation'))
            $convert = true;
        elseif ($objProduct->is_type('external') && WooConfig::getInstance()->option('currency') == 'convert')
            $convert = true;
        else
            $convert = false;

        if ($applay_price && $product->price)
        {
            $calculated_price = Dropshipping::calculatePrice($product->price, $product->domain, $objProduct);
            $objProduct->set_price(WooHelper::convertPrice($calculated_price, $product->currencyCode, $convert));

            if ($calculated_price != $product->price)
            {
                if (DropshippingConfig::getInstance()->option('old_price'))
                    $product->oldPrice = Dropshipping::calculatePrice($product->oldPrice, $product->domain, $objProduct);
                else
                    $product->oldPrice = 0;
            }

            // old price
            if ($applay_old_price)
            {
                if ($product->oldPrice)
                {
                    $objProduct->set_regular_price(WooHelper::convertPrice($product->oldPrice, $product->currencyCode, $convert));
                    $objProduct->set_sale_price(WooHelper::convertPrice($calculated_price, $product->currencyCode, $convert));
                } else
                {
                    $objProduct->set_regular_price(WooHelper::convertPrice($calculated_price, $product->currencyCode, $convert));
                    $objProduct->set_sale_price(null);
                }
            } else
                $objProduct->set_regular_price(WooHelper::convertPrice($calculated_price, $product->currencyCode, $convert));
        }
    }

    private static function applayStockStatus($objProduct, $product)
    {
        if (in_array($objProduct->get_type(), array('simple', 'variation', 'variable')))
        {
            if ($product->inStock)
                $objProduct->set_stock_status('instock');
            else
                $objProduct->set_stock_status('outofstock');
        }
    }

    public static function setAllMetaSuccess($product_id, Product $product)
    {
        self::setProductMeta($product_id, $product);
        self::setProductUrlMeta($product_id, $product->link);
        self::setLastUpdateMeta($product_id);

        $info = array();
        if ($product->inStock)
            $info['last_in_stock'] = time();
        $info['last_error'] = '';
        $info['last_error_code'] = '';
        self::addProductInfoMeta($product_id, $info);
    }

    public static function setProductMeta($product_id, Product $product)
    {
        \update_post_meta($product_id, self::META_PRODUCT, $product);
    }

    public static function getProductMeta($product_id)
    {
        return \get_post_meta($product_id, self::META_PRODUCT, true);
    }

    public static function setProductUrlMeta($product_id, $url)
    {
        \update_post_meta($product_id, self::META_PRODUCT_URL, $url);
        $domain = TextHelper::getHostName($url);
        self::setProductDomainMeta($product_id, $domain);
    }

    public static function getProductUrlMeta($product_id)
    {
        return \get_post_meta($product_id, self::META_PRODUCT_URL, true);
    }

    public static function setProductDomainMeta($product_id, $domain)
    {
        \update_post_meta($product_id, self::META_PRODUCT_DOMAIN, $domain);
    }

    public static function getProductDomainMeta($product_id)
    {
        return \get_post_meta($product_id, self::META_PRODUCT_DOMAIN, true);
    }

    public static function setLastUpdateMeta($product_id, $time = null)
    {
        if (!$time)
            $time = time();

        \update_post_meta($product_id, self::META_LAST_UPDATE, $time);
    }

    public static function getLastUpdateMeta($product_id)
    {
        return \get_post_meta($product_id, self::META_LAST_UPDATE, true);
    }

    public static function setProductInfoMeta($product_id, array $info)
    {
        \update_post_meta($product_id, self::META_PRODUCT_INFO, $info);
    }

    public static function getProductInfoMeta($product_id)
    {
        return \get_post_meta($product_id, self::META_PRODUCT_INFO, true);
    }

    public static function addProductInfoMeta($product_id, array $info)
    {
        if (!$data = self::getProductInfoMeta($product_id))
            $data = array();

        $data = array_replace($data, $info);
        self::setProductInfoMeta($product_id, $data);
    }

    public static function addReviews($reviews, $product_id)
    {
        $comment = array(
            'comment_post_ID' => $product_id,
            'comment_author_email' => '',
            'comment_author_url' => '',
            'comment_type' => 'review',
            'comment_parent' => 0,
            'user_id' => 0,
            'comment_approved' => 1,
        );

        $truncate = (int) WooConfig::getInstance()->option('truncate_reviews');
        $import_reviews_rating = WooConfig::getInstance()->option('import_reviews_rating');

        foreach ($reviews as $review)
        {
            $comment_content = TextHelper::sanitizeHtml($review['review']);
            if ($truncate)
                $comment_content = TextHelper::truncateHtml($comment_content, $truncate);

            $comment['comment_content'] = $comment_content;
            $comment['comment_author'] = $review['author'] ? \sanitize_text_field($review['author']) : '';
            $comment['comment_date'] = $review['date'] ? date('Y-m-d H:i:s', $review['date']) : null;

            if (!$comment_id = \wp_insert_comment(\wp_slash($comment)))
                continue;

            if ($import_reviews_rating && isset($review['rating']) && $review['rating'] > 0 && $review['rating'] <= 5)
                \add_comment_meta($comment_id, 'rating', $review['rating'], true);
        }

        \update_post_meta($product_id, '_wc_review_count', count($reviews));

        if (class_exists('\WC_Comments'))
            \WC_Comments::clear_transients($product_id); // Ensure product average rating and review count is kept up to date.
    }

    public static function addAttributes(array $features, $product_id, $variation = false)
    {
        if (!$product = \wc_get_product($product_id))
            return false;

        $attributes = $product->get_attributes();
        $taxonomy_count = 0;
        foreach ($features as $feature)
        {
            $f_name = \wc_clean($feature['name']);
            $f_slug = self::getSlug($f_name);

            // exists?
            if (isset($attributes[$f_slug]) || isset($attributes['pa_' . $f_slug]))
                continue;

            $f_value = \wc_sanitize_term_text_based($feature['value']);
            $f_value_array = self::value2Array($f_value);
            $term_ids = array();
            $taxonomy = '';

            //  Taxonomy exists?
            if (\taxonomy_exists(\wc_attribute_taxonomy_name($f_name)))
            {
                // Taxonomy attribute
                $taxonomy_count++;

                if ($attr_id = self::createTaxonomyAttribute($f_name, $f_slug))
                {
                    $taxonomy = \wc_attribute_taxonomy_name_by_id($attr_id);

                    // Register the taxonomy now so that the import works!
                    if (!\taxonomy_exists($taxonomy))
                    {
                        $taxonomy = TextHelper::truncate($taxonomy, 32, '');
                        $object_type = array('product');
                        if ($variation)
                            $object_type[] = 'product_variation';

                        \register_taxonomy($taxonomy, apply_filters('woocommerce_taxonomy_objects_' . $taxonomy, $object_type), apply_filters('woocommerce_taxonomy_args_' . $taxonomy, array('hierarchical' => true, 'show_ui' => false, 'query_var' => true, 'rewrite' => false)));
                    }

                    // Creates the term and taxonomy relationship if it doesn't already exist.
                    // It may be confusing but the returned array consists of term_taxonomy_ids instead of term_ids.
                    \wp_set_object_terms($product->get_id(), $f_value_array, $taxonomy);

                    $term_ids = array();
                    foreach ($f_value_array as $term)
                    {
                        if ($term_info = \term_exists($term, $taxonomy))
                            $term_ids[] = $term_info['term_id'];
                    }
                    $term_ids = array_map('intval', $term_ids);
                } else
                    $attr_id = 0;
            } else
            {
                // Local Attribute
                $attr_id = 0;
            }

            $attribute = new \WC_Product_Attribute();
            $attribute->set_id($attr_id); // 0 for product level attributes. ID for global attributes.
            if ($taxonomy)
                $attribute->set_name($taxonomy);
            else
                $attribute->set_name($f_name);

            // attribute value or array of term ids/names.
            if ($term_ids)
                $attribute->set_options($term_ids);
            else
                $attribute->set_options($f_value_array);

            $attribute->set_visible(true); // If visible on frontend.

            if ($variation)
                $attribute->set_variation(1);

            $attributes[] = $attribute;
        }
        $product->set_attributes($attributes);
        $res = $product->save();

        if ($taxonomy_count)
            \flush_rewrite_rules();
        return $res;
    }

    public static function createTaxonomyAttribute($slug, $name, $type = 'text')
    {
        global $wpdb;

        $attribute = array();
        $attribute['attribute_label'] = \wc_clean($slug);
        $attribute['attribute_name'] = \wc_sanitize_taxonomy_name($name);
        $attribute['attribute_type'] = $type;
        $attribute['attribute_orderby'] = 'menu_order';

        // validate slug
        if (strlen($attribute['attribute_name']) >= 28 || \wc_check_if_attribute_name_is_reserved($attribute['attribute_name']))
            return false;

        if (\taxonomy_exists(\wc_attribute_taxonomy_name($attribute['attribute_name'])))
            return \wc_attribute_taxonomy_id_by_name($attribute['attribute_name']);

        // Create the taxonomy
        $insert = $wpdb->insert($wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute);

        if (\is_wp_error($insert))
            return false;

        $id = $wpdb->insert_id;

        \wp_schedule_single_event(time(), 'woocommerce_flush_rewrite_rules');
        \delete_transient('wc_attribute_taxonomies');

        return $id;
    }

    public static function getSlug($name)
    {
        // already exists?
        $taxonomies = \wc_get_attribute_taxonomies();
        foreach ($taxonomies as $taxonomie)
        {
            if ($taxonomie->attribute_label == $name)
                return $taxonomie->attribute_name;
        }

        $slug = strtolower(TextHelper::sluggable($name));
        if (strlen($slug) >= 28)
            $slug = substr($slug, 0, 27);
        $slug = \wc_sanitize_taxonomy_name($slug);
        return $slug;
    }

    public static function value2Array($value)
    {
        // arrays
        $list = preg_split('/[,|;\/]\s/', $value);
        $list = array_map('trim', $list);
        $list = array_map('\wc_sanitize_term_text_based', $list);
        return $list;
    }

    public static function findWooProductByUrl($url)
    {
        global $wpdb;

        $sql = "SELECT post_id FROM {$wpdb->postmeta} AS pm JOIN {$wpdb->posts} AS p ON pm.post_id = p.ID WHERE pm.meta_key = %s AND pm.meta_value = %s AND p.post_status != 'trash'";
        $sql = $wpdb->prepare($sql, self::META_PRODUCT_URL, $url);
        $product_id = $wpdb->get_var($sql);
        return $product_id;
    }

    public static function isProductExistsByUrl($url)
    {
        if (self::findWooProductByUrl($url))
            return true;
        else
            return false;
    }

    public static function generateSku()
    {
        return strtoupper(substr(str_shuffle(md5(microtime())), 0, 8));
    }

    public static function createProductVariations($product_id, array $variations, $product)
    {
        $all_attributes = array();
        foreach ($variations as $v)
        {
            foreach ($v['attributes'] as $attribute)
            {
                if (!isset($all_attributes[$attribute['name']]))
                    $all_attributes[$attribute['name']] = array();

                $all_attributes[$attribute['name']][] = $attribute['value'];
            }
        }

        $attributes = array();
        foreach ($all_attributes as $name => $value)
        {
            $attributes[] = array(
                'name' => $name,
                'value' => join(', ', $value),
            );
        }

        self::addAttributes($attributes, $product_id, true);

        foreach ($variations as $v)
        {
            self::createProductVariation($product_id, $v, $product);
        }
    }

    public static function createProductVariation($product_id, $v, $product)
    {
        if (!$parent_product = \wc_get_product($product_id))
            return false;

        $variation_post = array(
            'post_title' => $parent_product->get_name(),
            'post_name' => 'product-' . $product_id . '-variation',
            'post_status' => 'publish',
            'post_parent' => $product_id,
            'post_type' => 'product_variation',
            'guid' => $parent_product->get_permalink()
        );

        $variation_id = \wp_insert_post($variation_post);

        foreach ($v['attributes'] as $attribute)
        {
            $taxonomy = \wc_clean($attribute['name']);
            $taxonomy = \wc_sanitize_taxonomy_name($taxonomy);
            $taxonomy = self::getSlug($taxonomy);
            $term = \wc_sanitize_term_text_based($attribute['value']);

            \update_post_meta($variation_id, 'attribute_' . $taxonomy, $term);
        }

        $variation = new \WC_Product_Variation($variation_id);

        self::applayPrice($variation, $product, WooConfig::getInstance()->option('import_price'), WooConfig::getInstance()->option('import_old_price'));

        $variation->save();
    }

}
