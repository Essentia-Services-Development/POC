<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * EggThickboxTable class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class EggThickboxTable extends MyListTable {

    const per_page = 15;

    function get_columns()
    {
        $columns = array(
            'id' => EggModel::model()->getAttributeLabel('id'),
            'name' => EggModel::model()->getAttributeLabel('name'),
            //'create_date' => EggModel::model()->getAttributeLabel('create_date'),
            'affegg_action' => 'Действие',
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'id' => array('id', true),
            'name' => array('name', true),
            'create_date' => array('create_date', true),
        );
        return $sortable_columns;
    }

    protected function column_affegg_action(array $item)
    {
        return '<input type="button" class="insert-shortcode button" title="' . esc_attr("[affegg id={$item['id']}]") . '" value="' . __('Add', 'affegg') . '" />';
    }

    function get_bulk_actions()
    {
        return array();
    }

}
