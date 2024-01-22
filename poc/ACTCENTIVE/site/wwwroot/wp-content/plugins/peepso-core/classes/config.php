<?php

class PeepSoConfig
{
    private static $instance = NULL;

    public static $slug = 'peepso_config';
    public $config_object = NULL;

    public $sections = NULL;
    public $form = NULL;

    private $tab_count = 0;
    private $curtab = NULL;

    public function __construct()
    {
    }

    // @todo docblock
    public function render()
    {
        wp_enqueue_media();
        wp_enqueue_script('peepso-admin-config');

        $input = new PeepSoInput();
        $tab = $this->curtab = $input->value('tab', 'site', false); // SQL safe

        $options = PeepSoConfigSettings::get_instance();

        add_action('peepso_admin_config_save', array(&$this, 'config_save'));
        add_filter('peepso_render_form_field_type-radio', array(&$this, 'render_admin_radio_field'), 10, 2);

        // handle tabs within config settings page
        $curtab = $input->value('tab','',false); // SQL safe

        $aTab = $this->get_tab($tab);

        if (!empty($curtab) && !isset($aTab['function'])) {
            switch ($curtab)
            {
                case 'email':
                    PeepSoConfigEmails::get_instance();
                    break;
            }

            // SQL safe, WP sanitizes it
            if ('POST' === $_SERVER['REQUEST_METHOD'] &&
                wp_verify_nonce($input->value('peepso-' . $curtab . '-nonce','',false), 'peepso-' . $curtab . '-nonce'))
                do_action('peepso_admin_config_save-' . $curtab);

            do_action('peepso_admin_config_tab-' . $curtab);

            return;
        }

        $aTab = $this->get_tab($tab);
        $this->config_object = new $aTab['function']();

        if (!($this->config_object instanceOf PeepSoConfigSectionAbstract)) {
            throw new Exception(__('Class must be instance of PeepSoConfigSectionAbstract', 'peepso-core'), 1);
        }

        $filter = 'peepso_admin_register_config_group-' . $aTab['tab'];

        $this->config_object->register_config_groups();
        $this->config_object->config_groups = apply_filters($filter, $this->config_object->config_groups);
        // Call build_form after all config_groups have been defined
        $this->config_object->build_form();

        add_filter('peepso_admin_config_form_open', array(&$this, 'set_form_args'));

        $this->prepare_metaboxes();

        if (isset($_REQUEST['peepso-config-nonce']) &&
            wp_verify_nonce($_REQUEST['peepso-config-nonce'], 'peepso-config-nonce')) {
            do_action('peepso_admin_config_save');
        }


        $this->render_tabs();

        PeepSoTemplate::set_template_dir('admin');
        PeepSoTemplate::exec_template(
            'config',
            'options',
            array(
                'config' => $this
            )
        );
    }

