<?php

/**
 * Template for displaying the 'task view/task editor' page
 */

if (!defined('ABSPATH')) {
	die;
}

use ZephyrProjectManager\Api\Emails;
use ZephyrProjectManager\Core\Task;
use ZephyrProjectManager\Core\Tasks;
use ZephyrProjectManager\Core\Projects;
use ZephyrProjectManager\Core\File;
use ZephyrProjectManager\Core\Members;
use ZephyrProjectManager\Core\Message;
use ZephyrProjectManager\Core\Utillities;
use ZephyrProjectManager\Base\BaseController;
use ZephyrProjectManager\Core\Categories;

$Tasks = new Tasks();
$BaseController = new BaseController();
$task_id = isset($_GET['task_id']) ? zpm_sanitize_int($_GET['task_id']) : '-1';
$this_task = ($Tasks->get_task($task_id) !== null) ? $Tasks->get_task($task_id) : '';
$projects = Projects::get_projects();

if (!is_object($this_task)) {
?>
	<p><?php _e('This task does not exist or has been deleted.', 'zephyr-project-manager'); ?></p>
<?php
	exit();
}

if (!Utillities::can_view_task($this_task)) {
?>
	<div class="zpm-notice"><?php _e('Sorry, you do not have access to this task.', 'zephyr-project-manager'); ?></div>
<?php
	exit();
}

if (isset($_GET['kanban'])) {
	$projectId = zpm_sanitize_int($_GET['kanban']);
	$base_url = admin_url("/admin.php?page=zephyr_project_manager_projects");
	$base_url .= '&action=edit_project&project=' . $projectId . '#project-tasks';
}

$general_settings = Utillities::general_settings();
$user = $BaseController->get_user_info($this_task->user_id);
$due_datetime = new DateTime($this_task->date_due);
$start_datetime = new DateTime($this_task->date_start);
$start_date = ($start_datetime->format('Y-m-d') !== '-0001-11-30') ? $start_datetime->format('Y-m-d H:i') : '';
$due_date = ($due_datetime->format('Y-m-d') !== '-0001-11-30') ? $due_datetime->format('Y-m-d H:i') : '';
$startDate = zpm_date($this_task->date_start, __('Not Set', 'zephyr-project-manager'), 'Y-m-d');
$dueDate = zpm_date($this_task->date_due, __('Not Set', 'zephyr-project-manager'), 'Y-m-d');
$priority = property_exists($this_task, 'priority') ? $this_task->priority : 'priority_none';
$priority_label = Utillities::get_priority_label($priority);
$priorities = Utillities::get_statuses('priority');
$statuses = Utillities::get_statuses('status');
$statuses = apply_filters('zpm/task/statuses', $statuses, $this_task);
$status = Utillities::get_status($priority);
$type = Tasks::get_type($this_task);
$days = Tasks::get_days($this_task);
$expires = Tasks::get_expiration_date($this_task);
$taskData = Tasks::get_task_data($this_task);
$recurrence_start = isset($taskData['start']) ? $taskData['start'] : '';
$recurrence_frequency = isset($taskData['frequency']) ? $taskData['frequency'] : '';
$task = new Task($this_task);
$subtasks = Tasks::get_subtasks($task->id);
$isSubtask = Tasks::isSubtask($this_task);
$pages = [];
$pages = apply_filters('zpm_task_pages', $pages, $task);
$parents = Tasks::getTaskParents($this_task);
$canEditTasks = Utillities::canEditTask($this_task);
$team = Members::get_team($this_task->team);
$project = Projects::get_project($this_task->project);
$taskStatus = Utillities::get_status($this_task->status);
$categories = Categories::get_categories();
$hasProject = Tasks::hasProject($this_task);

if (!$isSubtask) {
	$projectTasks = $hasProject ? Tasks::get_project_tasks($this_task->project, true) : Tasks::getTasks([ 'subtasks' => true ]);
} else {
	$rootTask = Tasks::getRoot($this_task);
	$hasProject = Tasks::hasProject($rootTask);
	$projectTasks = $hasProject ? Tasks::get_project_tasks($rootTask->project, true) : Tasks::getTasks([ 'subtasks' => true ]);
	$this_task->project = $rootTask->project;
}

$tasks = $projectTasks;

if (!$hasProject) {
	$tasks = Tasks::get_tasks();
}

$isCompleted = Tasks::isCompleted($this_task);

if (zpm_is_dev()) {
	$percentage = Tasks::getPercentage($this_task);
}

$filesEnabled = !$general_settings['disable_files_globally'];
$hasProject = Tasks::hasProject($this_task);
?>

<!-- Task Editor -->
<input type="hidden" id="zpm_js_task_id" value="<?php echo esc_attr($this_task->id); ?>" />
<input type="hidden" id="zpm-task-id" value="<?php echo esc_attr($this_task->id); ?>" />

