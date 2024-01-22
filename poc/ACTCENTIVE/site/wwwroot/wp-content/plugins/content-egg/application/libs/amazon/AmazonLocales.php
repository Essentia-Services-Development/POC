<?php

namespace ContentEgg\application\libs\amazon;

defined('\ABSPATH') || exit;

/**
 * AmazonLocales class
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 * @link: https://webservices.amazon.com/paapi5/documentation/locale-reference.html
 * @link: https://webservices.amazon.com/paapi5/documentation/common-request-parameters.html#host-and-region
 *
 */
class AmazonLocales
{

    static public $locales = array(
        'au' => array(
            'Australia',
            'amazon.com.au',
            'webservices.amazon.com.au',
            'us-west-2',
            'AUD',
            'ws-fe',
        ),
        'br' => array(
            'Brazil',
            'amazon.com.br',
            'webservices.amazon.com.br',
            'us-east-1',
            'BRL',
            'ws-na',
        ),
        'ca' => array(
            'Canada',
            'amazon.ca',
            'webservices.amazon.ca',
            'us-east-1',
            'CAD',
            'ws-na',
        ),
        'eg' => array(
            'Egypt',
            'amazon.eg',
            'webservices.amazon.eg',
            'eu-west-1',
            'EGP',
            'ws-eu',
        ),
        'fr' => array(
            'France',
            'amazon.fr',
            'webservices.amazon.fr',
            'eu-west-1',
            'EUR',
            'ws-eu',
        ),
        'de' => array(
            'Germany',
            'amazon.de',
            'webservices.amazon.de',
            'eu-west-1',
            'EUR',
            'ws-eu',
        ),
        'in' => array(
            'India',
            'amazon.in',
            'webservices.amazon.in',
            'eu-west-1',
            'INR',
            'ws-eu',
        ),
        'it' => array(
            'Italy',
            'amazon.it',
            'webservices.amazon.it',
            'eu-west-1',
            'EUR',
            'ws-eu',
        ),
        'jp' => array(
            'Japan',
            'amazon.co.jp',
            'webservices.amazon.co.jp',
            'us-west-2',
            'JPY',
            'ws-fe',
        ),
        'mx' => array(
            'Mexico',
            'amazon.com.mx',
            'webservices.amazon.com.mx',
            'us-east-1',
            'MXN',
            'ws-na',
        ),
        'nl' => array(
            'Netherlands',
            'amazon.nl',
            'webservices.amazon.nl',
            'eu-west-1',
            'EUR',
            'ws-eu',
        ),
        'pl' => array(
            'Poland',
            'amazon.pl',
            'webservices.amazon.pl',
            'eu-west-1',
            'EUR',
            'ws-eu',
        ),
        'sg' => array(
            'Singapore',
            'amazon.sg',
            'webservices.amazon.sg',
            'us-west-2',
            'SGD',
            'ws-fe',
        ),
        'es' => array(
            'Spain',
            'amazon.es',
            'webservices.amazon.es',
            'eu-west-1',
            'EUR',
            'ws-eu',
        ),
        'se' => array(
            'Sweden',
            'amazon.se',
            'webservices.amazon.se',
            'eu-west-1',
            'EUR',
            'ws-eu',
        ),
        'tr' => array(
            'Turkey',
            'amazon.com.tr',
            'webservices.amazon.com.tr',
            'eu-west-1',
            'TRY',
            'ws-eu',
        ),
        'ae' => array(
            'United Arab Emirates',
            'amazon.ae',
            'webservices.amazon.ae',
            'eu-west-1',
            'AED',
            'ws-eu',
        ),
        'uk' => array(
            'United Kingdom',
            'amazon.co.uk',
            'webservices.amazon.co.uk',
            'eu-west-1',
            'GBP',
            'ws-eu',
        ),
        'us' => array(
            'United States',
            'amazon.com',
            'webservices.amazon.com',
            'us-east-1',
            'USD',
            'ws-na',
        ),
        'sa' => array(
            'Saudi Arabia',
            'amazon.sa',
            'webservices.amazon.sa',
            'eu-west-1',
            'SAR',
            'ws-eu',
        ),
        'be' => array(
            'Belgium',
            'amazon.com.be',
            'webservices.amazon.com.be',
            'eu-west-1',
            'EUR',
            'ws-eu',
        ),
    );

    static public function locales()
    {
        return self::$locales;
    }

    static public function getLocale($locale)
    {
        $locales = self::$locales;
        if (isset($locales[$locale]))
        {
            return $locales[$locale];
        } else
        {
            throw new \Exception("Locale {$locale} does not exist.");
        }
    }

    static public function getApiHost($locale)
    {
        $data = self::getLocale($locale);

        return $data[2];
    }

    static public function getAdsystemHost($locale)
    {
        $data = self::getLocale($locale);

        return $data[5] . '.amazon-adsystem.com';
    }

    static public function getApiEndpoint($locale)
    {
        return 'https://' . self::getApiHost($locale);
    }

    static public function getRegion($locale)
    {
        $data = self::getLocale($locale);

        return $data[3];
    }

    static public function getDomain($locale)
    {
        $data = self::getLocale($locale);

        return $data[1];
    }

    static public function getCurrencyCode($locale)
    {
        $data = self::getLocale($locale);

        return $data[4];
    }

    static public function getAdsystemEndpoint($locale)
    {
        return 'https://' . self::getAdsystemHost($locale);
    }

}
