<?php
/*
 * Performs tasks for Admin page requests
 * @package PeepSo
 * @author PeepSo
 */

class PeepSoAdmin
{
    const NOTICE_KEY = 'peepso_admin_notices_';
    const NOTICE_TTL = 3600;                // set TTL to 1 hour - probably overkill
    const PEEPSO_URL = 'https://www.peepso.com';
    const MAYFLY_PLUGINS = 'peepso_plugins';

    private static $_instance = NULL;

    private $dashboard_tabs = NULL;
    private $dashboard_metaboxes = NULL;
    private $tab_count = 0;

    private $messages = array();

    private function __construct()
    {
        if (get_option('permalink_structure'))
            add_action('admin_menu', array(&$this, 'admin_menu'), 9);

        add_action('admin_enqueue_scripts', array(&$this, 'enqueue_scripts'));

        //allow redirection, even if my theme starts to send output to the browser
        add_action('init', array(&$this, 'do_output_buffer'));

        add_action('admin_notices', array(&$this, 'admin_notices'));

        // check for wp-admin/user.php page and include hooks/classes for user list
        add_filter('views_users', array(&$this, 'filter_user_views'), 100, 1);
        add_filter('manage_users_custom_column', array(&$this, 'filter_custom_user_column'), 10, 3);
        add_filter('user_row_actions', array(&$this, 'filter_user_actions'), 10, 2);
        add_action('manage_users_columns', array(&$this, 'filter_user_list_columns'));
        add_action('restrict_manage_users', array(&$this, 'peepso_roles'));
        add_action('current_screen', array(&$this, 'update_user_roles'));
        add_action('current_screen', array(&$this, 'update_report'));
        add_action('admin_notices', array(&$this,'register_notice'));

        $dir = explode('/', plugin_basename(__FILE__));
        $dir = $dir[0];

        add_action('admin_footer', array(&$this, 'show_deactivation_feedback_dialog'));
        add_filter('plugin_action_links_' . $dir . '/peepso.php', array(&$this, 'modify_plugin_action_links'), 10, 2 );
        add_filter('network_admin_plugin_action_links_' . $dir . '/peepso.php', array(&$this, 'modify_plugin_action_links'), 10, 2 );

        add_filter('peepso_admin_profile_field_types', array(&$this,'filter_admin_profile_field_types'));

        // delete cache
        add_action('delete_user', array('PeepSo3_Mayfly', 'clr_cache'));

        // #2805 unblock when promoted
        add_action( 'set_user_role', function( $user_id, $role, $old_roles )
        {
            if ('administrator' == $role) {
                $block = new PeepSoBlockUsers();
                $block->delete_from_blocked($user_id);
            }

        }, 10, 3 );

    }
    public function filter_admin_profile_field_types( $field_types )
    {
        $field_types[] = 'text';
        $field_types[] = 'textdate';
        $field_types[] = 'texturl';
        $field_types[] = 'texturlpreset';
        $field_types[] = 'textemail';
        #$field_types[] = 'textphonenumber';
        $field_types[] = 'location';
        $field_types[] = 'country';
        $field_types[] = 'selectsingle';
        $field_types[] = 'selectmulti';
        $field_types[] = 'separator';

        return $field_types;
    }


    /*
     * return singleton instance of PeepSoAdmin
     */
    public static function get_instance()
    {
        if (NULL === self::$_instance)
            self::$_instance = new self();
        return (self::$_instance);
    }


    /*
     * Callback for displaying admin notices
     */
    public function admin_notices()
    {
        $screen = get_current_screen();
        if ('users.php' === $screen->parent_file) {
            // check if there are one or more users with a role of 'verified' or 'registered'
//          $result = count_users();
//          if (isset($result['avail_roles']['peepso_register']) || isset($result['avail_roles']['peepso_verified'])) {
            $usradm = PeepSoUser::get_instance();
            $count_roles = $usradm->count_for_roles(array('verified', 'register'));
            if (0 !== $count_roles) {
                $notice = __('You have Registered or Verified users that need to be approved. To approve, change the user\'s role to PeepSo Member or other appropriate role.', 'peepso-core');
                $notice .= sprintf(__(' %1$sClick here%2$s for more information on assigning roles.', 'peepso-core'),
                    '<a href="#TB_inline?&inlineId=assign-roles-modal-id" class="thickbox">',
                    '</a>');
//              $notice .= ' <a href="#TB_inline?inlineId=assign-roles-modal-id" class="thickbox">' . __('Click here', 'peepso-core') . '</a>' . __(' for more information on assigning roles.', 'peepso-core');
                echo '<div class="update-nag" style="padding:11px 15px; margin:5px 15px 2px 0;">', $notice, '</div>', PHP_EOL;
                echo '<div id="assign-roles-modal-id" style="display:none;">';
                echo '<div>';
                echo '<h3>', __('PeepSo User Roles:', 'peepso-core'), '</h3>';
                echo '<p>', sprintf(__('You can change Roles for PeepSo users by selecting the checkboxes for individual users and then selecting the desired Role from the %s dropdown.', 'peepso-core'),
                    '<select><option>' . __('- Select Role -', 'peepso-core') . '</option></select>'), '</p>';
                echo '<p>', sprintf(__('Once the new Role is selected, click on the %s button and those users will be updated.', 'peepso-core'),
                    '<input type="button" name="sample" id="sample" class="button" value="' . __('Change Role', 'peepso-core') . '">'), '</p>';
                echo '<p>', __('Meaning of user roles:', 'peepso-core'), '</p>';
                $roles = $this->get_roles();
                $translated_roles = $this->get_translated_roles();
                foreach ($roles as $name => $desc) {
                    echo '&nbsp;&nbsp;<b>', $translated_roles[$name], '</b> - ', esc_html($desc), '<br/>';
                }
                echo '</div>';
                echo '</div>'; // #assign-roles-modal-id
                wp_enqueue_script('thickbox');
                wp_enqueue_style('thickbox');
            }
        }

        $key = self::NOTICE_KEY . get_current_user_id();
        $notices = PeepSo3_Mayfly::get($key);

        if ($notices) {
            foreach ($notices as $notice)
                echo '<div class="', $notice['class'], '" style="padding:11px 15px; margin:5px 15px 2px 0;">', $notice['message'], '</div>' . PHP_EOL;
        }
        PeepSo3_Mayfly::del($key);
    }


    /*
     * callback for admin_menu event. set up menus
     */
    public function admin_menu()
    {
        $installer_new = '';
        if(!PeepSoSystemRequirements::is_demo_site()) {
            PeepSo3_Helper_Addons::get_addons();
            if (PeepSo3_Helper_Addons::maybe_installer_has_new()) {
                $installer_new = ' <span style="color:orange"><i class="gcis gci-star"></i> NEW!';
            }
        }

        $admin = PeepSoAdmin::get_instance();
        // $dasboard_hookname = toplevel_page_peepso
        $dashboard_hookname = add_menu_page(__('PeepSo', 'peepso-core'), __('PeepSo', 'peepso-core').$installer_new,
            'manage_options',
            'peepso',
            array(&$this, 'dashboard'),
            PeepSo::get_asset('images/admin/logo-icon_20x20.png'),
            3);


        add_submenu_page('peepso',
            __('Dashboard', 'peepso-core'),
            __('Dashboard', 'peepso-core'),
            'manage_options',
            'peepso',
            array(&$this, 'dashboard')
        );

        add_action('load-' . $dashboard_hookname, array(&$this, $dashboard_hookname . '_loaded'));
        add_action('load-' . $dashboard_hookname, array(&$this, 'config_page_loaded'));

        $aTabs = $admin->get_tabs();

        // add submenu items for each item in tabs list
        foreach ($aTabs as $color => $tabs) {
            foreach ($tabs as $name => $tab) {
                $function = (isset($tab['function'])) ? $tab['function'] : null;

                $count = '';
                if (isset($tab['count']) && ($tab['count'] > 0 || (!is_int($tab['count']) && strlen($tab['count'])))) {
                    $count = '<span class="awaiting-mod"><span class="pending-count">' . $tab['count'] . '</span></span>';
                }
                $submenu = '';
                if (isset($tab['submenu']))
                    $submenu = $tab['submenu'];

                $submenu_page = add_submenu_page('peepso',
                    $tab['menu'], $tab['menu'] . $count . $submenu,
                    'manage_options', $tab['slug'], $function);

                if (method_exists($this, $submenu_page . '_loaded'))
                    add_action('load-' . $submenu_page, array(&$this, $submenu_page . '_loaded'));


                add_action('load-' . $submenu_page, array(&$this, 'config_page_loaded'));
            }
        }

        $rep = new PeepSoReport();
        $items = $rep->get_num_reported_items();
        $count = '';
        if ($items > 0)
            $count = '<span class="awaiting-mod"><span class="pending-count">' . $items . '</span></span>';

//		$report_sub = add_submenu_page(
//			'peepso',
//			__('Reported Items', 'peepso-core'),
//			__('Reported Items', 'peepso-core') . $count,
//			'manage_options',
//			'peepso-reports',
//			array('PeepSoAdminReport', 'dashboard')
//		);3244
    }


    public static function admin_header($title)
    {
        ?><h1 style="font-variant:small-caps;color: #666666;"><img width="130" src="<?php echo PeepSo::get_asset('images/admin/logo_red.png');?>" /> <?php echo strtolower($title);?></h1><?php
    }
    /*
     * callback to display the PeepSo Dashboard
     */
    public function dashboard()
    {
        //$aTabs = apply_filters('peepso_admin_dashboard_tabs', $this->dashboard_tabs);

        $peepso_config = PeepSoConfig::get_instance();
        $admin = PeepSoAdmin::get_instance();
        $admin->define_dashboard_metaboxes();
        $this->dashboard_metaboxes = apply_filters('peepso_admin_dashboard_metaboxes', $this->dashboard_metaboxes);
        $admin->prepare_metaboxes();

        PeepSoAdmin::admin_header(__('Dashboard', 'peepso-core'));
        echo '<div id="peepso" class="wrap">';

        echo '<div class="row-fluid">';

//		echo '<div class="dashtab">';
//		foreach ($aTabs as $color => $tabs)
//			$this->output_tabs($color, $tabs);
//		echo '</div>';

        echo '<div class="dashgraphs">';
        echo '<div class="row">
				<div class="col-xs-12">
				<div class="row">
					<!-- Left column -->
					<div class="col-xs-12 col-sm-6">';
        $peepso_config->do_meta_boxes('toplevel_page_peepso', 'left', null);
        echo '
					</div>
					<!-- Right column -->
					<div class="col-xs-12 col-sm-6">';
        $peepso_config->do_meta_boxes('toplevel_page_peepso', 'right', null);
        echo '
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
		<div class="clearfix"></div>';

        echo '</div>';
        echo '</div>';
        echo '</div>';  // .wrap
    }

    /**
     * Output the admin dashboard tabs
     * @param  string $color   The infobox color used as css class
     * @param  array $tablist The tabs to be displayed
     * @return void          Echoes the tab HTML.
     */
//	private function output_tabs($color, $tablist)
//	{
//		$size = number_format((100 / $this->tab_count) - 1, 2);
//		if ($size > 15)
//			$size = 15;
//		foreach ($tablist as $tab => $data) {
//			echo    '<div class="infobox infobox-', $color, ' infobox-dark" style="width:', $size, '%">', PHP_EOL;
//			if ('/' === substr($data['slug'], 0, 1))
//				echo    '<a href="', get_admin_url(NULL, $data['slug']), '">', PHP_EOL;
//			else
//				echo    '<a href="admin.php?page=', $data['slug'], '">', PHP_EOL;
//			echo            '<div class="infobox-icon dashicons dashicons-', $data['icon'], '"></div>' , PHP_EOL;
//			if (isset($data['count'])) {
//				echo            '<div class="infobox-data">', PHP_EOL;
//				echo                '<div class="infobox-content">', $data['count'], '</div>', PHP_EOL;
//				echo            '</div>', PHP_EOL;
//			}
//			echo            '<div class="infobox-caption">', $data['menu'], '</div>', PHP_EOL;
//			echo            '</a>', PHP_EOL;
//			echo    '</div>', PHP_EOL;
//		}
//	}


