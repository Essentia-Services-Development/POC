<?php

class PeepSoConfigSectionNetwork extends PeepSoConfigSectionAbstract
{
    // Builds the groups array
    public function register_config_groups()
    {
        $this->context = 'full';

        $this->_group_ajax();

        if (defined('PEEPSO_SSE')) {
            $this->_group_sse();
        }


    }


    private function _group_sse()
    {
        $php_current = phpversion();
        
        // uncomment to emulate wrong php version
        //$php_current = '7.1.0';

        if(-1 == version_compare($php_current, PeepSoSystemRequirements::PHP_REQUIRED_SSE)) {

            $settings = PeepSoConfigSettings::get_instance();
            $settings->set_option('sse', 0);

            $this->set_field(
                'sse_php_version',
                sprintf(__('PeepSo Server Sent Events requires PHP %s or newer. You are using PHP %s.','peepso-core'), PeepSoSystemRequirements::PHP_REQUIRED_SSE, $php_current),
                'separator'
            );

        } else {

            // ENABLE
            $this->set_field(
                'sse',
                __('Enabled', 'peepso-core'),
                'yesno_switch'
            );

            // URL
            $this->args('descript', __('SSE endpoint URL to connect. If this value not set, PeepSo uses a default PHP endpoint with limited capability to handle this. Learn how to configure Node SSE endpoint <a href="#">here</a>.', 'peepso-core'));

            $this->set_field(
                'sse_backend_url',
                __('URL', 'peepso-core'),
                'text'
            );

            // DELAY
            $this->args('descript', __('minutes:seconds - lower number means more robust notifications, but also higher server load.', 'peepso-core'));
            $this->args('default', 5000);
            $options = array();
            // 00:01, 00:02, 00:03 ..  00:10
            for ($i = 1000; $i <= 10000; $i += 1000) {
                $options[$i] = $i;
            }

            // Format
            foreach ($options as $i) {
                $options[$i] = gmdate("i:s", $i / 1000);
            }

            $options[5000] = $options[5000] . ' (' . __('default', 'peepso-core') . ')';

            $this->args('options', $options);

            $this->set_field(
                'sse_backend_delay',
                __('Delay', 'peepso-core'),
                'select'
            );

            // TIMEOUT

            $this->args('descript', __('minutes:seconds - how long SSE is allowed to stay active without resetting connection. If your server is fine with a long execution time, use the higher values. This setting can be interfered by server timeouts.', 'peepso-core'));
            $this->args('default', 60000);

            $options = array();

            // 00:30, 01:00, 01:30 ... 05:00
            for ($i = 30000; $i <= 300000; $i += 30000) {
                $options[$i] = $i;
            }

            // Format
            foreach ($options as $i) {
                $options[$i] = gmdate("i:s", $i / 1000);
            }

            $options[30000] = $options[30000] . ' (' . __('default', 'peepso-core') . ')';

            $this->args('options', $options);

            $this->set_field(
                'sse_backend_timeout',
                __('Timeout', 'peepso-core'),
                'select'
            );

            // KEEPALIVE

            $options = array('0' => __('Never', 'peepso-core'));

            // 00:30, 01:00, 01:30 ... 05:00
            for ($i = 1; $i <= 50; $i++) {
                $options[$i] = sprintf(_n('Every %s loop', 'Every %s loops', $i, 'peepso-core'), $i);
            }

            $this->args('options', $options);
            $this->args('default', 5);
            $this->args('descript', __('If there is no activity, the server will send an empty ping once in a while to keep the connection alive. It is recommended to avoid reconnection due to low keep-alive timeouts. This setting does not generate load, but it can affect mobile phones battery life by keeping them constantly listening.', 'peepso-core'));

            $this->set_field(
                'sse_backend_keepalive',
                __('Send empty events to maintain connection', 'peepso-core'),
                'select'
            );
        }
        // Build Group
        $this->set_group(
            'sse',
            __('Server Sent Events', 'peepso-core'),
            __('Server Sent Events replace the Timed AJAX calls on compatible browsers.', 'peepso-core')
            . '<br>' . __('It limits the amount of resources required to maintain constant server-browser communication with all users, but it requires the server to be able to maintain an unusual amount of persistent connections.', 'peepso-core')
            . '<br><strong>' . __('This feature has special server requirements and requires careful configuration', 'peepso-core').'</strong>'
            . '<br/>' . __('Settings to consider include: PHP max_execution_time, Apache keep-alive timeout settings and more.', 'peepso-core')
            . '<br/>' . sprintf(__('Please refer to %s and consult your hosting provider.', 'peepso-core'), 'DOCSLINK')
        );
    }

