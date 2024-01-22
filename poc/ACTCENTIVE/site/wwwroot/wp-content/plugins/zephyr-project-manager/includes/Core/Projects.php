<?php

/**
 * @package ZephyrProjectManager
 */

namespace ZephyrProjectManager\Core;

if (!defined('ABSPATH')) {
	die;
}

use \DateTime;
use ZephyrProjectManager\Api\Emails;
use ZephyrProjectManager\Core\Tasks;
use ZephyrProjectManager\Core\Members;
use ZephyrProjectManager\Core\Activity;
use ZephyrProjectManager\Core\Utillities;
use ZephyrProjectManager\Base\BaseController;
use ZephyrProjectManager\ZephyrProjectManager;

class Projects {
	private $settings;

	function __construct() {
		$this->settings = Utillities::general_settings();
		// Update project progress daily
		add_action('zpm_update_progress', array($this, 'update_progress'));
		$time = strtotime('00:00:00');
		$recurrence = 'daily';
		$hook = 'zpm_update_progress';

		if (!wp_next_scheduled($hook)) {
			wp_schedule_event($time, $recurrence, $hook);
		}

		// Send weekly email progress reports
		add_action('zpm_weekly_updates', array($this, 'weekly_updates'));
		$time = strtotime('00:00:00');
		$recurrence = 'weekly';
		$hook = 'zpm_weekly_updates';

		if (!wp_next_scheduled($hook)) {
			wp_schedule_event($time, $recurrence, $hook);
		}

		// Send daily updates on due tasks
		add_action('zpm_task_notifications', array($this, 'task_notifications'));
		$time = strtotime('00:00:00');
		$recurrence = 'daily';
		$hook = 'zpm_task_notifications';

		if (!wp_next_scheduled($hook)) {
			wp_schedule_event($time, $recurrence, $hook);
		}

		add_filter('zpm_filter_project', array($this, 'filter_project'));
		add_filter('zpm_filter_projects', array($this, 'filter_projects'));
		add_filter('zpm_should_show_project', array($this, 'should_show_project'), 1, 2);
	}

