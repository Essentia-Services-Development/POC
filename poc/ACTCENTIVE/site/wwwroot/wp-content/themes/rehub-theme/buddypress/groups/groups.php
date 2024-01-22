<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * BuddyPress - Groups
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

do_action( 'bp_before_directory_groups_page' ); ?>

	<?php 
		do_action( 'bp_before_directory_groups' ); 
		do_action( 'bp_before_directory_groups_content' ); 
	?>

	<form action="" method="post" id="groups-directory-form" class="dir-form">

		<?php
		/** This action is documented in bp-templates/bp-legacy/buddypress/activity/index.php */
		do_action( 'template_notices' ); ?>

		<div class="item-list-tabs" role="navigation">
			<ul>
				<li class="selected" id="groups-all"><a href="<?php bp_groups_directory_permalink(); ?>"><?php printf( esc_html__( 'All Groups %s', 'rehub-theme' ), '<span>' . bp_get_total_group_count() . '</span>' ); ?></a></li>

				<?php if ( is_user_logged_in() && bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ) : ?>
					<li id="groups-personal"><a href="<?php echo bp_loggedin_user_domain() . bp_get_groups_slug() . '/my-groups/'; ?>"><?php printf( esc_html__( 'My Groups %s', 'rehub-theme' ), '<span>' . bp_get_total_group_count_for_user( bp_loggedin_user_id() ) . '</span>' ); ?></a></li>
				<?php endif; ?>

				<?php do_action( 'bp_groups_directory_group_filter' ); ?>

			</ul>
		</div><!-- .item-list-tabs -->

		<div class="item-list-tabs" id="subnav" role="navigation">
			<ul>
				<?php do_action( 'bp_groups_directory_group_types' ); ?>
				<li id="groups-order-select" class="last filter">
					<label for="groups-order-by"><?php esc_html_e( 'Order By:', 'rehub-theme' ); ?></label>
					<select id="groups-order-by">
						<option value="active"><?php esc_html_e( 'Last Active', 'rehub-theme' ); ?></option>
						<option value="popular"><?php esc_html_e( 'Most Members', 'rehub-theme' ); ?></option>
						<option value="newest"><?php esc_html_e( 'Newly Created', 'rehub-theme' ); ?></option>
						<option value="alphabetical"><?php esc_html_e( 'Alphabetical', 'rehub-theme' ); ?></option>
						<?php do_action( 'bp_groups_directory_order_options' ); ?>
					</select>
				</li>
			</ul>
		</div>

		<div id="groups-dir-list" class="groups dir-list">
			<?php bp_get_template_part( 'buddypress/groups/groups-loop' ); ?>
		</div><!-- #groups-dir-list -->

		<?php 
			do_action( 'bp_directory_groups_content' );
			wp_nonce_field( 'directory_groups', '_wpnonce-groups-filter' ); 
			do_action( 'bp_after_directory_groups_content' ); 
		?>

	</form><!-- #groups-directory-form -->

	<?php do_action( 'bp_after_directory_groups' ); ?>



<?php do_action( 'bp_after_directory_groups_page' );
