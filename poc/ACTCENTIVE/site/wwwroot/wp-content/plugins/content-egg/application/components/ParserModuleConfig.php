<?php

namespace ContentEgg\application\components;

defined('\ABSPATH') || exit;

/**
 * ParserModuleConfig abstract class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
abstract class ParserModuleConfig extends ModuleConfig {

    public function options()
    {
        $tpl_manager = ModuleTemplateManager::getInstance($this->module_id);
        $options = array(
            'is_active' => array(
                'title' => __('Enable module', 'content-egg'),
                'description' => '',
                'callback' => array($this, 'render_checkbox'),
                'default' => 0,
                'section' => 'default',
                'validator' => array(
                    array(
                        'call' => array($this, 'checkRequirements'),
                        'message' => __('Could not activate.', 'content-egg'),
                    ),
                ),
            ),
            'embed_at' => array(
                'title' => __('Auto-embedding', 'content-egg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'shortcode' => __('Shortcodes only', 'content-egg'),
                    'post_bottom' => __('At the end of the post', 'content-egg'),
                    'post_top' => __('At the beginning of the post', 'content-egg'),
                ),
                'default' => 'shortcode',
                'section' => 'default',
            ),
            'priority' => array(
                'title' => __('Priority', 'content-egg'),
                'description' => __('Priority sets order of modules in post. 0 - is the most highest priority.', 'content-egg') . ' ' .
                __('Also it applied to price sorting.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => 10,
                'validator' => array(
                    'trim',
                    'absint',
                ),
                'section' => 'default',
            ),
            'template' => array(
                'title' => __('Template', 'content-egg'),
                'description' => __('Default template', 'content-egg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => $tpl_manager->getTemplatesList(),
                'default' => $this->getModuleInstance()->defaultTemplateName(),
                'section' => 'default',
            ),
            'tpl_title' => array(
                'title' => __('Title', 'content-egg'),
                'description' => __('Templates may use title on data output.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'section' => 'default',
            ),
            'featured_image' => array(
                'title' => 'Featured image',
                'description' => __('Automatically set Featured image for post', 'content-egg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    '' => __('Don\'t set', 'content-egg'),
                    'first' => __('First image', 'content-egg'),
                    'second' => __('Second image', 'content-egg'),
                    'rand' => __('Random image', 'content-egg'),
                    'last' => __('Last image', 'content-egg'),
                ),
                'default' => '',
                'section' => 'default',
            ),
            'set_local_redirect' => array(
                'title' => __('Redirect', 'content-egg'),
                'description' => __('Make links with local 301 redirect', 'content-egg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => 0,
                'section' => 'default',
            ),
        );

        return array_merge(parent::options(), $options);
    }

    public function checkRequirements($value)
    {
        if ($requirements = $this->getModuleInstance()->requirements())
        {
            return false;
        } else
        {
            return true;
        }
    }

    protected static function moveRequiredUp(array $options)
    {
        $keys = array('is_active');

        foreach ($options as $key => $option)
        {
            if (strpos($option['title'], '*'))
            {
                $keys[] = $key;
            }

            $options[$key]['title'] = str_replace('**', '', $option['title']);
        }

        $res = array();
        foreach ($keys as $key)
        {
            $res[$key] = $options[$key];
            unset($options[$key]);
        }

        $res = array_merge($res, $options);

        return $res;
    }

}
