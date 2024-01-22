<?php

namespace ContentEgg\application;

use ContentEgg\application\admin\GeneralConfig;
use ContentEgg\application\components\ModuleManager;
use ContentEgg\application\ModuleViewer;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\WooIntegrator;

defined('\ABSPATH') || exit;

/**
 * DataRestController class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class DataRestController extends \WP_REST_Controller {

    protected $namespace = 'cegg/v1/data';

    const VERSION = 1;
    const BASE = 'data';

    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct()
    {
        
    }

    public function init()
    {
        \add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes()
    {
        \register_rest_route(
                $this->namespace,
                '/' . $this->rest_base . '/(?P<post_id>[\d]+)',
                array(
                    array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($this, 'get_items'),
                        'permission_callback' => '__return_true',
                        'args' => array(
                            'module_id' => array(
                                'description' => __('Module ID.', 'woocommerce'),
                                'type' => 'string',
                            ),
                            'module_type' => array(
                                'description' => __('Module type.', 'woocommerce'),
                                'type' => 'string',
                            ),
                            'extra' => array(
                                'description' => __('Return extra.', 'woocommerce'),
                                'type' => 'boolean',
                            ),
                            'synced' => array(
                                'description' => __('Return a synced product only.', 'woocommerce'),
                                'type' => 'boolean',
                            ),
                        ),
                    ),
                    'schema' => array($this, 'get_public_item_schema'),
                )
        );
    }

    public function get_items($request, $module_type = '')
    {
        $post_id = (int) $request['post_id'];
        if (!isset($request['extra']))
            $return_extra = false;
        else
            $return_extra = (bool) $request['extra'];

        if (!isset($request['synced']))
            $return_synced = false;
        else
            $return_synced = (bool) $request['synced'];

        if (\get_post_status($post_id) !== 'publish')
            return new \WP_Error('cegg_rest_post_invalid_id', 'Invalid post ID.', array('status' => 404));

        if (!in_array(\get_post_type($post_id), GeneralConfig::getInstance()->option('post_types')))
            return new \WP_Error('cegg_rest_post_invalid_id', 'Invalid post type.', array('status' => 404));

        $data = array();

        $module_ids = ModuleManager::getInstance()->getParserModulesIdList(true);

        if ($return_synced)
        {
            $mdata = WooIntegrator::getSyncItem($post_id);

            if (!$mdata || !isset($mdata['module_id']) || !in_array($mdata['module_id'], $module_ids))
                return array();

            $data[$mdata['module_id']] = array($mdata['unique_id'] => $mdata);
        } else
        {
            if (!empty($request['module_id']))
                $module_ids = array_intersect($module_ids, TextHelper::getArrayFromCommaList($request['module_id']));

            if (!empty($request['module_type']))
                $module_type = TextHelper::getArrayFromCommaList(strtoupper($request['module_type']));
            else
                $module_type = array();

            if ($module_type)
                $module_ids = array_intersect($module_ids, ModuleManager::getInstance()->getParserModuleIdsByTypes($module_type, true));

            foreach ($module_ids as $module_id)
            {
                if ($mdata = ModuleViewer::getInstance()->getData($module_id, $post_id))
                    $data[$module_id] = $mdata;
            }
        }

        if (!$return_extra)
        {
            foreach ($data as $module_id => $mdata)
            {
                foreach ($mdata as $unique_id => $d)
                {
                    $d['extra'] = array();
                    $data[$module_id][$unique_id] = $d;
                }
            }
        }

        return \rest_ensure_response($data);
    }

}
