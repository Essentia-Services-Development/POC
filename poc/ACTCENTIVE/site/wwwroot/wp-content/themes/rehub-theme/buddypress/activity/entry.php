<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * BuddyPress - Activity Stream (Single Item)
 *
 * This template is used by activity-loop.php and AJAX functions to show
 * each activity.
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

/**
 * Fires before the display of an activity entry.
 *
 * @since 1.2.0
 */
do_action( 'bp_before_activity_entry' ); ?>

<li class="<?php bp_activity_css_class(); ?>" id="activity-<?php bp_activity_id(); ?>">

	<div class="activity-header-wrap flowhidden mb10">
		<div class="activity-avatar">
			<a href="<?php bp_activity_user_link(); ?>">
				<?php bp_activity_avatar('width=50&height=50'); ?>		
			</a>
			<?php if (function_exists('mycred_get_users_badges') && rehub_option('bp_enable_mycred_comment_badge') == '1') :?>
				<?php rh_mycred_display_users_badges(bp_get_activity_user_id());?>
			<?php endif;?>
		</div>	
		<div class="activity-header greycolor font90">
			<?php bp_activity_action(); ?>
		</div>			
	</div>



	<div class="activity-content">

		<?php if ( bp_activity_has_content() ) : ?>

			<div class="activity-inner">

				<?php bp_activity_content_body(); ?>

			</div>

		<?php endif; ?>

		<?php

		/**
		 * Fires after the display of an activity entry content.
		 *
		 * @since 1.2.0
		 */
		do_action( 'bp_activity_entry_content' ); ?>

		<div class="activity-meta-justicons">

			<?php if ( bp_get_activity_type() == 'activity_comment' ) : ?>
				<a href="<?php bp_activity_thread_permalink(); ?>" class="acomment-reply" title="<?php esc_html_e( 'View Conversation', 'rehub-theme' ); ?>"><?php esc_html_e( 'View Conversation', 'rehub-theme' ); ?></a>
			<?php endif; ?>

			<?php if ( is_user_logged_in() ) : ?>
				<?php if ( bp_activity_can_comment() ) : ?>
					<a href="<?php bp_activity_comment_link(); ?>" class="acomment-reply" id="acomment-comment-<?php bp_activity_id(); ?>"><?php printf( esc_html__( 'Comment %s', 'rehub-theme' ), '<span>' . bp_activity_get_comment_count() . '</span>' ); ?></a>
				<?php endif; ?>

				<?php if ( bp_activity_can_favorite() ) : ?>
					<?php if ( !bp_get_activity_is_favorite() ) : ?>
						<a href="<?php bp_activity_favorite_link(); ?>" class="fav" title="<?php esc_html_e( 'Mark as Favorite', 'rehub-theme' ); ?>"><?php esc_html_e( 'Favorite', 'rehub-theme' ); ?></a>
					<?php else : ?>
						<a href="<?php bp_activity_unfavorite_link(); ?>" class="unfav" title="<?php esc_html_e( 'Remove Favorite', 'rehub-theme' ); ?>"><?php esc_html_e( 'Remove Favorite', 'rehub-theme' ); ?></a>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( bp_activity_user_can_delete() ): ?>
					<?php
						$url   = bp_get_activity_delete_url();
						$class = 'delete-activity';
						// Determine if we're on a single activity page, and customize accordingly.
						if ( bp_is_activity_component() && is_numeric( bp_current_action() ) ) {
							$class = 'delete-activity-single';
						}
					?>
					<a href="<?php echo esc_url( $url );?>" class="<?php echo esc_attr($class);?> confirm" rel="nofollow"><?php esc_html_e( 'Delete', 'rehub-theme' );?></a>			
				<?php endif;?>

			<?php endif; ?>
		</div>		

		<div class="activity-meta">
			<?php if ( is_user_logged_in() ) : ?>
				<?php
				/**
				 * Fires at the end of the activity entry meta data area.
				 *
				 * @since 1.2.0
				 */
				do_action( 'bp_activity_entry_meta' ); ?>
			<?php endif; ?>
		</div>

	</div>

	<?php

	/**
	 * Fires before the display of the activity entry comments.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_before_activity_entry_comments' ); ?>

	<?php if ( ( bp_activity_get_comment_count() || bp_activity_can_comment() ) || bp_is_single_activity() ) : ?>

		<div class="activity-comments">

			<?php bp_activity_comments(); ?>

			<?php if ( is_user_logged_in() && bp_activity_can_comment() ) : ?>

				<form action="<?php bp_activity_comment_form_action(); ?>" method="post" id="ac-form-<?php bp_activity_id(); ?>" class="ac-form"<?php bp_activity_comment_form_nojs_display(); ?>>
					<div class="ac-reply-avatar"><?php bp_loggedin_user_avatar( 'width=' . BP_AVATAR_THUMB_WIDTH . '&height=' . BP_AVATAR_THUMB_HEIGHT ); ?></div>
					<div class="ac-reply-content">
						<div class="ac-textarea">
							<label for="ac-input-<?php bp_activity_id(); ?>" class="bp-screen-reader-text"><?php esc_html_e( 'Comment', 'rehub-theme' ); ?></label>
							<textarea id="ac-input-<?php bp_activity_id(); ?>" class="ac-input bp-suggestions" name="ac_input_<?php bp_activity_id(); ?>"></textarea>
						</div>
						<input type="submit" name="ac_form_submit" value="<?php esc_html_e( 'Post', 'rehub-theme' ); ?>" /> &nbsp; <a href="#" class="ac-reply-cancel font80"><?php esc_html_e( 'Cancel', 'rehub-theme' ); ?></a>
						<input type="hidden" name="comment_form_id" value="<?php bp_activity_id(); ?>" />
					</div>

					<?php

					/**
					 * Fires after the activity entry comment form.
					 *
					 * @since 1.5.0
					 */
					do_action( 'bp_activity_entry_comments' ); ?>

					<?php wp_nonce_field( 'new_activity_comment', '_wpnonce_new_activity_comment_'.bp_get_activity_id() ); ?>

				</form>

			<?php endif; ?>

		</div>

	<?php endif; ?>

	<?php

	/**
	 * Fires after the display of the activity entry comments.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_after_activity_entry_comments' ); ?>

</li>

<?php

/**
 * Fires after the display of an activity entry.
 *
 * @since 1.2.0
 */
do_action( 'bp_after_activity_entry' ); ?>
