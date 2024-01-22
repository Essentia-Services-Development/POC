<?php

namespace ContentEgg\application;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\CEWidget;

/**
 * ProductSearchWidget class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com/
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class ProductSearchWidget extends CEWidget {

    public function slug()
    {
        return 'cegg_product_search';
    }

    public function description()
    {
        return __('A search form for affiliate products.', 'content-egg');
    }

    protected function name()
    {
        return __('CE: Product Search', 'content-egg');
    }

    public function classname()
    {
        return 'widget widget_search';
    }

    public function settings()
    {
        return
                array(
                    'title' => array(
                        'type' => 'text',
                        'default' => '',
                        'title' => __('Title', 'content-egg'),
                    ),
        );
    }

    /**
     * Front-end display of widget.
     */
    public function widget($args, $instance)
    {
        $this->beforeWidget($args, $instance);

        // Use current theme search form if it exists
        echo self::getSearchForm(); // phpcs:ignore

        $this->afterWidget($args, $instance);
    }

    public static function getSearchForm()
    {
        $search_form_template = \locate_template('ce-product-searchform.php');
        if ($search_form_template)
        {
            ob_start();
            require( $search_form_template );
            $form = ob_get_clean();
        } else
        {
            // standart wp search from
            $form = \get_search_form(false);

            if (\get_option('permalink_structure'))
                $form = preg_replace('/action=["\'].+?["\']/', 'action="' . self::getSearchFormUri() . '"', $form);
            else
                $form = preg_replace('/<\/form>/', '<input type="hidden" name="pagename" value="' . \esc_attr(ProductSearch::getPageSlug()) . '"></form>', $form);

            $form = preg_replace('/placeholder=".+?"/', 'placeholder="' . esc_attr(__('Product Search...', 'content-egg-tpl')) . '"', $form);
        }
        return $form;
    }

    public static function getSearchFormUri()
    {
        if (\get_option('permalink_structure'))
            return \esc_url(\home_url(ProductSearch::getPageSlug()));
        else
            return \esc_url(\home_url('/'));
    }

}
