<?php

namespace WPGO_Plugins\SVG_Flags;

/*
 *    Plugin 'Welcome' asettings page
 */

class Welcome_Settings
{

    protected $module_roots;

    /* Main class constructor. */
    public function __construct($module_roots, $plugin_data, $custom_plugin_data, $utility)
    {
        $this->module_roots = $module_roots;
        $this->plugin_data = $plugin_data;
        $this->custom_plugin_data = $custom_plugin_data;
				$this->freemius_upgrade_url = $this->custom_plugin_data->freemius_upgrade_url;
				$this->freemius_discount_upgrade_url = $this->custom_plugin_data->freemius_discount_upgrade_url;
        $this->utility = $utility;

        //$this->pro_attribute = '<span class="pro" title="Shortcode attribute available in ' . $this->custom_plugin_data->main_menu_label . ' Pro"><a href="' . $this->freemius_upgrade_url . '">PRO</a></span>';
        //$this->settings_slug = $this->custom_plugin_data->settings_pages['settings']['slug'];
        //$this->new_features_slug = $this->custom_plugin_data->settings_pages['new-features']['slug'];
        $this->welcome_slug = $this->custom_plugin_data->settings_pages['welcome']['slug'];
        $this->plugin_data = $plugin_data;

        add_action('admin_menu', array(&$this, 'add_options_page'));
    }

    /* Add menu page. */
    public function add_options_page()
    {
        $parent_slug = null;
        $subpage_slug = $this->welcome_slug;

        //echo ">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> TEST: " . SVG_FLAGS_FREEMIUS_NAVIGATION . '<br>';

        if (SVG_FLAGS_FREEMIUS_NAVIGATION === 'tabs') {
            // only show submenu page when tabs enabled if welcome tab is active
            if (isset($_GET['page']) && $_GET['page'] === $subpage_slug) {
                $parent_slug = $this->custom_plugin_data->parent_slug;
            }
        } else {
            // always use this if navigation is set to 'menu'
            $parent_slug = $this->custom_plugin_data->parent_slug;
        }

        if ($this->custom_plugin_data->menu_type === 'top') {
            $label = 'About';
        } else if ($this->custom_plugin_data->menu_type === 'sub') {
            $label = '<span class="fs-submenu-item fs-sub wpgo-plugins">About</span>';
        }

        add_submenu_page($parent_slug, 'Welcome to ' . $this->custom_plugin_data->main_menu_label, $label, 'manage_options', $subpage_slug, array(&$this, 'render_sub_menu_form'));
    }

