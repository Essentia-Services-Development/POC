<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\Plugin;
use ExternalImporter\application\admin\PluginAdmin;
use ExternalImporter\application\helpers\TextHelper;
use ExternalImporter\application\helpers\ParserHelper;
use ExternalImporter\application\libs\pextractor\parser\ParserFormat;
use ExternalImporter\application\helpers\InputHelper;

/**
 * DevController class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class DevController {

    const slug = 'external-importer-dev';

    private $stat = array();

    public function __construct()
    {
        if (!Plugin::isDevEnvironment())
            return;

        \add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu()
    {
        \add_submenu_page(Plugin::slug, __('Dev tools', 'external-importer') . ' &lsaquo; ' . Plugin::getName(), __('Dev tools', 'external-importer'), 'manage_options', self::slug, array($this, 'actionIndex'));
    }

    public function actionIndex()
    {
        $_POST = array_map('stripslashes_deep', $_POST);
        if (!empty($_POST['nonce']) && \wp_verify_nonce($_POST['nonce'], basename(__FILE__)))
        {

            $product_urls = InputHelper::post('product_urls', array());
            $listing_urls = InputHelper::post('listing_urls', array());

            $this->test(self::prepareUrls($product_urls), self::prepareUrls($listing_urls));
        }

        PluginAdmin::getInstance()->render('dev_index', array('nonce' => \wp_create_nonce(basename(__FILE__))));
    }

    private function test(array $product_urls = array(), array $listing_urls = array())
    {
        @set_time_limit(1800);

        $this->testProductUrls($product_urls);
        $this->testListingUrls($listing_urls);
        $this->printStat();
    }

    private function testProductUrls(array $product_urls)
    {
        foreach ($product_urls as $url)
        {
            $domain = TextHelper::getHostName($url);
            if (isset($this->stat[$domain]['product']))
                continue;

            if (!isset($this->stat[$domain]))
                $this->stat[$domain] = array();

            try
            {
                $product = ParserHelper::parseProduct($url, true, ParserFormat::ALL_PRODUCT_AUTO);

                if (!$product->title || !$product->price || !$product->image)
                    throw new \Exception('Incomplete product data', 1);
            } catch (\Exception $e)
            {
                $error_code = $e->getCode();
                $error = $e->getMessage();

                if ($error_code === 1)
                    $this->stat[$domain]['product'] = false; // product data not found
                else
                    $this->stat[$domain]['product'] = null; // http error

                continue;
            }

            $this->stat[$domain]['product'] = true;
            $this->stat[$domain]['product_parsers'] = ParserHelper::getLastExtractor()->getLastUsedParsers();
        }
    }

    private function testListingUrls(array $listing_urls)
    {
        foreach ($listing_urls as $url)
        {
            $domain = TextHelper::getHostName($url);
            if (isset($this->stat[$domain]['listing']))
                continue;

            if (!isset($this->stat[$domain]))
                $this->stat[$domain] = array();

            try
            {
                $listing = ParserHelper::parseListing($url, ParserFormat::ALL_LISTING_AUTO);
            } catch (\Exception $e)
            {
                $error_code = $e->getCode();
                $error = $e->getMessage();

                if ($error_code === 1)
                    $this->stat[$domain]['listing'] = false; // products not found
                else
                    $this->stat[$domain]['listing'] = null; // http error

                continue;
            }

            $this->stat[$domain]['listing'] = true;
            $this->stat[$domain]['listing_parsers'] = ParserHelper::getLastExtractor()->getLastUsedParsers();
        }
    }

    private function printStat()
    {
        $product_http_errors = 0;
        $product_total = 0;
        $product_success = 0;
        $product_errors = 0;

        $listing_http_errors = 0;
        $listing_total = 0;
        $listing_success = 0;
        $listing_errors = 0;

        $product_listing_total = 0;
        $product_listing_success = 0;


        ksort($this->stat);

        foreach ($this->stat as $domain => $s)
        {
            if (array_key_exists('product', $s))
            {
                $product_total++;
                if ($s['product'] === true)
                    $product_success++;
                elseif ($s['product'] === null)
                    $product_http_errors++;
                elseif ($s['product'] === false)
                    $product_errors++;
            }
            if (array_key_exists('listing', $s))
            {
                $listing_total++;
                if ($s['listing'] === true)
                    $listing_success++;
                elseif ($s['listing'] === null)
                    $listing_http_errors++;
                elseif ($s['listing'] === false)
                    $listing_errors++;
            }

            if (isset($s['product']) && isset($s['listing']))
            {
                $product_listing_total++;
                if ($s['product'] && $s['listing'])
                    $product_listing_success++;
            }
        }

        echo '<div class="row"><div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">';
        echo '<ul>';
        echo sprintf('<li>Total product URLs tested: %d</li>', $product_total);
        echo sprintf('<li>HTTP errors: %d</li>', $product_http_errors);
        echo sprintf('<li>HTTP success: %d</li>', $product_total - $product_http_errors);
        echo sprintf('<li class="c-green">Parsed successfully: %d ', $product_success);
        if ($product_total - $product_http_errors)
            echo ' (' . round($product_success * 100 / ($product_total - $product_http_errors)) . '%)';
        echo '</li>';
        echo sprintf('<li>Structured data not found: %d</li>', $product_errors);

        echo '</ul>';
        echo '</div><div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">';
        echo '<ul>';

        echo sprintf('<li>Total listing URLs tested: %d</li>', $listing_total);
        echo sprintf('<li>HTTP errors: %d</li>', $listing_http_errors);
        echo sprintf('<li>HTTP success: %d</li>', $listing_total - $listing_http_errors);
        echo sprintf('<li class="c-green">Parsed successfully: %d ', $listing_success);
        if ($listing_total - $listing_http_errors)
            echo ' (' . round($listing_success * 100 / ($listing_total - $listing_http_errors)) . '%)';
        echo '</li>';
        echo sprintf('<li>Listing data not found: %d</li>', $listing_errors);

        echo '</ul>';
        echo '</div><div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">';
        echo '<ul>';

        echo sprintf('<li>Total product+listing: %d</li>', $product_listing_total);
        echo sprintf('<li class="c-green">Product+listing parsed successfully: %d ', $product_listing_success);
        if ($product_listing_total)
            echo ' (' . round($product_listing_success * 100 / $product_listing_total) . '%)';
        echo '</li>';
        echo '</ul>';

        echo '</div></div>';

        echo '<table class="table table-bordered table-sm"><tr><th>Domain</th><th>Product parsing</th><th>Used parsers (product)</th><th>Listing parsing</th><th>Used parsers (listing)</th></tr>';


        foreach ($this->stat as $domain => $s)
        {
            echo '<tr>';
            echo '<td class="c-primary">' . \esc_html($domain) . '</td>';
            if (array_key_exists('product', $s))
            {
                if ($s['product'] === true)
                    echo '<td class="success">Product - Success</td>';
                elseif ($s['product'] === null)
                    echo '<td style="background-color:#F5A9A9;">HTTP Error</td>';
                elseif ($s['product'] === false)
                    echo '<td style="background-color:#BDBDBD">Product data not found</td>';
                if (isset($s['product_parsers']))
                    echo '<td>' . \esc_html(join(', ', $s['product_parsers'])) . '</td>';
                else
                    echo '<td>&nbsp;</td>';
            } else
                echo '<td colspan="2">&nbsp;</td>';


            if (array_key_exists('listing', $s))
            {
                if ($s['listing'] === true)
                    echo '<td class="success">Listing - Success</td>';
                elseif ($s['listing'] === null)
                    echo '<td style="background-color:#F5A9A9;">HTTP Error</td>';
                elseif ($s['listing'] === false)
                    echo '<td style="background-color:#BDBDBD">Listing data not found</td>';
                if (isset($s['listing_parsers']))
                    echo '<td>' . \esc_html(join(', ', $s['listing_parsers'])) . '</td>';
                else
                    echo '<td>&nbsp;</td>';
            } else
                echo '<td colspan="2">&nbsp;</td>';
            echo '</tr>';
        }
        echo '</table>';

        exit;
    }

    private static function prepareUrls($urls)
    {
        $urls = explode("\r", $urls);
        $urls = array_map('trim', $urls);
        $urls = array_filter($urls);
        $urls = array_unique($urls);

        $results = array();
        foreach ($urls as $url)
        {
            if (TextHelper::isValidUrl($url))
                $results[] = $url;
        }
        return $results;
    }

}
