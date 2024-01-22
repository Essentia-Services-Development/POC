<?php

/**
 * @package ZephyrProjectManager
 */

namespace ZephyrProjectManager\Api;

if (!defined('ABSPATH')) {
	die;
}

use \DateTime;
use ZephyrProjectManager\Core\Tasks;
use ZephyrProjectManager\Core\Members;
use ZephyrProjectManager\Core\Projects;
use ZephyrProjectManager\Core\Utillities;
use ZephyrProjectManager\Base\BaseController;

class Emails {

	public function __construct() {

	}

	/**
	 * Sends an email to a give email address
	 * Used to send updates, reports and notifcations
	 * @param string $to The email of the recipient
	 * @param string $subject The subject of the email
	 * @param string $message_subject
	 * @param int $subject_id
	 * @param string $message
	 */
	public static function send_email($to, $subject, $message) {
		add_filter('wp_mail_content_type', array('ZephyrProjectManager\Api\Emails', 'set_html_content_type'));
		add_filter('wp_mail_from', array('ZephyrProjectManager\Api\Emails', 'do_email_filter'));
		add_filter('wp_mail_from_name', array('ZephyrProjectManager\Api\Emails', 'do_email_name_filter'));
		if (!empty($to)) {
			if (wp_mail($to, $subject, $message)) {
			} else {
			}
		}


		remove_filter('wp_mail_content_type', array('ZephyrProjectManager\Api\Emails', 'set_html_content_type'));
	}

	// define the wp_mail_failed callback
	public static function action_mail_failed($wp_error) {
		return error_log(print_r($wp_error, true));
	}

	public static function set_html_content_type() {
		return 'text/html';
	}

	public static function do_email_filter() {
		return Emails::get_from_email();
	}

	public static function do_email_name_filter() {
		return Emails::get_from_name();
	}

	public static function email_template($header, $body, $footer) {
		ob_start();
		include(ZPM_PLUGIN_PATH . '/templates/email_templates/email_template.php');
		$email_content = ob_get_clean();
		return $email_content;
	}

	public static function task_email_template($subject_id) {
		ob_start();
		include(ZPM_PLUGIN_PATH . '/templates/email_templates/task_email.php');
		$email_content = ob_get_clean();
		return $email_content;
	}

	public static function project_template($project_id) {
		ob_start();
		include(ZPM_PLUGIN_PATH . '/templates/email_templates/project_email.php');
		$email_content = ob_get_clean();
		return $email_content;
	}

	public static function task_notifications_template($subject_id) {
		ob_start();
		include(ZPM_PLUGIN_PATH . '/templates/email_templates/task_notifications_email.php');
		$email_content = ob_get_clean();
		return $email_content;
	}

	/**
	 * Sends an email update to all users depending on their notification preferences
	 */
	public static function send_updates($message = null, $subject = null, $subject_id = null) {
		$users = get_users();
		$project_managers = [];
	}

