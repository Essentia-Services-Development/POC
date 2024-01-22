<?php

namespace Keywordrush\AffiliateEgg;
defined('\ABSPATH') || exit;

/**
 * Currency class file
 * 
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 * 
 */
class CurrencyHelper {

    private $locale;
    protected $currencies = array();
    protected $locales = array();
    private static $instance = null;

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
            ),
            'EUR' => array(
                'currency_symbol' => '&euro;',
                'currency_pos' => array(
                    'nl' => 'left',
                    'be' => 'left',
                    'de' => 'left',
                    'es' => 'right',
                    'fr' => 'right',
                    'it' => 'right',
                ),
                'thousand_sep' => '.',
                'decimal_sep' => ',',
                'num_decimals' => 2,
            ),
            'CAD' => array(
                'currency_symbol' => 'C $',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
            ),
            'GBP' => array(
                'currency_symbol' => '&pound;',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
            ),
            'JPY' => array(
                'currency_symbol' => '&yen;',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
            ),
            'CNY' => array(
                'currency_symbol' => '&yen;',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
            ),
            'RUB' => array(
                'currency_symbol' => 'руб.',
                'currency_pos' => 'right_space',
                'thousand_sep' => ' ',
                'decimal_sep' => ',',
                'num_decimals' => 0,
            ),
            'RUR' => array(
                'currency_symbol' => 'руб.',
                'currency_pos' => 'right_space',
                'thousand_sep' => ' ',
                'decimal_sep' => ',',
                'num_decimals' => 0,
            ),
            'UAH' => array(
                'currency_symbol' => 'грн.',
                'currency_pos' => 'right_space',
                'thousand_sep' => ' ',
                'decimal_sep' => ',',
                'num_decimals' => 0,
            ),
            'INR' => array(
                'currency_symbol' => 'Rs.',
                'currency_pos' => 'left_space',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 0,
            ),
            'AUD' => array(
                'currency_symbol' => 'AU $',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
            ),
            'VND' => array(
                'currency_symbol' => '&#8363;',
                'currency_pos' => 'right',
                'thousand_sep' => '.',
                'decimal_sep' => ',',
                'num_decimals' => 0,
            ),
            'BRL' => array(
                'currency_symbol' => 'R$',
                'currency_pos' => 'left',
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
                'currency_pos' => 'left',
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
                'currency_symbol' => 'جنيه',
                'currency_pos' => 'left_space',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
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
                'currency_symbol' => '$',
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
            'KES' => array(
                'currency_symbol' => 'KSh',
                'currency_pos' => 'left_space',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Kenyan Shilling',
            ),
            'HRK' => array(
                'currency_symbol' => 'kn',
                'currency_pos' => 'right_space',
                'thousand_sep' => '.',
                'decimal_sep' => ',',
                'num_decimals' => 2,
                'name' => 'Croatian Kuna',
            ),
            'PEN' => array(
                'currency_symbol' => 'S/',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Peruvian sol',
            ),
            'DOP' => array(
                'currency_symbol' => 'RD$',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Dominican Peso',
            ),
            'UYU' => array(
                'currency_symbol' => 'U$S',
                'currency_pos' => 'left',
                'thousand_sep' => '.',
                'decimal_sep' => ',',
                'num_decimals' => 2,
                'name' => 'Uruguayan Peso',
            ),
            'NIO' => array(
                'currency_symbol' => 'C$',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Nicaraguan Córdoba',
            ),
            'PAB' => array(
                'currency_symbol' => 'B/.',
                'currency_pos' => 'left',
                'thousand_sep' => '.',
                'decimal_sep' => ',',
                'num_decimals' => 2,
                'name' => 'Panamanian Balboa',
            ),
            'SVC' => array(
                'currency_symbol' => '$',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Salvadoran Colón',
            ),
            'GTQ' => array(
                'currency_symbol' => 'Q',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Guatemalan Quetzal',
            ),
            'HNL' => array(
                'currency_symbol' => 'L',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Honduran Lempira',
            ),
            'JMD' => array(
                'currency_symbol' => 'JM$',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Jamaican Dollar',
            ),
            'CRC' => array(
                'currency_symbol' => '₡',
                'currency_pos' => 'left',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'Costa Rican Colón',
            ),
            'ARS' => array(
                'currency_symbol' => '$',
                'currency_pos' => 'left',
                'thousand_sep' => '.',
                'decimal_sep' => ',',
                'num_decimals' => 2,
                'name' => 'Argentine Peso',
            ),
            'BOB' => array(
                'currency_symbol' => 'Bs',
                'currency_pos' => 'left',
                'thousand_sep' => '.',
                'decimal_sep' => ',',
                'num_decimals' => 2,
                'name' => 'Bolivian Boliviano',
            ),
            'COP' => array(
                'currency_symbol' => '$',
                'currency_pos' => 'left',
                'thousand_sep' => '.',
                'decimal_sep' => ',',
                'num_decimals' => 2,
                'name' => 'Colombian Peso',
            ),
            'XOF' => array(
                'currency_symbol' => 'FCFA',
                'currency_pos' => 'left_space',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 0,
                'name' => 'CFA Franc',
            ),
            'UMO' => array(
                'currency_symbol' => '$/mo',
                'currency_pos' => 'right_space',
                'thousand_sep' => ',',
                'decimal_sep' => '.',
                'num_decimals' => 2,
                'name' => 'USD/month',
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
        $symbol = $this->getSymbol($currency);
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

    public function numberFormat($number, $currency, $thousand_sep = null, $decimal_sep = null, $num_decimals = null)
    {
        if (!$thousand_sep)
            $thousand_sep = $this->getValue($currency, 'thousand_sep', ',');
        if (!$decimal_sep)
            $decimal_sep = $this->getValue($currency, 'decimal_sep', '.');
        if (!$num_decimals)
            $num_decimals = $this->getValue($currency, 'num_decimals', 2);
        return number_format((float) $number, absint($num_decimals), $decimal_sep, $thousand_sep);
    }

}
