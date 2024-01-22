<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AutoblogTable class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com/
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
            //'id' => AutoblogModel::model()->getAttributeLabel('id'),
            'name' => AutoblogModel::model()->getAttributeLabel('name'),
            'create_date' => AutoblogModel::model()->getAttributeLabel('create_date'),
            'last_check' => AutoblogModel::model()->getAttributeLabel('last_check'),
            'status' => AutoblogModel::model()->getAttributeLabel('status'),
            'post_count' => AutoblogModel::model()->getAttributeLabel('post_count'),
                )
        );
        return $columns;
    }

    function column_name($item)
    {
        if (!trim($item['name']))
            $item['name'] = __('(without title)', 'affegg');

        $edit_url = '?page=affiliate-egg-autoblog-edit&id=%d';
        $delete_nonce = \wp_create_nonce('bulk-affegg-all-tables');

        $actions = array(
            'edit' => sprintf('<a href="' . $edit_url . '">%s</a>', $item['id'], __('Edit', 'affegg')),
            'run' => sprintf('<a class="run_avtoblogging" href="?page=affiliate-egg-autoblog&action=run&id=%d">%s</a>', $item['id'], __('Run now', 'affegg')),
            'delete' => sprintf('<a class="affegg-delete" href="?page=affiliate-egg-autoblog&action=delete&id=%d&_wpnonce=%s">%s</a>', $item['id'], $delete_nonce, __('Delete', 'affegg')),
        );
        $row_text = sprintf('<strong><a title="Редактировать" class="row-title" href="' . $edit_url . '">' . esc_html($item['name']) . '</a></strong>', $item['id']);
        return sprintf('%s %s', $row_text, $this->row_actions($actions));
    }

    function column_status($item)
    {
        if ($item['status'])
            return '<span style="color:green">' . __('Works', 'affegg') . '</span>';
        else
            return '<span style="color:red">' . __('Stoped', 'affegg') . '</span>';
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'id' => array('id', true),
            'name' => array('name', true),
            'create_date' => array('create_date', true),
            'last_check' => array('update_date', true),
            'status' => array('status', true),
            'post_count' => array('post_count', true)
        );
        return $sortable_columns;
    }

}
