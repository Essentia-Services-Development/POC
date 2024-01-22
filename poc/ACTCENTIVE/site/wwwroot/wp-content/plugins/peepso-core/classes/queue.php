<?php

class PeepSoQueue
{
	private static $instance = NULL;
	public static $slug = 'peepso-queue';
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

		$msg_count = PeepSoMailQueue::get_pending_item_count();
        $req_count = PeepSoGdpr::get_pending_item_count();

		$default_tabs = array(
			'email' => array(
				'label' => __('Outgoing email', 'peepso-core'),
				'icon' => 'https://cdn.peepso.com/icons/configsections/settings_email.svg',
				'tab' => 'email',
				'menu' => __('Outgoing email', 'peepso-core'),
				'count' => intval($msg_count),
				'function' => array('PeepSoAdminMailQueue', 'administration'),
				'cat' => 'foundation'
			),

            'gdpr' => array(
                'label' => __('GDPR requests', 'peepso-core'),
                'icon' => 'https://cdn.peepso.com/icons/configsections/gdpr.svg',
                'tab' => 'gdpr',
                'description' => '',
                'count' => intval($req_count),
                'function' => array('PeepSoAdminRequestData', 'administration'),
                'cat' => 'foundation'
            )
        );

		$tabs = apply_filters('peepso_admin_queue_tabs', array());

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
		$tab = $this->curtab = $input->value('tab', 'email', FALSE); // SQL Safe

		$aTab = $this->get_tab($tab);

		$this->render_tabs();

		call_user_func_array($aTab['function'], array());
	}

	/*
	 * Display the tabs
	 */
	public function render_tabs()
	{
	    ob_start();
	    $current_title = __('Queues', 'peepso-core');

		$input = new PeepSoInput();
		$curtab = $input->value('tab', 'email', FALSE); // SQL Safe

		$old_cat = 'foundation';

		$c = array(
            'foundation'=>'rgb(207,65,59)',
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
                $current_title =  $current_title . ' <small>-</small> ' . $tab['label'];
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
            echo    '<div class="ps-label-optional"> &nbsp; ' . esc_attr($tab['label']) . '</div>';
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