	/**
	 * Sends a weekly email update of projects to all users
	 */
	public static function weekly_updates($projects) {
		$members = Members::get_zephyr_members();

		// TODO: look into bringing weekly updates back in the future
		// return;

		$sent_emails = [];
		$count = 0;

		// if ($count > 0) {
			foreach ($members as $member) {
				if (in_array($member['email'], $sent_emails)) continue;

				ob_start();

				$count = 0;
				foreach ($projects as $project) {
					// if (!Utillities::check_user_project_setting($member['id'], $project->id, 'weekly_update_email')) {
					// 	continue;
					// }

					$task_count = Tasks::get_project_task_count($project->id);
					$completed_tasks = Tasks::get_project_completed_tasks($project->id);
					$args = array(
						'project_id' => $project->id
					);
					$overdue_tasks = sizeof(Tasks::get_overdue_tasks($args));
					$pending_tasks = $task_count - $completed_tasks;
					$percent_complete = ($task_count !== 0) ? floor($completed_tasks / $task_count * 100) : '100';

?>
					<h3 id="project-title"><?php echo esc_html($project->name); ?></h3>
					<div class="tasks_section" style="margin-bottom: 30px;">
						<span class="task_item">
							<div class="task_count"><?php echo esc_html($task_count); ?></div>
							<div class="task_subject"><?php _e('Tasks', 'zephyr-project-manager'); ?></div>
						</span>
						<span class="task_item">
							<div class="task_count"><?php echo esc_html($completed_tasks); ?></div>
							<div class="task_subject"><?php _e('Completed', 'zephyr-project-manager'); ?></div>
						</span>
						<span class="task_item">
							<div class="task_count"><?php echo esc_html($pending_tasks); ?></div>
							<div class="task_subject"><?php _e('Pending', 'zephyr-project-manager'); ?></div>
						</span>
						<span class="task_item">
							<div class="task_count"><?php echo esc_html($percent_complete); ?>%</div>
							<div class="task_subject"><?php _e('Complete', 'zephyr-project-manager'); ?></div>
						</span>
					</div>
					<?php
					$count++;
				}

				$body = ob_get_clean();
				$link = esc_url(admin_url("/admin.php?page=zephyr_project_manager_projects"));

				if (zpmIsFrontendEnabled()) {
					$link = Utillities::get_frontend_url('action=projects');
				}

				$footer = '<button id="zpm_action_button" style="padding: 10px;"><a href="' . $link . '" style="color: #fff; text-decoration: none;">' . __('View in WordPress', 'zephyr-project-manager') . '</a></button>';
				$html = Emails::email_template(__('Weekly Updates', 'zephyr-project-manager'), $body, $footer);

				if (Members::isNotificationEnabled($member, 'updates')) {
					Emails::send_email($member['email'], __('Weekly Updates', 'zephyr-project-manager'), $html);
				}
			}
		// }
	}

	public static function task_completed_email($task) {
		$members = Members::get_zephyr_members();
		$subject = __('Task Completed', 'zephyr-project-manager');
		$header =  __('Task Completed', 'zephyr-project-manager');
		$message = sprintf(__('The task %s has been completed.', 'zephyr-project-manager'), esc_html($task->name));
		$body = '<div><span class="zpm_content">' . $message . '</span></div>';
		$link = admin_url("/admin.php?page=zephyr_project_manager_tasks&action=view_task&task_id=" . $task->id);

		if (zpmIsFrontendEnabled()) {
			$link = Utillities::get_frontend_url('action=task&id=' . $task->id);
		}

		$url = esc_url($link);
		$footer = '<button id="zpm_action_button"><a href="' . $url . '" style="color: #fff; padding: 10px; text-decoration: none;">' . __('View Task', 'zephyr-project-manager') . '</a></button>';
		$sent_emails = [];
		$body .= '<div id="zpm-new-task-email__description">' . $task->description . '</div>';

		$html = Emails::email_template($header, $body, $footer);

		foreach ($members as $member) {
			if (!Members::isNotificationEnabled($member, 'tasks')) continue;

			if (!Tasks::is_assignee($task, $member['id']) && $task->user_id !== $member['id']) continue;

			if (in_array($member['email'], $sent_emails)) continue;

			if (!Utillities::check_user_project_setting($member['id'], $task->project, 'task_completed_email')) continue;

			Emails::send_email($member['email'], $subject, $html);
			$sent_emails[] = $member['email'];
		}

		if (Tasks::hasProject($task)) {
			$additionalEmails = Projects::getAdditionalEmails($task->project);
			foreach ($additionalEmails as $email) {
				if (!empty($email)) {
					Emails::send_email($email, $subject, $html);
				}
			}
		}
	}

