<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;

/**
 * JumiaAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class JumiaAdvanced extends AdvancedParser {

    public function getHttpOptions()
    {
        $options = parent::getHttpOptions();
        /**
         * Site scaping is permited IF the user-agent is clearly identify it as a bot and
         * the bot owner and is using less than 200 request per minute
         */
        $options['user-agent'] = 'Mozilla/5.0 (compatible; EIBot; +' . \get_home_url() . ')';
        return $options;
    }

    public function parseLinks()
    {
        $path = array(
            ".//article[@class='prd _fb col c-prd']/a/@href",
            ".//a[@class='link']/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@class='pg-w -pvxl']//a[contains(@href, 'page=')]/@href",
        );

        return $this->xpathArray($path);
    }
    
    public function parseDescription()
    {
        $paths = array(
            ".//div[@class='markup -mhm -pvl -oxa -sc']",
            ".//div[@class='card-b -fh']//div[@class='markup -pam']",
        );

        return $this->xpathScalar($paths, true);
    }   
    
    public function parseTitle()
    {
        $paths = array(
            ".//h1",
        );

        return $this->xpathScalar($paths);
    }    

    public function parseOldPrice()
    {
        $paths = array(
            ".//div/span[@data-price-old]",
            ".//*[@class='price-box']//*/@data-price)[2]",
            ".//*[@class='row card _no-g -fh -pas']//span[@data-price-old]",
        );

        return $this->xpathScalar($paths);
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name-value' => ".//div[@class='row -pas']//div[@class='markup -pam']/ul/li",
                'name-value' => ".//article[@class='col8 -pvs']//ul//li",
            ),
        );
    }

    public function getReviewsXpath()
    {
        return array(
            array(
                'review' => ".//section[@class='card aim -mtm']//p[@class='-pvs']",
                'rating' => ".//section[@class='card aim -mtm']//div[@class='stars _m _al -mvs']",
                'author' => ".//section[@class='card aim -mtm']//h3",
                'date' => ".//section[@class='card aim -mtm']//div[@class='-pvs']/span[@class='-prs']",
            ),
        );
    }

    public function afterParseFix(Product $product)
    {
        // redirect for outOfStock products
        if (!$product->price)
        {
            $product->inStock = false;
            $product->availability = null;
        }

        return $product;
    }

}
