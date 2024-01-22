<?php

/**
 * @package ZephyrProjectManager
 */

namespace ZephyrProjectManager\Base;

if (!defined('ABSPATH')) {
	die;
}

use \DateTime;
use ZephyrProjectManager\Zephyr;
use ZephyrProjectManager\Api\Emails;
use ZephyrProjectManager\Core\Task;
use ZephyrProjectManager\Core\Tasks;
use ZephyrProjectManager\Core\File;
use ZephyrProjectManager\Core\Message;
use ZephyrProjectManager\Core\Members;
use ZephyrProjectManager\Core\Projects;
use ZephyrProjectManager\Core\Activity;
use ZephyrProjectManager\Core\Utillities;
use ZephyrProjectManager\Core\Categories;
use ZephyrProjectManager\Api\ColorPickerApi;
use ZephyrProjectManager\Base\BaseController;
use ZephyrProjectManager\Pro\Kanban;

class AjaxHandler extends BaseController {
	public $actions = [];
	public $member = [];
	public $userID;

	public function __construct() {
		$this->actions = [
			'filter_by',
			'like_task',
			'follow_task',
			'update_project_members',
			'update_task_priority',
			'create_category',
			'remove_category',
			'update_category',
			'display_category_list',
			'send_comment',
			'remove_comment',
			'display_activities',
			'dismiss_notice',
			'update_user_access',
			'add_team',
			'update_team',
			'get_team',
			'delete_team',
			'get_all_tasks',
			'get_project_tasks',
			'update_task_start_date',
			'update_task_end_date',
			'new_project',
			'remove_project',
			'get_project',
			'get_projects',
			'save_project',
			'update_project_status',
			'like_project',
			'copy_project',
			'export_project',
			'print_project',
			'project_progress',
			'add_project_to_dashboard',
			'remove_project_from_dashboard',
			'remove_project_from_dashboard',
			'switch_project_type',
			'update_user_meta',
			'getUserData',
			'updateTaskDueDate',
			'updateFileProject',
			'newTaskModal',
			'editTaskModal',
			'newProjectModal',
			'editProjectModal',
			'update_subtasks',
			'subtaskEditModal',
			'getCalendarItems',
			'getSubtasks',
			'updateTaskStatus',
			'getStatus',
			'archiveProject',
			'archiveTask',
			'updateMessage',
			'uploadAjaxFile',
			'getTaskComments',
			'uploadDefaultFile',
			'getMembers',
			'sendEmail',
			'updateProjectSetting',
			'loadProjectsFromCSV',
			'loadProjectsFromJSON',
			'exportProjectsToCSV',
			'exportTasksToCSV',
			'saveProjects',
			'saveTasks',
			'getTasksDateRange',
			'removeProjectFromDashboard',
			'getTaskPanelHTML',
			'getProjectPanelHTML',
			'new_task',
			'view_task',
			'copy_task',
			'export_task',
			'export_tasks',
			'upload_tasks',
			'convert_to_project',
			'update_task_completion',
			'remove_task',
			'save_task',
			'get_task',
			'get_tasks',
			'filter_tasks',
			'filter_projects',
			'get_user_projects',
			'team_members_list_html',
			'get_user_progress',
			'create_status',
			'update_status',
			'delete_status',
			'get_user_by_unique_id',
			'complete_project',
			'view_project',
			'get_project_members',
			'project_task_progress',
			'get_paginated_projects',
			'get_members',
			'get_available_project_count',
			'uploadTaskFile',
			'filter_tasks_by',
			'updateTaskMeta',
			'createTaskList',
			'bulkDeleteTasks',
			'bulkArchiveTasks',
			'bulkUpdateTasks',
			'createQuickTask',
			'importIcal',
			'filterTasks',
			'getProjectOverview',
			'updateSubtaskOrder',
			'updateTaskDates',
			'sendTestEmails'
		];

		add_action('admin_init', [$this, 'authenticate']);

		foreach ($this->actions as $action) {
			$this->add_ajax_function($action);
		}
	}

	public function add_ajax_function($function_name) {
		add_action("wp_ajax_zpm_{$function_name}", [$this, $function_name]);
		add_action("wp_ajax_nopriv_zpm_{$function_name}", [$this, $function_name]);
	}

	public function authenticate() {
		if (defined('DOING_AJAX') && DOING_AJAX) {
			$action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';
			$nonce = isset($_POST['zpm_nonce']) ? sanitize_text_field($_POST['zpm_nonce']) : '';

			if (in_array(str_replace('zpm_', '', $action), $this->actions)) {
				if (!wp_verify_nonce($nonce, 'zpm_nonce')) {
					$this->error('Invalid nonce.');
				}

				$this->userID = get_current_user_id();
				$this->member = Members::get_member($this->userID);

				if (!Utillities::canZephyr($this->userID)) {
					$this->error('You do not have Zephyr permissions.');
				}
			}
		}
	}

	public function error($message) {
		wp_send_json_error([
			'message' => $message
		]);
		wp_die();
	}

	public function getTaskComments() {
		$task_id = isset($_POST['id']) ? intval(sanitize_text_field($_POST['id'])) : -1;
		$task_data = Tasks::get_task($task_id);
		$task = new Task($task_data);
		$data = [
			'data' => $task_data
		];
		ob_start(); ?>
			<div class="zpm_task_comments" data-task-id="<?php echo esc_attr($task->id); ?>">
				<?php $comments = $task->getComments(); ?>
				<?php foreach ($comments as $comment) : ?>
					<?php echo wp_kses_post($comment->html()); ?>
				<?php endforeach; ?>
			</div>
		<?php
		$data['html'] = ob_get_clean();
		echo json_encode($data);
		die();
	}

	public function getStatus() {
		$statusSlug = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
		$status = Utillities::get_status($statusSlug);
		echo json_encode($status);
		die();
	}

	public function updateTaskStatus() {
		$taskId = isset($_POST['task_id']) ? sanitize_text_field($_POST['task_id']) : '';
		$status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

		$statusSlug = Utillities::getStatusSlug($status);

		$args = [
			'status' => $statusSlug
		];

		do_action('zpm_task_status_changed', $taskId, $statusSlug);

		Tasks::update($taskId, $args);

		echo json_encode($args);
		die();
	}

	public function getSubtasks() {
		$results = [];
		$taskId = isset($_POST['task_id']) ? intval(sanitize_text_field($_POST['task_id'])) : '';
		if (!empty($taskId)) {
			$results = Tasks::get_subtasks($taskId);
		}

		echo json_encode($results);
		die();
	}

	public function send_comment() {
		global $wpdb;
		global $zpmMessages;

		$table_name = ZPM_MESSAGES_TABLE;
		$date =  date('Y-m-d H:i:s');
		$user_id = isset($_POST['user_id']) ? sanitize_text_field($_POST['user_id']) : $this->get_user_id();
		$subject_id = isset($_POST['subject_id']) ? sanitize_text_field($_POST['subject_id']) : '';
		$message = isset($_POST['message']) ? serialize(stripslashes($_POST['message'])) : '';
		$type = isset($_POST['type']) ? serialize(sanitize_text_field($_POST['type'])) : '';
		$parent_id = isset($_POST['parent_id']) ? intval(sanitize_text_field($_POST['parent_id'])) : 0;
		$attachments = isset($_POST['attachments']) && !empty($_POST['attachments']) ? $_POST['attachments'] : false;
		$subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
		$send_email = isset($_POST['send_email']) ? boolval($_POST['send_email']) : true;
		$settings = array(
			'user_id' => $user_id,
			'subject' => $subject,
			'subject_id' => $subject_id,
			'message' => $message,
			'date_created' => $date,
			'type' => $type,
			'parent_id' => $parent_id,
		);
		$args = $settings;
		$args['attachments'] = $attachments;

		do_action('zpm_new_comment', $args);

		if ($subject !== '') {
			$wpdb->insert($table_name, $settings);
			$last_comment = $wpdb->insert_id;
			$zpmMessages->addReadMessage($last_comment);
		} else {
			$last_comment = false;
		}

		$uploaded = [];

		if ($attachments) {
			$currentUserId = get_current_user_id();

			foreach ((array) $attachments as $attachment) {
				$parent_id = (!$last_comment) ? '' : $last_comment;
				// $attachment_type = isset($attachment['attachment_type']) ? $attachment['attachment_type'] : $subject;
				// $subject_id = isset($attachment['subject_id']) ? $attachment['subject_id'] : $subject_id;
				$settings['user_id'] = $currentUserId;
				$settings['subject'] = !empty($subject) ? $subject : $attachment['attachment_type'];
				$settings['subject_id'] = !empty($subject) ? $subject_id : $attachment['subject_id'];
				$settings['parent_id'] = $parent_id;
				$settings['type'] = serialize('attachment');
				$settings['message'] = serialize($attachment['attachment_id']);
				// var_dump($settings);
				$wpdb->insert($table_name, $settings);
				$id = $wpdb->insert_id;
				$isExternal = isset($attachment['isExternal']) && $attachment['isExternal'] == 'true';
				$icon = isset($attachment['icon']) && !empty($attachment['icon']) ? $attachment['icon'] : '';

				if (!empty($icon)) {
					Utillities::updateFileMeta($id, 'icon', $icon);
				}

				if (isset($attachment['filename']) && !empty($attachment['filename'])) {
					Utillities::updateFileMeta($id, 'filename', $attachment['filename']);
				}

				if ($isExternal) {
					Utillities::updateFileMeta($id, 'isExternal', $isExternal);
				}

				$uploaded[] = $id;
			}
		}

		if ($subject == 'task') {
			$last_comment = Tasks::get_comment($last_comment);
			$message = new Message($last_comment);
			$html = $message->html();
			$last_comment->message = maybe_unserialize($last_comment->message);
			$last_comment->message = html_entity_decode($last_comment->message);
			$attachments = Tasks::get_comment_attachments($last_comment->id);
			$user = BaseController::get_project_manager_user($last_comment->user_id);
			$last_comment->username = $user['name'];
			$attachments_array = [];

			foreach ($attachments as $attachment) {
				$this_attachment = wp_get_attachment_url(unserialize($attachment->message));
				array_push($attachments_array, $this_attachment);
			}

			$last_comment->attachments = $attachments_array;
			$task = Tasks::get_task($subject_id);

			Utillities::sendMentionEmails($last_comment->message, 'task', $task);

			$files = Tasks::get_task_attachments($subject_id);
			ob_start();
			foreach ($files as $attachment) : ?>
				<?php $file = new File($attachment); ?>
				<?php echo wp_kses_post($file->html()); ?>
		<?php endforeach;
			$filesHtml = ob_get_clean();

			$response = array(
				'html' => $html,
				'subject_object' => $task,
				'comment' => $last_comment,
				'files_html' => $filesHtml
			);

			if ($send_email) {
				Emails::send_comment_notification($last_comment, $task, 'task');
			}
		} elseif ($subject == 'project') {
			$last_comment = Projects::get_comment($last_comment);
			$message = new Message($last_comment);
			$last_comment->message = maybe_unserialize($last_comment->message);
			$last_comment->message = html_entity_decode($last_comment->message);
			$html = $message->html();
			$attachments = Projects::get_comment_attachments($last_comment->id);
			$user = BaseController::get_project_manager_user($last_comment->user_id);

			$last_comment->username = $user['name'];
			$attachments_array = [];

			foreach ($attachments as $attachment) {
				$this_attachment = wp_get_attachment_url(unserialize($attachment->message));
				array_push($attachments_array, $this_attachment);
			}

			$last_comment->attachments = $attachments_array;

			$project = Projects::get_project($subject_id);

			Utillities::sendMentionEmails($last_comment->message, 'project', $project);

			$response = array(
				'html' => $html,
				'subject_object' => $project,
				'comment' => $last_comment,
				'project' => true
			);

			if ($send_email) {
				Emails::send_comment_notification($last_comment, $project, 'project');
			}
		} else {
			$html = '';
			if (!empty($uploaded)) {
				$html = Utillities::getFileHtml(BaseController::get_attachment($uploaded[0]));
			}
			$attachments[0]['url'] = wp_get_attachment_url($attachments[0]['attachment_id']);
			$attachments[0]['task_id'] = $subject_id;
			$response = [
				'html' => $html,
				'data' => $attachments[0]
			];
		}

		echo json_encode($response);
		die();
	}

	public function uploadTaskFile() {
		global $wpdb;
		$userId = isset($_POST['user_id']) ? intval(sanitize_text_field($_POST['user_id'])) : get_current_user_id();
		$taskId = isset($_POST['task_id']) ? intval(sanitize_text_field($_POST['task_id'])) : '';
		$fileId = isset($_POST['file_id']) ? intval(sanitize_text_field($_POST['file_id'])) : '';
		$parentId = isset($_POST['parent_id']) ? intval(sanitize_text_field($_POST['parent_id'])) : 0;
		$type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
		$task = new Task($taskId);
		$task->addFile($fileId, $parentId, $type, $userId);
		echo json_encode(['success']);
		die();
	}

	public function new_project() {
		$manager = ZephyrProjectManager();
		$Project = new Projects();
		$data = array();

		if (isset($_POST['project_name'])) {
			$data['name'] = stripslashes(sanitize_text_field($_POST['project_name']));
		}

		if (isset($_POST['project_description'])) {
			$data['description'] = stripslashes(sanitize_textarea_field($_POST['project_description']));
		}

		if (isset($_POST['project_team'])) {
			$data['team'] = serialize(zpm_sanitize_array($_POST['project_team']));
		} else {
			$data['team'] = get_current_user_id();
		}

		if (isset($_POST['project_categories'])) {
			$data['categories'] = serialize(zpm_sanitize_array($_POST['project_categories']));
		}

		if (isset($_POST['project_start_date'])) {
			$data['date_start'] = sanitize_text_field($_POST['project_start_date']);
		}

		if (isset($_POST['project_due_date'])) {
			$data['date_due'] = serialize(sanitize_text_field($_POST['project_due_date']));
		}

		if (isset($_POST['categories'])) {
			$data['categories'] = serialize(zpm_sanitize_array($_POST['categories']));
		}

		$managers = isset($_POST['managers']) ? $_POST['managers'] : [];
		$data['type'] = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'list';
		$data['completed'] = '0';
		$data['priority'] = isset($_POST['priority']) ? sanitize_text_field($_POST['priority']) : 'priority_none';
		$data['assignees'] = implode(',', (array) $managers);
		$data = apply_filters('zpm_new_project_data', $data);
		$last_id = Projects::new_project($data);
		$project = Projects::get_project($last_id);
		$manager::add_project($project);
		$frontend = zpm_sanitize_bool($this->getPostVar('frontend'));

		do_action('zpm_new_project', $project);
		Projects::update_progress($last_id);
		Emails::new_project_email($last_id);

		$username = Members::get_member_name($project->user_id);
		$html = Projects::new_project_cell($project);
		$url = Projects::getUrl($project->id, $frontend);

		$response = [
			'html' 			=> $html,
			'frontend_html' => $html,
			'project' 		=> $project,
			'username'		=> $username,
			'url' 			=> $url
		];

		echo json_encode($response);
		die();
	}

	public function remove_project() {
		$projectID = isset($_POST['project_id']) ? intval(sanitize_text_field($_POST['project_id'])) : -1;
		$project = Projects::get_project($projectID);
		$archiveTasks = $this->getPostVar('archiveTasks', false);
		$deleteTasks = $this->getPostVar('deleteTasks', false);
		Projects::delete_project($projectID, $archiveTasks, $deleteTasks);
		do_action('zpm_project_deleted', $project);

		$return = array(
			'project_count' => Projects::project_count()
		);
		echo json_encode($return);
		die();
	}

	public function archiveProject() {
		$id = isset($_POST['project_id']) ? intval(sanitize_text_field($_POST['project_id'])) : -1;
		$archived = isset($_POST['archived']) ? boolval($_POST['archived']) : true;
		$args = [
			'archived' => $archived
		];
		Projects::update($id, $args);
		echo json_encode([]);
		die();
	}

	public function archiveTask() {
		$id = isset($_POST['id']) ? intval(sanitize_text_field($_POST['id'])) : -1;
		$archived = isset($_POST['archived']) ? zpm_sanitize_bool($_POST['archived']) : true;
		$args = [
			'archived' => $archived
		];
		Tasks::update($id, $args);
		echo json_encode([]);
		die();
	}