	public static function taskStatusChanged($task, $status) {
		$members = Members::get_zephyr_members();
		$subject = __('Task Status Changed', 'zephyr-project-manager');
		$header =  __('Task Status Changed', 'zephyr-project-manager');
		$message = sprintf(__('The task status of %s has been changed to %s.', 'zephyr-project-manager'), esc_html($task->name), $status);
		$body = '<div><span class="zpm_content">' . $message . '</span></div>';
		$link = admin_url("/admin.php?page=zephyr_project_manager_tasks&action=view_task&task_id=" . $task->id);

		if (zpmIsFrontendEnabled()) {
			$link = Utillities::get_frontend_url('action=task&id=' . $task->id);
		}

		$url = esc_url($link);
		$footer = '<button id="zpm_action_button"><a href="' . $url . '" style="color: #fff; padding: 10px; text-decoration: none;">' . __('View Task', 'zephyr-project-manager') . '</a></button>';
		$sent_emails = [];
		$body .= '<div id="zpm-new-task-email__description">' . $task->description . '</div>';

		$html = Emails::email_template($header, $body, $footer);

		foreach ($members as $member) {
			if (!Members::isNotificationEnabled($member, 'tasks')) continue;
			if (!Tasks::is_assignee($task, $member['id']) && $task->user_id !== $member['id']) continue;
			if (in_array($member['email'], $sent_emails)) continue;
			if (!Utillities::check_user_project_setting($member['id'], $task->project, 'task_completed_email')) continue;

			Emails::send_email($member['email'], $subject, $html);
			$sent_emails[] = $member['email'];
		}

		if (Tasks::hasProject($task)) {
			$additionalEmails = Projects::getAdditionalEmails($task->project);

			foreach ($additionalEmails as $email) {
				if (!empty($email)) {
					Emails::send_email($email, $subject, $html);
				}
			}
		}
	}

	/**
	 * Sends a weekly email update of projects to all users
	 * @param array $tasks Array of overdue tasks
	 */
	public static function task_notifications($tasks) {
		$users = BaseController::get_users();

		if (sizeof($tasks) >= 0) {
			foreach ($users as $user) {
				$user_id = $user->ID;
				$user = BaseController::get_project_manager_user($user_id);
				$email = $user['email'] !== '' ? $user['email'] : wp_get_current_user()->user_email;
				$name = $user['name'] !== '' ? $user['name'] : wp_get_current_user()->display_name;

				if (Members::isNotificationEnabled($user, 'tasks')) {
					$subject = __('Tasks due this week', 'zephyr-project-manager');
					$header = __('You have the following due tasks this week', 'zephyr-project-manager');

					ob_start();
					$i = 0;
					foreach ($tasks as $task) : ?>
						<?php
						$date = new DateTime();
						$original = new DateTime($task->date_due);
						$overdue = '';
						$due_date = $original->format('Y') !== '-0001' ? $original->format('d M') : __('No date set', 'zephyr-project-manager');
						$overdue = ($date->format('Y-m-d') > $original->format('Y-m-d')) ? 'overdue' : '';
						?>
						<?php if (Tasks::is_assignee($task, $user_id)) : ?>
							<div class="email_task">
								<?php echo esc_html($task->name); ?>
								<span class="email_task_date <?php echo esc_attr($overdue); ?>"><?php echo esc_html($due_date); ?></span>
							</div>
						<?php endif; ?>
						<?php $i++; ?>
<?php endforeach;
					$body = ob_get_clean();

					$link = esc_url(admin_url("/admin.php?page=zephyr_project_manager_tasks"));

					if (zpmIsFrontendEnabled()) {
						$link = Utillities::get_frontend_url('action=tasks');
					}

					$footer = '<button id="zpm_action_button" style="padding: 10px;"><a href="' . $link . '" style="color: #fff; text-decoration: none;">' . __('View Tasks in WordPress', 'zephyr-project-manager') . '</a></button>';
					if ($i > 0) {
						//$html = Emails::email_template($header, $body, $footer);
						//Emails::send_email($email, $subject, $html);
					}
				}
			}
		}
	}

