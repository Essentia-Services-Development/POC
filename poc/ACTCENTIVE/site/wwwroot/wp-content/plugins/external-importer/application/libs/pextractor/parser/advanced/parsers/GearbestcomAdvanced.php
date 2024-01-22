<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;

/**
 * GearbestcomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class GearbestcomAdvanced extends AdvancedParser {

    protected $user_agent = 'ia_archiver';

    public function parseLinks()
    {
        $path = array(
            ".//p[@class='all_proNam']//a/@href",
            ".//p[@class='title']/a/@href",
            ".//a[contains(@class, 'gbGoodsItem_title')]/@href",
            ".//a[@class='js-titleLink']/@href",
            ".//*[@class='goodsItem_title']/a/@hreff",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@class='cateMain_pageList']//a[contains(@href, '.html')]/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseDescription()
    {
        $path = array(
            ".//p[@class='textDesc']",
            ".//div[@class='textDesc']//p[@class='textDescContent']",
            ".//*[@class='product_pz'][1]",
        );
        return $this->xpathScalar($path, true);
    }

    public function parseOldPrice()
    {
        if ($this->xpathScalar(".//meta[@property='og:price:currency']/@content") != 'USD')
            return;

        $paths = array(
            ".//del[@class='goodsIntro_shopPrice js-currency js-panelIntroShopPrice']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        $images = array();
        $results = $this->xpathArray(".//span[@id='js-goodsThumbnail']//img/@data-normal-src");
        foreach ($results as $img)
        {
            $img = str_replace('/goods_img_big-v2/', '/', $img);
            $images[] = $img;
        }
        return $images;
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//div[@class='sizeDesc js-sizeDescription']//div[@class='sizeDescCateContent']",
                'value' => ".//div[@class='sizeDesc js-sizeDescription']//div[@class='sizeDescContent']",
            ),
        );
    }

    public function getReviewsXpath()
    {
        return array(
            array(
                'review' => "//div[@id='js-reviewWrap']//dd",
                'rating' => ".//div[@id='js-reviewWrap']//*[@class='goodsReviews_item']//*[contains(@class, 'js-rating')]/@data-value",
                'author' => ".//div[@id='js-reviewWrap']//*[@class='goodsReviews_item']//*[@class='goodsReviews_itemUserName']",
                'date' => ".//div[@id='js-reviewWrap']//*[@class='goodsReviews_item']//*[@class='goodsReviews_itemTime']",
            ),
        );
    }

    public function afterParseFix(Product $product)
    {
        if (strstr($product->description, 'at cheap price online,'))
            $product->description = '';

        return $product;
    }

}
