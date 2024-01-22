<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\Plugin;
use ExternalImporter\application\models\Model;

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

    function __construct(Model $model = null, array $config = array())
    {
        global $status, $page;

        $this->model = $model;
        parent::__construct(array(
            'singular' => Plugin::slug() . '-table',
            'plural' => Plugin::slug() . '-all-tables',
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
        if (!$this->model)
            return array();

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
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : $this->default_orderby();
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

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

    protected function view_column_date($item, $column_name)
    {
        if (is_numeric($item[$column_name]))
            $timestamp = (int) $item[$column_name];
        else
            $timestamp = strtotime($item[$column_name]);

        if (!$timestamp)
            return ' - ';

        $current = time();
        $time_diff = $current - $timestamp;
        $readable_time = \get_date_from_gmt(date('Y-m-d H:i:s', $timestamp));
        if ($time_diff >= 0 && $time_diff < \DAY_IN_SECONDS)
            $time_diff = \human_time_diff($timestamp, $current) . ' ' . __('ago', 'external-importer');
        else
            $time_diff = $readable_time;

        return '<abbr title="' . \esc_attr($readable_time) . '">' . $time_diff . '</abbr>';
    }

    function column_create_date($item)
    {
        return $this->view_column_date($item, 'create_date');
    }

    function column_update_date($item)
    {
        return $this->view_column_date($item, 'update_date');
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
            'delete' => __('Delete', 'external-importer')
        );
        return $actions;
    }

    function process_bulk_action()
    {
        if (!$this->model)
            return;

        if ($this->current_action() === 'delete')
        {
            if (!isset($_REQUEST['_wpnonce']) || !\wp_verify_nonce(sanitize_key($_REQUEST['_wpnonce']), 'bulk-' . $this->_args['plural']))
                die('Invalid nonce');

            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (!is_array($ids))
                $ids = (array) $ids;
            $ids = array_map('absint', $ids);

            foreach ($ids as $id)
            {
                $id = (int) $id;
                $this->model->delete($id);
            }
        }
    }

}
