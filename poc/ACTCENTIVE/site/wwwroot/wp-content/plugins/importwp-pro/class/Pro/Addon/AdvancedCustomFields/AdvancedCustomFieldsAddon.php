<?php

namespace ImportWP\Pro\Addon\AdvancedCustomFields;

use ImportWP\Pro\Addon\AdvancedCustomFields\Exporter\Mapper\PostMapper;
use ImportWP\Pro\Addon\AdvancedCustomFields\Exporter\Mapper\TaxMapper;
use ImportWP\Pro\Addon\AdvancedCustomFields\Exporter\Mapper\UserMapper;
use ImportWP\Pro\Addon\AdvancedCustomFields\Importer\Importer;

class AdvancedCustomFieldsAddon
{
    /**
     * @param EventHandler $event_handler 
     * @return void 
     */
    public function __construct($event_handler)
    {
        // Dont initialize if acf is not installed
        if (!class_exists('ACF')) {
            return;
        }

        $this->setup_importer($event_handler);
        $this->setup_exporter($event_handler);

        /**
         * Change custom field key to acf field key
         */
        add_filter('iwp/custom_field_key', [$this, 'get_custom_field_name'], 10);

        /**
         * Change custom field log message output to readable field label
         */
        add_filter('iwp/custom_field_label', [$this, 'get_custom_field_label'], 10);

        /**
         * Deactivate existing importwp/advanced-custom-fields plugin
         */
        add_action('activated_plugin', [$this, 'deactivate_acf_plugin']);
        add_action('pre_current_active_plugins', [$this, 'deactivate_acf_plugin_notice']);

        /**
         * Add ACF and ACF Pro plugin to compatability whitelist
         */
        add_filter('iwp/compat/whitelist', [$this, 'update_compat_whitelist']);
    }

    public function setup_importer($event_handler)
    {
        new Importer($event_handler);
    }

    public function setup_exporter($event_handler)
    {
        new PostMapper($event_handler);
        new TaxMapper($event_handler);
        new UserMapper($event_handler);
    }

    /**
     * Get field label from acf_field::(text|attachment)::field_key
     * @param string $key 
     * @return string 
     */
    public function get_custom_field_label($key)
    {
        $key = $this->get_custom_field_key($key);
        $field = get_field_object($key);
        if ($field) {
            return $field['label'];
        }
        return $key;
    }

    /**
     * Get field label from acf_field::(text|attachment)::field_key
     * @param string $key 
     * @return string 
     */
    public function get_custom_field_name($key)
    {
        $key = $this->get_custom_field_key($key);
        $field = get_field_object($key);
        if ($field) {
            return $field['name'];
        }
        return $key;
    }

    /**
     * Get acf field key from acf_field::(text|attachment)::field_key
     * @param string $key 
     * @return string 
     */
    public function get_custom_field_key($key)
    {
        if (!$this->key_contains_prefix($key)) {
            return $key;
        }

        if (!strpos($key, '::')) {
            return $key;
        }

        $field_key = substr($key, strrpos($key, '::') + strlen('::'));
        $matches = [];
        if (preg_match('/^([^-]+)-/', $field_key, $matches) === false) {
            return $key;
        }

        return $field_key;
    }

    /**
     * Check if string starts with acf_field::
     * @param string $key 
     * @return bool 
     */
    public function key_contains_prefix($key)
    {
        if (strpos($key, "acf_field::") !== 0) {
            return false;
        }
        return true;
    }

    public function deactivate_acf_plugin($plugin)
    {
        if (!in_array($plugin, array('importwp-advanced-custom-fields/advanced-custom-fields.php'), true)) {
            return;
        }

        $plugin_to_deactivate  = 'importwp-advanced-custom-fields/advanced-custom-fields.php';

        if (is_multisite() && is_network_admin()) {
            $active_plugins = (array) get_site_option('active_sitewide_plugins', array());
            $active_plugins = array_keys($active_plugins);
        } else {
            $active_plugins = (array) get_option('active_plugins', array());
        }

        foreach ($active_plugins as $plugin_basename) {
            if ($plugin_to_deactivate === $plugin_basename) {
                set_transient('importwp_deactivated_notice_acf', 'yes', 1 * HOUR_IN_SECONDS);
                deactivate_plugins($plugin_basename);
                return;
            }
        }
    }

    public function deactivate_acf_plugin_notice()
    {
        if (get_transient('importwp_deactivated_notice_acf') !== 'yes') {
            return;
        }

        $message = __("ImportWP Advanced Custom Fields Importer Addon is now included in ImportWP PRO and should not be active at the same time. We've automatically deactivated ImportWP Advanced Custom Fields Importer Addon.", 'importwp');

?>
        <div class="updated" style="border-left: 4px solid #ffba00;">
            <p><?php echo esc_html($message); ?></p>
        </div>
<?php

        delete_transient('importwp_deactivated_notice_acf');
    }

    /**
     * Add ACF and ACF Pro plugin to compatability whitelist
     * 
     * @param string[] $plugins 
     * @return string[] 
     */
    function update_compat_whitelist($plugins)
    {
        $plugins[] = 'advanced-custom-fields-pro/acf.php';
        $plugins[] = 'advanced-custom-fields/acf.php';
        return $plugins;
    }
}