	/**
	 * Sends an email update about a new project to all users depending on their notification preferences
	 */
	public static function new_project_email($project_id) {
		$project = Projects::get_project($project_id);
		$settings = Utillities::general_settings();
		$users = Members::get_members();
		$assignees = [];

		foreach ($users as $user) {
			if (!isset($user['email'])) continue;

			if (Projects::is_project_member($project, $user['id'])) {
				$assignees[] = $user;
			}
		}

		if ($settings['override_default_emails']) {
			$assignees = Members::get_zephyr_members();
		}

		$header = __('New Project', 'zephyr-project-manager');
		$subject = __('New Project', 'zephyr-project-manager');
		$message = '';
		$message .= '<br/>';
		$message .= __('Project Name', 'zephyr-project-manager') . ': ' . esc_html($project->name);
		$message .= '<br/>';
		$message .= __('Project Description', 'zephyr-project-manager') . ': ' . $project->description;
		$message .= '<br/>';
		$message .= __('Please login to view the details', 'zephyr-project-manager');
		$body = '<div><span class="zpm_content">' . $message . '.</span></div>';
		$link = admin_url("/admin.php?page=zephyr_project_manager_projects&action=view_project&project_id=" . $project->id);

		if (zpmIsFrontendEnabled()) {
			$link = Utillities::get_frontend_url("action=project&id={$project->id}");
		}

		$footer = '<a id="zpm_action_button" href="' . $link . '" style="color: #fff; padding: 10px; text-decoration: none;">' . __('View Project', 'zephyr-project-manager') . '</a>';
		$html = Emails::email_template($header, $body, $footer);
		$sent = [];

		foreach ($assignees as $assignee) {
			if (in_array($assignee['email'], $sent)) continue;

			if (!Members::isNotificationEnabled($assignee, 'activity') && !Members::isNotificationEnabled($assignee, 'tasks')) continue;

			Emails::send_email($assignee['email'], $subject, $html);
			$sent[] = $assignee['email'];
		}

		return $sent;
	}

	/**
	 * Sends an email update about a new project to all users depending on their notification preferences
	 */
	public static function new_task_email($task_id, $user_id = null) {
		$task = Tasks::get_task($task_id);
		$emails = Emails::assignedTaskEmail($task);
		return $emails;
	}

	/**
	 * Sends an email update about a new project to all users depending on their notification preferences
	 */
	public static function new_subtask_email($subtask, $user_id = null) {
		$members = Members::get_zephyr_members();
		$parent = Tasks::get_task($subtask->parent_id);
		$header = sprintf(__('New subtask in %s: %s', 'zephyr-project-manager'), esc_html($parent->name), esc_html($subtask->name));
		$subject = __('New Subtask', 'zephyr-project-manager');
		$body = '<div><span class="zpm_content">' . sprintf(__('A new subtask has been created for the task %s called %s', 'zephyr-project-manager'), esc_html($parent->name), esc_html($subtask->name)) . '.</span></div>';
		$link = admin_url("/admin.php?page=zephyr_project_manager_tasks&action=view_task&task_id=" . $parent->id);

		if (zpmIsFrontendEnabled()) {
			$link = Utillities::get_frontend_url('action=task&id=' . $parent->id);
		}

		$url = esc_url($link);
		$footer = '<button id="zpm_action_button"><a href="' . $url . '" style="color: #fff; padding: 10px; text-decoration: none;">' . __('View Task', 'zephyr-project-manager') . '</a></button>';

		$sent_emails = [];

		$team_id = property_exists($parent, 'team') ? $parent->team : '-1';
		$team = Members::get_team($team_id);

		if (!is_null($team)) {
			$body .= '<div>' . __('Assigned to Team:') . esc_html($team['name']) . '</div>';
		}

		foreach ($members as $member) {
			if (in_array($member['email'], $sent_emails)) continue;
			if (!Utillities::check_user_project_setting($member['id'], $parent->project, 'new_subtask_email')) continue;

			if (Members::isNotificationEnabled($member, 'tasks') || Members::isNotificationEnabled($member, 'task_assigned')) {
				$html = Emails::email_template($header, $body, $footer);
				Emails::send_email($member['email'], $subject, $html);
				$sent_emails[] = $member['email'];
			}
		}
	}

