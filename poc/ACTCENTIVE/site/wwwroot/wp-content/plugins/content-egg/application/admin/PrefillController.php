<?php

namespace ContentEgg\application\admin;

defined('\ABSPATH') || exit;

use ContentEgg\application\Plugin;
use ContentEgg\application\components\ModuleManager;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\components\ContentManager;
use ContentEgg\application\libs\KeywordDensity;
use ContentEgg\application\models\AutoblogModel;

/**
 * PrefillController class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class PrefillController {

    const slug = 'content-egg-prefill';

    public function __construct()
    {
        \add_action('admin_menu', array($this, 'add_admin_menu'));
        \add_action('wp_ajax_' . self::slug, array($this, 'addApiEntry'));

        if ($GLOBALS['pagenow'] == 'admin.php' && !empty($_GET['page']) && $_GET['page'] == self::slug)
        {
            \wp_enqueue_script('contentegg-prefill', \ContentEgg\PLUGIN_RES . '/js/prefill.js', array('jquery'));
            \wp_enqueue_script('jquery-ui-progressbar', array('jquery-ui-core'));
            \wp_enqueue_style('contentegg-admin-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/themes/smoothness/jquery-ui.css', false, Plugin::version, false);

            $post_statuses = array_merge(get_post_statuses(), array('future' => __('Future')));
            $post_ids = \get_posts(array(
                'post_type' => GeneralConfig::getInstance()->option('post_types'),
                'numberposts' => -1,
                'post_status' => array_keys($post_statuses),
                'fields' => 'ids',
            ));

            $posts = array();
            foreach ($post_ids as $post_id)
            {
                $post = array();
                $post['id'] = $post_id;
                $post['post_type'] = \get_post_field('post_type', $post_id);
                $post['post_status'] = \get_post_field('post_status', $post_id);
                $posts[] = $post;
            }

            \wp_localize_script('contentegg-prefill', 'content_egg_prefill', array(
                'posts' => $posts,
                'nonce' => \wp_create_nonce('contentegg-prefill')
            ));
        }
    }

    public function add_admin_menu()
    {
        \add_submenu_page(Plugin::slug, __('Fill', 'content-egg') . ' &lsaquo; Content Egg', __('Fill', 'content-egg'), 'publish_posts', self::slug, array($this, 'actionIndex'));
    }

    public function actionIndex()
    {
        PluginAdmin::getInstance()->render('prefill', array(
                //'nonce' => \wp_create_nonce(basename(__FILE__)),
        ));
    }

    public static function apiBase()
    {
        return self::slug;
    }

    public function addApiEntry()
    {
        if (!\current_user_can('edit_posts'))
            throw new \Exception("Access denied.");

        \check_ajax_referer('contentegg-prefill', 'nonce');

        if (empty($_POST['module_id']))
            throw new \Exception("Module is undefined.");
        if (empty($_POST['post_id']))
            throw new \Exception("Post ID is undefined.");

        $module_id = TextHelper::clear(sanitize_text_field(wp_unslash($_POST['module_id'])));
        $post_id = intval($_POST['post_id']);
        $keyword_source = isset($_POST['keyword_source']) ? sanitize_text_field(wp_unslash($_POST['keyword_source'])) : '';
        $autoupdate = isset($_POST['autoupdate']) ? sanitize_text_field(wp_unslash($_POST['autoupdate'])) : false;
        $autoupdate = filter_var($autoupdate, FILTER_VALIDATE_BOOLEAN);      
        $keyword_count = isset($_POST['keyword_count']) ? intval(wp_unslash($_POST['keyword_count'])) : 5;
        $minus_words = isset($_POST['minus_words']) ? TextHelper::commaList(sanitize_text_field(wp_unslash($_POST['minus_words']))) : '';
        $custom_field_names = isset($_POST['custom_field_names']) ? array_map('sanitize_text_field', wp_unslash($_POST['custom_field_names'])) : array();
        $custom_field_values = isset($_POST['custom_field_values']) ? array_map('sanitize_text_field', wp_unslash($_POST['custom_field_values'])) : array();
        $custom_field = isset($_POST['custom_field']) ? sanitize_text_field(wp_unslash($_POST['custom_field'])) : '';

        $parser = ModuleManager::getInstance()->parserFactory($module_id);
        if (!$parser->isActive())
            throw new \Exception("Parser module " . $parser->getId() . " is inactive.");

        if (!$post = \get_post($post_id))
            throw new \Exception("Post does not exists.");

        $log = 'Post ID: ' . $post->ID;
        $log .= ' (' . TextHelper::truncate($post->post_title) . ').';

        // data exists?
        if (ContentManager::isDataExists($post->ID, $parser->getId()))
        {
            $log .= ' - ' . __('Data already exist.', 'content-egg');
            $this->printResult($log);
        }

        $keyword = $this->getKeyword($post_id, $keyword_source, $keyword_count, $custom_field);

        if ($minus_words)
        {
            $minus_words = explode(',', $minus_words);            
            $keyword = trim(str_replace($minus_words, '', $keyword));
            $keyword = preg_replace("/\s+/ui", ' ', $keyword);
        }

        if (!$keyword)
            $this->printResult($log . ' - ' . __('Unable to find keyword.', 'content-egg'));

        $log .= ' Keyword: "' . $keyword . '"';

        // autoupdate keyword
        if ($autoupdate && $parser->isAffiliateParser())
        {
            // exists?
            if (\get_post_meta($post->ID, ContentManager::META_PREFIX_KEYWORD . $parser->getId(), true))
                $this->printResult($log . ' - ' . __('Keyword for autoupdate already exists.', 'content-egg'));

            // save & exit...
            \update_post_meta($post->ID, ContentManager::META_PREFIX_KEYWORD . $parser->getId(), $keyword);
            //$this->printResult($log . ' - ' . __('Keyword for autoupdate was saved.', 'content-egg'));
        }

        try
        {
            $data = $parser->doMultipleRequests($keyword, array(), true);
        } catch (\Exception $e)
        {
            // error
            $log .= ' - ' . __('Error:', 'content-egg') . ' ' . $e->getMessage();
            $this->printResult($log);
        }

        // nodata!
        if (!$data)
        {
            $log .= ' - ' . __('No data found...', 'content-egg');
            $this->printResult($log);
        }

        // save
        ContentManager::saveData($data, $parser->getId(), $post->ID);
        $log .= ' - ' . __('Data saved:', 'content-egg') . ' ' . count($data) . '.';

        // add custom fields
        $meta_input = array();
        if ($custom_field_names && is_array($custom_field_names))
        {
            foreach ($custom_field_names as $i => $cf_name)
            {
                if (!$cf_name || empty($custom_field_values[$i]))
                    continue;
                $cf_value = $custom_field_values[$i];
                if (\is_serialized($cf_value))
                    $cf_value = @unserialize($cf_value);
                else
                {
                    $modules_data = array($parser->getId() => $data);
                    $main_product = ContentManager::getMainProduct($modules_data, 'min_price');
                    $cf_value = AutoblogModel::buildTemplate($cf_value, $modules_data, $keyword, array(), $main_product);
                }
                \update_post_meta($post->ID, $cf_name, $cf_value);
            }
        }

        $this->printResult($log);
    }

    private function printResult($mess)
    {
        $res = array();
        $res['log'] = htmlspecialchars($mess);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($res);
        \wp_die();
    }

    private function getKeyword($post_id, $keyword_source, $keyword_count, $meta_name = '')
    {
        $keyword = '';
        if ($keyword_source == '_title')
        {
            $post = \get_post($post_id);
            $keyword = $post->post_title;
        } elseif ($keyword_source == '_tags')
        {
            $keyword = join(' ', \wp_get_post_tags($post_id, array('fields' => 'names')));
        } elseif ($keyword_source == '_density')
        {
            $kd = new KeywordDensity(GeneralConfig::getInstance()->option('lang'));
            $kd->setText($this->getDensText($post_id));
            $popular = $kd->getPopularWords($keyword_count);
            $keyword = join(' ', $popular);
        } elseif ($keyword_source == '_custom_field')
        {
            if (!$meta_name)
                return '';
            return \get_post_meta($post_id, $meta_name, true);
        } elseif (substr($keyword_source, 0, 9) == '_keyword_')
        {
            $module_id = substr($keyword_source, 9);
            if (!ModuleManager::getInstance()->moduleExists($module_id))
                return '';

            $keyword = \get_post_meta($post_id, ContentManager::META_PREFIX_KEYWORD . $module_id, true);
        } elseif (substr($keyword_source, 0, 5) == '_ean_')
        {
            $module_id = substr($keyword_source, 5);
            if (!ModuleManager::getInstance()->moduleExists($module_id))
                return '';

            if (!$data = ContentManager::getViewData($module_id, $post_id))
                return '';

            foreach ($data as $d)
            {
                if (!empty($d['ean']))
                {
                    $keyword = $d['ean'];
                    break;
                }
            }
        }

        if (!filter_var($keyword, FILTER_VALIDATE_URL))
        {
            // split into words
            $wordlist = preg_split('/\W/u', $keyword, 0, PREG_SPLIT_NO_EMPTY);
            $wordlist = array_unique($wordlist);
            $wordlist = array_slice($wordlist, 0, $keyword_count);
            $keyword = join(' ', $wordlist);
        }

        return $keyword;
    }

    private function getDensText($post_id)
    {
        $post = \get_post($post_id);
        $text = $post->post_title . ' ' . $post->post_content;

        $pattern = get_shortcode_regex();
        $text = preg_replace('/' . $pattern . '/s', ' ', $text);
        return $text;
    }

}
