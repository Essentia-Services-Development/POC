<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\models\AutoImportModel;
use ExternalImporter\application\components\Throttler;

/**
 * AutoimportTable class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class AutoimportTable extends MyListTable {

    const per_page = 15;

    function get_columns()
    {
        $columns = array_merge(
                array(
                    'cb' => '<input type="checkbox" />',
                ), array(
            'name' => AutoImportModel::model()->getAttributeLabel('name'),
            'domain' => AutoImportModel::model()->getAttributeLabel('domain'),
            'create_date' => AutoImportModel::model()->getAttributeLabel('create_date'),
            'last_run' => AutoImportModel::model()->getAttributeLabel('last_run'),
            'status' => AutoImportModel::model()->getAttributeLabel('status'),
            'post_count' => AutoImportModel::model()->getAttributeLabel('post_count'),
            'last_error' => AutoImportModel::model()->getAttributeLabel('last_error'),
                )
        );
        return $columns;
    }

    function column_name($item)
    {
        if (!trim($item['name']))
            $item['name'] = __('(no title)', 'external-importer');

        $edit_url = '?page=external-importer-autoimport-edit&id=%d';
        $delete_nonce = \wp_create_nonce('bulk-external-importer-all-tables');
        $run_nonce = \wp_create_nonce('exi_autoimport_run');
        
        $actions = array(
            'edit' => sprintf('<a href="' . $edit_url . '">%s</a>', $item['id'], __('Edit', 'external-importer')),
            'run' => sprintf('<a class="ei-run-autoimport" href="?page=external-importer-autoimport&action=run&id=%d&_wpnonce=%s">%s</a>', $item['id'], $run_nonce, __('Run now', 'external-importer')),
            'delete' => sprintf('<a class="ei-autoimport-delete" href="?page=external-importer-autoimport&action=delete&id=%d&_wpnonce=%s">%s</a>', $item['id'], $delete_nonce, __('Delete', 'external-importer')),
        );
        $row_text = sprintf('<strong><a title="' . __('Edit', 'external-importer') . '" class="row-title" href="' . $edit_url . '">' . \esc_html($item['name']) . '</a></strong>', $item['id']);
        return sprintf('%s %s', $row_text, $this->row_actions($actions));
    }

    function column_domain($item)
    {
        $r = \esc_html($item['domain']);

        if (Throttler::isThrottled($item['domain']))
            $r .= ' <small style="color:orange;"> ' . __('throttled', 'external-importer') . '</small>';

        return $r;
    }

    function column_last_run($item)
    {
        return $this->view_column_date($item, 'last_run');
    }

    function column_status($item)
    {
        if ($item['status'])
            return '<span style="color:#7ad03a">' . __('Enabled', 'external-importer') . '</span>';
        else
            return '<span style="color:#a44">' . __('Disabled', 'external-importer') . '</span>';
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'id' => array('id', true),
            'name' => array('name', true),
            'domain' => array('domain', true),
            'create_date' => array('create_date', true),
            'last_run' => array('last_run', true),
            'status' => array('status', true),
            'post_count' => array('post_count', true)
        );
        return $sortable_columns;
    }

}
