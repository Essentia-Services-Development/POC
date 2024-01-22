<?php

/**
 * Template for displaying the New Task modal
 */

if (!defined('ABSPATH')) {
	die;
}

use ZephyrProjectManager\Zephyr;
use ZephyrProjectManager\Core\Tasks;
use ZephyrProjectManager\Core\Members;
use ZephyrProjectManager\Core\Projects;
use ZephyrProjectManager\Core\Utillities;
use ZephyrProjectManager\Base\BaseController;
use ZephyrProjectManager\Core\Categories;
use ZephyrProjectManager\ZephyrProjectManager;

$manager = ZephyrProjectManager::get_instance();
$projects = Projects::get_projects();
$args = array('can_zephyr' => true);
$users = Members::get_zephyr_members(); //$manager::get_users(true, $args);
$date = date('Y-m-d');
$userID = get_current_user_id();
$statuses = Utillities::get_statuses('status');
$priorities = Utillities::get_statuses('priority');
$general_settings = Utillities::general_settings();
$extra_classes = $general_settings['hide_default_task_fields'] == '1' ? 'zpm-hide-default-fields' : '';
$defaultProject = isset($general_settings['default_project']) ? $general_settings['default_project'] : '-1';
$defaultAssignee = isset($general_settings['default_assignee']) ? $general_settings['default_assignee'] : '-1';

if ($defaultAssignee == 'current') $defaultAssignee = $userID;

$categories = Categories::get_categories();
$isProject = zpm_is_single_project();
$projectID = zpm_get_single_project_id();
$tasks = !$isProject ? Tasks::getTasks(['subtasks' => true]) : Tasks::get_project_tasks($projectID, true);
$project = $isProject ? Projects::get_project($projectID) : [];
$createAssignedProjectTasksOnly = Utillities::hasPerm('create_tasks_in_assigned_projects_only');
$isScrum = $isProject && Projects::isScrum($project) ? true : false;

if ($isProject) {
	$statuses = apply_filters('zpm/project/statuses', $statuses, $project);
}
$defaultStart = '';
$defaultDue = '';

if ($general_settings['use_current_dates_as_default']) {
	$defaultStart = date('Y-m-d');
	$defaultDue = date('Y-m-d');
}
?>

