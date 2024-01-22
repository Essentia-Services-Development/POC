<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * BuddyPress - Members
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

do_action( 'bp_before_directory_members_page' ); ?>

<div id="buddypress">

	<?php 
		do_action( 'bp_before_directory_members' ); 
		do_action( 'bp_before_directory_members_content' ); 
	?>

	<?php do_action( 'bp_before_directory_members_tabs' ); ?>

	<form action="" method="post" id="members-directory-form" class="dir-form">

		<div class="item-list-tabs" role="navigation">
			<ul>
				<li class="selected" id="members-all"><a href="<?php bp_members_directory_permalink(); ?>"><?php printf( esc_html__( 'All Members %s', 'rehub-theme' ), '<span>' . bp_get_total_member_count() . '</span>' ); ?></a></li>

				<?php if ( is_user_logged_in() && bp_is_active( 'friends' ) && bp_get_total_friend_count( bp_loggedin_user_id() ) ) : ?>
					<li id="members-personal"><a href="<?php echo bp_loggedin_user_domain() . bp_get_friends_slug() . '/my-friends/'; ?>"><?php printf( esc_html__( 'My Friends %s', 'rehub-theme' ), '<span>' . bp_get_total_friend_count( bp_loggedin_user_id() ) . '</span>' ); ?></a></li>
				<?php endif; ?>

				<?php do_action( 'bp_members_directory_member_types' ); ?>
			</ul>
		</div><!-- .item-list-tabs -->

		<div class="item-list-tabs" id="subnav" role="navigation">
			<ul>
				<?php do_action( 'bp_members_directory_member_sub_types' ); ?>

				<li id="members-order-select" class="last filter">
					<label for="members-order-by"><?php esc_html_e( 'Order By:', 'rehub-theme' ); ?></label>
					<select id="members-order-by">
						<option value="active"><?php esc_html_e( 'Last Active', 'rehub-theme' ); ?></option>
						<option value="newest"><?php esc_html_e( 'Newest Registered', 'rehub-theme' ); ?></option>

						<?php if ( bp_is_active( 'xprofile' ) ) : ?>
							<option value="alphabetical"><?php esc_html_e( 'Alphabetical', 'rehub-theme' ); ?></option>
						<?php endif; ?>

						<?php do_action( 'bp_members_directory_order_options' ); ?>
					</select>
				</li>
			</ul>
		</div>

		<div id="members-dir-list" class="members dir-list">
			<?php bp_get_template_part( 'buddypress/members/members-loop' ); ?>
		</div><!-- #members-dir-list -->

		<?php 
			do_action( 'bp_directory_members_content' );
			wp_nonce_field( 'directory_members', '_wpnonce-member-filter' ); 
			do_action( 'bp_after_directory_members_content' ); 
		?>

	</form><!-- #members-directory-form -->

	<?php do_action( 'bp_after_directory_members' ); ?>

</div><!-- #buddypress -->

<?php do_action( 'bp_after_directory_members_page' );
