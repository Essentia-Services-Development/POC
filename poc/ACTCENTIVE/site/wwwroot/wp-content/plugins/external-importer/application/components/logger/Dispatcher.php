<?php

namespace ExternalImporter\application\components\logger;

defined('\ABSPATH') || exit;

/**
 * Dispatcher class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class Dispatcher {

    public $targets = array();

    public function init()
    {
        foreach ($this->targets as $name => $target)
        {
            if (!is_object($target))
            {
                $this->targets[$name] = self::createTarget($target);
            }
        }
    }

    private function createTarget($target)
    {
        $class = __NAMESPACE__ . '\\' . $target['class'];
        unset($target['class']);

        $object = new $class;
        foreach ($target as $key => $value)
        {
            $object->$key = $value;
        }
        return $object;
    }

    public function dispatch($messages)
    {
        foreach ($this->targets as $target)
        {
            if (!$target->enabled)
                continue;
            try
            {
                $target->process($messages);
            } catch (\Exception $e)
            {
                //@TODO
                continue;
            }
        }
    }

}
