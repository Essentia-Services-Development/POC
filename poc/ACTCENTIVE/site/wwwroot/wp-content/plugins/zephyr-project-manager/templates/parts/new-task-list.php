<?php
	/**
	* Template for displaying the New Task modal
	*/

	if (!defined('ABSPATH')) die;

	use ZephyrProjectManager\Zephyr;
	use ZephyrProjectManager\Core\Tasks;
	use ZephyrProjectManager\Core\Members;
	use ZephyrProjectManager\Core\Projects;
	use ZephyrProjectManager\Core\Utillities;
	use ZephyrProjectManager\Base\BaseController;
	use ZephyrProjectManager\ZephyrProjectManager;

	$manager = ZephyrProjectManager::get_instance();
	$projects = $manager::get_projects();
	$args = array( 'can_zephyr' => true );
	$users = $manager::get_users( true, $args );
	$userID = get_current_user_id();
	$date = date('Y-m-d');
	$statuses = Utillities::get_statuses( 'status' );
	$priorities = Utillities::get_statuses( 'priority' );
	$general_settings = Utillities::general_settings();
	$extra_classes = $general_settings['hide_default_task_fields'] == '1' ? 'zpm-hide-default-fields' : '';
	$defaultProject = isset($general_settings['default_project']) ? $general_settings['default_project'] : '-1';
	$defaultAssignee = isset($general_settings['default_assignee']) ? $general_settings['default_assignee'] : '-1';
	$createAssignedProjectTasksOnly = Utillities::hasPerm('create_tasks_in_assigned_projects_only');
?>

<div id="zpm-new-task-list-modal" class="zpm-modal zpm-form" role="dialog" aria-labelledby="zpm-new-task-list-button" aria-modal="true" aria-hidden="true">
	<h5 class="zpm_modal_header"><?php _e( 'New Task List', 'zephyr-project-manager' ); ?></h5>
	<div class="zpm_modal_body">
		<div class="zpm_modal_content">
			<template data-zpm-template="taskListItem">
				<?php echo Tasks::taskListItemInput(); ?>
			</template>
			<div data-task-list>
				<?php echo Tasks::taskListItemInput(); ?>
			</div>
			<div>
				<div class="zpm-new-task-field__assignee">
					<label class="zpm_label" for="zpm-task-list-assignees"><?php _e( 'Assignees', 'zephyr-project-manager' ); ?></label>
					<select id="zpm-task-list-assignees" class="zpm-chosen zpm-chosen-input" multiple data-placeholder="<?php _e( 'Select Assignees', 'zephyr-project-manager' ); ?>">
						<?php foreach ($users as $user) : ?>
							<?php if (!Members::canViewMember($user['id'])) { continue; } ?>
							<option value="<?php esc_attr_e($user['id']); ?>" <?php echo $defaultAssignee == $user['id'] ? 'selected' : ''; ?>><?php esc_html_e($user['name']); ?></option>;
						<?php endforeach; ?>
					</select>
				</div>
				<div class="zpm-new-task-field__project">
					<label class="zpm_label" for="zpm-task-list-project"><?php _e( 'Project', 'zephyr-project-manager' ); ?></label>
					<select id="zpm-task-list-project" class="zpm-chosen zpm-chosen-input" data-placeholder="<?php _e( 'Select Project', 'zephyr-project-manager' ); ?>">
						<option value="-1"><?php esc_html_e('None', 'zephyr-project-manager'); ?></option>;
						<?php foreach ($projects as $project): ?>
							<?php if ($createAssignedProjectTasksOnly && (!Projects::isAssignee($project) && !Projects::isTeamMember($project, $userID))) continue; ?>
							<?php if (!Projects::has_project_access($project)) continue; ?>
							<option value="<?php esc_attr_e($project->id); ?>"><?php esc_html_e($project->name); ?></option>;
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		
		<div class="zpm-key-legends">
			<div class="zpm-key-legend">
				<span class="zpm-key-legend--text"><?php esc_html_e('Delete Task', 'zephyr-project-manager'); ?></span>
				<span class="zpm-key-legend--key">DEL</span>
			</div>
			<div class="zpm-key-legend">
				<span class="zpm-key-legend--text"><?php esc_html_e('Add Task', 'zephyr-project-manager'); ?></span>
				<span class="zpm-key-legend--key">ENTER</span>
			</div>
			<!-- <div class="zpm-key-legend">
				<span class="zpm-key-legend--text"><?php esc_html_e('Create Tasks', 'zephyr-project-manager'); ?></span>
				<span class="zpm-key-legend--key">ENTER</span>
			</div> -->
		</div>
	
		<div class="zpm_modal_buttons">
			<button id="zpm-create-task-list" type="submit" class="zpm_button" data-create-task-list><?php _e( 'Create Task', 'zephyr-project-manager' ); ?></button>
		</div>
	</div>
</div>
