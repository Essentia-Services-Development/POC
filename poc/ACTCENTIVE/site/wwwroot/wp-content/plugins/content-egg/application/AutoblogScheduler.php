<?php

namespace ContentEgg\application;

defined('\ABSPATH') || exit;

use ContentEgg\application\models\AutoblogModel;
use ContentEgg\application\components\Scheduler;

/**
 * AutoblogScheduler class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class AutoblogScheduler extends Scheduler {

    const CRON_TAG = 'cegg_autoblog_cron';
    const AUTOBLOG_LIMIT = 5;

    public static function getCronTag()
    {
        return self::CRON_TAG;
    }

    public static function run()
    {
        @set_time_limit(1200);
        $params = array(
            'select' => 'id',
            'where' => 'status = 1 AND (last_run IS NULL OR TIMESTAMPDIFF(SECOND, last_run, "' . \current_time('mysql') . '") > run_frequency)',
            'order' => 'last_run  ASC',
            'limit' => self::AUTOBLOG_LIMIT
        );

        $autoblogs = AutoblogModel::model()->findAll($params);
        foreach ($autoblogs as $autoblog)
        {
            AutoblogModel::model()->run($autoblog['id']);
        }

        if (!AutoblogModel::isActiveAutoblogs())
            AutoblogScheduler::clearScheduleEvent();
    }

}
