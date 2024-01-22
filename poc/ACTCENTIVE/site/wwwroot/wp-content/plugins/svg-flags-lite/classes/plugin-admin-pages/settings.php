<?php

namespace WPGO_Plugins\SVG_Flags;

/*
 *    Main plugin settings page
 */

class Settings
{

    protected $module_roots;

    /* Main class constructor. */
    public function __construct($module_roots, $plugin_data, $custom_plugin_data, $utility, $settings_fw)
    {
        $this->module_roots = $module_roots;
        $this->custom_plugin_data = $custom_plugin_data;
        $this->hook_prefix = $this->custom_plugin_data->plugin_settings_prefix;
        $this->freemius_upgrade_url = $this->custom_plugin_data->freemius_upgrade_url;
        $this->utility = $utility;
        $this->settings_fw = $settings_fw;

        $this->pro_attribute = $this->custom_plugin_data->is_premium ? '' : '<span class="pro" title="Upgrade now for immediate access to this feature"><a href="' . $this->freemius_upgrade_url . '">PRO</a></span>';
        $this->settings_slug = $this->custom_plugin_data->settings_pages['settings']['slug'];
        $this->new_features_slug = $this->custom_plugin_data->settings_pages['new-features']['slug'];
        $this->welcome_slug = $this->custom_plugin_data->settings_pages['welcome']['slug'];

        $this->plugin_data = $plugin_data;

        add_action('admin_init', array(&$this, 'init'));
        add_action('admin_menu', array(&$this, 'add_options_page'));
        add_filter('custom_menu_order', array(&$this, 'filter_menu_order')); // enable custom menu ordering
    }

    /* Init plugin options to white list our options. */
    public function init()
    {
        $pfx = $this->custom_plugin_data->plugin_settings_prefix;
        register_setting($pfx . '_plugin_options', $pfx . '_options', array(&$this, 'validate_options'));
    }

    /* Sanitize and validate input. Accepts an array, return a sanitized array. */
    public function validate_options($input)
    {
        // Strip html from textboxes
        // e.g. $input['textbox'] =  wp_filter_nohtml_kses($input['textbox']);
        //$input['txt_page_ids'] = sanitize_text_field( $input['txt_page_ids'] );
        return $input;
    }

    /* Add menu page. */
    public function add_options_page()
    {
        // echo "<pre>";
        // echo ">>>>>>>>>>>> >>>>>>>>>>>> " . $this->settings_slug;
        // echo "</pre>";

        if ($this->custom_plugin_data->menu_type === 'top') {
            // Add main plugin settings page as a top-level menu item
            add_menu_page(
                __('SVG Flags Settings Page', 'svg-flags'),
                __('SVG Flags', 'svg-flags'),
                'manage_options',
                $this->settings_slug,
                array(&$this, 'render_form'),
                'dashicons-flag',
                82
            );
        } else if ($this->custom_plugin_data->menu_type === 'sub') {
            // Add main plugin settings page as a submenu of 'Settings'
            add_options_page(
                __('SVG Flags Settings Page', 'svg-flags'),
                __('SVG Flags', 'svg-flags'),
                'manage_options',
                $this->settings_slug,
                array(&$this, 'render_form')
            );
        }
    }

