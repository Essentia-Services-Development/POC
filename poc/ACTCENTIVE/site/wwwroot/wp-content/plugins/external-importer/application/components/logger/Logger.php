<?php

namespace ExternalImporter\application\components\logger;

defined('\ABSPATH') || exit;

/**
 * Logger class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class Logger {

    const LEVEL_DEBUG = 0;
    const LEVEL_ERROR = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_INFO = 3;

    public $messages = array();
    public $flushInterval = 30;
    private $dispatcher;
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new self;
            self::$instance->init();
        }

        return self::$instance;
    }

    public function init()
    {
        $this->dispatcher = new Dispatcher();
        $this->dispatcher->targets = array(
            'db' => [
                'class' => 'DbTarget',
                'levels' => array(Logger::LEVEL_DEBUG, Logger::LEVEL_ERROR, Logger::LEVEL_INFO, Logger::LEVEL_WARNING),
            ],
            'email' => [
                'class' => 'EmailTarget',
                'levels' => array(Logger::LEVEL_ERROR),
            ],
        );
        $this->dispatcher->init();

        register_shutdown_function(function () {
            $this->flush();
            register_shutdown_function(array($this, 'flush'), true);
        });
    }

    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    public function log($message, $level)
    {
        $this->messages[] = array($message, $level, microtime(true));
        if ($this->flushInterval > 0 && count($this->messages) >= $this->flushInterval)
            $this->flush();
    }

    public function debug($message)
    {
        $this->log($message, Logger::LEVEL_DEBUG);
    }

    public function warning($message)
    {
        $this->log($message, Logger::LEVEL_WARNING);
    }

    public function error($message)
    {
        $this->log($message, Logger::LEVEL_ERROR);
    }

    public function info($message)
    {
        $this->log($message, Logger::LEVEL_INFO);
    }

    public function flush($final = false)
    {
        $messages = $this->messages;
        $this->messages = array();
        if ($this->dispatcher instanceof Dispatcher)
            $this->dispatcher->dispatch($messages, $final);
    }

    public static function getLevels()
    {
        return array(
            self::LEVEL_ERROR => 'error',
            self::LEVEL_WARNING => 'warning',
            self::LEVEL_INFO => 'info',
            self::LEVEL_DEBUG => 'debug',
        );
    }

    public static function getLevel($id)
    {
        $levels = self::getLevels();
        if (isset($levels[$id]))
            return $levels[$id];
        else
            return 'unknown';
    }

}