<div class="zpm-breadcrumbs">
	<a href="<?php echo Utillities::getTasksURL(); ?>"><?php _e('Tasks', 'zephyr-project-manager'); ?></a>
	<span class="zpm-breadcrumb-separator">/</span>

	<?php if ($hasProject): ?>
		<?php
		$projectURL = admin_url("/admin.php?page=zephyr_project_manager_projects");
		$projectURL .= '&action=edit_project&project=' . $this_task->project . '#project-tasks';
		?>
		<a href="<?php echo $projectURL; ?>"><?php echo $project->name; ?></a>
		<span class="zpm-breadcrumb-separator">/</span>
	<?php endif; ?>

	<?php if (!empty($parents)) : ?>
		<?php foreach ($parents as $key => $parent) : ?>
			<?php $url = Tasks::task_url($parent->id); ?>

			<a href="<?php echo $url; ?>"><?php echo $parent->name; ?></a>
			<span class="zpm-breadcrumb-separator">/</span>
		<?php endforeach; ?>
	<?php endif; ?>

	<a href="#"><?php echo $task->name; ?></a>
</div>

<?php if (apply_filters('zpm_can_complete_task', true, $this_task)) : ?>
	<label for="zpm_task_id_<?php echo esc_attr($this_task->id); ?>" class="zpm-material-checkbox">
		<input type="checkbox" id="zpm_task_id_<?php echo esc_attr($this_task->id); ?>" name="zpm_task_id_<?php echo esc_attr($this_task->id); ?>" class="zpm_task_mark_complete zpm_toggle invisible" value="1" <?php echo $this_task->completed == '1' ? 'checked' : ''; ?> data-task-id="<?php echo esc_attr($this_task->id); ?>" aria-label="<?php esc_attr_e('Toggle task completion', 'zephyr-project-manager'); ?>">
		<span class="zpm-material-checkbox-label"></span>
	</label>
<?php endif; ?>

<h2 id="zpm_task_name_title" class="zpm_admin_page_title">
	<?php echo esc_html($this_task->name); ?>
	<?php if ($general_settings['display_task_id']) : ?>
		<?php echo '(#' . esc_html($task->id) . ')'; ?>
	<?php endif; ?>
</h2>

<span id="zpm-task-edit-priority-label" class="zpm-task-priority-bubble <?php echo esc_attr($priority); ?> <?php echo ($priority !== "priority_none" && $priority !== "" && !is_null($priority)) ? '' : 'zpm-label-hidden'; ?>" style="background: <?php echo esc_attr($status['color']); ?>; color: <?php echo $status['color'] !== '' ? '#fff' : ''; ?>"><?php echo esc_html($status['name']); ?></span>

<small class="zpm_title_information"><?php echo zpm_esc_html($Tasks->task_created_by($this_task->id)); ?></small>

<div id="zpm-task__header-buttons">
	<?php do_action('zpm_task_header_buttons', $this_task); ?>
	<?php echo Tasks::contextMenu($this_task->id); ?>
</div>

<div class="zpm_nav_holder zpm_body">
	<nav class="zpm_nav">
		<ul class="zpm_nav_list">
			<li id="zpm-overview-tab" class="zpm_nav_item zpm_nav_item_selected" data-zpm-tab="task-overview" role="tab" aria-selected="true"><?php _e('Overview', 'zephyr-project-manager'); ?></li>
			<li id="zpm-subtasks-tab" class="zpm_nav_item" data-zpm-tab="task-subtasks" role="tab" aria-selected="false"><?php _e('Subtasks', 'zephyr-project-manager'); ?></li>
			<li id="zpm-discussion-tab" class="zpm_nav_item" data-zpm-tab="task-discussion" role="tab" aria-selected="false"><?php _e('Discussion', 'zephyr-project-manager'); ?></li>

			<?php if ($filesEnabled): ?>
				<li id="zpm-files-tab" class="zpm_nav_item" data-zpm-tab="task-files" role="tab" aria-selected="false"><?php _e('Files', 'zephyr-project-manager'); ?></li>
			<?php endif; ?>

			<?php foreach ($pages as $page) : ?>
				<li class="zpm_nav_item" data-zpm-tab="<?php echo esc_attr($page['slug']); ?>"><?php echo zpm_esc_html($page['title']); ?></li>
			<?php endforeach; ?>
		</ul>
	</nav>
</div>

