<?php

abstract class PeepSoMaintenanceFactory
{
    public function __construct()
    {
        // Otherwise filters / closures will refer to Factory class
        $that = get_class($this);
        $modules = get_class_methods($that);

        add_action(PeepSo::CRON_MAINTENANCE_EVENT, function() use ($modules, $that){

            if(count($modules)) {
                foreach ($modules as $module) {

                    if(strstr($module, '__')) {
                        continue;
                    }

                    $start = microtime(TRUE);
                    $count = $that::$module();

                    PeepSoMaintenanceDebug::get_instance()->debug("$that::$module", $start, microtime(TRUE), $count);
                }
            }
        });
    }
}