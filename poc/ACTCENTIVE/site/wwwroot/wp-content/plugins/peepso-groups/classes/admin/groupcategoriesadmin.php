<?php

class PeepSoGroupCategoriesAdmin
{
	public static function administration()
	{
		self::enqueue_scripts();

		$PeepSoGroupCategories = new PeepSoGroupCategories(TRUE, TRUE);
		$categories = $PeepSoGroupCategories->categories;

		// var_dump($categories);

		PeepSoTemplate::exec_template('admin', 'group_categories_list', $categories);
	}

	public static function enqueue_scripts()
	{
		wp_register_script('peepso-npm', PeepSo::get_asset('js/npm-expanded.min.js'),
			array('peepso'), PeepSo::PLUGIN_VERSION, 'all');

		wp_register_script('peepso-util', PeepSo::get_asset('js/util.min.js'),
			array('jquery', 'peepso-npm'), PeepSo::PLUGIN_VERSION, TRUE);

		wp_register_script('peepso-admin-groupcategories',
			PeepSo::get_asset('js/admin-groupcategories.js', dirname(dirname(__FILE__))),
			array('jquery', 'jquery-ui-sortable', 'underscore', 'peepso', 'peepso-util'), PeepSo::PLUGIN_VERSION, TRUE);

		//wp_register_script('peepso-admin-groupcategories', PeepSo::get_asset('js/groupcategories.min.js'),
		//	array('jquery', 'jquery-ui-sortable', 'underscore', 'peepso'), PeepSo::PLUGIN_VERSION, TRUE);

		wp_enqueue_script('peepso-admin-groupcategories');

		wp_enqueue_style('peepso-groupcategories-admin',
			PeepSo::get_asset('css/groupcategories.css', dirname(dirname(__FILE__))),
			array(), PeepSo::PLUGIN_VERSION);
	}
}