	public function save_project() {
		global $wpdb;
		$table_name = ZPM_PROJECTS_TABLE;
		$projectId = isset($_POST['project_id']) ? intval(sanitize_text_field($_POST['project_id'])) : -1;
		$old_project = Projects::get_project($projectId);
		$old_name = stripslashes(esc_html($old_project->name));
		$old_description = $old_project->description;
		$date = date('Y-m-d H:i:s');
		$settings = array();
		$name = isset($_POST['project_name']) ? stripslashes(sanitize_text_field($_POST['project_name'])) : '';
		$description = isset($_POST['project_description']) ? stripcslashes(sanitize_textarea_field($_POST['project_description'])) : '';
		$start_date = isset($_POST['project_start_date']) ? sanitize_text_field($_POST['project_start_date']) : '';
		$due_date = isset($_POST['project_due_date']) ? sanitize_text_field($_POST['project_due_date']) : '';
		$categories = isset($_POST['project_categories']) ? serialize(zpm_sanitize_array($_POST['project_categories'])) : serialize([]);
		$priority = isset($_POST['priority']) ? sanitize_text_field($_POST['priority']) : 'priority_none';
		$project = Projects::get_project($projectId);
		$settings = array(
			'name' 		  => $name,
			'description' => $description,
			'date_start'  => $start_date,
			'date_due'    => $due_date,
			'categories'  => $categories,
			'priority'    => $priority
		);

		if (isset($_POST['assignees'])) {
			$settings['assignees'] = zpm_array_to_comma_string(zpm_sanitize_array($_POST['assignees']));
		}

		$settings = apply_filters('zpm_update_project_data', $settings);

		if (Zephyr::isPro()) {
			$settings['custom_fields'] = isset($_POST['custom_fields']) ? serialize(zpm_sanitize_array($_POST['custom_fields'])) : '';
		}

		$where = array(
			'id' => $projectId
		);

		$wpdb->update($table_name, $settings, $where);
		$last_id = $wpdb->insert_id;

		if ($old_name !== $settings['name']) {
			Activity::log_activity($this->get_user_id(), $projectId, $old_name, esc_html($settings['name']), 'project', 'project_changed_name');
		}

		if ($old_description !== $settings['description']) {
			Activity::log_activity($this->get_user_id(), $projectId, '', esc_html($settings['name']), 'project', 'project_changed_description');
		}

		$general_settings = Utillities::general_settings();
		$start_datetime = new DateTime($settings['date_start']);
		$due_datetime = new DateTime($settings['date_due']);
		$start_date = ($start_datetime->format('Y-m-d') !== '-0001-11-30') ? date_i18n($general_settings['date_format'], strtotime($settings['date_start'])) : __('Not set', 'zephyr-project-manager');
		$due_date = ($due_datetime->format('Y-m-d') !== '-0001-11-30') ? date_i18n($general_settings['date_format'], strtotime($settings['date_due'])) : __('Not set', 'zephyr-project-manager');
		$categories = isset($_POST['project_categories']) ? zpm_sanitize_array($_POST['project_categories']) : array();
		$status_slug = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
		$status = Utillities::get_status($status_slug);
		$status_color = isset($status['color']) ? $status['color'] : '';
		$data = array(
			'id' => $projectId,
			'status' => esc_html($status['name']),
			'status_color' => $status_color
		);

		Projects::update_project_status($projectId, esc_html($status['name']), $status_slug);

		if (Projects::getStatus($project) !== $status_slug && $status_slug == 'completed') {
			do_action('zpm_project_completed', $project);
		}

		if (isset($settings['assignees']) && $settings['assignees'] !== $project->assignees) {
			$assignees = Projects::getAssignees($project);
			$assignees = array_unique($assignees);
			do_action('zpm_project_assigned', $project, $assignees);
		}

		do_action('zpm_project_status_changed', $data);

		$response = array(
			'response' => $settings,
			'categories' => $categories,
			'formatted_start_date' => $start_date,
			'formatted_due_date' => $due_date
		);

		ob_start();

		do_action('zpm_project_preview_fields', $old_project);

		if (isset($_POST['shortcode']) && $_POST['shortcode'] == true) {
			$response['shortcode_html'] = do_shortcode('[zephyr_project id="' . $projectId . '"]');
		}

		$response['custom_fields'] = ob_get_clean();

		if (Utillities::getSetting('task_blocking_enabled')) {
			$response['estimatedDateOfCompletion'] = zpm_date(Projects::getEstimatedCompletionDate($projectId));
		}

		echo json_encode($response);
		die();
	}

	public function update_task_priority() {
		global $wpdb;

		$table_name = ZPM_TASKS_TABLE;
		$settings = array();
		$task_id = isset($_POST['task_id']) ? intval(sanitize_text_field($_POST['task_id'])) : '-1';
		$task = Tasks::get_task($task_id);

		if (isset($_POST['priority'])) {
			$settings['priority'] = sanitize_text_field($_POST['priority']);
		}

		$where = array(
			'id' => $task_id
		);

		$wpdb->update($table_name, $settings, $where);
		$user_id = get_current_user_id();
		$member = Members::get_member($user_id);
		$priority = isset($settings['priority']) ? $settings['priority'] : 'priority_none';
		$status = Utillities::get_status($priority);
		$priority_label = esc_html($status['name']);
		$event_message = '';

		if ($task->priority !== $settings['priority']) {
			$event_message = sprintf(__('%s updated the priority of %s to %s', 'zephyr-project-manager'), $member['name'], esc_html($task->name), $priority_label);
		}

		$response = [
			'post' => $_POST,
			'event_message' => $event_message
		];
		echo json_encode($response);
		die();
	}

	public function update_task_start_date() {
		global $wpdb;

		$table_name = ZPM_TASKS_TABLE;
		$settings = array();
		$task_id = isset($_POST['task_id']) ? intval(sanitize_text_field($_POST['task_id'])) : '-1';

		if (isset($_POST['datetime'])) {
			$format = 'Y-m-d H:i:s';
			$date = date($format, intval(sanitize_text_field($_POST['datetime'])) / 1000);
			$settings['date_start'] = $date;
		}

		$where = array(
			'id' => $task_id
		);
		$wpdb->update($table_name, $settings, $where);

		echo json_encode($settings);
		die();
	}

	public function update_task_end_date() {
		global $wpdb;

		$table_name = ZPM_TASKS_TABLE;
		$settings = array();
		$task_id = isset($_POST['task_id']) ? intval(sanitize_text_field($_POST['task_id'])) : '-1';

		if (isset($_POST['datetime'])) {
			$format = 'Y-m-d H:i:s';
			$date = date($format, intval(sanitize_text_field($_POST['datetime'])) / 1000);
			$settings['date_due'] = $date;
		}

		$where = array(
			'id' => $task_id
		);
		$wpdb->update($table_name, $settings, $where);

		echo json_encode($settings);
		die();
	}

	public function switch_project_type() {
		global $wpdb;
		$table_name = ZPM_PROJECTS_TABLE;
		$settings = array();
		$projectID = isset($_POST['project_id']) ? intval(sanitize_text_field($_POST['project_id'])) : -1;
		$type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'list';
		$project = Projects::get_project($projectID);
		$settings = [
			'type'  => $type
		];
		$where = [
			'id' => $projectID
		];
		$wpdb->update($table_name, $settings, $where);
		do_action('zpm/project/type_changed', $project, $type, $project->type);
		echo json_encode(array(
			'response' => 'success'
		));
		die();
	}

	public function update_project_status() {
		global $wpdb;
		$project_id = isset($_POST['project_id']) ? intval(sanitize_text_field($_POST['project_id'])) : -1;
		$status = isset($_POST['status']) ? sanitize_textarea_field($_POST['status']) : '';
		$status_color = isset($_POST['status_color']) ? sanitize_text_field($_POST['status_color']) : '';
		$data = array(
			'id' => $project_id,
			'status' => $status,
			'status_color' => $status_color
		);

		Projects::update_project_status($project_id, $status, $status_color);

		do_action('zpm_project_status_changed', $data);

		echo json_encode(array(
			'status' => 'success',
			'data' => $data
		));
		die();
	}

	public function update_project_members() {
		global $wpdb;
		$projectID = isset($_POST['project_id']) ? intval(sanitize_text_field($_POST['project_id'])) : -1;
		$project = Projects::get_project($projectID);
		$previousMembers = Projects::getMembers($project);
		$members = isset($_POST['members']) ? zpm_sanitize_array($_POST['members']) : [];
		$removedMembers = [];

		foreach ($previousMembers as $member) {
			if (!in_array($member, $members)) $removedMembers[] = $member;
		}

		do_action('zpm/project/members/removed', $project, $removedMembers);
		Projects::update_members($projectID, $members);
		echo json_encode($members);
		die();
	}

	public function export_project() {
		$projectID = isset($_POST['project_id']) ? intval(sanitize_text_field($_POST['project_id'])) : -1;
		$project = Projects::get_project($projectID);
		$upload_dir = wp_upload_dir();

		if (isset($_POST['export_to']) && $_POST['export_to'] == 'json') {
			$tasks = Tasks::get_project_tasks($projectID, true);
			$project->tasks = $tasks;
			$formattedData = json_encode($project);
			$filename = $upload_dir['basedir'] . '/Project - ' . stripslashes(esc_html($project->name)) . '.json';
			$handle = fopen($filename, 'w+');
			fwrite($handle, $formattedData);
			fclose($handle);
			$filename = $upload_dir['baseurl'] . '/Project - ' . stripslashes(esc_html($project->name)) . '.json';
			$response = array(
				'file_name' => 'Project - ' . stripslashes(esc_html($project->name)) . '.json',
				'file_url'  => $filename
			);
			echo json_encode($response);
		} else {
			$filename = $upload_dir['basedir'] . '/Project - ' . esc_html($project->name) . '.csv';
			$filename = fopen($filename, 'w');
			$headers = array('ID', 'User ID', 'Name', 'Description', 'Completed', 'Assignees', 'Categories', 'Date Created', 'Date Due', 'Date Start', 'Date Completed', 'Other Data');
			$headers = apply_filters('zpm/projects/export/headers', $headers);
			fputcsv($filename, $headers);

			$completed = Projects::isCompleted($project);

			if ($completed == '1') {
				$completed = 'Yes';
			} else {
				$completed = 'No';
			}

			$filedata = [
				'id' => $project->id,
				'user_id' => $project->user_id,
				'name' => $project->name,
				'description' => $project->description,
				'completed' => $completed,
				'assignees' => Members::memberIdStringToNameString($project->assignees),
				'categories' => implode(',', (array) maybe_unserialize($project->categories)),
				'date_created' => $project->date_created,
				'date_due' => $project->date_due,
				'date_start' => $project->date_start,
				'date_completed' => $project->date_completed,
				'other_data' => '',
			];

			$data = apply_filters('zpm/projects/export/data', (array) $filedata, $project);
			fputcsv($filename, $data);
			$filename = $upload_dir['baseurl'] . '/Project - ' . esc_html($project->name) . '.csv';

			// Download project tasks to CSV as well
			$tasks = Tasks::get_project_tasks($projectID, true);
			$tasks = Projects::getOrderedTasks($project->id, $tasks);
			$tasks_file = $upload_dir['basedir'] . '/' . esc_html($project->name) . ' - Tasks.csv';
			header('Content-type: text/csv');
			header('Content-Disposition: attachment; filename="project_tasks.csv"');
			header('Pragma: no-cache');
			header('Expires: 0');

			$tasks_file = fopen($tasks_file, 'w');
			$headers = apply_filters('zpm/tasks/export/headers', ['ID', 'Parent ID', 'Created By', 'Project', 'Assignee', 'Task Name', 'Task Description', 'Categories', 'Completed', 'Created At', 'Start Date', 'Due Date', 'Completed At']);
			fputcsv($tasks_file, $headers);

			// save each row of the data
			foreach ($tasks as $row) {
				$completed = $row->completed;
				if ($completed == '1') {
					$completed = 'Yes';
				} else {
					$completed = 'No';
				}
				$data = (object) [
					'id' => $row->id,
					'parent_id' => $row->parent_id,
					'created_by' => $row->user_id,
					'project' => $row->project,
					'assignee' => Members::memberIdStringToNameString($row->assignee),
					'name' => $row->name,
					'description' => $row->description,
					'categories' => implode(',', (array) maybe_unserialize($row->categories)),
					'completed' => $completed,
					'created' => $row->date_created,
					'start' => $row->date_start,
					'due' => $row->date_due,
					'completed_at' => $row->date_completed
				];
				$data = get_object_vars($data);
				$data = apply_filters('zpm/tasks/export/data', $data, $row);
				fputcsv($tasks_file, $data);
			}

			$tasks_file = $upload_dir['baseurl'] . '/' . esc_html($project->name) . ' - Tasks.csv';

			$files = array(
				'project_csv' => $filename,
				'project_tasks_csv' => $tasks_file,
			);

			echo json_encode($files);
		}

		die();
	}

	public function print_project() {
		$projectId = isset($_POST['project_id']) ? intval(sanitize_text_field($_POST['project_id'])) : -1;
		$project = Projects::get_project($projectId);
		$project_tasks = Tasks::get_project_tasks($projectId);
		$data = array();
		$data['project'] = $project;

		foreach ($project_tasks as $project_task) {
			$user = BaseController::get_user_info($project_task->assignee);
			$project_task->username = $user;
			$data['tasks'][] = $project_task;
		}

		echo json_encode($data);
		die();
	}

	public function project_progress() {
		$project_id = isset($_POST['project_id']) ? intval(sanitize_text_field($_POST['project_id'])) : -1;
		$task_count = Tasks::get_project_task_count($project_id);
		$completed_tasks = Tasks::get_project_completed_tasks($project_id);
		$args = array('project_id' => $project_id);
		$data = Utillities::get_project_chart_data($project_id);
		$response = array(
			'chart_data' => $data
		);
		echo json_encode($response);
		die();
	}

	public function project_task_progress() {
		$projectId = isset($_POST['project_id']) ? intval(sanitize_text_field($_POST['project_id'])) : -1;
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
		echo json_encode($response);
		die();
	}

	public function get_project() {
		$Tasks = new Tasks();
		$project_id = isset($_POST['project_id']) ? intval(sanitize_text_field($_POST['project_id'])) : -1;
		$general_settings = Utillities::general_settings();
		$project = Projects::get_project($project_id);
		$comments = Projects::get_comments($project_id);
		$categories = maybe_unserialize($project->categories);
		$comments_html = '';

		foreach ($comments as $comment) {
			$html = Projects::new_comment($comment);
			$comments_html .= $html;
		}

		$start_date = new DateTime($project->date_start);
		$due_date = new DateTime($project->date_due);
		$start_date = $start_date->format('Y') !== "-0001" ? date_i18n($general_settings['date_format'], strtotime($project->date_start)) : __('None', 'zephyr-project-manager');
		$due_date = $due_date->format('Y') !== "-0001" ? date_i18n($general_settings['date_format'], strtotime($project->date_due)) : __('None', 'zephyr-project-manager');

		$total_tasks = Tasks::get_project_task_count($project->id);
		$completed_tasks = Tasks::get_project_completed_tasks($project->id);
		$active_tasks = (int) $total_tasks - (int) $completed_tasks;
		$message_count = sizeof($comments);

		$priority = property_exists($project, 'priority') ? $project->priority : 'priority_none';
		$priority_label = Utillities::get_priority_label($priority);
		$status = Utillities::get_status($priority);

		ob_start(); ?>

		<?php if ($priority !== "priority_none" && $priority_label !== "") : ?>
			<span class="zpm-task-priority-bubble <?php echo esc_attr($priority); ?>" style="background: <?php echo esc_attr($status['color']); ?>; color: <?php echo esc_html($status['name']) !== '' ? '#fff' : ''; ?>"><?php echo esc_html($status['name']); ?></span>
		<?php endif; ?>

		<?php $priority_html = ob_get_clean();

		ob_start();
		?>
			<span id="zpm_project_modal_dates" class="zpm_project_overview_section">
				<span id="zpm_project_modal_start_date">
					<label class="zpm_label"><?php _e('Start Date', 'zephyr-project-manager'); ?>:</label>
					<span class="zpm_project_date"><?php echo esc_html($start_date); ?></span>
				</span>

				<span id="zpm_project_modal_due_date">
					<label class="zpm_label"><?php _e('Due Date', 'zephyr-project-manager'); ?>:</label>
					<span class="zpm_project_date"><?php echo esc_html($due_date); ?></span>
				</span>
				</span>

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
						<p class="zpm_stat_number"><?php echo esc_html($message_count); ?></p>
						<p><?php _e('Message', 'zephyr-project-manager'); ?></p>
					</span>
				</div>

				<span id="zpm_project_modal_description" class="zpm_project_overview_section">
					<label class="zpm_label"><?php _e('Description', 'zephyr-project-manager'); ?>:</label>
					<p class="zpm_description"><?php echo wp_kses_post($project->description); ?></p>
					<?php if ($project->description == "") : ?>
						<p class="zpm-soft-error"><?php _e('None', 'zephyr-project-manager'); ?></p>
					<?php endif; ?>
				</span>

				<?php do_action('zpm_project_preview_fields', $project); ?>

				<span id="zpm_project_modal_categories" class="zpm_project_overview_section">
				<label class="zpm_label"><?php _e('Categories', 'zephyr-project-manager'); ?>:</label>
				<?php if (is_array($categories) && sizeof($categories)) : ?>
					<?php foreach ($categories as $category) : ?>
						<?php $category = Categories::get_category($category); ?>
						<span class="zpm_project_category"><?php echo esc_html($category->name); ?></span>
					<?php endforeach; ?>

				<?php else : ?>
					<p class="zpm-soft-error"><?php _e('No categories assigned', 'zephyr-project-manager'); ?></p>
				<?php endif; ?>
			</span>
		<?php

		$overview_html = ob_get_clean();
		$project->overview_html = $overview_html;
		$project->comments_html = $comments_html;
		$project->priority_html = $priority_html;
		$project->attachments = Projects::get_attachments($project->id);

		echo json_encode($project);
		die();
	}

	public function get_projects() {
		$projects = Projects::get_available_projects();
		echo json_encode($projects);
		die();
	}

	public function like_project() {
		$projectID = isset($_POST['project_id']) ? intval(sanitize_text_field($_POST['project_id'])) : -1;
		$user_id = $this->get_user_id();
		$liked_projects = unserialize(get_option('zpm_liked_projects_' . $user_id, false));

		if (!$liked_projects) {
			$liked_projects = array();
		}

		if (!in_array($projectID, $liked_projects)) {
			$liked_projects[] = $projectID;
		} else {
			$liked_projects = array_diff($liked_projects, [$projectID]);
		}

		$liked_projects = serialize($liked_projects);
		update_option('zpm_liked_projects_' . $user_id, $liked_projects);
		echo json_encode($liked_projects);
		die();
	}

