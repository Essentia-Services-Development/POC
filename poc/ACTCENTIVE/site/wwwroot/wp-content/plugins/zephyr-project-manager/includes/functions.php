<?php

// Zephyr helper and resuable functions

if (!defined('ABSPATH')) {
	die;
}

use ZephyrProjectManager\Core\Utillities;
use ZephyrProjectManager\ZephyrProjectManager;
use ZephyrProjectManager\Core\Tasks;
use ZephyrProjectManager\Zephyr;

function zpm_add_scheduled_events() {
	add_action('zpm_daily_processes', 'zpm_daily_scheduled_tasks');
	add_action('zpm_hourly_processes', 'zpm_hourly_processes');
	$dailyHook = 'zpm_daily_processes';
	$hourlyHook = 'zpm_hourly_processes';

	if (!wp_next_scheduled($dailyHook)) {
		wp_schedule_event(strtotime('00:00:00'), 'daily', $dailyHook);
	}

	if (!wp_next_scheduled($hourlyHook)) {
		wp_schedule_event(time(), 'hourly', $hourlyHook);
	}
}

function zpm_daily_scheduled_tasks() {
	$manager = ZephyrProjectManager();
	$tasks = $manager::get_tasks();

	foreach ($tasks as $task) {
		Tasks::recur_task($task);
	}
}

function zpm_hourly_processes() {
	if (Utillities::getSetting('ics_sync_enabled')) {
		Tasks::syncIcs();
	}
}

function zpm_array_to_comma_string($array) {
	$string = '';

	if (is_array($array)) {
		$string = '';

		foreach ($array as $key => $value) {
			$string .= $value;

			if ($key !== (sizeof($array) - 1)) {
				$string .= ',';
			}
		}
	}

	return $string;
}

function zpm_get_colors() {
	$general_settings = Utillities::general_settings();
	$primary = $general_settings['primary_color'];
	$primary_light = $general_settings['primary_color_light'];
	$primary_shifted = Utillities::adjust_brightness($primary, -40);
	$primary_dark = $general_settings['primary_color_dark'];
	$primary_dark_adjust = Utillities::adjust_brightness($primary, -40);

	$colors = array(
		'primary' => $primary,
		'primary_light' => $primary_light,
		'primary_dark' => $primary_shifted,
		'secondary' => $primary_dark,
		'secondary_dark' => $primary_dark_adjust
	);

	return $colors;
}

function zpm_get_primary_color() {
	$colors = zpm_get_colors();
	$primary = $colors['primary'];

	if (function_exists('zpm_get_primary_frontend_color')) {
		$primary = zpm_get_primary_frontend_color();
	}

	return $primary;
}

function zpm_user_has_role($user_id, $role) {
	$u = new WP_User($user_id);

	if (in_array($role, (array) $u->roles)) {
		return true;
	}

	return false;
}

function zpm_get_pages() {
	$zpm_pages = array(
		'zephyr_project_manager',
		'zephyr_project_manager_tasks',
		'zephyr_project_manager_files',
		'zephyr_project_manager_activity',
		'zephyr_project_manager_progress',
		'zephyr_project_manager_calendar',
		'zephyr_project_manager_settings',
		'zephyr_project_manager_projects',
		'zephyr_project_manager_categories',
		'zephyr_project_manager_teams_members',
		'zephyr_project_manager_asana',
		'zephyr_project_manager_reports',
		'zephyr_project_manager_custom_fields',
		'zephyr_project_manager_purchase_premium',
		'zephyr_project_manager_asana_settings',
		'zephyr_project_manager_devices',
		'zephyr_project_manager_help',
		'zephyr_project_manager_extensions',
		'zephyr_project_manager_milestones',
		'zephyr_project_manager_kanban'
	);

	$zpm_pages = apply_filters('zpm_hide_notice_pages', $zpm_pages);
	return $zpm_pages;
}

function isZephyrPage() {
	$pages = zpm_get_pages();

	if (isset($_REQUEST['page'])) {
		if (in_array($_REQUEST['page'], $pages)) {
			return true;
		}
	}

	return false;
}

