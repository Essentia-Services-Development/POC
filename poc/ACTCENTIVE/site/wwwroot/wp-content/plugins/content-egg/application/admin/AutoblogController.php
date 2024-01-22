<?php

namespace ContentEgg\application\admin;

defined('\ABSPATH') || exit;

use ContentEgg\application\Plugin;
use ContentEgg\application\models\AutoblogModel;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\AutoblogScheduler;

/**
 * AutoblogController class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class AutoblogController
{

    const slug = 'content-egg-autoblog';

    private $amazon_categs = array(
        'appliances' => 'Appliances',
        'mobile-apps' => 'Appstore for Android',
        'arts-crafts' => 'Arts, Crafts & Sewing',
        'automotive' => 'Automotive',
        'baby-products' => 'Baby',
        'beauty' => 'Beauty',
        'books' => 'Books',
        'photo' => 'Camera & Photo',
        'wireless' => 'Cell Phones & Accessories',
        'apparel' => 'Clothing',
        'pc' => 'Computers & Accessories',
        'electronics' => 'Electronics',
        'gift-cards' => 'Gift Cards Store',
        'grocery' => 'Grocery & Gourmet Food',
        'hpc' => 'Health & Personal Care',
        'home-garden' => 'Home & Kitchen',
        'hi' => 'Home Improvement',
        'industrial' => 'Industrial & Scientific',
        'jewelry' => 'Jewelry',
        'digital-text' => 'Kindle Store',
        'kitchen' => 'Kitchen & Dining',
        'dmusic' => 'MP3 Downloads',
        'magazines' => 'Magazines',
        'movies-tv' => 'Movies & TV',
        'music' => 'Music',
        'musical-instruments' => 'Musical Instruments',
        'office-products' => 'Office Products',
        'lawn-garden' => 'Patio, Lawn & Garden',
        'pet-supplies' => 'Pet Supplies',
        'shoes' => 'Shoes',
        'software' => 'Software',
        'sporting-goods' => 'Sports & Outdoors',
        'toys-and-games' => 'Toys & Games',
        'videogames' => 'Video Games',
        'watches' => 'Watches',
    );

    public function __construct()
    {
        \add_action('admin_menu', array($this, 'add_admin_menu'));

        if ($GLOBALS['pagenow'] == 'admin.php' && !empty($_GET['page']) && $_GET['page'] == 'content-egg-autoblog-edit')
        {
            \wp_enqueue_script('contentegg-keywords', \ContentEgg\PLUGIN_RES . '/js/keywords.js', array('jquery'), '' . Plugin::version());
            \wp_enqueue_script('jquery-ui-tabs');
            \wp_enqueue_script('jquery-ui-button');
            \wp_enqueue_style('contentegg-admin-ui-css', \ContentEgg\PLUGIN_RES . '/css/jquery-ui.min.css', false, Plugin::version());
        }
    }

    public function add_admin_menu()
    {
        \add_submenu_page(Plugin::slug, __('Autoblogging', 'content-egg') . ' &lsaquo; Content Egg', __('Autoblogging', 'content-egg'), 'manage_options', self::slug, array($this, 'actionIndex'));
        \add_submenu_page(Plugin::slug, __('Add autoblogging', 'content-egg') . ' &lsaquo; Content Egg', __('Add autoblogging', 'content-egg'), 'manage_options', 'content-egg-autoblog-edit', array($this, 'actionUpdate'));
        \add_submenu_page('options.php', __('Add autoblogging - bulk mode', 'content-egg') . ' &lsaquo; Content Egg', __('Add autoblogging - bulk mode', 'content-egg'), 'manage_options', 'content-egg-autoblog-edit--batch', array($this, 'actionUpdate'));
    }

    public function actionIndex()
    {
        if (!empty($_GET['action']) && $_GET['action'] == 'run' && !empty($_GET['id']))
        {
            if (!isset($_GET['_wpnonce']) || !\wp_verify_nonce(sanitize_key($_GET['_wpnonce']), 'cegg_autoblog_run'))
                die('Invalid nonce');

            @set_time_limit(180);
            AutoblogModel::model()->run((int) $_GET['id']);
        }
        \wp_enqueue_script('content-egg-blockUI', \ContentEgg\PLUGIN_RES . '/js/jquery.blockUI.js', array('jquery'));
        $table = new AutoblogTable(AutoblogModel::model());
        $table->prepare_items();
        PluginAdmin::getInstance()->render('autoblog_index', array('table' => $table));
    }

    public function actionUpdate()
    {
        if ($GLOBALS['pagenow'] == 'admin.php' && !empty($_GET['page']) && $_GET['page'] == 'content-egg-autoblog-edit--batch')
            $batch = true;
        else
            $batch = false;

        $default = array(
            'id' => 0,
            'name' => '',
            'status' => 1,
            'run_frequency' => 86400,
            'keywords_per_run' => 1,
            'post_status' => 1,
            'user_id' => \get_current_user_id(),
            'template_body' => '',
            'template_title' => '%KEYWORD%',
            'template_slug' => '',
            'keywords' => array(),
            'category' => \get_option('default_category'),
            'include_modules' => array(),
            'exclude_modules' => array(),
            'required_modules' => array(),
            'autoupdate_modules' => array(),
            'min_modules_count' => 1,
            'post_type' => 'post',
            'custom_field_names' => array_fill(0, 8, ''),
            'custom_field_values' => array_fill(0, 8, ''),
            'main_product' => 'min_price',
            'tags' => '',
            'condition' => '',
            'config' => array('dynamic_categories' => 0, 'min_comments_count' => 0),
        );

        $message = '';
        $notice = '';

        if (!empty($_POST['nonce']) && \wp_verify_nonce(sanitize_key($_POST['nonce']), basename(__FILE__)) && !empty($_POST['item']))
        {
            $pitem = isset($_POST['item']) ? wp_unslash($_POST['item']) : array(); // phpcs:ignore
            $item = array();
            $item['id'] = isset($pitem['id']) ? absint($pitem['id']) : 0;
            $item['name'] = isset($pitem['name']) ? \sanitize_text_field($pitem['name']) : '';
            $item['status'] = isset($pitem['status']) ? absint($pitem['status']) : '';
            $item['keywords_per_run'] = isset($pitem['keywords_per_run']) ? absint($pitem['keywords_per_run']) : 1;
            $item['run_frequency'] = isset($pitem['run_frequency']) ? absint($pitem['run_frequency']) : '';
            $item['post_status'] = isset($pitem['post_status']) ? absint($pitem['post_status']) : '';
            $item['user_id'] = isset($pitem['user_id']) ? absint($pitem['user_id']) : '';
            $item['template_body'] = isset($pitem['template_body']) ? \wp_kses_post($pitem['template_body']) : '';
            $item['template_title'] = isset($pitem['template_title']) ? trim(\sanitize_text_field($pitem['template_title'])) : '';
            $item['template_slug'] = isset($pitem['template_slug']) ? trim(\sanitize_text_field($pitem['template_slug'])) : '';
            $item['post_type'] = isset($pitem['post_type']) ? sanitize_key($pitem['post_type']) : null;
            $item['category'] = isset($pitem['category']) ? intval($pitem['category']) : null;
            $item['include_modules'] = isset($pitem['include_modules']) ? array_map('sanitize_text_field', $pitem['include_modules']) : array();
            $item['exclude_modules'] = isset($pitem['exclude_modules']) ? array_map('sanitize_text_field', $pitem['exclude_modules']) : array();
            $item['required_modules'] = isset($pitem['required_modules']) ? array_map('sanitize_text_field', $pitem['required_modules']) : array();
            $item['autoupdate_modules'] = isset($pitem['autoupdate_modules']) ? array_map('sanitize_text_field', $pitem['autoupdate_modules']) : array();
            $item['min_modules_count'] = isset($pitem['min_modules_count']) ? absint($pitem['min_modules_count']) : '';
            $item['keywords'] = isset($pitem['keywords']) ? array_map('sanitize_text_field', explode("\r\n", $pitem['keywords'])) : null;
            $item['custom_field_names'] = isset($pitem['custom_field_names']) ? array_map('sanitize_key', $pitem['custom_field_names']) : array();
            $item['custom_field_values'] = isset($pitem['custom_field_values']) ? array_map('sanitize_text_field', $pitem['custom_field_values']) : array();
            $item['main_product'] = isset($pitem['main_product']) ? sanitize_key($pitem['main_product']) : 'min_price';
            $item['tags'] = isset($pitem['tags']) ? sanitize_text_field(TextHelper::commaList($pitem['tags'])) : '';
            $item['config'] = isset($pitem['config']) ? $pitem['config'] : '';
            $item['product_condition'] = isset($pitem['product_condition']) ? sanitize_text_field($pitem['product_condition']) : '';

            $redirect_url = \get_admin_url(\get_current_blog_id(), 'admin.php?page=content-egg-autoblog');
            if ($batch)
            {
                $created_count = $this->createBatchAutoblog($item);
                if ($created_count === false)
                    $redirect_url = AdminNotice::add2Url($redirect_url, 'autoblog_csv_file_error', 'error');
                elseif (!$created_count)
                    $redirect_url = AdminNotice::add2Url($redirect_url, 'autoblog_create_error', 'error');
                else
                    $redirect_url = AdminNotice::add2Url($redirect_url, 'autoblog_batch_created', 'success', $created_count);
            } else
            {
                $item['id'] = $this->createAutoblog($item);

                if ($item['id'])
                    $redirect_url = AdminNotice::add2Url($redirect_url, 'autoblog_saved', 'success', $item['id']);
                else
                    $redirect_url = AdminNotice::add2Url($redirect_url, 'autoblog_create_error', 'error');
            }

            \wp_safe_redirect($redirect_url);
            exit;
        } else
        {
            // view page
            if (isset($_GET['duplicate_id']))
            {
                if (!isset($_GET['_wpnonce']) || !\wp_verify_nonce(sanitize_key($_GET['_wpnonce']), 'cegg_autoblog_duplicate'))
                    die('Invalid nonce');

                $duplicate = AutoblogModel::model()->findByPk((int) $_GET['duplicate_id']);
                if ($duplicate)
                {
                    foreach ($default as $key => $val)
                    {
                        if (!isset($duplicate[$key]))
                            continue;
                        $item[$key] = $duplicate[$key];
                        if (is_array($val))
                            $item[$key] = unserialize($item[$key]);
                    }
                    $item['id'] = null;
                } else
                    $item = $default;
            } else
                $item = $default;

            if (isset($_GET['id']))
            {
                $item = AutoblogModel::model()->findByPk((int) $_GET['id']);
                if (!$item)
                {
                    $item = $default;
                    $notice = __('Autoblogging is not found', 'content-egg');
                } else
                {
                    $item['keywords'] = unserialize($item['keywords']);
                    $item['include_modules'] = unserialize($item['include_modules']);
                    $item['exclude_modules'] = unserialize($item['exclude_modules']);
                    $item['required_modules'] = unserialize($item['required_modules']);
                    $item['autoupdate_modules'] = unserialize($item['autoupdate_modules']);
                    $item['custom_field_names'] = unserialize($item['custom_field_names']);
                    $item['custom_field_values'] = unserialize($item['custom_field_values']);
                    $item['config'] = unserialize($item['config']);
                }
            }
        }
        $item['keywords'] = join("\n", $item['keywords']);

        \add_meta_box('autoblog_metabox', 'Autoblog data', array($this, 'metaboxAutoblogCreateHandler'), 'autoblog_create', 'normal', 'default');

        $item['amazon_categs'] = $this->amazon_categs;

        PluginAdmin::getInstance()->render('autoblog_edit', array(
            'item' => $item,
            'notice' => $notice,
            'message' => $message,
            'nonce' => \wp_create_nonce(basename(__FILE__)),
            'batch' => $batch
        ));
    }

    private function createAutoblog($item)
    {
        $item['keywords'] = TextHelper::prepareKeywords($item['keywords']);
        $item['id'] = AutoblogModel::model()->save($item);

        if ($item['status'])
        {
            AutoblogScheduler::addScheduleEvent('hourly', time() + 900);
        }

        return $item['id'];
    }

    private function createBatchAutoblog($item)
    {
        @set_time_limit(180);

        if (empty($_FILES['item']['name']) || empty($_FILES['item']['name']['keywords_file']))
            return false;

        $file_name = sanitize_text_field(wp_unslash($_FILES['item']['name']['keywords_file']));

        $supported_types = array('text/csv', 'text/plain');
        $arr_file_type = \wp_check_filetype(basename($file_name));
        $uploaded_type = $arr_file_type['type'];

        if (!in_array($uploaded_type, $supported_types))
            return false;

        $handle = fopen($_FILES['item']['tmp_name']['keywords_file'], "r");
        if (!$handle)
            return false;

        $separator = ';';

        $i = 0;
        $keywords = array();
        $category_keywords = array();
        while (($data = fgetcsv($handle, 1000, $separator)) !== false)
        {
            $num = count($data);

            if ($i == 0)
            {
                if (substr($data[0], 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf))
                    $data[0] = substr($data[0], 3);

                if ($num == 1 && $item['category'] == -1)
                {
                    $item['category'] = \get_option('default_category');
                }
            }

            $data[0] = trim($data[0]);
            if (!$data[0])
                continue;

            if ($num == 1)
                $keywords[] = trim($data[0]);
            elseif ($num >= 2)
                $category_keywords[trim($data[0])][] = trim($data[1]);
            $i++;
        }
        fclose($handle);

        // create
        if ($keywords)
        {
            $item['keywords'] = $keywords;
            $id = $this->createAutoblog($item);
            if ($id)
                return 1; //1 count
            else
                return false;
        }

        // create by categ
        $created_count = 0;
        if ($category_keywords)
        {
            foreach ($category_keywords as $c_name => $keywords)
            {
                $c_name = \sanitize_text_field($c_name);
                $new_item = $item;

                if ($item['category'] == -1)
                {
                    $c_id = \wp_create_category($c_name);
                    if (!$c_id)
                        continue;

                    $new_item['category'] = $c_id;
                }
                if ($new_item['name'])
                    $new_item['name'] .= ' - ';
                $new_item['name'] .= $c_name;

                $new_item['keywords'] = $keywords;
                $a_id = $this->createAutoblog($new_item);
                if ($a_id)
                    $created_count++;
            }
        }
        return $created_count;
    }

    public function metaboxAutoblogCreateHandler($item)
    {
        if (!isset($item['batch']))
            $batch = false;
        else
        {
            $batch = (bool) $item['batch'];
            unset($item['batch']);
        }
        PluginAdmin::getInstance()->render('_metabox_autoblog', array('item' => $item, 'batch' => $batch));
    }

    private static function createTable()
    {
        $models = array('AutoblogModel');
        $sql = '';
        foreach ($models as $model)
        {
            $m = "\\ContentEgg\\application\\models\\" . $model;
            $sql .= $m::model()->getDump();
            $sql .= "\r\n";
        }
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);
    }

}
