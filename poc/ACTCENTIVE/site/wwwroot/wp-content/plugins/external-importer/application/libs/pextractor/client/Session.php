<?php

namespace ExternalImporter\application\libs\pextractor\client;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\ExtractorHelper;

/**
 * Session class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
class Session {

    const TRANSIENT_PREFIX = 'EI_SESSION_';
    const EXPIRATIONS_DAYS = 1;

    protected $domain;
    protected $transinetName;

    public function __construct($url)
    {
        $this->domain = ExtractorHelper::getHostName($url);
        $this->transinetName = $this->generateTransinetName();
    }

    protected function generateTransinetName()
    {
        return self::TRANSIENT_PREFIX . $this->domain;
    }

    public function save($user_agent, array $cookies)
    {
        if (!$data = $this->get())
            $data = array();

        $data['user-agent'] = $user_agent;
        $data['cookies'] = $cookies;

        \set_transient($this->transinetName, $data, 24 * 3600 * self::EXPIRATIONS_DAYS);
    }

    public function get()
    {
        return \get_transient($this->transinetName);
    }

    public function applay(array &$args)
    {
        if (!$data = $this->get())
            return;

        if (!empty($data['cookies']) && empty($args['cookies']))
            $args['cookies'] = $data['cookies'];
        if (!empty($data['user-agent']) && empty($args['user-agent']))
            $args['user-agent'] = $data['user-agent'];
    }

    public static function clearSessionVariables()
    {
        $transients = self::getTransientKeys();
        foreach ($transients as $transient)
        {
            \delete_transient($transient);
        }
    }

    private static function getTransientKeys()
    {
        global $wpdb;

        $prefix = '_transient_' . self::TRANSIENT_PREFIX;
        $sql = $wpdb->prepare('SELECT option_name FROM ' . $wpdb->options . ' WHERE option_name LIKE "%s"', $wpdb->esc_like($prefix) . '%');
        $keys = $wpdb->get_col($sql);
        foreach ($keys as $i => $value)
        {
            $keys[$i] = substr_replace($value, '', 0, strlen('_transient_'));
        }
        return $keys;
    }

}