    private function _group_ajax()
    {

        // DELAY MIN
        $this->args('descript', __('minutes:seconds - how often the calls are allowed to run if there is a related site activity', 'peepso-core'));
        $this->args('default', 30000);
        $options = array();

        // 00:01, 00:02, 00:03, 00:04
        for ($i = 1000; $i <= 4000; $i += 1000) {
            $options[$i] = $i;
        }
        // 00:05, 00:10, 00:15 ... 00:55
        for ($i = 5000; $i <= 55000; $i += 5000) {
            $options[$i] = $i;
        }

        // 01:00, 01:15, 01:30 ... 05:00
        for ($i = 60000; $i <= 300000; $i += 15000) {
            $options[$i] = $i;
        }

        // Format
        foreach ($options as $i) {
            $options[$i] = gmdate("i:s", $i / 1000);
        }

        // Default
        $options_min = $options;
        $options_min[5000] = $options_min[5000] . ' (' . __('default', 'peepso-core') . ')';

        $this->args('options', $options_min);

        $this->set_field(
            'notification_ajax_delay_min',
            __('Active', 'peepso-core'),
            'select'
        );

        // DELAY MAX
        $this->args('descript', __('minutes:seconds - how often the calls should be made, if the related site activity is idle', 'peepso-core'));
        $this->args('default', 5000);
        $options_max = $options;
        $options_max[30000] = $options_max[30000] . ' (' . __('default', 'peepso-core') . ')';


        // 10:00, 15:00 ... 30:00
        for ($i = 600000; $i <= 1800000; $i += 300000) {
            $options_max[$i] = gmdate("i:s", $i / 1000);
        }


        unset($options_max[1000]);
        unset($options_max[2000]);
        unset($options_max[3000]);
        unset($options_max[4000]);
        unset($options_max[5000]);


        $this->args('options', $options_max);

        $this->set_field(
            'notification_ajax_delay',
            __('Idle', 'peepso-core'),
            'select'
        );

        // DELAY MULTI
        $this->args('descript', __('If there is no related site activity, how quickly should the intensity shift from minimum/active to maximum/idle', 'peepso-core'));
        $this->args('default', '2.0');
        $options = array(
            '1.5' => '1.5 x',
            '2.0' => '2.0 x' . ' (' . __('default', 'peepso-core') . ')',
            '2.5' => '2.5 x',
            '3.0' => '3.0 x',
            '3.5' => '3.5 x',
            '4.0' => '4.0 x',
            '4.5' => '4.5 x',
            '5.0' => '5.0 x',
        );

        $this->args('options', $options);

        $this->set_field(
            'notification_ajax_delay_multiplier',
            __('Multiplier', 'peepso-core'),
            'select'
        );
        // Build Group
        $this->set_group(
            'ajax',
            __('Timed AJAX Calls', 'peepso-core'),
            __('PeepSo and all its plugins run various background (AJAX) calls for each user that is logged in.', 'peepso-core')
            . '<br>' . __('By adjusting the settings below you control how "instant" experience your users are having.', 'peepso-core')
            . '<br>' . __('<strong>Lower values mean more robust notifications, but also <u>higher server load.</u></strong>', 'peepso-core')
            . '<br>' . __('<strong><u>Values lower than defaults are not recommended.</u></strong>', 'peepso-core')
        );

    }
}