function zpmIsProjectsPage() {
	if (isset($_REQUEST['page'])) {
		if ($_REQUEST['page'] == 'zephyr_project_manager_projects') {
			return true;
		}
	}

	return false;
}

function zpmIsCalendarPage() {
	if (isset($_REQUEST['page'])) {
		if ($_REQUEST['page'] == 'zephyr_project_manager_calendar') {
			return true;
		}
	}

	return false;
}

function zpmIsTasksPage() {
	if (isset($_REQUEST['page'])) {
		if ($_REQUEST['page'] == 'zephyr_project_manager_tasks') {
			return true;
		}
	}

	return false;
}

function ZephyrProjectManager() {
	return ZephyrProjectManager::get_instance();
}

function zpm_get_attachment_types() {
	$attachment_types = [
		'attachment'
	];
	$attachment_types = apply_filters('zpm_attachment_types', $attachment_types);
	return $attachment_types;
}

function zpm_get_company_name() {
	return apply_filters('zpm_company_name', 'Zephyr');
}

function zpm_is_frontend() {
	if (!is_admin() || (isset($_REQUEST['frontend']) && $_REQUEST['frontend'] == true)) {
		return true;
	}
	return false;
}

function zpm_get_extensions() {
	$extensions = [
		[
			'link' => 'https://zephyr-one.com/purchase-pro/',
			'title' => 'Zephyr Project Manager Pro',
			'description' => 'Zephyr Project Manager Pro contains many new features to help with your projects including Kanban Boards, Gantt Charts, Reporting, Customizable Frontend Project Manager, Asana Integration, Custom Fields, Templates and many more useful features.
Some features include:
- Kanban style rojects
- Gantt style projects
- Customizable Frontend Project Manager
- Custom Fields
- Templates
- Asana Integration
- Reporting
- And much more...',
			'color' => '#137cc6',
			'installed' => apply_filters('zpm_pro_installed', false)
		],
		[
			'link' => 'https://zephyr-one.com/woocommerce-integration/',
			'title' => 'Zephyr - WooCommerce Integration',
			'description' => 'Implement Zephyr Project Manager into your WooCommerce workflow to automatically create tasks and projects when orders are placed or payments are made and simplify your workflow while keeping everything organized and on track. Some key features include:
- Choose when to create the project - when order is placed or only after payment is received
- Choose whether to create tasks when multiple items are purchased in a product
- Select agent to automatically assign the projects to
- And more...',
			'color' => '#9b5c8f',
			'installed' => apply_filters('zpm_woocommerce_installed', false)
		],
		[
			'link' => 'https://zephyr-one.com/google-integration/',
			'title' => 'Zephyr - Google Integration',
			'description' => 'Sync your tasks with Google Calendar and integrate with other Google products to keep everything synced and integrated to improve productivity.
Some features include:
- Google Calendar integration
- Sync your Zephyr tasks with any of your Google Calendars
- Sync different projects to different calendars
- Any changes in Zephyr are automatically updated on your Google Calendar and newly created tasks are added to the calendar instantly as well
- More Google features coming soon...',
			'color' => '#4DAA53',
			'installed' => apply_filters('zpm_google_installed', false)
		]
	];
	return $extensions;
}

function zpm_get_version() {
	$version = Zephyr::getPluginVersion();
	return $version;
}

function zpm_is_single_project() {
	if (isset($_GET['project']) || isset($_POST['project_id']) || isset($_REQUEST['project_id'])) return true;
	if (isset($_GET['action']) && $_GET['action'] == 'project' && isset($_GET['id'])) return true;
	return false;
}

function zpm_get_single_project_id() {
	if (isset($_GET['project'])) return $_GET['project'];
	if (isset($_POST['project_id'])) return $_POST['project_id'];
	if (isset($_REQUEST['project_id'])) return $_REQUEST['project_id'];
	if (isset($_GET['action']) && $_GET['action'] == 'project' && isset($_GET['id'])) return $_GET['id'];
	return -1;
}

