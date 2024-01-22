<?php
/**
 * Job dashboard shortcode content.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-dashboard.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @version     1.35.2
 *
 * @since 1.34.4 Available job actions are passed in an array (`$job_actions`, keyed by job ID) and not generated in the template.
 * @since 1.35.0 Switched to new date functions.
 *
 * @var array     $job_dashboard_columns Array of the columns to show on the job dashboard page.
 * @var int       $max_num_pages         Maximum number of pages
 * @var WP_Post[] $jobs                  Array of job post results.
 * @var array     $job_actions           Array of actions available for each job.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$submission_limit			= get_option( 'job_manager_submission_limit' );
$submit_job_form_page_id	= get_option( 'job_manager_submit_job_form_page_id' );
?>

<?php if ( ! $jobs ) : ?>
	<div>
		<?php
		$profile = PeepSoProfileShortcode::get_instance();
		if ($profile->get_view_user_id() != get_current_user_id()) {
			$user = PeepSoUser::get_instance($profile->get_view_user_id());
			$message = sprintf(__("%s doesn't have any active listings yet", 'peepso-wpjm'), $user->get_fullname());
		} else {
			$message = __('You do not have any active listings.', 'peepso-wpjm');
		}
		?>
		<div class="ps-alert"><?php echo $message; ?></div>
	</div>
<?php else : ?>
	<div id="ps-job-dashboard">
		<div class="ps-job-dashboard__inner">
			<?php foreach ( $jobs as $job ) : ?>
				<div class="ps-job-dashboard__item">
					<div class="ps-job-dashboard__item-details">
						<?php foreach ( $job_dashboard_columns as $key => $column ) : ?>
							<div class="<?php echo esc_attr( $key ); ?>">
								<?php if ('job_title' === $key ) : ?>
									<?php if ( $job->post_status == 'publish' ) : ?>
										<a href="<?php echo esc_url( get_permalink( $job->ID ) ); ?>"><?php wpjm_the_job_title( $job ); ?></a>
									<?php else : ?>
										<?php wpjm_the_job_title( $job ); ?> <small>(<?php the_job_status( $job ); ?>)</small>
									<?php endif; ?>
									<?php echo is_position_featured( $job ) ? '<span class="featured-job-icon" title="' . esc_attr__( 'Featured Job', 'wp-job-manager' ) . '"><i class="gcis gci-star"></i></span>' : ''; ?>
								<?php elseif ('date' === $key ) : ?>
									<div title="Date Posted"><i class="gcis gci-clock"></i> <?php echo esc_html( wp_date( get_option( 'date_format' ), get_post_datetime( $job )->getTimestamp() ) ); ?></div>
								<?php elseif ('expires' === $key ) : ?>
									<div title="Listing Expires"><i class="gcis gci-history"></i> <?php echo __('expired', 'peepso-wpjm');?> <?php
									$job_expires = WP_Job_Manager_Post_Types::instance()->get_job_expiration( $job );
									echo esc_html( $job_expires ? wp_date( get_option( 'date_format' ), $job_expires->getTimestamp() ) : '&ndash;' );
									?></div>
								<?php elseif ('filled' === $key ) : ?>
									<?php echo __('Filled?', 'peepso-wpjm');?> <?php echo is_position_filled( $job ) ? '<i class="gcis gci-circle-check"></i>' : '&ndash;'; ?>
								<?php else : ?>
									<?php do_action( 'job_manager_job_dashboard_column_' . $key, $job ); ?>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
					
					<ul class="job-dashboard-actions">
						<?php
							if ( ! empty( $job_actions[ $job->ID ] ) ) {
								foreach ( $job_actions[ $job->ID ] as $action => $value ) {
									$action_url = add_query_arg( [
										'action' => $action,
										'job_id' => $job->ID
									] );
									if ( $value['nonce'] ) {
										$action_url = wp_nonce_url( $action_url, $value['nonce'] );
									}
									echo '<li><a href="' . esc_url( $action_url ) . '" class="job-dashboard-action-' . esc_attr( $action ) . '">' . esc_html( $value['label'] ) . '</a></li>';
								}
							}
						?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>
<?php if (PeepSoWPJM_Permissions::user_can_create()) { ?>
<?php if ( $submit_job_form_page_id && ( job_manager_count_user_job_listings() < $submission_limit || ! $submission_limit ) ) : ?>
	<div class="ps-job-dashboard-actions">
		<a href="<?php echo esc_url( get_permalink( $submit_job_form_page_id ) ); ?>"><?php esc_html_e( 'Add Job', 'wp-job-manager' ); ?></a>
	</div>
<?php endif; ?>
<?php } ?>

<?php get_job_manager_template( 'pagination.php', [ 'max_num_pages' => $max_num_pages ] ); ?>