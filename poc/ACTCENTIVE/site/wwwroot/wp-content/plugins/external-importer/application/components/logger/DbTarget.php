<?php

namespace ExternalImporter\application\components\logger;

defined('\ABSPATH') || exit;

use ExternalImporter\application\models\LogModel;

/**
 * DbTarget class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class DbTarget extends Target {

    public function export()
    {
        foreach ($this->messages as $message)
        {
            $log = array(
                'id' => null,
                'message' => $message[0],
                'log_level' => $message[1],
                'log_time' => $message[2],
            );

            if (!LogModel::model()->save($log))
                throw new \Exception('Logging error: couldnt save log to table.');
        }

        if (rand(1, 10) == 1)
            LogModel::model()->cleanOldLogs();
    }

}
