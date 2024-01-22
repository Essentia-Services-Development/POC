<?php

/**
 * Tasks Page
 * Page where all tasks are listed and users can create, view, edit and manage them
 */

if (!defined('ABSPATH')) {
	die;
}

use ZephyrProjectManager\Core\Tasks;
use ZephyrProjectManager\Core\Projects;
use ZephyrProjectManager\Core\Members;
use ZephyrProjectManager\Core\Utillities;
use ZephyrProjectManager\ZephyrProjectManager;

$manager = ZephyrProjectManager();
$users = Members::get_zephyr_members();
$current_user = wp_get_current_user();
$base_url = esc_url(admin_url('/admin.php?page=zephyr_project_manager_tasks'));
$liked_tasks = unserialize(get_option('zpm_liked_tasks_' . $current_user->data->ID, false));
$followed_tasks = unserialize(get_option('zpm_followed_tasks_' . $current_user->data->ID, false));
$task_count = Tasks::get_task_count();
$userID = get_current_user_id();
$all_projects = Projects::get_projects();
$statuses = Utillities::get_statuses('status');
$lastSorting = Tasks::getLastSorting();
$sortingOptions = [
	[
		'text' => __('Date Created', 'zephyr-project-manager'),
		'value' => 'date-created'
	],
	[
		'text' => __('Start Date', 'zephyr-project-manager'),
		'value' => 'date-start'
	],
	[
		'text' => __('Due Date (Decending)', 'zephyr-project-manager'),
		'value' => 'date-due'
	],
	[
		'text' => __('Due Date (Ascending)', 'zephyr-project-manager'),
		'value' => 'date-due-asc'
	],
	[
		'text' => __('Assignee', 'zephyr-project-manager'),
		'value' => 'assignee'
	],
	[
		'text' => __('Alphabetical', 'zephyr-project-manager'),
		'value' => 'name'
	],
	[
		'text' => __('Priority', 'zephyr-project-manager'),
		'value' => 'sort-priority'
	]
];
$hasTasks = $task_count > 0;
?>

