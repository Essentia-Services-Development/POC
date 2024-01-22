<?php

namespace ContentEgg\application\admin;

defined('\ABSPATH') || exit;

use ContentEgg\application\models\AutoblogModel;

/**
 * AutoblogTable class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class AutoblogTable extends MyListTable {

    const per_page = 15;

    function get_columns()
    {
        $columns = array_merge(
                array(
                    'cb' => '<input type="checkbox" />',
                ), array(
            'name' => AutoblogModel::model()->getAttributeLabel('name'),
            'create_date' => AutoblogModel::model()->getAttributeLabel('create_date'),
            'last_run' => AutoblogModel::model()->getAttributeLabel('last_run'),
            'status' => AutoblogModel::model()->getAttributeLabel('status'),
            'keywords' => AutoblogModel::model()->getAttributeLabel('keywords'),
            'post_count' => AutoblogModel::model()->getAttributeLabel('post_count'),
            'last_error' => AutoblogModel::model()->getAttributeLabel('last_error'),
                )
        );
        return $columns;
    }

    function column_name($item)
    {
        if (!trim($item['name']))
            $item['name'] = __('(no title)', 'content-egg');

        $edit_url = '?page=content-egg-autoblog-edit&id=%d';
        $duplicate_url = '?page=content-egg-autoblog-edit&duplicate_id=%d&_wpnonce=%s';
        $duplicate_nonce = \wp_create_nonce('cegg_autoblog_duplicate');
        $delete_nonce = \wp_create_nonce('bulk-content-egg-all-tables');
        $run_nonce = \wp_create_nonce('cegg_autoblog_run');

        $actions = array(
            'edit' => sprintf('<a href="' . $edit_url . '">%s</a>', $item['id'], __('Edit', 'content-egg')),
            'run' => sprintf('<a class="run_avtoblogging" href="?page=content-egg-autoblog&action=run&id=%d&_wpnonce=%s">%s</a>', $item['id'], $run_nonce, __('Run now', 'content-egg')),
            'duplicate' => sprintf('<a href="' . $duplicate_url . '">%s</a>', $item['id'], $duplicate_nonce, __('Duplicate ', 'content-egg')),
            'delete' => sprintf('<a class="content-egg-delete" href="?page=content-egg-autoblog&action=delete&id=%d&_wpnonce=%s">%s</a>', $item['id'], $delete_nonce, __('Delete', 'content-egg')),
        );
        $row_text = sprintf('<strong><a title="' . __('Edit', 'content-egg') . '" class="row-title" href="' . $edit_url . '">' . esc_html($item['name']) . '</a></strong>', $item['id']);
        return sprintf('%s %s', $row_text, $this->row_actions($actions));
    }

    function column_status($item)
    {
        if ($item['status'])
            return '<span style="color:green">' . __('Enabled', 'content-egg') . '</span>';
        else
            return '<span style="color:red">' . __('Disabled', 'content-egg') . '</span>';
    }

    function column_keywords($item)
    {
        $item['keywords'] = unserialize($item['keywords']);

        $active = 0;
        foreach ($item['keywords'] as $keyword)
        {
            if (AutoblogModel::isActiveKeyword($keyword))
                $active++;
        }

        $abbr_title = __('active:', 'content-egg') . ' ' . $active . ', ' . __('total:', 'content-egg') . ' ' . count($item['keywords']);
        return '<abbr title="' . esc_attr($abbr_title) . '">' . $active . ' / ' . count($item['keywords']) . '</abbr>';
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'id' => array('id', true),
            'name' => array('name', true),
            'create_date' => array('create_date', true),
            'last_run' => array('last_run', true),
            'status' => array('status', true),
            'post_count' => array('post_count', true)
        );
        return $sortable_columns;
    }

}