    /* Display the menu page. */
    public function render_form()
    {
        $tabs_list_html = $this->utility->build_settings_tabs_html($this->plugin_data);
        $tab_classes = SVG_FLAGS_FREEMIUS_NAVIGATION === 'tabs' ? ' fs-section fs-full-size-wrapper' : ' no-tabs';
        ?>
   		<div class="wrap welcome main<?php echo $tab_classes; ?>">

			<?php echo $tabs_list_html; ?>

			<div class="wpgo-settings-inner">
				<h1 class="heading" style="font-weight:bold;"><?php _e('Welcome to SVG Flags!', 'svg-flags');?></h1>
				<div style="margin:20px 0 10px;font-size:14px;line-height:1.4em;">To see what's new at a glance and how to use the plugin we recommend visiting the <a href="<?php echo $this->custom_plugin_data->welcome_url; ?>">getting started</a> page. Or, why not Take a look at the SVG Flags <a href="https://demo.wpgothemes.com/flexr/svg-flags-demo/" target="_blank">demo</a> to see live examples of the plugin in action.</div>

				<div>
					<span><a class="plugin-btn" href="<?php echo $this->custom_plugin_data->welcome_url; ?>">Getting Started</a></span>
					<span style="margin-left:5px;"><a class="plugin-btn" href="https://demo.wpgothemes.com/flexr/svg-flags-demo/" target="_blank">Launch Demo</a></span>
				</div>

        <h2 style="margin:35px 0 0 0;">SVG Flags Blocks</h2>

        <p>Using blocks is the is the new preferred way to add content to posts and pages. The main benefit is that the editor view is exactly the same as the frontend. This means no more having to switch between the editor and frontend to check how everything looks.</p><p>Expand the section directly below to see all editor blocks included with the SVG Flags plugin.</p>
        <div class="wpgo-expand-box">
					<h4 style="margin-top:5px;display:inline-block;margin-bottom:10px;">Available Blocks and Settings</h4><button id="blocks-btn" class="button">Expand <span style="vertical-align:sub;width:16px;height:16px;font-size:16px;" class="dashicons dashicons-arrow-down-alt2"></span></button>
					<div id="blocks-wrap">
						<p>Blocks are a fantastic alternative to using shortcodes as they allow you to add content visually rather than having to remember all the available shortcode attributes.</p>
						<h3 style="margin:20px 0 0 0;">SVG Flag Block</h3>
						<p>This block is a direct replacement to the <code>[svg-flag]</code>shortcode and displays a single flag as a background SVG image.</p>
						<p><img style="width:500px;" src="<?php echo $this->module_roots['uri'] . '/assets/images/svg-flag-block-settings.png'; ?>"></p>
						<p>Available block settings are:</p>
						<ul class="shortcode-attributes">
							<li><code>flag='gb'</code> - The alpha-2 country code for the flag you wish to display. <strong>See the full list <a href="https://www.iban.com/country-codes" target="_blank">here</strong></a>.</li>
							<li><code>size='5'</code> - Controls the width and height of the flag. (Replaces the previous individual width/height attributes).</li>
							<li><code>size_unit='em'</code> - Controls the unit used to render the size of the flag.</li>
							<li><code>square='false'</code> - Display flag in 4:3 ratio (default) or in square format (1:1).</li>
							<li style="opacity:0.35;"><code>caption='false'</code> <em>(coming soon)</em> - Display a caption with the country name underneath the flag.</li>
              <li style="opacity:0.35;"><code>inline="false"</code> <em>(coming soon)</em> - Display SVG flag as a block level element (default), or inline with text.</li>
              <li style="opacity:0.35;"><code>inline_valign="false"</code> <em>(coming soon)</em> - Vertical flag alignment.</li>
							<li><code>random='false'</code> - Display a random flag on each page load!</li>
							<li><code>id=''</code> <?php echo $this->pro_attribute; ?> - Specify a unique ID for each flag.</li>
							<li><code>flag_class=''</code> <?php echo $this->pro_attribute; ?> - Add custom CSS classes for easy styling.</li>
							<li><code>tooltip='false'</code> <?php echo $this->pro_attribute; ?> - Enable tooltips when flag hovered over.</li>
							<li><code>custom_tooltip=''</code> <?php echo $this->pro_attribute; ?> - Displays custom text when the flag is hovered over. For this to have any effect the <code>tooltip="true"</code> attribute needs to be specified.</li>
							<li style="opacity:0.35;"><code>custom_caption=''</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Displays custom flag caption.</li>
            </ul>
            
						<h3 style="margin:20px 0 0 0;">SVG Flag Image Block</h3>
						<p>This block is a direct replacement to the <code>[svg-flag-image]</code>shortcode and displays a single flag as an SVG image element. Contains more flexible display options than the <code>[svg-flag]</code> shortcode.</p>
						<p><img style="width:500px;" src="<?php echo $this->module_roots['uri'] . '/assets/images/svg-flag-image-block-settings.png'; ?>"></p>
						<p>Available block settings are:</p>
						<ul class="shortcode-attributes">
							<li><code>flag='gb'</code> - The alpha-2 country code for the flag you wish to display. <strong>See the full list <a href="https://www.iban.com/country-codes" target="_blank">here</strong></a>.</li>
							<li><code>size='5'</code> - Controls the width and height of the flag. (Replaces the previous individual width/height attributes).</li>
							<li><code>size_unit='em'</code> - Controls the unit used to render the size of the flag.</li>
							<li><code>square='false'</code> - Display flag in 4:3 ratio (default) or in square format (1:1).</li>
							<li style="opacity:0.35;"><code>caption='false'</code> <em>(coming soon)</em> - Display a caption with the country name underneath the flag.</li>
              <li style="opacity:0.35;"><code>inline="false"</code> <em>(coming soon)</em> - Display SVG flag as a block level element (default), or inline with text.</li>
              <li style="opacity:0.35;"><code>inline_valign="false"</code> <em>(coming soon)</em> - Vertical flag alignment.</li>
							<li><code>random='false'</code> - Display a random flag on each page load!</li>
							<li><code>id=''</code> <?php echo $this->pro_attribute; ?> - Specify a unique ID for each flag.</li>
							<li><code>flag_class=''</code> <?php echo $this->pro_attribute; ?> - Add custom CSS classes for easy styling.</li>
							<li><code>tooltip='false'</code> <?php echo $this->pro_attribute; ?> - Enable tooltips when flag hovered over.</li>
							<li><code>custom_tooltip=''</code> <?php echo $this->pro_attribute; ?> - Displays custom text when the flag is hovered over. For this to have any effect the <code>tooltip="true"</code> attribute needs to be specified.</li>
              <li style="opacity:0.35;"><code>custom_caption=''</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Displays custom flag caption.</li>
              <li style="opacity:0.35;"><code>border=''</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Displays a border around the flag.</li>
              <li style="opacity:0.35;"><code>border_radius=''</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Flag border radius.</li>
              <li style="opacity:0.35;"><code>padding=''</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Flag padding.</li>
              <li style="opacity:0.35;"><code>margin=''</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Flag margin.</li>
            </ul>

            <h3 style="margin:20px 0 0 0;">SVG Flag Heading Block</h3>
						<p>This block displays a flag next to an HTML heading element (H1, H2 etc.). <?php if(!$this->custom_plugin_data->is_premium) { echo '<strong><em>Note: This block is available in the ' . $this->pro_attribute . ' version only.</em></strong>'; } ?></p>
						<p><img style="width:500px;" src="<?php echo $this->module_roots['uri'] . '/assets/images/svg-flag-heading-block-settings.png'; ?>"></p>
						<p>Available block settings are:</p>
						<ul class="shortcode-attributes">
							<li><code>flag='gb'</code> <?php echo $this->pro_attribute; ?> - The alpha-2 country code for the flag you wish to display. <strong>See the full list <a href="https://www.iban.com/country-codes" target="_blank">here</strong></a>.</li>
							<li><code>size='5'</code> <?php echo $this->pro_attribute; ?> - Controls the width and height of the flag. (Replaces the previous individual width/height attributes).</li>
							<li><code>size_unit='em'</code> <?php echo $this->pro_attribute; ?> - Controls the unit used to render the size of the flag.</li>
							<li><code>square='false'</code> <?php echo $this->pro_attribute; ?> - Display flag in 4:3 ratio (default) or in square format (1:1).</li>
							<li style="opacity:0.35;"><code>caption='false'</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Display a caption with the country name underneath the flag.</li>
              <li style="opacity:0.35;"><code>inline="false"</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Display SVG flag as a block level element (default), or inline with text.</li>
              <li style="opacity:0.35;"><code>inline_valign="false"</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Vertical flag alignment.</li>
							<li><code>random='false'</code> <?php echo $this->pro_attribute; ?> - Display a random flag on each page load!</li>
							<li><code>id=''</code> <?php echo $this->pro_attribute; ?> - Specify a unique ID for each flag.</li>
							<li><code>flag_class=''</code> <?php echo $this->pro_attribute; ?> - Add custom CSS classes for easy styling.</li>
							<li><code>tooltip='false'</code> <?php echo $this->pro_attribute; ?> - Enable tooltips when flag hovered over.</li>
							<li><code>custom_tooltip=''</code> <?php echo $this->pro_attribute; ?> - Displays custom text when the flag is hovered over. For this to have any effect the <code>tooltip="true"</code> attribute needs to be specified.</li>
              <li style="opacity:0.35;"><code>custom_caption=''</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Displays custom flag caption.</li>
              <li style="opacity:0.35;"><code>border=''</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Displays a border around the flag.</li>
              <li style="opacity:0.35;"><code>border_radius=''</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Flag border radius.</li>
              <li style="opacity:0.35;"><code>padding=''</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Flag padding.</li>
              <li style="opacity:0.35;"><code>margin=''</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Flag margin.</li>
              <li><code>heading_tag=''</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - HTML Heading element (H1, H2, etc.).</li>
              <li><code>heading=''</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Display heading text.</li>
              <li style="opacity:0.35;"><code>flag_align=''</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Align flag to the left/right of the heading.</li>
              <li style="opacity:0.35;"><code>heading_color=''</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Color of the heading text.</li>
						</ul>
					</div>
				</div>

				<h2 style="margin:35px 0 0 0;">SVG Flags Shortcodes</h2>

        <p>Shortcodes have been around for a long time in WordPress and, before blocks were available, they were the only really accessible way to add complex or dynamic content to the editor. We've included SVG Flag shortcodes for those who prefer them to blocks, or if you don't have any choice. e.g. If using a 3rd party page builder, or the block editor has been disabled.</p><p>Expand the section directly below to see all SVG Flag shortcodes currently available, along with a full list of supported shortcode attributes.</p>
				<div class="wpgo-expand-box" style="margin-top:20px;">
					<h4>Available Shortcodes & Attributes</h4><button id="shortcodes-btn" class="button">Expand <span style="vertical-align:sub;width:16px;height:16px;font-size:16px;" class="dashicons dashicons-arrow-down-alt2"></span></button>

					<div id="shortcodes-wrap">
						<p>Click on the shortcodes below to view the full documentation for each shortcode. Default values are always used for missing shortcode attributes. i.e. Override only the values you want to change.</p>
						<p style="margin:20px 0 0 0;"><code><a class="code-link" href="https://wpgoplugins.com/document/svg-flags-documentation/#svg-flag" target="_blank">[svg-flag]</a></code> <?php printf(__('Displays a single flag as a background SVG image.', 'svg-flags'));?></p>
						<p>Here are the available shortcode attributes along with default values:</p>
						<ul class="shortcode-attributes">
							<li><code>flag='gb'</code> - The alpha-2 country code for the flag you wish to display. <strong>See the full list <a href="https://www.iban.com/country-codes" target="_blank">here</strong></a>.</li>
							<li><code>size='5'</code> - Controls the width and height of the flag. (Replaces the previous individual width/height attributes).</li>
							<li><code>size_unit='em'</code> - Controls the unit used to render the size of the flag.</li>
							<li><code>square='false'</code> - Display flag in 4:3 ratio (default) or in square format (1:1).</li>
							<li><code>caption='false'</code> - Display a caption with the country name underneath the flag.</li>
              <li><code>inline="false"</code> - Display SVG flag as a block level element (default), or inline with text.</li>
              <li><code>inline_valign="false"</code> - Vertical flag alignment.</li>
							<li><code>random='false'</code> - Display a random flag on each page load!</li>
							<li><code>id=''</code> <?php echo $this->pro_attribute; ?> - Specify a unique ID for each flag.</li>
							<li><code>flag_class=''</code> <?php echo $this->pro_attribute; ?> - Add custom CSS classes for easy styling.</li>
							<li><code>tooltip='false'</code> <?php echo $this->pro_attribute; ?> - Enable tooltips when flag hovered over.</li>
							<li><code>custom_tooltip=''</code> <?php echo $this->pro_attribute; ?> - Displays custom text when the flag is hovered over (requires <code>tooltip</code> to be set to <code>"true"</code>)</li>
							<li><code>custom_caption=''</code> <?php echo $this->pro_attribute; ?> - Displays custom flag caption.</li>
						</ul>

						<p style="margin:25px 0 0 0;"><code><a class="code-link" href="https://wpgoplugins.com/document/svg-flags-documentation/#svg-flag-image" target="_blank">[svg-flag-image]</a></code> <?php printf(__('Displays a single flag as an SVG image. Contains more flexible display options than the [svg-flag] shortcode.', 'svg-flags'));?></p>
						<p>Here are the available shortcode attributes along with default values:</p>
						<ul class="shortcode-attributes">
							<li><code>flag='gb'</code> - The alpha-2 country code for the flag you wish to display. <strong>See the full list <a href="https://www.iban.com/country-codes" target="_blank">here</strong></a>.</li>
							<li><code>size='5'</code> - Controls the width and height of the flag. (Replaces the previous individual width/height attributes).</li>
							<li><code>size_unit='em'</code> - Controls the unit used to render the size of the flag.</li>
							<li><code>square='false'</code> - Display flag in 4:3 ratio (default) or in square format (1:1).</li>
							<li><code>caption='false'</code> - Display a caption with the country name underneath the flag.</li>
              <li><code>inline="false"</code> - Display SVG flag as a block level element (default), or inline with text.</li>
              <li><code>inline_valign="false"</code> - Vertical flag alignment.</li>
							<li><code>random='false'</code> - Display a random flag on each page load!</li>
							<li><code>id=''</code> <?php echo $this->pro_attribute; ?> - Specify a unique ID for each flag.</li>
							<li><code>flag_class=''</code> <?php echo $this->pro_attribute; ?> - Add custom CSS classes for easy styling.</li>
							<li><code>tooltip='false'</code> <?php echo $this->pro_attribute; ?> - Enable tooltips when flag hovered over.</li>
							<li><code>custom_tooltip=''</code> <?php echo $this->pro_attribute; ?> - Displays custom text when the flag is hovered over (requires <code>tooltip</code> to be set to <code>"true"</code>)</li>
							<li><code>custom_caption=''</code> <?php echo $this->pro_attribute; ?> - Displays custom flag caption.</li>
							<li><code>border=''</code> <?php echo $this->pro_attribute; ?> - Add a border around the flag. e.g. <code>1px blue solid</code>.</li>
							<li><code>border_radius=''</code> <?php echo $this->pro_attribute; ?> - Add rounded corners to a flag. e.g. <code>3px</code>.</li>
							<li><code>padding=''</code> <?php echo $this->pro_attribute; ?> - Add custom padding between the flag and border.</li>
							<li><code>margin=''</code> <?php echo $this->pro_attribute; ?> - Add custom margin outside of the flag border.</li>
						</ul>

            <p style="margin:25px 0 0 0;"><code><a class="code-link" href="https://wpgoplugins.com/document/svg-flags-documentation/#svg-flag-heading" target="_blank">[svg-flag-heading]</a></code> <?php echo $this->pro_attribute; ?> <?php printf(__('Displays an SVG flag next to a HTML heading element.', 'svg-flags'));?></p>
						<p>Here are the available shortcode attributes along with default values:</p>
						<ul class="shortcode-attributes">
							<li><code>flag='gb'</code> <?php echo $this->pro_attribute; ?> - The alpha-2 country code for the flag you wish to display. <strong>See the full list <a href="https://www.iban.com/country-codes" target="_blank">here</strong></a>.</li>
							<li><code>size='5'</code> <?php echo $this->pro_attribute; ?> - Controls the width and height of the flag. (Replaces the previous individual width/height attributes).</li>
							<li><code>size_unit='em'</code> <?php echo $this->pro_attribute; ?> - Controls the unit used to render the size of the flag.</li>
							<li><code>square='false'</code> <?php echo $this->pro_attribute; ?> - Display flag in 4:3 ratio (default) or in square format (1:1).</li>
							<li><code>caption='false'</code> <?php echo $this->pro_attribute; ?> - Display a caption with the country name underneath the flag.</li>
              <li><code>inline="false"</code> <?php echo $this->pro_attribute; ?> - Display SVG flag as a block level element (default), or inline with text.</li>
              <li><code>inline_valign="false"</code> <?php echo $this->pro_attribute; ?> - Vertical flag alignment.</li>
							<li><code>random='false'</code> <?php echo $this->pro_attribute; ?> - Display a random flag on each page load!</li>
							<li><code>id=''</code> <?php echo $this->pro_attribute; ?> - Specify a unique ID for each flag.</li>
							<li><code>flag_class=''</code> <?php echo $this->pro_attribute; ?> - Add custom CSS classes for easy styling.</li>
							<li><code>tooltip='false'</code> <?php echo $this->pro_attribute; ?> - Enable tooltips when flag hovered over.</li>
							<li><code>custom_tooltip=''</code> <?php echo $this->pro_attribute; ?> - Displays custom text when the flag is hovered over (requires <code>tooltip</code> to be set to <code>"true"</code>)</li>
							<li><code>custom_caption=''</code> <?php echo $this->pro_attribute; ?> - Displays custom flag caption.</li>
							<li><code>border=''</code> <?php echo $this->pro_attribute; ?> - Add a border around the flag. e.g. <code>1px blue solid</code>.</li>
							<li><code>border_radius=''</code> <?php echo $this->pro_attribute; ?> - Add rounded corners to a flag. e.g. <code>3px</code>.</li>
							<li><code>padding=''</code> <?php echo $this->pro_attribute; ?> - Add custom padding between the flag and border.</li>
              <li><code>margin=''</code> <?php echo $this->pro_attribute; ?> - Add custom margin outside of the flag border.</li>
              <li><code>heading_tag=''</code> <?php echo $this->pro_attribute; ?> - Set the heading HTML tag.</li>
              <li><code>heading=''</code> <?php echo $this->pro_attribute; ?> - Text to display in the heading.</li>
              <li><code>flag_align=''</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Align the flag to the left/right of the heading text.</li>
              <li><code>heading_color=''</code> <?php echo $this->pro_attribute; ?> <em>(coming soon)</em> - Color of the heading text.</li>
						</ul>
          </div>
				</div>

				<div style="margin-top:25px;"></div>

				<h2 style="margin:35px 0 0 0;">Stay In Touch!</h2>

				<table class="form-table">

					<?php do_action($this->hook_prefix . '_settings_row_section_1', 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6R94JSPJE9358');?>

          <?php echo $this->settings_fw->try_our_other_plugins(basename($this->module_roots['dir'])); ?>

          <?php echo $this->settings_fw->subscribe_to_newsletter('http://eepurl.com/bXZmmD'); ?>

          <?php echo $this->settings_fw->keep_in_touch(); ?>

          <?php echo $this->settings_fw->report_issues($this->custom_plugin_data->contact_us_url); ?>

				</table>

			</div>

		</div>
		<?php
}

    public function filter_menu_order($custom)
    {
        global $submenu;

        // selectively rearrange for 'tabs', and always for 'menu'
        if (SVG_FLAGS_FREEMIUS_NAVIGATION === 'tabs') {
            // don't bother to rearrange unless the submenu page is displayed
            if (
                !isset($_GET['page'])
                || ($_GET['page'] !== $this->new_features_slug && $_GET['page'] !== $this->welcome_slug)
            ) {
                return $custom;
            }
        }

        $parent_slug = $this->custom_plugin_data->parent_slug;
        $menu_type = $this->custom_plugin_data->menu_type;
        $pricingpage_index = 0;
        $parent_index = 0;
        $subpage_index1 = 0;
        $subpage_index2 = 0;

        // if global menu array is empty then don't try to reindex. This cis typically empty when the Freemius
        // optin is displayed.
        if (empty($submenu[$parent_slug])) {
            return $custom;
        }

        // store menu indexes of settings pages
        foreach ($submenu[$parent_slug] as $key => $val) {

            //echo "type:" . gettype($key);
            if ($val[2] === $this->settings_slug) {
                $parent_index = $key;
            }
            if ($val[2] === $this->new_features_slug) {
                $subpage_index1 = $key;
            }
            if ($val[2] === $this->welcome_slug) {
                $subpage_index2 = $key;
            }
            if ($val[2] === $this->custom_plugin_data->plugin_cpt_slug . "-wp-support-forum") {
              $wp_org_support_forum_index = $key;
            }
            if ($val[2] === $this->custom_plugin_data->freemius_slug . '-pricing') {
                $pricingpage_index = $key;
            }
            // if ($val[2] === $this->settings_slug . '-pricing') {
            //     $pricingpage_index = $key;
            // }
        }

        // only reindex new features page if menu type is 'sub'
        if ($menu_type === 'sub') {
            // only reindex if tabs are active and we're on new feature settings page OR tabs are not active
            if (
                (SVG_FLAGS_FREEMIUS_NAVIGATION === 'tabs' && $_GET['page'] === $this->new_features_slug)
                || SVG_FLAGS_FREEMIUS_NAVIGATION === 'menu'
            ) {
                // find the next available index after the main settings page
                $tmp_parent_index1 = $parent_index;
                while (isset($submenu[$parent_slug][$tmp_parent_index1])) {
                    $tmp_parent_index1++;
                }
                // move new features page to next position after main settings page
                $submenu[$parent_slug][$tmp_parent_index1] = $submenu[$parent_slug][$subpage_index1];
                unset($submenu[$parent_slug][$subpage_index1]);
                ksort($submenu[$parent_slug]);
            }
        }

        // only reindex if tabs are active and we're on welcome settings page OR tabs are not active
        if (
            (SVG_FLAGS_FREEMIUS_NAVIGATION === 'tabs' && $_GET['page'] === $this->welcome_slug)
            || SVG_FLAGS_FREEMIUS_NAVIGATION === 'menu'
        ) {
            // find the next available index after the pricing page unless tabs are active in which case get next
            // available index after main settings page
            if (SVG_FLAGS_FREEMIUS_NAVIGATION === 'tabs') {
                $tmp_parent_index2 = $parent_index;
            } else {
                $tmp_parent_index2 = $pricingpage_index;
            }
            while (isset($submenu[$parent_slug][$tmp_parent_index2])) {
                $tmp_parent_index2++;
            }
            // move welcome page to next position after pricing page
            $submenu[$parent_slug][$tmp_parent_index2] = $submenu[$parent_slug][$subpage_index2];
            unset($submenu[$parent_slug][$subpage_index2]);
            ksort($submenu[$parent_slug]);
        }

        // echo "<pre>";
        // echo 'BEFORE:';
        // print_r($submenu[$parent_slug]);
        // //print_r($submenu[$parent_slug][$tmp_parent_index1]);
        // //print_r($submenu[$parent_slug][$tmp_parent_index2]);
        // echo "Settings slug: " . $this->settings_slug . '<br>';
        // echo "Parent slug: " . $parent_slug . "<br>";
        // echo "Pricing-I: " . $pricingpage_index . "<br>";
        // echo "Parent-I: " . $parent_index . "<br>";
        // echo "TPI1: " . $tmp_parent_index1 . "<br>";
        // echo "TPI2: " . $tmp_parent_index2 . "<br>";
        // echo "SI1: " . $subpage_index1 . "<br>";
        // echo "SI2: " . $subpage_index2 . "<br>";
        // echo "</pre>";
        // die();

        return $custom;
    }

} /* End class definition */