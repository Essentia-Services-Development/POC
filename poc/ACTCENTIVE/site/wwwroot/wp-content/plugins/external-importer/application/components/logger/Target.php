<?php

namespace ExternalImporter\application\components\logger;

defined('\ABSPATH') || exit;

/**
 * Target class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
abstract class Target {

    public $messages = array();
    public $levels = array();
    public $enabled = true;

    abstract public function export();

    public function process($messages)
    {
        $this->messages = $this->filter($messages);
        if (!$this->messages)
            return;

        $this->export();
        $this->messages = array();
    }

    private function filter($messages)
    {
        foreach ($messages as $key => $message)
        {
            if ($this->levels && !in_array($message[1], $this->levels))
            {
                unset($messages[$key]);
                continue;
            }
        }
        return $messages;
    }

    protected function formatMessage(array $log)
    {
        list ($message, $log_level, $log_time) = $log;
        return '[' . self::formatDateTime($log_time) . ']' . " " . Logger::getLevel($log_level) . "   " . $message;
    }

    protected function formatMessages(array $logs)
    {
        $results = array();
        foreach ($logs as $log)
        {
            $results[] = Target::formatMessage($log);
        }
        return join("\r\n", $results);
    }

    public static function formatDateTime($microtime)
    {
        return date('Y-m-d H:i:s', (int) $microtime) . ' UTC';
    }

}
