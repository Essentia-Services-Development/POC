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
$args = array('can_zephyr' => true);
$users = $manager::get_users(true, $args);
$date = date('Y-m-d');
$statuses = Utillities::get_statuses('status');
$priorities = Utillities::get_statuses('priority');
$general_settings = Utillities::general_settings();
$extra_classes = $general_settings['hide_default_task_fields'] == '1' ? 'zpm-hide-default-fields' : '';
$defaultProject = isset($general_settings['default_project']) ? $general_settings['default_project'] : '-1';
$defaultAssignee = isset($general_settings['default_assignee']) ? $general_settings['default_assignee'] : '-1';
?>

<div id="zpm-edit-multiple-tasks-modal" class="zpm-modal zpm-form zpm-modal-default" role="dialog" aria-modal="true" aria-hidden="true">
    <h5 class="zpm_modal_header"><?php _e('Edit Multiple Tasks', 'zephyr-project-manager'); ?></h5>
    <div class="zpm_modal_body">
        <div class="zpm_modal_content">
            <!-- Assignees -->
            <div class="zpm-bulk-task-edit--toggle-container active" data-edit-multiple-task-container>
                <div class="zpm-edit-multiple-task--header" data-edit-multiple-task-header>
                    <label for="zpm-bulk-task-edit--assignee-toggle" class="zpm-material-checkbox">
                        <input type="checkbox" id="zpm-bulk-task-edit--assignee-toggle" checked class="zpm_toggle invisible" value="1" aria-label="<?php esc_attr_e('Toggle setting', 'zephyr-project-manager'); ?>" data-edit-multiple-task-toggle>
                        <span class="zpm-material-checkbox-label"></span>
                    </label>
                    <label class="zpm_label" for="zpm-task-list-assignees"><?php _e('Assignees', 'zephyr-project-manager'); ?></label>
                </div>
                <div class="zpm-edit-multiple-task--setting" data-edit-multiple-task-setting>
                    <select data-edit-multiple-task-input="assignees" id="zpm-task-list-assignees" class="zpm-chosen zpm-chosen-input" multiple data-placeholder="<?php _e('Select Assignees', 'zephyr-project-manager'); ?>">
                        <?php foreach ($users as $user) : ?>
                            <?php if (!Members::canViewMember($user['id'])) {
                                continue;
                            } ?>
                            <option value="<?php echo esc_attr($user['id']); ?>" <?php echo $defaultAssignee == $user['id'] ? 'selected' : ''; ?>><?php echo esc_html($user['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Start Date -->
            <div class="zpm-bulk-task-edit--toggle-container" data-edit-multiple-task-container>
                <div class="zpm-edit-multiple-task--header" data-edit-multiple-task-header>
                    <label for="zpm-bulk-task-edit--start-date-toggle" class="zpm-material-checkbox">
                        <input type="checkbox" id="zpm-bulk-task-edit--start-date-toggle" class="zpm_toggle invisible" value="1" aria-label="<?php esc_attr_e('Toggle setting', 'zephyr-project-manager'); ?>" data-edit-multiple-task-toggle>
                        <span class="zpm-material-checkbox-label"></span>
                    </label>
                    <label class="zpm_label" for="zpm-task-list-start-date"><?php _e('Start Date', 'zephyr-project-manager'); ?></label>
                </div>
                <div class="zpm-edit-multiple-task--setting" data-edit-multiple-task-setting>
                    <input type="text" autocomplete="off" data-edit-multiple-task-input="startDate" class="zpm-form__field zpm-datepicker" placeholder="<?php _e('Start Date', 'zephyr-project-manager'); ?>" />
                </div>
            </div>

            <!-- Due Date -->
            <div class="zpm-bulk-task-edit--toggle-container" data-edit-multiple-task-container>
                <div class="zpm-edit-multiple-task--header" data-edit-multiple-task-header>
                    <label for="zpm-bulk-task-edit--due-date-toggle" class="zpm-material-checkbox">
                        <input type="checkbox" id="zpm-bulk-task-edit--due-date-toggle" class="zpm_toggle invisible" value="1" aria-label="<?php esc_attr_e('Toggle setting', 'zephyr-project-manager'); ?>" data-edit-multiple-task-toggle>
                        <span class="zpm-material-checkbox-label"></span>
                    </label>
                    <label class="zpm_label" for="zpm-task-list-due-date"><?php _e('Due Date', 'zephyr-project-manager'); ?></label>
                </div>
                <div class="zpm-edit-multiple-task--setting" data-edit-multiple-task-setting>
                    <input type="text" autocomplete="off" data-edit-multiple-task-input="dueDate" class="zpm-form__field zpm-datepicker" placeholder="<?php _e('Due Date', 'zephyr-project-manager'); ?>" />
                </div>
            </div>

            <!-- Status -->
            <div class="zpm-bulk-task-edit--toggle-container" data-edit-multiple-task-container>
                <div class="zpm-edit-multiple-task--header" data-edit-multiple-task-header>
                    <label for="zpm-bulk-task-edit--status-toggle" class="zpm-material-checkbox">
                        <input type="checkbox" id="zpm-bulk-task-edit--status-toggle" class="zpm_toggle invisible" value="1" aria-label="<?php esc_attr_e('Toggle setting', 'zephyr-project-manager'); ?>" data-edit-multiple-task-toggle>
                        <span class="zpm-material-checkbox-label"></span>
                    </label>
                    <label class="zpm_label" for="zpm-task-list-status"><?php _e('Status', 'zephyr-project-manager'); ?></label>
                </div>
                <div class="zpm-edit-multiple-task--setting" data-edit-multiple-task-setting>
                    <select data-edit-multiple-task-input="status" id="zpm-task-list-status" class="zpm-chosen zpm-chosen-input" data-placeholder="<?php _e('Select Assignees', 'zephyr-project-manager'); ?>">
                        <?php foreach ($statuses as $key => $status) : ?>
                            <option value="<?php esc_attr_e($key); ?>"><?php esc_html_e($status['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Priority -->
            <div class="zpm-bulk-task-edit--toggle-container" data-edit-multiple-task-container>
                <div class="zpm-edit-multiple-task--header" data-edit-multiple-task-header>
                    <label for="zpm-bulk-task-edit--priority-toggle" class="zpm-material-checkbox">
                        <input type="checkbox" id="zpm-bulk-task-edit--priority-toggle" class="zpm_toggle invisible" value="1" aria-label="<?php esc_attr_e('Toggle setting', 'zephyr-project-manager'); ?>" data-edit-multiple-task-toggle>
                        <span class="zpm-material-checkbox-label"></span>
                    </label>
                    <label class="zpm_label" for="zpm-task-list-priority"><?php _e('Priority', 'zephyr-project-manager'); ?></label>
                </div>
                <div class="zpm-edit-multiple-task--setting" data-edit-multiple-task-setting>
                    <select data-edit-multiple-task-input="priority" id="zpm-task-list-priority" class="zpm-chosen zpm-chosen-input" data-placeholder="<?php _e('Select Assignees', 'zephyr-project-manager'); ?>">
                        <?php foreach ($priorities as $key => $priority) : ?>
                            <option value="<?php esc_attr_e($key); ?>"><?php esc_html_e($priority['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="zpm_modal_buttons">
            <button type="submit" class="zpm_button" data-update-multiple-tasks-button><?php _e('Update Selected Tasks', 'zephyr-project-manager'); ?></button>
        </div>
    </div>
</div>