<div class="zpm_settings_wrap">
	<?php $this->get_header(); ?>
	<div id="zpm_container">
		<?php if (isset($_GET['action']) && $_GET['action'] == 'view_task') : ?>
			<div id="zpm_task_view">
				<?php include(ZPM_PLUGIN_PATH . '/templates/parts/task-single.php'); ?>
			</div>
		<?php else : ?>


			<!-- There are no tasks yet -->
			<!-- <div class="zpm_no_results_message" style="<?php echo ($hasTasks) ? 'display: none;' : ''; ?>">
				<?php if (Utillities::can_create_tasks()) : ?>
					<?php printf(__('No tasks created yet. To create a task, click on the \'Add\' button at the top right of the screen or click %s here %s', 'zephyr-project-manager'), '<a id="zpm_first_task" class="zpm_button_link">', '</a>') ?>
				<?php else : ?>
					<?php _e('No tasks created yet.', 'zephyr-project-manager'); ?>
				<?php endif; ?>
			</div> -->


			<div id="zpm_task_option_container">
				<!-- <span class="zpm_modal_options_btn" data-dropdown-id="zpm_view_task_dropdown">
					<span class="lnr lnr-menu"></span>
					<div class="zpm_modal_dropdown" id="zpm_view_task_dropdown">
						<ul class="zpm_modal_list">
							<li id="zpm_export_task">
								<?php _e('Export Tasks', 'zephyr-project-manager'); ?>
								<div class="zpm_export_dropdown">
									<ul>
										<li id="zpm_export_all_tasks_to_csv"><?php _e('Export to CSV', 'zephyr-project-manager'); ?></li>
										<li id="zpm_export_all_tasks_to_json"><?php _e('Export to JSON', 'zephyr-project-manager'); ?></li>
									</ul>
								</div>
							</li>
							<li id="zpm_import_task">
								<?php _e('Import Tasks', 'zephyr-project-manager'); ?>
								<div class="zpm_export_dropdown">
									<ul>
										<li id="zpm_import_tasks_from_csv"><?php _e('Import from CSV', 'zephyr-project-manager'); ?></li>
										<li id="zpm_import_tasks_from_json"><?php _e('Import from JSON', 'zephyr-project-manager'); ?></li>
									</ul>
								</div>
							</li>
							<?php do_action('zpm_tasks_dropdown'); ?>
						</ul>
					</div>
				</span> -->

				<!-- Task filter options -->
				<div id="zpm-tasks-filter-nav" class="zpm_nav_holder zpm_body">
					<!-- <nav class="zpm_nav">
						<ul class="zpm_nav_list">
							<li class="zpm_nav_item zpm_selection_option zpm_nav_item_selected" data-zpm-filter="-1" role="tabpanel" aria-selected="false" aria-selected="true"><?php _e('All Tasks', 'zephyr-project-manager'); ?></li>
							<li class="zpm_nav_item zpm_selection_option" data-zpm-filter="1" role="tabpanel" aria-selected="false"><?php _e('Active Tasks', 'zephyr-project-manager'); ?></li>
							<li class="zpm_nav_item zpm_selection_option" id="zpm_update_project_progress" data-zpm-filter="2" role="tabpanel" aria-selected="false"><?php _e('Complete Tasks', 'zephyr-project-manager'); ?></li>
							<li class="zpm_nav_item zpm_selection_option" data-zpm-filter="archived" role="tabpanel" aria-selected="false"><?php _e('Archived Tasks', 'zephyr-project-manager'); ?></li>
							<?php echo apply_filters('zpm_tasks_filters', ''); ?>
						</ul>
					</nav> -->
					<!-- <?php if (Utillities::can_create_tasks()) : ?>
						<div class="zpm-task-action-buttons">
							<button class="zpm_button" id="zpm-new-task-list-button" data-add-task-list-button><?php _e('Add Task List', 'zephyr-project-manager'); ?></button>
							<button class="zpm_button" name="zpm_task_add_new" id="zpm_task_add_new"><?php _e('Add New', 'zephyr-project-manager'); ?></button>
						</div>
					<?php endif; ?> -->
				</div>
			</div>

			<div class="zpm-task-filter-container">
				<div id="zpm-task-filter-holder">
					<label><?php _e('Assignee', 'zephyr-project-manager'); ?>:</label>
					<select id="zpm-tasks-assignee-filter">
						<option value="my_tasks" selected><?php _e('My Tasks', 'zephyr-project-manager'); ?></option>
						<option value="all"><?php _e('All', 'zephyr-project-manager'); ?></option>

						<?php foreach ($users as $user) : ?>
							<?php if (!Members::canViewMember($user['id'])) continue; ?>
							<option value="<?php esc_attr_e($user['id']); ?>"><?php esc_html_e($user['name']); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div id="zpm-task-filter-holder">
					<label><?php _e('Sort By', 'zephyr-project-manager'); ?>:</label>
					<select id="zpm-tasks-filter" data-task-filter="sort">
						<?php foreach ($sortingOptions as $option) : ?>
							<option value="<?php echo $option['value']; ?>" <?php echo strpos($option['value'], $lastSorting) !== false ? 'selected' : ''; ?>><?php echo $option['text']; ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div id="zpm-task-status-filter-holder">
					<label><?php _e('Status', 'zephyr-project-manager'); ?>:</label>
					<select id="zpm-tasks-status-filter" data-filter-tasks-status>
						<option value="all"><?php esc_html_e('All', 'zephyr-project-manager'); ?></option>
						<option value="<?php esc_attr_e('active'); ?>"><?php esc_html_e('Active', 'zephyr-project-manager'); ?></option>
						<?php foreach ($statuses as $idx => $status) : ?>
							<option value="<?php esc_attr_e($idx); ?>"><?php esc_html_e($status['name']); ?></option>
						<?php endforeach; ?>
						<option value="<?php esc_attr_e('archived'); ?>"><?php esc_html_e('Archived', 'zephyr-project-manager'); ?></option>
					</select>
				</div>

				<div id="zpm-task-project-filter-holder">
					<label><?php _e('Project', 'zephyr-project-manager'); ?>:</label>
					<select id="zpm-tasks-project-filter">
						<option value="-1"><?php _e('All Projects', 'zephyr-project-manager'); ?></option>
						<?php foreach ($all_projects as $project) : ?>
							<option value="<?php echo $project->id; ?>"><?php echo esc_html($project->name); ?></option>
						<?php endforeach; ?>

					</select>
				</div>

				<?php if (Utillities::can_create_tasks()) : ?>
					<div class="zpm-task-action-buttons">
						<button class="zpm_button" id="zpm-new-task-list-button" data-add-task-list-button><?php _e('Add Task List', 'zephyr-project-manager'); ?></button>
						<button class="zpm_button" name="zpm_task_add_new" id="zpm_task_add_new"><?php _e('Add New', 'zephyr-project-manager'); ?></button>
					</div>
				<?php endif; ?>
			</div>

			<div id="zpm_task_list_container" class="zpm_body" role="tabpanel">
				<!-- Task List -->
				<div class="zpm_task_container">
					<div id="zpm_task_list" class="zpm_settings_form" data-task-list>
						<?php Tasks::view_task_list([
							'user_tasks' => $userID
						]); ?>
					</div>
				</div>
			</div>

			<div class="zpm-task-action-buttons zpm-task-action-buttons--footer">
				<?php if (Utillities::canDeleteTasks($userID)) : ?>
					<button class="zpm_button" data-bulk-delete-button><?php _e('Delete Selected', 'zephyr-project-manager'); ?></button>
					<button class="zpm_button" data-bulk-archive-button><?php _e('Archive Selected', 'zephyr-project-manager'); ?></button>
				<?php endif; ?>
				<?php if (Utillities::can_edit_tasks($userID)) : ?>
					<button class="zpm_button" data-bulk-edit-button><?php _e('Edit Selected', 'zephyr-project-manager'); ?></button>
				<?php endif; ?>
				<?php if (Utillities::canDeleteTasks($userID) || Utillities::can_edit_tasks($userID)) : ?>
					<button class="zpm_button" data-toggle-task-bulk-selection><?php _e('Select Multiple', 'zephyr-project-manager'); ?></button>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<!-- New Subtask Modal -->
<?php Tasks::newSubtaskModal(); ?>

<!-- Edit Subtask Modal -->
<div id="zpm_edit_subtask_modal" class="zpm-modal zpm_compact_modal" aria-hidden="true">
	<div class="zpm-form__group">
		<input type="text" name="zpm_edit_subtask_name" id="zpm_edit_subtask_name" class="zpm-form__field" placeholder="<?php _e('Subtask Name', 'zephyr-project-manager'); ?>">
		<label for="zpm_edit_subtask_name" class="zpm-form__label"><?php _e('Subtask Name', 'zephyr-project-manager'); ?></label>
	</div>

	<button id="zpm_update_subtask" class="zpm_button"><?php _e('Save Changes', 'zephyr-project-manager'); ?></button>
</div>

<?php $this->get_footer(); ?>

<?php do_action('zpm_after_task_page'); ?>