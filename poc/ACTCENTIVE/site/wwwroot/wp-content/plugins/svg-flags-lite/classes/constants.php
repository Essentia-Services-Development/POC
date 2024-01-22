<?php

namespace WPGO_Plugins\SVG_Flags;

/*
 *    Plugin constants
 */

class Constants
{

    /* Main class constructor. */
    public function __construct($module_roots)
    {
        $this->module_roots = $module_roots;

        $this->define_constants();
    }

    public function define_constants()
    {

        $this->is_premium = svg_flags_fs()->is_premium();

        // **********************
        // START - EDIT CONSTANTS
        $this->freemius_slug = svg_flags_fs()->get_slug();
        $this->main_menu_label = 'SVG Flags';
        $this->plugin_slug = 'svg-flags-wpgoplugins';
        $this->plugin_cpt_slug = ''; // use this as plugin (menu) slug if using CPT as parent menu
        $this->menu_type = 'sub'; // top|top-cpt|sub
        $this->cpt_slug = ''; // same one used in register_post_type()
        $this->css_prefix = 'svg-flags';
        if($this->is_premium) {
          $this->db_option_prefix = 'svg-flags';
        } else {
          $this->db_option_prefix = 'svg-flags-lite';
        }
				$this->enqueue_prefix = 'svg-flags';
        $this->plugin_settings_prefix = 'svg_flags';
        $this->filter_prefix = 'svg_flags';
        $this->donation_link = "https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6R94JSPJE9358";
				$coupon = "&coupon=30PCOFF";
        // END - EDIT CONSTANTS
        // **********************

        $root = $this->module_roots['dir'];

        // define country codes
        $json_countries = file_get_contents($root . 'assets/misc/countries.json');
        $this->country_codes = json_decode($json_countries, true);


        if ($this->menu_type === 'sub') {
            $this->parent_slug = 'options-general.php';
            $this->settings_page_hook = 'settings_page_' . $this->plugin_slug; // when main settings page is a submenu under the 'Settings' menu
        } else {
            $this->parent_slug = $this->plugin_slug; // when main settings page is a top-level menu
            $this->settings_page_hook_top = 'toplevel_page_' . $this->plugin_slug;
            // Important: the first part of this string (i.e. before '_page_') is based on the second argument passed to add_menu_page()
            // Unfortunately we can't generalise these two into a variable because the language translation functions don't allow vars.
            $this->settings_page_hook_sub = 'svg-flags_page_' . $this->plugin_slug;
        }

        // define settings pages used in the plugin
        $this->settings_pages = array(
            'settings' => array(
                'slug' => $this->plugin_slug,
                'label' => 'Settings',
                'css_class' => 'home',
            ),
            'new-features' => array(
                'slug' => $this->plugin_slug . '-new-features',
                'label' => 'New Features',
                'css_class' => 'new-features',
            ),
            'welcome' => array(
                'slug' => $this->plugin_slug . '-welcome',
                'label' => 'Welcome to SVG Flags!',
                'css_class' => 'welcome',
            ),
        );

        // define menu prefix and upgrade url
        $this->url_prefix = '';
        if ($this->menu_type === 'sub') {
            $this->url_prefix = 'options-general.php';
        } else if ($this->menu_type === 'top') {
            $this->url_prefix = 'admin.php';
        }
				$this->main_settings_url = admin_url() . $this->url_prefix . "?page=" . $this->settings_pages['settings']['slug'];
				$this->freemius_upgrade_url = admin_url() . $this->url_prefix . "?page=" . $this->settings_pages['settings']['slug'] . "-pricing";
				$this->freemius_discount_upgrade_url = admin_url() . $this->url_prefix . "?page=" . $this->settings_pages['settings']['slug'] . "-pricing&checkout=true&plan_id=8461&plan_name=pro&billing_cycle=annual&pricing_id=8343&currency=usd" . $coupon;
				$this->welcome_url = admin_url() . $this->url_prefix . "?page=" . $this->settings_pages['welcome']['slug'];
				$this->new_features_url = admin_url() . $this->url_prefix . "?page=" . $this->settings_pages['new-features']['slug'];
				$this->contact_us_url = admin_url() . $this->url_prefix . "?page=" . $this->settings_pages['settings']['slug'] . "-contact";

        // Don't allow tabs to be used when the plugin uses a top-level menu
        if (SVG_FLAGS_FREEMIUS_NAVIGATION === 'tabs' && $this->menu_type === 'top') {
            wp_die('WPGO PLUGINS ERROR: Freemius doesn\'t support using tabs with a top-level main settings page. Please change navigation to \'menu\' or use a submenu for the main settings page.');
        }
    }
} /* End class definition */