	/**
	 * Sends an email update about a deleted task
	 */
	public static function delete_task_email($task_id) {
		$users = get_users();
		$creator = BaseController::get_project_manager_user(get_current_user_id());
		$project_managers = [];
	}

	/**
	 * Sends an email update about a deleted project
	 */
	public static function deleted_project_email($project_id) {
		$users = get_users();
		$creator = BaseController::get_project_manager_user(get_current_user_id());
		$project_managers = [];
	}

	public static function task_date_change_email($id, $task_name, $date_due) {
		$creator = BaseController::get_project_manager_user(get_current_user_id());
		$project_managers = [];
		$task = Tasks::get_task($id);

		if ($task->assignee == "" || $task->assignee == "-1") {
			return;
		}
	}

	public static function get_from_name() {
		$settings = Utillities::general_settings();
		$name = $settings['email_from_name'];
		return $name;
	}

	public static function get_from_email() {
		$settings = Utillities::general_settings();
		$email = $settings['email_from_email'];
		return $email;
	}

	public static function send_comment_notification($comment, $object, $type) {
		$settings = Utillities::general_settings();
		$members = Members::get_zephyr_members();
		$content = '';
		$subject = __('New Comment', 'zephyr-project-manager');
		$header = __('New Comment', 'zephyr-project-manager');
		$userId = get_current_user_id();
		$sender = Members::get_member($userId);

		switch ($type) {
			case 'task':
				$subject = __('New Comment', 'zephyr-project-manager');
				$header = __('New Task Comment', 'zephyr-project-manager');
				$url = admin_url('/admin.php?page=zephyr_project_manager_tasks&action=view_task&task_id=' . $object->id . '#task-discussion');

				if (zpmIsFrontendEnabled()) {
					$url = Utillities::get_frontend_url('action=task&id=' . $object->id . '#tasks-discussion');
				}

				$content = sprintf(__('%s (%s) has commented on the task <b>"%s"</b>', 'zephyr-project-manager'), $comment->username, $sender['email'], esc_html($object->name));
				$content .= '<br><a href="' . $url . '">View Comments</a>';
				break;
			case 'project':
				$subject = __('New Comment', 'zephyr-project-manager');
				$header = __('New Project Comment', 'zephyr-project-manager');
				$url = admin_url('/admin.php?page=zephyr_project_manager_projects&action=edit_project&project=' . $object->id . '#project-discussion');

				if (zpmIsFrontendEnabled()) {
					$url = Utillities::get_frontend_url('action=project&id=' . $object->id . '#discussion');
				}

				$content = sprintf(__('%s (%s) has commented on the project <b>"%s"</b>', 'zephyr-project-manager'), $comment->username, $sender['email'], esc_html($object->name));
				$content .= '<br><a href="' . $url . '">View Comments</a>';
				break;
			default:
				break;
		}

		$content .= '<br>' . $comment->message;
		$sent_emails = [];

		if ($content !== '') {
			$html = Emails::email_template($header, $content, '');

			if ($userId !== $object->user_id) {
				if (isset($member['email'])) {
					$member = Members::get_member($object->user_id);
					Emails::send_email($member['email'], $subject, $html);
					$sent_emails[] = $member['email'];
				}
			}

			if ($type == 'task') {
				$assignees = Tasks::get_assignees($object);

				foreach ($assignees as $assignee) {
					if (isset($assignee['id']) && $assignee['id'] !== $userId) {
						$member = Members::get_member($assignee);
						Emails::send_email($member['email'], $subject, $html);
					}
				}
			} else {
				$members = Projects::getAssignees($object);

				foreach ($members as $member) {
					if (!isset($member['email'])) continue;
					if (in_array($member['email'], $sent_emails)) continue;
					if (!$settings['override_default_emails'] && ($type == 'project' && (property_exists($object, 'project') && !Utillities::check_user_project_setting($member['id'], $object->id, 'project_comments_email')))) continue;

					$footer = '';
					$html = Emails::email_template($header, $content, $footer);
					Emails::send_email($member['email'], $subject, $html);
					$sent_emails[] = $member['email'];
				}

				$additionalEmails = Projects::getAdditionalEmails($object->id);

				foreach ($additionalEmails as $email) {
					if (!empty($email)) {
						Emails::send_email($email, $subject, $html);
					}
				}
			}
		}
	}