	public function copy_project() {
		$project_id = (isset($_POST['project_id'])) ? intval(sanitize_text_field($_POST['project_id'])) : -1;
		$copy_options = (isset($_POST['copy_options'])) ? zpm_sanitize_array($_POST['copy_options']) : [];
		$name = isset($_POST['project_name']) ? sanitize_text_field($_POST['project_name']) : '';
		$args = [
			'project_id' => $project_id,
			'project_name' => $name,
			'copy_options' => $copy_options,
		];
		$last_project = Projects::copy_project($args);
		$response = array(
			'html' => Projects::new_project_cell($last_project)
		);
		echo json_encode($response);
		die();
	}

	public function add_project_to_dashboard() {
		$project_id = isset($_POST['project_id']) ? intval(sanitize_text_field($_POST['project_id'])) : false;
		if ($project_id) {
			Projects::add_to_dashboard($project_id);
		}
		return 'Success';
	}

	public function removeProjectFromDashboard() {
		$projectId = isset($_POST['id']) ? intval(sanitize_text_field($_POST['id'])) : false;
		Projects::removeFromDashboard($projectId);
		echo json_encode([]);
		wp_die();
	}

	public function remove_project_from_dashboard() {
		$project_id = isset($_POST['project_id']) ? intval(sanitize_text_field($_POST['project_id'])) : false;
		if ($project_id) {
			Projects::remove_from_dashboard($project_id);
		}
		return 'Success';
	}

	public function new_task() {
		global $wpdb;
		$manager = ZephyrProjectManager();
		$generalSettings = Utillities::general_settings();
		$table_name = ZPM_TASKS_TABLE;
		$assignees = isset($_POST['task_assignee']) && $_POST['task_assignee'] !== '-1' ? $_POST['task_assignee'] : [];
		$assignee = $assignees;
		$project = isset($_POST['task_project']) ? intval(sanitize_text_field($_POST['task_project'])) : '';
		$name = isset($_POST['task_name']) ? stripslashes(sanitize_text_field($_POST['task_name'])) : '';
		$description = isset($_POST['task_description']) ? stripslashes(sanitize_textarea_field($_POST['task_description'])) : '';
		$date = date('Y-m-d H:i:s');
		$date_due = isset($_POST['task_due_date']) ? sanitize_text_field($_POST['task_due_date']) : '';
		$date_start = isset($_POST['task_start_date']) ? sanitize_text_field($_POST['task_start_date']) : $date;
		$team = isset($_POST['team']) ? sanitize_text_field($_POST['team']) : '';
		$priority = isset($_POST['priority']) ? sanitize_text_field($_POST['priority']) : 'priority_none';
		$status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
		$type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'default';
		$parentId = isset($_POST['parent-id']) ? intval(sanitize_text_field($_POST['parent-id'])) : '-1';
		$isDueDateValid = zpm_is_date_valid($date_due);

		if (empty($parentId)) $parentId = '-1';

		$duration = $this->getPostVar('duration');
		$blockingTasks = (array) $this->getPostVar('blockingTasks', []);
		$categories = (array) $this->getPostVar('categories', []);

		if (empty($status)) {
			$status = Utillities::getSetting('default_status');
		}

		// Format start and end dates
		if (!empty($date_due)) {
			$date_due = date("Y-m-d H:i:s", strtotime($date_due));
		}
		if (!empty($date_start)) {
			$date_start = date("Y-m-d H:i:s", strtotime($date_start));
		}

		if (is_array($assignee)) {
			$assignee_string = '';

			foreach ($assignee as $id) {
				$assignee_string .= $id . ',';
			}

			$assignee = $assignee_string;
		}

		if ($duration) {
			$days = $duration - 1;
			$date_due = date('Y-m-d H:i:s', strtotime($date_start . " +{$days} days"));
		}

		$settings = array(
			'user_id' 	  	 => $this->get_user_id(),
			'parent_id'		 => $parentId,
			'assignee' 	  	 => $assignee,
			'project' 	  	 => $project,
			'name' 		  	 => $name,
			'description' 	 => $description,
			'completed'   	 => false,
			'date_start'  	 => $date_start,
			'date_due' 	  	 => $date_due,
			'date_created' 	 => $date,
			'date_completed' => '',
			'priority'		 => $priority,
			'status'		 => $status
		);

		if ($parentId !== '-1') {
			$parentTask = Tasks::get_task($parentId);

			if (is_object($parentTask)) {
				$settings['project'] = $parentTask->project;
			}
		}

		$settings['categories'] = serialize($categories);

		if (Utillities::table_column_exists(ZPM_TASKS_TABLE, 'team')) {
			$settings['team'] = $team;
		}

		if (Zephyr::isPro()) {
			$settings['custom_fields'] = isset($_POST['task_custom_fields']) ? serialize($_POST['task_custom_fields']) : '';
			$settings['kanban_col'] = isset($_POST['kanban_col']) ? sanitize_text_field($_POST['kanban_col']) : '';
			$settings['kanban_col'] = apply_filters('zpm_new_task_kanban_col', $settings['kanban_col'], $settings);
		}

		$settings = apply_filters('zpm_new_task_data', $settings);
		$wpdb->insert($table_name, $settings);
		$last_id = $wpdb->insert_id;

		if ($duration) {
			Tasks::updateMeta($last_id, 'duration', $duration);
		}

		if ($this->hasParam('blockingTasks')) {
			Tasks::updateBlockingTasks($last_id, $blockingTasks);
			Tasks::updateBlockingTaskDependencyDates($last_id);
		}

		$recurrence = isset($_POST['recurrence']) ? $_POST['recurrence'] : '';
		$recurrence_type = isset($recurrence['type']) ? $recurrence['type'] : 'default';
		$recurrence_expiration = isset($recurrence['expires']) ? $recurrence['expires'] : '';
		$recurrence_days = isset($recurrence['days']) ? $recurrence['days'] : '';
		$recurrence_start = isset($recurrence['starts']) ? $recurrence['starts'] : '';

		Tasks::update_task_data($last_id, array(
			'type' => $recurrence_type,
			'expires' => $recurrence_expiration,
			'days' => $recurrence_days,
			'start' => $recurrence_start
		));

		$task = Tasks::get_task($last_id);
		Utillities::sendMentionEmails($description, 'task', $task);
		$manager::add_task($task);
		// $due_date = new DateTime($task->date_due);
		// $task->date_due = $due_date->format('Y') !== '-0001' ? $due_date->format('d M') : '';
		$task->original_due_date = $settings['date_due'];
		$task_project = Projects::get_project($task->project);
		$task->project_name = is_object($task_project) ? esc_html($task_project->name) : '';

		if (Tasks::hasProject($task)) {
			$project = Projects::get_project($task->project);
			$completed_project_tasks = Tasks::get_project_completed_tasks($task->project);
			$project_tasks = Tasks::get_project_tasks($task->project);

			if ($completed_project_tasks == sizeof($project_tasks)) {
				$completed = '1';
			} else {
				$completed = '0';
			}

			Projects::mark_complete($task->project, $completed);
			$status = Projects::getStatus($project);

			if (in_array($status, ['not_started', 'completed'])) {
				Projects::update($project->id, [
					'status' => 'in_progress'
				]);
			}
		}

		do_action('zpm_new_task', $task);
		do_action('zpm_task_created', $task);
		$task = apply_filters('zpm/new/task', $task);

		Activity::log_activity($settings['user_id'], $last_id, '', $name, 'task', 'task_added');
		Activity::log_activity($settings['user_id'], $last_id, '', $name, 'task', 'task_assigned');

		$task->sending_email = "true";
		$emails = Emails::assignedTaskEmail($task);

		Projects::update_progress($project);

		$date = new DateTime($task->date_due);
		$frontend = isset($_POST['frontend']) ? true : false;
		$kanban_html = "";

		if ($generalSettings['auto_unassign_on_project_remove']) {
			if (Tasks::hasProject($task)) {
				$project = $task_project;
				$projectAssignees = !is_array($project->team) ? maybe_unserialize($project->team) : $project->team;
				$projectAssignees = array_merge((array) $projectAssignees, (array) $assignees);
				Projects::update_members($project->id, $projectAssignees);
			}
		}

		if (class_exists('ZephyrProjectManager\\Pro\\Kanban')) {
			$kanban = new Kanban();
			$kanban_html = method_exists($kanban, 'taskHtml') ? Kanban::taskHtml($task, $frontend) : '';
			$kanban_col = isset($settings['kanban_col']) ? $settings['kanban_col'] : '1';
			$task->kanban_html = $kanban_html;
			// $task->kanban_col = $kanban_col;
		}

		if (!Tasks::hasParent($task)) {
			$task->new_task_html = Tasks::new_task_row($task, $frontend);
		} else {
			$task->new_task_html = Tasks::subtaskItemHtml($task);
		}

		$task->id = $last_id;
		$task->name = $name;
		$task->username = Members::get_member_name($this->get_user_id());
		$task->settings = $settings;

		if (isset($_POST['shortcode']) && $_POST['shortcode']) {
			$type = isset($_POST['shortcode_type']) ? sanitize_text_field($_POST['shortcode_type']) : 'cards';
			$task->shortcode_html = do_shortcode('[zephyr_task id="' . $last_id . '" type="' . $type . '"]');
		}
		$task->settings = $settings;
		$task->emails = $emails;
		$task = apply_filters('zpm/tasks/new/response', $task);
		echo json_encode($task);
		die();
	}

	public function createQuickTask() {
		$data = $this->getParam('data', []);
		$newTaskID = Tasks::create($data);
		$newTask = Tasks::getTask($newTaskID);
		wp_send_json_success($newTask);
	}

	public function view_task() {
		$task_id = isset($_POST['task_id']) ? intval(sanitize_text_field($_POST['task_id'])) : '-1';
		ob_start();
		Tasks::view_task_modal($task_id);
		$html = ob_get_clean();
		echo $html;
		die();
	}

	public function copy_task() {
		global $wpdb;
		$table_name = ZPM_TASKS_TABLE;
		$task_id = (isset($_POST['task_id'])) ? intval(sanitize_text_field($_POST['task_id'])) : '';
		$task = Tasks::get_task($task_id);
		$copy_options =  (isset($_POST['copy_options'])) ? zpm_sanitize_array($_POST['copy_options']) : '';
		$date = date('Y-m-d H:i:s');
		$user_id = $this->get_user_id();
		$assignee = in_array('assignee', $copy_options) ? $task->assignee : $date;
		$name = isset($_POST['task_name']) ? sanitize_text_field($_POST['task_name']) : '';
		$description = in_array('description', $copy_options) ? sanitize_textarea_field($task->description) : '';
		$date_start = in_array('start_date', $copy_options) ? $task->date_start : $date;
		$date_due = in_array('due_date', $copy_options) ? $task->date_due : '';
		$project_id = isset($_POST['project']) ? sanitize_text_field($_POST['project']) : $task->project;
		$frontend = isset($_POST['frontend']) ? (boolean) $_POST['frontend'] : false;
		$settings = array(
			'user_id' 		 => $user_id,
			'assignee' 		 => $assignee,
			'project' 		 => $project_id,
			'name' 			 => $name,
			'description' 	 => $description,
			'completed' 	 => $task->completed,
			'date_start' 	 => $date_start,
			'date_due' 		 => $date_due,
			'date_created' 	 => $date,
			'date_completed' => ''
		);

		if (class_exists('ZephyrProjectManager\\Pro\\CustomFields')) {
			if (property_exists($task, 'custom_fields')) {
				$settings['custom_fields'] = $task->custom_fields;
			}
		}

		$wpdb->insert($table_name, $settings);
		$last_id = $wpdb->insert_id;
		$subtasks = Tasks::get_subtasks($task_id);

		foreach ($subtasks as $subtask) {
			$settings = array(
				'parent_id'		 => $last_id,
				'user_id' 		 => $user_id,
				'assignee' 		 => $subtask->assignee,
				'project' 		 => $project_id,
				'name' 			 => $subtask->name,
				'completed' 	 => $subtask->completed,
				'date_start' 	 => $subtask->date_start,
				'date_due' 		 => $subtask->date_due,
				'date_created' 	 => $subtask->date_created,
				'date_completed' => ''
			);

			$wpdb->insert($table_name, $settings);
		}

		$new_task = Tasks::get_task($last_id);

		$user_id = get_current_user_id();
		$member = Members::get_member($user_id);
		$event_message = sprintf(__('%s copied the task %s', 'zephyr-project-manager'), $member['name'], esc_html($task->name));

		$response = array(
			'html' => Tasks::new_task_row($new_task),
			'task' => $new_task,
			'event_message' => $event_message
		);

		if (class_exists('ZephyrProjectManager\\Pro\\Kanban')) {
			$kanban = new Kanban();
			$kanban_html = method_exists($kanban, 'taskHtml') ? Kanban::taskHtml($new_task, $frontend) : '';
			$response['kanban_html'] = $kanban_html;
		}


		Projects::update_progress($task->project);

		echo json_encode($response);
		die();
	}

	public function export_task() {
		$taskID = intval(sanitize_text_field($_POST['task_id']));
		$task = Tasks::get_task($taskID);
		$upload_dir = wp_upload_dir();

		if (isset($_POST['export_to']) && $_POST['export_to'] == 'json') {
			// Save JSON file
			$data = array($task);
			$formattedData = json_encode($data);
			$filename = $upload_dir['basedir'] . '/Task - ' . esc_html($task->name) . '.json';
			$handle = fopen($filename, 'w+');
			fwrite($handle, $formattedData);
			fclose($handle);
			$filename = $upload_dir['baseurl'] . '/Task - ' . esc_html($task->name) . '.json';
			$response = [
				'file_url'  => $filename,
				'file_name' => 'Task - ' . esc_html($task->name) . '.json'
			];
			echo json_encode($response);
		} else {
			$filename = $upload_dir['basedir'] . '/Task - ' . esc_html($task->name) . '.csv';
			$filename = fopen($filename, 'w');
			fputcsv($filename, array('ID', 'Parent ID', 'Created By', 'Project', 'Assignee', 'Task Name', 'Task Description', 'Categories', 'Completed', 'Created At', 'Start Date', 'Due Date', 'Completed At'));

			$data = (object) [
				'id' => $task->id,
				'parent_id' => $task->parent_id,
				'created_by' => $task->user_id,
				'project' => $task->project,
				'assignee' => $task->assignee,
				'name' => $task->name,
				'description' => $task->description,
				'categories' => implode(',', (array) maybe_unserialize($task->categories)),
				'completed' => $task->completed,
				'created' => $task->date_created,
				'start' => $task->date_start,
				'due' => $task->date_due,
				'completed_at' => $task->date_completed
			];

			fputcsv($filename, get_object_vars($data));

			$filename = $upload_dir['baseurl'] . '/Task - ' . esc_html($task->name) . '.csv';
			$response = [
				'file_url'  => $filename,
				'file_name' => 'Task - ' . esc_html($task->name) . '.csv'
			];
			echo json_encode($response);
		}
		die();
	}

	public function export_tasks() {
		$tasks = Tasks::getAllTasks();
		$upload_dir = wp_upload_dir();

		if (isset($_POST['export_to']) && $_POST['export_to'] == 'json') {

			$formattedData = json_encode($tasks);
			$filename = $upload_dir['basedir'] . '/All Tasks.json';
			$handle = fopen($filename, 'w+');
			fwrite($handle, $formattedData);
			fclose($handle);
			$filename = $upload_dir['baseurl'] . '/All Tasks.json';
			echo json_encode($filename);
		} else {
			$filename = $upload_dir['basedir'] . '/All Tasks.csv';
			// save the column headers

			header('Content-type: text/csv');
			header('Content-Disposition: attachment; filename="all_tasks.csv"');
			header('Pragma: no-cache');
			header('Expires: 0');

			$filename = fopen($filename, 'w');
			$headers = array('ID', 'Parent ID', 'Created By', 'Project', 'Assignee', 'Task Name', 'Task Description', 'Categories', 'Completed', 'Created At', 'Start Date', 'Due Date', 'Completed At', 'Team', 'Custom Fields', 'Status', 'Kanban Col', 'Priority', 'Other Data');
			$headers = apply_filters('zpm/tasks/export/headers', $headers);
			fputcsv($filename, $headers);

			// save each row of the data
			foreach ($tasks as $row) {
				$data = get_object_vars($row);
				$data = apply_filters('zpm/tasks/export/data', $data, $row);
				fputcsv($filename, $data);
			}
			$filename = $upload_dir['baseurl'] . '/All Tasks.csv';
			echo json_encode($filename);
		}

		die();
	}

