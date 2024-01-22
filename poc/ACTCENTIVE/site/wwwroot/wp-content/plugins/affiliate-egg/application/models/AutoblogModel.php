<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AutoblogModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class AutoblogModel extends Model {

    const INACTIVATE_AFTER_ERR_COUNT = 6;

    public function tableName()
    {
        return $this->getDb()->prefix . 'affegg_autoblog';
    }

    public function getDump()
    {

        return "CREATE TABLE " . $this->tableName() . " (
                    id int(11) unsigned NOT NULL auto_increment,
                    create_date datetime NOT NULL,
                    last_check datetime NOT NULL default '0000-00-00 00:00:00',
                    status tinyint(1) DEFAULT '0',
                    url text,
                    name varchar(200) DEFAULT NULL,                    
                    check_frequency int(11) NOT NULL,
                    items_per_check tinyint(3) NOT NULL,
                    items_per_post tinyint(3) NOT NULL,
                    post_status tinyint(1) DEFAULT '0',
                    user_id int(11) DEFAULT NULL,
                    post_count int(11) DEFAULT '0',
                    template varchar(255) NOT NULL,
                    title_tpl text,
                    category int(11) DEFAULT NULL,
                    row_err_count smallint(5) DEFAULT '0',
                    last_error varchar(255) DEFAULT NULL,
                    PRIMARY KEY  (id),
                    KEY last_check (status,last_check,check_frequency)
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
            'name' => __('Name', 'affegg'),
            'create_date' => __('Date created', 'affegg'),
            'last_check' => __('Last run', 'affegg'),
            'status' => __('Status', 'affegg'),
            'post_count' => __('Total posts', 'affegg'),
        );
    }

    public function validate(array $item)
    {
        if (!$item['url'])
        {
            return false;
        }

        if (!in_array($item['template'], array_keys(TemplateManager::getInstance()->getEggTemplatesList())))
            return false;

        if (!empty($item['id']) && !empty($item['url']))
            return true;

        try
        {
            ParserManager::getInstance()->getParserByUrl($item['url']);
        } catch (\Exception $e)
        {
            return false;
        }

        return true;
    }

    public function save(array $item)
    {
        $item['id'] = (int) $item['id'];
        if (!$item['id'])
        {
            $item['id'] = null;
            $item['create_date'] = current_time('mysql');
            $this->getDb()->insert($this->tableName(), $item);
            return $this->getDb()->insert_id;
        } else
        {
            $this->getDb()->update($this->tableName(), $item, array('id' => $item['id']));
            return $item['id'];
        }
    }

    public function delete($id)
    {
        if (parent::delete($id))
        {
            $items = AutoblogItemModel::model()->findAll(array('select' => 'id', 'where' => array('autoblog_id = %d', array($id))));
            foreach ($items as $item)
            {
                AutoblogItemModel::model()->delete($item['id']);
            }
        }
    }

    public function parseAndPost($id)
    {
        $autoblog = self::model()->findByPk($id);
        if (!$autoblog)
            return false;

        $autoblog_save = array();
        $autoblog_save['id'] = $autoblog['id'];
        $autoblog_save['last_check'] = current_time('mysql');
        $this->save($autoblog_save);

        // 1. Parse catalog
        $error = '';
        try
        {
            $product_urls = ParserManager::getInstance()->parseCatalog($autoblog['url'], $autoblog['items_per_check']);
        } catch (\Exception $e)
        {
            $error = $e->getMessage();
        }
        if (!$error && !$product_urls)
            $error = 'Products not found.';
        if ($error)
        {
            $autoblog['last_check'] = current_time('mysql');
            $autoblog['row_err_count']++;
            if ($autoblog['row_err_count'] >= AutoblogModel::INACTIVATE_AFTER_ERR_COUNT)
                $autoblog['status'] = 0;
            $autoblog['last_error'] = $error;
            $this->save($autoblog);
            return false;
        }
        // 2. Check duplicates
        foreach ($product_urls as $key => $product_url)
        {
            $count = AutoblogItemModel::model()->count('url_hash="' . AutoblogItemModel::createUrlHash($product_url) . '" AND autoblog_id=' . $autoblog['id']);
            if ($count)
                unset($product_urls[$key]);
        }
        if (!$product_urls)
            return;
        $product_urls = array_values($product_urls); // reindex

        foreach ($product_urls as $url)
        {
            $autoblog_item = array();
            $autoblog_item['autoblog_id'] = $autoblog['id'];
            $autoblog_item['url_hash'] = AutoblogItemModel::createUrlHash($url);
            AutoblogItemModel::model()->save($autoblog_item);
        }

        // 3. Create eggs
        $new_egg_ids = array();
        for ($i = 0; $i < count($product_urls); $i = $i + $autoblog['items_per_post'])
        {
            $item = array();
            $item['id'] = null;
            $item['update_date'] = current_time('mysql');
            if ($autoblog['name'])
                $item['name'] = __('Autoblogging', 'affegg') . ' - ' . $autoblog['name'];
            else
                $item['name'] = __('Autoblogging', 'affegg') . ' - ID#: ' . $autoblog['id'];
            $item['prod_limit'] = EggManager::TOTAL_PRODUCT_LIMIT;
            $item['template'] = $autoblog['template'];
            $item['user_id'] = $autoblog['user_id'];
            $item['id'] = EggModel::model()->save($item);
            if ($item['id'])
            {
                $urls = array_slice($product_urls, $i, $autoblog['items_per_post']);
                EggManager::getInstance()->updateUrls($urls, $item['id'], $item['prod_limit']);
            } else
                continue;
            $new_egg_ids[] = $item['id'];
        }

        // 4. Create posts
        foreach ($new_egg_ids as $egg_id)
        {
            $egg_products = ProductModel::model()->getEggProducts($egg_id, 1);
            if (!$egg_products)
                continue;

            $ga_label = 'affegg-' . $egg_id;
            $egg_products = Shortcode::getInstance()->prepareItems($egg_products, $ga_label);
            $title_replace = array(
                '%PRODUCT.TITLE%' => $egg_products[0]['title'],
                '%PRODUCT.PRICE%' => $egg_products[0]['price'],
                '%PRODUCT.OLD_PRICE%' => $egg_products[0]['old_price'],
                '%PRODUCT.CURRENCY%' => $egg_products[0]['currency'],
                '%PRODUCT.MANUFACTURER%' => $egg_products[0]['manufacturer'],
            );
            $title_tpl = TextHelper::spin($autoblog['title_tpl']);
            $post_title = str_replace(array_keys($title_replace), array_values($title_replace), $title_tpl);

            if ($autoblog['post_status'])
                $post_status = 'publish';
            else
                $post_status = 'draft';

            $post = array(
                'ID' => null,
                'post_title' => $post_title,
                'post_content' => '[affegg id=' . $egg_id . ']',
                'post_status' => $post_status,
                'post_author' => $autoblog['user_id'],
                'post_category' => array($autoblog['category']),
            );

            $post_id = \wp_insert_post($post);
            do_action('affegg_autoblog_create_post', $post_id, $egg_products);
            $autoblog['post_count']++;
        }

        $autoblog['last_check'] = current_time('mysql');
        $autoblog['row_err_count'] = 0;
        $autoblog['last_error'] = '';
        $this->save($autoblog);

        return true;
    }

}