	public static function assignedTaskEmail($task) {
		$settings = Utillities::general_settings();
		$users = Members::get_zephyr_members();
		$assigneeEmails = [];
		$emails = [];

		foreach ($users as $assignee) {
			if (Tasks::is_assignee($task, $assignee['id'])) {
				$assigneeEmails[] = $assignee;
			} else {
				$emails[] = $assignee['email'];
			}
		}

		$emails = apply_filters('zpm_new_task_emails', $emails);
		$header = __('New task assigned to you', 'zephyr-project-manager');
		$subject = __('New task assigned to you', 'zephyr-project-manager');
		$message = '';
		$message .= '<br/>';
		$message .= __('Task Name', 'zephyr-project-manager') . ': ' . esc_html($task->name);
		$message .= '<br/>';
		$message .= __('Task Description', 'zephyr-project-manager') . ': ' . $task->description;
		$message .= '<br/>';
		$message .= __('Please login to view the details', 'zephyr-project-manager');
		$body = '<div><span class="zpm_content">' . $message . '.</span></div>';
		$link = admin_url("/admin.php?page=zephyr_project_manager_tasks&action=view_task&task_id=" . $task->id);

		if (zpmIsFrontendEnabled()) {
			$link = Utillities::get_frontend_url("action=task&id={$task->id}");
		}

		$footer = '<a id="zpm_action_button" href="' . $link . '" style="color: #fff; padding: 10px; text-decoration: none;">' . __('View Task', 'zephyr-project-manager') . '</a>';
		$html = Emails::email_template($header, $body, $footer);
		$sent = [];

		foreach ($assigneeEmails as $member) {
			if (in_array($member['email'], $sent)) continue;

			if (!Members::isNotificationEnabled($member, 'tasks') && !Members::isNotificationEnabled($member, 'task_assigned')) continue;

			Emails::send_email($member['email'], $subject, $html);
			$sent[] = $member['email'];
		}

		$header = __('New task has been created', 'zephyr-project-manager');
		$subject = __('New task has been created', 'zephyr-project-manager');
		$html = Emails::email_template($header, $body, $footer);

		if (Tasks::hasProject($task)) {
			$projectMembers = Projects::getAssignees(Projects::get_project($task->project));

			foreach ($projectMembers as $member) {
				if (!isset($member['email'])) continue;
				if (in_array($member['email'], $sent)) continue;

				if (!Members::isNotificationEnabled($member, 'new_project_tasks')) continue;

				Emails::send_email($member['email'], $subject, $html);
				$sent[] = $member['email'];
			}
		}

		if ($settings['override_default_emails']) {
			foreach ($emails as $email) {
				if (in_array($email, $sent)) continue;

				Emails::send_email($email, $subject, $html);
				$sent[] = $email;
			}
		}

		return $sent;
	}