function zpm_is_image($url) {
	$size = @getimagesize($url);
	if (!is_array($size)) return false;
	return (strtolower(substr($size['mime'], 0, 5)) == 'image' ? true : false);
}

function zpm_get_current_task_id() {
	if (isset($_GET['task_id'])) {
		return sanitize_text_field(intval($_GET['task_id']));
	}
	if (isset($_POST['task_id'])) {
		return sanitize_text_field(intval($_POST['task_id']));
	}
	if (isset($_GET['action']) && isset($_GET['id'])) {
		if ($_GET['action'] == 'task') {
			return sanitize_text_field(intval($_GET['id']));
		}
	}
	return -1;
}

function zpm_move_array_element(&$array, $a, $b) {
	$out = array_splice($array, $a, 1);
	array_splice($array, $b, 0, $out);
}

function zpmIsPro() {
	if (class_exists('ZephyrProjectManager\\Pro\\Plugin')) {
		return true;
	}

	return false;
}

function zpmIsFrontendEnabled() {
	if (zpmIsPro()) {
		$settings = get_option('zpm_frontend_settings', array());
		$enabled = isset($settings['frontend_enabled']) ? $settings['frontend_enabled'] : false;
		return $enabled == true;
	}

	return false;
}

function zpm_get_timezone() {
	$tzstring = get_option('timezone_string');
	$offset = get_option('gmt_offset');

	if (empty($tzstring) && 0 != $offset && floor($offset) == $offset) {
		$offset_st = $offset > 0 ? "-$offset" : '+' . absint($offset);
		$tzstring  = 'Etc/GMT' . $offset_st;
	}

	if (empty($tzstring)) {
		$tzstring = 'UTC';
	}

	$timezone = new DateTimeZone($tzstring);
	return $timezone;
}

function zpm_get_datetime($date = 'now') {
	try {
		$datetime = new DateTime($date);
		$datetime->setTimezone(zpm_get_timezone());
	} catch (Exception $e) {
		try {
			$datetime = new DateTime($date);
		} catch (Expection $e) {
			return null;
		}
	}

	return $datetime;
}

function zpm_esc_html($html) {
	return wp_kses_post(stripslashes($html));
}

function zpm_sanitize_int($int) {
	return intval(sanitize_text_field($int));
}

function zpm_sanitize_key($value) {
	$value = strtolower($value);
	$value = str_replace(' ', '_', $value);
	return sanitize_key($value);
}

function zpm_sanitize_array($array = []) {
	if (!is_array($array)) return sanitize_text_field($array);

	foreach ($array as $idx => $item) {
		if (is_array($item)) {
			$array[$idx] = zpm_sanitize_array($item);
		} else {
			if (is_numeric($item)) {
				$array[$idx] = zpm_sanitize_int($item);
			} else {
				$array[$idx] = sanitize_text_field($item);
			}
		}
	}

	return (array) $array;
}

function zpm_sanitize_bool($bool) {
	if ($bool === 'true' || $bool === '1') $bool = true;
	if ($bool === 'false' || $bool === '0') $bool = false;

	return boolval($bool);
}

function zpm_is_required_pro_version() {
	if (!class_exists('\\ZephyrProjectManager\\Pro\\Plugin')) return true;

	$pro = new \ZephyrProjectManager\Pro\Plugin();
	$isRequiredVersion = version_compare($pro->getVersion(), ZPM_REQUIRED_PRO_VERSION, '>=');
	return $isRequiredVersion;
}

function zpm_date($date, $default = 'none', $format = '', $includeTime = false) {
	global $zpmSettings;

	$format = !empty($format) ? $format : $zpmSettings['date_format'];

	if ($includeTime) {
		$format .= ' H:i';
	}

	$default = $default !== 'none' ? $default : __('None', 'zephyr-project-manager');
	$date = strtotime($date) != '0000-00-00' && date('Y', strtotime($date)) != '-0001' ? date($format, strtotime($date)) : $default;

	if (strpos($date, '00:00') !== false) {
		$date = str_replace(' 00:00', '', $date);
	}

	if (!zpm_is_date_valid($date)) {
		$date = $default;
	}

	return $date;
}