    /*
     * Enqueue scripts and styles for PeepSo admin
     */
    public function enqueue_scripts()
    {
        global $wp_styles;

        $free_bundle = 0;
        if (apply_filters('peepso_free_bundle_should_brand', FALSE) && PeepSo3_Helper_Addons::license_is_free_bundle()) {
            $free_bundle = 1;
            $free_bundle_branding = PeepSo3_Helper_Remote_Content::get('free_bundle_branding');
        }

        wp_register_style('ace-admin-boostrap-min', PeepSo::get_asset('aceadmin/css/bootstrap.min.css'), NULL, PeepSo::PLUGIN_VERSION, 'all');
        wp_register_style('ace-admin-boostrap-responsive', PeepSo::get_asset('aceadmin/bootstrap-responsive.min.css'), NULL, PeepSo::PLUGIN_VERSION, 'all');
        wp_register_style('ace-admin-boostrap-timepicker', PeepSo::get_asset('aceadmin/bootstrap-timepicker.css'), NULL, PeepSo::PLUGIN_VERSION, 'all');

        wp_register_style('ace-admin-fonts', PeepSo::get_asset('aceadmin/css/ace-fonts.css'), NULL, PeepSo::PLUGIN_VERSION, 'all');
        wp_register_style('ace-admin-fontawesome', PeepSo::get_asset('aceadmin/css/font-awesome.min.css'), NULL, PeepSo::PLUGIN_VERSION, 'all');
        wp_register_style('ace-admin', PeepSo::get_asset('aceadmin/css/ace.min.css'), NULL, PeepSo::PLUGIN_VERSION, 'all');
        wp_register_style('ace-admin-responsive', PeepSo::get_asset('aceadmin/css/ace-responsive.min.css'), NULL, PeepSo::PLUGIN_VERSION, 'all');
        wp_register_style('ace-admin-skins', PeepSo::get_asset('aceadmin/css/ace-skins.min.css'), NULL, PeepSo::PLUGIN_VERSION, 'all');
        wp_register_style('ace-admin-ie', PeepSo::get_asset('aceadmin/css/ace-ie.min.css'), NULL, PeepSo::PLUGIN_VERSION, 'all');
        $wp_styles->add_data('ace-admin-ie', 'conditional', 'IE 7');

        if ( is_rtl() ) {
            wp_register_style('peepso-admin', PeepSo::get_asset('css/admin-rtl.css'), NULL, PeepSo::PLUGIN_VERSION, 'all');
        } else {
            wp_register_style('peepso-admin', PeepSo::get_asset('css/admin.css'), NULL, PeepSo::PLUGIN_VERSION, 'all');
        }

        // core peepso libraries
        wp_register_script('peepso-core', PeepSo::get_asset('js/core.min.js'), array('jquery', 'underscore'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso-observer', FALSE, array('peepso-core'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso-npm', FALSE, array('peepso-core'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso-util', FALSE, array('peepso-core'), PeepSo::PLUGIN_VERSION, TRUE);

        // peepso window
        wp_register_script('peepso-window', FALSE, array('peepso-core'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_localize_script('peepso-window', 'peepsowindowdata', array(
            'label_confirm' => __('Confirm', 'peepso-core'),
            'label_confirm_delete' => __('Confirm Delete', 'peepso-core'),
            'label_confirm_delete_content' => __('Are you sure you want to delete this?', 'peepso-core'),
            'label_yes' => __('Yes', 'peepso-core'),
            'label_no' => __('No', 'peepso-core'),
            'label_delete' => __('Delete', 'peepso-core'),
            'label_cancel' => __('Cancel', 'peepso-core'),
            'label_okay' => __('Okay', 'peepso-core'),
        ));

        wp_register_script('peepso-modules', PeepSo::get_asset('js/modules.min.js'), array('peepso-core'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso-elements', PeepSo::get_asset('js/elements.min.js'), array('peepso-core'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso-sections', PeepSo::get_asset('js/sections.min.js'), array('peepso-core'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso', FALSE, array('peepso-core', 'peepso-modules', 'peepso-elements', 'peepso-sections'), PeepSo::PLUGIN_VERSION, TRUE);

        wp_register_script('peepso-admin-config', PeepSo::get_asset('js/peepso-admin-config.min.js'),
            array('jquery'), PeepSo::PLUGIN_VERSION, TRUE);

        // Hashtags
        if(PeepSo::get_option('hashtags_enable', 1)) {
            wp_enqueue_script('peepso-hashtags', PeepSo::get_asset('js/hashtags.min.js'), array('peepso'), PeepSo::PLUGIN_VERSION, TRUE);
            add_filter('peepso_data', function ($data) {
                $data['hashtags'] = array(
                    'url' => PeepSo::hashtag_url(),
                    'everything' => PeepSo::get_option('hashtags_everything', 0),
                    'min_length' => PeepSo::get_option('hashtags_min_length', 3 /* PeepSoHashtagsPlugin::CONFIG_MIN_LENGTH */),
                    'max_length' => PeepSo::get_option('hashtags_max_length', 16 /* PeepSoHashtagsPlugin::CONFIG_MAX_LENGTH */),
                    'must_start_with_letter' => PeepSo::get_option('hashtags_must_start_with_letter', 0)
                );
                return $data;
            }, 10, 1);
        }

        $data_sections = apply_filters('peepso_data_sections', array(
            'search' => array(
                'show_images' => PeepSo::get_option_new('peepso_search_show_images'),
                'show_empty_sections' => PeepSo::get_option_new('peepso_search_show_empty_sections'),
                'text_no_results' => __('No results.', 'peepso-core'),
            )
        ));

        $data_dialog = array(
            'text_title_default' => __('Dialog', 'peepso-core'),
            'text_title_error' => __('Error', 'peepso-core'),
            'text_title_confirm' => __('Confirm', 'peepso-core'),
            'text_button_cancel' => __('Cancel', 'peepso-core'),
            'text_button_ok' => __('OK', 'peepso-core'),
            'template' => PeepSoTemplate::exec_template('general', 'dialog', NULL, TRUE)
        );

        $data = apply_filters('peepso_data', array(
            'is_admin' => PeepSo::is_admin(),
            'home_url' => home_url(),
            'site_url' => site_url(),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'ajaxurl_legacy' => get_bloginfo('wpurl') . '/peepsoajax/',
            'version' => PeepSo::PLUGIN_VERSION,
            'postsize' => PeepSo::get_option('site_status_limit', 4000),
            'readmore_min' => PeepSo::get_option('site_activity_readmore', 1000),
            'readmore_min_single' => PeepSo::get_option('site_activity_readmore_single', 2000),
            'currentuserid' => get_current_user_id(),
            'userid' => apply_filters('peepso_user_profile_id', 0),		// user id of the user being viewed (from PeepSoProfileShortcode)
            'objectid' => apply_filters('peepso_object_id', 0),			// user id of the object being viewed
            'objecttype' => apply_filters('peepso_object_type', ''),	// type of object being viewed (profile, group, etc.)
            'date_format' => ps_dateformat_php_to_datepicker(get_option('date_format')),
            'members_page' => PeepSo::get_page('members'),
            'members_hide_before_search' => PeepSo::get_option('members_hide_before_search', 0),
            'open_in_new_tab' => PeepSo::get_option('site_activity_open_links_in_new_tab',1),
            'loading_gif' => PeepSo::get_asset('images/ajax-loader.gif'),
            'upload_size' => wp_max_upload_size(),
            'peepso_nonce' => wp_create_nonce('peepso-nonce'),
            // TODO: all labels and messages, etc. need to be moved into HTML content instead of passed in via js data
            // ART: Which template best suited to define the HTML content for these labels?
            // TODO: the one in which they're used. The 'Notice' string isn't used on all pages. Find the javascript that uses it and add it to that page's template
            'ajax_exception_text' => __('Something went wrong. Please contact the administrator.', 'peepso-core'),
            'label_error' => __('Error', 'peepso-core'),
            'label_notice' => __('Notice', 'peepso-core'),
            'mark_all_as_read_text' => __('Mark all as read', 'peepso-core'),
            'mark_all_as_read_confirm_text' => __('Are you sure you want to mark all notifications as read?', 'peepso-core'),
            'show_unread_only_text' => __('Show unread only', 'peepso-core'),
            'show_all_text' => __('Show all', 'peepso-core'),
            'view_all_text' => __('View all', 'peepso-core'),
            'read_more_text' => __('Read more', 'peepso-core'),
            'mime_type_error' => __('The file type you uploaded is not allowed.', 'peepso-core'),
            'login_dialog_title' => __('Please login to continue', 'peepso-core'),
            'login_dialog' => PeepSoTemplate::exec_template('general', 'login', NULL, TRUE),
            'login_with_email' => 2 === (int) PeepSo::get_option('login_with_email', 0),
            'like_text' => _n(' person likes this', ' people like this.', 1, 'peepso-core'),
            'like_text_plural' => _n(' person likes this', ' people like this.', 2, 'peepso-core'),
            'profile_unsaved_notice' => __('There are unsaved changes on this page.', 'peepso-core'),
            'profile_saving_notice' => __('The system is currently saving your changes.', 'peepso-core'),
            'comments_unsaved_notice' => __('Any unsaved comments will be discarded. Are you sure?', 'peepso-core'),
            'activity_limit_page_load' => PeepSoActivity::ACTIVITY_LIMIT_PAGE_LOAD,
            'activity_limit_below_fold' => apply_filters('peepso_filter_activity_limit_below_fold',PeepSoActivity::ACTIVITY_LIMIT_BELOW_FOLD),
            'loadmore_enable' => PeepSo::get_option('loadmore_enable', 0),
            'loadmore_repeat' => PeepSo::get_option('loadmore_repeat', 0),
            'get_latest_interval' => PeepSo::get_option('notification_ajax_delay', 30000),
            'external_link_warning' => PeepSo::get_option('external_link_warning', 0),
            'external_link_warning_page' => PeepSo::get_page('external_link_warning', 0),
            'external_link_whitelist' => apply_filters('external_link_whitelist', ''),
            'notification_ajax_delay_min' => PeepSo::get_option('notification_ajax_delay_min', 5000),
            'notification_ajax_delay' => PeepSo::get_option('notification_ajax_delay', 30000),
            'notification_ajax_delay_multiplier' => PeepSo::get_option('notification_ajax_delay_multiplier', 1.5),
            'notification_url' => PeepSo::get_page('notifications'),
            'sse' => PeepSo::get_option('sse', 0),
            'sse_url' => ! empty( PeepSo::get_option('sse_backend_url', '') ) ? PeepSo::get_option('sse_backend_url', '') : plugin_dir_url( __FILE__ ) . 'sse.php',
            'sse_domains' => array( PeepSo::get_option('sse_backend_url', home_url()) ),
            'sse_backend_delay' => PeepSo::get_option('sse_backend_delay', 5000),
            'sse_backend_timeout' => PeepSo::get_option('sse_backend_timeout', 30000),
            'sse_backend_keepalive' => PeepSo::get_option('sse_backend_keepalive', 5),
            'auto_rtl' => PeepSo::is_dev_mode('auto_rtl'),
            'show_powered_by'=> $free_bundle,
            'powered_by' => isset($free_bundle_branding) ? $free_bundle_branding : '',
            'sections' => $data_sections,
            'dialog' => $data_dialog,
        ));

        wp_localize_script('peepso-core', 'peepsodata', $data);
        wp_enqueue_script('peepso');

        wp_enqueue_script('peepso-admin-config');

        if ( is_rtl() ) {
            wp_enqueue_style('peepso', PeepSo::get_template_asset(NULL, 'css/admin/peepso-rtl.css'), NULL, PeepSo::PLUGIN_VERSION, 'all');
            wp_enqueue_style('peepso-backend', PeepSo::get_asset('css/backend-rtl.css'), NULL, PeepSo::PLUGIN_VERSION, 'all');
        } else {
            wp_enqueue_style('peepso', PeepSo::get_template_asset(NULL, 'css/admin/peepso.css'), NULL, PeepSo::PLUGIN_VERSION, 'all');
            wp_enqueue_style('peepso-backend', PeepSo::get_asset('css/backend.css'), NULL, PeepSo::PLUGIN_VERSION, 'all');
        }

        wp_enqueue_style('peepso-icons-new', PeepSo::get_asset('css/icons.css'), NULL, PeepSo::PLUGIN_VERSION, 'all');
        wp_enqueue_style('peepso-icons', PeepSo::get_template_asset(NULL, 'css/admin/icons.css'), NULL, PeepSo::PLUGIN_VERSION, 'all');

        // if version < 3.9 include dashicons
        global $wp_version;
        if (version_compare($wp_version, '3.9', 'lt')) {
            wp_register_style('peepso-dashicons', PeepSo::get_asset('css/dashicons.css'),
                array(), PeepSo::PLUGIN_VERSION, 'all');
            wp_enqueue_style('peepso-dashicons');
        }

        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');

        wp_enqueue_script('peepso-notification', PeepSo::get_asset('js/notifications.min.js'), array('jquery', 'jquery-ui-position', 'underscore', 'peepso', 'peepso-observer'), PeepSo::PLUGIN_VERSION, TRUE);

        // Bundled peepso scripts.
        wp_enqueue_script('peepso-bundle', PeepSo::get_asset('js/bundle.min.js'),
            array('jquery-ui-position', 'peepso', 'peepso-window'), PeepSo::PLUGIN_VERSION, TRUE);

        // Lightbox.
        wp_register_script('peepso-lightbox', FALSE, array('peepso-bundle'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_localize_script('peepso-bundle', 'peepsolightboxdata', array(
            'template' => PeepSoTemplate::exec_template('general', 'lightbox', NULL, TRUE)
        ));

        // Auto-update time label script.
        wp_register_script('peepso-time', FALSE, array('peepso-bundle'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_localize_script('peepso-bundle', 'peepsotimedata', array(
            'ts'     => current_time('U'),
            'now'    => __('just now', 'peepso-core'),
            // Take account of the non-English plural forms (e.g. Arabic).
            // https://medium.com/@learningarabicwithangela/the-dual-in-arabic-language-al-muthanna-e7613051ef5f
            'min_1'   => sprintf( __('%s ago', 'peepso-core'), _n('%s min', '%s mins', 1, 'peepso-core') ),
            'min_2'   => sprintf( __('%s ago', 'peepso-core'), _n('%s min', '%s mins', 2, 'peepso-core') ),
            'min_3'   => sprintf( __('%s ago', 'peepso-core'), _n('%s min', '%s mins', 3, 'peepso-core') ),
            'hour_1'  => sprintf( __('%s ago', 'peepso-core'), _n('%s hour', '%s hours', 1, 'peepso-core') ),
            'hour_2'  => sprintf( __('%s ago', 'peepso-core'), _n('%s hour', '%s hours', 2, 'peepso-core') ),
            'hour_3'  => sprintf( __('%s ago', 'peepso-core'), _n('%s hour', '%s hours', 3, 'peepso-core') ),
            'day_1'   => sprintf( __('%s ago', 'peepso-core'), _n('%s day', '%s days', 1, 'peepso-core') ),
            'day_2'   => sprintf( __('%s ago', 'peepso-core'), _n('%s day', '%s days', 2, 'peepso-core') ),
            'day_3'   => sprintf( __('%s ago', 'peepso-core'), _n('%s day', '%s days', 3, 'peepso-core') ),
            'week_1'  => sprintf( __('%s ago', 'peepso-core'), _n('%s week', '%s weeks', 1, 'peepso-core') ),
            'week_2'  => sprintf( __('%s ago', 'peepso-core'), _n('%s week', '%s weeks', 2, 'peepso-core') ),
            'week_3'  => sprintf( __('%s ago', 'peepso-core'), _n('%s week', '%s weeks', 3, 'peepso-core') ),
            'month_1' => sprintf( __('%s ago', 'peepso-core'), _n('%s month', '%s months', 1, 'peepso-core') ),
            'month_2' => sprintf( __('%s ago', 'peepso-core'), _n('%s month', '%s months', 2, 'peepso-core') ),
            'month_3' => sprintf( __('%s ago', 'peepso-core'), _n('%s month', '%s months', 3, 'peepso-core') ),
            'year_1'  => sprintf( __('%s ago', 'peepso-core'), _n('%s year', '%s years', 1, 'peepso-core') ),
            'year_2'  => sprintf( __('%s ago', 'peepso-core'), _n('%s year', '%s years', 2, 'peepso-core') ),
            'year_3'  => sprintf( __('%s ago', 'peepso-core'), _n('%s year', '%s years', 3, 'peepso-core') ),
        ));
        wp_enqueue_script('peepso-time');

        // Enqueue page-specific peepso admin page script.
        if (isset($_GET['page'])) {
            $page = $_GET['page'];

            if ('peepso' === $page) {
                $path = 'js/admin/dashboard.js';
            } else if ('peepso_config' === $page) {
                $tab = isset($_GET['tab']) ? $_GET['tab'] : 'site';
                $page = 'config-' . $tab;
                $path = 'js/admin/' . $page . '.js';
            } else if ('peepso-queue' === $page) {
                $tab = isset($_GET['tab']) ? $_GET['tab'] : 'email';
                $page = 'queue-' . $tab;
                $path = 'js/admin/' . $page . '.js';
            } else if ('peepso-manage' === $page) {
                $tab = isset($_GET['tab']) ? $_GET['tab'] : 'reports';
                $page = 'manage-' . $tab;
                $path = 'js/admin/' . $page . '.js';
            } else {
                $page = str_replace('peepso-', '', $page);
                $path = 'js/admin/' . $page . '.js';
            }

            if ( isset($path) ) {
                // General peepso admin script.
                wp_enqueue_script('peepso-admin',
                    PeepSo::get_asset('js/admin/index.js'), array('jquery'),
                    PeepSo::PLUGIN_VERSION, TRUE);

                if ( file_exists(PeepSo::get_plugin_dir() . 'assets/' . $path) ) {
                    wp_enqueue_script('peepso-admin-' . $page,
                        PeepSo::get_asset($path), array('jquery'),
                        PeepSo::PLUGIN_VERSION, TRUE);
                }
            }
        }
    }

    /*
     * return list of tab items for PeepSo Dashboard display
     */
    public function get_tabs()
    {
        if (NULL === $this->dashboard_tabs) {
            global $wpdb;



            $msg_count = PeepSoMailQueue::get_pending_item_count();
            $req_count = PeepSoGdpr::get_pending_item_count();

            $installer_new = '';
            if (!PeepSoSystemRequirements::is_demo_site()) {
                PeepSo3_Helper_Addons::get_addons();
                if (PeepSo3_Helper_Addons::maybe_installer_has_new()) {
                    $installer_new = ' <span style="color:orange"><i class="gcis gci-star"></i> NEW!';
                }
            }

            $tabs = array(
                'red' => array(
                    'config' => array(
                        'slug' => PeepSoConfig::$slug,
                        'menu' => __('Configuration', 'peepso-core'),
                        'icon' => 'admin-settings',
                        'function' => array('PeepSoConfig', 'init')
                    ),
                    'manage' => array(
                        'slug' => PeepSoManage::$slug,
                        'menu' => __('Manage', 'peepso-core'),
                        'icon' => 'list-view',
                        'function' => array('PeepSoManage', 'init')
                    ),
                    'queue' => array(
                        'slug' => 'peepso-queue', // peepso-messages',
                        'menu' => __('Queues', 'peepso-core'),
                        'icon' => 'email', // 'envelope',               // dashicons-email
                        // 'count' => intval($msg_count),
                        'function' => array('PeepSoQueue', 'init'),
                    ),
                    'addon' => array(
                        'slug' => 'peepso-installer',
                        'menu' => '<span style="color:#3f9f2e">' . __('Installer', 'peepso-core') . '</span>' . $installer_new,
                        'icon' => 'admin-plugins',
                        'function' => array('PeepSoAdmin', 'addons'),
                        //'count' => __('NEW!', 'peepso-core'),
                    ),
                ),
            );

            if (PeepSoSystemRequirements::is_demo_site()) {
                unset($tabs['red']['addon']);
            }

            // #6618 PFB CTA
            if(PeepSo3_Helper_Addons::license_is_eligible_upgrade()) {
                $tabs['red']['upgrade'] = array(
                    'slug' => 'https://peepso.com/Pricing/ref/429/',
                    'menu' => '<span class="wp-submenu_upgrade-peepso"><i class="gcis gci-gem"></i> <span>' . __('Upgrade Now!', 'peepso-core') . '</span></span>',
                    'icon' => 'admin-plugins',
                    // 'function' => array('PeepSoAdmin', 'addons'),
                    //'count' => __('NEW!', 'peepso-core'),
                );
            }

            if (isset($_GET['page']) && 'peepso_config' === $_GET['page']) {
                $cfg = PeepSoConfig::get_instance();
                $cfg_tabs = $cfg->get_tabs();
                $list = '';
                foreach ($cfg_tabs as $cfg_tab => $cfg_data) {
                    $list .= '<li><a href="' . admin_url('admin.php?page=peepso_config&tab=' . $cfg_data['tab']) . '">';
                    $list .= '&raquo;&nbsp;' . $cfg_data['label'] . '</a></li>';
                }
                $tabs['red']['config']['submenu'] = '</a>' .
                    '<ul class="wp-submenu wp-submenu-wrap">' .
                    $list .
                    '</ul>';
            }

            if (isset($_GET['page']) && 'peepso-queue' === $_GET['page']) {
                $cfg = PeepSoQueue::get_instance();
                $cfg_tabs = $cfg->get_tabs();
                $list = '';
                foreach ($cfg_tabs as $cfg_tab => $cfg_data) {
                    $list .= '<li><a href="' . admin_url('admin.php?page=peepso-queue&tab=' . $cfg_data['tab']) . '">';
                    $list .= '&raquo;&nbsp;' . $cfg_data['label'] . '</a></li>';
                }
                $tabs['red']['queue']['submenu'] = '</a>' .
                    '<ul class="wp-submenu wp-submenu-wrap">' .
                    $list .
                    '</ul>';
            }

            if (isset($_GET['page']) && 'peepso-manage' === $_GET['page']) {
                $cfg = PeepSoManage::get_instance();
                $cfg_tabs = $cfg->get_tabs();
                $list = '';
                foreach ($cfg_tabs as $cfg_tab => $cfg_data) {
                    $list .= '<li><a href="' . admin_url('admin.php?page=peepso-manage&tab=' . $cfg_data['tab']) . '">';
                    $list .= '&raquo;&nbsp;' . $cfg_data['label'] . '</a></li>';
                }
                $tabs['red']['manage']['submenu'] = '</a>' .
                    '<ul class="wp-submenu wp-submenu-wrap"">' .
                    $list .
                    '</ul>';
            }

            $tabs = apply_filters('peepso_admin_dashboard_tabs', $tabs);
            $this->dashboard_tabs = &$tabs;

            $this->tab_count = 0;
            foreach ($tabs as $color => $tabitems)
                $this->tab_count += count($tabitems);
        }

        return ($this->dashboard_tabs);
    }


    private static function perform_activation($category)
    {

        $addons = [];
        $is_theme = ('themes' == $category) ? TRUE : FALSE;

        $request = 'activate_'.$category;

        if(isset($_REQUEST[$request])) {
            $addons = $_REQUEST[$request];
        }

        $license = PeepSo3_Helper_Addons::get_license();

        if( is_array($addons) && count($addons) ) {

            $success = 0;
            $errors = 0;
            $debug = '';

            foreach ($addons as $addon) {

                if ($is_theme) {
                    $activation = switch_theme($addon);
                    update_option('gecko_options', ['gecko_license' => $license]);
                }else {
                    $activation = activate_plugin($addon);
                }

                if ($activation instanceof WP_Error) {
                    ob_start();
                    foreach ($activation->errors as $error) {
                        $errors++;
                        $addon_escaped = esc_html($addon);
                        echo "<li><i class='gcis gci-exclamation-triangle'></i> <code>$addon_escaped</code>: {$error[0]}</li>";
                    }
                    $debug .= ob_get_clean();
                    continue;
                }

                // It seems to have worked?
                $success++;
            }

            if($success > 0) {
                echo "<div class='notice notice-success is-dismissible peepso'><h4>";
                if($is_theme) {
                    echo sprintf(_n('Theme activated','%d themes activated',$success, 'peepso-core'), $success);
                } else {
                    echo sprintf(_n('Plugin activated','%d plugins activated',$success, 'peepso-core'), $success);
                }

                echo "</h4></div>";
            }

            if(strlen($debug)) { ?>
                <div class='error peepso is-dismissible pa-activation-error'>
                    <h4>
                        <?php echo sprintf(_n('%d activation error','%d activation errors',$errors, 'peepso-core'), $errors);?>
                    </h4>

                    <b>Need assistance? We are here to help! <a class="ps-emphasis" href="https://www.PeepSo.com/contact" target="_blank">Contact PeepSo</b></a>.
                    <br/>
                    <ul>
                        <?php echo $debug;?>
                    </ul>


                </div>
                <?php
            }
        }
    }

    public static function addons() {

        if(isset($_REQUEST['activate_plugins'])) {
            self::perform_activation('plugins');
        }

        if(isset($_REQUEST['activate_themes'])) {
            self::perform_activation('themes');
        }

        PeepSoAdmin::admin_header(__('Installer', 'peepso-core'));

        wp_register_script('peepso-admin-addons', PeepSo::get_asset('js/admin-addons.js'),
            array('jquery', 'peepso'), PeepSo::PLUGIN_VERSION, TRUE);


        wp_localize_script('peepso-admin-addons', 'peepsoadminaddonsdata', array(
            'label' => [
                'install' => __('Install', 'peepso-core'),
                'installing' => __('Installing...', 'peepso-core'),
                'installed' => __('Installed', 'peepso-core'),
                'install_failed' => __('Failed to install', 'peepso-core'),
                'not_installed' => __('Not installed', 'peepso-core'),
                'active' => __('Active', 'peepso-core'),
                'activate' => __('Activate', 'peepso-core'),
                'activating' => __('Activating...', 'peepso-core'),
                'activated' => __('Activated', 'peepso-core'),
                'activate_failed' => __('Failed to activate', 'peepso-core'),
                'inactive' => __('Inactive', 'peepso-core'),
                'your_license' => __('Your license', 'peepso-core'),

                // Activate theme warning message.
                'activate_theme_warning_title' => 'You are about to switch the active theme on your site!',
                'activate_theme_warning_message' => 'You chose to activate the <b>Gecko Theme</b>, which is a fully featured WordPress Theme. Activating it will <b>immediately switch your entire site to the Gecko Theme</b>.<br/><br/>Do you want to continue and <b>switch your site theme to Gecko</b>?',
                'activate_theme_warning_btn_cancel' => '<b>Keep my old theme</b>',
                'activate_theme_warning_btn_confirm' => '<b>Activate Gecko</b>',

                'license_check_error_message' => '<strong>'.PeepSo3_Helper_PeepSoAJAX_Online::get_message('installer').'</strong><br/>',
                'license_check_error_description' => PeepSo3_Helper_PeepSoAJAX_Online::get_description(),
            ]
        ));

        wp_enqueue_script('peepso-admin-addons');
        $license = PeepSo3_Helper_Addons::get_license();

        if(PeepSo::is_admin()) {

            if( isset($_GET['action']) && ($_GET['action'] == 'peepso-free' || $_GET['action'] == 'peepso-free-accept') ) {

            }elseif(!get_user_option('peepso_user_installer_tutorial', get_current_user_id())) {
                PeepSoTemplate::exec_template('admin', 'addons_tutorial');
            }

            PeepSoTemplate::exec_template('admin', 'addons', [
                'license' => $license
            ]);
        }
    }


    /**
     * Add notice with type and message
     * @param string $notice The message to display in an Admin Notice
     * @param string $type The type of notice. One of: 'error', 'warning', 'info', 'note', 'none'
     */
    public function add_notice($notice, $type = 'error')
    {
        $types = array(
            'error' => 'error',
            'warning' => 'update-nag',
            'info' => 'check-column',
            'note' => 'updated',
            'none' => '',
        );
        if (!array_key_exists($type, $types))
            $type = 'none';

        $notice_data = array('class' => $types[$type], 'message' => $notice);

        $key = self::NOTICE_KEY . get_current_user_id();
        $notices = PeepSo3_Mayfly::get($key);

        if (NULL === $notices) {
            $notices = array( $notice_data );
        }

        // only add the message if it's not already there
        $found = FALSE;
        foreach ($notices as $notice) {
            if ($notice_data['message'] === $notice['message'])
                $found = TRUE;
        }

        if (!$found) {
            $notices[] = $notice_data;
        }

        PeepSo3_Mayfly::set($key, $notices, self::NOTICE_TTL);
    }

    // TODO: let's try to remove this and do away with output buffering
    public function do_output_buffer()
    {
        ob_start();
    }


    /*
     * Update the columns displayed for the WP user list
     * @param array $columns The current columns to display in the user list
     * @return array The modified column list
     */
    public function filter_user_list_columns($columns)
    {
        $ret = array();
        foreach ($columns as $key => $value) {
            // remove the 'Posts' column
            if ('posts' === $key)
                continue;
            $ret[$key] = $value;
            // add the PeepSo Role column after the WP Role column
            if ('role' === $key)
                $ret['peepso_role'] = __('PeepSo Role', 'peepso-core');
        }
        return ($ret);
    }

    /**
     * Filters the list of view links, adding some for PeepSo roles
     * @param array $views List of views
     * @return array The modified list of views
     */
    public function filter_user_views($views)
    {
        $usradm = PeepSoUser::get_instance();
        $res = $usradm->get_counts_by_role();
        if (is_array($res)) {
            foreach ($res as $row) {
                $translated_roles = $this->get_translated_roles();

                $link = '<a href="users.php?psrole=' . $row['role'] . '">' . $translated_roles[$row['role']] . ' <span class="count">(' . $row['count'] . ')</span></a>';
                $views[$row['role']] = $link;
            }
        }
        return ($views);
    }

    /**
     * Filters the custom column, displaying the PeepSo Role value for the indicated user
     * @param string $value Filter value
     * @param string $column The name of the column
     * @param int $id The user id for the row being displayed
     * @return string Appropriate column value for the user being displayed
     */
    public function filter_custom_user_column($value, $column, $id)
    {
        switch ($column)
        {
            case 'peepso_role':
                $roles = $this->get_roles();
                $translated_roles = $this->get_translated_roles();

                $user = PeepSoUser::get_instance($id);
                $role = $user->get_user_role();

                // Fallback for removed legacy user roles
                if(!array_key_exists($role, $roles)) {
                    $role = 'member';
                    $user->set_user_role($role);
                }

                ob_start();
                echo esc_attr($roles[$role]);
                $title = ob_get_clean();

                ob_start();
                if('register' == $role) {
                    $date = PeepSo3_Mayfly::get('user_' . $user->get_id() .'_send_activation_last_attempt_date');
                    $count = PeepSo3_Mayfly::get('user_' . $user->get_id() .'_send_activation_count');
                    $trigger = PeepSo3_Mayfly::get('user_' . $user->get_id() .'_send_activation_last_attempt_trigger');

                    if(!in_array(NULL, [$date, $count, $trigger])) {

                        echo "\n\n";
                        echo $count . ' total attempts';

                        echo "\n\n";
                        echo current_time('mysql') . ' - server time now';

                        echo "\n";
                        echo $date . ' - last attempt';

                        $name = $trigger;
                        if(is_numeric($name)) {
                            $name = (int) $name;
                            if($name == $user->get_id()) {
                                $name = $user->get_fullname();
                            } else {
                                $other_user = PeepSoUser::get_instance($name);
                                $name = $other_user->get_fullname();
                            }
                        }

                        echo "\n\n";
                        echo  "Last attempted by $name";


                    } else {
                        echo "\n\nNo attempt data found\nUser registered before PeepSo 3?";
                    }
                }
                $meta = ob_get_clean();

                $title.=$meta;



                $value = '<span title="' . $title . '">' .
                    $translated_roles[$role] . '</span>';

                if(isset($_GET['resend_activation_debug'])) {
                    $value.="<br/><pre>$meta</pre>";
                }


                break;
        }
        return ($value);
    }

    /**
     * Filters the WP_User_Query, adding the WHERE clause to look for PeepSo roles
     * @param WP_User_query $query The query object to filter
     * @return WP_User_Query The modified query object
     */
    public function filter_user_query($query)
    {
        global $wpdb;
        $input = new PeepSoInput();

        $query->query_from .= " LEFT JOIN `{$wpdb->prefix}" . PeepSoUser::TABLE . "` ON `{$wpdb->users}`.ID = `usr_id` ";
        $query->query_where .= " AND `usr_role`='" . esc_sql($input->value('psrole', 'member', FALSE)) . '\' '; // SQL Safe
        return ($query);
    }

    /**
     * Performs updates on the user selected via the Bulk Action checkboxes
     * @param object $screen The current screen object
     * @return type
     */
    public function update_user_roles($screen)
    {
        switch ($screen->base)
        {
            case 'toplevel_page_peepso' :
                $input = new PeepSoInput();

                $action = $input->value('action', '', FALSE); // SQL Safe
                $set = $input->value('set', '', FALSE); // SQL Safe
                $id = $input->int('id');

                // SQL safe, WP sanitizes it
                $_wpnonce = $input->value('_wpnonce','',FALSE);

                if (
                    $action === 'update-user-role' &&
                    ($set === 'member' || $set == 'ban') &&
                    wp_verify_nonce($_wpnonce, 'update-role-nonce_' . $id)
                )
                {
                    $user = PeepSoUser::get_instance($id);

                    switch ($set)
                    {
                        case 'member' :
                            $adm = PeepSoUser::get_instance($id);
                            $adm->approve_user();

                            // update the user with their new role
                            $user->set_user_role('member');

                            $this->add_notice(__(trim(strip_tags($user->get_fullname(TRUE)))  . ' approved', 'peepso-core'), 'note');
                            break;
                        case 'ban' :
                            $user->set_user_role('ban');

                            $this->add_notice(__(trim(strip_tags($user->get_fullname(TRUE))) . ' banned', 'peepso-core'), 'note');
                            break;
                    }
                } else if (isset($action) && $action === 'update-user-role')
                {
                    $this->add_notice(__('Invalid action', 'peepso-core'), 'error');
                }
                break;
            case 'users' :
                // if there is a PeepSo Role filter requestsed, add the WP_Users_query filter
                if (isset($_GET['psrole']))
                    add_filter('pre_user_query', array(&$this, 'filter_user_query'));
                if ('GET' === $_SERVER['REQUEST_METHOD']) {
                    $input = new PeepSoInput();
                    $role0  = strtolower($input->value('peepso-role-select', '0', FALSE)); // SQL Safe
                    $role2  = strtolower($input->value('peepso-role-select2', '0', FALSE)); // SQL Safe
                    $role   = $role2 != '0' ? $role2 : ( $role0 != '0' ? $role0 : '0' );
                    if ('0' !== $role) {
                        // verify that the form is valid
                        if (!current_user_can('edit_users')) {
                            $this->add_notice(__('You do not have permission to do that.', 'peepso-core'), 'error');
                            return;
                        }
                        if (!wp_verify_nonce($input->value('ps-role-nonce', '', FALSE),'psrole-nonce')) { // SQL Safe
                            $this->add_notice(__('Form is invalid.', 'peepso-core'), 'error');
                            return;
                        }
                        // $users = (isset($_GET['users']) ? $_GET['users'] : array());
                        $users = $input->value('users', array(), FALSE); // SQL Safe
                        $roles = $this->get_roles();
                        if (in_array($role, array_keys($roles)) && 0 < count($users)) {
                            foreach ($users as $user_id) {
                                $user = PeepSoUser::get_instance($user_id);
                                $old_role = $user->get_user_role();

                                if ('admin' === $role) {
                                    $block = new PeepSoBlockUsers();
                                    $block->delete_from_blocked($user_id);
                                }

                                // perform approval; sends welcome email
                                if ('member' === $role) {
                                    if ('member' === $role && 'verified' === $old_role) {
                                        $adm = PeepSoUser::get_instance($user_id);
                                        $adm->approve_user();
                                    }
                                }
                                // update the user with their new role
                                //                      $data = array('usr_role' => $role);
                                //                      $user->update_peepso_user($data);
                                $user->set_user_role($role);
                            }
                        }
                    } else {
                        if (isset($_GET['change-peepso-role']))
                            $this->add_notice(__('Please select a PeepSo Role before clicking on "Change Role".', 'peepso-core'), 'warning');
                    }
                }
                break;
        }
    }

    /**
     * Outputs UI controls for setting the User roles
     */
    public function peepso_roles()
    {
        static $counter = 0;
        $role_extra =  $counter != 0 ?  2 : '';

        echo '<div id="peepso-role-wrap" style="vertical-align: baseline">';
        echo '<span>';
        echo __('Set PeepSo Role:', 'peepso-core'), '&nbsp;&nbsp;';
        echo '<select id="peepso-role-select" name="peepso-role-select'.$role_extra.'">';
        echo '<option value="0">', __(' - Select Role -', 'peepso-core'), '</option>';
        $roles = $this->get_roles();
        $translated_roles = $this->get_translated_roles();
        foreach ($roles as $name => $desc) {
            echo '<option value="', $name, '">', $translated_roles[$name], '</option>';
        }
        echo '</select>';
        echo '<input type="hidden" name="ps-role-nonce" value="', wp_create_nonce('psrole-nonce'), '" />';
        echo '<input type="submit" name="change-peepso-role" id="change-peepso-role" class="button" value="', __('Change Role', 'peepso-core'), '">';
        echo '</span>';
        echo '</div>';
        echo '<style>';
        echo '#peepso-role-wrap { display: inline-block; margin-left: 1em; padding: 3px 5px; }';
        echo '#peepso-role-wrap span { bottom; padding-top: 2em }';
        echo '#peepso-role-wrap #peepso-role-select { float:none; }';
        echo '</style>';

        $counter++;
    }

    /**
     * Get a list of the Roles recognized by PeepSo
     * @return Array The list of Roles
     */
    public function get_roles()
    {
        $ret = array(
            'member' => __('Full member, can write posts and participate', 'peepso-core'),
            #'moderator' => __('Full member, can moderate posts', 'peepso-core'),
            'admin' => __('PeepSo Administrator, can Moderate, edit users, etc.', 'peepso-core'),
            'ban' => __('Banned, cannot login or participate', 'peepso-core'),
            'register' => __('Registered, awaiting email verification', 'peepso-core'),
            'verified' => __('Verified email, awaiting Adminstrator approval', 'peepso-core'),
            #'user' => __('Standard user account', 'peepso-core'),
        );

        // TODO: before we can allow filtering/adding to this list we need to change the `peepso_users`.`usr_role` column
        return ($ret);
    }

    public function get_translated_roles()
    {
        $ret = array(
            'member'    => __('Community Member',   'peepso'),
            'moderator' => __('Community Moderator', 'peepso-core'),
            'admin'     => __('Community Administrator',    'peepso'),
            'ban'       => __('Banned',         'peepso'),
            'register'  => __('Pending user email verification',    'peepso'),
            'verified'  => __('Pending admin approval',     'peepso'),
            #'user'         => __('role_user',      'peepso'),

        );

        foreach($ret as $k=>$v) {
            if(stristr($v, 'role_')) {
                $ret[$k] = ucwords($k);
            }
        }

        return $ret;
    }


    /*
     * Filter the avatar so that the PeepSo avatar is displayed
     * @param string $avatar The avatar HTML content
     * @param midxed $id_or_email The user id or email address of the user
     * @param int $size The size of the avatar to create
     * @param mixed $default
     * @param string $alt Alternate text
     */


    /*
     * Add a link to the user's profile page to the actions
     * @param array $actions The current list of actions
     * @param WP_User $user The WP_User instance
     * @return array List of actions, with a profile link added
     */
    public function filter_user_actions($actions, $user = NULL)
    {
        // add the 'Profile Link' action to the list of actions
        $user = PeepSoUser::get_instance($user->ID);
        $actions['profile'] = '<a href="' . $user->get_profileurl(FALSE) . '" target="_blank">' . __('Profile Link', 'peepso-core') . '</a>';
        return ($actions);
    }

    /**
     * Enqueues scripts after the config page has been loaded
     */
    public function config_page_loaded()
    {
        add_action('admin_enqueue_scripts', array(&$this, 'enqueue_ace_admin_scripts'));
    }

    /**
     * Enqueues the admin dashboard assets
     */
    public function enqueue_ace_admin_scripts()
    {
        wp_enqueue_style('ace-admin-boostrap-min');
        wp_enqueue_style('ace-admin');
        wp_enqueue_style('ace-admin-fontawesome');
        wp_enqueue_style('peepso-admin');
    }

    /**
     * Enqueues scripts when the peepso backend is accessed
     */
    public function toplevel_page_peepso_loaded()
    {
        wp_register_script('flot', PeepSo::get_asset('aceadmin/js/flot/jquery.flot.min.js'),
            array('jquery'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('flot-pie', PeepSo::get_asset('aceadmin/js/flot/jquery.flot.pie.min.js'),
            array('flot'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('flot-time', PeepSo::get_asset('aceadmin/js/flot/jquery.flot.time.js'),
            array('flot'), PeepSo::PLUGIN_VERSION, TRUE);
        wp_register_script('peepso-admin-dashboard', PeepSo::get_asset('js/admin-dashboard.min.js'),
            array('flot'), PeepSo::PLUGIN_VERSION, TRUE);

        wp_localize_script('peepso-admin-dashboard', 'peepsoadmindashboarddata', array(
            'user_id' => get_current_user_id()
        ));

        wp_enqueue_script('flot');
        wp_enqueue_script('flot-time');
        wp_enqueue_script('flot-pie');
        wp_enqueue_script('peepso-admin-dashboard');
    }

    /**
     * Calls add_meta_box for every metabox defined in define_dashboard_metaboxes()
     */
    public function prepare_metaboxes()
    {
        foreach ($this->dashboard_metaboxes as $metabox) {
            add_meta_box(
                'peepso_dashboard_' . $metabox['name'], // meta box ID
                $metabox['title'],                      // meta box Title
                $metabox['callback'],                   // callback defining the plugin's innards
                'toplevel_page_peepso',                 // screen to which to add the meta box
                isset($metabox['context']) ? $metabox['context'] : 'left', // context
                'default');
        }
    }

    /*
     * Defines the default metaboxes for the dashboard
     */
    public function define_dashboard_metaboxes()
    {
        $dashboard_metaboxes = array();

        $translated_roles = $this->get_translated_roles();

        if (count(get_user_meta(get_current_user_id() , 'peepso_admin_newsletter_subscribe')) === 0) {
            $dashboard_metaboxes[] = array(
                'name' => 'newsletter',
                'title' => __('Get Free eBook Now! ($9.99 Value)', 'peepso-core'),
                'callback' => array(&$this, 'newsletter_metabox'),
                'context' => 'left'
            );
        }

        $dashboard_metaboxes[] = array(
            'name' => 'user_engagement',
            'title' => __('User Engagement', 'peepso-core'),
            'callback' => array(&$this, 'engagement_metabox'),
            'context' => 'left'
        );

        $dashboard_metaboxes[] = array(
            'name' => 'pending_members',
            'title' => __('Users', 'peepso-core') . ' - ' . $translated_roles['verified'],
            'callback' => array(&$this, 'pending_members_metabox'),
            'context' => 'left'
        );
        $dashboard_metaboxes[] = array(
            'name' => 'reported_items',
            'title' => __('Reported Items', 'peepso-core'),
            'callback' => array(&$this, 'reported_items_metabox'),
            'context' => 'left'
        );

        $upsell = PeepSo3_Helper_Addons::get_upsell();
        if(PeepSo3_Utilities_String::maybe_strlen($upsell)) {
            $dashboard_metaboxes[] = array(
                'name' => 'bundle',
                'title' => __('PeepSo Bundles', 'peepso-core'),
                'callback' => array(&$this, 'upsell'),
                'context' => 'right'
            );
        }

        $dashboard_metaboxes[] = array(
            'name' => 'most_recent',
            'title' => __('Most Recent Content', 'peepso-core'),
            'callback' => array(&$this, 'recent_metabox'),
            'context' => 'right'
        );
        $dashboard_metaboxes[] = array(
            'name' => 'demographic',
            'title' => __('User Demographics', 'peepso-core'),
            'callback' => array(&$this, 'demographic_metabox'),
            'context' => 'right'
        );



        $this->dashboard_metaboxes = $dashboard_metaboxes;

    }

    public function system_metabox()
    {
        if(count($this->messages['errors'])) {
            ?>
            <ul style="color:red">
                <?php foreach($this->messages['errors'] as $e) { ?>
                    <li><?php echo $e;?></li>
                <?php } ?>
            </ul>
            <?php
        }

        if(count($this->messages['warnings'])) {
            ?>
            <ul style="color:darkorange">
                <?php foreach($this->messages['warnings'] as $e) { ?>
                    <li><?php echo $e;?></li>
                <?php } ?>
            </ul>
            <?php
        }
    }

    public function newsletter_metabox()
    {
        $user = wp_get_current_user();
        ?>

        <div class="row">

            <?php

            if(isset($_GET['newsletter_purge'])) {
                PeepSo3_Mayfly::del('peepso_newsletter_form');
                PeepSo3_Mayfly::del('peepso_newsletter_form_new');
            }

            if(!isset($_GET['newsletter_test'])) {
                $newsletter_form = PeepSo3_Mayfly::get('peepso_newsletter_form');

                if (empty($newsletter_form)) {
                    $url = PeepSoAdmin::PEEPSO_URL . '/peepsotools-integration-json/newsletter.txt';

                    // Attempt contact with PeepSo.com without sslverify
                    $resp = wp_remote_get(add_query_arg(array(), $url), array('timeout' => 10, 'sslverify' => FALSE));

                    // In some cases sslverify is needed
                    if (is_wp_error($resp)) {
                        $resp = wp_remote_get(add_query_arg(array(), $url), array('timeout' => 10, 'sslverify' => TRUE));
                    }

                    if (is_wp_error($resp)) {

                    } else {
                        $newsletter_form = $resp['body'];
                        PeepSo3_Mayfly::set('peepso_newsletter_form', $newsletter_form, 3600 * 24);
                    }
                }
            } else {
                $newsletter_form = PeepSo3_Mayfly::get('peepso_newsletter_form_new');

                if (!strlen($newsletter_form)) {
                    $url = PeepSoAdmin::PEEPSO_URL . '/peepsotools-integration-json/newsletter_new.txt';

                    // Attempt contact with PeepSo.com without sslverify
                    $resp = wp_remote_get(add_query_arg(array(), $url), array('timeout' => 10, 'sslverify' => FALSE));

                    // In some cases sslverify is needed
                    if (is_wp_error($resp)) {
                        $resp = wp_remote_get(add_query_arg(array(), $url), array('timeout' => 10, 'sslverify' => TRUE));
                    }

                    if (is_wp_error($resp)) {

                    } else {
                        $newsletter_form = $resp['body'];
                        PeepSo3_Mayfly::set('peepso_newsletter_form_new', $newsletter_form, 3600 * 24);
                    }
                }
            }

            $from = array(
                '{firstname}',
                '{lastname}',
                '{email}',
            );

            $user = wp_get_current_user();

            $to = array(
                $user->user_firstname,
                $user->user_lastname,
                $user->user_email,
            );

            echo str_ireplace($from, $to, $newsletter_form);

            ?>

        </div>

        <?php
    }



    /**
     * Renders the demographic metabox on the dashboard
     */
    public function demographic_metabox()
    {
        $peepso_user_model = PeepSoUser::get_instance();
        // Should this be 'm'?
        $males = $peepso_user_model->get_count_by_gender('m');
        $females = $peepso_user_model->get_count_by_gender('f');
        $unknown = $peepso_user_model->get_count_by_gender('u') + $peepso_user_model->get_count_by_gender('');

        $data = array();
        if (0 < $males)
            $data[] = array(
                'label' => __('Male', 'peepso-core'),
                'value' => $males,
                'icon' => PeepSo::get_asset('images/avatar/user-male-thumb.png'),
                'color' => 'rgb(237,194,64)'
            );
        if (0 < $females)
            $data[] = array(
                'label' => __('Female', 'peepso-core'),
                'value' => $females,
                'icon' => PeepSo::get_asset('images/avatar/user-female-thumb.png'),
                'color' => 'rgb(175,216,248)'
            );
        if (0 < $unknown)
            $data[] = array(
                'label' => __('Unknown', 'peepso-core'),
                'value' => $unknown,
                'icon' => PeepSo::get_asset('images/avatar/user-neutral-thumb.png'),
                'color' => 'rgb(180,180,180)'
            );

        $options = array(
            'series' => array(
                'pie' => array(
                    'show' => true,
                    'radius' => 100,
                    'highlight' => array(
                        'opacity' => 0.25
                    ),
                    'label' => array(
                        'show' => true
                    )
                )
            ),
            'legend' => array(
                'show' => true,
                'position' => "ne",
            ),
            'grid' => array(
                'hoverable' => true,
                'clickable' => true
            )
        );

        $data = apply_filters('peepso_admin_dashboard_demographic_data', $data);
        $options = apply_filters('peepso_admin_dashboard_demographic_options', $options);

        foreach($data as $k=>$v) {
            $color = dechex(crc32($v['label']));
            $color = substr($color, 0, 6);

            $data[$k]['label'] .= " ({$v['value']})";

            $data[$k]['color'] = "#$color";

        }

        echo '<script>', PHP_EOL;
        echo 'var demographic_data = ', json_encode($data), ';', PHP_EOL;
        echo 'var demographic_options = ', json_encode($options), ';', PHP_EOL;
        echo '</script>', PHP_EOL;
        echo '<div id="demographic-pie"></div>', PHP_EOL;
    }


    public function upsell()
    {
        echo PeepSo3_Helper_Addons::get_upsell();
    }

    /*
     * Display the content of the Most Recent metabox and gathers additional tabs from other plugins
     */
    public function recent_metabox()
    {
        // This metabox's default tabs
        $tabs = array(
            array(
                'id' => 'recent-posts',
                'title' => __('Posts', 'peepso-core'),
                'callback' => array(&$this, 'recent_posts_tab')
            ),
            array(
                'id' => 'recent-comments',
                'title' => __('Comments', 'peepso-core'),
                'callback' => array(&$this, 'recent_comments_tab')
            ),
            array(
                'id' => 'recent-members',
                'title' => __('Members', 'peepso-core'),
                'callback' => array(&$this, 'recent_members_tab')
            )
        );

        $tabs = apply_filters('peepso_admin_dashboard_recent_metabox_tabs', $tabs);

        echo '<ul class="nav nav-tabs">', PHP_EOL;

        $first = TRUE;
        foreach ($tabs as $tab) {
            echo '<li class="', ($first ? 'active' : ''), '">
					<a href="#', $tab['id'], '" data-toggle="tab">', $tab['title'], '</a>
				</li>', PHP_EOL;

            $first = FALSE;
        }

        echo '</ul>', PHP_EOL;

        $first = TRUE;
        echo '<div class="tab-content">', PHP_EOL;

        foreach ($tabs as $tab) {
            echo '<div class="tab-pane ', ($first ? 'active' : ''), '" id="', $tab['id'], '">', PHP_EOL;
            echo call_user_func($tab['callback']);
            echo '</div>', PHP_EOL;

            $first = FALSE;
        }

        echo '</div>', PHP_EOL;
    }

    /*
     * Display the content of the Posts tab under the Most Recent metabox
     */
    public function recent_posts_tab()
    {
        $activities = PeepSoActivity::get_instance();

        $posts = $activities->get_all_activities(
            'post_date_gmt',
            'desc',
            10,
            0,
            array(
                'post_type' => PeepSoActivityStream::CPT_POST
            )
        );

        if (0 === $posts->post_count) {
            echo __('No recent posts.', 'peepso-core');
        } else {
            add_filter('filter_remove_location_shortcode', array(&$this, 'filter_remove_location_shortcode'));

            echo '<div class="dialogs">', PHP_EOL;

            foreach ($posts->posts as $post) {
                $type = get_post_type_object($post->post_type);
                $user = PeepSoUser::get_instance($post->post_author);

                echo '<div class="itemdiv dialogdiv">' , PHP_EOL;
                echo '  <div class="user">' , PHP_EOL;
                echo '      <img title="', $user->get_username(), '" alt="', esc_attr($user->get_username()), '" src="', $user->get_avatar(), '" />', PHP_EOL;
                echo '  </div>', PHP_EOL;
                echo '  <div class="body">', PHP_EOL;
                echo '      <div class="time">', PHP_EOL;
                echo '          <i class="ace-icon fa fa-clock-o"></i>', PHP_EOL;
                echo '          <span class="green">', PeepSoTemplate::time_elapsed(strtotime($post->post_date_gmt), current_time('timestamp')), ' </span>', PHP_EOL;
                echo '      </div>', PHP_EOL;
                echo '      <div class="name">', PHP_EOL;
                echo '          <a href="', $user->get_profileurl(), '" title="', esc_attr(__('View profile', 'peepso-core')), '" target="_blank">', $user->get_fullname(TRUE), '</a>', PHP_EOL;
                echo '      </div>', PHP_EOL;
                echo '      <div class="text">', ucfirst($type->labels->activity_action), ': "', substr(strip_tags(apply_filters('filter_remove_location_shortcode', $post->post_content)), 0, 30), '"', PHP_EOL;
                echo '      </div>', PHP_EOL;
                echo '      <div class="tools">', PHP_EOL;
                echo '          <a href="', PeepSo::get_page('activity_status'), $post->post_title, '/" title="', esc_attr(__('View post', 'peepso-core')), '" target="_blank" class="btn btn-minier btn-info">', PHP_EOL;
                echo '              <i class="icon-only ace-icon fa fa-share"></i>', PHP_EOL;
                echo '          </a>', PHP_EOL;
                echo '      </div>', PHP_EOL;
                echo '  </div>', PHP_EOL;
                echo '</div>', PHP_EOL;
            }

            echo '</div>', PHP_EOL;
        }
    }

    /*
     * Display the content of the Comments tab under the Most Recent metabox
     */
    public function recent_comments_tab()
    {
        $activities = PeepSoActivity::get_instance();

        $comments = $activities->get_all_activities(
            'post_date_gmt',
            'desc',
            10,
            0,
            array(
                'post_type' => PeepSoActivityStream::CPT_COMMENT
            )
        );

        if (0 === $comments->post_count) {
            echo __('No recent posts.', 'peepso-core');
        } else {
            echo '<div class="dialogs">', PHP_EOL;

            foreach ($comments->posts as $post) {
                $type = get_post_type_object($post->post_type);
                $user = PeepSoUser::get_instance($post->post_author);

                $act = $activities->get_activity_data($post->ID);

                $parent_post = get_post($act->act_comment_object_id);
                $parent_post_act = $activities->get_activity_data($act->act_comment_object_id, $act->act_comment_module_id);

                // check is parent is comment, so it'll be nested comments
                if (is_object($parent_post)) {
                    if($parent_post->post_type === PeepSoActivityStream::CPT_COMMENT) {
                        $parent_post_comment = get_post($parent_post_act->act_comment_object_id);
                        $parent_post_comment_act = $activities->get_activity_data($parent_post_comment->ID, $parent_post_act->act_comment_module_id);

                        $post_title = $parent_post_comment->post_title . '#comment.' . $parent_post_comment_act->act_id . '.' . $parent_post->ID . '.' . $parent_post_act->act_id . '.' . $act->act_external_id;
                    } else {
                        $post_title = $parent_post->post_title . '#comment.' . $parent_post_act->act_id . '.' . $post->ID . '.' . $act->act_id;
                    }
                }

                echo '<div class="itemdiv dialogdiv">', PHP_EOL;
                echo '  <div class="user">', PHP_EOL;
                echo '      <img title="', esc_attr($user->get_username()), '" alt="', esc_attr($user->get_username()), '" src="', $user->get_avatar(), '" />', PHP_EOL;
                echo '  </div>', PHP_EOL;
                echo '  <div class="body">', PHP_EOL;
                echo '      <div class="time">', PHP_EOL;
                echo '          <i class="ace-icon fa fa-clock-o"></i>', PHP_EOL;
                echo '          <span class="green">', PeepSoTemplate::time_elapsed(strtotime($post->post_date_gmt), current_time('timestamp')), '</span>', PHP_EOL;
                echo '      </div>', PHP_EOL;
                echo '      <div class="name">', PHP_EOL;
                echo '          <a href="', $user->get_profileurl(), '" title="', esc_attr(__('View profile', 'peepso-core')), '" target="_blank">', $user->get_fullname(TRUE), '</a>', PHP_EOL;
                echo '      </div>', PHP_EOL;
                echo '      <div class="text">', PHP_EOL;
                echo '          <i class="fa fa-quote-left"></i>', PHP_EOL;
                echo            substr(strip_tags($post->post_content), 0, 30);
                echo '      </div>', PHP_EOL;
                echo '      <div class="tools">', PHP_EOL;
                echo '          <a href="', PeepSo::get_page('activity_status'), $post_title, '/" title="', esc_attr(__('View comment', 'peepso-core')), '" target="_blank" class="btn btn-minier btn-info">', PHP_EOL;
                echo '              <i class="icon-only ace-icon fa fa-share"></i>', PHP_EOL;
                echo '          </a>', PHP_EOL;
                echo '      </div>', PHP_EOL;
                echo '  </div>', PHP_EOL;
                echo '</div>', PHP_EOL;
            }

            echo '</div>', PHP_EOL;
        }
    }

    /*
     * Display the content of the Members tab under the Most Recent metabox
     */
    public function recent_members_tab()
    {
        global $wp_version, $wpdb;

        $args = array(
            'number' => 10,
            'orderby' => 'user_registered',
            'order' => 'DESC',
            'meta_key' => $wpdb->prefix . 'capabilities',
            'meta_value' => 'subscriber',
            'meta_compare' => 'LIKE'
        );

        $user_query = new WP_User_Query($args);

        if (0 === $user_query->total_users) {
            echo __('No users found', 'peepso-core');
        } else {
            $legacy_edit_link = (version_compare($wp_version, '3.5') < 0);

            foreach ($user_query->results as $user) {
                $user = PeepSoUser::get_instance($user->ID);

                if ($legacy_edit_link)
                    $edit_link = admin_url('user-edit.php?user_id=' . $user->get_id());
                else
                    $edit_link = get_edit_user_link($user->get_id());

                echo '<div class="itemdiv memberdiv clearfix">', PHP_EOL;
                echo '  <div class="user">', PHP_EOL;
                echo '      <a href="', $user->get_profileurl(), '" title="', esc_attr(__('View profile', 'peepso-core')), '" target="_blank">', PHP_EOL;
                echo '          <img alt="', esc_attr($user->get_firstname()), '" src="', $user->get_avatar(), '">', PHP_EOL;
                echo '      </a>', PHP_EOL;
                echo '  </div>', PHP_EOL;
                echo '  <div class="body">', PHP_EOL;
                echo '      <div class="name">', PHP_EOL;
                echo '          <a href="', $user->get_profileurl(), '" title="', esc_attr(__('View profile', 'peepso-core')), '" target="_blank">', $user->get_fullname(TRUE), '</a>', PHP_EOL;
                echo '      </div>', PHP_EOL;
                echo '      <div class="time">', PHP_EOL;
                echo '          <i class="ace-icon fa fa-clock-o"></i>', PHP_EOL;
                echo '          <span class="green">', PeepSoTemplate::time_elapsed(strtotime($user->get_date_registered()), current_time('timestamp')), '</span>', PHP_EOL;
                echo '      </div>', PHP_EOL;
                echo '      <div>', PHP_EOL;
                echo '          <span class="label label-success arrowed-in">', implode(', ', $user->get_role()), '</span>', PHP_EOL;
                echo '          <a href="', $edit_link, '" title="', esc_attr(__('Edit this user', 'peepso-core')), '"><i class="ace-icon fa fa-edit"></i></a>', PHP_EOL;
                echo '      </div>', PHP_EOL;
                echo '  </div>', PHP_EOL;
                echo '</div>', PHP_EOL;
            }

            echo '<div class="clearfix"></div>', PHP_EOL;
        }
    }



    private static function plugin_exists($filename, $class)
    {
        if(class_exists($class)) {
            return true;
        }
    }

    /*
     * Displays the User Engagement metabox and gathers additional tabs from other plugins
     */
    public function engagement_metabox()
    {
        add_filter(
            'peepso_admin_dashboard_engagement-' . PeepSoActivity::MODULE_ID . '_stat_types',
            array(&$this, 'stream_stat_types'));
        // This metabox's default tabs
        $tabs = array(
            array(
                'id' => 'engagment-stream',
                'title' => __('Stream', 'peepso-core'),
                'callback' => array(&$this, 'engagement_tab'),
                'module_id' => PeepSoActivity::MODULE_ID
            )
        );

        $tabs = apply_filters('peepso_admin_dashboard_engagement_metabox_tabs', $tabs);

        echo '<ul class="nav nav-tabs">';

        $first = TRUE;
        foreach ($tabs as $tab) {
            echo '<li class="', ($first ? 'active' : ''), '" data-module-id=', $tab['module_id'], '>
					<a href="#', $tab['id'], '" data-toggle="tab">', $tab['title'], '</a>
				</li>';

            $first = FALSE;
        }

        echo '</ul>';

        $first = TRUE;
        echo '<div class="tab-content">';

        foreach ($tabs as $tab) {
            echo '<div class="tab-pane ', ($first ? 'active' : ''), '" id="', $tab['id'], '">';
            echo call_user_func_array($tab['callback'], array($tab['module_id']));
            echo '</div>';

            $first = FALSE;
        }

        echo '</div>';
    }

    /*
     * Renders the contents of the tab under the User Engagement metabox
     * @param string $module_id MODULE_ID of the plugin from which the data will be referencing
     */
    public function engagement_tab($module_id)
    {
        $date_range_filters = apply_filters('peepso_admin_dashboard_' . $module_id . '_date_range',
            array(
                'this_week' => __('This week', 'peepso-core'),
                'last_week' => __('Last week', 'peepso-core'),
                'this_month' => __('This month', 'peepso-core'),
                'last_month' => __('Last month', 'peepso-core'),
            )
        );

        $stat_types = apply_filters('peepso_admin_dashboard_engagement-' . $module_id . '_stat_types', array());

        // Content is called via ajax PeepSoActivity::get_graph_data()
        echo '<div class="container-fluid">
				<div class="row">
					<div class="col-xs-12">
						<select name="engagement_', $module_id, '_date_range" class="engagement_date_range">', PHP_EOL;

        foreach ($date_range_filters as $val => $date_range)
            echo '<option value="', $val, '">', $date_range, '</option>', PHP_EOL;

        echo '          </select>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 graph-container"></div>
					<div class="col-xs-12 series-container">', PHP_EOL;

        foreach ($stat_types as $stat) {
            echo '<label>
					<input value="', $stat['stat_type'], '" type="checkbox" name="stats[]" checked="checked" id="id', $stat['label'], '" style="margin:0">
					<span class="lbl" for="id', $stat['label'], '">', ucwords($stat['label']), '</span> &nbsp; &nbsp;
				</label>', PHP_EOL;
        }

        echo '      </div>
				</div>
			</div>', PHP_EOL;
    }

    /**
     * Define which stats to track on the dashboard for the 'activity' module
     * @param array $types
     * @return array Stat types
     */
    public function stream_stat_types($types)
    {
        return array(
            array(
                'label' => __('posts', 'peepso-core'),
                'stat_type' => PeepSoActivityStream::CPT_POST
            ),
            array(
                'label' => __('comments', 'peepso-core'),
                'stat_type' => PeepSoActivityStream::CPT_COMMENT
            ),
            array(
                'label' => __('likes', 'peepso-core'),
                'stat_type' => 'likes'
            )
        );
    }

    /**
     * Display pending members who require Admin Activation
     */
    public function pending_members_metabox()
    {
        global $wp_version, $wpdb;

        $args = array(
            'number' => 6,
            'orderby' => 'user_registered',
            'order' => 'DESC',
            'peepso_roles' => 'verified'
        );

        add_action('pre_user_query', array(PeepSo::get_instance(), 'filter_user_roles'));

        $user_query = new WP_User_Query($args);

        if (!$user_query->total_users) {
            echo __('The list is empty', 'peepso-core') . ' ';
        } else {
            $legacy_edit_link = (version_compare($wp_version, '3.5') < 0);

            foreach ($user_query->get_results() as $user_item) {
                $user = PeepSoUser::get_instance($user_item->ID);
                $nonce = wp_create_nonce('update-role-nonce_' . $user_item->ID);

                echo '<div class="itemdiv memberdiv clearfix">', PHP_EOL;
                echo '  <div class="user">', PHP_EOL;
                echo '      <a href="', $user->get_profileurl(), '" title="', esc_attr(__('View profile', 'peepso-core')), '" target="_blank">', PHP_EOL;
                echo '          <img alt="', esc_attr($user->get_firstname()), '" src="', $user->get_avatar(), '">', PHP_EOL;
                echo '      </a>', PHP_EOL;
                echo '  </div>', PHP_EOL;
                echo '  <div class="body">', PHP_EOL;
                echo '      <div class="name">', PHP_EOL;
                echo '          <a href="', $user->get_profileurl(), '" title="', esc_attr(__('View profile', 'peepso-core')), '" target="_blank">', $user->get_fullname(TRUE) , '</a>', PHP_EOL;
                echo '      </div>', PHP_EOL;
                echo '      <div class="email">', PHP_EOL;
                echo '          <span>', $user->get_email(), '</span>', PHP_EOL;
                echo '      </div>', PHP_EOL;
                echo '      <div class="approve-links">', PHP_EOL;
                echo '          <a class="btn btn-xs btn-success" href="', admin_url('admin.php?page=peepso&action=update-user-role&set=member&id=' . $user_item->ID . '&_wpnonce=' . $nonce), '" title="', esc_attr(__('Approve', 'peepso-core')), '">', esc_attr(__('Approve', 'peepso-core')), '</a>', PHP_EOL;
                echo '          <a class="btn btn-xs" href="', admin_url('admin.php?page=peepso&action=update-user-role&set=ban&id=' . $user_item->ID . '&_wpnonce=' . $nonce), '" title="', esc_attr(__('Dismiss and Ban', 'peepso-core')), '">', esc_attr(__('Dismiss and Ban', 'peepso-core')), '</a>', PHP_EOL;
                echo '      </div>', PHP_EOL;
                echo '  </div>', PHP_EOL;
                echo '</div>', PHP_EOL;
            }

            echo '<div class="clearfix"></div>', PHP_EOL;
        }

        echo '<div class="center cta-full">
			<a href="', admin_url('users.php?psrole=verified'), '">',
        __('See all Pending Members', 'peepso-core'), ' (' , $user_query->total_users . ') &nbsp;
				<i class="fa fa-arrow-right"></i>
			</a>
		</div>', PHP_EOL;
    }

    function filter_remove_location_shortcode($content)
    {
        $content = str_replace('[/peepso_geo]', '', $content);
        return preg_replace('/\[peepso_geo(?:.*?)\]/', '', $content);
    }

    function reported_items_metabox()
    {
        $rep = new PeepSoReport();

        $report_items = $rep->get_reports('', 'DESC', 0, 5);

        if ($report_items)
        {
            add_filter('filter_remove_location_shortcode', array(&$this, 'filter_remove_location_shortcode'));
            echo '<div class="psa-list--reported">';
            foreach ($report_items as $item)
            {
                $nonce = wp_create_nonce('update-report-nonce_' . $item['rep_id']);

                echo '<div class="psa-list__item">';
                echo '<div class="psa-list--reported__amount" title="', __('Amount of reports', 'peepso-core'), '">' . $item['rep_user_count'] . '</div>';
                echo '<div class="psa-list--reported__reason"><span>' . $item['rep_reason'] . '</span></div>';
                echo '<div class="psa-list--reported__content">';

                switch ($item['rep_module_id'])
                {
                    case 0 :
                        $user = PeepSoUser::get_instance($item['rep_external_id']);
                        echo __('Profile', 'peepso-core'), ' : ' . $user->get_fullname(TRUE);
                        break;
                    default :
                        echo __('Content', 'peepso-core'), ' : ' . apply_filters('filter_remove_location_shortcode', $item['post_content']);
                        break;
                }
                echo '</div>';

                echo '<div class="psa-list--reported__action">';
                switch ($item['rep_module_id'])
                {
                    case 0 :
                        echo '<a class="psa-list--reported__link" href="' . $user->get_profileurl() . '" target="_blank">' . $user->get_fullname(TRUE) . ' <i class="fa fa-external-link"></i></a>';
                        echo '<a class="btn btn-xs" href="' . admin_url('admin.php?page=peepso&action=update-report&set=dismiss&id=' . $item['rep_id'] . '&_wpnonce=' . $nonce) . '">Dismiss</a>';
                        echo '<a class="btn btn-xs btn-danger" href="' . admin_url('admin.php?page=peepso&action=update-report&set=ban&id=' . $item['rep_id'] . '&_wpnonce=' . $nonce) . '">Ban Profile</a>';
                        break;
                    default :
                        $permalink = PeepSo::get_page('activity_status') . $item['post_title'];

                        $activities = PeepSoActivity::get_instance();

                        $not_activity = $activities->get_activity_data($item['rep_external_id'], PeepSoActivity::MODULE_ID);
                        if (intval($not_activity->act_comment_object_id) !== 0) {
                            $comment_activity = $activities->get_activity_data($not_activity->act_comment_object_id, $not_activity->act_comment_module_id);
                            if (intval($comment_activity->act_comment_object_id) !== 0) {
                                $post_activity = $activities->get_activity_data($comment_activity->act_comment_object_id, $comment_activity->act_comment_module_id);

                                $parent_comment = $activities->get_activity_post($comment_activity->act_id);
                                $parent_post = $activities->get_activity_post($post_activity->act_id);
                                $parent_id = $parent_comment->act_external_id;

                                $post_link = PeepSo::get_page('activity_status') . $parent_post->post_title . '/';
                                $permalink = $post_link . '?t=' . time() . '#comment.' . $post_activity->act_id . '.' . $parent_comment->ID . '.' . $comment_activity->act_id . '.' . $not_activity->act_external_id;
                            } else {
                                $post_activity = $comment_activity;

                                $parent_post = $activities->get_activity_post($post_activity->act_id);
                                $permalink = PeepSo::get_page('activity_status') .  $parent_post->post_title . '/#comment.' . $post_activity->act_id . '.' . $item['rep_external_id'] . '.' . $not_activity->act_external_id;
                            }
                        }

                        echo '<a class="psa-list--reported__link" href="' . $permalink . '" target="_blank">' . $item['post_title'] . ' <i class="fa fa-external-link"></i></a>';
                        echo '<a class="btn btn-xs" href="' . admin_url('admin.php?page=peepso&action=update-report&set=dismiss&id=' . $item['rep_id'] . '&_wpnonce=' . $nonce) . '">Dismiss</a>';
                        echo '<a class="btn btn-xs btn-danger" href="' . admin_url('admin.php?page=peepso&action=update-report&set=unpublish&id=' . $item['rep_id'] . '&_wpnonce=' . $nonce) . '">Unpublish</a>';
                        break;
                }
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        } else
        {
            echo __('The list is empty', 'peepso-core') . ' ';
        }

        echo '<div class="center cta-full">
					<a href="', admin_url('admin.php?page=peepso-manage&tab=reports'), '">',
        __('See all Reported Items', 'peepso-core'),
        ' (' , $rep->get_num_reported_items() . ') &nbsp;
						<i class="fa fa-arrow-right"></i>
					</a>
			</div>', PHP_EOL;
    }

    function update_report($screen)
    {
        if ($screen->base == 'toplevel_page_peepso')
        {
            $input = new PeepSoInput();

            $action = $input->value('action', '', FALSE); // SQL Safe
            $set = $input->value('set', '', FALSE); // SQL Safe
            $id = $input->int('id');

            // SQL safe, WP sanitizes it
            $_wpnonce = $input->value('_wpnonce','',FALSE);

            if (
                $action === 'update-report' &&
                ($set === 'dismiss' || $set == 'ban' || $set == 'unpublish') &&
                wp_verify_nonce($_wpnonce, 'update-report-nonce_' . $id)
            )
            {
                $rep = new PeepSoReport();
                switch ($set)
                {
                    case 'dismiss' :
                        if ($rep->dismiss_report($id))
                            $this->add_notice(__('Report dismissed.', 'peepso-core'), 'note');
                        break;
                    case 'ban' :
                        if ($rep->ban_user($id))
                            $this->add_notice(__('User banned.', 'peepso-core'), 'note');
                        break;
                    case 'unpublish' :
                        if ($rep->unpublish_report($id))
                            $this->add_notice(__('Post unpublished.', 'peepso-core'), 'note');
                        break;
                }


            } else if (isset($action) && $action === 'update-report')
            {
                $this->add_notice(__('Invalid action', 'peepso-core'), 'error');
            }
        }
    }

    function register_notice() {
        global $pagenow;

        if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'peepso') {
            $optionName = 'peepso_register';

            $registrationHide	 = filter_input(INPUT_GET, 'peepso_registration_hide' );

            if ( $registrationHide ) {
                update_option($optionName, 1);
            }

            $post = filter_input_array(INPUT_POST);
            $domain = 'https://www.peepso.com';

            if (!empty($post) && !empty($post['register_nonce'])) {

                $nonceCheck = wp_verify_nonce($post['register_nonce'], 'peepso_register');
                if ($nonceCheck) {

                    if (!PeepSo::get_option('optin_stats', 0)) {
                        if(isset($post['optin_stats'])) {
                            $PeepSoConfigSettings = PeepSoConfigSettings::get_instance();
                            $PeepSoConfigSettings->set_option('optin_stats', 1);
                        }
                    }

                    if(isset($post['optin_stats'])) {
                        unset($post['optin_stats']);
                    }
                    unset($post['register_nonce']);
                    $jsonData = wp_json_encode(array($post));

                    $args = array(
                        'body' => array(
                            'jsonData' => $jsonData
                        )
                    );

                    $href		 = str_replace('http', 'https', $domain) . '/wp-admin/admin-ajax.php?action=add_user&cminds_json_api=add_user';
                    $response	 = wp_remote_post($href, $args);

                    if (!is_wp_error($response))
                    {
                        $result = json_decode(wp_remote_retrieve_body($response), true);
                        if ($result && 1 === $result['result'])
                        {
                            update_option($optionName, 1);
                        }
                    } else {
                        $args['sslverify'] = false;
                        $href				 = $domain . '/wp-admin/admin-ajax.php?action=add_user&cminds_json_api=add_user';
                        $response			 = wp_remote_post($href, $args);

                        if (!is_wp_error($response))
                        {
                            $result = json_decode(wp_remote_retrieve_body($response), true);
                            if ($result && 1 === $result['result'])
                            {
                                update_option($optionName, 1);
                            }
                        } else {
                            $message = 'Registered fields: <br/><table>';
                            foreach ($post as $key => $value) {
                                if (!in_array($key, array('product_name', 'email', 'hostname'))) {
                                    continue;
                                }
                                $message .= '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
                            }
                            $message .= '</table>';

                            add_filter('wp_mail_content_type', array(&$this, 'set_mail_content_type'));
                            wp_mail('info@peepso.com', 'PeepSo Product Registration', $message);
                            remove_filter('wp_mail_content_type', array(&$this, 'set_mail_content_type'));
                        }
                    }
                }
            }


            $fields = array(
                'product_name'	 => 'peepso',
                'remote_url'	 => get_bloginfo('wpurl'),
                'remote_ip'		 => $_SERVER['SERVER_ADDR'],
                'remote_country' => '',
                'remote_city'	 => '',
                'email'			 => get_bloginfo('admin_email'),
                'hostname'		 => get_bloginfo('wpurl'),
                'username'		 => '',
            );

            $output = '';
            foreach ($fields as $key => $value) {
                $output .= sprintf( '<input type="hidden" name="%s" value="%s" />', $key, $value );
            }

            $registrationHidden = get_option($optionName);

            if (!$registrationHidden)
            {
                $dashboard_main = __('Once registered, you will receive updates and special offers from PeepSo. We will send your administrator\'s email and site URL to PeepSo server.','peepso');

                ?>
                <div class="cminds_registration_wrapper">
                    <form method="post" action="">
                        <div class="cminds_registration">
                            <div class="cminds_registration_action">

                                <?php
                                wp_nonce_field('peepso_register', 'register_nonce');
                                echo $output;
                                ?>
                                <input class="button button-primary" type="submit" value="Register Your Copy" />
                                <div class="no-registration">
                                    <a class="cminds-registration-hide-button" href="<?php echo add_query_arg( array( 'peepso_registration_hide' => 1 ), remove_query_arg( 'peepso_registration_hide' ) ); ?>"><?php echo __("I don't want to register", 'peepso-core'); ?></a>
                                </div>

                            </div>
                            <div class="cminds_registration_text">
								<span>
									<?php echo $dashboard_main; ?>
								</span>
                                <span>
									<?php if(!PeepSo::get_option('optin_stats', 0)) { ?>

                                        <p><input name="optin_stats" type="checkbox" checked="checked" /> <?php echo __('Enable additional statistics'); ?> <a target="_blank" href="<?php echo admin_url('admin.php?page=peepso_config&tab=advanced#field_optin_stats');?>"><i class="infobox-icon dashicons dashicons-editor-help"></i></a></p>

                                    <?php } ?>
								</span>
                            </div>
                        </div>
                    </form>
                </div>
                <?php
            }
        }
    }

    function modify_plugin_action_links($links, $file) {
        /*
         * This HTML element is used to identify the correct plugin when attaching an event to its Deactivate link.
         */
        if ( isset( $links[ 'deactivate' ] ) ) {
            $links[ 'deactivate' ] .= '<i class="peepso-slug" data-slug="cma"></i>';
        }

        return $links;
    }

    function show_deactivation_feedback_dialog()
    {
        global $pagenow;
        if ('plugins.php' === $pagenow)
        {
            PeepSoTemplate::exec_template('admin', 'deactivation_feedback_modal');
        }
    }


    function set_mail_content_type() {
        return "text/html";
    }

    public function count_plugin($plugins) {
        return count(array_filter($plugins->products, function($plugin) {
            return strpos($plugin->info->slug, 'translation') === FALSE && strpos($plugin->info->slug, 'bundle') === FALSE;
        }));
    }

    /**
     * Fires after the user's role has changed.
     * @param int    $user_id   The user ID.
     * @param string $role      The new role.
     * @param array  $old_roles An array of the user's previous roles.
     */
//  public function set_user_role($user_id, $role, $old_roles)
//  {
//      if ('peepso_member' === $role && in_array('peepso_verified', $old_roles)) {
//          $user = PeepSoUser::get_instance();
//          $user->approve_user();
//      }
//  }
}

// EOF