<div id="zpm_task_editor" class="zpm_body zpm_tab_pane zpm_tab_active zpm-form" data-task_id="<?php $this_task->id; ?>" data-zpm-tab="task-overview" role="tabpanel">
	<div class="container">
		<div id="zpm_task_editor_settings" class="col-md-6" role="form">

			<?php if ($canEditTasks) : ?>
				<!-- Task Name -->
				<div class="zpm_options_row">
					<div class="zpm-form__group">
						<input type="text" name="zpm_edit_task_name" id="zpm_edit_task_name" class="zpm-form__field" placeholder="<?php _e('Task Name', 'zephyr-project-manager'); ?>" value="<?php echo esc_attr($this_task->name); ?>">
						<label for="zpm_edit_task_name" class="zpm-form__label"><?php _e('Task Name', 'zephyr-project-manager'); ?></label>
					</div>
				</div>

				<div class="zpm-form__group">
					<textarea name="zpm_edit_project_description" id="zpm_edit_task_description" class="zpm-form__field zpm-auto-resize" placeholder="<?php _e('Task Description', 'zephyr-project-manager'); ?>"><?php echo esc_html(stripslashes($this_task->description)); ?></textarea>
					<label for="zpm_edit_task_description" class="zpm-form__label"><?php _e('Task Description', 'zephyr-project-manager'); ?></label>
				</div>

				<!-- Start Date -->
				<div class="zpm-form__group">
					<input type="text" name="zpm_edit_task_start_date" id="zpm_edit_task_start_date" class="zpm-form__field" placeholder="<?php _e('Start Date', 'zephyr-project-manager'); ?>" data-original="<?php esc_attr_e(date('Y-m-d', strtotime($this_task->date_start))); ?>" value="<?php echo esc_attr($startDate); ?>" data-start="<?php echo $hasProject && !is_null($project) ? date('Y-m-d', strtotime($project->date_start)) : ''; ?>">
					<label for="zpm_edit_task_start_date" class="zpm-form__label"><?php _e('Start Date', 'zephyr-project-manager'); ?></label>
				</div>

				<!-- Due Date -->
				<div class="zpm-form__group">
					<input type="text" name="zpm_edit_task_due_date" id="zpm_edit_task_due_date" class="zpm-form__field" placeholder="<?php _e('Due Date', 'zephyr-project-manager'); ?>" data-original="<?php esc_attr_e(date('Y-m-d', strtotime($this_task->date_due))); ?>" value="<?php echo esc_attr($dueDate); ?>">
					<label for="zpm_edit_task_due_date" class="zpm-form__label"><?php _e('Due Date', 'zephyr-project-manager'); ?></label>
				</div>

				<?php if (Utillities::getSetting('task_duration_enabled')): ?>
					<?php $duration = Tasks::getDuration($this_task); ?>
					<div class="zpm-form__group">
						<input type="text" name="zpm-edit-task--duration" id="zpm-edit-task--duration" data-ajax-name="duration" class="zpm-form__field" placeholder="<?php _e('Duration', 'zephyr-project-manager'); ?>" value="<?php esc_attr_e($duration); ?>" type="number">
						<label for="zpm-edit-task--duration" class="zpm-form__label"><?php _e('Duration', 'zephyr-project-manager'); ?></label>
					</div>
				<?php endif; ?>

				<?php if (Utillities::getSetting('task_blocking_enabled') || Utillities::getSetting('task_duration_enabled')): ?>
					<?php
					$estimatedDate = Tasks::getEstimatedCompletionDate($this_task);
					$estimatedOverdue = Utillities::isDateOverdue($estimatedDate);
					?>
					<div class="zpm-project-field zpm-project-field__due-date zpm-mt-2">
						<label><?php _e('Estimated Date of Completion', 'zephyr-project-manager'); ?>:</label>
						<p data-task-estimated-end class="<?php echo $estimatedOverdue && !$isCompleted ? 'zpm-overdue-text' : ''; ?>"><?php esc_html_e(zpm_date($estimatedDate)); ?></p>
					</div>
				<?php endif; ?>

				<!-- Select Assignee -->
				<label class="zpm_label" for="zpm_edit_task_assignee"><?php _e('Assignee', 'zephyr-project-manager'); ?></label>
				<select id="zpm_edit_task_assignee" class="zpm_input zpm-input-chosen" multiple data-placeholder="<?php _e('Select Assignees', 'zephyr-project-manager'); ?>">
					<?php $assignees = Tasks::get_assignees($this_task); ?>
					<?php foreach ($users as $user) : ?>
						<option <?php echo in_array($user['id'], $assignees) ? 'selected' : ''; ?> value="<?php echo esc_attr($user['id']); ?>"><?php echo esc_html($user['name']); ?></option>;
					<?php endforeach; ?>
				</select>

				<!-- Select Team -->
				<div class="zpm-edit-task-field__team">
					<label class="zpm_label" for="zpm-edit-task-team-selection"><?php _e('Team', 'zephyr-project-manager'); ?></label>
					<?php echo property_exists($this_task, 'team') ? Members::team_dropdown_html('zpm-edit-task-team-selection', $this_task->team) : Members::team_dropdown_html('zpm-edit-task-team-selection'); ?>
				</div>

				<!-- Select Project -->
				<label class="zpm_label" for="zpm_edit_task_project"><?php _e('Project', 'zephyr-project-manager'); ?></label>
				<select id="zpm_edit_task_project" class="zpm_input zpm-input-chosen">
					<option value="-1"><?php _e('Select Project', 'zephyr-project-manager'); ?></option>
					<?php foreach ($projects as $single_project) : ?>
						<option value="<?php echo esc_attr($single_project->id); ?>" <?php echo $this_task->project == $single_project->id ? 'selected' : ''; ?>><?php echo zpm_esc_html($single_project->name); ?></option>
					<?php endforeach; ?>
				</select>


				<!-- Blocking Tasks -->
				<?php if (Utillities::getSetting('task_blocking_enabled')) : ?>
					<?php $blockingTasks = Tasks::getBlockingTasks($this_task->id); ?>
					<label class="zpm_label" for="zpm-blocking-tasks"><?php _e('Blocking Tasks', 'zephyr-project-manager'); ?></label>
					<select id="zpm-blocking-tasks" data-blocking-tasks data-ajax-name="blockingTasks" class="zpm_input zpm-input-chosen zpm-multi-select" multiple placeholder="<?php _e('Select Blocking Tasks', 'zephyr-project-manager'); ?>">
						<?php foreach ($projectTasks as $projectTask) : ?>
							<?php if (Tasks::isCompleted($projectTask) || intval($projectTask->id) == intval($this_task->id)) continue; ?>
							<?php
								$blockingDueDate = Tasks::getEndDate($projectTask);
								$isBlockingDueDateValid = zpm_is_date_valid($blockingDueDate);
							?>
							<option value="<?php echo esc_attr($projectTask->id); ?>" <?php echo in_array($projectTask->id, $blockingTasks) ? 'selected' : ''; ?> data-due="<?php echo $isBlockingDueDateValid ? $blockingDueDate : ''; ?>"><?php echo zpm_esc_html($projectTask->name); ?></option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>

				<!-- Select Status -->
				<label class="zpm_label" for="zpm-edit-task__status"><?php _e('Status', 'zephyr-project-manager'); ?></label>
				<select id="zpm-edit-task__status" class="zpm_input zpm-input-chosen" data-original="<?php esc_attr_e($this_task->status); ?>">
					<option value="-1"><?php _e('Select Status', 'zephyr-project-manager'); ?></option>
					<?php foreach ($statuses as $slug => $value) : ?>
						<option value="<?php echo esc_attr($slug); ?>" <?php echo esc_attr($this_task->status) == $slug ? 'selected' : ''; ?>><?php echo esc_html($value['name']); ?></option>
					<?php endforeach; ?>
				</select>

				<!-- Select Category -->
				<div class="zpm-new-task-task--categories">
					<label class="zpm_label" for="zpm-new-task--categories"><?php _e('Categories', 'zephyr-project-manager'); ?></label>
					<select id="zpm-new-task--categories" data-position="top" class="zpm_input zpm-input-chosen zpm-multi-select" multiple data-placeholder="<?php _e('Select Categories', 'zephyr-project-manager'); ?>">
						<option value="-1"><?php _e('Select Categories', 'zephyr-project-manager'); ?></option>
						<?php foreach ($categories as $category) : ?>
							<option value="<?php echo esc_attr($category->id); ?>" <?php echo in_array($category->id, (array) maybe_unserialize($this_task->categories)) ? 'selected' : ''; ?>><?php echo esc_html($category->name); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<!-- Select Parent Task -->
				<label class="zpm_label"><?php _e('Parent Task', 'zephyr-project-manager'); ?></label>
				<select class="zpm_input zpm-input-chosen zpm-chosen" data-ajax-name="parentID">
					<option value="-1"><?php _e('None', 'zephyr-project-manager'); ?></option>
					<?php foreach ($tasks as $taskItem) : ?>
						<?php if (intval($taskItem->id) == $this_task->id) continue; ?>
						<option value="<?php esc_attr_e($taskItem->id); ?>" <?php echo intval($taskItem->id) == intval($this_task->parent_id) ? 'selected' : ''; ?>><?php esc_html_e($taskItem->name); ?></option>
					<?php endforeach; ?>
				</select>

				<?php do_action('zpm_after_task_settings', $task_id); ?>

				<div id="zpm-edit-task__recurrence" class="zpm-task-recurrence__wrap">
					<div id="zpm-edit-task__recurrence-selection" class="zpm-recurrence-selection">
						<label class="zpm_label"><?php _e('Set Recurrence', 'zephyr-project-manager'); ?></label>
						<select id="zpm-edit-task__recurrence-select" class="zpm_input zpm-input-chosen">
							<option value="default" <?php echo $type == 'default' ? 'selected' : ''; ?>><?php _e('None', 'zephyr-project-manager'); ?></option>
							<option value="daily" <?php echo $type == 'daily' ? 'selected' : ''; ?>><?php _e('Daily', 'zephyr-project-manager'); ?></option>
							<option value="weekly" <?php echo $type == 'weekly' ? 'selected' : ''; ?>><?php _e('Weekly', 'zephyr-project-manager'); ?></option>
							<option value="monthly" <?php echo $type == 'monthly' ? 'selected' : ''; ?>><?php _e('Monthly', 'zephyr-project-manager'); ?></option>
							<option value="annually" <?php echo $type == 'annually' ? 'selected' : ''; ?>><?php _e('Annually', 'zephyr-project-manager'); ?></option>
						</select>

						<!-- Daily Reccurence Settings -->
						<div class="zpm-edit-task__recurrence-section" data-section="daily" style="<?php echo $type !== 'daily' ? 'display: none;' : ''; ?>">

							<?php $ammount = 32; ?>
							<div class="zpm-form__inline">
								<span><?php _e('Repeat every', 'zephyr-project-manager'); ?> </span>
								<span>
									<input type="number" class="zpm-form-control zpm_input" data-ajax-name="frequency" value="<?php echo !empty($recurrence_frequency) ? $recurrence_frequency : 1; ?>" />
								</span>
								<span> <?php _e('day', 'zephyr-project-manager'); ?></span>
							</div>

							<div class="zpm-edit-task__recurrence-settings">

								<select id="zpm-edit-task__recurrence-daily" class="zpm-multi-select" multiple data-placeholder="<?php _e('Days to Repeat', 'zephyr-project-manager'); ?>">
									<option value="0" <?php echo in_array('0', $days) ? 'selected' : ''; ?>><?php _e('Monday', 'zephyr-project-manager'); ?></option>
									<option value="1" <?php echo in_array('1', $days) ? 'selected' : ''; ?>><?php _e('Tuesday', 'zephyr-project-manager'); ?></option>
									<option value="2" <?php echo in_array('2', $days) ? 'selected' : ''; ?>><?php _e('Wednesday', 'zephyr-project-manager'); ?></option>
									<option value="3" <?php echo in_array('3', $days) ? 'selected' : ''; ?>><?php _e('Thursday', 'zephyr-project-manager'); ?></option>
									<option value="4" <?php echo in_array('4', $days) ? 'selected' : ''; ?>><?php _e('Friday', 'zephyr-project-manager'); ?></option>
									<option value="5" <?php echo in_array('5', $days) ? 'selected' : ''; ?>><?php _e('Saturday', 'zephyr-project-manager'); ?></option>
									<option value="6" <?php echo in_array('6', $days) ? 'selected' : ''; ?>><?php _e('Sunday', 'zephyr-project-manager'); ?></option>
								</select>
							</div>

							<label class="zpm_label"><?php _e('Starts On:', 'zephyr-project-manager'); ?></label>
							<input class="zpm-datepicker zpm_input" id="zpm-edit-task__recurrence-start-date" value="<?php echo !empty($recurrence_start) ? esc_attr($recurrence_start) : ''; ?>" data-ajax-name="recurrence-start" />

							<label class="zpm_label"><?php _e('Expires On (leave empty for no expiration):', 'zephyr-project-manager'); ?></label>
							<input class="zpm-datepicker zpm_input" id="zpm-edit-task__recurrence-expiration-date" value="<?php echo !empty($expires) ? esc_attr($expires) : ''; ?>" data-ajax-name="recurrence-end" />
						</div>

						<!-- Weekly Reccurence Settings -->
						<div class="zpm-edit-task__recurrence-section" data-section="weekly" style="<?php echo $type !== 'weekly' ? 'display: none;' : ''; ?>">

							<?php $ammount = 11; ?>
							<div class="zpm-form__inline">
								<span><?php _e('Repeat every', 'zephyr-project-manager'); ?> </span>
								<span>
									<select class="zpm-select zpm-chosen-select zpm_input zpm-input-chosen" data-ajax-name="frequency">
										<option value="1">Single</option>
										<?php for ($i = 2; $i < $ammount; $i++) : ?>
											<option value="<?php echo esc_attr($i); ?>" <?php echo $recurrence_frequency == $i ? 'selected' : ''; ?>><?php echo esc_html(Utillities::ordinal($i)); ?></option>
										<?php endfor; ?>
									</select>
								</span>
								<span> <?php _e('week', 'zephyr-project-manager'); ?></span>
							</div>

							<label class="zpm_label"><?php _e('Starts On:', 'zephyr-project-manager'); ?></label>
							<input class="zpm-datepicker zpm_input" id="zpm-edit-task__recurrence-start-date-weekly" value="<?php echo !empty($recurrence_start) ? esc_html($recurrence_start) : ''; ?>" data-ajax-name="recurrence-start" />

							<label class="zpm_label"><?php _e('Expires On (leave empty for no expiration):', 'zephyr-project-manager'); ?></label>
							<input class="zpm-datepicker zpm_input" id="zpm-edit-task__recurrence-expiration-date-weekly" value="<?php echo !empty($expires) ? esc_html($expires) : ''; ?>" />
						</div>

						<!-- Monthly Reccurence Settings -->
						<div class="zpm-edit-task__recurrence-section" data-section="monthly" style="<?php echo $type !== 'monthly' ? 'display: none;' : ''; ?>">

							<?php $ammount = 13; ?>
							<div class="zpm-form__inline">
								<span><?php _e('Repeat every', 'zephyr-project-manager'); ?> </span>
								<span>
									<select class="zpm-select zpm-chosen-select zpm_input zpm-input-chosen" data-ajax-name="frequency">
										<option value="1">Single</option>
										<?php for ($i = 2; $i < $ammount; $i++) : ?>
											<option value="<?php echo esc_attr($i); ?>" <?php echo $recurrence_frequency == $i ? 'selected' : ''; ?>><?php echo esc_html(Utillities::ordinal($i)); ?></option>
										<?php endfor; ?>
									</select>
								</span>
								<span> <?php _e('month', 'zephyr-project-manager'); ?></span>
							</div>

							<label class="zpm_label"><?php _e('Starts On:', 'zephyr-project-manager'); ?></label>
							<input class="zpm-datepicker zpm_input" id="zpm-edit-task__recurrence-start-date-monthly" value="<?php echo !empty($recurrence_start) ? esc_attr($recurrence_start) : ''; ?>" data-ajax-name="recurrence-start" />

							<label class="zpm_label"><?php _e('Expires On (leave empty for no expiration):', 'zephyr-project-manager'); ?></label>
							<input class="zpm-datepicker zpm_input" id="zpm-edit-task__recurrence-expiration-date-monthly" value="<?php echo !empty($expires) ? esc_attr($expires) : ''; ?>" />
						</div>

						<!-- Annual Reccurence Settings -->
						<div class="zpm-edit-task__recurrence-section" data-section="annually" style="<?php echo $type !== 'annually' ? 'display: none;' : ''; ?>">

							<?php $ammount = 13; ?>
							<div class="zpm-form__inline">
								<span><?php _e('Repeat every', 'zephyr-project-manager'); ?> </span>
								<span>
									<select class="zpm-select zpm-chosen-select zpm_input zpm-input-chosen" data-ajax-name="frequency">
										<option value="1">Single</option>
										<?php for ($i = 2; $i < $ammount; $i++) : ?>
											<option value="<?php echo esc_attr($i); ?>" <?php echo $recurrence_frequency == $i ? 'selected' : ''; ?>><?php echo esc_html(Utillities::ordinal($i)); ?></option>
										<?php endfor; ?>
									</select>
								</span>
								<span> <?php _e('year', 'zephyr-project-manager'); ?></span>
							</div>

							<label class="zpm_label"><?php _e('Starts On:', 'zephyr-project-manager'); ?></label>
							<input class="zpm-datepicker zpm_input" id="zpm-edit-task__recurrence-start-date-annually" value="<?php echo !empty($recurrence_start) ? esc_attr($recurrence_start) : ''; ?>" data-ajax-name="recurrence-start" />

							<label class="zpm_label"><?php _e('Expires On (leave empty for no expiration):', 'zephyr-project-manager'); ?></label>
							<input class="zpm-datepicker zpm_input" id="zpm-edit-task__recurrence-expiration-date-annual" value="<?php echo !empty($expires) ? esc_attr($expires) : ''; ?>" />
						</div>
					</div>
				</div>

				<?php if (!BaseController::is_pro()) : ?>
					<button id="zpm_add_custom_field_pro" class="zpm_button" data-zpm-pro-upsell="true">
						<div class="zpm-pro-notice">
							<span class="lnr lnr-cross zpm-close-pro-notice"></span>
							<?php _e('This option is only available in the Pro version.', 'zephyr-project-manager'); ?> <br /><a class="zpm-purchase-link zpm_link" href="<?php echo esc_url(ZEPHYR_PRO_LINK); ?>" target="_blank"><span class="zpm-purchase-icon lnr lnr-star"></span><?php _e('Purchase the Pro Add-On Now', 'zephyr-project-manager'); ?></a>.
						</div>
						<?php _e('Add Custom Field', 'zephyr-project-manager'); ?>


					</button>
				<?php endif; ?>
			<?php else : ?>
				<div class="zpm_options_row">
					<div class="zpm-form__group">
						<div class="zpm-form-group__value"><?php esc_html_e($this_task->name) ?></div>
						<label for="zpm_edit_task_name" class="zpm-form__label"><?php _e('Task Name', 'zephyr-project-manager'); ?></label>
					</div>
				</div>

				<div class="zpm-form__group">
					<div class="zpm-form-group__value"><?php echo zpm_esc_html($this_task->description) ?></div>
					<label for="zpm_edit_task_description" class="zpm-form__label"><?php _e('Task Description', 'zephyr-project-manager'); ?></label>
				</div>

				<!-- Start Date -->
				<div class="zpm-form__group">
					<div class="zpm-form-group__value"><?php !empty($start_date) ? esc_html_e($start_date) : _e('Not Set', 'zephyr-project-manager') ?></div>
					<label for="zpm_edit_task_start_date" class="zpm-form__label"><?php _e('Start Date', 'zephyr-project-manager'); ?></label>
				</div>

				<!-- Due Date -->
				<div class="zpm-form__group">
					<div class="zpm-form-group__value"><?php !empty($due_date) ? esc_html_e($due_date) : _e('Not Set', 'zephyr-project-manager') ?></div>
					<label for="zpm_edit_task_due_date" class="zpm-form__label"><?php _e('Due Date', 'zephyr-project-manager'); ?></label>
				</div>

				<?php if (Utillities::getSetting('task_duration_enabled')): ?>
					<?php $duration = Tasks::getDuration($this_task); ?>
					<div class="zpm-form__group">
						<div class="zpm-form-group__value"><?php esc_html_e($duration); ?></div>
						<label for="zpm_edit_task_due_date" class="zpm-form__label"><?php _e('Duration', 'zephyr-project-manager'); ?></label>
					</div>
				<?php endif; ?>

				<?php if (Utillities::getSetting('task_blocking_enabled') || Utillities::getSetting('task_duration_enabled')): ?>
					<?php
					$estimatedDate = Tasks::getEstimatedCompletionDate($this_task);
					$estimatedOverdue = Utillities::isDateOverdue($estimatedDate);
					?>
					<div class="zpm-form__group">
						<div class="zpm-form-group__value <?php echo $estimatedOverdue && !$isCompleted ? 'zpm-overdue-text' : ''; ?>"><?php esc_html_e(zpm_date($estimatedDate)); ?></div>
						<label for="zpm_edit_task_due_date" class="zpm-form__label"><?php _e('Estimated Date of Completion', 'zephyr-project-manager'); ?></label>
					</div>
				<?php endif; ?>

				<div class="zpm-form__group">
					<div class="zpm-form-group__value"><?php echo Tasks::get_assignee_string($this_task, __('None', 'zephyr-project-manager')) ?></div>
					<label for="zpm_edit_task_due_date" class="zpm-form__label"><?php _e('Assignees', 'zephyr-project-manager'); ?></label>
				</div>

				<div class="zpm-form__group">
					<div class="zpm-form-group__value"><?php echo !is_null($team) ? esc_html($team['name']) : _e('None', 'zephyr-project-manager') ?></div>
					<label for="zpm_edit_task_due_date" class="zpm-form__label"><?php _e('Team', 'zephyr-project-manager'); ?></label>
				</div>

				<div class="zpm-form__group">
					<div class="zpm-form-group__value"><?php echo !is_null($project) ? esc_html($project->name) : _e('None', 'zephyr-project-manager') ?></div>
					<label for="zpm_edit_task_due_date" class="zpm-form__label"><?php _e('Project', 'zephyr-project-manager'); ?></label>
				</div>

				<div class="zpm-form__group zpm-mb-2">
					<div class="zpm-form-group__value"><?php echo !is_null($taskStatus) ? esc_html($taskStatus['name']) : _e('None', 'zephyr-project-manager') ?></div>
					<label for="zpm_edit_task_due_date" class="zpm-form__label"><?php _e('Status', 'zephyr-project-manager'); ?></label>
				</div>
			<?php endif; ?>

			<?php if ($canEditTasks) : ?>
				<a id="zpm_save_changes_task" type="submit" name="zpm_save_changes_task" class="zpm_button" data-task-id="<?php echo esc_attr($this_task->id); ?>"><?php _e('Save Changes', 'zephyr-project-manager'); ?></a>
			<?php endif; ?>

			<?php if ($hasProject): ?>
				<?php
					$base_url = admin_url("/admin.php?page=zephyr_project_manager_projects");
					$base_url .= '&action=edit_project&project=' . $this_task->project . '#project-tasks';
				?>
				<a class="zpm_button" href="<?php echo esc_url($base_url); ?>" id="zpm_back_to_projects"><?php _e('Back to Tasks', 'zephyr-project-manager'); ?></a>
			<?php else: ?>
				<a class="zpm_button" href="<?php echo esc_url($base_url); ?>" id="zpm_back_to_projects"><?php _e('Back to Tasks', 'zephyr-project-manager'); ?></a>
			<?php endif; ?>

			<?php if (Tasks::hasParent($this_task)) : ?>
				<a class="zpm_button" href="<?php echo esc_url(Utillities::getTaskLink($this_task->parent_id)); ?>" id="zpm_back_to_projects"><?php _e('Go To Parent Task', 'zephyr-project-manager'); ?></a>
			<?php endif; ?>

			<?php if (Tasks::hasProject($this_task)) : ?>
				<a id="zpm-go-to-project-btn" class="zpm_button" href="<?php echo esc_url(Utillities::getProjectLink($this_task->project)); ?>" id="zpm_back_to_projects"><?php _e('Go To Project', 'zephyr-project-manager'); ?></a>
			<?php endif; ?>

			<?php if (Utillities::canUploadFiles()) : ?>
				<a id="zpm-task-single__add-files-btn" name="zpm-task-single__add-files-btn" class="zpm_button" data-task-id="<?php echo esc_attr($this_task->id); ?>"><?php _e('Add Files', 'zephyr-project-manager'); ?></a>
			<?php endif; ?>

			<?php if ($canEditTasks) : ?>
				<?php do_action('zpm_edit_task_buttons'); ?>

				<input type="hidden" id="zpm-edit-task-priority-value" value="<?php echo esc_attr($priority); ?>" />
				<span id="zpm-edit-task-priority" class="zpm_button zpm_button_secondary" zpm-toggle-dropdown="zpm-edit-task-priority-dropdown" data-priority="<?php echo esc_attr($priority); ?>" style="background: <?php echo esc_attr($status['color']); ?> !important; color: <?php echo $status['color'] !== '' ? '#fff !important' : ''; ?>"><span class="zpm-priority-name"><?php echo isset($status['name']) && $status['name'] !== "" ? __('Priority', 'zephyr-project-manager') . ': ' . esc_html($status['name']) : __('Set Priority', 'zephyr-project-manager'); ?></span>

					<div id="zpm-edit-task-priority-dropdown" class="zpm-dropdown">

						<div class="zpm-dropdown-item zpm-edit-task-priority" data-value="priority_none" data-color="#f9f9f9"><span class="zpm-priority-indicator zpm-color-none"></span><?php _e('None', 'zephyr-project-manager'); ?></div>

						<?php foreach ($priorities as $slug => $priority) : ?>
							<div class="zpm-dropdown-item zpm-edit-task-priority" data-value="<?php echo esc_attr($slug); ?>" data-color="<?php echo esc_html($priority['color']); ?>">

								<span class="zpm-priority-indicator <?php echo esc_attr($slug); ?>" style="background-color: <?php echo esc_html($priority['color']); ?>"></span>
								<span class="zpm-priority-picker__name"><?php echo esc_html($priority['name']); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</span>
			<?php endif; ?>


			<div class="zpm_project_options">

				<span id="zpm-copy-task-shortcode" title="<?php _e('Copy Shortcode', 'zephyr-project-manager'); ?> [zephyr_task id='<?php echo esc_attr($this_task->id); ?>']" class="zpm_circle_option_btn" data-task-id="<?php echo esc_attr($this_task->id); ?>" data-shortcode="[zephyr_task id='<?php echo esc_attr($this_task->id); ?>']">
					<div class="lnr lnr-code"></div>
				</span>

				<!-- <span id="zpm_like_task_btn" title="<?php _e('Like Task', 'zephyr-project-manager'); ?>" class="zpm_circle_option_btn <?php echo (in_array($this_task->id, (array) $liked_tasks)) ? 'zpm_liked' : ''; ?>" data-task-id="<?php echo esc_attr($this_task->id); ?>">
					<div class="lnr lnr-thumbs-up"></div>
				</span> -->
			</div>
		</div>
	</div>
