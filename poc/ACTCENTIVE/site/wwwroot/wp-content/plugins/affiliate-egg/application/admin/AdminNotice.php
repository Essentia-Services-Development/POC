<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AdminNotice class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class AdminNotice {

    const GET_NOTICE_PARAM = 'affegg-notice';
    const GET_LEVEL_PARAM = 'affegg-notice-level';
    const GET_ID_PARAM = 'affegg-notice-id';

    protected static $instance = null;

    public function getMassages()
    {
        return array(
            'license_reset_error' => __('License can\'t be deactivated. Write to support of plugin.', 'affegg'),
            'license_reset_success' => __('The license has been deactivated.', 'external-importer') . ' ' . __('You must deactivate and delete Affiliate Egg from your current domain.', 'affegg'),
            
        );
    }

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct()
    {
        
    }

    public function adminInit()
    {
        \add_action('admin_notices', array($this, 'displayNotice'));
    }

    public function getMessage($message_id = null)
    {
        if (!$message_id && !empty($_GET[self::GET_NOTICE_PARAM]))
            $message_id = $_GET[self::GET_NOTICE_PARAM];
        else
            return '';

        $all = $this->getMassages();
        if (!array_key_exists($message_id, $all))
            return '';

        $message = $all[$message_id];

        if (!empty($_GET[self::GET_ID_PARAM]))
        {
            $id = (int) $_GET[self::GET_ID_PARAM];
            $message = str_replace('%%ID%%', $id, $message);
        }

        return $message;
    }

    public function displayNotice()
    {
        if (empty($_GET[self::GET_NOTICE_PARAM]))
            return;

        $level = 'info';
        if (!empty($_GET[self::GET_LEVEL_PARAM]))
        {
            $level = $_GET[self::GET_LEVEL_PARAM];
            if (!in_array($level, array('error', 'warning', 'info', 'success')))
                $level = 'info';
        }
        echo '<div class="notice notice-' . $level . ' is-dismissible"><p>' . $this->getMessage() . '</p></div>';
    }

    public static function add2Url($url, $message, $level = null, $id = null)
    {
        $url = add_query_arg(self::GET_NOTICE_PARAM, $message, $url);
        if ($level)
            $url = add_query_arg(self::GET_LEVEL_PARAM, $level, $url);
        if ($id)
            $url = add_query_arg(self::GET_ID_PARAM, $id, $url);
        return $url;
    }

}