	public function upload_tasks() {
		global $wpdb;
		$html = '';
		$filename = sanitize_text_field($_POST['zpm_file']);
		$file_type = sanitize_text_field($_POST['zpm_import_via']);
		$table_name = ZPM_TASKS_TABLE;

		if ($file_type == 'csv') {
			$row = 1;
			$taskArray = array();
			if (($handle = fopen($filename, "r")) !== FALSE) {
				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					$num = count($data);
					$task = array(
						'id' 			 => $data[0],
						'parent_id' 	 => $data[1],
						'user_id' 		 => $data[2],
						'project' 		 => $data[3],
						'assignee' 		 => $data[4],
						'name' 			 => $data[5],
						'description' 	 => $data[6],
						'categories' 	 => $data[7],
						'completed' 	 => $data[8],
						'date_created' 	 => $data[9],
						'date_start' 	 => $data[10],
						'date_due' 		 => $data[11],
						'date_completed' => $data[12],
						'team' 			 => $data[13],
						'custom_fields'  => $data[14],
						'status' 		 => $data[15],
						'kanban_col' 	 => $data[16],
						'priority' 	 	 => $data[17],
						'other_data' 	 => $data[18],
					);
					$task['date_start'] = date('Y-m-d', $task['date_start']);
					$task['date_due'] = date('Y-m-d', $task['date_due']);
					$task['date_completed'] = date('Y-m-d', $task['date_completed']);

					if (!Tasks::task_exists($data[0])) {
						$wpdb->insert($table_name, $task);
						$task = Tasks::get_task($wpdb->insert_id);
						if ($row > 1) {
							$html .= Tasks::new_task_row($task);
						}
					} else {
						$task['already_uploaded'] = true;
					}

					$row++;

					$taskArray[] = $task;
				}
				fclose($handle);
			}
			$response = [
				'tasks' => $taskArray,
				'html' => $html
			];
			echo json_encode($response);
		} elseif ($file_type == 'json') {
			$json = file_get_contents($filename);
			$json_array = json_decode($json, true);
			$taskArray = array();

			foreach ($json_array as $task) {
				$task = array(
					'id' 			 => $task['id'],
					'parent_id' 	 => $task['parent_id'],
					'user_id' 		 => $task['user_id'],
					'project' 		 => $task['project'],
					'assignee' 		 => $task['assignee'],
					'name' 			 => $task['name'],
					'description' 	 => $task['description'],
					'categories' 	 => $task['categories'],
					'completed' 	 => $task['completed'],
					'date_created' 	 => $task['date_created'],
					'date_start' 	 => $task['date_start'],
					'date_due' 		 => $task['date_due'],
					'date_completed' => $task['date_completed'],
					'team' 			 => $task['team'],
					'custom_fields'  => $task['custom_fields'],
					'status' 		 => $task['status'],
					'kanban_col' 	 => $task['kanban_col'],
					'priority' 	 	 => $task['priority'],
					'other_data' 	 => $task['other_data']
				);

				if (!Tasks::task_exists($task['id'])) {
					$wpdb->insert($table_name, $task);
					$task = Tasks::get_task($wpdb->insert_id);
					$html .= Tasks::new_task_row($task);
				} else {
					$task['already_uploaded'] = true;
				}
				$taskArray[] = $task;
			}
			$response = [
				'tasks' => $taskArray,
				'html' => $html
			];
			echo json_encode($response);
		}
		die();
	}

	public function convert_to_project() {
		global $wpdb;
		$task_id = (isset($_POST['task_id'])) ? intval(sanitize_text_field($_POST['task_id'])) : -1;
		$task = Tasks::get_task($task_id);
		$convert_options =  (isset($_POST['convert_options'])) ? zpm_sanitize_array($_POST['convert_options']) : [];
		$subtasks = (in_array('subtasks', $convert_options)) ? Tasks::get_subtasks($task_id) : '';
		$table_name = ZPM_PROJECTS_TABLE;
		$date = date('Y-m-d H:i:s');
		$settings = array();
		$settings['user_id'] = $this->get_user_id();
		$settings['name'] = (isset($_POST['project_name'])) ? sanitize_text_field($_POST['project_name']) : '';
		$settings['description'] = (in_array('description', $convert_options)) ? sanitize_textarea_field($task->description) : '';
		$settings['completed'] = false;
		$settings['date_due'] = $task->date_start;
		$settings['date_created'] = $date;
		$settings['date_completed'] = '';
		$wpdb->insert($table_name, $settings);
		$last_id = $wpdb->insert_id;

		if (is_array($subtasks)) {
			$tasks_table = ZPM_TASKS_TABLE;
			foreach ($subtasks as $subtask) {
				$task_settings = array();
				$task_settings['parent_id'] = '-1';
				$task_settings['user_id'] = $this->get_user_id();
				$task_settings['assignee'] = $this->get_user_id();
				$task_settings['project'] = $last_id;
				$task_settings['name'] = $subtask->name;
				$task_settings['description'] = '';
				$task_settings['completed'] = false;
				$task_settings['date_start'] = $date;
				$task_settings['date_due'] = '';
				$task_settings['date_created'] = $date;
				$task_settings['date_completed'] = '';
				$wpdb->insert($tasks_table, $task_settings);
			}
		}

		$project = new Projects();
		$new_project = $project->get_project($last_id);
		echo json_encode($new_project);
		die();
	}

	public function update_task_completion() {
		global $wpdb;
		$table_name = ZPM_TASKS_TABLE;
		$date = date('Y-m-d H:i:s');
		$completed = isset($_POST['completed']) ? sanitize_text_field($_POST['completed']) : '0';
		$task_id = isset($_POST['id']) ? intval(sanitize_text_field($_POST['id'])) : -1;
		$settings = array(
			'completed' 		=> $completed,
			'date_completed' 	=> $date
		);
		$where = array(
			'id' => $task_id
		);
		$wpdb->update($table_name, $settings, $where);
		$task = Tasks::get_task($task_id);
		$blockingTasksEnabled = Utillities::getSetting('task_blocking_enabled');
		$isSubtask = Tasks::isSubtask($task);

		if ($isSubtask) {
			$parent = Tasks::get_task($task->parent_id);

			if ($parent->status == 'priority_none' || empty($parent->status) || $parent->status == 'not_started') {
				Tasks::update($parent->id, [
					'status' => 'in_progress'
				]);
			}
		}

		$isCompleted = $completed === '1' || $completed === 'true';

		if ($isCompleted) {
			Emails::task_completed_email($task);
			do_action('zpm_task_completed', $task);

			if ($blockingTasksEnabled) {
				$blockedTasks = Tasks::getBlockedTasks($task->id);
				$startDate = date('Y-m-d H:i:s');

				foreach ($blockedTasks as $blockedTask) {
					$isBlocked = Tasks::isBlocked($blockedTask->id);

					if (!$isBlocked) {
						// TODO: Add notification that task is unblocked

						if (Utillities::getSetting('task_duration_enabled')) {
							$duration = Tasks::getMeta($blockedTask->id, 'duration', false);

							if ($duration !== false) {
								$endDate = date('Y-m-d H:i:s', strtotime($startDate . ' +' . $duration . ' days'));
								Tasks::update($blockedTask->id, [
									'date_start' => $startDate,
									'date_due' => $endDate
								]);
							}
						}
					}
				}
			}
		}

		Tasks::updateTaskProgress($task);

		$completed_project_tasks = Tasks::get_project_completed_tasks($task->project);
		$project_tasks = Tasks::get_project_tasks($task->project);

		if ($completed_project_tasks == sizeof($project_tasks)) {
			$completed = '1';
		} else {
			$completed = '0';
		}

		do_action('zpm_task_status_changed', $task->id, 'completed');
		$response = array();

		if ($isCompleted) {
			Tasks::update($task->id, [
				'status' => 'completed'
			]);
		} else {
			Tasks::update($task->id, [
				'status' => 'in_progress'
			]);
		}

		if (Tasks::hasProject($task)) {
			Projects::mark_complete($task->project, $completed);
			Projects::update_progress($task->project);
			$project = Projects::get_project($task->project);
			$percent = Projects::percent_complete($task->project);
			$response['percent'] = $percent;
			Utillities::updateProjectProgress($task->project);
			$status = Projects::getStatus($project);


			if (!$isCompleted) {
				if (in_array($status, ['not_started', 'completed', 'priority_none', 'not-started'])) {
					Projects::update($project->id, [
						'status' => 'in_progress'
					]);
				}
			} else {
				$allCompleted = true;

				foreach ($project_tasks as $projectTask) {
					if (intval($projectTask->id) !== intval($task_id) && !Tasks::isCompleted($projectTask)) {
						$allCompleted = false;
					}
				}

				if ($allCompleted) {
					if (in_array($status, ['not_started', 'in_progress'])) {
						Projects::update($project->id, [
							'status' => 'completed'
						]);
					}
				}
			}
		}

		$response['custom_data'] = apply_filters('zpm_task_completed_response', $task->id);
		echo json_encode($response);
		die();
	}

	public function remove_task() {
		global $wpdb;
		$table_name = ZPM_TASKS_TABLE;
		$taskID = intval(sanitize_text_field($_POST['task_id']));
		$task = Tasks::get_task($taskID);
		$settings = array(
			'id' => $taskID
		);
		Emails::delete_task_email($taskID);
		do_action('zpm_task_deleted', $taskID);
		$wpdb->delete($table_name, $settings, ['%d']);
		Activity::log_activity($this->get_user_id(), $taskID, '', esc_html($task->name), 'task', 'task_deleted');
		echo 'Success';
		die();
	}

	public function save_task() {
		global $wpdb;
		$table_name = ZPM_TASKS_TABLE;
		$task_id = isset($_POST['task_id']) ? intval(sanitize_text_field($_POST['task_id'])) : '';
		$old_task = Tasks::get_task($task_id);
		$assignees = (isset($_POST['task_assignee'])) ? $_POST['task_assignee'] : [];
		$settings = array();
		$settings['name'] = (isset($_POST['task_name'])) ? stripslashes(sanitize_text_field($_POST['task_name'])) : '';
		$settings['description'] = (isset($_POST['task_description'])) ? stripslashes(sanitize_textarea_field($_POST['task_description'])) : '';
		$settings['assignee'] = $assignees;
		$settings['date_due'] = (isset($_POST['task_due_date'])) ? sanitize_text_field($_POST['task_due_date']) : '';
		$settings['date_start'] = (isset($_POST['task_start_date'])) ? sanitize_text_field($_POST['task_start_date']) : '';
		$settings['project'] = (isset($_POST['task_project'])) ? intval(sanitize_text_field($_POST['task_project'])) : '-1';
		$settings['team'] = (isset($_POST['team'])) ? sanitize_text_field($_POST['team']) : -1;
		$settings['priority'] = (isset($_POST['priority'])) ? sanitize_text_field($_POST['priority']) : 'priority_none';
		$settings['status'] = (isset($_POST['status'])) ? sanitize_text_field($_POST['status']) : '';
		$parentID = $this->getPostVar('parentID', '');
		$isDueDateValid = zpm_is_date_valid($settings['date_due']);

		if (!empty($parentID)) {
			$settings['parent_id'] = $parentID;
		}

		$duration = (int) $this->getPostVar('duration');
		$type = (isset($_POST['type'])) ? sanitize_text_field($_POST['type']) : 'default';
		$categories = (array) $this->getPostVar('categories', []);

		if (is_array($settings['assignee'])) {
			$assignee_string = '';

			foreach ($settings['assignee'] as $id) {
				$assignee_string .= $id . ',';
			}
			$settings['assignee'] = $assignee_string;
		}

		if (Zephyr::isPro()) {
			$settings['custom_fields'] = isset($_POST['task_custom_fields']) ? serialize($_POST['task_custom_fields']) : '';
		}

		// Format start and end dates
		if (!empty($settings['date_start'])) {
			$settings['date_start'] = date("Y-m-d H:i:s", strtotime($settings['date_start']));
		}

		if (!empty($settings['date_due'])) {
			$settings['date_due'] = date("Y-m-d H:i:s", strtotime($settings['date_due']));
		}

		$shouldUpdateDuration = false;
		Tasks::updateMeta($task_id, 'duration', $duration);

		if (!empty($duration)) {
			// $currentDuration = (int) Tasks::getMeta($task_id, 'duration', 0);
			// $currentDueDate = $old_task->date_due;

			// if (intval($currentDuration) !== intval($duration)) {
			// 	$updatedTask = Tasks::get_task($task_id);
			// 	$updatedTask->date_due = '';
			// 	$newDueDate = Tasks::getEndDate($updatedTask);
			// 	$settings['date_due'] = date('Y-m-d H:i:s', strtotime($newDueDate));
			// } else if (date('Y-m-d', strtotime($currentDueDate)) !== date('Y-m-d', strtotime($settings['date_due']))) {
			// 	$shouldUpdateDuration = true;
			// }

			// if (!$isDueDateValid) {
			// }
		}

		$settings['categories'] = serialize($categories);
		$settings = apply_filters('zpm_update_task_data', $settings);
		$where = array(
			'id' => $task_id
		);
		$wpdb->update($table_name, $settings, $where);
		$recurrence = isset($_POST['recurrence']) ? zpm_sanitize_array($_POST['recurrence']) : [];
		$recurrence_type = isset($recurrence['type']) ? $recurrence['type'] : 'default';
		$recurrence_expiration = isset($recurrence['expires']) ? $recurrence['expires'] : '';
		$recurrence_days = isset($recurrence['days']) ? $recurrence['days'] : '';
		$frequency = isset($recurrence['frequency']) ? $recurrence['frequency'] : '';
		$recurrence_start = isset($recurrence['start']) ? $recurrence['start'] : '';

		Tasks::update_task_data($task_id, array(
			'type' => $recurrence_type,
			'expires' => $recurrence_expiration,
			'days' => $recurrence_days,
			'frequency' => $frequency,
			'start'	=> $recurrence_start
		));

		$date = date('Y-m-d H:i:s');
		$newBlockingTasks = isset($_POST['blockingTasks']) ? $_POST['blockingTasks'] : [];
		$blockingTasks = Tasks::getBlockingTasks($task_id);
		$blockingTasksDiff = array_diff($blockingTasks, $newBlockingTasks);
		$blockingTasksDiffB = array_diff($newBlockingTasks, $blockingTasks);

		// zpm_dump([
		// 	'diff' => $blockingTasksDiff,
		// 	'blockingTasks' => $blockingTasks,
		// 	'postBlockingTasks' => $newBlockingTasks
		// ]);

		if ($blockingTasksDiff !== $blockingTasksDiffB) {
			Tasks::updateBlockingTasks($task_id, $newBlockingTasks);
			$newStart = date('Y-m-d', strtotime(Tasks::updateBlockingTaskDependencyDates($task_id)));
		}

		if ($old_task->name !== $settings['name']) {
			Activity::log_activity($this->get_user_id(), $task_id, $old_task->name, esc_html($settings['name']), 'task', 'task_changed_name');
		}

		// Mark as complete if the status is changed to 'Completed'
		if ($settings['status'] == 'completed') {
			Tasks::complete($task_id, 1);
		}

		if ($old_task->date_due !== $settings['date_due']) {
			Activity::log_activity($this->get_user_id(), $task_id, esc_html($settings['name']), $settings['date_due'], 'task', 'task_changed_date');
			$date_due = new DateTime($settings['date_due']);
			Emails::task_date_change_email($task_id, esc_html($settings['name']), $date_due);
		}

		$general_settings = Utillities::general_settings();
		$start_datetime = new DateTime($settings['date_start']);
		$due_datetime = new DateTime($settings['date_due']);
		$start_date = ($start_datetime->format('Y-m-d') !== '-0001-11-30') ? date_i18n($general_settings['date_format'], strtotime($settings['date_start'])) : __('Not set', 'zephyr-project-manager');
		$due_date = ($due_datetime->format('Y-m-d') !== '-0001-11-30') ? date_i18n($general_settings['date_format'], strtotime($settings['date_due'])) : __('Not set', 'zephyr-project-manager');
		$task = Tasks::get_task($task_id);

		if ($shouldUpdateDuration) {
			Tasks::updateMeta($task_id, 'duration', Tasks::getDuration($task));
		}

		if ($old_task->status !== $settings['status']) {
			do_action('zpm_task_status_changed', $task_id, $settings['status']);
			do_action('zpm/task/status_changed', $task, $settings['status']);
		}

		ob_start();
		echo apply_filters('zpm_task_update_response', $task_id);
		do_action('zpm_task_updated', $task);

		if ($old_task->assignee !== $settings['assignee'] && !empty($settings['assignee']) && $settings['assignee'] !== '-1') {
			Emails::assignedTaskEmail($task);
		}

		$team = Members::get_team($settings['team']);
		$team_name = !is_null($team) ? esc_html($team['name']) : __('None', 'zephyr-project-manager');
		$other = ob_get_clean();
		$status = Utillities::get_status($task->status);
		$response = array(
			'task_id' => $task_id,
			'formatted_start_date' => $start_date,
			'formatted_due_date'   => $due_date,
			'other'				   => $other,
			'team_name'			   => $team_name,
			'status'			   => $status,
			'task' 				   => $task
		);

		if (isset($newStart)) {
			$response['newStartDate'] = $newStart;
		}

		if (class_exists('ZephyrProjectManager\\Pro\\Kanban')) {
			$kanban = new Kanban();
			$kanban_html = method_exists($kanban, 'taskHtml') ? Kanban::taskHtml($task) : '';
			$response['kanban_html'] = $kanban_html;
		}

		if (isset($_POST['shortcode']) && $_POST['shortcode']) {
			$type = isset($_POST['shortcode_type']) ? sanitize_text_field($_POST['shortcode_type']) : 'cards';
			$response['shortcode_html'] = do_shortcode('[zephyr_task id="' . $task_id . '" type="' . $type . '"]');
		}

		if (Utillities::getSetting('task_blocking_enabled') || Utillities::getSetting('task_duration_enabled')) {
			$response['estimatedDateOfCompletion'] = zpm_date(Tasks::getEstimatedCompletionDate($task));

			if ($shouldUpdateDuration) {
				$response['duration'] = Tasks::getDuration($task);
			}
		}

		if (!empty($duration)) {
			// $response['newDueDate'] = zpm_date(Tasks::getEndDate($task), '');
		}

		echo json_encode($response);
		die();
	}

	public function like_task() {
		$task_id = intval(sanitize_text_field($_POST['task_id']));
		$user_id = $this->get_user_id();
		$liked_tasks = get_option('zpm_liked_tasks_' . $user_id, false);
		$liked_tasks = unserialize($liked_tasks);

		if (!$liked_tasks) {
			$liked_tasks = array();
		}

		if (!in_array($task_id, $liked_tasks)) {
			$liked_tasks[] = $task_id;
		} else {
			$liked_tasks = array_diff($liked_tasks, [$task_id]);
		}

		$liked_tasks = serialize($liked_tasks);
		update_option('zpm_liked_tasks_' . $user_id, $liked_tasks);

		echo json_encode($liked_tasks);
		die();
	}

	public function follow_task() {
		$task_id = intval(sanitize_text_field($_POST['task_id']));
		$user_id = $this->get_user_id();
		$followed_tasks = get_option('zpm_followed_tasks_' . $user_id, false);
		$followed_tasks = unserialize($followed_tasks);

		if (!$followed_tasks) {
			$followed_tasks = array();
		}

		if (!in_array($task_id, $followed_tasks)) {
			$followed_tasks[] = $task_id;
		} else {
			$followed_tasks = array_diff($followed_tasks, [$task_id]);
		}

		$followed_tasks = serialize($followed_tasks);
		update_option('zpm_followed_tasks_' . $user_id, $followed_tasks);
		$user = BaseController::get_project_manager_user($user_id);
		$html = '<span class="zpm_task_follower" data-user-id="' . $user['id'] . '" title="' . $user['name'] . '" style="background-image: url(' . $user['avatar'] . ');"></span>';
		$following = in_array($task_id, unserialize($followed_tasks)) ? true : false;
		$response = array(
			'html' 		=> $html,
			'following' => $following,
			'user_id'   => $user_id
		);
		echo json_encode($response);
		die();
	}

	public function update_subtasks() {
		global $wpdb;
		$table_name = ZPM_TASKS_TABLE;
		$task_id = intval(sanitize_text_field($_POST['task_id']));
		$action = sanitize_text_field($_POST['subtask_action']);

		switch ($action) {
			case 'new_subtask':
				$parent_task = Tasks::get_task($task_id);
				$subtask_name = isset($_POST['subtask_name']) ? sanitize_text_field($_POST['subtask_name']) : '';
				$description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
				$start = isset($_POST['start']) ? sanitize_text_field($_POST['start']) : $parent_task->date_start;
				$due = isset($_POST['due']) ? sanitize_text_field($_POST['due']) : $parent_task->date_due;

				$date = date('Y-m-d H:i:s');

				$settings = array(
					'parent_id' 	 => $parent_task->id,
					'user_id' 		 => $parent_task->user_id,
					'assignee' 		 => $parent_task->assignee,
					'project' 		 => $parent_task->project,
					'name' 			 => $subtask_name,
					'description' 	 => $description,
					'completed' 	 => false,
					'date_start' 	 => $start,
					'date_due' 		 => $due,
					'date_created' 	 => $date,
					'date_completed' => ''
				);

				$wpdb->insert($table_name, $settings);
				$subtask = Tasks::get_task($wpdb->insert_id);

				$response = array(
					'name' => $subtask_name,
					'id' => $wpdb->insert_id,
					'html' => Tasks::subtaskItemHtml($subtask)
				);

				Emails::new_subtask_email($subtask, get_current_user_id());
				echo json_encode($response);
				break;

			case 'delete_subtask':
				$subtask_id = intval(sanitize_text_field($_POST['subtask_id']));
				$settings = array(
					'id' => $subtask_id
				);
				$wpdb->delete($table_name, $settings, ['%d']);
				$return = array(
					'success' => true
				);
				echo json_encode($return);
				break;

			case 'update_subtask':
				$new_subtask_name = isset($_POST['new_subtask_name']) ? sanitize_text_field($_POST['new_subtask_name']) : '';
				$description = isset($_POST['description']) ? sanitize_text_field($_POST['description']) : '';
				$start = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
				$due = isset($_POST['due_date']) ? sanitize_text_field($_POST['due_date']) : '';
				$subtaskID = intval(sanitize_text_field($_POST['subtask_id']));
				$settings = array(
					'name' => $new_subtask_name,
					'description' => $description,
					'date_due' => $due,
					'date_start' => $start
				);
				$where = array(
					'id' => $subtaskID
				);
				$wpdb->update($table_name, $settings, $where);
				$subtask = Tasks::get_task($subtaskID);
				$response = array(
					'html' => Tasks::subtaskItemHtml($subtask)
				);
				echo json_encode($response);
				break;
			default:
				break;
		}

		die();
	}

	public function get_tasks() {
		Tasks::view_task_list();
		die();
	}

	public function get_task() {
		$user_id = $this->get_user_id();
		$task_id = intval(sanitize_text_field($_POST['task_id']));
		$task_data = Tasks::get_task($task_id);

		$followed_tasks = get_option('zpm_followed_tasks_' . $user_id, false);
		$followed_tasks = unserialize($followed_tasks);
		$following = in_array($task_id, (array) $followed_tasks);

		$task_data->following = $following;
		$task_data->subtasks = Tasks::get_subtasks($task_id);
		$task_data->recurrence = Tasks::recurrence_string($task_data);
		$task_data->attachments = Tasks::get_task_attachments($task_id);
		$task_data = apply_filters('zpm_get_task_data', $task_data);

		echo json_encode($task_data);
		die();
	}

	public function filter_by() {
		$filter = isset($_POST['filter']) ? sanitize_text_field($_POST['filter']) : '-1';
		$current_filter = isset($_POST['current_filter']) ? sanitize_text_field($_POST['current_filter']) : '-1';
		$assignee = isset($_POST['assignee']) ? sanitize_text_field($_POST['assignee']) : '-1';
		$user_id = get_current_user_id();
		$tasks = array();

		switch ($current_filter) {
			case '-1':
				$tasks = Tasks::get_tasks();
				break;
			case '0':
				$tasks = Tasks::get_user_tasks($user_id);
				break;
			case '1':
				$tasks = Tasks::get_completed_tasks(0);
				break;
			case '2':
				$tasks = Tasks::get_completed_tasks(1);
				break;
			default:
				break;
		}

		if ($assignee !== '-1') {
			$filteredTasks = [];

			foreach ($tasks as $task) {
				if (Tasks::is_assignee($task, $user_id)) {
					$filteredTasks[] = $task;
				}
			}

			$tasks = $filteredTasks;
		}

		if (isset($_POST['project'])) {
			$project = intval(sanitize_text_field($_POST['project']));
			$tempTasks = $tasks;
			$tasks = [];

			foreach ($tempTasks as $task) {
				if ($task->project == $project || $project == '-1') {
					$tasks[] = $task;
				}
			}
		}

		update_user_meta($user_id, 'zpm_tasks_last_sorting', $filter);

		$tasks = Tasks::sortTasks($tasks, $filter);

		$html = '';

		$frontend = isset($_POST['frontend']) ? (boolean) $_POST['frontend'] : false;
		foreach ($tasks as $task) {
			$new_row = Tasks::new_task_row($task);
			if (!$frontend) {
				$html .= $new_row;
			} else {
				$html .= '<a href="?action=task&id=' . $task->id . '">' . $new_row . '</a>';
			}
		}

		if (empty($tasks)) {
			$html = '<p class="zpm_error_message">' . __('No results found...', 'zephyr-project-manager') . '</p>';
		}

		$response = array(
			'html' => $html
		);


		echo json_encode($response);
		die();
	}

	public function filter_tasks() {
		$manager = ZephyrProjectManager();
		$filter = sanitize_text_field($_POST['zpm_filter']);
		$user_id = sanitize_text_field($_POST['zpm_user_id']);
		$sorting = $this->getPostVar('sorting', 'date-created');
		$project = $this->getPostVar('project', 'all');
		$assignee = $this->getPostVar('assignee', 'all');

		if ($assignee == 'my_tasks') {
			$assignee = get_current_user_id();
		}

		$tasks = array();
		$hideCompletedTasks = Utillities::getSetting('hide_completed_tasks');

		if ($filter == '-1') {
			// All Tasks
			$tasks = $manager::get_tasks();
		} elseif ($filter == '0') {
			// My Tasks
			$tasks = Tasks::get_user_tasks($user_id);
		} elseif ($filter == '1') {
			// Completed Tasks
			$tasks = Tasks::get_completed_tasks(0);
		} elseif ($filter == '2') {
			// Incompleted Tasks
			$tasks = Tasks::get_completed_tasks(1);
		} elseif ($filter == '3') {
		} elseif ($filter == 'today') {
			$tasks = Tasks::getDueTasks('today');
		} elseif ($filter == '7_days') {
			$tasks = Tasks::getDueTasks('7 days');
		} elseif ($filter == 'overdue') {
			$tasks = Tasks::get_overdue_tasks();
		} elseif ($filter == 'archived') {
			$tasks = Tasks::getArchivedTasks();
		}

		$html = '';
		$frontend = isset($_POST['frontend']) ? (boolean) $_POST['frontend'] : false;
		$tasks = Tasks::sortTasks($tasks, $sorting);

		foreach ($tasks as $task) {
			if ($filter == 0 && $hideCompletedTasks) {
				if (Tasks::isCompleted($task)) continue;
			}

			if (!in_array($assignee, ['-1', 'all'])) {
				if (!Tasks::is_assignee($task, $assignee)) continue;
			} else {
				if ($user_id !== '-1' && !Tasks::is_assignee($task, $user_id)) continue;
			}

			if (!in_array($project, ['-1', 'all']) && !empty($project)) {
				if (intval($task->project) !== intval($project)) continue;
			}

			$new_row = Tasks::new_task_row($task, $frontend, [
				'filter' => $filter,
				'userID' => $user_id,
				'sorting' => $sorting,
				'project' => $project,
				'assignee' => $assignee
			]);

			if (!$frontend) {
				$html .= $new_row;
			} else {
				$html .= '<a href="?action=task&id=' . $task->id . '">' . $new_row . '</a>';
			}
		}

		if (empty($tasks) || empty($html)) {
			$html = '<p class="zpm_error_message">' . __('No results found...', 'zephyr-project-manager') . '</p>';
		}

		$response = array(
			'html' => $html
		);

		echo json_encode($response);
		die();
	}

	public function filter_projects() {
		$filter = sanitize_text_field($_POST['zpm_filter']);
		$user_id = sanitize_text_field($_POST['zpm_user_id']);
		$filter_category = isset($_POST['filter_category']) ? sanitize_text_field($_POST['filter_category']) : false;
		$projects = array();

		if ($filter_category == false) {
			if ($filter == '-1') {
				// All Projects
				$projects = Projects::get_projects();
			} elseif ($filter == '2') {
				// Completed Projects
				$projects = Projects::get_complete_projects();
			} elseif ($filter == '1') {
				// Incompleted projects
				$projects = Projects::get_incomplete_projects();
			} elseif ($filter == 'archived') {
				$projects = Projects::get_projects(null, [
					'archived' => '1'
				]);
			} else {
				$projects = Projects::get_projects();
			}
		} else {
			$projects = Projects::filter_by_category(Projects::get_projects(), $filter_category);
		}

		$html = '';

		foreach ($projects as $project) {
			$html .= Projects::new_project_cell($project);
		}

		if (empty($projects)) {
			$html = '<p class="zpm_error_message">' . __('No projects found...', 'zephyr-project-manager') . '</p>';
		}

		$response = array(
			'html' => $html
		);

		echo json_encode($response);
		die();
	}

	public function create_category() {
		global $wpdb;
		$table_name = ZPM_CATEGORY_TABLE;
		$settings = array();
		$settings['name'] = (isset($_POST['category_name'])) ? sanitize_text_field($_POST['category_name']) : '';
		$settings['description'] = (isset($_POST['category_description'])) ? sanitize_textarea_field($_POST['category_description']) : '';
		$settings['color'] 	= (isset($_POST['category_color'])) ? sanitize_text_field($_POST['category_color']) : false;

		if (ColorPickerApi::checkColor($settings['color']) !== false) {
			$settings['color'] = ColorPickerApi::sanitizeColor($settings['color']);
		} else {
			$settings['color'] = '#eee';
		}

		$wpdb->insert($table_name, $settings);
		Categories::display_category_list();
		die();
	}

	public function remove_category() {
		global $wpdb;
		$table_name = ZPM_CATEGORY_TABLE;
		$settings = array(
			'id' => intval(sanitize_text_field($_POST['id']))
		);

		$wpdb->delete($table_name, $settings, ['%d']);
		Categories::display_category_list();
		die();
	}

	public function update_category() {
		global $wpdb;
		$table_name = ZPM_CATEGORY_TABLE;
		$settings = array();
		$settings['name'] 			= (isset($_POST['category_name'])) ? sanitize_text_field($_POST['category_name']) : '';
		$settings['description'] 	= (isset($_POST['category_description'])) ? sanitize_text_field($_POST['category_description']) : '';
		$settings['color'] 	= (isset($_POST['category_color'])) ? sanitize_text_field($_POST['category_color']) : false;

		if (ColorPickerApi::checkColor($settings['color']) !== false) {
			$settings['color'] = ColorPickerApi::sanitizeColor($settings['color']);
		} else {
			$settings['color'] = '#eee';
		}

		$where = array(
			'id' => intval(sanitize_text_field($_POST['category_id']))
		);

		$wpdb->update($table_name, $settings, $where);
		Categories::display_category_list();
		die();
	}

	public function display_category_list() {
		Categories::display_category_list();
		die();
	}

	public function remove_comment() {
		global $wpdb;
		$table_name = ZPM_MESSAGES_TABLE;
		$commentID = isset($_POST['comment_id']) ? intval(sanitize_text_field($_POST['comment_id'])) : -1;
		$settings = array(
			'id' => $commentID
		);
		$wpdb->delete($table_name, $settings, ['%d']);
		$wpdb->delete($table_name, [
			'parent_id' => $commentID
		], ['%d']);
		die();
	}

	public function display_activities() {
		$all_activities = Activity::get_activities(array('offset' => intval(sanitize_text_field($_POST['offset'])) * 10, 'limit' => 10));
		echo Activity::display_activities($all_activities);
		die();
	}

	public function dismiss_notice() {
		$notice_id = sanitize_text_field($_POST['notice']);

		if ($notice_id == 'review_notice') {
			update_option('zpm_review_notice_dismissed', '1');
		} else if ($notice_id == 'welcome_notice') {
			update_option('zpm_welcome_notice_dismissed', '1');
		} else {
			Utillities::dismiss_notice($notice_id);
		}
	}

	public function update_user_access() {
		$userId = isset($_POST['user_id']) ? sanitize_text_field($_POST['user_id']) : '';
		$access = isset($_POST['access']) && $_POST['access'] == 'true' ? boolval($_POST['access']) : false;

		if (is_array($userId)) {
			foreach ($userId as $user) {
				Utillities::update_user_access($user['id'], $access);
			}
		} else {
			Utillities::update_user_access($userId, $access);
		}

		echo json_encode($_POST);
		die();
	}

	public function getPostVar($var, $default = false) {
		if ($this->hasParam($var)) return $_POST[$var];
		return $default;
	}

	public function getParam($var, $default = false) {
		return $this->getPostVar($var, $default);
	}

	public function hasParam($var) {
		return isset($_POST[$var]);
	}

	public function add_team() {
		$name = sanitize_text_field($_POST['name']);
		$description = sanitize_text_field($_POST['description']);
		$members = zpm_sanitize_array($this->getPostVar('members', []));
		$last_team = Members::add_team($name, $description, $members);
		$team = Members::get_team($last_team);

		$response = array(
			'html' => Members::team_single_html(Members::get_team($last_team)),
			'team' => $team
		);
		echo json_encode($response);
		die();
	}

	public function update_team() {
		$id = zpm_sanitize_int($_POST['id']);
		$name = sanitize_text_field($_POST['name']);
		$description = sanitize_text_field($_POST['description']);
		$members = zpm_sanitize_array($_POST['members']);
		Members::update_team($id, $name, $description, $members);
		echo json_encode(Members::team_single_html(Members::get_team($id)));
		die();
	}

	public function get_team() {
		$id = zpm_sanitize_int($_POST['id']);
		echo json_encode(Members::get_team($id));
		die();
	}

	public function delete_team() {
		$id = zpm_sanitize_int($_POST['id']);
		Members::delete_team($id);
		die();
	}

	public function get_all_tasks() {
		$tasks = Tasks::getAvailableTasks();

		foreach ($tasks as $task) {
			$project = Projects::get_project($task->project);
			$task->project_data = $project;
			$assignee = Members::get_member($task->assignee);
			$assignees = Tasks::get_assignees($task, true);
			$priority = property_exists($task, 'priority') ? $task->priority : 'priority_none';
			$status = Utillities::get_status($priority);
			$task->status = $priority;
			$task->assignees = $assignees;

			$categories = is_object($project) ? maybe_unserialize($task->project_data->categories) : [];
			$category = isset($categories[0]) ? Categories::get_category($categories[0]) : '-1';
			$color = is_object($category) ? $category->color : '';
			$task->styles = Utillities::auto_gradient_css($color, true);
			$task->type = Tasks::get_type($task);

			if ($task->type == 'daily') {
				$startToEnd = Tasks::getStartToEndDays($task);
				$task->otherDays = [];

				if ($startToEnd > 0) {
					for ($i = 0; $i < $startToEnd; $i++) {
						$newDate = date('Y-m-d', strtotime($task->date_start . ' + ' . $i . ' days'));
						$task->otherDays[] = $newDate;
					}
				}
			} elseif ($task->type == 'weekly') {
				$task->expires = Tasks::get_expiration_date($task);
				$startToEnd = !empty($task->expires) ? Tasks::getStartToEndDays($task, true) : 200;
				$task->diffDays = $startToEnd;
				$task->otherDays = [];
				if ($startToEnd > 0) {
					for ($i = 0; $i < $startToEnd; $i++) {
						$newDate = date('Y-m-d', strtotime($task->date_start . ' + ' . $i . ' days'));
						$day = date('D', strtotime($task->date_start . ' + ' . $i . ' days'));
						//$task->otherDays[] = $day;
						if ($day === 'Mon') {
							$task->otherDays[] = $newDate;
						}
					}
				}
			}
		}

		echo json_encode((array)$tasks);
		die();
	}

	public function getTasksDateRange() {
		$options = zpm_sanitize_array($_POST['options']);
		$rangeStart = isset($options['start']) ? sanitize_text_field($options['start']) : '';
		$rangeEnd = isset($options['end']) ? sanitize_text_field($options['end']) : '';
		$results = Tasks::getTasksDateRange($rangeStart, $rangeEnd);
		echo json_encode($results);
		die();
	}

	public function get_project_tasks() {
		$projectId = isset($_POST['project_id']) ? zpm_sanitize_int($_POST['project_id']) : -1;
		$tasks =  Tasks::get_project_tasks($projectId, true);
		$userID = get_current_user_id();

		foreach ($tasks as $task) {
			$task->hasApplied = Tasks::isApplied($task);
			$task->canComplete = apply_filters('zpm_can_complete_task', true, $task);
			$task->isAssignee = Tasks::is_assignee($task, $userID);
			$task->frontendUrl = Utillities::get_frontend_url('action=task&id=' . $task->id);
		}
		echo json_encode($tasks);
		die();
	}

	public function create_status() {
		$name = sanitize_text_field($_POST['name']);
		$color = sanitize_text_field($_POST['color']);
		$type = sanitize_text_field($_POST['type']);
		$status = Utillities::create_status($name, $color, $type);
		$type = empty($type) ? 'priority' : $type;
		ob_start();
		?>
			<div class="zpm-<?php echo esc_attr($type); ?>-list__item" data-status-slug="<?php echo esc_attr($status['slug']); ?>">
				<span class="zpm-<?php echo esc_attr($type); ?>-list__item-color" style="background: <?php echo esc_attr($status['color']); ?>"></span>
				<span class="zpm-<?php echo esc_attr($type); ?>-list__item-name"><?php echo esc_html($status['name']); ?></span>
				<span class="zpm-delete-<?php echo esc_attr($type); ?> lnr lnr-cross" data-id="<?php echo esc_attr($status['slug']); ?>"></span>
			</div>
		<?php
		$html = ob_get_clean();
		echo json_encode(array('result' => 'success', 'html' => $html));
		die();
	}

	public function update_status() {
		$name = sanitize_text_field($_POST['name']);
		$color = sanitize_text_field($_POST['color']);
		$slug = sanitize_text_field($_POST['slug']);
		$type = sanitize_text_field($_POST['type']);
		$status = Utillities::update_status($slug, $name, $color, $type);
		ob_start();

		?>
			<div class="zpm-status-list__item" data-<?php echo esc_attr($type); ?>-slug="<?php echo esc_attr($status['slug']); ?>">
				<span class="zpm-<?php echo esc_attr($type); ?>-list__item-color" style="background: <?php echo esc_attr($status['color']); ?>"></span>
				<span class="zpm-<?php echo esc_attr($type); ?>-list__item-name"><?php echo esc_html($status['name']); ?></span>
				<span class="zpm-delete-<?php echo esc_attr($type); ?> lnr lnr-cross" data-id="<?php echo esc_attr($status['slug']); ?>"></span>
			</div>
		<?php
		$html = ob_get_clean();
		echo json_encode(array('result' => 'success', 'html' => $html));
		die();
	}

	public function delete_status() {
		$slug = isset($_POST['slug']) ? sanitize_text_field($_POST['slug']) : '';
		$type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'priority';
		$status = Utillities::delete_status($slug, $type);
		echo json_encode(array(
			'result' => 'success',
			'status' => $status
		));
		die();
	}

	public function get_user_by_unique_id() {
		$response = array();
		$user = Members::get_user_by_meta_data('_zpm_unique_id', zpm_sanitize_int($_POST['id']));
		$response = Utillities::get_user($user->ID);
		echo json_encode($response);
		die();
	}

	public function complete_project() {
		$id = isset($_POST['id']) ? zpm_sanitize_int($_POST['id']) : -1;
		$completed = isset($_POST['completed']) ? zpm_sanitize_bool($_POST['completed']) : 0;
		Projects::mark_complete($id, $completed);
		echo json_encode(array());
		die();
	}

	public function view_project() {
		$project_id = isset($_POST['project_id']) ? zpm_sanitize_int($_POST['project_id']) : -1;
		ob_start();
		Projects::view_project_modal($project_id);
		$html = ob_get_clean();
		echo json_encode(array(
			'html' => $html
		));
		die();
	}

	public function get_user_projects() {
		$user_id = isset($_POST['user_id']) ? zpm_sanitize_int($_POST['user_id']) : -1;
		$projects = Projects::get_member_projects($user_id);
		echo json_encode(array(
			'projects' => $projects,
			'project_count' => sizeof($projects)
		));
		die();
	}

	public function team_members_list_html() {
		$base = new BaseController;
		$users = $base->get_users();

		ob_start();

		foreach ($users as $user) : ?>
			<?php
				$role = '';
				$user_id = $user->data->ID;
				$user_settings_option = get_option('zpm_user_' . $user->data->ID . '_settings');
				$avatar = isset($user_settings_option['profile_picture']) ? $user_settings_option['profile_picture'] : get_avatar_url($user->data->ID);
				$can_zephyr = isset($user_settings_option['can_zephyr']) ? $user_settings_option['can_zephyr'] : "true";
				$description = isset($user_settings_option['description']) ? $user_settings_option['description'] : '';
				$user_projects = Projects::get_user_projects($user->data->ID);
				$user_tasks = Tasks::get_user_tasks($user->data->ID);
				$completed_tasks = Tasks::get_user_completed_tasks($user->data->ID);
				$remaining_tasks = Tasks::get_user_completed_tasks($user->data->ID, '0');

				$percent_complete = (sizeof($user_tasks) !== 0) ? (sizeof($completed_tasks) / sizeof($user_tasks)) * 100 : '0';

				if (in_array('zpm_user', $user->roles)) {
					$role = 'ZPM User';
				} elseif (in_array('zpm_client_user', $user->roles)) {
					$role = 'ZPM Client User';
				} elseif (in_array('zpm_manager', $user->roles) || in_array('administrator', $user->roles)) {
					$role = 'ZPM Manager';
				}
				?>

				<?php $edit_url = admin_url('/admin.php?page=zephyr_project_manager_teams_members') . '&action=edit_member&user_id=' . $user->data->ID; ?>

				<a class="zpm_team_member <?php echo $can_zephyr == "true" ? 'zpm-user-can-zephyr' : ''; ?>" <?php echo current_user_can('administrator') ? "href='" . esc_url($edit_url) . "'" : ''; ?>>
					<div class="zpm_member_details" data-ripple="rgba(0,0,0,0.1)">

						<span class="zpm_avatar_image" style="background-image: url(<?php echo esc_url($avatar); ?>);"></span>
						<span class="zpm_member_name"><?php echo esc_html($user->data->display_name); ?></span>
						<span class="zpm_member_email"><?php echo esc_html($user->data->user_email); ?></span>
						<p class="zpm_member_bio"><?php echo wp_kses_post($description); ?></p>

						<?php if (current_user_can('administrator')) : ?>
							<!-- Adcurrent_user_can('administrator')min Controls -->
							<div class="zpm-access-controls">
								<label for="zpm-can-zephyr-<?php echo esc_attr($user_id); ?>" class="zpm_checkbox_label">
									<input type="checkbox" id="zpm-can-zephyr-<?php echo esc_attr($user_id); ?>" name="zpm_can_zephyr" class="zpm-can-zephyr zpm_toggle invisible" value="1" data-user-id="<?php echo esc_attr($user->data->ID); ?>" <?php echo $can_zephyr == "true" ? 'checked' : ''; ?>>

									<div class="zpm_main_checkbox">
										<svg width="20px" height="20px" viewBox="0 0 20 20">
											<path d="M3,1 L17,1 L17,1 C18.1045695,1 19,1.8954305 19,3 L19,17 L19,17 C19,18.1045695 18.1045695,19 17,19 L3,19 L3,19 C1.8954305,19 1,18.1045695 1,17 L1,3 L1,3 C1,1.8954305 1.8954305,1 3,1 Z"></path>
											<polyline points="4 11 8 15 16 6"></polyline>
										</svg>
									</div>
									<?php _e('Allow Access', 'zephyr-project-manager'); ?>
								</label>
							</div>
						<?php endif; ?>

						<div class="zpm_member_stats">
							<div class="zpm_member_stat">
								<h5 class="zpm_member_stat_number"><?php echo esc_html(sizeof($user_projects)); ?></h5>
								<p class="zpm_member_stat_label"><?php _e('Projects', 'zephyr-project-manager'); ?></p>
							</div>
							<div class="zpm_member_stat">
								<h5 class="zpm_member_stat_number"><?php echo esc_html(sizeof($completed_tasks)); ?></h5>
								<p class="zpm_member_stat_label"><?php _e('Completed Tasks', 'zephyr-project-manager'); ?></p>
							</div>
							<div class="zpm_member_stat">
								<h5 class="zpm_member_stat_number"><?php echo esc_html(sizeof($remaining_tasks)); ?></h5>
								<p class="zpm_member_stat_label"><?php _e('Remaining Tasks', 'zephyr-project-manager'); ?></p>
							</div>
							<div class="zpm_member_progress">
								<span class="zpm_member_progress_bar" style="width: <?php echo esc_attr($percent_complete); ?>%"></span>
							</div>
						</div>
					</div>
				</a>
			<?php endforeach; ?>
		<?php

		$html = ob_get_clean();
		echo json_encode(array(
			'html' => $html
		));
		die();
	}

	public function get_user_progress() {
		$user_id = isset($_POST['user_id']) ? zpm_sanitize_int($_POST['user_id']) : -1;
		$project_id = isset($_POST['project_id']) ? zpm_sanitize_int($_POST['project_id']) : -1;
		$user_completed_tasks = [];
		$user_pending_tasks = [];
		$user_tasks = Tasks::get_project_assignee_tasks($project_id, $user_id);

		foreach ($user_tasks as $task) {
			if ($task->completed == '1') {
				$user_completed_tasks[] = $task;
			} else {
				$user_pending_tasks[] = $task;
			}
		}

		$args = array(
			'project_id' => $project_id,
			'assignee' => $user_id
		);
		$overdue_tasks = Tasks::get_overdue_tasks($args);

		$percent_complete = (sizeof($user_tasks) !== 0) ? (sizeof($user_completed_tasks) / sizeof($user_tasks)) * 100 : '0';
		$total = sizeof($user_tasks);

		ob_start(); ?>

		<div class="zpm-member-progress__stats">
			<div class="zpm-member-progress__stat"><?php _e('Tasks', 'zephyr-project-manager'); ?>: <span class="zpm-stat-val"><?php echo esc_html($total); ?></span></div>
			<div class="zpm-member-progress__stat zpm-member-stat__completed"><?php _e('Completed Tasks', 'zephyr-project-manager'); ?>: <span class="zpm-stat-val"><?php echo esc_html(sizeof($user_completed_tasks)); ?></span></div>
			<div class="zpm-member-progress__stat zpm-member-stat__pending"><?php _e('Pending Tasks', 'zephyr-project-manager'); ?>: <span class="zpm-stat-val"><?php echo esc_html(sizeof($user_pending_tasks)); ?></span></div>
			<div class="zpm-member-progress__stat zpm-member-stat__percentage"><?php _e('Percentage Complete', 'zephyr-project-manager'); ?>: <span class="zpm-stat-val"><?php echo esc_html(round($percent_complete)) . '%'; ?></span></div>
		</div>

		<?php $html = ob_get_clean();

		$results = [
			'tasks' => $user_tasks,
			'tasks_total' => $total,
			'pending_tasks' => $user_pending_tasks,
			'completed_tasks' => $user_completed_tasks,
			'percent_complete' => $percent_complete,
			'overdue_tasks' => $overdue_tasks,
			'completed' => sizeof($user_completed_tasks),
			'pending' => sizeof($user_pending_tasks),
			'overdue' => sizeof($overdue_tasks),
			'html' => $html
		];

		echo json_encode($results);
		die();
	}

	public function get_project_members() {
		$project_id = isset($_POST['project_id']) ? zpm_sanitize_int($_POST['project_id']) : -1;
		$members = Projects::get_members($project_id);
		echo json_encode($members);
		die();
	}

	public function get_members() {
		$page = isset($_POST['page']) ? zpm_sanitize_int($_POST['page']) : '';
		$limit = isset($_POST['limit']) ? zpm_sanitize_int($_POST['limit']) : '';

		if (!empty($page) && !empty($limit)) {
			$members = Members::get_members($limit, $page);
		} else {
			$members = Members::get_members();
		}

		foreach ($members as $key => $member) {
			$members[$key]['list_html'] = Members::list_html($member);
		}

		echo json_encode($members);
		die();
	}

	public function get_paginated_projects() {
		$page = 1;

		if (!empty($_POST['page'])) {
			$page = zpm_sanitize_int($_POST['page']);
		}

		$sortingMethod = isset($_POST['sortingMethod']) ? sanitize_text_field($_POST['sortingMethod']) : 'date_created';
		$limit = isset($_POST['limit']) ? zpm_sanitize_int($_POST['limit']) : 10;
		$offset = ($page - 1) * $limit;

		if ($offset < 0) {
			$offset = 0;
		}

		$projects = Projects::get_paginated_projects($limit, $offset);
		$projects = Projects::sort($projects, $sortingMethod);
		update_user_meta(get_current_user_id(), 'zpm/projects/last_sorting_method', $sortingMethod);

		ob_start();

		foreach ($projects as $project) {
			echo Projects::new_project_cell($project);
		}

		$html = ob_get_clean();

		$response = [
			'projects' => $projects,
			'html' => $html
		];
		echo json_encode($response);
		die();
	}

	public function get_available_project_count() {
		//$projects = Projects::get_available_projects();
		$count = Projects::get_total_pages();
		$response = [
			'count' => $count
		];
		echo json_encode($response);
		die();
	}

	public function update_user_meta() {
		$user_id = isset($_POST['user_id']) ? zpm_sanitize_int($_POST['user_id']) : get_current_user_id();
		$meta_key = isset($_POST['key']) ? sanitize_text_field($_POST['key']) : '';
		$meta_value = isset($_POST['value']) ? sanitize_text_field($_POST['value']) : '';
		update_user_meta($user_id, $meta_key, $meta_value);
		$response = [];
		echo json_encode($response);
		die();
	}

	public function getUserData() {
		$userData = [];
		$manager = ZephyrProjectManager();
		$users = $manager::get_users();

		foreach ($users as $user) {
			$userData[] = [
				'id' => $user['id'],
				'name' => $user['name'],
				'avatar' => $user['avatar'],
				'type' => 'user'
			];
		}

		echo json_encode($userData);
		die();
	}

	public function updateTaskDueDate() {
		$taskId = isset($_POST['task_id']) ? zpm_sanitize_int($_POST['task_id']) : -1;
		$dueDate = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
		$dateTime = new DateTime($dueDate);
		$data = array(
			'date_due' => $dateTime->format('Y-m-d H:i:s')
		);
		Tasks::update($taskId, $data);
		echo json_encode(array(
			'formatted_date' => $dateTime->format('d M'),
			'data' => $data,
			'task_id' => $taskId
		));
		die();
	}

	public function updateFileProject() {
		global $wpdb;
		$tableName = ZPM_MESSAGES_TABLE;
		$fileId = isset($_POST['file_id']) ? zpm_sanitize_int($_POST['file_id']) : -1;
		$projectID = isset($_POST['project_id']) ? zpm_sanitize_int($_POST['project_id']) : -1;
		$settings = array(
			'subject_id' => $projectID
		);
		$where = array(
			'id' => $fileId
		);
		$wpdb->update($tableName, $settings, $where);
		echo json_encode(['success']);
		die();
	}

	public function newTaskModal() {
		ob_start();
		$isShortcode = true;
		require_once(ZPM_PLUGIN_PATH . '/templates/parts/new_task.php');
		$html = ob_get_clean();
		echo json_encode([
			'html' => $html
		]);
		die();
	}

	public function editTaskModal() {
		ob_start();
		$taskId = isset($_POST['id']) ? zpm_sanitize_int($_POST['id']) : -1;
		require_once(ZPM_PLUGIN_PATH . '/templates/parts/task-edit-modal.php');
		$html = ob_get_clean();
		echo json_encode([
			'html' => $html
		]);
		die();
	}

	public function newProjectModal() {
		ob_start();
		require_once(ZPM_PLUGIN_PATH . '/templates/parts/new-project-modal.php');
		$html = ob_get_clean();
		echo json_encode([
			'html' => $html
		]);
		die();
	}

	public function editProjectModal() {
		ob_start();
		$projectId = isset($_POST['id']) ? zpm_sanitize_int($_POST['id']) : -1;
		require_once(ZPM_PLUGIN_PATH . '/templates/parts/project-edit-modal.php');
		$html = ob_get_clean();
		echo json_encode([
			'html' => $html
		]);
		die();
	}

	public function subtaskEditModal() {
		$id = isset($_POST['id']) ? zpm_sanitize_int($_POST['id']) : -1;
		$subtask = new Task($id);
		ob_start();
		?>
			<h5 class="zpm-modal-header"><?php _e('Edit Subtask', 'zephyr-project-manager'); ?></h5>

			<input type="hidden" data-ajax-name="parent-id" value="<?php echo esc_attr($subtask->parentId); ?>" />
			<div class="zpm-form__group">
				<input type="text" name="zpm-edit-subtask__name" id="zpm-edit-subtask__name" class="zpm-form__field" placeholder="<?php _e('Subtask Name', 'zephyr-project-manager'); ?>" value="<?php echo esc_html($subtask->name); ?>" data-ajax-name="name" />
				<label for="zpm-edit-subtask__name" class="zpm-form__label"><?php _e('Subtask Name', 'zephyr-project-manager'); ?></label>
			</div>
			<div class="zpm-form__group">
				<textarea type="text" name="zpm-edit-subtask__description" id="zpm-edit-subtask__description" class="zpm-form__field" placeholder="<?php _e('Subtask Description', 'zephyr-project-manager'); ?>" data-ajax-name="description"><?php echo wp_kses_post($subtask->description); ?></textarea>
				<label for="zpm-edit-subtask__description" class="zpm-form__label"><?php _e('Subtask Description', 'zephyr-project-manager'); ?></label>
			</div>

			<div class="zpm-row">
				<div class="zpm-col zpm-col-6">
					<div class="zpm-form__group">
						<input type="text" name="zpm-edit-subtask__start" id="zpm-edit-subtask__start" class="zpm-form__field zpm-datepicker" placeholder="<?php _e('Start', 'zephyr-project-manager'); ?>" value="<?php $subtask->getStartDate('Y-m-d'); ?>" data-ajax-name="start-date" />
						<label for="zpm-edit-subtask__start" class="zpm-form__label"><?php _e('Start', 'zephyr-project-manager'); ?></label>
					</div>
				</div>
				<div class="zpm-col zpm-col-6">
					<div class="zpm-form__group">
						<input type="text" name="zpm-edit-subtask__due" id="zpm-edit-subtask__due" class="zpm-form__field zpm-datepicker" placeholder="<?php _e('Due', 'zephyr-project-manager'); ?>" value="<?php echo esc_attr($subtask->getDueDate('Y-m-d')); ?>" data-ajax-name="due-date" />
						<label for="zpm-edit-subtask__due" class="zpm-form__label"><?php _e('Due', 'zephyr-project-manager'); ?></label>
					</div>
				</div>
			</div>

			<div class="zpm-modal-buttons__right">
				<div class="zpm-modal-cancel-btn zpm_button" data-zpm-trigger="remove_modal">Cancel</div>
				<div class="zpm-modal-accept-btn zpm_button" data-zpm-trigger="remove_modal">Create</div>
			</div>
		<?php
		$html = ob_get_clean();
		echo $html;
		die();
	}

	public function getCalendarItems() {
		$tasks = Tasks::get_tasks();
		$tasks = apply_filters('zpm_filter_global_tasks', $tasks);
		$generalSettings = Utillities::general_settings();

		foreach ($tasks as $key => $task) {
			if (!Utillities::can_view_task($task)) {
				unset($tasks[$key]);
				continue;
			}

			$project = Projects::get_project($task->project);
			$task->project_data = $project;
			$assignee = Members::get_member($task->assignee);
			$assignees = Tasks::get_assignees($task, true);
			$priority = property_exists($task, 'priority') ? $task->priority : 'priority_none';
			$status = Utillities::get_status($priority);
			$taskStatus = Utillities::get_status($task->status);
			$task->taskStatus = $task->status;
			$task->status = $priority;
			$task->assignees = $assignees;
			$categories = is_object($project) ? maybe_unserialize($task->project_data->categories) : [];
			$category = isset($categories[0]) ? Categories::get_category($categories[0]) : '-1';
			$color = is_object($category) ? $category->color : '';

			if (!zpm_is_date_valid($task->date_start)) {
				$task->date_start = $task->date_due;
			}

			if (!zpm_is_date_valid($task->date_due)) {
				$task->date_due = $task->date_start;
			}

			if ($color == '#eee') {
				$color = $generalSettings['primary_color'];
			}

			if ($generalSettings['calendar_task_colors'] == 'status') {
				$color = $taskStatus['color'];
			} else if ($generalSettings['calendar_task_colors'] == 'priority') {
				$color = $status['color'];
			}

			$task->styles = Utillities::auto_gradient_css($color, true);
			$task->type = Tasks::get_type($task);
			$isDueDateValid = zpm_is_date_valid($task->date_due);

			if ($task->type !== 'default' && $task->type !== 'normal') {
				$frequency = Tasks::getFrequency($task);
			}

			if ($task->type == 'daily') {
				$task->expires = Tasks::get_expiration_date($task);
				$startToEnd = !empty($task->expires) ? Tasks::getStartToEndDays($task, true) : 200 * $frequency;
				$task->otherDays = [];

				if ($startToEnd > 0) {
					for ($i = 0; $i < $startToEnd; $i++) {
						$newDate = date('Y-m-d', strtotime($task->date_start . ' + ' . $i * $frequency . ' days'));
						$dueDate = $newDate;

						if ($isDueDateValid) {
							$dueDate = date('Y-m-d', strtotime($task->date_due . ' + ' . $i * $frequency . ' days'));
						}

						$task->otherDays[] = [
							'start' => $newDate,
							'due' => $dueDate
						];
					}
				}
			} elseif ($task->type == 'weekly') {
				$task->expires = Tasks::get_expiration_date($task);
				$startToEnd = !empty($task->expires) ? Tasks::getStartToEndDays($task, true) : 200;
				$task->diffDays = $startToEnd;
				$task->otherDays = [];

				if ($startToEnd > 0) {
					for ($i = 0; $i < $startToEnd; $i++) {
						$newDate = date('Y-m-d', strtotime($task->date_start . ' + ' . $i * $frequency . ' days'));
						$day = date('D', strtotime($task->date_start . ' + ' . $i * $frequency . ' days'));
						$dueDate = $newDate;

						if ($isDueDateValid) {
							$dueDate = date('Y-m-d', strtotime($task->date_due . ' + ' . $i * $frequency . ' days'));
						}
						//$task->otherDays[] = $day;
						if ($day === 'Mon') {
							$task->otherDays[] = [
								'start' => $newDate,
								'due' => $dueDate
							];
						}
					}
				}
			} elseif ($task->type == 'monthly') {
				$task->expires = Tasks::get_expiration_date($task);
				$startToEnd = !empty($task->expires) ? Tasks::getStartToEndMonths($task, true) : 200;
				$task->diffDays = $startToEnd;
				$task->otherDays = [];

				if ($startToEnd > 0) {
					for ($i = 0; $i < $startToEnd; $i++) {
						$newDate = date('Y-m-d', strtotime($task->date_start . ' + ' . $i * $frequency . ' months'));
						$day = date('D', strtotime($task->date_start . ' + ' . $i * $frequency . ' months'));
						$dueDate = $newDate;

						if ($isDueDateValid) {
							$dueDate = date('Y-m-d', strtotime($task->date_due . ' + ' . $i * $frequency . ' months'));
						}

						$task->otherDays[] = [
							'start' => $newDate,
							'due' => $dueDate
						];
						// //$task->otherDays[] = $day;
						// if ($day === 'Mon') {
						// }
					}
				}
			} elseif ($task->type == 'annually') {
				$task->expires = Tasks::get_expiration_date($task);
				$startToEnd = !empty($task->expires) ? Tasks::getStartToEndYears($task, true) : 10;
				$task->diffDays = $startToEnd;
				$task->otherDays = [];

				if ($startToEnd > 0) {
					for ($i = 0; $i < $startToEnd; $i++) {
						$newDate = date('Y-m-d', strtotime($task->date_start . ' + ' . ($i * 12) * $frequency . ' months'));
						$day = date('D', strtotime($task->date_start . ' + ' . ($i * 12) * $frequency . ' months'));
						$dueDate = $newDate;

						if ($isDueDateValid) {
							$dueDate = date('Y-m-d', strtotime($task->date_due . ' + ' . ($i * 12) * $frequency . ' months'));
						}

						$task->otherDays[] = [
							'start' => $newDate,
							'due' => $dueDate
						];
						// //$task->otherDays[] = $day;
						// if ($day === 'Mon') {
						// }
					}
				}
			}
		}

		$calendarItems = apply_filters('zpm_calendar_items', $tasks);
		echo json_encode($calendarItems);
		die();
	}

	public function updateMessage() {
		global $wpdb;
		$table_name = ZPM_MESSAGES_TABLE;

		$id = isset($_POST['message_id']) ? zpm_sanitize_int($_POST['message_id']) : -1;
		$message = isset($_POST['message']) ? sanitize_text_field($_POST['message']) : '';
		$where = [
			'id' => $id
		];
		$args = [
			'message' => serialize($message)
		];
		$wpdb->update($table_name, $args, $where);

		echo json_encode([]);
		die();
	}

	public function uploadAjaxFile() {
		$posted_data = isset($_POST) ? $_POST : array();
		$file_data = isset($_FILES) ? $_FILES : array();
		$data = array_merge($posted_data, $file_data);
		$response = array();
		$uploaded_file = wp_handle_upload($data['file'], array('test_form' => false));
		$response['uploaded_file'] = $uploaded_file;
		if ($uploaded_file && !isset($uploaded_file['error'])) {
			$response['response'] = "SUCCESS";
			$response['filename'] = basename($uploaded_file['url']);
			$response['url'] = $uploaded_file['url'];
			$response['type'] = $uploaded_file['type'];
		} else {
			$response['response'] = "ERROR";
			$response['error'] = $uploaded_file['error'];
		}

		echo json_encode($response);
		die();
	}

	public function getMembers() {
		$args = isset($_POST['args']) ? zpm_sanitize_array($_POST['args']) : [];
		$role = isset($args['role']) ? sanitize_text_field($args['role']) : false;

		$results = [];

		if (current_user_can('administrator')) {
			$members = Members::get_members();

			foreach ($members as $member) {
				if ($role) {
					if (user_can($member['id'], $role)) {
						$results[] = $member;
					}
					continue;
				}
				$results[] = $member;
			}
		}

		echo json_encode($results);
		wp_die();
	}

	public function sendEmail() {
		$header = isset($_POST['header']) ? sanitize_text_field($_POST['header']) : '';
		$subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
		$body = isset($_POST['body']) ? sanitize_text_field($_POST['body']) : '';
		$footer = isset($_POST['footer']) ? sanitize_text_field($_POST['footer']) : '';
		$userId = isset($_POST['user_id']) ? sanitize_text_field($_POST['user_id']) : '';
		$emails = isset($_POST['emails']) ? zpm_sanitize_array($_POST['emails']) : [];

		$html = Emails::email_template($header, $body, $footer);

		if (!empty($emails)) {
			foreach ($emails as $email) {
				Emails::send_email($email, $subject, $html);
			}
		} else {
			$member = Members::get_member($userId);
			Emails::send_email($member['email'], $subject, $html);
		}

		echo json_encode([]);
		wp_die();
	}

	public function updateProjectSetting() {
		$projectId = isset($_POST['project_id']) ? zpm_sanitize_int($_POST['project_id']) : -1;
		$key = isset($_POST['key']) ? sanitize_text_field($_POST['key']) : '';
		$value = isset($_POST['value']) ? sanitize_text_field($_POST['value']) : '';
		Projects::updateSetting($projectId, $key, $value);
		echo json_encode([]);
		wp_die();
	}

	public function loadProjectsFromCSV() {
		$file = isset($_POST['file']) ? sanitize_text_field($_POST['file']) : '';
		$projects = Projects::loadFromCSV($file);
		echo json_encode($projects);
		wp_die();
	}

	public function loadProjectsFromJSON() {
		$file = isset($_POST['file']) ? sanitize_text_field($_POST['file']) : '';
		$projects = Projects::loadFromJSON($file);
		echo json_encode($projects);
		wp_die();
	}

	public function saveProjects() {
		$projects = isset($_POST['projects']) ? zpm_sanitize_array($_POST['projects']) : [];

		foreach ($projects as $project) {
			$project = (array) $project;
			$userID = isset($project['user_id']) ? $project['user_id'] : '';
			$name = isset($project['name']) ? $project['name'] : '';
			$description = isset($project['description']) ? $project['description'] : '';
			$team = isset($project['team']) ? $project['team'] : '';
			$categories = isset($project['categories']) ? serialize((array)$project['categories']) : '';
			$completed = isset($project['completed']) ? $project['completed'] : '';
			$completed = !empty($completed) && $completed !== '0' ? '1' : $completed;

			$dueDate = isset($project['date_due']) ? $project['date_due'] : '';
			if (empty($dueDate)) {
				$dueDate = isset($project['due_date']) ? $project['due_date'] : $dueDate;
			}

			$startDate = isset($project['date_start']) ? $project['date_start'] : '';
			if (empty($dueDate)) {
				$startDate = isset($project['start_date']) ? $project['start_date'] : $startDate;
			}

			$args = array(
				'user_id'        => $userID,
				'name'           => $name,
				'description'    => $description,
				'team'           => $team,
				'categories'     => $categories,
				'completed'      => $completed,
				'date_start'     => $startDate,
				'date_due'       => $dueDate,
				'date_created'   => date('Y-m-d H:i:s'),
				'date_completed' => '',
				'priority'       => 'priority_none'
			);

			$args = apply_filters('zpm_project_import_row', $args, $project);
			$projectId = Projects::new_project($args);

			if (isset($project['id']) && !empty($project['id'])) {
				if (is_numeric($project['id'])) {
					update_option('zpm_import_id_' . $project['id'], $projectId);
				}
			}

			if (isset($project['tasks'])) {
				foreach ($project['tasks'] as $task) {
					$task = (array) $task;
					$args = array(
						'user_id'        => '',
						'parent_id'      => (int) $task['parent_id'],
						'assignee'       => (int) $task['assignee'],
						'project'      	 => $projectId,
						'name'           => $task['name'],
						'description'    => $task['description'],
						'date_start'     => $task['date_start'],
						'date_due'       => $task['date_due'],
						'date_created'   => date('Y-m-d H:i:s'),
						'date_completed' => '',
						'categories'     => $task['categories'],
						'completed'      => $task['completed'],
						'priority'       => 'priority_none'
					);
					Tasks::create($args);
				}
			}
		}

		echo json_encode($projects);
		wp_die();
	}

	public function saveTasks() {
		$tasks = isset($_POST['tasks']) ? zpm_sanitize_array($_POST['tasks']) : [];

		foreach ($tasks as $task) {
			$task = (array) $task;
			$args = [
				'user_id'        => -1,
				'parent_id'      => isset($task['parent_id']) && !empty($task['parent_id']) ? (int) $task['parent_id'] : -1,
				'assignee'       => isset($task['assignee']) && !empty($task['assignee']) ? (int) $task['assignee'] : -1,
				'name'           => isset($task['name']) && !empty($task['name']) ? $task['name'] : __('Untitled', 'zephyr-project-manager'),
				'description'    => isset($task['description']) ? $task['description'] : '',
				'date_start'     => isset($task['start_date']) && !empty($task['start_date']) ? date('Y-m-d H:i:s', strtotime($task['start_date'])) : '',
				'date_due'       => isset($task['due_date']) && !empty($task['due_date']) ? date('Y-m-d H:i:s', strtotime($task['due_date'])) : '',
				'date_created'   => isset($task['date_created']) && !empty($task['date_created']) ? date('Y-m-d H:i:s', strtotime($task['date_created'])) : date('Y-m-d H:i:s'),
				'date_completed' => isset($task['date_completed']) && !empty($task['date_completed']) ? date('Y-m-d H:i:s', strtotime($task['date_completed'])) : '',
				'categories'     => serialize(explode(',', $task['categories'])),
				'completed'      => isset($task['completed']) && !empty($task['completed']) ? $task['completed'] : '0',
				'priority'       => isset($task['priority']) && !empty($task['priority']) ? str_replace(' ', '_', strtolower($task['priority'])) : 'priority_none',
				'status'       	 => isset($task['status']) && !empty($task['status']) ? str_replace(' ', '_', strtolower($task['status'])) : '',
			];

			if (isset($task['project'])) {
				if (is_numeric($task['project'])) {
					// [ADD TO UPDATE]
					$args['project'] = (int) get_option('zpm_import_id_' . $task['project'], $task['project']);
					//$args['project'] = (int) $task['project'];
				} else {
					$project = Projects::getProjectByName($task['project']);

					if (!is_null($project)) {
						$args['project'] = $project->id;
					} else {
						$args['project'] = -1;
					}
				}
			} else {
				$args['project'] = -1;
			}

			if (empty($args['project'])) {
				$args['project'] = -1;
			}

			if (empty($args['date_due'])) {
				$args['date_due'] = '0000-00-00 00:00:00';
			}

			if (empty($args['date_start'])) {
				$args['date_start'] = '0000-00-00 00:00:00';
			}

			if (isset($task['team'])) {
				if (!is_numeric($task['team'])) {
					$team = Members::getTeamByName($task['team']);
					if (!is_null($team)) {
						$task['team'] = $team['id'];
					}
				} else {
					$args['team'] = $task['team'];
				}
			}
			$args = apply_filters('zpm/task/import', $args, $task);
			$taskID = Tasks::create($args);
			$newTask = Tasks::get_task($taskID);
			do_action('zpm_task_imported', $newTask, $task);
		}

		echo json_encode($args);
		wp_die();
	}

	public function exportProjectsToCSV() {
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename="project_tasks.csv"');
		header('Pragma: no-cache');
		header('Expires: 0');
		$projects = Projects::get_projects();
		$upload_dir = wp_upload_dir();
		$filename = $upload_dir['basedir'] . '/ZPM Projects.csv';
		$filename = fopen($filename, 'w+');
		$headers = apply_filters('zpm/projects/export/headers', ['ID', 'User ID', 'Name', 'Description', 'Completed', 'Assignees', 'Categories', 'Date Created', 'Date Due', 'Date Start', 'Date Completed']);
		fputcsv($filename, $headers);

		foreach ($projects as $project) {
			$completed = Projects::isCompleted($project);

			if ($completed) {
				$completed = 'Yes';
			} else {
				$completed = 'No';
			}

			$filedata = [
				'id' => $project->id,
				'user_id' => $project->user_id,
				'name' => $project->name,
				'description' => $project->description,
				'completed' => $completed,
				'assignees' => Members::memberIdStringToNameString($project->assignees),
				'categories' => implode(',', (array) maybe_unserialize($project->categories)),
				'date_created' => $project->date_created,
				'date_due' => $project->date_due,
				'date_start' => $project->date_start,
				'date_completed' => $project->date_completed
			];
			$data = apply_filters('zpm/projects/export/data', (array) $filedata, $project);
			fputcsv($filename, $data);
		}

		$files = array(
			'project_csv' => $filename
		);
		$filename = $upload_dir['baseurl'] . '/ZPM Projects.csv';

		echo json_encode($filename);
		wp_die();
	}

	public function exportTasksToCSV() {
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename="zpm_tasks.csv"');
		header('Pragma: no-cache');
		header('Expires: 0');
		$tasks = Tasks::getAllTasks();
		$upload_dir = wp_upload_dir();
		$filename = $upload_dir['basedir'] . '/ZPM Tasks.csv';
		$filename = fopen($filename, 'w+');
		$headers = Tasks::getExportHeaders();
		$options = $this->getPostVar('options', []);
		$hasOptions = !empty($options);

		if (!$hasOptions) {
			$options = [
				'headers' => [],
				'exportNames' => false
			];
		} else {
			$options['exportNames'] = zpm_sanitize_bool($options['exportNames']);
		}

		if ($hasOptions) {
			// var_dump($options);
			foreach ($headers as $slug => $header) {
				if (!in_array($slug, $options['headers'])) {
					unset($headers[$slug]);
				}
			}
		}

		fputcsv($filename, (array) $headers);

		foreach ($tasks as $task) {
			if (!Utillities::can_view_task($task)) continue;

			if ($hasOptions) {
				$dateCreated = strtotime(date('Y-m-d', strtotime($task->date_created)));

				if (!empty($options['from'])) {
					$from = strtotime(date('Y-m-d', strtotime($options['from'])));

					if ($dateCreated < $from) {
						continue;
					}
				}

				if (!empty($options['to'])) {
					$to = strtotime(date('Y-m-d', strtotime($options['to'])));

					if ($dateCreated > $to) {
						continue;
					}
				}
			}

			$completed = $task->completed;

			if ($completed == '1') {
				$completed = 'Yes';
			} else {
				$completed = 'No';
			}

			$filedata = [
				'id' => $task->id,
				'parent_id' => $task->parent_id,
				'created_by' => $task->user_id,
				'project' => $task->project,
				'assignee' => $task->assignee,
				'name' => $task->name,
				'description' => $task->description,
				'completed' => $completed,
				'categories' => implode(',', (array) maybe_unserialize($task->categories)),
				'date_created' => $task->date_created,
				'date_due' => $task->date_due,
				'date_start' => $task->date_start,
				'date_completed' => $task->date_completed
			];

			if ($hasOptions) {
				if ($options['exportNames']) {
					if (Tasks::hasParent($task)) {
						$parent = Tasks::get_task($task->parent_id);

						if (is_object($parent)) {
							$filedata['parent_id'] = $parent->name;
						} else {
							$filedata['parent_id'] = '';
						}
					} else {
						$filedata['parent_id'] = '';
					}

					if (Tasks::hasProject($task)) {
						$project = Projects::get_project($task->project);

						if (is_object($project)) {
							$filedata['project'] = $project->name;
						} else {
							$filedata['project'] = '';
						}
						// var_dump($project);
					} else {
						$filedata['project'] = '';
					}

					$filedata['assignee'] = Members::memberIdStringToNameString($task->assignee);
 				}
			}

			if (Utillities::getSetting('task_duration_enabled')) {
				$filedata['duration'] = Tasks::getDuration($task);
			}

			if (Utillities::getSetting('task_blocking_enabled')) {
				$blocking = Tasks::getBlockingTasks($task->id);
				$filedata['blocking_tasks'] = implode(',', $blocking);
			}

			$data = apply_filters('zpm/tasks/export/data', (array) $filedata, $task);

			if ($hasOptions) {
				foreach ($data as $key => $value) {
					if (!in_array($key, $options['headers'])) {
						unset($data[$key]);
					}
				}
			}

			fputcsv($filename, $data);
		}

		$filename = $upload_dir['baseurl'] . '/ZPM Tasks.csv';
		echo json_encode($filename);
		wp_die();
	}

	public function getTaskPanelHTML() {
		$taskId = isset($_POST['id']) ? zpm_sanitize_int($_POST['id']) : -1;

		ob_start();
		include(ZPM_PLUGIN_PATH . '/templates/parts/task-panel.php');
		$html = ob_get_clean();

		echo json_encode([
			'html' => $html
		]);
		wp_die();
	}

	public function getProjectPanelHTML() {
		$projectId = isset($_POST['id']) ? zpm_sanitize_int($_POST['id']) : -1;

		ob_start();
		include(ZPM_PLUGIN_PATH . '/templates/parts/project-panel.php');
		$html = ob_get_clean();

		echo json_encode([
			'html' => $html
		]);
		wp_die();
	}

	public function filter_tasks_by() {
		$filter = isset($_POST['filter']) ? $_POST['filter'] : '-1';
		$current_filter = isset($_POST['current_filter']) ? $_POST['current_filter'] : '-1';
		$assignee = isset($_POST['assignee']) ? $_POST['assignee'] : '-1';
		$user_id = get_current_user_id();
		$tasks = array();
		$taskRowFilters = [];

		if ($current_filter == '0' && $assignee == '-1') {
			$assignee = get_current_user_id();
		}

		switch ($current_filter) {
			case '-1':
				$tasks = Tasks::get_tasks();
				break;
			case '0':
				$tasks = Tasks::get_user_tasks($user_id);
				break;
			case '1':
				$tasks = Tasks::get_completed_tasks(0);
				break;
			case '2':
				$tasks = Tasks::get_completed_tasks(1);
				break;
			default:
				break;
		}

		if ($filter == 'status') {
			$status = $this->getPostVar('status', '');
			$taskRowFilters['filter'] = 'status';
			$taskRowFilters['status'] = $status;

			if (!in_array($status, ['all', '-1'])) {
				if ($status == 'incomplete') {
					$tasks = Tasks::get_completed_tasks(0);
				} else {
					if (!empty($status)) {
						$tasks = Tasks::getTasksByStatus($status);
					} else {
						$tasks = Tasks::sortByStatus($tasks);
					}
				}
			}
		}

		if ($filter == 'priority') {
			$priority = $this->getPostVar('status');
			$tasks = Tasks::getTasksByPriority($priority);
		}

		if ($filter == 'category') {
			$category = $this->getPostVar('category');
			$tasks = Tasks::getTasksByCategory($category);
		}

		if ($filter == 'team') {
			$team = $this->getPostVar('team');
			$tasks = Tasks::getTasksByTeam($team);
		}

		$tasks = apply_filters('zpm_task_filter_results', $tasks, $filter);

		if ($assignee !== '-1' && $assignee !== 'all') {
			if ($assignee == 'my_tasks') {
				$assignee = get_current_user_id();
			}

			$filteredTasks = [];

			foreach ($tasks as $task) {
				if (Tasks::is_assignee($task, $assignee)) {
					$filteredTasks[] = $task;
				}
			}

			$tasks = $filteredTasks;
		}

		if (isset($_POST['project'])) {
			$project = $_POST['project'];
			$tempTasks = $tasks;
			$tasks = [];

			foreach ($tempTasks as $task) {
				if ($project !== 'none') {
					if ($task->project == $project || $project == '-1') {
						$tasks[] = $task;
					}
				} else {
					if ($task->project == '-1') {
						$tasks[] = $task;
					}
				}
			}
		}

		update_user_meta($user_id, 'zpm_tasks_last_sorting', $filter);

		$tasks = Tasks::sortTasks($tasks, $filter);
		$html = '';
		$frontend = isset($_POST['frontend']) ? $_POST['frontend'] : false;

		foreach ($tasks as $task) {
			$new_row = Tasks::new_task_row($task, $frontend, $taskRowFilters);

			if (!$frontend) {
				$html .= $new_row;
			} else {
				$html .= '<a href="?action=task&id=' . $task->id . '">' . $new_row . '</a>';
			}
		}

		if (empty($tasks)) {
			$html = '<p class="zpm_error_message">' . __('No results found...', 'zephyr-project-manager') . '</p>';
		}

		$response = [
			'html' => $html
		];

		echo json_encode($response);
		die();
	}

	public function updateTaskMeta() {
		$id = $this->getPostVar('id');
		$key = $this->getPostVar('key');
		$value = $this->getPostVar('value');
		Tasks::updateMeta($id, $key, $value);
		wp_send_json_success([]);
	}

	public function createTaskList() {
		$list = $this->getPostVar('list', []);
		$frontend = $this->getPostVar('frontend', false);
		$assignees = $this->getPostVar('assignees', []);
		$project = $this->getPostVar('project', '-1');

		ob_start();
		foreach ($list as $item) {
			if (empty($item)) continue;

			$taskID = Tasks::create([
				'user_id' => '-1',
				'parent_id' => '-1',
				'project' => $project,
				'date_start' => '',
				'date_due' => '',
				'date_created' => date('Y-m-d H:i:s'),
				'date_completed' => '',
				'completed' => 0,
				'priority' => 'priority_none',
				'name' => $item,
				'assignee' => $assignees,
				'team' => '-1',
			]);
			$task = Tasks::get_task($taskID);
			echo Tasks::new_task_row($task, $frontend);
		}

		$html = ob_get_clean();
		wp_send_json_success([
			'html' => $html
		]);
	}

	public function bulkDeleteTasks() {
		$tasks = $this->getPostVar('tasks', []);

		if (Utillities::canDeleteTasks(get_current_user_id())) {
			foreach ($tasks as $taskID) {
				Tasks::delete($taskID);
			}
		}

		wp_send_json_success([]);
	}

	public function bulkArchiveTasks() {
		$tasks = $this->getPostVar('tasks', []);

		if (Utillities::canDeleteTasks(get_current_user_id())) {
			foreach ($tasks as $taskID) {
				Tasks::archive($taskID);
			}
		}

		wp_send_json_success([]);
	}

	public function bulkUpdateTasks() {
		$tasks = $this->getPostVar('tasks', []);
		$values = $this->getPostVar('values', []);

		foreach ($tasks as $taskID) {
			$taskData = [];

			if (isset($values['assignees'])) {
				$taskData['assignee'] = implode(',', $values['assignees']);
			}

			if (isset($values['priority'])) {
				$taskData['priority'] = $values['priority'];
			}

			if (isset($values['status'])) {
				$taskData['status'] = $values['status'];
			}

			if (isset($values['startDate'])) {
				$taskData['date_start'] = $values['startDate'];
			}

			if (isset($values['dueDate'])) {
				$taskData['date_due'] = $values['dueDate'];
			}

			Tasks::update($taskID, $taskData);
		}

		wp_send_json_success([]);
	}

	public function importIcal() {
		$url = $this->getParam('url');
		$tasks = Tasks::importIcal($url);
		wp_send_json_success([
			'tasks' => $tasks
		]);
	}

	public function filterTasks() {
		// $manager = ZephyrProjectManager();
		$filters = wp_parse_args($this->getPostVar('filters', []), [
			// 'tab' => 'my_tasks',
			'assignee' => 'my_tasks',
			'sorting' => 'date-created',
			'status' => 'all',
			'project' => 'all'
		]);
		$user_id = '-1';
		$sorting = $filters['sorting'];
		$project = $filters['project'];
		$assignee = $filters['assignee'];
		$status = $filters['status'];

		if ($assignee == 'my_tasks') {
			// $assignee = get_current_user_id();
			$user_id = get_current_user_id();
		}

		$tasks = [];
		$hideCompletedTasks = Utillities::getSetting('hide_completed_tasks');
		// $filter = $filters['tab'];

		// var_dump($filter);
		if ($assignee == 'all') {
			$tasks = Tasks::getTasks();
		} elseif ($assignee == 'my_tasks') {
			$tasks = Tasks::get_user_tasks(get_current_user_id());
		} elseif (is_numeric($assignee)) {
			$tasks = Tasks::get_user_tasks($assignee);
		}
		// if ($assignee == 'all') {
		// 	$tasks = Tasks::getTasks();
		// } elseif ($assignee == 'my_tasks') {
		// 	$tasks = Tasks::get_user_tasks($user_id);
		// } elseif ($filter == 'active' || $status == 'active') {
		// 	$tasks = Tasks::get_completed_tasks(0);
		// } elseif ($filter == 'completed') {
		// 	$tasks = Tasks::get_completed_tasks(1);
		// } elseif ($filter == 'archived' || $status == 'archived') {
		// 	$tasks = Tasks::getArchivedTasks();
		// }

		$totalCount = count($tasks);

		foreach ($tasks as $key => $task) {
			if (!in_array($project, ['all', '-1'])) {
				if (intval($task->project) !== intval($project)) unset($tasks[$key]); continue;
			}

			if (!in_array($status, ['all', '-1', 'archived', 'active'])) {
				if (!Tasks::isStatus($task, $status)) unset($tasks[$key]); continue;
			}

			// if (!in_array($assignee, ['all', '-1'])) {
			// 	if (!Tasks::is_assignee($task, $assignee)) unset($tasks[$key]); continue;
			// }

			if ($hideCompletedTasks) {
				if ($assignee == 'my_tasks') {
					if (Tasks::isCompleted($task)) unset($tasks[$key]); continue;
				}
			}
		}

		$html = '';
		$frontend = zpm_sanitize_bool($this->getPostVar('frontend', false));
		$tasks = Tasks::sortTasks($tasks, $sorting);
		$count = 0;

		foreach ($tasks as $task) {
			// if ($filter == 'my_tasks' && $hideCompletedTasks) {
			// 	if (Tasks::isCompleted($task)) continue;
			// }

			if (!in_array($assignee, ['-1', 'all', 'my_tasks'])) {
				if (!Tasks::is_assignee($task, $assignee)) continue;
			} else {
				// if ($user_id !== '-1' && !Tasks::is_assignee($task, $user_id)) continue;
			}

			if (!in_array($project, ['-1', 'all']) && !empty($project)) {
				if (intval($task->project) !== intval($project)) continue;
			}

			$new_row = Tasks::new_task_row($task, $frontend, [
				'userID' => $user_id,
				'sorting' => $sorting,
				'project' => $project,
				'assignee' => $assignee,
				'status' => $status
			]);

			if (!$frontend) {
				$html .= $new_row;
			} else {
				$html .= '<a href="?action=task&id=' . $task->id . '">' . $new_row . '</a>';
			}

			$count++;
		}

		if (empty($tasks) || empty($html)) {
			$html = '<p class="zpm_error_message">' . __('No results found...', 'zephyr-project-manager') . '</p>';
		}

		wp_send_json_success([
			'html' => $html,
			'tasks' => $tasks,
			'length' => $count,
			'length_total' => $totalCount
		]);
	}

	public function getProjectOverview() {
		$id = $this->getPostVar('id');
		$html = zpm_get_template('project/overview', [
			'project' => Projects::get_project($id)
		]);

		wp_send_json_success([
			'html' => $html
		]);
	}

	public function updateSubtaskOrder() {
		$id = $this->getPostVar('taskID');
		$positions = $this->getPostVar('positions');
		Tasks::updateMeta($id, 'subtaskOrder', $positions);
		wp_send_json_success([
			'positions' => $positions
		]);
	}

	public function updateTaskDates() {
		$taskID = $this->getPostVar('taskID');
		$start = $this->getPostVar('start');
		$due = $this->getPostVar('due');
		// $start = substr($start, 0, -3);
		// $due = substr($due, 0, -3);
		$data = [
			'date_start' => date('Y-m-d H:i:s', intval($start)),
			'date_due' => date('Y-m-d H:i:s', intval($due)),
		];

		// var_dump($data);

		Tasks::update($taskID, $data);

		if (Utillities::getSetting('task_duration_enabled')) {
			$diff = strtotime($data['date_due']) - strtotime($data['date_start']);

			if ($diff == 0) return 1;

			$days = abs(round($diff / 86400));
			$duration = $days + 1;
			Tasks::updateMeta($taskID, 'duration', $duration);
			$data['duration'] = $duration;
		}

		wp_send_json_success([
			'data' => $data
		]);
	}

	public function sendTestEmails() {
		$email = $this->getPostVar('email');
		Emails::sendTest($email);
		wp_send_json_success([]);
	}
}