</div>

<!-- Task Subtasks -->
<div id="zpm-subtasks-tabpanel" class="zpm_body zpm_tab_pane" data-zpm-tab="task-subtasks" role="tabpanel">
	<h3><?php _e('Subtasks', 'zephyr-project-manager'); ?></h3>

	<?php if (sizeof((array) $subtasks) <= 0) : ?>
		<p id="zpm-no-subtasks" class="zpm-no-results-error"><?php _e('No subtasks created.', 'zephyr-project-manager'); ?></p>
	<?php endif; ?>

	<div id="zpm_task_editor_subtasks" class="col-md-6">
		<ul id="zpm-subtask-list" class="zpm-subtasks-container" data-task-id="<?php esc_attr_e($this_task->id); ?>">
			<?php foreach ((array) $subtasks as $subtask) : ?>
				<?php echo Tasks::subtaskItemHtml($subtask); ?>
			<?php endforeach; ?>
		</ul>

		<?php if (Utillities::can_create_tasks()) : ?>
			<button id="zpm_add_new_subtask" class="zpm_button" data-project="<?php echo $this_task->project; ?>"><?php _e('New Subtask', 'zephyr-project-manager'); ?></button>
		<?php endif; ?>
	</div>
</div>

<!-- Task Comments -->
<div id="zpm_edit_task_comments" class="zpm_body zpm_tab_pane" data-zpm-tab="task-discussion" role="tabpanel">
	<h3><?php _e('Discussion', 'zephyr-project-manager'); ?></h3>
	<div class="zpm_task_comments" data-task-id="<?php echo esc_attr($this_task->id); ?>">
		<?php $comments = $task->getComments(); ?>
		<?php foreach ($comments as $comment) : ?>
			<?php echo $comment->html(); ?>
		<?php endforeach; ?>
	</div>

	<!-- Task Chat Box -->
	<div class="zpm_chat_box_section">
		<div class="zpm_chat_box">
			<div id="zpm_text_editor_wrap">
				<!-- <textarea id="zpm_chat_message" contenteditable="true" placeholder="<?php _e('Write comment...', 'zephyr-project-manager'); ?>"></textarea> -->
				<div id="zpm_chat_message" contenteditable="true" placeholder="<?php _e('Write comment...', 'zephyr-project-manager'); ?>"></div>
				<div class="zpm_editor_toolbar">
					<a href="#" data-command='addCode'><i class='lnr lnr-code'></i></a>
					<a href="#" data-command='createlink'><i class='lnr lnr-link'></i></a>
					<a href="#" data-command='undo'><i class='lnr lnr-undo'></i></a>
				</div>
			</div>
			<div class="zpm_chat_box_footer">
				<?php do_action('zpm_task_discussion_buttons', $this_task); ?>
				<?php if (Utillities::canUploadFiles()) : ?>
					<button data-task-id="<?php echo esc_attr($this_task->id); ?>" id="zpm_task_chat_files" class="zpm_task_chat_files zpm_button"><?php _e('Upload Files', 'zephyr-project-manager'); ?></button>
				<?php endif; ?>
				<button data-task-id="<?php echo esc_attr($this_task->id); ?>" id="zpm_task_chat_comment" data-text="<?php esc_attr_e('Comment', 'zephyr-project-manager'); ?>" data-loading-text="<?php esc_attr_e('Sending...', 'zephyr-project-manager'); ?>" class="zpm_button"><?php _e('Comment', 'zephyr-project-manager'); ?></button>
				<div id="zpm_chat_attachments">
				</div>
			</div>
		</div>
	</div>