    /* Display the sub menu page. */
    public function render_sub_menu_form()
    {
        $tabs_list_html = $this->utility->build_settings_tabs_html($this->plugin_data);
        $tab_classes = SVG_FLAGS_FREEMIUS_NAVIGATION === 'tabs' ? ' fs-section fs-full-size-wrapper' : ' no-tabs';
        $is_premium = $this->custom_plugin_data->is_premium;
				$plugin_lbl = $this->custom_plugin_data->main_menu_label;
				$more_features = $is_premium ? 'View the full set of <a href="https://wpgoplugins.com/document/svg-flags-documentation/#svg-flags-shortcodes" target="_blank">shortcode attributes</a> available.' : 'See the <a href="' . $this->custom_plugin_data->main_settings_url . '">main settings</a> page for a list of shortcode attributes available in the free version (expand the <strong>Shortcode Attributes & Default Values</strong> section).';
				$block_settings = $is_premium ? 'pro version.<p><img src="' . $this->module_roots['uri'] . '/assets/images/svg-flag-block-test-pro-settings.png"></p>' : 'free version.<p><img src="' . $this->module_roots['uri'] . '/assets/images/svg-flag-block-test-free-settings.png"></p>';
        ?>
				<div class="wrap welcome<?php echo $tab_classes; ?>">
					<?php echo $tabs_list_html; ?>
					<div class="wpgo-settings-inner">
						<div class="welcome-header">
							<div style="margin-right:60px;">
								<h1 class="heading" style="font-weight:bold;">Welcome to <?php _e($plugin_lbl . ' &nbsp;' . $this->plugin_data['Version'], 'svg-flags');?></h1>
								<p style="font-size:20px;">This is another exciting release! The plugin is really starting to take shape now. Let's dig in and see what's new, and how you can quickly get started adding SVG flags to your WordPress site.</p>
								<?php if(!$is_premium) : ?>
									<p><a href="<?php echo $this->freemius_discount_upgrade_url; ?>"><button class="button"><strong>Upgrade To PRO >> <span style="color:green;">30% OFF</span></strong></button></a>&nbsp; <span style="font-size: 16px;font-style: italic;">Exclusive discount offer! Get immediate access to all pro features version at a reduced price!</span></p>
								<?php endif; ?>								
							</div>
							<div>
								<img style="width:100px;height:100px;" src="<?php echo $this->module_roots['uri'] . '/assets/images/svg-flags.png'; ?>" />
							</div>
						</div>
						<h2>What's New in <?php echo $this->plugin_data['Version']; ?>?</h2>
            <p>The big news is that we've added two new SVG flag blocks to the Gutenberg editor, and a brand new shortcode. The first new block enables you to add a flag to your WordPress content via an SVG image element. This is provided as an alternative to using the <code>[svg-flag-image]</code> shortcode in the block editor.</p>
            <p>Next, we have the new <code>[svg-flag-heading]</code> shortcode that you can use to add an SVG flag next to a HTML heading. The second new block provides the same functionality as the flag heading shortcode.
            <p>Also, a lot of the plugin code has changed significantly under the hood since the last version. While you may not notice many differences using the plugin, the updated code structure means we can add new features more quickly than before which will benefit all users down the line.</p>
						<p>We're already working on the next set of features for the next version of the plugin! There's lot's more to come but we'd love to hear <strong>your ideas</strong> for <a href="<?php echo $this->custom_plugin_data->contact_us_url; ?>">new features</a>. Please tell us what you'd like to see added next.</p>
						<p>Click the button below to head on over to the New Features page to learn more.</p>
						<a href="<?php echo $this->custom_plugin_data->new_features_url; ?>"><button class="button"><strong>Show Me All New Features</strong></button></a>
						<div id="getting-started" style="padding-bottom:2px;"></div>

						<h2 style="margin-top:35px;">Getting Started Using <?php echo $plugin_lbl; ?></h2>
						<p>If this is your first time using the plugin or you just need a quick refresher then this section gives you all the information you need to get up to speed.</p>
						<p>Basically, the SVG Flags plugins allows you to add high quality SVG flags to your site via two shortcodes and an editor block as shown below.</p>
						<h3 style="margin-top:25px;">Using the <code>[svg-flag]</code> Shortcode</h3>
						<p>The <code>[svg-flag]</code> shortcode displays the flag as the background image of a <code>&lt;div&gt;</code> element.</p>
						<ul class="welcome-getting-started">
							<li><span>Step #1:</span> &nbsp;Create a new post or page, or edit an existing one.</li>
							<li><span>Step #2:</span> &nbsp;In the editor enter the <code>[svg-flag]</code> shortcode along with a two-letter <code>flag</code> attribute for a <a href="https://flagicons.lipis.dev/" target="_blank">specific country</a>.<p><img src="<?php echo $this->module_roots['uri']; ?>/assets/images/svg-flag-shortcode-test.png"></p></li>
							<li><span>Step #3:</span> &nbsp;Save the page and view on the frontend.<p><img src="<?php echo $this->module_roots['uri']; ?>/assets/images/svg-flag-shortcode-test-frontend.png"></p></li>
							<li><span>Step #4:</span> &nbsp;Add other shortcode attributes as required. <?php echo $more_features; ?></li>
						</ul>
						<h3 style="margin-top:25px;">Using the <code>[svg-flag-image]</code> Shortcode</h3>
						<p>The <code>[svg-flag-image]</code> shortcode displays the flag inside an <code>&lt;img&gt;</code> element. It generally allows more flexibility if you need to modify the flags general appearance (border, rounded corners, margin, padding etc.).</p>
						<ul class="welcome-getting-started">
							<li><span>Step #1:</span> &nbsp;Create a new post or page, or edit an existing one.</li>
							<li><span>Step #2:</span> &nbsp;In the editor enter the <code>[svg-flag-image]</code> shortcode along with a two-letter <code>flag</code> attribute for a <a href="https://flagicons.lipis.dev/" target="_blank">specific country</a>.<p><img src="<?php echo $this->module_roots['uri']; ?>/assets/images/svg-flag-image-shortcode-test.png"></p></li>
							<li><span>Step #3:</span> &nbsp;Save the page and view on the frontend.<p><img src="<?php echo $this->module_roots['uri']; ?>/assets/images/svg-flag-image-shortcode-test-frontend.png"></p></li>
							<li><span>Step #4:</span> &nbsp;Add other shortcode attributes as required. <?php echo $more_features; ?></li>
						</ul>
						<h3 style="margin-top:25px;">Using the <?php echo $plugin_lbl; ?> Editor Block</h3>
						<p>There's also a dedicated editor block available which is direct alternative to the <code>[svg-flag]</code> shortcode to add a flag visually inside the editor window.</p>
						<ul class="welcome-getting-started">
							<li><span>Step #1:</span> &nbsp;Create a new post or page, or edit an existing one.</li>
							<li><span>Step #2:</span> &nbsp;In the editor click the 'plus' icon to add a block. Start typing 'svg' in the search box and click to insert the SVG Flag block when it appears.<p><img src="<?php echo $this->module_roots['uri']; ?>/assets/images/svg-flag-block-test-insert.png"></p></li>
							<li><span>Step #3:</span> &nbsp;By default the GB flag will be selected. To change this click on the 'Select a flag' dropdown in block settings to choose a different flag.<p><img src="<?php echo $this->module_roots['uri']; ?>/assets/images/svg-flag-block-test-change.png"></p></li>
							<li><span>Step #4:</span> &nbsp;When the block is selected other flag settings are visible. The following screenshot shows the SVG flag block settings currently available in the <?php echo $block_settings; ?></li>
						</ul>
						<p>For full instructions on all the available shortcodes and attributes, as well as editor blocks check out the official plugin documentation.</p>
						<a href="https://wpgoplugins.com/document/svg-flags-documentation/" target="_blank"><button class="button"><strong>Take Me To Plugin Docs</strong></button></a>

						<h2 style="margin-top:35px;">Coming Soon</h2>
						<p>We're not done yet! There's still lot's more feature we want to add to <?php echo $plugin_lbl; ?>! Some of the upcoming features include:</p>
						<ul style="list-style:initial;margin-left:18px;" class="welcome-getting-started">
							<li>Add flags to navigation menus.</li>
							<li>Be able to add a flag to the main page/post title via the Gutenberg editor.</li>
							<li>More shortcodes, and shortcode attributes.</li>
							<li>More blocks and block settings.</li>
						</ul>
						<p>If you'd like to be notified of all plugin changes as soon as they are available then please <a href="https://us4.list-manage.com/subscribe?u=7ac9d1df68c71b93569502c5c&id=e4929d34d7" target="_blank">signup to our newsletter</a>.</p>
						<p>Or, if you'd like to see a feature added then why not drop us a line? We always like to hear how our plugins can be improved. Click the button below to send us a message and tell us what's on your mind.</a></p>
						<p><a href="<?php echo $this->custom_plugin_data->contact_us_url; ?>"><button class="button"><strong>Share Your Thoughts</strong></button></a></p>
						<br><br>
					</div>
				<?php
}

} /* End class definition */