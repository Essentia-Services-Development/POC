<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\models\LogModel;
use ExternalImporter\application\components\logger\Logger;
use ExternalImporter\application\Plugin;

/**
 * LogTable class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
class LogTable extends MyListTable {

    const per_page = 20;

    function get_columns()
    {
        return
                array(
                    'log_level' => LogModel::model()->getAttributeLabel('log_level'),
                    'log_time' => LogModel::model()->getAttributeLabel('log_time'),
                    'message' => LogModel::model()->getAttributeLabel('message'),
        );
    }

    function column_message($item)
    {
        return \esc_html($item['message']);
    }

    function column_log_time($item)
    {
        return $this->view_column_date($item, 'log_time');
    }

    function column_log_level($item)
    {

        if ($item['log_level'] == Logger::LEVEL_ERROR)
            $class = 'error';
        elseif ($item['log_level'] == Logger::LEVEL_WARNING)
            $class = 'warning';
        elseif ($item['log_level'] == Logger::LEVEL_INFO)
            $class = 'info';
        elseif ($item['log_level'] == Logger::LEVEL_DEBUG)
            $class = 'debug';
        else
            $class = '';

        return '<mark class="' . \esc_attr($class) . '">' . ucfirst(Logger::getLevel($item['log_level'])) . '</mark>';
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'log_level' => array('log_level', true),
            'log_time' => array('log_time', true),
        );

        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        return array();
    }

    protected function getWhereFilters()
    {
        global $wpdb;

        $where = '';

        // filters
        if (isset($_GET['log_level']) && $_GET['log_level'] !== '' && $_GET['log_level'] !== 'all')
        {
            $log_level = (int) $_GET['log_level'];

            if (array_key_exists($log_level, Logger::getLevels()))
            {
                if ($where)
                    $where .= ' AND ';
                $where .= $wpdb->prepare('log_level = %d', $log_level);
            }
        }

        return $where;
    }

    protected function extra_tablenav($which)
    {
        if ($which != 'top')
            return;

        $links = array();
        $class = (!isset($_REQUEST['log_level']) || $_REQUEST['log_level'] === '' || $_REQUEST['log_level'] === 'all') ? ' class="current"' : '';
        $admin_url = \get_admin_url(\get_current_blog_id(), 'admin.php?page=' . Plugin::slug() . '-logs');

        $levels = Logger::getLevels();
        $total = LogModel::model()->count();
        $links['all'] = '<a href="' . $admin_url . '&log_level="' . $class . '>' . __('All', 'external-importer') . sprintf(' <span class="count">(%s)</span></a>', \number_format_i18n($total));
        foreach ($levels as $level_id => $level_name)
        {
            $total = LogModel::model()->count('log_level = ' . (int) $level_id);
            if (!$total)
                continue;
            $class = (isset($_REQUEST['log_level']) && $_REQUEST['log_level'] !== '' && (int) $_REQUEST['log_level'] == $level_id) ? ' class="current"' : '';
            $links[$level_id] = '<a href="' . $admin_url . '&log_level=' . (int) $level_id . '"' . $class . '>' . \esc_html(ucfirst($level_name));
            $links[$level_id] .= sprintf(' <span class="count">(%s)</span></a>', \number_format_i18n($total));
        }
        echo '<div class="alignleft actions">';
        echo '<ul class="subsubsub">';

        $i = 0;
        foreach ($links as $id => $link)
        {
            echo '<li class="' . \esc_html($id) . '">';
            echo $link;
            if ($i < count($links) - 1)
                echo ' | ';

            echo '</li>';
            $i++;
        }
        echo '</ul>';
        echo '</div>';
    }

}
