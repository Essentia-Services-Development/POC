<form id="import_wordpress" method="post" class="importer-form" data-type="wordpress">
	<?php $roles = get_editable_roles(); ?>
	<p class="howto"><?php printf( esc_html__( 'Import your existing WordPress users here. You can define how Mailster should handle future users on the %s.', 'mailster' ), '<a href="edit.php?post_type=newsletter&page=mailster_settings#wordpress-users">' . esc_html__( 'Settings page', 'mailster' ) . '</a>' ); ?><?php echo mailster()->beacon( '611bb1fe21ef206e5592c2a9' ); ?></p>
	<div class="inner">
		<div class="wordpress-user-roles">
			<p><strong><?php esc_html_e( 'WordPress users roles', 'mailster' ); ?></strong></p>
			<p class="howto"><?php esc_html_e( 'Select the user roles you like to import.', 'mailster' ); ?></p>
			<ul>
				<li><label><input type="checkbox" class="list-toggle" checked> <?php esc_html_e( 'toggle all', 'mailster' ); ?></label></li>
				<li>&nbsp;</li>
				<ul class="roles">
				<?php foreach ( $roles as $role_key => $role ) : ?>
					<li><label><input type="checkbox" name="roles[]" value="<?php echo esc_attr( $role_key ); ?>" checked> <?php echo esc_html( $role['name'] ); ?></label></li>
				<?php endforeach; ?>
				</ul>
			</ul>
				<ul>
					<li><label><input type="checkbox" class="no-role-cb" name="no_role" value="1"> <?php esc_html_e( 'users without a role', 'mailster' ); ?></label></li>
				</ul>
		</div>
		<div class="wordpress-user-meta-fields">
			<?php $meta_values = mailster( 'helper' )->get_wpuser_meta_fields(); ?>
			<p><strong><?php esc_html_e( 'Handle following meta values', 'mailster' ); ?></strong></p>
			<p class="howto"><?php esc_html_e( 'Select the meta fields you like to import.', 'mailster' ); ?></p>
			<ul>
				<li><label><input type="checkbox" class="list-toggle"> <?php esc_html_e( 'toggle all', 'mailster' ); ?></label></li>
				<li>&nbsp;</li>
			<ul>
			<?php foreach ( $meta_values as $i => $meta_value ) : ?>
				<li><label><input type="checkbox" name="meta_values[]" value="<?php echo esc_attr( $meta_value ); ?>"> <?php echo esc_html( $meta_value ); ?></label></li>
			<?php endforeach; ?>
			</ul>
			</ul>
		</div>
	</div>
	<section class="footer alternate">
		<p>
			<?php submit_button( esc_html__( 'Next Step', 'mailster' ) . ' &#x2192;', 'primary', 'submit', false ); ?>
			<span class="status wp-ui-text-icon"></span>
		</p>
	</section>
</form>
