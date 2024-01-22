<?php

$table = new Mailster_Logs_Table();

$table->prepare_items();

?>
<div class="wrap">
<h1>
<?php printf( esc_html__( _n( '%s Entry found', '%s Entries found', $table->total_items, 'mailster' ) ), number_format_i18n( $table->total_items ) ); ?>
<?php if ( current_user_can( 'manage_options' ) ) : ?>
	<a href="<?php echo admin_url( 'edit.php?post_type=newsletter&page=mailster_settings#logging' ); ?>" class="page-title-action"><?php esc_html_e( 'Settings', 'mailster' ); ?></a>
<?php endif; ?>
<?php if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) : ?>
	<span class="subtitle"><?php printf( esc_html__( 'Search result for %s', 'mailster' ), '&quot;' . esc_html( stripslashes( $_GET['s'] ) ) . '&quot;' ); ?></span>
	<?php endif; ?>
</h1>
<?php
$table->search_box( esc_html__( 'Search Log Entries', 'mailster' ), 's' );
$table->views();

?>
 
<form method="post" action="" id="logs-overview-form">
<?php $table->display(); ?>
</form>
</div>
