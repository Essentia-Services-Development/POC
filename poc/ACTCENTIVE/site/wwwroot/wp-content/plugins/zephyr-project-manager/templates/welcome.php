<?php
	// The welcome page
	if (!defined('ABSPATH')) die;

	add_option('zpm_first_time', true);
?>

<main id="zpm_welcome_page">
	<div id="zpm_welcome_container" class="zpm_body">
		<h1><?php _e( 'Welcome to Zephyr Project Manager', 'zephyr-project-manager' ); ?></h1>
		<div id="zpm-welcome-content">
			<span class="zpm-col-4">
				<div class="zpm-feature-image-holder">
					<img class="zpm-feature-image" src="<?php echo esc_url(ZPM_PLUGIN_URL . 'assets/img/icon-tasks.png'); ?>">
				</div>
				<div class="zpm-feature-text">
					<h3 class="zpm-feature-title"><?php _e( 'Get Things Done', 'zephyr-project-manager' ); ?></h3>
					<p class="zpm-feature-description"><?php _e( 'Create unlimited projects, tasks and subtasks and manage them easily and get work done all inside WordPress.', 'zephyr-project-manager' ); ?></p>
				</div>
			</span>
			<span class="zpm-col-4">
				<div class="zpm-feature-image-holder">
					<img class="zpm-feature-image" src="<?php echo esc_url(ZPM_PLUGIN_URL . 'assets/img/icon-discussion.png'); ?>">
				</div>
				<div class="zpm-feature-text">
					<h3 class="zpm-feature-title"><?php _e( 'Communicate and collaborate', 'zephyr-project-manager' ); ?></h3>
					<p class="zpm-feature-description"><?php _e( 'Manage user roles, receive updates and notifications via email and add comments and discussions to tasks and projects.', 'zephyr-project-manager' ); ?></p>
				</div>
			</span>
			<span class="zpm-col-4">
				<div class="zpm-feature-image-holder">
					<img class="zpm-feature-image" src="<?php echo esc_url(ZPM_PLUGIN_URL . 'assets/img/icon-calender.png'); ?>">
				</div>
				<div class="zpm-feature-text">
					<h3 class="zpm-feature-title"><?php _e('Calender and Staying up to date', 'zephyr-project-manager'); ?></h3>
					<p class="zpm-feature-description"><?php _e('Plan tasks correctly with the built in calender. See upcoming, completed and tasks in progress. You will also receive notifications within WordPress and email notifications.', 'zephyr-project-manager'); ?></p>
				</div>
			</span>

			<span class="zpm-col-4">
				<div class="zpm-feature-image-holder">
					<img class="zpm-feature-image" src="<?php echo esc_url(ZPM_PLUGIN_URL . 'assets/img/icon-users.png'); ?>">
				</div>
				<div class="zpm-feature-text">
					<h3 class="zpm-feature-title"><?php _e('Manage Files', 'zephyr-project-manager'); ?></h3>
					<p class="zpm-feature-description"><?php _e('Manage, view and download files from discussions all in one place.', 'zephyr-project-manager'); ?></p>
				</div>
			</span>
			<span class="zpm-col-4">
				<div class="zpm-feature-image-holder">
					<img class="zpm-feature-image" src="<?php echo esc_url(ZPM_PLUGIN_URL . 'assets/img/icon-stats.png'); ?>">
				</div>
				<div class="zpm-feature-text">
					<h3 class="zpm-feature-title"><?php _e('Progress tracking and Statistics', 'zephyr-project-manager'); ?></h3>
					<p class="zpm-feature-description"><?php _e('Automatic progress tracking and statistic updates, help keep you in the loop and keep the projects on track.', 'zephyr-project-manager'); ?></p>
				</div>
			</span>
			<span class="zpm-col-4">
				<div class="zpm-feature-image-holder">
					<img class="zpm-feature-image" src="<?php echo esc_url(ZPM_PLUGIN_URL . 'assets/img/icon-ellipsis.png'); ?>">
				</div>
				<div class="zpm-feature-text">
					<h3 class="zpm-feature-title"><?php _e('And much more...', 'zephyr-project-manager'); ?></h3>
					<p class="zpm-feature-description"><?php _e('Plus many more features. If you have any feature suggestions I would be more than happy to hear them. You can contact me at dylanjkotze@gmail.com.', 'zephyr-project-manager'); ?></p>
				</div>
			</span>
		</div>

		<form method="post">
			<button id="zpm_get_started" name="zpm_first_time" class="zpm_button"><?php _e('Get started and create your first project.', 'zephyr-project-manager'); ?></button>
			
			<?php if (!zpmIsPro()): ?>
				<a class="zpm_button" href="https://zephyr-one.com/purchase-pro" target="_blank"><?php _e('Get Zephyr Pro Now', 'zephyr-project-manager'); ?></a>
			<?php endif; ?>
		</form>
	</div>
</main>
<?php $this->get_footer(); ?>