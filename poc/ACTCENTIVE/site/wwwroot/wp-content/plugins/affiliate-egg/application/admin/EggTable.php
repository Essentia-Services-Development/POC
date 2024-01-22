<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * EggTable class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class EggTable extends MyListTable {

    const per_page = 15;

    function get_columns()
    {
        $columns = array_merge(
                array(
                    'cb' => '<input type="checkbox" />',
                ), array(
            'id' => EggModel::model()->getAttributeLabel('id'),
            'name' => EggModel::model()->getAttributeLabel('name'),
            'update_date' => EggModel::model()->getAttributeLabel('update_date'),
            'create_date' => EggModel::model()->getAttributeLabel('create_date'),
                )
        );
        return $columns;
    }

    function column_name($item)
    {
        if (!trim($item['name']))
            $item['name'] = __('(without title)', 'affegg');

        $edit_url = '?page=affiliate-egg-edit&id=%d';

        $actions = array(
            'edit' => sprintf('<a href="' . $edit_url . '">%s</a>', $item['id'], __('Edit', 'affegg')),
            'shortcode' => sprintf('<a href="#" title="[affegg id=%d]">%s</a>', $item['id'], __('Shortcode', 'affegg')),
            'update_products' => sprintf('<a class="force_update_products" href="?page=affiliate-egg&action=update_products&id=%d">%s</a>', $item['id'], __('Update products', 'affegg')),
            'update_catalogs' => sprintf('<a class="force_update_catalogs" href="?page=affiliate-egg&action=update_catalogs&id=%d">%s</a>', $item['id'], __('Update Catalogs', 'affegg')),
            'delete' => sprintf('<a class="affegg-delete" href="?page=affiliate-egg&action=delete&id=%d">%s</a>', $item['id'], __('Delete', 'affegg')),
        );
        $row_text = sprintf('<strong><a title="Edit" class="row-title" href="' . $edit_url . '">' . \esc_html(str_replace('%', '%%', $item['name'])) . '</a></strong>', $item['id']);
        return sprintf('%s %s', $row_text, $this->row_actions($actions));
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'id' => array('id', true),
            'name' => array('name', true),
            'create_date' => array('create_date', true),
            'update_date' => array('update_date', false)
        );
        return $sortable_columns;
    }

}
