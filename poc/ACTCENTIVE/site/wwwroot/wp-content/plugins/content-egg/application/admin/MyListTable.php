<?php

namespace ContentEgg\application\admin;

defined('\ABSPATH') || exit;

use ContentEgg\application\Plugin;
use ContentEgg\application\models\Model;
use ContentEgg\application\helpers\TemplateHelper;

/**
 * MyListTable class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
if (!class_exists('\WP_List_Table'))
{
    require_once( \ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class MyListTable extends \WP_List_Table
{

    const per_page = 15;

    private $model;

    function __construct(Model $model, array $config = array())
    {
        global $status, $page;

        $this->model = $model;
        parent::__construct(array(
            'singular' => Plugin::getSlug() . '-table',
            'plural' => Plugin::getSlug() . '-all-tables',
            'screen' => get_current_screen()
        ));
    }

    function default_orderby()
    {
        return 'id';
    }

    protected function getWhereFilters()
    {
        return '';
    }

    function prepare_items()
    {
        $doaction = $this->current_action();
        if ($doaction)
        {
            //@todo
        }

        $columns = $this->get_columns();
        $where = $this->getWhereFilters();

        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();

        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? sanitize_text_field(wp_unslash($_REQUEST['orderby'])) : $this->default_orderby();
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? sanitize_key($_REQUEST['order']) : 'desc';

        $params = array(
            'select' => 'SQL_CALC_FOUND_ROWS *',
            'where' => $where,
            'limit' => static::per_page,
            'offset' => $paged * static::per_page,
            'order' => $orderby . ' ' . $order,
        );
        $this->items = $this->model->findAll($params);
        $total_items = (int) $this->model->getDb()->get_var('SELECT FOUND_ROWS();');

        $this->set_pagination_args(
                array(
                    'total_items' => $total_items,
                    'per_page' => static::per_page,
                    'total_pages' => ceil($total_items / static::per_page)
        ));
    }

    function column_default($item, $column_name)
    {
        return \esc_html($item[$column_name]);
    }

    private function view_column_datetime($item, $col_name)
    {

        if ($item[$col_name] == '0000-00-00 00:00:00')
            return ' - ';

        $modified_timestamp = strtotime($item[$col_name]);
        $current_timestamp = current_time('timestamp');
        $time_diff = $current_timestamp - $modified_timestamp;
        if ($time_diff >= 0 && $time_diff < DAY_IN_SECONDS)
            $time_diff = human_time_diff($modified_timestamp, $current_timestamp) . __(' ago', 'content-egg');
        else
            $time_diff = TemplateHelper::formatDatetime($item[$col_name], 'mysql', '<br />');

        $readable_time = TemplateHelper::formatDatetime($item[$col_name], 'mysql', ' ');
        return '<abbr title="' . esc_attr($readable_time) . '">' . $time_diff . '</abbr>';
    }

    function column_create_date($item)
    {
        return $this->view_column_datetime($item, 'create_date');
    }

    function column_update_date($item)
    {
        return $this->view_column_datetime($item, 'update_date');
    }

    function column_last_check($item)
    {
        return $this->view_column_datetime($item, 'last_check');
    }

    function column_last_run($item)
    {
        return $this->view_column_datetime($item, 'last_run');
    }

    function column_cb($item)
    {
        return sprintf(
                '<input type="checkbox" name="id[]" value="%d" />', $item['id']
        );
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => __('Delete', 'content-egg')
        );
        return $actions;
    }

    function process_bulk_action()
    {
        if ($this->current_action() === 'delete' && !empty($_REQUEST['id']))
        {            
            if (!isset($_REQUEST['_wpnonce']) || !\wp_verify_nonce(sanitize_key($_REQUEST['_wpnonce']), 'bulk-' . $this->_args['plural']))
                die('Invalid nonce');

            $ids = array_map('absint', (array) $_REQUEST['id']);
            
            foreach ($ids as $id)
            {
                $id = (int) $id;
                $this->model->delete($id);
            }
        }
    }

}