    /*
     * Display the tabs
     */
    public function render_tabs()
    {
        ob_start();
        $current_title = __('Configuration', 'peepso-core');

        $input = new PeepSoInput();
        $curtab = $input->value('tab', 'site', false); // SQL safe

        $old_cat = 'foundation';

        $c = array(
            'foundation-free-bundle' =>'#f7b431',
            'foundation'=>'rgb(207,65,59)',
            'foundation-notifications'=>'rgb(190,50,45)',
            'foundation-advanced'=>'rgb(175,40,35)',
            'core'=>'#fdd5d3',
            'extras'=>'#fdd5d3',
            'integrations'=>'#d2e8f7',
            'monetization'=>'#d2eed2',
            'default'       => '#ffffff',
        );

        echo '<div class="psa-navbar">', PHP_EOL;
        $tabs = $this->get_tabs();
        foreach ($tabs as $tab) {
            $config_tab = '';

            $cat = isset($tab['cat']) ? $tab['cat'] : $tab['label'];

            if($cat != $old_cat) {
                $old_cat=$cat;
            }

            if (isset($tab['tab']) && !empty($tab['tab']))
                $config_tab = $tab['tab'];
            $activeclass = '';
            if ($curtab === $config_tab) {
                $activeclass = 'active';
                $current_title =  $current_title . ' <small>-</small> ' . $tab['label'];
            }

            $color = $c['default'];
            if(isset($c[$cat])) {
                $color = $c[$cat];
            }

            echo '<div  class="psa-navbar__item ', $activeclass, '">', PHP_EOL;
            echo '<a class="ps-tooltip ps-tooltip-cat-'.$cat.'" style="background-color:',$color,' !important;" href="';
            $url = admin_url('admin.php?page=') . self::$slug;

            if (!empty($tab['tab'])) {
                $url .= '&tab=' . $tab['tab'];
                if('http' == substr($tab['tab'],0,4)) {
                    $url = $tab['tab'];
                }
            }

            echo $url . '"';

            //if (isset($tab['description']) && !empty($tab['description']))
            //	echo ' title="', esc_attr($tab['description']), '"';
            echo '>';

            $verbose_tabs_css = (1 == get_user_option('peepso_admin_verbose_tabs')) ? '' : ' style="display:none" ';
            echo	isset($tab['icon']) ? '<img src="'.$tab['icon'].'" height="32"/>' : $tab['label'];
            echo    isset($tab['icon']) ? '<div class="ps-label-optional" '.$verbose_tabs_css.' > &nbsp; ' . esc_attr($tab['label']) . '</div>' : '';
            echo	'</a>', PHP_EOL;


            echo '<div class="ps-tooltip__box">', esc_attr($tab['label']) , '</div>';

            echo '</div>';


        }

        echo '</div>', PHP_EOL;

        $tabs = ob_get_clean();

        PeepSoAdmin::admin_header($current_title);
        echo $tabs;

        echo PeepSo3_Helper_Addons::get_upsell('banner');
        echo PeepSo3_Helper_Addons::get_upsell('maybe_expired_license');

        // default to 1
        if(FALSE === get_user_option('peepso_admin_verbose_fields')) {
            update_user_option(get_current_user_id(),'peepso_admin_verbose_fields', 1);
        }

        // default to 1
        if(FALSE === get_user_option('peepso_admin_verbose_tabs')) {
            update_user_option(get_current_user_id(),'peepso_admin_verbose_tabs', 1);
        }
        ?>

        <div class="psa-dashboard__filters">
            <div class="psa-dashboard__filter ps-checkbox">
                <span><?php echo __('Tab titles','peepso-core');?></span>
                <input name="peepso_admin_verbose_tabs" class="ace ace-switch ace-switch-2" id="peepso_admin_verbose_tabs" type="checkbox" value="1" <?php echo (1 == get_user_option('peepso_admin_verbose_tabs')) ? 'checked' : ''; ?>>
                <label class="lbl" for="peepso_admin_verbose_tabs">

                </label>
            </div>
            <div class="psa-dashboard__filter ps-checkbox">
                <span><?php echo __('Field descriptions','peepso-core');?></span>
                <input name="peepso_admin_verbose_fields" class="ace ace-switch ace-switch-2" id="peepso_admin_verbose_fields" type="checkbox" value="1" <?php echo (1 == get_user_option('peepso_admin_verbose_fields')) ? 'checked' : ''; ?>>
                <label class="lbl" for="peepso_admin_verbose_fields">

                </label>
            </div>
        </div>

        <?php

        echo '<div class="edit-message" id="edit_warning">';
        echo __('Some settings have been changed. Be sure to save your changes.', 'peepso-core');
        echo '</div>';
    }


    /*
     * Opens config form, applies filters to <form> arguments
     *
     * @return string The opening form tag
     */
    public function form_open()
    {
        $form = apply_filters('peepso_admin_config_form_open', '', 10, array());

        return $this->config_object->get_form()->form_open($form);
    }

    // @todo docblock
    public function set_form_args()
    {
        return array();
    }

    /*
     * Creates a meta box for each config group item
     */
    public function prepare_metaboxes()
    {
        foreach ($this->config_object->config_groups as $id => $group) {
            add_meta_box(
                'peepso_config-' . $id, //Meta box ID
                __($group['title'], 'peepso-core'), //Meta box Title
                array(&$this, 'render_field_group'), //Callback defining the plugin's innards
                'peepso_page_peepso-config', // Screen to which to add the meta box
                isset($group['context']) ? $group['context'] : 'full', // Context
                'default',
                array('group' => $group)
            );
        }
    }

