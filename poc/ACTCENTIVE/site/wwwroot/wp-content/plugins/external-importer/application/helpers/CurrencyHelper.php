<?php

namespace ExternalImporter\application\helpers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\Plugin;

/**
 * Currency class file
 * 
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 * 
 */
class CurrencyHelper {

    private $locale;
    protected $currencies = array();
    protected $locales = array();
    private static $instance = null;
    private static $currencyRates = array();

    public static function getInstance($locale = null)
    {
        if (self::$instance === null)
        {
            self::$instance = new CurrencyHelper($locale);
        }
        return self::$instance;
    }

    private function __construct($locale)
    {
        $this->setLocale($locale);
        $this->currencies = self::currencies();
        $this->locales = self::locales();
    }

    public static function locales()
    {
        return array(
            'en' => array(
                'thousand_sep' => ',',
                'decimal_sep' => '.',
            ),
            'nl' => array(
                'thousand_sep' => '.',
                'decimal_sep' => ',',
            ),
            'be' => array(
                'thousand_sep' => ' ',
                'decimal_sep' => ',',
            ),
            'de' => array(
                'thousand_sep' => '.',
                'decimal_sep' => ',',
            ),
            'es' => array(
                'thousand_sep' => '.',
                'decimal_sep' => ',',
            ),
            'fr' => array(
                'thousand_sep' => ' ',
                'decimal_sep' => ',',
            ),
            'it' => array(
                'thousand_sep' => '.',
                'decimal_sep' => ',',
            ),
            'ru' => array(
                'thousand_sep' => ' ',
                'decimal_sep' => ',',
            ),
            'uk' => array(
                'thousand_sep' => ' ',
                'decimal_sep' => ',',
            ),
        );
    }

