<?php
$classes   = array( 'theme' );
$classes[] = 'theme-' . $slug;
if ( $item['is_default'] ) {
	$classes[] = 'active';
}
if ( $item['installed'] ) {
	$classes[] = 'is-installed';
}
if ( ! $item['is_supported'] && ! $item['installed'] ) {
	$classes[] = 'not-supported';
}
if ( $item['update_available'] ) {
	$classes[] = 'update-available';
}
if ( $item['envato_item_id'] ) {
	$classes[] = 'envato-item';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" tabindex="0" data-slug="<?php echo esc_attr( $slug ); ?>" data-item='<?php echo esc_attr( json_encode( $item ) ); ?>'>
	<span class="spinner"></span>
	<div class="theme-screenshot">
		<img loading="lazy" alt="" class="theme-screenshot-bg" srcset="<?php echo esc_attr( $item['image'] ); ?> 1x, <?php echo esc_attr( $item['imagex2'] ); ?> 2x" src="<?php echo esc_attr( $item['image'] ); ?>" >
		<?php if ( $item['index'] ) : ?>
		<iframe src="<?php echo esc_url( add_query_arg( array( 'nocache' => time() ), $item['index'] ) ); ?>" class="theme-screenshot-iframe" scrolling="no" allowTransparency="true" frameBorder="0" sandbox="allow-presentation allow-scripts" loading="lazy"></iframe>
		<?php endif; ?>
		<img loading="lazy" alt="" class="theme-screenshot-img" srcset="<?php echo esc_attr( $item['image'] ); ?> 1x, <?php echo esc_attr( $item['imagex2'] ); ?> 2x" src="<?php echo esc_attr( $item['image'] ); ?>" >
	</div>
	<div class="notice inline update-message notice-success notice-alt"></div>
	<div class="notice inline update-message notice-warning notice-alt"></div>
	<div class="notice inline update-message notice-error notice-alt"></div>
	<?php if ( ! $item['is_supported'] && ! $item['installed'] ) : ?>
	<div class="notice inline update-message notice-error notice-alt"><p><?php printf( esc_html__( 'This template requires Mailster version %s or above. Please update first.', 'mailster' ), '<strong>' . $item['requires'] . '</strong>' ); ?></p></div>
	<?php endif; ?>
	<?php if ( $item['update_available'] ) : ?>
	<div class="notice inline update-message notice-warning notice-alt theme-has-update">
		<p><?php esc_html_e( 'New version available.', 'mailster' ); ?>
		<?php if ( $item['download_url'] ) : ?>
		<a class="button-link update" data-width="800" data-height="80%" href="<?php echo esc_url( $item['download_url'] ); ?>"><?php esc_html_e( 'Update now', 'mailster' ); ?></a>
		<?php elseif ( $item['download'] ) : ?>
			<?php
			$url = add_query_arg(
				array(
					'action'   => 'mailster_template_endpoint',
					'slug'     => $slug,
					'url'      => rawurlencode( $item['download'] ),
					'_wpnonce' => wp_create_nonce( 'mailster_download_template_' . $slug ),
				),
				admin_url( 'admin-ajax.php' )
			);
			?>
		<a class="button-link request-update popup" data-width="800" data-height="80%" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Update now', 'mailster' ); ?></a>
		<?php endif; ?>
		</p>
	</div>
	<?php endif; ?>
	<span class="more-details"><?php esc_html_e( 'Details & Preview', 'mailster' ); ?></span>
	<div class="theme-author"><?php printf( esc_html__( 'By %s', 'mailster' ), $item['author'] ); ?></div>
	<div class="theme-id-container">
		<h3 class="theme-name">
			<?php echo esc_html( $item['name'] ); ?>
			<?php if ( $item['is_default'] ) : ?>
			<span class="theme-badge theme-default-badge"><?php esc_html_e( 'Current', 'mailster' ); ?></span>
			<?php elseif ( $item['installed'] ) : ?>
			<span class="theme-badge theme-installed-badge"><?php esc_html_e( 'Installed', 'mailster' ); ?></span>
			<?php endif; ?>
		</h3>
		<div class="theme-actions">
			<a class="button button-primary create-campaign" href="<?php echo admin_url( 'post-new.php?post_type=newsletter&template=' . esc_attr( $slug ) ); ?>" aria-label="<?php esc_attr_e( 'Create Campaign', 'mailster' ); ?>"><?php esc_html_e( 'Create Campaign', 'mailster' ); ?></a>
			<?php if ( $item['download_url'] ) : ?>
			<a class="button button-primary download" data-width="800" data-height="80%" href="<?php echo esc_url( $item['download_url'] ); ?>"><?php esc_html_e( 'Download', 'mailster' ); ?></a>
			<?php elseif ( $item['download'] ) : ?>
				<?php
				$url = add_query_arg(
					array(
						'action'   => 'mailster_template_endpoint',
						'slug'     => $slug,
						'url'      => rawurlencode( $item['download'] ),
						'_wpnonce' => wp_create_nonce( 'mailster_download_template_' . $slug ),
					),
					admin_url( 'admin-ajax.php' )
				);
				?>
			<a class="button button-primary request-download popup" data-width="800" data-height="90%" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Download', 'mailster' ); ?></a>
			<?php endif; ?>
			<?php if ( $item['price'] ) : ?>
			<a class="button button-primary buy external" data-width="800" data-height="80%" href="<?php echo mailster_url( $item['purchase_url'], 'utm_term=mailster_templates' ); ?>"><?php esc_html_e( 'Buy Template', 'mailster' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</div>
