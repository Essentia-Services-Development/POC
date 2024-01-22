<?php

class PeepSoManage
{
    private static $instance = NULL;
    public static $slug = 'peepso-manage';
    public $curtab;

    // Calls get_instance() to start
    public static function init()
    {
        $queue = self::get_instance();
        $queue->render();
    }

    // Return an instance of PeepSoConfig
    public static function get_instance()
    {
        if (NULL === self::$instance)
            self::$instance = new self();
        return self::$instance;
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
            PeepSo::redirect('wp-admin/404');
        }

        return $tabs[$tab];
    }


	/*
	 * Build a list of tabs to display at the top of config pages
	 * @return array List of tabs to display on config pages
	 */
	public function get_tabs()
	{
		$default_tabs = array(
			'reports' => array(
				'label' => __('Reported Items', 'peepso-core'),
				'tab' => 'reports',
				'menu' => __('Reported Items', 'peepso-core'),
				'function' => array('PeepSoAdminReport', 'dashboard'),
				'cat' => 'foundation',
                'icon' => 'https://cdn.peepso.com/icons/configsections/reports.svg',
			),
			'profile-fields' => array(
				'label' => __('Profile Fields', 'peepso-core'),
				'tab' => 'profile-fields',
				'menu' => __('Profile Fields', 'peepso-core'),
				'function' => array('PeepSoAdminProfiles', 'administration'),
				'cat' => 'foundation',
                'icon' => 'https://cdn.peepso.com/icons/configsections/profile_fields.svg',
			),
            'reactions' => array(
                'label' => __('Reactions', 'peepso-core'),
                'tab' => 'reactions',
                'menu' => __('Reactions', 'peepso-core'),
                'function' => array('PeepSoAdminReactions', 'administration'),
                'cat' => 'foundation',
                'icon' => 'https://cdn.peepso.com/icons/configsections/reactions.svg',
            ),
			'vip-icons' => array(
				'label' => __('VIP Icons', 'peepso-core'),
				'icon' => 'https://cdn.peepso.com/icons/plugins/97358.svg',
				'tab' => 'vip-icons',
				'description' => '',
				'function' => array('PeepSoVipIconAdmin', 'administration'),
				'cat'   => 'foundation',
			)
        );

        $brute_force_enabled = PeepSo::get_option('brute_force_enable', FALSE);
        if ($brute_force_enabled) {
        	$default_tabs['brute-force'] = array(
        		'label' => __('Login Attempts', 'peepso-core'),
				'tab' => 'brute-force',
				'menu' => __('Login Attempts', 'peepso-core'),
				'function' => array('PeepSoAdminBruteForce', 'administration'),
				'cat' => 'foundation',
                'icon' => 'https://cdn.peepso.com/icons/configsections/reports.svg',
        	);
        }

        if (PeepSo::get_option_new('post_backgrounds_enable')) {
			$default_tabs['post-backgrounds'] = array(
				'label' => __('Post backgrounds', 'peepso-core'),
				'tab' => 'post-backgrounds',
				'menu' => __('Post backgrounds', 'peepso-core'),
				'function' => array('PeepSoAdminPostBackgrounds', 'administration'),
				'cat' => 'foundation',
				'icon' => 'https://cdn.peepso.com/icons/configsections/post_backgrounds.svg',
			);
		}

        $tabs = apply_filters('peepso_admin_manage_tabs', array());

        $tabs_by_cat=array();
        foreach($tabs as $key=>$tab) {
            $cat = isset($tab['cat']) ? $tab['cat'] : 'thirdparty';

            $tab['key'] = $key;
            $tabs_by_cat[$cat][$tab['label']] = $tab;
            ksort($tabs_by_cat[$cat]);
        }

        $tabs = array();

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

        if(isset($tabs_by_cat['thirdparty'])) {
            foreach($tabs_by_cat['thirdparty'] as $key=>$tab) {
                $tabs[$tab['key']] = $tab;
            }
        }

        $tabs = array_merge($default_tabs, $tabs);

        return ($tabs);
    }

    // @todo docblock
    public function render()
    {
        wp_enqueue_media();
        wp_enqueue_script('peepso-admin-config');

        $input = new PeepSoInput();
        $tab = $this->curtab = $input->value('tab', 'reports', FALSE); // SQL Safe

        $aTab = $this->get_tab($tab);

        $this->render_tabs();

        call_user_func_array($aTab['function'], array());
    }

	/*
	 * Display the tabs
	 */
	public function render_tabs()
	{

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker');
        // wp_enqueue_script( 'wp-color-picker-script-handle', plugins_url('wp-color-picker-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );

	    ob_start();
	    $current_title = __('Manage', 'peepso-core');

        $input = new PeepSoInput();
        $curtab = $input->value('tab', 'reports', FALSE); // SQL Safe

        $old_cat = 'foundation';

        $c = array(
            'foundation'=>'rgb(207,65,59)',
            'foundation-profiles'=>'rgb(227,85,79)',
            'core'=>'#ddddff',
            'extras'=>'#ddffdd',
            'integrations'=>'#fdfddd',
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
                $current_title =  $current_title . ' ' . $tab['label'];
            }

            $color = $c['default'];
            if(isset($c[$cat])) {
                $color = $c[$cat];
            }

            echo '<div  class="psa-navbar__item ', $activeclass, '">', PHP_EOL;
            echo '<a class="ps-tooltip ps-tooltip-cat-'.$cat.'" style="background-color:',$color,' !important;" href="', admin_url('admin.php?page='), self::$slug;
            if (!empty($tab['tab']))
                echo '&tab=', $tab['tab'];
            echo '"';
            echo '>';

            if(isset($tab['icon'])) {
                echo '<img src="'.$tab['icon'].'" height="32" />';
            }

            echo    '<div class="ps-label-optional" style="display:inline-block;margin-left:10px !important;;">' . esc_attr($tab['label']) . '</div>';
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
    }
}
