<?php

namespace ContentEgg\application\admin;

defined('\ABSPATH') || exit;

use ContentEgg\application\models\ProductModel;
use ContentEgg\application\helpers\TemplateHelper;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\components\ModuleManager;

/**
 * ProductTable class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class ProductTable extends MyListTable {

    const per_page = 15;

    function get_columns()
    {
        return
                array(
                    'img' => '',
                    'title' => ProductModel::model()->getAttributeLabel('title'),
                    'module_id' => __('Module', 'content-egg'),
                    'stock_status' => ProductModel::model()->getAttributeLabel('stock_status'),
                    'price' => ProductModel::model()->getAttributeLabel('price'),
                    'last_update' => ProductModel::model()->getAttributeLabel('last_update'),
        );
    }

    function column_img($item)
    {
        echo '<a href="' . \esc_url(\get_edit_post_link($item['post_id'])) . '"><img class="attachment-thumbnail size-thumbnail wp-post-image" src="' . \esc_url($item['img']) . '" /></a>';
    }

    function column_title($item)
    {
        if (!trim($item['title']))
            $title = __('(no title)', 'content-egg');
        else
            $title = TextHelper::truncate($item['title'], 80);

        $edit_link = \get_edit_post_link($item['post_id']) . '#' . $item['module_id'] . '-' . $item['unique_id'];
        $actions = array(
            'post_id' => sprintf(__('Post ID: %d', 'content-egg'), $item['post_id']),
            'view' => sprintf('<a href="%s">%s</a>', \get_post_permalink($item['post_id']), __('View', 'content-egg')),
            
            'edit' => sprintf('<a href="%s">%s</a>', \esc_url($edit_link), __('Edit', 'content-egg')),
        );
        if ($item['url'])
            $actions['goto'] = sprintf('<a target="_blank" href="%s">%s</a>', \esc_url($item['url']), __('Go to', 'content-egg'));

        return '<strong><a class="row-title" href="' . \esc_url($edit_link) . '">' . \esc_html($title) . '</a></strong>' .
                $this->row_actions($actions);
    }

    function column_price($item)
    {
        $res = (float) $item['price_old'] ? '<del>' . \wp_kses_post(TemplateHelper::formatPriceCurrency($item['price_old'], $item['currency_code'])) . '</del>' : '';
        $res .= (float) $item['price'] ? '<ins>' . \wp_kses_post(TemplateHelper::formatPriceCurrency($item['price'], $item['currency_code'])) . '</ins>' : '<span class="na">&ndash;</span>';
        return $res;
    }

    function column_stock_status($item)
    {
        if ($item['stock_status'] == ContentProduct::STOCK_STATUS_IN_STOCK)
            return '<mark class="instock">' . __('In stock', 'content-egg') . '</mark>';
        elseif ($item['stock_status'] == ContentProduct::STOCK_STATUS_OUT_OF_STOCK)
            return '<mark class="outofstock">' . __('Out of stock', 'content-egg') . '</mark>';
        elseif ($item['stock_status'] == ContentProduct::STOCK_STATUS_UNKNOWN)
            return '<span class="na">&ndash;</span>';
    }

    function column_module_id($item)
    {
        $module_id = $item['module_id'];
        if (!ModuleManager::getInstance()->moduleExists($module_id))
            return;
        $module = ModuleManager::getInstance()->factory($item['module_id']);
        $output = '<strong>' . esc_html($module->getName()) . '</strong>';

        if (!$module->isActive())
            $output .= '<br><mark class="inactive">' . esc_html(__('inactive', 'content egg')) . '</mark>';

        return $output;
    }

    function column_last_update($item)
    {
        if (empty($item['last_update']))
            return '<span class="na">&ndash;</span>';

        $last_update_timestamp = strtotime($item['last_update']);
        $show_date_time = TemplateHelper::dateFormatFromGmt($last_update_timestamp, true);

        // last 24 hours?
        if ($last_update_timestamp > strtotime('-1 day', \current_time('timestamp', true)))
        {
            $show_date = sprintf(
                    __('%s ago', '%s = human-readable time difference', 'content-egg'), \human_time_diff($last_update_timestamp, \current_time('timestamp', true))
            );
        } else
        {
            $show_date = TemplateHelper::dateFormatFromGmt($last_update_timestamp, false);
        }
        return sprintf(
                '<abbr datetime="%1$s" title="%2$s">%3$s</abbr>', esc_attr($show_date_time), esc_attr($show_date_time), esc_html($show_date)
        );
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'price' => array('price', true),
            'title' => array('title', true),
            'module_id' => array('module_id', true),
            'stock_status' => array('stock_status', true),
            'last_update' => array('last_update', true),
        );

        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        return array();
    }

    protected function extra_tablenav($which)
    {
        if ($which != 'top')
            return;

        echo '<div class="alignleft actions">';

        $this->print_modules_dropdown();
        \submit_button(__('Filter', 'content-egg'), '', 'filter_action', false, array('id' => 'product-query-submit'));

        echo '</div>';
    }

    private function print_modules_dropdown()
    {
        $modules = ModuleManager::getInstance()->getAffiliteModulesList(true);
        $selected_module_id = !empty($_GET['module_id']) ? TextHelper::clear(sanitize_text_field( \wp_unslash($_GET['module_id']))) : '';

        echo '<select name="module_id" id="dropdown_module_id"><option value="">' . \esc_html__('Filter by module', 'content-egg') . '</option>';
        foreach ($modules as $module_id => $module_name)
        {
            echo '<option ' . \selected($module_id, $selected_module_id, false) . ' value="' . \esc_attr($module_id) . '">' . \esc_html($module_name) . '</option>';
        }
        echo '</select>';
    }

    protected function getWhereFilters()
    {
        global $wpdb;

        $where = '';

        // search
        if (!empty($_REQUEST['s']))
        {
            $s = trim(sanitize_text_field(wp_unslash($_REQUEST['s'])));
            if ($where)
                $where .= ' AND ';

            if (is_numeric($s))
                $where .= 'post_id = ' . (int) $s;
            else
                $where .= $wpdb->prepare('title LIKE %s', '%' . $wpdb->esc_like(\sanitize_text_field($s)) . '%');
        }

        // filters
        if (isset($_GET['stock_status']) && $_GET['stock_status'] !== '' && $_GET['stock_status'] !== 'all')
        {
            $stock_status = (int) $_GET['stock_status'];

            if (array_key_exists($stock_status, ProductModel::getStockStatuses()))
            {
                if ($where)
                    $where .= ' AND ';

                $where .= $wpdb->prepare('stock_status = %d', $stock_status);
            }
        }

        if (isset($_GET['module_id']) && $_GET['module_id'] !== '')
        {
            $module_id = TextHelper::clear(\sanitize_text_field(\wp_unslash($_GET['module_id'])));
            if (ModuleManager::getInstance()->moduleExists($module_id))
            {
                if ($where)
                    $where .= ' AND ';
                $where .= $wpdb->prepare('module_id = %s', $module_id);
            }
        }

        return $where;
    }

    protected function get_views()
    {
        $status_links = array();
        $class = (!isset($_REQUEST['stock_status']) || $_REQUEST['stock_status'] === '' || $_REQUEST['stock_status'] === 'all') ? ' class="current"' : '';
        $admin_url = \get_admin_url(\get_current_blog_id(), 'admin.php?page=content-egg-product');

        $statuses = ProductModel::getStockStatuses();
        $total = ProductModel::model()->count();
        $status_links['all'] = '<a href="' . $admin_url . '&stock_status=all"' . $class . '>' . __('All', 'content-egg') . sprintf(' <span class="count">(%s)</span></a>', \number_format_i18n($total));
        foreach ($statuses as $status_id => $status_name)
        {
            $total = ProductModel::model()->count('stock_status = ' . (int) $status_id);
            $class = (isset($_REQUEST['stock_status']) && $_REQUEST['stock_status'] !== '' && \sanitize_text_field(wp_unslash($_REQUEST['stock_status'])) == $status_id) ? ' class="current"' : '';
            $status_links[$status_id] = '<a href="' . $admin_url . '&stock_status=' . (int) $status_id . '"' . $class . '>' . \esc_html($status_name);
            $status_links[$status_id] .= sprintf(' <span class="count">(%s)</span></a>', \number_format_i18n($total));
        }

        return $status_links;
    }

}
