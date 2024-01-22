<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;
/**
 * MyListTable class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
if (!class_exists('\WP_List_Table'))
{
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class MyListTable extends \WP_List_Table {

    const per_page = 15;

    private $numeric_search = true;
    private $owner_check = false;
    private $model;

    function __construct(Model $model, array $config = array())
    {
        global $status, $page;

        if (isset($config['numeric_search']))
            $this->numeric_search = (bool) $config['numeric_search'];
        if (isset($config['owner_check']))
            $this->owner_check = (bool) $config['owner_check'];

        $this->model = $model;
        parent::__construct(array(
            'singular' => 'affegg-table',
            'plural' => 'affegg-all-tables',
            'screen' => get_current_screen()
        ));
    }

    function prepare_items()
    {
        global $wp_version;
        global $wpdb;

        $user_id = get_current_user_id();
        $columns = $this->get_columns();
        $where = '';
        $where_count = '';

        if ($this->owner_check && !is_super_admin())
        {
            $where = 'user_id = ' . $user_id;
            $where_count = "user_id = " . $user_id;
        }

        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
        $total_items = $this->model->count($where_count);

        if (!empty($_REQUEST['s']))
        {
            if ($this->numeric_search && is_numeric($_REQUEST['s']))
            {
                if ($where)
                    $where .= ' AND ';
                $where .= 'id = ' . (int) $_REQUEST['s'];
            } else
            {
                if ($where)
                    $where .= ' AND ';
                $where = array($where);
                $where[0] .= 'name LIKE %s';
                $where[1] = array('%' . sanitize_text_field($_REQUEST['s']) . '%');
            }
        }
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

        $params = array(
            'where' => $where,
            'limit' => self::per_page,
            'offset' => $paged * self::per_page,
            'order' => $orderby . ' ' . $order,
        );
        $this->items = $this->model->findAll($params);

        $this->set_pagination_args(
                array(
                    'total_items' => $total_items,
                    'per_page' => self::per_page,
                    'total_pages' => ceil($total_items / self::per_page)
        ));
    }

    function column_default($item, $column_name)
    {
        return esc_html($item[$column_name]);
    }

    private function view_column_datetime($item, $col_name)
    {
        if ($item[$col_name] == '0000-00-00 00:00:00')
            return ' - ';

        $modified_timestamp = strtotime($item[$col_name]);
        $current_timestamp = current_time('timestamp');
        $time_diff = $current_timestamp - $modified_timestamp;
        if ($time_diff >= 0 && $time_diff < DAY_IN_SECONDS)
            $time_diff = human_time_diff($modified_timestamp, $current_timestamp) . ' ' . __('ago', 'affegg');
        else
            $time_diff = TemplateHelper::format_datetime($item[$col_name], 'mysql', '<br />');

        $readable_time = TemplateHelper::format_datetime($item[$col_name], 'mysql', ' ');
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

    function column_cb($item)
    {
        return sprintf(
                '<input type="checkbox" name="id[]" value="%d" />', $item['id']
        );
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => __('Delete', 'affegg')
        );
        return $actions;
    }

    function process_bulk_action()
    {
        if ($this->current_action() === 'delete')
        {
            if (!isset($_REQUEST['_wpnonce']) || !\wp_verify_nonce(sanitize_key($_REQUEST['_wpnonce']), 'bulk-' . $this->_args['plural']))
                die('Invalid nonce');
            
            $ids = isset($_GET['id']) ? $_REQUEST['id'] : array();
            if (!is_array($ids))
                $ids = (array) $ids;
            foreach ($ids as $id)
            {
                $id = (int) $id;
                if ($this->owner_check && !is_super_admin())
                {
                    $egg = EggModel::model()->findByPk($id);
                    if (!$egg || $egg['user_id'] != get_current_user_id())
                    {
                        \wp_die(__('You do not have sufficient permissions to access this page.', 'default'));
                    }
                }
                $this->model->delete($id);
            }
        }
    }

}
