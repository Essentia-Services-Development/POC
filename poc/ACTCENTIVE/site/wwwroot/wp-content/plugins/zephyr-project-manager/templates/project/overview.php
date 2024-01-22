<?php
    // Project overview (used for PDF, printing, etc.)

    if (!defined('ABSPATH')) die;

    use ZephyrProjectManager\Core\Tasks;
    use ZephyrProjectManager\Core\Projects;

    if (is_null($project)) return;

    $tasks = Tasks::get_project_tasks($project->id);
    $files = Projects::getFiles($project->id);
    $description = zpm_esc_html($project->description);
?>

<div class="zpm-project-overview" data-project-overview="<?php esc_attr_e($project->id); ?>">
    <?php do_action('zpm/project/overview/before', $project); ?>

    <header class="zpm-project-overview-header" data-project-overview-name><?php esc_html_e($project->name); ?></header>
    
    <div class="zpm-overview-field">
        <label class="zpm-overview-field--label"><?php _e('Name', 'zephyr-project-manager'); ?></label>
        <div class="zpm-overview-field--value"><?php esc_html_e($project->name); ?></div>
    </div>
    <div class="zpm-overview-field">
        <label class="zpm-overview-field--label"><?php _e('Description', 'zephyr-project-manager'); ?></label>
        <div class="zpm-overview-field--value"><?php echo !empty($description) ? $description : __('None', 'zephyr-project-manager'); ?></div>
    </div>
    <div class="zpm-overview-field">
        <label class="zpm-overview-field--label"><?php _e('Start Date', 'zephyr-project-manager'); ?></label>
        <div class="zpm-overview-field--value"><?php echo zpm_date($project->date_start, __('None', 'zephyr-project-manager')); ?></div>
    </div>
    <div class="zpm-overview-field">
        <label class="zpm-overview-field--label"><?php _e('Due Date', 'zephyr-project-manager'); ?></label>
        <div class="zpm-overview-field--value"><?php echo zpm_date($project->date_due, __('None', 'zephyr-project-manager')); ?></div>
    </div>

    <?php do_action('zpm/project/overview/fields', $project); ?>

    <div class="zpm-overview-field">
        <label class="zpm-overview-field--label"><?php _e('Tasks', 'zephyr-project-manager'); ?></label>
        <div class="zpm-overview-field--value">
            <?php if (empty($tasks)): ?>
                <?php _e('No tasks', 'zephyr-project-manager'); ?>
            <?php endif; ?>
            <?php foreach ($tasks as $task): ?>
                <?php $description = zpm_esc_html($task->description); ?>
                <?php $isCompleted = Tasks::isCompleted($task); ?>
                <div>
                    <span>
                        <label for="zpm_task_id_<?php esc_attr_e($task->id); ?>" class="zpm-material-checkbox">
                        <input type="checkbox" id="zpm_task_id_<?php esc_attr_e($task->id); ?>" name="zpm_task_id_<?php esc_attr_e($task->id); ?>" class="zpm_task_mark_complete zpm_toggle invisible" data-task-id="<?php esc_attr_e($task->id); ?>" aria-label="Toggle task completion" <?php echo $isCompleted ? 'checked' : ''; ?>>
                        <span class="zpm-material-checkbox-label"></span>
                      </label>
                    </span>
                    <span><?php esc_html_e($task->name); ?></span>
                    <?php if (!empty($description)): ?>
                        <span> - <?php echo $description; ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="zpm-overview-field">
        <label class="zpm-overview-field--label"><?php _e('Files', 'zephyr-project-manager'); ?></label>
        <div class="zpm-overview-field--value">
            <?php if (empty($files)): ?>
                <?php _e('No files', 'zephyr-project-manager'); ?>
            <?php endif; ?>
            <?php foreach ($files as $file): ?>
                <a href="<?php echo esc_url($file['url']); ?>" target="_BLANK"><?php esc_attr_e($file['name']); ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php do_action('zpm/project/overview/after', $project); ?>
</div>