function zpm_is_date_valid($date) {
	$time = is_numeric($date) ? $date : strtotime($date);
	if (in_array($date, ['00-00-0000 00:00:00', '0000-00-00 00:00:00'])) return false;
	$date = date('d-m-Y H:i:s', $time);
	if (in_array($date, ['00-00-0000 00:00:00', '0000-00-00 00:00:00'])) return false;
	$year = date('Y', $time);
	if ($year === '0001') return false;
	return date('Y-m-d', $time) !== '1970-01-01';
}

function zpm_date_i18n($date, $default = 'none', $format = '') {
	global $zpmSettings;

	$format = !empty($format) ? $format : $zpmSettings['date_format'];
	$default = $default !== 'none' ? $default : __('None', 'zephyr-project-manager');
	$dateIl8n = strtotime($date) != '0000-00-00' && date('Y', strtotime($date)) != '0001' ? date_i18n($format, strtotime($date)) : $default;

	if (!zpm_is_date_valid(date($format, strtotime($date)))) {
		$dateIl8n = '';
	}

	return $dateIl8n;
}

function zpm_get_image($image) {
	return ZPM_PLUGIN_URL . "assets/img/$image";
}

function zpm_log($key, $message) {
	if (is_string($message)) {
		$log = $key . ': ' . $message . PHP_EOL;
	} else {
		ob_start();
		print_r($message);
		$message = ob_get_clean();
		$log = $key . ': ' . $message . PHP_EOL;
	}
	$log .= '___' . PHP_EOL;
	file_put_contents(wp_upload_dir()['basedir'] . '/zpm-log.log', $log, FILE_APPEND);
}

function zpm_dump($var, $title = null) {
	?>
	<div class="zpm-dump">
		<?php if (!is_null($title)): ?>
			<div><h6><?php echo $title; ?></h6></div>
		<?php endif; ?>
		<?php if (is_array($var)): ?>
			<div>Size: <?php echo count($var); ?></div>
		<?php endif; ?>
		<pre><?php print_r($var); ?></pre>
	</div>
	<?php
}

function zpm_is_dev() {
	return isset($_GET['zpm-dev']) || isset($_GET['dev']);
}

function _zpm_e($string) {
	$string = __($string, 'zephyr-project-manager');
	$string = apply_filters('zpm/string', $string);
	echo $string;
}

function zpm_get_template($templateName, $args = []) {
	$templatePath = ZPM_PLUGIN_PATH . "/templates/{$templateName}.php";

	extract($args);

	if (!file_exists($templatePath)) return '';

	$override = apply_filters("zpm/template/$templateName", '', $args);

	if (!empty($override)) {
		return $override;
	}

	ob_start();

	include($templatePath);
	$html = ob_get_clean();
	return $html;
	// return apply_filters("zpm/template/$templateName/", $html);
}

function zpm_label($label) {
	$key = sanitize_key($label);
	$labels = [
		'project' => __('Project', 'zephyr-project-manager'),
		'projects' => __('Projects', 'zephyr-project-manager'),
		'task' => __('Task', 'zephyr-project-manager'),
		'tasks' => __('Tasks', 'zephyr-project-manager'),
		'subtask' => __('Subtask', 'zephyr-project-manager'),
		'subtasks' => __('Subtasks', 'zephyr-project-manager'),
		'milestone' => __('Milestone', 'zephyr-project-manager'),
		'milestones' => __('Milestones', 'zephyr-project-manager'),
	];
	$value = isset($labels[$key]) ? $labels[$key] : $label;
	$value = __($value, 'zephyr-project-manager');
	return apply_filters("zpm/labels/$key", $value);
}

function zpm_label_string($string) {
	preg_match_all('/\{([^}]+)\}/', $string, $matches);
	$placeholders = $matches[1];

	foreach ($placeholders as $placeholder) {
		$string = str_replace('{' . $placeholder . '}', zpm_label($placeholder), $string);
	}

	return __($string, 'zephyr-project-manager');
}

// $string = zpm_label_string('All {projects}, {tasks} and {milestones} as well as {custom_fields} here');
// var_dump($string);