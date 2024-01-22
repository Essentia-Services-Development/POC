<?php

if(!class_exists('PeepSo3_Developer_Tools_Page')) {
    require_once(dirname(__FILE__) . '/devtools_page.php');
    //new PeepSoError('Autoload issue: PeepSo3_Developer_Tools_Page not found ' . __FILE__);
}

class PeepSo3_Developer_Tools_Page_Git extends PeepSo3_Developer_Tools_Page
{
	public function __construct()
	{
		$this->title 		= __('Git Repositories', 'peepso_debug');
		$this->description	= __('Simple list of all git tracked plugins and themes. Exports as a formatted .txt file.', 'peepso_debug');
	}

	public function page()
	{
		$this->page_start('git');
		printf('<pre>%s</pre>', $this->page_data());
		$this->page_end();
	}

	public function page_data()
	{
		ob_start();

		$plugins_dir 	= WP_PLUGIN_DIR;
		$themes_dir 	= dirname(get_template_directory());

		$plugins_git 	= array();
		$themes_git		= array();

		$plugins = scandir($plugins_dir);
		foreach($plugins as $plugin_dir) {

			$plugin_dir = $plugins_dir.'/'.$plugin_dir;
			if(!is_dir($plugin_dir) || in_array($plugin_dir, array('.','..'))) continue;


			$git_dir = $plugin_dir.'/'.'.git';

			if(file_exists($git_dir)) {


				$files = scandir($plugin_dir);
				foreach($files as $file) {

					if(!stristr($file, '.php')) {
						continue;
					}

					$file_path = $plugin_dir.'/'.$file;
					$plugin_info = get_plugin_data($file_path);

					if(strlen($plugin_info['Name'])){
						break;
					}
				}

				$stringfromfile = file($git_dir.'/HEAD', FILE_USE_INCLUDE_PATH);

				$plugins_git[$plugin_info['Name']] = array (
					'version' 	=> $plugin_info['Version'],
					'git' 		=> $stringfromfile[0],
					'path'		=> dirname($file_path)
				);
			}
		}


		$themes = scandir($themes_dir);
		foreach($themes as $theme_dir) {

			$theme_dir = $themes_dir.'/'.$theme_dir;
			if(!is_dir($theme_dir) || in_array($theme_dir, array('.','..'))) continue;


			$git_dir = $theme_dir.'/'.'.git';

			if(file_exists($git_dir)) {

				$files = scandir($theme_dir);
				foreach($files as $file) {

					if(!stristr($file, '.php')) {
						continue;
					}

					$file_path = $theme_dir.'/'.$file;
					$theme_info = @get_theme_data($file_path);

					if(strlen($theme_info['Name'])){
						break;
					}
				}

				$stringfromfile = file($git_dir.'/HEAD', FILE_USE_INCLUDE_PATH);

				$themes_git[$theme_info['Name']] = array (
					'version' 	=> $theme_info['Version'],
					'git' 		=> $stringfromfile[0],
					'path'		=> dirname($file_path)
				);
			}
		}

		echo "### Begin Git Repositories Info ###\n";
		echo "\n\t".'** PLUGINS **'."\n";

		if(count($plugins_git)) {
			ksort($plugins_git);

			foreach($plugins_git as $plugin_name=>$plugin_info) {

				echo __("Plugin\t\t", 'wordpress-system-report' ) 	. $plugin_name;
				echo "\n";

				echo __("Version\t\t", 'wordpress-system-report' ) 	. $plugin_info['version'];
				echo "\n";

				echo __("Path\t\t", 'wordpress-system-report' ) 	. $plugin_info['path'];
				echo "\n";

				echo __("Git ref\t\t", 'wordpress-system-report' ) 	. $plugin_info['git'];
				echo "\n\n";
			}

		} else {
			echo __('It appears you have no plugins using git', 'wordpress-system-report' );
		}

		echo "\n\t".'** THEMES **'."\n";

		if(count($themes_git)) {
			ksort($themes_git);

			foreach($themes_git as $theme_name=>$theme_info) {

				echo __("Theme\t\t", 'wordpress-system-report' ) 	. $theme_name;
				echo "\n";

				echo __("Version\t\t", 'wordpress-system-report' ) 	. $theme_info['version'];
				echo "\n";

				echo __("Path\t\t", 'wordpress-system-report' ) 	. $theme_info['path'];
				echo "\n";

				echo __("Git ref\t\t", 'wordpress-system-report' ) 	. $theme_info['git'];
				echo "\n\n";
			}

		} else {
			echo __('It appears you have no themes using git', 'wordpress-system-report' );
		}

		echo '### End Git Repositories Info ###';
		$git = ob_get_contents();
		ob_end_clean();

		return $git;
	}
}