    /**
     * Metabox callback - renders the field group
     * @param  object $post An object containing the current post.
     * @param  array $metabox Is an array with metabox id, title, callback, and args elements.
     * @return void Echoes the field group.
     */
    public function render_field_group($post, $metabox)
    {
        $group = $metabox['args']['group'];

        if (isset($group['description']))
            echo '<p style="color:gray">', $group['description'], '</p>', PHP_EOL;

        foreach ($group['fields'] as $field) {
            $field = $this->config_object->form->fields[$field['name']];
            echo '<div id="field_' . $field['name'] . '" class="form-group"';

            if(isset($field['children'])) {
                echo ' data-children="'. implode(',',$field['children']).'" ';
            }
            echo '>';
            echo $this->config_object->form->render_field($field);
            echo '</div>';
            echo '<div class="clearfix"></div>';
        }

        if (isset($group['summary']))
            echo '<p style="color:gray">', $group['summary'], '</p>', PHP_EOL;
    }

    // Calls get_instance() to start
    public static function init()
    {
        $config = self::get_instance();
        $config->render();
    }

    // Return an instance of PeepSoConfig
    public static function get_instance()
    {
        if (NULL === self::$instance)
            self::$instance = new self();
        return self::$instance;
    }


    /*
     * Build a list of tabs to display at the top of config pages
     * @return array List of tabs to display on config pages
     */
    public function get_tabs()
    {
        $default_tabs = array(

            'free' => array(
                'label' => __('PeepSo Free Bundle', 'peepso-core'),
                'icon' => 'https://cdn.peepso.com/icons/configsections/gift.svg',
                'tab' => admin_url('/admin.php?page=peepso-installer&action=peepso-free&peepso_remote_no_cache'),
                'description' => '',
                'function' => 'PeepSoConfigSectionNetwork',
                'cat' => 'foundation-free-bundle',
            ),
            'site' => array(
                'label' => __('General', 'peepso-core'),
                'icon' => 'https://cdn.peepso.com/icons/configsections/peepso.svg',
                'tab' => 'site',
                'description' => __('General configuration settings for PeepSo', 'peepso-core'),
                'function' => 'PeepSoConfigSections',
                'cat' => 'foundation',
            ),

            'appearance' => array(
                'label' => __('Appearance', 'peepso-core'),
                'tab' => 'appearance',
                'description' => __('Look and feel settings', 'peepso-core'),
                'icon' => 'https://cdn.peepso.com/icons/configsections/settings_appearance.svg',
                'function' => 'PeepSoConfigSectionAppearance',
                'cat' => 'foundation',
            ),

            'accounts' => array(
                'label' => __('Accounts & Security', 'peepso-core'),
                'tab' => 'accounts',
                'description' => __('Registration, login and security', 'peepso-core'),
                'icon' => 'https://cdn.peepso.com/icons/configsections/accounts.svg',
                'function' => 'PeepSoConfigSectionAccounts',
                'cat' => 'foundation',
            ),

            'postbox' => array(
                'label' => __('Stream Posts', 'peepso-core'),
                'icon' => 'https://cdn.peepso.com/icons/configsections/settings_postbox.svg',
                'tab' => 'postbox',
                'description' => '',
                'function' => 'PeepSoConfigSectionPostbox',
                'cat' => 'foundation',
            ),

            'markdown' => array(
                'label' => __('Markdown', 'peepso-core'),
                'icon' => 'https://cdn.peepso.com/icons/configsections/settings_markdown.svg',
                'tab' => 'markdown',
                'description' => __('Markdown', 'peepso-core'),
                'function' => 'PeepSoConfigSectionMarkdown',
                'cat' => 'foundation',
            ),

            'blogposts' => array(
                'label' => __('Blog Posts', 'peepso-core'),
                'icon' => 'https://cdn.peepso.com/icons/configsections/blogposts.svg',
                'tab' => 'blogposts',
                'description' => '',
                'function' => 'PeepSoConfigSectionBlogPosts',
                'cat'   => 'foundation',
            ),
//            'cpt' => array(
//                'label' => __('Custom Post Types', 'peepso-core'),
//                'icon' => 'https://cdn.peepso.com/icons/configsections/cpt.svg',
//                'tab' => 'cpt',
//                'description' => '',
//                'function' => 'PeepSoConfigSectionCPT',
//                'cat'   => 'foundation',
//            ),
            'notifications' => array(
                'label' => __('Notifications', 'peepso-core'),
                'icon' => 'https://cdn.peepso.com/icons/configsections/settings_notifications.svg',
                'tab' => 'notifications',
                'description' => '',
                'function' => 'PeepSoConfigSectionNotifications',
                'cat' => 'foundation-notifications',
            ),
            'email' => array(
                'label' => __('Edit Emails', 'peepso-core'),
                'icon' => 'https://cdn.peepso.com/icons/configsections/settings_email.svg',
                'tab' => 'email',
                'description' => __('Edit content of emails sent by PeepSo to users and Admins', 'peepso-core'),
                'cat' => 'foundation-notifications',
            ),

            'advanced' => array(
                'label' => __('Advanced', 'peepso-core'),
                'icon' => 'https://cdn.peepso.com/icons/configsections/settings_advanced.svg',
                'tab' => 'advanced',
                'description' => __('Advanced System options', 'peepso-core'),
                'function' => 'PeepSoConfigSectionAdvanced',
                'cat' => 'foundation-advanced',
            ),

            'moderation' => array(
                'label' => __('Moderation', 'peepso-core'),
                'icon' => 'https://cdn.peepso.com/icons/configsections/reports.svg',
                'tab' => 'moderation',
                'description' => __('Moderating content', 'peepso-core'),
                'function' => 'PeepSoConfigSectionModeration',
                'cat' => 'foundation-advanced',
            ),

            'location' => array(
                'label' => __('Location', 'peepso-core'),
                'icon' => 'https://cdn.peepso.com/icons/configsections/location.svg',
                'tab' => 'location',
                'description' => '',
                'function' => 'PeepSoConfigSectionLocation',
                'cat' => 'foundation-advanced',
            ),

            'navigation' => array(
                'label' => __('Navigation & Filters', 'peepso-core'),
                'icon' => 'https://cdn.peepso.com/icons/configsections/navigation.svg',
                'tab' => 'navigation',
                'description' => '',
                'function' => 'PeepSoConfigSectionNavigation',
                'cat' => 'foundation-advanced',
            ),

            'network' => array(
                'label' => __('Live updates', 'peepso-core'),
                'icon' => 'https://cdn.peepso.com/icons/configsections/settings_ajax.svg',
                'tab' => 'network',
                'description' => '',
                'function' => 'PeepSoConfigSectionNetwork',
                'cat' => 'foundation-advanced',
            ),



        );

        if(PeepSo3_Helper_Addons::get_license()) {
            unset($default_tabs['free']);
        }

        PeepSoLocation::get_instance();

        $tabs = apply_filters('peepso_admin_config_tabs', array());

        $tabs_by_cat=array();
        foreach($tabs as $key=>$tab) {
            $cat = isset($tab['cat']) ? $tab['cat'] : 'thirdparty';

            $tab['key'] = $key;
            $tabs_by_cat[$cat][$tab['label']] = $tab;
            ksort($tabs_by_cat[$cat]);
        }

        $tabs = array();

        if(isset($tabs_by_cat['foundation-advanced'])) {
            foreach($tabs_by_cat['foundation-advanced'] as $key=>$tab) {
                $tabs[$tab['key']] = $tab;
            }
        }

        if(isset($tabs_by_cat['core'])) {
            foreach($tabs_by_cat['core'] as $key=>$tab) {
                $tabs[$tab['key']] = $tab;
            }
        }

        if(isset($tabs_by_cat['extras'])) {
            foreach($tabs_by_cat['extras'] as $key=>$tab) {
                $tabs[$tab['key']] = $tab;
            }
        }

        if(isset($tabs_by_cat['integrations'])) {
            foreach($tabs_by_cat['integrations'] as $key=>$tab) {
                $tabs[$tab['key']] = $tab;
            }
        }

        if(isset($tabs_by_cat['monetization'])) {
            foreach($tabs_by_cat['monetization'] as $key=>$tab) {
                $tabs[$tab['key']] = $tab;
            }
        }

        if(isset($tabs_by_cat['thirdparty'])) {
            foreach($tabs_by_cat['thirdparty'] as $key=>$tab) {
                $tabs[$tab['key']] = $tab;
            }
        }

        $tabs = array_merge($default_tabs, $tabs);

        return ($tabs);
    }