	public static function createProjectsTable() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'zpm_projects';
		$charset_collate = $wpdb->get_charset_collate();
		$columnFields = "id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id mediumint(9) NOT NULL,
			parent_id mediumint(9) NOT NULL,
			managers TEXT,
			assignees TEXT,
			name text NOT NULL,
			description text NOT NULL,
			completed boolean NOT NULL,
			archived boolean NOT NULL,
			team TEXT DEFAULT '',
			categories varchar(100) NOT NULL,
			status varchar(255) NOT NULL,
			date_created DATETIME NOT NULL,
			date_due DATETIME NOT NULL,
			date_start DATETIME NOT NULL,
			priority varchar(255),
			date_completed DATETIME NOT NULL,
			other_data TEXT NOT NULL,
			type varchar(255),
			other_settings varchar(999) NOT NULL";
		$columnFields = apply_filters('zpm_project_table_sql', $columnFields);
		$sql = "CREATE TABLE $table_name (
			$columnFields,
			UNIQUE KEY id (id)
		) $charset_collate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	public static function new_project($args = null) {
		global $wpdb;
		$table_name = ZPM_PROJECTS_TABLE;
		$defaults = array(
			'user_id'        => get_current_user_id(),
			'name'           => 'Untitled Project',
			'description'    => '',
			'team'           => '',
			'categories'     => '',
			'completed'      => '1',
			'date_start'     => date('Y-m-d H:i:s'),
			'date_due'       => '',
			'date_created'   => date('Y-m-d H:i:s'),
			'date_completed' => '',
			'priority'       => 'priority_none'
		);
		$data = wp_parse_args($args, $defaults);
		$wpdb->insert($table_name, $data);
		$new_project_id = $wpdb->insert_id;
		Activity::log_activity($data['user_id'], $wpdb->insert_id, '', esc_html($data['name']), 'project', 'project_added');
		return $new_project_id;
	}

	public static function get_projects($limit = null, $args = null, $filters = null, $public = false, $userId = null) {
		global $wpdb;
		$defaults = [
			'limit' => '-1'
		];
		$fields = 'id, user_id, name, description, completed, team, categories, status, date_created, date_due, date_start, date_completed, other_data, other_settings, type, priority';
		$table_name = ZPM_PROJECTS_TABLE;
		$prepare = [];
		$query = "SELECT * FROM $table_name ";

		if (!isset($args['archived'])) {
			$args['archived'] = '0';
		}

		if (!is_null($args)) {
			foreach ($args as $key => $value) {
				if (!strpos($query, 'WHERE')) {
					if (is_numeric($value)) {
						$query .= " WHERE {$key} = %d";
					} else {
						$query .= " WHERE {$key} = %s";
					}

					$prepare[] = $value;
				} else {
					$query .= " AND {$key} = %s";
					$prepare[] = $value;
				}
			}
		}

		$query .= apply_filters('zpm_get_projects_query', '');

		if (!is_null($limit)) {
			$query .= " LIMIT %s ORDER BY id DESC";
			$prepare[] = $limit;
		} else {
			$query .= " ORDER BY id DESC";
		}

		$query = !empty($prepare) ? $wpdb->prepare($query, $prepare) : $query;
		$projects = $wpdb->get_results($query);

		foreach ($projects as $project) {
			$project->status = !empty($project->status) ? maybe_unserialize($project->status) : [
				'status' => __('None', 'zephyr-project-manager'),
				'color' => 'priority_none'
			];
			if (is_string($project->status)) {
				$project->status = [
					'color' => $project->status
				];
			}
			$project->team = maybe_unserialize($project->team) ? maybe_unserialize($project->team) : array();
			$project->assignees = !is_null($project->assignees) ? $project->assignees : '';
		}

		if (!is_null($filters)) {
			if (isset($filters['category']) && $filters['category'] !== '-1' && $filters['category'] !== 'all') {
				$projects = Projects::filter_by_category($projects, $filters['category']);
			}
		}

		return array_filter($projects, function ($project) use ($public, $userId) {
			if ($public) return true;

			return Projects::has_project_access($project);
		});
	}

	public static function get_paginated_projects($limit = null, $offset = null) {
		$manager = ZephyrProjectManager();
		$projects = $manager::get_projects();
		$results = [];
		$i = 0;
		$j = 0;

		foreach ($projects as $project) {

			if ($i >= $offset) {

				if ($j < $limit) {
					if (!is_array($project->status)) {
						$project->status = !empty($project->status) ? maybe_unserialize($project->status) : array();
					}

					if (is_string($project->status)) {
						$project->status = [
							'color' => $project->status
						];
					}

					$project->team = maybe_unserialize($project->team) ? maybe_unserialize($project->team) : array();
					if (apply_filters('zpm_should_show_project', true, $project)) {
						$results[] = $project;
						$j++;
					}
				}
			}
			if (apply_filters('zpm_should_show_project', true, $project)) {
				$i++;
			}
		}

		return $results;
	}

	public static function filter_by_category($projects, $cat) {
		$filtered = array();

		foreach ($projects as $project) {
			$cats = (array) maybe_unserialize($project->categories);
			if (in_array($cat, $cats)) {
				$filtered[] = $project;
			}
		}

		return $filtered;
	}

	public static function get_member_projects($user_id) {
		$results = [];
		$projects = Projects::get_projects();

		foreach ($projects as $project) {
			if (Projects::is_project_member($project, $user_id)) {
				$results[] = $project;
			}
		}

		return $results;
	}

	public static function getAssignees($project, $idsOnly = false) {
		if (!is_object($project)) return [];

		if (is_null($project->assignees)) $project->assignees = '';

		$assignees = explode(',', $project->assignees);
		$results = [];

		foreach ($assignees as $assignee) {
			if ($idsOnly) {
				$results[] = $assignee;
			} else {
				$results[] = Members::get_member($assignee);
			}
		}

		return $results;
	}

	public static function isAssignee($project) {
		$assignees = explode(',', $project->assignees);
		return in_array(get_current_user_id(), $assignees);
	}

	public static function get_members($project_id) {
		$project = Projects::get_project($project_id);
		$project_members = is_object($project) && maybe_unserialize($project->team) ? maybe_unserialize($project->team) : array();
		return (array) $project_members;
	}

	public static function get_complete_projects() {
		global $wpdb;
		$table_name = ZPM_PROJECTS_TABLE;
		$query = "SELECT * FROM $table_name WHERE completed = '1' ORDER BY id DESC";
		$projects = $wpdb->get_results($query);

		foreach ($projects as $project) {
			$project->status = $project->status == "" ? maybe_unserialize($project->status) : array();

			if (is_string($project->status)) {
				$project->status = [
					'color' => $project->status
				];
			}

			$project->team = maybe_unserialize($project->team) ? maybe_unserialize($project->team) : array();
		}

		return $projects;
	}

	public static function get_incomplete_projects() {
		global $wpdb;
		$table_name = ZPM_PROJECTS_TABLE;
		$query = "SELECT * FROM $table_name WHERE completed = '0' ORDER BY id DESC";
		$projects = $wpdb->get_results($query);

		foreach ($projects as $project) {
			$project = Projects::format($project);
			$project->team = maybe_unserialize($project->team) ? maybe_unserialize($project->team) : array();
		}

		return $projects;
	}

	public static function format($project) {
		$project->status = !empty($project->status) ? maybe_unserialize($project->status) : [
			'status' => __('None', 'zephyr-project-manager'),
			'color' => 'priority_none'
		];

		if (is_string($project->status)) {
			$project->status = [
				'color' => $project->status
			];
		}

		return $project;
	}

	public static function get_project($project_id) {
		global $wpdb;
		$table_name = ZPM_PROJECTS_TABLE;
		$project = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $project_id));

		if (is_object($project)) {
			$project = Projects::format($project);
			$project->team = maybe_unserialize($project->team) ? maybe_unserialize($project->team) : array();
			$project = apply_filters('zpm_filter_project', $project);

			if (is_null($project->assignees)) $project->assignees = '';
		}

		return $project;
	}

	public static function getProjectByName($name) {
		$projects = Projects::get_projects();

		if (empty(trim($name))) return null;

		foreach ($projects as $project) {
			if (strpos(strtolower($project->name), strtolower($name)) !== false) {
				return $project;
			}
		}

		return null;
	}

	public static function delete_project($id, $archiveTasks = false, $deleteTasks = true) {
		global $wpdb;
		$table_name = ZPM_PROJECTS_TABLE;
		$tasks_table = ZPM_TASKS_TABLE;
		$project_name = Projects::get_project($id);
		$project_name = $project_name->name;
		$settings = array('id' => $id);
		$wpdb->delete($table_name, $settings, ['%d']);
		$tasks = Tasks::get_project_tasks($id);

		if ($archiveTasks) {
			foreach ($tasks as $task) {
				Tasks::update($task->id, [
					'archived' => '1'
				]);
			}
		}

		if ($deleteTasks) {
			foreach ($tasks as $task) {
				$settings = array(
					'id' => $task->id
				);
				$wpdb->delete($tasks_table, $settings, ['%d']);
			}
		}

		$date_deleted = date('Y-m-d H:i:s');
		$subject_name = $project_name;
		do_action('zpm_project_deleted', $id);
		Activity::log_activity(get_current_user_id(), $id, '', $subject_name, 'project', 'project_deleted');
	}

	public static function update($id, $args) {
		global $wpdb;
		$table_name = ZPM_PROJECTS_TABLE;
		$where = [
			'id' => $id
		];
		$wpdb->update($table_name, $args, $where);
	}

	public static function project_count() {
		$manager = ZephyrProjectManager();
		$projects = $manager::get_projects();
		return sizeof($projects);
	}

	public static function getUserProjectCount() {
		$manager = ZephyrProjectManager();
		$projects = $manager::get_projects();
		$userID = get_current_user_id();
		$count = 0;

		foreach ($projects as $key => $project) {
			if (Projects::isAssignee($project, $userID) || Projects::isTeamMember($project, $userID)) {
				$count++;
			}
		}

		return $count;
	}

	public static function get_total() {
		global $wpdb;
		$table_name = ZPM_PROJECTS_TABLE;
		$query = "SELECT COUNT(*) FROM $table_name";
		$total = $wpdb->get_var($query);
	}

	public static function completed_project_count() {
		$results = [];
		$manager = ZephyrProjectManager();
		$projects = $manager::get_projects();

		foreach ($projects as $project) {
			if (Projects::isCompleted($project)) {
				$results[] = $project;
			}
		}
		return sizeof($results);
	}

	public static function isCompleted($project) {
		$status = $project->status;

		if ($status == 'completed') return true;

		if (is_array($status) && isset($status['color'])) {
			$status = $status['color'];
		}

		if ($project->completed == '1' || $status == 'completed') {
			return true;
		}

		return false;
	}

	public static function percent_complete($project_id) {
		$total_tasks = Tasks::get_project_task_count($project_id);
		$completed_tasks = Tasks::get_project_completed_tasks($project_id);
		$percent_complete = ($total_tasks !== 0) ? floor($completed_tasks / $total_tasks * 100) : 100;
		return $percent_complete;
	}

	public static function get_user_projects($user_id) {
		global $wpdb;
		$table_name = ZPM_PROJECTS_TABLE;
		$projects = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = '%d'", $user_id));
		return $projects;
	}

	public static function project_created_by($project_id) {
		global $wpdb;
		$tableName = ZPM_PROJECTS_TABLE;
		$query = "SELECT user_id, date_created FROM $tableName WHERE id = $project_id";
		$data = $wpdb->get_row($query);
		$user = get_user_by('id', $data->user_id);
		$today = new DateTime(date('Y-m-d H:i:s'));
		$created_on = new DateTime($data->date_created);
		$today->setTimezone(zpm_get_timezone());
		$created_on->setTimezone(zpm_get_timezone());

		if (is_object($user)) {
			$return = ($today->format('Y-m-d') == $created_on->format('Y-m-d')) ? sprintf(__('Created by %s at %s today', 'zephyr-project-manager'), $user->display_name, $created_on->format('H:i')) : sprintf(__('Created by %s on %s at %s', 'zephyr-project-manager'), $user->display_name, $created_on->format('d M'), $created_on->format('H:i'));
		} else {
			$return = ($today->format('Y-m-d') == $created_on->format('Y-m-d')) ? sprintf(__('Created at %s today', 'zephyr-project-manager'), $created_on->format('H:i')) : sprintf(__('Created on %s at %s', 'zephyr-project-manager'), $created_on->format('d M'), $created_on->format('H:i'));
		}

		return $return;
	}

	public static function new_project_cell($project, $args = []) {
		$defaultArgs = [
			'is_dashboard_project' => false
		];
		$args = wp_parse_args($args, $defaultArgs);
		$base_url = esc_url(admin_url('/admin.php?page=zephyr_project_manager_projects'));
		$color = maybe_unserialize($project->other_data);
		$color = isset($color['color']) ? $color['color'] : '#f4f4f4';
		$complete = (Projects::isCompleted($project) ? 'completed disabled' : '');
		$categories = maybe_unserialize($project->categories);
		$team = (array) maybe_unserialize($project->team);
		$assignees = Projects::getAssignees($project, true);
		$team = array_merge($team, $assignees);
		$team = array_unique($team);
		$total_tasks = Projects::get_task_count($project->id);
		$completed_tasks = Projects::get_completed_task_count($project->id);
		$active_tasks = (int) $total_tasks - (int) $completed_tasks;
		$overdueTasks = sizeof(Projects::getOverdueProjectTasks($project->id));
		$general_settings = Utillities::general_settings();
		$due_date = new DateTime($project->date_due);
		$url = $base_url . '&action=edit_project&project=' . $project->id;
		$url = apply_filters('zpm_project_item_url', $url, $project->id);
		$projectStatus = Projects::getStatus($project);
		$status = Utillities::get_status($projectStatus);

		if ($due_date->format('Y') == "-0001") {
			$due_date = '';
		} else {
			//$due_date = $due_date->format($general_settings['date_format']);
			$due_date = $due_date->format('d M Y');
		}

		if (!Projects::has_project_access($project)) return;

		$userId = get_current_user_id();
		$unread = Projects::unreadCommentsCount($project->id);
		ob_start();
		$stats = apply_filters('zpm_project_stats', [], $project); ?>

		<div class="zpm_project_grid_cell">
			<div class="zpm_project_grid_row zpm_project_item <?php esc_attr($project->type); ?>" data-project-id="<?php echo esc_attr($project->id); ?>">
				<a href="<?php echo esc_url($url); ?>" data-project_id="<?php echo esc_attr($project->id); ?>" class="zpm_project_title project_name" data-ripple="rgba(0,0,0,0.2)">
					<span class="zpm_project_grid_name">
						<?php if ($project->archived == 1) : ?>
							<i class="zpm-project-title__icon fa fa-archive"></i>
						<?php endif; ?>
						<?php echo esc_html($project->name); ?>
						<?php if ($general_settings['display_project_id'] == '1') : ?>
							(#<?php echo esc_html(Projects::get_unique_id($project->id)); ?>)
						<?php elseif ($general_settings['display_database_project_id'] == '1') : ?>
							(#<?php echo esc_html($project->id); ?>)
						<?php endif; ?>
						<?php do_action('zpm/project/grid/item/name', $project); ?>
					</span>

					<!-- Project options button and dropwdown -->
					<span class="zpm_project_grid_options">
						<i class="zpm_project_grid_options_icon dashicons dashicons-menu" aria-haspopup="true"></i>
						<div class="zpm_dropdown_menu" aria-hidden="true">
							<ul class="zpm_dropdown_list">
								<?php if (Utillities::canDeleteProject($userId, $project)) : ?>
									<li id="zpm_delete_project"><?php _e('Delete Project', 'zephyr-project-manager'); ?></li>
									<?php if ($project->archived) : ?>
										<li id="zpm-project-action__archive" data-archived="0"><?php _e('Unarchive Project', 'zephyr-project-manager'); ?></li>
									<?php else : ?>
										<li id="zpm-project-action__archive" data-archived="1"><?php _e('Archive Project', 'zephyr-project-manager'); ?></li>
									<?php endif; ?>
								<?php endif; ?>

								<?php if (Utillities::can_create_projects()) : ?>
									<li id="zpm_copy_project"><?php _e('Copy Project', 'zephyr-project-manager'); ?></li>
									<?php endif; ?>
								<li data-print-project-pdf-button="<?php esc_attr_e($project->id); ?>"><?php _e('Download PDF', 'zephyr-project-manager'); ?></li>
								<li id="zpm_export_project" class="zpm_dropdown_subdropdown"><?php _e('Export Project', 'zephyr-project-manager'); ?>
									<div class="zpm_export_dropdown zpm_submenu_item">
										<ul>
											<li id="zpm_export_project_to_csv" class="zpm_project_option_sub"><?php _e('Export to CSV', 'zephyr-project-manager'); ?></li>
											<li id="zpm_export_project_to_json" class="zpm_project_option_sub"><?php _e('Export to JSON', 'zephyr-project-manager'); ?></li>
										</ul>
									</div>
								</li>
								<?php if (!$args['is_dashboard_project']) : ?>
									<li id="zpm_add_project_to_dashboard"><?php _e('Add to Dashboard', 'zephyr-project-manager'); ?></li>
								<?php else : ?>
									<li id="zpm-remove-from-dashboard"><?php _e('Remove from Dashboard', 'zephyr-project-manager'); ?></li>
								<?php endif; ?>
							</ul>
						</div>
					</span>
				</a>

				<div class="zpm_project_body">
					<?php if (Projects::hasStatus($project)): ?>
						<div class="zpm-project-status">
							<span class="zpm-project-status-label" style="background: <?php esc_attr_e($status['color']); ?>"><?php esc_html_e($status['name']); ?></span>
						</div>
					<?php endif; ?>

					<span class="zpm_project_description project_description"><?php echo wp_kses_post($project->description); ?></span>
					<div id="zpm_project_progress">
						<span class="zpm_project_stat">
							<p class="zpm_stat_number"><?php echo esc_html($completed_tasks); ?></p>
							<p><?php _e('Completed Tasks', 'zephyr-project-manager'); ?></p>
						</span>
						<span class="zpm_project_stat">
							<p class="zpm_stat_number"><?php echo esc_html($active_tasks); ?></p>
							<p><?php _e('Active Tasks', 'zephyr-project-manager'); ?></p>
						</span>
						<span class="zpm_project_stat">
							<p class="zpm_stat_number"><?php echo esc_html($overdueTasks); ?></p>
							<p><?php _e('Overdue Tasks', 'zephyr-project-manager'); ?></p>
						</span>
						<?php foreach ($stats as $stat) : ?>
							<span class="zpm_project_stat">
								<p class="zpm_stat_number"><?php echo esc_html($stat['value']); ?></p>
								<p class="zpm-stat-label"><?php echo esc_html($stat['label']); ?></p>
							</span>
						<?php endforeach; ?>
					</div>

					<div class="zpm_project_progress_bar_background">
						<div class="zpm_project_progress_bar" data-total_tasks="<?php echo esc_attr($total_tasks); ?>" data-completed_tasks="<?php echo esc_attr($completed_tasks); ?>"></div>
					</div>

					<span class="zpm-project-grid__date"><?php echo esc_html($due_date); ?></span>

					<?php
					$i = 0;
					if (sizeof((array) $team) !== 0) : ?>
						<div class="zpm_project_grid_member">
							<div class="zpm_project_avatar">
								<?php
								foreach ((array) $team as $member) :
									$member = BaseController::get_project_manager_user($member);
									if (!isset($member['name'])) : ?>
										<p class="zpm_friendly_notice"><?php _e('There are no members assigned to this project.', 'zephyr-project-manager'); ?></p>
										<?php continue; ?>
									<?php endif; ?>

									<span class="zpm_avatar_container">
										<span class="zpm_avatar_background"></span>
										<span class="zpm_avatar_image" title="<?php echo esc_attr($member['name']); ?>" style="background-image: url(<?php echo esc_url($member['avatar']); ?>);">
										</span>
									</span>

								<?php
									$i++;
								endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div> <?php
		$content = ob_get_clean();
		$html = apply_filters('zpm_project_cell_html', $content, $project);
		return $html;
	}

	public static function has_project_access($project) {
		if (!is_object($project)) return false;

		$userId = get_current_user_id();

		if (current_user_can('zpm_all_zephyr_capabilities')) {
			return true;
		}

		if (current_user_can('zpm_view_assigned_projects')) {
			if (Projects::isTeamMember($project, $userId) || Projects::isAssignee($project)) {
				return true;
			} else {
				return false;
			}
		}

		if (current_user_can('administrator')) {
			return true;
		}

		if (!current_user_can('zpm_view_projects')) {
			return false;
		} else {
			return true;
		}

		return true;
	}

	public static function is_project_member($project, $user_id) {
		$project = is_object($project) ? $project : Projects::get_project((int) $project);

		if (!is_object($project)) return;

		$project_members = property_exists($project, 'team') && maybe_unserialize($project->team) ? maybe_unserialize($project->team) : array();

		if (in_array((int) $user_id, (array) $project_members) || (int) $user_id == (int) $project->user_id) {
			return true;
		}

		return false;
	}

	public static function isTeamMember($project, $userID) {
		$project = is_object($project) ? $project : Projects::get_project((int) $project);

		if (!is_object($project)) return;

		$projectMembers = property_exists($project, 'team') && maybe_unserialize($project->team) ? maybe_unserialize($project->team) : [];

		if (in_array((int) $userID, (array) $projectMembers)) return true;

		return false;
	}

	public static function getUserProjects($userId) {
		$results = [];
		$projects = Projects::get_projects();

		foreach ($projects as $project) {
			if (Projects::is_project_member($project, $userId)) {
				$results[] = $project;
			}
		}

		return $results;
	}

	public static function frontend_project_item($project, $theme = 'default') {
		ob_start();

		$general_settings = Utillities::general_settings();
		$manager = ZephyrProjectManager();

		$start_date = new DateTime($project->date_start);
		$due_date = new DateTime($project->date_due);
		$date_created = new DateTime($project->date_created);
		$start_date = $start_date->format('Y') !== "-0001" ? date_i18n($general_settings['date_format'], strtotime($project->date_start)) : __('None', 'zephyr-project-manager');
		$due_date = $due_date->format('Y') !== "-0001" ? date_i18n($general_settings['date_format'], strtotime($project->date_due)) : __('None', 'zephyr-project-manager');
		$date_created = $date_created->format('Y') !== "-0001" ? date_i18n($general_settings['date_format'], strtotime($project->date_created)) : __('None', 'zephyr-project-manager');
		$categories = maybe_unserialize($project->categories);
		$category = isset($categories[0]) ? $manager::get_category($categories[0]) : '-1';

		$priority = property_exists($project, 'priority') ? $project->priority : 'priority_none';
		$priority_label = Utillities::get_priority_label($priority); ?>
			<li class="zpm-project-item col-md-12" data-project-id="<?php echo esc_attr($project->id); ?>">
				<a class="zpm-project-item__link" href="?action=project&id=<?php echo esc_attr($project->id); ?>"></a>
				<a class="zpm-project-item-title" href="?action=project&id=<?php echo esc_attr($project->id); ?>"><?php echo stripslashes(esc_html($project->name)); ?>
					<?php if ($general_settings['display_project_id'] == '1') : ?>
						(#<?php echo esc_html(Projects::get_unique_id($project->id)); ?>)
					<?php elseif ($general_settings['display_database_project_id'] == '1') : ?>
						(#<?php echo esc_html($project->id); ?>)
					<?php endif; ?>
				</a>
				<span class="zpm-project-list-details"><?php echo esc_html($date_created); ?>
					<span class="zpm-project-item__due_date">
						<?php _e('Due Date', 'zephyr-project-manager'); ?>: <?php echo esc_html($due_date); ?>
					</span>
				</span>

				<?php if ($priority !== "priority_none" && $priority_label !== "") : ?>
					<span class="zpm-task-priority-bubble <?php echo esc_attr($priority); ?>"><?php echo esc_html($priority_label); ?></span>
				<?php endif; ?>
				<?php if ($theme == 'ultimate') : ?>
					<?php if (is_null($category)) : ?>
						<span class="project-edge"></span>
					<?php else : ?>
						<span class="project-edge" style="background: <?php echo esc_attr($category->color); ?>"></span>
					<?php endif; ?>
				<?php endif; ?>

				<p class="zpm-project-list-description"><?php echo wp_kses_post(stripslashes($project->description)); ?>
					<?php if ($project->description == "") : ?>
				<p class="zpm-error-subtle"><?php _e('No description', 'zephyr-project-manager'); ?></p>
			<?php endif; ?>
			</p>

			<div class="zpm-project-item__footer">
				<?php if (is_array($categories)) : ?>
					<?php foreach ($categories as $category) : ?>
						<?php $category = $manager::get_category($category); ?>
						<?php if (!is_null($category)) : ?>
							<span class="zpm-project-footer__category" style="background-color: <?php echo esc_attr($category->color); ?>"><?php echo esc_html($category->name); ?></span>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			</li>
		<?php return ob_get_clean();
	}

	public static function project_select($id = null, $default = null) {
		//$manager = ZephyrProjectManager::get_instance();
		//$projects = $manager::get_projects();
		$projects = Projects::get_available_projects();
		$html = !is_null($id) ? '<select id="' . $id . '" class="zpm_input">' : '<select class="zpm_input">';
		$html .= '<option value="-1">' . __('None', 'zephyr-project-manager') . '</option>';

		foreach ($projects as $project) {
			if (!is_object($project)) {
				continue;
			}
			if (!is_null($default) && $default == $project->id) {
				$html .= '<option value="' . $project->id . '" selected>' . esc_html($project->name) . '</option>';
			} else {
				$html .= '<option value="' . $project->id . '">' . esc_html($project->name) . '</option>';
			}
		}
		$html .= '</select>';

		if (empty($projects)) {
			$html = '<p class="zpm_error">' . __('There are no projects yet.', 'zephyr-project-manager') . '</p>';
		}

		echo $html;
	}

	public static function update_project_status($id, $status, $color) {
		global $wpdb;

		$table_name = ZPM_PROJECTS_TABLE;

		$data = array(
			'status' => $status,
			'color' => $color
		);

		$settings = array(
			'status' => serialize($data)
		);

		$where = array(
			'id' => $id
		);

		return $wpdb->update($table_name, $settings, $where);
	}

	public static function update_members($id, $members) {
		global $wpdb;
		$table_name = ZPM_PROJECTS_TABLE;
		$members = array_unique($members);
		$settings = [
			'team' => serialize($members)
		];
		$where = [
			'id' => $id
		];
		$wpdb->update($table_name, $settings, $where);
	}

	public static function mark_complete($id, $complete) {
		global $wpdb;
		$table_name = ZPM_PROJECTS_TABLE;

		$settings = array(
			'completed' => $complete
		);

		$where = array(
			'id' => $id
		);

		$wpdb->update($table_name, $settings, $where);
	}

	public static function getOverdueProjectTasks($projectId) {
		$args = array(
			'project_id' => $projectId
		);
		$overdueTasks = Tasks::get_overdue_tasks($args);
		return $overdueTasks;
	}

	public static function update_progress($id = null) {
		$chart_data = array();
		$current_chart_data = get_option('zpm_chart_data');

		if ($id) {
			if ($id == '' || $id == '-1') {
				return;
			}

			$project = Projects::get_project($id);

			if (!is_object($project)) return;

			$data = isset($current_chart_data[$project->id]) ? $current_chart_data[$project->id] : array();

			$task_count = Tasks::get_project_task_count($project->id);
			$completed_tasks = Tasks::get_project_completed_tasks($project->id);
			$pending_tasks = $task_count - $completed_tasks;
			$args = array('project_id' => $project->id);
			$overdue_tasks = sizeof(Tasks::get_overdue_tasks($args));

			$project_data = array(
				'project'			=> $project->id,
				'tasks' 			=> $task_count,
				'completed_tasks' 	=> $completed_tasks,
				'pending_tasks' 	=> $pending_tasks,
				'overdue_tasks' 	=> $overdue_tasks,
				'date'				=> date('d M')
			);

			$added = false;

			foreach ($data as $key => $value) {
				if (!$added) {
					if (isset($data[$key])) {
						if ($data[$key]['date'] == $project_data['date']) {
							$data[$key] = $project_data;
							$added = true;
						}
					}
				}
			}

			if (!$added) {
				array_push($data, $project_data);
			}

			$chart_data[$project->id] = $data;
		} else {
			$all_projects = Projects::get_projects();
			foreach ($all_projects as $project) {
				$data = isset($current_chart_data[$project->id]) ? $current_chart_data[$project->id] : array();

				$task_count = Tasks::get_project_task_count($project->id);
				$completed_tasks = Tasks::get_project_completed_tasks($project->id);
				$pending_tasks = $task_count - $completed_tasks;
				$args = array('project_id' => $project->id);
				$overdue_tasks = sizeof(Tasks::get_overdue_tasks($args));
				$project_data = array(
					'project'			=> $project->id,
					'tasks' 			=> $task_count,
					'completed_tasks' 	=> $completed_tasks,
					'pending_tasks' 	=> $pending_tasks,
					'overdue_tasks' 	=> $overdue_tasks,
					'date'				=> date('d M')
				);

				$added = false;

				foreach ($data as $key => $value) {
					if (!$added) {
						if (isset($data[$key]) && $data[$key]['date'] == $project_data['date']) {
							$data[$key] = $project_data;
							$added = true;
						}
					}
				}

				if (!$added) {
					array_push($data, $project_data);
				}


				$chart_data[$project->id] = $data;
			}
		}

		update_option('zpm_chart_data', $chart_data);
	}

	public static function get_comments($project_id) {
		global $wpdb;
		$table_name = ZPM_MESSAGES_TABLE;
		$tasks = $wpdb->get_results($wpdb->prepare("SELECT id, parent_id, user_id, subject, subject_id, message, type, date_created FROM $table_name WHERE subject = 'project' AND subject_id = '%d' ORDER BY date_created DESC", $project_id));
		return $tasks;
	}

	public static function get_comment($comment_id) {
		global $wpdb;
		$table_name = ZPM_MESSAGES_TABLE;
		$comment = $wpdb->get_row($wpdb->prepare("SELECT id, parent_id, user_id, subject_id, subject, message, type, date_created FROM $table_name WHERE subject = 'project' AND id = '%d'", $comment_id));
		return $comment;
	}

	public static function get_comment_attachments($comment_id) {
		global $wpdb;
		$table_name = ZPM_MESSAGES_TABLE;
		$attachments = $wpdb->get_results($wpdb->prepare("SELECT id, parent_id, user_id, subject, subject_id, message, type, date_created FROM $table_name WHERE subject = 'project' AND parent_id = '%d' ORDER BY date_created DESC", $comment_id));
		return $attachments;
	}

	public static function get_attachments($project_id = null) {
		global $wpdb;
		$table_name = ZPM_MESSAGES_TABLE;
		$query = "SELECT id, parent_id, user_id, subject_id, subject, message, type, date_created FROM $table_name WHERE subject = 'project'";
		$prepare = [];

		if (!is_null($project_id)) {
			$query .= " AND subject_id = '%d'";
			$prepare[] = $project_id;
		}

		$attachments = $wpdb->get_results(!empty($prepare) ? $wpdb->prepare($query, $prepare) : $query);
		$attachments_array = [];

		foreach ($attachments as $attachment) {
			if (unserialize($attachment->type) == 'attachment') {
				$attachments_array[] = array(
					'id' 	  => $attachment->id,
					'user_id' => $attachment->user_id,
					'subject' => $attachment->subject,
					'subject_id' => $attachment->subject_id,
					'message' => unserialize($attachment->message),
					'date_created' => $attachment->date_created,
					'html' => Utillities::attachment_html($attachment)
				);
			}
		}

		return $attachments_array;
	}

	public static function new_comment($comment) {
		$current_user = wp_get_current_user();

		$this_user = BaseController::get_project_manager_user($comment->user_id);
		$datetime1 = zpm_get_datetime(date('Y-m-d H:i:s'));
		$datetime2 = zpm_get_datetime($comment->date_created);

		if ($datetime1->format('m-d') == $datetime2->format('m-d')) {
			// Was sent today
			$time_sent = $datetime2->format('H:i');
		} else {
			// Was sent earlier than today
			$time_sent = $datetime2->format('H:i m/d');
		}

		$timediff = human_time_diff(date_timestamp_get($datetime1), date_timestamp_get($datetime2));
		$comment_attachments = Projects::get_comment_attachments($comment->id);

		$new_comment = '';
		$is_mine = $comment->user_id == get_current_user_id() ? true : false;
		$custom_classes = $is_mine ? 'zpm-my-message' : '';

		if (unserialize($comment->type) !== 'attachment') {

			$new_comment .= '<div data-zpm-comment-id="' . $comment->id . '" class="zpm_comment ' . $custom_classes . '">
		<div class="zpm-comment-bubble">
		<span class="zpm_comment_user_image">
			<span class="zpm_comment_user_avatar" style="background-image: url(' . $this_user['avatar'] . ')"></span>
		</span>';

			if ($comment->user_id == $current_user->ID || current_user_can('zpm_delete_other_comments')) {
				$new_comment .= '<span class="zpm_delete_comment lnr lnr-trash"></span>';
			}

			$new_comment .= '<span class="zpm_comment_user_text">
		<span class="zpm_comment_from">' . $this_user['name'] . '</span>
		<span class="zpm_comment_time_diff">' . $time_sent . '</span>
		<p class="zpm_comment_content">' . stripslashes_deep(unserialize($comment->message)) . '</p>';

			if (!empty($comment_attachments)) {
				$new_comment .= '<ul class="zpm_comment_attachments"><p>Attachments:</p>';

				foreach ($comment_attachments as $attachment) {
					$id = $attachment->id;
					$attachment_id = unserialize($attachment->message);
					$attachment = wp_get_attachment_url($attachment_id);
					if (wp_attachment_is_image($attachment_id)) {
						// Image preview
						$new_comment .= '<li class="zpm_comment_attachment" data-attachment="' . $id . '"><a class="zpm_link" href="' . $attachment . '" download><img class="zpm-image-attachment-preview" src="' . $attachment . '"></a></li>';
					} else {
						// Attachment link
						$new_comment .= '<li class="zpm_comment_attachment" data-attachment="' . $id . '"><a class="zpm_link" href="' . $attachment . '" download>' . $attachment . '</a></li>';
					}
				}
				$new_comment .= '</ul>';
			}
			$new_comment .= '</span></div></div>';
		}
		return $new_comment;
	}

	public static function unreadCommentsCount($projectId) {
		global $zpmMessages;
		$unread = 0;
		$comments = Projects::get_comments($projectId);
		foreach ($comments as $comment) {
			if (!$zpmMessages->isRead($comment->id)) {
				$unread++;
			}
		}
		return $unread;
	}

	public static function file_html($attachment_id, $comment_id) {
		$attachment = BaseController::get_attachment($comment_id);
		$project_id = $attachment->subject_id;
		$attachment_datetime = new DateTime();
		$attachment_date = $attachment_datetime->format('d M Y H:i');
		$attachment_url = wp_get_attachment_url($attachment_id);
		$attachment_type = wp_check_filetype($attachment_url)['ext'];
		$attachment_name = basename($attachment_url);

		if (!is_numeric($attachment_id) || empty($attachment_url)) {
			$attachment_url = $attachment_id;
			$attachment_name = basename($attachment_url);
		}

		$attachmentType = wp_check_filetype($attachment_url)['ext'];
		$isExternal = Utillities::getFileMeta($attachment_id, 'isExternal');
		$isImage = (in_array(strtolower($attachmentType), ['png', 'jpg', 'jpeg', 'gif', 'webm', 'svg'])) || zpm_is_image($attachment_url);
		$filename = Utillities::getFileMeta($attachment_id, 'filename');

		if (!empty($filename)) {
			$attachment_name = $filename;
		}

		ob_start(); ?>
			<div class="zpm_file_item_container" data-project-id="<?php echo esc_attr($project_id); ?>">
				<div class="zpm_file_item" data-attachment-id="<?php echo esc_attr($attachment_id); ?>" data-attachment-url="<?php echo esc_attr($attachment_url); ?>" data-attachment-name="<?php echo esc_attr($attachment_name); ?>" data-task-name="None" data-attachment-date="<?php echo esc_attr($attachment_date); ?>">
					<?php if ($isImage) : ?>
						<!-- If attachment is an image -->
						<div class="zpm_file_preview" data-zpm-action="show_info">
							<span class="zpm_file_image" style="background-image: url(<?php echo esc_url($attachment_url); ?>);"></span>
						</div>
					<?php else : ?>
						<div class="zpm_file_preview" data-zpm-action="show_info">
							<div class="zpm_file_type"><?php echo '.' . esc_html($attachment_type); ?></div>
						</div>
					<?php endif; ?>

					<h4 class="zpm_file_name">
						<?php echo esc_html($attachment_name); ?>
						<span class="zpm_file_actions zpm-colors__background-primary">
							<span class="zpm_file_action lnr lnr-download" data-zpm-action="download_file"></span>
							<span class="zpm_file_action lnr lnr-question-circle" data-zpm-action="show_info"></span>
							<span class="zpm_file_action lnr lnr-trash" data-zpm-action="remove_file"></span>
						</span>
					</h4>
				</div>
			</div>
		<?php return ob_get_clean();
	}

	public static function project_modal() {
		$manager = ZephyrProjectManager();
		$statuses = Utillities::get_statuses();
		$users = Members::get_zephyr_members();
		$categories = $manager::get_categories(); ?>
			<div id="zpm_project_modal" class="zpm-modal" role="form" aria-label="<?php esc_attr_e('New Project', 'zephyr-project-manager') ?>" aria-modal="true" aria-hidden="true">
				<div class="zpm_modal_body">
					<h3><?php _e('Create a new project', 'zephyr-project-manager'); ?></h3><span class="zpm_close_modal lnr lnr-cross"></span>

					<div class="zpm-new-project__field">
						<label class="zpm_label" for="zpm-new-project--name"><?php _zpm_e('Project Name'); ?></label>
						<input class="zpm_project_name_input zpm_input" id="zpm-new-project--name" name="zpm_project_name" placeholder="<?php _zpm_e('Add a project name'); ?>" />
					</div>

					<div class="zpm-new-project__field">
						<label class="zpm_label" for="zpm-new-project--description"><?php _zpm_e('Project Description'); ?></label>
						<textarea id="zpm-new-project-description" id="zpm-new-project--description" class="zpm_input" placeholder="<?php _zpm_e('Project Description'); ?>"></textarea>
					</div>

					<div class="zpm-new-project__field">
						<label class="zpm_label" for="zpm-new-project__managers"><?php _e('Project Managers', 'zephyr-project-manager'); ?></label>
						<select id="zpm-new-project__managers" class="zpm_input zpm-input-chosen" multiple data-placeholder="<?php _e('Select Project Managers', 'zephyr-project-manager'); ?>">
							<?php foreach ($users as $user) : ?>
								<option value="<?php echo esc_attr($user['id']); ?>"><?php echo esc_html($user['name']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="zpm-new-project__field">
						<label class="zpm_label" for="zpm-new-task__status"><?php _e('Categories', 'zephyr-project-manager'); ?></label>
						<select id="zpm-new-project__categories" class="zpm_input zpm-input-chosen" multiple data-placeholder="<?php _e('Select Categories', 'zephyr-project-manager'); ?>">
							<?php foreach ($categories as $category) : ?>
								<option value="<?php echo esc_attr($category->id); ?>"><?php echo esc_html($category->name); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div id="zpm-new-project__fields">
						<?php echo apply_filters('zpm_new_project_fields', ''); ?>
					</div>

					<div class="zpm_modal_content">
						<div class="zpm_col_container <?php echo zpmIsPro() ? 'zpm-project-type-picker' : ''; ?>">
							<?php ob_start(); ?>
							<div class="zpm_modal_item">
								<div class="image zpm_project_selected" data-project-type="list">
									<img class="zpm_selected_image" src="<?php echo esc_url(ZPM_PLUGIN_URL . '/assets/img/project_list_selected.png'); ?>" alt="<?php esc_attr_e('Click to change to the list view', 'zephyr-project-manager'); ?>" />
									<img src="<?php echo esc_url(ZPM_PLUGIN_URL . "/assets/img/project_list.png"); ?>" />
								</div>
								<h4 class="title"><?php _e('List', 'zephyr-project-manager'); ?></h4>
								<p class="description"><?php _e('Organize your work in an itemized list.', 'zephyr-project-manager'); ?></p>
							</div>

							<?php
							$project_types = ob_get_clean();
							echo apply_filters('zpm_project_types', $project_types);
							?>
						</div>
					</div>

					<input id="zpm-project-type" type="hidden" value="list">

					<div class="zpm_modal_buttons">
						<input type="hidden" id="zpm-new-project-priority-value" class="zpm-priority-value" value="priority_none" />
						<button id="zpm-new-project-priority" class="zpm_button zpm-priority-selection" zpm-toggle-dropdown="zpm-new-project-priority-dropdown" data-priority="priority_none"><span class="zpm-priority-name"><?php _e('Priority', 'zephyr-project-manager'); ?>: <?php _e('None', 'zephyr-project-manager'); ?></span>
							<div id="zpm-new-project-priority-dropdown" class="zpm-dropdown zpm-priority-dropdown">


								<div class="zpm-dropdown-item zpm-new-project-priority" data-value="priority_none" data-color="#f9f9f9"><span class="zpm-priority-indicator zpm-color-none"></span><?php _e('None', 'zephyr-project-manager'); ?></div>

								<?php foreach ($statuses as $slug => $status) : ?>
									<div class="zpm-dropdown-item zpm-new-project-priority" data-value="<?php echo esc_attr($slug); ?>" data-color="<?php echo esc_attr($status['color']); ?>">

										<span class="zpm-priority-indicator <?php echo esc_attr($slug); ?>" style="background-color: <?php echo esc_html($status['color']); ?>"></span>
										<span class="zpm-priority-picker__name"><?php echo esc_html($status['name']); ?></span>
									</div>
								<?php endforeach; ?>

							</div>
						</button>
						<button id="zpm_modal_add_project" class="zpm_button"><?php _e('Create Project', 'zephyr-project-manager'); ?></button>

						<?php if (!BaseController::is_pro()) : ?>
							<p class="zpm-pro-upselling"><?php _e('Create Kanban-style board projects with the ', 'zephyr-project-manager'); ?> <a class="zpm-pro-link" href="https://zephyr-one.com/purchase-pro" target="_blank"><?php _e('Pro Version', 'zephyr-project-manager'); ?></a>.</p>
						<?php endif; ?>
					</div>
				</div>
			</div <?php
	}

	public static function copy_project($args = null) {
		global $wpdb;
		$table_name = ZPM_PROJECTS_TABLE;
		$defaults = [
			'project_id' => -1,
			'project_name' => false,
			'copy_options' => array()
		];
		$args = wp_parse_args($args, $defaults);
		$project = Projects::get_project($args['project_id']);
		$description = in_array('description', $args['copy_options']) ? $project->description : '';
		$date = date('Y-m-d H:i:s');
		$date_start = in_array('start_date', $args['copy_options']) ? $project->date_start : $date;
		$date_due = in_array('due_date', $args['copy_options']) ? $project->date_due : '';
		$settings = array(
			'user_id' 	  	 => wp_get_current_user()->ID,
			'name' 		  	 => $args['project_name'],
			'description' 	 => $description,
			'completed'   	 => Projects::isCompleted($project),
			'categories'	 => $project->categories,
			'date_start'  	 => $date_start,
			'date_due' 	  	 => $date_due,
			'date_created' 	 => $date,
			'other_data'	 => $project->other_data,
			'date_completed' => '',
			'priority'		 => $project->priority,
			'type'			 => $project->type
		);
		$wpdb->insert($table_name, $settings);
		$last_id = $wpdb->insert_id;
		$last_project = Projects::get_project($last_id);
		$tasks = Tasks::get_project_tasks($args['project_id']);
		$task_table = ZPM_TASKS_TABLE;
		$i = $j = 0;

		if ((in_array('tasks', $args['copy_options']))) {
			foreach ($tasks as $task) {
				$settings = (array) $task;
				unset($settings['id']);
				$settings['project'] = $last_id;

				// $settings['status'] = 'not_started';
				// if (property_exists($task, 'kanban_col')) {
				// 	$settings['kanban_col'] = $task->kanban_col;
				// }

				$wpdb->insert($task_table, $settings);
				$last_task_id = $wpdb->insert_id;

				if (in_array('blockingTasks', $args['copy_options'])) {
					Tasks::updateBlockingTasks($last_task_id, Tasks::getBlockingTasks($task->id));
				}

				$i++;

				if ($settings['completed']) {
					$j++;
				}

				$subtasks = Tasks::get_subtasks($task->id);

				foreach ($subtasks as $subtask) {
					$wpdb->insert($task_table, [
						'parent_id'		 => $last_task_id,
						'user_id' 		 => $subtask->user_id,
						'assignee' 		 => $subtask->assignee,
						'project' 		 => $last_id,
						'name' 			 => $subtask->name,
						'completed' 	 => $subtask->completed,
						'date_start' 	 => $subtask->date_start,
						'date_due' 		 => $subtask->date_due,
						'date_created' 	 => $subtask->date_created,
						'date_completed' => '',
						'status' => 'not_started'
					]);
					$subtasks = Tasks::get_subtasks($subtask->id);

					foreach ($subtasks as $subtask) {
						$wpdb->insert($task_table, [
							'parent_id'		 => $last_task_id,
							'user_id' 		 => $subtask->user_id,
							'assignee' 		 => $subtask->assignee,
							'project' 		 => $last_id,
							'name' 			 => $subtask->name,
							'completed' 	 => $subtask->completed,
							'date_start' 	 => $subtask->date_start,
							'date_due' 		 => $subtask->date_due,
							'date_created' 	 => $subtask->date_created,
							'date_completed' => '',
							'status' => 'not_started'
						]);
					}
				}
			}
		}

		do_action('zpm_copy_project', $project->id, $last_id);

		$last_project->task_count = $i;
		$last_project->completed_tasks = $j;
		return $last_project;
	}

	public static function add_to_dashboard($project_id) {
		$option = maybe_unserialize(get_option('zpm_dashboard_projects', array()));
		if (!in_array($project_id, $option)) {
			$option[] = $project_id;
		}
		update_option('zpm_dashboard_projects', serialize($option));
	}

	public static function removeFromDashboard($project_id) {
		$option = maybe_unserialize(get_option('zpm_dashboard_projects', array()));
		if (($key = array_search($project_id, $option)) !== false) {
			unset($option[$key]);
		}
		update_option('zpm_dashboard_projects', serialize($option));
	}

	public static function get_dashboard_projects($object = true) {
		$results = [];
		$ids = maybe_unserialize(get_option('zpm_dashboard_projects', array()));
		$projects = apply_filters('zpm_filter_global_projects', Projects::get_projects());

		if (!$object) {
			$results = $ids;
		} else {
			foreach ($ids as $id) {
				foreach ($projects as $project) {

					if ($project->id == $id) {
						$results[] = $project;
					}
				}
			}
		}

		return $results;
	}

	public static function remove_from_dashboard($project_id) {
		$dashboard_projects = Projects::get_dashboard_projects(false);
		if (($project_id = array_search($project_id, $dashboard_projects)) !== false) {
			unset($dashboard_projects[$project_id]);
		}
		update_option('zpm_dashboard_projects', serialize($dashboard_projects));
	}

	public function weekly_updates() {
		$projects = Projects::get_projects();
		//$progress_data = get_option('zpm_chart_data');
		Emails::weekly_updates($projects);
	}

	public function task_notifications() {
		$tasks = Tasks::get_week_tasks();
		Emails::task_notifications($tasks);
	}

	public static function search($query) {
		global $wpdb;
		$table_name = ZPM_PROJECTS_TABLE;
		$result_projects = [];
		$added = [];
		$results = $wpdb->get_results($wpdb->prepare("SELECT id, name FROM `{$table_name}` WHERE name LIKE %s OR id LIKE %s", '%' . $wpdb->esc_like($query) . '%', '%' . $wpdb->esc_like($query) . '%'));

		foreach ($results as $result) {
			$result_projects[] = $result;
			$added[] = $result->id;
		}

		$projects = Projects::get_projects();

		foreach ($projects as $project) {

			if (!empty($project->categories)) {
				$cats = Projects::extract_categories($project);
				foreach ($cats as $cat) {
					if (strpos(strtolower($cat->name), strtolower($query)) > -1) {
						$project->cat_name = $cat->name;
					}
				}
			}
		}

		foreach ($result_projects as $result) {
			if ($result->id == $query) {
				$result->name .= ' (#' . $result->id . ')';
			}
		}

		$result_projects = apply_filters('zpm_project_search_results', $result_projects, $projects, $query);

		return $result_projects;
	}

	public static function send_comment($project_id, $data, $files = null) {
		global $wpdb;
		$table_name = ZPM_MESSAGES_TABLE;
		$date =  date('Y-m-d H:i:s');

		$user_id = isset($data['user_id']) ? sanitize_text_field($data['user_id']) : get_current_user_id();
		$message = isset($data['message']) ? serialize(sanitize_textarea_field($data['message'])) : '';
		$type = isset($data['type']) ? serialize($data['type']) : '';
		$parent_id = isset($data['parent_id']) ? $data['parent_id'] : 0;
		$subject = 'project';

		$settings = array(
			'user_id' => $user_id,
			'subject' => $subject,
			'subject_id' => $project_id,
			'message' => $message,
			'date_created' => $date,
			'type' => $type,
			'parent_id' => $parent_id,
		);

		$wpdb->insert($table_name, $settings);

		// if ($attachments) {
		// 	foreach ($attachments as $attachment) {
		// 		$parent_id = (!$last_comment) ? '' : $last_comment;
		// 		$attachment_type = ($subject == '' && $attachment['attachment_type'] !== '') ? $attachment['attachment_type'] : $subject;
		// 		$subject_id = ($subject_id == '' && $attachment['subject_id'] !== '') ? $attachment['subject_id'] : $subject;
		// 		$settings['user_id'] = $attachment_type;
		// 		$settings['subject'] = $attachment_type;
		// 		$settings['subject_id'] = $subject_id;
		// 		$settings['parent_id'] = $parent_id;
		// 		$settings['type'] = serialize('attachment');
		// 		$settings['message'] = serialize($attachment['attachment_id']);
		// 		$wpdb->insert($table_name, $settings);
		// 	}
		// }
		return $wpdb->insert_id;
	}

	public static function view_project_modal($project_id) {
		include(ZPM_PLUGIN_PATH . '/templates/parts/project-view.php');
	}

	public static function view_project_container($project_id = null) {
		?>
			<div id="zpm_quickview_modal" class="zpm-modal" data-project-id="<?php echo !is_null($project_id) ? esc_attr($project_id) : ''; ?>" aria-modal="true" aria-hidden="true"></div>
		<?php
	}

	public static function get_other_data($project_id) {
		global $wpdb;
		$table_name = ZPM_PROJECTS_TABLE;
		$row = $wpdb->get_row($wpdb->prepare("SELECT other_data FROM $table_name WHERE id = '%d' ORDER BY id DESC", $project_id));
		return (array) maybe_unserialize($row->other_data);
	}

	public static function get_settings($project_id) {
		global $wpdb;
		$table_name = ZPM_PROJECTS_TABLE;
		$row = $wpdb->get_row($wpdb->prepare("SELECT other_settings FROM $table_name WHERE id = '%d'", $project_id));

		if (is_null($row)) return [];

		$settings = maybe_unserialize($row->other_settings);
		$defaults = array(
			'weekly_update_email' 		   => '0',
			'task_completed_email' 		   => '1',
			'new_subtask_email' 		   => '0',
			'task_assignee_comments_email' => '0',
			'task_comments_email' 		   => '0',
			'project_comments_email' 	   => '0',
			'new_task_email' 			   => '1',
			'task_order'				   => [],
			'additional_emails'			   => [],
		);
		$defaults = apply_filters('zpm/project/settings/defaults', $defaults);
		return wp_parse_args($settings, $defaults);
	}

	public static function check_setting($project_id, $setting) {
		$settings = Projects::get_settings($project_id);

		if (!isset($settings[$setting]) || $project_id == '-1') {
			return false;
		}

		if ($settings[$setting] !== '0') {
			return true;
		} else {
			return false;
		}
	}

	public static function update_settings($project_id) {
		global $wpdb;
		$table_name = ZPM_PROJECTS_TABLE;

		if (!isset($_POST['zpm-update-project-settings'])) return;

		$settings = Projects::get_settings($project_id);

		if (isset($_POST['zpm-project-settings__new-task-email'])) {
			$settings['new_task_email'] = '1';
		} else {
			$settings['new_task_email'] = '0';
		}

		if (isset($_POST['zpm-project-settings__task-completed-email'])) {
			$settings['task_completed_email'] = '1';
		} else {
			$settings['task_completed_email'] = '0';
		}

		if (isset($_POST['zpm-project-settings__subtasks-email'])) {
			$settings['new_subtask_email'] = '1';
		} else {
			$settings['new_subtask_email'] = '0';
		}

		if (isset($_POST['zpm-project-settings__weekly-update-email'])) {
			$settings['weekly_update_email'] = '1';
		} else {
			$settings['weekly_update_email'] = '0';
		}

		if (isset($_POST['zpm-project-settings__task-assignee-comments-emails'])) {
			$settings['task_assignee_comments_email'] = '1';
		} else {
			$settings['task_assignee_comments_email'] = '0';
		}

		if (isset($_POST['zpm-project-settings__task-comments-emails'])) {
			$settings['task_comments_email'] = '1';
		} else {
			$settings['task_comments_email'] = '0';
		}

		if (isset($_POST['zpm-project-settings__project-comments-email'])) {
			$settings['project_comments_email'] = '1';
		} else {
			$settings['project_comments_email'] = '0';
		}

		if (isset($_POST['zpm-project-settings__additional-emails'])) {
			$_POST['zpm-project-settings__additional-emails'] = str_replace(' ', '', sanitize_text_field($_POST['zpm-project-settings__additional-emails']));
			$settings['additional_emails'] = explode(',', sanitize_text_field($_POST['zpm-project-settings__additional-emails']));
		}

		$settings = apply_filters('zpm_updated_project_user_settings', $settings, $project_id);
		Utillities::update_user_project_settings(get_current_user_id(), $project_id, $settings);

		$args = array(
			'other_settings' => serialize($settings)
		);

		$where = array(
			'id' => $project_id
		);

		$wpdb->update($table_name, $args, $where);
	}

	public static function updateSetting($projectId, $key, $value) {
		$settings = Projects::get_settings($projectId);
		$settings[$key] = $value;
		$data = [
			'other_settings' => serialize($settings)
		];
		Projects::update($projectId, $data);
	}

	public static function getSetting($projectId, $key) {
		$settings = Projects::get_settings($projectId);

		if (isset($settings[$key])) {
			return $settings[$key];
		} else {
			return null;
		}
	}

	public static function get_unique_id($project_id) {

		$other_data = Projects::get_other_data($project_id);

		if (isset($other_data['unique_id'])) {
			return $other_data['unique_id'];
		} else {
			return Projects::update_unique_id($project_id);
		}
	}

	public static function update_unique_id($project_id) {
		global $wpdb;
		$table_name = ZPM_PROJECTS_TABLE;

		$other_data = Projects::get_other_data($project_id);
		$other_data['unique_id'] = Utillities::generate_random_string(8);

		$args = array(
			'other_data' => serialize($other_data)
		);

		$where = array(
			'id' => $project_id
		);

		$wpdb->update($table_name, $args, $where);
		return $other_data['unique_id'];
	}

	public static function extract_categories($project) {
		$results = array();
		$manager = ZephyrProjectManager();
		if (!is_object($project)) {
			return $results;
		}

		$categories = maybe_unserialize($project->categories) ? (array) maybe_unserialize($project->categories) : array();

		foreach ($categories as $category) {
			if (!empty($category)) {
				$results[] = $manager::get_category($category);
			}
		}
		return $results;
	}

	public static function get_status($project) {
		$defaults = array(
			'status' => __('None', 'zephyr-project-manager'),
			'color' => __('zpm-default-color', 'zephyr-project-manager')
		);
		$status = maybe_unserialize($project->status);
		return (array) wp_parse_args($status, $defaults);
	}

	public static function category_projects($category_id, $all_fields = false) {
		$results = [];
		$manager = ZephyrProjectManager::get_instance();
		$projects = $manager::get_projects();

		foreach ($projects as $project) {
			if (!Projects::has_project_access($project)) continue;

			if (Projects::has_category($project, $category_id) || $category_id == '' || $category_id == '-1') {
				$results[] = $project;
			}
		}

		return $results;
	}

	public static function has_category($project, $category_id) {
		$categories = (array) maybe_unserialize($project->categories);
		if (in_array($category_id, $categories)) {
			return true;
		}
		return false;
	}

	public static function get_available_projects() {
		$results = [];
		$manager = ZephyrProjectManager();
		$projects = $manager::get_projects();

		foreach ($projects as $project) {
			if (Projects::has_project_access($project) && apply_filters('zpm_should_show_project', true, $project)) {
				$results[] = $project;
			}
		}
		//$results = apply_filters( 'zpm_project_grid_projects', $results );
		return $results;
	}

	public static function get_total_pages() {
		$settings = Utillities::general_settings();
		$projects_per_page = $settings['projects_per_page'];
		$projects = Projects::get_available_projects();
		$count = sizeof($projects);
		return ceil($count / $projects_per_page);
	}

	public static function get_task_count($id) {
		global $wpdb;
		$table_name = ZPM_TASKS_TABLE;
		$query = "SELECT COUNT(*) FROM $table_name WHERE project = '$id' AND parent_id = '-1'";
		$count = $wpdb->get_var($query);
		return $count;
	}

	public static function get_completed_task_count($id) {
		global $wpdb;
		$table_name = ZPM_TASKS_TABLE;
		$query = "SELECT COUNT(*) FROM $table_name WHERE project = '$id' AND completed = '1' AND parent_id = '-1'";
		$count = $wpdb->get_var($query);
		return $count;
	}

	public static function get_active_task_count($id) {
		global $wpdb;
		$table_name = ZPM_TASKS_TABLE;
		$query = "SELECT COUNT(*) FROM $table_name WHERE project = '$id' AND completed = '0'";
		$count = $wpdb->get_var($query);
		return $count;
	}

	public function filter_projects($projects) {
		$results = [];
		$category = isset($_GET['category_id']) ? zpm_sanitize_int($_GET['category_id']) : '';

		foreach ($projects as $project) {
			// if ( empty( $category ) || $category == '-1' ) {
			// 	$results[] = $project;
			// 	continue;
			// }
			if (Projects::has_category($project, $category)) {
				$results[] = $project;
			}
		}
		return $results;
	}

	public function should_show_project($show, $project) {
		$category_id = isset($_REQUEST['category_id']) ? zpm_sanitize_int($_REQUEST['category_id']) : '';
		$showComplete = isset($_GET['completed']) ? sanitize_text_field($_GET['completed']) : '';

		if (!empty($showComplete)) {
			if ($showComplete == 'true') {
				if (Projects::isCompleted($project)) {
					return true;
				} else {
					return false;
				}
			}
		}

		if (isset($_GET['user'])) {
			if (Projects::isAssignee($project, zpm_sanitize_int($_GET['user'])) || Projects::isTeamMember($project, zpm_sanitize_int($_GET['user']))) {
			// if (Projects::isAssignee($project, zpm_sanitize_int($_GET['user']))) {
				return true;
			} else {
				return false;
			}
		}

		if (Projects::has_category($project, $category_id) || ($category_id == '-1' || empty($category_id))) {
			return true;
		}
		return false;
	}

	public function filter_project($project) {
		$start_datetime = new DateTime($project->date_start);
		$due_datetime = new DateTime($project->date_start);
		$start_date = ($start_datetime->format('Y-m-d') !== '-0001-11-30' && $project->date_start !== '0000-00-00 00:00:00') ? date_i18n($this->settings['date_format'], strtotime($project->date_start)) : __('Not set', 'zephyr-project-manager');
		$due_date = ($due_datetime->format('Y-m-d') !== '-0001-11-30' && $project->date_due !== '0000-00-00 00:00:00') ? date_i18n($this->settings['date_format'], strtotime($project->date_due)) : __('Not set', 'zephyr-project-manager');
		$priority = Utillities::get_status($project->priority);
		$project->formatted_start_date = $start_date;
		$project->formatted_due_date = $due_date;
		$project->formatted_priority = $priority;
		return $project;
	}

	public static function getTaskOrder($projectId) {
		$order = Projects::getSetting($projectId, 'task_order');
		if (!is_array($order)) $order = [];
		return $order;
	}

	public static function getOrderedTasks($projectId, $tasks) {
		$orderIds = Projects::getTaskOrder($projectId);
		usort($tasks, function ($a, $b) use ($orderIds) {
			$pos_a = array_search($a->id, $orderIds);
			$pos_b = array_search($b->id, $orderIds);
			return $pos_a - $pos_b;
		});
		return $tasks;
	}

	public static function getAdditionalEmails($projectId) {
		$emails = Projects::getSetting($projectId, 'additional_emails');
		if (is_null($emails)) {
			$emails = [];
		}
		return $emails;
	}

	public static function loadFromCSV($file) {
		$projectArray = array();

		if (($handle = fopen($file, "r")) !== FALSE) {
			$row = 0;

			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if ($row > 0) {
					$num = count($data);
					$project = array(
						'id' 			 => $data[0],
						'user_id' 		 => $data[1],
						'name' 			 => $data[2],
						'description' 	 => $data[3],
						'completed' 	 => $data[4],
						'team' 			 => $data[5],
						'categories' 	 => $data[6],
						'date_created' 	 => $data[7],
						'date_due' 		 => $data[8],
						'date_start' 	 => $data[9],
						'date_completed' => $data[10],
						'other_data' 	 => $data[11]
					);
					// $task['date_start'] = date('Y-m-d', $task['date_start']);
					// $task['date_due'] = date('Y-m-d', $task['date_due']);
					// $task['date_completed'] = date('Y-m-d', $task['date_completed']);

					// if (!Tasks::task_exists($data[0])) {
					// 	$wpdb->insert( $table_name, $task );
					// 	$task = Tasks::get_task($wpdb->insert_id);
					// 	if ($row > 1) {
					// 		$html .= Tasks::new_task_row($task);
					// 	}
					// } else {
					// 	$task['already_uploaded'] = true;
					// }

					// $row++;

					$projectArray[] = $project;
				}

				$row++;
			}
			fclose($handle);
		}
		return $projectArray;
	}

	public static function loadFromJSON($file) {
		$json = file_get_contents($file);
		$jsonResult = json_decode($json);

		$projects = array();

		if (!is_array($jsonResult)) {
			$jsonArray[] = $jsonResult;
		} else {
			$jsonArray = $jsonResult;
		}

		foreach ($jsonArray as $project) {
			$project = array(
				'id' 			 => $project->id,
				'parent_id' 	 => $project->parent_id,
				'user_id' 		 => $project->user_id,
				'name' 			 => $project->name,
				'description' 	 => $project->description,
				'categories' 	 => $project->categories,
				'completed' 	 => $project->completed,
				'date_created' 	 => $project->date_created,
				'date_start' 	 => $project->date_start,
				'date_due' 		 => $project->date_due,
				'date_completed' => $project->date_completed,
				'team' 			 => $project->team,
				'custom_fields'  => $project->custom_fields
			);

			$projects[] = $project;
		}
		return $projects;
	}

	public static function getStatus($project) {
		$status = $project->status;

		if (isset($status['color'])) {
			return $status['color'];
		}

		return $status;
	}

	public static function hasStatus($project) {
		$status = Projects::getStatus($project);
		if ($status == 'priority_none' || empty($status) || $status == 'none') return false;
		return true;
	}

	public static function getSortingMethods() {
		$sortingMethods = [
			'date_created' => __('Date Created', 'zephyr-project-manager'),
			'date_due' => __('Date Due', 'zephyr-project-manager'),
			'alphabetical_asc' => __('Alphabetically', 'zephyr-project-manager'),
			'alphabetical_desc' => __('Alphabetically (Descending)', 'zephyr-project-manager'),
			'priority' => __('Priority', 'zephyr-project-manager'),
		];
		return apply_filters('zpm/projects/sorting_methods', $sortingMethods);
	}

	public static function getLastSortingMethod() {
		$method = get_user_meta(get_current_user_id(), 'zpm/projects/last_sorting_method', true);
		if (!$method || empty($method)) $method = 'date_created';
		return apply_filters('zpm/projects/last_sorting_method', $method);
	}

	public static function sort($projects = [], $sortingMethod = 'date_created') {
		switch ($sortingMethod) {
			case 'date_created':
				$projects = Projects::sortByDateCreated($projects);
				break;
			case 'date_due':
				$projects = Projects::sortByDateDue($projects);
				break;
			case 'alphabetical_asc':
				$projects = Projects::sortByName($projects);
				break;
			case 'alphabetical_desc':
				$projects = Projects::sortByName($projects, false);
				break;
			case 'priority':
				$projects = Projects::sortByPriority($projects);
				break;
		}

		return $projects;
	}

	public static function sortByDateCreated($projects = [], $ascending = true) {
		usort($projects, function ($a, $b) {
			return $a->id - $b->id;
		});

		if ($ascending) $projects = array_reverse($projects);

		return $projects;
	}

	public static function sortByDateDue($projects = [], $ascending = true) {
		$sorted = [];

		foreach ($projects as $project) {
			$dateDueTime = strtotime($project->date_due);

			if ($dateDueTime > 0) {
				$sorted["{$dateDueTime}_{$project->id}"] = $project;
			} else {
				$sorted["_{$project->id}"] = $project;
			}
		}

		$projects = $sorted;
		ksort($projects);

		if (!$ascending) $projects = array_reverse($projects);

		return $projects;
	}

	public static function sortByPriority($projects = [], $ascending = true) {
		$sorted = [];
		$map = [
			'critical' => 0,
			'high' => 1,
			'medium' => 2,
			'low' => 3,
			'priority_none' => 4,
		];

		foreach ($projects as $project) {
			$priority = $project->priority;
			$priority = isset($map[$priority]) ? $map[$priority] : 4;
			$sorted["{$priority}_{$project->id}"] = $project;
		}

		$projects = $sorted;
		ksort($projects);

		if (!$ascending) $projects = array_reverse($projects);

		return $projects;
	}

	public static function sortByName($projects = [], $ascending = true) {
		$sorted = [];

		foreach ($projects as $project) {
			$sorted["{$project->name}{$project->id}"] = $project;
		}

		$projects = $sorted;
		ksort($projects);

		if (!$ascending) $projects = array_reverse($projects);

		return $projects;
	}

	public static function getMembers($project) {
		$members = maybe_unserialize($project->team) ? (array) maybe_unserialize($project->team) : [];
		return $members;
	}

	public static function hasMember($project, $memberID) {
		return in_array($memberID, Projects::getMembers($project));
	}

	public static function hasTeam($project, $teamID) {
		$teamID = 'team_' . $teamID;
		return in_array($teamID, Projects::getMembers($project));
	}

	public static function getUrl($projectID, $frontend = false) {
		if ($frontend) {
			$url = Utillities::get_frontend_url('action=project&id=' . $projectID);
		} else {
			$url = admin_url('/admin.php?page=zephyr_project_manager_projects&action=edit_project&project=' . $projectID);
		}

		return $url;
	}

	public static function getMeta($id, $key, $default = false) {
		$meta = get_option("zpm/project/meta/$id/$key", $default);

		if (is_string($meta)) {
			$decoded = json_decode($meta, true);

			if (!is_null($decoded)) {
				$meta = $decoded;
			}
		}

		return $meta;
	}

	public static function updateMeta($id, $key, $value) {
		$meta = update_option("zpm/project/meta/$id/$key", $value);
		return $meta;
	}

	public static function getEstimatedCompletionDate($projectID) {
		$project = Projects::get_project($projectID);

		if (zpm_is_date_valid($project->date_due)) {
			return date('Y-m-d', strtotime($project->date_due));
		}

		$tasks = Tasks::get_project_tasks($projectID);
		$latestDate = null;

		foreach ($tasks as $task) {
			$dueTime = strtotime($task->date_due);

			if (is_null($latestDate) || $latestDate < $dueTime) {
				$latestDate = $dueTime;
			}
		}

		if (is_null($latestDate) || empty($latestDate) || !zpm_is_date_valid($latestDate)) return __('None', 'zephyr-project-manager');

		return date('Y-m-d', $latestDate);
	}

	public static function getProjectStats($projectId) {
		$tasks = Tasks::get_project_tasks($projectId);
		$task_count = count($tasks);
		$completed_tasks = Tasks::get_project_completed_tasks($projectId);
		$args = array(
			'project_id' => $projectId
		);
		$overdue_tasks = sizeof(Tasks::get_overdue_tasks($args));
		$pending_tasks = $task_count - $completed_tasks;
		$percent_complete = ($task_count !== 0) ? floor($completed_tasks / $task_count * 100) : '100';
		$statusTasks = [];

		foreach ($tasks as $task) {
			if ($task->status !== 'priority_none' && !empty($task->status)) {
				if (!isset($statusTasks[$task->status])) {
					$statusTasks[$task->status] = [
						'tasks' => 0,
						'color' => '',
						'name' => ''
					];
				}
				$status = Utillities::get_status($task->status);
				$statusTasks[$task->status]['tasks'] += 1;
				$statusTasks[$task->status]['color'] = $status['color'];
				$statusTasks[$task->status]['name'] = $status['name'];
			}
		}

		$response = array(
			'total' => $task_count,
			'completed' => $completed_tasks,
			'overdue' => $overdue_tasks,
			'pending' => $pending_tasks,
			'percent' => $percent_complete,
			'statuses' => $statusTasks
		);
		return $response;
	}

	public static function getFiles($projectID) {
		$files = [];
		$attachments = Projects::get_attachments($projectID);

		foreach ($attachments as $attachment) {
			$attachmentId = $attachment['message'];
			$attachmentUrl = !wp_http_validate_url($attachmentId) ? wp_get_attachment_url($attachmentId) : $attachmentId;
			$attachmentName = basename($attachmentUrl);
			$filename = Utillities::getFileMeta($attachment['id'], 'filename');

			if (!empty($filename)) {
				$attachmentName = $filename;
			}

			if (empty($attachmentName)) {
				$attachmentName = __('Untitled', 'zephyr-project-manager');
			}

			if (!empty($filename)) {
				$attachmentName = $filename;
			}

			$files[] = [
				'url' => $attachmentUrl,
				'name' => $attachmentName
			];
		}

		return $files;
	}

	public static function isScrum($project) {
		return $project->type == 'scrum';
	}

	public static function getTabs() {
		return apply_filters('zpm/project/single/tabs', [
			'overview' => __('Overview', 'zephyr-project-manager'),
			'tasks' => __('Tasks', 'zephyr-project-manager'),
			'discussion' => __('Discussion', 'zephyr-project-manager'),
			'members' => __('Members', 'zephyr-project-manager'),
			'progress' => __('Progress', 'zephyr-project-manager'),
			'settings' => __('Settings', 'zephyr-project-manager'),
		]);
	}
}
