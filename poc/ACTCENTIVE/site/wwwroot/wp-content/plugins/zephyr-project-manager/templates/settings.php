<?php

/**
 * The Settings page
 */
if (!defined('ABSPATH')) {
	die;
}

use ZephyrProjectManager\Zephyr;
use ZephyrProjectManager\Core\Members;
use ZephyrProjectManager\Api\Emails;
use ZephyrProjectManager\Core\Projects;
use ZephyrProjectManager\Core\Tasks;
use ZephyrProjectManager\Core\Utillities;

global $wpdb;

$current_user = wp_get_current_user();
$user_id = $current_user->data->ID;
$user_name = $current_user->data->display_name;
$user_email = $current_user->data->user_email;
$isAdmin = current_user_can('administrator');

// Delete all data if chosen
if (isset($_POST['zpm-delete-all-data']) && $isAdmin) {
	check_admin_referer('zpm_delete_data');
	$tables = Utillities::getTables();

	foreach ($tables as $table) {
		Utillities::truncateTable($table);
	}

	delete_option('zpm_general_settings');
	remove_role('zpm_user');
	remove_role('zpm_client_user');
	remove_role('zpm_manager');
	remove_role('zpm_admin');
}

// Save Profile Settings
if (isset($_POST['zpm_profile_settings'])) {
	check_admin_referer('zpm_save_project_settings');

	$name = (isset($_POST['zpm_settings_name']) && $_POST['zpm_settings_name'] !== '') ? sanitize_text_field($_POST['zpm_settings_name']) : $user_name;
	$profile_picture = isset($_POST['zpm_profile_picture']) ? sanitize_text_field($_POST['zpm_profile_picture']) : get_avatar_url($user_id);
	$description = isset($_POST['zpm_settings_description']) ? sanitize_textarea_field($_POST['zpm_settings_description']) : '';
	$email = (isset($_POST['zpm_settings_email']) && $_POST['zpm_settings_email'] !== '') ? sanitize_email($_POST['zpm_settings_email']) : $user_email;
	$notify_activity = isset($_POST['zpm_notify_activity']) ? 1 : '0';
	$notify_tasks = isset($_POST['zpm_notify_tasks']) ? 1 : '0';
	$notify_updates = isset($_POST['zpm_notify_updates']) ? 1 : '0';
	$notify_task_assigned = isset($_POST['zpm_notify_task_assigned']) ? 1 : '0';
	$notifyNewProjectTasks = isset($_POST['zpm-email-notifications--new-project-tasks']);
	$hide_dashboard_widgets = isset($_POST['zpm-hide-dashboard-widgets']) ? true : false;
	$access_level = isset($_POST['zpm-access-level']) ? sanitize_text_field($_POST['zpm-access-level']) : 'edit_posts';
	$settings = array(
		'user_id' 		  		 => $user_id,
		'profile_picture' 		 => $profile_picture,
		'name' 			  		 => $name,
		'description' 	  		 => $description,
		'email' 		  		 => $email,
		'notify_activity' 		 => $notify_activity,
		'notify_tasks' 	  		 => $notify_tasks,
		'notify_updates'  		 => $notify_updates,
		'notify_task_assigned'   => $notify_task_assigned,
		'notify_new_project_tasks' => $notifyNewProjectTasks,
		'hide_dashboard_widgets' => $hide_dashboard_widgets,
	);
	update_option('zpm_user_' . $user_id . '_settings', $settings);
	update_option('zpm_access_settings', $access_level);
}


$user_settings_option = get_option('zpm_user_' . $user_id . '_settings');
$general_settings = Utillities::general_settings(true);
$access_settings = Utillities::get_access_level();
$settings_profile_picture = (isset($user_settings_option['profile_picture'])) ? esc_url($user_settings_option['profile_picture']) : esc_url(get_avatar_url($user_id));
$settings_name = (isset($user_settings_option['name'])) ? esc_html($user_settings_option['name']) : esc_html($user_name);
$access_level = !empty($access_settings) ? $access_settings : 'edit_posts';
$settings_description = isset($user_settings_option['description']) ? esc_textarea($user_settings_option['description']) : '';
$settings_email = isset($user_settings_option['email']) ? esc_html($user_settings_option['email']) : esc_html($user_email);
$settings_notify_activity = (isset($user_settings_option['notify_activity'])) ? $user_settings_option['notify_activity'] : '0';
$settings_notify_tasks = (isset($user_settings_option['notify_tasks'])) ? $user_settings_option['notify_tasks'] : '0';
$settings_notify_updates = (isset($user_settings_option['notify_updates'])) ? $user_settings_option['notify_updates'] : '0';
$settings_notify_task_assigned = (isset($user_settings_option['notify_task_assigned'])) ? $user_settings_option['notify_task_assigned'] : '1';
$notifyNewProjectTasks = (isset($user_settings_option['notify_new_project_tasks'])) ? $user_settings_option['notify_new_project_tasks'] : true;
$settings_notifications['activity'] = $settings_notify_activity == '1' ? esc_attr('checked') : '';
$settings_notifications['tasks'] = $settings_notify_tasks == '1' ? esc_attr('checked') : '';
$settings_notifications['updates'] = $settings_notify_updates == '1' ? esc_attr('checked') : '';
$settings_notifications['task_assigned'] = $settings_notify_task_assigned == '1' ? esc_attr('checked') : '';
$settings_notifications['new_project_tasks'] = $notifyNewProjectTasks ? esc_attr('checked') : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$days_of_week = Utillities::getDaysOfWeek();
$date_formats = Utillities::getDateFormats();
$user_caps = Utillities::get_caps();
$zpm_roles = Utillities::get_editable_roles();
$settingsPages = Zephyr::getSettingsPages();
$users = Members::get_zephyr_members();
$projects = Projects::get_projects();
$canAccessGeneralSettings = Utillities::is_admin();
$canAccessAdvancedSettings = Utillities::is_admin();
$roles = Utillities::get_roles();
$accessRoles = $general_settings['access_roles'];
$allRoles = Utillities::get_editable_roles();
$calendarColorOptions = [
	'category' => __('Use Category Color', 'zephyr-project-manager'),
	'status' => __('Use Status Color', 'zephyr-project-manager'),
	'priority' => __('Use Priority Color', 'zephyr-project-manager'),
];
$icsUrl = Tasks::getIcsUrl(get_current_user_id());
$statuses = Utillities::get_statuses('status');
$customTabs = apply_filters('zpm/settings/tabs', []);
$projectTabs = Projects::getTabs();
?>