<div id="zpm_create_task" class="zpm-modal <?php echo esc_attr($extra_classes); ?> zpm-form" role="dialog" aria-labelledby="zpm_task_add_new" aria-modal="true" aria-hidden="true">
	<h5 class="zpm_modal_header"><?php echo !$isScrum ? __('New Task', 'zephyr-project-manager') : __('New User Story', 'zephyr-project-manager'); ?></h5>
	<?php echo apply_filters('zpm_new_task_before', ''); ?>
	<div class="zpm_modal_body" role="form" aria-label="<?php echo !$isScrum ? __('New Task', 'zephyr-project-manager') : __('New User Story', 'zephyr-project-manager'); ?>">
		<div class="zpm_modal_content">
			<div class="zpm-form__group zpm-new-task-field__name">
				<input type="text" name="zpm_new_task_name" id="zpm_new_task_name" class="zpm-form__field" placeholder="<?php echo !$isScrum ? __('Task Name', 'zephyr-project-manager') : __('User Story Name', 'zephyr-project-manager'); ?>" autocomplete="off">
				<label for="zpm_new_task_name" class="zpm-form__label"><?php echo !$isScrum ? __('Task Name', 'zephyr-project-manager') : __('User Story Name', 'zephyr-project-manager'); ?></label>
			</div>

			<div class="zpm-form__group zpm-new-task-description-field">
				<textarea type="text" name="zpm_new_task_description" id="zpm_new_task_description" class="zpm-form__field" placeholder="<?php echo !$isScrum ? __('Task Description', 'zephyr-project-manager') : __('User Story Description', 'zephyr-project-manager'); ?>" autocomplete="off"></textarea>
				<label for="zpm_new_task_description" class="zpm-form__label"><?php echo !$isScrum ? __('Task Description', 'zephyr-project-manager') : __('User Story Description', 'zephyr-project-manager'); ?></label>
			</div>

			<?php if (!$isProject) : ?>
				<div class="zpm-new-task-field__project">
					<label class="zpm_label" for="zpm_new_task_project"><?php echo !$isScrum ? __('Project', 'zephyr-project-manager') : __('Sprint', 'zephyr-project-manager'); ?></label>
					<select id="zpm_new_task_project">
						<option value="-1" <?php echo $defaultProject == '-1' ? 'selected' : ''; ?>><?php _e('Select Project', 'zephyr-project-manager'); ?></option>
						<?php foreach ($projects as $project): ?>
							<?php if ($createAssignedProjectTasksOnly && (!Projects::isAssignee($project) && !Projects::isTeamMember($project, $userID))) continue; ?>
							<?php if (Projects::has_project_access($project)) : ?>
								<option value="<?php echo esc_attr($project->id); ?>" <?php echo $defaultProject == $project->id ? 'selected' : ''; ?>><?php echo esc_html($project->name); ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</div>
			<?php else : ?>
				<input type="hidden" id="zpm_new_task_project" value="<?php esc_attr_e($projectID) ?>" />
			<?php endif; ?>

			<div class="zpm-new-task-field__assignee">
				<label class="zpm_label" for="zpm_new_task_assignee"><?php _e('Assignee', 'zephyr-project-manager'); ?></label>
				<select id="zpm_new_task_assignee" multiple data-placeholder="<?php _e('Select Assignees', 'zephyr-project-manager'); ?>" data-default="<?php echo $defaultAssignee; ?>">
					<?php foreach ($users as $user) : ?>
						<option value="<?php echo esc_attr($user['id']); ?>" <?php echo $defaultAssignee == $user['id'] ? 'selected' : ''; ?>><?php echo esc_html($user['name']); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="zpm-new-task-field__team">
				<label class="zpm_label" for="zpm-new-task-team-selection"><?php _e('Team', 'zephyr-project-manager'); ?></label>
				<?php echo Members::team_dropdown_html('zpm-new-task-team-selection'); ?>
			</div>

			<?php echo apply_filters('zpm_new_task_fields', ''); ?>
			<?php do_action('zpm/tasks/modal/create'); ?>

			<div class="zpm_options_container zpm-new-task-field__dates">
				<span class="zpm_options_col zpm-new-task-field__start-date">
					<div class="zpm-form__group">
						<input type="text" autocomplete="off" name="zpm_new_task_start_date" id="zpm_new_task_start_date" class="zpm-form__field" data-default="<?php echo $defaultStart; ?>" value="<?php echo $defaultStart; ?>" placeholder="<?php _e('Start Date', 'zephyr-project-manager'); ?>" data-start="<?php echo $isProject ? date('Y-m-d', strtotime($project->date_start)) : ''; ?>">
						<label for="zpm_new_task_start_date" class="zpm-form__label"><?php _e('Start Date', 'zephyr-project-manager'); ?></label>
					</div>
				</span>

				<span class="zpm_options_col zpm-new-task-field__due-date">
					<div class="zpm-form__group">
						<input type="text" autocomplete="off" name="zpm_new_task_due_date" id="zpm_new_task_due_date" class="zpm-form__field" data-default="<?php echo $defaultDue; ?>" value="<?php echo $defaultDue; ?>" placeholder="<?php _e('Due Date', 'zephyr-project-manager'); ?>">
						<label for="zpm_new_task_due_date" class="zpm-form__label"><?php _e('Due Date', 'zephyr-project-manager'); ?></label>
					</div>
				</span>

				<?php if ($general_settings['task_duration_enabled']) : ?>
					<span class="zpm_options_col zpm-new-task-field__duration">
						<div class="zpm-form__group">
							<input type="number" autocomplete="off" name="zpm-new-task-duration" id="zpm-new-task-duration" class="zpm-form__field" placeholder="<?php _e('Duration', 'zephyr-project-manager'); ?>" data-ajax-name="duration">
							<label for="zpm-new-task-duration" class="zpm-form__label"><?php _e('Duration', 'zephyr-project-manager'); ?></label>
						</div>
					</span>
				<?php endif; ?>
			</div>

			<!-- Select Status -->
			<div class="zpm-new-task-field__status">
				<label class="zpm_label" for="zpm-new-task__status"><?php _e('Status', 'zephyr-project-manager'); ?></label>
				<select id="zpm-new-task__status" data-position="top" class="zpm_input zpm-input-chosen" data-default="not_started">
					<option value="-1"><?php _e('Select Status', 'zephyr-project-manager'); ?></option>
					<?php foreach ($statuses as $slug => $status) : ?>
						<option value="<?php esc_attr_e($slug); ?>" <?php echo $slug == Utillities::getSetting('default_status') ? 'selected' : ''; ?>><?php esc_html_e($status['name']); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<!-- Select Category -->
			<div class="zpm-new-task-task--categories">
				<label class="zpm_label" for="zpm-new-task--categories"><?php _e('Category', 'zephyr-project-manager'); ?></label>
				<select id="zpm-new-task--categories" data-position="top" class="zpm_input zpm-input-chosen zpm-multi-select" multiple data-placeholder="<?php _e('Select Categories', 'zephyr-project-manager'); ?>">
					<option value="-1"><?php _e('Select Categories', 'zephyr-project-manager'); ?></option>
					<?php foreach ($categories as $category) : ?>
						<option value="<?php echo esc_attr($category->id); ?>"><?php echo esc_html($category->name); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<!-- Blocking Tasks -->
			<?php if (Utillities::getSetting('task_blocking_enabled')) : ?>
				<div class="zpm-new-task--blocking-tasks">
					<label class="zpm_label" for="zpm-blocking-tasks"><?php echo !$isScrum ? __('Blocking Tasks', 'zephyr-project-manager') : __('Blocking User Stories', 'zephyr-project-manager'); ?></label>
					<select id="zpm-blocking-tasks" data-blocking-tasks data-ajax-name="blockingTasks" class="zpm_input zpm-input-chosen zpm-multi-select" multiple placeholder="<?php _e('Select Blocking Tasks', 'zephyr-project-manager'); ?>">
						<?php foreach ($tasks as $projectTask) : ?>
							<?php if (Tasks::isCompleted($projectTask)) continue; ?>
							<option value="<?php echo esc_attr($projectTask->id); ?>"><?php echo zpm_esc_html($projectTask->name); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>

			<!-- Select Parent Task -->
			<div class="zpm-new-task--parent-task">
				<label class="zpm_label"><?php echo !$isScrum ? __('Parent Task', 'zephyr-project-manager') : __('Parent User Story', 'zephyr-project-manager'); ?></label>
				<select class="zpm_input zpm-input-chosen zpm-chosen" data-ajax-name="parent-id" data-default="-1">
					<option value="-1"><?php _e('None', 'zephyr-project-manager'); ?></option>
					<?php foreach ($tasks as $taskItem) : ?>
						<option value="<?php esc_attr_e($taskItem->id); ?>"><?php esc_html_e($taskItem->name); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<!-- Recurrence -->
			<div id="zpm-new-task__recurrence" class="zpm-task-recurrence__wrap">
				<div id="zpm-new-task__recurrence-selection" class="zpm-recurrence-selection">
					<label class="zpm_label"><?php _e('Set Recurrence', 'zephyr-project-manager'); ?></label>
					<select id="zpm-new-task__recurrence-select" class="zpm-chosen-select zpm_input zpm-input-chosen" data-position="top">
						<option value="default" selected><?php _e('None', 'zephyr-project-manager'); ?></option>
						<option value="daily"><?php _e('Daily', 'zephyr-project-manager'); ?></option>
						<option value="weekly"><?php _e('Weekly', 'zephyr-project-manager'); ?></option>
						<option value="monthly"><?php _e('Monthly', 'zephyr-project-manager'); ?></option>
						<option value="annually"><?php _e('Annually', 'zephyr-project-manager'); ?></option>
					</select>

					<!-- Daily Reccurence Settings -->
					<div class="zpm-new-task__recurrence-section" data-section="daily" style="display: none;">
						<label class="zpm_label"><?php _e('Repeat Every', 'zephyr-project-manager'); ?></label>
						<div class="zpm-new-task__recurrence-settings">
							<select id="zpm-new-task__recurrence-daily" class="zpm-multi-select" multiple data-placeholder="<?php _e('Days to Repeat', 'zephyr-project-manager'); ?>">
								<option value="0" selected><?php _e('Monday', 'zephyr-project-manager'); ?></option>
								<option value="1" selected><?php _e('Tuesday', 'zephyr-project-manager'); ?></option>
								<option value="2" selected><?php _e('Wednesday', 'zephyr-project-manager'); ?></option>
								<option value="3" selected><?php _e('Thursday', 'zephyr-project-manager'); ?></option>
								<option value="4" selected><?php _e('Friday', 'zephyr-project-manager'); ?></option>
								<option value="5"><?php _e('Saturday', 'zephyr-project-manager'); ?></option>
								<option value="6"><?php _e('Sunday', 'zephyr-project-manager'); ?></option>
							</select>
						</div>

						<label class="zpm_label"><?php _e('Starts On:', 'zephyr-project-manager'); ?></label>
						<input class="zpm-datepicker zpm_input" id="zpm-new-task__recurrence-start-date" />
						<label class="zpm_label"><?php _e('Expires On (leave empty for no expiration):', 'zephyr-project-manager'); ?></label>
						<input class="zpm-datepicker zpm_input" id="zpm-new-task__recurrence-expiration-date" />
					</div>

					<!-- Weekly Reccurence Settings -->
					<div class="zpm-new-task__recurrence-section" data-section="weekly" style="display: none;">
						<label class="zpm_label"><?php _e('Starts On:', 'zephyr-project-manager'); ?></label>
						<input class="zpm-datepicker zpm_input" id="zpm-new-task__recurrence-start-date-weekly" />
						<label class="zpm_label"><?php _e('Expires On (leave empty for no expiration):', 'zephyr-project-manager'); ?></label>
						<input class="zpm-datepicker zpm_input" id="zpm-new-task__recurrence-expiration-date-weekly" />
					</div>

					<!-- Monthly Reccurence Settings -->
					<div class="zpm-new-task__recurrence-section" data-section="monthly" style="display: none;">
						<label class="zpm_label"><?php _e('Starts On:', 'zephyr-project-manager'); ?></label>
						<input class="zpm-datepicker zpm_input" id="zpm-new-task__recurrence-start-date-monthly" />
						<label class="zpm_label"><?php _e('Expires On (leave empty for no expiration):', 'zephyr-project-manager'); ?></label>
						<input class="zpm-datepicker zpm_input" id="zpm-new-task__recurrence-expiration-date-monthly" />
					</div>

					<!-- Annual Reccurence Settings -->
					<div class="zpm-new-task__recurrence-section" data-section="annually" style="display: none;">
						<label class="zpm_label"><?php _e('Starts On:', 'zephyr-project-manager'); ?></label>
						<input class="zpm-datepicker zpm_input" id="zpm-new-task__recurrence-start-date-annual" />
						<label class="zpm_label"><?php _e('Expires On (leave empty for no expiration):', 'zephyr-project-manager'); ?></label>
						<input class="zpm-datepicker zpm_input" id="zpm-new-task__recurrence-expiration-date-annual" />
					</div>
				</div>
			</div>

			<div id="zpm-new-task__attachments"></div>

			<?php do_action('zpm_new_task_settings'); ?>
			<?php echo apply_filters('zpm-task-kanban-id', ''); ?>
		</div>

		<div class="zpm_modal_buttons">
			<input type="hidden" id="zpm-new-task-priority-value" value="priority_none" />
			<span id="zpm-new-task-priority" class="zpm_button zpm_button_secondary" zpm-toggle-dropdown="zpm-new-task-priority-dropdown" data-priority="priority_none"><span class="zpm-priority-name"><?php _e('Set Priority', 'zephyr-project-manager'); ?></span>
				<div id="zpm-new-task-priority-dropdown" class="zpm-dropdown">

					<div class="zpm-dropdown-item zpm-new-task-priority" data-value="priority_none" data-color="#f9f9f9"><span class="zpm-priority-indicator zpm-color-none"></span><?php _e('None', 'zephyr-project-manager'); ?></div>

					<?php foreach ($priorities as $slug => $status) : ?>
						<div class="zpm-dropdown-item zpm-new-task-priority" data-value="<?php echo esc_attr($slug); ?>" data-color="<?php echo esc_html($status['color']); ?>">

							<span class="zpm-priority-indicator <?php echo esc_attr($slug); ?>" style="background-color: <?php echo esc_html($status['color']); ?>"></span>
							<span class="zpm-priority-picker__name"><?php echo esc_html($status['name']); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</span>

			<?php if (!BaseController::is_pro() && !isset($isShortcode)) : ?>
				<button id="zpm_add_custom_field_pro" class="zpm_button" data-zpm-pro-upsell="true">
					<div class="zpm-pro-notice">
						<span class="lnr lnr-cross zpm-close-pro-notice"></span>
						<?php _e('This option is only available in the Pro version.', 'zephyr-project-manager'); ?> <br /><a class="zpm-purchase-link zpm_link" href="<?php echo esc_url(ZEPHYR_PRO_LINK); ?>" target="_blank"><span class="zpm-purchase-icon lnr lnr-star"></span><?php _e('Purchase the Pro Add-On Now', 'zephyr-project-manager'); ?></a>.
					</div>
					<?php _e('Add Custom Field', 'zephyr-project-manager'); ?>
				</button>
			<?php endif; ?>

			<?php do_action('zpm_new_task_buttons'); ?>
			<?php if (Utillities::canUploadFiles()): ?>
				<button id="zpm-new-task__new-file" class="zpm_button"><?php _e('Add File', 'zephyr-project-manager'); ?></button>
			<?php endif; ?>
			<button id="zpm_save_task" class="zpm_button" type="submit"><?php echo !$isScrum ? __('Create Task', 'zephyr-project-manager') : __('Create User Story', 'zephyr-project-manager'); ?></button>
		</div>
	</div>
	<?php do_action('zpm_new_task_after_body'); ?>
</div>