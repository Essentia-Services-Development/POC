<?php

namespace ExternalImporter\application\components\logger;

defined('\ABSPATH') || exit;

use ExternalImporter\application\Plugin;
use ExternalImporter\application\helpers\EmailHelper;

/**
 * EmailTarget class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class EmailTarget extends Target {

    public $config = array();

    public function export()
    {
        if (empty($this->config['to']))
            throw new \Exception('The "to" config must be set for EmailTarget.');

        $to = $this->config['to'];
        $domain = preg_replace('/^https?:\/\//', '', \get_home_url());
        $subject = Plugin::getName() . ' ' . __('alerts', 'external-importer') . ' - ' . $domain;
        $message = $this->formatMessages($this->messages);

        if (!EmailHelper::mail($to, $subject, $message))
            throw new \Exception('Logging error: couldnt send log by email.');
    }

}
