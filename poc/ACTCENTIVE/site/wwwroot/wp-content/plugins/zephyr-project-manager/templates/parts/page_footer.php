<?php

/**
 * Template for displaying the footer of the Zephyr Project Manager pages
 */
if (!defined('ABSPATH')) {
	die;
}

use ZephyrProjectManager\Core\Tasks;
use ZephyrProjectManager\Core\Projects;
use ZephyrProjectManager\Core\Utillities;
use ZephyrProjectManager\Core\Categories;
use ZephyrProjectManager\Base\BaseController;
use ZephyrProjectManager\Pro\CustomFields;

$taskExportHeaders = Tasks::getExportHeaders();

?>

<div id="zpm_new_file_upload" class="zpm-modal" aria-hidden="true" role="dialog" aria-modal="true">
	<h3 class="zpm_modal_header"><?php _e('New Attachment', 'zephyr-project-manager'); ?></h3>
	<input type="hidden" id="zpm_uploaded_file_name">
	<input type="hidden" id="zpm-uploaded-filename">
	<label class="zpm_label"><?php _e('Project', 'zephyr-project-manager'); ?></label>
	<?php Projects::project_select('zpm_file_upload_project'); ?>
	<div class="zpm_modal_footer">
		<button id="zpm_upload_file" class="zpm_button"><?php _e('Select File', 'zephyr-project-manager'); ?></button>
		<button id="zpm_submit_file" class="zpm_button"><?php _e('Upload Attachment', 'zephyr-project-manager'); ?></button>
	</div>
</div>
<?php Tasks::new_task_modal(); ?>
<?php Tasks::view_container(); ?>
<?php Projects::project_modal(); ?>
<?php Categories::new_category_modal(); ?>
<?php Categories::new_status_modal(); ?>
<?php Categories::edit_status_modal(); ?>
<?php if (BaseController::is_pro()) : ?>
	<?php CustomFields::task_custom_fields(); ?>
<?php endif; ?>

<?php Projects::view_project_container(); ?>

<div id="zpm-task-to-project-modal" class="zpm-modal" aria-modal="true" aria-hidden="true">
	<h3 class="zpm-modal-title"><?php _e('Copy to Project', 'zephyr-project-manager'); ?></h3>

	<div class="zpm-modal-content">
		<input type="hidden" id="zpm-kanban-to-project-task-id" />
		<input type="hidden" id="zpm-kanban-to-project-task-name" />
		<?php Projects::project_select('zpm-kanban-to-project-id'); ?>
	</div>

	<div class="zpm-modal-buttons">
		<button id="zpm-kanban-copy-task-to-project" class="zpm_button"><?php _e('Copy Task', 'zephyr-project-manager'); ?></button>
	</div>
</div>

<div id="zpm-column-to-project-modal" class="zpm-modal" aria-modal="true" aria-hidden="true">
	<h3 class="zpm-modal-title"><?php _e('Copy Column to Project', 'zephyr-project-manager'); ?></h3>

	<input type="hidden" id="zpm-kanban-to-project__project-id" />

	<div class="zpm-modal-content">
		<input type="hidden" id="zpm-kanban-column-to-project-task-id" />
		<input type="hidden" id="zpm-kanban-column-to-project-task-name" />
		<?php Projects::project_select('zpm-kanban-column-to-project-id'); ?>
	</div>

	<div class="zpm-modal-buttons">
		<button id="zpm-kanban-column-to-project-btn" class="zpm_button"><?php _e('Copy Column', 'zephyr-project-manager'); ?></button>
	</div>
</div>

<?php
Utillities::zephyr_modal(
	'zpm-task-attachments-modal',
	__('Task Attachments', 'zephyr-project-manager'),
	'',
	array(
		array(
			'id' => 'zpm-task-attachments__close-btn',
			'text' => __('Close', 'zephyr-project-manager')
		)
	)
);
?>

<template data-zpm-template="taskExportModal">
	<div data-task-export-options>
		<h3 class="zpm-modal-header"><?php _e('Export Tasks', 'zephyr-project-manager'); ?></h3>
		<div>
			<div class="zpm-checkbox-group">
				<input type="checkbox" data-export-names />
				<label><?php _e('Export names instead of IDs', 'zephyr-project-manager'); ?></label>
			</div>
			<div class="zpm-task-export-options-dates">
				<div>
					<label><?php _e('From', 'zephyr-project-manager'); ?></label>
					<input type="date" data-from />
				</div>
				<div>
					<label><?php _e('To', 'zephyr-project-manager'); ?></label>
					<input type="date" data-to />
				</div>
			</div>

			<h4><?php _e('Columns to Export', 'zephyr-project-manager'); ?></h4>

			<?php foreach ($taskExportHeaders as $slug => $header): ?>
				<div class="zpm-checkbox-group">
					<input type="checkbox" data-header="<?php echo esc_attr($slug); ?>" checked />
					<label><?php echo esc_html($header); ?></label>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="zpm-modal-footer">
			<button data-submit-task-export-button class="zpm_button"><?php _e('Export', 'zephyr-project-manager'); ?></button>
		</div>
	</div>
</template>

<?php do_action('zpm_modals'); ?>