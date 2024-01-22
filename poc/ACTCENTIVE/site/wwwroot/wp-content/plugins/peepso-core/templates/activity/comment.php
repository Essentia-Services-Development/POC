<?php
$PeepSoActivity = PeepSoActivity::get_instance();
$PeepSoUser = PeepSoUser::get_instance($post_author);
$fullName = $PeepSoUser->get_fullname();
?>
<div id="comment-item-<?php echo $ID; ?>" class="ps-comment ps-comment-item cstream-comment stream-comment ps-js-comment-item" data-comment-id="<?php echo $ID; ?>" data-author="<?php echo $post_author; ?>">
	<div class="ps-comment__avatar ps-avatar ps-avatar--comment">
		<a href="<?php echo $PeepSoUser->get_profileurl(); ?>">
			<img data-author="<?php echo $post_author; ?>" src="<?php echo PeepSoUser::get_instance($post_author)->get_avatar(); ?>" alt="<?php echo __($fullName); ?> avatar" />
		</a>
	</div>

	<div class="ps-comment__body js-stream-content ps-js-comment-body">
  	<?php if(empty($human_friendly) || empty(PeepSo3_Mayfly::get('peepso_cache_hf_'.$ID))) { ?>
		<input type="hidden" name="peepso_set_human_friendly"
			data-author="<?php echo $post_author; ?>"
			value="<?php echo $ID;?>" />
            <?php
            PeepSo3_Mayfly::set('peepso_cache_hf_' . $ID, 1, 600);
        }
        ?>

		<div class="ps-comment__author">
      @peepso_user_<?php echo $post_author; ?>(<?php echo $fullName; ?>)
    </div>

    <div class="ps-comment__content stream-comment-content ps-js-comment-content" data-type="stream-comment-content">
      <?php $PeepSoActivity->content(); ?>
    </div>

		<div data-type="stream-more" class="ps-comment__content-more cstream-more" data-commentmore="true"></div>

		<div class="ps-comment__attachments ps-comment-media js-stream-attachments ps-js-comment-attachment"><?php $PeepSoActivity->comment_attachment(); ?></div>


		<div class="ps-comment__meta">
      <div class="ps-comment__info">
				<?php
				$PeepSoActivity->post_edit_notice();
				?>
        <span class="activity-post-age activity-post-age-text-only" data-timestamp="<?php $PeepSoActivity->post_timestamp(); ?>" style="display:none">
					<?php $PeepSoActivity->post_age(); ?>
				</span>
				<span class="activity-post-age activity-post-age-link" data-timestamp="<?php $PeepSoActivity->post_timestamp(); ?>">
					<a href="<?php $PeepSoActivity->comment_link(); ?>">
					<?php $PeepSoActivity->post_age(); ?>
					</a>
				</span>
      </div>
      <div class="ps-comment__actions">
				<?php if($likes = $PeepSoActivity->has_likes($act_id)){ ?>
	        <div id="act-like-<?php echo $act_id; ?>" class="ps-comment__action ps-comment__action--like ps-js-act-like--<?php echo $act_id; ?>" data-count="<?php echo $likes ?>">
						<?php $PeepSoActivity->show_like_count($likes); ?>
					</div>
				<?php } else { ?>
					<div id="act-like-<?php echo $act_id; ?>" class="ps-comment__action ps-comment__action--like ps-js-act-like--<?php echo $act_id; ?>" data-count="0" style="display:none">
						<?php $PeepSoActivity->show_like_count($likes); ?>
					</div>
				<?php } ?>

				<?php $PeepSoActivity->comment_actions(); ?>

				<a class="ps-comment__copy" href="<?php $PeepSoActivity->comment_link(); ?>"><?php $PeepSoActivity->post_permalink(); ?></a>
      </div>
    </div>

		<?php if (is_user_logged_in()) : ?>
		<div class="ps-comment__actions-dropdown ps-dropdown--left ps-js-dropdown">
			<a href="javascript:" class="ps-dropdown__toggle ps-js-dropdown-toggle"><i class="gcis gci-ellipsis-h"></i></a>
			<div class="ps-dropdown__menu ps-js-dropdown-menu">
				<?php $PeepSoActivity->comment_actions_dropdown(); ?>
			</div>
		</div>
	<?php endif; ?>
	</div>
</div>

<?php
	$PeepSoActivity2 = new PeepSoActivity();
	$PeepSoActivity2->show_replycomment(get_current_user_id(), $ID, $act_id);
?>