</div>
<!-- End Task Comments -->

<!-- Files -->
<div id="zpm-task__files" class="zpm_body zpm_tab_pane zpm-tab__content" data-zpm-tab="task-files" role="tabpanel">
	<div class="zpm-flex zpm-flex-center zpm-flex-space-between">
		<h3><?php _e('Files', 'zephyr-project-manager'); ?></h3>
		<?php if (Utillities::canUploadFiles()): ?>
			<button class="zpm_button" data-upload-task-file-button data-text="<?php esc_attr_e('Upload Files', 'zephyr-project-manager'); ?>" data-loading-text="<?php esc_attr_e('Uploading...', 'zephyr-project-manager'); ?>"><?php _e('Upload Files', 'zephyr-project-manager'); ?></button>
		<?php endif; ?>
	</div>

	<?php $attachments = Tasks::get_task_attachments($this_task->id); ?>

	<div class="zpm-files__container" data-files-container>
		<?php foreach ($attachments as $attachment) : ?>
			<?php $file = new File($attachment); ?>
			<?php echo $file->html(); ?>
		<?php endforeach; ?>
	</div>

	<?php if (empty($attachments)) : ?>
		<p class="zpm-error zpm-error__subtle" data-no-files-message><?php _e('There are no files yet.', 'zephyr-project-manager'); ?></p>
	<?php endif; ?>

</div>

<?php foreach ($pages as $page) : ?>
	<div id="zpm_edit_task_comments" class="zpm_body zpm_tab_pane" data-zpm-tab="<?php echo esc_attr($page['slug']); ?>">
		<h3><?php echo esc_html($page['title']); ?></h3>
		<?php echo zpm_esc_html($page['content']); ?>
	</div>
<?php endforeach; ?>

<?php
do_action('zpm_after_task_page');
?>