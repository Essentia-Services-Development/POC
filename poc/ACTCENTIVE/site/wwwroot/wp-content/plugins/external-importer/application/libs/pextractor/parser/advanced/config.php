<?php

/**
 * class.php file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
$config = array();

$amazon = array('amazon.com', 'amazon.de', 'amazon.it', 'amazon.fr', 'amazon.co.uk', 'amazon.in', 'amazon.es', 'amazon.ca', 'amazon.com.br', 'amazon.co.jp', 'amazon.com.au', 'amazon.ae', 'amazon.com.tr', 'amazon.sg', 'amazon.nl', 'amazon.sa', 'amazon.se', 'amazon.pl', 'amazon.eg', 'amazon.com.mx', 'amazon.com.be');
$config += array_fill_keys($amazon, array('class' => 'AmazonAdvanced', 'unstable' => true));

$ebay = array('ebay.com', 'ebay.de', 'ebay.in', 'ebay.com.au', 'ebay.it', 'ebay.co.uk', 'ebay.fr', 'ebay.es', 'ebay.nl', 'ebay.pl', 'ebay.sg', 'ebay.ca');
$config += array_fill_keys($ebay, array('class' => 'EbayAdvanced'));

$lazada = array('lazada.com.my', 'lazada.vn', 'lazada.co.id', 'lazada.com.ph', 'lazada.sg', 'lazada.co.th');
$config += array_fill_keys($lazada, array('class' => 'LazadaAdvanced', 'unstable' => true));

$jumia = array('jumia.com.eg', 'jumia.com.ng', 'jumia.co.ke', 'jumia.com.gh', 'jumia.ug', 'jumia.ci', 'jumia.ma', 'jumia.dz');
$config += array_fill_keys($jumia, array('class' => 'JumiaAdvanced'));

$wiggle = array('wiggle.com', 'wiggle.co.uk', 'wiggle.es', 'wiggle.fr', 'wiggle.co.nz', 'wiggle.com.au');
$config += array_fill_keys($wiggle, array('class' => 'WiggleAdvanced'));

$manomano = array('manomano.fr', 'manomano.it', 'manomano.de', 'manomano.es', 'manomano.co.uk');
$config += array_fill_keys($manomano, array('class' => 'ManomanoAdvanced'));

$shopee = array('shopee.vn', 'shopee.co.id', 'shopee.com.my', 'shopee.co.th', 'shopee.ph', 'shopee.sg', 'shopee.com.br', 'shopee.pl');
$config += array_fill_keys($shopee, array('class' => 'ShopeeAdvanced'));

$alternate = array('alternate.be', 'alternate.de', 'alternate.nl');
$config += array_fill_keys($alternate, array('class' => 'AlternateAdvanced'));

return array_merge($config,
        array(
            'americanas.com.br' => array('class' => 'AmericanascombrAdvanced'),
            'banggood.com' => array('class' => 'BanggoodcomAdvanced'),
            'bestbuy.com' => array('class' => 'BestbuycomAdvanced'),
            'bhphotovideo.com' => array('class' => 'BhphotovideocomAdvanced'),
            'bol.com' => array('class' => 'BolcomAdvanced'),
            'boulanger.com' => array('class' => 'BoulangercomAdvanced'),
            'cdkeys.com' => array('class' => 'CdkeyscomAdvanced'),
            'ceneo.pl' => array('class' => 'CeneoplAdvanced'),
            'coolblue.be' => array('class' => 'CoolblueAdvanced'),
            'coolblue.nl' => array('class' => 'CoolblueAdvanced'),
            'coolblue.de' => array('class' => 'CoolblueAdvanced'),
            'croma.com' => array('class' => 'CromacomAdvanced'),
            'darty.com' => array('class' => 'DartycomAdvanced'),
            'etsy.com' => array('class' => 'EtsycomAdvanced'),
            'flipkart.com' => array('class' => 'FlipkartcomAdvanced'),
            'fnac.be' => array('class' => 'FnacAdvanced'),
            'fnac.com' => array('class' => 'FnacAdvanced'),
            'g2a.com' => array('class' => 'G2acomAdvanced'),
            'gearbest.com' => array('class' => 'GearbestcomAdvanced'),
            'geekbuying.com' => array('class' => 'GeekbuyingcomAdvanced'),
            'homedepot.com' => array('class' => 'HomedepotcomAdvanced'),
            'hrkgame.com' => array('class' => 'HrkgamecomAdvanced'),
            'jollychic.com' => array('class' => 'JollychiccomAdvanced'),
            'konga.com' => array('class' => 'KongacomAdvanced'),
            'ldlc.com' => array('class' => 'LdlccomAdvanced'),
            'luluhypermarket.com' => array('class' => 'LuluhypermarketcomAdvanced'),
            'mercadolivre.com.br' => array('class' => 'MercadolivrecombrAdvanced'),
            'myntra.com' => array('class' => 'MyntracomAdvanced'),
            'newegg.com' => array('class' => 'NeweggcomAdvanced'),
            'noon.com' => array('class' => 'NooncomAdvanced'),
            'otto.de' => array('class' => 'OttodeAdvanced'),
            'overstock.com' => array('class' => 'OverstockcomAdvanced'),
            'rakuten.com' => array('class' => 'RakutencomAdvanced'),
            'redsea.com' => array('class' => 'RedseacomAdvanced'),
            'rozetka.com.ua' => array('class' => 'RozetkacomuaAdvanced'),
            'rueducommerce.fr' => array('class' => 'RueducommercefrAdvanced'),
            'sendo.vn' => array('class' => 'SendovnAdvanced'),
            'shopclues.com' => array('class' => 'ShopcluescomAdvanced'),
            'snapdeal.com' => array('class' => 'SnapdealcomAdvanced'),
            'souq.com' => array('class' => 'SouqcomAdvanced'),
            'target.com' => array('class' => 'TargetcomAdvanced'),
            'tigerfitness.com' => array('class' => 'TigerfitnesscomAdvanced'),
            'tiki.vn' => array('class' => 'TikivnAdvanced'),
            'walmart.com' => array('class' => 'WalmartcomAdvanced', 'unstable' => true),
            'wayfair.com' => array('class' => 'WayfaircomAdvanced'),
            'wehkamp.nl' => array('class' => 'WehkampnlAdvanced'),
            'xcite.com' => array('class' => 'XciteAdvanced'),
            'xcite.com.sa' => array('class' => 'XciteAdvanced'),
            'yandex.ru' => array('class' => 'YandexruAdvanced', 'unstable' => true),
            'aliexpress.com' => array('class' => 'AliexpressAdvanced', 'unstable' => true),
            'aliexpress.ru' => array('class' => 'AliexpressAdvanced', 'unstable' => true),
            'udemy.com' => array('class' => 'UdemycomAdvanced'),
            'fiverr.com' => array('class' => 'FiverrcomAdvanced', 'unstable' => true),
            'edx.org' => array('class' => 'EdxorgAdvanced'),
            'coursera.org' => array('class' => 'CourseraorgAdvanced'),
            'alibaba.com' => array('class' => 'AlibabacomAdvanced'),
        ));



