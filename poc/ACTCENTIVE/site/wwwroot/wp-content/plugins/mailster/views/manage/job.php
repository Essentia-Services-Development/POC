<?php
	$lists  = mailster( 'lists' )->get();
	$lists  = wp_list_pluck( $lists, 'name', 'ID' );
	$status = mailster( 'subscribers' )->get_status();
	$user   = get_user_by( 'id', $job['user_id'] );
?>
<div>
	<?php esc_html_e( 'Delete all subscribers', 'mailster' ); ?>
	<?php if ( isset( $job['status'] ) ) : ?>
	<p><?php printf( esc_html__( 'with a status of %s', 'mailster' ), '<strong>' . implode( ', ', array_intersect_key( $status, array_flip( $job['status'] ) ) ) . '</strong>' ); ?></p>
	<?php endif; ?>

	<?php if ( isset( $job['lists'] ) ) : ?>
	<p><?php printf( esc_html__( 'assigned to lists %s', 'mailster' ), '<strong>' . implode( ', ', array_intersect_key( $lists, array_flip( $job['lists'] ) ) ) . '</strong>' ); ?></p>
	<?php endif; ?>

	<?php if ( isset( $job['nolists'] ) && $job['nolists'] ) : ?>
	<p><?php esc_html_e( 'and assigned to no list.', 'mailster' ); ?></p>
	<?php endif; ?>

	<?php if ( isset( $job['conditions'] ) ) : ?>
	<p><?php mailster( 'conditions' )->render( $job['conditions'] ); ?></p>
	<?php endif; ?>

	<?php if ( isset( $job['remove_actions'] ) ) : ?>
	<p>&#10004;<?php esc_html_e( 'remove actions', 'mailster' ); ?></p>
	<?php endif; ?>
	<p class="howto"><?php printf( esc_html__( '%1$s created this job on the %2$s', 'mailster' ), $user->display_name, wp_date( get_option( 'date_format' ), $job['timestamp'] ) ); ?></p>
</div>
