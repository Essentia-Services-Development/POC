<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

/**
 * ManomanoAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class ManomanoAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//a[contains(@id, 'product-card-')]/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//ul[@class='pagination']//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseDescription()
    {
        $path = array(
            ".//div[@class='description_content']",            
            ".//div[@data-testid='description-content']",
        );

        return $this->xpathScalar($path, true);        
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//p[@class='prices__retail-price']/span",
        );

        return $this->xpathScalar($paths);
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//div[@class='product-section__content product-section__content--padding']//ul[@class='list-table']/li[@class='list-table__row']/span[1]",
                'value' => ".//div[@class='product-section__content product-section__content--padding']//ul[@class='list-table']/li[@class='list-table__row']/span[2]",
            ),
        );
    }

}