    // @todo docblock
    public static function test()
    {
        $instance = self::get_instance();
    }

    /*
     * Get a tab based on the associative key
     *
     * @param string $tab The tab's associative key
     * @return array
     */
    public function get_tab($tab)
    {
        $tabs = $this->get_tabs();

        if (empty($tabs[$tab])) {
            echo "It looks like this PeepSo config tab does not exist";
            return NULL;
        }

        return $tabs[$tab];
    }

    /**
     * 'peepso_admin_config_save' action callback. Maps the $_POST data to the form fields,
     * calls validation and saves data once validation is passed.
     *
     * @return void Sets error or success messages to the 'peepso_config_notice' option.
     */
    public function config_save()
    {
        do_action('peepso_config_before_save-' . $this->curtab);
        global $wpdb;

        // Clear licensing Mayfly if saving the main config page
        if('site'==$this->curtab) {
            PeepSo3_Mayfly::clr( true, 'license' );
        }

        $this->config_object->get_form()->map_request();

        if ($this->config_object->get_form()->validate()) {
            $this->_save();
            $type = 'note';
            $message = __('Options updated', 'peepso-core');
        } else {
            $type = 'error';
            $message = __('Please correct the errors below', 'peepso-core');
        }

        $peepso_admin = PeepSoAdmin::get_instance();
        $peepso_admin->add_notice($message, $type);
    }

