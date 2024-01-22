<?php

namespace ExternalImporter\application;

defined('\ABSPATH') || exit;

use ExternalImporter\application\components\Scheduler;
use ExternalImporter\application\models\AutoimportModel;
use ExternalImporter\application\components\Autoimport;
use ExternalImporter\application\components\Throttler;

/**
 * AutoimportSheduler class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class AutoimportSheduler extends Scheduler {

    const CRON_TAG = 'ei_autoimport';
    const LIMIT_PER_RUN = 5;

    public static function getCronTag()
    {
        return self::CRON_TAG;
    }

    public static function run()
    {
        @set_time_limit(1200);

        $params = array(
            'select' => 'id',
            'where' => 'status = 1 AND (last_run IS NULL OR TIMESTAMPDIFF(SECOND, last_run, "' . \current_time('mysql') . '") >= recurrency)',
            'order' => 'last_run ASC',
            'limit' => self::LIMIT_PER_RUN
        );

        if ($throttled = Throttler::getThrottledDomains())
        {
            $throttled_sql = array_map(function($v) {
                return "'" . \esc_sql($v) . "'";
            }, $throttled);
            $throttled_sql = implode(',', $throttled_sql);
            $params['where'] .= ' AND domain NOT IN (' . $throttled_sql . ')';
        }

        $autoimports = AutoimportModel::model()->findAll($params);
        foreach ($autoimports as $autoimport)
        {
            Autoimport::run($autoimport['id']);
        }

        self::maybeClearScheduleEvent();
    }

    public static function maybeAddScheduleEvent()
    {
        if (self::totalActive())
            self::addScheduleEvent();
    }

    public static function maybeClearScheduleEvent()
    {
        if (!self::totalActive())
            self::clearScheduleEvent();
    }

    public static function totalActive()
    {
        return AutoimportModel::model()->count('status = 1');
    }

}
