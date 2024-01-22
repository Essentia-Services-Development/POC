<?php

namespace ExternalImporter\application\components;

defined('\ABSPATH') || exit;

use ExternalImporter\application\models\QueryModel;
use ExternalImporter\application\admin\ParserConfig;
use ExternalImporter\application\helpers\TextHelper;
use ExternalImporter\application\Plugin;

/**
 * Throttler class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class Throttler
{

    const ERRORS_COUNT_1HOUR = 3;
    const ERRORS_COUNT_24HOURS = 10;

    private static $throttled_domains = null;

    public static function getThrottledDomains()
    {
        if (self::$throttled_domains === null)
        {
            $domains = self::getThrottledByDailyLimit();
            $domains = array_merge($domains, self::getThrottledByErrors1());
            $domains = array_merge($domains, self::getThrottledByErrors24());
            $domains = array_values(array_unique($domains));
            self::$throttled_domains = $domains;
        }

        $throttled_domains = \apply_filters('ei_throttled_domains', self::$throttled_domains);

        return array_combine($throttled_domains, $throttled_domains);
    }

    public static function getThrottledByDailyLimit()
    {
        $daily_limit = (int) ParserConfig::getInstance()->option('daily_limit');
        if (!$daily_limit)
            return array();

        return QueryModel::model()->getDailyList($daily_limit);
    }

    public static function getThrottledByErrors1()
    {
        if (!ParserConfig::getInstance()->option('throttle_1'))
            return array();
        return QueryModel::model()->getErroredList(1, Throttler::ERRORS_COUNT_1HOUR);
    }

    public static function getThrottledByErrors24()
    {
        if (!ParserConfig::getInstance()->option('throttle_24'))
            return array();

        return QueryModel::model()->getErroredList(24, Throttler::ERRORS_COUNT_24HOURS);
    }

    public static function isThrottled($domain)
    {
        $throttled = self::getThrottledDomains();
        if (isset($throttled[$domain]))
            return true;
        else
            return false;
    }

    public static function isThrottledByUrl($url)
    {
        $domain = TextHelper::getHostName($url);
        return self::isThrottled($domain);
    }

    public static function addQueryLog($url, $error_code = 0)
    {
        $domain = TextHelper::getHostName($url);

        // Product data or listing URLs not found
        if ($error_code === 1)
            $error_code = 0;

        $throttledByDailyLimit = in_array($domain, self::getThrottledByDailyLimit()) ? true : false;
        $throttledByErrors1 = in_array($domain, self::getThrottledByErrors1()) ? true : false;
        $throttledByErrors24 = in_array($domain, self::getThrottledByErrors24()) ? true : false;

        $query = array(
            'domain' => $domain,
            'error_code' => (int) $error_code,
        );

        QueryModel::model()->save($query);

        if (!$throttledByDailyLimit && in_array($domain, self::getThrottledByDailyLimit()))
            Plugin::logger()->warning(__('Daily request limit reached.', 'external-importer') . ' ' . sprintf(__('All automatic requests to %s are throttled until the end of the day.', 'external-importer'), $domain));
        elseif (!$throttledByErrors1 && in_array($domain, self::getThrottledByErrors1()))
            Plugin::logger()->warning(sprintf(__('Errors occurred: %d.', 'external-importer'), Throttler::ERRORS_COUNT_1HOUR) . ' ' . sprintf(__('All automatic requests to %s are throttled to %d hour.', 'external-importer'), $domain, 1));
        elseif (!$throttledByErrors24 && in_array($domain, self::getThrottledByErrors24()))
            Plugin::logger()->warning(sprintf(__('Errors occurred: %d.', 'external-importer'), Throttler::ERRORS_COUNT_24HOURS) . ' ' . sprintf(__('All automatic requests to %s are throttled to %d hour.', 'external-importer'), $domain, 24));
    }
}