    /**
     * Rendering function for radio buttons. Called from the filter - 'peepso_render_form_field_type-radio'.
     * @param  string $sField The field's HTML.
     * @param  object $field  The field object.
     * @return string The radio button HTML.
     */
    public function render_admin_radio_field($sField, $field)
    {
        $sField = '';

        foreach ($field->options as $val => $text) {
            $sField .= '<label>';
            $sField .= '<input type="radio" name="' . $field->name . '" value="' . $val . '" ';
            if ($val === $field->value)
                $sField .= ' checked ';
            $sField .= ' />';
            $sField .= '<span class="lbl"> ' . $text . '</span>';
            $sField .= '</label>';
        }

        return $sField;
    }

    /**
     * Loops through the form object and saves the values as options via PeepSoConfigSettings.
     * @return void
     */
    private function _save()
    {
        foreach ($this->config_object->get_form()->fields as $field) {
            PeepSoConfigSettings::get_instance()->set_option(
                $field['name'],
                $field['value']
            );
        }

        do_action('peepso_config_after_save-' . $this->curtab);
    }

    /**
     * @TODO this is a temporary hack to remove dependency on the WP method,
     * do_meta_boxes implementation tends to change from version to version
     * should be at some point rewritten to something more PeepSo-ish
     * */
    function do_meta_boxes( $screen, $context, $object ) {
        global $wp_meta_boxes;
        static $already_sorted = false;

        if ( empty( $screen ) )
            $screen = get_current_screen();
        elseif ( is_string( $screen ) )
            $screen = convert_to_screen( $screen );

        $page = $screen->id;

        $hidden = get_hidden_meta_boxes( $screen );

        printf('<div id="%s-sortables" class="meta-box-sortables">', htmlspecialchars($context));

        // Grab the ones the user has manually sorted. Pull them out of their previous context/priority and into the one the user chose
        if ( ! $already_sorted && $sorted = get_user_option( "meta-box-order_$page" ) ) {
            foreach ( $sorted as $box_context => $ids ) {
                foreach ( explode( ',', $ids ) as $id ) {
                    if ( $id && 'dashboard_browser_nag' !== $id ) {
                        add_meta_box( $id, null, null, $screen, $box_context, 'sorted' );
                    }
                }
            }
        }

        $already_sorted = true;

        $i = 0;

        if ( isset( $wp_meta_boxes[ $page ][ $context ] ) ) {
            foreach ( array( 'high', 'sorted', 'core', 'default', 'low' ) as $priority ) {
                if ( isset( $wp_meta_boxes[ $page ][ $context ][ $priority ]) ) {
                    foreach ( (array) $wp_meta_boxes[ $page ][ $context ][ $priority ] as $box ) {
                        if ( false == $box || ! $box['title'] )
                            continue;
                        $i++;
                        $hidden_class = in_array($box['id'], $hidden) ? ' hide-if-js' : '';
                        echo '<div id="' . $box['id'] . '" class="postbox ' . postbox_classes($box['id'], $page) . $hidden_class . '" ' . '>' . "\n";
                        if ( 'dashboard_browser_nag' != $box['id'] ) {
                            echo '<button type="button" class="handlediv button-link" aria-expanded="true">';
                            echo '<span class="screen-reader-text">' . sprintf( __( 'Toggle panel: %s' ), $box['title'] ) . '</span>';
                            echo '<span class="toggle-indicator" aria-hidden="true"></span>';
                            echo '</button>';
                        }
                        echo "<h3 class='hndle'><span>{$box['title']}</span></h3>\n";
                        echo '<div class="inside">' . "\n";
                        call_user_func($box['callback'], $object, $box);
                        echo "</div>\n";
                        echo "</div>\n";
                    }
                }
            }
        }

        echo "</div>";

        return $i;

    }
}
