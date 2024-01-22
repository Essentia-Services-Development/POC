<?php

namespace WPGO_Plugins\SVG_Flags;

/*
 *    Plugin 'New Features' settings page
 */

class New_Features_Settings
{

    protected $module_roots;

    /* Main class constructor. */
    public function __construct($module_roots, $new_features_arr, $plugin_data, $custom_plugin_data, $utility, $new_features_fw)
    {
      $this->module_roots = $module_roots;
      $this->custom_plugin_data = $custom_plugin_data;
      $this->freemius_upgrade_url = $this->custom_plugin_data->freemius_upgrade_url;
      $this->freemius_discount_upgrade_url = $this->custom_plugin_data->freemius_discount_upgrade_url;
      $this->utility = $utility;
      $this->new_features_fw = $new_features_fw;

        //$this->pro_attribute = '<span class="pro" title="Shortcode attribute available in ' . $this->custom_plugin_data->main_menu_label . ' Pro"><a href="' . $this->freemius_upgrade_url . '">PRO</a></span>';
        $this->new_features_arr = $new_features_arr;
        //$this->settings_slug = $this->custom_plugin_data->settings_pages['settings']['slug'];
        $this->new_features_slug = $this->custom_plugin_data->settings_pages['new-features']['slug'];
        //$this->welcome_slug = $this->custom_plugin_data->settings_pages['welcome']['slug'];
        $this->plugin_data = $plugin_data;

        add_action('admin_menu', array(&$this, 'add_options_page'));
    }

    /* Add menu page. */
    public function add_options_page()
    {
        $parent_slug = null;
        $subpage_slug = $this->new_features_slug;

        //echo ">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> TEST: " . SVG_FLAGS_FREEMIUS_NAVIGATION . '<br>';

        if (SVG_FLAGS_FREEMIUS_NAVIGATION === 'tabs') {
            // only show submenu page when tabs enabled if new features tab is active
            if (isset($_GET['page']) && $_GET['page'] === $subpage_slug) {
                $parent_slug = $this->custom_plugin_data->parent_slug;
            }
        } else {
            // always use this if navigation is set to 'menu'
            $parent_slug = $this->custom_plugin_data->parent_slug;
        }

        if ($this->custom_plugin_data->menu_type === 'top') {
            $label = 'New Features';
        } else if ($this->custom_plugin_data->menu_type === 'sub') {
            $label = '<span class="fs-submenu-item fs-sub wpgo-plugins">New Features</span>';
        }

        add_submenu_page($parent_slug, 'New Features', $label, 'manage_options', $subpage_slug, array(&$this, 'render_sub_menu_form'));
    }

    /* Display the sub menu page. */
    public function render_sub_menu_form()
    {
        $tabs_list_html = $this->utility->build_settings_tabs_html($this->plugin_data);
        $tab_classes = SVG_FLAGS_FREEMIUS_NAVIGATION === 'tabs' ? ' fs-section fs-full-size-wrapper' : ' no-tabs';
        $is_premium = $this->custom_plugin_data->is_premium;
        $opt_pfx = $this->custom_plugin_data->db_option_prefix;

        // when new features page/tab is clicked don't show numbered icon again until plugin is updated again
        update_option($opt_pfx . '-new-features-numbered-icon', 'false');
        ?>
		 <div class="wrap welcome new-features<?php echo $tab_classes; ?>">

		<?php echo $tabs_list_html; ?>

		<div class="wpgo-settings-inner">
			<h1 class="heading" style="font-weight:bold;"><?php _e('New Plugin Features!', 'svg-flags');?></h1>
			<p>Features added in recent releases will appear here, ordered by the date first implemented. If you'd like to be notified of all plugin changes as soon as they're available then please <a href="https://us4.list-manage.com/subscribe?u=7ac9d1df68c71b93569502c5c&id=e4929d34d7" target="_blank">signup to our newsletter</a>. And if you have any suggestions for new features you'd like to see added to the plugin then why not <a href="<?php echo $this->custom_plugin_data->contact_us_url; ?>">drop us a line</a>? We always like to hear feedback from our users. Tell us what's on your mind!</p>
        <?php echo $this->new_features_fw->new_features_loop(
          $this->new_features_arr,
          $this->freemius_discount_upgrade_url,
          $is_premium,
          $this->plugin_data
          ); ?>
		</div>
	</div>
	<?php
}

} /* End class definition */