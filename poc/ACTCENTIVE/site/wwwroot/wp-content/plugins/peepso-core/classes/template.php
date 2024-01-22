<?php

class PeepSoTemplate
{
	private static $_template_dirs = NULL;
	private static $_template_dir = 'templates';

	/*
	 * Adds a directory to the list of template directories. Can be used by add-ons
	 * to include additional directories to look for class files in.
	 * @param string $dirname the directory name to be added
	 */
	public static function add_template_directory($dirname)
	{
		if (substr($dirname, -1) != DIRECTORY_SEPARATOR)
			$dirname .= DIRECTORY_SEPARATOR;
		self::$_template_dirs[] = $dirname;
	}

	/**
	 * Allows themes to override peepso templates.
	 * Retrieve the name of the highest priority template file that exists, loading that file.
	 *
	 * @param  string $sName The template name to search for.
	 * @return string        The template filename, if one is located, an empty string, if not.
	 */
	public static function locate_template($sName)
	{
		$sTempl = locate_template('peepso' . DIRECTORY_SEPARATOR . $sName . '.php');
		if (empty($sTempl)) {
			foreach (self::$_template_dirs as $dir) {
				if (file_exists($sFile = $dir . self::$_template_dir . DIRECTORY_SEPARATOR . $sName . '.php')) {
					$sTempl = $sFile;
					break;
				}
			}
		}

		return ($sTempl);
	}


	/*
	 * return fully qualified name of template file
	 * @param string $section The section of the application to load template from
	 * @param string $template The name of the template file to load
	 * @return string Name of template file
	 */
	public static function get_template($section, $template)
	{
		$sSect = apply_filters('peepso_get_template_section', $section, $template);
		$sTmpl = apply_filters('peepso_get_template_name', $template, $section);

		$sFile = self::locate_template($sSect . DIRECTORY_SEPARATOR . $sTmpl);
		$name = apply_filters('peepso_get_template_name', $sFile);

		return ($name);
	}


	/*
	 * execute a template with provided data
	 * @param string $section application section to load the template from
	 * @param string $template name of template file within section to load
	 * @param array $data array of data variables to pass to template
	 * @param boolean if TRUE will return the output of the template
	 */
	public static function exec_template($section, $template, $data = NULL, $return_output = FALSE)
	{
        /**
         * @since WordPress 5.8 legacy-widget-preview GET was set
         * @since WordPress 5.9 it is no longer passed, so we force set it when called from the WP preview URL
         */
        if( stristr($_SERVER['REQUEST_URI'],'wp-json') && stristr($_SERVER['REQUEST_URI'],'widget-types') ) {
            $_GET['legacy-widget-preview'] = TRUE;
        }

	    // If the Block Editor preview is loading a widget, pass args through Gecko filters to modify the args
        if(is_array($data) && isset($data['args']) && isset($_GET['legacy-widget-preview'])) {

            $data['args'] = apply_filters('peepso_fix_block_legacy_widget', $data['args'], $data['instance']);

            //echo "<span style='opacity:0.5;font-size:10px;'>".htmlspecialchars($data['args']['before_widget'])."</span>";
        }

		$templ = self::get_template($section, $template);

		if ($return_output)
			ob_start();

		do_action('peepso_action_before_exec_template',$section, $template, $data, $return_output);

		if (NULL !== $data)
			extract($data);
		include($templ);

        do_action('peepso_action_after_exec_template',$section, $template, $data, $return_output);
		if ($return_output) {
			$ret = ob_get_clean();
			return ($ret);
		}
	}

	/**
	 * Return the opening wrapper
	 * @return string
	 */
	public static function get_before_markup()
	{
		return (apply_filters('peepso_template_before_markup', '<div id="peepso-wrap" class="container-fluid">'));
	}

	/**
	 * Return the closing wrapper
	 * @return string
	 */
	public static function get_after_markup()
	{
		$html = '</div>'; // #peepso-wrap
		return (apply_filters('peepso_template_after_markup', $html));
	}

	/**
	 * Define which directory the templates should treat as root
	 * @param string $dir The directory name
	 */
	public static function set_template_dir($dir)
	{
		self::$_template_dir = $dir;
	}

	/*
	 * Convert two dates into a string representing the elapsed time between the dates
	 * @param int $start The starting date
	 * @param int $now The current date
	 * @return string The difference between the dates expressed as a string, as in 3 weeks, 2 days
	 */
	public static function time_elapsed($start, $now)
	{
		if (abs($start - $now) < 60) {
			return __('just now', 'peepso-core');
		} else {
			return sprintf(__('%s ago', 'peepso-core'), human_time_diff_round_alt($start, $now));
		}
	}

	public static function do_404() {
	    return self::exec_template('general','404', NULL, TRUE);
    }
}

// EOF
