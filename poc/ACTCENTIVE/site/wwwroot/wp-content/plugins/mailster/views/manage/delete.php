<?php
if ( mailster( 'subscribers' )->get_count_by_status( false ) ) :

	$lists   = mailster( 'lists' )->get( null, false );
	$no_list = mailster( 'lists' )->count( false );

	?>
<h2><?php esc_html_e( 'Which subscribers do you like to delete?', 'mailster' ); ?><?php echo mailster()->beacon( '63fbb3fe81d3090330dcbd5e' ); ?></h2>

<form method="post" id="delete-subscribers">
	<?php wp_nonce_field( 'mailster_nonce' ); ?>

<h4><?php esc_html_e( 'Lists', 'mailster' ); ?></h4>
<section>
	<?php if ( ! empty( $lists ) ) : ?>
	<ul>
		<li><label><input type="checkbox" class="list-toggle"> <?php esc_html_e( 'toggle all', 'mailster' ); ?></label></li>
		<li>&nbsp;</li>
			<?php mailster( 'lists' )->print_it( null, false, 'lists', esc_html__( 'total', 'mailster' ) ); ?>
	</ul>
	<?php endif; ?>
	<?php if ( $no_list ) : ?>
	<ul>
		<li><label><input type="hidden" name="nolists" value="0"><input type="checkbox" name="nolists" value="1"> <?php esc_html_e( 'subscribers not assigned to a list', 'mailster' ) . ' <span class="count">(' . number_format_i18n( $no_list ) . ' ' . esc_html__( 'total', 'mailster' ) . ')</span>'; ?></label></li>
	</ul>
	<?php endif; ?>
</section>
<h4><?php esc_html_e( 'Conditions', 'mailster' ); ?></h4>
<section>
		<p class="howto"> <?php esc_html_e( 'Define conditions to segment your selection further.', 'mailster' ); ?> </p>
	<?php mailster( 'conditions' )->view( array(), 'conditions' ); ?>
</section>
<h4><?php esc_html_e( 'Status', 'mailster' ); ?></h4>
<section>
	<p>
	<?php foreach ( mailster( 'subscribers' )->get_status( null, true ) as $i => $name ) : ?>
			<?php
			if ( 5 == $i ) :
				continue;
		endif;
			?>
		<label><input type="checkbox" name="status[]" value="<?php echo (int) $i; ?>"> <?php echo esc_html( $name ); ?> </label>
		<?php endforeach; ?>
	</p>
	<p>
		<label><input type="checkbox" name="remove_actions" value="1" checked> <?php esc_html_e( 'Remove all actions from affected users', 'mailster' ); ?> </label>
		<br><span class="howto"> <?php esc_html_e( 'This will remove all actions from the affected users as well which can change the stats of your campaigns.', 'mailster' ); ?></span>
	</p>
</section>
<h4><?php esc_html_e( 'Automation', 'mailster' ); ?></h4>
<section>
	<?php if ( $jobs = get_option( 'mailster_manage_jobs', array() ) ) : ?>
		<?php foreach ( $jobs as $hash => $job ) : ?>
	<div class="manage-job" data-id="<?php echo esc_attr( $hash ); ?>">
	<a class="remove-job" title="<?php esc_html_e( 'Remove job', 'mailster' ); ?>">&#10005;</a>
	<h4><?php echo esc_html( $job['name'] ); ?></h4>
			<?php include MAILSTER_DIR . 'views/manage/job.php'; ?>
	</div>
	<?php endforeach; ?>
	<?php endif; ?>
	<p class="howto"><?php esc_html_e( 'Mailster can perform this job on a regular basis to keep your list clean and healthy. Click this button to add a new job with the current settings.', 'mailster' ); ?></p>
	<p class="howto"><?php esc_html_e( 'These jobs will run automatically.', 'mailster' ); ?> <?php printf( esc_html__( 'Deleted contacts will get marked as deleted first and will get permanently removed after %d days.', 'mailster' ), 14 ); ?></p>
	<p>
		<input id="schedule-delete-subscriber-button" class="button" type="button" value="<?php esc_attr_e( 'Schedule Delete Job', 'mailster' ); ?>" />
	</p>
</section>
<section class="footer alternate">
<p>
	<input id="delete-subscriber-button" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Delete Subscribers permanently', 'mailster' ); ?>" />
	<span class="status wp-ui-text-icon spinner"></span>

</p>
</section>
</form>

<?php else : ?>

<h2><?php esc_html_e( 'You have no subscribers to delete!', 'mailster' ); ?></h2>

<?php endif; ?>