    public static function currencies()
    {
        return array(
            'USD' => array(
                'currency_symbol' => '$',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'United States dollar',
            ),
            'EUR' => array(
                'currency_symbol' => '&euro;',
                'currency_pos' => array(
                    'nl' => 'left',
                    'be' => 'left',
                    'de' => 'right',
                    'es' => 'right',
                    'fr' => 'right',
                    'it' => 'right',
                ),
                'thousand_sep' => '.',
                'decimal_sep' => ',',
                'num_decimals' => 2,
                'name' => 'Euro',
            ),
            'CAD' => array(
                'currency_symbol' => 'C $',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Canadian dollar',
            ),
            'GBP' => array(
                'currency_symbol' => '&pound;',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'British pound',
            ),
            'JPY' => array(
                'currency_symbol' => '&yen;',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 0,
                'name' => 'Japanese yen',
            ),
            'CNY' => array(
                'currency_symbol' => '&yen;',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Chinese yuan',
            ),
            'RUB' => array(
                'currency_symbol' => 'руб.',
                'currency_pos' => 'right_space',
                'thousand_sep' => ' ',
                'decimal_sep' => ',',
                'num_decimals' => 0,
                'name' => 'Russian ruble',
            ),
            'RUR' => array(
                'currency_symbol' => 'руб.',
                'currency_pos' => 'right_space',
                'thousand_sep' => ' ',
                'decimal_sep' => ',',
                'num_decimals' => 0,
                'name' => 'Russian ruble',
            ),
            'UAH' => array(
                'currency_symbol' => 'грн.',
                'currency_pos' => 'right_space',
                'thousand_sep' => ' ',
                'decimal_sep' => ',',
                'num_decimals' => 0,
                'name' => 'Ukrainian hryvnia',
            ),
            'INR' => array(
                'currency_symbol' => 'Rs.',
                'currency_pos' => 'left_space',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 0,
                'name' => 'Indian Rupee',
            ),
            'AUD' => array(
                'currency_symbol' => 'AU $',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Australian dollar',
            ),
            'VND' => array(
                'currency_symbol' => '&#8363;',
                'currency_pos' => 'right',
                'thousand_sep' => '.',
                'decimal_sep' => ',',
                'num_decimals' => 0,
                'name' => 'Vietnamese dong',
            ),
            'BRL' => array(
                'currency_symbol' => 'R$',
                'currency_pos' => 'left_space',
                'thousand_sep' => '.',
                'decimal_sep' => ',',
                'num_decimals' => 2,
                'name' => 'Brazilian real',
            ),
            'TND' => array(
                'currency_symbol' => 'DT',
                'currency_pos' => 'right',
                'thousand_sep' => ' ',
                'decimal_sep' => '.',
                'num_decimals' => 3,
                'name' => 'Tunisian dinar',
            ),
            'NGN' => array(
                'currency_symbol' => '₦',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Nigerian naira',
            ),
            'MXN' => array(
                'currency_symbol' => '$',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Mexican peso',
            ),
            'MDL' => array(
                'currency_symbol' => 'lei',
                'currency_pos' => 'right_space',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Moldovan leu',
            ),
            'KRW' => array(
                'currency_symbol' => '₩',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 0,
                'name' => 'South Korean won',
            ),
            'THB' => array(
                'currency_symbol' => '฿',
                'currency_pos' => 'left_space',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 0,
                'name' => 'Thai baht',
            ),
            'RON' => array(
                'currency_symbol' => 'Lei',
                'currency_pos' => 'right_space',
                'thousand_sep' => '.',
                'decimal_sep' => ',',
                'num_decimals' => 2,
                'name' => 'Romanian Leu',
            ),
            'EGP' => array(
                'currency_symbol' => 'EGP',
                'currency_pos' => 'right_space',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 0,
                'name' => 'Egypt Pound',
            ),
            'KWD' => array(
                'currency_symbol' => 'KD',
                'currency_pos' => 'right_space',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 3,
                'name' => 'Kuwaiti dinar',
            ),
            'TRY' => array(
                'currency_symbol' => 'TL',
                'currency_pos' => 'right_space',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Turkish Lira',
            ),
            'IDR' => array(
                'currency_symbol' => 'Rp',
                'currency_pos' => 'left_space',
                'thousand_sep' => '.',
                'decimal_sep' => ',',
                'num_decimals' => 0,
                'name' => 'Indonesian Rupiah',
            ),
            'PKR' => array(
                'currency_symbol' => 'PKR.',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 0,
                'name' => 'Pakistani Rupee',
            ),
            'HKD' => array(
                'currency_symbol' => 'HKD$',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Hong Kong dollar',
            ),
            'ILS' => array(
                'currency_symbol' => '&#8362;',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Israeli Shekel',
            ),
            'AED' => array(
                'currency_symbol' => 'AED',
                'currency_pos' => 'right_space',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'UAE Dirham',
            ),
            'SAR' => array(
                'currency_symbol' => 'SAR',
                'currency_pos' => 'right_space',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Saudi Riyal',
            ),
            'SGD' => array(
                'currency_symbol' => 'S$',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Singapore dollar',
            ),
            'HUF' => array(
                'currency_symbol' => 'Ft',
                'currency_pos' => 'right_space',
                'thousand_sep' => '.',
                'decimal_sep' => ',',
                'num_decimals' => 0,
                'name' => 'Hungarian forint',
            ),
            'PLN' => array(
                'currency_symbol' => 'zł',
                'currency_pos' => 'right_space',
                'thousand_sep' => '',
                'decimal_sep' => ',',
                'num_decimals' => 2,
                'name' => 'Polish Zloty',
            ),
            'CZK' => array(
                'currency_symbol' => 'Kč',
                'currency_pos' => 'right_space',
                'thousand_sep' => ' ',
                'decimal_sep' => ',',
                'num_decimals' => 0,
                'name' => 'Czech koruna',
            ),
            'MYR' => array(
                'currency_symbol' => 'RM',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Malaysia Ringgit',
            ),
            'PCT' => array(
                'currency_symbol' => '%',
                'currency_pos' => 'right',
                'thousand_sep' => '',
                'decimal_sep' => '.',
                'num_decimals' => 1,
                'name' => 'Percentage',
            ),
            'CLP' => array(
                'currency_symbol' => '$',
                'currency_pos' => 'left',
                'thousand_sep' => '.',
                'decimal_sep' => ',',
                'num_decimals' => 0,
                'name' => 'Peso Chileno',
            ),
            'DKK' => array(
                'currency_symbol' => 'DKK',
                'currency_pos' => 'left_space',
                'thousand_sep' => '.',
                'decimal_sep' => ',',
                'num_decimals' => 2,
                'name' => 'Danske Kroner',
            ),
        );
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    private function getValue($currency, $key, $default = null)
    {
        if (isset($this->currencies[$currency]) && isset($this->currencies[$currency][$key]))
            $value = $this->currencies[$currency][$key];
        else
            $value = null;

        if (!is_null($value) && is_scalar($value) && $currency == 'MXN')
            return $value;

        if (is_array($value) && isset($value[$this->locale]))
            return $value[$this->locale];
        elseif (isset($this->locales[$this->locale]) && isset($this->locales[$this->locale][$key]))
            return $this->locales[$this->locale][$key];
        elseif (is_array($value))
            return reset($value); // first value
        elseif (is_scalar($value) && !is_null($value))
            return $value;
        else
            return $default;
    }

    public function currencyFormat($amount, $currency, $thousand_sep = null, $decimal_sep = null, $before_symbol = '', $after_symbol = '')
    {
        $amount = $this->numberFormat($amount, $currency, $thousand_sep, $decimal_sep);
        $symbol = \apply_filters('ei_currency_symbol', $this->getSymbol($currency), $currency);
        $currency_pos = $this->getCurrencyPos($currency);
        $symbol = $before_symbol . $symbol . $after_symbol;
        switch ($currency_pos)
        {
            case 'left_space':
                return $symbol . ' ' . $amount;
            case 'left':
                return $symbol . $amount;
            case 'right_space':
                return $amount . ' ' . $symbol;
            case 'right':
                return $amount . $symbol;
            default:
                return $symbol . ' ' . $amount;
        }
    }

    public function getCurrencyPos($currency, $default = 'left_space')
    {
        return $this->getValue($currency, 'currency_pos', $default);
    }

    public function getSymbol($currency)
    {
        return $this->getValue($currency, 'currency_symbol', $currency);
    }

    public function getName($currency)
    {
        return $this->getValue($currency, 'name', $currency);
    }

    public function numberFormat($number, $currency, $thousand_sep = null, $decimal_sep = null, $num_decimals = null)
    {
        if ($thousand_sep === null)
            $thousand_sep = $this->getValue($currency, 'thousand_sep', ',');
        if (!$decimal_sep)
            $decimal_sep = $this->getValue($currency, 'decimal_sep', '.');
        if (!$num_decimals)
            $num_decimals = $this->getValue($currency, 'num_decimals', 2);
        return number_format((float) $number, absint($num_decimals), $decimal_sep, $thousand_sep);
    }

    public static function getCurrenciesList()
    {
        $list = array_keys(self::currencies());
        sort($list);
        return $list;
    }

    /**
     * @link: http://www.ecb.europa.eu/stats/policy_and_exchange_rates/euro_reference_exchange_rates/html/index.en.html#dev
     */
    public static function queryCurrencyRateEcb($from, $to, $force = false)
    {
        $transient_name = 'ei-currency-rates-ecb';
        $rates = \get_transient($transient_name);

        if ($rates === false || $force)
        {
            $url = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
            $params = array(
                'timeout' => 10,
                'user-agent' => Plugin::getName() . '(' . Plugin::getWebsite() . ')',
            );
            $response = \wp_remote_get($url, $params);
            $rates = array();
            if ($response && !\is_wp_error($response))
            {
                $results = TextHelper::unserialize_xml(\wp_remote_retrieve_body($response));
                if (!isset($results['Cube']['Cube']['Cube']))
                    return 0;
                foreach ($results['Cube']['Cube']['Cube'] as $r)
                {
                    $rates[$r['@attributes']['currency']] = (float) $r['@attributes']['rate'];
                }
            }
            \set_transient($transient_name, $rates, 6 * 3600);
        }

        if ($from == 'EUR' && isset($rates[$to]))
            return $rates[$to];
        elseif ($to == 'EUR' && isset($rates[$from]))
            return 1 / $rates[$from];
        elseif (isset($rates[$from]) && isset($rates[$to]))
            return $rates[$to] / $rates[$from];
        else
            return 0;
    }

    /**
     * @link: https://fixer.io/documentation
     */
    public static function queryCurrencyRateFixer($from, $to, $force = false)
    {
        if (!$api_key = \ExternalImporter\application\admin\GeneralConfig::getInstance()->option('fixer_api_key'))
            return 0;

        $transient_name = 'ei-currency-rates-fixer';
        $rates = \get_transient($transient_name);

        if ($rates === false || $force)
        {
            $url = 'http://data.fixer.io/api/latest';

            $data = array();
            $data['access_key'] = $api_key;
            $url .= '?' . http_build_query($data);

            $params = array(
                'timeout' => 10,
                'user-agent' => Plugin::getName() . '(' . Plugin::getWebsite() . ')',
            );
            $response = \wp_remote_get($url, $params);
            $rates = array();
            if ($response && !\is_wp_error($response))
            {
                $results = json_decode(\wp_remote_retrieve_body($response), true);
                if (!$results || !isset($results['rates']))
                    return 0;

                foreach ($results['rates'] as $currency => $rate)
                {
                    $rates[$currency] = (float) $rate;
                }
            }
            \set_transient($transient_name, $rates, 6 * 3600);
        }

        if ($from == 'EUR' && isset($rates[$to]))
            return $rates[$to];
        elseif ($to == 'EUR' && isset($rates[$from]))
            return 1 / $rates[$from];
        elseif (isset($rates[$from]) && isset($rates[$to]))
            return $rates[$to] / $rates[$from];
        else
            return 0;
    }

    public static function queryCurrencyRate($from, $to)
    {
        if ($rates = self::queryCurrencyRateFixer($from, $to))
            return $rates;
        else
            return self::queryCurrencyRateEcb($from, $to);
    }

    public static function getCurrencyRate($from, $to)
    {
        if ($from == 'RUR')
            $from = 'RUB';
        if ($to == 'RUR')
            $to = 'RUB';

        if ($rate = \apply_filters('ei_currency_rate', 0, $from, $to))
            return $rate;

        $transient_name = 'ei-currency-rate-' . $from . $to;
        if (!isset(self::$currencyRates[$transient_name]))
        {
            $rate = \get_transient($transient_name);
            if ($rate === false)
            {
                $rate = self::queryCurrencyRate($from, $to);
                \set_transient($transient_name, $rate, 24 * 3600);
            }
            self::$currencyRates[$transient_name] = $rate;
        }
        return self::$currencyRates[$transient_name];
    }

    public static function roundUp($value, $decimals = 2)
    {
        $pow = pow(10, $decimals);
        return (ceil($pow * $value) + ceil($pow * $value - ceil($pow * $value))) / $pow;
    }

    public static function roundDown($value, $decimals = 2)
    {
        return floor($value * pow(10, $decimals)) / pow(10, $decimals);
    }

    public static function ceiling($number, $significance = 1)
    {
        return ceil($number / $significance) * $significance;
    }

}