	public static function taskRecurred($task) {
		$settings = Utillities::general_settings();
		$users = Members::get_members();
		$assigneeEmails = [];
		$emails = [];

		foreach ($users as $assignee) {
			if (Tasks::is_assignee($task, $assignee['id'])) {
				$assigneeEmails[] = $assignee;
			}
		}

		$emails = apply_filters('zpm_new_task_emails', $emails);
		$header = __('New recurrence of task', 'zephyr-project-manager');
		$subject = __('New recurrence of task', 'zephyr-project-manager');
		$message = '';
		$message .= '<br/>';
		$message .= __('Task Name', 'zephyr-project-manager') . ': ' . esc_html($task->name);
		$message .= '<br/>';
		$message .= __('Task Description', 'zephyr-project-manager') . ': ' . $task->description;
		$message .= '<br/>';
		$message .= __('Please login to view the details', 'zephyr-project-manager');
		$body = '<div><span class="zpm_content">' . $message . '.</span></div>';
		$link = admin_url("/admin.php?page=zephyr_project_manager_tasks&action=view_task&task_id=" . $task->id);

		if (zpmIsFrontendEnabled()) {
			$link = Utillities::get_frontend_url("action=task&id={$task->id}");
		}

		$footer = '<a id="zpm_action_button" href="' . $link . '" style="color: #fff; padding: 10px; text-decoration: none;">' . __('View Task', 'zephyr-project-manager') . '</a>';
		$html = Emails::email_template($header, $body, $footer);
		$sent = [];

		foreach ($assigneeEmails as $member) {
			if (in_array($member['email'], $sent)) continue;

			if (!Members::isNotificationEnabled($member, 'tasks') && !Members::isNotificationEnabled($member, 'task_assigned')) continue;

			Emails::send_email($member['email'], $subject, $html);
			$sent[] = $member['email'];
		}

		$header = __('New task has been created', 'zephyr-project-manager');
		$subject = __('New task has been created', 'zephyr-project-manager');
		$html = Emails::email_template($header, $body, $footer);

		if (Tasks::hasProject($task)) {
			$projectMembers = Projects::getAssignees(Projects::get_project($task->project));

			foreach ($projectMembers as $member) {
				if (in_array($member['email'], $sent)) continue;

				if (!Members::isNotificationEnabled($member, 'tasks')) continue;

				Emails::send_email($member['email'], $subject, $html);
				$sent[] = $member['email'];
			}
		}

		return $sent;
	}

	public static function sendTest($email) {
		$projects = Projects::get_projects();
		ob_start();

		$count = 0;

		foreach ($projects as $project) {
			// if (!Utillities::check_user_project_setting($member['id'], $project->id, 'weekly_update_email')) {
			// 	continue;
			// }

			$task_count = Tasks::get_project_task_count($project->id);
			$completed_tasks = Tasks::get_project_completed_tasks($project->id);
			$args = array(
				'project_id' => $project->id
			);
			$overdue_tasks = sizeof(Tasks::get_overdue_tasks($args));
			$pending_tasks = $task_count - $completed_tasks;
			$percent_complete = ($task_count !== 0) ? floor($completed_tasks / $task_count * 100) : '100';

?>
			<h3 id="project-title"><?php echo esc_html($project->name); ?></h3>
			<div class="tasks_section" style="margin-bottom: 30px;">
				<span class="task_item">
					<div class="task_count"><?php echo esc_html($task_count); ?></div>
					<div class="task_subject"><?php _e('Tasks', 'zephyr-project-manager'); ?></div>
				</span>
				<span class="task_item">
					<div class="task_count"><?php echo esc_html($completed_tasks); ?></div>
					<div class="task_subject"><?php _e('Completed', 'zephyr-project-manager'); ?></div>
				</span>
				<span class="task_item">
					<div class="task_count"><?php echo esc_html($pending_tasks); ?></div>
					<div class="task_subject"><?php _e('Pending', 'zephyr-project-manager'); ?></div>
				</span>
				<span class="task_item">
					<div class="task_count"><?php echo esc_html($percent_complete); ?>%</div>
					<div class="task_subject"><?php _e('Complete', 'zephyr-project-manager'); ?></div>
				</span>
			</div>
			<?php
			$count++;
		}

		$body = ob_get_clean();
		$link = esc_url(admin_url("/admin.php?page=zephyr_project_manager_projects"));

		if (zpmIsFrontendEnabled()) {
			$link = Utillities::get_frontend_url('action=projects');
		}

		$footer = '<button id="zpm_action_button" style="padding: 10px;"><a href="' . $link . '" style="color: #fff; text-decoration: none;">' . __('View in WordPress', 'zephyr-project-manager') . '</a></button>';
		$html = Emails::email_template(__('Weekly Updates', 'zephyr-project-manager'), $body, $footer);

		Emails::send_email($email, __('Weekly Updates', 'zephyr-project-manager'), $html);
	}
}
