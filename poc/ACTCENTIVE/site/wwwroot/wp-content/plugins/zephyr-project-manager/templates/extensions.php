<?php

	if ( !defined( 'ABSPATH' ) ) {
		die;
	}
	
	use ZephyrProjectManager\Core\Tasks;
	use ZephyrProjectManager\Core\Members;
	use ZephyrProjectManager\Api\Callbacks\AdminCallbacks;
	use ZephyrProjectManager\Base\BaseController;

	$zpm_base = new AdminCallbacks();
	$extensions = zpm_get_extensions();
?>

<div class="zpm_settings_wrap">
	<?php $zpm_base->get_header(); ?>
	<div id="zpm_container" class="zpm_custom_fields">
		<h3 class="zpm-info-title"><?php _e( 'Extensions', 'zephyr-project-manager' ); ?> <small class="zpm-heading-subtext">- <?php _e( 'Zephyr Project Manager add-ons to increase productivity and integrate with your favourite tools', 'zephyr-project-manager' ); ?></small></h3>

		<div id="zpm-extensions">
			<?php foreach($extensions as $extension) : ?>
				<div class="zpm-extension" style="background-color: <?php echo esc_attr($extension['color']); ?>;">
					<p class="zpm-extension__title"><?php echo esc_html($extension['title']); ?></p>
					<?php if (!$extension['installed']) : ?>
						<p class="zpm-extension__description"><?php echo zpm_esc_html($extension['description']); ?></p>
						<div class="zpm-buttons__float-right">
							<a href="<?php echo esc_url($extension['link']); ?>" class="zpm_button zpm-button__white" style="color: <?php echo esc_attr($extension['color']); ?> !important;"><?php _e( 'Get Now', 'zephyr-project-manager' ); ?></a>
						</div>
					<?php else: ?>
						<p class="zpm-extension__description zpm-extension__installed"><i class="fa fa-check"></i><?php _e( 'Installed', 'zephyr-project-manager' ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php $zpm_base->get_footer(); ?>
</div>