<?php
	/*
	* Files Page
	* Page is used to display and view/download files that have been included in Tasks and Projects
	*/

	if ( !defined( 'ABSPATH' ) ) {
		die;
	}

	use ZephyrProjectManager\Core\Tasks;
	use ZephyrProjectManager\Core\Projects;
	use ZephyrProjectManager\Core\Utillities;
	use ZephyrProjectManager\Base\BaseController;

	$attachments = BaseController::get_attachments();
	$projects = Projects::get_projects();
	$filetypes = array();

	// Get an array of all filetypes that are used
	foreach ($attachments as $attachment) {
		$attachment_url = wp_get_attachment_url($attachment['message']);
		$attachment_type = wp_check_filetype($attachment_url)['ext'];
		
		if (!in_array($attachment_type, $filetypes)) {
			array_push($filetypes, $attachment_type);
		}
	}
?>

<main id="zpm_file_page" class="zpm_settings_wrap">
	<?php $this->get_header(); ?>
	<div id="zpm_container">
		<h1 class="zpm_page_title"><?php _e( 'Files', 'zephyr-project-manager' ); ?></h1>
		<?php if (Utillities::canUploadFiles()) : ?>
			<button data-task-id="no-task" id="zpm_upload_file_btn" class="zpm_task_chat_files zpm_button"><?php _e( 'Upload Files', 'zephyr-project-manager' ); ?></button>
		<?php endif; ?>
		<div class="zpm_body">
			<div class="zpm_side_navigation">
				<ul>
					<li data-project-id="-1" class="zpm_filter_file zpm_selected_link"><?php _e( 'All Files', 'zephyr-project-manager' ); ?></li>
					<?php foreach($projects as $project) : ?>
						<li data-project-id="<?php echo esc_attr($project->id); ?>" class="zpm_filter_file"><?php echo esc_html($project->name); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<input type="hidden" id="zpm-new-file-project-value" value="-1">

			<div class="zpm_files_container">
				<?php foreach($attachments as $attachment) : ?>
					<?php echo Utillities::getFileHtml($attachment); ?>
				<?php endforeach; ?>
				<p id="zpm_no_files" class="zpm_error_message" style="display: none;"><?php _e( 'No Files', 'zephyr-project-manager' ); ?></p>
			</div>
		</div>
	</div>
</main>
<?php $this->get_footer(); ?>