<main class="zpm_settings_wrap">
	<?php $this->get_header(); ?>
	<div id="zpm_container">
		<div class="zpm_body">
			<div class="zpm-tab-content">
				<div data-section="profile_settings" class="tab-pane active">
					<?php
					ob_start();
					?>
					<h3 class="zpm_h3 zpm_tab_title <?php echo esc_attr($action == 'profile' || $action == '' ? 'zpm_tab_selected' : '') ?>" data-zpm-tab-trigger="profile"><?php _e('Profile Settings', 'zephyr-project-manager') ?></h3>

					<?php if ($canAccessGeneralSettings) : ?>
						<h3 class="zpm_h3 zpm_tab_title <?php echo esc_attr($action == 'general' ? 'zpm_tab_selected' : '') ?>" data-zpm-tab-trigger="general"><?php _e('General Settings', 'zephyr-project-manager') ?></h3>
					<?php endif; ?>
					<?php
					$tabs = ob_get_clean();
					echo apply_filters('zpm_settings_tabs', $tabs);

					foreach ($customTabs as $id => $tab) {
					?>
						<h3 class="zpm_h3 zpm_tab_title <?php echo esc_attr($action == $id ? 'zpm_tab_selected' : '') ?>" data-zpm-tab-trigger="<?php esc_attr_e($id); ?>"><?php esc_html_e($tab['title']); ?></h3>
					<?php
					}

					if ($canAccessAdvancedSettings) {
					?>
						<h3 class="zpm_h3 zpm_tab_title <?php echo esc_attr($action == 'tools' ? 'zpm_tab_selected' : '') ?>" data-zpm-tab-trigger="tools"><?php _e('Tools', 'zephyr-project-manager'); ?></h3>
						<h3 class="zpm_h3 zpm_tab_title <?php echo esc_attr($action == 'advanced' ? 'zpm_tab_selected' : '') ?>" data-zpm-tab-trigger="advanced"><?php _e('Advanced', 'zephyr-project-manager'); ?></h3>
					<?php
					}

					foreach ($settingsPages as $page) {
					?>
						<h3 class="zpm_h3 zpm_tab_title <?php echo esc_attr($action == $page['slug'] ? 'zpm_tab_selected' : '') ?>" data-zpm-tab-trigger="<?php echo esc_attr($page['slug']); ?>"><?php echo esc_html($page['title']); ?></h3>
					<?php
					}
					?>

					<?php ob_start(); ?>

					<div class="zpm_tab_panel <?php echo $action == 'profile' || $action == '' ? 'zpm_tab_active' : ''; ?>" data-zpm-tab="profile">
						<!-- Profile Settings -->
						<form id="zpm_profile_settings" method="post">
							<label class="zpm_label"><?php _e('Profile Picture', 'zephyr-project-manager'); ?></label>
							<div class="zpm_settings_profile_picture">
								<span class="zpm_settings_profile_background"></span>
								<span class="zpm_settings_profile_image" style="background-image: url(<?php echo esc_url($settings_profile_picture); ?>);"></span>
							</div>

							<input type="hidden" id="zpm_profile_picture_hidden" name="zpm_profile_picture" value="<?php echo esc_attr($settings_profile_picture); ?>" />
							<input type="hidden" id="zpm_gravatar" value="<?php echo esc_url(get_avatar_url($user_id)); ?>" />

							<div class="zpm-form__group">
								<input type="text" name="zpm_settings_name" id="zpm_settings_name" class="zpm-form__field" placeholder="<?php _e('Name', 'zephyr-project-manager'); ?>" value="<?php echo esc_attr($settings_name); ?>">
								<label for="zpm_settings_name" class="zpm-form__label"><?php _e('Name', 'zephyr-project-manager'); ?></label>
							</div>

							<div class="zpm-form__group">
								<textarea type="text" name="zpm_settings_description" id="zpm_settings_description" class="zpm-form__field" placeholder="<?php _e('Description', 'zephyr-project-manager'); ?>"><?php echo zpm_esc_html($settings_description); ?></textarea>
								<label for="zpm_settings_description" class="zpm-form__label"><?php _e('Description', 'zephyr-project-manager'); ?></label>
							</div>

							<div class="zpm-form__group">
								<input type="text" name="zpm_settings_email" id="zpm_settings_email" class="zpm-form__field" placeholder="<?php _e('Email Address', 'zephyr-project-manager'); ?>" value="<?php echo esc_attr($settings_email); ?>">
								<label for="zpm_settings_email" class="zpm-form__label"><?php _e('Email Address', 'zephyr-project-manager'); ?></label>
							</div>

							<?php do_action('zpm_settings_fields'); ?>

							<label class="zpm_label"><?php _e('Hide WordPress Dashboard Widgets', 'zephyr-project-manager'); ?></label>

							<label for="zpm-hide-dashboard-widgets" class="zpm-material-checkbox">
								<input type="checkbox" id="zpm-hide-dashboard-widgets" name="zpm-hide-dashboard-widgets" class="zpm_toggle invisible" value="1" <?php echo isset($settings['hide_dashboard_widgets']) && $settings['hide_dashboard_widgets'] == true ? 'checked' : '';  ?>>
								<span class="zpm-material-checkbox-label"><?php _e('Hidden', 'zephyr-project-manager'); ?></span>
							</label>

							<label class="zpm_label"><?php _e('Email Notifications', 'zephyr-project-manager'); ?></label>
							<label for="zpm_notify_activity" class="zpm-material-checkbox">
								<input type="checkbox" id="zpm_notify_activity" name="zpm_notify_activity" class="zpm_toggle invisible" value="1" <?php echo esc_attr($settings_notifications['activity']); ?>>
								<span class="zpm-material-checkbox-label"><?php _e('All Activity', 'zephyr-project-manager'); ?></span>
							</label>

							<label for="zpm_notify_tasks" class="zpm-material-checkbox">
								<input type="checkbox" id="zpm_notify_tasks" name="zpm_notify_tasks" class="zpm_toggle invisible" value="1" <?php echo esc_attr($settings_notifications['tasks']); ?>>
								<span class="zpm-material-checkbox-label"><?php _e('New Tasks', 'zephyr-project-manager'); ?></span>
							</label>

							<label for="zpm_notify_task_assigned" class="zpm-material-checkbox">
								<input type="checkbox" id="zpm_notify_task_assigned" name="zpm_notify_task_assigned" class="zpm_toggle invisible" value="1" <?php echo esc_attr($settings_notifications['task_assigned']); ?>>
								<span class="zpm-material-checkbox-label"><?php _e('Task Assigned', 'zephyr-project-manager'); ?></span>
							</label>

							<label for="zpm-email-notifications--new-project-tasks" class="zpm-material-checkbox">
								<input type="checkbox" id="zpm-email-notifications--new-project-tasks" name="zpm-email-notifications--new-project-tasks" class="zpm_toggle invisible" value="1" <?php echo esc_attr($settings_notifications['new_project_tasks']); ?>>
								<span class="zpm-material-checkbox-label"><?php _e('New Project Tasks', 'zephyr-project-manager'); ?></span>
							</label>

							<label for="zpm_notify_updates" class="zpm-material-checkbox">
								<input type="checkbox" id="zpm_notify_updates" name="zpm_notify_updates" class="zpm_toggle invisible" value="1" <?php echo esc_attr($settings_notifications['updates']); ?>>
								<span class="zpm-material-checkbox-label"><?php _e('Weekly Updates', 'zephyr-project-manager'); ?></span>
							</label>

							<?php wp_nonce_field('zpm_save_project_settings'); ?>

							<div id="zpm-profile-settings-buttons">
								<button type="submit" class="zpm_button" name="zpm_profile_settings" id="zpm_profile_settings"><?php _e('Save Settings', 'zephyr-project-manager'); ?></button>
							</div>
						</form>
					</div>

					<?php if ($canAccessGeneralSettings) : ?>
						<div class="zpm_tab_panel <?php echo $action == 'general' ? 'zpm_tab_active' : ''; ?>" data-zpm-tab="general">
							<!-- General Settings -->
							<form id="zpm_profile_settings" method="post">

								<label class="zpm_label zpm_divider_label"><?php _e('General Settings', 'zephyr-project-manager') ?></label>

								<div class="zpm-form__group">
									<input type="text" name="zpm-settings__projects-per-page" id="zpm-settings__projects-per-page" class="zpm-form__field" placeholder="<?php _e('Projects Per Page', 'zephyr-project-manager'); ?>" value="<?php echo esc_attr($general_settings['projects_per_page']); ?>">
									<label for="zpm-settings__projects-per-page" class="zpm-form__label"><?php _e('Projects Per Page', 'zephyr-project-manager'); ?></label>
								</div>

								<div class="zpm-form__group">
									<input type="text" name="zpm-settings__tasks-per-page" id="zpm-settings__tasks-per-page" class="zpm-form__field" placeholder="<?php _e('Tasks Per Page', 'zephyr-project-manager'); ?>" value="<?php echo esc_attr($general_settings['tasks_per_page']); ?>">
									<label for="zpm-settings__tasks-per-page" class="zpm-form__label"><?php _e('Tasks Per Page', 'zephyr-project-manager'); ?></label>
								</div>

								<label class="zpm_label"><?php _e('Group Projects by Category', 'zephyr-project-manager'); ?></label>

								<label for="zpm-settings__enable-category-grouping" class="zpm-material-checkbox">
									<input type="checkbox" id="zpm-settings__enable-category-grouping" name="zpm-settings__enable-category-grouping" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['enable_category_grouping']) && $general_settings['enable_category_grouping'] ? 'checked' : '';  ?>>
									<span class="zpm-material-checkbox-label"><?php _e('Enable grouping of projects by category', 'zephyr-project-manager'); ?></span>
								</label>

								<label class="zpm_label"><?php _e('Display Project ID', 'zephyr-project-manager'); ?></label>

								<label for="zpm-settings-display-project-id" class="zpm-material-checkbox">
									<input type="checkbox" id="zpm-settings-display-project-id" name="zpm-settings-display-project-id" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['display_project_id']) && $general_settings['display_project_id'] !== "0" ? 'checked' : '';  ?>>
									<span class="zpm-material-checkbox-label"><?php _e('Display Unique Project ID', 'zephyr-project-manager'); ?></span>
								</label>

								<label for="zpm-settings-display-database-project-id" class="zpm-material-checkbox">
									<input type="checkbox" id="zpm-settings-display-database-project-id" name="zpm-settings-display-database-project-id" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['display_database_project_id']) && $general_settings['display_database_project_id'] !== "0" ? 'checked' : '';  ?>>
									<span class="zpm-material-checkbox-label"><?php _e('Display Auto Increment Project ID', 'zephyr-project-manager'); ?></span>
								</label>

								<div class="zpm-form-field-section">
									<label for="zpm-settings__display-task-id" class="zpm-material-checkbox">
										<input type="checkbox" id="zpm-settings__display-task-id" name="zpm-settings__display-task-id" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['display_task_id']) && $general_settings['display_task_id'] !== "0" ? 'checked' : '';  ?>>
										<span class="zpm-material-checkbox-label"><?php _e('Display Task ID', 'zephyr-project-manager'); ?></span>
									</label>
								</div>

								<div class="zpm-form-field-section">
									<label for="direct_link_project" class="zpm-material-checkbox">
										<input type="checkbox" id="direct_link_project" name="direct_link_project" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['direct_link_project']) && $general_settings['direct_link_project'] ? 'checked' : '';  ?>>
										<span class="zpm-material-checkbox-label"><?php _e('Directly link to project', 'zephyr-project-manager'); ?></span>
									</label>
								</div>

								<div class="zpm-form-field-section">
									<label for="direct_link_task" class="zpm-material-checkbox">
										<input type="checkbox" id="direct_link_task" name="direct_link_task" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['direct_link_task']) && $general_settings['direct_link_task'] ? 'checked' : '';  ?>>
										<span class="zpm-material-checkbox-label"><?php _e('Directly link to task', 'zephyr-project-manager'); ?></span>
									</label>
								</div>

								<div class="zpm-form__group">
									<select id="zpm-settings-default-project-tab" name="zpm-settings-default-project-tab" class="zpm_input">
										<?php foreach ($projectTabs as $idx => $tab) : ?>
											<option value="<?php esc_attr_e($idx); ?>" <?php selected($general_settings['default_project_tab'], $idx); ?>><?php echo esc_html($tab); ?></option>
										<?php endforeach; ?>
									</select>
									<label for="zpm-settings-default-project-tab" class="zpm-form__label"><?php _e('Default Project Tab', 'zephyr-project-manager'); ?></label>
								</div>

								<label class="zpm_label zpm_divider_label"><?php _e('Email Settings', 'zephyr-project-manager') ?></label>

								<div class="zpm-form__group">
									<input type="text" name="zpm-settings-email-from-name" id="zpm-settings-email-from-name" class="zpm-form__field" placeholder="<?php _e('From Name', 'zephyr-project-manager'); ?>" value="<?php echo esc_attr($general_settings['email_from_name']); ?>">
									<label for="zpm-settings-email-from-name" class="zpm-form__label"><?php _e('From Name', 'zephyr-project-manager'); ?></label>
								</div>

								<div class="zpm-form__group">
									<input type="text" name="zpm-settings-email-from-email" id="zpm-settings-email-from-email" class="zpm-form__field" placeholder="<?php _e('From Email', 'zephyr-project-manager'); ?>" value="<?php echo esc_attr($general_settings['email_from_email']); ?>">
									<label for="zpm-settings-email-from-email" class="zpm-form__label"><?php _e('From Email', 'zephyr-project-manager'); ?></label>
								</div>

								<div class="zpm-form-field-section">
									<label for="zpm-settings__override-default-emails" class="zpm-material-checkbox">
										<input type="checkbox" id="zpm-settings__override-default-emails" name="zpm-settings__override-default-emails" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['override_default_emails']) && $general_settings['override_default_emails'] ? 'checked' : '';  ?>>
										<span class="zpm-material-checkbox-label"><?php _e('Send emails to all users (not assigned users only)', 'zephyr-project-manager'); ?></span>
									</label>
								</div>

								<label class="zpm_label zpm_divider_label"><?php _e('Permissions & Capabilities', 'zephyr-project-manager') ?></label>

								<?php if (current_user_can('administrator')) : ?>
									<label class="zpm_label"><?php _e('Which Roles Can Access Zephyr Project Manager', 'zephyr-project-manager'); ?></label>
									<select id="zpm-access-roles" class="zpm_input zpm-chosen-multi zpm-multi-select" multiple name="zpm-access-roles[]">
										<?php foreach ($allRoles as $role => $data) : ?>
											<?php if (in_array($role, ['administrator'])) continue; ?>
											<option value=<?php esc_attr_e($role) ?> <?php echo in_array($role, $accessRoles) ? 'selected' : '' ?>><?php esc_html_e($data['name']); ?></option>
										<?php endforeach; ?>
									</select>
								<?php endif; ?>

								<?php if (Utillities::is_admin()) : ?>
									<?php foreach ($zpm_roles as $key => $role) : ?>
										<label class="zpm_label"><?php echo esc_html($role['name']); ?></label>
										<select multiple="true" class="zpm-multi-select" name="zpm-settings-user-caps-<?php echo esc_attr($key); ?>[]">
											<?php foreach ($user_caps as $cap) {
												$name = str_replace('zpm_', '', $cap);
												$name = str_replace('_', ' ', $name);
												$name = ucwords($name);
											?>

												<option <?php echo isset($role['capabilities'][$cap]) && $role['capabilities'][$cap] == true ? 'selected' : ''; ?> value="<?php echo esc_attr($cap); ?>"><?php echo esc_html($name); ?></option>
											<?php
											} ?>
										</select>
									<?php endforeach; ?>

									<!-- Who can complete tasks -->
									<label class="zpm_label"><?php _e('Who can complete tasks', 'zephyr-project-manager'); ?></label>
									<select class="zpm_input" name="zpm-settings__can-complete-tasks">
										<option value="0" <?php echo isset($general_settings['can_complete_tasks']) && $general_settings['can_complete_tasks'] == '0' ? 'selected' : ''; ?>><?php _e('Everyone', 'zephyr-project-manager'); ?></option>
										<option value="1" <?php echo isset($general_settings['can_complete_tasks']) && $general_settings['can_complete_tasks'] == '1' ? 'selected' : ''; ?>><?php _e('Only Assigned Users', 'zephyr-project-manager'); ?></option>
										<option value="2" <?php echo isset($general_settings['can_complete_tasks']) && $general_settings['can_complete_tasks'] == '2' ? 'selected' : ''; ?>><?php _e('Only Administrators & Managers', 'zephyr-project-manager'); ?></option>
										<option value="3" <?php echo isset($general_settings['can_complete_tasks']) && $general_settings['can_complete_tasks'] == '3' ? 'selected' : ''; ?>><?php _e('Nobody', 'zephyr-project-manager'); ?></option>
									</select>
								<?php endif; ?>

								<div class="zpm-form-field-section">
									<label for="zpm_view_own_files" class="zpm-material-checkbox">
										<input type="checkbox" id="zpm_view_own_files" name="zpm_view_own_files" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['view_own_files']) && $general_settings['view_own_files'] ? 'checked' : '';  ?>>
										<span class="zpm-material-checkbox-label"><?php _e('Allow users to only view own files', 'zephyr-project-manager'); ?></span>
									</label>
								</div>

								<div class="zpm-form-field-section">
									<label for="zpm-settings__view-members" class="zpm-material-checkbox">
										<input type="checkbox" id="zpm-settings__view-members" name="zpm-settings__view-members" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['view_members']) && $general_settings['view_members'] ? 'checked' : '';  ?>>
										<span class="zpm-material-checkbox-label"><?php _e('Allow users to view other members on the site', 'zephyr-project-manager'); ?></span>
									</label>
								</div>

								<div class="zpm-form-field-section">
									<label for="zpm-settings__extended-file-uploader" class="zpm-material-checkbox">
										<input type="checkbox" id="zpm-settings__extended-file-uploader" name="zpm-settings__extended-file-uploader" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['file_uploader_type']) && $general_settings['file_uploader_type'] == 'extended' ? 'checked' : '';  ?>>
										<span class="zpm-material-checkbox-label"><?php _e('Enable extended file uploader with external link input', 'zephyr-project-manager'); ?></span>
									</label>
								</div>

								<div class="zpm-form-field-section">
									<label for="zpm-settings__disable-files-globally" class="zpm-material-checkbox">
										<input type="checkbox" id="zpm-settings__disable-files-globally" name="zpm-settings__disable-files-globally" class="zpm_toggle invisible" value="1" <?php echo $general_settings['disable_files_globally'] ? 'checked' : '';  ?>>
										<span class="zpm-material-checkbox-label"><?php _e('Disable files globally', 'zephyr-project-manager'); ?></span>
									</label>
								</div>

								<!-- <div class="zpm-form-field-section">
									<label for="zpm-view-assigned-categories-only" class="zpm-material-checkbox">
										<input type="checkbox" id="zpm-view-assigned-categories-only" name="zpm-view-assigned-categories-only" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['view_assigned_categories_only']) && $general_settings['view_assigned_categories_only'] ? 'checked' : '';  ?>>
										<span class="zpm-material-checkbox-label"><?php _e('Allow users to only view categories with projects they are members of', 'zephyr-project-manager'); ?></span>
									</label>
								</div> -->

								<label class="zpm_label"><?php _e('Default Assignee', 'zephyr-project-manager'); ?></label>
								<select class="zpm_input" name="zpm-settings__default-assignee">
									<option value="-1" <?php echo isset($general_settings['default_assignee']) && $general_settings['default_assignee'] == '-1' ? 'selected' : ''; ?>><?php _e('None', 'zephyr-project-manager'); ?></option>
									<option value="current" <?php selected($general_settings['default_assignee'], 'current'); ?>><?php _e('Current User', 'zephyr-project-manager'); ?></option>
									<?php foreach ($users as $user) : ?>
										<option value="<?php echo esc_attr($user['id']); ?>" <?php echo isset($general_settings['default_assignee']) && $general_settings['default_assignee'] == $user['id'] ? 'selected' : ''; ?>><?php echo esc_html($user['name']); ?></option>
									<?php endforeach; ?>
								</select>

								<label class="zpm_label"><?php _e('Default Project', 'zephyr-project-manager'); ?></label>
								<select class="zpm_input" name="zpm-settings__default-project">
									<option value="-1" <?php echo isset($general_settings['default_project']) && $general_settings['default_project'] == '-1' ? 'selected' : ''; ?>><?php _e('None', 'zephyr-project-manager'); ?></option>
									<?php foreach ($projects as $project) : ?>
										<option value="<?php echo esc_attr($project->id); ?>" <?php echo isset($general_settings['default_project']) && $general_settings['default_project'] == $project->id ? 'selected' : ''; ?>><?php echo esc_html($project->name); ?></option>
									<?php endforeach; ?>
								</select>

								<label class="zpm_label"><?php _e('Default Status', 'zephyr-project-manager'); ?></label>
								<select class="zpm_input" name="zpm-settings__default-status">
									<option value="" <?php echo isset($general_settings['default_status']) && $general_settings['default_status'] == '' ? 'selected' : ''; ?>><?php _e('None', 'zephyr-project-manager'); ?></option>
									<?php foreach ($statuses as $slug => $status) : ?>
										<option value="<?php esc_attr_e($slug); ?>" <?php echo isset($general_settings['default_status']) && $general_settings['default_status'] == $slug ? 'selected' : ''; ?>><?php esc_html_e($status['name']); ?></option>
									<?php endforeach; ?>
								</select>

								<div class="zpm-form-field-section">
									<label for="zpm-settings__use-current-dates-as-default" class="zpm-material-checkbox">
										<input type="checkbox" id="zpm-settings__use-current-dates-as-default" name="zpm-settings__use-current-dates-as-default" class="zpm_toggle invisible" value="1" <?php echo $general_settings['use_current_dates_as_default'] ? 'checked' : '';  ?>>
										<span class="zpm-material-checkbox-label"><?php _e('Use current date as the default start and due dates', 'zephyr-project-manager'); ?></span>
									</label>
								</div>

								<?php do_action('zpm_permission_settings'); ?>

								<label class="zpm_label zpm_divider_label"><?php _e('Customization', 'zephyr-project-manager') ?></label>

								<label class="zpm_label" for="zpm_colorpicker_primary"><?php _e('Primary Color', 'zephyr-project-manager'); ?></label>
								<input type="text" name="zpm_backend_primary_color" id="zpm_colorpicker_primary" class="zpm_input" value="<?php echo esc_attr($general_settings['primary_color']); ?>">

								<label class="zpm_label" for="zpm_colorpicker_primary_dark"><?php _e('Primary Dark Color', 'zephyr-project-manager'); ?></label>
								<input type="text" name="zpm_backend_primary_color_dark" id="zpm_colorpicker_primary_dark" class="zpm_input" value="<?php echo esc_attr($general_settings['primary_color_dark']); ?>">
								<label class="zpm_label" for="zpm_colorpicker_primary_light"><?php _e('Primary Light Color', 'zephyr-project-manager'); ?></label>
								<input type="text" name="zpm_backend_primary_color_light" id="zpm_colorpicker_primary_light" class="zpm_input" value="<?php echo esc_attr($general_settings['primary_color_light']); ?>">

								<label class="zpm_label zpm_divider_label"><?php _e('Dates & Calendar Settings', 'zephyr-project-manager') ?></label>

								<!-- First day of week -->
								<label class="zpm_label"><?php _e('Calendar First Day', 'zephyr-project-manager'); ?></label>
								<select id="zpm-settings-first-day" class="zpm_input" name="zpm-settings-first-day">
									<?php foreach ($days_of_week as $val => $name) : ?>
										<option value="<?php echo esc_attr($val); ?>" <?php echo $general_settings['first_day'] == $val ? 'selected' : ''; ?>><?php echo esc_html($name); ?></option>
									<?php endforeach; ?>
								</select>

								<!-- Date formats -->
								<label class="zpm_label"><?php _e('Date Format', 'zephyr-project-manager'); ?></label>
								<select id="zpm-settings-date-format" class="zpm_input" name="zpm-settings-date-format">
									<?php foreach ($date_formats as $val => $date) : ?>
										<option value="<?php echo esc_attr($val); ?>" <?php echo $general_settings['date_format'] == $val ? 'selected' : ''; ?>><?php echo esc_html($date); ?></option>
									<?php endforeach; ?>
								</select>

								<!-- Show Time -->
								<label for="zpm-setting__show-time" class="zpm-material-checkbox">
									<input type="checkbox" id="zpm-setting__show-time" name="zpm-setting__show-time" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['show_time']) && $general_settings['show_time'] == true ? 'checked' : '';  ?>>
									<span class="zpm-material-checkbox-label"><?php _e('Show Time', 'zephyr-project-manager'); ?></span>
								</label>

								<!-- Calendar colors -->
								<label class="zpm_label"><?php _e('Calendar Task Colors', 'zephyr-project-manager'); ?></label>
								<select id="zpm-settings-calendar-task-colors" class="zpm_input" name="zpm-settings-calendar-task-colors">
									<?php foreach ($calendarColorOptions as $val => $name) : ?>
										<option value="<?php echo esc_attr($val); ?>" <?php echo $general_settings['calendar_task_colors'] == $val ? 'selected' : ''; ?>><?php echo esc_html($name); ?></option>
									<?php endforeach; ?>
								</select>

								<div class="zpm-mt-2">
									<label for="zpm-settings-show-calendar-due-date-only" class="zpm-material-checkbox">
										<input type="checkbox" id="zpm-settings-show-calendar-due-date-only" name="zpm-settings-show-calendar-due-date-only" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['show_calendar_due_date_only']) && $general_settings['show_calendar_due_date_only'] == true ? 'checked' : '';  ?>>
										<span class="zpm-material-checkbox-label"><?php _e('Show calendar due date only', 'zephyr-project-manager'); ?></span>
									</label>
								</div>

								<div class="zpm-mt-2">
									<label class="zpm_label" for="zpm-settings-calendar-text-color"><?php _e('Calendar Text Color', 'zephyr-project-manager'); ?></label>
									<input type="text" name="zpm-settings-calendar-text-color" id="zpm-settings-calendar-text-color" class="zpm_input zpm-color-picker" value="<?php echo esc_attr($general_settings['calendar_text_color']); ?>" />
								</div>

								<div class="zpm-mt-2">
									<label for="zpm-settings-ics-sync-enabled" class="zpm-material-checkbox">
										<input type="checkbox" id="zpm-settings-ics-sync-enabled" name="zpm-settings-ics-sync-enabled" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['ics_sync_enabled']) && $general_settings['ics_sync_enabled'] == true ? 'checked' : '';  ?>>
										<span class="zpm-material-checkbox-label"><?php _e('Enable public synced ICS file', 'zephyr-project-manager'); ?></span>
									</label>
								</div>
								<small><?php printf(__('File will be available at %s', 'zephyr-project-manager'), '<a href="' . $icsUrl . '">' . $icsUrl . '</a>'); ?></small>

								<!-- Emails -->
								<label class="zpm_label zpm_divider_label"><?php _e('Email Settings', 'zephyr-project-manager') ?></label>
								<div class="zpm-form__group">
									<textarea name="zpm-settings__email-mentions-content" id="zpm-settings__email-mentions-content" class="zpm-form__field" placeholder="<?php _e('Mentions Email', 'zephyr-project-manager'); ?>" value="<?php echo esc_attr($general_settings['email_from_email']); ?>"><?php echo esc_textarea($general_settings['email_mentions_content']); ?></textarea>
									<label for="zpm-settings__email-mentions-content" class="zpm-form__label"><?php _e('Mentions Email', 'zephyr-project-manager'); ?></label>
								</div>

								<!-- Files -->
								<label class="zpm_label zpm_divider_label"><?php _e('File Settings', 'zephyr-project-manager') ?></label>
								<label class="zpm_label"><?php _e('File Icon', 'zephyr-project-manager'); ?></label>
								<div class="zpm-site-logo-container" data-file-container>
									<span class="zpm-settings-file-icon-background zpm-file-upload-icon" data-file-background></span>
									<label class="zpm-settings-file-icon zpm-file-upload-label" data-file-label style="background-image: url(<?php echo isset($general_settings['file_icon']) && !empty($general_settings['file_icon']) ? $general_settings['file_icon'] : Utillities::getFileIcon(); ?>);" for="zpm-frontend-site-logo"></label>
									<input type="hidden" id="zpm-settings-file-icon" name="zpm-settings-file-icon" data-file-input value="<?php echo isset($general_settings['file_icon']) ? $general_settings['file_icon'] : ''; ?>" />
									<label id="zpm-settings-file-icon-reset" class="zpm-file-upload-reset" data-file-reset><?php _e('Reset', 'zephyr-project-manager'); ?></label>
								</div>
								<label class="zpm_label"><?php _e('Folder Icon', 'zephyr-project-manager'); ?></label>
								<div class="zpm-site-logo-container" data-file-container>
									<span class="zpm-settings-folder-icon-background zpm-file-upload-icon" data-file-background></span>
									<label class="zpm-settings-folder-icon zpm-file-upload-label" data-file-label style="background-image: url(<?php echo isset($general_settings['folder_icon']) && !empty($general_settings['folder_icon']) ? $general_settings['folder_icon'] : Utillities::getFolderIcon(); ?>);" for="zpm-frontend-site-logo"></label>
									<input type="hidden" id="zpm-settings-folder-icon" name="zpm-settings-folder-icon" data-file-input value="<?php echo isset($general_settings['folder_icon']) ? $general_settings['folder_icon'] : ''; ?>" />
									<label id="zpm-settings-folder-icon-reset" class="zpm-file-upload-reset" data-file-reset><?php _e('Reset', 'zephyr-project-manager'); ?></label>
								</div>

								<label class="zpm_label zpm_divider_label"><?php _e('Task Settings', 'zephyr-project-manager') ?></label>
								<div>
									<label for="zpm-settings-hide-completed-tasks" class="zpm-material-checkbox">
										<input type="checkbox" id="zpm-settings-hide-completed-tasks" name="zpm-settings-hide-completed-tasks" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['hide_completed_tasks']) && $general_settings['hide_completed_tasks'] == true ? 'checked' : '';  ?>>
										<span class="zpm-material-checkbox-label"><?php _e('Hide completed tasks on My Tasks page', 'zephyr-project-manager'); ?></span>
									</label>
								</div>

								<div>
									<label for="zpm-settings-auto-unassign-on-project-remove" class="zpm-material-checkbox">
										<input type="checkbox" id="zpm-settings-auto-unassign-on-project-remove" name="zpm-settings-auto-unassign-on-project-remove" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['auto_unassign_on_project_remove']) && $general_settings['auto_unassign_on_project_remove'] == true ? 'checked' : '';  ?>>
										<span class="zpm-material-checkbox-label"><?php _e('Automatically unassign users from tasks when removed from the project', 'zephyr-project-manager'); ?></span>
									</label>
								</div>

								<div>
									<label for="zpm-settings-task-duration-enabled" class="zpm-material-checkbox">
										<input type="checkbox" id="zpm-settings-task-duration-enabled" name="zpm-settings-task-duration-enabled" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['task_duration_enabled']) && $general_settings['task_duration_enabled'] == true ? 'checked' : '';  ?>>
										<span class="zpm-material-checkbox-label"><?php _e('Enable the task "Duration" field', 'zephyr-project-manager'); ?></span>
									</label>
								</div>

								<div>
									<label for="zpm-settings-task-blocking-enabled" class="zpm-material-checkbox">
										<input type="checkbox" id="zpm-settings-task-blocking-enabled" name="zpm-settings-task-blocking-enabled" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['task_blocking_enabled']) && $general_settings['task_blocking_enabled'] == true ? 'checked' : '';  ?>>
										<span class="zpm-material-checkbox-label"><?php _e('Enable the blocking tasks feature', 'zephyr-project-manager'); ?></span>
									</label>
								</div>
								<?php do_action('zpm/settings/tasks', $general_settings); ?>

								<?php do_action('zpm_general_settings', ''); ?>

								<!-- Require Authentication for REST API Requests -->

								<label class="zpm_label zpm_divider_label"><?php _e('Other Settings', 'zephyr-project-manager') ?></label>
								<div>
									<label for="zpm-settings__rest-api-disable-authentication" class="zpm-material-checkbox">
										<input type="checkbox" id="zpm-settings__rest-api-disable-authentication" name="zpm-settings__rest-api-disable-authentication" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['rest_api_disable_authentication']) && $general_settings['rest_api_disable_authentication'] == true ? 'checked' : '';  ?>>
										<span class="zpm-material-checkbox-label"><?php _e('Disable Authentication for REST API Requests', 'zephyr-project-manager'); ?></span>
									</label>
								</div>

								<div>
									<label for="zpm-settings__enable-node" class="zpm-material-checkbox">
										<input type="checkbox" id="zpm-settings__enable-node" name="zpm-settings__enable-node" class="zpm_toggle invisible" value="1" <?php echo isset($general_settings['node_enabled']) && $general_settings['node_enabled'] == true ? 'checked' : '';  ?>>
										<span class="zpm-material-checkbox-label"><?php _e('Enable Node Server (For real-time updates)', 'zephyr-project-manager'); ?></span>
									</label>
								</div>

								<?php wp_nonce_field('zpm_save_general_settings'); ?>
								<button type="submit" class="zpm_button" name="zpm_save_general_settings" id="zpm_save_general_settings"><?php _e('Save Settings', 'zephyr-project-manager'); ?></button>
							</form>
						</div>
					<?php endif; ?>

					<?php if ($canAccessAdvancedSettings) : ?>
						<div class="zpm_tab_panel <?php echo $action == 'tools' ? 'zpm_tab_active' : ''; ?>" data-zpm-tab="tools">
							<!-- General Settings -->
							<form id="zpm_advanced_settings" method="post" enctype='multipart/form-data'>
								<button type="button" class="zpm_button" zpm-send-test-emails><?php _e("Send Test Emails", 'zephyr-project-manager'); ?></button>
								<button type="button" class="zpm_button" data-export-tasks-csv><?php _e("Export Tasks To CSV", 'zephyr-project-manager'); ?></button>
								<button type="button" class="zpm_button" data-export-projects-csv><?php _e("Export Projects To CSV", 'zephyr-project-manager'); ?></button>
								<?php do_action('zpm/settings/tools'); ?>
								<?php wp_nonce_field('zpm_save_general_settings'); ?>
							</form>
						</div>
						<div class="zpm_tab_panel <?php echo $action == 'advanced' ? 'zpm_tab_active' : ''; ?>" data-zpm-tab="advanced">
							<!-- General Settings -->
							<form id="zpm_advanced_settings" method="post" enctype='multipart/form-data'>

								<?php do_action('zpm_advanced_settings_content'); ?>

								<div class="zpm-form__group">
									<textarea type="text" name="zpm-settings__custom-css" id="zpm-settings__custom-css" class="zpm-form__field" placeholder="<?php _e('Custom CSS', 'zephyr-project-manager'); ?>"><?php echo esc_textarea($general_settings['custom_css']); ?></textarea>
									<label for="zpm-settings__custom-css" class="zpm-form__label"><?php _e('Custom CSS', 'zephyr-project-manager'); ?></label>
								</div>

								<?php if (current_user_can('administrator')) : ?>
									<button type="button" class="zpm_button zpm-button__red" id="zpm-delete-data__button" data-zpm-modal="zpm-delete-data__modal"><?php _e("DELETE all Zephyr Project Manager Data", 'zephyr-project-manager'); ?></button>
									<?php endif; ?>

									<?php wp_nonce_field('zpm_save_general_settings'); ?>
								<button type="submit" class="zpm_button" name="zpm-settings__advanced-submit"><?php _e('Save Settings', 'zephyr-project-manager'); ?></button>
							</form>

							<?php if (current_user_can('administrator')) : ?>
								<form id="zpm-delete-data__form" style="display: none;" method="post" name="zpm-delete-all-data">
									<?php echo wp_nonce_field('zpm_delete_data'); ?>
									<button type="submit" name="zpm-delete-all-data"></button>
								</form>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php
					$pages = ob_get_clean();
					echo apply_filters('zpm_settings_pages', $pages);
					?>
					<?php
					foreach ($settingsPages as $page) {
					?>
						<div class="zpm_tab_panel <?php echo $action == $page['slug'] ? 'zpm_tab_active' : ''; ?>" data-zpm-tab="<?php echo esc_attr($page['slug']); ?>">
							<?php echo zpm_esc_html($page['content']); ?>
						</div>
					<?php
					}

					foreach ($customTabs as $id => $tab) {
					?>
						<div class="zpm_tab_panel <?php echo $action == $id ? 'zpm_tab_active' : ''; ?>" data-zpm-tab="<?php esc_attr_e($id); ?>">
							<?php echo $tab['content']; ?>
						</div>
					<?php
					}
					?>

				</div>
			</div>
		</div>
	</div>
</main>

<?php $this->get_footer(); ?>