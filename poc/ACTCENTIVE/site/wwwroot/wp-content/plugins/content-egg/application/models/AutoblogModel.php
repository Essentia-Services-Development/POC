<?php

namespace ContentEgg\application\models;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\ModuleManager;
use ContentEgg\application\components\ContentManager;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\components\FeaturedImage;
use ContentEgg\application\helpers\TemplateHelper;
use ContentEgg\application\admin\GeneralConfig;

/**
 * AutoblogModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class AutoblogModel extends Model
{

    const INACTIVATE_AFTER_ERR_COUNT = 5;

    public function tableName()
    {
        return $this->getDb()->prefix . 'cegg_autoblog';
    }

    public function getDump()
    {

        return "CREATE TABLE " . $this->tableName() . " (
                    id int(11) unsigned NOT NULL auto_increment,
                    create_date datetime NOT NULL,
                    last_run datetime NOT NULL default '0000-00-00 00:00:00',
                    status tinyint(1) DEFAULT '0',
                    name varchar(200) DEFAULT NULL,
                    run_frequency int(11) NOT NULL,                    
                    keywords_per_run tinyint(3) NOT NULL,
                    post_status tinyint(1) DEFAULT '0',
                    user_id int(11) DEFAULT NULL,
                    post_count int(11) DEFAULT '0',
                    min_modules_count int(11) DEFAULT '0',
                    template_slug varchar(255) DEFAULT NULL,                    
                    template_body text,
                    template_title text,
                    keywords mediumtext,
                    include_modules text,
                    exclude_modules text,
                    required_modules text,
                    autoupdate_modules text,
                    custom_field_names text,
                    custom_field_values text,
                    tags text,
                    config text,
                    post_type varchar(100) DEFAULT NULL,
                    last_error varchar(255) DEFAULT NULL,
                    main_product varchar(30) DEFAULT NULL,
                    category int(11) DEFAULT NULL,
                    product_condition varchar(100) DEFAULT NULL,
                    PRIMARY KEY  (id),
                    KEY last_run (status,last_run,run_frequency)
                    ) $this->charset_collate;";
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'name' => __('Name', 'content-egg'),
            'create_date' => __('Date of creation', 'content-egg'),
            'last_run' => __('Last work', 'content-egg'),
            'status' => __('Status', 'content-egg'),
            'post_count' => __('Total posts', 'content-egg'),
            'last_error' => __('Last error', 'content-egg'),
            'keywords' => __('Keywords', 'content-egg'),
        );
    }

    public function save(array $item)
    {
        $item['id'] = (int) $item['id'];

        $serialized_fileds = array(
            'keywords',
            'include_modules',
            'exclude_modules',
            'required_modules',
            'autoupdate_modules',
            'custom_field_names',
            'custom_field_values',
            'config',
        );
        foreach ($serialized_fileds as $field)
        {
            if (isset($item[$field]) && is_array($item[$field]))
            {
                $item[$field] = serialize($item[$field]);
            }
        }

        if (!$item['id'])
        {
            $item['id'] = null;
            $item['create_date'] = \current_time('mysql');
            $this->getDb()->insert($this->tableName(), $item);

            return $this->getDb()->insert_id;
        }
        else
        {
            $this->getDb()->update($this->tableName(), $item, array('id' => $item['id']));

            return $item['id'];
        }
    }

    public function run($id)
    {
        $autoblog = self::model()->findByPk($id);
        if (!$autoblog)
        {
            return false;
        }

        $autoblog['include_modules'] = unserialize($autoblog['include_modules']);
        $autoblog['exclude_modules'] = unserialize($autoblog['exclude_modules']);
        $autoblog['required_modules'] = unserialize($autoblog['required_modules']);
        $autoblog['keywords'] = unserialize($autoblog['keywords']);
        $autoblog['autoupdate_modules'] = unserialize($autoblog['autoupdate_modules']);
        $autoblog['custom_field_names'] = unserialize($autoblog['custom_field_names']);
        $autoblog['custom_field_values'] = unserialize($autoblog['custom_field_values']);
        $autoblog['config'] = unserialize($autoblog['config']);

        $autoblog_save = array();
        $autoblog_save['id'] = $autoblog['id'];
        $autoblog_save['last_run'] = \current_time('mysql');

        // next keyword exists?
        $keyword_id = self::getNextKeywordId($autoblog['keywords']);
        if ($keyword_id === false)
        {
            $autoblog_save['status'] = 0;
            $this->save($autoblog_save);

            return false;
        }

        // pre save autoblog
        $this->save($autoblog_save);

        $keywords_per_run = (int) $autoblog['keywords_per_run'];
        if ($keywords_per_run < 1)
        {
            $keywords_per_run = 1;
        }

        // create posts
        for ($i = 0; $i < $keywords_per_run; $i++)
        {
            if ($i)
            {
                sleep(1);
            }

            $keyword = $autoblog['keywords'][$keyword_id];

            $post_id = null;
            try
            {
                $post_id = $this->createPost($keyword, $autoblog);
            }
            catch (\Exception $e)
            {
                $error_mess = TemplateHelper::formatDatetime(time(), 'timestamp');
                if (strlen($keyword) < 100)
                {
                    $error_mess .= ' [' . $keyword . ']';
                }
                $error_mess .= ' - ';
                $autoblog['last_error'] = $error_mess . $e->getMessage();

                \do_action('cegg_autoblog_error_handler', $autoblog, $e);
            }

            if ($post_id)
            {
                $autoblog['post_count']++;
            }
            $autoblog['keywords'][$keyword_id] = self::markKeywordInactive($keyword);
            $keyword_id = self::getNextKeywordId($autoblog['keywords']);
            if ($keyword_id === false)
            {
                $autoblog['status'] = 0;
                break;
            }
        } //.for

        $autoblog['last_run'] = \current_time('mysql');
        $this->save($autoblog);

        return true;
    }

    public function createPost($keyword, $autoblog)
    {
        $module_ids = ModuleManager::getInstance()->getParserModulesIdList(true);
        if ($autoblog['include_modules'])
        {
            $module_ids = array_intersect($module_ids, $autoblog['include_modules']);
        }
        if ($autoblog['exclude_modules'])
        {
            $module_ids = array_diff($module_ids, $autoblog['exclude_modules']);
        }

        // copy module_ids to keys
        $module_ids = array_combine($module_ids, $module_ids);

        // run required modules first
        if ($autoblog['required_modules'])
        {
            foreach ($autoblog['required_modules'] as $required_module)
            {
                // module not found?
                if (!isset($module_ids[$required_module]))
                {
                    throw new \Exception(sprintf(__('Required module %s will not run. The module is not configured or deleted.', 'content-egg'), $required_module));
                }

                unset($module_ids[$required_module]);
                $module_ids = array($required_module => $required_module) + $module_ids;
            }
        }

        // module keywords
        $keyword_arr = str_getcsv($keyword, ';');
        $keyword = '';
        $tmp_module_keywords = array();

        foreach ($keyword_arr as $k)
        {
            $k_parts = explode(':', $k, 2);
            // main keyword
            if (count($k_parts) == 1 && !$keyword)
            {
                $keyword = trim($k);
            }
            elseif (count($k_parts) == 2)
            {
                $module_id = trim($k_parts[0]);
                $module_id = str_replace(' ', '', $module_id); // name -> id
                if (!ModuleManager::getInstance()->moduleExists($module_id))
                {
                    $keyword = trim($k);
                    continue;
                }
                $tmp_module_keywords[$module_id] = trim($k_parts[1]);
            }
            else
            {
                continue;
            } //error
        }

        // main keyword not set?
        if (!$keyword)
        {
            $keyword = reset($tmp_module_keywords);
        } // first
        $module_keywords = array();
        foreach ($module_ids as $module_id)
        {
            if (isset($tmp_module_keywords[$module_id]))
            {
                $module_keywords[$module_id] = $tmp_module_keywords[$module_id];
            }
            else
            {
                $module_keywords[$module_id] = $keyword;
            }
        }
        // .module keywords

        $modules_data = array();
        $count = count($module_ids) - 1;
        foreach ($module_ids as $module_id)
        {
            $module = ModuleManager::getInstance()->factory($module_id);
            try
            {
                $data = $module->doMultipleRequests($module_keywords[$module_id], $autoblog, true);
            }
            catch (\Exception $e)
            {
                // error
                $data = null;
            }
            if ($data)
            {
                foreach ($data as $i => $d)
                {
                    $data[$i]->keyword = $module_keywords[$module_id];
                }
                $modules_data[$module->getId()] = $data;
            }
            elseif ($autoblog['required_modules'] && in_array($module_id, $autoblog['required_modules']))
            {
                throw new \Exception(sprintf(__('Data was not found for required module %s.', 'content-egg'), $module_id));
            }

            // check min count modules
            if ($autoblog['min_modules_count'])
            {
                if (count($modules_data) + $count < $autoblog['min_modules_count'])
                {
                    throw new \Exception(sprintf(__('It does not reach the desired amount of data. Minimum required modules: %d.', 'content-egg'), $autoblog['min_modules_count']));
                }
            }
            $count--;
        }

        if (!empty($autoblog['config']['min_comments_count']))
        {
            $comments_count = 0;
            foreach ($modules_data as $module_id => $data)
            {
                foreach ($data as $d)
                {
                    $comments_count += count(ContentManager::getNormalizedReviews($data));
                }
            }
            if ($comments_count < (int) $autoblog['config']['min_comments_count'])
            {
                throw new \Exception(sprintf(__('Total reviews found: %d. Minimum reviews required: %d.', 'content-egg'), $comments_count, $autoblog['config']['min_comments_count']));
            }
        }

        // main product
        $main_product = ContentManager::getMainProduct($modules_data, $autoblog['main_product']);

        // set main product for woo sync
        $product_sync = GeneralConfig::getInstance()->option('woocommerce_product_sync');
        $woocommerce_modules = GeneralConfig::getInstance()->option('woocommerce_modules');

        if ($autoblog['post_type'] == 'product' && ($product_sync == 'manually' || !array_intersect_key($modules_data, $woocommerce_modules)) && $main_product)
        {
            foreach ($modules_data[$main_product['module_id']] as $i => $product)
            {
                if ($product->unique_id == $main_product['unique_id'] && $product instanceof \ContentEgg\application\components\ContentProduct)
                {
                    $modules_data[$main_product['module_id']][$i]->woo_sync = true;
                    if (GeneralConfig::getInstance()->option('woocommerce_attributes_sync'))
                    {
                        $modules_data[$main_product['module_id']][$i]->woo_attr = true;
                    }
                    break;
                }
            }
        }

        $title = AutoblogModel::buildTemplate($autoblog['template_title'], $modules_data, $keyword, $module_keywords, $main_product);
        $title = \wp_strip_all_tags($title);
        if (!$title)
        {
            $title = $keyword;
        }
        $body = AutoblogModel::buildTemplate($autoblog['template_body'], $modules_data, $keyword, $module_keywords, $main_product);
        if (isset($autoblog['template_slug']))
            $slug = sanitize_title_with_dashes(AutoblogModel::buildTemplate($autoblog['template_slug'], $modules_data, $keyword, $module_keywords, $main_product));
        else
            $slug = '';

        if ((bool) $autoblog['post_status'])
        {
            $post_status = 'publish';
        }
        else
        {
            $post_status = 'pending';
        }

        // custom fields
        $meta_input = array();
        if ($autoblog['custom_field_names'])
        {
            foreach ($autoblog['custom_field_names'] as $i => $custom_field)
            {
                $cf_value = $autoblog['custom_field_values'][$i];
                if (\is_serialized($cf_value))
                {
                    $cf_value = @unserialize($cf_value);
                }
                else
                {
                    $cf_value = AutoblogModel::buildTemplate($cf_value, $modules_data, $keyword, $module_keywords, $main_product);
                }

                $meta_input[$custom_field] = $cf_value;
            }
        }

        //tags
        if ($autoblog['tags'])
        {
            $tags_input = AutoblogModel::buildTemplate($autoblog['tags'], $modules_data, $keyword, $module_keywords, $main_product);
        }
        else
        {
            $tags_input = '';
        }

        // create category
        if (!empty($autoblog['config']['dynamic_categories']) && $autoblog['config']['dynamic_categories'] == 2 && $main_product['categoryPath'])
        {
            if ($autoblog['post_type'] == 'product')
            {
                $categ_id = self::createWooNestedCategories($main_product['categoryPath']);
            }
            else
            {
                $categ_id = self::createNestedCategories($main_product['categoryPath']);
            }
        }
        elseif (!empty($autoblog['config']['dynamic_categories']) && $autoblog['config']['dynamic_categories'] && $main_product['category'])
        {
            if ($autoblog['post_type'] == 'product')
            {
                $categ_id = self::createWooCategory($main_product['category']);
            }
            else
            {
                $categ_id = self::createCategory($main_product['category']);
            }
        }
        else
        {
            $categ_id = $autoblog['category'];
        }

        if (!$categ_id)
        {
            $categ_id = $autoblog['category'];
        }
        $categ_id = (int) $categ_id;

        // create post
        $post = array(
            'ID' => null,
            'post_title' => $title,
            'post_content' => $body,
            'post_status' => $post_status,
            'post_author' => $autoblog['user_id'],
            'post_category' => array($categ_id),
            'post_type' => $autoblog['post_type'],
            'meta_input' => $meta_input,
            'tags_input' => $tags_input,
            'post_name' => $slug,
        );

        if (!$post_id = \wp_insert_post($post))
        {
            throw new \Exception(sprintf(__('Post can\'t be created. Unknown error.', 'content-egg'), $autoblog['min_modules_count']));
        }

        // woocommerce product
        if (\get_post_type($post_id) == 'product')
        {
            // external by default
            //\wp_set_object_terms($post_id, 'external', 'product_type');
            $classname = \WC_Product_Factory::get_product_classname($post_id, 'external');
            $product = new $classname($post_id);
            $product->save();

            if (\term_exists($categ_id, 'product_cat'))
            {
                \wp_set_object_terms($post_id, (int) $categ_id, 'product_cat');
            }

            if ($tags_input)
            {
                \wp_set_object_terms($post_id, explode(',', $tags_input), 'product_tag');
            }
        }


        // save modules data & keyword for autoupdate
        $i = 0;
        foreach ($modules_data as $module_id => $data)
        {
            $i++;
            $autoupdate_keyword = \sanitize_text_field($module_keywords[$module_id]);
            if ($i == count($modules_data))
            {
                $last_iteration = true;
            }
            else
            {
                $last_iteration = false;
            }
            ContentManager::saveData($data, $module_id, $post_id, $last_iteration);
            if (in_array($module_id, $autoblog['autoupdate_modules']) && $autoupdate_keyword)
            {
                \update_post_meta($post_id, ContentManager::META_PREFIX_KEYWORD . $module_id, $autoupdate_keyword);
            }
        }

        \do_action('cegg_autoblog_post_create', $post_id, $autoblog);

        // set featured image. external or internal
        FeaturedImage::doAction($post_id);

        return $post_id;
    }

    public static function buildTemplate($template, array $modules_data, $keyword, $module_keywords = array(), $main_product = null)
    {
        if (!$template)
        {
            return $template;
        }

        $template = TextHelper::spin($template);
        if (!preg_match_all('/%[a-zA-Z0-9_\.\,\(\)]+%/', $template, $matches))
        {
            return $template;
        }

        $replace = array();

        foreach ($matches[0] as $pattern)
        {
            // random
            if (stristr($pattern, '%RANDOM'))
            {
                preg_match('/%RANDOM\((\d+),(\d+)\)%/', $pattern, $rmatches);
                if ($rmatches)
                {
                    $replace[$pattern] = rand((int) $rmatches[1], (int) $rmatches[2]);
                }
                else
                {
                    $replace[$pattern] = rand(0, 9999999);
                }
                continue;
            }

            // keyword
            if (stristr($pattern, '%KEYWORD%'))
            {
                $replace[$pattern] = $keyword;
                continue;
            }

            // module keyword
            if (stristr($pattern, '%KEYWORD.'))
            {
                $pattern_parts = explode('.', $pattern);
                $module_id = rtrim($pattern_parts[1], '%');
                $module_id = str_replace(' ', '', $module_id); // name -> id
                if (isset($module_keywords[$module_id]))
                {
                    $replace[$pattern] = $module_keywords[$module_id];
                }
                else
                {
                    $replace[$pattern] = '';
                }
                continue;
            }

            // main product
            if (stristr($pattern, '%PRODUCT.'))
            {
                if (!$main_product)
                {
                    $replace[$pattern] = '';
                    continue;
                }

                $extra = false;
                if (strstr($pattern, '.extra.'))
                {
                    $tpattern = str_replace('.extra.', '.', $pattern);
                    $extra = true;
                }
                else
                    $tpattern = $pattern;

                $pattern_parts = explode('.', $tpattern);
                $var_name = $pattern_parts[1];
                $var_name = rtrim($var_name, '%');

                if (!$extra && isset($main_product[$var_name]))
                    $replace[$pattern] = $main_product[$var_name];
                elseif ($extra && isset($main_product['extra'][$var_name]))
                    $replace[$pattern] = $main_product['extra'][$var_name];
                elseif ($extra && isset($main_product['extra']['data'][$var_name]))
                    $replace[$pattern] = $main_product['extra']['data'][$var_name];
            }

            // module data
            if (!stristr($pattern, '%PRODUCT.'))
            {
                $extra = false;
                if (strstr($pattern, '.extra.'))
                {
                    $tpattern = str_replace('.extra.', '.', $pattern);
                    $extra = true;
                }
                else
                    $tpattern = $pattern;

                $pattern_parts = explode('.', $tpattern);

                if (count($pattern_parts) == 3 && is_numeric($pattern_parts[1]))
                {
                    $index = (int) $pattern_parts[1]; // Amazon.0.title
                    $var_name = $pattern_parts[2];
                }
                elseif (count($pattern_parts) == 2)
                {
                    $index = 0; // Amazon.title
                    $var_name = $pattern_parts[1];
                }
                else
                {
                    $replace[$pattern] = '';
                    continue;
                }
                $module_id = ltrim($pattern_parts[0], '%');
                $var_name = rtrim($var_name, '%');

                if (array_key_exists($module_id, $modules_data) && isset($modules_data[$module_id][$index]))
                {
                    if (!$extra && property_exists($modules_data[$module_id][$index], $var_name))
                        $replace[$pattern] = $modules_data[$module_id][$index]->$var_name;
                    elseif ($extra && property_exists($modules_data[$module_id][$index]->extra, $var_name))
                        $replace[$pattern] = $modules_data[$module_id][$index]->extra->$var_name;
                    elseif ($extra && isset($modules_data[$module_id][$index]->extra->data[$var_name]))
                        $replace[$pattern] = $modules_data[$module_id][$index]->extra->data[$var_name];
                }
            }

            if (!isset($replace[$pattern]))
                $replace[$pattern] = '';

            if (!is_scalar($replace[$pattern]))
                $replace[$pattern] = '';

            if ($replace[$pattern] === null)
                $replace[$pattern] = '';
        }

        return str_ireplace(array_keys($replace), array_values($replace), $template);
    }

    public static function getNextKeywordId(array $keywords)
    {
        foreach ($keywords as $id => $keyword)
        {
            if (self::isActiveKeyword($keyword))
            {
                return $id;
            }
        }

        return false;
    }

    public static function isInactiveKeyword($keyword)
    {
        if ($keyword[0] == '[')
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function isActiveKeyword($keyword)
    {
        return !self::isInactiveKeyword($keyword);
    }

    public static function markKeywordInactive($keyword)
    {
        return '[' . $keyword . ']';
    }

    public static function isActiveAutoblogs()
    {
        $total_autoblogs = AutoblogModel::model()->count('status = 1');
        if ($total_autoblogs)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function createCategory($category)
    {
        if (!is_array($category))
        {
            $category = array($category);
        }

        return self::createNestedCategories($category);
    }

    static public function createNestedCategories(array $categoryPath)
    {
        // exists?
        $last = end($categoryPath);
        if ($eid = get_cat_ID($last))
        {
            return $eid;
        }

        if (!function_exists('\wp_create_category'))
        {
            require_once(\ABSPATH . 'wp-admin/includes/taxonomy.php');
        }

        $parent = 0;
        foreach ($categoryPath as $category)
        {
            // If the category already exists, it is not duplicated.
            // The ID of the original existing category is returned without error.
            if (!$id = \wp_create_category($category, $parent))
            {
                return $id;
            }
            $parent = $id;
        }

        return $id;
    }

    public static function createWooCategory($category)
    {
        if (!is_array($category))
        {
            $category = array($category);
        }

        return self::createWooNestedCategories($category);
    }

    public static function createWooNestedCategories(array $categoryPath)
    {
        $parent = 0;
        foreach ($categoryPath as $category)
        {
            $category = \sanitize_text_field($category);
            if (!$ids = \term_exists($category, 'product_cat', $parent))
            {
                $ids = \wp_insert_term($category, 'product_cat', array('parent' => $parent));
                if (\is_wp_error($ids))
                {
                    return false;
                }
            }

            $parent = $ids['term_id'];
        }

        return $parent;
    }
}
