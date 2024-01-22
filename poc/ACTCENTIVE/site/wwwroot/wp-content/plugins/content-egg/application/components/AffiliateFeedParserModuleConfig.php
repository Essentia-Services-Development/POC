<?php

namespace ContentEgg\application\components;

defined('\ABSPATH') || exit;

/**
 * ParserModuleConfig abstract class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
abstract class AffiliateFeedParserModuleConfig extends AffiliateParserModuleConfig
{

    public function options()
    {
        $options = array_merge(parent::options(), array(
            'entries_per_page' => array(
                'title' => __('Results', 'content-egg'),
                'description' => __('Number of results for one search query.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => 10,
                'validator' => array(
                    'trim',
                    'absint',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to'),
                        'arg' => 100,
                        'message' => sprintf(__('The field "%s" can not be more than %d.', 'content-egg'), 'Results', 100),
                    ),
                ),
            ),
            'entries_per_page_update' => array(
                'title' => __('Results for updates', 'content-egg'),
                'description' => __('Number of results for automatic updates and autoblogging.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => 6,
                'validator' => array(
                    'trim',
                    'absint',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to'),
                        'arg' => 100,
                        'message' => sprintf(__('The field "%s" can not be more than %d.', 'content-egg'), 'Results', 100),
                    ),
                ),
            ),
            'partial_url_match' => array(
                'title' => __('Search partial URL', 'content-egg'),
                'description' => __('Partial URL match', 'content-egg')
                . '<p class="description">' . __('You can use part of a URL to search for products by URL.', 'content-egg') . '</p>',
                'callback' => array($this, 'render_checkbox'),
                'default' => true,
                'section' => 'default',
            ),
            'save_img' => array(
                'title' => __('Save images', 'content-egg'),
                'description' => __('Save images on server', 'content-egg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
                'section' => 'default',
            )
                ));

        $options['update_mode']['dropdown_options'] = array(
            'cron' => __('Cron', 'content-egg') . ' (' . __('recommended', 'content-egg') . ')',
            'visit' => __('Page view', 'content-egg'),
            'visit_cron' => __('Page view + Cron', 'content-egg'),
        );

        $options['update_mode']['default'] = 'cron';
        $options['update_mode']['validator'][] = array(
            'call' => array($this, 'emptyLastImportDate'),
        );

        return $options;
    }

    public function emptyLastImportDate()
    {
        $this->getModuleInstance()->setLastImportDate(0);
        $this->getModuleInstance()->setLastImportError('');

        // download feed in background
        $hook = 'cegg_' . $this->getModuleId() . '_init_products';

        if ($this->option('is_active') && !\wp_next_scheduled($hook))
        {
            \wp_schedule_single_event(time() + 1, $hook, array('module_id' => $this->getModuleId()));
        }

        return true;
    }

}
