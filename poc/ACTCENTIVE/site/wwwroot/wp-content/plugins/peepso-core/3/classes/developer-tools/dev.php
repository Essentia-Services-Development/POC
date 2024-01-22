<?php

class PeepSo3_Developer_Tools
{
	static $instance = NULL;

	public $pages= array();
    public $pages_config;

	const PLUGIN_VERSION = '4.0.1';
	const EDD_ITEM_ID = 119;

	private function __construct()
	{
        if(!PeepSo::is_admin()) { return; }
		// Handle the wp-admin pages: menus, contents and exports
		add_action('admin_menu', function() {

			$show_developer_tools = isset($_REQUEST['show_developer_tools']) ? intval($_REQUEST['show_developer_tools']) : PeepSo::get_option_new('show_developer_tools', 0);
			if(!$show_developer_tools) {
				return;
			}

			$title_extra = ' | ' . 'PeepSo Dev Tools';
			add_menu_page(
				$this->pages['home']->title . $title_extra,
				'Developer Tools <br/><nobr><small>by PeepSo </small></nobr>',
				'manage_options',
				'peepso_developer_tools_home',
				array( $this->pages['home'], 'page'),
				'dashicons-admin-tools',
				75
			);

			foreach($this->pages_config as $page) {
				if('home'!=$page) {
					$class = $this->pages[$page];
					add_submenu_page('peepso_developer_tools_home', $class->title.$title_extra, $class->title, 'manage_options', 'peepso_developer_tools_'.$page, array($class, 'page'));
				}
			}
		});

		add_action('admin_init',			array(&$this, 'action_admin_export'));

		add_action( 'wp_ajax_peepso_log', function() {

			if(class_exists('PeepSo') && PeepSo::is_admin()) {
				$file = get_option('peepso_debug_file');
				if (!$file) {
					$file = md5(microtime() . $_SERVER['HTTP_HOST']);
					update_option('peepso_debug_file', $file);
				}

				$path = PeepSo::get_peepso_dir().$file.'.log';
				$trans = 'peepso_log_'.$_GET['hash'];

				$whence = SEEK_SET;
				if(!strlen($seek = get_transient($trans))) {
					$seek = -10;
					$whence = SEEK_END;
				}

				$handle = fopen($path, 'r');

				fseek($handle, $seek, $whence);

				while (($line = fgets($handle)) !== false) {
					echo htmlspecialchars($line);
				}

				set_transient($trans, ftell($handle), 24*3600);

				exit();
			}
		});

		$this->pages_config = array(
			'home',
			'peepso_log',
			'peepso_mayfly',
			'report',
			'phpinfo',
			'git',
		);

		foreach($this->pages_config as $page) {
			require_once(plugin_dir_path(__FILE__) . DIRECTORY_SEPARATOR . 'devtools_page_'.$page.'.php');
			$class='PeepSo3_Developer_Tools_Page_'.$page;
			$this->pages[$page] = new $class();

			if(isset($_GET['page']) && 'peepso_developer_tools_'.$page == $_GET['page']) {
				remove_all_actions('admin_notices');
				remove_all_actions('all_admin_notices');
				add_filter('peepso_developer_tools_buttons',array($class,'peepso_developer_tools_buttons'));
			}
		}
	}

	public static function get_instance()
	{
		if (NULL === self::$instance) {
			self::$instance = new self();
		}
		return (self::$instance);
	}

	# # # # # # # # # # Admin Pages Rendering & Export # # # # # # # # # #

	/**
	 * Handles the file export
	 * @return void
	 */
	public function action_admin_export()
	{
		// Check if the export is happening and the handler class is loaded
		if(!isset($_POST['system_report_export']) || !key_exists($name = $_POST['export_content'], $this->pages)) {
			return;
		}

        if(!PeepSo::is_admin()) {
            return;
        }

		// The handler class is already initialized
		$class = $this->pages[$name];

		// Output the page data as a file
		$this->file_export($class->page_data(), $name, $class->file_mime, $class->file_extension);
	}

	private function file_export($content, $file_name, $file_mime, $file_extension)
	{
		$file = sanitize_title_with_dashes(get_bloginfo('name') . ' ' . $file_name, '', 'save') . '.' . $file_extension;
		nocache_headers();
		header("Content-type: $file_mime");
		header('Content-Disposition: attachment; filename="' . $file . '"');
		exit($content);
	}
	# # # # # # # # # # Utils # # # # # # # # # #

	/**
	 * Returns the assets directory path
	 * @return string
	 */
	public static function assets_path()
	{
		return plugin_dir_url(dirname(dirname(__FILE__))) . 'assets'.DIRECTORY_SEPARATOR;
	}

	public static function num_convt( $v ) {
		$l   = substr( $v, -1 );
		$ret = substr( $v, 0, -1 );

		switch ( strtoupper( $l ) ) {
			case 'P': // fall-through
			case 'T': // fall-through
			case 'G': // fall-through
			case 'M': // fall-through
			case 'K': // fall-through
				$ret *= 1024;
				break;
			default:
				break;
		}

		return $ret;
	}

	/**
	 * Slightly modified version of http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/
	 * @author Torleif Berger, Lorenzo Stanco
	 * @link http://stackoverflow.com/a/15025877/995958
	 * @license http://creativecommons.org/licenses/by/3.0/
	 */
	public static function tailCustom($filepath, $lines = 1, $adaptive = true) {

		// Open file
		$f = @fopen($filepath, "rb");
		if ($f === false) return false;

		// Sets buffer size, according to the number of lines to retrieve.
		// This gives a performance boost when reading a few lines from the file.
		if (!$adaptive) $buffer = 4096;
		else $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));

		// Jump to last character
		fseek($f, -1, SEEK_END);

		// Read it and adjust line number if necessary
		// (Otherwise the result would be wrong if file doesn't end with a blank line)
		if (fread($f, 1) != "\n") $lines -= 1;

		// Start reading
		$output = '';
		$chunk = '';

		// While we would like more
		while (ftell($f) > 0 && $lines >= 0) {

			// Figure out how far back we should jump
			$seek = min(ftell($f), $buffer);

			// Do the jump (backwards, relative to where we are)
			fseek($f, -$seek, SEEK_CUR);

			// Read a chunk and prepend it to our output
			$output = ($chunk = fread($f, $seek)) . $output;

			// Jump back to where we started reading
			fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);

			// Decrease our line counter
			$lines -= substr_count($chunk, "\n");

		}

		// While we have too many lines
		// (Because of buffer size we might have read too many)
		while ($lines++ < 0) {

			// Find first newline and remove all text before that
			$output = substr($output, strpos($output, "\n") + 1);

		}

		// Close file and return
		fclose($f);
		return trim($output);

	}
}



// EOF