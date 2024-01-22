<?php

namespace WPGO_Plugins\SVG_Flags;

/*
 *    Utility functions for SVG Flags
 */

class Utility
{

    protected $module_roots;

    /* Main class constructor. */
    public function __construct($module_roots, $custom_plugin_data)
    {
				$this->module_roots = $module_roots;
				$this->custom_plugin_data = $custom_plugin_data;
    }

    public static function build_el_attributes($class_attribute, $style_attribute, $title_attribute)
    {
        $el_attributes = "";
        if (!empty($class_attribute)) {
            $el_attributes .= ' class="';
            foreach ($class_attribute as $key => $value) {
                $el_attributes .= $value;
            }
            $el_attributes .= '"';
        }
        if (!empty($style_attribute)) {
            $el_attributes .= ' style="';
            foreach ($style_attribute as $key => $value) {
                $el_attributes .= $value;
            }
            $el_attributes .= '"';
        }
        if (!empty($title_attribute)) {
            $el_attributes .= ' title="' . $title_attribute . '"';
        }

        return $el_attributes;
    }

    // Build and return tab HTML. Numbered icon is added vi JS.
    public function build_settings_tabs_html($plugin_data)
    {			
        if (SVG_FLAGS_FREEMIUS_NAVIGATION === 'menu') {
            return '';
        }
				
        $settings_page_main_url = admin_url() . "options-general.php?page=" . $this->custom_plugin_data->settings_pages['settings']['slug'];
        $settings_page_new_features_url = admin_url() . "options-general.php?page=" . $this->custom_plugin_data->settings_pages['new-features']['slug'];
        $settings_page_welcome_url = admin_url() . "options-general.php?page=" . $this->custom_plugin_data->settings_pages['welcome']['slug'];

        $main_active = (isset($_GET['page']) && ($_GET['page'] === $this->custom_plugin_data->settings_pages['settings']['slug'])) ? ' nav-tab-active' : '';
				$new_features_active = (isset($_GET['page']) && ($_GET['page'] === $this->custom_plugin_data->settings_pages['new-features']['slug'])) ? ' nav-tab-active' : '';
				$welcome_active = (isset($_GET['page']) && ($_GET['page'] === $this->custom_plugin_data->settings_pages['welcome']['slug'])) ? ' nav-tab-active' : '';

        $tabs_list_html = '<h2 class="nav-tab-wrapper"><a href="' . $settings_page_main_url . '" class="nav-tab fs-tab' . $main_active . '">Settings</a><a href="' . $settings_page_new_features_url . '" class="nav-tab fs-tab' . $new_features_active . '">New Features</a><a href="' . $settings_page_welcome_url . '" class="nav-tab fs-tab' . $welcome_active . '">About</a></h2>';

        return $tabs_list_html;
    }

    public static function filter_and_decode_json($data)
    {
        $new_features = json_decode($data);

        // echo "<pre>";
        // echo gettype($new_features);
        // print_r($new_features);
        // echo "</pre>";
        //echo ">>>>>>>>>>>> >>>>>>>>>>>> type:" . gettype($new_features);

        if (svg_flags_fs()->is_premium()) {
            // remove all free entries
            foreach ($new_features as $key => $new_feature) {
                if ($new_feature->type === 'free') {
                    unset($new_features[$key]);
                }
            }
            $new_features = array_values($new_features); // reindex array
        }

        // echo "<pre>";
        // echo gettype($new_features);
        // print_r($new_features);
        // echo "</pre>";

        return $new_features;
		}
} /* End class definition */
